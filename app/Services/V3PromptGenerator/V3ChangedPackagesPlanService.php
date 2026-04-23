<?php

namespace App\Services\V3PromptGenerator;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Support\PackageSeed\AbstractJsonPackageChangedPlanService;
use App\Support\PackageSeed\GitPackageDiffService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class V3ChangedPackagesPlanService extends AbstractJsonPackageChangedPlanService
{
    public function __construct(
        GitPackageDiffService $gitPackageDiffService,
        private readonly V3FolderPlanService $folderPlanService,
        private readonly V3ReleaseCheckService $releaseCheckService,
    ) {
        parent::__construct($gitPackageDiffService);
    }

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/V3';
    }

    protected function reportDirectory(): string
    {
        return 'changed-package-plans/v3';
    }

    /**
     * @return array<int, string>
     */
    protected function expectedLocales(): array
    {
        return ['uk', 'en', 'pl'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function planCurrentPackage(string $targetInput): array
    {
        return $this->folderPlanService->run($targetInput, [
            'mode' => 'sync',
            'with_release_check' => false,
            'strict' => false,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function deletedPackageMetadata(string $packageRootRelativePath, string $historicalRef): array
    {
        $expectedSeederClass = $this->expectedSeederClass($packageRootRelativePath);
        $definitionPath = trim($packageRootRelativePath, '/') . '/definition.json';
        $definitionContents = $this->gitPackageDiffService->showFile($historicalRef, $definitionPath);

        if ($definitionContents === null) {
            return [
                'available' => false,
                'safe' => false,
                'resolved_seeder_class' => $expectedSeederClass,
                'package_type' => 'v3_test',
                'summary' => [],
                'warnings' => ['Historical definition.json is unavailable for the deleted V3 package.'],
                '_saved_test_uuid' => null,
                '_saved_test_slug' => null,
            ];
        }

        try {
            $definition = json_decode($definitionContents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            return [
                'available' => false,
                'safe' => false,
                'resolved_seeder_class' => $expectedSeederClass,
                'package_type' => 'v3_test',
                'summary' => [],
                'warnings' => ['Historical definition.json contains invalid JSON: ' . $exception->getMessage()],
                '_saved_test_uuid' => null,
                '_saved_test_slug' => null,
            ];
        }

        if (! is_array($definition)) {
            return [
                'available' => false,
                'safe' => false,
                'resolved_seeder_class' => $expectedSeederClass,
                'package_type' => 'v3_test',
                'summary' => [],
                'warnings' => ['Historical definition.json must decode to a JSON object.'],
                '_saved_test_uuid' => null,
                '_saved_test_slug' => null,
            ];
        }

        $questions = array_values(array_filter((array) ($definition['questions'] ?? []), 'is_array'));
        $levels = array_values(array_unique(array_filter(array_map(
            static fn (array $question): string => trim((string) ($question['level'] ?? '')),
            $questions
        ))));

        natcasesort($levels);

        return [
            'available' => true,
            'safe' => true,
            'resolved_seeder_class' => trim((string) data_get($definition, 'seeder.class', '')) ?: $expectedSeederClass,
            'package_type' => 'v3_test',
            'summary' => [
                'saved_test_slug' => $this->nullableString(data_get($definition, 'saved_test.slug')),
                'saved_test_name' => $this->nullableString(data_get($definition, 'saved_test.name')),
                'saved_test_uuid' => $this->nullableString(data_get($definition, 'saved_test.uuid')),
                'question_count' => count($questions),
                'levels' => array_values($levels),
            ],
            'warnings' => [],
            '_saved_test_uuid' => $this->nullableString(data_get($definition, 'saved_test.uuid')),
            '_saved_test_slug' => $this->nullableString(data_get($definition, 'saved_test.slug')),
        ];
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    protected function deletedPackageOwnership(array $metadata): array
    {
        $resolvedSeederClass = trim((string) ($metadata['resolved_seeder_class'] ?? ''));
        $seedRunPresent = $resolvedSeederClass !== ''
            && Schema::hasTable('seed_runs')
            && DB::table('seed_runs')->where('class_name', $resolvedSeederClass)->exists();
        $questionCount = $resolvedSeederClass !== ''
            && Schema::hasTable('questions')
            && Schema::hasColumn('questions', 'seeder')
                ? Question::query()->where('seeder', $resolvedSeederClass)->count()
                : 0;
        $savedTestPresent = $this->canonicalSavedTestPresent(
            trim((string) ($metadata['_saved_test_uuid'] ?? '')),
            trim((string) ($metadata['_saved_test_slug'] ?? ''))
        );
        $packagePresent = $questionCount > 0 || $savedTestPresent;
        $warnings = [];

        if ($seedRunPresent && ! $packagePresent) {
            $warnings[] = 'Canonical seed_runs record exists, but package-owned database content is absent.';
        }

        if (! $seedRunPresent && $packagePresent) {
            $warnings[] = 'Package-owned database content exists without a canonical seed_runs record.';
        }

        return [
            'seed_run_present' => $seedRunPresent,
            'package_present_in_db' => $packagePresent,
            'warnings' => array_values(array_unique(array_filter($warnings))),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function releaseCheckReport(string $targetInput, string $profile): array
    {
        return $this->releaseCheckService->run($targetInput, $profile);
    }

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    protected function cleanupPhaseComparator(array $left, array $right): int
    {
        return strcmp($this->packageSortPath($right), $this->packageSortPath($left));
    }

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    protected function upsertPhaseComparator(array $left, array $right): int
    {
        return strcmp($this->packageSortPath($left), $this->packageSortPath($right));
    }

    private function expectedSeederClass(string $packageRootRelativePath): string
    {
        $relative = Str::after(str_replace('\\', '/', $packageRootRelativePath), 'database/seeders/V3/');
        $segments = array_values(array_filter(explode('/', $relative)));
        $className = (string) array_pop($segments);
        $namespace = implode('\\', $segments);

        return 'Database\\Seeders\\V3\\' . ($namespace !== '' ? $namespace . '\\' : '') . $className;
    }

    private function canonicalSavedTestPresent(string $savedTestUuid, string $savedTestSlug): bool
    {
        if (! Schema::hasTable('saved_grammar_tests')) {
            return false;
        }

        $query = SavedGrammarTest::query();

        if ($savedTestUuid !== '') {
            $query->where('uuid', $savedTestUuid);
        } elseif ($savedTestSlug !== '') {
            $query->where('slug', $savedTestSlug);
        } else {
            return false;
        }

        return $query->exists();
    }
}
