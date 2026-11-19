<?php
declare(strict_types=1);

// Explicit local CLI; no startup, HTTP mutation, seeder or blanket reseed.
use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\FiniteBuilderWordSlotsLocalGuard as Guard;
use App\Services\QuestionExportService;
use App\Services\FiniteBuilderWordSlotsLocalPolicy as Policy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$GLOBALS['fbws_stage'] = 'entry';
$safeException = static function (Throwable $error): void {
    fwrite(STDERR, json_encode(['status'=>'incomplete', 'stage'=>$GLOBALS['fbws_stage'],
        'detail'=>$GLOBALS['fbws_detail'] ?? null, 'exception'=>get_class($error)], JSON_THROW_ON_ERROR)."\n"); exit(1);
};
set_exception_handler($safeException);
function fbwsFail(string $reason): never { $GLOBALS['fbws_detail'] = $reason; throw new RuntimeException($reason); }
function fbwsJson(mixed $value): string { return json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n"; }
function fbwsDigest(mixed $value): string { return hash('sha256', fbwsJson($value)); }
function fbwsName(string $prefix): string { return $prefix.'-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json'; }
function fbwsExclusive(string $path, mixed $value): void {
    if (is_link($path)) { fbwsFail('Linked private output refused.'); }
    $stream = fopen($path, 'x'); if (!$stream) { fbwsFail('Exclusive private output exists.'); }
    try { $bytes = is_string($value) ? $value : fbwsJson($value); if (fwrite($stream, $bytes) !== strlen($bytes) || !fflush($stream)) { fbwsFail('Private output incomplete.'); } }
    finally { fclose($stream); }
}
function fbwsRead(string $directory, string $name, string $prefix): array {
    if (preg_match('/^'.preg_quote($prefix,'/').'-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$/D', $name) !== 1
        || !is_file($directory.'/'.$name) || is_link($directory.'/'.$name)) { fbwsFail('Exact private receipt basename required.'); }
    return json_decode(file_get_contents($directory.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
}
function fbwsRows($query): array { return $query->get()->map(static fn ($row): array => (array)$row)->all(); }
function fbwsOption(string $value): string { return mb_strtolower(Str::ascii(trim($value))); }
function fbwsIdentifier(string $name): string {
    if (preg_match('/^[a-zA-Z0-9_]+$/D', $name) !== 1) { fbwsFail('Unsafe SQL identifier.'); } return '`'.$name.'`';
}
function fbwsHashRow($hash, array $row): void {
    foreach ($row as $key=>$value) { hash_update($hash, strlen($key).':'.$key.($value === null ? 'N;' : 'S'.strlen((string)$value).':'.$value.';')); }
    hash_update($hash, "\n");
}

$mode = $argv[1] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array($mode, ['proof','inspect','preview','apply','verify','snapshots'], true)
    || strcasecmp(str_replace('\\','/',(string)realpath(dirname(__DIR__,2))), 'D:/DEV/htdocs/gramlyze.loc') !== 0) {
    fbwsFail('Explicit served Windows ROOT CLI mode required.');
}
require dirname(__DIR__,2).'/vendor/autoload.php';
$app = require dirname(__DIR__,2).'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler($safeException);
$directory = Guard::privateDirectory();
if (is_link($directory) || (!is_dir($directory) && !mkdir($directory,0700,true))
    || strcasecmp(str_replace('\\','/',(string)realpath($directory)), $directory) !== 0) { fbwsFail('Private local directory differs.'); }
$db = DB::connection(); Guard::assertConfiguredDatabase($db);
$guard = new Guard; $GLOBALS['fbws_stage'] = 'physical-local-target'; $physical = $guard->physicalEvidence();
$server = (array)$db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port, @@pid_file AS pid_file');
if ($server['db'] !== 'gr2' || !is_file($server['pid_file']) || !ctype_digit(trim((string)file_get_contents($server['pid_file'])))) { fbwsFail('Local MySQL identity differs.'); }
$guard->assertEvidence($physical, strtolower(str_replace('\\','/',(string)realpath(base_path()))), (int)trim(file_get_contents($server['pid_file'])),3306);
if ($mode === 'proof') {
    if (count($argv) !== 2) { fbwsFail('proof takes no arguments.'); }
    $nonce = bin2hex(random_bytes(16)); $hashes = [];
    foreach (['C:/Program Files/xampp/apache/conf/httpd.conf','C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
        'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf',Guard::ROOT.'/public/index.php'] as $path) {
        if (!is_file($path) || is_link($path)) { fbwsFail('Plain target file missing.'); } $hashes[$path]=hash_file('sha256',$path);
    }
    $proof=['target'=>'gramlyze.loc','nonce'=>$nonce,'created_at'=>gmdate('c'),'scope'=>Guard::scope(),
        'physical_hashes'=>$hashes,'physical'=>$physical,'runtime'=>Guard::publicIdentity($db,Guard::ROOT.'/public')];
    $name='local-proof-'.$nonce.'.json'; Guard::assertFreshProof($proof,$name);
    fbwsExclusive($directory.'/routes-before-'.$nonce.'.bin',file_get_contents(base_path('routes/api.php'))); fbwsExclusive($directory.'/'.$name,$proof);
    echo fbwsJson(['proof'=>$name,'nonce'=>$nonce,'route_path'=>'/api/_local/finite-builder-word-slots-'.$nonce,
        'routes_sha256'=>hash_file('sha256',base_path('routes/api.php')),'database_writes'=>0]); exit;
}

function fbwsSources(): array {
    $baseline=Guard::baseline();$editPath='docs/content/finite-builder-word-slots/prompt-edits.v1.json';
    if(!is_file(base_path($editPath))||is_link(base_path($editPath))
        ||hash_file('sha256',base_path($editPath))!=='c6ad8306295e7ce72a9056d9e79776f52ac1a0e3c11c9b17f09e976709614814'){fbwsFail('Exact reviewed prompt manifest missing.');}
    $edits=json_decode(file_get_contents(base_path($editPath)),true,flags:JSON_THROW_ON_ERROR);
    if(($edits['schema_version']??null)!==1||!is_array($edits['edits']??null)){fbwsFail('Reviewed prompt manifest schema differs.');}
    $editMap=[];foreach($edits['edits'] as $edit){if(isset($editMap[$edit['uuid']])){fbwsFail('Duplicate reviewed prompt UUID.');}$editMap[$edit['uuid']]=$edit;}
    $specs=[];$optionPool=[];$banks=[];$paths=[$editPath,'docs/content/finite-builder-word-slots.previous.v1.json'];$seenEdits=[];
    foreach($baseline['banks'] as $bank){
        $catalog=$bank['catalog'];$topic=$bank['topic'];$map=$bank['mapping'];
        if(!is_subclass_of($catalog,App\Support\FinitePracticeQualityCatalog::class)||$catalog::mapping($topic)!==$map){fbwsFail('Finite catalog binding differs.');}
        $old=$bank['definition'];$new=$catalog::compile($topic,'builder');
        $actual=json_decode(file_get_contents(base_path($map['builder']['definition'])),true,flags:JSON_THROW_ON_ERROR);
        if($actual!==$new||json_decode($bank['definition_bytes'],true,flags:JSON_THROW_ON_ERROR)!==$old){fbwsFail('Compiled builder differs from exact source: '.$catalog.'/'.$topic);}
        $oldStandard=json_decode($bank['standard_bytes'],true,flags:JSON_THROW_ON_ERROR);
        $newStandard=json_decode(file_get_contents(base_path($map['standard']['definition'])),true,flags:JSON_THROW_ON_ERROR);
        if($newStandard!==$catalog::compile($topic,'standard')){fbwsFail('Standard definition is stale.');}
        unset($oldStandard['compiled_from'],$newStandard['compiled_from']);
        if($oldStandard!==$newStandard){fbwsFail('Standard bank changes are outside this correction.');}
        $authorBefore=json_decode($bank['source_bytes'],true,flags:JSON_THROW_ON_ERROR);$authorAfter=$catalog::source($topic);$authorEdits=[];
        foreach($old['questions'] as $row){if(isset($editMap[$row['uuid']])){$edit=$editMap[$row['uuid']];if($edit['catalog']!==$catalog||$edit['topic']!==$topic||$edit['author_id']!==$row['author_id']){fbwsFail('Reviewed author identity differs.');}$authorEdits[$row['author_id']]=$edit;$seenEdits[$row['uuid']]=true;}}
        try { Policy::assertAuthor($authorBefore,$authorAfter,$authorEdits); }
        catch (RuntimeException $error) { fbwsFail($catalog.'/'.$topic.': '.$error->getMessage()); }
        $oldHeader=$old;$newHeader=$new;unset($oldHeader['questions'],$newHeader['questions'],$oldHeader['compiled_from'],$newHeader['compiled_from']);
        if($oldHeader!==$newHeader||array_column($old['questions'],'uuid')!==array_column($new['questions'],'uuid')){fbwsFail('Builder header/inventory changed.');}
        $seeder=$new['seeder']['class'];if(isset($banks[$seeder])){fbwsFail('Duplicate bank identity.');}
        $banks[$seeder]=['uuids'=>array_column($new['questions'],'uuid'),'category'=>$new['category']['name'],'sources'=>$new['sources']];
        foreach($new['questions'] as $i=>$row){
            $previous=$old['questions'][$i];
            try { Policy::assertQuestion($previous,$row); }
            catch (RuntimeException $error) { fbwsFail($row['uuid'].': '.$error->getMessage()); }
            if(Policy::persistence($previous)===Policy::persistence($row)){continue;}
            if(isset($specs[$row['uuid']])){fbwsFail('Duplicate target UUID.');}
            $specs[$row['uuid']]=['before'=>$previous,'after'=>$row,'seeder'=>$seeder,'category'=>$new['category']['name'],'source'=>$new['sources'][$row['source']]['name']];
            foreach(array_merge(Policy::options($row),[$row['verb_hint']]) as $value){$optionPool[$value]=true;}
        }
        foreach([$map['author'],$map['standard']['definition'],$map['builder']['definition'],$map['builder']['loader'],$map['builder']['implementation'],$map['builder']['old_definition'],$map['standard']['old_definition']] as $path){$paths[]=$path;}
        $paths[]='app/Support/'.substr($catalog,strrpos($catalog,'\\')+1).'.php';
    }
    if(count($seenEdits)!==count($editMap)||!$specs){fbwsFail('Reviewed edits unmatched or no changed builders.');}
    $paths=array_merge($paths,['app/Support/FinitePracticeQualityCatalog.php','app/Support/FinitePracticeChoiceOptions.php','app/Support/PassivePracticeQuality.php',
        'app/Services/FiniteBuilderWordSlotsLocalGuard.php','app/Services/FiniteBuilderWordSlotsLocalPolicy.php','app/Services/PresentPerfectPassiveWordSlotsLocalGuard.php',
        'app/Services/TheoryLocalReleaseGuard.php','app/Services/M11LocalTargetGuard.php','app/Services/M49LocalTargetGuard.php','app/Services/QuestionExportService.php',
        'tools/diagnostics/inspect-passive-voice-local-target.ps1','tools/diagnostics/finite-builder-word-slots-local.php',
        'resources/views/components/saved-test-js-helpers.blade.php','resources/views/test-modes/card-easy.blade.php','resources/views/test-modes/step-easy.blade.php','public/js/english-answer-variants.js']);
    foreach(glob(base_path('app/Support/*WordSlots*'))?:[] as $file){$paths[]=str_replace('\\','/',substr($file,strlen(base_path())+1));}
    $hashes=[];foreach(array_unique($paths) as $path){if(!is_file(base_path($path))||is_link(base_path($path))){fbwsFail('Source missing/linked.');}$hashes[$path]=hash_file('sha256',base_path($path));}
    $hashes['private:baseline.json']=Guard::BASELINE_SHA256;ksort($hashes);return ['specs'=>$specs,'optionPool'=>$optionPool,'hashes'=>$hashes,'banks'=>$banks];
}
function fbwsFocus(array $source): array {
    $all=fbwsRows(DB::table('questions')->whereIn('seeder',array_keys($source['banks']))->orderBy('id'));
    $categories=DB::table('categories')->pluck('name','id')->all();$sources=DB::table('sources')->pluck('name','id')->all();$seen=[];$questions=[];$ids=[];
    foreach($all as $row){
        $bank=$source['banks'][$row['seeder']];$uuid=$row['uuid'];
        if(isset($seen[$row['seeder']][$uuid])||!in_array($uuid,$bank['uuids'],true)||(string)$row['type']!=='4'||($categories[$row['category_id']]??null)!==$bank['category']){fbwsFail('Exact existing builder bank identity differs.');}
        $seen[$row['seeder']][$uuid]=true;$spec=$source['specs'][$uuid]??null;if(!$spec){continue;}
        if($row['seeder']!==$spec['seeder']||$row['level']!==$spec['after']['level']||($sources[$row['source_id']]??null)!==$spec['source']){fbwsFail('Target question source/level differs.');}
        $questions[]=$row;$ids[]=(int)$row['id'];
    }
    foreach($source['banks'] as $seeder=>$bank){$want=$bank['uuids'];$found=array_keys($seen[$seeder]??[]);sort($want);sort($found);if($want!==$found){fbwsFail('Existing finite bank UUID inventory differs.');}}
    if(count($ids)!==count($source['specs'])){fbwsFail('Not every exact target exists.');}
    $relations=[];foreach(['question_answers','verb_hints','question_option_question','question_variants','question_hints'] as $table){$relations[$table]=fbwsRows(DB::table($table)->whereIn('question_id',$ids)->orderBy('id'));}
    $optionIds=array_unique(array_merge(array_column($relations['question_answers'],'option_id'),array_column($relations['verb_hints'],'option_id'),array_column($relations['question_option_question'],'option_id')));
    $options=fbwsRows(DB::table('question_options')->whereIn('id',$optionIds)->orderBy('id'));
    return ['questions'=>$questions,'targetIds'=>$ids,'relations'=>$relations,'options'=>$options];
}
function fbwsTables(array $source,array $focus,?array $baseline=null): array {
    $pdo=DB::connection()->getPdo(); $tables=$pdo->query('SHOW FULL TABLES WHERE Table_type="BASE TABLE"')->fetchAll(PDO::FETCH_COLUMN); sort($tables);
    if(count($tables)!==46||($baseline!==null&&array_keys($baseline)!==$tables)) { fbwsFail('Exact46-table inventory differs.'); }
    $engines=$pdo->query('SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE="BASE TABLE"')->fetchAll(PDO::FETCH_KEY_PAIR); ksort($engines);
    if(array_keys($engines)!==$tables||array_filter($engines,static fn($e)=>$e!=='InnoDB')!==[]) { fbwsFail('All protected tables must be transactional.'); }
    $ids=array_fill_keys(array_map('strval',$focus['targetIds']),true); $out=[];
    $buffered=$pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY); $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,false);
    try { foreach($tables as $table) {
        $columns=$pdo->query('SHOW COLUMNS FROM '.fbwsIdentifier($table))->fetchAll(PDO::FETCH_COLUMN);
        $keys=$pdo->query('SHOW KEYS FROM '.fbwsIdentifier($table).' WHERE Key_name="PRIMARY"')->fetchAll(PDO::FETCH_ASSOC);
        usort($keys,static fn($a,$b)=>(int)$a['Seq_in_index']<=>(int)$b['Seq_in_index']); $primary=$keys?array_column($keys,'Column_name'):$columns;
        if($baseline!==null&&($baseline[$table]['columns']!==$columns||$baseline[$table]['primary']!==$primary)) { fbwsFail('Schema changed.'); }
        $statement=$pdo->query('SELECT * FROM '.fbwsIdentifier($table).' ORDER BY '.implode(',',array_map(fbwsIdentifier(...),$primary)));
        $full=hash_init('sha256'); $protected=hash_init('sha256'); $count=0; $protectedCount=0; $maxId=0; $newCount=0;
        try { while($row=$statement->fetch(PDO::FETCH_ASSOC)) {
            ++$count; fbwsHashRow($full,$row);
            if($table==='question_options') {
                $maxId=max($maxId,(int)$row['id']);
                if($baseline!==null&&(int)$row['id']>$baseline[$table]['maximum_id']) {
                    if(!isset($source['optionPool'][$row['option']??''])) { fbwsFail('Unapproved new shared option.'); } ++$newCount; continue;
                }
            }
            $masked=Guard::protectedRow($table,$row,$ids); if($masked!==null) { ++$protectedCount; fbwsHashRow($protected,$masked); }
        } } finally { $statement->closeCursor(); }
        $protectedHash=hash_final($protected);
        if($baseline!==null&&($protectedCount!==$baseline[$table]['protected_count']||$protectedHash!==$baseline[$table]['protected_sha256'])) { fbwsFail('Protected row changed: '.$table); }
        $out[$table]=['engine'=>$engines[$table],'columns'=>$columns,'primary'=>$primary,'count'=>$count,'sha256'=>hash_final($full),
            'protected_count'=>$protectedCount,'protected_sha256'=>$protectedHash,'maximum_id'=>$maxId,'approved_new_options'=>$newCount];
    } } finally { $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,$buffered); } return $out;
}
function fbwsCapture(array $source,?array $baseline=null): array { $focus=fbwsFocus($source); return ['focus'=>$focus,'tables'=>fbwsTables($source,$focus,$baseline['tables']??null)]; }
function fbwsRelated(array $focus,string $table,int $id): array { return array_values(array_filter($focus['relations'][$table],static fn($r)=>(int)$r['question_id']===$id)); }
function fbwsMatches(array $focus,array $row,array $expected): bool { return Policy::matches($focus,$row,$expected); }
/** Safe structural diagnostics only: never emit questions, answers, hints or runtime credentials. */
function fbwsMismatchFields(array $focus,array $row,array $expected): array {
    $fields=[];$options=array_column($focus['options'],'option','id');$id=(int)$row['id'];
    if($row['question']!==$expected['question']){$fields[]='question';}
    $stored=$row['options_by_marker']===null?null:json_decode($row['options_by_marker'],true,flags:JSON_THROW_ON_ERROR);
    if($stored!==Policy::optionsByMarker($expected)){$fields[]='options_by_marker';}
    foreach(['question_answers'=>'answer','verb_hints'=>'verb_hint'] as $table=>$field){
        $found=[];$invalid=false;
        foreach(Policy::related($focus,$table,$id) as $entry){
            if(isset($found[$entry['marker']])||($table==='verb_hints'&&!in_array($entry['locale'],['','uk'],true))){$invalid=true;}
            $found[$entry['marker']]=Policy::option($options[$entry['option_id']]??'');
        }
        $want=array_map(static fn($m)=>Policy::option($m[$field]),$expected['markers']);ksort($found);ksort($want);
        if($invalid||$found!==$want){$fields[]=$table;}
    }
    $variants=Policy::related($focus,'question_variants',$id);$hints=Policy::related($focus,'question_hints',$id);
    if(count($variants)!==1||$variants[0]['text']!==$expected['question']){$fields[]='question_variants';}
    if(count($hints)!==1||$hints[0]['provider']!=='compose_prompt'||$hints[0]['locale']!=='uk'||$hints[0]['hint']!==$expected['question']){$fields[]='question_hints';}
    $actual=[];foreach(Policy::related($focus,'question_option_question',$id) as $pivot){$actual[]=($pivot['flag']===null?'option':((int)$pivot['flag']===1?'hint':'foreign')).'|'.Policy::option($options[$pivot['option_id']]??'');}
    $want=array_map(static fn($value)=>'option|'.Policy::option($value),Policy::options($expected));$want[]='hint|'.Policy::option($expected['verb_hint']);$want=array_values(array_unique($want));sort($actual);sort($want);
    if($actual!==$want){$fields[]='question_option_question';}
    return ['fields'=>$fields,'actual_pivots'=>count($actual),'expected_pivots'=>count($want),'normalized_duplicate_pivots'=>count($actual)-count(array_unique($actual))];
}
/** The same exact relation sets as full-focus filtering, indexed once instead of once per marker. */
function fbwsQuestionFocus(array $focus): array {
    $byQuestion=[];$empty=array_fill_keys(array_keys($focus['relations']),[]);
    foreach($focus['questions'] as $row){$byQuestion[(int)$row['id']]=['relations'=>$empty,'options'=>[]];}
    foreach($focus['relations'] as $table=>$rows){
        foreach($rows as $row){$id=(int)$row['question_id'];if(isset($byQuestion[$id])){$byQuestion[$id]['relations'][$table][]=$row;}}
    }
    $options=array_column($focus['options'],null,'id');
    foreach($byQuestion as &$question){
        $ids=[];
        foreach(['question_answers','verb_hints','question_option_question'] as $table){
            foreach($question['relations'][$table] as $row){$ids[$row['option_id']]=true;}
        }
        foreach($ids as $id=>$_){if(isset($options[$id])){$question['options'][]=$options[$id];}}
    }
    unset($question);return $byQuestion;
}
function fbwsStatus(array $state,array $source): string {
    $statuses=[];$indexed=fbwsQuestionFocus($state['focus']);foreach($state['focus']['questions'] as $row) {
        $focus=$indexed[(int)$row['id']];$spec=$source['specs'][$row['uuid']]; $status=fbwsMatches($focus,$row,$spec['after'])?'after':(fbwsMatches($focus,$row,$spec['before'])?'before':'conflict');
        if($status==='conflict' && !isset($GLOBALS['fbws_first_conflict'])){$GLOBALS['fbws_first_conflict']=$row['uuid'];}
        $statuses[]=$status;
    } return count(array_unique($statuses))===1?$statuses[0]:'conflict';
}
function fbwsPlan(array $state,array $source): array {
    $out=[];foreach($state['focus']['questions'] as $row) { $spec=$source['specs'][$row['uuid']];$out[$row['uuid']]=['id'=>(int)$row['id'],'uuid'=>$row['uuid'],'level'=>$row['level'],
        'before_question'=>$spec['before']['question'],'after_question'=>$spec['after']['question'],'before_markers'=>$spec['before']['markers'],'after_markers'=>$spec['after']['markers'],
        'after_options'=>Policy::options($spec['after']),'after_options_by_marker'=>Policy::optionsByMarker($spec['after'])]; }return $out;
}
/** Match the exact normalized option-set contract, even when stored option IDs differ by spelling. */
function fbwsPivots(int $questionId,array $values,array $optionIds,string $hint): array {
    $seen=[];$pivots=[];
    foreach($values as $value){$key=Policy::option($value);if(isset($seen[$key])){continue;}$seen[$key]=true;
        $pivots[]=['question_id'=>$questionId,'option_id'=>$optionIds[$value],'flag'=>null];
    }
    $pivots[]=['question_id'=>$questionId,'option_id'=>$optionIds[$hint],'flag'=>1];return $pivots;
}
function fbwsRetainedRelations(array $before,array $after): void {
    foreach(['question_answers','verb_hints'] as $table) {
        $current=array_column($after['focus']['relations'][$table],null,'id');
        foreach($before['focus']['relations'][$table] as $row) { $actual=$current[$row['id']]??null; if(!$actual) { fbwsFail('An old marker relation disappeared.'); } unset($row['option_id'],$actual['option_id']); if($row!==$actual) { fbwsFail('Old relation identity/timestamps changed.'); } }
    }
}

