<?php

namespace App\Services\PageV3PromptGenerator;

use App\Services\PageV3PromptGenerator\Data\TheoryVariantPromptGenerationInput;
use App\Support\TheoryVariantPayloadSanitizer;
use Database\Seeders\Pages\Concerns\GrammarPageVariantSeeder as GrammarPageVariantSeederBase;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use Throwable;

class VariantSeederPromptBuilder
{
    private const FALLBACK_BASE_VARIANT_SEEDER_FQCN = GrammarPageVariantSeederBase::class;

    /**
     * @var array<int, string>
     */
    private const CONTRACT_METHODS = [
        'targetType',
        'targetCategorySlug',
        'targetPageSlug',
        'locale',
        'variantKey',
        'label',
        'payload',
        'provider',
        'model',
        'promptVersion',
        'status',
        'sourceHash',
    ];

    private const TEMPLATE = <<<'PROMPT'
Generate exactly one PHP file for a Laravel project.

Preferred output mode:
{{PREFERRED_OUTPUT_MODE}}

Task type:
This is NOT a question seeder.
This is NOT a JSON V3 test definition.
This is NOT a SavedGrammarTest generator.
This is a THEORY PAGE VARIANT SEEDER for an existing /theory page or /theory category.

Repository assumptions:
- You do NOT need live repository access.
- Work only from the compatibility contract and the metadata embedded below.
- Do not invent a different architecture.
- Assume the project already contains a base class named:
  `{{BASE_VARIANT_SEEDER_FQCN}}`

Your job:
Generate one valid PHP seeder class that extends `{{BASE_VARIANT_SEEDER_CLASS}}` and represents exactly one theory-content variant.

Compatibility contract to follow:

```php
{{REAL_VARIANT_SEEDER_CONTRACT_SNIPPET}}
```

Important inherited behavior already present in the base class:
- Do not create or override controllers, routes, views, migrations, models, or theory runtime logic.
- Do not override `run()` unless it is truly necessary.
- The inherited preview/persist flow sanitizes `array_merge($this->payload(), ['locale' => $this->locale()])` through `App\Support\TheoryVariantPayloadSanitizer`.
- The inherited preview/persist flow persists target identity, locale, variant key, label, provider, model, status, prompt version, payload, source hash, and seeder class.

Payload contract to follow:
- `payload()` must return an array compatible with `App\Support\TheoryVariantPayloadSanitizer::sanitizePayload()`.
- The effective payload shape is:

```php
{{REAL_VARIANT_PAYLOAD_SNIPPET}}
```

Content rules:
- Preserve the same grammar topic and learner intent as the source page/category.
- Do not change page identity, slug, category slug, or route semantics.
- Write a fresh alternative explanation, not a copy of the source.
- Keep explanations clean, structured, and teacher-friendly.
- Keep the content logically chunked into blocks.
- Keep the content compatible with existing theory page rendering.
- Use only safe HTML supported by the sanitizer.
- Allowed HTML tags:
  {{ALLOWED_HTML_TAGS}}
- Forbidden:
  script, style, iframe, object, embed, form, input, button, meta, link, base, inline JS handlers, javascript: URLs, vbscript: URLs, data:text/html URLs
- Do not generate JavaScript.
- Do not generate CSS files.
- Do not generate migrations, controllers, or routes.
- Output only the seeder class.

Style rules:
- Valid PHP 8.1+
- Laravel-style namespace and class naming
- Minimal comments
- Do not invent helper classes or traits
- Do not reference files that are not necessary
- Do not assume additional methods beyond the live contract above unless they are inherited Seeder behavior

Important anti-confusion rules:
- Do not output a QuestionSeeder
- Do not output JsonTestSeeder
- Do not output V3 JSON
- Do not output tests
- Do not output markdown explanation
- Output only one theory variant seeder class

Embedded target metadata:
- namespace: {{NAMESPACE}}
- class_name: {{CLASS_NAME}}
- target_type: {{TARGET_TYPE}}
- target_category_slug: {{CATEGORY_SLUG}}
- target_page_slug: {{PAGE_SLUG_OR_NULL}}
- locale: {{LOCALE}}
- variant_key: {{VARIANT_KEY}}
- label: {{LABEL}}
- provider: {{PROVIDER}}
- model: {{MODEL}}
- prompt_version: {{PROMPT_VERSION}}

Source identity:
- source_url: {{SOURCE_URL}}
- source_page_title: {{SOURCE_TITLE}}
- source_category_title: {{SOURCE_CATEGORY_TITLE}}
- source_page_seeder_class: {{SOURCE_SEEDER_CLASS}}
- source_locale_requested: {{SOURCE_LOCALE_REQUESTED}}
- source_locale_used: {{SOURCE_LOCALE_USED}}

Desired rewrite brief:
- target learner levels: {{TARGET_LEVELS}}
- tone: {{TONE}}
- rewrite goal: {{REWRITE_GOAL}}
- keep / expand / simplify: {{CONTENT_STRATEGY}}
- must cover these subtopics:
  {{MUST_COVER_LIST}}
- avoid these problems:
  {{AVOID_LIST}}

Embedded source material:
1. Current page/category content:
{{SOURCE_PAGE_CONTENT}}

2. Normalized payload excerpt:
```json
{{OPTIONAL_BASE_PAYLOAD_EXCERPT}}
```

3. Optional notes from teacher/editor:
{{EDITOR_NOTES}}

Output requirement:
Return exactly one valid PHP seeder class file matching all rules above.
PROMPT;

