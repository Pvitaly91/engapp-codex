<?php

// Read-only, finite source audit. No application bootstrap, database, writes or HTTP.
declare(strict_types=1);

const PPC_AUDIT_BASE = '4644a1729d326b9a967d03c397998506f00d704b';
$root = dirname(__DIR__, 2);
$catalogHashes = [];
foreach (array_merge(glob($root.'/scripts/lib/ppc_quality_mixed*.php'), [$root.'/scripts/lib/ppc_quality_builder.php']) as $path) {
    $catalogHashes[substr(str_replace('\\', '/', $path), strlen(str_replace('\\', '/', $root)) + 1)] = hash_file('sha256', $path);
}
require_once $root.'/scripts/lib/ppc_quality_mixed.php';
require_once $root.'/scripts/lib/ppc_quality_builder.php';
$mixedCatalog = ppcQualityCatalog(); $builderCatalog = ppcQualityBuilderBanks();

function ppcAuditNormalize(string $text): string
{
    $text = preg_replace('/\{[a-z_]+\d+\}/i', '{gap}', $text);
    $text = mb_strtolower(str_replace(['’', '‘', 'ʼ', '`', '´'], "'", $text));
    return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text)));
}

function ppcAuditTemplate(string $text): string
{
    // Candidate discovery only: these masks never determine correctness or duplicate status.
    $common = ['The','By','When','Until','How','Had','Before','Since','For','At','In','During','Throughout','Only','Even','Although','What','Why','Where','Which','Who','Whose','A','Each','Every','Most','All','Yes','No','Neither','Not','Both','There','As','With','To','On','After','Despite','If'];
    $text = preg_replace_callback('/\b[A-Z][a-z]+\b/', static fn ($m) => in_array($m[0], $common, true) ? $m[0] : 'ROLE', $text);
    $text = ppcAuditNormalize($text);
    $text = preg_replace('/\b(i|you|we|they|he|she|it|my|your|our|their|his|her|its|me|him|us|them)\b/', 'role', $text);
    $text = preg_replace('/\b(one|two|three|four|five|six|seven|eight|nine|ten|eleven|twelve|twenty|thirty|forty|ninety|\d+)\b/', 'number', $text);
    return $text;
}

function ppcAuditGrams(string $text): array
{
    $words = explode(' ', ppcAuditNormalize($text)); $grams = [];
    for ($i = 0; $i + 2 < count($words); $i++) { $grams[implode(' ', array_slice($words, $i, 3))] = true; }
    // Universal tense morphology is not an independent authored-task similarity.
    unset($grams['had been working'], $grams['had not been'], $grams['not been working']);
    return $grams;
}

function ppcAuditQuestionContext(string $text): string
{
    // Exact/normalized question checks still use the complete visible question.
    // Only fuzzy discovery excludes these finite shared UI/order instructions,
    // whose repetition is not repetition of an authored scene or learning task.
    foreach ([
        'Побудуй речення про попередній процес у Past Perfect Continuous. ',
        'Відтвори тривалий стан у Past Perfect Simple. ',
        'Почни з питальної частини або допоміжного дієслова; обставини залиш після дієслівної групи. ',
        'Збережи порядок смислових частин умови; початкову часову, допустову або тематичну частину залиш на початку. ',
        'Почни з підмета головної частини; часові та причинні обставини не перенось на початок. ',
        'Почни з усієї питальної частини; далі — допоміжне дієслово й підмет. ',
    ] as $prefix) {
        if (str_starts_with($text, $prefix)) { $text = substr($text, strlen($prefix)); }
    }
    return $text;
}

function ppcAuditCompleted(array $question): string
{
    if (filledPpcAudit($question['target_text'] ?? null)) { return trim($question['target_text']); }
    $answers = $question['answers'] ?? [];
    if (isset($question['markers'])) { $answers = array_map(static fn ($m) => $m['answer'], $question['markers']); }
    if (!$answers && isset($question['answer'])) { $answers = ['a1' => $question['answer']]; }
    return preg_replace_callback('/\{([a-z_]+\d+)\}/i', static fn ($m) => (string) ($answers[$m[1]] ?? $m[0]), $question['question']);
}

function filledPpcAudit(mixed $value): bool { return is_string($value) && trim($value) !== ''; }

function ppcAuditGit(string $root, string $path): string
{
    $process = proc_open(['git', 'show', PPC_AUDIT_BASE.':'.$path], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, null, ['bypass_shell' => true]);
    if (!is_resource($process)) { throw new RuntimeException('Cannot read immutable Git baseline.'); }
    $out = stream_get_contents($pipes[1]); $err = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
    if (proc_close($process) !== 0) { throw new RuntimeException('Git baseline read failed: '.$err); }
    return $out;
}

function ppcAuditGroups(array $rows, string $field, bool $normalized): array
{
    $groups = [];
    foreach ($rows as $row) { $key = $normalized ? ppcAuditNormalize($row[$field]) : $row[$field]; $groups[$key][] = $row['uuid']; }
    return array_values(array_filter($groups, static fn ($ids) => count($ids) > 1));
}

