<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PastPerfectTheoryPagesContentQualityTest extends TestCase
{
    private const DIRECTORY = 'database/seeders/Page_V3/Tenses/PastPerfect';

    /** @return array<string, array{0: string, 1: string, 2: int}> */
    public static function pageProvider(): array
    {
        return [
            'forms' => ['PastPerfectFormsTheorySeeder', 'past-perfect-forms-practice', 0],
            'negatives' => ['PastPerfectNegativesTheorySeeder', 'past-perfect-negatives-practice', 1],
            'questions' => ['PastPerfectQuestionsTheorySeeder', 'past-perfect-questions-practice', 2],
            'time expressions' => ['PastPerfectTimeExpressionsTheorySeeder', 'past-perfect-time-expressions-practice', 3],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_every_lesson_has_localized_checkable_mini_practice(
        string $directory,
        string $practiceUuid,
        int $currentLesson
    ): void {
        $definition = $this->readJson(self::DIRECTORY.'/'.$directory.'/definition.json');
        $blocks = $definition['page']['blocks'] ?? [];

        $this->assertCount(7, $blocks, $directory.' should have content, practice, and navigation.');
        $this->assertSame('summary-list', $blocks[4]['type'] ?? null);
        $this->assertSame('practice-set', $blocks[5]['type'] ?? null);
        $this->assertSame('navigation-chips', $blocks[6]['type'] ?? null);
        $this->assertSame($practiceUuid, $blocks[5]['uuid_key'] ?? null);

        $practice = $this->decodeBody($blocks[5]);
        $this->assertSame('5. Практика', $practice['title'] ?? null);
        $this->assertSame(['a', 'b'], $practice['options'] ?? null);
        $this->assertCount(2, $practice['selects'] ?? []);
        $this->assertCount(2, $practice['inputs'] ?? []);

        foreach ($practice['selects'] as $select) {
            $this->assertContains($select['answer'] ?? null, $practice['options']);
            $this->assertMatchesRegularExpression('/a\).+\/ b\).+/u', (string) ($select['label'] ?? ''));
        }

        foreach ($practice['inputs'] as $input) {
            $accepted = $input['accepted'] ?? $input['answers'] ?? $input['answer'] ?? [];
            $accepted = is_array($accepted) ? $accepted : [$accepted];
            $this->assertNotEmpty(array_filter(
                $accepted,
                static fn ($answer): bool => trim((string) $answer) !== ''
            ));
        }

        $this->assertCurrentLesson($this->decodeBody($blocks[6]), $currentLesson);

        foreach (['en', 'pl'] as $locale) {
            $localization = $this->readJson(
                self::DIRECTORY.'/'.$directory.'/localizations/'.$locale.'.json'
            );
            $localizedPracticeBlock = $this->blockAtIndex($localization['blocks'] ?? [], 6);
            $localizedNavigationBlock = $this->blockAtIndex($localization['blocks'] ?? [], 7);

            $this->assertNotNull($localizedPracticeBlock, strtoupper($locale).' practice is missing.');
            $this->assertNotNull($localizedNavigationBlock, strtoupper($locale).' navigation is missing.');

            $localizedPractice = $this->decodeBody($localizedPracticeBlock);
            $this->assertCount(2, $localizedPractice['selects'] ?? []);
            $this->assertCount(2, $localizedPractice['inputs'] ?? []);
            $this->assertSame(
                array_column($practice['selects'], 'answer'),
                array_column($localizedPractice['selects'], 'answer')
            );
            $this->assertSame(
                array_column($practice['inputs'], 'answer'),
                array_column($localizedPractice['inputs'], 'answer')
            );

            $this->assertCurrentLesson($this->decodeBody($localizedNavigationBlock), $currentLesson);
        }
    }

    public function test_standalone_examples_have_clear_past_anchors_and_natural_explanations(): void
    {
        $directories = [
            'PastPerfectFormsTheorySeeder',
            'PastPerfectNegativesTheorySeeder',
            'PastPerfectQuestionsTheorySeeder',
            'PastPerfectTimeExpressionsTheorySeeder',
        ];
        $documents = [];

        foreach ($directories as $directory) {
            $documents[] = $this->readJson(self::DIRECTORY.'/'.$directory.'/definition.json');

            foreach (['en', 'pl'] as $locale) {
                $documents[] = $this->readJson(
                    self::DIRECTORY.'/'.$directory.'/localizations/'.$locale.'.json'
                );
            }
        }

        $corpus = (string) json_encode(
            $documents,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        foreach ([
            'By noon, we had completed the task.',
            'They had already left by noon.',
            "He hadn't finished the report by Friday.",
            'Had she finished the report by Friday?',
            'I had not finished the task before lunch.',
            "Had she called before lunch? No, she hadn't.",
        ] as $ambiguousExample) {
            $this->assertStringNotContainsString($ambiguousExample, $corpus);
        }

        foreach ([
            'By noon that day, we had completed the task.',
            'They had already left by noon that day.',
            "He hadn't finished the report by that Friday.",
            'Had she finished the report by that Friday?',
            'I had not finished the task before lunch began.',
            "Had she called before lunch began? No, she hadn't.",
        ] as $anchoredExample) {
            $this->assertStringContainsString($anchoredExample, $corpus);
        }

        foreach ([
            'Одна дія сталася раніше або вже була готова',
            'дія була раніше за іншу минулу дію або вже була готова',
            'Jedna czynność wydarzyła się wcześniej albo była już gotowa',
            'jak gotowa albo jak niedawna była wcześniejsza czynność',
            'Czynność wciąż nie była wtedy zrobiona',
            'Czytanie jeszcze się wtedy nie wydarzyło',
            'rezultat był już gotowy',
            'результат уже був готовий',
        ] as $awkwardExplanation) {
            $this->assertStringNotContainsString($awkwardExplanation, $corpus);
        }

        $timeDirectory = self::DIRECTORY.'/PastPerfectTimeExpressionsTheorySeeder';
        $ukTime = $this->readJson($timeDirectory.'/definition.json');
        $ukComparison = $this->decodeBody($ukTime['page']['blocks'][3]);
        $this->assertSame('потяг поїхав до нашого прибуття', $ukComparison['rows'][2]['ua'] ?? null);

        foreach ([
            'en' => 'the train left before we arrived',
            'pl' => 'pociąg odjechał przed naszym przyjazdem',
        ] as $locale => $explanation) {
            $localization = $this->readJson($timeDirectory.'/localizations/'.$locale.'.json');
            $comparison = $this->decodeBody(
                $this->blockAtIndex($localization['blocks'] ?? [], 4) ?? []
            );
            $this->assertSame($explanation, $comparison['rows'][2]['ua'] ?? null);
        }
    }

    public function test_questions_practice_uses_the_same_explicit_past_reference_in_every_locale(): void
    {
        $directory = self::DIRECTORY.'/PastPerfectQuestionsTheorySeeder';
        $definition = $this->readJson($directory.'/definition.json');
        $practices = [$this->decodeBody($definition['page']['blocks'][5])];

        foreach (['en', 'pl'] as $locale) {
            $localization = $this->readJson($directory.'/localizations/'.$locale.'.json');
            $practices[] = $this->decodeBody(
                $this->blockAtIndex($localization['blocks'] ?? [], 6) ?? []
            );
        }

        foreach ($practices as $practice) {
            $this->assertSame(
                'a) Why had they went home before the concert ended? / b) Why had they gone home before the concert ended?',
                $practice['selects'][1]['label'] ?? null
            );

            $question = $practice['inputs'][1] ?? [];
            $this->assertSame(
                'home / the concert ended / why / gone / before / they / had',
                $question['before'] ?? null
            );
            $this->assertSame(
                'Why had they gone home before the concert ended?',
                $question['answer'] ?? null
            );
        }
    }

    public function test_word_order_fragments_are_scrambled_on_every_page_and_locale(): void
    {
        $expected = [
            'PastPerfectFormsTheorySeeder' => [
                'started / we arrived / the film / before / had',
                'the report / began / Maya / Friday’s meeting / had / before / completed',
            ],
            'PastPerfectNegativesTheorySeeder' => [
                "the door / before he left / hadn't / he / locked",
                'found / a solution / the team / by midnight that night / had not',
            ],
            'PastPerfectQuestionsTheorySeeder' => [
                'finished / by noon that day / you / had',
                'home / the concert ended / why / gone / before / they / had',
            ],
            'PastPerfectTimeExpressionsTheorySeeder' => [
                'already / before noon that day / left / they / had',
                'closed / the shop / we arrived / had / by the time',
            ],
        ];

        foreach ($expected as $directory => $scrambledInputs) {
            $definition = $this->readJson(self::DIRECTORY.'/'.$directory.'/definition.json');
            $practices = [$this->decodeBody($definition['page']['blocks'][5])];

            foreach (['en', 'pl'] as $locale) {
                $localization = $this->readJson(
                    self::DIRECTORY.'/'.$directory.'/localizations/'.$locale.'.json'
                );
                $practices[] = $this->decodeBody(
                    $this->blockAtIndex($localization['blocks'] ?? [], 6) ?? []
                );
            }

            foreach ($practices as $practice) {
                $this->assertSame($scrambledInputs, array_column($practice['inputs'] ?? [], 'before'));
            }
        }
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

    private function assertCurrentLesson(array $navigation, int $currentLesson): void
    {
        $items = $navigation['items'] ?? [];
        $current = array_keys(array_filter(
            $items,
            static fn (array $item): bool => (bool) ($item['current'] ?? false)
        ));

        $this->assertSame([$currentLesson], $current);
        foreach ($items as $item) {
            $this->assertArrayHasKey('url', $item);
            $this->assertStringNotContainsString('?', (string) $item['url']);
        }
    }
}
