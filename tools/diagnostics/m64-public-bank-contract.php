<?php

/** M64 explicit public teaching allowlist. Private DB/runtime snapshots never enter the versioned artifact. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) { throw new RuntimeException('M64 public contract projection takes no arguments.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
if (strtolower(str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2)))) !== strtolower($root)) { throw new RuntimeException('M64 public projection ROOT differs.'); }
require $root.'/vendor/autoload.php';
use App\Support\M26DetailPackage as Json;
$dir = $root.'/storage/app/seo-m64-local'; $path = $dir.'/bank-review-projection-v1.json';
if (!is_file($path) || is_link($path) || hash_file('sha256', $path) !== 'fd4499e1498d194c5db9c9b1a0d157d3dcf2d44b75b0ef9d431c7086f6b9e5a5') { throw new RuntimeException('M64 reviewed private projection changed.'); }
$private = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
$out = array_intersect_key($private, array_flip(['schema_version','package','version','status','locale','independent_before_sha256','scope','pending','review_summary']));
$out['privacy_policy'] = 'Versioned teaching-only allowlist: semantic conditions, intended English/UK sentences, selected/pool IDs, exact practice bindings and opaque hashes. No raw question attributes, SQL rows, timestamps, runtime identity, table inventories or private diagnostic records.';
$out['targets'] = [];
foreach ($private['targets'] as $target) {
    $safe = array_intersect_key($target, array_flip(['identity','local_url','page_id','category_id','locale','level','type','bank_seeders',
        'old_bank_seeder','expected_old_pool_ids','expected_pool_ids_by_bank','expected_selected_ids','expected_selected_uuids','excluded_question_ids']));
    $safe['category_ancestry'] = array_map(static fn ($c) => array_intersect_key($c, array_flip(['id','slug','language','type','parent_id'])), $target['category_ancestry']);
    $safe['first_five'] = [];
    foreach ($target['first_five'] as $sample) {
        $item = array_intersect_key($sample, array_flip(['id','uuid','bank_seeder','is_new','context_prefix_uk','displayed_condition_uk',
            'visible_condition_uk','semantic_case_review','expected_display_sentence','expected_word_key','correct_tokens','token_presentations','option_presentations']));
        $item['question_attribute_keys'] = array_keys($sample['question_snapshot']);
        $pivots = array_map(static fn ($row) => ['question_id'=>$row['question_id'],'option_id'=>$row['option_id'],'flag'=>$row['flag']], $sample['option_pivots_snapshot']);
        usort($pivots, static fn ($a,$b) => $a['option_id'] <=> $b['option_id']);
        $item['snapshot_hashes'] = array_intersect_key($sample['snapshot_hashes'], array_flip(['question_snapshot','ordered_answer_snapshot','original_options_snapshot','hints_snapshot','verb_hints_snapshot']));
        $item['snapshot_hashes']['loaded_option_pivots'] = Json::digest($pivots);
        $item['full_private_snapshot_sha256'] = $sample['original_snapshot_sha256'];
        $safe['first_five'][] = $item;
    }
    $safe['all_eligible_review'] = array_map(static fn ($r) => array_intersect_key($r,array_flip(['id','uuid','is_new','condition_uk','actual_ordered_key','selected','review'])), $target['all_eligible_review']);
    $out['targets'][] = $safe;
}
$out['practice_bindings'] = $private['practice_bindings'];
$forbidden = ['created_at','updated_at','question_snapshot','ordered_answer_snapshot','original_options_snapshot','option_pivots_snapshot','hints_snapshot','verb_hints_snapshot'];
$check = static function ($value, bool $inHashes=false) use (&$check,$forbidden): void {
    if (!is_array($value)) { return; }
    foreach ($value as $key=>$child) {
        if (!$inHashes && in_array($key,$forbidden,true)) { throw new RuntimeException('M64 public allowlist leaked a raw diagnostic field.'); }
        $check($child,$key==='snapshot_hashes');
    }
};
$check($out);
$filePath = $dir.'/bank-author-contract-v1.json';
if (file_exists($filePath) || is_link($filePath)) { throw new RuntimeException('M64 teaching contract receipt exists; no overwrite.'); }
$bytes = json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$f=fopen($filePath,'x');
try { if (!$f || fwrite($f,$bytes)!==strlen($bytes) || !fflush($f)) { throw new RuntimeException('M64 teaching contract projection incomplete.'); } }
finally { if (is_resource($f)) { fclose($f); } }
echo Json::json(['file'=>basename($filePath),'sha256'=>hash('sha256',$bytes),'targets_sha256'=>Json::digest($out['targets']),'bytes'=>strlen($bytes),'raw_diagnostic_fields'=>0,'db_writes'=>0])."\n";
