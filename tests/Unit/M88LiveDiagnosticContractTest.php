<?php

namespace Tests\Unit;

use App\Support\M88PastContinuousPresentPerfectBasics as Package;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure diagnostic-fixture tests only; not fabricated live BEFORE or browser evidence. */
class M88LiveDiagnosticContractTest extends TestCase
{
    private static function contract(): void
    {
        $path = dirname(__DIR__, 2).'/tools/diagnostics/m88-live-contract.php';
        self::assertFileExists($path);
        self::assertSame('3bf30a202acb921e148fcea0c1135e873dda28ba25966588abd67e2b10d5c922', hash_file('sha256', $path));
        require_once $path;
    }

    private function lessons(): array
    {
        $out = [];
        foreach (Package::OWNERS as $i => $owner) {
            $items = [];
            for ($n = 0; $n < ($i === 5 ? 4 : 3); ++$n) {
                $items[] = ['wrong_en' => 'She have finished.', 'wrong_ua' => 'Вона закінчила.',
                    'right_en' => 'She has finished.', 'right_ua' => 'Вона закінчила.',
                    'note' => 'Синтетичне пояснення для перевірки guard, не текст реального уроку.'];
            }
            $out[] = ['identity' => $owner, 'blocks' => [4 => ['type' => 'mistakes-grid', 'data' => ['items' => $items]]]];
        }
        return $out;
    }

