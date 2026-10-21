<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Models\VerbHint;
use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage;
use App\Support\M51QuestionSupplement;
use App\Support\M73GenericIrregularCollective as Package;
use App\Support\M73BuilderSelectionPresentation as Presentation;
use App\Support\TheoryBuilderSelectionPresentation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

/** Frozen educational contract + explicitly synthetic unsaved guard probes; no DB fixtures. */
class M73BuilderSelectionPresentationTest extends TestCase
{
    private const SELECTED = [
        [31638,31640,31641,31642,31643],
        [32548,32551,32554,32555,32557],
        [32631,32632,32634,32639,32641],
    ];

    private const KEYS = [
        'The elderly have influence', 'The wolf is a protected species', 'People need reliable information',
        'A wolf can represent a whole class', 'The wolf in the reserve looked calm',
        'These sheep are very quiet', 'Two children are playing in the yard', 'Two women work in this office',
        'Many people came to the meeting', 'My feet hurt after the long walk',
        'The club won and it celebrated as one group', 'The staff finished work and they went home separately',
        'A group of children is playing near the gate', 'The team is ready as one unit', 'The committee has made one decision',
    ];

    private function audit(): array
    {
        self::assertSame('docs/content/m73-generic-irregular-collective-bank-audit.v1.json', Presentation::AUDIT);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', Presentation::AUDIT_SHA);
        self::assertSame(Presentation::AUDIT_SHA, hash_file('sha256',base_path(Presentation::AUDIT)));
        $audit=json_decode(file_get_contents(base_path(Presentation::AUDIT)),true,flags:JSON_THROW_ON_ERROR);
        self::assertSame('frozen',$audit['status']); self::assertSame('1.0.0',$audit['version']);
        self::assertSame('m73-generic-irregular-collective-bank-audit',$audit['package']);
        self::assertSame('41cb3ac163738e4d2662b4c53a3b2288de3303161d2da6ef5a1f42a9632d0ac9',$audit['independent_before_sha256']);
        return $audit;
    }

    private function block(int $i): TextBlock
    {
        [, $patch]=Package::load(); $a=$this->audit()['practice_bindings'][$i];
        $source=$patch['targets'][$i]['after']['page']['blocks'][$a['source_index']];
        $block=new TextBlock;
        $block->setRawAttributes(['id'=>730000+$i,'uuid'=>$a['uuid'],'page_id'=>$a['page_id'],'page_category_id'=>$a['category_id'],
            'seeder'=>$a['identity'],'locale'=>'uk','type'=>'practice-set','level'=>$a['level'],'column'=>$a['column'],
            'heading'=>$a['heading'],'css_class'=>$a['css_class'],'sort_order'=>$a['source_index']+1,'body'=>$source['body']],true);
        return $block;
    }

