<?php

namespace Tests\Feature;

use App\Support\AcceptedAnswerVariants;
use App\Support\M69AdjectivesAdverbs as Package;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Independent finite language expectations from M69; not a universal grammar analyser or live UI test. */
class M69SemanticBoundariesTest extends TestCase
{
    private function lessons(): array { return Package::load()[2]['lessons']; }
    private function practice(array $lesson): array { return $lesson['blocks'][count($lesson['blocks']) - 1]['data']; }
    private function text(mixed $value): string
    {
        if (!is_array($value)) { return is_string($value) ? $value : ''; }
        return implode("\n", array_map(fn ($child) => $this->text($child), $value));
    }

    public function test_adverbs_basic_covers_functions_spelling_lexical_meanings_position_and_full_clause_models(): void
    {
        $lesson = $this->lessons()[0]; $basic = $this->text(array_slice($lesson['blocks'], 0, 11));
        foreach (['a beautiful dress', 'She dances beautifully.', 'The task is quite easy.',
            'He speaks very clearly.', 'Fortunately, nobody was hurt.',
            'quick → quickly', 'careful → carefully', 'happy → happily', 'easy → easily',
            'simple → simply', 'gentle → gently', 'Friendly і lovely', 'earlyly',
            'It was an early train. We left early.', 'It is a daily service. The bus runs daily.',
            'She works hard. She hardly works.', 'I arrived late.', 'I have been busy lately.',
            'She is a good singer.', 'She sings well.', 'I feel well today.', 'I feel good today.',
            'You look tired.', 'You look carefully at the photo.', 'She carefully opened the box.',
            'She answered the question correctly.', 'She is often late.', 'He speaks very quietly.',
            'The soup tastes good.', 'She tasted the soup carefully.',
            'Come near. I nearly forgot.', 'The birds fly high. This book is highly recommended.',
            'She cut the speech short. I will call shortly.',
            'Is the room quiet? Yes, it is.', 'Does he speak clearly? Yes, he does.',
            'She does not speak loudly.', 'Yesterday she checked the form carefully.'] as $phrase) {
            self::assertStringContainsString($phrase, $basic);
        }
        self::assertStringContainsString('Look не забороняє прислівник', $basic);
        self::assertStringContainsString('не обов’язково на абсолютний нуль', $basic);
        self::assertStringContainsString('Good після feel також', $basic);
        self::assertStringContainsString('Hard у works hard уже є прислівником', $basic);
        self::assertStringContainsString('Разовий епізод не доводить постійної риси', $basic);
        self::assertSame([], $lesson['details'], 'Core functions and short cautions must not require expanding detail.');
    }

    public function test_order_basic_preserves_features_compound_names_article_quantity_and_nonabsolute_ordering(): void
    {
        $lesson = $this->lessons()[1]; $basic = $this->text(array_slice($lesson['blocks'], 0, 6));
        foreach (['вік / форма', 'не універсальний сортувальник', 'a beautiful old house',
            'a small black dog', 'an expensive Italian car', 'an unusual red chair',
            'my three new Ukrainian books', 'a light blue sleeping bag',
            'a modern glass office building', 'a red racing bike', 'Office — іменник-означення',
            'light blue разом називає колір', 'a cold, damp cellar', 'a small stone cottage',
            'The cellar is cold and damp.', 'Did you buy a small metal box?',
            "No, I didn't. I bought a large metal box.", "I don't need a new racing bike.",
            'питання саме по собі не доводить'] as $phrase) { self::assertStringContainsString($phrase, $basic); }
        $corrections = $lesson['blocks'][3]['data']['items'];
        self::assertSame('a wooden old box', $corrections[0]['wrong_en']);
        self::assertSame('an old wooden box', $corrections[0]['right_en']);
        self::assertStringContainsString('голосний звук old', $corrections[0]['note']);
        self::assertSame('a small black dog', $corrections[1]['right_en']);
        self::assertStringContainsString('не заборона', $corrections[1]['note']);
        self::assertSame('a warm blue sleeping bag', $corrections[2]['right_en']);
        foreach (['Warm', 'blue', 'жодну'] as $feature) { self::assertStringContainsString($feature, $corrections[2]['note']); }
        self::assertCount(1, $lesson['details']); $detail = $lesson['details'][0];
        self::assertSame('order-age-shape-context', $detail['key']);
        self::assertSame('order-usage', $detail['owner_block_key']); self::assertSame('/sections/0', $detail['owner_pointer']);
        self::assertSame(['They repaired an old round table.', 'They repaired a round old table.'],
            array_column($detail['fragment']['data']['sections'][0]['examples'], 'en'));
        self::assertStringContainsString('не змінює подію ремонту', $detail['fragment']['data']['sections'][0]['description']);
        $practice = $this->practice($lesson);
        self::assertSame('А — рівноправні ознаки; Б — уточнення типу будинку', $practice['choices'][0]['answer']);
        self::assertSame('А: lightweight прямо описує вагу', $practice['choices'][1]['answer']);
        self::assertStringContainsString('a light blue raincoat', $practice['choices'][1]['prompt']);
        self::assertStringNotContainsString('punctuation_sensitive', json_encode($practice, JSON_THROW_ON_ERROR));
    }

