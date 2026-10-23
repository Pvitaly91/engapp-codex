<?php

/** Pure finite successor compiler: no bootstrap, SQL, HTTP or active source writes. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) { throw new RuntimeException('M79 revision is CLI-only with no arguments.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
$private = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m79-local';
$pins = [
    'database/content-patches/m79-question-types-completion.v1.json'=>'0e0b1406a376e392c4b12028e589ec099e4a675599b6907a873b738474e51d11',
    'docs/content/m79-question-types-completion.v1.json'=>'1941b5bc4976d9526145e32216f429b5bde81a6776718c894634a0e0ee9f532f',
    'docs/content/m79-source-checksums.v1.json'=>'16a473c3cd5a36eece553d6260e011f1f5d0af2d864dd40a095c9d2f15cdff17',
    'docs/content/m79-question-types-completion.components.v1.json'=>'a19a60057869554808c5b8d508b719ca4a73e6d61ebd75382d31379fe54bebb4',
    'docs/content/m79-question-types-completion.tasks.v1.json'=>'f5012be1e9b85d8462a31018d451b003e433a2691f8c538828532d1694b6c191',
    'docs/content/m79-question-types-completion.editorial.v1.json'=>'13ad14dbe3a293fb094aba09f6a49ee5ef2a517ddc929b8e8d2df6602367a8f6',
];
$read = static function ($path) use ($root, $pins) {
    if (is_link($root.'/'.$path) || !is_file($root.'/'.$path) || !hash_equals($pins[$path], hash_file('sha256', $root.'/'.$path))) {
        throw new RuntimeException('M79 immutable predecessor differs: '.$path);
    }
    return json_decode(file_get_contents($root.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
};
$json = static fn ($v) => json_encode($v, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$patch = $read(array_keys($pins)[0]); $oldPatch = $patch;
$master = $read(array_keys($pins)[1]); $oldMaster = $master;
$checks = $read(array_keys($pins)[2]); $maps = $read(array_keys($pins)[3]);
$tasks = $read(array_keys($pins)[4]); $editorial = $read(array_keys($pins)[5]);
$transitions = []; $labels = [];
foreach ([0, 2] as $i) {
    $owner = $master['lessons'][$i]['identity'];
    $block = &$master['lessons'][$i]['blocks'][11]['data'];
    $bodyBefore = $patch['targets'][$i]['after']['page']['blocks'][11]['body'];
    $body = json_decode($bodyBefore, true, flags: JSON_THROW_ON_ERROR);
    if (count($block['selects']) !== 3 || $body['selects'] !== $block['selects']) { throw new RuntimeException('M79 exact three-select predecessor mismatch.'); }
    foreach ($block['selects'] as $n => &$task) {
        $oldLabel = $task['label']; $prompt = $task['prompt'];
        if (!is_string($prompt) || trim($prompt) !== $prompt || $prompt === '' || str_starts_with($oldLabel, $prompt)) { throw new RuntimeException('M79 explicit missing visible criterion differs.'); }
        $task['label'] = $prompt.' '.$oldLabel;
        $body['selects'][$n]['label'] = $task['label'];
        $found = 0;
        foreach ($tasks[$owner] as &$map) {
            if (($map['after_group'] ?? null) === 'selects' && ($map['after_group_index'] ?? null) === $n) {
                if ($map['after_task'] !== $oldMaster['lessons'][$i]['blocks'][11]['data']['selects'][$n]) { throw new RuntimeException('M79 frozen task map mismatch.'); }
                $map['after_task']['label'] = $task['label']; ++$found;
            }
        }
        unset($map);
        if ($found !== 1) { throw new RuntimeException('M79 finite task map identity absent/repeated.'); }
        $labels[] = ['lesson_index'=>$i, 'identity'=>$owner, 'block_source_index'=>11, 'group'=>'selects', 'group_index'=>$n,
            'prompt'=>$prompt, 'before_label'=>$oldLabel, 'after_label'=>$task['label'], 'other_task_fields_identical'=>true];
    }
    unset($task, $block);
    $bodyAfter = rtrim($json($body), "\n");
    // Body strings use the original release's compact JSON serialization.
    if ($bodyBefore !== json_encode(json_decode($bodyBefore, true, flags: JSON_THROW_ON_ERROR), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)) {
        throw new RuntimeException('M79 exact compact body serializer differs.');
    }
    $bodyAfter = json_encode($body, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
    $patch['targets'][$i]['after']['page']['blocks'][11]['body'] = $bodyAfter;
    $mapFound = 0;
    foreach ($maps as &$map) { if ($map['identity'] === $owner && $map['source_index'] === 11) {
        if ($map['body_sha256'] !== hash('sha256', $bodyBefore)) { throw new RuntimeException('M79 component predecessor hash differs.'); }
        $map['body_sha256'] = hash('sha256', $bodyAfter); ++$mapFound;
    } }
    unset($map);
    if ($mapFound !== 1) { throw new RuntimeException('M79 practice component map absent/repeated.'); }
    $transitions[] = ['lesson_index'=>$i, 'identity'=>$owner, 'definition_path'=>$patch['targets'][$i]['path'],
        'page_id'=>$patch['targets'][$i]['page_binding']['id'], 'page_category_id'=>22, 'locale'=>'uk',
        'block_source_index'=>11, 'type'=>'practice-set', 'column'=>'left',
        'before_body_sha256'=>hash('sha256', $bodyBefore), 'after_body_sha256'=>hash('sha256', $bodyAfter),
        'allowed_field'=>'body', 'only_six_select_labels_changed'=>true];
}
$master['version'] = '1.0.1'; $editorial['version'] = '1.0.1';
$editorial['practice_criteria_revision'] = ['reason'=>'Actual native select DOM renders label but not prompt; move each A/C selection criterion into its visible label.',
    'learning_delta'=>'Six prompt prefixes only; prompt fields and all answers, options, feedback, aliases, task identities, types and counts remain unchanged.',
    'view_changes'=>0, 'engine_changes'=>0, 'sql_body_updates'=>2, 'sql_inserts'=>0];
$outputs = [
    'database/content-patches/m79-question-types-completion.v1.0.1.json'=>$json($patch),
    'docs/content/m79-question-types-completion.v1.0.1.json'=>$json($master),
    'docs/content/m79-question-types-completion.components.v1.0.1.json'=>$json($maps),
    'docs/content/m79-question-types-completion.tasks.v1.0.1.json'=>$json($tasks),
    'docs/content/m79-question-types-completion.editorial.v1.0.1.json'=>$json($editorial),
];
$md = "# M79 — типи питань: редакція 1.0.1\n\nТочне виправлення видимості шести умов вибору. Дизайн і механізм оцінювання не змінені.\n\n";
$plain = static fn ($v) => trim(html_entity_decode(strip_tags((string) $v), ENT_QUOTES|ENT_HTML5, 'UTF-8'));
$walk = static function ($data) use (&$walk, $plain) { $out=''; foreach ($data as $key=>$value) {
    if (in_array($key,['id','source_index','key','type','column','accent','icon','current','design','linked_practice','seeder_classes','m79_native_v1','m79_detail_key','level'],true)) { continue; }
    if (is_array($value)) { $out.=($key==='feedback'?"\nПояснення відповідей:\n\n":'').$walk($value); continue; }
    if (is_bool($value)||$value===null||trim((string)$value)==='') { continue; }
    $out.=$plain($value)."\n\n";
} return $out; };
foreach ($master['lessons'] as $lesson) {
    $md.='## '.$lesson['preserved_h1']."\n\n".$lesson['local_url']."\n\n".$plain($lesson['subtitle_html'])."\n\n";
    foreach ($lesson['blocks'] as $block) { $md.='### '.$block['key']."\n\n".$walk($block['data']); }
    foreach ($lesson['details'] as $detail) { $md.='### Докладніше: '.$detail['title']."\n\n".$walk($detail['fragment']['data']); }
}
$outputs['docs/content/m79-question-types-completion.v1.0.1.uk.md'] = rtrim($md)."\n";
$definitions=[]; $beforeBytes=[];
foreach ($patch['targets'] as $i=>$target) {
    $path=$target['path']; $bytes=file_get_contents($root.'/'.$path);
    if (is_link($root.'/'.$path)||json_decode($bytes,true,flags:JSON_THROW_ON_ERROR)!==$oldPatch['targets'][$i]['after']) { throw new RuntimeException('M79 active predecessor definition differs.'); }
    if (in_array($i,[0,2],true)) { $definitions[$path]=$json($target['after']); $beforeBytes[$path]=$bytes; }
}
$checks['version']='1.0.1'; $checks['artifacts']=[];
foreach (['docs/content/m79-native-bindings.v1.json','docs/content/m79-before-source.v1.json','docs/content/theory-sentence-builders/m79-question-types-completion.v1.json','docs/content/m79-sentence-builder-bank-audit.v1.json'] as $path) {
    $checks['artifacts'][]=['path'=>$path,'sha256'=>hash_file('sha256',$root.'/'.$path),'bytes'=>filesize($root.'/'.$path)];
}
foreach ($outputs as $path=>$bytes) { $checks['artifacts'][]=['path'=>$path,'sha256'=>hash('sha256',$bytes),'bytes'=>strlen($bytes)]; }
foreach ($checks['active_definitions'] as &$entry) {
    if (isset($definitions[$entry['path']])) { $entry['sha256']=hash('sha256',$definitions[$entry['path']]); $entry['bytes']=strlen($definitions[$entry['path']]); }
}
unset($entry);
$outputs['docs/content/m79-source-checksums.v1.0.1.json']=$json($checks);
$ledger=['schema_version'=>1,'package'=>'m79-practice-criteria-revision','version'=>'1.0.1','status'=>'frozen',
    'reason'=>'Actual select prompt is not rendered by the unchanged native view; criteria must be in the rendered label.',
    'predecessor_pins'=>$pins,'active_artifacts'=>[], 'transitions'=>$transitions,'labels'=>$labels,
    'invariants'=>['only_changed_learning_fields'=>'Six A/C selects label prefixes', 'unchanged_prompt_fields'=>true,
        'answers_options_feedback_aliases_ids_source_indices_unchanged'=>true,'other_four_lessons_identical'=>true,
        'old_artifacts_preserved'=>true,'author_parts_preserved'=>true,'view_changes'=>0,'grader_changes'=>0,'db_body_updates'=>2,'db_inserts'=>0]];
foreach ($outputs as $path=>$bytes) { $ledger['active_artifacts'][$path]=['sha256'=>hash('sha256',$bytes),'bytes'=>strlen($bytes)]; }
$outputs['docs/content/m79-practice-criteria-revision.v1.0.1.json']=$json($ledger);
foreach ($outputs+$definitions as $path=>$bytes) {
    if (str_contains($bytes,"\r")||str_starts_with($bytes,"\xEF\xBB\xBF")||!str_ends_with($bytes,"\n")||str_ends_with($bytes,"\n\n")||preg_match('/[\t ]+$/m',$bytes)) { throw new RuntimeException('M79 successor UTF8/LF/EOF violation: '.$path); }
    if (isset($outputs[$path])&&(file_exists($root.'/'.$path)||is_link($root.'/'.$path))) { throw new RuntimeException('M79 immutable successor already exists: '.$path); }
}
$plan=['schema_version'=>1,'kind'=>'M79_practice_criteria_revision_plan','predecessor_pins'=>$pins,
    'outputs'=>$outputs+$definitions,'before_bytes'=>$beforeBytes,'ledger_sha256'=>hash('sha256',end($outputs)),
    'changed_practice_bodies'=>2,'changed_labels'=>6,'db_connections'=>0,'db_writes'=>0,'http_requests'=>0];
$path=$private.'/practice-criteria-revision-plan-v1.json';
if (file_exists($path)||is_link($path)) { throw new RuntimeException('M79 exclusive successor plan already exists.'); }
$bytes=$json($plan); $h=fopen($path,'x');
if (!$h) { throw new RuntimeException('M79 exclusive successor plan cannot be created.'); }
try { if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)) { throw new RuntimeException('M79 successor plan incomplete.'); } } finally { fclose($h); }
echo $json(['plan'=>basename($path),'plan_sha256'=>hash('sha256',$bytes),'ledger_sha256'=>$plan['ledger_sha256'],
    'artifacts'=>$ledger['active_artifacts'],'labels'=>6,'bodies'=>2,'db_connections'=>0,'db_writes'=>0,'active_source_writes'=>0]);
