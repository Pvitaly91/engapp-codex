<?php
/** Pure immutable-source validation, no bootstrap/HTTP/SQL. */
if(PHP_SAPI!=='cli'||count($argv)!==1){throw new RuntimeException('M85 validation takes no arguments.');}
$root='D:/DEV/htdocs/gramlyze.loc';require $root.'/vendor/autoload.php';
$sources=App\Support\M85VerbToBe::load($root);App\Support\M85VerbToBe::validate(...$sources);
$audit=App\Support\M85BuilderSelectionPresentation::loadAudit($root);
echo json_encode(['owners'=>count($sources[2]['lessons']),'own_graded'=>36,'full_inputs'=>12,
    'audit_targets'=>count($audit['targets']),'selected_existing_questions'=>30,'db_connections'=>0,'db_writes'=>0],JSON_THROW_ON_ERROR)."\n";
