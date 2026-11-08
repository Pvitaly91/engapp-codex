<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite M74 educational descriptor. Presentation and grading remain native. */
final class M74OtherPronounsBasics
{
    public const BASE_SHA = 'fd1ad30e710b3d950e574848c886d86f6045f2e3';
    public const BEFORE = 'docs/content/m74-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m74-other-pronouns-basics.v1.0.1.json';
    public const MASTER = 'docs/content/m74-other-pronouns-basics.v1.0.1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/revisions/m74-other-pronouns-basics.v1.0.1.json';
    public const CHECKSUMS = 'docs/content/m74-source-checksums.v1.0.1.json';
    public const BEFORE_SHA = '9aac299f76f4618af7294b5542010385778110d03e239a576a435c93497a1769';
    public const SOURCE_SHA = '7346e58f395daaceaec2d2630f66ee49b52794db958e83ecc82d50543bcb77a8';
    public const MASTER_SHA = '118d754ab5fc36c8e941a285240ec9156b4f214f9d9bd760a231cc26a6697fd2';
    public const CONTRACT_SHA = '74b1df94815b59ba6c75741d39410cf1a2075c50e21ec42e64387546e7fd7849';
    public const CHECKSUMS_SHA = '57fda6d76d2431a6dba9c5a0fac935f5d7666c776d15ce5638a42b850d841479';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityAnotherOtherTheOtherTheorySeeder',
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPronounsTheorySeeder',
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPersonalObjectPronounsTheorySeeder',
    ];
    public const SLUGS = ['another-other-the-other','pronouns-theory','personal-object-pronouns'];
    public const PAGE_IDS = [81,126,124];
    public const CATEGORY_IDS = [16,20,20];
    public const ANCESTRIES = [['imennyky-artykli-ta-kilkist'],['zaimennyky-ta-vkazivni-slova'],['zaimennyky-ta-vkazivni-slova']];
    public const LEVELS = ['B1',null,null];
    public const DISPLAY_LEVELS = ['B1','A1–B1','A1'];
    public const CONTINUING_COUNTS = [6,12,10];
    public const CONTINUING_LEVELS = [
        ['B1','B1','B1','B1','B1',null],
        [null,null,null,null,null,null,null,null,null,null,null,null],
        [null,null,null,null,null,null,null,null,null,null],
    ];
    public const PRACTICE_SOURCE_INDICES = [6,10,8];
    public const RENDERED_TITLES = ['Another / Other / The Other','Pronouns','Personal / Object Pronouns'];

    public static function profile(): array
    {
        $bindings=[];
        foreach(self::OWNERS as $i=>$owner){
            $binding=['identity'=>$owner,'slug'=>self::SLUGS[$i],'page_id'=>self::PAGE_IDS[$i],
                'category_id'=>self::CATEGORY_IDS[$i],'ancestry'=>self::ANCESTRIES[$i],
                'local_url'=>'http://gramlyze.loc/theory/'.self::ANCESTRIES[$i][0].'/'.self::SLUGS[$i],
                'practice_level'=>self::LEVELS[$i],'display_level'=>self::DISPLAY_LEVELS[$i],
                'rendered_title'=>self::RENDERED_TITLES[$i],'continuing_levels'=>self::CONTINUING_LEVELS[$i],
                'appended_blocks'=>$i===0?[['key'=>'another-practice','type'=>'practice-set','column'=>'left','level'=>'B1']]:[]];
            if($i>0){
                $binding['existing_practice']=['source_index'=>self::PRACTICE_SOURCE_INDICES[$i],
                    'uuid'=>$i===1?'b00124f3-ec4d-54fb-91b8-b6e5becaf4b9':'1613f5c2-8294-57fd-bbd8-ea49c51a4df4','level'=>null];
                $binding['practice_contract']=['counts'=>['selects'=>$i===1?4:5,'choices'=>2,'inputs'=>$i===1?5:6],
                    'sentence_input_indices'=>$i===1?[3,4]:[4,5]];
            }
            if($i===2){
                $binding['before_subtitle']=['mode'=>'captured-plain','sha256'=>'2adab9ac9a1886f3f32ed98d744e2f3b1f916c91c1dd1843e9aff37877ae9175',
                    'raw_page_title'=>self::RENDERED_TITLES[$i],'extracted_title'=>self::RENDERED_TITLES[$i],'h1'=>self::RENDERED_TITLES[$i]];
                $binding['preserved_plain_subtitle']=['sha256'=>'d65b1652ffe6a2618f4c1a9dc02c69b0b2c7b734576332049298f78f317ea7ee','extracted_title'=>self::RENDERED_TITLES[$i],'h1'=>self::RENDERED_TITLES[$i]];
            }
            $bindings[]=$binding;
        }
        return ['label'=>'M74','base_sha'=>self::BASE_SHA,'version'=>'1.0.1','marker'=>'m74_native_v1',
            'detail_marker'=>'m74_detail_key','uuid_prefix'=>'m74:','design'=>'m74-native-v1',
            'artifacts'=>[self::BEFORE=>self::BEFORE_SHA,self::SOURCE=>self::SOURCE_SHA,self::MASTER=>self::MASTER_SHA,
                self::CONTRACT=>self::CONTRACT_SHA,self::CHECKSUMS=>self::CHECKSUMS_SHA],'bindings'=>$bindings];
    }
    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root=null):array { return self::release()->load($root); }
    public static function definition(array $lesson,array $before):array { return self::release()->definition($lesson,$before); }
    public static function validate(array $before,array $patch,array $master,?array $contract=null):void
    {
        if(($before['content_type']??null)!=='M74_source_before'||($patch['content_type']??null)!=='M74_Other_Pronouns_Basics'){
            throw new RuntimeException('M74 exact provenance differs.');
        }
        self::release()->validate($before,$patch,$master,$contract);
        foreach($master['lessons'] as $i=>$lesson){
            $old=$before['targets'][$i]['before']['page']['blocks'];
            $continuing=array_slice($lesson['blocks'],0,self::CONTINUING_COUNTS[$i]);
            if(array_column($old,'type')!==array_column($continuing,'type')||array_column($old,'column')!==array_column($continuing,'column')){
                throw new RuntimeException('M74 continuing components/columns differ.');
            }
            $data=$lesson['blocks'][self::PRACTICE_SOURCE_INDICES[$i]]['data'];$graded=0;
            foreach(['selects','choices','inputs','rephrase'] as $group){
                foreach($data[$group]??[] as $n=>$task){
                    if($group==='rephrase'&&$n===0&&!empty($task['example_original'])){continue;}
                    ++$graded;
                    if(!is_string($task['answer']??null)||trim($task['answer'])===''||!is_array($task['accepted']??null)
                        ||!in_array($task['answer'],$task['accepted'],true)||array_filter($task['accepted'],static fn($s)=>!is_string($s)||trim($s)==='')){
                        throw new RuntimeException('M74 nonempty deliberate accepted answer required.');
                    }
                    foreach($task['accepted'] as $answer){
                        if(self::comparisonKey($answer)===''||empty($task['feedback'][mb_strtolower(trim($answer))])){
                            throw new RuntimeException('M74 accepted key/actual literal feedback is missing.');
                        }
                    }
                    if(in_array($group,['selects','choices'],true)){
                        $options=$task['options']??($data['choice_options']??[]);
                        if(!is_array($options)||!array_is_list($options)||count($options)<2
                            ||array_filter($options,static fn($s)=>!is_string($s)||trim($s)==='')){
                            throw new RuntimeException('M74 finite visible options must be nonempty.');
                        }
                        $keys=array_map(self::comparisonKey(...),$options);
                        if(!in_array($task['answer'],$options,true)||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)){
                            throw new RuntimeException('M74 visible option/key mismatch or normalization collision.');
                        }
                        $accepted=array_map(self::comparisonKey(...),$task['accepted']);
                        foreach($options as $option){
                            if(!in_array($option,$task['accepted'],true)&&in_array(self::comparisonKey($option),$accepted,true)){
                                throw new RuntimeException('M74 distractor collides with accepted key.');
                            }
                        }
                    }
                    foreach($task['feedback']??[] as $key=>$text){
                        if(!is_string($key)||$key===''||$key!==mb_strtolower(trim($key))||!is_string($text)||trim($text)===''
                            ||preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)){
                            throw new RuntimeException('M74 feedback must be literal-lower-keyed plain text.');
                        }
                    }
                }
            }
            if($graded!==[6,13,16][$i]){throw new RuntimeException('M74 exact learning-function count differs.');}
            if($i>0){
                if(count($data['rephrase']??[])!==($i===1?3:4)){
                    throw new RuntimeException('M74 retained correction positions differ.');
                }
                foreach(['selects'=>$i===1?4:5,'inputs'=>$i===1?3:4,'rephrase'=>$i===1?3:4] as $group=>$count){
                    foreach(array_slice($data[$group]??[],0,$count) as $task){
                        if(array_key_exists('id',$task)||array_key_exists('source_index',$task)){throw new RuntimeException('M74 retained control identity changed.');}
                    }
                }
                if(empty($data['rephrase'][0]['example_original'])||isset($data['rephrase'][0]['answer'])){
                    throw new RuntimeException('M74 ungraded retained demo differs.');
                }
                if($i===1&&(($data['rephrase'][1]['answer']??null)!=='речення Б'
                    ||($data['rephrase'][1]['accepted']??null)!==['речення Б','Б'])){
                    throw new RuntimeException('M74 apostrophe-only task must retain its semantic UK transfer.');
                }
            }
            $navOld=json_decode($old[self::CONTINUING_COUNTS[$i]-1]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];
            $navNew=$lesson['blocks'][self::CONTINUING_COUNTS[$i]-1]['data']['items'];
            $labels=static fn($items)=>array_map(static fn($v)=>array_intersect_key($v,array_flip(['label','current'])),$items);
            if(($i===0?$navOld!==$navNew:$labels($navOld)!==$labels($navNew))||count($navNew)!==[8,9,9][$i]){
                throw new RuntimeException('M74 navigation labels/current state/explicit URLs differ.');
            }
        }
    }
    /** Validation only; the existing native comparison alphabet/grader is unchanged. */
    private static function comparisonKey(string $value):string
    {
        $value=\Normalizer::normalize($value,\Normalizer::FORM_KD);
        if(!is_string($value)){throw new RuntimeException('M74 comparison alphabet unavailable.');}
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
