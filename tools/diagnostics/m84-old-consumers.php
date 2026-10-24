<?php

/** Finite six-owner profile of unchanged SELECT-only old-consumer capture. */
require __DIR__.'/frozen-readonly-profile.php';
$finalContext=[];
if(($argv[1]??null)==='audit'){
    if(count($argv)!==7||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[5])!==1
        ||preg_match('/^[a-f0-9]{64}$/D',$argv[6])!==1){throw new RuntimeException('M84 audit additionally requires exact independent final receipt/hash.');}
    $path='D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m84-local/'.$argv[5];
    if(is_link($path)||!is_file($path)||!hash_equals($argv[6],hash_file('sha256',$path))){throw new RuntimeException('M84 independent final receipt drift.');}
    $final=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
    if(($final['independent_before_sha256']??null)!=='babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916'
        ||($final['all_old_rows_and_timestamps_exact']??null)!==true||($final['independent_before_projection_exact']??null)!==true
        ||($final['db_writes']??null)!==0||($final['sql_tables']??null)!==46){throw new RuntimeException('M84 final reconstruction is incomplete.');}
    $finalContext=['final'=>$final,'final_file'=>$argv[5],'final_sha256'=>$argv[6]];$argv=array_slice($argv,0,5);
}
$primitive=file_get_contents(__DIR__.'/m52-old-consumers.php');
$start=strpos($primitive,'$manifests = [');$end=strpos($primitive,'// Named route templates own URL construction.');
if($start===false||$end===false||$end<=$start){throw new RuntimeException('Reviewed diagnostic declarations unavailable.');}
$oldDeclarations=str_replace('M52','M84',substr($primitive,$start,$end-$start));
$declarations= <<<'PHP'
$beforeFile=$dir.'/before-v1.json';
if(!is_file($beforeFile)||is_link($beforeFile)||!hash_equals('babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916',hash_file('sha256',$beforeFile))){throw new RuntimeException('M84 source/SQL BEFORE differs.');}
$captured=json_decode(file_get_contents($beforeFile),true,flags:JSON_THROW_ON_ERROR);
$uuidProjection=new class extends App\Support\Database\JsonTestSeeder {
    protected function definitionPath():string {throw new RuntimeException('Read-only UUID projection cannot run a seeder.');}
    public function sourceQuestions(array $definition):array {
        $namespace=$this->resolveUuidNamespace($definition,$definition['seeder']['class']);$out=[];
        foreach($definition['questions'] as $index=>$q){
            $q['uuid']=$this->resolveQuestionUuid($q,$namespace,$index,trim((string)$q['question']));$out[]=$q;
        }return $out;
    }
};
$declarations=[];$pages=[];
foreach($captured['pages'] as $owner=>$capture){
    $categoryPath=implode('/',array_column($capture['category_ancestry'],'slug'));
    $pages[$owner]=['page_seeder_class'=>$owner,'category_slug'=>end($capture['category_ancestry'])['slug'],
        'category_path'=>$categoryPath,'slug'=>$capture['page']['slug']];
    foreach($capture['bank_inventory'] as $bank=>$inventory){
        $relative='database/seeders/'.str_replace('\\','/',substr($bank,strlen('Database\\Seeders\\'))).'/definition.json';
        $definition=$readSource($relative);$saved=$definition['saved_test']??null;
        if(($definition['seeder']['class']??null)!==$bank||!is_array($saved)||empty($saved['uuid'])||empty($saved['slug'])){
            throw new RuntimeException('M84 independently discovered old bank declaration differs.');
        }
        $projectedQuestions=$uuidProjection->sourceQuestions($definition);
        $saved['question_uuids']??=array_column($projectedQuestions,'uuid');
        $types=array_unique(array_map(static fn($q)=>(string)($q['type']??$definition['defaults']['type']??''),$definition['questions']));
        $entry=['bank'=>$bank,'definition'=>$relative,'saved'=>$saved,
            'source_question_uuids'=>array_column($projectedQuestions,'uuid'),'compose'=>$types===['4'],'page_owner'=>$owner];
        if(isset($declarations[$saved['slug']])&&$declarations[$saved['slug']]!==$entry){throw new RuntimeException('M84 duplicate old declaration differs.');}
        $declarations[$saved['slug']]=$entry;
    }
}
ksort($declarations);ksort($pages);ksort($sourceHashes);
PHP;
// The original source-family check is narrowed to every exact bank path from
// the independently hash-bound BEFORE, not widened to arbitrary file access.
$capture=json_decode(file_get_contents('D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m84-local/before-v1.json'),true,flags:JSON_THROW_ON_ERROR);
$paths=[];foreach($capture['pages'] as $page){foreach(array_keys($page['bank_inventory']) as $bank){
    $paths[]='database/seeders/'.str_replace('\\','/',substr($bank,strlen('Database\\Seeders\\'))).'/definition.json';
}}
$paths=array_values(array_unique($paths));sort($paths);
$guard='~^(?:'.implode('|',array_map(static fn($p)=>preg_quote($p,'~'),$paths)).')$~D';
$oldGuard='~^database/seeders/V3/(?:TheoryLinks/data/[a-z0-9-]+\\.json|(?:Polyglot|PassiveVoice)/[A-Za-z0-9]+/definition\\.json)$~D';
$oldTableCheck=<<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M84 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M84 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$newTableCheck=<<<'PHP'
if($mode==='audit'){
    $final=$profileContext['final']??null;
    if(!is_array($final)||($final['normalized_before']??null)!==$workingBefore['sql_before']
        ||($final['sql_after']??null)!==$allTablesBeforeHttp){throw new RuntimeException('M84 all46 SQL fingerprints differ from exact final reconstruction.');}
}elseif($allTablesBeforeHttp!==$workingBefore['sql_before']){throw new RuntimeException('M84 independent BEFORE SQL drift.');}
PHP;
runFrozenReadOnlyProfile('m52-old-consumers.php','1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775',[
    ['M52','M84',43],['m52','m84',4],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc','D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
    [$oldDeclarations,$declarations,1],[$oldGuard,$guard,1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff','babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916',1],
    ["['categoryPath' => \$source['category_slug'], 'pageSlug' => \$source['slug']]","['categoryPath' => \$source['category_path'], 'pageSlug' => \$source['slug']]",1],
    ['CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60','CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 120',1],
    [$oldTableCheck,$newTableCheck,1],
    ["'sql_allowlist_during_apply' => ['pages', 'text_blocks'],","'sql_allowlist_during_apply' => 'No table-wide exemptions; exact reviewed rows are bound through the independent final reconstruction.',",1],
],$finalContext);
