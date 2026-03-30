<?php

namespace App\Services\PageV3PromptGenerator;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PageV3BlueprintService
{
    /**
     * @param  array<string, mixed>|null  $categoryContext
     * @return array<string, mixed>
     */
    public function buildPreview(?string $topic, string $categoryMode, ?array $categoryContext, ?string $newCategoryTitle): array
    {
        $topicSlug = $this->topicSlug($topic);
        $topicStem = $this->topicStem($topic);

        if ($categoryMode === 'ai_select') {
            $pageClassName = $topicStem . 'TheorySeeder';

            return [
                'category_mode' => $categoryMode,
                'will_create_category' => null,
                'category_title' => 'AI will choose or create a category',
                'category_slug' => '<resolved-category-slug>',
                'category_namespace' => '<resolved-category-namespace>',
                'category_class_name' => '<ResolvedCategory>CategorySeeder',
                'category_seeder_relative_path' => 'database/seeders/Page_V3/<resolved-category-path>/<ResolvedCategory>CategorySeeder.php',
                'category_definition_relative_path' => 'database/seeders/Page_V3/definitions/<resolved_category>_category.json',
                'category_localization_en_relative_path' => 'database/seeders/Page_V3/localizations/en/<resolved_category>_category.json',
                'category_localization_pl_relative_path' => 'database/seeders/Page_V3/localizations/pl/<resolved_category>_category.json',
                'page_class_name' => $pageClassName,
                'page_fully_qualified_class_name' => 'Database\\Seeders\\Page_V3\\<resolved-category-namespace>\\' . $pageClassName,
                'page_seeder_relative_path' => 'database/seeders/Page_V3/<resolved-category-path>/' . $pageClassName . '.php',
                'page_definition_relative_path' => 'database/seeders/Page_V3/definitions/' . $topicSlug . '_theory.json',
                'page_localization_en_relative_path' => 'database/seeders/Page_V3/localizations/en/' . $topicSlug . '_theory.json',
                'page_localization_pl_relative_path' => 'database/seeders/Page_V3/localizations/pl/' . $topicSlug . '_theory.json',
            ];
        }

        $resolvedCategoryTitle = $categoryMode === 'existing'
            ? (string) ($categoryContext['title'] ?? 'Existing Theory Category')
            : trim((string) $newCategoryTitle);
        $resolvedCategoryTitle = $resolvedCategoryTitle !== '' ? $resolvedCategoryTitle : 'New Theory Category';
        $resolvedCategorySlug = $categoryMode === 'existing'
            ? (string) ($categoryContext['slug'] ?? $this->topicSlug($resolvedCategoryTitle))
            : $this->topicSlug($resolvedCategoryTitle);
        $resolvedCategoryNamespace = $categoryMode === 'existing'
            ? (string) ($categoryContext['namespace'] ?? $this->categoryNamespaceFromTitle($resolvedCategoryTitle))
            : $this->categoryNamespaceFromTitle($resolvedCategoryTitle);
        $resolvedCategoryNamespace = trim($resolvedCategoryNamespace, '\\');
        $namespacePath = str_replace('\\', '/', $resolvedCategoryNamespace);
        $pageClassName = $topicStem . 'TheorySeeder';
        $categoryClassName = $this->topicStem($resolvedCategoryTitle) . 'CategorySeeder';
        $existingSeederPath = $categoryMode === 'existing'
            ? (string) ($categoryContext['seeder_relative_path'] ?? '')
            : '';
        $categorySeederPath = $existingSeederPath !== ''
            ? $existingSeederPath
            : 'database/seeders/Page_V3/' . $namespacePath . '/' . $categoryClassName . '.php';

        return [
            'category_mode' => $categoryMode,
            'will_create_category' => $categoryMode === 'new',
            'category_title' => $resolvedCategoryTitle,
            'category_slug' => $resolvedCategorySlug,
            'category_namespace' => $resolvedCategoryNamespace,
            'category_class_name' => $categoryClassName,
            'category_seeder_relative_path' => $categorySeederPath,
            'category_definition_relative_path' => 'database/seeders/Page_V3/definitions/' . $resolvedCategorySlug . '_category.json',
            'category_localization_en_relative_path' => 'database/seeders/Page_V3/localizations/en/' . $resolvedCategorySlug . '_category.json',
            'category_localization_pl_relative_path' => 'database/seeders/Page_V3/localizations/pl/' . $resolvedCategorySlug . '_category.json',
            'page_class_name' => $pageClassName,
            'page_fully_qualified_class_name' => 'Database\\Seeders\\Page_V3\\' . $resolvedCategoryNamespace . '\\' . $pageClassName,
            'page_seeder_relative_path' => 'database/seeders/Page_V3/' . $namespacePath . '/' . $pageClassName . '.php',
            'page_definition_relative_path' => 'database/seeders/Page_V3/definitions/' . $topicSlug . '_theory.json',
            'page_localization_en_relative_path' => 'database/seeders/Page_V3/localizations/en/' . $topicSlug . '_theory.json',
            'page_localization_pl_relative_path' => 'database/seeders/Page_V3/localizations/pl/' . $topicSlug . '_theory.json',
        ];
    }

