<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage as Digest;
use App\Support\M51QuestionSupplement;
use App\Support\M88BuilderSelectionPresentation as Presentation;
use App\Support\M88PastContinuousPresentPerfectBasics as Package;
use App\Support\TheoryBuilderSelectionPresentation;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Public opaque contracts and invented unsaved models, never private SQL reconstruction. */
class M88BuilderSelectionPresentationTest extends TestCase
{
    // Independent fresh M88 approvals; final D order is native UUID order, not a CEFR filter.
    private const SELECTED = [
        [21387,21388,21391,21394,21397],
        [21459,21460,21461,21462,21466],
        [21531,21532,21534,21537,21538],
        [21610,21609,21611,21606,21603],
        [21966,21967,21968,21970,21971],
        [22035,22038,22041,22042,22043],
    ];
    private const SETTLED = [
        ['My brother was doing homework at nine yesterday.','The children were playing in the yard at lunchtime yesterday.','Anna was writing a message at lunchtime yesterday.','The teacher was making plans all afternoon.','My friends were driving to school from two to three.'],
        ['My brother wasn\'t doing homework at nine yesterday.','The children weren\'t playing in the yard at lunchtime yesterday.','The teacher was not explaining the rule at that moment.','My friends were not driving to school at that moment.','The children weren\'t playing in the yard because they finished earlier.'],
        ['Was my brother doing homework at nine yesterday?','Were the children playing in the yard at lunchtime yesterday?','Were my friends driving to school at that moment?','Where was the teacher going at that moment?','Where were the children going from two to three?'],
        ['The children were playing in the yard all morning.','The teacher was explaining the rule all afternoon.','My friends were driving to school from two to three.','At that moment the children were playing in the yard.','My brother was doing homework at nine yesterday.'],
        ['My brother has worked from home for six months.','We have never seen snow before.','Emma still cannot find her glasses because she has lost them.','The children have just found the missing puzzle piece.','I have watched four documentaries this month and the month is not over.'],
        ['You have not completed the online form yet.','My parents have never travelled abroad before.','We haven\'t booked a table for tonight yet.','Ella hasn\'t watched the new episode yet.','We have not taken this route before.'],
    ];
    private const SELECTED_LEVELS = [
        ['A2','A2','A2','A2','A2'],
        ['A2','A2','A2','A2','A2'],
        ['A2','A2','A2','A2','A2'],
        ['A2','A2','A2','A2','A2'],
        ['A2','A2','A2','A2','A2'],
        ['A2','A2','A2','A2','A2'],
    ];
    private string $servedRoot;

    protected function setUp(): void
    {
        parent::setUp(); $this->servedRoot=base_path();
    }

    private function audit(): array
    {
        $a = Presentation::loadAudit(dirname(__DIR__, 2));
        self::assertSame(Presentation::AUDIT_SHA, hash_file('sha256', $this->servedRoot.'/'.Presentation::AUDIT));
        self::assertSame('frozen', $a['status']); self::assertSame([], $a['pending']);
        self::assertSame(Presentation::BEFORE_SHA, $a['independent_before_sha256']);
        return $a;
    }

    private function block(int $i): TextBlock
    {
        [, $p] = Package::load(dirname(__DIR__, 2)); $a = $this->audit()['practice_bindings'][$i];
        $source = $p['targets'][$i]['after']['page']['blocks'][$a['source_index']];
        $b = new TextBlock; $b->setRawAttributes(['id' => 8490000 + $i, 'uuid' => $a['uuid'], 'page_id' => $a['page_id'],
            'page_category_id' => $a['category_id'], 'seeder' => $a['identity'], 'locale' => 'uk', 'type' => $a['type'], 'level' => $a['level'],
            'column' => $a['column'], 'heading' => $a['heading'], 'css_class' => $a['css_class'], 'sort_order' => $a['source_index'] + 1, 'body' => $source['body']], true);
        return $b;
    }

