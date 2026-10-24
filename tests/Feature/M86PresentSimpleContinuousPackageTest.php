<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M86PresentSimpleContinuousBasics as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Frozen content and finite negative contracts; no working database or HTTP. */
class M86PresentSimpleContinuousPackageTest extends TestCase
{
    private function release(): array { return Package::load(dirname(__DIR__, 2)); }
    private function artifact(string $path): array
    {
        $file = dirname(__DIR__, 2).'/'.$path; self::assertFileExists($file); self::assertFalse(is_link($file));
        return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    }
    private function refused(callable $call, string $fragment = 'M86'): void
    {
        $caught = null; try { $call(); } catch (RuntimeException $e) { $caught = $e; }
        self::assertInstanceOf(RuntimeException::class, $caught, 'Changed coherent data must reach the finite guard.');
        self::assertStringContainsString($fragment, $caught->getMessage());
    }
    private function syncTask(array &$master, int $owner, string $group, int $index): void
    {
        $task = $master['lessons'][$owner]['blocks'][Package::PRACTICE_SOURCE_INDICES[$owner]]['data'][$group][$index];
        $found = 0;
        foreach ($master['editorial_review']['task_map'][Package::OWNERS[$owner]] as &$row) {
            if ($row['after_group'] === $group && $row['after_group_index'] === $index) { $row['after_task'] = $task; $found++; }
        }
        unset($row); self::assertSame(1, $found);
    }
    private function resyncProjection(array $before, array &$patch, array $master): void
    {
        foreach ($master['lessons'] as $i => $lesson) {
            $patch['targets'][$i]['after'] = Package::definition($lesson, $before['targets'][$i]['before']);
            $patch['targets'][$i]['block_keys'] = array_column($lesson['blocks'], 'key');
        }
    }

    public function test_frozen_six_owner_package_uses_unchanged_native_projection_and_exact_source_metadata(): void
    {
        [$b, $p, $m, $c] = $this->release(); Package::validate($b, $p, $m, $c);
        self::assertSame(Package::OWNERS, array_column($m['lessons'], 'identity'));
        self::assertSame('1.0.0', $m['version']); self::assertSame(Package::BASE_SHA, $m['base_sha']);
        self::assertSame([8, 8, 9, 8, 8, 8], array_map(static fn ($l) => count($l['blocks']), $m['lessons']));
        foreach ($m['lessons'] as $i => $lesson) {
            self::assertSame($b['targets'][$i]['before']['page']['title'], $lesson['raw_title']);
            self::assertSame(Package::RENDERED_TITLES[$i], $lesson['preserved_h1']);
            self::assertSame(Package::PAGE_IDS[$i], $p['targets'][$i]['page_binding']['id']);
            self::assertSame(Package::CATEGORY_IDS[$i], $p['targets'][$i]['page_binding']['page_category_id']);
            self::assertSame(Package::ANCESTRIES[$i], $p['targets'][$i]['ancestry']);
            self::assertSame(Package::definition($lesson, $b['targets'][$i]['before']), $p['targets'][$i]['after']);
            foreach ($b['targets'][$i]['before']['page']['blocks'] as $slot => $old) {
                $new = $p['targets'][$i]['after']['page']['blocks'][$slot];
                foreach (['type', 'column', 'heading', 'level', 'uuid', 'uuid_key', 'css_class'] as $field) {
                    self::assertSame(array_key_exists($field, $old), array_key_exists($field, $new), $i.':'.$slot.':'.$field);
                    self::assertSame($old[$field] ?? null, $new[$field] ?? null);
                }
                self::assertSame(M26DetailPackage::uuid(Package::OWNERS[$i], $old, $slot + 1),
                    M26DetailPackage::uuid(Package::OWNERS[$i], $new, $slot + 1));
            }
        }
    }

