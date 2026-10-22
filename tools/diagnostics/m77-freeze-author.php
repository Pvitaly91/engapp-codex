<?php

/** Explicit finite artifact compiler. No bootstrap, SQL, HTTP, mirroring or Git. */
if(PHP_SAPI!=='cli'||count($argv)!==7
    ||preg_match('/^author-projection-v[1-9][0-9]*\.json$/D',$argv[1])!==1
    ||preg_match('/^bank-audit-projection-v[1-9][0-9]*\.json$/D',$argv[3])!==1
    ||preg_match('/^prospective-metadata-v[1-9][0-9]*\.json$/D',$argv[5])!==1
    ||array_filter([$argv[2],$argv[4],$argv[6]],static fn($s)=>preg_match('/^[a-f0-9]{64}$/D',$s)!==1)){
    throw new RuntimeException('M77 needs three independently reviewed private artifact basenames/raw hashes.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m77-local';
require $root.'/vendor/autoload.php';require_once $wt.'/app/Support/M77OnePrepositionsPhrasal.php';
use App\Support\M77OnePrepositionsPhrasal as Package;
$read=static function($name,$sha)use($dir){$path=$dir.'/'.$name;if(is_link($path)||!is_file($path)||!hash_equals($sha,hash_file('sha256',$path))){throw new RuntimeException('M77 reviewed private artifact drift.');}return json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);};
$author=$read($argv[1],$argv[2]);$bank=$read($argv[3],$argv[4]);$metadata=$read($argv[5],$argv[6]);
Package::validate($author['source_before'],$author['patch'],$author['master'],$author['contract']);
if(($bank['source_proof']['author_projection_sha256']??null)!==$argv[2]
    ||array_column($bank['targets']??[],'identity')!==Package::OWNERS
    ||($metadata['projection_sha256']??null)!==$argv[2]
    ||array_column($metadata['metadata']??[],'identity')!==Package::OWNERS
    ||array_map(static fn($t)=>count($t['first_five']),$bank['targets'])!==[5,5,5,5,5,5]){
    throw new RuntimeException('M77 bank/metadata exact six-owner author binding differs.');
}
// The user's M77 process clarification supersedes the autonomous-preview gate.
// Private HTML/SSR artifacts are auxiliary, not browser evidence or approval.
// Keep real-page 320px acceptance pending; neither freeze nor apply proves it.
$formulaReview=[
    'status'=>'deferred_to_real_after_apply','prefreeze_status'=>'not_checked',
    'required_before_freeze'=>false,'required_before_guarded_local_apply'=>false,
    'user_approval_required'=>false,'user_screenshots_required'=>false,
    'autonomous_html_artifacts'=>'auxiliary_not_browser_acceptance',
    'after_apply'=>['status'=>'not_checked','target'=>'http://gramlyze.loc',
        'scope'=>'All six real local theory pages','viewport_width'=>320,'themes'=>['light','dark'],
        'evidence'=>'Separate actual browser acceptance receipt; blocked checks remain blocked/not_checked.'],
    'process_clarification'=>'Autonomous HTML review is optional. Do not require user screenshots or approval, bypass browser security, or count post-apply acceptance as pre-apply PASS.',
];
$master=$author['master'];$master['status']='frozen';$master['editorial_review']['expected_http_metadata']=$metadata;
$patch=$author['patch'];$patch['status']='frozen';$bank['status']='frozen';$bank['pending']=[];
$editorial=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'version'=>'1.0.0','status'=>'frozen',
    'review'=>'Independent main/agent editorial review, not human review. Original examples/tasks; publisher exercises not copied.',
    'author_projection_sha256'=>$argv[2],'draft_parts_sha256'=>$author['draft_sha256'],
    'lesson_reviews'=>$master['editorial_review']['lesson_reviews'],'projection_edits'=>$master['editorial_review']['projection_edits'],
    'expected_http_metadata'=>$metadata,'prefreeze_formula_browser_sha256'=>null,
    'formula_browser_validation'=>$formulaReview,
    'main_language_review'=>[
        'One keeps informal my/your one, modified numeral/some/any models and mass/unit reference; one is not always a different object.',
        'Time has nonempty absence and BrE/AmE alternatives; night/specific day and in two hours are contextual.',
        'Place explicitly separates point/interior/surface, at does not mean outdoors, passenger on differs from by.',
        'Movement allows natural in/into and on/onto alternatives; direction does not imply an already completed arrival.',
        'Dependent separates adjective/verb/noun patterns, contextual agree models and to-preposition versus infinitive; answer-to focus is explicit.',
        'Phrasal separates particle change from polysemy, object placement from transitivity and specific expression register; noun-order aliases accepted.',
    ],
    'main_source_access'=>[
        'All ten requested grammar URLs attempted directly: British Council adjective/verb/phrasal editorial bodies read; Cambridge URLs returned403 and BC one timed out.',
        'Official Cambridge indexed editorial bodies read for one/time/place/in-into/on/multiword; access not misreported as direct success. BC One indexed text/identified editorial replies distinguished from learner comments.',
        'Google Search Central helpful-content editorial body read; no indexing/ranking guarantee or word quota.',
    ],
    'boundaries'=>['42 own graded tasks: eight One source functions transferred,34 new tasks. Six widgets/30 reused questions counted separately.',
        'Existing native comparison remains case/punctuation insensitive. No empty canonical key or punctuation-only correct/distractor pair.',
        'Zero details is an explicit semantic decision; all essential rules, examples, translations and short notes remain visible.',
        'One practice is in-place/NULL. Five new practice blocks have explicit A1/A1/A2/B1/B1; no continuing SQL-level rewrite.',
        'No M77 Blade/CSS/JS, formatter, grader or practice-engine edits. Only exact-bound package/cardinality registrations.']];