    private function refused(callable $call): void
    {
        $error = null;
        try { $call(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
    }

    private static function plain(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function dom(string $html): DOMXPath
    {
        $document = new DOMDocument('1.0', 'UTF-8'); $old = libxml_use_internal_errors(true);
        try { self::assertTrue($document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET)); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($old); }
        return new DOMXPath($document);
    }

    public function test_exact_19_notes_and_six_control_population_vectors_are_finite(): void
    {
        self::contract();
        self::assertSame([6, 6, 6, 6, 6, 6], \M88LiveDiagnosticContract::GRADED);
        self::assertSame([2, 2, 2, 2, 2, 2], \M88LiveDiagnosticContract::FULL_INPUTS);
        self::assertSame([0, 0, 0, 0, 0, 0], \M88LiveDiagnosticContract::DEMOS);
        self::assertSame(['mistake_blocks' => 6, 'required_notes' => 19, 'legacy_hint_aliases' => 0],
            \M88LiveDiagnosticContract::assertMistakeNotes($this->lessons()));
    }

    public function test_missing_paired_field_note_hint_alias_wrong_owner_or_slot_cannot_be_treated_as_visible(): void
    {
        self::contract();
        foreach (['note', 'paired', 'hint', 'owner', 'slot', 'count'] as $kind) {
            $lessons = $this->lessons();
            if ($kind === 'note') { unset($lessons[0]['blocks'][4]['data']['items'][0]['note']); }
            elseif ($kind === 'paired') { unset($lessons[5]['blocks'][4]['data']['items'][3]['right_ua']); }
            elseif ($kind === 'hint') { $lessons[4]['blocks'][4]['data']['items'][0]['hint'] = 'Не дозволений legacy alias.'; }
            elseif ($kind === 'owner') { $lessons[0]['identity'] = Package::OWNERS[1]; }
            elseif ($kind === 'slot') { $lessons[0]['blocks'][3] = $lessons[0]['blocks'][4]; unset($lessons[0]['blocks'][4]); }
            else { array_pop($lessons[5]['blocks'][4]['data']['items']); }
            $this->refused(fn () => \M88LiveDiagnosticContract::assertMistakeNotes($lessons));
        }
    }

    public function test_paired_mistake_dom_requires_separate_ordered_en_and_uk_nodes(): void
    {
        self::contract(); $item = $this->lessons()[0]['blocks'][4]['data']['items'][0];
        $html = '<article id="point"><div class="theory-example--wrong"><span lang="en">She have finished.</span></div>'
            .'<p lang="uk" class="theory-translation">Вона закінчила.</p>'
            .'<div class="theory-example--right"><span lang="en">She has finished.</span></div>'
            .'<p lang="uk" class="theory-translation">Вона закінчила.</p></article>';
        $xp = $this->dom($html); $point = $xp->query('//*[@id="point"]')->item(0);
        self::assertSame(['english_nodes' => 2, 'ukrainian_nodes' => 2, 'ordered_fields_exact' => true],
            \M88LiveDiagnosticContract::assertPairedMistakeDom($xp, $point, $item, self::plain(...)));
        foreach ([
            str_replace('lang="en"', 'lang="uk"', $html),
            str_replace('She has finished.', 'She finished.', $html),
            str_replace('<p lang="uk" class="theory-translation">Вона закінчила.</p>', '', $html),
            str_replace('theory-example--right', 'theory-example--wrong', $html),
        ] as $bad) {
            $badXp = $this->dom($bad); $badPoint = $badXp->query('//*[@id="point"]')->item(0);
            $this->refused(fn () => \M88LiveDiagnosticContract::assertPairedMistakeDom($badXp, $badPoint, $item, self::plain(...)));
        }
    }

    public function test_lexer_changes_only_real_start_tag_event_names_not_text_or_script_values(): void
    {
        self::contract();
        $raw = '<!-- @click="comment" --><button @click="answer = &quot;@focus&quot;" data-note="@input">Text @click</button>'
            .'<script>const text = \'<button @click="untouched">\';</script>'
            .'<style>.x:after { content: "@focus"; }</style><textarea>@input</textarea><title>@click</title>';
        $expected = str_replace('<button @click="answer', '<button data-m88-diagnostic-click="answer', $raw);
        self::assertSame($expected, \M88LiveDiagnosticContract::prepareHtmlForDom($raw));
        foreach ([
            '<button data-m88-diagnostic-click="spoof">x</button>',
            '<button @click="one" @click="two">x</button>',
            '<button @click="unterminated>',
            '<script>unterminated',
            '<!-- unterminated',
        ] as $bad) { $this->refused(fn () => \M88LiveDiagnosticContract::prepareHtmlForDom($bad)); }
    }

    public function test_only_four_frozen_legacy_generic_select_prompts_are_classified_as_nonlearning_metadata(): void
    {
        self::contract(); [$before, , $master] = Package::load(dirname(__DIR__, 2)); $count = 0;
        foreach ([4, 5] as $owner) {
            $old = json_decode($before['targets'][$owner]['before']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach ([0, 1] as $index) {
                $task = $master['lessons'][$owner]['blocks'][6]['data']['selects'][$index];
                $record = \M88LiveDiagnosticContract::classifyGenericSelectPrompt($owner, $index, $task, $old['selects'][$index]);
                self::assertSame('unchanged generic non-teaching select metadata', $record['classification']);
                self::assertFalse($record['prompt_rendered_by_native_view']);
                self::assertFalse($record['prompt_visible_acceptance']);
                self::assertTrue($record['visible_owned_label_required']); ++$count;
            }
        }
        self::assertSame(4, $count);
    }

    public function test_changed_or_unique_teaching_prompt_cannot_borrow_the_generic_prompt_exception(): void
    {
        self::contract(); [$before, , $master] = Package::load(dirname(__DIR__, 2));
        $task = $master['lessons'][4]['blocks'][6]['data']['selects'][0];
        $old = json_decode($before['targets'][4]['before']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR)['selects'][0];
        foreach (['owner', 'index', 'label', 'prompt', 'before'] as $kind) {
            $owner = 4; $index = 0; $after = $task; $prior = $old;
            if ($kind === 'owner') { $owner = 0; }
            elseif ($kind === 'index') { $index = 2; }
            elseif ($kind === 'label') { $after['label'] .= ' '; }
            elseif ($kind === 'prompt') { $after['prompt'] = 'Нове окреме навчальне питання.'; }
            else { unset($prior['prompt']); }
            $this->refused(fn () => \M88LiveDiagnosticContract::classifyGenericSelectPrompt($owner, $index, $after, $prior));
        }
    }
}
