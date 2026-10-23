<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Six reviewed teaching-data owners; the shared native release owns presentation. */
final class M82CommonMistakesLevelRevision
{
    public const BASE_SHA = 'b8fecb29492038dc01d374955b61c500c718fa4c';
    public const BEFORE = 'docs/content/m82-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m82-common-mistakes-level-revision.v1.0.2.json';
    public const MASTER = 'docs/content/m82-common-mistakes-level-revision.v1.0.2.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m82-common-mistakes-level-revision.v1.json';
    public const CHECKSUMS = 'docs/content/m82-source-checksums.v1.0.2.json';
    public const BINDINGS = 'docs/content/m82-native-bindings.v1.json';
    public const BEFORE_SHA = '2e792f79e2419cf5993b452b403bcaa8c452f4bef39cf8e98960022f49f95b1f';
    public const SOURCE_SHA = 'cc3744501c2f5b4852dcbb064a84339a3db13142fa5ac61b270380a1ac2ee2fa';
    public const MASTER_SHA = 'fd9e54e1cd019fb74b02862f232835f1e25332d26748987646ef3881f504f93d';
    public const CONTRACT_SHA = '9a483c3e56e800ee2755c68345031e51406a3ffdddae87a8daa15f114bc4fff1';
    public const CHECKSUMS_SHA = '82b34ee07e908b0c544a7d1e49a506a7c38780c9af6a12df9626dd9878256a9c';
    public const BINDINGS_SHA = 'a998208e041a2d231deff859cc5ec37e72800a7a6c79c61b64deef2b67ad4fc0';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesPresentPerfectTheorySeeder',
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesCountableUncountableTheorySeeder',
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesPrepositionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesB1B2GrammarTrapsTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarA1MixedRevisionTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarA2MixedRevisionTheorySeeder',
    ];
    public const PAGE_IDS = [42,40,41,39,325,326];
    public const CATEGORY_IDS = [9,9,9,9,59,59];
    public const SLUGS = ['present-perfect-common-mistakes','countable-vs-uncountable-nouns-common-mistakes','prepositions-common-mistakes','b1-b2-grammar-traps','a1-mixed-revision','a2-mixed-revision'];
    public const ANCESTRIES = [['common-mistakes'],['common-mistakes'],['common-mistakes'],['common-mistakes'],['mixed-revision'],['mixed-revision']];
    public const LEVELS = ['A2','A2','A2','B1','A1','A2'];
    public const FOCUS_LEVELS = self::LEVELS;
    public const DISPLAY_LEVELS = ['A2-B1','A2-B1','A2-B1','B1-B2','A1','A2'];
    public const CONTINUING_COUNTS = [6,6,6,6,4,4];
    public const PRACTICE_SOURCE_INDICES = [6,6,6,6,4,4];
    public const RENDERED_TITLES = ['Present Perfect: Common Mistakes','Countable vs Uncountable Nouns: Common Mistakes','Prepositions: Common Mistakes','B1/B2 Grammar Traps','A1 Mixed Revision','A2 Mixed Revision'];
    public const GRADED_COUNTS = [6,6,6,6,6,6];
    public const SELECT_COUNTS = [2,2,2,2,2,2];
    public const INPUT_COUNTS = [2,2,2,2,2,2];
    public const REPHRASE_COUNTS = [0,0,0,0,0,0];
    public const SENTENCE_INPUT_INDICES = [[0,1],[0,1],[0,1],[0,1],[0,1],[0,1]];
    private const APPROVED_NAVIGATION_URLS = [
        4 => ['/theory/basic-grammar/verb-to-be/verb-to-be-present', '/theory/basic-grammar/verb-to-be/there-is-there-are',
            '/theory/some-any/theory-some-any-things', '/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative'],
        5 => ['/theory/tenses/present-perfect/present-perfect-forms', '/theory/conditionals/first-conditional',
            '/theory/reported-speech/reported-statements', '/theory/conditionals/second-conditional'],
    ];

    public static function draftProfile(array $bindings): array
    {
        return ['label'=>'M82','base_sha'=>self::BASE_SHA,'version'=>'1.0.2','marker'=>'m82_native_v1',
            'detail_marker'=>'m82_detail_key','uuid_prefix'=>'m82:','design'=>'m82-native-v1','bindings'=>$bindings];
    }

    public static function profile(): array
    {
        $path=dirname(__DIR__,2).'/'.self::BINDINGS;
        if (!is_file($path)||is_link($path)||preg_match('/^[a-f0-9]{64}$/D',self::BINDINGS_SHA)!==1
            ||!hash_equals(self::BINDINGS_SHA,hash_file('sha256',$path))) {
            throw new RuntimeException('M82 six-owner native bindings are not reviewed/frozen.');
        }
        $data=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
        if (($data['base_sha']??null)!==self::BASE_SHA||array_column($data['bindings']??[],'identity')!==self::OWNERS) {
            throw new RuntimeException('M82 exact owner scope differs.');
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
        if (!hash_equals(self::BEFORE_SHA,hash('sha256',$bytes))) { throw new RuntimeException('M82 complete source BEFORE differs.'); }
        self::release()->validate($before,$patch,$master,$contract);
        self::assertEditorial($before,$patch,$master);
    }

    /** Finite content invariants; no classifier, automatic paraphrase grader or runtime word threshold. */
    public static function assertEditorial(array $before,array $patch,array $master): void
    {
        if (($before['content_type']??null)!=='M82_source_before'||($patch['content_type']??null)!=='M82_Common_Mistakes_Level_Revision'
            ||array_column($master['lessons']??[],'identity')!==self::OWNERS) { throw new RuntimeException('M82 six-owner source provenance differs.'); }
        $ids=[];
        foreach ($master['lessons'] as $i=>$lesson) {
            $old=$before['targets'][$i]['before']['page']['blocks'];$blocks=$lesson['blocks'];
            if (($lesson['preserved_h1']??null)!==self::RENDERED_TITLES[$i]
                ||($lesson['intended_own_practice_level']??null)!==self::LEVELS[$i]
                ||($lesson['raw_title']??null)!==$before['targets'][$i]['before']['page']['title']
                ||empty($lesson['point_detail_decisions'])||count($old)!==self::CONTINUING_COUNTS[$i]
                ||array_column($old,'column')!==array_column(array_slice($blocks,0,count($old)),'column')) {
                throw new RuntimeException('M82 retained titles/columns/semantic decisions differ.');
            }
            $types=array_column(array_slice($blocks,0,count($old)),'type');
            $expected=$i<4?['hero','usage-panels','usage-panels','mistakes-grid','summary-list','navigation-chips']
                :['hero','summary-list','forms-grid','navigation-chips'];
            if ($types!==$expected) { throw new RuntimeException('M82 retained native functions / eight box transitions differ.'); }
            $practice=$blocks[self::PRACTICE_SOURCE_INDICES[$i]];
            if ($practice['type']!=='practice-set'||$practice['column']!=='left'||$practice['level']!==self::LEVELS[$i]) {
                throw new RuntimeException('M82 new practice owner/level differs.');
            }
            $data=$practice['data'];
            if (array_key_exists('intro',$data)) { throw new RuntimeException('M82 generic practice intro is unsupported and must not carry criteria.'); }
            foreach (['selects','choices','inputs','rephrase'] as $group) {
                $tasks=$data[$group]??[];
                if (count($tasks)!==($group==='rephrase'?0:2)) { throw new RuntimeException('M82 exact reviewed 2/2/2 task composition differs.'); }
                foreach ($tasks as $task) {
                    if ($group==='selects'&&array_key_exists('prompt',$task)) { throw new RuntimeException('M82 select criteria belong in its visible label, not an unsupported prompt.'); }
                    $id=$task['id']??'';
                    if (!str_starts_with($id,'m82-')||isset($ids[$id])||isset($task['strict'])||isset($task['punctuation_sensitive'])
                        ||!is_array($task['accepted']??null)||$task['accepted']===[]||!in_array($task['answer']??null,$task['accepted'],true)) {
                        throw new RuntimeException('M82 stable task/answer contract differs.');
                    }
                    $ids[$id]=true;
                    foreach ($task['feedback']??[] as $key=>$text) {
                        if (!is_string($key)||$key!==mb_strtolower(trim($key))||!is_string($text)||trim($text)===''
                            ||preg_match('/<(?:\\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)) {
                            throw new RuntimeException('M82 feedback must be plain text with literal keys.');
                        }
                    }
                    foreach ($task['accepted'] as $answer) {
                        if (!is_string($answer)||self::comparisonKey($answer)===''||empty($task['feedback'][mb_strtolower(trim($answer))])) {
                            throw new RuntimeException('M82 reviewed alias lacks an explanation.');
                        }
                    }
                    if ($group==='inputs') {
                        if (isset($task['hint'])||isset($task['placeholder'])||trim(strip_tags($task['after']??''))==='') {
                            throw new RuntimeException('M82 full sentence lacks visible condition or leaks an authored hint.');
                        }
                    } else {
                        $options=$task['options']??($group==='selects'?($data['options']??[]):($data['choice_options']??['a','b']));
                        $keys=array_map(self::comparisonKey(...),$options);
                        if (count($options)<2||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)
                            ||array_diff($task['accepted'],$options)!==[]||trim(strip_tags($task['label']??''))==='') {
                            throw new RuntimeException('M82 invisible option criterion or normalized answer collision.');
                        }
                        foreach ($options as $option) {
                            if (empty($task['feedback'][mb_strtolower(trim($option))])) { throw new RuntimeException('M82 option lacks feedback.'); }
                        }
                    }
                }
            }
            {
                $navSlot=$i<4?5:3;
                $oldNav=json_decode($old[$navSlot]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];$newNav=$blocks[$navSlot]['data']['items'];
                $labels=static fn($items)=>array_map(static fn($item)=>array_intersect_key($item,array_flip(['label','current'])),$items);
                if (count($newNav)!==($i<4?6:5)||$labels($oldNav)!==$labels($newNav)) { throw new RuntimeException('M82 existing owner-specific navigation labels/current contract changed.'); }
                if ($i>=4) {
                    if (array_column(array_slice($newNav,0,4),'url')!==self::APPROVED_NAVIGATION_URLS[$i]
                        ||array_key_exists('url',$newNav[4])) { throw new RuntimeException('M82 approved eight navigation URLs differ.'); }
                    foreach ($newNav as $item) {
                        if (empty($item['current']) && preg_match('~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)+$~D', $item['url']??'')!==1) {
                            throw new RuntimeException('M82 approved navigation URL is missing.');
                        }
                    }
                }
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
