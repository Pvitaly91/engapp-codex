<?php

declare(strict_types=1);

/**
 * Rebuild the Present Perfect forms test with unambiguous contexts, natural
 * collocations, Ukrainian hints, and genuine A1-C2 progression. The script is
 * deterministic and safe to run repeatedly.
 */

$root = dirname(__DIR__);
$standardPath = $root.'/database/seeders/V3/Tenses/PresentPerfect/PresentPerfectFormsAllLevelsV3Seeder/definition.json';
$polyglotPath = $root.'/database/seeders/V3/Polyglot/PolyglotPresentPerfectFormsAllLevelsLessonSeeder/definition.json';
$basicPath = $root.'/database/seeders/V3/Polyglot/PolyglotPresentPerfectBasicLessonSeeder/definition.json';

/** @return array<string, mixed> */
function readJsonFile(string $path): array
{
    return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}

/** @param array<string, mixed> $data */
function writeJsonFile(string $path, array $data): void
{
    file_put_contents(
        $path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL
    );
}

/**
 * @param list<string> $options
 * @return array{question: string, answer: string, options: list<string>, hint: string}
 */
function standardItem(string $question, string $answer, array $options, string $hint): array
{
    if (! str_contains($question, '{a1}')) {
        throw new RuntimeException("Missing {a1} in question: {$question}");
    }
    if (count($options) !== 5 || count(array_unique($options)) !== 5) {
        throw new RuntimeException("Each question must have five unique options: {$question}");
    }
    if ($options[0] !== $answer) {
        throw new RuntimeException("The correct answer must be the first authored option: {$question}");
    }

    return compact('question', 'answer', 'options', 'hint');
}

