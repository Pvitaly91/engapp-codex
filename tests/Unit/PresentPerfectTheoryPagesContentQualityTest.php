<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PresentPerfectTheoryPagesContentQualityTest extends TestCase
{
    private const DIRECTORY =
        'database/seeders/Page_V3/Tenses/PresentPerfect';

    private const LESSON_SLUGS = [
        'present-perfect-forms',
        'present-perfect-negatives',
        'present-perfect-questions',
        'present-perfect-time-expressions',
    ];

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

    /** @return array<string, array{0: string, 1: string, 2: int}> */
    public static function pageProvider(): array
    {
        return [
            'forms' => ['PresentPerfectFormsTheorySeeder', 'present-perfect-forms-practice', 0],
            'negatives' => ['PresentPerfectNegativesTheorySeeder', 'present-perfect-negatives-practice', 1],
            'questions' => ['PresentPerfectQuestionsTheorySeeder', 'present-perfect-questions-practice', 2],
            'time expressions' => ['PresentPerfectTimeExpressionsTheorySeeder', 'present-perfect-time-expressions-practice', 3],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_every_page_has_checkable_mini_practice_and_canonical_navigation(
        string $directory,
        string $practiceUuid,
        int $currentLesson
    ): void {
        $definitionPath = self::DIRECTORY.'/'.$directory.'/definition.json';
        $definition = $this->readJson($definitionPath);
        $blocks = $definition['page']['blocks'] ?? [];

        $this->assertCount(8, $blocks, $directory.' should have six content blocks, practice, and navigation.');
        $this->assertSame('summary-list', $blocks[5]['type'] ?? null);
        $this->assertSame('practice-set', $blocks[6]['type'] ?? null);
        $this->assertSame('navigation-chips', $blocks[7]['type'] ?? null);
        $this->assertSame($practiceUuid, $blocks[6]['uuid_key'] ?? null);

        $practice = $this->decodeBody($blocks[6]);
        $this->assertSame('6. Практика', $practice['title'] ?? null);
        $this->assertCount(2, $practice['selects'] ?? []);
        $this->assertCount(2, $practice['inputs'] ?? []);
        $this->assertSame(['a', 'b'], $practice['options'] ?? null);

        foreach ($practice['selects'] as $select) {
            $this->assertContains($select['answer'] ?? null, $practice['options']);
            $this->assertMatchesRegularExpression('/a\).+\/ b\).+/u', (string) ($select['label'] ?? ''));
        }

        foreach ($practice['inputs'] as $input) {
            $accepted = $input['accepted'] ?? $input['answers'] ?? $input['answer'] ?? [];
            $accepted = is_array($accepted) ? $accepted : [$accepted];
            $this->assertNotEmpty(array_filter($accepted, static fn ($answer): bool => trim((string) $answer) !== ''));
        }

        $navigation = $this->decodeBody($blocks[7]);
        $this->assertSame(
            array_map(
                static fn (string $slug): string => '/theory/tenses/present-perfect/'.$slug,
                self::LESSON_SLUGS
            ),
            array_column($navigation['items'] ?? [], 'url')
        );
        $this->assertSame(
            [$currentLesson],
            array_keys(array_filter(
                $navigation['items'] ?? [],
                static fn (array $item): bool => (bool) ($item['current'] ?? false)
            ))
        );

        foreach (['en', 'pl'] as $locale) {
            $localization = $this->readJson(
                self::DIRECTORY.'/'.$directory.'/localizations/'.$locale.'.json'
            );
            $localizedBlocks = collect($localization['blocks'] ?? []);
            $localizedPracticeBlock = $localizedBlocks->firstWhere('index', 7);
            $localizedNavigationBlock = $localizedBlocks->firstWhere('index', 8);

            $this->assertIsArray($localizedPracticeBlock, strtoupper($locale).' practice is missing.');
            $this->assertIsArray($localizedNavigationBlock, strtoupper($locale).' navigation is missing.');

            $localizedPractice = $this->decodeBody($localizedPracticeBlock);
            $this->assertCount(2, $localizedPractice['selects'] ?? []);
            $this->assertCount(2, $localizedPractice['inputs'] ?? []);
            $this->assertSame(
                array_column($practice['selects'], 'answer'),
                array_column($localizedPractice['selects'], 'answer')
            );

            $localizedNavigation = $this->decodeBody($localizedNavigationBlock);
            $this->assertSame(
                array_map(
                    static fn (string $slug): string => "/{$locale}/theory/tenses/present-perfect/{$slug}",
                    self::LESSON_SLUGS
                ),
                array_column($localizedNavigation['items'] ?? [], 'url')
            );
            $this->assertSame(
                [$currentLesson],
                array_keys(array_filter(
                    $localizedNavigation['items'] ?? [],
                    static fn (array $item): bool => (bool) ($item['current'] ?? false)
                ))
            );
        }
    }

    public function test_forms_explains_v3_finished_time_and_open_periods_without_the_typo(): void
    {
        $content = $this->baseContent('PresentPerfectFormsTheorySeeder');

        $this->assertStringNotContainsString('Минуло щось сталося', $content);
        $this->assertStringContainsString('V3 — третя форма', $content);
        $this->assertStringContainsString('since', $content);
        $this->assertStringContainsString('маркером завершеного періоду', $content);
        $this->assertStringContainsString('Anna has gone to the shop', $content);
        $this->assertStringContainsString('Anna has been to the shop', $content);
    }

    public function test_negatives_explains_never_yet_and_finished_past_time(): void
    {
        $content = $this->baseContent('PresentPerfectNegativesTheorySeeder');

        $this->assertStringContainsString("He hasn't never seen snow", $content);
        $this->assertStringContainsString('Never уже має заперечне значення', $content);
        $this->assertStringContainsString("haven't yet finished", $content);
        $this->assertStringContainsString('Past Simple', $content);
        $this->assertStringContainsString('британського варіанта англійської', $content);
    }

    public function test_questions_covers_wh_subject_duration_and_past_simple_questions(): void
    {
        $content = $this->baseContent('PresentPerfectQuestionsTheorySeeder');

        foreach ([
            'What have you done?',
            'Who has called?',
            'How long have you known Anna?',
            'How long have you been waiting?',
            'When did you send it?',
            "Yes, she's",
        ] as $example) {
            $this->assertStringContainsString($example, $content);
        }
    }

    private function baseContent(string $directory): string
    {
        $definition = $this->readJson(self::DIRECTORY.'/'.$directory.'/definition.json');

        return json_encode(
            $definition['page']['blocks'] ?? [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }
}
