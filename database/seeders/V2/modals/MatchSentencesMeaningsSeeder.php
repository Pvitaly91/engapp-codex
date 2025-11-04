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

class MatchSentencesMeaningsSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Match Sentences with Meanings'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'Modals']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Modal Meanings Match'],
            ['category' => 'English Grammar Detail']
        )->id;

        // Data structure for match exercise
        $matchData = [
            'title' => 'Match the Sentences with Their Meanings',
            'type' => 'match',
            'left' => [ // sentences (left side)
                ['key' => 'd', 'text' => "I can't speak Spanish but I might go with you."],
                ['key' => 'f', 'text' => "I'm free on Tuesday, mum. I can take you to the mall."],
                ['key' => 'e', 'text' => "Ben's parents ought not to let him go to bed so late."],
                ['key' => 'h', 'text' => "You should save some money."],
                ['key' => 'a', 'text' => "Excuse me, could you tell me the time, please?"],
                ['key' => 'b', 'text' => "I was wondering if I might leave earlier."],
                ['key' => 'c', 'text' => "I'm not going with you. I can't swim."],
                ['key' => 'g', 'text' => "You mustn't drive on the right in England."],
            ],
            'right' => [ // meanings (right side)
                ['key' => 'a', 'text' => "You're making a request."],
                ['key' => 'b', 'text' => "You're asking permission."],
                ['key' => 'c', 'text' => "You don't know how to do it."],
                ['key' => 'd', 'text' => "It's possible, but not certain."],
                ['key' => 'e', 'text' => "You're talking critically. You consider it wrong."],
                ['key' => 'f', 'text' => "It's possible for me to do it."],
                ['key' => 'g', 'text' => "It is forbidden to do it."],
                ['key' => 'h', 'text' => "You're giving some advice."],
            ],
            'answer_map' => [ // left_index => right_key
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

        $questionText = $this->buildMatchQuestionText($matchData);
        $uuid = $this->generateQuestionUuid(1, 'match-sentences-meanings');

        // Build answers from the match structure
        $answers = [];
        foreach ($matchData['left'] as $index => $leftItem) {
            $marker = 'a' . ($index + 1);
            $correctKey = $matchData['answer_map'][$index];
            $correctMeaning = collect($matchData['right'])->firstWhere('key', $correctKey)['text'] ?? '';
            
            $answers[] = [
                'marker' => $marker,
                'answer' => $correctKey,
                'verb_hint' => null,
            ];
        }

        // Build options from right side
        $options = collect($matchData['right'])->pluck('key')->toArray();

        $items = [[
            'uuid' => $uuid,
            'question' => $questionText,
            'category_id' => $categoryId,
            'difficulty' => $this->levelDifficulty['B1'] ?? 3,
            'source_id' => $sourceId,
            'flag' => 0,
            'level' => 'B1',
            'type' => '1', // Match type
            'tag_ids' => array_values(array_unique([$themeTagId, $modalsTagId, $detailTagId])),
            'answers' => $answers,
            'options' => $options,
            'variants' => [],
            'seeder' => static::class,
        ]];

        $meta = [[
            'uuid' => $uuid,
            'hints' => $this->buildHints($matchData),
            'answers' => collect($matchData['left'])->mapWithKeys(function ($leftItem, $index) use ($matchData) {
                $marker = 'a' . ($index + 1);
                $correctKey = $matchData['answer_map'][$index];
                return [$marker => $correctKey];
            })->toArray(),
            'explanations' => $this->buildExplanations($matchData),
            'option_markers' => [],
        ]];

        $service = new QuestionSeedingService();
        $service->seed($items);

        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();
            if (!$question) {
                continue;
            }

            if (Schema::hasColumn('questions', 'seeder') && empty($question->seeder)) {
                $question->forceFill(['seeder' => static::class])->save();
            }

            if (Schema::hasColumn('questions', 'type') && empty($question->type)) {
                $question->forceFill(['type' => '1'])->save();
            }

            $hintText = $this->formatHintBlocks($data['hints']);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            foreach ($data['explanations'] as $option => $text) {
                $correct = $data['answers']['a1'] ?? reset($data['answers']);
                if (!is_string($correct)) {
                    $correct = (string) $correct;
                }

                ChatGPTExplanation::updateOrCreate(
                    [
                        'question' => $question->question,
                        'wrong_answer' => $option,
                        'correct_answer' => $correct,
                        'language' => 'ua',
                    ],
                    ['explanation' => $text]
                );
            }
        }
    }

    private function buildMatchQuestionText(array $matchData): string
    {
        $parts = [];
        $parts[] = "**" . $matchData['title'] . "**\n";
        $parts[] = "Match each sentence with its meaning:\n";
        
        foreach ($matchData['left'] as $index => $leftItem) {
            $marker = '{a' . ($index + 1) . '}';
            $parts[] = ($index + 1) . ". " . $leftItem['text'] . " — " . $marker;
        }
        
        $parts[] = "\n**Meanings:**";
        foreach ($matchData['right'] as $rightItem) {
            $parts[] = $rightItem['key'] . ") " . $rightItem['text'];
        }

        return implode("\n", $parts);
    }

    private function buildHints(array $matchData): array
    {
        $hints = [];
        
        foreach ($matchData['left'] as $index => $leftItem) {
            $marker = 'a' . ($index + 1);
            $correctKey = $matchData['answer_map'][$index];
            $correctMeaning = collect($matchData['right'])->firstWhere('key', $correctKey)['text'] ?? '';
            
            $hints[$marker] = "Речення: \"{$leftItem['text']}\"\n" .
                "Підказка: Зверніть увагу на модальне дієслово і контекст використання.\n" .
                "Правильне значення: {$correctMeaning}";
        }
        
        return $hints;
    }

    private function buildExplanations(array $matchData): array
    {
        $explanations = [];
        
        foreach ($matchData['right'] as $rightItem) {
            $key = $rightItem['key'];
            $meaning = $rightItem['text'];
            
            // Find which sentence matches this meaning
            $matchingIndex = array_search($key, $matchData['answer_map']);
            
            if ($matchingIndex !== false) {
                $sentence = $matchData['left'][$matchingIndex]['text'];
                $explanations[$key] = "✅ Правильно! Речення \"{$sentence}\" означає: {$meaning}";
            } else {
                $explanations[$key] = "❌ Неправильна відповідь. Перевірте значення модального дієслова.";
            }
        }
        
        return $explanations;
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
