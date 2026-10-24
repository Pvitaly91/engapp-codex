<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M85VerbToBe as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Public frozen content contracts only; no Laravel bootstrap, working .env, SQL or HTTP. */
class M85VerbToBePackageTest extends TestCase
{
    private function release(): array { return Package::load(dirname(__DIR__, 2)); }
    private function readArtifactJson(string $path): array
    {
        $file=dirname(__DIR__,2).'/'.$path;self::assertFileExists($file);self::assertFalse(is_link($file));
        return json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
    }
    private function refused(callable $call): void
    {
        $caught=null;try{$call();}catch(RuntimeException $e){$caught=$e;}
        self::assertInstanceOf(RuntimeException::class,$caught,'A coherent changed fixture must reach and fail the intended finite guard.');
    }
    private function syncTaskLedger(array &$master,int $owner,string $group,int $index): void
    {
        $task=$master['lessons'][$owner]['blocks'][Package::PRACTICE_SOURCE_INDICES[$owner]]['data'][$group][$index];$found=0;
        foreach($master['editorial_review']['task_map'][Package::OWNERS[$owner]] as &$row){
            if($row['after_group']===$group&&$row['after_group_index']===$index){$row['after_task']=$task;++$found;}
        }unset($row);self::assertSame(1,$found);
    }
    private function resyncProjection(array $before,array &$patch,array $master): void
    {
        foreach($master['lessons'] as $i=>$lesson){
            $patch['targets'][$i]['after']=Package::definition($lesson,$before['targets'][$i]['before']);
            $patch['targets'][$i]['block_keys']=array_column($lesson['blocks'],'key');
        }
    }

