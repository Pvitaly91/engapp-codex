<?php

declare(strict_types=1);

/**
 * Rebuild Present Perfect negatives with contextual, unambiguous content and
 * genuine A1-C2 progression. The script is deterministic and idempotent.
 */

$root = dirname(__DIR__);
$standardPath = $root.'/database/seeders/V3/Tenses/PresentPerfect/PresentPerfectNegativesAllLevelsV3Seeder/definition.json';
$composePath = $root.'/database/seeders/V3/Polyglot/PolyglotPresentPerfectNegativesAllLevelsLessonSeeder/definition.json';

/** @return array<string, mixed> */
function ppNegReadJson(string $path): array
{
    return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}

/** @param array<string, mixed> $data */
function ppNegWriteJson(string $path, array $data): void
{
    file_put_contents(
        $path,
        json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ).PHP_EOL
    );
}

/**
 * @param list<string> $options
 * @return array{question: string, answer: string, options: list<string>, hint: string}
 */
function ppNegItem(string $question, string $answer, array $options, string $hint): array
{
    if (substr_count($question, '{a1}') !== 1) {
        throw new RuntimeException("Question must contain {a1} exactly once: {$question}");
    }

    if (count($options) !== 5 || count(array_unique($options)) !== 5) {
        throw new RuntimeException("Question must have five unique options: {$question}");
    }

    if ($options[0] !== $answer) {
        throw new RuntimeException("Correct answer must be the first authored option: {$question}");
    }

    return compact('question', 'answer', 'options', 'hint');
}

