<?php

declare(strict_types=1);

/**
 * Canonical Ukrainian infinitives for Present Perfect questions whose gap
 * contains a lexical verb. The compact level arrays follow the stable
 * 01-12 suffixes used by the JSON question banks.
 *
 * @return array<string, string>
 */
function presentPerfectUkrainianVerbs(): array
{
    static $verbs = null;

    if (is_array($verbs)) {
        return $verbs;
    }

    $families = [
        'forms' => [
            'a1' => ['завершити', 'жити', 'прибрати', 'працювати', 'бувати', 'загубити', 'подорожувати', 'знайти', 'переглянути', 'купити', 'написати', 'закінчити їсти'],
            'a2' => ['їздити верхи', 'відвідувати', 'завершити заповнення', 'працювати', 'бачити', 'загубити', 'відвідати', 'знайти', 'переглянути', 'початися', 'забронювати', 'приготувати'],
            'b1' => ['знати', 'підвищувати', 'полагодити', 'завершити', 'бачити', 'забути', 'жити', 'відкрити', 'отримати', 'принести', 'зрости', 'спрацювати'],
            'b2' => ['виявити', 'досягти цілей', 'коштувати', 'працювати', 'доводитися', 'пошкодити', 'зіткнутися', 'оприлюднити', 'отримати', 'знизитися', 'відповісти', 'домовитися щодо умов'],
            'c1' => ['поставити під сумнів', 'розглянути', 'продемонструвати', 'залишатися', 'розглянути апеляцію', 'стабілізуватися', 'входити до складу комісії', 'узгодити', 'розкрити', 'показати', 'мати', 'сформувати'],
            'c2' => ['виявитися', 'стати', 'пояснити', 'отримати', 'перебувати під тиском', 'представляти як', 'здобути', 'ухвалити', 'підірвати', 'запропонувати', 'зірватися', 'спричинити'],
        ],
        'neg' => [
            'a1' => ['завершити', 'з’їсти', 'спакувати', 'бачити', 'надіслати', 'знайти', 'купити', 'прочитати', 'виконати', 'скуштувати', 'отримати', 'піти'],
            'a2' => ['завершити заповнення', 'прибрати', 'знайти', 'подорожувати', 'надіслати', 'прочитати', 'забронювати', 'переглянути', 'їхати цим маршрутом', 'водити', 'отримати', 'вийти з офісу'],
            'b1' => ['завершити', 'полагодити', 'ухвалити рішення', 'зіткнутися', 'відповісти', 'отримати', 'завантажити', 'перевірити', 'опрацювати', 'мати', 'отримати', 'затвердити'],
            'b2' => ['досягти цілей', 'усунути несправність', 'досягти', 'зіткнутися', 'подати', 'перевірити', 'відкинути', 'затвердити', 'зіткнутися', 'оприлюднити', 'оприлюднити', 'надати'],
            'c1' => ['розглянути', 'пояснити', 'відтворити', 'зафіксувати', 'розкрити', 'підтвердити', 'відкинути', 'затвердити', 'спостерігати', 'піддати', 'розв’язати', 'рецензувати'],
            'c2' => ['встановити', 'оцінити кількісно', 'надати', 'спричинити', 'розкрити', 'обґрунтувати', 'відкинути', 'схвалити', 'зіткнутися', 'перевірити', 'узгодити', 'винести рішення'],
        ],
        'q' => [
            'a1' => ['завершити', 'прочитати', 'зробити', 'бувати', 'надіслати', 'бачити', 'зрозуміти', 'відкрити', 'покласти', 'бачити', 'знайти', 'знайти'],
            'a2' => ['виконати', 'прочитати', 'пофарбувати', 'чути', 'надіслати', 'прибути', 'приготувати', 'спекти', 'залишити', 'їхати цим маршрутом', 'зателефонувати', 'увімкнути'],
            'b1' => ['завершити', 'переглянути', 'завантажити', 'працювати', 'відповісти', 'підтвердити', 'виявити', 'підписати', 'зберегти', 'зафіксувати', 'знайти', 'надіслати'],
            'b2' => ['остаточно підготувати', 'перевірити', 'завершити', 'аналізувати', 'надіслати', 'висловити', 'подати дані', 'підписати', 'зберегти', 'спостерігати', 'отримати', 'розгорнути'],
            'c1' => ['додати', 'вивчити', 'поставити під сумнів', 'зустрічати', 'надати', 'усунути', 'надати', 'виправити', 'перемістити', 'визнавати', 'прийняти', 'перевірити'],
            'c2' => ['змінити', 'розглянути', 'спричинити', 'застосувати', 'оприлюднити', 'підірвати', 'усунути', 'переглянути', 'задокументувати', 'надати', 'ухвалити', 'розкрити'],
        ],
    ];

    $verbs = [];
    foreach ($families as $family => $levels) {
        foreach ($levels as $level => $levelVerbs) {
            if (count($levelVerbs) !== 12) {
                throw new RuntimeException("Expected 12 {$family}/{$level} verbs.");
            }

            foreach ($levelVerbs as $index => $verb) {
                $uuid = sprintf('present-perfect-%s-v3-%s-%02d', $family, $level, $index + 1);
                $verbs[$uuid] = $verb;
            }
        }
    }

    $timeExpressionVerbs = [
        'present-perfect-time-v3-a1-10' => 'вивчити',
        'present-perfect-time-v3-a1-12' => 'загубити',
        'present-perfect-time-v3-a2-10' => 'прочитати',
        'present-perfect-time-v3-a2-12' => 'відвідати',
        'present-perfect-time-v3-b1-10' => 'відвідати',
        'present-perfect-time-v3-b1-12' => 'зустріти',
        'present-perfect-time-v3-b2-10' => 'опитати',
        'present-perfect-time-v3-b2-12' => 'відкликати',
        'present-perfect-time-v3-c1-10' => 'знайти',
        'present-perfect-time-v3-c1-12' => 'оголосити судове рішення',
        'present-perfect-time-v3-c2-10' => 'дійти висновків',
        'present-perfect-time-v3-c2-12' => 'скасувати',
    ];

    return $verbs = array_merge($verbs, $timeExpressionVerbs);
}

