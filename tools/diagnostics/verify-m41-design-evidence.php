<?php

// Compare exclusively saved SELECT-only design evidence. No application bootstrap or DB connection.
function verifyM41DesignEvidence(array $before, array $after): array
{
    $assert = static function (mixed $actual, mixed $expected, string $message): void {
        if ($actual !== $expected) { throw new RuntimeException($message); }
    };
    foreach ([$before, $after] as $record) {
        $assert($record['target']['root'] ?? null, 'D:/DEV/htdocs/gramlyze.loc', 'Wrong working root.');
        $assert($record['target']['driver'] ?? null, 'mysql', 'Wrong local driver.');
        $assert($record['target']['database'] ?? null, 'gr2', 'Wrong local database.');
        $assert((int) ($record['target']['port'] ?? 0), 3306, 'Wrong local port.');
        if (!in_array($record['target']['host'] ?? null, ['localhost', '127.0.0.1'], true)) {
            throw new RuntimeException('Wrong local host.');
        }
        $identities = array_map(static fn ($name) => 'Database\\Seeders\\Page_V3\\Tenses\\'.$name,
            ['TensesPastSimpleVsPastContinuousTheorySeeder', 'TensesPresentSimpleVsPresentContinuousTheorySeeder', 'TensesPresentPerfectVsPastSimpleTheorySeeder']);
        $assert(array_column($record['targets'] ?? [], 'identity'), $identities, 'All three exact M41 owners required in author order.');
        $assert(count($record['regressions'] ?? []), 47, 'All 47 accepted prior owners required.');
        $assert(count($record['fingerprints'] ?? []), 24, 'All raw protected table fingerprints required.');
        foreach ($record['source_fidelity'] ?? [] as $state) {
            $assert($state['state'] ?? null, 'author_after', 'Accepted M41 author-after is required.');
            $assert($state['source_db_exact'] ?? null, true, 'Exact accepted source/DB bodies required.');
        }
        $assert(array_keys($record['source_fidelity'] ?? []), $identities, 'Exact source evidence for all owners required.');
        $assert(array_values(array_column($record['linked_banks'] ?? [], 'question_count')), [72, 72, 24], 'Own bank scopes differ.');
    }
    $old = $before; $new = $after;
    unset($old['at'], $new['at']);
    $assert($new, $old, 'Design-only evidence changed: all rows, raw table hashes, banks, progress, UUIDs and metadata must remain exact.');
    return ['pass' => true, 'updated' => 0, 'inserted' => 0, 'deleted' => 0,
        'protected_raw_tables' => count($after['fingerprints']), 'prior_owners_unchanged' => count($after['regressions']),
        'target_rows_all_locales' => array_map(static fn ($t) => count($t['blocks']), $after['targets']),
        'text_blocks' => $after['fingerprints']['text_blocks']['count'],
        'source_bodies_exact' => true, 'banks' => [72, 72, 24], 'progress_unchanged' => true,
        'basis' => 'Fresh before/after SELECT: all 24 full raw table fingerprints plus exact target/protected evidence; no allowed DB delta.'];
}

if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    $dir = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-design-local';
    [$beforeName, $afterName, $resultName] = array_pad(array_slice($argv, 1), 3, null);
    if (!is_string($beforeName) || !preg_match('/^m41-design-before-v[1-9][0-9]*\.json$/D', $beforeName)
        || !is_string($afterName) || !preg_match('/^m41-design-after(?:-final)?-v[1-9][0-9]*\.json$/D', $afterName)
        || ($resultName !== null && !preg_match('/^db-equality-v[1-9][0-9]*\.json$/D', $resultName))) {
        throw new RuntimeException('Use exact private before/after evidence basenames and optional exclusive db-equality result.');
    }
    $read = static fn ($name) => json_decode(file_get_contents($dir.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
    $result = verifyM41DesignEvidence($read($beforeName), $read($afterName));
    $result['before_sha256'] = hash_file('sha256', $dir.'/'.$beforeName);
    $result['after_sha256'] = hash_file('sha256', $dir.'/'.$afterName);
    $bytes = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    if ($resultName !== null) {
        $file = fopen($dir.'/'.$resultName, 'xb');
        if (!$file) { throw new RuntimeException('Exclusive design equality evidence already exists.'); }
        try {
            if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('Incomplete equality evidence.'); }
        } finally { fclose($file); }
    }
    echo $bytes;
}