$content = [
    'A1' => [
        ppNegItem('I {a1} my homework yet.', 'have not finished', ['have not finished', 'has not finished', 'have not finish', 'have finished not', 'do not finished'], 'Yet показує, що очікуваного результату досі немає; зважте на підмет I та форму V3.'),
        ppNegItem('Mia {a1} breakfast yet.', 'has not eaten', ['has not eaten', 'have not eaten', 'has not ate', 'has eaten not', 'is not eaten'], 'Підмет Mia має значення однини, а основне дієслово повинно стояти у третій формі.'),
        ppNegItem('We {a1} our bags yet.', 'have not packed', ['have not packed', 'has not packed', 'have not pack', 'have packed not', 'are not packed'], 'Маркер yet вказує на незавершену дію; перевірте порядок заперечної конструкції.'),
        ppNegItem('I {a1} snow before.', 'have never seen', ['have never seen', 'has never seen', 'have never saw', 'have not never seen', 'never have saw'], 'Never уже надає реченню заперечного значення; після допоміжного дієслова потрібна V3.'),
        ppNegItem('Tom {a1} the message yet.', 'has not sent', ['has not sent', 'have not sent', 'has not send', 'has sent not', 'does not sent'], 'Yet показує відсутність очікуваного результату; узгодьте допоміжне дієслово з Tom.'),
        ppNegItem('She cannot open the file because she {a1} the password.', 'has not found', ['has not found', 'have not found', 'has not find', 'has found not', 'is not found'], 'Друга частина пояснює теперішню проблему; зверніть увагу на третю форму неправильного дієслова.'),
        ppNegItem('We {a1} the tickets yet.', "haven't bought", ["haven't bought", "hasn't bought", "haven't buy", "haven't buying", "aren't bought"], 'Потрібна коротка заперечна форма для підмета we та третя форма смислового дієслова.'),
        ppNegItem('Lina {a1} the email yet.', "hasn't read", ["hasn't read", "haven't read", "hasn't reads", "hasn't reading", "isn't read"], 'Зважте на підмет в однині та форму основного дієслова після короткої заперечної конструкції.'),
        ppNegItem('I {a1} this exercise before.', 'have not done', ['have not done', 'has not done', 'have not did', 'have done not', 'am not done'], 'Before пов’язує відсутність досвіду з теперішнім моментом; перевірте форму неправильного дієслова.'),
        ppNegItem('Leo {a1} sushi before.', 'has never tried', ['has never tried', 'have never tried', 'has never try', 'has not never tried', 'never has try'], 'Never стоїть усередині конструкції й уже виражає заперечення; зважте на підмет Leo.'),
        ppNegItem('They {a1} our reply yet.', 'have not received', ['have not received', 'has not received', 'have not receive', 'have received not', 'are not received'], 'Yet показує, що відповідь очікують досі; підмет they потребує форми множини.'),
        ppNegItem('Eva is still here because she {a1} home yet.', "hasn't gone", ["hasn't gone", "haven't gone", "hasn't went", "hasn't go", "doesn't gone"], 'Теперішня ситуація пояснюється незавершеною дією; потрібна третя форма неправильного дієслова.'),
    ],
    'A2' => [
        ppNegItem('You {a1} the online form yet.', 'have not completed', ['have not completed', 'has not completed', 'have not complete', 'have completed not', 'are not completed'], 'Yet показує, що форму досі не заповнено; зважте на підмет you та форму V3.'),
        ppNegItem('Daniel {a1} his room yet.', 'has not cleaned', ['has not cleaned', 'have not cleaned', 'has not clean', 'has cleaned not', 'is not cleaned'], 'Підмет Daniel має значення однини; маркер yet вказує на відсутність результату.'),
        ppNegItem('They {a1} the correct address yet.', 'have not found', ['have not found', 'has not found', 'have not find', 'have found not', 'are not found'], 'Зважте на порядок заперечення та третю форму неправильного дієслова.'),
        ppNegItem('My parents {a1} abroad before.', 'have never travelled', ['have never travelled', 'has never travelled', 'have never travel', 'have not never travelled', 'never have travel'], 'Before описує досвід до тепер, а never уже виражає його відсутність.'),
        ppNegItem('Nora {a1} the parcel yet.', 'has not sent', ['has not sent', 'have not sent', 'has not send', 'has sent not', 'does not sent'], 'Посилку все ще очікують; узгодьте допоміжне дієслово з Nora та використайте V3.'),
        ppNegItem('Ben cannot answer the question because he {a1} the instructions yet.', 'has not read', ['has not read', 'have not read', 'has not reads', 'has read not', 'is not read'], 'Причина має актуальний наслідок зараз; зважте на підмет he та незмінну третю форму неправильного дієслова.'),
        ppNegItem('We {a1} a table for tonight yet.', "haven't booked", ["haven't booked", "hasn't booked", "haven't book", "haven't booking", "didn't booked"], 'Tonight і yet показують, що очікуваного результату поки немає; потрібна коротка форма для we.'),
        ppNegItem('Ella {a1} the new episode yet.', "hasn't watched", ["hasn't watched", "haven't watched", "hasn't watch", "hasn't watching", "isn't watched"], 'Маркер yet стоїть наприкінці; перевірте узгодження з Ella та форму основного дієслова.'),
        ppNegItem('We {a1} this route before.', 'have not taken', ['have not taken', 'has not taken', 'have not took', 'have taken not', 'are not taken'], 'Before вказує на попередній досвід; потрібна третя форма неправильного дієслова.'),
        ppNegItem('Tom {a1} an electric car before.', 'has never driven', ['has never driven', 'have never driven', 'has never drove', 'has not never driven', 'never has drive'], 'Never уже робить значення заперечним; зважте на підмет Tom і третю форму.'),
        ppNegItem('The children {a1} their test results yet.', 'have not received', ['have not received', 'has not received', 'have not receive', 'have received not', 'are not received'], 'Children має значення множини, а yet показує, що результатів усе ще немає.'),
        ppNegItem('Mark is still at work because he {a1} the office yet.', "hasn't left", ["hasn't left", "haven't left", "hasn't leave", "hasn't leaving", "isn't left"], 'Still at work описує теперішній наслідок; після короткої форми потрібна V3.'),
    ],
    'B1' => [
        ppNegItem('I cannot send the report because I {a1} it yet.', 'have not finished', ['have not finished', 'has not finished', 'have not finish', 'have finished not', 'do not finished'], 'Незавершена дія пояснює теперішню неможливість; зважте на підмет I та маркер yet.'),
        ppNegItem('The printer is still out of order because the technician {a1} it yet.', 'has not repaired', ['has not repaired', 'have not repaired', 'has not repair', 'has repaired not', 'is not repaired'], 'Теперішній стан принтера є наслідком незавершеної дії; головне слово підмета technician стоїть в однині.'),
        ppNegItem('We {a1} a final decision yet.', 'have not made', ['have not made', 'has not made', 'have not make', 'have made not', 'are not made'], 'Yet показує, що рішення досі немає; перевірте третю форму неправильного дієслова.'),
        ppNegItem('Our lead engineer {a1} this kind of problem before.', 'has never faced', ['has never faced', 'have never faced', 'has never face', 'has not never faced', 'never has face'], 'Never описує відсутність попереднього досвіду; головне слово підмета engineer стоїть в однині.'),
        ppNegItem('The supplier {a1} to our complaint yet.', 'has not responded', ['has not responded', 'have not responded', 'has not respond', 'has responded not', 'is not responded'], 'Відповіді очікують до цього моменту; узгодьте конструкцію з головним словом supplier.'),
        ppNegItem('Anna cannot open the attachment because she {a1} the access code.', 'has not received', ['has not received', 'have not received', 'has not receive', 'has received not', 'is not received'], 'Причина створює проблему зараз; після допоміжного дієслова потрібна V3.'),
        ppNegItem('The review cannot begin because we {a1} the files yet.', "haven't downloaded", ["haven't downloaded", "hasn't downloaded", "haven't download", "haven't downloading", "aren't downloaded"], 'Неможливість розпочати перевірку є теперішнім наслідком; зважте на коротку форму для we.'),
        ppNegItem('The editor {a1} the revised article yet.', "hasn't reviewed", ["hasn't reviewed", "haven't reviewed", "hasn't review", "hasn't reviewing", "isn't reviewed"], 'Yet вказує на відсутність очікуваного результату; підмет editor має форму однини.'),
        ppNegItem('I {a1} a complaint like this before.', 'have not handled', ['have not handled', 'has not handled', 'have not handle', 'have handled not', 'am not handled'], 'Before охоплює досвід до тепер; перевірте порядок заперечення та форму V3.'),
        ppNegItem('Maya {a1} such a tight deadline before.', 'has never had', ['has never had', 'have never had', 'has never have', 'has not never had', 'never has have'], 'Never показує відсутність такого досвіду; зважте на підмет Maya та неправильне дієслово.'),
        ppNegItem('The volunteers {a1} the promised funding yet.', 'have not received', ['have not received', 'has not received', 'have not receive', 'have received not', 'are not received'], 'Підмет volunteers має значення множини, а yet позначає очікуваний результат.'),
        ppNegItem('Anna cannot publish the report because her manager {a1} it yet.', "hasn't approved", ["hasn't approved", "haven't approved", "hasn't approve", "hasn't approving", "isn't approved"], 'Підрядна частина пояснює поточну перешкоду; узгодьте коротку форму з manager.'),
    ],
    'B2' => [
        ppNegItem('The sales teams {a1} their annual targets so far.', 'have not met', ['have not met', 'has not met', 'have not meet', 'have met not', 'are not met'], 'So far підсумовує незавершений період; підмет teams має значення множини.'),
        ppNegItem('The service remains unstable because the latest update {a1} the underlying fault.', 'has not resolved', ['has not resolved', 'have not resolved', 'has not resolve', 'has resolved not', 'is not resolved'], 'Теперішня нестабільність є наслідком відсутнього результату; головне слово update стоїть в однині.'),
        ppNegItem('The two departments {a1} a workable compromise yet.', 'have not reached', ['have not reached', 'has not reached', 'have not reach', 'have reached not', 'are not reached'], 'Yet показує, що домовленості досі немає; підмет the two departments має форму множини.'),
        ppNegItem('Our analysts {a1} a discrepancy of this scale before.', 'have never encountered', ['have never encountered', 'has never encountered', 'have never encounter', 'have not never encountered', 'never have encounter'], 'Before охоплює професійний досвід до тепер, а never уже виражає заперечення.'),
        ppNegItem('The contractor {a1} the revised schedule yet.', 'has not submitted', ['has not submitted', 'have not submitted', 'has not submit', 'has submitted not', 'is not submitted'], 'Оновлений графік усе ще очікують; узгодьте конструкцію з contractor.'),
        ppNegItem('The supporting evidence {a1} independently yet.', 'has not been verified', ['has not been verified', 'have not been verified', 'has not verified', 'has not been verify', 'is not been verified'], 'Evidence є незлічуваним підметом і об’єктом перевірки; потрібна заперечна пасивна будова.'),
        ppNegItem('The investigators {a1} an alternative explanation yet.', "haven't ruled out", ["haven't ruled out", "hasn't ruled out", "haven't rule out", "haven't ruled", "aren't ruled out"], 'Йдеться про можливість, яку досі продовжують розглядати; зважте на підмет у множині.'),
        ppNegItem('The project director {a1} the recovery plan yet.', "hasn't approved", ["hasn't approved", "haven't approved", "hasn't approve", "hasn't approving", "isn't approved"], 'Yet показує відсутність офіційного результату; головне слово підмета director стоїть в однині.'),
        ppNegItem('We {a1} resistance on this scale before.', 'have not encountered', ['have not encountered', 'has not encountered', 'have not encounter', 'have encountered not', 'are not encountered'], 'Before пов’язує ситуацію з усім попереднім досвідом; перевірте порядок заперечення.'),
        ppNegItem('The confidential report {a1} outside the department.', 'has never been released', ['has never been released', 'have never been released', 'has never released', 'has never been release', 'is never been released'], 'Звіт є об’єктом дії, а never описує відсутність такої події до тепер.'),
        ppNegItem('The agencies {a1} the requested data yet.', 'have not disclosed', ['have not disclosed', 'has not disclosed', 'have not disclose', 'have disclosed not', 'are not disclosed'], 'Підмет agencies стоїть у множині, а yet показує, що дані все ще не оприлюднено.'),
        ppNegItem('The project cannot proceed because the final permit {a1}.', "hasn't been granted", ["hasn't been granted", "haven't been granted", "hasn't granted", "hasn't been grant", "isn't been granted"], 'Дозвіл є об’єктом дії, а його відсутність блокує проєкт зараз; потрібна пасивна будова.'),
    ],
    'C1' => [
        ppNegItem('The reviewers {a1} the central assumption in any of their responses.', 'have not addressed', ['have not addressed', 'has not addressed', 'have not address', 'have addressed not', 'are not addressed'], 'Період охоплює всі наявні відповіді до тепер; підмет reviewers має значення множини.'),
        ppNegItem('The proposed model {a1} all the observed anomalies so far.', 'has not accounted for', ['has not accounted for', 'have not accounted for', 'has not account for', 'has accounted not for', 'is not accounted for'], 'So far обмежує висновок наявними спостереженнями; зважте на підмет model та незмінну частку for у сполуці account for.'),
        ppNegItem('Not once {a1} the findings independently.', 'have other teams replicated', ['have other teams replicated', 'has other teams replicated', 'other teams have replicated', 'have other teams replicate', 'did other teams replicated'], 'Після винесеного на початок заперечного звороту потрібна інверсія допоміжного дієслова та підмета.'),
        ppNegItem('No previous investigation {a1} such a consistent pattern across all sites.', 'has ever documented', ['has ever documented', 'have ever documented', 'has ever document', 'has never documented', 'is ever documented'], 'Заперечний підмет охоплює всі попередні дослідження; головне слово investigation стоїть в однині.'),
        ppNegItem('The chief auditor {a1} how the final estimate was calculated.', 'has not yet disclosed', ['has not yet disclosed', 'have not yet disclosed', 'has not yet disclose', 'has yet not disclosed', 'is not yet disclosed'], 'Очікувану інформацію досі не оприлюднено; головне слово підмета auditor стоїть в однині.'),
        ppNegItem("The witness's account {a1} by any independent evidence.", 'has not been corroborated', ['has not been corroborated', 'have not been corroborated', 'has not corroborated', 'has not been corroborate', 'is not been corroborated'], 'Account є об’єктом перевірки, а any підсилює відсутність підтвердження; потрібна пасивна будова.'),
        ppNegItem('The researchers {a1} the possibility of sampling bias.', "haven't ruled out", ["haven't ruled out", "hasn't ruled out", "haven't rule out", "haven't ruled", "aren't ruled out"], 'Можливість усе ще залишається відкритою; узгодьте коротку форму з підметом researchers.'),
        ppNegItem('The ethics officer {a1} the revised protocol.', "hasn't approved", ["hasn't approved", "haven't approved", "hasn't approve", "hasn't approving", "isn't approved"], 'Відсутність рішення залишається актуальною; головне слово підмета officer стоїть в однині.'),
        ppNegItem('We {a1} this interaction under controlled conditions before.', 'have not observed', ['have not observed', 'has not observed', 'have not observe', 'have observed not', 'are not observed'], 'Before стосується накопиченого досвіду; перевірте порядок заперечної конструкції.'),
        ppNegItem('The policy {a1} to independent judicial review.', 'has never been subjected', ['has never been subjected', 'have never been subjected', 'has never subjected', 'has never been subjecting', 'is never been subjected'], 'Політика є об’єктом дії, а never показує відсутність такого досвіду до тепер.'),
        ppNegItem('The parties {a1} the central issue despite months of negotiation.', 'have not resolved', ['have not resolved', 'has not resolved', 'have not resolve', 'have resolved not', 'are not resolved'], 'Despite months of negotiation показує тривалий період без очікуваного результату.'),
        ppNegItem('The manuscript cannot be cited because it {a1}.', "hasn't been peer-reviewed", ["hasn't been peer-reviewed", "haven't been peer-reviewed", "hasn't peer-reviewed", "hasn't been review", "isn't been peer-reviewed"], 'Рукопис є об’єктом перевірки, а теперішня заборона є її наслідком; потрібна пасивна будова.'),
    ],
    'C2' => [
        ppNegItem('The investigators {a1} whether the two events are causally linked.', 'have not established', ['have not established', 'has not established', 'have not establish', 'have established not', 'are not established'], 'Висновок залишається недоведеним до тепер; підмет investigators має значення множини.'),
        ppNegItem('The long-term impact {a1} with sufficient precision.', 'has not yet been quantified', ['has not yet been quantified', 'have not yet been quantified', 'has not quantified', 'has not yet been quantify', 'is not yet been quantified'], 'Impact є об’єктом кількісної оцінки; yet підкреслює відсутність достатнього результату.'),
        ppNegItem('At no stage {a1} sufficient evidence to support the allegation.', 'has the inquiry produced', ['has the inquiry produced', 'have the inquiry produced', 'the inquiry has produced', 'has the inquiry produce', 'did the inquiry produced'], 'Після початкового заперечного обставинного звороту потрібна інверсія допоміжного дієслова й підмета.'),
        ppNegItem('No comparable ruling {a1} so much uncertainty across an entire sector.', 'has ever generated', ['has ever generated', 'have ever generated', 'has ever generate', 'has never generated', 'is ever generated'], 'Заперечний підмет охоплює всі попередні випадки; головне слово ruling стоїть в однині.'),
        ppNegItem('The lead reviewer still {a1} the criteria on which the applications were rejected.', 'has not disclosed', ['has not disclosed', 'have not disclosed', 'has not disclose', 'has disclosed not', 'is not disclosed'], 'Still підкреслює, що очікуваної інформації досі немає; головне слово підмета reviewer стоїть в однині.'),
        ppNegItem('The central claim {a1} by evidence independent of the original dataset.', 'has not been substantiated', ['has not been substantiated', 'have not been substantiated', 'has not substantiated', 'has not been substantiate', 'is not been substantiated'], 'Claim є об’єктом підтвердження, тому зважте на заперечну пасивну будову та актуальність висновку.'),
        ppNegItem('The talks remain open because the negotiators {a1} a temporary settlement.', "haven't ruled out", ["haven't ruled out", "hasn't ruled out", "haven't rule out", "haven't ruled", "aren't ruled out"], 'Поточний стан переговорів показує, що можливість усе ще розглядають; підмет negotiators стоїть у множині.'),
        ppNegItem('The proposal {a1} by either regulatory body.', "hasn't been endorsed", ["hasn't been endorsed", "haven't been endorsed", "hasn't endorsed", "hasn't been endorse", "isn't been endorsed"], 'Proposal є об’єктом рішення, а either охоплює обидва регуляторні органи; потрібна пасивна будова.'),
        ppNegItem('We {a1} a conflict between these two provisions before.', 'have not encountered', ['have not encountered', 'has not encountered', 'have not encounter', 'have encountered not', 'are not encountered'], 'Before охоплює весь попередній досвід; перевірте порядок елементів у заперечній конструкції.'),
        ppNegItem('The underlying assumption {a1} in a controlled study.', 'has never been tested', ['has never been tested', 'have never been tested', 'has never tested', 'has never been test', 'is never been tested'], 'Assumption є об’єктом перевірки, а never показує відсутність такої події донині.'),
        ppNegItem('The parties {a1} the competing definitions despite extensive mediation.', 'have not reconciled', ['have not reconciled', 'has not reconciled', 'have not reconcile', 'have reconciled not', 'are not reconciled'], 'Despite extensive mediation підкреслює тривалий період без результату; підмет parties має форму множини.'),
        ppNegItem('The ruling cannot take effect because it {a1} by the court.', "hasn't been formally issued", ["hasn't been formally issued", "haven't been formally issued", "hasn't formally issued", "hasn't been formally issue", "isn't been formally issued"], 'Рішення є об’єктом офіційної дії, а неможливість набуття чинності є теперішнім наслідком.'),
    ],
];

