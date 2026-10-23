<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Six reviewed teaching-data owners; the shared native release owns presentation. */
final class M80NegativesTransformations
{
    public const BASE_SHA = 'b56f29df4b3409edc1ad6cfb34b7fedd901e893d';
    public const BEFORE = 'docs/content/m80-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m80-negatives-transformations.v1.json';
    public const MASTER = 'docs/content/m80-negatives-transformations.v1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m80-negatives-transformations.v1.json';
    public const CHECKSUMS = 'docs/content/m80-source-checksums.v1.json';
    public const BINDINGS = 'docs/content/m80-native-bindings.v1.json';
    public const BEFORE_SHA = '7ea4b55203a0833e6017d4cbd991a366d4ad31c3c02595514d045e81d6324751';
    public const SOURCE_SHA = '463a25bf3af2788b87398244ce80be42632083b8bdc7977a3a32f13ef8bba739';
    public const MASTER_SHA = '50edd647628ab0e459b60bc0053adc25aca2df2c573b642929eec97a15eef332';
    public const CONTRACT_SHA = 'baf73db204524ff6cbf51defe2546fdbbf6f39328e9dc6afccd42ae0a6094618';
    public const CHECKSUMS_SHA = '0b53167ec2433a7a0a05a9be98db73dd8607ff2a35e1399625c9bc22704082d1';
    public const BINDINGS_SHA = 'd334fe4336f89c78ccc0808cdd194349b8e79f5c8602a21373b1883c5dd14981';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsNegativeFormsTheorySeeder',
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsDoubleNegativesTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsTensesTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsModalsTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsPassiveTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsReportedSpeechTheorySeeder',
    ];
    public const PAGE_IDS = [331,332,152,148,149,151];
    public const CATEGORY_IDS = [21,21,23,23,23,23];
    public const SLUGS = ['negative-forms','double-negatives','tenses','modals','passive','reported-speech'];
    public const ANCESTRIES = [['pytalni-rechennia-ta-zaperechennia'],['pytalni-rechennia-ta-zaperechennia'],
        ['sentence-transformations'],['sentence-transformations'],['sentence-transformations'],['sentence-transformations']];
    public const LEVELS = ['A2','B1','B1','B1','B1','B1'];
    public const FOCUS_LEVELS = self::LEVELS;
    public const DISPLAY_LEVELS = ['A1-B1','A1-B1','B1-B2','B1-B2','B1-B2','B1-B2'];
    public const CONTINUING_COUNTS = [5,5,6,6,6,6];
    public const PRACTICE_SOURCE_INDICES = [5,5,6,6,6,6];
    public const RENDERED_TITLES = ['Negative Forms','Double Negatives','Sentence Transformations — Tenses',
        'Sentence Transformations — Modals','Sentence Transformations — Passive','Sentence Transformations — Reported Speech'];
    public const GRADED_COUNTS = [6,6,6,6,6,6];
    public const SELECT_COUNTS = [2,2,2,2,2,2];
    public const INPUT_COUNTS = [2,2,2,2,2,2];
    public const REPHRASE_COUNTS = [0,0,0,0,0,0];
    public const SENTENCE_INPUT_INDICES = [[0,1],[0,1],[0,1],[0,1],[0,1],[0,1]];

    public static function draftProfile(array $bindings): array
    {
        return ['label'=>'M80','base_sha'=>self::BASE_SHA,'version'=>'1.0.0','marker'=>'m80_native_v1',
            'detail_marker'=>'m80_detail_key','uuid_prefix'=>'m80:','design'=>'m80-native-v1','bindings'=>$bindings];
    }

    public static function profile(): array
    {
        $path=dirname(__DIR__,2).'/'.self::BINDINGS;
        if (!is_file($path)||is_link($path)||preg_match('/^[a-f0-9]{64}$/D',self::BINDINGS_SHA)!==1
            ||!hash_equals(self::BINDINGS_SHA,hash_file('sha256',$path))) {
            throw new RuntimeException('M80 six-owner native bindings are not reviewed/frozen.');
        }
        $data=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
        if (($data['base_sha']??null)!==self::BASE_SHA||array_column($data['bindings']??[],'identity')!==self::OWNERS) {
            throw new RuntimeException('M80 exact owner scope differs.');
        }
        return self::draftProfile($data['bindings'])+['artifacts'=>[
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
        if (!hash_equals(self::BEFORE_SHA,hash('sha256',$bytes))) { throw new RuntimeException('M80 complete source BEFORE differs.'); }
        self::release()->validate($before,$patch,$master,$contract);
        self::assertEditorial($before,$patch,$master);
    }

    /** Finite content invariants; no classifier, automatic paraphrase grader or runtime word threshold. */
    public static function assertEditorial(array $before,array $patch,array $master): void
    {
        if (($before['content_type']??null)!=='M80_source_before'||($patch['content_type']??null)!=='M80_Negatives_Transformations'
            ||array_column($master['lessons']??[],'identity')!==self::OWNERS) { throw new RuntimeException('M80 six-owner source provenance differs.'); }
        $ids=[];
        foreach ($master['lessons'] as $i=>$lesson) {
            $old=$before['targets'][$i]['before']['page']['blocks'];$blocks=$lesson['blocks'];
            if (($lesson['preserved_h1']??null)!==self::RENDERED_TITLES[$i]
                ||($lesson['intended_own_practice_level']??null)!==self::LEVELS[$i]
                ||($lesson['raw_title']??null)!==$before['targets'][$i]['before']['page']['title']
                ||empty($lesson['point_detail_decisions'])||count($old)!==self::CONTINUING_COUNTS[$i]
                ||array_column($old,'column')!==array_column(array_slice($blocks,0,count($old)),'column')
                ||$blocks[3]['type']!=='mistakes-grid'||$blocks[3]['column']!=='right'
                ||$blocks[4]['type']!=='summary-list'||$blocks[4]['column']!=='right') {
                throw new RuntimeException('M80 retained titles/columns/semantic decisions differ.');
            }
            if ($i>=2&&($blocks[1]['type']!=='usage-panels'||$blocks[2]['type']!=='comparison-table')) {
                throw new RuntimeException('M80 exact HTML-to-native component transition differs.');
            }
            $practice=$blocks[self::PRACTICE_SOURCE_INDICES[$i]];
            if ($practice['type']!=='practice-set'||$practice['column']!=='left'||$practice['level']!==self::LEVELS[$i]) {
                throw new RuntimeException('M80 new practice owner/level differs.');
            }
            $data=$practice['data'];
            foreach (['selects','choices','inputs','rephrase'] as $group) {
                $tasks=$data[$group]??[];
                if (count($tasks)!==($group==='rephrase'?0:2)) { throw new RuntimeException('M80 exact reviewed 2/2/2 task composition differs.'); }
                foreach ($tasks as $task) {
                    $id=$task['id']??'';
                    if (!str_starts_with($id,'m80-')||isset($ids[$id])||isset($task['strict'])||isset($task['punctuation_sensitive'])
                        ||!is_array($task['accepted']??null)||$task['accepted']===[]||!in_array($task['answer']??null,$task['accepted'],true)) {
                        throw new RuntimeException('M80 stable task/answer contract differs.');
                    }
                    $ids[$id]=true;
                    foreach ($task['feedback']??[] as $key=>$text) {
                        if (!is_string($key)||$key!==mb_strtolower(trim($key))||!is_string($text)||trim($text)===''
                            ||preg_match('/<(?:\\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)) {
                            throw new RuntimeException('M80 feedback must be plain text with literal keys.');
                        }
                    }
                    foreach ($task['accepted'] as $answer) {
                        if (!is_string($answer)||self::comparisonKey($answer)===''||empty($task['feedback'][mb_strtolower(trim($answer))])) {
                            throw new RuntimeException('M80 reviewed alias lacks an explanation.');
                        }
                    }
                    if ($group==='inputs') {
                        if (isset($task['hint'])||isset($task['placeholder'])||trim(strip_tags($task['after']??''))==='') {
                            throw new RuntimeException('M80 full sentence lacks visible condition or leaks an authored hint.');
                        }
                    } else {
                        $options=$task['options']??($group==='selects'?($data['options']??[]):($data['choice_options']??['a','b']));
                        $keys=array_map(self::comparisonKey(...),$options);
                        if (count($options)<2||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)
                            ||array_diff($task['accepted'],$options)!==[]||trim(strip_tags($task['label']??''))==='') {
                            throw new RuntimeException('M80 invisible option criterion or normalized answer collision.');
                        }
                        foreach ($options as $option) {
                            if (empty($task['feedback'][mb_strtolower(trim($option))])) { throw new RuntimeException('M80 option lacks feedback.'); }
                        }
                    }
                }
            }
            if ($i>=2) {
                $oldNav=json_decode($old[5]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];$newNav=$blocks[5]['data']['items'];
                $labels=static fn($items)=>array_map(static fn($item)=>array_intersect_key($item,array_flip(['label','current'])),$items);
                if (count($newNav)!==8||$labels($oldNav)!==$labels($newNav)) { throw new RuntimeException('M80 existing eight navigation labels changed.'); }
            }
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
