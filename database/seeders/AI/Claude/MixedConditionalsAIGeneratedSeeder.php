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
                'question' => 'If the Byzantine Empire {a1} in the fifteenth century, contemporary Orthodox theological discourse {a2} fundamentally different today.',
                'answers' => ['a1' => "hadn't fallen", 'a2' => 'would be'],
                'options' => [
                    'a1' => ["hadn't fallen", "didn't fall", "doesn't fall", "wouldn't fall"],
                    'a2' => ['would be', 'will be', 'is', 'was'],
                ],
                'verb_hints' => ['a1' => 'fall', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + hadn\'t + V3, would + base verb.',
                    'Минуле падіння імперії впливає на теперішній богословський дискурс.',
                    'Приклад: If ancient libraries hadn\'t burned, knowledge would be more accessible now.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
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

            // ===== A1 Level: Additional 12 questions =====
            [
                'level' => 'A1',
                'question' => 'If he {a1} the medicine yesterday, he {a2} sick now.',
                'answers' => ['a1' => 'had taken', 'a2' => "wouldn't be"],
                'options' => [
                    'a1' => ['had taken', 'took', 'takes', 'would take'],
                    'a2' => ["wouldn't be", "won't be", "isn't", "wasn't"],
                ],
                'verb_hints' => ['a1' => 'take', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Минулий прийом ліків впливає на теперішній стан здоров\'я.',
                    'Приклад: If she had rested, she would feel better now.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'We {a1} lost now if we {a2} the map.',
                'answers' => ['a1' => 'would be', 'a2' => "hadn't brought"],
                'options' => [
                    'a1' => ['would be', 'will be', 'are', 'were'],
                    'a2' => ["hadn't brought", "didn't bring", "don't bring", "won't bring"],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'bring'],
                'hints' => [
                    'Формула: would + base verb, if + hadn\'t + V3.',
                    'Минула дія (взяти карту) впливає на теперішнє (бути загубленими).',
                    'Приклад: We would be hungry if we hadn\'t eaten.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'If my parents {a1} me drive, I {a2} my own car now.',
                'answers' => ['a1' => 'had taught', 'a2' => 'would have'],
                'options' => [
                    'a1' => ['had taught', 'taught', 'teach', 'would teach'],
                    'a2' => ['would have', 'will have', 'have', 'had'],
                ],
                'verb_hints' => ['a1' => 'teach', 'a2' => 'have'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Минуле навчання впливає на теперішнє володіння.',
                    'Приклад: If they had trained me, I would work here now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} she speak French now if she {a2} in Paris?',
                'answers' => ['a1' => 'Would', 'a2' => 'had lived'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Does', 'Did'],
                    'a2' => ['had lived', 'lived', 'lives', 'would live'],
                ],
                'verb_hints' => ['a1' => 'Use modal for unreal present', 'a2' => 'live'],
                'hints' => [
                    'Формула: Would + subject + base verb, if + Past Perfect?',
                    'Питання про теперішню здатність через минулий досвід.',
                    'Приклад: Would he swim if he had learned as a child?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'If you {a1} your homework, you {a2} watch TV now.',
                'answers' => ['a1' => 'had finished', 'a2' => 'could'],
                'options' => [
                    'a1' => ['had finished', 'finished', 'finish', 'would finish'],
                    'a2' => ['could', 'can', 'will', 'would'],
                ],
                'verb_hints' => ['a1' => 'finish', 'a2' => 'Use modal for ability'],
                'hints' => [
                    'Формула: If + Past Perfect, could + base verb.',
                    'Минуле завершення впливає на теперішню можливість.',
                    'Приклад: If I had cleaned, I could rest now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The dog {a1} hungry now if you {a2} it this morning.',
                'answers' => ['a1' => "wouldn't be", 'a2' => 'had fed'],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "isn't", "wasn't"],
                    'a2' => ['had fed', 'fed', 'feed', 'would feed'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'feed'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + Past Perfect.',
                    'Заперечення теперішнього стану через минулу дію.',
                    'Приклад: The cat wouldn\'t be thirsty if you had given it water.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'If I {a1} brave, I {a2} to her yesterday.',
                'answers' => ['a1' => 'were', 'a2' => 'would have talked'],
                'options' => [
                    'a1' => ['were', 'am', 'was', 'had been'],
                    'a2' => ['would have talked', 'would talk', 'will talk', 'talked'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'talk'],
                'hints' => [
                    'Формула: If + were, would have + V3.',
                    'Теперішня риса характеру і минулий результат.',
                    'Приклад: If I were confident, I would have asked.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'They {a1} the match last week if they {a2} a better team.',
                'answers' => ['a1' => 'would have won', 'a2' => 'were'],
                'options' => [
                    'a1' => ['would have won', 'would win', 'will win', 'won'],
                    'a2' => ['were', 'are', 'had been', 'would be'],
                ],
                'verb_hints' => ['a1' => 'win', 'a2' => 'be'],
                'hints' => [
                    'Формула: would have + V3, if + were.',
                    'Постійна якість команди і минулий результат.',
                    'Приклад: She would have succeeded if she were more patient.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'If he {a1} so shy, he {a2} more friends at the party last night.',
                'answers' => ['a1' => "weren't", 'a2' => 'would have made'],
                'options' => [
                    'a1' => ["weren't", "isn't", "hadn't been", "wouldn't be"],
                    'a2' => ['would have made', 'would make', 'will make', 'made'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'make'],
                'hints' => [
                    'Формула: If + weren\'t, would have + V3.',
                    'Заперечення теперішньої риси і позитивний минулий результат.',
                    'Приклад: If she weren\'t afraid, she would have tried.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} he have passed the test last month if he {a2} smarter?',
                'answers' => ['a1' => 'Would', 'a2' => 'were'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Did', 'Had'],
                    'a2' => ['were', 'is', 'was', 'had been'],
                ],
                'verb_hints' => ['a1' => 'Use modal for unreal past', 'a2' => 'be'],
                'hints' => [
                    'Формула: Would + subject + have + V3, if + were?',
                    'Питання про минулий результат за теперішньої умови.',
                    'Приклад: Would you have won if you were faster?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'If the weather {a1} nice yesterday, we {a2} at the beach now.',
                'answers' => ['a1' => 'had been', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had been', 'was', 'is', 'would be'],
                    'a2' => ['would be', 'will be', 'are', 'were'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + had been, would + base verb.',
                    'Минула погода впливає на теперішнє місцезнаходження.',
                    'Приклад: If it had rained, we would be inside now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'She {a1} so happy now if her team {a2} the championship.',
                'answers' => ['a1' => "wouldn't be", 'a2' => "hadn't won"],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "isn't", "wasn't"],
                    'a2' => ["hadn't won", "didn't win", "doesn't win", "wouldn't win"],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'win'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + hadn\'t + V3.',
                    'Заперечення теперішнього стану без минулої перемоги.',
                    'Приклад: He wouldn\'t be proud if he hadn\'t achieved it.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],

            // ===== A2 Level: Additional 12 questions =====
            [
                'level' => 'A2',
                'question' => 'If Lisa {a1} that email, she {a2} about the meeting now.',
                'answers' => ['a1' => 'had read', 'a2' => 'would know'],
                'options' => [
                    'a1' => ['had read', 'read', 'reads', 'would read'],
                    'a2' => ['would know', 'will know', 'knows', 'knew'],
                ],
                'verb_hints' => ['a1' => 'read', 'a2' => 'know'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Минуле читання впливає на теперішнє знання.',
                    'Приклад: If he had checked, he would understand now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'We {a1} tired now if we {a2} the night train.',
                'answers' => ['a1' => "wouldn't be", 'a2' => "hadn't taken"],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "aren't", "weren't"],
                    'a2' => ["hadn't taken", "didn't take", "don't take", "wouldn't take"],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'take'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + hadn\'t + V3.',
                    'Заперечення теперішньої втоми через минулу подорож.',
                    'Приклад: I wouldn\'t be sleepy if I hadn\'t stayed up late.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} you be angry now if I {a2} you the truth earlier?',
                'answers' => ['a1' => 'Would', 'a2' => 'had told'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Do', 'Did'],
                    'a2' => ['had told', 'told', 'tell', 'would tell'],
                ],
                'verb_hints' => ['a1' => 'Use modal for hypothetical', 'a2' => 'tell'],
                'hints' => [
                    'Формула: Would + subject + base verb, if + Past Perfect?',
                    'Питання про теперішній емоційний стан через минулу дію.',
                    'Приклад: Would she be sad if I had left earlier?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'If John {a1} harder in school, he {a2} a better job today.',
                'answers' => ['a1' => 'had worked', 'a2' => 'would have'],
                'options' => [
                    'a1' => ['had worked', 'worked', 'works', 'would work'],
                    'a2' => ['would have', 'will have', 'has', 'had'],
                ],
                'verb_hints' => ['a1' => 'work', 'a2' => 'have'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Минуле зусилля впливає на теперішню кар\'єру.',
                    'Приклад: If she had studied, she would earn more now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The garden {a1} beautiful now if we {a2} flowers last spring.',
                'answers' => ['a1' => 'would look', 'a2' => 'had planted'],
                'options' => [
                    'a1' => ['would look', 'will look', 'looks', 'looked'],
                    'a2' => ['had planted', 'planted', 'plant', 'would plant'],
                ],
                'verb_hints' => ['a1' => 'look', 'a2' => 'plant'],
                'hints' => [
                    'Формула: would + base verb, if + Past Perfect.',
                    'Минуле садіння впливає на теперішній вигляд.',
                    'Приклад: The house would be warm if we had fixed the heating.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'If she {a1} a good singer, she {a2} the competition last year.',
                'answers' => ['a1' => 'were', 'a2' => 'would have won'],
                'options' => [
                    'a1' => ['were', 'is', 'was', 'had been'],
                    'a2' => ['would have won', 'would win', 'will win', 'won'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'win'],
                'hints' => [
                    'Формула: If + were, would have + V3.',
                    'Постійний талант і минулий результат.',
                    'Приклад: If he were talented, he would have succeeded.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'They {a1} the project on time last month if they {a2} more organized.',
                'answers' => ['a1' => 'would have finished', 'a2' => 'were'],
                'options' => [
                    'a1' => ['would have finished', 'would finish', 'will finish', 'finished'],
                    'a2' => ['were', 'are', 'had been', 'would be'],
                ],
                'verb_hints' => ['a1' => 'finish', 'a2' => 'be'],
                'hints' => [
                    'Формула: would have + V3, if + were.',
                    'Постійна організованість і минуле завершення.',
                    'Приклад: She would have arrived if she were more punctual.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'If you {a1} so lazy, you {a2} your work yesterday.',
                'answers' => ['a1' => "weren't", 'a2' => 'would have done'],
                'options' => [
                    'a1' => ["weren't", "aren't", "hadn't been", "wouldn't be"],
                    'a2' => ['would have done', 'would do', 'will do', 'did'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'do'],
                'hints' => [
                    'Формула: If + weren\'t, would have + V3.',
                    'Заперечення постійної риси і минулий результат.',
                    'Приклад: If he weren\'t careless, he wouldn\'t have forgotten.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} the team have scored more goals last game if the players {a2} faster?',
                'answers' => ['a1' => 'Would', 'a2' => 'were'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Did', 'Had'],
                    'a2' => ['were', 'are', 'was', 'had been'],
                ],
                'verb_hints' => ['a1' => 'Use modal for hypothetical', 'a2' => 'be'],
                'hints' => [
                    'Формула: Would + subject + have + V3, if + were?',
                    'Питання про минулий результат за теперішньої умови.',
                    'Приклад: Would he have caught it if he were taller?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'If the bus {a1} on time, I {a2} late for school now.',
                'answers' => ['a1' => 'had arrived', 'a2' => "wouldn't be"],
                'options' => [
                    'a1' => ['had arrived', 'arrived', 'arrives', 'would arrive'],
                    'a2' => ["wouldn't be", "won't be", "am not", "wasn't"],
                ],
                'verb_hints' => ['a1' => 'arrive', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + Past Perfect, wouldn\'t + base verb.',
                    'Минуле прибуття автобуса і теперішнє запізнення.',
                    'Приклад: If the train had come, we wouldn\'t be waiting.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'My sister {a1} happy now if she {a2} her dream job.',
                'answers' => ['a1' => 'would be', 'a2' => 'had gotten'],
                'options' => [
                    'a1' => ['would be', 'will be', 'is', 'was'],
                    'a2' => ['had gotten', 'got', 'gets', 'would get'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'get'],
                'hints' => [
                    'Формула: would + base verb, if + Past Perfect.',
                    'Минуле отримання роботи і теперішній стан.',
                    'Приклад: He would be proud if he had achieved his goal.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'If I {a1} you, I {a2} that offer last week.',
                'answers' => ['a1' => 'were', 'a2' => 'would have accepted'],
                'options' => [
                    'a1' => ['were', 'am', 'was', 'had been'],
                    'a2' => ['would have accepted', 'would accept', 'will accept', 'accepted'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'accept'],
                'hints' => [
                    'Формула: If + were, would have + V3.',
                    'Гіпотетична ситуація і минуле рішення.',
                    'Приклад: If I were him, I would have tried harder.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],

            // ===== B1 Level: Additional 12 questions =====
            [
                'level' => 'B1',
                'question' => 'If the company {a1} in technology earlier, it {a2} more competitive today.',
                'answers' => ['a1' => 'had invested', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had invested', 'invested', 'invests', 'would invest'],
                    'a2' => ['would be', 'will be', 'is', 'was'],
                ],
                'verb_hints' => ['a1' => 'invest', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Минулі інвестиції впливають на теперішню конкурентоспроможність.',
                    'Приклад: If they had modernized, they would succeed now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The patient {a1} in such critical condition now if the doctors {a2} the symptoms earlier.',
                'answers' => ['a1' => "wouldn't be", 'a2' => 'had recognized'],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "isn't", "wasn't"],
                    'a2' => ['had recognized', 'recognized', 'recognize', 'would recognize'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'recognize'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + Past Perfect.',
                    'Минула діагностика і теперішній стан здоров\'я.',
                    'Приклад: He wouldn\'t suffer now if they had treated him sooner.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => '{a1} the city look different today if urban planners {a2} more parks decades ago?',
                'answers' => ['a1' => 'Would', 'a2' => 'had built'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Does', 'Did'],
                    'a2' => ['had built', 'built', 'build', 'would build'],
                ],
                'verb_hints' => ['a1' => 'Use modal for hypothetical', 'a2' => 'build'],
                'hints' => [
                    'Формула: Would + subject + base verb, if + Past Perfect?',
                    'Питання про вплив минулого планування на теперішній вигляд.',
                    'Приклад: Would traffic be better if they had expanded roads?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'If the students {a1} more diligent, they {a2} better results in last semester\'s exams.',
                'answers' => ['a1' => 'were', 'a2' => 'would have achieved'],
                'options' => [
                    'a1' => ['were', 'are', 'was', 'had been'],
                    'a2' => ['would have achieved', 'would achieve', 'will achieve', 'achieved'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'achieve'],
                'hints' => [
                    'Формула: If + were, would have + V3.',
                    'Постійна працьовитість і минулі результати.',
                    'Приклад: If she were more focused, she would have passed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The marketing campaign {a1} more successful last quarter if the team {a2} more creative.',
                'answers' => ['a1' => 'would have been', 'a2' => 'were'],
                'options' => [
                    'a1' => ['would have been', 'would be', 'will be', 'was'],
                    'a2' => ['were', 'are', 'had been', 'would be'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: would have + V3, if + were.',
                    'Постійна креативність і минулий успіх кампанії.',
                    'Приклад: The project would have worked if the idea were better.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'If the manager {a1} such a perfectionist, the project {a2} on time last month.',
                'answers' => ['a1' => "weren't", 'a2' => 'would have been completed'],
                'options' => [
                    'a1' => ["weren't", "isn't", "hadn't been", "wouldn't be"],
                    'a2' => ['would have been completed', 'would be completed', 'will be completed', 'was completed'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'complete'],
                'hints' => [
                    'Формула: If + weren\'t, would have been + V3.',
                    'Заперечення постійної риси і минуле завершення.',
                    'Приклад: If he weren\'t so strict, we would have finished earlier.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'If our team {a1} the contract last year, we {a2} in a much stronger position now.',
                'answers' => ['a1' => 'had secured', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had secured', 'secured', 'secures', 'would secure'],
                    'a2' => ['would be', 'will be', 'are', 'were'],
                ],
                'verb_hints' => ['a1' => 'secure', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Минуле отримання контракту і теперішня позиція.',
                    'Приклад: If we had signed the deal, we would have more clients now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Jennifer {a1} so stressed at work now if she {a2} better time management skills.',
                'answers' => ['a1' => "wouldn't be", 'a2' => 'had developed'],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "isn't", "wasn't"],
                    'a2' => ['had developed', 'developed', 'develops', 'would develop'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'develop'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + Past Perfect.',
                    'Минулий розвиток навичок і теперішній стрес.',
                    'Приклад: He wouldn\'t struggle now if he had learned earlier.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => '{a1} we have more customers now if we {a2} our marketing strategy last year?',
                'answers' => ['a1' => 'Would', 'a2' => 'had changed'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Do', 'Did'],
                    'a2' => ['had changed', 'changed', 'change', 'would change'],
                ],
                'verb_hints' => ['a1' => 'Use modal for hypothetical', 'a2' => 'change'],
                'hints' => [
                    'Формула: Would + subject + have + noun, if + Past Perfect?',
                    'Питання про теперішню ситуацію через минулу зміну.',
                    'Приклад: Would sales be higher if we had advertised more?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'If he {a1} a more responsible person, he {a2} his bills on time last month.',
                'answers' => ['a1' => 'were', 'a2' => 'would have paid'],
                'options' => [
                    'a1' => ['were', 'is', 'was', 'had been'],
                    'a2' => ['would have paid', 'would pay', 'will pay', 'paid'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'pay'],
                'hints' => [
                    'Формула: If + were, would have + V3.',
                    'Постійна відповідальність і минула оплата.',
                    'Приклад: If she were more careful, she would have saved money.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The restaurant {a1} still open today if the owner {a2} more attention to customer feedback.',
                'answers' => ['a1' => 'would be', 'a2' => 'had paid'],
                'options' => [
                    'a1' => ['would be', 'will be', 'is', 'was'],
                    'a2' => ['had paid', 'paid', 'pays', 'would pay'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'pay'],
                'hints' => [
                    'Формула: would + base verb, if + Past Perfect.',
                    'Минула увага до відгуків і теперішній стан бізнесу.',
                    'Приклад: The shop would exist if they had improved service.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'If the software {a1} more user-friendly, it {a2} better in the market last year.',
                'answers' => ['a1' => 'were', 'a2' => 'would have sold'],
                'options' => [
                    'a1' => ['were', 'is', 'was', 'had been'],
                    'a2' => ['would have sold', 'would sell', 'will sell', 'sold'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'sell'],
                'hints' => [
                    'Формула: If + were, would have + V3.',
                    'Постійна якість продукту і минулі продажі.',
                    'Приклад: If the app were simpler, it would have been downloaded more.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],

            // ===== B2 Level: Additional 12 questions =====
            [
                'level' => 'B2',
                'question' => 'If the renewable energy sector {a1} more government support in the past, carbon emissions {a2} significantly lower today.',
                'answers' => ['a1' => 'had received', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had received', 'received', 'receives', 'would receive'],
                    'a2' => ['would be', 'will be', 'are', 'were'],
                ],
                'verb_hints' => ['a1' => 'receive', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Минула підтримка впливає на теперішній рівень викидів.',
                    'Приклад: If policies had changed, pollution would be less severe now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The pharmaceutical industry {a1} facing such scrutiny now if it {a2} more transparent about clinical trials.',
                'answers' => ['a1' => "wouldn't be", 'a2' => 'had been'],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "isn't", "wasn't"],
                    'a2' => ['had been', 'was', 'is', 'would be'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: wouldn\'t be + V-ing, if + Past Perfect.',
                    'Минула прозорість і теперішня перевірка.',
                    'Приклад: Banks wouldn\'t be struggling if they had been more careful.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => '{a1} international relations be more harmonious today if world leaders {a2} dialogue over conflict during the Cold War?',
                'answers' => ['a1' => 'Would', 'a2' => 'had prioritized'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Does', 'Did'],
                    'a2' => ['had prioritized', 'prioritized', 'prioritize', 'would prioritize'],
                ],
                'verb_hints' => ['a1' => 'Use modal for counterfactual', 'a2' => 'prioritize'],
                'hints' => [
                    'Формула: Would + subject + base verb, if + Past Perfect?',
                    'Питання про вплив минулих рішень на сучасні відносини.',
                    'Приклад: Would peace be lasting if they had negotiated earlier?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'If the education system {a1} more innovative, students {a2} better prepared for the job market in the past decade.',
                'answers' => ['a1' => 'were', 'a2' => 'would have been'],
                'options' => [
                    'a1' => ['were', 'is', 'was', 'had been'],
                    'a2' => ['would have been', 'would be', 'will be', 'were'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + were, would have been + V3.',
                    'Постійна інноваційність системи і минула підготовка.',
                    'Приклад: If schools were better, graduates would have found jobs easier.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The infrastructure project {a1} completed on schedule last year if the contractors {a2} more reliable.',
                'answers' => ['a1' => 'would have been', 'a2' => 'were'],
                'options' => [
                    'a1' => ['would have been', 'would be', 'will be', 'was'],
                    'a2' => ['were', 'are', 'had been', 'would be'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: would have been + V3, if + were.',
                    'Постійна надійність підрядників і минуле завершення.',
                    'Приклад: The bridge would have opened if builders were more skilled.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'If healthcare {a1} more accessible, fewer people {a2} from preventable diseases in recent years.',
                'answers' => ['a1' => 'were', 'a2' => 'would have suffered'],
                'options' => [
                    'a1' => ['were', 'is', 'was', 'had been'],
                    'a2' => ['would have suffered', 'would suffer', 'will suffer', 'suffered'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'suffer'],
                'hints' => [
                    'Формула: If + were, would have + V3.',
                    'Постійна доступність медицини і минулі страждання.',
                    'Приклад: If treatment were cheaper, more patients would have recovered.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'If the startup {a1} its initial funding round, it {a2} one of the leading tech companies today.',
                'answers' => ['a1' => "hadn't lost", 'a2' => 'would be'],
                'options' => [
                    'a1' => ["hadn't lost", "didn't lose", "doesn't lose", "wouldn't lose"],
                    'a2' => ['would be', 'will be', 'is', 'was'],
                ],
                'verb_hints' => ['a1' => 'lose', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + hadn\'t + V3, would + base verb.',
                    'Минула втрата фінансування і теперішній статус.',
                    'Приклад: If they hadn\'t failed, they would dominate the market now.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The endangered species {a1} thriving in the wild today if conservation efforts {a2} earlier.',
                'answers' => ['a1' => 'would be', 'a2' => 'had started'],
                'options' => [
                    'a1' => ['would be', 'will be', 'is', 'was'],
                    'a2' => ['had started', 'started', 'start', 'would start'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'start'],
                'hints' => [
                    'Формула: would be + V-ing, if + Past Perfect.',
                    'Минулі зусилля збереження і теперішній стан видів.',
                    'Приклад: The forest would be greener if we had protected it.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => '{a1} the housing market be more stable now if regulators {a2} stricter lending policies a decade ago?',
                'answers' => ['a1' => 'Would', 'a2' => 'had enforced'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Does', 'Did'],
                    'a2' => ['had enforced', 'enforced', 'enforce', 'would enforce'],
                ],
                'verb_hints' => ['a1' => 'Use modal for hypothetical', 'a2' => 'enforce'],
                'hints' => [
                    'Формула: Would + subject + be, if + Past Perfect?',
                    'Питання про вплив минулого регулювання на теперішній ринок.',
                    'Приклад: Would prices be lower if they had controlled inflation?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'If the judicial system {a1} more efficient, the backlog of cases {a2} resolved much faster in the past few years.',
                'answers' => ['a1' => 'were', 'a2' => 'would have been'],
                'options' => [
                    'a1' => ['were', 'is', 'was', 'had been'],
                    'a2' => ['would have been', 'would be', 'will be', 'was'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + were, would have been + V3.',
                    'Постійна ефективність системи і минуле вирішення справ.',
                    'Приклад: If courts were faster, justice would have been served sooner.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The artist {a1} such international acclaim today if her early work {a2} by major galleries.',
                'answers' => ['a1' => "wouldn't have", 'a2' => "hadn't been discovered"],
                'options' => [
                    'a1' => ["wouldn't have", "won't have", "doesn't have", "didn't have"],
                    'a2' => ["hadn't been discovered", "wasn't discovered", "isn't discovered", "wouldn't be discovered"],
                ],
                'verb_hints' => ['a1' => 'have', 'a2' => 'discover'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + hadn\'t been + V3.',
                    'Минуле відкриття і теперішнє визнання.',
                    'Приклад: He wouldn\'t be famous if he hadn\'t been noticed.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'If cybersecurity measures {a1} more robust, the data breach last month {a2}.',
                'answers' => ['a1' => 'were', 'a2' => "wouldn't have occurred"],
                'options' => [
                    'a1' => ['were', 'are', 'had been', 'would be'],
                    'a2' => ["wouldn't have occurred", "won't occur", "didn't occur", "wouldn't occur"],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'occur'],
                'hints' => [
                    'Формула: If + were, wouldn\'t have + V3.',
                    'Постійна захищеність і минула атака.',
                    'Приклад: If security were better, the hack wouldn\'t have happened.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],

            // ===== C1 Level: Additional 12 questions =====
            [
                'level' => 'C1',
                'question' => 'If the socioeconomic policies of the 1980s {a1} more egalitarian, wealth inequality {a2} far less pronounced in contemporary society.',
                'answers' => ['a1' => 'had been', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had been', 'were', 'are', 'would be'],
                    'a2' => ['would be', 'will be', 'is', 'was'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + had been, would + base verb.',
                    'Минула політика впливає на теперішню нерівність.',
                    'Приклад: If reforms had been implemented, poverty would be lower today.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The biodiversity of tropical rainforests {a1} under such severe threat today if deforestation {a2} at such an alarming rate over the past decades.',
                'answers' => ['a1' => "wouldn't be", 'a2' => "hadn't accelerated"],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "isn't", "wasn't"],
                    'a2' => ["hadn't accelerated", "didn't accelerate", "doesn't accelerate", "wouldn't accelerate"],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'accelerate'],
                'hints' => [
                    'Формула: wouldn\'t be + prep phrase, if + hadn\'t + V3.',
                    'Минуле знеліснення і теперішня загроза біорізноманіттю.',
                    'Приклад: Species wouldn\'t be endangered if habitats hadn\'t been destroyed.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} the global financial architecture be more resilient today if central banks {a2} more prudent monetary policies before the 2008 crisis?',
                'answers' => ['a1' => 'Would', 'a2' => 'had adopted'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Does', 'Did'],
                    'a2' => ['had adopted', 'adopted', 'adopt', 'would adopt'],
                ],
                'verb_hints' => ['a1' => 'Use modal for counterfactual', 'a2' => 'adopt'],
                'hints' => [
                    'Формула: Would + subject + be, if + Past Perfect?',
                    'Питання про вплив минулої монетарної політики на теперішню стійкість.',
                    'Приклад: Would banks be safer if regulations had been stricter?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'If the diplomatic corps {a1} more adept at intercultural communication, the treaty negotiations {a2} more smoothly in the previous administration.',
                'answers' => ['a1' => 'were', 'a2' => 'would have proceeded'],
                'options' => [
                    'a1' => ['were', 'is', 'was', 'had been'],
                    'a2' => ['would have proceeded', 'would proceed', 'will proceed', 'proceeded'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'proceed'],
                'hints' => [
                    'Формула: If + were, would have + V3.',
                    'Постійна дипломатична вправність і минулі переговори.',
                    'Приклад: If negotiators were skilled, agreements would have been reached.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The humanitarian crisis {a1} averted last year if international organizations {a2} better equipped to respond to emergencies.',
                'answers' => ['a1' => 'would have been', 'a2' => 'were'],
                'options' => [
                    'a1' => ['would have been', 'would be', 'will be', 'was'],
                    'a2' => ['were', 'are', 'had been', 'would be'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: would have been + V3, if + were.',
                    'Постійна готовність організацій і минула криза.',
                    'Приклад: The disaster would have been prevented if systems were better.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'If the pharmaceutical regulations {a1} more stringent, the adverse drug reactions reported last quarter {a2}.',
                'answers' => ['a1' => 'were', 'a2' => "wouldn't have occurred"],
                'options' => [
                    'a1' => ['were', 'are', 'had been', 'would be'],
                    'a2' => ["wouldn't have occurred", "won't occur", "didn't occur", "wouldn't occur"],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'occur'],
                'hints' => [
                    'Формула: If + were, wouldn\'t have + V3.',
                    'Постійна суворість регуляцій і минулі побічні ефекти.',
                    'Приклад: If testing were thorough, side effects wouldn\'t have appeared.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'If the academic institution {a1} its curriculum decades ago, its graduates {a2} better positioned in today\'s job market.',
                'answers' => ['a1' => 'had modernized', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had modernized', 'modernized', 'modernizes', 'would modernize'],
                    'a2' => ['would be', 'will be', 'are', 'were'],
                ],
                'verb_hints' => ['a1' => 'modernize', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + Past Perfect, would + base verb.',
                    'Минула модернізація і теперішня конкурентоспроможність випускників.',
                    'Приклад: If they had updated courses, students would find jobs easier now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The geopolitical tensions {a1} so acute today if the superpowers {a2} multilateral agreements in the post-war period.',
                'answers' => ['a1' => "wouldn't be", 'a2' => 'had honored'],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "aren't", "weren't"],
                    'a2' => ['had honored', 'honored', 'honor', 'would honor'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'honor'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + Past Perfect.',
                    'Минуле дотримання угод і теперішня напруженість.',
                    'Приклад: Conflicts wouldn\'t exist if treaties had been respected.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} the linguistic diversity of the region be richer today if colonial powers {a2} local languages during their rule?',
                'answers' => ['a1' => 'Would', 'a2' => "hadn't suppressed"],
                'options' => [
                    'a1' => ['Would', 'Will', 'Does', 'Did'],
                    'a2' => ["hadn't suppressed", "didn't suppress", "don't suppress", "wouldn't suppress"],
                ],
                'verb_hints' => ['a1' => 'Use modal for counterfactual', 'a2' => 'suppress'],
                'hints' => [
                    'Формула: Would + subject + be, if + hadn\'t + V3?',
                    'Питання про вплив минулого придушення на теперішнє різноманіття.',
                    'Приклад: Would cultures be stronger if traditions hadn\'t been banned?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'If the urban infrastructure {a1} more pedestrian-friendly, traffic congestion {a2} significantly reduced during the last transportation crisis.',
                'answers' => ['a1' => 'were', 'a2' => 'would have been'],
                'options' => [
                    'a1' => ['were', 'is', 'was', 'had been'],
                    'a2' => ['would have been', 'would be', 'will be', 'was'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + were, would have been + V3.',
                    'Постійна пішохідна доступність і минуле зменшення заторів.',
                    'Приклад: If roads were wider, delays would have been shorter.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The pandemic response {a1} more coordinated last year if national health systems {a2} better integrated.',
                'answers' => ['a1' => 'would have been', 'a2' => 'were'],
                'options' => [
                    'a1' => ['would have been', 'would be', 'will be', 'was'],
                    'a2' => ['were', 'are', 'had been', 'would be'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: would have been + adj, if + were.',
                    'Постійна інтеграція систем і минула координація.',
                    'Приклад: The outbreak would have been contained if agencies were unified.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'If the electoral reforms {a1} enacted earlier, voter turnout {a2} substantially higher in the previous elections.',
                'answers' => ['a1' => 'had been', 'a2' => 'would have been'],
                'options' => [
                    'a1' => ['had been', 'were', 'are', 'would be'],
                    'a2' => ['would have been', 'would be', 'will be', 'was'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + had been + V3, would have been + adj.',
                    'Минулі реформи і минулі показники явки.',
                    'Приклад: If access had been easier, participation would have increased.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldHaveTagId, $type1MixedTagId],
            ],

            // ===== C2 Level: Additional 12 questions =====
            [
                'level' => 'C2',
                'question' => 'If the ontological presuppositions of post-structuralist thought {a1} more widely disseminated in the mid-twentieth century, contemporary critical theory {a2} markedly different today.',
                'answers' => ['a1' => 'had been', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['had been', 'were', 'are', 'would be'],
                    'a2' => ['would be', 'will be', 'is', 'was'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + had been + V3, would + base verb.',
                    'Минуле поширення філософських ідей і теперішній стан теорії.',
                    'Приклад: If ideas had spread, discourse would differ now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hermeneutical frameworks employed in biblical exegesis {a1} so contentious in academic circles today if the Reformation {a2} more pluralistic interpretive methodologies.',
                'answers' => ['a1' => "wouldn't be", 'a2' => 'had encouraged'],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "aren't", "weren't"],
                    'a2' => ['had encouraged', 'encouraged', 'encourages', 'would encourage'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'encourage'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + Past Perfect.',
                    'Минула Реформація і теперішні дебати в екзегетиці.',
                    'Приклад: Debates wouldn\'t exist if movements had embraced diversity.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the hegemonic discourse of neoliberal economics be less pervasive today if alternative economic paradigms {a2} more institutional support in the late twentieth century?',
                'answers' => ['a1' => 'Would', 'a2' => 'had received'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Does', 'Did'],
                    'a2' => ['had received', 'received', 'receive', 'would receive'],
                ],
                'verb_hints' => ['a1' => 'Use modal for counterfactual', 'a2' => 'receive'],
                'hints' => [
                    'Формула: Would + subject + be, if + Past Perfect?',
                    'Питання про вплив минулої підтримки альтернатив на теперішній дискурс.',
                    'Приклад: Would policies differ if theories had been promoted?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'If the epistemic foundations of Western jurisprudence {a1} more attuned to non-Western legal traditions, international law {a2} more equitably applied in colonial-era disputes.',
                'answers' => ['a1' => 'were', 'a2' => 'would have been'],
                'options' => [
                    'a1' => ['were', 'is', 'was', 'had been'],
                    'a2' => ['would have been', 'would be', 'will be', 'was'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + were, would have been + V3.',
                    'Постійна чутливість юриспруденції і минуле застосування права.',
                    'Приклад: If systems were inclusive, justice would have been fairer.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The phenomenological investigations into consciousness {a1} yielded more conclusive results in the early twentieth century if the scientific instrumentation {a2} more sophisticated.',
                'answers' => ['a1' => 'would have', 'a2' => 'were'],
                'options' => [
                    'a1' => ['would have', 'would', 'will', 'had'],
                    'a2' => ['were', 'is', 'had been', 'would be'],
                ],
                'verb_hints' => ['a1' => 'have', 'a2' => 'be'],
                'hints' => [
                    'Формула: would have + V3, if + were.',
                    'Постійна складність обладнання і минулі дослідження.',
                    'Приклад: Discoveries would have been made if tools were better.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'If the teleological assumptions underlying evolutionary theory {a1} less anthropocentric, the scientific community\'s understanding of biodiversity {a2} more nuanced today.',
                'answers' => ['a1' => 'were', 'a2' => 'would be'],
                'options' => [
                    'a1' => ['were', 'are', 'had been', 'would be'],
                    'a2' => ['would be', 'will be', 'is', 'was'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + were, would + base verb.',
                    'Постійні телеологічні припущення і теперішнє розуміння.',
                    'Приклад: If paradigms were different, knowledge would be broader now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'If the syncretic religious movements of late antiquity {a1} more thoroughly documented, contemporary scholars {a2} a richer understanding of early Christian theology today.',
                'answers' => ['a1' => 'had been', 'a2' => 'would have'],
                'options' => [
                    'a1' => ['had been', 'were', 'are', 'would be'],
                    'a2' => ['would have', 'will have', 'have', 'had'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'have'],
                'hints' => [
                    'Формула: If + had been + V3, would + base verb.',
                    'Минула документація і теперішнє розуміння теології.',
                    'Приклад: If records had survived, we would know more now.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The axiological foundations of utilitarian ethics {a1} so dominant in contemporary moral philosophy if deontological alternatives {a2} more persuasively articulated during the Enlightenment.',
                'answers' => ['a1' => "wouldn't be", 'a2' => 'had been'],
                'options' => [
                    'a1' => ["wouldn't be", "won't be", "aren't", "weren't"],
                    'a2' => ['had been', 'were', 'are', 'would be'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: wouldn\'t + base verb, if + had been + V3.',
                    'Минуле формулювання альтернатив і теперішнє домінування утилітаризму.',
                    'Приклад: One theory wouldn\'t dominate if others had been clearer.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the dialectical materialism of Marx have influenced political movements differently in the past century if the theoretical underpinnings {a2} more accessible to the proletariat?',
                'answers' => ['a1' => 'Would', 'a2' => 'were'],
                'options' => [
                    'a1' => ['Would', 'Will', 'Did', 'Had'],
                    'a2' => ['were', 'are', 'had been', 'would be'],
                ],
                'verb_hints' => ['a1' => 'Use modal for counterfactual', 'a2' => 'be'],
                'hints' => [
                    'Формула: Would + subject + have + V3, if + were?',
                    'Питання про вплив постійної доступності теорії на минулі рухи.',
                    'Приклад: Would revolutions have succeeded if ideas were simpler?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'If the cartographic representations of the pre-Columbian Americas {a1} more accurate, European colonization strategies {a2} significantly different.',
                'answers' => ['a1' => 'had been', 'a2' => 'would have been'],
                'options' => [
                    'a1' => ['had been', 'were', 'are', 'would be'],
                    'a2' => ['would have been', 'would be', 'will be', 'were'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + had been, would have been + adj.',
                    'Минула картографія і минулі стратегії колонізації.',
                    'Приклад: If maps had been precise, expeditions would have differed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldHaveTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The lexicographical documentation of endangered languages {a1} more comprehensive today if field linguists {a2} adequate funding throughout the twentieth century.',
                'answers' => ['a1' => 'would be', 'a2' => 'had received'],
                'options' => [
                    'a1' => ['would be', 'will be', 'is', 'was'],
                    'a2' => ['had received', 'received', 'receive', 'would receive'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'receive'],
                'hints' => [
                    'Формула: would + base verb, if + Past Perfect.',
                    'Минуле фінансування і теперішній стан документації.',
                    'Приклад: Records would be fuller if researchers had been supported.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $wouldTagId, $type1MixedTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'If the metaphysical commitments of logical positivism {a1} less dogmatic, the subsequent development of analytic philosophy {a2} more receptive to continental perspectives.',
                'answers' => ['a1' => 'were', 'a2' => 'would have been'],
                'options' => [
                    'a1' => ['were', 'are', 'had been', 'would be'],
                    'a2' => ['would have been', 'would be', 'will be', 'was'],
                ],
                'verb_hints' => ['a1' => 'be', 'a2' => 'be'],
                'hints' => [
                    'Формула: If + were, would have been + adj.',
                    'Постійна догматичність і минулий розвиток філософії.',
                    'Приклад: If views were open, dialogue would have flourished.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $wouldHaveTagId, $type2MixedTagId],
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
