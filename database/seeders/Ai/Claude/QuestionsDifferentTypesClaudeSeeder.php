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
                'verb_hint' => 'auxiliary verb for questions',
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
                'verb_hint' => 'auxiliary verb for 3rd person',
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
                'verb_hint' => 'auxiliary for present tense questions',
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
                'verb_hint' => 'auxiliary for past tense questions',
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
                'verb_hint' => 'past tense auxiliary',
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
                'verb_hint' => 'auxiliary for 3rd person singular',
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
                'verb_hint' => 'auxiliary for it/he/she',
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
    private function getB1Questions(): array
    {
        return [
            // 1. Present Perfect - Have/Has
            [
                'level' => 'B1',
                'question' => "{a1} you ever been to London?",
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Has', 'Did', 'Do'],
                'verb_hint' => 'present perfect auxiliary',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Для питання про життєвий досвід у Present Perfect з you використовуємо Have. Формула: Have + you + ever + V3."],
                'explanations' => [
                    'Have' => "✅ Правильно! Для питання про життєвий досвід використовуємо Present Perfect з Have. Приклад: Have you ever been to London?",
                    'Has' => "❌ Неправильно. Has використовується з he/she/it, а не з you. Правильна відповідь: Have you ever been to London?",
                    'Did' => "❌ Неправильно. Did використовується у Past Simple, але це Present Perfect. Правильна відповідь: Have you ever been to London?",
                    'Do' => "❌ Неправильно. Do використовується у Present Simple. Правильна відповідь: Have you ever been to London?",
                ],
            ],
            
            // 2. Negative Question - Present Simple
            [
                'level' => 'B1',
                'question' => "{a1} he know about the meeting?",
                'answers' => ['a1' => "Doesn't"],
                'options' => ["Doesn't", "Don't", "Isn't", "Didn't"],
                'verb_hint' => 'negative question form',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Заперечне питання у Present Simple з he формується з Doesn't. Виражає здивування або очікування."],
                'explanations' => [
                    "Doesn't" => "✅ Правильно! Заперечне питання з he використовує Doesn't. Приклад: Doesn't he know about the meeting?",
                    "Don't" => "❌ Неправильно. Don't використовується з I/you/we/they. Правильна відповідь: Doesn't he know about the meeting?",
                    "Isn't" => "❌ Неправильно. Isn't використовується з to be. Правильна відповідь: Doesn't he know about the meeting?",
                    "Didn't" => "❌ Неправильно. Didn't — це минулий час. Правильна відповідь: Doesn't he know about the meeting?",
                ],
            ],
            
            // 3. Alternative Question with "or"
            [
                'level' => 'B1',
                'question' => "Do you prefer coffee {a1} tea?",
                'answers' => ['a1' => 'or'],
                'options' => ['or', 'and', 'but', 'nor'],
                'verb_hint' => 'choice connector',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Альтернативні питання використовують сполучник or (або) для вибору між варіантами."],
                'explanations' => [
                    'or' => "✅ Правильно! Альтернативні питання використовують or для вибору. Приклад: Do you prefer coffee or tea?",
                    'and' => "❌ Неправильно. And означає 'і', але тут потрібен вибір. Правильна відповідь: Do you prefer coffee or tea?",
                    'but' => "❌ Неправильно. But означає 'але', не підходить для вибору. Правильна відповідь: Do you prefer coffee or tea?",
                    'nor' => "❌ Неправильно. Nor використовується у заперечних реченнях. Правильна відповідь: Do you prefer coffee or tea?",
                ],
            ],
            
            // 4. Present Perfect Continuous - How long
            [
                'level' => 'B1',
                'question' => "How long {a1} you been studying English?",
                'answers' => ['a1' => 'have'],
                'options' => ['have', 'has', 'are', 'do'],
                'verb_hint' => 'perfect continuous auxiliary',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Для питання про тривалість дії до теперішнього моменту використовуємо Present Perfect Continuous з have."],
                'explanations' => [
                    'have' => "✅ Правильно! How long + have + you + been + V-ing для тривалості дії. Приклад: How long have you been studying English?",
                    'has' => "❌ Неправильно. Has використовується з he/she/it. Правильна відповідь: How long have you been studying English?",
                    'are' => "❌ Неправильно. Are використовується у Present Continuous, але це Perfect Continuous. Правильна відповідь: How long have you been studying English?",
                    'do' => "❌ Неправильно. Do не використовується у Perfect Continuous. Правильна відповідь: How long have you been studying English?",
                ],
            ],
            
            // 5. Tag Question - Present Perfect
            [
                'level' => 'B1',
                'question' => "They haven't arrived yet, {a1}?",
                'answers' => ['a1' => 'have they'],
                'options' => ['have they', "haven't they", 'did they', "didn't they"],
                'verb_hint' => 'positive tag after negative',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Після заперечного речення у Present Perfect додаємо ствердний tag."],
                'explanations' => [
                    'have they' => "✅ Правильно! Після haven't потрібен ствердний tag have. Приклад: They haven't arrived yet, have they?",
                    "haven't they" => "❌ Неправильно. Після заперечення потрібен ствердний tag. Правильна відповідь: They haven't arrived yet, have they?",
                    'did they' => "❌ Неправильно. Did використовується з Past Simple. Правильна відповідь: They haven't arrived yet, have they?",
                    "didn't they" => "❌ Неправильно. Didn't — це Past Simple. Правильна відповідь: They haven't arrived yet, have they?",
                ],
            ],
            
            // 6. Past Continuous - Were/Was
            [
                'level' => 'B1',
                'question' => "What {a1} you doing at 8 pm yesterday?",
                'answers' => ['a1' => 'were'],
                'options' => ['were', 'was', 'did', 'have'],
                'verb_hint' => 'past continuous auxiliary',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Past Continuous з you використовує were. Формула: What + were + you + V-ing."],
                'explanations' => [
                    'were' => "✅ Правильно! Past Continuous з you використовує were. Приклад: What were you doing at 8 pm yesterday?",
                    'was' => "❌ Неправильно. Was використовується з I/he/she/it. Правильна відповідь: What were you doing at 8 pm yesterday?",
                    'did' => "❌ Неправильно. Did використовується у Past Simple. Правильна відповідь: What were you doing at 8 pm yesterday?",
                    'have' => "❌ Неправильно. Have не використовується у Past Continuous. Правильна відповідь: What were you doing at 8 pm yesterday?",
                ],
            ],
            
            // 7. Indirect Question - Present Perfect
            [
                'level' => 'B1',
                'question' => "Can you tell me where {a1}?",
                'answers' => ['a1' => 'she has gone'],
                'options' => ['she has gone', 'has she gone', 'she gone has', 'gone she has'],
                'verb_hint' => 'statement order in indirect question',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "У непрямих питаннях використовується прямий порядок слів: підмет + has + V3."],
                'explanations' => [
                    'she has gone' => "✅ Правильно! У непрямих питаннях прямий порядок слів. Приклад: Can you tell me where she has gone?",
                    'has she gone' => "❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: Can you tell me where she has gone?",
                    'she gone has' => "❌ Неправильно. Неправильний порядок слів. Правильна відповідь: Can you tell me where she has gone?",
                    'gone she has' => "❌ Неправильно. Повністю неправильний порядок. Правильна відповідь: Can you tell me where she has gone?",
                ],
            ],
            
            // 8. Going to - Future
            [
                'level' => 'B1',
                'question' => "{a1} she going to join us?",
                'answers' => ['a1' => 'Is'],
                'options' => ['Is', 'Are', 'Does', 'Will'],
                'verb_hint' => 'be + going to',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Питання з be going to використовує is для she. Формула: Is + she + going to + V."],
                'explanations' => [
                    'Is' => "✅ Правильно! З she та going to використовуємо is. Приклад: Is she going to join us?",
                    'Are' => "❌ Неправильно. Are використовується з you/we/they. Правильна відповідь: Is she going to join us?",
                    'Does' => "❌ Неправильно. Does не використовується з going to. Правильна відповідь: Is she going to join us?",
                    'Will' => "❌ Неправильно. Will — інша форма майбутнього, тут be going to. Правильна відповідь: Is she going to join us?",
                ],
            ],
            
            // 9. Subject Question - Who
            [
                'level' => 'B1',
                'question' => "Who {a1} the best answer?",
                'answers' => ['a1' => 'gave'],
                'options' => ['gave', 'did give', 'has given', 'gives'],
                'verb_hint' => 'past tense verb',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "У питаннях про підмет не використовуємо допоміжне дієслово. Просто дієслово у Past Simple."],
                'explanations' => [
                    'gave' => "✅ Правильно! У питаннях про підмет використовуємо просто V2 без did. Приклад: Who gave the best answer?",
                    'did give' => "❌ Неправильно. У питаннях про підмет не використовуємо did. Правильна відповідь: Who gave the best answer?",
                    'has given' => "❌ Неправильно. Контекст вимагає Past Simple, не Present Perfect. Правильна відповідь: Who gave the best answer?",
                    'gives' => "❌ Неправильно. Gives — це Present Simple. Правильна відповідь: Who gave the best answer?",
                ],
            ],
            
            // 10. Negative Question - Past Simple
            [
                'level' => 'B1',
                'question' => "{a1} you see the sign?",
                'answers' => ['a1' => "Didn't"],
                'options' => ["Didn't", "Don't", "Haven't", "Weren't"],
                'verb_hint' => 'negative past question',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Заперечне питання у Past Simple використовує Didn't. Виражає здивування."],
                'explanations' => [
                    "Didn't" => "✅ Правильно! Заперечне питання у Past Simple з Didn't. Приклад: Didn't you see the sign?",
                    "Don't" => "❌ Неправильно. Don't — це Present Simple. Правильна відповідь: Didn't you see the sign?",
                    "Haven't" => "❌ Неправильно. Haven't — це Present Perfect. Правильна відповідь: Didn't you see the sign?",
                    "Weren't" => "❌ Неправильно. Weren't — це to be у Past. Правильна відповідь: Didn't you see the sign?",
                ],
            ],
            
            // 11. Alternative Question - Is/Are
            [
                'level' => 'B1',
                'question' => "Is it black {a1} white?",
                'answers' => ['a1' => 'or'],
                'options' => ['or', 'and', 'but', 'so'],
                'verb_hint' => 'alternative connector',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Альтернативні питання пропонують вибір через or."],
                'explanations' => [
                    'or' => "✅ Правильно! Альтернативні питання використовують or. Приклад: Is it black or white?",
                    'and' => "❌ Неправильно. And з'єднує, але не дає вибору. Правильна відповідь: Is it black or white?",
                    'but' => "❌ Неправильно. But — це протиставлення. Правильна відповідь: Is it black or white?",
                    'so' => "❌ Неправильно. So означає 'тому'. Правильна відповідь: Is it black or white?",
                ],
            ],
            
            // 12. How often - Frequency question
            [
                'level' => 'B1',
                'question' => "How often {a1} they meet?",
                'answers' => ['a1' => 'do'],
                'options' => ['do', 'does', 'are', 'have'],
                'verb_hint' => 'present simple auxiliary',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Питання про частоту у Present Simple з they використовує do."],
                'explanations' => [
                    'do' => "✅ Правильно! How often + do + they + V для питання про частоту. Приклад: How often do they meet?",
                    'does' => "❌ Неправильно. Does використовується з he/she/it. Правильна відповідь: How often do they meet?",
                    'are' => "❌ Неправильно. Are не використовується з дієсловом meet. Правильна відповідь: How often do they meet?",
                    'have' => "❌ Неправильно. Have не використовується у Present Simple питаннях. Правильна відповідь: How often do they meet?",
                ],
            ],
        ];
    }
    private function getB2Questions(): array
    {
        return [
            // 1. Past Perfect
            [
                'level' => 'B2',
                'question' => "{a1} you finished the project before the deadline?",
                'answers' => ['a1' => 'Had'],
                'options' => ['Had', 'Have', 'Did', 'Were'],
                'verb_hint' => 'past perfect auxiliary',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Для питання про дію до певного моменту у минулому використовуємо Past Perfect з Had."],
                'explanations' => [
                    'Had' => "✅ Правильно! Past Perfect використовує had для дії до моменту у минулому. Приклад: Had you finished the project before the deadline?",
                    'Have' => "❌ Неправильно. Have — це Present Perfect. Правильна відповідь: Had you finished the project before the deadline?",
                    'Did' => "❌ Неправильно. Did — це Past Simple. Правильна відповідь: Had you finished the project before the deadline?",
                    'Were' => "❌ Неправильно. Were — це to be у Past. Правильна відповідь: Had you finished the project before the deadline?",
                ],
            ],
            
            // 2. Modal Perfect - Should have
            [
                'level' => 'B2',
                'question' => "What {a1} I done differently?",
                'answers' => ['a1' => 'should have'],
                'options' => ['should have', 'should', 'have', 'had'],
                'verb_hint' => 'modal + perfect',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Для питання про те, що треба було зробити інакше, використовуємо should have + V3."],
                'explanations' => [
                    'should have' => "✅ Правильно! Should have для критики/поради про минуле. Приклад: What should I have done differently?",
                    'should' => "❌ Неправильно. Should без have — про майбутнє/теперішнє. Правильна відповідь: What should I have done differently?",
                    'have' => "❌ Неправильно. Have без should не передає модальне значення. Правильна відповідь: What should I have done differently?",
                    'had' => "❌ Неправильно. Had — це Past Perfect, але тут модальний перфект. Правильна відповідь: What should I have done differently?",
                ],
            ],
            
            // 3. Tag Question - Modal Perfect
            [
                'level' => 'B2',
                'question' => "He could have told us earlier, {a1}?",
                'answers' => ['a1' => "couldn't he"],
                'options' => ["couldn't he", 'could he', "didn't he", "hasn't he"],
                'verb_hint' => 'negative tag with modal',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Після ствердного речення з could have додаємо заперечний tag couldn't."],
                'explanations' => [
                    "couldn't he" => "✅ Правильно! Tag від could have — couldn't. Приклад: He could have told us earlier, couldn't he?",
                    'could he' => "❌ Неправильно. Після ствердження потрібен заперечний tag. Правильна відповідь: He could have told us earlier, couldn't he?",
                    "didn't he" => "❌ Неправильно. Didn't використовується з Past Simple. Правильна відповідь: He could have told us earlier, couldn't he?",
                    "hasn't he" => "❌ Неправильно. Hasn't — це Present Perfect. Правильна відповідь: He could have told us earlier, couldn't he?",
                ],
            ],
            
            // 4. Negative Question - Present Perfect
            [
                'level' => 'B2',
                'question' => "{a1} you heard the news yet?",
                'answers' => ['a1' => "Haven't"],
                'options' => ["Haven't", "Didn't", "Don't", "Aren't"],
                'verb_hint' => 'negative perfect question',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Заперечне питання у Present Perfect з you використовує Haven't. Виражає здивування."],
                'explanations' => [
                    "Haven't" => "✅ Правильно! Заперечне питання у Present Perfect. Приклад: Haven't you heard the news yet?",
                    "Didn't" => "❌ Неправильно. Didn't — це Past Simple. Правильна відповідь: Haven't you heard the news yet?",
                    "Don't" => "❌ Неправильно. Don't — це Present Simple. Правильна відповідь: Haven't you heard the news yet?",
                    "Aren't" => "❌ Неправильно. Aren't — це to be. Правильна відповідь: Haven't you heard the news yet?",
                ],
            ],
            
            // 5. Passive Voice - Present Perfect
            [
                'level' => 'B2',
                'question' => "{a1} the documents been reviewed?",
                'answers' => ['a1' => 'Have'],
                'options' => ['Have', 'Has', 'Were', 'Did'],
                'verb_hint' => 'perfect passive auxiliary',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Present Perfect Passive з множинним підметом використовує Have been + V3."],
                'explanations' => [
                    'Have' => "✅ Правильно! Present Perfect Passive з множиною. Приклад: Have the documents been reviewed?",
                    'Has' => "❌ Неправильно. Has — для однини. Правильна відповідь: Have the documents been reviewed?",
                    'Were' => "❌ Неправильно. Were — це Past Simple Passive. Правильна відповідь: Have the documents been reviewed?",
                    'Did' => "❌ Неправильно. Did не використовується з passive been. Правильна відповідь: Have the documents been reviewed?",
                ],
            ],
            
            // 6. Indirect Question - Past Perfect
            [
                'level' => 'B2',
                'question' => "Do you know why {a1}?",
                'answers' => ['a1' => 'she had left early'],
                'options' => ['she had left early', 'had she left early', 'she left early had', 'early left she had'],
                'verb_hint' => 'statement order with had',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "У непрямих питаннях прямий порядок слів: підмет + had + V3."],
                'explanations' => [
                    'she had left early' => "✅ Правильно! У непрямих питаннях прямий порядок слів. Приклад: Do you know why she had left early?",
                    'had she left early' => "❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: Do you know why she had left early?",
                    'she left early had' => "❌ Неправильно. Неправильний порядок слів. Правильна відповідь: Do you know why she had left early?",
                    'early left she had' => "❌ Неправильно. Повністю неправильний порядок. Правильна відповідь: Do you know why she had left early?",
                ],
            ],
            
            // 7. Subject Question - What with Present Perfect
            [
                'level' => 'B2',
                'question' => "What {a1} happened to your car?",
                'answers' => ['a1' => 'has'],
                'options' => ['has', 'have', 'did', 'was'],
                'verb_hint' => 'perfect tense for result',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "У питаннях про підмет у Present Perfect використовуємо has (What — однина)."],
                'explanations' => [
                    'has' => "✅ Правильно! What як підмет — однина, використовуємо has. Приклад: What has happened to your car?",
                    'have' => "❌ Неправильно. Have — для множини. Правильна відповідь: What has happened to your car?",
                    'did' => "❌ Неправильно. Did — це Past Simple. Правильна відповідь: What has happened to your car?",
                    'was' => "❌ Неправильно. Was — це to be. Правильна відповідь: What has happened to your car?",
                ],
            ],
            
            // 8. Alternative Question - Would you like
            [
                'level' => 'B2',
                'question' => "Would you like tea {a1} coffee?",
                'answers' => ['a1' => 'or'],
                'options' => ['or', 'and', 'nor', 'but'],
                'verb_hint' => 'choice between options',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Альтернативні питання з would like використовують or для вибору."],
                'explanations' => [
                    'or' => "✅ Правильно! Альтернативне питання з or. Приклад: Would you like tea or coffee?",
                    'and' => "❌ Неправильно. And пропонує обидва варіанти. Правильна відповідь: Would you like tea or coffee?",
                    'nor' => "❌ Неправильно. Nor — для заперечних речень. Правильна відповідь: Would you like tea or coffee?",
                    'but' => "❌ Неправильно. But — протиставлення. Правильна відповідь: Would you like tea or coffee?",
                ],
            ],
            
            // 9. Negative Question - Modal
            [
                'level' => 'B2',
                'question' => "{a1} it be better to postpone?",
                'answers' => ['a1' => "Wouldn't"],
                'options' => ["Wouldn't", "Won't", "Isn't", "Doesn't"],
                'verb_hint' => 'negative modal suggestion',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Заперечне питання з would для ввічливої пропозиції використовує Wouldn't."],
                'explanations' => [
                    "Wouldn't" => "✅ Правильно! Wouldn't для ввічливої пропозиції. Приклад: Wouldn't it be better to postpone?",
                    "Won't" => "❌ Неправильно. Won't менш ввічливий. Правильна відповідь: Wouldn't it be better to postpone?",
                    "Isn't" => "❌ Неправильно. Isn't — це to be. Правильна відповідь: Wouldn't it be better to postpone?",
                    "Doesn't" => "❌ Неправильно. Doesn't не підходить до конструкції. Правильна відповідь: Wouldn't it be better to postpone?",
                ],
            ],
            
            // 10. Past Perfect Continuous
            [
                'level' => 'B2',
                'question' => "How long {a1} they been waiting before the bus arrived?",
                'answers' => ['a1' => 'had'],
                'options' => ['had', 'have', 'were', 'did'],
                'verb_hint' => 'past perfect continuous',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "Past Perfect Continuous для тривалості до моменту у минулому використовує had been + V-ing."],
                'explanations' => [
                    'had' => "✅ Правильно! Past Perfect Continuous з had been. Приклад: How long had they been waiting before the bus arrived?",
                    'have' => "❌ Неправильно. Have — це Present Perfect. Правильна відповідь: How long had they been waiting before the bus arrived?",
                    'were' => "❌ Неправильно. Were — це Past Continuous. Правильна відповідь: How long had they been waiting before the bus arrived?",
                    'did' => "❌ Неправильно. Did — це Past Simple. Правильна відповідь: How long had they been waiting before the bus arrived?",
                ],
            ],
            
            // 11. Indirect Question - Whether
            [
                'level' => 'B2',
                'question' => "I'm not sure whether {a1} there or not.",
                'answers' => ['a1' => 'we should go'],
                'options' => ['we should go', 'should we go', 'we go should', 'go we should'],
                'verb_hint' => 'statement order with whether',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "У непрямих питаннях з whether використовується прямий порядок слів."],
                'explanations' => [
                    'we should go' => "✅ Правильно! З whether прямий порядок слів. Приклад: I'm not sure whether we should go there or not.",
                    'should we go' => "❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: I'm not sure whether we should go there or not.",
                    'we go should' => "❌ Неправильно. Неправильний порядок слів. Правильна відповідь: I'm not sure whether we should go there or not.",
                    'go we should' => "❌ Неправильно. Повністю неправильний порядок. Правильна відповідь: I'm not sure whether we should go there or not.",
                ],
            ],
            
            // 12. Tag Question - Imperative
            [
                'level' => 'B2',
                'question' => "Let's start the meeting, {a1}?",
                'answers' => ['a1' => 'shall we'],
                'options' => ['shall we', "let's we", "don't we", 'will we'],
                'verb_hint' => 'suggestion tag',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Після Let's (пропозиція) використовується tag shall we."],
                'explanations' => [
                    'shall we' => "✅ Правильно! Після Let's використовується shall we. Приклад: Let's start the meeting, shall we?",
                    "let's we" => "❌ Неправильно. Неправильна конструкція tag. Правильна відповідь: Let's start the meeting, shall we?",
                    "don't we" => "❌ Неправильно. Don't we не використовується після Let's. Правильна відповідь: Let's start the meeting, shall we?",
                    'will we' => "❌ Неправильно. Will we можливий, але shall we більш поширений. Правильна відповідь: Let's start the meeting, shall we?",
                ],
            ],
        ];
    }
    private function getC1Questions(): array
    {
        return [
            // 1. Negative Inversion - Rarely
            [
                'level' => 'C1',
                'question' => "Rarely {a1} such dedication to a project.",
                'answers' => ['a1' => 'have I seen'],
                'options' => ['have I seen', 'I have seen', 'did I see', 'I saw'],
                'verb_hint' => 'inversion after negative adverb',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Після rarely на початку речення використовується інверсія з Present Perfect: have + підмет + V3."],
                'explanations' => [
                    'have I seen' => "✅ Правильно! Після rarely потрібна інверсія. Приклад: Rarely have I seen such dedication to a project.",
                    'I have seen' => "❌ Неправильно. Після rarely потрібна інверсія. Правильна відповідь: Rarely have I seen such dedication to a project.",
                    'did I see' => "❌ Неправильно. Тут потрібен Present Perfect, не Past Simple. Правильна відповідь: Rarely have I seen such dedication to a project.",
                    'I saw' => "❌ Неправильно. Потрібна інверсія та Present Perfect. Правильна відповідь: Rarely have I seen such dedication to a project.",
                ],
            ],
            
            // 2. Tag Question - Modal Perfect (ought to)
            [
                'level' => 'C1',
                'question' => "She ought to have been more careful, {a1}?",
                'answers' => ['a1' => "oughtn't she"],
                'options' => ["oughtn't she", 'ought she', "shouldn't she", "didn't she"],
                'verb_hint' => 'tag from ought to',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Після ought to have додаємо заперечний tag oughtn't (формальна форма)."],
                'explanations' => [
                    "oughtn't she" => "✅ Правильно! Tag від ought to — oughtn't. Приклад: She ought to have been more careful, oughtn't she?",
                    'ought she' => "❌ Неправильно. Після ствердження потрібен заперечний tag. Правильна відповідь: She ought to have been more careful, oughtn't she?",
                    "shouldn't she" => "❌ Неправильно. Shouldn't можливий у розмовній мові, але формально правильний oughtn't. Правильна відповідь: She ought to have been more careful, oughtn't she?",
                    "didn't she" => "❌ Неправильно. Didn't — це Past Simple. Правильна відповідь: She ought to have been more careful, oughtn't she?",
                ],
            ],
            
            // 3. Indirect Question - Modal Perfect
            [
                'level' => 'C1',
                'question' => "Do you have any idea where {a1}?",
                'answers' => ['a1' => 'he might have gone'],
                'options' => ['he might have gone', 'might he have gone', 'he have might gone', 'might have he gone'],
                'verb_hint' => 'statement order with might have',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "У непрямих питаннях з модальними перфектами прямий порядок слів: підмет + might have + V3."],
                'explanations' => [
                    'he might have gone' => "✅ Правильно! У непрямих питаннях прямий порядок. Приклад: Do you have any idea where he might have gone?",
                    'might he have gone' => "❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: Do you have any idea where he might have gone?",
                    'he have might gone' => "❌ Неправильно. Неправильний порядок слів. Правильна відповідь: Do you have any idea where he might have gone?",
                    'might have he gone' => "❌ Неправильно. Підмет має стояти перед might have. Правильна відповідь: Do you have any idea where he might have gone?",
                ],
            ],
            
            // 4. Negative Question - Perfect Continuous
            [
                'level' => 'C1',
                'question' => "{a1} we been pursuing the wrong strategy?",
                'answers' => ['a1' => "Haven't"],
                'options' => ["Haven't", "Hadn't", "Aren't", "Weren't"],
                'verb_hint' => 'negative perfect continuous',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Заперечне питання у Present Perfect Continuous використовує Haven't been + V-ing."],
                'explanations' => [
                    "Haven't" => "✅ Правильно! Заперечне питання у Present Perfect Continuous. Приклад: Haven't we been pursuing the wrong strategy?",
                    "Hadn't" => "❌ Неправильно. Hadn't — це Past Perfect. Правильна відповідь: Haven't we been pursuing the wrong strategy?",
                    "Aren't" => "❌ Неправильно. Aren't — це Present Continuous. Правильна відповідь: Haven't we been pursuing the wrong strategy?",
                    "Weren't" => "❌ Неправильно. Weren't — це Past Continuous. Правильна відповідь: Haven't we been pursuing the wrong strategy?",
                ],
            ],
            
            // 5. Subject Question - Modal Perfect
            [
                'level' => 'C1',
                'question' => "Who {a1} sent this message?",
                'answers' => ['a1' => 'could have'],
                'options' => ['could have', 'could', 'did', 'has'],
                'verb_hint' => 'modal speculation about past',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "Для здогадки про минуле у питаннях про підмет використовуємо could have + V3."],
                'explanations' => [
                    'could have' => "✅ Правильно! Could have для здогадки про минуле. Приклад: Who could have sent this message?",
                    'could' => "❌ Неправильно. Could без have — про теперішнє/майбутнє. Правильна відповідь: Who could have sent this message?",
                    'did' => "❌ Неправильно. Did — факт, але тут здогадка. Правильна відповідь: Who could have sent this message?",
                    'has' => "❌ Неправильно. Has — Present Perfect, але потрібен модальний перфект. Правильна відповідь: Who could have sent this message?",
                ],
            ],
            
            // 6. Inversion - Not only
            [
                'level' => 'C1',
                'question' => "Not only {a1} the deadline, but they also exceeded expectations.",
                'answers' => ['a1' => 'did they meet'],
                'options' => ['did they meet', 'they met', 'they did meet', 'met they'],
                'verb_hint' => 'inversion after not only',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Після Not only на початку речення використовується інверсія з did."],
                'explanations' => [
                    'did they meet' => "✅ Правильно! Після Not only потрібна інверсія. Приклад: Not only did they meet the deadline, but they also exceeded expectations.",
                    'they met' => "❌ Неправильно. Після Not only потрібна інверсія. Правильна відповідь: Not only did they meet the deadline, but they also exceeded expectations.",
                    'they did meet' => "❌ Неправильно. Це емфатична форма, але після Not only потрібна інверсія. Правильна відповідь: Not only did they meet the deadline, but they also exceeded expectations.",
                    'met they' => "❌ Неправильно. Неправильна інверсія, потрібен did. Правильна відповідь: Not only did they meet the deadline, but they also exceeded expectations.",
                ],
            ],
            
            // 7. Complex Indirect Question - Conditional
            [
                'level' => 'C1',
                'question' => "I wonder whether {a1} if they had known the risks.",
                'answers' => ['a1' => 'they would have agreed'],
                'options' => ['they would have agreed', 'would they have agreed', 'they had agreed would', 'would have they agreed'],
                'verb_hint' => 'statement order in conditional',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "У непрямих питаннях навіть з умовними конструкціями прямий порядок слів."],
                'explanations' => [
                    'they would have agreed' => "✅ Правильно! У непрямих питаннях прямий порядок слів. Приклад: I wonder whether they would have agreed if they had known the risks.",
                    'would they have agreed' => "❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: I wonder whether they would have agreed if they had known the risks.",
                    'they had agreed would' => "❌ Неправильно. Неправильний порядок слів. Правильна відповідь: I wonder whether they would have agreed if they had known the risks.",
                    'would have they agreed' => "❌ Неправильно. Підмет має стояти перед would have. Правильна відповідь: I wonder whether they would have agreed if they had known the risks.",
                ],
            ],
            
            // 8. Negative Question - Should have
            [
                'level' => 'C1',
                'question' => "{a1} you have informed me earlier?",
                'answers' => ['a1' => "Shouldn't"],
                'options' => ["Shouldn't", "Hadn't", "Wouldn't", "Didn't"],
                'verb_hint' => 'negative modal criticism',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Заперечне питання з should have виражає критику або докір: Shouldn't + підмет + have + V3."],
                'explanations' => [
                    "Shouldn't" => "✅ Правильно! Shouldn't have для критики за минуле. Приклад: Shouldn't you have informed me earlier?",
                    "Hadn't" => "❌ Неправильно. Hadn't — Past Perfect, але для критики потрібен модальний перфект. Правильна відповідь: Shouldn't you have informed me earlier?",
                    "Wouldn't" => "❌ Неправильно. Wouldn't виражає небажання, не критику. Правильна відповідь: Shouldn't you have informed me earlier?",
                    "Didn't" => "❌ Неправильно. Didn't — Past Simple, але для критики потрібен shouldn't have. Правильна відповідь: Shouldn't you have informed me earlier?",
                ],
            ],
            
            // 9. Future Perfect Question
            [
                'level' => 'C1',
                'question' => "Which proposals {a1} implemented by the end of the quarter?",
                'answers' => ['a1' => 'will have been'],
                'options' => ['will have been', 'will be', 'have been', 'will been'],
                'verb_hint' => 'future perfect passive',
                'detail' => 'subject_questions',
                'hints' => ['a1' => "Future Perfect Passive для дії, що буде завершена до певного моменту: will have been + V3."],
                'explanations' => [
                    'will have been' => "✅ Правильно! Future Perfect Passive з by the end. Приклад: Which proposals will have been implemented by the end of the quarter?",
                    'will be' => "❌ Неправильно. Will be — Future Simple, але by the end вимагає Perfect. Правильна відповідь: Which proposals will have been implemented by the end of the quarter?",
                    'have been' => "❌ Неправильно. Have been — Present Perfect, але контекст майбутній. Правильна відповідь: Which proposals will have been implemented by the end of the quarter?",
                    'will been' => "❌ Неправильно. Неправильна форма, потрібно will have been. Правильна відповідь: Which proposals will have been implemented by the end of the quarter?",
                ],
            ],
            
            // 10. Alternative Question - Complex
            [
                'level' => 'C1',
                'question' => "Would you rather work independently {a1} collaborate with a team?",
                'answers' => ['a1' => 'or'],
                'options' => ['or', 'and', 'than', 'but'],
                'verb_hint' => 'preference between options',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Would rather... or для вибору між варіантами переваги."],
                'explanations' => [
                    'or' => "✅ Правильно! Would rather... or для альтернативного питання. Приклад: Would you rather work independently or collaborate with a team?",
                    'and' => "❌ Неправильно. And пропонує обидва варіанти. Правильна відповідь: Would you rather work independently or collaborate with a team?",
                    'than' => "❌ Неправильно. Than використовується після prefer, але не після would rather. Правильна відповідь: Would you rather work independently or collaborate with a team?",
                    'but' => "❌ Неправильно. But — протиставлення. Правильна відповідь: Would you rather work independently or collaborate with a team?",
                ],
            ],
            
            // 11. Rhetorical Negative Question
            [
                'level' => 'C1',
                'question' => "{a1} we all benefit from better communication?",
                'answers' => ['a1' => "Wouldn't"],
                'options' => ["Wouldn't", "Won't", "Don't", "Aren't"],
                'verb_hint' => 'rhetorical conditional',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Wouldn't у риторичному питанні м\'якше та ввічливіше, припускає згоду."],
                'explanations' => [
                    "Wouldn't" => "✅ Правильно! Wouldn't для риторичного питання. Приклад: Wouldn't we all benefit from better communication?",
                    "Won't" => "❌ Неправильно. Won't можливий, але wouldn't більш ввічливий. Правильна відповідь: Wouldn't we all benefit from better communication?",
                    "Don't" => "❌ Неправильно. Don't занадто прямолінійний для риторичного питання. Правильна відповідь: Wouldn't we all benefit from better communication?",
                    "Aren't" => "❌ Неправильно. Aren't з to be, але тут дієслово benefit. Правильна відповідь: Wouldn't we all benefit from better communication?",
                ],
            ],
            
            // 12. Indirect Question - Continuous Perfect
            [
                'level' => 'C1',
                'question' => "I'd like to understand what {a1} all this time.",
                'answers' => ['a1' => 'you have been working on'],
                'options' => ['you have been working on', 'have you been working on', 'you been have working on', 'on you have been working'],
                'verb_hint' => 'statement order with phrasal verb',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "У непрямих питаннях прийменник фразового дієслова залишається в кінці, прямий порядок слів."],
                'explanations' => [
                    'you have been working on' => "✅ Правильно! Прямий порядок слів, прийменник в кінці. Приклад: I'd like to understand what you have been working on all this time.",
                    'have you been working on' => "❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: I'd like to understand what you have been working on all this time.",
                    'you been have working on' => "❌ Неправильно. Неправильний порядок слів. Правильна відповідь: I'd like to understand what you have been working on all this time.",
                    'on you have been working' => "❌ Неправильно. Неприродний порядок, прийменник має бути в кінці. Правильна відповідь: I'd like to understand what you have been working on all this time.",
                ],
            ],
        ];
    }
    private function getC2Questions(): array
    {
        return [
            // 1. Cleft Sentence
            [
                'level' => 'C2',
                'question' => "Was it the CEO {a1} made the decision?",
                'answers' => ['a1' => 'who'],
                'options' => ['who', 'that', 'which', 'whom'],
                'verb_hint' => 'cleft with person',
                'detail' => 'yes_no',
                'hints' => ['a1' => "У cleft sentences для людей використовується who (хоча that теж можливий)."],
                'explanations' => [
                    'who' => "✅ Правильно! У cleft sentences для людей краще використовувати who. Приклад: Was it the CEO who made the decision?",
                    'that' => "❌ Неправильно. That можливий, але для людей краще who. Правильна відповідь: Was it the CEO who made the decision?",
                    'which' => "❌ Неправильно. Which для речей, не для людей. Правильна відповідь: Was it the CEO who made the decision?",
                    'whom' => "❌ Неправильно. Whom для об\'єкта, але тут підмет. Правильна відповідь: Was it the CEO who made the decision?",
                ],
            ],
            
            // 2. Inverted Conditional (Had)
            [
                'level' => 'C2',
                'question' => "{a1} I known about the difficulties, I would have helped.",
                'answers' => ['a1' => 'Had'],
                'options' => ['Had', 'If had', 'Would', 'If I had'],
                'verb_hint' => 'inverted conditional without if',
                'detail' => 'yes_no',
                'hints' => ['a1' => "В умовних реченнях третього типу можна використовувати інверсію без if: Had + підмет + V3."],
                'explanations' => [
                    'Had' => "✅ Правильно! Інверсія в умовному реченні без if. Приклад: Had I known about the difficulties, I would have helped.",
                    'If had' => "❌ Неправильно. При інверсії if не використовується. Правильна відповідь: Had I known about the difficulties, I would have helped.",
                    'Would' => "❌ Неправильно. Would у головній частині, не в умовній. Правильна відповідь: Had I known about the difficulties, I would have helped.",
                    'If I had' => "❌ Неправильно. Це стандартна форма, але потрібна інверсія без if. Правильна відповідь: Had I known about the difficulties, I would have helped.",
                ],
            ],
            
            // 3. Subjunctive in Indirect Question
            [
                'level' => 'C2',
                'question' => "The board insists that we clarify whether {a1} present.",
                'answers' => ['a1' => 'he be'],
                'options' => ['he be', 'he is', 'is he', 'he will be'],
                'verb_hint' => 'subjunctive after insist',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Після insists that у формальній англійській використовується subjunctive: підмет + be (базова форма)."],
                'explanations' => [
                    'he be' => "✅ Правильно! Subjunctive mood після insists that. Приклад: The board insists that we clarify whether he be present.",
                    'he is' => "❌ Неправильно. У розмовній мові можливо, але формально потрібен subjunctive. Правильна відповідь: The board insists that we clarify whether he be present.",
                    'is he' => "❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: The board insists that we clarify whether he be present.",
                    'he will be' => "❌ Неправильно. Will be не використовується після insists. Правильна відповідь: The board insists that we clarify whether he be present.",
                ],
            ],
            
            // 4. Inversion - Scarcely
            [
                'level' => 'C2',
                'question' => "Scarcely {a1} the presentation when questions started.",
                'answers' => ['a1' => 'had she finished'],
                'options' => ['had she finished', 'she had finished', 'she finished', 'did she finish'],
                'verb_hint' => 'inversion after scarcely',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Після scarcely/hardly на початку речення інверсія з Past Perfect: had + підмет + V3."],
                'explanations' => [
                    'had she finished' => "✅ Правильно! Після scarcely потрібна інверсія з Past Perfect. Приклад: Scarcely had she finished the presentation when questions started.",
                    'she had finished' => "❌ Неправильно. Після scarcely потрібна інверсія. Правильна відповідь: Scarcely had she finished the presentation when questions started.",
                    'she finished' => "❌ Неправильно. Потрібна інверсія з Past Perfect. Правильна відповідь: Scarcely had she finished the presentation when questions started.",
                    'did she finish' => "❌ Неправильно. Після scarcely потрібен Past Perfect, не Past Simple. Правильна відповідь: Scarcely had she finished the presentation when questions started.",
                ],
            ],
            
            // 5. Tag Question - Must have (deduction)
            [
                'level' => 'C2',
                'question' => "He must have been lying, {a1}?",
                'answers' => ['a1' => "mustn't he"],
                'options' => ["mustn't he", 'must he', "hasn't he", "wasn't he"],
                'verb_hint' => 'tag from must have',
                'detail' => 'tag_questions',
                'hints' => ['a1' => "Tag від must have (дедукція про минуле) формується як mustn't."],
                'explanations' => [
                    "mustn't he" => "✅ Правильно! Tag від must have — mustn't. Приклад: He must have been lying, mustn't he?",
                    'must he' => "❌ Неправильно. Після ствердження потрібен заперечний tag. Правильна відповідь: He must have been lying, mustn't he?",
                    "hasn't he" => "❌ Неправильно. Hasn't — від Present Perfect, але це модальна дедукція. Правильна відповідь: He must have been lying, mustn't he?",
                    "wasn't he" => "❌ Неправильно. Wasn't — від Past Continuous, але це модальний перфект. Правильна відповідь: He must have been lying, mustn't he?",
                ],
            ],
            
            // 6. Inversion - Little
            [
                'level' => 'C2',
                'question' => "Little {a1} that this would change everything.",
                'answers' => ['a1' => 'did we know'],
                'options' => ['did we know', 'we knew', 'we did know', 'knew we'],
                'verb_hint' => 'inversion after little',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Після little на початку речення використовується інверсія з did: did + підмет + базове дієслово."],
                'explanations' => [
                    'did we know' => "✅ Правильно! Після little потрібна інверсія з did. Приклад: Little did we know that this would change everything.",
                    'we knew' => "❌ Неправильно. Після little потрібна інверсія. Правильна відповідь: Little did we know that this would change everything.",
                    'we did know' => "❌ Неправильно. Це емфатична форма, але після little потрібна інверсія. Правильна відповідь: Little did we know that this would change everything.",
                    'knew we' => "❌ Неправильно. Неправильна інверсія, потрібен did. Правильна відповідь: Little did we know that this would change everything.",
                ],
            ],
            
            // 7. Future Perfect Continuous - Indirect
            [
                'level' => 'C2',
                'question' => "By next month, can you estimate how long {a1} on this?",
                'answers' => ['a1' => 'we will have been working'],
                'options' => ['we will have been working', 'will we have been working', 'we have been working will', 'will have we been working'],
                'verb_hint' => 'statement order with future perfect continuous',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "У непрямих питаннях прямий порядок слів навіть із складними часовими формами."],
                'explanations' => [
                    'we will have been working' => "✅ Правильно! У непрямих питаннях прямий порядок слів. Приклад: By next month, can you estimate how long we will have been working on this?",
                    'will we have been working' => "❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: By next month, can you estimate how long we will have been working on this?",
                    'we have been working will' => "❌ Неправильно. Неправильний порядок слів. Правильна відповідь: By next month, can you estimate how long we will have been working on this?",
                    'will have we been working' => "❌ Неправильно. Підмет має стояти перед will have been. Правильна відповідь: By next month, can you estimate how long we will have been working on this?",
                ],
            ],
            
            // 8. Emphatic Cleft with Wh-word
            [
                'level' => 'C2',
                'question' => "What it was {a1} impressed me most was their dedication.",
                'answers' => ['a1' => 'that'],
                'options' => ['that', 'which', 'what', 'who'],
                'verb_hint' => 'cleft connector',
                'detail' => 'wh_questions',
                'hints' => ['a1' => "У складних cleft sentences використовується that як сполучний елемент."],
                'explanations' => [
                    'that' => "✅ Правильно! У емфатичних cleft використовується that. Приклад: What it was that impressed me most was their dedication.",
                    'which' => "❌ Неправильно. Which не використовується у цій конструкції. Правильна відповідь: What it was that impressed me most was their dedication.",
                    'what' => "❌ Неправильно. What вже використано на початку. Правильна відповідь: What it was that impressed me most was their dedication.",
                    'who' => "❌ Неправильно. Who для людей, але тут абстрактне поняття. Правильна відповідь: What it was that impressed me most was their dedication.",
                ],
            ],
            
            // 9. Inverted Conditional - Should
            [
                'level' => 'C2',
                'question' => "{a1} any issues arise, please contact me.",
                'answers' => ['a1' => 'Should'],
                'options' => ['Should', 'If should', 'Would', 'If'],
                'verb_hint' => 'inverted conditional first type',
                'detail' => 'yes_no',
                'hints' => ['a1' => "В умовних реченнях першого типу можна використовувати інверсію з should без if (формально)."],
                'explanations' => [
                    'Should' => "✅ Правильно! Інверсія з should без if (формальна форма). Приклад: Should any issues arise, please contact me.",
                    'If should' => "❌ Неправильно. При інверсії if не використовується. Правильна відповідь: Should any issues arise, please contact me.",
                    'Would' => "❌ Неправильно. Would для другого типу умовних. Правильна відповідь: Should any issues arise, please contact me.",
                    'If' => "❌ Неправильно. If можливий, але потрібна інверсія без if. Правильна відповідь: Should any issues arise, please contact me.",
                ],
            ],
            
            // 10. Past Continuous Passive - Indirect
            [
                'level' => 'C2',
                'question' => "The investigators need to determine what {a1} at the time.",
                'answers' => ['a1' => 'was being done'],
                'options' => ['was being done', 'was done', 'has been done', 'had been done'],
                'verb_hint' => 'past continuous passive',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "Past Continuous Passive для дії, що тривала у певний момент минулого: was being + V3."],
                'explanations' => [
                    'was being done' => "✅ Правильно! Past Continuous Passive з at the time. Приклад: The investigators need to determine what was being done at the time.",
                    'was done' => "❌ Неправильно. Was done — Past Simple Passive, але at the time вимагає continuous. Правильна відповідь: The investigators need to determine what was being done at the time.",
                    'has been done' => "❌ Неправильно. Has been done — Present Perfect Passive, але контекст минулий. Правильна відповідь: The investigators need to determine what was being done at the time.",
                    'had been done' => "❌ Неправильно. Had been done — Past Perfect Passive, але потрібен Past Continuous Passive. Правильна відповідь: The investigators need to determine what was being done at the time.",
                ],
            ],
            
            // 11. Complex Double Modal - Indirect
            [
                'level' => 'C2',
                'question' => "One might wonder whether {a1} sooner had we known.",
                'answers' => ['a1' => 'we might not have acted'],
                'options' => ['we might not have acted', 'might we not have acted', 'we acted might not have', 'not have acted we might'],
                'verb_hint' => 'statement order with complex modal',
                'detail' => 'indirect_questions',
                'hints' => ['a1' => "У непрямих питаннях прямий порядок слів навіть зі складними заперечними модальними перфектами."],
                'explanations' => [
                    'we might not have acted' => "✅ Правильно! Прямий порядок слів зі складними модальними. Приклад: One might wonder whether we might not have acted sooner had we known.",
                    'might we not have acted' => "❌ Неправильно. У непрямих питаннях не використовується інверсія. Правильна відповідь: One might wonder whether we might not have acted sooner had we known.",
                    'we acted might not have' => "❌ Неправильно. Повністю неправильний порядок слів. Правильна відповідь: One might wonder whether we might not have acted sooner had we known.",
                    'not have acted we might' => "❌ Неправильно. Повністю неправильний порядок. Правильна відповідь: One might wonder whether we might not have acted sooner had we known.",
                ],
            ],
            
            // 12. Alternative Question - Sophisticated
            [
                'level' => 'C2',
                'question' => "Should we proceed with the original plan {a1} reconsider our approach?",
                'answers' => ['a1' => 'or'],
                'options' => ['or', 'and', 'nor', 'but'],
                'verb_hint' => 'choice in formal context',
                'detail' => 'yes_no',
                'hints' => ['a1' => "Альтернативні питання навіть у формальних контекстах використовують or для вибору між варіантами."],
                'explanations' => [
                    'or' => "✅ Правильно! Альтернативне питання з or. Приклад: Should we proceed with the original plan or reconsider our approach?",
                    'and' => "❌ Неправильно. And пропонує обидва варіанти, не вибір. Правильна відповідь: Should we proceed with the original plan or reconsider our approach?",
                    'nor' => "❌ Неправильно. Nor для заперечних речень. Правильна відповідь: Should we proceed with the original plan or reconsider our approach?",
                    'but' => "❌ Неправильно. But — протиставлення, не вибір. Правильна відповідь: Should we proceed with the original plan or reconsider our approach?",
                ],
            ],
        ];
    }
}
