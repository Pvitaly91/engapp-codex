<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M44AuthoredFutureFormsPackage as Package;
use App\Support\M44SimplifiedPresentation;
use App\Support\TheoryAuthoredAdapter;
use App\Support\TheorySection;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\HtmlString;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class TheoryCompactDisclosureAnchorsTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        app()->setLocale('uk');
        $this->withoutVite();
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

    public function test_all_m44_compact_groups_keep_original_anchors_and_valid_closed_disclosures(): void
    {
        [, $package] = Package::load();
        $sourceSnapshot = serialize($package);
        $sectionCount = 0;
        $groupCount = 0;
        $expectedRegressions = ['m44-cont-arrangement-more', 'm44-cont-going-to-more'];
        foreach ($package['targets'] as $target) {
            foreach ($target['after']['page']['blocks'] as $slot => $config) {
                $data = json_decode($config['body'], true);
                if (!isset($data['author_section'])) { continue; }
                $sectionCount++;
                $block = new TextBlock;
                $block->forceFill(['id' => 94400 + $slot,
                    'uuid' => M26DetailPackage::uuid($target['identity'], $config, $slot + 1),
                    'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $slot + 1,
                    'type' => $config['type'], 'body' => $config['body'], 'level' => $config['level'] ?? null,
                    'heading' => $config['heading'] ?? null, 'column' => $config['column'], 'css_class' => $config['css_class'] ?? null]);
                $block->setRelation('tags', collect());
                $block->setRelation('page', null);

                $presentation = Package::presentation($block, $data);
                self::assertNotNull($presentation);
                $pointSections = [];
                foreach ($presentation['points'] as $index => $point) {
                    $detail = '';
                    foreach ($point['fragments'] as $fragment) {
                        $detail .= view('theory.partials.point-detail-fragment', ['fragment' => $fragment, 'theoryCanonical' => true])->render();
                    }
                    $basic = '<p>Basic reference.</p>';
                    $full = $basic.$detail;
                    $pointSections[$index] = TheorySection::resolve($point['key'], $point['title'], new HtmlString($full), null, [
                        'source_key' => $point['key'], 'source_revision' => hash('sha256', $full), 'main' => new HtmlString($basic),
                        'detail' => new HtmlString($detail), 'detail_references' => array_column($point['fragments'], 'id'),
                    ], nativeHtml5: true);
                    self::assertNull($pointSections[$index]->diagnosticCode);
                }
                $groups = M44SimplifiedPresentation::section($data['author_section']);
                self::assertNotNull($groups);
                $node = TheoryAuthoredAdapter::section($block, $data, $pointSections, $groups);
                foreach ($groups as $index => $group) {
                    if (!$group['disclosure']) { continue; }
                    $groupCount++;
                    $disclosure = $node['items'][$index]['detail'];
                    self::assertSame('disclosure', $disclosure['kind'], $group['id'].' must not fall back to expanded content');
                    self::assertNull($disclosure['section']->diagnosticCode, $group['id']);
                    self::assertSame($group['id'].'-more', $disclosure['toggle_id']);
                }

                // Compare actual dispatcher output, not only the normalized plan.
                $before = $this->xpath(view('theory.partials.content-block', ['block' => $block, 'm44StyleContext' => true])->render());
                $after = $this->xpath(view('theory.partials.content-block', ['block' => $block, 'm44StyleContext' => true, 'theoryCanonical' => true])->render());
                foreach ($before->query('//*[@id]') as $anchor) {
                    self::assertSame(1, $after->query('//*[@id="'.$anchor->getAttribute('id').'"]')->length, 'Preserve original anchor '.$anchor->getAttribute('id'));
                }
                foreach ($before->query('//details/summary[@id]') as $toggle) {
                    $id = $toggle->getAttribute('id');
                    self::assertSame(1, $after->query('//details[not(@open)]/summary[@id="'.$id.'"]')->length, 'Preserve closed public toggle '.$id);
                    $expectedRegressions = array_values(array_diff($expectedRegressions, [$id]));
                }
                $ids = array_map(static fn ($anchor) => $anchor->getAttribute('id'), iterator_to_array($after->query('//*[@id]')));
                self::assertSame(count($ids), count(array_unique($ids)), 'No duplicate control or content anchors');
                self::assertSame(0, $after->query('//details//details')->length, 'No nested group disclosures');
            }
        }
        self::assertSame(20, $sectionCount);
        self::assertGreaterThan(2, $groupCount);
        self::assertSame([], $expectedRegressions, 'Both live M44-PC regressions were exercised');
        self::assertSame($sourceSnapshot, serialize($package));
        self::assertSame(Package::SOURCE_SHA, hash_file('sha256', base_path(Package::SOURCE)));
    }
}
