<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ModalVerbsLeveledComprehensiveAiSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 6,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Verbs'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'AI: Modal Verbs Comprehensive Practice by Level'])->id;

        // Create theme tags
        $themeTagIds = $this->createThemeTags();
        
        // Create modal pair tags
        $modalTagIds = $this->createModalTags();
        
        // Create general modal tag
        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'English Grammar Theme']
        )->id;

        // Create fixed tag for corrected questions
        $fixedTagId = Tag::firstOrCreate(
            ['name' => 'fixed'],
            ['category' => 'Question Status']
        )->id;

        $questions = $this->getQuestionData();
        
        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $level = $entry['level'];
            $theme = $entry['theme'];
            $questionText = $entry['question'];
            $options = $entry['options'];
            $answerIndex = $entry['answer_index'];
            $verbHint = $entry['verb_hint'] ?? '';
            $isFixed = $entry['is_fixed'] ?? false;
            $correctionTags = $entry['correction_tags'] ?? [];
            
            $answer = $options[$answerIndex];
            
            // Build tag IDs
            $tagIds = [$modalsTagId];
            if (isset($themeTagIds[$theme])) {
                $tagIds[] = $themeTagIds[$theme];
            }
            
            // Determine modal tags based on answer
            $modalTags = $this->determineModalTags($answer, $options, $modalTagIds);
            $tagIds = array_merge($tagIds, $modalTags);
            
            // Add fixed tag and correction tags if this question was fixed
            if ($isFixed) {
                $tagIds[] = $fixedTagId;
                
                foreach ($correctionTags as $correctionTag) {
                    $correctionTagId = Tag::firstOrCreate(
                        ['name' => $correctionTag],
                        ['category' => 'Corrections']
                    )->id;
                    $tagIds[] = $correctionTagId;
                }
            }
            
            // Build comprehensive hints and explanations
            $hint = $this->buildHint($theme, $verbHint, $questionText, $level);
            $explanations = $this->buildExplanations($options, $answer, $theme, $questionText, $level);
            
            // Format example
            $example = $this->formatExample($questionText, $answer);
            
            $uuid = $this->generateQuestionUuid($level, $theme, $index + 1);
            
            $items[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$level] ?? 3,
                'source_id' => $sourceId,
                'flag' => 2,
                'type' => 0,
                'level' => $level,
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => $answer,
                        'verb_hint' => $this->normalizeHint($verbHint),
                    ],
                ],
                'options' => $options,
                'variants' => [],
            ];
            
            $meta[] = [
                'uuid' => $uuid,
                'answers' => ['a1' => $answer],
                'option_markers' => array_fill_keys($options, 'a1'),
                'hints' => ['a1' => $hint],
                'explanations' => $explanations,
            ];
        }
        
        $this->seedQuestionData($items, $meta);
    }

    private function createThemeTags(): array
    {
        $themes = [
            'Ability & Permission' => 'Modal Ability & Permission',
            'Requests & Offers' => 'Modal Requests & Offers',
            'Advice' => 'Modal Advice & Suggestions',
            'Obligation & Prohibition' => 'Modal Obligation & Prohibition',
            'Possibility' => 'Modal Possibility & Probability',
            'Offers' => 'Modal Offers',
            'Ability' => 'Modal Ability Focus',
            'Permission' => 'Modal Permission Focus',
            'Requests' => 'Modal Requests',
            'Deduction (Present)' => 'Modal Deduction (Present)',
            'Deduction (Past)' => 'Modal Deduction (Past)',
            'Obligation & Necessity' => 'Modal Obligation & Necessity',
            'Advice & Criticism' => 'Modal Advice & Criticism',
            'Possibility vs Ability' => 'Modal Possibility vs Ability',
            'Polite Requests' => 'Modal Polite Requests',
            'Past Possibility' => 'Modal Past Possibility',
            'Past Criticism' => 'Modal Past Criticism',
            'Degrees of Probability' => 'Modal Degrees of Probability',
            'Necessity vs Obligation' => 'Modal Necessity vs Obligation',
            'Habits & Hypotheticals' => 'Modal Habits & Hypotheticals',
            'Subtle Deduction' => 'Modal Subtle Deduction',
            'Nuanced Necessity' => 'Modal Nuanced Necessity',
            'Expectation' => 'Modal Expectation',
            'Formal Permission' => 'Modal Formal Permission',
            'Politeness' => 'Modal Politeness Degrees',
            'Speculation (Future)' => 'Modal Future Speculation',
            'Advanced Deduction' => 'Modal Advanced Deduction',
            'Epistemic vs Deontic' => 'Modal Epistemic vs Deontic',
            'Nuanced Necessity (Past)' => 'Modal Nuanced Necessity (Past)',
            'Register & Formality' => 'Modal Register & Formality',
            'Probability & Hedging' => 'Modal Probability & Hedging',
            'Hypotheticals (Past)' => 'Modal Hypotheticals (Past)',
        ];
        
        $tagIds = [];
        foreach ($themes as $key => $tagName) {
            $tagIds[$key] = Tag::firstOrCreate(
                ['name' => $tagName],
                ['category' => 'English Grammar Theme']
            )->id;
        }
        
        return $tagIds;
    }

    private function createModalTags(): array
    {
        $modalPairs = [
            'can_could' => 'Can / Could',
            'may_might' => 'May / Might',
            'must_have_to' => 'Must / Have to',
            'should_ought_to' => 'Should / Ought to',
            'will_would' => 'Will / Would',
            'shall' => 'Shall',
        ];
        
        $tagIds = [];
        foreach ($modalPairs as $key => $name) {
            $tagIds[$key] = Tag::firstOrCreate(
                ['name' => $name],
                ['category' => 'English Grammar Modal Pair']
            )->id;
        }
        
        return $tagIds;
    }

    private function determineModalTags(string $answer, array $options, array $modalTagIds): array
    {
        $tags = [];
        $answerLower = mb_strtolower(trim($answer));
        
        // Map modals to tag keys - ordered by specificity (longer strings first)
        $modalMap = [
            'can\'t' => 'can_could',
            'cannot' => 'can_could',
            'couldn\'t' => 'can_could',
            'could' => 'can_could',
            'can' => 'can_could',
            'may not' => 'may_might',
            'might not' => 'may_might',
            'might' => 'may_might',
            'may' => 'may_might',
            'mustn\'t' => 'must_have_to',
            'must' => 'must_have_to',
            'have to' => 'must_have_to',
            'has to' => 'must_have_to',
            'had to' => 'must_have_to',
            'shouldn\'t' => 'should_ought_to',
            'should' => 'should_ought_to',
            'ought to' => 'should_ought_to',
            'won\'t' => 'will_would',
            'wouldn\'t' => 'will_would',
            'would' => 'will_would',
            'will' => 'will_would',
            'shall' => 'shall',
        ];
        
        foreach ($modalMap as $modal => $tagKey) {
            if (str_contains($answerLower, $modal) && isset($modalTagIds[$tagKey])) {
                $tags[] = $modalTagIds[$tagKey];
                break;
            }
        }
        
        return array_values(array_unique($tags));
    }

    private function buildHint(string $theme, string $verbHint, string $question, string $level): string
    {
        $themeHints = [
            'Ability & Permission' => 'Модальні дієслова can і may допомагають виразити здатність або дозвіл. Can вживається для загальної здатності, а may — для ввічливого прохання дозволу.',
            'Requests & Offers' => 'Can і could використовуються для запитів та пропозицій. Can звучить простіше, could — більш ввічливо.',
            'Advice' => 'Should виражає пораду або рекомендацію. Це не суворий обов\'язок, а м\'яка пропозиція.',
            'Obligation & Prohibition' => 'Must означає сильний обов\'язок або заборону (mustn\'t). Must використовується для правил та необхідності.',
            'Possibility' => 'May і might показують можливість або ймовірність чогось. Вони виражають невпевненість про майбутнє або теперішнє.',
            'Offers' => 'Can використовується для дружніх пропозицій допомоги в повсякденному спілкуванні.',
            'Ability' => 'Can і could виражають здатність. Can — для теперішнього, could — для минулого або ввічливих запитів.',
            'Permission' => 'May і can використовуються для дозволів. May звучить більш формально, can — неформально.',
            'Deduction (Present)' => 'Must виражає сильну впевненість, can\'t — неможливість. Ці модальні дієслова допомагають робити логічні висновки про теперішнє.',
            'Deduction (Past)' => 'Must have, can\'t have, might have використовуються для висновків про минуле на основі доказів.',
            'Obligation & Necessity' => 'Have to виражає зовнішню необхідність, must — особисту впевненість у необхідності.',
            'Advice & Criticism' => 'Should виражає пораду, shouldn\'t have — критику минулої дії.',
            'Possibility vs Ability' => 'Could може виражати як можливість (залежно від умов), так і здатність.',
            'Polite Requests' => 'Could і would роблять запити більш ввічливими та делікатними.',
            'Past Possibility' => 'Could have показує можливість, яка не реалізувалася в минулому.',
            'Past Criticism' => 'Should have вказує на дію, яку слід було виконати, але не виконали.',
            'Degrees of Probability' => 'Might, may, could, must виражають різні ступені ймовірності від низької до високої.',
            'Necessity vs Obligation' => 'Needn\'t означає відсутність необхідності, на відміну від mustn\'t (заборона).',
            'Habits & Hypotheticals' => 'Would використовується для опису звичок у минулому та гіпотетичних ситуацій.',
            'Subtle Deduction' => 'Must і might have допомагають робити тонкі висновки про теперішнє та минуле.',
            'Nuanced Necessity' => 'Needn\'t виражає відсутність необхідності у теперішньому.',
            'Expectation' => 'Should виражає очікування або ймовірність події. Використовується для передбачень, що базуються на логіці або попередньому досвіді.',
            'Formal Permission' => 'May використовується у формальних контекстах для надання дозволу.',
            'Politeness' => 'Would, could, might роблять мову більш ввічливою та делікатною.',
            'Speculation (Future)' => 'Could і may вказують на можливість майбутніх подій.',
            'Advanced Deduction' => 'Must be і could have допомагають робити складні логічні висновки.',
            'Epistemic vs Deontic' => 'Might виражає епістемічну невпевненість, must — деонтичний обов\'язок.',
            'Nuanced Necessity (Past)' => 'Needn\'t have показує, що дія була непотрібною, але була виконана.',
            'Register & Formality' => 'Shall використовується у формальних контекстах та офіційних документах.',
            'Probability & Hedging' => 'Might well, could easily вказують на ймовірність з певним відтінком.',
            'Hypotheticals (Past)' => 'Could have, might have описують нереалізовані можливості минулого.',
        ];
        
        $baseHint = $themeHints[$theme] ?? 'Оберіть правильне модальне дієслово, яке найкраще підходить за змістом.';
        
        if ($verbHint) {
            $baseHint .= "\n\nВказівка: " . $verbHint;
        }
        
        return $baseHint;
    }

    private function buildExplanations(array $options, string $correctAnswer, string $theme, string $question, string $level): array
    {
        $explanations = [];
        
        foreach ($options as $option) {
            $isCorrect = ($option === $correctAnswer);
            
            if ($isCorrect) {
                $explanations[$option] = $this->buildCorrectExplanation($option, $theme, $question);
            } else {
                $explanations[$option] = $this->buildIncorrectExplanation($option, $correctAnswer, $theme, $question);
            }
        }
        
        return $explanations;
    }

    private function buildCorrectExplanation(string $option, string $theme, string $question): string
    {
        $example = $this->formatExample($question, $option);
        
        $explanations = [
            'can' => '✅ «can» правильно, бо виражає загальну здатність або можливість виконати дію.',
            'could' => '✅ «could» правильно, бо виражає здатність у минулому або ввічливий запит.',
            'may' => '✅ «may» правильно, бо виражає дозвіл або можливість у формальному контексті.',
            'might' => '✅ «might» правильно, бо виражає невпевнену можливість або ввічливу пропозицію.',
            'must' => '✅ «must» правильно, бо виражає сильний обов\'язок або логічний висновок.',
            'mustn\'t' => '✅ «mustn\'t» правильно, бо виражає сувору заборону.',
            'should' => '✅ «should» правильно, бо виражає пораду або рекомендацію.',
            'shouldn\'t' => '✅ «shouldn\'t» правильно, бо радить не робити щось.',
            'will' => '✅ «will» правильно, бо виражає майбутню дію або рішення.',
            'would' => '✅ «would» правильно, бо виражає ввічливість або гіпотетичну ситуацію.',
        ];
        
        $optionLower = mb_strtolower(trim($option));
        $baseExplanation = $explanations[$optionLower] ?? "✅ «{$option}» правильно для даного контексту.";
        
        return $baseExplanation . "\nПриклад: *$example*";
    }

    private function buildIncorrectExplanation(string $option, string $correctAnswer, string $theme, string $question): string
    {
        $example = $this->formatExample($question, $correctAnswer);
        
        $incorrectReasons = [
            'can' => 'може виражати здатність, але тут потрібно інше значення',
            'could' => 'виражає можливість або минулу здатність, але не підходить у цьому контексті',
            'may' => 'надто формальне для цієї ситуації',
            'might' => 'виражає слабку можливість, але тут потрібна інша форма',
            'must' => 'виражає сильний обов\'язок, що не відповідає контексту',
            'mustn\'t' => 'означає заборону, а не те, що потрібно',
            'should' => 'є лише порадою, а не тим, що потрібно в цьому реченні',
            'shouldn\'t' => 'виражає пораду не робити щось, але контекст інший',
            'will' => 'виражає майбутнє, але тут потрібно інше модальне дієслово',
            'would' => 'виражає умовність або ввічливість, що не відповідає контексту',
        ];
        
        $optionLower = mb_strtolower(trim($option));
        $reason = $incorrectReasons[$optionLower] ?? 'не підходить за змістом';
        
        return "❌ «{$option}» $reason.\nПравильна відповідь: *$example*";
    }

    private function getQuestionData(): array
    {
        return [
            // -------------------------
            // A1 — 13 питань
            // -------------------------

            // Ability & Permission (can / can't)
            [
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'question' => 'Choose the best option: I {a1} swim.',
                'options' => ['can', 'must', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'basic ability; very common, simple form',
            ],
            [
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'question' => 'Choose the best option: {a1} I sit here?',
                'options' => ['Can', 'Must', 'Should', 'Have to'],
                'answer_index' => 0,
                'verb_hint' => 'informal asking for permission',
            ],
            [
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'question' => 'Choose the best option: She {a1} ride a bike.',
                'options' => ['can', 'mustn\'t', 'shouldn\'t', 'has to'],
                'answer_index' => 0,
                'verb_hint' => 'ability; positive form',
            ],

            // Requests & Offers (can / could)
            [
                'level' => 'A1',
                'theme' => 'Requests & Offers',
                'question' => 'Choose the best option: {a1} you help me, please?',
                'options' => ['Can', 'Must', 'Should', 'May'],
                'answer_index' => 0,
                'verb_hint' => 'simple polite request (not strong form)',
            ],
            [
                'level' => 'A1',
                'theme' => 'Requests & Offers',
                'question' => 'Choose the best option: {a1} I open the window?',
                'options' => ['Can', 'Have to', 'Should', 'Must'],
                'answer_index' => 0,
                'verb_hint' => 'asking permission in a friendly way',
            ],

            // Advice (should)
            [
                'level' => 'A1',
                'theme' => 'Advice',
                'question' => 'Choose the best option: You {a1} drink more water.',
                'options' => ['should', 'must', 'can', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'mild advice, not a rule',
            ],
            [
                'level' => 'A1',
                'theme' => 'Advice',
                'question' => 'Choose the best option: He {a1} see a doctor.',
                'options' => ['should', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'recommendation, not obligation',
            ],

            // Obligation & Prohibition (must, mustn't)
            [
                'level' => 'A1',
                'theme' => 'Obligation & Prohibition',
                'question' => 'Choose the best option: You {a1} stop at a red light.',
                'options' => ['must', 'can', 'could', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'strong rule/necessity',
            ],
            [
                'level' => 'A1',
                'theme' => 'Obligation & Prohibition',
                'question' => 'Choose the best option: You {a1} smoke here.',
                'options' => ['mustn\'t', 'should', 'may', 'can'],
                'answer_index' => 0,
                'verb_hint' => 'prohibition; not allowed',
            ],

            // Possibility (may / might)
            [
                'level' => 'A1',
                'theme' => 'Possibility',
                'question' => 'Choose the best option: It {a1} rain later.',
                'options' => ['may', 'must', 'should', 'can'],
                'answer_index' => 0,
                'verb_hint' => 'weak possibility about the future',
            ],
            [
                'level' => 'A1',
                'theme' => 'Possibility',
                'question' => 'Choose the best option: She {a1} be at home now.',
                'options' => ['might', 'mustn\'t', 'can', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'maybe true now, not certain',
            ],

            // Polite Offers (can / could)
            [
                'level' => 'A1',
                'theme' => 'Offers',
                'question' => 'Choose the best option: {a1} I carry your bag?',
                'options' => ['Can', 'Must', 'Should', 'May not'],
                'answer_index' => 0,
                'verb_hint' => 'friendly offer, everyday English',
            ],
            [
                'level' => 'A1',
                'theme' => 'Offers',
                'question' => 'Choose the best option: {a1} I help you with that?',
                'options' => ['Can', 'Have to', 'Must', 'Ought to'],
                'answer_index' => 0,
                'verb_hint' => 'simple offer of help',
            ],


            // -------------------------
            // A2 — 12 питань
            // -------------------------

            // Ability & Past Ability (can / could)
            [
                'level' => 'A2',
                'theme' => 'Ability',
                'question' => 'Choose the best option: When I was ten, I {a1} run very fast.',
                'options' => ['could', 'can', 'must', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'past ability form',
            ],
            [
                'level' => 'A2',
                'theme' => 'Ability',
                'question' => 'Choose the best option: Sorry, I {a1} come tomorrow — I\'m busy.',
                'options' => ['can\'t', 'must', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'negative present ability/availability',
            ],

            // Permission (may / can)
            [
                'level' => 'A2',
                'theme' => 'Permission',
                'question' => 'Choose the best option: {a1} I leave early today?',
                'options' => ['May', 'Must', 'Should', 'Have to'],
                'answer_index' => 0,
                'verb_hint' => 'more formal than a basic request',
            ],
            [
                'level' => 'A2',
                'theme' => 'Permission',
                'question' => 'Choose the best option: You {a1} use my laptop if you want.',
                'options' => ['can', 'must', 'shouldn\'t', 'ought to'],
                'answer_index' => 0,
                'verb_hint' => 'giving permission informally',
            ],

            // Advice & Suggestion (should / shouldn't / ought to)
            [
                'level' => 'A2',
                'theme' => 'Advice',
                'question' => 'Choose the best option: You {a1} take a break if you feel tired.',
                'options' => ['should', 'must', 'can', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'friendly recommendation',
            ],
            [
                'level' => 'A2',
                'theme' => 'Advice',
                'question' => 'Choose the best option: He {a1} eat so much sugar.',
                'options' => ['shouldn\'t', 'mustn\'t', 'can\'t', 'may not'],
                'answer_index' => 0,
                'verb_hint' => 'soft advice not to do something',
            ],

            // Obligation (must / have to)
            [
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'question' => 'Choose the best option: I {a1} finish this report by Friday.',
                'options' => ['have to', 'can', 'might', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'external requirement/deadline',
            ],
            [
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'question' => 'Choose the best option: You {a1} wear a helmet on a motorbike.',
                'options' => ['must', 'may', 'could', 'shouldn\'t'],
                'answer_index' => 0,
                'verb_hint' => 'strong rule, safety',
            ],

            // Possibility & Probability (may / might / could)
            [
                'level' => 'A2',
                'theme' => 'Possibility',
                'question' => 'Choose the best option: They {a1} arrive late because of traffic.',
                'options' => ['might', 'must', 'should', 'have to'],
                'answer_index' => 0,
                'verb_hint' => 'uncertain future event',
            ],
            [
                'level' => 'A2',
                'theme' => 'Possibility',
                'question' => 'Choose the best option: It {a1} be cold tonight. Take a jacket.',
                'options' => ['could', 'must', 'should', 'can\'t'],
                'answer_index' => 0,
                'verb_hint' => 'real possibility but not certain',
            ],

            // Requests (could / would)
            [
                'level' => 'A2',
                'theme' => 'Requests',
                'question' => 'Choose the best option: {a1} you pass the salt, please?',
                'options' => ['Could', 'Must', 'May', 'Should'],
                'answer_index' => 0,
                'verb_hint' => 'more polite than a basic request',
            ],
            [
                'level' => 'A2',
                'theme' => 'Requests',
                'question' => 'Choose the best option: {a1} you mind opening the door?',
                'options' => ['Would', 'Must', 'Can', 'Ought to'],
                'answer_index' => 0,
                'verb_hint' => 'very polite request starter',
            ],


            // -------------------------
            // B1 — 12 питань
            // -------------------------

            // Deduction (present): must / can't
            [
                'level' => 'B1',
                'theme' => 'Deduction (Present)',
                'question' => 'Choose the best option: The lights are off. They {a1} be at home.',
                'options' => ['can\'t', 'must', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'negative strong deduction about now',
            ],
            [
                'level' => 'B1',
                'theme' => 'Deduction (Present)',
                'question' => 'Choose the best option: She studied all day. She {a1} be tired.',
                'options' => ['must', 'might', 'can', 'shouldn\'t'],
                'answer_index' => 0,
                'verb_hint' => 'very probable conclusion now',
            ],

            // Obligation & Necessity (have to / must)
            [
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'question' => 'Choose the best option: We {a1} submit the application online.',
                'options' => ['have to', 'might', 'could', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'required by rules/conditions',
            ],
            [
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'question' => 'Choose the best option: You {a1} bring your ID to the exam.',
                'options' => ['must', 'may', 'could', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'non-negotiable requirement',
            ],

            // Advice & Criticism (should / shouldn't / ought to)
            [
                'level' => 'B1',
                'theme' => 'Advice & Criticism',
                'question' => 'Choose the best option: You {a1} back up your files regularly.',
                'options' => ['should', 'mustn\'t', 'can\'t', 'may not'],
                'answer_index' => 0,
                'verb_hint' => 'good practice, recommendation',
            ],
            [
                'level' => 'B1',
                'theme' => 'Advice & Criticism',
                'question' => 'Choose the best option: He {a1} have spoken to her like that.',
                'options' => ['shouldn\'t', 'must', 'can', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'mild criticism about his action',
            ],

            // Possibility vs Ability (could / can)
            [
                'level' => 'B1',
                'theme' => 'Possibility vs Ability',
                'question' => 'Choose the best option: We {a1} go hiking this weekend if the weather is good.',
                'options' => ['could', 'can', 'must', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'conditional possibility, not skill',
            ],
            [
                'level' => 'B1',
                'theme' => 'Possibility vs Ability',
                'question' => 'Choose the best option: She {a1} speak three languages.',
                'options' => ['can', 'may', 'might', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'general ability, present',
            ],

            // Polite Requests & Offers (would / could)
            [
                'level' => 'B1',
                'theme' => 'Polite Requests',
                'question' => 'Choose the best option: {a1} you like some coffee?',
                'options' => ['Would', 'Must', 'May', 'Should'],
                'answer_index' => 0,
                'verb_hint' => 'polite offer structure',
            ],
            [
                'level' => 'B1',
                'theme' => 'Polite Requests',
                'question' => 'Choose the best option: {a1} you possibly email me the details?',
                'options' => ['Could', 'Must', 'Can\'t', 'Shouldn\'t'],
                'answer_index' => 0,
                'verb_hint' => 'polite/request with softener',
            ],

            // B1 - 2 more questions to complete 12
            [
                'level' => 'B1',
                'theme' => 'Ability',
                'question' => 'Choose the best option: I {a1} swim when I was five years old.',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'past ability',
            ],
            [
                'level' => 'B1',
                'theme' => 'Permission',
                'question' => 'Choose the best option: {a1} I borrow your pen?',
                'options' => ['May', 'Must', 'Should', 'Have to'],
                'answer_index' => 0,
                'verb_hint' => 'polite permission request',
            ],


            // -------------------------
            // B2 — 12 питань
            // -------------------------

            // Past Possibility & Criticism (could have / should have)
            [
                'level' => 'B2',
                'theme' => 'Past Possibility',
                'question' => 'Choose the best option: We {a1} left earlier to avoid traffic.',
                'options' => ['could have', 'must have', 'may', 'can\'t have'],
                'answer_index' => 0,
                'verb_hint' => 'missed chance in the past',
            ],
            [
                'level' => 'B2',
                'theme' => 'Past Criticism',
                'question' => 'Choose the best option: You {a1} told me about the change.',
                'options' => ['should have', 'might have', 'must', 'can'],
                'answer_index' => 0,
                'verb_hint' => 'regret/criticism about past inaction',
            ],

            // Past Deduction (must have / can't have / might have)
            [
                'level' => 'B2',
                'theme' => 'Deduction (Past)',
                'question' => 'Choose the best option: The ground is wet. It {a1} rained last night.',
                'options' => ['must have', 'should have', 'can\'t have', 'would have'],
                'answer_index' => 0,
                'verb_hint' => 'strong conclusion about the past',
            ],
            [
                'level' => 'B2',
                'theme' => 'Deduction (Past)',
                'question' => 'Choose the best option: He looks surprised — he {a1} known about it.',
                'options' => ['can\'t have', 'must have', 'should have', 'could have'],
                'answer_index' => 0,
                'verb_hint' => 'negative deduction about past knowledge',
            ],

            // Degrees of Probability (may / might / could / must)
            [
                'level' => 'B2',
                'theme' => 'Degrees of Probability',
                'question' => 'Choose the best option: They {a1} be late; the train is often delayed.',
                'options' => ['might', 'must', 'should', 'have to'],
                'answer_index' => 0,
                'verb_hint' => 'medium-low probability',
            ],
            [
                'level' => 'B2',
                'theme' => 'Degrees of Probability',
                'question' => 'Choose the best option: She studied all week; she {a1} pass the exam.',
                'options' => ['should', 'might', 'can', 'may not'],
                'answer_index' => 0,
                'verb_hint' => 'expected positive outcome',
            ],

            // Necessity vs Obligation (need to / have to / must)
            [
                'level' => 'B2',
                'theme' => 'Necessity vs Obligation',
                'question' => 'Choose the best option: You {a1} show your passport at the border.',
                'options' => ['have to', 'needn\'t', 'might', 'ought to'],
                'answer_index' => 0,
                'verb_hint' => 'external rule, not personal insistence',
            ],
            [
                'level' => 'B2',
                'theme' => 'Necessity vs Obligation',
                'question' => 'Choose the best option: You {a1} bring snacks; there will be food.',
                'options' => ['needn\'t', 'must', 'have to', 'should have'],
                'answer_index' => 0,
                'verb_hint' => 'no necessity required',
            ],

            // Habits & Hypotheticals (would)
            [
                'level' => 'B2',
                'theme' => 'Habits & Hypotheticals',
                'question' => 'Choose the best option: When we were kids, we {a1} play outside for hours.',
                'options' => ['would', 'might', 'should', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'past repeated habit (not used to)',
            ],
            [
                'level' => 'B2',
                'theme' => 'Habits & Hypotheticals',
                'question' => 'Choose the best option: I {a1} help if I had more time.',
                'options' => ['would', 'could have', 'must have', 'shouldn\'t'],
                'answer_index' => 0,
                'verb_hint' => 'imagined condition; polite willingness',
            ],

            // B2 - 2 more questions to complete 12
            [
                'level' => 'B2',
                'theme' => 'Advice',
                'question' => 'Choose the best option: You {a1} have checked the instructions before starting.',
                'options' => ['should', 'must', 'can', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'past advice/regret',
            ],
            [
                'level' => 'B2',
                'theme' => 'Expectation',
                'question' => 'Choose the best option: By next year, I {a1} speak fluent Spanish.',
                'options' => ['should', 'must', 'may', 'can'],
                'answer_index' => 0,
                'verb_hint' => 'future expectation (probability)',
                'is_fixed' => true,
                'correction_tags' => ['Ability -> Expectation (theme)', 'future expectation/ability -> future expectation (probability) (verb_hint)'],
            ],


            // -------------------------
            // C1 — 12 питань
            // -------------------------

            // Subtle Deduction (present & past)
            [
                'level' => 'C1',
                'theme' => 'Subtle Deduction',
                'question' => 'Choose the best option: That {a1} be the new manager — everyone\'s greeting her.',
                'options' => ['must', 'might', 'can', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'highly likely present conclusion',
            ],
            [
                'level' => 'C1',
                'theme' => 'Subtle Deduction',
                'question' => 'Choose the best option: He {a1} forgotten your email; try resending.',
                'options' => ['might have', 'must have', 'could', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'uncertain past inference',
            ],

            // Nuanced Necessity (needn't have / didn't need to)
            [
                'level' => 'C1',
                'theme' => 'Nuanced Necessity',
                'question' => 'Choose the best option: You {a1} have brought flowers — but thank you!',
                'options' => ['needn\'t', 'mustn\'t', 'can\'t', 'ought to'],
                'answer_index' => 0,
                'verb_hint' => 'lack of necessity (present)',
            ],
            [
                'level' => 'C1',
                'theme' => 'Nuanced Necessity',
                'question' => 'Choose the best option: We {a1} have rushed; the train was delayed anyway.',
                'options' => ['needn\'t', 'must', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'no necessity in hindsight',
            ],

            // Formal Permission & Expectation (be supposed to / may)
            [
                'level' => 'C1',
                'theme' => 'Expectation',
                'question' => 'Choose the best option: You\'re {a1} to submit the form by midnight.',
                'options' => ['supposed', 'must', 'can', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'expected/required by arrangement',
            ],
            [
                'level' => 'C1',
                'theme' => 'Formal Permission',
                'question' => 'Choose the best option: Candidates {a1} be accompanied by an adult.',
                'options' => ['may', 'can', 'should', 'might have'],
                'answer_index' => 0,
                'verb_hint' => 'formal allowance wording',
            ],

            // Degrees of Politeness (would / could / might)
            [
                'level' => 'C1',
                'theme' => 'Politeness',
                'question' => 'Choose the best option: {a1} you be able to forward the document today?',
                'options' => ['Would', 'Must', 'Shall', 'Ought to'],
                'answer_index' => 0,
                'verb_hint' => 'elevated, deferential request opening',
            ],
            [
                'level' => 'C1',
                'theme' => 'Politeness',
                'question' => 'Choose the best option: {a1} I possibly have a look at your notes?',
                'options' => ['Might', 'Must', 'Should', 'Need to'],
                'answer_index' => 0,
                'verb_hint' => 'very polite, tentative permission request',
            ],

            // Speculation about Future (may well / could easily)
            [
                'level' => 'C1',
                'theme' => 'Speculation (Future)',
                'question' => 'Choose the best option: The plan {a1} work if we secure funding.',
                'options' => ['could', 'mustn\'t', 'needn\'t', 'shouldn\'t'],
                'answer_index' => 0,
                'verb_hint' => 'realistic possibility without certainty',
            ],
            [
                'level' => 'C1',
                'theme' => 'Speculation (Future)',
                'question' => 'Choose the best option: She {a1} well decide to join us.',
                'options' => ['may', 'must', 'can\'t', 'ought to'],
                'answer_index' => 0,
                'verb_hint' => 'collocation with "well" for likelihood',
            ],

            // C1 - 2 more questions to complete 12
            [
                'level' => 'C1',
                'theme' => 'Deduction (Past)',
                'question' => 'Choose the best option: They {a1} have arrived by now; the flight landed an hour ago.',
                'options' => ['should', 'might', 'can', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'logical expectation about past',
            ],
            [
                'level' => 'C1',
                'theme' => 'Obligation & Necessity',
                'question' => 'Choose the best option: All participants {a1} register before Friday.',
                'options' => ['must', 'might', 'could', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'formal requirement',
            ],


            // -------------------------
            // C2 — 12 питань
            // -------------------------

            // Fine-grained Deduction & Stance
            [
                'level' => 'C2',
                'theme' => 'Advanced Deduction',
                'question' => 'Choose the best option: Given the evidence, the committee {a1} have reached a different conclusion.',
                'options' => ['could', 'must', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'counterfactual possibility in hindsight',
            ],
            [
                'level' => 'C2',
                'theme' => 'Advanced Deduction',
                'question' => 'Choose the best option: He {a1} be unaware of the latest findings; his argument ignores them.',
                'options' => ['must', 'might', 'can', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'high-certainty present inference',
            ],

            // Epistemic vs Deontic Contrast
            [
                'level' => 'C2',
                'theme' => 'Epistemic vs Deontic',
                'question' => 'Choose the best option: You {a1} think this is obvious, but it isn\'t.',
                'options' => ['might', 'must', 'can\'t', 'shouldn\'t'],
                'answer_index' => 0,
                'verb_hint' => 'hedged opinion, low commitment',
            ],
            [
                'level' => 'C2',
                'theme' => 'Epistemic vs Deontic',
                'question' => 'Choose the best option: Researchers {a1} obtain consent before collecting data.',
                'options' => ['must', 'may', 'might', 'could have'],
                'answer_index' => 0,
                'verb_hint' => 'non-negotiable ethical obligation',
            ],

            // Nuanced Necessity & Absence (needn't have / didn't need to)
            [
                'level' => 'C2',
                'theme' => 'Nuanced Necessity (Past)',
                'question' => 'Choose the best option: We {a1} have printed the reports; a digital copy was enough.',
                'options' => ['needn\'t', 'mustn\'t', 'should have', 'could'],
                'answer_index' => 0,
                'verb_hint' => 'unnecessary action in retrospect',
            ],
            [
                'level' => 'C2',
                'theme' => 'Nuanced Necessity (Past)',
                'question' => 'Choose the best option: She {a1} have apologized; no harm was done.',
                'options' => ['needn\'t', 'should', 'must', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'lack of obligation after the fact',
            ],

            // Register & Formality (shall / may / would)
            [
                'level' => 'C2',
                'theme' => 'Register & Formality',
                'question' => 'Choose the best option: {a1} we proceed to the next item on the agenda?',
                'options' => ['Shall', 'Must', 'May', 'Should'],
                'answer_index' => 0,
                'verb_hint' => 'formal proposal/question in meetings',
            ],
            [
                'level' => 'C2',
                'theme' => 'Register & Formality',
                'question' => 'Choose the best option: Documents {a1} be submitted no later than Thursday.',
                'options' => ['shall', 'might', 'could', 'should have'],
                'answer_index' => 0,
                'verb_hint' => 'formal requirement in official text',
            ],

            // Subtle Probability & Hedging (might well / could easily / is likely to)
            [
                'level' => 'C2',
                'theme' => 'Probability & Hedging',
                'question' => 'Choose the best option: The policy {a1} well face legal challenges.',
                'options' => ['might', 'must', 'can\'t', 'shouldn\'t'],
                'answer_index' => 0,
                'verb_hint' => 'collocation signalling plausible likelihood',
            ],
            [
                'level' => 'C2',
                'theme' => 'Probability & Hedging',
                'question' => 'Choose the best option: This approach {a1} easily be adapted to other contexts.',
                'options' => ['could', 'mustn\'t', 'may not', 'should have'],
                'answer_index' => 0,
                'verb_hint' => 'practical possibility with minimal effort',
            ],

            // Hypothetical Reasoning (would have / could have / might have)
            [
                'level' => 'C2',
                'theme' => 'Hypotheticals (Past)',
                'question' => 'Choose the best option: With better planning, they {a1} avoided the delay.',
                'options' => ['could have', 'must have', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'unrealized feasible past outcome',
            ],
            [
                'level' => 'C2',
                'theme' => 'Hypotheticals (Past)',
                'question' => 'Choose the best option: Had we known the risks, we {a1} chosen a different strategy.',
                'options' => ['might have', 'must', 'can\'t have', 'shouldn\'t'],
                'answer_index' => 0,
                'verb_hint' => 'counterfactual decision-making',
            ],
        ];
    }
}
