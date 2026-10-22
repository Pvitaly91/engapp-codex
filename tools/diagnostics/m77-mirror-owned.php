<?php

declare(strict_types=1);

/** Explicit filesystem transfer only. No application bootstrap, SQL, HTTP or Git. */
const M77_MIRROR_ROOT = 'D:/DEV/htdocs/gramlyze.loc';
const M77_MIRROR_WT = M77_MIRROR_ROOT.'/storage/app/m75-worktree';
const M77_MIRROR_BEFORE_SHA = '3ee7c26081727dd746dfe1e94a03dcc4273c48bcb40be5091b84d27bc52cab31';
const M77_MIRROR_LEDGER_SHA = 'bf8544aa970b29400eecb903dd78151dbe7a908888d23842161b398916bd4b56';
const M77_MIRROR_CHECKSUMS_SHA = 'ce5e2ad30a751fd318b21375347a4d61c2746195323233d3d088c1fa8face861';
const M77_MIRROR_CONTROLLER_BEFORE = 'c9d41a3f0b49e04a3eb33d060fe31bb6b0f6b00a6de67b06bb98fd08ceeb62ec';

if (PHP_SAPI !== 'cli' || ($argc ?? 0) !== 2 || !in_array($argv[1], ['--preview', '--apply'], true)) {
    throw new RuntimeException('Use --preview or explicitly reviewed --apply; M77 filesystem only.');
}
$applying = $argv[1] === '--apply';
$norm = static fn (string $path): string => str_replace('\\', '/', $path);
foreach ([M77_MIRROR_ROOT, M77_MIRROR_WT] as $base) {
    if (is_link($base) || $norm((string) realpath($base)) !== $base) {
        throw new RuntimeException('M77 mirror fixed physical root/worktree differs.');
    }
}

/** Reject traversal and every existing linked/resolved-away ancestor. */
$path = static function (string $base, string $relative, bool $required = true) use ($norm): string {
    if (preg_match('~^[A-Za-z0-9_./-]+$~D', $relative) !== 1 || str_contains($relative, '..')
        || str_starts_with($relative, '/') || str_contains($relative, '//')) {
        throw new RuntimeException('M77 mirror nonliteral relative path.');
    }
    $full = $base.'/'.$relative;
    $cursor = $base;
    foreach (explode('/', $relative) as $part) {
        $cursor .= '/'.$part;
        if (is_link($cursor) || (file_exists($cursor) && $norm((string) realpath($cursor)) !== $cursor)) {
            throw new RuntimeException('M77 mirror refuses a linked/resolved-away path: '.$relative);
        }
    }
    if ($required && !is_file($full)) { throw new RuntimeException('M77 mirror missing source: '.$relative); }
    return $full;
};
$read = static function (string $base, string $relative, ?string $sha = null) use ($path): string {
    $file = $path($base, $relative);
    $bytes = file_get_contents($file);
    if (!is_string($bytes) || ($sha !== null && !hash_equals($sha, hash('sha256', $bytes)))) {
        throw new RuntimeException('M77 mirror reviewed bytes differ: '.$relative);
    }
    return $bytes;
};
$decode = static fn (string $bytes): array => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
$lf = static fn (string $bytes): string => str_replace("\r\n", "\n", $bytes);
$eolCounts = static fn (string $bytes): array => ['CRLF' => substr_count($bytes, "\r\n"),
    'LF' => substr_count($bytes, "\n") - substr_count($bytes, "\r\n"),
    'bare_CR' => substr_count(str_replace("\r\n", '', $bytes), "\r")];
