<?php

namespace App\Services\PageV3PromptGenerator;

use App\Support\Scaffold\AbstractSkeletonWriter;
use Illuminate\Support\Str;
use RuntimeException;

class PageV3SkeletonWriterService extends AbstractSkeletonWriter
{
    /**
     * @param  array<string, mixed>  $generated
     * @return array<int, string>
     */
    public function plannedFiles(array $generated): array
    {
        $this->assertResolvedCategoryMode($generated);

        $preview = (array) ($generated['preview'] ?? []);
        $paths = [
            $this->relativePreviewPath($preview, 'page_seeder_relative_path'),
            $this->relativePreviewPath($preview, 'page_real_seeder_relative_path'),
            $this->relativePreviewPath($preview, 'page_definition_relative_path'),
            $this->relativePreviewPath($preview, 'page_localization_en_relative_path'),
            $this->relativePreviewPath($preview, 'page_localization_pl_relative_path'),
        ];

        if ($this->writesCategoryPackage($generated)) {
            $paths = array_merge($paths, [
                $this->relativePreviewPath($preview, 'category_seeder_relative_path'),
                $this->relativePreviewPath($preview, 'category_real_seeder_relative_path'),
                $this->relativePreviewPath($preview, 'category_definition_relative_path'),
                $this->relativePreviewPath($preview, 'category_localization_en_relative_path'),
                $this->relativePreviewPath($preview, 'category_localization_pl_relative_path'),
            ]);
        }

        return array_values(array_map(
            fn (string $relativePath): string => $this->absolutePath($relativePath),
            array_filter($paths)
        ));
    }

    /**
     * @param  array<string, mixed>  $generated
     * @return array<string, mixed>
     */
    public function write(array $generated, bool $force = false): array
    {
        $this->assertResolvedCategoryMode($generated);

        return $this->writeFiles($this->fileContents($generated), $force);
    }

    /**
     * @param  array<string, mixed>  $generated
     * @return array<string, string>
     */
    private function fileContents(array $generated): array
    {
        $preview = (array) ($generated['preview'] ?? []);
        $source = (array) ($generated['source'] ?? []);
        $category = (array) ($generated['category'] ?? []);

        $pageClassName = trim((string) ($preview['page_class_name'] ?? ''));
        $pageSeederClass = trim((string) ($preview['page_fully_qualified_class_name'] ?? ''));
        $pageSlug = $this->pageSlug($source);
        $pageTitle = $this->pageTitle($source, $pageSlug);
        $categorySlug = trim((string) ($preview['category_slug'] ?? ''));
        $categoryTitle = trim((string) ($preview['category_title'] ?? $category['selected_category']['title'] ?? $category['new_category_title'] ?? ''));
        $categoryTitle = $categoryTitle !== '' ? $categoryTitle : Str::headline(str_replace('-', ' ', $categorySlug));

        if ($pageClassName === '' || $pageSeederClass === '' || $pageSlug === '' || $categorySlug === '') {
            throw new RuntimeException('Page_V3 scaffold preview is incomplete. Re-run prompt generation first.');
        }

        $files = [
            $this->absolutePath($this->relativePreviewPath($preview, 'page_seeder_relative_path'))
                => $this->loaderStubContents($pageClassName),
            $this->absolutePath($this->relativePreviewPath($preview, 'page_real_seeder_relative_path'))
                => $this->realSeederContents($pageSeederClass),
            $this->absolutePath($this->relativePreviewPath($preview, 'page_definition_relative_path'))
                => $this->encodeJson($this->pageDefinition($preview, $pageSlug, $pageTitle, $categorySlug, $categoryTitle)),
            $this->absolutePath($this->relativePreviewPath($preview, 'page_localization_en_relative_path'))
                => $this->pageLocalizationContents($pageClassName, $pageSeederClass, 'en'),
            $this->absolutePath($this->relativePreviewPath($preview, 'page_localization_pl_relative_path'))
                => $this->pageLocalizationContents($pageClassName, $pageSeederClass, 'pl'),
        ];

        if ($this->writesCategoryPackage($generated)) {
            $categoryClassName = trim((string) ($preview['category_class_name'] ?? ''));
            $categorySeederClass = $this->categorySeederClass($preview);

            if ($categoryClassName === '' || $categorySeederClass === '') {
                throw new RuntimeException('Page_V3 category scaffold preview is incomplete.');
            }

            $files[$this->absolutePath($this->relativePreviewPath($preview, 'category_seeder_relative_path'))]
                = $this->loaderStubContents($categoryClassName);
            $files[$this->absolutePath($this->relativePreviewPath($preview, 'category_real_seeder_relative_path'))]
                = $this->realSeederContents($categorySeederClass);
            $files[$this->absolutePath($this->relativePreviewPath($preview, 'category_definition_relative_path'))]
                = $this->encodeJson($this->categoryDefinition($preview, $categorySlug, $categoryTitle));
            $files[$this->absolutePath($this->relativePreviewPath($preview, 'category_localization_en_relative_path'))]
                = $this->categoryLocalizationContents($categoryClassName, $categorySeederClass, 'en');
            $files[$this->absolutePath($this->relativePreviewPath($preview, 'category_localization_pl_relative_path'))]
                = $this->categoryLocalizationContents($categoryClassName, $categorySeederClass, 'pl');
        }

        return $files;
    }

