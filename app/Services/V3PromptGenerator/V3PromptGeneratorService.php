<?php

namespace App\Services\V3PromptGenerator;

use App\Services\V3PromptGenerator\Data\PromptGenerationInput;
use App\Support\CodexPromptEnvelopeFormatter;
use RuntimeException;

class V3PromptGeneratorService
{
    public const CEFR_LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    private const PROMPT_ID_PREFIX = 'V3-PROMPT-';

    public function __construct(
        private TheoryPageSearchService $theoryPageSearchService,
        private ExternalTheoryUrlService $externalTheoryUrlService,
        private V3SeederBlueprintService $v3SeederBlueprintService,
        private CodexPromptEnvelopeFormatter $codexPromptEnvelopeFormatter,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function levels(): array
    {
        return self::CEFR_LEVELS;
    }

    /**
     * @return array<int, string>
     */
    public function namespaceSuggestions(): array
    {
        return $this->v3SeederBlueprintService->namespaceSuggestions();
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

    public function buildPreview(string $namespace, ?string $topic): array
    {
        return $this->v3SeederBlueprintService->buildPreview($namespace, $topic);
    }

    public function topicFromExternalUrl(string $url): string
    {
        return $this->v3SeederBlueprintService->topicFromExternalUrl($url);
    }

    /**
     * @return array<string, mixed>
     */
    public function generate(PromptGenerationInput $input): array
    {
        $warnings = [];
        $source = $this->buildSourceContext($input, $warnings);
        $preview = $this->v3SeederBlueprintService->buildPreview($input->targetNamespace, $source['topic']);
        $referenceFiles = $this->v3SeederBlueprintService->referenceFiles($input->targetNamespace);
        $distribution = collect($input->levels)
            ->mapWithKeys(fn (string $level) => [$level => $input->questionsPerLevel])
            ->all();

        $prompts = $input->generationMode === 'single'
            ? [
                $this->buildPromptItem(
                    $input,
                    $source,
                    $preview,
                    $distribution,
                    'single',
                    'Prompt for Codex',
                    $this->buildSinglePrompt($input, $source, $preview, $referenceFiles, $distribution),
                ),
            ]
            : [
                $this->buildPromptItem(
                    $input,
                    $source,
                    $preview,
                    $distribution,
                    'llm_json',
                    'Prompt for LLM JSON generation',
                    $this->buildLlmJsonPrompt($input, $source, $preview, $referenceFiles, $distribution),
                ),
                $this->buildPromptItem(
                    $input,
                    $source,
                    $preview,
                    $distribution,
                    'codex_seeder',
                    'Prompt for Codex seeder generation',
                    $this->buildCodexSeederPrompt($input, $source, $preview, $referenceFiles, $distribution),
                ),
            ];

        return [
            'source' => $source,
            'preview' => $preview,
            'reference_files' => $referenceFiles,
            'warnings' => $warnings,
            'distribution' => $distribution,
            'total_questions' => array_sum($distribution),
            'generation_mode' => $input->generationMode,
            'prompt_a_mode' => $input->promptAMode,
            'prompt_a_mode_label' => $this->promptAModeLabel($input->promptAMode),
            'prompts' => $prompts,
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $preview
     * @param  array<string, int>  $distribution
     * @return array<string, mixed>
     */
    protected function buildPromptItem(
        PromptGenerationInput $input,
        array $source,
        array $preview,
        array $distribution,
        string $key,
        string $title,
        string $body,
    ): array {
        $promptId = $this->buildPromptId($input, $source, $preview, $distribution, $key);
        $summary = $this->buildPromptSummary($input, $source, $preview, $distribution, $key, $title);

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
     * @param  array<string, mixed>  $preview
     * @param  array<string, int>  $distribution
     * @return array<string, string>
     */
    protected function buildPromptSummary(
        PromptGenerationInput $input,
        array $source,
        array $preview,
        array $distribution,
        string $promptKey,
        string $title,
    ): array {
        $distributionLabel = implode(', ', array_map(
            static fn (string $level, int $count): string => $level . ': ' . $count,
            array_keys($distribution),
            array_values($distribution)
        ));
        $modeLabel = $input->generationMode === 'split'
            ? 'split / ' . $promptKey . ' / ' . $this->promptAModeLabel($input->promptAMode)
            : 'single / Codex';
        $sourceLabel = (string) ($source['source_label'] ?? $source['source_type'] ?? 'Unknown source');
        $topic = trim((string) ($source['topic'] ?? ''));
        $artifacts = match ($promptKey) {
            'llm_json' => sprintf(
                'Один V3 JSON artifact для `%s` у namespace `%s`.',
                $preview['class_name'] ?? '',
                $preview['target_namespace'] ?? ''
            ),
            'codex_seeder' => sprintf(
                'Інтегрований V3 package `%s` з loader stub, `definition.json`, `localizations/uk|en|pl` і SavedGrammarTest wiring.',
                $preview['class_name'] ?? ''
            ),
            default => sprintf(
                'Готовий V3 package `%s` у namespace `%s` з loader stub, `definition.json`, `localizations/uk|en|pl` і SavedGrammarTest wiring.',
                $preview['class_name'] ?? '',
                $preview['target_namespace'] ?? ''
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
                'Target namespace `%s`; рівні %s; distribution %s; generation mode %s.',
                $preview['target_namespace'] ?? '',
                implode(', ', array_keys($distribution)),
                $distributionLabel,
                $modeLabel
            ),
            'constraints' => sprintf(
                'Не ламати чинний V3 schema/loader contract; source type `%s`; expected artifact flow для `%s` без змін runtime.',
                $input->sourceType,
                $promptKey
            ),
            'result' => $artifacts,
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $preview
     * @param  array<string, int>  $distribution
     */
    protected function buildPromptId(
        PromptGenerationInput $input,
        array $source,
        array $preview,
        array $distribution,
        string $promptKey,
    ): string {
        $seed = implode('|', [
            $promptKey,
            $input->sourceType,
            $source['id'] ?? '',
            $source['slug'] ?? '',
            $source['normalized_url'] ?? ($source['url'] ?? ''),
            $source['topic'] ?? '',
            $input->siteDomain,
            $preview['target_namespace'] ?? '',
            $preview['class_name'] ?? '',
            implode(',', $input->levels),
            json_encode($distribution, JSON_UNESCAPED_SLASHES),
            $input->questionsPerLevel,
            $input->generationMode,
            $input->promptAMode,
        ]);

        return self::PROMPT_ID_PREFIX . strtoupper(substr(sha1($seed), 0, 8));
    }

    /**
     * @param  array<int, string>  $warnings
     * @return array<string, mixed>
     */
    protected function buildSourceContext(PromptGenerationInput $input, array &$warnings): array
    {
        return match ($input->sourceType) {
            'theory_page' => $this->buildTheoryPageContext($input->theoryPageId, $input->siteDomain),
            'external_url' => $this->buildExternalUrlContext($input->externalUrl, $warnings),
            default => $this->buildManualContext($input->manualTopic),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildTheoryPageContext(?int $pageId, string $siteDomain): array
    {
        $context = $pageId ? $this->theoryPageSearchService->promptContext($pageId, $siteDomain) : null;

        if (! $context) {
            throw new RuntimeException('Обрана theory page не знайдена.');
        }

        return $context;
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

        $topic = $fetched['title'] ?: $this->v3SeederBlueprintService->topicFromExternalUrl($normalizedUrl);

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
     * @param  array<string, int>  $distribution
     * @param  array<int, string>  $referenceFiles
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $preview
     */
    protected function buildSinglePrompt(
        PromptGenerationInput $input,
        array $source,
        array $preview,
        array $referenceFiles,
        array $distribution,
    ): string {
        $lines = [
            'You are working in repository `Pvitaly91/engapp-codex` on branch `main`.',
            '',
            'Create a fully compatible V3 grammar-test seeder package for this Laravel project.',
            '',
            'Topic and source',
            $this->formatSourceSection($source),
            '',
            'Target output',
            '- Target namespace inside `database/seeders/V3`: `' . $preview['target_namespace'] . '`',
            '- Suggested PHP class: `' . $preview['fully_qualified_class_name'] . '`',
            '- Suggested PHP loader stub: `' . $preview['seeder_relative_path'] . '`',
            '- Suggested seeder package folder: `' . $preview['package_relative_path'] . '`',
            '- Suggested real seeder PHP: `' . $preview['real_seeder_relative_path'] . '`',
            '- Suggested JSON definition path: `' . $preview['definition_relative_path'] . '`',
            '- Suggested localization JSON path (uk): `' . $preview['localization_uk_relative_path'] . '`',
            '- Suggested localization JSON path (en): `' . $preview['localization_en_relative_path'] . '`',
            '- Suggested localization JSON path (pl): `' . $preview['localization_pl_relative_path'] . '`',
            '- If nearby V3 seeders in this namespace use a slightly different but stronger local naming rule, follow that local convention consistently.',
            '',
            'Question plan',
            '- Levels: ' . implode(', ', array_keys($distribution)),
            '- Questions per level: ' . $input->questionsPerLevel,
            '- Total questions: ' . array_sum($distribution),
            $this->formatDistributionLines($distribution),
            '',
            'Hard requirements',
            '- Before generating files, inspect the real V3 implementation already present in `database/seeders/V3`.',
            '- Treat `app/Support/Database/JsonTestSeeder.php`, `app/Support/Database/JsonTestDirectorySeeder.php`, `app/Support/Database/JsonTestLocalizationManager.php`, and nearby V3 seeder packages as the compatibility contract.',
            '- Do not invent a new schema, new file layout, or new naming convention.',
            '- Follow the existing namespace, stub loader, package folder, real seeder class, `definition.json`, and `localizations/*.json` pattern used by neighboring V3 seeders.',
            '- Generate exactly the requested number of questions for every selected CEFR level.',
            '- Make the difficulty progression feel natural from the lowest selected level to the highest selected level.',
            '- The generated result must persist a real `SavedGrammarTest` entry with ordered question links, not only raw question data.',
            $this->savedTestUuidRequirement(),
            $this->savedTestSlugRequirement(),
            ...$this->savedTestQuestionUuidRules(),
            '- Materialize the seeder as a self-contained package: keep the top-level PHP file as a compatibility loader stub and place the real seeder PHP, `definition.json`, and all localization JSON files inside the seeder folder.',
            '- Use package-local localization JSON files under `localizations/uk.json`, `localizations/en.json`, and `localizations/pl.json` instead of any shared global localization directory.',
            '- In every companion localization JSON file, keep `target.definition_path` pointed at `../definition.json` and `target.seeder_class` matched to the base V3 seeder class.',
            $this->singlePromptQuestionsOnlyLocalizationRequirement(),
            $this->formatTheoryPageLinkageRequirement($source),
            '- Keep the final result fully compatible with the current Laravel V3 seeding system.',
            '',
            'Useful nearby references',
            $this->formatReferenceLines($referenceFiles),
            '',
            'Execution notes',
            '- Use the topic source below as the pedagogical source of truth for coverage and terminology.',
            '- Reuse the project’s existing V3 question JSON structure, marker format, options format, hints, explanations, tag organization, and source naming style.',
            ...$this->grammarTaggingRules(),
            '- Keep every learner-facing `question` and every `variants` entry in English only. Store Ukrainian teaching feedback in the seeder package localization flow (`localizations.uk` in the definition when required by the local pattern, plus the companion `localizations/uk.json` file).',
            '- Make Ukrainian `hints` and `explanations` genuinely instructional in both the definition content and the companion package localization JSON: explain the rule, mention the clue in the sentence, and clarify why distractors are wrong instead of using very short labels. Hints must guide the learner without revealing the final answer.',
            ...$this->detailedLocalizationQualityRules(),
            '- At the end, show: 1) changed files, 2) a short summary, 3) a per-level question count check.',
        ];

        return implode("\n", array_filter($lines, static fn ($line) => $line !== null));
    }

    /**
     * @param  array<string, int>  $distribution
     * @param  array<int, string>  $referenceFiles
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $preview
     */
    protected function buildLlmJsonPrompt(
        PromptGenerationInput $input,
        array $source,
        array $preview,
        array $referenceFiles,
        array $distribution,
    ): string {
        $schemaExample = <<<'TEXT'
{
  "schema_version": 1,
  "seeder": {
    "class": "Database\\Seeders\\V3\\Your\\Namespace\\YourSeederClass",
    "uuid_namespace": "YourSeederClass"
  },
  "defaults": {
    "default_locale": "uk",
    "flag": 0,
    "type": 0,
    "level_difficulty": {
      "A1": 1,
      "A2": 2,
      "B1": 3,
      "B2": 4,
      "C1": 5,
      "C2": 5
    }
  },
  "category": { "name": "..." },
  "sources": { "source_key": { "name": "..." } },
  "tags": {
    "grammar_core_rule": { "name": "Core Grammar Rule", "category": "grammar" },
    "grammar_subrule": { "name": "Specific Grammar Subrule", "category": "grammar_detail" }
  },
  "default_tag_keys": ["grammar_core_rule"],
  "questions": [
    {
      "id": 1,
      "question": "... {a1} ...",
      "source": "source_key",
      "level": "A1",
      "markers": {
        "a1": {
          "answer": "...",
          "options": ["...", "...", "..."],
          "verb_hint": "...",
          "gap_tags": ["grammar_subrule"]
        }
      },
      "localizations": {
        "uk": {
          "hints": ["..."],
          "explanations": {
            "a1": {
              "wrong_option": "...",
              "correct_option": "..."
            }
          }
        }
      },
      "tag_keys": ["grammar_subrule"],
      "variants": ["... {a1} ..."]
    }
  ]
}
TEXT;

        $promptModeLines = [
            'Prompt A mode',
            '- Selected Prompt A mode: ' . $this->promptAModeLabel($input->promptAMode),
        ];

        if ($input->promptAMode === 'no_repository') {
            $promptModeLines[] = '- This prompt must work without a connected repository. Do not assume live repo access.';
            $promptModeLines[] = '- Use the embedded compatibility reference below as the source of truth for schema, naming, and saved-test wiring.';
        } else {
            $promptModeLines[] = '- This prompt assumes the repository is connected. Inspect the real V3 files first and follow the live project contract.';
            $promptModeLines[] = '- Primary live repository references to inspect before generating JSON:';
            $promptModeLines[] = $this->formatReferenceLines($referenceFiles);
            $promptModeLines[] = '- If repository access is unavailable, switch this generator to Mode A2 / no-repository fallback instead of guessing.';
        }

        $lines = [
            'Generate one standalone `.json` file for the Laravel project `Pvitaly91/engapp-codex`.',
            '',
            'Preferred output mode',
            '- If your chat interface can generate downloadable files, return exactly one downloadable `.json` file and nothing else.',
            '- The attachment filename may be arbitrary. The JSON content must be correct.',
            '- If your chat interface cannot generate files, return only one fenced `json` code block with no commentary before or after it.',
            '',
            'This JSON must match the real V3 definition style used by the Laravel project `Pvitaly91/engapp-codex` inside self-contained seeder packages under `database/seeders/V3/<namespace>/<SeederClass>/definition.json`.',
            '',
            ...$promptModeLines,
            '',
            'Target metadata',
            '- `seeder.class`: `' . $preview['fully_qualified_class_name'] . '`',
            '- `seeder.uuid_namespace`: `' . $preview['class_name'] . '`',
            '- Planned loader stub path: `' . $preview['seeder_relative_path'] . '`',
            '- Planned seeder package folder: `' . $preview['package_relative_path'] . '`',
            '- Planned real seeder path: `' . $preview['real_seeder_relative_path'] . '`',
            '- Planned definition path: `' . $preview['definition_relative_path'] . '`',
            '- Planned localization path (uk): `' . $preview['localization_uk_relative_path'] . '`',
            '- Planned localization path (en): `' . $preview['localization_en_relative_path'] . '`',
            '- Planned localization path (pl): `' . $preview['localization_pl_relative_path'] . '`',
            '- Planned namespace inside `database/seeders/V3`: `' . $preview['target_namespace'] . '`',
            '',
            'Topic and source',
            $this->formatSourceSection($source),
            '',
            'Question plan',
            '- Levels: ' . implode(', ', array_keys($distribution)),
            '- Questions per level: ' . $input->questionsPerLevel,
            '- Total questions: ' . array_sum($distribution),
            $this->formatDistributionLines($distribution),
            '',
        ];

        if ($input->promptAMode === 'no_repository') {
            $lines[] = 'Embedded compatibility reference';
            $lines[] = $this->formatEmbeddedLlmCompatibilityReference($source, $preview, $referenceFiles, $distribution);
            $lines[] = '';
        }

        $lines = array_merge($lines, [
            'Required structure',
            $schemaExample,
            '',
            'Rules',
            '- Use the exact top-level keys shown above: `schema_version`, `seeder`, `defaults`, `category`, `sources`, `tags`, `default_tag_keys`, `questions`.',
            '- Use the real V3 pattern already used in this project. Do not invent another schema.',
            '- The final seeder built from this JSON must be able to persist a real `SavedGrammarTest` entry; include any saved-test metadata required by the neighboring V3 pattern.',
            $this->savedTestUuidRequirement(),
            $this->savedTestSlugRequirement(),
            ...$this->savedTestQuestionUuidRules(),
            '- Generate exactly the requested number of questions for every selected CEFR level.',
            '- Ensure every marker answer is present in its options list.',
            '- Keep source keys and tag keys stable and reusable.',
            ...$this->grammarTaggingRules(),
            '- Write every `question` and every `variants` entry in English only. Do not put Ukrainian translations into `variants`.',
            $this->llmJsonQuestionsOnlyLocalizationRequirement(),
            '- Keep `localizations.uk.hints` in Ukrainian, but make them detailed: use complete teaching sentences that name the rule and point to the clue in the sentence, while never revealing the exact correct answer or directly naming the correct option.',
            '- Make `localizations.uk.explanations` more detailed for every option: explain in Ukrainian why an option is correct or incorrect by referring to the plural rule, irregular form, or agreement pattern in context.',
            ...$this->detailedLocalizationQualityRules(),
            $this->formatTheoryPageLinkageRequirement($source, true),
            '- Make the JSON self-consistent and ready to be saved as a V3 definition file.',
        ]);

        return implode("\n", array_filter($lines, static fn ($line) => $line !== null));
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $preview
     * @param  array<int, string>  $referenceFiles
     * @param  array<string, int>  $distribution
     */
    protected function formatEmbeddedLlmCompatibilityReference(
        array $source,
        array $preview,
        array $referenceFiles,
        array $distribution,
    ): string {
        $savedTestExample = [
            '{',
            '  "saved_test": {',
            '    "uuid": "short-topic-saved-test",',
            '    "slug": "' . ($preview['topic_slug'] ?? 'topic-practice') . '",',
            '    "name": "Short Topic Practice",',
            '    "filters": {',
            '      "num_questions": ' . array_sum($distribution) . ',',
            '      "levels": ' . json_encode(array_keys($distribution), JSON_UNESCAPED_SLASHES) . ',',
            '      "seeder_classes": ["' . addslashes($preview['fully_qualified_class_name'] ?? '') . '"]',
            '    }',
            '  }',
            '}',
        ];

        $lines = [
            '- Observed naming pattern for this target namespace: top-level loader stub `' . ($preview['seeder_relative_path'] ?? '') . '`, real seeder class `' . ($preview['fully_qualified_class_name'] ?? '') . '`, package folder `' . ($preview['package_relative_path'] ?? '') . '`, `seeder.uuid_namespace` `' . ($preview['class_name'] ?? '') . '`, and base definition file `' . basename((string) ($preview['definition_relative_path'] ?? '')) . '`.',
            '- For AI-oriented namespaces in this repo, the normal local convention is a thin top-level `...V3QuestionsOnlySeeder.php` loader stub plus a same-named package folder containing the real seeder class, `definition.json`, and companion `localizations/*.json` files.',
            '- Real loader contract from `app/Support/Database/JsonTestSeeder.php`: use top-level keys `schema_version`, `seeder`, `defaults`, `category`, `sources`, `tags`, `default_tag_keys`, `questions`, and optional `saved_test`.',
            '- Real question contract: each question normally carries `id`, `question`, `source`, `level`, `markers`, `localizations`, `tag_keys`, `variants`; each `markers.<marker>` object uses `answer`, `options`, optional `verb_hint`, optional `gap_tags`.',
            '- Real tag contract: declare every reusable grammar tag key under top-level `tags`; use `default_tag_keys` only for shared umbrella grammar rules, question-level `tag_keys` for the grammar point tested by that question, and `markers.<marker>.gap_tags` when different gaps test different sub-rules.',
            '- Real localization contract inside the definition: under `localizations.<locale>`, use `hints` as an array and `explanations.<marker>.<option>` as per-option feedback text.',
            '- Real companion localization file contract from `app/Support/Database/JsonTestLocalizationManager.php`: package-local localization JSON files use top-level keys `schema_version`, `seeder`, `target`, `locale`, optional `hint_provider`, and `questions`.',
            '- Real companion localization targeting rules: keep these files inside the same seeder package under `localizations/<locale>.json`, set `target.definition_path` to `../definition.json`, and set `target.seeder_class` to the base V3 seeder class.',
            '- Keep all learner-facing question stems in English: both `question` and every entry in `variants` should stay English-only, while Ukrainian support belongs in `localizations.uk`.',
            '- For AI-oriented `...V3QuestionsOnlySeeder` targets, the repository keeps companion localization JSON files inside the seeder package, but the generated content still needs rich `localizations.uk.hints` and `localizations.uk.explanations` so Codex can materialize the package correctly.',
            '- Prefer detailed teaching feedback over very short labels: `hints` should explain the rule and the sentence clue, may include a similar example, and must not reveal the final correct answer or explicitly identify the correct option; `explanations` should explain why each option is right or wrong.',
            ...$this->detailedLocalizationQualityRules(),
            '- Real saved-test contract: if you include `saved_test`, it must define `uuid`, `slug`, and `name`; it may also define `description`, `question_uuids`, and `filters`.',
            '- Real saved-test rules from the loader: `saved_test.uuid` must be at most 36 characters; `filters.seeder_classes` must include `' . ($preview['fully_qualified_class_name'] ?? '') . '`; and `filters.num_questions` should equal ' . array_sum($distribution) . '.',
            '- Real saved-test rule from the loader and repository practice: every seeder must use its own unique `saved_test.slug`. If two seeders would share a slug, rename it with a provider- or namespace-specific suffix instead of reusing another test slug.',
            '- Prevent the runtime/preview failure `saved_test.question_uuids references questions that were not seeded.`',
            '- Never invent `saved_test.question_uuids` from planned numbering, `id`, CEFR labels, marker names, or a guessed UUID prefix.',
            '- If you include `saved_test.question_uuids`, derive it only from the final generated question UUIDs in this same JSON, keeping exactly the same UUID set and the same order.',
            '- If you are not fully certain that the list is exact, omit `saved_test.question_uuids` entirely. The loader will fall back to the seeded question UUID order automatically.',
            '- Minimal `saved_test` skeleton example:',
            '```json',
            ...$savedTestExample,
            '```',
        ];

        if (($source['source_type'] ?? null) === 'theory_page' && ! empty($source['id'])) {
            $lines[] = '- For a local theory-page source, mirror the theory linkage in `saved_test.filters.prompt_generator` exactly as specified later in this prompt. Treat `theory_page.page_seeder_class` as the stable page link and keep `theory_page_id` only as auxiliary metadata.';
        }

        if ($referenceFiles !== []) {
            $lines[] = '- Neighbor reference filenames embedded for offline use:';

            foreach ($referenceFiles as $path) {
                $lines[] = '  - `' . $path . '`';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, int>  $distribution
     * @param  array<int, string>  $referenceFiles
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $preview
     */
    protected function buildCodexSeederPrompt(
        PromptGenerationInput $input,
        array $source,
        array $preview,
        array $referenceFiles,
        array $distribution,
    ): string {
        $lines = [
            'You are working in repository `Pvitaly91/engapp-codex` on branch `main`.',
            '',
            'Take the attached or pasted JSON definition and integrate it into the project as a fully compatible V3 seeder.',
            '',
            'Input handling',
            '- One JSON file may be attached in Codex or pasted inline.',
            '- The uploaded filename may be arbitrary. Do not use the attachment name as the target repository filename.',
            '- Determine the final repository paths from the topic, target namespace, JSON contents, and the neighboring V3 seeders in this repo.',
            '',
            'Target',
            '- Target namespace: `' . $preview['target_namespace'] . '`',
            '- Planned PHP class: `' . $preview['fully_qualified_class_name'] . '`',
            '- Planned PHP loader stub: `' . $preview['seeder_relative_path'] . '`',
            '- Planned seeder package folder: `' . $preview['package_relative_path'] . '`',
            '- Planned real seeder PHP: `' . $preview['real_seeder_relative_path'] . '`',
            '- Planned JSON definition path: `' . $preview['definition_relative_path'] . '`',
            '- Planned localization JSON path (uk): `' . $preview['localization_uk_relative_path'] . '`',
            '- Planned localization JSON path (en): `' . $preview['localization_en_relative_path'] . '`',
            '- Planned localization JSON path (pl): `' . $preview['localization_pl_relative_path'] . '`',
            '- If nearby seeders in this namespace use a slightly different local suffix or file naming pattern, follow that local convention consistently.',
            '',
            'Question plan',
            '- Levels: ' . implode(', ', array_keys($distribution)),
            '- Questions per level: ' . $input->questionsPerLevel,
            '- Total questions: ' . array_sum($distribution),
            $this->formatDistributionLines($distribution),
            '',
            'Topic and source',
            $this->formatSourceSection($source),
            '',
            'Hard requirements',
            '- First inspect the real V3 implementation in `database/seeders/V3` plus `app/Support/Database/JsonTestSeeder.php`, `app/Support/Database/JsonTestDirectorySeeder.php`, and `app/Support/Database/JsonTestLocalizationManager.php`.',
            '- Do not invent a new schema, new loader logic, or a custom one-off seeder implementation.',
            '- Preserve the provided JSON question set as the canonical content. Do not rewrite or rebalance it unless a small technical compatibility fix is required.',
            '- Preserve incoming grammar tags when they are already specific and reusable. If `tags`, `default_tag_keys`, `questions[*].tag_keys`, or marker `gap_tags` are missing, overly generic, or inconsistent with the actual grammar rule, normalize them to the local V3 style without changing the question set.',
            '- If the provided JSON uses Ukrainian translations inside `variants`, reveals the final answer inside `localizations.uk.hints`, or has overly terse `localizations.uk.hints` / `localizations.uk.explanations`, normalize those fields to the local V3 standard while preserving the same question set, ordering, and level counts.',
            ...$this->grammarTaggingRules(true),
            $this->codexSeederQuestionsOnlyLocalizationRequirement(),
            '- Ensure the final `seeder.class`, namespace, loader stub, package folder, real seeder file path, JSON definition path, and localization JSON paths are all consistent.',
            '- The final integrated result must persist a real `SavedGrammarTest` entry with ordered question links so the public theory page can surface this test.',
            $this->savedTestUuidRequirement(true),
            $this->savedTestSlugRequirement(true),
            ...$this->savedTestQuestionUuidRules(true),
            '- Materialize the seeder as a self-contained package: keep the top-level PHP file as a compatibility loader stub and place the real seeder PHP, `definition.json`, and all companion localization JSON files inside the seeder folder.',
            '- Wire package-local localization JSON files with `target.definition_path: ../definition.json` and the correct `target.seeder_class` instead of introducing ad-hoc logic or any shared localization directory.',
            ...$this->detailedLocalizationQualityRules(),
            $this->formatTheoryPageLinkageRequirement($source),
            '- Verify the per-level counts before finishing.',
            '',
            'Useful nearby references',
            $this->formatReferenceLines($referenceFiles),
            '',
            'Final response',
            '- List the changed files.',
            '- Give a short summary of what was created.',
            '- Report the final counts per selected level.',
        ];

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

        if (($source['source_type'] ?? null) === 'theory_page') {
            $lines[] = '- Page id: `' . ($source['id'] ?? '') . '`';
            $lines[] = '- Page title: `' . ($source['title'] ?? '') . '`';
            $lines[] = '- Page slug: `' . ($source['slug'] ?? '') . '`';

            if (! empty($source['category_path'])) {
                $lines[] = '- Category/path: `' . $source['category_path'] . '`';
            }

            if (! empty($source['url'])) {
                $lines[] = '- Full URL: `' . $source['url'] . '`';
            }

            if (! empty($source['resolved_seeder_class'])) {
                $lines[] = '- Page or category seeder class: `' . $source['resolved_seeder_class'] . '`';
            }
        }

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
     * @param  array<string, mixed>  $source
     */
    protected function formatTheoryPageLinkageRequirement(array $source, bool $llmJsonMode = false): ?string
    {
        if (($source['source_type'] ?? null) !== 'theory_page' || empty($source['id'])) {
            return null;
        }

        $prefix = $llmJsonMode
            ? '- If the real neighboring V3 pattern persists a saved test payload, include source linkage in `saved_test.filters.prompt_generator` as a structured object'
            : '- Because this test is sourced from an existing local theory page, ensure the persisted saved test filters carry source linkage in `prompt_generator` as a structured object';

        $payload = [
            'source_type' => 'theory_page',
            'theory_page_id' => (int) $source['id'],
            'theory_page_ids' => [(int) $source['id']],
            'theory_page' => [
                'id' => (int) $source['id'],
                'slug' => $source['slug'] ?? '',
                'title' => $source['title'] ?? '',
                'category_slug_path' => $source['category_slug_path'] ?? '',
                'page_seeder_class' => $source['page_seeder_class'] ?? '',
                'url' => $source['url'] ?? '',
            ],
        ];

        return $prefix . ': `' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`. Use `theory_page.page_seeder_class` as the canonical page identifier so the link survives DB reseeding.';
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
     * @param  array<string, int>  $distribution
     */
    protected function formatDistributionLines(array $distribution): string
    {
        return implode("\n", array_map(
            static fn (string $level, int $count) => '- ' . $level . ': ' . $count . ' question(s)',
            array_keys($distribution),
            array_values($distribution)
        ));
    }

    /**
     * @return array<int, string>
     */
    protected function detailedLocalizationQualityRules(): array
    {
        return [
            '- Write at least two Ukrainian `hints` for every question: one must name the exact grammar or spelling rule, and another must point to the concrete clue in that sentence by quoting the actual trigger word(s) or structure.',
            '- Never put the exact gap answer, the full correct option text, or wording like "правильна відповідь" inside `hints`; hints may include a similar example or a leading clue, but the learner must still infer the answer independently.',
            '- Do not use vague clue phrasing like "слова на кшталт ..."; name the real cue from the sentence such as `two`, `many`, `are`, the noun ending, or the surrounding agreement pattern.',
            '- For every option inside `explanations`, write 1-2 full Ukrainian teaching sentences that identify the exact error type or success reason, such as singular instead of plural, wrong `-es` spelling, `y -> ies`, `f/fe -> ves`, irregular plural, zero plural, singular-only/plural-only noun, or agreement mismatch.',
            '- Avoid generic repeated templates like "не підходить, бо потрібна правильна форма"; vary the wording and tie each hint or explanation to the actual noun pattern and sentence context.',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function grammarTaggingRules(bool $integrationMode = false): array
    {
        return [
            $integrationMode
                ? '- Ensure every question ends up with grammar-filter tags: each question must have `tag_keys` naming the actual grammar concept(s) being tested so admins can filter question banks by grammar.'
                : '- Generate grammar-filter tags for every question: each question must include `tag_keys` naming the actual grammar concept(s) being tested so admins can filter question banks by grammar.',
            '- Prefer stable, reusable grammar tag keys for rule families and sub-rules, such as tense usage, article choice, modal meaning, conditional type, agreement pattern, passive structure, or gerund/infinitive contrast, instead of generic tags like `grammar`, `mixed`, `exercise`, or only topical labels.',
            '- Keep tags focused on the grammar mechanism, not the lesson title, topic summary, exercise format, provider, or UI shape. Do not generate broad/meta labels such as `Plural Nouns`, `Rules, exceptions, and usage`, `Single-gap multiple choice`, or similar page-title / task-format tags unless the label itself names a reusable grammar rule.',
            '- Name the specific rule or contrast being tested. Prefer tags such as `plural_s`, `plural_es_after_sibilant`, `plural_y_to_ies`, `plural_f_fe_to_ves`, `irregular_plural_nouns`, `zero_plural_nouns`, `plural_only_nouns`, `singular_only_nouns`, or `plural_subject_verb_agreement` over vague bucket tags.',
            '- Declare every used grammar tag key in top-level `tags`. If the whole test shares one umbrella rule, keep that umbrella rule in `default_tag_keys` and use question-level `tag_keys` for the more specific grammar point tested by each question.',
            '- When different markers inside the same question target different grammar points, add marker-level `gap_tags` for those markers so filtering can stay precise at gap level instead of collapsing everything into one broad tag.',
            '- If a question also carries topic or vocabulary tags, grammar-filter tags are still mandatory and should remain the clearest signal for grammar-based filtering.',
        ];
    }

    protected function singlePromptQuestionsOnlyLocalizationRequirement(): string
    {
        return '- For AI-oriented `...V3QuestionsOnlySeeder` targets, `QuestionsOnly` does not mean omitting teaching feedback: keep the base definition question-focused if that is the local pattern, but still generate companion localization JSON files with detailed `hints` and per-option `explanations` inside the same seeder package.';
    }

    protected function llmJsonQuestionsOnlyLocalizationRequirement(): string
    {
        return '- For AI-oriented `...V3QuestionsOnlySeeder` targets, include detailed `localizations.uk.hints` and `localizations.uk.explanations` in this JSON even if the repository later stores them in companion localization JSON files.';
    }

    protected function codexSeederQuestionsOnlyLocalizationRequirement(): string
    {
        return '- For AI-oriented `...V3QuestionsOnlySeeder` targets, if the provided JSON omits Ukrainian teaching feedback or keeps it terse, create or enrich the companion `localizations/uk.json` file inside the seeder package so every question gets detailed `hints` and per-option `explanations` without changing the question set.';
    }

    protected function savedTestUuidRequirement(bool $integrationMode = false): string
    {
        return $integrationMode
            ? '- Validate `saved_test` identifiers against database limits: `saved_test.uuid` must be at most 36 characters because it is stored in `saved_grammar_tests.uuid`. If it is longer, shorten only the UUID and keep the saved-test slug stable.'
            : '- Any generated `saved_test.uuid` must fit the database limit for `saved_grammar_tests.uuid`: at most 36 characters. Prefer a short slug-like UUID such as `<short-topic>-saved-test`.';
    }

    protected function savedTestSlugRequirement(bool $integrationMode = false): string
    {
        return $integrationMode
            ? '- Validate `saved_test.slug` for repository-wide uniqueness before finishing. If the incoming slug collides with a different saved-test UUID, rename the slug to a stable unique value instead of overwriting another test.'
            : '- Any generated `saved_test.slug` must be unique across repository V3 definitions. Derive it from the topic plus provider/namespace when needed; never reuse another seeder\'s saved-test slug.';
    }

    /**
     * @return array<int, string>
     */
    protected function savedTestQuestionUuidRules(bool $integrationMode = false): array
    {
        $rules = [
            '- Prevent the preview/runtime error `saved_test.question_uuids references questions that were not seeded.`',
            '- Never invent `saved_test.question_uuids` from planned numbering, numeric `id`, CEFR labels, marker names, or a guessed UUID prefix.',
            '- If you include `saved_test.question_uuids`, derive it only from the final generated question UUIDs in the same artifact (`questions[*].uuid` in the current JSON contract, or the final seeded question UUID list after integration), keeping exactly the same UUID set and the same order.',
            '- If you cannot guarantee an exact one-to-one match, omit `saved_test.question_uuids` entirely and let the loader use the seeded question UUID order automatically.',
        ];

        if ($integrationMode) {
            $rules[] = '- When integrating attached JSON, validate any incoming `saved_test.question_uuids` against the actual question UUIDs that will be seeded; if there is any mismatch, regenerate the list from the final UUIDs or remove the field before finishing.';
        }

        return $rules;
    }

    protected function promptAModeLabel(string $promptAMode): string
    {
        return $this->promptAModes()[$promptAMode] ?? $this->promptAModes()['repository_connected'];
    }
}
