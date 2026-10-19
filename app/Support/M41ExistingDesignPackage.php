<?php

namespace App\Support;

use RuntimeException;

/** Presentation-only restoration. All teaching text remains in the accepted M41 master. */
final class M41ExistingDesignPackage
{
    public const SOURCE = 'database/content-patches/m41-existing-native-design.v1.json';
    public const SOURCE_SHA = '2b985fcc79b1481b38c7a6570fcc6829858bc99c76a9f2d569574958094de1d7';
    public const ACCEPTED_SHA = 'b92854de75ced14865594137f139a3f7f3b12d03';
    public const VIEW = 'engram.theory.blocks-v3.m41-existing-design-section';

    /** Only source indices, semantic component choices and colors: never replacement teaching text. */
    private const POINTS = [
        [
            'past-core' => ['usage-panels', [['past-event', 'usage-panel', 'blue'], ['past-process', 'usage-panel', 'emerald']]],
            'past-forms' => ['forms-grid', [['past-be', 'forms-note', 'slate']]],
            'past-together' => ['usage-panels', [['past-background', 'usage-panel', 'emerald'], ['past-parallel', 'usage-panel', 'blue']]],
            'past-markers' => ['usage-panels', [['past-clock', 'usage-panel', 'sky'], ['past-yesterday', 'usage-panel', 'blue'], ['past-state', 'usage-panel', 'amber']]],
            'past-mistakes' => ['composite', [['past-error-did', 'mistakes-grid', 'rose'], ['past-error-ing', 'mistakes-grid', 'amber'], ['past-not-error', 'usage-panel', 'sky']]],
            'past-summary' => ['summary-list', [['past-check', 'summary-list', 'emerald']]],
        ],
        [
            'present-core' => ['usage-panels', [['present-routine', 'usage-panel', 'blue'], ['present-current', 'usage-panel', 'emerald']]],
            'present-forms' => ['forms-grid', [['present-be', 'forms-note', 'slate']]],
            'present-uses' => ['usage-panels', [['present-around', 'usage-panel', 'emerald'], ['present-temporary', 'usage-panel', 'blue'], ['present-change', 'usage-panel', 'amber']]],
            'present-states' => ['composite', [['present-know', 'usage-panel', 'blue'], ['present-think', 'comparison-table', 'sky']]],
            'present-context' => ['usage-panels', [['present-markers', 'usage-panel', 'blue'], ['present-always', 'usage-panel', 'emerald'], ['present-future', 'usage-panel', 'amber']]],
            'present-check' => ['composite', [['present-form-errors', 'mistakes-grid', 'rose'], ['present-summary', 'summary-list', 'emerald']]],
        ],
        [
            'perfect-core' => ['usage-panels', [['perfect-now', 'usage-panel', 'emerald'], ['perfect-then', 'usage-panel', 'blue']]],
            'perfect-forms' => ['forms-grid', [['perfect-v2v3', 'forms-note', 'slate']]],
            'perfect-experience' => ['usage-panels', [['perfect-ever', 'usage-panel', 'emerald'], ['perfect-news', 'usage-panel', 'blue']]],
            'perfect-time' => ['composite', [['perfect-today', 'comparison-table', 'sky'], ['perfect-duration', 'comparison-table', 'blue']]],
            'perfect-markers' => ['composite', [['perfect-recent', 'usage-panel', 'emerald'], ['perfect-real-errors', 'mistakes-grid', 'rose']]],
            'perfect-summary' => ['summary-list', [['perfect-check', 'summary-list', 'emerald']]],
        ],
    ];

    /** Explicitly approved correction pairs; warning kinds or arrows do not infer an error. */
    private const ERRORS = [
        'past-error-did' => [[0, 'Did she took the key?', 'Did she take the key?']],
        'past-error-ing' => [[0, 'We were wait outside.', 'We were waiting outside.']],
        'present-form-errors' => [
            [0, 'Does she works here?', 'Does she work here?'],
            [1, 'They waiting outside.', 'They are waiting outside.'],
            [2, 'I am knowing the answer.', 'I know the answer.'],
        ],
        'perfect-real-errors' => [
            [0, 'She has wrote the note.', 'She has written the note.'],
            [1, 'Did she wrote the note?', 'Did she write the note?'],
            [2, 'I have checked it yesterday.', 'I checked it yesterday.'],
        ],
    ];

