<?php

namespace App\Services\PageV3PromptGenerator;

use App\Services\PageV3PromptGenerator\Data\PagePromptGenerationInput;
use App\Services\V3PromptGenerator\ExternalTheoryUrlService;
use Illuminate\Support\Str;
use RuntimeException;

class PageV3PromptGeneratorService
{
    public function __construct(
        private TheoryCategorySearchService $theoryCategorySearchService,
        private ExternalTheoryUrlService $externalTheoryUrlService,
        private PageV3BlueprintService $pageV3BlueprintService,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function generationModes(): array
    {
        return [
            'single' => 'Mode 1: One prompt for Codex',
            'split' => 'Mode 2: Two prompts (LLM JSON pack + Codex integration)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function categoryModes(): array
    {
        return [
            'existing' => 'Use existing theory category',
            'new' => 'Create new theory category',
            'ai_select' => 'Let AI choose the best category or create a new one',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function categoryOptions(): array
    {
        return $this->theoryCategorySearchService->allForPicker();
    }

    /**
     * @param  array<string, mixed>|null  $categoryContext
     * @return array<string, mixed>
     */
    public function buildPreview(?string $topic, string $categoryMode, ?array $categoryContext, ?string $newCategoryTitle): array
    {
        return $this->pageV3BlueprintService->buildPreview($topic, $categoryMode, $categoryContext, $newCategoryTitle);
    }

    public function topicFromExternalUrl(string $url): string
    {
        return $this->pageV3BlueprintService->topicFromExternalUrl($url);
    }

    /**
     * @return array<string, mixed>
     */
    public function generate(PagePromptGenerationInput $input): array
    {
        $warnings = [];
        $categoryCatalog = $this->theoryCategorySearchService->catalog();
        $source = $this->buildSourceContext($input, $warnings);
        $category = $this->buildCategoryContext($input, $categoryCatalog);
        $preview = $this->pageV3BlueprintService->buildPreview(
            $source['topic'] ?? null,
            $input->categoryMode,
            $category['selected_category'] ?? null,
            $input->newCategoryTitle,
        );
        $referenceFiles = $this->pageV3BlueprintService->referenceFiles(
            $input->categoryMode,
            $category['selected_category'] ?? null,
        );

        $prompts = $input->generationMode === 'single'
            ? [[
                'key' => 'single',
                'title' => 'Prompt for Codex',
                'text' => $this->buildSinglePrompt($source, $category, $categoryCatalog, $preview, $referenceFiles),
            ]]
            : [
                [
                    'key' => 'llm_json_pack',
                    'title' => 'Prompt for LLM JSON generation',
                    'text' => $this->buildLlmJsonPrompt($source, $category, $categoryCatalog, $preview),
                ],
                [
                    'key' => 'codex_page_v3',
                    'title' => 'Prompt for Codex seeder generation',
                    'text' => $this->buildCodexSeederPrompt($source, $category, $categoryCatalog, $preview, $referenceFiles),
                ],
            ];

        return [
            'source' => $source,
            'category' => $category,
            'category_catalog_count' => count($categoryCatalog),
            'preview' => $preview,
            'reference_files' => $referenceFiles,
            'warnings' => $warnings,
            'generation_mode' => $input->generationMode,
            'prompts' => $prompts,
        ];
    }

    /**
     * @param  array<int, string>  $warnings
     * @return array<string, mixed>
     */
    protected function buildSourceContext(PagePromptGenerationInput $input, array &$warnings): array
    {
        return match ($input->sourceType) {
            'external_url' => $this->buildExternalUrlContext($input->externalUrl, $warnings),
            default => $this->buildManualContext($input->manualTopic),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildManualContext(?string $manualTopic): array
    {
        $topic = trim((string) $manualTopic);

        return [
            'source_type' => 'manual_topic',
            'source_label' => 'Manual topic',
            'topic' => $topic,
            'context_excerpt' => null,
        ];
    }

    /**
     * @param  array<int, string>  $warnings
     * @return array<string, mixed>
     */
    protected function buildExternalUrlContext(?string $externalUrl, array &$warnings): array
    {
        $normalizedUrl = $this->externalTheoryUrlService->normalizeAndValidatePublicUrl((string) $externalUrl);
        $fetched = $this->externalTheoryUrlService->fetch($normalizedUrl);

        if (! empty($fetched['error'])) {
            $warnings[] = (string) $fetched['error'];
        }

        $topic = $fetched['title'] ?: $this->pageV3BlueprintService->topicFromExternalUrl($normalizedUrl);

        return [
            'source_type' => 'external_url',
            'source_label' => 'External theory URL',
            'topic' => $topic,
            'url' => $fetched['url'] ?? $normalizedUrl,
            'normalized_url' => $normalizedUrl,
            'title' => $fetched['title'] ?? null,
            'context_excerpt' => $fetched['snippet'] ?? null,
            'fetch_warning' => $fetched['error'] ?? null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $categoryCatalog
     * @return array<string, mixed>
     */
    protected function buildCategoryContext(PagePromptGenerationInput $input, array $categoryCatalog): array
    {
        return match ($input->categoryMode) {
            'existing' => $this->buildExistingCategoryContext($input->existingCategoryId),
            'new' => $this->buildNewCategoryContext($input->newCategoryTitle, $categoryCatalog),
            default => $this->buildAiCategoryContext($categoryCatalog),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildExistingCategoryContext(?int $categoryId): array
    {
        $selectedCategory = $categoryId
            ? $this->theoryCategorySearchService->findSummaryById($categoryId)
            : null;

        if (! $selectedCategory) {
            throw new RuntimeException('Обрана категорія теорії не знайдена.');
        }

        return [
            'mode' => 'existing',
            'mode_label' => 'Use existing theory category',
            'selected_category' => $selectedCategory,
            'requires_category_creation' => false,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $categoryCatalog
     * @return array<string, mixed>
     */
    protected function buildNewCategoryContext(?string $newCategoryTitle, array $categoryCatalog): array
    {
        $title = trim((string) $newCategoryTitle);

        if ($title === '') {
            throw new RuntimeException('Вкажіть назву нової категорії.');
        }

        return [
            'mode' => 'new',
            'mode_label' => 'Create new theory category',
            'new_category_title' => $title,
            'new_category_slug' => $this->pageV3BlueprintService->topicSlug($title),
            'selected_category' => null,
            'requires_category_creation' => true,
            'category_catalog' => $categoryCatalog,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $categoryCatalog
     * @return array<string, mixed>
     */
    protected function buildAiCategoryContext(array $categoryCatalog): array
    {
        return [
            'mode' => 'ai_select',
            'mode_label' => 'Let AI choose the best category or create a new one',
            'selected_category' => null,
            'requires_category_creation' => null,
            'category_catalog' => $categoryCatalog,
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $category
     * @param  array<int, array<string, mixed>>  $categoryCatalog
     * @param  array<string, mixed>  $preview
     * @param  array<int, string>  $referenceFiles
     */
    protected function buildSinglePrompt(
        array $source,
        array $category,
        array $categoryCatalog,
        array $preview,
        array $referenceFiles,
    ): string {
        $lines = [
            'You are working in repository `Pvitaly91/engapp-codex` on branch `main`.',
            '',
            'Create a fully compatible `Page_V3` theory seeder package for this Laravel project.',
            '',
            'Topic and source',
            $this->formatSourceSection($source),
            '',
            'Category instructions',
            $this->formatCategorySection($category),
            '',
            'Suggested targets',
            '- Suggested page PHP wrapper: `' . $preview['page_seeder_relative_path'] . '`',
            '- Suggested page class: `' . $preview['page_fully_qualified_class_name'] . '`',
            '- Suggested page definition JSON: `' . $preview['page_definition_relative_path'] . '`',
            '- Suggested page localization JSON (en): `' . $preview['page_localization_en_relative_path'] . '`',
            '- Suggested page localization JSON (pl): `' . $preview['page_localization_pl_relative_path'] . '`',
        ];

        if ($category['mode'] !== 'existing') {
            $lines[] = '- Suggested category PHP wrapper: `' . $preview['category_seeder_relative_path'] . '`';
            $lines[] = '- Suggested category definition JSON: `' . $preview['category_definition_relative_path'] . '`';
            $lines[] = '- Suggested category localization JSON (en): `' . $preview['category_localization_en_relative_path'] . '`';
            $lines[] = '- Suggested category localization JSON (pl): `' . $preview['category_localization_pl_relative_path'] . '`';
        }

        $lines = array_merge($lines, [
            '',
            'Hard requirements',
            '- First inspect the real `Page_V3` implementation already present in `database/seeders/Page_V3`.',
            '- Treat `app/Support/Database/JsonPageSeeder.php`, `app/Support/Database/JsonPageDirectorySeeder.php`, `app/Support/Database/JsonPageLocalizationManager.php`, and nearby Page_V3 files as the compatibility contract.',
            '- Use the existing `Page_V3` schema only: `content_type: page` or `content_type: category`, `type: theory`, base definition in `uk`, localization JSON files in `database/seeders/Page_V3/localizations/en` and `database/seeders/Page_V3/localizations/pl`.',
            '- Do not invent a new schema, runtime loader, localization mechanism, or naming convention.',
            '- Create fully written content. No demo content, no placeholders, no TODOs, no stubs.',
            '- Do not create PHP wrapper classes for localization JSON files unless you find a real existing project pattern that requires them. Current Page_V3 localization files are JSON-driven.',
            '- Keep all `seeder.class`, `target.definition`, `target.seeder_class`, category slug linkage, and file paths internally consistent.',
            '- Make the output directly runnable through the current Page_V3 seeding system.',
        ]);

        if ($category['mode'] === 'existing') {
            $lines[] = '- Reuse the selected existing category. Do not create a duplicate category seeder or duplicate category JSON.';
        } elseif ($category['mode'] === 'new') {
            $lines[] = '- Create a new category with the exact requested title, create its category definition JSON and PHP wrapper seeder, and attach the new page to it.';
            $lines[] = '- Use the current theory category catalog only to choose the best parent placement and to match namespace/path conventions.';
        } else {
            $lines[] = '- Inspect the current theory category catalog below. Reuse the best-fit existing category if one clearly matches the topic.';
            $lines[] = '- If no existing category is a good fit, create a new category, its category definition JSON, and its PHP wrapper seeder.';
        }

        $lines = array_merge($lines, [
            '',
            'Useful reference files',
            $this->formatReferenceLines($referenceFiles),
        ]);

        if ($category['mode'] !== 'existing') {
            $lines[] = '';
            $lines[] = 'Current theory category catalog';
            $lines[] = $this->formatCategoryCatalog($categoryCatalog);
        }

        $lines = array_merge($lines, [
            '',
            'Final response',
            '- List the changed files.',
            '- Give a short summary.',
            '- State whether you reused an existing category or created a new one.',
            '- Report the final page slug, category slug, and the files created for base definitions and localizations.',
        ]);

        return implode("\n", array_filter($lines, static fn ($line) => $line !== null));
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $category
     * @param  array<int, array<string, mixed>>  $categoryCatalog
     * @param  array<string, mixed>  $preview
     */
    protected function buildLlmJsonPrompt(
        array $source,
        array $category,
        array $categoryCatalog,
        array $preview,
    ): string {
        $pageDefinitionBaseName = pathinfo((string) $preview['page_definition_relative_path'], PATHINFO_FILENAME);
        $pageLocalizationEnClass = $this->localizationSeederClass($pageDefinitionBaseName, 'en');
        $pageLocalizationPlClass = $this->localizationSeederClass($pageDefinitionBaseName, 'pl');
        $categoryDefinitionBaseName = pathinfo((string) $preview['category_definition_relative_path'], PATHINFO_FILENAME);
        $categoryLocalizationEnClass = $this->localizationSeederClass($categoryDefinitionBaseName, 'en');
        $categoryLocalizationPlClass = $this->localizationSeederClass($categoryDefinitionBaseName, 'pl');

        $lines = [
            'Generate only the JSON file payloads for the Laravel project `Pvitaly91/engapp-codex` `Page_V3` system.',
            '',
            'Return the response in this exact layout and nothing else:',
            'PATH: relative/path/to/file.json',
            '```json',
            '{...}',
            '```',
            '',
            'No commentary. No bullet points. No markdown except the required `PATH:` lines and `json` code fences.',
            '',
            'Source topic',
            $this->formatSourceSection($source),
            '',
            'Category instructions',
            $this->formatCategorySection($category),
            '',
            'Target file expectations',
            '- Always output the base page definition JSON in `uk`: `' . $preview['page_definition_relative_path'] . '`',
            '- Always output the page localization JSON for `en`: `' . $preview['page_localization_en_relative_path'] . '`',
            '- Always output the page localization JSON for `pl`: `' . $preview['page_localization_pl_relative_path'] . '`',
        ];

        if ($category['mode'] === 'new') {
            $lines[] = '- Also output the base category definition JSON in `uk`: `' . $preview['category_definition_relative_path'] . '`';
            $lines[] = '- Also output the category localization JSON for `en`: `' . $preview['category_localization_en_relative_path'] . '`';
            $lines[] = '- Also output the category localization JSON for `pl`: `' . $preview['category_localization_pl_relative_path'] . '`';
        } elseif ($category['mode'] === 'ai_select') {
            $lines[] = '- If you decide a new category is required, also output the category definition JSON and `en`/`pl` category localizations before the page files.';
            $lines[] = '- If you reuse an existing category, do not output category JSON files.';
        }

        $lines = array_merge($lines, [
            '',
            'Required JSON shapes',
            '- Page definition JSON must use top-level keys like: `schema_version`, `content_type`, `slug`, `page`, `seeder`, `type`.',
            '- Category definition JSON must use top-level keys like: `schema_version`, `content_type`, `slug`, `seeder`, `category`, `description`.',
            '- Localization JSON must use top-level keys like: `locale`, `seeder`, `target`, `blocks`.',
            '',
            'Required `seeder.class` values',
            '- Page wrapper class: `' . $preview['page_fully_qualified_class_name'] . '`',
            '- Page EN localization virtual seeder class: `' . $pageLocalizationEnClass . '`',
            '- Page PL localization virtual seeder class: `' . $pageLocalizationPlClass . '`',
        ]);

        if ($category['mode'] === 'new') {
            $lines[] = '- Category wrapper class: `' . $this->categoryWrapperClass($preview) . '`';
            $lines[] = '- Category EN localization virtual seeder class: `' . $categoryLocalizationEnClass . '`';
            $lines[] = '- Category PL localization virtual seeder class: `' . $categoryLocalizationPlClass . '`';
        } elseif ($category['mode'] === 'ai_select') {
            $lines[] = '- If you create a new category, derive its wrapper class and localization virtual seeder classes in the same style as the examples above.';
        }

        $lines = array_merge($lines, [
            '',
            'Rules',
            '- Base definitions must be written for locale `uk`.',
            '- Companion localizations must be written for locales `en` and `pl`.',
            '- `target.definition` in localization JSON must match the base definition filename without `.json`.',
            '- `target.seeder_class` in localization JSON must exactly match the corresponding base wrapper class.',
            '- Use `content_type: page` for the theory page JSON and `content_type: category` only when category JSON is needed.',
            '- Use `type: theory` consistently.',
            '- Produce complete theory content with meaningful subtitle text, structured blocks, tags, and realistic educational material. No placeholders.',
            '- Keep page/category slug linkage internally consistent.',
            '- Follow the selected category mode exactly.',
        ]);

        if ($category['mode'] !== 'existing') {
            $lines[] = '';
            $lines[] = 'Current theory category catalog';
            $lines[] = $this->formatCategoryCatalog($categoryCatalog);
        }

        return implode("\n", array_filter($lines, static fn ($line) => $line !== null));
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $category
     * @param  array<int, array<string, mixed>>  $categoryCatalog
     * @param  array<string, mixed>  $preview
     * @param  array<int, string>  $referenceFiles
     */
    protected function buildCodexSeederPrompt(
        array $source,
        array $category,
        array $categoryCatalog,
        array $preview,
        array $referenceFiles,
    ): string {
        $lines = [
            'You are working in repository `Pvitaly91/engapp-codex` on branch `main`.',
            '',
            'Take the pasted Page_V3 JSON file payloads and integrate them into the project as fully compatible `Page_V3` seeders.',
            '',
            'Input format',
            '- The JSON payloads will be provided as `PATH: ...` lines followed by fenced `json` blocks.',
            '- Materialize those JSON files into the repository.',
            '',
            'Source topic',
            $this->formatSourceSection($source),
            '',
            'Category instructions',
            $this->formatCategorySection($category),
            '',
            'Suggested targets',
            '- Suggested page PHP wrapper: `' . $preview['page_seeder_relative_path'] . '`',
            '- Suggested page definition JSON: `' . $preview['page_definition_relative_path'] . '`',
            '- Suggested page localization JSON (en): `' . $preview['page_localization_en_relative_path'] . '`',
            '- Suggested page localization JSON (pl): `' . $preview['page_localization_pl_relative_path'] . '`',
        ];

        if ($category['mode'] !== 'existing') {
            $lines[] = '- Suggested category PHP wrapper: `' . $preview['category_seeder_relative_path'] . '`';
            $lines[] = '- Suggested category definition JSON: `' . $preview['category_definition_relative_path'] . '`';
        }

        $lines = array_merge($lines, [
            '',
            'Hard requirements',
            '- First inspect the real `Page_V3` implementation in `database/seeders/Page_V3` plus `app/Support/Database/JsonPageSeeder.php`, `app/Support/Database/JsonPageDirectorySeeder.php`, and `app/Support/Database/JsonPageLocalizationManager.php`.',
            '- Do not invent a new schema, new runtime loader, or a custom one-off seeder implementation.',
            '- Preserve the provided JSON content as the canonical content. Only make small technical compatibility fixes if necessary.',
            '- Create PHP wrapper seeders only for base page/category definitions. Do not create localization PHP wrappers unless the real project already does that for the same pattern.',
            '- Keep `seeder.class`, `target.definition`, `target.seeder_class`, category slug linkage, and file placement consistent.',
            '- If nearby Page_V3 files in the chosen namespace use a slightly different naming convention than the suggested preview, follow the local convention consistently and update all related JSON references to match.',
            '- Ensure the final output stays compatible with the current Page_V3 directory seeder and localization sync flow.',
        ]);

        if ($category['mode'] === 'existing') {
            $lines[] = '- Reuse the selected category and do not create a duplicate category.';
        } elseif ($category['mode'] === 'new') {
            $lines[] = '- Create the requested new category and wire the page into it.';
        } else {
            $lines[] = '- Reuse an existing category if the provided JSON clearly targets one; otherwise create a new category and its wrapper seeder.';
        }

        $lines = array_merge($lines, [
            '',
            'Useful reference files',
            $this->formatReferenceLines($referenceFiles),
        ]);

        if ($category['mode'] !== 'existing') {
            $lines[] = '';
            $lines[] = 'Current theory category catalog';
            $lines[] = $this->formatCategoryCatalog($categoryCatalog);
        }

        $lines = array_merge($lines, [
            '',
            'Final response',
            '- List the changed files.',
            '- Give a short summary.',
            '- State which category slug/path was used.',
            '- Mention whether a new category wrapper seeder was created.',
        ]);

        return implode("\n", array_filter($lines, static fn ($line) => $line !== null));
    }

    /**
     * @param  array<string, mixed>  $source
     */
    protected function formatSourceSection(array $source): string
    {
        $lines = [
            '- Source type: ' . ($source['source_label'] ?? $source['source_type'] ?? 'Unknown'),
            '- Topic: `' . ($source['topic'] ?? '') . '`',
        ];

        if (($source['source_type'] ?? null) === 'external_url') {
            $lines[] = '- External URL: `' . ($source['normalized_url'] ?? $source['url'] ?? '') . '`';

            if (! empty($source['title'])) {
                $lines[] = '- Fetched page title: `' . $source['title'] . '`';
            }

            if (! empty($source['fetch_warning'])) {
                $lines[] = '- Fetch note: ' . $source['fetch_warning'];
            }
        }

        if (! empty($source['context_excerpt'])) {
            $lines[] = '- Source context excerpt (trimmed):';
            $lines[] = '"""';
            $lines[] = (string) $source['context_excerpt'];
            $lines[] = '"""';
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $category
     */
    protected function formatCategorySection(array $category): string
    {
        if (($category['mode'] ?? null) === 'existing') {
            $selected = $category['selected_category'] ?? [];

            return implode("\n", array_filter([
                '- Mode: Use existing theory category',
                '- Category title: `' . ($selected['title'] ?? '') . '`',
                '- Category slug: `' . ($selected['slug'] ?? '') . '`',
                ! empty($selected['path']) ? '- Category path: `' . $selected['path'] . '`' : null,
                ! empty($selected['namespace']) ? '- Category namespace/folder: `' . $selected['namespace'] . '`' : null,
                ! empty($selected['seeder_class']) ? '- Existing category seeder: `' . $selected['seeder_class'] . '`' : null,
                isset($selected['page_count']) ? '- Existing pages in category: ' . (int) $selected['page_count'] : null,
            ]));
        }

        if (($category['mode'] ?? null) === 'new') {
            return implode("\n", [
                '- Mode: Create new theory category',
                '- New category title: `' . ($category['new_category_title'] ?? '') . '`',
                '- Suggested category slug: `' . ($category['new_category_slug'] ?? '') . '`',
                '- Create a new Page_V3 category seeder package for this category.',
            ]);
        }

        return implode("\n", [
            '- Mode: Let AI choose the best existing theory category or create a new one',
            '- First review the current theory category catalog included below.',
            '- Reuse an existing category if it clearly matches the topic.',
            '- If nothing is a good fit, create a new category and its Page_V3 category seeder package.',
        ]);
    }

    /**
     * @param  array<int, string>  $referenceFiles
     */
    protected function formatReferenceLines(array $referenceFiles): string
    {
        return implode("\n", array_map(
            static fn (string $path) => '- `' . $path . '`',
            $referenceFiles
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $categoryCatalog
     */
    protected function formatCategoryCatalog(array $categoryCatalog): string
    {
        return implode("\n", array_map(function (array $category): string {
            $parts = [
                'slug=' . ($category['slug'] ?? ''),
                'title=' . ($category['title'] ?? ''),
            ];

            if (! empty($category['path'])) {
                $parts[] = 'path=' . $category['path'];
            }

            if (! empty($category['namespace'])) {
                $parts[] = 'namespace=' . $category['namespace'];
            }

            if (! empty($category['seeder_class'])) {
                $parts[] = 'seeder=' . $category['seeder_class'];
            }

            return '- ' . implode(' | ', $parts);
        }, $categoryCatalog));
    }

    /**
     * @param  array<string, mixed>  $preview
     */
    protected function categoryWrapperClass(array $preview): string
    {
        $namespace = trim((string) ($preview['category_namespace'] ?? ''), '\\');
        $className = (string) ($preview['category_class_name'] ?? '');

        return $namespace !== '' && $className !== ''
            ? 'Database\\Seeders\\Page_V3\\' . $namespace . '\\' . $className
            : 'Database\\Seeders\\Page_V3\\<ResolvedCategoryNamespace>\\<ResolvedCategory>CategorySeeder';
    }

    protected function localizationSeederClass(string $definitionBaseName, string $locale): string
    {
        $localeNamespace = Str::ucfirst(strtolower($locale));
        $classStem = Str::studly(str_replace('_', ' ', $definitionBaseName));

        return 'Database\\Seeders\\Page_V3\\Localizations\\' . $localeNamespace . '\\' . $classStem . 'LocalizationSeeder';
    }
}
