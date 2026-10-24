<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M87ContinuousCompletionPastSimple as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Frozen content and finite negative contracts; no working database or HTTP. */
class M87ContinuousCompletionPastSimplePackageTest extends TestCase
{
    private function release(): array { return Package::load(dirname(__DIR__, 2)); }
    private function artifact(string $path): array
    {
        $file = dirname(__DIR__, 2).'/'.$path; self::assertFileExists($file); self::assertFalse(is_link($file));
        return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    }
    private function refused(callable $call, string $fragment = 'M87'): void
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
        self::assertSame([9, 8, 8, 8, 9, 8], array_map(static fn ($l) => count($l['blocks']), $m['lessons']));
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
        self::assertSame(['A1', 'A2', 'A1', 'A1', 'A1', 'A2'], array_column($m['lessons'], 'intended_own_practice_level'));
        foreach ($m['lessons'] as $i => $lesson) {
            self::assertArrayHasKey('technical_block_level', $lesson); self::assertNull($lesson['technical_block_level']);
            self::assertNull($lesson['subtitle_text']);
            $practice = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]];
            self::assertSame('practice-set', $practice['type']); self::assertSame('left', $practice['column']);
            self::assertSame($i >= 2 ? 6 : null, $practice['existing_source_index']);
            if ($i >= 2) { self::assertSame([], $bindings[$i]['appended_blocks']);
                self::assertSame(Package::EXISTING_PRACTICE_UUIDS[$i], $bindings[$i]['existing_practice']['uuid']); }
            else { self::assertNull($bindings[$i]['existing_practice'] ?? null); self::assertCount(1, $bindings[$i]['appended_blocks']); }
            foreach ($lesson['blocks'] as $block) { self::assertArrayHasKey('level', $block); self::assertNull($block['level']); }
            foreach (['title', 'select_title', 'input_title', 'rephrase_title', 'choice_title'] as $field) {
                self::assertStringNotContainsString('поліглот', mb_strtolower($practice['data'][$field] ?? ''));
            }
        }
    }

    public function test_exact_old_to_new_task_ledger_preserves_36_answers_all_aliases_and_four_ungraded_demos(): void
    {
        [$b, , $m] = $this->release(); $retained = $new = $demos = $full = 0; $perOwner = [];
        foreach ($m['lessons'] as $i => $lesson) {
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $data = $lesson['blocks'][$slot]['data']; $count = 0;
            $old = $i >= 2 ? json_decode($b['targets'][$i]['before']['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR) : [];
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
                    self::assertStringStartsWith('m87-', $task['id']); $new++;
                }
                self::assertTrue($row['graded']); $count++;
                if ($g === 'inputs') { $full++; }
            }
            $perOwner[] = $count;
        }
        self::assertSame([36, 21, 4, 16], [$retained, $new, $demos, $full]);
        self::assertSame([6, 6, 11, 11, 11, 12], $perOwner);
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
            self::assertCount($i >= 2 ? 3 : 2, $data['inputs']);
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
        foreach ([[0, 7], [4, 8]] as [$owner, $slot]) {
            self::assertSame(json_decode($b['targets'][$owner]['before']['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR),
                $m['lessons'][$owner]['blocks'][$slot]['data']);
        }
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
                if ($kind === 'id') { $task['id'] = 'm87-invented-old-id'; }
                elseif ($kind === 'source_index') { $task['source_index'] = 1; }
                else { $task['answer'] = 'He visited my aunt yesterday.'; $task['accepted'][] = $task['answer']; }
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
        foreach ([['inputs', 2, 'They did not watch the film.'], ['rephrase', 3, 'She did not call me yesterday.']] as [$group, $index, $alias]) {
            [$b, $p, $m] = $this->release(); $task = &$m['lessons'][3]['blocks'][6]['data'][$group][$index];
            self::assertContains($alias, $task['accepted']);
            $task['accepted'] = array_values(array_filter($task['accepted'], static fn ($v) => $v !== $alias));
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
                else { $task['answer'] = 'She watched TV last night.'; $task['accepted'] = [$task['answer']]; }
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
            if ($kind === 'select_hidden') { $task['label'] = 'a) He visited. b) He visit.'; $task['prompt'] = 'Критерій лише в невидимому полі.'; }
            elseif ($kind === 'input_hidden') { $task['after'] = ''; $task['context'] = 'Умова лише в невидимому полі.'; }
            elseif ($kind === 'rephrase_hidden') { $task['original'] = 'He go to bed late.'; $task['prompt'] = 'Умова лише в невидимому полі.'; }
            elseif ($kind === 'collision') { $task['options'] = ['a', 'A']; }
            else { $task['feedback'][mb_strtolower($task['answer'])] = ['html' => '<b>Правильно</b>', 'marker' => 'Правильно {a1}', 'entity' => 'Правильно &nbsp;'][$kind]; }
            unset($task); $this->syncTask($m, 2, $group, $index);
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_token_banks_cannot_omit_visited_went_had_negative_auxiliary_or_be_presolved(): void
    {
        foreach (['missing_visited', 'missing_went', 'missing_had', 'missing_didnt', 'duplicate', 'large_group', 'ordered'] as $kind) {
            [$b, $p, $m] = $this->release();
            $owner = $kind === 'missing_didnt' ? 3 : 2;
            $index = ['missing_went' => 1, 'missing_had' => 2][$kind] ?? 0;
            $task = &$m['lessons'][$owner]['blocks'][6]['data']['inputs'][$index];
            if ($kind === 'missing_visited') { $task['before'] = 'yesterday. / my aunt / I'; }
            elseif ($kind === 'missing_went') { $task['before'] = 'last night. / She / home'; }
            elseif ($kind === 'missing_had') { $task['before'] = 'dinner / at seven. / They'; }
            elseif ($kind === 'missing_didnt') { $task['before'] = 'Anna / yesterday. / see / I'; }
            elseif ($kind === 'duplicate') { $task['before'] .= ' / visited'; }
            elseif ($kind === 'large_group') { $task['before'] = 'I visited my aunt / yesterday.'; }
            else { $task['before'] = 'I / visited / my aunt / yesterday.'; }
            unset($task); $this->syncTask($m, $owner, 'inputs', $index);
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_missing_supported_note_or_related_footer_and_omitted_ledger_control_are_refused(): void
    {
        foreach (['note', 'footer', 'ledger'] as $kind) {
            [$b, $p, $m] = $this->release();
            if ($kind === 'note') { unset($m['lessons'][4]['blocks'][4]['data']['items'][0]['note']); }
            elseif ($kind === 'footer') { $m['lessons'][4]['blocks'][8]['data']['items'] = []; }
            else { array_pop($m['editorial_review']['task_map'][Package::OWNERS[0]]); }
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_named_meaning_controls_keep_reference_time_polarity_speaker_and_subject_roles(): void
    {
        [, , $master] = $this->release();
        $expected = [
            [
                ['m87-cont-questions-meaning-subject-who', 'Про людину, яка телефонує Максимові; за поданим фактом це Ніна.'],
                ['m87-cont-questions-meaning-question-not-fact', 'Оля запитує, чи вони сьогодні працюють; відповіді ще невідомо.'],
            ],
            [
                ['m87-cont-time-meaning-week-with-pauses', 'Репетиції належать до тижня, у якому відбувається розмова; поточна діяльність може мати паузи.'],
                ['m87-cont-time-meaning-temporary-repeat', 'Тимчасово повторювану дію в поточний період; every day тут не забороняє Continuous.'],
            ],
            [
                ['m87-past-simple-forms-meaning-past-state', 'a'],
                ['m87-past-simple-forms-meaning-past-repeat', 'b'],
            ],
            [
                ['m87-past-simple-negatives-meaning-finish', 'b'],
                ['m87-past-simple-negatives-meaning-early', 'a'],
            ],
            [
                ['m87-past-questions-meaning-who', 'a'],
                ['m87-past-questions-meaning-speaker', 'a'],
            ],
            [
                ['m87-past-time-meaning-ago-for', 'a'],
                ['m87-past-time-meaning-calendar-month', 'a'],
                ['m87-past-time-meaning-reference', 'a'],
            ],
        ];
        $actual = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $practice = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
            $actual[] = array_map(static fn ($task) => [$task['id'], $task['answer']], $practice['choices']);
        }
        self::assertSame($expected, $actual);
        $a = $master['lessons'][0]['blocks'][8]['data'];
        self::assertSame('Yes, I am.', $a['selects'][1]['answer']);
        self::assertStringContainsString('тільки до Назара', $a['selects'][1]['label']);
        self::assertStringContainsString('Назар справді читає', $a['selects'][1]['label']);
        self::assertStringContainsString('Who is calling Maksym?', $a['choices'][0]['label']);
        $b = $master['lessons'][1]['blocks'][7]['data'];
        foreach (['середа', 'перерва на обід'] as $fact) { self::assertStringContainsString($fact, $b['choices'][0]['label']); }
        foreach (['Зазвичай', 'Зараз сім\'я вдома', 'every day'] as $fact) { self::assertStringContainsString($fact, $b['choices'][1]['label']); }
        $e = $master['lessons'][4]['blocks'][6]['data'];
        foreach (['Who called Mark?', 'Who did Olia call?'] as $question) { self::assertStringContainsString($question, $e['choices'][0]['label']); }
        self::assertStringContainsString("No, I didn't. / No, I did not.", $e['choices'][1]['label']);
        $f = $master['lessons'][5]['blocks'][6]['data'];
        foreach (['14 травня 2024', '12 травня', 'for two days'] as $fact) { self::assertStringContainsString($fact, $f['choices'][0]['label']); }
        foreach (['6–12 травня 2024', 'травні 2023'] as $fact) { self::assertStringContainsString($fact, $f['choices'][1]['label']); }
        foreach (['14 травня 2020', '12 травня 2020', 'before / earlier'] as $fact) { self::assertStringContainsString($fact, $f['choices'][2]['label']); }
    }

    public function test_visible_past_lexical_be_emphatic_did_spelling_and_negation_scope_are_not_overgeneralized(): void
    {
        [, , $master] = $this->release();
        $c = $master['lessons'][2]['blocks']; $d = $master['lessons'][3]['blocks'];
        $intro = strip_tags($c[0]['data']['intro']);
        self::assertStringContainsString('worked і went обидві є V2', $intro);
        self::assertStringContainsString('окремі моделі was / were', $intro);
        $formTitles = array_column($c[1]['data']['items'], 'title');
        self::assertContains('study → studied / play → played', $formTitles);
        self::assertContains('stop → stopped / plan → planned / visit → visited', $formTitles);
        self::assertStringContainsString('не встановлює', $c[2]['data']['sections'][1]['description']);
        self::assertStringContainsString('повторення', $c[2]['data']['sections'][3]['description']);
        self::assertStringContainsString('також граматично можливе', $c[4]['data']['items'][1]['note']);
        self::assertStringContainsString('We did play football yesterday', $c[4]['data']['items'][1]['note']);
        self::assertStringContainsString('одну минулу ніч', $c[4]['data']['items'][2]['note']);
        self::assertSame('He went to bed late.', $c[4]['data']['items'][2]['right_en']);
        self::assertStringContainsString('минуле значення', strip_tags($d[0]['data']['intro']));
        self::assertContains("didn't + do", array_column($d[1]['data']['items'], 'title'));
        self::assertStringContainsString('чи починали', $d[2]['data']['sections'][0]['description']);
        self::assertStringContainsString('was / were', $d[2]['data']['sections'][3]['description']);
        self::assertStringContainsString('could', $d[2]['data']['sections'][3]['description']);
        foreach ([2, 3] as $i) {
            foreach ($master['lessons'][$i]['blocks'][3]['data']['rows'] as $row) {
                self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $row['ua']);
                self::assertNotSame(strip_tags($row['en']), strip_tags($row['ua']));
            }
        }
    }

}
