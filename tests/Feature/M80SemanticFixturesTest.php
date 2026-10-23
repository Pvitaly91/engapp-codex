<?php

namespace Tests\Feature;

use App\Support\ComposeTokenCase;
use App\Support\M80BuilderSelectionPresentation as Builder;
use App\Support\M80NegativesTransformations as Package;
use PHPUnit\Framework\TestCase;

/**
 * Independently reviewed literal teaching facts, not expectations generated from AFTER.
 * Reads frozen files only: no application bootstrap, environment, SQL or browser state.
 * Finite-key checks below are not a claim to understand arbitrary paraphrases.
 */
class M80SemanticFixturesTest extends TestCase
{
    private function release(): array
    {
        $release = Package::load(dirname(__DIR__, 2));
        Package::validate($release[0], $release[1], $release[2], $release[3]);

        return $release;
    }

    private function tasks(array $lesson): array
    {
        $practices = array_values(array_filter($lesson['blocks'], static fn (array $block): bool => $block['type'] === 'practice-set'));
        self::assertCount(1, $practices);
        $result = [];
        foreach (['selects', 'choices', 'inputs'] as $group) {
            foreach ($practices[0]['data'][$group] as $task) {
                self::assertArrayNotHasKey($task['id'], $result);
                $result[$task['id']] = $task;
            }
        }
        self::assertCount(6, $result);

        return $result;
    }

    private function reviewed(array $task, string $answer, array $aliases, array $visibleFragments, array $rejected): void
    {
        self::assertSame($answer, $task['answer']);
        self::assertSame($aliases, $task['accepted']);
        $visible = $task['label'] ?? $task['after'] ?? '';
        self::assertStringContainsString('<p lang="en">', $visible);
        foreach ($visibleFragments as $fragment) {
            self::assertStringContainsString($fragment, $visible, $task['id'].' visible criterion/source');
        }
        foreach (['hint', 'placeholder', 'source_sentence', 'strict', 'punctuation_sensitive'] as $field) {
            self::assertArrayNotHasKey($field, $task);
        }
        $acceptedKeys = array_map(Package::comparisonKey(...), $task['accepted']);
        foreach ($aliases as $alias) {
            self::assertContains(Package::comparisonKey($alias), $acceptedKeys);
            $feedback = $task['feedback'][mb_strtolower(trim($alias))];
            self::assertStringContainsString('—', $feedback, 'Feedback includes an actual translation.');
            self::assertDoesNotMatchRegularExpression('/<\/?[a-z][^>]*>|&(?:#[0-9]+|[a-z]+);/iu', $feedback);
        }
        foreach ($rejected as $candidate) {
            self::assertNotContains(Package::comparisonKey($candidate), $acceptedKeys, $task['id'].' finite rejected counterexample');
        }
        if (isset($task['before'])) {
            $groups = preg_split('/\s*\/\s*/u', $task['before']);
            foreach ($groups as $group) {
                self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', trim($group))));
            }
            self::assertNotSame($answer, implode(' ', $groups), 'The visible token bank is not an ordered target hint.');
            $actualWords = preg_split('/\s+/u', implode(' ', $groups));
            $expectedWords = preg_split('/\s+/u', $answer);
            sort($actualWords); sort($expectedWords);
            self::assertSame($expectedWords, $actualWords, 'Token multiset retains every literal word, including named agents and dates.');
        }
    }

