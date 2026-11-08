<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Six finite native teaching-data owners; no UI, formatter or grader implementation. */
final class M85VerbToBe
{
    public const BASE_SHA='6f7751612a3bc9d8bec2315c052e81f9e2fc0973';
    public const VERSION='1.0.0';
    public const BEFORE='docs/content/m85-before-source.v1.json';
    public const SOURCE='database/content-patches/m85-verb-to-be.v1.0.1.json';
    public const MASTER='docs/content/m85-verb-to-be.v1.0.1.json';
    public const CONTRACT='docs/content/theory-sentence-builders/m85-verb-to-be.v1.json';
    public const CHECKSUMS='docs/content/m85-source-checksums.v1.0.1.json';
    public const BINDINGS='docs/content/m85-native-bindings.v1.json';
    public const NAVIGATION='docs/content/m85-navigation-review.v1.json';
    public const BEFORE_SHA='1433e927e995c76ded7d3abb700d67cc3d2a22824d2248b94fd1356d0f8e12a2';
    public const SOURCE_SHA='d733c8f7c22bbb5b0567a7fb463091b04eaeccf55a01c97a39b9f471fc08612e';
    public const MASTER_SHA='a961ff3072fd6a5a4656b87748db377d016615607289fa2c0b808735afc407ab';
    public const CONTRACT_SHA='903fedfc9b3e7f1ca64eb912dc68a102dcdd79da0abd918400a77a66a0efb7c7';
    public const CHECKSUMS_SHA='f1a4e3630d5154c3c3d8bfc657f04b9e25f34e050b1c96e59a14b0c7f45ee2d0';
    public const BINDINGS_SHA='c9d15def04003c846e0536d9737986d810f2a3c302cf74ca61cdb6e8fddf5bcd';
    public const NAVIGATION_SHA='a5d6aa476bccbc9cf00707768f2f342305865b15bd7cf310c890d28a97ea0500';
    public const OWNERS=[
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder',
    ];
    public const PAGE_IDS=[28,26,29,27,25,24];
    public const CATEGORY_IDS=[8,8,8,8,8,8];
    public const BANKS=[
        'Database\\Seeders\\V3\\Polyglot\\PolyglotVerbToBePresentAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotVerbToBeNegativesAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotVerbToBeQuestionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotVerbToBePastAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotVerbToBeFutureAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotThereIsThereAreAllLevelsLessonSeeder',
    ];
    public const SLUGS=['verb-to-be-present','verb-to-be-negatives','verb-to-be-questions','verb-to-be-past','verb-to-be-future','there-is-there-are'];
    public const ANCESTRIES=[['basic-grammar','verb-to-be'],['basic-grammar','verb-to-be'],['basic-grammar','verb-to-be'],
        ['basic-grammar','verb-to-be'],['basic-grammar','verb-to-be'],['basic-grammar','verb-to-be']];
    public const LEVELS=[null,null,null,null,null,null];
    public const FOCUS_LEVELS=['A1','A1','A1','A2','A2','A1'];
    public const DISPLAY_LEVELS=['A1','A1','A1','A1–A2','A1–A2','A1–A2'];
    public const CONTINUING_COUNTS=[7,7,8,8,8,7];
    public const PRACTICE_SOURCE_INDICES=[7,7,8,8,8,7];
    public const NAVIGATION_SOURCE_INDICES=[6,6,6,7,7,6];
    public const EXISTING_PRACTICE_UUIDS=[null,null,null,null,null,null];
    public const RENDERED_TITLES=['Verb to Be: Present Forms','Verb to Be: Negatives','Verb to Be: Questions and Short Answers',
        'Verb to Be: Past Forms','Verb to Be: Future','There Is / There Are'];

