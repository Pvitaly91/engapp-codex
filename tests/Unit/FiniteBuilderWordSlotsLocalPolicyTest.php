<?php

namespace Tests\Unit;

use App\Services\FiniteBuilderWordSlotsLocalGuard as Guard;
use App\Services\FiniteBuilderWordSlotsLocalPolicy as Policy;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure fixtures: never reads a live baseline, connects to a server or mutates a database. */
final class FiniteBuilderWordSlotsLocalPolicyTest extends TestCase
{
    private function question(): array
    {
        $hint='Побудуй речення про завершену дію.';
        $row=['id'=>1,'uuid'=>'fixture-builder','author_id'=>'A1-c01','type'=>4,'level'=>'A1','question'=>'Ми вже закінчили.',
            'translation_uk'=>'Ми вже закінчили.','source_text_uk'=>'Ми вже закінчили.','target_text'=>'We have finished.',
            'accepted_target_texts'=>['We have finished.'],'verb_hint'=>$hint,'builder_word_slots_version'=>1,'markers'=>[],
            'options'=>[],'options_by_marker'=>[]];
        foreach ([['We','They','He','She'],['have','has','had','are'],['finished','finish','finishing','finishes']] as $i=>$options) {
            $key='a'.($i+1);$row['markers'][$key]=['answer'=>$i===2?'finished.':$options[0],'verb_hint'=>$hint,'options'=>$options];
            $row['options_by_marker'][$key]=$options;$row['options']=array_merge($row['options'],$options);
        }
        return $row;
    }

    private function persisted(array $expected): array
    {
        $focus=['options'=>[],'relations'=>['question_answers'=>[],'verb_hints'=>[],'question_option_question'=>[],
            'question_variants'=>[['id'=>1,'question_id'=>7,'text'=>$expected['question']]],
            'question_hints'=>[['id'=>1,'question_id'=>7,'provider'=>'compose_prompt','locale'=>'uk','hint'=>$expected['question']]]]];
        $ids=[];foreach(array_unique(array_merge(Policy::options($expected),[$expected['verb_hint']])) as $value){
            $id=count($ids)+1;$ids[$value]=$id;$focus['options'][]=['id'=>$id,'option'=>$value];
        }
        foreach($expected['markers'] as $key=>$marker){
            $focus['relations']['question_answers'][]=['id'=>count($focus['relations']['question_answers'])+1,'question_id'=>7,'marker'=>$key,'option_id'=>$ids[$marker['answer']]];
            $focus['relations']['verb_hints'][]=['id'=>count($focus['relations']['verb_hints'])+1,'question_id'=>7,'marker'=>$key,'option_id'=>$ids[$marker['verb_hint']],'locale'=>''];
        }
        foreach(Policy::options($expected) as $value){$focus['relations']['question_option_question'][]=['id'=>count($focus['relations']['question_option_question'])+1,'question_id'=>7,'option_id'=>$ids[$value],'flag'=>null];}
        $focus['relations']['question_option_question'][]=['id'=>99,'question_id'=>7,'option_id'=>$ids[$expected['verb_hint']],'flag'=>1];
        return [$focus,['id'=>7,'question'=>$expected['question'],'options_by_marker'=>json_encode(Policy::optionsByMarker($expected))]];
    }

    public function test_exact_word_projection_is_allowed_and_content_changes_are_rejected(): void
    {
        $after=$this->question();$before=$after;$before['markers']=['a1'=>['answer'=>'We have','verb_hint'=>$after['verb_hint']],'a2'=>['answer'=>'finished.','verb_hint'=>$after['verb_hint']]];
        unset($before['builder_word_slots_version']);Policy::assertQuestion($before,$after);$this->addToAssertionCount(1);
        foreach(['target_text'=>'They have finished.','accepted_target_texts'=>['Other sentence.'],'uuid'=>'foreign','level'=>'B1','type'=>0] as $key=>$value){
            $wrong=$after;$wrong[$key]=$value;$rejected=false;try{Policy::assertQuestion($before,$wrong);}catch(RuntimeException){$rejected=true;}self::assertTrue($rejected,$key);
        }
        $after['markers']['a1']['options'][1]='They have';$this->expectException(RuntimeException::class);Policy::assertQuestion($before,$after);
    }

    public function test_only_manifest_bound_author_copy_can_change(): void
    {
        $old=['title'=>'Fixture','questions'=>[['id'=>'A1-c01','kind'=>'compose','question'=>'Так, ми закінчили. Використай доконаний час.','uk'=>'Так, ми закінчили.','hint_uk'=>'Передай завершення дії.','target'=>'Yes, we have.']]];
        $new=$old;$new['questions'][0]['question']='Так, ми закінчили.';$new['questions'][0]['hint_uk']='Дай коротку ствердну відповідь.';
        $edit=['question_before'=>$old['questions'][0]['question'],'question_after'=>$new['questions'][0]['question'],
            'uk_before'=>$old['questions'][0]['uk'],'uk_after'=>$new['questions'][0]['uk'],
            'hint_before'=>$old['questions'][0]['hint_uk'],'hint_after'=>$new['questions'][0]['hint_uk']];
        Policy::assertAuthor($old,$new,['A1-c01'=>$edit]);$this->addToAssertionCount(1);
        foreach(['target','kind'] as $field){$bad=$new;$bad['questions'][0][$field]='changed';$rejected=false;try{Policy::assertAuthor($old,$bad,['A1-c01'=>$edit]);}catch(RuntimeException){$rejected=true;}self::assertTrue($rejected);}
        $this->expectException(RuntimeException::class);Policy::assertAuthor($old,$new,[]);
    }

