<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite learning-data policy; all presentation remains in canonical components. */
final class M70IntensifiersNounBasics
{
    public const BASE_SHA = 'be2b1c2549e70d91cc6f3c47cd7708a68ff7eb71';
    public const BEFORE = 'docs/content/m70-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m70-intensifiers-noun-basics.v1.json';
    public const MASTER = 'docs/content/m70-intensifiers-noun-basics.v1.0.0.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m70-intensifiers-noun-basics.v1.json';
    public const CHECKSUMS = 'docs/content/m70-source-checksums.v1.json';
    public const BEFORE_SHA = '69a09c1dd2937d0f7282fd130c32779d798f0d6ea99c9d5b9986c9c3223d7de2';
    public const SOURCE_SHA = '6609e7482723ec79d088de3d77b23fd185078d1bbd550dda03c30241d82a9708';
    public const MASTER_SHA = '35f566620b34183e982d8d8978730ccc325ad86a6c0e8fb599c76111338a405b';
    public const CONTRACT_SHA = '2d8089fa084c564ee7c407f44dc25693ada0abf3472bd23b2dd21bc3d7745aaf';
    public const CHECKSUMS_SHA = '491fb207b64c2566fd0e802e4d09973e22f2938b08a76aa31edc7e64a7ad109c';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesIntensifiersTheorySeeder',
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder',
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityCountableUncountableNounsTheorySeeder',
    ];
    public const SLUGS = ['intensifiers','plural-nouns-s-es-ies','countable-vs-uncountable-nouns'];
    public const PAGE_IDS = [277,89,85];
    public const CATEGORY_IDS = [52,16,16];
    public const ANCESTRIES = [['prykmetniky-ta-pryslinknyky'],['imennyky-artykli-ta-kilkist'],['imennyky-artykli-ta-kilkist']];
    // Technical nulls are intentional: no level transition or widening to obtain five questions.
    public const LEVELS = ['B1',null,null];
    public const DISPLAY_LEVELS = ['B1','A1–B1','A1–A2'];
    public const CONTINUING_COUNTS = [6,14,12];
    public const PRACTICE_SOURCE_INDICES = [6,12,10];
    public const PRACTICE_UUIDS = [null,'e480381a-dd8f-5388-af75-ff58d96827af','132a698f-082e-5722-8315-bbea0dcbb824'];
    public const RENDERED_TITLES = ['Intensifiers','Plural nouns','Countable / Uncountable Nouns — Злічувані й незлічувані'];
    private const PRACTICE_DATA_SHAS = [
        'ed1bb462018c799ccbb690f0dd314fa6ba01c5791faac29ccd3a9a2da1de5894',
        'efdf0c7795fb450045b7b769617e89129dc20d8d834b08fc8a9ac70fe1fc3f2c',
        'e20f310eb38a1b7612b22e8b3088e877ed60a51fe53e486e0317abcf3341d786',
    ];
    public const CONTINUING_TYPES = [
        ['hero','forms-grid','usage-panels','mistakes-grid','summary-list','navigation-chips'],
        ['hero','forms-grid','usage-panels','usage-panels','usage-panels','usage-panels','usage-panels','comparison-table','usage-panels','usage-panels','mistakes-grid','summary-list','practice-set','navigation-chips'],
        ['hero','forms-grid','usage-panels','usage-panels','usage-panels','usage-panels','comparison-table','usage-panels','mistakes-grid','summary-list','practice-set','navigation-chips'],
    ];

    public static function profile(): array
    {
        $bindings=[];
        foreach(self::OWNERS as $i=>$owner){
            $bindings[]=['identity'=>$owner,'slug'=>self::SLUGS[$i],'page_id'=>self::PAGE_IDS[$i],
                'category_id'=>self::CATEGORY_IDS[$i],'ancestry'=>self::ANCESTRIES[$i],
                'local_url'=>'http://gramlyze.loc/theory/'.implode('/',self::ANCESTRIES[$i]).'/'.self::SLUGS[$i],
                'practice_level'=>self::LEVELS[$i],'display_level'=>self::DISPLAY_LEVELS[$i],
                'rendered_title'=>self::RENDERED_TITLES[$i],
                'continuing_levels'=>$i===0?['B1','B1','B1','B1','B1',null]:array_fill(0,self::CONTINUING_COUNTS[$i],null),
                'appended_blocks'=>$i===0?[['key'=>'intensifiers-practice','type'=>'practice-set','column'=>'left','level'=>'B1']]:[],
                'practice_contract'=>['counts'=>['selects'=>$i===0?2:4,'choices'=>2,'inputs'=>[2,6,5][$i]],
                    'sentence_input_indices'=>[[0,1],[4,5],[3,4]][$i]]]
                +($i===0?[]:['existing_practice'=>['source_index'=>self::PRACTICE_SOURCE_INDICES[$i],
                    'uuid'=>self::PRACTICE_UUIDS[$i],'level'=>null]]);
        }
        return ['label'=>'M70','base_sha'=>self::BASE_SHA,'version'=>'1.0.0','marker'=>'m70_native_v1',
            'detail_marker'=>'m70_detail_key','uuid_prefix'=>'m70:','design'=>'m70-native-v1',
            'artifacts'=>[self::BEFORE=>self::BEFORE_SHA,self::SOURCE=>self::SOURCE_SHA,self::MASTER=>self::MASTER_SHA,
                self::CONTRACT=>self::CONTRACT_SHA,self::CHECKSUMS=>self::CHECKSUMS_SHA],'bindings'=>$bindings];
    }
    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root=null):array { return self::release()->load($root); }
    public static function definition(array $lesson,array $before):array { return self::release()->definition($lesson,$before); }
    public static function validate(array $before,array $patch,array $master,?array $contract=null):void
    {
        if(($before['content_type']??null)!=='M70_source_before'||($patch['content_type']??null)!=='M70_Intensifiers_Noun_Basics'){
            throw new RuntimeException('M70 exact provenance differs.');
        }
        foreach($master['lessons']??[] as $i=>$lesson){
            if(array_column($before['targets'][$i]['before']['page']['blocks']??[],'type')!==self::CONTINUING_TYPES[$i]
                ||array_column(array_slice($lesson['blocks']??[],0,self::CONTINUING_COUNTS[$i]),'type')!==self::CONTINUING_TYPES[$i]){
                throw new RuntimeException('M70 per-owner native slots differ.');
            }
            $data=$lesson['blocks'][self::PRACTICE_SOURCE_INDICES[$i]]['data']??[];
            if(M26DetailPackage::digest($data)!==self::PRACTICE_DATA_SHAS[$i]){
                throw new RuntimeException('M70 reviewed retained/new practice data changed.');
            }
            foreach(['selects','choices','inputs','rephrase'] as $group){
                foreach($data[$group]??[] as $index=>$task){
                    if($group==='rephrase'&&$index===0&&!empty($task['example_original'])){continue;}
                    if(!in_array($task['answer']??null,$task['accepted']??[],true)){
                        throw new RuntimeException('M70 canonical/retained answer missing.');
                    }
                    foreach($task['feedback']??[] as $text){
                        if(!is_string($text)||preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)){
                            throw new RuntimeException('M70 x-text feedback must be plain text.');
                        }
                    }
                }
            }
            if($i>0){
                // All old controls had no IDs/source_index; preserve that absence and their order.
                foreach(['selects'=>4,'inputs'=>$i===1?4:3] as $group=>$count){
                    foreach(array_slice($data[$group]??[],0,$count) as $task){
                        if(array_key_exists('id',$task)||array_key_exists('source_index',$task)){
                            throw new RuntimeException('M70 retained task identity changed.');
                        }
                    }
                }
                if(count($data['rephrase']??[])!==($i===1?4:3)
                    ||empty($data['rephrase'][0]['example_original'])){
                    throw new RuntimeException('M70 retained rephrase/demo lost.');
                }
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
