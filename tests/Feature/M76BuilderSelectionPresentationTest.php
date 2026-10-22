<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Models\VerbHint;
use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage as Digest;
use App\Support\M51QuestionSupplement;
use App\Support\M76BuilderSelectionPresentation as Presentation;
use App\Support\M76PronounRelations as Package;
use App\Support\TheoryBuilderSelectionPresentation;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Public opaque evidence plus invented, unsaved model probes; never reconstructs private database rows. */
class M76BuilderSelectionPresentationTest extends TestCase
{
    private const SELECTED = [[33928,33930,33933,33936,33937],[47523,47527,47528,47531,47533],[33843,33847,33851,33852,33854]];

    private function audit(): array
    {
        $path = dirname(__DIR__,2).'/'.Presentation::AUDIT;
        self::assertSame(Presentation::AUDIT_SHA,hash_file('sha256',$path));
        self::assertSame(Presentation::AUDIT_SHA,hash_file('sha256',base_path(Presentation::AUDIT)), 'Runtime must use the same frozen public audit.');
        $audit = Presentation::loadAudit(dirname(__DIR__,2));
        self::assertSame('frozen',$audit['status']); self::assertSame([],$audit['pending']);
        self::assertSame('m76-pronoun-relations-bank-audit',$audit['package']); self::assertSame('1.0.0',$audit['version']);
        self::assertSame('15 selected / 15 reused / 0 new; no question/answer/option/grader/progress writes',$audit['scope']);
        self::assertSame(Presentation::BEFORE_SHA,$audit['independent_before_sha256']);
        self::assertSame(Presentation::TARGETS_SHA,Digest::digest($audit['targets']));
        return $audit;
    }

    private function block(int $i): TextBlock
    {
        [, $patch] = Package::load(dirname(__DIR__,2)); $a = $this->audit()['practice_bindings'][$i];
        $source = $patch['targets'][$i]['after']['page']['blocks'][$a['source_index']];
        $block = new TextBlock;
        $block->setRawAttributes(['id'=>7690000+$i,'uuid'=>$a['uuid'],'page_id'=>$a['page_id'],'page_category_id'=>$a['category_id'],
            'seeder'=>$a['identity'],'locale'=>'uk','type'=>$a['type'],'level'=>$a['level'],'column'=>$a['column'],
            'heading'=>$a['heading'],'css_class'=>$a['css_class'],'sort_order'=>$a['source_index']+1,'body'=>$source['body']],true);
        return $block;
    }

    private function withoutQueries(callable $callback): void
    {
        self::assertSame('testing',app()->environment());
        self::assertNotSame(realpath(base_path()),realpath(app()->environmentPath()));
        self::assertSame('sqlite',config('database.default'));
        self::assertSame(':memory:',config('database.connections.sqlite.database'));
        $connection = DB::connection(); self::assertSame('sqlite',$connection->getDriverName());
        self::assertSame(':memory:',$connection->getDatabaseName());
        $connection->enableQueryLog(); $connection->flushQueryLog();
        try { $callback(); self::assertSame([],$connection->getQueryLog(),'Selection and clone guards must not query or write progress.'); }
        finally { $connection->disableQueryLog(); app()->setLocale('uk'); }
    }

