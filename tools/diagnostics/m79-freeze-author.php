<?php

/** Explicit finite artifact compiler. No bootstrap, SQL, HTTP, mirroring or Git. */
if(PHP_SAPI!=='cli'||count($argv)!==7
    ||preg_match('/^author-projection-v[1-9][0-9]*\.json$/D',$argv[1])!==1
    ||preg_match('/^bank-audit-projection-v[1-9][0-9]*\.json$/D',$argv[3])!==1
    ||preg_match('/^prospective-metadata-v[1-9][0-9]*\.json$/D',$argv[5])!==1
    ||array_filter([$argv[2],$argv[4],$argv[6]],static fn($s)=>preg_match('/^[a-f0-9]{64}$/D',$s)!==1)){
    throw new RuntimeException('M79 needs three independently reviewed private artifact basenames/raw hashes.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m79-local';
require $root.'/vendor/autoload.php';require_once $wt.'/app/Support/M79QuestionTypesCompletion.php';
require_once $wt.'/app/Support/M79QuestionSupplement.php';
use App\Support\M79QuestionTypesCompletion as Package;
$read=static function($name,$sha)use($dir){$path=$dir.'/'.$name;if(is_link($path)||!is_file($path)||!hash_equals($sha,hash_file('sha256',$path))){throw new RuntimeException('M79 reviewed private artifact drift.');}return json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);};
$author=$read($argv[1],$argv[2]);$bank=$read($argv[3],$argv[4]);$metadata=$read($argv[5],$argv[6]);
(new App\Support\TheoryNativeContentRelease(Package::draftProfile($author['native_bindings']['bindings'])))
    ->validate($author['source_before'],$author['patch'],$author['master'],$author['contract']);
Package::assertEditorial($author['source_before'],$author['patch'],$author['master']);
if(($bank['source_proof']['author_projection_sha256']??null)!==$argv[2]
    ||array_column($bank['targets']??[],'identity')!==Package::OWNERS
    ||($metadata['projection_sha256']??null)!==$argv[2]
    ||array_column($metadata['metadata']??[],'identity')!==Package::OWNERS
    ||array_map(static fn($t)=>count($t['first_five']),$bank['targets'])!==[5,5,5,5,5,5]){
    throw new RuntimeException('M79 bank/metadata exact six-owner author binding differs.');
}
// The user's M79 process clarification supersedes the autonomous-preview gate.
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
    'review'=>'Main independently read all six author lessons/tasks and corrections; agent review is not human review. Original examples/tasks; publisher exercises not copied.',
    'author_projection_sha256'=>$argv[2],'draft_parts_sha256'=>$author['draft_sha256'],
    'lesson_reviews'=>$master['editorial_review']['lesson_reviews'],'projection_edits'=>$master['editorial_review']['projection_edits'],
    'expected_http_metadata'=>$metadata,'prefreeze_formula_browser_sha256'=>null,
    'formula_browser_validation'=>$formulaReview,
    'main_language_review'=>[
        'Alternative: written context distinguishes genuine choice from yes/no reading with or; both/neither, parallel options, main be and natural cash/card patterns are preserved.',
        'Tags: confirmation pattern is distinguished from same-polarity intent; formal am I not is valid, requested contracted/full forms and factual replies stay explicit.',
        'Negative: contracted and full not-order, time/participants and actual reply facts remain distinct; the question alone never proves its answer.',
        'Subject: neutral positive Simple does not require question do-support; required auxiliaries, negative/emphatic do, lexical do, plural groups and passive subjects are retained.',
        'Embedded: inner structure is separated from main inversion; lexical/negative do, who-subject/object and specific if/whether positions remain explicit.',
        'Indirect: practical current requests and useful responses are distinguished from statements and actual reporting, without inventing a universal terminology boundary.',
    ],
    'main_source_access'=>[
        'Requested Cambridge direct pages returned403; some British Council direct requests timed out. The British Council Questions and negatives body and Google helpful-content guidance were read directly; official indexed editorial bodies were read separately.',
        'Named LearnEnglish Team am I not reply is an editorial response, not an anonymous learner comment or direct main-body claim.',
        'Primary grammar-source ledger is preserved in each independently reviewed author part; new examples and tasks are original, not publisher exercises.',
        'Google helpful-content guidance is an editorial orientation, not an indexing/ranking guarantee.',
    ],
    'boundaries'=>[
        '57 own graded functions:31 retained/corrected+26new;3 retained demos separately.20inputs:16full and4retainedpartialtaggaps.',
        'Six widgets/30selected questions:22reused+8minimum insert-only supplement questions; original question/options/answers/links and standalone memberships unchanged.',
        'Native case/punctuation normalization is unchanged; textual score does not prove intonation or independently enforce question marks/commas.',
        'Four semantic details have exact point owners; essential rules, translations and task criteria remain visible basic.',
        'Three existing practice UUIDs/levels and all continuing metadata retained; onlyD/E/F append A2/B1/B1 practices. Display ranges never become exact filters.',
        'No visual source, formatter, grader or practice-engine changes. M78 retained-practice/M77 variable-owner/M76 every-shift safeguards retained.',
        'Defective old mid-clause-capitalized candidates are excluded, not repaired through shared options or hidden formatter rules.',
    ]];
