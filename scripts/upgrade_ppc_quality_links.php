<?php

declare(strict_types=1);

require_once __DIR__.'/lib/ppc_quality_links.php';
$root = dirname(__DIR__);
$check = in_array('--check', $argv, true);
$families = ['Forms' => 'forms', 'Negatives' => 'negatives', 'Questions' => 'questions', 'TimeExpressions' => 'time-expressions'];
foreach ($families as $family => $slug) {
    $path = $root.'/database/seeders/V3/TheoryLinks/data/past-perfect-continuous-'.$slug.'-theory-links.json';
    $manifest = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $projected = ppcQualityProjectLinks($manifest, $family, $root);
    if ($check) {
        if ($projected !== $manifest) {
            throw new RuntimeException('Stale PPC UUID link projection: '.$family);
        }
    } else {
        file_put_contents($path, json_encode($projected, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
    }
    $count = array_sum(array_map(static fn ($test) => count($test['question_links']), $projected['tests_on_page']));
    echo ($check ? 'Verified' : 'Projected').' '.$family.': '.$count.' explicit scoped UUID maps.'.PHP_EOL;
}
