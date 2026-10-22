<?php

/** Fresh SELECT-only comparison with the immutable M78 BEFORE; never refreshes that evidence. */
$failure=static function(Throwable $e):void {
    $message=$e instanceof RuntimeException && !$e instanceof PDOException
        && str_starts_with($e->getMessage(),'M78 freshness: ')
        ? $e->getMessage() : 'M78 freshness: check incomplete; no receipt.';
    fwrite(STDERR,$message." No SQL writes.\n");exit(1);
};
set_exception_handler($failure);
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==2
    ||preg_match('/^before-freshness-v[1-9][0-9]*\.json$/D',$argv[1])!==1) {
    throw new RuntimeException('M78 freshness: one exclusive receipt basename is required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m78-local';$receiptName=$argv[1];
$normal=static fn(string $path):string=>strtolower(str_replace('\\','/',(string)realpath($path)));
if($normal(dirname(__DIR__,2))!==$normal($root)||!is_dir($dir)||is_link($dir)
    ||file_exists($dir.'/'.$receiptName)||is_link($dir.'/'.$receiptName)) {
    throw new RuntimeException('M78 freshness: actual served/private scope differs or receipt already exists.');
}
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler($failure);

use App\Services\M26ContentPatch as Digest;
use App\Services\M51ContentPatch;
use App\Services\M78ContentPatch as Patch;
use App\Services\M78LocalTargetGuard as Guard;
use App\Support\M78PhrasalQuestionBasics as Package;

$beforeSha='327424de32f1cebaad86adef078d25aa1d4747b257894be3fdadc6706c630422';
$consumersSha='073f1da1354c6227146a920ff86997dcfccb35b25b1f9068da2aa290012dada3';
if(Patch::BEFORE_FILE!=='before-v1.json'||Patch::BEFORE_SHA!==$beforeSha
    ||Patch::CONSUMERS_FILE!=='before-old-consumers-v1.json'||Patch::CONSUMERS_SHA!==$consumersSha) {
    throw new RuntimeException('M78 freshness: immutable evidence policy differs.');
}
$hash=static function(string $path):string {
    if(!is_file($path)||is_link($path)) { throw new RuntimeException('M78 freshness: evidence is absent or linked.'); }
    return hash_file('sha256',$path);
};
$pins=[__FILE__=>$hash(__FILE__)];
$read=static function(string $name,string $sha)use($dir,$hash,&$pins):array {
    $path=$dir.'/'.$name;
    if(!hash_equals($sha,$hash($path))) { throw new RuntimeException('M78 freshness: immutable evidence bytes differ.'); }
    $pins[$path]=$sha;
    return json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
};
$before=$read(Patch::BEFORE_FILE,$beforeSha);$consumers=$read(Patch::CONSUMERS_FILE,$consumersSha);
if(($before['base_sha']??null)!==Package::BASE_SHA||($before['served_root']??null)!==$root
    ||($before['source_worktree']??null)!==Patch::SOURCE_WORKTREE
    ||array_keys($before['pages']??[])!==Package::OWNERS
    ||count($before['sql_before']??[])!==46||($before['sql_before']??null)!==($before['sql_after_http']??null)
    ||count($before['schema']??[])!==11
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']
    ||($consumers['all_tables_after_http']??null)!==$before['sql_before']
    ||($consumers['unchanged_during_http']??null)!==true) {
    throw new RuntimeException('M78 freshness: original BEFORE/consumer scope differs.');
}
M51ContentPatch::assertConsumerCapture($before,$consumers);

// The current active definitions are already AFTER. Validate the frozen package,
// not an invented equality between those current sources and the old SQL state.
[$sourceBefore,$patch,$master,$contract]=Package::load($root);
Package::validate($sourceBefore,$patch,$master,$contract);
foreach(Package::profile()['artifacts'] as $path=>$sha) {
    if(!hash_equals($sha,$hash($root.'/'.$path))) { throw new RuntimeException('M78 freshness: frozen package bytes differ.'); }
    $pins[$root.'/'.$path]=$sha;
}

$db=Illuminate\Support\Facades\DB::connection();Guard::assertConfiguredDatabase($db);
$guard=new Guard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $key) {
    if(!is_string($server[$key]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$key]))!==1) {
        throw new RuntimeException('M78 freshness: physical MySQL path is not absolute local Windows.');
    }
}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);
$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306
    ||!str_starts_with($server['os']??'','Win')||!ctype_digit($pid)
    ||$dataPath===''||$pidPath===''||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')) {
    throw new RuntimeException('M78 freshness: fresh local MySQL identity differs.');
}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);
$identity=Guard::publicIdentity($db,$root.'/public');
if($identity!==$before['identity']) { throw new RuntimeException('M78 freshness: actual local runtime differs.'); }

