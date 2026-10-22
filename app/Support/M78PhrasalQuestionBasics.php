<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Six finite teaching-data owners. No views, styles, grading or database inference. */
final class M78PhrasalQuestionBasics
{
    public const BASE_SHA = 'fe61b3634af5d307cc32167a16e224c1d5fb1591';
    public const BEFORE = 'docs/content/m78-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m78-phrasal-question-basics.v1.json';
    public const MASTER = 'docs/content/m78-phrasal-question-basics.v1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m78-phrasal-question-basics.v1.json';
    public const CHECKSUMS = 'docs/content/m78-source-checksums.v1.json';
    public const BINDINGS = 'docs/content/m78-native-bindings.v1.json';
    public const BEFORE_SHA = 'eae1f86007ce9b8a70774cae895f88b178a56f8fee431d5ea7a5e4052bf39715';
    public const SOURCE_SHA = '62fa18fb0010e85b806bed21f9ddff86fc5854556127d397909afb4d2b581a34';
    public const MASTER_SHA = '4fa08e891f7a44538bb29801dc8f5287368232c9c3a7524252d575f19374fd67';
    public const CONTRACT_SHA = '85ac2ebb4c5f040d54e1fd904519c64195f1e4d547f79b07de9f109f86ae1353';
    public const CHECKSUMS_SHA = '8b1b57d6f42e60f8256fa0754981545d1257e972cf2a9412f3f932f66edf8864';
    public const BINDINGS_SHA = '63cfcb4fccea1a3a8008d07cfcd323224ef0699b2c58e948d10d92a5ee44300a';
    public const OWNERS = ['Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PhrasalVerbsSeparableInseparableTheorySeeder','Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\CommonPhrasalVerbsByTopicTheorySeeder','Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsQuestionFormsTheorySeeder','Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsYesNoQuestionsGeneralQuestionsTheorySeeder','Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsWhQuestionsSpecialQuestionsTheorySeeder','Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsAnswersToQuestionsTheorySeeder'];
    public const PAGE_IDS = [117,114,131,140,139,133];
    public const CATEGORY_IDS = [19,19,21,22,22,22];
    public const SLUGS = ['separable-and-inseparable-phrasal-verbs','common-phrasal-verbs-by-topic','question-forms','yesno-questions-general-questions','wh-questions-special-questions-who-what-where-when-why-how','answers-to-questions-yes-i-do-no-i-dont'];
    public const ANCESTRIES = [['prepositions-and-phrasal-verbs'],['prepositions-and-phrasal-verbs'],['pytalni-rechennia-ta-zaperechennia'],['pytalni-rechennia-ta-zaperechennia','types-of-questions'],['pytalni-rechennia-ta-zaperechennia','types-of-questions'],['pytalni-rechennia-ta-zaperechennia','types-of-questions']];
    public const LEVELS = ['B1','B1',null,'A1','A1','A1'];
    public const FOCUS_LEVELS = ['B1','B1','A1','A1','A1','A1'];
    public const DISPLAY_LEVELS = ['B1-B2','B1-B2','A1–A2','A1–A2','A1–B1','A1–A2'];
    public const CONTINUING_COUNTS = [6,6,13,12,14,14];
    public const PRACTICE_SOURCE_INDICES = [6,6,11,10,12,12];
    public const RENDERED_TITLES = ['Separable and Inseparable Phrasal Verbs','Common Phrasal Verbs by Topic','Question Forms','Yes / No Questions','Wh-Questions','Answers to Questions'];
    public const GRADED_COUNTS = [6,6,14,13,13,15];
    public const INPUT_COUNTS = [2,2,4,4,4,6];
    public const REPHRASE_COUNTS = [0,0,4,3,3,3];
    public const SENTENCE_INPUT_INDICES = [[0,1],[0,1],[0,1,2,3],[0,1,2,3],[0,1,2,3],[4,5]];
    public const POINT_COUNTS = [
        [0=>['rules'=>3],1=>['items'=>6],2=>['rows'=>12],3=>['items'=>3],4=>['items'=>6],5=>['items'=>7]],
        [0=>['rules'=>3],1=>['sections'=>3],2=>['sections'=>3],3=>['sections'=>3],4=>['items'=>6],5=>['items'=>7]],
        [0=>['rules'=>3],1=>['items'=>6],2=>['sections'=>4],3=>['sections'=>3],4=>['sections'=>3],5=>['sections'=>4],6=>['sections'=>5],7=>['rows'=>6],8=>['rows'=>5],9=>['items'=>6],10=>['items'=>6],12=>['items'=>7]],
        [0=>['rules'=>3],1=>['items'=>3],2=>['sections'=>3],3=>['sections'=>3],4=>['sections'=>3],5=>['sections'=>2],6=>['sections'=>3],7=>['rows'=>6],8=>['items'=>4],9=>['items'=>7],11=>['items'=>7]],
        [0=>['rules'=>3],1=>['items'=>3],2=>['sections'=>3],3=>['sections'=>3],4=>['sections'=>2],5=>['sections'=>2],6=>['sections'=>3],7=>['sections'=>3],8=>['rows'=>6],9=>['sections'=>3],10=>['items'=>4],11=>['items'=>7],13=>['items'=>7]],
        [0=>['rules'=>3],1=>['items'=>3],2=>['sections'=>3],3=>['sections'=>3],4=>['sections'=>3],5=>['sections'=>3],6=>['sections'=>3],7=>['sections'=>3],8=>['sections'=>2],9=>['rows'=>5],10=>['items'=>4],11=>['items'=>10],13=>['items'=>7]],
    ];

