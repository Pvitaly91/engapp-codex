<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Six finite teaching-data owners. No views, styles, grading or database inference. */
final class M77OnePrepositionsPhrasal
{
    public const BASE_SHA = '5270494e0f999eee2a3d1fba6fb7c3f46a914481';
    public const BEFORE = 'docs/content/m77-before-source.v1.json';
    public const SOURCE = 'database/content-patches/m77-one-prepositions-phrasal.v1.json';
    public const MASTER = 'docs/content/m77-one-prepositions-phrasal.v1.json';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m77-one-prepositions-phrasal.v1.json';
    public const CHECKSUMS = 'docs/content/m77-source-checksums.v1.json';
    public const BINDINGS = 'docs/content/m77-native-bindings.v1.json';
    public const BEFORE_SHA = 'b30c34fbc22da48bbce8a5bb62d90cbae2a90e6bb2b969192a328bf52d4ed5bc';
    public const SOURCE_SHA = '901af7f3832a6aa071baa0229331968d43ea979c8b04cfd70f98bb0de632e962';
    public const MASTER_SHA = '57a7f061296e591d75764080e0d3b0c8517aa699e932ac98807cd01617507579';
    public const CONTRACT_SHA = '7d5a99d1d41d0351f62eebfc2131920bba137ee538a4cd6b27e16bb4cf3b53cf';
    public const CHECKSUMS_SHA = 'ce5e2ad30a751fd318b21375347a4d61c2746195323233d3d088c1fa8face861';
    public const BINDINGS_SHA = '233a223616a5f35efb27500f60cf295041f01e6637058c96b73889fd464b0d1f';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesOneOnesTheorySeeder',
        'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfTimeTheorySeeder',
        'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfPlaceTheorySeeder',
        'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfMovementTheorySeeder',
        'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\DependentPrepositionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PhrasalVerbsBasicsTheorySeeder',
    ];
    public const PAGE_IDS = [123,120,119,118,115,116];
    public const CATEGORY_IDS = [20,19,19,19,19,19];
    public const SLUGS = ['one-ones','prepositions-of-time','prepositions-of-place','prepositions-of-movement','dependent-prepositions','phrasal-verbs-basics'];
    public const ANCESTRIES = [['zaimennyky-ta-vkazivni-slova'],['prepositions-and-phrasal-verbs'],['prepositions-and-phrasal-verbs'],['prepositions-and-phrasal-verbs'],['prepositions-and-phrasal-verbs'],['prepositions-and-phrasal-verbs']];
    public const LEVELS = [null,'A1','A1','A2','B1','B1'];
    public const FOCUS_LEVELS = ['A2','A1','A1','A2','B1','B1'];
    public const DISPLAY_LEVELS = ['A2–B1','A1-A2','A1-A2','A2-B1','B1-B2','B1-B2'];
    public const CONTINUING_COUNTS = [11,6,6,6,6,6];
    public const PRACTICE_SOURCE_INDICES = [8,6,6,6,6,6];
    public const RENDERED_TITLES = ['One','Prepositions of Time','Prepositions of Place','Prepositions of Movement','Dependent Prepositions','Phrasal Verbs Basics'];
    public const GRADED_COUNTS = [12,6,6,6,6,6];
    public const SENTENCE_INPUT_INDICES = [[0,1],[0,1],[0,1],[0,1],[0,1],[0,1]];

    /** The literal metadata ledger is independently frozen, not derived from current content. */
    public static function profile(): array
    {
        $path=dirname(__DIR__,2).'/'.self::BINDINGS;
        if (!is_file($path)||is_link($path)||!hash_equals(self::BINDINGS_SHA,hash_file('sha256',$path))) {
            throw new RuntimeException('M77 finite native bindings are not reviewed/frozen.');
        }
        $bindings=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
        if (($bindings['base_sha']??null)!==self::BASE_SHA || array_column($bindings['bindings']??[],'identity')!==self::OWNERS) {
            throw new RuntimeException('M77 frozen six-owner metadata scope differs.');
        }
        return self::draftProfile($bindings['bindings'])+['artifacts'=>[
            self::BEFORE=>self::BEFORE_SHA,self::SOURCE=>self::SOURCE_SHA,self::MASTER=>self::MASTER_SHA,
            self::CONTRACT=>self::CONTRACT_SHA,self::CHECKSUMS=>self::CHECKSUMS_SHA,self::BINDINGS=>self::BINDINGS_SHA,
        ]];
    }

    /** Pure compiler metadata only; runtime entry points always use hash-bound profile(). */
    public static function draftProfile(array $bindings):array
    {
        return ['label'=>'M77','base_sha'=>self::BASE_SHA,'version'=>'1.0.0','marker'=>'m77_native_v1',
            'detail_marker'=>'m77_detail_key','uuid_prefix'=>'m77:','design'=>'m77-native-v1','bindings'=>$bindings];
    }
    private static function release():TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root=null):array { return self::release()->load($root); }
    public static function definition(array $lesson,array $before):array { return self::release()->definition($lesson,$before); }

    public static function validate(array $before,array $patch,array $master,?array $contract=null):void
    {
        if (($before['content_type']??null)!=='M77_source_before'||($patch['content_type']??null)!=='M77_One_Prepositions_Phrasal') {
            throw new RuntimeException('M77 exact source provenance differs.');
        }
        self::release()->validate($before,$patch,$master,$contract);
        foreach ($master['lessons'] as $i=>$lesson) {
            $old=$before['targets'][$i]['before']['page']['blocks']; $blocks=$lesson['blocks'];
            if (($lesson['preserved_h1']??null)!==self::RENDERED_TITLES[$i]
                || ($lesson['intended_own_practice_level']??null)!==self::FOCUS_LEVELS[$i]
                || ($lesson['raw_title']??null)!==$before['targets'][$i]['before']['page']['title']
                || ($lesson['details']??null)!==[] || empty($lesson['point_detail_decisions'])
                || array_column($old,'column')!==array_column(array_slice($blocks,0,count($old)),'column')) {
                throw new RuntimeException('M77 title, continuing columns, semantic detail decision or focus differs.');
            }
            if ($i>0 && ($blocks[2]['type']!=='comparison-table'||$blocks[3]['type']!=='mistakes-grid'
                ||$blocks[3]['column']!=='right'||$blocks[4]['type']!=='summary-list'||$blocks[4]['column']!=='right')) {
                throw new RuntimeException('M77 source table/right corrections/right summary were moved or replaced.');
            }
            $data=$blocks[self::PRACTICE_SOURCE_INDICES[$i]]['data']; $graded=0;
            foreach (['selects','choices','inputs','rephrase'] as $group) {
                foreach ($data[$group]??[] as $n=>$task) {
                    ++$graded;
                    if (isset($task['example_original'])||!is_array($task['accepted']??null)||$task['accepted']===[]
                        ||!in_array($task['answer']??null,$task['accepted'],true)||isset($task['punctuation_sensitive'])||isset($task['strict'])) {
                        throw new RuntimeException('M77 task must have a nonempty canonical/accepted native answer.');
                    }
                    foreach ($task['feedback']??[] as $key=>$text) {
                        if (!is_string($key)||$key!==mb_strtolower(trim($key))||!is_string($text)||trim($text)===''
                            ||preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$text)) {
                            throw new RuntimeException('M77 feedback must use plain text and literal lower-case keys.');
                        }
                    }
                    foreach ($task['accepted'] as $answer) {
                        if (!is_string($answer)||self::comparisonKey($answer)===''||empty($task['feedback'][mb_strtolower(trim($answer))])) {
                            throw new RuntimeException('M77 accepted alias has no nonempty native value/plain explanation.');
                        }
                    }
                    if (in_array($group,['selects','choices'],true)) {
                        $options=$task['options']??[]; $keys=array_map(self::comparisonKey(...),$options);
                        if (count($options)<2||count(array_unique($keys))!==count($keys)||in_array('',$keys,true)
                            ||array_diff($task['accepted'],$options)!==[]) { throw new RuntimeException('M77 options collide or omit an accepted answer.'); }
                        foreach ($options as $option) { if (empty($task['feedback'][mb_strtolower(trim($option))])) { throw new RuntimeException('M77 option feedback missing.'); } }
                    }
                    $transferred=$i===0&&$group==='selects';
                    if ($transferred ? (array_key_exists('id',$task)||array_key_exists('source_index',$task))
                        : (!str_starts_with($task['id']??'','m77-')||array_key_exists('source_index',$task))) {
                        throw new RuntimeException('M77 old absent task IDs or new task identity differs.');
                    }
                }
            }
            if ($graded!==self::GRADED_COUNTS[$i]||count($data['selects']??[])!==($i===0?8:2)
                ||count($data['choices']??[])!==2||count($data['inputs']??[])!==2||($data['rephrase']??null)!==[]
                ||($data['linked_practice']['title']??null)!=='Вправа 4. Побудуй речення') {
                throw new RuntimeException('M77 exact retained/new own-task count or separate widget differs.');
            }
            if ($i===0 && (substr_count($old[8]['body']??'','<li>')!==8
                || array_column($data['selects'],'answer')!==['one','ones','one','one','yours','ones','some','this one']
                || $blocks[9]['type']!=='forms-grid'||$blocks[9]['column']!=='footer'
                || ($blocks[9]['data']['items'][0]['url']??null)!=='/theory/zaimennyky-ta-vkazivni-slova'
                || !str_contains($blocks[0]['data']['intro']??'','id="block-8966"'))) {
                throw new RuntimeException('M77 eight transferred One functions/category-back/old hero anchor differ.');
            }
            $nav=$i===0?10:5; $oldNav=json_decode($old[$nav]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];
            $newNav=$blocks[$nav]['data']['items'];
            $labels=static fn($items)=>array_map(static fn($item)=>array_intersect_key($item,array_flip(['label','current'])),$items);
            if (count($newNav)!==($i===0?9:7)||$labels($oldNav)!==$labels($newNav)
                ||count(array_filter($blocks,static fn($b)=>$b['type']==='navigation-chips'))!==1) {
                throw new RuntimeException('M77 exact footer labels/current states or unique navigation differs.');
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
