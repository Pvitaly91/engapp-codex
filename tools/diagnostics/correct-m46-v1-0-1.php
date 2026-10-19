<?php
/** Explicit single-field editorial revision 1.0.0 -> 1.0.1, with fresh local proof and exclusive backup. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array($argv[1] ?? '', ['preview','apply'], true) || count($argv)!==3) { exit(1); }
$source=dirname(__DIR__,2);$root='D:/DEV/htdocs/gramlyze.loc';require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Support\M46ConditionalsPackage as Package;
use App\Services\M46LocalTargetGuard as Guard;
use Illuminate\Support\Facades\DB;
$db=DB::connection();Guard::assertConfiguredDatabase($db);$dir=Guard::privateDirectory();
$proof=$argv[2];
$physical=(new App\Services\PronounContentRepair($db,$root.'/database',$dir))->connection('gr2',$argv[1]==='apply');
(new Guard)->verify($db,'gramlyze.loc',$dir,$proof,$physical);
$oldPath='database/content-patches/m46-conditionals.v1.json';$newPath='database/content-patches/m46-conditionals.v1.0.1.json';
$oldSha='e3b7bf9bcbc0d5293f000d922010f0022d3f182d5b13113f769dcbccfc2648d5';$newSha='22f6d02e2bedf737a02ced86a6f8af3c75880cdc893d0d20688c4b64e4047c56';
foreach([$oldPath=>$oldSha,$newPath=>$newSha] as $p=>$sha){foreach([$source,$root] as $base){if(hash_file('sha256',$base.'/'.$p)!==$sha){throw new RuntimeException('M46 correction source differs.');}}}
$old=json_decode(file_get_contents($source.'/'.$oldPath),true);$new=json_decode(file_get_contents($source.'/'.$newPath),true);
$before=$old['targets'][1]['after']['page']['blocks'][2]['body'];$after=$new['targets'][1]['after']['page']['blocks'][2]['body'];
$a=json_decode($before,true);$b=json_decode($after,true);$a['sections'][2]['note']=str_replace('give — вказівка','tell — вказівка',$a['sections'][2]['note']);
if($a!==$b||$before===$after){throw new RuntimeException('M46 correction must change only the named imperative note.');}
$expected=$old;$expected['targets'][1]['after']['page']['blocks'][2]['body']=$after;$expected['revision']='1.0.1';
if($expected!==$new){throw new RuntimeException('M46 correction has extra source changes.');}
$identity=Package::OWNERS[1];$uuid=App\Support\M26DetailPackage::uuid($identity,$old['targets'][1]['after']['page']['blocks'][2],3);
$select=static fn()=>DB::table('text_blocks')->where('uuid',$uuid)->where('seeder',$identity)->where('locale','uk');
$row=(array)$select()->first();
if(!$row||!in_array($row['body'],[$before,$after],true)||$row['type']!=='usage-panels'||(int)$row['sort_order']!==3||(int)$row['page_id']!==44){throw new RuntimeException('M46 correction target conflict.');}
$hashes=static function(int $id) use($db):array {
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);$out=[];
    foreach($tables as $table){$columns=$db->getSchemaBuilder()->getColumnListing($table);$q=$db->table($table);
        foreach(in_array('id',$columns,true)?['id']:$columns as $c){$q->orderBy($c);}if($table==='text_blocks')$q->where('id','!=',$id);
        $h=hash_init('sha256');$count=0;foreach($q->cursor() as $r){hash_update($h,App\Services\M26ContentPatch::digest((array)$r)."\n");$count++;}
        $out[$table]=['count'=>$count,'sha256'=>hash_final($h)];}
    return $out;
};
$exclusive=static function(string $file,array $data):void{
    $bytes=json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
    $h=fopen($file,'x');if(!$h)throw new RuntimeException('M46 correction evidence exists; no overwrite.');
    try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h))throw new RuntimeException('Incomplete M46 correction evidence.');}finally{fclose($h);}
};
$path=$dir.'/editorial-preview-v1-0-1.json';
if($argv[1]==='preview'){
    $plan=['at'=>gmdate('c'),'row'=>$row,'before'=>$before,'after'=>$after,'source_sha256'=>[$oldPath=>$oldSha,$newPath=>$newSha],
        'protected'=>$hashes($row['id'])];
    $exclusive($path,$plan);echo json_encode(['status'=>'reviewed-preview','updated'=>$row['body']===$before?1:0,'inserted'=>0,'deleted'=>0,'id'=>$row['id'],'uuid'=>$uuid,'field'=>'body'],JSON_PRETTY_PRINT)."\n";exit(0);
}
$plan=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
$result=$db->transaction(function() use($db,$select,$row,$plan,$before,$after,$hashes,$exclusive,$dir):array{
    $now=(array)$select()->lockForUpdate()->first();
    if($now['body']===$after){$expected=$plan['row'];$expected['body']=$after;
        if($now!==$expected||$hashes($now['id'])!==$plan['protected'])throw new RuntimeException('M46 correction no-op postcondition differs.');
        return ['status'=>'no-op','updated'=>0,'inserted'=>0,'deleted'=>0];}
    if($now!==$plan['row']||$now['body']!==$before||$hashes($now['id'])!==$plan['protected'])throw new RuntimeException('M46 correction preview stale.');
    $exclusive($dir.'/editorial-backup-v1-0-1.json',$plan);
    if($select()->where('id',$now['id'])->where('body',$before)->update(['body'=>$after])!==1)throw new RuntimeException('M46 correction concurrent change.');
    $expected=$now;$expected['body']=$after;
    if((array)$select()->first()!==$expected||$hashes($now['id'])!==$plan['protected'])throw new RuntimeException('M46 correction postcondition failed; rollback.');
    return ['status'=>'applied','updated'=>1,'inserted'=>0,'deleted'=>0];
});
echo json_encode($result,JSON_PRETTY_PRINT)."\n";
