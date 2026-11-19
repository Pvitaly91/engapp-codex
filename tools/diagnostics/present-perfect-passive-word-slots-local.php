<?php
declare(strict_types=1);

// Explicit local CLI; no startup, HTTP mutation, seeder or blanket reseed.
use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\PresentPerfectPassiveWordSlotsLocalGuard as Guard;
use App\Services\QuestionExportService;
use App\Support\PassiveVoicePracticeQuality as Quality;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$GLOBALS['ppws_stage'] = 'entry';
$safeException = static function (Throwable $error): void {
    fwrite(STDERR, json_encode(['status'=>'incomplete', 'stage'=>$GLOBALS['ppws_stage'],
        'detail'=>$GLOBALS['ppws_detail'] ?? null, 'exception'=>get_class($error)], JSON_THROW_ON_ERROR)."\n"); exit(1);
};
set_exception_handler($safeException);
function ppwsFail(string $reason): never { $GLOBALS['ppws_detail'] = $reason; throw new RuntimeException($reason); }
function ppwsJson(mixed $value): string { return json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n"; }
function ppwsDigest(mixed $value): string { return hash('sha256', ppwsJson($value)); }
function ppwsName(string $prefix): string { return $prefix.'-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json'; }
function ppwsExclusive(string $path, mixed $value): void {
    if (is_link($path)) { ppwsFail('Linked private output refused.'); }
    $stream = fopen($path, 'x'); if (!$stream) { ppwsFail('Exclusive private output exists.'); }
    try { $bytes = is_string($value) ? $value : ppwsJson($value); if (fwrite($stream, $bytes) !== strlen($bytes) || !fflush($stream)) { ppwsFail('Private output incomplete.'); } }
    finally { fclose($stream); }
}
function ppwsRead(string $directory, string $name, string $prefix): array {
    if (preg_match('/^'.preg_quote($prefix,'/').'-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$/D', $name) !== 1
        || !is_file($directory.'/'.$name) || is_link($directory.'/'.$name)) { ppwsFail('Exact private receipt basename required.'); }
    return json_decode(file_get_contents($directory.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
}
function ppwsRows($query): array { return $query->get()->map(static fn ($row): array => (array)$row)->all(); }
function ppwsOption(string $value): string { return mb_strtolower(Str::ascii(trim($value))); }
function ppwsIdentifier(string $name): string {
    if (preg_match('/^[a-zA-Z0-9_]+$/D', $name) !== 1) { ppwsFail('Unsafe SQL identifier.'); } return '`'.$name.'`';
}
function ppwsHashRow($hash, array $row): void {
    foreach ($row as $key=>$value) { hash_update($hash, strlen($key).':'.$key.($value === null ? 'N;' : 'S'.strlen((string)$value).':'.$value.';')); }
    hash_update($hash, "\n");
}

$mode = $argv[1] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array($mode, ['proof','inspect','preview','apply','verify','snapshots'], true)
    || strcasecmp(str_replace('\\','/',(string)realpath(dirname(__DIR__,2))), 'D:/DEV/htdocs/gramlyze.loc') !== 0) {
    ppwsFail('Explicit served Windows ROOT CLI mode required.');
}
require dirname(__DIR__,2).'/vendor/autoload.php';
$app = require dirname(__DIR__,2).'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler($safeException);
$directory = Guard::privateDirectory();
if (is_link($directory) || (!is_dir($directory) && !mkdir($directory,0700,true))
    || strcasecmp(str_replace('\\','/',(string)realpath($directory)), $directory) !== 0) { ppwsFail('Private local directory differs.'); }
$db = DB::connection(); Guard::assertConfiguredDatabase($db);
$guard = new Guard; $GLOBALS['ppws_stage'] = 'physical-local-target'; $physical = $guard->physicalEvidence();
$server = (array)$db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port, @@pid_file AS pid_file');
if ($server['db'] !== 'gr2' || !is_file($server['pid_file']) || !ctype_digit(trim((string)file_get_contents($server['pid_file'])))) { ppwsFail('Local MySQL identity differs.'); }
$guard->assertEvidence($physical, strtolower(str_replace('\\','/',(string)realpath(base_path()))), (int)trim(file_get_contents($server['pid_file'])),3306);
if ($mode === 'proof') {
    if (count($argv) !== 2) { ppwsFail('proof takes no arguments.'); }
    $nonce = bin2hex(random_bytes(16)); $hashes = [];
    foreach (['C:/Program Files/xampp/apache/conf/httpd.conf','C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
        'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf',Guard::ROOT.'/public/index.php'] as $path) {
        if (!is_file($path) || is_link($path)) { ppwsFail('Plain target file missing.'); } $hashes[$path]=hash_file('sha256',$path);
    }
    $proof=['target'=>'gramlyze.loc','nonce'=>$nonce,'created_at'=>gmdate('c'),'scope'=>Guard::scope(),
        'physical_hashes'=>$hashes,'physical'=>$physical,'runtime'=>Guard::publicIdentity($db,Guard::ROOT.'/public')];
    $name='local-proof-'.$nonce.'.json'; Guard::assertFreshProof($proof,$name);
    ppwsExclusive($directory.'/routes-before-'.$nonce.'.bin',file_get_contents(base_path('routes/api.php'))); ppwsExclusive($directory.'/'.$name,$proof);
    echo ppwsJson(['proof'=>$name,'nonce'=>$nonce,'route_path'=>'/api/_local/present-perfect-passive-word-slots-'.$nonce,
        'routes_sha256'=>hash_file('sha256',base_path('routes/api.php')),'database_writes'=>0]); exit;
}

function ppwsSources(): array {
    $map=Quality::mapping('present-perfect'); $oldPath=Guard::privateDirectory().'/builder-before.json';
    if (!is_file($oldPath) || is_link($oldPath) || hash_file('sha256',$oldPath) !== 'ecc5195c70e8f7f59207812a8be7a93e731b968397469a1d7c96eacb8a834733') { ppwsFail('Exact pre-correction bank unavailable.'); }
    $old=json_decode(file_get_contents($oldPath),true,flags:JSON_THROW_ON_ERROR);
    $new=Quality::compile('present-perfect','builder'); $actual=json_decode(file_get_contents(base_path($map['builder']['definition'])),true,flags:JSON_THROW_ON_ERROR);
    if ($actual!==$new || count($new['questions'])!==18 || count($old['questions'])!==18 || $old['seeder']['class']!==$new['seeder']['class']) { ppwsFail('Exact compiled eighteen-row bank differs.'); }
    $before=array_column($old['questions'],null,'uuid'); $specs=[]; $optionPool=[];
    foreach ($new['questions'] as $i=>$row) {
        $previous=$before[$row['uuid']]??null;
        if (!$previous || count($previous['markers'])!==4 || $row['question']!==$row['translation_uk']
            || $previous['question']!==$row['question'].' Використай теперішній доконаний пасив.'
            || $row['type']!==4 || $row['target_text']!==$previous['target_text'] || $row['level']!==$previous['level']
            || $row['accepted_target_texts']!==$previous['accepted_target_texts'] || $row['verb_hint']!==$previous['verb_hint']
            || implode(' ',array_column($row['markers'],'answer'))!==$row['target_text']) { ppwsFail('Source correction exceeds exact prompt/word-slot allowance.'); }
        foreach ($row['markers'] as $marker) {
            if (count(preg_split('/\s+/u',$marker['answer']))!==1 || count($marker['options'])!==4) { ppwsFail('One word per canonical marker required.'); }
            foreach (array_merge($marker['options'],[$marker['verb_hint']]) as $value) { $optionPool[$value]=true; }
        }
        $specs[$row['uuid']]=['id'=>64550+$i,'before'=>$previous,'after'=>$row,'seeder'=>$new['seeder']['class']];
    }
    $paths=[$map['author'],$map['standard']['definition'],$map['builder']['definition'],
        'docs/content/passive-voice-quality/present-perfect.word-slots.previous.v1.json',
        'app/Support/PassiveVoicePracticeQuality.php','app/Support/FinitePracticeQualityCatalog.php','app/Support/FinitePracticeChoiceOptions.php',
        'app/Support/PassivePracticeQuality.php','app/Services/PresentPerfectPassiveWordSlotsLocalGuard.php','app/Services/TheoryLocalReleaseGuard.php',
        'app/Services/M11LocalTargetGuard.php','app/Services/M49LocalTargetGuard.php','app/Services/QuestionExportService.php',
        'tools/diagnostics/inspect-passive-voice-local-target.ps1','tools/diagnostics/present-perfect-passive-word-slots-local.php',
        'resources/views/components/saved-test-js-helpers.blade.php','resources/views/test-modes/card-easy.blade.php',
        'resources/views/test-modes/step-easy.blade.php','public/js/english-answer-variants.js'];
    foreach (glob(base_path('app/Support/*PresentPerfectPassive*'))?:[] as $file) { $paths[]=str_replace('\\','/',substr($file,strlen(base_path())+1)); }
    $hashes=[]; foreach(array_unique($paths) as $path) { if(!is_file(base_path($path))||is_link(base_path($path))) { ppwsFail('Source missing/linked.'); } $hashes[$path]=hash_file('sha256',base_path($path)); }
    ksort($hashes); return ['specs'=>$specs,'optionPool'=>$optionPool,'hashes'=>$hashes,'bank'=>$new['seeder']['class']];
}
function ppwsFocus(array $source): array {
    $pages=ppwsRows(DB::table('pages')->where('seeder',Guard::scope()[0]['identity'])->orderBy('id'));
    if(count($pages)!==1||(int)$pages[0]['id']!==112||(int)$pages[0]['page_category_id']!==18||$pages[0]['slug']!=='theory-passive-voice-present-perfect') { ppwsFail('Exact Page112 differs.'); }
    $questions=ppwsRows(DB::table('questions')->where('seeder',$source['bank'])->orderBy('id'));
    if(count($questions)!==18) { ppwsFail('Exact existing eighteen-question bank count differs.'); }
    $ids=[]; foreach($questions as $row) {
        $spec=$source['specs'][$row['uuid']]??null;
        if(!$spec||(int)$row['id']!==$spec['id']||(string)$row['type']!=='4'||(int)$row['category_id']!==7||(int)$row['source_id']!==499||$row['level']!==$spec['after']['level']) { ppwsFail('Question identity differs.'); } $ids[]=(int)$row['id'];
    }
    $relations=[]; foreach(['question_answers','verb_hints','question_option_question','question_variants','question_hints'] as $table) { $relations[$table]=ppwsRows(DB::table($table)->whereIn('question_id',$ids)->orderBy('id')); }
    $optionIds=array_unique(array_merge(array_column($relations['question_answers'],'option_id'),array_column($relations['verb_hints'],'option_id'),array_column($relations['question_option_question'],'option_id')));
    $options=ppwsRows(DB::table('question_options')->whereIn('id',$optionIds)->orderBy('id'));
    return ['pages'=>$pages,'questions'=>$questions,'targetIds'=>$ids,'relations'=>$relations,'options'=>$options];
}
function ppwsTables(array $source,array $focus,?array $baseline=null): array {
    $pdo=DB::connection()->getPdo(); $tables=$pdo->query('SHOW FULL TABLES WHERE Table_type="BASE TABLE"')->fetchAll(PDO::FETCH_COLUMN); sort($tables);
    if(count($tables)!==46||($baseline!==null&&array_keys($baseline)!==$tables)) { ppwsFail('Exact46-table inventory differs.'); }
    $engines=$pdo->query('SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE="BASE TABLE"')->fetchAll(PDO::FETCH_KEY_PAIR); ksort($engines);
    if(array_keys($engines)!==$tables||array_filter($engines,static fn($e)=>$e!=='InnoDB')!==[]) { ppwsFail('All protected tables must be transactional.'); }
    $ids=array_fill_keys(array_map('strval',$focus['targetIds']),true); $out=[];
    $buffered=$pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY); $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,false);
    try { foreach($tables as $table) {
        $columns=$pdo->query('SHOW COLUMNS FROM '.ppwsIdentifier($table))->fetchAll(PDO::FETCH_COLUMN);
        $keys=$pdo->query('SHOW KEYS FROM '.ppwsIdentifier($table).' WHERE Key_name="PRIMARY"')->fetchAll(PDO::FETCH_ASSOC);
        usort($keys,static fn($a,$b)=>(int)$a['Seq_in_index']<=>(int)$b['Seq_in_index']); $primary=$keys?array_column($keys,'Column_name'):$columns;
        if($baseline!==null&&($baseline[$table]['columns']!==$columns||$baseline[$table]['primary']!==$primary)) { ppwsFail('Schema changed.'); }
        $statement=$pdo->query('SELECT * FROM '.ppwsIdentifier($table).' ORDER BY '.implode(',',array_map(ppwsIdentifier(...),$primary)));
        $full=hash_init('sha256'); $protected=hash_init('sha256'); $count=0; $protectedCount=0; $maxId=0; $newCount=0;
        try { while($row=$statement->fetch(PDO::FETCH_ASSOC)) {
            ++$count; ppwsHashRow($full,$row);
            if($table==='question_options') {
                $maxId=max($maxId,(int)$row['id']);
                if($baseline!==null&&(int)$row['id']>$baseline[$table]['maximum_id']) {
                    if(!isset($source['optionPool'][$row['option']??''])) { ppwsFail('Unapproved new shared option.'); } ++$newCount; continue;
                }
            }
            $masked=Guard::protectedRow($table,$row,$ids); if($masked!==null) { ++$protectedCount; ppwsHashRow($protected,$masked); }
        } } finally { $statement->closeCursor(); }
        $protectedHash=hash_final($protected);
        if($baseline!==null&&($protectedCount!==$baseline[$table]['protected_count']||$protectedHash!==$baseline[$table]['protected_sha256'])) { ppwsFail('Protected row changed: '.$table); }
        $out[$table]=['engine'=>$engines[$table],'columns'=>$columns,'primary'=>$primary,'count'=>$count,'sha256'=>hash_final($full),
            'protected_count'=>$protectedCount,'protected_sha256'=>$protectedHash,'maximum_id'=>$maxId,'approved_new_options'=>$newCount];
    } } finally { $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,$buffered); } return $out;
}
function ppwsCapture(array $source,?array $baseline=null): array { $focus=ppwsFocus($source); return ['focus'=>$focus,'tables'=>ppwsTables($source,$focus,$baseline['tables']??null)]; }
function ppwsRelated(array $focus,string $table,int $id): array { return array_values(array_filter($focus['relations'][$table],static fn($r)=>(int)$r['question_id']===$id)); }
function ppwsMatches(array $focus,array $row,array $expected): bool {
    $id=(int)$row['id']; $options=array_column($focus['options'],'option','id');
    if($row['question']!==$expected['question']||json_decode($row['options_by_marker'],true,flags:JSON_THROW_ON_ERROR)!==$expected['options_by_marker']) { return false; }
    foreach(['question_answers'=>'answer','verb_hints'=>'verb_hint'] as $table=>$field) {
        $found=[]; foreach(ppwsRelated($focus,$table,$id) as $entry) {
            $marker=$entry['marker']; if(isset($found[$marker])||!isset($expected['markers'][$marker])||($table==='verb_hints'&&!in_array($entry['locale'],['','uk'],true))) { return false; }
            $found[$marker]=ppwsOption($options[$entry['option_id']]??'');
        }
        $want=array_map(static fn($m)=>ppwsOption($m[$field]),$expected['markers']); ksort($found);ksort($want); if($found!==$want) { return false; }
    }
    $variants=ppwsRelated($focus,'question_variants',$id); $hints=ppwsRelated($focus,'question_hints',$id);
    if(count($variants)!==1||$variants[0]['text']!==$expected['question']||count($hints)!==1||$hints[0]['provider']!=='compose_prompt'||$hints[0]['locale']!=='uk'||$hints[0]['hint']!==$expected['question']) { return false; }
    $actual=[]; foreach(ppwsRelated($focus,'question_option_question',$id) as $p) { $actual[]=($p['flag']===null?'option':((int)$p['flag']===1?'hint':'foreign')).'|'.ppwsOption($options[$p['option_id']]??''); }
    $want=array_map(static fn($v)=>'option|'.ppwsOption($v),$expected['options']);
    // Live seeder pivots include one flagged hint, intentionally omitted by snapshot options().
    $want[]='hint|'.ppwsOption($expected['verb_hint']);
    $want=array_values(array_unique($want)); sort($actual); sort($want); return $actual===$want;
}
function ppwsStatus(array $state,array $source): string {
    $statuses=[]; foreach($state['focus']['questions'] as $row) {
        $spec=$source['specs'][$row['uuid']]; $statuses[]=ppwsMatches($state['focus'],$row,$spec['after'])?'after':(ppwsMatches($state['focus'],$row,$spec['before'])?'before':'conflict');
    } return count(array_unique($statuses))===1?$statuses[0]:'conflict';
}
function ppwsPlan(array $state,array $source): array {
    $out=[];foreach($state['focus']['questions'] as $row) { $spec=$source['specs'][$row['uuid']];$out[$row['uuid']]=['id'=>(int)$row['id'],'uuid'=>$row['uuid'],'level'=>$row['level'],
        'before_question'=>$spec['before']['question'],'after_question'=>$spec['after']['question'],'before_markers'=>$spec['before']['markers'],'after_markers'=>$spec['after']['markers'],
        'after_options'=>$spec['after']['options'],'after_options_by_marker'=>$spec['after']['options_by_marker']]; }return $out;
}
function ppwsRetainedRelations(array $before,array $after): void {
    foreach(['question_answers','verb_hints'] as $table) {
        $current=array_column($after['focus']['relations'][$table],null,'id');
        foreach($before['focus']['relations'][$table] as $row) { $actual=$current[$row['id']]??null; if(!$actual) { ppwsFail('An old marker relation disappeared.'); } unset($row['option_id'],$actual['option_id']); if($row!==$actual) { ppwsFail('Old relation identity/timestamps changed.'); } }
    }
}

