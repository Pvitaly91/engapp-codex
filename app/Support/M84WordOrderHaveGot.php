<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Six finite teaching-data owners using the unchanged canonical native release. */
final class M84WordOrderHaveGot
{
    public const BASE_SHA='91c718a119a6d42f180c2a7121fbbbf94ef5afa4';
    public const VERSION='1.0.0';
    public const BEFORE='docs/content/m84-before-source.v1.json';
    public const SOURCE='database/content-patches/m84-word-order-have-got.v1.json';
    public const MASTER='docs/content/m84-word-order-have-got.v1.json';
    public const CONTRACT='docs/content/theory-sentence-builders/m84-word-order-have-got.v1.json';
    public const CHECKSUMS='docs/content/m84-source-checksums.v1.0.1.json';
    public const BINDINGS='docs/content/m84-native-bindings.v1.json';
    public const NAVIGATION='docs/content/m84-navigation-review.v1.json';
    public const BEFORE_SHA='b66e54e60d83e36447f5137282e1b83bf361b4239f5b1e377a7af742e3586fd3';
    public const SOURCE_SHA='61df54db3ad439f0aefc40d4d361f5782d8188de8bc10abdfe3598a97f6d2876';
    public const MASTER_SHA='44faea66143a762baf255acd212937674da71cedc70d81b56520ea9b3ddb30a7';
    public const CONTRACT_SHA='6420871ba6d08a98764fe087aee8837ed701216cc45179363cd5fa42ed33e8bd';
    public const CHECKSUMS_SHA='74a9bd8d44ed9aee037fb7cdf232bcd68b43eca330b0dd89d09a9259462b5c0a';
    public const BINDINGS_SHA='097b51143d9ee1ad0294ef9fb9f2e54f7606d8da22923634d52b8743037da47a';
    public const NAVIGATION_SHA='704215158e6eca4af25f93451dec446aeda0257ebd2c6f8e96cf29014275ed6d';
    public const OWNERS=[
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\BasicWordOrderTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\WordOrderQuestionsNegativesTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\WordOrderAdverbsAdverbialsTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\WordOrderVerbsObjectsTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\AdvancedWordOrderEmphasisTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarHaveGotHasGotTheorySeeder',
    ];
    public const PAGE_IDS=[256,258,257,259,255,201];
    public const CATEGORY_IDS=[48,48,48,48,48,2];
    public const BANKS=[
        'Database\\Seeders\\V3\\Polyglot\\PolyglotBasicWordOrderAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotWordOrderQuestionsNegativesAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotWordOrderAdverbsAdverbialsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotWordOrderVerbsObjectsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotHaveGotHasGotAllLevelsLessonSeeder',
    ];
    public const SLUGS=['theory-basic-word-order','theory-word-order-questions-negatives',
        'theory-word-order-adverbs-adverbials','theory-word-order-verbs-objects','theory-advanced-word-order-emphasis','have-got-has-got'];
    public const ANCESTRIES=[['basic-grammar','word-order'],['basic-grammar','word-order'],['basic-grammar','word-order'],
        ['basic-grammar','word-order'],['basic-grammar','word-order'],['basic-grammar']];
    public const LEVELS=[null,null,null,null,null,null];
    public const FOCUS_LEVELS=['A1','A1','A2','A2','B2','A1'];
    public const DISPLAY_LEVELS=['A1–A2','A1–A2','A2–B1','A2–B1','B1–B2','A1'];
    public const CONTINUING_COUNTS=[10,10,12,10,11,5];
    public const PRACTICE_SOURCE_INDICES=[8,8,10,8,9,5];
    public const NAVIGATION_SOURCE_INDICES=[9,9,11,9,10,null];
    public const EXISTING_PRACTICE_UUIDS=['7cc46418-51ed-5f75-b880-9afda56b5739','86092f77-4036-5f6e-b5cc-bde8c2111452',
        'f9d22b4c-40d7-52f2-9f3d-8d1466b804bf','801cbd62-190e-5ce6-9feb-92719d7e1d9a','47388a9c-40d0-589e-8584-f46b3af68f13',null];
    public const RENDERED_TITLES=['Basic Word Order — Базовий порядок слів',
        'Word Order in Questions and Negatives — Порядок слів у питаннях і запереченнях',
        'Word Order with Adverbs and Adverbials — Прислівники й обставини',
        'Word Order with Verbs and Objects — Дієслова й додатки',
        'Advanced Word Order / Emphasis — Інверсія й акцент','Have got / has got'];

