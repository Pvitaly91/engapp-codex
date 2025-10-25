<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Question;
use App\Models\TextBlock;
use App\Services\QuestionDeletionService;
use Database\Seeders\Pages\Concerns\GrammarPageSeeder as GrammarPageSeederBase;
use Database\Seeders\QuestionSeeder as QuestionSeederBase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class SeedRunController extends Controller
{
    public function __construct(private QuestionDeletionService $questionDeletionService)
    {
    }

    public function index(): View
    {
        $overview = $this->assembleSeedRunOverview();

        return view('seed-runs.index', [
            'tableExists' => $overview['tableExists'],
            'executedSeeders' => $overview['executedSeeders'],
            'pendingSeeders' => $overview['pendingSeeders'],
            'executedSeederHierarchy' => $overview['executedSeederHierarchy'],
            'recentSeedRunOrdinals' => $overview['recentSeedRunOrdinals'],
        ]);
    }

    protected function assembleSeedRunOverview(): array
    {
        $tableExists = Schema::hasTable('seed_runs');
        $executedSeeders = collect();
        $pendingSeeders = collect();
        $executedSeederHierarchy = collect();
        $recentSeedRunOrdinals = collect();
        $recentThreshold = now()->subDay();

        if (! $tableExists) {
            return [
                'tableExists' => false,
                'executedSeeders' => $executedSeeders,
                'pendingSeeders' => $pendingSeeders,
                'executedSeederHierarchy' => $executedSeederHierarchy,
                'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
            ];
        }

        $executedSeeders = DB::table('seed_runs')
            ->orderByDesc('ran_at')
            ->get()
            ->map(function ($seedRun) {
                $seedRun->ran_at = $seedRun->ran_at ? Carbon::parse($seedRun->ran_at) : null;
                $seedRun->display_class_name = $this->formatSeederClassName($seedRun->class_name);

                return $seedRun;
            });

        $recentSeedRuns = $executedSeeders
            ->filter(fn ($seedRun) => optional($seedRun->ran_at)->greaterThanOrEqualTo($recentThreshold))
            ->sortByDesc(fn ($seedRun) => optional($seedRun->ran_at)->timestamp ?? 0)
            ->values();

        $recentSeedRunOrdinals = $recentSeedRuns
            ->mapWithKeys(fn ($seedRun, $index) => [$seedRun->id => $index + 1]);

        $executedClasses = $executedSeeders
            ->pluck('class_name')
            ->all();

        $pendingSeeders = collect($this->discoverSeederClasses(database_path('seeders')))
            ->reject(fn (string $class) => in_array($class, $executedClasses, true))
            ->map(fn (string $class) => (object) [
                'class_name' => $class,
                'display_class_name' => $this->formatSeederClassName($class),
            ])
            ->values();

        $questionCounts = collect();

        if (Schema::hasColumn('questions', 'seeder') && $executedSeeders->isNotEmpty()) {
            $questionCounts = Question::query()
                ->select('seeder', DB::raw('COUNT(*) as aggregate'))
                ->whereIn('seeder', $executedClasses)
                ->groupBy('seeder')
                ->pluck('aggregate', 'seeder');
        }

        $executedSeeders = $executedSeeders->map(function ($seedRun) use ($questionCounts) {
            $seedRun->question_count = (int) ($questionCounts[$seedRun->class_name] ?? 0);
            $seedRun->data_profile = $this->describeSeederData($seedRun->class_name);

            return $seedRun;
        });

        $executedSeederHierarchy = $this->buildSeederHierarchy($executedSeeders);

        return [
            'tableExists' => true,
            'executedSeeders' => $executedSeeders,
            'pendingSeeders' => $pendingSeeders,
            'executedSeederHierarchy' => $executedSeederHierarchy,
            'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
        ];
    }

    protected function formatSeederClassName(string $className): string
    {
        $shortName = Str::after($className, 'Database\\Seeders\\');

        return $shortName !== '' ? $shortName : $className;
    }

    protected function renderQuestionWithHighlightedAnswers(Question $question): string
    {
        $questionText = e($question->question ?? '');

        foreach ($question->answers as $answer) {
            $answerText = optional($answer->option)->option ?? $answer->answer;

            if (! filled($answerText)) {
                continue;
            }

            $replacement = sprintf(
                '<span class="inline-flex items-center px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">%s</span>',
                e($answerText)
            );

            $questionText = str_replace('{' . $answer->marker . '}', $replacement, $questionText);
        }

        return nl2br($questionText);
    }

    protected function buildSeederHierarchy(Collection $seedRuns): Collection
    {
        $root = [
            'folders' => [],
            'seeders' => [],
        ];

        foreach ($seedRuns as $seedRun) {
            $segments = array_values(array_filter(explode('\\', $seedRun->display_class_name), 'strlen'));

            if (empty($segments)) {
                $root['seeders'][] = [
                    'name' => $seedRun->display_class_name,
                    'seed_run' => $seedRun,
                ];

                continue;
            }

            $current =& $root;

            foreach ($segments as $index => $segment) {
                $isLast = $index === count($segments) - 1;

                if ($isLast) {
                    $current['seeders'][] = [
                        'name' => $segment,
                        'seed_run' => $seedRun,
                    ];

                    continue;
                }

                if (! isset($current['folders'][$segment])) {
                    $current['folders'][$segment] = [
                        'name' => $segment,
                        'folders' => [],
                        'seeders' => [],
                    ];
                }

                $current =& $current['folders'][$segment];
            }

            unset($current);
        }

        return $this->normalizeSeederHierarchy($root);
    }

    protected function normalizeSeederHierarchy(array $node, string $path = ''): Collection
    {
        $folders = collect($node['folders'] ?? [])
            ->sortBy(fn ($folder) => $folder['name'])
            ->map(function ($folder) use ($path) {
                $folderPath = ltrim(($path !== '' ? $path . '/' : '') . $folder['name'], '/');
                $children = $this->normalizeSeederHierarchy($folder, $folderPath);
                $seedRunIds = $children->flatMap(function ($child) {
                    return collect($child['seed_run_ids'] ?? []);
                })->unique()->values();
                $classNames = $children->flatMap(function ($child) {
                    return collect($child['class_names'] ?? []);
                })->unique()->values();
                $folderProfile = $this->describeFolderData($classNames);

                return [
                    'type' => 'folder',
                    'name' => $folder['name'],
                    'children' => $children,
                    'seeder_count' => $seedRunIds->count(),
                    'seed_run_ids' => $seedRunIds->all(),
                    'class_names' => $classNames->all(),
                    'path' => $folderPath,
                    'folder_profile' => $folderProfile,
                ];
            });

        $seeders = collect($node['seeders'] ?? [])
            ->sortBy(fn ($seeder) => $seeder['name'])
            ->map(function ($seeder) use ($path) {
                $seedRun = $seeder['seed_run'];
                $seedRunIds = [$seedRun->id];
                $classNames = [$seedRun->class_name];
                $fullPath = ltrim(($path !== '' ? $path . '/' : '') . $seeder['name'], '/');

                return [
                    'type' => 'seeder',
                    'name' => $seeder['name'],
                    'seed_run' => $seedRun,
                    'seeder_count' => 1,
                    'seed_run_ids' => $seedRunIds,
                    'class_names' => $classNames,
                    'path' => $fullPath,
                    'data_profile' => $this->describeSeederData($seedRun->class_name),
                ];
            });

        return $folders->values()->merge($seeders->values())->values();
    }

    public function loadFolderChildren(Request $request): JsonResponse
    {
        $overview = $this->assembleSeedRunOverview();
        $path = trim((string) $request->query('path', ''), '/');
        $depth = max(0, (int) $request->query('depth', 0));

        if (! $overview['tableExists']) {
            return response()->json(['html' => '']);
        }

        $nodes = $overview['executedSeederHierarchy'];
        $targetDepth = $depth > 0 ? $depth : ($path === '' ? 0 : substr_count($path, '/') + 1);

        if ($path === '') {
            $children = $nodes;
        } else {
            $node = $this->findNodeByPath($nodes, $path);

            if (! $node || ($node['type'] ?? null) !== 'folder') {
                return response()->json([
                    'html' => '',
                    'message' => __('Не вдалося знайти вказану папку.'),
                ], 404);
            }

            $children = collect($node['children'] ?? []);
        }

        $html = view('seed-runs.partials.node-collection', [
            'nodes' => $children,
            'depth' => $targetDepth,
            'recentSeedRunOrdinals' => $overview['recentSeedRunOrdinals'],
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function loadSeederCategories(int $seedRunId): JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            return response()->json([
                'html' => '',
                'message' => __('Таблиця seed_runs недоступна.'),
            ], 404);
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            return response()->json([
                'html' => '',
                'message' => __('Запис сидера не знайдено.'),
            ], 404);
        }

        $categories = $this->buildCategorySummaries($seedRun->class_name);
        $seedRun->question_count = (int) $categories->sum(fn ($category) => $category['question_count'] ?? 0);

        $html = view('seed-runs.partials.seeder-categories', [
            'seedRun' => $seedRun,
            'categories' => $categories,
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function loadSourceQuestions(int $seedRunId, string $categoryKey, string $sourceKey): JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            return response()->json([
                'html' => '',
                'message' => __('Таблиця seed_runs недоступна.'),
            ], 404);
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            return response()->json([
                'html' => '',
                'message' => __('Запис сидера не знайдено.'),
            ], 404);
        }

        try {
            $categoryId = $this->parseCategoryKey($categoryKey);
            $sourceId = $this->parseSourceKey($sourceKey);
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'html' => '',
                'message' => $exception->getMessage(),
            ], 422);
        }

        $questionsQuery = Question::query()
            ->with(['answers.option'])
            ->where('seeder', $seedRun->class_name)
            ->orderBy('id');

        if (is_null($categoryId)) {
            $questionsQuery->whereNull('category_id');
        } else {
            $questionsQuery->where('category_id', $categoryId);
        }

        if (is_null($sourceId)) {
            $questionsQuery->whereNull('source_id');
        } else {
            $questionsQuery->where('source_id', $sourceId);
        }

        $questions = $questionsQuery
            ->get()
            ->map(function (Question $question) {
                return [
                    'id' => $question->id,
                    'highlighted_text' => $this->renderQuestionWithHighlightedAnswers($question),
                ];
            });

        $html = view('seed-runs.partials.source-questions', [
            'seedRunId' => $seedRunId,
            'categoryKey' => $categoryKey,
            'sourceKey' => $sourceKey,
            'questions' => $questions,
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function loadQuestionAnswers(int $seedRunId, Question $question): JsonResponse
    {
        if (! Schema::hasTable('seed_runs') || ! Schema::hasTable('questions')) {
            return response()->json([
                'html' => '',
                'message' => __('Потрібні таблиці бази даних недоступні.'),
            ], 404);
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            return response()->json([
                'html' => '',
                'message' => __('Запис сидера не знайдено.'),
            ], 404);
        }

        if ($question->seeder !== $seedRun->class_name) {
            return response()->json([
                'html' => '',
                'message' => __('Питання не належить до вибраного сидера.'),
            ], 404);
        }

        $question->loadMissing(['answers.option', 'options']);

        $answers = $question->answers->map(function ($answer) {
            $label = optional($answer->option)->option ?? $answer->answer;

            return [
                'marker' => $answer->marker,
                'label' => $label,
                'option_id' => $answer->option_id,
            ];
        });

        $correctOptionIds = $answers
            ->pluck('option_id')
            ->filter()
            ->unique()
            ->all();

        $options = $question->options
            ->map(function ($option) use ($correctOptionIds) {
                return [
                    'id' => $option->id,
                    'label' => $option->option,
                    'is_correct' => in_array($option->id, $correctOptionIds, true),
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $textAnswers = $answers
            ->filter(function ($answer) {
                return empty($answer['option_id']) && filled($answer['label']);
            })
            ->map(function ($answer) {
                return [
                    'marker' => $answer['marker'],
                    'label' => $answer['label'],
                ];
            })
            ->values();

        $html = view('seed-runs.partials.question-answers', [
            'options' => $options,
            'textAnswers' => $textAnswers,
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function loadQuestionTags(int $seedRunId, Question $question): JsonResponse
    {
        if (! Schema::hasTable('seed_runs') || ! Schema::hasTable('questions') || ! Schema::hasTable('tags')) {
            return response()->json([
                'html' => '',
                'message' => __('Потрібні таблиці бази даних недоступні.'),
            ], 404);
        }

        if (! Schema::hasTable('question_tag')) {
            return response()->json([
                'html' => '',
                'message' => __('Потрібні таблиці бази даних недоступні.'),
            ], 404);
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            return response()->json([
                'html' => '',
                'message' => __('Запис сидера не знайдено.'),
            ], 404);
        }

        if ($question->seeder !== $seedRun->class_name) {
            return response()->json([
                'html' => '',
                'message' => __('Питання не належить до вибраного сидера.'),
            ], 404);
        }

        $question->loadMissing(['tags']);

        $tags = $question->tags
            ->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'category' => $tag->category,
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $html = view('seed-runs.partials.question-tags', [
            'tags' => $tags,
        ])->render();

        return response()->json(['html' => $html]);
    }

    protected function findNodeByPath(Collection $nodes, string $path): ?array
    {
        foreach ($nodes as $node) {
            if (($node['path'] ?? null) === $path) {
                return $node;
            }

            if (($node['type'] ?? null) === 'folder') {
                $children = collect($node['children'] ?? []);
                $match = $this->findNodeByPath($children, $path);

                if ($match) {
                    return $match;
                }
            }
        }

        return null;
    }

    protected function buildCategorySummaries(string $className): Collection
    {
        $questions = Question::query()
            ->with(['category:id,name', 'source:id,name'])
            ->where('seeder', $className)
            ->orderBy('id')
            ->get(['id', 'category_id', 'source_id']);

        return $questions
            ->groupBy(function (Question $question) {
                return optional($question->category)->name ?? __('Без категорії');
            })
            ->map(function (Collection $categoryQuestions, string $categoryName) {
                $categoryModel = optional($categoryQuestions->first()->category);
                $categoryId = $categoryModel?->id;
                $categoryKey = $this->makeCategoryKey($categoryId);

                $sources = $categoryQuestions
                    ->groupBy(function (Question $question) {
                        return optional($question->source)->name ?? __('Без джерела');
                    })
                    ->map(function (Collection $sourceQuestions, string $sourceName) {
                        $sourceModel = optional($sourceQuestions->first()->source);
                        $sourceId = $sourceModel?->id;

                        return [
                            'key' => $this->makeSourceKey($sourceId),
                            'source' => $sourceModel ? [
                                'id' => $sourceModel->id,
                                'name' => $sourceModel->name,
                            ] : null,
                            'display_name' => $sourceName,
                            'question_count' => $sourceQuestions->count(),
                        ];
                    })
                    ->values();

                return [
                    'key' => $categoryKey,
                    'category' => $categoryModel ? [
                        'id' => $categoryModel->id,
                        'name' => $categoryModel->name,
                    ] : null,
                    'display_name' => $categoryName,
                    'question_count' => $categoryQuestions->count(),
                    'sources' => $sources,
                ];
            })
            ->values();
    }

    protected function makeCategoryKey(?int $categoryId): string
    {
        return $categoryId ? 'id-' . $categoryId : 'null';
    }

    protected function makeSourceKey(?int $sourceId): string
    {
        return $sourceId ? 'id-' . $sourceId : 'null';
    }

    protected function parseCategoryKey(string $categoryKey): ?int
    {
        if ($categoryKey === 'null') {
            return null;
        }

        if (Str::startsWith($categoryKey, 'id-')) {
            return (int) Str::after($categoryKey, 'id-');
        }

        throw new \InvalidArgumentException('Invalid category key provided.');
    }

    protected function parseSourceKey(string $sourceKey): ?int
    {
        if ($sourceKey === 'null') {
            return null;
        }

        if (Str::startsWith($sourceKey, 'id-')) {
            return (int) Str::after($sourceKey, 'id-');
        }

        throw new \InvalidArgumentException('Invalid source key provided.');
    }

    public function run(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_name' => ['required', 'string'],
        ]);

        $className = $validated['class_name'];

        if (! class_exists($className)) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => __('Seeder :class was not found.', ['class' => $className])]);
        }

        if (! $this->isInstantiableSeeder($className)) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => __('Seeder :class cannot be executed.', ['class' => $className])]);
        }

        try {
            Artisan::call('db:seed', ['--class' => $className]);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => $exception->getMessage()]);
        }

        return redirect()
            ->route('seed-runs.index')
            ->with('status', __('Seeder :class executed successfully.', ['class' => $className]));
    }

    public function runMissing(): RedirectResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => __('The seed_runs table does not exist.')]);
        }

        $executedClasses = DB::table('seed_runs')
            ->pluck('class_name')
            ->all();

        $pendingSeeders = collect($this->discoverSeederClasses(database_path('seeders')))
            ->reject(fn (string $class) => in_array($class, $executedClasses, true))
            ->values();

        $ran = collect();
        $errors = collect();

        foreach ($pendingSeeders as $className) {
            if (! class_exists($className)) {
                $errors->push(__('Seeder :class is not autoloadable.', ['class' => $className]));
                continue;
            }

            if (! $this->isInstantiableSeeder($className)) {
                $errors->push(__('Seeder :class cannot be executed.', ['class' => $className]));
                continue;
            }

            try {
                Artisan::call('db:seed', ['--class' => $className]);
                $ran->push($className);
            } catch (\Throwable $exception) {
                report($exception);
                $errors->push($exception->getMessage());
            }
        }

        $redirect = redirect()->route('seed-runs.index');

        if ($ran->isNotEmpty()) {
            $redirect = $redirect->with('status', __('Executed :count seeder(s): :classes', [
                'count' => $ran->count(),
                'classes' => $ran->implode(', '),
            ]));
        }

        if ($errors->isNotEmpty()) {
            $redirect = $redirect->withErrors(['run' => $errors->implode(' ')]);
        }

        return $redirect;
    }

    public function destroy(int $seedRunId): RedirectResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => __('The seed_runs table does not exist.')]);
        }

        $deleted = DB::table('seed_runs')->where('id', $seedRunId)->delete();

        if (! $deleted) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => __('Seed run record was not found.')]);
        }

        return redirect()
            ->route('seed-runs.index')
            ->with('status', __('Seed run entry removed.'));
    }

    public function destroyWithQuestions(int $seedRunId): RedirectResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => __('The seed_runs table does not exist.')]);
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => __('Seed run record was not found.')]);
        }

        $deletedQuestions = 0;
        $deletedBlocks = 0;
        $deletedPages = 0;
        $profile = $this->describeSeederData($seedRun->class_name);

        DB::transaction(function () use ($seedRun, &$deletedQuestions, &$deletedBlocks, &$deletedPages, $profile) {
            $classNames = collect([$seedRun->class_name]);

            if ($profile['type'] === 'questions') {
                $deletedQuestions = $this->deleteQuestionsForSeeders($classNames);
            } elseif ($profile['type'] === 'pages') {
                $pageResult = $this->deletePageContentForSeeders($classNames);
                $deletedBlocks = $pageResult['blocks'];
                $deletedPages = $pageResult['pages_deleted'];
            } else {
                $deletedQuestions = $this->deleteQuestionsForSeeders($classNames);
                $pageResult = $this->deletePageContentForSeeders($classNames);
                $deletedBlocks = $pageResult['blocks'];
                $deletedPages = $pageResult['pages_deleted'];
            }

            DB::table('seed_runs')->where('id', $seedRun->id)->delete();
        });

        $status = match ($profile['type']) {
            'pages' => __('Removed seeder :class and deleted :blocks related text block(s).', [
                'class' => $seedRun->class_name,
                'blocks' => $deletedBlocks,
            ]) . ($deletedPages > 0
                ? ' ' . __('Deleted :count related page record(s).', ['count' => $deletedPages])
                : ''),
            'questions' => __('Removed seeder :class and deleted :count related question(s).', [
                'class' => $seedRun->class_name,
                'count' => $deletedQuestions,
            ]),
            default => __('Removed seeder :class entry and cleaned related data.', [
                'class' => $seedRun->class_name,
            ]),
        };

        return redirect()
            ->route('seed-runs.index')
            ->with('status', $status);
    }

    public function destroyFolder(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => __('The seed_runs table does not exist.')]);
        }

        $validated = $request->validate([
            'seed_run_ids' => ['required', 'array', 'min:1'],
            'seed_run_ids.*' => ['integer', 'distinct'],
            'folder_label' => ['nullable', 'string'],
        ]);

        $seedRunIds = collect($validated['seed_run_ids'])->filter()->unique()->values();

        if ($seedRunIds->isEmpty()) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => __('No seed runs were selected.')]);
        }

        $seedRuns = DB::table('seed_runs')
            ->whereIn('id', $seedRunIds)
            ->get();

        if ($seedRuns->isEmpty()) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => __('Seed run records were not found.')]);
        }

        DB::table('seed_runs')
            ->whereIn('id', $seedRuns->pluck('id'))
            ->delete();

        $folderLabel = $this->resolveFolderLabel($validated['folder_label'] ?? null);

        return redirect()
            ->route('seed-runs.index')
            ->with('status', __('Removed :count seed run entries from folder :folder.', [
                'count' => $seedRuns->count(),
                'folder' => $folderLabel,
            ]));
    }

    public function destroyFolderWithQuestions(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => __('The seed_runs table does not exist.')]);
        }

        $validated = $request->validate([
            'seed_run_ids' => ['required', 'array', 'min:1'],
            'seed_run_ids.*' => ['integer', 'distinct'],
            'folder_label' => ['nullable', 'string'],
        ]);

        $seedRunIds = collect($validated['seed_run_ids'])->filter()->unique()->values();

        if ($seedRunIds->isEmpty()) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => __('No seed runs were selected.')]);
        }

        $seedRuns = DB::table('seed_runs')
            ->whereIn('id', $seedRunIds)
            ->get();

        if ($seedRuns->isEmpty()) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => __('Seed run records were not found.')]);
        }

        $folderLabel = $this->resolveFolderLabel($validated['folder_label'] ?? null);
        $classNames = $seedRuns->pluck('class_name')->filter()->unique()->values();
        $seedRunIdsToDelete = $seedRuns->pluck('id');
        $typeMap = $classNames->mapWithKeys(function ($className) {
            $profile = $this->describeSeederData($className);

            return [$className => $profile['type']];
        });

        $questionClasses = $typeMap->filter(fn ($type) => $type === 'questions')->keys()->values();
        $pageClasses = $typeMap->filter(fn ($type) => $type === 'pages')->keys()->values();
        $unknownClasses = $typeMap->filter(fn ($type) => ! in_array($type, ['questions', 'pages'], true))->keys()->values();

        $deletedQuestions = 0;
        $deletedBlocks = 0;
        $deletedPages = 0;

        DB::transaction(function () use (
            $seedRunIdsToDelete,
            $questionClasses,
            $pageClasses,
            $unknownClasses,
            &$deletedQuestions,
            &$deletedBlocks,
            &$deletedPages
        ) {
            if ($questionClasses->isNotEmpty()) {
                $deletedQuestions += $this->deleteQuestionsForSeeders($questionClasses);
            }

            if ($pageClasses->isNotEmpty()) {
                $pageResult = $this->deletePageContentForSeeders($pageClasses);
                $deletedBlocks += $pageResult['blocks'];
                $deletedPages += $pageResult['pages_deleted'];
            }

            if ($unknownClasses->isNotEmpty()) {
                $deletedQuestions += $this->deleteQuestionsForSeeders($unknownClasses);
                $pageResult = $this->deletePageContentForSeeders($unknownClasses);
                $deletedBlocks += $pageResult['blocks'];
                $deletedPages += $pageResult['pages_deleted'];
            }

            DB::table('seed_runs')
                ->whereIn('id', $seedRunIdsToDelete)
                ->delete();
        });

        $statusMessage = __('Removed :count seed run entries from folder :folder.', [
            'count' => $seedRuns->count(),
            'folder' => $folderLabel,
        ]);

        if ($deletedQuestions > 0) {
            $statusMessage .= ' ' . __('Deleted :count related question(s).', ['count' => $deletedQuestions]);
        }

        if ($deletedBlocks > 0) {
            $statusMessage .= ' ' . __('Deleted :count related text block(s).', ['count' => $deletedBlocks]);
        }

        if ($deletedPages > 0) {
            $statusMessage .= ' ' . __('Deleted :count related page record(s).', ['count' => $deletedPages]);
        }

        return redirect()
            ->route('seed-runs.index')
            ->with('status', $statusMessage);
    }

    public function destroyQuestion(Request $request, Question $question): JsonResponse|RedirectResponse
    {
        $questionId = $question->id;
        $seederName = $question->seeder;
        $successMessage = __('Питання №:id з сидера :seeder успішно видалено.', [
            'id' => $questionId,
            'seeder' => $seederName ?? __('невідомий сидер'),
        ]);

        try {
            DB::transaction(function () use ($question) {
                $this->questionDeletionService->deleteQuestion($question);
            });
        } catch (\Throwable $exception) {
            report($exception);

            $errorMessage = __('Не вдалося видалити питання. Спробуйте пізніше.');

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $errorMessage,
                ], 500);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $successMessage,
                'question_id' => $questionId,
                'seeder' => $seederName,
            ]);
        }

        return redirect()
            ->route('seed-runs.index')
            ->with('status', $successMessage);
    }

    protected function describeSeederData(string $className): array
    {
        $default = [
            'type' => 'unknown',
            'delete_button' => __('Видалити з даними'),
            'delete_confirm' => __('Видалити лог та пов’язані дані?'),
            'folder_delete_button' => __('Видалити з даними'),
            'folder_delete_confirm' => __('Видалити всі сидери в папці «:folder» та пов’язані дані?'),
        ];

        if (! class_exists($className)) {
            return $default;
        }

        if (is_subclass_of($className, QuestionSeederBase::class)) {
            return [
                'type' => 'questions',
                'delete_button' => __('Видалити з питаннями'),
                'delete_confirm' => __('Видалити лог та пов’язані питання?'),
                'folder_delete_button' => __('Видалити з питаннями'),
                'folder_delete_confirm' => __('Видалити всі сидери в папці «:folder» разом із питаннями?'),
            ];
        }

        if ($this->classProvidesGrammarPages($className)) {
            return [
                'type' => 'pages',
                'delete_button' => __('Видалити зі сторінками'),
                'delete_confirm' => __('Видалити лог та пов’язані сторінки й блоки?'),
                'folder_delete_button' => __('Видалити зі сторінками'),
                'folder_delete_confirm' => __('Видалити всі сидери в папці «:folder» разом із сторінками та блоками?'),
            ];
        }

        return $default;
    }

    protected function describeFolderData(Collection $classNames): array
    {
        $default = [
            'type' => 'unknown',
            'delete_button' => __('Видалити з даними'),
            'delete_confirm' => __('Видалити всі сидери в папці «:folder» та пов’язані дані?'),
        ];

        if ($classNames->isEmpty()) {
            return $default;
        }

        $profiles = $classNames->map(fn ($class) => $this->describeSeederData($class));
        $types = $profiles->pluck('type')->filter()->unique();

        if ($types->count() === 1) {
            $type = $types->first();
            $profile = $profiles->firstWhere('type', $type);

            if ($profile) {
                return [
                    'type' => $type,
                    'delete_button' => $profile['folder_delete_button'],
                    'delete_confirm' => $profile['folder_delete_confirm'],
                ];
            }
        }

        return $default;
    }

    protected function deleteQuestionsForSeeders(Collection $classNames): int
    {
        if ($classNames->isEmpty() || ! Schema::hasColumn('questions', 'seeder')) {
            return 0;
        }

        $deleted = 0;

        Question::query()
            ->whereIn('seeder', $classNames)
            ->orderBy('id')
            ->chunkById(100, function ($questions) use (&$deleted) {
                foreach ($questions as $question) {
                    $this->questionDeletionService->deleteQuestion($question);
                    $deleted++;
                }
            });

        return $deleted;
    }

    protected function deletePageContentForSeeders(Collection $classNames): array
    {
        $hasTextBlockTable = Schema::hasTable('text_blocks');
        $hasPagesTable = Schema::hasTable('pages');

        if ($classNames->isEmpty() || (! $hasTextBlockTable && ! $hasPagesTable)) {
            return ['blocks' => 0, 'pages_deleted' => 0];
        }

        $classNames = $this->expandGrammarPageSeederClasses($classNames);

        $deletedBlocks = 0;
        $deletedPages = 0;
        $processedPageIds = collect();

        foreach ($classNames as $className) {
            if ($hasTextBlockTable) {
                TextBlock::query()
                    ->where('seeder', $className)
                    ->orderBy('id')
                    ->chunkById(100, function ($blocks) use (&$deletedBlocks) {
                        foreach ($blocks as $block) {
                            $block->delete();
                            $deletedBlocks++;
                        }
                    });
            }

            if (! $hasPagesTable) {
                continue;
            }

            $pages = Page::query()
                ->where('seeder', $className)
                ->get();

            if ($pages->isEmpty()) {
                $slug = $this->resolvePageSlugForSeeder($className);

                if ($slug !== null) {
                    $page = Page::query()->where('slug', $slug)->first();

                    if ($page) {
                        $pages = collect([$page]);
                    }
                }
            }

            foreach ($pages as $page) {
                if ($processedPageIds->contains($page->id)) {
                    continue;
                }

                $processedPageIds->push($page->id);

                if ($hasTextBlockTable) {
                    $deletedBlocks += TextBlock::query()
                        ->where('page_id', $page->id)
                        ->delete();
                }

                $page->delete();
                $deletedPages++;
            }
        }

        return [
            'blocks' => $deletedBlocks,
            'pages_deleted' => $deletedPages,
        ];
    }

    protected function expandGrammarPageSeederClasses(Collection $classNames): Collection
    {
        return $classNames
            ->flatMap(function ($className) {
                $classes = collect([$className]);

                if (! class_exists($className)) {
                    return $classes;
                }

                if (is_subclass_of($className, GrammarPageSeederBase::class)) {
                    return $classes;
                }

                $aggregateClasses = $this->resolveAggregateSeederClasses($className);

                if ($aggregateClasses->isEmpty()) {
                    return $classes;
                }

                return $classes->merge($aggregateClasses);
            })
            ->filter(fn ($class) => is_string($class) && $class !== '')
            ->unique()
            ->values();
    }

    protected function resolvePageSlugForSeeder(string $className): ?string
    {
        if (! class_exists($className)) {
            return null;
        }

        try {
            $reflection = new \ReflectionClass($className);

            if ($reflection->isAbstract()) {
                return null;
            }

            if (! $reflection->isSubclassOf(GrammarPageSeederBase::class)) {
                return null;
            }

            $instance = app()->make($className);

            if (! method_exists($instance, 'slug')) {
                return null;
            }

            $slug = $instance->slug();

            return is_string($slug) ? $slug : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function resolveAggregateSeederClasses(string $className): Collection
    {
        if (! class_exists($className)) {
            return collect();
        }

        try {
            $reflection = new \ReflectionClass($className);

            $constant = collect($reflection->getReflectionConstants())
                ->firstWhere(fn (\ReflectionClassConstant $constant) => $constant->getName() === 'SEEDERS');

            if (! $constant) {
                return collect();
            }

            $value = $constant->getValue();

            if (! is_array($value)) {
                return collect();
            }

            return collect($value)
                ->filter(fn ($class) => is_string($class) && $class !== '');
        } catch (\Throwable) {
            return collect();
        }
    }

    protected function classProvidesGrammarPages(string $className): bool
    {
        if (! class_exists($className)) {
            return false;
        }

        if (is_subclass_of($className, GrammarPageSeederBase::class)) {
            return true;
        }

        return $this->resolveAggregateSeederClasses($className)
            ->contains(fn ($class) => is_string($class) && class_exists($class) && is_subclass_of($class, GrammarPageSeederBase::class));
    }

    /**
     * @return array<int, string>
     */
    private function discoverSeederClasses(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        return collect(File::allFiles($directory))
            ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
            ->map(fn (SplFileInfo $file) => $this->classFromFile($file))
            ->filter(fn (?string $class) => $class && $this->isInstantiableSeeder($class))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function classFromFile(SplFileInfo $file): ?string
    {
        $contents = File::get($file->getPathname());

        if ($contents === false) {
            return null;
        }

        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $namespaceMatch)) {
            return null;
        }

        if (! preg_match('/^\s*(?:final\s+)?(?:abstract\s+)?class\s+(\w+)/mi', $contents, $classMatch)) {
            return null;
        }

        $namespace = trim($namespaceMatch[1]);
        $className = trim($classMatch[1]);

        if ($namespace === '' || $className === '') {
            return null;
        }

        return $namespace . '\\' . $className;
    }

    private function isInstantiableSeeder(string $class): bool
    {
        try {
            $reflection = new \ReflectionClass($class);

            if (! $reflection->isInstantiable()) {
                return false;
            }

            return $reflection->isSubclassOf(\Illuminate\Database\Seeder::class);
        } catch (\ReflectionException) {
            return false;
        }
    }

    private function resolveFolderLabel(?string $label): string
    {
        $label = trim((string) $label);

        return $label !== '' ? $label : __('selected folder');
    }
}