$GLOBALS['ppws_stage']='canonical-source'; $source=ppwsSources();
if($mode==='inspect') {
    if(count($argv)!==2) { ppwsFail('inspect takes no arguments.'); }
    $db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
    try { $state=ppwsCapture($source);$status=ppwsStatus($state,$source);if($status==='conflict') { ppwsFail('Mixed/partial target state refused.'); } } finally { $db->rollBack(); }
    $name=ppwsName('before'); ppwsExclusive($directory.'/'.$name,['created_at'=>gmdate('c'),'scope'=>Guard::scope(),'source_hashes'=>$source['hashes'],'state'=>$state,'status'=>$status]);
    echo ppwsJson(['before'=>$name,'status'=>$status,'targets'=>18,'protected_tables'=>46,'database_writes'=>0]);exit;
}
$beforeName=$argv[2]??'';$before=ppwsRead($directory,$beforeName,'before');
if(($before['scope']??null)!==Guard::scope()||($before['source_hashes']??null)!==$source['hashes']||ppwsStatus($before['state'],$source)==='conflict') { ppwsFail('BEFORE scope/source/state differs.'); }
$plan=ppwsPlan($before['state'],$source);$proofName=$mode==='preview'?($argv[3]??''):($argv[4]??'');
$guard->verify($db,'gramlyze.loc',$directory,$proofName,$server);
if($mode==='preview') {
    if(count($argv)!==4) { ppwsFail('preview requires BEFORE and PROOF basenames.'); }
    $db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
    try { $state=ppwsCapture($source,$before['state']);$status=ppwsStatus($state,$source);if($status==='conflict'||(ppwsDigest($state)!==ppwsDigest($before['state'])&&$status!=='after')) { ppwsFail('Fresh exact BEFORE or complete AFTER required.'); } } finally { $db->rollBack(); }
    $name=ppwsName('preview');ppwsExclusive($directory.'/'.$name,['created_at'=>gmdate('c'),'before_file'=>$beforeName,'before_sha256'=>hash_file('sha256',$directory.'/'.$beforeName),'source_hashes'=>$source['hashes'],'plan'=>$plan]);
    echo ppwsJson(['preview'=>$name,'target_question_updates'=>18,'new_questions'=>0,'native_updates'=>0,'filter_updates'=>0,'already_after'=>$status==='after','database_writes'=>0]);exit;
}
if(count($argv)!==5) { ppwsFail('apply/verify/snapshots require BEFORE, PREVIEW, PROOF basenames.'); }
$previewName=$argv[3];$preview=ppwsRead($directory,$previewName,'preview');
if(($preview['before_file']??null)!==$beforeName||($preview['before_sha256']??null)!==hash_file('sha256',$directory.'/'.$beforeName)||($preview['source_hashes']??null)!==$source['hashes']||($preview['plan']??null)!==$plan) { ppwsFail('Reviewed exact diff/source changed.'); }
if($mode==='apply') {
    $GLOBALS['ppws_stage']='transactional-exact18-apply';$db->statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');$db->beginTransaction();
    try {
        $ids=array_column($plan,'id');DB::table('questions')->whereIn('id',$ids)->orderBy('id')->lockForUpdate()->get();
        foreach(['question_answers','verb_hints','question_variants','question_hints','question_option_question'] as $table) { DB::table($table)->whereIn('question_id',$ids)->orderBy('id')->lockForUpdate()->get(); }
        $state=ppwsCapture($source,$before['state']);$status=ppwsStatus($state,$source);
        if($status==='after') { ppwsRetainedRelations($before['state'],$state);$db->rollBack();echo ppwsJson(['no_op'=>true,'database_writes'=>0,'targets'=>18]);exit; }
        if($status!=='before'||ppwsDigest($state)!==ppwsDigest($before['state'])) { ppwsFail('Exact unmodified BEFORE required.'); }
        $backup=ppwsName('backup');ppwsExclusive($directory.'/'.$backup,['created_at'=>gmdate('c'),'before_file'=>$beforeName,'preview_file'=>$previewName,'source_hashes'=>$source['hashes'],'state'=>$state,'plan'=>$plan]);
        foreach($plan as $patch) {
            $id=$patch['id'];$optionIds=[];$hint=reset($patch['after_markers'])['verb_hint'];
            foreach(array_merge($patch['after_options'],[$hint]) as $value) { $optionIds[$value]=QuestionOption::firstOrCreate(['option'=>$value])->id; }
            DB::table('questions')->where('id',$id)->update(['question'=>$patch['after_question'],'options_by_marker'=>json_encode($patch['after_options_by_marker'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)]);
            foreach(['question_answers'=>'answer','verb_hints'=>'verb_hint'] as $table=>$field) {
                $existing=array_column(ppwsRelated($state['focus'],$table,$id),null,'marker');$template=reset($existing);
                foreach($patch['after_markers'] as $marker=>$definition) {
                    if(isset($existing[$marker])) { DB::table($table)->where('id',$existing[$marker]['id'])->where('question_id',$id)->update(['option_id'=>$optionIds[$definition[$field]]]); }
                    else { $entry=$template;unset($entry['id']);$entry['marker']=$marker;$entry['option_id']=$optionIds[$definition[$field]];DB::table($table)->insert($entry); }
                }
            }
            DB::table('question_variants')->where('question_id',$id)->update(['text'=>$patch['after_question']]);
            DB::table('question_hints')->where('question_id',$id)->where('provider','compose_prompt')->where('locale','uk')->update(['hint'=>$patch['after_question']]);
            DB::table('question_option_question')->where('question_id',$id)->delete();$seen=[];
            foreach($patch['after_options'] as $value) { $option=$optionIds[$value];if(isset($seen[$option])) {continue;}$seen[$option]=true;DB::table('question_option_question')->insert(['question_id'=>$id,'option_id'=>$option,'flag'=>null]); }
            DB::table('question_option_question')->insert(['question_id'=>$id,'option_id'=>$optionIds[$hint],'flag'=>1]);
        }
        $after=ppwsCapture($source,$before['state']);ppwsRetainedRelations($before['state'],$after);
        if(ppwsStatus($after,$source)!=='after'||$source['hashes']!==ppwsSources()['hashes']) { ppwsFail('Exact source/postconditions failed.'); }
        $db->commit();$receipt=ppwsName('applied');ppwsExclusive($directory.'/'.$receipt,['created_at'=>gmdate('c'),'before_file'=>$beforeName,'preview_file'=>$previewName,'backup'=>$backup,'source_hashes'=>$source['hashes'],'ids'=>$ids,'protected_tables'=>46,'old_relation_ids_timestamps_preserved'=>true,'new_questions'=>0]);
        echo ppwsJson(['applied'=>true,'receipt'=>$receipt,'backup'=>$backup,'target_question_updates'=>18,'new_questions'=>0,'protected_tables'=>46]);exit;
    } catch(Throwable $e) { if($db->transactionLevel()) {$db->rollBack();}throw $e; }
}
$GLOBALS['ppws_stage']='read-only-after-verification';$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try {
    $state=ppwsCapture($source,$before['state']);ppwsRetainedRelations($before['state'],$state);if(ppwsStatus($state,$source)!=='after') { ppwsFail('Complete eighteen-row AFTER unavailable.'); }
    if($mode==='snapshots') {
        $snapshotDirectory=config('questions.export_path',database_path('seeders/questions'));
        if(is_link($snapshotDirectory)||strcasecmp(str_replace('\\','/',(string)realpath($snapshotDirectory)),Guard::ROOT.'/database/seeders/questions')!==0) { ppwsFail('Canonical snapshot directory differs.'); }
        $names=array_fill_keys(array_map(static fn($u)=>$u.'.json',array_keys($plan)),true);$outside=[];$backupFiles=[];
        foreach(glob($snapshotDirectory.'/*.json')?:[] as $file) { if(is_link($file)){ppwsFail('Linked snapshot.');}if(isset($names[basename($file)])){$backupFiles[basename($file)]=file_get_contents($file);}else{$outside[basename($file)]=hash_file('sha256',$file);} }
        if(count($backupFiles)!==18){ppwsFail('All eighteen previous snapshots required.');}$snapshotBackup=ppwsName('snapshot-backup');ppwsExclusive($directory.'/'.$snapshotBackup,$backupFiles);$files=[];
        foreach(Question::query()->whereIn('id',array_column($plan,'id'))->orderBy('id')->get() as $question) {
            if(!isset($plan[$question->uuid])||$question->seeder!==$source['bank']) { ppwsFail('Snapshot model binding differs.'); }
            app(QuestionExportService::class)->export($question);$path=$snapshotDirectory.'/'.$question->uuid.'.json';$files[basename($path)]=hash_file('sha256',$path);
        }
        $outsideAfter=[];foreach(glob($snapshotDirectory.'/*.json')?:[] as $file){if(!isset($names[basename($file)])){$outsideAfter[basename($file)]=hash_file('sha256',$file);}}
        if(count($files)!==18||$outside!==$outsideAfter||ppwsDigest(ppwsCapture($source,$before['state']))!==ppwsDigest($state)){ppwsFail('Snapshot export changed outside files/DB.');}
        $snapshotReceipt=['count'=>18,'files'=>$files,'backup'=>$snapshotBackup,'outside_snapshots_unchanged'=>true];
    }
} finally {$db->rollBack();}
$receipt=ppwsName($mode==='snapshots'?'snapshots':'verified');ppwsExclusive($directory.'/'.$receipt,['created_at'=>gmdate('c'),'before_file'=>$beforeName,'preview_file'=>$previewName,'source_hashes'=>$source['hashes'],'targets'=>18,'protected_tables'=>46,'database_writes'=>0,'snapshots'=>$snapshotReceipt??null]);
echo ppwsJson(['verified'=>true,'receipt'=>$receipt,'targets'=>18,'protected_tables'=>46,'snapshots_exported'=>$snapshotReceipt['count']??0,'database_writes'=>0]);
