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
use App\Support\M77BuilderSelectionPresentation as Presentation;
use App\Support\M77OnePrepositionsPhrasal as Package;
use App\Support\TheoryBuilderSelectionPresentation;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Public opaque evidence plus invented, unsaved model probes; never reconstructs private database rows. */
class M77BuilderSelectionPresentationTest extends TestCase
{
    private const SELECTED = [[47595,47596,47597,47599,47600],[42831,42832,42833,42834,42840],[33183,33185,33186,33192,33194],[42915,42916,42918,42919,42923],[42999,43002,43003,43006,43009],[43287,43290,43291,43293,43295]];

    private function audit(): array
    {
        $path = dirname(__DIR__,2).'/'.Presentation::AUDIT;
        self::assertSame(Presentation::AUDIT_SHA,hash_file('sha256',$path));
        self::assertSame(Presentation::AUDIT_SHA,hash_file('sha256',base_path(Presentation::AUDIT)), 'Runtime must use the same frozen public audit.');
        $audit = Presentation::loadAudit(dirname(__DIR__,2));
        self::assertSame('frozen',$audit['status']); self::assertSame([],$audit['pending']);
        self::assertSame('m77-one-prepositions-phrasal-bank-audit',$audit['package']); self::assertSame('1.0.0',$audit['version']);
        self::assertSame('30 selected / 30 reused / 0 new; no question/answer/option/grader/progress writes',$audit['scope']);
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

    public function test_frozen_thirty_use_exact_eligible_pools_and_actual_practice_levels(): void
    {
        $audit=$this->audit();[, $patch, , $contract]=Package::load(dirname(__DIR__,2));
        Presentation::validateAudit($audit,$patch,$contract);
        self::assertSame([123,120,119,118,115,116],Package::PAGE_IDS);
        self::assertSame([8,6,6,6,6,6],Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([null,'A1','A1','A2','B1','B1'],Package::LEVELS);
        self::assertSame(Package::LEVELS,$audit['review_summary']['technical_filter']);
        self::assertSame([132,30,30,0],array_map(static fn($k)=>$audit['review_summary'][$k],['actual_filtered_pool','selected','reused','new']));
        self::assertSame(432,$audit['review_summary']['pagewide_original_type4_pool']);
        self::assertSame(['A2','A1','A1','A2','B1','B1'],$audit['review_summary']['intended_selected_levels']);
        self::assertCount(6,$audit['targets']);self::assertCount(6,$audit['practice_bindings']);
        $new=0;$totalPool=0;
        foreach($audit['targets'] as $i=>$target) {
            self::assertSame(Package::OWNERS[$i],$target['identity']);self::assertSame(Package::PAGE_IDS[$i],$target['page_id']);
            self::assertSame(Package::CATEGORY_IDS[$i],$target['category_id']);self::assertSame(Package::LEVELS[$i],$target['level']);
            self::assertSame(Package::ANCESTRIES[$i],array_column($target['category_ancestry'],'slug'));
            self::assertSame($contract['targets'][$i]['bank_seeders'],$target['bank_seeders']);
            self::assertSame(self::SELECTED[$i],$target['expected_selected_ids']);
            self::assertSame(self::SELECTED[$i],array_column($target['first_five'],'id'));
            self::assertSame($target['expected_selected_uuids'],array_column($target['first_five'],'uuid'));
            self::assertSame([$i===0?72:12],array_values(array_map('count',$target['expected_pool_ids_by_bank'])));
            $pool=array_merge(...array_values($target['expected_pool_ids_by_bank']));sort($pool);$totalPool+=count($pool);
            self::assertSame($pool,array_column($target['all_pool_snapshots'],'id'));
            $partition=[...$target['expected_selected_ids'],...$target['excluded_question_ids']];sort($partition);
            self::assertSame($pool,$partition);self::assertCount(count($pool),array_unique($partition));
            self::assertCount($i===0?67:7,$target['excluded_question_ids']);
            self::assertCount(count($pool),$target['all_eligible_review']);
            self::assertCount(5,array_filter($target['all_eligible_review'],static fn($r)=>$r['decision']==='selected'));
            foreach($target['all_pool_snapshots'] as $snapshot) {
                self::assertCount(6,$snapshot['snapshot_hashes']);
                foreach($snapshot['snapshot_hashes'] as $sha) { self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D',$sha); }
                foreach(['question','answers','options','option_pivots','created_at','updated_at','runtime','password','APP_KEY'] as $field) {
                    self::assertArrayNotHasKey($field,$snapshot);
                }
                if($i!==0) { self::assertSame(Package::LEVELS[$i],$snapshot['level']); }
            }
            foreach($target['first_five'] as $sample) {
                $new+=(int)$sample['is_new'];
                self::assertSame(M51QuestionSupplement::ROW_COLUMNS['questions'],$sample['question_attribute_keys']);
                self::assertSame([],$sample['token_presentations']);self::assertSame([],$sample['option_presentations']);
                self::assertSame($sample['correct_tokens'],ComposeTokenCase::normalize($sample['raw_correct_tokens']));
                self::assertSame(Package::FOCUS_LEVELS[$i],array_column($target['all_pool_snapshots'],'level','id')[$sample['id']]);
                $joined=implode(' ',$sample['correct_tokens']);
                self::assertSame(preg_replace('/\\s+([?.!,;:])/u','$1',$joined.(preg_match('/[?.!]$/u',$joined)===1?'':'.')),$sample['actual_correct_visible']);
                self::assertSame($sample['actual_correct_visible'],$sample['actual_selected_visible']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u',$sample['visible_condition_uk']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u',$sample['translation_uk']);
                self::assertDoesNotMatchRegularExpression('/<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|[a-z]+);/iu',$sample['visible_condition_uk']);
                self::assertArrayNotHasKey('original_ordered_instances',$sample);
                $available=array_count_values($sample['token_bank_with_multiplicity']);
                foreach(array_count_values($sample['correct_tokens']) as $token=>$count) {
                    self::assertGreaterThanOrEqual($count,$available[$token]??0);
                }
                self::assertStringNotContainsString(' .',$sample['actual_correct_visible']);
                self::assertStringNotContainsString(' ?',$sample['actual_correct_visible']);
            }
            $binding=$audit['practice_bindings'][$i];$source=$patch['targets'][$i]['after']['page']['blocks'][$binding['source_index']];
            self::assertSame(Package::PRACTICE_SOURCE_INDICES[$i],$binding['source_index']);
            self::assertSame(Digest::uuid(Package::OWNERS[$i],$source,$binding['source_index']+1),$binding['uuid']);
            self::assertSame(Package::LEVELS[$i],$binding['level']);self::assertSame('left',$binding['column']);
            self::assertSame(hash('sha256',$source['body']),$binding['body_sha256']);
        }
        self::assertSame(132,$totalPool);self::assertSame(0,$new);
        $one=array_column($audit['targets'][0]['first_five'],null,'id');
        $counts=array_count_values(array_map('strtolower',$one[47595]['correct_tokens']));
        self::assertSame(2,$counts['the']);self::assertSame(2,$counts['one']);
        $dependent=$audit['targets'][4];
        self::assertContains(43000,$dependent['excluded_question_ids']);
        self::assertStringContainsString('Mathematics',array_column($dependent['known_scope_exclusions'],'reason','id')[43000]);
        $phrasal=array_column($audit['targets'][5]['first_five'],null,'id');
        self::assertStringContainsString('повний додаток',$phrasal[43291]['visible_condition_uk']);
        self::assertSame('Please turn off the light.',$phrasal[43291]['actual_correct_visible']);
        $walk=static function($value)use(&$walk):void{
            if(!is_array($value)) { return; }
            foreach($value as $key=>$child) {
                if(is_string($key)) {
                    self::assertDoesNotMatchRegularExpression('/^(?:created_at|updated_at|started_at|finished_at|served_root|source_worktree|sql_before|sql_after_http|password|secret|credentials|physical|runtime_identity)$/iD',$key);
                }
                $walk($child);
            }
        };
        $walk($audit);
    }

    public function test_actual_unchanged_native_formatter_and_grader_cover_all_thirty_on_both_profiles(): void
    {
        $this->withoutQueries(function():void {
            $audit=$this->audit();$probe=$audit['native_runtime_probe'];
            self::assertSame('actual-unchanged-native-method-replay',$probe['kind']);
            self::assertSame(['independent-served-ROOT-before','accepted-worktree'],array_column($probe['profiles'],'profile'));
            foreach(['db_connections','db_writes','http_requests','file_writes'] as $key) { self::assertSame(0,$probe[$key]); }
            $component='resources/views/components/text-block-practice-questions.blade.php';$profiles=[];
            foreach([base_path($component),dirname(__DIR__,2).'/'.$component] as $path) {
                $raw=file_get_contents($path);self::assertIsString($raw);$lf=str_replace("\r\n","\n",$raw);
                $take=static function(string $start,string $end)use($lf):string{
                    $i=strpos($lf,$start);$j=strpos($lf,$end,$i===false?0:$i);
                    self::assertIsInt($i);self::assertIsInt($j);self::assertGreaterThan($i,$j);
                    return substr($lf,$i,$j-$i);
                };
                $methods=$take('                composeCorrectText() {','                getDisplayText() {')
                    .$take('                checkAnswer() {','                hasMoreQuestions() {');
                $tuple=[hash('sha256',$raw),hash('sha256',$lf),hash('sha256',$methods)];
                $coherent=array_values(array_filter($probe['profiles'],static fn($p)=>$tuple===[$p['native_raw_sha256'],$p['native_lf_sha256'],$p['method_source_sha256']]));
                self::assertCount(1,$coherent,'Each complete native source tuple must match one approved current profile, never blended hash pieces.');
                self::assertSame(array_merge(...self::SELECTED),array_column($coherent[0]['selected'],'id'));
                $profiles[]=['path'=>$path,'raw_sha256'=>$tuple[0],'method_sha256'=>$tuple[2]];
            }
            foreach($probe['profiles'] as $profile) {
                self::assertSame(array_merge(...self::SELECTED),array_column($profile['selected'],'id'));
                self::assertStringContainsString('not graded strictly',$profile['formatting_boundary']);
                self::assertArrayNotHasKey('excluded_apostrophe_trap',$profile,'The prior M76 trap belongs to its own regression suite, not this finite thirty.');
                foreach($profile['selected'] as $result) {
                    self::assertTrue($result['canonical_check']);self::assertTrue($result['empty_rejected']);
                    self::assertTrue($result['repeated_check_stable']);self::assertGreaterThan(20,$result['adversarial_sequences']);
                    self::assertSame([],$result['wrong_lexical_visible_scored_correct']);
                    self::assertGreaterThanOrEqual(0,$result['formatting_only_normalization_matches']);
                }
            }
            $samples=[];
            foreach($audit['targets'] as $target) { foreach($target['first_five'] as $sample) {
                $samples[]=['id'=>$sample['id'],'condition'=>$sample['visible_condition_uk'],
                    'correct'=>$sample['correct_tokens'],'settled'=>$sample['actual_correct_visible']];
            } }
            $node=<<<'JS'
const fs=require('fs'),vm=require('vm'),crypto=require('crypto'),assert=require('assert/strict');
const input=JSON.parse(fs.readFileSync(0,'utf8'));let checked=0;
for(const profile of input.profiles){
 const bytes=fs.readFileSync(profile.path),sha=x=>crypto.createHash('sha256').update(x).digest('hex');
 assert.equal(sha(bytes),profile.raw_sha256);
 const text=bytes.toString('utf8').replace(/\r\n/g,'\n');
 function take(a,b){const i=text.indexOf(a),j=text.indexOf(b,i);assert.ok(i>=0&&j>i);return text.slice(i,j);}
 const source=take('                composeCorrectText() {','                getDisplayText() {')+take('                checkAnswer() {','                hasMoreQuestions() {');
 assert.equal(sha(Buffer.from(source)),profile.method_sha256);
 const methods=vm.runInNewContext('({'+source+'})',{window:{}},{timeout:1000});
 for(const sample of input.samples){
  function run(tokens){
   const m={...methods,currentQuestion:{question:sample.condition,correct_tokens:sample.correct,type:'4',authored_compose:false},
    selectedTokens:tokens.map((value,id)=>({id,value})),answered:false,isCorrect:false,correctCount:0,currentIndex:0,answeredIndices:[],
    isComposeQuestion(){return true;},persistState(){}};
   m.correctAnswer=m.composeCorrectText();assert.equal(m.correctAnswer,sample.settled);
   m.checkAnswer();const count=m.correctCount;m.checkAnswer();assert.equal(m.correctCount,count);
   return{ok:m.isCorrect,visible:m.composeSelectedText()};
  }
  const correct=run(sample.correct);assert.equal(correct.ok,true);assert.equal(correct.visible,sample.settled);
  assert.equal(run([]).ok,false);
  const wrong=[...sample.correct];[wrong[0],wrong[1]]=[wrong[1],wrong[0]];assert.equal(run(wrong).ok,false);
  checked++;
 }
}
process.stdout.write(JSON.stringify({checked,profiles:input.profiles.length,db_writes:0,http_requests:0,file_writes:0}));
JS;
            $process=new \Symfony\Component\Process\Process(['node','-e',$node],dirname(__DIR__,2));
            $process->setTimeout(30);$process->setInput(json_encode(['profiles'=>$profiles,'samples'=>$samples],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
            $process->run();self::assertTrue($process->isSuccessful(),$process->getErrorOutput());
            $result=json_decode($process->getOutput(),true,flags:JSON_THROW_ON_ERROR);
            self::assertSame(['checked'=>60,'profiles'=>2,'db_writes'=>0,'http_requests'=>0,'file_writes'=>0],$result);
        });
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
        foreach ([null,'A1-A2'] as $level) {
            $bad = $contract; $bad['targets'][1]['level']=$level; $rejected=false;
            try { Presentation::validateAudit($audit,$patch,$bad); } catch (RuntimeException) { $rejected=true; }
            self::assertTrue($rejected,'A missing technical filter or a display range must never replace exact A1.');
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
                foreach (array_values(array_filter([null,'A1','A2','B1','A1-A2','A2–B1',''],static fn($value)=>$value!==Package::LEVELS[$i])) as $level) {
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
                'difficulty'=>2,'level'=>Package::FOCUS_LEVELS[$index],'category_id'=>76000,'source_id'=>76001,'flag'=>0,'type'=>'4','options_by_marker'=>null,
                'theory_text_block_uuid'=>null,'seeder'=>'SyntheticM77Only','created_at'=>'2000-01-01 00:00:00','updated_at'=>'2000-01-01 00:00:00'];
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

    public function test_shared_snapshot_matcher_rejects_all_six_synthetic_stale_missing_unknown_and_null_families(): void
    {
        $this->withoutQueries(function():void {
            $method=new ReflectionMethod(TheoryBuilderSelectionPresentation::class,'matchesQuestions');
            foreach(Package::OWNERS as $index=>$_) {
                [$questions,$target]=$this->synthetic($index);$before=$questions->toArray();
                self::assertTrue($method->invoke(null,$questions,$target));
                self::assertSame($before,$questions->toArray());
                $changes=[
                    fn($q)=>$q[4]->setAttribute('question','Змінено.'),
                    fn($q)=>$q[3]->setAttribute('created_at','2001-01-01 00:00:00'),
                    fn($q)=>$q[2]->setAttribute('seeder','Foreign'),
                    fn($q)=>$q[1]->setAttribute('level','C2'),
                    fn($q)=>$q[0]->answers[0]->option->setAttribute('option','Changed'),
                    fn($q)=>$q[1]->answers[0]->setAttribute('marker','a99'),
                    fn($q)=>$q[2]->options[0]->pivot->setAttribute('flag',1),
                    fn($q)=>$q[3]->options[0]->unsetRelation('pivot'),
                    fn($q)=>$q[4]->answers[0]->unsetRelation('option'),
                    fn($q)=>$q[0]->unsetRelation('answers'),
                    fn($q)=>$q[1]->unsetRelation('options'),
                    fn($q)=>$q[2]->unsetRelation('hints'),
                    fn($q)=>$q[3]->unsetRelation('verbHints'),
                    fn($q)=>$q[4]->setAttribute('foreign_decoration',[]),
                    fn($q)=>$q[4]->setAttribute('foreign_null_decoration',null),
                    fn($q)=>$q[4]->setAttribute('match_score',null),
                    static function($q):void {
                        $h=new QuestionHint;$h->setRawAttributes(['id'=>1,'question_id'=>$q[0]->id,'provider'=>'synthetic','locale'=>'uk','hint'=>'Нова підказка.'],true);
                        $q[0]->setRelation('hints',new Collection([$h]));
                    },
                    static function($q):void {
                        $h=new VerbHint;$h->setRawAttributes(['id'=>2,'question_id'=>$q[0]->id],true);
                        $q[0]->setRelation('verbHints',new Collection([$h]));
                    },
                ];
                foreach($changes as $change) {
                    [$q,$t]=$this->synthetic($index);$change($q);$before=$q->toArray();
                    self::assertFalse($method->invoke(null,$q,$t));
                    self::assertSame($before,$q->toArray(),'Even invalid originals remain unchanged.');
                }
                [$q,$t]=$this->synthetic($index);
                foreach($q as $row) {
                    $row->setAttribute('match_score',null);$row->setAttribute('matched_tag_ids',[]);$row->setAttribute('marker_tags',[]);
                }
                self::assertTrue($method->invoke(null,$q,$t),'The exact caller decoration trio with a NULL score remains valid.');
                $before=$q->toArray();self::assertTrue($method->invoke(null,$q,$t));self::assertSame($before,$q->toArray());
                $q[0]->setAttribute('match_score',1);self::assertFalse($method->invoke(null,$q,$t));
                foreach([new Collection,$questions->take(4),new Collection([$questions[0],$questions[0],$questions[2],$questions[3],$questions[4]]),$questions] as $pool) {
                    $bindings=['block'=>$this->block($index),'theoryCanonical'=>true,'practiceQuestions'=>$pool,'unrelated'=>'retained'];$before=$pool->toArray();
                    self::assertSame($bindings,Presentation::bindings($bindings),'Real opaque snapshots cannot be authorized by fabricated models.');
                    self::assertSame($before,$pool->toArray());
                }
                foreach([false,null] as $canonical) {
                    $bindings=['block'=>$this->block($index),'theoryCanonical'=>$canonical,'practiceQuestions'=>$questions,'unrelated'=>'retained'];
                    self::assertSame($bindings,Presentation::bindings($bindings),'Course and standalone callers never opt into theory-only clones.');
                }
            }
        });
    }

    public function test_inherited_bindings_clone_only_conditions_for_complete_synthetic_evidence(): void
    {
        $this->withoutQueries(function (): void {
            $release=Package::load(dirname(__DIR__,2)); $audit=$this->audit(); $collections=[]; $blocks=[];
            foreach($audit['targets'] as $i=>$_){[$collections[$i],$audit['targets'][$i]]=$this->synthetic($i);$blocks[$i]=$this->block($i);}
            M77SyntheticBuilderPackage::$release=$release;
            $directory=storage_path('framework/testing/m77-builder-'.bin2hex(random_bytes(8)));
            self::assertTrue(mkdir($directory,0700)); IsolatedTestEnvironment::assertOwnedPath($directory);
            $file=$directory.'/audit.json'; $bytes=Digest::json($audit); self::assertSame(strlen($bytes),file_put_contents($file,$bytes,LOCK_EX));
            M77SyntheticBuilderPresentation::$auditSha=hash('sha256',$bytes);
            M77SyntheticBuilderPresentation::$targetsSha=Digest::digest($audit['targets']);
            $root=base_path();
            try {
                app()->setBasePath($directory); app()->setLocale('uk');
                foreach($collections as $i=>$questions){
                    $before=$questions->toArray();$blockBefore=$blocks[$i]->getAttributes();
                    $bindings=['block'=>$blocks[$i],'theoryCanonical'=>true,'practiceQuestions'=>$questions,'unrelated'=>'retained'];
                    $out=M77SyntheticBuilderPresentation::bindings($bindings); self::assertSame('retained',$out['unrelated']);
                    self::assertNotSame($questions,$out['practiceQuestions']);
                    foreach($out['practiceQuestions'] as $n=>$copy){
                        self::assertNotSame($questions[$n],$copy); $expected=$questions[$n]->getAttributes();
                        $expected['question']=$audit['targets'][$i]['first_five'][$n]['visible_condition_uk'];
                        self::assertSame($expected,$copy->getAttributes());self::assertSame([],$copy->getDirty());self::assertFalse($copy->exists);
                        foreach(['answers','options','hints','verbHints'] as $relation){self::assertSame($questions[$n]->getRelation($relation),$copy->getRelation($relation));}
                    }
                    self::assertSame($before,$questions->toArray());self::assertSame($blockBefore,$blocks[$i]->getAttributes());
                    $short=$bindings;$short['practiceQuestions']=$questions->take(4);self::assertSame($short,M77SyntheticBuilderPresentation::bindings($short));
                    $course=$bindings;$course['theoryCanonical']=false;self::assertSame($course,M77SyntheticBuilderPresentation::bindings($course));
                }
            } finally {
                app()->setBasePath($root); IsolatedTestEnvironment::assertOwnedPath($file); unlink($file); rmdir($directory);
                M77SyntheticBuilderPackage::$release=[];
            }
        });
    }
}

/** Test-only package facade: real public block metadata, invented question snapshots, no release/source mutation. */
final class M77SyntheticBuilderPackage
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
final class M77SyntheticBuilderPresentation extends TheoryBuilderSelectionPresentation
{
    public static string $auditSha='', $targetsSha='';
    protected static function packageClass():string{return M77SyntheticBuilderPackage::class;}
    protected static function policy():array
    {
        return ['label'=>'M77 synthetic test','package'=>'m77-one-prepositions-phrasal-bank-audit','version'=>'1.0.0',
            'scope'=>'30 selected / 30 reused / 0 new; no question/answer/option/grader/progress writes',
            'before_sha256'=>Presentation::BEFORE_SHA,'audit'=>'audit.json','audit_sha256'=>self::$auditSha,'targets_sha256'=>self::$targetsSha];
    }
}
