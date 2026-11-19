<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite educational policy only; native components and grading are unchanged. */
final class M75PronounForms
{
    public const BASE_SHA = '67ebba5ed98fae05ca6022cac802870cfacf0d52';
    public const BEFORE = 'docs/content/m75-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m75-possessive-reflexive-indefinite.v1.json';
    public const MASTER = 'docs/content/m75-possessive-reflexive-indefinite.v1.0.0.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m75-possessive-reflexive-indefinite.v1.json';
    public const CHECKSUMS = 'docs/content/m75-source-checksums.v1.0.0.json';
    // Exact reviewed UTF-8 artifact bytes; browser acceptance is recorded separately.
    public const BEFORE_SHA = '93ad3acd46790de520b4ef39d0f16d0b7300e43f77db5b111df8fe2a508c0e97';
    public const SOURCE_SHA = '099f4173f18713ca568c15ba72e49a6e4e77789b2880507549a25b01ab22eaa5';
    public const MASTER_SHA = '817b280b6e59bc60198a717b4b965238fa8d3ebcbc8a5181a8c909687db1f6e9';
    public const CONTRACT_SHA = '3ad762f87af6b32c23df64d1ca3baf02993fdb6ba9760a1d79ae168dccb130a1';
    public const CHECKSUMS_SHA = '404c61c18fea10a6ded1d7fc71551e4979325bb9b8c60dedbfaf53406a96b4d8';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder',
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesReflexivePronounsTheorySeeder',
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesIndefinitePronounsTheorySeeder',
    ];
    public const SLUGS = ['possessive-adjectives-vs-pronouns-my-mine-your-yours','reflexive-pronouns-myself-yourself-themselves','indefinite-pronouns-someone-anyone-nobody-nothing'];
    public const PAGE_IDS = [125,128,122];
    public const CATEGORY_IDS = [20,20,20];
    public const ANCESTRIES = [['zaimennyky-ta-vkazivni-slova'],['zaimennyky-ta-vkazivni-slova'],['zaimennyky-ta-vkazivni-slova']];
    public const LEVELS = [null,null,null];
    public const DISPLAY_LEVELS = ['A1–A2','A2–B1','A2–B1'];
    public const CONTINUING_COUNTS = [11,11,12];
    public const PRACTICE_SOURCE_INDICES = [9,9,10];
    public const PRACTICE_UUIDS = ['135baaed-d147-599a-b147-5b2c93924300','aedf70d6-fcc4-57b0-b303-c98548d17060','57ef1463-3d71-5a26-971a-53022c7be0b7'];
    public const RENDERED_TITLES = ['Possessive Forms','Зворотні займенники','Indefinite pronouns'];

    public static function profile(): array
    {
        $bindings=[];
        foreach(self::OWNERS as $i=>$owner){
            $binding=['identity'=>$owner,'slug'=>self::SLUGS[$i],'page_id'=>self::PAGE_IDS[$i],
                'category_id'=>20,'ancestry'=>self::ANCESTRIES[$i],
                'local_url'=>'http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/'.self::SLUGS[$i],
                'practice_level'=>null,'display_level'=>self::DISPLAY_LEVELS[$i],
                'rendered_title'=>self::RENDERED_TITLES[$i],'continuing_levels'=>array_fill(0,self::CONTINUING_COUNTS[$i],null),
                'appended_blocks'=>[],
                'existing_practice'=>['source_index'=>self::PRACTICE_SOURCE_INDICES[$i],'uuid'=>self::PRACTICE_UUIDS[$i],'level'=>null],
                'practice_contract'=>['counts'=>['selects'=>5,'choices'=>2,'inputs'=>6],'sentence_input_indices'=>[4,5]]];
            if($i===0){
                $binding['before_subtitle']=['mode'=>'captured-plain','sha256'=>'de220b6ed52b35b944e69568921d651e55b16fc2f528027fd629f05e6ab8cc07',
                    'raw_page_title'=>'Possessive Forms','extracted_title'=>'Possessive Forms','h1'=>'Possessive Forms'];
                $binding['preserved_plain_subtitle']=['sha256'=>'4dc3249164823b508cf2bdbf30ad58f5a7f1302717392420c0bc01669d7a2f4b','extracted_title'=>'Possessive Forms','h1'=>'Possessive Forms'];
            }
            $bindings[]=$binding;
        }
        return ['label'=>'M75','base_sha'=>self::BASE_SHA,'version'=>'1.0.0','marker'=>'m75_native_v1',
            'detail_marker'=>'m75_detail_key','uuid_prefix'=>'m75:','design'=>'m75-native-v1',
            'artifacts'=>[self::BEFORE=>self::BEFORE_SHA,self::SOURCE=>self::SOURCE_SHA,self::MASTER=>self::MASTER_SHA,
                self::CONTRACT=>self::CONTRACT_SHA,self::CHECKSUMS=>self::CHECKSUMS_SHA],'bindings'=>$bindings];
    }
    private static function release():TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root=null):array { return self::release()->load($root); }
    public static function definition(array $lesson,array $before):array { return self::release()->definition($lesson,$before); }
    public static function validate(array $before,array $patch,array $master,?array $contract=null):void
    {
        if(($before['content_type']??null)!=='M75_source_before'||($patch['content_type']??null)!=='M75_Pronoun_Forms'){
            throw new RuntimeException('M75 exact provenance differs.');
        }
        self::release()->validate($before,$patch,$master,$contract);
        foreach($master['lessons'] as $i=>$lesson){
            $old=$before['targets'][$i]['before']['page']['blocks'];
            if(array_column($old,'type')!==array_column($lesson['blocks'],'type')||array_column($old,'column')!==array_column($lesson['blocks'],'column')){
                throw new RuntimeException('M75 continuing native components/columns changed.');
            }
            $data=$lesson['blocks'][self::PRACTICE_SOURCE_INDICES[$i]]['data'];$graded=0;
            foreach(['selects','choices','inputs','rephrase'] as $group){foreach($data[$group]??[] as $n=>$task){
                if($group==='rephrase'&&$n===0&&!empty($task['example_original'])){continue;}
                ++$graded;
                if(!is_string($task['answer']??null)||trim($task['answer'])===''||!is_array($task['accepted']??null)
                    ||!in_array($task['answer'],$task['accepted'],true)||array_filter($task['accepted'],static fn($s)=>!is_string($s)||trim($s)==='')){
                    throw new RuntimeException('M75 nonempty accepted answers required.');
                }
                foreach($task['accepted'] as $answer){if(self::comparisonKey($answer)===''||empty($task['feedback'][mb_strtolower(trim($answer))])){
                    throw new RuntimeException('M75 literal accepted-answer feedback is missing.');
                }}
                if(in_array($group,['selects','choices'],true)){
                    $options=$task['options']??($data['choice_options']??[]);$keys=array_map(self::comparisonKey(...),$options);
                    if(count($options)<2||!in_array($task['answer'],$options,true)||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)){
                        throw new RuntimeException('M75 finite options collide or have no canonical answer.');
                    }
                    foreach($options as $option){if(empty($task['feedback'][mb_strtolower(trim($option))])){throw new RuntimeException('M75 visible option feedback is missing.');}}
                }
                foreach($task['feedback']??[] as $key=>$text){
                    if(!is_string($key)||$key===''||$key!==mb_strtolower(trim($key))||!is_string($text)||trim($text)===''
                        ||preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)){
                        throw new RuntimeException('M75 feedback must be plain text with literal lower-case keys.');
                    }
                }
            }}
            if($graded!==16||count($data['rephrase']??[])!==4||empty($data['rephrase'][0]['example_original'])||isset($data['rephrase'][0]['answer'])){
                throw new RuntimeException('M75 sixteen graded tasks and one old demo required.');
            }
            foreach(['selects'=>5,'inputs'=>4,'rephrase'=>4] as $group=>$count){foreach(array_slice($data[$group],0,$count) as $task){
                if(array_key_exists('id',$task)||array_key_exists('source_index',$task)){throw new RuntimeException('M75 old task identity changed.');}
            }}
            if($i===0&&(($data['rephrase'][3]['answer']??null)!=='речення Б'||($data['rephrase'][3]['accepted']??null)!==['речення Б','Б'])){
                throw new RuntimeException('M75 apostrophe ambiguity must use the retained semantic slot.');
            }
            if($i===1){foreach([2,4] as $n){if(($data['selects'][$n]['answer']??null)!=='Займенник не потрібен'){
                throw new RuntimeException('M75 deliberate no-pronoun is distinct from unanswered/reset.');
            }}}
            $navOld=json_decode($old[self::CONTINUING_COUNTS[$i]-1]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];
            $navNew=$lesson['blocks'][self::CONTINUING_COUNTS[$i]-1]['data']['items'];
            $labels=static fn($items)=>array_map(static fn($v)=>array_intersect_key($v,array_flip(['label','current'])),$items);
            if(count($navNew)!==9||$labels($navOld)!==$labels($navNew)){throw new RuntimeException('M75 navigation labels/current state changed.');}
        }
    }
    /** Test-only comparison alphabet mirrors, but never replaces, the unchanged native grader. */
    public static function comparisonKey(string $value):string
    {
        $value=\Normalizer::normalize($value,\Normalizer::FORM_KD);
        if(!is_string($value)){throw new RuntimeException('M75 comparison alphabet unavailable.');}
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