$content = [
    'A1' => [
        standardItem('I {a1} three exercises so far today.', 'have finished', ['have finished', 'has finished', 'have finish', 'having finished', 'am finished'], 'Зважте на підмет I та маркер so far; основне дієслово має стояти у формі V3.'),
        standardItem('We {a1} here since 2022.', 'have lived', ['have lived', 'has lived', 'have live', 'are lived', 'living'], 'Дія почалася у 2022 році й досі триває; зважте на форму після підмета we.'),
        standardItem('She {a1} her room twice this week, and the week is not over.', 'has cleaned', ['has cleaned', 'have cleaned', 'has clean', 'is cleaned', 'cleaning'], 'Період this week прямо позначено як незавершений; після допоміжного дієслова потрібна V3.'),
        standardItem('He {a1} at this shop since Monday.', 'has worked', ['has worked', 'have worked', 'has work', 'is worked', 'working'], 'Since Monday задає початкову точку дії, яка триває до тепер.'),
        standardItem('We {a1} to London before.', 'have been', ['have been', 'has been', 'have be', 'are been', 'have went'], 'Before описує життєвий досвід без конкретної дати; потрібна форма V3 неправильного дієслова be.'),
        standardItem('I still cannot find my key; I {a1} it.', 'have lost', ['have lost', 'has lost', 'have lose', 'am lost', 'losing'], 'Результат втрати важливий зараз; відрізніть V3 дієслова lose від його початкової форми.'),
        standardItem('She {a1} by plane before.', 'has never travelled', ['has never travelled', 'have never travelled', 'has never travel', 'is never travelled', 'never has travel'], 'Йдеться про відсутність досвіду до теперішнього моменту; зважте на підмет she.'),
        standardItem('We {a1} your book; here it is.', 'have just found', ['have just found', 'has just found', 'have just find', 'just have find', 'are just found'], 'Just підкреслює щойно завершену дію; дієслово find має неправильну форму V3.'),
        standardItem('I {a1} this video three times so far.', 'have watched', ['have watched', 'has watched', 'have watch', 'am watched', 'watching'], 'So far охоплює період до тепер; зважте на підмет I та форму V3.'),
        standardItem('My friends {a1} three train tickets so far.', 'have bought', ['have bought', 'has bought', 'have buy', 'are bought', 'have buyed'], 'Підмет стоїть у множині, а buy є неправильним дієсловом; не використовуйте вигадану форму buyed.'),
        standardItem('I {a1} the note already, and it is on your desk.', 'have written', ['have written', 'has written', 'have wrote', 'have write', 'am written'], 'Записка вже існує зараз; після допоміжного дієслова потрібна V3 дієслова write, а не Past Simple.'),
        standardItem('Tom {a1} his breakfast yet.', 'has not finished', ['has not finished', 'have not finished', 'has not finish', 'is not finished', 'did not finished'], 'Yet у запереченні показує, що дія досі не завершилася; зважте на підмет Tom.'),
    ],
    'A2' => [
        standardItem('{a1} a horse?', 'Have you ever ridden', ['Have you ever ridden', 'Has you ever ridden', 'Have you ever rode', 'Do you ever ridden', 'Are you ever ridden'], 'Ever запитує про досвід у будь-який момент до тепер; після підмета потрібна V3 дієслова ride.'),
        standardItem('They {a1} us since January.', 'have not visited', ['have not visited', 'has not visited', 'have not visit', 'are not visited', 'not have visited'], 'Since January пов’язує минулий початок із теперішнім; зважте на підмет they.'),
        standardItem('{a1} the online form yet?', 'Has Mia finished', ['Has Mia finished', 'Have Mia finished', 'Has Mia finish', 'Did Mia finished', 'Is Mia finished'], 'Yet у запитанні перевіряє наявність результату зараз; зважте на порядок слів і підмет Mia.'),
        standardItem('My brother {a1} from home for six months.', 'has worked', ['has worked', 'have worked', 'has work', 'is worked', 'working'], 'For six months позначає тривалість, що доходить до теперішнього моменту.'),
        standardItem('We {a1} snow before.', 'have never seen', ['have never seen', 'has never seen', 'have never saw', 'are never seen', 'never have see'], 'Never і before описують відсутність досвіду донині; потрібна V3 дієслова see.'),
        standardItem('Emma still cannot find her glasses because she {a1} them.', 'has lost', ['has lost', 'have lost', 'has lose', 'is lost', 'losing'], 'Окуляри досі не знайдені, тому результат дії актуальний зараз.'),
        standardItem('How many countries {a1} so far?', 'has Leo visited', ['has Leo visited', 'have Leo visited', 'has Leo visit', 'did Leo visited', 'is Leo visited'], 'So far обмежує кількість теперішнім моментом; у запитанні збережіть правильний порядок слів.'),
        standardItem('The children {a1} the missing puzzle piece.', 'have just found', ['have just found', 'has just found', 'have just find', 'are just found', 'just have find'], 'Just вказує на зовсім недавню знахідку; підмет children має значення множини.'),
        standardItem('I {a1} four documentaries this month, and the month is not over.', 'have watched', ['have watched', 'has watched', 'have watch', 'am watched', 'watching'], 'Місяць прямо позначено як незавершений; основне дієслово має стояти у формі V3.'),
        standardItem('Our class {a1} yet.', 'has not begun', ['has not begun', 'have not begun', 'has not began', 'is not begun', 'did not begun'], 'Yet показує, що очікувана дія досі не почалася; begin є неправильним дієсловом, тому не плутайте V3 з Past Simple.'),
        standardItem('{a1} the hotel?', 'Have your parents already booked', ['Have your parents already booked', 'Has your parents already booked', 'Have your parents already book', 'Did your parents already booked', 'Are your parents already booked'], 'Already перевіряє наявний результат; зважте на підмет у множині та порядок слів.'),
        standardItem('This is the first time I {a1} soup by myself.', 'have cooked', ['have cooked', 'has cooked', 'have cook', 'am cooked', 'cooking'], 'This is the first time підсумовує досвід до теперішнього моменту.'),
    ],
    'B1' => [
        standardItem('I {a1} Maya since primary school.', 'have known', ['have known', 'has known', 'have knew', 'am known', 'knowing'], 'Know описує стан, що триває від минулого до тепер; потрібна неправильна форма V3.'),
        standardItem('Local shops {a1} their prices for two years.', 'have not increased', ['have not increased', 'has not increased', 'have not increase', 'are not increased', 'not have increased'], 'For two years охоплює період, який ще триває; підмет shops має значення множини.'),
        standardItem('{a1} the printer yet?', 'Has the technician repaired', ['Has the technician repaired', 'Have the technician repaired', 'Has the technician repair', 'Did the technician repaired', 'Is the technician repaired'], 'Yet запитує, чи є очікуваний результат зараз; зважте на інверсію в запитанні.'),
        standardItem('We {a1} five tasks so far this morning.', 'have completed', ['have completed', 'has completed', 'have complete', 'are completed', 'completing'], 'So far this morning означає незавершений проміжок до тепер.'),
        standardItem('This is the best film I {a1}.', 'have ever seen', ['have ever seen', 'has ever seen', 'have ever saw', 'am ever seen', 'ever have see'], 'Найвищий ступінь оцінює весь досвід до тепер; потрібна V3 дієслова see.'),
        standardItem('I cannot access the account because I {a1} my password.', 'have forgotten', ['have forgotten', 'has forgotten', 'have forgot', 'am forgotten', 'forgetting'], 'Забутий пароль створює теперішню проблему; forget є неправильним дієсловом, тому не плутайте V3 з Past Simple.'),
        standardItem('How long {a1} in Berlin?', 'has Anna lived', ['has Anna lived', 'have Anna lived', 'has Anna live', 'did Anna lived', 'is Anna lived'], 'How long запитує про тривалість до сьогодні; врахуйте інверсію з підметом Anna.'),
        standardItem('The city council {a1} a new sports centre.', 'has recently opened', ['has recently opened', 'have recently opened', 'has recently open', 'is recently opened', 'recently has open'], 'Recently пов’язує недавню подію з поточною ситуацією; council тут є одним органом.'),
        standardItem('I {a1} a reply yet.', 'have not received', ['have not received', 'has not received', 'have not receive', 'am not received', 'did not received'], 'Yet означає відсутність очікуваного результату до тепер.'),
        standardItem('{a1} enough food so far?', 'Have the volunteers brought', ['Have the volunteers brought', 'Has the volunteers brought', 'Have the volunteers bring', 'Did the volunteers brought', 'Are the volunteers brought'], 'So far запитує про проміжний підсумок; bring має неправильну форму V3.'),
        standardItem('Sales {a1} by 15 percent since April.', 'have risen', ['have risen', 'has risen', 'have rose', 'are risen', 'rising'], 'Since April задає точку відліку зміни; rise є неправильним дієсловом, тому не плутайте V3 з Past Simple.'),
        standardItem('It is the second time the alarm {a1} today.', 'has gone off', ['has gone off', 'have gone off', 'has went off', 'is gone off', 'has go off'], 'The second time підсумовує повторення в незавершеному періоді today.'),
    ],
    'B2' => [
        standardItem('Researchers {a1} three new species over the past decade.', 'have identified', ['have identified', 'has identified', 'have identify', 'are identified', 'identifying'], 'Over the past decade включає період до тепер; підмет researchers стоїть у множині.'),
        standardItem('The sales teams {a1} their annual targets so far.', 'have not met', ['have not met', 'has not met', 'have not meet', 'are not met', 'did not met'], 'So far показує, що підсумок ще не остаточний; meet є неправильним дієсловом, тому потрібна його форма V3.'),
        standardItem('How much {a1} to date?', 'has the project cost', ['has the project cost', 'have the project cost', 'has the project costed', 'did the project costed', 'is the project cost'], 'To date означає «станом на зараз»; врахуйте порядок слів і дієслово з однаковими формами V1, V2 та V3.'),
        standardItem('The manager {a1} remotely since the office closed.', 'has worked', ['has worked', 'have worked', 'has work', 'is worked', 'working'], 'Основна дія триває від моменту, позначеного Past Simple у підрядній частині.'),
        standardItem('{a1} to revise an important decision?', 'Have you ever had', ['Have you ever had', 'Has you ever had', 'Have you ever have', 'Did you ever had', 'Are you ever had'], 'Ever стосується всього попереднього досвіду; після підмета потрібна V3 дієслова have.'),
        standardItem('The road is still closed because the storm {a1} the bridge.', 'has damaged', ['has damaged', 'have damaged', 'has damage', 'is damaged', 'damaging'], 'Пошкодження має видимий наслідок: дорога досі закрита.'),
        standardItem('No member of the team {a1} a deadline this tight before.', 'has ever faced', ['has ever faced', 'have ever faced', 'has ever face', 'is ever faced', 'ever has face'], 'Before охоплює професійний досвід донині; головне слово підмета member має форму однини.'),
        standardItem('The committee {a1} its final recommendation.', 'has just published', ['has just published', 'have just published', 'has just publish', 'is just published', 'just has publish'], 'Just позначає щойно оприлюднений результат; committee тут є одним органом.'),
        standardItem('This is the most convincing proposal we {a1} so far.', 'have received', ['have received', 'has received', 'have receive', 'are received', 'have receiving'], 'Найвищий ступінь і so far охоплюють усі пропозиції до теперішнього моменту.'),
        standardItem('Customer complaints {a1} steadily since the update.', 'have fallen', ['have fallen', 'has fallen', 'have fell', 'are fallen', 'falling'], 'Since the update задає початок поточної тенденції; fall є неправильним дієсловом, тому не плутайте V3 з Past Simple.'),
        standardItem('Why {a1} yet?', 'has the supplier not responded', ['has the supplier not responded', 'have the supplier not responded', 'has the supplier not respond', 'did the supplier not responded', 'is the supplier not responded'], 'У прямому заперечному запитанні врахуйте порядок слів і маркер yet.'),
        standardItem('By now, both departments {a1} on the main terms.', 'have agreed', ['have agreed', 'has agreed', 'have agree', 'have agreeing', 'just have agree'], 'By now підсумовує результат, досягнутий на цей момент; both departments — множина.'),
    ],
    'C1' => [
        standardItem('The evidence accumulated so far {a1} the original assumption.', 'has challenged', ['has challenged', 'have challenged', 'has challenge', 'have challenge', 'has challenging'], 'So far обмежує висновок наявним масивом доказів; головне слово evidence є незлічуваним.'),
        standardItem('Few researchers {a1} this issue in sufficient depth to date.', 'have addressed', ['have addressed', 'has addressed', 'have address', 'are addressed', 'have addressing'], 'To date підсумовує стан досліджень до сьогодні; підмет researchers стоїть у множині.'),
        standardItem('To our knowledge, no previous study {a1} this effect under natural conditions.', 'has demonstrated', ['has demonstrated', 'have demonstrated', 'has demonstrate', 'is demonstrated', 'has demonstrating'], 'Початок речення охоплює весь відомий дослідницький досвід; study має форму однини.'),
        standardItem('The policy {a1} unchanged ever since it was introduced.', 'has remained', ['has remained', 'have remained', 'has remain', 'is remained', 'has remaining'], 'Ever since поєднує початкову подію з теперішнім станом.'),
        standardItem('This is only the third time the court {a1} such an appeal.', 'has heard', ['has heard', 'have heard', 'has hear', 'is heard', 'has hearing'], 'The third time підсумовує повторюваний досвід до тепер; hear має неправильну V3.'),
        standardItem('The latest figures suggest that demand {a1} over the past quarter.', 'has stabilised', ['has stabilised', 'have stabilised', 'has stabilise', 'is stabilised', 'has stabilising'], 'Період over the past quarter доходить до сьогодні; demand є незлічуваним іменником.'),
        standardItem('How many peer-review panels {a1} on so far?', 'has the researcher served', ['has the researcher served', 'have the researcher served', 'has the researcher serve', 'did the researcher served', 'is the researcher served'], 'So far стосується накопиченого досвіду; врахуйте інверсію з підметом researcher.'),
        standardItem('The two research teams {a1} on a shared protocol.', 'have just agreed', ['have just agreed', 'has just agreed', 'have just agree', 'just have agree', 'have just agreeing'], 'Just позначає щойно досягнуту домовленість; the two teams має значення множини.'),
        standardItem('The board {a1} how it reached the decision.', 'has not yet disclosed', ['has not yet disclosed', 'have not yet disclosed', 'has not yet disclose', 'is not yet disclosed', 'has yet not disclose'], 'Not yet показує, що очікуване розкриття досі не відбулося; board тут є одним органом.'),
        standardItem('Recent advances in imaging {a1} changes that older scanners could not detect.', 'have shown', ['have shown', 'has shown', 'have showed', 'are shown', 'have show'], 'Головне слово advances має форму множини, а наслідок актуальний зараз; show є неправильним дієсловом.'),
        standardItem('Not until now {a1} enough data to test the hypothesis.', 'have analysts had', ['have analysts had', 'has analysts had', 'analysts have had', 'have analysts have', 'did analysts had'], 'Після винесеного вперед Not until now потрібна інверсія допоміжного дієслова й підмета.'),
        standardItem('It is the first time that such a broad coalition {a1} without government support.', 'has been formed', ['has been formed', 'have been formed', 'has formed', 'has been form', 'is been formed'], 'Дія подається в пасиві як досвід до тепер; coalition має форму однини.'),
    ],
    'C2' => [
        standardItem('So far in the inquiry, several assumptions {a1} to be unfounded.', 'have proved', ['have proved', 'has proved', 'have prove', 'have been prove', 'have proving'], 'Це проміжний висновок незавершеного розслідування; assumptions стоїть у множині.'),
        standardItem('The long-term implications of the reform {a1} increasingly difficult to ignore over the past year.', 'have become', ['have become', 'has become', 'have became', 'are become', 'have becoming'], 'Головне слово implications має форму множини, а період доходить до сьогодні.'),
        standardItem('No credible explanation {a1} for all the available evidence.', 'has yet accounted', ['has yet accounted', 'have yet accounted', 'has yet account', 'is yet accounted', 'has yet accounting'], 'No ... yet підкреслює відсутність прийнятного пояснення донині; explanation має форму однини.'),
        standardItem('This is the strongest indication we {a1} thus far that the two processes are linked.', 'have obtained', ['have obtained', 'has obtained', 'have obtain', 'are obtained', 'have obtaining'], 'Thus far охоплює всі отримані свідчення до цього моменту; підмет we вимагає форми множини.'),
        standardItem('Rarely {a1} under such sustained pressure at so early a stage.', 'have policymakers been', ['have policymakers been', 'has policymakers been', 'policymakers have been', 'have policymakers be', 'did policymakers been'], 'Після Rarely на початку речення потрібна інверсія; be має неправильну форму V3.'),
        standardItem('Until now, the debate {a1} as a choice between efficiency and fairness.', 'has largely been framed', ['has largely been framed', 'have largely been framed', 'has largely framed', 'has largely been frame', 'is largely been framed'], 'Until now задає поточну межу, а дія є пасивною; debate має форму однини.'),
        standardItem('The chair is the only mediator who {a1} support from every faction.', 'has ever won', ['has ever won', 'have ever won', 'has ever win', 'is ever won', 'has ever winning'], 'Узгодьте присудок у підрядній частині з the only mediator; win має неправильну V3.'),
        standardItem('A revised protocol {a1} following months of consultation.', 'has just been adopted', ['has just been adopted', 'have just been adopted', 'has just adopted', 'has just been adopt', 'is just been adopted'], 'Just позначає недавню подію, а protocol є об’єктом дії, тому потрібен пасив.'),
        standardItem('How far {a1} the prevailing interpretation?', 'has the new evidence undermined', ['has the new evidence undermined', 'have the new evidence undermined', 'has the new evidence undermine', 'did the new evidence undermined', 'is the new evidence undermined'], 'У прямому запитанні після How far потрібна інверсія; evidence є незлічуваним іменником.'),
        standardItem('Neither side {a1} a definition that all participants can accept.', 'has put forward', ['has put forward', 'have put forward', 'has putted forward', 'is put forward', 'has putting forward'], 'У формальному контексті Neither side узгоджується як однина; put є неправильним дієсловом.'),
        standardItem('For the third time this year, negotiations {a1} before an agreement could be reached.', 'have broken down', ['have broken down', 'has broken down', 'have broke down', 'have break down', 'have been break down'], 'Маркер підсумовує повторення в незавершеному році; break має неправильну форму V3.'),
        standardItem('Never before {a1} so much uncertainty across the sector.', 'has a single ruling generated', ['has a single ruling generated', 'have a single ruling generated', 'a single ruling has generated', 'has a single ruling generate', 'did a single ruling generated'], 'Після Never before на початку речення потрібна інверсія; підмет a single ruling має форму однини.'),
    ],
];

