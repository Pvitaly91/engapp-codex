<?php

/** Pure materialization review; exclusive private plan only, no application/SQL/HTTP. */
if(PHP_SAPI!=='cli'||count($argv)!==1){throw new RuntimeException('M75 fixed pure question plan takes no arguments.');}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m75-local';
require $root.'/vendor/autoload.php';require_once $wt.'/app/Support/M75PronounForms.php';
require_once $wt.'/app/Support/M75QuestionSupplement.php';
use App\Support\M75QuestionSupplement as Supplement;
use App\Support\M51QuestionSupplement as Rows;
$name='question-inventory-v1.json';$sha='5c1d4ee1eb53dfbf07c69b669b2bb5f824ee8e31f6b0d8a7a6ef6c4327845900';
if(is_link($dir)||!is_dir($dir)||is_link($dir.'/'.$name)||!is_file($dir.'/'.$name)||!hash_equals($sha,hash_file('sha256',$dir.'/'.$name))){
    throw new RuntimeException('M75 fixed independent inventory bytes differ.');
}
$i=json_decode(file_get_contents($dir.'/'.$name),true,flags:JSON_THROW_ON_ERROR);
$s=Supplement::load($wt);$p=Supplement::project($s,Supplement::binding(),$i['option_matches']);
$rows=Rows::materialize($p,$i['ids'],$i['row_timestamp']);
if($p!==$i['projection']||$rows!==$i['materialized']||$i['source_sha256']!==Supplement::SOURCE_SHA
    ||$i['base_sha']!==App\Support\M75PronounForms::BASE_SHA||$i['db_writes']!==0||$i['http_requests']!==0
    ||array_map('count',$rows)!==['questions'=>2,'question_options'=>0,'question_answers'=>10,'question_option_question'=>16,'question_theory_text_blocks'=>2]){
    throw new RuntimeException('M75 two-family pure projection differs from independent inventory.');
}
$out=['schema_version'=>1,'kind'=>'M75_question_insert_plan','base_sha'=>$i['base_sha'],
    'before_sha256'=>$i['before_sha256'],'source_sha256'=>Supplement::SOURCE_SHA,
    'inventory'=>['file'=>$name,'sha256'=>$sha],'binding'=>$i['binding'],'projection'=>$p,
    'ids'=>$i['ids'],'row_timestamp'=>$i['row_timestamp'],'materialized'=>$rows,'taxonomy'=>$i['taxonomy']];
$bytes=json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$file=$dir.'/question-plan-v1.json';
if(file_exists($file)||is_link($file)){throw new RuntimeException('M75 private question plan already exists.');}
$h=fopen($file,'x');if(!$h){throw new RuntimeException('M75 exclusive plan creation failed.');}
try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M75 private plan incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>'question-plan-v1.json','sha256'=>hash('sha256',$bytes),'new_rows'=>array_sum(array_map('count',$rows)),
    'db_connections'=>0,'db_writes'=>0,'http_requests'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
