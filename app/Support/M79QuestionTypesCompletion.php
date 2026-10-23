<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Six exact educational owners; data projection only, never a view or answer engine. */
final class M79QuestionTypesCompletion
{
    public const BASE_SHA = 'f33d1a0e2eff08e66044d9b746a46bc33f207bce';
    public const BEFORE = 'docs/content/m79-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m79-question-types-completion.v1.0.1.json';
    public const MASTER = 'docs/content/m79-question-types-completion.v1.0.1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m79-question-types-completion.v1.json';
    public const CHECKSUMS = 'docs/content/m79-source-checksums.v1.0.1.json';
    public const BINDINGS = 'docs/content/m79-native-bindings.v1.json';
    public const BEFORE_SHA = '0dab3990ae499b6852d92425112138dc998c44d1466b95d41406fc2d46e4c63d';
    public const SOURCE_SHA = '9af348eae92ac0cb361f5a9da2c407275037cfb1fc1673e84486abef31d7396c';
    public const MASTER_SHA = '8ec1e542444fbf26bea877cba2896c5bf64cea959b91b1fc51914f9a7b0ecc7f';
    public const CONTRACT_SHA = '7e37db3333c42f866ef6ef3af3807ad36d998d1c7fd3fba19f0d6687cb344dd4';
    public const CHECKSUMS_SHA = '4a685cdab60e6e5c64063fadf76b529fa5b43df88f9794edf28e7383afa64c55';
    public const BINDINGS_SHA = '64d980df40c8150285b0996b07422ef189431dc76de30ed1198807b1a79bdd73';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsAlternativeQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsQuestionTagsDisjunctiveQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsNegativeQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsSubjectQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsEmbeddedQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsIndirectQuestionsTheorySeeder',
    ];
    public const PAGE_IDS = [132,137,136,138,134,135];
    public const CATEGORY_IDS = [22,22,22,22,22,22];
    public const SLUGS = ['alternative-questions-coffee-or-tea','question-tags-disjunctive-questions-dont-you-isnt-it',
        'negative-questions-dont-you-know','subject-questions','embedded-questions','indirect-questions'];
    public const ANCESTRIES = [
        ['pytalni-rechennia-ta-zaperechennia','types-of-questions'],
        ['pytalni-rechennia-ta-zaperechennia','types-of-questions'],
        ['pytalni-rechennia-ta-zaperechennia','types-of-questions'],
        ['pytalni-rechennia-ta-zaperechennia','types-of-questions'],
        ['pytalni-rechennia-ta-zaperechennia','types-of-questions'],
        ['pytalni-rechennia-ta-zaperechennia','types-of-questions'],
    ];
    public const LEVELS = ['A2','B1','B1','A2','B1','B1'];
    public const FOCUS_LEVELS = self::LEVELS;
    public const DISPLAY_LEVELS = ['A2–B1','B1–B2','B1–B2','A2–B1','B1','B1'];
    public const CONTINUING_COUNTS = [13,14,13,6,6,6];
    public const PRACTICE_SOURCE_INDICES = [11,12,11,6,6,6];
    public const RENDERED_TITLES = ['Alternative Questions','Question Tags','Negative Questions','Subject Questions','Embedded Questions','Indirect Questions'];
    public const GRADED_COUNTS = [12,15,12,6,6,6];
    public const SELECT_COUNTS = [3,4,3,2,2,2];
    public const INPUT_COUNTS = [4,6,4,2,2,2];
    public const REPHRASE_COUNTS = [3,3,3,0,0,0];
    public const SENTENCE_INPUT_INDICES = [[0,1,2,3],[4,5],[0,1,2,3],[0,1],[0,1],[0,1]];

    public static function draftProfile(array $bindings): array
    {
        return ['label'=>'M79','base_sha'=>self::BASE_SHA,'version'=>'1.0.1','marker'=>'m79_native_v1',
            'detail_marker'=>'m79_detail_key','uuid_prefix'=>'m79:','design'=>'m79-native-v1','bindings'=>$bindings];
    }

    public static function profile(): array
    {
        $path=dirname(__DIR__,2).'/'.self::BINDINGS;
        if (!is_file($path)||is_link($path)||!hash_equals(self::BINDINGS_SHA,hash_file('sha256',$path))) {
            throw new RuntimeException('M79 finite native bindings are not reviewed/frozen.');
        }
        $bindings=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
        if (($bindings['base_sha']??null)!==self::BASE_SHA||array_column($bindings['bindings']??[],'identity')!==self::OWNERS) {
            throw new RuntimeException('M79 exact six-owner metadata scope differs.');
        }
        return self::draftProfile($bindings['bindings'])+['artifacts'=>[
            self::BEFORE=>self::BEFORE_SHA,self::SOURCE=>self::SOURCE_SHA,self::MASTER=>self::MASTER_SHA,
            self::CONTRACT=>self::CONTRACT_SHA,self::CHECKSUMS=>self::CHECKSUMS_SHA,self::BINDINGS=>self::BINDINGS_SHA,
        ]];
    }

    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root=null): array { return self::release()->load($root); }
    public static function definition(array $lesson,array $before): array { return self::release()->definition($lesson,$before); }
    public static function validate(array $before,array $patch,array $master,?array $contract=null): void
    {
        $bytes=json_encode($before,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
        if (!hash_equals(self::BEFORE_SHA,hash('sha256',$bytes))) { throw new RuntimeException('M79 complete independent source BEFORE differs.'); }
        self::release()->validate($before,$patch,$master,$contract);
        self::assertEditorial($before,$patch,$master);
    }

    /** Position/identity retention and independently reviewed native grading data. */
    public static function assertEditorial(array $before,array $patch,array $master): void
    {
        if (($before['content_type']??null)!=='M79_source_before'||($patch['content_type']??null)!=='M79_Question_Types_Completion'
            ||array_column($master['lessons']??[],'identity')!==self::OWNERS) { throw new RuntimeException('M79 source provenance differs.'); }
        $ids=[];
        foreach ($master['lessons'] as $i=>$lesson) {
            $old=$before['targets'][$i]['before']['page']['blocks']; $blocks=$lesson['blocks'];
            if (($lesson['preserved_h1']??null)!==self::RENDERED_TITLES[$i]
                ||($lesson['intended_own_practice_level']??null)!==self::FOCUS_LEVELS[$i]
                ||($lesson['raw_title']??null)!==$before['targets'][$i]['before']['page']['title']
                ||empty($lesson['point_detail_decisions'])
                ||array_column($old,'column')!==array_column(array_slice($blocks,0,count($old)),'column')) {
                throw new RuntimeException('M79 retained title/columns/focus/detail decisions differ.');
            }
            if ($i>=3&&($blocks[3]['type']!=='mistakes-grid'||$blocks[3]['column']!=='right'
                ||$blocks[4]['type']!=='summary-list'||$blocks[4]['column']!=='right')) { throw new RuntimeException('M79 right-column learning components changed.'); }
            $data=$blocks[self::PRACTICE_SOURCE_INDICES[$i]]['data'];
            $previous=$i<3?json_decode($old[self::PRACTICE_SOURCE_INDICES[$i]]['body'],true,flags:JSON_THROW_ON_ERROR):[];
            $graded=0;$demos=0;
            foreach (['selects','choices','inputs','rephrase'] as $group) {
                $oldTasks=$previous[$group]??[];
                if (count($data[$group]??[])<count($oldTasks)) { throw new RuntimeException('M79 retained practice function missing.'); }
                foreach ($data[$group]??[] as $n=>$task) {
                    $retained=$oldTasks[$n]??null;
                    if ($retained!==null) {
                        foreach (['id','source_index'] as $field) {
                            if (array_key_exists($field,$retained)!==array_key_exists($field,$task)
                                ||(array_key_exists($field,$task)&&$retained[$field]!==$task[$field])) { throw new RuntimeException('M79 retained task identity/absence changed.'); }
                        }
                    } elseif (!str_starts_with($task['id']??'','m79-')) { throw new RuntimeException('M79 new task lacks stable ID.'); }
                    if (isset($task['example_original'])) {
                        if (!isset($retained['example_original'])||isset($task['answer'])||isset($task['accepted'])) { throw new RuntimeException('M79 retained ungraded demo changed role.'); }
                        ++$demos;continue;
                    }
                    if (isset($retained['example_original'])) { throw new RuntimeException('M79 demo became a graded task.'); }
                    ++$graded;
                    if (!is_array($task['accepted']??null)||$task['accepted']===[]||!in_array($task['answer']??null,$task['accepted'],true)
                        ||isset($task['strict'])||isset($task['punctuation_sensitive'])) { throw new RuntimeException('M79 canonical/alias native contract differs.'); }
                    if (isset($task['id'])) {
                        if (isset($ids[$task['id']])) { throw new RuntimeException('M79 duplicate task ID.'); }
                        $ids[$task['id']]=true;
                    }
                    foreach ($task['feedback']??[] as $key=>$text) {
                        if (!is_string($key)||$key!==mb_strtolower(trim($key))||!is_string($text)||trim($text)===''
                            ||preg_match('/<(?:\\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)) { throw new RuntimeException('M79 feedback is not plain literal-key text.'); }
                    }
                    foreach ($task['accepted'] as $answer) {
                        if (!is_string($answer)||self::comparisonKey($answer)===''||empty($task['feedback'][mb_strtolower(trim($answer))])) { throw new RuntimeException('M79 accepted alias lacks explanation.'); }
                    }
                    if (in_array($group,['selects','choices'],true)) {
                        $options=$task['options']??($group==='selects'?($data['options']??[]):($data['choice_options']??['a','b']));
                        $keys=array_map(self::comparisonKey(...),$options);
                        if (count($options)<2||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)||array_diff($task['accepted'],$options)!==[]) { throw new RuntimeException('M79 option/key collision or missing accepted option.'); }
                        foreach ($options as $option) { if (empty($task['feedback'][mb_strtolower(trim($option))])) { throw new RuntimeException('M79 option lacks feedback.'); } }
                    }
                }
            }
            if ($graded!==self::GRADED_COUNTS[$i]||$demos!==($i<3?1:0)
                ||count($data['selects']??[])!==self::SELECT_COUNTS[$i]||count($data['choices']??[])!==2
                ||count($data['inputs']??[])!==self::INPUT_COUNTS[$i]
                ||count($data['rephrase']??[])!==self::REPHRASE_COUNTS[$i]+($i<3?1:0)) { throw new RuntimeException('M79 exact retained/new task and demo counts differ.'); }
            $nav=$i<3?count($old)-1:5;
            $oldNav=json_decode($old[$nav]['body'],true,flags:JSON_THROW_ON_ERROR)['items']; $newNav=$blocks[$nav]['data']['items'];
            $labels=static fn($items)=>array_map(static fn($item)=>array_intersect_key($item,array_flip(['label','current'])),$items);
            if ($labels($oldNav)!==$labels($newNav)||count($newNav)!==[7,7,7,9,8,8][$i]
                ||count(array_filter($blocks,static fn($b)=>$b['type']==='navigation-chips'))!==1) { throw new RuntimeException('M79 exact navigation labels/current states differ.'); }
        }
    }

    public static function comparisonKey(string $value): string { return M76PronounRelations::comparisonKey($value); }
    public static function presentation(object $block,array $data): ?array { return self::release()->presentation($block,$data); }
    public static function validDesign(?array $design): bool { return self::release()->validDesign($design); }
    public static function pointPanels(?array $design): ?array { return self::release()->pointPanels($design); }
    public static function correction(array $item,?array $design): ?array { return self::release()->correction($item,$design); }
    public static function preserveExcludedTextBlock(object $block,string $caller): ?object { return self::release()->preserveExcludedTextBlock($block,$caller); }
    public static function preserveCoursePage(?Page $page): ?Page { return self::release()->preserveCoursePage($page); }
}