$ukPrompts = [
    'A1' => [
        'Сьогодні я вже виконав три вправи.',
        'Ми живемо тут із 2022 року.',
        'Цього тижня вона вже двічі прибрала свою кімнату, і тиждень ще не закінчився.',
        'Він працює в цій крамниці з понеділка.',
        'Ми раніше бували в Лондоні.',
        'Я досі не можу знайти ключ — я його загубив.',
        'Вона ще ніколи не літала літаком.',
        'Ми щойно знайшли вашу книжку — ось вона.',
        'Я вже тричі переглянув це відео.',
        'Мої друзі вже купили три квитки на потяг.',
        'Я вже написав записку, і вона лежить на вашому столі.',
        'Том ще не доїв свій сніданок.',
    ],
    'A2' => [
        'Ти коли-небудь їздив верхи на коні?',
        'Вони не відвідували нас із січня.',
        'Мія вже заповнила онлайн-форму?',
        'Мій брат працює з дому вже шість місяців.',
        'Ми ще ніколи не бачили снігу.',
        'Емма досі не може знайти окуляри, бо загубила їх.',
        'Скільки країн Лео вже відвідав?',
        'Діти щойно знайшли загублену деталь пазла.',
        'Цього місяця я вже переглянув чотири документальні фільми, і місяць ще не закінчився.',
        'Наш урок ще не почався.',
        'Твої батьки вже забронювали готель?',
        'Я вперше самостійно приготував суп.',
    ],
    'B1' => [
        'Я знаю Майю ще з початкової школи.',
        'Місцеві крамниці не підвищували ціни вже два роки.',
        'Технік уже полагодив принтер?',
        'Сьогодні вранці ми вже виконали п’ять завдань.',
        'Це найкращий фільм, який я коли-небудь бачив.',
        'Я не можу увійти в обліковий запис, бо забув пароль.',
        'Як довго Анна живе в Берліні?',
        'Міська рада нещодавно відкрила новий спортивний центр.',
        'Я ще не отримав відповіді.',
        'Волонтери вже принесли достатньо їжі?',
        'Із квітня продажі зросли на 15 відсотків.',
        'Сьогодні сигналізація спрацювала вже вдруге.',
    ],
    'B2' => [
        'За останнє десятиліття дослідники виявили три нові види.',
        'Відділи продажів поки що не досягли своїх річних цілей.',
        'У скільки вже обійшовся проєкт?',
        'Менеджер працює віддалено відтоді, як закрився офіс.',
        'Тобі коли-небудь доводилося переглядати важливе рішення?',
        'Дорога досі перекрита, бо буря пошкодила міст.',
        'Жоден член команди ще ніколи не стикався з настільки стислим терміном.',
        'Комітет щойно оприлюднив остаточну рекомендацію.',
        'Це найпереконливіша пропозиція з усіх, які ми отримали досі.',
        'Від часу оновлення кількість скарг клієнтів стабільно зменшується.',
        'Чому постачальник досі не відповів?',
        'На цей момент обидва відділи погодили основні умови.',
    ],
    'C1' => [
        'Накопичені досі докази поставили під сумнів початкове припущення.',
        'На сьогодні мало хто з дослідників розглянув це питання достатньо глибоко.',
        'Наскільки нам відомо, жодне попереднє дослідження не продемонструвало цього ефекту в природних умовах.',
        'Політика залишається незмінною від самого моменту її запровадження.',
        'Це лише третій раз, коли суд розглянув таку апеляцію.',
        'Останні показники свідчать, що за останній квартал попит стабілізувався.',
        'У скількох експертних комісіях дослідник уже працював?',
        'Дві дослідницькі групи щойно узгодили спільний протокол.',
        'Рада директорів ще не оприлюднила, як саме вона ухвалила рішення.',
        'Останні досягнення у візуалізації виявили зміни, яких старі сканери не могли зафіксувати.',
        'Лише тепер аналітики отримали достатньо даних, щоб перевірити гіпотезу.',
        'Це перший випадок, коли таку широку коаліцію сформовано без підтримки уряду.',
    ],
    'C2' => [
        'На цьому етапі розслідування кілька припущень виявилися необґрунтованими.',
        'За останній рік довгострокові наслідки реформи дедалі важче ігнорувати.',
        'Жодне переконливе пояснення досі не врахувало всіх наявних доказів.',
        'Це найвагоміше свідчення зв’язку між двома процесами, яке ми отримали досі.',
        'Політики рідко опинялися під таким тривалим тиском на настільки ранньому етапі.',
        'До цього часу дискусію здебільшого подавали як вибір між ефективністю та справедливістю.',
        'Голова — єдиний посередник, який коли-небудь здобував підтримку всіх фракцій.',
        'Переглянутий протокол щойно ухвалили після кількох місяців консультацій.',
        'Якою мірою нові докази підірвали панівне тлумачення?',
        'Жодна зі сторін не запропонувала визначення, яке могли б прийняти всі учасники.',
        'Уже втретє цього року переговори зірвалися, перш ніж сторони змогли досягти угоди.',
        'Ніколи раніше одне судове рішення не породжувало стільки невизначеності в усьому секторі.',
    ],
];

