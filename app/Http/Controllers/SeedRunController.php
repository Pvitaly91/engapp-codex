<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Services\QuestionDeletionService;
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
        $executedSeeders = collect();
        $pendingSeeders = collect();
        $questionsBySeeder = collect();
        $tableExists = Schema::hasTable('seed_runs');
        $executedSeederHierarchy = collect();

        if ($tableExists) {
            $executedSeeders = DB::table('seed_runs')
                ->orderByDesc('ran_at')
                ->get()
                ->map(function ($seedRun) {
                    $seedRun->ran_at = $seedRun->ran_at ? Carbon::parse($seedRun->ran_at) : null;
                    $seedRun->display_class_name = $this->formatSeederClassName($seedRun->class_name);

                    return $seedRun;
                });

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

            if (Schema::hasColumn('questions', 'seeder') && $executedSeeders->isNotEmpty()) {
                $executedClasses = $executedSeeders->pluck('class_name');

                $questionsBySeeder = Question::query()
                    ->with(['answers.option', 'category', 'source'])
                    ->whereIn('seeder', $executedClasses)
                    ->orderBy('id')
                    ->get()
                    ->groupBy('seeder')
                    ->map(function ($questions) {
                        return $questions
                            ->groupBy(function (Question $question) {
                                return optional($question->category)->name ?? __('Без категорії');
                            })
                            ->map(function ($categoryQuestions, $categoryName) {
                                $category = optional($categoryQuestions->first()->category);

                                $sources = $categoryQuestions
                                    ->groupBy(function (Question $question) {
                                        return optional($question->source)->name ?? __('Без джерела');
                                    })
                                    ->map(function ($sourceQuestions, $sourceName) {
                                        $source = optional($sourceQuestions->first()->source);

                                        return [
                                            'source' => $source ? [
                                                'id' => $source->id,
                                                'name' => $source->name,
                                            ] : null,
                                            'display_name' => $sourceName,
                                            'questions' => $sourceQuestions->map(function (Question $question) {
                                                return [
                                                    'id' => $question->id,
                                                    'uuid' => $question->uuid,
                                                    'highlighted_text' => $this->renderQuestionWithHighlightedAnswers($question),
                                                ];
                                            })->values(),
                                        ];
                                    })
                                    ->values();

                                return [
                                    'category' => $category ? [
                                        'id' => $category->id,
                                        'name' => $category->name,
                                    ] : null,
                                    'display_name' => $categoryName,
                                    'sources' => $sources,
                                    'question_count' => $sources->sum(fn ($sourceGroup) => $sourceGroup['questions']->count()),
                                ];
                            })
                            ->values();
                    });
            }

            $executedSeeders = $executedSeeders->map(function ($seedRun) use ($questionsBySeeder) {
                $questionGroups = $questionsBySeeder->get($seedRun->class_name, collect());
                $seedRun->question_groups = $questionGroups;
                $seedRun->question_count = $questionGroups->sum(fn ($categoryGroup) => $categoryGroup['question_count'] ?? 0);

                return $seedRun;
            });

            $executedSeederHierarchy = $this->buildSeederHierarchy($executedSeeders);
        }

        return view('seed-runs.index', [
            'tableExists' => $tableExists,
            'executedSeeders' => $executedSeeders,
            'pendingSeeders' => $pendingSeeders,
            'executedSeederHierarchy' => $executedSeederHierarchy,
        ]);
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

    protected function normalizeSeederHierarchy(array $node): Collection
    {
        $folders = collect($node['folders'] ?? [])
            ->sortBy(fn ($folder) => $folder['name'])
            ->map(function ($folder) {
                $children = $this->normalizeSeederHierarchy($folder);

                return [
                    'type' => 'folder',
                    'name' => $folder['name'],
                    'children' => $children,
                    'seeder_count' => $children->sum(fn ($child) => $child['seeder_count'] ?? 0),
                ];
            });

        $seeders = collect($node['seeders'] ?? [])
            ->sortBy(fn ($seeder) => $seeder['name'])
            ->map(function ($seeder) {
                return [
                    'type' => 'seeder',
                    'name' => $seeder['name'],
                    'seed_run' => $seeder['seed_run'],
                    'seeder_count' => 1,
                ];
            });

        return $folders->values()->merge($seeders->values())->values();
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

        DB::transaction(function () use ($seedRun, &$deletedQuestions) {
            if (! Schema::hasColumn('questions', 'seeder')) {
                DB::table('seed_runs')->where('id', $seedRun->id)->delete();

                return;
            }

            $questions = Question::query()
                ->where('seeder', $seedRun->class_name)
                ->get();

            foreach ($questions as $question) {
                $this->questionDeletionService->deleteQuestion($question);
                $deletedQuestions++;
            }

            DB::table('seed_runs')->where('id', $seedRun->id)->delete();
        });

        return redirect()
            ->route('seed-runs.index')
            ->with('status', __('Removed seeder :class and deleted :count related question(s).', [
                'class' => $seedRun->class_name,
                'count' => $deletedQuestions,
            ]));
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
            ->map(fn (SplFileInfo $file) => $this->classFromFile($file, $directory))
            ->filter(fn (?string $class) => $class && $this->isInstantiableSeeder($class))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function classFromFile(SplFileInfo $file, string $baseDirectory): ?string
    {
        $relativePath = Str::after($file->getPathname(), rtrim($baseDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

        if ($relativePath === $file->getPathname()) {
            return null;
        }

        $classPath = str_replace(['/', '\\'], '\\', Str::beforeLast($relativePath, '.php'));

        return 'Database\\Seeders\\' . $classPath;
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
}
