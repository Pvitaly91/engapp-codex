<?php

/** Finite artifact orchestration; emits an exclusive private plan, never active-source/SQL writes. */
if (PHP_SAPI!=='cli'||count($argv)!==7
    ||preg_match('/^author-projection-v[1-9][0-9]*\.json$/D',$argv[1])!==1
    ||preg_match('/^bank-audit-projection-v[1-9][0-9]*\.json$/D',$argv[3])!==1
    ||preg_match('/^prospective-metadata-v[1-9][0-9]*\.json$/D',$argv[5])!==1
    ||array_filter([$argv[2],$argv[4],$argv[6]],static fn($s)=>preg_match('/^[a-f0-9]{64}$/D',$s)!==1)) {
    throw new RuntimeException('M81 needs exact reviewed author/bank/metadata basenames and hashes.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m81-local';
require $root.'/vendor/autoload.php';require_once $wt.'/app/Support/M81TransformationsCommonMistakes.php';
use App\Support\M81TransformationsCommonMistakes as Package;
$read=static function($name,$sha)use($dir) {
    $path=$dir.'/'.$name;if (is_link($path)||!is_file($path)||!hash_equals($sha,hash_file('sha256',$path))) { throw new RuntimeException('M81 reviewed private artifact drift.'); }
    return json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
};
$author=$read($argv[1],$argv[2]);$bank=$read($argv[3],$argv[4]);$metadata=$read($argv[5],$argv[6]);
(new App\Support\TheoryNativeContentRelease(Package::draftProfile($author['native_bindings']['bindings'])))
    ->validate($author['source_before'],$author['patch'],$author['master'],$author['contract']);
Package::assertEditorial($author['source_before'],$author['patch'],$author['master']);
if (($bank['source_proof']['author_projection_sha256']??null)!==$argv[2]
    ||array_column($bank['targets']??[],'identity')!==Package::OWNERS||($metadata['projection_sha256']??null)!==$argv[2]
    ||array_column($metadata['metadata']??[],'identity')!==Package::OWNERS
    ||array_map(static fn($t)=>count($t['first_five']),$bank['targets'])!==[5,5,5,5,5,5]) {
    throw new RuntimeException('M81 bank/metadata exact author lineage differs.');
}
$master=$author['master'];$master['status']='frozen';$master['editorial_review']['expected_http_metadata']=$metadata;
$patch=$author['patch'];$patch['status']='frozen';$bank['status']='frozen';$bank['pending']=[];
$editorial=['schema_version'=>1,'base_sha'=>Package::BASE_SHA,'version'=>'1.0.0','status'=>'frozen',
    'review'=>'Main independently read all six lessons/tasks/maps and reviewed agent findings; agent review is not human review. Original new tasks, no publisher exercises copied.',
    'author_projection_sha256'=>$argv[2],'draft_parts_sha256'=>$author['draft_sha256'],
    'lesson_reviews'=>$master['editorial_review']['lesson_reviews'],'projection_edits'=>$master['editorial_review']['projection_edits'],
    'expected_http_metadata'=>$metadata,
    'real_page_acceptance'=>['status'=>'not_checked_before_freeze','required_after_guarded_apply'=>true,'target'=>'http://gramlyze.loc',
        'widths'=>[1440,390,320],'themes'=>['light','dark'],'target_states'=>36,'full_inputs'=>12,'manual_token_size_cases'=>48,
        'widget_questions'=>30,'autonomous_html_preview_required'=>false,'user_screenshots_required'=>false],
    'main_language_review'=>[
        'A: unless preserves the exact condition without a converse guarantee; wish has an explicit event/reference time; would rather preserves the actor; had better warning force is justified by a visible consequence.',
        'B: relative pronouns follow clause role, not place naming; reduction preserves the actor; cause/concession/purpose/result and exam ownership/time remain explicit. Both versions of transformation pairs are translated.',
        'C: countability, identifiable reference and number remain distinct; some advice never implies exactly one unit by itself; a few/a little do not guarantee sufficiency; exact small quantities qualify the reviewed comparisons.',
        'D: inversion preserves auxiliary/person/tense and the scope of adverbial only; subject-only is separate. Cleft retains the explicit not me contrast; emphatic do confirms a fact, not every sense of intensity. Neutral often order is not a universal literary prohibition; Do be careful remains valid.',
        'E: diagnostic intent precedes minimal article correction. Sound-based a/an, known referent versus general reference, institutional school and already-correct coffee are explicit, not arbitrary mandatory changes.',
        'F: neutral word order is scoped, not global; subject questions, embedded questions and the main clause keep their roles. Ordinary be/modal and lexical do-support stay distinct; needed subjects, negative words and adverbs are not removed to make a sentence pass.',
    ],
    'main_source_access'=>[
        ['url'=>'https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/indefinite-article','access'=>'Direct primary editorial main body read: singular indefinite reference, sound-based a/an, no indefinite article with ordinary plural/uncount meanings. Named editorial replies are distinguished from learner comments; no exercise copied.'],
        ['url'=>'https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/quantifiers','access'=>'Main and author direct fetches of this exact starting URL timed out. It is not claimed read; the distinct accessible few/a few primary URL is recorded separately in the lesson ledger.'],
        ['url'=>'https://developers.google.com/search/docs/fundamentals/creating-helpful-content','access'=>'Direct primary guidance read for people-first helpful content; not a claim about rankings or indexing.'],
    ],
    'boundaries'=>[
        '36 new own graded tasks:12 form,12 meaning,12 full manual/token inputs; no existing own practice was present in independently captured source or current SQL.',
        'Six widgets/30 selected existing questions are separate from own tasks. No new question, answer, option, link, progress or standalone membership writes.',
        'Twelve continuing box type/heading transitions are explicit; UUID/id/order/column/SQL NULL levels remain exact. All six retained levels and right mistakes/summary stay unchanged.',
        'All four source table functions on each of A-D remain mapped and bilingual; E/F retain diagnostic steps and minimal-correction functions.',
        'Finite reviewed aliases are not an unrestricted paraphrase grader. Native case/punctuation/formatting and all visual/engine sources remain unchanged.',
        'Known defective old keys/options and the actual native No formatter defect are excluded from the finite widget five, not rewritten or silently reformatted.',
        'Meaningful details have finite point owners; essential rules, translations and visible control criteria stay in basic.',
        'Freeze and isolated/offline probes are not actual browser or working-local acceptance; those remain separate required receipts.',
    ]];
$json=static fn($v)=>json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$outputs=[Package::BINDINGS=>$json($author['native_bindings']),Package::BEFORE=>$json($author['source_before']),
    Package::SOURCE=>$json($patch),Package::MASTER=>$json($master),Package::CONTRACT=>$json($author['contract']),
    'docs/content/m81-transformations-common-mistakes.components.v1.json'=>$json($author['component_map']),
    'docs/content/m81-transformations-common-mistakes.tasks.v1.json'=>$json($author['task_map']),
    'docs/content/m81-transformations-common-mistakes.editorial.v1.json'=>$json($editorial),
    'docs/content/m81-sentence-builder-bank-audit.v1.json'=>$json($bank)];
$plain=static fn($v)=>trim(html_entity_decode(strip_tags((string)$v),ENT_QUOTES|ENT_HTML5,'UTF-8'));
$walk=static function($data)use(&$walk,$plain) {
    $out='';foreach ($data as $key=>$value) {
        if (in_array($key,['id','source_index','key','type','column','accent','color','icon','current','design','linked_practice','seeder_classes','m81_native_v1','m81_detail_key','level'],true)) { continue; }
        if (is_array($value)) { $out.=$key==='feedback'?"\nПояснення відповідей:\n\n":'';$out.=$walk($value);continue; }
        if (is_bool($value)||$value===null||trim((string)$value)==='') { continue; }
        $label=['answer'=>'Ключ','question'=>'Умова','prompt'=>'Умова','translation'=>'Переклад','title'=>'Пункт','subtitle'=>'Пояснення','text'=>'Пояснення','en'=>'Англійською','ua'=>'Українською','url'=>'Посилання'][$key]??'';
        $out.=($label===''?'':$label.': ').$plain($value)."\n\n";
    }return $out;
};
$md="# M81 — трансформації та діагностика типових помилок\n\nАвторська редакція 1.0.0. Чинний канонічний дизайн і механізм оцінювання не змінюються.\n\n";
foreach ($master['lessons'] as $lesson) {
    $md.='## '.$lesson['preserved_h1']."\n\n".$lesson['local_url']."\n\n".$plain($lesson['subtitle_html'])."\n\n";
    foreach ($lesson['blocks'] as $block) { $md.='### '.$block['key']."\n\n".$walk($block['data']); }
    foreach ($lesson['details'] as $detail) { $md.='### Докладніше: '.$detail['title']."\n\n".$walk($detail['fragment']['data']); }
}
$outputs['docs/content/m81-transformations-common-mistakes.v1.0.0.uk.md']=rtrim($md)."\n";
$definitions=[];foreach ($patch['targets'] as $target) { $definitions[$target['path']]=$json($target['after']); }
$checks=['schema_version'=>1,'package'=>'m81-transformations-common-mistakes','version'=>'1.0.0','status'=>'frozen',
    'hash_algorithm'=>'SHA-256 raw UTF-8 bytes','pre_hash_checks'=>['utf8_bom'=>false,'eol'=>'LF','exactly_one_final_lf'=>true,'trailing_whitespace'=>false],
    'artifacts'=>[],'active_definitions'=>[],'author_inputs'=>[],'notes'=>$editorial['boundaries']];
$eol=static function($path,$bytes) {
    if (str_contains($bytes,"\r")||str_starts_with($bytes,"\xEF\xBB\xBF")||!str_ends_with($bytes,"\n")||str_ends_with($bytes,"\n\n")||preg_match('/[\t ]+$/m',$bytes)) {
        throw new RuntimeException('M81 pre-hash UTF8/LF/single EOF policy failed: '.$path);
    }
};
foreach ([$outputs,$definitions] as $i=>$group) { foreach ($group as $path=>$bytes) { $eol($path,$bytes);$checks[$i===0?'artifacts':'active_definitions'][]=['path'=>$path,'sha256'=>hash('sha256',$bytes),'bytes'=>strlen($bytes)]; } }
$inputs=['docs/content/m81-widget-review-draft.v1.json','docs/content/m81-widget-selection.v1.json'];
foreach ($author['draft_sha256'] as $name=>$sha) {
    $path='docs/content/m81-author-parts/'.$name.'.v1.json';if (!hash_equals($sha,hash_file('sha256',$wt.'/'.$path))) { throw new RuntimeException('M81 reviewed author input changed.'); }$inputs[]=$path;
}
foreach ($inputs as $path) {
    if (!is_file($wt.'/'.$path)||is_link($wt.'/'.$path)) { throw new RuntimeException('M81 reviewed input unavailable.'); }
    $bytes=file_get_contents($wt.'/'.$path);$eol($path,$bytes);$checks['author_inputs'][]=['path'=>$path,'sha256'=>hash('sha256',$bytes),'bytes'=>strlen($bytes)];
}
$selectionPin=$bank['source_proof']['author_review_sha256']['docs/content/m81-widget-selection.v1.json']??null;
if (!is_string($selectionPin)||!hash_equals($selectionPin,hash_file('sha256',$wt.'/docs/content/m81-widget-selection.v1.json'))) { throw new RuntimeException('M81 bank selection provenance changed.'); }
$outputs[Package::CHECKSUMS]=$json($checks);$beforeBytes=[];
foreach ($outputs as $path=>$bytes) { if (file_exists($wt.'/'.$path)||is_link($wt.'/'.$path)) { throw new RuntimeException('M81 frozen output already exists: '.$path); } }
foreach ($definitions as $path=>$bytes) {
    $old=array_values(array_filter($author['source_before']['targets'],static fn($t)=>$t['path']===$path));
    if (count($old)!==1||json_decode(file_get_contents($wt.'/'.$path),true,flags:JSON_THROW_ON_ERROR)!==$old[0]['before']) { throw new RuntimeException('M81 active source changed before freeze.'); }
    $beforeBytes[$path]=file_get_contents($wt.'/'.$path);
}
$plan=['schema_version'=>1,'author_projection_sha256'=>$argv[2],'bank_projection_sha256'=>$argv[4],
    'prospective_metadata_sha256'=>$argv[6],'outputs'=>$outputs+$definitions,'before_bytes'=>$beforeBytes,
    'artifacts'=>$checks['artifacts'],'active_definitions'=>$checks['active_definitions'],'checksums_sha256'=>hash('sha256',$outputs[Package::CHECKSUMS]),
    'db_connections'=>0,'db_writes'=>0,'http_requests'=>0];
$file=$dir.'/freeze-plan-v2.json';if (file_exists($file)||is_link($file)) { throw new RuntimeException('M81 exclusive freeze plan already exists.'); }
$bytes=$json($plan);$h=fopen($file,'x');if (!$h) { throw new RuntimeException('M81 private freeze plan unavailable.'); }
try { if (fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)) { throw new RuntimeException('M81 freeze plan incomplete.'); } } finally { fclose($h); }
echo $json(['file'=>'freeze-plan-v2.json','sha256'=>hash('sha256',$bytes),'artifacts'=>$checks['artifacts'],
    'active_definitions'=>$checks['active_definitions'],'checksums_sha256'=>$plan['checksums_sha256'],'db_writes'=>0,'active_source_writes'=>0]);
