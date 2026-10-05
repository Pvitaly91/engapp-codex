<?php

declare(strict_types=1);

/** Finite presentation-only packages. Preview by default; no bootstrap or DB. */
require_once __DIR__.'/lib/ppc_quality_builder.php';
if (is_file(__DIR__.'/lib/ppc_quality_mixed_display.php')) {
    require_once __DIR__.'/lib/ppc_quality_mixed_display.php';
}

function ppcComposeBuilderPresentationRows(string $root, array $inventory): array
{
    $records = [];
    foreach ($inventory['questions'] as $record) {
        if ($record['bank_kind'] === 'Builder') {
            $records[$record['seeder_class']."\0".$record['editorial_uuid']] = $record;
        }
    }
    $result = [];
    foreach (ppcQualityBuilderBanks() as $bank => $levels) {
        $name = $bank === 'BasicsB2' ? 'PolyglotPastPerfectContinuousBasicsB2LessonSeeder'
            : 'PolyglotPastPerfectContinuous'.$bank.'AllLevelsLessonSeeder';
        $definition = json_decode(file_get_contents($root.'/database/seeders/V3/Polyglot/'.$name.'/definition.json'), true, flags: JSON_THROW_ON_ERROR);
        $seeder = $definition['seeder']['class'];
        $positions = [];
        foreach ($definition['questions'] as $question) {
            $index = $positions[$question['level']] ?? 0;
            $row = $levels[$question['level']][$index] ?? throw new RuntimeException('Missing authored builder display row.');
            $record = $records[$seeder."\0".$question['uuid']] ?? throw new RuntimeException('Missing persistent builder identity.');
            if ($question !== ppcQualityBuilderProjection($bank, $question, $row)) {
                throw new RuntimeException('Canonical builder definition drift: '.$question['uuid']);
            }
            $result[] = [
                'seeder_class' => $seeder,
                'editorial_uuid' => $question['uuid'],
                'persistent_uuid' => $record['persistent_uuid'],
                'locales' => ppcQualityBuilderDisplayProjection($bank, $question, $row),
            ];
            $positions[$question['level']] = $index + 1;
        }
        foreach ($levels as $level => $rows) {
            if (($positions[$level] ?? 0) !== count($rows)) {
                throw new RuntimeException('Unused builder display rows: '.$bank.'/'.$level);
            }
        }
    }
    return $result;
}

/** Validate every exact source/identity and prevent completed-target leakage. */
function ppcComposeValidatePresentationRows(array $rows, array $inventory, string $scope): array
{
    $kind = match ($scope) { 'builder' => 'Builder', 'mixed' => 'Mixed', default => throw new RuntimeException('Unknown presentation scope.') };
    $count = $scope === 'builder' ? 336 : 288;
    $records = [];
    foreach ($inventory['questions'] as $record) {
        if ($record['bank_kind'] === $kind) {
            $records[$record['seeder_class']."\0".$record['persistent_uuid']] = $record;
        }
    }
    if (count($rows) !== $count || count($records) !== $count) {
        throw new RuntimeException('Incomplete finite '.$scope.' presentation scope.');
    }
    $seen = [];
    $normalize = static fn (string $text): string => trim(mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text)));
    foreach ($rows as $row) {
        if (array_keys($row) !== ['seeder_class', 'editorial_uuid', 'persistent_uuid', 'locales']) {
            throw new RuntimeException('Unexpected presentation fields (answers/targets are forbidden).');
        }
        $key = $row['seeder_class']."\0".$row['persistent_uuid'];
        $record = $records[$key] ?? throw new RuntimeException('Out-of-scope presentation identity.');
        if (isset($seen[$key]) || $record['editorial_uuid'] !== $row['editorial_uuid'] || array_keys($row['locales']) !== ['uk', 'en', 'pl']) {
            throw new RuntimeException('Duplicate, mismatched or incomplete presentation identity.');
        }
        foreach ($row['locales'] as $locale => $localized) {
            if (array_keys($localized) !== ['expected_source', 'display_source', 'instructions']
                || ! is_string($localized['expected_source']) || $localized['expected_source'] !== $record['source_condition'][$locale]
                || ! is_string($localized['display_source']) || trim($localized['display_source']) === ''
                || ! is_string($localized['instructions'])) {
                throw new RuntimeException('Exact canonical-source guard failed: '.$row['persistent_uuid'].'/'.$locale);
            }
            $target = $normalize($record['completed_target']);
            foreach (['display_source', 'instructions'] as $field) {
                if (str_contains($normalize($localized[$field]), $target)) {
                    throw new RuntimeException('Presentation leaks completed target: '.$row['persistent_uuid'].'/'.$locale.'/'.$field);
                }
            }
        }
        $seen[$key] = true;
    }
    usort($rows, static fn (array $left, array $right): int => strcmp($left['seeder_class']."\0".$left['persistent_uuid'], $right['seeder_class']."\0".$right['persistent_uuid']));
    return $rows;
}