    public function test_exact_filter_scope_preserves_ninety_six_snapshots_thirty_six_reviews_and_fifteen_reused_questions(): void
    {
        self::assertSame('cb55b329827e52a56cb6d41edb21e70768cf86f4',Package::BASE_SHA);
        self::assertSame([80,86,84],Package::PAGE_IDS); self::assertSame([11,7,6],Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([null,'A2','B1'],Package::LEVELS);
        $audit=$this->audit(); [, $patch, , $contract]=Package::load();
        Presentation::validateAudit($audit,$patch,$contract);
        self::assertSame([null,'A2','B1'],$audit['review_summary']['technical_filter']);
        self::assertSame(['B2','A2','B1'],$audit['review_summary']['intended_selected_levels']);
        self::assertSame(96,$audit['review_summary']['actual_filtered_pool']);
        self::assertSame(36,$audit['review_summary']['candidate_semantic_review']);
        self::assertSame(60,$audit['review_summary']['scope_excluded_without_semantic_review']);
        self::assertSame(15,$audit['scope']['selected_questions']); self::assertSame(15,$audit['scope']['reused_questions']);
        foreach (array_diff(array_keys($audit['scope']),['selected_questions','reused_questions']) as $field) {
            self::assertSame(0,$audit['scope'][$field],$field);
        }
        $poolCount=$reviewCount=$scopeCount=0; $keys=[]; $allIds=[];
        foreach ($audit['targets'] as $i=>$target) {
            self::assertSame(Package::LEVELS[$i],$target['level']); self::assertSame($target['level'],$contract['targets'][$i]['level']);
            self::assertSame(self::SELECTED[$i],$target['expected_selected_ids']);
            self::assertSame(self::SELECTED[$i],array_column($target['first_five'],'id'));
            self::assertCount(1,$target['bank_seeders']);
            $pool=$target['expected_pool_ids_by_bank'][$target['bank_seeders'][0]];
            self::assertCount([72,12,12][$i],$pool); self::assertCount(count($pool),array_unique($pool));
            self::assertCount([67,7,7][$i],$target['excluded_question_ids']);
            self::assertCount(12,$target['all_eligible_review']); self::assertCount([60,0,0][$i],$target['scope_exclusions']);
            self::assertSame($pool,array_column($target['all_pool_snapshots'],'id'));
            self::assertSame([],array_intersect(array_column($target['scope_exclusions'],'id'),array_column($target['all_eligible_review'],'id')));
            $partition=[...$target['expected_selected_ids'],...$target['excluded_question_ids']]; sort($partition); $sorted=$pool; sort($sorted);
            self::assertSame($sorted,$partition);
            $reviewed=array_values(array_filter($target['all_eligible_review'],static fn($q)=>$q['selected']));
            self::assertSame(self::SELECTED[$i],array_column($reviewed,'id'));
            foreach ($target['all_pool_snapshots'] as $snapshot) {
                self::assertCount(6,$snapshot['snapshot_hashes']);
                foreach ([...array_values($snapshot['snapshot_hashes']),$snapshot['full_private_snapshot_sha256'],$snapshot['variants_sha256'],$snapshot['links_sha256'],
                    $snapshot['all_original_option_attributes_sha256'],$snapshot['all_original_answer_attributes_sha256'],
                    $snapshot['all_original_option_pivot_attributes_sha256'],$snapshot['all_original_tag_relations_sha256']] as $hash) {
                    self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D',$hash);
                }
                foreach (['question','answers','options','created_at','updated_at','runtime','password','APP_KEY'] as $field) {
                    self::assertArrayNotHasKey($field,$snapshot);
                }
            }
            foreach ($target['all_eligible_review'] as $review) {
                self::assertSame([0,0],$review['hint_counts']); self::assertSame(0,$review['variants_count']);
                self::assertNotSame('',trim($review['decision'])); self::assertNotSame('',trim($review['translation_uk']));
                self::assertSame(['B2','A2','B1'][$i],$review['level']);
            }
            foreach ($target['scope_exclusions'] as $excluded) {
                self::assertSame(0,$i); self::assertNotSame('B2',$excluded['level']);
                self::assertStringContainsString('no semantic review claimed',$excluded['reason']);
            }
            foreach ($target['first_five'] as $question) {
                self::assertFalse($question['is_new']);
                self::assertSame(M51QuestionSupplement::ROW_COLUMNS['questions'],$question['question_attribute_keys']);
                self::assertSame([], $question['token_presentations']); self::assertSame([], $question['option_presentations']);
                self::assertSame($question['correct_tokens'],ComposeTokenCase::normalize($question['raw_correct_tokens']));
                self::assertSame($question['correct_tokens'],ComposeTokenCase::normalize($question['correct_tokens']));
                self::assertSame($question['raw_correct_tokens'],array_column($question['original_ordered_instances'],'option'));
                self::assertSame(implode(' ',$question['correct_tokens']),$question['expected_word_key']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u',$question['visible_condition_uk']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u',$question['translation_uk']);
                $counts=array_count_values($question['token_bank_with_multiplicity']);
                foreach (array_count_values($question['correct_tokens']) as $token=>$count) {
                    self::assertGreaterThanOrEqual($count,$counts[$token]??0,'Repeated ordered instances must all be available.');
                    self::assertNotContains(mb_strtolower($token),['ø','без артикля','no article']);
                }
                $keys[]=$question['expected_word_key']; $allIds[]=$question['id'];
            }
            $poolCount+=count($pool); $reviewCount+=count($target['all_eligible_review']); $scopeCount+=count($target['scope_exclusions']);
        }
        self::assertSame([96,36,60],[$poolCount,$reviewCount,$scopeCount]); self::assertCount(15,array_unique($allIds));
        self::assertSame(self::KEYS,$keys);
    }

    public function test_selected_contexts_bound_generic_claims_and_collective_agreement_without_changing_native_tokens(): void
    {
        $targets=$this->audit()['targets']; $samples=[];
        foreach ($targets as $target) { foreach ($target['first_five'] as $sample) { $samples[$sample['id']]=$sample; } }
        self::assertStringContainsString('вигаданому',$samples[31640]['visible_condition_uk']);
        self::assertStringContainsString('не твердження про реальний правовий статус',$samples[31640]['visible_condition_uk']);
        self::assertStringContainsString('не біологічний таксономічний ранг',$samples[31642]['visible_condition_uk']);
        self::assertSame(['a','wolf','can','represent','a','whole','class'],$samples[31642]['raw_correct_tokens']);
        self::assertSame(2,array_count_values($samples[31643]['raw_correct_tokens'])['the']);
        self::assertStringContainsString('повсякден',$samples[32555]['visible_condition_uk']);
        foreach ([32639,32641] as $id) { self::assertStringContainsString('однин',$samples[$id]['visible_condition_uk']); }
        foreach ([32633,32636,32642] as $id) { self::assertContains($id,$targets[2]['excluded_question_ids']); }
        self::assertSame('The staff finished work and they went home separately',$samples[32632]['expected_word_key']);
        $reserves=array_column($targets[2]['all_eligible_review'],null,'id');
        self::assertSame('A group of Students is waiting outside',$reserves[32633]['native_case_word_key']);
        self::assertSame('A crowd of Tourists is near the museum',$reserves[32636]['native_case_word_key']);
        self::assertSame('excluded-visible-internal-capital-defect',$reserves[32633]['semantic_verdict']);
        self::assertSame('excluded-visible-internal-capital-defect',$reserves[32636]['semantic_verdict']);
        self::assertSame('excluded-non-group-lesson-sense',$reserves[32642]['semantic_verdict']);
    }

    public function test_hash_bound_guard_refuses_scope_snapshots_filter_selection_and_practice_alias_changes(): void
    {
        $audit=$this->audit(); [, $patch, , $contract]=Package::load();
        $changes=[
            static function(&$a):void{$a['scope']['question_writes']++;},
            static function(&$a):void{$a['targets'][0]['level']='B2';},
            static function(&$a):void{$a['targets'][1]['page_id']++;},
            static function(&$a):void{$a['targets'][0]['all_pool_snapshots'][0]['full_private_snapshot_sha256']=str_repeat('0',64);},
            static function(&$a):void{$a['targets'][1]['first_five'][0]['snapshot_hashes']['question_snapshot']=str_repeat('0',64);},
            static function(&$a):void{$a['targets'][2]['first_five'][0]['visible_condition_uk']='Інший задум.';},
            static function(&$a):void{array_pop($a['targets'][0]['excluded_question_ids']);},
            static function(&$a):void{$a['targets'][2]['expected_selected_ids'][0]=32633;},
            static function(&$a):void{$a['practice_bindings'][0]['uuid']='foreign';},
            static function(&$a):void{$a['practice_bindings'][1]['source_index']++;},
            static function(&$a):void{$a['practice_bindings'][2]['level']=null;},
            static function(&$a):void{$a['practice_bindings'][2]['body_sha256']=str_repeat('0',64);},
        ];
        foreach ($changes as $change) {
            $bad=$audit; $change($bad); $refused=false;
            try { Presentation::validateAudit($bad,$patch,$contract); } catch (RuntimeException) { $refused=true; }
            self::assertTrue($refused,'Every changed exact-bound artifact must fail closed.');
        }
        $badContract=$contract; $badContract['targets'][0]['level']='B2'; $refused=false;
        try { Presentation::validateAudit($audit,$patch,$badContract); } catch (RuntimeException) { $refused=true; }
        self::assertTrue($refused,'A semantic focus is not a replacement for the SQL NULL filter.');
    }

    public function test_only_exact_canonical_unsaved_block_clones_extend_exclusions_without_queries(): void
    {
        app()->setLocale('uk'); DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        try {
            foreach ($this->audit()['targets'] as $i=>$target) {
                $block=$this->block($i); $original=$block->getAttributes();
                $config=['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>$target['bank_seeders']];
                self::assertSame([999],Presentation::exclusions($block,$config,[999]));
                $copy=Presentation::matchingBlock($block,true); self::assertNotSame($block,$copy);
                self::assertSame([999,...$target['excluded_question_ids']],Presentation::exclusions($copy,$config,[999]));
                self::assertSame([999],Presentation::exclusions(clone $copy,$config,[999]));
                self::assertSame($original,$block->getAttributes()); self::assertSame([],$block->getDirty()); self::assertFalse($block->exists);
                foreach (['seeder'=>'Foreign\\Owner','locale'=>'en','type'=>'box','level'=>'C2','page_id'=>1,'page_category_id'=>1,
                    'uuid'=>'foreign','body'=>$block->body."\n",'sort_order'=>99,'column'=>'right','heading'=>'Foreign','css_class'=>'foreign'] as $field=>$value) {
                    $foreign=clone $block; $foreign->$field=$value;
                    self::assertSame($foreign,Presentation::matchingBlock($foreign,true),$field);
                }
                self::assertSame($block,Presentation::matchingBlock($block,false));
                foreach (['en','pl'] as $locale) { app()->setLocale($locale); self::assertSame($block,Presentation::matchingBlock($block,true)); }
                app()->setLocale('uk'); $wrong=$config; $wrong['seeder_classes']=['Foreign\\Bank'];
                self::assertSame([999],Presentation::exclusions($copy,$wrong,[999]));
                $selectedExcluded=[$target['expected_selected_ids'][0]];
                self::assertSame($selectedExcluded,Presentation::exclusions($copy,$config,$selectedExcluded));
                $foreignProfile=new class extends TheoryBuilderSelectionPresentation {
                    protected static function packageClass():string{throw new RuntimeException('Foreign clone provenance must stop before package access.');}
                    protected static function policy():array{throw new RuntimeException('Foreign clone provenance must stop before policy access.');}
                };
                self::assertSame([999],$foreignProfile::exclusions($copy,$config,[999]));
            }
            self::assertSame([],DB::connection()->getQueryLog());
        } finally { DB::connection()->disableQueryLog(); app()->setLocale('uk'); }
    }

    /** Invented attributes/timestamps are declared synthetic; opaque real snapshots are never reconstructed. */
    private function synthetic(): array
    {
        $questions=[]; $samples=[];
        for($n=0;$n<5;$n++) {
            $id=83000+$n; $attrs=['id'=>$id,'uuid'=>'m73-synthetic-'.$n,'question'=>'Синтетична умова.',
                'difficulty'=>2,'level'=>'A2','category_id'=>45,'source_id'=>305,'flag'=>0,'type'=>'4',
                'options_by_marker'=>null,'theory_text_block_uuid'=>null,'seeder'=>'SyntheticOnly',
                'created_at'=>'2000-01-01 00:00:00','updated_at'=>'2000-01-01 00:00:00'];
            $q=new Question; $q->setRawAttributes($attrs,true); $options=$answers=$optionRows=$answerRows=$pivots=[];
            foreach(['These','children','are','not','hungry'] as $i=>$token) {
                $optionId=95000+$n*10+$i; $answerId=96000+$n*10+$i;
                $option=new QuestionOption; $option->setRawAttributes(['id'=>$optionId,'option'=>$token],true);
                $pivotRow=['question_id'=>$id,'option_id'=>$optionId,'flag'=>null];
                $pivot=new Pivot; $pivot->setRawAttributes($pivotRow,true); $option->setRelation('pivot',$pivot);
                $answer=new QuestionAnswer; $answer->setRawAttributes(['id'=>$answerId,'question_id'=>$id,'option_id'=>$optionId,'marker'=>'a'.($i+1),'position'=>null],true);
                $answer->setRelation('option',$option); $options[]=$option; $answers[]=$answer;
                $optionRows[]=['id'=>$optionId,'option'=>$token];
                $answerRows[]=['id'=>$answerId,'marker'=>'a'.($i+1),'position'=>null,'option_id'=>$optionId,'option'=>$token]; $pivots[]=$pivotRow;
            }
            $q->setRelation('answers',new Collection($answers)); $q->setRelation('options',new Collection($options));
            $q->setRelation('hints',new Collection); $q->setRelation('verbHints',new Collection); $questions[]=$q;
            $samples[]=['question_attribute_keys'=>array_keys($attrs),'snapshot_hashes'=>array_map(M26DetailPackage::digest(...),[
                'question_snapshot'=>$attrs,'ordered_answer_snapshot'=>$answerRows,'original_options_snapshot'=>$optionRows,
                'hints_snapshot'=>[],'verb_hints_snapshot'=>[],'loaded_option_pivots'=>$pivots]),'token_presentations'=>[]];
        }
        return [new Collection($questions),['expected_selected_ids'=>array_map(static fn($q)=>$q->id,$questions),
            'expected_selected_uuids'=>array_map(static fn($q)=>$q->uuid,$questions),'first_five'=>$samples]];
    }

    public function test_complete_synthetic_exact_snapshots_pass_and_stale_or_missing_loaded_relations_fail_without_queries(): void
    {
        $method=new ReflectionMethod(TheoryBuilderSelectionPresentation::class,'matchesQuestions');
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        try {
            [$questions,$target]=$this->synthetic(); $before=$questions->toArray();
            self::assertTrue($method->invoke(null,$questions,$target)); self::assertSame($before,$questions->toArray());
            $changes=[
                fn($q)=>$q[4]->setAttribute('question','Інше формулювання.'),
                fn($q)=>$q[4]->setAttribute('created_at','2001-01-01 00:00:00'),
                fn($q)=>$q[4]->setAttribute('level','B1'), fn($q)=>$q[4]->setAttribute('type','1'),
                fn($q)=>$q[4]->setAttribute('seeder','ForeignBank'),
                fn($q)=>$q[2]->answers[0]->option->setAttribute('option','Changed'),
                fn($q)=>$q[1]->options[0]->pivot->setAttribute('flag',1),
                fn($q)=>$q[3]->unsetRelation('hints'), fn($q)=>$q[3]->unsetRelation('verbHints'),
                fn($q)=>$q[0]->options[0]->unsetRelation('pivot'), fn($q)=>$q[1]->answers[0]->unsetRelation('option'),
                fn($q)=>$q[4]->setAttribute('unknown_decoration',[]),
                static function($q):void{$hint=new QuestionHint; $hint->setRawAttributes(['id'=>97000,'question_id'=>$q[3]->id,'provider'=>'synthetic','locale'=>'uk','hint'=>'Інша підказка.'],true); $q[3]->setRelation('hints',new Collection([$hint]));},
                static function($q):void{$hint=new VerbHint; $hint->setRawAttributes(['id'=>97001,'question_id'=>$q[3]->id],true); $q[3]->setRelation('verbHints',new Collection([$hint]));},
            ];
            foreach($changes as $change) {
                [$questions,$target]=$this->synthetic(); $change($questions); $before=$questions->toArray();
                self::assertFalse($method->invoke(null,$questions,$target)); self::assertSame($before,$questions->toArray());
            }
            self::assertSame([],DB::connection()->getQueryLog());
        } finally { DB::connection()->disableQueryLog(); }
    }

    public function test_only_complete_known_matcher_decoration_tuple_is_allowed_without_mutation(): void
    {
        $method=new ReflectionMethod(TheoryBuilderSelectionPresentation::class,'matchesQuestions');
        [$questions,$target]=$this->synthetic();
        foreach($questions as $q) {$q->setAttribute('match_score',null); $q->setAttribute('matched_tag_ids',[]); $q->setAttribute('marker_tags',[]);}
        $before=$questions->toArray(); self::assertTrue($method->invoke(null,$questions,$target)); self::assertSame($before,$questions->toArray());
        foreach(['score','tags','markers-null','markers-string','partial'] as $mutation) {
            [$questions,$target]=$this->synthetic();
            foreach($questions as $q) {$q->setAttribute('match_score',null); $q->setAttribute('matched_tag_ids',[]); $q->setAttribute('marker_tags',[]);}
            match($mutation) {
                'score'=>$questions[4]->setAttribute('match_score',1), 'tags'=>$questions[4]->setAttribute('matched_tag_ids',[1]),
                'markers-null'=>$questions[4]->setAttribute('marker_tags',null), 'markers-string'=>$questions[4]->setAttribute('marker_tags','foreign'),
                'partial'=>$questions[4]->offsetUnset('marker_tags'),
            };
            $before=$questions->toArray(); self::assertFalse($method->invoke(null,$questions,$target)); self::assertSame($before,$questions->toArray());
        }
    }

    public function test_public_bindings_fully_fall_back_for_empty_short_duplicate_or_synthetic_collections(): void
    {
        app()->setLocale('uk'); DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        try {
            $block=$this->block(0); [$questions]=$this->synthetic();
            foreach([new Collection,$questions->take(4),new Collection([$questions[0],$questions[0],$questions[2],$questions[3],$questions[4]]),$questions] as $pool) {
                $before=$pool->toArray();
                foreach([false,true] as $canonical) {
                    $bindings=['block'=>$block,'theoryCanonical'=>$canonical,'practiceQuestions'=>$pool,'unrelated'=>'retained'];
                    self::assertSame($bindings,Presentation::bindings($bindings)); self::assertSame($before,$pool->toArray());
                }
            }
            self::assertSame([],DB::connection()->getQueryLog());
        } finally { DB::connection()->disableQueryLog(); }
    }
}
