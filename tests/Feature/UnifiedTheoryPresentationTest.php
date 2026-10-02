<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Support\TheoryPresentation;
use DOMDocument;
use DOMXPath;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class UnifiedTheoryPresentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Tests\Support\IsolatedTestEnvironment::assertSafeDatabase(\Illuminate\Support\Facades\DB::connection());
        app()->setLocale('uk');
    }

    public function test_every_versioned_html_block_retains_ordered_text_links_tables_and_existing_ids(): void
    {
        $count = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(database_path('seeders/Page_V3'))) as $file) {
            if (!$file->isFile() || $file->getFilename() !== 'definition.json') {
                continue;
            }
            $definition = json_decode(file_get_contents($file->getPathname()), true, flags: JSON_THROW_ON_ERROR);
            foreach ($definition['page']['blocks'] ?? [] as $index => $source) {
                if (!in_array($source['type'] ?? '', ['', 'box'], true)) {
                    continue;
                }
                $block = (object) ['id' => 'fixture-'.$count, 'body' => $source['body'] ?? ''];
                $result = TheoryPresentation::html($block);
                $before = $this->dom($block->body);
                $after = $this->dom((string) $result['html']);
                $this->assertSame($this->spaces($before->getElementsByTagName('body')->item(0)->textContent), $this->spaces($after->getElementsByTagName('body')->item(0)->textContent), $file->getPathname().' '.$index);
                foreach (['a', 'th', 'td', 'summary', 'ol', 'li'] as $tag) {
                    $this->assertSame($before->getElementsByTagName($tag)->length, $after->getElementsByTagName($tag)->length, $tag);
                    foreach ($before->getElementsByTagName($tag) as $i => $node) {
                        $next = $after->getElementsByTagName($tag)->item($i);
                        $this->assertSame($this->spaces($node->textContent), $this->spaces($next->textContent), $tag);
                        foreach (['id', 'href', 'start', 'value', 'scope', 'open'] as $attribute) {
                            $this->assertSame($node->getAttribute($attribute), $next->getAttribute($attribute), $tag.' '.$attribute);
                        }
                    }
                }
                $xpath = new DOMXPath($before);
                foreach ($xpath->query('//*[@id]') as $node) {
                    $id = $node->getAttribute('id');
                    $matches = (new DOMXPath($after))->query('//*[@id="'.$id.'"]');
                    $this->assertSame(1, $matches->length, $id);
                }
                // M26's authored answer disclosures are source content, not generated
                // short/detail UI. The adapter must neither add nor remove them.
                $this->assertSame(
                    (new DOMXPath($before))->query('//*[@data-theory-details]')->length,
                    (new DOMXPath($after))->query('//*[@data-theory-details]')->length,
                );
                $count++;
            }
        }
        $this->assertGreaterThan(0, $count);
    }

    public function test_legacy_html_full_fallback_entities_and_anchors(): void
    {
        $html = '<h4 id="old">1. Tea &amp; coffee</h4><p>Do not remove 3 examples. — Не прибирай переклад.</p><h4>2. Forms B1/C2</h4><p><a href="/theory/a#old">Next</a></p><details><summary>Keys</summary><p>Answer.</p></details>';
        $result = TheoryPresentation::html((object) ['id' => 10, 'body' => $html]);
        $this->assertSame(['old', 'lesson-block-10-section-2'], array_column($result['toc'], 'id'));
        $this->assertSame('1. Tea & coffee', $result['toc'][0]['title']);
        $this->assertSame(2, (new DOMXPath($this->dom((string) $result['html'])))->query('//h2')->length);
        $this->assertStringContainsString('Tea &amp; coffee', (string) $result['html']);
        $this->assertStringNotContainsString('data-theory-details', (string) $result['html']);
        $broken = '<h4>Heading<p>Unclosed original &amp; text.';
        $this->assertSame($broken, (string) TheoryPresentation::html((object) ['id' => 1, 'body' => $broken])['html']);
        $this->assertSame([], TheoryPresentation::data('"scalar"'));
        $this->assertSame([], TheoryPresentation::data('{invalid'));
        $this->assertNull(TheoryPresentation::nativeView('../../private'));
    }

    public function test_course_and_theory_share_all_native_types_without_duplicate_ids_or_new_details(): void
    {
        foreach (TheoryPresentation::NATIVE_TYPES as $index => $type) {
            $block = new TextBlock(['type' => $type, 'body' => json_encode(['title' => ($index + 1).'. '.$type, 'items' => [], 'rows' => [], 'sections' => []]), 'uuid' => 'fixture-'.$index]);
            $block->id = $index + 1;
            $block->setRelation('tags', collect());
            $page = new Page(['title' => 'Unchanged H1']);
            $page->setRelation('tags', collect());
            $page->setRelation('textBlocks', collect([$block]));
            foreach (['theory.show', 'courses.partials.theory-page-content'] as $view) {
                $html = view($view, ['page' => $page, 'categories' => collect(), 'categoryPages' => collect()])->render();
                $xpath = new DOMXPath($this->dom($html));
                $this->assertSame(1, $xpath->query('//*[@id="block-'.($index + 1).'"]')->length, $view.' '.$type);
                $this->assertStringContainsString($type, $html);
                $this->assertStringNotContainsString('data-theory-details', $html);
            }
        }
        $this->assertSame('eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6', hash_file('sha256', base_path('docs/content/m23-authored-content.v1.json')));
        $this->assertSame('33373412ed077bf7fa0aea8dde8a6d2c211c126288b6cedfdde299ded43bf5a2', hash_file('sha256', base_path('docs/content/m24-authored-content.v1.json')));
    }

    public function test_malformed_native_json_keeps_the_existing_safe_fallback_and_never_leaks_json(): void
    {
        $block = new TextBlock(['type' => 'summary-list', 'body' => '{"title":"4. Source","items":["Authored item"}]}', 'uuid' => 'malformed-fixture']);
        $block->id = 322;
        $block->setRelation('tags', collect());
        $html = view('theory.partials.content-block', ['block' => $block])->render();
        $this->assertStringContainsString('data-theory-render-fallback="invalid-native-data"', $html);
        $this->assertSame(1, (new DOMXPath($this->dom($html)))->query('//*[@id="block-322"]')->length);
        $this->assertStringNotContainsString('Authored item', $html);
        $this->assertStringNotContainsString('title', $this->dom($html)->getElementsByTagName('body')->item(0)->textContent);
    }

    public function test_subtitle_keeps_its_old_anchor_without_repeating_the_hero_intro(): void
    {
        $block = new TextBlock(['type' => 'subtitle', 'body' => 'Intro shown in the hero only']);
        $block->id = 321;
        $html = view('theory.partials.content-block', ['block' => $block])->render();
        $matches = (new DOMXPath($this->dom($html)))->query('//*[@id="block-321"]');
        $this->assertSame(1, $matches->length);
        $this->assertStringNotContainsString('Intro shown in the hero only', $html);
    }

    public function test_lesson_toc_does_not_duplicate_an_authored_leading_number(): void
    {
        $html = view('theory.partials.lesson-toc', ['lessonToc' => [
            ['id' => 'authored', 'title' => '1. Existing authored heading'],
            ['id' => 'unnumbered', 'title' => 'Unnumbered heading'],
        ]])->render();
        $dom = $this->dom($html);
        $badges = (new DOMXPath($dom))->query('//span[contains(@class,"theory-toc-number")]');
        $this->assertSame(1, $badges->length);
        $this->assertSame('2', trim($badges->item(0)->textContent));
        $this->assertStringContainsString('1. Existing authored heading', $dom->textContent);
    }

    private function spaces(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private function dom(string $html): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $dom;
    }
}
