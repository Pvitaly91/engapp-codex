<?php

/** Pure immutable-source validation, no application bootstrap, HTTP or SQL. */
if(PHP_SAPI!=='cli'||count($argv)!==1){throw new RuntimeException('M87 validation takes no arguments.');}
$root='D:/DEV/htdocs/gramlyze.loc';require $root.'/vendor/autoload.php';
$sources=App\Support\M87ContinuousCompletionPastSimple::load($root);
App\Support\M87ContinuousCompletionPastSimple::validate(...$sources);
$audit=App\Support\M87BuilderSelectionPresentation::loadAudit($root);
echo json_encode(['owners'=>count($sources[2]['lessons']),'own_graded'=>57,'ungraded_demos'=>4,'full_inputs'=>16,
    'audit_targets'=>count($audit['targets']),'selected_existing_questions'=>30,'new_questions'=>0,'db_connections'=>0,'db_writes'=>0],JSON_THROW_ON_ERROR)."\n";
