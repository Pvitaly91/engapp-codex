<?php

declare(strict_types=1);

/**
 * Rebuild Present Perfect question banks with contextual, unambiguous content,
 * Ukrainian hints, compact atomic Sentence Builder options, and real A1-C2 progression.
 */

$root = dirname(__DIR__);
$standardPath = $root.'/database/seeders/V3/Tenses/PresentPerfect/PresentPerfectQuestionsAllLevelsV3Seeder/definition.json';
$composePath = $root.'/database/seeders/V3/Polyglot/PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder/definition.json';

/** @return array<string, mixed> */
function ppQuestionsReadJson(string $path): array
{
    return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}

/** @param array<string, mixed> $data */
function ppQuestionsWriteJson(string $path, array $data): void
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
 * @param list<string> $distractors
 * @return array{question: string, answer: string, options: list<string>, hint: string, prompt: string, target: string, distractors: list<string>}
 */
function ppQuestion(
    string $question,
    string $answer,
    array $options,
    string $hint,
    string $prompt,
    string $target,
    array $distractors
): array {
    if (substr_count($question, '{a1}') !== 1) {
        throw new RuntimeException("Question must contain {a1} exactly once: {$question}");
    }

    if (count($options) !== 5 || count(array_unique($options)) !== 5 || $options[0] !== $answer) {
        throw new RuntimeException("Question must have five unique options with the answer first: {$question}");
    }

    if (! preg_match('/[.!?]$/u', $question) || ! preg_match('/[.!?]$/u', $prompt) || ! preg_match('/[.!?]$/u', $target)) {
        throw new RuntimeException("Question, prompt, and target need terminal punctuation: {$question}");
    }

    if (count($distractors) < 4 || count($distractors) > 8 || count(array_unique($distractors)) !== count($distractors)) {
        throw new RuntimeException("Sentence Builder needs four to eight unique distractors: {$question}");
    }

    foreach ($distractors as $distractor) {
        if (trim($distractor) === '' || preg_match('/\s/u', $distractor)) {
            throw new RuntimeException("Sentence Builder distractors must be atomic: {$question}");
        }
    }

    return compact('question', 'answer', 'options', 'hint', 'prompt', 'target', 'distractors');
}

