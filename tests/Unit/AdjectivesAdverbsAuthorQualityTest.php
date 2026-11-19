<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Support\AdjectivesAdverbsPracticeQuality as Quality;
use App\Support\AdjectivesAdverbsPracticeSupplement as Supplement;
use App\Support\FinitePracticeQualityCatalog;
use PHPUnit\Framework\TestCase;

/** Pure versioned-data checks; never loads .env, DB, HTTP or learner storage. */
final class AdjectivesAdverbsAuthorQualityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Finite native projection reads only locale; no kernel/providers/.env are booted.
        $app = new \Illuminate\Foundation\Application(dirname(__DIR__, 2));
        $app->instance('config', new \Illuminate\Config\Repository(['app' => ['locale' => 'uk']]));
    }

    private function clean(string $text, string $id, bool $gap = false): void
    {
        self::assertNotSame('', trim($text), $id); self::assertSame(trim($text), $text, $id);
        if ($gap) { $text = str_replace('{a1}', '', $text); }
        self::assertDoesNotMatchRegularExpression('/<[^>]*>|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{[^}]*\}|_{2,}/iu', $text, $id);
    }

    public function test_seven_topics_have_six_gaps_and_three_translations_at_every_level(): void
    {
        self::assertCount(7, Quality::TOPICS); $ids = [];
        foreach (array_keys(Quality::TOPICS) as $topic) {
            $source = Quality::source($topic); self::assertCount(54, $source['questions']);
            self::assertDoesNotMatchRegularExpression('/polyglot|поліглот/iu', $source['title']);
            foreach (Quality::LEVELS as $level) {
                $rows = array_filter($source['questions'], fn ($r) => $r['level'] === $level);
                self::assertCount(9, $rows, $topic.'/'.$level);
                self::assertCount(6, array_filter($rows, fn ($r) => $r['kind'] === 'gap'));
                self::assertCount(3, array_filter($rows, fn ($r) => $r['kind'] === 'compose'));
            }
            foreach ($source['questions'] as $row) {
                $uuid = Quality::questionUuid($topic, $row['id']); self::assertArrayNotHasKey($uuid, $ids); $ids[$uuid] = true;
            }
        }
        self::assertCount(378, $ids);
    }

    public function test_clean_ukrainian_hints_and_logical_short_groups_preserve_all_words(): void
    {
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (Quality::source($topic)['questions'] as $row) {
            $id = $topic.'/'.$row['id'];
            foreach (['question', 'target', 'uk', 'hint_uk'] as $field) { $this->clean($row[$field], $id.'/'.$field, $field === 'question' && $row['kind'] === 'gap'); }
            foreach (['uk', 'hint_uk'] as $field) {
                self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $row[$field], $id);
                self::assertDoesNotMatchRegularExpression('/[A-Za-z]/u', $row[$field], $id);
            }
            self::assertGreaterThanOrEqual(35, mb_strlen($row['hint_uk']), $id);
            self::assertSame($row['target'], implode(' ', $row['groups']), $id);
            foreach ($row['groups'] as $group) { self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $group)), $id); }
            if ($row['kind'] === 'compose') { self::assertSame($row['uk'], $row['question'], $id); }
        }
    }

    public function test_gap_phrases_are_complete_short_and_lexical_alternatives_are_explicit(): void
    {
        $aliases = 0; $phrases = 0;
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (Quality::source($topic)['questions'] as $row) {
            if ($row['kind'] !== 'gap') { continue; } $id = $topic.'/'.$row['id'];
            self::assertSame(1, substr_count($row['question'], '{a1}'), $id);
            self::assertSame($row['target'], str_replace('{a1}', $row['answer'], $row['question']), $id);
            self::assertCount(3, $row['options']); self::assertCount(3, array_unique($row['options'])); self::assertContains($row['answer'], $row['options']);
            $phrases += str_contains($row['answer'], ' ') ? 1 : 0;
            foreach ($row['options'] as $option) {
                $this->clean($option, $id); self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $option)), $id);
            }
            foreach ($row['answer_synonyms'] ?? [] as $alias) {
                ++$aliases; $this->clean($alias, $id); self::assertLessThanOrEqual(5, count(preg_split('/\s+/u', $alias)), $id);
                self::assertNotContains($alias, array_slice($row['options'], 1), $id);
            }
        }
        self::assertGreaterThan(100, $aliases); self::assertGreaterThan(20, $phrases);
    }

    public function test_every_compose_slot_has_varied_role_choices_and_reachable_displayed_aliases(): void
    {
        $choiceSets = []; $aliases = 0;
        foreach (array_keys(Quality::TOPICS) as $topic) {
            $source = Quality::source($topic); $compiled = array_column(Quality::compile($topic, 'builder')['questions'], null, 'author_id');
            foreach ($source['choice_pools'] as $pool) {
                self::assertGreaterThanOrEqual(6, count(array_unique($pool)));
                foreach ($pool as $choice) { $this->clean($choice, $topic); self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $choice))); }
            }
            foreach ($source['questions'] as $row) {
                if ($row['kind'] !== 'compose') { continue; } $id = $topic.'/'.$row['id']; $q = $compiled[$row['id']];
                self::assertSame($row['target'], implode(' ', $row['compose_slots']), $id);
                self::assertCount(count($row['compose_slots']), $row['slot_roles']); $variants = [''];
                // Authored groups still test the whitelist; visible fields are individual words.
                foreach ($q['markers'] as $markerKey => $compiledMarker) {
                    $options = $q['options_by_marker'][$markerKey];
                    self::assertCount(4, $options); self::assertCount(4, array_unique($options));
                    self::assertContains(\App\Support\FiniteBuilderWordSlots::clean($compiledMarker['answer']), $options);
                    $choiceSets[json_encode($options)] = true;
                    foreach ($options as $option) { self::assertDoesNotMatchRegularExpression('/\\s/u', $option, $id); }
                }
                foreach ($row['compose_slots'] as $i => $slot) {
                    self::assertArrayHasKey($row['slot_roles'][$i], $source['choice_pools'], $id);
                    $next = [];
                    foreach ($variants as $prefix) foreach (array_merge([$slot], FinitePracticeQualityCatalog::displayAliasesForToken($slot, $row['display_synonyms'] ?? [])) as $v) { $next[] = trim($prefix.' '.$v); }
                    $variants = $next;
                }
                $accepted = array_unique(array_merge([$row['target']], $row['accepted_targets'] ?? [])); $aliases += count($accepted) - 1;
                foreach ($accepted as $v) { self::assertContains($v, $variants, $id.' unreachable alias'); }
                foreach ($variants as $v) { self::assertContains($v, $accepted, $id.' missing full-sentence whitelist'); }
            }
        }
        self::assertGreaterThan(100, count($choiceSets)); self::assertGreaterThan(70, $aliases);
    }

    public function test_active_finite_targets_are_unique_and_do_not_repeat_native_or_lower_widget_targets(): void
    {
        $reserved = [];
        foreach (Supplement::retainedFormMeaningTargets() as $id => $targets) foreach ($targets as $target) { $reserved[Quality::normalized($target)] = $id; }
        foreach (Supplement::load()['targets'] as $target) foreach ($target['after_data']['inputs'] as $item) {
            $sentence = ($item['practice_kind'] ?? '') === 'gap' ? str_replace('____', $item['answer'], $item['before']) : $item['answer'];
            $reserved[Quality::normalized($sentence)] = $item['id'];
        }
        foreach (Supplement::loadWidgets()['questions'] as $row) { $reserved[Quality::normalized(implode(' ', $row['correct_tokens']))] = 'widget-'.$row['id']; }
        self::assertGreaterThanOrEqual(100, count($reserved)); $seen = []; $canonicalWords = [];
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (Quality::source($topic)['questions'] as $row) {
            $id = $topic.'/'.$row['id'];
            $canonicalWords[$id] = array_unique(explode(' ', Quality::normalized($row['target'])));
            foreach (array_unique(array_merge([$row['target']], $row['accepted_targets'] ?? [])) as $text) {
                $key = Quality::normalized($text); self::assertArrayNotHasKey($key, $reserved, $id.' repeats active practice '.$text);
                self::assertArrayNotHasKey($key, $seen, $id.' duplicates '.$text); $seen[$key] = $id;
            }
        }
        // Historical reuse is intentional: exact old data stays frozen but is not an active UK finite bank.
        self::assertGreaterThan(378, count($seen));
        foreach ($canonicalWords as $a => $words) foreach ($canonicalWords as $b => $other) {
            if (strcmp($a, $b) >= 0) { continue; }
            $similarity = count(array_intersect($words, $other)) / count(array_unique(array_merge($words, $other)));
            self::assertLessThan(0.90, $similarity, $a.' and '.$b.' are near-duplicate active sentences');
        }
    }

    public function test_generated_definitions_match_canonical_authors_and_originals_remain_byte_pinned(): void
    {
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (['standard', 'builder'] as $kind) {
            $map = Quality::mapping($topic)[$kind]; $old = Quality::originalDefinition($topic, $kind);
            self::assertCount(72, $old['questions']);
            $actual = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.$map['definition']), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame(Quality::compile($topic, $kind), $actual, $topic.'/'.$kind);
            self::assertCount($kind === 'standard' ? 36 : 18, $actual['questions']);
            self::assertFileExists(dirname(__DIR__, 2).'/'.$map['loader']); self::assertFileExists(dirname(__DIR__, 2).'/'.$map['implementation']);
        }
        self::assertCount(14, Quality::metadataPatches());
    }

    public function test_hint_restoration_is_exact_revision_and_locale_bound_and_preserves_progress(): void
    {
        foreach (Quality::questions() as $source) {
            $payload = ['uuid' => $source['uuid'], 'question' => $source['question'], 'type' => (string) $source['type'],
                'answer_map' => array_map(fn ($m) => $m['answer'], $source['markers']), 'chosen' => ['kept'],
                'manualInputsBySlot' => ['draft'], 'attemptsBySlot' => [2], 'done' => false];
            $fresh = Quality::decorate($payload); self::assertSame($source['verb_hint'], $fresh['verb_hint']);
            self::assertSame(array_fill_keys(array_keys($source['markers']), $source['verb_hint']), $fresh['verb_hints']);
            foreach (['chosen', 'manualInputsBySlot', 'attemptsBySlot', 'done'] as $key) { self::assertSame($payload[$key], $fresh[$key]); }
            if ((string) $source['type'] === '0') { self::assertTrue($fresh['passive_gap_phrase_input']); self::assertTrue($fresh['passive_phrase_word_inputs']); }
            $stale = $fresh; $stale['verb_hint'] = 'lost'; $stale['verb_hints'] = [];
            self::assertTrue(Quality::cachedNeedsRefresh([$stale]));
            self::assertSame($source['verb_hint'], Quality::restoreClarifiedHints($stale, 'uk')['verb_hint']);
            self::assertSame($stale, Quality::restoreClarifiedHints($stale, 'en'));
            $foreign = $stale; $foreign['question'] .= ' changed'; self::assertSame($foreign, Quality::restoreClarifiedHints($foreign, 'uk'));
        }
        $foreign = ['uuid' => 'qnq-wh-a1-g01', 'verb_hint' => 'keep']; self::assertSame($foreign, Quality::restoreClarifiedHints($foreign, 'uk'));
    }

    public function test_saved_metadata_locale_projection_and_builder_display_counts_are_exact_owned_only(): void
    {
        foreach (Quality::metadataPatches() as $uuid => $patch) {
            $old = Quality::originalDefinition($patch['topic'], $patch['kind']);
            $projected = Quality::definitionMetadata($old, 'uk');
            self::assertSame($old['questions'], $projected['questions']);
            self::assertSame($old, Quality::definitionMetadata($old, 'en'));
            self::assertSame($patch['after'], $projected['saved_test']);
            self::assertSame($patch['before']['filters'], Quality::filtersForLocale($uuid, $patch['after']['filters'], 'en'));
            self::assertNotNull(Quality::stateNamespace($patch['after']['filters'], 'uk', $uuid));
            $count = Quality::savedBuilderDisplayCount($uuid, $patch['before']['slug'], $patch['after']['filters'], 'uk');
            self::assertSame($patch['kind'] === 'builder' ? 18 : null, $count);
            self::assertNull(Quality::savedBuilderDisplayCount($uuid, $patch['before']['slug'].'-foreign', $patch['after']['filters'], 'uk'));
            self::assertNull(Quality::savedBuilderDisplayCount($uuid, $patch['before']['slug'], $patch['after']['filters'], 'en'));
            $changed = $old; $changed['questions'][0]['question'] .= ' changed'; self::assertSame($changed, Quality::definitionMetadata($changed, 'uk'));
        }
        self::assertNull(Quality::savedBuilderDisplayCount('polyglot-comparatives-a1-saved-test', 'comparatives', [], 'uk'));
        foreach (['GrammarTestController', 'TestJsV2Controller'] as $controller) {
            $text = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/'.$controller.'.php');
            self::assertStringContainsString('AdjectivesAdverbsPracticeQuality::restoreClarifiedHints', $text);
            self::assertStringContainsString('QuestionsNegationsPracticeQuality::restoreClarifiedHints', $text);
        }
    }

    public function test_caption_opt_in_is_exact_source_bound_and_never_changes_canonical_data(): void
    {
        foreach (Quality::questions() as $source) {
            $before = $source;
            $payload = ['uuid' => $source['uuid'], 'question' => $source['question'], 'type' => (string) $source['type'],
                'answer_map' => array_map(fn ($marker) => $marker['answer'], $source['markers'])];
            $decorated = Quality::decorate($payload);
            self::assertSame($decorated['psp_content_revision'], $decorated['aaq_option_caption_revision']);
            self::assertSame($payload['answer_map'], $decorated['answer_map']);
            self::assertSame($source['accepted_target_texts'], (string) $source['type'] === '4'
                ? $decorated['compose_accepted_sentences'] : $decorated['reorder_accepted_answers']);
            self::assertFalse(Quality::cachedNeedsRefresh([$decorated]));
            unset($decorated['aaq_option_caption_revision']); self::assertTrue(Quality::cachedNeedsRefresh([$decorated]));
            foreach (['question', 'uuid', 'type'] as $field) {
                $foreign = $payload; $foreign[$field] .= '-changed';
                self::assertSame($foreign, Quality::decorate($foreign));
            }
            self::assertSame($before, Quality::questions()[$source['uuid']]);
        }
    }
}