    public function test_eding_basic_allows_people_as_sources_and_keeps_neutral_meanings_separate_from_corrections(): void
    {
        $lesson = $this->lessons()[2]; $basic = $this->text(array_slice($lesson['blocks'], 0, 6));
        foreach (['bored / boring', 'interested / interesting', 'excited / exciting', 'tired / tiring',
            'confused / confusing', 'surprised / surprising', 'an interesting guide',
            'I am bored.', 'I am boring.', 'He is interesting, but he is not interested in this topic.',
            'It was a tiring journey.', 'She felt embarrassed in class.', 'The puzzle seems confusing.',
            'Are you bored? — No, I am not.', 'Was the trip exciting? — Yes, it was.',
            'I am tired now. I was tired yesterday. I will be tired after the walk.',
            'She is interested in art.', 'excited about', 'worried about', 'excited at the idea',
            'The story is interesting.', 'They are reading the story.',
            'The door was painted by a decorator yesterday.', 'smoked salmon'] as $phrase) {
            self::assertStringContainsString($phrase, $basic);
        }
        self::assertStringContainsString('Не кожне -ed описує емоцію', $basic);
        self::assertStringContainsString('не кожне be + -ing є Continuous', $basic);
        self::assertStringContainsString('не означає «у захваті»', $basic);
        $meaningPoint = $lesson['blocks'][2]['data']['sections'][0];
        self::assertStringContainsString('Обидва речення з I нижче граматичні', $meaningPoint['description']);
        $corrections = $lesson['blocks'][3]['data']['items'];
        self::assertNotContains('I am boring.', array_column($corrections, 'wrong_en'));
        self::assertSame(['She interested in art.', 'Was the film boring? — Yes, it did.', 'She is interesting in art.'],
            array_column($corrections, 'wrong_en'));
        self::assertStringContainsString('хоче більше знати про мистецтво', $corrections[2]['title']);
        self::assertStringContainsString('не заборона interesting для людей', $corrections[2]['note']);
        self::assertSame([], $lesson['details']);
        $practice = $this->practice($lesson);
        self::assertSame('interesting', $practice['selects'][1]['answer']);
        self::assertStringContainsString('не її власне зацікавлення', $practice['selects'][1]['label']);
        self::assertSame('Мовець описує себе як нудного для інших', $practice['choices'][0]['answer']);
        self::assertSame('Повідомлено лише, що Оля не нудьгує', $practice['choices'][1]['answer']);
    }

    public function test_six_own_inputs_keep_reviewed_aliases_time_role_words_and_exact_token_instances(): void
    {
        $expected = [
            'm69-adverbs-build-1' => ['Does Mark drive carefully?', 'Does Mark carefully drive?'],
            'm69-adverbs-build-2' => ['Lina checked the labels carefully yesterday.', 'Lina carefully checked the labels yesterday.'],
            'm69-order-build-1' => ['We need a small wooden shelf.'],
            'm69-order-build-2' => ['Did they buy two red racing bikes?'],
            'm69-eding-build-1' => ['The instructions were confusing.'],
            'm69-eding-build-2' => ['Is Danylo interested in art?'],
        ];
        $seen = []; $aliases = 0;
        foreach ($this->lessons() as $lesson) {
            foreach ($this->practice($lesson)['inputs'] as $task) {
                $seen[] = $task['id']; self::assertSame($expected[$task['id']], $task['accepted']);
                self::assertSame($expected[$task['id']][0], $task['answer']); $aliases += count($task['accepted']);
                $tokens = array_map('trim', explode('/', $task['before']));
                $canonical = preg_split('/\s+/', $task['answer']); sort($tokens, SORT_STRING); sort($canonical, SORT_STRING);
                self::assertSame($canonical, $tokens, 'Individual word spelling/case/punctuation/multiplicity are preserved.');
                foreach ($task['accepted'] as $answer) {
                    self::assertStringNotContainsString($answer, $task['after'], 'Do not put a complete answer into the visible criterion.');
                    self::assertArrayHasKey(mb_strtolower(trim($answer)), $task['feedback']);
                    self::assertTrue(AcceptedAnswerVariants::matches($answer, rtrim($answer, '.?')), 'Existing punctuation normalization only.');
                }
            }
        }
        self::assertSame(array_keys($expected), $seen); self::assertSame(8, $aliases);
        $adverbs = $this->practice($this->lessons()[0]);
        self::assertStringContainsString('yesterday — учора наприкінці', $adverbs['inputs'][1]['after']);
        self::assertStringContainsString('не універсальна заборона', $adverbs['inputs'][1]['feedback']['lina checked the labels yesterday carefully.']);
        $eding = $this->practice($this->lessons()[2]);
        self::assertStringContainsString('Confused', $eding['inputs'][0]['feedback']['the instructions were confused.']);
        self::assertStringContainsString('граматичний', $eding['inputs'][1]['feedback']['does danylo feel interested in art?']);
    }

