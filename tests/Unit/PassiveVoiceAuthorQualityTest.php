<?php
declare(strict_types=1);
namespace Tests\Unit;

use App\Support\PassiveVoicePracticeQuality as Quality;
use App\Support\FinitePracticeQualityCatalog;
use PHPUnit\Framework\TestCase;

/** Pure canonical author/compiled-bank checks: no kernel, .env, DB or learner storage. */
final class PassiveVoiceAuthorQualityTest extends TestCase
{
    private function clean(string $text, string $id, bool $gap = false): void
    {
        self::assertNotSame('', trim($text), $id);
        self::assertSame(trim($text), $text, $id);
        if ($gap) { $text = str_replace('{a1}', '', $text); }
        self::assertDoesNotMatchRegularExpression('/<[^>]*>|&(?:#\d+|#x[\da-f]+|[a-z][a-z\d]+);|\{[^}]*\}|_{2,}/iu', $text, $id);
    }

    public function test_twenty_seven_topics_have_exactly_six_gaps_and_three_translations_per_level(): void
    {
        self::assertCount(27, Quality::TOPICS); $seen = [];
        foreach (array_keys(Quality::TOPICS) as $topic) {
            $source = Quality::source($topic); self::assertCount(54, $source['questions']);
            self::assertDoesNotMatchRegularExpression('/polyglot|поліглот/iu', $source['title']);
            foreach (Quality::LEVELS as $level) {
                $rows = array_filter($source['questions'], static fn ($r) => $r['level'] === $level);
                self::assertCount(9, $rows, $topic.'/'.$level);
                self::assertCount(6, array_filter($rows, static fn ($r) => $r['kind'] === 'gap'));
                self::assertCount(3, array_filter($rows, static fn ($r) => $r['kind'] === 'compose'));
            }
            foreach ($source['questions'] as $row) {
                $id = $topic.'/'.$row['id']; $uuid = Quality::questionUuid($topic, $row['id']);
                self::assertArrayNotHasKey($uuid, $seen); $seen[$uuid] = true;
                foreach (['question','target','uk','hint_uk'] as $field) { $this->clean($row[$field], $id, $field === 'question' && $row['kind'] === 'gap'); }
                foreach (['uk','hint_uk'] as $field) {
                    self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $row[$field], $id);
                    self::assertDoesNotMatchRegularExpression('/[A-Za-z]/u', $row[$field], $id);
                }
                self::assertGreaterThanOrEqual(12, mb_strlen($row['hint_uk']), $id);
                self::assertSame($row['target'], implode(' ', $row['groups']), $id);
                foreach ($row['groups'] as $group) { self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $group)), $id); }
                if ($row['kind'] === 'gap') {
                    self::assertSame(1, substr_count($row['question'], '{a1}'), $id);
                    self::assertSame($row['target'], str_replace('{a1}', $row['answer'], $row['question']), $id);
                    self::assertCount(3, $row['options']); self::assertCount(3, array_unique($row['options']));
                    self::assertContains($row['answer'], $row['options']);
                    foreach ($row['options'] as $option) { $this->clean($option, $id); self::assertLessThanOrEqual(6, count(preg_split('/\s+/u', $option)), $id); }
                    foreach ($row['answer_synonyms'] ?? [] as $alias) { self::assertNotContains($alias, array_slice($row['options'], 1), $id); }
                }
            }
        }
        self::assertCount(1458, $seen);
    }

    public function test_every_compose_slot_has_four_varied_short_reviewed_choices_and_reachable_aliases(): void
    {
        $sets = []; $synonyms = 0;
        foreach (array_keys(Quality::TOPICS) as $topic) {
            $source = Quality::source($topic); $compiled = array_column(Quality::compile($topic, 'builder')['questions'], null, 'author_id');
            foreach ($source['choice_pools'] as $pool) {
                self::assertGreaterThanOrEqual(6, count(array_unique($pool)));
                foreach ($pool as $value) { $this->clean($value, $topic); self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $value))); }
            }
            foreach ($source['questions'] as $row) {
                if ($row['kind'] !== 'compose') { continue; } $id = $topic.'/'.$row['id']; $q = $compiled[$row['id']];
                self::assertSame($row['target'], implode(' ', $row['compose_slots']));
                self::assertCount(count($row['compose_slots']), $row['slot_roles']); $variants = [''];
                // Authored groups still test the whitelist; visible fields are individual words.
                foreach ($q['markers'] as $markerKey => $compiledMarker) {
                    $options = $q['options_by_marker'][$markerKey];
                    self::assertCount(4, $options); self::assertCount(4, array_unique($options));
                    self::assertContains(\App\Support\FiniteBuilderWordSlots::clean($compiledMarker['answer']), $options);
                    $sets[json_encode($options)] = true;
                    foreach ($options as $option) { self::assertDoesNotMatchRegularExpression('/\\s/u', $option, $id); }
                }
                foreach ($row['compose_slots'] as $i => $slot) {
                    $next = [];
                    foreach ($variants as $prefix) foreach (array_merge([$slot], FinitePracticeQualityCatalog::displayAliasesForToken($slot, $row['display_synonyms'] ?? [])) as $v) { $next[] = trim($prefix.' '.$v); }
                    $variants = $next;
                }
                $accepted = array_unique(array_merge([$row['target']], $row['accepted_targets'] ?? [])); $synonyms += count($accepted) - 1;
                $variantKeys = array_map(Quality::normalized(...), $variants);
                $acceptedKeys = array_map(Quality::normalized(...), $accepted);
                if ($topic === 'present-perfect' && $row['id'] === 'A1-c03') {
                    // Explicit sentence paths preserve two-/three-word synonyms
                    // without putting an entire phrase into one word input.
                    self::assertCount(6, $accepted);
                    foreach ($accepted as $v) {
                        self::assertMatchesRegularExpression('/^The broken (?:mug|cup) has been replaced (?:at no cost|for free|free of charge)\.$/D', $v);
                    }
                } else {
                    foreach ($accepted as $v) { self::assertContains(Quality::normalized($v), $variantKeys, $id.' unreachable full-sentence alternative'); }
                }
                foreach ($variants as $v) { self::assertContains(Quality::normalized($v), $acceptedKeys, $id.' missing whole-sentence whitelist'); }
            }
        }
        self::assertGreaterThan(500, count($sets)); self::assertGreaterThan(0, $synonyms);
    }

    public function test_active_author_targets_have_no_exact_or_near_duplicates_between_levels_and_topics(): void
    {
        $seen = []; $words = [];
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (Quality::source($topic)['questions'] as $row) {
            $id = $topic.'/'.$row['id']; $words[$id] = array_unique(explode(' ', Quality::normalized($row['target'])));
            foreach (array_unique(array_merge([$row['target']], $row['accepted_targets'] ?? [])) as $text) {
                $key = Quality::normalized($text);
                if (isset($seen[$key])) { self::assertSame($id, $seen[$key], $id.' repeats '.$seen[$key].': '.$text); }
                $seen[$key] = $id;
            }
        }
        $ids = array_keys($words); $near = [];
        foreach ($ids as $i => $a) foreach (array_slice($ids, $i + 1) as $b) {
            $dice = 2 * count(array_intersect($words[$a], $words[$b])) / (count($words[$a]) + count($words[$b]));
            if ($dice >= 0.90) { $near[] = $a.' / '.$b; }
        }
        self::assertSame([], $near, 'Near-duplicate active sentences require a content review');
    }


    public function test_finite_authors_do_not_repeat_native_practice_or_frozen_widget_sentences(): void
    {
        $root=dirname(__DIR__,2); $reserved=[];
        foreach ([
            \App\Support\PassiveVoicePracticeSupplement::SOURCE=>\App\Support\PassiveVoicePracticeSupplement::SOURCE_SHA,
            \App\Support\PassiveVoiceWidgetHints::SOURCE=>\App\Support\PassiveVoiceWidgetHints::SOURCE_SHA,
        ] as $file=>$sha) { self::assertSame($sha,hash_file('sha256',$root.'/'.$file)); }
        $native=json_decode(file_get_contents($root.'/'.\App\Support\PassiveVoicePracticeSupplement::SOURCE),true,flags:JSON_THROW_ON_ERROR);
        foreach($native['targets'] as $target) foreach($target['after_data']['inputs'] as $index=>$row) {
            foreach(array_unique(array_merge([$row['answer']],$row['accepted']??[])) as $answer) {
                $text=($row['practice_kind']??'')==='gap'?str_replace('____',$answer,$row['before']):$answer;
                $text=preg_replace('/\s*\([^)]*[А-Яа-яІіЇїЄєҐґ][^)]*\)\s*$/u','',$text);
                $reserved[Quality::normalized($text)]=$target['topic'].'/'.($row['id']??'input-'.$index);
            }
        }
        $widgets=json_decode(file_get_contents($root.'/'.\App\Support\PassiveVoiceWidgetHints::SOURCE),true,flags:JSON_THROW_ON_ERROR);
        self::assertCount(135,$widgets['questions']);
        foreach($widgets['questions'] as $row) { $reserved[Quality::normalized(implode(' ',$row['correct_tokens']))]='widget/'.$row['id']; }
        self::assertGreaterThanOrEqual(250,count($reserved));
        $collisions=[]; $near=[];
        foreach(array_keys(Quality::TOPICS) as $topic) foreach(Quality::source($topic)['questions'] as $row) {
            $id=$topic.'/'.$row['id']; $texts=array_merge([$row['target']],$row['accepted_targets']??[]);
            if($row['kind']==='gap') foreach($row['answer_synonyms']??[] as $alias) { $texts[]=str_replace('{a1}',$alias,$row['question']); }
            foreach(array_unique($texts) as $text) {
                $key=Quality::normalized($text);
                if(isset($reserved[$key])) { $collisions[]=$id.' duplicates '.$reserved[$key].': '.$text; }
            }
            $words=array_unique(explode(' ',Quality::normalized($row['target'])));
            foreach($reserved as $key=>$owner) {
                $other=array_unique(explode(' ',$key));
                $dice=2*count(array_intersect($words,$other))/(count($words)+count($other));
                if($dice>=0.90 && Quality::normalized($row['target'])!==$key) { $near[]=$id.' resembles '.$owner; }
            }
        }
        self::assertSame([],$collisions,'Finite banks must not recycle the visible native/widget questions');
        self::assertSame([],$near,'Near-duplicate native/widget sentences require a content review');
    }

    public function test_generated_banks_and_loaders_match_the_authors_without_mutating_historical_sources(): void
    {
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (['standard','builder'] as $kind) {
            $map = Quality::mapping($topic)[$kind]; $old = Quality::originalDefinition($topic, $kind);
            self::assertCount(72, $old['questions']);
            $actual = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.$map['definition']), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame(Quality::compile($topic, $kind), $actual, $topic.'/'.$kind);
            self::assertCount($kind === 'standard' ? 36 : 18, $actual['questions']);
            self::assertFileExists(dirname(__DIR__, 2).'/'.$map['loader']); self::assertFileExists(dirname(__DIR__, 2).'/'.$map['implementation']);
        }
        self::assertCount(54, Quality::metadataPatches());
    }

    public function test_exact_content_revision_and_locale_restore_hints_without_touching_progress(): void
    {
        self::assertCount(1458, Quality::questions(), 'Every finite topic must have fresh compiled definitions');
        foreach (Quality::questions() as $source) {
            $payload = ['uuid'=>$source['uuid'],'question'=>$source['question'],'type'=>(string)$source['type'],
                'answer_map'=>array_map(static fn ($m) => $m['answer'], $source['markers']),
                'chosen'=>['kept'],'manualInputsBySlot'=>['draft'],'attemptsBySlot'=>[2],'done'=>false];
            $fresh = Quality::decorate($payload);
            self::assertSame($source['verb_hint'], $fresh['verb_hint']);
            self::assertSame(array_fill_keys(array_keys($source['markers']), $source['verb_hint']), $fresh['verb_hints']);
            self::assertSame($fresh['psp_content_revision'], $fresh['pvq_option_caption_revision']);
            foreach (['chosen','manualInputsBySlot','attemptsBySlot','done'] as $field) { self::assertSame($payload[$field], $fresh[$field]); }
            if ((string)$source['type'] === '0') { self::assertTrue($fresh['passive_gap_phrase_input']); self::assertTrue($fresh['passive_phrase_word_inputs']); }
            self::assertFalse(Quality::cachedNeedsRefresh([$fresh]));
            $stale = $fresh; $stale['verb_hint'] = 'stale'; self::assertTrue(Quality::cachedNeedsRefresh([$stale]));
            self::assertSame($source['verb_hint'], Quality::restoreClarifiedHints($stale, 'uk')['verb_hint']);
            self::assertSame($stale, Quality::restoreClarifiedHints($stale, 'en'));
            $foreign = $payload; $foreign['question'] .= ' changed'; self::assertSame($foreign, Quality::decorate($foreign));
        }
    }

    public function test_saved_metadata_and_foreign_locale_projection_preserve_old_questions_and_course_boundaries(): void
    {
        foreach (Quality::metadataPatches() as $uuid => $patch) {
            $old = Quality::originalDefinition($patch['topic'], $patch['kind']);
            $projected = Quality::definitionMetadata($old, 'uk');
            self::assertSame($old['questions'], $projected['questions']); self::assertSame($old, Quality::definitionMetadata($old, 'en'));
            self::assertSame($patch['after'], $projected['saved_test']);
            self::assertSame($patch['before']['filters'], Quality::filtersForLocale($uuid, $patch['after']['filters'], 'en'));
            self::assertNotNull(Quality::stateNamespace($patch['after']['filters'], 'uk', $uuid));
            self::assertSame($patch['kind'] === 'builder' ? 18 : null, Quality::savedBuilderDisplayCount($uuid, $patch['before']['slug'], $patch['after']['filters'], 'uk'));
            self::assertNull(Quality::savedBuilderDisplayCount($uuid, $patch['before']['slug'].'-foreign', $patch['after']['filters'], 'uk'));
            $changed = $old; $changed['questions'][0]['question'] .= ' changed'; self::assertSame($changed, Quality::definitionMetadata($changed, 'uk'));
        }
    }
}