    public function test_frozen_fifteen_use_exact_all_level_pools_and_preserved_null_practice_bindings(): void
    {
        $audit = $this->audit(); [, $patch, , $contract] = Package::load(dirname(__DIR__,2));
        Presentation::validateAudit($audit,$patch,$contract);
        self::assertSame([129,121,127],Package::PAGE_IDS); self::assertSame([10,10,9],Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([null,null,null],Package::LEVELS); self::assertSame([null,null,null],$audit['review_summary']['technical_filter']);
        self::assertSame([216,15,15,0],array_map(static fn ($k) => $audit['review_summary'][$k],['actual_filtered_pool','selected','reused','new']));
        $new = 0;
        foreach ($audit['targets'] as $i=>$target) {
            self::assertSame(Package::OWNERS[$i],$target['identity']); self::assertSame(Package::PAGE_IDS[$i],$target['page_id']);
            self::assertSame(20,$target['category_id']); self::assertNull($target['level']);
            self::assertSame(Package::ANCESTRIES[$i],array_column($target['category_ancestry'],'slug'));
            self::assertSame($contract['targets'][$i]['bank_seeders'],$target['bank_seeders']);
            self::assertSame(self::SELECTED[$i],$target['expected_selected_ids']);
            self::assertSame(self::SELECTED[$i],array_column($target['first_five'],'id'));
            self::assertSame($target['expected_selected_uuids'],array_column($target['first_five'],'uuid'));
            self::assertSame([72],array_values(array_map('count',$target['expected_pool_ids_by_bank'])));
            $pool = array_merge(...array_values($target['expected_pool_ids_by_bank'])); sort($pool);
            self::assertSame($pool,array_column($target['all_pool_snapshots'],'id'));
            $partition = [...$target['expected_selected_ids'],...$target['excluded_question_ids']]; sort($partition);
            self::assertSame($pool,$partition); self::assertCount(count($pool),array_unique($partition));
            self::assertSame([67,67,67][$i],count($target['excluded_question_ids']));
            foreach ($target['all_pool_snapshots'] as $snapshot) {
                self::assertCount(6,$snapshot['snapshot_hashes']);
                foreach ($snapshot['snapshot_hashes'] as $sha) { self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D',$sha); }
                foreach (['question','answers','options','option_pivots','created_at','updated_at','runtime','password','APP_KEY'] as $field) {
                    self::assertArrayNotHasKey($field,$snapshot);
                }
            }
            foreach ($target['first_five'] as $sample) {
                $new += (int)$sample['is_new'];
                self::assertSame(M51QuestionSupplement::ROW_COLUMNS['questions'],$sample['question_attribute_keys']);
                self::assertSame([], $sample['token_presentations']); self::assertSame([], $sample['option_presentations']);
                self::assertSame($sample['correct_tokens'],ComposeTokenCase::normalize($sample['raw_correct_tokens']));
                $joined=implode(' ',$sample['correct_tokens']);
                self::assertSame(preg_replace('/\\s+([?.!,;:])/u','$1',$joined.(preg_match('/[?.!]$/u',$joined)===1?'':'.')),$sample['actual_correct_visible']);
                self::assertSame($sample['actual_correct_visible'],$sample['actual_selected_visible']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u',$sample['visible_condition_uk']);
                self::assertArrayNotHasKey('original_ordered_instances',$sample,'Opaque hashes must not become private answer-row fixtures.');
                $available = array_count_values($sample['token_bank_with_multiplicity']);
                foreach (array_count_values($sample['correct_tokens']) as $token=>$count) { self::assertGreaterThanOrEqual($count,$available[$token]??0); }
            }
            $binding = $audit['practice_bindings'][$i];
            self::assertSame(Package::PRACTICE_SOURCE_INDICES[$i],$binding['source_index']);
            self::assertSame(Package::PRACTICE_UUIDS[$i],$binding['uuid']); self::assertNull($binding['level']);
            self::assertSame(hash('sha256',$patch['targets'][$i]['after']['page']['blocks'][$binding['source_index']]['body']),$binding['body_sha256']);
        }
        self::assertSame(0,$new);
        self::assertSame(['B1','A2','A2'],$audit['review_summary']['intended_selected_levels']);
        foreach($audit['targets'] as $i=>$target){foreach($target['first_five'] as $sample){
            self::assertSame(['B1','A2','A2'][$i],array_column($target['all_pool_snapshots'],'level','id')[$sample['id']]);
            if($i===1){
                self::assertSame('.',end($sample['correct_tokens']));
                self::assertSame(1,substr_count($sample['actual_correct_visible'],'.'));
                self::assertStringNotContainsString(' .',$sample['actual_correct_visible']);
            }
        }}
        $relative=array_column($audit['targets'][0]['first_five'],null,'id');
        self::assertSame(2,array_count_values($relative[33930]['correct_tokens'])['is']);
        self::assertStringContainsString('which',$relative[33928]['visible_condition_uk']);
        self::assertStringContainsString('that',$relative[33937]['visible_condition_uk']);
        $each=array_column($audit['targets'][1]['first_five'],null,'id');
        self::assertSame(['Every','one','of'],array_slice($each[47527]['correct_tokens'],0,3));
        $reciprocal=array_column($audit['targets'][2]['first_five'],null,'id');
        self::assertArrayNotHasKey(33849,$reciprocal);
        self::assertSame(2,array_count_values(array_map('strtolower',$reciprocal[33847]['correct_tokens']))['the']);
        self::assertStringContainsString('власне відображення',$reciprocal[33852]['visible_condition_uk']);
    }

    public function test_actual_native_replay_distinguishes_lexical_rejection_from_case_and_punctuation_limits(): void
    {
        $audit=$this->audit();$probe=$audit['native_runtime_probe'];
        self::assertSame('actual-unchanged-native-method-replay',$probe['kind']);
        self::assertSame(['independent-served-ROOT-before','accepted-worktree'],array_column($probe['profiles'],'profile'));
        foreach(['db_connections','db_writes','http_requests','file_writes'] as $key){self::assertSame(0,$probe[$key]);}
        $component='resources/views/components/text-block-practice-questions.blade.php';
        $fixPath='docs/content/m76-token-label-display-fix.v1.json';
        $fixSha='7b705e98f0c2be7593d1e9cb0baad57a8562c151d684ccc150f0b374b8146edd';
        foreach([dirname(__DIR__,2).'/'.$fixPath,base_path($fixPath)] as $path){self::assertSame($fixSha,hash_file('sha256',$path));}
        $fix=json_decode(file_get_contents(dirname(__DIR__,2).'/'.$fixPath),true,flags:JSON_THROW_ON_ERROR);
        self::assertSame(['m76-token-label-display-fix','1.0.0','frozen',$component],[$fix['package'],$fix['version'],$fix['status'],$fix['component']]);
        self::assertSame(['served-root','accepted-worktree'],array_column($fix['profiles'],'profile'));
        self::assertSame(1,$fix['allowed_change']['occurrences_per_profile']);
        $afterBinding='x-text="Object.prototype.hasOwnProperty.call(currentQuestion?.token_labels ?? {}, token.value) && typeof currentQuestion.token_labels[token.value] === \'string\' ? currentQuestion.token_labels[token.value] : token.value"';
        self::assertSame($afterBinding,$fix['allowed_change']['after_binding']);
        foreach([dirname(__DIR__,2).'/'.$component,base_path($component)] as $path){
            $raw=file_get_contents($path);self::assertIsString($raw);$lf=str_replace("\r\n","\n",$raw);
            $successors=array_values(array_filter($fix['profiles'],static fn($p)=>hash('sha256',$raw)===$p['after_raw_sha256']&&hash('sha256',$lf)===$p['after_lf_sha256']));
            self::assertCount(1,$successors,'Only one explicitly approved current raw/LF display profile is accepted.');
            $successor=$successors[0];
            self::assertSame(1,substr_count($raw,$afterBinding),'Only the available-token label binding may differ from the frozen widget.');
            $predecessor=str_replace($afterBinding,$successor['before_binding'],$raw,$replacements);
            self::assertSame(1,$replacements);
            self::assertSame($successor['before_raw_sha256'],hash('sha256',$predecessor),'The exact one-attribute reversal restores its own immutable predecessor bytes.');
            self::assertSame(1,preg_match_all('/<script(?:\s[^>]*)?>(.*?)<\/script>/s',$raw,$embedded));
            self::assertSame($successor['embedded_script_count'],count($embedded[1]));
            self::assertSame($successor['unchanged_embedded_script_sha256'],hash('sha256',$embedded[0][0]),'The approved display fix does not change the complete script element or widget engine methods.');
            $take=static function(string $start,string $end)use($lf):string{
                $i=strpos($lf,$start);$j=strpos($lf,$end,$i===false?0:$i);
                self::assertIsInt($i);self::assertIsInt($j);self::assertGreaterThan($i,$j);
                return substr($lf,$i,$j-$i);
            };
            $methods=$take('                composeCorrectText() {','                getDisplayText() {')
                .$take('                checkAnswer() {','                hasMoreQuestions() {');
            $tuple=[hash('sha256',$predecessor),hash('sha256',str_replace("\r\n","\n",$predecessor)),hash('sha256',$methods)];
            $coherent=array_filter($probe['profiles'],static fn($p)=>$tuple===[$p['native_raw_sha256'],$p['native_lf_sha256'],$p['method_source_sha256']]);
            self::assertCount(1,$coherent,'No arbitrary bytes or cross-profile hash combination is accepted.');
            self::assertSame($successor['profile']==='served-root'?'independent-served-ROOT-before':'accepted-worktree',array_values($coherent)[0]['profile']);
        }
        foreach($probe['profiles'] as $profile){
            self::assertSame(array_merge(...self::SELECTED),array_column($profile['selected'],'id'));
            foreach($profile['selected'] as $result){
                self::assertTrue($result['canonical_check']);self::assertTrue($result['empty_rejected']);
                self::assertTrue($result['repeated_check_stable']);self::assertGreaterThan(20,$result['adversarial_sequences']);
                self::assertSame([],$result['wrong_lexical_visible_scored_correct']);
                self::assertGreaterThanOrEqual(0,$result['formatting_only_normalization_matches']);
            }
            self::assertStringContainsString('not graded strictly',$profile['formatting_boundary']);
            self::assertSame(33849,$profile['excluded_apostrophe_trap']['id']);
            self::assertTrue($profile['excluded_apostrophe_trap']['reproduced']);
            self::assertNotEmpty($profile['excluded_apostrophe_trap']['wrong_lexical_visible_scored_correct']);
            self::assertStringContainsString('each others',json_encode($profile['excluded_apostrophe_trap'],JSON_THROW_ON_ERROR));
        }
        $excluded=array_column($audit['targets'][2]['known_scope_exclusions'],'reason','id');
        self::assertStringContainsString('Apostrophe-only',$excluded[33849]);
        self::assertContains(33849,$audit['targets'][2]['excluded_question_ids']);
    }

    public function test_changed_audit_policy_pool_snapshots_conditions_and_binding_metadata_fail_closed(): void
    {
        $audit = $this->audit(); [, $patch, , $contract] = Package::load(dirname(__DIR__,2));
        $changes = [
            'draft'=>static function (&$a): void { $a['status']='draft'; },
            'scope'=>static function (&$a): void { $a['scope'].=' expanded'; },
            'before'=>static function (&$a): void { $a['independent_before_sha256']=str_repeat('0',64); },
            'owner'=>static function (&$a): void { $a['targets'][0]['identity']='Foreign\\Owner'; },
            'category'=>static function (&$a): void { $a['targets'][1]['category_id']=16; },
            'range-is-not-null'=>static function (&$a): void { $a['targets'][2]['level']='A2–B1'; },
            'snapshot'=>static function (&$a): void { $a['targets'][1]['first_five'][0]['snapshot_hashes']['question_snapshot']=str_repeat('0',64); },
            'condition'=>static function (&$a): void { $a['targets'][2]['first_five'][0]['visible_condition_uk']='Інший задум.'; },
            'missing-selected'=>static function (&$a): void { array_pop($a['targets'][0]['first_five']); },
            'missing-exclusion'=>static function (&$a): void { array_pop($a['targets'][1]['excluded_question_ids']); },
            'pool'=>static function (&$a): void { array_pop($a['targets'][2]['expected_pool_ids_by_bank'][$a['targets'][2]['bank_seeders'][0]]); },
            'uuid'=>static function (&$a): void { $a['practice_bindings'][0]['uuid']='foreign'; },
            'index'=>static function (&$a): void { $a['practice_bindings'][1]['source_index']++; },
            'body'=>static function (&$a): void { $a['practice_bindings'][2]['body_sha256']=str_repeat('0',64); },
        ];
        foreach ($changes as $name=>$change) {
            $bad = $audit; $change($bad); $rejected = false;
            try { Presentation::validateAudit($bad,$patch,$contract); } catch (RuntimeException) { $rejected=true; }
            self::assertTrue($rejected,$name);
        }
        foreach (['A2','A2–B1'] as $level) {
            $bad = $contract; $bad['targets'][1]['level']=$level; $rejected=false;
            try { Presentation::validateAudit($audit,$patch,$bad); } catch (RuntimeException) { $rejected=true; }
            self::assertTrue($rejected,'Semantic focus must never replace actual SQL NULL.');
        }
        self::assertSame($audit,$this->audit());
    }

    public function test_only_exact_canonical_block_provenance_extends_the_finite_exclusions_without_queries(): void
    {
        $this->withoutQueries(function (): void {
            app()->setLocale('uk');
            foreach ($this->audit()['targets'] as $i=>$target) {
                $block=$this->block($i); $before=$block->getAttributes();
                $config=['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>$target['bank_seeders']];
                self::assertSame([999],Presentation::exclusions($block,$config,[999]));
                $clone=Presentation::matchingBlock($block,true); self::assertNotSame($block,$clone);
                self::assertSame([999,...$target['excluded_question_ids']],Presentation::exclusions($clone,$config,[999]));
                self::assertSame([999],Presentation::exclusions(clone $clone,$config,[999]));
                self::assertSame([$target['expected_selected_ids'][0]],Presentation::exclusions($clone,$config,[$target['expected_selected_ids'][0]]));
                $foreignConfig=$config; $foreignConfig['seeder_classes']=['Foreign\\Bank'];
                self::assertSame([999],Presentation::exclusions($clone,$foreignConfig,[999]));
                foreach (['seeder'=>'Foreign\\Owner','locale'=>'en','page_id'=>1,'page_category_id'=>16,'type'=>'box',
                    'uuid'=>'foreign','column'=>'right','heading'=>'foreign','css_class'=>'foreign','sort_order'=>99,'body'=>$block->body."\n"] as $field=>$value) {
                    $foreign=clone $block; $foreign->setRawAttributes(array_replace($before,[$field=>$value]),true);
                    self::assertSame($foreign,Presentation::matchingBlock($foreign,true),$field);
                }
                foreach (['A2','A2–B1',''] as $level) {
                    $foreign=clone $block; $foreign->setRawAttributes(array_replace($before,['level'=>$level]),true);
                    self::assertSame($foreign,Presentation::matchingBlock($foreign,true));
                }
                self::assertSame($block,Presentation::matchingBlock($block,false));
                app()->setLocale('en'); self::assertSame($block,Presentation::matchingBlock($block,true)); app()->setLocale('uk');
                self::assertSame($before,$block->getAttributes()); self::assertSame([],$block->getDirty()); self::assertFalse($block->exists);
            }
        });
    }

    /** Every attribute, timestamp and relation below is invented; only the public teaching IDs/order are reused. */
    private function synthetic(int $index=0): array
    {
        $target=$this->audit()['targets'][$index]; $questions=[];
        foreach ($target['first_five'] as $n=>&$sample) {
            $id=$sample['id']; $attrs=['id'=>$id,'uuid'=>$sample['uuid'],'question'=>'Синтетична незбережена умова '.$n.'.',
                'difficulty'=>2,'level'=>'A2','category_id'=>76000,'source_id'=>76001,'flag'=>0,'type'=>'4','options_by_marker'=>null,
                'theory_text_block_uuid'=>null,'seeder'=>'SyntheticM76Only','created_at'=>'2000-01-01 00:00:00','updated_at'=>'2000-01-01 00:00:00'];
            $q=new Question; $q->setRawAttributes($attrs,true); $options=$answers=$or=$ar=$pr=[];
            foreach (['These','children','are','not','hungry'] as $i=>$token) {
                $oid=761000+$index*1000+$n*10+$i; $aid=762000+$index*1000+$n*10+$i;
                $o=new QuestionOption; $o->setRawAttributes(['id'=>$oid,'option'=>$token],true);
                $pivot=['question_id'=>$id,'option_id'=>$oid,'flag'=>null]; $p=new Pivot; $p->setRawAttributes($pivot,true); $o->setRelation('pivot',$p);
                $a=new QuestionAnswer; $a->setRawAttributes(['id'=>$aid,'question_id'=>$id,'option_id'=>$oid,'marker'=>'a'.($i+1),'position'=>null],true);
                $a->setRelation('option',$o); $options[]=$o; $answers[]=$a;
                $or[]=['id'=>$oid,'option'=>$token]; $ar[]=['id'=>$aid,'marker'=>'a'.($i+1),'position'=>null,'option_id'=>$oid,'option'=>$token]; $pr[]=$pivot;
            }
            $q->setRelation('answers',new Collection($answers)); $q->setRelation('options',new Collection($options));
            $q->setRelation('hints',new Collection); $q->setRelation('verbHints',new Collection); $questions[]=$q;
            $sample['snapshot_hashes']=array_map(Digest::digest(...),['question_snapshot'=>$attrs,'ordered_answer_snapshot'=>$ar,
                'original_options_snapshot'=>$or,'loaded_option_pivots'=>$pr,'hints_snapshot'=>[],'verb_hints_snapshot'=>[]]);
        }
        unset($sample);
        return [new Collection($questions),$target];
    }

    public function test_shared_snapshot_matcher_rejects_stale_or_missing_synthetic_relations_without_original_mutation(): void
    {
        $this->withoutQueries(function (): void {
            $method=new ReflectionMethod(TheoryBuilderSelectionPresentation::class,'matchesQuestions');
            [$questions,$target]=$this->synthetic(); $before=$questions->toArray();
            self::assertTrue($method->invoke(null,$questions,$target)); self::assertSame($before,$questions->toArray());
            $changes=[
                fn($q)=>$q[4]->setAttribute('question','Змінено.'), fn($q)=>$q[3]->setAttribute('created_at','2001-01-01 00:00:00'),
                fn($q)=>$q[2]->setAttribute('seeder','Foreign'), fn($q)=>$q[1]->setAttribute('level','B1'),
                fn($q)=>$q[0]->answers[0]->option->setAttribute('option','Changed'), fn($q)=>$q[1]->answers[0]->setAttribute('marker','a99'),
                fn($q)=>$q[2]->options[0]->pivot->setAttribute('flag',1), fn($q)=>$q[3]->options[0]->unsetRelation('pivot'),
                fn($q)=>$q[4]->answers[0]->unsetRelation('option'), fn($q)=>$q[0]->unsetRelation('answers'),
                fn($q)=>$q[1]->unsetRelation('options'), fn($q)=>$q[2]->unsetRelation('hints'), fn($q)=>$q[3]->unsetRelation('verbHints'),
                fn($q)=>$q[4]->setAttribute('foreign_decoration',[]),
                static function($q):void{$h=new QuestionHint; $h->setRawAttributes(['id'=>1,'question_id'=>$q[0]->id,'provider'=>'synthetic','locale'=>'uk','hint'=>'Нова підказка.'],true);$q[0]->setRelation('hints',new Collection([$h]));},
                static function($q):void{$h=new VerbHint;$h->setRawAttributes(['id'=>2,'question_id'=>$q[0]->id],true);$q[0]->setRelation('verbHints',new Collection([$h]));},
            ];
            foreach($changes as $change){[$q,$t]=$this->synthetic();$change($q);$before=$q->toArray();self::assertFalse($method->invoke(null,$q,$t));self::assertSame($before,$q->toArray());}
            [$q,$t]=$this->synthetic(); foreach($q as $row){$row->setAttribute('match_score',null);$row->setAttribute('matched_tag_ids',[]);$row->setAttribute('marker_tags',[]);}
            self::assertTrue($method->invoke(null,$q,$t));$q[0]->setAttribute('match_score',1);self::assertFalse($method->invoke(null,$q,$t));
            foreach ([new Collection,$questions->take(4),new Collection([$questions[0],$questions[0],$questions[2],$questions[3],$questions[4]]),$questions] as $pool) {
                $bindings=['block'=>$this->block(0),'theoryCanonical'=>true,'practiceQuestions'=>$pool,'unrelated'=>'retained']; $before=$pool->toArray();
                self::assertSame($bindings,Presentation::bindings($bindings),'Real opaque snapshots cannot be authorized by synthetic models.');
                self::assertSame($before,$pool->toArray());
            }
        });
    }

    public function test_inherited_bindings_clone_only_conditions_for_complete_synthetic_evidence(): void
    {
        $this->withoutQueries(function (): void {
            $release=Package::load(dirname(__DIR__,2)); $audit=$this->audit(); $collections=[]; $blocks=[];
            foreach($audit['targets'] as $i=>$_){[$collections[$i],$audit['targets'][$i]]=$this->synthetic($i);$blocks[$i]=$this->block($i);}
            M76SyntheticBuilderPackage::$release=$release;
            $directory=storage_path('framework/testing/m76-builder-'.bin2hex(random_bytes(8)));
            self::assertTrue(mkdir($directory,0700)); IsolatedTestEnvironment::assertOwnedPath($directory);
            $file=$directory.'/audit.json'; $bytes=Digest::json($audit); self::assertSame(strlen($bytes),file_put_contents($file,$bytes,LOCK_EX));
            M76SyntheticBuilderPresentation::$auditSha=hash('sha256',$bytes);
            M76SyntheticBuilderPresentation::$targetsSha=Digest::digest($audit['targets']);
            $root=base_path();
            try {
                app()->setBasePath($directory); app()->setLocale('uk');
                foreach($collections as $i=>$questions){
                    $before=$questions->toArray();$blockBefore=$blocks[$i]->getAttributes();
                    $bindings=['block'=>$blocks[$i],'theoryCanonical'=>true,'practiceQuestions'=>$questions,'unrelated'=>'retained'];
                    $out=M76SyntheticBuilderPresentation::bindings($bindings); self::assertSame('retained',$out['unrelated']);
                    self::assertNotSame($questions,$out['practiceQuestions']);
                    foreach($out['practiceQuestions'] as $n=>$copy){
                        self::assertNotSame($questions[$n],$copy); $expected=$questions[$n]->getAttributes();
                        $expected['question']=$audit['targets'][$i]['first_five'][$n]['visible_condition_uk'];
                        self::assertSame($expected,$copy->getAttributes());self::assertSame([],$copy->getDirty());self::assertFalse($copy->exists);
                        foreach(['answers','options','hints','verbHints'] as $relation){self::assertSame($questions[$n]->getRelation($relation),$copy->getRelation($relation));}
                    }
                    self::assertSame($before,$questions->toArray());self::assertSame($blockBefore,$blocks[$i]->getAttributes());
                    $short=$bindings;$short['practiceQuestions']=$questions->take(4);self::assertSame($short,M76SyntheticBuilderPresentation::bindings($short));
                    $course=$bindings;$course['theoryCanonical']=false;self::assertSame($course,M76SyntheticBuilderPresentation::bindings($course));
                }
            } finally {
                app()->setBasePath($root); IsolatedTestEnvironment::assertOwnedPath($file); unlink($file); rmdir($directory);
                M76SyntheticBuilderPackage::$release=[];
            }
        });
    }
}

/** Test-only package facade: real public block metadata, invented question snapshots, no release/source mutation. */
final class M76SyntheticBuilderPackage
{
    public const OWNERS=Package::OWNERS, PAGE_IDS=Package::PAGE_IDS, CATEGORY_IDS=Package::CATEGORY_IDS,
        LEVELS=Package::LEVELS, ANCESTRIES=Package::ANCESTRIES, PRACTICE_SOURCE_INDICES=Package::PRACTICE_SOURCE_INDICES;
    public static array $release=[];
    public static function load(?string $root=null):array{return self::$release;}
    public static function presentation(object $block,array $data):?array
    {
        $i=array_search($block->seeder,self::OWNERS,true);
        if($i===false){return null;}
        $source=self::$release[1]['targets'][$i]['after']['page']['blocks'][self::PRACTICE_SOURCE_INDICES[$i]];
        return $data===json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR)?['synthetic_guard_probe'=>true]:null;
    }
}

/** Exercises the production inherited implementation against an exclusive ephemeral synthetic audit only. */
final class M76SyntheticBuilderPresentation extends TheoryBuilderSelectionPresentation
{
    public static string $auditSha='', $targetsSha='';
    protected static function packageClass():string{return M76SyntheticBuilderPackage::class;}
    protected static function policy():array
    {
        return ['label'=>'M76 synthetic test','package'=>'m76-pronoun-relations-bank-audit','version'=>'1.0.0',
            'scope'=>'15 selected / 15 reused / 0 new; no question/answer/option/grader/progress writes',
            'before_sha256'=>Presentation::BEFORE_SHA,'audit'=>'audit.json','audit_sha256'=>self::$auditSha,'targets_sha256'=>self::$targetsSha];
    }
}