    public static function bytes(array $mapping): string
    {
        return json_encode($mapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    }

    /** Pure deterministic projection: no Laravel, database, HTML or authored prose writes. */
    public static function build(array $master): array
    {
        if (($master['version'] ?? null) !== '1.0.1' || ($master['locale'] ?? null) !== 'uk' || count($master['lessons'] ?? []) !== 3) {
            throw new RuntimeException('M41 native design requires the exact approved lesson set.');
        }
        $mapping = ['schema' => 'gramlyze.m41.existing-native-design.v1', 'accepted_commit' => self::ACCEPTED_SHA,
            'design_reference_commit' => M41AuthoredTenseComparisonsPackage::BASE_SHA,
            'master_sha256' => M41AuthoredTenseComparisonsPackage::MASTER_SHA,
            'accepted_projection_sha256' => M41AuthoredTenseComparisonsPackage::SOURCE_SHA, 'targets' => []];
        $count = 0; $errors = 0;
        foreach ($master['lessons'] as $owner => $lesson) {
            if (array_keys(self::POINTS[$owner]) !== array_column($lesson['sections'], 'id')) {
                throw new RuntimeException('M41 native design section order differs.');
            }
            $target = ['identity' => $lesson['identity'], 'lesson_key' => $lesson['key'], 'sections' => []];
            foreach ($lesson['sections'] as $index => $section) {
                [$layout, $choices] = self::POINTS[$owner][$section['id']];
                if (array_column($choices, 0) !== array_column($section['points'], 'id')) {
                    throw new RuntimeException('M41 native design point order differs.');
                }
                $config = ['id' => $section['id'], 'section_index' => $index, 'layout' => $layout, 'points' => [], 'forms' => null];
                foreach ($section['points'] as $pointIndex => $point) {
                    [, $component, $color] = $choices[$pointIndex];
                    $pointConfig = ['id' => $point['id'], 'point_index' => $pointIndex, 'component' => $component,
                        'color' => $color, 'source_pointer' => '/lessons/'.$owner.'/sections/'.$index.'/points/'.$pointIndex,
                        'error_paragraphs' => []];
                    foreach (self::ERRORS[$point['id']] ?? [] as [$paragraphIndex, $wrong, $right]) {
                        $paragraph = $point['paragraphs_uk'][$paragraphIndex] ?? null;
                        if (!is_string($paragraph) || substr_count($paragraph, $wrong) !== 1 || substr_count($paragraph, $right) !== 1) {
                            throw new RuntimeException('M41 explicit native error selector differs.');
                        }
                        $wrongStart = strpos($paragraph, $wrong); $rightStart = strpos($paragraph, $right);
                        if ($wrongStart + strlen($wrong) > $rightStart) { throw new RuntimeException('M41 error source order differs.'); }
                        $fragments = [];
                        $cursor = 0;
                        foreach ([['wrong', $wrongStart, strlen($wrong)], ['right', $rightStart, strlen($right)]] as [$role, $start, $length]) {
                            if ($start > $cursor) { $fragments[] = ['role' => 'plain', 'start_bytes' => $cursor, 'length_bytes' => $start - $cursor]; }
                            $fragments[] = ['role' => $role, 'start_bytes' => $start, 'length_bytes' => $length]; $cursor = $start + $length;
                        }
                        if ($cursor < strlen($paragraph)) {
                            $fragments[] = ['role' => 'plain', 'start_bytes' => $cursor, 'length_bytes' => strlen($paragraph) - $cursor];
                        }
                        $pointConfig['error_paragraphs'][] = ['paragraph_index' => $paragraphIndex,
                            'paragraph_sha256' => hash('sha256', $paragraph), 'fragments' => $fragments];
                        $errors++;
                    }
                    if (($component === 'mistakes-grid') !== !empty($pointConfig['error_paragraphs'])) {
                        throw new RuntimeException('M41 mistakes require an explicit finite correction pair.');
                    }
                    $config['points'][] = $pointConfig; $count++;
                }
                if (isset($section['table'])) {
                    if ($layout !== 'forms-grid' || count($section['table']['columns']) !== 3 || count($section['table']['rows']) !== 3) {
                        throw new RuntimeException('M41 forms table structure differs.');
                    }
                    $cells = [];
                    foreach ($section['table']['rows'] as $rowIndex => $row) {
                        if (count($row) !== 3) { throw new RuntimeException('M41 forms row arity differs.'); }
                        foreach ([1, 2] as $columnIndex) {
                            if (substr_count($row[$columnIndex], "\n") !== 1) { throw new RuntimeException('M41 form must have its exact English and Ukrainian source lines.'); }
                            $cells[] = ['row_index' => $rowIndex, 'column_index' => $columnIndex,
                                'split_byte_offset' => strpos($row[$columnIndex], "\n"), 'source_sha256' => hash('sha256', $row[$columnIndex])];
                        }
                    }
                    $config['forms'] = ['component' => 'forms-grid', 'column_indices' => [0, 1, 2], 'row_indices' => [0, 1, 2], 'cells' => $cells];
                } elseif ($layout === 'forms-grid') { throw new RuntimeException('M41 missing forms source.'); }
                $target['sections'][] = $config;
            }
            $mapping['targets'][] = $target;
        }
        if ($count !== 35 || $errors !== 8) { throw new RuntimeException('M41 native finite scope differs.'); }
        return $mapping;
    }

    public static function validate(array $master, array $mapping): void
    {
        if ($mapping !== self::build($master)) { throw new RuntimeException('M41 native design differs from its finite semantic mapping.'); }
    }

    public static function load(?string $root = null): array
    {
        $root ??= base_path();
        $bytes = file_get_contents($root.'/'.self::SOURCE);
        if (!hash_equals(self::SOURCE_SHA, hash('sha256', $bytes))) { throw new RuntimeException('M41 native design mapping bytes differ.'); }
        $mapping = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        [$before] = M41AuthoredTenseComparisonsPackage::load($root);
        self::validate(M41AuthoredTenseComparisonsPackage::authorMaster($before, $root), $mapping);
        return $mapping;
    }

    /** Ordered source-only fragments, with no omitted or repeated paragraph bytes. */
    public static function paragraphFragments(string $paragraph, array $plan): array
    {
        if (($plan['paragraph_sha256'] ?? null) !== hash('sha256', $paragraph)) { throw new RuntimeException('M41 native paragraph source differs.'); }
        $out = []; $cursor = 0; $roles = [];
        foreach ($plan['fragments'] ?? [] as $fragment) {
            $role = $fragment['role'] ?? null; $start = $fragment['start_bytes'] ?? null; $length = $fragment['length_bytes'] ?? null;
            if (!in_array($role, ['plain', 'wrong', 'right'], true) || !is_int($start) || !is_int($length)
                || $start !== $cursor || $length <= 0 || $start + $length > strlen($paragraph)) {
                throw new RuntimeException('M41 native error range must cover ordered source exactly once.');
            }
            $text = substr($paragraph, $start, $length);
            if (!mb_check_encoding($text, 'UTF-8')) { throw new RuntimeException('M41 native range splits a UTF-8 character.'); }
            $out[] = ['role' => $role, 'text' => $text]; $roles[] = $role; $cursor += $length;
        }
        if ($cursor !== strlen($paragraph) || count(array_filter($roles, fn ($r) => $r === 'wrong')) !== 1
            || count(array_filter($roles, fn ($r) => $r === 'right')) !== 1 || implode('', array_column($out, 'text')) !== $paragraph) {
            throw new RuntimeException('M41 native error partition is incomplete.');
        }
        return $out;
    }

    /** Called after existing M41 guards. Independently rebinds the exact physical block. */
    public static function decorate(object $block, array $data, array $presentation): ?array
    {
        if (($block->locale ?? null) !== 'uk' || !isset($data['m41_v1'])) { return null; }
        try {
            [$before, $package] = M41AuthoredTenseComparisonsPackage::load();
            M41AuthoredTenseComparisonsPackage::validate($before, $package);
            $mapping = self::load();
            foreach ($package['targets'] as $owner => $target) {
                if (($block->seeder ?? null) !== $target['identity']) { continue; }
                foreach ($target['plans'] as $plan) {
                    if ($plan['role'] !== 'section') { continue; }
                    $slot = $plan['slot']; $source = $target['after']['page']['blocks'][$slot];
                    if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $slot + 1)) { continue; }
                    if (($block->type ?? null) !== $source['type'] || (int) ($block->sort_order ?? -1) !== $slot + 1
                        || $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
                    $expectedData = $data; $expectedPoints = [];
                    foreach ($plan['points'] as $pointPlan) {
                        if ($pointPlan['decision'] !== 'point_detail') { continue; }
                        unset($expectedData['sections'][$pointPlan['point_index']]['note']);
                        $key = 'm41-'.$pointPlan['id'];
                        $expectedPoints[$pointPlan['point_index']] = ['key' => $key, 'title' => $pointPlan['title'], 'fragments' => [
                            ['id' => 'block-'.$key.'-detail', 'type' => 'm41-author-html', 'value' => $pointPlan['detail_html']],
                        ]];
                    }
                    if (($presentation['data'] ?? null) !== $expectedData || ($presentation['points'] ?? null) !== $expectedPoints
                        || ($presentation['legacy_section'] ?? null) !== null || ($presentation['legacy_practice_id'] ?? null) !== null
                        || ($presentation['native_view'] ?? null) !== 'engram.theory.blocks-v3.m41-author-section') { return null; }
                    $config = $mapping['targets'][$owner]['sections'][$plan['section_index']];
                    if ($config['id'] !== $data['author_section']['id']) { return null; }
                    if ($config['forms'] !== null) {
                        $table = $data['author_section']['table']; $formRows = [];
                        foreach ($config['forms']['row_indices'] as $rowIndex) {
                            $cells = [];
                            foreach (array_filter($config['forms']['cells'], fn ($cell) => $cell['row_index'] === $rowIndex) as $cell) {
                                $text = $table['rows'][$rowIndex][$cell['column_index']]; $split = $cell['split_byte_offset'];
                                if (hash('sha256', $text) !== $cell['source_sha256'] || substr($text, $split, 1) !== "\n") { return null; }
                                $cells[] = ['column_index' => $cell['column_index'], 'source_cell' => $rowIndex.'-'.$cell['column_index'],
                                    'en' => substr($text, 0, $split), 'uk' => substr($text, $split + 1)];
                            }
                            $formRows[] = ['row_index' => $rowIndex, 'label' => $table['rows'][$rowIndex][0], 'cells' => $cells];
                        }
                        $config['forms'] = ['columns' => $table['columns'], 'rows' => $formRows];
                    }
                    $presentation['data']['m41_existing_design'] = $config;
                    foreach ($presentation['points'] as $pointIndex => &$pointPresentation) {
                        // A finite render hint only; original fragment identity,
                        // type and frozen HTML value are deliberately untouched.
                        $pointPresentation['fragments'][0]['m41_existing_design_detail'] =
                            $data['author_section']['points'][$pointIndex]['detail'];
                    }
                    unset($pointPresentation);
                    $presentation['native_view'] = self::VIEW;
                    return $presentation;
                }
            }
        } catch (\Throwable) { /* Original complete accepted M41 rendering remains available. */ }
        return null;
    }
}