    public function test_modals_preserve_obligation_prohibition_inference_and_confirmed_success_as_distinct_messages(): void
    {
        [, , $master] = $this->release();
        $lesson = $master['lessons'][3]; $tasks = $this->tasks($lesson);
        $this->reviewed($tasks['m80-modals-form-past-obligation'], 'had to', ['had to'],
            ['It was necessary for Olha to leave before six yesterday.', 'Ольга мала піти'], ['has to', 'must have']);
        $this->reviewed($tasks['m80-modals-form-prohibition'], "mustn't", ["mustn't"],
            ['It is forbidden to enter this room.', 'заборону'], ["don't have to", "shouldn't"]);
        $this->reviewed($tasks['m80-modals-meaning-explicit-success'],
            'Так: можливість названа, а друга частина прямо підтверджує успіх.',
            ['Так: можливість названа, а друга частина прямо підтверджує успіх.'],
            ['We could finish the report yesterday, and we did.'],
            ['Ні: друга частина прямо повідомляє, що звіт не завершили.']);
        $this->reviewed($tasks['m80-modals-meaning-deduction'], 'Мовець майже впевнений у висновку, що Олег пішов.',
            ['Мовець майже впевнений у висновку, що Олег пішов.'],
            ['Oleh must have left the office.', 'не посилається на правило'],
            ['Правило зобов’язувало Олега піти; мовець повідомляє цей обов’язок.']);
        $this->reviewed($tasks['m80-modals-sentence-achievement'], 'I managed to unlock the gate yesterday.',
            ['I managed to unlock the gate yesterday.'], ['I succeeded in unlocking the gate yesterday.', 'Попри труднощі'],
            ['I could unlock the gate yesterday.', 'I managed to unlock the gate.', 'We managed to unlock the gate yesterday.']);
        $this->reviewed($tasks['m80-modals-sentence-no-necessity'], "We don't have to pay today.",
            ["We don't have to pay today.", 'We do not have to pay today.'],
            ['It is not necessary for us to pay today.', 'а не заборону'], ['We must not pay today.', 'We have to pay today.']);
        $section = $lesson['blocks'][1]['data']['sections'][0];
        self::assertSame('We could finish the report yesterday, and we did.', $section['examples'][0]['en']);
        self::assertStringContainsString('прямо підтверджує результат', $section['description']);
        self::assertStringContainsString('сприйняття', $lesson['details'][0]['fragment']['data']['sections'][0]['description']);
    }

    public function test_passive_retains_named_agents_recipient_roles_aspect_negation_and_question_force(): void
    {
        [, , $master] = $this->release(); $lesson = $master['lessons'][4]; $tasks = $this->tasks($lesson);
        $this->reviewed($tasks['m80-passive-form-past-agent'], 'was written', ['was written'],
            ['Lina wrote the letter yesterday.', 'by Lina yesterday.'], ['was wrote', 'is written']);
        $this->reviewed($tasks['m80-passive-form-ongoing'], 'is being painted', ['is being painted'],
            ['Some workers are painting the gate.', 'результат ще не готовий'], ['is painted', 'has been painted']);
        $this->reviewed($tasks['m80-passive-meaning-named-agent'], 'The bridge will be inspected by Marta tomorrow.',
            ['The bridge will be inspected by Marta tomorrow.'], ['Marta will inspect the bridge tomorrow.', 'зберегти і виконавицю'],
            ['The bridge will be inspected tomorrow.', 'The bridge was inspected by Marta tomorrow.']);
        $this->reviewed($tasks['m80-passive-meaning-question-roles'], 'Were the files sent to Iryna by Oleh yesterday?',
            ['Were the files sent to Iryna by Oleh yesterday?'], ['Did Oleh send Iryna the files yesterday?', 'відправник Олег, одержувач Ірина'],
            ['Were the files sent to Oleh by Iryna yesterday?', 'The files were sent to Iryna by Oleh yesterday.']);
        $this->reviewed($tasks['m80-passive-sentence-negative-perfect'], 'The form has not been signed by Nina.',
            ['The form has not been signed by Nina.', "The form hasn't been signed by Nina."],
            ['Nina has not signed the form.', 'заперечення'],
            ['The form has been signed by Nina.', 'The form has not been signed.', 'The form was not signed by Nina.']);
        $this->reviewed($tasks['m80-passive-sentence-past-question'], 'Was the bike repaired by Marta yesterday?',
            ['Was the bike repaired by Marta yesterday?'], ['Did Marta repair the bike yesterday?', 'виконавицю Марту'],
            ['The bike was repaired by Marta yesterday.', 'Was the bike repaired yesterday?', 'Was the bike repaired by Nina yesterday?']);
        self::assertSame('The road was closed by the authorities.', $lesson['blocks'][1]['data']['sections'][0]['examples'][0]['en']);
        self::assertStringContainsString('Обидва речення граматичні', $lesson['blocks'][1]['data']['sections'][0]['note']);
        self::assertStringContainsString('to/for', $lesson['blocks'][1]['data']['sections'][2]['description']);
        self::assertSame('A cake was made for him by Olha.', $lesson['details'][0]['fragment']['data']['sections'][0]['examples'][3]['en']);
    }