function presentPerfectUkrainianVerb(string $uuid): ?string
{
    return presentPerfectUkrainianVerbs()[strtolower($uuid)] ?? null;
}

/**
 * Canonical subjects hidden inside a Present Perfect answer marker. The
 * learner-facing value is Ukrainian only; the English fragment is retained
 * solely for content validation against the authored answer.
 *
 * @return array<string, array{answer: string, hint: string}>
 */
function presentPerfectHiddenSubjects(): array
{
    static $subjects = null;

    if (is_array($subjects)) {
        return $subjects;
    }

    $questionLevels = [
        'a1' => [
            ['you', 'ти / ви'], ['she', 'вона'], ['you', 'ти / ви'], ['you', 'ти / ви'],
            ['Leo', 'Лео'], null, ['they', 'вони'], ['Anna', 'Анна'],
            ['you', 'ти / ви'], ['you', 'ти / ви'], ['Mia', 'Мія'], ['you', 'ти / ви'],
        ],
        'a2' => [
            ['you', 'ти / ви'], ['Nora', 'Нора'], ['you', 'ти / ви'], ['you', 'ти / ви'],
            ['Daniel', 'Даніель'], null, ['they', 'вони'], ['Mia', 'Мія'],
            ['you', 'ти / ви'], ['we', 'ми'], ['Leo', 'Лео'], ['you', 'ти / ви'],
        ],
        'b1' => [
            ['you', 'ти / ви'], ['she', 'вона'], ['you', 'ти / ви'], ['you', 'ти / ви'],
            ['he', 'він'], null, ['the other teams', 'інші команди'], ['Anna', 'Анна'],
            ['you', 'ти / ви'], ['we', 'ми'], ['she', 'вона'], ['you', 'ти / ви'],
        ],
        'b2' => [
            ['you', 'ти / ви'], ['counsel', 'юрисконсульт'], ['we', 'ми'], ['you', 'ти / ви'],
            ['he', 'він'], null, ['all regions', 'усі регіони'], ['Anna', 'Анна'],
            ['they', 'вони'], ['we', 'ми'], ['the team', 'команда'], ['you', 'ти / ви'],
        ],
        'c1' => [
            ['you', 'ти / ви'], ['she', 'вона'], ['the reviewers', 'рецензенти'], ['you', 'ти / ви'],
            ['counsel', 'юрисконсульт'], null, ['the researchers', 'дослідники'], ['the editor', 'редактор'],
            ['the team', 'команда'], ['the court', 'суд'], ['the reviewers', 'рецензенти'], ['you', 'ти / ви'],
        ],
        'c2' => [
            ['the latest evidence', 'найновіші докази'], ['the panel', 'колегія'], ['what', 'що саме'], ['this doctrine', 'ця доктрина'],
            ['the authority', 'уповноважений орган'], null, ['the investigators', 'слідчі'], ['the tribunal', 'трибунал'],
            ['the authors', 'автори'], ['the court', 'суд'], ['the commission', 'комісія'], ['the parties', 'сторони'],
        ],
    ];

    $subjects = [];
    foreach ($questionLevels as $level => $levelSubjects) {
        if (count($levelSubjects) !== 12) {
            throw new RuntimeException("Expected 12 q/{$level} subjects.");
        }

        foreach ($levelSubjects as $index => $subject) {
            if ($subject === null) {
                continue;
            }

            $uuid = sprintf('present-perfect-q-v3-%s-%02d', $level, $index + 1);
            $subjects[$uuid] = ['answer' => $subject[0], 'hint' => $subject[1]];
        }
    }

    $subjects += [
        'present-perfect-forms-v3-a2-01' => ['answer' => 'you', 'hint' => 'ти / ви'],
        'present-perfect-forms-v3-a2-03' => ['answer' => 'Mia', 'hint' => 'Мія'],
        'present-perfect-forms-v3-a2-07' => ['answer' => 'Leo', 'hint' => 'Лео'],
        'present-perfect-forms-v3-a2-11' => ['answer' => 'your parents', 'hint' => 'твої / ваші батьки'],
        'present-perfect-forms-v3-b1-03' => ['answer' => 'the technician', 'hint' => 'технік'],
        'present-perfect-forms-v3-b1-07' => ['answer' => 'Anna', 'hint' => 'Анна'],
        'present-perfect-forms-v3-b1-10' => ['answer' => 'the volunteers', 'hint' => 'волонтери'],
        'present-perfect-forms-v3-b2-03' => ['answer' => 'the project', 'hint' => 'проєкт'],
        'present-perfect-forms-v3-b2-05' => ['answer' => 'you', 'hint' => 'ти / ви'],
        'present-perfect-forms-v3-b2-11' => ['answer' => 'the supplier', 'hint' => 'постачальник'],
        'present-perfect-forms-v3-c1-07' => ['answer' => 'the researcher', 'hint' => 'дослідник'],
        'present-perfect-forms-v3-c1-11' => ['answer' => 'analysts', 'hint' => 'аналітики'],
        'present-perfect-forms-v3-c2-05' => ['answer' => 'policymakers', 'hint' => 'політики'],
        'present-perfect-forms-v3-c2-09' => ['answer' => 'the new evidence', 'hint' => 'нові докази'],
        'present-perfect-forms-v3-c2-12' => ['answer' => 'a single ruling', 'hint' => 'одне судове рішення'],
        'present-perfect-neg-v3-c1-03' => ['answer' => 'other teams', 'hint' => 'інші команди'],
        'present-perfect-neg-v3-c2-03' => ['answer' => 'the inquiry', 'hint' => 'слідство'],
    ];

    return $subjects;
}