$ukPrompts = [
    'A1' => [
        'Я ще не закінчив домашнє завдання.',
        'Мія ще не поснідала.',
        'Ми ще не спакували валізи.',
        'Я ніколи раніше не бачив снігу.',
        'Том ще не надіслав повідомлення.',
        'Вона не може відкрити файл, бо ще не знайшла пароль.',
        'Ми ще не купили квитки.',
        'Ліна ще не прочитала електронний лист.',
        'Я раніше не виконував цю вправу.',
        'Лео ніколи раніше не куштував суші.',
        'Вони ще не отримали нашу відповідь.',
        'Єва досі тут, бо ще не пішла додому.',
    ],
    'A2' => [
        'Ти ще не заповнив онлайн-форму.',
        'Даніель ще не прибрав свою кімнату.',
        'Вони ще не знайшли правильну адресу.',
        'Мої батьки ніколи раніше не подорожували за кордон.',
        'Нора ще не надіслала посилку.',
        'Бен не може відповісти на запитання, бо ще не прочитав інструкції.',
        'Ми ще не забронювали столик на сьогоднішній вечір.',
        'Елла ще не переглянула новий епізод.',
        'Ми раніше не їздили цим маршрутом.',
        'Том ніколи раніше не водив електромобіль.',
        'Діти ще не отримали результати тесту.',
        'Марк досі на роботі, бо ще не вийшов з офісу.',
    ],
    'B1' => [
        'Я не можу надіслати звіт, бо ще не закінчив його.',
        'Принтер досі не працює, бо технік його ще не полагодив.',
        'Ми ще не ухвалили остаточного рішення.',
        'Наш провідний інженер ніколи раніше не стикався з такою проблемою.',
        'Постачальник ще не відповів на нашу скаргу.',
        'Анна не може відкрити вкладення, бо ще не отримала код доступу.',
        'Перевірка не може розпочатися, бо ми ще не завантажили файли.',
        'Редактор ще не перевірив оновлену статтю.',
        'Я раніше не опрацьовував подібної скарги.',
        'Мая ніколи раніше не мала такого стислого терміну.',
        'Волонтери ще не отримали обіцяного фінансування.',
        'Анна не може опублікувати звіт, бо її керівник ще не затвердив його.',
    ],
    'B2' => [
        'Відділи продажів поки що не досягли річних цілей.',
        'Сервіс залишається нестабільним, бо останнє оновлення не усунуло основної несправності.',
        'Два відділи ще не досягли прийнятного компромісу.',
        'Наші аналітики ніколи раніше не стикалися з розбіжністю такого масштабу.',
        'Підрядник ще не подав оновлений графік.',
        'Надані докази ще не пройшли незалежної перевірки.',
        'Слідчі ще не відкинули альтернативного пояснення.',
        'Керівник проєкту ще не затвердив план відновлення.',
        'Ми раніше не стикалися з опором такого масштабу.',
        'Конфіденційний звіт ніколи не оприлюднювали за межами відділу.',
        'Відомства ще не оприлюднили запитані дані.',
        'Проєкт не може продовжуватися, бо остаточного дозволу ще не надали.',
    ],
    'C1' => [
        'Рецензенти не розглянули основного припущення в жодній зі своїх відповідей.',
        'Запропонована модель досі не пояснила всіх спостережуваних аномалій.',
        'Інші команди жодного разу не відтворили результати незалежно.',
        'Жодне попереднє розслідування ще не зафіксувало настільки послідовної картини на всіх об’єктах.',
        'Головний аудитор досі не розкрив, як обчислили остаточну оцінку.',
        'Версію свідка досі не підтверджено жодними незалежними доказами.',
        'Дослідники ще не відкинули можливості систематичного зміщення вибірки.',
        'Уповноважений з етики ще не затвердив оновлений протокол.',
        'Раніше ми не спостерігали цієї взаємодії в контрольованих умовах.',
        'Цю політику ще ніколи не піддавали незалежному судовому перегляду.',
        'Попри місяці переговорів, сторони ще не розв’язали центральне питання.',
        'Рукопис не можна цитувати, бо він ще не пройшов рецензування.',
    ],
    'C2' => [
        'Слідчі досі не встановили, чи пов’язані дві події причинно.',
        'Довгостроковий вплив досі не оцінили кількісно з достатньою точністю.',
        'На жодному етапі слідство не надало достатніх доказів на підтримку звинувачення.',
        'Жодне подібне рішення ще ніколи не спричиняло стільки невизначеності в усьому секторі.',
        'Провідний рецензент досі не розкрив критеріїв, за якими відхилили заявки.',
        'Головне твердження досі не підтверджене доказами, незалежними від початкового набору даних.',
        'Перемовини досі тривають, бо учасники ще не відкинули можливості тимчасового врегулювання.',
        'Жоден із двох регуляторних органів ще не схвалив пропозицію.',
        'Раніше ми не стикалися з конфліктом між цими двома положеннями.',
        'Основне припущення ще ніколи не перевіряли в контрольованому дослідженні.',
        'Попри тривале посередництво, сторони ще не узгодили конкуруючі визначення.',
        'Рішення не може набути чинності, бо суд його ще офіційно не виніс.',
    ],
];