    public static function draftProfile(array $bindings):array
    {
        return ['label'=>'M85','base_sha'=>self::BASE_SHA,'version'=>self::VERSION,'marker'=>'m85_native_v1',
            'detail_marker'=>'m85_detail_key','uuid_prefix'=>'m85:','design'=>'m85-native-v1','bindings'=>$bindings];
    }
    private static function readFrozen(string $path,string $sha,?string $root=null):array
    {
        $root??=dirname(__DIR__,2);$file=$root.'/'.$path;
        if(preg_match('/^[a-f0-9]{64}$/D',$sha)!==1||!is_file($file)||is_link($file)||!hash_equals($sha,hash_file('sha256',$file))){
            throw new RuntimeException('M85 immutable artifact pending or changed: '.$path);
        }return json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
    }
    public static function profile():array
    {
        $bindings=self::readFrozen(self::BINDINGS,self::BINDINGS_SHA);
        if(($bindings['base_sha']??null)!==self::BASE_SHA||array_column($bindings['bindings']??[],'identity')!==self::OWNERS){
            throw new RuntimeException('M85 exact six-owner binding vector differs.');
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
        if(!hash_equals(self::BEFORE_SHA,hash('sha256',$bytes))){throw new RuntimeException('M85 independent source BEFORE differs.');}
        self::release()->validate($before,$patch,$master,$contract);self::assertEditorial($before,$patch,$master);
    }
    public static function assertEditorial(array $before,array $patch,array $master,?array $navigation=null):void
    {
        $navigation??=self::readFrozen(self::NAVIGATION,self::NAVIGATION_SHA);
        if(($before['content_type']??null)!=='M85_source_before'||($patch['content_type']??null)!=='M85_Verb_To_Be'
            ||array_column($before['targets']??[],'identity')!==self::OWNERS
            ||array_column($master['lessons']??[],'identity')!==self::OWNERS
            ||array_column($navigation['targets']??[],'identity')!==self::OWNERS){throw new RuntimeException('M85 finite author/source/navigation vector differs.');}
        $ids=[];$graded=0;$sentences=0;
        foreach($master['lessons'] as $i=>$lesson){
            $old=$before['targets'][$i]['before'];$blocks=$lesson['blocks']??[];$count=self::CONTINUING_COUNTS[$i];
            if(($lesson['preserved_h1']??null)!==self::RENDERED_TITLES[$i]||($lesson['raw_title']??null)!==$old['page']['title']
                ||($lesson['intended_own_practice_level']??null)!==self::FOCUS_LEVELS[$i]
                ||!array_key_exists('technical_block_level',$lesson)||$lesson['technical_block_level']!==null
                ||($lesson['display_level']??null)!==self::DISPLAY_LEVELS[$i]||empty($lesson['point_detail_decisions'])
                ||count($blocks)!==$count+1||$lesson['subtitle_text']!==null){throw new RuntimeException('M85 actual metadata/finite author vector differs.');}
            $mistakes=$blocks[[4,4,4,5,5,4][$i]]??[];
            if(($mistakes['type']??null)!=='mistakes-grid'||count($mistakes['data']['items']??[])!==3){
                throw new RuntimeException('M85 exact mistake block/three-item vector differs.');
            }
            foreach($mistakes['data']['items'] as $item){
                if(!is_string($item['note']??null)||trim($item['note'])===''){
                    throw new RuntimeException('M85 required mistake note missing.');
                }
                if($i>=4){
                    if(!is_string($item['hint']??null)||$item['note']!==$item['hint']){
                        throw new RuntimeException('M85 six retained hint aliases must equal their notes byte-for-byte.');
                    }
                }elseif(array_key_exists('hint',$item)){
                    throw new RuntimeException('M85 hint alias outside the exact six E/F mistakes.');
                }
            }
            foreach($old['page']['blocks'] as $slot=>$source){
                $after=$blocks[$slot]??[];
                if(($after['existing_source_index']??null)!==$slot||$after['type']!==$source['type']||$after['column']!==$source['column']
                    ||!array_key_exists('level',$after)||$after['level']!==null){throw new RuntimeException('M85 continuing native metadata changed.');}
            }
            $practice=$blocks[$count];$data=$practice['data'];
            if($practice['type']!=='practice-set'||$practice['column']!=='left'||$practice['level']!==null||isset($data['intro'])
                ||($data['linked_practice']['source']??null)!=='theory_links'||($data['linked_practice']['question_types']??null)!==['4']
                ||($data['linked_practice']['seeder_classes']??null)!==[self::BANKS[$i]]){throw new RuntimeException('M85 exact appended practice/thematic bank differs.');}
            $ledger=$master['editorial_review']['task_map'][self::OWNERS[$i]]??[];$seen=[];
            foreach($ledger as $row){
                $g=$row['after_group']??'';$n=$row['after_group_index']??null;$task=$data[$g][$n]??null;
                if(!is_array($task)||($row['after_task']??null)!==$task||isset($seen[$g.':'.$n])
                    ||($row['old_task']??null)!==null||empty($row['learning_function'])||empty($row['change_reason'])){
                    throw new RuntimeException('M85 new-control ledger is stale or incomplete.');
                }$seen[$g.':'.$n]=true;
            }
            foreach(['selects','choices','inputs'] as $group){
                if(count($data[$group]??[])!==2){throw new RuntimeException('M85 requires exactly two form/meaning/full-sentence controls.');}
                foreach($data[$group] as $n=>$task){
                    if(!isset($seen[$group.':'.$n])||!is_string($task['id']??null)||!str_starts_with($task['id'],'m85-')
                        ||isset($ids[$task['id']])){throw new RuntimeException('M85 new control identity missing or repeated.');}
                    $ids[$task['id']]=true;self::assertTask($task,$group,$data);$graded++;
                    if($group==='inputs'){self::assertSentence($task);$sentences++;}
                }
            }
            if(($data['rephrase']??[])!==[]){throw new RuntimeException('M85 own practice must not silently add rephrases/demos.');}
            $navSlot=self::NAVIGATION_SOURCE_INDICES[$i];$nav=$navigation['targets'][$i];
            $oldNav=json_decode($old['page']['blocks'][$navSlot]['body'],true,flags:JSON_THROW_ON_ERROR);
            $items=$blocks[$navSlot]['data']['items'];
            $labels=static fn($xs)=>array_map(static fn($x)=>array_intersect_key($x,array_flip(['label','current'])),$xs);
            if(($nav['items']??null)!==$items||$labels($items)!==$labels($oldNav['items'])){throw new RuntimeException('M85 footer labels/current/order changed.');}
            foreach($items as $item){if(empty($item['current'])&&preg_match('~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)*$~D',$item['url']??'')!==1){throw new RuntimeException('M85 canonical footer URL missing.');}}
            if($i===2&&$blocks[7]['data']!==json_decode($old['page']['blocks'][7]['body'],true,flags:JSON_THROW_ON_ERROR)){
                throw new RuntimeException('M85 related-topic second navigation must remain exact.');
            }
        }
        if([$graded,$sentences]!==[36,12]){throw new RuntimeException('M85 own controls and separate widgets are conflated.');}
    }
    private static function assertTask(array $task,string $group,array $data):void
    {
        if(isset($task['strict'])||isset($task['punctuation_sensitive'])||($group==='selects'&&isset($task['prompt']))
            ||!is_array($task['accepted']??null)||!in_array($task['answer']??null,$task['accepted'],true)){throw new RuntimeException('M85 native accepted contract differs.');}
        foreach($task['feedback']??[] as $key=>$value){if($key!==mb_strtolower(trim($key))||!self::plain($value)){throw new RuntimeException('M85 feedback must be plain text with native keys.');}}
        foreach($task['accepted'] as $answer){if(!self::plain($answer)||!self::plain($task['feedback'][mb_strtolower(trim($answer))]??null)){throw new RuntimeException('M85 accepted answer lacks plain feedback.');}}
        if(in_array($group,['selects','choices'],true)){
            $options=$task['options']??($group==='selects'?($data['options']??[]):($data['choice_options']??['a','b']));$correct=0;
            $keys=array_map(self::comparisonKey(...),$options);
            if($options!==[]&&count(array_filter($options,static fn($o)=>preg_match('/^[a-zа-яіїєґ]$/iu',$o)===1))===count($options)
                &&$task['accepted']!==[$task['answer']]){throw new RuntimeException('M85 semantic letter controls accept only the reviewed candidate identifier, never an English contraction alias.');}
            if(count($options)<2||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)||empty($task['label'])){throw new RuntimeException('M85 options collide or lack a visible criterion.');}
            foreach($options as $option){
                if(!self::plain($task['feedback'][mb_strtolower(trim($option))]??null)){throw new RuntimeException('M85 displayed option lacks feedback.');}
                foreach($task['accepted'] as $answer){if(in_array(self::comparisonKey($option),array_map(self::comparisonKey(...),AcceptedAnswerVariants::for($answer)),true)){$correct++;break;}}
            }if($correct!==1){throw new RuntimeException('M85 displayed options need exactly one native-correct choice.');}
        }
    }
    private static function assertSentence(array $task):void
    {
        $tokens=array_map('trim',explode('/',$task['before']??''));$remaining=$tokens;$tail=$task['answer'];
        if(count($tokens)<2||implode(' ',$tokens)===$tail||isset($task['hint'])||isset($task['placeholder'])
            ||preg_match('/[\x{0400}-\x{04FF}]/u',$task['after']??'')!==1){throw new RuntimeException('M85 full sentence needs shuffled tokens and a visible Ukrainian condition.');}
        foreach($tokens as $t){if($t===''||count(preg_split('/\s+/u',$t))>3){throw new RuntimeException('M85 groups must contain one to three words.');}}
        while($tail!==''&&$remaining!==[]){$found=false;foreach($remaining as $n=>$t){if($tail===$t||str_starts_with($tail,$t.' ')){
            $tail=$tail===$t?'':substr($tail,strlen($t)+1);unset($remaining[$n]);$found=true;break;}}
            if(!$found){throw new RuntimeException('M85 literal token bank cannot compose the sentence.');}}
        if($tail!==''||$remaining!==[]){throw new RuntimeException('M85 token multiset loses or repeats a group.');}
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
