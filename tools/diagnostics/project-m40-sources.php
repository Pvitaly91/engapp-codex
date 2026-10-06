<?php

// Exclusive, deterministic technical projection of accepted M24. No Laravel or
// DB boot; existing native configs/body strings remain exact before values.
require __DIR__.'/m40-author-projection.php';

$root = dirname(__DIR__, 2);
$masterPath = 'docs/content/m24-authored-content.v1.json';
$notesPath = 'docs/content/m24-author-sources.md';
$masterBytes = file_get_contents($root.'/'.$masterPath); $masterLf = str_replace("\r\n", "\n", $masterBytes);
$notesBytes = file_get_contents($root.'/'.$notesPath); $notesLf = str_replace("\r\n", "\n", $notesBytes);
$masterSha = '33373412ed077bf7fa0aea8dde8a6d2c211c126288b6cedfdde299ded43bf5a2';
$masterBlob = '2d7c450533795bdab3267bd029b54fb325ff7cbb';
$notesBlob = '3330b22b22e401eec9c36275651e60645b4b87f6';
if (hash('sha256', $masterLf) !== $masterSha || hash('sha1', 'blob '.strlen($masterLf)."\0".$masterLf) !== $masterBlob
    || hash('sha1', 'blob '.strlen($notesLf)."\0".$notesLf) !== $notesBlob) {
    throw new RuntimeException('Immutable M24 author source differs; stop without writes.');
}
$master = json_decode($masterBytes, true, flags: JSON_THROW_ON_ERROR);
foreach (['rewrite', 'summarise', 'translate_again', 'add_examples', 'add_exercises', 'production_write', 'mixed_question_bank_write'] as $flag) {
    if (($master['content_policy'][$flag] ?? null) !== false) { throw new RuntimeException('Frozen M24 author policy differs.'); }
}
$baseSha = 'd92d759a5c950e0f9eabd2f0fa95557327f42005';
$blobs = ['e4a950880b6f4fc0e1b865edc886c658805ffd3c', '55317a6a7bb48a640e804ec613afb83bdca05373', '8b6422baea0cf0c9f4ec1ca1ed921b9fab91c53d'];
$beforePath = 'database/content-patches/m40-m24-tenses-b1-before.json';
$sourcePath = 'database/content-patches/m40-m24-tenses-b1.v1.json';
$mode = $argv[1] ?? '';