/** @return list<string> */
function ppNegSentenceTokens(string $sentence): array
{
    $normalized = preg_replace('/[.,!?;:]+/u', '', $sentence);
    if (! is_string($normalized)) {
        throw new RuntimeException('Unable to normalize sentence punctuation.');
    }

    return array_values(array_filter(
        preg_split('/\s+/u', trim($normalized)) ?: [],
        static fn (string $token): bool => $token !== ''
    ));
}

/**
 * @param list<string> $correctTokens
 * @param list<string> $wrongAnswers
 * @return list<string>
 */
function ppNegComposeOptions(array $correctTokens, array $wrongAnswers): array
{
    $options = $correctTokens;
    $seen = [];
    foreach ($correctTokens as $token) {
        $seen[mb_strtolower($token)] = true;
    }

    // Do not offer tiles that can be recombined into another defensible tense
    // or into the full/contracted equivalent of the authored answer.
    $blocked = array_fill_keys([
        'do', 'does', 'did', "don't", "doesn't", "didn't",
        'am', 'is', 'are', 'was', 'were', "isn't", "aren't", "wasn't", "weren't",
        'had', "hadn't", 'will', "won't",
    ], true);

    foreach (['not', 'never', 'ever'] as $negativeToken) {
        if (! isset($seen[$negativeToken])) {
            $blocked[$negativeToken] = true;
        }
    }

    if (isset($seen["haven't"])) {
        $blocked['have'] = true;
    }
    if (isset($seen["hasn't"])) {
        $blocked['has'] = true;
    }
    if (isset($seen['have'], $seen['not'])) {
        $blocked["haven't"] = true;
    }
    if (isset($seen['has'], $seen['not'])) {
        $blocked["hasn't"] = true;
    }

    $distractorCount = 0;
    foreach ($wrongAnswers as $wrongAnswer) {
        foreach (ppNegSentenceTokens($wrongAnswer) as $token) {
            $key = mb_strtolower($token);
            if (isset($seen[$key]) || isset($blocked[$key])) {
                continue;
            }

            $seen[$key] = true;
            $options[] = $token;
            ++$distractorCount;

            if ($distractorCount >= 6) {
                break 2;
            }
        }
    }

    foreach (['being', 'already', 'since', 'just', 'ago', 'yesterday', 'tomorrow', 'recently', 'during'] as $fallback) {
        if ($distractorCount >= 6) {
            break;
        }

        $key = mb_strtolower($fallback);
        if (isset($seen[$key]) || isset($blocked[$key])) {
            continue;
        }

        $seen[$key] = true;
        $options[] = $fallback;
        ++$distractorCount;
    }

    if ($distractorCount < 4) {
        throw new RuntimeException('Every compose question must have at least four distinct distractors.');
    }

    return $options;
}

