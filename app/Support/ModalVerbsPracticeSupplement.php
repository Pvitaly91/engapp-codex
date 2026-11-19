<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Exact canonical modal practice completion; frozen releases, old IDs and old answers remain authoritative. */
final class ModalVerbsPracticeSupplement
{
    public const SOURCE='docs/content/modal-verbs-practice-supplement.v1.json';
    public const SOURCE_SHA='3c15ca031923317fa7cffa855506b13122691c8c2b5bb58c881157fbf110df28';

    public static function digest(array $value): string { return ModalVerbsPracticeSource::digest($value); }

    private static function uk(string $text): void
    {
        if(trim($text)===''||!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$text)||preg_match('/[A-Za-z<>]|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu',$text))throw new RuntimeException('Modal native hints must be plain Ukrainian.');
    }

    private static function groups(string $ordered,string $answer): array
    {
        $groups=explode('|',$ordered);
        if(count($groups)<2||implode(' ',$groups)!==$answer)throw new RuntimeException('Modal native ordered groups changed the complete answer.');
        foreach($groups as $group)if(trim($group)!==$group||$group===''||str_contains($group,'/')||count(preg_split('/\s+/u',$group))>3)throw new RuntimeException('Modal native token groups need one to three words.');
        return $groups;
    }

    private static function shuffled(array $groups,string $seed): array
    {
        $rows=[];foreach($groups as $i=>$text)$rows[]=['text'=>$text,'sort'=>hash('sha256',$seed.'|'.$i.'|'.$text)];
        usort($rows,static fn($a,$b)=>strcmp($a['sort'],$b['sort']));$out=array_column($rows,'text');
        if($out===$groups)$out[]=array_shift($out);return $out;
    }

    private static function tokenLabels(array $groups,array $accepted): array
    {
        $out=[];$words=preg_split('/\s+/u',implode(' ',$groups));
        foreach($accepted as $alternative){
            $other=preg_split('/\s+/u',$alternative);$left=0;
            while(isset($words[$left],$other[$left])&&$words[$left]===$other[$left])$left++;
            $right=0;while($right<count($words)-$left&&$right<count($other)-$left&&$words[count($words)-1-$right]===$other[count($other)-1-$right])$right++;
            $old=implode(' ',array_slice($words,$left,count($words)-$left-$right));$new=implode(' ',array_slice($other,$left,count($other)-$left-$right));
            if($old===''||$new==='')continue;
            foreach($groups as $group)if(str_contains($group,$old)){$alias=str_replace($old,$new,$group);$out[$group][$alias]=true;break;}
        }
        $labels=[];foreach($out as $group=>$aliases)$labels[$group]=$group.' ('.implode(' / ',array_keys($aliases)).')';return $labels;
    }

    public static function compile(): array
    {
        $sources=ModalVerbsPracticeSource::load()['targets'];$additions=ModalVerbsNativePlans::additions();$retained=ModalVerbsNativePlans::retained();
        if(array_column($sources,'topic')!==array_keys($additions)||array_keys($additions)!==array_keys($retained))throw new RuntimeException('Modal native finite topic order differs.');
        $doc=['schema_version'=>1,'package'=>'modal-verbs-native-practice-supplement','status'=>'frozen','source'=>['path'=>ModalVerbsPracticeSource::SOURCE,'sha256'=>ModalVerbsPracticeSource::SOURCE_SHA],'targets'=>[]];
        foreach($sources as $target){
            $topic=$target['topic'];$before=$target['after_data'];$after=$before;$updates=[];$labels=[];$added=[];$counts=[];
            if(count($before['inputs'])!==2||count($retained[$topic])!==2)throw new RuntimeException('Modal native retained tasks differ.');
            foreach($before['inputs'] as $i=>$item){
                [$ordered,$hint]=$retained[$topic][$i];self::uk($hint);$groups=self::groups($ordered,$item['answer']);$shuffled=self::shuffled($groups,$topic.'-retained-'.$i);
                $after['inputs'][$i]['before']=implode(' / ',$shuffled);
                if(array_key_exists('m36_token_groups',$item))$after['inputs'][$i]['m36_token_groups']=$shuffled;
                $after['inputs'][$i]['practice_kind']='order';$after['inputs'][$i]['verb_hint']=$hint;$after['inputs'][$i]['punctuation_sensitive']=false;
                $labels[$i]=self::tokenLabels($groups,$item['accepted']??[$item['answer']]);
                $updates[]=['index'=>$i,'ordered_groups'=>$groups,'hint_uk'=>$hint];
            }
            foreach($additions[$topic] as $row){
                [$kind,$condition,$ordered,$hint]=$row;self::uk($hint);$number=($counts[$kind]??0)+1;$counts[$kind]=$number;
                $id='mv-native-'.$topic.'-'.($kind==='translation'?'translate':$kind).'-'.$number;$groups=[];$shuffled=[];
                if($kind==='gap'){
                    if(substr_count($condition,'{a1}')!==1||preg_match('/\{a[2-9]/u',$condition))throw new RuntimeException('Modal native gap must hold one complete phrase.');
                    $answer=$ordered;$visible=str_replace('{a1}','____',$condition);$afterText=$hint;
                }else{
                    self::uk($condition);$answer=str_replace('|',' ',$ordered);$groups=self::groups($ordered,$answer);$shuffled=self::shuffled($groups,$id);$visible=implode(' / ',$shuffled);$afterText=$condition;
                }
                $accepted=array_values(array_unique(array_merge([$answer],$row[4]??[])));$index=count($after['inputs']);
                $after['inputs'][]=['id'=>$id,'practice_kind'=>$kind,'before'=>$visible,'after'=>$afterText,'verb_hint'=>$hint,'answer'=>$answer,'accepted'=>$accepted,'punctuation_sensitive'=>false,'feedback'=>array_fill_keys(array_map('mb_strtolower',$accepted),'Правильно. '.$hint)];
                if($groups!==[])$labels[$index]=self::tokenLabels($groups,$accepted);
                $added[]=['id'=>$id,'index'=>$index,'kind'=>$kind,'condition'=>$condition,'answer'=>$answer,'accepted'=>$accepted,'hint_uk'=>$hint,'ordered_groups'=>$groups,'shuffled_groups'=>$shuffled];
            }
            if($counts!==['gap'=>2,'translation'=>2]||count($after['inputs'])!==6)throw new RuntimeException('Modal native three-type coverage incomplete.');
            if($topic==='need-neednt-dont-have-to')$after['linked_practice']['seeder_classes']=[ModalVerbsWidgetHints::NEED_BANK];
            $after['modal_verbs_practice_supplement']=['revision'=>'v1','topic'=>$topic];
            $doc['targets'][]=['topic'=>$topic,'identity'=>$target['identity'],'local_url'=>$target['local_url'],'definition'=>$target['definition'],'definition_sha256'=>$target['definition_sha256'],'release'=>$target['source'],'release_sha256'=>$target['source_sha256'],'binding'=>$target['binding'],'design'=>$target['design'],'source_index'=>$target['source_index'],'before_data'=>$before,'before_data_sha256'=>self::digest($before),'after_data'=>$after,'after_data_sha256'=>self::digest($after),'input_updates'=>$updates,'added'=>$added,'input_token_labels'=>$labels];
        }
        return $doc;
    }

    public static function manifest(?array $document=null): array
    {
        $document??=self::compile();foreach($document['targets'] as &$target){unset($target['before_data'],$target['after_data']);}unset($target);return $document;
    }
    public static function validate(array $doc): void { if($doc!==self::manifest())throw new RuntimeException('Modal native exact finite source differs.'); }
    public static function load(): array
    {
        $path=base_path(self::SOURCE);
        if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Modal native source bytes differ.');
        static $document;if($document===null){$candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$derived=self::compile();if($candidate!==self::manifest($derived))throw new RuntimeException('Modal native exact finite source differs.');$document=$derived;}return $document;
    }

    private static function entry(object $block,array $data,bool $projected): ?array
    {
        if(app()->getLocale()!=='uk'||($block->locale??null)!=='uk'||($block->type??null)!=='practice-set'||!in_array($block->seeder??null,ModalVerbsPracticeSource::OWNERS,true))return null;
        try{foreach(self::load()['targets'] as $target){
            foreach($target['binding'] as $field=>$expected)if($field!=='body_sha256'&&($block->$field??null)!==$expected)continue 2;
            if(is_string($block->body??null)&&hash_equals($target['binding']['body_sha256'],hash('sha256',$block->body))&&$data===$target[$projected?'after_data':'before_data'])return $target;
        }}catch(Throwable){}return null;
    }
    public static function project(object $block,array $data,bool $canonicalCaller): array {return $canonicalCaller?(self::entry($block,$data,false)['after_data']??$data):$data;}
    public static function isProjected(object $block,array $data): bool {return self::entry($block,$data,true)!==null;}
    public static function originalDataForDisplay(object $block,array $data): array {return self::entry($block,$data,true)['before_data']??$data;}
    public static function inputTokenLabels(object $block,array $data): array {return self::entry($block,$data,true)['input_token_labels']??[];}
}
