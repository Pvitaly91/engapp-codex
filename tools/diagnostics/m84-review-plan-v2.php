<?php
/** Pure exact-diff review of a fresh guarded preview. No bootstrap, HTTP or SQL. */
if(PHP_SAPI!=='cli'||count($argv)!==1){throw new RuntimeException('M84 exact review takes no arguments.');}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m84-local';require $root.'/vendor/autoload.php';
$load=static function($name,$sha)use($dir){$path=$dir.'/'.$name;if(is_link($path)||!is_file($path)||!hash_equals($sha,hash_file('sha256',$path))){throw new RuntimeException('M84 reviewed input bytes drift.');}return json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);};
$before=$load('before-v1.json',App\Services\M84ContentPatch::BEFORE_SHA);
$path=$dir.'/preview-v2.json';if(is_link($path)||!is_file($path)){throw new RuntimeException('M84 preview absent.');}
$preview=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$plan=$preview;unset($plan['sha256']);
$digest=App\Support\M26DetailPackage::digest(...);
if(($preview['sha256']??null)!=='3e1d48d3d8a0b32e10524ebe1bfa9a8bc02675d371cc0bce8d4b76842557bf30'||$digest($plan)!==$preview['sha256']||$preview['state']!=='before'||($preview['deletes']??[])!==[]){throw new RuntimeException('M84 exact reviewed preview digest/state differs.');}
[$sourceBefore,$patch,$master,$contract]=App\Support\M84WordOrderHaveGot::load($root);App\Support\M84WordOrderHaveGot::validate($sourceBefore,$patch,$master,$contract);
$expected=[];$summary=[];
foreach($patch['targets'] as $i=>$target){
 $owner=$target['identity'];$captured=$before['pages'][$owner];
 foreach(['page','category_ancestry','blocks']as $field){if($preview['pages'][$owner][$field]!==$captured[$field]){throw new RuntimeException('M84 independent original inventory differs.');}}
 $old=$captured['page'];$after=$target['after']['page']['subtitle_text'];
 if($old['text']!==$after){$expected['pages:'.$old['id']]=['table'=>'pages','id'=>$old['id'],'uuid'=>$old['uuid']??null,'seeder'=>$owner,'before'=>['text'=>$old['text']],'after'=>['text'=>$after]];}
 foreach($captured['blocks']as $old){
  if($old['locale']!=='uk'){continue;}$pos=$old['sort_order'];$body=$pos===0?$target['after']['page']['subtitle_html']:$target['after']['page']['blocks'][$pos-1]['body'];
  if($body!==$old['body']){$expected['text_blocks:'.$old['id']]=['table'=>'text_blocks','id'=>$old['id'],'uuid'=>$old['uuid'],'seeder'=>$owner,'before'=>['body'=>$old['body']],'after'=>['body'=>$body]];}
 }
 $summary[]=['owner'=>$owner,'page_id'=>$captured['page']['id'],'continuing_uk'=>App\Support\M84WordOrderHaveGot::CONTINUING_COUNTS[$i]+1,'after_blocks'=>count($target['after']['page']['blocks']),'own_graded'=>$i<5?10:6,'demos'=>$i<5?1:0,'full_inputs'=>$i<5?3:2,'widget'=>5];
}
$seen=[];foreach($preview['updates']as $change){$key=$change['table'].':'.$change['id'];$want=$expected[$key]??null;
 if($want===null||isset($seen[$key])||array_diff_key($change,['before_sha256'=>true,'after_sha256'=>true])!==$want||$change['before_sha256']!==$digest($want['before'])||$change['after_sha256']!==$digest($want['after'])){throw new RuntimeException('M84 an update differs from independently derived exact data.');}$seen[$key]=true;}
if(count($seen)!==70||count($expected)!==70||count($preview['inserts'])!==1){throw new RuntimeException('M84 exact reviewed70/1 scope differs.');}
$insert=$preview['inserts'][0];$owner=App\Support\M84WordOrderHaveGot::OWNERS[5];$config=$patch['targets'][5]['after']['page']['blocks'][5];$uuid=App\Support\M26DetailPackage::uuid($owner,$config,6);
$fields=['uuid'=>$uuid,'page_id'=>201,'page_category_id'=>2,'locale'=>'uk','type'=>'practice-set','column'=>'left','heading'=>$config['heading']??null,'css_class'=>$config['css_class']??null,'sort_order'=>6,'body'=>$config['body'],'level'=>null,'seeder'=>$owner];
if($insert['table']!=='text_blocks'||$insert['seeder']!==$owner||$insert['fields']!==$fields){throw new RuntimeException('M84 exact single new Have got practice differs.');}
$qplan=$load(App\Services\M84ContentPatch::QUESTION_PLAN_FILE,App\Services\M84ContentPatch::QUESTION_PLAN_SHA);
if($preview['question_supplement']['expected']!==$qplan['materialized']||$preview['question_supplement']['projection']!==$qplan['projection']||$preview['question_supplement']['actual']!==array_fill_keys(array_keys(App\Support\M51QuestionSupplement::ROW_COLUMNS),[])){throw new RuntimeException('M84 exact complete question family projection differs.');}
if(array_map('count',$qplan['materialized'])!==['questions'=>25,'question_options'=>51,'question_answers'=>99,'question_option_question'=>169,'question_theory_text_blocks'=>25]){throw new RuntimeException('M84 exact369-row question-family inventory differs.');}
$result=['preview_file'=>'preview-v2.json','preview_raw_sha256'=>hash_file('sha256',$path),'reviewed_digest'=>$preview['sha256'],'independent_before_sha256'=>App\Services\M84ContentPatch::BEFORE_SHA,'update_operations'=>70,'page_text_updates'=>6,'continuing_body_updates'=>64,'new_practice'=>$fields['uuid'],'inserted_question_family_rows'=>array_map('count',$qplan['materialized']),'owners'=>$summary,'approved_fields'=>['pages'=>['text'],'text_blocks'=>['body']],'metadata_changes'=>[],'old_question_family_updates'=>0,'deletes'=>0,'db_connections'=>0,'db_writes'=>0,'http_requests'=>0];
$bytes=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";$out=$dir.'/exact-diff-review-v2.json';
if(file_exists($out)||is_link($out)){throw new RuntimeException('M84 exact review receipt already exists.');}$h=fopen($out,'x');if(!$h){throw new RuntimeException('M84 exact review cannot be exclusive.');}try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M84 exact review incomplete.');}}finally{fclose($h);}
echo $bytes;echo json_encode(['receipt_sha256'=>hash('sha256',$bytes)],JSON_THROW_ON_ERROR)."\n";
