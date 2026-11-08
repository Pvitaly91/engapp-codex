<?php
declare(strict_types=1);

// File-only export for the real shared browser functions; no Laravel/.env/DB.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$payloads = [];
foreach (App\Support\NounsArticlesQuantityPracticeQuality::questions() as $row) {
    $answers = array_map(static fn ($marker) => $marker['answer'], $row['markers']);
    $payloads[] = App\Support\NounsArticlesQuantityPracticeQuality::decorate([
        'uuid' => $row['uuid'], 'question' => $row['question'], 'type' => (string) $row['type'], 'level' => $row['level'],
        'answer_map' => $answers, 'answers' => array_values($answers), 'markers' => array_keys($answers),
        'markers_count' => count($answers), 'options' => $row['options'] ?? $row['markers']['a1']['options'],
        'options_by_marker' => $row['options_by_marker'] ?? array_map(fn ($marker) => $marker['options'] ?? [], $row['markers']),
    ]);
}
echo json_encode($payloads, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