    private function noQueries(callable $call): void
    {
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection()); self::assertSame('testing', app()->environment());
        self::assertSame(':memory:', DB::connection()->getDatabaseName()); self::assertSame('array', config('cache.default'));
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        try { $call(); self::assertSame([], DB::connection()->getQueryLog()); }
        finally { DB::connection()->disableQueryLog(); app()->setLocale('uk'); }
    }

    private function refused(callable $call, string $case): void
    {
        $error = null; try { $call(); } catch (RuntimeException $e) { $error = $e; }
        self::assertInstanceOf(RuntimeException::class, $error, $case);
    }

    public function test_all_30_existing_selections_partition_six_exact_72_question_unfiltered_pools(): void
    {
        $a = $this->audit(); [, $p,, $c] = Package::load(dirname(__DIR__, 2)); Presentation::validateAudit($a, $p, $c);
        self::assertSame([null,null,null,null,null,null], $a['review_summary']['technical_filter']);
        self::assertSame([432,432,30,30,0], array_map(static fn ($k) => $a['review_summary'][$k],
            ['pagewide_original_type4_pool','actual_filtered_pool','selected','reused','new']));
        self::assertSame(Package::FOCUS_LEVELS, $a['review_summary']['intended_selected_levels']);
        self::assertSame(402, $a['review_summary']['excluded']);
        self::assertSame(self::SELECTED, array_column($a['targets'], 'expected_selected_ids'));
        self::assertSame(self::SETTLED, array_map(static fn ($t) => array_column($t['first_five'], 'actual_correct_visible'), $a['targets']));
        $new = $total = 0;
        foreach ($a['targets'] as $i => $t) {
            self::assertSame(Package::OWNERS[$i], $t['identity']); self::assertNull($t['level']);
            self::assertSame(Package::PAGE_IDS[$i], $t['page_id']); self::assertSame(Package::CATEGORY_IDS[$i], $t['category_id']);
            self::assertSame($c['targets'][$i]['bank_seeders'], $t['bank_seeders']);
            self::assertSame([Package::BANKS[$i]], $t['bank_seeders']); self::assertSame(4, $t['type']);
            self::assertSame([72], array_values(array_map('count', $t['expected_pool_ids_by_bank'])));
            $pool = array_merge(...array_values($t['expected_pool_ids_by_bank'])); sort($pool); $total += count($pool);
            $partition = [...$t['expected_selected_ids'], ...$t['excluded_question_ids']]; sort($partition);
            self::assertSame($pool, $partition); self::assertCount(count($pool), array_unique($partition));
            self::assertSame($pool, array_column($t['all_pool_snapshots'], 'id'));
            self::assertCount(67, $t['excluded_question_ids']);
            $levels = array_values(array_unique(array_column($t['all_pool_snapshots'], 'level'))); sort($levels);
            self::assertSame(['A1','A2','B1','B2','C1','C2'], $levels);
            self::assertSame(self::SELECTED_LEVELS[$i], $t['selected_levels']);
            foreach ($t['first_five'] as $n => $sample) {
                $new += (int) $sample['is_new'];
                self::assertSame(self::SELECTED[$i][$n], $sample['id']); self::assertSame($t['expected_selected_uuids'][$n], $sample['uuid']);
                self::assertSame(M51QuestionSupplement::ROW_COLUMNS['questions'], $sample['question_attribute_keys']);
                self::assertSame([], $sample['token_presentations']); self::assertSame([], $sample['option_presentations']);
                self::assertSame(ComposeTokenCase::normalize($sample['raw_correct_tokens']), $sample['correct_tokens']);
                self::assertSame($sample['actual_correct_visible'], $sample['actual_selected_visible']);
                self::assertDoesNotMatchRegularExpression('/<[^>]+>|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu', $sample['visible_condition_uk']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $sample['translation_uk']);
                $bank = array_count_values($sample['token_bank_with_multiplicity']);
                foreach (array_count_values($sample['correct_tokens']) as $token => $count) { self::assertGreaterThanOrEqual($count, $bank[$token] ?? 0); }
            }
        }
        self::assertSame([432,0], [$total,$new]);
        $forbidden = [];
        $walk = static function ($node) use (&$walk, &$forbidden): void {
            if (!is_array($node)) { return; }
            foreach ($node as $key => $value) {
                if (is_string($key) && preg_match('/^(created_at|updated_at|password|secret|APP_KEY|served_root|source_worktree|sql_before|sql_after_http)$/iD', $key)) { $forbidden[] = $key; }
                $walk($value);
            }
        };
        $walk($a); self::assertSame([], $forbidden, 'Only teaching identities and opaque snapshots are public.');
    }

    public function test_changed_policy_pool_conditions_and_actual_null_bindings_fail_closed(): void
    {
        $a = $this->audit(); [, $p,, $c] = Package::load(dirname(__DIR__, 2));
        foreach ([
            'draft' => static function (&$x): void { $x['status'] = 'draft'; },
            'owner' => static function (&$x): void { $x['targets'][4]['identity'] = 'Foreign\\Owner'; },
            'condition' => static function (&$x): void { $x['targets'][2]['first_five'][0]['visible_condition_uk'] = 'Інша умова.'; },
            'target-type' => static function (&$x): void { $x['targets'][0]['type'] = 1; },
            'exact-bank' => static function (&$x): void { $x['targets'][2]['bank_seeders'] = ['Database\\Seeders\\V3\\Polyglot\\PolyglotPresentSimpleVerbsLessonSeeder']; },
            'pool' => static function (&$x): void { array_pop($x['targets'][3]['expected_pool_ids_by_bank'][$x['targets'][3]['bank_seeders'][0]]); },
            'binding-uuid' => static function (&$x): void { $x['practice_bindings'][1]['uuid'] = 'foreign'; },
            'binding-heading' => static function (&$x): void { $x['practice_bindings'][1]['heading'] = 'Непогоджена назва'; },
            'binding-level' => static function (&$x): void { $x['practice_bindings'][5]['level'] = 'A1'; },
            'binding-body' => static function (&$x): void { $x['practice_bindings'][0]['body_sha256'] = str_repeat('0', 64); },
        ] as $case => $change) { $bad = $a; $change($bad); $this->refused(fn () => Presentation::validateAudit($bad, $p, $c), $case); }
        foreach (['A1','A2–B1'] as $wrong) { $bad = $c; $bad['targets'][2]['level'] = $wrong; $this->refused(fn () => Presentation::validateAudit($a, $p, $bad), 'NULL is not focus or display'); }
        $bad = $c; $bad['targets'][5]['bank_seeders'] = ['Database\\Seeders\\V3\\Polyglot\\PolyglotPresentContinuousLessonSeeder'];
        $this->refused(fn () => Presentation::validateAudit($a, $p, $bad), 'Wrong course bank');
    }

    public function test_unchanged_widget_formatter_and_grader_replay_all_30_complete_visible_sentences(): void
    {
        $a=$this->audit(); $profiles=[]; $component='resources/views/components/text-block-practice-questions.blade.php';
        $paths=[base_path($component),dirname(__DIR__,2).'/'.$component];
        $served=getenv('GRAMLYZE_M88_SERVED_ROOT'); if (is_string($served)&&$served!=='') { $paths[]=$served.'/'.$component; }
        foreach (array_unique($paths) as $path) {
            self::assertFileExists($path); $raw=file_get_contents($path); $text=str_replace("\r\n","\n",$raw);
            $take=static function (string $start,string $end) use ($text): string {
                $at=strpos($text,$start); $to=strpos($text,$end,$at===false?0:$at);
                self::assertIsInt($at); self::assertIsInt($to); self::assertGreaterThan($at,$to); return substr($text,$at,$to-$at);
            };
            $methods=$take('                composeCorrectText() {','                getDisplayText() {').$take('                checkAnswer() {','                hasMoreQuestions() {');
            $tuple=[hash('sha256',$raw),hash('sha256',$text),hash('sha256',$methods)];
            $matched=array_values(array_filter($a['native_runtime_probe']['profiles'],static fn ($p)=>$tuple===[$p['native_raw_sha256'],$p['native_lf_sha256'],$p['method_source_sha256']]));
            self::assertCount(1,$matched,'One coherent ROOT or WT native source tuple, never blended hash pieces.');
            self::assertSame(array_merge(...self::SELECTED),array_column($matched[0]['selected'],'id'));
            $profiles[]=['path'=>$path,'raw_sha'=>$tuple[0],'methods_sha'=>$tuple[2]];
        }
        $samples=[]; foreach ($a['targets'] as $i=>$target) { foreach ($target['first_five'] as $n=>$q) {
            $samples[]=['condition'=>$q['visible_condition_uk'],'correct'=>$q['correct_tokens'],'settled'=>self::SETTLED[$i][$n]];
        } }
        $js=<<<'JS'
const fs=require('node:fs'),vm=require('node:vm'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const input=JSON.parse(fs.readFileSync(0,'utf8'));let checked=0;
for(const p of input.profiles){
 const bytes=fs.readFileSync(p.path),sha=x=>crypto.createHash('sha256').update(x).digest('hex');assert.equal(sha(bytes),p.raw_sha);
 const text=bytes.toString('utf8').replace(/\r\n/g,'\n');
 function take(a,b){const i=text.indexOf(a),j=text.indexOf(b,i);assert.ok(i>=0&&j>i);return text.slice(i,j);}
 const source=take('                composeCorrectText() {','                getDisplayText() {')+take('                checkAnswer() {','                hasMoreQuestions() {');
 assert.equal(sha(Buffer.from(source)),p.methods_sha);const methods=vm.runInNewContext('({'+source+'})',{window:{}},{timeout:1000});
 for(const q of input.samples){
  function run(tokens){const m={...methods,currentQuestion:{question:q.condition,correct_tokens:q.correct,type:'4',authored_compose:false},selectedTokens:tokens.map((value,id)=>({id,value})),
   answered:false,isCorrect:false,correctCount:0,currentIndex:0,answeredIndices:[],isComposeQuestion(){return true;},persistState(){}};
   m.correctAnswer=m.composeCorrectText();assert.equal(m.correctAnswer,q.settled);m.checkAnswer();const count=m.correctCount;m.checkAnswer();assert.equal(m.correctCount,count);
   return{correct:m.isCorrect,visible:m.composeSelectedText()};}
  const yes=run(q.correct);assert.equal(yes.correct,true);assert.equal(yes.visible,q.settled);assert.equal(run([]).correct,false);
  const wrong=[...q.correct];[wrong[0],wrong[1]]=[wrong[1],wrong[0]];assert.equal(run(wrong).correct,false);checked++;
 }
 assert.equal(sha(fs.readFileSync(p.path)),p.raw_sha);
}
process.stdout.write(JSON.stringify({checked,network:0,db:0,writes:0}));
JS;
        $p=new \Symfony\Component\Process\Process([getenv('NODE_BINARY')?:'node','-e',$js],dirname(__DIR__,2),timeout:30);
        $p->setInput(json_encode(['profiles'=>$profiles,'samples'=>$samples],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)); $p->run();
        self::assertSame(0,$p->getExitCode(),$p->getOutput().$p->getErrorOutput());
        self::assertSame(['checked'=>30*count($profiles),'network'=>0,'db'=>0,'writes'=>0],json_decode($p->getOutput(),true,flags:JSON_THROW_ON_ERROR));
    }

    public function test_only_exact_canonical_clone_provenance_can_add_exclusions_without_mutation(): void
    {
        $this->noQueries(function (): void {
            app()->setLocale('uk');
            foreach ($this->audit()['targets'] as $i => $t) {
                $b = $this->block($i); $before = $b->getAttributes(); $config = ['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>$t['bank_seeders']];
                self::assertSame([999], Presentation::exclusions($b, $config, [999]));
                $copy = Presentation::matchingBlock($b, true); self::assertNotSame($b, $copy);
                self::assertSame([999,...$t['excluded_question_ids']], Presentation::exclusions($copy, $config, [999]));
                self::assertSame([999], Presentation::exclusions(clone $copy, $config, [999]), 'An unregistered clone is not a canonical caller.');
                self::assertSame([$t['expected_selected_ids'][0]], Presentation::exclusions($copy, $config, [$t['expected_selected_ids'][0]]));
                $foreign = $config; $foreign['seeder_classes'] = ['Foreign\\Bank']; self::assertSame([999], Presentation::exclusions($copy, $foreign, [999]));
                foreach (['seeder'=>'Foreign\\Owner','locale'=>'en','page_id'=>999,'page_category_id'=>999,'uuid'=>'foreign','type'=>'box',
                    'column'=>'right','heading'=>'Foreign title','css_class'=>'foreign','sort_order'=>99,'body'=>$b->body."\n",'level'=>'A1'] as $field => $value) {
                    $bad = clone $b; $bad->setRawAttributes(array_replace($before, [$field=>$value]), true);
                    self::assertSame($bad, Presentation::matchingBlock($bad, true), $field);
                }
                self::assertSame($b, Presentation::matchingBlock($b, false), 'Courses and standalone callers do not opt in.');
                foreach (['en','pl','unknown'] as $locale) { app()->setLocale($locale); self::assertSame($b, Presentation::matchingBlock($b, true)); } app()->setLocale('uk');
                self::assertSame($before, $b->getAttributes()); self::assertSame([], $b->getDirty()); self::assertFalse($b->exists);
            }
        });
    }

    /** Every attribute/relation/timestamp is invented; only public teaching IDs/order are reused. */
    private function synthetic(int $index): array
    {
        $target = $this->audit()['targets'][$index]; $questions = [];
        foreach ($target['first_five'] as $n => &$sample) {
            $attrs = ['id'=>$sample['id'],'uuid'=>$sample['uuid'],'question'=>'Синтетична незбережена умова '.$n.'.','difficulty'=>1,
                'level'=>self::SELECTED_LEVELS[$index][$n],'category_id'=>84000,'source_id'=>84001,'flag'=>0,'type'=>'4','options_by_marker'=>null,
                'theory_text_block_uuid'=>null,'seeder'=>'SyntheticM88Only','created_at'=>'2000-01-01 00:00:00','updated_at'=>'2000-01-01 00:00:00'];
            $q = new Question; $q->setRawAttributes($attrs, true); $options = $answers = $or = $ar = $pr = [];
            foreach (['These','children','are','not','hungry'] as $i => $token) {
                $oid = 841000 + $index*1000 + $n*10 + $i; $aid = 842000 + $index*1000 + $n*10 + $i;
                $o = new QuestionOption; $o->setRawAttributes(['id'=>$oid,'option'=>$token], true);
                $pivot = ['question_id'=>$q->id,'option_id'=>$oid,'flag'=>null]; $pv = new Pivot; $pv->setRawAttributes($pivot, true); $o->setRelation('pivot', $pv);
                $answer = new QuestionAnswer; $answer->setRawAttributes(['id'=>$aid,'question_id'=>$q->id,'option_id'=>$oid,'marker'=>'a'.($i+1),'position'=>null], true);
                $answer->setRelation('option', $o); $options[]=$o; $answers[]=$answer;
                $or[]=['id'=>$oid,'option'=>$token]; $ar[]=['id'=>$aid,'marker'=>'a'.($i+1),'position'=>null,'option_id'=>$oid,'option'=>$token]; $pr[]=$pivot;
            }
            $q->setRelation('answers', new Collection($answers)); $q->setRelation('options', new Collection($options));
            $q->setRelation('hints', new Collection); $q->setRelation('verbHints', new Collection); $questions[]=$q;
            $sample['snapshot_hashes'] = array_map(Digest::digest(...), ['question_snapshot'=>$attrs,'ordered_answer_snapshot'=>$ar,
                'original_options_snapshot'=>$or,'loaded_option_pivots'=>$pr,'hints_snapshot'=>[],'verb_hints_snapshot'=>[]]);
        }
        unset($sample); return [new Collection($questions), $target];
    }

    public function test_shared_snapshot_guard_rejects_stale_relations_and_unknown_attributes_for_all_six(): void
    {
        $this->noQueries(function (): void {
            $match = new ReflectionMethod(TheoryBuilderSelectionPresentation::class, 'matchesQuestions');
            foreach (Package::OWNERS as $i => $_) {
                [$q,$t] = $this->synthetic($i); self::assertTrue($match->invoke(null, $q, $t));
                foreach ([
                    'timestamp'=>fn($x)=>$x[0]->setAttribute('updated_at','2001-01-01 00:00:00'),
                    'unknown-null'=>fn($x)=>$x[0]->setAttribute('foreign_null',null),
                    'answer-option'=>fn($x)=>$x[1]->answers[0]->option->setAttribute('option','Changed'),
                    'marker'=>fn($x)=>$x[2]->answers[0]->setAttribute('marker','a99'),
                    'pivot'=>fn($x)=>$x[3]->options[0]->pivot->setAttribute('flag',1),
                    'missing-options'=>fn($x)=>$x[4]->unsetRelation('options'),
                    'missing-hints'=>fn($x)=>$x[4]->unsetRelation('hints'),
                ] as $case=>$change) { [$bad,$bt]=$this->synthetic($i); $change($bad); $before=$bad->toArray(); self::assertFalse($match->invoke(null,$bad,$bt),$case); self::assertSame($before,$bad->toArray()); }
                $bindings = ['block'=>$this->block($i),'theoryCanonical'=>true,'practiceQuestions'=>$q,'unrelated'=>'keep'];
                self::assertSame($bindings, Presentation::bindings($bindings), 'Invented models cannot pass real public opaque snapshots.');
                $bindings['theoryCanonical']=false; self::assertSame($bindings, Presentation::bindings($bindings));
            }
        });
    }

    public function test_inherited_condition_cloning_preserves_all_original_attributes_relations_and_callers(): void
    {
        $this->noQueries(function (): void {
            $audit=$this->audit(); $questions=$blocks=[];
            M88SyntheticBuilderPackage::$release=Package::load(dirname(__DIR__,2));
            foreach ($audit['targets'] as $i=>$_) { [$questions[$i],$audit['targets'][$i]]=$this->synthetic($i); $blocks[$i]=$this->block($i); }
            $directory=storage_path('framework/testing/m88-builder-'.bin2hex(random_bytes(8))); self::assertTrue(mkdir($directory,0700)); IsolatedTestEnvironment::assertOwnedPath($directory);
            $file=$directory.'/audit.json'; $bytes=Digest::json($audit); self::assertSame(strlen($bytes),file_put_contents($file,$bytes,LOCK_EX));
            M88SyntheticBuilderPresentation::$auditSha=hash('sha256',$bytes); M88SyntheticBuilderPresentation::$targetsSha=Digest::digest($audit['targets']); $root=base_path();
            try {
                app()->setBasePath($directory); app()->setLocale('uk');
                foreach ($questions as $i=>$rows) {
                    $snapshot=$rows->toArray(); $blockBefore=$blocks[$i]->getAttributes();
                    $bindings=['block'=>$blocks[$i],'theoryCanonical'=>true,'practiceQuestions'=>$rows,'unrelated'=>'keep'];
                    $out=M88SyntheticBuilderPresentation::bindings($bindings); self::assertNotSame($rows,$out['practiceQuestions']); self::assertSame('keep',$out['unrelated']);
                    foreach ($out['practiceQuestions'] as $n=>$copy) {
                        $attrs=$rows[$n]->getAttributes(); $attrs['question']=$audit['targets'][$i]['first_five'][$n]['visible_condition_uk'];
                        self::assertNotSame($rows[$n],$copy); self::assertSame($attrs,$copy->getAttributes()); self::assertSame([],$copy->getDirty());
                        foreach (['answers','options','hints','verbHints'] as $relation) { self::assertSame($rows[$n]->getRelation($relation),$copy->getRelation($relation)); }
                    }
                    self::assertSame($snapshot,$rows->toArray()); self::assertSame($blockBefore,$blocks[$i]->getAttributes());
                    foreach (['short','course','option','hint'] as $case) {
                        [$bad]=$this->synthetic($i); $stale=$bindings; $stale['practiceQuestions']=$bad;
                        if ($case==='short') { $stale['practiceQuestions']=$bad->take(4); }
                        elseif ($case==='course') { $stale['theoryCanonical']=false; }
                        elseif ($case==='option') { $bad[0]->options[0]->setAttribute('option','Changed'); }
                        else { $hint=new QuestionHint; $hint->setRawAttributes(['id'=>1,'question_id'=>$bad[0]->id,'provider'=>'synthetic','locale'=>'uk','hint'=>'Інша підказка.'],true); $bad[0]->setRelation('hints',new Collection([$hint])); }
                        $before=$bad->toArray(); self::assertSame($stale,M88SyntheticBuilderPresentation::bindings($stale),$case); self::assertSame($before,$bad->toArray());
                    }
                }
            } finally { app()->setBasePath($root); IsolatedTestEnvironment::assertOwnedPath($file); unlink($file); rmdir($directory); M88SyntheticBuilderPackage::$release=[]; }
        });
    }

    public function test_exact_moment_negation_questions_ongoing_situation_and_duplicate_instances_remain_reviewed(): void
    {
        $a = $this->audit();
        foreach ($a['targets'] as $i => $target) {
            foreach ($target['first_five'] as $n => $sample) {
                self::assertFalse($sample['is_new']);
                self::assertSame(self::SETTLED[$i][$n], $sample['actual_correct_visible']);
                self::assertStringNotContainsString($sample['actual_correct_visible'], $sample['visible_condition_uk'],
                    'A display criterion must not expose the complete English target sentence.');
            }
        }
        foreach ($a['targets'][2]['first_five'] as $sample) {
            self::assertStringEndsWith('?', $sample['actual_correct_visible']);
            self::assertNotContains('did', $sample['correct_tokens']);
        }
        self::assertSame('Where was the teacher going at that moment?', $a['targets'][2]['first_five'][3]['actual_correct_visible']);
        self::assertSame('At that moment the children were playing in the yard.', $a['targets'][3]['first_five'][3]['actual_correct_visible']);
        self::assertStringContainsString('почни саме з', $a['targets'][3]['first_five'][3]['visible_condition_uk']);
        $e = $a['targets'][4]['first_five'];
        self::assertStringContainsString('досі так працює', $e[0]['visible_condition_uk']);
        self::assertStringContainsString('не в Continuous', $e[0]['visible_condition_uk']);
        self::assertSame('My brother has worked from home for six months.', $e[0]['actual_correct_visible']);
        self::assertContains('she', $e[2]['correct_tokens']);
        self::assertNotContains('She', $e[2]['correct_tokens']);
        self::assertSame(2, count(array_filter($e[3]['correct_tokens'], static fn ($t) => strtolower($t) === 'the')));
        self::assertSame(2, count(array_filter($e[3]['token_bank_with_multiplicity'], static fn ($t) => strtolower($t) === 'the')));
        self::assertSame(9, $e[3]['source_ordered_instance_count']);
        self::assertSame(2, count(array_filter($e[4]['correct_tokens'], static fn ($t) => $t === 'month')));
        self::assertSame(2, count(array_filter($e[4]['token_bank_with_multiplicity'], static fn ($t) => $t === 'month')));
        self::assertSame(13, $e[4]['source_ordered_instance_count']);
        $f = $a['targets'][5]['first_five'];
        self::assertContains('not', $f[0]['correct_tokens']);
        self::assertContains('never', $f[1]['correct_tokens']);
        self::assertContains("haven't", $f[2]['correct_tokens']);
        self::assertContains("hasn't", $f[3]['correct_tokens']);
        self::assertSame('We have not taken this route before.', $f[4]['actual_correct_visible']);
        // Ongoing work is not called a stative verb, and for-long is own meaning practice,
        // not claimed as selected widget coverage or invented as a new bank row.
        self::assertNotContains('They haven\'t lived here for long.', self::SETTLED[5]);
    }

    public function test_complete_snapshot_guard_refuses_foreign_order_types_hints_and_decorations_without_queries(): void
    {
        $this->noQueries(function (): void {
            $match=new ReflectionMethod(TheoryBuilderSelectionPresentation::class,'matchesQuestions');
            foreach (Package::OWNERS as $i=>$_) {
                [$rows,$target]=$this->synthetic($i);
                self::assertTrue($match->invoke(null,$rows,$target));
                foreach ([
                    'reverse'=>static fn($x)=>$x->reverse()->values(),
                    'four'=>static fn($x)=>$x->take(4)->values(),
                    'foreign-id'=>static function($x){$x[0]->setAttribute('id',999999);return $x;},
                    'foreign-uuid'=>static function($x){$x[0]->setAttribute('uuid','foreign');return $x;},
                    'duplicate-id'=>static function($x){$x[1]->setAttribute('id',$x[0]->id);return $x;},
                    'duplicate-model'=>static function($x){$x[1]=clone $x[0];return $x;},
                    'changed-condition'=>static function($x){$x[0]->setAttribute('question','Синтетичний процес в іншому місці.');return $x;},
                    'different-level'=>static function($x){$x[0]->setAttribute('level','B1');return $x;},
                    'wrong-type'=>static function($x){$x[0]->setAttribute('type','1');return $x;},
                    'foreign-seeder'=>static function($x){$x[0]->setAttribute('seeder','Foreign\\Bank');return $x;},
                    'missing-answers'=>static function($x){$x[0]->unsetRelation('answers');return $x;},
                    'missing-verb-hints'=>static function($x){$x[0]->unsetRelation('verbHints');return $x;},
                    'nonempty-verb-hints'=>static function($x){$x[0]->setRelation('verbHints',new Collection([(object)['marker'=>'a1']]));return $x;},
                    'non-null-score'=>static function($x){$x[0]->setRawAttributes($x[0]->getAttributes()+['match_score'=>0,'matched_tag_ids'=>[],'marker_tags'=>[]],true);return $x;},
                    'unknown-decoration'=>static function($x){$x[0]->setAttribute('unreviewed_null',null);return $x;},
                ] as $case=>$change) {
                    [$bad,$bt]=$this->synthetic($i); $bad=$change($bad); $before=$bad->toArray();
                    self::assertFalse($match->invoke(null,$bad,$bt),$case);
                    self::assertSame($before,$bad->toArray(),$case.' must remain unmutated');
                }
                $original=$rows->toArray();
                $copy=$rows->map(static fn($q)=>clone $q);
                $copy[0]->setRawAttributes($copy[0]->getAttributes()+['match_score'=>null,'matched_tag_ids'=>[],'marker_tags'=>[]],true);
                self::assertTrue($match->invoke(null,$copy,$target),'Only the documented exact NULL matcher decoration is permitted.');
                self::assertSame($original,$rows->toArray());
            }
        });
    }

    public function test_only_verbatim_theory_bank_config_and_nonintersecting_exclusions_opt_in(): void
    {
        $this->noQueries(function (): void {
            app()->setLocale('uk');
            foreach ($this->audit()['targets'] as $i=>$target) {
                $b=$this->block($i); $attrs=$b->getAttributes(); $copy=Presentation::matchingBlock($b,true);
                $config=['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>[Package::BANKS[$i]]];
                self::assertSame([777,...$target['excluded_question_ids']],Presentation::exclusions($copy,$config,[777]));
                foreach ([
                    ['source'=>'page_questions','question_types'=>['4'],'seeder_classes'=>[Package::BANKS[$i]]],
                    ['source'=>'theory_links','question_types'=>['1'],'seeder_classes'=>[Package::BANKS[$i]]],
                    ['source'=>'theory_links','question_types'=>['4','1'],'seeder_classes'=>[Package::BANKS[$i]]],
                    ['source'=>'theory_links','question_types'=>[4],'seeder_classes'=>[Package::BANKS[$i]]],
                    ['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>[Package::BANKS[$i],Package::BANKS[$i]]],
                    ['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>['Database\\Seeders\\V3\\Polyglot\\PolyglotPresentSimpleVerbsLessonSeeder']],
                ] as $foreign) {
                    self::assertSame([777],Presentation::exclusions($copy,$foreign,[777]));
                }
                $blocked=[$target['expected_selected_ids'][0],888];
                self::assertSame($blocked,Presentation::exclusions($copy,$config,$blocked));
                self::assertSame($attrs,$b->getAttributes()); self::assertFalse($b->exists); self::assertSame([],$b->getDirty());
            }
        });
    }
}

final class M88SyntheticBuilderPackage
{
    public const OWNERS=Package::OWNERS, PAGE_IDS=Package::PAGE_IDS, CATEGORY_IDS=Package::CATEGORY_IDS,
        LEVELS=Package::LEVELS, ANCESTRIES=Package::ANCESTRIES, PRACTICE_SOURCE_INDICES=Package::PRACTICE_SOURCE_INDICES;
    public static array $release=[];
    public static function load(?string $root=null): array { return self::$release; }
    public static function presentation(object $b,array $data): ?array
    {
        $i=array_search($b->seeder,self::OWNERS,true); if ($i===false) { return null; }
        $source=self::$release[1]['targets'][$i]['after']['page']['blocks'][self::PRACTICE_SOURCE_INDICES[$i]];
        return $data===json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR)?['synthetic_guard_probe'=>true]:null;
    }
}

final class M88SyntheticBuilderPresentation extends TheoryBuilderSelectionPresentation
{
    public static string $auditSha='', $targetsSha='';
    protected static function packageClass(): string { return M88SyntheticBuilderPackage::class; }
    protected static function policy(): array
    {
        return ['label'=>'M88 synthetic','package'=>'m88-past-continuous-present-perfect-basics-bank-audit','version'=>'1.0.0',
            'scope'=>'30 selected existing questions; theory-only condition clones; no question/answer/option/grader/progress writes',
            'before_sha256'=>Presentation::BEFORE_SHA,'audit'=>'audit.json','audit_sha256'=>self::$auditSha,'targets_sha256'=>self::$targetsSha];
    }
}
