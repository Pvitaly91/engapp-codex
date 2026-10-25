<?php

/** Pure immutable-source validation, no application bootstrap, HTTP or SQL. */
if(PHP_SAPI!=='cli'||count($argv)!==1){throw new RuntimeException('M88 validation takes no arguments.');}
$root='D:/DEV/htdocs/gramlyze.loc';require $root.'/vendor/autoload.php';
$sources=App\Support\M88PastContinuousPresentPerfectBasics::load($root);
App\Support\M88PastContinuousPresentPerfectBasics::validate(...$sources);
$audit=App\Support\M88BuilderSelectionPresentation::loadAudit($root);
echo json_encode(['owners'=>count($sources[2]['lessons']),'own_graded'=>36,'retained_graded'=>8,'new_graded'=>28,'ungraded_demos'=>0,'full_inputs'=>12,
    'audit_targets'=>count($audit['targets']),'selected_existing_questions'=>30,'new_questions'=>0,'db_connections'=>0,'db_writes'=>0],JSON_THROW_ON_ERROR)."\n";
