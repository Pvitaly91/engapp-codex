<?php

/**
 * Pure composition of two independently reviewed SELECT-only M79 postconditions.
 * Does not bootstrap Laravel, load .env, connect to SQL or issue HTTP requests.
 */
function m79FinalReadOnlyLineage(string $root,string $dir):array
{
    $pins=[];
    $same=static function(mixed $a,mixed $b,string $why):void { if($a!==$b){throw new RuntimeException('M79 final lineage: '.$why);} };
    $read=static function(string $file,string $sha)use(&$pins):array {
        if(!is_file($file)||is_link($file)||!hash_equals($sha,hash_file('sha256',$file))){throw new RuntimeException('M79 final lineage evidence/source bytes differ: '.basename($file));}
        $pins[$file]=$sha; return str_ends_with($file,'.json')?json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR):[];
    };
    $digest=static fn(array $value):string=>hash('sha256',json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
    $initialSha='c9c8f0d1b42d5a0158555ef29f3843c66a448cbcacb963348f4f46c1e5c5fb67';
    $baselineSha='43b480a36883d551a6b5f05320c228f1fed8e79584a6d5b7a0eb22fadb0d5b09';
    $criteriaSha='b9f38d0767c8baba56443f7e4aab286e483986bd800c4d8d013e5eeacbf009a3';
    $planSha='3cbc96de22a9b1aafd6c90b65c9a67521b822d7f977c04a9b685bb1971369800';
    $initial=$read($dir.'/final-readonly-v1.json',$initialSha);
    $baseline=$read($dir.'/m79-practice-criteria-before-v1.json',$baselineSha);
    $criteria=$read($dir.'/criteria-postconditions-v1.json',$criteriaSha);
    $plan=$read($dir.'/criteria-preview-v1.json',$planSha);
    $backup=$read($dir.'/criteria-backup-v1.json',$planSha);
    $same($baseline['original_postconditions_sha256']??null,$initialSha,'initial→fresh v1 baseline pin');
    $same($baseline['identity']??null,$initial['runtime']??null,'initial→fresh v1 runtime');
    $same($baseline['sql_before']??null,$initial['sql_after']??null,'initial AFTER→fresh v1 all46');
    $same($criteria['runtime']??null,$initial['runtime']??null,'successor runtime');
    $same($criteria['independent_before_sha256']??null,$baselineSha,'successor independent baseline pin');
    $same($criteria['normalized_before']??null,$baseline['sql_before']??null,'successor→fresh v1 all46 normalization');
    $same([$criteria['kind']??null,$criteria['sql_tables']??null,$criteria['updated_bodies']??null,$criteria['exact_label_prefixes']??null,
        $criteria['inserted_rows']??null,$criteria['deleted_rows']??null,$criteria['db_writes']??null,$criteria['http_requests']??null],
        ['M79_practice_criteria_postconditions',46,2,6,0,0,0,0],'finite successor counters');
    foreach(['all_other_fields_and_timestamps_exact','all_questions_progress_and_other_rows_exact','backup_equals_reviewed_preview'] as $key){
        $same($criteria[$key]??null,true,'independent successor postcondition '.$key);
    }
    $same([$baseline['kind']??null,$baseline['row_ids']??null,$baseline['db_writes']??null,$baseline['http_requests']??null],
        ['M79_practice_criteria_before',[4243,4372],0,0],'fresh v1 capture scope');
    $same($plan,$backup,'successor exclusive backup');
    $unsigned=$plan;unset($unsigned['sha256']);
    $same($digest($unsigned),'68936ba88db70843e581cf6f8368621bae4e9ec6e787d207b18b079d1e6f3ceb','successor reviewed plan digest');
    $same($plan['sha256']??null,$criteria['plan_digest']??null,'successor plan→postconditions');
    $same([$plan['patch']??null,$plan['version']??null,$plan['state']??null,count($plan['updates']??[]),$plan['inserts']??null],
        ['m79-practice-visible-criteria-v1.0.1',1,'before',2,[]],'successor exact plan boundary');
    $same($plan['protected']??null,$baseline['sql_before']??null,'successor protected full SQL boundary');
    $old=$read($root.'/database/content-patches/m79-question-types-completion.v1.json','0e0b1406a376e392c4b12028e589ec099e4a675599b6907a873b738474e51d11');
    $next=$read($root.'/database/content-patches/m79-question-types-completion.v1.0.1.json','9af348eae92ac0cb361f5a9da2c407275037cfb1fc1673e84486abef31d7396c');
    $ledger=$read($root.'/docs/content/m79-practice-criteria-revision.v1.0.1.json','333f101e6fb32c122d5d20ffbe67f4482f43d157b4d8b163cc7222d40b4abfc5');
    $normalized=$next;$prefixes=0;
    foreach([0,2] as $n=>$index){
        $a=$old['targets'][$index]['after']['page']['blocks'][11]['body'];
        $b=$next['targets'][$index]['after']['page']['blocks'][11]['body'];
        $data=json_decode($a,true,flags:JSON_THROW_ON_ERROR);$target=json_decode($b,true,flags:JSON_THROW_ON_ERROR);
        $same(count($data['selects']),3,'three existing selects per continuing practice');
        foreach($data['selects'] as &$task){$task['label']=$task['prompt'].' '.$task['label'];++$prefixes;}unset($task);
        $same($data,$target,'independent exact six-label transformation');
        $same($baseline['rows'][$n]['body'],$a,'fresh v1 original body');
        $same($plan['updates'][$n]['before'],['body'=>$a],'reviewed exact before body');
        $same($plan['updates'][$n]['after'],['body'=>$b],'reviewed exact after body');
        $same($plan['updates'][$n]['id'],[4243,4372][$n],'reviewed exact physical owner');
        $row=$baseline['rows'][$n];$after=$row;$after['body']=$b;
        $same($criteria['row_hashes'][(string)$row['id']]??null,
            ['before_sha256'=>$digest($row),'after_sha256'=>$digest($after),'non_body_fields_and_timestamps_exact'=>true],
            'independent complete row/timestamp normalization');
        $normalized['targets'][$index]['after']['page']['blocks'][11]['body']=$a;
    }
    $same($normalized,$old,'no other patch learning changes');$same($prefixes,6,'six exact visible label prefixes');
    foreach($ledger['predecessor_pins'] as $path=>$sha){$read($root.'/'.$path,$sha);}
    foreach($ledger['active_artifacts'] as $path=>$entry){foreach([$root,$root.'/storage/app/m75-worktree'] as $base){
        $read($base.'/'.$path,$entry['sha256']);$same(filesize($base.'/'.$path),$entry['bytes'],'active successor artifact length');
    }}
    foreach($plan['sources'] as $path=>$sha){$read(str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path,$sha);}
    $successors=[
        'app/Support/M79QuestionTypesCompletion.php'=>['before'=>'4e78ee4088416f34054e1211de3069af4078d910726c9cd3092d3ca30710d3b3','after'=>'61f987aed2dc1a535e066c4c04a81a3e4ed411c0fd3979d7e2acace7d9036c02'],
        'app/Support/M79BuilderSelectionPresentation.php'=>['before'=>'10f2078dde33572a09d7d89d60b34291fce0fb501c4c836e33eeb60c1e5f2647','after'=>'65bcdcd939f9c109d0d7e643b069575599058a6f890d69f058b835e736497246'],
        'database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder/definition.json'=>['before'=>'56d4dbca0b6da232b7c7b249bdac81034f27aea9fd9f7f0b6035e6bf881df8b1','after'=>'17339b038a8d496daf861ce8ab28de2ae4721fbb9872e5a41635e34d60612de1'],
        'database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsNegativeQuestionsTheorySeeder/definition.json'=>['before'=>'eb726538310ed50dfaab4b2a74b33d132943e91b7f6ef4d526b822f43a00351f','after'=>'5b9b78355e292e191f9d810e59000e227fa621a752136dedd303d898a390d2ed'],
    ];
    foreach($successors as $path=>$pin){foreach([$root,$root.'/storage/app/m75-worktree'] as $base){
        if(!is_file($base.'/'.$path)||is_link($base.'/'.$path)||!hash_equals($pin['after'],hash_file('sha256',$base.'/'.$path))){
            throw new RuntimeException('M79 final lineage active ROOT/worktree successor differs: '.$path);
        }$pins[$base.'/'.$path]=$pin['after'];
    }}
    $combined=$initial;
    $combined['kind']='M79_two_layer_readonly_postconditions_composition';
    $combined['sql_after']=$criteria['sql_after'];
    $combined['combined_lineage']=[
        'initial_final_file'=>'final-readonly-v1.json','initial_final_sha256'=>$initialSha,
        'fresh_v1_baseline_file'=>'m79-practice-criteria-before-v1.json','fresh_v1_baseline_sha256'=>$baselineSha,
        'criteria_final_file'=>'criteria-postconditions-v1.json','criteria_final_sha256'=>$criteriaSha,
        'criteria_plan_file'=>'criteria-preview-v1.json','criteria_plan_raw_sha256'=>$planSha,
        'criteria_plan_digest'=>$criteria['plan_digest'],'criteria_backup_file'=>'criteria-backup-v1.json',
        'all46_successor_normalized_to_fresh_v1_to_initial_after'=>true,
        'initial70updates3practice_inserts131bank_rows'=>true,'successor2body_updates6visible_label_prefixes'=>true,
        'original46_before_reconstruction_retained'=>true,
        'scope_note'=>'Initial-layer fields/plan remain historical. Current sql_after is the independently verified successor; four exact source successor pins are explicit. This composition itself does not query SQL.',
    ];
    return ['final'=>$combined,'pins'=>$pins,'source_successors'=>$successors,'active_successor_artifacts'=>$ledger['active_artifacts']];
}

if(realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){
    if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==1){throw new RuntimeException('Explicit pure M79 final lineage CLI only.');}
    $root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m79-local';
    if(strtolower(str_replace('\\','/',(string)realpath(dirname(__DIR__,2))))!==strtolower($root)){throw new RuntimeException('Actual served ROOT required.');}
    $data=m79FinalReadOnlyLineage($root,$dir);$file=$dir.'/final-readonly-v2.json';
    if(file_exists($file)||is_link($file)){throw new RuntimeException('Exclusive final composite receipt exists.');}
    $bytes=json_encode($data['final'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
    $h=fopen($file,'x');if(!$h){throw new RuntimeException('Exclusive final composite unavailable.');}
    try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('Final composite incomplete.');}}finally{fclose($h);}
    echo json_encode(['file'=>basename($file),'sha256'=>hash('sha256',$bytes),'layers'=>2,'tables'=>46,'source_successors'=>4,
        'application_bootstrapped'=>false,'db_connections'=>0,'db_queries'=>0,'db_writes'=>0,'http_requests'=>0],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
}