$expectedTables=array_keys($before['sql_before']);$sortedExpected=$expectedTables;sort($sortedExpected);
if($expectedTables!==$sortedExpected) { throw new RuntimeException('M78 freshness: BEFORE table order differs.'); }
foreach($expectedTables as $table) {
    if(preg_match('/^[a-zA-Z0-9_]+$/D',$table)!==1
        ||array_keys($before['sql_before'][$table])!==['count','sha256']
        ||!is_int($before['sql_before'][$table]['count'])
        ||preg_match('/^[a-f0-9]{64}$/D',$before['sql_before'][$table]['sha256'])!==1) {
        throw new RuntimeException('M78 freshness: original table fingerprint shape differs.');
    }
}
if(array_diff(array_keys($before['schema']),$expectedTables)!==[]) {
    throw new RuntimeException('M78 freshness: captured schema scope differs.');
}

$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try {
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);
    if($tables!==$expectedTables) {
        $different=array_values(array_unique([...array_diff($expectedTables,$tables),...array_diff($tables,$expectedTables)]));
        $table=$different[0]??'inventory';
        if(preg_match('/^[a-zA-Z0-9_]+$/D',$table)!==1) { $table='inventory'; }
        throw new RuntimeException('M78 freshness: stale table '.$table.'.');
    }
    $current=[];$schemaChecked=[];
    foreach($tables as $table) {
        $schema=$db->getSchemaBuilder()->getColumns($table);
        if(array_key_exists($table,$before['schema'])) {
            if($schema!==$before['schema'][$table]) { throw new RuntimeException('M78 freshness: stale schema '.$table.'.'); }
            $schemaChecked[]=$table;
        }
        $columns=array_column($schema,'name');$query=$db->table($table);
        foreach(in_array('id',$columns,true)?['id']:$columns as $column) { $query->orderBy($column); }
        $hashState=hash_init('sha256');$count=0;
        foreach($query->cursor() as $record) { hash_update($hashState,Digest::digest((array)$record)."\n");++$count; }
        $current[$table]=['count'=>$count,'sha256'=>hash_final($hashState)];
        if($current[$table]!==$before['sql_before'][$table]) {
            throw new RuntimeException('M78 freshness: stale table '.$table.'.');
        }
    }
    if(count($schemaChecked)!==11||$current!==$before['sql_before']) {
        throw new RuntimeException('M78 freshness: complete BEFORE comparison differs.');
    }
} finally { $db->rollBack(); }
foreach($pins as $path=>$sha) {
    if(!hash_equals($sha,$hash($path))) { throw new RuntimeException('M78 freshness: evidence changed during check.'); }
}

$result=[
    'schema_version'=>1,'status'=>'exact-original-before-still-current','checked_at'=>gmdate('c'),
    'independent_before_file'=>Patch::BEFORE_FILE,'independent_before_sha256'=>$beforeSha,
    'old_consumers_file'=>Patch::CONSUMERS_FILE,'old_consumers_sha256'=>$consumersSha,
    'diagnostic_sha256'=>$pins[__FILE__],'frozen_package_valid'=>true,
    'local_physical_evidence_verified'=>true,'runtime_matches_before'=>true,
    'runtime_identity_sha256'=>Digest::digest($identity),'table_inventory_exact'=>true,
    'sql_tables'=>count($current),'sql_fingerprints'=>$current,
    'all_table_rows_and_timestamps_exact'=>true,'target_pages_blocks_and_relations_covered_by_all_tables'=>true,
    'captured_schema_tables_checked'=>count($schemaChecked),'captured_schema_table_names'=>$schemaChecked,
    'uncaptured_schema_table_names'=>array_values(array_diff($expectedTables,$schemaChecked)),
    'schema_coverage_note'=>'The immutable BEFORE captured eleven schemas, not all forty-six. Only those eleven historical schema arrays are asserted equal; every table name and full-row fingerprint is exact.',
    'original_before_replaced'=>false,'db_writes'=>0,'http_requests'=>0,
];
$bytes=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$handle=fopen($dir.'/'.$receiptName,'x');
if(!$handle) { throw new RuntimeException('M78 freshness: exclusive receipt unavailable.'); }
try {
    if(fwrite($handle,$bytes)!==strlen($bytes)||!fflush($handle)) { throw new RuntimeException('M78 freshness: receipt incomplete.'); }
} finally { fclose($handle); }
echo json_encode(['file'=>$receiptName,'sha256'=>hash('sha256',$bytes),'status'=>$result['status'],
    'sql_tables'=>count($current),'captured_schema_tables_checked'=>count($schemaChecked),
    'db_writes'=>0,'http_requests'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";

