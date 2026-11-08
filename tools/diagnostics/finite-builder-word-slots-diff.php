<?php
declare(strict_types=1);

// Pure before/after scope audit. The private baseline is never exported or committed.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$root = dirname(__DIR__, 2);
$path = $root.'/storage/app/finite-builder-word-slots/baseline.json';
if (hash_file('sha256', $path) !== 'f671cd7b8d9d376e9bbb3cab539f7f8a8f3dee186b767031467e84186425a88c') {
    throw new RuntimeException('Exact pre-edit checkpoint required.');
}
$baseline = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
$counts = ['banks' => 0, 'builders' => 0, 'changed_builders' => 0, 'changed_prompts' => 0, 'changed_hints' => 0,
    'changed_banks' => 0, 'gap_rows_unchanged' => 0, 'word_slots' => 0];
$failures = [];
foreach ($baseline['banks'] as $bank) {
    $catalog = $bank['catalog']; $topic = $bank['topic']; ++$counts['banks'];
    $oldSource = json_decode($bank['source_bytes'], true, flags: JSON_THROW_ON_ERROR);
    $newSource = $catalog::source($topic);
    $oldRows = array_column($oldSource['questions'], null, 'id');
    foreach ($newSource['questions'] as $row) {
        $old = $oldRows[$row['id']] ?? null;
        if (!$old) { $failures[] = "$catalog/$topic: added row"; continue; }
        if ($row['kind'] === 'gap') {
            if ($old !== $row) { $failures[] = "$catalog/$topic/{$row['id']}: gap altered"; }
            ++$counts['gap_rows_unchanged'];
        } else {
            $counts['changed_prompts'] += (int)($old['question'] !== $row['question']);
            $counts['changed_hints'] += (int)($old['hint_uk'] !== $row['hint_uk']);
            foreach (['question', 'uk', 'hint_uk', 'builder_prompt_sentence_only'] as $key) { unset($old[$key], $row[$key]); }
            if ($old !== $row) { $failures[] = "$catalog/$topic/{$row['id']}: non-prompt source changed"; }
        }
    }
    if (count($oldRows) !== count($newSource['questions'])) { $failures[] = "$catalog/$topic: row count altered"; }
    $oldDefinition = array_column($bank['definition']['questions'], null, 'uuid');
    $newDefinition = $catalog::compile($topic, 'builder'); $bankChanged = false;
    foreach ($newDefinition['questions'] as $row) {
        ++$counts['builders']; $old = $oldDefinition[$row['uuid']] ?? [];
        if ($old !== $row) { ++$counts['changed_builders']; $bankChanged = true; }
        foreach (['uuid','id','level','type','target_text','accepted_target_texts','source','token_groups','reorder_groups'] as $key) {
            if (($old[$key] ?? null) !== ($row[$key] ?? null)) { $failures[] = "$catalog/$topic/{$row['uuid']}: $key altered"; }
        }
        foreach ($row['markers'] as $marker => $data) {
            ++$counts['word_slots'];
            if (preg_match('/\s/u', $data['answer'])) { $failures[] = "$catalog/$topic/{$row['uuid']}/$marker: multiword slot"; }
            foreach ($data['options'] ?? [] as $option) {
                if (preg_match('/\s/u', $option)) { $failures[] = "$catalog/$topic/{$row['uuid']}/$marker: multiword choice"; }
            }
        }
    }
    $counts['changed_banks'] += (int)$bankChanged;
    $oldStandard = json_decode($bank['standard_bytes'], true, flags: JSON_THROW_ON_ERROR);
    $newStandard = $catalog::compile($topic, 'standard');
    unset($oldStandard['compiled_from'], $newStandard['compiled_from']);
    if ($oldStandard !== $newStandard) { $failures[] = "$catalog/$topic: standard bank changed"; }
}
echo json_encode(['ok' => $failures === [], 'counts' => $counts, 'failures' => $failures], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
exit($failures === [] ? 0 : 1);
