<?php

namespace Database\Seeders\V2\Modals;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Database\Seeders\QuestionSeeder;
use Illuminate\Support\Facades\Schema;

class ModalDeductionPossibilityPracticeV2MatchSeeder extends QuestionSeeder
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
        $categoryId = Category::firstOrCreate(['name' => 'Modal Deduction & Possibility Practice V2'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Modal Deduction Possibility V2 - Match Exercise'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'Modals']
        )->id;

        $matchExercise = [
            'title' => 'Match the Sentences with Their Meanings',
            'level' => 'B1',
            'left' => [
                ['key' => 'd', 'text' => "I can't speak Spanish but I might go with you."],
                ['key' => 'f', 'text' => "I'm free on Tuesday, mum. I can take you to the mall."],
                ['key' => 'e', 'text' => "Ben's parents ought not to let him go to bed so late."],
                ['key' => 'h', 'text' => "You should save some money."],
                ['key' => 'a', 'text' => "Excuse me, could you tell me the time, please?"],
                ['key' => 'b', 'text' => "I was wondering if I might leave earlier."],
                ['key' => 'c', 'text' => "I'm not going with you. I can't swim."],
                ['key' => 'g', 'text' => "You mustn't drive on the right in England."],
            ],
            'right' => [
                ['key' => 'a', 'text' => "You're making a request."],
                ['key' => 'b', 'text' => "You're asking permission."],
                ['key' => 'c', 'text' => "You don't know how to do it."],
                ['key' => 'd', 'text' => "It's possible, but not certain."],
                ['key' => 'e', 'text' => "You're talking critically. You consider it wrong."],
                ['key' => 'f', 'text' => "It's possible for me to do it."],
                ['key' => 'g', 'text' => "It is forbidden to do it."],
                ['key' => 'h', 'text' => "You're giving some advice."],
            ],
            'answer_map' => [
                0 => 'd',
                1 => 'f',
                2 => 'e',
                3 => 'h',
                4 => 'a',
                5 => 'b',
                6 => 'c',
                7 => 'g',
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        $meta = [];

        foreach ($matchExercise['left'] as $index => $leftItem) {
            $leftKey = $leftItem['key'];
            $leftText = $leftItem['text'];
            $rightKey = $matchExercise['answer_map'][$index];
            $rightItem = collect($matchExercise['right'])->firstWhere('key', $rightKey);
            $rightText = $rightItem['text'] ?? '';

            $questionText = $leftText;
            $uuid = $this->generateQuestionUuid($index + 1, $questionText);

            $hint = $this->buildHint($leftText, $rightText);
            $explanation = $this->buildExplanation($leftText, $rightText);

            $items[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$matchExercise['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $matchExercise['level'],
                'tag_ids' => [$themeTagId, $modalsTagId],
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => $rightText,
                        'verb_hint' => null,
                    ],
                ],
                'options' => [$rightText],
                'variants' => [$questionText],
                'seeder' => static::class,
                'type' => '1',
            ];

            $meta[] = [
                'uuid' => $uuid,
                'hints' => ['a1' => $hint],
                'answer' => $rightText,
                'explanation' => $explanation,
            ];
        }

        $service->seed($items);

        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();
            if (!$question) {
                continue;
            }

            if (Schema::hasColumn('questions', 'seeder') && empty($question->seeder)) {
                $question->forceFill(['seeder' => static::class])->save();
            }

            if (Schema::hasColumn('questions', 'type')) {
                $question->forceFill(['type' => '1'])->save();
            }

            $hintText = $this->formatHintBlocks($data['hints']);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            ChatGPTExplanation::updateOrCreate(
                [
                    'question' => $question->question,
                    'wrong_answer' => '',
                    'correct_answer' => $data['answer'],
                    'language' => 'ua',
                ],
                ['explanation' => $data['explanation']]
            );
        }
    }

    private function buildHint(string $sentence, string $meaning): string
    {
        return "Речення: *{$sentence}*. Шукай пояснення, яке найкраще описує функцію модального дієслова в цьому реченні.";
    }

    private function buildExplanation(string $sentence, string $meaning): string
    {
        return "✅ Речення «{$sentence}» відповідає поясненню «{$meaning}».";
    }

    private function formatHintBlocks(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $marker => $text) {
            $clean = trim((string) $text);
            if ($clean === '') {
                continue;
            }
            $parts[] = '{' . $marker . '} ' . $clean;
        }

        if (empty($parts)) {
            return null;
        }

        return implode("\n", $parts);
    }
}
