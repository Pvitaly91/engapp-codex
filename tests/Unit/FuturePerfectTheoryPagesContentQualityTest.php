<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FuturePerfectTheoryPagesContentQualityTest extends TestCase
{
    private const DIRECTORY = 'database/seeders/Page_V3/FutureForms/FuturePerfect';

    /**
     * @return array<string, array{0: string, 1: string, 2: int, 3: int, 4: int, 5: string, 6: int}>
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
            ],
            'negatives' => [
                'FuturePerfectNegativesTheorySeeder',
                'future-perfect-negatives-practice',
                6,
                7,
                8,
                '6. Практика',
                1,
            ],
            'questions' => [
                'FuturePerfectQuestionsTheorySeeder',
                'future-perfect-questions-practice',
                6,
                7,
                8,
                '6. Практика',
                2,
            ],
            'time expressions' => [
                'FuturePerfectTimeExpressionsTheorySeeder',
                'future-perfect-time-expressions-practice',
                5,
                6,
                7,
                '5. Практика',
                3,
            ],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_every_lesson_has_localized_checkable_practice(
        string $directory,
        string $practiceUuid,
        int $practicePosition,
        int $localizedPracticeIndex,
        int $localizedNavigationIndex,
        string $ukrainianTitle,
        int $currentLesson
    ): void {
        $definition = $this->readJson(self::DIRECTORY.'/'.$directory.'/definition.json');
        $blocks = $definition['page']['blocks'] ?? [];

        $this->assertCount($practicePosition + 2, $blocks);
        $this->assertSame('summary-list', $blocks[$practicePosition - 1]['type'] ?? null);
        $this->assertSame('practice-set', $blocks[$practicePosition]['type'] ?? null);
        $this->assertSame('navigation-chips', $blocks[$practicePosition + 1]['type'] ?? null);
        $this->assertSame($practiceUuid, $blocks[$practicePosition]['uuid_key'] ?? null);

        $practice = $this->decodeBody($blocks[$practicePosition]);
        $this->assertSame($ukrainianTitle, $practice['title'] ?? null);
        $this->assertSame('Вправа 3. Поліглот', $practice['rephrase_title'] ?? null);
        $this->assertPracticeIsCheckableAndScrambled($practice);
        $this->assertCurrentLesson(
            $this->decodeBody($blocks[$practicePosition + 1]),
            $currentLesson
        );

        foreach (['en', 'pl'] as $locale) {
            $localization = $this->readJson(
                self::DIRECTORY.'/'.$directory.'/localizations/'.$locale.'.json'
            );
            $localizedPracticeBlock = $this->blockAtIndex(
                $localization['blocks'] ?? [],
                $localizedPracticeIndex
            );
            $localizedNavigationBlock = $this->blockAtIndex(
                $localization['blocks'] ?? [],
                $localizedNavigationIndex
            );

            $this->assertNotNull(
                $localizedPracticeBlock,
                strtoupper($locale).' practice is missing for '.$directory.'.'
            );
            $this->assertNotNull(
                $localizedNavigationBlock,
                strtoupper($locale).' navigation is missing for '.$directory.'.'
            );

            $localizedPractice = $this->decodeBody($localizedPracticeBlock);
            $this->assertPracticeIsCheckableAndScrambled($localizedPractice);
            $this->assertSame(
                array_column($practice['selects'], 'answer'),
                array_column($localizedPractice['selects'], 'answer')
            );
            $this->assertSame(
                array_column($practice['inputs'], 'before'),
                array_column($localizedPractice['inputs'], 'before')
            );
            $this->assertSame(
                array_column($practice['inputs'], 'answer'),
                array_column($localizedPractice['inputs'], 'answer')
            );
            $this->assertSame(
                array_column($practice['rephrase'], 'answer'),
                array_column($localizedPractice['rephrase'], 'answer')
            );

            $this->assertCurrentLesson(
                $this->decodeBody($localizedNavigationBlock),
                $currentLesson
            );
        }
    }

    private function assertPracticeIsCheckableAndScrambled(array $practice): void
    {
        $this->assertNotSame('', trim((string) ($practice['title'] ?? '')));
        $this->assertSame(['a', 'b'], $practice['options'] ?? null);
        $this->assertCount(2, $practice['selects'] ?? []);
        $this->assertCount(2, $practice['inputs'] ?? []);
        $this->assertNotSame('', trim((string) ($practice['rephrase_intro'] ?? '')));
        $this->assertCount(3, $practice['rephrase'] ?? []);

        foreach ($practice['selects'] as $select) {
            $this->assertContains($select['answer'] ?? null, $practice['options']);
            $this->assertMatchesRegularExpression(
                '/^a\).+\s\/\sb\).+/u',
                (string) ($select['label'] ?? '')
            );
        }

        foreach ($practice['inputs'] as $input) {
            $before = trim((string) ($input['before'] ?? ''));
            $answer = trim((string) ($input['answer'] ?? ''));
            $fragments = preg_split('/\s*\/\s*/u', $before) ?: [];

            $this->assertNotSame('', $answer);
            $this->assertGreaterThanOrEqual(3, count($fragments));

            foreach ($fragments as $fragment) {
                $words = preg_split('/\s+/u', trim($fragment)) ?: [];
                $this->assertGreaterThanOrEqual(1, count($words));
                $this->assertLessThanOrEqual(
                    3,
                    count($words),
                    'Each clickable token must contain between one and three words.'
                );
            }

            $this->assertNotSame(
                $this->normalizeSentence(implode(' ', $fragments)),
                $this->normalizeSentence($answer),
                'The token bank must not start in the correct answer order.'
            );
        }

        $example = $practice['rephrase'][0] ?? [];
        $this->assertNotSame('', trim((string) ($example['example_original'] ?? '')));
        $this->assertNotSame('', trim((string) ($example['example_target'] ?? '')));

        foreach (array_slice($practice['rephrase'], 1) as $item) {
            $this->assertNotSame('', trim((string) ($item['original'] ?? '')));
            $this->assertNotSame('', trim((string) ($item['answer'] ?? '')));
        }
    }

    private function normalizeSentence(string $sentence): string
    {
        return preg_replace(
            '/[^\p{L}\p{N}\']+/u',
            ' ',
            mb_strtolower(trim($sentence))
        ) ?? '';
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
    }
}
