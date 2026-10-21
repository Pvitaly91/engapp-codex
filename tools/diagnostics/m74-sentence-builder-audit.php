<?php

/** Actual M74/full-registry read-only gate; preserve the independently captured 142 old owners. */
require __DIR__.'/frozen-readonly-profile.php';

$registryCheck = <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if ($argv[1] === 'registry') {
    $path = $root.'/storage/app/seo-m74-local/registry-before-sentence-builders-v1.json';
    if (!is_file($path) || is_link($path)
        || !hash_equals('e5be2989a1a49e6b45c1ba0d21a8e9a6e2857739f35f706a2021c0eb8bdcef9e', hash_file('sha256', $path))) {
        throw new RuntimeException('M74 independent accepted-registry BEFORE is unavailable or changed.');
    }
    $before = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    $byOwner = static function (array $rows): array {
        $result = [];
        foreach ($rows as $row) {
            $owner = $row['identity'] ?? null;
            if (!is_string($owner) || $owner === '' || isset($result[$owner])) {
                throw new RuntimeException('M74 actual registry owner is missing or duplicated.');
            }
            $result[$owner] = $row;
        }
        ksort($result); return $result;
    };
    $old = $byOwner($before['rows'] ?? []); $after = $byOwner($data['rows'] ?? []);
    if (($before['failed'] ?? null) !== 0 || ($before['writes'] ?? null) !== 0
        || ($before['targets'] ?? null) !== count($old)
        || ($data['targets'] ?? null) !== count($after) || ($data['runtime'] ?? null) !== ($before['runtime'] ?? null)) {
        throw new RuntimeException('M74 actual registry count/independent accepted baseline differs.');
    }
    $transport = [];
    foreach ($old as $owner => $row) {
        $current = $after[$owner] ?? null;
        if (!is_array($current) || preg_match('/^[a-f0-9]{64}$/D', $row['http_sha256'] ?? '') !== 1
            || preg_match('/^[a-f0-9]{64}$/D', $current['http_sha256'] ?? '') !== 1) {
            throw new RuntimeException('M74 accepted registry transport evidence is missing or malformed.');
        }
        $transport[$owner] = ['before_sha256' => $row['http_sha256'], 'after_sha256' => $current['http_sha256'],
            'raw_equal' => $row['http_sha256'] === $current['http_sha256']];
        unset($row['http_sha256'], $current['http_sha256']);
        if ($current !== $row) {
            throw new RuntimeException('M74 accepted registry record disappeared or changed: '.$owner);
        }
    }
    $newOwners = array_values(array_diff(array_keys($after), array_keys($old)));
    $expected = App\Support\M74OtherPronounsBasics::OWNERS; sort($expected); sort($newOwners);
    if ($newOwners !== $expected || count($after) !== count($old) + count($expected)) {
        throw new RuntimeException('M74 registry gained an owner outside the exact three approved pages.');
    }
    $data['independent_registry_before_sha256'] = 'e5be2989a1a49e6b45c1ba0d21a8e9a6e2857739f35f706a2021c0eb8bdcef9e';
    $data['old_registry_records_preserved'] = count($old);
    $data['new_registry_owners'] = $newOwners;
    $data['old_registry_transport'] = $transport;
    $data['registry_comparison_policy'] = 'All 142 old owner-keyed structural records exact; full HTTP HTML hashes retained separately, not claimed byte-identical. The current total is the captured old count plus three exact M74 owners, not a permanent global registry cap.';
}
$audit = App\Support\M74BuilderSelectionPresentation::loadAudit($root);
[$packageBefore,$patch,$master,$contract] = App\Support\M74OtherPronounsBasics::load($root);
App\Support\M74OtherPronounsBasics::validate($packageBefore,$patch,$master,$contract);
if (array_column($contract['targets'],'level') !== ['B1',null,null]
    || ($audit['review_summary']['technical_filter'] ?? null) !== ['B1',null,null]) {
    throw new RuntimeException('M74 exact declared B1/NULL/NULL bank filters differ.');
}
$m74Rows = [];
foreach ($data['rows'] ?? [] as $row) {
    if (in_array($row['identity'] ?? null, App\Support\M74OtherPronounsBasics::OWNERS, true)) {
        if (isset($m74Rows[$row['identity']])) { throw new RuntimeException('Duplicate M74 target owner.'); }
        $m74Rows[$row['identity']] = $row;
    }
}
if (count($m74Rows) !== 3) { throw new RuntimeException('Both audit scopes require the exact three M74 owners.'); }
foreach ($audit['targets'] as $index => $target) {
    $current = $m74Rows[$target['identity']] ?? [];
    $ids = [[32707,32708,32711,32712,32714],[33123,33126,33128,33131,33133],[51184,51185,51187,51188,51189]][$index];
    if (($target['expected_selected_ids'] ?? null) !== $ids
        || ($target['level'] ?? null) !== ['B1',null,null][$index]
        || ($current['page_id'] ?? null) !== App\Support\M74OtherPronounsBasics::PAGE_IDS[$index]
        || ($current['effective_config']['seeder_classes'] ?? null) !== $target['bank_seeders']
        || ($current['selected_ids'] ?? null) !== $target['expected_selected_ids']
        || ($current['eligible_count'] ?? null) !== array_sum(array_map('count', $target['expected_pool_ids_by_bank']))
        || ($current['selected_count'] ?? null) !== 5 || ($current['selected_types'] ?? null) !== array_fill(0, 5, '4')
        || ($current['widget_count'] ?? null) !== 1 || ($current['practice_widget_count'] ?? null) !== 1) {
        throw new RuntimeException('M74 target differs from the independent finite thematic bank contract.');
    }
}
$data['m74_declared_technical_filters'] = ['B1',null,null];
$data['m74_exact_selected_ids_checked'] = 15;
PHP;

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M74', 7], ['m52', 'm74', 5],
    ["$"."app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();",
        "$"."app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();\n".
        "set_exception_handler(static function (Throwable $"."error): void {\n".
        "    fwrite(STDERR, $"."error instanceof RuntimeException && !$"."error instanceof PDOException\n".
        "        ? 'M74 sentence-builder audit: '.$"."error->getMessage().\"\\n\" : \"M74 sentence-builder audit incomplete; no receipt. No writes.\\n\");\n".
        "    exit(1);\n".
        "});", 1],
    ['App\\Services\\M74LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m74-passive-basics.v1.json', 'm74-other-pronouns-basics.v1.json', 1],
    ["$"."data = json_decode($"."output, true, flags: JSON_THROW_ON_ERROR);", $registryCheck, 1],
]);
