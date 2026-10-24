<?php

/** Pure independent exact review of one hash-bound guarded preview; no app/DB/HTTP. */
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==3
 ||preg_match('/^preview-v([1-9][0-9]*)\.json$/D',$argv[1],$version)!==1
 ||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1){throw new RuntimeException('M86 review requires private preview basename and explicitly reviewed logical digest.');}
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m86-local';
$normal=static fn($p)=>rtrim(strtolower(str_replace('\\','/',(string)realpath($p))),'/');
if($normal(dirname(__DIR__,2))!==$normal($root)){throw new RuntimeException('M86 pure review must run from served ROOT.');}
require $root.'/vendor/autoload.php';
use App\Support\M86PresentSimpleContinuousBasics as Package;
use App\Services\M86ContentPatch as Service;
use App\Support\M26DetailPackage;
$digest=M26DetailPackage::digest(...);
$load=static function(string $path,?string $sha=null):array{if(is_link($path)||!is_file($path)||($sha!==null&&!hash_equals($sha,hash_file('sha256',$path)))){throw new RuntimeException('M86 reviewed input absent/linked/drifted.');}return json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);};
$before=$load($dir.'/before-v1.json',Service::BEFORE_SHA);$path=$dir.'/'.$argv[1];$rawSha=hash_file('sha256',$path);
$preview=$load($path);$plan=$preview;unset($plan['sha256']);
if(($preview['sha256']??null)!==$argv[2]||$digest($plan)!==$argv[2]||($preview['state']??null)!=='before'
 ||($preview['patch']??null)!=='m86-present-simple-continuous-basics-standard-view-v1'||($preview['version']??null)!==1
 ||($preview['names']??null)!==Package::OWNERS||array_keys($preview['pages']??[])!==Package::OWNERS
 ||($preview['deletes']??[])!==[]||array_key_exists('question_supplement',$preview)){
 throw new RuntimeException('M86 exact reviewed preview digest/state/six-owner/no-question scope differs.');
}
if(($preview['connection']['driver']??null)!=='mysql'||($preview['connection']['database']??null)!=='gr2'||($preview['connection']['port']??null)!==3306){throw new RuntimeException('M86 reviewed preview is not the exact local MySQL target.');}
[$sourceBefore,$patch,$master,$contract]=Package::load($root);Package::validate($sourceBefore,$patch,$master,$contract);
$sources=array_values(array_unique([...Service::sourcePaths($patch,$contract,$root),'private:'.Service::BEFORE_FILE,'private:'.Service::CONSUMERS_FILE,'private:'.Service::SYNC_RECEIPT]));sort($sources);$actualSources=array_keys($preview['sources']);sort($actualSources);
if($actualSources!==$sources){throw new RuntimeException('M86 reviewed source/evidence path inventory differs.');}
foreach($preview['sources'] as $source=>$sha){$p=str_starts_with($source,'private:')?$dir.'/'.substr($source,8):$root.'/'.$source;if(is_link($p)||!is_file($p)||!hash_equals($sha,hash_file('sha256',$p))){throw new RuntimeException('M86 reviewed source/evidence bytes drifted: '.$source);}}
if(array_keys($preview['protected']??[])!==array_keys($before['sql_before'])||count($preview['protected'])!==46){throw new RuntimeException('M86 protected full-table inventory differs.');}
foreach($before['sql_before'] as $table=>$fp){if(!in_array($table,['pages','text_blocks'],true)&&$preview['protected'][$table]!==$fp){throw new RuntimeException('M86 non-content table differs from fresh BEFORE: '.$table);}}
// SQL row ordering differs between the independent UUID-ordered capture and
// the native ID-ordered plan. Compare every row field with multiplicity; marker,
// position and pivot values themselves are never normalized or omitted.
$rowSet=static function(array $rows)use($digest):array{$out=[];foreach($rows as $row){$key=$digest($row);if(isset($out[$key])){++$out[$key]['multiplicity'];}else{$out[$key]=['row'=>$row,'multiplicity'=>1];}}ksort($out);return $out;};
$familyMap=['answers'=>'question_answers','option_pivots'=>'question_option_question','verb_hints'=>'verb_hints','hints'=>'question_hints','variants'=>'question_variants','tags'=>'question_tag','marker_tags'=>'question_marker_tag'];
$expected=[];$expectedInserts=[];$owners=[];$operations=[];$allOldUuids=[];$seenOldUuids=[];$retainedPractices=[];$pageQuestions=[];$banks=[];$ukCount=0;$nonUk=0;$bodyKinds=['subtitle'=>0,'teaching'=>0];
foreach($before['pages'] as $capture){foreach($capture['blocks'] as $row){if(isset($allOldUuids[$row['uuid']])){throw new RuntimeException('M86 duplicate independent old block UUID.');}$allOldUuids[$row['uuid']]=true;}}
foreach($patch['targets'] as $i=>$target){
 $owner=$target['identity'];$captured=$before['pages'][$owner];$oldSource=$sourceBefore['targets'][$i]['before'];$after=$target['after'];$page=$captured['page'];
 if(($before['definitions'][$owner]['definition']??null)!==$oldSource||$page['id']!==Package::PAGE_IDS[$i]||$page['page_category_id']!==Package::CATEGORY_IDS[$i]
  ||$page['seeder']!==$owner||$page['text']!==null||$oldSource['page']['subtitle_text']!==null||$after['page']['subtitle_text']!==null
  ||$after['page']['title']!==$page['title']){throw new RuntimeException('M86 independent source/Page/NULL subtitle/practice baseline differs.');}
 Service::assertDefinitionScope($oldSource,$after,$target,$i);
 foreach(['page','category_ancestry','blocks'] as $field){if(($preview['pages'][$owner][$field]??null)!==$captured[$field]){throw new RuntimeException('M86 reviewed original Page/category/block inventory differs.');}}
 if(count($oldSource['page']['blocks'])!==Package::CONTINUING_COUNTS[$i]||count($after['page']['blocks'])!==Package::CONTINUING_COUNTS[$i]+($i<4?0:1)||Package::PRACTICE_SOURCE_INDICES[$i]!==($i<4?6:7)){throw new RuntimeException('M86 exact continuing/four-retained/two-append count differs.');}
 if($load($root.'/'.$target['path'])!==$after){throw new RuntimeException('M86 active source is not frozen AFTER.');}
 $positions=[];$ownerBodies=0;
 foreach($captured['blocks'] as $old){
  if(isset($seenOldUuids[$old['uuid']])){throw new RuntimeException('M86 captured block UUID duplicated.');}$seenOldUuids[$old['uuid']]=true;
  if($old['locale']!=='uk'){++$nonUk;continue;}++$ukCount;$pos=$old['sort_order'];
  if(!is_int($pos)||$pos<0||$pos>Package::CONTINUING_COUNTS[$i]||isset($positions[$pos])||$old['seeder']!==$owner||$old['page_id']!==$page['id']||$old['page_category_id']!==Package::CATEGORY_IDS[$i]||$old['level']!==null){throw new RuntimeException('M86 captured continuing UUID/owner/order/NULL level differs.');}$positions[$pos]=true;
  $oldConfig=$pos===0?['type'=>'subtitle','column'=>'header','heading'=>null,'css_class'=>null,'level'=>$oldSource['page']['subtitle_level']??null,'uuid_key'=>$oldSource['page']['subtitle_uuid_key']??'subtitle','body'=>$oldSource['page']['subtitle_html']]:$oldSource['page']['blocks'][$pos-1];
  $config=$pos===0?array_replace($oldConfig,['body'=>$after['page']['subtitle_html']]):$after['page']['blocks'][$pos-1];
  if($oldConfig['body']!==$old['body']||M26DetailPackage::uuid($owner,$oldConfig,$pos)!==$old['uuid']||M26DetailPackage::uuid($owner,$config,$pos)!==$old['uuid']){throw new RuntimeException('M86 independent continuing source/SQL body or UUID differs.');}
  foreach(['type','column','heading','css_class','level'] as $f){if(($oldConfig[$f]??null)!==$old[$f]||($config[$f]??null)!==$old[$f]){throw new RuntimeException('M86 unapproved continuing metadata field: '.$f);}}
  if($config['body']!==$old['body']){$key='text_blocks:'.$old['id'];$expected[$key]=['table'=>'text_blocks','id'=>$old['id'],'uuid'=>$old['uuid'],'seeder'=>$owner,'before'=>['body'=>$old['body']],'after'=>['body'=>$config['body']]];++$bodyKinds[$pos===0?'subtitle':'teaching'];++$ownerBodies;$operations[]=['operation'=>'body-update','owner'=>$owner,'id'=>$old['id'],'uuid'=>$old['uuid'],'sort_order'=>$pos,'type'=>$old['type'],'column'=>$old['column'],'level'=>null,'before_body_sha256'=>hash('sha256',$old['body']),'after_body_sha256'=>hash('sha256',$config['body'])];}
 }
 $posList=array_keys($positions);sort($posList);if($posList!==range(0,Package::CONTINUING_COUNTS[$i])){throw new RuntimeException('M86 continuing position coverage differs.');}
 $slot=Package::PRACTICE_SOURCE_INDICES[$i];$practice=$after['page']['blocks'][$slot];$position=$slot+1;$uuid=M26DetailPackage::uuid($owner,$practice,$position);
 if($practice['type']!=='practice-set'||$practice['column']!=='left'||($practice['level']??null)!==null){throw new RuntimeException('M86 exact practice type/column/NULL level differs.');}
 $capturedPractices=$captured['existing_practices']??null;
 if($i<4){
  $oldPractice=$oldSource['page']['blocks'][6];$matches=array_values(array_filter($captured['blocks'],static fn($row)=>$row['uuid']===$uuid));
  if(!is_array($capturedPractices)||count($capturedPractices)!==1||count($matches)!==1||$capturedPractices!==$matches
   ||$uuid!==Package::EXISTING_PRACTICE_UUIDS[$i]||M26DetailPackage::uuid($owner,$oldPractice,7)!==$uuid
   ||$matches[0]['page_id']!==$page['id']||$matches[0]['page_category_id']!==Package::CATEGORY_IDS[$i]
   ||$matches[0]['seeder']!==$owner||$matches[0]['sort_order']!==7||$matches[0]['type']!=='practice-set'
   ||$matches[0]['locale']!=='uk'||$matches[0]['column']!=='left'||$matches[0]['level']!==null){throw new RuntimeException('M86 four continuing practice UUID/owner/source6/full-row identities differ.');}
  $retainedPractices[$owner]=['id'=>$matches[0]['id'],'uuid'=>$uuid,'source_index'=>6,'sort_order'=>7];
 }else{
  if($capturedPractices!==[]||isset($allOldUuids[$uuid])||$slot!==7||!array_key_exists('level',$practice)){throw new RuntimeException('M86 new E/F practice collides or invents an existing practice.');}
  $fields=['uuid'=>$uuid,'page_id'=>$page['id'],'page_category_id'=>Package::CATEGORY_IDS[$i],'locale'=>'uk','type'=>'practice-set','column'=>'left','heading'=>$practice['heading']??null,'css_class'=>$practice['css_class']??null,'sort_order'=>$position,'body'=>$practice['body'],'level'=>null,'seeder'=>$owner];
  $expectedInserts[$owner]=['table'=>'text_blocks','seeder'=>$owner,'fields'=>$fields,'sha256'=>$digest($fields)];
  $operations[]=['operation'=>'practice-insert','owner'=>$owner,'uuid'=>$uuid,'sort_order'=>$position,'type'=>'practice-set','column'=>'left','level'=>null,'fields_sha256'=>$digest($fields),'body_sha256'=>hash('sha256',$fields['body'])];
 }
 $actualRelations=$preview['pages'][$owner]['relations'];$capturedRelations=$captured['relations'];
 foreach(['page_tag','page_category_tag','tag_text_block','question_theory_text_blocks','tags','questions','saved_grammar_test_questions'] as $family){if($rowSet($actualRelations[$family]??[])!==$rowSet($capturedRelations[$family])){throw new RuntimeException('M86 original linked row family differs: '.$family);}}
 $flat=array_fill_keys(array_values($familyMap),[]);$options=[];
 foreach($capturedRelations['question_snapshots'] as $snapshot){foreach($familyMap as $from=>$to){array_push($flat[$to],...$snapshot[$from]);}foreach($snapshot['options'] as $option){if(isset($options[$option['id']])&&$options[$option['id']]!==$option){throw new RuntimeException('M86 shared captured option differs.');}$options[$option['id']]=$option;}}
 $flat['question_options']=array_values($options);foreach($flat as $family=>$rows){if($rowSet($actualRelations[$family]??[])!==$rowSet($rows)){throw new RuntimeException('M86 complete old answer/option/hint/tag/variant family differs: '.$family);}}
 foreach($capturedRelations['questions'] as $q){if(isset($pageQuestions[$q['id']])&&$pageQuestions[$q['id']]!==$q){throw new RuntimeException('M86 cross-page question snapshot differs.');}$pageQuestions[$q['id']]=$q;}
 foreach(array_keys($captured['bank_inventory']) as $bank){$banks[$bank]=true;}
 $owners[]=['owner'=>$owner,'page_id'=>$page['id'],'category_id'=>Package::CATEGORY_IDS[$i],'continuing_uk'=>count($positions),'body_updates'=>$ownerBodies,'retained_practice'=>$retainedPractices[$owner]??null,'new_practice_uuid'=>$i<4?null:$uuid,'new_practice_sort_order'=>$i<4?null:$position,'own_graded'=>[10,11,11,11,6,6][$i],'full_inputs'=>$i<4?3:2,'widget_questions'=>5,'original_linked_question_count'=>count($capturedRelations['questions']),'all_old_relation_families_exact'=>true];
}
$seen=[];foreach($preview['updates'] as $change){$key=$change['table'].':'.$change['id'];$want=$expected[$key]??null;if($want===null||isset($seen[$key])||array_keys($change)!==['table','id','uuid','seeder','before','after','before_sha256','after_sha256']||array_diff_key($change,['before_sha256'=>true,'after_sha256'=>true])!==$want||$change['before_sha256']!==$digest($want['before'])||$change['after_sha256']!==$digest($want['after'])){throw new RuntimeException('M86 reviewed update is not the independently derived body-only change.');}$seen[$key]=true;}
$seenInserts=[];foreach($preview['inserts'] as $insert){$owner=$insert['seeder']??'';if(isset($seenInserts[$owner])||($expectedInserts[$owner]??null)!==$insert){throw new RuntimeException('M86 reviewed practice fields/UUID/order/hash differ.');}$seenInserts[$owner]=true;}
if(count($expected)!==53||count($seen)!==53||$bodyKinds!==['subtitle'=>6,'teaching'=>47]||count($expectedInserts)!==2||array_keys($seenInserts)!==array_slice(Package::OWNERS,4)||array_keys($retainedPractices)!==array_slice(Package::OWNERS,0,4)||$ukCount!==53||$nonUk!==98||count($operations)!==55||count($pageQuestions)!==912||count($banks)!==14){throw new RuntimeException('M86 exact53+2 row-operation /53UK/98ENPL/912-question/14-bank scope differs.');}
$scopeIds=array_keys($pageQuestions);sort($scopeIds,SORT_NUMERIC);
// Free large per-page snapshots before reading the independent full-bank receipt.
unset($plan,$preview['pages'],$before,$captured,$capturedRelations,$actualRelations,$flat,$options,$snapshot,$pageQuestions);
$consumers=$load($dir.'/'.Service::CONSUMERS_FILE,Service::CONSUMERS_SHA);$bankIds=array_column($consumers['old_sql']['questions'],'id');sort($bankIds,SORT_NUMERIC);
if($bankIds!==$scopeIds||($consumers['source_bank_count']??null)!==14||count($consumers['old_sql']['saved_tests']??[])!==14||($consumers['unchanged_during_http']??null)!==true){throw new RuntimeException('M86 independently captured page-linked/full-bank union scope differs.');}unset($consumers);
if(!hash_equals($rawSha,hash_file('sha256',$path))){throw new RuntimeException('M86 preview changed during pure review.');}
$result=['preview_file'=>$argv[1],'preview_raw_sha256'=>$rawSha,'reviewed_digest'=>$argv[2],'independent_before_sha256'=>Service::BEFORE_SHA,'independent_consumers_sha256'=>Service::CONSUMERS_SHA,'source_master_sha256'=>Package::MASTER_SHA,'source_patch_sha256'=>Package::SOURCE_SHA,'update_operations'=>53,'body_updates'=>$bodyKinds,'page_text_updates'=>0,'retained_practices'=>$retainedPractices,'practice_inserts'=>2,'total_exact_row_operations'=>55,'continuing_uk_rows'=>53,'unchanged_en_pl_rows'=>98,'owners'=>$owners,'exact_operation_review'=>$operations,'approved_fields'=>['updates'=>['text_blocks'=>['body']],'inserted_practice'=>array_keys(reset($expectedInserts)['fields'])],'metadata_changes'=>[],'question_family_updates'=>0,'question_family_inserts'=>0,'deletes'=>0,'question_scope_notes'=>['unique_page_linked_question_ids'=>912,'full_fourteen_bank_assigned_question_ids'=>912,'same_id_union'=>true,'saved_pools'=>14,'per_page_counts_overlap'=>true],'verification_boundary'=>'Pure file/source/preview comparison only. Fresh DB identity, backup/apply, SQL postconditions, HTTP and browser acceptance are separate gates.','application_bootstrapped'=>false,'db_connections'=>0,'db_queries'=>0,'db_writes'=>0,'http_requests'=>0];
$bytes=json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";$out=$dir.'/exact-diff-review-v'.$version[1].'.json';
if(is_link($out)||file_exists($out)){throw new RuntimeException('M86 pure review output already exists.');}$h=fopen($out,'x');if(!$h){throw new RuntimeException('M86 exclusive review receipt unavailable.');}try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M86 review receipt incomplete.');}}finally{fclose($h);}
echo json_encode(['receipt'=>basename($out),'receipt_sha256'=>hash('sha256',$bytes),'reviewed_digest'=>$argv[2],'updates'=>53,'inserts'=>2,'question_changes'=>0,'page_text_changes'=>0,'app_bootstrap'=>false,'SQL_access'=>false,'http_requests'=>0],JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
