<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M39AuthoredRevisionPackage as Original;
use App\Support\M39PracticeUiPackage as Package;
use DOMDocument;
use DOMXPath;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M39PracticeUiPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function data(array $target, string $state = 'after'): array
    {
        return json_decode($target[$state]['page']['blocks'][7]['body'], true, flags: JSON_THROW_ON_ERROR);
    }

    private function plain(string $html): string
    {
        $html = preg_replace('~</?(?:p|li|ul|ol|br)\b[^>]*>~i', ' ', $html);
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function dom(string $html): DOMXPath
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true); $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); libxml_clear_errors();
        return new DOMXPath($dom);
    }

    private function block(array $target, array $data, int $id): TextBlock
    {
        $source = $target['after']['page']['blocks'][7];
        $block = new TextBlock;
        $block->forceFill(['id' => $id, 'uuid' => M26DetailPackage::uuid($target['identity'], $source, 8),
            'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => 8, 'type' => 'practice-set',
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'level' => $source['level']]);
        $block->setRelation('tags', collect()); $block->setRelation('page', null);
        return $block;
    }

    public function test_frozen_master_original_projection_and_only_three_practice_bodies_change(): void
    {
        $package = Package::load(); Package::validate($package); [, $original] = Original::load();
        self::assertSame(1, $package['version']); self::assertSame('m39-practice-ui-quality-v1', $package['patch']);
        self::assertCount(3, $package['targets']);
        self::assertSame('bbfe17773121f6d13cda6beda3d15b5285c54fe73360c605045080e8beddb817', hash_file('sha256', base_path(Original::SOURCE)));
        $master = file_get_contents(base_path(Original::MASTER_PATH));
        self::assertSame(Original::MASTER_SHA, hash('sha256', str_replace("\r\n", "\n", $master)));
        foreach ($package['targets'] as $i => $target) {
            self::assertSame($original['targets'][$i]['identity'], $target['identity']);
            self::assertSame($original['targets'][$i]['path'], $target['path']);
            self::assertSame($original['targets'][$i]['after'], $target['before']);
            $restored = $target['after']; $restored['page']['blocks'][7]['body'] = $target['before']['page']['blocks'][7]['body'];
            self::assertSame($target['before'], $restored, 'Only the existing practice body changes: no metadata, theory, table, hero, anchor, UUID or bank mutation.');
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            $old = $this->data($target, 'before'); $new = $this->data($target);
            foreach (['author_self_check', 'm39_v1', 'linked_practice', 'title'] as $field) { self::assertSame($old[$field], $new[$field]); }
            foreach (['selects', 'choices', 'inputs', 'choice_options', 'select_title', 'choice_title', 'input_title'] as $removed) { self::assertArrayNotHasKey($removed, $new); }
            self::assertSame(['revision' => 1], $new['m39_practice_ui_v1']);
        }
    }

    public function test_finite_semantic_interactions_keep_all_eighteen_cases_and_compound_subparts(): void
    {
        $package = Package::load();
        $expected = [
            [['select','choice'], ['manual'], ['choice','multi','manual'], ['manual'], ['manual'], ['manual']],
            [['manual'], ['manual'], ['manual'], ['manual'], ['manual'], ['manual']],
            [['manual'], ['manual'], ['manual','choice'], ['choice','manual'], ['manual'], ['manual']],
        ];
        $ids = []; $manuals = 0; $controls = 0;
        foreach ($package['targets'] as $i => $target) {
            $data = $this->data($target);
            self::assertCount(6, $data['cases']); self::assertSame(range(1, 6), array_column($data['cases'], 'source_index'));
            foreach ($data['cases'] as $j => $case) {
                self::assertSame($expected[$i][$j], array_column($case['controls'], 'kind'));
                self::assertSame(count($case['controls']) > 1 ? 'compound' : 'manual', $case['interaction']);
                foreach (['prompt', 'context', 'feedback', 'author_explanation'] as $forbidden) { self::assertArrayNotHasKey($forbidden, $case); }
                foreach ($case['controls'] as $control) {
                    $controls++; if ($control['kind'] === 'manual') { $manuals++; }
                    self::assertNotContains($control['id'], $ids); $ids[] = $control['id'];
                    foreach (['prompt', 'context', 'feedback', 'author_explanation'] as $forbidden) { self::assertArrayNotHasKey($forbidden, $control); }
                }
            }
        }
        self::assertSame(23, $controls); self::assertSame(17, $manuals);
        $nominal = $this->data($package['targets'][0]); $c2 = $this->data($package['targets'][2]);
        self::assertSame('Ні.', $nominal['cases'][2]['controls'][0]['answer']);
        self::assertSame(['час уже скоротився', 'обслуговування поліпшилося'], $nominal['cases'][2]['controls'][1]['answer']);
        self::assertSame('The team plans a reduction in waiting times.', $nominal['cases'][2]['controls'][2]['answer']);
        self::assertSame('факт друку не заданий', $c2['cases'][2]['controls'][1]['answer']);
        self::assertSame('принаймні один непридатний', $c2['cases'][3]['controls'][0]['answer']);
        self::assertSame('Не встановлено точного числа непридатних і придатних.', $c2['cases'][3]['controls'][1]['answer']);
    }

    public function test_manual_answers_tokens_and_all_source_allowed_variants_are_exact(): void
    {
        foreach (Package::load()['targets'] as $target) {
            $data = $this->data($target); $old = $this->data($target, 'before');
            foreach ($data['cases'] as $case) {
                $key = $this->plain($data['author_self_check']['answers'][$case['source_index'] - 1]);
                foreach ($case['controls'] as $control) {
                    if ($control['kind'] !== 'manual') { continue; }
                    self::assertSame([], $control['options']); self::assertContains($control['answer'], $control['accepted']);
                    self::assertStringContainsString($control['answer'], $key, 'Canonical answer is an exact fragment of the original author key, not the key plus rationale.');
                    self::assertSame($control['answer'], implode(' ', $control['tokens']));
                    foreach ($control['tokens'] as $token) {
                        self::assertGreaterThanOrEqual(1, preg_match_all('/\S+/u', $token)); self::assertLessThanOrEqual(3, preg_match_all('/\S+/u', $token));
                        self::assertDoesNotMatchRegularExpression('/[.!?]\s+\p{L}/u', $token, 'No group crosses sentences.');
                    }
                }
                if ($case['source_index'] < 5) { continue; }
                $previousInput = array_values(array_filter($old['inputs'], fn ($input) => $input['source_index'] === $case['source_index']))[0];
                self::assertSame($previousInput['accepted'], $case['controls'][0]['accepted'], 'Previously accepted explicit variants are preserved.');
            }
        }
        $targets = Package::load()['targets'];
        $n = $this->data($targets[0]); $c2 = $this->data($targets[2]);
        self::assertContains('A reassessment by the committee of the exhibition plan may take place in October.', $n['cases'][1]['controls'][0]['accepted']);
        self::assertContains('The assessment of the insurance documents by the curator took place on Monday.', $n['cases'][3]['controls'][0]['accepted']);
        self::assertSame(['Marta needn’t have printed a second timetable.', 'Marta need not have printed a second timetable.'], $c2['cases'][2]['controls'][0]['accepted']);
        self::assertSame(['Had', 'the guide', 'not brought', 'a spare lamp,', 'we', 'could have been', 'stranded underground.'], $c2['cases'][0]['controls'][0]['tokens']);
        foreach ($c2['cases'][0]['controls'][0]['accepted'] as $candidate) {
            self::assertStringStartsWith('Had the guide not brought a spare lamp,', $candidate, 'Conditional first-clause order is explicit; negative-question contraction is not an accepted alias.');
        }
        self::assertSame(2, count(array_filter($c2['cases'][5]['controls'][0]['tokens'], fn ($token) => $token === 'the first prototype')), 'Repeated identical groups remain two distinct bank instances.');
    }

    public function test_selectable_options_are_only_finite_answer_fragments_not_full_keys_or_rationale(): void
    {
        $expectedLabels = [
            'n1-form' => ['is', 'are'],
            'n1-head' => ['expansion', 'rooms'],
            'n3-equivalence' => ['Ні.', 'Так.'],
            'n3-added-facts' => ['час уже скоротився', 'обслуговування поліпшилося', 'лише план'],
            'c2-3-b' => ['факт друку не заданий', 'надрукувала', 'не надрукувала'],
            'c2-4-guaranteed' => ['кожний непридатний', 'принаймні один непридатний', 'рівно один непридатний'],
        ];
        foreach (Package::load()['targets'] as $target) {
            $data = $this->data($target);
            foreach ($data['cases'] as $case) {
                $fullKey = $this->plain($data['author_self_check']['answers'][$case['source_index'] - 1]);
                foreach ($case['controls'] as $control) {
                    if ($control['kind'] !== 'manual') { self::assertSame($expectedLabels[$control['id']], array_column($control['options'], 'label'), 'Finite semantic answer fragments are independent of generated labels.'); }
                    foreach ($control['options'] as $index => $option) {
                        self::assertSame($option['value'], $option['label']);
                        self::assertNotSame($fullKey, $this->plain($option['label']));
                        self::assertStringNotContainsString(' — ', $option['label']);
                        self::assertDoesNotMatchRegularExpression('/<[^>]+>|Ключ:|Тому|Збережено|Не замінюємо/u', $option['label']);
                        Package::assertOptionContract($option['label'], $expectedLabels[$control['id']][$index], $fullKey);
                    }
                }
            }
        }
    }

    public function test_had_and_portraits_regression_fixtures_keep_answer_and_explanation_separate(): void
    {
        $target = Package::load()['targets'][2]; $data = $this->data($target);
        foreach ([0, 1] as $index) {
            $answer = $data['cases'][$index]['controls'][0]['answer'];
            $key = $data['author_self_check']['answers'][$index];
            $plainKey = $this->plain($key);
            Package::assertOptionContract($answer, $answer, $key);
            foreach ([$key, $plainKey, $answer.' '.$plainKey] as $dirty) {
                try { Package::assertOptionContract($dirty, $answer, $key); self::fail('Full explanation accepted as an answer option.'); }
                catch (RuntimeException) { self::assertTrue(true); }
            }
            // Render the actual choice control with the two source answers as a
            // test-only regression fixture. The accepted task remains manual.
            $fixture = $data;
            $fixture['cases'][$index]['controls'] = [['id' => 'source-regression-'.$index, 'kind' => 'choice', 'label' => 'Відповідь',
                'options' => [['value' => $answer, 'label' => $answer]], 'answer' => $answer]];
            $html = view('engram.theory.blocks-v3.m39-practice-ui', ['block' => $this->block($target, $fixture, 5392),
                'data' => $fixture, 'practiceQuestions' => collect()])->render();
            $xpath = $this->dom($html);
            $option = $xpath->query('//*[@data-m39-ui-case="'.($index + 1).'"]//*[@data-m39-answer]')->item(0);
            self::assertSame($answer, trim($option->textContent));
            self::assertNotSame($plainKey, trim($option->textContent));
            self::assertSame(0, $xpath->query('//*[@data-m39-ui-case="'.($index + 1).'"]//fieldset//*[@data-m39-self-check-answers]')->length);
            self::assertSame(1, $xpath->query('//*[@data-m39-ui-case="'.($index + 1).'"]/details[@data-m39-ui-explanation]')->length);
            self::assertStringContainsString($key, $html);
        }
    }

    public function test_all_prompts_full_author_keys_and_natural_case_render_separate_from_options(): void
    {
        foreach (Package::load()['targets'] as $i => $target) {
            $data = $this->data($target); $block = $this->block($target, $data, 5390 + $i);
            self::assertNotNull(Package::presentation($block, $data));
            $html = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
            $xpath = $this->dom($html);
            self::assertSame(6, $xpath->query('//*[@data-m39-ui-case]')->length);
            self::assertSame(6, $xpath->query('//*[@data-m39-author-prompt]')->length);
            self::assertSame(6, $xpath->query('//details[@data-m39-ui-explanation]')->length);
            self::assertSame(0, $xpath->query('//fieldset//*[@data-m39-self-check-answers]')->length);
            foreach (['prompts', 'answers'] as $kind) { foreach ($data['author_self_check'][$kind] as $exact) { self::assertStringContainsString($exact, $html); } }
            foreach ($xpath->query('//*[@data-m39-answer]') as $option) {
                self::assertStringContainsString('text-transform:none', $option->getAttribute('style'));
                self::assertDoesNotMatchRegularExpression('/(?:^|\s)uppercase(?:\s|$)/', $option->getAttribute('class'));
            }
            self::assertStringContainsString(':open="checked[', $html);
            self::assertStringContainsString('autocomplete="off"', $html);
            self::assertStringContainsString('@keydown.ctrl.enter.prevent=', $html);
            self::assertStringNotContainsString('text-transform:uppercase', $html);
        }
    }

    public function test_source_semantic_option_key_ownership_and_missing_part_mutations_fail_closed(): void
    {
        foreach (['key-option', 'translation-option', 'missing-part', 'wrong-owner', 'lost-prompt', 'changed-modal', 'cross-sentence-token', 'negative-inversion-prefix'] as $kind) {
            $package = Package::load();
            $data = $this->data($package['targets'][2]);
            if ($kind === 'key-option') { $data['cases'][2]['controls'][1]['options'][0]['label'] = $data['author_self_check']['answers'][2]; }
            elseif ($kind === 'translation-option') { $data['cases'][2]['controls'][1]['options'][0]['label'] .= ' — Марта надрукувала другий розклад.'; }
            elseif ($kind === 'missing-part') { array_pop($data['cases'][2]['controls']); }
            elseif ($kind === 'wrong-owner') { $package['targets'][2]['identity'] = $package['targets'][1]['identity']; }
            elseif ($kind === 'lost-prompt') { array_pop($data['author_self_check']['prompts']); }
            elseif ($kind === 'changed-modal') { $data['cases'][0]['controls'][0]['answer'] = str_replace('could', 'would', $data['cases'][0]['controls'][0]['answer']); }
            elseif ($kind === 'negative-inversion-prefix') {
                $wrong = "Hadn't the guide brought a spare lamp, we could have been stranded underground.";
                self::assertNotSame($data['cases'][0]['controls'][0]['answer'], $wrong);
                $data['cases'][0]['controls'][0]['accepted'][] = $wrong;
            }
            else { $data['cases'][5]['controls'][0]['tokens'][4] = 'was successful. The'; }
            $package['targets'][2]['after']['page']['blocks'][7]['body'] = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            try { Package::validate($package); self::fail('Malformed author/interaction mapping accepted.'); }
            catch (RuntimeException) { self::assertTrue(true); }
        }
    }

    public function test_presentation_rejects_wrong_owner_locale_uuid_order_and_edited_body(): void
    {
        $target = Package::load()['targets'][0]; $data = $this->data($target);
        foreach (['owner', 'locale', 'uuid', 'order', 'body'] as $kind) {
            $block = $this->block($target, $data, 5390);
            if ($kind === 'owner') { $block->seeder = 'Foreign'; }
            elseif ($kind === 'locale') { $block->locale = 'en'; }
            elseif ($kind === 'uuid') { $block->uuid = 'unknown'; }
            elseif ($kind === 'order') { $block->sort_order = 2; }
            else { $data['cases'][0]['controls'][0]['answer'] = 'are'; }
            self::assertNull(Package::presentation($block, $data));
        }
    }
}
