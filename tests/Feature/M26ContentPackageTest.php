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

    public function test_exact_author_extensions_full_basic_dom_and_visible_practice(): void
    {
        [$m, $manifest] = Package::load(); $details = 0; $tasks = 0;
        foreach ($m['targets'] as $t) {
            $before = $manifest['definitions'][$t['identity']];
            $after = json_decode(file_get_contents(base_path($t['definition_path'])), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame(\App\Support\M26InteractivePractice::definition($t, $before), $after);
            $root = $t['source_content_root'];
            foreach ($before[$root]['blocks'] as $i => $config) {
                if (!TheoryPresentation::nativeView($config['type'])) { continue; }
                $b = $this->block($t, $config, $i);
                $basic = view('theory.partials.content-block', ['block' => $b])->render();
                $newBlock = $this->block($t, $after[$root]['blocks'][$i], $i);
                $html = view('theory.partials.content-block', ['block' => $newBlock])->render();
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
                $nodes = $new->query('//*[@data-theory-native-extension]/details');
                self::assertSame($ext ? 1 : 0, $nodes->length);
                if ($ext) {
                    self::assertSame(1, $new->query('//*[@data-theory-native-extension]/ancestor::*[contains(concat(" ",normalize-space(@class)," ")," theory-section-card ")]')->length);
                    $details++; self::assertFalse($nodes->item(0)->hasAttribute('open'));
                    self::assertSame(0, $new->query('//*[@data-theory-native-extension]//h2')->length);
                    $detail = $this->dom(view(TheoryPresentation::nativeView($config['type']), [
                        'block' => $newBlock, 'data' => $ext['detail_native_data'], 'embeddedDetail' => true])->render());
                    $actual = $new->query('//*[@data-theory-native-extension]//section')->item(0);
                    self::assertSame(trim($detail->query('//section')->item(0)->textContent), trim($actual->textContent));
                    $ids = [];
                    foreach ($new->query('//*[@id]') as $node) { self::assertNotContains($node->getAttribute('id'), $ids); $ids[] = $node->getAttribute('id'); }
                    self::assertStringNotContainsString('<template', $actual->ownerDocument->saveHTML($actual));
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
        self::assertSame(14, $details); self::assertSame(24, $tasks);
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
