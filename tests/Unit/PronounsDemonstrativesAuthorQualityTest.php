<?php

namespace Tests\Unit;

use App\Support\PronounsDemonstrativesPracticeQuality as Quality;
use PHPUnit\Framework\TestCase;

/** Source-only contracts; grammar and CEFR are additionally reviewed editorially. */
final class PronounsDemonstrativesAuthorQualityTest extends TestCase
{
    private function sources(): array
    {
        $result = [];
        foreach (array_keys(Quality::TOPICS) as $topic) { $result[$topic] = Quality::source($topic); }
        return $result;
    }

    private function key(string $text): string
    {
        return trim(preg_replace('/[^\pL\pN]+/u', ' ', mb_strtolower($text)));
    }

    private function clean(string $text, string $id): void
    {
        self::assertNotSame('', trim($text), $id);
        self::assertSame(trim($text), $text, $id);
        self::assertDoesNotMatchRegularExpression('/<[^>]*>|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{[^}]*\}|_{2,}/iu', $text, $id);
    }

    public function test_all_ten_topics_have_six_gaps_and_three_translations_on_every_level(): void
    {
        foreach ($this->sources() as $topic => $source) {
            self::assertCount(54, $source['questions'], $topic);
            self::assertCount(54, array_unique(array_column($source['questions'], 'id')), $topic);
            self::assertDoesNotMatchRegularExpression('/поліглот|polyglot/iu', $source['title']);
            foreach (Quality::LEVELS as $level) {
                self::assertNotSame('', trim($source['level_notes'][$level] ?? ''), $topic.'/'.$level);
                $rows = array_filter($source['questions'], fn ($row) => $row['level'] === $level);
                self::assertCount(9, $rows, $topic.'/'.$level);
                self::assertCount(6, array_filter($rows, fn ($row) => $row['kind'] === 'gap'));
                self::assertCount(3, array_filter($rows, fn ($row) => $row['kind'] === 'compose'));
            }
        }
    }

    public function test_manual_hints_are_contextual_ukrainian_and_groups_reconstruct_complete_sentences(): void
    {
        foreach ($this->sources() as $topic => $source) {
            foreach ($source['questions'] as $row) {
                $id = $topic.'/'.$row['id'];
                foreach (['hint_uk', 'uk'] as $field) {
                    $this->clean($row[$field], $id.'/'.$field);
                    self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $row[$field], $id);
                    self::assertDoesNotMatchRegularExpression('/[A-Za-z]/u', $row[$field], $id);
                }
                self::assertGreaterThanOrEqual(30, mb_strlen($row['hint_uk']), $id);
                $this->clean($row['target'], $id);
                self::assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $row['target'], $id);
                self::assertMatchesRegularExpression('/[.!?]$/u', $row['target'], $id);
                self::assertSame($row['target'], implode(' ', $row['groups']), $id);
                self::assertGreaterThanOrEqual(2, count($row['groups']), $id);
                foreach ($row['groups'] as $group) {
                    $this->clean($group, $id);
                    self::assertMatchesRegularExpression('/[\pL\pN]/u', $group, $id);
                    self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $group)), $id);
                }
                if ($row['kind'] === 'compose') {
                    self::assertSame($row['uk'], $row['question'], $id);
                    self::assertSame($row['target'], implode(' ', $row['compose_slots'] ?? preg_split('/\s+/u', $row['target'])), $id);
                }
            }
        }
    }

    public function test_gap_answers_short_choices_and_explicit_omission_semantics_agree(): void
    {
        $omissions = 0; $aliases = 0;
        foreach ($this->sources() as $topic => $source) {
            $compiled = array_column(Quality::compile($topic, 'standard')['questions'], null, 'author_id');
            foreach ($source['questions'] as $row) {
                $id = $topic.'/'.$row['id'];
                if ($row['kind'] !== 'gap') { continue; }
                self::assertSame(1, substr_count($row['question'], '{a1}'), $id);
                $this->clean(trim(str_replace('{a1}', '', $row['question'])), $id);
                $reconstructed = str_replace('{a1}', $row['answer'], $row['question']);
                if (($row['gap_semantics'] ?? null) === 'no_article') {
                    ++$omissions; self::assertSame('-', $row['answer'], $id);
                    $reconstructed = trim(preg_replace('/\s+/u', ' ', str_replace('{a1}', '', $row['question'])));
                    self::assertNotContains('-', $row['groups'], $id);
                } else { self::assertNotSame('-', $row['answer'], $id); }
                self::assertSame($row['target'], $reconstructed, $id);
                self::assertCount(3, $row['options'], $id);
                self::assertCount(3, array_unique($row['options']), $id);
                self::assertSame($row['answer'], $row['options'][0], $id);
                foreach ($row['options'] as $option) {
                    $this->clean($option, $id);
                    self::assertLessThanOrEqual(6, count(preg_split('/\s+/u', $option)), $id.'/compact-option');
                }
                foreach ($row['answer_synonyms'] ?? [] as $alias) {
                    ++$aliases; self::assertNotContains($alias, array_slice($row['options'], 1), $id);
                    self::assertContains(str_replace('{a1}', $alias, $row['question']), $compiled[$row['id']]['accepted_target_texts'], $id);
                }
            }
        }
        self::assertSame(0, $omissions);
        self::assertGreaterThan(0, $aliases, 'Natural lexical variants must also be accepted in gaps.');
    }

    public function test_targets_and_aliases_are_not_duplicated_across_levels_topics_or_prior_quality_packages(): void
    {
        $seen = []; $root = dirname(__DIR__, 2);
        foreach (['basic-grammar-quality', 'verb-to-be-quality', 'word-order-quality', 'nouns-articles-quantity-quality'] as $dir) {
            foreach (glob($root.'/docs/content/'.$dir.'/*.v1.json') as $file) {
                foreach (json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR)['questions'] ?? [] as $row) {
                    foreach (array_merge([$row['target']], $row['accepted_targets'] ?? []) as $target) { $seen[$this->key($target)] = $file.'/'.$row['id']; }
                }
            }
        }
        $count = 0;
        foreach ($this->sources() as $topic => $source) {
            foreach ($source['questions'] as $row) {
                ++$count; $owner = $topic.'/'.$row['id'];
                foreach (array_unique(array_merge([$row['target']], $row['accepted_targets'] ?? [])) as $target) {
                    $key = $this->key($target);
                    if (isset($seen[$key])) { self::assertSame($owner, $seen[$key], 'Duplicate: '.$target); }
                    $seen[$key] = $owner;
                }
            }
        }
        self::assertSame(540, $count);
    }
}
