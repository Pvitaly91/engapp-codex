<?php
/** Explicit SELECT-only schema capture, fresh before M85 apply. */
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==1){throw new RuntimeException('M85 schema BEFORE has no arguments.');}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m85-local';
require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$file=$dir.'/before-v1.json';$pin='fe639c06446c6fb46b5b4ab25ae654e3910b6733cc124a9e4bee262e41b411b7';
if(is_link($file)||!hash_equals($pin,hash_file('sha256',$file))){throw new RuntimeException('M85 independent BEFORE drift.');}
$before=json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);$db=Illuminate\Support\Facades\DB::connection();
App\Services\M49LocalTargetGuard::assertConfiguredDatabase($db);
$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try{
 $identity=App\Services\M49LocalTargetGuard::publicIdentity($db,$root.'/public');
 if($identity!==$before['identity']){throw new RuntimeException('M85 schema identity differs.');}
 $schema=[];$hashes=[];
 foreach($before['sql_before'] as $table=>$expected){
  $schema[$table]=$db->getSchemaBuilder()->getColumns($table);
  $columns=$db->getSchemaBuilder()->getColumnListing($table);$query=$db->table($table);
  foreach(in_array('id',$columns,true)?['id']:$columns as $column){$query->orderBy($column);}
  $hash=hash_init('sha256');$count=0;
  foreach($query->cursor() as $record){hash_update($hash,App\Support\M26DetailPackage::digest((array)$record)."\n");++$count;}
  $hashes[$table]=['count'=>$count,'sha256'=>hash_final($hash)];
 }
 if($hashes!==$before['sql_before']||count($schema)!==46){throw new RuntimeException('M85 all-table rows drifted before schema capture.');}
}finally{$db->rollBack();}
$out=['kind'=>'M85 fresh SELECT-only schema BEFORE','base_sha'=>'6f7751612a3bc9d8bec2315c052e81f9e2fc0973','before_sha256'=>$pin,'identity'=>$identity,'schema'=>$schema,'sql_before'=>$hashes,'db_writes'=>0,'http_requests'=>0];
$json=json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$path=$dir.'/schema-before-v1.json';if(is_link($path)||file_exists($path)){throw new RuntimeException('M85 schema capture already exists.');}
$h=fopen($path,'x');if(!$h){throw new RuntimeException('M85 schema capture unavailable.');}try{if(fwrite($h,$json)!==strlen($json)||!fflush($h)){throw new RuntimeException('M85 schema capture incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>basename($path),'sha256'=>hash('sha256',$json),'tables'=>count($schema),'db_writes'=>0],JSON_THROW_ON_ERROR)."\n";
