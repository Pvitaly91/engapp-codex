<?php

declare(strict_types=1);

/** Explicit, offline projection into exactly four versioned PPC mixed definitions. No DB or runtime writes. */
require_once __DIR__.'/lib/ppc_quality_mixed.php';

$root = dirname(__DIR__);
$check = in_array('--check', $argv, true);
foreach (ppcQualityCatalog() as $family => $levels) {
    $path = $root.'/database/seeders/V3/Tenses/PastPerfectContinuous/PastPerfectContinuous'.$family.'AllLevelsV3Seeder/definition.json';
    $definition = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $projected = ppcQualityProject($definition, $levels);
    $json = json_encode($projected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    if ($check) {
        if ($definition !== $projected) {
            throw new RuntimeException('Stale PPC authored projection: '.$family);
        }
    } else {
        file_put_contents($path, $json);
    }
    echo ($check ? 'Verified' : 'Projected').' '.$family.': 72 UUID-preserving questions, 12 per CEFR level.'.PHP_EOL;
}
