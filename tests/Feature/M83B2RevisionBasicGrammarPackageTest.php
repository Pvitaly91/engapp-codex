<?php

namespace Tests\Feature;

use App\Support\M83B2RevisionBasicGrammar as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Finite author/data invariants, not a new grammar classifier or practice engine. */
class M83B2RevisionBasicGrammarPackageTest extends TestCase
{
    private function release():array { return Package::load(dirname(__DIR__,2)); }

    public function test_six_exact_uk_owners_preserve_actual_levels_and_all_native_functions():void
    {
        [$before,$patch,$master,$contract]=$this->release();Package::validate($before,$patch,$master,$contract);
        self::assertSame(Package::OWNERS,array_column($master['lessons'],'identity'));
        self::assertSame(['B2',null,null,null,null,null],array_column($contract['targets'],'level'));
        self::assertSame([6,15,13,12,13,13],array_map(static fn($l)=>count($l['blocks']),$master['lessons']));
        foreach($master['lessons']as $i=>$lesson){
            self::assertSame('uk',$lesson['locale']);self::assertSame(Package::RENDERED_TITLES[$i],$lesson['preserved_h1']);
            foreach($before['targets'][$i]['before']['page']['blocks']as $slot=>$old){
                $new=$patch['targets'][$i]['after']['page']['blocks'][$slot];
                foreach(['type','column','level','heading','uuid','uuid_key','css_class']as $field){
                    self::assertSame(array_key_exists($field,$old),array_key_exists($field,$new),$field);
                    self::assertSame($old[$field]??null,$new[$field]??null,$field);
                }
            }
        }
    }

    public function test_all_old_tasks_and_demos_have_exact_once_lineage():void
    {
        [$before,$patch,$master,$contract]=$this->release();Package::validate($before,$patch,$master,$contract);
        $retained=0;$new=0;$demos=0;
        foreach($master['editorial_review']['task_map']as $owner=>$rows){foreach($rows as $row){
            if($row['old_task']===null){$new++;}elseif($row['graded']){$retained++;}else{$demos++;}
        }}
        self::assertSame(42,$retained);self::assertSame(18,$new);self::assertSame(5,$demos);
    }

    public function test_both_word_by_word_analyses_and_the_ungraded_example_are_retained():void
    {
        [,, $master]=$this->release();$p=$master['lessons'][1]['blocks'][13]['data'];
        self::assertArrayNotHasKey('answer',$p['rephrase'][0]);
        self::assertCount(7,explode(' ',$p['rephrase'][1]['answer']));
        self::assertCount(10,explode(' ',$p['rephrase'][2]['answer']));
        self::assertStringContainsString('verb verb',$p['rephrase'][2]['answer']);
        self::assertCount(2,$p['rephrase'][1]['accepted']);self::assertCount(2,$p['rephrase'][2]['accepted']);
    }

    public function test_reducing_a_retained_analysis_to_a_noun_only_key_is_rejected():void
    {
        [$b,$p,$m]=$this->release();$m['lessons'][1]['blocks'][13]['data']['rephrase'][1]['answer']='noun';
        $this->expectException(RuntimeException::class);Package::assertEditorial($b,$p,$m);
    }

    public function test_missing_a_retained_task_or_demo_is_rejected():void
    {
        [$b,$p,$m]=$this->release();array_pop($m['lessons'][4]['blocks'][11]['data']['rephrase']);
        $this->expectException(RuntimeException::class);Package::assertEditorial($b,$p,$m);
    }

    public function test_changing_sql_null_to_editorial_a1_is_rejected():void
    {
        [$b,$p,$m]=$this->release();$m['lessons'][2]['blocks'][11]['level']='A1';
        $this->expectException(RuntimeException::class);Package::assertEditorial($b,$p,$m);
    }

    public function test_punctuation_only_repair_has_one_explicit_meaningful_control_transfer():void
    {
        [,, $m]=$this->release();$rows=$m['editorial_review']['task_map'][Package::OWNERS[5]];
        $transfers=array_values(array_filter($rows,static fn($r)=>$r['old_group']!==null&&$r['old_group']!==$r['after_group']));
        self::assertCount(1,$transfers);$t=$transfers[0];
        self::assertSame('rephrase',$t['old_group']);self::assertSame(2,$t['old_group_index']);
        self::assertSame('choices',$t['after_group']);self::assertSame(0,$t['after_group_index']);
        self::assertSame('m83-conjunctions-retained-comma',$t['after_task']['id']);
        self::assertSame(['A','B'],$t['after_task']['options']);
        self::assertSame('A',$t['after_task']['answer']);
    }

    public function test_same_source_position_cannot_gain_an_invented_identity():void
    {
        [$b,$p,$m]=$this->release();$m['lessons'][2]['blocks'][11]['data']['inputs'][0]['id']='m83-invented';
        $this->expectException(RuntimeException::class);Package::assertEditorial($b,$p,$m);
    }

    public function test_sixteen_full_inputs_and_three_short_inputs_have_separate_banks_and_visible_conditions():void
    {
        [,, $m]=$this->release();$full=0;$short=0;
        foreach($m['lessons']as $i=>$lesson){$p=$lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
            foreach($p['inputs']as $index=>$task){
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u',$task['after']);
                if(in_array($index,Package::SENTENCE_INPUT_INDICES[$i],true)){
                    $full++;self::assertStringContainsString('/',$task['before']);
                    foreach(explode('/',$task['before'])as $token){self::assertLessThanOrEqual(3,count(preg_split('/\s+/u',trim($token))));}
                }else{$short++;self::assertStringNotContainsString('/',$task['before']);}
            }
        }
        self::assertSame(16,$full);self::assertSame(3,$short);
    }

    public function test_raw_feedback_markup_or_a_leaking_service_marker_is_not_valid_learning_text():void
    {
        [$b,$p,$m]=$this->release();$task=&$m['lessons'][0]['blocks'][5]['data']['inputs'][0];
        $task['feedback'][mb_strtolower(trim($task['answer']))]='<strong>Правильно</strong> {a1}';
        $this->expectException(RuntimeException::class);Package::assertEditorial($b,$p,$m);
    }

    public function test_footer_keeps_every_label_and_current_flag_with_thirty_real_data_urls():void
    {
        [$b,, $m]=$this->release();$urls=0;
        foreach($m['lessons']as $i=>$lesson){$slot=Package::NAVIGATION_SOURCE_INDICES[$i];
            $old=json_decode($b['targets'][$i]['before']['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];
            $new=$lesson['blocks'][$slot]['data']['items'];self::assertCount(6,$new);
            foreach($new as $j=>$item){self::assertSame($old[$j]['label'],$item['label']);self::assertSame($old[$j]['current'],$item['current']);
                if(!$item['current']){$urls++;self::assertMatchesRegularExpression('~^/theory/[a-z0-9/-]+$~D',$item['url']);}
            }
        }
        self::assertSame(30,$urls);
    }
}
