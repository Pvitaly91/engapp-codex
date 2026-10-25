<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M90PastPerfectFutureSimpleBasics as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Frozen teaching/ledger contracts only; no environment, working database or HTTP bootstrap. */
class M90PastPerfectFutureSimpleBasicsPackageTest extends TestCase
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

    public function test_frozen_six_owner_projection_preserves_all_42_continuing_native_blocks_and_titles(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame([7, 7, 7, 7, 7, 7], array_map(static fn ($l) => count($l['blocks']), $master['lessons']));
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
        self::assertSame(Package::DISPLAY_LEVELS, array_column($master['lessons'], 'display_level'));
        self::assertSame(Package::FOCUS_LEVELS, array_column($master['lessons'], 'intended_own_practice_level'));
        foreach ($master['lessons'] as $i => $lesson) {
            self::assertArrayHasKey('technical_block_level', $lesson); self::assertNull($lesson['technical_block_level']);
            self::assertNull($lesson['subtitle_text']); self::assertSame([], $lesson['details'] ?? []);
            foreach ($lesson['blocks'] as $block) { self::assertArrayHasKey('level', $block); self::assertNull($block['level']); }
            $practice = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]];
            self::assertSame('practice-set', $practice['type']); self::assertSame('left', $practice['column']);
            self::assertSame(Package::PRACTICE_SOURCE_INDICES[$i], $practice['existing_source_index']);
            foreach (['title', 'select_title', 'choice_title', 'input_title', 'rephrase_title'] as $field) {
                self::assertStringNotContainsString('поліглот', mb_strtolower($practice['data'][$field] ?? ''));
            }
        }
    }

    public function test_all_36_controls_have_literal_ledger_with_24_old_answers_12_new_and_no_demos(): void
    {
        [$before, , $master] = $this->release(); $retained = $new = $full = $graded = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $data = $lesson['blocks'][$slot]['data'];
            self::assertSame([2, 2, 2, 0], array_map(static fn ($g) => count($data[$g] ?? []), ['selects', 'choices', 'inputs', 'rephrase']));
            $old = json_decode($before['targets'][$i]['before']['page']['blocks'][5]['body'], true, flags: JSON_THROW_ON_ERROR);
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
                    self::assertStringStartsWith('m90-', $task['id']); ++$new;
                }
                ++$graded; if ($g === 'inputs') { ++$full; }
            }
            self::assertCount(6, $seen);
        }
        self::assertSame([24, 12, 36, 12], [$retained, $new, $graded, $full]);
    }


    public function test_all_24_retained_controls_keep_explicit_canonicals_and_all_26_original_answers(): void
    {
        [$before, , $master] = $this->release(); $controls=$answers=0;
        foreach($before['targets'] as $i=>$target){
            $old=json_decode($target['before']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['body'],true,flags:JSON_THROW_ON_ERROR);
            $after=$master['lessons'][$i]['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
            foreach(['selects','inputs'] as $group){foreach($old[$group] as $index=>$task){
                ++$controls;self::assertArrayHasKey('answer',$task);self::assertSame($task['answer'],$after[$group][$index]['answer']);
                $accepted=$task['accepted']??[$task['answer']];$answers+=count($accepted);
                if(array_key_exists('accepted',$task)){self::assertSame($accepted,$after[$group][$index]['accepted']);}
                foreach($accepted as $alias){self::assertContains($alias,$after[$group][$index]['accepted']);self::assertIsString($after[$group][$index]['feedback'][mb_strtolower($alias)]);}
                foreach(['id','source_index'] as $field){self::assertSame(array_key_exists($field,$task),array_key_exists($field,$after[$group][$index]));}
            }}
        }
        self::assertSame([24,26],[$controls,$answers]);
    }

    public function test_all_six_subtitle_h1s_six_paired_mistake_notes_and_table_translations_are_visible_data(): void
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
        self::assertSame(6, $mistakes); self::assertSame(21, $tables);
        foreach($before['targets'] as $i=>$target){
            $oldNav=json_decode($target['before']['page']['blocks'][Package::NAVIGATION_SOURCE_INDICES[$i]]['body'],true,flags:JSON_THROW_ON_ERROR);
            $actual=$master['lessons'][$i]['blocks'][Package::NAVIGATION_SOURCE_INDICES[$i]]['data']['items'];
            $labels=static fn($items)=>array_map(static fn($item)=>array_intersect_key($item,array_flip(['label','current'])),$items);
            self::assertSame($labels($oldNav['items']),$labels($actual),'Resolved hrefs must retain source labels/current/order.');
        }
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
        foreach(['id','source_index','answer','alias','old_task','old_position'] as $kind){
            [$b,$p,$m]=$this->release();$task=&$m['lessons'][5]['blocks'][5]['data']['inputs'][0];
            if($kind==='id'){$task['id']='m90-invented-old-id';}
            elseif($kind==='source_index'){$task['source_index']=1;}
            elseif($kind==='answer'){$task['answer']='She will be late.';$task['accepted'][]=$task['answer'];}
            elseif($kind==='alias'){array_pop($task['accepted']);}
            unset($task);$this->syncTask($m,5,'inputs',0);
            if(in_array($kind,['old_task','old_position'],true)){foreach($m['editorial_review']['task_map'][Package::OWNERS[5]] as &$row){
                if($row['after_group']==='inputs'&&$row['after_group_index']===0){if($kind==='old_task'){$row['old_task']['answer']='Changed before';}else{$row['old_group_index']=1;}}
            }unset($row);}
            $this->refused(fn()=>Package::assertEditorial($b,$p,$m));
        }
    }

    public function test_hidden_conditions_option_collisions_and_technical_feedback_are_refused(): void
    {
        foreach (['select_hidden', 'input_hidden', 'collision', 'html', 'marker', 'entity'] as $kind) {
            [$b, $p, $m] = $this->release(); $group = $kind === 'select_hidden' ? 'selects' : ($kind === 'collision' ? 'choices' : 'inputs');
            $index = $kind === 'collision' ? 1 : 0; $task = &$m['lessons'][4]['blocks'][5]['data'][$group][$index];
            if ($kind === 'select_hidden') { $task['label'] = 'a) She will call. / b) She will calls.'; $task['prompt'] = 'Лише окреме поле з умовою.'; }
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
            [$b, $p, $m] = $this->release(); $task = &$m['lessons'][4]['blocks'][5]['data']['inputs'][0];
            if ($kind === 'missing') { $task['before'] = 'tomorrow. / I / call you'; }
            elseif ($kind === 'duplicate') { $task['before'] .= ' / will'; }
            elseif ($kind === 'large') { $task['before'] = 'I will call you / tomorrow.'; }
            else { $task['before'] = 'I / will / call you / tomorrow.'; }
            unset($task); $this->syncTask($m, 4, 'inputs', 0);
            $this->refused(fn () => Package::assertEditorial($b, $p, $m));
        }
    }


    public function test_hint_only_mistakes_missing_ledger_and_changed_main_navigation_are_refused(): void
    {
        foreach(['hint_only','ledger','navigation'] as $kind){
            [$b,$p,$m]=$this->release();
            if($kind==='hint_only'){$item=&$m['lessons'][5]['blocks'][3]['data']['items'][0];$item['hint']=$item['note'];unset($item['note'],$item);}
            elseif($kind==='ledger'){array_pop($m['editorial_review']['task_map'][Package::OWNERS[0]]);}
            else{$m['lessons'][2]['blocks'][6]['data']['items'][0]['label']='Changed related topic';}
            $this->refused(fn()=>Package::assertEditorial($b,$p,$m));
        }
    }


    public function test_all_twelve_meaning_controls_are_new_and_all_retained_controls_keep_their_roles(): void
    {
        [$before,,$master]=$this->release();$actual=[];
        foreach($master['lessons'] as $i=>$lesson){
            $data=$lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
            $actual[]=array_map(static fn($task)=>[$task['id'],$task['answer']],$data['choices']);
            $old=json_decode($before['targets'][$i]['before']['page']['blocks'][5]['body'],true,flags:JSON_THROW_ON_ERROR);
            self::assertSame([],$old['choices']??[]);
            foreach($master['editorial_review']['task_map'][Package::OWNERS[$i]] as $row){
                $role=$row['after_group']==='inputs'?'full_sentence':($row['after_group']==='choices'?'meaning':'grammar_form');
                self::assertSame($role,$row['assessed_role']);
                if($row['after_group']==='choices'){self::assertNull($row['old_task']);}
                else{self::assertNotNull($row['old_task']);}
            }
        }
        self::assertSame(M90MeaningExpected::PAIRS,$actual);
    }


    public function test_visible_temporal_scope_speaker_fact_and_modal_meaning_criteria_remain_explicit(): void
    {
        [,,$m]=$this->release();$d=array_map(static fn($l)=>$l['blocks'][5]['data'],$m['lessons']);
        self::assertSame('The film had started before we arrived.',$d[0]['inputs'][0]['answer']);
        self::assertStringContainsString('до',$d[0]['inputs'][0]['after']);
        self::assertSame('Maya had completed the report before Friday’s meeting began.',$d[0]['inputs'][1]['answer']);
        self::assertStringContainsString('звіт',$d[0]['inputs'][1]['after']);
        self::assertStringContainsString('недостатньо',$d[1]['choices'][1]['label']);
        self::assertStringContainsString('початок',$d[1]['choices'][0]['prompt']);
        self::assertStringContainsString('who',$d[2]['choices'][0]['id']);
        self::assertStringContainsString('Оля одна',$d[2]['choices'][1]['label']);
        self::assertStringContainsString('не пізніше',$d[3]['choices'][0]['label']);
        self::assertStringContainsString('подальших подій',$d[3]['choices'][1]['label']);
        self::assertStringContainsString('не гарантує',$d[4]['choices'][0]['prompt']);
        self::assertStringContainsString('чи прийнято',$d[4]['choices'][1]['prompt']);
        self::assertStringContainsString('причина',$d[5]['choices'][0]['prompt']);
        self::assertStringContainsString('може допомогти',$d[5]['choices'][1]['label']);
        self::assertStringContainsString('каже «ні»',$d[5]['choices'][1]['label']);
    }


    public function test_retained_form_loss_reclassification_and_new_meaning_downgrade_fail_even_with_synced_task_copies(): void
    {
        foreach(['drop-old-select','old-form-as-meaning','new-meaning-as-form','missing-owner','foreign-owner'] as $case){
            [$b,$p,$m]=$this->release();
            if($case==='missing-owner'){array_pop($m['lessons']);}
            elseif($case==='foreign-owner'){$m['lessons'][2]['identity']='Foreign\\Owner';}
            elseif($case==='drop-old-select'){array_shift($m['lessons'][2]['blocks'][5]['data']['selects']);}
            else{foreach($m['editorial_review']['task_map'][Package::OWNERS[2]] as &$row){
                if($case==='old-form-as-meaning'&&$row['after_group']==='selects'&&$row['after_group_index']===0){$row['assessed_role']='meaning';}
                if($case==='new-meaning-as-form'&&$row['after_group']==='choices'&&$row['after_group_index']===0){$row['assessed_role']='grammar_form';}
            }unset($row);}
            $this->refused(fn()=>Package::assertEditorial($b,$p,$m));
        }
    }


}

final class M90MeaningExpected
{
    public const PAIRS = [[['m90-past-perfect-forms-meaning-1', 'a'], ['m90-past-perfect-forms-meaning-2', 'b']], [['m90-past-perfect-negatives-meaning-1', 'b'], ['m90-past-perfect-negatives-meaning-2', 'a']], [['m90-past-perfect-questions-meaning-who-subject', 'a'], ['m90-past-perfect-questions-meaning-one-speaker', 'b']], [['m90-past-perfect-time-meaning-by-deadline', 'a'], ['m90-past-perfect-time-meaning-until-reference', 'b']], [['m90-future-simple-forms-meaning-prediction', 'b'], ['m90-future-simple-forms-meaning-offer', 'a']], [['m90-future-simple-negatives-meaning-scope', 'b'], ['m90-future-simple-negatives-meaning-refusal', 'c']]];
}
