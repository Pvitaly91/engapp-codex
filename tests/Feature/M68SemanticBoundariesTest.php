<?php

namespace Tests\Feature;

use App\Support\AcceptedAnswerVariants;
use App\Support\M68ComparisonBasics as Package;
use Tests\TestCase;

/** Finite authored morphology/math/role fixtures, not a universal grammar analyser or browser run. */
class M68SemanticBoundariesTest extends TestCase
{
    private function lessons(): array { return Package::load()[2]['lessons']; }
    private function practice(array $lesson): array { return $lesson['blocks'][count($lesson['blocks']) - 1]['data']; }
    private function text(mixed $value): string
    {
        if (!is_array($value)) { return is_string($value) ? $value : ''; }
        return implode("\n", array_map(fn ($v) => $this->text($v), $value));
    }

    public function test_degrees_basic_distinguishes_morphology_adverbs_quantity_and_article_boundaries(): void
    {
        $lesson = $this->lessons()[0]; $forms = $this->text($lesson['blocks'][1]['data']);
        foreach (['не перенось це правило на всі двоскладові', 'happy → happier → happiest',
            'big → bigger → biggest', 'new → newer → newest', 'slow → slower → slowest',
            'large → larger → largest'] as $phrase) { self::assertStringContainsString($phrase, $forms); }
        $long = $this->text($lesson['blocks'][2]['data']);
        foreach (['careful → more careful → most careful', 'early → earlier → earliest',
            'cleverer / more clever', 'shallower / more shallow', 'Narrower і more narrow',
            'She works more carefully now.', 'He works harder now.'] as $phrase) { self::assertStringContainsString($phrase, $long); }
        $irregular = $this->text($lesson['blocks'][3]['data']);
        foreach (['good → better → best', 'well → better → best', 'bad → worse → worst',
            'badly → worse → worst', 'farther / further', 'fewer books', 'less water',
            'не розміру', 'further information'] as $phrase) { self::assertStringContainsString($phrase, $irregular); }
        $patterns = $this->text($lesson['blocks'][5]['data']);
        foreach (['Not bigger не означає обов’язково smaller', 'my best result', 'Olia ran fastest.',
            'the не завжди потрібне', 'Is this box bigger than that one?'] as $phrase) { self::assertStringContainsString($phrase, $patterns); }
        $errors = $lesson['blocks'][6]['data']['items'];
        self::assertSame('My car is more reliable than yours.', $errors[2]['right_en']);
        self::assertStringContainsString('Your потребує наступного іменника', $errors[2]['note']);
        self::assertSame([], $lesson['details'], 'Essential short boundaries stay in visible basic; no detail quota.');
    }

    public function test_comparative_data_supports_pairwise_all_others_and_group_without_two_vs_three_shortcut(): void
    {
        $lesson = $this->lessons()[1]; $core = $lesson['blocks'][1]['data']; $text = $this->text($lesson);
        foreach (['A — 2 кг', 'B — 5 кг', 'C — 8 кг'] as $fact) { self::assertStringContainsString($fact, $core['intro']); }
        $mass = ['A' => 2, 'B' => 5, 'C' => 8]; // Independent explicit fixture, kilograms throughout.
        self::assertGreaterThan($mass['B'], $mass['C']);
        self::assertGreaterThan(max($mass['A'], $mass['B']), $mass['C']);
        self::assertSame('C', array_search(max($mass), $mass, true));
        self::assertSame('A', array_search(min($mass), $mass, true));
        self::assertSame('Parcel C is heavier than any other parcel in this set.', $core['rows'][1]['en']);
        self::assertStringContainsString('Other виключає саму C', $core['rows'][1]['note']);
        foreach (['the taller of the two', 'the better of the two', 'comparative',
            'This is my best result.', 'This is one of the longest routes in the park.',
            'This exam was harder than the last one.', 'the cheaper option'] as $phrase) { self::assertStringContainsString($phrase, $text); }
        $checklist = $lesson['blocks'][8]['data'];
        self::assertArrayHasKey('sections', $checklist); self::assertArrayNotHasKey('items', $checklist);
        self::assertStringNotContainsString('"wrong', json_encode($checklist, JSON_THROW_ON_ERROR));
        self::assertCount(1, $lesson['details']);
        self::assertNotSame('comparative-checklist', $lesson['details'][0]['owner_block_key']);
    }