    public function topicSlug(?string $topic): string
    {
        $normalized = trim((string) $topic);

        if ($normalized === '') {
            return 'new_page_v3_topic';
        }

        $slug = Str::slug($normalized, '_');

        return $slug !== '' ? $slug : 'new_page_v3_topic';
    }

    public function topicStem(?string $topic): string
    {
        return Str::studly(str_replace('_', ' ', $this->topicSlug($topic))) ?: 'NewPageV3Topic';
    }

    public function topicFromExternalUrl(string $url): string
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path === '') {
            return 'External Page V3 Topic';
        }

        $lastSegment = last(array_values(array_filter(explode('/', $path))));
        $normalized = str_replace(['-', '_'], ' ', (string) $lastSegment);
        $normalized = trim($normalized);

        return $normalized !== '' ? Str::title($normalized) : 'External Page V3 Topic';
    }

    /**
     * @param  array<string, mixed>|null  $categoryContext
     * @return array<int, string>
     */
    public function referenceFiles(string $categoryMode, ?array $categoryContext): array
    {
        $files = collect([
            'app/Support/Database/JsonPageSeeder.php',
            'app/Support/Database/JsonPageDirectorySeeder.php',
            'app/Support/Database/JsonPageLocalizationManager.php',
        ]);

        if ($categoryMode === 'existing') {
            $categorySeederPath = (string) ($categoryContext['seeder_relative_path'] ?? '');

            if ($categorySeederPath !== '' && File::exists(base_path($categorySeederPath))) {
                $files->push($categorySeederPath);

                foreach ($this->siblingSeederFiles($categorySeederPath) as $relativePath) {
                    $files->push($relativePath);
                }

                $definitionPath = $this->definitionRelativePathFromSeeder($categorySeederPath);

                if ($definitionPath) {
                    $files->push($definitionPath);
                    $files->push('database/seeders/Page_V3/localizations/en/' . pathinfo($definitionPath, PATHINFO_BASENAME));
                    $files->push('database/seeders/Page_V3/localizations/pl/' . pathinfo($definitionPath, PATHINFO_BASENAME));
                }
            }
        }

        if ($files->count() <= 3) {
            foreach ($this->fallbackReferenceFiles() as $relativePath) {
                $files->push($relativePath);
            }
        }

        return $files
            ->filter(fn (string $relativePath) => File::exists(base_path($relativePath)))
            ->unique()
            ->values()
            ->all();
    }

    protected function categoryNamespaceFromTitle(string $title): string
    {
        return $this->topicStem($title);
    }

    /**
     * @return array<int, string>
     */
    protected function siblingSeederFiles(string $categorySeederRelativePath): array
    {
        $directory = dirname(base_path($categorySeederRelativePath));

        if (! File::isDirectory($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'php')
            ->sortBy(fn ($file) => str_replace('\\', '/', $file->getPathname()))
            ->map(fn ($file) => $this->toRelativeProjectPath($file->getPathname()))
            ->take(4)
            ->values()
            ->all();
    }

    protected function definitionRelativePathFromSeeder(string $seederRelativePath): ?string
    {
        $absolutePath = base_path($seederRelativePath);

        if (! File::exists($absolutePath)) {
            return null;
        }

        $contents = File::get($absolutePath);

        if (! preg_match("/database_path\\('([^']+Page_V3\\/definitions\\/[^']+\\.json)'\\)/", $contents, $matches)) {
            return null;
        }

        $relativePath = str_replace('\\', '/', $matches[1]);

        return Str::startsWith($relativePath, 'seeders/')
            ? 'database/' . $relativePath
            : null;
    }

    /**
     * @return array<int, string>
     */
    protected function fallbackReferenceFiles(): array
    {
        return [
            'database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsCategorySeeder.php',
            'database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder.php',
            'database/seeders/Page_V3/definitions/types_of_questions_category.json',
            'database/seeders/Page_V3/definitions/types_of_questions_alternative_questions_theory.json',
            'database/seeders/Page_V3/localizations/en/types_of_questions_category.json',
            'database/seeders/Page_V3/localizations/en/types_of_questions_alternative_questions_theory.json',
            'database/seeders/Page_V3/localizations/pl/types_of_questions_category.json',
            'database/seeders/Page_V3/localizations/pl/types_of_questions_alternative_questions_theory.json',
        ];
    }

    protected function toRelativeProjectPath(string $absolutePath): string
    {
        return ltrim(str_replace('\\', '/', Str::after($absolutePath, base_path())), '/');
    }
}
