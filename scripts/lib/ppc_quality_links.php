<?php

declare(strict_types=1);

require_once __DIR__.'/ppc_quality_mixed.php';

/** Finite, reviewed UUID-slot maps. No textual/options fallback survives projection. */
function ppcQualityBuilderLinkSlots(): array
{
    return [
        'Forms' => [
            'A1' => ['past_result', 'continued_to_past_point', 'continued_to_past_point', 'duration_before_past', 'background_duration', 'past_result', 'past_result', 'background_duration', 'duration_before_past', 'background_duration', 'continued_to_past_point', 'typical_mistakes'],
            'A2' => ['duration_before_past', 'background_duration', 'duration_before_past', 'continued_to_past_point', 'past_result', 'past_result', 'continued_to_past_point', 'continued_to_past_point', 'background_duration', 'background_duration', 'typical_mistakes', 'background_duration'],
            'B1' => ['past_result', 'past_result', 'typical_mistakes|background_duration', 'duration_before_past', 'background_duration', 'past_result', 'duration_before_past', 'continued_to_past_point', 'background_duration', 'past_result', 'typical_mistakes', 'background_duration'],
            'B2' => ['past_result', 'duration_before_past', 'background_duration', 'past_result', 'continued_to_past_point', 'typical_mistakes|background_duration', 'past_result', 'background_duration', 'background_duration', 'continued_to_past_point', 'typical_mistakes', 'past_result'],
            'C1' => ['background_duration', 'continued_to_past_point', 'background_duration', 'past_result', 'background_duration', 'typical_mistakes|background_duration', 'continued_to_past_point', 'past_result', 'background_duration', 'typical_mistakes|background_duration', 'duration_before_past', 'past_result'],
            'C2' => ['background_duration', 'background_duration', 'continued_to_past_point', 'typical_mistakes|background_duration', 'background_duration', 'background_duration', 'typical_mistakes|background_duration', 'past_result', 'background_duration', 'typical_mistakes|background_duration', 'typical_mistakes|background_duration', 'past_result'],
        ],
        'Negatives' => [
            'A1' => ['negative_duration', 'negative_duration', 'had_not_been', 'negative_duration', 'negative_duration', 'not_position', 'not_position', 'not_position', 'had_not_been', 'had_not_been', 'not_position', 'negative_duration'],
            'A2' => ['negative_duration', 'negative_duration', 'not_position', 'not_position', 'negative_duration', 'negative_duration', 'negative_duration', 'not_position', 'not_position', 'had_not_been', 'negative_duration', 'negative_duration'],
            'B1' => ['not_position', 'negative_duration', 'not_position', 'not_position', 'negative_duration|typical_mistakes', 'not_position', 'negative_duration', 'not_position', 'not_position', 'negative_duration', 'not_position', 'had_not_been'],
            'B2' => ['not_position', 'negative_duration', 'negative_duration', 'not_position', 'not_position|typical_mistakes', 'negative_duration', 'not_position', 'negative_duration', 'negative_duration', 'not_position', 'negative_duration', 'negative_duration'],
            'C1' => ['not_position', 'not_position', 'not_position', 'negative_duration', 'not_position', 'not_position|typical_mistakes', 'negative_duration', 'negative_duration', 'negative_duration', 'not_position', 'negative_duration', 'negative_duration'],
            'C2' => ['not_position|typical_mistakes', 'not_position', 'negative_duration', 'negative_duration', 'not_position', 'typical_mistakes|not_position', 'not_position', 'typical_mistakes|not_position', 'not_position', 'negative_duration', 'not_position', 'not_position|typical_mistakes'],
        ],
        'Questions' => [
            'A1' => ['yes_no_questions', 'had_subject_been_ing', 'question_order', 'wh_questions', 'how_long_questions', 'short_answers', 'question_order', 'wh_questions', 'how_long_questions', 'yes_no_questions', 'short_answers', 'yes_no_questions'],
            'A2' => ['yes_no_questions', 'wh_questions', 'wh_questions|question_order', 'wh_questions', 'yes_no_questions', 'short_answers', 'how_long_questions', 'wh_questions', 'yes_no_questions', 'wh_questions|question_order', 'short_answers', 'yes_no_questions'],
            'B1' => ['wh_questions', 'wh_questions|question_order', 'how_long_questions', 'wh_questions|question_order', 'yes_no_questions', 'short_answers', 'wh_questions', 'wh_questions', 'yes_no_questions|question_order', 'wh_questions', 'short_answers', 'wh_questions|question_order'],
            'B2' => ['wh_questions', 'how_long_questions', 'wh_questions|question_order', 'wh_questions|question_order', 'yes_no_questions|question_order', 'short_answers', 'wh_questions', 'wh_questions', 'wh_questions', 'question_order|wh_questions', 'short_answers', 'wh_questions'],
            'C1' => ['question_order|wh_questions|how_long_questions', 'wh_questions|question_order', 'wh_questions|question_order', 'wh_questions|question_order', 'wh_questions', 'question_order|yes_no_questions', 'wh_questions', 'yes_no_questions|question_order', 'wh_questions|question_order', 'question_order|wh_questions', 'short_answers', 'wh_questions'],
            'C2' => ['question_order|wh_questions', 'wh_questions|question_order', 'wh_questions', 'wh_questions|question_order', 'yes_no_questions|question_order', 'question_order|yes_no_questions', 'wh_questions|question_order', 'wh_questions', 'wh_questions|question_order', 'question_order|wh_questions', 'short_answers', 'how_long_questions'],
        ],
        'TimeExpressions' => [
            'A1' => ['for_duration', 'since_start_point', 'all_period', 'before', 'by_the_time|for_duration', 'until_then', 'how_long', 'for_duration', 'since_start_point', 'all_period', 'for_duration|duration_before_past', 'since_start_point'],
            'A2' => ['for_duration', 'since_start_point', 'all_period', 'until_then', 'by_the_time|for_duration', 'since_start_point', 'for_duration', 'duration_before_past', 'since_start_point', 'for_duration', 'all_period|until_then', 'how_long'],
            'B1' => ['for_duration', 'since_start_point', 'all_period|duration_before_past', 'until_then', 'by_the_time|for_duration', 'since_start_point', 'duration_before_past', 'duration_before_past', 'before', 'for_duration', 'since_start_point', 'how_long|by_the_time'],
            'B2' => ['since_start_point', 'for_duration', 'until_then', 'by_the_time|for_duration', 'before|duration_before_past', 'until_then', 'since_start_point|before', 'all_period|duration_before_past', 'since_start_point', 'for_duration|before', 'duration_before_past', 'how_long|before'],
            'C1' => ['by_the_time|for_duration', 'since_start_point|until_then', 'since_start_point', 'before|duration_before_past', 'before', 'until_then', 'duration_before_past', 'for_duration', 'since_start_point', 'before|duration_before_past', 'until_then', 'how_long|before'],
            'C2' => ['by_the_time|for_duration', 'before', 'since_start_point', 'for_duration|duration_before_past', 'until_then|before', 'duration_before_past', 'since_start_point|before', 'until_then', 'all_period|duration_before_past', 'before', 'for_duration|since_start_point', 'by_the_time|for_duration'],
        ],
    ];
}