/** @return list<string> */
function sentenceTokens(string $sentence): array
{
    $withoutPunctuation = preg_replace('/[.,!?;:]+/u', '', $sentence);
    if (! is_string($withoutPunctuation)) {
        throw new RuntimeException('Unable to normalize sentence punctuation.');
    }

    return array_values(array_filter(
        preg_split('/\s+/u', trim($withoutPunctuation)) ?: [],
        static fn (string $token): bool => $token !== ''
    ));
}

/**
 * @param list<string> $correctTokens
 * @param list<string> $authoredOptions
 * @return list<string>
 */
function composeOptions(array $correctTokens, array $authoredOptions): array
{
    // Correct-token multiplicity is intentional: some compose surfaces render
    // each occurrence as a separate tile.
    $options = $correctTokens;
    $seen = [];
    foreach ($correctTokens as $token) {
        $seen[mb_strtolower($token)] = true;
    }

    foreach ($authoredOptions as $option) {
        foreach (sentenceTokens($option) as $token) {
            $normalized = mb_strtolower($token);
            if (isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $options[] = $token;
        }
    }

    $distractorCount = count($options) - count($correctTokens);
    foreach (['do', 'does', 'did', 'was', 'were', 'had', 'been', 'being'] as $fallback) {
        if ($distractorCount >= 4) {
            break;
        }

        $normalized = mb_strtolower($fallback);
        if (isset($seen[$normalized])) {
            continue;
        }

        $seen[$normalized] = true;
        $options[] = $fallback;
        ++$distractorCount;
    }

    return $options;
}

/** @param list<string> $tokens @return array<string, string> */
function markerAnswers(array $tokens): array
{
    $answers = [];
    foreach ($tokens as $index => $token) {
        $answers['a'.($index + 1)] = $token;
    }

    return $answers;
}

$standard = readJsonFile($standardPath);
$standardByUuid = [];
foreach ($standard['questions'] as $index => $question) {
    $standardByUuid[(string) $question['uuid']] = $index;
}

foreach ($content as $level => $items) {
    if (count($items) !== 12) {
        throw new RuntimeException("Expected 12 {$level} questions, got ".count($items));
    }

    foreach ($items as $offset => $item) {
        $number = str_pad((string) ($offset + 1), 2, '0', STR_PAD_LEFT);
        $uuid = 'present-perfect-forms-v3-'.strtolower($level).'-'.$number;
        $index = $standardByUuid[$uuid] ?? null;
        if (! is_int($index)) {
            throw new RuntimeException("Missing standard question {$uuid}");
        }

        $question =& $standard['questions'][$index];
        $question['question'] = $item['question'];
        $question['markers']['a1']['answer'] = $item['answer'];
        $question['markers']['a1']['options'] = $item['options'];
        $question['markers']['a1']['verb_hint'] = $item['hint'];
        $question['variants'] = [$item['question']];
        unset($question);
    }
}

writeJsonFile($standardPath, $standard);

echo 'Updated 72 standard Present Perfect forms questions.'.PHP_EOL;

$polyglot = readJsonFile($polyglotPath);
$polyglotByUuid = [];
foreach ($polyglot['questions'] as $index => $question) {
    $polyglotByUuid[(string) $question['uuid']] = $index;
}

foreach ($content as $level => $items) {
    if (count($ukPrompts[$level] ?? []) !== 12) {
        throw new RuntimeException("Expected 12 Ukrainian {$level} prompts.");
    }

    foreach ($items as $offset => $item) {
        $number = str_pad((string) ($offset + 1), 2, '0', STR_PAD_LEFT);
        $uuid = 'present-perfect-forms-poly-'.strtolower($level).'-'.$number;
        $index = $polyglotByUuid[$uuid] ?? null;
        if (! is_int($index)) {
            throw new RuntimeException("Missing polyglot question {$uuid}");
        }

        $fullSentence = str_replace('{a1}', $item['answer'], $item['question']);
        $correctTokens = sentenceTokens($fullSentence);
        $distractorOptions = array_values(array_slice($item['options'], 1));

        $question =& $polyglot['questions'][$index];
        $question['question'] = $ukPrompts[$level][$offset];
        $question['answers'] = markerAnswers($correctTokens);
        $question['options'] = composeOptions($correctTokens, $distractorOptions);
        $question['variants'] = [];
        unset($question);
    }
}

writeJsonFile($polyglotPath, $polyglot);

echo 'Updated 72 Present Perfect Sentence Builder questions.'.PHP_EOL;

$basic = readJsonFile($basicPath);
$updatedBasicPrompt = false;
foreach ($basic['questions'] as &$question) {
    if (($question['uuid'] ?? null) !== 'polyglot-present-perfect-basic-a2-q21') {
        continue;
    }

    $question['question'] = 'Що ти зробив сьогодні?';
    $updatedBasicPrompt = true;
}
unset($question);

if (! $updatedBasicPrompt) {
    throw new RuntimeException('Missing basic Present Perfect A2 question q21.');
}

writeJsonFile($basicPath, $basic);

echo 'Updated Present Perfect Basic A2 wording and retained authored question casing.'.PHP_EOL;
