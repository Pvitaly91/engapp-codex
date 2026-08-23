<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FuturePerfectTheoryPlainTextContentTest extends TestCase
{
    #[DataProvider('formsGridProvider')]
    public function test_forms_grid_subtitles_do_not_contain_raw_html(string $relativePath, ?int $index): void
    {
        $definition = json_decode(
            (string) file_get_contents($this->rootPath().'/'.$relativePath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if ($index === null) {
            $block = collect($definition['page']['blocks'] ?? [])->firstWhere('type', 'forms-grid');
        } else {
            $block = collect($definition['blocks'] ?? [])->firstWhere('index', $index);
        }

        $this->assertIsArray($block, $relativePath.': forms-grid block is missing');
        $body = json_decode((string) ($block['body'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
        $this->assertNotEmpty($body['items'] ?? [], $relativePath.': forms-grid items are missing');

        foreach ($body['items'] as $item) {
            $subtitle = (string) ($item['subtitle'] ?? '');
            $this->assertSame(strip_tags($subtitle), $subtitle, $relativePath.': subtitle contains raw HTML');
        }
    }

    public static function formsGridProvider(): array
    {
        $directory = 'database/seeders/Page_V3/FutureForms/FuturePerfect/FuturePerfectFormsTheorySeeder';

        return [
            'uk' => [$directory.'/definition.json', null],
            'en' => [$directory.'/localizations/en.json', 2],
            'pl' => [$directory.'/localizations/pl.json', 2],
        ];
    }

    private function rootPath(): string
    {
        return dirname(__DIR__, 2);
    }
}