$GLOBALS['fbws_stage']='canonical-source'; $source=fbwsSources();
if($mode==='inspect') {
    if(count($argv)!==2) { fbwsFail('inspect takes no arguments.'); }
    $db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
    try { $state=fbwsCapture($source);$status=fbwsStatus($state,$source);if($status==='conflict') { fbwsFail('Mixed/partial target state refused: '.($GLOBALS['fbws_first_conflict']??'mixed')); } } finally { $db->rollBack(); }
    $name=fbwsName('before'); fbwsExclusive($directory.'/'.$name,['created_at'=>gmdate('c'),'scope'=>Guard::scope(),'source_hashes'=>$source['hashes'],'state'=>$state,'status'=>$status]);
    echo fbwsJson(['before'=>$name,'status'=>$status,'targets'=>count($source['specs']),'protected_tables'=>46,'database_writes'=>0]);exit;
}
$beforeName=$argv[2]??'';$before=fbwsRead($directory,$beforeName,'before');
if(($before['scope']??null)!==Guard::scope()||($before['source_hashes']??null)!==$source['hashes']||fbwsStatus($before['state'],$source)==='conflict') { fbwsFail('BEFORE scope/source/state differs.'); }
$plan=fbwsPlan($before['state'],$source);$proofName=$mode==='preview'?($argv[3]??''):($argv[4]??'');
$guard->verify($db,'gramlyze.loc',$directory,$proofName,$server);
if($mode==='preview') {
    if(count($argv)!==4) { fbwsFail('preview requires BEFORE and PROOF basenames.'); }
    $db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
    try { $state=fbwsCapture($source,$before['state']);$status=fbwsStatus($state,$source);if($status==='conflict'||(fbwsDigest($state)!==fbwsDigest($before['state'])&&$status!=='after')) { fbwsFail('Fresh exact BEFORE or complete AFTER required.'); } } finally { $db->rollBack(); }
    $name=fbwsName('preview');fbwsExclusive($directory.'/'.$name,['created_at'=>gmdate('c'),'before_file'=>$beforeName,'before_sha256'=>hash_file('sha256',$directory.'/'.$beforeName),'source_hashes'=>$source['hashes'],'plan'=>$plan]);
    echo fbwsJson(['preview'=>$name,'target_question_updates'=>count($source['specs']),'new_questions'=>0,'native_updates'=>0,'filter_updates'=>0,'already_after'=>$status==='after','database_writes'=>0]);exit;
}
if(count($argv)!==5) { fbwsFail('apply/verify/snapshots require BEFORE, PREVIEW, PROOF basenames.'); }
$previewName=$argv[3];$preview=fbwsRead($directory,$previewName,'preview');
if(($preview['before_file']??null)!==$beforeName||($preview['before_sha256']??null)!==hash_file('sha256',$directory.'/'.$beforeName)||($preview['source_hashes']??null)!==$source['hashes']||($preview['plan']??null)!==$plan) { fbwsFail('Reviewed exact diff/source changed.'); }
if($mode==='apply') {
    $GLOBALS['fbws_stage']='transactional-finite-builder-apply';$db->statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');$db->beginTransaction();
    try {
        $ids=array_column($plan,'id');DB::table('questions')->whereIn('id',$ids)->orderBy('id')->lockForUpdate()->get();
        foreach(['question_answers','verb_hints','question_variants','question_hints','question_option_question'] as $table) { DB::table($table)->whereIn('question_id',$ids)->orderBy('id')->lockForUpdate()->get(); }
        $state=fbwsCapture($source,$before['state']);$status=fbwsStatus($state,$source);
        if($status==='after') { fbwsRetainedRelations($before['state'],$state);$db->rollBack();echo fbwsJson(['no_op'=>true,'database_writes'=>0,'targets'=>count($source['specs'])]);exit; }
        if($status!=='before'||fbwsDigest($state)!==fbwsDigest($before['state'])) { fbwsFail('Exact unmodified BEFORE required.'); }
        $backup=fbwsName('backup');fbwsExclusive($directory.'/'.$backup,['created_at'=>gmdate('c'),'before_file'=>$beforeName,'preview_file'=>$previewName,'source_hashes'=>$source['hashes'],'state'=>$state,'plan'=>$plan]);
        $optionCache=[];$oldMarkers=[];
        foreach(['question_answers','verb_hints'] as $table){
            foreach($state['focus']['relations'][$table] as $row){$oldMarkers[$table][(int)$row['question_id']][$row['marker']]=$row;}
        }
        foreach($plan as $patch) {
            $id=$patch['id'];$optionIds=[];$hint=reset($patch['after_markers'])['verb_hint'];
            foreach(array_merge($patch['after_options'],[$hint]) as $value) {
                // Exact authored spelling remains the cache key; database collation still resolves first occurrence.
                if(!array_key_exists($value,$optionCache)){$optionCache[$value]=QuestionOption::firstOrCreate(['option'=>$value])->id;}
                $optionIds[$value]=$optionCache[$value];
            }
            DB::table('questions')->where('id',$id)->update(['question'=>$patch['after_question'],'options_by_marker'=>$patch['after_options_by_marker']===null?null:json_encode($patch['after_options_by_marker'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)]);
            foreach(['question_answers'=>'answer','verb_hints'=>'verb_hint'] as $table=>$field) {
                $existing=$oldMarkers[$table][$id];$template=reset($existing);$inserts=[];
                foreach($patch['after_markers'] as $marker=>$definition) {
                    if(isset($existing[$marker])) { DB::table($table)->where('id',$existing[$marker]['id'])->where('question_id',$id)->update(['option_id'=>$optionIds[$definition[$field]]]); }
                    else { $entry=$template;unset($entry['id']);$entry['marker']=$marker;$entry['option_id']=$optionIds[$definition[$field]];$inserts[]=$entry; }
                }
                if($inserts){DB::table($table)->insert($inserts);}
            }
            DB::table('question_variants')->where('question_id',$id)->update(['text'=>$patch['after_question']]);
            DB::table('question_hints')->where('question_id',$id)->where('provider','compose_prompt')->where('locale','uk')->update(['hint'=>$patch['after_question']]);
            DB::table('question_option_question')->where('question_id',$id)->delete();
            DB::table('question_option_question')->insert(fbwsPivots($id,$patch['after_options'],$optionIds,$hint));
        }
        $after=fbwsCapture($source,$before['state']);fbwsRetainedRelations($before['state'],$after);
        $afterStatus=fbwsStatus($after,$source);
        if($afterStatus!=='after'){
            $indexedAfter=fbwsQuestionFocus($after['focus']);$conflicts=[];
            foreach($after['focus']['questions'] as $row){$expected=$source['specs'][$row['uuid']]['after'];$focus=$indexedAfter[(int)$row['id']];
                if(!fbwsMatches($focus,$row,$expected)){$conflicts[$row['uuid']]=fbwsMismatchFields($focus,$row,$expected);if(count($conflicts)===5){break;}}
            }
            fbwsFail('Exact AFTER differs: '.json_encode(['status'=>$afterStatus,'first_conflicts'=>$conflicts],JSON_THROW_ON_ERROR));
        }
        $freshHashes=fbwsSources()['hashes'];
        if($source['hashes']!==$freshHashes){$changed=[];foreach(array_unique(array_merge(array_keys($source['hashes']),array_keys($freshHashes))) as $path){if(($source['hashes'][$path]??null)!==($freshHashes[$path]??null)){$changed[]=$path;}}fbwsFail('Source hashes changed: '.json_encode($changed,JSON_THROW_ON_ERROR));}
        $db->commit();$receipt=fbwsName('applied');fbwsExclusive($directory.'/'.$receipt,['created_at'=>gmdate('c'),'before_file'=>$beforeName,'preview_file'=>$previewName,'backup'=>$backup,'source_hashes'=>$source['hashes'],'ids'=>$ids,'protected_tables'=>46,'old_relation_ids_timestamps_preserved'=>true,'new_questions'=>0]);
        echo fbwsJson(['applied'=>true,'receipt'=>$receipt,'backup'=>$backup,'target_question_updates'=>count($source['specs']),'new_questions'=>0,'protected_tables'=>46]);exit;
    } catch(Throwable $e) { if($db->transactionLevel()) {$db->rollBack();}throw $e; }
}
$GLOBALS['fbws_stage']='read-only-after-verification';$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try {
    $state=fbwsCapture($source,$before['state']);fbwsRetainedRelations($before['state'],$state);if(fbwsStatus($state,$source)!=='after') { fbwsFail('Complete finite-builder AFTER unavailable.'); }
    if($mode==='snapshots') {
        $snapshotDirectory=config('questions.export_path',database_path('seeders/questions'));
        if(is_link($snapshotDirectory)||strcasecmp(str_replace('\\','/',(string)realpath($snapshotDirectory)),Guard::ROOT.'/database/seeders/questions')!==0) { fbwsFail('Canonical snapshot directory differs.'); }
        $names=array_fill_keys(array_map(static fn($u)=>$u.'.json',array_keys($plan)),true);$outside=[];$backupFiles=[];
        foreach(glob($snapshotDirectory.'/*.json')?:[] as $file) { if(is_link($file)){fbwsFail('Linked snapshot.');}if(isset($names[basename($file)])){$backupFiles[basename($file)]=file_get_contents($file);}else{$outside[basename($file)]=hash_file('sha256',$file);} }
        if(count($backupFiles)!==count($source['specs'])){fbwsFail('All target previous snapshots required.');}$snapshotBackup=fbwsName('snapshot-backup');fbwsExclusive($directory.'/'.$snapshotBackup,$backupFiles);$files=[];
        foreach(Question::query()->whereIn('id',array_column($plan,'id'))->orderBy('id')->get() as $question) {
            if(!isset($plan[$question->uuid])||$question->seeder!==$source['specs'][$question->uuid]['seeder']) { fbwsFail('Snapshot model binding differs.'); }
            app(QuestionExportService::class)->export($question);$path=$snapshotDirectory.'/'.$question->uuid.'.json';$files[basename($path)]=hash_file('sha256',$path);
        }
        $outsideAfter=[];foreach(glob($snapshotDirectory.'/*.json')?:[] as $file){if(!isset($names[basename($file)])){$outsideAfter[basename($file)]=hash_file('sha256',$file);}}
        if(count($files)!==count($source['specs'])||$outside!==$outsideAfter||fbwsDigest(fbwsCapture($source,$before['state']))!==fbwsDigest($state)){fbwsFail('Snapshot export changed outside files/DB.');}
        $snapshotReceipt=['count'=>count($source['specs']),'files'=>$files,'backup'=>$snapshotBackup,'outside_snapshots_unchanged'=>true];
    }
} finally {$db->rollBack();}
$receipt=fbwsName($mode==='snapshots'?'snapshots':'verified');fbwsExclusive($directory.'/'.$receipt,['created_at'=>gmdate('c'),'before_file'=>$beforeName,'preview_file'=>$previewName,'source_hashes'=>$source['hashes'],'targets'=>count($source['specs']),'protected_tables'=>46,'database_writes'=>0,'snapshots'=>$snapshotReceipt??null]);
echo fbwsJson(['verified'=>true,'receipt'=>$receipt,'targets'=>count($source['specs']),'protected_tables'=>46,'snapshots_exported'=>$snapshotReceipt['count']??0,'database_writes'=>0]);


