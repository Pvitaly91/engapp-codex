<?php

namespace Tests\Feature;

use App\Support\M67VerbPatternsCompletion as Package;
use Tests\TestCase;

/** Finite authored situations and aliases, not a universal grammar analyser or live UI run. */
class M67SemanticBoundariesTest extends TestCase
{
    private function lessons(): array { return Package::load()[2]['lessons']; }

    private function examples(array $section): array
    {
        $out = [];
        foreach ($section['examples'] ?? [] as $example) {
            self::assertNotEmpty($example['ua'], 'A complete English example keeps its specific UK translation.');
            $out[$example['en']] = $example['ua'];
        }
        return $out;
    }

    public function test_infinitive_basic_keeps_four_source_groups_roles_negative_scope_and_valid_alternative_patterns(): void
    {
        $lesson = $this->lessons()[0]; $forms = $lesson['blocks'][1]['data'];
        self::assertSame([
            'want / hope / need + to + базова форма',
            'decide / plan / choose + to + базова форма',
            'promise / refuse / agree + to + базова форма',
            'try / offer / learn + to + базова форма',
        ], array_column($forms['items'], 'title'));
        $sections = $lesson['blocks'][2]['data']['sections']; self::assertCount(4, $sections);
        $roles = $this->examples($sections[0]);
        self::assertSame('Я хочу поговорити з менеджером.', $roles['I want to speak to the manager.']);
        self::assertSame('Я хочу, щоб Тарас поговорив із менеджером.', $roles['I want Taras to speak to the manager.']);
        self::assertSame('Я сподіваюся, що Тарас отримає відповідь сьогодні.', $roles['I hope that Taras gets an answer today.']);
        $commitments = $this->examples($sections[1]);
        self::assertSame('Назар пообіцяв сестрі, що він забере посилку.',
            $commitments['Nazar promised his sister that he would collect the parcel.']);
        $negative = $this->examples($sections[2]);
        self::assertSame('Вони не прийняли рішення піти.', $negative['They did not decide to leave.']);
        self::assertSame('Вони вирішили не йти.', $negative['They decided not to leave.']);
        self::assertSame('Вона пообіцяла не поширювати файл.', $negative['She promised not to share the file.']);
        self::assertStringContainsString('She did not promise to share the file.', $sections[2]['note']);
        $alternatives = $this->examples($sections[3]);
        self::assertSame($alternatives['The curtains need washing.'], $alternatives['The curtains need to be washed.']);
        self::assertSame('Ми плануємо залишитися до понеділка.', $alternatives['We plan on staying until Monday.']);
        self::assertSame('Ми обрали меншу кімнату.', $alternatives['We decided on the smaller room.']);
        self::assertSame('/sections/2', $lesson['details'][0]['owner_pointer']);
        self::assertSame('infinitive-usage', $lesson['details'][0]['owner_block_key']);
    }

    public function test_all_five_meaning_change_verbs_have_their_own_full_basic_contrast_not_a_future_vs_past_shortcut(): void
    {
        $lesson = $this->lessons()[1]; self::assertSame('comparison-table', $lesson['blocks'][1]['type']);
        $sections = $lesson['blocks'][2]['data']['sections']; self::assertCount(5, $sections);
        foreach (['Stop', 'Remember', 'Forget', 'Try', 'Regret'] as $i => $verb) {
            self::assertStringStartsWith($verb.':', $sections[$i]['label']);
            self::assertNotEmpty($sections[$i]['description']); self::assertGreaterThanOrEqual(2, count($this->examples($sections[$i])));
        }
        self::assertArrayHasKey('We were cycling to the lake. We stopped to check the map.', $this->examples($sections[0]));
        self::assertArrayHasKey('I remembered to pay before I left.', $this->examples($sections[1]));
        self::assertArrayHasKey('Remember to pay before you leave.', $this->examples($sections[1]));
        self::assertStringContainsString('Did you remember to pay?', $sections[1]['note']);
        self::assertArrayHasKey('I will never forget seeing the sea for the first time.', $this->examples($sections[2]));
        self::assertArrayHasKey('I forgot to collect the parcel yesterday, so I will collect it today.', $this->examples($sections[2]));
        self::assertArrayHasKey('We tried adding a shelf, but the room still felt crowded.', $this->examples($sections[3]));
        self::assertArrayHasKey('I tried to move the desk, and I managed it.', $this->examples($sections[3]));
        self::assertArrayHasKey('He tried calling her twice.', $this->examples($sections[3]));
        self::assertArrayHasKey('I regret not keeping a copy.', $this->examples($sections[4]));
        self::assertArrayHasKey('We regret to inform you that the concert has been postponed.', $this->examples($sections[4]));
        $practice = $lesson['blocks'][6]['data'];
        self::assertStringContainsString('припинив звичку', $practice['selects'][0]['label']);
        self::assertStringContainsString('саме модель try + -ing', $practice['selects'][1]['label']);
        self::assertStringContainsString('A. Danylo remembered to turn off the oven.', $practice['choices'][0]['prompt']);
        self::assertStringContainsString('B. Did Danylo remember to turn off the oven?', $practice['choices'][0]['prompt']);
        self::assertStringContainsString('but I sent it at two.', $practice['choices'][1]['prompt']);
    }

