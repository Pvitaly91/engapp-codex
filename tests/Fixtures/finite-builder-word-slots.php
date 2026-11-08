<?php
declare(strict_types=1);

// Pure canonical payloads, without Laravel, .env, DB, learner progress or network.
require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Support\FinitePracticeQualityCatalog;
use App\Support\PassivePracticeQuality;

$rows = [];
$catalogs = (new ReflectionClass(PassivePracticeQuality::class))->getConstant('CATALOGS');
foreach ($catalogs as $catalog) {
    if (!is_subclass_of($catalog, FinitePracticeQualityCatalog::class)) { continue; }
    foreach (array_keys($catalog::TOPICS) as $topic) {
        foreach ($catalog::compile($topic, 'builder')['questions'] as $row) {
            $answers = array_column($row['markers'], 'answer');
            $payload = ['uuid' => $row['uuid'], 'question' => $row['question'], 'type' => '4', 'level' => $row['level'],
                'answer_map' => array_combine(array_keys($row['markers']), $answers), 'answers' => $answers,
                'markers' => array_keys($row['markers']), 'markers_count' => count($answers),
                'verb_hint' => $row['verb_hint'], 'options' => $row['options'], 'options_by_marker' => $row['options_by_marker']];
            $payload = PassivePracticeQuality::decorate($payload, $row);
            $payload['test_catalog'] = $catalog; $payload['test_topic'] = $topic;
            $rows[] = $payload;
        }
    }
}
echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
