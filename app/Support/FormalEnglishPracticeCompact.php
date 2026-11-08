<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Finite concise teaching copy using the existing native and authored components only. */
final class FormalEnglishPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/formal-english-practice-compact.v1.json';
public const SOURCE_SHA='b253808682c6812f8c296e93791e793ccb081e9040e342727a26ef34518c2982';
    public const SUPPLEMENT=FormalEnglishPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];
    private static function copy():array{return FormalEnglishNativePlans::copy();}
    private static function authoredCopy():array{return FormalEnglishNativePlans::authoredCopy();}
    public static function compile():array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>FormalEnglishPracticeSupplement::SOURCE,'sha256'=>FormalEnglishPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(FormalEnglishPracticeSupplement::load()['targets']as$s){$t=['topic'=>$s['topic'],'binding'=>$s['binding'],'after_data_sha256'=>$s['after_data_sha256'],'projection_family'=>'formal-english-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase']as$kind){$t['items'][$kind]=[];foreach($s['after_data'][$kind]??[]as$i=>$item){$labels=null;if($kind==='inputs'){if($i<count($s['input_updates'])){$r=$s['input_updates'][$i];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$r['condition_uk']],['lang'=>'uk','text'=>$r['hint_uk']]];}else{$r=$s['added'][$i-count($s['input_updates'])];$gap=$r['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';$stimuli=[['lang'=>$gap?'en':'uk','text'=>$gap?str_replace('{a1}','____',$r['condition']):$r['condition']],['lang'=>'uk','text'=>$r['hint_uk']]];}}
                    else{$copy=self::copy()[$s['topic']][$kind][$i]??null;if($copy===null)throw new RuntimeException('Formal English finite native copy missing.');$instruction=$copy[0];$stimuli=[];foreach($copy[1]as[$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];if(isset($copy[2])){$options=$item['options']??($kind==='choices'?($s['after_data']['choice_options']??['a','b']):($s['after_data']['options']??[]));if(count($options)!==count($copy[2]))throw new RuntimeException('Formal English native option cardinality differs.');$labels=$kind==='selects'?$copy[2]:array_combine($options,$copy[2]);}}
                    $r=['index'=>$i,'source_item_sha256'=>FormalEnglishPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>array_values(array_filter(self::FIELDS,fn($f)=>array_key_exists($f,$item)))];if($labels!==null)$r['option_labels']=$labels;$t['items'][$kind][]=$r;}
                if($s['mode']==='authored'||$kind==='rephrase')continue;
                $sets=$kind==='inputs'?[['indices'=>array_keys($s['before_data']['inputs']),'title'=>'Побудуй речення','intro'=>'Склади повні речення з перемішаних груп слів або введи вручну.'],['indices'=>[count($s['input_updates']),count($s['input_updates'])+1],'title'=>'Заповни пропуски','intro'=>'Уведи всю пропущену частину; наведені слова не повторюй.'],['indices'=>[count($s['input_updates'])+2,count($s['input_updates'])+3],'title'=>'Переклади речення','intro'=>'Переклади обидва речення англійською вручну або натисканням на групи слів.']]:[['indices'=>array_keys($s['after_data'][$kind]),'title'=>$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','intro'=>'Обери відповідь для кожної умови.']];
                foreach($sets as$set){if($set['indices']===[])continue;$chunks=count($set['indices'])===3?[$set['indices']]:array_chunk($set['indices'],2);foreach($chunks as$indices)$t['groups'][]=['key'=>'feq-native-'.$s['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$set['title'],'instruction'=>$set['intro']];}
            }
            if($s['mode']==='authored'){$prompts=self::authoredCopy()[$s['topic']];$prompts[]='<p>Уведи лише пропущену частину в обох реченнях.</p>';$prompts[]='<p>Переклади обидва речення англійською; вводь вручну або натискай на групи слів.</p>';$t['authored_cases']=[];foreach($s['after_data']['cases']as$i=>$case)$t['authored_cases'][]=['index'=>$i,'source_case_sha256'=>FormalEnglishPracticeSupplement::digest($case),'source_prompt_sha256'=>hash('sha256',$s['after_data']['author_self_check']['prompts'][$i]),'prompt_html'=>$prompts[$i]];}
            $doc['targets'][]=$t;
        }return$doc;
    }
    public static function validate(array$d):void{parent::validate($d);if($d!==self::compile())throw new RuntimeException('Formal English compact semantic copy differs.');$normal=$authored=0;foreach($d['targets']as$t){foreach($t['groups']as$g){$normal++;if(!in_array(count($g['indices']),[2,3],true))throw new RuntimeException('Formal English native card needs2tasks.');}$authored+=count($t['authored_cases']??[]);}if($normal!==23||$authored!==8)throw new RuntimeException('Formal English compact coverage differs.');}
    public static function nativeDisplay(?array$c,?array$o):?array{if(($c['projection_family']??null)!=='formal-english-practice-compact-v1')return$o;$d=$o??['selects'=>[],'choices'=>[],'answer_groups'=>[]];foreach(['selects','choices']as$kind)foreach($c['items'][$kind]as$r)if(isset($r['option_labels']))$d[$kind][$r['index']][$kind==='selects'?'options':'option_labels']=$r['option_labels'];return$d;}
    public static function authoredPresentation(object$b,array$d,array$a,bool$c):array
    {
        if(!$c||!FormalEnglishPracticeSupplement::isProjected($b,$d))return$a;
        try{foreach(self::load()['targets']as$t){if(($b->uuid??null)!==$t['binding']['uuid']||!isset($t['authored_cases']))continue;if(!hash_equals($t['after_data_sha256'],FormalEnglishPracticeSupplement::digest($d)))return$a;foreach($t['authored_cases']as$r){$i=$r['index'];if(!isset($a['presentation'][$i]))return$a;
                // Keep the already-rendered canonical heading and technical badge.
                // Only the finite exact-bound instruction body is replaced.
                $original=(string)$a['presentation'][$i]['prompt_html'];$heading=preg_match('~^\s*(<h4\b[^>]*>.*?</h4>)~su',$original,$match)===1?$match[0]:'';
                $a['presentation'][$i]['prompt_html']=$heading.$r['prompt_html'];}return$a;}}catch(Throwable){}return$a;
    }
}


