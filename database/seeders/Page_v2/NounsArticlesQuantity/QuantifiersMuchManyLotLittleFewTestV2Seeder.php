<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use App\Models\Category;
use App\Models\Source;
use Database\Seeders\QuestionSeeder;

class QuantifiersMuchManyLotLittleFewTestV2Seeder extends QuestionSeeder
{
    public function run(): void
    {
        // Джерело: https://test-english.com/grammar-points/a1/much-many-lot-little-few/
        // (вправа 1-3)

        $categoryId = Category::firstOrCreate(['name' => 'Nouns, Articles & Quantity'])->id;

        $sourceId = Source::firstOrCreate([
            'name' => 'https://test-english.com/grammar-points/a1/much-many-lot-little-few/'
        ])->id;

        $items = [];
        $meta = [];

        /**
         * =========================
         * Exercise 1 (10)
         * =========================
         */
        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex1', 1);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'If I want to pass the exam, I need to study {a1}.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'a lot'],
            ],
            'options' => ['a lot', 'a lot of', 'many'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a lot'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex1', 2);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'There aren\'t {a1} things to do in this village.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'many'],
            ],
            'options' => ['many', 'a lot', 'much'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'many'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex1', 3);
        $items[] = [
            'uuid' => $uuid,
            'question' => '{a1} sugar do you take in your tea?',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'How much'],
            ],
            'options' => ['How many', 'How little', 'How much'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'How much'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex1', 4);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'There was {a1} tension at the meeting.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'a lot of'],
            ],
            'options' => ['much', 'a lot of', 'many'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a lot of'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex1', 5);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'Dad, I need {a1} money for school.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'a little'],
            ],
            'options' => ['a little', 'many', 'a few'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a little'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex1', 6);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'There are {a1} things that you can do to improve your writing.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'a few'],
            ],
            'options' => ['any', 'a little', 'a few'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a few'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex1', 7);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'There is {a1} milk in the fridge. We need to buy some.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'no'],
            ],
            'options' => ['none', 'no', 'any'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'no'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex1', 8);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'He doesn\'t have {a1} hobbies.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'any'],
            ],
            'options' => ['none', 'no', 'any'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'any'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex1', 9);
        $items[] = [
            'uuid' => $uuid,
            'question' => '"How many computers do you have?" "{a1}."',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'None'],
            ],
            'options' => ['None', 'Any', 'No'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'None'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex1', 10);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'I can help you; I have {a1} time today.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'a lot of'],
            ],
            'options' => ['a lot of', 'much', 'many'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a lot of'], 'hints' => ['Quantifiers (A1)']];

        /**
         * =========================
         * Exercise 2 (10)
         * =========================
         */
        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex2', 1);
        $items[] = [
            'uuid' => $uuid,
            'question' => '"How much water do you drink?" "{a1}."',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'A lot'],
            ],
            'options' => ['Much', 'A lot of', 'A lot'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'A lot'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex2', 2);
        $items[] = [
            'uuid' => $uuid,
            'question' => '{a1} goals did they score?',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'How many'],
            ],
            'options' => ['How many', 'How much', 'How little'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'How many'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex2', 3);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'Nowadays we don\'t use {a1} cash, because we use our credit cards.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'much'],
            ],
            'options' => ['many', 'much', 'a lot'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'much'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex2', 4);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'There\'s {a1} pressure on the players.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'a lot of'],
            ],
            'options' => ['a lot of', 'much', 'many'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a lot of'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex2', 5);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'They got married {a1} months after they met for the first time.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'a few'],
            ],
            'options' => ['much', 'a little', 'a few'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a few'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex2', 6);
        $items[] = [
            'uuid' => $uuid,
            'question' => '"How much milk do you want in your coffee?" "Only {a1}."',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'a little'],
            ],
            'options' => ['a little', 'a few', 'much'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a little'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex2', 7);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'There were {a1} problems during the festival.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'no'],
            ],
            'options' => ['any', 'no', 'none'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'no'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex2', 8);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'I don\'t want {a1} gifts.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'any'],
            ],
            'options' => ['none', 'no', 'any'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'any'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex2', 9);
        $items[] = [
            'uuid' => $uuid,
            'question' => '"How many gifts do you want?" "{a1}."',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'None'],
            ],
            'options' => ['None', 'Any', 'No'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'None'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex2', 10);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'I eat {a1} vegetables.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'a lot of'],
            ],
            'options' => ['much', 'a lot of', 'many'],
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a lot of'], 'hints' => ['Quantifiers (A1)']];

        /**
         * =========================
         * Exercise 3 (10)
         * word bank: much, how much, many, a lot of, a lot, a little, a few, any, no, none
         * =========================
         */
        $bank = ['much', 'How much', 'many', 'a lot of', 'a lot', 'a little', 'a few', 'any', 'no', 'none'];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex3', 1);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'He has {a1} paintings. It\'s a really big collection.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [['marker' => 'a1', 'answer' => 'a lot of']],
            'options' => $bank,
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a lot of'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex3', 2);
        $items[] = [
            'uuid' => $uuid,
            'question' => '{a1} salt do you put in your food?',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [['marker' => 'a1', 'answer' => 'How much']],
            'options' => $bank,
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'How much'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex3', 3);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'I normally use {a1} makeup, but not much, only some lipstick.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [['marker' => 'a1', 'answer' => 'a little']],
            'options' => $bank,
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a little'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex3', 4);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'It rained {a1} last week; it rained every day.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [['marker' => 'a1', 'answer' => 'a lot']],
            'options' => $bank,
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a lot'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex3', 5);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'They only scored one goal. They didn\'t have {a1} opportunities to score; maybe two or three.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [['marker' => 'a1', 'answer' => 'many']],
            'options' => $bank,
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'many'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex3', 6);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'Please, I just need 5 minutes of your time. I only have {a1} questions.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [['marker' => 'a1', 'answer' => 'a few']],
            'options' => $bank,
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'a few'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex3', 7);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'We had {a1} problems. It was 100% perfect.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [['marker' => 'a1', 'answer' => 'no']],
            'options' => $bank,
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'no'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex3', 8);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'We need to be fast; we haven\'t got {a1} time, only 20 minutes.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [['marker' => 'a1', 'answer' => 'much']],
            'options' => $bank,
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'much'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex3', 9);
        $items[] = [
            'uuid' => $uuid,
            'question' => 'I didn\'t see {a1} people in the room. It was completely empty.',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [['marker' => 'a1', 'answer' => 'any']],
            'options' => $bank,
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'any'], 'hints' => ['Quantifiers (A1)']];

        $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', 'ex3', 10);
        $items[] = [
            'uuid' => $uuid,
            'question' => '"How many cups of coffee did you have?" "{a1}. I don\'t drink coffee."',
            'category_id' => $categoryId,
            'source_id' => $sourceId,
            'level' => 'A1',
            'difficulty' => 1,
            'type' => null,
            'answers' => [['marker' => 'a1', 'answer' => 'none']],
            'options' => $bank,
        ];
        $meta[] = ['uuid' => $uuid, 'answers' => ['a1' => 'none'], 'hints' => ['Quantifiers (A1)']];

        $this->seedQuestionData($items, $meta);
    }
}
