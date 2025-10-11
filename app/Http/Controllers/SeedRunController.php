<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Services\QuestionDeletionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
                $questionsBySeeder = Question::query()
                    ->with(['answers.option'])
                    ->whereIn('seeder', $executedSeeders->pluck('class_name'))
                    ->orderBy('id')
                    ->get()
                    ->groupBy('seeder')
                    ->map(function ($questions) {
                        return $questions->map(function (Question $question) {
                            return [
                                'id' => $question->id,
                                'highlighted_text' => $this->renderQuestionWithHighlightedAnswers($question),
                            ];
                        });
                    });
            }

            $executedSeeders = $executedSeeders->map(function ($seedRun) use ($questionsBySeeder) {
                $questions = $questionsBySeeder->get($seedRun->class_name, collect());
                $seedRun->questions = $questions;
                $seedRun->question_count = $questions->count();

                return $seedRun;
            });
        }

        return view('seed-runs.index', [
            'tableExists' => $tableExists,
            'executedSeeders' => $executedSeeders,
            'pendingSeeders' => $pendingSeeders,
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
            ->filter(fn (?string $class) => $class && class_exists($class))
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
}
