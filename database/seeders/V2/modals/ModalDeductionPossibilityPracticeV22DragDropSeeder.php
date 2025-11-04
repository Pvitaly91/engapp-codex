<?php

namespace Database\Seeders\V2\Modals;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;
use Illuminate\Support\Facades\Schema;

class ModalDeductionPossibilityPracticeV22DragDropSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Deduction & Possibility Practice V2-2'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Modal Deduction Possibility V2.2 - Drag Drop'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'Modals']
        )->id;

        $questions = [
            [
                'id' => 1,
                'text' => "Mike, (...) you mind not doing that?",
                'correct' => "would",
                'level' => 'B1',
                'hint' => 'Для ввічливого прохання не робити щось використовуємо "would you mind".',
                'explanation' => 'Фраза "would you mind" є стандартним способом ввічливо попросити когось припинити або не робити щось.',
            ],
            [
                'id' => 2,
                'text' => "I (...) speak six languages.",
                'correct' => "can",
                'level' => 'A2',
                'hint' => 'Для вираження здатності або вміння використовуємо модальне дієслово "can".',
                'explanation' => '"Can" виражає здатність або навичку робити щось, у цьому випадку - вміння говорити шістьма мовами.',
            ],
            [
                'id' => 3,
                'text' => '"Why hasn\'t she arrived yet?" "She (...) be ill."',
                'correct' => "may / might",
                'level' => 'B1',
                'hint' => 'Для припущення або можливості використовуємо "may" або "might".',
                'explanation' => '"May" та "might" виражають можливість або припущення про те, що хтось може бути хворим.',
            ],
            [
                'id' => 4,
                'text' => "(...) you please help me with these bags?",
                'correct' => "could / can / will",
                'level' => 'A2',
                'hint' => 'Для ввічливого прохання можна використати "could", "can" або "will".',
                'explanation' => 'У ввічливих проханнях можна використовувати "Could you", "Can you" або "Will you please".',
            ],
            [
                'id' => 5,
                'text' => "You (...) have told him the truth.",
                'correct' => "should / ought to",
                'level' => 'B2',
                'hint' => 'Для вираження порад або рекомендацій про минуле використовуємо "should have" або "ought to have".',
                'explanation' => '"Should have told" або "ought to have told" виражає, що було б правильно сказати правду, але цього не зробили.',
            ],
            [
                'id' => 6,
                'text' => "You (...) bring animals into England.",
                'correct' => "mustn't",
                'level' => 'B1',
                'hint' => 'Для вираження заборони використовуємо "mustn\'t".',
                'explanation' => '"Mustn\'t" виражає сувору заборону - не можна ввозити тварин до Англії.',
            ],
            [
                'id' => 7,
                'text' => "The piano is so heavy. It (...) move.",
                'correct' => "can't",
                'level' => 'B1',
                'hint' => 'Для вираження неможливості використовуємо "can\'t".',
                'explanation' => '"Can\'t move" означає, що піаніно настільки важке, що воно не може рухатися.',
            ],
            [
                'id' => 8,
                'text' => "(...) we invite the Smiths for the party?",
                'correct' => "shall",
                'level' => 'B1',
                'hint' => 'Для пропозиції або запиту про дію використовуємо "shall".',
                'explanation' => '"Shall we" використовується для пропозиції зробити щось разом.',
            ],
            [
                'id' => 9,
                'text' => "I'm afraid I (...) go with you.",
                'correct' => "can't",
                'level' => 'A2',
                'hint' => 'Для вираження неможливості зробити щось використовуємо "can\'t".',
                'explanation' => '"Can\'t" показує, що людина не може піти разом з кимось.',
            ],
            [
                'id' => 10,
                'text' => "My parents say I (...) eat so many sweets.",
                'correct' => "shouldn't",
                'level' => 'B1',
                'hint' => 'Для вираження поради або рекомендації використовуємо "shouldn\'t".',
                'explanation' => '"Shouldn\'t" виражає пораду не їсти так багато солодощів.',
            ],
            [
                'id' => 11,
                'text' => "I have a wedding party at the weekend. I (...) buy a new dress and a pair of shoes.",
                'correct' => "must",
                'level' => 'B1',
                'hint' => 'Для вираження необхідності або обов\'язку використовуємо "must".',
                'explanation' => '"Must" виражає необхідність або сильне бажання купити нову сукню та взуття.',
            ],
            [
                'id' => 12,
                'text' => "I was wondering if I (...) ask you for a favour.",
                'correct' => "could",
                'level' => 'B2',
                'hint' => 'Для дуже ввічливого прохання використовуємо "could".',
                'explanation' => '"I was wondering if I could" - це дуже ввічливий спосіб попросити про послугу.',
            ],
        ];

        $questionPayloads = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $questionText = $entry['text'];
            $uuid = $this->generateQuestionUuid('drag-drop', $entry['id'], $questionText);
            $level = $entry['level'];

            // Handle multiple correct answers separated by " / "
            $correctAnswers = array_map('trim', explode('/', $entry['correct']));

            $questionPayloads[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$level] ?? 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $level,
                'tag_ids' => [$themeTagId, $modalsTagId],
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => $correctAnswers[0],
                    ],
                ],
                'options' => [],
                'variants' => [$questionText],
                'type' => Question::TYPE_DRAG_DROP,
            ];

            $meta[] = [
                'uuid' => $uuid,
                'hints' => [$entry['hint']],
                'answers' => ['a1' => $correctAnswers[0]],
                'explanations' => [
                    $correctAnswers[0] => $entry['explanation'],
                ],
                'option_markers' => [
                    $correctAnswers[0] => 'a1',
                ],
                'all_correct_answers' => $correctAnswers,
            ];
        }

        $this->seedQuestionData($questionPayloads, $meta);
    }
}
