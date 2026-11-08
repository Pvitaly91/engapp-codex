<?php
declare(strict_types=1);

// One-time mechanical public content projection: no DB/runtime identity is copied.
$root = dirname(__DIR__, 2);
$baselinePath = $root.'/storage/app/finite-builder-word-slots/baseline.json';
if (hash_file('sha256', $baselinePath) !== 'f671cd7b8d9d376e9bbb3cab539f7f8a8f3dee186b767031467e84186425a88c') {
    throw new RuntimeException('Exact before checkpoint required.');
}
$baseline = json_decode(file_get_contents($baselinePath), true, flags: JSON_THROW_ON_ERROR);
$rows = [];
foreach ($baseline['banks'] as $bank) {
    foreach ($bank['definition']['questions'] as $row) {
        $rows[$row['uuid']][] = ['question' => $row['question'], 'answers' => array_column($row['markers'], 'answer'),
            'markers' => array_keys($row['markers']),
            'revision' => hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))];
    }
}
foreach (json_decode(file_get_contents($root.'/docs/content/passive-voice-quality/present-perfect.word-slots.previous.v1.json'), true, flags: JSON_THROW_ON_ERROR) as $uuid => $row) {
    if (!isset($rows[$uuid])) { throw new RuntimeException('Earlier UUID outside scope.'); }
    $rows[$uuid][] = $row;
}
ksort($rows);
$path = $root.'/docs/content/finite-builder-word-slots.previous.v1.json';
$bytes = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
if (is_file($path) && file_get_contents($path) !== $bytes) { throw new RuntimeException('Existing projection differs.'); }
if (!is_file($path) && file_put_contents($path, $bytes) !== strlen($bytes)) { throw new RuntimeException('Incomplete projection.'); }
echo json_encode(['questions' => count($rows), 'sha256' => hash_file('sha256', $path)])."\n";
