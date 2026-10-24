<?php

namespace Tests\Feature;

use App\Support\M84WordOrderHaveGot as Package;
use PHPUnit\Framework\TestCase;

/** Independent finite teaching cases; this does not attempt to classify arbitrary English. */
class M84WordOrderHaveGotSemanticFixturesTest extends TestCase
{
    private const TARGETS = [
        [
            'selects' => ['a', 'a', 'b'],
            'choices' => ['Том допомагає, а Анна отримує допомогу.', 'Час і місце події залишаться тими самими.'],
            'inputs' => ['She often reads books.', 'I eat breakfast every morning.', 'We have dinner at home on Sundays.'],
            'rephrase' => ['She always reads books in the evening.', 'We go to the supermarket every week.'],
        ],
        [
            'selects' => ['a', 'a', 'b'],
            'choices' => ['Невідомо, хто телефонував Мії.', 'Чи готовий співрозмовник зараз.'],
            'inputs' => ['Do you speak English?', 'Does she work in a bank?', 'Did they go to the party?'],
            'rephrase' => ["She doesn't play tennis.", "They didn't watch the movie."],
        ],
        [
            'selects' => ['a', 'b', 'b'],
            'choices' => ['Марта не працює вдома кожного разу; речення не означає, що вона ніколи там не працює.',
                'Ступінь втоми Нори, а не місце або частоту роботи.'],
            'inputs' => ['She is always late.', 'He plays the piano well.', 'They work in the office every day.'],
            'rephrase' => ['She speaks French well.', 'Never have I been to Japan.'],
        ],
        [
            'selects' => ['a', 'b', 'a'],
            'choices' => ['Обидва про ту саму дію й посилку; короткий іменник можна поставити з обох боків up.',
                'B: have утворює Perfect разом із finished.'],
            'inputs' => ['Pick it up.', 'Turn it off.', 'Put them on.'],
            'rephrase' => ['He can play the guitar.', 'Throw them away.'],
        ],
        [
            'selects' => ['b', 'a', 'a'],
            'choices' => ['It was on Friday that Mia met Leo.', 'Нам допомогли і Том, і Леа.'],
            'inputs' => ['It was you who called me.', 'It was yesterday that she left.', 'It is help that I need.'],
            'rephrase' => ['Rarely does she make mistakes.', 'Not only did he finish early, but he also helped.'],
        ],
        [
            'selects' => ['have got', 'Has'],
            'choices' => ['У нього є два брати.', 'No, I have not.'],
            'inputs' => ['Our teacher has got two pets.', 'The children have not got a red ball.'],
            'rephrase' => [],
        ],
    ];

    private function practices(): array
    {
        $r = Package::load(dirname(__DIR__, 2)); Package::validate($r[0], $r[1], $r[2], $r[3]);
        return array_map(static fn ($l, $i) => $l['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'], $r[2]['lessons'], array_keys($r[2]['lessons']));
    }