function ppcComposePresentationPackage(string $root, array $inventory, string $scope): array
{
    $rows = $scope === 'builder' ? ppcComposeBuilderPresentationRows($root, $inventory)
        : (function_exists('ppcQualityMixedPresentationRows') ? ppcQualityMixedPresentationRows($root, $inventory)
            : throw new RuntimeException('Mixed presentation author projection is not ready.'));
    $rows = ppcComposeValidatePresentationRows($rows, $inventory, $scope);
    $hashes = [];
    foreach ($inventory['banks'] as $bank) {
        if (strtolower($bank['kind']) !== $scope) { continue; }
        foreach (array_merge([$bank['definition_path']], $bank['external_localization_paths'] ?? []) as $path) {
            $hashes[$path] = hash('sha256', str_replace("\r\n", "\n", file_get_contents($root.'/'.$path)));
        }
    }
    $author = $scope === 'builder' ? 'scripts/lib/ppc_quality_builder.php' : 'scripts/lib/ppc_quality_mixed_display.php';
    $hashes[$author] = hash('sha256', str_replace("\r\n", "\n", file_get_contents($root.'/'.$author)));
    if ($scope === 'mixed' && function_exists('ppcQualityMixedPresentationSourcePaths')) {
        foreach (ppcQualityMixedPresentationSourcePaths() as $path) {
            $hashes[$path] = hash('sha256', str_replace("\r\n", "\n", file_get_contents($root.'/'.$path)));
        }
    }
    ksort($hashes);
    return [
        'schema_version' => 1,
        'projection' => 'ppc-compose-presentation-v1',
        'scope' => $scope,
        'counts' => ['questions' => count($rows), 'locale_rows' => count($rows) * 3],
        'source_sha256' => $hashes,
        'questions' => $rows,
    ];
}

if (defined('PPC_COMPOSE_PRESENTATION_LIBRARY_ONLY')) { return; }
$root = dirname(__DIR__);
$inventory = json_decode(file_get_contents($root.'/docs/reports/past-perfect-continuous-quality-inventory.json'), true, flags: JSON_THROW_ON_ERROR);
$scope = in_array('--scope=builder', $argv, true) ? ['builder'] : (in_array('--scope=mixed', $argv, true) ? ['mixed'] : ['builder', 'mixed']);
$write = in_array('--write', $argv, true);
$check = in_array('--check', $argv, true);
if ($write && $check) { throw new RuntimeException('Choose preview, --write or --check.'); }
$packages = [];
foreach ($scope as $name) { $packages[$name] = ppcComposePresentationPackage($root, $inventory, $name); }
$failed = false;
foreach ($packages as $name => $package) {
    $path = $root.'/database/content-patches/ppc-compose-presentation/'.$name.'.json';
    $json = json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    $changed = ! is_file($path) || str_replace("\r\n", "\n", file_get_contents($path)) !== $json;
    if (is_link($path) || is_link(dirname($path))) { throw new RuntimeException('Linked presentation output refused.'); }
    if ($write && $changed) {
        if (! is_dir(dirname($path)) && ! mkdir(dirname($path), 0700, true)) { throw new RuntimeException('Presentation output directory unavailable.'); }
        file_put_contents($path, $json);
    }
    $failed = $failed || ($check && $changed);
    echo $name.': '.$package['counts']['questions'].' questions, '.$package['counts']['locale_rows'].' locale rows, '.($changed ? ($write ? 'written' : 'pending') : 'unchanged')."\n";
}
exit($failed ? 1 : 0);
