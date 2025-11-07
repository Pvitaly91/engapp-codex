<?php

namespace Database\Seeders\AI\modals;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ModalVerbsSubthemesLevelComparisonAiSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'AI: Modal Verbs Subthemes Level Comparison'])->id;

        $themeTagIds = $this->createThemeTags();
        $modalTagIds = $this->createModalTags();
        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'English Grammar Theme']
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
            $concept = $entry['concept'];
            $exampleHint = $entry['example_hint'];

            $answer = $options[$answerIndex];

            $tagIds = [$modalsTagId];
            if (isset($themeTagIds[$theme])) {
                $tagIds[] = $themeTagIds[$theme];
            }

            $modalTags = $this->determineModalTags($answer, $options, $modalTagIds);
            $tagIds = array_merge($tagIds, $modalTags);

            $hint = $this->buildHint($theme, $concept, $verbHint, $exampleHint);
            $explanations = $this->buildExplanations($options, $answer, $concept, $questionText);

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
            'Ability & Permission' => 'Modal Ability & Permission Comparison',
            'Obligation & Necessity' => 'Modal Obligation & Necessity Comparison',
            'Possibility & Deduction' => 'Modal Possibility & Deduction Comparison',
        ];

        $tagIds = [];
        foreach ($themes as $key => $name) {
            $tagIds[$key] = Tag::firstOrCreate(
                ['name' => $name],
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
            'need' => 'Need / Needn\'t',
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

        $modalMap = [
            "can't" => 'can_could',
            'cannot' => 'can_could',
            'can' => 'can_could',
            'could' => 'can_could',
            "couldn't" => 'can_could',
            'may' => 'may_might',
            'might' => 'may_might',
            "mightn't" => 'may_might',
            'must' => 'must_have_to',
            "mustn't" => 'must_have_to',
            'have to' => 'must_have_to',
            'has to' => 'must_have_to',
            'had to' => 'must_have_to',
            'should' => 'should_ought_to',
            "shouldn't" => 'should_ought_to',
            'ought to' => 'should_ought_to',
            'would' => 'will_would',
            'will' => 'will_would',
            "wouldn't" => 'will_would',
            'shall' => 'shall',
            'need to' => 'need',
            "don't need to" => 'need',
            "needn't" => 'need',
        ];

        foreach ($modalMap as $modal => $tagKey) {
            if (str_contains($answerLower, $modal) && isset($modalTagIds[$tagKey])) {
                $tags[] = $modalTagIds[$tagKey];
            }
        }

        return array_values(array_unique($tags));
    }

    private function buildHint(string $theme, string $concept, string $verbHint, string $exampleHint): string
    {
        $conceptHints = [
            'present_ability' => 'Мова йде про теперішню навичку або можливість. Обери модальне слово, що передає просту здібність.',
            'asking_permission' => 'Зверни увагу на ситуацію прохання дозволу. Потрібне ввічливе модальне слово, що не звучить як наказ.',
            'lack_permission' => 'Контекст натякає на заборону. Вибери форму, що прямо показує відсутність дозволу.',
            'past_ability' => 'Речення описує минулий досвід. Потрібна форма, що демонструє здібність у минулому.',
            'conditional_ability' => 'Розглянь умовний характер дії: здібність можлива за певної умови.',
            'formal_permission' => 'Використай модальне слово для формального дозволу або офіційної ситуації.',
            'external_obligation' => 'Йдеться про правило чи зовнішню вимогу. Обери модальне слово, що вказує на необхідність.',
            'personal_duty' => 'Ця ситуація підкреслює особистий обов\'язок або моральний імператив.',
            'absence_of_obligation' => 'Контекст показує, що дія не є обов\'язковою. Знайди форму, яка знімає необхідність.',
            'future_requirement' => 'Речення говорить про вимогу, що може настати. Вибери форму, що передає неминучість.',
            'strong_deduction' => 'Є чіткі підказки для впевненого висновку. Обери модальне слово з високою впевненістю.',
            'tentative_possibility' => 'Контекст лише припускає варіант розвитку. Потрібна форма зі слабкою впевненістю.',
            'logical_conclusion_past' => 'Речення описує минуле і просить зробити логічний висновок.',
            'speculative_future' => 'Зверни увагу на майбутнє припущення. Потрібне модальне дієслово для обережного прогнозу.',
            'contradicting_evidence' => 'Є докази, які заперечують можливість. Вибери форму для неможливості.',
            'probable_consequence' => 'Ситуація вказує на наслідок, який майже гарантований.',
        ];

        $base = $conceptHints[$concept] ?? 'Зосередься на значенні модального дієслова у цьому реченні.';

        if ($verbHint) {
            $base .= "\n\nПідказка щодо дієслова: " . $verbHint;
        }

        $base .= "\n\nПриклад: " . $exampleHint;

        return $base;
    }

    private function buildExplanations(array $options, string $answer, string $concept, string $question): array
    {
        $explanations = [];

        foreach ($options as $option) {
            if ($option === $answer) {
                $explanations[$option] = $this->buildCorrectExplanation($concept, $question);
            } else {
                $explanations[$option] = $this->buildIncorrectExplanation($option, $concept, $question);
            }
        }

        return $explanations;
    }

    private function buildCorrectExplanation(string $concept, string $question): string
    {
        $conceptExplanations = [
            'present_ability' => 'Правильний вибір описує наявну навичку, тому він доречний у цій простій ситуації.',
            'asking_permission' => 'Доречний варіант ставить запитання про дозвіл ввічливо й без тиску.',
            'lack_permission' => 'Правильна форма прямо передає заборону, що відповідає ситуації.',
            'past_ability' => 'Правильний варіант описує здібність у минулому, що і потрібно в реченні.',
            'conditional_ability' => 'Коректний вибір передає умовну можливість за певних обставин.',
            'formal_permission' => 'Правильна форма звучить формально й показує надання дозволу.',
            'external_obligation' => 'Потрібний варіант відображає правило або зовнішню вимогу.',
            'personal_duty' => 'Доречний вибір натякає на моральний або внутрішній обов\'язок.',
            'absence_of_obligation' => 'Правильна форма показує, що дія не є обов\'язковою.',
            'future_requirement' => 'Потрібний варіант підкреслює майбутню необхідність, яку складно уникнути.',
            'strong_deduction' => 'Правильний варіант робить упевнений логічний висновок на основі фактів.',
            'tentative_possibility' => 'Коректний вибір лише припускає, не обіцяючи результат.',
            'logical_conclusion_past' => 'Потрібна форма пояснює минуле через логічний висновок.',
            'speculative_future' => 'Правильний варіант прогнозує можливий розвиток з обережністю.',
            'contradicting_evidence' => 'Доречна форма виражає неможливість через наявні факти.',
            'probable_consequence' => 'Коректний вибір показує наслідок, який майже неминучий.',
        ];

        $base = $conceptExplanations[$concept] ?? 'Правильний варіант найкраще відповідає контексту речення.';

        return $base . "\nКонтекст: " . $question;
    }

    private function buildIncorrectExplanation(string $option, string $concept, string $question): string
    {
        $option = mb_strtolower($option);

        $reasons = [
            'can' => 'передає звичайну здатність і не відображає потрібного відтінку.',
            "can't" => 'виражає заборону, що не узгоджується з ідеєю речення.',
            'could' => 'натякає на минулий або умовний контекст, який тут не підходить.',
            "couldn't" => 'звучить як відсутність можливості, а ситуація вимагає іншого значення.',
            'may' => 'занадто формально або невпевнено для даної ситуації.',
            'might' => 'виражає лише припущення, а контекст потребує іншого смислу.',
            'must' => 'передає суворий обов\'язок, що не збігається з реченням.',
            "mustn't" => 'означає заборону, якої немає в реченні.',
            'have to' => 'натякає на зовнішній примус, який тут не згадується.',
            "has to" => 'підкреслює зовнішню вимогу, не властиву ситуації.',
            'should' => 'дає пораду, а не те, що потрібно сказати.',
            "shouldn't" => 'рекомендує уникати дії, хоча текст цього не потребує.',
            'would' => 'описує гіпотетичні сценарії, які не відповідають контексту.',
            "wouldn't" => 'передає відмову, що не підтримується фактами речення.',
            'shall' => 'звучить надто офіційно і не пасує сюжету.',
            'need to' => 'означає необхідність, якої не згадано.',
            "don't need to" => 'знімає обов\'язок, хоча текст говорить про інше.',
            "needn't" => 'показує відсутність потреби, що суперечить змісту.',
            'ought to' => 'звучить як моральна порада, а не як потрібний сенс.',
        ];

        $reason = $reasons[$option] ?? 'не підходить до смислу речення.';

        return 'Цей варіант ' . $reason . "\nКонтекст: " . $question;
    }

    private function getQuestionData(): array
    {
        $datasets = [
            'Ability & Permission' => [
                'A1' => [
                    [
                        'question' => 'Обери правильний варіант: I {a1} ride a bike.',
                        'options' => ['can', 'must', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'express ability; base form',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: «Дитина має навичку читати швидко» — добери слово, що показує вміння.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: {a1} I sit here?',
                        'options' => ['Can', 'Must', 'Should', 'Need to'],
                        'answer_index' => 0,
                        'verb_hint' => 'simple permission question',
                        'concept' => 'asking_permission',
                        'example_hint' => 'Наприклад: «Учень запитує, чи дозволено відкрити вікно» — потрібна м’яка форма.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: She {a1} play the piano very well.',
                        'options' => ['can', 'must', 'might', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'describing a present skill',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: «Вона має талант малювати» — обери форму для здібності.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Teacher, {a1} we leave early?',
                        'options' => ['May', 'Must', 'Would', 'Shall'],
                        'answer_index' => 0,
                        'verb_hint' => 'formal permission request',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Учень офіційно питає про дозвіл піти додому раніше» — потрібна ввічлива форма.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: You {a1} bring pets into the library.',
                        'options' => ["can't", 'should', 'might', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'rules against permission',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «На дверях висить табличка про заборону їжі» — обери форму, що показує заборону.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: He {a1} swim yet.',
                        'options' => ["can't", 'may', 'must', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'missing skill right now',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «Малюк ще не навчився кататися на велосипеді» — потрібна форма відсутності навички.',
                    ],
                ],
                'A2' => [
                    [
                        'question' => 'Обери правильний варіант: When we were kids, we {a1} stay up late on Fridays.',
                        'options' => ['could', 'can', 'must', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'past ability; informal',
                        'concept' => 'past_ability',
                        'example_hint' => 'Наприклад: «У дитинстві ми мали можливість гуляти до пізна» — згадай минулу навичку.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Visitors {a1} enter the gallery after 8 p.m. without a ticket.',
                        'options' => ["can't", 'must', 'might', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'restriction; rule in present',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «Є табличка, що вхід дозволений тільки з браслетом» — потрібне слово про заборону.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Students {a1} use the lab only if a teacher is present.',
                        'options' => ['may', 'must', 'could', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'controlled permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Охоронець дозволяє користуватися залом лише під наглядом» — обери відповідну форму.',
                    ],
                    [
                        'question' => 'Заповни пропуски: During rehearsals we {a1} practise the solo parts, and we {a1} repeat them quietly backstage.',
                        'options' => ['can', 'might', 'must', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'general ability in structured time',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: «Команда має можливість тренуватися і в залі, і поза ним» — однакова форма в обох діях.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If I join the exchange, I {a1} speak Italian better by the end of summer.',
                        'options' => ['could', 'must', 'may', 'shall'],
                        'answer_index' => 0,
                        'verb_hint' => 'ability developing under condition',
                        'concept' => 'conditional_ability',
                        'example_hint' => 'Наприклад: «За умови регулярних занять людина зможе спілкуватися впевненіше» — подумай про умовну можливість.',
                    ],
                    [
                        'question' => 'Заповни пропуски: Campers {a1} borrow the kayaks after training, and they {a1} explore the lake with a guide.',
                        'options' => ['can', 'might', 'must', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'permission after preparation',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: «Після інструктажу учасники отримують можливість користуватися спорядженням» — використай слово про дозвіл.',
                    ],
                ],
                'B1' => [
                    [
                        'question' => 'Обери правильний варіант: During the hackathon we {a1} work overnight if we register with security.',
                        'options' => ['can', 'must', 'should', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'show allowed ability after sign-up',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: «Команда має можливість працювати всю ніч за умови реєстрації» — потрібна форма про дозвіл.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: You {a1} take the company laptop abroad without written approval.',
                        'options' => ["can't", 'could', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'rule that forbids action',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «Правила компанії забороняють виносити пристрій» — згадай про пряме обмеження.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Participants {a1} present their prototype only after the director arrives.',
                        'options' => ['may', 'must', 'might', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'formal allowance dependent on arrival',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Виступ дозволений лише після сигналу керівника» — обери формальний варіант.',
                    ],
                    [
                        'question' => 'Заповни пропуски: Choir members {a1} rehearse in the hall, and they {a1} book extra time on weekends when needed.',
                        'options' => ['can', 'might', 'must', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'habitual permission in two settings',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: «Хор має доступ до зали та може продовжити репетиції» — однакове слово для обох дій.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If we invest in new software, the team {a1} handle more clients remotely.',
                        'options' => ['could', 'must', 'may', 'shall'],
                        'answer_index' => 0,
                        'verb_hint' => 'potential ability with condition',
                        'concept' => 'conditional_ability',
                        'example_hint' => 'Наприклад: «Після оновлення системи з’явиться додаткова спроможність» — подумай про можливість за умови.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Graduates {a1} request mentorship even after the program ends.',
                        'options' => ['may', 'must', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'continued permission for alumni',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Колишні учасники мають право звертатися за порадою» — обери слово про дозвіл.',
                    ],
                ],
                'B2' => [
                    [
                        'question' => 'Обери правильний варіант: Senior analysts {a1} override the protocol only when an emergency is declared.',
                        'options' => ['may', 'can', 'must', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'rare formal permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Лише у надзвичайних випадках дозволено порушити процедуру» — подумай про офіційний дозвіл.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: The diving team {a1} remain underwater for nearly an hour thanks to new tanks.',
                        'options' => ['can', 'might', 'must', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'enhanced ability in present',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: «Спорядження дозволяє спортсменам перебувати довше під водою» — обери слово про спроможність.',
                    ],
                    [
                        'question' => 'Заповни пропуски: Delegates {a1} consult the legal team, and they {a1} brief the press before signing.',
                        'options' => ['may', 'should', 'must', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'formal steps allowed twice',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Представники мають дозвіл радитися з юристами і давати коментарі» — одна форма для двох дій.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Even experienced pilots {a1} ignore weather alerts according to regulations.',
                        'options' => ["mustn't", "can't", 'might', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'strict prohibition in rules',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «Правила льотної безпеки суворо забороняють певні дії» — згадай форму, що виражає заборону.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If the funding is approved, regional labs {a1} develop prototypes independently.',
                        'options' => ['could', 'must', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'ability unlocked by approval',
                        'concept' => 'conditional_ability',
                        'example_hint' => 'Наприклад: «Отримавши фінансування, лабораторії зможуть працювати самостійно» — обери слово про потенційну здатність.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Researchers {a1} access the database only after encryption keys are refreshed.',
                        'options' => ['may', 'must', 'might', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'permission tied to security step',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Система дозволяє роботу лише після оновлення ключів» — потрібна форма офіційного дозволу.',
                    ],
                ],
                'C1' => [
                    [
                        'question' => 'Обери правильний варіант: The curator {a1} authorize night tours provided the insurance clauses are renewed.',
                        'options' => ['may', 'must', 'should', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'conditional formal permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Екскурсії дозволяють лише після оновлення договору» — обери слово для офіційного дозволу.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Seasoned interpreters {a1} render live debates without preparation.',
                        'options' => ['can', 'might', 'must', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'high-level ability now',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: «Досвідчені перекладачі справляються з імпровізованими промовами» — потрібна форма для навички.',
                    ],
                    [
                        'question' => 'Заповни пропуски: Fellows {a1} audit the archive remotely, and they {a1} request sensitive files only through encrypted channels.',
                        'options' => ['may', 'can', 'must', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'formal remote permission repeated',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Стипендіати мають дозвіл на дистанційний перегляд та запити» — одна форма для двох дозволів.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Departments {a1} circulate the draft before the ethics board reviews it, so they keep it internal.',
                        'options' => ["can't", 'might', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'prohibition before review',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «Документ не можна розсилати до завершення перевірки» — потрібна форма заборони.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If the policy shifts, associates {a1} collaborate directly with overseas auditors.',
                        'options' => ['could', 'must', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'possibility under new policy',
                        'concept' => 'conditional_ability',
                        'example_hint' => 'Наприклад: «Зі зміною правил з’явиться можливість працювати напряму» — подумай про умовну спроможність.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Conference guests {a1} request access to the restricted gallery provided they show accreditation.',
                        'options' => ['may', 'must', 'might', 'shall'],
                        'answer_index' => 0,
                        'verb_hint' => 'permission linked to proof',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Гості мають право зайти лише після пред’явлення перепустки» — обери слово про дозвіл.',
                    ],
                ],
                'C2' => [
                    [
                        'question' => 'Обери правильний варіант: Only the chief negotiator {a1} override security protocols during emergency summits.',
                        'options' => ['may', 'must', 'should', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'exclusive formal permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Лише одна посада має право відступати від правил безпеки» — згадай про офіційний дозвіл.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Veteran pilots {a1} execute zero-visibility landings after years of specialised training.',
                        'options' => ['can', 'might', 'must', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'elite ability in present',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: «Досвід і тренування дають змогу виконувати складні посадки» — обери слово про високий рівень навички.',
                    ],
                    [
                        'question' => 'Заповни пропуски: Senior editors {a1} share preliminary data internally, and they {a1} disclose it publicly once embargoes lift.',
                        'options' => ['may', 'can', 'must', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'permission repeated with timing',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Редактори мають право обговорювати інформацію всередині команди й оприлюднювати її після дозволу» — одна форма для двох кроків.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Under the new charter, observers {a1} enter closed sessions without a two-thirds vote.',
                        'options' => ["can't", 'could', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'explicit prohibition in rules',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «Регламент блокує доступ без додаткового голосування» — потрібне слово про заборону.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If the alliance expands, member labs {a1} deploy joint experiments within hours.',
                        'options' => ['could', 'must', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'advanced ability under new alliance',
                        'concept' => 'conditional_ability',
                        'example_hint' => 'Наприклад: «Розширення мережі відкриє можливість швидко запускати досліди» — обери слово про потенційну спроможність.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Diplomatic aides {a1} request satellite briefings so long as they maintain clearance.',
                        'options' => ['may', 'must', 'might', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'permission tied to clearance',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: «Доступ до закритих брифінгів дозволений лише за наявності допуску» — згадай про офіційну згоду.',
                    ],
                ],
            ],
            'Obligation & Necessity' => [
                'A1' => [
                    [
                        'question' => 'Обери правильний варіант: You {a1} wear a helmet on the bike path.',
                        'options' => ['must', 'can', 'might', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'safety rule; strong requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «На велосипедній доріжці висить знак про обов’язковий шолом» — обери слово про правило.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: He {a1} tidy his room before watching cartoons.',
                        'options' => ['should', 'must', 'might', 'can'],
                        'answer_index' => 0,
                        'verb_hint' => 'gentle duty; advice from parent',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: «Батьки очікують, що дитина прибере кімнату перед відпочинком» — потрібна форма м’якого обов’язку.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: We {a1} be quiet in the library.',
                        'options' => ['must', 'might', 'can', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'rule in public space',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Бібліотекар нагадує про тишу» — обери слово про вимогу.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: They {a1} arrive on time for the team meeting.',
                        'options' => ['have to', 'might', 'could', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'schedule requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Керівник очікує пунктуальності» — потрібна форма про обов’язковість.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: You {a1} bring your ID to enter the building.',
                        'options' => ['have to', 'might', 'can', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'entry requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Охорона просить пред’явити документ» — обери слово про необхідність.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: You {a1} pay to enter; it’s free today.',
                        'options' => ["don't need to", 'must', 'should', 'have to'],
                        'answer_index' => 0,
                        'verb_hint' => 'no obligation this time',
                        'concept' => 'absence_of_obligation',
                        'example_hint' => 'Наприклад: «Адміністратор каже, що вхід сьогодні безкоштовний» — обери форму, яка скасовує необхідність.',
                    ],
                ],
                'A2' => [
                    [
                        'question' => 'Обери правильний варіант: Employees {a1} clock in before using the workshop.',
                        'options' => ['have to', 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'company rule before work',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Інструкція вимагає відмітити прихід перед роботою» — обери слово про необхідність.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Visitors {a1} touch the paintings.',
                        'options' => ["mustn't", 'might', 'should', 'can'],
                        'answer_index' => 0,
                        'verb_hint' => 'strict prohibition in museum',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «Охоронець попереджає, що експонати чіпати не можна» — згадай форму заборони.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Students {a1} submit the assignment by Friday.',
                        'options' => ['must', 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'deadline requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Викладач наголошує на конкретній даті» — обери слово про обов’язок.',
                    ],
                    [
                        'question' => 'Заповни пропуски: During field trips we {a1} stay with the group, and we {a1} report to the leader hourly.',
                        'options' => ['must', 'should', 'might', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'two linked requirements',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Керівник дає чіткі правила поведінки на екскурсії» — однакова форма для обох правил.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If you miss the bus, you {a1} call the coordinator immediately.',
                        'options' => ['must', 'might', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'urgent instruction',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Організатор наполягає на швидкому дзвінку при запізненні» — потрібна форма суворої вимоги.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Guests {a1} bring their own towels; the hostel provides them.',
                        'options' => ["don't need to", 'must', 'should', 'have to'],
                        'answer_index' => 0,
                        'verb_hint' => 'optional item provided',
                        'concept' => 'absence_of_obligation',
                        'example_hint' => 'Наприклад: «Адміністратор каже, що рушники є на місці» — обери слово, яке знімає потребу.',
                    ],
                ],
                'B1' => [
                    [
                        'question' => 'Обери правильний варіант: Managers {a1} submit the weekly summary before Monday noon.',
                        'options' => ['have to', 'might', 'could', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'official reporting duty',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Компанія встановила конкретний дедлайн для звіту» — обери слово про обов’язок.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: You {a1} ignore customer complaints; log them immediately.',
                        'options' => ["mustn't", 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'prohibition against neglect',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «Процедура забороняє пропускати звернення клієнтів» — потрібна форма заборони.',
                    ],
                    [
                        'question' => 'Заповни пропуски: Volunteers {a1} sign the attendance sheet, and they {a1} wear badges during the event.',
                        'options' => ['must', 'should', 'might', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'paired requirements for volunteers',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Організатори вимагають підпис і бейдж» — одна форма для двох правил.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Team leads {a1} encourage breaks to prevent burnout.',
                        'options' => ['should', 'must', 'might', 'can'],
                        'answer_index' => 0,
                        'verb_hint' => 'recommended duty for leaders',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: «Керівник дбає про команду» — обери слово для поради-обов’язку.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If the server fails, technicians {a1} restart the backup protocol within minutes.',
                        'options' => ['must', 'might', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'emergency requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Інструкція описує негайну дію у критичній ситуації» — обери суворе слово.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Staff {a1} stay late tonight; the release was postponed.',
                        'options' => ["don't need to", 'must', 'should', 'have to'],
                        'answer_index' => 0,
                        'verb_hint' => 'obligation removed due to delay',
                        'concept' => 'absence_of_obligation',
                        'example_hint' => 'Наприклад: «Проєкт перенесли, тому додаткова робота не потрібна» — обери форму, що знімає вимогу.',
                    ],
                ],
                'B2' => [
                    [
                        'question' => 'Обери правильний варіант: Compliance officers {a1} report any breach to headquarters within twenty-four hours.',
                        'options' => ['must', 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'strict legal requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Регулятор вимагає негайного звіту» — обери сувору форму.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Specialists {a1} operate the reactor without the safety supervisor present.',
                        'options' => ["mustn't", 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'prohibition in high-risk area',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «Правила безпеки забороняють роботу без контролю» — потрібна форма заборони.',
                    ],
                    [
                        'question' => 'Заповни пропуски: Contractors {a1} submit updated insurance forms, and they {a1} display permits on-site.',
                        'options' => ['must', 'should', 'might', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'dual compliance requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Підрядники мають виконати дві формальні умови» — одна форма для обох вимог.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Department heads {a1} review budgets quarterly to secure funding.',
                        'options' => ['need to', 'might', 'would', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'necessary regular action',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Щоб отримати кошти, керівники повинні регулярно перевіряти бюджети» — обери слово про потребу.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If inspections reveal faults, engineers {a1} shut down the line immediately.',
                        'options' => ['must', 'should', 'might', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'emergency shutdown rule',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Процедура вимагає негайного зупинення при несправності» — потрібна сильна форма.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If regulations change, suppliers {a1} redesign their packaging within months.',
                        'options' => ['will have to', 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'future requirement after change',
                        'concept' => 'future_requirement',
                        'example_hint' => 'Наприклад: «Нові правила змусять компанії переробити упаковку» — обери форму про майбутній обов’язок.',
                    ],
                ],
                'C1' => [
                    [
                        'question' => 'Обери правильний варіант: Auditors {a1} finalize the compliance report before the board convenes.',
                        'options' => ['must', 'should', 'might', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'critical deadline for auditors',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Засідання ради вимагає готового звіту» — обери сувору форму.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Representatives {a1} disclose negotiation details before the embargo lifts.',
                        'options' => ["mustn't", 'might', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'confidentiality rule',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «Домовленості тримають у таємниці до офіційного оголошення» — потрібна форма заборони.',
                    ],
                    [
                        'question' => 'Заповни пропуски: Fellows {a1} document every interview, and they {a1} store recordings in encrypted drives.',
                        'options' => ['must', 'should', 'might', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'paired archival obligations',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Програма вимагає детального збереження матеріалів» — одна форма для двох вимог.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Senior advisers {a1} outline contingency plans to guide junior staff.',
                        'options' => ['ought to', 'must', 'might', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'moral duty for leaders',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: «Досвідчені консультанти мають показувати шлях» — обери форму морального обов’язку.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If the treaty fails, mediators {a1} brief allied governments before dawn.',
                        'options' => ['must', 'might', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'urgent diplomatic obligation',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «У випадку провалу перемовин необхідно одразу повідомити партнерів» — обери сильну форму.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: After the policy change, contractors {a1} file duplicate forms anymore.',
                        'options' => ["needn't", 'must', 'should', 'have to'],
                        'answer_index' => 0,
                        'verb_hint' => 'requirement removed',
                        'concept' => 'absence_of_obligation',
                        'example_hint' => 'Наприклад: «Нові правила скасували дублікати документів» — обери слово, що показує відсутність потреби.',
                    ],
                ],
                'C2' => [
                    [
                        'question' => 'Обери правильний варіант: Cabinet ministers {a1} deliver a unified statement within one hour of the verdict.',
                        'options' => ['must', 'should', 'might', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'high-level directive',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Уряд має швидко реагувати на рішення суду» — обери сувору форму.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Advisory panels {a1} leak draft findings before regulators review them.',
                        'options' => ["mustn't", 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'strict confidentiality order',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: «Доповідь не можна оприлюднювати до схвалення» — потрібна форма заборони.',
                    ],
                    [
                        'question' => 'Заповни пропуски: Emergency coordinators {a1} activate the satellite link, and they {a1} secure a backup command post.',
                        'options' => ['must', 'should', 'might', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'dual emergency obligations',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «План реагування містить два обов’язкові кроки» — одна форма для обох.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Chief economists {a1} reassess fiscal models monthly to anticipate shocks.',
                        'options' => ['need to', 'must', 'might', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'continuous necessity for experts',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: «Щоб уникнути криз, потрібно регулярно переглядати моделі» — обери слово про потребу.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If sanctions expand, logistics teams {a1} redesign supply routes overnight.',
                        'options' => ['will have to', 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'future necessity under pressure',
                        'concept' => 'future_requirement',
                        'example_hint' => 'Наприклад: «Нові санкції змусять команду швидко перебудувати маршрути» — обери форму про майбутній обов’язок.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: After the treaty is ratified, observers {a1} attend daily briefings anymore.',
                        'options' => ["don't need to", 'must', 'should', 'have to'],
                        'answer_index' => 0,
                        'verb_hint' => 'requirement lifted later',
                        'concept' => 'absence_of_obligation',
                        'example_hint' => 'Наприклад: «Після ратифікації щоденні зустрічі більше не обов’язкові» — обери форму, що скасовує обов’язок.',
                    ],
                ],
            ],
            'Possibility & Deduction' => [
                'A1' => [
                    [
                        'question' => 'Обери правильний варіант: It {a1} rain later.',
                        'options' => ['might', 'must', 'can', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'weak possibility about weather',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: «Сірі хмари лише натякають на дощ» — обери слово з невеликою впевненістю.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: She {a1} be at home now.',
                        'options' => ['might', 'must', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'uncertain location',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: «Ми не впевнені, чи вона вдома» — обери м’яке припущення.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: He {a1} be in the office; I just saw him leaving.',
                        'options' => ["can't", 'might', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence against idea',
                        'concept' => 'contradicting_evidence',
                        'example_hint' => 'Наприклад: «Факт суперечить припущенню» — обери слово, що показує неможливість.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: They walked for hours, so they {a1} be hungry now.',
                        'options' => ['must', 'might', 'can', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'logical conclusion about present',
                        'concept' => 'strong_deduction',
                        'example_hint' => 'Наприклад: «Після довгої подорожі люди напевно голодні» — обери слово з високою впевненістю.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: This {a1} be the right key; it fits perfectly.',
                        'options' => ['must', 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence strongly supports',
                        'concept' => 'probable_consequence',
                        'example_hint' => 'Наприклад: «Ключ підходить без зусиль» — обери слово з впевненим висновком.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: The lights are off, so she {a1} be asleep.',
                        'options' => ['must', 'might', 'may', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'strong deduction from clue',
                        'concept' => 'strong_deduction',
                        'example_hint' => 'Наприклад: «Темно в кімнаті, значить людина ймовірно спить» — обери впевнене припущення.',
                    ],
                ],
                'A2' => [
                    [
                        'question' => 'Обери правильний варіант: They {a1} arrive late because of traffic.',
                        'options' => ['might', 'must', 'can', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'uncertain delay',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: «Затори лише припускають затримку» — обери форму з невеликою впевненістю.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: He studied all night, so he {a1} be exhausted now.',
                        'options' => ['must', 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'logical result of effort',
                        'concept' => 'strong_deduction',
                        'example_hint' => 'Наприклад: «Після нічного навчання людина майже напевно втомлена» — обери впевнений висновок.',
                    ],
                    [
                        'question' => 'Заповни пропуски: The schedule {a1} change tomorrow, and it {a1} affect our plans.',
                        'options' => ['might', 'must', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'possibility repeated twice',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: «Є шанс, що графік зміниться і вплине на нас» — однакова форма для обох припущень.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: This noise {a1} be the neighbours; they left town yesterday.',
                        'options' => ["can't", 'might', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence rules out option',
                        'concept' => 'contradicting_evidence',
                        'example_hint' => 'Наприклад: «Факт відсутності людей суперечить припущенню» — обери слово, що показує неможливість.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: The package {a1} be theirs; the label has their name.',
                        'options' => ['must', 'might', 'could', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence points to ownership',
                        'concept' => 'probable_consequence',
                        'example_hint' => 'Наприклад: «Підпис на посилці збігається» — обери слово з високою впевненістю.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: She {a1} be at the gym; her phone shows another district.',
                        'options' => ["can't", 'might', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'tracking contradicts assumption',
                        'concept' => 'contradicting_evidence',
                        'example_hint' => 'Наприклад: «Локація на карті заперечує припущення» — обери форму, що відхиляє можливість.',
                    ],
                ],
                'B1' => [
                    [
                        'question' => 'Обери правильний варіант: The report {a1} be accurate; multiple sources confirm the figures.',
                        'options' => ['must', 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence points strongly to truth',
                        'concept' => 'probable_consequence',
                        'example_hint' => 'Наприклад: «Кілька джерел підтверджують дані» — обери слово з високою впевненістю.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: You {a1} expect him at eight; his flight was cancelled.',
                        'options' => ["can't", 'might', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'cancellation blocks possibility',
                        'concept' => 'contradicting_evidence',
                        'example_hint' => 'Наприклад: «Скасований рейс робить прибуття неможливим» — обери слово, що відкидає очікування.',
                    ],
                    [
                        'question' => 'Заповни пропуски: The sensors {a1} fail tonight, and the system {a1} trigger an alarm if they do.',
                        'options' => ['might', 'must', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'uncertain failure repeated',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: «Поломка не гарантована, але можлива» — однакова форма для двох припущень.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Given the strange noise, the machine {a1} need maintenance soon.',
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'weak warning about future issue',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: «Незвичний звук лише натякає на проблему» — обери слово з обережним припущенням.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If the sky clears, we {a1} see the comet from the hill.',
                        'options' => ['might', 'must', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'future chance depending on weather',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: «За умови хорошого неба з’явиться шанс побачити комету» — обери слово про можливість.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Her desk is empty, so she {a1} left early.',
                        'options' => ['must have', 'might', 'could', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'past deduction from clue',
                        'concept' => 'logical_conclusion_past',
                        'example_hint' => 'Наприклад: «Порожнє робоче місце свідчить, що людина вже пішла» — обери форму для минулого висновку.',
                    ],
                ],
                'B2' => [
                    [
                        'question' => 'Обери правильний варіант: The witness {a1} be mistaken; three cameras confirm the timeline.',
                        'options' => ['must', 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'strong deduction about error',
                        'concept' => 'strong_deduction',
                        'example_hint' => 'Наприклад: «Докази показують, що свідок помиляється» — обери впевнене припущення.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: This figure {a1} represent revenue; it aligns with expenses instead.',
                        'options' => ["can't", 'might', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence contradicts assumption',
                        'concept' => 'contradicting_evidence',
                        'example_hint' => 'Наприклад: «Стаття бюджету показує інший показник» — обери форму, що відкидає можливість.',
                    ],
                    [
                        'question' => 'Заповни пропуски: The committee {a1} announce a decision today, and the markets {a1} shift sharply afterward.',
                        'options' => ['might', 'must', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'speculation about announcement and reaction',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: «Є шанс, що рішення сьогодні вплине на ринок» — однакова форма для двох припущень.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: After reviewing the logs, the error {a1} come from user input.',
                        'options' => ['must have', 'might', 'could', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'past deduction from data',
                        'concept' => 'logical_conclusion_past',
                        'example_hint' => 'Наприклад: «Журнали показують, що саме користувач спричинив збій» — обери форму для минулого висновку.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If negotiations fail, the currency {a1} drop overnight.',
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'future speculation on failure',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: «Провал перемовин може вплинути на курс» — обери обережне припущення.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: The team {a1} have missed the deadline; the submission timestamp is confirmed.',
                        'options' => ["can't have", 'might', 'must have', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence rejects past failure',
                        'concept' => 'contradicting_evidence',
                        'example_hint' => 'Наприклад: «Система показує, що файл надійшов вчасно» — обери форму, що заперечує минулу можливість.',
                    ],
                ],
                'C1' => [
                    [
                        'question' => 'Обери правильний варіант: The lab results {a1} indicate contamination; every sample shows the same anomaly.',
                        'options' => ['must', 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'strong deduction from repeated data',
                        'concept' => 'strong_deduction',
                        'example_hint' => 'Наприклад: «Кожен зразок має однакову аномалію» — обери слово з високою впевненістю.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: The crash {a1} be due to user error; automated logs prove otherwise.',
                        'options' => ["can't", 'might', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'logs contradict assumption',
                        'concept' => 'contradicting_evidence',
                        'example_hint' => 'Наприклад: «Записи системи спростовують версію про користувача» — обери форму, що відкидає можливість.',
                    ],
                    [
                        'question' => 'Заповни пропуски: The auditors {a1} uncover irregularities, and they {a1} escalate the report to regulators.',
                        'options' => ['might', 'must', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'speculative chain of events',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: «Є шанс, що аудитори знайдуть проблему і повідомлять владу» — одна форма для двох припущень.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Given the encrypted messages, the leak {a1} have originated inside the legal team.',
                        'options' => ['must have', 'might', 'could', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'past deduction from context',
                        'concept' => 'logical_conclusion_past',
                        'example_hint' => 'Наприклад: «Доступ до шифрів був тільки у юридичної групи» — обери форму для минулого висновку.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If projections hold, demand {a1} surge before winter.',
                        'options' => ['might', 'must', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'future speculation from forecasts',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: «Прогнози натякають на зростання попиту» — обери слово з обережним прогнозом.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: The device {a1} have failed spontaneously; maintenance records show tampering.',
                        'options' => ["can't have", 'might', 'must have', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence rejects past scenario',
                        'concept' => 'contradicting_evidence',
                        'example_hint' => 'Наприклад: «Записи техобслуговування показують сторонній вплив» — обери форму, що заперечує можливість.',
                    ],
                ],
                'C2' => [
                    [
                        'question' => 'Обери правильний варіант: Satellite data {a1} confirm the hypothesis; the trajectories overlap precisely.',
                        'options' => ['must', 'might', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'compelling scientific evidence',
                        'concept' => 'strong_deduction',
                        'example_hint' => 'Наприклад: «Орбіти збігаються на графіках» — обери слово з максимальною впевненістю.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: The minister’s statement {a1} be accidental; it mirrors the leaked memo word for word.',
                        'options' => ["can't", 'might', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence contradicts coincidence',
                        'concept' => 'contradicting_evidence',
                        'example_hint' => 'Наприклад: «Текст збігається з витоком» — обери форму, що заперечує випадковість.',
                    ],
                    [
                        'question' => 'Заповни пропуски: The diplomatic cables {a1} reveal coordinated pressure, and global markets {a1} react within hours.',
                        'options' => ['might', 'must', 'should', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'speculation on revelation and reaction',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: «Міжнародні листи можуть показати тиск і спричинити коливання» — одна форма для двох припущень.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: Forensic analysts {a1} have traced the breach to an external server; the timestamps match their infrastructure.',
                        'options' => ['must have', 'might', 'could', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'past deduction from forensic match',
                        'concept' => 'logical_conclusion_past',
                        'example_hint' => 'Наприклад: «Часові позначки збігаються з конкретною мережею» — обери форму для минулого висновку.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: If the ceasefire collapses, regional alliances {a1} shift overnight.',
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'future geopolitical speculation',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: «Порушення перемир’я може швидко змінити союзи» — обери слово про можливий сценарій.',
                    ],
                    [
                        'question' => 'Обери правильний варіант: The committee {a1} have predicted this outcome; their models excluded the recent disruption.',
                        'options' => ["can't have", 'might', 'must have', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence denies past prediction',
                        'concept' => 'contradicting_evidence',
                        'example_hint' => 'Наприклад: «Моделі не враховували нову подію» — обери форму, що заперечує минулий прогноз.',
                    ],
                ],
            ],
        ];

        $questions = [];
        foreach ($datasets as $theme => $levels) {
            foreach ($levels as $level => $items) {
                foreach ($items as $item) {
                    $questions[] = array_merge($item, [
                        'theme' => $theme,
                        'level' => $level,
                    ]);
                }
            }
        }

        return $questions;
    }
}
