<?php

/** Actual M72/full-registry read-only gate; preserve the independently captured 136 old owners. */
require __DIR__.'/frozen-readonly-profile.php';

$registryCheck = <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if ($argv[1] === 'registry') {
    $path = $root.'/storage/app/seo-m72-local/registry-before-sentence-builders-v1.json';
    if (!is_file($path) || is_link($path)
        || !hash_equals('84de37c00b4c5a75a042be92d82327eaa73f3acb6bb628aac7baec6c3adc7a55', hash_file('sha256', $path))) {
        throw new RuntimeException('M72 independent accepted-registry BEFORE is unavailable or changed.');
    }
    $before = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    $byOwner = static function (array $rows): array {
        $result = [];
        foreach ($rows as $row) {
            $owner = $row['identity'] ?? null;
            if (!is_string($owner) || $owner === '' || isset($result[$owner])) {
                throw new RuntimeException('M72 actual registry owner is missing or duplicated.');
            }
            $result[$owner] = $row;
        }
        ksort($result); return $result;
    };
    $old = $byOwner($before['rows'] ?? []); $after = $byOwner($data['rows'] ?? []);
    if (($before['failed'] ?? null) !== 0 || ($before['writes'] ?? null) !== 0
        || ($before['targets'] ?? null) !== count($old) || count($old) !== 136
        || ($data['targets'] ?? null) !== count($after)) {
        throw new RuntimeException('M72 actual registry count/independent accepted baseline differs.');
    }
    $transport = [];
    foreach ($old as $owner => $row) {
        $current = $after[$owner] ?? null;
        if (!is_array($current) || preg_match('/^[a-f0-9]{64}$/D', $row['http_sha256'] ?? '') !== 1
            || preg_match('/^[a-f0-9]{64}$/D', $current['http_sha256'] ?? '') !== 1) {
            throw new RuntimeException('M72 accepted registry transport evidence is missing or malformed.');
        }
        $transport[$owner] = ['before_sha256' => $row['http_sha256'], 'after_sha256' => $current['http_sha256'],
            'raw_equal' => $row['http_sha256'] === $current['http_sha256']];
        unset($row['http_sha256'], $current['http_sha256']);
        if ($current !== $row) {
            throw new RuntimeException('M72 accepted registry record disappeared or changed: '.$owner);
        }
    }
    $newOwners = array_values(array_diff(array_keys($after), array_keys($old)));
    $expected = App\Support\M72PartitivesNegativesGeography::OWNERS; sort($expected); sort($newOwners);
    if ($newOwners !== $expected || count($after) !== count($old) + count($expected)) {
        throw new RuntimeException('M72 registry gained an owner outside the exact three approved pages.');
    }
    $audit = App\Support\M72BuilderSelectionPresentation::loadAudit($root);
    foreach ($audit['targets'] as $target) {
        $current = $after[$target['identity']];
        if (($current['selected_ids'] ?? null) !== $target['expected_selected_ids']
            || ($current['eligible_count'] ?? null) !== count($target['expected_pool_ids_by_bank'][$target['bank_seeders'][0]])
            || ($current['selected_count'] ?? null) !== 5 || ($current['selected_types'] ?? null) !== array_fill(0, 5, '4')
            || ($current['widget_count'] ?? null) !== 1 || ($current['practice_widget_count'] ?? null) !== 1) {
            throw new RuntimeException('M72 new registry owner differs from the independent finite thematic bank contract.');
        }
    }
    $data['independent_registry_before_sha256'] = '84de37c00b4c5a75a042be92d82327eaa73f3acb6bb628aac7baec6c3adc7a55';
    $data['old_registry_records_preserved'] = count($old);
    $data['new_registry_owners'] = $newOwners;
    $data['old_registry_transport'] = $transport;
    $data['registry_comparison_policy'] = 'All 136 old owner-keyed structural records exact; full HTTP HTML hashes retained separately, not claimed byte-identical. The current total is the captured old count plus three exact M72 owners, not a permanent global registry cap.';
}
PHP;

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M72', 7], ['m52', 'm72', 5],
    ['App\\Services\\M72LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m72-passive-basics.v1.json', 'm72-partitives-negatives-geography.v1.json', 1],
    ["$"."data = json_decode($"."output, true, flags: JSON_THROW_ON_ERROR);", $registryCheck, 1],
]);
