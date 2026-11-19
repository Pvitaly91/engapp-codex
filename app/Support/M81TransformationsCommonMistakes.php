<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Six reviewed teaching-data owners; the shared native release owns presentation. */
final class M81TransformationsCommonMistakes
{
    public const BASE_SHA = '36753e6bd0bd1f8257908f3b30af9856d21e8e9f';
    public const BEFORE = 'docs/content/m81-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m81-transformations-common-mistakes.v1.json';
    public const MASTER = 'docs/content/m81-transformations-common-mistakes.v1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m81-transformations-common-mistakes.v1.json';
    public const CHECKSUMS = 'docs/content/m81-source-checksums.v1.json';
    public const BINDINGS = 'docs/content/m81-native-bindings.v1.json';
    public const BEFORE_SHA = 'd71d9d85441c98efec25152ebc101762d305621f17e846cff83479bfb3709f33';
    public const SOURCE_SHA = '1ef1b3749671599b4b897e142e22b0b3a1b6fc1b1b191735708556191f2ee923';
    public const MASTER_SHA = 'f425c97115830465b383795596632fe776fabfe122339dc2bc8d0906df298c77';
    public const CONTRACT_SHA = 'd51c57761b2692366d3493ddb1ec136894fd9fe960004983b86a0fd337a05040';
    public const CHECKSUMS_SHA = '3f649322833c420ca6a8f1beb64eb67af0536d7983a0759d3e92a0eeca35bfb1';
    public const BINDINGS_SHA = 'd118fb390a713cb5256e57c04377afdf6c9aa7dad0259c80db1058582353bc23';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsConditionalsTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsRelativeClausesLinkingWordsTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsArticlesQuantifiersTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsWordOrderEmphasisTheorySeeder',
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesArticlesTheorySeeder',
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesWordOrderTheorySeeder',
    ];
    public const PAGE_IDS = [147,150,146,153,38,43];
    public const CATEGORY_IDS = [23,23,23,23,9,9];
    public const SLUGS = ['conditionals','relative-clauses-linking-words','articles-quantifiers','word-order-emphasis','articles-common-mistakes','word-order-common-mistakes'];
    public const ANCESTRIES = [['sentence-transformations'],['sentence-transformations'],['sentence-transformations'],['sentence-transformations'],['common-mistakes'],['common-mistakes']];
    public const LEVELS = ['B1','B1','B1','B2','A2','A2'];
    public const FOCUS_LEVELS = self::LEVELS;
    public const DISPLAY_LEVELS = ['B1-B2','B1-B2','B1-B2','B2-C1','A2-B1','A2-B1'];
    public const CONTINUING_COUNTS = [6,6,6,6,6,6];
    public const PRACTICE_SOURCE_INDICES = [6,6,6,6,6,6];
    public const RENDERED_TITLES = ['Sentence Transformations — Conditionals','Sentence Transformations — Relative Clauses / Linking Words','Sentence Transformations — Articles / Quantifiers','Sentence Transformations — Word Order / Emphasis','Articles: Common Mistakes','Word Order: Common Mistakes'];
    public const GRADED_COUNTS = [6,6,6,6,6,6];
    public const SELECT_COUNTS = [2,2,2,2,2,2];
    public const INPUT_COUNTS = [2,2,2,2,2,2];
    public const REPHRASE_COUNTS = [0,0,0,0,0,0];
    public const SENTENCE_INPUT_INDICES = [[0,1],[0,1],[0,1],[0,1],[0,1],[0,1]];

    public static function draftProfile(array $bindings): array
    {
        return ['label'=>'M81','base_sha'=>self::BASE_SHA,'version'=>'1.0.0','marker'=>'m81_native_v1',
            'detail_marker'=>'m81_detail_key','uuid_prefix'=>'m81:','design'=>'m81-native-v1','bindings'=>$bindings];
    }

    public static function profile(): array
    {
        $path=dirname(__DIR__,2).'/'.self::BINDINGS;
        if (!is_file($path)||is_link($path)||preg_match('/^[a-f0-9]{64}$/D',self::BINDINGS_SHA)!==1
            ||!hash_equals(self::BINDINGS_SHA,hash_file('sha256',$path))) {
            throw new RuntimeException('M81 six-owner native bindings are not reviewed/frozen.');
        }
        $data=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
        if (($data['base_sha']??null)!==self::BASE_SHA||array_column($data['bindings']??[],'identity')!==self::OWNERS) {
            throw new RuntimeException('M81 exact owner scope differs.');
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
        if (!hash_equals(self::BEFORE_SHA,hash('sha256',$bytes))) { throw new RuntimeException('M81 complete source BEFORE differs.'); }
        self::release()->validate($before,$patch,$master,$contract);
        self::assertEditorial($before,$patch,$master);
    }

    /** Finite content invariants; no classifier, automatic paraphrase grader or runtime word threshold. */
    public static function assertEditorial(array $before,array $patch,array $master): void
    {
        if (($before['content_type']??null)!=='M81_source_before'||($patch['content_type']??null)!=='M81_Transformations_Common_Mistakes'
            ||array_column($master['lessons']??[],'identity')!==self::OWNERS) { throw new RuntimeException('M81 six-owner source provenance differs.'); }
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
                throw new RuntimeException('M81 retained titles/columns/semantic decisions differ.');
            }
            if ($blocks[1]['type']!=='usage-panels'||$blocks[2]['type']!==($i<4?'comparison-table':'usage-panels')) {
                throw new RuntimeException('M81 exact HTML-to-native component transition differs.');
            }
            $practice=$blocks[self::PRACTICE_SOURCE_INDICES[$i]];
            if ($practice['type']!=='practice-set'||$practice['column']!=='left'||$practice['level']!==self::LEVELS[$i]) {
                throw new RuntimeException('M81 new practice owner/level differs.');
            }
            $data=$practice['data'];
            if (array_key_exists('intro',$data)) { throw new RuntimeException('M81 generic practice intro is unsupported and must not carry criteria.'); }
            foreach (['selects','choices','inputs','rephrase'] as $group) {
                $tasks=$data[$group]??[];
                if (count($tasks)!==($group==='rephrase'?0:2)) { throw new RuntimeException('M81 exact reviewed 2/2/2 task composition differs.'); }
                foreach ($tasks as $task) {
                    if ($group==='selects'&&array_key_exists('prompt',$task)) { throw new RuntimeException('M81 select criteria belong in its visible label, not an unsupported prompt.'); }
                    $id=$task['id']??'';
                    if (!str_starts_with($id,'m81-')||isset($ids[$id])||isset($task['strict'])||isset($task['punctuation_sensitive'])
                        ||!is_array($task['accepted']??null)||$task['accepted']===[]||!in_array($task['answer']??null,$task['accepted'],true)) {
                        throw new RuntimeException('M81 stable task/answer contract differs.');
                    }
                    $ids[$id]=true;
                    foreach ($task['feedback']??[] as $key=>$text) {
                        if (!is_string($key)||$key!==mb_strtolower(trim($key))||!is_string($text)||trim($text)===''
                            ||preg_match('/<(?:\\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)) {
                            throw new RuntimeException('M81 feedback must be plain text with literal keys.');
                        }
                    }
                    foreach ($task['accepted'] as $answer) {
                        if (!is_string($answer)||self::comparisonKey($answer)===''||empty($task['feedback'][mb_strtolower(trim($answer))])) {
                            throw new RuntimeException('M81 reviewed alias lacks an explanation.');
                        }
                    }
                    if ($group==='inputs') {
                        if (isset($task['hint'])||isset($task['placeholder'])||trim(strip_tags($task['after']??''))==='') {
                            throw new RuntimeException('M81 full sentence lacks visible condition or leaks an authored hint.');
                        }
                    } else {
                        $options=$task['options']??($group==='selects'?($data['options']??[]):($data['choice_options']??['a','b']));
                        $keys=array_map(self::comparisonKey(...),$options);
                        if (count($options)<2||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)
                            ||array_diff($task['accepted'],$options)!==[]||trim(strip_tags($task['label']??''))==='') {
                            throw new RuntimeException('M81 invisible option criterion or normalized answer collision.');
                        }
                        foreach ($options as $option) {
                            if (empty($task['feedback'][mb_strtolower(trim($option))])) { throw new RuntimeException('M81 option lacks feedback.'); }
                        }
                    }
                }
            }
            {
                $oldNav=json_decode($old[5]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];$newNav=$blocks[5]['data']['items'];
                $labels=static fn($items)=>array_map(static fn($item)=>array_intersect_key($item,array_flip(['label','current'])),$items);
                if (count($newNav)!==($i<4?8:6)||$labels($oldNav)!==$labels($newNav)) { throw new RuntimeException('M81 existing owner-specific navigation labels changed.'); }
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
