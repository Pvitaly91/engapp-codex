<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage as Digest;
use App\Support\M94RelativeClausesWhoseWhich as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Public frozen data only. No private candidate, application, HTTP or working database. */
class M94RelativeClausesWhoseWhichPackageTest extends TestCase
{
    private function release(): array
    {
        $r = Package::load(dirname(__DIR__, 2));
        Package::validate($r[0], $r[1], $r[2], $r[3]);
        return $r;
    }

    private function editorial(array $r): void
    {
        Package::assertEditorial($r[0], $r[1], $r[2], $r[6], $r[3]);
    }

    private function refused(callable $operation, string $prefix = 'M94 '): void
    {
        try { $operation(); }
        catch (RuntimeException $e) { self::assertStringStartsWith($prefix, $e->getMessage()); return; }
        self::fail('The independently changed finite contract must be refused.');
    }

    /** Keep dependent projections coherent so later semantic guards are actually reached. */
    private function reproject(array &$r): void
    {
        $r[1]['targets'][0]['after'] = Package::definition($r[2]['lessons'][0], $r[0]['targets'][0]['before']);
    }

    private function replaceTask(array &$r, string $group, int $index, array $task): void
    {
        $r[2]['lessons'][0]['blocks'][7]['data'][$group][$index] = $task;
        $offset = ['selects' => 0, 'choices' => 2, 'inputs' => 4][$group] + $index;
        $r[2]['editorial_review']['task_map'][Package::OWNERS[0]][$offset]['after_task'] = $task;
        $this->reproject($r);
    }

