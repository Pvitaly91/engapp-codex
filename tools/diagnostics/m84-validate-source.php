<?php
/** Pure frozen-source validation. No application bootstrap, HTTP or SQL. */
if(PHP_SAPI!=='cli'||count($argv)!==1){throw new RuntimeException('M84 source validation takes no args.');}
$root='D:/DEV/htdocs/gramlyze.loc';require $root.'/vendor/autoload.php';
$sources=App\Support\M84WordOrderHaveGot::load($root);App\Support\M84WordOrderHaveGot::validate(...$sources);
$supplement=App\Support\M84QuestionSupplement::load($root);$audit=App\Support\M84BuilderSelectionPresentation::loadAudit($root);
echo json_encode(['owners'=>count($sources[2]['lessons']),'supplement'=>array_sum(array_map(static fn($b)=>count($b['questions']),$supplement['banks'])),'audit_targets'=>count($audit['targets']),'db_connections'=>0,'db_writes'=>0],JSON_THROW_ON_ERROR)."\n";