$eol = static function (string $bytes) use ($eolCounts): string {
    $counts = $eolCounts($bytes);
    if ($counts['bare_CR'] !== 0) {
        throw new RuntimeException('M77 mirror refuses bare-CR source EOL.');
    }
    return $counts['CRLF'] > 0 ? ($counts['LF'] > 0 ? 'MIXED' : 'CRLF') : 'LF';
};
$replace = static function (string $bytes, string $old, string $new): string {
    if ($old === '' || $old === $new || !str_ends_with($old, "\n") || !str_ends_with($new, "\n")) {
        throw new RuntimeException('M77 mirror hunk is not a complete-line replacement.');
    }
    $pattern = '~^'.preg_quote($old, '~').'~m';
    if (preg_match_all($pattern, $bytes) !== 1) { throw new RuntimeException('M77 mirror exact anchored hunk is absent/ambiguous.'); }
    $result = preg_replace_callback($pattern, static fn (): string => $new, $bytes, 1, $count);
    if (!is_string($result) || $count !== 1) { throw new RuntimeException('M77 mirror anchored hunk replacement failed.'); }
    return $result;
};
$before = $decode($read(M77_MIRROR_ROOT, 'storage/app/seo-m77-local/before-v1.json', M77_MIRROR_BEFORE_SHA));
$ledger = $decode($read(M77_MIRROR_WT, 'tests/Fixtures/m77-owned-source-hunks.json', M77_MIRROR_LEDGER_SHA));
$checksPath = 'docs/content/m77-source-checksums.v1.json';
$checks = $decode($read(M77_MIRROR_WT, $checksPath, M77_MIRROR_CHECKSUMS_SHA));
if (($before['served_root'] ?? null) !== M77_MIRROR_ROOT || ($before['source_worktree'] ?? null) !== M77_MIRROR_WT
    || ($before['base_sha'] ?? null) !== '5270494e0f999eee2a3d1fba6fb7c3f46a914481'
    || ($ledger['base_sha'] ?? null) !== $before['base_sha'] || ($ledger['owned_added_registration_lines'] ?? null) !== 12
    || count($ledger['files'] ?? []) !== 9 || count($before['visual_hashes'] ?? []) !== 326
    || count($before['frozen_hashes'] ?? []) !== 2662 || ($checks['status'] ?? null) !== 'frozen'
    || ($checks['package'] ?? null) !== 'm77-one-prepositions-phrasal' || ($checks['version'] ?? null) !== '1.0.0') {
    throw new RuntimeException('M77 mirror finite independent scope differs.');
}
$sharedPaths = [
    'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php', 'app/Support/TheoryLegacyAdapter.php',
    'app/Support/TheoryContentRenderer.php', 'app/Support/PracticeCompletionLinkedPractice.php',
    'app/Services/Theory/TextBlockToQuestionsMatcherService.php', 'app/Services/TheoryCourseManifestService.php',
    'app/Http/Controllers/TestJsV2Controller.php', 'app/Support/TheoryBuilderSelectionPresentation.php',
];
if (array_column($ledger['files'], 'path') !== $sharedPaths) { throw new RuntimeException('M77 mirror exact nine-file hunk inventory differs.'); }
$operations = []; $hunks = 0;
foreach ($ledger['files'] as $file) {
    $relative = $file['path'];
    $source = $read(M77_MIRROR_WT, $relative, $file['after_raw_sha256']);
    if (!hash_equals($file['after_lf_sha256'], hash('sha256', $lf($source)))) { throw new RuntimeException('M77 WT normalized hunk source changed.'); }
    $original = $read(M77_MIRROR_ROOT, $relative);
    if ($relative === 'app/Http/Controllers/TestJsV2Controller.php') {
        if (!hash_equals(M77_MIRROR_CONTROLLER_BEFORE, hash('sha256', $original))) { throw new RuntimeException('M77 foreign ROOT controller BEFORE changed.'); }
    } elseif (!hash_equals($file['base_lf_sha256'], hash('sha256', $lf($original)))) {
        throw new RuntimeException('M77 ROOT accepted M76 base changed: '.$relative);
    }
    if (isset($before['shared_source_hashes'][$relative]['root'])
        && !hash_equals($before['shared_source_hashes'][$relative]['root'], hash('sha256', $original))) {
        throw new RuntimeException('M77 independent ROOT raw shared BEFORE changed: '.$relative);
    }
    $ending = $eol($original); $after = $original; $actualHunks = [];
    if ($ending === 'MIXED' && !isset($before['shared_source_hashes'][$relative]['root'])) {
        throw new RuntimeException('M77 mixed EOL requires the exact independent raw ROOT pin.');
    }
    foreach ($file['hunks'] as $hunk) {
        if (str_contains($hunk['before'], "\r") || str_contains($hunk['after'], "\r")) { throw new RuntimeException('M77 fixture hunks must remain LF.'); }
        $matches = 0; $actual = null;
        foreach (["\n" => 'LF', "\r\n" => 'CRLF'] as $localEnding => $label) {
            $oldFragment = str_replace("\n", $localEnding, $hunk['before']);
            $count = preg_match_all('~^'.preg_quote($oldFragment, '~').'~m', $after);
            if ($count === false) { throw new RuntimeException('M77 anchored local EOL matching failed.'); }
            $matches += $count;
            if ($count === 1) {
                $actual = ['before' => $oldFragment, 'after' => str_replace("\n", $localEnding, $hunk['after']), 'eol' => $label];
            }
        }
        if ($matches !== 1 || $actual === null) { throw new RuntimeException('M77 exact old hunk must match once across LF/CRLF variants.'); }
        $after = $replace($after, $actual['before'], $actual['after']);
        $actualHunks[] = $actual;
        ++$hunks;
    }
    $restored = $after;
    foreach (array_reverse($actualHunks) as $actual) {
        $restored = $replace($restored, $actual['after'], $actual['before']);
    }
    if ($restored !== $original || $eol($after) !== $ending
        || ($relative !== 'app/Http/Controllers/TestJsV2Controller.php' && !hash_equals($file['after_lf_sha256'], hash('sha256', $lf($after))))) {
        throw new RuntimeException('M77 exact raw reversal/EOL/owned hunk isolation failed: '.$relative);
    }
    $operations[$relative] = ['kind' => 'owned-hunks', 'source' => $source, 'before' => $original, 'after' => $after,
        'eol' => $ending, 'eol_counts_before' => $eolCounts($original), 'eol_counts_after' => $eolCounts($after),
        'hunk_eol' => array_column($actualHunks, 'eol'), 'hunks' => count($file['hunks'])];
}
if ($hunks !== 13) { throw new RuntimeException('M77 mirror needs exactly thirteen owned hunks.'); }

