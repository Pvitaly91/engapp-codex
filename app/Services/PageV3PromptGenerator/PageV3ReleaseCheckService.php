<?php

namespace App\Services\PageV3PromptGenerator;

use App\Support\Database\JsonPageDefinitionIndex;
use App\Support\ReleaseCheck\AbstractJsonPackageReleaseCheckService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PageV3ReleaseCheckService extends AbstractJsonPackageReleaseCheckService
{
    public function __construct(
        private readonly JsonPageDefinitionIndex $definitionIndex,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function run(string $targetInput, string $profile = 'release'): array
    {
        $resolvedProfile = $this->normalizeProfile($profile);
        $target = $this->resolvePackageTarget($targetInput);
        $target['localizations'] = [
            'uk' => null,
            'en' => $target['localizations']['en'] ?? $this->normalizePath((string) $target['package_root_absolute_path'] . '/localizations/en.json'),
            'pl' => $target['localizations']['pl'] ?? $this->normalizePath((string) $target['package_root_absolute_path'] . '/localizations/pl.json'),
        ];

        $checks = [];
        $expectedSeederClass = $this->expectedSeederClass((string) $target['package_root_relative_path']);
        $className = basename((string) $target['package_root_absolute_path']);

        $checks[] = $this->requiredFilesCheck($target);
        $checks[] = $this->loaderCheck((string) $target['loader_absolute_path'], $className);
        $checks[] = $this->realSeederCheck((string) $target['real_seeder_absolute_path'], $expectedSeederClass, $className);

        $definitionPayload = null;
        $blockIndex = null;
        $expectedBlockReferences = [];
        $definitionLoad = $this->loadJsonFile((string) $target['definition_absolute_path'], 'Page_V3 definition');

        if ($definitionLoad['error'] !== null) {
            $checks[] = $this->makeCheck(
                self::STATUS_FAIL,
                'page_v3.definition.json',
                'Page_V3 definition JSON is readable',
                ['error' => $definitionLoad['error']]
            );
        } else {
            /** @var array<string, mixed> $definitionPayload */
            $definitionPayload = $definitionLoad['data'];

            $checks[] = $this->makeCheck(
                self::STATUS_PASS,
                'page_v3.definition.json',
                'Page_V3 definition JSON is readable',
                ['path' => $target['definition_relative_path']]
            );

            [$blockIndex, $expectedBlockReferences, $definitionChecks] = $this->definitionChecks(
                $definitionPayload,
                (string) $target['definition_absolute_path'],
                $expectedSeederClass,
                $resolvedProfile
            );

            $checks = array_merge($checks, $definitionChecks);
        }

        $checks = array_merge(
            $checks,
            $this->localizationChecks(
                $target,
                $expectedSeederClass,
                $resolvedProfile,
                $blockIndex,
                $expectedBlockReferences
            )
        );

        return [
            'target' => $target,
            'profile' => $resolvedProfile,
            'checks' => $checks,
            'summary' => $this->summarizeChecks($checks),
            'artifacts' => [
                'report_path' => null,
            ],
        ];
    }

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/Page_V3';
    }

    protected function reportDirectory(): string
    {
        return 'release-checks/page-v3';
    }

    /**
     * @return array<int, string>
     */
    protected function expectedLocales(): array
    {
        return ['en', 'pl'];
    }

    /**
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    private function requiredFilesCheck(array $target): array
    {
        $expectedPaths = [
            (string) $target['loader_absolute_path'],
            (string) $target['real_seeder_absolute_path'],
            (string) $target['definition_absolute_path'],
            (string) ($target['localizations']['en'] ?? ''),
            (string) ($target['localizations']['pl'] ?? ''),
        ];

        $missing = [];

        foreach ($expectedPaths as $absolutePath) {
            if ($absolutePath === '' || ! File::exists($absolutePath)) {
                $missing[] = $this->relativePath($absolutePath);
            }
        }

        return $this->makeCheck(
            $missing === [] ? self::STATUS_PASS : self::STATUS_FAIL,
            'page_v3.package.files',
            'Required Page_V3 package files exist',
            $missing === []
                ? ['package_root' => $target['package_root_relative_path']]
                : ['missing' => $missing]
        );
    }

    private function loaderCheck(string $loaderAbsolutePath, string $className): array
    {
        if (! File::exists($loaderAbsolutePath)) {
            return $this->makeCheck(
                self::STATUS_FAIL,
                'page_v3.loader.stub',
                'Top-level Page_V3 loader stub matches the package contract',
                ['missing' => $this->relativePath($loaderAbsolutePath)]
            );
        }

        $contents = File::get($loaderAbsolutePath);
        $expectedRequire = "require_once __DIR__ . '/{$className}/{$className}.php';";
        $matches = str_contains($contents, $expectedRequire);

        return $this->makeCheck(
            $matches ? self::STATUS_PASS : self::STATUS_FAIL,
            'page_v3.loader.stub',
            'Top-level Page_V3 loader stub matches the package contract',
            $matches
                ? ['path' => $this->relativePath($loaderAbsolutePath)]
                : ['expected' => $expectedRequire]
        );
    }

    private function realSeederCheck(string $realSeederAbsolutePath, string $expectedSeederClass, string $className): array
    {
        if (! File::exists($realSeederAbsolutePath)) {
            return $this->makeCheck(
                self::STATUS_FAIL,
                'page_v3.real_seeder.runtime',
                'Package-local Page_V3 seeder extends JsonPageSeeder',
                ['missing' => $this->relativePath($realSeederAbsolutePath)]
            );
        }

        $contents = File::get($realSeederAbsolutePath);
        preg_match('/namespace\s+([^;]+);/', $contents, $namespaceMatches);
        preg_match('/class\s+([A-Za-z0-9_]+)\s+extends\s+JsonPageSeeder/', $contents, $classMatches);
        $actualSeederClass = trim(((string) ($namespaceMatches[1] ?? '')) . '\\' . ((string) ($classMatches[1] ?? '')), '\\');
        $matchesContract = str_contains($contents, 'extends JsonPageSeeder')
            && str_contains($contents, "return __DIR__ . '/definition.json';")
            && $actualSeederClass === $expectedSeederClass
            && (($classMatches[1] ?? null) === $className);

        return $this->makeCheck(
            $matchesContract ? self::STATUS_PASS : self::STATUS_FAIL,
            'page_v3.real_seeder.runtime',
            'Package-local Page_V3 seeder extends JsonPageSeeder',
            $matchesContract
                ? ['class' => $expectedSeederClass]
                : [
                    'expected' => $expectedSeederClass,
                    'actual' => $actualSeederClass !== '' ? $actualSeederClass : null,
                ]
        );
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array{0: array<string, mixed>|null, 1: array<int, string>, 2: array<int, array<string, mixed>>}
     */
    private function definitionChecks(
        array $definition,
        string $definitionAbsolutePath,
        string $expectedSeederClass,
        string $profile,
    ): array {
        $checks = [];
        $issues = [];
        $warnings = [];
        $placeholders = $this->placeholderMatches($definition);
        $contentType = $this->definitionIndex->resolveContentType($definition);
        $config = $this->definitionIndex->resolveContentConfig($definition);
        $resolvedSeederClass = $this->definitionIndex->resolveSeederClassName($definition, $expectedSeederClass);
        $slug = trim((string) ($definition['slug'] ?? Arr::get($config, 'slug', '')));
        $title = trim((string) (
            $contentType === 'category'
                ? (Arr::get($definition, 'category.title') ?: Arr::get($config, 'title', ''))
                : Arr::get($config, 'title', '')
        ));

        if ($resolvedSeederClass !== $expectedSeederClass) {
            $issues[] = 'seeder.class must match the package seeder class.';
        }

        if ($slug === '') {
            $issues[] = 'slug must not be empty.';
        }

        if ($title === '') {
            $issues[] = ($contentType === 'category' ? 'Category' : 'Page') . ' title must not be empty.';
        }

        if ($contentType === 'page' && trim((string) Arr::get($config, 'category.slug', '')) === '') {
            $warnings[] = 'page.category.slug is empty; the page will seed without a linked theory category.';
        }

        if ($placeholders !== []) {
            $issues[] = 'Resolved placeholder tokens are still present in definition.json.';
        }

        $checks[] = $this->makeCheck(
            $issues !== []
                ? self::STATUS_FAIL
                : ($warnings !== [] ? self::STATUS_WARN : self::STATUS_PASS),
            'page_v3.definition.content_contract',
            'definition.json matches the expected Page_V3 content contract',
            [
                'content_type' => $contentType,
                'issues' => array_slice($issues, 0, 6),
                'warnings' => array_slice($warnings, 0, 6),
            ]
        );

        $blockIndex = null;
        $expectedBlockReferences = [];
        $blockIssues = [];
        $rawBlocks = is_array($config['blocks'] ?? null) ? $config['blocks'] : [];
        $contentBlockCount = count(array_filter($rawBlocks, 'is_array'));
        $expectedItemCount = count(array_filter($rawBlocks, 'is_array')) + (! empty($config['subtitle_html']) ? 1 : 0);

        try {
            $blockIndex = $this->definitionIndex->indexBlocks($definition, $definitionAbsolutePath, $expectedSeederClass);
            $expectedBlockReferences = array_keys((array) ($blockIndex['items'] ?? []));

            if (count($expectedBlockReferences) !== $expectedItemCount) {
                $blockIssues[] = 'Block references collide or duplicate across the package definition.';
            }
        } catch (\Throwable $exception) {
            $blockIssues[] = $exception->getMessage();
        }

        $checks[] = $this->makeCheck(
            $blockIssues === [] ? self::STATUS_PASS : self::STATUS_FAIL,
            'page_v3.definition.block_structure',
            'Page_V3 blocks are structurally indexable',
            $blockIssues === []
                ? ['block_count' => count($expectedBlockReferences)]
                : ['issues' => array_slice($blockIssues, 0, 6)]
        );

        $checks[] = $this->makeCheck(
            $this->readinessStatus($contentBlockCount > 0, $profile),
            'page_v3.definition.block_readiness',
            'The Page_V3 definition has content blocks ready for seeding',
            [
                'content_block_count' => $contentBlockCount,
                'indexed_item_count' => count($expectedBlockReferences),
            ]
        );

        return [
            $blockIndex,
            $expectedBlockReferences,
            $checks,
        ];
    }

    /**
     * @param  array<string, mixed>  $target
     * @param  array<string, mixed>|null  $blockIndex
     * @param  array<int, string>  $expectedBlockReferences
     * @return array<int, array<string, mixed>>
     */
    private function localizationChecks(
        array $target,
        string $expectedSeederClass,
        string $profile,
        ?array $blockIndex,
        array $expectedBlockReferences,
    ): array {
        $checks = [];
        $expectedBlockCount = count($expectedBlockReferences);

        foreach (['en', 'pl'] as $locale) {
            $absolutePath = (string) ($target['localizations'][$locale] ?? '');
            $relativePath = $this->relativePath($absolutePath);

            if (! File::exists($absolutePath)) {
                $checks[] = $this->makeCheck(
                    self::STATUS_FAIL,
                    'page_v3.localization.' . $locale . '.contract',
                    strtoupper($locale) . ' localization file matches the Page_V3 package contract',
                    ['missing' => $relativePath]
                );

                continue;
            }

            $load = $this->loadJsonFile($absolutePath, strtoupper($locale) . ' Page_V3 localization');

            if ($load['error'] !== null) {
                $checks[] = $this->makeCheck(
                    self::STATUS_FAIL,
                    'page_v3.localization.' . $locale . '.contract',
                    strtoupper($locale) . ' localization file matches the Page_V3 package contract',
                    ['error' => $load['error']]
                );

                continue;
            }

            /** @var array<string, mixed> $payload */
            $payload = $load['data'];
            $blocks = $payload['blocks'] ?? null;
            $localeValue = $this->normalizeLocale((string) ($payload['locale'] ?? ''));
            $targetSeederClass = trim((string) ($payload['target']['seeder_class'] ?? ''));
            $definitionPathSetting = trim((string) ($payload['target']['definition_path'] ?? ''));
            $definitionPathCandidate = $definitionPathSetting !== ''
                ? dirname($absolutePath) . DIRECTORY_SEPARATOR . $definitionPathSetting
                : '';
            $resolvedDefinitionPath = $definitionPathCandidate !== ''
                ? $this->normalizePath((string) (realpath($definitionPathCandidate) ?: $definitionPathCandidate))
                : '';
            $expectedLocalizationClass = $this->expectedLocalizationSeederClass($expectedSeederClass, $locale);
            $actualLocalizationClass = trim((string) ($payload['seeder']['class'] ?? ''));
            $issues = [];

            if (! is_array($blocks)) {
                $issues[] = 'blocks must be an array.';
            }

            if ($localeValue !== $locale) {
                $issues[] = 'locale must match the file name.';
            }

            if ($targetSeederClass !== $expectedSeederClass) {
                $issues[] = 'target.seeder_class must match the package seeder class.';
            }

            if ($resolvedDefinitionPath !== $this->normalizePath((string) $target['definition_absolute_path'])) {
                $issues[] = 'target.definition_path must resolve to the package definition.json file.';
            }

            if ($actualLocalizationClass !== $expectedLocalizationClass) {
                $issues[] = 'seeder.class must match the expected localization seeder class.';
            }

            if ($this->placeholderMatches($payload) !== []) {
                $issues[] = 'Resolved placeholder tokens are still present in the localization payload.';
            }

            $checks[] = $this->makeCheck(
                $issues === [] ? self::STATUS_PASS : self::STATUS_FAIL,
                'page_v3.localization.' . $locale . '.contract',
                strtoupper($locale) . ' localization file matches the Page_V3 package contract',
                $issues === []
                    ? ['path' => $relativePath]
                    : ['issues' => array_slice($issues, 0, 6)]
            );

            if (! is_array($blocks) || $blockIndex === null) {
                continue;
            }

            $matchedReferences = [];
            $unresolvedReferences = [];

            foreach ($blocks as $offset => $blockReference) {
                if (! is_array($blockReference)) {
                    $unresolvedReferences[] = 'blocks.' . $offset;

                    continue;
                }

                $resolvedReference = $this->definitionIndex->resolveIndexedBlockReference($blockIndex, $blockReference);

                if ($resolvedReference === null) {
                    $unresolvedReferences[] = 'blocks.' . $offset;

                    continue;
                }

                $matchedReferences[$resolvedReference] = true;
            }

            $matchedCount = count($matchedReferences);
            $coverageReady = $expectedBlockCount > 0
                && $matchedCount === $expectedBlockCount
                && $unresolvedReferences === [];

            $checks[] = $this->makeCheck(
                $coverageReady
                    ? self::STATUS_PASS
                    : $this->readinessStatus(false, $profile),
                'page_v3.localization.' . $locale . '.coverage',
                strtoupper($locale) . ' localization covers the Page_V3 block set',
                [
                    'expected' => $expectedBlockCount,
                    'matched' => $matchedCount,
                    'unresolved' => array_slice($unresolvedReferences, 0, 6),
                ]
            );
        }

        return $checks;
    }

    private function expectedSeederClass(string $packageRootRelativePath): string
    {
        $relative = Str::after(str_replace('\\', '/', $packageRootRelativePath), 'database/seeders/Page_V3/');
        $segments = array_values(array_filter(explode('/', $relative)));
        $className = (string) array_pop($segments);
        $namespace = implode('\\', $segments);

        return 'Database\\Seeders\\Page_V3\\' . ($namespace !== '' ? $namespace . '\\' : '') . $className;
    }

    private function expectedLocalizationSeederClass(string $expectedSeederClass, string $locale): string
    {
        $className = Str::afterLast($expectedSeederClass, '\\');
        $classStem = preg_replace('/Seeder$/', '', $className) ?: $className;

        return 'Database\\Seeders\\Page_V3\\Localizations\\'
            . Str::ucfirst($locale)
            . '\\'
            . $classStem
            . 'LocalizationSeeder';
    }

    private function normalizeLocale(string $locale): string
    {
        $normalized = strtolower(trim($locale));

        return $normalized === 'ua' ? 'uk' : $normalized;
    }
}