$json=static fn($v)=>json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$outputs=[Package::BEFORE=>$json($author['source_before']),Package::SOURCE=>$json($patch),Package::MASTER=>$json($master),
    Package::CONTRACT=>$json($author['contract']),'docs/content/m77-one-prepositions-phrasal.components.v1.json'=>$json($author['component_map']),
    'docs/content/m77-one-prepositions-phrasal.tasks.v1.json'=>$json($author['task_map']),
    'docs/content/m77-one-prepositions-phrasal.editorial.v1.json'=>$json($editorial),
    'docs/content/m77-sentence-builder-bank-audit.v1.json'=>$json($bank)];
$md="# M77 — One / Ones, прийменники та базові фразові дієслова\n\nАвторська редакція 1.0.0. Чинний native-дизайн і механізм оцінювання не змінюються.\n\n";
$plain=static fn($v)=>html_entity_decode(strip_tags((string)$v),ENT_QUOTES|ENT_HTML5,'UTF-8');
$walk=static function($data)use(&$walk,$plain){$out='';foreach($data as $key=>$value){
    if(in_array($key,['id','source_index','key','type','column','accent','icon','current','design','linked_practice','seeder_classes','m77_native_v1','m77_detail_key','level'],true)){continue;}
    if(is_array($value)){$out.=$key==='feedback'?"\nПояснення відповідей:\n\n":'';$out.=$walk($value);continue;}
    if(is_bool($value)||$value===null||trim((string)$value)===''){continue;}
    $label=['answer'=>'Ключ','question'=>'Умова','prompt'=>'Умова','translation'=>'Переклад','title'=>'Пункт','subtitle'=>'Пояснення','text'=>'Пояснення','en'=>'Англійською','ua'=>'Українською','hint'=>'Підказка','url'=>'Посилання'][$key]??'';
    $out.=($label===''?'':$label.': ').$plain($value)."\n\n";
}return $out;};
foreach($master['lessons'] as $lesson){$md.='## '.$lesson['preserved_h1']."\n\n".$lesson['local_url']."\n\n".$plain($lesson['subtitle_html'])."\n\n";
    foreach($lesson['blocks'] as $block){$md.='### '.$block['key']."\n\n".$walk($block['data']);}}
