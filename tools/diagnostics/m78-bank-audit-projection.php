<?php

/** Pure M78 teaching projection; no application bootstrap, SQL, HTTP, seeding or active-source write. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 4
    || preg_match('/^--author-sha256=([a-f0-9]{64})$/D', $argv[1], $argument) !== 1
    || preg_match('/^author-projection-v[1-9][0-9]*\.json$/D', $argv[2]) !== 1
    || preg_match('/^bank-audit-projection-v[1-9][0-9]*\.json$/D', $argv[3]) !== 1) {
    throw new RuntimeException('M78 requires reviewed author SHA256 and two safe exclusive private basenames.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
$wt = $root.'/storage/app/m75-worktree';
$private = $root.'/storage/app/seo-m78-local';
$normal = static fn (string $path): string => strtolower(str_replace('\\', '/', (string) realpath($path)));
if ($normal(dirname(__DIR__, 2)) !== $normal($wt) || !is_dir($private) || is_link($private) || is_link($wt)) {
    throw new RuntimeException('M78 exact worktree/private paths differ.');
}
$output = $private.'/'.$argv[3];
if (file_exists($output) || is_link($output)) { throw new RuntimeException('M78 exclusive private output exists or is linked.'); }
$beforeSha = '327424de32f1cebaad86adef078d25aa1d4747b257894be3fdadc6706c630422';
$authorSha = $argument[1];
$read = static function (string $path, string $sha): array {
    if (!is_file($path) || is_link($path) || preg_match('/^[a-f0-9]{64}$/D', $sha)!==1
        || !hash_equals($sha, hash_file('sha256', $path))) {
        throw new RuntimeException('M78 exact reviewed input is missing, linked or changed.');
    }
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
};
$before = $read($private.'/before-v1.json', $beforeSha);
$author = $read($private.'/'.$argv[2], $authorSha);
require $root.'/vendor/autoload.php';
require_once $wt.'/app/Support/TheoryNativeContentRelease.php';
require_once $wt.'/app/Support/TheoryBuilderSelectionPresentation.php';
require_once $wt.'/app/Support/M78PhrasalQuestionBasics.php';

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage as Digest;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M78PhrasalQuestionBasics as Package;
use App\Support\TheoryBuilderSelectionPresentation;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

if (($before['base_sha'] ?? null) !== Package::BASE_SHA
    || ($author['source_before']['base_sha'] ?? null) !== Package::BASE_SHA
    || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null)
    || ($author['native_bindings']['base_sha'] ?? null) !== Package::BASE_SHA
    || array_column($author['native_bindings']['bindings'] ?? [], 'identity') !== Package::OWNERS
    || ($author['db_connections'] ?? null)!==0 || ($author['db_writes'] ?? null)!==0
    || ($author['http_requests'] ?? null)!==0) {
    throw new RuntimeException('M78 independent BEFORE/author/package lineage differs.');
}
(new App\Support\TheoryNativeContentRelease(Package::draftProfile($author['native_bindings']['bindings'])))
    ->validate($author['source_before'],$author['patch'],$author['master'],$author['contract']);
Package::assertEditorial($author['source_before'],$author['patch'],$author['master']);
if ($author['native_bindings'] !== $read($private.'/native-bindings-v1.json','63cfcb4fccea1a3a8008d07cfcd323224ef0699b2c58e948d10d92a5ee44300a')) {
    throw new RuntimeException('M78 reviewed author binding differs from the package-bound literal metadata ledger.');
}

/** The six hashes exactly mirror the shared matcher, including null position and the three loaded pivot fields. */
$runtimeParts = static function (array $snapshot): array {
    $question = $snapshot['question']; $ordered = [];
    $keys = array_keys($question); $expected = Rows::ROW_COLUMNS['questions']; sort($keys); sort($expected);
    if ($keys !== $expected) { throw new RuntimeException('M78 original question is not the complete fourteen-column snapshot.'); }
    foreach (Rows::ROW_COLUMNS['questions'] as $field) { $ordered[$field] = $question[$field]; }
    $options = array_map(static fn ($r) => ['id'=>$r['id'], 'option'=>$r['option']], $snapshot['options']);
    usort($options, static fn ($a,$b) => $a['id'] <=> $b['id']);
    $optionMap = array_column($options, 'option', 'id'); $answers = [];
    foreach ($snapshot['answers'] as $answer) {
        if ($answer['question_id'] !== $question['id'] || !isset($optionMap[$answer['option_id']])) {
            throw new RuntimeException('M78 original answer/option ownership differs.');
        }
        $answers[] = ['id'=>$answer['id'], 'marker'=>$answer['marker'], 'position'=>$answer['position'] ?? null,
            'option_id'=>$answer['option_id'], 'option'=>$optionMap[$answer['option_id']]];
    }
    usort($answers, static fn ($a,$b) => strnatcasecmp($a['marker'], $b['marker']));
    if ($answers !== $snapshot['ordered_answer_instances']) { throw new RuntimeException('M78 ordered answer instances do not match complete original families.'); }
    $pivots = array_map(static fn ($r) => ['question_id'=>$r['question_id'], 'option_id'=>$r['option_id'], 'flag'=>$r['flag']], $snapshot['option_pivots']);
    usort($pivots, static fn ($a,$b) => $a['option_id'] <=> $b['option_id']);
    if (array_column($pivots,'option_id') !== array_column($options,'id')
        || array_filter($pivots, static fn ($p) => $p['question_id'] !== $question['id'] || !in_array($p['flag'],[null,0],true))) {
        throw new RuntimeException('M78 captured options differ from the unchanged native loaded pivot set.');
    }
    $hints = array_map(static fn ($r) => ['id'=>$r['id'], 'question_id'=>$r['question_id'], 'provider'=>$r['provider'],
        'locale'=>$r['locale'], 'hint'=>$r['hint']], $snapshot['hints']);
    if ($snapshot['verb_hints'] !== []) { throw new RuntimeException('M78 type-four pool unexpectedly contains verb hints.'); }
    return ['question'=>$ordered, 'answers'=>$answers, 'options'=>$options, 'pivots'=>$pivots, 'hints'=>$hints];
};
$snapshotHashes = static function (array $snapshot) use ($runtimeParts): array {
    $r = $runtimeParts($snapshot);
    return ['question_snapshot'=>Digest::digest($r['question']), 'ordered_answer_snapshot'=>Digest::digest($r['answers']),
        'original_options_snapshot'=>Digest::digest($r['options']), 'loaded_option_pivots'=>Digest::digest($r['pivots']),
        'hints_snapshot'=>Digest::digest($r['hints']), 'verb_hints_snapshot'=>Digest::digest([])];
};
$publicHashes = static function (array $snapshot,array $links) use ($snapshotHashes): array {
    return ['full_private_snapshot_sha256'=>Digest::digest($snapshot), 'snapshot_hashes'=>$snapshotHashes($snapshot),
        'variants_sha256'=>Digest::digest($snapshot['variants']), 'links_sha256'=>Digest::digest($links),
        'all_original_option_attributes_sha256'=>Digest::digest($snapshot['options']),
        'all_original_answer_attributes_sha256'=>Digest::digest($snapshot['answers']),
        'all_original_option_pivot_attributes_sha256'=>Digest::digest($snapshot['option_pivots']),
        'all_original_tag_relations_sha256'=>Digest::digest(['tags'=>$snapshot['tags'],'marker_tags'=>$snapshot['marker_tags']])];
};


