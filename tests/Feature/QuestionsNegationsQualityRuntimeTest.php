<?php
namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\SavedGrammarTest;
use App\Services\GrammarTestFilterService;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Support\FinitePracticeQualityCatalogs;
use App\Support\QuestionsNegationsPracticeQuality as Quality;
use App\Support\SentenceReorderQuestionFactory as Factory;
use App\Support\VirtualTestRegistry;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Real filter/service/controller path in isolated SQLite, never the working .env database. */
final class QuestionsNegationsQualityRuntimeTest extends TestCase
{
    use RebuildsComposeTestSchema;
    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema();
        config(['coming-soon.enabled'=>false,'tests.tech_info_enabled'=>false,'cache.default'=>'array']);
    }
    private function setupTopic(string $topic): Page
    {
        app()->setLocale('uk'); $map = Quality::mapping($topic);
        foreach (['standard','builder'] as $kind) { $this->seed($map[$kind]['class']); }
        foreach (Quality::metadataPatches() as $patch) {
            if ($patch['topic'] !== $topic) { continue; }
            SavedGrammarTest::forceCreate(array_intersect_key($patch['after'],array_flip(['uuid','slug','name','description','filters'])));
        }
        PageCategory::find(21) ?? PageCategory::forceCreate(['id'=>21,'title'=>'Питальні речення та заперечення','slug'=>Quality::CATEGORY_SLUG]);
        PageCategory::find(22) ?? PageCategory::forceCreate(['id'=>22,'title'=>'Types of Questions','slug'=>'types-of-questions','parent_id'=>21]);
        $prompt = Quality::originalDefinition($topic,'standard')['saved_test']['filters']['prompt_generator']['theory_page'];
        return Page::forceCreate(['page_category_id'=>in_array($topic,Quality::TYPES_TOPICS,true)?22:21,
            'slug'=>$prompt['slug'],'title'=>$prompt['title'],'seeder'=>$prompt['page_seeder_class']]);
    }
    private function assertInterleaving(array $rows,int $perType,string $context): void
    {
        $previous = null;
        foreach (collect($rows)->groupBy('level') as $level => $questions) {
            $counts = ['gap'=>0,'compose'=>0,'order'=>0];
            foreach ($questions as $q) {
                $kind = ($q['presentation'] ?? '') === Factory::PRESENTATION ? 'order' : ((string)$q['type']==='4'?'compose':'gap');
                ++$counts[$kind]; self::assertNotSame($previous,$kind,$context.'/'.$level); $previous=$kind;
                self::assertArrayHasKey($q['uuid'],Quality::questions(),$context);
                $source=Quality::questions()[$q['uuid']]; self::assertSame(array_column($source['markers'],'answer'),$q['answers']);
                if($kind==='gap'){ self::assertTrue($q['passive_gap_phrase_input']);self::assertTrue($q['passive_phrase_word_inputs']); }
                if($kind==='order'){
                    self::assertTrue($q['reorder_manual_input']);self::assertSame($source['target_text'],$q['reorder_answer']);
                    self::assertNotSame($q['reorder_groups'],$q['reorder_tokens']);self::assertEqualsCanonicalizing($q['reorder_groups'],$q['reorder_tokens']);
                }
                foreach($q['verb_hints'] ?? [] as $hint){self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u',$hint);self::assertDoesNotMatchRegularExpression('/[A-Za-z]|<[^>]*>|\{a\d+\}/u',$hint);}
            }
            self::assertSame(['gap'=>$perType,'compose'=>$perType,'order'=>$perType],$counts,$context.'/'.$level);
        }
    }
    public function test_twelve_actual_page_mixed_endpoints_interleave_three_formats_on_every_level(): void
    {
        foreach(array_keys(Quality::TOPICS) as $topic){
            $page=$this->setupTopic($topic);
            $mixed=app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page)->first(fn($t)=>(bool)data_get($t->filters,'__meta.theory_page_mixed_all_levels_test'));
            self::assertNotNull($mixed,$topic);self::assertSame(Quality::REVISION,$mixed->filters['theory_page_mixed_type_profile']);self::assertSame(54,$mixed->filters['num_questions']);
            self::assertCount(54,app(GrammarTestFilterService::class)->questionsFromFilters($mixed->filters));
            $rows=$this->getJson('/test/'.$mixed->getAttribute('public_slug').'/questions')->assertOk()->json('questions');
            self::assertCount(54,$rows,$topic);self::assertCount(54,array_unique(array_column($rows,'uuid')));
            self::assertSame(Quality::LEVELS,collect($rows)->pluck('level')->unique()->values()->all());
            $this->assertInterleaving($rows,3,$topic);
        }
    }
    public function test_twenty_four_historical_page_urls_resolve_only_the_new_owned_bank_and_short_choices(): void
    {
        foreach(array_keys(Quality::TOPICS) as $topic)$this->setupTopic($topic);
        foreach(Quality::metadataPatches() as $uuid=>$patch){
            $rows=$this->getJson('/test/'.$patch['before']['slug'].'/questions')->assertOk()->json('questions');
            self::assertCount($patch['kind']==='standard'?36:18,$rows,$uuid);
            self::assertSame($patch['after']['filters'],SavedGrammarTest::where('uuid',$uuid)->firstOrFail()->filters);
            foreach($rows as $q){
                self::assertArrayHasKey($q['uuid'],Quality::questions());
                if((string)$q['type']!=='4')continue;
                foreach($q['options_by_marker'] as $choices){self::assertCount(4,$choices);foreach($choices as $v)self::assertLessThanOrEqual(3,count(preg_split('/\s+/u',$v)));}
            }
        }
    }
    public function test_root_and_nested_category_six_level_tests_have_only_owned_balanced_page_pairs(): void
    {
        $pages=[];foreach(array_keys(Quality::TOPICS) as $topic)$pages[$topic]=$this->setupTopic($topic);
        foreach([21=>Quality::CATEGORY_TOPICS,22=>Quality::TYPES_TOPICS] as $id=>$topics){
            $selected=new \Illuminate\Database\Eloquent\Collection(array_intersect_key($pages,array_flip($topics)));
            $tests=app(TheoryPagePromptLinkedTestsService::class)->buildForCategory(PageCategory::findOrFail($id),$selected);
            self::assertCount(6,$tests,(string)$id);
            foreach($tests as $test){
                self::assertSame(Quality::REVISION,$test->filters['theory_page_mixed_type_profile']);
                self::assertNotNull(Quality::stateNamespace($test->filters,'uk'));
                $slug=VirtualTestRegistry::register($test->slug,$test->name,$test->filters,$test->description,$test->totalQuestionsAvailable);
                $rows=$this->getJson('/test/'.$slug.'/questions')->assertOk()->json('questions');
                self::assertCount(9*count($topics),$rows);self::assertCount(1,array_unique(array_column($rows,'level')));
                $this->assertInterleaving($rows,3*count($topics),'category-'.$id);
            }
        }
    }
    public function test_foreign_owners_locales_categories_and_changed_payloads_never_opt_in(): void
    {
        self::assertTrue(Factory::isCompactQualityProfile(Quality::REVISION));self::assertFalse(Factory::isPassiveQualityProfile(Quality::REVISION));
        self::assertFalse(Factory::isCompactQualityProfile(Quality::REVISION.'-foreign'));
        self::assertFalse(FinitePracticeQualityCatalogs::ownsCategoryProfile(21,'foreign',Quality::REVISION));
        self::assertNull(Quality::categoryProfile(22,'types-of-questions',[['seeder_classes'=>['foreign'],'base_filters'=>['theory_page_mixed_type_profile'=>Quality::REVISION]]]));
        $payload=['uuid'=>'foreign','question'=>'A {a1}.','type'=>'0','answer_map'=>['a1'=>'test'],'chosen'=>['saved'],'manualInputsBySlot'=>['draft']];
        self::assertSame($payload,Quality::decorate($payload));
        $map=Quality::mapping('tags');$filters=['seeder_classes'=>[$map['standard']['class'],$map['builder']['class']],
            'theory_page_mixed_type_profile'=>Quality::REVISION,'aggregated_theory_page_test'=>true,
            'prompt_generator'=>['theory_page'=>['page_seeder_class'=>'ForeignOwner']], '__meta'=>['mode'=>'filters','theory_page_mixed_polyglot_test'=>true]];
        self::assertNull(Quality::stateNamespace($filters,'uk'));self::assertNull(Quality::stateNamespace($filters,'en'));
        foreach(Quality::metadataPatches() as $uuid=>$patch){
            self::assertSame($patch['before']['filters'],Quality::filtersForLocale($uuid,$patch['after']['filters'],'en'),$uuid);
            self::assertNotNull(Quality::stateNamespace($patch['after']['filters'],'uk',$uuid),$uuid);
        }
        $extra=Quality::mapping('tags')['additional_originals'][0];
        $course=json_decode(file_get_contents(base_path($extra['path'])),true,flags:JSON_THROW_ON_ERROR);
        self::assertSame('polyglot-english-a2',$course['saved_test']['filters']['course_slug']);
        self::assertArrayNotHasKey($course['saved_test']['uuid'],Quality::metadataPatches());
        self::assertSame($course,Quality::definitionMetadata($course,'uk'));
        self::assertSame($course['saved_test']['filters'],Quality::filtersForLocale($course['saved_test']['uuid'],$course['saved_test']['filters'],'en'));
        self::assertNull(Quality::stateNamespace($course['saved_test']['filters'],'uk',$course['saved_test']['uuid']));
        self::assertNull(Quality::savedBuilderDisplayCount($course['saved_test']['uuid'],$course['saved_test']['slug'],$course['saved_test']['filters'],'uk'));
    }
}