function ppcQualityMixedBundles(string $family, array $question, array $item): array
{
    $bundles = $question['tag_keys'];
    if ($family === 'Forms') {
        $bundles = match ($item['focus']) {
            'state', 'complete' => ['typical_mistakes'],
            'cause' => ['past_result'], 'spelling' => ['ing_spelling'],
            default => $bundles,
        };
    } elseif ($family === 'Negatives') {
        $bundles = match ($item['focus']) {
            'not_long', 'not_well' => ['negative_duration', 'not_position'],
            default => [str_contains($item['answer'], "hadn't") ? 'hadnt_been' : 'had_not_been', 'not_position'],
        };
    } elseif ($family === 'Questions') {
        $bundles = match ($item['focus']) {
            'short_yes', 'short_no' => ['short_answers'],
            'duration_q' => ['how_long_questions', 'question_order'],
            'yesno' => ['yes_no_questions', 'question_order'],
            'negative_q' => ['yes_no_questions', 'question_order', 'typical_mistakes'],
            'embedded' => ['question_order', str_contains($item['question'], 'whether') ? 'yes_no_questions' : 'wh_questions'],
            default => ['wh_questions', 'question_order'],
        };
    }
    return array_values(array_unique([...$bundles, 'general_overview']));
}

function ppcQualityProjectLinks(array $manifest, string $family, string $root): array
{
    $mixedName = 'PastPerfectContinuous'.$family.'AllLevelsV3Seeder';
    $builderName = 'PolyglotPastPerfectContinuous'.$family.'AllLevelsLessonSeeder';
    $mixedPath = '/database/seeders/V3/Tenses/PastPerfectContinuous/'.$mixedName.'/definition.json';
    $builderPath = '/database/seeders/V3/Polyglot/'.$builderName.'/definition.json';
    $mixed = json_decode(file_get_contents($root.$mixedPath), true, 512, JSON_THROW_ON_ERROR);
    $builder = json_decode(file_get_contents($root.$builderPath), true, 512, JSON_THROW_ON_ERROR);
    $catalog = ppcQualityCatalog()[$family];
    $slots = ppcQualityBuilderLinkSlots()[$family];
    foreach ($manifest['tests_on_page'] as &$test) {
        if ($test['kind'] === 'direct_sentence_builder') {
            $test['question_links'] = [];
            foreach ($builder['questions'] as $index => $question) {
                $selected = $slots[$question['level']][$index % 12];
                $test['question_links'][$question['uuid']] = [...explode('|', $selected), 'general_overview'];
            }
        } elseif ($test['kind'] === 'virtual_mixed_test') {
            $pattern = $test['saved_test_slug_pattern'];
            $test = [
                'kind' => 'virtual_mixed_test', 'saved_test_slug_pattern' => $pattern,
                'seeder_class' => $mixed['seeder']['class'],
                'strategy' => 'explicit_question_uuid_map', 'question_links' => [],
            ];
            foreach ($mixed['questions'] as $index => $question) {
                $test['question_links'][$question['uuid']] = ppcQualityMixedBundles($family, $question, $catalog[$question['level']][$index % 12]);
            }
        } elseif ($test['kind'] === 'legacy_polyglot_coverage') {
            $legacy = json_decode(file_get_contents($root.'/database/seeders/V3/Polyglot/PolyglotPastPerfectContinuousBasicsB2LessonSeeder/definition.json'), true, 512, JSON_THROW_ON_ERROR);
            $legacySlots = [
                'background_duration', 'background_duration', 'continued_to_past_point', 'continued_to_past_point', 'past_result', 'background_duration', 'typical_mistakes|background_duration', 'continued_to_past_point', 'duration_before_past', 'background_duration', 'background_duration', 'past_result',
                'negative_meaning', 'negative_form|negative_meaning', 'negative_meaning', 'negative_form|negative_meaning', 'negative_meaning', 'negative_form|negative_meaning', 'negative_meaning', 'negative_meaning', 'negative_form|negative_meaning', 'negative_meaning', 'negative_form|negative_meaning', 'negative_meaning',
                'question_form', 'question_form', 'question_form', 'question_form', 'question_form', 'question_form', 'question_form', 'question_form', 'question_form', 'question_form', 'question_form', 'question_form',
                'question_wh|time_intervals', 'question_wh|time_intervals', 'question_wh', 'question_wh', 'question_wh', 'question_wh', 'question_wh', 'question_wh', 'question_wh|negative_form', 'question_wh', 'question_wh', 'question_wh',
            ];
            $test['question_links'] = [];
            foreach ($legacy['questions'] as $index => $question) {
                $test['question_links'][$question['uuid']] = [...explode('|', $legacySlots[$index]), 'general_overview'];
            }
        }
    }
    unset($test);
    if ($family === 'Forms') {
        $cross = [
            'negative_form' => ['Negatives', 2, 'Negative form'],
            'negative_meaning' => ['Negatives', 3, 'Negative scope: duration and quality'],
            'question_form' => ['Questions', 2, 'Direct question order'],
            'question_wh' => ['Questions', 3, 'Wh-questions and requested information'],
            'time_intervals' => ['TimeExpressions', 3, 'Duration and frequency before a past point'],
        ];
        foreach ($cross as $alias => [$theme, $sort, $title]) {
            $manifest['theory_text_block_aliases'][$alias] = [
                'seeder_class' => 'Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuous'.$theme.'TheorySeeder',
                'sort_order' => $sort, 'title' => $title,
            ];
            $manifest['bundles'][$alias] = [$alias, 'summary'];
        }
        $manifest['seeder_requirements']['seed_pages'] = array_values(array_unique([
            ...$manifest['seeder_requirements']['seed_pages'],
            ...array_column($manifest['theory_text_block_aliases'], 'seeder_class'),
        ]));
    }
    foreach ($manifest['tests_on_page'] as $test) {
        foreach ($test['question_links'] as $uuid => $bundles) {
            foreach ($bundles as $bundle) {
                if (! isset($manifest['bundles'][$bundle])) {
                    throw new RuntimeException('Unknown bundle '.$bundle.' for '.$uuid);
                }
            }
        }
    }
    return $manifest;
}
