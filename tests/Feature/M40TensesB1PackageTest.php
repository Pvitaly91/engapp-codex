<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M24NativeTitleNumber;
use App\Support\M26DetailPackage;
use App\Support\M40TensesB1Package as Package;
use DOMDocument;
use DOMXPath;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M40TensesB1PackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function data(array $target): array
    {
        return json_decode($target['after']['page']['blocks'][$target['native_count'] + 2]['body'], true, flags: JSON_THROW_ON_ERROR);
    }

    private function block(array $target, int $index, int $id): TextBlock
    {
        $config = $target['after']['page']['blocks'][$index]; $block = new TextBlock;
        $block->forceFill(['id' => $id, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, $index + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $index + 1, 'type' => $config['type'],
            'body' => $config['body'], 'level' => $config['level'] ?? null, 'heading' => $config['heading'] ?? null]);
        $block->setRelation('tags', collect()); $block->setRelation('page', null);
        return $block;
    }

    public function test_exact_native_preservation_metadata_casing_split_and_deterministic_uuid(): void
    {
        [$before, $projection] = Package::load(); Package::validate($before, $projection);
        self::assertSame([8,6,4], array_column($projection['targets'], 'native_count'));
        foreach ($projection['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $after = $target['after']; $native = $target['native_count'];
            self::assertCount([11,9,7][$i], $after['page']['blocks']); self::assertCount(3, $target['plans']);
            self::assertSame($old['seeder']['class'], $target['identity']); self::assertSame([['tenses'],['tenses'],['mixed-revision']][$i], $target['ancestry']);
            $restored = $after; $restored['page']['blocks'] = $old['page']['blocks']; self::assertSame($old, $restored);
            self::assertSame(array_slice($old['page']['blocks'], 0, $native), array_slice($after['page']['blocks'], 0, $native));
            $first = $after['page']['blocks'][$native]; $first['type'] = 'box'; $first['body'] = $old['page']['blocks'][$native]['body'];
            self::assertSame($old['page']['blocks'][$native], $first);
            self::assertSame(['usage-panels','usage-panels','practice-set'], array_column(array_slice($after['page']['blocks'], $native), 'type'));
            foreach (array_slice($after['page']['blocks'], $native + 1) as $config) { self::assertStringStartsWith('m40-', $config['uuid_key']); }
            self::assertSame($after, json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertSame(range(1,3), array_column($target['plans'], 'legacy_section'));
            foreach ($target['plans'] as $plan) { self::assertSame([], $plan['points']); }
        }
        self::assertSame('Narrative Tenses: Past Simple, Past Continuous and Past Perfect', $projection['targets'][1]['after']['page']['title']);
        self::assertStringContainsString('<strong>Narrative tenses</strong>', $projection['targets'][1]['after']['page']['subtitle_html']);
    }

    public function test_finite_eighteen_tasks_all_thirty_two_controls_and_twelve_compounds(): void
    {
        [, $projection] = Package::load(); $controls = 0; $manual = 0; $compounds = 0; $ids = [];
        $expected = [
            [['select','select'],['select','select'],['manual'],['choice','choice'],['choice','choice'],['manual']],
            [['manual'],['select','select','choice'],['manual'],['choice'],['manual','manual'],['manual']],
            [['select','select'],['select','select'],['manual','manual'],['manual','manual'],['manual','manual'],['manual','manual','manual']],
        ];
        $classes = ['PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder', 'PolyglotNarrativeTensesBasicsLessonSeeder', 'PolyglotFinalDrillB1LessonSeeder'];
        foreach ($projection['targets'] as $i => $target) {
            $data = $this->data($target); self::assertSame(range(1,6), array_column($data['cases'], 'source_index'));
            self::assertSame(['Database\\Seeders\\V3\\Polyglot\\'.$classes[$i]], $data['linked_practice']['seeder_classes']);
            self::assertSame(['4'], $data['linked_practice']['question_types']);
            foreach ($data['cases'] as $j => $case) {
                self::assertSame($expected[$i][$j], array_column($case['controls'], 'kind'));
                if (count($case['controls']) > 1) { $compounds++; self::assertSame('compound', $case['interaction']); }
                foreach (['prompt','context','feedback','author_explanation'] as $field) { self::assertArrayNotHasKey($field, $case); }
                foreach ($case['controls'] as $control) {
                    $controls++; self::assertNotContains($control['id'], $ids); $ids[] = $control['id'];
                    foreach (['context','feedback','author_explanation'] as $field) { self::assertArrayNotHasKey($field, $control); }
                    if ($control['kind'] !== 'manual') { continue; }
                    $manual++; self::assertContains($control['answer'], $control['accepted']);
                    self::assertSame($control['answer'], implode(' ', $control['tokens']));
                    foreach ($control['tokens'] as $token) {
                        self::assertGreaterThanOrEqual(1, preg_match_all('/\S+/u', $token)); self::assertLessThanOrEqual(3, preg_match_all('/\S+/u', $token));
                        self::assertDoesNotMatchRegularExpression('/[.!?]\s+\p{L}/u', $token);
                    }
                }
            }
        }
        self::assertSame(32, $controls); self::assertSame(16, $manual); self::assertSame(12, $compounds);
    }

    public function test_source_allowed_aliases_and_all_b1_subparts_remain_required_data(): void
    {
        [, $projection] = Package::load(); [$perfect, $narrative, $b1] = array_map($this->data(...), $projection['targets']);
        self::assertContains('I have already translated the introduction. I have been translating the second chapter for two hours, but it is not yet ready.', $perfect['cases'][5]['controls'][0]['accepted']);
        self::assertContains('Yesterday I unlocked the gate. I switched on the light and opened the window.', $narrative['cases'][0]['controls'][0]['accepted']);
        self::assertContains('When we arrived, the courier had left.', $narrative['cases'][2]['controls'][0]['accepted']);
        self::assertContains('Olena was talking on the phone when I arrived. Someone had removed the note before I arrived. Then I opened the cupboard.', $narrative['cases'][5]['controls'][0]['accepted']);
        self::assertSame(['Mila asked me if I was ready.', 'She told me not to open the parcel.'], array_column($b1['cases'][2]['controls'], 'answer'));
        self::assertContains('The boxes must be labeled by the volunteers.', $b1['cases'][3]['controls'][0]['accepted']);
        self::assertSame(['The boxes must be labelled by the volunteers.', 'I had my bicycle repaired yesterday.'], array_column($b1['cases'][3]['controls'], 'answer'));
        self::assertSame(['Marta, who lives nearby, agreed to help.', 'Although it was late, we continued.'], array_column($b1['cases'][4]['controls'], 'answer'));
        self::assertSame(['I wish I had a bigger desk.', 'If we had left earlier, we would have caught the bus.', 'Lena might be in the reading room.'], array_column($b1['cases'][5]['controls'], 'answer'));
    }

    public function test_full_render_numbering_author_key_separation_and_natural_answer_typography(): void
    {
        [, $projection] = Package::load();
        foreach ($projection['targets'] as $i => $target) {
            foreach ($target['after']['page']['blocks'] as $j => $config) {
                $block = $this->block($target, $j, 6000 + $i * 100 + $j);
                $data = json_decode($config['body'], true, flags: JSON_THROW_ON_ERROR);
                // Navigation is deliberately rendered by the page footer, not
                // TheoryPresentation's standalone native content-block partial.
                if ($config['type'] === 'navigation-chips') { self::assertSame($config['body'], $block->body); continue; }
                $html = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
                if (isset($data['title']) && preg_match('/^(\d+)\./u', $data['title'], $number)) {
                    self::assertSame($number[1], M24NativeTitleNumber::forBlock($block, $data['title']));
                    self::assertStringContainsString($number[1], $html); self::assertStringContainsString(preg_replace('/^\d+\.\s*/u', '', $data['title']), $html);
                }
                if ($j < $target['native_count']) { continue; }
                self::assertNotNull(Package::presentation($block, $data));
                if ($config['type'] === 'usage-panels') { self::assertStringContainsString($data['intro'], $html); continue; }
                $dom = new DOMDocument; libxml_use_internal_errors(true); $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); libxml_clear_errors(); $xp = new DOMXPath($dom);
                self::assertSame(6, $xp->query('//*[@data-m40-ui-case]')->length); self::assertSame(6, $xp->query('//details[@data-m40-ui-explanation]')->length);
                self::assertSame(0, $xp->query('//fieldset//*[@data-m40-self-check-answers]')->length);
                foreach (['prompts','answers'] as $kind) { foreach ($data['author_self_check'][$kind] as $exact) { self::assertStringContainsString($exact, $html); } }
                foreach ($xp->query('//*[@data-m40-answer]') as $option) {
                    self::assertStringContainsString('text-transform:none', $option->getAttribute('style'));
                    self::assertStringNotContainsString(' — ', trim($option->textContent));
                }
                self::assertStringContainsString('autocomplete="off"', $html); self::assertStringNotContainsString('text-transform:uppercase', $html);
            }
        }
        $foreign = (object) ['seeder' => 'Unrelated']; self::assertNull(M24NativeTitleNumber::forBlock($foreign, '1. Example'));
    }

    public function test_legacy_box_anchor_identity_survives_dynamic_id_change_and_invalid_opt_in_refuses(): void
    {
        [$before, $projection] = Package::load();
        foreach ($projection['targets'] as $i => $target) {
            $n = $target['native_count'];
            $uuid = M26DetailPackage::uuid($target['identity'], $before['targets'][$i]['before']['page']['blocks'][$n], $n + 1);
            foreach ($target['plans'] as $j => $plan) {
                $block = $this->block($target, $n + $j, 9000 + $i * 100 + $j); $data = json_decode($block->body, true);
                $presentation = Package::presentation($block, $data); self::assertSame($uuid, $presentation['legacy_block_uuid']);
                self::assertSame($j + 1, $presentation['legacy_section']);
                $block->seeder = 'Foreign'; self::assertNull(Package::presentation($block, $data));
            }
        }
    }

    public function test_option_key_leak_missing_compound_part_and_native_change_fail_closed(): void
    {
        foreach (['option-key','missing-part','native','title','uuid'] as $kind) {
            [$before, $projection] = Package::load(); $target = &$projection['targets'][0]; $index = $target['native_count'] + 2;
            if ($kind === 'native') { $target['after']['page']['blocks'][1]['body'] .= ' '; }
            elseif ($kind === 'title') { $target['after']['page']['title'] = 'Wrong title'; }
            elseif ($kind === 'uuid') { $target['after']['page']['blocks'][$target['native_count']]['uuid_key'] = 'wrong'; }
            else {
                $data = json_decode($target['after']['page']['blocks'][$index]['body'], true);
                if ($kind === 'option-key') { $data['cases'][3]['controls'][0]['options'][0]['label'] = $data['author_self_check']['answers'][3]; }
                else { array_pop($data['cases'][0]['controls']); }
                $target['after']['page']['blocks'][$index]['body'] = Package::json($data);
            }
            unset($target);
            try { Package::validate($before, $projection); self::fail('Unapproved M40 projection mutation accepted.'); }
            catch (RuntimeException) { self::assertTrue(true); }
        }
    }
}