    public static function draftProfile(array $bindings):array
    {
        return ['label'=>'M84','base_sha'=>self::BASE_SHA,'version'=>self::VERSION,
            'marker'=>'m84_native_v1','detail_marker'=>'m84_detail_key','uuid_prefix'=>'m84:',
            'design'=>'m84-native-v1','bindings'=>$bindings];
    }
    private static function readFrozen(string $path,string $sha,?string $root=null):array
    {
        $root??=dirname(__DIR__,2);$file=$root.'/'.$path;
        if(preg_match('/^[a-f0-9]{64}$/D',$sha)!==1||!is_file($file)||is_link($file)||!hash_equals($sha,hash_file('sha256',$file))){
            throw new RuntimeException('M84 pending or changed immutable artifact: '.$path);
        }return json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
    }
    public static function profile():array
    {
        $bindings=self::readFrozen(self::BINDINGS,self::BINDINGS_SHA);
        if(($bindings['base_sha']??null)!==self::BASE_SHA||array_column($bindings['bindings']??[],'identity')!==self::OWNERS){
            throw new RuntimeException('M84 exact binding vector differs.');
        }
        return self::draftProfile($bindings['bindings'])+['artifacts'=>[
            self::BEFORE=>self::BEFORE_SHA,self::SOURCE=>self::SOURCE_SHA,self::MASTER=>self::MASTER_SHA,
            self::CONTRACT=>self::CONTRACT_SHA,self::CHECKSUMS=>self::CHECKSUMS_SHA,self::BINDINGS=>self::BINDINGS_SHA,
            self::NAVIGATION=>self::NAVIGATION_SHA]];
    }
    private static function release():TheoryNativeContentRelease{return new TheoryNativeContentRelease(self::profile());}
    public static function load(?string $root=null):array{return self::release()->load($root);}
    public static function definition(array $lesson,array $before):array{return self::release()->definition($lesson,$before);}
    public static function validate(array $before,array $patch,array $master,?array $contract=null):void
    {
        $bytes=json_encode($before,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
        if(!hash_equals(self::BEFORE_SHA,hash('sha256',$bytes))){throw new RuntimeException('M84 independent public source BEFORE differs.');}
        self::release()->validate($before,$patch,$master,$contract);self::assertEditorial($before,$patch,$master);
    }
    public static function assertEditorial(array $before,array $patch,array $master,?array $navigation=null):void
    {
        $navigation??=self::readFrozen(self::NAVIGATION,self::NAVIGATION_SHA);
        if(($before['content_type']??null)!=='M84_source_before'||($patch['content_type']??null)!=='M84_Word_Order_Have_Got'
            ||array_column($before['targets']??[],'identity')!==self::OWNERS
            ||array_column($master['lessons']??[],'identity')!==self::OWNERS
            ||array_column($navigation['targets']??[],'identity')!==self::OWNERS){throw new RuntimeException('M84 finite author/source/navigation scope differs.');}
        $graded=0;$demos=0;$sentences=0;$ids=[];
        foreach($master['lessons'] as $i=>$lesson){
            $old=$before['targets'][$i]['before'];$blocks=$lesson['blocks']??[];$count=self::CONTINUING_COUNTS[$i];
            if(($lesson['preserved_h1']??null)!==self::RENDERED_TITLES[$i]||($lesson['raw_title']??null)!==$old['page']['title']
                ||($lesson['intended_own_practice_level']??null)!==self::FOCUS_LEVELS[$i]
                ||!array_key_exists('technical_block_level',$lesson)||$lesson['technical_block_level']!==null
                ||($lesson['display_level']??null)!==self::DISPLAY_LEVELS[$i]||empty($lesson['point_detail_decisions'])
                ||count($blocks)!==$count+($i===5?1:0)){throw new RuntimeException('M84 retained identity/levels/block vector differs.');}
            foreach($old['page']['blocks'] as $slot=>$source){
                $after=$blocks[$slot]??[];
                if(($after['existing_source_index']??null)!==$slot||$after['type']!==$source['type']||$after['column']!==$source['column']
                    ||!array_key_exists('level',$after)||$after['level']!==null){throw new RuntimeException('M84 continuing native metadata changed.');}
            }
            $slot=self::PRACTICE_SOURCE_INDICES[$i];$practice=$blocks[$slot];$data=$practice['data'];
            if($practice['type']!=='practice-set'||$practice['column']!=='left'||$practice['level']!==null||isset($data['intro'])){
                throw new RuntimeException('M84 exact native practice/visible-criterion contract differs.');
            }
            $banks=$i<5?[self::BANKS[$i],M84QuestionSupplement::BANKS[$i]]:[self::BANKS[$i]];
            if(($data['linked_practice']['source']??null)!=='theory_links'
                ||($data['linked_practice']['question_types']??null)!==['4']
                ||($data['linked_practice']['seeder_classes']??null)!==$banks){
                throw new RuntimeException('M84 exact thematic banks differ; a course bank cannot replace Have got all-levels.');
            }
            if($i<5&&M26DetailPackage::uuid(self::OWNERS[$i],$old['page']['blocks'][$slot],$slot+1)!==self::EXISTING_PRACTICE_UUIDS[$i]){
                throw new RuntimeException('M84 independently captured practice UUID differs.');
            }
            $oldData=$i===5?[]:json_decode($old['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR);
            $ledger=$master['editorial_review']['task_map'][self::OWNERS[$i]]??[];$seenOld=[];$seenAfter=[];
            foreach($ledger as $row){
                $g=$row['after_group']??'';$n=$row['after_group_index']??null;$task=$data[$g][$n]??null;
                if(!is_array($task)||($row['after_task']??null)!==$task||isset($seenAfter[$g.':'.$n])
                    ||empty($row['learning_function'])||empty($row['change_reason'])){throw new RuntimeException('M84 exact task ledger is incomplete/stale.');}
                $seenAfter[$g.':'.$n]=true;$og=$row['old_group']??null;$on=$row['old_group_index']??null;
                if($og!==null){
                    if(($row['old_task']??null)!==($oldData[$og][$on]??null)||isset($seenOld[$og.':'.$on])||$og!==$g||$on!==$n){throw new RuntimeException('M84 old task/demo lost or moved without review.');}
                    $seenOld[$og.':'.$on]=true;
                    foreach(['id','source_index'] as $field){if(array_key_exists($field,$task)!==array_key_exists($field,$row['old_task'])
                        ||(array_key_exists($field,$task)&&$task[$field]!==$row['old_task'][$field])){throw new RuntimeException('M84 retained task identity/absence changed.');}}
                }elseif(!is_string($task['id']??null)||!str_starts_with($task['id'],'m84-')){throw new RuntimeException('M84 new task needs stable ID.');}
            }
            foreach(['selects'=>$i<5?3:2,'choices'=>2,'inputs'=>$i<5?3:2,'rephrase'=>$i<5?3:0] as $group=>$expected){
                if(count($data[$group]??[])!==$expected){throw new RuntimeException('M84 retained/new task count differs.');}
                foreach($data[$group]??[] as $n=>$task){
                    if(!isset($seenAfter[$group.':'.$n])){throw new RuntimeException('M84 unmapped after control.');}
                    if($group==='rephrase'&&$n===0&&$i<5){if(empty($task['example_original'])||empty($task['example_target'])||isset($task['answer'])){throw new RuntimeException('M84 demo must stay ungraded.');}$demos++;continue;}
                    self::assertTask($task,$group,$data,$ids);$graded++;
                    if($group==='inputs'){self::assertSentence($task);$sentences++;}
                }
                foreach($oldData[$group]??[] as $n=>$_){if(!isset($seenOld[$group.':'.$n])){throw new RuntimeException('M84 old control/demo missing.');}}
            }
            $navSlot=self::NAVIGATION_SOURCE_INDICES[$i];$nav=$navigation['targets'][$i];
            if($navSlot===null){if(($nav['items']??null)!==[]){throw new RuntimeException('M84 Have got had no old navigation.');}continue;}
            $oldNav=json_decode($old['page']['blocks'][$navSlot]['body'],true,flags:JSON_THROW_ON_ERROR);
            $items=$blocks[$navSlot]['data']['items'];$labels=static fn($xs)=>array_map(static fn($x)=>array_intersect_key($x,array_flip(['label','current'])),$xs);
            if(($nav['items']??null)!==$items||$labels($items)!==$labels($oldNav['items'])){throw new RuntimeException('M84 footer label/current/order changed.');}
            foreach($items as $item){if(empty($item['current'])&&preg_match('~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)*$~D',$item['url']??'')!==1){throw new RuntimeException('M84 explicit canonical footer destination missing.');}}
        }
        if([$graded,$demos,$sentences]!==[56,5,17]){throw new RuntimeException('M84 graded/demo/full-sentence populations conflated.');}
    }
    private static function assertTask(array $task,string $group,array $data,array &$ids):void
    {
        if(isset($task['strict'])||isset($task['punctuation_sensitive'])||($group==='selects'&&isset($task['prompt']))
            ||!is_array($task['accepted']??null)||!in_array($task['answer']??null,$task['accepted'],true)){throw new RuntimeException('M84 explicit native accepted contract differs.');}
        if(isset($task['id'])){if(isset($ids[$task['id']])){throw new RuntimeException('M84 repeated task ID.');}$ids[$task['id']]=true;}
        foreach($task['feedback']??[] as $key=>$value){if($key!==mb_strtolower(trim($key))||!self::plain($value)){throw new RuntimeException('M84 feedback must use plain-text native keys.');}}
        foreach($task['accepted'] as $answer){if(!self::plain($answer)||!self::plain($task['feedback'][mb_strtolower(trim($answer))]??null)){throw new RuntimeException('M84 alias lacks reviewed plain feedback.');}}
        if(in_array($group,['selects','choices'],true)){
            $options=$task['options']??($group==='selects'?($data['options']??[]):($data['choice_options']??['a','b']));
            $keys=array_map(self::comparisonKey(...),$options);$correct=0;
            if(count($options)<2||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)||empty($task['label'])){throw new RuntimeException('M84 option collision/visible criterion missing.');}
            foreach($options as $option){if(!self::plain($task['feedback'][mb_strtolower(trim($option))]??null)){throw new RuntimeException('M84 option feedback missing.');}
                foreach($task['accepted'] as $answer){if(in_array(self::comparisonKey($option),array_map(self::comparisonKey(...),AcceptedAnswerVariants::for($answer)),true)){$correct++;break;}}}
            if($correct!==1){throw new RuntimeException('M84 displayed options need exactly one native-correct choice.');}
        }
        if($group==='rephrase'&&(empty($task['original'])||($task['placeholder']??null)===$task['answer'])){throw new RuntimeException('M84 rephrase criterion missing or answer leaked.');}
    }
    private static function assertSentence(array $task):void
    {
        $tokens=array_map('trim',explode('/',$task['before']??''));$remaining=$tokens;$tail=$task['answer'];
        if(count($tokens)<2||implode(' ',$tokens)===$tail||isset($task['hint'])||isset($task['placeholder'])
            ||preg_match('/[\x{0400}-\x{04FF}]/u',$task['after']??'')!==1){throw new RuntimeException('M84 full sentence needs shuffled bank and visible UK condition.');}
        foreach($tokens as $t){if($t===''||count(preg_split('/\s+/u',$t))>3){throw new RuntimeException('M84 token needs one to three words.');}}
        while($tail!==''&&$remaining!==[]){$found=false;foreach($remaining as $n=>$t){if($tail===$t||str_starts_with($tail,$t.' ')){$tail=$tail===$t?'':substr($tail,strlen($t)+1);unset($remaining[$n]);$found=true;break;}}
            if(!$found){throw new RuntimeException('M84 literal bank cannot compose canonical sentence.');}}
        if($tail!==''||$remaining!==[]){throw new RuntimeException('M84 token multiset loses/duplicates a group.');}
    }
    private static function plain(mixed $v):bool{return is_string($v)&&trim($v)!==''&&preg_match('/<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$v)!==1;}
    public static function comparisonKey(string $v):string{return M76PronounRelations::comparisonKey($v);}
    public static function presentation(object $b,array $d):?array{return self::release()->presentation($b,$d);}
    public static function validDesign(?array $d):bool{return self::release()->validDesign($d);}
    public static function pointPanels(?array $d):?array{return self::release()->pointPanels($d);}
    public static function correction(array $i,?array $d):?array{return self::release()->correction($i,$d);}
    public static function preserveExcludedTextBlock(object $b,string $c):?object{return self::release()->preserveExcludedTextBlock($b,$c);}
    public static function preserveCoursePage(?Page $p):?Page{return self::release()->preserveCoursePage($p);}
}
