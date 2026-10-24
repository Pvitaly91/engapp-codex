<?php

/** Finite M87 BEFORE profile; immutable old capture, actual legacy pools retained. */
require __DIR__.'/frozen-readonly-profile.php';
$profileOnly=($argv[1]??null)==='--profile-check-only'&&count($argv)===2;
if(!$profileOnly&&(($argv[1]??null)!=='capture'||count($argv)!==3)){throw new RuntimeException('M87 BEFORE profile supports capture only; no apply/audit shortcuts.');}
$primitive=file_get_contents(__DIR__.'/m52-old-consumers.php');
$start=strpos($primitive,'$manifests = [');$end=strpos($primitive,'// Named route templates own URL construction.');
if($start===false||$end===false||$end<=$start){throw new RuntimeException('M87 immutable consumer declarations unavailable.');}
$oldDeclarations=str_replace('M52','M87',substr($primitive,$start,$end-$start));
$declarations=<<<'PHP'
$beforeFile=$dir.'/before-v1.json';
if(is_link($beforeFile)||!is_file($beforeFile)||!hash_equals('c9b7cffb07a0440e5051a0b7c522eb016ce4cdcc80107edf6573e8780eb151c5',hash_file('sha256',$beforeFile))){throw new RuntimeException('M87 independent BEFORE differs.');}
$captured=json_decode(file_get_contents($beforeFile),true,flags:JSON_THROW_ON_ERROR);
$uuidProjection=new class extends App\Support\Database\JsonTestSeeder{
 protected function definitionPath():string{throw new RuntimeException('Read-only source UUID projector cannot seed.');}
 public function sourceQuestions(array $definition):array{$scope=$this->resolveUuidNamespace($definition,$definition['seeder']['class']);$out=[];foreach($definition['questions'] as $i=>$q){$out[]=$this->resolveQuestionUuid($q,$scope,$i,trim((string)$q['question']));}return $out;}
};
$pages=[];$bankDeclarations=[];$savedGroups=[];$sourceSetObservations=[];$routingObservations=[];
foreach($captured['pages'] as $owner=>$capture){
 $ancestry=$capture['category_ancestry'];$pages[$owner]=['page_seeder_class'=>$owner,'category_slug'=>end($ancestry)['slug'],'category_path'=>implode('/',array_column($ancestry,'slug')),'slug'=>$capture['page']['slug']];
 foreach($capture['bank_inventory'] as $bank=>$inventory){
  if(isset($bankDeclarations[$bank])){$bankDeclarations[$bank]['page_owners'][]=$owner;continue;}
  $relative='database/seeders/'.str_replace('\\','/',substr($bank,strlen('Database\\Seeders\\'))).'/definition.json';$definition=$readSource($relative);$saved=$definition['saved_test']??null;
  if(($definition['seeder']['class']??null)!==$bank||!is_array($saved)||!preg_match('/^[a-z0-9_-]+$/D',$saved['slug']??'')||empty($saved['uuid'])){throw new RuntimeException('M87 discovered bank source identity/saved declaration differs.');}
  $projected=$uuidProjection->sourceQuestions($definition);$types=array_values(array_unique(array_map(static fn($q)=>(string)($q['type']??$definition['defaults']['type']??''),$definition['questions'])));
  $bankDeclarations[$bank]=['bank'=>$bank,'definition'=>$relative,'saved'=>$saved,'source_question_uuids'=>$projected,'compose'=>$types===['4'],'page_owners'=>[$owner]];
  $slug=$saved['slug'];if(isset($savedGroups[$slug])&&($savedGroups[$slug]['saved']['uuid']!==$saved['uuid']||$savedGroups[$slug]['compose']!==($types===['4']))){throw new RuntimeException('M87 shared saved identity/type differs.');}
  if(!isset($savedGroups[$slug])){$savedGroups[$slug]=['bank'=>$bank,'banks'=>[],'definition'=>$relative,'saved'=>$saved,'declared_source_question_uuids'=>[],'compose'=>$types===['4'],'page_owner'=>$owner];}
  $savedGroups[$slug]['banks'][]=$bank;$savedGroups[$slug]['declared_source_question_uuids']=[...$savedGroups[$slug]['declared_source_question_uuids'],...$projected];
 }
}
ksort($bankDeclarations);ksort($pages);ksort($sourceHashes);ksort($savedGroups);
if(count($bankDeclarations)!==14||count($pages)!==6||count($savedGroups)!==14){throw new RuntimeException('M87 finite bank/page/saved discovery count differs.');}
$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try{
 foreach($bankDeclarations as $bank=>$decl){$actual=$db->table('questions')->where('seeder',$bank)->orderBy('id')->pluck('uuid')->all();$expected=$decl['source_question_uuids'];sort($actual);sort($expected);$sourceSetObservations[$bank]=['source_count'=>count($expected),'actual_count'=>count($actual),'source_sql_set_exact'=>$actual===$expected,'missing_from_sql'=>array_values(array_diff($expected,$actual)),'not_in_source'=>array_values(array_diff($actual,$expected)),'policy'=>'Pre-existing source/SQL differences are captured, never repaired, filtered, or reported as source equality. AFTER must preserve this actual complete SQL baseline.'];}
 $declarations=[];
 foreach($savedGroups as $slug=>$decl){$test=(array)$db->table('saved_grammar_tests')->where('slug',$slug)->sole();if($test['uuid']!==$decl['saved']['uuid']){throw new RuntimeException('M87 saved-test SQL identity differs.');}
  $actual=$db->table('saved_grammar_test_questions')->where('saved_grammar_test_id',$test['id'])->orderBy('position')->orderBy('id')->pluck('question_uuid')->all();if($actual===[]||count(array_unique($actual))!==count($actual)){throw new RuntimeException('M87 saved pool absent/duplicate.');}
  $declared=array_values(array_unique($decl['declared_source_question_uuids']));$a=$actual;$e=$declared;sort($a);sort($e);$decl['declared_pool_set_exact']=$a===$e;$decl['actual_saved_pool_policy']='Use immutable actual SQL saved-link order for baseline/route count; declared source union retained separately, never substituted into DB.';
  $decl['source_question_uuids']=$actual;$decl['saved']['question_uuids']=$actual;$declarations[$slug]=$decl;
 }
}finally{$db->rollBack();}
unset($captured,$capture,$inventory,$definition,$saved,$projected);
PHP;
$oldSnapshotStart=strpos($primitive,'$sqlSnapshot = static function ()');$oldSnapshotEnd=strpos($primitive,'$readOnly = static function', $oldSnapshotStart);
if($oldSnapshotStart===false||$oldSnapshotEnd===false){throw new RuntimeException('M87 old SQL snapshot primitive unavailable.');}
$oldSnapshot=str_replace('M52','M87',substr($primitive,$oldSnapshotStart,$oldSnapshotEnd-$oldSnapshotStart));
$snapshot=<<<'PHP'
$sqlSnapshot=static function()use($db,$rows,$orderedRows,$bankDeclarations,$declarations,$sourceSetObservations):array{
 $questions=$rows($db->table('questions')->whereIn('seeder',array_keys($bankDeclarations))->orderBy('id'));$ids=array_column($questions,'id');$uuids=array_column($questions,'uuid');
 $result=['questions'=>$questions,'source_set_observations'=>$sourceSetObservations,'source_bank_declarations'=>$bankDeclarations,'saved_pool_declarations'=>$declarations];
 foreach(['question_answers','question_option_question','verb_hints','question_hints','question_variants','question_tag','question_marker_tag'] as $table){$result[$table]=$orderedRows($table,$db->table($table)->whereIn('question_id',$ids));}
 $optionIds=array_unique([...array_column($result['question_answers'],'option_id'),...array_column($result['question_option_question'],'option_id'),...array_column($result['verb_hints'],'option_id')]);
 $result['question_options']=$rows($db->table('question_options')->whereIn('id',$optionIds)->orderBy('id'));
 $result['question_theory_text_blocks']=$orderedRows('question_theory_text_blocks',$db->table('question_theory_text_blocks')->whereIn('question_uuid',$uuids),['question_uuid','position','text_block_uuid']);$result['saved_tests']=[];$result['saved_question_links']=[];
 foreach($declarations as $slug=>$decl){$test=(array)$db->table('saved_grammar_tests')->where('slug',$slug)->sole();$links=$orderedRows('saved_grammar_test_questions',$db->table('saved_grammar_test_questions')->where('saved_grammar_test_id',$test['id']),['position']);if($test['uuid']!==$decl['saved']['uuid']||array_column($links,'question_uuid')!==$decl['saved']['question_uuids']){throw new RuntimeException('M87 independently captured saved pool drift.');}$result['saved_tests'][$slug]=$test;$result['saved_question_links'][$slug]=$links;}
 return $result;
};
PHP;
$routeStart=strpos($primitive,'$routePath = static function');$routeEnd=strpos($primitive,'$routes = [];',$routeStart);
if($routeStart===false||$routeEnd===false){throw new RuntimeException('M87 route primitive unavailable.');}
$oldRoute=str_replace('M52','M87',substr($primitive,$routeStart,$routeEnd-$routeStart));
$route=<<<'PHP'
$routePath=static function(string $routeName,array $parameters)use(&$routingObservations):array{
 $route=app('router')->getRoutes()->getByName($routeName);if(!$route||!in_array('GET',$route->methods(),true)){throw new RuntimeException('M87 named GET route unavailable.');}
 $uri='/'.$route->uri();foreach($parameters as $key=>$value){$uri=str_replace('{'.$key.'}',$value,$uri);}
 if(str_contains($uri,'{')||!preg_match('~^/(?:test|courses/english-grammar-theory/lesson)/[a-z0-9_/-]+$~D',$uri)){throw new RuntimeException('M87 consumer URI outside finite local families.');}
 $matched=app('router')->getRoutes()->match(Illuminate\Http\Request::create('http://gramlyze.loc'.$uri,'GET'));
 if(in_array('App\\Http\\Middleware\\AdminAuthenticate',$matched->gatherMiddleware(),true)){throw new RuntimeException('M87 old consumer route is admin-gated.');}
 if($matched->getName()!==$routeName||$matched->getActionName()!==$route->getActionName()){
  if(!in_array($uri,['/test/present-continuous/questions','/test/past-simple/questions'],true)||$routeName!=='test.show'||$matched->getName()!=='test.js.questions'||$matched->getActionName()!=='App\\Http\\Controllers\\GrammarTestController@fetchSavedTestJsQuestions'){throw new RuntimeException('M87 unclassified old route collision: '.$uri);}
  $routingObservations[$uri]=['requested_url'=>'http://gramlyze.loc'.$uri,'requested_route_name'=>$routeName,'requested_action'=>$route->getActionName(),'requested_consumer_kind'=>'ssr','actual_route_name'=>$matched->getName(),'actual_action'=>$matched->getActionName(),'actual_content_kind'=>'json','strict_requested_kind_acceptance'=>false,'scope'=>'Pre-existing route collision captured literally; no route/controller repair and no SSR acceptance claim.'];
  return ['path'=>$uri,'route_name'=>$matched->getName(),'action'=>$matched->getActionName(),'kind'=>'json','requested_route_name'=>$routeName,'requested_kind'=>'ssr','baseline_collision'=>true];
 }
 return ['path'=>$uri,'route_name'=>$routeName,'action'=>$route->getActionName()];
};
PHP;
$loopStart=strpos($primitive,'foreach ($routes as $path => $route) {');$loopEnd=strpos($primitive,'$sqlAfter = $readOnly($sqlSnapshot);',$loopStart);
if($loopStart===false||$loopEnd===false){throw new RuntimeException('M87 finite HTTP loop unavailable.');}
$oldLoop=str_replace('M52','M87',substr($primitive,$loopStart,$loopEnd-$loopStart));
$loop=<<<'PHP'
foreach($routes as $path=>$route){
 $http=$getLocal($route);$requested=$route['requested_kind']??$route['kind'];$entry=['url'=>'http://gramlyze.loc'.$path,'status'=>$http['status'],'robots_header'=>$http['robots_header'],'route_name'=>$route['route_name'],'action'=>$route['action'],'kind'=>$route['kind'],'requested_consumer_kind'=>$requested,'actual_consumer_kind'=>$route['kind'],'strict_requested_kind_acceptance'=>!($route['baseline_collision']??false),'strict_http_200'=>$http['status']===200,'expected_count'=>$route['expected_count'],'body_sha256'=>hash('sha256',$http['body']),'single_actual_request'=>true];
 if($http['status']!==200){$entry['strict_payload_acceptance']=false;$entry['acceptance']='Existing unavailable/gated consumer captured literally; no bypass or acceptance claim.';}
 elseif($route['kind']==='course'){$entry['facts']=$htmlFacts($http['body'],true);$entry['strict_payload_acceptance']=true;}
 else{
  try{$questions=$route['kind']==='json'?(json_decode($http['body'],true,flags:JSON_THROW_ON_ERROR)['questions']??[]):$ssrQuestions($http['body']);$entry['payload']=$questionPayload($questions);$entry['strict_payload_acceptance']=true;$entry['strict_expected_count_acceptance']=$route['expected_count']===null||$entry['payload']['count']===$route['expected_count'];
   if(!$entry['strict_expected_count_acceptance']){$entry['count_observation']='Pre-existing HTTP payload differs from actual saved-link count. Both exact values retained; no question filtering, repair, replay, or count acceptance.';}
  }catch(RuntimeException|JsonException $error){$entry['strict_payload_acceptance']=false;$entry['payload_error_class']=get_class($error);$entry['payload_error']=$error->getMessage();$entry['acceptance']='Pre-existing malformed/unavailable requested payload captured without acceptance claim.';}
  if($route['kind']==='ssr'){$entry['facts']=$htmlFacts($http['body'],false);}
 }
 $result['http'][$path]=$entry;echo $json(['consumer'=>$path,'status'=>$http['status'],'requested_kind'=>$requested,'actual_kind'=>$route['kind'],'questions'=>$entry['payload']['count']??null,'expected_count'=>$route['expected_count'],'strict_kind'=>$entry['strict_requested_kind_acceptance'],'strict_count'=>$entry['strict_expected_count_acceptance']??null]);
}
PHP;
$capturePath='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m87-local/before-v1.json';
if(is_link($capturePath)||!hash_equals('c9b7cffb07a0440e5051a0b7c522eb016ce4cdcc80107edf6573e8780eb151c5',hash_file('sha256',$capturePath))){throw new RuntimeException('M87 finite source guard BEFORE differs.');}
$capture=json_decode(file_get_contents($capturePath),true,flags:JSON_THROW_ON_ERROR);$paths=[];
foreach($capture['pages'] as $page){foreach(array_keys($page['bank_inventory']) as $bank){$paths[]='database/seeders/'.str_replace('\\','/',substr($bank,strlen('Database\\Seeders\\'))).'/definition.json';}}
$paths=array_values(array_unique($paths));sort($paths);if(count($paths)!==14){throw new RuntimeException('M87 exact source guard count differs.');}
$guard='~^(?:'.implode('|',array_map(static fn($p)=>preg_quote($p,'~'),$paths)).')$~D';
$oldGuard='~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\\.json)$~D';
$oldTableCheck=<<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M87 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M87 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$newTableCheck=<<<'PHP'
if(count($allTablesBeforeHttp)!==46||$allTablesBeforeHttp!==$workingBefore['sql_before']){throw new RuntimeException('M87 all46 BEFORE SQL differs; no table exceptions.');}
PHP;
$replacements=[
 ['M52','M87',43],['m52','m87',4],
 ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
 [$oldDeclarations,$declarations,1],[$oldSnapshot,$snapshot,1],[$oldGuard,$guard,1],[$oldRoute,$route,1],[$oldLoop,$loop,1],
 ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff','c9b7cffb07a0440e5051a0b7c522eb016ce4cdcc80107edf6573e8780eb151c5',1],
 ["['categoryPath' => \$source['category_slug'], 'pageSlug' => \$source['slug']]","['categoryPath' => \$source['category_path'], 'pageSlug' => \$source['slug']]",1],
 ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
 ["'route_declarations' => \$routes, 'source_hashes' => \$sourceHashes, 'old_sql' => \$sqlBefore, 'http' => [],","'route_declarations' => \$routes, 'baseline_route_observations' => \$routingObservations, 'source_bank_count' => count(\$bankDeclarations), 'source_hashes' => \$sourceHashes, 'old_sql' => \$sqlBefore, 'http' => [],",1],
 ['curl_close($curl);','unset($curl);',1],
 ["\$workingBefore = json_decode(file_get_contents(\$workingBeforePath), true, flags: JSON_THROW_ON_ERROR);","\$workingBefore = json_decode(file_get_contents(\$workingBeforePath), true, flags: JSON_THROW_ON_ERROR);\n\$workingBefore=array_intersect_key(\$workingBefore,array_flip(['served_root','source_worktree','sql_before','sql_after_http']));",1],
 [$oldTableCheck,$newTableCheck,1],
 ["'sql_allowlist_during_apply' => ['pages', 'text_blocks'],","'sql_allowlist_during_apply' => 'None; capture-only BEFORE profile, all46 tables exact.',",1],
];
if($profileOnly){
 if(!hash_equals('1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',hash('sha256',$primitive))){throw new RuntimeException('M87 old primitive drift.');}
 foreach($replacements as $i=>[$old,$new,$count]){$n=substr_count($primitive,$old);if($n!==$count){throw new RuntimeException('M87 old-consumer literal mismatch at '.$i.': '.$n.' expected '.$count);}$primitive=str_replace($old,$new,$primitive);}token_get_all($primitive,TOKEN_PARSE);echo json_encode(['profile'=>'M87 old-consumer BEFORE','replacements'=>count($replacements),'parsed'=>true,'SQL_access'=>false,'http_requests'=>0])."\n";return;
}
runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',$replacements);