function presentPerfectHiddenSubject(string $uuid): ?string
{
    return presentPerfectHiddenSubjects()[strtolower($uuid)]['hint'] ?? null;
}

function presentPerfectHiddenAnswerSubject(string $uuid): ?string
{
    return presentPerfectHiddenSubjects()[strtolower($uuid)]['answer'] ?? null;
}

/** @return array<string, string> */
function presentPerfectQuestionSubjects(): array
{
    return array_map(
        static fn (array $subject): string => $subject['hint'],
        array_filter(
            presentPerfectHiddenSubjects(),
            static fn (string $uuid): bool => str_starts_with($uuid, 'present-perfect-q-v3-'),
            ARRAY_FILTER_USE_KEY
        )
    );
}

function presentPerfectQuestionSubject(string $uuid): ?string
{
    return presentPerfectQuestionSubjects()[strtolower($uuid)] ?? null;
}

/**
 * Replace legacy literal English subjects in authored hint context. This map
 * also keeps every content rebuild deterministic without repeating the hidden
 * subject after the Ukrainian prefix.
 *
 * @return array<string, array<string, string>>
 */
function presentPerfectSubjectWordingReplacements(): array
{
    return [
        'present-perfect-forms-v3-a1-01' => ['підмет I' => 'підмет «я»'],
        'present-perfect-forms-v3-a1-02' => ['після підмета we' => 'після підмета «ми»'],
        'present-perfect-forms-v3-a1-07' => ['підмет she' => 'підмет «вона»'],
        'present-perfect-forms-v3-a1-09' => ['підмет I' => 'підмет «я»'],
        'present-perfect-forms-v3-a1-12' => ['підмет Tom' => 'підмет «Том»'],
        'present-perfect-forms-v3-a2-02' => ['підмет they' => 'підмет «вони»'],
        'present-perfect-forms-v3-a2-03' => ['зважте на порядок слів і підмет Mia' => 'зважте на порядок слів'],
        'present-perfect-forms-v3-a2-08' => ['підмет children' => 'підмет «діти»'],
        'present-perfect-forms-v3-b1-02' => ['підмет shops' => 'підмет «магазини»'],
        'present-perfect-forms-v3-b1-08' => ['council тут є одним органом' => 'підмет «міська рада» тут позначає один орган'],
        'present-perfect-forms-v3-b1-07' => ['врахуйте інверсію з підметом Anna' => 'врахуйте інверсію'],
        'present-perfect-forms-v3-b2-01' => ['підмет researchers' => 'підмет «дослідники»'],
        'present-perfect-forms-v3-b2-07' => ['головне слово підмета member' => 'головне слово підмета «учасник»'],
        'present-perfect-forms-v3-b2-08' => ['committee тут є одним органом' => 'підмет «комітет» тут позначає один орган'],
        'present-perfect-forms-v3-b2-12' => ['both departments — множина' => 'підмет «обидва відділи» має форму множини'],
        'present-perfect-forms-v3-c1-01' => ['головне слово evidence є незлічуваним' => 'підмет означає «докази», але в англійському реченні є незлічуваним'],
        'present-perfect-forms-v3-c1-02' => ['підмет researchers' => 'підмет «дослідники»'],
        'present-perfect-forms-v3-c1-03' => ['study має форму однини' => 'підмет «дослідження» має форму однини'],
        'present-perfect-forms-v3-c1-06' => ['demand є незлічуваним іменником' => 'підмет «попит» є незлічуваним іменником'],
        'present-perfect-forms-v3-c1-07' => ['врахуйте інверсію з підметом researcher' => 'врахуйте інверсію'],
        'present-perfect-forms-v3-c1-08' => ['the two teams має значення множини' => 'підмет «дві дослідницькі групи» має значення множини'],
        'present-perfect-forms-v3-c1-09' => ['board тут є одним органом' => 'підмет «рада директорів» тут позначає один орган'],
        'present-perfect-forms-v3-c1-10' => ['Головне слово advances має форму множини' => 'Головне слово підмета «досягнення» має форму множини'],
        'present-perfect-forms-v3-c1-12' => ['coalition має форму однини' => 'підмет «коаліція» має форму однини'],
        'present-perfect-forms-v3-c2-01' => ['assumptions стоїть у множині' => 'підмет «кілька припущень» стоїть у множині'],
        'present-perfect-forms-v3-c2-02' => ['Головне слово implications має форму множини' => 'Підмет «довгострокові наслідки» має форму множини'],
        'present-perfect-forms-v3-c2-03' => ['explanation має форму однини' => 'підмет «жодне переконливе пояснення» має форму однини'],
        'present-perfect-forms-v3-c2-04' => ['підмет we' => 'підмет «ми»'],
        'present-perfect-forms-v3-c2-06' => ['debate має форму однини' => 'підмет «дискусія» має форму однини'],
        'present-perfect-forms-v3-c2-07' => ['з the only mediator' => 'з підметом «єдиний посередник»'],
        'present-perfect-forms-v3-c2-08' => ['protocol є об’єктом дії' => 'підмет «переглянутий протокол» є об’єктом дії'],
        'present-perfect-forms-v3-c2-09' => ['evidence є незлічуваним іменником' => 'цей підмет є незлічуваним і вимагає форми однини'],
        'present-perfect-forms-v3-c2-10' => ['Neither side узгоджується як однина' => 'підмет «жодна зі сторін» узгоджується як однина'],
        'present-perfect-forms-v3-c2-12' => ['підмет a single ruling має форму однини' => 'підмет має форму однини'],
        'present-perfect-neg-v3-a1-01' => ['підмет I' => 'підмет «я»'],
        'present-perfect-neg-v3-a1-02' => ['Підмет Mia' => 'Підмет «Мія»'],
        'present-perfect-neg-v3-a1-05' => ['з Tom' => 'з підметом «Том»'],
        'present-perfect-neg-v3-a1-07' => ['для підмета we' => 'для підмета «ми»'],
        'present-perfect-neg-v3-a1-10' => ['підмет Leo' => 'підмет «Лео»'],
        'present-perfect-neg-v3-a1-11' => ['підмет they' => 'підмет «вони»'],
        'present-perfect-neg-v3-a2-01' => ['підмет you' => 'підмет «ти / ви»'],
        'present-perfect-neg-v3-a2-02' => ['Підмет Daniel' => 'Підмет «Даніель»'],
        'present-perfect-neg-v3-a2-05' => ['з Nora' => 'з підметом «Нора»'],
        'present-perfect-neg-v3-a2-06' => ['підмет he' => 'підмет «він»'],
        'present-perfect-neg-v3-a2-07' => ['для we' => 'для підмета «ми»'],
        'present-perfect-neg-v3-a2-08' => ['з Ella' => 'з підметом «Елла»'],
        'present-perfect-neg-v3-a2-10' => ['підмет Tom' => 'підмет «Том»'],
        'present-perfect-neg-v3-a2-11' => ['Children має значення множини' => 'Підмет «діти» має значення множини'],
        'present-perfect-neg-v3-b1-01' => ['підмет I' => 'підмет «я»'],
        'present-perfect-neg-v3-b1-02' => ['головне слово підмета technician' => 'головне слово підмета «технік»'],
        'present-perfect-neg-v3-b1-04' => ['головне слово підмета engineer' => 'головне слово підмета «інженер»'],
        'present-perfect-neg-v3-b1-05' => ['з головним словом supplier' => 'з підметом «постачальник»'],
        'present-perfect-neg-v3-b1-07' => ['для we' => 'для підмета «ми»'],
        'present-perfect-neg-v3-b1-08' => ['підмет editor' => 'підмет «редактор»'],
        'present-perfect-neg-v3-b1-10' => ['підмет Maya' => 'підмет «Мая»'],
        'present-perfect-neg-v3-b1-11' => ['Підмет volunteers' => 'Підмет «волонтери»'],
        'present-perfect-neg-v3-b1-12' => ['з manager' => 'з підметом «її керівник»'],
        'present-perfect-neg-v3-b2-01' => ['підмет teams' => 'підмет «відділи продажів»'],
        'present-perfect-neg-v3-b2-02' => ['головне слово update стоїть в однині' => 'підмет «останнє оновлення» стоїть в однині'],
        'present-perfect-neg-v3-b2-03' => ['підмет the two departments' => 'підмет «два відділи»'],
        'present-perfect-neg-v3-b2-05' => ['з contractor' => 'з підметом «підрядник»'],
        'present-perfect-neg-v3-b2-06' => ['Evidence є незлічуваним підметом' => 'Цей підмет є незлічуваним'],
        'present-perfect-neg-v3-b2-08' => ['головне слово підмета director' => 'головне слово підмета «керівник»'],
        'present-perfect-neg-v3-b2-11' => ['Підмет agencies' => 'Підмет «відомства»'],
        'present-perfect-neg-v3-c1-01' => ['підмет reviewers' => 'підмет «рецензенти»'],
        'present-perfect-neg-v3-c1-02' => ['підмет model' => 'підмет «модель»'],
        'present-perfect-neg-v3-c1-04' => ['головне слово investigation' => 'головне слово підмета «розслідування»'],
        'present-perfect-neg-v3-c1-05' => ['головне слово підмета auditor' => 'головне слово підмета «аудитор»'],
        'present-perfect-neg-v3-c1-06' => ['Account є об’єктом перевірки' => 'Підмет «версія свідка» є об’єктом перевірки'],
        'present-perfect-neg-v3-c1-07' => ['підметом researchers' => 'підметом «дослідники»'],
        'present-perfect-neg-v3-c1-08' => ['головне слово підмета officer' => 'головне слово підмета «уповноважений»'],
        'present-perfect-neg-v3-c2-01' => ['підмет investigators' => 'підмет «слідчі»'],
        'present-perfect-neg-v3-c2-02' => ['Impact є об’єктом кількісної оцінки' => 'Підмет «довгостроковий вплив» є об’єктом кількісної оцінки'],
        'present-perfect-neg-v3-c2-04' => ['головне слово ruling' => 'головне слово підмета «судове рішення»'],
        'present-perfect-neg-v3-c2-05' => ['головне слово підмета reviewer' => 'головне слово підмета «рецензент»'],
        'present-perfect-neg-v3-c2-06' => ['Claim є об’єктом підтвердження' => 'Підмет «головне твердження» є об’єктом підтвердження'],
        'present-perfect-neg-v3-c2-07' => ['підмет negotiators' => 'підмет «учасники переговорів»'],
        'present-perfect-neg-v3-c2-08' => ['Proposal є об’єктом рішення' => 'Підмет «пропозиція» є об’єктом схвалення'],
        'present-perfect-neg-v3-c2-10' => ['Assumption є об’єктом перевірки' => 'Підмет «основне припущення» є об’єктом перевірки'],
        'present-perfect-neg-v3-c2-11' => ['підмет parties' => 'підмет «сторони»'],
        'present-perfect-q-v3-a1-02' => ['Підмет she позначає одну особу, а дія важлива' => 'Дія важлива'],
        'present-perfect-q-v3-a1-05' => ['Вітальний лист досі очікують, а Leo — одна людина.' => 'Вітальний лист досі очікують.'],
        'present-perfect-q-v3-a1-07' => ['Результат розуміння перевіряють зараз, а they позначає кількох людей.' => 'Результат розуміння перевіряють зараз.'],
        'present-perfect-q-v3-a2-02' => ['Нора має відповісти зараз' => 'Відповідь потрібна зараз'],
        'present-perfect-q-v3-b1-07' => ['Порівнюються результати кількох команд станом на тепер.' => 'Порівнюється аналогічний результат станом на тепер.'],
        'present-perfect-q-v3-b1-11' => ['Її полегшення' => 'Полегшення після знахідки'],
        'present-perfect-q-v3-b2-07' => ['Оновлена панель показує сукупний результат кількох регіонів.' => 'Оновлена панель показує сукупний результат станом на тепер.'],
        'present-perfect-q-v3-c1-03' => ['проміжний результат рецензування' => 'проміжний результат оцінювання'],
        'present-perfect-q-v3-c1-07' => ['Сильніший висновок є наслідком нового результату дослідження.' => 'Сильніший висновок є наслідком щойно наданих доказів.'],
        'present-perfect-q-v3-c1-08' => ['результатом редакторської роботи' => 'результатом виконаної роботи'],
        'present-perfect-q-v3-c1-10' => ['з усією попередньою судовою практикою' => 'з усіма попередніми рішеннями'],
        'present-perfect-q-v3-c2-01' => ['Йдеться про те, як нові дані змінили теперішній стан аргументації.' => 'Йдеться про зміну теперішнього стану аргументації.'],
        'present-perfect-q-v3-c2-04' => ['всю попередню практику застосування доктрини' => 'всі попередні випадки застосування'],
    ];
}

