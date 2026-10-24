<?php

namespace Tests\Unit;

use App\Support\M87ContinuousCompletionPastSimple as Package;
use M87LiveDiagnosticContract as Contract;
use PHPUnit\Framework\TestCase;
use RuntimeException;

require_once dirname(__DIR__, 2).'/tools/diagnostics/m87-live-contract.php';

/** Pure finite source/synthetic-DOM/profile checks; never SQL, HTTP or live UI acceptance. */
class M87LiveDiagnosticContractTest extends TestCase
{
    private static function lessons(): array
    {
        $lessons = [];
        foreach (Package::OWNERS as $i => $owner) {
            $items = [];
            foreach (range(0, 2) as $n) {
                $items[] = ['wrong_en' => 'She work.', 'wrong_ua' => 'Вона працює.',
                    'right_en' => 'She works.', 'right_ua' => 'Вона працює.',
                    'note' => 'Синтетичне пояснення '.$i.':'.$n.'.'];
            }
            $blocks = array_fill(0, 5, ['type' => 'synthetic-unused']);
            $blocks[4] = ['type' => 'mistakes-grid', 'data' => ['items' => $items]];
            $lessons[] = ['identity' => $owner, 'blocks' => $blocks];
        }
        return $lessons;
    }

    public function test_exact_eighteen_notes_have_no_legacy_hint_aliases(): void
    {
        self::assertSame(['mistake_blocks' => 6, 'required_notes' => 18, 'legacy_hint_aliases' => 0],
            Contract::assertMistakeNotes(self::lessons()));
    }