    public function test_null_sql_display_ranges_focus_and_two_appended_practices_are_distinct(): void
    {
        [, , $m, $c] = $this->release(); $bindings = $this->artifact(Package::BINDINGS)['bindings'];
        Package::assertBindings($bindings);
        self::assertSame(array_fill(0, 6, null), array_column($c['targets'], 'level'));
        self::assertSame(array_fill(0, 6, 'A1–A2'), array_column($m['lessons'], 'display_level'));
        self::assertSame(['A1', 'A1', 'A1', 'A2', 'A1', 'A1'], array_column($m['lessons'], 'intended_own_practice_level'));
        foreach ($m['lessons'] as $i => $lesson) {
            self::assertArrayHasKey('technical_block_level', $lesson); self::assertNull($lesson['technical_block_level']);
            self::assertNull($lesson['subtitle_text']);
            $practice = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]];
            self::assertSame('practice-set', $practice['type']); self::assertSame('left', $practice['column']);
            self::assertSame($i < 4 ? 6 : null, $practice['existing_source_index']);
            if ($i < 4) { self::assertSame([], $bindings[$i]['appended_blocks']);
                self::assertSame(Package::EXISTING_PRACTICE_UUIDS[$i], $bindings[$i]['existing_practice']['uuid']); }
            else { self::assertNull($bindings[$i]['existing_practice'] ?? null); self::assertCount(1, $bindings[$i]['appended_blocks']); }
            foreach ($lesson['blocks'] as $block) { self::assertArrayHasKey('level', $block); self::assertNull($block['level']); }
            foreach (['title', 'select_title', 'input_title', 'rephrase_title', 'choice_title'] as $field) {
                self::assertStringNotContainsString('поліглот', mb_strtolower($practice['data'][$field] ?? ''));
            }
        }
    }

    public function test_exact_old_to_new_task_ledger_preserves_35_answers_all_aliases_and_four_ungraded_demos(): void
    {
        [$b, , $m] = $this->release(); $retained = $new = $demos = $full = 0; $perOwner = [];
        foreach ($m['lessons'] as $i => $lesson) {
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $data = $lesson['blocks'][$slot]['data']; $count = 0;
            $old = $i < 4 ? json_decode($b['targets'][$i]['before']['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR) : [];
            $mapped = [];
            foreach ($m['editorial_review']['task_map'][Package::OWNERS[$i]] as $row) {
                $g = $row['after_group']; $n = $row['after_group_index']; $task = $data[$g][$n];
                self::assertSame($task, $row['after_task']); self::assertSame($slot, $row['after_block_source_index']);
                self::assertArrayNotHasKey($g.':'.$n, $mapped); $mapped[$g.':'.$n] = true;
                if (isset($old[$g][$n])) {
                    $source = $old[$g][$n]; self::assertSame($source, $row['old_task']);
                    self::assertSame($g, $row['old_group']); self::assertSame($n, $row['old_group_index']);
                    foreach (['id', 'source_index'] as $field) {
                        self::assertSame(array_key_exists($field, $source), array_key_exists($field, $task));
                        self::assertSame($source[$field] ?? null, $task[$field] ?? null);
                    }
                    if (!array_key_exists('answer', $source)) {
                        self::assertSame('rephrase', $g); self::assertSame(0, $n); self::assertFalse($row['graded']);
                        self::assertArrayNotHasKey('answer', $task); self::assertArrayNotHasKey('accepted', $task);
                        self::assertStringContainsString($source['example_original'], $task['example_original']);
                        self::assertStringContainsString($source['example_target'], $task['example_target']); $demos++; continue;
                    }
                    self::assertSame($source['answer'], $task['answer']);
                    foreach ($source['accepted'] ?? [$source['answer']] as $alias) { self::assertContains($alias, $task['accepted']); }
                    $retained++;
                } else {
                    foreach (['old_task', 'old_group', 'old_group_index', 'old_block_source_index'] as $field) { self::assertNull($row[$field]); }
                    self::assertStringStartsWith('m86-', $task['id']); $new++;
                }
                self::assertTrue($row['graded']); $count++;
                if ($g === 'inputs') { $full++; }
            }
            $perOwner[] = $count;
        }
        self::assertSame([35, 20, 4, 16], [$retained, $new, $demos, $full]);
        self::assertSame([10, 11, 11, 11, 6, 6], $perOwner);
    }

    public function test_six_exact_type_four_banks_are_separate_from_own_inputs_and_from_neighbor_banks(): void
    {
        [, , $m, $c] = $this->release();
        foreach ($m['lessons'] as $i => $lesson) {
            $data = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
            self::assertSame('theory_links', $data['linked_practice']['source']);
            self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertSame([Package::BANKS[$i]], $data['linked_practice']['seeder_classes']);
            self::assertSame([Package::BANKS[$i]], $c['targets'][$i]['bank_seeders']);
            self::assertCount($i < 4 ? 3 : 2, $data['inputs']);
        }
        foreach (range(0, 5) as $owner) {
            [$b, $p, $m] = $this->release(); $slot = Package::PRACTICE_SOURCE_INDICES[$owner];
            $m['lessons'][$owner]['blocks'][$slot]['data']['linked_practice']['seeder_classes'] = [Package::BANKS[($owner + 1) % 6]];
            $this->refused(fn () => Package::assertEditorial($b, $p, $m), 'thematic bank');
        }
    }

    public function test_tables_h1_mistake_notes_and_related_navigation_are_supported_visible_data(): void
    {
        [$b, $p, $m] = $this->release(); $mistakes = 0; $tables = 0;
        foreach ($m['lessons'] as $i => $lesson) {
            preg_match('~^<p><strong>(.*?)</strong>~su', $lesson['subtitle_html'], $title);
            self::assertSame(Package::RENDERED_TITLES[$i], strip_tags($title[1] ?? ''));
            self::assertNotEmpty($lesson['point_detail_decisions']);
            foreach ($lesson['blocks'] as $block) {
                if ($block['type'] === 'comparison-table') {
                    foreach ($block['data']['rows'] as $row) { self::assertNotSame('', trim(strip_tags($row['en'])));
                        self::assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u', $row['ua']); $tables++; }
                }
                if ($block['type'] === 'mistakes-grid') {
                    foreach ($block['data']['items'] as $item) {
                        foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) { self::assertNotSame('', trim($item[$field])); }
                        self::assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u', $item['note']); $mistakes++;
                    }
                }
            }
            self::assertSame($b['targets'][$i]['before']['page']['title'], $p['targets'][$i]['after']['page']['title']);
        }
        self::assertSame(18, $mistakes); self::assertGreaterThan(0, $tables);
        self::assertSame(json_decode($b['targets'][2]['before']['page']['blocks'][8]['body'], true, flags: JSON_THROW_ON_ERROR),
            $m['lessons'][2]['blocks'][8]['data']);
    }

    public function test_missing_foreign_owners_categories_locales_and_titles_are_refused(): void
    {
        foreach (['missing', 'owner', 'category', 'locale', 'title', 'range'] as $kind) {
            [$b, $p, $m, $c] = $this->release();
            if ($kind === 'missing') { array_pop($m['lessons']); }
            elseif ($kind === 'owner') { $m['lessons'][2]['identity'] = Package::OWNERS[3]; }
            elseif ($kind === 'category') { $p['targets'][2]['page_binding']['page_category_id'] = 28; }
            elseif ($kind === 'locale') { $m['lessons'][2]['locale'] = 'en'; }
            elseif ($kind === 'title') { $m['lessons'][2]['preserved_h1'] = 'Questions'; }
            else { $m['lessons'][2]['technical_block_level'] = 'A1–A2'; }
            $this->refused(fn () => Package::validate($b, $p, $m, $c));
        }
    }

    public function test_retained_identity_absence_canonical_answer_and_exact_old_mapping_cannot_change(): void
    {
        foreach (['id', 'source_index', 'answer', 'old_task', 'old_position'] as $kind) {
            [$b, $p, $m] = $this->release(); $slot = Package::PRACTICE_SOURCE_INDICES[2];
            if (in_array($kind, ['id', 'source_index', 'answer'], true)) {
                $task = &$m['lessons'][2]['blocks'][$slot]['data']['inputs'][0];
                if ($kind === 'id') { $task['id'] = 'm86-invented-old-id'; }
                elseif ($kind === 'source_index') { $task['source_index'] = 1; }
                else { $task['answer'] = 'Does he play tennis?'; $task['accepted'][] = $task['answer']; }
                unset($task); $this->syncTask($m, 2, 'inputs', 0);
            } else {
                foreach ($m['editorial_review']['task_map'][Package::OWNERS[2]] as &$row) {
                    if ($row['after_group'] === 'inputs' && $row['after_group_index'] === 0) {
                        if ($kind === 'old_task') { $row['old_task']['answer'] = 'Changed before'; }
                        else { $row['old_group_index'] = 1; }
                    }
                }
                unset($row);
            }
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_retained_full_negative_aliases_cannot_be_dropped_from_input_or_rephrase(): void
    {
        foreach ([['inputs', 2], ['rephrase', 3]] as [$group, $index]) {
            [$b, $p, $m] = $this->release(); $task = &$m['lessons'][3]['blocks'][6]['data'][$group][$index];
            self::assertContains('She does not usually drink tea.', $task['accepted']);
            $task['accepted'] = array_values(array_filter($task['accepted'], static fn ($v) => $v !== 'She does not usually drink tea.'));
            unset($task); $this->syncTask($m, 3, $group, $index);
            $this->refused(fn () => Package::assertEditorial($b, $p, $m), 'retained accepted alias');
        }
    }

    public function test_demonstration_loss_replacement_or_conversion_to_a_graded_control_is_refused(): void
    {
        foreach (['lost_target', 'graded', 'removed'] as $kind) {
            [$b, $p, $m] = $this->release();
            if ($kind === 'removed') { array_shift($m['lessons'][2]['blocks'][6]['data']['rephrase']); }
            else {
                $task = &$m['lessons'][2]['blocks'][6]['data']['rephrase'][0];
                if ($kind === 'lost_target') { $task['example_target'] = 'Unrelated demonstration'; }
                else { $task['answer'] = 'Does she like coffee?'; $task['accepted'] = [$task['answer']]; }
                unset($task); $this->syncTask($m, 2, 'rephrase', 0);
                if ($kind === 'graded') {
                    foreach ($m['editorial_review']['task_map'][Package::OWNERS[2]] as &$row) {
                        if ($row['after_group'] === 'rephrase' && $row['after_group_index'] === 0) { $row['graded'] = true; }
                    }
                    unset($row);
                }
            }
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_invisible_criteria_option_collisions_and_technical_feedback_are_refused_with_coherent_ledger(): void
    {
        foreach (['select_hidden', 'input_hidden', 'rephrase_hidden', 'collision', 'html', 'marker', 'entity'] as $kind) {
            [$b, $p, $m] = $this->release();
            $group = $kind === 'select_hidden' || $kind === 'collision' ? 'selects'
                : ($kind === 'rephrase_hidden' ? 'rephrase' : 'inputs');
            $index = $group === 'rephrase' ? 1 : 0; $task = &$m['lessons'][2]['blocks'][6]['data'][$group][$index];
            if ($kind === 'select_hidden') { $task['label'] = 'a) Do you like coffee? b) You do like coffee?'; $task['prompt'] = 'Критерій лише в невидимому полі.'; }
            elseif ($kind === 'input_hidden') { $task['after'] = ''; $task['context'] = 'Умова лише в невидимому полі.'; }
            elseif ($kind === 'rephrase_hidden') { $task['original'] = 'Does he works here?'; $task['prompt'] = 'Умова лише в невидимому полі.'; }
            elseif ($kind === 'collision') { $task['options'] = ['a', 'A']; }
            else { $task['feedback'][mb_strtolower($task['answer'])] = ['html' => '<b>Правильно</b>', 'marker' => 'Правильно {a1}', 'entity' => 'Правильно &nbsp;'][$kind]; }
            unset($task); $this->syncTask($m, 2, $group, $index);
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_token_banks_cannot_omit_do_or_does_duplicate_words_exceed_group_size_or_be_presolved(): void
    {
        foreach (['missing_do', 'missing_does', 'duplicate', 'large_group', 'ordered'] as $kind) {
            [$b, $p, $m] = $this->release(); $index = $kind === 'missing_does' ? 1 : 0;
            $task = &$m['lessons'][2]['blocks'][6]['data']['inputs'][$index];
            if ($kind === 'missing_do') { $task['before'] = 'tennis? / you / play'; }
            elseif ($kind === 'missing_does') { $task['before'] = 'work / here? / she'; }
            elseif ($kind === 'duplicate') { $task['before'] .= ' / Do'; }
            elseif ($kind === 'large_group') { $task['before'] = 'Do you play tennis? / duplicate'; }
            else { $task['before'] = 'Do / you / play / tennis?'; }
            unset($task); $this->syncTask($m, 2, 'inputs', $index);
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_missing_supported_note_or_related_footer_and_omitted_ledger_control_are_refused(): void
    {
        foreach (['note', 'footer', 'ledger'] as $kind) {
            [$b, $p, $m] = $this->release();
            if ($kind === 'note') { unset($m['lessons'][4]['blocks'][4]['data']['items'][0]['note']); }
            elseif ($kind === 'footer') { $m['lessons'][2]['blocks'][8]['data']['items'] = []; }
            else { array_pop($m['editorial_review']['task_map'][Package::OWNERS[0]]); }
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }
}
