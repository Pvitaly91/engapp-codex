<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Exact theory-only completion. Old cases, control IDs, answers and scoring ownership are preserved. */
final class FormalEnglishPracticeSupplement
{
    public const SOURCE='docs/content/formal-english-practice-supplement.v1.json';
public const SOURCE_SHA='8453f87c04d0e7a28a55d6c4706da0ed9564ba19d0b8d1eaa3e85e0ff7f92f0a';
    public static function digest(array$v):string{return FormalEnglishPracticeSource::digest($v);}
    private static function uk(string$text):void{if(trim($text)===''||!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$text)||preg_match('/[A-Za-z<>]|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu',$text))throw new RuntimeException('Formal English hints must be plain Ukrainian.');}
    public static function groups(string$ordered,string$answer):array
    {
        $groups=explode('|',$ordered);if(count($groups)<2||implode(' ',$groups)!==$answer)throw new RuntimeException('Formal English complete ordered answer differs.');
        foreach($groups as$g)if(trim($g)!==$g||$g===''||str_contains($g,'/')||count(preg_split('/\s+/u',$g))>3)throw new RuntimeException('Formal English group needs1–3words.');return$groups;
    }
    public static function shuffled(array$groups,string$seed):array
    {
        $rows=[];foreach($groups as$i=>$text)$rows[]=['text'=>$text,'sort'=>hash('sha256',$seed.'|'.$i.'|'.$text)];usort($rows,fn($a,$b)=>strcmp($a['sort'],$b['sort']));$out=array_column($rows,'text');if($out===$groups)$out[]=array_shift($out);return$out;
    }
    public static function tokenLabels(array$groups,array$accepted):array
    {
        $out=[];$words=preg_split('/\s+/u',implode(' ',$groups));$key=static fn($s)=>trim(preg_replace('/\s+/u',' ',preg_replace('/[.,!?;:…]/u','',$s)));
        foreach($accepted as$a){if($key($a)===$key(implode(' ',$groups)))continue;$other=preg_split('/\s+/u',$a);$left=0;while(isset($words[$left],$other[$left])&&$words[$left]===$other[$left])$left++;$right=0;while($right<count($words)-$left&&$right<count($other)-$left&&$words[count($words)-1-$right]===$other[count($other)-1-$right])$right++;$old=implode(' ',array_slice($words,$left,count($words)-$left-$right));$new=implode(' ',array_slice($other,$left,count($other)-$left-$right));if($old===''||$new==='')continue;foreach($groups as$g)if(str_contains($g,$old)){$alias=str_replace($old,$new,$g);$out[$g][$alias]=true;break;}}
        $labels=[];foreach($out as$g=>$aliases)$labels[$g]=$g.' ('.implode(' / ',array_keys($aliases)).')';return$labels;
    }
    private static function addition(array$row,string$topic,int$number):array
    {
        [$kind,$condition,$ordered,$hint]=$row;self::uk($hint);$id='feq-native-'.$topic.'-'.($kind==='translation'?'translate':'gap').'-'.$number;$groups=[];$shuffled=[];
        if($kind==='gap'){if(substr_count($condition,'{a1}')!==1||preg_match('/\{a[2-9]/u',$condition))throw new RuntimeException('Formal English gap must be one complete phrase.');$answer=$ordered;}
        else{self::uk($condition);$answer=str_replace('|',' ',$ordered);$groups=self::groups($ordered,$answer);$shuffled=self::shuffled($groups,$id);}
        $accepted=array_values(array_unique(array_merge([$answer],$row[4]??[])));return['id'=>$id,'kind'=>$kind,'condition'=>$condition,'answer'=>$answer,'accepted'=>$accepted,'hint_uk'=>$hint,'ordered_groups'=>$groups,'shuffled_groups'=>$shuffled,'token_labels'=>self::tokenLabels($groups,$accepted)];
    }
    public static function compile():array
    {
        $sources=FormalEnglishPracticeSource::load()['targets'];$plans=FormalEnglishNativePlans::additions();$retained=FormalEnglishNativePlans::retained();$hints=FormalEnglishNativePlans::authoredHints();
        if(array_column($sources,'topic')!==array_keys($plans))throw new RuntimeException('Formal English six topic order differs.');
        $doc=['schema_version'=>1,'package'=>'formal-english-native-practice-supplement','status'=>'frozen','source'=>['path'=>FormalEnglishPracticeSource::SOURCE,'sha256'=>FormalEnglishPracticeSource::SOURCE_SHA],'targets'=>[]];
        foreach($sources as$t){$topic=$t['topic'];$before=$t['after_data'];$after=$before;$updates=[];$labels=[];$added=[];$counts=[];$authored=$t['mode']==='authored';
            if(!$authored){foreach($before['inputs']as$i=>$item){[$ordered,$condition,$hint]=$retained[$topic][$i];self::uk($hint);$groups=self::groups($ordered,$item['answer']);$shuffled=self::shuffled($groups,$topic.'-retained-'.$i);$after['inputs'][$i]['before']=implode(' / ',$shuffled);if(isset($item['m36_token_groups']))$after['inputs'][$i]['m36_token_groups']=$shuffled;$extra=FormalEnglishNativePlans::retainedAliases()[$topic][$i]??[];if($extra!==[])$after['inputs'][$i]['accepted']=array_values(array_unique(array_merge($item['accepted']??[$item['answer']],$extra)));$after['inputs'][$i]['practice_kind']='order';$after['inputs'][$i]['verb_hint']=$hint;$after['inputs'][$i]['punctuation_sensitive']=false;$labels[$i]=self::tokenLabels($groups,$after['inputs'][$i]['accepted']??[$item['answer']]);$updates[]=['index'=>$i,'ordered_groups'=>$groups,'condition_uk'=>$condition,'hint_uk'=>$hint];}}
            else{foreach($before['cases']as$i=>$case)foreach($case['controls']as$p=>$control)if($control['kind']==='manual'){$id=$control['id'];$hint=$hints[$id]??null;if(!is_string($hint))throw new RuntimeException('Formal English authored manual hint absent.');self::uk($hint);$after['cases'][$i]['controls'][$p]['verb_hint']=$hint;$after['cases'][$i]['controls'][$p]['punctuation_sensitive']=false;$after['cases'][$i]['controls'][$p]['practice_kind']='order';$tokenLabels=self::tokenLabels($control['tokens']??[],$control['accepted']??[$control['answer']]);if($tokenLabels!==[])$after['cases'][$i]['controls'][$p]['token_labels']=$tokenLabels;$updates[]=['index'=>$i,'part'=>$p,'id'=>$id,'hint_uk'=>$hint,'ordered_groups'=>$control['tokens']??[]];}}
            foreach($plans[$topic]as$row){$kind=$row[0];$number=($counts[$kind]??0)+1;$counts[$kind]=$number;$new=self::addition($row,$topic,$number);
                $feedbackAnswer=$kind==='gap'?$new['answer'].(count($new['accepted'])>1?' ('.implode(' / ',array_slice($new['accepted'],1)).')':'').'. ':'';if(!$authored){$index=count($after['inputs']);$after['inputs'][]=['id'=>$new['id'],'practice_kind'=>$kind,'before'=>$kind==='gap'?str_replace('{a1}','____',$new['condition']):implode(' / ',$new['shuffled_groups']),'after'=>$kind==='gap'?$new['hint_uk']:$new['condition'],'verb_hint'=>$new['hint_uk'],'answer'=>$new['answer'],'accepted'=>$new['accepted'],'punctuation_sensitive'=>false,'feedback'=>array_fill_keys(array_map('mb_strtolower',$new['accepted']),'Правильно. '.$feedbackAnswer.$new['hint_uk'])];$new['index']=$index;if($new['ordered_groups']!==[])$labels[$index]=$new['token_labels'];}
                else{$caseIndex=$kind==='gap'?6:7;$part=$number-1;if(!isset($after['cases'][$caseIndex])){$after['cases'][$caseIndex]=['source_index'=>$caseIndex+1,'interaction'=>'manual','controls'=>[]];$after['author_self_check']['prompts'][$caseIndex]=$kind==='gap'?'Уведи лише пропущену частину в обох реченнях.':'Переклади обидва речення англійською.';$after['author_self_check']['answers'][$caseIndex]='';}
                    $control=['id'=>$new['id'],'kind'=>'manual','practice_kind'=>$kind,'label'=>$kind==='gap'?'Доповни речення':'Переклади речення','verb_hint'=>$new['hint_uk'],'answer'=>$new['answer'],'accepted'=>$new['accepted'],'tokens'=>$new['ordered_groups'],'punctuation_sensitive'=>false];$control[$kind==='gap'?'stimulus_en':'stimulus_uk']=$kind==='gap'?str_replace('{a1}','____',$new['condition']):$new['condition'];if($new['token_labels']!==[])$control['token_labels']=$new['token_labels'];$after['cases'][$caseIndex]['controls'][]=$control;$after['author_self_check']['answers'][$caseIndex].=($part?'<br>':'').htmlspecialchars($new['answer'].($kind==='gap'&&count($new['accepted'])>1?' ('.implode(' / ',array_slice($new['accepted'],1)).')':''),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').' — '.htmlspecialchars($new['hint_uk'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');$new['index']=$caseIndex;$new['part']=$part;
                }$added[]=$new;
            }
            if($counts!==['gap'=>2,'translation'=>2])throw new RuntimeException('Formal English missing two new manual tasks per type.');
            $after['formal_english_practice_supplement']=['revision'=>'v1','topic'=>$topic];$doc['targets'][]=['topic'=>$topic,'mode'=>$t['mode'],'identity'=>$t['identity'],'local_url'=>$t['local_url'],'definition'=>$t['definition'],'definition_sha256'=>$t['definition_sha256'],'release'=>$t['source'],'release_sha256'=>$t['source_sha256'],'binding'=>$t['binding'],'design'=>$t['design'],'source_index'=>$t['source_index'],'before_data'=>$before,'before_data_sha256'=>self::digest($before),'after_data'=>$after,'after_data_sha256'=>self::digest($after),'input_updates'=>$updates,'added'=>$added,'input_token_labels'=>$labels];
        }return$doc;
    }
    public static function manifest(?array$doc=null):array{$doc??=self::compile();foreach($doc['targets']as&$t){unset($t['before_data'],$t['after_data']);}unset($t);return$doc;}
    public static function validate(array$doc):void{if($doc!==self::manifest())throw new RuntimeException('Formal English supplement differs from exact finite source.');}
    public static function load():array
    {
        $path=base_path(self::SOURCE);if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Formal English supplement bytes differ.');static$doc;if($doc===null){$candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$derived=self::compile();if($candidate!==self::manifest($derived))throw new RuntimeException('Formal English supplement exact plan differs.');$doc=$derived;}return$doc;
    }
    private static function entry(object$block,array$data,bool$projected):?array
    {
        if(app()->getLocale()!=='uk'||($block->locale??null)!=='uk'||($block->type??null)!=='practice-set'||!in_array($block->seeder??null,FormalEnglishPracticeSource::owners(),true))return null;
        try{foreach(self::load()['targets']as$t){foreach($t['binding']as$f=>$e)if($f!=='body_sha256'&&($block->$f??null)!==$e)continue 2;if(is_string($block->body??null)&&hash_equals($t['binding']['body_sha256'],hash('sha256',$block->body))&&$data===$t[$projected?'after_data':'before_data'])return$t;}}catch(Throwable){}return null;
    }
    public static function project(object$b,array$d,bool$c):array{return$c?(self::entry($b,$d,false)['after_data']??$d):$d;}
    public static function isProjected(object$b,array$d):bool{return self::entry($b,$d,true)!==null;}
    public static function originalDataForDisplay(object$b,array$d):array{return self::entry($b,$d,true)['before_data']??$d;}
    public static function inputTokenLabels(object$b,array$d):array{return self::entry($b,$d,true)['input_token_labels']??[];}
}