/** One finite educational selection, independent of current SQL/AFTER output. */
$reviewPins=['docs/content/m78-widget-selection.v1.json'=>'4b009bc23f878b27d6feee2ca6a8c919f892fe21c0f93f1842afdb6f32512e30'];
$review=$read($wt.'/'.array_key_first($reviewPins),array_values($reviewPins)[0]);
if (($review['independent_before_sha256']??null)!==$beforeSha
    ||array_column($review['targets']??[],'identity')!==Package::OWNERS) {
    throw new RuntimeException('M78 exact six-owner selection provenance differs.');
}
$selections=[];$reviewExclusions=[];
foreach ($review['targets'] as $target) { $selections[$target['identity']]=$target['selected']; }
foreach ($review['scope_exclusions'] as $item) { $reviewExclusions[$item['identity']][$item['id']]=$item['reason']; }
if (array_map('count',array_values($selections))!==[5,5,5,5,5,5]) { throw new RuntimeException('M78 requires six independent fives.'); }
$audit = [
    'schema_version'=>1,'package'=>'m78-phrasal-question-basics-bank-audit','version'=>'1.0.0','status'=>'draft','locale'=>'uk',
    'independent_before_sha256'=>$beforeSha,
    'scope'=>'30 selected / 30 reused / 0 new; no question/answer/option/grader/progress writes',
    'pending'=>['Independent main review/freeze, exact local widget acceptance and all-old-row preservation proof.'],
    'privacy_policy'=>'Teaching IDs/conditions/token-option text plus opaque snapshot hashes only; no raw SQL row families, timestamps, secrets, runtime identity or progress.',
    'source_proof'=>['author_projection_sha256'=>$authorSha,'independent_before_sha256'=>$beforeSha,'author_review_sha256'=>$reviewPins],
    'review_summary'=>[
        'actual_filtered_pool'=>132,'pagewide_original_type4_pool'=>432,'selected'=>30,'reused'=>30,'new'=>0,
        'technical_filter'=>Package::LEVELS,'intended_selected_levels'=>Package::FOCUS_LEVELS,
        'null_policy'=>'Only Question Forms has actual SQL NULL and therefore72 eligible originals. A/B new B1 practices and D/E/F continuingA1 practices each have12 eligible originals; display ranges never select questions.',
        'boundary'=>'Only thirty selected functions are accepted here. Every eligible row is snapshot-bound, but other rows are scope exclusions, not universally invalid.',
        'human_review'=>false,
    ],
    'clone_only_policy'=>'The complete fourteen question attributes and loaded answer/option/pivot/hint snapshots must match. Only the Ukrainian condition may change on unsaved canonical-theory clones. No answer/token/option/formatter/casing/grader change.',
    'formatter_review'=>[
        'native_case_source_sha256'=>hash_file('sha256',$root.'/app/Support/ComposeTokenCase.php'),
        'native_blade_lf_sha256'=>hash('sha256',str_replace("\r\n","\n",file_get_contents($root.'/resources/views/components/text-block-practice-questions.blade.php'))),
        'settled_string_policy'=>'Actual unchanged native methods replayed on ROOT and WT below. Original punctuation and repeated token instances retained; no strict case/punctuation grading claim.',
        'browser_acceptance'=>'not-claimed-by-offline-projection',
    ],
    'targets'=>[],'practice_bindings'=>[],
];
$privateSelected=[];
foreach (Package::OWNERS as $i=>$owner) {
    $page=$before['pages'][$owner]??null;
    $declaration=$author['contract']['targets'][$i]??null;
    $index=Package::PRACTICE_SOURCE_INDICES[$i];
    $block=$author['patch']['targets'][$i]['after']['page']['blocks'][$index]??null;
    $level=Package::LEVELS[$i];
    $data=is_array($block)?json_decode($block['body'],true,flags:JSON_THROW_ON_ERROR):null;
    $banks=$declaration['bank_seeders']??null;
    if (!is_array($page)||!is_array($block)||!is_array($banks)||count($banks)!==1
        || $page['page']['id']!==Package::PAGE_IDS[$i]||$declaration['identity']!==$owner
        ||$declaration['level']!==$level||($block['level']??null)!==$level
        ||($data['linked_practice']['seeder_classes']??null)!==$banks
        ||($data['linked_practice']['source']??null)!=='theory_links'
        ||($data['linked_practice']['question_types']??null)!==['4']
        ||array_column($page['category_ancestry'],'slug')!==Package::ANCESTRIES[$i]) {
        throw new RuntimeException('M78 exact page/ancestry/practice/thematic bank/level binding differs.');
    }
    $snapshots=$page['relations']['question_snapshots']; $poolRows=[]; $poolByBank=[];
    foreach ($banks as $bank) {
        $pagewide=array_values(array_filter($page['relations']['questions'],static fn($q)=>$q['seeder']===$bank&&(string)$q['type']==='4'));
        if (count($pagewide)!==72) { throw new RuntimeException('M78 original pagewide bank must contain exactly72 existing type4 rows.'); }
        $questions=array_values(array_filter($pagewide,static fn($q)=>$level===null||$q['level']===$level));
        if (count($questions)!==($i===2?72:12)) { throw new RuntimeException('M78 exact eligible matcher pool differs.'); }
        $poolByBank[$bank]=array_column($questions,'id');sort($poolByBank[$bank]);
        foreach ($questions as $q) {
            if (isset($poolRows[$q['id']])) { throw new RuntimeException('M78 eligible bank contains a duplicate question.'); }
            $s=$snapshots[$q['uuid']]??null;
            if (!is_array($s)||$s['question']!==$q) { throw new RuntimeException('M78 full independent eligible snapshot missing.'); }
            $poolRows[$q['id']]=$s;
        }
    }
    $selected=$selections[$owner];$orderedSelected=array_column($selected,'id');
    foreach ($selected as $sample) {
        $s=$poolRows[$sample['id']]??null;
        if (!is_array($s)||$s['question']['uuid']!==$sample['uuid']||$s['question']['level']!==$sample['level']
            ||$sample['level']!==Package::FOCUS_LEVELS[$i]||$s['question']['question']!==$sample['source']) {
            throw new RuntimeException('M78 reviewed selected question is not its exact eligible original.');
        }
    }
    $nativeOrder=$orderedSelected;
    usort($nativeOrder,static fn($a,$b)=>strcmp($poolRows[$a]['question']['uuid'],$poolRows[$b]['question']['uuid']));
    if ($nativeOrder!==$orderedSelected||count(array_unique($orderedSelected))!==5) {
        throw new RuntimeException('M78 native UUID order/five unique selected IDs differ.');
    }
    $excluded=array_values(array_diff(array_merge(...array_values($poolByBank)),$orderedSelected));sort($excluded);
    $linksFor=static fn($uuid)=>array_values(array_filter($page['relations']['question_theory_text_blocks'],static fn($r)=>$r['question_uuid']===$uuid));
    $ukUuids=array_column(array_filter($page['blocks'],static fn($r)=>$r['locale']==='uk'&&$r['seeder']===$owner),'uuid');
    $target=[
        'identity'=>$owner,'page_id'=>Package::PAGE_IDS[$i],'category_id'=>Package::CATEGORY_IDS[$i],
        'category_ancestry'=>array_map(static fn($slug)=>['slug'=>$slug],Package::ANCESTRIES[$i]),
        'local_url'=>$declaration['local_url'],'locale'=>'uk','level'=>$level,'intended_selected_level'=>Package::FOCUS_LEVELS[$i],
        'type'=>4,'bank_seeders'=>$banks,'expected_pool_ids_by_bank'=>$poolByBank,
        'expected_selected_ids'=>$orderedSelected,'expected_selected_uuids'=>array_map(static fn($id)=>$poolRows[$id]['question']['uuid'],$orderedSelected),
        'excluded_question_ids'=>$excluded,'all_pool_snapshots'=>[],'all_eligible_review'=>[],
        'scope_exclusions'=>['ids'=>$excluded,'reason'=>'Finite five-question scope; original rows/keys/case retained.'],
        'known_scope_exclusions'=>[],'first_five'=>[],
    ];
    ksort($poolRows);
    foreach ($poolRows as $id=>$snapshot) {
        $q=$snapshot['question'];$hashes=$publicHashes($snapshot,$linksFor($q['uuid']));
        $target['all_pool_snapshots'][]=['id'=>$id,'uuid'=>$q['uuid'],'level'=>$q['level'],...$hashes];
        if (in_array($id,$excluded,true)) {
            $reason=$reviewExclusions[$owner][$id]??'Outside the finite five; not a claim that this original is invalid or independently linguistically accepted.';
            $target['all_eligible_review'][]=['id'=>$id,'uuid'=>$q['uuid'],'decision'=>'scope_exclusion','reason'=>$reason];
            if (isset($reviewExclusions[$owner][$id])) { $target['known_scope_exclusions'][]=['id'=>$id,'uuid'=>$q['uuid'],'reason'=>$reason]; }
        }
    }
    foreach ($selected as $sample) {
        $id=$sample['id'];$snapshot=$poolRows[$id];$q=$snapshot['question'];$r=$runtimeParts($snapshot);
        $condition=$sample['condition'];$translation=$sample['translation'];$settled=$sample['settled'];
        if (trim($condition)===''||trim($translation)===''||trim($settled)===''
            ||preg_match('/[\x{0400}-\x{04FF}]/u',$condition)!==1
            ||preg_match('/<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|[a-z]+);/iu',$condition)) {
            throw new RuntimeException('M78 selected plain Ukrainian clone condition is incomplete or technical.');
        }
        $rawTokens=array_column($r['answers'],'option');$tokens=ComposeTokenCase::normalize($rawTokens);
        if ($rawTokens!==$sample['raw_tokens']||$tokens!==$sample['tokens']||ComposeTokenCase::normalize($tokens)!==$tokens
            ||array_intersect(array_column($linksFor($q['uuid']),'text_block_uuid'),$ukUuids)===[]) {
            throw new RuntimeException('M78 actual original token case/instances/uk owner link differs from review.');
        }
        $target['first_five'][]=[
            'id'=>$id,'uuid'=>$q['uuid'],'is_new'=>false,'question_attribute_keys'=>Rows::ROW_COLUMNS['questions'],
            'displayed_condition_uk'=>$q['question'],'visible_condition_uk'=>$condition,'translation_uk'=>$translation,
            'correct_tokens'=>$tokens,'raw_correct_tokens'=>$rawTokens,'expected_word_key'=>implode(' ',$tokens),
            'actual_correct_visible'=>$settled,'actual_selected_visible'=>$settled,'source_ordered_instance_count'=>count($r['answers']),
            'original_option_texts'=>array_column($r['options'],'option'),
            'token_bank_with_multiplicity'=>ComposeTokenCase::mergeOptions(array_column($r['options'],'option'),$rawTokens),
            'token_presentations'=>[],'option_presentations'=>[],...$publicHashes($snapshot,$linksFor($q['uuid'])),
        ];
        $target['all_eligible_review'][]=[
            'id'=>$id,'uuid'=>$q['uuid'],'decision'=>'selected','function'=>$sample['coverage'],
            'key_policy'=>'Exact unchanged original sequence; alternatives outside this visibly focused finite task are not declared universally ungrammatical.',
        ];
        $privateSelected[$i][]=$snapshot;
    }
    $audit['targets'][]=$target;
    $audit['practice_bindings'][]=[
        'identity'=>$owner,'page_id'=>Package::PAGE_IDS[$i],'category_id'=>Package::CATEGORY_IDS[$i],'locale'=>'uk','source_index'=>$index,
        'uuid'=>Digest::uuid($owner,$block,$index+1),'type'=>$block['type'],'column'=>$block['column'],'heading'=>$block['heading']??null,
        'level'=>$level,'css_class'=>$block['css_class']??null,'body_sha256'=>hash('sha256',$block['body']),'linked_practice'=>$data['linked_practice'],
    ];
}
if (array_map(static fn($t)=>array_sum(array_map('count',$t['expected_pool_ids_by_bank'])),$audit['targets'])!==[12,12,72,12,12,12]) {
    throw new RuntimeException('M78 six exact eligible bank populations differ.');
}