    public function test_six_exact_native_owners_public_sources_and_all_continuing_metadata_validate(): void
    {
        [$b,$p,$m,$c]=$this->release();Package::validate($b,$p,$m,$c);
        self::assertSame(Package::OWNERS,array_column($m['lessons'],'identity'));self::assertSame('1.0.0',$m['version']);
        self::assertSame([8,8,9,9,9,8],array_map(static fn($l)=>count($l['blocks']),$m['lessons']));
        self::assertSame([28,26,29,27,25,24],Package::PAGE_IDS);self::assertSame([8,8,8,8,8,8],Package::CATEGORY_IDS);
        self::assertSame('6f7751612a3bc9d8bec2315c052e81f9e2fc0973',Package::BASE_SHA);
        foreach($m['lessons'] as $i=>$lesson){
            self::assertSame('uk',$lesson['locale']);self::assertSame(Package::RENDERED_TITLES[$i],$lesson['preserved_h1']);
            self::assertSame($b['targets'][$i]['before']['page']['title'],$lesson['raw_title']);
            self::assertSame($lesson['raw_title'],$p['targets'][$i]['after']['page']['title']);
            self::assertSame(Package::PAGE_IDS[$i],$p['targets'][$i]['page_binding']['id']);
            self::assertSame(Package::CATEGORY_IDS[$i],$p['targets'][$i]['page_binding']['page_category_id']);
            self::assertSame(['basic-grammar','verb-to-be'],$p['targets'][$i]['ancestry']);
            self::assertSame('http://gramlyze.loc/theory/basic-grammar/verb-to-be/'.Package::SLUGS[$i],$p['targets'][$i]['local_url']);
            foreach($b['targets'][$i]['before']['page']['blocks'] as $slot=>$old){
                $new=$p['targets'][$i]['after']['page']['blocks'][$slot];
                foreach(['type','column','level','heading','uuid','uuid_key','css_class'] as $field){
                    self::assertSame(array_key_exists($field,$old),array_key_exists($field,$new),$field);
                    self::assertSame($old[$field]??null,$new[$field]??null,$field);
                }
                self::assertSame(M26DetailPackage::uuid(Package::OWNERS[$i],$old,$slot+1),M26DetailPackage::uuid(Package::OWNERS[$i],$new,$slot+1));
            }
        }
    }
    public function test_actual_null_levels_display_ranges_and_editorial_focus_are_separate(): void
    {
        [,, $m,$c]=$this->release();self::assertSame(array_fill(0,6,null),array_column($c['targets'],'level'));
        self::assertSame(['A1','A1','A1','A1–A2','A1–A2','A1–A2'],array_column($m['lessons'],'display_level'));
        self::assertSame(['A1','A1','A1','A2','A2','A1'],array_column($m['lessons'],'intended_own_practice_level'));
        foreach($m['lessons'] as $lesson){self::assertArrayHasKey('technical_block_level',$lesson);self::assertNull($lesson['technical_block_level']);
            self::assertNull($lesson['subtitle_text']);foreach($lesson['blocks'] as $block){self::assertArrayHasKey('level',$block);self::assertNull($block['level']);}}
    }
    public function test_thirty_six_new_graded_controls_and_twelve_full_inputs_have_exact_literal_ledger(): void
    {
        [$b,,$m]=$this->release();$seen=[];$graded=$full=0;
        foreach($m['lessons'] as $i=>$lesson){
            $slot=Package::PRACTICE_SOURCE_INDICES[$i];$data=$lesson['blocks'][$slot]['data'];
            self::assertSame(Package::CONTINUING_COUNTS[$i],$slot);self::assertCount($slot,$b['targets'][$i]['before']['page']['blocks']);
            self::assertSame([2,2,2],[count($data['selects']),count($data['choices']),count($data['inputs'])]);self::assertSame([],$data['rephrase']??[]);
            $ledger=$m['editorial_review']['task_map'][Package::OWNERS[$i]];self::assertCount(6,$ledger);
            foreach($ledger as $row){
                self::assertNull($row['old_task']);self::assertNull($row['old_group']);self::assertNull($row['old_block_source_index']);
                self::assertTrue($row['graded']);self::assertSame($slot,$row['after_block_source_index']);
                $task=$data[$row['after_group']][$row['after_group_index']];self::assertSame($task,$row['after_task']);
                self::assertArrayNotHasKey($task['id'],$seen);$seen[$task['id']]=true;self::assertStringStartsWith('m85-',$task['id']);++$graded;
                if($row['after_group']!=='inputs'){continue;}++$full;
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u',$task['after']);
                self::assertArrayNotHasKey('hint',$task);self::assertArrayNotHasKey('placeholder',$task);
                $tokens=array_map('trim',explode('/',$task['before']));self::assertGreaterThanOrEqual(2,count($tokens));
                self::assertNotSame($task['answer'],implode(' ',$tokens));$tail=$task['answer'];
                foreach($tokens as $token){self::assertNotSame('',$token);self::assertLessThanOrEqual(3,count(preg_split('/\s+/u',$token)));}
                while($tokens!==[]&&$tail!==''){$found=false;foreach($tokens as $n=>$token){
                    if($tail===$token||str_starts_with($tail,$token.' ')){$tail=$tail===$token?'':substr($tail,strlen($token)+1);unset($tokens[$n]);$found=true;break;}}
                    self::assertTrue($found,'Every shuffled group belongs to the complete canonical sentence.');}
                self::assertSame('',$tail);self::assertSame([],$tokens);
            }
        }self::assertSame([36,12],[$graded,$full]);self::assertCount(36,$seen);
    }
    public function test_one_appended_left_practice_per_owner_and_six_distinct_exact_bank_declarations(): void
    {
        [,, $m,$c]=$this->release();$bindings=$this->readArtifactJson(Package::BINDINGS)['bindings'];self::assertCount(6,$bindings);
        foreach($m['lessons'] as $i=>$lesson){
            $practices=array_values(array_filter($lesson['blocks'],static fn($b)=>$b['type']==='practice-set'));self::assertCount(1,$practices);
            self::assertSame('left',$practices[0]['column']);self::assertNull($practices[0]['level']);$data=$practices[0]['data'];
            self::assertSame('theory_links',$data['linked_practice']['source']);self::assertSame(['4'],$data['linked_practice']['question_types']);
            self::assertSame([Package::BANKS[$i]],$data['linked_practice']['seeder_classes']);self::assertSame([Package::BANKS[$i]],$c['targets'][$i]['bank_seeders']);
            self::assertNull($bindings[$i]['existing_practice']??null);self::assertCount(1,$bindings[$i]['appended_blocks']);
            self::assertSame('practice-set',$bindings[$i]['appended_blocks'][0]['type']);self::assertNull($bindings[$i]['appended_blocks'][0]['level']);
            foreach(['title','select_title','choice_title','input_title'] as $field){self::assertStringNotContainsString('поліглот',mb_strtolower($data[$field]??''));}
        }self::assertCount(6,array_unique(Package::BANKS));
    }
    public function test_subtitle_null_strong_h1_all_tables_and_paired_correction_translations_are_visible_source_data(): void
    {
        [$b,,$m]=$this->release();$tableRows=$mistakes=0;
        foreach($m['lessons'] as $i=>$l){
            self::assertNull($l['subtitle_text']);
            if($i>=4){self::assertSame($b['targets'][$i]['before']['page']['subtitle_html'],$l['subtitle_html']);}
            else{self::assertNotSame($b['targets'][$i]['before']['page']['subtitle_html'],$l['subtitle_html'],'A–D have explicitly authored subtitle clarification, never a Page.text transition.');}
            preg_match('~^<p><strong>(.*?)</strong>~su',$l['subtitle_html'],$title);self::assertSame(Package::RENDERED_TITLES[$i],strip_tags($title[1]??''));
            self::assertSame([],$l['details']);self::assertNotEmpty($l['point_detail_decisions']);
            foreach($l['blocks'] as $block){
                if($block['type']==='comparison-table'){foreach($block['data']['rows'] as $row){++$tableRows;
                    self::assertNotSame('',trim(strip_tags($row['en'])));self::assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u',$row['ua']);
                    self::assertNotContains($row['ua'],['One thing','More than one thing','Question']);}}
                if($block['type']==='mistakes-grid'){foreach($block['data']['items'] as $item){++$mistakes;
                    foreach(['wrong_en','wrong_ua','right_en','right_ua'] as $field){self::assertNotSame('',trim($item[$field]??''));}
                    foreach(['wrong_ua','right_ua'] as $field){self::assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u',$item[$field]);}}}
            }
        }self::assertGreaterThan(0,$tableRows);self::assertSame(18,$mistakes);
    }
    public function test_missing_foreign_owner_category_locale_and_parent_url_are_refused(): void
    {
        foreach(['missing','owner','category','locale','path'] as $kind){[$b,$p,$m,$c]=$this->release();
            if($kind==='missing'){array_pop($m['lessons']);}
            elseif($kind==='owner'){$m['lessons'][4]['identity']='Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarHaveGotHasGotTheorySeeder';}
            elseif($kind==='category'){$p['targets'][4]['page_binding']['page_category_id']=2;}
            elseif($kind==='locale'){$m['lessons'][4]['locale']='en';}
            else{$p['targets'][4]['local_url']='http://gramlyze.loc/theory/verb-to-be/verb-to-be-future';}
            $this->refused(fn()=>Package::validate($b,$p,$m,$c));}
    }
    public function test_display_range_cannot_replace_sql_null_or_raw_title(): void
    {
        foreach(['level','title'] as $kind){[$b,$p,$m,$c]=$this->release();
            if($kind==='level'){$m['lessons'][4]['blocks'][8]['level']='A1–A2';$m['lessons'][4]['technical_block_level']='A1–A2';}
            else{$m['lessons'][4]['preserved_h1']='Future Simple';}
            $this->refused(fn()=>Package::validate($b,$p,$m,$c));}
    }
    public function test_unmapped_control_and_duplicate_practice_are_refused(): void
    {
        foreach(['ledger','duplicate'] as $kind){[$b,$p,$m,$c]=$this->release();
            if($kind==='ledger'){array_pop($m['editorial_review']['task_map'][Package::OWNERS[0]]);}
            else{$m['lessons'][0]['blocks'][]=$m['lessons'][0]['blocks'][7];}
            $this->refused(fn()=>Package::validate($b,$p,$m,$c));}
    }
    public function test_invisible_condition_normalized_collision_and_markup_feedback_are_refused_with_coherent_ledger(): void
    {
        foreach(['hidden','collision','html','marker','entity'] as $kind){[$b,$p,$m]=$this->release();$slot=Package::PRACTICE_SOURCE_INDICES[5];$group=$kind==='collision'?'selects':'inputs';
            $t=&$m['lessons'][5]['blocks'][$slot]['data'][$group][0];
            if($kind==='hidden'){$t['after']='   ';$t['context']='Критерій лише в невидимому полі.';}
            elseif($kind==='collision'){$t['options'][1]=strtoupper($t['options'][0]);}
            else{$t['feedback'][mb_strtolower($t['answer'])]=['html'=>'<b>Правильно</b>','marker'=>'Правильно {a1}','entity'=>'Правильно &nbsp;'][$kind];}
            unset($t);$this->syncTaskLedger($m,5,$group,0);$this->refused(fn()=>Package::assertEditorial($b,$p,$m));}
    }
    public function test_foreign_bank_is_refused_even_with_coherent_contract_and_projection(): void
    {
        [$b,$p,$m,$c]=$this->release();$foreign=['Database\\Seeders\\V3\\Polyglot\\PolyglotHaveGotHasGotAllLevelsLessonSeeder'];
        $c['targets'][5]['bank_seeders']=$foreign;$m['lessons'][5]['blocks'][7]['data']['linked_practice']['seeder_classes']=$foreign;
        $this->resyncProjection($b,$p,$m);$this->refused(fn()=>Package::validate($b,$p,$m,$c));
    }
    public function test_semantic_a_b_choice_cannot_gain_an_invalid_terminal_contraction_alias(): void
    {
        foreach([[4,'choices',1,"Yes, I'll."],[5,'selects',1,"Yes, there's."]] as [$owner,$group,$n,$bad]){
            [$b,$p,$m]=$this->release();$slot=Package::PRACTICE_SOURCE_INDICES[$owner];$t=&$m['lessons'][$owner]['blocks'][$slot]['data'][$group][$n];
            self::assertSame('А',$t['answer']);$t['accepted'][]=$bad;$t['feedback'][mb_strtolower($bad)]='Неприйнятний кінцевий позитивний варіант.';
            unset($t);$this->syncTaskLedger($m,$owner,$group,$n);$this->refused(fn()=>Package::assertEditorial($b,$p,$m));
        }
    }
}
