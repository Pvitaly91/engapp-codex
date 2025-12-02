<?php

namespace Database\Seeders\AI\Claude;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class MixedConditionalsAIGeneratedSeeder extends QuestionSeeder
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
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'AI Claude Mixed Conditionals Set 1'])->id;
        $tagIds = $this->buildTags();
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
            $questionTagIds = array_merge($tagIds, $entry['tag_ids'] ?? []);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
                'source_id' => $sourceId,
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
                'hints' => $entry['hints'] ?? [],
                'answers' => $entry['answers'],
                'option_markers' => $this->buildOptionMarkers($entry['options']),
                'explanations' => $this->buildExplanations($entry['options'], $entry['answers'], $entry['level']),
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildTags(): array
    {
        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditionals'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditional Tense Combinations'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditional Sentences'],
            ['category' => 'English Grammar Structure']
        )->id;

        return [$themeTagId, $detailTagId, $structureTagId];
    }

    private function questionEntries(): array
    {
        // Tag IDs for sentence types
        $affirmativeTagId = Tag::firstOrCreate(['name' => 'Affirmative Sentence'], ['category' => 'Sentence Type'])->id;
        $negativeTagId = Tag::firstOrCreate(['name' => 'Negative Sentence'], ['category' => 'Sentence Type'])->id;
        $interrogativeTagId = Tag::firstOrCreate(['name' => 'Interrogative Sentence'], ['category' => 'Sentence Type'])->id;

        // Tag IDs for tenses/conditionals
        $pastPerfectTagId = Tag::firstOrCreate(['name' => 'Past Perfect'], ['category' => 'Tense'])->id;
        $wouldTagId = Tag::firstOrCreate(['name' => 'Would + Base Verb'], ['category' => 'Modal'])->id;
        $wouldHaveTagId = Tag::firstOrCreate(['name' => 'Would Have + V3'], ['category' => 'Modal'])->id;
        $pastSimpleTagId = Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'Tense'])->id;

        // Mixed conditional type tags
        $type1MixedTagId = Tag::firstOrCreate(['name' => 'Past condition → Present result'], ['category' => 'Mixed Conditional Type'])->id;
        $type2MixedTagId = Tag::firstOrCreate(['name' => 'Present condition → Past result'], ['category' => 'Mixed Conditional Type'])->id;

        return [
            // ===== A1 Level: 6 questions =====
            [
                'level' => 'A1',
                'question' => 'If I {a1} breakfast this morning, I {a2} hungry now.',
                'answers' => ['a1' => 'had eaten', 'a2' => "wouldn't be"],
                'options' => [
                    'a1' => ['had eaten', 'ate', 'eat', 'would eat'],
                    'a2' => ["wouldn't be", "won't be", "wasn't", "am not"],
                ],
                'verb_hints' => ['a1' => 'eat', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Описуємо минулу дію, яка впливає на теперішній стан.',
                    'Приклад: If I had slept well, I would feel better now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'She {a1} here now if she {a2} the bus.',
                'answers' => ['a1' => 'would be', 'a2' => "hadn't missed"],
                'options' => [
                    'a1' => ['would be', 'will be', 'is', 'was'],
                    'a2' => ["hadn't missed", "didn't miss", "doesn't miss", "won't miss"],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'miss'],
                'hints' => [
                    'Формула: would + base verb, if + Past Perfect.',
                    'Минула дія (пропустити автобус) впливає на теперішнє (бути тут).',
                    'Приклад: He would be happy now if he had passed the test.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'If Tom {a1} harder at school, he {a2} a better job today.',
                'answers' => ['a1' => 'had studied', 'a2' => 'would have'],
                'options' => [
                    'a1' => ['had studied', 'studied', 'studies', 'would study'],
                    'a2' => ['would have', 'will have', 'has', 'had'],
                ],
                'verb_hints' => ['a1' => 'study', 'a2' => 'have'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Минуле навчання впливає на теперішню роботу.',
                    'Приклад: If she had learned English, she would speak it now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'I {a1} so tired now if I {a2} to bed earlier last night.',
                'answers' => ['a1' => "wouldn't be", 'a2' => 'had gone'],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "am not", "wasn't"],
                    'a2' => ['had gone', 'went', 'go', 'would go'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'go'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + Past Perfect.',
                    'Заперечення в головній частині показує теперішній стан.',
                    'Приклад: I wouldn\'t be late if I had left earlier.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} you be happy now if you {a2} that course?',
                'answers' => ['a1' => 'Would', 'a2' => 'had taken'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Do', 'Did'],
                    'a2' => ['had taken', 'took', 'take', 'would take'],
                ],
                'verb_hints' => ['a1' => 'Use modal for unreal present', 'a2' => 'take'],
                'hints' => [
                    'Формула: Would + subject + base verb, if + Past Perfect?',
                    'Питання про теперішній нереальний стан через минулу дію.',
                    'Приклад: Would you be rich if you had invested earlier?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'If we {a1} the tickets yesterday, we {a2} at the concert now.',
                'answers' => ['a1' => 'had bought', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had bought', 'bought', 'buy', 'would buy'],
                    'a2' => ['would be', 'will be', 'are', 'were'],
                ],
                'verb_hints' => ['a1' => 'buy', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Минула покупка впливає на теперішню ситуацію.',
                    'Приклад: If I had booked a hotel, we would stay there now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],

            // ===== A2 Level: 6 questions =====
            [
                'level' => 'A2',
                'question' => 'If Maria {a1} English as a child, she {a2} fluent now.',
                'answers' => ['a1' => 'had learned', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had learned', 'learned', 'learns', 'would learn'],
                    'a2' => ['would be', 'will be', 'is', 'was'],
                ],
                'verb_hints' => ['a1' => 'learn', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + had + V3 (Past Perfect), would + base verb.',
                    'Описуємо наслідок минулої дії в теперішньому часі.',
                    'Приклад: If he had practiced, he would play better today.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'He {a1} the answer now if he {a2} attention in class yesterday.',
                'answers' => ['a1' => 'would know', 'a2' => 'had paid'],
                'options' => [
                    'a1' => ['would know', 'will know', 'knows', 'knew'],
                    'a2' => ['had paid', 'paid', 'pays', 'would pay'],
                ],
                'verb_hints' => ['a1' => 'know', 'a2' => 'pay'],
                'hints' => [
                    'Формула: would + base verb, if + Past Perfect.',
                    'Минула неуважність призвела до незнання зараз.',
                    'Приклад: She would understand if she had listened.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'If I {a1} a doctor, I {a2} him last week.',
                'answers' => ['a1' => 'were', 'a2' => 'would have helped'],
                'options' => [
                    'a1' => ['were', 'was', 'am', 'had been'],
                    'a2' => ['would have helped', 'would help', 'will help', 'helped'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'help'],
                'hints' => [
                    'Формула: If + Past Simple (were), would have + V3.',
                    'Теперішня нереальна умова і минулий результат.',
                    'Приклад: If I were rich, I would have bought that car.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'They {a1} the game yesterday if they {a2} better players.',
                'answers' => ['a1' => 'would have won', 'a2' => 'were'],
                'options' => [
                    'a1' => ['would have won', 'would win', 'will win', 'won'],
                    'a2' => ['were', 'are', 'had been', 'would be'],
                ],
                'verb_hints' => ['a1' => 'win', 'a2' => 'be'],
                'hints' => [
                    'Формула: would have + V3, if + Past Simple.',
                    'Постійна характеристика впливає на минулий результат.',
                    'Приклад: She would have passed if she were smarter.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'If you {a1} more careful, you {a2} your keys yesterday.',
                'answers' => ['a1' => 'were', 'a2' => "wouldn't have lost"],
                'options' => [
                    'a1' => ['were', 'are', 'had been', 'would be'],
                    'a2' => ["wouldn't have lost", "won't lose", "didn't lose", "wouldn't lose"],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'lose'],
                'hints' => [
                    'Формула: If + were, wouldn\'t have + V3.',
                    'Заперечення минулого результату через теперішню рису.',
                    'Приклад: If he were organized, he wouldn\'t have forgotten.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} she have gotten the job last month if she {a2} more experienced?',
                'answers' => ['a1' => 'Would', 'a2' => 'were'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Did', 'Had'],
                    'a2' => ['were', 'is', 'was', 'had been'],
                ],
                'verb_hints' => ['a1' => 'Use modal for unreal past result', 'a2' => 'be'],
                'hints' => [
                    'Формула: Would + subject + have + V3, if + Past Simple?',
                    'Питання про минулий результат за теперішньої умови.',
                    'Приклад: Would you have succeeded if you were braver?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],

            // ===== B1 Level: 6 questions =====
            [
                'level' => 'B1',
                'question' => 'If Sarah {a1} that job offer last year, she {a2} in London now.',
                'answers' => ['a1' => 'had accepted', 'a2' => 'would be living'],
                'options' => [
                    'a1' => ['had accepted', 'accepted', 'accepts', 'would accept'],
                    'a2' => ['would be living', 'will be living', 'is living', 'was living'],
                ],
                'verb_hints' => ['a1' => 'accept', 'a2' => 'live'],
                'hints' => [
                    'Формула: If + Past Perfect, would be + V-ing.',
                    'Минуле рішення впливає на теперішню тривалу дію.',
                    'Приклад: If I had moved, I would be working there now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Michael {a1} his own business today if he {a2} so much money on travel.',
                'answers' => ['a1' => 'would own', 'a2' => "hadn't spent"],
                'options' => [
                    'a1' => ['would own', 'will own', 'owns', 'owned'],
                    'a2' => ["hadn't spent", "didn't spend", "doesn't spend", "wouldn't spend"],
                ],
                'verb_hints' => ['a1' => 'own', 'a2' => 'spend'],
                'hints' => [
                    'Формула: would + base verb, if + hadn\'t + V3.',
                    'Заперечна умова в минулому з позитивним результатом зараз.',
                    'Приклад: She would have money if she hadn\'t wasted it.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'If I {a1} taller, I {a2} the basketball team in high school.',
                'answers' => ['a1' => 'were', 'a2' => 'would have joined'],
                'options' => [
                    'a1' => ['were', 'am', 'was', 'had been'],
                    'a2' => ['would have joined', 'would join', 'will join', 'joined'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'join'],
                'hints' => [
                    'Формула: If + were, would have + V3.',
                    'Постійна фізична характеристика і минулий результат.',
                    'Приклад: If she were stronger, she would have lifted it.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => '{a1} you be in a different career now if you {a2} to university?',
                'answers' => ['a1' => 'Would', 'a2' => 'had gone'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Do', 'Did'],
                    'a2' => ['had gone', 'went', 'go', 'would go'],
                ],
                'verb_hints' => ['a1' => 'Use modal for hypothetical present', 'a2' => 'go'],
                'hints' => [
                    'Формула: Would + subject + base verb, if + Past Perfect?',
                    'Питання про теперішній гіпотетичний стан.',
                    'Приклад: Would you live here if you had found a job?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'If we {a1} the early train, we {a2} waiting at the station right now.',
                'answers' => ['a1' => 'had caught', 'a2' => "wouldn't be"],
                'options' => [
                    'a1' => ['had caught', 'caught', 'catch', 'would catch'],
                    'a2' => ["wouldn't be", "won't be", "aren't", "weren't"],
                ],
                'verb_hints' => ['a1' => 'catch', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + Past Perfect, wouldn\'t be + V-ing.',
                    'Минула пропущена можливість і теперішній негативний стан.',
                    'Приклад: If I had hurried, I wouldn\'t be late now.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'She {a1} that expensive car last year if she {a2} so irresponsible with money.',
                'answers' => ['a1' => "wouldn't have bought", 'a2' => "weren't"],
                'options' => [
                    'a1' => ["wouldn't have bought", "won't buy", "didn't buy", "wouldn't buy"],
                    'a2' => ["weren't", "isn't", "hadn't been", "wouldn't be"],
                ],
                'verb_hints' => ['a1' => 'buy', 'a2' => 'be'],
                'hints' => [
                    'Формула: wouldn\'t have + V3, if + weren\'t.',
                    'Постійна риса характеру і минулий негативний результат.',
                    'Приклад: He wouldn\'t have failed if he weren\'t lazy.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],

            // ===== B2 Level: 6 questions =====
            [
                'level' => 'B2',
                'question' => 'If the government {a1} stricter environmental policies years ago, air quality {a2} much better today.',
                'answers' => ['a1' => 'had implemented', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had implemented', 'implemented', 'implements', 'would implement'],
                    'a2' => ['would be', 'will be', 'is', 'was'],
                ],
                'verb_hints' => ['a1' => 'implement', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + had + V3, would + base verb.',
                    'Минуле політичне рішення і теперішній екологічний стан.',
                    'Приклад: If they had invested, the economy would be stronger.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The company {a1} bankrupt last year if the CEO {a2} such a visionary leader.',
                'answers' => ['a1' => 'would have gone', 'a2' => "weren't"],
                'options' => [
                    'a1' => ['would have gone', 'would go', 'will go', 'went'],
                    'a2' => ["weren't", "isn't", "hadn't been", "wouldn't be"],
                ],
                'verb_hints' => ['a1' => 'go', 'a2' => 'be'],
                'hints' => [
                    'Формула: would have + V3, if + weren\'t.',
                    'Постійна якість лідера запобігла минулому банкрутству.',
                    'Приклад: We would have failed if he weren\'t so dedicated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => '{a1} the economy be more stable now if the central bank {a2} interest rates earlier?',
                'answers' => ['a1' => 'Would', 'a2' => 'had raised'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Does', 'Did'],
                    'a2' => ['had raised', 'raised', 'raises', 'would raise'],
                ],
                'verb_hints' => ['a1' => 'Use modal for hypothetical outcome', 'a2' => 'raise'],
                'hints' => [
                    'Формула: Would + subject + base verb, if + Past Perfect?',
                    'Питання про вплив минулого рішення на теперішній стан.',
                    'Приклад: Would inflation be lower if they had acted?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'If the researchers {a1} more meticulous, they {a2} the crucial data in the experiment last month.',
                'answers' => ['a1' => 'were', 'a2' => "wouldn't have overlooked"],
                'options' => [
                    'a1' => ['were', 'are', 'had been', 'would be'],
                    'a2' => ["wouldn't have overlooked", "won't overlook", "didn't overlook", "wouldn't overlook"],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'overlook'],
                'hints' => [
                    'Формула: If + were, wouldn\'t have + V3.',
                    'Постійна характеристика і заперечний минулий результат.',
                    'Приклад: If she were more careful, she wouldn\'t have made errors.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'If the diplomatic negotiations {a1} differently, the two countries {a2} at peace today.',
                'answers' => ['a1' => 'had gone', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had gone', 'went', 'go', 'would go'],
                    'a2' => ['would be', 'will be', 'are', 'were'],
                ],
                'verb_hints' => ['a1' => 'go', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Минулі переговори впливають на теперішній мирний стан.',
                    'Приклад: If the treaty had been signed, relations would be better.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'She {a1} such a renowned expert in her field today if she {a2} those groundbreaking research papers.',
                'answers' => ['a1' => "wouldn't be", 'a2' => "hadn't published"],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "isn't", "wasn't"],
                    'a2' => ["hadn't published", "didn't publish", "doesn't publish", "wouldn't publish"],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'publish'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + hadn\'t + V3.',
                    'Заперечення теперішнього статусу без минулої публікації.',
                    'Приклад: He wouldn\'t be famous if he hadn\'t written that book.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],

            // ===== C1 Level: 6 questions =====
            [
                'level' => 'C1',
                'question' => 'If the pharmaceutical company {a1} more rigorous clinical trials, the medication {a2} under such intense scrutiny now.',
                'answers' => ['a1' => 'had conducted', 'a2' => "wouldn't be"],
                'options' => [
                    'a1' => ['had conducted', 'conducted', 'conducts', 'would conduct'],
                    'a2' => ["wouldn't be", "won't be", "isn't", "wasn't"],
                ],
                'verb_hints' => ['a1' => 'conduct', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + Past Perfect, wouldn\'t be + noun phrase.',
                    'Минулі клінічні випробування і теперішня перевірка.',
                    'Приклад: If they had tested thoroughly, there wouldn\'t be concerns now.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The geopolitical landscape {a1} fundamentally different today if the Cold War {a2} in a nuclear conflict.',
                'answers' => ['a1' => 'would be', 'a2' => 'had escalated'],
                'options' => [
                    'a1' => ['would be', 'will be', 'is', 'was'],
                    'a2' => ['had escalated', 'escalated', 'escalates', 'would escalate'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'escalate'],
                'hints' => [
                    'Формула: would + base verb, if + Past Perfect.',
                    'Гіпотетична ескалація минулого і теперішній геополітичний стан.',
                    'Приклад: The world would look different if history had changed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'If the architect {a1} more environmentally conscious, the building {a2} with sustainable materials from the start.',
                'answers' => ['a1' => 'were', 'a2' => 'would have been constructed'],
                'options' => [
                    'a1' => ['were', 'is', 'was', 'had been'],
                    'a2' => ['would have been constructed', 'would be constructed', 'will be constructed', 'was constructed'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'construct'],
                'hints' => [
                    'Формула: If + were, would have been + V3.',
                    'Постійна екологічна свідомість і минуле будівництво.',
                    'Приклад: If he were innovative, the design would have been different.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} the literary canon look different today if scholars {a2} more diverse voices throughout history?',
                'answers' => ['a1' => 'Would', 'a2' => 'had included'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Does', 'Did'],
                    'a2' => ['had included', 'included', 'include', 'would include'],
                ],
                'verb_hints' => ['a1' => 'Use modal for counterfactual present', 'a2' => 'include'],
                'hints' => [
                    'Формула: Would + subject + base verb, if + Past Perfect?',
                    'Питання про вплив історичних рішень на теперішній канон.',
                    'Приклад: Would art be different if masters had shared techniques?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'If indigenous knowledge systems {a1} more highly valued, traditional ecological practices {a2} preserved more effectively.',
                'answers' => ['a1' => 'were', 'a2' => 'would have been'],
                'options' => [
                    'a1' => ['were', 'are', 'had been', 'would be'],
                    'a2' => ['would have been', 'would be', 'will be', 'are'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + were, would have been + V3.',
                    'Теперішнє ставлення і минуле збереження практик.',
                    'Приклад: If cultures were respected, traditions would have survived.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The neurological disorder {a1} so prevalent in the population today if researchers {a2} a cure decades ago.',
                'answers' => ['a1' => "wouldn't be", 'a2' => 'had discovered'],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "isn't", "wasn't"],
                    'a2' => ['had discovered', 'discovered', 'discover', 'would discover'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'discover'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + Past Perfect.',
                    'Минуле відкриття ліків і теперішня поширеність хвороби.',
                    'Приклад: Cancer wouldn\'t be so feared if a cure had been found.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],

            // ===== C2 Level: 6 questions =====
            [
                'level' => 'C2',
                'question' => 'If the epistemological foundations of Western philosophy {a1} more pluralistic, contemporary metaphysical discourse {a2} considerably less Eurocentric.',
                'answers' => ['a1' => 'had been', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had been', 'were', 'are', 'would be'],
                    'a2' => ['would be', 'will be', 'is', 'was'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + had been, would + base verb.',
                    'Історичні філософські основи і теперішній дискурс.',
                    'Приклад: If ancient thought had been different, modern views would change.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The socioeconomic ramifications of colonialism {a1} so entrenched in postcolonial societies today if imperial powers {a2} systemic exploitation.',
                'answers' => ['a1' => "wouldn't be", 'a2' => "hadn't institutionalized"],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "aren't", "weren't"],
                    'a2' => ["hadn't institutionalized", "didn't institutionalize", "don't institutionalize", "wouldn't institutionalize"],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'institutionalize'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + hadn\'t + V3.',
                    'Минула інституціоналізація і теперішні наслідки колоніалізму.',
                    'Приклад: Inequality wouldn\'t persist if systems hadn\'t been created.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'If quantum computing {a1} more advanced, cryptographic security protocols {a2} fundamentally restructured by now.',
                'answers' => ['a1' => 'were', 'a2' => 'would have been'],
                'options' => [
                    'a1' => ['were', 'is', 'had been', 'would be'],
                    'a2' => ['would have been', 'would be', 'will be', 'are'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + were, would have been + V3.',
                    'Теперішній рівень технології і минула реструктуризація.',
                    'Приклад: If AI were smarter, society would have been transformed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the trajectory of anthropogenic climate change be less catastrophic today if policymakers {a2} the warnings of environmental scientists in the 1970s?',
                'answers' => ['a1' => 'Would', 'a2' => 'had heeded'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Does', 'Did'],
                    'a2' => ['had heeded', 'heeded', 'heed', 'would heed'],
                ],
                'verb_hints' => ['a1' => 'Use modal for counterfactual scenario', 'a2' => 'heed'],
                'hints' => [
                    'Формула: Would + subject + base verb, if + Past Perfect?',
                    'Питання про альтернативний розвиток кліматичних змін.',
                    'Приклад: Would emissions be lower if action had been taken?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'If the Byzantine Empire {a1} in the fifteenth century, the Renaissance {a2} an entirely different intellectual character.',
                'answers' => ['a1' => "hadn't fallen", 'a2' => 'would have assumed'],
                'options' => [
                    'a1' => ["hadn't fallen", "didn't fall", "doesn't fall", "wouldn't fall"],
                    'a2' => ['would have assumed', 'would assume', 'will assume', 'assumed'],
                ],
                'verb_hints' => ['a1' => 'fall', 'a2' => 'assume'],
                'hints' => [
                    'Формула: If + hadn\'t + V3, would have + V3.',
                    'Обидві частини описують минулі гіпотетичні події.',
                    'Приклад: If Rome hadn\'t declined, Europe would have developed differently.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldHaveTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The philosophical underpinnings of existentialism {a1} profoundly different today if Kierkegaard and Nietzsche {a2} in dialogue with Eastern philosophical traditions.',
                'answers' => ['a1' => 'would be', 'a2' => 'had engaged'],
                'options' => [
                    'a1' => ['would be', 'will be', 'are', 'were'],
                    'a2' => ['had engaged', 'engaged', 'engage', 'would engage'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'engage'],
                'hints' => [
                    'Формула: would + base verb, if + Past Perfect.',
                    'Минулий філософський діалог і теперішній екзистенціалізм.',
                    'Приклад: Modern thought would differ if thinkers had collaborated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
        ];
    }

    private function flattenOptions(array $options): array
    {
        $flat = [];
        foreach ($options as $values) {
            foreach ($values as $value) {
                if (!in_array($value, $flat, true)) {
                    $flat[] = $value;
                }
            }
        }
        return $flat;
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

    private function buildExplanations(array $options, array $answers, string $level): array
    {
        $explanations = [];
        foreach ($options as $marker => $values) {
            $correctAnswer = $answers[$marker] ?? '';
            foreach ($values as $value) {
                if ($value === $correctAnswer) {
                    $explanations[$value] = $this->correctExplanation($value, $marker);
                } else {
                    $explanations[$value] = $this->wrongExplanation($value, $correctAnswer, $marker);
                }
            }
        }
        return $explanations;
    }

    private function correctExplanation(string $correct, string $marker): string
    {
        $explanationsByType = [
            'had' => "✅ Правильно! «{$correct}» — це Past Perfect, необхідний для опису минулої нереальної умови у змішаному умовному реченні.",
            'would' => "✅ Правильно! «{$correct}» — це форма з would, яка показує теперішній або минулий гіпотетичний результат.",
            'were' => "✅ Правильно! «{$correct}» — форма were використовується для теперішньої нереальної умови (навіть з однини).",
        ];

        if (str_contains(strtolower($correct), 'had ') || str_contains(strtolower($correct), "hadn't")) {
            return $explanationsByType['had'];
        }
        if (str_contains(strtolower($correct), 'would')) {
            return $explanationsByType['would'];
        }
        if (strtolower($correct) === 'were' || str_contains(strtolower($correct), "weren't")) {
            return $explanationsByType['were'];
        }

        return "✅ Правильно! «{$correct}» є коректним вибором для цього змішаного умовного речення.";
    }

    private function wrongExplanation(string $wrong, string $correct, string $marker): string
    {
        // Past Perfect vs other forms
        if ((str_contains(strtolower($correct), 'had ') || str_contains(strtolower($correct), "hadn't")) 
            && !str_contains(strtolower($wrong), 'had ') && !str_contains(strtolower($wrong), "hadn't")) {
            return "❌ «{$wrong}» — неправильна форма. Для опису минулої нереальної умови потрібен Past Perfect (had + V3).";
        }

        // Would vs will
        if (str_contains(strtolower($correct), 'would') && str_contains(strtolower($wrong), 'will')) {
            return "❌ «{$wrong}» — will використовується для реальних майбутніх подій. Тут потрібен would для гіпотетичної ситуації.";
        }

        // Would have vs would
        if (str_contains(strtolower($correct), 'would have') && !str_contains(strtolower($wrong), 'would have') && str_contains(strtolower($wrong), 'would')) {
            return "❌ «{$wrong}» — форма would + V1 показує теперішній результат. Для минулого результату потрібен would have + V3.";
        }

        // Were vs was/is/are
        if ((strtolower($correct) === 'were' || str_contains(strtolower($correct), "weren't"))) {
            if (strtolower($wrong) === 'was') {
                return "❌ «{$wrong}» — у нереальних умовних реченнях використовуємо were з усіма особами, включаючи I/he/she/it.";
            }
            if (strtolower($wrong) === 'is' || strtolower($wrong) === 'are' || strtolower($wrong) === 'am') {
                return "❌ «{$wrong}» — Present Simple. Для нереальної теперішньої умови потрібен Past Simple (were).";
            }
        }

        // Simple past vs past perfect
        if (str_contains(strtolower($correct), 'had ') && !str_contains(strtolower($wrong), 'had ')) {
            if (!str_contains(strtolower($wrong), 'would') && !str_contains(strtolower($wrong), 'will')) {
                return "❌ «{$wrong}» — це проста минула форма. Для змішаного умовного потрібен Past Perfect (had + V3).";
            }
        }

        // Present simple in wrong place
        if (!str_contains(strtolower($wrong), 'had') && !str_contains(strtolower($wrong), 'would') && !str_contains(strtolower($wrong), 'will') && !str_contains(strtolower($wrong), 'were') && strtolower($wrong) !== 'was') {
            return "❌ «{$wrong}» — Present Simple не використовується у змішаних умовних реченнях.";
        }

        return "❌ «{$wrong}» не підходить у цьому контексті. Перевірте правила змішаних умовних речень.";
    }
}
