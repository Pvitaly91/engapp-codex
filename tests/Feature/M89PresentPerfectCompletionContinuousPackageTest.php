<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M89PresentPerfectCompletionContinuous as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Frozen teaching/ledger contracts only; no environment, working database or HTTP bootstrap. */
class M89PresentPerfectCompletionContinuousPackageTest extends TestCase
{
    private function release(): array { return Package::load(dirname(__DIR__, 2)); }

    private function refused(callable $call): void
    {
        $error = null;
        try { $call(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error, 'Coherent changed data must reach a finite guard.');
    }

    private function syncTask(array &$master, int $owner, string $group, int $index): void
    {
        $task = $master['lessons'][$owner]['blocks'][Package::PRACTICE_SOURCE_INDICES[$owner]]['data'][$group][$index];
        $found = 0;
        foreach ($master['editorial_review']['task_map'][Package::OWNERS[$owner]] as &$row) {
            if ($row['after_group'] === $group && $row['after_group_index'] === $index) { $row['after_task'] = $task; ++$found; }
        }
        unset($row); self::assertSame(1, $found);
    }

    public function test_frozen_six_owner_projection_preserves_all_48_continuing_native_blocks_and_titles(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame([8, 8, 8, 8, 8, 8], array_map(static fn ($l) => count($l['blocks']), $master['lessons']));
        self::assertSame(Package::OWNERS, array_column($master['lessons'], 'identity'));
        self::assertSame('1.0.0', $master['version']);
        self::assertSame(Package::BASE_SHA, $master['base_sha']);
        foreach ($master['lessons'] as $i => $lesson) {
            $old = $before['targets'][$i]['before']; $after = $patch['targets'][$i]['after'];
            self::assertSame(Package::definition($lesson, $old), $after);
            self::assertSame(Package::PAGE_IDS[$i], $patch['targets'][$i]['page_binding']['id']);
            self::assertSame(Package::CATEGORY_IDS[$i], $patch['targets'][$i]['page_binding']['page_category_id']);
            self::assertSame($old['page']['title'], $lesson['raw_title']);
            self::assertSame(Package::RENDERED_TITLES[$i], $lesson['preserved_h1']);
            foreach ($old['page']['blocks'] as $slot => $source) {
                foreach (['type', 'column', 'heading', 'level', 'uuid', 'uuid_key', 'css_class'] as $field) {
                    self::assertSame(array_key_exists($field, $source), array_key_exists($field, $after['page']['blocks'][$slot]));
                    self::assertSame($source[$field] ?? null, $after['page']['blocks'][$slot][$field] ?? null);
                }
                self::assertSame(M26DetailPackage::uuid(Package::OWNERS[$i], $source, $slot + 1),
                    M26DetailPackage::uuid(Package::OWNERS[$i], $after['page']['blocks'][$slot], $slot + 1));
            }
        }
    }

    public function test_sql_null_wide_display_focus_and_six_retained_practices_remain_distinct(): void
    {
        [, , $master, $contract] = $this->release();
        self::assertSame(array_fill(0, 6, null), array_column($contract['targets'], 'level'));
        self::assertSame(['A2–B1', 'A1–C2', 'A2–B1', 'A2–B1', 'A2–B1', 'A2–B1'], array_column($master['lessons'], 'display_level'));
        self::assertSame(array_fill(0, 6, 'A2'), array_column($master['lessons'], 'intended_own_practice_level'));
        foreach ($master['lessons'] as $i => $lesson) {
            self::assertArrayHasKey('technical_block_level', $lesson); self::assertNull($lesson['technical_block_level']);
            self::assertNull($lesson['subtitle_text']); self::assertSame([], $lesson['details'] ?? []);
            foreach ($lesson['blocks'] as $block) { self::assertArrayHasKey('level', $block); self::assertNull($block['level']); }
            $practice = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]];
            self::assertSame('practice-set', $practice['type']); self::assertSame('left', $practice['column']);
            self::assertSame(6, $practice['existing_source_index']);
            foreach (['title', 'select_title', 'choice_title', 'input_title', 'rephrase_title'] as $field) {
                self::assertStringNotContainsString('поліглот', mb_strtolower($practice['data'][$field] ?? ''));
            }
        }
    }

    public function test_all_44_controls_have_literal_ledger_with_32_old_answers_12_new_and_no_demos(): void
    {
        [$before, , $master] = $this->release(); $retained = $new = $full = $graded = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $data = $lesson['blocks'][$slot]['data'];
            self::assertSame([2, $i < 2 ? 2 : 4, 2, 0], array_map(static fn ($g) => count($data[$g] ?? []), ['selects', 'choices', 'inputs', 'rephrase']));
            $old = json_decode($before['targets'][$i]['before']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
            $seen = [];
            foreach ($master['editorial_review']['task_map'][Package::OWNERS[$i]] as $row) {
                $g = $row['after_group']; $n = $row['after_group_index']; $task = $data[$g][$n];
                self::assertArrayNotHasKey($g.':'.$n, $seen); $seen[$g.':'.$n] = true;
                self::assertSame($task, $row['after_task']); self::assertSame($slot, $row['after_block_source_index']);
                self::assertTrue($row['graded']); self::assertIsString($task['answer']);
                if (isset($old[$g][$n])) {
                    $source = $old[$g][$n]; self::assertSame($source, $row['old_task']);
                    self::assertSame($g, $row['old_group']); self::assertSame($n, $row['old_group_index']);
                    foreach (['id', 'source_index'] as $field) {
                        self::assertSame(array_key_exists($field, $source), array_key_exists($field, $task));
                        self::assertSame($source[$field] ?? null, $task[$field] ?? null);
                    }
                    self::assertSame($source['answer'] ?? $source['accepted'][0], $task['answer']);
                    foreach ($source['accepted'] ?? [$source['answer']] as $alias) { self::assertContains($alias, $task['accepted']); }
                    ++$retained;
                } else {
                    foreach (['old_task', 'old_group', 'old_group_index', 'old_block_source_index'] as $field) { self::assertNull($row[$field]); }
                    self::assertStringStartsWith('m89-', $task['id']); ++$new;
                }
                ++$graded; if ($g === 'inputs') { ++$full; }
            }
            self::assertCount($i < 2 ? 6 : 8, $seen);
        }
        self::assertSame([32, 12, 44, 12], [$retained, $new, $graded, $full]);
    }

    public function test_three_original_accepted_only_controls_preserve_all_six_aliases_and_canonical_order(): void
    {
        [$before, , $master] = $this->release();
        $expected = [
            [1, 1, ['We have not heard from her yet.', "We haven't heard from her yet."]],
            [3, 0, ["Olena hasn't been studying enough lately.", 'Olena has not been studying enough lately.']],
            [3, 1, ["They haven't been checking the equipment this week.", 'They have not been checking the equipment this week.']],
        ];
        foreach ($expected as [$owner, $index, $aliases]) {
            $old = json_decode($before['targets'][$owner]['before']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR)['inputs'][$index];
            $after = $master['lessons'][$owner]['blocks'][6]['data']['inputs'][$index];
            self::assertArrayNotHasKey('answer', $old, 'Accepted-only source was valid in the native runtime.');
            self::assertSame($aliases, $old['accepted']); self::assertSame($aliases, $after['accepted']);
            self::assertSame($aliases[0], $after['answer'], 'Explicit first-accepted canonical serves the unchanged package validator.');
            foreach (['id', 'source_index'] as $field) { self::assertSame(array_key_exists($field, $old), array_key_exists($field, $after)); }
            foreach ($aliases as $alias) { self::assertIsString($after['feedback'][mb_strtolower($alias)]); }
        }
    }

    public function test_all_six_subtitle_h1s_22_paired_mistake_notes_and_table_translations_are_visible_data(): void
    {
        [$before, $patch, $master] = $this->release(); $mistakes = 0; $tables = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            preg_match('~^<p><strong>(.*?)</strong>~su', $lesson['subtitle_html'], $title);
            self::assertSame(Package::RENDERED_TITLES[$i], strip_tags($title[1] ?? ''));
            self::assertSame($before['targets'][$i]['before']['page']['title'], $patch['targets'][$i]['after']['page']['title']);
            foreach ($lesson['blocks'] as $block) {
                if ($block['type'] === 'mistakes-grid') {
                    foreach ($block['data']['items'] as $item) {
                        foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) {
                            self::assertIsString($item[$field]); self::assertNotSame('', trim($item[$field]));
                        }
                        foreach (['wrong_ua', 'right_ua', 'note'] as $field) { self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $item[$field]); }
                        self::assertStringNotContainsString('class=', $item['right_en'] ?? ''); ++$mistakes;
                    }
                }
                if ($block['type'] === 'comparison-table') {
                    foreach ($block['data']['rows'] as $row) {
                        self::assertNotSame('', trim(strip_tags($row['en'])));
                        self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $row['ua']); ++$tables;
                    }
                }
            }
        }
        self::assertSame(22, $mistakes); self::assertSame(25, $tables);
        self::assertSame(json_decode($before['targets'][2]['before']['page']['blocks'][7]['body'], true, flags: JSON_THROW_ON_ERROR),
            $master['lessons'][2]['blocks'][7]['data']);
    }

    public function test_six_exact_type_four_banks_do_not_replace_own_full_inputs(): void
    {
        [, , $master, $contract] = $this->release();
        foreach ($master['lessons'] as $i => $lesson) {
            $data = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
            self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertSame('theory_links', $data['linked_practice']['source']);
            self::assertSame([Package::BANKS[$i]], $data['linked_practice']['seeder_classes']);
            self::assertSame([Package::BANKS[$i]], $contract['targets'][$i]['bank_seeders']);
            self::assertCount(2, $data['inputs']);
            [$b, $p, $m] = $this->release();
            $m['lessons'][$i]['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data']['linked_practice']['seeder_classes'] = [Package::BANKS[($i + 1) % 6]];
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_retained_ids_canonical_aliases_and_old_mapping_cannot_change_with_a_coherent_ledger(): void
    {
        foreach (['id', 'source_index', 'answer', 'alias', 'old_task', 'old_position'] as $kind) {
            [$b, $p, $m] = $this->release(); $task = &$m['lessons'][3]['blocks'][6]['data']['inputs'][0];
            if ($kind === 'id') { $task['id'] = 'm89-invented-old-id'; }
            elseif ($kind === 'source_index') { $task['source_index'] = 1; }
            elseif ($kind === 'answer') { $task['answer'] = 'Olena has been studying enough lately.'; $task['accepted'][] = $task['answer']; }
            elseif ($kind === 'alias') { $task['accepted'] = array_values(array_filter($task['accepted'], static fn ($v) => $v !== "Olena hasn't been studying enough lately.")); }
            unset($task); $this->syncTask($m, 3, 'inputs', 0);
            if (in_array($kind, ['old_task', 'old_position'], true)) {
                foreach ($m['editorial_review']['task_map'][Package::OWNERS[3]] as &$row) {
                    if ($row['after_group'] === 'inputs' && $row['after_group_index'] === 0) {
                        if ($kind === 'old_task') { $row['old_task']['accepted'][0] = 'Changed before'; }
                        else { $row['old_group_index'] = 1; }
                    }
                }
                unset($row);
            }
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_hidden_conditions_option_collisions_and_technical_feedback_are_refused(): void
    {
        foreach (['select_hidden', 'input_hidden', 'collision', 'html', 'marker', 'entity'] as $kind) {
            [$b, $p, $m] = $this->release(); $group = $kind === 'select_hidden' ? 'selects' : ($kind === 'collision' ? 'choices' : 'inputs');
            $index = $kind === 'collision' ? 1 : 0; $task = &$m['lessons'][4]['blocks'][6]['data'][$group][$index];
            if ($kind === 'select_hidden') { $task['label'] = 'a) She has finished. / b) She have finished.'; $task['prompt'] = 'Лише окреме поле з умовою.'; }
            elseif ($kind === 'input_hidden') { $task['after'] = ''; $task['context'] = 'Лише невидиме для цього control поле.'; }
            elseif ($kind === 'collision') { $task['options'] = ['a', 'A']; }
            else { $task['feedback'][mb_strtolower($task['answer'])] = ['html' => '<b>Правильно</b>', 'marker' => 'Правильно {a1}', 'entity' => 'Правильно &nbsp;'][$kind]; }
            unset($task); $this->syncTask($m, 4, $group, $index);
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_missing_auxiliary_wrong_multiplicity_oversized_and_presolved_token_groups_are_refused(): void
    {
        foreach (['missing', 'duplicate', 'large', 'ordered'] as $kind) {
            [$b, $p, $m] = $this->release(); $task = &$m['lessons'][4]['blocks'][6]['data']['inputs'][0];
            if ($kind === 'missing') { $task['before'] = 'the report. / Tom / finished'; }
            elseif ($kind === 'duplicate') { $task['before'] .= ' / has'; }
            elseif ($kind === 'large') { $task['before'] = 'Tom has finished the / report.'; }
            else { $task['before'] = 'Tom / has / finished / the report.'; }
            unset($task); $this->syncTask($m, 4, 'inputs', 0);
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_hint_only_mistakes_missing_ledger_and_changed_secondary_navigation_are_refused(): void
    {
        foreach (['hint_only', 'ledger', 'secondary_nav'] as $kind) {
            [$b, $p, $m] = $this->release();
            if ($kind === 'hint_only') { $item = &$m['lessons'][5]['blocks'][4]['data']['items'][0]; $item['hint'] = $item['note']; unset($item['note'], $item); }
            elseif ($kind === 'ledger') { array_pop($m['editorial_review']['task_map'][Package::OWNERS[0]]); }
            else { $m['lessons'][2]['blocks'][7]['data']['items'][0]['label'] = 'Changed related topic'; }
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_all_twelve_meaning_controls_are_new_and_retained_c_to_f_choices_remain_form_controls(): void
    {
        [$before, , $master] = $this->release();
        $expected = M89MeaningExpected::PAIRS;
        $actual = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $choices = $lesson['blocks'][6]['data']['choices'];
            $meaning = $i < 2 ? $choices : array_slice($choices, 2);
            $actual[] = array_map(static fn ($task) => [$task['id'], $task['answer']], $meaning);
            foreach ($master['editorial_review']['task_map'][Package::OWNERS[$i]] as $row) {
                $g = $row['after_group']; $n = $row['after_group_index'];
                $role = $g === 'inputs' ? 'full_sentence' : ($g === 'choices' && ($i < 2 || $n >= 2) ? 'meaning' : 'grammar_form');
                self::assertSame($role, $row['assessed_role']);
                if ($g === 'choices' && $i >= 2 && $n < 2) {
                    self::assertSame('choices', $row['old_group']);
                    self::assertSame($n, $row['old_group_index']); self::assertNotNull($row['old_task']);
                }
            }
        }
        self::assertSame($expected, $actual);
        foreach ([2, 3, 4, 5] as $owner) {
            $old = json_decode($before['targets'][$owner]['before']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertSame(array_column($old['choices'], 'answer'), array_column(array_slice($master['lessons'][$owner]['blocks'][6]['data']['choices'], 0, 2), 'answer'));
        }
    }

    public function test_visible_when_and_for_since_criteria_and_negative_amount_scope_are_not_hidden_or_overclaimed(): void
    {
        [, , $master] = $this->release();
        $a = $master['lessons'][0]['blocks'][6]['data'];
        self::assertStringContainsString('When did you send the email?', $a['selects'][1]['label']);
        self::assertStringContainsString('конкретний час', $a['selects'][1]['label']);
        self::assertSame('b', $a['selects'][1]['answer']);
        $f = $master['lessons'][5]['blocks'][6]['data'];
        self::assertSame(['for two hours', 'since Monday'], $f['options']);
        self::assertSame(['for two hours', 'since Monday'], array_column($f['selects'], 'answer'));
        self::assertStringContainsString('дві години', $f['selects'][0]['label']);
        self::assertStringContainsString('понеділок', $f['selects'][1]['label']);
        self::assertStringContainsString('граматично можливе', $f['selects'][0]['feedback']['since monday']);
        self::assertStringContainsString('не помилка for взагалі', $f['selects'][1]['feedback']['for two hours']);
        $c = $master['lessons'][2]; $d = $master['lessons'][3];
        self::assertSame('slate', $c['blocks'][2]['data']['sections'][3]['color'], 'Preserve actual old native fallback for unsupported violet.');
        self::assertSame('I have known him for years.', $c['blocks'][4]['data']['items'][2]['right_en']);
        self::assertStringNotContainsString('ще не йдуть у потрібному процесі', $d['subtitle_html']);
        self::assertStringContainsString('кількості', $d['subtitle_html']);
        self::assertStringContainsString('точну тривалість', $d['blocks'][6]['data']['choices'][2]['label']);
        self::assertStringContainsString('три хвилини', $d['blocks'][6]['data']['choices'][3]['label']);
    }


    public function test_retained_form_loss_reclassification_and_new_meaning_downgrade_fail_even_with_synced_task_copies(): void
    {
        foreach (['drop-old-choice', 'old-form-as-meaning', 'new-meaning-as-form', 'missing-owner', 'past-owner'] as $case) {
            [$b, $p, $m] = $this->release();
            if ($case === 'missing-owner') { array_pop($m['lessons']); }
            elseif ($case === 'past-owner') { $m['lessons'][2]['identity'] = str_replace('PresentPerfect', 'PastPerfect', Package::OWNERS[2]); }
            elseif ($case === 'drop-old-choice') {
                array_shift($m['lessons'][2]['blocks'][6]['data']['choices']);
                foreach ($m['editorial_review']['task_map'][Package::OWNERS[2]] as &$row) {
                    if ($row['after_group'] === 'choices' && isset($m['lessons'][2]['blocks'][6]['data']['choices'][$row['after_group_index']])) {
                        $row['after_task'] = $m['lessons'][2]['blocks'][6]['data']['choices'][$row['after_group_index']];
                    }
                }
                unset($row);
            } else {
                $index = $case === 'old-form-as-meaning' ? 0 : 2;
                foreach ($m['editorial_review']['task_map'][Package::OWNERS[2]] as &$row) {
                    if ($row['after_group'] === 'choices' && $row['after_group_index'] === $index) {
                        $row['assessed_role'] = $case === 'old-form-as-meaning' ? 'meaning' : 'grammar_form';
                    }
                }
                unset($row);
            }
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

}

final class M89MeaningExpected
{
    public const PAIRS = [[['m89-present-perfect-questions-meaning-subject', 'Хто виконав переставляння стільців.'], ['m89-present-perfect-questions-meaning-short-answer', 'У Лесі був такий досвід до цього моменту; її теперішнє місце перебування не названо.']], [['m89-present-perfect-time-meaning-startpoint', '2022 — початкова точка періоду до тепер; точну тривалість у роках без моменту мовлення не встановлено.'], ['m89-present-perfect-time-meaning-open-morning', 'До 10:30 Ольга відповіла на п\'ять листів; час кожної відповіді не названо, ранок ще триває.']], [['m89-ppc-forms-meaning-repeated-breaks', 'a'], ['m89-ppc-forms-meaning-recent-result', 'b']], [['m89-ppc-negatives-meaning-not-enough', 'a'], ['m89-ppc-negatives-meaning-not-long', 'b']], [['m89-present-perfect-continuous-questions-meaning-1', 'a'], ['m89-present-perfect-continuous-questions-meaning-2', 'b']], [['m89-present-perfect-continuous-time-expressions-meaning-1', 'b'], ['m89-present-perfect-continuous-time-expressions-meaning-2', 'a']]];
}