    public function test_one_owner_two_continuing_slots_and_six_appends_preserve_exact_legacy_metadata(): void
    {
        [$before, $patch, $master, , , $bindings] = $this->release();
        self::assertSame([284], Package::PAGE_IDS); self::assertSame([34], Package::CATEGORY_IDS);
        self::assertSame(Package::OWNERS, array_column($master['lessons'], 'identity'));
        $old = $before['targets'][0]['before']; $after = $patch['targets'][0]['after'];
        self::assertCount(2, $old['page']['blocks']); self::assertCount(8, $after['page']['blocks']);
        self::assertSame(['hero', 'box', 'forms-grid', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips', 'practice-set'], array_column($after['page']['blocks'], 'type'));
        self::assertSame(['header', 'left', 'left', 'left', 'right', 'right', 'footer', 'left'], array_column($after['page']['blocks'], 'column'));
        self::assertArrayNotHasKey('level', $old['page']['blocks'][1]);
        self::assertArrayNotHasKey('level', $after['page']['blocks'][1]);
        foreach ([0, 1] as $slot) {
            $a = $old['page']['blocks'][$slot]; $b = $after['page']['blocks'][$slot];
            self::assertSame(Package::CONTINUING_UUIDS[$slot], Digest::uuid(Package::OWNERS[0], $b, $slot + 1));
            unset($a['body'], $b['body']);
            if ($slot === 1) { self::assertSame('B2 focus', $a['heading']); self::assertSame(Package::BOX_HEADING, $b['heading']); $b['heading'] = $a['heading']; }
            self::assertSame($a, $b, 'Continuing absent keys are not equivalent to invented null fields.');
        }
        self::assertSame('B2', $bindings['bindings'][0]['practice_level']);
        self::assertArrayNotHasKey('existing_practice', $bindings['bindings'][0]);
        self::assertSame($old['page']['title'], $after['page']['title']);
        $protected = $after; $protected['page']['blocks'] = $old['page']['blocks'];
        foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
        self::assertSame($old, $protected);
    }

    public function test_literal_box_is_html_not_a_native_migration_and_hero_keeps_three_english_models(): void
    {
        [$before, $patch, $master] = $this->release(); $lesson = $master['lessons'][0];
        $box = $lesson['blocks'][1]; self::assertSame(['html'], array_keys($box['data']));
        self::assertSame($box['data']['html'], $patch['targets'][0]['after']['page']['blocks'][1]['body']);
        self::assertStringStartsWith('<p><strong>Whose + іменник</strong>', $box['data']['html']);
        self::assertStringNotContainsString('<h2', $box['data']['html']);
        self::assertStringNotContainsString('theory anchor', $box['data']['html']);
        $oldHero = json_decode($before['targets'][0]['before']['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
        foreach ($oldHero['rules'] as $i => $rule) {
            self::assertStringStartsWith($rule['example'].' — ', $lesson['blocks'][0]['data']['rules'][$i]['example']);
            self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $lesson['blocks'][0]['data']['rules'][$i]['example']);
        }
        self::assertSame(Package::SUBTITLE_TEXT_AFTER, $lesson['subtitle_text']);
        self::assertStringStartsWith('<p><strong>Relative Clauses with Whose and Which</strong>', $lesson['subtitle_html']);
    }

    public function test_six_new_task_rows_have_literal_after_copies_and_no_invented_old_identity(): void
    {
        [, , $master] = $this->release(); $data = $master['lessons'][0]['blocks'][7]['data'];
        $ledger = $master['editorial_review']['task_map'][Package::OWNERS[0]];
        self::assertCount(6, $ledger); $ids = []; $aliases = $options = 0;
        foreach (['selects' => ['grammar_form', 0], 'choices' => ['meaning', 2], 'inputs' => ['full_sentence', 4]] as $group => [$role, $offset]) {
            self::assertCount(2, $data[$group]);
            foreach ($data[$group] as $index => $task) {
                $row = $ledger[$offset + $index]; $ids[] = $task['id'];
                foreach (['old_task', 'old_group', 'old_group_index', 'old_block_source_index'] as $key) { self::assertArrayHasKey($key, $row); self::assertNull($row[$key]); }
                self::assertSame($task, $row['after_task']); self::assertSame($role, $row['assessed_role']);
                self::assertSame($group, $row['after_group']); self::assertSame($index, $row['after_group_index']);
                self::assertSame(7, $row['after_block_source_index']); self::assertTrue($row['graded']); self::assertSame('new', $row['classification']);
                self::assertSame($offset + $index, $task['source_index']); $aliases += count($task['accepted']);
                self::assertSame($task['answer'], $task['accepted'][0]); $options += count($task['options'] ?? []);
                foreach ($task['feedback'] as $key => $feedback) {
                    self::assertSame(mb_strtolower(trim($key)), $key);
                    self::assertDoesNotMatchRegularExpression('/<[^>]+>|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu', $feedback);
                    self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $feedback);
                }
            }
        }
        self::assertSame(Package::TASK_IDS, $ids); self::assertSame(7, $aliases); self::assertSame(12, $options);
        self::assertSame(['4'], $data['linked_practice']['question_types']); self::assertSame(Package::BANKS, $data['linked_practice']['seeder_classes']);
    }

    public function test_meaning_choices_explicitly_assess_defined_group_and_whole_situation_not_comma_typing(): void
    {
        [, , $master] = $this->release(); $choices = $master['lessons'][0]['blocks'][7]['data']['choices'];
        self::assertStringContainsString('шість', $choices[0]['label']);
        self::assertStringContainsString('Дві', $choices[0]['label']);
        self::assertStringContainsString('The boxes which were damaged were sent back.', $choices[0]['label']);
        self::assertStringContainsString('The boxes, which were damaged, were sent back.', $choices[0]['label']);
        self::assertStringContainsString('Sofia closed the warehouse early, which forced the couriers to wait outside.', $choices[1]['label']);
        self::assertStringContainsString('попередня ситуація', $choices[1]['answer']);
        foreach ($choices as $task) { self::assertArrayNotHasKey('punctuation_sensitive', $task); self::assertCount(3, array_unique(array_map(Package::comparisonKey(...), $task['options']))); }
    }

    public function test_full_sentence_banks_are_shuffled_composable_and_extra_bus_alias_is_manual_only(): void
    {
        [, , $master] = $this->release(); $inputs = $master['lessons'][0]['blocks'][7]['data']['inputs'];
        self::assertSame(['The studio whose owner we interviewed will open next month.', 'We missed the last bus, which delayed our arrival.'], array_column($inputs, 'answer'));
        foreach ($inputs as $task) {
            $bank = array_map('trim', explode('/', $task['before'])); self::assertNotSame($task['answer'], implode(' ', $bank));
            foreach ($bank as $token) { self::assertGreaterThanOrEqual(1, count(preg_split('/\s+/u', $token))); self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $token))); }
            self::assertSame(['manual', 'token'], $task['alias_modes']['canonical']);
            self::assertSame(strip_tags($task['after']), $task['after']);
            $tail = $task['answer'];
            while ($bank !== []) {
                $matched = false;
                foreach ($bank as $i => $token) { if ($tail === $token || str_starts_with($tail, $token.' ')) { $tail = $tail === $token ? '' : substr($tail, strlen($token) + 1); unset($bank[$i]); $matched = true; break; } }
                self::assertTrue($matched, 'Every canonical group must consume the next literal sentence prefix.');
            }
            self::assertSame('', $tail);
        }
        self::assertSame([$inputs[0]['answer']], $inputs[0]['accepted']);
        self::assertSame([$inputs[1]['answer'], Package::MANUAL_ONLY_ALIAS], $inputs[1]['accepted']);
        self::assertSame(['manual'], $inputs[1]['alias_modes'][Package::MANUAL_ONLY_ALIAS]);
    }

    public function test_four_bilingual_mistakes_and_one_semantic_detail_keep_complete_models_visible(): void
    {
        [, , $master] = $this->release(); $lesson = $master['lessons'][0];
        self::assertCount(4, $lesson['blocks'][4]['data']['items']);
        foreach ($lesson['blocks'][4]['data']['items'] as $item) { foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) { self::assertNotSame('', trim($item[$field])); self::assertSame(strip_tags($item[$field]), $item[$field]); } }
        self::assertCount(1, $lesson['details']); $detail = $lesson['details'][0];
        self::assertSame('/sections/0', $detail['owner_pointer']); self::assertSame('m94-whose-of-which', $detail['key']);
        self::assertSame(['The old gate, whose lock was broken, was replaced.', 'The old gate, the lock of which was broken, was replaced.'], array_column($detail['fragment']['data']['sections'][0]['examples'], 'en'));
        self::assertCount(4, $lesson['blocks'][3]['data']['sections']);
        foreach ($lesson['blocks'][3]['data']['sections'] as $section) { self::assertNotSame('', trim($section['description'])); foreach ($section['examples'] as $example) { self::assertArrayHasKey('ua', $example); self::assertNotSame('', $example['ua']); } }
    }

    public function test_foreign_owner_bank_locale_level_and_legacy_migration_bindings_are_refused(): void
    {
        $r = $this->release(); $binding = $r[5]['bindings'][0];
        foreach (['identity' => 'Foreign\\Owner', 'page_id' => 285, 'category_id' => 50, 'practice_level' => null,
            'display_level' => 'B1', 'ancestry' => ['relative-clauses'], 'local_url' => 'https://example.invalid/theory/foreign',
            'continuing_levels' => ['B2', 'B2'], 'existing_practice' => [], 'legacy_box_projection' => [], 'component_overrides' => []] as $key => $value) {
            $bad = $binding; $bad[$key] = $value; $this->refused(fn () => Package::assertBindings([$bad]));
        }
        $bad = $r; $bad[3]['targets'][0]['bank_seeders'] = ['Foreign\\Bank']; $this->refused(fn () => $this->editorial($bad));
        foreach (['locale' => 'en', 'type' => 'usage-panels', 'level' => 'B2'] as $key => $value) {
            $bad = $r;
            if ($key === 'locale') { $bad[2]['lessons'][0]['locale'] = $value; } else { $bad[2]['lessons'][0]['blocks'][1][$key] = $value; }
            if ($key === 'locale') {
                $this->reproject($bad); $this->refused(fn () => $this->editorial($bad));
            } else {
                // The unchanged native serializer refuses migration before editorial validation.
                $this->refused(fn () => $this->reproject($bad), 'M49 continuing block metadata differs.');
            }
        }
    }

    public function test_hidden_criteria_html_feedback_duplicate_options_and_marker_leaks_are_refused(): void
    {
        $r = $this->release(); $original = $r[2]['lessons'][0]['blocks'][7]['data']['selects'][0];
        foreach (['hidden' => '', 'marker' => 'Оберіть {a1}.', 'feedback' => '<p>Правильна відповідь.</p>', 'duplicate' => null, 'strict' => true] as $case => $value) {
            $bad = $r; $task = $original;
            if ($case === 'feedback') { $task['feedback'][mb_strtolower($task['answer'])] = $value; }
            elseif ($case === 'duplicate') { $task['options'][0] = $task['options'][1]; }
            elseif ($case === 'strict') { $task['punctuation_sensitive'] = true; }
            else { $task['label'] = $value; }
            $this->replaceTask($bad, 'selects', 0, $task); $this->refused(fn () => $this->editorial($bad));
        }
    }

    public function test_impossible_token_multiset_wrong_alias_modes_and_stale_ledger_are_refused(): void
    {
        $r = $this->release();
        foreach (['missing' => 'The studio / whose owner', 'ordered' => $r[2]['lessons'][0]['blocks'][7]['data']['inputs'][0]['answer']] as $bank) {
            $bad = $r; $task = $bad[2]['lessons'][0]['blocks'][7]['data']['inputs'][0]; $task['before'] = $bank;
            $this->replaceTask($bad, 'inputs', 0, $task); $this->refused(fn () => $this->editorial($bad));
        }
        $bad = $r; $task = $bad[2]['lessons'][0]['blocks'][7]['data']['inputs'][1]; $task['alias_modes'][Package::MANUAL_ONLY_ALIAS] = ['manual', 'token'];
        $this->replaceTask($bad, 'inputs', 1, $task); $this->refused(fn () => $this->editorial($bad));
        $bad = $r; $bad[2]['editorial_review']['task_map'][Package::OWNERS[0]][0]['old_task'] = ['id' => 'invented']; $this->refused(fn () => $this->editorial($bad));
    }

    public function test_reviewed_learning_freeze_refuses_unreviewed_semantics_without_claiming_a_general_grammar_parser(): void
    {
        $r = $this->release(); $bad = $r;
        $bad[2]['lessons'][0]['blocks'][3]['data']['sections'][0]['description'] = 'Whose вживається лише для людей і ніколи не позначає частини предмета.';
        $this->reproject($bad);
        $this->refused(fn () => Package::validate($bad[0], $bad[1], $bad[2], $bad[3]));
        $bad = $r; $bad[2]['lessons'][0]['blocks'][1]['data']['html'] = '<p class="m94-card">Нове оформлення.</p>';
        $this->reproject($bad); $this->refused(fn () => $this->editorial($bad));
    }
}