$content = [
    'A1' => [
        ppQuestion('{a1} your homework yet?', 'Have you finished', ['Have you finished', 'Has you finished', 'Have you finish', 'Have you finishing', 'Have finished you'], 'Маркер yet показує, що результат перевіряють станом на тепер.', 'Ти вже завершив своє домашнє завдання?', 'Have you finished your homework yet?', ['Has', 'finish', 'finishing', 'yours', 'homeworks']),
        ppQuestion('{a1} the email yet?', 'Has she read', ['Has she read', 'Have she read', 'Has she reads', 'Has she reading', 'Has read she'], 'Підмет she позначає одну особу, а дія важлива для теперішньої ситуації.', 'Вона вже прочитала електронний лист?', 'Has she read the email yet?', ['Have', 'reads', 'reading', 'he', 'emails']),
        ppQuestion('{a1} so far today?', 'What have you done', ['What have you done', 'What has you done', 'What have you did', 'What you have done', 'What have done you'], 'So far обмежує запит періодом від початку дня до цього моменту.', 'Що ти встиг зробити сьогодні станом на зараз?', 'What have you done so far today?', ['has', 'did', 'doing', 'Where', 'made']),
        ppQuestion('{a1} to London?', 'Have you ever been', ['Have you ever been', 'Has you ever been', 'Have you ever went', 'Have ever you been', 'Have you ever being'], 'Йдеться про життєвий досвід без конкретної минулої дати.', 'Ти коли-небудь бував у Лондоні?', 'Have you ever been to London?', ['Has', 'went', 'was', 'being', 'from']),
        ppQuestion('{a1} the welcome email yet?', 'Has Leo sent', ['Has Leo sent', 'Have Leo sent', 'Has Leo send', 'Has sent Leo', 'Has Leo sending'], 'Вітальний лист досі очікують, а Leo — одна людина.', 'Лео вже надіслав вітальний електронний лист?', 'Has Leo sent the welcome email yet?', ['Have', 'send', 'sending', 'sended', 'she']),
        ppQuestion('Have you seen the new schedule? — Yes, I {a1}.', 'have', ['have', 'has', 'did', 'do', 'am'], 'У короткій відповіді не повторюйте смислове дієслово.', 'Ти бачив новий розклад? — Так.', 'Yes, I have.', ['has', 'he', 'am', 'seen']),
        ppQuestion('{a1} the instructions?', 'Have they understood', ['Have they understood', 'Has they understood', 'Have they understand', 'Have understood they', 'Have they understanding'], 'Результат розуміння перевіряють зараз, а they позначає кількох людей.', 'Вони вже зрозуміли інструкції?', 'Have they understood the instructions?', ['Has', 'understand', 'understanding', 'them', 'understands']),
        ppQuestion('{a1} the book yet?', 'Has Anna opened', ['Has Anna opened', 'Have Anna opened', 'Has Anna open', 'Has opened Anna', 'Has Anna opening'], 'Йдеться про Анну та очікуваний результат, якого перевіряють зараз.', 'Анна вже відкрила книжку?', 'Has Anna opened the book yet?', ['Have', 'open', 'opening', 'closed', 'she']),
        ppQuestion('{a1} the note that I need now?', 'Where have you put', ['Where have you put', 'Where has you put', 'Where have you putted', 'Where you have put', 'Where have put you'], 'Слово now підкреслює, що місце записки важливе саме тепер.', 'Куди ти поклав записку, яка потрібна мені зараз?', 'Where have you put the note that I need now?', ['has', 'putted', 'puts', 'When', 'there']),
        ppQuestion('{a1} anything like this before?', 'Have you ever seen', ['Have you ever seen', 'Has you ever seen', 'Have you ever saw', 'Have ever you seen', 'Have you ever seeing'], 'Запит стосується досвіду до теперішнього моменту.', 'Ти коли-небудь раніше бачив щось подібне?', 'Have you ever seen anything like this before?', ['Has', 'saw', 'seeing', 'something', 'often']),
        ppQuestion('{a1} the answer?', 'Has Mia already found', ['Has Mia already found', 'Have Mia already found', 'Has Mia already find', 'Has already Mia found', 'Has Mia already finding'], 'Already тут передає здивування через швидкий результат.', 'Невже Мія вже знайшла відповідь?', 'Has Mia already found the answer?', ['Have', 'find', 'finding', 'he', 'answers']),
        ppQuestion('{a1} the ticket you need for the bus yet?', 'Have you found', ['Have you found', 'Has you found', 'Have you find', 'Have found you', 'Have you finding'], 'Квиток потрібен зараз, тому важливий теперішній результат пошуку.', 'Ти вже знайшов квиток, потрібний для автобуса?', 'Have you found the ticket you need for the bus yet?', ['Has', 'find', 'finding', 'lost', 'tickets']),
    ],
    'A2' => [
        ppQuestion('{a1} the exercise due today yet?', 'Have you completed', ['Have you completed', 'Has you completed', 'Have you complete', 'Have completed you', 'Have you completing'], 'Yet і дедлайн today пов’язують виконання з поточним моментом.', 'Ти вже виконав вправу, яку треба здати сьогодні?', 'Have you completed the exercise due today yet?', ['Has', 'complete', 'completing', 'tomorrow', 'exercises']),
        ppQuestion('{a1} the message she needs to answer yet?', 'Has Nora read', ['Has Nora read', 'Have Nora read', 'Has Nora reads', 'Has read Nora', 'Has Nora reading'], 'Нора має відповісти зараз, тож перевіряється вже отриманий результат.', 'Нора вже прочитала повідомлення, на яке їй треба відповісти?', 'Has Nora read the message she needs to answer yet?', ['Have', 'reads', 'reading', 'he', 'messages']),
        ppQuestion('{a1} that has left blue marks on your hands?', 'What have you painted', ['What have you painted', 'What has you painted', 'What have you paint', 'What you have painted', 'What have painted you'], 'Сині сліди на руках є теперішнім наслідком недавньої дії.', 'Що ти пофарбував, якщо на руках залишилися сині сліди?', 'What have you painted that has left blue marks on your hands?', ['artist', 'paint', 'painting', 'Which', 'made']),
        ppQuestion('{a1} this song before?', 'Have you ever heard', ['Have you ever heard', 'Has you ever heard', 'Have you ever hear', 'Have ever you heard', 'Have you ever hearing'], 'Запит перевіряє попередній досвід без указаної дати.', 'Ти коли-небудь раніше чув цю пісню?', 'Have you ever heard this song before?', ['Has', 'hear', 'hearing', 'listened', 'songs']),
        ppQuestion('{a1} the parcel we are still waiting for?', 'Has Daniel sent', ['Has Daniel sent', 'Have Daniel sent', 'Has Daniel send', 'Has sent Daniel', 'Has Daniel sending'], 'Посилку досі очікують, тому результат актуальний зараз.', 'Даніель уже надіслав посилку, яку ми досі чекаємо?', 'Has Daniel sent the parcel we are still waiting for?', ['Have', 'send', 'sending', 'received', 'parcels']),
        ppQuestion('Have your parents arrived? — No, they {a1}.', "haven't", ["haven't", "hasn't", "weren't", "aren't", "didn't"], 'У короткій відповіді використовується те саме допоміжне слово, що й у запитанні.', 'Твої батьки вже приїхали? — Ні.', "No, they haven't.", ["hasn't", 'he', 'arrived', 'are']),
        ppQuestion('{a1} the dinner that smells so good?', 'Have they cooked', ['Have they cooked', 'Has they cooked', 'Have they cook', 'Have cooked they', 'Have they cooking'], 'Запах є видимим теперішнім результатом завершеної дії.', 'Вони приготували вечерю, яка так смачно пахне?', 'Have they cooked the dinner that smells so good?', ['Has', 'cook', 'cooking', 'smelled', 'dinners']),
        ppQuestion('{a1} the cake on the table?', 'Has Mia baked', ['Has Mia baked', 'Have Mia baked', 'Has Mia bake', 'Has baked Mia', 'Has Mia baking'], 'Готовий пиріг на столі є результатом, який бачимо зараз.', 'Мія спекла пиріг, який стоїть на столі?', 'Has Mia baked the cake on the table?', ['Have', 'bake', 'baking', 'cooked', 'cakes']),
        ppQuestion('{a1} the form I need now?', 'Where have you left', ['Where have you left', 'Where has you left', 'Where have you leave', 'Where you have left', 'Where have left you'], 'Форма потрібна зараз, тому запит стосується її теперішнього місця.', 'Де ти залишив бланк, який потрібен мені зараз?', 'Where have you left the form I need now?', ['has', 'leave', 'leaving', 'When', 'there']),
        ppQuestion('{a1} this route before?', 'Have we ever taken', ['Have we ever taken', 'Has we ever taken', 'Have we ever took', 'Have ever we taken', 'Have we ever taking'], 'Знайомий маршрут спонукає запитати про попередній досвід.', 'Ми коли-небудь раніше їхали цим маршрутом?', 'Have we ever taken this route before?', ['Has', 'took', 'taking', 'walked', 'routes']),
        ppQuestion('{a1} everyone on the list?', 'Has Leo already called', ['Has Leo already called', 'Have Leo already called', 'Has Leo already call', 'Has already Leo called', 'Has Leo already calling'], 'Already показує, що результат міг з’явитися раніше, ніж очікували.', 'Лео вже встиг зателефонувати всім зі списку?', 'Has Leo already called everyone on the list?', ['Have', 'call', 'calling', 'she', 'somebody']),
        ppQuestion('{a1} the computer that should be showing the slides?', 'Have you turned on', ['Have you turned on', 'Has you turned on', 'Have you turn on', 'Have turned on you', 'Have you turning on'], 'Порожній екран показує, що потрібний результат перевіряють зараз.', 'Ти ввімкнув комп’ютер, який має показувати слайди?', 'Have you turned on the computer that should be showing the slides?', ['Has', 'turn', 'turning', 'off', 'computers']),
    ],
    'B1' => [
        ppQuestion('{a1} the report the manager is waiting for yet?', 'Have you finished', ['Have you finished', 'Has you finished', 'Have you finish', 'Have finished you', 'Have you finishing'], 'Менеджер очікує документ зараз, тому важливий актуальний результат.', 'Ти вже завершив звіт, на який чекає менеджер?', 'Have you finished the report the manager is waiting for yet?', ['Has', 'finish', 'finishing', 'managers', 'reports']),
        ppQuestion('{a1} the proposal she must discuss today?', 'Has she reviewed', ['Has she reviewed', 'Have she reviewed', 'Has she review', 'Has reviewed she', 'Has she reviewing'], 'Обговорення заплановано на сьогодні, тож перевіряють готовність до нього.', 'Вона вже переглянула пропозицію, яку має обговорити сьогодні?', 'Has she reviewed the proposal she must discuss today?', ['Have', 'review', 'reviewing', 'he', 'proposals']),
        ppQuestion('{a1} so far?', 'Which files have you uploaded', ['Which files have you uploaded', 'Which files has you uploaded', 'Which files have you upload', 'Which files you have uploaded', 'Which have you uploaded files'], 'So far просить підсумувати результат до цього моменту.', 'Які файли ти вже завантажив станом на зараз?', 'Which files have you uploaded so far?', ['has', 'upload', 'uploading', 'What', 'downloaded']),
        ppQuestion('{a1} in Berlin before?', 'Have you ever worked', ['Have you ever worked', 'Has you ever worked', 'Have you ever work', 'Have ever you worked', 'Have you ever working'], 'Знання місцевих звичаїв пов’язують із можливим попереднім досвідом.', 'Ти коли-небудь раніше працював у Берліні?', 'Have you ever worked in Berlin before?', ['Has', 'work', 'working', 'lived', 'from']),
        ppQuestion('{a1} to the invitation we sent last week yet?', 'Has he replied', ['Has he replied', 'Have he replied', 'Has he reply', 'Has replied he', 'Has he replying'], 'Запрошення надіслали раніше, але відповідь важлива станом на тепер.', 'Він уже відповів на запрошення, яке ми надіслали минулого тижня?', 'Has he replied to the invitation we sent last week yet?', ['Have', 'reply', 'replying', 'answered', 'invitations']),
        ppQuestion('Has the supplier confirmed the order? — Yes, it {a1}.', 'has', ['has', 'have', 'did', 'does', 'is'], 'У короткій відповіді смислову частину запитання не повторюють.', 'Постачальник підтвердив замовлення? — Так.', 'Yes, it has.', ['have', 'they', 'is', 'confirmed']),
        ppQuestion('{a1} the same pattern in their data?', 'Have the other teams found', ['Have the other teams found', 'Has the other teams found', 'Have the other teams find', 'Have found the other teams', 'Have the other teams finding'], 'Порівнюються результати кількох команд станом на тепер.', 'Інші команди також виявили таку закономірність у своїх даних?', 'Have the other teams found the same pattern in their data?', ['Has', 'find', 'finding', 'team', 'patterns']),
        ppQuestion('{a1} the contract that now carries both signatures?', 'Has Anna signed', ['Has Anna signed', 'Have Anna signed', 'Has Anna sign', 'Has signed Anna', 'Has Anna signing'], 'Обидва підписи на документі є актуальним результатом.', 'Анна підписала контракт, на якому тепер є обидва підписи?', 'Has Anna signed the contract that now carries both signatures?', ['Have', 'sign', 'signing', 'bought', 'contracts']),
        ppQuestion('{a1} the file that is missing from the shared folder?', 'Where have you saved', ['Where have you saved', 'Where has you saved', 'Where have you save', 'Where you have saved', 'Where have saved you'], 'Файл шукають зараз, тому запитують про його поточне місце.', 'Де ти зберіг файл, якого немає у спільній папці?', 'Where have you saved the file that is missing from the shared folder?', ['has', 'save', 'saving', 'When', 'deleted']),
        ppQuestion('{a1} anything comparable in our records before?', 'Have we ever recorded', ['Have we ever recorded', 'Has we ever recorded', 'Have we ever record', 'Have ever we recorded', 'Have we ever recording'], 'Запит порівнює нинішній результат з усім попереднім досвідом.', 'Ми коли-небудь раніше фіксували щось подібне у своїх записах?', 'Have we ever recorded anything comparable in our records before?', ['Has', 'record', 'recording', 'heard', 'comparisons']),
        ppQuestion('{a1} a solution to the problem?', 'Has she already found', ['Has she already found', 'Have she already found', 'Has she already find', 'Has already she found', 'Has she already finding'], 'Її полегшення натякає на неочікувано швидкий результат.', 'Невже вона вже знайшла розв’язання проблеми?', 'Has she already found a solution to the problem?', ['Have', 'find', 'finding', 'he', 'solutions']),
        ppQuestion('{a1} the revised figures the client needs for the call?', 'Have you sent', ['Have you sent', 'Has you sent', 'Have you send', 'Have sent you', 'Have you sending'], 'Цифри потрібні клієнтові зараз, тому перевіряється результат надсилання.', 'Ти вже надіслав уточнені цифри, потрібні клієнтові для дзвінка?', 'Have you sent the revised figures the client needs for the call?', ['Has', 'send', 'sending', 'received', 'figure']),
    ],
    'B2' => [
        ppQuestion('{a1} the quarterly report the board needs yet?', 'Have you finalized', ['Have you finalized', 'Has you finalized', 'Have you finalize', 'Have finalized you', 'Have you finalizing'], 'Засідання наближається, тому важлива готовність документа зараз.', 'Ти вже остаточно підготував квартальний звіт, потрібний раді директорів?', 'Have you finalized the quarterly report the board needs yet?', ['Has', 'finalize', 'finalizing', 'annual', 'reports']),
        ppQuestion('{a1} the latest draft that is awaiting publication?', 'Has counsel reviewed', ['Has counsel reviewed', 'Have counsel reviewed', 'Has counsel review', 'Has reviewed counsel', 'Has counsel reviewing'], 'Публікація залежить від завершеної юридичної перевірки.', 'Юрисконсульт уже перевірив останню редакцію, яка очікує публікації?', 'Has counsel reviewed the latest draft that is awaiting publication?', ['Have', 'review', 'reviewing', 'lawyer', 'drafts']),
        ppQuestion('{a1} so far if two remain open?', 'How many milestones have we completed', ['How many milestones have we completed', 'How many milestones has we completed', 'How many milestones have we complete', 'How many milestones we have completed', 'How many have we completed milestones'], 'Кількість відкритих етапів дає змогу підсумувати прогрес до цього моменту.', 'Скільки етапів ми вже завершили, якщо два ще залишаються відкритими?', 'How many milestones have we completed so far if two remain open?', ['has', 'complete', 'completing', 'much', 'closed']),
        ppQuestion('{a1} data from this source before?', 'Have you ever analyzed', ['Have you ever analyzed', 'Has you ever analyzed', 'Have you ever analyze', 'Have ever you analyzed', 'Have you ever analyzing'], 'Точність порівняння спонукає запитати про попередній професійний досвід.', 'Ти коли-небудь раніше аналізував дані з цього джерела?', 'Have you ever analyzed data from this source before?', ['Has', 'analyze', 'analyzing', 'collected', 'sources']),
        ppQuestion('{a1} the proposal the recipient is still waiting for?', 'Has he sent', ['Has he sent', 'Have he sent', 'Has he send', 'Has sent he', 'Has he sending'], 'Одержувач досі нічого не отримав, тому результат перевіряють зараз.', 'Він уже надіслав пропозицію, на яку досі чекає одержувач?', 'Has he sent the proposal the recipient is still waiting for?', ['Have', 'send', 'sending', 'received', 'proposals']),
        ppQuestion('Have the auditors raised any objections? — No, they {a1}.', "haven't", ["haven't", "hasn't", "weren't", "aren't", "didn't"], 'Коротка відповідь зберігає допоміжний елемент початкового запитання.', 'Аудитори вже висловили якісь заперечення? — Ні.', "No, they haven't.", ["hasn't", 'he', 'are', 'raised']),
        ppQuestion('{a1} the new figures now visible on the dashboard?', 'Have all regions reported', ['Have all regions reported', 'Has all regions reported', 'Have all regions report', 'Have reported all regions', 'Have all regions reporting'], 'Оновлена панель показує сукупний результат кількох регіонів.', 'Усі регіони вже подали нові цифри, які видно на панелі?', 'Have all regions reported the new figures now visible on the dashboard?', ['Has', 'report', 'reporting', 'region', 'old']),
        ppQuestion('{a1} the agreement that is ready for filing?', 'Has Anna signed', ['Has Anna signed', 'Have Anna signed', 'Has Anna sign', 'Has signed Anna', 'Has Anna signing'], 'Готовність до реєстрації залежить від уже отриманого підпису.', 'Анна вже підписала угоду, готову до реєстрації?', 'Has Anna signed the agreement that is ready for filing?', ['Have', 'sign', 'signing', 'bought', 'agreements']),
        ppQuestion('{a1} the original document that is absent from the archive?', 'Where have they stored', ['Where have they stored', 'Where has they stored', 'Where have they store', 'Where they have stored', 'Where have stored they'], 'Оригінал шукають тепер, тому запит стосується його актуального місця.', 'Де вони зберегли оригінал документа, якого немає в архіві?', 'Where have they stored the original document that is absent from the archive?', ['has', 'store', 'storing', 'When', 'copies']),
        ppQuestion('{a1} a pattern like this in our records before?', 'Have we ever observed', ['Have we ever observed', 'Has we ever observed', 'Have we ever observe', 'Have ever we observed', 'Have we ever observing'], 'Нинішній випадок порівнюють з усією попередньою практикою.', 'Ми коли-небудь раніше спостерігали таку закономірність у своїх записах?', 'Have we ever observed a pattern like this in our records before?', ['Has', 'observe', 'observing', 'ignored', 'patterns']),
        ppQuestion('{a1} approval for the project?', 'Has the team already received', ['Has the team already received', 'Have the team already received', 'Has the team already receive', 'Has already the team received', 'Has the team already receiving'], 'Святкування вказує на результат, що з’явився раніше, ніж очікували.', 'Команда вже отримала схвалення проєкту?', 'Has the team already received approval for the project?', ['Have', 'receive', 'receiving', 'teams', 'rejected']),
        ppQuestion('{a1} the update if the customer still sees the old version?', 'Have you deployed', ['Have you deployed', 'Has you deployed', 'Have you deploy', 'Have deployed you', 'Have you deploying'], 'Стара версія на екрані є поточним наслідком, який треба пояснити.', 'Ти вже розгорнув оновлення, якщо клієнт досі бачить стару версію?', 'Have you deployed the update if the customer still sees the old version?', ['Has', 'deploy', 'deploying', 'removed', 'updates']),
    ],
    'C1' => [
        ppQuestion('{a1} the appendix the committee needs for its vote?', 'Have you attached', ['Have you attached', 'Has you attached', 'Have you attach', 'Have attached you', 'Have you attaching'], 'Голосування залежить від наявності документа в остаточній редакції.', 'Ти вже додав додаток, потрібний комітету для голосування?', 'Have you attached the appendix the committee needs for its vote?', ['Has', 'attach', 'attaching', 'detached', 'appendices']),
        ppQuestion('{a1} the full case file if her response addresses every objection?', 'Has she examined', ['Has she examined', 'Have she examined', 'Has she examine', 'Has examined she', 'Has she examining'], 'Повнота відповіді є теперішнім доказом попереднього опрацювання матеріалів.', 'Вона вже вивчила всю справу, якщо її відповідь охоплює кожне заперечення?', 'Has she examined the full case file if her response addresses every objection?', ['Have', 'examine', 'examining', 'he', 'files']),
        ppQuestion('{a1} so far if only the methodology remains unclear?', 'Which assumptions have the reviewers challenged', ['Which assumptions have the reviewers challenged', 'Which assumptions has the reviewers challenged', 'Which assumptions have the reviewers challenge', 'Which assumptions the reviewers have challenged', 'Which have the reviewers challenged assumptions'], 'So far просить назвати проміжний результат рецензування до цього моменту.', 'Які припущення рецензенти вже поставили під сумнів, якщо незрозумілою лишилася тільки методологія?', 'Which assumptions have the reviewers challenged so far if only the methodology remains unclear?', ['has', 'challenge', 'challenging', 'What', 'accepted']),
        ppQuestion('{a1} this argument in earlier literature?', 'Have you ever encountered', ['Have you ever encountered', 'Has you ever encountered', 'Have you ever encounter', 'Have ever you encountered', 'Have you ever encountering'], 'Запит стосується всього досвіду читання попередніх досліджень.', 'Ти коли-небудь раніше зустрічав цей аргумент у науковій літературі?', 'Have you ever encountered this argument in earlier literature?', ['Has', 'encounter', 'encountering', 'developed', 'arguments']),
        ppQuestion('{a1} the clarification the regulator requested yet?', 'Has counsel provided', ['Has counsel provided', 'Have counsel provided', 'Has counsel provide', 'Has provided counsel', 'Has counsel providing'], 'Регулятор досі чекає, тому перевіряється актуальний результат надання відповіді.', 'Юрисконсульт уже надав уточнення, яке запросив регулятор?', 'Has counsel provided the clarification the regulator requested yet?', ['Have', 'provide', 'providing', 'requests', 'clarifications']),
        ppQuestion('Has the revised protocol resolved the inconsistency? — Yes, it {a1}.', 'has', ['has', 'have', 'did', 'does', 'is'], 'У стислому підтвердженні не потрібно повторювати всю дію.', 'Переглянутий протокол усунув невідповідність? — Так.', 'Yes, it has.', ['have', 'they', 'is', 'resolved']),
        ppQuestion('{a1} the missing evidence that now strengthens the conclusion?', 'Have the researchers supplied', ['Have the researchers supplied', 'Has the researchers supplied', 'Have the researchers supply', 'Have supplied the researchers', 'Have the researchers supplying'], 'Сильніший висновок є наслідком нового результату дослідження.', 'Дослідники вже надали відсутні докази, які тепер посилюють висновок?', 'Have the researchers supplied the missing evidence that now strengthens the conclusion?', ['Has', 'supply', 'supplying', 'researcher', 'removed']),
        ppQuestion('{a1} all the citations that now follow one standard?', 'Has the editor corrected', ['Has the editor corrected', 'Have the editor corrected', 'Has the editor correct', 'Has corrected the editor', 'Has the editor correcting'], 'Однакове оформлення є видимим теперішнім результатом редакторської роботи.', 'Редактор уже виправив усі посилання, які тепер відповідають одному стандарту?', 'Has the editor corrected all the citations that now follow one standard?', ['Have', 'correct', 'correcting', 'editors', 'deleted']),
        ppQuestion('{a1} the appendix that has vanished from the workspace?', 'Where has the team moved', ['Where has the team moved', 'Where have the team moved', 'Where has the team move', 'Where the team has moved', 'Where has moved the team'], 'Документ шукають зараз, отже важливе його актуальне місце.', 'Куди команда перемістила додаток, який зник із робочого простору?', 'Where has the team moved the appendix that has vanished from the workspace?', ['have', 'move', 'moving', 'When', 'deleted']),
        ppQuestion('{a1} such an interpretation in previous rulings?', 'Has the court ever recognized', ['Has the court ever recognized', 'Have the court ever recognized', 'Has the court ever recognize', 'Has ever the court recognized', 'Has the court ever recognizing'], 'Нинішнє тлумачення порівнюють з усією попередньою судовою практикою.', 'Суд коли-небудь раніше визнавав таке тлумачення у своїх рішеннях?', 'Has the court ever recognized such an interpretation in previous rulings?', ['Have', 'recognize', 'recognizing', 'courts', 'rejected']),
        ppQuestion('{a1} the revision?', 'Have the reviewers already accepted', ['Have the reviewers already accepted', 'Has the reviewers already accepted', 'Have the reviewers already accept', 'Have already the reviewers accepted', 'Have the reviewers already accepting'], 'Неочікувано позитивний тон указує на можливий ранній результат.', 'Рецензенти вже прийняли нову редакцію?', 'Have the reviewers already accepted the revision?', ['Has', 'accept', 'accepting', 'reviewer', 'rejected']),
        ppQuestion('{a1} the source data if the figures still do not reconcile?', 'Have you verified', ['Have you verified', 'Has you verified', 'Have you verify', 'Have verified you', 'Have you verifying'], 'Невідповідність зберігається зараз, тому перевіряють уже виконану верифікацію.', 'Ти вже перевірив вихідні дані, якщо цифри досі не узгоджуються?', 'Have you verified the source data if the figures still do not reconcile?', ['Has', 'verify', 'verifying', 'changed', 'sources']),
    ],
    'C2' => [
        ppQuestion('{a1} the conceptual framework?', 'How has the latest evidence altered', ['How has the latest evidence altered', 'How have the latest evidence altered', 'How has the latest evidence alter', 'How the latest evidence has altered', 'How has altered the latest evidence'], 'Йдеться про те, як нові дані змінили теперішній стан аргументації.', 'Як найновіші докази змінили концептуальну рамку?', 'How has the latest evidence altered the conceptual framework?', ['have', 'alter', 'altering', 'Why', 'frameworks']),
        ppQuestion('{a1} confidential evidence if the ruling cites material outside the public file?', 'Has the panel considered', ['Has the panel considered', 'Have the panel considered', 'Has the panel consider', 'Has considered the panel', 'Has the panel considering'], 'Посилання в чинному рішенні є наслідком попереднього розгляду матеріалів.', 'Колегія вже розглянула конфіденційні докази, якщо рішення посилається на матеріали поза відкритою справою?', 'Has the panel considered confidential evidence if the ruling cites material outside the public file?', ['Have', 'consider', 'considering', 'panels', 'ignored']),
        ppQuestion('{a1} the convergence now visible in the models?', 'What has brought about', ['What has brought about', 'What have brought about', 'What has bring about', 'What brought has about', 'What has bringing about'], 'Запитується причина зміни, результат якої вже видно в моделях.', 'Що спричинило зближення, яке тепер видно в моделях?', 'What has brought about the convergence now visible in the models?', ['have', 'bring', 'bringing', 'Which', 'divergence']),
        ppQuestion('{a1} elsewhere before?', 'Has this doctrine ever been applied', ['Has this doctrine ever been applied', 'Have this doctrine ever been applied', 'Has this doctrine ever applied', 'Has ever this doctrine been applied', 'Has this doctrine ever be applied'], 'Запит охоплює всю попередню практику застосування доктрини.', 'Цю доктрину коли-небудь раніше застосовували в інших юрисдикціях?', 'Has this doctrine ever been applied elsewhere before?', ['Have', 'apply', 'applying', 'never', 'doctrines']),
        ppQuestion('{a1} the decision the public is still waiting for yet?', 'Has the authority published', ['Has the authority published', 'Have the authority published', 'Has the authority publish', 'Has published the authority', 'Has the authority publishing'], 'Дедлайн минув, а результат досі відсутній у відкритому доступі.', 'Орган влади вже оприлюднив рішення, на яке досі чекає громадськість?', 'Has the authority published the decision the public is still waiting for yet?', ['Have', 'publish', 'publishing', 'authorities', 'withheld']),
        ppQuestion('Has the new evidence undermined the original inference? — It certainly {a1}.', 'has', ['has', 'have', 'did', 'does', 'is'], 'Після прислівника certainly у короткій відповіді залишається лише допоміжний елемент.', 'Нові докази підірвали початковий висновок? — Безперечно.', 'It certainly has.', ['have', 'they', 'is', 'undermined']),
        ppQuestion('{a1} the apparent contradiction between the two accounts?', 'How have the investigators resolved', ['How have the investigators resolved', 'How has the investigators resolved', 'How have the investigators resolve', 'How the investigators have resolved', 'How have resolved the investigators'], 'Сумісність двох версій є теперішнім результатом проведеного аналізу.', 'Як слідчі усунули очевидну суперечність між двома свідченнями?', 'How have the investigators resolved the apparent contradiction between the two accounts?', ['has', 'resolve', 'resolving', 'Why', 'investigator']),
        ppQuestion('{a1} its interpretation even though the treaty text remains unchanged?', 'Has the tribunal revised', ['Has the tribunal revised', 'Have the tribunal revised', 'Has the tribunal revise', 'Has revised the tribunal', 'Has the tribunal revising'], 'Незмінний текст контрастує з можливим новим результатом його тлумачення.', 'Трибунал уже переглянув тлумачення, хоча текст договору не змінився?', 'Has the tribunal revised its interpretation even though the treaty text remains unchanged?', ['Have', 'revise', 'revising', 'tribunals', 'texts']),
        ppQuestion('{a1} the assumptions that no independent team can reproduce?', 'Where have the authors documented', ['Where have the authors documented', 'Where has the authors documented', 'Where have the authors document', 'Where the authors have documented', 'Where have documented the authors'], 'Неможливість відтворення робить місце документації актуальною проблемою.', 'Де автори задокументували припущення, які не може відтворити жодна незалежна команда?', 'Where have the authors documented the assumptions that no independent team can reproduce?', ['has', 'document', 'documenting', 'When', 'author']),
        ppQuestion('{a1} a remedy this broad in comparable cases before?', 'Has the court ever granted', ['Has the court ever granted', 'Have the court ever granted', 'Has the court ever grant', 'Has ever the court granted', 'Has the court ever granting'], 'Широту нинішнього засобу захисту порівнюють з усією попередньою практикою.', 'Суд коли-небудь раніше надавав настільки широкий засіб захисту в подібних справах?', 'Has the court ever granted a remedy this broad in comparable cases before?', ['Have', 'grant', 'granting', 'courts', 'narrow']),
        ppQuestion('{a1} a final position even though the consultation closed only yesterday?', 'Has the commission already adopted', ['Has the commission already adopted', 'Have the commission already adopted', 'Has the commission already adopt', 'Has already the commission adopted', 'Has the commission already adopting'], 'Короткий проміжок після консультації надає already відтінку здивування.', 'Комісія вже ухвалила остаточну позицію, хоча консультація завершилася лише вчора?', 'Has the commission already adopted a final position even though the consultation closed only yesterday?', ['Have', 'adopt', 'adopting', 'commissions', 'draft']),
        ppQuestion('{a1} all relevant correspondence if the public record is still incomplete?', 'Have the parties disclosed', ['Have the parties disclosed', 'Has the parties disclosed', 'Have the parties disclose', 'Have disclosed the parties', 'Have the parties disclosing'], 'Неповний відкритий реєстр є поточним наслідком можливої незавершеної дії.', 'Сторони вже розкрили все релевантне листування, якщо відкритий реєстр досі неповний?', 'Have the parties disclosed all relevant correspondence if the public record is still incomplete?', ['Has', 'disclose', 'disclosing', 'party', 'withheld']),
    ],
];

