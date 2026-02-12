<?php

namespace Database\Seeders\AI\chatGpt\PassiveVoice;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class PassiveVoiceTensesAiSeeder extends QuestionSeeder
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
        return array_merge([
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'The room {a1} every day.',
                'answers' => ['a1' => 'is cleaned'],
                'options' => ['a1' => ['is cleaned', 'is cleaning', 'was cleaned']],
                'verb_hints' => ['a1' => 'clean'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The windows {a1} at night.',
                'answers' => ['a1' => 'are not opened'],
                'options' => ['a1' => ['are not opened', 'were not opened', 'is not opened']],
                'verb_hints' => ['a1' => 'open'],
                'tense' => 'present_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'A1',
                'question' => 'Are the tickets {a1} online?',
                'answers' => ['a1' => 'sold'],
                'options' => ['a1' => ['sold', 'selling', 'sell']],
                'verb_hints' => ['a1' => 'sell'],
                'tense' => 'present_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A1',
                'question' => 'The cake {a1} yesterday.',
                'answers' => ['a1' => 'was baked'],
                'options' => ['a1' => ['was baked', 'is baked', 'was baking']],
                'verb_hints' => ['a1' => 'bake'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The letters {a1} last week.',
                'answers' => ['a1' => 'were not delivered'],
                'options' => ['a1' => ['were not delivered', 'are not delivered', 'were not delivering']],
                'verb_hints' => ['a1' => 'deliver'],
                'tense' => 'past_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'A1',
                'question' => 'Was the door {a1} by the guard?',
                'answers' => ['a1' => 'locked'],
                'options' => ['a1' => ['locked', 'locking', 'lock']],
                'verb_hints' => ['a1' => 'lock'],
                'tense' => 'past_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A1',
                'question' => 'The bridge {a1} this month.',
                'answers' => ['a1' => 'is being repaired'],
                'options' => ['a1' => ['is being repaired', 'is repaired', 'was being repaired']],
                'verb_hints' => ['a1' => 'repair'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'Is the report {a1} right now?',
                'answers' => ['a1' => 'being written'],
                'options' => ['a1' => ['being written', 'written', 'being write']],
                'verb_hints' => ['a1' => 'write'],
                'tense' => 'present_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A1',
                'question' => 'The results {a1} tomorrow.',
                'answers' => ['a1' => 'will be announced'],
                'options' => ['a1' => ['will be announced', 'are announced', 'will have announced']],
                'verb_hints' => ['a1' => 'announce'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The meeting {a1} next Monday.',
                'answers' => ['a1' => 'will not be held'],
                'options' => ['a1' => ['will not be held', 'is not held', 'will not be holding']],
                'verb_hints' => ['a1' => 'hold'],
                'tense' => 'future_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'A1',
                'question' => 'Will the gifts {a1} by Friday?',
                'answers' => ['a1' => 'be delivered'],
                'options' => ['a1' => ['be delivered', 'deliver', 'be delivering']],
                'verb_hints' => ['a1' => 'deliver'],
                'tense' => 'future_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A1',
                'question' => 'These photos {a1} on the website.',
                'answers' => ['a1' => 'are displayed'],
                'options' => ['a1' => ['are displayed', 'display', 'were displayed']],
                'verb_hints' => ['a1' => 'display'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'The dishes {a1} right now.',
                'answers' => ['a1' => 'are not being washed'],
                'options' => ['a1' => ['are not being washed', 'are not washed', 'were not being washed']],
                'verb_hints' => ['a1' => 'wash'],
                'tense' => 'present_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'A2',
                'question' => 'Are the guests {a1} at the moment?',
                'answers' => ['a1' => 'being served'],
                'options' => ['a1' => ['being served', 'served', 'serving']],
                'verb_hints' => ['a1' => 'serve'],
                'tense' => 'present_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The documents {a1} already.',
                'answers' => ['a1' => 'have been signed'],
                'options' => ['a1' => ['have been signed', 'are being signed', 'had been signed']],
                'verb_hints' => ['a1' => 'sign'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The movie {a1} yet.',
                'answers' => ['a1' => 'has not been released'],
                'options' => ['a1' => ['has not been released', 'is not released', 'was not released']],
                'verb_hints' => ['a1' => 'release'],
                'tense' => 'present_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'A2',
                'question' => 'Has the package {a1}?',
                'answers' => ['a1' => 'been shipped'],
                'options' => ['a1' => ['been shipped', 'being shipped', 'shipped']],
                'verb_hints' => ['a1' => 'ship'],
                'tense' => 'present_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The road {a1} when the storm started.',
                'answers' => ['a1' => 'was being cleared'],
                'options' => ['a1' => ['was being cleared', 'was cleared', 'had been cleared']],
                'verb_hints' => ['a1' => 'clear'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The computers {a1} while we were in class.',
                'answers' => ['a1' => 'were not being used'],
                'options' => ['a1' => ['were not being used', 'were not used', 'are not being used']],
                'verb_hints' => ['a1' => 'use'],
                'tense' => 'past_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'A2',
                'question' => 'Was the patient {a1} during the night?',
                'answers' => ['a1' => 'being monitored'],
                'options' => ['a1' => ['being monitored', 'monitored', 'being monitor']],
                'verb_hints' => ['a1' => 'monitor'],
                'tense' => 'past_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The invitations {a1} next week.',
                'answers' => ['a1' => 'will be sent'],
                'options' => ['a1' => ['will be sent', 'are sent', 'will have been sent']],
                'verb_hints' => ['a1' => 'send'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The park {a1} this weekend.',
                'answers' => ['a1' => 'will not be opened'],
                'options' => ['a1' => ['will not be opened', 'is not opened', 'will not be opening']],
                'verb_hints' => ['a1' => 'open'],
                'tense' => 'future_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'A2',
                'question' => 'Is lunch {a1} at noon?',
                'answers' => ['a1' => 'served'],
                'options' => ['a1' => ['served', 'serving', 'serve']],
                'verb_hints' => ['a1' => 'serve'],
                'tense' => 'present_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The old bridge {a1} in 1995.',
                'answers' => ['a1' => 'was built'],
                'options' => ['a1' => ['was built', 'is built', 'was being built']],
                'verb_hints' => ['a1' => 'build'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The final decision {a1} by the committee.',
                'answers' => ['a1' => 'has been made'],
                'options' => ['a1' => ['has been made', 'is being made', 'had been made']],
                'verb_hints' => ['a1' => 'make'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The reports {a1} yet.',
                'answers' => ['a1' => 'have not been checked'],
                'options' => ['a1' => ['have not been checked', 'are not being checked', 'were not checked']],
                'verb_hints' => ['a1' => 'check'],
                'tense' => 'present_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'Have the seats {a1} for the guests?',
                'answers' => ['a1' => 'been reserved'],
                'options' => ['a1' => ['been reserved', 'being reserved', 'reserved']],
                'verb_hints' => ['a1' => 'reserve'],
                'tense' => 'present_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B1',
                'question' => 'By noon, the stage {a1}.',
                'answers' => ['a1' => 'had been prepared'],
                'options' => ['a1' => ['had been prepared', 'was prepared', 'has been prepared']],
                'verb_hints' => ['a1' => 'prepare'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The files {a1} before the audit began.',
                'answers' => ['a1' => 'had not been updated'],
                'options' => ['a1' => ['had not been updated', 'were not updated', 'have not been updated']],
                'verb_hints' => ['a1' => 'update'],
                'tense' => 'past_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'Had the contract {a1} before he left?',
                'answers' => ['a1' => 'been signed'],
                'options' => ['a1' => ['been signed', 'being signed', 'signed']],
                'verb_hints' => ['a1' => 'sign'],
                'tense' => 'past_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B1',
                'question' => 'The stadium {a1} next month.',
                'answers' => ['a1' => 'will be being renovated'],
                'options' => ['a1' => ['will be being renovated', 'will be renovated', 'is being renovated']],
                'verb_hints' => ['a1' => 'renovate'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The website {a1} during the event.',
                'answers' => ['a1' => 'will not be being updated'],
                'options' => ['a1' => ['will not be being updated', 'will not be updated', 'is not being updated']],
                'verb_hints' => ['a1' => 'update'],
                'tense' => 'future_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'Will the documents {a1} while we wait?',
                'answers' => ['a1' => 'be being reviewed'],
                'options' => ['a1' => ['be being reviewed', 'be reviewed', 'being reviewed']],
                'verb_hints' => ['a1' => 'review'],
                'tense' => 'future_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B1',
                'question' => 'By 5 p.m., the orders {a1}.',
                'answers' => ['a1' => 'will have been processed'],
                'options' => ['a1' => ['will have been processed', 'will be processed', 'have been processed']],
                'verb_hints' => ['a1' => 'process'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'By next week, the errors {a1}.',
                'answers' => ['a1' => 'will not have been fixed'],
                'options' => ['a1' => ['will not have been fixed', 'will not be fixed', 'have not been fixed']],
                'verb_hints' => ['a1' => 'fix'],
                'tense' => 'future_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'Will the artwork {a1} by the time they arrive?',
                'answers' => ['a1' => 'have been restored'],
                'options' => ['a1' => ['have been restored', 'be restored', 'have restored']],
                'verb_hints' => ['a1' => 'restore'],
                'tense' => 'future_perfect',
                'form' => 'interrogative',
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'Access to the lab {a1} only to staff.',
                'answers' => ['a1' => 'is granted'],
                'options' => ['a1' => ['is granted', 'is granting', 'was granted']],
                'verb_hints' => ['a1' => 'grant'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The new policy {a1} right now because of legal review.',
                'answers' => ['a1' => 'is not being implemented'],
                'options' => ['a1' => ['is not being implemented', 'is not implemented', 'was not being implemented']],
                'verb_hints' => ['a1' => 'implement'],
                'tense' => 'present_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'B2',
                'question' => 'Has the complaint {a1} by the manager yet?',
                'answers' => ['a1' => 'been handled'],
                'options' => ['a1' => ['been handled', 'being handled', 'handled']],
                'verb_hints' => ['a1' => 'handle'],
                'tense' => 'present_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B2',
                'question' => 'The witnesses {a1} during the trial.',
                'answers' => ['a1' => 'were not questioned'],
                'options' => ['a1' => ['were not questioned', 'are not questioned', 'were not questioning']],
                'verb_hints' => ['a1' => 'question'],
                'tense' => 'past_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'B2',
                'question' => 'Were the exhibits {a1} when the alarm went off?',
                'answers' => ['a1' => 'being displayed'],
                'options' => ['a1' => ['being displayed', 'displayed', 'being display']],
                'verb_hints' => ['a1' => 'display'],
                'tense' => 'past_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B2',
                'question' => 'Before the announcement, the decision {a1} in private.',
                'answers' => ['a1' => 'had been discussed'],
                'options' => ['a1' => ['had been discussed', 'was discussed', 'has been discussed']],
                'verb_hints' => ['a1' => 'discuss'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'All applicants {a1} of the outcome.',
                'answers' => ['a1' => 'will be informed'],
                'options' => ['a1' => ['will be informed', 'are informed', 'will have been informed']],
                'verb_hints' => ['a1' => 'inform'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'Will the contract {a1} this quarter?',
                'answers' => ['a1' => 'be renewed'],
                'options' => ['a1' => ['be renewed', 'renew', 'be renewing']],
                'verb_hints' => ['a1' => 'renew'],
                'tense' => 'future_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B2',
                'question' => 'By the deadline, the report {a1}.',
                'answers' => ['a1' => 'will not have been finalized'],
                'options' => ['a1' => ['will not have been finalized', 'will not be finalized', 'has not been finalized']],
                'verb_hints' => ['a1' => 'finalize'],
                'tense' => 'future_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'B2',
                'question' => 'Several changes {a1} to the schedule.',
                'answers' => ['a1' => 'have been made'],
                'options' => ['a1' => ['have been made', 'are being made', 'had been made']],
                'verb_hints' => ['a1' => 'make'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'Was the equipment {a1} by a specialist?',
                'answers' => ['a1' => 'inspected'],
                'options' => ['a1' => ['inspected', 'inspecting', 'inspect']],
                'verb_hints' => ['a1' => 'inspect'],
                'tense' => 'past_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B2',
                'question' => 'The main hall {a1} during the conference.',
                'answers' => ['a1' => 'will be being cleaned'],
                'options' => ['a1' => ['will be being cleaned', 'will be cleaned', 'is being cleaned']],
                'verb_hints' => ['a1' => 'clean'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The results {a1} since the review started.',
                'answers' => ['a1' => 'have been monitored'],
                'options' => ['a1' => ['have been monitored', 'are being monitored', 'had been monitored']],
                'verb_hints' => ['a1' => 'monitor'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'No clear reason {a1} before the resignation.',
                'answers' => ['a1' => 'had been given'],
                'options' => ['a1' => ['had been given', 'was given', 'has been given']],
                'verb_hints' => ['a1' => 'give'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'Will the archive {a1} by the time the exhibit opens?',
                'answers' => ['a1' => 'have been digitized'],
                'options' => ['a1' => ['have been digitized', 'be digitized', 'have digitized']],
                'verb_hints' => ['a1' => 'digitize'],
                'tense' => 'future_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C1',
                'question' => 'Is the proposal {a1} by external advisers this week?',
                'answers' => ['a1' => 'being evaluated'],
                'options' => ['a1' => ['being evaluated', 'evaluated', 'being evaluate']],
                'verb_hints' => ['a1' => 'evaluate'],
                'tense' => 'present_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C1',
                'question' => 'The network {a1} when the outage occurred.',
                'answers' => ['a1' => 'was being upgraded'],
                'options' => ['a1' => ['was being upgraded', 'was upgraded', 'had been upgraded']],
                'verb_hints' => ['a1' => 'upgrade'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'The list of finalists {a1} until the jury decides.',
                'answers' => ['a1' => 'will not be published'],
                'options' => ['a1' => ['will not be published', 'is not published', 'will not be publishing']],
                'verb_hints' => ['a1' => 'publish'],
                'tense' => 'future_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'C1',
                'question' => 'Each specimen {a1} under identical conditions.',
                'answers' => ['a1' => 'is stored'],
                'options' => ['a1' => ['is stored', 'is storing', 'was stored']],
                'verb_hints' => ['a1' => 'store'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'The gallery {a1} during the private preview.',
                'answers' => ['a1' => 'will not be being opened'],
                'options' => ['a1' => ['will not be being opened', 'will not be opened', 'is not being opened']],
                'verb_hints' => ['a1' => 'open'],
                'tense' => 'future_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'C1',
                'question' => 'The guidelines {a1} in any official document.',
                'answers' => ['a1' => 'have not been referenced'],
                'options' => ['a1' => ['have not been referenced', 'are not referenced', 'were not referenced']],
                'verb_hints' => ['a1' => 'reference'],
                'tense' => 'present_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'C1',
                'question' => 'Were the claims {a1} by independent experts?',
                'answers' => ['a1' => 'verified'],
                'options' => ['a1' => ['verified', 'verifying', 'verify']],
                'verb_hints' => ['a1' => 'verify'],
                'tense' => 'past_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C1',
                'question' => 'By next spring, the collection {a1}.',
                'answers' => ['a1' => 'will have been catalogued'],
                'options' => ['a1' => ['will have been catalogued', 'will be catalogued', 'has been catalogued']],
                'verb_hints' => ['a1' => 'catalogue'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C1',
                'question' => 'Had the permits {a1} before construction began?',
                'answers' => ['a1' => 'been issued'],
                'options' => ['a1' => ['been issued', 'being issued', 'issued']],
                'verb_hints' => ['a1' => 'issue'],
                'tense' => 'past_perfect',
                'form' => 'interrogative',
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'A wide range of sources {a1} in the analysis.',
                'answers' => ['a1' => 'has been cited'],
                'options' => ['a1' => ['has been cited', 'is being cited', 'had been cited']],
                'verb_hints' => ['a1' => 'cite'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C2',
                'question' => 'The new satellite data {a1} at the moment due to encryption.',
                'answers' => ['a1' => 'is not being processed'],
                'options' => ['a1' => ['is not being processed', 'is not processed', 'was not being processed']],
                'verb_hints' => ['a1' => 'process'],
                'tense' => 'present_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'C2',
                'question' => 'Several artifacts {a1} during the excavation.',
                'answers' => ['a1' => 'were uncovered'],
                'options' => ['a1' => ['were uncovered', 'are uncovered', 'were uncovering']],
                'verb_hints' => ['a1' => 'uncover'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C2',
                'question' => 'Were the samples {a1} while the contamination was discovered?',
                'answers' => ['a1' => 'being tested'],
                'options' => ['a1' => ['being tested', 'tested', 'being test']],
                'verb_hints' => ['a1' => 'test'],
                'tense' => 'past_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C2',
                'question' => 'The treaty {a1} before the conflict escalated.',
                'answers' => ['a1' => 'had not been ratified'],
                'options' => ['a1' => ['had not been ratified', 'was not ratified', 'has not been ratified']],
                'verb_hints' => ['a1' => 'ratify'],
                'tense' => 'past_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'C2',
                'question' => 'Will the findings {a1} in the next issue?',
                'answers' => ['a1' => 'be published'],
                'options' => ['a1' => ['be published', 'publish', 'be publishing']],
                'verb_hints' => ['a1' => 'publish'],
                'tense' => 'future_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C2',
                'question' => 'The entire platform {a1} throughout the upgrade window.',
                'answers' => ['a1' => 'will be being monitored'],
                'options' => ['a1' => ['will be being monitored', 'will be monitored', 'is being monitored']],
                'verb_hints' => ['a1' => 'monitor'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'C2',
                'question' => 'The servers {a1} during the migration weekend.',
                'answers' => ['a1' => 'will not be being accessed'],
                'options' => ['a1' => ['will not be being accessed', 'will not be accessed', 'are not being accessed']],
                'verb_hints' => ['a1' => 'access'],
                'tense' => 'future_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'C2',
                'question' => 'Will the manuscript {a1} by the editorial board by then?',
                'answers' => ['a1' => 'have been approved'],
                'options' => ['a1' => ['have been approved', 'be approved', 'have approved']],
                'verb_hints' => ['a1' => 'approve'],
                'tense' => 'future_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C2',
                'question' => 'Access codes {a1} without authorization.',
                'answers' => ['a1' => 'are not issued'],
                'options' => ['a1' => ['are not issued', 'were not issued', 'are not issuing']],
                'verb_hints' => ['a1' => 'issue'],
                'tense' => 'present_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'C2',
                'question' => 'Have the procedural errors {a1} in the report?',
                'answers' => ['a1' => 'been documented'],
                'options' => ['a1' => ['been documented', 'being documented', 'documented']],
                'verb_hints' => ['a1' => 'document'],
                'tense' => 'present_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'C2',
                'question' => 'By midnight, all records {a1}.',
                'answers' => ['a1' => 'will have been backed up'],
                'options' => ['a1' => ['will have been backed up', 'will be backed up', 'have been backed up']],
                'verb_hints' => ['a1' => 'back up'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
        ], $this->additionalQuestionEntries());
    }

    private function additionalQuestionEntries(): array
    {
        return [
            // ===== Additional Present Simple Passive (14) =====
            [
                'level' => 'A2',
                'question' => 'The brochures {a1} in the lobby every morning.',
                'answers' => ['a1' => 'are placed'],
                'options' => ['a1' => ['are placed', 'are placing', 'were placed']],
                'verb_hints' => ['a1' => 'place'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'Coffee {a1} in this machine.',
                'answers' => ['a1' => 'is made'],
                'options' => ['a1' => ['is made', 'is making', 'was made']],
                'verb_hints' => ['a1' => 'make'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The uniforms {a1} on Fridays.',
                'answers' => ['a1' => 'are not washed'],
                'options' => ['a1' => ['are not washed', 'were not washed', 'are not washing']],
                'verb_hints' => ['a1' => 'wash'],
                'tense' => 'present_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'The data {a1} to the server every hour.',
                'answers' => ['a1' => 'is uploaded'],
                'options' => ['a1' => ['is uploaded', 'is uploading', 'was uploaded']],
                'verb_hints' => ['a1' => 'upload'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The rules {a1} clearly on the notice board.',
                'answers' => ['a1' => 'are displayed'],
                'options' => ['a1' => ['are displayed', 'were displayed', 'are displaying']],
                'verb_hints' => ['a1' => 'display'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The library books {a1} with new labels.',
                'answers' => ['a1' => 'are marked'],
                'options' => ['a1' => ['are marked', 'are marking', 'were marked']],
                'verb_hints' => ['a1' => 'mark'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The hall {a1} by security every night.',
                'answers' => ['a1' => 'is checked'],
                'options' => ['a1' => ['is checked', 'was checked', 'is checking']],
                'verb_hints' => ['a1' => 'check'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'Is the newsletter {a1} weekly?',
                'answers' => ['a1' => 'sent'],
                'options' => ['a1' => ['sent', 'sending', 'send']],
                'verb_hints' => ['a1' => 'send'],
                'tense' => 'present_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B1',
                'question' => 'The form {a1} by every applicant.',
                'answers' => ['a1' => 'is completed'],
                'options' => ['a1' => ['is completed', 'is completing', 'was completed']],
                'verb_hints' => ['a1' => 'complete'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The walls {a1} in white.',
                'answers' => ['a1' => 'are painted'],
                'options' => ['a1' => ['are painted', 'were painted', 'are painting']],
                'verb_hints' => ['a1' => 'paint'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The museum {a1} on Mondays.',
                'answers' => ['a1' => 'is closed'],
                'options' => ['a1' => ['is closed', 'was closed', 'is closing']],
                'verb_hints' => ['a1' => 'close'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The receipts {a1} in the safe.',
                'answers' => ['a1' => 'are kept'],
                'options' => ['a1' => ['are kept', 'are keeping', 'were kept']],
                'verb_hints' => ['a1' => 'keep'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'Is the menu {a1} in English?',
                'answers' => ['a1' => 'printed'],
                'options' => ['a1' => ['printed', 'printing', 'print']],
                'verb_hints' => ['a1' => 'print'],
                'tense' => 'present_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B1',
                'question' => 'The phones {a1} before they are sold.',
                'answers' => ['a1' => 'are tested'],
                'options' => ['a1' => ['are tested', 'were tested', 'are testing']],
                'verb_hints' => ['a1' => 'test'],
                'tense' => 'present_simple',
                'form' => 'affirmative',
            ],

            // ===== Additional Present Continuous Passive (15) =====
            [
                'level' => 'A2',
                'question' => 'The kitchen {a1} right now.',
                'answers' => ['a1' => 'is being cleaned'],
                'options' => ['a1' => ['is being cleaned', 'is cleaned', 'was being cleaned']],
                'verb_hints' => ['a1' => 'clean'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The chairs {a1} for the ceremony.',
                'answers' => ['a1' => 'are being arranged'],
                'options' => ['a1' => ['are being arranged', 'are arranged', 'were being arranged']],
                'verb_hints' => ['a1' => 'arrange'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The emails {a1} at the moment.',
                'answers' => ['a1' => 'are being answered'],
                'options' => ['a1' => ['are being answered', 'are answered', 'were being answered']],
                'verb_hints' => ['a1' => 'answer'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The bridge {a1} this week.',
                'answers' => ['a1' => 'is being inspected'],
                'options' => ['a1' => ['is being inspected', 'is inspected', 'was being inspected']],
                'verb_hints' => ['a1' => 'inspect'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The orders {a1} now.',
                'answers' => ['a1' => 'are not being processed'],
                'options' => ['a1' => ['are not being processed', 'are not processed', 'were not being processed']],
                'verb_hints' => ['a1' => 'process'],
                'tense' => 'present_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'A2',
                'question' => 'Is the stage {a1} right now?',
                'answers' => ['a1' => 'being set up'],
                'options' => ['a1' => ['being set up', 'set up', 'being set']],
                'verb_hints' => ['a1' => 'set up'],
                'tense' => 'present_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'Are the lights {a1} currently?',
                'answers' => ['a1' => 'being tested'],
                'options' => ['a1' => ['being tested', 'tested', 'being test']],
                'verb_hints' => ['a1' => 'test'],
                'tense' => 'present_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The phones {a1} while customers wait.',
                'answers' => ['a1' => 'are being charged'],
                'options' => ['a1' => ['are being charged', 'are charged', 'were being charged']],
                'verb_hints' => ['a1' => 'charge'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The invoices {a1} today.',
                'answers' => ['a1' => 'are being verified'],
                'options' => ['a1' => ['are being verified', 'are verified', 'were being verified']],
                'verb_hints' => ['a1' => 'verify'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The patient {a1} at this time.',
                'answers' => ['a1' => 'is being examined'],
                'options' => ['a1' => ['is being examined', 'is examined', 'was being examined']],
                'verb_hints' => ['a1' => 'examine'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'Are the packages {a1} right now?',
                'answers' => ['a1' => 'being scanned'],
                'options' => ['a1' => ['being scanned', 'scanned', 'being scan']],
                'verb_hints' => ['a1' => 'scan'],
                'tense' => 'present_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B1',
                'question' => 'The film {a1} at the studio this month.',
                'answers' => ['a1' => 'is being edited'],
                'options' => ['a1' => ['is being edited', 'is edited', 'was being edited']],
                'verb_hints' => ['a1' => 'edit'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The flights {a1} right now.',
                'answers' => ['a1' => 'are being delayed'],
                'options' => ['a1' => ['are being delayed', 'are delayed', 'were being delayed']],
                'verb_hints' => ['a1' => 'delay'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The schedule {a1} at the moment.',
                'answers' => ['a1' => 'is not being updated'],
                'options' => ['a1' => ['is not being updated', 'is not updated', 'was not being updated']],
                'verb_hints' => ['a1' => 'update'],
                'tense' => 'present_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'A2',
                'question' => 'The lecture hall {a1} this afternoon.',
                'answers' => ['a1' => 'is being prepared'],
                'options' => ['a1' => ['is being prepared', 'is prepared', 'was being prepared']],
                'verb_hints' => ['a1' => 'prepare'],
                'tense' => 'present_continuous',
                'form' => 'affirmative',
            ],

            // ===== Additional Present Perfect Passive (10) =====
            [
                'level' => 'A2',
                'question' => 'The payment {a1} already.',
                'answers' => ['a1' => 'has been received'],
                'options' => ['a1' => ['has been received', 'is being received', 'had been received']],
                'verb_hints' => ['a1' => 'receive'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The rooms {a1} recently.',
                'answers' => ['a1' => 'have been cleaned'],
                'options' => ['a1' => ['have been cleaned', 'are being cleaned', 'had been cleaned']],
                'verb_hints' => ['a1' => 'clean'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The project {a1} yet.',
                'answers' => ['a1' => 'has not been finished'],
                'options' => ['a1' => ['has not been finished', 'is not finished', 'was not finished']],
                'verb_hints' => ['a1' => 'finish'],
                'tense' => 'present_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'The keys {a1} to the guard.',
                'answers' => ['a1' => 'have been handed'],
                'options' => ['a1' => ['have been handed', 'are being handed', 'had been handed']],
                'verb_hints' => ['a1' => 'hand'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The results {a1} so far.',
                'answers' => ['a1' => 'have been published'],
                'options' => ['a1' => ['have been published', 'are being published', 'had been published']],
                'verb_hints' => ['a1' => 'publish'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'Has the contract {a1} yet?',
                'answers' => ['a1' => 'been renewed'],
                'options' => ['a1' => ['been renewed', 'being renewed', 'renewed']],
                'verb_hints' => ['a1' => 'renew'],
                'tense' => 'present_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The tickets {a1} for months.',
                'answers' => ['a1' => 'have been sold'],
                'options' => ['a1' => ['have been sold', 'are being sold', 'had been sold']],
                'verb_hints' => ['a1' => 'sell'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The policy {a1} this year.',
                'answers' => ['a1' => 'has been revised'],
                'options' => ['a1' => ['has been revised', 'is being revised', 'had been revised']],
                'verb_hints' => ['a1' => 'revise'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The files {a1} since Monday.',
                'answers' => ['a1' => 'have been uploaded'],
                'options' => ['a1' => ['have been uploaded', 'are being uploaded', 'had been uploaded']],
                'verb_hints' => ['a1' => 'upload'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The complaint {a1} by the manager.',
                'answers' => ['a1' => 'has been resolved'],
                'options' => ['a1' => ['has been resolved', 'is being resolved', 'had been resolved']],
                'verb_hints' => ['a1' => 'resolve'],
                'tense' => 'present_perfect',
                'form' => 'affirmative',
            ],

            // ===== Additional Past Simple Passive (14) =====
            [
                'level' => 'A2',
                'question' => 'The statue {a1} in 2005.',
                'answers' => ['a1' => 'was restored'],
                'options' => ['a1' => ['was restored', 'is restored', 'was restoring']],
                'verb_hints' => ['a1' => 'restore'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The gates {a1} after the storm.',
                'answers' => ['a1' => 'were closed'],
                'options' => ['a1' => ['were closed', 'are closed', 'were closing']],
                'verb_hints' => ['a1' => 'close'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The bill {a1} last night.',
                'answers' => ['a1' => 'was paid'],
                'options' => ['a1' => ['was paid', 'is paid', 'was paying']],
                'verb_hints' => ['a1' => 'pay'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The invitations {a1} last Tuesday.',
                'answers' => ['a1' => 'were sent'],
                'options' => ['a1' => ['were sent', 'are sent', 'were sending']],
                'verb_hints' => ['a1' => 'send'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The game {a1} due to rain.',
                'answers' => ['a1' => 'was canceled'],
                'options' => ['a1' => ['was canceled', 'is canceled', 'was canceling']],
                'verb_hints' => ['a1' => 'cancel'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The carpets {a1} in spring.',
                'answers' => ['a1' => 'were cleaned'],
                'options' => ['a1' => ['were cleaned', 'are cleaned', 'were cleaning']],
                'verb_hints' => ['a1' => 'clean'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The file {a1} by mistake.',
                'answers' => ['a1' => 'was deleted'],
                'options' => ['a1' => ['was deleted', 'is deleted', 'was deleting']],
                'verb_hints' => ['a1' => 'delete'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The fence {a1} by workers.',
                'answers' => ['a1' => 'was repaired'],
                'options' => ['a1' => ['was repaired', 'is repaired', 'was repairing']],
                'verb_hints' => ['a1' => 'repair'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'Were the documents {a1} yesterday?',
                'answers' => ['a1' => 'delivered'],
                'options' => ['a1' => ['delivered', 'delivering', 'deliver']],
                'verb_hints' => ['a1' => 'deliver'],
                'tense' => 'past_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The lights {a1} during the outage.',
                'answers' => ['a1' => 'were turned off'],
                'options' => ['a1' => ['were turned off', 'are turned off', 'were turning off']],
                'verb_hints' => ['a1' => 'turn off'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The shop {a1} on Sunday.',
                'answers' => ['a1' => 'was opened'],
                'options' => ['a1' => ['was opened', 'is opened', 'was opening']],
                'verb_hints' => ['a1' => 'open'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A1',
                'question' => 'The gift {a1} for her birthday.',
                'answers' => ['a1' => 'was wrapped'],
                'options' => ['a1' => ['was wrapped', 'is wrapped', 'was wrapping']],
                'verb_hints' => ['a1' => 'wrap'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The rules {a1} by the coach.',
                'answers' => ['a1' => 'were explained'],
                'options' => ['a1' => ['were explained', 'are explained', 'were explaining']],
                'verb_hints' => ['a1' => 'explain'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The street {a1} last year.',
                'answers' => ['a1' => 'was widened'],
                'options' => ['a1' => ['was widened', 'is widened', 'was widening']],
                'verb_hints' => ['a1' => 'widen'],
                'tense' => 'past_simple',
                'form' => 'affirmative',
            ],

            // ===== Additional Past Continuous Passive (16) =====
            [
                'level' => 'B1',
                'question' => 'The road {a1} when the accident happened.',
                'answers' => ['a1' => 'was being resurfaced'],
                'options' => ['a1' => ['was being resurfaced', 'was resurfaced', 'had been resurfaced']],
                'verb_hints' => ['a1' => 'resurface'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The passengers {a1} while the plane taxied.',
                'answers' => ['a1' => 'were being seated'],
                'options' => ['a1' => ['were being seated', 'were seated', 'had been seated']],
                'verb_hints' => ['a1' => 'seat'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The stage {a1} when we arrived.',
                'answers' => ['a1' => 'was being lit'],
                'options' => ['a1' => ['was being lit', 'was lit', 'had been lit']],
                'verb_hints' => ['a1' => 'light'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The files {a1} at 3 p.m.',
                'answers' => ['a1' => 'were being copied'],
                'options' => ['a1' => ['were being copied', 'were copied', 'had been copied']],
                'verb_hints' => ['a1' => 'copy'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The engine {a1} when the alarm sounded.',
                'answers' => ['a1' => 'was being tested'],
                'options' => ['a1' => ['was being tested', 'was tested', 'had been tested']],
                'verb_hints' => ['a1' => 'test'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The classrooms {a1} during the break.',
                'answers' => ['a1' => 'were being cleaned'],
                'options' => ['a1' => ['were being cleaned', 'were cleaned', 'had been cleaned']],
                'verb_hints' => ['a1' => 'clean'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The report {a1} at that moment.',
                'answers' => ['a1' => 'was not being reviewed'],
                'options' => ['a1' => ['was not being reviewed', 'was not reviewed', 'had not been reviewed']],
                'verb_hints' => ['a1' => 'review'],
                'tense' => 'past_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'A2',
                'question' => 'The trucks {a1} while we waited.',
                'answers' => ['a1' => 'were being loaded'],
                'options' => ['a1' => ['were being loaded', 'were loaded', 'had been loaded']],
                'verb_hints' => ['a1' => 'load'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'Was the software {a1} when the system crashed?',
                'answers' => ['a1' => 'being updated'],
                'options' => ['a1' => ['being updated', 'updated', 'being update']],
                'verb_hints' => ['a1' => 'update'],
                'tense' => 'past_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B1',
                'question' => 'Were the guests {a1} when the fire drill started?',
                'answers' => ['a1' => 'being escorted'],
                'options' => ['a1' => ['being escorted', 'escorted', 'being escort']],
                'verb_hints' => ['a1' => 'escort'],
                'tense' => 'past_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The bridge {a1} overnight.',
                'answers' => ['a1' => 'was being painted'],
                'options' => ['a1' => ['was being painted', 'was painted', 'had been painted']],
                'verb_hints' => ['a1' => 'paint'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The tickets {a1} when the power went out.',
                'answers' => ['a1' => 'were being printed'],
                'options' => ['a1' => ['were being printed', 'were printed', 'had been printed']],
                'verb_hints' => ['a1' => 'print'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The patient {a1} when the nurse called.',
                'answers' => ['a1' => 'was being moved'],
                'options' => ['a1' => ['was being moved', 'was moved', 'had been moved']],
                'verb_hints' => ['a1' => 'move'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The hall {a1} at midnight.',
                'answers' => ['a1' => 'was being decorated'],
                'options' => ['a1' => ['was being decorated', 'was decorated', 'had been decorated']],
                'verb_hints' => ['a1' => 'decorate'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The computers {a1} during the exam.',
                'answers' => ['a1' => 'were not being used'],
                'options' => ['a1' => ['were not being used', 'were not used', 'had not been used']],
                'verb_hints' => ['a1' => 'use'],
                'tense' => 'past_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'The luggage {a1} when we reached the counter.',
                'answers' => ['a1' => 'was being checked'],
                'options' => ['a1' => ['was being checked', 'was checked', 'had been checked']],
                'verb_hints' => ['a1' => 'check'],
                'tense' => 'past_continuous',
                'form' => 'affirmative',
            ],

            // ===== Additional Past Perfect Passive (15) =====
            [
                'level' => 'B1',
                'question' => 'The parcels {a1} before noon.',
                'answers' => ['a1' => 'had been delivered'],
                'options' => ['a1' => ['had been delivered', 'were delivered', 'have been delivered']],
                'verb_hints' => ['a1' => 'deliver'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The report {a1} before the meeting.',
                'answers' => ['a1' => 'had been prepared'],
                'options' => ['a1' => ['had been prepared', 'was prepared', 'has been prepared']],
                'verb_hints' => ['a1' => 'prepare'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The tickets {a1} before we arrived.',
                'answers' => ['a1' => 'had been sold'],
                'options' => ['a1' => ['had been sold', 'were sold', 'have been sold']],
                'verb_hints' => ['a1' => 'sell'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The files {a1} when the audit started.',
                'answers' => ['a1' => 'had been backed up'],
                'options' => ['a1' => ['had been backed up', 'were backed up', 'have been backed up']],
                'verb_hints' => ['a1' => 'back up'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The equipment {a1} before use.',
                'answers' => ['a1' => 'had been calibrated'],
                'options' => ['a1' => ['had been calibrated', 'was calibrated', 'has been calibrated']],
                'verb_hints' => ['a1' => 'calibrate'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The decision {a1} before the announcement.',
                'answers' => ['a1' => 'had been made'],
                'options' => ['a1' => ['had been made', 'was made', 'has been made']],
                'verb_hints' => ['a1' => 'make'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The error {a1} by the time we noticed.',
                'answers' => ['a1' => 'had been corrected'],
                'options' => ['a1' => ['had been corrected', 'was corrected', 'has been corrected']],
                'verb_hints' => ['a1' => 'correct'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The rooms {a1} before the guests checked in.',
                'answers' => ['a1' => 'had been cleaned'],
                'options' => ['a1' => ['had been cleaned', 'were cleaned', 'have been cleaned']],
                'verb_hints' => ['a1' => 'clean'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The invoice {a1} before the deadline.',
                'answers' => ['a1' => 'had been issued'],
                'options' => ['a1' => ['had been issued', 'was issued', 'has been issued']],
                'verb_hints' => ['a1' => 'issue'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'Had the permits {a1} before construction began?',
                'answers' => ['a1' => 'been approved'],
                'options' => ['a1' => ['been approved', 'being approved', 'approved']],
                'verb_hints' => ['a1' => 'approve'],
                'tense' => 'past_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B1',
                'question' => 'The message {a1} before he called.',
                'answers' => ['a1' => 'had not been sent'],
                'options' => ['a1' => ['had not been sent', 'was not sent', 'has not been sent']],
                'verb_hints' => ['a1' => 'send'],
                'tense' => 'past_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'B2',
                'question' => 'The contract {a1} before negotiations ended.',
                'answers' => ['a1' => 'had been signed'],
                'options' => ['a1' => ['had been signed', 'was signed', 'has been signed']],
                'verb_hints' => ['a1' => 'sign'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The files {a1} before the server crashed.',
                'answers' => ['a1' => 'had not been saved'],
                'options' => ['a1' => ['had not been saved', 'were not saved', 'have not been saved']],
                'verb_hints' => ['a1' => 'save'],
                'tense' => 'past_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'B2',
                'question' => 'The agenda {a1} before the session.',
                'answers' => ['a1' => 'had been shared'],
                'options' => ['a1' => ['had been shared', 'was shared', 'has been shared']],
                'verb_hints' => ['a1' => 'share'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The seats {a1} before the show started.',
                'answers' => ['a1' => 'had been reserved'],
                'options' => ['a1' => ['had been reserved', 'were reserved', 'have been reserved']],
                'verb_hints' => ['a1' => 'reserve'],
                'tense' => 'past_perfect',
                'form' => 'affirmative',
            ],

            // ===== Additional Future Simple Passive (13) =====
            [
                'level' => 'A2',
                'question' => 'The new policy {a1} next month.',
                'answers' => ['a1' => 'will be introduced'],
                'options' => ['a1' => ['will be introduced', 'is introduced', 'will have introduced']],
                'verb_hints' => ['a1' => 'introduce'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The invitations {a1} tomorrow.',
                'answers' => ['a1' => 'will be mailed'],
                'options' => ['a1' => ['will be mailed', 'are mailed', 'will have mailed']],
                'verb_hints' => ['a1' => 'mail'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The hotel {a1} next year.',
                'answers' => ['a1' => 'will be renovated'],
                'options' => ['a1' => ['will be renovated', 'is renovated', 'will have renovated']],
                'verb_hints' => ['a1' => 'renovate'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The meeting {a1} next week.',
                'answers' => ['a1' => 'will not be held'],
                'options' => ['a1' => ['will not be held', 'is not held', 'will not be holding']],
                'verb_hints' => ['a1' => 'hold'],
                'tense' => 'future_simple',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'The trucks {a1} at dawn.',
                'answers' => ['a1' => 'will be loaded'],
                'options' => ['a1' => ['will be loaded', 'are loaded', 'will have loaded']],
                'verb_hints' => ['a1' => 'load'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'Will the report {a1} on Friday?',
                'answers' => ['a1' => 'be submitted'],
                'options' => ['a1' => ['be submitted', 'submit', 'be submitting']],
                'verb_hints' => ['a1' => 'submit'],
                'tense' => 'future_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The fence {a1} soon.',
                'answers' => ['a1' => 'will be repaired'],
                'options' => ['a1' => ['will be repaired', 'is repaired', 'will have repaired']],
                'verb_hints' => ['a1' => 'repair'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The tickets {a1} online.',
                'answers' => ['a1' => 'will be sold'],
                'options' => ['a1' => ['will be sold', 'are sold', 'will have sold']],
                'verb_hints' => ['a1' => 'sell'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The list {a1} later today.',
                'answers' => ['a1' => 'will be posted'],
                'options' => ['a1' => ['will be posted', 'is posted', 'will have posted']],
                'verb_hints' => ['a1' => 'post'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'The order {a1} within 24 hours.',
                'answers' => ['a1' => 'will be shipped'],
                'options' => ['a1' => ['will be shipped', 'is shipped', 'will have shipped']],
                'verb_hints' => ['a1' => 'ship'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The equipment {a1} after inspection.',
                'answers' => ['a1' => 'will be returned'],
                'options' => ['a1' => ['will be returned', 'is returned', 'will have returned']],
                'verb_hints' => ['a1' => 'return'],
                'tense' => 'future_simple',
                'form' => 'affirmative',
            ],
            [
                'level' => 'A2',
                'question' => 'Will the results {a1} by email?',
                'answers' => ['a1' => 'be sent'],
                'options' => ['a1' => ['be sent', 'send', 'be sending']],
                'verb_hints' => ['a1' => 'send'],
                'tense' => 'future_simple',
                'form' => 'interrogative',
            ],
            [
                'level' => 'A2',
                'question' => 'The package {a1} in the morning.',
                'answers' => ['a1' => 'will not be delivered'],
                'options' => ['a1' => ['will not be delivered', 'is not delivered', 'will not be delivering']],
                'verb_hints' => ['a1' => 'deliver'],
                'tense' => 'future_simple',
                'form' => 'negative',
            ],

            // ===== Additional Future Continuous Passive (15) =====
            [
                'level' => 'B2',
                'question' => 'The auditorium {a1} at 2 p.m.',
                'answers' => ['a1' => 'will be being prepared'],
                'options' => ['a1' => ['will be being prepared', 'will be prepared', 'is being prepared']],
                'verb_hints' => ['a1' => 'prepare'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The reports {a1} during the workshop.',
                'answers' => ['a1' => 'will be being discussed'],
                'options' => ['a1' => ['will be being discussed', 'will be discussed', 'are being discussed']],
                'verb_hints' => ['a1' => 'discuss'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The servers {a1} throughout the night.',
                'answers' => ['a1' => 'will be being monitored'],
                'options' => ['a1' => ['will be being monitored', 'will be monitored', 'are being monitored']],
                'verb_hints' => ['a1' => 'monitor'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The road {a1} this time tomorrow.',
                'answers' => ['a1' => 'will be being repaired'],
                'options' => ['a1' => ['will be being repaired', 'will be repaired', 'is being repaired']],
                'verb_hints' => ['a1' => 'repair'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The exhibits {a1} while visitors wait.',
                'answers' => ['a1' => 'will be being arranged'],
                'options' => ['a1' => ['will be being arranged', 'will be arranged', 'are being arranged']],
                'verb_hints' => ['a1' => 'arrange'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The forms {a1} during the inspection.',
                'answers' => ['a1' => 'will not be being checked'],
                'options' => ['a1' => ['will not be being checked', 'will not be checked', 'are not being checked']],
                'verb_hints' => ['a1' => 'check'],
                'tense' => 'future_continuous',
                'form' => 'negative',
            ],
            [
                'level' => 'B2',
                'question' => 'Will the system {a1} during the upgrade?',
                'answers' => ['a1' => 'be being tested'],
                'options' => ['a1' => ['be being tested', 'be tested', 'being tested']],
                'verb_hints' => ['a1' => 'test'],
                'tense' => 'future_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B2',
                'question' => 'The cameras {a1} during the event.',
                'answers' => ['a1' => 'will be being used'],
                'options' => ['a1' => ['will be being used', 'will be used', 'are being used']],
                'verb_hints' => ['a1' => 'use'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The new menu {a1} this evening.',
                'answers' => ['a1' => 'will be being printed'],
                'options' => ['a1' => ['will be being printed', 'will be printed', 'is being printed']],
                'verb_hints' => ['a1' => 'print'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The trucks {a1} at midnight.',
                'answers' => ['a1' => 'will be being unloaded'],
                'options' => ['a1' => ['will be being unloaded', 'will be unloaded', 'are being unloaded']],
                'verb_hints' => ['a1' => 'unload'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The lecture {a1} at 10 a.m.',
                'answers' => ['a1' => 'will be being recorded'],
                'options' => ['a1' => ['will be being recorded', 'will be recorded', 'is being recorded']],
                'verb_hints' => ['a1' => 'record'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The guests {a1} while we speak.',
                'answers' => ['a1' => 'will be being seated'],
                'options' => ['a1' => ['will be being seated', 'will be seated', 'are being seated']],
                'verb_hints' => ['a1' => 'seat'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'The flooring {a1} tomorrow afternoon.',
                'answers' => ['a1' => 'will be being installed'],
                'options' => ['a1' => ['will be being installed', 'will be installed', 'is being installed']],
                'verb_hints' => ['a1' => 'install'],
                'tense' => 'future_continuous',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'Will the software {a1} over the weekend?',
                'answers' => ['a1' => 'be being deployed'],
                'options' => ['a1' => ['be being deployed', 'be deployed', 'being deployed']],
                'verb_hints' => ['a1' => 'deploy'],
                'tense' => 'future_continuous',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B2',
                'question' => 'The files {a1} during the migration.',
                'answers' => ['a1' => 'will not be being copied'],
                'options' => ['a1' => ['will not be being copied', 'will not be copied', 'are not being copied']],
                'verb_hints' => ['a1' => 'copy'],
                'tense' => 'future_continuous',
                'form' => 'negative',
            ],

            // ===== Additional Future Perfect Passive (14) =====
            [
                'level' => 'B1',
                'question' => 'By noon, the hall {a1}.',
                'answers' => ['a1' => 'will have been cleaned'],
                'options' => ['a1' => ['will have been cleaned', 'will be cleaned', 'have been cleaned']],
                'verb_hints' => ['a1' => 'clean'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'By Friday, the invoices {a1}.',
                'answers' => ['a1' => 'will have been issued'],
                'options' => ['a1' => ['will have been issued', 'will be issued', 'have been issued']],
                'verb_hints' => ['a1' => 'issue'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'By the end of the day, the orders {a1}.',
                'answers' => ['a1' => 'will have been processed'],
                'options' => ['a1' => ['will have been processed', 'will be processed', 'have been processed']],
                'verb_hints' => ['a1' => 'process'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'By next month, the bridge {a1}.',
                'answers' => ['a1' => 'will have been repaired'],
                'options' => ['a1' => ['will have been repaired', 'will be repaired', 'has been repaired']],
                'verb_hints' => ['a1' => 'repair'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The report {a1} by the time you arrive.',
                'answers' => ['a1' => 'will have been finalized'],
                'options' => ['a1' => ['will have been finalized', 'will be finalized', 'has been finalized']],
                'verb_hints' => ['a1' => 'finalize'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'Will the contract {a1} by then?',
                'answers' => ['a1' => 'have been signed'],
                'options' => ['a1' => ['have been signed', 'be signed', 'have signed']],
                'verb_hints' => ['a1' => 'sign'],
                'tense' => 'future_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B2',
                'question' => 'By the deadline, the samples {a1}.',
                'answers' => ['a1' => 'will not have been tested'],
                'options' => ['a1' => ['will not have been tested', 'will not be tested', 'have not been tested']],
                'verb_hints' => ['a1' => 'test'],
                'tense' => 'future_perfect',
                'form' => 'negative',
            ],
            [
                'level' => 'B1',
                'question' => 'By next week, the staff {a1}.',
                'answers' => ['a1' => 'will have been trained'],
                'options' => ['a1' => ['will have been trained', 'will be trained', 'have been trained']],
                'verb_hints' => ['a1' => 'train'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The passports {a1} before the flight.',
                'answers' => ['a1' => 'will have been checked'],
                'options' => ['a1' => ['will have been checked', 'will be checked', 'have been checked']],
                'verb_hints' => ['a1' => 'check'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'Will the system {a1} by 6 p.m.?',
                'answers' => ['a1' => 'have been upgraded'],
                'options' => ['a1' => ['have been upgraded', 'be upgraded', 'have upgraded']],
                'verb_hints' => ['a1' => 'upgrade'],
                'tense' => 'future_perfect',
                'form' => 'interrogative',
            ],
            [
                'level' => 'B2',
                'question' => 'By next year, the archives {a1}.',
                'answers' => ['a1' => 'will have been digitized'],
                'options' => ['a1' => ['will have been digitized', 'will be digitized', 'have been digitized']],
                'verb_hints' => ['a1' => 'digitize'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B1',
                'question' => 'The books {a1} by Monday.',
                'answers' => ['a1' => 'will have been returned'],
                'options' => ['a1' => ['will have been returned', 'will be returned', 'have been returned']],
                'verb_hints' => ['a1' => 'return'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'By midnight, the security report {a1}.',
                'answers' => ['a1' => 'will have been filed'],
                'options' => ['a1' => ['will have been filed', 'will be filed', 'has been filed']],
                'verb_hints' => ['a1' => 'file'],
                'tense' => 'future_perfect',
                'form' => 'affirmative',
            ],
            [
                'level' => 'B2',
                'question' => 'By the time we leave, the tables {a1}.',
                'answers' => ['a1' => 'will not have been set'],
                'options' => ['a1' => ['will not have been set', 'will not be set', 'have not been set']],
                'verb_hints' => ['a1' => 'set'],
                'tense' => 'future_perfect',
                'form' => 'negative',
            ],
        ];
    }
}
