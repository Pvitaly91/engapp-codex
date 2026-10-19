<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M29SentenceStructurePackage as Package;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M29DetailQualityFollowupTest extends TestCase
{
    use RebuildsComposeTestSchema;

    /** Explicit reviewed author owners; no length-based selection. */
    private const MERGED = [
        'm29-cleft-section-2' => [1, 2, 3],
        'm29-cleft-section-3' => [1, 2, 3],
        'm29-cleft-section-4' => [1, 2, 3],
        'm29-cleft-section-5' => [1, 2, 3],
        'm29-noun-section-3' => [1, 2, 3],
        'm29-noun-section-4' => [1, 2, 3],
        'm29-noun-section-5' => [1],
        'm29-noun-section-6' => [1, 2, 4, 5],
        'm29-noun-section-7' => [1, 2],
        'm29-ellipsis-section-1' => [1],
        'm29-ellipsis-section-2' => [1, 2, 3, 4],
        'm29-ellipsis-section-3' => [4, 5, 6],
        'm29-ellipsis-section-4' => [1, 2, 4],
        'm29-ellipsis-section-6' => [1, 2, 3],
        'm29-ellipsis-section-7' => [5],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        app()->setLocale('uk');
        $this->withoutVite();
    }

    public function test_exact_finite_merges_keep_author_text_visible_without_details(): void
    {
        [$before, $current] = Package::load();
        Package::validate($before, $current);
        $frozen = json_decode(file_get_contents(base_path('database/content-patches/m29-m13-sentence-structure.v1.json')), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('e28b75ee73f02fb7ed12bff25ef35423ee724a384927156eac877a3f79429d06', hash_file('sha256', base_path('database/content-patches/m29-m13-sentence-structure.v1.json')));
        self::assertSame(2, $current['version']);
        $visited = [];
        foreach ($current['targets'] as $i => $target) {
            self::assertSame($frozen['targets'][$i]['after'], $target['after'], 'No educational, DB, practice, metadata or bank payload change.');
            foreach ($target['plans'] as $j => $plan) {
                $source = $target['after']['page']['blocks'][$j + 1];
                $block = new TextBlock;
                $block->forceFill(['id' => 100 + $j, 'uuid' => M26DetailPackage::uuid($target['identity'], $source, $j + 2),
                    'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $j + 2, 'type' => $source['type'], 'body' => $source['body']]);
                $block->setRelation('tags', collect());
                $block->setRelation('page', null);
                $projection = Package::presentation($block, json_decode($source['body'], true));
                self::assertNotNull($projection);
                self::assertSame([], $projection['points']);
                $html = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
                self::assertStringNotContainsString('data-theory-native-extension', $html);
                foreach ($plan['points'] as $k => $point) {
                    $old = $frozen['targets'][$i]['plans'][$j]['points'][$k];
                    $merge = in_array($k + 1, self::MERGED[$plan['key']] ?? [], true);
                    if ($merge) {
                        self::assertNotSame('', $old['detail']);
                        self::assertSame($old['basic'].'<br><br>'.$old['detail'], $point['basic']);
                        self::assertSame('', $point['detail']);
                        $visited[] = $plan['key'].'/'.($k + 1);
                    } else {
                        self::assertSame('', $old['detail'], 'Every previously hidden point requires an explicit semantic decision.');
                        self::assertSame($old, $point);
                    }
                    self::assertSame($point['basic'], $projection['data']['sections'][$k]['description']);
                    self::assertStringContainsString($point['basic'], $html);
                    self::assertStringNotContainsString('id="block-'.$plan['key'].'-point-'.($k + 1).'-detail"', $html);
                }
            }
        }
        $expected = [];
        foreach (self::MERGED as $section => $points) {
            foreach ($points as $point) { $expected[] = $section.'/'.$point; }
        }
        sort($visited); sort($expected);
        self::assertSame($expected, $visited, 'No stale, unreviewed or unmapped author point.');
    }

    public function test_reintroducing_a_short_historical_detail_fails_closed(): void
    {
        [$before, $current] = Package::load();
        $frozen = json_decode(file_get_contents(base_path('database/content-patches/m29-m13-sentence-structure.v1.json')), true, flags: JSON_THROW_ON_ERROR);
        $current['targets'][0]['plans'][1]['points'][0] = $frozen['targets'][0]['plans'][1]['points'][0];
        $this->expectException(RuntimeException::class);
        Package::validate($before, $current);
    }
}