$json=static fn($v)=>json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$outputs=[Package::BINDINGS=>$json($author['native_bindings']),Package::BEFORE=>$json($author['source_before']),Package::SOURCE=>$json($patch),Package::MASTER=>$json($master),
    Package::CONTRACT=>$json($author['contract']),'docs/content/m79-question-types-completion.components.v1.json'=>$json($author['component_map']),
    'docs/content/m79-question-types-completion.tasks.v1.json'=>$json($author['task_map']),
    'docs/content/m79-question-types-completion.editorial.v1.json'=>$json($editorial),
    'docs/content/m79-sentence-builder-bank-audit.v1.json'=>$json($bank)];
$md="# M79 — типи питань: вибір, tags, заперечення та вкладення\n\nАвторська редакція 1.0.0. Чинний native-дизайн і механізм оцінювання не змінюються.\n\n";
$plain=static fn($v)=>trim(html_entity_decode(strip_tags((string)$v),ENT_QUOTES|ENT_HTML5,'UTF-8'));
$walk=static function($data)use(&$walk,$plain){$out='';foreach($data as $key=>$value){
    if(in_array($key,['id','source_index','key','type','column','accent','icon','current','design','linked_practice','seeder_classes','m79_native_v1','m79_detail_key','level'],true)){continue;}
    if(is_array($value)){$out.=$key==='feedback'?"\nПояснення відповідей:\n\n":'';$out.=$walk($value);continue;}
    if(is_bool($value)||$value===null||trim((string)$value)===''){continue;}
    $label=['answer'=>'Ключ','question'=>'Умова','prompt'=>'Умова','translation'=>'Переклад','title'=>'Пункт','subtitle'=>'Пояснення','text'=>'Пояснення','en'=>'Англійською','ua'=>'Українською','hint'=>'Підказка','url'=>'Посилання'][$key]??'';
    $out.=($label===''?'':$label.': ').$plain($value)."\n\n";
}return $out;};
foreach($master['lessons'] as $lesson){$md.='## '.$lesson['preserved_h1']."\n\n".$lesson['local_url']."\n\n".$plain($lesson['subtitle_html'])."\n\n";
    foreach($lesson['blocks'] as $block){$md.='### '.$block['key']."\n\n".$walk($block['data']);}
    foreach($lesson['details'] as $detail){$md.='### Докладніше: '.$detail['title']."\n\n".$walk($detail['fragment']['data']);}}
$outputs['docs/content/m79-question-types-completion.v1.0.0.uk.md']=rtrim($md)."\n";
$definitions=[];foreach($patch['targets'] as $target){$definitions[$target['path']]=$json($target['after']);}
$checks=['schema_version'=>1,'package'=>'m79-question-types-completion','version'=>'1.0.0','status'=>'frozen',
    'hash_algorithm'=>'SHA-256 raw UTF-8 bytes','pre_hash_checks'=>['utf8_bom'=>false,'eol'=>'LF','exactly_one_final_lf'=>true,'trailing_whitespace'=>false],
    'artifacts'=>[],'active_definitions'=>[],'author_inputs'=>[],'notes'=>$editorial['boundaries']];
foreach([$outputs,$definitions] as $i=>$group){foreach($group as $path=>$bytes){
    if(str_contains($bytes,"\r")||str_starts_with($bytes,"\xEF\xBB\xBF")||!str_ends_with($bytes,"\n")||str_ends_with($bytes,"\n\n")||preg_match('/[\t ]+$/m',$bytes)){
        throw new RuntimeException('M79 pre-hash UTF8/LF policy failed: '.$path);}
    $checks[$i===0?'artifacts':'active_definitions'][]=['path'=>$path,'sha256'=>hash('sha256',$bytes),'bytes'=>strlen($bytes)];
}}

