<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M41AuthoredTenseComparisonsPackage;
use App\Support\TheoryAuthoredAdapter;
use DOMDocument;
use DOMXPath;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class TheoryAuthoredAdapterFallbackTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        app()->setLocale('uk');
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return new DOMXPath($document);
    }

    public function test_missing_m41_mapping_retains_all_fourteen_idless_author_details(): void
    {
        [, $source] = M41AuthoredTenseComparisonsPackage::load();
        $snapshot = serialize($source);
        $ids = [];
        foreach ($source['targets'] as $target) {
            foreach ($target['after']['page']['blocks'] as $slot => $config) {
                $data = json_decode($config['body'], true, flags: JSON_THROW_ON_ERROR);
                $points = $data['author_section']['points'] ?? [];
                if (!array_filter($points, static fn ($point) => isset($point['detail']))) { continue; }
                self::assertArrayNotHasKey('m41_existing_design', $data, 'This is the real missing-mapping input, not a synthetic renamed detail.');
                $block = new TextBlock;
                $block->forceFill(['id' => 94100 + $slot, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, $slot + 1),
                    'type' => $config['type'], 'body' => $config['body'], 'level' => $config['level'] ?? null]);
                $block->setRelation('tags', collect());
                $block->setRelation('page', null);

                // Exercise the actual rejected-mapping Blade boundary. Warnings
                // remain test failures, including an undefined detail ID.
                $html = view('engram.theory.blocks-v3.m41-existing-design-section', [
                    'block' => $block, 'data' => $data, 'theoryCanonical' => true,
                    'practiceQuestions' => collect(),
                ])->render();
                $xpath = $this->xpath($html);
                self::assertSame(0, $xpath->query('//details[@data-theory-details]')->length, 'Rejected mapping cannot hide author text.');
                self::assertSame(0, $xpath->query('//*[@id="block-"]')->length);

                $guarded = TheoryAuthoredAdapter::section($block, $data, guarded: true);
                $fallback = TheoryAuthoredAdapter::section($block, $data, guarded: false);
                foreach ($points as $index => $point) {
                    if (!isset($point['detail'])) { continue; }
                    self::assertArrayNotHasKey('id', $point['detail']);
                    $id = 'block-'.$data['author_section']['id'].'-'.$point['id'].'-detail';
                    self::assertNotContains($id, $ids, 'Fallback anchors are distinct across all accepted M41 details.');
                    $ids[] = $id;
                    $detail = $xpath->query('//*[@id="'.$id.'"]');
                    self::assertSame(1, $detail->length, $id);
                    $text = $detail->item(0)->textContent;
                    self::assertStringContainsString($point['detail']['title'], $text);
                    foreach ($point['detail']['paragraphs_uk'] as $paragraph) { self::assertStringContainsString($paragraph, $text); }
                    foreach ($point['detail']['examples'] as $example) {
                        self::assertStringContainsString($example['en'], $text);
                        self::assertStringContainsString($example['uk'], $text);
                    }
                    // The new behavior only appends a complete unguarded tail;
                    // the canonical basic node remains byte-for-byte identical.
                    $fallbackPoint = $fallback['items'][$index];
                    $tail = array_pop($fallbackPoint['tail']);
                    self::assertSame($id, $tail['id']);
                    self::assertEquals($guarded['items'][$index], $fallbackPoint);
                }
            }
        }
        self::assertCount(14, $ids);
        self::assertSame($snapshot, serialize($source), 'The frozen projection is never mutated.');
        self::assertSame(M41AuthoredTenseComparisonsPackage::SOURCE_SHA, hash_file('sha256', base_path(M41AuthoredTenseComparisonsPackage::SOURCE)));
    }
}
