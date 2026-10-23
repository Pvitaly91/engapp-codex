<?php

/** Bind reviewed literal conditions to independent original snapshots; no SQL/bootstrap/source edit. */
if (PHP_SAPI!=='cli'||count($argv)!==3||preg_match('/^[a-f0-9]{64}$/D',$argv[1])!==1
    ||preg_match('/^widget-selection-plan-v[1-9][0-9]*\.json$/D',$argv[2])!==1) {
    throw new RuntimeException('M80 needs reviewed draft SHA and one exclusive private plan basename.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$private=$root.'/storage/app/seo-m80-local';
require $root.'/vendor/autoload.php';require_once $wt.'/app/Support/M80NegativesTransformations.php';
use App\Support\M80NegativesTransformations as Package;
use App\Support\ComposeTokenCase;
$read=static function(string $file,string $sha):array {
    if (is_link($file)||!is_file($file)||!hash_equals($sha,hash_file('sha256',$file))) { throw new RuntimeException('M80 reviewed selection input drifted.'); }
    return json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
};
$beforeSha='75a9e58003f9364c45e4e3c2696aab7c0b8e5d5073a2bb2acefe54fb198dbefb';
$before=$read($private.'/before-v1.json',$beforeSha);$draft=$read($wt.'/docs/content/m80-widget-review-draft.v1.json',$argv[1]);
if (($draft['independent_before_sha256']??null)!==$beforeSha||array_column($draft['targets']??[],'identity')!==Package::OWNERS) {
    throw new RuntimeException('M80 selection exact six-owner BEFORE provenance differs.');
}
$out=['schema_version'=>1,'package'=>'m80-negatives-transformations-widget-selection','version'=>'1.0.0',
    'independent_before_sha256'=>$beforeSha,'review'=>'Agent candidates and main independently reviewed; not human review or browser evidence.',
    'selection_policy'=>$draft['policy'],'coverage_before_selection'=>$draft['coverage_before_selection'],
    'scope_exclusions'=>[],'sources'=>[],'targets'=>[]];
$index=[];
foreach (Package::OWNERS as $i=>$owner) {
    $page=$before['pages'][$owner];$selected=[];
    foreach ($page['relations']['questions'] as $q) { $index[$q['uuid']]=['identity'=>$owner,'id'=>$q['id']]; }
    foreach ($draft['targets'][$i]['selected'] as $sample) {
        $s=$page['relations']['question_snapshots'][$sample['uuid']]??null;$q=$s['question']??[];
        $bank='Database\\Seeders\\V3\\Polyglot\\'.[
            'PolyglotNegativeFormsAllLevelsLessonSeeder','PolyglotDoubleNegativesAllLevelsLessonSeeder',
            'PolyglotSentenceTransformationsTensesAllLevelsLessonSeeder','PolyglotSentenceTransformationsModalsAllLevelsLessonSeeder',
            'PolyglotSentenceTransformationsPassiveAllLevelsLessonSeeder','PolyglotSentenceTransformationsReportedSpeechAllLevelsLessonSeeder',
        ][$i];
        if (($q['uuid']??null)!==$sample['uuid']||($q['seeder']??null)!==$bank||($q['level']??null)!==Package::LEVELS[$i]
            ||(string)($q['type']??'')!=='4'||$s['hints']!==[]||$s['verb_hints']!==[]
            ||str_contains($sample['condition'],$sample['settled'])||preg_match('/<[^>]+>|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu',$sample['condition'])) {
            throw new RuntimeException('M80 selected original/condition/hint scope differs: '.$sample['uuid']);
        }
        $raw=array_column($s['ordered_answer_instances'],'option');$tokens=ComposeTokenCase::normalize($raw);
        $settled=preg_replace('/\s+([?.!,;:])/u','$1',implode(' ',$tokens));
        if ($settled!==$sample['settled']) { throw new RuntimeException('M80 unchanged native case/word sequence differs from literal review: '.$sample['uuid']); }
        $selected[]=['id'=>$q['id'],'uuid'=>$q['uuid'],'level'=>$q['level'],'source'=>$q['question'],'raw_tokens'=>$raw,
            'condition'=>$sample['condition'],'translation'=>$sample['translation'],'settled'=>$sample['settled'],'tokens'=>$tokens,'coverage'=>$sample['coverage']];
    }
    $uuids=array_column($selected,'uuid');$sorted=$uuids;sort($sorted,SORT_STRING);
    if (count($selected)!==5||count(array_unique($uuids))!==5||$sorted!==$uuids) { throw new RuntimeException('M80 finite native selected order differs.'); }
    $out['targets'][]=['identity'=>$owner,'selected'=>$selected];
}
foreach ($draft['known_exclusions'] as $item) {
    $row=$index[$item['uuid']]??null;if ($row===null) { throw new RuntimeException('M80 known exclusion has no exact original.'); }
    $out['scope_exclusions'][]=$row+['uuid'=>$item['uuid'],'reason'=>$item['reason']];
}
$json=static fn($v)=>json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$bytes=$json($out);$plan=['path'=>'docs/content/m80-widget-selection.v1.json','bytes'=>$bytes,'sha256'=>hash('sha256',$bytes),
    'draft_sha256'=>$argv[1],'selected'=>30,'new_questions'=>0,'db_connections'=>0,'db_writes'=>0,'http_requests'=>0];
$file=$private.'/'.$argv[2];if (file_exists($file)||is_link($file)) { throw new RuntimeException('M80 exclusive selection plan exists.'); }
$h=fopen($file,'x');if (!$h) { throw new RuntimeException('M80 private selection plan unavailable.'); }
try { $data=$json($plan);if (fwrite($h,$data)!==strlen($data)||!fflush($h)) { throw new RuntimeException('M80 selection plan incomplete.'); } } finally { fclose($h); }
echo $json(['file'=>$argv[2],'selection_sha256'=>$plan['sha256'],'selected'=>30,'new_questions'=>0,'db_writes'=>0,'active_source_writes'=>0]);
