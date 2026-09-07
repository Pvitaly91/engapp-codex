<?php

namespace Tests\Unit;

use App\Support\TheoryInlineHtml;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TheoryInlineHtmlTest extends TestCase
{
    public function test_preserves_needed_inline_formatting_and_only_known_span_classes(): void
    {
        $result = TheoryInlineHtml::render(
            'Закінчення <strong>-ed</strong>, <em>дієслово</em><br><span class="text-slate-500 arbitrary" title="omit">примітка</span>'
        )->toHtml();

        $this->assertSame('Закінчення <strong>-ed</strong>, <em>дієслово</em><br><span class="text-slate-500">примітка</span>', $result);
        $this->assertSame($result, TheoryInlineHtml::render($result)->toHtml());
    }

    #[DataProvider('unsafeHtml')]
    public function test_removes_active_elements_attributes_and_unsafe_urls(string $payload): void
    {
        $result = TheoryInlineHtml::render($payload)->toHtml();
        $dom = $this->document($result);
        $xpath = new DOMXPath($dom);

        $this->assertSame(0, $xpath->query('//script|//style|//iframe|//object|//embed|//svg|//math|//img|//a|//form|//input|//template')->length, $result);
        $this->assertSame(0, $xpath->query('//body//*[@*[name() != "class"]]')->length, $result);
        $this->assertSame(0, $xpath->query('//body//*[@class and not(self::span)]')->length, $result);
        $this->assertStringNotContainsString('javascript:', $result);
        $this->assertStringNotContainsString('data:text/html', $result);
    }

    public static function unsafeHtml(): array
    {
        return [
            'script' => ['До<script>alert(document.cookie)</script><strong>після</strong>'],
            'events and styles' => ['<strong onclick="alert(1)" onmouseover="alert(2)" style="background:url(javascript:alert(3))">слово</strong><img src=x onerror="alert(4)">'],
            'unsafe urls' => ['<a href="javascript:alert(1)">текст</a><a href="data:text/html,x">інший</a><span src="javascript:x">примітка</span>'],
            'encoded protocol' => ['<a href="&#x6a;avascript:alert(1)">слово</a><a href="java&#10;script:alert(2)">ще</a>'],
            'foreign content' => ['<svg><a xlink:href="javascript:alert(1)"><text>bad</text></a></svg><math><mtext><img src=x onerror=alert(2)></mtext></math>'],
            'malformed markup' => ['<strong onclick=alert(1)>слово<iframe srcdoc="&lt;script&gt;alert(2)&lt;/script&gt;"></iframe></strong><template><script>alert(3)</script></template>'],
            'no arbitrary attributes' => ['<span class="text-slate-500" id="target" data-url="javascript:x" x-init="alert(1)">слово</span><code formaction="javascript:x">code</code>'],
        ];
    }

    public function test_preserves_quotes_ampersands_comparison_signs_and_ukrainian_text(): void
    {
        $text = 'Українська «мова»: "yes" і \'no\'; 2 < 3 && 5 > 4.';
        $result = TheoryInlineHtml::render($text)->toHtml();

        $this->assertSame($text, $this->document($result)->getElementsByTagName('body')->item(0)->textContent);
        $this->assertStringContainsString('&lt;', $result);
        $this->assertStringContainsString('&amp;', $result);
    }

    public function test_encoded_code_examples_remain_literal_and_are_not_decoded_into_elements(): void
    {
        $result = TheoryInlineHtml::render('<code>&lt;strong&gt;текст&lt;/strong&gt; &amp; &quot;лапки&quot;</code>')->toHtml();
        $dom = $this->document($result);

        $this->assertSame(0, $dom->getElementsByTagName('strong')->length);
        $this->assertSame('<strong>текст</strong> & "лапки"', $dom->getElementsByTagName('code')->item(0)->textContent);
    }

    private function document(string $html): DOMDocument
    {
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $dom;
    }
}
