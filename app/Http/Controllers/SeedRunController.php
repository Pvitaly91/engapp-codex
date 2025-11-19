<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\TextBlock;
use App\Services\QuestionDeletionService;
use Database\Seeders\Pages\Concerns\GrammarPageSeeder as GrammarPageSeederBase;
use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder as PageCategoryDescriptionSeederBase;
use Database\Seeders\QuestionSeeder as QuestionSeederBase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder as LaravelSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class SeedRunController extends Controller
{
    /**
     * @var array<string, string>|null
     */
    private ?array $seederClassMap = null;

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

    public function preview(Request $request)
    {
        $className = (string) $request->query('class_name', '');

        if ($className === '') {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['preview' => __('Не вказано клас сидера для перегляду.')]);
        }

        if (! $this->ensureSeederClassIsLoaded($className)) {
            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['preview' => __('Сидер :class не знайдено.', ['class' => $className])]);
        }

        try {
            $preview = $this->buildSeederPreview($className);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['preview' => __('Не вдалося підготувати попередній перегляд: :message', ['message' => $exception->getMessage()])]);
        }

        return view('seed-runs.preview', [
            'className' => $className,
            'displayClassName' => $this->formatSeederClassName($className),
            'preview' => $preview,
        ]);
    }

    public function showSeederFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_name' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Надано некоректні дані для завантаження файлу сидера.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $className = (string) $validator->validated()['class_name'];
        $filePath = $this->resolveSeederFilePath($className);

        if (! $filePath || ! File::exists($filePath)) {
            return response()->json([
                'message' => __('Файл для сидера :class не знайдено.', ['class' => $className]),
            ], 404);
        }

        if (! is_readable($filePath)) {
            return response()->json([
                'message' => __('Файл сидера :class недоступний для читання.', ['class' => $className]),
            ], 403);
        }

        try {
            $contents = File::get($filePath);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => __('Не вдалося прочитати файл сидера :class.', ['class' => $className]),
            ], 500);
        }

        $lastModified = null;

        try {
            $timestamp = File::lastModified($filePath);

            if ($timestamp) {
                $lastModified = Carbon::createFromTimestamp($timestamp)->toDateTimeString();
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'class_name' => $className,
            'display_class_name' => $this->formatSeederClassName($className),
            'path' => $this->makeSeederFileDisplayPath($filePath),
            'contents' => $contents,
            'last_modified' => $lastModified,
        ]);
    }

    public function updateSeederFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_name' => ['required', 'string'],
            'contents' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Неможливо зберегти файл сидера через помилки валідації.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $className = (string) $validated['class_name'];
        $contents = (string) $validated['contents'];
        $filePath = $this->resolveSeederFilePath($className);

        if (! $filePath || ! File::exists($filePath)) {
            return response()->json([
                'message' => __('Файл для сидера :class не знайдено.', ['class' => $className]),
            ], 404);
        }

        if (! File::isWritable($filePath)) {
            return response()->json([
                'message' => __('Файл сидера :class доступний лише для читання.', ['class' => $className]),
            ], 403);
        }

        try {
            File::put($filePath, $contents, true);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => __('Не вдалося зберегти файл сидера :class.', ['class' => $className]),
            ], 500);
        }

        clearstatcache(true, $filePath);

        $lastModified = null;

        try {
            $timestamp = File::lastModified($filePath);

            if ($timestamp) {
                $lastModified = Carbon::createFromTimestamp($timestamp)->toDateTimeString();
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        $freshContents = $contents;

        try {
            $freshContents = File::get($filePath);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'message' => __('Файл сидера успішно збережено.'),
            'path' => $this->makeSeederFileDisplayPath($filePath),
            'last_modified' => $lastModified,
            'contents' => $freshContents,
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
            ->map(function (string $class) {
                $displayName = $this->formatSeederClassName($class);
                [$namespace, $baseName] = $this->splitSeederDisplayName($displayName);

                return (object) [
                    'class_name' => $class,
                    'display_class_name' => $displayName,
                    'display_class_namespace' => $namespace,
                    'display_class_basename' => $baseName,
                    'supports_preview' => $this->seederSupportsPreview($class),
                ];
            })
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

    /**
     * @return array{0: string|null, 1: string}
     */
    protected function splitSeederDisplayName(string $displayName): array
    {
        if (! Str::contains($displayName, '\\')) {
            return [null, $displayName];
        }

        return [
            Str::beforeLast($displayName, '\\'),
            Str::afterLast($displayName, '\\'),
        ];
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

    protected function seederSupportsPreview(string $className): bool
    {
        if (! $this->ensureSeederClassIsLoaded($className)) {
            return false;
        }

        if (! is_subclass_of($className, LaravelSeeder::class)) {
            return false;
        }

        return method_exists($className, 'run');
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

    protected function buildSeederPreview(string $className): array
    {
        if (is_subclass_of($className, QuestionSeederBase::class)) {
            return $this->buildQuestionSeederPreview($className);
        }

        if (is_subclass_of($className, GrammarPageSeederBase::class)) {
            return $this->buildPageSeederPreview($className);
        }

        if (is_subclass_of($className, PageCategoryDescriptionSeederBase::class)) {
            return $this->buildCategorySeederPreview($className);
        }

        throw new \RuntimeException(__('Сидер :class не підтримує попередній перегляд.', ['class' => $className]));
    }

    protected function buildQuestionSeederPreview(string $className): array
    {
        $hasSeederColumn = Schema::hasColumn('questions', 'seeder');
        $keyColumn = Schema::hasColumn('questions', 'uuid') ? 'uuid' : 'id';

        $existingQuestionKeys = Question::query()
            ->pluck($keyColumn)
            ->filter(fn ($value) => $value !== null)
            ->values()
            ->all();

        if (empty($existingQuestionKeys) && $keyColumn === 'uuid') {
            $keyColumn = 'id';
            $existingQuestionKeys = Question::query()->pluck($keyColumn)->all();
        }

        $existingQuestionCount = $hasSeederColumn
            ? Question::query()->where('seeder', $className)->count()
            : null;

        $previewQuestions = collect();

        $relations = [
            'answers.option',
            'options',
            'tags',
            'category',
            'source',
            'verbHints.option',
            'variants',
            'hints',
            'chatgptExplanations',
        ];

        DB::beginTransaction();

        try {
            $seeder = app($className);

            if (! method_exists($seeder, 'run')) {
                throw new \RuntimeException(__('Сидер :class не підтримує попередній перегляд.', ['class' => $className]));
            }

            $seeder->run();

            $questions = Question::query()
                ->with($relations)
                ->when(! empty($existingQuestionKeys), function ($query) use ($keyColumn, $existingQuestionKeys) {
                    $query->whereNotIn($keyColumn, $existingQuestionKeys);
                })
                ->orderBy('id')
                ->get();

            if ($questions->isEmpty() && $hasSeederColumn) {
                $questions = Question::query()
                    ->with($relations)
                    ->where('seeder', $className)
                    ->orderBy('id')
                    ->get();
            }

            if ($questions->isEmpty()) {
                throw new \RuntimeException(__('Сидер не створює нові питання для попереднього перегляду.'));
            }

            $previewQuestions = $questions
                ->map(function (Question $question) {
                    $question->answers = $question->answers->sortBy('marker')->values();

                    return [
                        'uuid' => $question->uuid,
                        'highlighted_text' => $this->renderQuestionWithHighlightedAnswers($question),
                        'raw_text' => $question->question,
                        'category' => optional($question->category)->name,
                        'source' => optional($question->source)->name,
                        'difficulty' => $question->difficulty,
                        'level' => $question->level,
                        'flag' => $question->flag,
                        'tags' => $question->tags
                            ->map(fn ($tag) => [
                                'id' => $tag->id,
                                'name' => $tag->name,
                                'category' => $tag->category,
                            ])
                            ->values(),
                        'answers' => $question->answers
                            ->map(fn ($answer) => [
                                'marker' => $answer->marker,
                                'label' => optional($answer->option)->option ?? $answer->answer,
                            ])
                            ->values(),
                        'options' => $question->options
                            ->map(fn ($option) => $option->option)
                            ->values(),
                        'verb_hints' => $question->verbHints
                            ->map(fn ($hint) => [
                                'marker' => $hint->marker,
                                'label' => optional($hint->option)->option,
                            ])
                            ->filter(fn ($hint) => filled($hint['label']))
                            ->values(),
                        'variants' => $question->variants
                            ->pluck('text')
                            ->filter()
                            ->values(),
                        'hints' => $question->hints
                            ->map(fn (QuestionHint $hint) => [
                                'provider' => $hint->provider,
                                'locale' => $hint->locale,
                                'text' => $hint->hint,
                            ])
                            ->values(),
                        'explanations' => $question->chatgptExplanations
                            ->map(fn ($explanation) => [
                                'wrong_answer' => $explanation->wrong_answer,
                                'text' => $explanation->explanation,
                            ])
                            ->values(),
                    ];
                })
                ->values();

            // Collect all unique tags from the preview questions
            $allTags = $questions
                ->flatMap(fn (Question $question) => $question->tags)
                ->unique(function ($tag) {
                    // Use ID if available, otherwise use name and category for uniqueness
                    return $tag->id ?? ($tag->name . ':::' . ($tag->category ?? ''));
                })
                ->values();

            // Get existing tags from database to compare
            $existingTagNames = collect();
            if (Schema::hasTable('tags')) {
                $existingTagNames = \App\Models\Tag::query()
                    ->pluck('name')
                    ->flip();
            }

            // Categorize tags as new or existing
            $tagsSummary = $allTags
                ->map(function ($tag) use ($existingTagNames) {
                    $isExisting = $existingTagNames->has($tag->name);
                    
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'category' => $tag->category,
                        'is_new' => !$isExisting,
                    ];
                })
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        } finally {
            DB::rollBack();
        }

        return [
            'type' => 'questions',
            'questions' => $previewQuestions,
            'existingQuestionCount' => $existingQuestionCount,
            'tagsSummary' => $tagsSummary,
        ];
    }

    protected function buildPageSeederPreview(string $className): array
    {
        DB::beginTransaction();

        try {
            $seeder = app($className);

            if (! method_exists($seeder, 'run')) {
                throw new \RuntimeException(__('Сидер :class не підтримує попередній перегляд.', ['class' => $className]));
            }

            $seeder->run();

            $page = Page::query()
                ->with(['textBlocks', 'category'])
                ->where('seeder', $className)
                ->first();

            if (! $page) {
                throw new \RuntimeException(__('Сидер не створює сторінку для попереднього перегляду.'));
            }

            $textBlocks = $page->textBlocks;
            $subtitleBlock = $textBlocks->firstWhere(fn (TextBlock $block) => $block->type === 'subtitle');

            $columns = [
                'left' => $textBlocks->filter(fn (TextBlock $block) => $block->column === 'left'),
                'right' => $textBlocks->filter(fn (TextBlock $block) => $block->column === 'right'),
            ];

            $locale = $subtitleBlock->locale
                ?? ($textBlocks->first()?->locale)
                ?? 'uk';

            $categories = PageCategory::query()
                ->withCount('pages')
                ->orderBy('title')
                ->get();

            $selectedCategory = $page->category;

            $categoryPages = collect();

            if ($selectedCategory) {
                $categoryPages = Page::query()
                    ->where('page_category_id', $selectedCategory->getKey())
                    ->where('id', '!=', $page->getKey())
                    ->inRandomOrder()
                    ->limit(3)
                    ->get();
            }

            if ($categoryPages->isEmpty()) {
                $categoryPages = collect([$page]);
            }

            $breadcrumbs = [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Теорія', 'url' => route('pages.index')],
            ];

            if ($selectedCategory) {
                $breadcrumbs[] = [
                    'label' => $selectedCategory->title,
                    'url' => route('pages.category', $selectedCategory->slug),
                ];
            }

            $breadcrumbs[] = ['label' => $page->title];

            $pageViewData = [
                'page' => $page,
                'breadcrumbs' => $breadcrumbs,
                'subtitleBlock' => $subtitleBlock,
                'columns' => $columns,
                'locale' => $locale,
                'categories' => $categories,
                'selectedCategory' => $selectedCategory,
                'categoryPages' => $categoryPages,
            ];

            $pageHtml = view('engram.pages.show', $pageViewData)->render();

            $pageMeta = [
                'title' => $page->title,
                'slug' => $page->slug,
                'category_title' => optional($selectedCategory)->title,
                'category_slug' => optional($selectedCategory)->slug,
                'locale' => $locale,
                'text_block_count' => $textBlocks->count(),
                'html' => $pageHtml,
            ];
        } finally {
            DB::rollBack();
        }

        return [
            'type' => 'page',
            'questions' => collect(),
            'existingQuestionCount' => null,
            'page' => $pageMeta,
        ];
    }

    protected function buildCategorySeederPreview(string $className): array
    {
        DB::beginTransaction();

        try {
            $seeder = app($className);

            if (! method_exists($seeder, 'run')) {
                throw new \RuntimeException(__('Сидер :class не підтримує попередній перегляд.', ['class' => $className]));
            }

            if (! $seeder instanceof PageCategoryDescriptionSeederBase) {
                throw new \RuntimeException(__('Сидер :class не підтримує попередній перегляд категорії.', ['class' => $className]));
            }

            $seeder->run();

            $category = PageCategory::query()
                ->with(['textBlocks', 'pages' => fn ($query) => $query->orderBy('title')])
                ->where('slug', $seeder->previewCategorySlug())
                ->first();

            if (! $category) {
                throw new \RuntimeException(__('Сидер не створює опис категорії для попереднього перегляду.'));
            }

            $blocks = $category->textBlocks ?? collect();
            $subtitleBlock = $blocks->firstWhere(fn (TextBlock $block) => $block->type === 'subtitle');
            $columns = [
                'left' => $blocks->filter(fn (TextBlock $block) => $block->column === 'left'),
                'right' => $blocks->filter(fn (TextBlock $block) => $block->column === 'right'),
            ];

            $locale = $subtitleBlock->locale
                ?? ($blocks->first()?->locale)
                ?? app()->getLocale()
                ?? 'uk';

            $categories = PageCategory::query()
                ->withCount('pages')
                ->orderBy('title')
                ->get();

            $categoryPages = $category->pages ?? collect();

            $categoryDescription = [
                'blocks' => $blocks,
                'subtitleBlock' => $subtitleBlock,
                'columns' => $columns,
                'locale' => $locale,
                'hasBlocks' => $blocks->isNotEmpty(),
            ];

            $viewData = [
                'categories' => $categories,
                'selectedCategory' => $category,
                'categoryPages' => $categoryPages,
                'categoryDescription' => $categoryDescription,
            ];

            $categoryHtml = view('engram.pages.index', $viewData)->render();

            $categoryMeta = [
                'title' => $category->title,
                'slug' => $category->slug,
                'locale' => $locale,
                'page_count' => $categoryPages->count(),
                'text_block_count' => $blocks->count(),
                'url' => route('pages.category', $category->slug),
                'html' => $categoryHtml,
            ];
        } finally {
            DB::rollBack();
        }

        return [
            'type' => 'category',
            'questions' => collect(),
            'existingQuestionCount' => null,
            'category' => $categoryMeta,
        ];
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

    public function run(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'class_name' => ['required', 'string'],
        ]);

        $className = $validated['class_name'];

        if (! $this->ensureSeederClassIsLoaded($className)) {
            $message = __('Seeder :class was not found.', ['class' => $className]);
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => $message]);
        }

        $filePath = $this->resolveSeederFilePath($className);

        if (! $this->isInstantiableSeeder($className, $filePath)) {
            $message = __('Seeder :class cannot be executed.', ['class' => $className]);
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => $message]);
        }

        try {
            Artisan::call('db:seed', ['--class' => $className]);
        } catch (\Throwable $exception) {
            report($exception);

            if ($request->wantsJson()) {
                return response()->json(['message' => $exception->getMessage()], 500);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => $exception->getMessage()]);
        }

        $message = __('Seeder :class executed successfully.', ['class' => $className]);
        
        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview();
            return response()->json([
                'message' => $message,
                'seeder_moved' => true,
                'class_name' => $className,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        return redirect()
            ->route('seed-runs.index')
            ->with('status', $message);
    }

    public function destroySeederFile(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'class_name' => ['required', 'string'],
            'delete_with_questions' => ['nullable', 'boolean'],
        ]);

        $className = $validated['class_name'];
        $deleteQuestions = $request->boolean('delete_with_questions');
        $result = $this->removeSeederFileAndAssociatedRuns($className, null, $deleteQuestions);

        if (($result['status'] ?? null) === 'success') {
            if ($request->wantsJson()) {
                $overview = $this->assembleSeedRunOverview();
                return response()->json([
                    'message' => $result['message'],
                    'seeder_removed' => true,
                    'class_name' => $className,
                    'runs_deleted' => $result['runs_deleted'] ?? 0,
                    'questions_deleted' => $result['questions_deleted'] ?? 0,
                    'overview' => [
                        'pending_count' => $overview['pendingSeeders']->count(),
                        'executed_count' => $overview['executedSeeders']->count(),
                    ],
                ]);
            }

            return redirect()
                ->route('seed-runs.index')
                ->with('status', $result['message']);
        }

        $errorMessage = $result['message'] ?? __('Не вдалося видалити файл сидера.');
        
        if ($request->wantsJson()) {
            return response()->json(['message' => $errorMessage], 500);
        }

        return redirect()
            ->route('seed-runs.index')
            ->withErrors(['run' => $errorMessage]);
    }

    public function destroySeederFiles(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'class_names' => ['required', 'array', 'min:1'],
            'class_names.*' => ['string'],
            'delete_with_questions' => ['nullable', 'array'],
            'delete_with_questions.*' => ['string'],
        ]);

        $classNames = collect($validated['class_names'] ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        $deleteWithQuestions = collect($validated['delete_with_questions'] ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($classNames->isEmpty()) {
            $errorMessage = __('Будь ласка, оберіть принаймні один сидер для видалення.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 422);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => $errorMessage]);
        }

        $seedRunsTableExists = Schema::hasTable('seed_runs');
        $successCount = 0;
        $totalRunsDeleted = 0;
        $totalQuestionsDeleted = 0;
        $errorMessages = [];

        foreach ($classNames as $className) {
            $shouldDeleteQuestions = $deleteWithQuestions->contains($className);
            $result = $this->removeSeederFileAndAssociatedRuns($className, $seedRunsTableExists, $shouldDeleteQuestions);
            $status = $result['status'] ?? null;

            if ($status === 'success') {
                $successCount++;
                $totalRunsDeleted += (int) ($result['runs_deleted'] ?? 0);
                $totalQuestionsDeleted += (int) ($result['questions_deleted'] ?? 0);

                continue;
            }

            if ($status === 'partial') {
                $successCount++;
                $totalRunsDeleted += (int) ($result['runs_deleted'] ?? 0);
                $totalQuestionsDeleted += (int) ($result['questions_deleted'] ?? 0);
                $errorMessages[] = $result['message'];

                continue;
            }

            if (! empty($result['message'])) {
                $errorMessages[] = $result['message'];
            }
        }

        $statusMessages = [];

        if ($successCount > 0) {
            $statusMessages[] = trans_choice(
                '{1}Успішно видалено :count файл сидера.|[2,4]Успішно видалено :count файли сидерів.|[5,*]Успішно видалено :count файлів сидерів.',
                $successCount,
                ['count' => $successCount]
            );

            if ($totalRunsDeleted > 0) {
                $statusMessages[] = trans_choice(
                    '{1}Також видалено :count пов’язаний запис seed_runs.|[2,4]Також видалено :count пов’язані записи seed_runs.|[5,*]Також видалено :count пов’язаних записів seed_runs.',
                    $totalRunsDeleted,
                    ['count' => $totalRunsDeleted]
                );
            }

            if ($totalQuestionsDeleted > 0) {
                $statusMessages[] = trans_choice(
                    '{1}Видалено :count пов’язане питання.|[2,4]Видалено :count пов’язані питання.|[5,*]Видалено :count пов’язаних питань.',
                    $totalQuestionsDeleted,
                    ['count' => $totalQuestionsDeleted]
                );
            }
        }

        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview();
            return response()->json([
                'message' => implode(' ', $statusMessages),
                'errors' => $errorMessages,
                'success_count' => $successCount,
                'runs_deleted' => $totalRunsDeleted,
                'questions_deleted' => $totalQuestionsDeleted,
                'removed_class_names' => $classNames->all(),
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ], empty($errorMessages) && $successCount > 0 ? 200 : 500);
        }

        $redirect = redirect()->route('seed-runs.index');

        if (! empty($statusMessages)) {
            $redirect = $redirect->with('status', implode(' ', $statusMessages));
        }

        if (! empty($errorMessages)) {
            $redirect = $redirect->withErrors(new MessageBag(['run' => $errorMessages]));
        }

        return $redirect;
    }

    protected function removeSeederFileAndAssociatedRuns(string $className, ?bool $seedRunsTableExists = null, bool $deleteQuestions = false): array
    {
        if (! is_bool($seedRunsTableExists)) {
            $seedRunsTableExists = Schema::hasTable('seed_runs');
        }

        $filePath = $this->resolveSeederFilePath($className);

        if (! $filePath || ! File::exists($filePath)) {
            return [
                'status' => 'missing',
                'class' => $className,
                'message' => __('Файл для сидера :class не знайдено.', ['class' => $className]),
                'runs_deleted' => 0,
                'questions_deleted' => 0,
            ];
        }

        try {
            if (! File::delete($filePath)) {
                return [
                    'status' => 'error',
                    'class' => $className,
                    'message' => __('Не вдалося видалити файл сидера :class.', ['class' => $className]),
                    'runs_deleted' => 0,
                    'questions_deleted' => 0,
                ];
            }
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'status' => 'error',
                'class' => $className,
                'message' => __('Не вдалося видалити файл сидера :class.', ['class' => $className]),
                'runs_deleted' => 0,
                'questions_deleted' => 0,
            ];
        }

        $runsDeleted = 0;
        $questionsDeleted = 0;
        $questionDeletionFailed = false;

        if ($deleteQuestions) {
            try {
                $questionsDeleted = $this->deleteQuestionsForSeeders(collect([$className]));
            } catch (\Throwable $exception) {
                report($exception);
                $questionDeletionFailed = true;
            }
        }

        if ($seedRunsTableExists) {
            try {
                $runsDeleted = DB::table('seed_runs')
                    ->where('class_name', $className)
                    ->delete();
            } catch (\Throwable $exception) {
                report($exception);

                return [
                    'status' => 'partial',
                    'class' => $className,
                    'message' => __('Файл сидера :class видалено, але не вдалося оновити seed_runs. Будь ласка, перевірте журнал.', ['class' => $className]),
                    'runs_deleted' => 0,
                    'questions_deleted' => $questionsDeleted,
                ];
            }
        }

        if ($questionDeletionFailed) {
            return [
                'status' => 'partial',
                'class' => $className,
                'message' => __('Файл сидера :class видалено, але не вдалося видалити пов’язані питання.', ['class' => $className]),
                'runs_deleted' => $runsDeleted,
                'questions_deleted' => $questionsDeleted,
            ];
        }

        $message = __('Файл сидера :class успішно видалено.', ['class' => $className]);

        if ($runsDeleted > 0) {
            $message .= ' ' . __('Пов’язаний запис seed_runs видалено.');
        }

        if ($questionsDeleted > 0) {
            $message .= ' ' . trans_choice(
                '{1}Видалено :count пов’язане питання.|[2,4]Видалено :count пов’язані питання.|[5,*]Видалено :count пов’язаних питань.',
                $questionsDeleted,
                ['count' => $questionsDeleted]
            );
        }

        return [
            'status' => 'success',
            'class' => $className,
            'message' => $message,
            'runs_deleted' => $runsDeleted,
            'questions_deleted' => $questionsDeleted,
        ];
    }

    protected function resolveSeederFilePath(string $className): ?string
    {
        $candidatePaths = collect();

        $map = $this->getSeederClassMap();

        if ($map->has($className)) {
            $candidatePaths->push($map->get($className));
        }

        if (Str::startsWith($className, 'Database\\Seeders\\')) {
            $relative = Str::after($className, 'Database\\Seeders\\');
            $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
            $candidatePaths->push(database_path('seeders/' . $relativePath));
        }

        if (class_exists($className, false) || $this->classExistsSafely($className)) {
            try {
                $reflection = new \ReflectionClass($className);
                $fileName = $reflection->getFileName();

                if ($fileName) {
                    $candidatePaths->push($fileName);
                }
            } catch (\ReflectionException $exception) {
                report($exception);
            }
        }

        return $candidatePaths
            ->filter(fn ($path) => filled($path))
            ->map(fn ($path) => realpath($path) ?: $path)
            ->unique()
            ->first(function ($path) {
                return File::exists($path) && $this->isSeederFilePathAllowed($path);
            });
    }

    protected function isSeederFilePathAllowed(string $path): bool
    {
        $realPath = realpath($path);

        if ($realPath === false) {
            return false;
        }

        $basePath = realpath(base_path());

        if (! $basePath) {
            return false;
        }

        $normalizedBase = rtrim($basePath, DIRECTORY_SEPARATOR);

        if ($realPath === $normalizedBase) {
            return true;
        }

        return Str::startsWith($realPath, $normalizedBase . DIRECTORY_SEPARATOR);
    }

    protected function makeSeederFileDisplayPath(string $filePath): string
    {
        $realPath = realpath($filePath) ?: $filePath;
        $realPath = str_replace('\\', DIRECTORY_SEPARATOR, $realPath);
        $databaseSeedersPath = realpath(database_path('seeders'));

        if ($databaseSeedersPath) {
            $normalizedSeeders = rtrim(str_replace('\\', DIRECTORY_SEPARATOR, $databaseSeedersPath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            if (Str::startsWith($realPath, $normalizedSeeders)) {
                $relative = Str::after($realPath, $normalizedSeeders);

                if ($relative !== $realPath) {
                    return 'database/seeders/' . str_replace(['\\', DIRECTORY_SEPARATOR], '/', $relative);
                }
            }
        }

        $basePath = realpath(base_path());

        if ($basePath) {
            $normalizedBase = rtrim(str_replace('\\', DIRECTORY_SEPARATOR, $basePath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            if (Str::startsWith($realPath, $normalizedBase)) {
                $relative = Str::after($realPath, $normalizedBase);

                if ($relative !== $realPath) {
                    return ltrim(str_replace(['\\', DIRECTORY_SEPARATOR], '/', $relative), '/');
                }
            }
        }

        return basename($realPath);
    }

    public function markAsExecuted(Request $request): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => $errorMessage]);
        }

        $validated = $request->validate([
            'class_name' => ['required', 'string'],
        ]);

        $className = $validated['class_name'];

        if (! $this->ensureSeederClassIsLoaded($className)) {
            $errorMessage = __('Seeder :class was not found.', ['class' => $className]);
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => $errorMessage]);
        }

        try {
            DB::table('seed_runs')->updateOrInsert(
                ['class_name' => $className],
                ['ran_at' => now()]
            );
        } catch (\Throwable $exception) {
            report($exception);

            if ($request->wantsJson()) {
                return response()->json(['message' => $exception->getMessage()], 500);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => $exception->getMessage()]);
        }

        $message = __('Seeder :class marked as executed.', ['class' => $className]);
        
        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview();
            return response()->json([
                'message' => $message,
                'seeder_moved' => true,
                'class_name' => $className,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        return redirect()
            ->route('seed-runs.index')
            ->with('status', $message);
    }

    public function runMissing(Request $request): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['run' => $errorMessage]);
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
            if (! $this->ensureSeederClassIsLoaded($className)) {
                $errors->push(__('Seeder :class is not autoloadable.', ['class' => $className]));
                continue;
            }

            $filePath = $this->resolveSeederFilePath($className);

            if (! $this->isInstantiableSeeder($className, $filePath)) {
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

        $successMessage = null;
        if ($ran->isNotEmpty()) {
            $successMessage = __('Executed :count seeder(s): :classes', [
                'count' => $ran->count(),
                'classes' => $ran->implode(', '),
            ]);
        }

        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview();
            return response()->json([
                'message' => $successMessage ?? __('No seeders were executed.'),
                'errors' => $errors->all(),
                'executed_count' => $ran->count(),
                'executed_classes' => $ran->all(),
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ], $ran->isNotEmpty() ? 200 : 422);
        }

        $redirect = redirect()->route('seed-runs.index');

        if ($successMessage) {
            $redirect = $redirect->with('status', $successMessage);
        }

        if ($errors->isNotEmpty()) {
            $redirect = $redirect->withErrors(['run' => $errors->implode(' ')]);
        }

        return $redirect;
    }

    public function destroy(Request $request, int $seedRunId): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            $errorMessage = __('Seed run record was not found.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
        }

        $className = $seedRun->class_name;
        $deleted = DB::table('seed_runs')->where('id', $seedRunId)->delete();

        if (! $deleted) {
            $errorMessage = __('Seed run record was not found.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
        }

        $message = __('Seed run entry removed.');
        
        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview();
            
            // Check if seeder file still exists and should appear in pending
            $filePath = $this->resolveSeederFilePath($className);
            $fileExists = $filePath && File::exists($filePath);
            
            // Find the pending seeder data if it returns to pending
            $pendingSeederData = null;
            if ($fileExists) {
                $pendingSeederData = $overview['pendingSeeders']
                    ->firstWhere('class_name', $className);
            }
            
            return response()->json([
                'message' => $message,
                'seed_run_id' => $seedRunId,
                'class_name' => $className,
                'returns_to_pending' => $fileExists,
                'pending_seeder' => $pendingSeederData,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        return redirect()
            ->route('seed-runs.index')
            ->with('status', $message);
    }

    public function destroyWithQuestions(Request $request, int $seedRunId): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            $errorMessage = __('Seed run record was not found.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
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

        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview();
            
            // Check if seeder file still exists and should appear in pending
            $filePath = $this->resolveSeederFilePath($seedRun->class_name);
            $fileExists = $filePath && File::exists($filePath);
            
            // Find the pending seeder data if it returns to pending
            $pendingSeederData = null;
            if ($fileExists) {
                $pendingSeederData = $overview['pendingSeeders']
                    ->firstWhere('class_name', $seedRun->class_name);
            }
            
            return response()->json([
                'message' => $status,
                'seed_run_id' => $seedRunId,
                'class_name' => $seedRun->class_name,
                'returns_to_pending' => $fileExists,
                'pending_seeder' => $pendingSeederData,
                'questions_deleted' => $deletedQuestions,
                'blocks_deleted' => $deletedBlocks,
                'pages_deleted' => $deletedPages,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        return redirect()
            ->route('seed-runs.index')
            ->with('status', $status);
    }

    public function refresh(Request $request, int $seedRunId): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['refresh' => $errorMessage]);
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            $errorMessage = __('Seed run record was not found.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['refresh' => $errorMessage]);
        }

        $className = $seedRun->class_name;

        if (! $this->ensureSeederClassIsLoaded($className)) {
            $errorMessage = __('Seeder :class was not found.', ['class' => $className]);
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['refresh' => $errorMessage]);
        }

        $filePath = $this->resolveSeederFilePath($className);

        if (! $this->isInstantiableSeeder($className, $filePath)) {
            $errorMessage = __('Seeder :class cannot be executed.', ['class' => $className]);
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 422);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['refresh' => $errorMessage]);
        }

        $deletedQuestions = 0;
        $deletedBlocks = 0;
        $deletedPages = 0;
        $profile = $this->describeSeederData($seedRun->class_name);

        try {
            DB::transaction(function () use ($seedRun, &$deletedQuestions, &$deletedBlocks, &$deletedPages, $profile, $className) {
                $classNames = collect([$seedRun->class_name]);

                // Delete old data
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

                // Re-run the seeder
                Artisan::call('db:seed', ['--class' => $className]);

                // Update the ran_at timestamp
                DB::table('seed_runs')
                    ->where('id', $seedRun->id)
                    ->update(['ran_at' => now()]);
            });
        } catch (\Throwable $exception) {
            report($exception);

            if ($request->wantsJson()) {
                return response()->json(['message' => $exception->getMessage()], 500);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['refresh' => $exception->getMessage()]);
        }

        $status = match ($profile['type']) {
            'pages' => __('Refreshed seeder :class. Deleted :blocks text block(s) and regenerated content.', [
                'class' => $seedRun->class_name,
                'blocks' => $deletedBlocks,
            ]) . ($deletedPages > 0
                ? ' ' . __('Deleted :count page record(s).', ['count' => $deletedPages])
                : ''),
            'questions' => __('Refreshed seeder :class. Deleted :count question(s) and regenerated them.', [
                'class' => $seedRun->class_name,
                'count' => $deletedQuestions,
            ]),
            default => __('Refreshed seeder :class. Data has been regenerated.', [
                'class' => $seedRun->class_name,
            ]),
        };

        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview();
            
            return response()->json([
                'message' => $status,
                'seed_run_id' => $seedRunId,
                'class_name' => $seedRun->class_name,
                'questions_deleted' => $deletedQuestions,
                'blocks_deleted' => $deletedBlocks,
                'pages_deleted' => $deletedPages,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        return redirect()
            ->route('seed-runs.index')
            ->with('status', $status);
    }

    public function destroyFolder(Request $request): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
        }

        $validated = $request->validate([
            'seed_run_ids' => ['required', 'array', 'min:1'],
            'seed_run_ids.*' => ['integer', 'distinct'],
            'folder_label' => ['nullable', 'string'],
        ]);

        $seedRunIds = collect($validated['seed_run_ids'])->filter()->unique()->values();

        if ($seedRunIds->isEmpty()) {
            $errorMessage = __('No seed runs were selected.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 422);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
        }

        $seedRuns = DB::table('seed_runs')
            ->whereIn('id', $seedRunIds)
            ->get();

        if ($seedRuns->isEmpty()) {
            $errorMessage = __('Seed run records were not found.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
        }

        DB::table('seed_runs')
            ->whereIn('id', $seedRuns->pluck('id'))
            ->delete();

        $folderLabel = $this->resolveFolderLabel($validated['folder_label'] ?? null);
        $message = __('Removed :count seed run entries from folder :folder.', [
            'count' => $seedRuns->count(),
            'folder' => $folderLabel,
        ]);

        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview();
            return response()->json([
                'message' => $message,
                'deleted_count' => $seedRuns->count(),
                'folder_label' => $folderLabel,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        return redirect()
            ->route('seed-runs.index')
            ->with('status', $message);
    }

    public function destroyFolderWithQuestions(Request $request): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
        }

        $validated = $request->validate([
            'seed_run_ids' => ['required', 'array', 'min:1'],
            'seed_run_ids.*' => ['integer', 'distinct'],
            'folder_label' => ['nullable', 'string'],
        ]);

        $seedRunIds = collect($validated['seed_run_ids'])->filter()->unique()->values();

        if ($seedRunIds->isEmpty()) {
            $errorMessage = __('No seed runs were selected.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 422);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
        }

        $seedRuns = DB::table('seed_runs')
            ->whereIn('id', $seedRunIds)
            ->get();

        if ($seedRuns->isEmpty()) {
            $errorMessage = __('Seed run records were not found.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return redirect()
                ->route('seed-runs.index')
                ->withErrors(['delete' => $errorMessage]);
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

        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview();
            return response()->json([
                'message' => $statusMessage,
                'deleted_count' => $seedRuns->count(),
                'questions_deleted' => $deletedQuestions,
                'blocks_deleted' => $deletedBlocks,
                'pages_deleted' => $deletedPages,
                'folder_label' => $folderLabel,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
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

        if (! $this->ensureSeederClassIsLoaded($className)) {
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

                if (! $this->ensureSeederClassIsLoaded($className)) {
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
        if (! $this->ensureSeederClassIsLoaded($className)) {
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
        if (! $this->ensureSeederClassIsLoaded($className)) {
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
        if (! $this->ensureSeederClassIsLoaded($className)) {
            return false;
        }

        if (is_subclass_of($className, GrammarPageSeederBase::class)) {
            return true;
        }

        return $this->resolveAggregateSeederClasses($className)
            ->contains(fn ($class) => is_string($class) && $this->ensureSeederClassIsLoaded($class) && class_exists($class, false) && is_subclass_of($class, GrammarPageSeederBase::class));
    }

    /**
     * @return array<int, string>
     */
    private function discoverSeederClasses(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        return $this->getSeederClassMap()
            ->filter(fn (string $path, string $class) => $this->isInstantiableSeeder($class, $path))
            ->keys()
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

    private function isInstantiableSeeder(string $class, ?string $filePath = null): bool
    {
        try {
            if (! $this->ensureSeederClassIsLoaded($class)) {
                if (! $filePath) {
                    $filePath = $this->getSeederClassMap()->get($class);
                }

                if (! $filePath || ! is_file($filePath)) {
                    return false;
                }

                try {
                    require_once $filePath;
                } catch (\Throwable) {
                    return false;
                }

                if (! class_exists($class, false)) {
                    return false;
                }
            }

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

    protected function classExistsSafely(string $className): bool
    {
        if (class_exists($className, false)) {
            return true;
        }

        return @class_exists($className);
    }

    /**
     * @return Collection<string, string>
     */
    private function getSeederClassMap(): Collection
    {
        if (is_array($this->seederClassMap)) {
            return collect($this->seederClassMap);
        }

        $directory = database_path('seeders');

        if (! is_dir($directory)) {
            $this->seederClassMap = [];

            return collect();
        }

        $map = collect(File::allFiles($directory))
            ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
            ->mapWithKeys(function (SplFileInfo $file) {
                $class = $this->classFromFile($file);

                return $class ? [$class => $file->getPathname()] : [];
            })
            ->all();

        $this->seederClassMap = $map;

        return collect($map);
    }

    private function ensureSeederClassIsLoaded(string $className): bool
    {
        if ($this->classExistsSafely($className)) {
            return true;
        }

        $filePath = $this->resolveSeederFilePath($className);

        if (! $filePath) {
            $filePath = $this->getSeederClassMap()->get($className);
        }

        if (! $filePath || ! is_file($filePath)) {
            return false;
        }

        try {
            require_once $filePath;
        } catch (\Throwable) {
            return false;
        }

        return $this->classExistsSafely($className);
    }
}