function presentPerfectVerbHint(string $uuid, string $context): string
{
    $uuid = strtolower($uuid);
    $verb = presentPerfectUkrainianVerb($uuid);
    if ($verb === null) {
        return $context;
    }

    $context = trim((string) preg_replace('/^Дієслово: «[^»]+»\.\s*/u', '', trim($context)));
    $context = trim((string) preg_replace('/^Підмет у запитанні — .+?\.\s*/u', '', $context));
    foreach (presentPerfectSubjectWordingReplacements()[$uuid] ?? [] as $from => $to) {
        $context = str_replace($from, $to, $context);
    }

    $prefix = "Дієслово: «{$verb}».";
    $subject = presentPerfectHiddenSubject($uuid);
    if ($subject !== null) {
        $prefix .= " Підмет у запитанні — «{$subject}».";
    }

    return $context === '' ? $prefix : $prefix.' '.$context;
}

/**
 * Sentence Builder hints belong next to the actual V3 token and contain only
 * the Ukrainian infinitive; the view already supplies parentheses and styling.
 *
 * @return array{marker: string, verb: string}|null
 */
function presentPerfectQuestionPolyglotVerbHint(string $uuid): ?array
{
    if (! preg_match('/^present-perfect-q-poly-(a1|a2|b1|b2|c1|c2)-(\d{2})$/', strtolower($uuid), $matches)) {
        return null;
    }

    $markers = [
        'a1' => [3, 3, 4, 4, 3, null, 3, 3, 4, 4, 4, 3],
        'a2' => [3, 3, 4, 4, 3, null, 3, 3, 4, 4, 4, 3],
        'b1' => [3, 3, 5, 4, 3, null, 5, 3, 4, 4, 4, 3],
        'b2' => [3, 3, 6, 4, 3, null, 4, 3, 4, 4, 5, 3],
        'c1' => [3, 3, 6, 4, 3, null, 4, 4, 5, 5, 5, 3],
        'c2' => [6, 4, 3, 6, 4, null, 5, 4, 5, 5, 5, 4],
    ];

    $level = $matches[1];
    $number = (int) $matches[2];
    $marker = $markers[$level][$number - 1] ?? null;
    if (! is_int($marker)) {
        return null;
    }

    $standardUuid = sprintf('present-perfect-q-v3-%s-%02d', $level, $number);
    $verb = presentPerfectUkrainianVerb($standardUuid);
    if ($verb === null) {
        throw new RuntimeException("Missing Present Perfect verb for {$uuid}.");
    }

    return ['marker' => 'a'.$marker, 'verb' => $verb];
}