    public function test_an_omitted_note_cannot_be_hidden_by_a_hint(): void
    {
        $lessons = self::lessons(); $item = &$lessons[4]['blocks'][4]['data']['items'][0];
        $item['hint'] = $item['note']; unset($item['note']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('historical M85 hint aliases');
        Contract::assertMistakeNotes($lessons);
    }

    public function test_a_missing_note_without_any_hint_is_rejected(): void
    {
        $lessons = self::lessons(); unset($lessons[0]['blocks'][4]['data']['items'][1]['note']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('paired mistake/note field missing');
        Contract::assertMistakeNotes($lessons);
    }

    public function test_an_empty_note_and_missing_paired_translation_are_not_optional(): void
    {
        foreach (['note', 'wrong_ua', 'right_ua'] as $field) {
            $lessons = self::lessons(); $lessons[2]['blocks'][4]['data']['items'][1][$field] = ' ';
            try { Contract::assertMistakeNotes($lessons); self::fail('Empty '.$field.' was accepted.'); }
            catch (RuntimeException $e) { self::assertStringContainsString('field missing', $e->getMessage()); }
        }
    }

    public function test_a_shifted_mistake_slot_or_missing_item_is_rejected(): void
    {
        $lessons = self::lessons(); array_pop($lessons[3]['blocks'][4]['data']['items']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('slot4 three-item');
        Contract::assertMistakeNotes($lessons);
    }

    private static function document(string $html): array
    {
        $doc = new \DOMDocument('1.0', 'UTF-8'); $old = libxml_use_internal_errors(true);
        try { $ok = $doc->loadHTML('<?xml encoding="UTF-8">'.Contract::prepareHtmlForDom($html), LIBXML_NONET); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($old); }
        self::assertTrue($ok); return [$doc, new \DOMXPath($doc)];
    }

    private static function paired(bool $twoTranslations = true, bool $swapEnglish = false, bool $swapUkrainian = false): array
    {
        $item = ['wrong_en' => 'She work here.', 'right_en' => 'She works here.',
            'wrong_ua' => 'Вона працює тут: задум помилкової форми.', 'right_ua' => 'Вона працює тут.'];
        $wrong = $swapEnglish ? $item['right_en'] : $item['wrong_en'];
        $right = $swapEnglish ? $item['wrong_en'] : $item['right_en'];
        $wrongUa = $swapUkrainian ? $item['right_ua'] : $item['wrong_ua'];
        $rightUa = $swapUkrainian ? $item['wrong_ua'] : $item['right_ua'];
        [, $xp] = self::document('<article id="point"><div class="theory-example--wrong"><code lang="en">'.$wrong.'</code></div><p lang="uk" class="theory-translation">'.$wrongUa.'</p><div class="theory-example--right"><span lang="en">'.$right.'</span></div>'.($twoTranslations ? '<p lang="uk" class="theory-translation">'.$rightUa.'</p>' : '').'</article>');
        return [$xp, $xp->query('//*[@id="point"]')->item(0), $item, static fn ($s) => trim(strip_tags($s))];
    }

    public function test_two_actual_ordered_en_and_uk_nodes_are_required(): void
    {
        self::assertSame(['english_nodes' => 2, 'ukrainian_nodes' => 2, 'ordered_fields_exact' => true],
            Contract::assertPairedMistakeDom(...self::paired()));
    }

    public function test_one_translation_node_cannot_satisfy_both_models(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('two separate paired UK nodes');
        Contract::assertPairedMistakeDom(...self::paired(false));
    }

    public function test_swapping_english_models_is_rejected(): void
    {
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('wrong_en');
        Contract::assertPairedMistakeDom(...self::paired(true, true));
    }

    public function test_swapping_ukrainian_meanings_is_rejected(): void
    {
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('wrong_ua');
        Contract::assertPairedMistakeDom(...self::paired(true, false, true));
    }

    private static function callbacks(): array
    {
        $plain = static function (string $s): string {
            $s = preg_replace('/<(?:br\s*\/?|\/(?:p|div|li|section))>/i', ' ', $s);
            return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($s), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        };
        $text = static function (\DOMXPath $xp, \DOMNode $node) use ($plain): string {
            $parts = []; foreach ($xp->query('.//text()[not(ancestor::script) and not(ancestor::style)]', $node) as $n) { $parts[] = $n->nodeValue; }
            return $plain(implode(' ', $parts));
        };
        $contains = static function (string $text, mixed $expected, string $label) use ($plain): void {
            if (!is_string($expected) || $plain($expected) === '') { return; }
            if (!str_contains($text, $plain($expected))) { throw new RuntimeException('Missing SSR learning field: '.$label); }
        };
        $same = static function (mixed $actual, mixed $expected, string $label): void {
            if ($actual !== $expected) { throw new RuntimeException($label.' differs.'); }
        };
        return [$text, $plain, $contains, $same];
    }

    /** Synthetic C-shape: 11 graded, one ungraded demo, three full inputs; no real source or HTTP mutation. */
    private static function practice(bool $optionalPrompt = false, bool $omitPromptDom = false, bool $scoreDemo = false, bool $omitDemoModel = false, int $ownerIndex = 2): array
    {
        $data = ['select_title' => 'Обери форму', 'select_intro' => 'Синтетична інструкція вибору.',
            'choice_title' => 'Перевір зміст', 'choice_intro' => 'Синтетична змістова інструкція.',
            'input_title' => 'Побудуй речення', 'input_intro' => 'Синтетична інструкція повного введення.',
            'rephrase_title' => 'Виправ речення', 'rephrase_intro' => 'Синтетична інструкція виправлення.',
            'options' => ['a', 'b'], 'selects' => [], 'choices' => [], 'inputs' => [], 'rephrase' => []];
        foreach (range(0, $ownerIndex >= 2 ? 2 : 1) as $n) {
            $data['selects'][] = ['label' => 'Синтетична умова вибору '.$n.'.', 'answer' => 'a', 'accepted' => ['a'],
                'feedback' => ['a' => 'Правильно у заданій моделі.', 'b' => 'Інший варіант не відповідає умові.']];
            $data['inputs'][] = ['before' => 'now. / I / work', 'after' => 'Я працюю зараз: синтетична умова '.$n.'.',
                'answer' => 'I work now.', 'accepted' => ['I work now.'], 'feedback' => ['i work now.' => 'Потрібне повне речення.']];
        }
        foreach (range(0, $ownerIndex === 5 ? 2 : 1) as $n) {
            $task = ['label' => 'Повна синтетична змістова умова '.$n.'.', 'options' => ['Так', 'Ні'],
                'answer' => 'Так', 'accepted' => ['Так'], 'feedback' => ['так' => 'Цей висновок підтверджено.', 'ні' => 'Цей висновок не підтверджено.']];
            if ($optionalPrompt) { $task['prompt'] = 'Окремий необов’язковий стимул '.$n.'.'; }
            $data['choices'][] = $task;
        }
        if ($ownerIndex >= 2) {
        $data['rephrase'][] = ['example_label' => 'Неоцінюваний зразок.',
            'example_original' => 'Nina walk. — Ніна ходить пішки.', 'example_target' => 'Nina walks. — Ніна ходить пішки.'];
        foreach (range(1, 3) as $n) {
            $data['rephrase'][] = ['original' => 'I works. — Я працюю: синтетичне виправлення '.$n.'.',
                'placeholder' => 'Виправ речення', 'answer' => 'I work.', 'accepted' => ['I work.'],
                'feedback' => ['i work.' => 'З I потрібна базова форма.']];
        }
        }
        if ($scoreDemo) {
            $data['rephrase'][0] = ['original' => 'Nina walk. — Ніна ходить пішки.', 'placeholder' => 'Виправ речення',
                'answer' => 'Nina walks.', 'accepted' => ['Nina walks.'], 'feedback' => ['nina walks.' => 'Форма третьої особи.']];
        }
        $nonLearning = ['noInputTokenFocus' => false, 'inputTokenLabels' => [],
            'i18n' => ['check' => 'Перевірити'], 'wordSearchEndpoint' => 'http://gramlyze.loc/api/words/search?lang=uk'];
        $config = array_intersect_key($data, array_flip(['selects', 'choices', 'inputs', 'rephrase'])) + $nonLearning;
        $beforeConfig = array_fill_keys(['selects', 'choices', 'inputs', 'rephrase'], []) + $nonLearning;
        $esc = static fn ($s) => htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = '<section id="practice">';
        foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
            $prefix = ['selects' => 'select', 'choices' => 'choice', 'inputs' => 'input', 'rephrase' => 'rephrase'][$group];
            if ($data[$group] === []) { continue; }
            $html .= '<div class="theory-exercise"><h4>'.$esc($data[$prefix.'_title']).'</h4><p>'.$esc($data[$prefix.'_intro']).'</p>';
            foreach ($data[$group] as $n => $task) {
                if ($group === 'selects' || $group === 'choices') {
                    $html .= '<label>'.$esc($task['label']).'</label>';
                    if (isset($task['prompt']) && !$omitPromptDom) { $html .= '<p>'.$esc($task['prompt']).'</p>'; }
                    foreach ($task['options'] ?? $data['options'] as $option) {
                        $binding = ($group === 'selects' ? 'selectAnswers' : 'choiceAnswers').'['.$n."] = '".$option."'";
                        $html .= '<button type="button" @click="'.$esc($binding).'">'.$esc($option).'</button>';
                    }
                } elseif ($group === 'inputs') {
                    $html .= '<span>'.$esc($task['after']).'</span><template x-for="token in inputTokenBank('.$n.')"><button type="button">Токен</button></template>';
                    $attrs = ['data-word-suggestion-input' => 'inputs-'.$n, 'autocomplete' => 'off', 'autocorrect' => 'off',
                        'autocapitalize' => 'none', 'spellcheck' => 'false', 'placeholder' => '...',
                        'x-model' => 'inputAnswers['.$n.']', '@input' => 'syncInputTokenBank('.$n.')'];
                    $html .= '<input'; foreach ($attrs as $a => $v) { $html .= ' '.$a.'="'.$esc($v).'"'; } $html .= '>';
                } elseif (!array_key_exists('answer', $task)) {
                    foreach (['example_label', 'example_original', 'example_target'] as $field) {
                        if ($omitDemoModel && $field === 'example_target') { continue; }
                        $html .= '<p>'.$esc($task[$field]).'</p>';
                    }
                } else {
                    $html .= '<p>'.$esc($task['original']).'</p><input data-word-suggestion-input="rephrase-'.$n.'" placeholder="'.$esc($task['placeholder']).'">';
                }
            }
            $html .= '</div>';
        }
        $html .= '</section>'; [, $xp] = self::document($html);
        return [$xp, $xp->query('//*[@id="practice"]')->item(0), $data, $config, $beforeConfig, $ownerIndex, ...self::callbacks()];
    }

    public function test_exact_serializer_demo_and_full_input_profile_accepts_synthetic_c_shape(): void
    {
        $facts = Contract::assertPracticeDom(...self::practice());
        self::assertSame([11, 3, 1, 3, 10, 4],
            [$facts['graded'], $facts['full_inputs'], $facts['ungraded_demos'], $facts['rephrase_inputs'], $facts['option_texts'], $facts['groups']]);
        self::assertCount(12, $facts['controls']);
    }

    public function test_optional_prompt_can_be_absent_when_complete_criterion_is_in_label(): void
    {
        self::assertSame(11, Contract::assertPracticeDom(...self::practice(false))['graded']);
    }

    public function test_present_optional_prompt_is_checked_in_server_html(): void
    {
        self::assertSame(11, Contract::assertPracticeDom(...self::practice(true))['graded']);
    }

    public function test_present_prompt_cannot_be_silently_omitted_from_server_html(): void
    {
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('optional separate prompt');
        Contract::assertPracticeDom(...self::practice(true, true));
    }

    public function test_serialized_answer_alias_or_feedback_drift_is_rejected(): void
    {
        foreach (['answer', 'accepted', 'feedback'] as $field) {
            $args = self::practice();
            $args[3]['choices'][0][$field] = $field === 'answer' ? 'Ні' : ($field === 'accepted' ? ['Ні'] : ['так' => 'Інша версія.']);
            try { Contract::assertPracticeDom(...$args); self::fail('Serialized '.$field.' drift accepted.'); }
            catch (RuntimeException $e) { self::assertStringContainsString('Exact serialized choices', $e->getMessage()); }
        }
    }

    public function test_native_non_learning_config_drift_is_rejected(): void
    {
        $args = self::practice(); $args[3]['noInputTokenFocus'] = true;
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('BEFORE native non-learning config');
        Contract::assertPracticeDom(...$args);
    }

    public function test_demonstration_cannot_be_reclassified_as_a_scored_control(): void
    {
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('Exact per-owner own/full/demo');
        Contract::assertPracticeDom(...self::practice(false, false, true));
    }

    public function test_demo_model_and_its_translation_must_actually_exist_in_ssr(): void
    {
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('example_target');
        Contract::assertPracticeDom(...self::practice(false, false, false, true));
    }

    public function test_diagnostic_aliasing_preserves_raw_html_and_exact_binding_values(): void
    {
        $raw = '<button title="literal @click=inside" @click="selectAnswers[0] = &apos;a&apos;">a</button><input @input="syncInputTokenBank(0)">';
        $sha = hash('sha256', $raw);
        $expected = '<button title="literal @click=inside" data-m87-diagnostic-click="selectAnswers[0] = &apos;a&apos;">a</button><input data-m87-diagnostic-input="syncInputTokenBank(0)">';
        self::assertSame($expected, Contract::prepareHtmlForDom($raw));
        [, $xp] = self::document($raw);
        self::assertSame("selectAnswers[0] = 'a'", $xp->query('//button')->item(0)->getAttribute(Contract::domAttribute('@click')));
        self::assertSame('syncInputTokenBank(0)', $xp->query('//input')->item(0)->getAttribute(Contract::domAttribute('@input')));
        self::assertSame($sha, hash('sha256', $raw), 'The HTTP/source bytes are never the diagnostic parse copy.');
    }

    public function test_binding_lookalikes_inside_values_text_comments_and_raw_text_are_unchanged(): void
    {
        $raw = <<<'HTML'
<div title='literal @click="not-an-event"' data-note="literal @input=unchanged">Text @focus="not-an-attribute"</div>
<!-- <button @click="commentOnly()"> -->
<script>const sample = "<button @click='scriptOnly()'>";</script>
<style>.x::after {content: "<input @input='styleOnly()'>";}</style>
<textarea><button @click="textareaOnly()"></textarea>
<title>Text @click="titleOnly()"</title>
<button @click="real()">a</button>
HTML;
        $expected = str_replace('<button @click="real()">', '<button data-m87-diagnostic-click="real()">', $raw);
        self::assertSame($expected, Contract::prepareHtmlForDom($raw));
    }

    public function test_a_reserved_alias_cannot_spoof_an_event_from_raw_html(): void
    {
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('forbidden diagnostic alias');
        Contract::prepareHtmlForDom('<button data-m87-diagnostic-click="selectAnswers[0] = &apos;a&apos;">a</button>');
    }

    public function test_duplicate_case_insensitive_event_bindings_are_ambiguous(): void
    {
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('ambiguous duplicate event');
        Contract::prepareHtmlForDom('<button @click="first()" @CLICK="second()">a</button>');
    }

    public function test_an_option_without_its_actual_binding_is_not_silently_counted(): void
    {
        $args = self::practice();
        $args[0]->query('.//button', $args[1])->item(0)->removeAttribute(Contract::domAttribute('@click'));
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('selects/0/ordered owned option texts');
        Contract::assertPracticeDom(...$args);
    }

    public function test_an_option_bound_to_another_control_does_not_satisfy_ownership(): void
    {
        $args = self::practice();
        $args[0]->query('.//button', $args[1])->item(0)->setAttribute(Contract::domAttribute('@click'), "selectAnswers[1] = 'a'");
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('selects/0/ordered owned option texts');
        Contract::assertPracticeDom(...$args);
    }

    public function test_ordered_option_texts_remain_strict_after_diagnostic_aliasing(): void
    {
        $args = self::practice(); $buttons = $args[0]->query('.//button', $args[1]);
        $buttons->item(0)->nodeValue = 'b'; $buttons->item(1)->nodeValue = 'a';
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('selects/0/ordered owned option texts');
        Contract::assertPracticeDom(...$args);
    }

    public function test_manual_input_requires_the_exact_raw_sync_binding(): void
    {
        $args = self::practice();
        $args[0]->query('.//*[@data-word-suggestion-input="inputs-0"]', $args[1])->item(0)->removeAttribute(Contract::domAttribute('@input'));
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('inputs/0/native input @input');
        Contract::assertPracticeDom(...$args);
    }

    /** Keep the frozen label criterion in its own control, with actual paragraph/br boundaries. */
    private static function multipartLabelPractice(): array
    {
        $args = self::practice();
        $markup = '<p>Обери <strong>звичайну форму</strong> для заданого підмета.</p><p lang="en">a) I work.<br>b) I works.</p>';
        [, $fragmentXp] = self::document('<label id="multipart-label">'.$markup.'</label>');
        $label = $args[0]->query('.//label', $args[1])->item(0);
        $replacement = $label->ownerDocument->importNode($fragmentXp->query('//*[@id="multipart-label"]')->item(0), true);
        $label->parentNode->replaceChild($replacement, $label);
        $args[2]['selects'][0]['label'] = $markup;
        $args[3]['selects'][0]['label'] = $markup;
        return $args;
    }

    public function test_owned_label_paragraph_br_and_inline_boundaries_match_the_frozen_criterion(): void
    {
        $facts = Contract::assertPracticeDom(...self::multipartLabelPractice());
        self::assertSame(11, $facts['graded']); self::assertSame(10, $facts['option_texts']);
    }

    public function test_a_missing_owned_label_paragraph_is_not_masked_by_the_same_text_elsewhere(): void
    {
        $args = self::multipartLabelPractice();
        $label = $args[0]->query('//*[@id="multipart-label"]')->item(0);
        $paragraph = $args[0]->query('./p', $label)->item(1);
        $label->parentNode->appendChild($paragraph); // Still in group text, no longer in the owning label.
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('selects/0/exact owned label');
        Contract::assertPracticeDom(...$args);
    }

    public function test_reordered_owned_label_paragraphs_are_not_silently_accepted(): void
    {
        $args = self::multipartLabelPractice();
        $label = $args[0]->query('//*[@id="multipart-label"]')->item(0);
        $label->appendChild($args[0]->query('./p', $label)->item(0));
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('selects/0/exact owned label');
        Contract::assertPracticeDom(...$args);
    }

    private static function frozenSelectMetadataFixtures(): array
    {
        $out = []; $root = dirname(__DIR__, 2);
        foreach ([Package::BEFORE => Package::BEFORE_SHA, Package::MASTER => Package::MASTER_SHA] as $relative => $sha) {
            $path = $root.'/'.$relative; self::assertFileExists($path); self::assertFalse(is_link($path));
            $raw = file_get_contents($path); self::assertSame($sha, hash('sha256', $raw));
            $out[] = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        }
        return $out;
    }

    public function test_only_twelve_before_bound_generic_select_prompts_are_nonrendered_metadata(): void
    {
        [$before, $master] = self::frozenSelectMetadataFixtures(); $count = 0;
        foreach ([2, 3, 4, 5] as $ownerIndex) {
            $old = json_decode($before['targets'][$ownerIndex]['before']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
            $data = $master['lessons'][$ownerIndex]['blocks'][6]['data'];
            foreach ([0, 1, 2] as $index) {
                $fact = Contract::classifyGenericSelectPrompt($ownerIndex, $index, $data['selects'][$index], $old['selects'][$index]);
                self::assertFalse($fact['prompt_rendered_by_native_view']); self::assertFalse($fact['prompt_visible_acceptance']);
                self::assertTrue($fact['visible_owned_label_required']);
                self::assertSame(hash('sha256', $data['selects'][$index]['label']), $fact['frozen_label_sha256']);
                self::assertSame(hash('sha256', $old['selects'][$index]['prompt']), $fact['before_prompt_sha256']);
                self::assertSame('unchanged generic non-teaching select metadata', $fact['classification']); ++$count;
            }
        }
        self::assertSame(12, $count);
    }

    public function test_unique_select_prompts_unknown_positions_and_changed_before_or_label_cannot_use_the_exception(): void
    {
        [$before, $master] = self::frozenSelectMetadataFixtures();
        $old = json_decode($before['targets'][2]['before']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR)['selects'][0];
        $task = $master['lessons'][2]['blocks'][6]['data']['selects'][0];
        $cases = [[0, 0, $task, $old], [2, 3, $task, $old], [2, 0, $task, null]];
        $unique = $task; $unique['prompt'] = 'Використай лише нейтральне пряме питання з do.';
        $cases[] = [2, 0, $unique, $old];
        $changedLabel = $task; $changedLabel['label'] .= ' '; $cases[] = [2, 0, $changedLabel, $old];
        $changedBefore = $old; $changedBefore['prompt'] = 'Інше початкове завдання.'; $cases[] = [2, 0, $task, $changedBefore];
        foreach ($cases as $case) {
            try { Contract::classifyGenericSelectPrompt(...$case); self::fail('An unbound select prompt used the finite exception.'); }
            catch (RuntimeException $e) { self::assertStringContainsString('unsupported select prompt', $e->getMessage()); }
        }
    }

    public function test_an_unreviewed_select_prompt_is_not_silently_removed_from_the_visible_gate(): void
    {
        $args = self::practice();
        $args[2]['selects'][0]['prompt'] = 'Який варіант правильний?';
        $args[3]['selects'][0]['prompt'] = 'Який варіант правильний?';
        $this->expectException(RuntimeException::class); $this->expectExceptionMessage('unsupported select prompt');
        Contract::assertPracticeDom(...$args);
    }

    public function test_current_frozen_master_has_eighteen_notes_without_hint_aliases(): void
    {
        $file = dirname(__DIR__, 2).'/'.Package::MASTER; self::assertFileExists($file); self::assertFalse(is_link($file));
        $raw = file_get_contents($file); self::assertSame(Package::MASTER_SHA, hash('sha256', $raw));
        $master = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(18, Contract::assertMistakeNotes($master['lessons'])['required_notes']);
    }

    public function test_live_profile_has_only_two_exact_preexisting_omissions_and_no_historical_attempt_claims(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/tools/diagnostics/m87-live-pages.php');
        foreach (['$secondarySlots=[0=>7,4=>8]',
            '5422133ff19278a6e7d78e555d1c46980825854f2f0bbacfe0378eb5fccbdfe1',
            "'secondary_navigation_visible_acceptance'=>false",
            'accepted-with-pre-existing-secondary-navigation-omission',
            '[57,16,4,12,30,30]', '[24,18,2]', '[18,18,72]', '[16,13,16,16]',
            'not_applicable_zero_details', 'Exact fifteen unique local HTTP GET count'] as $literal) {
            self::assertStringContainsString($literal, $source);
        }
        foreach (['first_actual_attempt', 'pre_receipt_attempt', 'mistake-hint-visibility-v1.json',
            'exact_hint_note_aliases_SSR_checked', 'M85LiveDiagnosticContract'] as $oldStory) {
            self::assertStringNotContainsString($oldStory, $source);
        }
    }

    public function test_each_owner_has_its_exact_graded_demo_and_full_input_population_in_synthetic_dom(): void
    {
        $expectedCounts = [[2,2,2,0],[2,2,2,0],[3,2,3,4],[3,2,3,4],[3,2,3,4],[3,3,3,4]];
        foreach ([0,1,2,3,4,5] as $owner) {
            $facts = Contract::assertPracticeDom(...self::practice(false, false, false, false, $owner));
            self::assertSame($expectedCounts[$owner], array_values($facts['task_counts']));
            self::assertSame([6,6,11,11,11,12][$owner], $facts['graded']);
            self::assertSame([2,2,3,3,3,3][$owner], $facts['full_inputs']);
            self::assertSame([0,0,1,1,1,1][$owner], $facts['ungraded_demos']);
        }
    }

    public function test_live_profile_can_be_purely_parsed_without_app_sql_or_http(): void
    {
        $root = dirname(__DIR__, 2); $pipes = [];
        $process = proc_open([PHP_BINARY, $root.'/tools/diagnostics/m87-live-pages.php', '--profile-check-only'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
        self::assertIsResource($process);
        $stdout = stream_get_contents($pipes[1]); $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]); $code = proc_close($process);
        self::assertSame(0, $code, $stderr); self::assertSame('', $stderr);
        $result = json_decode($stdout, true, flags: JSON_THROW_ON_ERROR);
        self::assertTrue($result['parsed']); self::assertFalse($result['SQL_access']); self::assertSame(0, $result['HTTP_requests']);
        self::assertSame(27, $result['replacements']);
    }
}
