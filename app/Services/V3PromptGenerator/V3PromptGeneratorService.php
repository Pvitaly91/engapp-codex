<?php

namespace App\Services\V3PromptGenerator;

use App\Services\V3PromptGenerator\Data\PromptGenerationInput;
use RuntimeException;

class V3PromptGeneratorService
{
    public const CEFR_LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    public function __construct(
        private TheoryPageSearchService $theoryPageSearchService,
        private ExternalTheoryUrlService $externalTheoryUrlService,
        private V3SeederBlueprintService $v3SeederBlueprintService,
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
                [
                    'key' => 'single',
                    'title' => 'Prompt for Codex',
                    'text' => $this->buildSinglePrompt($input, $source, $preview, $referenceFiles, $distribution),
                ],
            ]
            : [
                [
                    'key' => 'llm_json',
                    'title' => 'Prompt for LLM JSON generation',
                    'text' => $this->buildLlmJsonPrompt($input, $source, $preview, $referenceFiles, $distribution),
                ],
                [
                    'key' => 'codex_seeder',
                    'title' => 'Prompt for Codex seeder generation',
                    'text' => $this->buildCodexSeederPrompt($input, $source, $preview, $referenceFiles, $distribution),
                ],
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
            '- Suggested PHP seeder path: `' . $preview['seeder_relative_path'] . '`',
            '- Suggested JSON definition path: `' . $preview['definition_relative_path'] . '`',
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
            '- Treat `database/seeders/V3/Concerns/JsonTestSeeder.php`, `app/Support/Database/JsonTestSeeder.php`, and nearby V3 definition files as the compatibility contract.',
            '- Do not invent a new schema, new file layout, or new naming convention.',
            '- Follow the existing namespace, path, wrapper seeder, JSON definition, and localization pattern used by neighboring V3 seeders.',
            '- Generate exactly the requested number of questions for every selected CEFR level.',
            '- Make the difficulty progression feel natural from the lowest selected level to the highest selected level.',
            '- The generated result must persist a real `SavedGrammarTest` entry with ordered question links, not only raw question data.',
            $this->savedTestUuidRequirement(),
            '- If this namespace pattern uses a thin PHP wrapper seeder plus a JSON definition file, create both.',
            '- If nearby V3 seeders also rely on localization JSON files under `database/seeders/V3/localizations/...`, create or update the correct companion files instead of inventing custom runtime logic.',
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
            '- Keep every learner-facing `question` and every `variants` entry in English only. Use Ukrainian only inside `localizations.uk`.',
            '- Make `localizations.uk.hints` and `localizations.uk.explanations` genuinely instructional: explain the rule, mention the clue in the sentence, and clarify why distractors are wrong instead of using very short labels. Hints must guide the learner without revealing the final answer.',
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
  "tags": { "tag_key": { "name": "...", "category": "..." } },
  "default_tag_keys": ["theme", "detail", "structure"],
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
          "verb_hint": "..."
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
      "tag_keys": ["theme"],
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
            'This JSON must match the real V3 definition style used by the Laravel project `Pvitaly91/engapp-codex` in `database/seeders/V3/definitions`.',
            '',
            ...$promptModeLines,
            '',
            'Target metadata',
            '- `seeder.class`: `' . $preview['fully_qualified_class_name'] . '`',
            '- `seeder.uuid_namespace`: `' . $preview['class_name'] . '`',
            '- Planned definition path: `' . $preview['definition_relative_path'] . '`',
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
            '- Generate exactly the requested number of questions for every selected CEFR level.',
            '- Ensure every marker answer is present in its options list.',
            '- Keep source keys and tag keys stable and reusable.',
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
            '- Observed naming pattern for this target namespace: wrapper class `' . ($preview['fully_qualified_class_name'] ?? '') . '`, `seeder.uuid_namespace` `' . ($preview['class_name'] ?? '') . '`, definition file `' . basename((string) ($preview['definition_relative_path'] ?? '')) . '`.',
            '- For AI-oriented namespaces in this repo, the normal local convention is a thin `...V3QuestionsOnlySeeder` wrapper plus a definition file named `..._v3_questions_only.json`.',
            '- Real loader contract from `app/Support/Database/JsonTestSeeder.php`: use top-level keys `schema_version`, `seeder`, `defaults`, `category`, `sources`, `tags`, `default_tag_keys`, `questions`, and optional `saved_test`.',
            '- Real question contract: each question normally carries `id`, `question`, `source`, `level`, `markers`, `localizations`, `tag_keys`, `variants`; each `markers.<marker>` object uses `answer`, `options`, optional `verb_hint`, optional `gap_tags`.',
            '- Real localization contract: under `localizations.<locale>`, use `hints` as an array and `explanations.<marker>.<option>` as per-option feedback text.',
            '- Keep all learner-facing question stems in English: both `question` and every entry in `variants` should stay English-only, while Ukrainian support belongs in `localizations.uk`.',
            '- For AI-oriented `...V3QuestionsOnlySeeder` targets, the repository may later split Ukrainian teaching feedback into companion localization JSON files, but the generated content still needs rich `localizations.uk.hints` and `localizations.uk.explanations`.',
            '- Prefer detailed teaching feedback over very short labels: `hints` should explain the rule and the sentence clue, may include a similar example, and must not reveal the final correct answer or explicitly identify the correct option; `explanations` should explain why each option is right or wrong.',
            ...$this->detailedLocalizationQualityRules(),
            '- Real saved-test contract: if you include `saved_test`, it must define `uuid`, `slug`, and `name`; it may also define `description`, `question_uuids`, and `filters`.',
            '- Real saved-test rules from the loader: `saved_test.uuid` must be at most 36 characters; `filters.seeder_classes` must include `' . ($preview['fully_qualified_class_name'] ?? '') . '`; `filters.num_questions` should equal ' . array_sum($distribution) . '; and `saved_test.question_uuids`, if present, must match the generated question UUID set in the same order.',
            '- Minimal `saved_test` skeleton example:',
            '```json',
            ...$savedTestExample,
            '```',
        ];

