<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M91FutureSimpleCompletionContinuous as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Finite M91 teaching and old/new ledgers; no app, environment, SQL or HTTP. */
class M91FutureSimpleCompletionContinuousPackageTest extends TestCase
{
    private const OLD_CANONICALS = [
        ['Will you come tomorrow?', 'When will they arrive?'],
        ["I'll call you tomorrow.", 'It will start in an hour.'],
    ];
    // This vector must be reviewed against the final frozen six-author projection before execution.
    private const MEANING = [
        [['m91-future-simple-questions-meaning-1', 'a'], ['m91-future-simple-questions-meaning-2', 'b']],
        [['m91-future-simple-time-expressions-meaning-1', 'a'], ['m91-future-simple-time-expressions-meaning-2', 'b']],
        [['m91-future-continuous-forms-meaning-process', 'a'], ['m91-future-continuous-forms-meaning-background', 'b']],
        [['m91-future-continuous-negatives-meaning-all-day', 'b'], ['m91-future-continuous-negatives-meaning-permission', 'a']],
        [['m91-fc-questions-meaning-room', 'a'], ['m91-fc-questions-meaning-long', 'b']],
        [['m91-fc-time-meaning-reference', 'b'], ['m91-fc-time-meaning-overlap', 'a']],
    ];

    private function release(): array { return Package::load(dirname(__DIR__, 2)); }
    private function refused(callable $call): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $e) { $caught = $e; }
        self::assertInstanceOf(RuntimeException::class, $caught, 'Changed coherent payload must reach a finite guard.');
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
    private static function practice(array $master, int $owner): array
    {
        return $master['lessons'][$owner]['blocks'][Package::PRACTICE_SOURCE_INDICES[$owner]]['data'];
    }

    public function test_exact_six_owners_keep_42_continuing_components_and_add_only_four_practices(): void
    {
        [$before, $patch, $master, $contract] = $this->release(); Package::validate($before, $patch, $master, $contract);
        self::assertSame(Package::BASE_SHA, $master['base_sha']);
        self::assertSame(Package::OWNERS, array_column($master['lessons'], 'identity'));
        self::assertSame([7, 7, 8, 8, 8, 8], array_map(static fn ($l) => count($l['blocks']), $master['lessons']));
        $oldComponents = $newComponents = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $old = $before['targets'][$i]['before']; $after = $patch['targets'][$i]['after'];
            self::assertSame(Package::definition($lesson, $old), $after);
            self::assertSame($old['page']['title'], $lesson['raw_title']);
            self::assertSame(Package::RENDERED_TITLES[$i], $lesson['preserved_h1']);
            self::assertSame(Package::PAGE_IDS[$i], $patch['targets'][$i]['page_binding']['id']);
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$oldComponents;
                foreach (['type', 'column', 'heading', 'level', 'uuid', 'uuid_key', 'css_class'] as $field) {
                    self::assertSame(array_key_exists($field, $source), array_key_exists($field, $after['page']['blocks'][$slot]));
                    self::assertSame($source[$field] ?? null, $after['page']['blocks'][$slot][$field] ?? null);
                }
                self::assertSame(M26DetailPackage::uuid(Package::OWNERS[$i], $source, $slot + 1),
                    M26DetailPackage::uuid(Package::OWNERS[$i], $after['page']['blocks'][$slot], $slot + 1));
            }
            foreach (array_slice($after['page']['blocks'], count($old['page']['blocks'])) as $source) {
                ++$newComponents; self::assertGreaterThanOrEqual(2, $i);
                self::assertSame('practice-set', $source['type']); self::assertSame('left', $source['column']); self::assertNull($source['level']);
                self::assertSame('m91:'.Package::SLUGS[$i].':'.Package::SLUGS[$i].'-practice', $source['uuid_key']);
            }
        }
        self::assertSame([42, 4], [$oldComponents, $newComponents]);
    }

    public function test_sql_levels_display_focus_and_actual_component_sets_are_distinct(): void
    {
        [$before, , $master, $contract] = $this->release();
        self::assertSame(array_fill(0, 6, null), array_column($contract['targets'], 'level'));
        self::assertSame(Package::DISPLAY_LEVELS, array_column($master['lessons'], 'display_level'));
        self::assertSame(Package::FOCUS_LEVELS, array_column($master['lessons'], 'intended_own_practice_level'));
        foreach ($master['lessons'] as $i => $lesson) {
            self::assertArrayHasKey('technical_block_level', $lesson); self::assertNull($lesson['technical_block_level']);
            foreach ($lesson['blocks'] as $block) { self::assertArrayHasKey('level', $block); self::assertNull($block['level']); }
            $types = array_column($lesson['blocks'], 'type');
            if ($i === 0) { self::assertNotContains('usage-panels', $types); }
            if ($i === 1) { self::assertNotContains('mistakes-grid', $types); }
            $slot = Package::PRACTICE_SOURCE_INDICES[$i];
            self::assertSame('practice-set', $lesson['blocks'][$slot]['type']);
            self::assertSame($i < 2 ? 5 : null, $lesson['blocks'][$slot]['existing_source_index'] ?? null);
            if ($i < 4) { self::assertSame($before['targets'][$i]['before']['page']['subtitle_text'], $lesson['subtitle_text']); }
            foreach (['title', 'select_title', 'choice_title', 'input_title'] as $field) {
                self::assertStringNotContainsString('поліглот', mb_strtolower(self::practice($master, $i)[$field] ?? ''));
            }
        }
    }

    public function test_all_36_controls_are_literal_ledgered_eight_retained_and_28_new_with_12_full_inputs(): void
    {
        [$before, , $master] = $this->release(); $retained = $new = $graded = $full = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $data = self::practice($master, $i);
            self::assertSame([2, 2, 2, 0], array_map(static fn ($g) => count($data[$g] ?? []), ['selects', 'choices', 'inputs', 'rephrase']));
            $old = $i < 2 ? json_decode($before['targets'][$i]['before']['page']['blocks'][5]['body'], true, flags: JSON_THROW_ON_ERROR) : [];
            $seen = [];
            foreach ($master['editorial_review']['task_map'][Package::OWNERS[$i]] as $row) {
                $g = $row['after_group']; $n = $row['after_group_index']; $task = $data[$g][$n];
                self::assertArrayNotHasKey($g.':'.$n, $seen); $seen[$g.':'.$n] = true;
                self::assertSame($task, $row['after_task']); self::assertSame($slot, $row['after_block_source_index']);
                self::assertTrue($row['graded']); self::assertIsString($task['answer']);
                if (isset($old[$g][$n])) {
                    $source = $old[$g][$n]; self::assertSame($source, $row['old_task']);
                    self::assertSame($g, $row['old_group']); self::assertSame($n, $row['old_group_index']);
                    self::assertSame(5, $row['old_block_source_index']);
                    foreach (['id', 'source_index'] as $field) {
                        self::assertSame(array_key_exists($field, $source), array_key_exists($field, $task));
                        self::assertSame($source[$field] ?? null, $task[$field] ?? null);
                    }
                    self::assertSame($source['answer'], $task['answer']);
                    foreach ($source['accepted'] ?? [$source['answer']] as $alias) { self::assertContains($alias, $task['accepted']); }
                    ++$retained;
                } else {
                    foreach (['old_task', 'old_group', 'old_group_index', 'old_block_source_index'] as $field) { self::assertNull($row[$field]); }
                    self::assertStringStartsWith('m91-', $task['id']); ++$new;
                }
                ++$graded; if ($g === 'inputs') { ++$full; }
            }
            self::assertCount(6, $seen);
        }
        self::assertSame([8, 28, 36, 12], [$retained, $new, $graded, $full]);
    }

    public function test_all_eight_old_answers_and_four_old_full_canonicals_are_preserved_without_invented_ids(): void
    {
        [$before, , $master] = $this->release(); $controls = $answers = 0;
        foreach (range(0, 1) as $i) {
            $old = json_decode($before['targets'][$i]['before']['page']['blocks'][5]['body'], true, flags: JSON_THROW_ON_ERROR);
            $after = self::practice($master, $i); self::assertSame(self::OLD_CANONICALS[$i], array_column($after['inputs'], 'answer'));
            foreach (['selects', 'inputs'] as $g) { foreach ($old[$g] as $n => $task) {
                ++$controls; self::assertSame($task['answer'], $after[$g][$n]['answer']);
                $accepted = $task['accepted'] ?? [$task['answer']]; $answers += count($accepted);
                if (array_key_exists('accepted', $task)) { self::assertSame($accepted, $after[$g][$n]['accepted']); }
                foreach ($accepted as $alias) { self::assertContains($alias, $after[$g][$n]['accepted']); }
                foreach (['id', 'source_index'] as $field) { self::assertSame(array_key_exists($field, $task), array_key_exists($field, $after[$g][$n])); }
            } }
        }
        self::assertSame([8, 8], [$controls, $answers]);
    }

    public function test_bilingual_subtitle_titles_paired_mistakes_notes_and_table_fields_remain_explicit(): void
    {
        [$before, $patch, $master] = $this->release(); $mistakes = $rows = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            preg_match('~^<p><strong>(.*?)</strong>~su', $lesson['subtitle_html'], $title);
            self::assertSame(Package::RENDERED_TITLES[$i], strip_tags($title[1] ?? ''));
            self::assertSame($before['targets'][$i]['before']['page']['title'], $patch['targets'][$i]['after']['page']['title']);
            foreach ($lesson['blocks'] as $block) {
                if ($block['type'] === 'mistakes-grid') { foreach ($block['data']['items'] as $item) {
                    foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) { self::assertIsString($item[$field]); self::assertNotSame('', trim($item[$field])); }
                    foreach (['wrong_ua', 'right_ua', 'note'] as $field) { self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $item[$field]); }
                    self::assertStringNotContainsString('class=', $item['right_en']); ++$mistakes;
                } }
                if ($block['type'] === 'comparison-table') { foreach ($block['data']['rows'] as $row) {
                    self::assertNotSame('', trim(strip_tags($row['en']))); self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $row['ua']); ++$rows;
                } }
            }
        }
        self::assertSame(15, $mistakes); self::assertSame(26, $rows);
    }

    public function test_six_exact_type_four_banks_and_five_source_footer_labels_are_not_replaced_by_own_inputs(): void
    {
        [$before, , $master, $contract] = $this->release(); $links = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $data = self::practice($master, $i);
            self::assertSame(['4'], $data['linked_practice']['question_types']); self::assertSame('theory_links', $data['linked_practice']['source']);
            self::assertSame([Package::BANKS[$i]], $data['linked_practice']['seeder_classes']);
            self::assertSame([Package::BANKS[$i]], $contract['targets'][$i]['bank_seeders']); self::assertCount(2, $data['inputs']);
            $old = json_decode($before['targets'][$i]['before']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
            $items = $lesson['blocks'][6]['data']['items']; $links += count($items);
            $labels = static fn ($rows) => array_map(static fn ($row) => array_intersect_key($row, array_flip(['label', 'current'])), $rows);
            self::assertSame($labels($old['items']), $labels($items));
            [$b, $p, $m] = $this->release(); $m['lessons'][$i]['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data']['linked_practice']['seeder_classes'] = [Package::BANKS[($i + 1) % 6]];
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
        self::assertSame(26, $links);
    }

    public function test_retained_identity_canonical_alias_and_old_ledger_cannot_change_coherently(): void
    {
        foreach (['id', 'source_index', 'answer', 'alias', 'old_task', 'old_position'] as $case) {
            [$b, $p, $m] = $this->release(); $task = &$m['lessons'][1]['blocks'][5]['data']['inputs'][0];
            if ($case === 'id') { $task['id'] = 'm91-invented-old-id'; }
            elseif ($case === 'source_index') { $task['source_index'] = 1; }
            elseif ($case === 'answer') { $task['answer'] = 'I will stay here.'; $task['accepted'][] = $task['answer']; }
            elseif ($case === 'alias') { array_shift($task['accepted']); }
            unset($task); $this->syncTask($m, 1, 'inputs', 0);
            if (in_array($case, ['old_task', 'old_position'], true)) { foreach ($m['editorial_review']['task_map'][Package::OWNERS[1]] as &$row) {
                if ($row['after_group'] === 'inputs' && $row['after_group_index'] === 0) { if ($case === 'old_task') { $row['old_task']['answer'] = 'Changed old answer'; } else { $row['old_group_index'] = 1; } }
            } unset($row); }
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_hidden_criteria_option_collisions_and_raw_feedback_markers_are_refused(): void
    {
        foreach (['select_hidden', 'input_hidden', 'collision', 'html', 'marker', 'entity'] as $case) {
            [$b, $p, $m] = $this->release(); $group = $case === 'select_hidden' ? 'selects' : ($case === 'collision' ? 'choices' : 'inputs');
            $task = &$m['lessons'][0]['blocks'][5]['data'][$group][0];
            if ($case === 'select_hidden') { $task['label'] = 'a) Yes, I do. / b) Yes, I will.'; $task['prompt'] = 'Умова лише в невидимому для selects полі.'; }
            elseif ($case === 'input_hidden') { $task['after'] = ''; $task['context'] = 'Умова лише в невидимому для inputs полі.'; }
            elseif ($case === 'collision') { $task['options'] = ['a', 'A']; }
            else { $task['feedback'][mb_strtolower($task['answer'])] = ['html' => '<b>Правильно</b>', 'marker' => 'Правильно {a1}', 'entity' => 'Правильно &nbsp;'][$case]; }
            unset($task); $this->syncTask($m, 0, $group, 0); $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_missing_duplicate_oversized_and_already_ordered_ready_groups_are_refused(): void
    {
        foreach (['missing', 'duplicate', 'large', 'ordered'] as $case) {
            [$b, $p, $m] = $this->release(); $task = &$m['lessons'][0]['blocks'][5]['data']['inputs'][0];
            if ($case === 'missing') { $task['before'] = 'tomorrow? / you / come'; }
            elseif ($case === 'duplicate') { $task['before'] .= ' / will'; }
            elseif ($case === 'large') { $task['before'] = 'Will you come tomorrow?'; }
            else { $task['before'] = 'Will you / come / tomorrow?'; }
            unset($task); $this->syncTask($m, 0, 'inputs', 0); $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_no_hint_only_mistake_missing_ledger_or_changed_footer_label_is_accepted(): void
    {
        foreach (['hint_only', 'ledger', 'navigation'] as $case) {
            [$b, $p, $m] = $this->release();
            if ($case === 'hint_only') { $item = &$m['lessons'][0]['blocks'][3]['data']['items'][0]; $item['hint'] = $item['note']; unset($item['note'], $item); }
            elseif ($case === 'ledger') { array_pop($m['editorial_review']['task_map'][Package::OWNERS[0]]); }
            else { $m['lessons'][4]['blocks'][6]['data']['items'][0]['label'] = 'Changed related topic'; }
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_all_twelve_meaning_controls_are_new_while_old_ab_forms_and_inputs_keep_roles(): void
    {
        [$before, , $m] = $this->release(); $pairs = [];
        foreach ($m['lessons'] as $i => $lesson) {
            $data = self::practice($m, $i); $pairs[] = array_map(static fn ($task) => [$task['id'], $task['answer']], $data['choices']);
            foreach ($m['editorial_review']['task_map'][Package::OWNERS[$i]] as $row) {
                $role = $row['after_group'] === 'inputs' ? 'full_sentence' : ($row['after_group'] === 'choices' ? 'meaning' : 'grammar_form');
                self::assertSame($role, $row['assessed_role']);
                if ($i >= 2 || $row['after_group'] === 'choices') { self::assertNull($row['old_task']); }
                else { self::assertNotNull($row['old_task']); }
            }
        }
        self::assertSame(self::MEANING, $pairs);
    }

    public function test_exact_ab_semantic_scope_and_declared_synonyms_remain_visible_not_hidden_hints(): void
    {
        [, , $m] = $this->release(); $a = self::practice($m, 0); $b = self::practice($m, 1);
        self::assertStringContainsString('лише Олену', $a['selects'][1]['label']);
        self::assertStringContainsString('підтверджує', $a['selects'][1]['label']);
        self::assertStringContainsString('ствердної короткої', $m['lessons'][0]['blocks'][2]['data']['warning']);
        self::assertStringContainsString('Who will open', $a['choices'][0]['label']);
        self::assertStringContainsString('двох друзів', $a['choices'][1]['label']);
        self::assertStringContainsString('2 березня 2026', $b['choices'][0]['label']);
        self::assertStringContainsString('9–15 березня', $b['choices'][0]['prompt']);
        self::assertStringContainsString('10:00', $b['choices'][1]['label']);
        self::assertStringContainsString('тривалість', $b['choices'][1]['prompt']);
        self::assertStringContainsString('phone', $b['inputs'][0]['after']);
        self::assertContains("I will phone you tomorrow.", $b['inputs'][0]['accepted']);
        self::assertStringContainsString('14:00', $b['inputs'][1]['after']);
        self::assertStringContainsString('begin', $b['inputs'][1]['after']);
        self::assertContains("It'll begin in an hour.", $b['inputs'][1]['accepted']);
    }

    public function test_retained_loss_new_task_role_downgrade_and_owner_swaps_are_refused(): void
    {
        foreach (['drop-old', 'old-as-meaning', 'new-as-form', 'missing-owner', 'foreign-owner'] as $case) {
            [$b, $p, $m] = $this->release();
            if ($case === 'drop-old') { array_shift($m['lessons'][0]['blocks'][5]['data']['selects']); }
            elseif ($case === 'missing-owner') { array_pop($m['lessons']); }
            elseif ($case === 'foreign-owner') { $m['lessons'][2]['identity'] = 'Foreign\\Owner'; }
            else { foreach ($m['editorial_review']['task_map'][Package::OWNERS[0]] as &$row) {
                if ($case === 'old-as-meaning' && $row['after_group'] === 'selects' && $row['after_group_index'] === 0) { $row['assessed_role'] = 'meaning'; }
                if ($case === 'new-as-form' && $row['after_group'] === 'choices' && $row['after_group_index'] === 0) { $row['assessed_role'] = 'grammar_form'; }
            } unset($row); }
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }
}
