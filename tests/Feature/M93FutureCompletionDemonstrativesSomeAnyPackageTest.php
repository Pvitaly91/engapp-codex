<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage as Digest;
use App\Support\M93FutureCompletionDemonstrativesSomeAny as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Finite source-backed learning expectations and native guard negatives; no app/SQL/HTTP. */
class M93FutureCompletionDemonstrativesSomeAnyPackageTest extends TestCase
{
    private const FULL=[['How long will the children have been waiting outside by noon tomorrow?','Who will have been teaching English for two hours by ten tomorrow?'],['By noon tomorrow, we will have been painting the fence for three hours.','By 1 July, I will have been living here since 1 June.'],['Those are my keys.','I like these ones.'],['We can meet anywhere in this park.','There is nowhere to park near the station.'],['Some people in our group speak Spanish.','I do not know anyone here.'],['Some things in this bag are heavy.','I would like something cold to drink.']];
    private const MEANING=[[['m93-fpc-questions-meaning-duration','Вона з’ясовує тривалість навчання, накопичену до завтрашнього полудня; саме питання ще не підтверджує виконання плану.'],['m93-fpc-questions-meaning-reply','No, she won\'t.']],[['m93-fpc-time-meaning-reference','Since 2020 — початок; by next June — майбутня точка. Десять років можна обчислити з конкретних дат умови, не з одного слова since.'],['m93-fpc-time-meaning-all-morning','Ранок розглянуто як період перевірки зі звичайними перервами; завершення всіх есе не встановлене.']],[['m93-demonstratives-meaning-said','До щойно висловленої пропозиції зустрітися після уроку.'],['m93-demonstratives-meaning-water','This може стояти з незлічуваною назвою water; йдеться про цю воду біля мовця.']],[['m93-places-meaning-free-choice','Вибрати будь-яке місце в цій кімнаті.'],['m93-places-meaning-near','Тут поблизу немає місця, де можна поїсти; про віддалені місця не сказано.']],[['m93-people-meaning-expectation','b'],['m93-people-meaning-here','a']],[['m93-things-meaning-partial','b'],['m93-things-meaning-free-choice','c']]];
    private function release():array{return Package::load(dirname(__DIR__,2));}
    private function refused(callable$call,string$why=''):void
    {
        $error=null;try{$call();}catch(RuntimeException$e){$error=$e;}
        self::assertInstanceOf(RuntimeException::class,$error,$why);
    }
    private static function practice(array$m,int$i):array{return$m['lessons'][$i]['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];}
    private function syncTask(array&$m,int$i,string$group,int$n):void
    {
        $task=self::practice($m,$i)[$group][$n];$found=0;
        foreach($m['editorial_review']['task_map'][Package::OWNERS[$i]]as&$row){
            if($row['after_group']===$group&&$row['after_group_index']===$n){$row['after_task']=$task;++$found;}
        }unset($row);self::assertSame(1,$found);
    }
    public function test_forty_continuing_components_keep_exact_identity_and_five_new_practices_are_appended_only():void
    {
        [$b,$p,$m,$c]=$this->release();Package::validate($b,$p,$m,$c);
        self::assertSame([8,8,7,8,7,7],array_map(static fn($l)=>count($l['blocks']),$m['lessons']));
        $old=$new=0;
        foreach($m['lessons']as$i=>$lesson){
            $before=$b['targets'][$i]['before'];$after=$p['targets'][$i]['after'];
            self::assertSame(Package::definition($lesson,$before),$after);
            self::assertSame($before['page']['title'],$lesson['raw_title']);
            foreach($before['page']['blocks']as$n=>$block){
                ++$old;foreach(['type','column','heading','level','uuid','uuid_key','css_class']as$field){
                    self::assertSame(array_key_exists($field,$block),array_key_exists($field,$after['page']['blocks'][$n]));
                    self::assertSame($block[$field]??null,$after['page']['blocks'][$n][$field]??null);
                }
                self::assertSame(Digest::uuid(Package::OWNERS[$i],$block,$n+1),Digest::uuid(Package::OWNERS[$i],$after['page']['blocks'][$n],$n+1));
            }
            foreach(array_slice($after['page']['blocks'],count($before['page']['blocks']))as$block){
                ++$new;self::assertNotSame(3,$i);self::assertSame('practice-set',$block['type']);
                self::assertSame('left',$block['column']);self::assertSame(Package::LEVELS[$i],$block['level']);
                self::assertSame('m93:'.Package::SLUGS[$i].':'.Package::PRACTICE_KEYS[$i],$block['uuid_key']);
            }
        }
        self::assertSame([40,5],[$old,$new]);
    }
    public function test_mixed_technical_levels_subtitle_page_text_and_columns_are_preserved_not_globally_normalized():void
    {
        [$b,$p,$m,$c]=$this->release();
        self::assertSame([null,null,'A1',null,'A2','A2'],array_column($c['targets'],'level'));
        foreach($m['lessons']as$i=>$lesson){
            self::assertSame(Package::LEVELS[$i],$lesson['technical_block_level']);
            self::assertSame(Package::DISPLAY_LEVELS[$i],$lesson['display_level']);
            self::assertSame(Package::FOCUS_LEVELS[$i],$lesson['intended_own_practice_level']);
            foreach(Package::CONTINUING_LEVELS[$i]as$n=>$level){self::assertSame($level,$lesson['blocks'][$n]['level']);}
            foreach(['subtitle_html','subtitle_text']as$f){self::assertSame($b['targets'][$i]['before']['page'][$f],$lesson[$f]);}
            self::assertSame($b['targets'][$i]['before']['page']['subtitle_text'],$p['targets'][$i]['after']['page']['subtitle_text']);
        }
        self::assertSame('right',$m['lessons'][2]['blocks'][3]['column']);
        self::assertSame('right',$m['lessons'][2]['blocks'][4]['column']);
    }
    public function test_all_42_controls_and_one_demo_have_exact_old_new_ledgers_and_distinct_partial_full_roles():void
    {
        [$b,,$m]=$this->release();$retained=$new=$graded=$full=$demo=0;
        foreach($m['lessons']as$i=>$lesson){
            $data=self::practice($m,$i);$seen=[];$old=$i===3?json_decode($b['targets'][3]['before']['page']['blocks'][6]['body'],true,flags:JSON_THROW_ON_ERROR):[];
            self::assertSame($i===3?[3,2,5,3]:[2,2,2,0],array_map(static fn($g)=>count($data[$g]??[]),['selects','choices','inputs','rephrase']));
            foreach($m['editorial_review']['task_map'][Package::OWNERS[$i]]as$row){
                $g=$row['after_group'];$n=$row['after_group_index'];$task=$data[$g][$n];
                self::assertArrayNotHasKey($g.':'.$n,$seen);$seen[$g.':'.$n]=true;
                self::assertSame($task,$row['after_task']);self::assertSame(Package::PRACTICE_SOURCE_INDICES[$i],$row['after_block_source_index']);
                $isGraded=array_key_exists('answer',$task);self::assertSame($isGraded,$row['graded']);
                if(isset($old[$g][$n])){
                    self::assertSame($old[$g][$n],$row['old_task']);self::assertSame($g,$row['old_group']);self::assertSame($n,$row['old_group_index']);
                    foreach(['id','source_index']as$f){self::assertSame(array_key_exists($f,$old[$g][$n]),array_key_exists($f,$task));}
                    if(!$isGraded){++$demo;self::assertSame('demonstration',$row['assessed_role']);continue;}
                    self::assertSame($old[$g][$n]['answer'],$task['answer']);++$retained;
                }else{
                    foreach(['old_task','old_group','old_group_index','old_block_source_index']as$f){self::assertNull($row[$f]);}
                    self::assertStringStartsWith('m93-',$task['id']);++$new;
                }
                $isFull=$g==='inputs'&&in_array($n,Package::SENTENCE_INPUT_INDICES[$i],true);
                $role=$isFull?'full_sentence':($g==='choices'?'meaning':($g==='rephrase'?'rephrasing':'grammar_form'));
                self::assertSame($role,$row['assessed_role']);++$graded;if($isFull){++$full;}
            }
            self::assertCount($i===3?13:6,$seen);
        }
        self::assertSame([8,34,42,12,1],[$retained,$new,$graded,$full,$demo]);
    }
    public function test_places_retains_all_eight_keys_absent_task_ids_partial_gaps_two_manual_rephrases_and_demo():void
    {
        [$b,,$m]=$this->release();$old=json_decode($b['targets'][3]['before']['page']['blocks'][6]['body'],true,flags:JSON_THROW_ON_ERROR);$d=self::practice($m,3);$count=0;
        self::assertArrayNotHasKey('uuid_key',$b['targets'][3]['before']['page']['blocks'][6]);
        self::assertSame($old['options'],$d['options']);
        foreach(['selects','inputs','rephrase']as$g){foreach($old[$g]as$n=>$t){
            foreach(['id','source_index']as$f){self::assertArrayNotHasKey($f,$t);self::assertArrayNotHasKey($f,$d[$g][$n]);}
            if(isset($t['answer'])){self::assertSame($t['answer'],$d[$g][$n]['answer']);++$count;}
            else{self::assertArrayNotHasKey('answer',$d[$g][$n]);self::assertArrayNotHasKey('accepted',$d[$g][$n]);}
        }}
        foreach(range(0,2)as$n){self::assertSame($old['inputs'][$n]['before'],$d['inputs'][$n]['before']);self::assertStringNotContainsString('/',$d['inputs'][$n]['before']);}
        self::assertSame('everywhere',$d['inputs'][2]['answer']);self::assertStringContainsString('всі міста',$d['inputs'][2]['prompt']);
        self::assertSame("I don't go anywhere on Friday evenings.",$d['rephrase'][1]['answer']);
        self::assertContains('I do not go anywhere on Friday evenings.',$d['rephrase'][1]['accepted']);
        self::assertSame('She goes nowhere without her phone.',$d['rephrase'][2]['answer']);
        self::assertSame(8,$count);
    }
    public function test_paired_mistakes_have_visible_ukrainian_notes_and_tables_have_actual_translations():void
    {
        [,,$m]=$this->release();$notes=$rows=0;
        foreach($m['lessons']as$l){foreach($l['blocks']as$b){
            if($b['type']==='mistakes-grid'){foreach($b['data']['items']as$t){
                foreach(['wrong_en','wrong_ua','right_en','right_ua','note']as$f){self::assertNotSame('',trim($t[$f]));self::assertDoesNotMatchRegularExpression('/<[^>]+>|\{a[0-9]+\}/u',$t[$f]);}
                foreach(['wrong_ua','right_ua','note']as$f){self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u',$t[$f]);}++$notes;
            }}
            if($b['type']==='comparison-table'){foreach($b['data']['rows']as$t){self::assertNotSame('',trim(strip_tags($t['en'])));self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u',$t['ua']);++$rows;}}
        }}
        self::assertSame([18,11],[$notes,$rows]);
    }
    public function test_six_widgets_bind_exact_main_type_four_banks_and_22_footer_labels_keep_source_order():void
    {
        [$b,,$m,$c]=$this->release();$links=0;
        foreach($m['lessons']as$i=>$l){
            $d=self::practice($m,$i);self::assertSame(['4'],$d['linked_practice']['question_types']);self::assertSame([Package::BANKS[$i]],$d['linked_practice']['seeder_classes']);
            self::assertSame([Package::BANKS[$i]],$c['targets'][$i]['bank_seeders']);
            self::assertStringContainsString($i===3?'Вправа 5.':'Вправа 4.',$d['linked_practice']['title']);
            $n=Package::NAVIGATION_SOURCE_INDICES[$i];$old=json_decode($b['targets'][$i]['before']['page']['blocks'][$n]['body'],true,flags:JSON_THROW_ON_ERROR);
            $items=$l['blocks'][$n]['data']['items'];$links+=count($items);
            $labels=static fn($xs)=>array_map(static fn($x)=>array_intersect_key($x,array_flip(['label','current'])),$xs);
            self::assertSame($labels($old['items']),$labels($items));
        }
        self::assertSame(22,$links);
    }
    public function test_retained_places_ids_canonicals_aliases_demo_and_source_ledger_cannot_change_coherently():void
    {
        foreach(['id','source_index','answer','alias','old-task','demo-scored']as$case){
            [$b,$p,$m]=$this->release();$t=&$m['lessons'][3]['blocks'][6]['data']['inputs'][0];
            if($case==='id'){$t['id']='m93-invented-old';}elseif($case==='source_index'){$t['source_index']=0;}
            elseif($case==='answer'){$t['answer']='somewhere';$t['accepted'][]='somewhere';}elseif($case==='alias'){$t['accepted']=[];}unset($t);
            $this->syncTask($m,3,'inputs',0);
            if($case==='old-task'){$m['editorial_review']['task_map'][Package::OWNERS[3]][0]['old_task']['answer']='changed';}
            if($case==='demo-scored'){$m['lessons'][3]['blocks'][6]['data']['rephrase'][0]['answer']='I go nowhere on Saturdays.';$this->syncTask($m,3,'rephrase',0);}
            $this->refused(fn()=>Package::assertEditorial($b,$p,$m),$case);
        }
    }
    public function test_hidden_criteria_option_collisions_and_raw_feedback_are_refused():void
    {
        foreach(['select-hidden','full-hidden','partial-hidden','rephrase-hidden','ambiguous-criterion','collision','html','marker','entity']as$case){
            [$b,$p,$m,$c]=$this->release();$i=in_array($case,['partial-hidden','rephrase-hidden','ambiguous-criterion'],true)?3:0;
            $g=in_array($case,['select-hidden','ambiguous-criterion'],true)?'selects':($case==='collision'?'choices':($case==='rephrase-hidden'?'rephrase':'inputs'));
            $n=in_array($case,['rephrase-hidden','ambiguous-criterion'],true)?1:0;$slot=Package::PRACTICE_SOURCE_INDICES[$i];$t=&$m['lessons'][$i]['blocks'][$slot]['data'][$g][$n];
            if($case==='select-hidden'){$t['label']='English only';$t['prompt']='Прихована умова.';}
            elseif($case==='full-hidden'){$t['after']='';$t['context']='Прихована умова.';}
            elseif($case==='partial-hidden'){$t['prompt']='';$t['context']='Прихована умова.';}
            elseif($case==='rephrase-hidden'){$t['original']='English only';}
            elseif($case==='ambiguous-criterion'){$t['label']='<p>Вона хоче жити біля моря.</p><p>She wants to live ___ near the sea.</p>';}
            elseif($case==='collision'){$t['options']=['a','A'];}
            else{$t['feedback'][mb_strtolower($t['answer'])]=['html'=>'<b>Правильно</b>','marker'=>'Правильно {a1}','entity'=>'Правильно &nbsp;'][$case];}
            unset($t);$this->syncTask($m,$i,$g,$n);
            if($case==='ambiguous-criterion'){$this->refused(fn()=>Package::validate($b,$p,$m,$c),'Exact frozen criterion/definition binding rejects an unreviewed ambiguous replacement; no general semantic classifier is claimed.');}
            else{$this->refused(fn()=>Package::assertEditorial($b,$p,$m),$case);}
        }
    }
    public function test_impossible_duplicate_oversized_or_already_ordered_full_token_banks_are_refused():void
    {
        foreach(['missing','duplicate','large','ordered']as$case){
            [$b,$p,$m]=$this->release();$t=&$m['lessons'][2]['blocks'][6]['data']['inputs'][0];
            $t['before']=match($case){'missing'=>'Those / my keys.','duplicate'=>'my keys. / Those / are / are','large'=>'Those are my keys.','ordered'=>'Those / are / my keys.'};
            unset($t);$this->syncTask($m,2,'inputs',0);$this->refused(fn()=>Package::assertEditorial($b,$p,$m),$case);
        }
    }
    public function test_missing_notes_ledger_or_changed_footer_labels_and_duplicate_practice_are_refused():void
    {
        foreach(['note','ledger','footer','duplicate-practice']as$case){
            [$b,$p,$m]=$this->release();
            if($case==='note'){unset($m['lessons'][2]['blocks'][3]['data']['items'][0]['note']);}
            elseif($case==='ledger'){array_pop($m['editorial_review']['task_map'][Package::OWNERS[3]]);}
            elseif($case==='footer'){$m['lessons'][3]['blocks'][7]['data']['items'][0]['label']='Changed';}
            else{$m['lessons'][3]['blocks'][]=$m['lessons'][3]['blocks'][6];}
            $this->refused(fn()=>Package::assertEditorial($b,$p,$m),$case);
        }
    }
    public function test_all_twelve_real_meaning_keys_and_twelve_full_canonicals_are_independently_expected():void
    {
        [,,$m]=$this->release();$meaning=$full=[];
        foreach($m['lessons']as$i=>$l){$d=self::practice($m,$i);$meaning[]=array_map(static fn($t)=>[$t['id'],$t['answer']],$d['choices']);
            $full[]=array_map(static fn($n)=>$d['inputs'][$n]['answer'],Package::SENTENCE_INPUT_INDICES[$i]);}
        self::assertSame(self::MEANING,$meaning);self::assertSame(self::FULL,$full);
    }
    public function test_visible_time_speaker_distance_number_negative_scope_and_any_some_criteria_are_concrete():void
    {
        [,,$m]=$this->release();$a=self::practice($m,0);$b=self::practice($m,1);$c=self::practice($m,2);$d=self::practice($m,3);$e=self::practice($m,4);$f=self::practice($m,5);
        self::assertStringContainsString('завтрашнього полудня',$a['choices'][0]['label']);
        foreach(['10:00','12:00','Назар','Олену','найкоротшу']as$s){self::assertStringContainsString($s,$a['choices'][1]['label']);}
        foreach(['15 січня 2030','1 червня 2030']as$s){self::assertStringContainsString($s,$b['choices'][0]['label']);}
        self::assertStringContainsString('перерв',$b['choices'][1]['label']);self::assertStringContainsString('завершення',$b['choices'][1]['answer']);
        self::assertStringContainsString('тримає',$c['choices'][1]['label']);self::assertStringContainsString('склянку',$c['choices'][1]['label']);
        self::assertStringContainsString('три ключі',$c['inputs'][0]['after']);self::assertStringContainsString('дві чашки',$c['inputs'][1]['after']);
        self::assertStringContainsString('стверджувальну модель із some-',$d['selects'][1]['label']);
        self::assertStringContainsString('запрошення',$d['selects'][2]['label']);self::assertStringContainsString('всі міста',$d['inputs'][2]['prompt']);
        self::assertStringContainsString('зайняті',$d['inputs'][1]['prompt']);self::assertStringContainsString('near',$d['choices'][1]['label']);
        self::assertStringContainsString('не очікує',$e['selects'][0]['label']);self::assertStringContainsString('куртка',$f['choices'][0]['label']);
        foreach($m['lessons']as$i=>$l){foreach(Package::SENTENCE_INPUT_INDICES[$i]as$n){self::assertDoesNotMatchRegularExpression('/<[^>]+>/u',self::practice($m,$i)['inputs'][$n]['after']);}}
    }
    public function test_missing_owner_wrong_bank_or_reclassification_of_partial_full_meaning_or_demo_is_refused():void
    {
        foreach(['missing-owner','foreign-bank','partial-full','meaning-form','demo-role']as$case){
            [$b,$p,$m]=$this->release();
            if($case==='missing-owner'){array_pop($m['lessons']);}
            elseif($case==='foreign-bank'){$m['lessons'][2]['blocks'][6]['data']['linked_practice']['seeder_classes']=[Package::BANKS[3]];}
            else{foreach($m['editorial_review']['task_map'][Package::OWNERS[3]]as&$r){
                if($case==='partial-full'&&$r['after_group']==='inputs'&&$r['after_group_index']===0){$r['assessed_role']='full_sentence';}
                if($case==='meaning-form'&&$r['after_group']==='choices'){$r['assessed_role']='grammar_form';}
                if($case==='demo-role'&&!$r['graded']){$r['assessed_role']='rephrasing';}
            }unset($r);}
            $this->refused(fn()=>Package::assertEditorial($b,$p,$m),$case);
        }
    }
}
