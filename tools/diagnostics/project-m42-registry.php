<?php

// Read-only accepted-source inventory; the optional output is a new metadata-only
// registry. No Laravel bootstrap, database writes, seeding or server configuration.
require dirname(__DIR__, 2).'/vendor/autoload.php';

$root = dirname(__DIR__, 2);
$classes = [
    27 => App\Support\M27LinkingWordsPackage::class,
    28 => App\Support\M28EmphasisPackage::class,
    29 => App\Support\M29SentenceStructurePackage::class,
    30 => App\Support\M30ParticipleClausesPackage::class,
    31 => App\Support\M31ConditionalsPackage::class,
    32 => App\Support\M32FormalEnglishPackage::class,
    33 => App\Support\M33AcademicEnglishPackage::class,
    34 => App\Support\M34ArgumentationCohesionPackage::class,
    35 => App\Support\M35PassiveReportingPackage::class,
    36 => App\Support\M36ModalsSubjunctivePackage::class,
    37 => App\Support\M37GrammarStructuresPackage::class,
    38 => App\Support\M38ArticlesCollocationsPackage::class,
    39 => App\Support\M39AuthoredRevisionPackage::class,
    40 => App\Support\M40TensesB1Package::class,
];
$reports = glob($root.'/docs/reports/seo-m*-layers.md');
$registry = ['schema' => 'gramlyze.m42.native-design-registry.v1',
    'reference_sha' => '053c6ddbe4b42b23f724ec336fdf472b6d219215',
    'base_sha' => '053c6ddbe4b42b23f724ec336fdf472b6d219215', 'targets' => []];
foreach ($classes as $packageNumber => $class) {
    [, $package] = $class::load($root);
    $overlay = $packageNumber === 39 ? App\Support\M39PracticeUiPackage::load($root) : null;
    foreach ($package['targets'] as $owner => $target) {
        $definition = $overlay !== null ? $overlay['targets'][$owner]['after'] : $target['after'];
        $slug = $definition['slug'];
        $category = $definition['page']['category']['slug'];
        $path = '/theory/'.($category === 'word-order' ? 'basic-grammar/word-order' : $category).'/'.$slug;
        $reportMatches = [];
        foreach ($reports as $report) {
            if (!str_starts_with(basename($report), 'seo-m'.$packageNumber.'-')) { continue; }
            preg_match_all('~http://gramlyze\.loc/theory/[^\s)\]#]+~u', file_get_contents($report), $matches);
            foreach ($matches[0] as $url) {
                if (str_ends_with($url, '/'.$slug)) { $reportMatches[] = $url; }
            }
        }
        if (array_values(array_unique($reportMatches)) !== ['http://gramlyze.loc'.$path]) {
            throw new RuntimeException('M42 canonical route disagrees with accepted report: '.$slug);
        }
        $blocks = []; $details = 0; $exerciseCount = 0; $controls = 0; $formats = []; $answerKeys = 0;
        foreach ($definition['page']['blocks'] as $index => $block) {
            $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
            $detailCount = 0;
            $planIndex = $packageNumber === 40 ? $index - $target['native_count'] : $index - 1;
            $plan = $target['plans'][$planIndex] ?? null;
            if ($plan !== null) {
                $detailCount = count(array_filter($plan['points'] ?? [], fn ($point) => ($point['detail'] ?? '') !== ''));
                $details += $detailCount;
            }
            $blockRecord = ['source_index' => $index, 'sort_order' => $index + 1,
                'uuid' => App\Support\M26DetailPackage::uuid($target['identity'], $block, $index + 1),
                'type' => $block['type'], 'column' => $block['column'] ?? null,
                'heading' => $block['heading'] ?? null, 'level' => $block['level'] ?? null,
                'title' => $data['title'] ?? null, 'body_sha256' => hash('sha256', $block['body']),
                'detail_count' => $detailCount, 'section_count' => count($data['sections'] ?? []),
                'table_columns' => count($data['headers'] ?? []), 'table_rows' => count($data['rows'] ?? [])];
            if ($block['type'] === 'practice-set') {
                $answerKeys = count($data['author_self_check']['answers'] ?? []);
                if (isset($data['cases'])) {
                    $exerciseCount = count($data['cases']);
                    foreach ($data['cases'] as $case) {
                        foreach ($case['controls'] as $control) { $controls++; $formats[] = $control['kind']; }
                    }
                } else {
                    foreach (['selects' => 'select', 'choices' => 'choice', 'inputs' => 'manual', 'rephrase' => 'rephrase'] as $key => $format) {
                        foreach ($data[$key] ?? [] as $item) {
                            $exerciseCount++; $controls++; $formats[] = $format;
                            foreach ($item['m38_semantic_checks'] ?? [] as $part) { $controls++; $formats[] = 'semantic-choice'; }
                        }
                    }
                }
            }
            $blocks[] = $blockRecord;
        }
        $registry['targets'][] = ['original_stage' => 'M'.($packageNumber - 16),
            'current_package' => 'M'.$packageNumber, 'package_class' => $class,
            'source' => $class::SOURCE, 'source_sha256' => $class::SOURCE_SHA,
            'overlay_source' => $overlay !== null ? App\Support\M39PracticeUiPackage::SOURCE : null,
            'overlay_sha256' => $overlay !== null ? App\Support\M39PracticeUiPackage::SOURCE_SHA : null,
            'owner_index' => $owner, 'identity' => $target['identity'], 'definition' => $target['path'],
            'slug' => $slug, 'title' => $definition['page']['title'], 'local_url' => 'http://gramlyze.loc'.$path,
            'source_locales' => [$definition['page']['locale']],
            'blocks' => $blocks, 'teaching_details' => $details, 'answer_keys' => $answerKeys,
            'exercise_count' => $exerciseCount, 'control_count' => $controls,
            'response_formats' => array_values(array_unique($formats))];
    }
}
if (count($registry['targets']) !== 42 || count(array_unique(array_column($registry['targets'], 'identity'))) !== 42
    || count(array_unique(array_column($registry['targets'], 'local_url'))) !== 42) {
    throw new RuntimeException('M42 inventory must contain exactly 42 distinct accepted owners.');
}
$bytes = json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$path = $root.'/database/content-patches/m42-native-design-registry.v1.json';
if (($argv[1] ?? '--check') === '--write-new') {
    $handle = fopen($path, 'x');
    if ($handle === false) { throw new RuntimeException('M42 registry already exists; no overwrite.'); }
    fwrite($handle, $bytes); fclose($handle);
} elseif (!is_file($path) || file_get_contents($path) !== $bytes) {
    throw new RuntimeException('M42 registry differs from accepted-source inventory.');
}
echo json_encode(['targets' => count($registry['targets']), 'sha256' => hash('sha256', $bytes),
    'blocks' => array_sum(array_map(fn ($target) => count($target['blocks']), $registry['targets'])),
    'details' => array_sum(array_column($registry['targets'], 'teaching_details')),
    'exercises' => array_sum(array_column($registry['targets'], 'exercise_count')),
    'controls' => array_sum(array_column($registry['targets'], 'control_count'))], JSON_THROW_ON_ERROR)."\n";
