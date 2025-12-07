<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class QuestionsDifferentTypesV2Seeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Questions - Different Types'])->id;

        $sourceId = Source::firstOrCreate([
            'name' => 'https://test-english.com/grammar-points/b1-b2/questions-different-types/'
        ])->id;

        $themeTag = Tag::firstOrCreate(
            ['name' => 'Question Formation Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTags = [
            'indirect_questions' => Tag::firstOrCreate(['name' => 'Indirect Questions'], ['category' => 'English Grammar Detail'])->id,
            'embedded_questions' => Tag::firstOrCreate(['name' => 'Embedded Questions'], ['category' => 'English Grammar Detail'])->id,
            'wh_questions' => Tag::firstOrCreate(['name' => 'Wh-Questions Formation'], ['category' => 'English Grammar Detail'])->id,
            'question_word_order' => Tag::firstOrCreate(['name' => 'Question Word Order'], ['category' => 'English Grammar Detail'])->id,
            'subject_questions' => Tag::firstOrCreate(['name' => 'Subject Questions'], ['category' => 'English Grammar Detail'])->id,
        ];

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        // Exercise 1: Choose the correct forms to complete the questions.
        $exercise1 = [
            [
                'level' => 'B1',
                'question' => "Tom has gone out, but I don't know {a1}.",
                'correct' => 'where he has gone',
                'options' => [
                    'where he has gone',
                    'where has he gone',
                    'where gone he has',
                ],
                'verb_hint' => 'embedded question word order',
                'detail' => 'embedded_questions',
            ],
            [
                'level' => 'B1',
                'question' => "This book belongs to someone. {a1}",
                'correct' => 'Who does it belong to?',
                'options' => [
                    'Who does it belong to?',
                    'Who does it belong?',
                    'Who belongs it to?',
                ],
                'verb_hint' => 'preposition at end',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'question' => "Somebody lives in that house. {a1}",
                'correct' => 'Who lives in that house?',
                'options' => [
                    'Who lives in that house?',
                    'Who live in that house?',
                    'Who does lives in that house?',
                ],
                'verb_hint' => 'subject question',
                'detail' => 'subject_questions',
            ],
            [
                'level' => 'B1',
                'question' => "This word means something. {a1}",
                'correct' => 'What does this word mean?',
                'options' => [
                    'What does this word mean?',
                    'What means this word?',
                    'What does mean this word?',
                ],
                'verb_hint' => 'auxiliary + subject + verb',
                'detail' => 'question_word_order',
            ],
            [
                'level' => 'B1',
                'question' => "They were talking about something. {a1}",
                'correct' => 'What were they talking about?',
                'options' => [
                    'What were they talking about?',
                    'About what they were talking?',
                    'What they were talking about?',
                ],
                'verb_hint' => 'preposition at end',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'question' => "Something fell on the floor. {a1}",
                'correct' => 'What fell on the floor?',
                'options' => [
                    'What fell on the floor?',
                    'What did fall on the floor?',
                    'What did fell on the floor?',
                ],
                'verb_hint' => 'subject question',
                'detail' => 'subject_questions',
            ],
            [
                'level' => 'B1',
                'question' => "The library is near here. Do you know {a1}",
                'correct' => 'where the library is?',
                'options' => [
                    'where the library is?',
                    'where is the library?',
                    'where is near the library?',
                ],
                'verb_hint' => 'embedded question',
                'detail' => 'embedded_questions',
            ],
            [
                'level' => 'B2',
                'question' => "She borrowed the money from somebody. I wonder {a1}.",
                'correct' => 'who she borrowed the money from',
                'options' => [
                    'who she borrowed the money from',
                    'who did she borrow the money from',
                    'from who did she borrow the money',
                ],
                'verb_hint' => 'embedded question',
                'detail' => 'embedded_questions',
            ],
            [
                'level' => 'B1',
                'question' => "She can't understand something. {a1}",
                'correct' => 'What can she not understand?',
                'options' => [
                    'What can she not understand?',
                    'What she can\'t understand?',
                    'What does she understand?',
                ],
                'verb_hint' => 'modal question',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'question' => "He didn't come to the party for some reason. {a1}",
                'correct' => 'Why didn\'t he come to the party?',
                'options' => [
                    'Why didn\'t he come to the party?',
                    'Why he didn\'t come to the party?',
                    'Why did not he come to the party?',
                ],
                'verb_hint' => 'negative question',
                'detail' => 'question_word_order',
            ],
        ];

        // Exercise 2: More mixed question types (some with two correct options).
        $exercise2 = [
            [
                'level' => 'B1',
                'question' => "I don't know why {a1}.",
                'correct' => 'the meeting was cancelled',
                'options' => [
                    'the meeting was cancelled',
                    'was the meeting cancelled',
                    'cancelled was the meeting',
                ],
                'verb_hint' => 'embedded question',
                'detail' => 'embedded_questions',
            ],
            [
                'level' => 'B2',
                'question' => "Who {a1}?",
                'correct' => 'has he been writing to',
                'options' => [
                    'has he been writing to',
                    'he has been writing to',
                    'has been writing to',
                ],
                'verb_hint' => 'present perfect continuous',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B2',
                'question' => "Why {a1}? (Choose TWO correct варіанти.)",
                'correct' => ['has she not replied to your email', 'hasn\'t she replied to your email'],
                'options' => [
                    'has she not replied to your email',
                    'has not she replied to your email',
                    'hasn\'t she replied to your email',
                ],
                'verb_hint' => 'negative question',
                'detail' => 'question_word_order',
                'multiple_correct' => true,
            ],
            [
                'level' => 'B1',
                'question' => "I'm wondering which countries {a1}.",
                'correct' => 'he has visited',
                'options' => [
                    'he has visited',
                    'has he visited',
                    'has he been',
                ],
                'verb_hint' => 'embedded question',
                'detail' => 'embedded_questions',
            ],
            [
                'level' => 'B1',
                'question' => "When we arrived, someone had taken the tickets. Who {a1}?",
                'correct' => 'had taken the tickets',
                'options' => [
                    'had taken the tickets',
                    'had he taken the tickets',
                    'taken the tickets',
                ],
                'verb_hint' => 'subject question',
                'detail' => 'subject_questions',
            ],
            [
                'level' => 'B1',
                'question' => "Who {a1}?",
                'correct' => 'told you that',
                'options' => [
                    'told you that',
                    'did tell you that',
                    'you told that',
                ],
                'verb_hint' => 'subject question',
                'detail' => 'subject_questions',
            ],
            [
                'level' => 'B1',
                'question' => "What {a1}?",
                'correct' => 'are you waiting for',
                'options' => [
                    'are you waiting for',
                    'are you waiting',
                    'you are waiting for',
                ],
                'verb_hint' => 'preposition at end',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'question' => "Do you know {a1}?",
                'correct' => 'whether he will come or not',
                'options' => [
                    'whether he will come or not',
                    'if will he come or not',
                    'whether will he come or not',
                ],
                'verb_hint' => 'embedded question',
                'detail' => 'embedded_questions',
            ],
            [
                'level' => 'B1',
                'question' => "You shouldn't be playing video games now. {a1}?",
                'correct' => 'Don\'t you have homework to do',
                'options' => [
                    'Don\'t you have homework to do',
                    'You don\'t have homework to do',
                    'Do you haven\'t homework to do',
                ],
                'verb_hint' => 'negative question',
                'detail' => 'question_word_order',
            ],
            [
                'level' => 'B1',
                'question' => "Who {a1} in the last election?",
                'correct' => 'did you vote for',
                'options' => [
                    'did you vote for',
                    'did you vote',
                    'voted you',
                ],
                'verb_hint' => 'preposition at end',
                'detail' => 'wh_questions',
            ],
        ];

        // Exercise 3: Ask questions about the underlined words
        $exercise3 = [
            [
                'level' => 'B1',
                'sentence' => 'He has visited fifty countries.',
                'underlined' => 'fifty countries',
                'question' => 'Ask a question about the underlined words.',
                'correct' => 'How many countries has he visited?',
                'options' => [
                    'How many countries has he visited?',
                    'How many countries he has visited?',
                    'How many has he visited countries?',
                    'How much countries has he visited?',
                ],
                'verb_hint' => 'how many + plural noun',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'sentence' => 'Two thousand people protested against the new law.',
                'underlined' => 'Two thousand people',
                'question' => 'Ask a question about the underlined words.',
                'correct' => 'How many people protested against the new law?',
                'options' => [
                    'How many people protested against the new law?',
                    'How many people did protest against the new law?',
                    'How much people protested against the new law?',
                    'Who protested against the new law?',
                ],
                'verb_hint' => 'quantity question',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'sentence' => 'They weren\'t able to finish the final report.',
                'underlined' => 'the final report',
                'question' => 'Ask a question about the underlined words.',
                'correct' => 'What couldn\'t they finish?',
                'options' => [
                    'What couldn\'t they finish?',
                    'What they couldn\'t finish?',
                    'What could they not finish?',
                    'What weren\'t they able to finish?',
                ],
                'verb_hint' => 'object question',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'sentence' => 'He has been posting on his blog every day.',
                'underlined' => 'every day',
                'question' => 'Ask a question about the underlined words.',
                'correct' => 'How often has he been posting on his blog?',
                'options' => [
                    'How often has he been posting on his blog?',
                    'How often he has been posting on his blog?',
                    'When has he been posting on his blog?',
                    'How many times has he been posting on his blog?',
                ],
                'verb_hint' => 'frequency question',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'sentence' => 'They have been listening to their teacher.',
                'underlined' => 'their teacher',
                'question' => 'Ask a question about the underlined words.',
                'correct' => 'Who have they been listening to?',
                'options' => [
                    'Who have they been listening to?',
                    'To who have they been listening?',
                    'Who they have been listening to?',
                    'Whom have they been listening?',
                ],
                'verb_hint' => 'preposition at end',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'sentence' => 'It took them two hours to get to Rome.',
                'underlined' => 'two hours',
                'question' => 'Ask a question about the underlined words.',
                'correct' => 'How long did it take them to get to Rome?',
                'options' => [
                    'How long did it take them to get to Rome?',
                    'How long it took them to get to Rome?',
                    'How much time did it take them to get to Rome?',
                    'When did it take them to get to Rome?',
                ],
                'verb_hint' => 'duration question',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'sentence' => 'I\'m looking for a new dress.',
                'underlined' => 'a new dress',
                'question' => 'Ask a question about the underlined words.',
                'correct' => 'What are you looking for?',
                'options' => [
                    'What are you looking for?',
                    'What you are looking for?',
                    'For what are you looking?',
                    'What do you looking for?',
                ],
                'verb_hint' => 'preposition at end',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'sentence' => 'He never does his homework.',
                'underlined' => 'his homework',
                'question' => 'Ask a question about the underlined words.',
                'correct' => 'What does he never do?',
                'options' => [
                    'What does he never do?',
                    'What he never does?',
                    'What doesn\'t he do?',
                    'What he does never?',
                ],
                'verb_hint' => 'object question',
                'detail' => 'wh_questions',
            ],
            [
                'level' => 'B1',
                'sentence' => 'She is thinking about the new neighbour.',
                'underlined' => 'about the new neighbour',
                'question' => 'Ask a question as an embedded question.',
                'correct' => 'I\'d like to know what she is thinking about.',
                'options' => [
                    'I\'d like to know what she is thinking about.',
                    'I\'d like to know what is she thinking about.',
                    'I\'d like to know about what she is thinking.',
                    'I\'d like to know she is thinking about what.',
                ],
                'verb_hint' => 'embedded question',
                'detail' => 'embedded_questions',
            ],
            [
                'level' => 'B1',
                'sentence' => 'That thing on the chair is my new bag.',
                'underlined' => 'That thing on the chair',
                'question' => 'Ask a question as an embedded question.',
                'correct' => 'Can you tell me what that thing is?',
                'options' => [
                    'Can you tell me what that thing is?',
                    'Can you tell me what is that thing?',
                    'Can you tell me that thing is what?',
                    'Can you tell me what that thing on the chair is?',
                ],
                'verb_hint' => 'embedded question',
                'detail' => 'embedded_questions',
            ],
        ];

        $allQuestions = [];

        // Process Exercise 1 and 2
        foreach ([$exercise1, $exercise2] as $exerciseData) {
            foreach ($exerciseData as $entry) {
                $allQuestions[] = $this->processQuestion($entry);
            }
        }

        // Process Exercise 3
        foreach ($exercise3 as $entry) {
            $allQuestions[] = $this->processExercise3Question($entry);
        }

        $items = [];
        $meta = [];

        foreach ($allQuestions as $index => $question) {
            $uuid = $this->generateQuestionUuid($question['level'], $index, $question['question']);

            $answers = [];
            $optionMarkers = [];
            
            if (isset($question['multiple_correct']) && $question['multiple_correct']) {
                foreach ($question['options'] as $option) {
                    $optionMarkers[$option] = 'a1';
                }
                $answers[] = [
                    'marker' => 'a1',
                    'answer' => $question['answers']['a1'],
                    'verb_hint' => $this->normalizeHint($question['verb_hint']['a1'] ?? null),
                ];
            } else {
                foreach ($question['options'] as $option) {
                    $optionMarkers[$option] = 'a1';
                }
                $answers[] = [
                    'marker' => 'a1',
                    'answer' => $question['answers']['a1'],
                    'verb_hint' => $this->normalizeHint($question['verb_hint']['a1'] ?? null),
                ];
            }

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
                'flag' => 0,
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

    private function processQuestion(array $entry): array
    {
        $isMultipleCorrect = isset($entry['multiple_correct']) && $entry['multiple_correct'];
        
        if ($isMultipleCorrect) {
            $correct = $entry['correct'];
            $answer = implode(' / ', $correct);
        } else {
            $correct = $entry['correct'];
            $answer = $correct;
        }

        $options = $entry['options'];

        $example = $this->formatExample($entry['question'], ['a1' => $answer]);

        $explanations = [];
        foreach ($options as $option) {
            if ($isMultipleCorrect) {
                $isCorrect = in_array($option, $correct, true);
            } else {
                $isCorrect = $option === $correct;
            }

            if ($isCorrect) {
                $explanations[$option] = $this->buildCorrectExplanation($option, $example);
            } else {
                $explanations[$option] = $this->buildWrongExplanation($option, $correct, $entry['detail']);
            }
        }

        $hint = $this->buildHint($entry['verb_hint'], $example);

        return [
            'level' => $entry['level'],
            'question' => $entry['question'],
            'verb_hint' => ['a1' => '(' . $entry['verb_hint'] . ')'],
            'options' => $options,
            'answers' => ['a1' => $answer],
            'explanations' => $explanations,
            'hints' => ['a1' => $hint],
            'detail' => $entry['detail'],
            'multiple_correct' => $isMultipleCorrect,
        ];
    }

    private function processExercise3Question(array $entry): array
    {
        $correct = $entry['correct'];
        $options = $entry['options'];

        $questionText = "Sentence: \"{$entry['sentence']}\" (underlined: {$entry['underlined']})  \n{$entry['question']}: {a1}";
        $example = $this->formatExample($questionText, ['a1' => $correct]);

        $explanations = [];
        foreach ($options as $option) {
            if ($option === $correct) {
                $explanations[$option] = $this->buildCorrectExplanation($option, $example);
            } else {
                $explanations[$option] = $this->buildWrongExplanation($option, $correct, $entry['detail']);
            }
        }

        $hint = $this->buildHint($entry['verb_hint'], $example);

        return [
            'level' => $entry['level'],
            'question' => $questionText,
            'verb_hint' => ['a1' => '(' . $entry['verb_hint'] . ')'],
            'options' => $options,
            'answers' => ['a1' => $correct],
            'explanations' => $explanations,
            'hints' => ['a1' => $hint],
            'detail' => $entry['detail'],
            'multiple_correct' => false,
        ];
    }

    private function buildHint(string $verbHint, string $example): string
    {
        return "Підказка: {$verbHint}.  \nПриклад правильної відповіді: *{$example}*";
    }

    private function buildCorrectExplanation(string $option, string $example): string
    {
        return "✅ Правильно! «{$option}» — це коректна форма питання.  \nПриклад: *{$example}*";
    }

    private function buildWrongExplanation(string $option, $correct, string $detail): string
    {
        $correctText = is_array($correct) ? implode('» або «', $correct) : $correct;

        $explanationMap = [
            'embedded_questions' => "❌ У непрямих/вбудованих питаннях використовується прямий порядок слів (підмет + дієслово), а не інвертований.",
            'wh_questions' => "❌ У прямих Wh-питаннях потрібен правильний порядок слів: питальне слово + допоміжне дієслово + підмет + основне дієслово.",
            'subject_questions' => "❌ Коли питаємо про підмет, не використовуємо допоміжне дієслово do/does/did.",
            'question_word_order' => "❌ Неправильний порядок слів у питанні. Перевірте структуру питального речення.",
        ];

        $baseExplanation = $explanationMap[$detail] ?? "❌ Ця форма не є правильною для даного типу питання.";

        return "{$baseExplanation}  \nПравильна відповідь: «{$correctText}»";
    }
}
