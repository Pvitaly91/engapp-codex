<?php

// Pure comparison of exclusively captured SELECT evidence: no app bootstrap or connection.
function verifyM42DesignEvidence(array $before, array $after, array $identities): array
{
    $assert = static function (mixed $actual, mixed $expected, string $message): void {
        if ($actual !== $expected) { throw new RuntimeException($message); }
    };
    $assert(count($identities), 42, 'Exactly 42 finite expected identities required.');
    $assert(count(array_unique($identities)), 42, '42 unique expected identities required.');
    foreach ([$before, $after] as $record) {
        $assert($record['target']['root'] ?? null, 'D:/DEV/htdocs/gramlyze.loc', 'Wrong actual application root.');
        $assert($record['target']['document_root'] ?? null, 'D:/DEV/htdocs/gramlyze.loc/public', 'Wrong document root.');
        $assert($record['target']['driver'] ?? null, 'mysql', 'Wrong actual DB driver.');
        $assert($record['target']['database'] ?? null, 'gr2', 'Wrong actual DB name.');
        $assert($record['target']['port'] ?? null, 3306, 'Wrong actual DB port.');
        $assert($record['target']['site_mode'] ?? null, 'development', 'Expected .loc development SiteMode.');
        if (!in_array($record['target']['host'] ?? null, ['localhost', '127.0.0.1'], true)) { throw new RuntimeException('Wrong local DB host.'); }
        $assert(array_column($record['targets'] ?? [], 'identity'), $identities, 'All42 accepted finite owners required in exact order.');
        $assert(count($record['m41_references'] ?? []), 3, 'M41 references must remain separate from42.');
        $assert($record['read_only_select_guard'] ?? null, true, 'SELECT-only SQL guard required.');
        if (count($record['fingerprints'] ?? []) < 46) { throw new RuntimeException('Full physical schema fingerprint inventory required.'); }
        foreach ([...$record['targets'], ...$record['m41_references']] as $owner) {
            $assert($owner['exact_accepted_source'] ?? null, true, 'Exact latest accepted source/working DB required.');
            if (!in_array('uk', $owner['locales'] ?? [], true) || ($owner['blocks'] ?? []) === []) { throw new RuntimeException('All-target source/locales evidence required.'); }
        }
    }
    $old = $before; $new = $after;
    unset($old['at'], $new['at']);
    $assert($new, $old, 'M42 presentation-only evidence changed: all rows/tables, content, locale, keys, banks, UUIDs and progress must be exact.');
    return ['pass' => true, 'updated' => 0, 'inserted' => 0, 'deleted' => 0, 'targets' => 42, 'm41_references' => 3,
        'protected_raw_tables' => count($after['fingerprints']), 'text_blocks' => $after['fingerprints']['text_blocks']['count'],
        'target_rows_all_locales' => array_map(static fn ($owner) => count($owner['blocks']), $after['targets']),
        'all_banks_and_progress_unchanged' => true, 'all_source_bodies_exact' => true,
        'basis' => 'Fresh full46-table SELECT-only before/after equality after all live requests; no allowed DB delta.'];
}

if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    [$beforeName, $afterName, $resultName] = array_pad(array_slice($argv, 1), 3, null);
    if (!is_string($beforeName) || !preg_match('/^m42-design-before-v[1-9][0-9]*\.json$/D', $beforeName)
        || !is_string($afterName) || !preg_match('/^m42-design-after(?:-final)?-v[1-9][0-9]*\.json$/D', $afterName)
        || ($resultName !== null && !preg_match('/^db-equality-v[1-9][0-9]*\.json$/D', $resultName))) {
        throw new RuntimeException('Use exact exclusive private M42 before/after basenames and optional result basename.');
    }
    $dir = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local';
    $registry = json_decode(file_get_contents(dirname(__DIR__, 2).'/database/content-patches/m42-native-design-registry.v1.json'), true, flags: JSON_THROW_ON_ERROR);
    $read = static fn ($name) => json_decode(file_get_contents($dir.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
    $result = verifyM42DesignEvidence($read($beforeName), $read($afterName), array_column($registry['targets'], 'identity'));
    $result['before_sha256'] = hash_file('sha256', $dir.'/'.$beforeName);
    $result['after_sha256'] = hash_file('sha256', $dir.'/'.$afterName);
    $bytes = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    if ($resultName !== null) {
        $file = fopen($dir.'/'.$resultName, 'xb');
        if (!$file) { throw new RuntimeException('Exclusive M42 equality evidence already exists.'); }
        try { if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('Incomplete equality evidence.'); } }
        finally { fclose($file); }
    }
    echo $bytes;
}