if ($mode === '--capture-before') {
    if (file_exists($root.'/'.$beforePath)) { throw new RuntimeException('M40 before manifest already exists.'); }
    $before = ['base_sha' => $baseSha,
        'author_master_source' => ['path' => $masterPath, 'git_blob' => $masterBlob, 'git_lf_sha256' => $masterSha,
            'working_raw_sha256' => hash('sha256', $masterBytes), 'git_lf_bytes' => $masterLf],
        'author_notes_source' => ['path' => $notesPath, 'git_blob' => $notesBlob, 'working_raw_sha256' => hash('sha256', $notesBytes)],
        'targets' => []];
    foreach ($master['lessons'] as $i => $lesson) {
        $bytes = file_get_contents($root.'/'.$lesson['definition_path']); $lf = str_replace("\r\n", "\n", $bytes);
        if (hash('sha1', 'blob '.strlen($lf)."\0".$lf) !== $blobs[$i]) { throw new RuntimeException('M24 accepted definition blob differs.'); }
        $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if ($definition['seeder']['class'] !== $lesson['seeder'] || $definition['page']['title'] !== $lesson['preserve_page_title']
            || $definition['page']['subtitle_html'] !== $lesson['subtitle_html'] || $definition['page']['subtitle_text'] !== $lesson['subtitle_text']
            || count($definition['page']['blocks']) !== $lesson['baseline_source_block_count'] + 1) {
            throw new RuntimeException('M24 definition metadata/source structure differs.');
        }
        foreach ($lesson['existing_blocks'] as $config) {
            $block = $definition['page']['blocks'][$config['source_index']];
            if ($block['type'] !== $config['preserve_type'] || json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR) !== $config['replacement_body_json']) {
                throw new RuntimeException('M24 existing native learner source differs.');
            }
        }
        if ($definition['page']['blocks'][$lesson['baseline_source_block_count']]['body'] !== $lesson['append_blocks'][0]['body_html']) {
            throw new RuntimeException('M24 appended author box differs.');
        }
        $before['targets'][] = ['path' => $lesson['definition_path'], 'source_sha256' => hash('sha256', $bytes),
            'source_git_blob' => $blobs[$i], 'before' => $definition];
    }
    $file = fopen($root.'/'.$beforePath, 'xb'); $bytes = m40Json($before, true);
    if ($file === false || fwrite($file, $bytes) !== strlen($bytes)) { throw new RuntimeException('M40 exclusive before capture failed.'); }
    fclose($file);
    echo m40Json(['captured' => 3, 'before_sha256' => hash('sha256', $bytes), 'master_raw_sha256' => hash('sha256', $masterBytes)], true);
    exit;
}
if (!in_array($mode, ['--project', '--check'], true)) { throw new RuntimeException('Use --capture-before once, then --project/--check --linked-banks FILE.'); }
$bankFlag = array_search('--linked-banks', $argv, true);
if ($bankFlag === false || empty($argv[$bankFlag + 1])) { throw new RuntimeException('Actual read-only bank evidence required.'); }
$bankRecords = json_decode(file_get_contents($argv[$bankFlag + 1]), true, flags: JSON_THROW_ON_ERROR);
$banks = $bankRecords['linked_banks'] ?? $bankRecords;
if (count($banks) !== 3) { throw new RuntimeException('M40 exactly three actual primary bank records required.'); }
$before = json_decode(file_get_contents($root.'/'.$beforePath), true, flags: JSON_THROW_ON_ERROR);
if ($before['base_sha'] !== $baseSha || $before['author_master_source']['git_lf_bytes'] !== $masterLf || count($before['targets']) !== 3) {
    throw new RuntimeException('M40 frozen before/master identity differs.');
}
require __DIR__.'/m40-practice-projection.php';
$projection = ['version' => 1, 'targets' => []]; $files = []; $states = [];
foreach ($master['lessons'] as $i => $lesson) {
    $record = $before['targets'][$i]; $original = $record['before']; $identity = $lesson['seeder'];
    if ($record['path'] !== $lesson['definition_path'] || $record['source_git_blob'] !== $blobs[$i]
        || $original['seeder']['class'] !== $identity || $original['page']['locale'] !== 'uk'
        || $original['page']['category']['slug'] !== $lesson['category_path'][0] || !isset($banks[$identity])) {
        throw new RuntimeException('M40 exact owner/category/locale/bank mapping differs.');
    }
    $bank = $banks[$identity];
    if (($bank['question_type'] ?? null) !== '4' || empty($bank['seeder_class']) || empty($bank['question_ids'])
        || count(array_unique($bank['question_ids'])) !== count($bank['question_ids'])) {
        throw new RuntimeException('M40 primary bank evidence incomplete or duplicated.');
    }
    $nativeCount = count($lesson['existing_blocks']); $parts = m40AppendSections($lesson['append_blocks'][0]['body_html']);
    $blocks = array_slice($original['page']['blocks'], 0, $nativeCount); $plans = [];
    foreach ($parts as $j => $part) {
        $key = 'm40-'.$lesson['key'].'-section-'.($j + 1);
        if ($j < 2) {
            $type = 'usage-panels'; $data = ['title' => $part['title'], 'intro' => $part['html'], 'sections' => []];
        } else {
            $type = 'practice-set'; $data = m40Practice($part['author_self_check'], $i, $bank);
            $data['title'] = $part['title'];
        }
        $data['m40_v1'] = ['key' => $key, 'legacy_section' => $j + 1];
        if ($j === 2) { $data['m40_v1']['legacy_practice_id'] = $part['legacy_practice_id']; }
        $config = $j === 0 ? $original['page']['blocks'][$nativeCount]
            : ['column' => 'left', 'heading' => null, 'level' => $j === 2 ? $lesson['level'] : null,
                'uuid_key' => $key, 'inherit_base_tags' => false, 'tags' => []];
        $config['type'] = $type; $config['body'] = m40Json($data); $blocks[] = $config;
        $plans[] = ['key' => $key, 'legacy_section' => $j + 1, 'type' => $type, 'source_title' => $part['title'], 'points' => []];
    }
    $after = $original; $after['page']['blocks'] = $blocks;
    foreach (array_slice($after['page']['blocks'], 0, $nativeCount) as $j => $config) {
        if ($config !== $original['page']['blocks'][$j]) { throw new RuntimeException('M40 existing native config/body bytes changed.'); }
    }
    $current = json_decode(file_get_contents($root.'/'.$record['path']), true, flags: JSON_THROW_ON_ERROR);
    if ($current !== $original && $current !== $after) { throw new RuntimeException('M40 refuses to overwrite a manual canonical change.'); }
    $states[] = $current === $original ? 'before' : 'after';
    $projection['targets'][] = ['path' => $record['path'], 'identity' => $identity, 'slug' => $original['slug'],
        'ancestry' => $lesson['category_path'], 'native_count' => $nativeCount, 'after' => $after,
        'plans' => $plans, 'detail_quality_audit' => m40DetailAudit($lesson, $parts)];
    $files[$record['path']] = m40Json($after, true);
}
$bytes = m40Json($projection, true);
if ($mode === '--project') {
    if ($states !== ['before', 'before', 'before'] || file_exists($root.'/'.$sourcePath)) {
        throw new RuntimeException('M40 projection requires all exact before states and a new exclusive snapshot.');
    }
    $file = fopen($root.'/'.$sourcePath, 'xb');
    if ($file === false || fwrite($file, $bytes) !== strlen($bytes)) { throw new RuntimeException('M40 exclusive projection creation failed.'); }
    fclose($file);
    foreach ($files as $path => $data) { if (file_put_contents($root.'/'.$path, $data) !== strlen($data)) { throw new RuntimeException('M40 canonical generation incomplete.'); } }
}
$audits = array_merge(...array_column($projection['targets'], 'detail_quality_audit'));
echo m40Json(['mode' => $mode, 'states' => $states, 'before_sha256' => hash_file('sha256', $root.'/'.$beforePath),
    'source_sha256' => hash('sha256', $bytes), 'definition_sha256' => array_map(fn ($data) => hash('sha256', $data), $files),
    'blocks' => array_map(fn ($target) => count($target['after']['page']['blocks']), $projection['targets']),
    'audit_candidates' => count($audits), 'short_candidates' => count(array_filter($audits, fn ($candidate) => $candidate['detail_word_count'] < 30))], true);
