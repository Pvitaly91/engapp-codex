<?php

namespace Database\Seeders\QuestionWords;

use App\Models\InteractiveTest;
use App\Support\Database\Seeder;

class QuestionWordsDragDropSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            ['template' => '_____ is your hobby?', 'answer' => 'What', 'tail' => '– Drawing'],
            ['template' => '_____ did you live last year?', 'answer' => 'Where', 'tail' => '– In London'],
            ['template' => '_____ are you late?', 'answer' => 'Why', 'tail' => '– I’ve missed my bus.'],
            ['template' => '_____ lessons do you have?', 'answer' => 'How many', 'tail' => '– Six lessons'],
            ['template' => '_____ is that man at the door?', 'answer' => 'Who', 'tail' => '– My uncle.'],
            ['template' => '_____ do you go to the club?', 'answer' => 'When', 'tail' => '– At 6 o’clock'],
            ['template' => '_____ did you feel yesterday?', 'answer' => 'How', 'tail' => '– Awfully'],
            ['template' => '_____ is your sister?', 'answer' => 'How old', 'tail' => '– She is eleven.'],
            ['template' => '_____ are you crying?', 'answer' => 'Why', 'tail' => '– I’ve lost my keys.'],
            ['template' => '_____ will you return?', 'answer' => 'When', 'tail' => '– In two days'],
            ['template' => '_____ books have you bought?', 'answer' => 'How many', 'tail' => '– Three books'],
            ['template' => '_____ is your dad?', 'answer' => 'How old', 'tail' => '– He is 45.'],
            ['template' => '_____ will the concert start?', 'answer' => 'When', 'tail' => '– At seven p.m.'],
            ['template' => '_____ is playing with the dog?', 'answer' => 'Who', 'tail' => '– My friend Tom'],
            ['template' => '_____ is the kitten?', 'answer' => 'Where', 'tail' => '– Under the table'],
            ['template' => '_____ book is on the table?', 'answer' => 'Whose', 'tail' => '– It’s mine.'],
            ['template' => '_____ will you get to London?', 'answer' => 'How', 'tail' => '– By car'],
            ['template' => '_____ do you do in the evening?', 'answer' => 'What', 'tail' => '– I usually watch TV.'],
            ['template' => '_____ friends do you have?', 'answer' => 'How many', 'tail' => '– I have a lot of friends.'],
            ['template' => '_____ is the tea?', 'answer' => 'How much', 'tail' => '– It’s 50p.'],
            ['template' => '_____ cat is on the tree?', 'answer' => 'Whose', 'tail' => '– It’s Mona’s cat.'],
            ['template' => '_____ sports do you like?', 'answer' => 'What', 'tail' => '– I like basketball.'],
            ['template' => '_____ are your parents?', 'answer' => 'Where', 'tail' => '– They are in the shop.'],
            ['template' => '_____ swims faster: you or Alec?', 'answer' => 'Who', 'tail' => '– Alec swims faster.'],
            ['template' => '_____ is your new car?', 'answer' => 'How much', 'tail' => '– It’s very expensive.'],
            ['template' => '_____ will you spend your holiday?', 'answer' => 'Where', 'tail' => '– In Greece, I think.'],
            ['template' => '_____ will you go to Paris?', 'answer' => 'How', 'tail' => '– By plane.'],
            ['template' => '_____ bag is it?', 'answer' => 'Whose', 'tail' => '– It’s Tom’s bag.'],
        ];

        InteractiveTest::updateOrCreate(
            ['slug' => 'question-words-drag-drop'],
            [
                'name' => 'Question Words — Drag & Drop',
                'type' => 'question_words',
                'data' => [
                    'questions' => $questions,
                ],
            ]
        );
    }
}