    /**
     * @param  array<string, mixed>  $generated
     */
    private function assertResolvedCategoryMode(array $generated): void
    {
        $categoryMode = (string) (($generated['category']['mode'] ?? $generated['preview']['category_mode'] ?? ''));
        $preview = (array) ($generated['preview'] ?? []);

        if ($categoryMode === 'ai_select'
            || str_contains((string) ($preview['category_slug'] ?? ''), '<resolved-')
            || str_contains((string) ($preview['page_seeder_relative_path'] ?? ''), '<resolved-')) {
            throw new RuntimeException(
                'Cannot write Page_V3 skeleton for ai_select mode. Resolve the category first with --category-mode=existing or --category-mode=new.'
            );
        }
    }

    /**
     * @param  array<string, mixed>  $generated
     */
    private function writesCategoryPackage(array $generated): bool
    {
        return (($generated['category']['mode'] ?? $generated['preview']['category_mode'] ?? null) === 'new');
    }

    /**
     * @param  array<string, mixed>  $preview
     */
    private function relativePreviewPath(array $preview, string $key): string
    {
        $relativePath = trim((string) ($preview[$key] ?? ''));

        if ($relativePath === '') {
            throw new RuntimeException(sprintf('Page_V3 scaffold preview is missing `%s`.', $key));
        }

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function pageSlug(array $source): string
    {
        $topic = trim((string) ($source['topic'] ?? ''));

        return Str::slug($topic) !== '' ? Str::slug($topic) : 'new-page-v3-topic';
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function pageTitle(array $source, string $pageSlug): string
    {
        $title = trim((string) ($source['title'] ?? $source['topic'] ?? ''));

        return $title !== '' ? $title : Str::headline(str_replace('-', ' ', $pageSlug));
    }

    /**
     * @param  array<string, mixed>  $preview
     * @return array<string, mixed>
     */
    private function pageDefinition(
        array $preview,
        string $pageSlug,
        string $pageTitle,
        string $categorySlug,
        string $categoryTitle,
    ): array {
        return [
            'schema_version' => 1,
            'content_type' => 'page',
            'slug' => $pageSlug,
            'page' => [
                'title' => $pageTitle,
                'subtitle_html' => '<p><strong>' . $pageTitle . '</strong></p>',
                'subtitle_text' => $pageTitle,
                'locale' => 'uk',
                'category' => [
                    'slug' => $categorySlug,
                    'title' => $categoryTitle,
                    'language' => 'uk',
                    'type' => 'theory',
                ],
                'tags' => [$pageTitle],
                'blocks' => [],
            ],
            'type' => 'theory',
            'seeder' => [
                'class' => (string) $preview['page_fully_qualified_class_name'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $preview
     * @return array<string, mixed>
     */
    private function categoryDefinition(array $preview, string $categorySlug, string $categoryTitle): array
    {
        return [
            'schema_version' => 1,
            'content_type' => 'category',
            'slug' => $categorySlug,
            'category' => [
                'slug' => $categorySlug,
                'title' => $categoryTitle,
                'language' => 'uk',
                'type' => 'theory',
            ],
            'description' => [
                'title' => $categoryTitle,
                'subtitle_html' => '<p><strong>' . $categoryTitle . '</strong></p>',
                'subtitle_text' => $categoryTitle,
                'locale' => 'uk',
                'tags' => [$categoryTitle],
                'blocks' => [],
            ],
            'type' => 'theory',
            'seeder' => [
                'class' => $this->categorySeederClass($preview),
            ],
        ];
    }

    private function loaderStubContents(string $className): string
    {
        return <<<PHP
<?php

require_once __DIR__ . '/{$className}/{$className}.php';
PHP;
    }

    private function realSeederContents(string $fullyQualifiedClassName): string
    {
        $namespace = Str::beforeLast($fullyQualifiedClassName, '\\');
        $className = Str::afterLast($fullyQualifiedClassName, '\\');

        return <<<PHP
<?php

namespace {$namespace};

use App\Support\Database\JsonPageSeeder;

class {$className} extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
PHP;
    }

    private function categorySeederClass(array $preview): string
    {
        $namespace = trim((string) ($preview['category_namespace'] ?? ''), '\\');
        $className = trim((string) ($preview['category_class_name'] ?? ''));

        if ($namespace === '' || $className === '') {
            return '';
        }

        return 'Database\\Seeders\\Page_V3\\' . $namespace . '\\' . $className;
    }

    private function pageLocalizationContents(string $pageClassName, string $pageSeederClass, string $locale): string
    {
        return $this->encodeJson([
            'locale' => strtolower($locale),
            'seeder' => [
                'class' => $this->localizationSeederClass($pageClassName, $locale),
            ],
            'target' => [
                'seeder_class' => $pageSeederClass,
                'definition_path' => '../definition.json',
            ],
            'blocks' => [],
        ]);
    }

    private function categoryLocalizationContents(string $categoryClassName, string $categorySeederClass, string $locale): string
    {
        return $this->encodeJson([
            'locale' => strtolower($locale),
            'seeder' => [
                'class' => $this->localizationSeederClass($categoryClassName, $locale),
            ],
            'target' => [
                'seeder_class' => $categorySeederClass,
                'definition_path' => '../definition.json',
            ],
            'blocks' => [],
        ]);
    }

    private function localizationSeederClass(string $definitionBaseName, string $locale): string
    {
        $localeNamespace = Str::ucfirst(strtolower($locale));
        $classStem = preg_replace('/Seeder$/', '', trim($definitionBaseName)) ?: trim($definitionBaseName);

        return 'Database\\Seeders\\Page_V3\\Localizations\\' . $localeNamespace . '\\' . $classStem . 'LocalizationSeeder';
    }
}
