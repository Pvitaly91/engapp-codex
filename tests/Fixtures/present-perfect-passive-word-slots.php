<?php
declare(strict_types=1);

// Actual catalog payloads only. No Laravel kernel, .env, DB or network.
require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Support\PassiveVoicePracticeQuality as Quality;
use App\Support\SentenceReorderQuestionFactory;

$compiled = array_merge(Quality::compile('present-perfect', 'standard')['questions'], Quality::compile('present-perfect', 'builder')['questions']);
$rows = [];
foreach ($compiled as $row) {
    $answers = array_map(static fn ($marker) => $marker['answer'], $row['markers']);
    $rows[] = Quality::decorate([
        'uuid' => $row['uuid'], 'question' => $row['question'], 'type' => (string) $row['type'], 'level' => $row['level'],
        'answer_map' => $answers, 'answers' => array_values($answers), 'markers' => array_keys($answers),
        'verb_hint' => $row['verb_hint'], 'verb_hints' => array_map(static fn ($marker) => $marker['verb_hint'], $row['markers']),
        'markers_count' => count($answers), 'options' => $row['options'] ?? $row['markers']['a1']['options'],
        'options_by_marker' => $row['options_by_marker'] ?? array_map(static fn ($marker) => $marker['options'] ?? [], $row['markers']),
    ]);
}

echo json_encode(['plain' => $rows, 'mixed' => SentenceReorderQuestionFactory::addToMixedTheoryTest($rows, [
    'theory_page_mixed_type_profile' => Quality::REVISION, 'theory_page_mixed_interleave_question_types' => true,
    '__meta' => ['theory_page_mixed_polyglot_test' => true],
])], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