/** @param list<string> $tokens @return array<string, string> */
function ppNegMarkerAnswers(array $tokens): array
{
    $answers = [];
    foreach ($tokens as $index => $token) {
        $answers['a'.($index + 1)] = $token;
    }

    return $answers;
}

$standard = ppNegReadJson($standardPath);
$standardByUuid = [];
foreach ($standard['questions'] as $index => $question) {
    $standardByUuid[(string) $question['uuid']] = $index;
}

foreach ($content as $level => $items) {
    if (count($items) !== 12) {
        throw new RuntimeException("Expected 12 {$level} standard questions.");
    }

    foreach ($items as $offset => $item) {
        $number = str_pad((string) ($offset + 1), 2, '0', STR_PAD_LEFT);
        $uuid = 'present-perfect-neg-v3-'.strtolower($level).'-'.$number;
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

$compose = ppNegReadJson($composePath);
$composeByUuid = [];
foreach ($compose['questions'] as $index => $question) {
    $composeByUuid[(string) $question['uuid']] = $index;
}

foreach ($content as $level => $items) {
    if (count($ukPrompts[$level] ?? []) !== 12) {
        throw new RuntimeException("Expected 12 {$level} Ukrainian prompts.");
    }

    foreach ($items as $offset => $item) {
        $number = str_pad((string) ($offset + 1), 2, '0', STR_PAD_LEFT);
        $uuid = 'present-perfect-neg-poly-'.strtolower($level).'-'.$number;
        $index = $composeByUuid[$uuid] ?? null;
        if (! is_int($index)) {
            throw new RuntimeException("Missing compose question {$uuid}");
        }

        $completedSentence = str_replace('{a1}', $item['answer'], $item['question']);
        $correctTokens = ppNegSentenceTokens($completedSentence);

        $question =& $compose['questions'][$index];
        $question['question'] = $ukPrompts[$level][$offset];
        $question['answers'] = ppNegMarkerAnswers($correctTokens);
        $question['options'] = ppNegComposeOptions($correctTokens, array_slice($item['options'], 1));
        $question['variants'] = [];
        unset($question);
    }
}

ppNegWriteJson($standardPath, $standard);
ppNegWriteJson($composePath, $compose);

echo 'Updated 72 standard and 72 Sentence Builder Present Perfect negative questions.'.PHP_EOL;
