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

class ModalDeductionPossibilityPracticeV2DialogueSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Modal Deduction Possibility V2 - Dialogue Exercise'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'Modals']
        )->id;

        $dialogue = [
            [
                'speaker' => 'A',
                'text' => 'Hi, Tom. Would you like to come to the cinema with me this evening?',
                'level' => 'B1',
            ],
            [
                'speaker' => 'B',
                'text' => "No, sorry, I can't. We're going to my sister's graduation. We {a1} (take) the train this evening because the ceremony is early tomorrow morning.",
                'correct' => 'are taking',
                'verb_hint' => 'take',
                'level' => 'B1',
            ],
            [
                'speaker' => 'A',
                'text' => "Well, that should be good. What's her degree in?",
                'level' => 'B1',
            ],
            [
                'speaker' => 'B',
                'text' => "She's been studying archaeology for the last two years. She was studying history too and {a1} (do) really well in it, but then she decided to specialise.",
                'correct' => 'did',
                'verb_hint' => 'do',
                'level' => 'B1',
            ],
            [
                'speaker' => 'A',
                'text' => "What's she going to do next?",
                'level' => 'B1',
            ],
            [
                'speaker' => 'B',
                'text' => "She's really lucky. She speaks Italian and she {a1} (work) on a dig in Rome when I see her next.",
                'correct' => 'will be working',
                'verb_hint' => 'work',
                'level' => 'B1',
            ],
            [
                'speaker' => 'B',
                'text' => "We're all going to visit her there in the summer holidays. Then I think she wants to do a master's in Italy as well. By the time she finishes, she {a1} (study) for six years. I think she wants to be an academic.",
                'correct' => 'will have been studying',
                'verb_hint' => 'study',
                'level' => 'B1',
            ],
            [
                'speaker' => 'A',
                'text' => "Isn't it great that she {a1} (find) something she really wants to do?",
                'correct' => 'has found',
                'verb_hint' => 'find',
                'level' => 'B1',
            ],
            [
                'speaker' => 'B',
                'text' => 'Definitely. My parents are really proud.',
                'level' => 'B1',
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        $meta = [];

        foreach ($dialogue as $index => $line) {
            if (!isset($line['correct'])) {
                continue;
            }

            $speaker = $line['speaker'];
            $text = $line['text'];
            $correct = $line['correct'];
            $verbHint = $line['verb_hint'] ?? '';
            $level = $line['level'] ?? 'B1';

            $questionText = "{$speaker}: {$text}";
            $uuid = $this->generateQuestionUuid($index + 1, $questionText);

            $hint = $this->buildHint($verbHint, $correct);
            $explanation = $this->buildExplanation($correct, $questionText);

            $items[] = [
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
                        'answer' => $correct,
                        'verb_hint' => $verbHint,
                    ],
                ],
                'options' => [$correct],
                'variants' => [$questionText],
                'seeder' => static::class,
                'type' => '2',
            ];

            $meta[] = [
                'uuid' => $uuid,
                'hints' => ['a1' => $hint],
                'answer' => $correct,
                'explanation' => $explanation,
                'verb_hint' => $verbHint,
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
                $question->forceFill(['type' => '2'])->save();
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

    private function buildHint(string $verbHint, string $correct): string
    {
        return "Підказка: використай дієслово «{$verbHint}» у правильній формі. Правильна відповідь: *{$correct}*.";
    }

    private function buildExplanation(string $correct, string $context): string
    {
        return "✅ Правильна відповідь: «{$correct}». Контекст: *{$context}*.";
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
