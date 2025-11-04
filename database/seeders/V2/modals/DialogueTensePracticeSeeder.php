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

class DialogueTensePracticeSeeder extends QuestionSeeder
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
        $categoryId = Category::firstOrCreate(['name' => 'Future Tenses Practice'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Dialogue Tense Practice'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Future Tenses'],
            ['category' => 'English Grammar Theme']
        )->id;

        $tenseTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Tenses'],
            ['category' => 'English Grammar Tense']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Dialogue Practice'],
            ['category' => 'English Grammar Detail']
        )->id;

        // Original dialogue data
        $dialogue = [
            [
                'speaker' => 'A',
                'text' => 'Hi, Tom. Would you like to come to the cinema with me this evening?',
            ],
            [
                'speaker' => 'B',
                'text' => "No, sorry, I can't. We're going to my sister's graduation. We (take) the train this evening because the ceremony is early tomorrow morning.",
                'correct' => 'are taking',
                'verb_hint' => 'take',
            ],
            [
                'speaker' => 'A',
                'text' => "Well, that should be good. What's her degree in?",
            ],
            [
                'speaker' => 'B',
                'text' => "She's been studying archaeology for the last two years. She was studying history too and (do) really well in it, but then she decided to specialise.",
                'correct' => 'did',
                'verb_hint' => 'do',
            ],
            [
                'speaker' => 'A',
                'text' => "What's she going to do next?",
            ],
            [
                'speaker' => 'B',
                'text' => "She's really lucky. She speaks Italian and she (work) on a dig in Rome when I see her next.",
                'correct' => 'will be working',
                'verb_hint' => 'work',
            ],
            [
                'speaker' => 'B',
                'text' => "We're all going to visit her there in the summer holidays. Then I think she wants to do a master's in Italy as well. By the time she finishes, she (study) for six years. I think she wants to be an academic.",
                'correct' => 'will have been studying',
                'verb_hint' => 'study',
            ],
            [
                'speaker' => 'A',
                'text' => "Isn't it great that she (find) something she really wants to do?",
                'correct' => 'has found',
                'verb_hint' => 'find',
            ],
            [
                'speaker' => 'B',
                'text' => 'Definitely. My parents are really proud.',
            ],
        ];

        $questionText = $this->buildDialogueQuestionText($dialogue);
        $uuid = $this->generateQuestionUuid(1, 'dialogue-tense-practice');

        // Build answers from dialogue items with correct answers
        $answers = [];
        $markerIndex = 1;
        foreach ($dialogue as $item) {
            if (isset($item['correct'])) {
                $marker = 'a' . $markerIndex;
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $item['correct'],
                    'verb_hint' => $item['verb_hint'] ?? null,
                ];
                $markerIndex++;
            }
        }

        // Build options from all correct answers (in real scenario, you'd add more options)
        $options = collect($dialogue)
            ->filter(fn($item) => isset($item['correct']))
            ->pluck('correct')
            ->unique()
            ->toArray();

        // Add some additional common options for variety
        $additionalOptions = ['will take', 'is doing', 'works', 'studies', 'found'];
        $options = array_unique(array_merge($options, $additionalOptions));

        $items = [[
            'uuid' => $uuid,
            'question' => $questionText,
            'category_id' => $categoryId,
            'difficulty' => $this->levelDifficulty['B1'] ?? 3,
            'source_id' => $sourceId,
            'flag' => 0,
            'level' => 'B1',
            'type' => '2', // Dialogue type
            'tag_ids' => array_values(array_unique([$themeTagId, $tenseTagId, $detailTagId])),
            'answers' => $answers,
            'options' => $options,
            'variants' => [],
            'seeder' => static::class,
        ]];

        $meta = [[
            'uuid' => $uuid,
            'hints' => $this->buildHints($dialogue),
            'answers' => collect($dialogue)
                ->filter(fn($item) => isset($item['correct']))
                ->values()
                ->mapWithKeys(function ($item, $index) {
                    $marker = 'a' . ($index + 1);
                    return [$marker => $item['correct']];
                })
                ->toArray(),
            'explanations' => $this->buildExplanations($dialogue),
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
                $question->forceFill(['type' => '2'])->save();
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

    private function buildDialogueQuestionText(array $dialogue): string
    {
        $parts = [];
        $parts[] = "**Complete the Dialogue**";
        $parts[] = "Fill in the correct verb forms:\n";
        
        $markerIndex = 1;
        foreach ($dialogue as $item) {
            $speaker = $item['speaker'];
            $text = $item['text'];
            
            // Replace (verb) with {aN} marker
            if (isset($item['verb_hint'])) {
                $marker = '{a' . $markerIndex . '}';
                // Find text in parentheses and replace with marker
                $text = preg_replace('/\([^)]+\)/', $marker, $text);
                $markerIndex++;
            }
            
            $parts[] = "**{$speaker}:** {$text}";
        }

        return implode("\n", $parts);
    }

    private function buildHints(array $dialogue): array
    {
        $hints = [];
        $markerIndex = 1;
        
        foreach ($dialogue as $item) {
            if (isset($item['correct']) && isset($item['verb_hint'])) {
                $marker = 'a' . $markerIndex;
                $hints[$marker] = "Підказка: Використайте дієслово '{$item['verb_hint']}' у правильній формі.\n" .
                    "Зверніть увагу на контекст і час дії.\n" .
                    "Правильна форма: {$item['correct']}";
                $markerIndex++;
            }
        }
        
        return $hints;
    }

    private function buildExplanations(array $dialogue): array
    {
        $explanations = [];
        
        // Collect all correct answers
        $correctAnswers = collect($dialogue)
            ->filter(fn($item) => isset($item['correct']))
            ->pluck('correct', 'verb_hint')
            ->toArray();
        
        foreach ($correctAnswers as $verb => $correct) {
            $explanations[$correct] = "✅ Правильно! '{$correct}' — це правильна форма дієслова '{$verb}' у даному контексті.";
        }
        
        // Add some common wrong explanations
        $wrongOptions = ['will take', 'is doing', 'works', 'studies', 'found'];
        foreach ($wrongOptions as $wrong) {
            if (!isset($explanations[$wrong])) {
                $explanations[$wrong] = "❌ Неправильна форма. Перевірте час дієслова і контекст речення.";
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
