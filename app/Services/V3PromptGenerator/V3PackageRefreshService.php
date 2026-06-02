<?php

namespace App\Services\V3PromptGenerator;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Modules\SeedRunsV2\Services\SeedRunsService;
use App\Support\Database\JsonRuntimeSeeder;
use App\Support\PackageSeed\AbstractJsonPackageRefreshService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class V3PackageRefreshService extends AbstractJsonPackageRefreshService
{
    public function __construct(
        private readonly V3ReleaseCheckService $releaseCheckService,
        private readonly SeedRunsService $seedRunsService,
    ) {}

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/V3';
    }

    protected function reportDirectory(): string
    {
        return 'package-refresh-reports/v3';
    }

    /**
     * @return array<int, string>
     */
    protected function expectedLocales(): array
    {
        return ['uk', 'en', 'pl'];
    }

    /**
     * @param  array<string, mixed>  $target
     */
    protected function expectedSeederClass(array $target): string
    {
        $relative = Str::after(
            str_replace('\\', '/', (string) $target['package_root_relative_path']),
            'database/seeders/V3/'
        );
        $segments = array_values(array_filter(explode('/', $relative)));
        $className = (string) array_pop($segments);
        $namespace = implode('\\', $segments);

        return 'Database\\Seeders\\V3\\' . ($namespace !== '' ? $namespace . '\\' : '') . $className;
    }

    /**
     * @return array<string, mixed>
     */
    protected function releaseCheckReport(string $targetInput, string $profile): array
    {
        return $this->releaseCheckService->run($targetInput, $profile);
    }

    /**
     * @return array<string, mixed>
     */
    protected function readDefinition(string $definitionAbsolutePath): array
    {
        return (new JsonRuntimeSeeder($definitionAbsolutePath))->readDefinition();
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    protected function ownershipContext(
        array $definition,
        array $target,
        string $resolvedSeederClass,
    ): array {
        $questionCount = Schema::hasTable('questions') && Schema::hasColumn('questions', 'seeder')
            ? Question::query()->where('seeder', $resolvedSeederClass)->count()
            : 0;
        $savedTestPresent = $this->canonicalSavedTestPresent($definition);
        $seedRunPresent = $this->seedRunPresent($resolvedSeederClass);
        $packagePresent = $questionCount > 0 || $savedTestPresent;
        $warnings = [];

        if (! $seedRunPresent) {
            $warnings[] = 'Canonical seed_runs record is missing for the resolved seeder class.';
        }

        if (! $packagePresent) {
            $warnings[] = 'Package-owned database content is absent; refresh will fall back to seed-only behavior.';
        }

        return [
            'seed_run_present' => $seedRunPresent,
            'package_present_in_db' => $packagePresent,
            'warnings' => array_values(array_unique(array_filter($warnings))),
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function executeAdminRefresh(string $resolvedSeederClass, array $options): array
    {
        return $this->seedRunsService->refreshSeedersByClassNames([$resolvedSeederClass], [
            'atomic' => true,
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'rollback_on_error' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    protected function definitionSummary(
        array $definition,
        array $target,
        string $resolvedSeederClass,
    ): array {
        $questions = array_values(array_filter((array) ($definition['questions'] ?? []), 'is_array'));
        $levels = array_values(array_unique(array_filter(array_map(
            static fn (array $question): string => trim((string) ($question['level'] ?? '')),
            $questions
        ))));

        natcasesort($levels);

        return [
            'question_count' => count($questions),
            'saved_test_slug' => $this->nullableString(data_get($definition, 'saved_test.slug')),
            'saved_test_uuid' => $this->nullableString(data_get($definition, 'saved_test.uuid')),
            'levels' => array_values($levels),
            'default_locale' => $this->nullableString(data_get($definition, 'defaults.default_locale')),
            'category_name' => $this->nullableString(data_get($definition, 'category.name')),
            'source_keys' => array_values(array_map(
                'strval',
                array_keys((array) ($definition['sources'] ?? []))
            )),
            'resolved_seeder_class' => $resolvedSeederClass,
            'definition_relative_path' => (string) ($target['definition_relative_path'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $definitionSummary
     * @param  array<string, mixed>  $target
     */
    protected function packageType(
        array $definitionSummary,
        string $resolvedSeederClass,
        array $target,
    ): string {
        return 'v3_test';
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function canonicalSavedTestPresent(array $definition): bool
    {
        if (! Schema::hasTable('saved_grammar_tests')) {
            return false;
        }

        $uuid = trim((string) data_get($definition, 'saved_test.uuid', ''));
        $slug = trim((string) data_get($definition, 'saved_test.slug', ''));
        $query = SavedGrammarTest::query();

        if ($uuid !== '') {
            $query->where('uuid', $uuid);
        } elseif ($slug !== '') {
            $query->where('slug', $slug);
        } else {
            return false;
        }

        return $query->exists();
    }

    private function nullableString(mixed $value): ?string
    {
        $resolved = trim((string) $value);

        return $resolved !== '' ? $resolved : null;
    }
}