/** Replay actual unchanged native methods; no old M76 trap is part of this new package. */
$bladePath=$root.'/resources/views/components/text-block-practice-questions.blade.php';
$nativeBeforeSha=$before['visual_hashes']['resources/views/components/text-block-practice-questions.blade.php']??'';
if (!is_string($nativeBeforeSha)||!hash_equals($nativeBeforeSha,hash_file('sha256',$bladePath))) {
    throw new RuntimeException('M78 served native component changed from independent BEFORE.');
}
$selectedProbe=[];
foreach ($audit['targets'] as $target) { foreach ($target['first_five'] as $sample) {
    $selectedProbe[]=['id'=>$sample['id'],'condition'=>$sample['visible_condition_uk'],'correct'=>$sample['correct_tokens'],
        'bank'=>$sample['token_bank_with_multiplicity'],'settled'=>$sample['actual_correct_visible']];
} }
$nodeSource = <<<'JS'
const fs=require('node:fs'),vm=require('node:vm'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const payload=JSON.parse(fs.readFileSync(0,'utf8'));
function runProfile(input){
const bytes=fs.readFileSync(input.blade),hash=b=>crypto.createHash('sha256').update(b).digest('hex');
assert.equal(hash(bytes),input.raw_sha256,'Served native source must equal independent BEFORE bytes.');
const text=bytes.toString('utf8').replace(/\r\n/g,'\n');
function take(start,end){const i=text.indexOf(start),j=text.indexOf(end,i);assert.ok(i>=0&&j>i);assert.equal(text.indexOf(start,i+start.length),-1);return text.slice(i,j);}
const source=take('                composeCorrectText() {','                getDisplayText() {')
    +take('                checkAnswer() {','                hasMoreQuestions() {');
const methods=vm.runInNewContext('({'+source+'})',{window:{}},{timeout:1000});
const lexical=s=>String(s).toLowerCase().replace(/[\u2018\u2019]/g,"'").replace(/[^\p{L}\p{N}']+/gu,' ').replace(/\s+/g,' ').trim();
function run(sample,tokens){
 const m={...methods,currentQuestion:{question:sample.condition,correct_tokens:sample.correct,type:'4',authored_compose:false},
 selectedTokens:tokens.map((value,id)=>({id,value})),answered:false,isCorrect:false,correctCount:0,currentIndex:0,answeredIndices:[],
 isComposeQuestion(){return true;},persistState(){}};
 m.correctAnswer=m.composeCorrectText();assert.equal(m.correctAnswer,sample.settled,'Actual native canonical settled text.');
 m.checkAnswer();const first=m.correctCount;m.checkAnswer();assert.equal(m.correctCount,first,'Repeated Check cannot double score.');
 return{correct:m.isCorrect,display:m.composeSelectedText()};
}
function check(sample){
 const canonical=run(sample,sample.correct);assert.equal(canonical.correct,true);assert.equal(canonical.display,sample.settled);
 assert.equal(run(sample,[]).correct,false,'Empty answer cannot score.');
 const capacity=new Map;for(const token of sample.bank)capacity.set(token,(capacity.get(token)||0)+1);
 const possible=tokens=>{const counts=new Map;for(const token of tokens){const n=(counts.get(token)||0)+1;counts.set(token,n);if(n>(capacity.get(token)||0))return false;}return true;};
 const cases=new Map;const add=tokens=>{if(possible(tokens))cases.set(JSON.stringify(tokens),tokens);};add([]);
 for(let start=0;start<sample.correct.length;start++){
  add([...sample.correct.slice(0,start),...sample.correct.slice(start+1)]);
  for(let length=1;length<=3&&start+length<=sample.correct.length;length++){
   for(const option of sample.bank)add([...sample.correct.slice(0,start),option,...sample.correct.slice(start+length)]);
  }
  for(let other=start+1;other<sample.correct.length;other++){
   const copy=[...sample.correct];[copy[start],copy[other]]=[copy[other],copy[start]];add(copy);
  }
  if(/^[?.!,;:]$/.test(sample.correct[start]))for(let at=0;at<sample.correct.length;at++){
   const copy=[...sample.correct];const [punctuation]=copy.splice(start,1);copy.splice(at,0,punctuation);add(copy);
  }
 }
 const bad=[],formatting=[];let correct=0;
 for(const tokens of cases.values()){const r=run(sample,tokens);if(r.correct){correct++;if(r.display!==sample.settled){
  const item={tokens,visible:r.display};
  if(lexical(r.display)===lexical(sample.settled))formatting.push(item);else bad.push(item);
 }}}
 return{id:sample.id,canonical_check:true,empty_rejected:true,repeated_check_stable:true,
 adversarial_sequences:cases.size,accepted_sequences:correct,wrong_lexical_visible_scored_correct:bad,
 formatting_only_normalization_matches:formatting.length,formatting_only_examples:formatting.slice(0,3)};
}
const selected=input.selected.map(check);for(const result of selected)assert.equal(result.wrong_lexical_visible_scored_correct.length,0,'A selected lexical/apostrophe trap cannot be frozen.');
return {profile:input.label,kind:'actual-unchanged-native-method-replay',native_raw_sha256:hash(bytes),
 native_lf_sha256:hash(Buffer.from(text)),method_source_sha256:hash(Buffer.from(source)),
 finite_adversarial_scope:'Empty, each token removal, all pairwise token swaps, standalone punctuation moves, and all bank-option replacements of contiguous spans of one to three answer tokens, bounded by exact available bank instances. Not exhaustive permutations.',
 selected,
 formatting_boundary:'Case and non-apostrophe punctuation differences are deliberately not graded strictly by this unchanged engine; formatting-only accepted variants are recorded, not represented as strict rejection or as wrong lexical content.',
 db_connections:0,db_writes:0,http_requests:0,file_writes:0};
}
const profiles=payload.profiles.map(profile=>runProfile({...payload,...profile}));
process.stdout.write(JSON.stringify({kind:'actual-unchanged-native-method-replay',profiles,db_connections:0,db_writes:0,http_requests:0,file_writes:0}));
JS;

$process=new \Symfony\Component\Process\Process(['node','-e',$nodeSource],$wt);
$process->setTimeout(45);
$acceptedWtBladeSha='773c82eed2e099e7a512a5760a4cb425cc83a72a33e40ea9420db19e663862fe';
$process->setInput(json_encode(['profiles'=>[
    ['label'=>'independent-served-ROOT-before','blade'=>$bladePath,'raw_sha256'=>$nativeBeforeSha],
    ['label'=>'accepted-worktree','blade'=>$wt.'/resources/views/components/text-block-practice-questions.blade.php','raw_sha256'=>$acceptedWtBladeSha]],
    'selected'=>$selectedProbe],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
$process->run();
if (!$process->isSuccessful()) {
    throw new RuntimeException('M78 actual unchanged native canonical/adversarial probe failed: '.trim($process->getErrorOutput()));
}
$nativeProbe=json_decode($process->getOutput(),true,flags:JSON_THROW_ON_ERROR);
if (($nativeProbe['kind']??null)!=='actual-unchanged-native-method-replay'
    ||array_column($nativeProbe['profiles']??[],'profile')!==['independent-served-ROOT-before','accepted-worktree']
    ||($nativeProbe['db_connections']??null)!==0||($nativeProbe['db_writes']??null)!==0
    ||($nativeProbe['http_requests']??null)!==0||($nativeProbe['file_writes']??null)!==0) {
    throw new RuntimeException('M78 actual native dual-profile probe scope differs.');
}
foreach ($nativeProbe['profiles'] as $i=>$profile) {
    if (($profile['native_raw_sha256']??null)!==[$nativeBeforeSha,$acceptedWtBladeSha][$i]
        ||array_column($profile['selected']??[],'id')!==array_column($selectedProbe,'id')) {
        throw new RuntimeException('M78 native profile/selected-question lineage differs.');
    }
    foreach ($profile['selected'] as $result) {
        if (($result['canonical_check']??null)!==true||($result['empty_rejected']??null)!==true
            ||($result['repeated_check_stable']??null)!==true||($result['wrong_lexical_visible_scored_correct']??null)!==[]) {
            throw new RuntimeException('M78 selected canonical/negative lexical acceptance differs.');
        }
    }
}
$audit['native_runtime_probe']=$nativeProbe;

/** Metadata-only adapter: no source load, application boot or database access. */
final class M78BankProjectionPackage
{
    public const OWNERS=Package::OWNERS;
    public const PAGE_IDS=Package::PAGE_IDS;
    public const CATEGORY_IDS=Package::CATEGORY_IDS;
    public const LEVELS=Package::LEVELS;
    public const ANCESTRIES=Package::ANCESTRIES;
    public const CONTINUING_COUNTS=Package::CONTINUING_COUNTS;
    public const PRACTICE_SOURCE_INDICES=Package::PRACTICE_SOURCE_INDICES;
}
final class M78BankAuditProjectionValidator extends TheoryBuilderSelectionPresentation
{
    public static string $targetDigest;
    protected static function packageClass():string { return M78BankProjectionPackage::class; }
    protected static function policy():array
    {
        return ['label'=>'M78 projection','package'=>'m78-phrasal-question-basics-bank-audit','version'=>'1.0.0',
            'scope'=>'30 selected / 30 reused / 0 new; no question/answer/option/grader/progress writes',
            'before_sha256'=>'327424de32f1cebaad86adef078d25aa1d4747b257894be3fdadc6706c630422',
            'targets_sha256'=>self::$targetDigest];
    }
}
M78BankAuditProjectionValidator::$targetDigest=Digest::digest($audit['targets']);
$validationCopy=$audit;$validationCopy['status']='frozen';$validationCopy['pending']=[];
M78BankAuditProjectionValidator::validateAudit($validationCopy,$author['patch'],$author['contract']);

/** Rehydrate the captured loaded relations in memory only, then exercise the unchanged exact private matcher. */
$model = static function (string $class,array $attributes): object { $m = new $class; $m->setRawAttributes($attributes,true); return $m; };
$hydrate = static function (array $s) use ($model): Question {
    $q = $model(Question::class,$s['question']); $optionMap = []; $options = [];
    $pivots = array_column($s['option_pivots'],null,'option_id');
    foreach ($s['options'] as $row) {
        $o = $model(QuestionOption::class,$row); $p = $pivots[$row['id']];
        $o->setRelation('pivot',$model(Pivot::class,['question_id'=>$p['question_id'],'option_id'=>$p['option_id'],'flag'=>$p['flag']]));
        $optionMap[$row['id']] = $o; $options[] = $o;
    }
    $answers = [];
    foreach ($s['answers'] as $row) { $a = $model(QuestionAnswer::class,$row); $a->setRelation('option',$optionMap[$row['option_id']]); $answers[] = $a; }
    $q->setRelation('answers',new Collection($answers)); $q->setRelation('options',new Collection($options));
    $q->setRelation('hints',new Collection(array_map(static fn ($row) => $model(QuestionHint::class,$row),$s['hints'])));
    $q->setRelation('verbHints',new Collection); return $q;
};
$matcher = new ReflectionMethod(TheoryBuilderSelectionPresentation::class,'matchesQuestions'); $checks = 0;
foreach ($audit['targets'] as $i=>$target) {
    $questions = new Collection(array_map($hydrate,$privateSelected[$i]));
    if ($matcher->invoke(null,$questions,$target)!==true) { throw new RuntimeException('M78 complete model-only snapshot matcher rejected canonical selection.'); }
    ++$checks;
    $stale = $questions->map(static fn ($q) => clone $q); $first = $stale->first();
    $first->setRawAttributes(array_replace($first->getAttributes(),['question'=>$first->question.' stale']),true);
    if ($matcher->invoke(null,$stale,$target)!==false) { throw new RuntimeException('M78 exact matcher failed to reject a stale question.'); }
    ++$checks;
    $stale = $questions->map(static fn ($q) => clone $q); $first = $stale->first();
    $options = $first->getRelation('options')->map(static fn ($o) => clone $o); $option = $options->first();
    $option->setRawAttributes(array_replace($option->getAttributes(),['option'=>$option->option.' stale']),true); $first->setRelation('options',$options);
    if ($matcher->invoke(null,$stale,$target)!==false) { throw new RuntimeException('M78 exact matcher failed to reject a stale shared option.'); }
    ++$checks;
    $stale=$questions->map(static fn($q)=>clone $q);$stale->first()->unsetRelation('hints');
    if ($matcher->invoke(null,$stale,$target)!==false) { throw new RuntimeException('M78 matcher accepted an incompletely loaded hint family.'); }
    ++$checks;
    $stale=$questions->map(static fn($q)=>clone $q);$first=$stale->first();
    $answers=$first->getRelation('answers')->map(static fn($a)=>clone $a);$answer=$answers->first();
    $answer->setRawAttributes(array_replace($answer->getAttributes(),['marker'=>'a999']),true);$first->setRelation('answers',$answers);
    if ($matcher->invoke(null,$stale,$target)!==false) { throw new RuntimeException('M78 matcher accepted a changed original answer marker.'); }
    ++$checks;
    $stale=$questions->map(static fn($q)=>clone $q);$first=$stale->first();
    $options=$first->getRelation('options')->map(static fn($o)=>clone $o);$option=$options->first();
    $pivot=clone $option->getRelation('pivot');$pivot->setRawAttributes(array_replace($pivot->getAttributes(),['flag'=>1]),true);
    $option->setRelation('pivot',$pivot);$first->setRelation('options',$options);
    if ($matcher->invoke(null,$stale,$target)!==false) { throw new RuntimeException('M78 matcher accepted a changed option pivot.'); }
    ++$checks;
    if ($matcher->invoke(null,$questions->reverse()->values(),$target)!==false
        || $matcher->invoke(null,$questions->slice(0,4)->values(),$target)!==false) {
        throw new RuntimeException('M78 matcher accepted wrong order or an incomplete selection.');
    }
    $checks+=2;
}

/** Traverse every public field, including nested targets; only opaque hashes represent captured row families. */
$privacyCheck=static function(mixed $value) use(&$privacyCheck):void {
    if (!is_array($value)) { return; }
    foreach ($value as $key=>$child) {
        if (is_string($key)&&preg_match('/^(?:created_at|updated_at|started_at|finished_at|row_timestamp|served_root|source_worktree|sql_before|sql_after_http|password|secret|connection|credentials|physical|runtime_identity)$/iD',$key)) {
            throw new RuntimeException('M78 public audit contains a forbidden private/runtime field.');
        }
        $privacyCheck($child);
    }
};
$privacyCheck($audit);
$json=json_encode($audit,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
if (file_exists($output)||is_link($output)) { throw new RuntimeException('M78 exclusive private output collided.'); }
$handle=fopen($output,'x');
if ($handle===false) { throw new RuntimeException('M78 exclusive private output cannot be opened.'); }
try {
    if (fwrite($handle,$json)!==strlen($json)||!fflush($handle)) { throw new RuntimeException('M78 private projection write was incomplete.'); }
} finally { fclose($handle); }
echo json_encode([
    'status'=>'draft-source-only-projection','file'=>$argv[3],'sha256'=>hash('sha256',$json),
    'targets_sha256'=>Digest::digest($audit['targets']),
    'selected_counts'=>array_map(static fn($t)=>count($t['first_five']),$audit['targets']),
    'pool_counts'=>array_map(static fn($t)=>array_sum(array_map('count',$t['expected_pool_ids_by_bank'])),$audit['targets']),
    'shared_model_matcher_checks'=>$checks,
    'db_connections'=>0,'db_writes'=>0,'http_requests'=>0,'active_source_writes'=>0,
],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";

