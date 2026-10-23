<?php

namespace Tests\Feature;

use App\Support\M83B2RevisionBasicGrammar as Package;
use PHPUnit\Framework\TestCase;

/** Independent finite teaching literals; not a general grammar or paraphrase classifier. */
class M83B2RevisionBasicGrammarSemanticFixturesTest extends TestCase
{
    private const ANSWERS = [
        [
            'selects' => ['will have been checked', 'be checked'],
            'choices' => ['Уявлено інше минуле та його наслідок; жаль потребує окремого контексту.',
                'Йдеться про очікуване майбутнє рішення; фактичне затвердження ще не підтверджено.'],
            'inputs' => ['Ivan said that he was tired.', 'The implementation of the plan, which we approved, will take time.'],
            'rephrase' => [],
        ],
        [
            'selects' => ['noun', 'adverb', 'preposition'],
            'choices' => ['У першому fast — adjective, у другому — adverb.',
                'It — займенник у позиції підмета; у повідомленні про погоду попередній предмет не потрібний.'],
            'inputs' => ['beautifully', 'good', 'hard', 'A small bird sings loudly.', 'Olia and I are in the garden.'],
            'rephrase' => ['pronoun conjunction pronoun verb preposition noun adverb',
                'interjection article adjective noun verb verb preposition article adjective noun'],
        ],
        [
            'selects' => ['Subject', 'Verb', 'Object'],
            'choices' => ['Ні: у цьому значенні sleep має повну модель S–V.', 'Описує стан підмета: це complement, а не object.'],
            'inputs' => ['Maria loves music.', 'He plays tennis.', 'The students read books.'],
            'rephrase' => ['I study English every day.', 'My brother plays football.'],
        ],
        [
            'selects' => ['Affirmative', 'Interrogative', 'Imperative', 'Negative'],
            'choices' => ['Це заперечне спонукання; з нього не випливає, що адресат уже послухався.',
                'Ні: це запит інформації, а відповідь не наведено.'],
            'inputs' => ["She doesn't like music.", 'Does she like music?', 'Be quiet.'],
            'rephrase' => ['Does he like football?', 'Be quiet!'],
        ],
        [
            'selects' => ['a', 'b', 'b'],
            'choices' => ['A', 'B'],
            'inputs' => ["Don't open the window.", "Don't be loud.", "Don't forget your keys."],
            'rephrase' => ['Be quiet!', "Don't be late for class!"],
        ],
        [
            'selects' => ['and', 'but', 'or', 'because'],
            'choices' => ['A', 'B', 'A'],
            'inputs' => ['I was tired, so I went to bed.', "She's rich, but she's not happy.", "I can't come because I'm busy."],
            'rephrase' => ['I stayed home because it was cold.'],
        ],
    ];

    private function lessons(): array
    {
        $r = Package::load(dirname(__DIR__, 2));
        Package::validate($r[0], $r[1], $r[2], $r[3]);

        return $r[2]['lessons'];
    }

    private function practice(array $lessons, int $owner): array
    {
        return $lessons[$owner]['blocks'][[5, 13, 11, 10, 11, 11][$owner]]['data'];
    }

    private function reviewed(array $task, string $answer, array $accepted, array $visible, array $rejected): void
    {
        self::assertSame($answer, $task['answer']); self::assertSame($accepted, $task['accepted']);
        $condition = $task['label'] ?? $task['after'] ?? $task['original'] ?? '';
        foreach ($visible as $literal) { self::assertStringContainsString($literal, $condition); }
        $keys = array_map(Package::comparisonKey(...), $accepted);
        foreach ($rejected as $wrong) { self::assertNotContains(Package::comparisonKey($wrong), $keys, $wrong); }
        foreach ($accepted as $value) {
            self::assertArrayHasKey(mb_strtolower(trim($value)), $task['feedback']);
            self::assertDoesNotMatchRegularExpression('/<\/?[a-z]|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu', $task['feedback'][mb_strtolower(trim($value))]);
        }
    }

