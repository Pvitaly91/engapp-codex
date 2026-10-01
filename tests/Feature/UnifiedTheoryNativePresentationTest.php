<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UnifiedTheoryNativePresentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('uk');
        $this->withoutVite();
    }

    private function dom(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    private function text(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    public static function headings(): array
    {
        return [
            'period' => ['3. Порівняй B1 і B2', '3.', 'Порівняй B1 і B2'],
            'parenthesis' => ['12) Вправа 2', '12)', 'Вправа 2'],
            'embedded levels' => ['Порівняння A1–C2', '#', 'Порівняння A1–C2'],
            'embedded numbers' => ['Rule 42 and 51', '#', 'Rule 42 and 51'],
            'not a section prefix' => ['3.14 is not a section number', '#', '3.14 is not a section number'],
        ];
    }

    #[DataProvider('headings')]
    public function test_shared_header_preserves_anchored_author_number_and_punctuation(string $title, string $badge, string $remainder): void
    {
        $html = view('components.theory-native-header', ['title' => $title, 'level' => null, 'fallback' => '#'])->render();
        $xpath = $this->dom($html);
        $number = $xpath->query('//h2/span[1]')->item(0);
        self::assertSame($badge, $this->text($number->textContent));
        self::assertSame($remainder, $this->text($xpath->query('//h2/span[2]')->item(0)->textContent));
        if ($badge === '#') {
            self::assertTrue($number->hasAttribute('data-theory-ui'));
            self::assertSame('true', $number->getAttribute('aria-hidden'));
        } else {
            self::assertSame($title, $this->text($xpath->query('//h2')->item(0)->textContent));
            self::assertFalse($number->hasAttribute('aria-hidden'));
        }
        self::assertSame(0, $xpath->query('//details | //summary | //button')->length);
    }

    public function test_native_header_does_not_promote_heading_markup_to_executable_html(): void
    {
        $title = '4. <img src=x onerror=alert(1)> & <strong>текст</strong>';
        $html = view('components.theory-native-header', ['title' => $title, 'level' => null, 'fallback' => '#'])->render();
        $xpath = $this->dom($html);
        self::assertSame($title, $this->text($xpath->query('//h2')->item(0)->textContent));
        self::assertSame(0, $xpath->query('//img | //strong | //script | //*[@onerror]')->length);
    }

    public function test_authored_m24_native_sections_use_one_design_and_keep_complete_title(): void
    {
        $names = [
            'Tenses/TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder',
            'Tenses/TensesNarrativeTensesTheorySeeder',
            'BasicGrammar/BasicGrammarB1MixedRevisionTheorySeeder',
        ];
        $supported = ['forms-grid', 'lesson-rule-cards', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list', 'practice-set'];
        $count = 0;
        foreach ($names as $name) {
            $source = json_decode(file_get_contents(database_path('seeders/Page_V3/'.$name.'/definition.json')), true, flags: JSON_THROW_ON_ERROR);
            foreach ($source['page']['blocks'] as $index => $item) {
                if (!in_array($item['type'], $supported, true)) {
                    continue;
                }
                $data = json_decode($item['body'], true, flags: JSON_THROW_ON_ERROR);
                $block = (object) ['id' => $index + 1, 'uuid' => 'm25-native-'.$index, 'level' => null, 'tags' => collect(), 'body' => $item['body']];
                $html = view('engram.theory.blocks-v3.'.$item['type'], ['block' => $block, 'data' => $data, 'practiceQuestions' => collect()])->render();
                $xpath = $this->dom($html);
                self::assertSame(1, $xpath->query('//section[@id="block-'.($index + 1).'"]')->length);
                self::assertSame(1, $xpath->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-section-card ")]')->length);
                self::assertSame($data['title'], $this->text($xpath->query('//h2')->item(0)->textContent), $name.' '.$item['type']);
                self::assertSame(0, $xpath->query('//summary | //*[@data-theory-disclosure]')->length);
                $count++;
            }
        }
        self::assertGreaterThan(10, $count);
    }

    public function test_tense_matrix_keeps_cell_relations_and_safe_local_scroll(): void
    {
        // Technical rendering fixture, not a new authored lesson or database row.
        $data = [
            'title' => '2. Форми обох часів',
            'intro' => 'Порівняй базові моделі для ствердження, заперечення і питання.',
            'corner' => ['forms' => 'Forms', 'times' => 'Times'],
            'headers' => [['key' => 'affirmative', 'title' => 'Affirmative'], ['key' => 'negative', 'title' => 'Negative']],
            'rows' => [['time' => 'Past Simple', 'cells' => [
                'affirmative' => ['formula' => 'Subject + V2', 'lines' => ['I worked. / She went.']],
                'negative' => ['formula' => 'Subject + did not + V1', 'lines' => ['They did not work. / He did not go.']],
            ]]],
        ];
        $block = (object) ['id' => 42, 'uuid' => 'm25-table', 'body' => json_encode($data), 'level' => null];
        $html = view('engram.theory.blocks-v3.tense-forms-table', compact('block', 'data'))->render();
        $xpath = $this->dom($html);
        self::assertSame('2. Форми обох часів', $this->text($xpath->query('//h2')->item(0)->textContent));
        self::assertSame(3, $xpath->query('//thead/tr/th')->length);
        self::assertSame(1, $xpath->query('//tbody/tr')->length);
        self::assertSame(2, $xpath->query('//tbody/tr/td')->length);
        self::assertStringContainsString('I worked. / She went.', $xpath->query('//tbody/tr/td[1]')->item(0)->textContent);
        self::assertStringContainsString('They did not work. / He did not go.', $xpath->query('//tbody/tr/td[2]')->item(0)->textContent);
        self::assertSame(1, $xpath->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-table-scroll ")]')->length);
        self::assertSame(0, $xpath->query('//details | //summary')->length);
    }

    public function test_rule_cards_keep_existing_links_examples_and_inline_html_policy(): void
    {
        $data = ['title' => '1. Present Simple', 'items' => [[
            'title' => 'Affirmative', 'subtitle' => 'I <strong>work</strong>.',
            'rules' => [['label' => 'V1', 'formula' => '<strong>V1</strong>', 'text' => 'She <strong>works</strong>.', 'example' => 'I work. / She works.']],
        ]]];
        $block = (object) ['id' => 8, 'uuid' => 'm25-widget', 'level' => null, 'tags' => collect(), 'body' => json_encode($data)];
        $html = view('engram.theory.blocks-v3.lesson-rule-cards', ['block' => $block, 'data' => $data, 'lessonLinks' => ['Affirmative' => '/theory/tenses/present-simple/forms'], 'practiceQuestions' => collect()])->render();
        $xpath = $this->dom($html);
        self::assertSame(1, $xpath->query('//a[@href="/theory/tenses/present-simple/forms"]')->length);
        self::assertStringContainsString('I work. / She works.', $xpath->query('//a')->item(0)->textContent);
        self::assertStringNotContainsString('&lt;strong&gt;', $html);
        self::assertStringContainsString('theory-rule', $html);
        self::assertStringContainsString('theory-example', $html);
    }
}
