<?php

namespace Tests\Unit;

use App\Support\AcceptedAnswerVariants;
use App\Support\ComposeTokenCase;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2).'/scripts/lib/ppc_quality_builder.php';

class PastPerfectContinuousBuilderQualityTest extends TestCase
{
    private function banks(): array
    {
        $result = [];
        foreach (ppcQualityBuilderBanks() as $bank => $rows) {
            $name = $bank === 'BasicsB2'
                ? 'PolyglotPastPerfectContinuousBasicsB2LessonSeeder'
                : 'PolyglotPastPerfectContinuous'.$bank.'AllLevelsLessonSeeder';
            $path = dirname(__DIR__, 2).'/database/seeders/V3/Polyglot/'.$name.'/definition.json';
            $result[$bank] = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        }
        return $result;
    }

    private function all(): array
    {
        return array_merge(...array_column($this->banks(), 'questions'));
    }

    private function normalize(string $text): string
    {
        return trim(mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text)));
    }

    public function test_every_authored_slot_is_projected_and_remains_in_its_original_scope(): void
    {
        $author = ppcQualityBuilderBanks();
        $total = 0;
        foreach ($this->banks() as $bank => $data) {
            $expectedCount = $bank === 'BasicsB2' ? 48 : 72;
            $this->assertCount($expectedCount, $data['questions'], $bank);
            $positions = [];
            $focuses = [];
            foreach ($data['questions'] as $question) {
                $level = $question['level'];
                $index = $positions[$level] ?? 0;
                $row = $author[$bank][$level][$index];
                $uuid = $question['uuid'];
                $expectedUuid = $bank === 'BasicsB2'
                    ? sprintf('polyglot-past-perfect-continuous-basics-b2-q%02d', $index + 1)
                    : sprintf('pastpc-%s-poly-%s-%02d', strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $bank)), strtolower($level), $index + 1);
                $this->assertSame($expectedUuid, $uuid);
                $this->assertSame($question, ppcQualityBuilderProjection($bank, $question, $row), $uuid);
                $this->assertSame(4, $question['type'], $uuid);
                $this->assertSame([], $question['variants'], 'Variants are presentation alternatives, not accepted sentences: '.$uuid);
                $this->assertNotEmpty($question['source'], $uuid);
                $this->assertNotEmpty($question['tag_keys'], $uuid);
                $this->assertSame($row['focus'], $question['quality_review']['learning_focus']);
                $this->assertNotEmpty($question['quality_review']['level_rationale']);
                $this->assertNotEmpty($question['quality_review']['near_duplicate_decision']);
                $focuses[$level][] = $row['focus'];
                $positions[$level] = $index + 1;
                $total++;
            }
            foreach ($author[$bank] as $level => $rows) {
                $this->assertSame(count($rows), $positions[$level]);
                $this->assertCount(count($rows), array_unique($focuses[$level]), 'Learning operations repeat within '.$bank.'/'.$level);
            }
        }
        $this->assertSame(336, $total);
    }

    public function test_all_336_prompts_are_localized_without_leaking_a_completed_answer(): void
    {
        foreach ($this->all() as $question) {
            $uuid = $question['uuid'];
            $this->assertSame($question['source_text_uk'], $question['question'], $uuid);
            $this->assertSame($question['question'], $question['localizations']['uk']['source_text'], $uuid);
            $this->assertSame($question['hint_uk'], $question['hints'][0], $uuid);
            $this->assertMatchesRegularExpression('/[ІіЇїЄєҐґА-Яа-я]/u', $question['question'], $uuid);
            $withoutGrammarNames = str_replace(['Past Perfect Continuous', 'Past Perfect Simple'], '', $question['question']);
            $this->assertDoesNotMatchRegularExpression('/[a-z]/i', $withoutGrammarNames, 'Mixed-language source: '.$uuid);
            $this->assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $question['target_text'], $uuid);
            $this->assertSame(str_ends_with($question['target_text'], '?'), str_ends_with($question['question'], '?'), $uuid);
            $completed = $this->normalize($question['target_text']);
            foreach (['uk', 'en', 'pl'] as $locale) {
                $payload = $question['localizations'][$locale];
                $this->assertNotEmpty($payload['source_text'], $uuid.'/'.$locale);
                $this->assertNotEmpty($payload['hints'], $uuid.'/'.$locale);
                $this->assertStringNotContainsString($completed, $this->normalize($payload['source_text']), 'Prompt exposes target: '.$uuid.'/'.$locale);
                $this->assertStringNotContainsString($completed, $this->normalize(implode(' ', $payload['hints'])), 'Hint exposes target: '.$uuid.'/'.$locale);
            }
            $this->assertDoesNotMatchRegularExpression('/[ІЇЄҐієїґ]/u', $question['localizations']['pl']['source_text'], $uuid.'/pl');
            $this->assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $question['localizations']['en']['source_text'], $uuid.'/en');
        }
    }

    public function test_every_target_can_be_built_with_exact_token_multiplicities(): void
    {
        $repeatedTokenQuestions = 0;
        foreach ($this->all() as $question) {
            $uuid = $question['uuid'];
            $this->assertSame(array_values($question['answers']), $question['tokens_correct'], $uuid);
            $this->assertSame($question['tokens_correct'], ComposeTokenCase::normalize($question['tokens_correct']), $uuid);
            $this->assertSame(rtrim($question['target_text'], '.?'), implode(' ', $question['tokens_correct']), $uuid);
            $this->assertSame($question['tokens_correct'], array_slice($question['options'], 0, count($question['tokens_correct'])), $uuid);
            $available = array_count_values($question['options']);
            foreach (array_count_values($question['tokens_correct']) as $token => $count) {
                $this->assertGreaterThanOrEqual($count, $available[$token] ?? 0, $uuid.' / '.$token);
                if ($count > 1) {
                    $repeatedTokenQuestions++;
                }
            }
            foreach ($question['options'] as $token) {
                $this->assertDoesNotMatchRegularExpression('/\s/u', $token, $uuid.' / '.$token);
            }
            $correct = array_map('mb_strtolower', $question['tokens_correct']);
            foreach ($question['distractors'] as $token) {
                $this->assertNotContains(mb_strtolower($token), $correct, 'Correct token marked as distractor: '.$uuid);
                $this->assertContains($token, $question['options'], $uuid);
            }
            foreach ($question['compose_accepted_token_variants'] as $token) {
                $this->assertContains($token, $question['options'], $uuid);
                $this->assertNotContains($token, $question['distractors'], 'Legal contraction marked wrong: '.$uuid);
            }
        }
        $this->assertGreaterThan(100, $repeatedTokenQuestions, 'The full repeated-token bank was not covered.');
    }

    public function test_no_exact_or_normalized_author_duplicates_remain_in_any_builder_bank(): void
    {
        $questions = $this->all();
        foreach (['question', 'target_text'] as $field) {
            $exact = array_column($questions, $field);
            $normal = array_map(fn ($text) => $this->normalize($text), $exact);
            $this->assertCount(336, array_unique($exact), $field);
            $this->assertCount(336, array_unique($normal), $field);
        }
        $families = [];
        foreach (ppcQualityBuilderBanks() as $bank => $levels) {
            if ($bank === 'BasicsB2') {
                continue;
            }
            for ($index = 0; $index < 12; $index++) {
                $rows = array_map(fn ($rows) => $rows[$index], $levels);
                $focuses = array_column($rows, 'focus');
                $this->assertGreaterThanOrEqual(4, count(array_unique($focuses)), 'Legacy cosmetic family '.$bank.'/'.($index + 1));
                $families[] = $bank.'/'.($index + 1);
            }
        }
        $this->assertCount(48, $families);
    }

    public function test_existing_matcher_accepts_contractions_but_rejects_missing_grammar_and_role_changes(): void
    {
        $checkedProcesses = 0;
        foreach ($this->all() as $question) {
            $expected = $question['target_text'];
            $uuid = $question['uuid'];
            $this->assertTrue(AcceptedAnswerVariants::matches($expected, $expected), $uuid);
            foreach (AcceptedAnswerVariants::for($expected) as $variant) {
                $this->assertTrue(AcceptedAnswerVariants::matches($expected, $variant), $uuid.' / '.$variant);
            }
            if (preg_match('/\bbeen [a-z]+ing\b/', $expected)) {
                $withoutBeen = preg_replace('/\bbeen\s+/', '', $expected, 1);
                $this->assertFalse(AcceptedAnswerVariants::matches($expected, $withoutBeen), $uuid.' / missing been');
                $checkedProcesses++;
            }
            if (preg_match('/\bnot\b/', $expected)) {
                $withoutNot = preg_replace('/\s+\bnot\b/', '', $expected, 1);
                $this->assertFalse(AcceptedAnswerVariants::matches($expected, $withoutNot), $uuid.' / missing not');
            }
        }
        $this->assertGreaterThan(300, $checkedProcesses);
        $state = 'Anna had known Lev for five years by then.';
        $this->assertTrue(AcceptedAnswerVariants::matches($state, $state));
        $this->assertFalse(AcceptedAnswerVariants::matches($state, 'Lev had known Anna for five years by then.'));
        $this->assertFalse(AcceptedAnswerVariants::matches($state, 'Anna had been knowing Lev for five years by then.'));
        $this->assertTrue(AcceptedAnswerVariants::matches('Had the team not been preparing?', "Hadn't the team been preparing?"));
        $this->assertTrue(AcceptedAnswerVariants::matches('No, they had not.', "No, they hadn't."));
        $this->assertFalse(AcceptedAnswerVariants::matches('Yes, I had.', "Yes, I'd."));
    }

    public function test_higher_level_short_answers_require_inference_not_a_ready_made_polarity_directive(): void
    {
        $questions = $this->banks()['Questions']['questions'];
        $checked = 0;
        foreach ($questions as $question) {
            if (!str_starts_with($question['quality_review']['learning_focus'], 'short-')) {
                continue;
            }
            if (in_array($question['level'], ['A1', 'A2'], true)) {
                continue;
            }
            $this->assertDoesNotMatchRegularExpression('/(ствердну|заперечну) відповідь/u', $question['question'], $question['uuid']);
            $this->assertStringContainsString('. ', $question['question'], 'Visible evidence is missing: '.$question['uuid']);
            $this->assertDoesNotMatchRegularExpression('/Answer (affirmatively|negatively)/', $question['localizations']['en']['source_text']);
            $this->assertDoesNotMatchRegularExpression('/Odpowiedz krótko (twierdząco|przecząco)/u', $question['localizations']['pl']['source_text']);
            $checked++;
        }
        $this->assertSame(6, $checked);
        $byUuid = array_column($questions, null, 'uuid');
        $this->assertStringContainsString('дві гіпотези', $byUuid['pastpc-questions-poly-c1-11']['question']);
        $this->assertStringContainsString('одинадцяти', $byUuid['pastpc-questions-poly-c2-11']['question']);
    }

    public function test_state_contrasts_do_not_visibly_demand_continuous_forms(): void
    {
        $checked = 0;
        foreach ($this->banks()['Forms']['questions'] as $question) {
            if (!in_array($question['quality_review']['learning_focus'], ['state-contrast', 'desire-state', 'ownership-state', 'belief-state'], true)) {
                continue;
            }
            $this->assertStringContainsString('Past Perfect Simple', $question['question'], $question['uuid']);
            $this->assertStringNotContainsString('Past Perfect Continuous', $question['question'], $question['uuid']);
            $this->assertStringNotContainsString('had been', $question['target_text'], $question['uuid']);
            $this->assertStringContainsString('Лексична основа:', $question['hint_uk']);
            $this->assertStringContainsString('past perfect simple', $question['localizations']['en']['source_text']);
            $this->assertStringContainsString('Past Perfect Simple', $question['localizations']['pl']['source_text']);
            $checked++;
        }
        $this->assertSame(4, $checked);
    }

    public function test_basics_companion_localizations_cannot_restore_legacy_prompts_or_hints(): void
    {
        $base = $this->banks()['BasicsB2']['questions'];
        foreach (['uk', 'en', 'pl'] as $locale) {
            $path = dirname(__DIR__, 2).'/database/seeders/V3/Polyglot/PolyglotPastPerfectContinuousBasicsB2LessonSeeder/localizations/'.$locale.'.json';
            $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($locale, $data['locale']);
            $this->assertCount(48, $data['questions']);
            $this->assertSame('polyglot-v3', $data['hint_provider']);
            foreach ($data['questions'] as $index => $localized) {
                $question = $base[$index];
                $this->assertSame($question['uuid'], $localized['uuid']);
                $this->assertSame($question['id'], $localized['id']);
                $this->assertSame($question['localizations'][$locale]['source_text'], $localized['source_text']);
                $this->assertSame($question['localizations'][$locale]['hints'], $localized['hints']);
                $this->assertSame('polyglot-v3', $question['localizations'][$locale]['hint_provider']);
                $this->assertSame([], $localized['explanations']);
            }
        }
    }
}