    public function test_own_numeric_choices_preserve_complete_data_ties_and_unknown_comparators(): void
    {
        $lessons = $this->lessons(); $degrees = $this->practice($lessons[0]); $comparative = $this->practice($lessons[1]);
        self::assertStringContainsString('A — 4 л', $degrees['choices'][1]['label']);
        self::assertStringContainsString('B — теж 4 л', $degrees['choices'][1]['label']);
        self::assertTrue(4 <= 4); self::assertFalse(4 < 4);
        self::assertSame('Так: однакова місткість не суперечить not bigger', $degrees['choices'][1]['answer']);
        $prices = ['A' => 60, 'B' => 90, 'C' => 120];
        foreach ($prices as $ticket => $price) { self::assertStringContainsString($ticket.' — '.$price.' грн', $comparative['select_intro']); }
        self::assertLessThan($prices['B'], $prices['A']); self::assertSame('A', array_search(min($prices), $prices, true));
        self::assertSame('cheaper', $comparative['selects'][0]['answer']);
        self::assertSame('the cheapest', $comparative['selects'][1]['answer']);
        $unknown = $comparative['choices'][0]; self::assertStringContainsString('A — 4 км, B — 6 км', $unknown['label']);
        self::assertStringContainsString('C невідома', $unknown['label']);
        self::assertLessThan(6, 4); self::assertFalse(4 < min(6, 2), 'A possible 2-km C disproves the unsupported whole-group inference.');
        self::assertSame('Лише А: відоме порівняння A з B', $unknown['answer']);
        $tie = $comparative['choices'][1]; self::assertStringContainsString('Nina — 9 балів, Iryna — 9, Maks — 6', $tie['label']);
        $scores = ['Nina' => 9, 'Iryna' => 9, 'Maks' => 6];
        self::assertSame(2, count(array_filter($scores, static fn ($n) => $n === max($scores))));
        self::assertFalse($scores['Nina'] > $scores['Iryna']);
        self::assertSame('А: Ніна поділяє найвищий результат', $tie['answer']);
    }

    public function test_equality_keeps_direction_countability_and_total_not_added_multipliers(): void
    {
        $lesson = $this->lessons()[2]; $practice = $this->practice($lesson); $text = $this->text($lesson);
        self::assertSame('carefully', $practice['selects'][0]['answer']); self::assertSame('much', $practice['selects'][1]['answer']);
        self::assertStringContainsString('учора було 2 °C, сьогодні 8 °C', $practice['choices'][0]['label']);
        self::assertGreaterThan(2, 8); self::assertStringStartsWith('Сьогодні тепліше', $practice['choices'][0]['answer']);
        self::assertStringContainsString('10 см', $practice['choices'][1]['label']);
        self::assertSame(30, 3 * 10); self::assertNotSame(30, 10 + 3 * 10);
        self::assertStringStartsWith('30 см:', $practice['choices'][1]['answer']);
        foreach (['синя — 10 см', 'червона — 20 см', 'зелена — 30 см', 'жовта — 5 см'] as $fact) { self::assertStringContainsString($fact, $text); }
        self::assertSame(20, 2 * 10); self::assertEquals(5, 10 / 2);
        self::assertSame('He is as tall as me.', $lesson['blocks'][9]['data']['items'][3]['right_en']);
        self::assertStringContainsString('Taller than me було б граматичним', $lesson['blocks'][9]['data']['items'][3]['note']);
        foreach (['Marta works as carefully as Olia.', 'Did Olia work as hard as Marta? — Yes, she did.',
            'as many as 500', 'as well as', 'Not quite as long', 'не точно однаково'] as $phrase) { self::assertStringContainsString($phrase, $text); }
    }

