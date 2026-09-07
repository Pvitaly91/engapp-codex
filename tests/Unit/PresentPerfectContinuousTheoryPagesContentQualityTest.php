<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PresentPerfectContinuousTheoryPagesContentQualityTest extends TestCase
{
    private const DIRECTORY = 'database/seeders/Page_V3/Tenses/PresentPerfectContinuous';

    private const ROUTE_PREFIX = '/theory/tenses/present-perfect-continuous/';

    private const LESSON_SLUGS = [
        'present-perfect-continuous-forms',
        'present-perfect-continuous-negatives',
        'present-perfect-continuous-questions',
        'present-perfect-continuous-time-expressions',
    ];

    /**
     * @return array<string, array{0: string, 1: string, 2: int, 3: class-string}>
     */
    public static function pageProvider(): array
    {
        return [
            'forms' => [
                'PresentPerfectContinuousFormsTheorySeeder',
                'present-perfect-continuous-forms-practice',
                0,
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousFormsAllLevelsLessonSeeder',
            ],
            'negatives' => [
                'PresentPerfectContinuousNegativesTheorySeeder',
                'present-perfect-continuous-negatives-practice',
                1,
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousNegativesAllLevelsLessonSeeder',
            ],
            'questions' => [
                'PresentPerfectContinuousQuestionsTheorySeeder',
                'present-perfect-continuous-questions-practice',
                2,
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousQuestionsAllLevelsLessonSeeder',
            ],
            'time expressions' => [
                'PresentPerfectContinuousTimeExpressionsTheorySeeder',
                'present-perfect-continuous-time-expressions-practice',
                3,
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousTimeExpressionsAllLevelsLessonSeeder',
            ],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_every_page_has_four_localized_practice_types_and_canonical_navigation(
        string $directory,
        string $practiceUuid,
        int $currentLesson,
        string $builderSeeder,
    ): void {
        $definition = $this->readJson(self::DIRECTORY.'/'.$directory.'/definition.json');
        $blocks = $definition['page']['blocks'] ?? [];

        $this->assertCount(8, $blocks, $directory.' should contain six content blocks, practice, and navigation.');
        $this->assertSame('summary-list', $blocks[5]['type'] ?? null);
        $this->assertSame('practice-set', $blocks[6]['type'] ?? null);
        $this->assertSame('navigation-chips', $blocks[7]['type'] ?? null);
        $this->assertSame($practiceUuid, $blocks[6]['uuid_key'] ?? null);

        $practice = $this->decodeBody($blocks[6]);
        $this->assertSame('6. Практика', $practice['title'] ?? null);
        $this->assertStringStartsWith('Вправа 1. Заповни пропуск', (string) ($practice['select_title'] ?? ''));
        $this->assertStringStartsWith('Вправа 2. Обери правильне твердження', (string) ($practice['choice_title'] ?? ''));
        $this->assertStringStartsWith('Вправа 3. Побудуй', (string) ($practice['input_title'] ?? ''));
        $this->assertStringStartsWith('Вправа 4. Побудуй', (string) ($practice['linked_practice']['title'] ?? ''));
        $this->assertPracticeContent($practice, $builderSeeder, 'uk');
        $this->assertCanonicalNavigation($this->decodeBody($blocks[7]), $currentLesson, '');

        if ($directory === 'PresentPerfectContinuousFormsTheorySeeder') {
            $formsGrid = $this->decodeBody($blocks[1]);
            $spellingSubtitle = (string) ($formsGrid['items'][2]['subtitle'] ?? '');

            $this->assertSame('Після been бери дієслово з -ing.', $spellingSubtitle);
            $this->assertPlainText($spellingSubtitle, 'UK forms-grid spelling subtitle');
        }

        foreach (['en', 'pl'] as $locale) {
            $localization = $this->readJson(
                self::DIRECTORY.'/'.$directory.'/localizations/'.$locale.'.json'
            );
            $localizedPracticeBlock = $this->blockAtIndex($localization['blocks'] ?? [], 7);
            $localizedNavigationBlock = $this->blockAtIndex($localization['blocks'] ?? [], 8);

            $this->assertNotNull($localizedPracticeBlock, strtoupper($locale).' practice is missing for '.$directory.'.');
            $this->assertNotNull($localizedNavigationBlock, strtoupper($locale).' navigation is missing for '.$directory.'.');

            $localizedPractice = $this->decodeBody($localizedPracticeBlock);
            $this->assertPracticeContent($localizedPractice, $builderSeeder, $locale);
            $this->assertSame(
                array_column($practice['selects'], 'answer'),
                array_column($localizedPractice['selects'], 'answer'),
                strtoupper($locale).' gap answers must stay aligned with UK.'
            );
            $this->assertSame(
                array_column($practice['choices'], 'answer'),
                array_column($localizedPractice['choices'], 'answer'),
                strtoupper($locale).' A/B answers must stay aligned with UK.'
            );
            $this->assertCanonicalNavigation(
                $this->decodeBody($localizedNavigationBlock),
                $currentLesson,
                '/'.$locale
            );

            if ($directory === 'PresentPerfectContinuousFormsTheorySeeder') {
                $localizedFormsGrid = $this->decodeBody($this->blockAtIndex($localization['blocks'], 2));
                $this->assertPlainText(
                    (string) ($localizedFormsGrid['items'][2]['subtitle'] ?? ''),
                    strtoupper($locale).' forms-grid spelling subtitle'
                );
            }
        }
    }

    private function assertPracticeContent(array $practice, string $builderSeeder, string $locale): void
    {
        $selects = $practice['selects'] ?? [];
        $options = $practice['options'] ?? [];
        $choices = $practice['choices'] ?? [];
        $choiceOptions = $practice['choice_options'] ?? [];
        $inputs = $practice['inputs'] ?? [];

        $this->assertCount(2, $selects, strtoupper($locale).' gap-fill exercise count.');
        $this->assertCount(2, $options, strtoupper($locale).' gap-fill options count.');

        foreach ($selects as $select) {
            $this->assertStringContainsString('____', (string) ($select['label'] ?? ''));
            $this->assertContains($select['answer'] ?? null, $options);
            $this->assertPlainText((string) ($select['label'] ?? ''), strtoupper($locale).' gap label');
            $this->assertPlainText((string) ($select['prompt'] ?? ''), strtoupper($locale).' gap prompt');
        }

        $this->assertCount(2, $choices, strtoupper($locale).' A/B exercise count.');
        $this->assertSame(['a', 'b'], $choiceOptions, strtoupper($locale).' A/B options.');

        foreach ($choices as $choice) {
            $label = (string) ($choice['label'] ?? '');

            $this->assertMatchesRegularExpression('/^a\)\s.+\s\/\s+b\)\s.+/u', $label);
            $this->assertContains($choice['answer'] ?? null, $choiceOptions);
            $this->assertPlainText($label, strtoupper($locale).' A/B label');
            $this->assertPlainText((string) ($choice['prompt'] ?? ''), strtoupper($locale).' A/B prompt');
        }

        $this->assertCount(2, $inputs, strtoupper($locale).' word-order exercise count.');

        foreach ($inputs as $input) {
            $chunks = array_values(array_filter(array_map(
                static fn (string $chunk): string => trim($chunk),
                explode('/', (string) ($input['before'] ?? ''))
            )));
            $answers = $input['accepted'] ?? $input['answers'] ?? $input['answer'] ?? [];
            $answers = is_array($answers) ? array_values($answers) : [$answers];
            $primaryAnswer = trim((string) ($answers[0] ?? ''));

            $this->assertGreaterThanOrEqual(3, count($chunks), strtoupper($locale).' token bank needs several chunks.');
            $this->assertSame('→', $input['after'] ?? null);
            $this->assertNotSame('', $primaryAnswer);
            $this->assertMatchesRegularExpression('/[.!?]$/u', $primaryAnswer);

            foreach ($chunks as $chunk) {
                $wordCount = count(preg_split('/\s+/u', $chunk, -1, PREG_SPLIT_NO_EMPTY) ?: []);
                $this->assertGreaterThanOrEqual(1, $wordCount, $chunk);
                $this->assertLessThanOrEqual(3, $wordCount, $chunk.' must contain no more than three words.');
                $this->assertPlainText($chunk, strtoupper($locale).' word-order chunk');
            }

            $this->assertSame(
                $this->sortedWords(implode(' ', $chunks)),
                $this->sortedWords($primaryAnswer),
                strtoupper($locale).' chunks must reconstruct the primary answer.'
            );
            $this->assertNotSame(
                $this->normalizeSentence(implode(' ', $chunks)),
                $this->normalizeSentence($primaryAnswer),
                strtoupper($locale).' chunks must be shuffled, not pre-sorted.'
            );
        }

        $linked = $practice['linked_practice'] ?? null;
        $this->assertIsArray($linked);
        $this->assertSame('theory_links', $linked['source'] ?? null);
        $this->assertSame(['4'], $linked['question_types'] ?? null);
        $this->assertSame([$builderSeeder], $linked['seeder_classes'] ?? null);
        $this->assertStringContainsString('4.', (string) ($linked['title'] ?? ''));

        foreach (['title', 'intro', 'footer'] as $visibleKey) {
            $value = trim((string) ($linked[$visibleKey] ?? ''));
            $this->assertNotSame('', $value, strtoupper($locale).' linked '.$visibleKey);
            $this->assertStringNotContainsStringIgnoringCase('polyglot', $value);
            $this->assertPlainText($value, strtoupper($locale).' linked '.$visibleKey);
        }

        $this->assertPlainPracticeTree($practice, strtoupper($locale).' practice');
    }

    private function assertCanonicalNavigation(array $navigation, int $currentLesson, string $localePrefix): void
    {
        $items = $navigation['items'] ?? [];
        $this->assertCount(4, $items);
        $this->assertSame(
            array_map(
                static fn (string $slug): string => $localePrefix.self::ROUTE_PREFIX.$slug,
                self::LESSON_SLUGS
            ),
            array_column($items, 'url')
        );
        $this->assertSame(
            [$currentLesson],
            array_keys(array_filter($items, static fn (array $item): bool => (bool) ($item['current'] ?? false)))
        );
    }

    private function assertPlainPracticeTree(array $practice, string $label): void
    {
        $walk = function (mixed $value, string $path) use (&$walk): void {
            if (is_array($value)) {
                foreach ($value as $key => $child) {
                    if ($key === 'seeder_classes') {
                        continue;
                    }

                    $walk($child, $path.'.'.$key);
                }

                return;
            }

            if (is_string($value)) {
                $this->assertPlainText($value, $path);
            }
        };

        $walk($practice, $label);
    }

    private function assertPlainText(string $value, string $label): void
    {
        $this->assertDoesNotMatchRegularExpression('/<\/?[a-z][^>]*>/iu', $value, $label.' contains raw HTML.');
        $this->assertDoesNotMatchRegularExpression('/\{a\d+\}/iu', $value, $label.' contains a service marker.');
        $this->assertDoesNotMatchRegularExpression('/&(?:#\d+|#x[0-9a-f]+|[a-z]+);/iu', $value, $label.' contains an HTML entity.');
    }

    /** @return array<int, string> */
    private function sortedWords(string $value): array
    {
        $normalized = mb_strtolower($value);
        $normalized = preg_replace("/[^\\p{L}\\p{N}'’]+/u", ' ', $normalized) ?? '';
        $words = preg_split('/\s+/u', trim($normalized), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        sort($words);

        return $words;
    }

    private function normalizeSentence(string $value): string
    {
        return implode(' ', $this->wordsInOrder($value));
    }

    /** @return array<int, string> */
    private function wordsInOrder(string $value): array
    {
        $normalized = mb_strtolower($value);
        $normalized = preg_replace("/[^\\p{L}\\p{N}'’]+/u", ' ', $normalized) ?? '';

        return preg_split('/\s+/u', trim($normalized), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /** @param array<int, array<string, mixed>> $blocks */
    private function blockAtIndex(array $blocks, int $index): ?array
    {
        foreach ($blocks as $block) {
            if (($block['index'] ?? null) === $index) {
                return $block;
            }
        }

        return null;
    }

    private function rootPath(): string
    {
        return dirname(__DIR__, 2);
    }

    private function readJson(string $relativePath): array
    {
        return json_decode(
            (string) file_get_contents($this->rootPath().'/'.$relativePath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    private function decodeBody(array $block): array
    {
        return json_decode((string) ($block['body'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
    }
}
