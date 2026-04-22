<?php

namespace App\Services\PageV3PromptGenerator;

use App\Services\PageV3PromptGenerator\Data\PagePromptGenerationInput;
use App\Support\CodexPromptEnvelopeFormatter;
use App\Services\V3PromptGenerator\ExternalTheoryUrlService;
use Illuminate\Support\Str;
use RuntimeException;

class PageV3PromptGeneratorService
{
    private const PROMPT_ID_PREFIX = 'PAGE-V3-PROMPT-';

    public function __construct(
        private TheoryCategorySearchService $theoryCategorySearchService,
        private ExternalTheoryUrlService $externalTheoryUrlService,
        private PageV3BlueprintService $pageV3BlueprintService,
        private CodexPromptEnvelopeFormatter $codexPromptEnvelopeFormatter,
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
    public function promptAModes(): array
    {
        return [
            'repository_connected' => 'Mode A1 / repository-connected',
            'no_repository' => 'Mode A2 / no-repository fallback',
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
            ? [$this->buildPromptItem(
                $input,
                $source,
                $category,
                $preview,
                'single',
                'Prompt for Codex',
                $this->buildSinglePrompt($source, $category, $categoryCatalog, $preview, $referenceFiles),
            )]
            : [
                $this->buildPromptItem(
                    $input,
                    $source,
                    $category,
                    $preview,
                    'llm_json_pack',
                    'Prompt for LLM JSON generation',
                    $this->buildLlmJsonPrompt($input, $source, $category, $categoryCatalog, $preview, $referenceFiles),
                ),
                $this->buildPromptItem(
                    $input,
                    $source,
                    $category,
                    $preview,
                    'codex_page_v3',
                    'Prompt for Codex seeder generation',
                    $this->buildCodexSeederPrompt($source, $category, $categoryCatalog, $preview, $referenceFiles),
                ),
            ];

        return [
            'source' => $source,
            'category' => $category,
            'category_catalog_count' => count($categoryCatalog),
            'preview' => $preview,
            'reference_files' => $referenceFiles,
            'warnings' => $warnings,
            'generation_mode' => $input->generationMode,
            'prompt_a_mode' => $input->promptAMode,
            'prompt_a_mode_label' => $this->promptAModeLabel($input->promptAMode),
            'prompts' => $prompts,
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $preview
     * @return array<string, mixed>
     */
    protected function buildPromptItem(
        PagePromptGenerationInput $input,
        array $source,
        array $category,
        array $preview,
        string $key,
        string $title,
        string $body,
    ): array {
        $promptId = $this->buildPromptId($input, $source, $category, $preview, $key);
        $summary = $this->buildPromptSummary($input, $source, $category, $preview, $key, $title);

        return [
            'key' => $key,
            'title' => $title,
            'prompt_id' => $promptId,
            'prompt_id_text' => $this->codexPromptEnvelopeFormatter->formatPromptIdLine($promptId),
            'summary' => $summary,
            'summary_top_text' => $this->codexPromptEnvelopeFormatter->formatSummaryBlock('Top', $promptId, $summary),
            'summary_bottom_text' => $this->codexPromptEnvelopeFormatter->formatSummaryBlock('Bottom', $promptId, $summary),
            'text' => $this->codexPromptEnvelopeFormatter->wrapPrompt($promptId, $summary, $body),
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $preview
     * @return array<string, string>
     */
    protected function buildPromptSummary(
        PagePromptGenerationInput $input,
        array $source,
        array $category,
        array $preview,
        string $promptKey,
        string $title,
    ): array {
        $modeLabel = $input->generationMode === 'split'
            ? 'split / ' . $promptKey . ' / ' . $this->promptAModeLabel($input->promptAMode)
            : 'single / Codex';
        $sourceLabel = (string) ($source['source_label'] ?? $source['source_type'] ?? 'Unknown source');
        $topic = trim((string) ($source['topic'] ?? ''));
        $categorySummary = match ($category['mode'] ?? null) {
            'existing' => sprintf(
                'existing `%s` (%s)',
                $category['selected_category']['title'] ?? '',
                $category['selected_category']['slug'] ?? ''
            ),
            'new' => sprintf(
                'new `%s` (%s)',
                $category['new_category_title'] ?? '',
                $category['new_category_slug'] ?? ''
            ),
            default => 'ai_select / choose-or-create',
        };
        $artifacts = match ($promptKey) {
            'llm_json_pack' => $category['mode'] === 'existing'
                ? sprintf(
                    'JSON pack для page artifact `%s` і `en|pl` localizations без дублювання category files.',
                    $preview['page_class_name'] ?? ''
                )
                : sprintf(
                    'JSON pack для page artifact `%s` і category artifact `%s` з `en|pl` localizations.',
                    $preview['page_class_name'] ?? '',
                    $preview['category_class_name'] ?? ''
                ),
            'codex_page_v3' => $category['mode'] === 'existing'
                ? sprintf(
                    'Інтегрований Page_V3 package `%s` у вибрану category `%s`.',
                    $preview['page_class_name'] ?? '',
                    $preview['category_slug'] ?? ''
                )
                : sprintf(
                    'Інтегрований Page_V3 package `%s` плюс category package `%s` за потреби.',
                    $preview['page_class_name'] ?? '',
                    $preview['category_class_name'] ?? ''
                ),
            default => $category['mode'] === 'existing'
                ? sprintf(
                    'Готовий Page_V3 package `%s` у category `%s` з base definition і `en|pl` localizations.',
                    $preview['page_class_name'] ?? '',
                    $preview['category_slug'] ?? ''
                )
                : sprintf(
                    'Готовий Page_V3 package `%s` плюс category package `%s` з base definitions і `en|pl` localizations.',
                    $preview['page_class_name'] ?? '',
                    $preview['category_class_name'] ?? ''
                ),
        };

        return [
            'goal' => sprintf(
                'Підготувати %s для теми "%s" (%s).',
                $title,
                $topic !== '' ? $topic : 'Untitled topic',
                $sourceLabel
            ),
            'work' => sprintf(
                'Category mode `%s`; category context %s; generation mode %s.',
                $category['mode'] ?? '',
                $categorySummary,
                $modeLabel
            ),
            'constraints' => sprintf(
                'Не ламати чинний Page_V3 schema/localization contract; source type `%s`; page target `%s`.',
                $input->sourceType,
                $preview['page_fully_qualified_class_name'] ?? ''
            ),
            'result' => $artifacts,
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $preview
     */
    protected function buildPromptId(
        PagePromptGenerationInput $input,
        array $source,
        array $category,
        array $preview,
        string $promptKey,
    ): string {
        $seed = implode('|', [
            $promptKey,
            $input->sourceType,
            $source['normalized_url'] ?? ($source['url'] ?? ''),
            $source['topic'] ?? '',
            $input->categoryMode,
            $category['selected_category']['id'] ?? '',
            $category['selected_category']['slug'] ?? '',
            $category['new_category_title'] ?? '',
            $category['new_category_slug'] ?? '',
            $preview['category_slug'] ?? '',
            $preview['page_class_name'] ?? '',
            $preview['category_class_name'] ?? '',
            $input->generationMode,
            $input->promptAMode,
        ]);

        return self::PROMPT_ID_PREFIX . strtoupper(substr(sha1($seed), 0, 8));
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
            '- Suggested page loader stub: `' . $preview['page_seeder_relative_path'] . '`',
            '- Suggested page package folder: `' . $preview['page_package_relative_path'] . '`',
            '- Suggested page real seeder PHP: `' . $preview['page_real_seeder_relative_path'] . '`',
            '- Suggested page class: `' . $preview['page_fully_qualified_class_name'] . '`',
            '- Suggested page definition JSON: `' . $preview['page_definition_relative_path'] . '`',
            '- Suggested page localization JSON (en): `' . $preview['page_localization_en_relative_path'] . '`',
            '- Suggested page localization JSON (pl): `' . $preview['page_localization_pl_relative_path'] . '`',
        ];

        if ($category['mode'] !== 'existing') {
            $lines[] = '- Suggested category loader stub: `' . $preview['category_seeder_relative_path'] . '`';
            $lines[] = '- Suggested category package folder: `' . $preview['category_package_relative_path'] . '`';
            $lines[] = '- Suggested category real seeder PHP: `' . $preview['category_real_seeder_relative_path'] . '`';
            $lines[] = '- Suggested category definition JSON: `' . $preview['category_definition_relative_path'] . '`';
            $lines[] = '- Suggested category localization JSON (en): `' . $preview['category_localization_en_relative_path'] . '`';
            $lines[] = '- Suggested category localization JSON (pl): `' . $preview['category_localization_pl_relative_path'] . '`';
        }

        $lines = array_merge($lines, [
            '',
            'Hard requirements',
            '- First inspect the real `Page_V3` implementation already present in `database/seeders/Page_V3`.',
            '- Treat `app/Support/Database/JsonPageSeeder.php`, `app/Support/Database/JsonPageDirectorySeeder.php`, `app/Support/Database/JsonPageLocalizationManager.php`, and nearby Page_V3 files as the compatibility contract.',
            '- Use the existing `Page_V3` schema only: `content_type: page` or `content_type: category`, `type: theory`, base definition in `uk`, and companion localization JSON files in each seeder package under `localizations/en.json` and `localizations/pl.json`.',
            '- Do not invent a new schema, runtime loader, localization mechanism, or naming convention.',
            '- Each seeder must be self-contained in its own package folder with the real PHP class, `definition.json`, and all related localization JSON files.',
            '- Keep the top-level PHP file as a compatibility loader stub that requires the real class from its package folder.',
            '- Create fully written content. No demo content, no placeholders, no TODOs, no stubs.',
            '- Do not create PHP wrapper classes for localization JSON files unless you find a real existing project pattern that requires them. Current Page_V3 localization files are JSON-driven.',
            '- Keep all `seeder.class`, `target.definition_path`, `target.seeder_class`, category slug linkage, and file paths internally consistent.',
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
        PagePromptGenerationInput $input,
        array $source,
        array $category,
        array $categoryCatalog,
        array $preview,
        array $referenceFiles,
    ): string {
        $pageLocalizationEnClass = $this->localizationSeederClass((string) $preview['page_class_name'], 'en');
        $pageLocalizationPlClass = $this->localizationSeederClass((string) $preview['page_class_name'], 'pl');
        $categoryLocalizationEnClass = $this->localizationSeederClass((string) $preview['category_class_name'], 'en');
        $categoryLocalizationPlClass = $this->localizationSeederClass((string) $preview['category_class_name'], 'pl');

        $promptModeLines = [
            'Prompt A mode',
            '- Selected Prompt A mode: ' . $this->promptAModeLabel($input->promptAMode),
        ];

        if ($input->promptAMode === 'no_repository') {
            $promptModeLines[] = '- This prompt must work without a connected repository. Do not assume live repo access.';
            $promptModeLines[] = '- Use the embedded compatibility reference below as the source of truth for Page_V3 schema, localizations, and target linkage.';
        } else {
            $promptModeLines[] = '- This prompt assumes the repository is connected. Inspect the real Page_V3 files first and follow the live project contract.';
            $promptModeLines[] = '- Primary live repository references to inspect before generating JSON:';
            $promptModeLines[] = $this->formatReferenceLines($referenceFiles);
            $promptModeLines[] = '- If repository access is unavailable, switch this generator to Mode A2 / no-repository fallback instead of guessing.';
        }

        $lines = [
            'Generate only the JSON file payloads for the Laravel project `Pvitaly91/engapp-codex` `Page_V3` system.',
            '',
            'Preferred output mode',
            '- Return each required JSON as a separate downloadable `.json` file.',
            '- The attachment filenames may be arbitrary. The JSON contents must be correct; repository paths will be handled later in Codex.',
            '- Do not add commentary, explanations, or extra prose.',
            '- If your chat interface cannot generate downloadable files, fall back to multiple `json` code blocks in this exact order: page base `uk`, page localization `en`, page localization `pl`, then category files only if required by the selected category mode.',
            '- In that fallback mode, place a short role label before each block, such as `PAGE_BASE_UK`, `PAGE_EN`, `PAGE_PL`, `CATEGORY_BASE_UK`, `CATEGORY_EN`, `CATEGORY_PL`. Do not print repository filenames or paths.',
            '',
            ...$promptModeLines,
            '',
            'Source topic',
            $this->formatSourceSection($source),
            '',
            'Category instructions',
            $this->formatCategorySection($category),
            '',
        ];

        if ($input->promptAMode === 'no_repository') {
            $lines[] = 'Embedded compatibility reference';
            $lines[] = $this->formatEmbeddedLlmCompatibilityReference($category, $preview, $referenceFiles);
            $lines[] = '';
        }

        $lines = array_merge($lines, [
            'Target file expectations',
            '- Always keep the PHP loader stub at: `' . $preview['page_seeder_relative_path'] . '`',
            '- Always keep the real page seeder class at: `' . $preview['page_real_seeder_relative_path'] . '`',
            '- Always output the base page definition JSON in `uk`: `' . $preview['page_definition_relative_path'] . '`',
            '- Always output the page localization JSON for `en`: `' . $preview['page_localization_en_relative_path'] . '`',
            '- Always output the page localization JSON for `pl`: `' . $preview['page_localization_pl_relative_path'] . '`',
        ]);

        if ($category['mode'] === 'new') {
            $lines[] = '- Also keep the category loader stub at: `' . $preview['category_seeder_relative_path'] . '`';
            $lines[] = '- Also keep the real category seeder class at: `' . $preview['category_real_seeder_relative_path'] . '`';
            $lines[] = '- Also output the base category definition JSON in `uk`: `' . $preview['category_definition_relative_path'] . '`';
            $lines[] = '- Also output the category localization JSON for `en`: `' . $preview['category_localization_en_relative_path'] . '`';
            $lines[] = '- Also output the category localization JSON for `pl`: `' . $preview['category_localization_pl_relative_path'] . '`';
        } elseif ($category['mode'] === 'ai_select') {
            $lines[] = '- If you decide a new category is required, also output the category loader stub, real category seeder, category definition JSON, and `en`/`pl` category localizations before the page files.';
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
            '- `target.definition_path` in localization JSON should point to `../definition.json` so each localization stays self-contained with its base seeder package.',
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
            'Take the attached or pasted Page_V3 JSON file payloads and integrate them into the project as fully compatible `Page_V3` seeders.',
            '',
            'Input format',
            '- One or more JSON files may be attached in Codex or pasted inline.',
            '- Attachment filenames may be arbitrary and must not be treated as target repository paths.',
            '- If the JSON is pasted inline instead of attached files, it may arrive as multiple `json` blocks with simple role labels such as `PAGE_BASE_UK`, `PAGE_EN`, `PAGE_PL`, `CATEGORY_BASE_UK`, `CATEGORY_EN`, `CATEGORY_PL`.',
            '- Materialize those JSON payloads into the correct repository files based on their content and the target rules below, not based on uploaded filenames.',
            '',
            'Source topic',
            $this->formatSourceSection($source),
            '',
            'Category instructions',
            $this->formatCategorySection($category),
            '',
            'Suggested targets',
            '- Suggested page loader stub: `' . $preview['page_seeder_relative_path'] . '`',
            '- Suggested page real seeder PHP: `' . $preview['page_real_seeder_relative_path'] . '`',
            '- Suggested page definition JSON: `' . $preview['page_definition_relative_path'] . '`',
            '- Suggested page localization JSON (en): `' . $preview['page_localization_en_relative_path'] . '`',
            '- Suggested page localization JSON (pl): `' . $preview['page_localization_pl_relative_path'] . '`',
        ];

        if ($category['mode'] !== 'existing') {
            $lines[] = '- Suggested category loader stub: `' . $preview['category_seeder_relative_path'] . '`';
            $lines[] = '- Suggested category real seeder PHP: `' . $preview['category_real_seeder_relative_path'] . '`';
            $lines[] = '- Suggested category definition JSON: `' . $preview['category_definition_relative_path'] . '`';
        }

        $lines = array_merge($lines, [
            '',
            'Hard requirements',
            '- First inspect the real `Page_V3` implementation in `database/seeders/Page_V3` plus `app/Support/Database/JsonPageSeeder.php`, `app/Support/Database/JsonPageDirectorySeeder.php`, and `app/Support/Database/JsonPageLocalizationManager.php`.',
            '- Do not invent a new schema, new runtime loader, or a custom one-off seeder implementation.',
            '- Preserve the provided JSON content as the canonical content. Only make small technical compatibility fixes if necessary.',
            '- Create PHP wrapper seeders only for base page/category definitions. Do not create localization PHP wrappers unless the real project already does that for the same pattern.',
            '- Keep `seeder.class`, `target.definition_path`, `target.seeder_class`, category slug linkage, and file placement consistent.',
            '- Materialize each base seeder as a self-contained package folder with the real PHP class, `definition.json`, and localized JSON files, while keeping the top-level PHP path as a compatibility loader stub.',
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
        $classStem = preg_replace('/Seeder$/', '', trim($definitionBaseName)) ?: trim($definitionBaseName);

        return 'Database\\Seeders\\Page_V3\\Localizations\\' . $localeNamespace . '\\' . $classStem . 'LocalizationSeeder';
    }

    /**
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $preview
     * @param  array<int, string>  $referenceFiles
     */
    protected function formatEmbeddedLlmCompatibilityReference(
        array $category,
        array $preview,
        array $referenceFiles,
    ): string {
        $lines = [
            '- Observed Page_V3 naming pattern: top-level loader stub `' . ($preview['page_seeder_relative_path'] ?? '') . '`, real page class `' . ($preview['page_real_seeder_relative_path'] ?? '') . '`, base definition in `' . ($preview['page_definition_relative_path'] ?? '') . '`, and localizations in `' . ($preview['page_package_relative_path'] ?? '') . '/localizations/en.json|pl.json`.',
            '- Real loader contract from `app/Support/Database/JsonPageSeeder.php`: base definitions are JSON-driven and use `content_type: page` or `content_type: category`, `type: theory`, and `seeder.class` for the base PHP wrapper.',
            '- Real localization contract from `app/Support/Database/JsonPageLocalizationManager.php`: localization JSON uses top-level keys `locale`, `seeder`, `target`, `blocks`.',
            '- Real localization targeting rules: `target.definition_path` should point to `../definition.json`, and `target.seeder_class` must exactly match the corresponding base wrapper class.',
            '- Real runtime directory contract from `app/Support/Database/JsonPageDirectorySeeder.php`: base definitions are discovered anywhere under `database/seeders/Page_V3`, excluding nested `localizations` folders; localization PHP wrappers are virtual and do not require standalone PHP files.',
            '- Base page content rules: write the base definition in locale `uk`; write companion `en` and `pl` localizations; keep page slug, category slug, and wrapper class linkage internally consistent.',
        ];

        if (($category['mode'] ?? null) === 'existing') {
            $lines[] = '- Category mode rule: reuse the selected existing category and do not output duplicate category JSON files.';
        } elseif (($category['mode'] ?? null) === 'new') {
            $lines[] = '- Category mode rule: output both page JSON files and the new category JSON package (`uk`, `en`, `pl`) using the suggested category slug and wrapper naming.';
        } else {
            $lines[] = '- Category mode rule: choose the best existing category if it clearly fits; otherwise create a new category JSON package and keep the selected page attached to it.';
        }

        if ($referenceFiles !== []) {
            $lines[] = '- Neighbor reference filenames embedded for offline use:';

            foreach ($referenceFiles as $path) {
                $lines[] = '  - `' . $path . '`';
            }
        }

        return implode("\n", $lines);
    }

    protected function promptAModeLabel(string $promptAMode): string
    {
        return $this->promptAModes()[$promptAMode] ?? $this->promptAModes()['repository_connected'];
    }
}
