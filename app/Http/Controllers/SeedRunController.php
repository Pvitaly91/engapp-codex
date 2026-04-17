<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\TextBlock;
use App\Services\SeederPromptTheoryPageResolver;
use App\Services\SeederTestTargetResolver;
use App\Services\QuestionDeletionService;
use App\Support\Database\JsonPageSeeder;
use App\Support\Database\JsonPageLocalizationManager;
use App\Support\Database\JsonTestLocalizationManager;
use Database\Seeders\Pages\Concerns\GrammarPageVariantSeeder as GrammarPageVariantSeederBase;
use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder as GrammarPageSeederBase;
use Database\Seeders\Page_v2\Concerns\PageCategoryDescriptionSeeder as PageCategoryDescriptionSeederBase;
use Database\Seeders\QuestionSeeder as QuestionSeederBase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder as LaravelSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

    public function __construct(
        private QuestionDeletionService $questionDeletionService,
        private SeederPromptTheoryPageResolver $seederPromptTheoryPageResolver,
        private SeederTestTargetResolver $seederTestTargetResolver,
        private JsonTestLocalizationManager $jsonTestLocalizationManager,
        private JsonPageLocalizationManager $jsonPageLocalizationManager,
    )
    {
    }

    protected function isVirtualLocalizationSeeder(string $className): bool
    {
        return $this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)
            || $this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className);
    }

    protected function virtualLocalizationType(string $className): ?string
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return 'question_localizations';
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return 'page_localizations';
        }

        return null;
    }

    protected function virtualLocalizationClasses(): array
    {
        return collect($this->jsonTestLocalizationManager->virtualSeederClasses())
            ->merge($this->jsonPageLocalizationManager->virtualSeederClasses())
            ->unique()
            ->values()
            ->all();
    }

    protected function virtualLocalizationFilePath(string $className): ?string
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonTestLocalizationManager->filePathForClass($className);
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonPageLocalizationManager->filePathForClass($className);
        }

        return null;
    }

    protected function applyVirtualLocalizationSeeder(string $className): array
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonTestLocalizationManager->applyVirtualSeeder($className);
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonPageLocalizationManager->applyVirtualSeeder($className);
        }

        throw new \RuntimeException(__('Localization seeder :class was not found.', ['class' => $className]));
    }

    protected function removeVirtualLocalizationSeederData(string $className): array
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonTestLocalizationManager->removeVirtualSeederData($className);
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonPageLocalizationManager->removeVirtualSeederData($className);
        }

        return [];
    }

    protected function buildVirtualLocalizationPreview(string $className): array
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonTestLocalizationManager->buildVirtualSeederPreview($className);
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonPageLocalizationManager->buildVirtualSeederPreview($className);
        }

        throw new \RuntimeException(__('Localization seeder :class was not found.', ['class' => $className]));
    }

    protected function localizationTargetSeederClass(string $className): ?string
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonTestLocalizationManager->targetSeederClassForVirtualSeeder($className);
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonPageLocalizationManager->targetSeederClassForVirtualSeeder($className);
        }

        return null;
    }

    protected function localizationDescriptorForClass(string $className): ?array
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonTestLocalizationManager->descriptorForClass($className);
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonPageLocalizationManager->descriptorForClass($className);
        }

        return null;
    }

    protected function normalizeLocalizationLocale(?string $locale): string
    {
        $normalized = strtolower(trim((string) $locale));

        return $normalized === 'ua' ? 'uk' : $normalized;
    }

    protected function localizationLocaleLabel(?string $locale): string
    {
        $normalized = $this->normalizeLocalizationLocale($locale);

        return $normalized !== '' ? Str::upper($normalized) : __('N/A');
    }

    protected function localizationTypeLabel(?string $type): string
    {
        return match ($type) {
            'question_localizations' => __('Локалізація питань'),
            'page_localizations' => __('Локалізація сторінки'),
            default => __('Локалізація'),
        };
    }

    protected function buildRelatedLocalizationMap(Collection $executedSeeders): Collection
    {
        return $executedSeeders
            ->map(function ($seedRun) {
                $className = (string) ($seedRun->class_name ?? '');
                $profileType = (string) data_get($seedRun, 'data_profile.type', '');

                if (! in_array($profileType, ['question_localizations', 'page_localizations'], true)) {
                    return null;
                }

                $targetClassName = $this->localizationTargetSeederClass($className);

                if (! filled($targetClassName)) {
                    return null;
                }

                $descriptor = $this->localizationDescriptorForClass($className);
                $locale = $this->normalizeLocalizationLocale($descriptor['locale'] ?? null);

                return [
                    'seed_run_id' => (int) ($seedRun->id ?? 0),
                    'class_name' => $className,
                    'display_name' => (string) ($seedRun->display_class_name ?? $this->formatSeederClassName($className)),
                    'display_basename' => (string) ($seedRun->display_class_basename ?? class_basename($className)),
                    'target_class_name' => $targetClassName,
                    'locale' => $locale,
                    'locale_label' => $this->localizationLocaleLabel($locale),
                    'type' => $profileType,
                    'type_label' => $this->localizationTypeLabel($profileType),
                    'ran_at' => optional($seedRun->ran_at)->format('Y-m-d H:i:s'),
                ];
            })
            ->filter()
            ->groupBy('target_class_name')
            ->map(function (Collection $items) {
                return $items
                    ->sortBy(fn (array $item) => sprintf(
                        '%s|%s|%010d',
                        $item['locale'] ?? '',
                        $item['display_name'] ?? '',
                        (int) ($item['seed_run_id'] ?? 0)
                    ))
                    ->values();
            });
    }

    protected function buildAvailableLocalizationMap(iterable $targetSeeders): Collection
    {
        $targetLookup = collect($targetSeeders)
            ->map(function ($seeder) {
                if (is_string($seeder)) {
                    return trim($seeder);
                }

                return trim((string) data_get($seeder, 'class_name', ''));
            })
            ->filter()
            ->flip();

        if ($targetLookup->isEmpty()) {
            return collect();
        }

        return collect($this->virtualLocalizationClasses())
            ->map(function (string $className) {
                $type = $this->virtualLocalizationType($className);

                if (! in_array($type, ['question_localizations', 'page_localizations'], true)) {
                    return null;
                }

                $targetClassName = $this->localizationTargetSeederClass($className);

                if (! filled($targetClassName)) {
                    return null;
                }

                $descriptor = $this->localizationDescriptorForClass($className);
                $locale = $this->normalizeLocalizationLocale($descriptor['locale'] ?? null);

                return [
                    'class_name' => $className,
                    'display_name' => $this->formatSeederClassName($className),
                    'display_basename' => class_basename($className),
                    'target_class_name' => $targetClassName,
                    'locale' => $locale,
                    'locale_label' => $this->localizationLocaleLabel($locale),
                    'type' => $type,
                    'type_label' => $this->localizationTypeLabel($type),
                ];
            })
            ->filter(fn (?array $item) => $item !== null && $targetLookup->has($item['target_class_name'] ?? ''))
            ->groupBy('target_class_name')
            ->map(function (Collection $items) {
                return $items
                    ->sortBy(fn (array $item) => sprintf(
                        '%s|%s|%s',
                        $item['locale'] ?? '',
                        $item['display_name'] ?? '',
                        $item['class_name'] ?? ''
                    ))
                    ->values();
            });
    }

    protected function buildPendingLocalizationMap(iterable $targetSeeders, iterable $executedClassNames): Collection
    {
        $executedLookup = collect($executedClassNames)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->flip();

        return $this->buildAvailableLocalizationMap($targetSeeders)
            ->map(function (Collection $items) use ($executedLookup) {
                return $items
                    ->reject(fn (array $item) => $executedLookup->has((string) ($item['class_name'] ?? '')))
                    ->values();
            })
            ->filter(fn (Collection $items) => $items->isNotEmpty());
    }

    protected function normalizeSeederTab(?string $tab): string
    {
        return in_array($tab, ['localizations', 'theory-tests'], true) ? $tab : 'main';
    }

    protected function seederTabForClass(string $className): string
    {
        return in_array($this->virtualLocalizationType($className), ['question_localizations', 'page_localizations'], true)
            ? 'localizations'
            : 'main';
    }

    protected function currentSeederTab(?Request $request = null): string
    {
        $request ??= request();

        return $this->normalizeSeederTab((string) $request->input('tab', $request->query('tab', 'main')));
    }

    protected function routeParametersForSeederTab(string $tab): array
    {
        $tab = $this->normalizeSeederTab($tab);

        return $tab === 'main' ? [] : ['tab' => $tab];
    }

    protected function redirectToIndexWithTab(Request $request): RedirectResponse
    {
        return redirect()->route('seed-runs.index', $this->routeParametersForSeederTab(
            $this->currentSeederTab($request)
        ));
    }

    protected function filterSeedersForTab(Collection $seeders, string $tab): Collection
    {
        $normalizedTab = $this->normalizeSeederTab($tab);

        return $seeders
            ->filter(function ($seeder) use ($normalizedTab) {
                $className = (string) data_get($seeder, 'class_name', '');
                $seederTab = (string) data_get($seeder, 'seeder_tab', '');

                if ($seederTab === '' && $className !== '') {
                    $seederTab = $this->seederTabForClass($className);
                }

                return $this->normalizeSeederTab($seederTab) === $normalizedTab;
            })
            ->values();
    }

    protected function buildTheoryTestPages(Collection $executedSeeders, ?Collection $pendingSeeders = null): Collection
    {
        $pendingSeeders ??= collect();

        return $executedSeeders
            ->map(fn ($seedRun) => ['status' => 'executed', 'seeder' => $seedRun])
            ->merge($pendingSeeders->map(fn ($pendingSeeder) => ['status' => 'pending', 'seeder' => $pendingSeeder]))
            ->filter(function (array $entry) {
                return filled(data_get($entry, 'seeder.prompt_theory_target.url'))
                    || filled(data_get($entry, 'seeder.prompt_theory_target.title'));
            })
            ->groupBy(function (array $entry) {
                $url = trim((string) data_get($entry, 'seeder.prompt_theory_target.url', ''));

                if ($url !== '') {
                    return $url;
                }

                return trim((string) data_get(
                    $entry,
                    'seeder.prompt_theory_target.title',
                    data_get($entry, 'seeder.class_name', 'unknown-seeder')
                ));
            })
            ->map(function (Collection $seeders, string $groupKey) {
                $executedPageSeeders = $seeders
                    ->filter(fn (array $entry) => ($entry['status'] ?? null) === 'executed')
                    ->map(fn (array $entry) => $entry['seeder'])
                    ->sortByDesc(fn ($seedRun) => optional($seedRun->ran_at)->timestamp ?? 0)
                    ->values();

                $pendingPageSeeders = $seeders
                    ->filter(fn (array $entry) => ($entry['status'] ?? null) === 'pending')
                    ->map(fn (array $entry) => $entry['seeder'])
                    ->sortBy(fn ($pendingSeeder) => Str::lower((string) data_get(
                        $pendingSeeder,
                        'display_class_name',
                        data_get($pendingSeeder, 'class_name', '')
                    )))
                    ->values();

                $sortedSeeders = $executedPageSeeders
                    ->concat($pendingPageSeeders)
                    ->values();

                $firstSeeder = $executedPageSeeders->first() ?? $pendingPageSeeders->first();

                return [
                    'group_key' => $groupKey,
                    'page' => [
                        'label' => (string) data_get($firstSeeder, 'prompt_theory_target.label', __('Пов’язана сторінка теорії')),
                        'title' => (string) data_get($firstSeeder, 'prompt_theory_target.title', $groupKey),
                        'url' => (string) data_get($firstSeeder, 'prompt_theory_target.url', ''),
                    ],
                    'seeders' => $sortedSeeders,
                    'executed_seeders' => $executedPageSeeders,
                    'pending_seeders' => $pendingPageSeeders,
                    'seeders_count' => $sortedSeeders->count(),
                    'executed_seeders_count' => $executedPageSeeders->count(),
                    'pending_seeders_count' => $pendingPageSeeders->count(),
                    'question_count' => $sortedSeeders->sum(fn ($seedRun) => (int) ($seedRun->question_count ?? 0)),
                    'tests_count' => $sortedSeeders
                        ->pluck('test_target.url')
                        ->filter(fn ($url) => filled($url))
                        ->unique()
                        ->count(),
                    'latest_ran_at' => $sortedSeeders->max(
                        fn ($seedRun) => optional(data_get($seedRun, 'ran_at'))->timestamp ?? 0
                    ),
                ];
            })
            ->sort(function (array $left, array $right) {
                return [
                    $right['latest_ran_at'] ?? 0,
                    Str::lower((string) data_get($left, 'page.title', '')),
                ] <=> [
                    $left['latest_ran_at'] ?? 0,
                    Str::lower((string) data_get($right, 'page.title', '')),
                ];
            })
            ->values();
    }

    protected function buildSeederTabCounts(
        Collection $pendingSeeders,
        Collection $executedSeeders,
        ?Collection $theoryTestPages = null
    ): array
    {
        $counts = [];

        foreach (['main', 'localizations'] as $tab) {
            $pendingCount = $this->filterSeedersForTab($pendingSeeders, $tab)->count();
            $executedCount = $this->filterSeedersForTab($executedSeeders, $tab)->count();

            $counts[$tab] = [
                'pending' => $pendingCount,
                'executed' => $executedCount,
                'total' => $pendingCount + $executedCount,
            ];
        }

        $theoryTestPages ??= collect();
        $theoryPagesCount = $theoryTestPages->count();

        $counts['theory-tests'] = [
            'pending' => $theoryTestPages
                ->filter(fn (array $pageGroup) => (int) ($pageGroup['pending_seeders_count'] ?? 0) > 0)
                ->count(),
            'executed' => $theoryTestPages
                ->filter(fn (array $pageGroup) => (int) ($pageGroup['executed_seeders_count'] ?? 0) > 0)
                ->count(),
            'total' => $theoryPagesCount,
        ];

        return $counts;
    }

    protected function buildPendingSeederState(string $className, iterable $executedClassNames): array
    {
        $localizationType = $this->virtualLocalizationType($className);
        $requiredBaseSeeder = $this->localizationTargetSeederClass($className);
        $executedLookup = collect($executedClassNames)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->flip();

        $canExecute = true;
        $blockedReason = null;

        if (
            in_array($localizationType, ['question_localizations', 'page_localizations'], true)
            && filled($requiredBaseSeeder)
            && ! $executedLookup->has($requiredBaseSeeder)
        ) {
            $canExecute = false;
            $blockedReason = __('Спочатку виконайте основний сидер :seeder.', [
                'seeder' => $this->formatSeederClassName($requiredBaseSeeder),
            ]);
        }

        return [
            'seeder_tab' => $this->seederTabForClass($className),
            'required_base_seeder' => $requiredBaseSeeder,
            'required_base_display_name' => filled($requiredBaseSeeder)
                ? $this->formatSeederClassName($requiredBaseSeeder)
                : null,
            'can_execute' => $canExecute,
            'execution_block_reason' => $blockedReason,
        ];
    }

    protected function executionBlockedMessageForSeeder(string $className, iterable $executedClassNames): ?string
    {
        $state = $this->buildPendingSeederState($className, $executedClassNames);

        return ($state['can_execute'] ?? true)
            ? null
            : (string) ($state['execution_block_reason'] ?? __('Сидер тимчасово недоступний для виконання.'));
    }

    protected function executedSeederClasses(): Collection
    {
        if (! Schema::hasTable('seed_runs')) {
            return collect();
        }

        return DB::table('seed_runs')
            ->pluck('class_name')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values();
    }

    public function index(Request $request): View
    {
        $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));

        return view('seed-runs.index', [
            'tableExists' => $overview['tableExists'],
            'executedSeeders' => $overview['executedSeeders'],
            'pendingSeeders' => $overview['pendingSeeders'],
            'pendingSeederHierarchy' => $overview['pendingSeederHierarchy'],
            'executedSeederHierarchy' => $overview['executedSeederHierarchy'],
            'recentSeedRunOrdinals' => $overview['recentSeedRunOrdinals'],
            'activeSeederTab' => $overview['activeSeederTab'],
            'seederTabCounts' => $overview['seederTabCounts'],
            'runnablePendingCount' => $overview['runnablePendingCount'],
            'theoryTestPages' => $overview['theoryTestPages'],
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

    public function storeSeederFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_name' => ['required', 'string', 'regex:/^[A-Z][a-zA-Z0-9]*$/'],
            'contents' => ['required', 'string'],
            'folder' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Неможливо створити файл сидера через помилки валідації.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $className = (string) $validated['class_name'];
        $contents = (string) $validated['contents'];
        $folder = trim((string) ($validated['folder'] ?? ''));

        // Build the directory path with strict validation
        $baseDir = database_path('seeders');
        $targetDir = $baseDir;

        if ($folder !== '') {
            // Use whitelist approach: only allow alphanumeric characters and underscores in folder names
            // Split by any directory separator and validate each part
            $parts = preg_split('/[\/\\\\]+/', $folder);
            $sanitizedParts = [];

            foreach ($parts as $part) {
                $part = trim($part);

                if ($part === '' || $part === '.' || $part === '..') {
                    continue;
                }

                // Only allow alphanumeric and underscores in folder names
                if (! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $part)) {
                    return response()->json([
                        'message' => __('Назва папки може містити лише літери, цифри та підкреслення, і має починатися з літери.'),
                    ], 422);
                }

                $sanitizedParts[] = $part;
            }

            $folder = implode(DIRECTORY_SEPARATOR, $sanitizedParts);

            if ($folder !== '') {
                $targetDir = $baseDir . DIRECTORY_SEPARATOR . $folder;

                // Ensure the resolved path is still within the seeders directory
                $resolvedPath = realpath(dirname($targetDir));

                if ($resolvedPath !== false && strpos($resolvedPath, realpath($baseDir)) !== 0) {
                    return response()->json([
                        'message' => __('Невалідний шлях до папки.'),
                    ], 422);
                }
            }
        }

        // Build the namespace based on folder structure
        $namespace = 'Database\\Seeders';

        if ($folder !== '') {
            $namespaceParts = explode(DIRECTORY_SEPARATOR, $folder);
            $namespaceParts = array_filter($namespaceParts, fn ($part) => $part !== '');

            if (! empty($namespaceParts)) {
                $namespace .= '\\' . implode('\\', $namespaceParts);
            }
        }

        // Full class name with namespace
        $fullClassName = $namespace . '\\' . $className;

        // Check if file already exists
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $className . '.php';

        if (File::exists($filePath)) {
            return response()->json([
                'message' => __('Файл сидера :class вже існує.', ['class' => $fullClassName]),
            ], 409);
        }

        // Create directory if it doesn't exist
        if (! File::isDirectory($targetDir)) {
            try {
                File::makeDirectory($targetDir, 0755, true);
            } catch (\Throwable $exception) {
                report($exception);

                return response()->json([
                    'message' => __('Не вдалося створити директорію для сидера.'),
                ], 500);
            }
        }

        // Write the file
        try {
            File::put($filePath, $contents, true);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => __('Не вдалося створити файл сидера :class.', ['class' => $fullClassName]),
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

        // Clear the seeder class map cache
        $this->seederClassMap = null;

        // Prepare the pending seeder data for UI update
        $displayName = $this->formatSeederClassName($fullClassName);
        [$displayNamespace, $baseName] = $this->splitSeederDisplayName($displayName);

        $pendingSeeder = (object) [
            'class_name' => $fullClassName,
            'display_class_name' => $displayName,
            'display_class_namespace' => $displayNamespace,
            'display_class_basename' => $baseName,
            'supports_preview' => false,
        ];

        $availableLocalizations = $this->buildAvailableLocalizationMap([$fullClassName])->get($fullClassName, collect());
        $pendingSeeder->available_localizations = $availableLocalizations instanceof Collection
            ? $availableLocalizations->all()
            : [];
        $pendingSeeder->available_localizations_count = count($pendingSeeder->available_localizations);

        return response()->json([
            'message' => __('Файл сидера успішно створено.'),
            'class_name' => $fullClassName,
            'path' => $this->makeSeederFileDisplayPath($filePath),
            'last_modified' => $lastModified,
            'pending_seeder' => $pendingSeeder,
        ]);
    }

    public function getSeederFolders(): JsonResponse
    {
        $baseDir = database_path('seeders');
        $folders = $this->discoverSeederFolders($baseDir, $baseDir);

        return response()->json([
            'folders' => $folders,
        ]);
    }

    protected function discoverSeederFolders(string $baseDir, string $currentDir): array
    {
        $folders = [];

        if (! File::isDirectory($currentDir)) {
            return $folders;
        }

        $directories = File::directories($currentDir);

        foreach ($directories as $directory) {
            $relativePath = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $directory);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            $folders[] = $relativePath;

            // Recursively discover subfolders
            $subFolders = $this->discoverSeederFolders($baseDir, $directory);
            $folders = array_merge($folders, $subFolders);
        }

        sort($folders);

        return $folders;
    }

    protected function assembleSeedRunOverview(?string $activeTab = null): array
    {
        $activeTab = $this->normalizeSeederTab($activeTab);
        $tableExists = Schema::hasTable('seed_runs');
        $executedSeeders = collect();
        $pendingSeeders = collect();
        $executedSeederHierarchy = collect();
        $recentSeedRunOrdinals = collect();
        $recentThreshold = now()->subDay();
        $seederTabCounts = $this->buildSeederTabCounts($pendingSeeders, $executedSeeders);
        $runnablePendingCount = 0;

        if (! $tableExists) {
            return [
                'tableExists' => false,
                'executedSeeders' => $executedSeeders,
                'pendingSeeders' => $pendingSeeders,
                'pendingSeederHierarchy' => collect(),
                'executedSeederHierarchy' => $executedSeederHierarchy,
                'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
                'activeSeederTab' => $activeTab,
                'seederTabCounts' => $seederTabCounts,
                'runnablePendingCount' => $runnablePendingCount,
                'theoryTestPages' => collect(),
            ];
        }

        $executedSeeders = DB::table('seed_runs')
            ->orderByDesc('ran_at')
            ->get()
            ->map(function ($seedRun) {
                $seedRun->ran_at = $seedRun->ran_at ? Carbon::parse($seedRun->ran_at) : null;
                $seedRun->display_class_name = $this->formatSeederClassName($seedRun->class_name);
                [$namespace, $baseName] = $this->splitSeederDisplayName($seedRun->display_class_name);
                $seedRun->display_class_namespace = $namespace;
                $seedRun->display_class_basename = $baseName;
                $seedRun->seeder_tab = $this->seederTabForClass($seedRun->class_name);

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
            ->filter()
            ->values();

        $pendingSeeders = collect($this->discoverSeederClasses(database_path('seeders')))
            ->reject(fn (string $class) => $executedClasses->contains($class))
            ->map(function (string $class) use ($executedClasses) {
                $displayName = $this->formatSeederClassName($class);
                [$namespace, $baseName] = $this->splitSeederDisplayName($displayName);
                $dataProfile = $this->describeSeederData($class);
                $dependencyState = $this->buildPendingSeederState($class, $executedClasses);

                return (object) array_merge([
                    'class_name' => $class,
                    'display_class_name' => $displayName,
                    'display_class_namespace' => $namespace,
                    'display_class_basename' => $baseName,
                    'supports_preview' => $this->seederSupportsPreview($class),
                    'data_type' => $dataProfile['type'] ?? 'unknown',
                ], $dependencyState);
            })
            ->values();

        $allDiscoveredSeederClasses = $executedClasses
            ->merge($pendingSeeders->pluck('class_name'))
            ->filter()
            ->unique()
            ->values();

        $promptTheoryTargets = $this->seederPromptTheoryPageResolver->resolveForSeeders(
            $allDiscoveredSeederClasses->all()
        );
        $testTargets = $this->seederTestTargetResolver->resolveForSeeders($allDiscoveredSeederClasses->all());

        $availableLocalizationMap = $this->buildAvailableLocalizationMap($pendingSeeders);

        $pendingSeeders = $pendingSeeders->map(function ($pendingSeeder) use ($availableLocalizationMap, $promptTheoryTargets, $testTargets) {
            $availableLocalizations = $availableLocalizationMap->get($pendingSeeder->class_name, collect());
            $pendingSeeder->available_localizations = $availableLocalizations instanceof Collection
                ? $availableLocalizations->all()
                : [];
            $pendingSeeder->available_localizations_count = count($pendingSeeder->available_localizations);
            $pendingSeeder->prompt_theory_target = $promptTheoryTargets->get($pendingSeeder->class_name);
            $pendingSeeder->test_target = $testTargets->get($pendingSeeder->class_name);

            return $pendingSeeder;
        });

        $questionCounts = collect();

        if (Schema::hasColumn('questions', 'seeder') && $executedSeeders->isNotEmpty()) {
            $questionCounts = Question::query()
                ->select('seeder', DB::raw('COUNT(*) as aggregate'))
                ->whereIn('seeder', $executedClasses->all())
                ->groupBy('seeder')
                ->pluck('aggregate', 'seeder');
        }

        $theoryPageTargets = $executedClasses->isEmpty()
            ? collect()
            : Page::query()
                ->with('category')
                ->whereIn('seeder', $executedClasses->all())
                ->where('type', 'theory')
                ->get()
                ->keyBy('seeder');

        $theoryCategoryTargets = $executedClasses->isEmpty()
            ? collect()
            : PageCategory::query()
                ->whereIn('seeder', $executedClasses->all())
                ->where('type', 'theory')
                ->get()
                ->keyBy('seeder');

        $executedSeeders = $executedSeeders->map(function ($seedRun) use ($questionCounts, $theoryPageTargets, $theoryCategoryTargets, $promptTheoryTargets) {
            $seedRun->data_profile = $this->describeSeederData($seedRun->class_name);
            $seedRun->question_count = ($seedRun->data_profile['type'] ?? 'unknown') === 'questions'
                ? (int) ($questionCounts[$seedRun->class_name] ?? 0)
                : 0;
            $seedRun->theory_target = $this->resolveTheoryTargetForSeeder(
                $seedRun->class_name,
                $theoryPageTargets,
                $theoryCategoryTargets
            );
            $seedRun->prompt_theory_target = $promptTheoryTargets->get($seedRun->class_name);

            return $seedRun;
        });

        $executedSeeders = $executedSeeders->map(function ($seedRun) use ($testTargets) {
            $seedRun->test_target = $testTargets->get($seedRun->class_name);

            return $seedRun;
        });

        $relatedLocalizationMap = $this->buildRelatedLocalizationMap($executedSeeders);
        $pendingLocalizationMap = $this->buildPendingLocalizationMap($executedSeeders, $executedClasses);

        $executedSeeders = $executedSeeders->map(function ($seedRun) use ($relatedLocalizationMap, $pendingLocalizationMap) {
            $relatedLocalizations = $relatedLocalizationMap->get($seedRun->class_name, collect());
            $seedRun->related_localizations = $relatedLocalizations instanceof Collection
                ? $relatedLocalizations->all()
                : [];
            $seedRun->related_localizations_count = count($seedRun->related_localizations);
            $pendingLocalizations = $pendingLocalizationMap->get($seedRun->class_name, collect());
            $seedRun->pending_localizations = $pendingLocalizations instanceof Collection
                ? $pendingLocalizations->all()
                : [];
            $seedRun->pending_localizations_count = count($seedRun->pending_localizations);

            return $seedRun;
        });

        $theoryTestPages = $this->buildTheoryTestPages($executedSeeders, $pendingSeeders);
        $seederTabCounts = $this->buildSeederTabCounts($pendingSeeders, $executedSeeders, $theoryTestPages);

        if ($activeTab === 'theory-tests') {
            $pendingSeeders = collect();
            $executedSeeders = collect();
            $runnablePendingCount = 0;
            $pendingSeederHierarchy = collect();
            $executedSeederHierarchy = collect();
        } else {
            $pendingSeeders = $this->filterSeedersForTab($pendingSeeders, $activeTab);
            $executedSeeders = $this->filterSeedersForTab($executedSeeders, $activeTab);
            $runnablePendingCount = $pendingSeeders
                ->filter(fn ($seeder) => (bool) data_get($seeder, 'can_execute', true))
                ->count();
            $pendingSeederHierarchy = $this->buildPendingSeederHierarchy($pendingSeeders);
            $executedSeederHierarchy = $this->buildSeederHierarchy($executedSeeders);
        }

        return [
            'tableExists' => true,
            'executedSeeders' => $executedSeeders,
            'pendingSeeders' => $pendingSeeders,
            'pendingSeederHierarchy' => $pendingSeederHierarchy,
            'executedSeederHierarchy' => $executedSeederHierarchy,
            'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
            'activeSeederTab' => $activeTab,
            'seederTabCounts' => $seederTabCounts,
            'runnablePendingCount' => $runnablePendingCount,
            'theoryTestPages' => $theoryTestPages,
        ];
    }

    protected function formatSeederClassName(string $className): string
    {
        $shortName = Str::after($className, 'Database\\Seeders\\');

        return $shortName !== '' ? $shortName : $className;
    }

    protected function formatDetailedErrorMessage(Collection $errors): string
    {
        $messages = $errors
            ->map(fn ($message) => trim((string) $message))
            ->filter()
            ->values();

        if ($messages->isEmpty()) {
            return '';
        }

        if ($messages->count() === 1) {
            return (string) $messages->first();
        }

        return $messages
            ->values()
            ->map(fn (string $message, int $index) => ($index + 1) . '. ' . $message)
            ->implode("\n");
    }

    protected function formatSeederExecutionError(string $className, \Throwable $exception): string
    {
        $message = trim($exception->getMessage());
        $displayName = $this->formatSeederClassName($className);

        if ($message === '') {
            return $displayName . ': ' . __('Невідома помилка під час виконання сидера.');
        }

        if (Str::startsWith($message, $displayName . ':') || Str::startsWith($message, $className . ':')) {
            return $message;
        }

        return $displayName . ': ' . $message;
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

    protected function buildQuestionLocalizationPreview(Question $question): array
    {
        $localizations = [];

        foreach ($question->hints as $hint) {
            $locale = $this->normalizeLocalizationLocale($hint->locale);
            $text = trim((string) $hint->hint);

            if ($locale === '' || $text === '') {
                continue;
            }

            if (! array_key_exists($locale, $localizations)) {
                $localizations[$locale] = [
                    'locale' => $locale,
                    'locale_label' => $this->localizationLocaleLabel($locale),
                    'hints' => [],
                    'explanations' => [],
                ];
            }

            $localizations[$locale]['hints'][] = [
                'provider' => $hint->provider,
                'text' => $text,
            ];
        }

        foreach ($question->chatgptExplanations as $explanation) {
            $locale = $this->normalizeLocalizationLocale($explanation->language);
            $text = trim((string) $explanation->explanation);

            if ($locale === '' || $text === '') {
                continue;
            }

            if (! array_key_exists($locale, $localizations)) {
                $localizations[$locale] = [
                    'locale' => $locale,
                    'locale_label' => $this->localizationLocaleLabel($locale),
                    'hints' => [],
                    'explanations' => [],
                ];
            }

            $localizations[$locale]['explanations'][] = [
                'wrong_answer' => $explanation->wrong_answer,
                'correct_answer' => $explanation->correct_answer,
                'text' => $text,
            ];
        }

        $preferredLocale = $this->normalizeLocalizationLocale((string) app()->getLocale());

        return collect($localizations)
            ->map(function (array $localization) {
                $localization['hints'] = collect($localization['hints'])
                    ->unique(fn (array $hint) => sprintf(
                        '%s|%s',
                        $hint['provider'] ?? '',
                        $hint['text'] ?? ''
                    ))
                    ->values()
                    ->all();

                $localization['explanations'] = collect($localization['explanations'])
                    ->unique(fn (array $explanation) => sprintf(
                        '%s|%s|%s',
                        $explanation['wrong_answer'] ?? '',
                        $explanation['correct_answer'] ?? '',
                        $explanation['text'] ?? ''
                    ))
                    ->values()
                    ->all();

                return $localization;
            })
            ->sortBy(function (array $localization) use ($preferredLocale) {
                return sprintf(
                    '%d|%s',
                    ($localization['locale'] ?? '') === $preferredLocale ? 0 : 1,
                    $localization['locale'] ?? ''
                );
            })
            ->values()
            ->all();
    }

    protected function resolveQuestionPreviewDefaultLocale(array $localizations): ?string
    {
        if ($localizations === []) {
            return null;
        }

        return $localizations[0]['locale'] ?? null;
    }

    protected function seederSupportsPreview(string $className): bool
    {
        if ($this->isVirtualLocalizationSeeder($className)) {
            return true;
        }

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

    protected function buildPendingSeederHierarchy(Collection $pendingSeeders): Collection
    {
        $root = [
            'folders' => [],
            'seeders' => [],
        ];

        foreach ($pendingSeeders as $seeder) {
            $segments = array_values(array_filter(explode('\\', $seeder->display_class_name), 'strlen'));

            if (empty($segments)) {
                $root['seeders'][] = [
                    'name' => $seeder->display_class_name,
                    'seeder' => $seeder,
                ];

                continue;
            }

            $current =& $root;

            foreach ($segments as $index => $segment) {
                $isLast = $index === count($segments) - 1;

                if ($isLast) {
                    $current['seeders'][] = [
                        'name' => $segment,
                        'seeder' => $seeder,
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

        return $this->normalizePendingSeederHierarchy($root);
    }

    protected function normalizePendingSeederHierarchy(array $node, string $path = ''): Collection
    {
        $folders = collect($node['folders'] ?? [])
            ->sortBy(fn ($folder) => $folder['name'])
            ->map(function ($folder) use ($path) {
                $folderPath = ltrim(($path !== '' ? $path . '/' : '') . $folder['name'], '/');
                $children = $this->normalizePendingSeederHierarchy($folder, $folderPath);
                $seederCount = $children->sum(fn ($child) => (int) ($child['seeder_count'] ?? 0));
                $classNames = $children->flatMap(function ($child) {
                    return collect($child['class_names'] ?? []);
                })->unique()->values();
                $runnableClassNames = $children->flatMap(function ($child) {
                    return collect($child['runnable_class_names'] ?? []);
                })->unique()->values();
                $blockedSeederCount = $children->sum(fn ($child) => (int) ($child['blocked_seeder_count'] ?? 0));

                return [
                    'type' => 'folder',
                    'name' => $folder['name'],
                    'children' => $children,
                    'seeder_count' => $seederCount,
                    'class_names' => $classNames->all(),
                    'runnable_class_names' => $runnableClassNames->all(),
                    'blocked_seeder_count' => $blockedSeederCount,
                    'path' => $folderPath,
                ];
            });

        $seeders = collect($node['seeders'] ?? [])
            ->sortBy(fn ($seeder) => $seeder['name'])
            ->map(function ($seeder) use ($path) {
                $pendingSeeder = $seeder['seeder'];
                $className = $pendingSeeder->class_name ?? '';
                $fullPath = ltrim(($path !== '' ? $path . '/' : '') . $seeder['name'], '/');
                $canExecute = (bool) ($pendingSeeder->can_execute ?? true);

                return [
                    'type' => 'seeder',
                    'name' => $seeder['name'],
                    'pending_seeder' => $pendingSeeder,
                    'seeder_count' => 1,
                    'class_names' => [$className],
                    'runnable_class_names' => $canExecute ? [$className] : [],
                    'blocked_seeder_count' => $canExecute ? 0 : 1,
                    'path' => $fullPath,
                ];
            });

        return $folders->values()->merge($seeders->values())->values();
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

    protected function resolveTheoryTargetForSeeder(
        string $className,
        ?Collection $theoryPageTargets = null,
        ?Collection $theoryCategoryTargets = null,
    ): ?array {
        if (! $this->isTheorySiteSeeder($className)) {
            return null;
        }

        $page = $theoryPageTargets?->get($className);

        if (! $page instanceof Page) {
            $page = $this->findTheoryPageForSeeder($className);
        }

        if ($page instanceof Page && filled($page->slug) && filled($page->category?->slug)) {
            return [
                'type' => 'page',
                'label' => __('Сторінка теорії'),
                'url' => localized_route('theory.show', [$page->category->slug, $page->slug]),
                'title' => $page->title,
            ];
        }

        $category = $theoryCategoryTargets?->get($className);

        if (! $category instanceof PageCategory) {
            $category = $this->findTheoryCategoryForSeeder($className);
        }

        if ($category instanceof PageCategory && filled($category->slug)) {
            return [
                'type' => 'category',
                'label' => __('Категорія теорії'),
                'url' => localized_route('theory.category', $category->slug),
                'title' => $category->title,
            ];
        }

        return null;
    }

    protected function findTheoryPageForSeeder(string $className): ?Page
    {
        $page = Page::query()
            ->with('category')
            ->where('seeder', $className)
            ->where('type', 'theory')
            ->orderBy('id')
            ->first();

        if ($page instanceof Page && filled($page->slug) && filled($page->category?->slug)) {
            return $page;
        }

        $slug = $this->resolvePageSlugForSeeder($className);

        if (! filled($slug)) {
            return null;
        }

        $page = Page::query()
            ->with('category')
            ->where('slug', $slug)
            ->where('type', 'theory')
            ->orderBy('id')
            ->first();

        return $page instanceof Page && filled($page->slug) && filled($page->category?->slug)
            ? $page
            : null;
    }

    protected function findTheoryCategoryForSeeder(string $className): ?PageCategory
    {
        $category = PageCategory::query()
            ->where('seeder', $className)
            ->where('type', 'theory')
            ->orderBy('id')
            ->first();

        if ($category instanceof PageCategory && filled($category->slug)) {
            return $category;
        }

        $slug = $this->resolveCategorySlugForSeeder($className);

        if (! filled($slug)) {
            return null;
        }

        $category = PageCategory::query()
            ->where('slug', $slug)
            ->where('type', 'theory')
            ->orderBy('id')
            ->first();

        return $category instanceof PageCategory && filled($category->slug)
            ? $category
            : null;
    }

    protected function isTheorySiteSeeder(string $className): bool
    {
        return Str::startsWith($className, [
            'Database\\Seeders\\Page_v2\\',
            'Database\\Seeders\\Page_V3\\',
        ]);
    }

    protected function resolveCategorySlugForSeeder(string $className): ?string
    {
        if (! $this->ensureSeederClassIsLoaded($className)) {
            return null;
        }

        if (is_subclass_of($className, JsonPageSeeder::class)) {
            $definition = $this->loadJsonSeederDefinition($className);
            $contentType = Str::lower(trim((string) ($definition['content_type'] ?? '')));

            if ($definition && ($contentType === 'category' || (is_array($definition['category'] ?? null) && ! is_array($definition['page'] ?? null)))) {
                $slug = trim((string) ($definition['slug'] ?? data_get($definition, 'category.slug', '')));

                return $slug !== '' ? $slug : null;
            }

            return null;
        }

        if (is_subclass_of($className, PageCategoryDescriptionSeederBase::class) || Str::contains(class_basename($className), 'Category')) {
            $slug = $this->invokeSeederMethod($className, 'previewCategorySlug');

            return is_string($slug) && $slug !== '' ? $slug : null;
        }

        return null;
    }

    protected function invokeSeederMethod(string $className, string $method): mixed
    {
        try {
            $reflection = new \ReflectionClass($className);

            if ($reflection->isAbstract() || ! $reflection->isInstantiable() || ! $reflection->hasMethod($method)) {
                return null;
            }

            $instance = app()->make($className);
            $methodReflection = $reflection->getMethod($method);

            if (method_exists($methodReflection, 'setAccessible')) {
                $methodReflection->setAccessible(true);
            }

            return $methodReflection->invoke($instance);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function buildSeederPreview(string $className): array
    {
        if ($this->isVirtualLocalizationSeeder($className)) {
            return $this->buildVirtualLocalizationPreview($className);
        }

        if (is_subclass_of($className, QuestionSeederBase::class)) {
            return $this->buildQuestionSeederPreview($className);
        }

        if (is_subclass_of($className, GrammarPageVariantSeederBase::class)) {
            return $this->buildTheoryVariantSeederPreview($className);
        }

        if (is_subclass_of($className, GrammarPageSeederBase::class)) {
            return $this->buildPageSeederPreview($className);
        }

        if (is_subclass_of($className, PageCategoryDescriptionSeederBase::class)) {
            return $this->buildCategorySeederPreview($className);
        }

        throw new \RuntimeException(__('Сидер :class не підтримує попередній перегляд.', ['class' => $className]));
    }

    protected function buildTheoryVariantSeederPreview(string $className): array
    {
        $seeder = app($className);

        if (! $seeder instanceof GrammarPageVariantSeederBase) {
            throw new \RuntimeException(__('Сидер :class не підтримує попередній перегляд варіанта.', ['class' => $className]));
        }

        $definition = $seeder->previewDefinition();
        $targetType = trim((string) ($definition['target_type'] ?? ''));
        $categorySlug = trim((string) ($definition['category_slug'] ?? ''));
        $pageSlug = trim((string) ($definition['page_slug'] ?? ''));
        $payload = is_array($definition['payload'] ?? null) ? $definition['payload'] : [];
        $targetUrl = $this->buildTheoryVariantTargetUrl($targetType, $categorySlug, $pageSlug);

        return [
            'type' => 'theory_variant',
            'questions' => collect(),
            'existingQuestionCount' => null,
            'variant' => [
                'target_type' => $targetType,
                'category_slug' => $categorySlug,
                'page_slug' => $pageSlug !== '' ? $pageSlug : null,
                'locale' => $definition['locale'] ?? null,
                'variant_key' => $definition['variant_key'] ?? null,
                'label' => $definition['label'] ?? null,
                'provider' => $definition['provider'] ?? null,
                'model' => $definition['model'] ?? null,
                'prompt_version' => $definition['prompt_version'] ?? null,
                'target_exists' => $this->theoryVariantPreviewTargetExists($targetType, $categorySlug, $pageSlug),
                'target_url' => $targetUrl,
                'title' => data_get($payload, 'title'),
                'subtitle_html' => data_get($payload, 'subtitle_html'),
                'blocks' => $this->buildTheoryVariantPreviewBlocks(data_get($payload, 'blocks', [])),
                'block_count' => count(data_get($payload, 'blocks', [])),
            ],
            'levelsSummary' => collect(),
            'answersSummary' => collect(),
        ];
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
                    $localizations = $this->buildQuestionLocalizationPreview($question);

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
                        'localizations' => $localizations,
                        'default_locale' => $this->resolveQuestionPreviewDefaultLocale($localizations),
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

            // Collect all unique levels from the preview questions
            $levelsSummary = $questions
                ->pluck('level')
                ->filter(fn ($level) => filled($level))
                ->unique()
                ->sort(SORT_NATURAL)
                ->values();

            // Collect all unique correct answers from the preview questions
            $answersSummary = $questions
                ->flatMap(function (Question $question) {
                    return $question->answers->map(function ($answer) {
                        return optional($answer->option)->option ?? $answer->answer;
                    });
                })
                ->filter(fn ($answer) => filled($answer))
                ->unique()
                ->sort(SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        } finally {
            DB::rollBack();
        }

        return [
            'type' => 'questions',
            'questions' => $previewQuestions,
            'existingQuestionCount' => $existingQuestionCount,
            'tagsSummary' => $tagsSummary,
            'levelsSummary' => $levelsSummary,
            'answersSummary' => $answersSummary,
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
                ['label' => 'Home', 'url' => localized_route('home')],
                ['label' => 'Теорія', 'url' => localized_route('pages.index')],
            ];

            if ($selectedCategory) {
                $breadcrumbs[] = [
                    'label' => $selectedCategory->title,
                    'url' => localized_route('pages.category', $selectedCategory->slug),
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
            'levelsSummary' => collect(),
            'answersSummary' => collect(),
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
                'url' => localized_route('pages.category', $category->slug),
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
            'levelsSummary' => collect(),
            'answersSummary' => collect(),
        ];
    }

    protected function buildTheoryVariantTargetUrl(string $targetType, string $categorySlug, string $pageSlug): ?string
    {
        if ($categorySlug === '') {
            return null;
        }

        if ($targetType === 'page' && $pageSlug !== '') {
            return localized_route('theory.show', [$categorySlug, $pageSlug]);
        }

        if ($targetType === 'category') {
            return localized_route('theory.category', $categorySlug);
        }

        return null;
    }

    protected function theoryVariantPreviewTargetExists(string $targetType, string $categorySlug, string $pageSlug): bool
    {
        $category = PageCategory::query()
            ->where('slug', $categorySlug)
            ->where('type', 'theory')
            ->first();

        if (! $category) {
            return false;
        }

        if ($targetType === 'category') {
            return true;
        }

        if ($targetType !== 'page' || $pageSlug === '') {
            return false;
        }

        return Page::query()
            ->where('slug', $pageSlug)
            ->where('type', 'theory')
            ->where('page_category_id', $category->getKey())
            ->exists();
    }

    protected function buildTheoryVariantPreviewBlocks(array $blocks): Collection
    {
        return collect($blocks)
            ->filter(fn ($block) => is_array($block))
            ->values()
            ->map(function (array $block, int $index) {
                return [
                    'index' => $index + 1,
                    'type' => $block['type'] ?? 'box',
                    'column' => $block['column'] ?? 'left',
                    'heading' => $block['heading'] ?? null,
                    'preview_html' => $this->buildTheoryVariantBlockPreviewHtml($block),
                ];
            });
    }

    protected function buildTheoryVariantBlockPreviewHtml(array $block): string
    {
        $body = trim((string) ($block['body'] ?? ''));

        if ($body === '') {
            return '';
        }

        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $body;
        }

        $snippets = [];
        $this->collectTheoryVariantPreviewSnippets($decoded, $snippets, 6);

        if ($snippets === []) {
            return '';
        }

        return collect($snippets)
            ->take(6)
            ->map(fn (string $snippet) => '<p>' . $snippet . '</p>')
            ->implode('');
    }

    /**
     * @param  array<int, string>  $results
     */
    protected function collectTheoryVariantPreviewSnippets(mixed $value, array &$results, int $limit): void
    {
        if (count($results) >= $limit) {
            return;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed !== '') {
                $results[] = $trimmed;
            }

            return;
        }

        if (! is_array($value)) {
            return;
        }

        $preferredKeys = [
            'title',
            'intro',
            'description',
            'warning',
            'note',
            'label',
            'text',
            'example',
            'subtitle',
            'prompt',
            'before',
            'after',
            'original',
            'wrong',
            'right',
            'en',
            'ua',
        ];

        foreach ($preferredKeys as $key) {
            if (! array_key_exists($key, $value) || count($results) >= $limit) {
                continue;
            }

            $this->collectTheoryVariantPreviewSnippets($value[$key], $results, $limit);
        }

        foreach ($value as $key => $item) {
            if (in_array($key, $preferredKeys, true) || count($results) >= $limit) {
                continue;
            }

            $this->collectTheoryVariantPreviewSnippets($item, $results, $limit);
        }
    }

    public function loadFolderChildren(Request $request): JsonResponse
    {
        $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));
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
            'activeSeederTab' => $overview['activeSeederTab'],
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

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['run' => $message]);
        }

        $blockedMessage = $this->executionBlockedMessageForSeeder($className, $this->executedSeederClasses());

        if ($blockedMessage !== null) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $blockedMessage], 422);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['run' => $blockedMessage]);
        }

        try {
            if ($this->isVirtualLocalizationSeeder($className)) {
                $this->applyVirtualLocalizationSeeder($className);
                DB::table('seed_runs')->updateOrInsert(
                    ['class_name' => $className],
                    ['ran_at' => now()]
                );
            } else {
                $filePath = $this->resolveSeederFilePath($className);

                if (! $this->isInstantiableSeeder($className, $filePath)) {
                    $message = __('Seeder :class cannot be executed.', ['class' => $className]);

                    if ($request->wantsJson()) {
                        return response()->json(['message' => $message], 422);
                    }

                    return $this->redirectToIndexWithTab($request)
                        ->withErrors(['run' => $message]);
                }

                $this->executeConcreteSeeder($className);
            }
        } catch (\Throwable $exception) {
            report($exception);

            if ($request->wantsJson()) {
                return response()->json(['message' => $exception->getMessage()], 500);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['run' => $exception->getMessage()]);
        }

        $message = __('Seeder :class executed successfully.', ['class' => $className]);
        $testTargets = $this->resolveTestTargetsForClasses([$className]);
        
        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));
            return response()->json([
                'message' => $message,
                'test_target' => $testTargets[0] ?? null,
                'test_targets' => $testTargets,
                'seeder_moved' => true,
                'class_name' => $className,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        $redirect = $this->redirectToIndexWithTab($request)->with('status', $message);

        if ($testTargets !== []) {
            $redirect = $redirect->with('status_links', $testTargets);
        }

        return $redirect;
    }

    public function runFolder(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'class_names' => ['required', 'array', 'min:1'],
            'class_names.*' => ['string'],
            'folder_label' => ['nullable', 'string'],
        ]);

        $classNames = collect($validated['class_names'] ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($classNames->isEmpty()) {
            $message = __('No seeders were selected.');

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['run' => $message]);
        }

        $folderLabel = $this->resolveFolderLabel($validated['folder_label'] ?? null);
        $result = $this->executeSeedersInOrder($classNames);

        $successMessage = $result['ran']->isNotEmpty()
            ? __('Executed :count seeder(s) from folder :folder.', [
                'count' => $result['ran']->count(),
                'folder' => $folderLabel,
            ])
            : __('No seeders were executed from folder :folder.', ['folder' => $folderLabel]);
        $testTargets = $this->resolveTestTargetsForClasses($result['ran']);

        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));

            return response()->json([
                'message' => $successMessage,
                'test_targets' => $testTargets,
                'errors' => $result['errors']->all(),
                'folder_label' => $folderLabel,
                'executed_count' => $result['ran']->count(),
                'executed_classes' => $result['ran']->all(),
                'ordered_classes' => $result['ordered']->all(),
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ], $result['ran']->isNotEmpty() ? 200 : 422);
        }

        $redirect = $this->redirectToIndexWithTab($request);

        if ($result['ran']->isNotEmpty()) {
            $redirect = $redirect->with('status', $successMessage);

            if ($testTargets !== []) {
                $redirect = $redirect->with('status_links', $testTargets);
            }
        }

        if ($result['errors']->isNotEmpty()) {
            $redirect = $redirect->withErrors(['run' => $result['errors']->implode(' ')]);
        }

        return $redirect;
    }

    public function refreshFolder(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'class_names' => ['required', 'array', 'min:1'],
            'class_names.*' => ['string'],
            'folder_label' => ['nullable', 'string'],
        ]);

        $classNames = collect($validated['class_names'] ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($classNames->isEmpty()) {
            $message = __('No seeders were selected.');

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['refresh' => $message]);
        }

        $folderLabel = $this->resolveFolderLabel($validated['folder_label'] ?? null);
        $result = $this->refreshExecutedSeeders($classNames);

        if (($result['selected_ran'] ?? collect())->isEmpty()) {
            $errorMessage = $result['message'] ?? __('Не вдалося оновити дані сидерів у папці.');

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $errorMessage,
                    'errors' => ($result['errors'] ?? collect())->all(),
                ], 422);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['refresh' => $errorMessage]);
        }

        $statusMessage = __('Оновлено дані :count сидер(ів) у папці :folder.', [
            'count' => ($result['selected_ran'] ?? collect())->count(),
            'folder' => $folderLabel,
        ]);

        $localizationsRan = (int) ($result['localizations_ran_total'] ?? 0);
        if ($localizationsRan > 0) {
            $statusMessage .= ' ' . __('Повторно виконано :count сидер(ів) локалізації.', [
                'count' => $localizationsRan,
            ]);
        }

        if (($result['questions_deleted'] ?? 0) > 0) {
            $statusMessage .= ' ' . __('Видалено :count пов’язаних питань.', ['count' => $result['questions_deleted']]);
        }

        if (($result['blocks_deleted'] ?? 0) > 0) {
            $statusMessage .= ' ' . __('Видалено :count пов’язаних текстових блоків.', ['count' => $result['blocks_deleted']]);
        }

        if (($result['pages_deleted'] ?? 0) > 0) {
            $statusMessage .= ' ' . __('Видалено :count пов’язаних сторінок.', ['count' => $result['pages_deleted']]);
        }

        if (($result['categories_deleted'] ?? 0) > 0) {
            $statusMessage .= ' ' . __('Видалено :count пов’язаних категорій.', ['count' => $result['categories_deleted']]);
        }

        if (($result['hints_deleted'] ?? 0) > 0) {
            $statusMessage .= ' ' . __('Видалено :count пов’язаних підказок.', ['count' => $result['hints_deleted']]);
        }

        if (($result['explanations_deleted'] ?? 0) > 0) {
            $statusMessage .= ' ' . __('Видалено :count пов’язаних пояснень.', ['count' => $result['explanations_deleted']]);
        }

        $errors = $result['errors'] ?? collect();
        $statusSummary = $statusMessage;

        if ($errors->isNotEmpty()) {
            $statusMessage .= ' ' . __('Під час повторного запуску виникли помилки: :errors', [
                'errors' => $this->formatDetailedErrorMessage($errors),
            ]);
        }

        $testTargets = $this->resolveTestTargetsForClasses($result['ran'] ?? collect());

        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));

            return response()->json([
                'status' => $errors->isNotEmpty() ? 'partial' : 'success',
                'message' => $statusSummary,
                'test_targets' => $testTargets,
                'errors' => $errors->all(),
                'folder_label' => $folderLabel,
                'refreshed_count' => ($result['selected_ran'] ?? collect())->count(),
                'refreshed_classes' => ($result['selected_ran'] ?? collect())->all(),
                'executed_classes' => ($result['ran'] ?? collect())->all(),
                'ordered_classes' => ($result['ordered'] ?? collect())->all(),
                'questions_deleted' => $result['questions_deleted'] ?? 0,
                'blocks_deleted' => $result['blocks_deleted'] ?? 0,
                'pages_deleted' => $result['pages_deleted'] ?? 0,
                'categories_deleted' => $result['categories_deleted'] ?? 0,
                'hints_deleted' => $result['hints_deleted'] ?? 0,
                'explanations_deleted' => $result['explanations_deleted'] ?? 0,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        $redirect = $this->redirectToIndexWithTab($request)
            ->with('status', $statusMessage);

        if ($testTargets !== []) {
            $redirect = $redirect->with('status_links', $testTargets);
        }

        if ($errors->isNotEmpty()) {
            $redirect = $redirect->withErrors(new MessageBag(['refresh' => $errors->all()]));
        }

        return $redirect;
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
                $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));
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

            return $this->redirectToIndexWithTab($request)
                ->with('status', $result['message']);
        }

        $errorMessage = $result['message'] ?? __('Не вдалося видалити файл сидера.');
        
        if ($request->wantsJson()) {
            return response()->json(['message' => $errorMessage], 500);
        }

        return $this->redirectToIndexWithTab($request)
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

            return $this->redirectToIndexWithTab($request)
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
            $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));
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

        $redirect = $this->redirectToIndexWithTab($request);

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

        if ($this->isVirtualLocalizationSeeder($className)) {
            $candidatePaths->push($this->virtualLocalizationFilePath($className));
        }

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

            return $this->redirectToIndexWithTab($request)
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

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['run' => $errorMessage]);
        }

        $blockedMessage = $this->executionBlockedMessageForSeeder($className, $this->executedSeederClasses());

        if ($blockedMessage !== null) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $blockedMessage], 422);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['run' => $blockedMessage]);
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

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['run' => $exception->getMessage()]);
        }

        $message = __('Seeder :class marked as executed.', ['class' => $className]);
        $testTargets = $this->resolveTestTargetsForClasses([$className]);
        
        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));
            return response()->json([
                'message' => $message,
                'test_target' => $testTargets[0] ?? null,
                'test_targets' => $testTargets,
                'seeder_moved' => true,
                'class_name' => $className,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        $redirect = $this->redirectToIndexWithTab($request)->with('status', $message);

        if ($testTargets !== []) {
            $redirect = $redirect->with('status_links', $testTargets);
        }

        return $redirect;
    }

    public function runMissing(Request $request): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['run' => $errorMessage]);
        }

        $activeTab = $this->currentSeederTab($request);
        $executedClasses = $this->executedSeederClasses();

        $pendingSeeders = collect($this->discoverSeederClasses(database_path('seeders')))
            ->reject(fn (string $class) => $executedClasses->contains($class))
            ->filter(fn (string $class) => $this->seederTabForClass($class) === $activeTab)
            ->values();
        $result = $this->executeSeedersInOrder($pendingSeeders);
        $ran = $result['ran'];
        $errors = $result['errors'];

        $successMessage = null;
        if ($ran->isNotEmpty()) {
            $successMessage = __('Executed :count seeder(s): :classes', [
                'count' => $ran->count(),
                'classes' => $ran->implode(', '),
            ]);
        }
        $testTargets = $this->resolveTestTargetsForClasses($ran);

        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview($activeTab);
            return response()->json([
                'message' => $successMessage ?? __('No seeders were executed.'),
                'test_targets' => $testTargets,
                'errors' => $errors->all(),
                'executed_count' => $ran->count(),
                'executed_classes' => $ran->all(),
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ], $ran->isNotEmpty() ? 200 : 422);
        }

        $redirect = $this->redirectToIndexWithTab($request);

        if ($successMessage) {
            $redirect = $redirect->with('status', $successMessage);

            if ($testTargets !== []) {
                $redirect = $redirect->with('status_links', $testTargets);
            }
        }

        if ($errors->isNotEmpty()) {
            $redirect = $redirect->withErrors(['run' => $errors->implode(' ')]);
        }

        return $redirect;
    }

    /**
     * @param  iterable<int, string>  $classNames
     * @return array<int, array<string, mixed>>
     */
    protected function resolveTestTargetsForClasses(iterable $classNames): array
    {
        return $this->seederTestTargetResolver->resolveForSeeders($classNames)
            ->filter(fn ($target) => filled($target['url'] ?? null))
            ->unique(fn ($target) => (string) ($target['url'] ?? ''))
            ->values()
            ->all();
    }

    protected function executeSeedersInOrder(Collection $classNames): array
    {
        $orderedClassNames = $this->orderSeedersForExecution($classNames);
        $ran = collect();
        $errors = collect();
        $executedClasses = $this->executedSeederClasses();

        foreach ($orderedClassNames as $className) {
            if (! $this->ensureSeederClassIsLoaded($className)) {
                $errors->push(__('Seeder :class is not autoloadable.', ['class' => $className]));
                continue;
            }

            $blockedMessage = $this->executionBlockedMessageForSeeder($className, $executedClasses);

            if ($blockedMessage !== null) {
                $errors->push($blockedMessage);
                continue;
            }

            try {
                if ($this->isVirtualLocalizationSeeder($className)) {
                    $this->applyVirtualLocalizationSeeder($className);
                    DB::table('seed_runs')->updateOrInsert(
                        ['class_name' => $className],
                        ['ran_at' => now()]
                    );
                } else {
                    $filePath = $this->resolveSeederFilePath($className);

                    if (! $this->isInstantiableSeeder($className, $filePath)) {
                        $errors->push(__('Seeder :class cannot be executed.', ['class' => $className]));
                        continue;
                    }

                    $this->executeConcreteSeeder($className);
                }

                $ran->push($className);
                $executedClasses->push($className);
            } catch (\Throwable $exception) {
                report($exception);
                $errors->push($this->formatSeederExecutionError($className, $exception));
            }
        }

        return [
            'ordered' => $orderedClassNames,
            'ran' => $ran,
            'errors' => $errors,
        ];
    }

    protected function executeConcreteSeeder(string $className): void
    {
        $seeder = app()->make($className);

        if (! $seeder instanceof LaravelSeeder || ! method_exists($seeder, 'run')) {
            throw new \RuntimeException(__('Seeder :class cannot be executed.', ['class' => $className]));
        }

        $seeder->setContainer(app());
        $seeder->run();

        if (Schema::hasTable('seed_runs')) {
            DB::table('seed_runs')->updateOrInsert(
                ['class_name' => $className],
                ['ran_at' => now()]
            );
        }
    }

    protected function orderSeedersForExecution(Collection $classNames): Collection
    {
        $normalized = $classNames
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return collect();
        }

        $metadataByClass = $normalized->mapWithKeys(fn (string $className) => [
            $className => $this->buildSeederExecutionMetadata($className),
        ]);

        $categoryProviders = $metadataByClass
            ->filter(fn (array $metadata) => filled($metadata['provided_category_slug'] ?? null))
            ->mapWithKeys(fn (array $metadata, string $className) => [
                (string) $metadata['provided_category_slug'] => $className,
            ]);

        $dependencies = [];
        $dependents = [];
        $inDegree = [];

        foreach ($normalized as $className) {
            $metadata = $metadataByClass->get($className, []);
            $dependentClasses = collect();

            $parentSlug = trim((string) ($metadata['parent_category_slug'] ?? ''));
            if ($parentSlug !== '' && $categoryProviders->has($parentSlug)) {
                $dependentClasses->push($categoryProviders->get($parentSlug));
            }

            $targetSlug = trim((string) ($metadata['target_category_slug'] ?? ''));
            if ($targetSlug !== '' && $categoryProviders->has($targetSlug)) {
                $dependentClasses->push($categoryProviders->get($targetSlug));
            }

            $requiredBaseSeeder = trim((string) ($metadata['required_base_seeder'] ?? ''));
            if ($requiredBaseSeeder !== '' && $normalized->contains($requiredBaseSeeder)) {
                $dependentClasses->push($requiredBaseSeeder);
            }

            $resolvedDependencies = $dependentClasses
                ->filter(fn ($dependency) => is_string($dependency) && $dependency !== '' && $dependency !== $className)
                ->unique()
                ->values()
                ->all();

            $dependencies[$className] = $resolvedDependencies;
            $inDegree[$className] = count($resolvedDependencies);

            foreach ($resolvedDependencies as $dependencyClass) {
                $dependents[$dependencyClass] ??= [];
                $dependents[$dependencyClass][] = $className;
            }
        }

        $ready = $this->sortSeedersByExecutionPriority(
            array_keys(array_filter($inDegree, fn (int $degree) => $degree === 0)),
            $metadataByClass
        );

        $ordered = [];

        while ($ready !== []) {
            $currentClass = array_shift($ready);
            $ordered[] = $currentClass;

            foreach ($dependents[$currentClass] ?? [] as $dependentClass) {
                $inDegree[$dependentClass]--;

                if ($inDegree[$dependentClass] === 0) {
                    $ready[] = $dependentClass;
                }
            }

            $ready = $this->sortSeedersByExecutionPriority(array_values(array_unique($ready)), $metadataByClass);
        }

        $remaining = array_values(array_diff($normalized->all(), $ordered));

        if ($remaining !== []) {
            $ordered = array_merge(
                $ordered,
                $this->sortSeedersByExecutionPriority($remaining, $metadataByClass)
            );
        }

        return collect($ordered)->values();
    }

    /**
     * @param  array<int, string>  $classNames
     * @param  Collection<string, array<string, mixed>>  $metadataByClass
     * @return array<int, string>
     */
    protected function sortSeedersByExecutionPriority(array $classNames, Collection $metadataByClass): array
    {
        usort($classNames, function (string $leftClass, string $rightClass) use ($metadataByClass) {
            $left = $metadataByClass->get($leftClass, []);
            $right = $metadataByClass->get($rightClass, []);

            $groupOrder = [
                'category' => 0,
                'page' => 1,
                'other' => 2,
                'localization' => 3,
            ];

            $leftGroup = $groupOrder[$left['group'] ?? 'other'] ?? 99;
            $rightGroup = $groupOrder[$right['group'] ?? 'other'] ?? 99;

            if ($leftGroup !== $rightGroup) {
                return $leftGroup <=> $rightGroup;
            }

            $leftHasParent = filled($left['parent_category_slug'] ?? null) ? 1 : 0;
            $rightHasParent = filled($right['parent_category_slug'] ?? null) ? 1 : 0;

            if ($leftHasParent !== $rightHasParent) {
                return $leftHasParent <=> $rightHasParent;
            }

            $leftDepth = (int) ($left['folder_depth'] ?? 0);
            $rightDepth = (int) ($right['folder_depth'] ?? 0);

            if ($leftDepth !== $rightDepth) {
                return $leftDepth <=> $rightDepth;
            }

            return strcmp(
                (string) ($left['sort_key'] ?? $leftClass),
                (string) ($right['sort_key'] ?? $rightClass)
            );
        });

        return $classNames;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSeederExecutionMetadata(string $className): array
    {
        $displayName = $this->formatSeederClassName($className);
        $segments = array_values(array_filter(explode('\\', $displayName), 'strlen'));

        $metadata = [
            'group' => 'other',
            'folder_depth' => max(0, count($segments) - 1),
            'sort_key' => Str::lower($displayName),
            'provided_category_slug' => null,
            'parent_category_slug' => null,
            'target_category_slug' => null,
        ];

        if ($this->isVirtualLocalizationSeeder($className)) {
            $metadata['group'] = 'localization';
            $metadata['required_base_seeder'] = $this->localizationTargetSeederClass($className);

            return $metadata;
        }

        if (! $this->ensureSeederClassIsLoaded($className)) {
            return $metadata;
        }

        if (is_subclass_of($className, JsonPageSeeder::class)) {
            return array_merge($metadata, $this->buildJsonSeederExecutionMetadata($className));
        }

        $categorySlug = $this->resolveCategorySlugForSeeder($className);

        if (
            is_subclass_of($className, PageCategoryDescriptionSeederBase::class)
            || ($categorySlug !== null && Str::contains(class_basename($className), 'Category'))
        ) {
            $metadata['group'] = 'category';
            $metadata['provided_category_slug'] = $categorySlug;

            return $metadata;
        }

        if (is_subclass_of($className, GrammarPageSeederBase::class)) {
            $metadata['group'] = 'page';
            $metadata['target_category_slug'] = $this->resolveGrammarPageCategorySlug($className);

            return $metadata;
        }

        return $metadata;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildJsonSeederExecutionMetadata(string $className): array
    {
        $definition = $this->loadJsonSeederDefinition($className);

        if ($definition === null) {
            return [];
        }

        $contentType = Str::lower(trim((string) ($definition['content_type'] ?? '')));

        if ($contentType === '') {
            $contentType = is_array($definition['page'] ?? null) ? 'page' : 'category';
        }

        if ($contentType === 'category') {
            return [
                'group' => 'category',
                'provided_category_slug' => trim((string) ($definition['slug'] ?? data_get($definition, 'category.slug', ''))) ?: null,
                'parent_category_slug' => trim((string) data_get($definition, 'category.parent_slug', '')) ?: null,
                'target_category_slug' => null,
            ];
        }

        return [
            'group' => 'page',
            'provided_category_slug' => null,
            'parent_category_slug' => null,
            'target_category_slug' => trim((string) data_get($definition, 'page.category.slug', '')) ?: null,
        ];
    }

    protected function loadJsonSeederDefinition(string $className): ?array
    {
        $definitionPath = $this->invokeSeederMethod($className, 'definitionPath');

        if (! is_string($definitionPath) || $definitionPath === '' || ! File::exists($definitionPath)) {
            return null;
        }

        $decoded = json_decode((string) File::get($definitionPath), true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function resolveGrammarPageCategorySlug(string $className): ?string
    {
        $pageConfig = $this->invokeSeederMethod($className, 'page');

        if (is_array($pageConfig)) {
            $categorySlug = trim((string) data_get($pageConfig, 'category.slug', ''));

            if ($categorySlug !== '') {
                return $categorySlug;
            }
        }

        $categoryConfig = $this->invokeSeederMethod($className, 'category');

        if (! is_array($categoryConfig)) {
            return null;
        }

        $categorySlug = trim((string) ($categoryConfig['slug'] ?? ''));

        return $categorySlug !== '' ? $categorySlug : null;
    }

    public function destroy(Request $request, int $seedRunId): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['delete' => $errorMessage]);
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            $errorMessage = __('Seed run record was not found.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['delete' => $errorMessage]);
        }

        $className = $seedRun->class_name;
        $deleted = DB::table('seed_runs')->where('id', $seedRunId)->delete();

        if (! $deleted) {
            $errorMessage = __('Seed run record was not found.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['delete' => $errorMessage]);
        }

        $message = __('Seed run entry removed.');
        
        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));
            
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

        return $this->redirectToIndexWithTab($request)
            ->with('status', $message);
    }

    public function destroyWithQuestions(Request $request, int $seedRunId): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['delete' => $errorMessage]);
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            $errorMessage = __('Seed run record was not found.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['delete' => $errorMessage]);
        }

        $deletedQuestions = 0;
        $deletedBlocks = 0;
        $deletedPages = 0;
        $deletedCategories = 0;
        $deletedHints = 0;
        $deletedExplanations = 0;
        $profile = $this->describeSeederData($seedRun->class_name);

        DB::transaction(function () use (
            $seedRun,
            &$deletedQuestions,
            &$deletedBlocks,
            &$deletedPages,
            &$deletedCategories,
            &$deletedHints,
            &$deletedExplanations,
            $profile
        ) {
            $classNames = collect([$seedRun->class_name]);

            if ($profile['type'] === 'question_localizations') {
                $result = $this->removeVirtualLocalizationSeederData($seedRun->class_name);
                $deletedHints = (int) ($result['deleted_hints'] ?? 0);
                $deletedExplanations = (int) ($result['deleted_explanations'] ?? 0);
            } elseif ($profile['type'] === 'page_localizations') {
                $result = $this->removeVirtualLocalizationSeederData($seedRun->class_name);
                $deletedBlocks = (int) ($result['deleted_blocks'] ?? 0);
            } elseif ($profile['type'] === 'questions') {
                $deletedQuestions = $this->deleteQuestionsForSeeders($classNames);
            } elseif ($profile['type'] === 'pages') {
                $pageResult = $this->deletePageContentForSeeders($classNames);
                $deletedBlocks = $pageResult['blocks'];
                $deletedPages = $pageResult['pages_deleted'];
                $deletedCategories = $pageResult['categories_deleted'];
            } else {
                $deletedQuestions = $this->deleteQuestionsForSeeders($classNames);
                $pageResult = $this->deletePageContentForSeeders($classNames);
                $deletedBlocks = $pageResult['blocks'];
                $deletedPages = $pageResult['pages_deleted'];
                $deletedCategories = $pageResult['categories_deleted'];
            }

            DB::table('seed_runs')->where('id', $seedRun->id)->delete();
        });

        $status = match ($profile['type']) {
            'question_localizations' => __('Removed localization seeder :class and deleted :hints hint(s), :explanations explanation(s).', [
                'class' => $seedRun->class_name,
                'hints' => $deletedHints,
                'explanations' => $deletedExplanations,
            ]),
            'page_localizations' => __('Removed page localization seeder :class and deleted :blocks localized text block(s).', [
                'class' => $seedRun->class_name,
                'blocks' => $deletedBlocks,
            ]),
            'pages' => __('Removed seeder :class and deleted :blocks related text block(s).', [
                'class' => $seedRun->class_name,
                'blocks' => $deletedBlocks,
            ]) . ($deletedPages > 0
                ? ' ' . __('Deleted :count related page record(s).', ['count' => $deletedPages])
                : '')
                . ($deletedCategories > 0
                    ? ' ' . __('Deleted :count related category record(s).', ['count' => $deletedCategories])
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
            $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));
            
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
                'categories_deleted' => $deletedCategories,
                'hints_deleted' => $deletedHints,
                'explanations_deleted' => $deletedExplanations,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        return $this->redirectToIndexWithTab($request)
            ->with('status', $status);
    }

    public function refresh(Request $request, int $seedRunId): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['refresh' => $errorMessage]);
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            $errorMessage = __('Seed run record was not found.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['refresh' => $errorMessage]);
        }

        $className = $seedRun->class_name;
        $profile = $this->describeSeederData($seedRun->class_name);
        $result = $this->refreshExecutedSeeders(collect([$className]));

        if (! ($result['selected_ran'] ?? collect())->contains($className)) {
            $errorMessage = $result['message'] ?? __('Не вдалося оновити дані сидера.');

            if (($result['errors'] ?? collect())->isNotEmpty()) {
                $errorMessage = $this->formatDetailedErrorMessage($result['errors']);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $errorMessage,
                    'errors' => ($result['errors'] ?? collect())->all(),
                ], 422);
            }

            return $this->redirectToIndexWithTab($request)
                ->withErrors(new MessageBag(['refresh' => [$errorMessage]]));
        }

        $deletedQuestions = (int) ($result['questions_deleted'] ?? 0);
        $deletedBlocks = (int) ($result['blocks_deleted'] ?? 0);
        $deletedPages = (int) ($result['pages_deleted'] ?? 0);
        $deletedCategories = (int) ($result['categories_deleted'] ?? 0);
        $deletedHints = (int) ($result['hints_deleted'] ?? 0);
        $deletedExplanations = (int) ($result['explanations_deleted'] ?? 0);

        $status = match ($profile['type']) {
            'question_localizations' => __('Refreshed localization seeder :class. Deleted :hints hint(s) and :explanations explanation(s), then re-applied localization.', [
                'class' => $seedRun->class_name,
                'hints' => $deletedHints,
                'explanations' => $deletedExplanations,
            ]),
            'page_localizations' => __('Refreshed page localization seeder :class. Deleted :blocks localized text block(s) and regenerated them.', [
                'class' => $seedRun->class_name,
                'blocks' => $deletedBlocks,
            ]),
            'pages' => __('Refreshed seeder :class. Deleted :blocks text block(s) and regenerated content.', [
                'class' => $seedRun->class_name,
                'blocks' => $deletedBlocks,
            ]) . ($deletedPages > 0
                ? ' ' . __('Deleted :count page record(s).', ['count' => $deletedPages])
                : '')
                . ($deletedCategories > 0
                    ? ' ' . __('Deleted :count category record(s).', ['count' => $deletedCategories])
                    : ''),
            'questions' => __('Refreshed seeder :class. Deleted :count question(s) and regenerated them.', [
                'class' => $seedRun->class_name,
                'count' => $deletedQuestions,
            ]),
            default => __('Refreshed seeder :class. Data has been regenerated.', [
                'class' => $seedRun->class_name,
            ]),
        };

        $relatedLocalizationsRan = (int) ($result['related_localizations_ran'] ?? 0);
        if ($relatedLocalizationsRan > 0 && ! in_array($profile['type'], ['question_localizations', 'page_localizations'], true)) {
            $status .= ' ' . __('Повторно виконано :count пов’язаних сидер(ів) локалізації.', [
                'count' => $relatedLocalizationsRan,
            ]);
        }

        $errors = $result['errors'] ?? collect();
        $statusSummary = $status;
        if ($errors->isNotEmpty()) {
            $status .= ' ' . __('Під час повторного запуску виникли помилки: :errors', [
                'errors' => $this->formatDetailedErrorMessage($errors),
            ]);
        }

        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));
            
            return response()->json([
                'status' => $errors->isNotEmpty() ? 'partial' : 'success',
                'message' => $statusSummary,
                'seed_run_id' => $seedRunId,
                'class_name' => $seedRun->class_name,
                'questions_deleted' => $deletedQuestions,
                'blocks_deleted' => $deletedBlocks,
                'pages_deleted' => $deletedPages,
                'categories_deleted' => $deletedCategories,
                'hints_deleted' => $deletedHints,
                'explanations_deleted' => $deletedExplanations,
                'related_localizations_ran' => $relatedLocalizationsRan,
                'errors' => $errors->all(),
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        $redirect = $this->redirectToIndexWithTab($request)
            ->with('status', $status);

        if ($errors->isNotEmpty()) {
            $redirect = $redirect->withErrors(new MessageBag(['refresh' => $errors->all()]));
        }

        return $redirect;
    }

    protected function refreshExecutedSeeders(Collection $selectedClasses): array
    {
        $selectedClasses = $selectedClasses
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        $emptyResult = [
            'success' => false,
            'message' => __('No seeders were selected.'),
            'selected_classes' => collect(),
            'classes_to_refresh' => collect(),
            'related_localization_classes' => collect(),
            'selected_ran' => collect(),
            'ran' => collect(),
            'ordered' => collect(),
            'errors' => collect(),
            'questions_deleted' => 0,
            'blocks_deleted' => 0,
            'pages_deleted' => 0,
            'categories_deleted' => 0,
            'hints_deleted' => 0,
            'explanations_deleted' => 0,
            'localizations_ran_total' => 0,
            'related_localizations_ran' => 0,
        ];

        if ($selectedClasses->isEmpty()) {
            return $emptyResult;
        }

        $executedSeeders = $this->buildExecutedSeedersForRefresh();
        $classesToRefresh = $this->expandRefreshClassNames($selectedClasses, $executedSeeders);
        $autoRefreshedLocalizationClasses = $this->autoRefreshedRelatedLocalizationClasses($selectedClasses, $executedSeeders);
        $validationErrors = $this->validateSeederClassesForRefresh($classesToRefresh);

        if ($validationErrors->isNotEmpty()) {
            return array_merge($emptyResult, [
                'message' => $validationErrors->implode(' '),
                'selected_classes' => $selectedClasses,
                'classes_to_refresh' => $classesToRefresh,
                'errors' => $validationErrors,
            ]);
        }

        try {
            $deletionStats = $this->deleteSeederDataForRefresh($classesToRefresh);
        } catch (\Throwable $exception) {
            report($exception);

            return array_merge($emptyResult, [
                'message' => $exception->getMessage(),
                'selected_classes' => $selectedClasses,
                'classes_to_refresh' => $classesToRefresh,
                'errors' => collect([$exception->getMessage()]),
            ]);
        }

        $rerunResult = $this->executeSeedersInOrder($classesToRefresh);
        $ran = collect($rerunResult['ran'] ?? collect())->values();
        $ordered = collect($rerunResult['ordered'] ?? collect())->values();
        $errors = collect($rerunResult['errors'] ?? collect())
            ->filter()
            ->unique()
            ->values();

        try {
            $this->touchSeedRunTimestamps(
                $ran
                    ->merge($autoRefreshedLocalizationClasses)
                    ->unique()
                    ->values()
            );
        } catch (\Throwable $exception) {
            report($exception);
            $errors = $errors->push($exception->getMessage())->filter()->unique()->values();
        }

        $relatedLocalizationClasses = $classesToRefresh
            ->diff($selectedClasses)
            ->merge($autoRefreshedLocalizationClasses)
            ->unique()
            ->values();
        $refreshedLocalizationClasses = $ran
            ->filter(fn (string $className) => in_array($this->virtualLocalizationType($className), ['question_localizations', 'page_localizations'], true))
            ->merge($autoRefreshedLocalizationClasses)
            ->unique()
            ->values();

        return array_merge($deletionStats, [
            'success' => $ran->isNotEmpty(),
            'message' => $ran->isNotEmpty()
                ? __('Seeder data refreshed.')
                : __('No seeders were refreshed.'),
            'selected_classes' => $selectedClasses,
            'classes_to_refresh' => $classesToRefresh,
            'related_localization_classes' => $relatedLocalizationClasses,
            'selected_ran' => $ran->intersect($selectedClasses)->values(),
            'ran' => $ran,
            'ordered' => $ordered,
            'errors' => $errors,
            'localizations_ran_total' => $refreshedLocalizationClasses->count(),
            'related_localizations_ran' => $refreshedLocalizationClasses
                ->filter(fn (string $className) => $relatedLocalizationClasses->contains($className))
                ->count(),
        ]);
    }

    protected function expandRefreshClassNames(Collection $classNames, ?Collection $executedSeeders = null): Collection
    {
        $normalized = $classNames
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return collect();
        }

        $executedSeeders ??= $this->buildExecutedSeedersForRefresh();

        if ($executedSeeders->isEmpty()) {
            return $normalized;
        }

        $relatedLocalizationMap = $this->buildRelatedLocalizationMap($executedSeeders);
        $autoRefreshedLocalizationClasses = $this->autoRefreshedRelatedLocalizationClasses($normalized, $executedSeeders);

        $relatedLocalizationClasses = $normalized
            ->reject(fn (string $className) => in_array($this->virtualLocalizationType($className), ['question_localizations', 'page_localizations'], true))
            ->flatMap(function (string $className) use ($relatedLocalizationMap) {
                return collect($relatedLocalizationMap->get($className, collect()))
                    ->pluck('class_name');
            })
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->reject(fn (string $className) => $autoRefreshedLocalizationClasses->contains($className))
            ->unique()
            ->values();

        return $normalized
            ->merge($relatedLocalizationClasses)
            ->unique()
            ->values();
    }

    protected function autoRefreshedRelatedLocalizationClasses(Collection $classNames, ?Collection $executedSeeders = null): Collection
    {
        $normalized = $classNames
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return collect();
        }

        $executedSeeders ??= $this->buildExecutedSeedersForRefresh();

        if ($executedSeeders->isEmpty()) {
            return collect();
        }

        $relatedLocalizationMap = $this->buildRelatedLocalizationMap($executedSeeders);

        return $normalized
            ->reject(fn (string $className) => in_array($this->virtualLocalizationType($className), ['question_localizations', 'page_localizations'], true))
            ->filter(fn (string $className) => $this->refreshesPageLocalizationsWithinBaseSeeder($className))
            ->flatMap(function (string $className) use ($relatedLocalizationMap) {
                return collect($relatedLocalizationMap->get($className, collect()))
                    ->filter(fn (array $localization) => ($localization['type'] ?? null) === 'page_localizations')
                    ->pluck('class_name');
            })
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();
    }

    protected function refreshesPageLocalizationsWithinBaseSeeder(string $className): bool
    {
        if (! $this->ensureSeederClassIsLoaded($className)) {
            return false;
        }

        return is_subclass_of($className, JsonPageSeeder::class);
    }

    protected function buildExecutedSeedersForRefresh(): Collection
    {
        if (! Schema::hasTable('seed_runs')) {
            return collect();
        }

        return DB::table('seed_runs')
            ->orderByDesc('ran_at')
            ->get()
            ->map(function ($seedRun) {
                $className = trim((string) ($seedRun->class_name ?? ''));

                $seedRun->ran_at = $seedRun->ran_at ? Carbon::parse($seedRun->ran_at) : null;
                $seedRun->display_class_name = $this->formatSeederClassName($className);
                $seedRun->display_class_basename = class_basename($className);
                $seedRun->data_profile = $this->describeSeederData($className);

                return $seedRun;
            });
    }

    protected function validateSeederClassesForRefresh(Collection $classNames): Collection
    {
        if ($classNames->isEmpty()) {
            return collect();
        }

        $executedClasses = $this->executedSeederClasses();

        return $classNames
            ->map(function (string $className) use ($executedClasses) {
                if (! $this->ensureSeederClassIsLoaded($className)) {
                    return __('Seeder :class was not found.', ['class' => $className]);
                }

                $blockedMessage = $this->executionBlockedMessageForSeeder($className, $executedClasses);

                if ($blockedMessage !== null) {
                    return $blockedMessage;
                }

                if ($this->isVirtualLocalizationSeeder($className)) {
                    return null;
                }

                $filePath = $this->resolveSeederFilePath($className);

                return $this->isInstantiableSeeder($className, $filePath)
                    ? null
                    : __('Seeder :class cannot be executed.', ['class' => $className]);
            })
            ->filter()
            ->unique()
            ->values();
    }

    protected function deleteSeederDataForRefresh(Collection $classNames): array
    {
        $typeMap = $classNames->mapWithKeys(function (string $className) {
            $profile = $this->describeSeederData($className);

            return [$className => $profile['type'] ?? 'unknown'];
        });

        $questionLocalizationClasses = $typeMap
            ->filter(fn ($type) => $type === 'question_localizations')
            ->keys()
            ->values();
        $pageLocalizationClasses = $typeMap
            ->filter(fn ($type) => $type === 'page_localizations')
            ->keys()
            ->values();
        $questionClasses = $typeMap
            ->filter(fn ($type) => $type === 'questions')
            ->keys()
            ->values();
        $pageClasses = $typeMap
            ->filter(fn ($type) => $type === 'pages')
            ->keys()
            ->values();
        $unknownClasses = $typeMap
            ->filter(fn ($type) => ! in_array($type, ['question_localizations', 'page_localizations', 'questions', 'pages'], true))
            ->keys()
            ->values();

        $deletedQuestions = 0;
        $deletedBlocks = 0;
        $deletedPages = 0;
        $deletedCategories = 0;
        $deletedHints = 0;
        $deletedExplanations = 0;

        DB::transaction(function () use (
            $questionLocalizationClasses,
            $pageLocalizationClasses,
            $questionClasses,
            $pageClasses,
            $unknownClasses,
            &$deletedQuestions,
            &$deletedBlocks,
            &$deletedPages,
            &$deletedCategories,
            &$deletedHints,
            &$deletedExplanations
        ) {
            foreach ($questionLocalizationClasses as $className) {
                $result = $this->removeVirtualLocalizationSeederData($className);
                $deletedHints += (int) ($result['deleted_hints'] ?? 0);
                $deletedExplanations += (int) ($result['deleted_explanations'] ?? 0);
            }

            foreach ($pageLocalizationClasses as $className) {
                $result = $this->removeVirtualLocalizationSeederData($className);
                $deletedBlocks += (int) ($result['deleted_blocks'] ?? 0);
            }

            if ($questionClasses->isNotEmpty()) {
                $deletedQuestions += $this->deleteQuestionsForSeeders($questionClasses);
            }

            if ($pageClasses->isNotEmpty()) {
                $pageResult = $this->deletePageContentForSeeders($pageClasses);
                $deletedBlocks += $pageResult['blocks'];
                $deletedPages += $pageResult['pages_deleted'];
                $deletedCategories += $pageResult['categories_deleted'];
            }

            if ($unknownClasses->isNotEmpty()) {
                $deletedQuestions += $this->deleteQuestionsForSeeders($unknownClasses);
                $pageResult = $this->deletePageContentForSeeders($unknownClasses);
                $deletedBlocks += $pageResult['blocks'];
                $deletedPages += $pageResult['pages_deleted'];
                $deletedCategories += $pageResult['categories_deleted'];
            }
        });

        return [
            'questions_deleted' => $deletedQuestions,
            'blocks_deleted' => $deletedBlocks,
            'pages_deleted' => $deletedPages,
            'categories_deleted' => $deletedCategories,
            'hints_deleted' => $deletedHints,
            'explanations_deleted' => $deletedExplanations,
        ];
    }

    protected function touchSeedRunTimestamps(Collection $classNames): void
    {
        if ($classNames->isEmpty() || ! Schema::hasTable('seed_runs')) {
            return;
        }

        $timestamp = now();

        foreach ($classNames as $className) {
            DB::table('seed_runs')->updateOrInsert(
                ['class_name' => $className],
                ['ran_at' => $timestamp]
            );
        }
    }

    public function destroyFolder(Request $request): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return $this->redirectToIndexWithTab($request)
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

            return $this->redirectToIndexWithTab($request)
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

            return $this->redirectToIndexWithTab($request)
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
            $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));
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

        return $this->redirectToIndexWithTab($request)
            ->with('status', $message);
    }

    public function destroyFolderWithQuestions(Request $request): RedirectResponse|JsonResponse
    {
        if (! Schema::hasTable('seed_runs')) {
            $errorMessage = __('The seed_runs table does not exist.');
            
            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 404);
            }

            return $this->redirectToIndexWithTab($request)
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

            return $this->redirectToIndexWithTab($request)
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

            return $this->redirectToIndexWithTab($request)
                ->withErrors(['delete' => $errorMessage]);
        }

        $folderLabel = $this->resolveFolderLabel($validated['folder_label'] ?? null);
        $classNames = $seedRuns->pluck('class_name')->filter()->unique()->values();
        $seedRunIdsToDelete = $seedRuns->pluck('id');
        $typeMap = $classNames->mapWithKeys(function ($className) {
            $profile = $this->describeSeederData($className);

            return [$className => $profile['type']];
        });

        $questionLocalizationClasses = $typeMap->filter(fn ($type) => $type === 'question_localizations')->keys()->values();
        $pageLocalizationClasses = $typeMap->filter(fn ($type) => $type === 'page_localizations')->keys()->values();
        $questionClasses = $typeMap->filter(fn ($type) => $type === 'questions')->keys()->values();
        $pageClasses = $typeMap->filter(fn ($type) => $type === 'pages')->keys()->values();
        $unknownClasses = $typeMap->filter(fn ($type) => ! in_array($type, ['question_localizations', 'page_localizations', 'questions', 'pages'], true))->keys()->values();

        $deletedQuestions = 0;
        $deletedBlocks = 0;
        $deletedPages = 0;
        $deletedCategories = 0;
        $deletedHints = 0;
        $deletedExplanations = 0;

        DB::transaction(function () use (
            $seedRunIdsToDelete,
            $questionLocalizationClasses,
            $pageLocalizationClasses,
            $questionClasses,
            $pageClasses,
            $unknownClasses,
            &$deletedQuestions,
            &$deletedBlocks,
            &$deletedPages,
            &$deletedCategories,
            &$deletedHints,
            &$deletedExplanations
        ) {
            foreach ($questionLocalizationClasses as $className) {
                $result = $this->removeVirtualLocalizationSeederData($className);
                $deletedHints += (int) ($result['deleted_hints'] ?? 0);
                $deletedExplanations += (int) ($result['deleted_explanations'] ?? 0);
            }

            foreach ($pageLocalizationClasses as $className) {
                $result = $this->removeVirtualLocalizationSeederData($className);
                $deletedBlocks += (int) ($result['deleted_blocks'] ?? 0);
            }

            if ($questionClasses->isNotEmpty()) {
                $deletedQuestions += $this->deleteQuestionsForSeeders($questionClasses);
            }

            if ($pageClasses->isNotEmpty()) {
                $pageResult = $this->deletePageContentForSeeders($pageClasses);
                $deletedBlocks += $pageResult['blocks'];
                $deletedPages += $pageResult['pages_deleted'];
                $deletedCategories += $pageResult['categories_deleted'];
            }

            if ($unknownClasses->isNotEmpty()) {
                $deletedQuestions += $this->deleteQuestionsForSeeders($unknownClasses);
                $pageResult = $this->deletePageContentForSeeders($unknownClasses);
                $deletedBlocks += $pageResult['blocks'];
                $deletedPages += $pageResult['pages_deleted'];
                $deletedCategories += $pageResult['categories_deleted'];
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

        if ($deletedCategories > 0) {
            $statusMessage .= ' ' . __('Deleted :count related category record(s).', ['count' => $deletedCategories]);
        }

        if ($deletedHints > 0) {
            $statusMessage .= ' ' . __('Deleted :count related hint(s).', ['count' => $deletedHints]);
        }

        if ($deletedExplanations > 0) {
            $statusMessage .= ' ' . __('Deleted :count related explanation(s).', ['count' => $deletedExplanations]);
        }

        if ($request->wantsJson()) {
            $overview = $this->assembleSeedRunOverview($this->currentSeederTab($request));
            return response()->json([
                'message' => $statusMessage,
                'deleted_count' => $seedRuns->count(),
                'questions_deleted' => $deletedQuestions,
                'blocks_deleted' => $deletedBlocks,
                'pages_deleted' => $deletedPages,
                'categories_deleted' => $deletedCategories,
                'hints_deleted' => $deletedHints,
                'explanations_deleted' => $deletedExplanations,
                'folder_label' => $folderLabel,
                'overview' => [
                    'pending_count' => $overview['pendingSeeders']->count(),
                    'executed_count' => $overview['executedSeeders']->count(),
                ],
            ]);
        }

        return $this->redirectToIndexWithTab($request)
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

        $localizationType = $this->virtualLocalizationType($className);

        if ($localizationType === 'question_localizations') {
            return [
                'type' => 'question_localizations',
                'delete_button' => __('Видалити локалізації'),
                'delete_confirm' => __('Видалити лог та пов’язані локалізації?'),
                'folder_delete_button' => __('Видалити локалізації'),
                'folder_delete_confirm' => __('Видалити всі локалізаційні сидери в папці «:folder» разом із локалізаціями?'),
            ];
        }

        if ($localizationType === 'page_localizations') {
            return [
                'type' => 'page_localizations',
                'delete_button' => __('Видалити локалізації сторінок'),
                'delete_confirm' => __('Видалити лог та пов’язані локалізовані блоки сторінок?'),
                'folder_delete_button' => __('Видалити локалізації сторінок'),
                'folder_delete_confirm' => __('Видалити всі локалізаційні сидери сторінок у папці «:folder» разом із локалізованими блоками?'),
            ];
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
        $hasPageCategoriesTable = Schema::hasTable('page_categories');

        if ($classNames->isEmpty() || (! $hasTextBlockTable && ! $hasPagesTable && ! $hasPageCategoriesTable)) {
            return ['blocks' => 0, 'pages_deleted' => 0, 'categories_deleted' => 0];
        }

        $classNames = $this->expandGrammarPageSeederClasses($classNames);

        $deletedBlocks = 0;
        $deletedPages = 0;
        $deletedCategories = 0;
        $processedPageIds = collect();
        $explicitCategoryIds = collect();
        $categoriesToEvaluate = collect();

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

            if ($hasPagesTable) {
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

                    if ($hasPageCategoriesTable && ! is_null($page->page_category_id)) {
                        $categoriesToEvaluate->put((int) $page->page_category_id, $page->page_category_id);
                    }

                    if ($hasTextBlockTable) {
                        $deletedBlocks += TextBlock::query()
                            ->where('page_id', $page->id)
                            ->delete();
                    }

                    $page->delete();
                    $deletedPages++;
                }
            }

            if (! $hasPageCategoriesTable) {
                continue;
            }

            $categories = PageCategory::query()
                ->where('seeder', $className)
                ->get();

            foreach ($categories as $category) {
                $categoriesToEvaluate->put($category->id, $category->id);
                $explicitCategoryIds->push($category->id);
            }
        }

        if ($hasPageCategoriesTable && $categoriesToEvaluate->isNotEmpty()) {
            $categoryIds = $categoriesToEvaluate->keys()->map(fn ($id) => (int) $id)->unique()->values();
            $explicitCategoryIds = $explicitCategoryIds->map(fn ($id) => (int) $id)->unique()->values();

            $categories = PageCategory::query()
                ->whereIn('id', $categoryIds)
                ->get()
                ->sortByDesc(fn (PageCategory $category) => $this->resolvePageCategoryDepth($category));

            foreach ($categories as $category) {
                $result = $this->deletePageCategoryRecord(
                    $category,
                    $explicitCategoryIds->contains($category->id),
                    $hasTextBlockTable,
                    $hasPagesTable,
                    $hasPageCategoriesTable
                );

                $deletedBlocks += $result['blocks'];
                $deletedCategories += $result['categories_deleted'];
            }
        }

        return [
            'blocks' => $deletedBlocks,
            'pages_deleted' => $deletedPages,
            'categories_deleted' => $deletedCategories,
        ];
    }

    protected function deletePageCategoryRecord(
        PageCategory $category,
        bool $forceDelete,
        bool $hasTextBlockTable,
        bool $hasPagesTable,
        bool $hasPageCategoriesTable,
    ): array {
        $category = PageCategory::query()->find($category->id);

        if (! $category) {
            return ['blocks' => 0, 'categories_deleted' => 0];
        }

        if (! $forceDelete) {
            $hasRemainingPages = $hasPagesTable
                && Page::query()->where('page_category_id', $category->id)->exists();
            $hasRemainingChildren = $hasPageCategoriesTable
                && PageCategory::query()->where('parent_id', $category->id)->exists();

            if ($hasRemainingPages || $hasRemainingChildren) {
                return ['blocks' => 0, 'categories_deleted' => 0];
            }
        }

        $deletedBlocks = 0;

        if ($hasTextBlockTable) {
            TextBlock::query()
                ->where('page_category_id', $category->id)
                ->whereNotNull('page_id')
                ->update(['page_category_id' => null]);

            $deletedBlocks += TextBlock::query()
                ->where('page_category_id', $category->id)
                ->whereNull('page_id')
                ->delete();
        }

        if ($hasPagesTable) {
            Page::query()
                ->where('page_category_id', $category->id)
                ->update(['page_category_id' => null]);
        }

        if ($hasPageCategoriesTable) {
            PageCategory::query()
                ->where('parent_id', $category->id)
                ->update(['parent_id' => null]);
        }

        $category->delete();

        return [
            'blocks' => $deletedBlocks,
            'categories_deleted' => 1,
        ];
    }

    protected function resolvePageCategoryDepth(PageCategory $category): int
    {
        $depth = 0;
        $parentId = $category->parent_id;
        $visited = [];

        while ($parentId && ! in_array($parentId, $visited, true)) {
            $visited[] = $parentId;
            $depth++;
            $parentId = PageCategory::query()
                ->whereKey($parentId)
                ->value('parent_id');
        }

        return $depth;
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

            $slug = $this->invokeSeederMethod($className, 'slug');

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
            return $this->virtualLocalizationClasses();
        }

        return $this->getSeederClassMap()
            ->filter(fn (string $path, string $class) => $this->isInstantiableSeeder($class, $path))
            ->keys()
            ->merge($this->virtualLocalizationClasses())
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
        if ($this->isVirtualLocalizationSeeder($className)) {
            return true;
        }

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