    public function test_twelve_own_select_choices_have_one_key_under_actual_shared_variant_normalization(): void
    {
        $count = 0;
        foreach ($this->lessons() as $lesson) {
            foreach (['selects', 'choices'] as $group) {
                foreach ($this->practice($lesson)[$group] as $task) {
                    ++$count; self::assertSame([$task['answer']], $task['accepted']);
                    self::assertContains($task['answer'], $task['options']);
                    $matches = array_values(array_filter($task['options'], static fn ($option) => AcceptedAnswerVariants::matches($task['answer'], $option)));
                    self::assertSame([$task['answer']], $matches, $task['id'].' must not collapse its own distractors.');
                    foreach ($task['options'] as $option) {
                        self::assertArrayHasKey(mb_strtolower(trim($option)), $task['feedback']);
                        self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $task['feedback'][mb_strtolower(trim($option))]);
                    }
                }
            }
        }
        self::assertSame(12, $count);
    }

    public function test_full_english_examples_and_corrections_have_own_uk_fields_and_feedback_stays_plain_exact_data(): void
    {
        [$before, , $master] = Package::load(); $pairs = 0; $tasks = 0; $leaves = 0;
        $walk = function (array $node) use (&$walk, &$pairs): void {
            foreach (['en' => 'ua', 'wrong_en' => 'wrong_ua', 'right_en' => 'right_ua'] as $en => $uk) {
                if (!array_key_exists($en, $node)) { continue; }
                ++$pairs; self::assertIsString($node[$en]); self::assertNotSame('', trim($node[$en]));
                self::assertArrayHasKey($uk, $node); self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $node[$uk]);
            }
            foreach ($node as $child) { if (is_array($child)) { $walk($child); } }
        };
        foreach ($master['lessons'] as $i => $lesson) {
            $walk($lesson); $copy = $lesson; $projected = Package::definition($lesson, $before['targets'][$i]['before']);
            $slot = count($lesson['blocks']) - 1; $data = json_decode($projected['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach ($lesson['blocks'][0]['data']['rules'] as $rule) {
                self::assertMatchesRegularExpression('/[A-Za-z]/', strip_tags($rule['example']));
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', strip_tags($rule['example']));
            }
            foreach (['selects', 'choices', 'inputs'] as $group) {
                foreach ($lesson['blocks'][$slot]['data'][$group] as $n => $task) {
                    ++$tasks; self::assertSame($task['feedback'], $data[$group][$n]['feedback']);
                    foreach ($group === 'inputs' ? ['before', 'after'] : ['label', 'prompt'] as $field) {
                        if (array_key_exists($field, $task)) { self::assertSame($task[$field], $data[$group][$n][$field]); }
                    }
                    foreach ($task['feedback'] as $text) {
                        ++$leaves; self::assertIsString($text);
                        self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text);
                    }
                }
            }
            self::assertSame($copy, $lesson); self::assertDoesNotMatchRegularExpression('/✅|❌|font-mono/u', $this->text($lesson));
        }
        self::assertGreaterThan(60, $pairs); self::assertSame(18, $tasks); self::assertGreaterThan(30, $leaves);
    }

    public static function invalidFeedback(): array
    {
        $cases = [];
        foreach (['selects', 'choices', 'inputs'] as $group) {
            foreach (['tag' => '<span>Текст</span>', 'closing-tag' => 'Текст</span>', 'break' => 'Текст<br>Далі',
                'named-entity' => '&amp;', 'decimal-entity' => '&#39;', 'hex-entity' => '&#x27;',
                'comment' => '<!-- literal -->', 'not-text' => null] as $name => $text) {
                $cases[$group.'-'.$name] = [$group, $text];
            }
        }
        return $cases;
    }

    #[DataProvider('invalidFeedback')]
    public function test_each_native_feedback_group_refuses_markup_entities_or_nontext_without_sanitizing(string $group, mixed $text): void
    {
        [$before, , $master] = Package::load(); $lesson = $master['lessons'][0]; $slot = 11;
        $key = array_key_first($lesson['blocks'][$slot]['data'][$group][0]['feedback']);
        $lesson['blocks'][$slot]['data'][$group][0]['feedback'][$key] = $text; $mutated = $lesson;
        $caught = null;
        try { Package::definition($lesson, $before['targets'][0]['before']); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught);
        self::assertStringContainsString('M69 x-text feedback', $caught->getMessage());
        self::assertSame($mutated, $lesson, 'Reject the authored value; do not silently strip markup or decode entities.');
    }
}
