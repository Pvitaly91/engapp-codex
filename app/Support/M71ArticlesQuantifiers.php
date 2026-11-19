<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite content-only release; the accepted native template and grader own presentation. */
final class M71ArticlesQuantifiers
{
    public const BASE_SHA = '1291b576b47dc28e5ba9e0c58faa052f6e49460e';
    public const BEFORE = 'docs/content/m71-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m71-articles-quantifiers.v1.json';
    public const MASTER = 'docs/content/m71-articles-quantifiers.v1.0.0.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m71-articles-quantifiers.v1.json';
    public const CHECKSUMS = 'docs/content/m71-source-checksums.v1.json';
    public const BEFORE_SHA = '75d6fbf91ccbee4f695c91c0c3e2118de66f03fa213c6bf595d961b5d711aeb8';
    public const SOURCE_SHA = '6478604f7b9a4af998cf462d4031467519a407c1303f0698ec662cfa5d514d2a';
    public const MASTER_SHA = '95f15dcbb849bc6ecb8e666d51dec521c0b6c598800e3f90cf4458a98f94f6d0';
    public const CONTRACT_SHA = '3fdee3c0a39bc03845b8a6c1698d2d866b7f15423e863b29036b4953edbcf8e7';
    public const CHECKSUMS_SHA = '7a98d657c93b8ddbf0a5f8ae011724a1168a35ac0b96ce7783d592dd595ba99d';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesTheorySeeder',
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityZeroArticleTheorySeeder',
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityQuantifiersTheorySeeder',
    ];
    public const SLUGS = ['articles-a-an-the','zero-article','quantifiers-much-many-a-lot-few-little'];
    public const PAGE_IDS = [82,91,90];
    public const CATEGORY_IDS = [16,16,16];
    public const ANCESTRIES = [['imennyky-artykli-ta-kilkist'],['imennyky-artykli-ta-kilkist'],['imennyky-artykli-ta-kilkist']];
    public const LEVELS = [null,null,null];
    public const DISPLAY_LEVELS = ['A1–A2','A1–A2','A2–B1'];
    public const CONTINUING_COUNTS = [12,14,13];
    public const PRACTICE_SOURCE_INDICES = [10,12,11];
    public const PRACTICE_UUIDS = ['f1edde68-82a6-56d6-8aed-7b5dc7b969c0','e0348abd-f7ce-509e-9329-462c974db20a','44661475-7c1a-5267-b9ba-261101001cc9'];
    public const RENDERED_TITLES = ['Articles: a / an / the','Zero article','Quantifiers: much, many, few, little'];

    public static function profile(): array
    {
        $bindings=[];
        foreach(self::OWNERS as $i=>$owner){
            $bindings[]=['identity'=>$owner,'slug'=>self::SLUGS[$i],'page_id'=>self::PAGE_IDS[$i],
                'category_id'=>self::CATEGORY_IDS[$i],'ancestry'=>self::ANCESTRIES[$i],
                'local_url'=>'http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/'.self::SLUGS[$i],
                'practice_level'=>null,'display_level'=>self::DISPLAY_LEVELS[$i],
                'rendered_title'=>self::RENDERED_TITLES[$i],
                'continuing_levels'=>array_fill(0,self::CONTINUING_COUNTS[$i],null),'appended_blocks'=>[],
                'existing_practice'=>['source_index'=>self::PRACTICE_SOURCE_INDICES[$i],'uuid'=>self::PRACTICE_UUIDS[$i],'level'=>null],
                'practice_contract'=>['counts'=>['selects'=>4,'choices'=>2,'inputs'=>5],'sentence_input_indices'=>[3,4]]];
        }
        return ['label'=>'M71','base_sha'=>self::BASE_SHA,'version'=>'1.0.0','marker'=>'m71_native_v1',
            'detail_marker'=>'m71_detail_key','uuid_prefix'=>'m71:','design'=>'m71-native-v1',
            'artifacts'=>[self::BEFORE=>self::BEFORE_SHA,self::SOURCE=>self::SOURCE_SHA,self::MASTER=>self::MASTER_SHA,
                self::CONTRACT=>self::CONTRACT_SHA,self::CHECKSUMS=>self::CHECKSUMS_SHA],'bindings'=>$bindings];
    }
    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root=null):array { return self::release()->load($root); }
    public static function definition(array $lesson,array $before):array { return self::release()->definition($lesson,$before); }
    public static function validate(array $before,array $patch,array $master,?array $contract=null):void
    {
        if(($before['content_type']??null)!=='M71_source_before'||($patch['content_type']??null)!=='M71_Articles_Quantifiers'){
            throw new RuntimeException('M71 exact provenance differs.');
        }
        foreach($master['lessons']??[] as $i=>$lesson){
            $old=$before['targets'][$i]['before']['page']['blocks']??[];
            if(array_column($old,'type')!==array_column($lesson['blocks']??[],'type')
                ||count($old)!==self::CONTINUING_COUNTS[$i]){throw new RuntimeException('M71 continuing native slots differ.');}
            $data=$lesson['blocks'][self::PRACTICE_SOURCE_INDICES[$i]]['data']??[];
            foreach(['selects','choices','inputs','rephrase'] as $group){
                foreach($data[$group]??[] as $n=>$task){
                    if($group==='rephrase'&&$n===0&&!empty($task['example_original'])){continue;}
                    if(!is_string($task['answer']??null)||trim($task['answer'])===''||!in_array($task['answer'],$task['accepted']??[],true)){
                        throw new RuntimeException('M71 canonical answer must be deliberate, nonempty and accepted.');
                    }
                    foreach($task['feedback']??[] as $text){
                        if(!is_string($text)||preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)){
                            throw new RuntimeException('M71 x-text feedback must be plain text.');
                        }
                    }
                }
            }
            foreach(['selects'=>4,'inputs'=>3,'rephrase'=>3] as $group=>$count){
                foreach(array_slice($data[$group]??[],0,$count) as $task){
                    if(array_key_exists('id',$task)||array_key_exists('source_index',$task)){throw new RuntimeException('M71 retained control identity changed.');}
                }
            }
            if(count($data['rephrase']??[])!==3||empty($data['rephrase'][0]['example_original'])){
                throw new RuntimeException('M71 retained corrections or ungraded demo lost.');
            }
            // Content validation mirrors the accepted factory's comparison alphabet;
            // it does not grade learner answers or change the runtime normalizer.
            $normal=static function(string $value):string{
                $value=\Normalizer::normalize($value,\Normalizer::FORM_KD);
                if(!is_string($value)){throw new RuntimeException('M71 comparison alphabet unavailable.');}
                $value=preg_replace('/[\x{0300}-\x{036F}]/u','',mb_strtolower($value));
                $value=preg_replace('/[\x{2018}\x{2019}\x{201B}\x{2032}`´ʼ\']/u','',$value);
                return trim(preg_replace('/\s+/u',' ',preg_replace('/[^\p{L}\p{N}]+/u',' ',$value)));
            };
            foreach(['selects','choices'] as $group){foreach($data[$group] as $task){
                $options=$task['options']??[];$keys=array_map($normal,$options);
                if(!in_array($task['answer'],$options,true)){throw new RuntimeException('M71 visible option/key differs.');}
                if(count($options)<2||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)){
                    throw new RuntimeException('M71 normalized option collision or empty choice.');
                }
                $accepted=array_map($normal,$task['accepted']);
                foreach($options as $option){
                    if(!in_array($option,$task['accepted'],true)&&in_array($normal($option),$accepted,true)){
                        throw new RuntimeException('M71 distractor collides with an accepted answer.');
                    }
                }
            }}
            if($i===1&&($data['inputs'][1]['answer']??null)!=='Без артикля'){
                throw new RuntimeException('M71 zero decision needs an explicit nonempty answer, not an untouched field.');
            }
            $oldNav=json_decode($old[count($old)-1]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];
            $newNav=$lesson['blocks'][count($old)-1]['data']['items']??[];
            if(array_map(static fn($v)=>array_intersect_key($v,array_flip(['label','current'])),$newNav)!==$oldNav){
                throw new RuntimeException('M71 existing navigation labels/current states changed.');
            }
        }
        self::release()->validate($before,$patch,$master,$contract);
    }
    public static function presentation(object $block,array $data):?array { return self::release()->presentation($block,$data); }
    public static function validDesign(?array $design):bool { return self::release()->validDesign($design); }
    public static function pointPanels(?array $design):?array { return self::release()->pointPanels($design); }
    public static function correction(array $item,?array $design):?array { return self::release()->correction($item,$design); }
    public static function preserveExcludedTextBlock(object $block,string $caller):?object { return self::release()->preserveExcludedTextBlock($block,$caller); }
    public static function preserveCoursePage(?Page $page):?Page { return self::release()->preserveCoursePage($page); }
}
