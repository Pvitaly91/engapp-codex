<?php
declare(strict_types=1);

// Compatibility fixture for already-saved grouped progress, not today's builder policy.
require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Support\AcceptedAnswerVariants;
use App\Support\FinitePracticeQualityCatalog;
use App\Support\PronounsDemonstrativesPracticeQuality as Quality;

$path = dirname(__DIR__, 2).'/docs/content/finite-builder-word-slots.previous.v1.json';
if (hash_file('sha256', $path) !== '0b997e24a644968772538bd31133db70e50bb5259f6744ca1a6167cef2ed8430') {
    throw new RuntimeException('The exact pre-word-slot educational checkpoint is required.');
}
$previous = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
$rows = [];
foreach (array_keys(Quality::TOPICS) as $topic) {
    $authors = array_column(Quality::source($topic)['questions'], null, 'id');
    foreach (Quality::compile($topic, 'builder')['questions'] as $row) {
        $author = $authors[$row['author_id']]; $old = $previous[$row['uuid']][0];
        if ($old['answers'] !== ($author['compose_slots'] ?? preg_split('/\s+/u', $author['target']))
            || implode(' ', $old['answers']) !== $author['target']) {
            throw new RuntimeException('Legacy grouped fixture no longer matches its pinned authored target.');
        }
        $accepted = [];
        foreach ($old['answers'] as $index => $answer) {
            $aliases = FinitePracticeQualityCatalog::displayAliasesForToken($answer, $author['display_synonyms'] ?? []);
            $accepted['a'.($index + 1)] = array_values(array_unique(array_merge(...array_map(AcceptedAnswerVariants::for(...), array_merge([$answer], $aliases)))));
        }
        $rows[] = ['uuid' => $row['uuid'], 'question' => $old['question'], 'type' => '4', 'level' => $row['level'],
            'answers' => $old['answers'], 'answer' => $old['answers'][0], 'markers' => $old['markers'], 'markers_count' => count($old['markers']),
            'answer_map' => array_combine($old['markers'], $old['answers']), 'psp_content_revision' => $old['revision'],
            'compose_content_revision' => $old['revision'], 'compose_accepted_sentences' => $row['accepted_target_texts'],
            'accepted_answers_by_marker' => $accepted];
    }
}
echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
