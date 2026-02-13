<?php

namespace Database\Seeders\AI\Claude\PassiveVoice;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class PassiveVoiceAllTensesClaudeSeeder extends QuestionSeeder
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
        $categoryId = Category::firstOrCreate(['name' => 'Passive Voice'])->id;
        $sourceIds = $this->buildSourceIds();
        $baseTagIds = $this->buildBaseTags();
        $tagMap = $this->buildTagMap();
        $questions = $this->questionEntries();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $entry['verb_hints'][$marker] ?? null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);
            $uuid = $this->generateQuestionUuid($entry['level'], $index + 1, $entry['question']);
            $questionTagIds = array_values(array_unique(array_merge(
                $baseTagIds,
                [$tagMap['forms'][$entry['form']]],
                [$tagMap['tenses'][$entry['tense']]],
                [$tagMap['times'][$this->tenseConfig()[$entry['tense']]['time']]],
                [$tagMap['levels'][$entry['level']]]
            )));

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
                'source_id' => $sourceIds[$entry['tense']] ?? reset($sourceIds),
                'flag' => 2,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => $questionTagIds,
                'answers' => $answers,
                'options' => $options,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'hints' => $this->buildQuestionHints($entry['tense'], $entry['form']),
                'answers' => $entry['answers'],
                'option_markers' => $this->buildOptionMarkers($entry['options']),
                'explanations' => $this->buildExplanations($entry['options'], $entry['answers'], $entry['tense'], $entry['form']),
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildBaseTags(): array
    {
        $grammarTagId = Tag::firstOrCreate(
            ['name' => 'Grammar'],
            ['category' => 'English Grammar Area']
        )->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice Tenses'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Passive Construction'],
            ['category' => 'English Grammar Structure']
        )->id;

        $voiceTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice'],
            ['category' => 'English Grammar Focus']
        )->id;

        return [$grammarTagId, $themeTagId, $detailTagId, $structureTagId, $voiceTagId];
    }

    private function buildSourceIds(): array
    {
        return [
            'present_simple' => Source::firstOrCreate(
                ['name' => 'AI generated: Present Simple Passive (Set 1)']
            )->id,
            'present_continuous' => Source::firstOrCreate(
                ['name' => 'AI generated: Present Continuous Passive (Set 1)']
            )->id,
            'present_perfect' => Source::firstOrCreate(
                ['name' => 'AI generated: Present Perfect Passive (Set 1)']
            )->id,
            'past_simple' => Source::firstOrCreate(
                ['name' => 'AI generated: Past Simple Passive (Set 1)']
            )->id,
            'past_continuous' => Source::firstOrCreate(
                ['name' => 'AI generated: Past Continuous Passive (Set 1)']
            )->id,
            'past_perfect' => Source::firstOrCreate(
                ['name' => 'AI generated: Past Perfect Passive (Set 1)']
            )->id,
            'future_simple' => Source::firstOrCreate(
                ['name' => 'AI generated: Future Simple Passive (Set 1)']
            )->id,
            'future_continuous' => Source::firstOrCreate(
                ['name' => 'AI generated: Future Continuous Passive (Set 1)']
            )->id,
            'future_perfect' => Source::firstOrCreate(
                ['name' => 'AI generated: Future Perfect Passive (Set 1)']
            )->id,
        ];
    }

    private function buildTagMap(): array
    {
        $forms = [
            'affirmative' => Tag::firstOrCreate(['name' => 'Affirmative Sentence'], ['category' => 'Sentence Type'])->id,
            'negative' => Tag::firstOrCreate(['name' => 'Negative Sentence'], ['category' => 'Sentence Type'])->id,
            'interrogative' => Tag::firstOrCreate(['name' => 'Interrogative Sentence'], ['category' => 'Sentence Type'])->id,
        ];

        $times = [
            'present' => Tag::firstOrCreate(['name' => 'Present Tense'], ['category' => 'Tense'])->id,
            'past' => Tag::firstOrCreate(['name' => 'Past Tense'], ['category' => 'Tense'])->id,
            'future' => Tag::firstOrCreate(['name' => 'Future Tense'], ['category' => 'Tense'])->id,
        ];

        $tenses = [
            'present_simple' => Tag::firstOrCreate(['name' => 'Present Simple Passive'], ['category' => 'Tense'])->id,
            'present_continuous' => Tag::firstOrCreate(['name' => 'Present Continuous Passive'], ['category' => 'Tense'])->id,
            'present_perfect' => Tag::firstOrCreate(['name' => 'Present Perfect Passive'], ['category' => 'Tense'])->id,
            'past_simple' => Tag::firstOrCreate(['name' => 'Past Simple Passive'], ['category' => 'Tense'])->id,
            'past_continuous' => Tag::firstOrCreate(['name' => 'Past Continuous Passive'], ['category' => 'Tense'])->id,
            'past_perfect' => Tag::firstOrCreate(['name' => 'Past Perfect Passive'], ['category' => 'Tense'])->id,
            'future_simple' => Tag::firstOrCreate(['name' => 'Future Simple Passive'], ['category' => 'Tense'])->id,
            'future_continuous' => Tag::firstOrCreate(['name' => 'Future Continuous Passive'], ['category' => 'Tense'])->id,
            'future_perfect' => Tag::firstOrCreate(['name' => 'Future Perfect Passive'], ['category' => 'Tense'])->id,
        ];

        $levels = [
            'A1' => Tag::firstOrCreate(['name' => 'A1'], ['category' => 'CEFR Level'])->id,
            'A2' => Tag::firstOrCreate(['name' => 'A2'], ['category' => 'CEFR Level'])->id,
            'B1' => Tag::firstOrCreate(['name' => 'B1'], ['category' => 'CEFR Level'])->id,
            'B2' => Tag::firstOrCreate(['name' => 'B2'], ['category' => 'CEFR Level'])->id,
            'C1' => Tag::firstOrCreate(['name' => 'C1'], ['category' => 'CEFR Level'])->id,
            'C2' => Tag::firstOrCreate(['name' => 'C2'], ['category' => 'CEFR Level'])->id,
        ];

        return [
            'forms' => $forms,
            'times' => $times,
            'tenses' => $tenses,
            'levels' => $levels,
        ];
    }

    private function tenseConfig(): array
    {
        return [
            'present_simple' => [
                'label' => 'Present Simple Passive',
                'time' => 'present',
                'rule' => 'Present Simple Passive описує регулярні або загальні дії у пасивному стані.',
                'patterns' => [
                    'affirmative' => 'am/is/are + V3',
                    'negative' => 'am/is/are + not + V3',
                    'interrogative' => 'Am/Is/Are + subject + V3?',
                ],
                'examples' => [
                    'The doors are locked every evening.',
                    'A new menu is prepared each day.',
                ],
            ],
            'present_continuous' => [
                'label' => 'Present Continuous Passive',
                'time' => 'present',
                'rule' => 'Present Continuous Passive підкреслює дію, що триває зараз у пасиві.',
                'patterns' => [
                    'affirmative' => 'am/is/are being + V3',
                    'negative' => 'am/is/are not being + V3',
                    'interrogative' => 'Am/Is/Are + subject + being + V3?',
                ],
                'examples' => [
                    'The stage is being decorated right now.',
                    'The reports are being checked this afternoon.',
                ],
            ],
            'present_perfect' => [
                'label' => 'Present Perfect Passive',
                'time' => 'present',
                'rule' => 'Present Perfect Passive показує завершену дію з результатом у теперішньому.',
                'patterns' => [
                    'affirmative' => 'has/have been + V3',
                    'negative' => 'has/have not been + V3',
                    'interrogative' => 'Has/Have + subject + been + V3?',
                ],
                'examples' => [
                    'The application has been approved already.',
                    'Several rooms have been renovated this year.',
                ],
            ],
            'past_simple' => [
                'label' => 'Past Simple Passive',
                'time' => 'past',
                'rule' => 'Past Simple Passive описує завершену дію в минулому у пасиві.',
                'patterns' => [
                    'affirmative' => 'was/were + V3',
                    'negative' => 'was/were not + V3',
                    'interrogative' => 'Was/Were + subject + V3?',
                ],
                'examples' => [
                    'The museum was opened in 1998.',
                    'The files were archived last month.',
                ],
            ],
            'past_continuous' => [
                'label' => 'Past Continuous Passive',
                'time' => 'past',
                'rule' => 'Past Continuous Passive показує дію, що тривала у певний момент у минулому.',
                'patterns' => [
                    'affirmative' => 'was/were being + V3',
                    'negative' => 'was/were not being + V3',
                    'interrogative' => 'Was/Were + subject + being + V3?',
                ],
                'examples' => [
                    'The bridge was being repaired at dawn.',
                    'The patients were being moved during the drill.',
                ],
            ],
            'past_perfect' => [
                'label' => 'Past Perfect Passive',
                'time' => 'past',
                'rule' => 'Past Perfect Passive позначає дію, завершену до іншої події в минулому.',
                'patterns' => [
                    'affirmative' => 'had been + V3',
                    'negative' => 'had not been + V3',
                    'interrogative' => 'Had + subject + been + V3?',
                ],
                'examples' => [
                    'The tickets had been sold out before noon.',
                    'The equipment had been inspected earlier.',
                ],
            ],
            'future_simple' => [
                'label' => 'Future Simple Passive',
                'time' => 'future',
                'rule' => 'Future Simple Passive виражає дію, яка буде виконана у майбутньому.',
                'patterns' => [
                    'affirmative' => 'will be + V3',
                    'negative' => 'will not be + V3',
                    'interrogative' => 'Will + subject + be + V3?',
                ],
                'examples' => [
                    'The schedule will be updated on Friday.',
                    'The badges will be printed tomorrow.',
                ],
            ],
            'future_continuous' => [
                'label' => 'Future Continuous Passive',
                'time' => 'future',
                'rule' => 'Future Continuous Passive показує дію, що буде тривати в певний момент у майбутньому.',
                'patterns' => [
                    'affirmative' => 'will be being + V3',
                    'negative' => 'will not be being + V3',
                    'interrogative' => 'Will + subject + be being + V3?',
                ],
                'examples' => [
                    'The hall will be being prepared at 3 p.m.',
                    'The system will be being tested during the update.',
                ],
            ],
            'future_perfect' => [
                'label' => 'Future Perfect Passive',
                'time' => 'future',
                'rule' => 'Future Perfect Passive підкреслює дію, що буде завершена до певного моменту в майбутньому.',
                'patterns' => [
                    'affirmative' => 'will have been + V3',
                    'negative' => 'will not have been + V3',
                    'interrogative' => 'Will + subject + have been + V3?',
                ],
                'examples' => [
                    'The project will have been completed by July.',
                    'The papers will have been submitted before the deadline.',
                ],
            ],
        ];
    }

    private function buildQuestionHints(string $tenseKey, string $form): array
    {
        $config = $this->tenseConfig()[$tenseKey];
        $pattern = $config['patterns'][$form];

        $formHints = [
            'affirmative' => 'Ствердження: обирайте правильну форму be та V3.',
            'negative' => 'Заперечення: not ставимо після допоміжного be.',
            'interrogative' => 'Питання: допоміжне be/Will/Has ставимо на початок.',
        ];

        return [
            "Формула {$config['label']}: {$pattern}.",
            $formHints[$form] ?? 'Звертайте увагу на форму пасиву.',
        ];
    }

    private function buildOptionMarkers(array $options): array
    {
        $map = [];
        foreach ($options as $marker => $values) {
            foreach ($values as $value) {
                $map[$value] = $marker;
            }
        }

        return $map;
    }

    private function flattenOptions(array $options): array
    {
        $flat = [];
        foreach ($options as $values) {
            foreach ($values as $value) {
                if (! in_array($value, $flat, true)) {
                    $flat[] = $value;
                }
            }
        }

        return $flat;
    }

    private function buildExplanations(array $options, array $answers, string $tenseKey, string $form): array
    {
        $config = $this->tenseConfig()[$tenseKey];
        $pattern = $config['patterns'][$form];
        $examples = implode(' ', $config['examples']);
        $formNotes = [
            'affirmative' => 'У ствердженні використовується правильна форма be та дієприкметник минулого часу.',
            'negative' => 'У запереченні частка not стоїть після допоміжного be або will.',
            'interrogative' => 'У питанні допоміжне дієслово стоїть перед підметом.',
        ];

        $explanations = [];
        foreach ($options as $marker => $values) {
            $correctAnswer = $answers[$marker] ?? '';
            foreach ($values as $value) {
                $prefix = $value === $correctAnswer ? '✅ ' : '❌ ';
                $explanations[$value] = $prefix.$config['rule'].' Формула: '.$pattern.'. '.$formNotes[$form].' Приклади: '.$examples;
            }
        }

        return $explanations;
    }

    private function questionEntries(): array
    {
        return [
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'The shop {a1} at 9 a.m. every morning.',
                'answers' => ['a1' => 'is opened'],
                'options' => ['a1' => ['is opened', 'was opened', 'opens']],
                'verb_hints' => ['a1' => 'open'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The car {a1} in the garage.',
                'answers' => ['a1' => 'is not parked'],
                'options' => ['a1' => ['is not parked', 'was not parked', 'does not park']],
                'verb_hints' => ['a1' => 'park'],
                'tense' => 'present_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'A1',
                'question' => 'Is the house {a1} every week?',
                'answers' => ['a1' => 'cleaned'],
                'options' => ['a1' => ['cleaned', 'cleaning', 'clean']],
                'verb_hints' => ['a1' => 'clean'],
                'tense' => 'present_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A1',
                'question' => 'The letter {a1} yesterday.',
                'answers' => ['a1' => 'was sent'],
                'options' => ['a1' => ['was sent', 'is sent', 'sent']],
                'verb_hints' => ['a1' => 'send'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The flowers {a1} last night.',
                'answers' => ['a1' => 'were not watered'],
                'options' => ['a1' => ['were not watered', 'are not watered', 'did not water']],
                'verb_hints' => ['a1' => 'water'],
                'tense' => 'past_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'A1',
                'question' => 'Was the window {a1} by accident?',
                'answers' => ['a1' => 'broken'],
                'options' => ['a1' => ['broken', 'breaking', 'break']],
                'verb_hints' => ['a1' => 'break'],
                'tense' => 'past_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A1',
                'question' => 'The door {a1} now.',
                'answers' => ['a1' => 'is being painted'],
                'options' => ['a1' => ['is being painted', 'is painted', 'was being painted']],
                'verb_hints' => ['a1' => 'paint'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'Is the food {a1} at the moment?',
                'answers' => ['a1' => 'being cooked'],
                'options' => ['a1' => ['being cooked', 'cooked', 'cooking']],
                'verb_hints' => ['a1' => 'cook'],
                'tense' => 'present_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A1',
                'question' => 'The package {a1} next week.',
                'answers' => ['a1' => 'will be delivered'],
                'options' => ['a1' => ['will be delivered', 'is delivered', 'was delivered']],
                'verb_hints' => ['a1' => 'deliver'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The test {a1} tomorrow.',
                'answers' => ['a1' => 'will not be cancelled'],
                'options' => ['a1' => ['will not be cancelled', 'is not cancelled', 'was not cancelled']],
                'verb_hints' => ['a1' => 'cancel'],
                'tense' => 'future_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'A1',
                'question' => 'Will the boxes {a1} this afternoon?',
                'answers' => ['a1' => 'be moved'],
                'options' => ['a1' => ['be moved', 'move', 'be moving']],
                'verb_hints' => ['a1' => 'move'],
                'tense' => 'future_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A1',
                'question' => 'The books {a1} on the shelf.',
                'answers' => ['a1' => 'are kept'],
                'options' => ['a1' => ['are kept', 'were kept', 'keep']],
                'verb_hints' => ['a1' => 'keep'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'The tables {a1} at this moment.',
                'answers' => ['a1' => 'are not being cleaned'],
                'options' => ['a1' => ['are not being cleaned', 'are not cleaned', 'were not being cleaned']],
                'verb_hints' => ['a1' => 'clean'],
                'tense' => 'present_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'A2',
                'question' => 'The software {a1} recently.',
                'answers' => ['a1' => 'has been updated'],
                'options' => ['a1' => ['has been updated', 'was updated', 'is updated']],
                'verb_hints' => ['a1' => 'update'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'Have the documents {a1} yet?',
                'answers' => ['a1' => 'been signed'],
                'options' => ['a1' => ['been signed', 'signed', 'being signed']],
                'verb_hints' => ['a1' => 'sign'],
                'tense' => 'present_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The walls {a1} when I arrived.',
                'answers' => ['a1' => 'were being painted'],
                'options' => ['a1' => ['were being painted', 'were painted', 'are being painted']],
                'verb_hints' => ['a1' => 'paint'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'Were the guests {a1} during the inspection?',
                'answers' => ['a1' => 'being interviewed'],
                'options' => ['a1' => ['being interviewed', 'interviewed', 'interviewing']],
                'verb_hints' => ['a1' => 'interview'],
                'tense' => 'past_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The room {a1} before the party started.',
                'answers' => ['a1' => 'had been decorated'],
                'options' => ['a1' => ['had been decorated', 'was decorated', 'has been decorated']],
                'verb_hints' => ['a1' => 'decorate'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'Had the car {a1} before the accident?',
                'answers' => ['a1' => 'been checked'],
                'options' => ['a1' => ['been checked', 'checked', 'being checked']],
                'verb_hints' => ['a1' => 'check'],
                'tense' => 'past_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The invitations {a1} by next Friday.',
                'answers' => ['a1' => 'will have been sent'],
                'options' => ['a1' => ['will have been sent', 'will be sent', 'have been sent']],
                'verb_hints' => ['a1' => 'send'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'Will the project {a1} by June?',
                'answers' => ['a1' => 'have been finished'],
                'options' => ['a1' => ['have been finished', 'be finished', 'been finished']],
                'verb_hints' => ['a1' => 'finish'],
                'tense' => 'future_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The new system {a1} at 2 p.m. tomorrow.',
                'answers' => ['a1' => 'will be being installed'],
                'options' => ['a1' => ['will be being installed', 'will be installed', 'is being installed']],
                'verb_hints' => ['a1' => 'install'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The emails {a1} since morning.',
                'answers' => ['a1' => 'have not been answered'],
                'options' => ['a1' => ['have not been answered', 'were not answered', 'are not answered']],
                'verb_hints' => ['a1' => 'answer'],
                'tense' => 'present_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'A2',
                'question' => 'The report {a1} before lunch.',
                'answers' => ['a1' => 'had not been finished'],
                'options' => ['a1' => ['had not been finished', 'was not finished', 'has not been finished']],
                'verb_hints' => ['a1' => 'finish'],
                'tense' => 'past_perfect',
                'form' => 'negative',
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The presentation {a1} during the meeting.',
                'answers' => ['a1' => 'was not being shown'],
                'options' => ['a1' => ['was not being shown', 'was not shown', 'is not being shown']],
                'verb_hints' => ['a1' => 'show'],
                'tense' => 'past_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'The merchandise {a1} in the warehouse regularly.',
                'answers' => ['a1' => 'is inspected'],
                'options' => ['a1' => ['is inspected', 'inspects', 'was inspected']],
                'verb_hints' => ['a1' => 'inspect'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'Are the applications {a1} on weekends?',
                'answers' => ['a1' => 'processed'],
                'options' => ['a1' => ['processed', 'processing', 'process']],
                'verb_hints' => ['a1' => 'process'],
                'tense' => 'present_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B1',
                'question' => 'The certificates {a1} by the board.',
                'answers' => ['a1' => 'have been issued'],
                'options' => ['a1' => ['have been issued', 'were issued', 'are issued']],
                'verb_hints' => ['a1' => 'issue'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The proposals {a1} in detail yet.',
                'answers' => ['a1' => 'have not been reviewed'],
                'options' => ['a1' => ['have not been reviewed', 'were not reviewed', 'are not reviewed']],
                'verb_hints' => ['a1' => 'review'],
                'tense' => 'present_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'The contract {a1} before the deadline.',
                'answers' => ['a1' => 'had been approved'],
                'options' => ['a1' => ['had been approved', 'was approved', 'has been approved']],
                'verb_hints' => ['a1' => 'approve'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The vehicles {a1} before they left.',
                'answers' => ['a1' => 'had not been serviced'],
                'options' => ['a1' => ['had not been serviced', 'were not serviced', 'have not been serviced']],
                'verb_hints' => ['a1' => 'service'],
                'tense' => 'past_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'The equipment {a1} by 5 p.m. next Monday.',
                'answers' => ['a1' => 'will have been delivered'],
                'options' => ['a1' => ['will have been delivered', 'will be delivered', 'has been delivered']],
                'verb_hints' => ['a1' => 'deliver'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'Will the budget {a1} before the meeting?',
                'answers' => ['a1' => 'have been approved'],
                'options' => ['a1' => ['have been approved', 'be approved', 'been approved']],
                'verb_hints' => ['a1' => 'approve'],
                'tense' => 'future_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B1',
                'question' => 'The data {a1} at midnight.',
                'answers' => ['a1' => 'will be being transferred'],
                'options' => ['a1' => ['will be being transferred', 'will be transferred', 'is being transferred']],
                'verb_hints' => ['a1' => 'transfer'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'Will the files {a1} during the maintenance?',
                'answers' => ['a1' => 'be being backed up'],
                'options' => ['a1' => ['be being backed up', 'be backed up', 'been backed up']],
                'verb_hints' => ['a1' => 'back up'],
                'tense' => 'future_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B1',
                'question' => 'The offices {a1} during construction.',
                'answers' => ['a1' => 'were being renovated'],
                'options' => ['a1' => ['were being renovated', 'were renovated', 'are being renovated']],
                'verb_hints' => ['a1' => 'renovate'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'The regulations {a1} in accordance with international standards.',
                'answers' => ['a1' => 'are enforced'],
                'options' => ['a1' => ['are enforced', 'enforce', 'were enforced']],
                'verb_hints' => ['a1' => 'enforce'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The guidelines {a1} uniformly across departments.',
                'answers' => ['a1' => 'are not applied'],
                'options' => ['a1' => ['are not applied', 'were not applied', 'do not apply']],
                'verb_hints' => ['a1' => 'apply'],
                'tense' => 'present_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'B2',
                'question' => 'The infrastructure {a1} currently.',
                'answers' => ['a1' => 'is being upgraded'],
                'options' => ['a1' => ['is being upgraded', 'is upgraded', 'was being upgraded']],
                'verb_hints' => ['a1' => 'upgrade'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'Are the protocols {a1} at this stage?',
                'answers' => ['a1' => 'being revised'],
                'options' => ['a1' => ['being revised', 'revised', 'revising']],
                'verb_hints' => ['a1' => 'revise'],
                'tense' => 'present_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B2',
                'question' => 'The security measures {a1} significantly.',
                'answers' => ['a1' => 'have been enhanced'],
                'options' => ['a1' => ['have been enhanced', 'were enhanced', 'are enhanced']],
                'verb_hints' => ['a1' => 'enhance'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'Have the vulnerabilities {a1} thoroughly?',
                'answers' => ['a1' => 'been assessed'],
                'options' => ['a1' => ['been assessed', 'assessed', 'being assessed']],
                'verb_hints' => ['a1' => 'assess'],
                'tense' => 'present_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B2',
                'question' => 'The archives {a1} when the power failed.',
                'answers' => ['a1' => 'were being digitized'],
                'options' => ['a1' => ['were being digitized', 'were digitized', 'are being digitized']],
                'verb_hints' => ['a1' => 'digitize'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The credentials {a1} before the investigation began.',
                'answers' => ['a1' => 'had been verified'],
                'options' => ['a1' => ['had been verified', 'were verified', 'have been verified']],
                'verb_hints' => ['a1' => 'verify'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'Had the parameters {a1} correctly beforehand?',
                'answers' => ['a1' => 'been configured'],
                'options' => ['a1' => ['been configured', 'configured', 'being configured']],
                'verb_hints' => ['a1' => 'configure'],
                'tense' => 'past_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B2',
                'question' => 'The infrastructure {a1} by the end of this year.',
                'answers' => ['a1' => 'will have been modernized'],
                'options' => ['a1' => ['will have been modernized', 'will be modernized', 'has been modernized']],
                'verb_hints' => ['a1' => 'modernize'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The assets {a1} at 10 a.m. sharp tomorrow.',
                'answers' => ['a1' => 'will be being audited'],
                'options' => ['a1' => ['will be being audited', 'will be audited', 'are being audited']],
                'verb_hints' => ['a1' => 'audit'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The innovations {a1} next month.',
                'answers' => ['a1' => 'will not be implemented'],
                'options' => ['a1' => ['will not be implemented', 'are not implemented', 'were not implemented']],
                'verb_hints' => ['a1' => 'implement'],
                'tense' => 'future_simple',
                'form' => 'negative',
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The ethical implications {a1} by the committee.',
                'answers' => ['a1' => 'are being scrutinized'],
                'options' => ['a1' => ['are being scrutinized', 'are scrutinized', 'were being scrutinized']],
                'verb_hints' => ['a1' => 'scrutinize'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'The regulatory framework {a1} comprehensively.',
                'answers' => ['a1' => 'has been overhauled'],
                'options' => ['a1' => ['has been overhauled', 'was overhauled', 'is overhauled']],
                'verb_hints' => ['a1' => 'overhaul'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'Have the anomalies {a1} sufficiently?',
                'answers' => ['a1' => 'been investigated'],
                'options' => ['a1' => ['been investigated', 'investigated', 'being investigated']],
                'verb_hints' => ['a1' => 'investigate'],
                'tense' => 'present_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C1',
                'question' => 'The manuscripts {a1} when the curator discovered them.',
                'answers' => ['a1' => 'were being cataloged'],
                'options' => ['a1' => ['were being cataloged', 'were cataloged', 'are being cataloged']],
                'verb_hints' => ['a1' => 'catalog'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'Were the allegations {a1} during the trial?',
                'answers' => ['a1' => 'being substantiated'],
                'options' => ['a1' => ['being substantiated', 'substantiated', 'substantiating']],
                'verb_hints' => ['a1' => 'substantiate'],
                'tense' => 'past_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C1',
                'question' => 'The precedent {a1} prior to the ruling.',
                'answers' => ['a1' => 'had been established'],
                'options' => ['a1' => ['had been established', 'was established', 'has been established']],
                'verb_hints' => ['a1' => 'establish'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'The hypothesis {a1} before the new evidence emerged.',
                'answers' => ['a1' => 'had not been validated'],
                'options' => ['a1' => ['had not been validated', 'was not validated', 'has not been validated']],
                'verb_hints' => ['a1' => 'validate'],
                'tense' => 'past_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'C1',
                'question' => 'The amendments {a1} by December.',
                'answers' => ['a1' => 'will have been ratified'],
                'options' => ['a1' => ['will have been ratified', 'will be ratified', 'have been ratified']],
                'verb_hints' => ['a1' => 'ratify'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'Will the legislation {a1} before the session ends?',
                'answers' => ['a1' => 'have been enacted'],
                'options' => ['a1' => ['have been enacted', 'be enacted', 'been enacted']],
                'verb_hints' => ['a1' => 'enact'],
                'tense' => 'future_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C1',
                'question' => 'The specimens {a1} at 3 p.m. during the examination.',
                'answers' => ['a1' => 'will be being analyzed'],
                'options' => ['a1' => ['will be being analyzed', 'will be analyzed', 'are being analyzed']],
                'verb_hints' => ['a1' => 'analyze'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'The parameters {a1} periodically.',
                'answers' => ['a1' => 'are calibrated'],
                'options' => ['a1' => ['are calibrated', 'calibrate', 'were calibrated']],
                'verb_hints' => ['a1' => 'calibrate'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'Are the methodologies {a1} rigorously?',
                'answers' => ['a1' => 'validated'],
                'options' => ['a1' => ['validated', 'validating', 'validate']],
                'verb_hints' => ['a1' => 'validate'],
                'tense' => 'present_simple',
                'form' => 'interrogative',
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'The discourse {a1} by contemporary scholars.',
                'answers' => ['a1' => 'is being recontextualized'],
                'options' => ['a1' => ['is being recontextualized', 'is recontextualized', 'was being recontextualized']],
                'verb_hints' => ['a1' => 'recontextualize'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C2',
                'question' => 'The paradigm {a1} fundamentally.',
                'answers' => ['a1' => 'has been reconceptualized'],
                'options' => ['a1' => ['has been reconceptualized', 'was reconceptualized', 'is reconceptualized']],
                'verb_hints' => ['a1' => 'reconceptualize'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C2',
                'question' => 'Have the epistemological foundations {a1} adequately?',
                'answers' => ['a1' => 'been interrogated'],
                'options' => ['a1' => ['been interrogated', 'interrogated', 'being interrogated']],
                'verb_hints' => ['a1' => 'interrogate'],
                'tense' => 'present_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C2',
                'question' => 'The theoretical constructs {a1} during the symposium.',
                'answers' => ['a1' => 'were being deconstructed'],
                'options' => ['a1' => ['were being deconstructed', 'were deconstructed', 'are being deconstructed']],
                'verb_hints' => ['a1' => 'deconstruct'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C2',
                'question' => 'Were the axioms {a1} throughout the deliberation?',
                'answers' => ['a1' => 'being challenged'],
                'options' => ['a1' => ['being challenged', 'challenged', 'challenging']],
                'verb_hints' => ['a1' => 'challenge'],
                'tense' => 'past_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C2',
                'question' => 'The narrative {a1} prior to the publication.',
                'answers' => ['a1' => 'had been corroborated'],
                'options' => ['a1' => ['had been corroborated', 'was corroborated', 'has been corroborated']],
                'verb_hints' => ['a1' => 'corroborate'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C2',
                'question' => 'Had the contentions {a1} before the debate?',
                'answers' => ['a1' => 'been substantiated'],
                'options' => ['a1' => ['been substantiated', 'substantiated', 'being substantiated']],
                'verb_hints' => ['a1' => 'substantiate'],
                'tense' => 'past_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C2',
                'question' => 'The doctrine {a1} by next spring.',
                'answers' => ['a1' => 'will have been promulgated'],
                'options' => ['a1' => ['will have been promulgated', 'will be promulgated', 'has been promulgated']],
                'verb_hints' => ['a1' => 'promulgate'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C2',
                'question' => 'Will the postulates {a1} by the conference conclusion?',
                'answers' => ['a1' => 'have been articulated'],
                'options' => ['a1' => ['have been articulated', 'be articulated', 'been articulated']],
                'verb_hints' => ['a1' => 'articulate'],
                'tense' => 'future_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C2',
                'question' => 'The manuscripts {a1} at noon tomorrow.',
                'answers' => ['a1' => 'will be being authenticated'],
                'options' => ['a1' => ['will be being authenticated', 'will be authenticated', 'are being authenticated']],
                'verb_hints' => ['a1' => 'authenticate'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C2',
                'question' => 'The propositions {a1} systematically.',
                'answers' => ['a1' => 'are elucidated'],
                'options' => ['a1' => ['are elucidated', 'elucidate', 'were elucidated']],
                'verb_hints' => ['a1' => 'elucidate'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C2',
                'question' => 'Are the corollaries {a1} with sufficient rigor?',
                'answers' => ['a1' => 'demonstrated'],
                'options' => ['a1' => ['demonstrated', 'demonstrating', 'demonstrate']],
                'verb_hints' => ['a1' => 'demonstrate'],
                'tense' => 'present_simple',
                'form' => 'interrogative',
            ],
        ];
    }
}
