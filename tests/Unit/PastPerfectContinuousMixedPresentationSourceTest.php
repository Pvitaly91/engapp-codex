<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PastPerfectContinuousMixedPresentationSourceTest extends TestCase
{
    private function inventory(): array
    {
        return json_decode(file_get_contents(dirname(__DIR__, 2).'/docs/reports/past-perfect-continuous-quality-inventory.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    private function rows(array $inventory): array
    {
        require_once dirname(__DIR__, 2).'/scripts/lib/ppc_quality_mixed_display.php';
        return ppcQualityMixedPresentationRows(dirname(__DIR__, 2), $inventory);
    }

    public function test_exact_288_rows_preserve_all_canonical_sources_and_inventory_data(): void
    {
        $inventory = $this->inventory(); $before = serialize($inventory);
        $rows = $this->rows($inventory);
        $this->assertCount(288, $rows);
        $this->assertCount(288, array_unique(array_column($rows, 'persistent_uuid')));
        $canonical = array_column($inventory['questions'], null, 'persistent_uuid');
        foreach ($rows as $row) {
            $record = $canonical[$row['persistent_uuid']];
            $this->assertSame($record['seeder_class'], $row['seeder_class']);
            $this->assertSame($record['editorial_uuid'], $row['editorial_uuid']);
            $this->assertSame(['uk', 'en', 'pl'], array_keys($row['locales']));
            foreach ($row['locales'] as $locale => $presentation) {
                $this->assertSame($record['source_condition'][$locale], $presentation['expected_source']);
                $this->assertNotSame('', trim($presentation['display_source']));
                $this->assertNotSame('', trim($presentation['instructions']));
            }
        }
        $this->assertSame($before, serialize($inventory));
    }

    public function test_english_display_is_the_canonical_blank_stem_never_a_completed_answer(): void
    {
        $inventory = $this->inventory(); $canonical = array_column($inventory['questions'], null, 'persistent_uuid');
        foreach ($this->rows($inventory) as $row) {
            $record = $canonical[$row['persistent_uuid']];
            $stem = preg_replace('/\{a\d+\}/', '____', $record['question_template']);
            $this->assertSame($stem, $row['locales']['en']['display_source']);
            $this->assertStringContainsString('____', $stem);
            $this->assertNotSame($record['completed_target'], $stem);
            $this->assertStringNotContainsString('Reconstruct the sentence', $stem);
            foreach (['uk', 'pl'] as $locale) {
                $this->assertStringContainsString($stem, $row['locales'][$locale]['instructions']);
            }
        }
    }

    public function test_only_fourteen_explicit_grammar_asides_move_for_uk_and_pl(): void
    {
        $rows = $this->rows($this->inventory()); $moves = ppcQualityMixedPresentationMoves();
        $this->assertCount(14, $moves); $changed = 0;
        foreach ($rows as $row) {
            foreach (['uk', 'pl'] as $locale) {
                $presentation = $row['locales'][$locale];
                if (isset($moves[$row['editorial_uuid']][$locale])) {
                    $changed++;
                    $this->assertNotSame($presentation['expected_source'], $presentation['display_source']);
                    $this->assertStringContainsString($moves[$row['editorial_uuid']][$locale][2], $presentation['instructions']);
                } else {
                    $this->assertSame($presentation['expected_source'], $presentation['display_source']);
                }
            }
        }
        $this->assertSame(28, $changed);
    }

    public function test_short_answer_facts_and_time_endpoint_context_remain_visible(): void
    {
        $rows = array_column($this->rows($this->inventory()), null, 'editorial_uuid');
        $shortAnswers = 0;
        foreach ($rows as $uuid => $row) {
            if (preg_match('/^pastpc-questions-v3-(?:a[12]|b[12]|c[12])-(?:06|11)$/D', $uuid) !== 1) { continue; }
            $shortAnswers++;
            foreach (['uk', 'pl'] as $locale) {
                $this->assertSame($row['locales'][$locale]['expected_source'], $row['locales'][$locale]['display_source']);
            }
        }
        $this->assertSame(12, $shortAnswers);
        foreach (['pastpc-time-expressions-v3-a1-04', 'pastpc-time-expressions-v3-b2-05', 'pastpc-time-expressions-v3-c2-05'] as $uuid) {
            foreach (['uk', 'pl'] as $locale) {
                $this->assertSame($rows[$uuid]['locales'][$locale]['expected_source'], $rows[$uuid]['locales'][$locale]['display_source']);
            }
        }
    }
}
