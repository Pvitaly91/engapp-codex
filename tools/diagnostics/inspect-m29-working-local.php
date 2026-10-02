<?php

// Read-only inventory. Only nonce/proof evidence is written to private owned storage.
if (PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows') { exit(1); }
$root='D:/DEV/htdocs/gramlyze.loc';
require $root.'/vendor/autoload.php';
require_once dirname(__DIR__,2).'/app/Services/M11LocalTargetGuard.php';
$app=require $root.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path())!==realpath($root)) { throw new RuntimeException('Wrong working root.'); }
$db=Illuminate\Support\Facades\DB::connection();
$physical=(new App\Services\PronounContentRepair($db,database_path(),storage_path('app/seo-m29-local')))->connection();
echo json_encode(['physical'=>$physical],JSON_UNESCAPED_SLASHES)."\n";
$inventory=[];
foreach (['Database\\Seeders\\Page_V3\\SentenceStructure\\CleftSentencesEmphasisTheorySeeder','Database\\Seeders\\Page_V3\\SentenceStructure\\ComplexNounPhrasesTheorySeeder','Database\\Seeders\\Page_V3\\SentenceStructure\\EllipsisSubstitutionAndReferenceTheorySeeder'] as $name) {
    $identity=$name;
    $page=$db->table('pages')->where('seeder',$identity)->first();
    if (!$page) { throw new RuntimeException('Missing M29 page.'); }
    $blocks=$db->table('text_blocks')->where('page_id',$page->id)->get();
    $uuids=$blocks->pluck('uuid');
    $questions=$db->table('questions')->join('question_theory_text_blocks','questions.uuid','=','question_theory_text_blocks.question_uuid')
        ->whereIn('text_block_uuid',$uuids)->distinct()->get(['questions.id','questions.seeder','questions.type','questions.level']);
    $groups=[]; foreach ($questions as $q) { $key=$q->seeder.'|'.$q->type.'|'.$q->level; $groups[$key]=($groups[$key]??0)+1; } ksort($groups);
    $record=['slug'=>$page->slug,'page_id'=>$page->id,'blocks'=>$blocks->map(fn($b)=>['id'=>$b->id,'uuid'=>$b->uuid,'locale'=>$b->locale,'type'=>$b->type,'order'=>$b->sort_order,'body_sha256'=>hash('sha256',$b->body)]),
        'linked_bank_groups'=>$groups,'linked_bank_ids'=>$questions->groupBy('seeder')->map(fn($rows)=>$rows->pluck('id')->sort()->values()->all())];
    $inventory[]=$record;
    echo json_encode($record,JSON_UNESCAPED_SLASHES)."\n";
}
if (($argv[1]??'')==='--inventory') {
    $dir=storage_path('app/seo-m29-local'); $file=fopen($dir.'/m29-bank-inventory-v1.json','x');
    if (!$file) { throw new RuntimeException('Inventory exists.'); }
    fwrite($file,json_encode(['physical'=>$physical,'targets'=>$inventory],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)); fclose($file);
}
if (($argv[1]??'')==='--proof') {
    $nonce=bin2hex(random_bytes(16)); $dir=storage_path('app/seo-m29-local');
    if (!is_dir($dir)) { mkdir($dir,0700,true); }
    $file=fopen($dir.'/local-proof-'.$nonce.'.json','x');
    if (!$file) { throw new RuntimeException('Proof exists.'); }
    fwrite($file,json_encode(['target'=>'gramlyze.loc','nonce'=>$nonce])); fclose($file);
    echo json_encode(['proof'=>'local-proof-'.$nonce.'.json','nonce'=>$nonce])."\n";
}