    public function test_reported_speech_uses_known_people_time_place_and_scope_without_certifying_the_reported_event(): void
    {
        [, , $master] = $this->release(); $lesson = $master['lessons'][5]; $tasks = $this->tasks($lesson);
        $this->reviewed($tasks['m80-reported-form-past-process'], 'was working', ['was working'],
            ['Iryna said to Oleh, “I am working today.”', 'У понеділок', 'У середу вдома', 'процес'], ['had worked', 'is working']);
        $this->reviewed($tasks['m80-reported-form-negative-request'], 'not to touch', ['not to touch'],
            ["The guide said to our group, “Please don't touch the map.”", 'саме до нашої групи', 'Після екскурсії в іншій кімнаті'],
            ['to touch', 'not touching']);
        $this->reviewed($tasks['m80-reported-meaning-referent'], 'Lina said that she would finish that report the next day.',
            ['Lina said that she would finish that report the next day.'],
            ['Lina said, “I will finish this report tomorrow.”', 'У понеділок', 'У середу вдома', 'Ліна у вівторок'],
            ['Lina said that I would finish that report the next day.', 'Lina said that she would finish that report tomorrow.']);
        $this->reviewed($tasks['m80-reported-meaning-report-not-fact'], 'Oleh said that he had posted the letter.',
            ['Oleh said that he had posted the letter.'],
            ['Oleh said, “I have posted the letter.”', 'незалежного доказу відправлення немає'], ['Oleh had posted the letter.']);
        $this->reviewed($tasks['m80-reported-sentence-known-request'], 'The teacher asked us to open our books.',
            ['The teacher asked us to open our books.'], ['The teacher said to us, “Please open your books.”', 'Вранці в класі', 'Після уроку в коридорі'],
            ['The teacher told us to open our books.', 'The teacher asked me to open my books.', 'The teacher asked us to open your books.']);
        $this->reviewed($tasks['m80-reported-sentence-yes-no'], 'Olha asked me if I knew her address.',
            ['Olha asked me if I knew her address.', 'Olha asked me whether I knew her address.'],
            ['Olha asked me, “Do you know my address?”', 'У понеділок', 'У вівторок удома', 'Олега'],
            ['Olha asked me did I know her address.', 'Olha asked me if she knew my address.', 'Olha asked me if I know her address.']);
        $detail = $lesson['details'][0]['fragment']['data']['sections'][0];
        self::assertSame('The librarian said that the library opens at nine.', $detail['examples'][0]['en']);
        self::assertSame('The librarian said that the library opened at nine.', $detail['examples'][1]['en']);
        self::assertStringContainsString('сама по собі не стверджує, що розклад уже скасовано', $detail['description']);
        self::assertSame('The teacher asked us not to open the window.', $lesson['blocks'][1]['data']['sections'][2]['examples'][0]['en']);
        self::assertSame("The teacher didn't ask us to open the window.", $lesson['blocks'][1]['data']['sections'][2]['examples'][1]['en']);
    }