        if (($source['source_type'] ?? null) === 'theory_page' && ! empty($source['id'])) {
            $lines[] = '- For a local theory-page source, mirror the theory linkage in `saved_test.filters.prompt_generator` exactly as specified later in this prompt.';
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
            '- Planned PHP seeder path: `' . $preview['seeder_relative_path'] . '`',
            '- Planned JSON definition path: `' . $preview['definition_relative_path'] . '`',
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
            '- First inspect the real V3 implementation in `database/seeders/V3`, especially `database/seeders/V3/Concerns/JsonTestSeeder.php`, and compare it with the JSON contract in `app/Support/Database/JsonTestSeeder.php`.',
            '- Do not invent a new schema, new loader logic, or a custom one-off seeder implementation.',
            '- Preserve the provided JSON question set as the canonical content. Do not rewrite or rebalance it unless a small technical compatibility fix is required.',
            '- If the provided JSON uses Ukrainian translations inside `variants`, reveals the final answer inside `localizations.uk.hints`, or has overly terse `localizations.uk.hints` / `localizations.uk.explanations`, normalize those fields to the local V3 standard while preserving the same question set, ordering, and level counts.',
            $this->codexSeederQuestionsOnlyLocalizationRequirement(),
            '- Ensure the final `seeder.class`, namespace, wrapper seeder file path, and JSON definition path are all consistent.',
            '- The final integrated result must persist a real `SavedGrammarTest` entry with ordered question links so the public theory page can surface this test.',
            $this->savedTestUuidRequirement(true),
            '- If neighboring V3 seeders in this namespace also use localization JSON files, wire them correctly instead of introducing ad-hoc logic.',
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
                'url' => $source['url'] ?? '',
            ],
        ];

        return $prefix . ': `' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`.';
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

    protected function singlePromptQuestionsOnlyLocalizationRequirement(): string
    {
        return '- For AI-oriented `...V3QuestionsOnlySeeder` targets, `QuestionsOnly` does not mean omitting teaching feedback: keep the base definition question-focused if that is the local pattern, but still generate companion localization JSON files with detailed `hints` and per-option `explanations`.';
    }

    protected function llmJsonQuestionsOnlyLocalizationRequirement(): string
    {
        return '- For AI-oriented `...V3QuestionsOnlySeeder` targets, include detailed `localizations.uk.hints` and `localizations.uk.explanations` in this JSON even if the repository later stores them in companion localization JSON files.';
    }

    protected function codexSeederQuestionsOnlyLocalizationRequirement(): string
    {
        return '- For AI-oriented `...V3QuestionsOnlySeeder` targets, if the provided JSON omits Ukrainian teaching feedback or keeps it terse, create or enrich the companion `database/seeders/V3/localizations/uk/...` JSON so every question gets detailed `hints` and per-option `explanations` without changing the question set.';
    }

    protected function savedTestUuidRequirement(bool $integrationMode = false): string
    {
        return $integrationMode
            ? '- Validate `saved_test` identifiers against database limits: `saved_test.uuid` must be at most 36 characters because it is stored in `saved_grammar_tests.uuid`. If it is longer, shorten only the UUID and keep the saved-test slug stable.'
            : '- Any generated `saved_test.uuid` must fit the database limit for `saved_grammar_tests.uuid`: at most 36 characters. Prefer a short slug-like UUID such as `<short-topic>-saved-test`.';
    }

    protected function promptAModeLabel(string $promptAMode): string
    {
        return $this->promptAModes()[$promptAMode] ?? $this->promptAModes()['repository_connected'];
    }
}
