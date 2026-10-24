<?php

/** Actual native read-only registry command, full SQL equivalence, finite pool snapshots and clone-only checks. */
require __DIR__.'/frozen-readonly-profile.php';
$profileCheck=($argv[1]??null)==='--profile-check-only';
if(!$profileCheck&&(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==5
    ||!in_array($argv[1],['m84','registry'],true)
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[2])!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$argv[3])!==1
    ||preg_match('/^(?:m84|registry)-sentence-builders-v[1-9][0-9]*\.json$/D',$argv[4])!==1
    ||!str_starts_with($argv[4],$argv[1].'-sentence-builders-'))){
    throw new RuntimeException('M84 scope, independent final receipt/hash and scope-matched exclusive output required.');
}
$context=$profileCheck?[]:['final_name'=>$argv[2],'final_sha'=>$argv[3],'receipt_name'=>$argv[4]];
if(!$profileCheck){$argv=array_slice($argv,0,2);}
$setup=<<<'PHP'
$private=$root.'/storage/app/seo-m84-local';
$hash=static function(string $path):string{
    if(!is_file($path)||is_link($path)){throw new RuntimeException('M84 regular immutable receipt/source missing.');}
    $sha=hash_file('sha256',$path);if(!is_string($sha)){throw new RuntimeException('M84 receipt/source hash unavailable.');}return $sha;
};
$finalPath=$private.'/'.$profileContext['final_name'];$finalSha=$profileContext['final_sha'];
if(!hash_equals($finalSha,$hash($finalPath))){throw new RuntimeException('M84 independent final AFTER receipt drift.');}
$final=json_decode(file_get_contents($finalPath),true,flags:JSON_THROW_ON_ERROR);
if(($final['independent_before_sha256']??null)!==App\Services\M84ContentPatch::BEFORE_SHA
    ||($final['sql_tables']??null)!==46||count($final['sql_after']??[])!==46
    ||($final['db_writes']??null)!==0||($final['http_requests']??null)!==0
    ||($final['all_old_rows_and_timestamps_exact']??null)!==true){throw new RuntimeException('M84 independent full SQL AFTER proof is incomplete.');}
$db=Illuminate\Support\Facades\DB::connection();$digest=App\Support\M26DetailPackage::digest(...);
$fingerprints=static function()use($db,$digest):array{
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);$out=[];
    foreach($tables as $table){
        $columns=$db->getSchemaBuilder()->getColumnListing($table);$query=$db->table($table);
        foreach(in_array('id',$columns,true)?['id']:$columns as $column){$query->orderBy($column);}
        $h=hash_init('sha256');$n=0;foreach($query->cursor() as $record){hash_update($h,$digest((array)$record)."\n");++$n;}
        $out[$table]=['count'=>$n,'sha256'=>hash_final($h)];
    }return $out;
};
$readOnly=static function(Closure $operation)use($db){
    if($db->transactionLevel()!==0){throw new RuntimeException('M84 audit cannot use an ambient transaction.');}
    $db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();try{return $operation();}finally{$db->rollBack();}
};
$sqlBeforeAudit=$readOnly($fingerprints);
if($sqlBeforeAudit!==$final['sql_after']){throw new RuntimeException('M84 all46 SQL fingerprints differ from independent AFTER before registry.');}

