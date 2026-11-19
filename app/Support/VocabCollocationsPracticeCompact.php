<?php

namespace App\Support;

use RuntimeException;

/** Finite semantic copy; canonical components, stored values and grading remain unchanged. */
final class VocabCollocationsPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/vocabulary-collocations-practice-compact.v1.json';
    public const SOURCE_SHA='e4e5bc9ddbd21fc6fba62aa211109607ef6ba8c690284ff5333b317a06cf8ecd';
    public const SUPPLEMENT=VocabCollocationsPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];

    private static function copy():array{return VocabCollocationsNativePlans::copy();}
    public static function compile():array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>VocabCollocationsPracticeSupplement::SOURCE,'sha256'=>VocabCollocationsPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(VocabCollocationsPracticeSupplement::load()['targets'] as $source){
            $target=['topic'=>$source['topic'],'binding'=>$source['binding'],'after_data_sha256'=>$source['after_data_sha256'],'projection_family'=>'vocabulary-collocations-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase'] as $kind){$target['items'][$kind]=[];
                foreach($source['after_data'][$kind]??[] as $index=>$item){
                    $labels=null;
                    if($kind==='inputs'){
                        if($index<2){$row=$source['input_updates'][$index];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$row['condition_uk']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                        else{$row=$source['added'][$index-2];$gap=$row['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';$stimuli=[['lang'=>$gap?'en':'uk','text'=>$gap?str_replace('{a1}','____',$row['condition']):$row['condition']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                    }else{
                        $copy=self::copy()[$source['topic']][$kind][$index]??null;if($copy===null)throw new RuntimeException('Vocabulary and collocations finite native copy absent.');$instruction=$copy[0];$stimuli=[];foreach($copy[1] as [$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];
                        if(isset($copy[2])){$options=$item['options']??($kind==='selects'?($source['after_data']['options']??[]):($source['after_data']['choice_options']??['a','b']));if(count($options)!==count($copy[2]))throw new RuntimeException('Vocabulary and collocations option cardinality differs.');$labels=$kind==='selects'?$copy[2]:array_combine($options,$copy[2]);}
                    }
                    $row=['index'=>$index,'source_item_sha256'=>VocabCollocationsPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>array_values(array_filter(self::FIELDS,static fn($f)=>array_key_exists($f,$item)))];if($labels!==null)$row['option_labels']=$labels;$target['items'][$kind][]=$row;
                }
                $groups=$kind==='inputs'?[[[0,1],'Побудуй речення','Введи речення вручну або натискай на перемішані групи слів.'],[[2,3],'Заповни пропуски','Уведи лише пропущену частину; наведені слова не повторюй.'],[[4,5],'Переклади речення','Відповідай вручну або складай речення з груп слів.']]:($kind==='rephrase'?[]:[[array_keys($source['after_data'][$kind]),$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','Обери одну відповідь для кожної умови.']]);
                foreach($groups as [$indices,$title,$intro])$target['groups'][]=['key'=>'vcq-native-'.$source['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$title,'instruction'=>$intro];
            }$doc['targets'][]=$target;
        }return $doc;
    }
    public static function validate(array $doc):void
    {
        parent::validate($doc);if($doc!==self::compile())throw new RuntimeException('Vocabulary and collocations finite native copy differs.');$items=$cards=0;
        foreach($doc['targets'] as $target){foreach($target['items'] as $rows)foreach($rows as $row){$items++;foreach($row['option_labels']??[] as $label)if(!is_string($label)||$label===''||mb_strlen($label)>180||preg_match('/<[^>]+>|\{a\d+\}|&(?:nbsp|amp|quot);/u',$label))throw new RuntimeException('Vocabulary and collocations option label must remain short and plain.');}foreach($target['groups'] as $group){$cards++;if(count($group['indices'])!==2)throw new RuntimeException('Vocabulary and collocations cards need two tasks.');}}
        if($items!==10||$cards!==5)throw new RuntimeException('Vocabulary and collocations compact coverage differs.');
    }
    public static function nativeDisplay(?array $compact,?array $original):?array
    {
        if(($compact['projection_family']??null)!=='vocabulary-collocations-practice-compact-v1')return $original;$display=$original??['selects'=>[],'choices'=>[],'answer_groups'=>[]];foreach(['selects','choices'] as $kind)foreach($compact['items'][$kind] as $row)if(isset($row['option_labels']))$display[$kind][$row['index']][$kind==='selects'?'options':'option_labels']=$row['option_labels'];return $display;
    }
}
