<?php

namespace Database\Seeders\Ai\chatGpt;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class IndefinitePronounsPracticeAiChatGptSeeder extends QuestionSeeder
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
        $categoryId = Category::firstOrCreate(['name' => 'Pronouns'])->id;

        $sourceIds = [
            'negative' => Source::firstOrCreate(['name' => 'AI ChatGPT: Indefinite Pronouns — Negative'])->id,
            'question' => Source::firstOrCreate(['name' => 'AI ChatGPT: Indefinite Pronouns — Interrogative'])->id,
            'past' => Source::firstOrCreate(['name' => 'AI ChatGPT: Indefinite Pronouns — Past Tense'])->id,
            'present' => Source::firstOrCreate(['name' => 'AI ChatGPT: Indefinite Pronouns — Present Tense'])->id,
            'future' => Source::firstOrCreate(['name' => 'AI ChatGPT: Indefinite Pronouns — Future Tense'])->id,
        ];
        $defaultSourceId = $sourceIds['question'];

        $themeTagId = Tag::firstOrCreate([
            'name' => 'Indefinite Pronouns Practice AI'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate([
            'name' => 'Some/Any/No/Every Compounds (AI)'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate([
            'name' => 'Gap-fill Pronoun Forms (AI)'],
            ['category' => 'English Grammar Structure']
        )->id;

        $questions = $this->questionEntries();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            if (! is_array($entry) || ! isset($entry['answers'], $entry['options'])) {
                continue;
            }

            $entry = $this->applyDefaultSupports($entry);

            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $entry['verb_hints'][$marker] ?? null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);

            $uuid = $this->generateQuestionUuid($index + 1, $entry['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 2,
                'source_id' => $sourceIds[$entry['source']] ?? $defaultSourceId,
                'flag' => 2,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => [$themeTagId, $detailTagId, $structureTagId],
                'answers' => $answers,
                'options' => $options,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $entry['answers'],
                'hints' => $entry['hints'] ?? [],
                'explanations' => $entry['explanations'] ?? [],
            ];
        }

        $this->seedQuestionData($items, []);
        $this->attachHintsAndExplanations($meta);
    }

    private function attachHintsAndExplanations(array $meta): void
    {
        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();

            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints'] ?? []);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            $answers = $data['answers'] ?? [];
            foreach ($data['explanations'] ?? [] as $marker => $options) {
                if (! isset($answers[$marker])) {
                    $fallback = reset($answers);
                    $answers[$marker] = is_string($fallback) ? $fallback : (string) $fallback;
                }

                $correct = $answers[$marker];
                if (! is_string($correct)) {
                    $correct = (string) $correct;
                }

                foreach ($options as $option => $text) {
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
    }

    private function questionEntries(): array
    {
        $entries = [];

        $levels = [
            'A1' => [
                'negative' => [
                    ['question' => "I don't see {a1} on the table.", 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => 'not'], 'hints' => ['a1' => 'Після заперечення використовуємо any-.']],
                    ['question' => "There isn't {a1} in my cup; it's empty.", 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => 'not'], 'hints' => ['a1' => 'Empty = жодного вмісту → any-']],
                    ['question' => 'We have {a1} left for dinner.', 'options' => ['a1' => ['nothing', 'something', 'anything']], 'answers' => ['a1' => 'nothing'], 'verb_hints' => ['a1' => 'not (we)']],
                    ['question' => 'The fridge has {a1}; we must shop.', 'options' => ['a1' => ['nothing', 'anything', 'something']], 'answers' => ['a1' => 'nothing'], 'verb_hints' => ['a1' => 'not']],
                    ['question' => "She doesn't want {a1} sweet tonight.", 'options' => ['a1' => ['anything', 'something', 'everything']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => 'not (she)']],
                    ['question' => "They aren't talking to {a1} today.", 'options' => ['a1' => ['anyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => 'not (they)']],
                ],
                'question' => [
                    ['question' => 'Is {a1} in the garden?', 'options' => ['a1' => ['anyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'Do you have {a1} to ask?', 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'Is {a1} hungry?', 'options' => ['a1' => ['anyone', 'someone', 'everyone']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'Did {a1} call me?', 'options' => ['a1' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'someone'], 'verb_hints' => ['a1' => 'he/she']],
                    ['question' => 'Can {a1} help me with this bag?', 'options' => ['a1' => ['anyone', 'someone', 'everyone']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'Is there {a1} to read here?', 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => null]],
                ],
                'past' => [
                    ['question' => 'Yesterday {a1} knocked on the door.', 'options' => ['a1' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'someone'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'We ate {a1} tasty at lunch.', 'options' => ['a1' => ['something', 'anything', 'everything']], 'answers' => ['a1' => 'something'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'Last night {a1} strange happened.', 'options' => ['a1' => ['something', 'nothing', 'anything']], 'answers' => ['a1' => 'something'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'Nobody was free; {a1} joined the game.', 'options' => ['a1' => ['no one', 'everyone', 'someone']], 'answers' => ['a1' => 'no one'], 'verb_hints' => ['a1' => 'not']],
                ],
                'present' => [
                    ['question' => '{a1} needs a seat on the bus.', 'options' => ['a1' => ['someone', 'anyone', 'everyone']], 'answers' => ['a1' => 'someone'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'There is {a1} in my pocket.', 'options' => ['a1' => ['nothing', 'anything', 'something']], 'answers' => ['a1' => 'something'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'She brings {a1} new every week.', 'options' => ['a1' => ['something', 'anything', 'everything']], 'answers' => ['a1' => 'something'], 'verb_hints' => ['a1' => null]],
                    ['question' => '{a1} knows the rules here.', 'options' => ['a1' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'everyone'], 'verb_hints' => ['a1' => null]],
                ],
                'future' => [
                    ['question' => 'Tomorrow {a1} will visit us.', 'options' => ['a1' => ['someone', 'anyone', 'everyone']], 'answers' => ['a1' => 'someone'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'I will not buy {a1} else today.', 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => 'not (I)']],
                    ['question' => 'Will {a1} arrive early for the trip?', 'options' => ['a1' => ['anyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'We will prepare {a1} special.', 'options' => ['a1' => ['something', 'anything', 'everything']], 'answers' => ['a1' => 'something'], 'verb_hints' => ['a1' => null]],
                ],
            ],
            'A2' => [
                'negative' => [
                    ['question' => "He didn't invite {a1} because {a2} was busy.", 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['everyone', 'anyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not (he)', 'a2' => null]],
                    ['question' => "They aren't giving {a1} extra seats; {a2} is available.", 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['nothing', 'anything', 'something']], 'answers' => ['a1' => 'anyone', 'a2' => 'nothing'], 'verb_hints' => ['a1' => 'not (they)', 'a2' => null]],
                    ['question' => "We didn't hear {a1} new in the update.", 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => 'not (we)']],
                    ['question' => "She hasn't told {a1} about the surprise.", 'options' => ['a1' => ['anyone', 'someone', 'everyone']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => 'not (she)']],
                    ['question' => 'There was {a1} to eat, so {a2} went hungry.', 'options' => ['a1' => ['anything', 'nothing', 'something'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'nothing', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => "He can't see {a1} without his glasses.", 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => 'not (he)']],
                ],
            'B1' => [
                'negative' => [
                    ['question' => "I haven't shared {a1} of the draft because {a2} is final yet.", 'options' => ['a1' => ['anything', 'something', 'nothing'], 'a2' => ['nothing', 'something', 'everything']], 'answers' => ['a1' => 'anything', 'a2' => 'nothing'], 'verb_hints' => ['a1' => 'not (I)', 'a2' => 'not']],
                    ['question' => "They wouldn't tell {a1} the secret; {a2} deserved to know.", 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not (they)', 'a2' => null]],
                    ['question' => "She didn't rely on {a1} else when the system failed.", 'options' => ['a1' => ['anyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => 'not (she)']],
                    ['question' => "We couldn't find {a1} that matched the description.", 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => 'not (we)']],
                    ['question' => "The manager isn't letting {a1} leave early, and {a2} likes it.", 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'anyone', 'a2' => 'no one'], 'verb_hints' => ['a1' => 'not', 'a2' => 'not']],
                    ['question' => 'By midnight, there had not been {a1} helpful feedback.', 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => 'not']],
                ],
                'question' => [
                    ['question' => 'Has {a1} already booked {a2} seats for the concert?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Would {a1} like to join, or is {a2} too busy?', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Have {a1} completed {a2} for the presentation?', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Could {a1} tell us if {a2} left messages?', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['anyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'anyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Is there {a1} else we should review, or have we covered {a2}?', 'options' => ['a1' => ['anything', 'something', 'nothing'], 'a2' => ['everything', 'something', 'nothing']], 'answers' => ['a1' => 'anything', 'a2' => 'everything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Was {a1} from the audience asking questions, or was {a2} silent?', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
                'past' => [
                    ['question' => 'By the time we arrived, {a1} had fixed the issue, and {a2} else noticed.', 'options' => ['a1' => ['someone', 'anyone', 'no one'], 'a2' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'someone', 'a2' => 'no one'], 'verb_hints' => ['a1' => null, 'a2' => 'not']],
                    ['question' => '{a1} said {a2} during the meeting, so it ended early.', 'options' => ['a1' => ['no one', 'someone', 'everyone'], 'a2' => ['nothing', 'anything', 'something']], 'answers' => ['a1' => 'no one', 'a2' => 'nothing'], 'verb_hints' => ['a1' => 'not', 'a2' => 'not']],
                    ['question' => 'We found {a1} left in the lockers because {a2} cleaned them.', 'options' => ['a1' => ['nothing', 'anything', 'something'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'nothing', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => '{a1} had promised {a2}, yet {a3} delivered.', 'options' => ['a1' => ['everyone', 'someone', 'anyone'], 'a2' => ['everything', 'something', 'nothing'], 'a3' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'everyone', 'a2' => 'something', 'a3' => 'no one'], 'verb_hints' => ['a1' => null, 'a2' => null, 'a3' => 'not']],
                ],
                'present' => [
                    ['question' => '{a1} on the team handles crises, and {a2} avoids them.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'everyone', 'a2' => 'no one'], 'verb_hints' => ['a1' => null, 'a2' => 'not']],
                    ['question' => 'There is hardly {a1} left to discuss, but {a2} keeps asking.', 'options' => ['a1' => ['anything', 'something', 'nothing'], 'a2' => ['someone', 'anyone', 'everyone']], 'answers' => ['a1' => 'anything', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => '{a1} believes {a2} can change without feedback.', 'options' => ['a1' => ['no one', 'someone', 'everyone'], 'a2' => ['anyone', 'someone', 'everyone']], 'answers' => ['a1' => 'no one', 'a2' => 'anyone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'If {a1} needs help, {a2} offers support immediately.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
                'future' => [
                    ['question' => 'Soon {a1} will know the results, and {a2} will celebrate together.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'everyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Will {a1} bring {a2} to share, or should we prepare extras?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['something', 'anything', 'nothing']], 'answers' => ['a1' => 'someone', 'a2' => 'something'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => "We will not allow {a1} to feel excluded; {a2} deserves a voice.", 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not (we)', 'a2' => null]],
                    ['question' => 'If {a1} arrives late, {a2} will record the meeting.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
            ],
            'B2' => [
                'negative' => [
                    ['question' => 'The committee has not granted {a1} special favors, and {a2} complained.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'anyone', 'a2' => 'no one'], 'verb_hints' => ['a1' => 'not', 'a2' => 'not']],
                    ['question' => "We aren't relying on {a1} else now that the plan failed.", 'options' => ['a1' => ['anyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => 'not (we)']],
                    ['question' => 'She scarcely trusts {a1}; past mistakes haunt her.', 'options' => ['a1' => ['anyone', 'someone', 'everyone']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => 'not (she)']],
                    ['question' => 'He would not let {a1} interrupt because {a2} was presenting.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not (he)', 'a2' => null]],
                    ['question' => "There hasn't been {a1} consistent guidance since the merger.", 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => 'not']],
                    ['question' => 'They never exclude {a1}, but today {a2} felt ignored.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Hardly {a1} acknowledged the bias, and {a2} apologized afterward.', 'options' => ['a1' => ['anything', 'something', 'nothing'], 'a2' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'anything', 'a2' => 'no one'], 'verb_hints' => ['a1' => 'not', 'a2' => 'not']],
                    ['question' => 'We are not granting {a1} exemptions now that {a2} challenged the process.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not (we)', 'a2' => null]],
                ],
                'question' => [
                    ['question' => 'Has {a1} raised {a2} objection during negotiations?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Is {a1} willing to mentor us, or is {a2} overloaded?', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Did {a1} notice {a2} unusual about the report?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Could {a1} confirm whether {a2} agreed to the timeline?', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Will {a1} share {a2} data before the audit?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['something', 'anything', 'nothing']], 'answers' => ['a1' => 'someone', 'a2' => 'something'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Have {a1} been waiting long, or has {a2} just arrived?', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'everyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Would {a1} be able to flag {a2} missing references in the draft?', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'someone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Has {a1} confirmed whether {a2} else is still undecided?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'no one'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
                'past' => [
                    ['question' => 'By dawn {a1} had responded, yet {a2} remained unanswered.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['nothing', 'anything', 'something']], 'answers' => ['a1' => 'everyone', 'a2' => 'nothing'], 'verb_hints' => ['a1' => null, 'a2' => 'not']],
                    ['question' => '{a1} volunteered when {a2} else hesitated.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'After the storm, {a1} was left intact; {a2} had been damaged.', 'options' => ['a1' => ['nothing', 'anything', 'something'], 'a2' => ['everything', 'something', 'nothing']], 'answers' => ['a1' => 'nothing', 'a2' => 'everything'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'They said {a1}, but {a2} remembered the warning.', 'options' => ['a1' => ['nothing', 'anything', 'something'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'nothing', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'There was {a1} unusual in the logs until {a2} uncovered anomalies.', 'options' => ['a1' => ['nothing', 'anything', 'something'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'nothing', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'During the audit, {a1} raised concerns, and {a2} addressed them promptly.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
                'present' => [
                    ['question' => '{a1} expects transparency, so {a2} keeps minutes meticulously.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'everyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'There is {a1} ambiguous in the policy, yet {a2} keeps questioning it.', 'options' => ['a1' => ['nothing', 'anything', 'something'], 'a2' => ['someone', 'anyone', 'everyone']], 'answers' => ['a1' => 'nothing', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => '{a1} doubts that {a2} truly understands the risks.', 'options' => ['a1' => ['no one', 'someone', 'everyone'], 'a2' => ['everyone', 'someone', 'anyone']], 'answers' => ['a1' => 'no one', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'If {a1} raises concerns, {a2} documents them immediately.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => '{a1} assumes {a2} will volunteer if asked directly.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'everyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Hardly {a1} wants to postpone, though {a2} keeps suggesting delays.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'anyone', 'everyone']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                ],
                'future' => [
                    ['question' => 'Soon {a1} will contribute, and {a2} will benefit.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'everyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Will {a1} submit {a2} before the deadline?', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'someone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'We will not permit {a1} to dominate the discussion; {a2} should participate.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not (we)', 'a2' => null]],
                    ['question' => 'If {a1} misses the train, {a2} will arrange transport.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'If {a1} does not respond, {a2} will escalate the issue tomorrow.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'Soon {a1} will circulate {a2} draft scenarios for feedback.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['something', 'anything', 'nothing']], 'answers' => ['a1' => 'someone', 'a2' => 'something'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
            ],
            'C1' => [
                'negative' => [
                    ['question' => 'He would never concede that {a1} else had foresight, insisting {a2} predicted the crisis.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'anyone', 'a2' => 'no one'], 'verb_hints' => ['a1' => 'not (he)', 'a2' => 'not']],
                    ['question' => "The panel hasn't credited {a1} with the discovery, leaving {a2} disappointed.", 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => "She hadn't promised {a1} confidentiality, yet {a2} assumed secrecy.", 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'We scarcely received {a1} constructive criticism during the pilot.', 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => 'not']],
                    ['question' => 'They declined to involve {a1} else while {a2} negotiated.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Not even {a1} dared to challenge the data once {a2} presented it.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'The committee had not offered {a1} reassurance, so {a2} remained skeptical.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'She would not brief {a1} until {a2} signed the agreement.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not (she)', 'a2' => null]],
                ],
                'question' => [
                    ['question' => 'Would {a1} have anticipated {a2} gap in the research?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Has {a1} else reported anomalies, or has {a2} remained silent?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Could {a1} outline why {a2} disagreed with the proposal?', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Did {a1} flag {a2} unusual correlations in the dataset?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Will {a1} volunteer {a2} examples during the defense?', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['something', 'anything', 'nothing']], 'answers' => ['a1' => 'someone', 'a2' => 'something'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Had {a1} been aware of the risk, would {a2} have acted differently?', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'everyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Would {a1} clarify whether {a2} implications were considered?', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everything', 'something', 'nothing']], 'answers' => ['a1' => 'someone', 'a2' => 'everything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Has {a1} determined if {a2} objections remain unresolved?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
                'past' => [
                    ['question' => 'By publication, {a1} had reviewed the manuscript and {a2} requested revisions.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['someone', 'no one', 'everyone']], 'answers' => ['a1' => 'everyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => '{a1} admitted {a2} during the hearing, though {a3} believed the opposite.', 'options' => ['a1' => ['no one', 'someone', 'everyone'], 'a2' => ['nothing', 'anything', 'something'], 'a3' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'no one', 'a2' => 'nothing', 'a3' => 'everyone'], 'verb_hints' => ['a1' => 'not', 'a2' => 'not', 'a3' => null]],
                    ['question' => 'After months, {a1} remained unresolved because {a2} responded slowly.', 'options' => ['a1' => ['something', 'nothing', 'anything'], 'a2' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'something', 'a2' => 'no one'], 'verb_hints' => ['a1' => null, 'a2' => 'not']],
                    ['question' => '{a1} had promised {a2}, but {a3} delivered beyond expectations.', 'options' => ['a1' => ['everyone', 'someone', 'anyone'], 'a2' => ['everything', 'something', 'nothing'], 'a3' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'everyone', 'a2' => 'everything', 'a3' => 'no one'], 'verb_hints' => ['a1' => null, 'a2' => null, 'a3' => 'not']],
                    ['question' => 'Before the launch, {a1} had flagged {a2}, yet {a3} dismissed it.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['something', 'anything', 'nothing'], 'a3' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'something', 'a3' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null, 'a3' => null]],
                    ['question' => 'In the draft, {a1} promised {a2}, but {a3} never delivered.', 'options' => ['a1' => ['everyone', 'someone', 'anyone'], 'a2' => ['something', 'anything', 'nothing'], 'a3' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'everyone', 'a2' => 'something', 'a3' => 'no one'], 'verb_hints' => ['a1' => null, 'a2' => null, 'a3' => 'not']],
                ],
                'present' => [
                    ['question' => '{a1} claims {a2} can foresee every consequence, which few believe.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'There is hardly {a1} about the methodology that {a2} has not scrutinized.', 'options' => ['a1' => ['anything', 'something', 'nothing'], 'a2' => ['anyone', 'someone', 'everyone']], 'answers' => ['a1' => 'anything', 'a2' => 'anyone'], 'verb_hints' => ['a1' => 'not', 'a2' => 'not']],
                    ['question' => '{a1} expects {a2} to question the conclusion during review.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['someone', 'anyone', 'everyone']], 'answers' => ['a1' => 'everyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'If {a1} raises concerns now, {a2} will adjust the experiment promptly.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => '{a1} suspects {a2} will contest the citation metrics.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['someone', 'anyone', 'everyone']], 'answers' => ['a1' => 'someone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'There is almost {a1} that {a2} has not rechecked this week.', 'options' => ['a1' => ['nothing', 'anything', 'something'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'nothing', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                ],
                'future' => [
                    ['question' => 'Soon {a1} will request clarifications, and {a2} will provide them in full.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'everyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Will {a1} submit {a2} before the grant window closes?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'We will not let {a1} dismiss peer feedback; {a2} deserves respect.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not (we)', 'a2' => null]],
                    ['question' => 'If {a1} misses the briefing, {a2} will circulate notes immediately.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'If {a1} fails to justify the costs, {a2} will veto the plan.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'Soon {a1} will compile {a2} illustrative cases for the panel.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['something', 'anything', 'nothing']], 'answers' => ['a1' => 'someone', 'a2' => 'something'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
            ],
            'C2' => [
                'negative' => [
                    ['question' => 'No scenario persuaded {a1} that {a2} else possessed deeper insight.', 'options' => ['a1' => ['him', 'her', 'them'], 'a2' => ['anyone', 'someone', 'everyone']], 'answers' => ['a1' => 'him', 'a2' => 'anyone'], 'verb_hints' => ['a1' => 'not (he)', 'a2' => 'not']],
                    ['question' => 'The committee scarcely acknowledged {a1} of the warnings, so {a2} were ignored.', 'options' => ['a1' => ['any', 'some', 'no'], 'a2' => ['many', 'all', 'none']], 'answers' => ['a1' => 'any', 'a2' => 'none'], 'verb_hints' => ['a1' => 'not', 'a2' => 'not']],
                    ['question' => "She hadn't allowed {a1} access until {a2} promised discretion.", 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'We could not attribute the leak to {a1}; {a2} claimed responsibility.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'anyone', 'a2' => 'no one'], 'verb_hints' => ['a1' => 'not (we)', 'a2' => 'not']],
                    ['question' => 'There has been hardly {a1} transparency, and {a2} trusts the process.', 'options' => ['a1' => ['any', 'some', 'no'], 'a2' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'any', 'a2' => 'no one'], 'verb_hints' => ['a1' => 'not', 'a2' => 'not']],
                    ['question' => 'Not even {a1} colleagues objected when {a2} dismissed the evidence.', 'options' => ['a1' => ['her', 'his', 'their'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'her', 'a2' => 'anyone'], 'verb_hints' => ['a1' => null, 'a2' => 'not']],
                    ['question' => 'No record showed that {a1} had consulted {a2} experts before publishing.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['any', 'some', 'no']], 'answers' => ['a1' => 'anyone', 'a2' => 'any'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'They refused to grant {a1} access while {a2} questioned the ethics.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                ],
                'question' => [
                    ['question' => 'Would {a1} have challenged the premise if {a2} had withheld the data?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Has {a1} provided {a2} beyond speculation to justify the claim?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Could {a1} articulate why {a2} refrained from publishing?', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Did {a1} encounter {a2} irregularities during replication?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Will {a1} supply {a2} counterexamples if reviewers request them?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['any', 'some', 'no']], 'answers' => ['a1' => 'someone', 'a2' => 'any'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Had {a1} foreseen the backlash, would {a2} have revised earlier?', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'everyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Would {a1} have documented {a2} conflicting results if asked?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['any', 'some', 'no']], 'answers' => ['a1' => 'anyone', 'a2' => 'any'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Has {a1} identified whether {a2} still resists transparency?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
                'past' => [
                    ['question' => 'By the defense, {a1} had scrutinized the model while {a2} requested clarifications.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['someone', 'no one', 'everyone']], 'answers' => ['a1' => 'everyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => '{a1} disclosed {a2} during cross-examination, yet {a3} dismissed it.', 'options' => ['a1' => ['no one', 'someone', 'everyone'], 'a2' => ['nothing', 'anything', 'something'], 'a3' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'no one', 'a2' => 'nothing', 'a3' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => 'not', 'a3' => null]],
                    ['question' => 'When the data leaked, {a1} accepted responsibility while {a2} stayed silent.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => 'not']],
                    ['question' => 'Nothing in the archive suggested {a1} had altered the figures, and {a2} proved it.', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['someone', 'anyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'Until the appeal, {a1} had provided {a2}, and {a3} archived it.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['something', 'anything', 'nothing'], 'a3' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'something', 'a3' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null, 'a3' => null]],
                    ['question' => 'After the breach, {a1} accepted none of the blame while {a2} took everything on.', 'options' => ['a1' => ['someone', 'anyone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'no one', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                ],
                'present' => [
                    ['question' => '{a1} assumes {a2} can contest the verdict if new evidence emerges.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['anyone', 'someone', 'everyone']], 'answers' => ['a1' => 'someone', 'a2' => 'anyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'There is scarcely {a1} the reviewers have left unexamined, though {a2} keeps digging.', 'options' => ['a1' => ['anything', 'something', 'nothing'], 'a2' => ['someone', 'anyone', 'everyone']], 'answers' => ['a1' => 'anything', 'a2' => 'someone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => '{a1} expects {a2} to contest the outcome in writing.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['someone', 'anyone', 'everyone']], 'answers' => ['a1' => 'everyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'If {a1} challenges the premise, {a2} will moderate the debate.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => '{a1} maintains that {a2} will contest every inference.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'There is scarcely {a1} in the proposal that {a2} has not questioned.', 'options' => ['a1' => ['anything', 'something', 'nothing'], 'a2' => ['anyone', 'someone', 'everyone']], 'answers' => ['a1' => 'anything', 'a2' => 'anyone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                ],
                'future' => [
                    ['question' => 'Soon {a1} will publish supplementary notes, and {a2} will annotate them.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'everyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Will {a1} provide {a2} further evidence if reviewers demand it?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['any', 'some', 'no']], 'answers' => ['a1' => 'anyone', 'a2' => 'any'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'We will not allow {a1} to ignore methodological flaws; {a2} must address them.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not (we)', 'a2' => null]],
                    ['question' => 'If {a1} skips the colloquium, {a2} will synthesize the findings.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'If {a1} bypasses peer review, {a2} will flag it immediately.', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['someone', 'everyone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Soon {a1} will circulate {a2} addenda, assuming reviewers ask.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['something', 'anything', 'nothing']], 'answers' => ['a1' => 'someone', 'a2' => 'something'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
            ],
                'question' => [
                    ['question' => 'Did {a1} leave {a2} for us?', 'options' => ['a1' => ['anyone', 'someone', 'no one'], 'a2' => ['something', 'anything', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Is {a1} waiting outside, or did they all go?', 'options' => ['a1' => ['anyone', 'someone', 'everyone']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'Has {a1} seen my notebook, or is {a2} sure it is lost?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'Could {a1} explain this rule to me?', 'options' => ['a1' => ['someone', 'anyone', 'everyone']], 'answers' => ['a1' => 'someone'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'Is there {a1} we should buy before the trip?', 'options' => ['a1' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anything'], 'verb_hints' => ['a1' => null]],
                    ['question' => 'Were {a1} upset about the delay, or was {a2} calm?', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['someone', 'no one', 'anyone']], 'answers' => ['a1' => 'everyone', 'a2' => 'someone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
                'past' => [
                    ['question' => 'Last weekend {a1} unusual happened, and {a2} talked about it.', 'options' => ['a1' => ['something', 'nothing', 'anything'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'something', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => '{a1} called while we were out, but {a2} left a note.', 'options' => ['a1' => ['someone', 'anyone', 'no one'], 'a2' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'someone', 'a2' => 'no one'], 'verb_hints' => ['a1' => null, 'a2' => 'not']],
                    ['question' => 'There was {a1} left after the party; {a2} finished the snacks.', 'options' => ['a1' => ['nothing', 'anything', 'something'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'nothing', 'a2' => 'everyone'], 'verb_hints' => ['a1' => 'not', 'a2' => null]],
                    ['question' => 'We met {a1} new on the trip and exchanged {a2} special.', 'options' => ['a1' => ['someone', 'anyone', 'no one'], 'a2' => ['something', 'anything', 'everything']], 'answers' => ['a1' => 'someone', 'a2' => 'something'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
                'present' => [
                    ['question' => 'At lunch {a1} always tells {a2} funny.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['something', 'anything', 'nothing']], 'answers' => ['a1' => 'someone', 'a2' => 'something'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'There is {a1} in the inbox for you, but {a2} urgent.', 'options' => ['a1' => ['something', 'anything', 'nothing'], 'a2' => ['nothing', 'something', 'everything']], 'answers' => ['a1' => 'something', 'a2' => 'nothing'], 'verb_hints' => ['a1' => null, 'a2' => 'not']],
                    ['question' => '{a1} expects honest answers, so {a2} breaks promises.', 'options' => ['a1' => ['everyone', 'someone', 'no one'], 'a2' => ['no one', 'someone', 'everyone']], 'answers' => ['a1' => 'everyone', 'a2' => 'no one'], 'verb_hints' => ['a1' => null, 'a2' => 'not']],
                    ['question' => 'She never travels with {a1}; she prefers solo trips.', 'options' => ['a1' => ['anyone', 'someone', 'everyone']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => 'not (she)']],
                ],
                'future' => [
                    ['question' => 'Tomorrow {a1} will check the reports, and {a2} else will rest.', 'options' => ['a1' => ['someone', 'anyone', 'everyone'], 'a2' => ['everyone', 'someone', 'no one']], 'answers' => ['a1' => 'someone', 'a2' => 'everyone'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                    ['question' => 'If {a1} calls, tell them we will arrive soon.', 'options' => ['a1' => ['anyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => null]],
                    ['question' => "We won't leave {a1} behind this time.", 'options' => ['a1' => ['anyone', 'someone', 'no one']], 'answers' => ['a1' => 'anyone'], 'verb_hints' => ['a1' => 'not (we)']],
                    ['question' => 'Will {a1} have {a2} ready by Friday?', 'options' => ['a1' => ['anyone', 'someone', 'everyone'], 'a2' => ['anything', 'something', 'nothing']], 'answers' => ['a1' => 'anyone', 'a2' => 'anything'], 'verb_hints' => ['a1' => null, 'a2' => null]],
                ],
            ],
        ];

        foreach ($levels as $level => $groups) {
            foreach ($groups as $source => $items) {
                foreach ($items as $item) {
                    $entries[] = array_merge($item, [
                        'level' => $level,
                        'source' => $source,
                        'explanations' => $item['explanations'] ?? [],
                    ]);
                }
            }
        }

        return $entries;
    }

    private function applyDefaultSupports(array $entry): array
    {
        $entry['hints'] = $entry['hints'] ?? [];
        $entry['explanations'] = $entry['explanations'] ?? [];

        foreach ($entry['answers'] as $marker => $answer) {
            if (! isset($entry['hints'][$marker])) {
                $entry['hints'][$marker] = $this->defaultHintForAnswer((string) $answer, $entry['source'] ?? 'question');
            }

            if (! isset($entry['explanations'][$marker])) {
                $entry['explanations'][$marker] = $this->buildDefaultExplanations(
                    (string) $answer,
                    $entry['options'][$marker] ?? [],
                    $entry['source'] ?? 'question'
                );
            }
        }

        return $entry;
    }

    private function defaultHintForAnswer(string $answer, string $source): string
    {
        $family = $this->pronounFamily($answer);

        return match ($family) {
            'any' => 'Any/anything/anyone — заперечення та запитання, часто після not або допоміжних дієслів.',
            'some' => 'Some/something/someone — ствердження, пропозиції або ввічливі прохання.',
            'no' => 'No one/nothing/nobody — заперечення без окремого not, показує повну відсутність.',
            'every' => 'Everyone/everything — охоплює всіх або все без винятку.',
            default => $source === 'negative'
                ? 'У запереченнях уникайте дублювання not і вибирайте форму з any або no.'
                : 'Оберіть займенник відповідно до контексту (запитання/ствердження/повна відсутність).',
        };
    }

    private function buildDefaultExplanations(string $answer, array $options, string $source): array
    {
        $family = $this->pronounFamily($answer);
        $messages = [];

        foreach ($options as $option) {
            if ($option === $answer) {
                continue;
            }

            $optionFamily = $this->pronounFamily((string) $option);
            $messages[$option] = match ($optionFamily) {
                'any' => 'Any використовується у запитаннях і запереченнях; тут контекст може вимагати іншої групи.',
                'some' => 'Some більше пасує ствердженням чи пропозиціям, тому не виражає браку/питання достатньо чітко.',
                'no' => 'No-композити вже містять заперечення, тож їх не поєднують з not у тій же частині речення.',
                'every' => 'Every означає «кожен/усе» і не підкреслює вибір чи відсутність, тому не збігається з контекстом.',
                default => $source === 'future'
                    ? 'У майбутньому часі зверніть увагу, чи ви питаєте/заперечуєте, і доберіть відповідний займенник.'
                    : 'Доберіть форму, що відповідає наявності/відсутності та типу речення (ствердження, питання, заперечення).',
            };
        }

        return $messages;
    }

    private function pronounFamily(string $value): string
    {
        $lower = strtolower($value);

        return match (true) {
            str_starts_with($lower, 'any') => 'any',
            str_starts_with($lower, 'some') => 'some',
            str_starts_with($lower, 'no') => 'no',
            str_starts_with($lower, 'every') => 'every',
            default => 'other',
        };
    }

    private function flattenOptions(array $optionSets): array
    {
        $values = [];

        foreach ($optionSets as $options) {
            foreach ($options as $option) {
                $values[] = (string) $option;
            }
        }

        return array_values(array_unique($values));
    }
}