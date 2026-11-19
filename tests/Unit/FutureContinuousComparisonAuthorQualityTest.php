<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Support\FinitePracticeQualityCatalog;
use PHPUnit\Framework\TestCase;

/** Pure authored-data checks. Grammar/level decisions also receive independent editorial review. */
final class FutureContinuousComparisonAuthorQualityTest extends TestCase
{
    private function root(): string { return dirname(__DIR__, 2); }
    private function source(): array
    {
        return json_decode(file_get_contents($this->root().'/docs/content/future-continuous-comparison-quality/comparison.v1.json'), true, flags: JSON_THROW_ON_ERROR);
    }
    private function key(string $value): string
    {
        return trim(preg_replace('/[^\pL\pN]+/u', ' ', mb_strtolower($value)));
    }
    private function clean(string $value, string $id, bool $gap = false): void
    {
        self::assertSame(trim($value), $value, $id);
        self::assertNotSame('', $value, $id);
        if ($gap) { $value = str_replace('{a1}', '', $value); }
        self::assertDoesNotMatchRegularExpression('/<[^>]*>|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{[^}]*\}|_{2,}/iu', $value, $id);
    }
    public function test_fifty_four_distinct_tasks_cover_both_forms_and_every_level(): void
    {
        $s = $this->source();
        self::assertSame(1, $s['schema_version']);
        self::assertSame('comparison', $s['topic_key']);
        self::assertCount(54, $s['questions']);
        self::assertCount(54, array_unique(array_column($s['questions'], 'id')));
        self::assertDoesNotMatchRegularExpression('/polyglot|поліглот/iu', $s['title']);
        self::assertNotSame('', $s['cefr_note_uk']);
        $forms = array_count_values(array_column($s['questions'], 'future_form'));
        self::assertSame(27, $forms['future_continuous']);
        self::assertSame(27, $forms['future_perfect_continuous']);
        foreach (['A1','A2','B1','B2','C1','C2'] as $level) {
            self::assertNotSame('', $s['level_notes'][$level]);
            $rows = array_values(array_filter($s['questions'], fn ($r) => $r['level'] === $level));
            self::assertCount(9, $rows, $level);
            self::assertCount(6, array_filter($rows, fn ($r) => $r['kind'] === 'gap'));
            self::assertCount(3, array_filter($rows, fn ($r) => $r['kind'] === 'compose'));
            $counts = array_count_values(array_column($rows, 'future_form'));
            self::assertContains($counts['future_continuous'], [4,5]);
            self::assertContains($counts['future_perfect_continuous'], [4,5]);
        }
    }
    public function test_manual_hints_are_ukrainian_and_order_chunks_are_complete_logical_units(): void
    {
        foreach ($this->source()['questions'] as $r) {
            foreach (['uk','hint_uk'] as $field) {
                $this->clean($r[$field], $r['id'].'/'.$field);
                self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $r[$field]);
                self::assertDoesNotMatchRegularExpression('/[A-Za-z]/u', $r[$field]);
            }
            self::assertGreaterThanOrEqual(35, mb_strlen($r['hint_uk']));
            $this->clean($r['target'], $r['id']);
            self::assertMatchesRegularExpression('/[.!?]$/u', $r['target']);
            self::assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $r['target']);
            self::assertSame($r['target'], implode(' ', $r['groups']), $r['id']);
            self::assertGreaterThanOrEqual(2, count($r['groups']));
            foreach ($r['groups'] as $group) {
                $this->clean($group, $r['id']);
                $words = preg_split('/\s+/u', $group);
                self::assertLessThanOrEqual(3, count($words), $r['id']);
                if (count($words) > 1) {
                    self::assertDoesNotMatchRegularExpression('/\b(?:a|an|the|my|your|his|her|our|their|next)$/i', rtrim($group, ',.!?'), $r['id'].' dangling determiner');
                }
            }
            if ($r['kind'] === 'compose') { self::assertSame($r['uk'], $r['question']); }
        }
    }
    public function test_gap_stems_keep_will_subject_and_wh_out_of_short_complete_answers(): void
    {
        $synonyms = 0;
        foreach ($this->source()['questions'] as $r) {
            if ($r['kind'] !== 'gap') { continue; }
            self::assertSame(1, substr_count($r['question'], '{a1}'), $r['id']);
            $this->clean($r['question'], $r['id'], true);
            self::assertMatchesRegularExpression('/\bwill\b.*\{a1\}/i', $r['question'], $r['id']);
            self::assertDoesNotMatchRegularExpression('/\bwill\b/i', $r['answer'], $r['id']);
            self::assertSame(str_replace('{a1}', $r['answer'], $r['question']), $r['target']);
            self::assertCount(3, $r['options']);
            self::assertCount(3, array_unique($r['options']));
            self::assertSame($r['answer'], $r['options'][0]);
            foreach (array_merge($r['options'], $r['answer_synonyms'] ?? []) as $option) {
                $this->clean($option, $r['id']);
                self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $option)), $r['id']);
                self::assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $option);
            }
            $pattern = $r['future_form'] === 'future_continuous' ? '/^be \S+ing$/' : '/^have been \S+ing$/';
            self::assertMatchesRegularExpression($pattern, $r['answer'], $r['id']);
            foreach ($r['answer_synonyms'] ?? [] as $alias) {
                $synonyms++;
                self::assertNotContains($alias, array_slice($r['options'], 1));
                self::assertNotSame($r['answer'], $alias);
            }
        }
        self::assertGreaterThan(12, $synonyms);
    }
    public function test_reviewed_role_pools_and_every_contracted_or_lexical_slot_combination_are_reachable(): void
    {
        $s = $this->source();
        foreach ($s['choice_pools'] as $role => $pool) {
            self::assertMatchesRegularExpression('/^[a-z_]+$/D', $role);
            self::assertGreaterThanOrEqual(6, count(array_unique($pool)), $role);
            foreach ($pool as $value) {
                $this->clean($value, $role);
                self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $value)), $role);
            }
        }
        $aliases = 0;
        foreach ($s['questions'] as $r) {
            if ($r['kind'] !== 'compose') { continue; }
            self::assertSame($r['target'], implode(' ', $r['compose_slots']));
            self::assertCount(count($r['compose_slots']), $r['slot_roles']);
            $variants = [''];
            foreach ($r['compose_slots'] as $index => $slot) {
                self::assertArrayHasKey($r['slot_roles'][$index], $s['choice_pools'], $r['id']);
                $slotVariants = array_merge([$slot], FinitePracticeQualityCatalog::displayAliasesForToken($slot, $r['display_synonyms'] ?? []));
                $next = [];
                foreach ($variants as $prefix) {
                    foreach ($slotVariants as $value) { $next[] = trim($prefix.' '.$value); }
                }
                $variants = $next;
            }
            $accepted = array_merge([$r['target']], $r['accepted_targets'] ?? []);
            foreach ($variants as $v) { self::assertContains($v, $accepted, $r['id'].' incomplete finite whitelist: '.$v); }
            foreach ($accepted as $v) {
                $this->clean($v, $r['id']);
                self::assertContains($v, $variants, $r['id'].' unreachable alias');
            }
            $aliases += count($accepted) - 1;
        }
        self::assertGreaterThan(35, $aliases);
    }
    public function test_targets_and_full_aliases_do_not_repeat_prior_canonical_banks_or_other_levels(): void
    {
        $seen = [];
        foreach (['basic-grammar-quality','verb-to-be-quality','word-order-quality','nouns-articles-quantity-quality','pronouns-demonstratives-quality'] as $dir) {
            foreach (glob($this->root().'/docs/content/'.$dir.'/*.v1.json') as $file) {
                $d = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
                foreach ($d['questions'] ?? [] as $row) {
                    foreach (array_merge([$row['target']], $row['accepted_targets'] ?? []) as $target) {
                        $seen[$this->key($target)] = $file.'/'.$row['id'];
                    }
                }
            }
        }
        foreach ([
            'database/seeders/V3/FutureForms/FutureContinuousVsFuturePerfectContinuousAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Polyglot/PolyglotFutureContinuousVsFuturePerfectContinuousAllLevelsLessonSeeder/definition.json',
            'database/seeders/V3/PassiveVoice/PassiveVoicePresentSimpleAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Polyglot/PolyglotPassiveVoicePresentSimpleAllLevelsLessonSeeder/definition.json',
            'database/seeders/V3/PassiveVoice/PassiveVoicePastSimpleAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Polyglot/PolyglotPassiveVoicePastSimpleAllLevelsLessonSeeder/definition.json',
            'database/seeders/V3/PassiveVoice/PassiveVoiceFutureSimpleAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Polyglot/PolyglotPassiveVoiceFutureSimpleAllLevelsLessonSeeder/definition.json',
        ] as $path) {
            $d = json_decode(file_get_contents($this->root().'/'.$path), true, flags: JSON_THROW_ON_ERROR);
            foreach ($d['questions'] as $q) {
                $target = $q['target_text'] ?? $q['question'];
                foreach ($q['markers'] ?? [] as $marker => $data) { $target = str_replace('{'.$marker.'}', $data['answer'], $target); }
                foreach (array_merge([$target], $q['accepted_target_texts'] ?? []) as $accepted) {
                    $seen[$this->key($accepted)] = $path;
                }
            }
        }
        $canonical = [];
        foreach ($this->source()['questions'] as $r) {
            foreach (array_unique(array_merge([$r['target']], $r['accepted_targets'] ?? [])) as $target) {
                $key = $this->key($target);
                self::assertArrayNotHasKey($key, $seen, $r['id'].' duplicates '.$target);
                $seen[$key] = $r['id'];
            }
            $canonical[$r['id']] = array_unique(explode(' ', $this->key($r['target'])));
        }
        foreach ($canonical as $a => $words) {
            foreach ($canonical as $b => $other) {
                if (strcmp($a, $b) >= 0) { continue; }
                $score = count(array_intersect($words, $other)) / count(array_unique(array_merge($words, $other)));
                self::assertLessThan(0.82, $score, $a.' and '.$b.' near-duplicate');
            }
        }
    }
}