    /** The literal metadata ledger is independently frozen, not derived from current content. */
    public static function profile(): array
    {
        $path=dirname(__DIR__,2).'/'.self::BINDINGS;
        if (!is_file($path)||is_link($path)||!hash_equals(self::BINDINGS_SHA,hash_file('sha256',$path))) {
            throw new RuntimeException('M78 finite native bindings are not reviewed/frozen.');
        }
        $bindings=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
        if (($bindings['base_sha']??null)!==self::BASE_SHA || array_column($bindings['bindings']??[],'identity')!==self::OWNERS) {
            throw new RuntimeException('M78 frozen six-owner metadata scope differs.');
        }
        return self::draftProfile($bindings['bindings'])+['artifacts'=>[
            self::BEFORE=>self::BEFORE_SHA,self::SOURCE=>self::SOURCE_SHA,self::MASTER=>self::MASTER_SHA,
            self::CONTRACT=>self::CONTRACT_SHA,self::CHECKSUMS=>self::CHECKSUMS_SHA,self::BINDINGS=>self::BINDINGS_SHA,
        ]];
    }

    /** Pure compiler metadata only; runtime entry points always use hash-bound profile(). */
    public static function draftProfile(array $bindings):array
    {
        return ['label'=>'M78','base_sha'=>self::BASE_SHA,'version'=>'1.0.0','marker'=>'m78_native_v1',
            'detail_marker'=>'m78_detail_key','uuid_prefix'=>'m78:','design'=>'m78-native-v1','bindings'=>$bindings];
    }
    private static function release():TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root=null):array { return self::release()->load($root); }
    public static function definition(array $lesson,array $before):array { return self::release()->definition($lesson,$before); }

    public static function validate(array $before,array $patch,array $master,?array $contract=null):void
    {
        $beforeBytes=json_encode($before,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
        if (!hash_equals(self::BEFORE_SHA,hash('sha256',$beforeBytes))) {
            throw new RuntimeException('M78 independently frozen complete source BEFORE differs.');
        }
        self::release()->validate($before,$patch,$master,$contract);
        self::assertEditorial($before,$patch,$master);
    }

    /** Finite source/author review; also used by the pure compiler before freeze. */
    public static function assertEditorial(array $before,array $patch,array $master):void
    {
        if (($before['content_type']??null)!=='M78_source_before'||($patch['content_type']??null)!=='M78_Phrasal_Question_Basics'
            ||array_column($master['lessons']??[],'identity')!==self::OWNERS) {
            throw new RuntimeException('M78 exact six-owner source provenance differs.');
        }
        $ids=[];
        foreach ($master['lessons'] as $i=>$lesson) {
            $old=$before['targets'][$i]['before']['page']['blocks']; $blocks=$lesson['blocks'];
            foreach (self::POINT_COUNTS[$i] as $slot=>$fields) {
                foreach ($fields as $field=>$count) {
                    if (count($blocks[$slot]['data'][$field]??[])!==$count) {
                        throw new RuntimeException('M78 reviewed native point inventory changed.');
                    }
                }
            }
            if (($lesson['preserved_h1']??null)!==self::RENDERED_TITLES[$i]
                ||($lesson['intended_own_practice_level']??null)!==self::FOCUS_LEVELS[$i]
                ||($lesson['raw_title']??null)!==$before['targets'][$i]['before']['page']['title']
                ||($lesson['details']??null)!==[]||empty($lesson['point_detail_decisions'])
                ||array_column($old,'column')!==array_column(array_slice($blocks,0,count($old)),'column')) {
                throw new RuntimeException('M78 title, continuing columns, explicit detail decision or focus differs.');
            }
            if ($i<2 && ($blocks[3]['column']!=='right'||$blocks[4]['type']!=='summary-list'||$blocks[4]['column']!=='right'
                ||($i===0?($blocks[2]['type']!=='comparison-table'||$blocks[3]['type']!=='mistakes-grid'):$blocks[3]['type']!=='usage-panels'))) {
                throw new RuntimeException('M78 retained table/right correction-or-recommendation/summary differs.');
            }
            $data=$blocks[self::PRACTICE_SOURCE_INDICES[$i]]['data'];
            $previous=$i<2?[]:json_decode($old[self::PRACTICE_SOURCE_INDICES[$i]]['body'],true,flags:JSON_THROW_ON_ERROR);
            $graded=0;$demos=0;
            foreach (['selects','choices','inputs','rephrase'] as $group) {
                $oldTasks=$previous[$group]??[];
                if (count($data[$group]??[])<count($oldTasks)) { throw new RuntimeException('M78 retained practice position lost.'); }
                foreach ($data[$group]??[] as $n=>$task) {
                    if (isset($task['example_original'])) {
                        if (!isset($oldTasks[$n]['example_original'])) { throw new RuntimeException('M78 unreviewed practice demo.'); }
                        if (array_key_exists('answer',$task)||array_key_exists('accepted',$task)) {
                            throw new RuntimeException('M78 ungraded demo cannot acquire an answer or score.');
                        }
                        foreach (['id','source_index'] as $field) {
                            if (array_key_exists($field,$oldTasks[$n])!==array_key_exists($field,$task)
                                ||(array_key_exists($field,$task)&&$task[$field]!==$oldTasks[$n][$field])) {
                                throw new RuntimeException('M78 retained demo identity/absence changed.');
                            }
                        }
                        ++$demos; continue;
                    }
                    ++$graded;
                    if (!is_array($task['accepted']??null)||$task['accepted']===[]||!in_array($task['answer']??null,$task['accepted'],true)
                        ||isset($task['punctuation_sensitive'])||isset($task['strict'])) {
                        throw new RuntimeException('M78 canonical/accepted native answer differs.');
                    }
                    if (isset($oldTasks[$n])) {
                        foreach (['id','source_index'] as $field) {
                            if (array_key_exists($field,$oldTasks[$n])!==array_key_exists($field,$task)
                                ||(array_key_exists($field,$task)&&$task[$field]!==$oldTasks[$n][$field])) {
                                throw new RuntimeException('M78 retained task identity/absence changed.');
                            }
                        }
                        $answerCorrections=[
                            1=>['Q: Can you swim? A: Yes, I can.','Yes, I can.'],
                            2=>['Q: Is she happy? A: Yes, she is.','Yes, she is.'],
                            3=>['Q: Do you like it? A: Yes, I do.','Yes, I do.'],
                        ];
                        $reviewedCorrection=$i===5&&$group==='rephrase'&&isset($answerCorrections[$n])
                            &&[$oldTasks[$n]['answer'],$task['answer']]===$answerCorrections[$n];
                        if (!$reviewedCorrection&&!in_array($oldTasks[$n]['answer'],$task['accepted'],true)) {
                            throw new RuntimeException('M78 retained canonical answer was lost or reordered: '.$i.' '.$group.':'.$n);
                        }
                    } elseif (!str_starts_with($task['id']??'','m78-')||array_key_exists('source_index',$task)) {
                        throw new RuntimeException('M78 new task stable identity differs.');
                    }
                    if (isset($task['id'])) {
                        if (isset($ids[$task['id']])) { throw new RuntimeException('M78 duplicate new task ID.'); }
                        $ids[$task['id']]=true;
                    }
                    foreach ($task['feedback']??[] as $key=>$text) {
                        if (!is_string($key)||$key!==mb_strtolower(trim($key))||!is_string($text)||trim($text)===''
                            ||preg_match('/<(?:\\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)) {
                            throw new RuntimeException('M78 feedback must be plain text with literal keys.');
                        }
                    }
                    foreach ($task['accepted'] as $answer) {
                        if (!is_string($answer)||self::comparisonKey($answer)===''||empty($task['feedback'][mb_strtolower(trim($answer))])) {
                            throw new RuntimeException('M78 accepted alias lacks a nonempty value/plain explanation.');
                        }
                    }
                    if (in_array($group,['selects','choices'],true)) {
                        $options=$task['options']??($group==='selects'?($data['options']??[]):($data['choice_options']??['a','b']));
                        $keys=array_map(self::comparisonKey(...),$options);
                        if (count($options)<2||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)||array_diff($task['accepted'],$options)!==[]) {
                            throw new RuntimeException('M78 option/key normalization collision or missing accepted option.');
                        }
                        foreach ($options as $option) {
                            if (empty($task['feedback'][mb_strtolower(trim($option))])) { throw new RuntimeException('M78 displayed option lacks feedback.'); }
                        }
                    }
                }
            }
            if ($graded!==self::GRADED_COUNTS[$i]||$demos!==($i<2?0:1)
                ||count($data['selects']??[])!==($i<2?2:4)||count($data['choices']??[])!==2
                ||count($data['inputs']??[])!==self::INPUT_COUNTS[$i]
                ||count($data['rephrase']??[])!==self::REPHRASE_COUNTS[$i]+($i<2?0:1)
                ||($data['linked_practice']['title']??null)!==($i<2?'Вправа 4. Побудуй речення':($i===5?'Вправа 5. Побудуй відповідь':'Вправа 5. Побудуй речення'))) {
                throw new RuntimeException('M78 exact retained/new task, demo or independent widget count differs: '.$i.' graded='.$graded.' demos='.$demos);
            }
            $nav=$i<2?5:count($old)-1;
            $oldNav=json_decode($old[$nav]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];$newNav=$blocks[$nav]['data']['items'];
            $labels=static fn($items)=>array_map(static fn($item)=>array_intersect_key($item,array_flip(['label','current'])),$items);
            if (count($newNav)!==7||$labels($oldNav)!==$labels($newNav)
                ||count(array_filter($blocks,static fn($b)=>$b['type']==='navigation-chips'))!==1) {
                throw new RuntimeException('M78 exact footer labels/current states or navigation count differs.');
            }
        }
    }

    public static function comparisonKey(string $value):string { return M76PronounRelations::comparisonKey($value); }
    public static function presentation(object $block,array $data):?array { return self::release()->presentation($block,$data); }
    public static function validDesign(?array $design):bool { return self::release()->validDesign($design); }
    public static function pointPanels(?array $design):?array { return self::release()->pointPanels($design); }
    public static function correction(array $item,?array $design):?array { return self::release()->correction($item,$design); }
    public static function preserveExcludedTextBlock(object $block,string $caller):?object { return self::release()->preserveExcludedTextBlock($block,$caller); }
    public static function preserveCoursePage(?Page $page):?Page { return self::release()->preserveCoursePage($page); }
}