function ppcAuditPairs(array $rows): array
{
    $pairs = []; $count = count($rows);
    foreach ($rows as &$row) { $row['_question_norm'] = ppcAuditNormalize($row['question']); $row['_question_grams'] = ppcAuditGrams(ppcAuditQuestionContext($row['question'])); $row['_norm'] = ppcAuditNormalize($row['completed_target']); $row['_template'] = ppcAuditTemplate($row['completed_target']); $row['_grams'] = ppcAuditGrams($row['completed_target']); } unset($row);
    for ($i = 0; $i < $count; $i++) {
        for ($j = $i + 1; $j < $count; $j++) {
            $a = $rows[$i]; $b = $rows[$j]; $reasons = [];
            if ($a['question'] === $b['question']) { $reasons[] = 'exact-question'; }
            elseif ($a['_question_norm'] === $b['_question_norm']) { $reasons[] = 'normalized-question'; }
            if ($a['completed_target'] === $b['completed_target']) { $reasons[] = 'exact-completed-target'; }
            elseif ($a['_norm'] === $b['_norm']) { $reasons[] = 'normalized-completed-target'; }
            if ($a['_template'] === $b['_template'] && $a['_norm'] !== $b['_norm']) { $reasons[] = 'role-duration-template'; }
            $union = count($a['_grams']) + count($b['_grams']);
            $dice = $union ? 2 * count(array_intersect_key($a['_grams'], $b['_grams'])) / $union : 0;
            if ($dice >= 0.72 && count(array_intersect_key($a['_grams'], $b['_grams'])) >= 3) { $reasons[] = 'three-gram-dice'; }
            $questionUnion = count($a['_question_grams']) + count($b['_question_grams']);
            $questionShared = count(array_intersect_key($a['_question_grams'], $b['_question_grams']));
            $questionDice = $questionUnion ? 2 * $questionShared / $questionUnion : 0;
            if ($questionDice >= 0.72 && $questionShared >= 3) { $reasons[] = 'question-three-gram-dice'; }
            if (!$reasons) { continue; }
            $compact = static fn ($r) => array_intersect_key($r, array_flip(['uuid','bank','type','level','question','completed_target','learning_focus']));
            $pairs[] = ['pair' => [$a['uuid'], $b['uuid']], 'reasons' => $reasons, 'three_gram_dice' => round($dice, 3), 'question_three_gram_dice' => round($questionDice, 3), 'cross_bank' => $a['bank'] !== $b['bank'], 'left' => $compact($a), 'right' => $compact($b)];
        }
    }
    return $pairs;
}

$paths = [];
foreach (['Forms','Negatives','Questions','TimeExpressions'] as $family) {
    $paths['Mixed/'.$family] = 'database/seeders/V3/Tenses/PastPerfectContinuous/PastPerfectContinuous'.$family.'AllLevelsV3Seeder/definition.json';
    $paths['Builder/'.$family] = 'database/seeders/V3/Polyglot/PolyglotPastPerfectContinuous'.$family.'AllLevelsLessonSeeder/definition.json';
}
$paths['Builder/BasicsB2'] = 'database/seeders/V3/Polyglot/PolyglotPastPerfectContinuousBasicsB2LessonSeeder/definition.json';
$result = ['schema_version' => 1, 'scope' => 'nine connected PPC banks only; translations are not separate authored tasks', 'baseline_commit' => PPC_AUDIT_BASE,
    'candidate_method' => 'Exact/normalized complete questions and completed targets; completed-target role/number masks and >=0.72 question-context or completed-target three-gram Dice with >=3 shared grams discover candidates only. Question-context fuzzy comparison omits six finite shared Builder UI/order prefixes. Equal correct forms alone are not duplicates.', 'source_sha256' => $catalogHashes, 'phases' => []];