    public function test_fifteen_reused_widget_keys_match_literal_native_visible_answers_and_do_not_hide_case_defects(): void
    {
        $this->release(); $audit = Builder::loadAudit(dirname(__DIR__, 2));
        $expected = [
            3 => [
                'stmod-poly-b1-02' => 'We have to leave now.',
                'stmod-poly-b1-03' => 'You are not allowed to park here.',
                'stmod-poly-b1-05' => 'You ought to call her.',
                'stmod-poly-b1-08' => 'He must have forgotten.',
                'stmod-poly-b1-11' => 'We were able to finish on time.',
            ],
            4 => [
                'stpas-poly-b1-02' => 'Anna wrote the letter.',
                'stpas-poly-b1-04' => 'The play was written by Shakespeare.',
                'stpas-poly-b1-05' => 'The road is being repaired.',
                'stpas-poly-b1-08' => 'The report has already been checked.',
                'stpas-poly-b1-12' => 'This form must be signed today.',
            ],
            5 => [
                'strsp-poly-b1-02' => 'He asked if I was ready.',
                'strsp-poly-b1-03' => 'She asked me to open the door.',
                'strsp-poly-b1-05' => 'Anna said that her bag was there.',
                'strsp-poly-b1-06' => 'He said that he would call the next day.',
                'strsp-poly-b1-09' => 'Dad told us not to wait outside.',
            ],
        ];
        foreach ($expected as $owner => $cases) {
            $samples = $audit['targets'][$owner]['first_five'];
            self::assertSame(array_keys($cases), array_column($samples, 'uuid'));
            foreach ($samples as $sample) {
                $literal = $cases[$sample['uuid']];
                self::assertSame($literal, preg_replace('/\s+([?.!,;:])/', '$1', $sample['expected_word_key']));
                self::assertSame($literal, $sample['actual_correct_visible']);
                self::assertSame($literal, $sample['actual_selected_visible']);
                $tokens = ComposeTokenCase::normalize($sample['raw_correct_tokens']);
                self::assertSame($tokens, $sample['correct_tokens']);
                self::assertSame($literal, preg_replace('/\s+([?.!,;:])/', '$1', implode(' ', $tokens)));
                self::assertSame([], $sample['token_presentations']);
                self::assertSame([], $sample['option_presentations']);
                self::assertStringNotContainsString($literal, $sample['visible_condition_uk'], 'No ready target in pre-answer condition.');
                self::assertStringNotContainsString('Food', $literal);
                self::assertDoesNotMatchRegularExpression('/\sWhere\s/', $literal);
            }
        }
        // Literal BEFORE defects stay excluded, not silently normalized by a new rule.
        self::assertSame(['You', 'need', 'not', 'bring', 'Food', '.'], ComposeTokenCase::normalize(['You', 'need', 'not', 'bring', 'Food', '.']));
        self::assertSame(['He', 'asked', 'me', 'Where', 'I', 'was', 'going', '.'],
            ComposeTokenCase::normalize(['He', 'asked', 'me', 'Where', 'I', 'was', 'going', '.']));
    }

    public function test_twelve_useful_source_table_functions_remain_bilingual_with_explicit_semantic_repairs(): void
    {
        [, , $master] = $this->release();
        $pairs = [
            3 => [
                ['I managed to solve the puzzle after several attempts.', 'I was able to solve the puzzle after several attempts.'],
                ['It is necessary for all visitors to wear badges.', 'All visitors have to wear badges.'],
                ['It would be a good idea for you to leave earlier.', 'You should leave earlier.'],
                ["I'm sure he isn't at home.", "He can't be at home."],
            ],
            4 => [
                ['People speak English in many offices.', 'English is spoken in many offices.'],
                ['They have cancelled the meeting.', 'The meeting has been cancelled.'],
                ['Someone will send you the link tomorrow.', 'The link will be sent to you tomorrow.'],
                ['They are repairing the bridge.', 'The bridge is being repaired.'],
            ],
            5 => [
                ['Anna said, “I can\'t stay long.”', "Anna said that she couldn't stay long."],
                ['He asked me, “Where do you work?”', 'He asked me where I worked.'],
                ['The teacher said to our group, “Please close the window.”', 'The teacher asked us to close the window.'],
                ['They said, “We are meeting tomorrow.”', 'They said that they were meeting the next day.'],
            ],
        ];
        foreach ($pairs as $owner => $rows) {
            $actual = $master['lessons'][$owner]['blocks'][2]['data']['rows']; self::assertCount(4, $actual);
            foreach ($rows as $index => [$source, $target]) {
                self::assertSame($source.'<br>→ '.$target, $actual[$index]['en']);
                self::assertStringContainsString('<br>→ ', $actual[$index]['ua']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $actual[$index]['ua']);
                self::assertNotSame('', trim($actual[$index]['note']));
            }
        }
    }
}
