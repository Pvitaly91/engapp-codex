<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PolyglotUkrainianSourceLanguageTest extends TestCase
{
    private const SOURCE_FIELDS = [
        'question',
        'source_text_uk',
        'prompt_uk',
        'source_sentence_uk',
    ];

    private const GENERATED_PLACEHOLDER_MARKERS = [
        'склади пропущену частину речення:',
        'українська підказка',
        'до слово',
        'для українська',
        'з українська',
        '[',
        ']',
        '(a1 context)',
        '(a2 context)',
        '(b1 context)',
        '(b2 context)',
        '(c1 context)',
        '(c2 context)',
        '(a1)',
        '(a2)',
        '(b1)',
        '(b2)',
        '(c1)',
        '(c2)',
        '(a1 контекст)',
        '(a2 контекст)',
        '(b1 контекст)',
        '(b2 контекст)',
        '(c1 контекст)',
        '(c2 контекст)',
        '...',
        '…',
        'not sure',
        'advanced word order',
        'context)',
        'контекст)',
        'фразове дієслово',
    ];

    public function test_theory_page_polyglot_source_prompts_are_fully_ukrainian(): void
    {
        $definitionFiles = $this->polyglotDefinitionFiles();
        $relevantFiles = 0;
        $scannedQuestions = 0;
        $failures = [];

        foreach ($definitionFiles as $path) {
            $definition = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

            if (! $this->isRelevantComposeDefinition($definition)) {
                continue;
            }

            $relevantFiles++;

            foreach (($definition['questions'] ?? []) as $index => $question) {
                if (! is_array($question)) {
                    continue;
                }

                $scannedQuestions++;
                $uuid = $question['uuid'] ?? '#' . ($index + 1);
                $presentSourceFields = [];

                foreach (self::SOURCE_FIELDS as $field) {
                    if (! array_key_exists($field, $question)) {
                        continue;
                    }

                    $presentSourceFields[] = $field;
                    $value = $question[$field];

                    if (! is_string($value) || trim($value) === '') {
                        $failures[] = $this->failure($path, (string) $uuid, $field, 'source field is empty or not a string');
                        continue;
                    }

                    if (! preg_match('/\p{Cyrillic}/u', $value)) {
                        $failures[] = $this->failure($path, (string) $uuid, $field, 'source field has no Cyrillic text', $value);
                    }

                    if (preg_match('/[A-Za-z]{2,}/', $value)) {
                        $failures[] = $this->failure($path, (string) $uuid, $field, 'source field contains Latin words', $value);
                    }

                    foreach (self::GENERATED_PLACEHOLDER_MARKERS as $marker) {
                        if (str_contains(mb_strtolower($value), $marker)) {
                            $failures[] = $this->failure($path, (string) $uuid, $field, 'source field contains generated, cloze, or placeholder text', $value);
                        }
                    }
                }

                if ($presentSourceFields === []) {
                    $failures[] = $this->failure($path, (string) $uuid, 'source', 'question has no Ukrainian source field');
                }

                if (
                    array_key_exists('question', $question)
                    && array_key_exists('source_text_uk', $question)
                    && $question['question'] !== $question['source_text_uk']
                ) {
                    $failures[] = $this->failure(
                        $path,
                        (string) $uuid,
                        'question/source_text_uk',
                        'source fields are not synchronized',
                        (string) $question['question'],
                        (string) $question['source_text_uk']
                    );
                }
            }
        }

        $this->assertGreaterThan(0, $definitionFiles->count(), 'No Polyglot definition files were found.');
        $this->assertGreaterThan(0, $relevantFiles, 'No relevant compose Polyglot definition files were found.');
        $this->assertGreaterThan(0, $scannedQuestions, 'No compose Polyglot questions were scanned.');

        $this->assertSame(
            [],
            $failures,
            "Invalid Ukrainian source prompts were found.\n" . json_encode($failures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function polyglotDefinitionFiles()
    {
        return collect(File::allFiles(base_path('database/seeders/V3/Polyglot')))
            ->filter(fn ($file): bool => $file->getFilename() === 'definition.json')
            ->map(fn ($file): string => $file->getPathname())
            ->values();
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function isRelevantComposeDefinition(array $definition): bool
    {
        if ((int) data_get($definition, 'defaults.type') === 4) {
            return true;
        }

        if (data_get($definition, 'category.name') === 'English Sentence Builder') {
            return true;
        }

        if (in_array('polyglot_compose_tokens', data_get($definition, 'default_tag_keys', []), true)) {
            return true;
        }

        foreach (array_keys(data_get($definition, 'sources', [])) as $sourceKey) {
            if (str_starts_with((string) $sourceKey, 'theory_page_')) {
                return true;
            }
        }

        foreach (($definition['questions'] ?? []) as $question) {
            if (is_array($question) && (int) ($question['type'] ?? 0) === 4) {
                return true;
            }
        }

        return str_contains(json_encode($definition['saved_tests'] ?? [], JSON_THROW_ON_ERROR), 'theory_page');
    }

    private function failure(
        string $path,
        string $uuid,
        string $field,
        string $reason,
        ?string $value = null,
        ?string $otherValue = null
    ): array {
        $failure = [
            'file' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path),
            'uuid' => $uuid,
            'field' => $field,
            'reason' => $reason,
        ];

        if ($value !== null) {
            $failure['value'] = $value;
        }

        if ($otherValue !== null) {
            $failure['other_value'] = $otherValue;
        }

        return $failure;
    }
}