$outputs['docs/content/m77-one-prepositions-phrasal.v1.0.0.uk.md']=rtrim($md)."\n";
$definitions=[];foreach($patch['targets'] as $target){$definitions[$target['path']]=$json($target['after']);}
$checks=['schema_version'=>1,'package'=>'m77-one-prepositions-phrasal','version'=>'1.0.0','status'=>'frozen',
    'hash_algorithm'=>'SHA-256 raw UTF-8 bytes','pre_hash_checks'=>['utf8_bom'=>false,'eol'=>'LF','exactly_one_final_lf'=>true,'trailing_whitespace'=>false],
    'artifacts'=>[],'active_definitions'=>[],'author_inputs'=>[],'notes'=>$editorial['boundaries']];
foreach([$outputs,$definitions] as $i=>$group){foreach($group as $path=>$bytes){
    if(str_contains($bytes,"\r")||str_starts_with($bytes,"\xEF\xBB\xBF")||!str_ends_with($bytes,"\n")||str_ends_with($bytes,"\n\n")||preg_match('/[\t ]+$/m',$bytes)){
        throw new RuntimeException('M77 pre-hash UTF8/LF policy failed: '.$path);}
    $checks[$i===0?'artifacts':'active_definitions'][]=['path'=>$path,'sha256'=>hash('sha256',$bytes),'bytes'=>strlen($bytes)];
}}
$bindingBytes=file_get_contents($wt.'/'.Package::BINDINGS);
if(!hash_equals(Package::BINDINGS_SHA,hash('sha256',$bindingBytes))||str_contains($bindingBytes,"\r")){throw new RuntimeException('M77 metadata ledger changed.');}
$checks['artifacts'][]=['path'=>Package::BINDINGS,'sha256'=>Package::BINDINGS_SHA,'bytes'=>strlen($bindingBytes)];
foreach($author['draft_sha256'] as $name=>$sha){$path='docs/content/m77-author-parts/'.$name.'.v1.json';
    if(!hash_equals($sha,hash_file('sha256',$wt.'/'.$path))){throw new RuntimeException('M77 reviewed author input changed.');}
    $checks['author_inputs'][]=['path'=>$path,'sha256'=>$sha,'bytes'=>filesize($wt.'/'.$path)];}
$outputs[Package::CHECKSUMS]=$json($checks);
foreach($outputs as $path=>$bytes){if(file_exists($wt.'/'.$path)||is_link($wt.'/'.$path)){throw new RuntimeException('M77 frozen path already exists: '.$path);}}
foreach($definitions as $path=>$bytes){$old=array_values(array_filter($author['source_before']['targets'],static fn($t)=>$t['path']===$path));
    if(count($old)!==1||json_decode(file_get_contents($wt.'/'.$path),true,flags:JSON_THROW_ON_ERROR)!==$old[0]['before']){throw new RuntimeException('M77 active source changed before compile.');}}
foreach($outputs+$definitions as $path=>$bytes){$file=$wt.'/'.$path;if(!is_dir(dirname($file))&&!mkdir(dirname($file),0700,true)){throw new RuntimeException('M77 artifact directory unavailable.');}
    if(file_put_contents($file,$bytes)!==strlen($bytes)){throw new RuntimeException('M77 artifact write incomplete.');}}
echo $json(['artifacts'=>$checks['artifacts'],'active_definitions'=>$checks['active_definitions'],
    'checksums_sha256'=>hash('sha256',$outputs[Package::CHECKSUMS]),'formula_browser_validation'=>$formulaReview,
    'db_connections'=>0,'db_writes'=>0,'http_requests'=>0]);
