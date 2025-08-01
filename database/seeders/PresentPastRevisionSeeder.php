<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;

class PresentPastRevisionSeeder extends Seeder
{
    public function run()
    {
        // Категорії: 1 — Past Simple, 2 — Present Simple
        $cat_past = 1;
        $cat_present = 2;

        // 1. Underline the correct form (варіанти відповіді — зображено у формі select)
        $questions = [
            [
                'question' => 'Liz {a1} her mom yesterday.',
                'category_id' => $cat_past,
                'answers' => [['marker'=>'a1','answer'=>'helped','verb_hint'=>'help']],
                'options' => ['helps', 'helped'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'Ben {a1} hockey every day.',
                'category_id' => $cat_present,
                'answers' => [['marker'=>'a1','answer'=>'plays','verb_hint'=>'play']],
                'options' => ['plays', 'played'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'I {a1} TV yesterday.',
                'category_id' => $cat_past,
                'answers' => [['marker'=>'a1','answer'=>'watched','verb_hint'=>'watch']],
                'options' => ['watch', 'watched'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'They {a1} milk every day.',
                'category_id' => $cat_present,
                'answers' => [['marker'=>'a1','answer'=>'drink','verb_hint'=>'drink']],
                'options' => ['drink', 'drank'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'I always {a1} my room.',
                'category_id' => $cat_present,
                'answers' => [['marker'=>'a1','answer'=>'clean','verb_hint'=>'clean']],
                'options' => ['clean', 'cleaned'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'Andrew {a1} apples and bananas one hour ago.',
                'category_id' => $cat_past,
                'answers' => [['marker'=>'a1','answer'=>'ate','verb_hint'=>'eat']],
                'options' => ['eats', 'ate'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'Kate often {a1} her little sister.',
                'category_id' => $cat_present,
                'answers' => [['marker'=>'a1','answer'=>'helps','verb_hint'=>'help']],
                'options' => ['helps', 'helped'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'John {a1} a new mobile phone yesterday.',
                'category_id' => $cat_past,
                'answers' => [['marker'=>'a1','answer'=>'bought','verb_hint'=>'buy']],
                'options' => ['buys', 'bought'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'We {a1} to the cinema every weekend.',
                'category_id' => $cat_present,
                'answers' => [['marker'=>'a1','answer'=>'go','verb_hint'=>'go']],
                'options' => ['go', 'went'],
                'source' => 'Revision: Present or Past Simple',
            ],
        ];

        // 2. Fill in the correct form of the verb (input/select, вказуємо verb_hint)
        $questions = array_merge($questions, [
            [
                'question' => 'The cat {a1} the mouse yesterday.',
                'category_id' => $cat_past,
                'answers' => [['marker'=>'a1','answer'=>'caught','verb_hint'=>'catch']],
                'options' => ['catches', 'caught'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'Nora often {a1} the violin.',
                'category_id' => $cat_present,
                'answers' => [['marker'=>'a1','answer'=>'plays','verb_hint'=>'play']],
                'options' => ['plays', 'played'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'Sam {a1} an SMS every day.',
                'category_id' => $cat_present,
                'answers' => [['marker'=>'a1','answer'=>'sends','verb_hint'=>'send']],
                'options' => ['sends', 'sent'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'Nick {a1} an English test yesterday.',
                'category_id' => $cat_past,
                'answers' => [['marker'=>'a1','answer'=>'wrote','verb_hint'=>'write']],
                'options' => ['writes', 'wrote'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'They always {a1} apples.',
                'category_id' => $cat_present,
                'answers' => [['marker'=>'a1','answer'=>'buy','verb_hint'=>'buy']],
                'options' => ['buy', 'bought'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'The boys {a1} 2 km two days ago.',
                'category_id' => $cat_past,
                'answers' => [['marker'=>'a1','answer'=>'ran','verb_hint'=>'run']],
                'options' => ['run', 'ran'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'Mona {a1} tea every day.',
                'category_id' => $cat_present,
                'answers' => [['marker'=>'a1','answer'=>'drinks','verb_hint'=>'drink']],
                'options' => ['drinks', 'drank'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'We often {a1} Nick.',
                'category_id' => $cat_present,
                'answers' => [['marker'=>'a1','answer'=>'help','verb_hint'=>'help']],
                'options' => ['help', 'helped'],
                'source' => 'Revision: Present or Past Simple',
            ],
            [
                'question' => 'Pam {a1} to the classical music yesterday.',
                'category_id' => $cat_past,
                'answers' => [['marker'=>'a1','answer'=>'listened','verb_hint'=>'listen']],
                'options' => ['listens', 'listened'],
                'source' => 'Revision: Present or Past Simple',
            ],
        ]);

        // Для Make sentences negative, Write questions та Correct the mistakes
        // можна додати як окремі питання — для ручного вводу (без select-варіантів, лише verb_hint)
        // Ось приклади для "Make sentences negative":

        $questions = array_merge($questions, [
            [
                'question' => 'Ann {a1} very often.',
                'category_id' => $cat_present,
                'answers' => [['marker'=>'a1','answer'=>'does not skate','verb_hint'=>'skate / negative']],
                'options' => ['does not skate', 'did not skate'],
                'source' => 'Revision: Present or Past Simple (negative)',
            ],
            [
                'question' => 'Pam {a1} yesterday.',
                'category_id' => $cat_past,
                'answers' => [['marker'=>'a1','answer'=>'did not jog','verb_hint'=>'jog / negative']],
                'options' => ['does not jog', 'did not jog'],
                'source' => 'Revision: Present or Past Simple (negative)',
            ],
            // ... додай інші негативні та питання, якщо потрібно
        ]);

        // --- Запис у БД ---
        foreach ($questions as $data) {
            $q = Question::create([
                'question'    => $data['question'],
                'difficulty'  => 2,
                'category_id' => $data['category_id'],
                'flag'        => 0,
                'source'      => $data['source'],
            ]);
            foreach ($data['answers'] as $ans) {
                QuestionAnswer::create([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'answer'      => $ans['answer'],
                    'verb_hint'   => $ans['verb_hint'] ?? null,
                ]);
            }
            if (!empty($data['options'])) {
                foreach ($data['options'] as $opt) {
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'option'      => $opt,
                    ]);
                }
            }
        }
    }
}