    private function reviewed(array $task, string $answer, array $accepted, array $visible, array $rejected): void
    {
        self::assertSame($answer, $task['answer']); self::assertSame($accepted, $task['accepted']);
        $condition = $task['label'] ?? $task['after'] ?? $task['original'] ?? '';
        foreach ($visible as $literal) { self::assertStringContainsString($literal, $condition); }
        $keys = array_map(Package::comparisonKey(...), $accepted);
        foreach ($rejected as $wrong) { self::assertNotContains(Package::comparisonKey($wrong), $keys, $wrong); }
        foreach ($accepted as $value) {
            $feedback = $task['feedback'][mb_strtolower(trim($value))] ?? null;
            self::assertIsString($feedback); self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $feedback);
            self::assertDoesNotMatchRegularExpression('/<[^>]+>|\{a\d+\}|&(?:#[0-9]+|#x[0-9a-f]+|[a-z]+);/iu', $feedback);
        }
    }

    public function test_all_56_canonical_targets_are_independent_literals_and_five_demos_never_grade(): void
    {
        $practices = $this->practices(); $count = $demos = 0;
        foreach (self::TARGETS as $i => $groups) {
            foreach ($groups as $group => $expected) {
                $rows = $practices[$i][$group] ?? [];
                if ($group === 'rephrase' && $i < 5) {
                    $demo = array_shift($rows); self::assertArrayNotHasKey('answer', $demo); self::assertArrayNotHasKey('accepted', $demo);
                    self::assertNotEmpty($demo['example_original']); self::assertNotEmpty($demo['example_target']); ++$demos;
                }
                self::assertSame($expected, array_column($rows, 'answer'), $i.':'.$group);
                foreach ($rows as $task) {
                    ++$count; self::assertContains($task['answer'], $task['accepted']);
                    self::assertArrayNotHasKey('strict', $task); self::assertArrayNotHasKey('punctuation_sensitive', $task);
                }
            }
        }
        self::assertSame([56, 5], [$count, $demos]);
    }

    public function test_basic_order_keeps_roles_and_allows_initial_time_when_the_condition_allows_it(): void
    {
        $p = $this->practices()[0];
        $this->reviewed($p['inputs'][0], 'She often reads books.', ['She often reads books.'],
            ['Почни з підмета She', 'often безпосередньо перед reads'], ['Often she reads books.', 'She reads often books.']);
        $this->reviewed($p['inputs'][1], 'I eat breakfast every morning.', ['I eat breakfast every morning.', 'Every morning, I eat breakfast.'],
            ['в кінці або на початку'], ['I eat dinner every morning.']);
        $this->reviewed($p['inputs'][2], 'We have dinner at home on Sundays.', ['We have dinner at home on Sundays.', 'On Sundays, we have dinner at home.'],
            ['місце перед часом', 'можна почати з часу'], ['We have dinner at school on Sundays.']);
        $this->reviewed($p['choices'][0], self::TARGETS[0]['choices'][0], [self::TARGETS[0]['choices'][0]],
            ['Tom helps Anna.'], ['Том отримує допомогу від Анни.']);
        self::assertStringContainsString('Початковий час допустимий', $p['choices'][1]['feedback'][mb_strtolower($p['choices'][1]['answer'])]);
    }

    public function test_questions_keep_be_and_subject_wh_boundaries_and_auxiliary_base_form(): void
    {
        $p = $this->practices()[1];
        $this->reviewed($p['selects'][1], 'a', ['a'], ['Where does she live?', 'Where she lives?'], ['b']);
        $this->reviewed($p['inputs'][1], 'Does she work in a bank?', ['Does she work in a bank?'],
            ['She works in a bank.', 'нейтральне повне питання'], ['Does she works in a bank?']);
        $this->reviewed($p['inputs'][2], 'Did they go to the party?', ['Did they go to the party?'],
            ['They went to the party.', 'про минуле'], ['Did they went to the party?']);
        $this->reviewed($p['choices'][0], self::TARGETS[1]['choices'][0], [self::TARGETS[1]['choices'][0]],
            ['Who called Mia?'], ['Невідомо, кому телефонувала Мія.']);
        $this->reviewed($p['choices'][1], self::TARGETS[1]['choices'][1], [self::TARGETS[1]['choices'][1]],
            ['Are you ready?'], ['Чи вміє співрозмовник плавати.']);
        self::assertSame(["She doesn't play tennis.", 'She does not play tennis.'], $p['rephrase'][1]['accepted']);
        self::assertSame(["They didn't watch the movie.", 'They did not watch the movie.'], $p['rephrase'][2]['accepted']);
    }

    public function test_adverb_scope_manner_object_and_initial_never_have_explicit_local_criteria(): void
    {
        $p = $this->practices()[2];
        $this->reviewed($p['choices'][0], self::TARGETS[2]['choices'][0], [self::TARGETS[2]['choices'][0]],
            ['Marta does not always work at home.'], ['Марта ніколи не працює вдома.']);
        $feedback = $p['choices'][0]['feedback'][mb_strtolower($p['choices'][0]['answer'])];
        self::assertStringContainsString('є випадки, коли Марта працює не вдома', $feedback);
        self::assertStringContainsString('не визначає, як часто вона працює вдома', $feedback);
        $this->reviewed($p['inputs'][1], 'He plays the piano well.', ['He plays the piano well.'],
            ['прислівник способу наприкінці', 'після додатка'], ['He plays well the piano.']);
        $this->reviewed($p['rephrase'][2], 'Never have I been to Japan.', ['Never have I been to Japan.'],
            ['Збережи Never на початку', 'решту слів не змінюй'], ['Never I have been to Japan.', 'I have never been to Japan.']);
    }

    public function test_verbs_objects_keep_singular_plural_particles_and_do_not_add_an_article_trap(): void
    {
        $p = $this->practices()[3];
        self::assertSame(['Pick it up.', 'Turn it off.', 'Put them on.'], array_column($p['inputs'], 'answer'));
        $this->reviewed($p['rephrase'][1], 'He can play the guitar.', ['He can play the guitar.'],
            ['He can to play the guitar.', 'Прибери тільки зайве to', 'всі інші слова'], ['He can play guitar.', 'He can to play the guitar.']);
        self::assertSame(str_replace(' to ', ' ', 'He can to play the guitar.'), $p['rephrase'][1]['answer']);
        $this->reviewed($p['rephrase'][2], 'Throw them away.', ['Throw them away.'],
            ['старі чеки', 'займенник множини them'], ['Throw away them.', 'Throw it away.']);
        $this->reviewed($p['choices'][0], self::TARGETS[3]['choices'][0], [self::TARGETS[3]['choices'][0]],
            ['Pick up the package.', 'Pick the package up.'], ['У другому реченні наказують забрати іншу посилку.']);
    }

    public function test_emphasis_preserves_facts_focus_and_valid_infinitival_what_cleft_alternative(): void
    {
        $p = $this->practices()[4];
        $this->reviewed($p['selects'][2], 'a', ['a'], ['Обидві конструкції можуть бути граматичними.', 'іменною групою', 'What I need is to rest.'], ['b']);
        self::assertStringContainsString('теж граматичне', $p['selects'][2]['feedback']['b']);
        $this->reviewed($p['inputs'][0], 'It was you who called me.', ['It was you who called me.', 'It was you that called me.'],
            ['You called me.', 'виділи саме тебе', 'who або that'], ['It was only you who called me.', 'It was you which called me.']);
        $this->reviewed($p['choices'][0], 'It was on Friday that Mia met Leo.', ['It was on Friday that Mia met Leo.'],
            ['саме день', 'Mia met Leo on Friday.'], ['It was Mia who met Leo on Friday.', 'It was Leo that Mia met on Friday.']);
        $this->reviewed($p['choices'][1], self::TARGETS[4]['choices'][1], [self::TARGETS[4]['choices'][1]],
            ['Not only Tom but also Lea helped us.', 'спільним підметом'], ['Том не допоміг, а Леа допомогла.']);
        $this->reviewed($p['rephrase'][2], self::TARGETS[4]['rephrase'][1], [self::TARGETS[4]['rephrase'][1]],
            ['Після but повтори he', 'also постав безпосередньо перед helped', 'обидві події й минулий час'],
            ['Not only did he finish early, but also he helped.', 'Not only did he finish early, but he also helps.']);
    }

    public function test_have_got_keeps_agreement_speaker_contractions_and_family_meaning(): void
    {
        $p = $this->practices()[5];
        $this->reviewed($p['selects'][1], 'Has', ['Has'], ['___ Nina got a blue bag?', 'не переходь до іншої моделі з do'], ['Have', 'Does']);
        $this->reviewed($p['choices'][0], 'У нього є два брати.', ['У нього є два брати.'],
            ["He's got two brothers.", "'s скорочує has"], ['Він є одним із двох братів.', 'У неї є два брати.']);
        $this->reviewed($p['choices'][1], 'No, I have not.', ['No, I have not.'],
            ['Оля питає Марка', 'Марк відповідає', 'повну форму', 'Have you got a bike?'], ['No, you have not.', 'No, I do not.']);
        $this->reviewed($p['inputs'][0], 'Our teacher has got two pets.', ['Our teacher has got two pets.', "Our teacher's got two pets."],
            ['У нашої вчительки', 'повну або доречну скорочену'], ['Our teacher have got two pets.']);
        $this->reviewed($p['inputs'][1], 'The children have not got a red ball.', ['The children have not got a red ball.', "The children haven't got a red ball."],
            ['У дітей немає', 'повну або доречну скорочену'], ['The children have got a red ball.']);
    }
}