$artifactPaths = [
    'docs/content/m77-before-source.v1.json', 'database/content-patches/m77-one-prepositions-phrasal.v1.json',
    'docs/content/m77-one-prepositions-phrasal.v1.json', 'docs/content/theory-sentence-builders/m77-one-prepositions-phrasal.v1.json',
    'docs/content/m77-one-prepositions-phrasal.components.v1.json', 'docs/content/m77-one-prepositions-phrasal.tasks.v1.json',
    'docs/content/m77-one-prepositions-phrasal.editorial.v1.json', 'docs/content/m77-sentence-builder-bank-audit.v1.json',
    'docs/content/m77-one-prepositions-phrasal.v1.0.0.uk.md', 'docs/content/m77-native-bindings.v1.json',
];
$authorPaths = array_map(static fn (string $name): string => 'docs/content/m77-author-parts/'.$name.'.v1.json', ['one', 'time', 'place', 'movement', 'dependent', 'phrasal']);
$definitionPaths = [
    'database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesOneOnesTheorySeeder/definition.json',
    'database/seeders/Page_V3/PrepositionsAndPhrasalVerbs/PrepositionsOfTimeTheorySeeder/definition.json',
    'database/seeders/Page_V3/PrepositionsAndPhrasalVerbs/PrepositionsOfPlaceTheorySeeder/definition.json',
    'database/seeders/Page_V3/PrepositionsAndPhrasalVerbs/PrepositionsOfMovementTheorySeeder/definition.json',
    'database/seeders/Page_V3/PrepositionsAndPhrasalVerbs/DependentPrepositionsTheorySeeder/definition.json',
    'database/seeders/Page_V3/PrepositionsAndPhrasalVerbs/PhrasalVerbsBasicsTheorySeeder/definition.json',
];
if (array_column($checks['artifacts'] ?? [], 'path') !== $artifactPaths
    || array_column($checks['author_inputs'] ?? [], 'path') !== $authorPaths
    || array_column($checks['active_definitions'] ?? [], 'path') !== $definitionPaths) {
    throw new RuntimeException('M77 frozen transfer manifest has a foreign/missing/reordered path.');
}
$copies = [...$artifactPaths, $checksPath, ...$authorPaths,
    'docs/content/m77-author-parts/one-time-bank-review.v1.json',
    'docs/content/m77-author-parts/place-movement-bank-review.v1.json',
    'docs/content/m77-author-parts/dependent-phrasal-bank-review.v1.json',
    'app/Support/M77OnePrepositionsPhrasal.php', 'app/Support/M77BuilderSelectionPresentation.php',
    'app/Services/M77ContentPatch.php', 'app/Services/M77LocalTargetGuard.php', 'app/Console/Commands/ContentPatchM77.php',
    'tests/Feature/M77BuilderSelectionPresentationTest.php', 'tests/Feature/M77ContentPatchTest.php',
    'tests/Feature/M77OnePrepositionsPhrasalContentTest.php', 'tests/Feature/M77OnePrepositionsPhrasalRuntimeTest.php',
    'tests/Feature/M77OwnedSharedSourceDeltaTest.php', 'tests/Feature/M77PracticeEngineTest.php',
    'tests/Browser/m77-practice-contract.cjs', 'tests/Fixtures/m77-owned-source-hunks.json',
    'tools/diagnostics/m77-bank-audit-projection.php', 'tools/diagnostics/m77-before-registry.php',
    'tools/diagnostics/m77-formula-preview.php', 'tools/diagnostics/m77-freeze-author.php',
    'tools/diagnostics/m77-navigation-audit.php', 'tools/diagnostics/m77-old-consumers.php',
    'tools/diagnostics/m77-postconditions.php', 'tools/diagnostics/m77-project-author.php',
    'tools/diagnostics/m77-prospective-metadata.php', 'tools/diagnostics/m77-review-plan.php',
    'tools/diagnostics/m77-runtime-proof.php', 'tools/diagnostics/m77-source-sync.php',
    'tools/diagnostics/m77-working-local.php', 'tools/diagnostics/m77-before-freshness.php',
    'tools/diagnostics/m77-mirror-owned.php',
];
$frozenPins = [];
foreach ([...$checks['artifacts'], ...$checks['author_inputs'], ...$checks['active_definitions']] as $entry) {
    $bytes = $read(M77_MIRROR_WT, $entry['path'], $entry['sha256']);
    if (strlen($bytes) !== $entry['bytes']) { throw new RuntimeException('M77 frozen source length differs.'); }
    $frozenPins[$entry['path']] = $entry['sha256'];
}
$frozenPins[$checksPath] = M77_MIRROR_CHECKSUMS_SHA;
foreach ($copies as $relative) {
    if (isset($operations[$relative])) { throw new RuntimeException('M77 duplicate transfer path.'); }
    $bytes = $read(M77_MIRROR_WT, $relative, $frozenPins[$relative] ?? null);
    $destination = $path(M77_MIRROR_ROOT, $relative, false);
    $existing = is_file($destination) ? file_get_contents($destination) : null;
    if (file_exists($destination) && !is_file($destination)) { throw new RuntimeException('M77 new-file destination is not an ordinary file.'); }
    if ($existing !== null && $existing !== $bytes) { throw new RuntimeException('M77 refuses to overwrite a differing new-file path: '.$relative); }
    $operations[$relative] = ['kind' => $existing === null ? 'new-copy' : 'already-identical', 'source' => $bytes, 'before' => $existing, 'after' => $bytes];
}
$sourceBefore = $decode($read(M77_MIRROR_WT, $artifactPaths[0], $frozenPins[$artifactPaths[0]]));
if (count($before['definitions'] ?? []) !== 6 || count($sourceBefore['targets'] ?? []) !== 6) { throw new RuntimeException('M77 exact six source BEFORE bindings differ.'); }
foreach ($definitionPaths as $i => $relative) {
    $target = $sourceBefore['targets'][$i]; $captured = $before['definitions'][$target['identity']] ?? null;
    if (!is_array($captured) || ($captured['path'] ?? null) !== $relative || ($target['path'] ?? null) !== $relative
        || ($captured['definition'] ?? null) !== $target['before'] || !is_string($captured['bytes'] ?? null)
        || !hash_equals($captured['sha256'], hash('sha256', $captured['bytes']))) {
        throw new RuntimeException('M77 definition identity/full raw independent BEFORE differs.');
    }
    $original = $read(M77_MIRROR_ROOT, $relative, $captured['sha256']);
    if ($original !== $captured['bytes']) { throw new RuntimeException('M77 ROOT definition raw BEFORE differs.'); }
    $bytes = $read(M77_MIRROR_WT, $relative, $frozenPins[$relative]);
    $operations[$relative] = ['kind' => 'frozen-definition', 'source' => $bytes, 'before' => $original, 'after' => $bytes];
}
// Install every new dependency before changing the six source definitions;
// enable shared registrations last, so a served request cannot reach a missing
// M77 package/selection class during this explicit filesystem-only transfer.
$ordered = [];
foreach ([...$copies, ...$definitionPaths, ...$sharedPaths] as $relative) {
    if (isset($ordered[$relative]) || !isset($operations[$relative])) { throw new RuntimeException('M77 dependency-first transfer order differs.'); }
    $ordered[$relative] = $operations[$relative];
}
if (count($ordered) !== count($operations)) { throw new RuntimeException('M77 dependency-first transfer omitted a path.'); }
$operations = $ordered;
$assertProtected = static function () use ($before, $read): void {
    foreach (['visual_hashes', 'frozen_hashes'] as $group) {
        foreach ($before[$group] as $relative => $sha) { $read(M77_MIRROR_ROOT, $relative, $sha); }
    }
};
foreach (array_keys($operations) as $relative) {
    if (isset($before['visual_hashes'][$relative]) || isset($before['frozen_hashes'][$relative])
        || str_contains($relative, '.env') || str_contains($relative, 'reports/') || $relative === '.gitattributes') {
        throw new RuntimeException('M77 mirror transfer intersects an excluded/protected path.');
    }
}
$assertProtected();
$receiptRelative = 'storage/app/seo-m77-local/mechanical-sync-v1.json';
$receiptPath = $path(M77_MIRROR_ROOT, $receiptRelative, false);
if (file_exists($receiptPath) || is_link($receiptPath)) { throw new RuntimeException('M77 mirror refuses an existing exclusive receipt.'); }
$summary = static function (array $op): array {
    return ['kind' => $op['kind'], 'before_sha256' => $op['before'] === null ? null : hash('sha256', $op['before']),
        'after_sha256' => hash('sha256', $op['after']), 'before_bytes' => $op['before'] === null ? null : strlen($op['before']),
        'after_bytes' => strlen($op['after'])] + array_intersect_key($op, array_flip(['eol', 'eol_counts_before', 'eol_counts_after', 'hunk_eol', 'hunks']));
};
$receipt = ['schema_version' => 1, 'status' => $applying ? 'pending' : 'preview', 'independent_before_sha256' => M77_MIRROR_BEFORE_SHA,
    'hunk_ledger_sha256' => M77_MIRROR_LEDGER_SHA, 'frozen_checksums_sha256' => M77_MIRROR_CHECKSUMS_SHA,
    'source_worktree' => M77_MIRROR_WT, 'served_root' => M77_MIRROR_ROOT, 'operations' => array_map($summary, $operations),
    'owned_hunks' => $hunks, 'visual_files_unchanged' => 326, 'frozen_files_unchanged' => 2662,
    'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'git_operations' => 0];
if (!$applying) {
    echo json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), PHP_EOL;
    exit(0);
}
// Full preflight above precedes every filesystem mutation, including receipt creation.
foreach ($operations as $relative => $op) {
    if ($read(M77_MIRROR_WT, $relative) !== $op['source']) { throw new RuntimeException('M77 WT source changed after complete preflight.'); }
    $destination = $path(M77_MIRROR_ROOT, $relative, false);
    if ($op['before'] === null ? file_exists($destination) : (!is_file($destination) || file_get_contents($destination) !== $op['before'])) {
        throw new RuntimeException('M77 ROOT changed after complete preflight: '.$relative);
    }
}
$handle = fopen($receiptPath, 'xb');
if ($handle === false) { throw new RuntimeException('M77 exclusive receipt cannot be created.'); }
$written = [];
try {
    foreach ($operations as $relative => $op) {
        if ($op['kind'] === 'already-identical') { continue; }
        $destination = $path(M77_MIRROR_ROOT, $relative, false);
        if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0700, true)) { throw new RuntimeException('M77 destination directory unavailable.'); }
        $path(M77_MIRROR_ROOT, $relative, false);
        $out = fopen($destination, $op['before'] === null ? 'x+b' : 'r+b');
        if ($out === false) { throw new RuntimeException('M77 destination cannot be opened: '.$relative); }
        try {
            if (!flock($out, LOCK_EX)) { throw new RuntimeException('M77 destination lock failed.'); }
            $current = stream_get_contents($out);
            if ($current !== ($op['before'] ?? '')) { throw new RuntimeException('M77 destination changed before locked write.'); }
            rewind($out);
            if (!ftruncate($out, 0)) { throw new RuntimeException('M77 exact destination write cannot start.'); }
            $offset = 0;
            while ($offset < strlen($op['after'])) {
                $count = fwrite($out, substr($op['after'], $offset));
                if (!is_int($count) || $count < 1) { throw new RuntimeException('M77 exact destination write was incomplete.'); }
                $offset += $count;
            }
            if (!fflush($out)) { throw new RuntimeException('M77 destination flush failed.'); }
        } finally { fclose($out); }
        if ($read(M77_MIRROR_ROOT, $relative) !== $op['after']) { throw new RuntimeException('M77 exact after-write bytes differ.'); }
        $written[] = $relative;
    }
    $assertProtected();
    foreach ($operations as $relative => $op) {
        if ($read(M77_MIRROR_ROOT, $relative) !== $op['after'] || $read(M77_MIRROR_WT, $relative) !== $op['source']) {
            throw new RuntimeException('M77 final exact ROOT/WT state changed.');
        }
    }
    $receipt['status'] = 'mirrored';
} catch (Throwable $error) {
    // Filesystem transfer is not a database transaction. Never conceal a partial
    // copy or attempt to restore unrelated files automatically.
    $receipt['status'] = 'incomplete';
    $receipt['failure'] = $error->getMessage();
} finally {
    $receipt['written_paths'] = $written;
    $json = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) { fclose($handle); throw new RuntimeException('M77 exclusive mechanical receipt write failed.'); }
    fclose($handle);
}
echo json_encode(['status' => $receipt['status'], 'operations' => count($operations), 'written' => count($written),
    'owned_hunks' => $hunks, 'receipt' => basename($receiptPath), 'receipt_sha256' => hash_file('sha256', $receiptPath),
    'db_writes' => 0, 'http_requests' => 0], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), PHP_EOL;
exit($receipt['status'] === 'mirrored' ? 0 : 1);
