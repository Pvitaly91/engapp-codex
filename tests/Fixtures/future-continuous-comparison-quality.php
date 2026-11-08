<?php
declare(strict_types=1);

// File-only export of actual compiled/decorated rows; no Laravel/.env/DB.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$payloads = [];
foreach (App\Support\FutureContinuousComparisonPracticeQuality::questions() as $row) {
    $answers = array_map(static fn ($marker) => $marker['answer'], $row['markers']);
    $payloads[] = App\Support\FutureContinuousComparisonPracticeQuality::decorate([
        'uuid' => $row['uuid'], 'question' => $row['question'], 'type' => (string) $row['type'], 'level' => $row['level'],
        'answer_map' => $answers, 'answers' => array_values($answers), 'markers' => array_keys($answers),
        'verb_hint' => $row['verb_hint'],
        'verb_hints' => array_map(static fn ($marker) => $marker['verb_hint'], $row['markers']),
        'markers_count' => count($answers), 'options' => $row['options'] ?? $row['markers']['a1']['options'],
        'options_by_marker' => $row['options_by_marker'] ?? array_map(static fn ($marker) => $marker['options'] ?? [], $row['markers']),
    ]);
}
if (($argv[1] ?? '') === '--mixed') {
    $payloads = App\Support\SentenceReorderQuestionFactory::addToMixedTheoryTest($payloads, [
        'theory_page_mixed_type_profile' => App\Support\FutureContinuousComparisonPracticeQuality::REVISION,
        'theory_page_mixed_interleave_question_types' => true,
        '__meta' => ['theory_page_mixed_polyglot_test' => true],
    ]);
}
echo json_encode($payloads, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
