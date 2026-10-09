<?php

namespace Tests\Feature;

use App\Support\TheoryHtmlAdapter;
use DOMDocument;
use DOMXPath;
use Tests\TestCase;

/** Render-only fidelity; the framework runs in the guarded in-memory test runtime. */
class TheoryHtmlAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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

    public function test_example_retains_supported_root_attributes_and_inline_author_markup(): void
    {
        $html = '<div class="theory-example" id="example-source" data-origin="A&amp;B" aria-describedby="note-source">'
            .'<p lang="en">We <strong title="emphasis">had been waiting</strong>.</p>'
            .'<p lang="uk">Ми <em>чекали</em>.</p><p lang="uk">Пояснення <a href="#note-source">тут</a>.</p></div>';
        $out = TheoryHtmlAdapter::fragment($html)->toHtml();
        $xpath = $this->xpath($out);
        $example = $xpath->query('//*[@data-theory-component="example"]')->item(0);
        self::assertNotNull($example);
        self::assertSame('example-source', $example->getAttribute('id'));
        self::assertSame('A&B', $example->getAttribute('data-origin'));
        self::assertSame('note-source', $example->getAttribute('aria-describedby'));
        self::assertSame('had been waiting', $xpath->evaluate('string(//p[@lang="en"]/strong[@title="emphasis"])'));
        self::assertSame('чекали', $xpath->evaluate('string(//p[@lang="uk"]/em)'));
        self::assertSame('#note-source', $xpath->evaluate('string(//p[@lang="uk"]/a/@href)'));
    }

    public function test_unrepresentable_example_attributes_and_nodes_return_exact_source(): void
    {
        $pair = '<p lang="en">They had been waiting.</p><p lang="uk">Вони чекали.</p>';
        $cases = [
            "<div class='theory-example' title='source tooltip'>".$pair.'</div>',
            '<div class="theory-example" lang="uk">'.$pair.'</div>',
            '<div class="theory-example" data-theory-component="author">'.$pair.'</div>',
            '<div class="theory-example"><div role="group">'.$pair.'</div></div>',
            '<div class="theory-example"><div id="pair-anchor">'.$pair.'</div></div>',
            '<div class="theory-example"><p lang="en" data-source="sentence">English.</p><p lang="uk">Переклад.</p></div>',
            '<div class="theory-example"><p lang="en" id="sentence-anchor">English.</p><p lang="uk">Переклад.</p></div>',
            '<div class="theory-example">'.$pair.'<!-- source annotation --></div>',
            '<div class="theory-example">'.$pair.'<button type="button"></button></div>',
        ];
        foreach ($cases as $source) {
            self::assertSame($source, TheoryHtmlAdapter::fragment($source)->toHtml());
        }
        // A later unsupported fragment must not leave a half-normalized document.
        $combined = '<div class="theory-example">'.$pair.'</div>  '.$cases[0];
        self::assertSame($combined, TheoryHtmlAdapter::fragment($combined)->toHtml());
    }

    public function test_rich_heading_or_unrepresentable_section_attributes_keep_exact_original(): void
    {
        $cases = [
            "<section class='theory-rich-section' id='lesson'><h3>1. <em lang='en'>Past Perfect</em> &amp; значення</h3><p>Текст.</p></section>",
            '<section class="theory-html-section"><h2>Назва <span id="heading-anchor">уроку</span></h2><p>Текст.</p></section>',
            '<section class="theory-rich-section" lang="uk"><h3>Назва</h3><p>Текст.</p></section>',
            '<section class="theory-rich-section"><h3 aria-label="Повна назва">Назва</h3><p>Текст.</p></section>',
            '<section class="theory-rich-section"><h3>Назва</h3><div class="theory-section-body" data-owner="source"><p>Текст.</p></div></section>',
            '<section class="theory-rich-section" aria-labelledby="heading"><h3 id="heading">Назва</h3><p>Текст.</p></section>',
            '<section class="theory-rich-section"><h3>Назва</h3><div class="theory-section-body" id="body"><p>Текст.</p></div></section><aside aria-describedby="body">Примітка.</aside>',
        ];
        foreach ($cases as $source) {
            self::assertSame($source, TheoryHtmlAdapter::sections($source)->toHtml());
        }
    }

    public function test_plain_section_retains_supported_attributes_all_anchors_and_text_order(): void
    {
        $html = '<section class="theory-rich-section" id="section-source" data-source="native" aria-label="Формула">'
            .'<h3 id="heading-source">1. Формула</h3><div class="theory-rich-section-body" id="body-source">'
            .'<p id="first">Перше.</p><p id="second"><strong>Друге.</strong></p></div></section>';
        $xpath = $this->xpath(TheoryHtmlAdapter::sections($html)->toHtml());
        $section = $xpath->query('//*[@data-theory-component="section"]')->item(0);
        self::assertNotNull($section);
        self::assertSame('native', $section->getAttribute('data-source'));
        self::assertSame('Формула', $section->getAttribute('aria-label'));
        foreach (['section-source', 'heading-source', 'body-source', 'first', 'second'] as $id) {
            self::assertSame(1, $xpath->query('//*[@id="'.$id.'"]')->length, $id);
        }
        self::assertSame('Формула', trim($xpath->evaluate('string(//h2/span[last()])')));
        self::assertSame('Друге.', $xpath->evaluate('string(//*[@id="second"]/strong)'));
        self::assertSame(1, $xpath->query('//*[@id="first"]/following-sibling::p[@id="second"]')->length);
    }

    public function test_table_keeps_caption_cell_attributes_spans_and_both_source_anchors(): void
    {
        $html = '<div class="legacy-table-scroll" id="scroll-source"><table id="table-source" aria-label="Порівняння">'
            .'<caption id="caption-source">Форми</caption><thead><tr><th id="column-source" scope="col" colspan="2">Назва</th></tr></thead>'
            .'<tbody><tr><td headers="column-source" data-cell="first" lang="en"><strong>had been</strong></td>'
            .'<td lang="uk">тривала дія</td></tr></tbody></table></div>';
        $xpath = $this->xpath(TheoryHtmlAdapter::fragment($html)->toHtml());
        self::assertSame(1, $xpath->query('//*[@data-theory-component="table"]')->length);
        foreach (['scroll-source', 'table-source', 'caption-source', 'column-source'] as $id) {
            self::assertSame(1, $xpath->query('//*[@id="'.$id.'"]')->length, $id);
        }
        self::assertSame('Порівняння', $xpath->evaluate('string(//*[@data-theory-component="table"]/@aria-label)'));
        self::assertSame('2', $xpath->evaluate('string(//th/@colspan)'));
        self::assertSame('column-source', $xpath->evaluate('string(//td[1]/@headers)'));
        self::assertSame('first', $xpath->evaluate('string(//td[1]/@data-cell)'));
        self::assertSame('had been', $xpath->evaluate('string(//td[1]/strong)'));
        self::assertSame('тривала дія', $xpath->evaluate('string(//td[2])'));
    }

    public function test_unknown_table_or_scroll_wrapper_attributes_keep_exact_original(): void
    {
        $content = '<tbody><tr><td>Форма &amp; зміст</td></tr></tbody>';
        foreach (['data-owner="source"', 'aria-describedby="help"', 'lang="uk"', 'role="grid"', 'title="Назва"', 'style="width:100%"'] as $attribute) {
            $source = "<table class='original' ".$attribute.'>'.$content.'</table>';
            self::assertSame($source, TheoryHtmlAdapter::fragment($source)->toHtml());
            $source = '<div class="scroll" '.$attribute.'><table>'.$content.'</table></div>';
            self::assertSame($source, TheoryHtmlAdapter::fragment($source)->toHtml());
        }
    }

    public function test_malformed_and_unrecognized_fragments_remain_byte_exact(): void
    {
        foreach ([
            '<div><p>Незакритий абзац.</div>',
            '<table><tbody><tr><td>Комірка</tr></tbody></table>',
            '<section class="theory-rich-section"><h3>Незакритий заголовок</section>',
            "<p lang='uk'>Звичайний <em>приклад</em> &amp; пояснення.</p>\n<!-- залишити -->",
            '<em>They had been waiting.</em> — Вони чекали. Це не автоматична пара.',
        ] as $source) {
            self::assertSame($source, TheoryHtmlAdapter::fragment($source)->toHtml());
            self::assertSame($source, TheoryHtmlAdapter::sections($source)->toHtml());
        }
    }

    public function test_recognized_conversion_preserves_author_text_and_sequence(): void
    {
        $html = '<p id="before">До прикладу.</p><div class="theory-example">'
            .'<p lang="en">She had been <em>reading</em> &amp; writing.</p><p lang="uk">Вона читала й писала.</p>'
            .'<p lang="uk">Процес до події.</p></div><p id="after">Після прикладу.</p>';
        $xpath = $this->xpath(TheoryHtmlAdapter::fragment($html)->toHtml());
        $paragraphs = [];
        foreach ($xpath->query('//p') as $paragraph) { $paragraphs[] = $paragraph->textContent; }
        self::assertSame(['До прикладу.', 'She had been reading & writing.', 'Вона читала й писала.', 'Процес до події.', 'Після прикладу.'], $paragraphs);
        self::assertSame(1, $xpath->query('//*[@data-theory-component="example"]/preceding-sibling::p[@id="before"]')->length);
        self::assertSame(1, $xpath->query('//*[@data-theory-component="example"]/following-sibling::p[@id="after"]')->length);
    }
}
