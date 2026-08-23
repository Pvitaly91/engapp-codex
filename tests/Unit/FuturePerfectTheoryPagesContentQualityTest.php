<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FuturePerfectTheoryPagesContentQualityTest extends TestCase
{
    private const DIRECTORY = 'database/seeders/Page_V3/FutureForms/FuturePerfect';

    private const ROUTE_PREFIX = '/theory/maibutni-formy/future-perfect/';

    /**
     * @return array<string, array{0: string, 1: string, 2: int, 3: int, 4: int, 5: string, 6: int, 7: string, 8: class-string, 9: string}
     */
    public static function pageProvider(): array
    {
        return [
            'forms' => [
                'FuturePerfectFormsTheorySeeder',
                'future-perfect-forms-practice',
                6,
                7,
                8,
                '6. Практика',
                0,
                'future-perfect-forms',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectFormsAllLevelsLessonSeeder',
                'future-perfect-forms-theory-links.json',
            ],
            'negatives' => [
                'FuturePerfectNegativesTheorySeeder',
                'future-perfect-negatives-practice',
                6,
                7,
                8,
                '6. Практика',
                1,
                'future-perfect-negatives',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectNegativesAllLevelsLessonSeeder',
                'future-perfect-negatives-theory-links.json',
            ],
            'questions' => [
                'FuturePerfectQuestionsTheorySeeder',
                'future-perfect-questions-practice',
                6,
                7,
                8,
                '6. Практика',
                2,
                'future-perfect-questions',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder',
                'future-perfect-questions-theory-links.json',
            ],
            'time expressions' => [
                'FuturePerfectTimeExpressionsTheorySeeder',
                'future-perfect-time-expressions-practice',
                5,
                6,
                7,
                '5. Практика',
                3,
                'future-perfect-time-expressions',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder',
                'future-perfect-time-expressions-theory-links.json',
            ],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_every_lesson_uses_a_localized_topic_specific_sentence_builder(
        string $directory,
        string $practiceUuid,
        int $practicePosition,
        int $localizedPracticeIndex,
        int $localizedNavigationIndex,
        string $ukrainianTitle,
        int $currentLesson,
        string $slug,
        string $questionSeeder,
        string $manifestName,
    ): void {
        $definitionPath = self::DIRECTORY.'/'.$directory.'/definition.json';
        $definition = $this->readJson($definitionPath);
        $blocks = $definition['page']['blocks'] ?? [];

        $this->assertCount($practicePosition + 2, $blocks);
        $this->assertSame('summary-list', $blocks[$practicePosition - 1]['type'] ?? null);
        $this->assertSame('practice-set', $blocks[$practicePosition]['type'] ?? null);
        $this->assertSame('navigation-chips', $blocks[$practicePosition + 1]['type'] ?? null);
        $this->assertSame($practiceUuid, $blocks[$practicePosition]['uuid_key'] ?? null);

        $practice = $this->decodeBody($blocks[$practicePosition]);
        $this->assertSame($ukrainianTitle, $practice['title'] ?? null);
        $this->assertPracticeHasWordOrderExercise($practice);
        $this->assertPracticeUsesLinkedSentenceBuilder($practice, $questionSeeder, 'uk');
        $this->assertSame('Вправа 3. Побудуй речення', $practice['linked_practice']['title'] ?? null);
        $this->assertSame('Склади речення, клікаючи на слова.', $practice['linked_practice']['intro'] ?? null);
        $this->assertCurrentLessonAndCanonicalRoutes(
            $this->decodeBody($blocks[$practicePosition + 1]),
            $currentLesson,
            ''
        );
        $this->assertNoManualTranslationSource($definitionPath);

        foreach (['en', 'pl'] as $locale) {
            $localizationPath = self::DIRECTORY.'/'.$directory.'/localizations/'.$locale.'.json';
            $localization = $this->readJson($localizationPath);
            $localizedPracticeBlock = $this->blockAtIndex(
                $localization['blocks'] ?? [],
                $localizedPracticeIndex
            );
            $localizedNavigationBlock = $this->blockAtIndex(
                $localization['blocks'] ?? [],
                $localizedNavigationIndex
            );

            $this->assertNotNull($localizedPracticeBlock, strtoupper($locale).' practice is missing for '.$directory.'.');
            $this->assertNotNull($localizedNavigationBlock, strtoupper($locale).' navigation is missing for '.$directory.'.');

            $localizedPractice = $this->decodeBody($localizedPracticeBlock);
            $this->assertPracticeUsesLinkedSentenceBuilder($localizedPractice, $questionSeeder, $locale);
            $this->assertSame(
                array_column($practice['selects'], 'answer'),
                array_column($localizedPractice['selects'], 'answer')
            );
            $this->assertCurrentLessonAndCanonicalRoutes(
                $this->decodeBody($localizedNavigationBlock),
                $currentLesson,
                '/'.$locale
            );
            $this->assertNoManualTranslationSource($localizationPath);
        }

        $manifest = $this->readJson('database/seeders/V3/TheoryLinks/data/'.$manifestName);
        $this->assertSame(self::ROUTE_PREFIX.$slug, $manifest['page']['route'] ?? null);
        $directBuilder = collect($manifest['tests_on_page'] ?? [])->firstWhere('kind', 'direct_sentence_builder');
        $this->assertIsArray($directBuilder);
        $this->assertSame($questionSeeder, $directBuilder['seeder_class'] ?? null);
        $this->assertCount(72, $directBuilder['question_links'] ?? []);
    }

    private function assertPracticeUsesLinkedSentenceBuilder(
        array $practice,
        string $questionSeeder,
        string $locale,
    ): void {
        $this->assertNotSame('', trim((string) ($practice['title'] ?? '')));
        $this->assertSame(['a', 'b'], $practice['options'] ?? null);
        $this->assertCount(2, $practice['selects'] ?? []);

        foreach (['rephrase_title', 'rephrase_intro', 'rephrase'] as $removedKey) {
            $this->assertArrayNotHasKey($removedKey, $practice);
        }

        foreach ($practice['selects'] as $select) {
            $this->assertContains($select['answer'] ?? null, $practice['options']);
            $this->assertMatchesRegularExpression('/^a\).+\s\/\sb\).+/u', (string) ($select['label'] ?? ''));
        }

        $linked = $practice['linked_practice'] ?? null;
        $this->assertIsArray($linked);
        $this->assertSame('theory_links', $linked['source'] ?? null);
        $this->assertSame(['4'], $linked['question_types'] ?? null);
        $this->assertSame([$questionSeeder], $linked['seeder_classes'] ?? null);
        $this->assertNotSame('', trim((string) ($linked['title'] ?? '')), "$locale builder title");
        $this->assertNotSame('', trim((string) ($linked['intro'] ?? '')), "$locale builder intro");
        $this->assertNotSame('', trim((string) ($linked['footer'] ?? '')), "$locale builder footer");
        $this->assertStringNotContainsStringIgnoringCase('polyglot', implode(' ', [
            (string) $linked['title'],
            (string) $linked['intro'],
            (string) $linked['footer'],
        ]));
    }

    private function assertPracticeHasWordOrderExercise(array $practice): void
    {
        $this->assertStringStartsWith('Вправа 2. Побудуй', (string) ($practice['input_title'] ?? ''));
        $this->assertSame('Постав слова у правильному порядку.', $practice['input_intro'] ?? null);
        $this->assertCount(2, $practice['inputs'] ?? []);

        foreach ($practice['inputs'] as $input) {
            $this->assertStringContainsString(' / ', (string) ($input['before'] ?? ''));
            $this->assertSame('→', $input['after'] ?? null);
            $this->assertMatchesRegularExpression('/[.!?]$/', (string) ($input['answer'] ?? ''));
        }
    }

    private function assertNoManualTranslationSource(string $relativePath): void
    {
        $source = (string) file_get_contents($this->rootPath().'/'.$relativePath);

        foreach ([
            'rephrase_title',
            'rephrase_intro',
            'Напиши англійською',
            'Переклади речення',
            'Write in English',
            'Translate the sentence',
            'Napisz po angielsku',
            'Przetłumacz zdanie',
        ] as $removedText) {
            $this->assertStringNotContainsString($removedText, $source, $relativePath);
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

    private function assertCurrentLessonAndCanonicalRoutes(
        array $navigation,
        int $currentLesson,
        string $localePrefix,
    ): void {
        $items = $navigation['items'] ?? [];
        $current = array_keys(array_filter(
            $items,
            static fn (array $item): bool => (bool) ($item['current'] ?? false)
        ));

        $this->assertSame([$currentLesson], $current);
        $this->assertCount(4, $items);

        foreach ($items as $item) {
            $this->assertStringStartsWith($localePrefix.self::ROUTE_PREFIX, (string) ($item['url'] ?? ''));
        }
    }
}