    public function test_used_to_basic_covers_state_change_old_habit_questions_nouns_and_nonaccustomed_passive_use(): void
    {
        $lesson = $this->lessons()[2]; self::assertSame('comparison-table', $lesson['blocks'][1]['type']);
        $sections = $lesson['blocks'][2]['data']['sections']; self::assertCount(5, $sections);
        $state = $this->examples($sections[0]);
        self::assertArrayHasKey('We are used to long meetings.', $state);
        self::assertArrayHasKey('I was used to the cold then.', $state);
        self::assertArrayHasKey('By December, we will be used to it.', $state);
        $change = $this->examples($sections[1]);
        self::assertArrayHasKey('She is getting used to living alone.', $change);
        self::assertArrayHasKey('You will get used to driving on the left.', $change);
        self::assertArrayHasKey('I have got used to it now.', $change);
        self::assertArrayHasKey('They used to live in a small village. Now they live in the city.', $this->examples($sections[2]));
        $questions = $this->examples($sections[3]);
        foreach (['Are you used to the noise? — Yes, I am.', 'Was she used to it? — No, she was not.',
            'Did he get used to the new timetable? — Yes, he did.', 'He did not get used to the new timetable.',
            'Are they getting used to it? — Yes, they are.', 'Did you use to work here? — No, I did not.',
            'I did not use to work here.'] as $sentence) { self::assertArrayHasKey($sentence, $questions); }
        $purpose = $this->examples($sections[4]);
        self::assertArrayHasKey('I am used to cutting fabric.', $purpose);
        self::assertArrayHasKey('A sharp knife is used to cut the fabric.', $purpose);
        self::assertStringContainsString('/theory/tenses/used-to-would', $sections[2]['note']);
    }

    public function test_six_finite_input_keys_and_ten_reviewed_variants_preserve_roles_time_and_negative_scope(): void
    {
        $expected = [
            'm67-infinitive-build-1' => ['They decided not to sell the house.', 'They decided to not sell the house.'],
            'm67-infinitive-build-2' => ['I need you to check the address.'],
            'm67-meaning-build-1' => ['I regret not keeping the receipt.', 'I regret not having kept the receipt.'],
            'm67-meaning-build-2' => ['We regret to inform you that the workshop is full.'],
            'm67-usedto-build-1' => ['We are not used to the damp climate yet.', "We aren't used to the damp climate yet.", "We're not used to the damp climate yet."],
            'm67-usedto-build-2' => ['Did Artem get used to the new timetable last week?'],
        ];
        $seen = []; $variants = 0;
        foreach ($this->lessons() as $lesson) {
            foreach ($lesson['blocks'][6]['data']['inputs'] as $task) {
                $seen[] = $task['id']; self::assertArrayHasKey($task['id'], $expected);
                self::assertSame($expected[$task['id']][0], $task['answer']);
                self::assertSame($expected[$task['id']], $task['accepted']);
                $variants += count($task['accepted']);
                foreach ($task['accepted'] as $value) {
                    self::assertArrayHasKey(mb_strtolower($value), $task['feedback']);
                    self::assertStringNotContainsString($value, $task['after'], 'No complete ordered key in the visible input condition.');
                }
            }
        }
        self::assertSame(array_keys($expected), $seen); self::assertSame(10, $variants);
    }
}