    public function test_six_input_keys_and_fifteen_accepted_values_are_finite_bounded_and_keep_token_multiplicity(): void
    {
        $expected = [
            'm68-degrees-build-1' => ['This exercise is easier than that one.', "This exercise's easier than that one."],
            'm68-degrees-build-2' => ['Is the museum farther than the bank?', 'Is the museum further than the bank?'],
            'm68-comparative-build-1' => ['The green suitcase is lighter than the blue one.', 'The green suitcase is lighter than the blue suitcase.',
                "The green suitcase's lighter than the blue one.", "The green suitcase's lighter than the blue suitcase."],
            'm68-comparative-build-2' => ['Is Route C the longest of the three routes?'],
            'm68-equality-build-1' => ['The new bag is not as expensive as the old bag.', "The new bag isn't as expensive as the old bag.", "The new bag's not as expensive as the old bag."],
            'm68-equality-build-2' => ['Marta has as many books as Danylo.', 'Marta has as many books as Danylo does.', 'Marta has as many books as Danylo has.'],
        ];
        $seen = []; $count = 0;
        foreach ($this->lessons() as $lesson) {
            foreach ($this->practice($lesson)['inputs'] as $task) {
                $seen[] = $task['id']; self::assertSame($expected[$task['id']], $task['accepted']);
                self::assertSame($expected[$task['id']][0], $task['answer']); $count += count($task['accepted']);
                foreach ($task['accepted'] as $accepted) { self::assertStringNotContainsString($accepted, $task['after']); }
                $tokens = array_map('trim', explode('/', $task['before']));
                self::assertNotEmpty($tokens); self::assertNotContains('', $tokens);
                if (str_contains($task['id'], 'equality')) {
                    self::assertSame(2, count(array_filter($tokens, static fn ($t) => $t === 'as')), 'Repeated as instances are distinct.');
                }
                if ($task['id'] === 'm68-comparative-build-2' || $task['id'] === 'm68-degrees-build-2') {
                    self::assertSame(2, count(array_filter($tokens, static fn ($t) => $t === 'the')));
                }
            }
        }
        self::assertSame(array_keys($expected), $seen); self::assertSame(15, $count);
    }

    public function test_every_own_select_choice_has_one_reviewed_key_without_shared_variant_collision(): void
    {
        $tasks = 0;
        foreach ($this->lessons() as $lesson) {
            foreach (['selects', 'choices'] as $group) {
                foreach ($this->practice($lesson)[$group] as $task) {
                    ++$tasks; self::assertContains($task['answer'], $task['options']);
                    self::assertSame([$task['answer']], $task['accepted']);
                    $correct = array_values(array_filter($task['options'],
                        static fn ($option) => AcceptedAnswerVariants::matches($task['answer'], $option)));
                    self::assertSame([$task['answer']], $correct, $task['id'].' exact finite shared-variants collision check; actual JS is separately exercised.');
                }
            }
        }
        self::assertSame(12, $tasks);
    }

    public function test_structured_english_examples_rows_and_corrections_have_their_own_ukrainian_fields(): void
    {
        $pairs = 0;
        $walk = function (array $value) use (&$walk, &$pairs): void {
            foreach (['en' => 'ua', 'wrong_en' => 'wrong_ua', 'right_en' => 'right_ua'] as $english => $ukrainian) {
                if (array_key_exists($english, $value)) {
                    ++$pairs; self::assertIsString($value[$english]); self::assertNotSame('', trim($value[$english]));
                    self::assertArrayHasKey($ukrainian, $value);
                    self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $value[$ukrainian]);
                }
            }
            foreach ($value as $child) { if (is_array($child)) { $walk($child); } }
        };
        foreach ($this->lessons() as $lesson) {
            $walk($lesson);
            foreach ($lesson['blocks'][0]['data']['rules'] as $rule) {
                self::assertMatchesRegularExpression('/[A-Za-z]/', strip_tags($rule['example']));
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', strip_tags($rule['example']));
            }
            self::assertDoesNotMatchRegularExpression('/✅|❌|font-mono/u', $this->text($lesson));
        }
        self::assertGreaterThan(50, $pairs, 'Actually traverse all three teaching lessons, not just six practice keys.');
    }
}
