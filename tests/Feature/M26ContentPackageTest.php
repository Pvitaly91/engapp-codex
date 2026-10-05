<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage as Package;
use App\Support\TheoryPresentation;
use DOMDocument;
use DOMXPath;
use Tests\TestCase;

class M26ContentPackageTest extends TestCase
{
    protected function setUp(): void { parent::setUp(); app()->setLocale('uk'); $this->withoutVite(); }

    private function dom(string $html): DOMXPath
    {
        $d = new DOMDocument; $p = libxml_use_internal_errors(true);
        try { $d->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($p); }
        return new DOMXPath($d);
    }

    private function block(array $target, array $config, int $i): object
    {
        $block = new \App\Models\TextBlock;
        $block->forceFill(['id' => $i + 40, 'uuid' => Package::uuid($target['identity'], $config, $i + 1),
            'type' => $config['type'], 'body' => $config['body'], 'level' => $config['level'] ?? null,
            'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $i + 1]);
        // Real working blocks have tags; their unchanged UI contains valid
        // Alpine @click attributes rejected by libxml's legacy HTML4 parser.
        $tag = new \App\Models\Tag; $tag->forceFill(['id'=>7, 'name'=>'Past Perfect Continuous']);
        $block->setRelation('tags', collect([$tag]));
        return $block;
    }

    private function renderStandaloneBlock(object $block): string
    {
        // This is page-global bootstrap, not teaching markup belonging to one
        // native card. An extended card pre-renders its basic view and then
        // includes the native view again; @once is consumed by that pre-render.
        // Establish the identical page-level setup before both snapshots so
        // the exact comparison below still checks every basic node/attribute.
        $html = \Illuminate\Support\Facades\Blade::render(
            '@include("components.english-answer-variants") @include("theory.partials.content-block")',
            ['block' => $block, 'practiceQuestions' => collect()],
        );
        $dom = $this->dom($html);
        self::assertSame(1, $dom->query('//script[starts-with(text(),"window.GRAMLYZE_CONTRACTION_RULES")]')->length);
        self::assertSame(1, $dom->query('//script[contains(@src,"/js/english-answer-variants.js")]')->length);
        self::assertSame(0, $dom->query('//section[starts-with(@id,"block-")]//script')->length);
        return $html;
    }

    public function test_exact_author_extensions_full_basic_dom_and_visible_practice(): void
    {
        [$m, $manifest] = Package::load(); $details = 0; $tasks = 0; $extendedBlocks = 0;
        foreach ($m['targets'] as $t) {
            $before = $manifest['definitions'][$t['identity']];
            $after = json_decode(file_get_contents(base_path($t['definition_path'])), true, flags: JSON_THROW_ON_ERROR);
            // The finite practice-only v2 supersedes the stored v1 practice body;
            // immutable M26 author/detail transfer checks below remain unchanged.
            self::assertSame(\App\Support\PastPerfectContinuousPracticeQuality::definition($t, $before), $after);
            $root = $t['source_content_root'];
            foreach ($before[$root]['blocks'] as $i => $config) {
                if (!TheoryPresentation::nativeView($config['type'])) { continue; }
                $b = $this->block($t, $config, $i);
                $basic = $this->renderStandaloneBlock($b);
                $newBlock = $this->block($t, $after[$root]['blocks'][$i], $i);
                $html = $this->renderStandaloneBlock($newBlock);
                $old = $this->dom($basic); $new = $this->dom($html);
                $oldNode = $old->query('//section[@id="block-'.$b->id.'"]')->item(0);
                $newNode = $new->query('//section[@id="block-'.$b->id.'"]')->item(0);
                // The new code-owned disclosure lives inside this same card.
                // Remove only that opt-in UI from a clone, preserving all basic
                // content/attributes/anchors and normalising whitespace only.
                $comparison = $newNode->cloneNode(true);
                foreach (iterator_to_array($comparison->getElementsByTagName('*')) as $n) {
                    if ($n->hasAttribute('data-theory-native-extension')) { $n->parentNode->removeChild($n); }
                }
                $normalise = fn($n) => preg_replace('/\s+/u', ' ', $n->ownerDocument->saveHTML($n));
                self::assertSame($normalise($oldNode), $normalise($comparison));
                $ext = Package::detailFor($newBlock, json_decode($newBlock->body, true));
                $points = \App\Support\M26PointDetails::fragmentsFor($newBlock, json_decode($newBlock->body, true));
                $nodes = $new->query('//*[@data-theory-native-extension]/details');
                self::assertSame($ext ? count($points) : 0, $nodes->length);
                if ($ext) {
                    self::assertSame(1, $new->query('//*[@data-theory-native-extension]/ancestor::*[contains(concat(" ",normalize-space(@class)," ")," theory-section-card ")]')->length);
                    $extendedBlocks++; $details += $nodes->length;
                    self::assertSame(0, $new->query('//*[@data-theory-native-extension]/ancestor::a')->length);
                    self::assertSame(0, $new->query('//*[@data-theory-native-extension]//h2')->length);
                    foreach ($nodes as $index => $node) {
                        self::assertFalse($node->hasAttribute('open'));
                        self::assertSame((string) $index, $node->parentNode->getAttribute('data-theory-point-index'));
                        self::assertSame($points[$index]['key'], $node->parentNode->getAttribute('data-theory-section'));
                        $actualFragments = $new->query('//*[@data-theory-point-index="'.$index.'"]//section');
                        self::assertSame(count($points[$index]['fragments']), $actualFragments->length);
                        foreach ($points[$index]['fragments'] as $j => $fragment) {
                            $expected = $this->dom(view('theory.partials.point-detail-fragment', ['fragment' => $fragment])->render());
                            $actual = $actualFragments->item($j);
                            self::assertSame($normalise($expected->query('//section')->item(0)), $normalise($actual));
                            self::assertStringNotContainsString('<template', $actual->ownerDocument->saveHTML($actual));
                        }
                    }
                    $ids = [];
                    foreach ($new->query('//*[@id]') as $node) { self::assertNotContains($node->getAttribute('id'), $ids); $ids[] = $node->getAttribute('id'); }
                }
            }
            if (isset($t['practice_insert'])) {
                // Historical approved master transfer is still immutable; the
                // current interactive replacement has separate schema tests.
                $legacy = Package::definition($t, $before);
                $p = end($legacy[$root]['blocks']); $d = $this->dom($p['body']);
                self::assertSame(6, $d->query('//ol[@class="practice-list"]/li')->length);
                self::assertSame(6, $d->query('//li/details[@class="answer"]')->length);
                self::assertSame(0, $d->query('//ol/ancestor::details')->length);
                // Each entire original prompt + explained key remains byte-identical.
                foreach ($d->query('//ol/li') as $li) {
                    $id = $li->getAttribute('id');
                    $masterDom = $this->dom($t['practice_insert']['body_html']);
                    $original = $masterDom->query('//*[@id="'.$id.'"]')->item(0);
                    self::assertSame($original->ownerDocument->saveHTML($original), $li->ownerDocument->saveHTML($li));
                    $tasks++;
                }
            }
        }
        self::assertSame(14, $extendedBlocks); self::assertSame(56, $details); self::assertSame(24, $tasks);
    }

    public function test_foreign_owner_locale_order_and_any_detail_change_fail_closed(): void
    {
        [$m, $manifest] = Package::load();
        foreach ($m['targets'] as $t) {
            foreach ($t['blocks'] as $item) {
                if (!isset($item['progressive_v1'])) { continue; }
                $i = $item['source_index']; $root = $t['source_content_root'];
                $source = Package::definition($t, $manifest['definitions'][$t['identity']]);
                $b = $this->block($t, $source[$root]['blocks'][$i], $i); $data = json_decode($b->body, true);
                self::assertNotNull(Package::detailFor($b, $data));
                foreach (['locale' => 'en', 'seeder' => 'foreign', 'uuid' => 'foreign', 'type' => 'box', 'sort_order' => 99] as $key => $bad) {
                    $foreign = clone $b; $foreign->$key = $bad; self::assertNull(Package::detailFor($foreign, $data));
                }
                $wrong = $data; $wrong['progressive_v2']['detail_native_sha256'] = str_repeat('0', 64);
                self::assertNull(Package::detailFor($b, $wrong));
                $wrong = $data; $wrong['progressive_v2']['detail_native_data'] = array_reverse($item['native_data'], true);
                self::assertNull(Package::detailFor($b, $wrong), 'Reordered author keys rejected');
                foreach (['items', 'sections', 'rows'] as $field) {
                    if (!isset($item['native_data'][$field])) { continue; }
                    $wrong = $data; array_pop($wrong['progressive_v2']['detail_native_data'][$field]);
                    self::assertNull(Package::detailFor($b, $wrong), 'Missing detail rejected');
                    $wrong = $data; $wrong['progressive_v2']['detail_native_data'][$field] = array_reverse($item['native_data'][$field]);
                    self::assertNull(Package::detailFor($b, $wrong), 'Reordered rows rejected');
                }
                $bytes = Package::json($item['native_data']);
                foreach (['not', 'been', 'long', 'well', 'for', 'since', 'before', 'after', '9:00', '11:00', 'twenty', 'ua'] as $word) {
                    if (!str_contains($bytes, $word)) { continue; }
                    $wrong = $data;
                    $wrong['progressive_v2']['detail_native_data'] = json_decode(str_replace($word, '', $bytes), true, flags: JSON_THROW_ON_ERROR);
                    self::assertNull(Package::detailFor($b, $wrong), 'Fidelity mutation '.$word);
                }
                $malformed = clone $b; $malformed->body = '{broken';
                $html = view('theory.partials.content-block', ['block' => $malformed])->render();
                self::assertStringNotContainsString('data-theory-native-extension', $html);
                self::assertStringContainsString('invalid-native-data', $html);
            }
        }
    }
}
