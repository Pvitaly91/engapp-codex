<?php

/** Pure exact bank-binding successor plan. No SQL, HTTP or active file writes. */
if(PHP_SAPI!=='cli'||count($argv)!==1){throw new RuntimeException('M79 finite bank revision is CLI-only.');}
$root='D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';$dir='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m79-local';
$pins=[
    'docs/content/m79-sentence-builder-bank-audit.v1.json'=>'9953bcf155eac62bc7369d6859513cc682bd46175eed2ce3bcd5f76eb300bf49',
    'database/content-patches/m79-question-types-completion.v1.0.1.json'=>'9af348eae92ac0cb361f5a9da2c407275037cfb1fc1673e84486abef31d7396c',
    'docs/content/m79-source-checksums.v1.0.1.json'=>'f0588a2eb7138c997c4f18b4a43e26061f55bcf5292e5708fb2d393c2e6438cc',
    'docs/content/m79-practice-criteria-revision.v1.0.1.json'=>'6ef28d0d3d31f57ebd3c2105e42f3522b7bfbee6114f0dea2df4a116aff35195',
];
$read=static function($path)use($pins,$root){if(is_link($root.'/'.$path)||!is_file($root.'/'.$path)||!hash_equals($pins[$path],hash_file('sha256',$root.'/'.$path))){throw new RuntimeException('M79 reviewed finite bank input drift.');}return json_decode(file_get_contents($root.'/'.$path),true,flags:JSON_THROW_ON_ERROR);};
$json=static fn($v)=>json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$audit=$read(array_keys($pins)[0]);$oldAudit=$audit;$patch=$read(array_keys($pins)[1]);
$checks=$read(array_keys($pins)[2]);$ledger=$read(array_keys($pins)[3]);
$audit['version']='1.0.1';$changes=[];
foreach([0,2] as $i){
    $old=$audit['practice_bindings'][$i]['body_sha256'];
    if($audit['practice_bindings'][$i]['identity']!==$patch['targets'][$i]['identity']){throw new RuntimeException('M79 exact bank owner differs.');}
    $new=hash('sha256',$patch['targets'][$i]['after']['page']['blocks'][11]['body']);
    if($old===$new){throw new RuntimeException('M79 criteria bank binding did not change.');}
    $audit['practice_bindings'][$i]['body_sha256']=$new;
    $changes[]=['lesson_index'=>$i,'identity'=>$patch['targets'][$i]['identity'],'before_body_sha256'=>$old,'after_body_sha256'=>$new];
}
$normalized=$audit;$normalized['version']='1.0.0';foreach([0,2] as $i){$normalized['practice_bindings'][$i]['body_sha256']=$oldAudit['practice_bindings'][$i]['body_sha256'];}
if($normalized!==$oldAudit){throw new RuntimeException('M79 bank revision changes more than exact body bindings/version.');}
$path='docs/content/m79-sentence-builder-bank-audit.v1.0.1.json';$auditBytes=$json($audit);
if(file_exists($root.'/'.$path)||is_link($root.'/'.$path)){throw new RuntimeException('M79 successor bank artifact already exists.');}
foreach($checks['artifacts'] as &$entry){if($entry['path']==='docs/content/m79-sentence-builder-bank-audit.v1.json'){$entry=['path'=>$path,'sha256'=>hash('sha256',$auditBytes),'bytes'=>strlen($auditBytes)];}}
unset($entry);$checkPath='docs/content/m79-source-checksums.v1.0.1.json';$checksBytes=$json($checks);
$ledger['predecessor_pins'][array_keys($pins)[0]]=$pins[array_keys($pins)[0]];
$ledger['active_artifacts'][$path]=['sha256'=>hash('sha256',$auditBytes),'bytes'=>strlen($auditBytes)];
$ledger['active_artifacts'][$checkPath]=['sha256'=>hash('sha256',$checksBytes),'bytes'=>strlen($checksBytes)];
$ledger['bank_binding_successor']=['version_before'=>'1.0.0','version_after'=>'1.0.1','practice_body_bindings'=>$changes,
    'targets_and_selected_questions_identical'=>true,'all_other_audit_fields_identical'=>true];
$ledgerPath='docs/content/m79-practice-criteria-revision.v1.0.1.json';$ledgerBytes=$json($ledger);
$outputs=[$path=>$auditBytes,$checkPath=>$checksBytes,$ledgerPath=>$ledgerBytes];$beforeBytes=[];
foreach([$checkPath,$ledgerPath] as $p){$beforeBytes[$p]=file_get_contents($root.'/'.$p);}
$plan=['schema_version'=>1,'kind'=>'M79_practice_criteria_bank_revision_plan','predecessor_pins'=>$pins,'outputs'=>$outputs,'before_bytes'=>$beforeBytes,
    'audit_sha256'=>hash('sha256',$auditBytes),'checksums_sha256'=>hash('sha256',$checksBytes),'ledger_sha256'=>hash('sha256',$ledgerBytes),
    'changed_audit_bindings'=>2,'selected_questions_changed'=>0,'db_connections'=>0,'db_writes'=>0,'http_requests'=>0];
$file=$dir.'/practice-criteria-revision-plan-v2.json';if(file_exists($file)||is_link($file)){throw new RuntimeException('M79 exclusive bank revision plan already exists.');}
$bytes=$json($plan);$h=fopen($file,'x');if(!$h){throw new RuntimeException('M79 exclusive bank revision plan cannot be created.');}
try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M79 finite bank revision plan incomplete.');}}finally{fclose($h);}
echo $json(['plan'=>basename($file),'plan_sha256'=>hash('sha256',$bytes),'audit_sha256'=>$plan['audit_sha256'],
    'checksums_sha256'=>$plan['checksums_sha256'],'ledger_sha256'=>$plan['ledger_sha256'],'db_connections'=>0,'db_writes'=>0,'active_source_writes'=>0]);
