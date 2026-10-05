<?php

namespace Tests\Unit;

use App\Support\AcceptedAnswerVariants;
use PHPUnit\Framework\TestCase;

class PastPerfectContinuousMixedQualityTest extends TestCase
{
    private function definitions(): array
    {
        $definitions = [];
        foreach (['Forms', 'Negatives', 'Questions', 'TimeExpressions'] as $family) {
            $path = dirname(__DIR__, 2).'/database/seeders/V3/Tenses/PastPerfectContinuous/PastPerfectContinuous'.$family.'AllLevelsV3Seeder/definition.json';
            $definitions[$family] = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        }
        return $definitions;
    }

    private function normalize(string $value): string
    {
        $value = AcceptedAnswerVariants::normalizeTypography($value);
        $value = preg_replace('/\{a\d+\}/u', '{gap}', $value);
        return mb_strtolower(trim(preg_replace('/[\p{P}\s]+/u', ' ', $value)));
    }

    public function test_complete_editorial_projection_is_stable_and_uuid_preserving(): void
    {
        require_once dirname(__DIR__, 2).'/scripts/lib/ppc_quality_mixed.php';
        $catalog = ppcQualityCatalog();
        $prefixes = ['Forms' => 'forms', 'Negatives' => 'negatives', 'Questions' => 'questions', 'TimeExpressions' => 'time-expressions'];
        foreach ($this->definitions() as $family => $definition) {
            $this->assertCount(72, $definition['questions']);
            $this->assertSame(0, $definition['defaults']['type']);
            $this->assertSame($definition, ppcQualityProject($definition, $catalog[$family]), $family.' projection is stale');
            $counts = array_count_values(array_column($definition['questions'], 'level'));
            foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
                $this->assertSame(12, $counts[$level]);
            }
            foreach ($definition['questions'] as $index => $question) {
                $this->assertSame($index + 1, $question['id']);
                $this->assertSame(sprintf('pastpc-%s-v3-%s-%02d', $prefixes[$family], strtolower($question['level']), $index % 12 + 1), $question['uuid']);
            }
        }
    }

    public function test_every_marker_has_a_unique_answer_localized_usable_hint_and_live_source(): void
    {
        $markerCount = 0;
        foreach ($this->definitions() as $family => $definition) {
            foreach ($definition['questions'] as $question) {
                $uuid = $question['uuid'];
                $this->assertSame([$question['question']], $question['variants'], $uuid);
                $this->assertMatchesRegularExpression('/[.!?]$/u', $question['question'], $uuid);
                $this->assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $question['question'], $uuid);
                preg_match_all('/\{(a\d+)\}/', $question['question'], $matches);
                $this->assertSame(array_keys($question['markers']), $matches[1], $uuid);
                $this->assertNotSame('', $question['source_text_uk'], $uuid);
                $this->assertDoesNotMatchRegularExpression('/[a-z]/i', $question['source_text_uk'], $uuid.' mixed-language source');
                $this->assertSame($question['source_text_uk'], $question['localizations']['uk']['source_text'], $uuid);
                $this->assertNotSame($question['source_text_uk'], $question['localizations']['pl']['source_text'], $uuid);
                foreach (['en', 'pl'] as $locale) {
                    $this->assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $question['localizations'][$locale]['source_text'], $uuid.' '.$locale);
                }
                $this->assertStringStartsWith('Reconstruct the sentence in the displayed clause order.', $question['localizations']['en']['source_text'], $uuid);
                $this->assertSame(count($question['markers']), substr_count($question['localizations']['en']['source_text'], '____'), $uuid.' EN reveals the target');
                foreach ($question['markers'] as $name => $marker) {
                    $markerCount++;
                    $this->assertCount(5, $marker['options'], $uuid.' '.$name);
                    $this->assertSame($marker['answer'], $marker['options'][0], $uuid.' '.$name);
                    $normalized = array_map($this->normalize(...), $marker['options']);
                    $this->assertCount(5, array_unique($normalized), $uuid.' '.$name);
                    [$before, $after] = explode('{'.$name.'}', $question['question'], 2);
                    foreach (array_slice($marker['options'], 1) as $distractor) {
                        $this->assertFalse(AcceptedAnswerVariants::matches($marker['answer'], $distractor, $after, $before), $uuid.' accepted distractor '.$distractor);
                    }
                    $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $marker['verb_hint'], $uuid.' '.$name);
                    foreach (['uk', 'en', 'pl'] as $locale) {
                        $hint = $question['localizations'][$locale]['verb_hints'][$name];
                        $this->assertNotSame('', $hint, $uuid.' '.$locale.' '.$name);
                        $this->assertLessThanOrEqual(255, mb_strlen($hint), $uuid.' '.$locale.' '.$name.' exceeds question_answers.verb_hint');
                        if ($locale !== 'uk') {
                            $this->assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $hint, $uuid.' '.$locale.' '.$name);
                        }
                    }
                    if (preg_match('/ing\b/', $marker['answer'])) {
                        $this->assertStringStartsWith('Навчальна лема: ', $marker['verb_hint'], $uuid.' cannot be answered manually');
                        $this->assertStringNotContainsStringIgnoringCase($marker['answer'], $marker['verb_hint'], $uuid.' ready answer leaked');
                    }
                    if ($family === 'TimeExpressions') {
                        $this->assertDoesNotMatchRegularExpression('/\bhad\b/i', $marker['answer'], $uuid.' time bank became a generic verb gap');
                    }
                }
            }
        }
        $this->assertSame(295, $markerCount);
    }

    public function test_all_authored_stems_targets_and_localized_prompts_are_unique_after_normalization(): void
    {
        $stems = [];
        $targets = [];
        $sources = [];
        foreach ($this->definitions() as $definition) {
            foreach ($definition['questions'] as $question) {
                $uuid = $question['uuid'];
                $stem = $this->normalize($question['question']);
                $this->assertArrayNotHasKey($stem, $stems, $uuid.' duplicates '.($stems[$stem] ?? ''));
                $stems[$stem] = $uuid;
                $target = preg_replace_callback('/\{(a\d+)\}/', static fn ($match) => $question['markers'][$match[1]]['answer'], $question['question']);
                $target = $this->normalize($target);
                $this->assertArrayNotHasKey($target, $targets, $uuid.' duplicates '.($targets[$target] ?? ''));
                $targets[$target] = $uuid;
                $source = $this->normalize($question['source_text_uk']);
                $this->assertArrayNotHasKey($source, $sources, $uuid.' source duplicates '.($sources[$source] ?? ''));
                $sources[$source] = $uuid;
            }
        }
        $this->assertCount(288, $targets);
    }

    public function test_contractions_preserve_polarity_been_and_subject_roles(): void
    {
        foreach ($this->definitions() as $definition) {
            foreach ($definition['questions'] as $question) {
                foreach ($question['markers'] as $name => $marker) {
                    [$before, $after] = explode('{'.$name.'}', $question['question'], 2);
                    foreach (AcceptedAnswerVariants::for($marker['answer']) as $variant) {
                        $this->assertTrue(AcceptedAnswerVariants::matches($marker['answer'], $variant, $after, $before), $question['uuid'].' '.$variant);
                    }
                    $full = str_replace("hadn't", 'had not', $marker['answer']);
                    if (str_contains($full, 'been ')) {
                        $this->assertFalse(AcceptedAnswerVariants::matches($marker['answer'], str_replace('been ', '', $full), $after, $before), $question['uuid'].' missing been');
                    }
                    if (str_contains($full, 'not ')) {
                        $this->assertFalse(AcceptedAnswerVariants::matches($marker['answer'], str_replace('not ', '', $full), $after, $before), $question['uuid'].' missing not');
                    }
                }
            }
        }
        $this->assertTrue(AcceptedAnswerVariants::matches('She had known Lev for five years.', 'She’d known Lev for five years.'));
        $this->assertFalse(AcceptedAnswerVariants::matches('Anna had known Lev for five years.', 'Lev had known Anna for five years.'));
        $this->assertFalse(AcceptedAnswerVariants::matches('Yes, I had', "Yes, I'd"));
    }

    public function test_advanced_time_tasks_require_multiple_distinct_temporal_relationships(): void
    {
        $questions = $this->definitions()['TimeExpressions']['questions'];
        $multi = array_filter($questions, static fn ($question) => count($question['markers']) > 1);
        $this->assertCount(7, $multi);
        foreach ($multi as $question) {
            $this->assertContains($question['level'], ['C1', 'C2']);
            $this->assertCount(2, array_unique(array_column($question['markers'], 'answer')));
            $this->assertNotSame($question['markers']['a1']['verb_hint'], $question['markers']['a2']['verb_hint']);
        }
        $this->assertSame('for', $questions[0]['markers']['a1']['answer']);
        $this->assertStringContainsString('{a1} two hours', $questions[0]['question']);
    }

    public function test_theory_links_cover_only_all_624_authored_uuids_with_finite_semantic_maps(): void
    {
        require_once dirname(__DIR__, 2).'/scripts/lib/ppc_quality_links.php';
        $root = dirname(__DIR__, 2);
        $families = ['Forms' => 'forms', 'Negatives' => 'negatives', 'Questions' => 'questions', 'TimeExpressions' => 'time-expressions'];
        $seen = [];
        foreach ($families as $family => $slug) {
            $manifest = json_decode(file_get_contents($root.'/database/seeders/V3/TheoryLinks/data/past-perfect-continuous-'.$slug.'-theory-links.json'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($manifest, ppcQualityProjectLinks($manifest, $family, $root));
            $count = 0;
            foreach ($manifest['tests_on_page'] as $test) {
                $this->assertSame('explicit_question_uuid_map', $test['strategy']);
                $this->assertArrayNotHasKey('answer_value_to_bundle_fallback', $test);
                $this->assertArrayNotHasKey('question_text_to_bundle_fallback', $test);
                $this->assertArrayNotHasKey('tag_key_to_bundle', $test);
                $name = substr($test['seeder_class'], strrpos($test['seeder_class'], '\\') + 1);
                $path = $test['kind'] === 'virtual_mixed_test'
                    ? '/database/seeders/V3/Tenses/PastPerfectContinuous/'.$name.'/definition.json'
                    : '/database/seeders/V3/Polyglot/'.$name.'/definition.json';
                $definition = json_decode(file_get_contents($root.$path), true, 512, JSON_THROW_ON_ERROR);
                $this->assertSame(array_column($definition['questions'], 'uuid'), array_keys($test['question_links']));
                foreach ($test['question_links'] as $uuid => $bundles) {
                    $count++;
                    $this->assertArrayNotHasKey($uuid, $seen, 'A canonical question was mapped twice');
                    $seen[$uuid] = true;
                    $this->assertNotEmpty($bundles);
                    foreach ($bundles as $bundle) {
                        $this->assertArrayHasKey($bundle, $manifest['bundles']);
                        foreach ($manifest['bundles'][$bundle] as $alias) {
                            $this->assertArrayHasKey($alias, $manifest['theory_text_block_aliases']);
                            $this->assertStringContainsString('\\PastPerfectContinuous\\', $manifest['theory_text_block_aliases'][$alias]['seeder_class']);
                        }
                    }
                }
            }
            $this->assertSame($family === 'Forms' ? 192 : 144, $count);
            if ($family === 'Forms') {
                $this->assertSame(['negative_meaning', 'general_overview'], $manifest['tests_on_page'][1]['question_links']['polyglot-past-perfect-continuous-basics-b2-q13']);
                $this->assertSame(['question_form', 'general_overview'], $manifest['tests_on_page'][1]['question_links']['polyglot-past-perfect-continuous-basics-b2-q25']);
            }
        }
        $this->assertCount(624, $seen);
    }
}