    public function __construct(
        private TheoryVariantSourceResolver $theoryVariantSourceResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function generate(TheoryVariantPromptGenerationInput $input): array
    {
        $source = $this->theoryVariantSourceResolver->resolve($input);
        $baseSeeder = $this->baseVariantSeeder();
        $form = $this->resolvedForm($input, $source);
        $prompt = $this->renderPrompt($form, $source, $baseSeeder);

        return [
            'form' => $form,
            'source' => $source,
            'source_content_excerpt' => $this->renderSourceContent($source),
            'prompts' => [[
                'key' => 'theory-variant',
                'title' => 'Theory Variant Prompt',
                'text' => $prompt,
            ]],
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, string>
     */
    protected function resolvedForm(TheoryVariantPromptGenerationInput $input, array $source): array
    {
        $locale = strtolower(trim($input->locale));
        $categoryStudly = $this->studly($source['category_title'] ?? $source['target_category_slug'] ?? 'Theory Category');
        $targetStudly = $source['target_type'] === 'page'
            ? $this->studly($source['page_title'] ?? $source['target_page_slug'] ?? 'Theory Page')
            : $this->studly($source['category_title'] ?? $source['target_category_slug'] ?? 'Theory Category');
        $localeStudly = $this->studly($locale);

        return [
            'source_lookup_url' => $input->sourceLookupUrl ?? '',
            'target_type' => (string) $source['target_type'],
            'target_category_slug' => (string) $source['target_category_slug'],
            'target_page_slug' => (string) ($source['target_page_slug'] ?? ''),
            'locale' => $locale,
            'namespace' => $input->targetNamespace ?: 'Database\\Seeders\\Page_v2\\Variants\\'.$categoryStudly,
            'class_name' => $input->className ?: $targetStudly.$localeStudly.'V1Seeder',
            'variant_key' => $input->variantKey ?: 'generated-'.$locale.'-v1',
            'label' => $input->label ?: 'Generated '.strtoupper($locale).' v1',
            'provider' => $input->provider ?: '',
            'model' => $input->model ?: '',
            'prompt_version' => $input->promptVersion ?: 'v1',
            'source_url' => $input->sourceUrl ?: (string) ($source['source_url'] ?? ''),
            'source_page_title' => $input->sourcePageTitle ?: (string) ($source['page_title'] ?? ''),
            'source_category_title' => $input->sourceCategoryTitle ?: (string) ($source['category_title'] ?? ''),
            'source_page_seeder_class' => $input->sourcePageSeederClass ?: (string) ($source['page_seeder_class'] ?? ''),
            'target_learner_levels' => $input->targetLearnerLevels ?: '',
            'tone' => $input->tone ?: '',
            'rewrite_goal' => $input->rewriteGoal ?: '',
            'content_strategy' => $input->contentStrategy ?: '',
            'must_cover_list' => $input->mustCoverList ?: '',
            'avoid_list' => $input->avoidList ?: '',
            'editor_notes' => $input->editorNotes ?: '',
            'output_mode_preference' => $input->outputModePreference,
        ];
    }

    /**
     * @param  array<string, string>  $form
     * @param  array<string, mixed>  $source
     * @param  array<string, string>  $baseSeeder
     */
    protected function renderPrompt(array $form, array $source, array $baseSeeder): string
    {
        return strtr(self::TEMPLATE, [
            '{{PREFERRED_OUTPUT_MODE}}' => $this->preferredOutputModeBlock($form['output_mode_preference']),
            '{{BASE_VARIANT_SEEDER_FQCN}}' => $baseSeeder['fqcn'],
            '{{BASE_VARIANT_SEEDER_CLASS}}' => $baseSeeder['class'],
            '{{REAL_VARIANT_SEEDER_CONTRACT_SNIPPET}}' => $baseSeeder['contract_snippet'],
            '{{REAL_VARIANT_PAYLOAD_SNIPPET}}' => $this->payloadContractSnippet($form['locale']),
            '{{ALLOWED_HTML_TAGS}}' => $this->allowedHtmlTags(),
            '{{LOCALE}}' => $form['locale'],
            '{{NAMESPACE}}' => $form['namespace'],
            '{{CLASS_NAME}}' => $form['class_name'],
            '{{TARGET_TYPE}}' => $form['target_type'],
            '{{CATEGORY_SLUG}}' => $form['target_category_slug'],
            '{{PAGE_SLUG_OR_NULL}}' => $this->literalOrNull($form['target_page_slug']),
            '{{VARIANT_KEY}}' => $form['variant_key'],
            '{{LABEL}}' => $form['label'],
            '{{PROVIDER}}' => $this->literalOrNull($form['provider']),
            '{{MODEL}}' => $this->literalOrNull($form['model']),
            '{{PROMPT_VERSION}}' => $this->literalOrNull($form['prompt_version']),
            '{{SOURCE_URL}}' => $this->literalOrNull($form['source_url']),
            '{{SOURCE_TITLE}}' => $this->literalOrNull($form['source_page_title']),
            '{{SOURCE_CATEGORY_TITLE}}' => $this->literalOrNull($form['source_category_title']),
            '{{SOURCE_SEEDER_CLASS}}' => $this->literalOrNull($form['source_page_seeder_class']),
            '{{SOURCE_LOCALE_REQUESTED}}' => $this->literalOrNull((string) ($source['requested_locale'] ?? '')),
            '{{SOURCE_LOCALE_USED}}' => $this->literalOrNull((string) ($source['source_locale'] ?? '')),
            '{{TARGET_LEVELS}}' => $this->paragraphOrFallback($form['target_learner_levels']),
            '{{TONE}}' => $this->paragraphOrFallback($form['tone']),
            '{{REWRITE_GOAL}}' => $this->paragraphOrFallback($form['rewrite_goal']),
            '{{CONTENT_STRATEGY}}' => $this->paragraphOrFallback($form['content_strategy']),
            '{{MUST_COVER_LIST}}' => $this->bulletList($form['must_cover_list']),
            '{{AVOID_LIST}}' => $this->bulletList($form['avoid_list']),
            '{{SOURCE_PAGE_CONTENT}}' => $this->renderSourceContent($source),
            '{{OPTIONAL_BASE_PAYLOAD_EXCERPT}}' => (string) ($source['normalized_payload_json'] ?? '{}'),
            '{{EDITOR_NOTES}}' => $this->paragraphOrFallback($form['editor_notes']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $source
     */
    protected function renderSourceContent(array $source): string
    {
        $payload = is_array($source['normalized_payload'] ?? null) ? $source['normalized_payload'] : [];
        $blocks = collect($payload['blocks'] ?? [])->filter(fn ($block) => is_array($block))->values();
        $lines = [
            '- source_type: '.($source['target_type'] ?? 'page'),
            '- source_url: '.($source['source_url'] ?? 'null'),
            '- source_page_title: '.($source['page_title'] ?? 'null'),
            '- source_category_title: '.($source['category_title'] ?? 'null'),
            '- source_locale_requested: '.($source['requested_locale'] ?? 'null'),
            '- source_locale_used: '.($source['source_locale'] ?? 'null'),
            '- title: '.($payload['title'] ?? 'null'),
            '- subtitle_html:',
            $this->indentMultiline($payload['subtitle_html'] ?? 'null', 2),
            '- subtitle_text: '.($payload['subtitle_text'] ?? 'null'),
            '- locale: '.($payload['locale'] ?? 'null'),
            '- blocks:',
        ];

        if ($blocks->isEmpty()) {
            $lines[] = '  - none';
        } else {
            foreach ($blocks as $index => $block) {
                $lines[] = '  - #'.($index + 1)
                    .' column='.($block['column'] ?? 'null')
                    .' | type='.($block['type'] ?? 'null')
                    .' | sort_order='.($block['sort_order'] ?? 0)
                    .' | level='.($block['level'] ?? 'null');
                $lines[] = '    heading: '.($block['heading'] ?? 'null');
                $lines[] = '    body:';
                $lines[] = $this->indentMultiline($block['body'] ?? 'null', 6);
            }
        }

        $childPages = collect($source['child_pages'] ?? [])->filter(fn ($page) => is_array($page))->values();

        if ($childPages->isNotEmpty()) {
            $lines[] = '- child_pages (limited):';

            foreach ($childPages->take(8) as $page) {
                $lines[] = '  - '.($page['title'] ?? 'Untitled').' ['.($page['slug'] ?? 'no-slug').']';
            }

            if ($childPages->count() > 8) {
                $lines[] = '  - ... +'.($childPages->count() - 8).' more';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @return array{fqcn: string, class: string, contract_snippet: string}
     */
    protected function baseVariantSeeder(): array
    {
        $fqcn = self::FALLBACK_BASE_VARIANT_SEEDER_FQCN;
        $class = Str::afterLast($fqcn, '\\') ?: 'GrammarPageVariantSeeder';

        return [
            'fqcn' => $fqcn,
            'class' => $class,
            'contract_snippet' => $this->realVariantSeederContractSnippet($fqcn, $class),
        ];
    }

    protected function realVariantSeederContractSnippet(string $fqcn, string $class): string
    {
        try {
            $reflection = new ReflectionClass($fqcn);
            $filePath = $reflection->getFileName();

            if (! is_string($filePath) || ! is_file($filePath)) {
                return $this->fallbackContractSnippet($class);
            }

            $lines = file($filePath, FILE_IGNORE_NEW_LINES);

            if (! is_array($lines)) {
                return $this->fallbackContractSnippet($class);
            }

            $methodSnippets = collect(self::CONTRACT_METHODS)
                ->filter(fn (string $methodName) => $reflection->hasMethod($methodName))
                ->map(fn (string $methodName) => $this->extractMethodSnippet($reflection->getMethod($methodName), $lines))
                ->filter()
                ->values()
                ->all();

            if ($methodSnippets === []) {
                return $this->fallbackContractSnippet($class);
            }

            $body = collect($methodSnippets)
                ->map(fn (string $snippet) => $this->indentBlock($snippet, 4))
                ->implode("\n\n");

            return trim(implode("\n", [
                'abstract class '.$class.' extends Seeder',
                '{',
                $body,
                '}',
            ]));
        } catch (Throwable) {
            return $this->fallbackContractSnippet($class);
        }
    }

    /**
     * @param  array<int, string>  $lines
     */
    protected function extractMethodSnippet(ReflectionMethod $method, array $lines): string
    {
        $start = $method->getStartLine();
        $end = $method->getEndLine();

        if ($start < 1 || $end < $start) {
            return '';
        }

        return trim(implode("\n", array_slice($lines, $start - 1, $end - $start + 1)));
    }

    protected function fallbackContractSnippet(string $class): string
    {
        return trim(implode("\n", [
            'abstract class '.$class.' extends Seeder',
            '{',
            '    abstract protected function targetType(): string;',
            '',
            '    abstract protected function targetCategorySlug(): string;',
            '',
            '    protected function targetPageSlug(): ?string',
            '    {',
            '        return null;',
            '    }',
            '',
            '    abstract protected function locale(): string;',
            '',
            '    abstract protected function variantKey(): string;',
            '',
            '    abstract protected function label(): string;',
            '',
            '    abstract protected function payload(): array;',
            '',
            '    protected function provider(): ?string',
            '    {',
            '        return null;',
            '    }',
            '',
            '    protected function model(): ?string',
            '    {',
            '        return null;',
            '    }',
            '',
            '    protected function promptVersion(): ?string',
            '    {',
            '        return null;',
            '    }',
            '',
            '    protected function status(): string',
            '    {',
            "        return 'ready';",
            '    }',
            '',
            '    protected function sourceHash(array $payload): ?string',
            '    {',
            "        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');",
            '    }',
            '}',
        ]));
    }

    protected function payloadContractSnippet(string $locale): string
    {
        $payload = TheoryVariantPayloadSanitizer::sanitizePayload([
            'title' => '...',
            'subtitle_html' => '<p>...</p>',
            'subtitle_text' => '...',
            'locale' => $locale,
            'blocks' => [[
                'column' => 'left',
                'type' => 'box',
                'heading' => '...',
                'body' => '<p>...</p>',
                'sort_order' => 10,
                'level' => 'B1',
            ]],
        ]);

        return $this->exportPhpValue($payload);
    }

    protected function allowedHtmlTags(): string
    {
        $tags = $this->reflectedAllowedHtmlTags();

        return implode(', ', $tags);
    }

    /**
     * @return array<int, string>
     */
    protected function reflectedAllowedHtmlTags(): array
    {
        try {
            $reflection = new ReflectionClass(TheoryVariantPayloadSanitizer::class);
            $constant = collect($reflection->getReflectionConstants())
                ->first(fn (ReflectionClassConstant $item) => $item->getName() === 'ALLOWED_HTML_TAGS');

            $value = $constant?->getValue();

            if (is_array($value)) {
                return array_values(array_filter($value, fn ($tag) => is_string($tag) && $tag !== ''));
            }
        } catch (Throwable) {
            // Fall back to the current known sanitizer tags below.
        }

        return [
            'p',
            'ul',
            'ol',
            'li',
            'strong',
            'em',
            'table',
            'thead',
            'tbody',
            'tr',
            'th',
            'td',
            'code',
            'pre',
            'br',
            'span',
        ];
    }

    protected function exportPhpValue(mixed $value, int $indent = 0): string
    {
        $padding = str_repeat(' ', $indent);

        if (is_array($value)) {
            if ($value === []) {
                return '[]';
            }

            $lines = ['['];
            $isList = array_is_list($value);

            foreach ($value as $key => $item) {
                $entry = $this->exportPhpValue($item, $indent + 4);

                if (! $isList) {
                    $entry = $this->exportPhpKey($key).' => '.$entry;
                }

                $lines[] = str_repeat(' ', $indent + 4).$entry.',';
            }

            $lines[] = $padding.']';

            return implode("\n", $lines);
        }

        return match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value), is_float($value) => (string) $value,
            default => "'".str_replace(
                ['\\', "'"],
                ['\\\\', "\\'"],
                (string) $value
            )."'",
        };
    }

    protected function exportPhpKey(mixed $key): string
    {
        return is_int($key)
            ? (string) $key
            : "'".str_replace(
                ['\\', "'"],
                ['\\\\', "\\'"],
                (string) $key
            )."'";
    }

    protected function indentBlock(string $value, int $spaces): string
    {
        $indent = str_repeat(' ', $spaces);

        return $indent.str_replace("\n", "\n".$indent, trim($value));
    }

    protected function preferredOutputModeBlock(string $outputModePreference): string
    {
        if ($outputModePreference === 'fenced_php_code_block') {
            return implode("\n", [
                '- Prefer exactly one fenced `php` code block and nothing else.',
                '- If your interface cannot return fenced code blocks cleanly, return exactly one downloadable `.php` file and nothing else.',
                '- Do not add commentary before or after the file.',
            ]);
        }

        return implode("\n", [
            '- If your interface supports downloadable files, return exactly one downloadable `.php` file and nothing else.',
            '- If your interface cannot return files, return exactly one fenced `php` code block and nothing else.',
            '- Do not add commentary before or after the file.',
        ]);
    }

    protected function paragraphOrFallback(?string $value): string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : 'Not specified.';
    }

    protected function literalOrNull(?string $value): string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : 'null';
    }

    protected function bulletList(?string $value): string
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) $value) ?: [])
            ->map(fn (string $line) => trim(preg_replace('/^[-*•\s]+/u', '', $line) ?? $line))
            ->filter()
            ->values();

        if ($lines->isEmpty()) {
            return '- None specified.';
        }

        return $lines->map(fn (string $line) => '- '.$line)->implode("\n  ");
    }

    protected function indentMultiline(?string $value, int $spaces): string
    {
        $indent = str_repeat(' ', $spaces);
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return $indent.'null';
        }

        return $indent.str_replace("\n", "\n".$indent, $normalized);
    }

    protected function studly(string $value): string
    {
        return Str::studly(Str::slug($value, ' ')) ?: 'TheoryVariant';
    }
}
