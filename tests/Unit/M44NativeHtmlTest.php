<?php

namespace Tests\Unit;

use App\Support\M43NativeHtml;
use App\Support\M44NativeHtml;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;

class M44NativeHtmlTest extends TestCase
{
    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors(); libxml_use_internal_errors($previous);
        return new DOMXPath($dom);
    }

    public function test_reference_examples_keep_exact_languages_and_separate_notes_without_executable_markup(): void
    {
        $example = [
            'en' => 'I’m meeting <script>alert(1)</script> & "Nina" tomorrow.',
            'uk' => 'Я зустрічаюся з <img src=x onerror=alert(2)> завтра.',
            'note_uk' => 'Окремий контекст, не переклад: <svg onload=alert(3)>.',
        ];
        $html = M44NativeHtml::examples([$example]);
        self::assertSame(str_replace('class="m43-example-note ', 'class="theory-example-note ',
            M43NativeHtml::examples([$example])), $html);
        $xp = $this->xpath($html);
        self::assertSame(0, $xp->query('//script | //img | //svg | //*[@onerror or @onload]')->length);
        self::assertSame(['en', 'uk', 'uk'], array_map(fn ($node) => $node->getAttribute('lang'),
            iterator_to_array($xp->query('//p'))));
        self::assertSame(array_values($example), array_map(fn ($node) => $node->textContent,
            iterator_to_array($xp->query('//p'))));
        self::assertSame(1, $xp->query('//p[contains(@class,"theory-example-note")]')->length);
        self::assertStringNotContainsString('font-mono', $html);
        self::assertStringNotContainsString('italic', $html);
        self::assertStringNotContainsString('m43_v1', $html);
    }

    public function test_formula_text_and_bilingual_table_cells_are_distinct_escaped_shapes(): void
    {
        $formula = 'will have been + V-ing <script>alert(1)</script>';
        $xp = $this->xpath(M44NativeHtml::cell(['formula' => $formula]));
        self::assertSame($formula, $xp->query('//body/span')->item(0)->textContent);
        self::assertSame(0, $xp->query('//script | //p')->length);
        $text = 'Контекст: <a href="javascript:alert(1)">текст</a>';
        $xp = $this->xpath(M44NativeHtml::cell(['text_uk' => $text]));
        self::assertSame($text, $xp->query('//span[@lang="uk"]')->item(0)->textContent);
        self::assertSame(0, $xp->query('//a')->length);
        self::assertSame('<span>Literal &amp; &lt;text&gt;</span>', M44NativeHtml::cell('Literal & <text>'));
        $example = ['en' => 'The tour starts at eleven.', 'uk' => 'Екскурсія починається об одинадцятій.'];
        self::assertSame(M44NativeHtml::examples([$example]), M44NativeHtml::cell($example));
    }

    public function test_muted_form_paragraphs_reuse_reference_escape_and_leave_default_paragraphs_exact(): void
    {
        $paragraphs = ['Перший абзац <script>alert(1)</script>.', 'Другий: ’ та &.'];
        self::assertSame(M43NativeHtml::paragraphs($paragraphs), M44NativeHtml::paragraphs($paragraphs));
        self::assertSame(M43NativeHtml::paragraphs($paragraphs, true), M44NativeHtml::paragraphs($paragraphs, true));
        $xp = $this->xpath(M44NativeHtml::paragraphs($paragraphs, true));
        self::assertSame(2, $xp->query('//p[@lang="uk" and @class="text-muted-foreground"]')->length);
        self::assertSame(0, $xp->query('//script')->length);
        self::assertSame($paragraphs, array_map(fn ($node) => $node->textContent, iterator_to_array($xp->query('//p'))));
    }

    public function test_generated_detail_feedback_decoration_preserves_every_inner_byte_and_is_idempotent(): void
    {
        $inner = '<p lang="en">We aren’t meeting on Saturday.</p><p lang="uk" class="theory-translation">Ми не зустрічаємося в суботу.</p><p lang="uk">Окремий контекст.</p>';
        $html = '<h4>Пояснення</h4><div class="theory-example">'.$inner.'</div>';
        $decorated = M44NativeHtml::decorateStoredExamples($html);
        self::assertSame(M43NativeHtml::decorateStoredExamples($html), $decorated);
        self::assertStringContainsString($inner, $decorated);
        self::assertSame($decorated, M44NativeHtml::decorateStoredExamples($decorated));
        self::assertSame($this->xpath($html)->query('//h4')->item(0)->textContent,
            $this->xpath($decorated)->query('//h4')->item(0)->textContent);
        $other = '<div class="other-example"><p>Інший компонент.</p></div>';
        self::assertSame($other, M44NativeHtml::decorateStoredExamples($other));
    }

    public function test_prompt_badge_preserves_author_title_and_all_instruction_paragraphs(): void
    {
        $html = '<h4>Зміна домовленості</h4><p>Побудуй пряме питання.</p><p>Контекст із &amp; знаком.</p>';
        $decorated = M44NativeHtml::decoratePracticePrompt($html, 6);
        self::assertSame(M43NativeHtml::decoratePracticePrompt($html, 6), $decorated);
        self::assertSame($decorated, M44NativeHtml::decoratePracticePrompt($decorated, 6));
        $xp = $this->xpath($decorated);
        self::assertSame('6', $xp->query('//h4/span[@data-theory-ui and @aria-hidden="true"]')->item(0)->textContent);
        self::assertSame(['Побудуй пряме питання.', 'Контекст із & знаком.'], array_map(fn ($node) => $node->textContent,
            iterator_to_array($xp->query('//p'))));
    }

    public function test_codegen_notes_are_muted_without_rewriting_note_text_or_other_class_shapes(): void
    {
        $html = '<div class="theory-example"><p lang="en">We are meeting on Friday.</p><p lang="uk" class="theory-translation">Ми зустрічаємося в п’ятницю.</p><p lang="uk" class="theory-example-note">Час уже відомий; це не гарантія.</p></div>';
        $old = $this->xpath($html);
        $decorated = M44NativeHtml::decorateStoredExamples($html);
        $current = $this->xpath($decorated);
        self::assertSame(array_map(fn ($n) => [$n->getAttribute('lang'), $n->textContent], iterator_to_array($old->query('//p'))),
            array_map(fn ($n) => [$n->getAttribute('lang'), $n->textContent], iterator_to_array($current->query('//p'))));
        self::assertSame(1, $current->query('//p[@class="theory-example-note text-muted-foreground"]')->length);
        self::assertSame($decorated, M44NativeHtml::decorateStoredExamples($decorated));
        self::assertSame(str_replace('class="theory-example-note"', 'class="theory-example-note text-muted-foreground"',
            M43NativeHtml::decorateStoredExamples($html)), $decorated);
        $other = '<p class="theory-example-note other">Інший компонент.</p><span class="other-note">Незмінний текст.</span>';
        self::assertSame($other, M44NativeHtml::decorateStoredExamples($other));
        self::assertStringNotContainsString('text-muted-foreground', M43NativeHtml::decorateStoredExamples($html));
    }

    public function test_feedback_note_gap_is_scoped_composition_not_an_aesthetic_override(): void
    {
        $styles = file_get_contents(dirname(__DIR__, 2).'/resources/views/engram/theory/blocks-v3/m44-native-styles.blade.php');
        self::assertStringContainsString('.theory-design .m44-native-design[data-m44-practice-scope] .theory-example-note{margin-top:.5rem}', $styles);
        foreach (['!important', 'font-mono', 'font-size:', 'color:', 'background-color:'] as $broadOverride) {
            self::assertStringNotContainsString($broadOverride, $styles);
        }
    }
}
