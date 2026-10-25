<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M88PastContinuousPresentPerfectBasics as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Frozen teaching/ledger contracts only; no environment, working database or HTTP bootstrap. */
class M88PastContinuousPresentPerfectBasicsPackageTest extends TestCase
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

    public function test_frozen_six_owner_projection_preserves_all_45_continuing_native_blocks_and_titles(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame([8, 8, 9, 8, 8, 8], array_map(static fn ($l) => count($l['blocks']), $master['lessons']));
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

    public function test_sql_null_display_ranges_focus_four_appends_and_two_retained_practices_are_distinct(): void
    {
        [, , $master, $contract] = $this->release();
        self::assertSame(array_fill(0, 6, null), array_column($contract['targets'], 'level'));
        self::assertSame(['A2', 'A2', 'A2', 'A2', 'A2–B1', 'A2–B1'], array_column($master['lessons'], 'display_level'));
        self::assertSame(array_fill(0, 6, 'A2'), array_column($master['lessons'], 'intended_own_practice_level'));
        foreach ($master['lessons'] as $i => $lesson) {
            self::assertArrayHasKey('technical_block_level', $lesson); self::assertNull($lesson['technical_block_level']);
            self::assertNull($lesson['subtitle_text']); self::assertSame([], $lesson['details'] ?? []);
            foreach ($lesson['blocks'] as $block) { self::assertArrayHasKey('level', $block); self::assertNull($block['level']); }
            $practice = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]];
            self::assertSame('practice-set', $practice['type']); self::assertSame('left', $practice['column']);
            if ($i >= 4) { self::assertSame(6, $practice['existing_source_index']); }
            else { self::assertNull($practice['existing_source_index'] ?? null); }
            foreach (['title', 'select_title', 'choice_title', 'input_title', 'rephrase_title'] as $field) {
                self::assertStringNotContainsString('поліглот', mb_strtolower($practice['data'][$field] ?? ''));
            }
        }
    }

    public function test_all_36_controls_have_literal_ledger_with_eight_old_answers_28_new_and_no_demos(): void
    {
        [$before, , $master] = $this->release(); $retained = $new = $full = $graded = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $data = $lesson['blocks'][$slot]['data'];
            self::assertSame([2, 2, 2, 0], array_map(static fn ($g) => count($data[$g] ?? []), ['selects', 'choices', 'inputs', 'rephrase']));
            $old = $i >= 4 ? json_decode($before['targets'][$i]['before']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR) : [];
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
                    self::assertStringStartsWith('m88-', $task['id']); ++$new;
                }
                ++$graded; if ($g === 'inputs') { ++$full; }
            }
            self::assertCount(6, $seen);
        }
        self::assertSame([8, 28, 36, 12], [$retained, $new, $graded, $full]);
    }

    public function test_original_accepted_only_case_is_preserved_not_misreported_as_a_broken_original(): void
    {
        [$before, , $master] = $this->release();
        $source = json_decode($before['targets'][5]['before']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR)['inputs'][0];
        $after = $master['lessons'][5]['blocks'][6]['data']['inputs'][0];
        self::assertArrayNotHasKey('answer', $source);
        self::assertSame(["He hasn't found his keys yet.", "He hasn't yet found his keys."], $source['accepted']);
        self::assertSame($source['accepted'][0], $after['answer']);
        self::assertSame($source['accepted'], array_slice($after['accepted'], 0, 2));
        foreach (["He has not found his keys yet.", "He has not yet found his keys."] as $alias) { self::assertContains($alias, $after['accepted']); }
        self::assertStringContainsString('обидві позиції yet', $after['after']);
        self::assertStringContainsString('Банк групує his keys yet.', $after['after']);
    }

    public function test_all_six_subtitle_h1s_19_paired_mistake_notes_and_table_translations_are_visible_data(): void
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
                        self::assertStringNotContainsString('class=', $item['right'] ?? ''); ++$mistakes;
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
        self::assertSame(19, $mistakes); self::assertGreaterThan(0, $tables);
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
            [$b, $p, $m] = $this->release(); $task = &$m['lessons'][5]['blocks'][6]['data']['inputs'][0];
            if ($kind === 'id') { $task['id'] = 'm88-invented-old-id'; }
            elseif ($kind === 'source_index') { $task['source_index'] = 1; }
            elseif ($kind === 'answer') { $task['answer'] = 'He has found his keys.'; $task['accepted'][] = $task['answer']; }
            elseif ($kind === 'alias') { $task['accepted'] = array_values(array_filter($task['accepted'], static fn ($v) => $v !== "He hasn't yet found his keys.")); }
            unset($task); $this->syncTask($m, 5, 'inputs', 0);
            if (in_array($kind, ['old_task', 'old_position'], true)) {
                foreach ($m['editorial_review']['task_map'][Package::OWNERS[5]] as &$row) {
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

    public function test_meaning_controls_have_independent_literal_keys_not_repeated_form_selection(): void
    {
        [, , $master] = $this->release();
        $expected = [
            [['m88-past-cont-forms-meaning-completion', 'На момент мого входу Тарас писав; чи він дописав або відправив листа, не сказано.'],
                ['m88-past-cont-forms-meaning-no-forced-stop', 'Малювання тривало в момент дзвінка; чи діти одразу припинили його, невідомо.']],
            [['m88-past-cont-negative-meaning-time-scope', 'Об одинадцятій Ніна не спала; чи спала вона в інший час, не сказано.'],
                ['m88-past-cont-negative-meaning-place-scope', 'У той момент вони не чекали надворі; очікування в іншому місці не встановлено й не виключено.']],
            [['m88-past-cont-questions-meaning-who-subject', 'Про виконавця дзвінка; за заданим фактом це Андрій.'],
                ['m88-past-cont-questions-meaning-not-assertion', 'Марта запитує про їхню роботу опівночі; відповідь ще невідома.']],
            [['m88-past-cont-time-meaning-overlap', 'Малювання й готування певний час відбувалися одночасно; їхні точні початок і кінець не задано.'],
                ['m88-past-cont-time-meaning-no-forced-stop', 'Телефон задзвонив під час читання; чи Назар припинив читати, не сказано.']],
            [['m88-present-perfect-forms-meaning-1', 'b'], ['m88-present-perfect-forms-meaning-2', 'a']],
            [['m88-present-perfect-negatives-meaning-1', 'c'], ['m88-present-perfect-negatives-meaning-2', 'a']],
        ];
        $actual = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $actual[] = array_map(static fn ($t) => [$t['id'], $t['answer']], $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data']['choices']);
        }
        self::assertSame($expected, $actual);
        $data = $master['lessons'][2]['blocks'][Package::PRACTICE_SOURCE_INDICES[2]]['data'];
        self::assertStringContainsString('Назара й Марти разом', $data['selects'][1]['label']);
        self::assertStringContainsString('жоден із них', $data['selects'][1]['label']);
        self::assertSame("No, we weren't.", $data['selects'][1]['answer']);
        $time = $master['lessons'][3]['blocks'][Package::PRACTICE_SOURCE_INDICES[3]]['data'];
        self::assertStringContainsString('сам по собі означає', $time['selects'][0]['label']);
        self::assertStringContainsString('не лише відсилає', $time['selects'][0]['label']);
    }

    public function test_lexical_have_had_contraction_object_and_short_residence_scopes_remain_explicit(): void
    {
        [, , $master] = $this->release(); $e = $master['lessons'][4]['blocks']; $f = $master['lessons'][5]['blocks'];
        self::assertStringContainsString('She has had two meetings today.', $e[0]['data']['rules'][0]['text']);
        self::assertStringContainsString("He's finished the report.", $e[1]['data']['items'][1]['subtitle']);
        self::assertSame('They haven\'t lived here for long.', $f[2]['data']['sections'][2]['examples'][1]['en']);
        self::assertSame('Вони живуть тут не так давно.', $f[2]['data']['sections'][2]['examples'][1]['ua']);
        self::assertStringContainsString('заперечується довга тривалість', $f[2]['data']['sections'][2]['description']);
        self::assertStringContainsString('Вони живуть тут не так давно.', $f[6]['data']['choices'][1]['label']);
    }
}