PHP;
$review=<<<'PHP'
$data=json_decode($output,true,flags:JSON_THROW_ON_ERROR);
if($exit!==0||($data['failed']??null)!==0||($data['writes']??null)!==0){
    $failed=[];foreach($data['rows']??[] as $row){if(($row['errors']??[])!==[]){$failed[]=['identity'=>$row['identity']??null,'url'=>$row['url']??null,'errors'=>$row['errors']];}}
    fwrite(STDERR,json_encode(['scope'=>$argv[1],'command_exit'=>$exit,'targets'=>$data['targets']??null,'failed'=>$data['failed']??null,
        'writes'=>$data['writes']??null,'failed_rows'=>$failed,'context'=>$data['context']??null],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
    throw new RuntimeException('M84 actual native read-only command did not pass.');
}
$package=App\Support\M84WordOrderHaveGot::class;
[$sourceBefore,$patch,$master,$contract]=$package::load($root);$package::validate($sourceBefore,$patch,$master,$contract);
$audit=App\Support\M84BuilderSelectionPresentation::loadAudit($root);$owners=$package::OWNERS;
if(array_column($contract['targets'],'identity')!==$owners||array_column($audit['targets'],'identity')!==$owners
    ||($data['runtime']??null)!==$final['runtime']){throw new RuntimeException('M84 six-owner source/runtime vector differs.');}
$current=[];$httpHashes=[];
foreach($data['rows']??[] as $row){
    $owner=$row['identity']??'';
    if(!is_string($owner)||$owner===''||isset($current[$owner])
        ||preg_match('/^[a-f0-9]{64}$/D',$row['http_sha256']??'')!==1){throw new RuntimeException('M84 registry duplicate/empty owner or missing fresh HTTP hash.');}
    $httpHashes[$owner]=$row['http_sha256'];unset($row['http_sha256']);$current[$owner]=$row;
}
foreach($owners as $i=>$owner){
    $row=$current[$owner]??null;$declared=$contract['targets'][$i];$selected=$audit['targets'][$i];$practice=$audit['practice_bindings'][$i];
    if(!is_array($row)||($row['errors']??null)!==[]||($row['ok']??null)!==true
        ||($row['url']??null)!==$declared['local_url']||($row['final_url']??null)!==$declared['local_url']
        ||($row['http_status']??null)!==200||($row['page_id']??null)!==$package::PAGE_IDS[$i]
        ||($row['practice_uuid']??null)!==$practice['uuid']||($row['sources']??null)!==[$package::SOURCE]
        ||($row['effective_config']??null)!==$practice['linked_practice']||$declared['level']!==null
        ||($row['selected_ids']??null)!==$selected['expected_selected_ids']||($row['eligible_count']??null)!==($i<5?77:72)
        ||($row['selected_types']??null)!==array_fill(0,5,'4')||($row['selected_count']??null)!==5
        ||($row['widget_count']??null)!==1||($row['practice_widget_count']??null)!==1){
        throw new RuntimeException('M84 exact owner/page/NULL-filter/original+supplemental pool/selected-five/widget differs: '.$owner);
    }
}
$poolEvidence=$readOnly(static function()use($db,$digest,$package,$audit):array{
    $rows=static fn($query)=>$query->get()->map(static fn($r)=>(array)$r)->all();
    $ordered=static function(string $table,$query)use($db,$rows):array{
        $columns=$db->getSchemaBuilder()->getColumnListing($table);
        foreach(in_array('id',$columns,true)?['id']:$columns as $column){$query->orderBy($column);}return $rows($query);
    };
    $snapshot=static function(array $q)use($db,$rows,$ordered):array{
        $s=['question'=>$q];
        foreach(['answers'=>'question_answers','option_pivots'=>'question_option_question','verb_hints'=>'verb_hints','hints'=>'question_hints',
            'variants'=>'question_variants','tags'=>'question_tag','marker_tags'=>'question_marker_tag'] as $key=>$table){
            $s[$key]=$ordered($table,$db->table($table)->where('question_id',$q['id']));
        }
        // Fresh BEFORE uses the reviewed natural marker comparator; inserted markers have the same order.
        usort($s['answers'],static fn($a,$b)=>strnatcmp((string)$a['marker'],(string)$b['marker'])
            ?:(($a['position']??0)<=>($b['position']??0))?:($a['id']<=>$b['id']));
        $ids=array_values(array_unique([...array_column($s['answers'],'option_id'),...array_column($s['option_pivots'],'option_id'),...array_column($s['verb_hints'],'option_id')]));
        $s['options']=$rows($db->table('question_options')->whereIn('id',$ids)->orderBy('id'));$byId=array_column($s['options'],null,'id');
        $s['ordered_answer_instances']=array_map(static fn($a)=>['id'=>$a['id'],'marker'=>$a['marker'],'position'=>$a['position']??null,
            'option_id'=>$a['option_id'],'option'=>$byId[$a['option_id']]['option']??null],$s['answers']);return $s;
    };
    $out=[];$total=0;$cloneCount=0;
    foreach($package::OWNERS as $i=>$owner){
        $target=$audit['targets'][$i];$blockUuids=$db->table('text_blocks')->where('page_id',$package::PAGE_IDS[$i])->pluck('uuid')->all();
        $originalQuestionCount=0;$supplementCount=0;
        foreach($target['expected_pool_ids_by_bank'] as $bank=>$expectedIds){
            $actual=$db->table('questions')->where('seeder',$bank)->where('type','4')
                ->whereIn('uuid',$db->table('question_theory_text_blocks')->select('question_uuid')->whereIn('text_block_uuid',$blockUuids))
                ->orderBy('id')->get()->map(static fn($r)=>(array)$r)->all();
            if(array_column($actual,'id')!==$expectedIds){throw new RuntimeException('M84 original/supplemental exact bank membership differs.');}
            if($bank===$package::BANKS[$i]){$originalQuestionCount=count($actual);}else{$supplementCount+=count($actual);}
            foreach($actual as $q){
                $expected=array_values(array_filter($target['all_pool_snapshots'],static fn($s)=>$s['id']===$q['id']));
                if(count($expected)!==1||$expected[0]['uuid']!==$q['uuid']||$expected[0]['level']!==$q['level']){throw new RuntimeException('M84 exact bank snapshot identity differs.');}
                $s=$snapshot($q);$expected=$expected[0];
                $links=$ordered('question_theory_text_blocks',$db->table('question_theory_text_blocks')->where('question_uuid',$q['uuid'])->whereIn('text_block_uuid',$blockUuids));
                if($digest($s)!==$expected['full_private_snapshot_sha256']||$digest($links)!==$expected['links_sha256']
                    ||$digest($s['options'])!==$expected['all_original_option_attributes_sha256']
                    ||$digest($s['answers'])!==$expected['all_original_answer_attributes_sha256']
                    ||$digest($s['option_pivots'])!==$expected['all_original_option_pivot_attributes_sha256']
                    ||$digest($s['variants'])!==$expected['variants_sha256']
                    ||$digest(['tags'=>$s['tags'],'marker_tags'=>$s['marker_tags']])!==$expected['all_original_tag_relations_sha256']){
                    throw new RuntimeException('M84 complete original/materialized bank snapshot or link/timestamp differs: '.$q['uuid']);
                }++$total;
            }
        }
        if($originalQuestionCount!==72||$supplementCount!==($i<5?5:0)){throw new RuntimeException('M84 only five exact WordOrder supplements are allowed.');}
        $block=App\Models\TextBlock::query()->where('uuid',$audit['practice_bindings'][$i]['uuid'])->sole();
        if($block->level!==null){throw new RuntimeException('M84 original SQL NULL practice changed.');}
        $originalBlock=$block->getAttributes();$copy=App\Support\PracticeCompletionLinkedPractice::matchingBlock($block,true);
        $selected=app(App\Services\Theory\TextBlockToQuestionsMatcherService::class)->findQuestionsForTextBlocks(new Illuminate\Database\Eloquent\Collection([$copy]),5)[$block->uuid]??collect();
        if($selected->pluck('id')->all()!==$target['expected_selected_ids']){throw new RuntimeException('M84 fresh matcher selection differs.');}
        $originals=$selected->map(static fn($q)=>['attributes'=>$q->getAttributes(),'relations'=>$q->toArray()])->all();
        $presented=App\Support\M84BuilderSelectionPresentation::bindings(['block'=>$block,'practiceQuestions'=>$selected,'theoryCanonical'=>true])['practiceQuestions'];
        foreach($selected->values() as $n=>$q){
            $p=$presented->values()->get($n);$want=$q->getAttributes();$want['question']=$target['first_five'][$n]['visible_condition_uk'];
            if($p===$q||$p->getAttributes()!==$want||$q->getAttributes()!==$originals[$n]['attributes']||$q->toArray()!==$originals[$n]['relations']){
                throw new RuntimeException('M84 exact condition-only clone or original model/relations preservation differs.');
            }++$cloneCount;
        }
        if($block->getAttributes()!==$originalBlock){throw new RuntimeException('M84 original practice model mutated.');}
        $out[]=['identity'=>$owner,'original_bank_pool'=>$originalQuestionCount,'supplemental_pool'=>$supplementCount,
            'actual_SQL_level'=>null,'full_snapshot_count'=>$originalQuestionCount+$supplementCount,'selected_ids'=>$target['expected_selected_ids'],
            'original_models_unchanged'=>true,'condition_only_copy_count'=>5,'full_old_and_new_snapshots_exact'=>true];
    }
    if($total!==457||$cloneCount!==30){throw new RuntimeException('M84 exact 457 snapshots/30 copies dimensions differ.');}
    return ['targets'=>$out,'all_full_snapshots'=>457,'original_snapshots'=>432,'new_snapshots'=>25,'condition_only_copies'=>30];
});
$beforeRegistrySha='09d687e799b18d1e72c908b0cc280151e5aa0018ed51ef10277afa0b8796b7ff';
if($argv[1]==='registry'){
    $path=$private.'/registry-before-sentence-builders-v1.json';
    if(!hash_equals($beforeRegistrySha,$hash($path))){throw new RuntimeException('M84 fresh pre-registration registry BEFORE differs.');}
    $beforeRegistry=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$old=[];$sameHttp=0;$changedHttp=[];
    foreach($beforeRegistry['rows'] as $row){
        $owner=$row['identity'];if(isset($old[$owner])){throw new RuntimeException('M84 prior registry owner repeated.');}
        if(($httpHashes[$owner]??null)===$row['http_sha256']){++$sameHttp;}else{$changedHttp[]=$owner;}
        unset($row['http_sha256']);$old[$owner]=$row;
    }
    if(($beforeRegistry['failed']??null)!==0||($beforeRegistry['writes']??null)!==0
        ||($beforeRegistry['targets']??null)!==count($old)||array_intersect(array_keys($old),$owners)!==[]){throw new RuntimeException('M84 original registry scope is not independent.');}
    foreach($old as $owner=>$row){if(($current[$owner]??null)!==$row){throw new RuntimeException('M84 previous registry semantic row changed: '.$owner);}}
    $expected=[...array_keys($old),...$owners];sort($expected,SORT_STRING);$actual=array_keys($current);sort($actual,SORT_STRING);
    if($actual!==$expected||($data['targets']??null)!==count($expected)){throw new RuntimeException('M84 registry is not exact fresh old/new union.');}
    $data['previous_registry_comparison']=['before_receipt_sha256'=>$beforeRegistrySha,'exact_prior_semantic_rows'=>count($old),
        'new_manifest_rows'=>count($owners),'actual_after_union'=>count($actual),'whole_http_response_hash_equal'=>$sameHttp,
        'whole_http_response_hash_different'=>count($changedHttp),'http_hash_different_owners'=>$changedHttp,
        'only_excluded_row_field'=>'http_sha256','all_other_prior_fields_byte_exact'=>true,'whole_http_response_equality_claimed'=>false];
}else{
    $actual=array_keys($current);sort($actual,SORT_STRING);$expected=$owners;sort($expected,SORT_STRING);
    if($actual!==$expected||($data['targets']??null)!==6){throw new RuntimeException('M84 scoped command includes non-manifest owner.');}
}
$sqlAfterAudit=$readOnly($fingerprints);
if($sqlAfterAudit!==$sqlBeforeAudit||$sqlAfterAudit!==$final['sql_after']){throw new RuntimeException('M84 read-only command/pool scan/GET changed a complete SQL table.');}
if(!hash_equals($finalSha,$hash($finalPath))){throw new RuntimeException('M84 independent final receipt changed during audit.');}
$data['m84_readonly_evidence']=['final_after_receipt'=>$profileContext['final_name'],'final_after_receipt_sha256'=>$finalSha,
    'sql_before_cli'=>$sqlBeforeAudit,'sql_after_cli'=>$sqlAfterAudit,'all_46_sql_tables_equal_final_after'=>true,
    'actual_cli_invocation'=>['command'=>'theory:audit-sentence-builders','options'=>$options,
        'mechanism'=>'Actual existing Artisan command via Artisan::call; not a command reimplementation.'],
    'actual_command_exit'=>$exit,'scoped_source'=>$argv[1]==='m84'?basename($package::SOURCE):null,
    'actual_pool_snapshots'=>$poolEvidence,'browser_acceptance_claimed'=>false,'sql_writes'=>0,
    'http_scope'=>'Actual native command GETs only http://gramlyze.loc/theory/* resolved to127.0.0.1; no redirects.'];

PHP;
$replacements=[
    ['M52','M84',7],['m52','m84',5],
    ['m84-passive-basics.v1.json','m84-word-order-have-got.v1.json',1],
    ["(\$data['targets'] ?? null) !== 3","(\$data['targets'] ?? null) !== count(App\\Support\\M84WordOrderHaveGot::OWNERS)",1],
    ['App\\Services\\M84LocalTargetGuard::assertConfiguredDatabase(Illuminate\\Support\\Facades\\DB::connection());',
        'App\\Services\\M84LocalTargetGuard::assertConfiguredDatabase(Illuminate\\Support\\Facades\\DB::connection());'."\n".$setup,1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);",$review,1],
    ["\$file = \$root.'/storage/app/seo-m84-local/'.\$argv[1].'-sentence-builders-v1.json';",
        "\$file=\$root.'/storage/app/seo-m84-local/'.\$profileContext['receipt_name'];",1],
];
if($profileCheck){
    $bytes=file_get_contents(__DIR__.'/m52-sentence-builder-audit.php');
    if(!hash_equals('9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',hash('sha256',$bytes))){throw new RuntimeException('M84 immutable M52 audit primitive drift.');}
    foreach($replacements as $i=>[$old,$new,$count]){
        $actual=substr_count($bytes,$old);if($actual!==$count){throw new RuntimeException('M84 registry literal count differs at '.$i.': '.$actual.' expected '.$count);}
        $bytes=str_replace($old,$new,$bytes);
    }token_get_all($bytes,TOKEN_PARSE);
    echo json_encode(['profile'=>'M84 actual native registry','literal_replacements'=>count($replacements),'parsed'=>true,'db_connections'=>0,'http_requests'=>0],JSON_THROW_ON_ERROR)."\n";return;
}
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',$replacements,$context);
