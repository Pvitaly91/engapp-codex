<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite content-only release; the accepted native template and grader own presentation. */
final class M73GenericIrregularCollective
{
    public const BASE_SHA = 'cb55b329827e52a56cb6d41edb21e70768cf86f4';
    public const BEFORE = 'docs/content/m73-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m73-generic-irregular-collective.v1.json';
    public const MASTER = 'docs/content/m73-generic-irregular-collective.v1.0.0.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m73-generic-irregular-collective.v1.json';
    public const CHECKSUMS = 'docs/content/m73-source-checksums.v1.json';
    public const BEFORE_SHA = 'f27fd3004060b79a939fc1c41db63683269b28b2b7b1c5a83c65251bf40ebcb5';
    public const SOURCE_SHA = '8628f99afc29eb66c50b2996bbd75a360366680aeb362995c50a274af9e87eb5';
    public const MASTER_SHA = '9c3663e9e27aedd29a1ebc5cd33b4ce34a20a87e75ba18431408742e84aac015';
    public const CONTRACT_SHA = '0a081d823b09ac1e04292c48cf27c4b94e6957f2b64a617f15bc3afb24726621';
    public const CHECKSUMS_SHA = '1dfc1cf0aa72fdcd376633a79beb334e5e67582925ebb0bffa69f7fa0f1c304b';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder',
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityIrregularPluralsTheorySeeder',
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityCollectiveNounsTheorySeeder',
    ];
    public const SLUGS = ['advanced-articles-generic-reference-the-rich-a-tiger-people','irregular-plurals','collective-nouns'];
    public const PAGE_IDS = [80,86,84];
    public const CATEGORY_IDS = [16,16,16];
    public const ANCESTRIES = [['imennyky-artykli-ta-kilkist'],['imennyky-artykli-ta-kilkist'],['imennyky-artykli-ta-kilkist']];
    public const LEVELS = [null,'A2','B1'];
    public const DISPLAY_LEVELS = ['B2–C1','A1–A2','B1'];
    public const CONTINUING_COUNTS = [13,7,6];
    public const CONTINUING_LEVELS = [
        [null,null,null,null,null,null,null,null,null,null,null,null,null],
        ['A1–A2','A1','A1–A2','A1–A2','A1','A1–A2','A1–A2'],
        ['B1','B1','B1','B1','B1',null],
    ];
    public const PRACTICE_SOURCE_INDICES = [11,7,6];
    public const GENERIC_PRACTICE_UUID = '4ec54834-db1f-55f8-983a-e6537527e477';
    public const RENDERED_TITLES = ['Generic Reference: the rich, a tiger, Ø people','Irregular Plurals','Collective Nouns'];

    public static function profile(): array
    {
        $bindings=[];
        foreach(self::OWNERS as $i=>$owner){
            $binding=['identity'=>$owner,'slug'=>self::SLUGS[$i],'page_id'=>self::PAGE_IDS[$i],
                'category_id'=>self::CATEGORY_IDS[$i],'ancestry'=>self::ANCESTRIES[$i],
                'local_url'=>'http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/'.self::SLUGS[$i],
                'practice_level'=>self::LEVELS[$i],'display_level'=>self::DISPLAY_LEVELS[$i],
                'rendered_title'=>self::RENDERED_TITLES[$i],'continuing_levels'=>self::CONTINUING_LEVELS[$i],
                'appended_blocks'=>$i===0?[]:[['key'=>$i===1?'irregular-practice':'collective-practice',
                    'type'=>'practice-set','column'=>'left','level'=>self::LEVELS[$i]]]];
            if($i===0){
                $binding['existing_practice']=['source_index'=>11,'uuid'=>self::GENERIC_PRACTICE_UUID,'level'=>null];
                $binding['practice_contract']=['counts'=>['selects'=>4,'choices'=>2,'inputs'=>5],'sentence_input_indices'=>[3,4]];
            }
            $bindings[]=$binding;
        }
        return ['label'=>'M73','base_sha'=>self::BASE_SHA,'version'=>'1.0.0','marker'=>'m73_native_v1',
            'detail_marker'=>'m73_detail_key','uuid_prefix'=>'m73:','design'=>'m73-native-v1',
            'artifacts'=>[self::BEFORE=>self::BEFORE_SHA,self::SOURCE=>self::SOURCE_SHA,self::MASTER=>self::MASTER_SHA,
                self::CONTRACT=>self::CONTRACT_SHA,self::CHECKSUMS=>self::CHECKSUMS_SHA],'bindings'=>$bindings];
    }
    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root=null):array { return self::release()->load($root); }
    public static function definition(array $lesson,array $before):array { return self::release()->definition($lesson,$before); }

    public static function validate(array $before,array $patch,array $master,?array $contract=null):void
    {
        if(($before['content_type']??null)!=='M73_source_before'||($patch['content_type']??null)!=='M73_Generic_Irregular_Collective'){
            throw new RuntimeException('M73 exact provenance differs.');
        }
        self::release()->validate($before,$patch,$master,$contract);
        $gradedTotal=0;
        foreach($master['lessons'] as $i=>$lesson){
            $old=$before['targets'][$i]['before']['page']['blocks'];
            $continuing=array_slice($lesson['blocks'],0,self::CONTINUING_COUNTS[$i]);
            if(count($old)!==self::CONTINUING_COUNTS[$i]
                ||array_column($old,'type')!==array_column($continuing,'type')
                ||array_column($old,'column')!==array_column($continuing,'column')){
                throw new RuntimeException('M73 continuing native slots or columns differ.');
            }
            $data=$lesson['blocks'][self::PRACTICE_SOURCE_INDICES[$i]]['data'];
            $graded=0;
            foreach(['selects','choices','inputs','rephrase'] as $group){
                foreach($data[$group]??[] as $n=>$task){
                    if($i===0&&$group==='rephrase'&&$n===0&&!empty($task['example_original'])){continue;}
                    ++$graded;
                    if(!is_string($task['answer']??null)||trim($task['answer'])===''||!is_array($task['accepted']??null)
                        ||!array_is_list($task['accepted'])||!in_array($task['answer'],$task['accepted'],true)){
                        throw new RuntimeException('M73 canonical answer must be deliberate, nonempty and accepted.');
                    }
                    foreach($task['accepted'] as $answer){
                        if(!is_string($answer)||trim($answer)===''||self::comparisonKey($answer)===''){
                            throw new RuntimeException('M73 accepted answers cannot contain an empty comparison key.');
                        }
                    }
                    foreach($task['feedback']??[] as $key=>$text){
                        if(!is_string($key)||$key===''||$key!==mb_strtolower(trim($key))
                            ||!is_string($text)||trim($text)===''
                            ||preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)){
                            throw new RuntimeException('M73 x-text feedback requires normalized keys and nonempty plain text.');
                        }
                    }
                    if(in_array($group,['selects','choices'],true)){
                        $options=$task['options']??($data['choice_options']??[]);
                        if(!is_array($options)||!array_is_list($options)||count($options)<2
                            ||array_filter($options,static fn($v)=>!is_string($v)||trim($v)==='')){
                            throw new RuntimeException('M73 visible choices must be nonempty strings.');
                        }
                        $keys=array_map(self::comparisonKey(...),$options);
                        if(!in_array($task['answer'],$options,true)||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)){
                            throw new RuntimeException('M73 visible option/key differs or normalized options collide.');
                        }
                        $accepted=array_map(self::comparisonKey(...),$task['accepted']);
                        foreach($options as $option){
                            if(!in_array($option,$task['accepted'],true)&&in_array(self::comparisonKey($option),$accepted,true)){
                                throw new RuntimeException('M73 distractor collides with an accepted answer.');
                            }
                        }
                    }
                }
            }
            if($graded!==($i===0?13:6)){throw new RuntimeException('M73 exact graded learning-position count differs.');}
            $gradedTotal+=$graded;
            if($i===0){
                foreach(['selects'=>4,'inputs'=>3,'rephrase'=>3] as $group=>$count){
                    foreach(array_slice($data[$group]??[],0,$count) as $task){
                        if(array_key_exists('id',$task)||array_key_exists('source_index',$task)){
                            throw new RuntimeException('M73 retained Generic control identity changed.');
                        }
                    }
                }
                if(count($data['rephrase']??[])!==3||empty($data['rephrase'][0]['example_original'])
                    ||empty($data['rephrase'][0]['example_target'])||isset($data['rephrase'][0]['answer'])
                    ||($data['inputs'][2]['answer']??null)!=='Без артикля'){
                    throw new RuntimeException('M73 Generic retained demo, correction positions or nonempty zero answer differs.');
                }
            }elseif(($data['rephrase']??[])!==[]){
                throw new RuntimeException('M73 appended practices cannot add undeclared graded corrections.');
            }
            $footer=self::CONTINUING_COUNTS[$i]-1;
            $oldNav=json_decode($old[$footer]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];
            $newNav=$lesson['blocks'][$footer]['data']['items'];
            $labels=static fn($items)=>array_map(static fn($v)=>array_intersect_key($v,array_flip(['label','current'])),$items);
            if(count($oldNav)!==[9,6,8][$i]||($i===0?$labels($newNav)!==$labels($oldNav):$newNav!==$oldNav)){
                throw new RuntimeException('M73 continuing navigation labels/current states or explicit URLs changed.');
            }
        }
        if($gradedTotal!==25){throw new RuntimeException('M73 exact 25-task educational scope differs.');}
    }

    /** Validation only; the accepted runtime grader and comparison alphabet remain unchanged. */
    private static function comparisonKey(string $value):string
    {
        $value=\Normalizer::normalize($value,\Normalizer::FORM_KD);
        if(!is_string($value)){throw new RuntimeException('M73 comparison alphabet unavailable.');}
        $value=preg_replace('/[\x{0300}-\x{036F}]/u','',mb_strtolower($value));
        $value=preg_replace('/[\x{2018}\x{2019}\x{201B}\x{2032}\x{0060}´ʼ\']/u','',$value);
        return trim(preg_replace('/\s+/u',' ',preg_replace('/[^\p{L}\p{N}]+/u',' ',$value)));
    }
    public static function presentation(object $block,array $data):?array { return self::release()->presentation($block,$data); }
    public static function validDesign(?array $design):bool { return self::release()->validDesign($design); }
    public static function pointPanels(?array $design):?array { return self::release()->pointPanels($design); }
    public static function correction(array $item,?array $design):?array { return self::release()->correction($item,$design); }
    public static function preserveExcludedTextBlock(object $block,string $caller):?object { return self::release()->preserveExcludedTextBlock($block,$caller); }
    public static function preserveCoursePage(?Page $page):?Page { return self::release()->preserveCoursePage($page); }
}
