<?php

namespace App\Services\V3PromptGenerator;

use App\Support\Database\JsonRuntimeSeeder;
use App\Support\PackageSeed\AbstractJsonPackageSeedService;
use Illuminate\Support\Str;

class V3PackageSeedService extends AbstractJsonPackageSeedService
{
    public function __construct(
        private readonly V3ReleaseCheckService $releaseCheckService,
    ) {}

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/V3';
    }

    protected function reportDirectory(): string
    {
        return 'release-checks/v3';
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

        return 'Database\\Seeders\\V3\\'.($namespace !== '' ? $namespace.'\\' : '').$className;
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

    protected function executeRuntimeSeed(string $definitionAbsolutePath, string $resolvedSeederClass): void
    {
        (new JsonRuntimeSeeder($definitionAbsolutePath, $resolvedSeederClass))->seedFile();
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

    private function nullableString(mixed $value): ?string
    {
        $resolved = trim((string) $value);

        return $resolved !== '' ? $resolved : null;
    }
}