$standard = ppQuestionsReadJson($standardPath);
$compose = ppQuestionsReadJson($composePath);

if (count($standard['questions'] ?? []) !== 72 || count($compose['questions'] ?? []) !== 72) {
    throw new RuntimeException('Expected 72 questions in each Present Perfect question bank.');
}

foreach ($standard['questions'] as &$question) {
    $level = (string) ($question['level'] ?? '');
    preg_match('/-([0-9]{2})$/', (string) ($question['uuid'] ?? ''), $matches);
    $index = ((int) ($matches[1] ?? 0)) - 1;
    $item = $content[$level][$index] ?? null;

    if (! is_array($item)) {
        throw new RuntimeException("Missing standard replacement for {$level} index {$index}");
    }

    $gapTags = $question['markers']['a1']['gap_tags'] ?? [];
    $question['question'] = $item['question'];
    $question['markers'] = [
        'a1' => [
            'answer' => $item['answer'],
            'options' => $item['options'],
            'verb_hint' => $item['hint'],
            'gap_tags' => $gapTags,
        ],
    ];
    $question['variants'] = [$item['question']];
}
unset($question);

foreach ($compose['questions'] as &$question) {
    $level = (string) ($question['level'] ?? '');
    preg_match('/-([0-9]{2})$/', (string) ($question['uuid'] ?? ''), $matches);
    $index = ((int) ($matches[1] ?? 0)) - 1;
    $item = $content[$level][$index] ?? null;

    if (! is_array($item)) {
        throw new RuntimeException("Missing Sentence Builder replacement for {$level} index {$index}");
    }

    $targetWithoutTerminalPunctuation = preg_replace('/[.!?]$/u', '', $item['target']);
    $tokens = preg_split('/\s+/u', trim((string) $targetWithoutTerminalPunctuation)) ?: [];
    $tokens = array_values(array_filter($tokens, static fn (string $token): bool => $token !== ''));

    if ($tokens === []) {
        throw new RuntimeException("Missing target tokens for {$level} index {$index}");
    }

    $answers = [];
    foreach ($tokens as $tokenIndex => $token) {
        $answers['a'.($tokenIndex + 1)] = $token;
    }

    $correctLookup = array_count_values(array_map('mb_strtolower', $tokens));
    foreach ($item['distractors'] as $distractor) {
        if (isset($correctLookup[mb_strtolower($distractor)])) {
            throw new RuntimeException("Distractor duplicates a correct token in {$level} index {$index}: {$distractor}");
        }
    }

    $question['question'] = $item['prompt'];
    $question['answers'] = $answers;
    $question['options'] = array_merge($tokens, $item['distractors']);
    $question['verb_hints'] = ['a1' => $item['hint']];
    $question['variants'] = [];
}
unset($question);

ppQuestionsWriteJson($standardPath, $standard);
ppQuestionsWriteJson($composePath, $compose);

echo "Updated Present Perfect questions content.\n";
