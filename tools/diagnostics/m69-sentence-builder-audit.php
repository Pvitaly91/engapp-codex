<?php

/** Actual M69/full-registry read-only gate; preserve every accepted owner record, not a fixed registry cap. */
require __DIR__.'/frozen-readonly-profile.php';

$registryCheck = <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if ($argv[1] === 'registry') {
    $path = $root.'/storage/app/seo-m69-local/registry-before-sentence-builders-v1.json';
    if (!is_file($path) || is_link($path)
        || !hash_equals('90cccfbf5509c2392921e82d0b24225268bb9c9c18712462491eefcd83d9f0cd', hash_file('sha256', $path))) {
        throw new RuntimeException('M69 independent accepted-registry BEFORE is unavailable or changed.');
    }
    $before = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    $byOwner = static function (array $rows): array {
        $result = [];
        foreach ($rows as $row) {
            $owner = $row['identity'] ?? null;
            if (!is_string($owner) || $owner === '' || isset($result[$owner])) {
                throw new RuntimeException('M69 actual registry owner is missing or duplicated.');
            }
            $result[$owner] = $row;
        }
        ksort($result); return $result;
    };
    $old = $byOwner($before['rows'] ?? []); $after = $byOwner($data['rows'] ?? []);
    if (($before['failed'] ?? null) !== 0 || ($before['writes'] ?? null) !== 0
        || ($before['targets'] ?? null) !== count($old) || count($old) < 127
        || ($data['targets'] ?? null) !== count($after)) {
        throw new RuntimeException('M69 actual registry count/independent accepted baseline differs.');
    }
    $transport = [];
    foreach ($old as $owner => $row) {
        $current = $after[$owner] ?? null;
        if (!is_array($current) || preg_match('/^[a-f0-9]{64}$/D', $row['http_sha256'] ?? '') !== 1
            || preg_match('/^[a-f0-9]{64}$/D', $current['http_sha256'] ?? '') !== 1) {
            throw new RuntimeException('M69 accepted registry transport evidence is missing or malformed.');
        }
        $transport[$owner] = ['before_sha256'=>$row['http_sha256'], 'after_sha256'=>$current['http_sha256'],
            'raw_equal'=>$row['http_sha256'] === $current['http_sha256']];
        unset($row['http_sha256'], $current['http_sha256']);
        if ($current !== $row) {
            throw new RuntimeException('M69 accepted registry record disappeared or changed: '.$owner);
        }
    }
    $newOwners = array_values(array_diff(array_keys($after), array_keys($old)));
    $expected = App\Support\M69AdjectivesAdverbs::OWNERS; sort($expected); sort($newOwners);
    if ($newOwners !== $expected || count($after) !== count($old) + count($expected)) {
        throw new RuntimeException('M69 registry gained an owner outside the exact three approved pages.');
    }
    $data['independent_registry_before_sha256'] = '90cccfbf5509c2392921e82d0b24225268bb9c9c18712462491eefcd83d9f0cd';
    $data['old_registry_records_preserved'] = count($old);
    $data['new_registry_owners'] = $newOwners;
    $data['old_registry_transport'] = $transport;
    $data['registry_comparison_policy'] = 'All old owner-keyed structural facts exact; full HTTP HTML hashes retained separately, not claimed byte-identical. Independent old-consumers learning DOM/SQL remains exact.';
}
PHP;

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M69', 7], ['m52', 'm69', 5],
    ['App\\Services\\M69LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m69-passive-basics.v1.json', 'm69-adjectives-adverbs.v1.json', 1],
    ["$"."data = json_decode($"."output, true, flags: JSON_THROW_ON_ERROR);", $registryCheck, 1],
]);
