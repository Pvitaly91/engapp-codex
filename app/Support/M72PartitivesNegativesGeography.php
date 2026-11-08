<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite content-only release; the accepted native template and grader own presentation. */
final class M72PartitivesNegativesGeography
{
    public const BASE_SHA = '2bcd468b25af3c44dcf34ccee0e012f3f683335d';
    public const BEFORE = 'docs/content/m72-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m72-partitives-negatives-geography.v1.json';
    public const MASTER = 'docs/content/m72-partitives-negatives-geography.v1.0.0.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m72-partitives-negatives-geography.v1.json';
    public const CHECKSUMS = 'docs/content/m72-source-checksums.v1.json';
    public const BEFORE_SHA = '2567255a6e63255a474a6b6041ab2e99c69298a1b5ba0624e488ae44d09239bb';
    public const SOURCE_SHA = '4437f03a77bac1cb9e7f49f73f0c68f0482d9708f2ffad8804972b07d74d84a3';
    public const MASTER_SHA = '253da61264abd8544be816342b8e3522b11870c1f0ba1722b8b976eabc2f232c';
    public const CONTRACT_SHA = 'baf496f103c9056cd03b1010e51dadee614df55afdd8e3fb9018b67c9dea6f22';
    public const CHECKSUMS_SHA = 'a7b32c1f05eb75c760073fcc53a5e0e752b045d788c0de46e116ad392536a4a2';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPartitivesTheorySeeder',
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder',
        'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder',
    ];
    public const SLUGS = ['partitives-with-uncountable-nouns-a-piece-of-a-cup-of','no-none-neither-either','articles-with-geographical-names'];
    public const PAGE_IDS = [88,87,83];
    public const CATEGORY_IDS = [16,16,16];
    public const ANCESTRIES = [['imennyky-artykli-ta-kilkist'],['imennyky-artykli-ta-kilkist'],['imennyky-artykli-ta-kilkist']];
    public const LEVELS = [null,null,null];
    public const DISPLAY_LEVELS = ['A2–B1','B1–B2','A2–B1'];
    public const CONTINUING_COUNTS = [14,13,15];
    public const PRACTICE_SOURCE_INDICES = [12,11,13];
    public const PRACTICE_UUIDS = ['54ce9ed8-8e28-574c-9485-a780a04f9a37','b2fd69af-1520-5e69-a5e9-6d315246ac01','63edb425-3ba4-59e1-bfcd-aa676eac86a1'];
    public const RENDERED_TITLES = ['Partitives: a piece of, a cup of, a bit of','No, none, neither, either','Articles with Geographical Names'];

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
        return ['label'=>'M72','base_sha'=>self::BASE_SHA,'version'=>'1.0.0','marker'=>'m72_native_v1',
            'detail_marker'=>'m72_detail_key','uuid_prefix'=>'m72:','design'=>'m72-native-v1',
            'artifacts'=>[self::BEFORE=>self::BEFORE_SHA,self::SOURCE=>self::SOURCE_SHA,self::MASTER=>self::MASTER_SHA,
                self::CONTRACT=>self::CONTRACT_SHA,self::CHECKSUMS=>self::CHECKSUMS_SHA],'bindings'=>$bindings];
    }
    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root=null):array { return self::release()->load($root); }
    public static function definition(array $lesson,array $before):array { return self::release()->definition($lesson,$before); }
    public static function validate(array $before,array $patch,array $master,?array $contract=null):void
    {
        if(($before['content_type']??null)!=='M72_source_before'||($patch['content_type']??null)!=='M72_Partitives_Negatives_Geography'){
            throw new RuntimeException('M72 exact provenance differs.');
        }
        foreach($master['lessons']??[] as $i=>$lesson){
            $old=$before['targets'][$i]['before']['page']['blocks']??[];
            if(array_column($old,'type')!==array_column($lesson['blocks']??[],'type')
                ||count($old)!==self::CONTINUING_COUNTS[$i]){throw new RuntimeException('M72 continuing native slots differ.');}
            $data=$lesson['blocks'][self::PRACTICE_SOURCE_INDICES[$i]]['data']??[];
            foreach(['selects','choices','inputs','rephrase'] as $group){
                foreach($data[$group]??[] as $n=>$task){
                    if($group==='rephrase'&&$n===0&&!empty($task['example_original'])){continue;}
                    if(!is_string($task['answer']??null)||trim($task['answer'])===''||!in_array($task['answer'],$task['accepted']??[],true)){
                        throw new RuntimeException('M72 canonical answer must be deliberate, nonempty and accepted.');
                    }
                    foreach($task['feedback']??[] as $text){
                        if(!is_string($text)||preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)){
                            throw new RuntimeException('M72 x-text feedback must be plain text.');
                        }
                    }
                }
            }
            foreach(['selects'=>4,'inputs'=>3,'rephrase'=>3] as $group=>$count){
                foreach(array_slice($data[$group]??[],0,$count) as $task){
                    if(array_key_exists('id',$task)||array_key_exists('source_index',$task)){throw new RuntimeException('M72 retained control identity changed.');}
                }
            }
            if(count($data['rephrase']??[])!==3||empty($data['rephrase'][0]['example_original'])){
                throw new RuntimeException('M72 retained corrections or ungraded demo lost.');
            }
            // Content validation mirrors the accepted factory's comparison alphabet;
            // it does not grade learner answers or change the runtime normalizer.
            $normal=static function(string $value):string{
                $value=\Normalizer::normalize($value,\Normalizer::FORM_KD);
                if(!is_string($value)){throw new RuntimeException('M72 comparison alphabet unavailable.');}
                $value=preg_replace('/[\x{0300}-\x{036F}]/u','',mb_strtolower($value));
                $value=preg_replace('/[\x{2018}\x{2019}\x{201B}\x{2032}`´ʼ\']/u','',$value);
                return trim(preg_replace('/\s+/u',' ',preg_replace('/[^\p{L}\p{N}]+/u',' ',$value)));
            };
            foreach(['selects','choices'] as $group){foreach($data[$group] as $task){
                $options=$task['options']??[];$keys=array_map($normal,$options);
                if(!in_array($task['answer'],$options,true)){throw new RuntimeException('M72 visible option/key differs.');}
                if(count($options)<2||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)){
                    throw new RuntimeException('M72 normalized option collision or empty choice.');
                }
                $accepted=array_map($normal,$task['accepted']);
                foreach($options as $option){
                    if(!in_array($option,$task['accepted'],true)&&in_array($normal($option),$accepted,true)){
                        throw new RuntimeException('M72 distractor collides with an accepted answer.');
                    }
                }
            }}
            // Geography's retained zero-article controls now require complete nonempty sentences.
            if($i===2){foreach($data['inputs'] as $task){
                if(!str_contains($task['answer'],' ')||in_array(mb_strtolower($task['answer']),['the','ø','без артикля'],true)){
                    throw new RuntimeException('M72 geography answer is a complete sentence, never an empty zero label.');
                }
            }}
            $oldNav=json_decode($old[count($old)-1]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];
            $newNav=$lesson['blocks'][count($old)-1]['data']['items']??[];
            if(array_map(static fn($v)=>array_intersect_key($v,array_flip(['label','current'])),$newNav)!==$oldNav){
                throw new RuntimeException('M72 existing navigation labels/current states changed.');
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
