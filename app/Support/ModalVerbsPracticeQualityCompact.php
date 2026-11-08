<?php

namespace App\Support;

use RuntimeException;

/** Next exact version: accepted short copy plus forty new manual tasks in native two-item cards. */
final class ModalVerbsPracticeQualityCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/modal-verbs-practice-compact.v2.json';
    public const SOURCE_SHA='0b8332089d2a16fbcfc81b1995c7f2d7964adbf3e5dc999aad99a803208241b3';
    public const SUPPLEMENT=ModalVerbsPracticeSupplement::class;

    public static function compile(): array
    {
        $old=array_column(ModalVerbsPracticeCompact::load()['targets'],null,'topic');
        $doc=['schema_version'=>1,'source'=>['path'=>ModalVerbsPracticeSupplement::SOURCE,'sha256'=>ModalVerbsPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(ModalVerbsPracticeSupplement::load()['targets'] as $source){
            $target=$old[$source['topic']];$target['projection_family']='modal-verbs-practice-compact-v2';$target['after_data_sha256']=$source['after_data_sha256'];
            foreach(['selects','choices','inputs','rephrase'] as $kind){
                foreach($target['items'][$kind] as &$row){$item=$source['after_data'][$kind][$row['index']];$row['source_item_sha256']=ModalVerbsPracticeSupplement::digest($item);}unset($row);
            }
            foreach($source['added'] as $addition){
                $index=$addition['index'];$item=$source['after_data']['inputs'][$index];$gap=$addition['kind']==='gap';
                $stimuli=[['lang'=>$gap?'en':'uk','text'=>$gap?str_replace('{a1}','____',$addition['condition']):$addition['condition']]];
                $stimuli[]=['lang'=>'uk','text'=>$addition['hint_uk']];
                if(count($addition['accepted'])>1){
                    // Only the differing lexical span is shown, never a repeated complete sentence key.
                    $canonical=preg_split('/\s+/u',$addition['answer']);$alternatives=[];
                    foreach(array_slice($addition['accepted'],1) as $alias){$other=preg_split('/\s+/u',$alias);$left=0;while(isset($canonical[$left],$other[$left])&&$canonical[$left]===$other[$left])$left++;$right=0;while($right<count($canonical)-$left&&$right<count($other)-$left&&$canonical[count($canonical)-1-$right]===$other[count($other)-1-$right])$right++;$span=implode(' ',array_slice($other,$left,count($other)-$left-$right));if($span!=='')$alternatives[]=$span;}
                    if($alternatives!==[])$stimuli[]=['lang'=>'en','text'=>'('.implode(' / ',array_unique($alternatives)).')'];
                }
                $target['items']['inputs'][]=['index'=>$index,'source_item_sha256'=>ModalVerbsPracticeSupplement::digest($item),'source_index'=>null,'instruction_uk'=>$gap?'Уведи всю відсутню дієслівну групу.':'Переклади речення англійською.','stimuli'=>$stimuli,'deferred_source_fields'=>[]];
            }
            foreach([['gap',[2,3],'Заповни пропуски','Уведи лише відсутню частину; наведені слова не повторюй.'],['translate',[4,5],'Переклади речення','Відповідай вручну або натискай перемішані групи слів.']] as [$kind,$indices,$title,$instruction]){
                $target['groups'][]=['key'=>'mv-quality-'.$source['topic'].'-'.$kind,'kind'=>'inputs','indices'=>$indices,'title'=>$title,'instruction'=>$instruction];
            }
            $doc['targets'][]=$target;
        }
        return $doc;
    }

    public static function validate(array $doc): void
    {
        parent::validate($doc);if($doc!==self::compile())throw new RuntimeException('Modal quality compact finite projection differs.');
        $tasks=0;$cards=0;foreach($doc['targets'] as $target){foreach($target['items'] as $items)$tasks+=count($items);foreach($target['groups'] as $group){if(count($group['indices'])!==2)throw new RuntimeException('Modal native cards need two tasks.');$cards++;}}
        if($tasks!==104||$cards!==52)throw new RuntimeException('Modal quality compact coverage incomplete.');
    }

    public static function nativeDisplay(?array $compact,?array $original): ?array
    {
        if(($compact['projection_family']??null)!=='modal-verbs-practice-compact-v2')return $original;
        $legacy=$compact;$legacy['projection_family']='modal-verbs-practice-compact-v1';return ModalVerbsPracticeCompact::nativeDisplay($legacy,$original);
    }
}