    public function test_live_contract_includes_exact_hint_pivot_and_excludes_foreign_relations(): void
    {
        $expected=$this->question();[$focus,$row]=$this->persisted($expected);self::assertTrue(Policy::matches($focus,$row,$expected));
        $mutations=[
            static function(&$f){array_pop($f['relations']['question_option_question']);},
            static function(&$f){$f['relations']['question_option_question'][0]['flag']=2;},
            static function(&$f){$f['relations']['verb_hints'][0]['locale']='en';},
            static function(&$f){$f['relations']['verb_hints'][0]['option_id']=1;},
            static function(&$f){$f['relations']['question_hints'][0]['locale']='en';},
            static function(&$f){$f['relations']['question_hints'][0]['provider']='foreign';},
            static function(&$f){$f['relations']['question_variants'][0]['text']='foreign';},
            static function(&$f){$f['relations']['question_answers'][]=$f['relations']['question_answers'][0];},
        ];
        foreach($mutations as $mutate){$wrong=$focus;$mutate($wrong);self::assertFalse(Policy::matches($wrong,$row,$expected));}
        $row['question'].=' extra';self::assertFalse(Policy::matches($focus,$row,$expected));
    }

    public function test_old_source_without_options_by_marker_has_the_exact_seeders_inferred_contract(): void
    {
        $expected=$this->question();unset($expected['options_by_marker']);[$focus,$row]=$this->persisted($expected);
        self::assertTrue(Policy::matches($focus,$row,$expected));$row['options_by_marker']=null;self::assertFalse(Policy::matches($focus,$row,$expected));
    }

    public function test_proof_is_only_local_loopback_get_and_mask_does_not_touch_unrelated_data(): void
    {
        $nonce=str_repeat('a',32);$url='http://gramlyze.loc/api/_local/finite-builder-word-slots-'.$nonce;
        $make=static function($url,$method='GET',$server=[]){$r=Request::create($url,$method,server:array_merge(['REMOTE_ADDR'=>'127.0.0.1'],$server));$r->server->set('REQUEST_METHOD',$method);return $r;};
        self::assertTrue(Guard::allowsProofRequest($make($url),$nonce,Guard::ROOT,Guard::ROOT.'/public'));
        foreach(['POST','HEAD','DELETE'] as $method){self::assertFalse(Guard::allowsProofRequest($make($url,$method),$nonce,Guard::ROOT,Guard::ROOT.'/public'));}
        foreach(['https://gramlyze.loc','http://gramlyze.ub','http://gramlyze.com','http://127.0.0.1'] as $host){self::assertFalse(Guard::allowsProofRequest($make(str_replace('http://gramlyze.loc',$host,$url)),$nonce,Guard::ROOT,Guard::ROOT.'/public'));}
        self::assertFalse(Guard::allowsProofRequest($make($url,'GET',['REMOTE_ADDR'=>'192.168.0.102']),$nonce,Guard::ROOT,Guard::ROOT.'/public'));
        $row=['question_id'=>7,'provider'=>'other','locale'=>'uk','hint'=>'kept'];self::assertSame($row,Guard::protectedRow('question_hints',$row,['7'=>true]));
        self::assertSame($row,Guard::protectedRow('user_progress',$row,['7'=>true]));
        self::assertSame($row,Guard::protectedRow('question_hints',$row,['8'=>true]));
        $row['provider']='compose_prompt';self::assertSame(array_replace($row,['hint'=>null]),Guard::protectedRow('question_hints',$row,['7'=>true]));
    }

    public function test_cli_writer_deduplicates_normalized_choices_even_when_option_ids_differ(): void
    {
        // Evaluate only the pure writer helper; never invoke the CLI/bootstrap or a database.
        $source=file_get_contents(dirname(__DIR__,2).'/tools/diagnostics/finite-builder-word-slots-local.php');
        $start=strpos($source,'function fbwsPivots(');$end=strpos($source,'function fbwsRetainedRelations(',$start);
        self::assertNotFalse($start);self::assertNotFalse($end);
        if(!function_exists('finiteBuilderPivotsFixture')){
            eval('use App\\Services\\FiniteBuilderWordSlotsLocalPolicy as Policy;'.str_replace('function fbwsPivots(',
                'function finiteBuilderPivotsFixture(',substr($source,$start,$end-$start)));
        }
        $rows=\finiteBuilderPivotsFixture(7,['We','we','go'],['We'=>11,'we'=>12,'go'=>13],'go');
        self::assertSame([
            ['question_id'=>7,'option_id'=>11,'flag'=>null],
            ['question_id'=>7,'option_id'=>13,'flag'=>null],
            ['question_id'=>7,'option_id'=>13,'flag'=>1],
        ],$rows,'The separately flagged hint is retained; case-equivalent normal choices are not duplicated.');
    }
}