foreach($author['draft_sha256'] as $name=>$sha){$path='docs/content/m79-author-parts/'.$name.'.v1.json';
    if(!hash_equals($sha,hash_file('sha256',$wt.'/'.$path))){throw new RuntimeException('M79 reviewed author input changed.');}
    $checks['author_inputs'][]=['path'=>$path,'sha256'=>$sha,'bytes'=>filesize($wt.'/'.$path)];}
$inputs=['docs/content/m79-widget-selection.v1.json','docs/content/m79-question-bank-supplement.v1.json',
    'docs/content/m79-bank-author-parts/negative.v1.json','docs/content/m79-bank-author-parts/embedded.v1.json','docs/content/m79-bank-author-parts/indirect.v1.json'];
foreach($inputs as $path){
    if(!is_file($wt.'/'.$path)||is_link($wt.'/'.$path)){throw new RuntimeException('M79 reviewed input missing or linked.');}
    $bytes=file_get_contents($wt.'/'.$path);
    if(str_contains($bytes,"\r")||str_starts_with($bytes,"\xEF\xBB\xBF")||!str_ends_with($bytes,"\n")||str_ends_with($bytes,"\n\n")||preg_match('/[\t ]+$/m',$bytes)){throw new RuntimeException('M79 author input UTF8/LF/EOF policy failed: '.$path);}
    $checks['author_inputs'][]=['path'=>$path,'sha256'=>hash('sha256',$bytes),'bytes'=>strlen($bytes)];
}
$selectionPin=$bank['source_proof']['author_review_sha256']['docs/content/m79-widget-selection.v1.json']??null;
if(!is_string($selectionPin)||!hash_equals($selectionPin,hash_file('sha256',$wt.'/docs/content/m79-widget-selection.v1.json'))){throw new RuntimeException('M79 bank review selection input drift.');}
App\Support\M79QuestionSupplement::load($wt);
$outputs[Package::CHECKSUMS]=$json($checks);
foreach($outputs as $path=>$bytes){if(file_exists($wt.'/'.$path)||is_link($wt.'/'.$path)){throw new RuntimeException('M79 frozen path already exists: '.$path);}}
foreach($definitions as $path=>$bytes){$old=array_values(array_filter($author['source_before']['targets'],static fn($t)=>$t['path']===$path));
    if(count($old)!==1||json_decode(file_get_contents($wt.'/'.$path),true,flags:JSON_THROW_ON_ERROR)!==$old[0]['before']){throw new RuntimeException('M79 active source changed before compile.');}}
// This compiler emits only an exclusive private plan. Main applies reviewed
// repository edits with apply_patch; this command never edits active sources.
$beforeBytes=[];
foreach ($definitions as $path=>$bytes) { $beforeBytes[$path]=file_get_contents($wt.'/'.$path); }
$plan=['schema_version'=>1,'author_projection_sha256'=>$argv[2],'bank_projection_sha256'=>$argv[4],
    'prospective_metadata_sha256'=>$argv[6],'outputs'=>$outputs+$definitions,'before_bytes'=>$beforeBytes,
    'artifacts'=>$checks['artifacts'],'active_definitions'=>$checks['active_definitions'],
    'checksums_sha256'=>hash('sha256',$outputs[Package::CHECKSUMS]),'db_connections'=>0,'db_writes'=>0,'http_requests'=>0];
$file=$dir.'/freeze-plan-v1.json';$bytes=$json($plan);
if(file_exists($file)||is_link($file)){throw new RuntimeException('M79 exclusive freeze plan already exists.');}
$h=fopen($file,'x');if(!$h){throw new RuntimeException('M79 private freeze plan unavailable.');}
try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M79 private freeze plan incomplete.');}}finally{fclose($h);}
echo $json(['file'=>'freeze-plan-v1.json','sha256'=>hash('sha256',$bytes),
    'artifacts'=>$checks['artifacts'],'active_definitions'=>$checks['active_definitions'],
    'checksums_sha256'=>$plan['checksums_sha256'],'db_connections'=>0,'db_writes'=>0,'http_requests'=>0,'active_source_writes'=>0]);
