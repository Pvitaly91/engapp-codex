<?php

// Pure application metadata formatter over the exact approved M41 source.
// No Laravel kernel, .env, HTTP, model, connection or working database.
if (PHP_SAPI !== 'cli') { exit(1); }
$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$bytes = file_get_contents($root.'/docs/content/m41-authored-tense-comparisons.v1.0.1.json');
if (hash('sha256', $bytes) !== '9020cf977e903c76f93c7165bbe402567b5fbf40a98a8139dc6751b223b34553') {
    throw new RuntimeException('M41 approved metadata author bytes differ.');
}
$master = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
$metadata = [];
foreach ($master['lessons'] as $lesson) {
    $metadata[$lesson['theory_path']] = App\Support\PageMetadata::theory($lesson['preserve']['page_title'], 'Tenses',
        htmlspecialchars($lesson['subtitle'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'), $lesson['identity'], 'uk');
}
echo json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
