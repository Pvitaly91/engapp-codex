<?php

namespace Tests\Unit;

use App\Support\M26DetailPackage;
use App\Support\M84QuestionSupplement;
use App\Support\M84WordOrderHaveGot as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure frozen teaching-data contracts; no application bootstrap or working database. */
class M84WordOrderHaveGotPackageTest extends TestCase
{
    private function release(): array { return Package::load(dirname(__DIR__, 2)); }

    private function readArtifactJson(string $path): array
    {
        $file = dirname(__DIR__, 2).'/'.$path;
        self::assertFileExists($file); self::assertFalse(is_link($file));
        return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    }

    private function refused(callable $call): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $e) { $caught = $e; }
        self::assertInstanceOf(RuntimeException::class, $caught, 'Changed finite data must be refused, not silently accepted.');
    }

    /** Keep a negative fixture coherent so it reaches the intended semantic guard. */
    private function syncTaskLedger(array &$master, int $ownerIndex, string $group, int $index): void
    {
        $owner = Package::OWNERS[$ownerIndex];
        $task = $master['lessons'][$ownerIndex]['blocks'][Package::PRACTICE_SOURCE_INDICES[$ownerIndex]]['data'][$group][$index];
        $matches = 0;
        foreach ($master['editorial_review']['task_map'][$owner] as &$row) {
            if ($row['after_group'] === $group && $row['after_group_index'] === $index) { $row['after_task'] = $task; $matches++; }
        }
        unset($row); self::assertSame(1, $matches);
    }

    private function resyncProjection(array $before, array &$patch, array $master): void
    {
        foreach ($master['lessons'] as $i => $lesson) {
            $patch['targets'][$i]['after'] = Package::definition($lesson, $before['targets'][$i]['before']);
            $patch['targets'][$i]['block_keys'] = array_column($lesson['blocks'], 'key');
        }
    }

    public function test_exact_six_owners_frozen_sources_and_all_native_functions_validate(): void
    {
        [$b, $p, $m, $c] = $this->release(); Package::validate($b, $p, $m, $c);
        self::assertSame(Package::OWNERS, array_column($m['lessons'], 'identity'));
        self::assertSame([10, 10, 12, 10, 11, 6], array_map(static fn ($l) => count($l['blocks']), $m['lessons']));
        self::assertSame('1.0.0', $m['version']); self::assertSame(1, $c['schema_version']);
        self::assertSame(Package::BASE_SHA, $b['base_sha']); self::assertSame(Package::BASE_SHA, $p['base_sha']);
        foreach ($m['lessons'] as $i => $lesson) {
            self::assertSame('uk', $lesson['locale']);
            self::assertSame(Package::RENDERED_TITLES[$i], $lesson['preserved_h1']);
            self::assertSame($b['targets'][$i]['before']['page']['title'], $lesson['raw_title']);
            self::assertSame($lesson['raw_title'], $p['targets'][$i]['after']['page']['title']);
            self::assertMatchesRegularExpression('~^<p><strong>(.*?)</strong>~su', $lesson['subtitle_html']);
            preg_match('~^<p><strong>(.*?)</strong>~su', $lesson['subtitle_html'], $title);
            self::assertSame(Package::RENDERED_TITLES[$i], html_entity_decode(strip_tags($title[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            self::assertSame(Package::PAGE_IDS[$i], $p['targets'][$i]['page_binding']['id']);
            self::assertSame(Package::CATEGORY_IDS[$i], $p['targets'][$i]['page_binding']['page_category_id']);
            foreach ($b['targets'][$i]['before']['page']['blocks'] as $slot => $old) {
                $new = $p['targets'][$i]['after']['page']['blocks'][$slot];
                foreach (['type', 'column', 'level', 'heading', 'uuid', 'uuid_key', 'css_class'] as $field) {
                    self::assertSame(array_key_exists($field, $old), array_key_exists($field, $new), $field);
                    self::assertSame($old[$field] ?? null, $new[$field] ?? null, $field);
                }
                self::assertSame(M26DetailPackage::uuid(Package::OWNERS[$i], $old, $slot + 1), M26DetailPackage::uuid(Package::OWNERS[$i], $new, $slot + 1));
            }
        }
    }

    public function test_sql_null_display_ranges_and_editorial_focus_are_three_separate_contracts(): void
    {
        [,, $m, $c] = $this->release();
        self::assertSame([null, null, null, null, null, null], array_column($c['targets'], 'level'));
        self::assertSame(['A1–A2', 'A1–A2', 'A2–B1', 'A2–B1', 'B1–B2', 'A1'], array_column($m['lessons'], 'display_level'));
        self::assertSame(['A1', 'A1', 'A2', 'A2', 'B2', 'A1'], array_column($m['lessons'], 'intended_own_practice_level'));
        foreach ($m['lessons'] as $lesson) {
            self::assertArrayHasKey('technical_block_level', $lesson); self::assertNull($lesson['technical_block_level']);
            foreach ($lesson['blocks'] as $block) { self::assertArrayHasKey('level', $block); self::assertNull($block['level']); }
        }
    }

    public function test_40_retained_16_new_5_demo_and_17_full_inputs_are_counted_separately(): void
    {
        [$b,, $m] = $this->release(); $retained = $new = $demos = $full = 0; $oldSeen = $afterSeen = [];
        foreach ($m['lessons'] as $i => $lesson) {
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $practice = $lesson['blocks'][$slot]['data'];
            self::assertCount($i < 5 ? 3 : 2, $practice['selects']); self::assertCount(2, $practice['choices']);
            self::assertCount($i < 5 ? 3 : 2, $practice['inputs']); self::assertCount($i < 5 ? 3 : 0, $practice['rephrase'] ?? []);
            foreach ($m['editorial_review']['task_map'][Package::OWNERS[$i]] as $row) {
                $afterKey = $i.':'.$row['after_group'].':'.$row['after_group_index'];
                self::assertArrayNotHasKey($afterKey, $afterSeen); $afterSeen[$afterKey] = true;
                self::assertSame($practice[$row['after_group']][$row['after_group_index']], $row['after_task']);
                if ($row['old_task'] === null) { self::assertTrue($row['graded']); $new++; continue; }
                $oldKey = $i.':'.$row['old_group'].':'.$row['old_group_index'];
                self::assertArrayNotHasKey($oldKey, $oldSeen); $oldSeen[$oldKey] = true;
                $oldPractice = json_decode($b['targets'][$i]['before']['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR);
                self::assertSame($oldPractice[$row['old_group']][$row['old_group_index']], $row['old_task']);
                self::assertSame($row['old_group'], $row['after_group']); self::assertSame($row['old_group_index'], $row['after_group_index']);
                foreach (['id', 'source_index'] as $field) {
                    self::assertSame(array_key_exists($field, $row['old_task']), array_key_exists($field, $row['after_task']));
                    self::assertSame($row['old_task'][$field] ?? null, $row['after_task'][$field] ?? null);
                }
                if ($row['graded']) { $retained++; } else { $demos++; self::assertArrayNotHasKey('answer', $row['after_task']); }
            }
            foreach ($practice['inputs'] as $task) {
                $full++; self::assertStringContainsString('/', $task['before']); self::assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u', $task['after']);
                self::assertArrayNotHasKey('hint', $task); self::assertArrayNotHasKey('placeholder', $task);
                $tokens = array_map('trim', explode('/', $task['before'])); self::assertNotSame($task['answer'], implode(' ', $tokens));
                foreach ($tokens as $token) { self::assertNotSame('', $token); self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $token))); }
                $tail = $task['answer'];
                while ($tokens !== [] && $tail !== '') {
                    $found = false;
                    foreach ($tokens as $n => $token) {
                        if ($tail === $token || str_starts_with($tail, $token.' ')) { $tail = $tail === $token ? '' : substr($tail, strlen($token) + 1); unset($tokens[$n]); $found = true; break; }
                    }
                    self::assertTrue($found, 'The literal shuffled groups must assemble the entire canonical sentence.');
                }
                self::assertSame('', $tail); self::assertSame([], $tokens);
            }
        }
        self::assertSame([40, 16, 5, 17], [$retained, $new, $demos, $full]); self::assertCount(61, $afterSeen);
    }

    public function test_every_page_has_one_native_practice_and_one_exact_bank_declaration(): void
    {
        [,, $m, $c] = $this->release();
        foreach ($m['lessons'] as $i => $lesson) {
            $practices = array_values(array_filter($lesson['blocks'], static fn ($b) => $b['type'] === 'practice-set')); self::assertCount(1, $practices);
            $linked = $practices[0]['data']['linked_practice']; self::assertSame('theory_links', $linked['source']); self::assertSame(['4'], $linked['question_types']);
            self::assertSame($c['targets'][$i]['bank_seeders'], $linked['seeder_classes']);
            self::assertSame($i < 5 ? [Package::BANKS[$i], M84QuestionSupplement::BANKS[$i]] : [Package::BANKS[$i]], $linked['seeder_classes']);
            foreach (['title', 'select_title', 'choice_title', 'input_title', 'rephrase_title'] as $field) { self::assertStringNotContainsString('поліглот', mb_strtolower($practices[0]['data'][$field] ?? '')); }
        }
    }

    public function test_public_bindings_capture_five_existing_practices_and_only_one_new_have_got_practice(): void
    {
        $bindings = $this->readArtifactJson(Package::BINDINGS)['bindings']; self::assertCount(6, $bindings);
        foreach ($bindings as $i => $binding) {
            self::assertSame(Package::OWNERS[$i], $binding['identity']); self::assertNull($binding['practice_level']);
            if ($i < 5) { self::assertSame(['source_index' => Package::PRACTICE_SOURCE_INDICES[$i], 'uuid' => Package::EXISTING_PRACTICE_UUIDS[$i], 'level' => null], $binding['existing_practice']); self::assertSame([], $binding['appended_blocks']); }
            else { self::assertNull($binding['existing_practice'] ?? null); self::assertCount(1, $binding['appended_blocks']); self::assertSame('practice-set', $binding['appended_blocks'][0]['type']); self::assertNull($binding['appended_blocks'][0]['level']); }
        }
    }

    public function test_missing_owner_wrong_advanced_owner_category_locale_and_canonical_routes_are_rejected(): void
    {
        foreach (['missing', 'wrong-advanced-owner', 'category', 'locale', 'theory-prefix', 'parent-path'] as $kind) {
            [$b, $p, $m, $c] = $this->release();
            if ($kind === 'missing') { array_pop($m['lessons']); }
            elseif ($kind === 'wrong-advanced-owner') { $m['lessons'][4]['identity'] = 'Database\\Seeders\\Page_V3\\BasicGrammar\\AdvancedFrontingAndEmphasisTheorySeeder'; }
            elseif ($kind === 'category') { $p['targets'][4]['page_binding']['page_category_id'] = 2; }
            elseif ($kind === 'locale') { $m['lessons'][4]['locale'] = 'en'; }
            else { $p['targets'][4]['local_url'] = $kind === 'theory-prefix' ? 'http://gramlyze.loc/theory/basic-grammar/word-order/advanced-word-order-emphasis' : 'http://gramlyze.loc/theory/word-order/theory-advanced-word-order-emphasis'; }
            $this->refused(fn () => Package::validate($b, $p, $m, $c));
        }
    }

    public function test_display_range_cannot_replace_actual_null_level_or_raw_title(): void
    {
        foreach (['level', 'title'] as $kind) {
            [$b, $p, $m, $c] = $this->release();
            if ($kind === 'level') { $m['lessons'][2]['blocks'][10]['level'] = 'A2–B1'; $m['lessons'][2]['technical_block_level'] = 'A2–B1'; }
            else { $m['lessons'][4]['preserved_h1'] = 'Advanced Fronting and Emphasis'; }
            $this->refused(fn () => Package::validate($b, $p, $m, $c));
        }
    }

    public function test_old_control_or_demo_cannot_be_unmapped_and_practice_cannot_be_duplicated(): void
    {
        foreach (['ledger', 'demo', 'duplicate'] as $kind) {
            [$b, $p, $m, $c] = $this->release();
            if ($kind === 'ledger') { array_shift($m['editorial_review']['task_map'][Package::OWNERS[0]]); }
            elseif ($kind === 'demo') { array_shift($m['lessons'][4]['blocks'][9]['data']['rephrase']); }
            else { $m['lessons'][5]['blocks'][] = $m['lessons'][5]['blocks'][5]; }
            $this->refused(fn () => Package::validate($b, $p, $m, $c));
        }
    }

    public function test_invisible_input_condition_and_normalized_option_collision_are_rejected_with_coherent_ledger(): void
    {
        foreach (['condition', 'collision'] as $kind) {
            [$b, $p, $m] = $this->release(); $slot = Package::PRACTICE_SOURCE_INDICES[5];
            if ($kind === 'condition') { $m['lessons'][5]['blocks'][$slot]['data']['inputs'][0]['after'] = '   '; $this->syncTaskLedger($m, 5, 'inputs', 0); }
            else { $task = &$m['lessons'][5]['blocks'][$slot]['data']['selects'][0]; $task['options'][1] = strtoupper($task['options'][0]); unset($task); $this->syncTaskLedger($m, 5, 'selects', 0); }
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_raw_html_and_service_markers_in_feedback_are_rejected_with_coherent_ledger(): void
    {
        foreach (['<strong>Правильно</strong>', 'Правильно {a1}'] as $bad) {
            [$b, $p, $m] = $this->release(); $slot = Package::PRACTICE_SOURCE_INDICES[5];
            $task = &$m['lessons'][5]['blocks'][$slot]['data']['inputs'][0]; $task['feedback'][mb_strtolower(trim($task['answer']))] = $bad; unset($task);
            $this->syncTaskLedger($m, 5, 'inputs', 0); $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }

    public function test_foreign_bank_is_rejected_even_when_contract_and_native_payload_agree(): void
    {
        [$b, $p, $m, $c] = $this->release(); $foreign = ['Database\\Seeders\\V3\\Polyglot\\PolyglotHaveGotHasGotLessonSeeder'];
        $c['targets'][5]['bank_seeders'] = $foreign; $m['lessons'][5]['blocks'][5]['data']['linked_practice']['seeder_classes'] = $foreign;
        $this->resyncProjection($b, $p, $m); $this->refused(fn () => Package::validate($b, $p, $m, $c));
    }
}
