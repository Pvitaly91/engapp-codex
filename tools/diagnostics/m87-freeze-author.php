<?php

/** Pure finite M87 source-freeze proposal. Writes one private plan, never active files, SQL or HTTP. */
if(PHP_SAPI!=='cli'||count($argv)!==4||preg_match('/^freeze-inputs-v[1-9][0-9]*\.json$/D',$argv[1])!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1||preg_match('/^freeze-plan-v[1-9][0-9]*\.json$/D',$argv[3])!==1){
    throw new RuntimeException('M87 requires exact private freeze inputs/hash and exclusive output.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m87-local';
require $root.'/vendor/autoload.php';
use App\Support\M87ContinuousCompletionPastSimple as Package;
use App\Support\M26DetailPackage as Digest;
$json=static fn($v)=>json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$read=static function($file,$sha)use($dir){
    if(!is_string($file)||preg_match('/^[a-z0-9-]+\.json$/D',$file)!==1||preg_match('/^[a-f0-9]{64}$/D',$sha)!==1){throw new RuntimeException('M87 private input identity invalid.');}
    $p=$dir.'/'.$file;if(!is_file($p)||is_link($p)||!hash_equals($sha,hash_file('sha256',$p))){throw new RuntimeException('M87 private input drift: '.$file);}
    return json_decode(file_get_contents($p),true,flags:JSON_THROW_ON_ERROR);
};
// These pins are set only after independent main review. PENDING cannot execute.
$reviewedPins=[
    'author'=>'b1095ac259a063caad110d2997f815177cc83a75d0a23a6cd937c6d4d3aaad01', 'bank'=>'01f4e7f3e7b5399e866c21d2a796e41352f5a421c8f421fa71797a447bba1d86',
    'selection'=>'de1466a5d0eba78ba146395fa899cabf9f09c3fd65522d2642459f242f22d4c2',
    'metadata'=>'9767946a3b606b67f0da074b02470fa36a1b75c9b07f88103aa89b24a39ab297', 'coverage'=>'6ab3a98645290273745bd34cb34b887e5806934a577a04f00d781f8d40f903aa',
    'review_ab'=>'4c33ab1c1209739b259d9cb1d5709d8048450d96a91d55b4b13704e1cefdac82',
    'review_cd'=>'a1434a4535f4e768640409755b4a58713035ef833c3333b50dd512d34b2c2b14',
    'review_ef'=>'2c7c2f52a9654333caed0d4fb54b4f7c62bcb527da14f243a7f180f40b593160',
];
$reviewedInputsSha='6d6bec232bc6207839557c5f11b1c8628f5657f3e981f71040fc8ce6008c348d'; $reviewedFixtureSha='3410762321f9d3cb935043f2f9d3feb47f28c0cc2958fec6ecd996dc12456ce3';
if(preg_match('/^[a-f0-9]{64}$/D',$reviewedInputsSha)!==1||!hash_equals($reviewedInputsSha,$argv[2])){
    throw new RuntimeException('M87 freeze-input manifest remains unreviewed or changed.');
}
$pins=$read($argv[1],$argv[2]);$inputs=[];
if(array_keys($pins)!==array_keys($reviewedPins)){throw new RuntimeException('M87 exact eight-input pin map differs.');}
foreach(['author','bank','selection','metadata','coverage','review_ab','review_cd','review_ef'] as $key){
    if(!isset($pins[$key]['file'],$pins[$key]['sha256'])){throw new RuntimeException('M87 freeze input missing: '.$key);}
    if(preg_match('/^[a-f0-9]{64}$/D',$reviewedPins[$key])!==1||!hash_equals($reviewedPins[$key],$pins[$key]['sha256'])){
        throw new RuntimeException('M87 independently reviewed input pin pending or changed: '.$key);
    }
    $inputs[$key]=$read($pins[$key]['file'],$pins[$key]['sha256']);
}
$beforeSha='c9b7cffb07a0440e5051a0b7c522eb016ce4cdcc80107edf6573e8780eb151c5';
$before=$read('before-v1.json',$beforeSha);$author=$inputs['author'];$bank=$inputs['bank'];$selection=$inputs['selection'];$metadata=$inputs['metadata'];
foreach(['author','bank','selection','metadata','coverage','review_ab','review_cd','review_ef'] as $key){
    $lineage=$inputs[$key]['independent_before_sha256']??null;
    if($lineage!==$beforeSha){throw new RuntimeException('M87 independent input lineage differs: '.$key);}
}
if(($author['db_writes']??null)!==0||($author['http_requests']??null)!==0||($metadata['db_writes']??null)!==0
    ||($metadata['http_requests']??null)!==0||($metadata['projection_sha256']??null)!==$pins['author']['sha256']
    ||($bank['source_proof']['author_projection_sha256']??null)!==$pins['author']['sha256']
    ||($bank['source_proof']['widget_selection_sha256']??null)!==$pins['selection']['sha256']
    ||array_column($bank['targets']??[],'identity')!==Package::OWNERS||array_column($selection['targets']??[],'identity')!==Package::OWNERS
    ||array_column($inputs['coverage']['targets']??[],'identity')!==Package::OWNERS
    ||array_column($metadata['metadata']??[],'identity')!==Package::OWNERS
    ||($selection['actual_pool_counts']??null)!==[72,72,72,72,72,72]
    ||($selection['new_questions']??null)!==0||($selection['selected_existing']??null)!==30
    ||($bank['review_summary']['actual_filtered_pool']??null)!==432
    ||($bank['review_summary']['excluded']??null)!==402){throw new RuntimeException('M87 finite author/bank/metadata/coverage scope differs.');}
$validator='app/Support/TheoryNativeContentRelease.php';$validatorSha='23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11';
foreach([$root,$wt] as $base){if(!hash_equals($validatorSha,hash_file('sha256',$base.'/'.$validator))){throw new RuntimeException('M87 native release validator changed.');}}
$release=new App\Support\TheoryNativeContentRelease(Package::draftProfile($author['native_bindings']['bindings']));
$release->validate($author['source_before'],$author['patch'],$author['master'],$author['contract']);
Package::assertEditorial($author['source_before'],$author['patch'],$author['master'],$author['navigation_review']);
$sources=[]; $partInputs=[];
foreach(['ab','cd','ef'] as $group){
    $key='review_'.$group;$file=$pins[$key]['file'];$sha=$pins[$key]['sha256'];
    if(preg_match('/^author-'.$group.'-draft-v[1-9][0-9]*\\.json$/D',$file)!==1
        ||($author['draft_input_pins'][$file]??null)!==$sha){throw new RuntimeException('M87 author-part and review provenance differ: '.$group);}
    $partInputs[$group]=$inputs[$key];$catalog=[];
    if($group==='cd'){$catalog=$inputs[$key]['external_primary_review']??[];}
    else{foreach($inputs[$key]['parts']??[] as $part){
        $field=$group==='ab'?'source_access':'sources';
        foreach($part['editorial_review'][$field]??[] as $source){$catalog[]=$source;}
    }}
    if(!is_array($catalog)||$catalog===[]){throw new RuntimeException('M87 primary source catalogue missing: '.$group);}
    foreach($catalog as $source){
        $access=$source['access']??$source['access_mode']??null;
        if(!is_string($source['url']??null)||!str_starts_with($source['url'],'https://')||!is_string($access)||trim($access)===''){
            throw new RuntimeException('M87 source access evidence incomplete.');
        }
        $sources[]=['review_group'=>$key]+$source;
    }
}
if(count($author['draft_input_pins']??[])!==3||count($sources)<6){throw new RuntimeException('M87 finite author/source provenance incomplete.');}
$master=$author['master'];$master['status']='frozen';$master['sources']=$sources;
$master['editorial_review']['expected_http_metadata']=$metadata;
$coverage=['schema_version'=>1,'package'=>'m87-continuous-completion-past-simple','version'=>Package::VERSION,'status'=>'frozen',
    'independent_before_sha256'=>$beforeSha,'author_projection_sha256'=>$pins['author']['sha256'],'bank_projection_sha256'=>$pins['bank']['sha256'],
    'claim'=>'Finite source/model coverage only;57graded controls,4ungraded demos and30separate widget questions are not full-course coverage or CEFR certification.',
    'targets'=>[]];
$components=[];$graded=0;$full=0;$widgets=0;$demoTotal=0;$retainedTotal=0;$newTotal=0;$continuingTotal=0;$appendedTotal=0;
$expectedGraded=[6,6,11,11,11,12];$expectedFull=[2,2,3,3,3,3];$expectedDemos=[0,0,1,1,1,1];
foreach($master['lessons'] as $i=>$lesson){
    $owner=Package::OWNERS[$i];$practice=$lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];$own=[];$demos=[];$ownerFull=0;
    $oldPractice=$i>=2?json_decode($author['source_before']['targets'][$i]['before']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['body'],true,flags:JSON_THROW_ON_ERROR):[];
    foreach(['selects','choices','inputs','rephrase'] as $group){foreach($practice[$group]??[] as $n=>$task){
        $key=$group.'/'.$n;$isGraded=array_key_exists('answer',$task);
        $ref=['control_key'=>$key,'group'=>$group,'index'=>$n,
            'kind'=>$isGraded?($group==='inputs'?'full_sentence_manual_token':$group):'ungraded_demonstration',
            'task_sha256'=>Digest::digest($task),'graded'=>$isGraded];
        // Preserve legacy absence/null exactly; a new identifier is not invented for retained controls.
        if(array_key_exists('id',$task)){$ref['task_id']=$task['id'];}
        if(array_key_exists('source_index',$task)){$ref['task_source_index']=$task['source_index'];}
        if(!$isGraded){
            if($i<2||$group!=='rephrase'||$n!==0||isset($task['accepted'])||empty($task['example_original'])||empty($task['example_target'])){
                throw new RuntimeException('M87 only four original first rephrase examples may be ungraded.');
            }
            $demos[$key]=$ref;$demoTotal++;continue;
        }
        $own[$key]=$ref;$graded++;
        if($group==='inputs'){$full++;$ownerFull++;}
        if(array_key_exists('answer',$oldPractice[$group][$n]??[])){$retainedTotal++;}else{$newTotal++;}
    }}
    if(count($own)!==$expectedGraded[$i]||count($demos)!==$expectedDemos[$i]||$ownerFull!==$expectedFull[$i]){
        throw new RuntimeException('M87 per-owner graded/rephrase/demo/full-input counts differ.');
    }
    $selected=$selection['targets'][$i]['selected']??[];$audited=$bank['targets'][$i]['first_five']??[];
    if(count($selected)!==5||count($audited)!==5||!array_key_exists('level',$selection['targets'][$i])
        ||$selection['targets'][$i]['level']!==null||$author['contract']['targets'][$i]['level']!==null){throw new RuntimeException('M87 five-question NULL practice selection differs.');}
    foreach($selected as $n=>$s){
        if($s['id']!==$audited[$n]['id']||$s['uuid']!==$audited[$n]['uuid']||$s['condition']!==$audited[$n]['visible_condition_uk']
            ||$s['settled']!==$audited[$n]['actual_correct_visible']||$s['settled']!==$audited[$n]['actual_selected_visible']){
            throw new RuntimeException('M87 selected identity/condition/native settled sentence differs.');
        }
    }
    $resolved=[];$seenOwn=[];$seenWidgets=[];$seenDemos=[];$seenTopics=[];
    foreach($inputs['coverage']['targets'][$i]['rows']??[] as $spec){
        if(empty($spec['key'])||empty($spec['topic'])||empty($spec['limit'])||isset($seenTopics[$spec['key']])){throw new RuntimeException('M87 explicit unique coverage topic/limit absent.');}
        $seenTopics[$spec['key']]=true;
        foreach(['blocks','own','widgets'] as $field){if(!is_array($spec[$field]??null)||!array_is_list($spec[$field])||count(array_unique($spec[$field],SORT_REGULAR))!==count($spec[$field])){throw new RuntimeException('M87 coverage references must be finite unique lists.');}}
        if(isset($spec['demos'])&&(!is_array($spec['demos'])||!array_is_list($spec['demos'])||count(array_unique($spec['demos'],SORT_REGULAR))!==count($spec['demos']))){throw new RuntimeException('M87 demo coverage references must be a finite unique list.');}
        $theory=[];foreach($spec['blocks'] as $n){
            $block=$lesson['blocks'][$n]??null;if(!is_int($n)||!is_array($block)||in_array($block['type'],['practice-set','navigation-chips'],true)){throw new RuntimeException('M87 coverage references non-teaching block.');}
            $theory[]=['source_index'=>$n,'block_key'=>$block['key'],'type'=>$block['type'],'data_sha256'=>Digest::digest($block['data'])];
        }
        $ownRefs=[];foreach($spec['own'] as $key){if(!isset($own[$key])){throw new RuntimeException('M87 coverage references unknown graded own control or demo as scored.');}$ownRefs[]=$own[$key];$seenOwn[$key]=true;}
        $demoRefs=[];foreach($spec['demos']??[] as $key){if(!isset($demos[$key])){throw new RuntimeException('M87 coverage references unknown ungraded demonstration.');}$demoRefs[]=$demos[$key];$seenDemos[$key]=true;}
        $widgetRefs=[];foreach($spec['widgets'] as $n){
            if(!is_int($n)||!isset($selected[$n])){throw new RuntimeException('M87 coverage references unknown selected widget.');}
            $s=$selected[$n];$widgetRefs[]=['selected_index'=>$n,'id'=>$s['id'],'uuid'=>$s['uuid'],'level'=>$s['level'],'function'=>$s['coverage']];$seenWidgets[$n]=true;
        }
        $resolved[]=['key'=>$spec['key'],'source_topic'=>$spec['topic'],'theoretical_reference'=>$theory,'own_task_references'=>$ownRefs,
            'selected_widget_questions'=>$widgetRefs,'ungraded_demo_references'=>$demoRefs,
            'coverage_kind'=>$ownRefs===[]?($widgetRefs===[]?($demoRefs===[]?'theory_only':'demo_only'):'widget_only'):($widgetRefs===[]?'own_only':'own_and_widget'),
            'uncovered_or_link'=>$spec['limit'],'reference_url'=>parse_url($lesson['local_url'],PHP_URL_PATH)];
    }
    if(count($seenOwn)!==$expectedGraded[$i]||count($seenWidgets)!==5||count($seenDemos)!==$expectedDemos[$i]){throw new RuntimeException('M87 finite coverage must account for every graded control, ungraded demo and selected question.');}
    $coverage['targets'][]=['identity'=>$owner,'local_url'=>$lesson['local_url'],'technical_practice_level'=>null,
        'editorial_focus_level'=>Package::FOCUS_LEVELS[$i],'display_level'=>Package::DISPLAY_LEVELS[$i],
        'actual_selected_levels'=>array_column($selected,'level'),'own_graded_controls'=>array_values($own),'ungraded_demos'=>array_values($demos),
        'selected_widget_ids'=>array_column($selected,'id'),'selected_widget_uuids'=>array_column($selected,'uuid'),'rows'=>$resolved];
    $master['editorial_review']['lesson_reviews'][$owner]['coverage_map']=$resolved;
    $widgets+=count($selected);
    foreach($author['patch']['targets'][$i]['after']['page']['blocks'] as $slot=>$block){
        if(strlen($block['body'])>(int)$before['body_capacity']['max_bytes']){throw new RuntimeException('M87 body exceeds actual captured capacity.');}
        $prior=$author['source_before']['targets'][$i]['before']['page']['blocks'][$slot]??null;
        if($prior===null){$appendedTotal++;if($i>=2||$slot!==Package::PRACTICE_SOURCE_INDICES[$i]){throw new RuntimeException('M87 only A/B owner-bound practices may append.');}}else{$continuingTotal++;}
        $components[]=['identity'=>$owner,'source_index'=>$slot,'key'=>$lesson['blocks'][$slot]['key'],'before_type'=>$prior['type']??null,
            'type'=>$block['type'],'column'=>$block['column'],'level'=>$block['level']??null,'uuid'=>Digest::uuid($owner,$block,$slot+1),
            'before_block_sha256'=>$prior===null?null:Digest::digest($prior),'body_sha256'=>hash('sha256',$block['body']),'new_block'=>$prior===null,
            'detail_count'=>count(array_filter($lesson['details'],static fn($d)=>$d['owner_block_key']===$lesson['blocks'][$slot]['key']))];
    }
}
if([$graded,$demoTotal,$full,$widgets,$retainedTotal,$newTotal,$continuingTotal,$appendedTotal]!==[57,4,16,30,36,21,48,2]
    ||count($components)!==50||$master['lessons']!==$author['master']['lessons']){throw new RuntimeException('M87 freeze changed teaching payload or exact retained/new/demo/component counts.');}
$patch=$author['patch'];$patch['status']='frozen';$bank['status']='frozen';$bank['pending']=[];
App\Support\M87BuilderSelectionPresentation::validateAudit($bank,$patch,$author['contract']);
$publicSelection=['schema_version'=>1,'package'=>'m87-continuous-completion-past-simple-widget-selection','version'=>Package::VERSION,'status'=>'frozen',
    'independent_before_sha256'=>$beforeSha,'private_selection_input_sha256'=>$pins['selection']['sha256'],
    'scope'=>'Thirty existing reviewed conditions and canonical complete sentences; original SQL question/answer/option/hint/timestamp records are omitted.',
    'human_review'=>false,'targets'=>[]];
foreach($selection['targets'] as $i=>$target){
    $row=['identity'=>$target['identity'],'local_url'=>$author['contract']['targets'][$i]['local_url'],'bank_seeders'=>$author['contract']['targets'][$i]['bank_seeders'],
        'actual_practice_level'=>null,'selected_levels'=>$target['selected_levels'],'selected'=>[]];
    foreach($target['selected'] as $n=>$sample){
        $safe=array_intersect_key($sample,array_flip(['id','uuid','level','source','condition','translation','settled','raw_tokens','tokens','coverage']));
        if(count($safe)!==10){throw new RuntimeException('M87 selected teaching fields incomplete.');}
        $safe['snapshot_hashes']=$bank['targets'][$i]['first_five'][$n]['snapshot_hashes'];
        $safe['full_private_snapshot_sha256']=$bank['targets'][$i]['first_five'][$n]['full_private_snapshot_sha256'];$row['selected'][]=$safe;
    }$publicSelection['targets'][]=$row;
}
$editorial=['schema_version'=>1,'package'=>'m87-continuous-completion-past-simple','version'=>Package::VERSION,'status'=>'frozen','base_sha'=>Package::BASE_SHA,
    'review'=>'Agent authored and main cross-reviewed; not an independent human review or live acceptance.',
    'private_input_pins'=>$pins,'lesson_reviews'=>$master['editorial_review']['lesson_reviews'],'primary_editorial_sources'=>$sources,
    'expected_http_metadata'=>$metadata,'coverage_artifact'=>'docs/content/m87-continuous-completion-past-simple.coverage.v1.json',
    'real_page_acceptance'=>['status'=>'not_checked_before_freeze','target'=>'http://gramlyze.loc','states'=>36,'own_graded'=>57,'ungraded_demos'=>4,'full_inputs'=>16,'manual_token_size_cases'=>64,'widget_questions'=>30],
    'boundaries'=>['Six UK theory owners only; original titles, ancestry, metadata and continuing native identities retained.',
        'Four continuing practices and two appended native practices;36retained plus21new graded controls,4retained ungraded demos,16full manual/token inputs,30separate reused widget questions. No question-family writes.',
        'Actual NULL SQL practice levels remain separate from display range and editorial focus. Six exact existing all-levels banks independently retain72questions each; no course24-bank substitution or insertion.',
        'No view/design/native validator/formatter/grader/engine changes. Explicit semantic details policy, no global word threshold.',
        'Public author-parts export only schema/base/BEFORE lineage, unchanged teaching parts and the CD primary catalogue. Agent/runtime/revision bookkeeping remains private; original private raw input SHA is pinned and all teaching parts/task maps/source catalogues remain exact.',
        'Courses, EN/PL, category descriptions, standalone tests/theory help, saved tests and personal progress are read-only consumers.',
        'Freeze is source-only. Guarded local apply, fresh exact diff/backup/transaction/no-op/cleanup and real browser acceptance remain separate gates.']];
$outputs=[Package::BEFORE=>$json($author['source_before']),Package::SOURCE=>$json($patch),Package::MASTER=>$json($master),
    Package::CONTRACT=>$json($author['contract']),Package::BINDINGS=>$json($author['native_bindings']),Package::NAVIGATION=>$json($author['navigation_review']),
    'docs/content/m87-continuous-completion-past-simple.components.v1.json'=>$json($components),'docs/content/m87-continuous-completion-past-simple.tasks.v1.json'=>$json($master['editorial_review']['task_map']),
    'docs/content/m87-continuous-completion-past-simple.editorial.v1.json'=>$json($editorial),'docs/content/m87-sentence-builder-bank-audit.v1.json'=>$json($bank),
    'docs/content/m87-continuous-completion-past-simple.coverage.v1.json'=>$json($coverage),'docs/content/m87-widget-selection.v1.json'=>$json($publicSelection)];
$plain=static fn($v)=>trim(html_entity_decode(strip_tags(preg_replace('~<br\s*/?>|</(?:p|li|div|tr)>~i',"\n",(string)$v)),ENT_QUOTES|ENT_HTML5,'UTF-8'));
$walk=static function(array $data)use(&$walk,$plain):string{$out='';foreach($data as $key=>$value){
    if(in_array($key,['id','source_index','key','type','column','accent','color','icon','current','design','linked_practice','seeder_classes','m87_native_v1','m87_detail_key','level'],true)){continue;}
    if(is_array($value)){$out.=$walk($key==='feedback'?array_values(array_unique($value)):$value);continue;}
    if($value===null||is_bool($value)||trim((string)$value)===''){continue;}$out.=$plain($value)."\n\n";
}return $out;};
$md="# M87 — Present Continuous: питання й часові вирази; Past Simple: форми, заперечення, питання й часові вирази\n\nАвторська редакція1.0.0. Source-only навчальний пакет; це не звіт про live acceptance. Канонічний дизайн і оцінювання незмінні.\n\n";
foreach($master['lessons'] as $i=>$lesson){$md.='## '.$lesson['preserved_h1']."\n\n[Локальний урок](".$lesson['local_url'].")\n\n".$plain($lesson['subtitle_html'])."\n\n";
    foreach($lesson['blocks'] as $block){$md.='### '.$block['key']."\n\n".$walk($block['data']);}
    $md.="### Окремі п’ять питань конструктора\n\n";foreach($publicSelection['targets'][$i]['selected'] as $s){$md.='- '.$s['condition']."\n  Ключ: ".$s['settled']."\n  Переклад: ".$s['translation']."\n\n";}
    $md.="### Межі оцінювання\n\n";foreach($coverage['targets'][$i]['rows'] as $row){$md.='- '.$row['source_topic'].': '.$row['uncovered_or_link']."\n";}$md.="\n";
}
$outputs['docs/content/m87-continuous-completion-past-simple.v1.0.0.uk.md']=rtrim($md)."\n";
foreach(['ab','cd','ef'] as $group){
    $export=$partInputs[$group];
    // Export only teaching parts and their primary catalogue; private agent/runtime bookkeeping stays local.
    $export=array_intersect_key($export,array_flip(['schema_version','base_sha','independent_before_sha256','parts','external_primary_review']));
    if(($export['parts']??null)!==($partInputs[$group]['parts']??null)){throw new RuntimeException('M87 public author-part export changed teaching parts.');}
    $outputs['docs/content/m87-author-parts/'.$group.'.v1.json']=$json($export);
}
$definitions=[];
foreach($patch['targets'] as $i=>$target){$path=$target['path'];$captured=$before['definitions'][$target['identity']];
    foreach([$root,$wt] as $base){if(!is_file($base.'/'.$path)||is_link($base.'/'.$path)||file_get_contents($base.'/'.$path)!==$captured['bytes']){throw new RuntimeException('M87 active definition drift before proposal.');}}
    if($captured['definition']!==$author['source_before']['targets'][$i]['before']){throw new RuntimeException('M87 public BEFORE differs from private source capture.');}
    $definitions[$path]=$json($target['after']);
}
$eol=static function($path,$bytes){if(!mb_check_encoding($bytes,'UTF-8')||str_contains($bytes,"\r")||str_starts_with($bytes,"\xEF\xBB\xBF")
    ||!str_ends_with($bytes,"\n")||str_ends_with($bytes,"\n\n")||preg_match('/[\t ]+$/m',$bytes)){throw new RuntimeException('M87 raw UTF-8/LF/EOF policy differs: '.$path);}};
$privacy=static function($value)use(&$privacy){if(!is_array($value)){return;}foreach($value as $key=>$child){
    if(is_string($key)&&preg_match('/^(?:created_at|updated_at|started_at|finished_at|served_root|source_worktree|sql_before|sql_after_http|password|db_password|secret|credentials|app_key|api_key|access_token|refresh_token|authorization|cookie|cookies|request_headers|response_headers|nonce|proof_nonce|physical|runtime_identity|option_matches|materialized|allocation)$/iD',$key)){throw new RuntimeException('M87 public source contains private runtime/SQL field: '.$key);}
    $privacy($child);
}};
$checks=['schema_version'=>1,'package'=>'m87-continuous-completion-past-simple','version'=>Package::VERSION,'status'=>'frozen','hash_algorithm'=>'SHA-256 raw UTF-8 bytes',
    'pre_hash_checks'=>['utf8_bom'=>false,'eol'=>'LF','exactly_one_final_lf'=>true,'trailing_whitespace'=>false],
    'independent_before_sha256'=>$beforeSha,'bank_targets_sha256'=>Digest::digest($bank['targets']),'unchanged_native_validator_sha256'=>$validatorSha,
    'artifacts'=>[],'active_definitions'=>[],'notes'=>$editorial['boundaries']];
foreach(['artifacts'=>$outputs,'active_definitions'=>$definitions] as $group=>$files){foreach($files as $path=>$bytes){
    $eol($path,$bytes);if(str_ends_with($path,'.json')){$privacy(json_decode($bytes,true,flags:JSON_THROW_ON_ERROR));}
    $checks[$group][]=['path'=>$path,'sha256'=>hash('sha256',$bytes),'bytes'=>strlen($bytes)];
}}
$fixture='tests/Fixtures/m87-owned-source-hunks.json';$fixtureBytes=file_get_contents($wt.'/'.$fixture);$fixtureData=json_decode($fixtureBytes,true,flags:JSON_THROW_ON_ERROR);
if(preg_match('/^[a-f0-9]{64}$/D',$reviewedFixtureSha)!==1||!hash_equals($reviewedFixtureSha,hash('sha256',$fixtureBytes))
    ||($fixtureData['owned_added_registration_lines']??null)!==12||($fixtureData['independent_db_before_sha256']??null)!==$beforeSha){throw new RuntimeException('M87 additive fixture scope or independently reviewed raw hash differs.');}
$checks['owned_source_fixture']=['path'=>$fixture,'sha256'=>hash('sha256',$fixtureBytes)];
$outputs[Package::CHECKSUMS]=$json($checks);
$release->validate($author['source_before'],$patch,$master,$author['contract']);Package::assertEditorial($author['source_before'],$patch,$master,$author['navigation_review']);
$plan=['schema_version'=>1,'purpose'=>'M87 source-only freeze proposal, no materialization/apply/live acceptance','base_sha'=>Package::BASE_SHA,
    'input_pins_file'=>$argv[1],'input_pins_sha256'=>$argv[2],'independent_before_sha256'=>$beforeSha,'outputs'=>[],
    'package_hashes'=>[],'bank_audit_sha256'=>hash('sha256',$outputs[App\Support\M87BuilderSelectionPresentation::AUDIT]),
    'bank_targets_sha256'=>Digest::digest($bank['targets']),'db_connections'=>0,'db_writes'=>0,'http_requests'=>0,'active_source_writes'=>0];
foreach($outputs+$definitions as $path=>$bytes){$plan['outputs'][]=['path'=>$path,'bytes'=>$bytes,'sha256'=>hash('sha256',$bytes),
    'root_before_sha256'=>is_file($root.'/'.$path)?hash_file('sha256',$root.'/'.$path):null,
    'worktree_before_sha256'=>is_file($wt.'/'.$path)?hash_file('sha256',$wt.'/'.$path):null];}
foreach(['BEFORE','SOURCE','MASTER','CONTRACT','CHECKSUMS','BINDINGS','NAVIGATION'] as $name){$path=constant(Package::class.'::'.$name);$plan['package_hashes'][$name.'_SHA']=hash('sha256',$outputs[$path]);}
$out=$dir.'/'.$argv[3];if(file_exists($out)||is_link($out)){throw new RuntimeException('M87 exclusive freeze proposal collision.');}
$bytes=$json($plan);$h=fopen($out,'x');if(!$h){throw new RuntimeException('M87 proposal output unavailable.');}try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M87 proposal write incomplete.');}}finally{fclose($h);}
echo $json(['file'=>$argv[3],'sha256'=>hash('sha256',$bytes),'public_outputs'=>count($outputs),'active_definitions'=>count($definitions),
    'owners'=>6,'own_graded'=>$graded,'ungraded_demos'=>$demoTotal,'retained_graded'=>$retainedTotal,'new_graded'=>$newTotal,
    'full_inputs'=>$full,'continuing_blocks'=>$continuingTotal,'appended_blocks'=>$appendedTotal,'separate_widget_questions'=>$widgets,'db_writes'=>0,'active_source_writes'=>0]);