$baselineLineage = null;
foreach (['before','after'] as $phase) {
    $rows = []; $inventory = [];
    foreach ($paths as $bank => $path) {
        $bytes = $phase === 'before' ? ppcAuditGit($root, $path) : file_get_contents($root.'/'.$path);
        $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR); $levels = []; $types = [];
        $family = explode('/', $bank)[1]; $mode = explode('/', $bank)[0];
        foreach ($definition['questions'] as $i => $question) {
            $level = $question['level']; $type = (string) ($question['type'] ?? $definition['defaults']['type']);
            $levels[$level] = ($levels[$level] ?? 0) + 1; $types['type_'.$type] = ($types['type_'.$type] ?? 0) + 1;
            $focus = $mode === 'Mixed' ? ($mixedCatalog[$family][$level][$i % 12]['focus'] ?? null)
                : ($question['quality_review']['learning_focus'] ?? $builderCatalog[$family][$level][$family === 'BasicsB2' ? $i : $i % 12]['focus'] ?? null);
            $rows[] = ['uuid' => $question['uuid'], 'bank' => $bank, 'type' => $type, 'level' => $level,
                'question' => $question['question'], 'completed_target' => ppcAuditCompleted($question),
                'learning_focus' => $phase === 'after' ? $focus : null];
        }
        $inventory[$bank] = ['count' => count($definition['questions']), 'by_type' => $types, 'by_level' => $levels];
        if ($phase === 'after') { $result['source_sha256'][$path] = hash('sha256', $bytes); }
    }
    if (count($rows) !== 624 || count(array_unique(array_column($rows, 'uuid'))) !== 624) { throw new RuntimeException('The finite 624 UUID scope differs.'); }
    $lineage = array_map(static fn ($row) => array_intersect_key($row, array_flip(['uuid', 'bank', 'type', 'level'])), $rows);
    if ($phase === 'before') {
        $baselineLineage = $lineage;
    } elseif ($lineage !== $baselineLineage) {
        throw new RuntimeException('Editorial UUID, bank, source order, type or CEFR slot changed from the immutable baseline.');
    }
    foreach ($inventory as $bank => &$info) {
        $bankRows = array_values(array_filter($rows, static fn ($r) => $r['bank'] === $bank));
        foreach (['question','completed_target'] as $field) {
            foreach ([false,true] as $normalized) {
                $g = ppcAuditGroups($bankRows, $field, $normalized);
                $info['collisions'][($normalized ? 'normalized_' : 'exact_').$field] = ['groups' => count($g), 'extra_records' => array_sum(array_map(static fn ($ids) => count($ids) - 1, $g))];
            }
        }
    } unset($info);
    $pairs = ppcAuditPairs($rows);
    $groups = []; $bankByUuid = array_column($rows, 'bank', 'uuid');
    foreach (['question','completed_target'] as $field) {
        foreach ([false,true] as $normalized) {
            $name = ($normalized ? 'normalized_' : 'exact_').$field;
            $g = ppcAuditGroups($rows, $field, $normalized);
            $crossGroups = 0; $crossPairs = 0;
            foreach ($g as $ids) {
                if (count(array_unique(array_map(static fn ($id) => $bankByUuid[$id], $ids))) > 1) { $crossGroups++; }
                for ($i = 0; $i < count($ids); $i++) {
                    for ($j = $i + 1; $j < count($ids); $j++) {
                        if ($bankByUuid[$ids[$i]] !== $bankByUuid[$ids[$j]]) { $crossPairs++; }
                    }
                }
            }
            $groups[$name] = ['groups' => count($g), 'extra_records' => array_sum(array_map(static fn ($ids) => count($ids) - 1, $g)), 'cross_bank_groups' => $crossGroups, 'cross_bank_pairs' => $crossPairs, 'uuid_groups' => $g];
        }
    }
    $result['phases'][$phase] = ['count' => 624, 'inventory' => $inventory, 'collisions' => $groups,
        'candidate_pairs' => count($pairs), 'cross_bank_candidate_pairs' => count(array_filter($pairs, static fn ($p) => $p['cross_bank'])), 'candidates' => $pairs];
}
foreach ($result['source_sha256'] as $path => $sha) {
    if (!hash_equals($sha, hash_file('sha256', $root.'/'.$path))) {
        throw new RuntimeException('Sources changed during audit; rerun after catalogue edits finish: '.$path);
    }
}
$mode = $argv[1] ?? 'summary';
if ($mode === 'after-candidates') { $result = $result['phases']['after']['candidates']; }
elseif ($mode === 'after-rows') { $result = $rows; }
elseif ($mode === 'verify-review') {
    $review = json_decode(file_get_contents($root.'/docs/reports/past-perfect-continuous-quality-duplicate-review.json'), true, flags: JSON_THROW_ON_ERROR);
    if ($review['baseline_commit'] !== PPC_AUDIT_BASE || $review['source_sha256'] !== $result['source_sha256']) {
        throw new RuntimeException('Versioned semantic review has stale source hashes or baseline.');
    }
    $reviewed = array_map(static fn ($candidate) => array_diff_key($candidate, array_flip(['decision', 'reason'])), $review['reviewed_after_candidates']);
    if (json_encode($reviewed, JSON_UNESCAPED_UNICODE) !== json_encode($result['phases']['after']['candidates'], JSON_UNESCAPED_UNICODE)) {
        throw new RuntimeException('Versioned semantic review does not cover every current candidate exactly.');
    }
    foreach ($review['reviewed_after_candidates'] as $candidate) {
        if ($candidate['decision'] !== 'keep' || !filledPpcAudit($candidate['reason'])) {
            throw new RuntimeException('An unresolved semantic candidate remains.');
        }
    }
    $result = ['source_fresh' => true, 'baseline_uuid_bank_type_level_order_preserved' => true, 'authored_records' => count($rows), 'reviewed_pairs' => count($reviewed),
        'cross_bank_reviewed_pairs' => $result['phases']['after']['cross_bank_candidate_pairs'], 'unresolved' => 0];
}
elseif ($mode === 'summary') { foreach ($result['phases'] as &$phase) { unset($phase['candidates']); } unset($phase); }
elseif ($mode !== 'all') { throw new RuntimeException('Expected summary, after-candidates, after-rows, verify-review or all.'); }
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