    public function test_all_sixty_graded_targets_are_independent_literals_and_five_examples_stay_ungraded(): void
    {
        $lessons = $this->lessons(); $count = $demos = 0;
        foreach (self::ANSWERS as $i => $groups) {
            $p = $this->practice($lessons, $i);
            foreach ($groups as $group => $expected) {
                $rows = $p[$group] ?? [];
                if ($group === 'rephrase' && $i > 0) {
                    $demo = array_shift($rows); self::assertArrayNotHasKey('answer', $demo);
                    self::assertArrayNotHasKey('accepted', $demo); self::assertNotEmpty($demo['example_original']);
                    self::assertNotEmpty($demo['example_target']); ++$demos;
                }
                self::assertSame($expected, array_column($rows, 'answer'), $i.':'.$group);
                foreach ($rows as $task) {
                    ++$count; self::assertContains($task['answer'], $task['accepted']);
                    $keys = array_map(Package::comparisonKey(...), $task['accepted']);
                    foreach (['', ' ', "\t\n", '—'] as $empty) { self::assertNotContains(Package::comparisonKey($empty), $keys); }
                    self::assertArrayNotHasKey('strict', $task); self::assertArrayNotHasKey('punctuation_sensitive', $task);
                    foreach (array_unique([...$task['accepted'], ...($task['options'] ?? [])]) as $value) {
                        $feedback = $task['feedback'][mb_strtolower(trim($value))] ?? null;
                        self::assertIsString($feedback); self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $feedback);
                        self::assertDoesNotMatchRegularExpression('/<\/?[a-z]|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu', $feedback);
                    }
                }
            }
            self::assertSame([], $lessons[$i]['details']);
            self::assertNotEmpty($lessons[$i]['point_detail_decisions']);
        }
        self::assertSame(60, $count); self::assertSame(5, $demos);
    }

    public function test_b2_scope_preserves_future_passive_counterfactual_force_expected_not_actual_and_speaker(): void
    {
        $p = $this->practice($this->lessons(), 0);
        $this->reviewed($p['selects'][0], 'will have been checked', ['will have been checked'],
            ['Future Perfect Passive', 'By five tomorrow', 'by the auditor'], ['will have checked', 'will have been checking']);
        $this->reviewed($p['selects'][1], 'be checked', ['be checked'], ['пасив після must', 'The documents must'], ['been checked', 'check']);
        $this->reviewed($p['choices'][0], self::ANSWERS[0]['choices'][0], [self::ANSWERS[0]['choices'][0]],
            ['Контексту про почуття мовця немає', 'If I had known'], ['Форма сама доводить, що мовець обов’язково жалкує.']);
        $this->reviewed($p['choices'][1], self::ANSWERS[0]['choices'][1], [self::ANSWERS[0]['choices'][1]],
            ['не звіт про вже ухвалене рішення', 'is expected to be approved next week'], ['Пропозицію вже затверджено; очікування гарантує результат.']);
        $this->reviewed($p['inputs'][0], 'Ivan said that he was tired.', ['Ivan said that he was tired.', 'Ivan said he was tired.'],
            ['не змінюй мовця', 'that можна опустити'], ['Ivan said that I was tired.', 'I said that Ivan was tired.']);
        $this->reviewed($p['inputs'][1], self::ANSWERS[0]['inputs'][1], [self::ANSWERS[0]['inputs'][1]],
            ['Схвалили саме план', 'назви процесу', 'не саме розставлення ком'],
            ['We implemented the plan.', 'The approval of the plan will take time.']);
    }

    public function test_word_classes_keep_contextual_roles_all_seventeen_positions_and_short_lexical_criteria(): void
    {
        $p = $this->practice($this->lessons(), 1);
        self::assertSame([['noun', 'verb', 'adjective'], ['adjective', 'adverb', 'noun'], ['conjunction', 'preposition', 'adverb']],
            array_column($p['selects'], 'options'));
        foreach ([['She sings ___.', 'beautifully', 'спосіб співу'], ['He is a ___ student.', 'good', 'не його здоров’я'],
            ['They work ___ every day.', 'hard', 'майже не працюють']] as $i => [$stem, $answer, $criterion]) {
            self::assertSame($stem, $p['inputs'][$i]['before']); self::assertStringNotContainsString('/', $stem);
            self::assertSame($answer, $p['inputs'][$i]['answer']); self::assertStringContainsString($criterion, $p['inputs'][$i]['after']);
            self::assertArrayNotHasKey('id', $p['inputs'][$i]); self::assertArrayNotHasKey('source_index', $p['inputs'][$i]);
        }
        $this->reviewed($p['choices'][0], self::ANSWERS[1]['choices'][0], [self::ANSWERS[1]['choices'][0]],
            ['The train is FAST.', 'The train moves FAST.'], ['В обох fast — noun, бо йдеться про поїзд.']);
        $this->reviewed($p['choices'][1], self::ANSWERS[1]['choices'][1], [self::ANSWERS[1]['choices'][1]],
            ['It is cold today.', 'Раніше жодного предмета не названо'], ['It обов’язково замінює вже названий предмет; без нього речення неправильне.']);
        self::assertSame(['pronoun', 'conjunction', 'pronoun', 'verb', 'preposition', 'noun', 'adverb'], explode(' ', $p['rephrase'][1]['answer']));
        self::assertSame(['interjection', 'article', 'adjective', 'noun', 'verb', 'verb', 'preposition', 'article', 'adjective', 'noun'],
            explode(' ', $p['rephrase'][2]['answer']));
        self::assertSame('займенник сполучник займенник дієслово прийменник іменник прислівник', $p['rephrase'][1]['accepted'][1]);
        self::assertSame('вигук артикль прикметник іменник дієслово дієслово прийменник артикль прикметник іменник', $p['rephrase'][2]['accepted'][1]);
        self::assertArrayNotHasKey('id', $p['rephrase'][1]); self::assertArrayNotHasKey('source_index', $p['rephrase'][1]);
        self::assertArrayNotHasKey('answer', $p['rephrase'][0]);
    }

    public function test_svo_is_neutral_not_universal_and_complement_is_not_an_object(): void
    {
        $p = $this->practice($this->lessons(), 2);
        $this->reviewed($p['choices'][0], self::ANSWERS[2]['choices'][0], [self::ANSWERS[2]['choices'][0]],
            ['She sleeps.', 'повне правильне речення'], ['Так: після кожного англійського дієслова потрібен додаток.']);
        $this->reviewed($p['choices'][1], self::ANSWERS[2]['choices'][1], [self::ANSWERS[2]['choices'][1]],
            ['She is happy.'], ['Називає предмет, на який вона спрямовує дію.']);
        $this->reviewed($p['rephrase'][1], 'I study English every day.', ['I study English every day.'],
            ['без акцентного винесення', 'збережи every day'], ['English studies me every day.', 'I study English.']);
        $this->reviewed($p['rephrase'][2], 'My brother plays football.', ['My brother plays football.'],
            ['не змінюючи виконавця та заняття'], ['Football plays my brother.', 'My brother plays tennis.']);
    }

    public function test_sentence_type_polarity_question_force_and_emphatic_you_are_not_conflated(): void
    {
        $p = $this->practice($this->lessons(), 3);
        self::assertSame([['Affirmative', 'Negative'], ['Statement', 'Interrogative', 'Imperative'],
            ['Statement', 'Interrogative', 'Imperative'], ['Affirmative', 'Negative']], array_column($p['selects'], 'options'));
        $this->reviewed($p['choices'][0], self::ANSWERS[3]['choices'][0], [self::ANSWERS[3]['choices'][0]],
            ["Don't open the window."], ['Це лише заперечне твердження, яке доводить, що вікно зараз зачинено.']);
        $this->reviewed($p['choices'][1], self::ANSWERS[3]['choices'][1], [self::ANSWERS[3]['choices'][1]],
            ['Does she like music?'], ['Так: питання саме підтверджує позитивну відповідь.']);
        self::assertSame(["She doesn't like music.", 'She does not like music.'], $p['inputs'][0]['accepted']);
        self::assertStringContainsString('не розмовне перепитування', $p['inputs'][1]['after']);
        self::assertStringContainsString('можливе емфатичне звернення', $p['rephrase'][2]['original']);
        self::assertStringContainsString('без вираженого підмета', $p['rephrase'][2]['original']);
    }

    public function test_imperative_keeps_the_objects_negation_inclusion_and_request_not_achievement(): void
    {
        $p = $this->practice($this->lessons(), 4);
        $this->reviewed($p['choices'][0], 'A', ['A'], ['включно із собою', 'Let’s sit here.'], ['B']);
        $this->reviewed($p['choices'][1], 'B', ['B'], ['може його пом’якшувати', 'не повідомляє', 'гарантує ввічливість'], ['A']);
        self::assertSame(["Don't open the window.", 'Do not open the window.'], $p['inputs'][0]['accepted']);
        self::assertSame(["Don't be loud.", 'Do not be loud.'], $p['inputs'][1]['accepted']);
        self::assertSame(["Don't forget your keys.", 'Do not forget your keys.'], $p['inputs'][2]['accepted']);
        $this->reviewed($p['rephrase'][2], "Don't be late for class!", ["Don't be late for class!", 'Do not be late for class!'],
            ['збережи заперечення та for class'], ["Don't late for class!", 'Be late for class!']);
    }

    public function test_conjunctions_preserve_cause_result_negation_scope_contextual_or_and_comma_function(): void
    {
        $p = $this->practice($this->lessons(), 5);
        self::assertStringContainsString('не загальна властивість or', $p['selects'][2]['label']);
        self::assertStringContainsString('не роби загального висновку', $p['selects'][1]['label']);
        $this->reviewed($p['choices'][0], 'A', ['A'], ['Because I was hungry, I made lunch.', 'Початкову частину з because'], ['B']);
        $this->reviewed($p['choices'][1], 'B', ['B'], ['Саме хвороба була причиною', 'Tom was ill, so he stayed at home.'], ['A']);
        $this->reviewed($p['choices'][2], 'A', ['A'], ['обидва варіанти окремо', 'He doesn’t eat meat or fish.'], ['B']);
        self::assertSame(["She's rich, but she's not happy.", 'She is rich, but she is not happy.', "She's rich, but she isn't happy.", "She is rich, but she isn't happy."], $p['inputs'][1]['accepted']);
        self::assertSame(["I can't come because I'm busy.", 'I cannot come because I am busy.', "I can't come because I am busy.", "I cannot come because I'm busy."], $p['inputs'][2]['accepted']);
        $this->reviewed($p['rephrase'][1], 'I stayed home because it was cold.', ['I stayed home because it was cold.'],
            ['збережи холод як причину'], ['I stayed home because of it was cold.', 'I stayed home so it was cold.']);
    }
}
