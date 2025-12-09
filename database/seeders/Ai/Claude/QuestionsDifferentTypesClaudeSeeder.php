<?php

namespace Database\Seeders\Ai\Claude;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class QuestionsDifferentTypesClaudeSeeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Questions - Different Types'])->id;

        $sourceId = Source::firstOrCreate(['name' => 'AI generated: Questions: Different types (SET 1)'])->id;

        $themeTag = Tag::firstOrCreate(
            ['name' => 'Question Formation Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTags = [
            'yes_no' => Tag::firstOrCreate(['name' => 'Yes/No Questions'], ['category' => 'English Grammar Detail'])->id,
            'wh_questions' => Tag::firstOrCreate(['name' => 'Wh-Questions'], ['category' => 'English Grammar Detail'])->id,
            'subject_questions' => Tag::firstOrCreate(['name' => 'Subject Questions'], ['category' => 'English Grammar Detail'])->id,
            'indirect_questions' => Tag::firstOrCreate(['name' => 'Indirect Questions'], ['category' => 'English Grammar Detail'])->id,
            'tag_questions' => Tag::firstOrCreate(['name' => 'Tag Questions'], ['category' => 'English Grammar Detail'])->id,
        ];

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $questions = $this->buildQuestions();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $question) {
            $uuid = $this->generateQuestionUuid($question['level'], $index, $question['question']);

            $answers = [];
            $optionMarkers = [];
            
            foreach ($question['options'] as $option) {
                $optionMarkers[$option] = 'a1';
            }
            
            $answers[] = [
                'marker' => 'a1',
                'answer' => $question['answers']['a1'],
                'verb_hint' => $this->normalizeHint($question['verb_hint'] ?? null),
            ];

            $tagIds = [$themeTag];
            if (isset($question['detail']) && isset($detailTags[$question['detail']])) {
                $tagIds[] = $detailTags[$question['detail']];
            }

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 2,
                'type' => 0,
                'level' => $question['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answers,
                'options' => $question['options'],
                'variants' => [],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $question['answers'],
                'option_markers' => $optionMarkers,
                'hints' => $question['hints'],
                'explanations' => $question['explanations'],
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildQuestions(): array
    {
        $questions = [];
        
        // A1 Level - 12 questions
        $questions = array_merge($questions, $this->getA1Questions());
        $questions = array_merge($questions, $this->getA2Questions());
        $questions = array_merge($questions, $this->getB1Questions());
        $questions = array_merge($questions, $this->getB2Questions());
        $questions = array_merge($questions, $this->getC1Questions());
        $questions = array_merge($questions, $this->getC2Questions());
        
        return $questions;
    }

    private function getA1Questions(): array
    {
        return [
            [
                'level' => 'A1',
                'question' => "{a1} you like pizza?",
                'answers' => ['a1' => 'Do'],
                'options' => ['Do', 'Does', 'Are', 'Is'],
                'verb_hint' => 'do',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Для формування загального питання у Present Simple з you використовуємо Do. Формула: Do + you + базова форма дієслова."],
                'explanations' => [
                    'Do' => "✅ Правильно! Для формування загального питання у Present Simple з you використовуємо Do. Приклад: Do you like pizza?",
                    'Does' => "❌ Неправильно. Does використовується з he/she/it, а не з you. Правильна відповідь: Do you like pizza?",
                    'Are' => "❌ Неправильно. Are використовується з дієсловом to be, а не з дієсловами дії типу like. Правильна відповідь: Do you like pizza?",
                    'Is' => "❌ Неправильно. Is використовується з дієсловом to be та he/she/it. Правильна відповідь: Do you like pizza?",
                ],
            ],
            [
                'level' => 'A1',
                'question' => "{a1} she speak English?",
                'answers' => ['a1' => 'Does'],
                'options' => ['Does', 'Do', 'Is', 'Can'],
                'verb_hint' => 'do/does',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Для формування загального питання у Present Simple з she використовуємо Does. Формула: Does + she + базова форма дієслова."],
                'explanations' => [
                    'Does' => "✅ Правильно! Для формування загального питання у Present Simple з she використовуємо Does. Приклад: Does she speak English?",
                    'Do' => "❌ Неправильно. Do використовується з I/you/we/they, а не з she. Правильна відповідь: Does she speak English?",
                    'Is' => "❌ Неправильно. Is використовується з дієсловом to be. Правильна відповідь: Does she speak English?",
                    'Can' => "❌ Неправильно. Can — це модальне дієслово з іншим значенням. Правильна відповідь: Does she speak English?",
                ],
            ],
            [
                'level' => 'A1',
                'question' => "What {a1} your name?",
                'answers' => ['a1' => 'is'],
                'options' => ['is', 'are', 'does', 'do'],
                'verb_hint' => 'be',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Питання про ім'я формується з допоміжним дієсловом is. Формула: What + is + your name?"],
                'explanations' => [
                    'is' => "✅ Правильно! Питання про ім'я формується з is. Приклад: What is your name?",
                    'are' => "❌ Неправильно. Are використовується з множиною, але your name — однина. Правильна відповідь: What is your name?",
                    'does' => "❌ Неправильно. Does використовується з дієсловами дії. Правильна відповідь: What is your name?",
                    'do' => "❌ Неправильно. Do не використовується з конструкцією your name is. Правильна відповідь: What is your name?",
                ],
            ],
            [
                'level' => 'A1',
                'question' => "{a1} they at home?",
                'answers' => ['a1' => 'Are'],
                'options' => ['Are', 'Is', 'Do', 'Does'],
                'verb_hint' => 'be',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Для формування питання з дієсловом to be та підметом they використовуємо Are."],
                'explanations' => [
                    'Are' => "✅ Правильно! З they використовуємо Are. Приклад: Are they at home?",
                    'Is' => "❌ Неправильно. Is використовується з he/she/it. Правильна відповідь: Are they at home?",
                    'Do' => "❌ Неправильно. Do використовується з дієсловами дії. Правильна відповідь: Are they at home?",
                    'Does' => "❌ Неправильно. Does не використовується з to be. Правильна відповідь: Are they at home?",
                ],
            ],
            [
                'level' => 'A1',
                'question' => "Where {a1} you live?",
                'answers' => ['a1' => 'do'],
                'options' => ['do', 'does', 'are', 'is'],
                'verb_hint' => 'do',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Для Wh-питання у Present Simple з you використовуємо do. Формула: Where + do + you + базова форма дієслова."],
                'explanations' => [
                    'do' => "✅ Правильно! З you використовуємо do. Приклад: Where do you live?",
                    'does' => "❌ Неправильно. Does використовується з he/she/it. Правильна відповідь: Where do you live?",
                    'are' => "❌ Неправильно. Are використовується з дієсловом to be. Правильна відповідь: Where do you live?",
                    'is' => "❌ Неправильно. Is не використовується з you live. Правильна відповідь: Where do you live?",
                ],
            ],
            [
                'level' => 'A1',
                'question' => "{a1} you go to school yesterday?",
                'answers' => ['a1' => 'Did'],
                'options' => ['Did', 'Do', 'Was', 'Were'],
                'verb_hint' => 'do (past)',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Для формування загального питання у Past Simple використовуємо Did з усіма підметами."],
                'explanations' => [
                    'Did' => "✅ Правильно! У Past Simple використовуємо Did. Приклад: Did you go to school yesterday?",
                    'Do' => "❌ Неправильно. Do використовується у Present Simple. Правильна відповідь: Did you go to school yesterday?",
                    'Was' => "❌ Неправильно. Was використовується з to be, а не з go. Правильна відповідь: Did you go to school yesterday?",
                    'Were' => "❌ Неправильно. Were не використовується з дієсловом go. Правильна відповідь: Did you go to school yesterday?",
                ],
            ],
            [
                'level' => 'A1',
                'question' => "When {a1} she arrive?",
                'answers' => ['a1' => 'did'],
                'options' => ['did', 'does', 'was', 'is'],
                'verb_hint' => 'do (past)',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Для Wh-питання у Past Simple використовуємо did."],
                'explanations' => [
                    'did' => "✅ Правильно! У минулому часі використовуємо did. Приклад: When did she arrive?",
                    'does' => "❌ Неправильно. Does використовується у Present Simple. Правильна відповідь: When did she arrive?",
                    'was' => "❌ Неправильно. Was — це форма to be. Правильна відповідь: When did she arrive?",
                    'is' => "❌ Неправильно. Is — це Present Simple. Правильна відповідь: When did she arrive?",
                ],
            ],
            [
                'level' => 'A1',
                'question' => "Who {a1} this book?",
                'answers' => ['a1' => 'wrote'],
                'options' => ['wrote', 'write', 'did write', 'writes'],
                'verb_hint' => 'write',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "У питаннях про підмет не використовуємо допоміжне дієслово did."],
                'explanations' => [
                    'wrote' => "✅ Правильно! У питаннях про підмет використовуємо дієслово у Past Simple без did. Приклад: Who wrote this book?",
                    'write' => "❌ Неправильно. Write — це базова форма, потрібен минулий час. Правильна відповідь: Who wrote this book?",
                    'did write' => "❌ Неправильно. У питаннях про підмет не використовуємо did. Правильна відповідь: Who wrote this book?",
                    'writes' => "❌ Неправильно. Writes — це Present Simple. Правильна відповідь: Who wrote this book?",
                ],
            ],
            [
                'level' => 'A1',
                'question' => "You like coffee, {a1}?",
                'answers' => ['a1' => "don't you"],
                'options' => ["don't you", "do you", "aren't you", "are you"],
                'verb_hint' => 'Use negative tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Після ствердного речення у Present Simple додаємо заперечний tag."],
                'explanations' => [
                    "don't you" => "✅ Правильно! Після ствердного речення потрібен заперечний tag. Приклад: You like coffee, don't you?",
                    "do you" => "❌ Неправильно. Після ствердного речення потрібен заперечний tag. Правильна відповідь: You like coffee, don't you?",
                    "aren't you" => "❌ Неправильно. Aren't використовується з to be. Правильна відповідь: You like coffee, don't you?",
                    "are you" => "❌ Неправильно. Are використовується з to be. Правильна відповідь: You like coffee, don't you?",
                ],
            ],
            [
                'level' => 'A1',
                'question' => "{a1} you swim?",
                'answers' => ['a1' => 'Can'],
                'options' => ['Can', 'Do', 'Are', 'Does'],
                'verb_hint' => 'modal verb',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Для питання про здатність використовуємо модальне дієслово Can."],
                'explanations' => [
                    'Can' => "✅ Правильно! Для питання про здатність використовуємо Can. Приклад: Can you swim?",
                    'Do' => "❌ Неправильно. Do використовується зі звичайними дієсловами. Правильна відповідь: Can you swim?",
                    'Are' => "❌ Неправильно. Are використовується з to be. Правильна відповідь: Can you swim?",
                    'Does' => "❌ Неправильно. Does не використовується для питань про здатність. Правильна відповідь: Can you swim?",
                ],
            ],
            [
                'level' => 'A1',
                'question' => "How {a1} you today?",
                'answers' => ['a1' => 'are'],
                'options' => ['are', 'is', 'do', 'does'],
                'verb_hint' => 'be',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Питання про стан використовує дієслово to be. З you використовується are."],
                'explanations' => [
                    'are' => "✅ Правильно! Питання про стан використовує to be. Приклад: How are you today?",
                    'is' => "❌ Неправильно. Is використовується з he/she/it. Правильна відповідь: How are you today?",
                    'do' => "❌ Неправильно. How are you — це усталений вираз із to be. Правильна відповідь: How are you today?",
                    'does' => "❌ Неправильно. Does не використовується у виразі How are you. Правильна відповідь: How are you today?",
                ],
            ],
            [
                'level' => 'A1',
                'question' => "{a1} it cold last night?",
                'answers' => ['a1' => 'Was'],
                'options' => ['Was', 'Were', 'Did', 'Is'],
                'verb_hint' => 'be (past)',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Для питання у минулому часі з дієсловом to be та підметом it використовуємо was."],
                'explanations' => [
                    'Was' => "✅ Правильно! З it у минулому використовуємо was. Приклад: Was it cold last night?",
                    'Were' => "❌ Неправильно. Were використовується з you/we/they. Правильна відповідь: Was it cold last night?",
                    'Did' => "❌ Неправильно. Did використовується з дієсловами дії. Правильна відповідь: Was it cold last night?",
                    'Is' => "❌ Неправильно. Is — це Present Simple. Правильна відповідь: Was it cold last night?",
                ],
            ],
        ];
    }

    // Placeholder methods for other levels - to be implemented
        private function getA2Questions(): array
    {
        return [
            [
                'level' => 'A2',
                'question' => "Why {a1} he always late?",
                'answers' => ['a1' => 'is'],
                'options' => ['is', 'does', 'do', 'are'],
                'verb_hint' => 'be',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Питання про причину стану з to be. З he використовується is."],
                'explanations' => [
                    'is' => "✅ Правильно! Для питання про причину стану з he використовуємо is. Приклад: Why is he always late?",
                    'does' => "❌ Неправильно. Does використовується з дієсловами дії, але late — це прикметник. Правильна відповідь: Why is he always late?",
                    'do' => "❌ Неправильно. Do не використовується з прикметником late. Правильна відповідь: Why is he always late?",
                    'are' => "❌ Неправильно. Are використовується з you/we/they. Правильна відповідь: Why is he always late?",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "Can you tell me where {a1}?",
                'answers' => ['a1' => 'the station is'],
                'options' => ['the station is', 'is the station', 'does the station', 'the station are'],
                'verb_hint' => 'Use statement order',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "У непрямих питаннях використовується прямий порядок слів: підмет + дієслово."],
                'explanations' => [
                    'the station is' => "✅ Правильно! У непрямих питаннях прямий порядок слів. Приклад: Can you tell me where the station is?",
                    'is the station' => "❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: Can you tell me where the station is?",
                    'does the station' => "❌ Неправильно. Does не використовується з to be. Правильна відповідь: Can you tell me where the station is?",
                    'the station are' => "❌ Неправильно. The station — однина, потрібен is. Правильна відповідь: Can you tell me where the station is?",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "She doesn't speak French, {a1}?",
                'answers' => ['a1' => 'does she'],
                'options' => ['does she', "doesn't she", 'is she', "isn't she"],
                'verb_hint' => 'Use positive tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Після заперечного речення додаємо ствердний tag."],
                'explanations' => [
                    'does she' => "✅ Правильно! Після заперечного речення потрібен ствердний tag. Приклад: She doesn't speak French, does she?",
                    "doesn't she" => "❌ Неправильно. Після заперечного речення потрібен ствердний tag. Правильна відповідь: She doesn't speak French, does she?",
                    'is she' => "❌ Неправильно. Is використовується з to be, але тут speak. Правильна відповідь: She doesn't speak French, does she?",
                    "isn't she" => "❌ Неправильно. Isn't використовується з to be. Правильна відповідь: She doesn't speak French, does she?",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "How many languages {a1} she speak?",
                'answers' => ['a1' => 'does'],
                'options' => ['does', 'do', 'is', 'are'],
                'verb_hint' => 'do/does',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Для питання про кількість у Present Simple з she використовуємо does."],
                'explanations' => [
                    'does' => "✅ Правильно! З she використовуємо does. Приклад: How many languages does she speak?",
                    'do' => "❌ Неправильно. Do використовується з I/you/we/they. Правильна відповідь: How many languages does she speak?",
                    'is' => "❌ Неправильно. Is не використовується з дієсловом speak. Правильна відповідь: How many languages does she speak?",
                    'are' => "❌ Неправильно. Are не використовується з she speak. Правильна відповідь: How many languages does she speak?",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "{a1} they working now?",
                'answers' => ['a1' => 'Are'],
                'options' => ['Are', 'Is', 'Do', 'Does'],
                'verb_hint' => 'be + -ing',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Present Continuous використовує to be. З they використовується are."],
                'explanations' => [
                    'Are' => "✅ Правильно! У Present Continuous з they використовуємо are. Приклад: Are they working now?",
                    'Is' => "❌ Неправильно. Is використовується з he/she/it. Правильна відповідь: Are they working now?",
                    'Do' => "❌ Неправильно. Do використовується у Present Simple. Правильна відповідь: Are they working now?",
                    'Does' => "❌ Неправильно. Does не використовується у Present Continuous. Правильна відповідь: Are they working now?",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "What {a1} you doing?",
                'answers' => ['a1' => 'are'],
                'options' => ['are', 'is', 'do', 'does'],
                'verb_hint' => 'be + -ing',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Present Continuous для дії зараз. З you використовується are."],
                'explanations' => [
                    'are' => "✅ Правильно! У Present Continuous з you використовуємо are. Приклад: What are you doing?",
                    'is' => "❌ Неправильно. Is використовується з he/she/it. Правильна відповідь: What are you doing?",
                    'do' => "❌ Неправильно. Do використовується у Present Simple. Правильна відповідь: What are you doing?",
                    'does' => "❌ Неправильно. Does використовується у Present Simple з he/she/it. Правильна відповідь: What are you doing?",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "Who {a1} the window?",
                'answers' => ['a1' => 'broke'],
                'options' => ['broke', 'did break', 'break', 'broken'],
                'verb_hint' => 'break',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "У питаннях про підмет використовуємо дієслово у Past Simple без did."],
                'explanations' => [
                    'broke' => "✅ Правильно! У питаннях про підмет використовуємо Past Simple без did. Приклад: Who broke the window?",
                    'did break' => "❌ Неправильно. У питаннях про підмет не використовуємо did. Правильна відповідь: Who broke the window?",
                    'break' => "❌ Неправильно. Break — це базова форма, потрібен Past Simple. Правильна відповідь: Who broke the window?",
                    'broken' => "❌ Неправильно. Broken — це V3, потрібна V2 форма. Правильна відповідь: Who broke the window?",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "{a1} you come to the party?",
                'answers' => ['a1' => 'Will'],
                'options' => ['Will', 'Do', 'Are', 'Did'],
                'verb_hint' => 'future',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Will використовується для майбутнього часу."],
                'explanations' => [
                    'Will' => "✅ Правильно! Для майбутньої дії використовуємо will. Приклад: Will you come to the party?",
                    'Do' => "❌ Неправильно. Do використовується у Present Simple. Правильна відповідь: Will you come to the party?",
                    'Are' => "❌ Неправильно. Are не використовується для Future Simple. Правильна відповідь: Will you come to the party?",
                    'Did' => "❌ Неправильно. Did використовується у Past Simple. Правильна відповідь: Will you come to the party?",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "How much {a1} it cost?",
                'answers' => ['a1' => 'does'],
                'options' => ['does', 'do', 'is', 'are'],
                'verb_hint' => 'do/does',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Для питання про ціну з it використовуємо does."],
                'explanations' => [
                    'does' => "✅ Правильно! З it використовуємо does. Приклад: How much does it cost?",
                    'do' => "❌ Неправильно. Do використовується з I/you/we/they. Правильна відповідь: How much does it cost?",
                    'is' => "❌ Неправильно. Is не використовується з дієсловом cost. Правильна відповідь: How much does it cost?",
                    'are' => "❌ Неправильно. Are не використовується з it cost. Правильна відповідь: How much does it cost?",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "Do you know what time {a1}?",
                'answers' => ['a1' => 'the train left'],
                'options' => ['the train left', 'did the train leave', 'left the train', 'the train leave'],
                'verb_hint' => 'Use statement order',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "У непрямих питаннях прямий порядок слів без did."],
                'explanations' => [
                    'the train left' => "✅ Правильно! У непрямих питаннях прямий порядок слів без did. Приклад: Do you know what time the train left?",
                    'did the train leave' => "❌ Неправильно. У непрямих питаннях не використовується did. Правильна відповідь: Do you know what time the train left?",
                    'left the train' => "❌ Неправильно. Неправильний порядок слів. Правильна відповідь: Do you know what time the train left?",
                    'the train leave' => "❌ Неправильно. Leave — це базова форма, потрібен Past Simple. Правильна відповідь: Do you know what time the train left?",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "Whose book {a1} this?",
                'answers' => ['a1' => 'is'],
                'options' => ['is', 'are', 'does', 'do'],
                'verb_hint' => 'be',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Питання про власника з to be. Book — однина, тому is."],
                'explanations' => [
                    'is' => "✅ Правильно! Для питання про власника з однинним іменником використовуємо is. Приклад: Whose book is this?",
                    'are' => "❌ Неправильно. Are використовується з множиною. Правильна відповідь: Whose book is this?",
                    'does' => "❌ Неправильно. Does не використовується з конструкцією whose book. Правильна відповідь: Whose book is this?",
                    'do' => "❌ Неправильно. Do не використовується з конструкцією whose book. Правильна відповідь: Whose book is this?",
                ],
            ],
            [
                'level' => 'A2',
                'question' => "They visited Paris last year, {a1}?",
                'answers' => ['a1' => "didn't they"],
                'options' => ["didn't they", 'did they', "weren't they", 'were they'],
                'verb_hint' => 'Use negative tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Після ствердного речення у Past Simple додаємо заперечний tag з didn't."],
                'explanations' => [
                    "didn't they" => "✅ Правильно! Після ствердного речення потрібен заперечний tag. Приклад: They visited Paris last year, didn't they?",
                    'did they' => "❌ Неправильно. Після ствердного речення потрібен заперечний tag. Правильна відповідь: They visited Paris last year, didn't they?",
                    "weren't they" => "❌ Неправильно. Weren't використовується з to be, але тут visited. Правильна відповідь: They visited Paris last year, didn't they?",
                    'were they' => "❌ Неправильно. Were використовується з to be. Правильна відповідь: They visited Paris last year, didn't they?",
                ],
            ],
        ];
    }
    private function getB1Questions(): array { return []; }
    private function getB2Questions(): array { return []; }
    private function getC1Questions(): array { return []; }
    private function getC2Questions(): array { return []; }
}
