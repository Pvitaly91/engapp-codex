# M55 — повна читабельна UK редакція 1.0.0

Статус: reviewed-before-apply. Цей файл механічно розгортає всі навчальні поля погодженого frozen master без змістових скорочень. Він не підтверджує локального apply або browser acceptance. JSON зберігає точні типи, source pointers і структуру; нижче HTML emphasis прибрано, а br передано розривом рядка.

Master: `docs/content/m55-passive-review-infinitive.v1.0.0.json`

Master SHA-256: `d99299587c3876973869c1b9593bb158cf3904cae2df1c972d07866ce573cd1d`

18 власних tasks (2 selects + 2 choices + 2 inputs на урок) і 15 reused widget questions — різні джерела. Ручний і token способи одного input не рахуються як два tasks.

## theory-passive-voice-common-mistakes

Owner: `Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceCommonMistakesTheorySeeder`

URL: [http://gramlyze.loc/theory/passive-voice/theory-passive-voice-common-mistakes](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-common-mistakes)

Ancestry: `passive-voice`

Рівень: A2–B2; власна практика: A2

Subtitle: Приклади вживання та типові помилки — як перевірити підмет, форму be, V3, питання й заперечення в пасиві та відрізнити помилку від іншого значення або стилістичного вибору.

Page text: Самоперевірка пасиву: підмет і допоміжні дієслова, V3, питання, заперечення, by/with, переклади та практика.

### mistakes-hero — hero

Source slot: 0; key: `mistakes-hero`; column: header; level: A2

Поле `mistakes-hero/data/level` — Рівень:

A2–B2

Поле `mistakes-hero/data/intro` — Вступ:

Спершу визнач, що хочеш повідомити й хто зазнає дії. У звичайному be-пасиві перевір форму be та V3, потім питання, заперечення й потрібного виконавця. Не кожне інше речення — помилка: іноді змінюється зміст, фокус або ситуація спілкування.

Поле `mistakes-hero/data/rules/0/label` — Назва пункту:

ПІДМЕТ І BE

Поле `mistakes-hero/data/rules/0/color` — навчальне значення:

emerald

Поле `mistakes-hero/data/rules/0/text` — Правило:

Be узгоджується з підметом пасиву й передає потрібний час.

Поле `mistakes-hero/data/rules/0/example` — Приклад:

The windows were cleaned yesterday.
Вікна помили вчора.

Поле `mistakes-hero/data/rules/1/label` — Назва пункту:

ПОТРІБНА ФОРМА V3

Поле `mistakes-hero/data/rules/1/color` — навчальне значення:

rose

Поле `mistakes-hero/data/rules/1/text` — Правило:

V3 має свою функцію; у regular verbs його написання часто збігається з V2.

Поле `mistakes-hero/data/rules/1/example` — Приклад:

The window was broken.
Вікно розбили.

Поле `mistakes-hero/data/rules/2/label` — Назва пункту:

ПЕРШЕ ДОПОМІЖНЕ

Поле `mistakes-hero/data/rules/2/color` — навчальне значення:

blue

Поле `mistakes-hero/data/rules/2/text` — Правило:

У нейтральному be-passive питанні перше допоміжне стоїть перед підметом.

Поле `mistakes-hero/data/rules/2/example` — Приклад:

Was the parcel delivered?
Посилку доставили?

### 1. Чи є потрібне be і чи узгоджене воно з підметом? — mistakes-grid

Source slot: 1; key: `mistakes-be`; column: left; level: A2

Поле `mistakes-be/data/title` — Заголовок:

1. Чи є потрібне be і чи узгоджене воно з підметом?

Поле `mistakes-be/data/items/0/label` — Назва пункту:

ПОВНЕ РЕЧЕННЯ

Поле `mistakes-be/data/items/0/color` — навчальне значення:

rose

Поле `mistakes-be/data/items/0/title` — Заголовок:

Для самостійного повідомлення про вчорашню дію потрібне was.

Поле `mistakes-be/data/items/0/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The letter written yesterday.

Поле `mistakes-be/data/items/0/wrong_ua` — Український задум:

Задум: Лист написали вчора.

Поле `mistakes-be/data/items/0/right_en` — Виправлення:

The letter was written yesterday.

Поле `mistakes-be/data/items/0/right_ua` — Переклад виправлення:

Лист написали вчора.

Поле `mistakes-be/data/items/0/note` — Примітка:

Без was це не повне самостійне речення в цьому завданні. Така група слів може бути частиною більшого речення: The letter written yesterday is on the desk. — Лист, написаний учора, лежить на столі.

Поле `mistakes-be/data/items/1/label` — Назва пункту:

МНОЖИНА В PRESENT

Поле `mistakes-be/data/items/1/color` — навчальне значення:

amber

Поле `mistakes-be/data/items/1/title` — Заголовок:

Letters — множина, тому are, а не is.

Поле `mistakes-be/data/items/1/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The letters is sent every day.

Поле `mistakes-be/data/items/1/wrong_ua` — Український задум:

Задум: Листи надсилають щодня.

Поле `mistakes-be/data/items/1/right_en` — Виправлення:

The letters are sent every day.

Поле `mistakes-be/data/items/1/right_ua` — Переклад виправлення:

Листи надсилають щодня.

Поле `mistakes-be/data/items/1/note` — Примітка:

Час лишається Present Simple; змінюємо лише помилкове узгодження.

Поле `mistakes-be/data/items/2/label` — Назва пункту:

МНОЖИНА В PAST

Поле `mistakes-be/data/items/2/color` — навчальне значення:

sky

Поле `mistakes-be/data/items/2/title` — Заголовок:

Windows — множина, тому were, а не was.

Поле `mistakes-be/data/items/2/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The windows was broken.

Поле `mistakes-be/data/items/2/wrong_ua` — Український задум:

Задум: Вікна розбили.

Поле `mistakes-be/data/items/2/right_en` — Виправлення:

The windows were broken.

Поле `mistakes-be/data/items/2/right_ua` — Переклад виправлення:

Вікна розбили.

Поле `mistakes-be/data/items/2/note` — Примітка:

Форму be визначає windows, не можливий виконавець після by.

Поле `mistakes-be/data/items/3/label` — Назва пункту:

PERFECT: BEEN

Поле `mistakes-be/data/items/3/color` — навчальне значення:

rose

Поле `mistakes-be/data/items/3/title` — Заголовок:

У повідомленні про виконану роботу перевір увесь ланцюжок.

Поле `mistakes-be/data/items/3/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The work has done.

Поле `mistakes-be/data/items/3/wrong_ua` — Український задум:

Задум: Роботу виконано.

Поле `mistakes-be/data/items/3/right_en` — Виправлення:

The work has been done.

Поле `mistakes-be/data/items/3/right_ua` — Переклад виправлення:

Роботу виконано.

Поле `mistakes-be/data/items/3/note` — Примітка:

У Present Perfect passive: has/have been + V3. Has погоджується з work; been не замінює done. Це розширений приклад для тих, хто вже знає Present Perfect.

### 2. Чи вибрана форма Past Participle — V3? — mistakes-grid

Source slot: 2; key: `mistakes-participle`; column: left; level: A2

Поле `mistakes-participle/data/title` — Заголовок:

2. Чи вибрана форма Past Participle — V3?

Поле `mistakes-participle/data/items/0/label` — Назва пункту:

НЕ ПОЧАТКОВА ФОРМА

Поле `mistakes-participle/data/items/0/color` — навчальне значення:

rose

Поле `mistakes-participle/data/items/0/title` — Заголовок:

Після is у цьому пасиві потрібне spoken.

Поле `mistakes-participle/data/items/0/wrong_en` — Помилкова / невідповідна заданому задуму версія:

English is speak here.

Поле `mistakes-participle/data/items/0/wrong_ua` — Український задум:

Задум: Тут розмовляють англійською.

Поле `mistakes-participle/data/items/0/right_en` — Виправлення:

English is spoken here.

Поле `mistakes-participle/data/items/0/right_ua` — Переклад виправлення:

Тут розмовляють англійською.

Поле `mistakes-participle/data/items/0/note` — Примітка:

Speak — початкова форма; spoken — V3.

Поле `mistakes-participle/data/items/1/label` — Назва пункту:

V2 І V3: РІЗНІ ФУНКЦІЇ

Поле `mistakes-participle/data/items/1/color` — навчальне значення:

amber

Поле `mistakes-participle/data/items/1/title` — Заголовок:

У break форми відрізняються: broke — V2, broken — V3.

Поле `mistakes-participle/data/items/1/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The window was broke.

Поле `mistakes-participle/data/items/1/wrong_ua` — Український задум:

Задум: Вікно розбили.

Поле `mistakes-participle/data/items/1/right_en` — Виправлення:

The window was broken.

Поле `mistakes-participle/data/items/1/right_ua` — Переклад виправлення:

Вікно розбили.

Поле `mistakes-participle/data/items/1/note` — Примітка:

Не вимагай різного написання завжди: cleaned має однакову форму V2/V3; cut і put також не змінюються. Функцію визначає конструкція.

Поле `mistakes-participle/data/items/2/label` — Назва пункту:

НЕПРАВИЛЬНЕ ДІЄСЛОВО

Поле `mistakes-participle/data/items/2/color` — навчальне значення:

sky

Поле `mistakes-participle/data/items/2/title` — Заголовок:

Write має V3 written, а не writed.

Поле `mistakes-participle/data/items/2/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The book was writed by him.

Поле `mistakes-participle/data/items/2/wrong_ua` — Український задум:

Задум: Книжку написав він.

Поле `mistakes-participle/data/items/2/right_en` — Виправлення:

The book was written by him.

Поле `mistakes-participle/data/items/2/right_ua` — Переклад виправлення:

Книжку написав він.

Поле `mistakes-participle/data/items/2/note` — Примітка:

Зберігаємо автора й минулий час; виправляємо форму головного дієслова.

Поле `mistakes-participle/data/items/3/label` — Назва пункту:

ПРОЦЕС: BEING + V3

Поле `mistakes-participle/data/items/3/color` — навчальне значення:

rose

Поле `mistakes-participle/data/items/3/title` — Заголовок:

Якщо хтось зараз пише лист, лист зазнає дії.

Поле `mistakes-participle/data/items/3/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The letter is writing now.

Поле `mistakes-participle/data/items/3/wrong_ua` — Український задум:

Задум: Лист зараз пишуть.

Поле `mistakes-participle/data/items/3/right_en` — Виправлення:

The letter is being written now.

Поле `mistakes-participle/data/items/3/right_ua` — Переклад виправлення:

Лист зараз пишуть.

Поле `mistakes-participle/data/items/3/note` — Примітка:

Present Continuous passive: is/are being + V3. Is writing саме по собі було б активною формою й не передає заданої ролі листа.

### 3. Чи правильно побудоване звичайне питання? — mistakes-grid

Source slot: 3; key: `mistakes-questions`; column: left; level: A2

Поле `mistakes-questions/data/title` — Заголовок:

3. Чи правильно побудоване звичайне питання?

Поле `mistakes-questions/data/items/0/label` — Назва пункту:

BE ВЖЕ Є ДОПОМІЖНИМ

Поле `mistakes-questions/data/items/0/color` — навчальне значення:

rose

Поле `mistakes-questions/data/items/0/title` — Заголовок:

Не додавай does до звичайного питання з is.

Поле `mistakes-questions/data/items/0/wrong_en` — Помилкова / невідповідна заданому задуму версія:

Does the letter is written?

Поле `mistakes-questions/data/items/0/wrong_ua` — Український задум:

Задум: Лист написано?

Поле `mistakes-questions/data/items/0/right_en` — Виправлення:

Is the letter written?

Поле `mistakes-questions/data/items/0/right_ua` — Переклад виправлення:

Лист написано?

Поле `mistakes-questions/data/items/0/note` — Примітка:

Межа правила — be-passive. Get-passive має іншу будову: Did the parcel get damaged? — Посилка пошкодилася? Тут did потрібне для get; цей короткий контраст не змінює правило про is.

Поле `mistakes-questions/data/items/1/label` — Назва пункту:

ПИТАЛЬНЕ СЛОВО

Поле `mistakes-questions/data/items/1/color` — навчальне значення:

amber

Поле `mistakes-questions/data/items/1/title` — Заголовок:

У нейтральному питанні після where ставимо was, потім підмет.

Поле `mistakes-questions/data/items/1/wrong_en` — Помилкова / невідповідна заданому задуму версія:

Where the book was found?

Поле `mistakes-questions/data/items/1/wrong_ua` — Український задум:

Задум: Де знайшли книжку?

Поле `mistakes-questions/data/items/1/right_en` — Виправлення:

Where was the book found?

Поле `mistakes-questions/data/items/1/right_ua` — Переклад виправлення:

Де знайшли книжку?

Поле `mistakes-questions/data/items/1/note` — Примітка:

Це самостійне питання про місце. Порядок у непрямому питанні — окрема модель, не виняток усередині цього завдання.

Поле `mistakes-questions/data/items/2/label` — Назва пункту:

КОРОТКА ВІДПОВІДЬ

Поле `mistakes-questions/data/items/2/color` — навчальне значення:

sky

Поле `mistakes-questions/data/items/2/title` — Заголовок:

У короткій відповіді повторюємо допоміжне was.

Поле `mistakes-questions/data/items/2/wrong_en` — Помилкова / невідповідна заданому задуму версія:

Was the work done? — Yes, it done.

Поле `mistakes-questions/data/items/2/wrong_ua` — Український задум:

Задум: Роботу виконали? — Так.

Поле `mistakes-questions/data/items/2/right_en` — Виправлення:

Was the work done? — Yes, it was.

Поле `mistakes-questions/data/items/2/right_ua` — Переклад виправлення:

Роботу виконали? — Так.

Поле `mistakes-questions/data/items/2/note` — Примітка:

Done не замінює особову допоміжну форму. Для множини відповідь мала б відповідні займенник і were.

### 4. Чи збережені not і повний допоміжний ланцюжок? — mistakes-grid

Source slot: 4; key: `mistakes-negatives`; column: left; level: A2

Поле `mistakes-negatives/data/title` — Заголовок:

4. Чи збережені not і повний допоміжний ланцюжок?

Поле `mistakes-negatives/data/items/0/label` — Назва пункту:

NOT Є, АЛЕ V3 НЕПРАВИЛЬНЕ

Поле `mistakes-negatives/data/items/0/color` — навчальне значення:

rose

Поле `mistakes-negatives/data/items/0/title` — Заголовок:

У цьому реченні проблема в write, а не в місці not.

Поле `mistakes-negatives/data/items/0/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The letter is not write.

Поле `mistakes-negatives/data/items/0/wrong_ua` — Український задум:

Задум: Лист не написано.

Поле `mistakes-negatives/data/items/0/right_en` — Виправлення:

The letter is not written.

Поле `mistakes-negatives/data/items/0/right_ua` — Переклад виправлення:

Лист не написано.

Поле `mistakes-negatives/data/items/0/note` — Примітка:

Not уже стоїть після is. Потрібна форма written.

Поле `mistakes-negatives/data/items/1/label` — Назва пункту:

ЗАДАНИЙ ПАСИВНИЙ ЗМІСТ

Поле `mistakes-negatives/data/items/1/color` — навчальне значення:

amber

Поле `mistakes-negatives/data/items/1/title` — Заголовок:

Треба повідомити, що роботу не завершено, а не що вона сама щось завершує.

Поле `mistakes-negatives/data/items/1/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The work doesn't finished.

Поле `mistakes-negatives/data/items/1/wrong_ua` — Український задум:

Задум: Роботу не завершено.

Поле `mistakes-negatives/data/items/1/right_en` — Виправлення:

The work isn't finished.

Поле `mistakes-negatives/data/items/1/right_ua` — Переклад виправлення:

Роботу не завершено.

Поле `mistakes-negatives/data/items/1/note` — Примітка:

У заданій моделі використовуємо is not + finished. Не намагаємося вгадати інший активний зміст незавершеної чернетки.

Поле `mistakes-negatives/data/items/2/label` — Назва пункту:

NOT, А НЕ NO

Поле `mistakes-negatives/data/items/2/color` — навчальне значення:

sky

Поле `mistakes-negatives/data/items/2/title` — Заголовок:

Заперечення після is утворює not.

Поле `mistakes-negatives/data/items/2/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The letter is no written.

Поле `mistakes-negatives/data/items/2/wrong_ua` — Український задум:

Задум: Лист не написано.

Поле `mistakes-negatives/data/items/2/right_en` — Виправлення:

The letter is not written.

Поле `mistakes-negatives/data/items/2/right_ua` — Переклад виправлення:

Лист не написано.

Поле `mistakes-negatives/data/items/2/note` — Примітка:

Можливе скорочення isn't; зміст заперечення зберігається.

Поле `mistakes-negatives/data/items/3/label` — Назва пункту:

PERFECT НЕ ВТРАЧАЄ BEEN

Поле `mistakes-negatives/data/items/3/color` — навчальне значення:

rose

Поле `mistakes-negatives/data/items/3/title` — Заголовок:

Not стоїть після першого допоміжного has.

Поле `mistakes-negatives/data/items/3/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The work hasn't done.

Поле `mistakes-negatives/data/items/3/wrong_ua` — Український задум:

Задум: Роботу ще не виконано.

Поле `mistakes-negatives/data/items/3/right_en` — Виправлення:

The work hasn't been done.

Поле `mistakes-negatives/data/items/3/right_ua` — Переклад виправлення:

Роботу ще не виконано.

Поле `mistakes-negatives/data/items/3/note` — Примітка:

Has not / hasn't + been + done. Заперечення не дозволяє опустити been; це розширена perfect-модель.

### 5. By чи with: чи правильно передані ролі? — mistakes-grid

Source slot: 5; key: `mistakes-agent-instrument`; column: left; level: B1

Поле `mistakes-agent-instrument/data/title` — Заголовок:

5. By чи with: чи правильно передані ролі?

Поле `mistakes-agent-instrument/data/items/0/label` — Назва пункту:

ВІДПРАВНИЦЯ, НЕ СУПУТНИЦЯ

Поле `mistakes-agent-instrument/data/items/0/color` — навчальне значення:

rose

Поле `mistakes-agent-instrument/data/items/0/title` — Заголовок:

Олена сама відправила посилку. Потрібно назвати виконавицю.

Поле `mistakes-agent-instrument/data/items/0/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The parcel was sent with Olena.

Поле `mistakes-agent-instrument/data/items/0/wrong_ua` — Український задум:

Задум: Посилку відправила Олена.

Поле `mistakes-agent-instrument/data/items/0/right_en` — Виправлення:

The parcel was sent by Olena.

Поле `mistakes-agent-instrument/data/items/0/right_ua` — Переклад виправлення:

Посилку відправила Олена.

Поле `mistakes-agent-instrument/data/items/0/note` — Примітка:

With Olena може мати інший зміст — разом з Оленою; це не заборона граматичного with у всіх контекстах. У нашій ситуації виконавицю називаємо через by.

Поле `mistakes-agent-instrument/data/items/1/label` — Назва пункту:

МАРКЕР ЯК ІНСТРУМЕНТ

Поле `mistakes-agent-instrument/data/items/1/color` — навчальне значення:

amber

Поле `mistakes-agent-instrument/data/items/1/title` — Заголовок:

Людина зробила позначку маркером; маркер не діяв сам.

Поле `mistakes-agent-instrument/data/items/1/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The mark was made by with a marker.

Поле `mistakes-agent-instrument/data/items/1/wrong_ua` — Український задум:

Задум: Позначку зробили маркером.

Поле `mistakes-agent-instrument/data/items/1/right_en` — Виправлення:

The mark was made with a marker.

Поле `mistakes-agent-instrument/data/items/1/right_ua` — Переклад виправлення:

Позначку зробили маркером.

Поле `mistakes-agent-instrument/data/items/1/note` — Примітка:

Не поєднуємо by with перед одним інструментом. With a marker називає засіб. By може називати також неістоту-причину: The fence was damaged by the wind. — Паркан пошкодив вітер. Отже, правило не зводиться до «після by лише людина».

### 6. Інший контекст — не обов’язково помилка — usage-panels

Source slot: 6; key: `mistakes-context`; column: left; level: B1

Поле `mistakes-context/data/title` — Заголовок:

6. Інший контекст — не обов’язково помилка

Поле `mistakes-context/data/sections/0/label` — Назва пункту:

НЕЙТРАЛЬНЕ ПИТАННЯ Й ПЕРЕПИТУВАННЯ

Поле `mistakes-context/data/sections/0/color` — навчальне значення:

blue

Поле `mistakes-context/data/sections/0/description` — Пояснення:

Для першого нейтрального запиту використовуй інверсію. Але порядок твердження з питальною інтонацією можливий, коли співрозмовник здивовано перепитує вже почуте.

Поле `mistakes-context/data/sections/0/examples/0/en` — Англійською:

Is the letter written?

Поле `mistakes-context/data/sections/0/examples/0/ua` — Українською:

Лист написано?

Поле `mistakes-context/data/sections/0/examples/1/en` — Англійською:

The letter is written?

Поле `mistakes-context/data/sections/0/examples/1/ua` — Українською:

То лист уже написано?

Поле `mistakes-context/data/sections/0/note` — Примітка:

Другий варіант потребує відповідної розмовної ситуації; його не підставляємо замість нейтрального питання у початковій вправі.

Поле `mistakes-context/data/sections/1/label` — Назва пункту:

АГЕНТ МОЖЕ БУТИ НЕОБОВ’ЯЗКОВИМ

Поле `mistakes-context/data/sections/1/color` — навчальне значення:

amber

Поле `mistakes-context/data/sections/1/description` — Пояснення:

By someone граматично можливе, але часто не додає потрібних відомостей. Опущення агента — редакторський вибір за контекстом, не виправлення неграматичного речення.

Поле `mistakes-context/data/sections/1/examples/0/en` — Англійською:

My car was stolen by someone.

Поле `mistakes-context/data/sections/1/examples/0/ua` — Українською:

Мій автомобіль хтось украв.

Поле `mistakes-context/data/sections/1/examples/1/en` — Англійською:

My car was stolen.

Поле `mistakes-context/data/sections/1/examples/1/ua` — Українською:

Мій автомобіль украли.

Поле `mistakes-context/data/sections/1/note` — Примітка:

Коротший варіант не називає виконавця прямо. Якщо протиставляємо невідомого виконавця конкретній особі, someone може бути корисним.

Поле `mistakes-context/data/sections/2/label` — Назва пункту:

НЕЙТРАЛЬНИЙ ПОРЯДОК І ВИНЕСЕННЯ

Поле `mistakes-context/data/sections/2/color` — навчальне значення:

slate

Поле `mistakes-context/data/sections/2/description` — Пояснення:

Для звичайного повідомлення постав by-phrase після дієслівної конструкції. У завданні на початковий рівень прямо вкажи нейтральний порядок. Це робоча модель вправи, а не абсолютна заборона всіх контекстних або стилістичних винесень.

Поле `mistakes-context/data/sections/2/examples/0/en` — Англійською:

The report was prepared by the team.

Поле `mistakes-context/data/sections/2/examples/0/ua` — Українською:

Звіт підготувала команда.

Поле `mistakes-context/data/sections/2/examples/1/en` — Англійською:

The report was prepared by the team, not by an outside consultant.

Поле `mistakes-context/data/sections/2/examples/1/ua` — Українською:

Звіт підготувала команда, а не зовнішній консультант.

Поле `mistakes-context/data/sections/2/note` — Примітка:

У другому реченні збережено контраст виконавців без зміни нейтрального порядку. Окремий маркований порядок не потрібно вимагати в A2 вправі чи використовувати без контексту як доказ неграматичності всіх винесених by-фраз.

Поле `mistakes-context/data/sections/3/label` — Назва пункту:

ПОВНИЙ ЛАНЦЮЖОК У РОЗШИРЕНИХ ФОРМАХ

Поле `mistakes-context/data/sections/3/color` — навчальне значення:

emerald

Поле `mistakes-context/data/sections/3/description` — Пояснення:

У perfect перевір been, у continuous — being, після modal — be. У питанні виносимо перше допоміжне; not також іде після першого допоміжного.

Поле `mistakes-context/data/sections/3/examples/0/en` — Англійською:

Have all tickets been sold?

Поле `mistakes-context/data/sections/3/examples/0/ua` — Українською:

Усі квитки продано?

Поле `mistakes-context/data/sections/3/examples/1/en` — Англійською:

The room is not being cleaned now.

Поле `mistakes-context/data/sections/3/examples/1/ua` — Українською:

Кімнату зараз не прибирають.

Поле `mistakes-context/data/sections/3/examples/2/en` — Англійською:

The task must be done today.

Поле `mistakes-context/data/sections/3/examples/2/ua` — Українською:

Завдання треба виконати сьогодні.

Поле `mistakes-context/data/sections/3/note` — Примітка:

Це коротка перевірка вже вивчених моделей, а не вимога знати їх усі для базової A2 практики.

### 7. Чи збережено подію, час і потрібних учасників? — comparison-table

Source slot: 7; key: `mistakes-transformations`; column: left; level: A2

Поле `mistakes-transformations/data/title` — Заголовок:

7. Чи збережено подію, час і потрібних учасників?

Поле `mistakes-transformations/data/intro` — Вступ:

Зміна стану не повинна непомітно змінювати час, заперечення або виконавця. Якщо агента опускаємо, прямо розуміємо, яку інформацію більше не називаємо.

Поле `mistakes-transformations/data/headers/0` — навчальне значення:

Актив і переклад

Поле `mistakes-transformations/data/headers/1` — навчальне значення:

Пасив і переклад

Поле `mistakes-transformations/data/headers/2` — навчальне значення:

Перевірка

Поле `mistakes-transformations/data/rows/0/cells/0` — навчальне значення:

Tom writes letters.
Том пише листи.

Поле `mistakes-transformations/data/rows/0/cells/1` — навчальне значення:

Letters are written by Tom.
Листи пише Том.

Поле `mistakes-transformations/data/rows/0/cells/2` — навчальне значення:

Present Simple збережено; letters стає підметом, Tom лишається виконавцем.

Поле `mistakes-transformations/data/rows/1/cells/0` — навчальне значення:

She cleaned the room.
Вона прибрала кімнату.

Поле `mistakes-transformations/data/rows/1/cells/1` — навчальне значення:

The room was cleaned.
Кімнату прибрали.

Поле `mistakes-transformations/data/rows/1/cells/2` — навчальне значення:

Минула дія збережена, але речення більше прямо не каже, що виконавиця — вона. За потреби додай by her.

Поле `mistakes-transformations/data/rows/2/cells/0` — навчальне значення:

They have finished the project.
Вони завершили проєкт.

Поле `mistakes-transformations/data/rows/2/cells/1` — навчальне значення:

The project has been finished.
Проєкт завершено.

Поле `mistakes-transformations/data/rows/2/cells/2` — навчальне значення:

Розширений приклад: perfect лишається perfect; by them можна зберегти, якщо важливо прямо назвати виконавців.

Поле `mistakes-transformations/data/rows/3/cells/0` — навчальне значення:

Someone must do this work.
Хтось має виконати цю роботу.

Поле `mistakes-transformations/data/rows/3/cells/1` — навчальне значення:

This work must be done.
Цю роботу треба виконати.

Поле `mistakes-transformations/data/rows/3/cells/2` — навчальне значення:

Must і необхідність збережено; невизначеного виконавця не названо.

Поле `mistakes-transformations/data/warning` — Застереження:

Не кожне дієслово дозволяє потрібний пасив. Наприклад, arrived у звичайному значенні прибуття лишається активним; дивись окремий урок про обмеження пасиву.

### 8. Алгоритм самоперевірки — forms-grid

Source slot: 8; key: `mistakes-checklist`; column: left; level: A2

Поле `mistakes-checklist/data/title` — Заголовок:

8. Алгоритм самоперевірки

Поле `mistakes-checklist/data/intro` — Вступ:

Перевіряй у цьому порядку: зміст → підмет → допоміжні → V3 → питання/заперечення → агент/інструмент → доречність.

Поле `mistakes-checklist/data/items/0/label` — Назва пункту:

ЗМІСТ І ПІДМЕТ

Поле `mistakes-checklist/data/items/0/title` — Заголовок:

Хто або що зазнає дії?

Поле `mistakes-checklist/data/items/0/subtitle` — Пояснення / приклад:

Збережи подію, час, заперечення й названих важливих учасників.

Поле `mistakes-checklist/data/items/1/label` — Назва пункту:

ДОПОМІЖНІ

Поле `mistakes-checklist/data/items/1/title` — Заголовок:

Потрібне be та правильне узгодження

Поле `mistakes-checklist/data/items/1/subtitle` — Пояснення / приклад:

Для звичайного be-пасиву вибери am/is/are або was/were; у розширених формах перевір увесь ланцюжок.

Поле `mistakes-checklist/data/items/2/label` — Назва пункту:

V3

Поле `mistakes-checklist/data/items/2/title` — Заголовок:

Перевір форму, а не лише закінчення

Поле `mistakes-checklist/data/items/2/subtitle` — Пояснення / приклад:

Broken і written — неправильні форми; cleaned і cut можуть збігатися з V2 за написанням.

Поле `mistakes-checklist/data/items/3/label` — Назва пункту:

ПИТАННЯ Й NOT

Поле `mistakes-checklist/data/items/3/title` — Заголовок:

Знайди перше допоміжне

Поле `mistakes-checklist/data/items/3/subtitle` — Пояснення / приклад:

У нейтральному питанні воно перед підметом; not — після нього. Не перенось правило be-пасиву на всі get-питання.

Поле `mistakes-checklist/data/items/4/label` — Назва пункту:

РОЛІ

Поле `mistakes-checklist/data/items/4/title` — Заголовок:

By — виконавець/причина, with — інструмент

Поле `mistakes-checklist/data/items/4/subtitle` — Пояснення / приклад:

Перевір значення фрази в ситуації; не вибирай прийменник лише за перекладом «ким».

Поле `mistakes-checklist/data/items/5/label` — Назва пункту:

ДОРЕЧНІСТЬ

Поле `mistakes-checklist/data/items/5/title` — Заголовок:

Чи зрозуміло, навіщо саме пасив?

Поле `mistakes-checklist/data/items/5/subtitle` — Пояснення / приклад:

Актив або пасив обирай за темою повідомлення; граматичний інший варіант не автоматично помилковий.

### 9. Короткий підсумок — summary-list

Source slot: 9; key: `mistakes-summary`; column: left; level: A2

Поле `mistakes-summary/data/title` — Заголовок:

9. Короткий підсумок

Поле `mistakes-summary/data/items/0` — навчальне значення:

У звичайному be-пасиві потрібні відповідна форма be та V3; підмет визначає узгодження.

Поле `mistakes-summary/data/items/1` — навчальне значення:

V2 і V3 мають різні функції, але не обов’язково різне написання.

Поле `mistakes-summary/data/items/2` — навчальне значення:

У нейтральному питанні працює перше допоміжне; not ставимо після нього. Get-passive має власну модель.

Поле `mistakes-summary/data/items/3` — навчальне значення:

У perfect не губи been, у continuous — being; після modal у simple passive потрібне be.

Поле `mistakes-summary/data/items/4` — навчальне значення:

By-phrase не є помилкою сама по собі. Зберігай важливого виконавця й не плутай його з інструментом.

Поле `mistakes-summary/data/items/5` — навчальне значення:

Перед виправленням визнач контекст: інший фокус, перепитування або інше значення не доводять граматичної помилки.

### Пов’язані уроки пасивного стану — navigation-chips

Source slot: 10; key: `mistakes-navigation`; column: footer; level: не заданий

Поле `mistakes-navigation/data/title` — Заголовок:

Пов’язані уроки пасивного стану

Поле `mistakes-navigation/data/items/0/label` — Назва пункту:

Утворення пасиву

Поле `mistakes-navigation/data/items/0/url` — Посилання:

/theory/passive-voice/theory-passive-voice-formation-rules

Поле `mistakes-navigation/data/items/1/label` — Назва пункту:

Пасив із модальними

Поле `mistakes-navigation/data/items/1/url` — Посилання:

/theory/passive-voice/theory-passive-voice-modal-verbs

Поле `mistakes-navigation/data/items/2/label` — Назва пункту:

Каузатив

Поле `mistakes-navigation/data/items/2/url` — Посилання:

/theory/passive-voice/theory-passive-voice-causative

Поле `mistakes-navigation/data/items/3/label` — Назва пункту:

Get-passive

Поле `mistakes-navigation/data/items/3/url` — Посилання:

/theory/passive-voice/theory-passive-voice-get-passive

Поле `mistakes-navigation/data/items/4/label` — Назва пункту:

Безособовий пасив

Поле `mistakes-navigation/data/items/4/url` — Посилання:

/theory/passive-voice/theory-passive-voice-impersonal-passive

Поле `mistakes-navigation/data/items/5/label` — Назва пункту:

Питання й заперечення

Поле `mistakes-navigation/data/items/5/url` — Посилання:

/theory/passive-voice/theory-passive-voice-negatives-questions

Поле `mistakes-navigation/data/items/6/label` — Назва пункту:

Фразові дієслова

Поле `mistakes-navigation/data/items/6/url` — Посилання:

/theory/passive-voice/theory-passive-voice-phrasal-verbs

Поле `mistakes-navigation/data/items/7/label` — Назва пункту:

Обмеження пасиву

Поле `mistakes-navigation/data/items/7/url` — Посилання:

/theory/passive-voice/theory-passive-voice-restrictions

Поле `mistakes-navigation/data/items/8/label` — Назва пункту:

Дієслова з двома додатками

Поле `mistakes-navigation/data/items/8/url` — Посилання:

/theory/passive-voice/theory-passive-voice-two-object-verbs

Поле `mistakes-navigation/data/items/9/label` — Назва пункту:

Типові помилки

Поле `mistakes-navigation/data/items/9/url` — Посилання:

/theory/passive-voice/theory-passive-voice-common-mistakes

Поле `mistakes-navigation/data/items/10/label` — Назва пункту:

Формальність і стиль

Поле `mistakes-navigation/data/items/10/url` — Посилання:

/theory/passive-voice/theory-passive-voice-formality-style

### 10. Практика — practice-set

Source slot: новий appended practice; key: `mistakes-practice`; column: left; level: A2

Поле `mistakes-practice/data/title` — Заголовок:

10. Практика

Поле `mistakes-practice/data/select_title` — Група форми:

Перевір узгодження та V3

Поле `mistakes-practice/data/select_intro` — Інструкція до форми:

Контекст задає час і значення. Обери форму, що підходить саме до цього be-пасиву.

Поле `mistakes-practice/data/selects/0/id` — навчальне значення:

m55-mistakes-form-plural

Поле `mistakes-practice/data/selects/0/label` — Назва пункту:

Йдеться про кілька дверей і подію вчора. Доповни «Двері пофарбували вчора».
The doors ___ painted yesterday.

Поле `mistakes-practice/data/selects/0/options/0` — навчальне значення:

was

Поле `mistakes-practice/data/selects/0/options/1` — навчальне значення:

were

Поле `mistakes-practice/data/selects/0/options/2` — навчальне значення:

are

Поле `mistakes-practice/data/selects/0/answer` — Канонічна відповідь:

were

Поле `mistakes-practice/data/selects/0/accepted/0` — навчальне значення:

were

Поле `mistakes-practice/data/selects/0/feedback/was` — навчальне значення:

The doors were painted yesterday. — Двері пофарбували вчора. Doors — множина, тому were.

Поле `mistakes-practice/data/selects/0/feedback/were` — навчальне значення:

Правильно: The doors were painted yesterday. — Двері пофарбували вчора. Множина й минулий час узгоджені.

Поле `mistakes-practice/data/selects/0/feedback/are` — навчальне значення:

The doors were painted yesterday. — Двері пофарбували вчора. Yesterday задає Past Simple, не Present Simple.

Поле `mistakes-practice/data/selects/1/id` — навчальне значення:

m55-mistakes-form-v3

Поле `mistakes-practice/data/selects/1/label` — Назва пункту:

Хтось узяв ключ учора. Доповни пасив «Ключ узяли вчора».
The key was ___ yesterday.

Поле `mistakes-practice/data/selects/1/options/0` — навчальне значення:

took

Поле `mistakes-practice/data/selects/1/options/1` — навчальне значення:

take

Поле `mistakes-practice/data/selects/1/options/2` — навчальне значення:

taken

Поле `mistakes-practice/data/selects/1/answer` — Канонічна відповідь:

taken

Поле `mistakes-practice/data/selects/1/accepted/0` — навчальне значення:

taken

Поле `mistakes-practice/data/selects/1/feedback/took` — навчальне значення:

The key was taken yesterday. — Ключ узяли вчора. Took — V2; після was у пасиві потрібне V3 taken.

Поле `mistakes-practice/data/selects/1/feedback/take` — навчальне значення:

The key was taken yesterday. — Ключ узяли вчора. Take — початкова форма, а потрібне V3.

Поле `mistakes-practice/data/selects/1/feedback/taken` — навчальне значення:

Правильно: The key was taken yesterday. — Ключ узяли вчора. Was + taken утворює Past Simple passive.

Поле `mistakes-practice/data/choice_title` — Група змісту:

Не втрать ролі й час

Поле `mistakes-practice/data/choice_intro` — Інструкція до змісту:

Українська ситуація задає зміст. Точний переклад стимулу побачиш після перевірки.

Поле `mistakes-practice/data/choices/0/id` — навчальне значення:

m55-mistakes-meaning-agent

Поле `mistakes-practice/data/choices/0/label` — Назва пункту:

Оля й Андрій у майстерні. З повідомлення треба встановити, хто саме полагодив велосипед. Який висновок підтримує речення?

Поле `mistakes-practice/data/choices/0/prompt` — Стимул:

The bike was repaired by Olia.

Поле `mistakes-practice/data/choices/0/options/0` — навчальне значення:

Велосипед полагодила Оля.

Поле `mistakes-practice/data/choices/0/options/1` — навчальне значення:

Оля отримала відремонтований велосипед, але виконавця не названо.

Поле `mistakes-practice/data/choices/0/options/2` — навчальне значення:

Велосипед полагодив Андрій.

Поле `mistakes-practice/data/choices/0/answer` — Канонічна відповідь:

Велосипед полагодила Оля.

Поле `mistakes-practice/data/choices/0/accepted/0` — навчальне значення:

Велосипед полагодила Оля.

Поле `mistakes-practice/data/choices/0/feedback/велосипед полагодила оля.` — навчальне значення:

Правильно: The bike was repaired by Olia. — Велосипед полагодила Оля. By Olia називає виконавицю.

Поле `mistakes-practice/data/choices/0/feedback/оля отримала відремонтований велосипед, але виконавця не названо.` — навчальне значення:

The bike was repaired by Olia. — Велосипед полагодила Оля. Olia тут виконавиця після by; речення не каже, кому належить велосипед.

Поле `mistakes-practice/data/choices/0/feedback/велосипед полагодив андрій.` — навчальне значення:

The bike was repaired by Olia. — Велосипед полагодила Оля. Андрія як виконавця не названо.

Поле `mistakes-practice/data/choices/1/id` — навчальне значення:

m55-mistakes-meaning-time

Поле `mistakes-practice/data/choices/1/label` — Назва пункту:

Треба повідомити, що скриньку не відкривали саме вчора. Який варіант зберігає і час, і заперечення?

Поле `mistakes-practice/data/choices/1/prompt` — Стимул:

A: The box wasn't opened yesterday.
B: The box isn't opened every day.

Поле `mistakes-practice/data/choices/1/options/0` — навчальне значення:

A

Поле `mistakes-practice/data/choices/1/options/1` — навчальне значення:

B

Поле `mistakes-practice/data/choices/1/answer` — Канонічна відповідь:

A

Поле `mistakes-practice/data/choices/1/accepted/0` — навчальне значення:

A

Поле `mistakes-practice/data/choices/1/feedback/a` — навчальне значення:

Правильно: The box wasn't opened yesterday. — Скриньку вчора не відкривали. The box isn't opened every day. — Скриньку відкривають не щодня. Друге речення граматичне, але передає інший час і зміст.

Поле `mistakes-practice/data/choices/1/feedback/b` — навчальне значення:

The box isn't opened every day. — Скриньку відкривають не щодня. Це граматична звичка/регулярність, але задано вчорашню подію: The box wasn't opened yesterday. — Скриньку вчора не відкривали.

Поле `mistakes-practice/data/input_title` — Група побудови речень:

Побудуй питання й заперечення

Поле `mistakes-practice/data/input_intro` — Інструкція до побудови:

Введи відповідь вручну або використай усі токени перемішаного банку. Перевір підмет, be й V3.

Поле `mistakes-practice/data/inputs/0/id` — навчальне значення:

m55-mistakes-build-question

Поле `mistakes-practice/data/inputs/0/before` — Перемішаний банк:

yesterday? / cleaned / Were / windows / the

Поле `mistakes-practice/data/inputs/0/after` — Українська умова:

Запитай нейтрально: «Вікна помили вчора?» Використай Past Simple be-passive з clean, почни з Were. Лексика: windows — вікна; використай the.

Поле `mistakes-practice/data/inputs/0/answer` — Канонічна відповідь:

Were the windows cleaned yesterday?

Поле `mistakes-practice/data/inputs/0/accepted/0` — навчальне значення:

Were the windows cleaned yesterday?

Поле `mistakes-practice/data/inputs/0/feedback/were the windows cleaned yesterday?` — навчальне значення:

Правильно: Were the windows cleaned yesterday? — Вікна помили вчора? Were стоїть перед підметом, cleaned — V3.

Поле `mistakes-practice/data/inputs/0/feedback/were the windows cleaned yesterday` — навчальне значення:

Правильно: Were the windows cleaned yesterday? — Вікна помили вчора?

Поле `mistakes-practice/data/inputs/0/feedback/was the windows cleaned yesterday?` — навчальне значення:

Were the windows cleaned yesterday? — Вікна помили вчора? Для windows потрібне were.

Поле `mistakes-practice/data/inputs/1/id` — навчальне значення:

m55-mistakes-build-negative

Поле `mistakes-practice/data/inputs/1/before` — Перемішаний банк:

on / not / is / Sundays. / The / opened / shop

Поле `mistakes-practice/data/inputs/1/after` — Українська умова:

Скажи: «Магазин не відчиняють по неділях». Використай Present Simple be-passive з open, почни з The shop. Лексика: on Sundays — по неділях. Збережи заперечення.

Поле `mistakes-practice/data/inputs/1/answer` — Канонічна відповідь:

The shop is not opened on Sundays.

Поле `mistakes-practice/data/inputs/1/accepted/0` — навчальне значення:

The shop is not opened on Sundays.

Поле `mistakes-practice/data/inputs/1/accepted/1` — навчальне значення:

The shop isn't opened on Sundays.

Поле `mistakes-practice/data/inputs/1/feedback/the shop is not opened on sundays.` — навчальне значення:

Правильно: The shop is not opened on Sundays. — Магазин не відчиняють по неділях. Not стоїть після is, opened — V3.

Поле `mistakes-practice/data/inputs/1/feedback/the shop isn't opened on sundays.` — навчальне значення:

Правильно: The shop isn't opened on Sundays. — Магазин не відчиняють по неділях. Isn't замінює is not.

Поле `mistakes-practice/data/inputs/1/feedback/the shop is not opened on sundays` — навчальне значення:

Правильно: The shop is not opened on Sundays. — Магазин не відчиняють по неділях.

Поле `mistakes-practice/data/inputs/1/feedback/the shop isn't opened on sundays` — навчальне значення:

Правильно: The shop isn't opened on Sundays. — Магазин не відчиняють по неділях.

Поле `mistakes-practice/data/inputs/1/feedback/the shop is not open on sundays.` — навчальне значення:

The shop is not opened on Sundays. — Магазин не відчиняють по неділях. Варіант з open природно описує стан «не відчинений», але завдання просить дієслівний пасив із V3 opened.

Поле `mistakes-practice/data/linked_practice/source` — навчальне значення:

theory_links

Поле `mistakes-practice/data/linked_practice/question_types/0` — навчальне значення:

4

Поле `mistakes-practice/data/linked_practice/seeder_classes/0` — навчальне значення:

Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceCommonMistakesAllLevelsLessonSeeder

Поле `mistakes-practice/data/linked_practice/title` — Заголовок:

Вправа 4. Побудуй речення

Поле `mistakes-practice/data/linked_practice/intro` — Вступ:

Склади речення в нейтральному порядку, починаючи з підмета. Обставини часу або місця став наприкінці. Перевір підмет, допоміжне та V3; у повідомленні про вже виконану роботу збережи теперішній результат.

Поле `mistakes-practice/data/linked_practice/footer` — Підпис:

Завдання підібрані лише для цієї теми.

### Змістовні point-level «Докладніше»

#### Чому питання без інверсії іноді можливе

Key: `mistakes-echo-question`; owner: `mistakes-context/sections/0`

Поле `mistakes-echo-question/fragment/data/sections/0/label` — Назва пункту:

ПЕРШИЙ ЗАПИТ ЧИ РЕАКЦІЯ

Поле `mistakes-echo-question/fragment/data/sections/0/color` — навчальне значення:

slate

Поле `mistakes-echo-question/fragment/data/sections/0/description` — Пояснення:

Нейтральне питання просить нову інформацію. Перепитування реагує на вже почуте й може повторювати порядок твердження з висхідною інтонацією або здивуванням. Тому оцінка залежить не тільки від знака питання, а й від попередньої репліки. У письмовій початковій вправі без діалогу обираємо нейтральну інверсію.

Поле `mistakes-echo-question/fragment/data/sections/0/examples/0/en` — Англійською:

A: The letter is written. B: The letter is written? I thought we were still planning it.

Поле `mistakes-echo-question/fragment/data/sections/0/examples/0/ua` — Українською:

А: Лист написано. Б: Лист уже написано? Я думав, ми ще його плануємо.

Поле `mistakes-echo-question/fragment/data/sections/0/note` — Примітка:

Це не означає, що будь-який порядок слів підходить до будь-якого wh-питання. Перепитування є окремим комунікативним сценарієм.

## theory-passive-voice-formality-style

Owner: `Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceFormalityStyleTheorySeeder`

URL: [http://gramlyze.loc/theory/passive-voice/theory-passive-voice-formality-style](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-formality-style)

Ancestry: `passive-voice`

Рівень: B2–C1; власна практика: B2

Subtitle: Формальність та стиль пасиву — як обирати Active або Passive за темою повідомлення, новою інформацією, відповідальністю й вимогами жанру, не підміняючи стиль граматичною забороною.

Page text: Active чи Passive за метою повідомлення: відома тема, нова інформація, виконавець, зв’язність, be/get і практика редагування.

### style-hero — hero

Source slot: 0; key: `style-hero`; column: header; level: B2

Поле `style-hero/data/level` — Рівень:

B2–C1

Поле `style-hero/data/intro` — Вступ:

Active і Passive по-різному організовують повідомлення. Обирай форму за тим, про що вже йдеться, яка інформація нова й чи потрібно назвати виконавця. Пасив сам по собі не робить текст формальним, об’єктивним або правдивим.

Поле `style-hero/data/rules/0/label` — Назва пункту:

ТЕМА — ПРЕДМЕТ

Поле `style-hero/data/rules/0/color` — навчальне значення:

emerald

Поле `style-hero/data/rules/0/text` — Правило:

Пасив допомагає продовжити розмову про вже відомий предмет.

Поле `style-hero/data/rules/0/example` — Приклад:

The cabinet was restored last week.
Шафу відреставрували минулого тижня.

Поле `style-hero/data/rules/1/label` — Назва пункту:

ВАЖЛИВИЙ ВИКОНАВЕЦЬ

Поле `style-hero/data/rules/1/color` — навчальне значення:

blue

Поле `style-hero/data/rules/1/text` — Правило:

Актив може прямо назвати відповідального; важливого агента можна зберегти й після by.

Поле `style-hero/data/rules/1/example` — Приклад:

Our team checked the labels.
Наша команда перевірила етикетки.

Поле `style-hero/data/rules/2/label` — Назва пункту:

НЕ ФОРМА, А ФАКТИ

Поле `style-hero/data/rules/2/color` — навчальне значення:

amber

Поле `style-hero/data/rules/2/text` — Правило:

Речення без агента не повідомляє, хто діяв; не додавай вигадане we.

Поле `style-hero/data/rules/2/example` — Приклад:

The labels were checked.
Етикетки перевірили.

### 1. Коли предмет або процес стає темою — usage-panels

Source slot: 1; key: `style-contexts`; column: left; level: B2

Поле `style-contexts/data/title` — Заголовок:

1. Коли предмет або процес стає темою

Поле `style-contexts/data/sections/0/label` — Назва пункту:

ПОСЛІДОВНІСТЬ РОБІТ

Поле `style-contexts/data/sections/0/color` — навчальне значення:

emerald

Поле `style-contexts/data/sections/0/description` — Пояснення:

Коли текст уже про матеріали чи предмети, пасив може тримати їх у центрі опису. Якщо дії конкретної команди важливі, актив із we також доречний; назва жанру сама по собі не визначає стан кожного речення.

Поле `style-contexts/data/sections/0/examples/0/en` — Англійською:

The panels were washed and left to dry.

Поле `style-contexts/data/sections/0/examples/0/ua` — Українською:

Панелі помили й залишили сохнути.

Поле `style-contexts/data/sections/0/examples/1/en` — Англійською:

We washed the panels and left them to dry.

Поле `style-contexts/data/sections/0/examples/1/ua` — Українською:

Ми помили панелі й залишили їх сохнути.

Поле `style-contexts/data/sections/0/note` — Примітка:

Другий варіант прямо називає виконавців як we; перший цього не робить. Не називаємо їх повністю тотожними за всією інформацією.

Поле `style-contexts/data/sections/1/label` — Назва пункту:

ПОВІДОМЛЕННЯ ДЛЯ ВІДВІДУВАЧІВ

Поле `style-contexts/data/sections/1/color` — навчальне значення:

blue

Поле `style-contexts/data/sections/1/description` — Пояснення:

В оголошенні може бути важливим, що доступно або яку дію потрібно виконати. Виконавця називай, якщо без нього читач не зрозуміє потрібного кроку.

Поле `style-contexts/data/sections/1/examples/0/en` — Англійською:

Tickets can be collected at the front desk.

Поле `style-contexts/data/sections/1/examples/0/ua` — Українською:

Квитки можна отримати на стійці реєстрації.

Поле `style-contexts/data/sections/1/examples/1/en` — Англійською:

Please collect your ticket at the front desk.

Поле `style-contexts/data/sections/1/examples/1/ua` — Українською:

Будь ласка, отримайте свій квиток на стійці реєстрації.

Поле `style-contexts/data/sections/1/note` — Примітка:

Це різні способи організувати звернення: інформаційне повідомлення й пряме прохання. Обидва можуть бути ввічливими.

Поле `style-contexts/data/sections/2/label` — Назва пункту:

НОВИНА ПРО ВІДОМИЙ ОБ’ЄКТ

Поле `style-contexts/data/sections/2/color` — навчальне значення:

amber

Поле `style-contexts/data/sections/2/description` — Пояснення:

У короткому повідомленні про вже згаданий об’єкт на перший план можна поставити подію з ним. Якщо ж запитання стосується виконавця, потрібна інформація про нього, а не просто коротша фраза.

Поле `style-contexts/data/sections/2/examples/0/en` — Англійською:

The old noticeboard has been replaced.

Поле `style-contexts/data/sections/2/examples/0/ua` — Українською:

Стару дошку оголошень замінили.

Поле `style-contexts/data/sections/2/examples/1/en` — Англійською:

The caretaker has replaced the old noticeboard.

Поле `style-contexts/data/sections/2/examples/1/ua` — Українською:

Доглядач замінив стару дошку оголошень.

Поле `style-contexts/data/sections/2/note` — Примітка:

У другому повідомленні є додатковий прямо названий виконавець. Перший варіант не означає, що його особа невідома.

Поле `style-contexts/data/sections/3/label` — Назва пункту:

ТЕХНІЧНИЙ ОПИС БЕЗ ЗАЙВОГО ДУБЛЮВАННЯ

Поле `style-contexts/data/sections/3/color` — навчальне значення:

slate

Поле `style-contexts/data/sections/3/description` — Пояснення:

Коли об’єкт лишається темою кількох дій, пасив допомагає не починати кожне речення новим виконавцем. Однак ясна інструкція може використовувати й прямий наказ або актив.

Поле `style-contexts/data/sections/3/examples/0/en` — Англійською:

The cover is removed before the filter is cleaned.

Поле `style-contexts/data/sections/3/examples/0/ua` — Українською:

Кришку знімають перед тим, як очищають фільтр.

Поле `style-contexts/data/sections/3/examples/1/en` — Англійською:

Remove the cover before you clean the filter.

Поле `style-contexts/data/sections/3/examples/1/ua` — Українською:

Зніміть кришку перед тим, як очищати фільтр.

Поле `style-contexts/data/sections/3/note` — Примітка:

Це вигаданий навчальний опис, не інструкція з безпеки для конкретного пристрою. Вибір залежить від завдання тексту.

### 2. Порівняй фокус і явно названі факти — comparison-table

Source slot: 2; key: `style-information`; column: left; level: B2

Поле `style-information/data/title` — Заголовок:

2. Порівняй фокус і явно названі факти

Поле `style-information/data/intro` — Вступ:

Замість правила «active — неформально, passive — формально» перевір, на яке запитання відповідає кожна версія.

Поле `style-information/data/headers/0` — навчальне значення:

Ситуація

Поле `style-information/data/headers/1` — навчальне значення:

Версія і переклад

Поле `style-information/data/headers/2` — навчальне значення:

Що саме повідомлено

Поле `style-information/data/rows/0/cells/0` — навчальне значення:

Уже говоримо про звіт; важливо, чи подано його вчасно.

Поле `style-information/data/rows/0/cells/1` — навчальне значення:

The report was submitted on time.
Звіт подали вчасно.

Поле `style-information/data/rows/0/cells/2` — навчальне значення:

У центрі звіт і час подання; виконавця прямо не названо.

Поле `style-information/data/rows/1/cells/0` — навчальне значення:

Я відповідаю, чи виконав свою роботу.

Поле `style-information/data/rows/1/cells/1` — навчальне значення:

I submitted the report on time.
Я подав звіт вчасно.

Поле `style-information/data/rows/1/cells/2` — навчальне значення:

Автор прямо називає себе виконавцем. Це може бути цілком доречно в робочому листі.

Поле `style-information/data/rows/2/cells/0` — навчальне значення:

Повідомляємо про наявність помилок, але не називаємо виконавців.

Поле `style-information/data/rows/2/cells/1` — навчальне значення:

Mistakes were made.
Було допущено помилки.

Поле `style-information/data/rows/2/cells/2` — навчальне значення:

З речення не випливає саме we. Відсутність агента сама по собі ще не доводить наміру приховати відповідальність.

Поле `style-information/data/rows/3/cells/0` — навчальне значення:

Команда знає, що саме вона припустилася помилок, і це визнає.

Поле `style-information/data/rows/3/cells/1` — навчальне значення:

We made mistakes.
Ми припустилися помилок.

Поле `style-information/data/rows/3/cells/2` — навчальне значення:

We додає прямо названих відповідальних. Таке редагування дозволене лише за наявності цього факту.

Поле `style-information/data/warning` — Застереження:

Не відновлюй неназваного агента за припущенням. Перехід до active може вимагати інформації, якої в початковому реченні немає.

### 3. Як редагувати, не вигадуючи помилок — usage-panels

Source slot: 3; key: `style-editing`; column: left; level: B2

Поле `style-editing/data/title` — Заголовок:

3. Як редагувати, не вигадуючи помилок

Поле `style-editing/data/sections/0/label` — Назва пункту:

ВІДПОВІДЬ ПРО ВИКОНАВЦЯ

Поле `style-editing/data/sections/0/color` — навчальне значення:

emerald

Поле `style-editing/data/sections/0/description` — Пояснення:

Якщо співрозмовник питає, хто вдарив м’яч, назви John прямо. Якщо розмова про м’яч і його рух, пасив теж може бути природним. Обидва речення граматичні; контекст визначає фокус.

Поле `style-editing/data/sections/0/examples/0/en` — Англійською:

John kicked the ball.

Поле `style-editing/data/sections/0/examples/0/ua` — Українською:

Джон ударив м’яч.

Поле `style-editing/data/sections/0/examples/1/en` — Англійською:

The ball was kicked by John.

Поле `style-editing/data/sections/0/examples/1/ua` — Українською:

М’яч ударив Джон.

Поле `style-editing/data/sections/0/note` — Примітка:

By John не робить речення автоматично неприродним. Важлива роль інформації в розмові, а не сама кількість слів.

Поле `style-editing/data/sections/1/label` — Назва пункту:

ВАЖЛИВИЙ АВТОР НЕ ЗАБОРОНЯЄ ПАСИВ

Поле `style-editing/data/sections/1/color` — навчальне значення:

blue

Поле `style-editing/data/sections/1/description` — Пояснення:

Уяви розмову про книжку: назва вже відома, а нова інформація — хто її написав. Пасив із by-phrase може залишити книжку темою й назвати автора наприкінці.

Поле `style-editing/data/sections/1/examples/0/en` — Англійською:

The book was written by Shakespeare.

Поле `style-editing/data/sections/1/examples/0/ua` — Українською:

Книжку написав Шекспір.

Поле `style-editing/data/sections/1/examples/1/en` — Англійською:

Shakespeare wrote the book.

Поле `style-editing/data/sections/1/examples/1/ua` — Українською:

Шекспір написав книжку.

Поле `style-editing/data/sections/1/note` — Примітка:

Обидві версії граматичні. Коли книжка вже є темою, by-phrase може додавати автора як нову інформацію; відомість його імені сама по собі не забороняє пасиву.

Поле `style-editing/data/sections/2/label` — Назва пункту:

ПОВТОР ДОПОМІЖНОГО — НЕ GRAMMAR ERROR

Поле `style-editing/data/sections/2/color` — навчальне значення:

amber

Поле `style-editing/data/sections/2/description` — Пояснення:

Повторене was у ряді дій граматично можливе. Якщо підмет і час спільні, повтор можна прибрати, зберігши пасив. Перехід до we дозволений тільки тоді, коли відомо, що всі ці дії виконали саме ми.

Поле `style-editing/data/sections/2/examples/0/en` — Англійською:

The report was written, was reviewed, and was submitted.

Поле `style-editing/data/sections/2/examples/0/ua` — Українською:

Звіт написали, перевірили й подали.

Поле `style-editing/data/sections/2/examples/1/en` — Англійською:

The report was written, reviewed, and submitted.

Поле `style-editing/data/sections/2/examples/1/ua` — Українською:

Звіт написали, перевірили й подали.

Поле `style-editing/data/sections/2/note` — Примітка:

Скорочено повтор, а не змінено стан або виконавців. Не виправляємо ритм тексту ціною нового неперевіреного факту.

### 4. Be і get: типовий регістр, а не загальна заборона — forms-grid

Source slot: 4; key: `style-be-get`; column: left; level: B2

Поле `style-be-get/data/title` — Заголовок:

4. Be і get: типовий регістр, а не загальна заборона

Поле `style-be-get/data/intro` — Вступ:

Be-passive нейтральний і придатний для багатьох жанрів. Get-passive частіше трапляється в розмовному повідомленні й часто підкреслює подію або зміну. Це тенденції вживання, не універсальний дозвіл чи заборона для кожного тексту.

Поле `style-be-get/data/items/0/label` — Назва пункту:

НЕЙТРАЛЬНИЙ ОПИС

Поле `style-be-get/data/items/0/title` — Заголовок:

be + V3

Поле `style-be-get/data/items/0/subtitle` — Пояснення / приклад:

The document was signed.
Документ підписали.

Поле `style-be-get/data/items/1/label` — Назва пункту:

РОЗМОВНЕ ПОВІДОМЛЕННЯ ПРО ПОДІЮ

Поле `style-be-get/data/items/1/title` — Заголовок:

get + V3

Поле `style-be-get/data/items/1/subtitle` — Пояснення / приклад:

The document got signed.
Документ підписали.

Поле `style-be-get/data/items/2/label` — Назва пункту:

РЕДАКЦІЙНА ВИМОГА

Поле `style-be-get/data/items/2/title` — Заголовок:

Перевір конкретний жанр і настанови

Поле `style-be-get/data/items/2/subtitle` — Пояснення / приклад:

У нейтральному описі процедури be часто є зручним вибором. Це не граматичне правило «в кожному науковому тексті можливе тільки be».

Поле `style-be-get/data/items/3/label` — Назва пункту:

ПОЗИТИВНА ПОДІЯ

Поле `style-be-get/data/items/3/title` — Заголовок:

Get не обмежений неприємностями

Поле `style-be-get/data/items/3/subtitle` — Пояснення / приклад:

He got promoted.
Його підвищили.

### 5. Дистанція, відповідальність і доказовість — usage-panels

Source slot: 5; key: `style-effects`; column: left; level: C1

Поле `style-effects/data/title` — Заголовок:

5. Дистанція, відповідальність і доказовість

Поле `style-effects/data/sections/0/label` — Назва пункту:

НЕОСОБОВЕ ПОВІДОМЛЕННЯ НЕ Є ДОКАЗОМ

Поле `style-effects/data/sections/0/color` — навчальне значення:

emerald

Поле `style-effects/data/sections/0/description` — Пояснення:

Reporting-passive може відділяти повідомлення від особистого твердження автора. Але слова reported, believed або independently мають власний зміст; сама пасивна форма не підтверджує істинності чи незалежності.

Поле `style-effects/data/sections/0/examples/0/en` — Англійською:

The schedule is reported to have changed.

Поле `style-effects/data/sections/0/examples/0/ua` — Українською:

Повідомляють, що розклад змінився.

Поле `style-effects/data/sections/0/examples/1/en` — Англійською:

The figures were checked independently.

Поле `style-effects/data/sections/0/examples/1/ua` — Українською:

Показники перевірили незалежно.

Поле `style-effects/data/sections/0/note` — Примітка:

Друге речення прямо заявляє незалежність через independently; пасив без цього слова такої інформації не додає. Підтвердження фактів — окрема робота, не властивість граматики.

Поле `style-effects/data/sections/1/label` — Назва пункту:

НЕВКАЗАНИЙ АГЕНТ І МОТИВ АВТОРА

Поле `style-effects/data/sections/1/color` — навчальне значення:

blue

Поле `style-effects/data/sections/1/description` — Пояснення:

Речення може не називати виконавця, бо він очевидний, неважливий для теми, невідомий або свідомо не названий. Без контексту не обираємо один із цих мотивів як доведений факт.

Поле `style-effects/data/sections/1/examples/0/en` — Англійською:

The meeting has been cancelled.

Поле `style-effects/data/sections/1/examples/0/ua` — Українською:

Зустріч скасували.

Поле `style-effects/data/sections/1/examples/1/en` — Англійською:

The committee has cancelled the meeting.

Поле `style-effects/data/sections/1/examples/1/ua` — Українською:

Комітет скасував зустріч.

Поле `style-effects/data/sections/1/note` — Примітка:

Комітет можна додати лише тоді, коли це справді відомий виконавець. Перше речення саме по собі його не встановлює.

Поле `style-effects/data/sections/2/label` — Назва пункту:

ЗВ’ЯЗНІСТЬ ВІДОМОГО Й НОВОГО

Поле `style-effects/data/sections/2/color` — навчальне значення:

amber

Поле `style-effects/data/sections/2/description` — Пояснення:

Пасив може починатися зі знайомого читачеві предмета, а нову інформацію подавати далі. Це допомагає зв’язності, але не вимагає робити кожне речення пасивним.

Поле `style-effects/data/sections/2/examples/0/en` — Англійською:

We found an old map. It had been drawn by a local teacher.

Поле `style-effects/data/sections/2/examples/0/ua` — Українською:

Ми знайшли стару мапу. Її намалював місцевий учитель.

Поле `style-effects/data/sections/2/note` — Примітка:

It продовжує тему map; by-phrase додає нового автора. Минуле до знахідки передає had been drawn, не сам вибір пасиву.

### 6. Академічний і робочий текст: перевір завдання та вимоги — comparison-table

Source slot: 6; key: `style-academic`; column: left; level: C1

Поле `style-academic/data/title` — Заголовок:

6. Академічний і робочий текст: перевір завдання та вимоги

Поле `style-academic/data/intro` — Вступ:

Немає єдиного правила всіх журналів. Наприклад, APA Style дозволяє обидва стани: актив корисний для ясного опису дій людей, пасив — коли важливіший об’єкт дії. Це конкретна редакційна система, не закон усіх жанрів.

Поле `style-academic/data/headers/0` — навчальне значення:

Мета фрагмента

Поле `style-academic/data/headers/1` — навчальне значення:

Варіант і переклад

Поле `style-academic/data/headers/2` — навчальне значення:

Умова доречності

Поле `style-academic/data/rows/0/cells/0` — навчальне значення:

Описати послідовність із матеріалом.

Поле `style-academic/data/rows/0/cells/1` — навчальне значення:

The samples were labelled before storage.
Зразки позначили перед зберіганням.

Поле `style-academic/data/rows/0/cells/2` — навчальне значення:

У центрі зразки та процедура; виконавців прямо не названо.

Поле `style-academic/data/rows/1/cells/0` — навчальне значення:

Прямо повідомити, що зробила наша команда.

Поле `style-academic/data/rows/1/cells/1` — навчальне значення:

We labelled the samples before storage.
Ми позначили зразки перед зберіганням.

Поле `style-academic/data/rows/1/cells/2` — навчальне значення:

We має позначати реальних виконавців; актив не є автоматично «неакадемічним».

Поле `style-academic/data/rows/2/cells/0` — навчальне значення:

Продовжити опис матеріалу, потім назвати власну дію.

Поле `style-academic/data/rows/2/cells/1` — навчальне значення:

The samples were collected on Monday. We labelled them on Tuesday.
Зразки зібрали в понеділок. Ми позначили їх у вівторок.

Поле `style-academic/data/rows/2/cells/2` — навчальне значення:

Змішування станів граматично нормальне. Перша частина не стверджує, що збирання виконали саме ми.

Поле `style-academic/data/warning` — Застереження:

Перевіряй вимоги конкретного видання, установи або типу документа. Жоден вибір Active/Passive сам по собі не засвідчує наукової якості повідомлення.

### 7. Граматичні альтернативи: поясни свій вибір — mistakes-grid

Source slot: 7; key: `style-alternatives`; column: left; level: B2; exact semantic mapping: usage-panels

Поле `style-alternatives/data/title` — Заголовок:

7. Граматичні альтернативи: поясни свій вибір

Поле `style-alternatives/data/sections/0/label` — Назва пункту:

НЕЙТРАЛЬНИЙ ПРОТОКОЛ ЧИ РОЗМОВНА ПОДІЯ

Поле `style-alternatives/data/sections/0/color` — навчальне значення:

emerald

Поле `style-alternatives/data/sections/0/description` — Пояснення:

Для нейтрального протоколу виконання обираємо be-passive. Get-passive в іншому контексті може підкреслити, що дія зрештою відбулася; різницю регістру не називаємо загальною граматичною забороною.

Поле `style-alternatives/data/sections/0/examples/0/en` — Англійською:

The interview was recorded.

Поле `style-alternatives/data/sections/0/examples/0/ua` — Українською:

Інтерв’ю записали.

Поле `style-alternatives/data/sections/0/examples/1/en` — Англійською:

The interview got recorded after all.

Поле `style-alternatives/data/sections/0/examples/1/ua` — Українською:

Інтерв’ю все-таки записали.

Поле `style-alternatives/data/sections/0/note` — Примітка:

After all додає окремий зміст «попри попередні сумніви». Це не повністю тотожні повідомлення.

Поле `style-alternatives/data/sections/1/label` — Назва пункту:

ПРО М’ЯЧ ЧИ ПРО JOHN

Поле `style-alternatives/data/sections/1/color` — навчальне значення:

blue

Поле `style-alternatives/data/sections/1/description` — Пояснення:

Вибір залежить від теми розмови: рух м’яча можна описати пасивом, дію John — активом. Зберігаємо й Mary як адресатку передачі м’яча.

Поле `style-alternatives/data/sections/1/examples/0/en` — Англійською:

The ball was kicked by John to Mary.

Поле `style-alternatives/data/sections/1/examples/0/ua` — Українською:

М’яч до Мері ударом відправив Джон.

Поле `style-alternatives/data/sections/1/examples/1/en` — Англійською:

John kicked the ball to Mary.

Поле `style-alternatives/data/sections/1/examples/1/ua` — Українською:

Джон ударом відправив м’яч до Мері.

Поле `style-alternatives/data/sections/1/note` — Примітка:

Немає підстав перекреслювати перший варіант лише за те, що він пасивний. У короткій розповіді про John актив може бути простішим продовженням.

Поле `style-alternatives/data/sections/2/label` — Назва пункту:

ВАЖЛИВИЙ УЧИТЕЛЬ АБО МІЙ ДОСВІД

Поле `style-alternatives/data/sections/2/color` — навчальне значення:

amber

Поле `style-alternatives/data/sections/2/description` — Пояснення:

Коли говорю про свій досвід на уроці, I може залишатися темою. Якщо потрібно почати з дії вчителя, починаємо з The teacher. В обох версіях усі учасники названі.

Поле `style-alternatives/data/sections/2/examples/0/en` — Англійською:

I was asked a question by the teacher.

Поле `style-alternatives/data/sections/2/examples/0/ua` — Українською:

Учитель поставив мені запитання.

Поле `style-alternatives/data/sections/2/examples/1/en` — Англійською:

The teacher asked me a question.

Поле `style-alternatives/data/sections/2/examples/1/ua` — Українською:

Учитель поставив мені запитання.

Поле `style-alternatives/data/sections/2/note` — Примітка:

Очевидність учителя не робить by the teacher неграматичним; вирішуємо, чи потрібна ця інформація читачеві.

Поле `style-alternatives/data/sections/3/label` — Назва пункту:

СТАЛИЙ ВИСЛІВ, А НЕ ПОМИЛКА

Поле `style-alternatives/data/sections/3/color` — навчальне значення:

slate

Поле `style-alternatives/data/sections/3/description` — Пояснення:

A good time was had by all — засвідчений сталий вислів про те, що всі добре провели час. Він може звучати формульно або жартівливо; прямий active часто простіший, але пасив не стає через це неграматичним.

Поле `style-alternatives/data/sections/3/examples/0/en` — Англійською:

A good time was had by all at the party.

Поле `style-alternatives/data/sections/3/examples/0/ua` — Українською:

На вечірці всі добре провели час.

Поле `style-alternatives/data/sections/3/examples/1/en` — Англійською:

Everyone had a good time at the party.

Поле `style-alternatives/data/sections/3/examples/1/ua` — Українською:

На вечірці всі добре провели час.

Поле `style-alternatives/data/sections/3/note` — Примітка:

Це не модель для механічного пасиву з кожним значенням have. Сталі сполучення перевіряємо окремо.

### 8. Короткий підсумок — summary-list

Source slot: 8; key: `style-summary`; column: left; level: B2

Поле `style-summary/data/title` — Заголовок:

8. Короткий підсумок

Поле `style-summary/data/items/0` — навчальне значення:

Обирай Active/Passive за темою й метою повідомлення, а не за шкалою «розмовне/формальне».

Поле `style-summary/data/items/1` — навчальне значення:

Пасив може зберігати відомий предмет темою й переносити нового агента в кінець речення.

Поле `style-summary/data/items/2` — навчальне значення:

Важливого виконавця можна назвати в активі або через by. Якщо його не названо, не вигадуй we.

Поле `style-summary/data/items/3` — навчальне значення:

Невказаний агент не обов’язково невідомий або навмисно прихований; мотив установлює контекст.

Поле `style-summary/data/items/4` — навчальне значення:

Be/get мають типові регістрові відмінності, але не створюють універсальних жанрових заборон.

Поле `style-summary/data/items/5` — навчальне значення:

Повтор допоміжного можна редагувати без зміни стану; нові виконавці не повинні з’являтися заради коротшого тексту.

Поле `style-summary/data/items/6` — навчальне значення:

Академічні вимоги залежать від конкретного видання. Пасив не доводить об’єктивності, незалежності чи правдивості.

### Пов’язані уроки пасивного стану — navigation-chips

Source slot: 9; key: `style-navigation`; column: footer; level: не заданий

Поле `style-navigation/data/title` — Заголовок:

Пов’язані уроки пасивного стану

Поле `style-navigation/data/items/0/label` — Назва пункту:

Утворення пасиву

Поле `style-navigation/data/items/0/url` — Посилання:

/theory/passive-voice/theory-passive-voice-formation-rules

Поле `style-navigation/data/items/1/label` — Назва пункту:

Пасив із модальними

Поле `style-navigation/data/items/1/url` — Посилання:

/theory/passive-voice/theory-passive-voice-modal-verbs

Поле `style-navigation/data/items/2/label` — Назва пункту:

Каузатив

Поле `style-navigation/data/items/2/url` — Посилання:

/theory/passive-voice/theory-passive-voice-causative

Поле `style-navigation/data/items/3/label` — Назва пункту:

Get-passive

Поле `style-navigation/data/items/3/url` — Посилання:

/theory/passive-voice/theory-passive-voice-get-passive

Поле `style-navigation/data/items/4/label` — Назва пункту:

Безособовий пасив

Поле `style-navigation/data/items/4/url` — Посилання:

/theory/passive-voice/theory-passive-voice-impersonal-passive

Поле `style-navigation/data/items/5/label` — Назва пункту:

Питання й заперечення

Поле `style-navigation/data/items/5/url` — Посилання:

/theory/passive-voice/theory-passive-voice-negatives-questions

Поле `style-navigation/data/items/6/label` — Назва пункту:

Фразові дієслова

Поле `style-navigation/data/items/6/url` — Посилання:

/theory/passive-voice/theory-passive-voice-phrasal-verbs

Поле `style-navigation/data/items/7/label` — Назва пункту:

Обмеження пасиву

Поле `style-navigation/data/items/7/url` — Посилання:

/theory/passive-voice/theory-passive-voice-restrictions

Поле `style-navigation/data/items/8/label` — Назва пункту:

Дієслова з двома додатками

Поле `style-navigation/data/items/8/url` — Посилання:

/theory/passive-voice/theory-passive-voice-two-object-verbs

Поле `style-navigation/data/items/9/label` — Назва пункту:

Типові помилки

Поле `style-navigation/data/items/9/url` — Посилання:

/theory/passive-voice/theory-passive-voice-common-mistakes

Поле `style-navigation/data/items/10/label` — Назва пункту:

Формальність і стиль

Поле `style-navigation/data/items/10/url` — Посилання:

/theory/passive-voice/theory-passive-voice-formality-style

### 9. Практика — practice-set

Source slot: новий appended practice; key: `style-practice`; column: left; level: B2

Поле `style-practice/data/title` — Заголовок:

9. Практика

Поле `style-practice/data/select_title` — Група форми:

Збережи часову форму під час редагування

Поле `style-practice/data/select_intro` — Інструкція до форми:

Мета повідомлення визначена; тепер перевір потрібний допоміжний ланцюжок, не змінюючи час або аспект.

Поле `style-practice/data/selects/0/id` — навчальне значення:

m55-style-form-perfect

Поле `style-practice/data/selects/0/label` — Назва пункту:

Команда вже перевірила макет, і результат важливий зараз. Потрібен Present Perfect passive: «Макет перевірила наша команда».
The layout ___ checked by our team.

Поле `style-practice/data/selects/0/options/0` — навчальне значення:

has been

Поле `style-practice/data/selects/0/options/1` — навчальне значення:

has being

Поле `style-practice/data/selects/0/options/2` — навчальне значення:

was been

Поле `style-practice/data/selects/0/answer` — Канонічна відповідь:

has been

Поле `style-practice/data/selects/0/accepted/0` — навчальне значення:

has been

Поле `style-practice/data/selects/0/feedback/has been` — навчальне значення:

Правильно: The layout has been checked by our team. — Макет перевірила наша команда. Has been + checked зберігає Present Perfect і названих виконавців.

Поле `style-practice/data/selects/0/feedback/has being` — навчальне значення:

The layout has been checked by our team. — Макет перевірила наша команда. У perfect потрібне been, не being.

Поле `style-practice/data/selects/0/feedback/was been` — навчальне значення:

The layout has been checked by our team. — Макет перевірила наша команда. Was been не утворює потрібного Present Perfect passive.

Поле `style-practice/data/selects/1/id` — навчальне значення:

m55-style-form-process-question

Поле `style-practice/data/selects/1/label` — Назва пункту:

Питаємо про процес, що тривав учора о десятій. Збережи Past Continuous passive: «О десятій плакати саме друкували?»
Were the posters ___ at ten?

Поле `style-practice/data/selects/1/options/0` — навчальне значення:

been printed

Поле `style-practice/data/selects/1/options/1` — навчальне значення:

being print

Поле `style-practice/data/selects/1/options/2` — навчальне значення:

being printed

Поле `style-practice/data/selects/1/answer` — Канонічна відповідь:

being printed

Поле `style-practice/data/selects/1/accepted/0` — навчальне значення:

being printed

Поле `style-practice/data/selects/1/feedback/been printed` — навчальне значення:

Were the posters being printed at ten? — О десятій плакати саме друкували? Continuous потребує being.

Поле `style-practice/data/selects/1/feedback/being print` — навчальне значення:

Were the posters being printed at ten? — О десятій плакати саме друкували? Після being потрібне V3 printed.

Поле `style-practice/data/selects/1/feedback/being printed` — навчальне значення:

Правильно: Were the posters being printed at ten? — О десятій плакати саме друкували? Перше допоміжне were уже стоїть перед підметом.

Поле `style-practice/data/choice_title` — Група змісту:

Обери повідомлення за явною метою

Поле `style-practice/data/choice_intro` — Інструкція до змісту:

Не вгадуй абстрактно «кращий стиль». Перевір заданий початок, виконавця й факти; інший граматичний варіант може просто не виконувати умову.

Поле `style-practice/data/choices/0/id` — навчальне значення:

m55-style-meaning-responsibility

Поле `style-practice/data/choices/0/label` — Назва пункту:

Відомо, що помилку допустила саме наша команда. Ми маємо прямо визнати це, почавши з We. Який варіант виконує обидві вимоги?

Поле `style-practice/data/choices/0/prompt` — Стимул:

A: We entered the wrong date.
B: The wrong date was entered.

Поле `style-practice/data/choices/0/options/0` — навчальне значення:

A

Поле `style-practice/data/choices/0/options/1` — навчальне значення:

B

Поле `style-practice/data/choices/0/answer` — Канонічна відповідь:

A

Поле `style-practice/data/choices/0/accepted/0` — навчальне значення:

A

Поле `style-practice/data/choices/0/feedback/a` — навчальне значення:

Правильно: We entered the wrong date. — Ми внесли неправильну дату. The wrong date was entered. — Було внесено неправильну дату. Обидва речення граматичні, але лише A прямо називає we й починається з нього.

Поле `style-practice/data/choices/0/feedback/b` — навчальне значення:

The wrong date was entered. — Було внесено неправильну дату. Цей граматичний пасив не називає виконавців, а умова вимагає явного визнання: We entered the wrong date. — Ми внесли неправильну дату.

Поле `style-practice/data/choices/1/id` — навчальне значення:

m55-style-meaning-topic

Поле `style-practice/data/choices/1/label` — Назва пункту:

Попередній абзац уже про лавку. Відомо, що її відремонтували волонтери в суботу. Наступне речення має починатися з The bench, зберегти день і виконавців. Обери його.

Поле `style-practice/data/choices/1/prompt` — Стимул:

A: The bench was repaired on Saturday.
B: The bench was repaired by volunteers on Saturday.
C: Volunteers repaired the bench on Saturday.

Поле `style-practice/data/choices/1/options/0` — навчальне значення:

A

Поле `style-practice/data/choices/1/options/1` — навчальне значення:

B

Поле `style-practice/data/choices/1/options/2` — навчальне значення:

C

Поле `style-practice/data/choices/1/answer` — Канонічна відповідь:

B

Поле `style-practice/data/choices/1/accepted/0` — навчальне значення:

B

Поле `style-practice/data/choices/1/feedback/a` — навчальне значення:

The bench was repaired on Saturday. — Лавку відремонтували в суботу. Речення граматичне, але не називає волонтерів, яких умова просить зберегти.

Поле `style-practice/data/choices/1/feedback/b` — навчальне значення:

Правильно: The bench was repaired by volunteers on Saturday. — Лавку відремонтували волонтери в суботу. Збережено тему, виконавців і день.

Поле `style-practice/data/choices/1/feedback/c` — навчальне значення:

Volunteers repaired the bench on Saturday. — Волонтери відремонтували лавку в суботу. Факти збережено, але завдання прямо просить почати з The bench; це невиконана умова фокуса, не граматична помилка.

Поле `style-practice/data/input_title` — Група побудови речень:

Відредагуй із заданим фокусом

Поле `style-practice/data/input_intro` — Інструкція до побудови:

Використай факти умови й заданий початок. Можна вводити вручну або складати з перемішаних токенів.

Поле `style-practice/data/inputs/0/id` — навчальне значення:

m55-style-build-topic

Поле `style-practice/data/inputs/0/before` — Перемішаний банк:

team. / revised / The / by / was / draft / our

Поле `style-practice/data/inputs/0/after` — Українська умова:

Відомо, що наша команда відредагувала чернетку. Продовж розмову саме про чернетку: «Чернетку відредагувала наша команда». Почни з The draft і використай Past Simple passive. Лексика: revise — редагувати; our team — наша команда. Збережи виконавців.

Поле `style-practice/data/inputs/0/answer` — Канонічна відповідь:

The draft was revised by our team.

Поле `style-practice/data/inputs/0/accepted/0` — навчальне значення:

The draft was revised by our team.

Поле `style-practice/data/inputs/0/feedback/the draft was revised by our team.` — навчальне значення:

Правильно: The draft was revised by our team. — Чернетку відредагувала наша команда. Чернетка лишається темою, виконавці названі після by.

Поле `style-practice/data/inputs/0/feedback/the draft was revised by our team` — навчальне значення:

Правильно: The draft was revised by our team. — Чернетку відредагувала наша команда.

Поле `style-practice/data/inputs/0/feedback/our team revised the draft.` — навчальне значення:

Our team revised the draft. — Наша команда відредагувала чернетку. Граматика й факти правильні, але тут потрібен заданий початок The draft і пасивний фокус.

Поле `style-practice/data/inputs/1/id` — навчальне значення:

m55-style-build-responsibility

Поле `style-practice/data/inputs/1/before` — Перемішаний банк:

lunch. / We / the / checked / before / labels

Поле `style-practice/data/inputs/1/after` — Українська умова:

Ти пишеш від імені команди й знаєш, що перевірку виконали саме ви. Прямо повідом: «Ми перевірили етикетки до обіду». Почни з We, використай Past Simple active. Лексика: check — перевіряти; labels — етикетки; before lunch — до обіду; використай the.

Поле `style-practice/data/inputs/1/answer` — Канонічна відповідь:

We checked the labels before lunch.

Поле `style-practice/data/inputs/1/accepted/0` — навчальне значення:

We checked the labels before lunch.

Поле `style-practice/data/inputs/1/feedback/we checked the labels before lunch.` — навчальне значення:

Правильно: We checked the labels before lunch. — Ми перевірили етикетки до обіду. Актив прямо називає відомих виконавців і зберігає час.

Поле `style-practice/data/inputs/1/feedback/we checked the labels before lunch` — навчальне значення:

Правильно: We checked the labels before lunch. — Ми перевірили етикетки до обіду.

Поле `style-practice/data/inputs/1/feedback/the labels were checked before lunch.` — навчальне значення:

The labels were checked before lunch. — Етикетки перевірили до обіду. Це граматичний пасив, але він не виконує вимогу прямо почати з We та назвати команду виконавцями.

Поле `style-practice/data/linked_practice/source` — навчальне значення:

theory_links

Поле `style-practice/data/linked_practice/question_types/0` — навчальне значення:

4

Поле `style-practice/data/linked_practice/seeder_classes/0` — навчальне значення:

Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceFormalityStyleAllLevelsLessonSeeder

Поле `style-practice/data/linked_practice/title` — Заголовок:

Вправа 4. Побудуй речення

Поле `style-practice/data/linked_practice/intro` — Вступ:

Виконай явно заданий фокус української умови: починай з названої теми або автора. Збережи час, усіх указаних учасників та окремо заявлені об’єктивність чи незалежність; пасив сам цих властивостей не додає. Обставини часу або місця став наприкінці.

Поле `style-practice/data/linked_practice/footer` — Підпис:

Завдання підібрані лише для цієї теми.

### Змістовні point-level «Докладніше»

#### Чому важливий агент може стояти наприкінці

Key: `style-new-agent-focus`; owner: `style-editing/sections/1`

Поле `style-new-agent-focus/fragment/data/sections/0/label` — Назва пункту:

ВІД ЗНАЙОМОЇ ТЕМИ ДО НОВОЇ ВІДОМОСТІ

Поле `style-new-agent-focus/fragment/data/sections/0/color` — навчальне значення:

slate

Поле `style-new-agent-focus/fragment/data/sections/0/description` — Пояснення:

У розмові про вже відомий предмет читач не мусить щоразу починати з іншого виконавця. Пасив дозволяє зберегти предмет на початку, а ім’я нового автора чи майстра подати далі, де на нього природно падає увага. Тому порада «коли агент важливий, пасив заборонений» хибна: важливість може бути причиною зберегти by-phrase, а не відмовитися від пасиву.

Поле `style-new-agent-focus/fragment/data/sections/0/examples/0/en` — Англійською:

This is the repaired clock. It was restored by my aunt.

Поле `style-new-agent-focus/fragment/data/sections/0/examples/0/ua` — Українською:

Ось відремонтований годинник. Його відреставрувала моя тітка.

Поле `style-new-agent-focus/fragment/data/sections/0/note` — Примітка:

Перший і другий рядки пов’язані темою clock/it. Якщо ж потрібно розповісти, що робить тітка, початок з неї може бути доречнішим. Це зміна організації інформації, не оцінка граматичності.

#### Структура речення й висновок про мотиви — різні речі

Key: `style-responsibility-inference`; owner: `style-effects/sections/1`

Поле `style-responsibility-inference/fragment/data/sections/0/label` — Назва пункту:

НЕ ДОДАВАЙ НЕВІДОМЕ

Поле `style-responsibility-inference/fragment/data/sections/0/color` — навчальне значення:

slate

Поле `style-responsibility-inference/fragment/data/sections/0/description` — Пояснення:

Факт відсутності агента перевіряється за самим реченням. Причина цієї відсутності потребує контексту: учасники можуть уже знати виконавця, або повідомлення може стосуватися лише нового розкладу. Звинувачення автора в уникненні відповідальності — сильніший висновок, ніж дозволяє одна пасивна форма. Коли відповідальність треба назвати, спершу встанови виконавців із надійних відомостей, а потім обери active чи by-phrase.

Поле `style-responsibility-inference/fragment/data/sections/0/examples/0/en` — Англійською:

The time has been changed.

Поле `style-responsibility-inference/fragment/data/sections/0/examples/0/ua` — Українською:

Час змінили.

Поле `style-responsibility-inference/fragment/data/sections/0/examples/1/en` — Англійською:

The organiser has changed the time.

Поле `style-responsibility-inference/fragment/data/sections/0/examples/1/ua` — Українською:

Організатор змінив час.

Поле `style-responsibility-inference/fragment/data/sections/0/note` — Примітка:

Друга версія додає прямо названого організатора. Вона не є механічним розгортанням першої без перевірки, хто насправді діяв.

## theory-passive-voice-passive-infinitive

Owner: `Database\Seeders\Page_V3\PassiveVoice\InfinitivesGerund\PassiveVoicePassiveInfinitiveTheorySeeder`

URL: [http://gramlyze.loc/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-infinitive](http://gramlyze.loc/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-infinitive)

Ancestry: `passive-voice → passive-voice-infinitives-gerund`

Рівень: B2–C1; власна практика: B2

Subtitle: Passive Infinitive — будова to be + V3 і to have been + V3, ролі учасників, час відносно головної конструкції, питання, заперечення та практика.

Page text: Пасивний інфінітив to be done / to have been done: ролі, часові зв’язки, дієслівні моделі, питання й тематична практика.

### infinitive-hero — hero

Source slot: 0; key: `infinitive-hero`; column: header; level: B2

Поле `infinitive-hero/data/level` — Рівень:

B2–C1

Поле `infinitive-hero/data/intro` — Вступ:

Пасивний інфінітив називає дію, якої зазнає відповідний учасник: to be + V3 або to have been + V3. Це не окремий теперішній чи майбутній час: часовий зв’язок визначаємо за головною конструкцією та контекстом. Бажання, очікування й враження не гарантують, що подія відбулася.

Поле `infinitive-hero/data/rules/0/label` — Назва пункту:

ПРОСТА ФОРМА

Поле `infinitive-hero/data/rules/0/color` — навчальне значення:

emerald

Поле `infinitive-hero/data/rules/0/text` — Правило:

To be + V3: учасник зазнає дії; час задає контекст.

Поле `infinitive-hero/data/rules/0/example` — Приклад:

The report needs to be finished.
Звіт потрібно завершити.

Поле `infinitive-hero/data/rules/1/label` — Назва пункту:

ПЕРФЕКТНА ФОРМА

Поле `infinitive-hero/data/rules/1/color` — навчальне значення:

blue

Поле `infinitive-hero/data/rules/1/text` — Правило:

To have been + V3 показує попередність або завершеність до відповідної точки відліку.

Поле `infinitive-hero/data/rules/1/example` — Приклад:

He seems to have been promoted.
Здається, його підвищили.

Поле `infinitive-hero/data/rules/2/label` — Назва пункту:

ПЕРЕВІР РОЛЬ

Поле `infinitive-hero/data/rules/2/color` — навчальне значення:

amber

Поле `infinitive-hero/data/rules/2/text` — Правило:

Want to invite і want to be invited задають різні ролі, а не просто різну довжину речення.

Поле `infinitive-hero/data/rules/2/example` — Приклад:

I want to be invited.
Я хочу, щоб мене запросили.

### 1. Утворення: active і passive — forms-grid

Source slot: 1; key: `infinitive-forms`; column: left; level: B2

Поле `infinitive-forms/data/title` — Заголовок:

1. Утворення: active і passive

Поле `infinitive-forms/data/intro` — Вступ:

Форма інфінітива не узгоджується з підметом за особою чи числом. У пасиві головне дієслово має V3; to належить до інфінітивної моделі, а be/been передає пасивну будову.

Поле `infinitive-forms/data/items/0/label` — Назва пункту:

ACTIVE SIMPLE

Поле `infinitive-forms/data/items/0/title` — Заголовок:

to + V1

Поле `infinitive-forms/data/items/0/subtitle` — Пояснення / приклад:

I want to write a letter.
Я хочу написати листа.

Поле `infinitive-forms/data/items/0/rules/0/label` — Назва пункту:

ХТО ДІЄ

Поле `infinitive-forms/data/items/0/rules/0/text` — Правило:

Той самий I хоче й має намір писати. Letter — об’єкт написання.

Поле `infinitive-forms/data/items/1/label` — Назва пункту:

PASSIVE SIMPLE

Поле `infinitive-forms/data/items/1/title` — Заголовок:

to be + V3

Поле `infinitive-forms/data/items/1/subtitle` — Пояснення / приклад:

The letter needs to be written.
Листа потрібно написати.

Поле `infinitive-forms/data/items/1/rules/0/label` — Назва пункту:

ХТО ЗАЗНАЄ ДІЇ

Поле `infinitive-forms/data/items/1/rules/0/text` — Правило:

Letter — те, що потрібно написати; виконавця не названо. Need указує на необхідність, а не на вже виконану роботу.

Поле `infinitive-forms/data/items/2/label` — Назва пункту:

ACTIVE PERFECT

Поле `infinitive-forms/data/items/2/title` — Заголовок:

to have + V3

Поле `infinitive-forms/data/items/2/subtitle` — Пояснення / приклад:

He claims to have written the book.
Він стверджує, що написав книжку.

Поле `infinitive-forms/data/items/2/rules/0/label` — Назва пункту:

ДІЯ ДО ТВЕРДЖЕННЯ

Поле `infinitive-forms/data/items/2/rules/0/text` — Правило:

Він називає себе автором попередньої дії. Claims передає його твердження, не незалежне підтвердження.

Поле `infinitive-forms/data/items/3/label` — Назва пункту:

PASSIVE PERFECT

Поле `infinitive-forms/data/items/3/title` — Заголовок:

to have been + V3

Поле `infinitive-forms/data/items/3/subtitle` — Пояснення / приклад:

The book seems to have been written long ago.
Здається, книжку написали давно.

Поле `infinitive-forms/data/items/3/rules/0/label` — Назва пункту:

ПОВНИЙ ЛАНЦЮЖОК

Поле `infinitive-forms/data/items/3/rules/0/text` — Правило:

Have + been + written: книжка зазнала написання раніше; seem позначає теперішнє враження.

### 2. Простий пасивний інфінітив: потреба, бажання, враження — usage-panels

Source slot: 2; key: `infinitive-simple`; column: left; level: B2

Поле `infinitive-simple/data/title` — Заголовок:

2. Простий пасивний інфінітив: потреба, бажання, враження

Поле `infinitive-simple/data/sections/0/label` — Назва пункту:

НЕОБХІДНІСТЬ АБО ОЧІКУВАННЯ

Поле `infinitive-simple/data/sections/0/color` — навчальне значення:

emerald

Поле `infinitive-simple/data/sections/0/description` — Пояснення:

Need може вказувати на потрібну дію з підметом. У моделі expect + object + to be + V3 саме object зазнає очікуваної дії. Очікування не є повідомленням, що її вже виконано.

Поле `infinitive-simple/data/sections/0/examples/0/en` — Англійською:

The report needs to be finished by Friday.

Поле `infinitive-simple/data/sections/0/examples/0/ua` — Українською:

Звіт потрібно завершити до п’ятниці.

Поле `infinitive-simple/data/sections/0/examples/1/en` — Англійською:

They expect the project to be approved.

Поле `infinitive-simple/data/sections/0/examples/1/ua` — Українською:

Вони очікують, що проєкт схвалять.

Поле `infinitive-simple/data/sections/0/note` — Примітка:

У першому реченні report потребує завершення; у другому they очікують, а project має зазнати схвалення. Не переносимо один object-pattern на всі дієслова.

Поле `infinitive-simple/data/sections/1/label` — Назва пункту:

SEEM/APPEAR: ПОДІЯ ЧИ СТАН

Поле `infinitive-simple/data/sections/1/color` — навчальне значення:

blue

Поле `infinitive-simple/data/sections/1/description` — Пояснення:

Після seem/appear інфінітив передає зміст враження. Частота або учасники допомагають побачити дію; без такого контексту to be + -ed може також описувати стан чи властивість.

Поле `infinitive-simple/data/sections/1/examples/0/en` — Англійською:

The hall appears to be cleaned every evening.

Поле `infinitive-simple/data/sections/1/examples/0/ua` — Українською:

Схоже, залу прибирають щовечора.

Поле `infinitive-simple/data/sections/1/examples/1/en` — Англійською:

The door seems to be locked.

Поле `infinitive-simple/data/sections/1/examples/1/ua` — Українською:

Здається, двері замкнені.

Поле `infinitive-simple/data/sections/1/note` — Примітка:

Cleaned every evening описує повторювану дію. Locked у другому прикладі природно описує стан; сам написаний вигляд -ed не доводить окремої події замикання.

Поле `infinitive-simple/data/sections/2/label` — Назва пункту:

WOULD LIKE І PREFER

Поле `infinitive-simple/data/sections/2/color` — навчальне значення:

amber

Поле `infinitive-simple/data/sections/2/description` — Пояснення:

Для бажання чи переваги можна назвати дію, якої має зазнати підмет або явно названий object. Конкретну модель перевіряємо з відповідним дієсловом.

Поле `infinitive-simple/data/sections/2/examples/0/en` — Англійською:

I would like to be informed about the decision.

Поле `infinitive-simple/data/sections/2/examples/0/ua` — Українською:

Я хотів би, щоб мене поінформували про рішення.

Поле `infinitive-simple/data/sections/2/examples/1/en` — Англійською:

She prefers to be left alone.

Поле `infinitive-simple/data/sections/2/examples/1/ua` — Українською:

Вона воліє, щоб її залишили на самоті.

Поле `infinitive-simple/data/sections/2/examples/2/en` — Англійською:

We would like the guests to be informed today.

Поле `infinitive-simple/data/sections/2/examples/2/ua` — Українською:

Ми хотіли б, щоб гостей поінформували сьогодні.

Поле `infinitive-simple/data/sections/2/note` — Примітка:

У третьому реченні we — ті, хто висловлює бажання, guests — ті, кого мають поінформувати. Виконавців повідомлення не названо.

Поле `infinitive-simple/data/sections/3/label` — Назва пункту:

ПИТАННЯ Й ЗАПЕРЕЧЕННЯ ПОВНОГО РЕЧЕННЯ

Поле `infinitive-simple/data/sections/3/color` — навчальне значення:

slate

Поле `infinitive-simple/data/sections/3/description` — Пояснення:

Питання утворює головна особова конструкція, а не сам інфінітив. Not перед to be заперечує інфінітивний зміст; заперечення головного дієслова може передавати іншу позицію мовця.

Поле `infinitive-simple/data/sections/3/examples/0/en` — Англійською:

Do you expect to be invited?

Поле `infinitive-simple/data/sections/3/examples/0/ua` — Українською:

Ти очікуєш, що тебе запросять?

Поле `infinitive-simple/data/sections/3/examples/1/en` — Англійською:

I expect not to be invited.

Поле `infinitive-simple/data/sections/3/examples/1/ua` — Українською:

Я очікую, що мене не запросять.

Поле `infinitive-simple/data/sections/3/examples/2/en` — Англійською:

I don't expect to be invited.

Поле `infinitive-simple/data/sections/3/examples/2/ua` — Українською:

Я не очікую, що мене запросять.

Поле `infinitive-simple/data/sections/3/note` — Примітка:

Expect not передає очікування незапрошення; don't expect заперечує очікування запрошення. У розмові висновки можуть зближуватися, але місце заперечення й буквальний зміст не однакові.

### 3. Перфектний пасивний інфінітив і точка відліку — usage-panels

Source slot: 3; key: `infinitive-perfect`; column: left; level: C1

Поле `infinitive-perfect/data/title` — Заголовок:

3. Перфектний пасивний інфінітив і точка відліку

Поле `infinitive-perfect/data/sections/0/label` — Назва пункту:

ПОПЕРЕДНЯ ДІЯ ВІДНОСНО ВРАЖЕННЯ

Поле `infinitive-perfect/data/sections/0/color` — навчальне значення:

emerald

Поле `infinitive-perfect/data/sections/0/description` — Пояснення:

To have been + V3 часто показує, що дія передує моменту враження або повідомлення. Якщо seem стоїть у минулому, точкою відліку є те минуле враження, а не автоматично теперішній момент.

Поле `infinitive-perfect/data/sections/0/examples/0/en` — Англійською:

He seems to have been promoted last month.

Поле `infinitive-perfect/data/sections/0/examples/0/ua` — Українською:

Здається, його підвищили минулого місяця.

Поле `infinitive-perfect/data/sections/0/examples/1/en` — Англійською:

At the meeting, she seemed to have been informed earlier.

Поле `infinitive-perfect/data/sections/0/examples/1/ua` — Українською:

На зустрічі здавалося, що її поінформували раніше.

Поле `infinitive-perfect/data/sections/0/note` — Примітка:

Seem не перетворює враження на підтверджений факт. Earlier у другому реченні відноситься до моменту зустрічі.

Поле `infinitive-perfect/data/sections/1/label` — Назва пункту:

REPORTING: ДВА РІВНІ ПОВІДОМЛЕННЯ

Поле `infinitive-perfect/data/sections/1/color` — навчальне значення:

blue

Поле `infinitive-perfect/data/sections/1/description` — Пояснення:

У subject + is said/believed/reported + infinitive головна форма передає саме повідомлення або оцінку, а пасивний інфінітив — дію з названим підметом. Зберігай цю дистанцію в перекладі.

Поле `infinitive-perfect/data/sections/1/examples/0/en` — Англійською:

The painting is thought to have been stolen.

Поле `infinitive-perfect/data/sections/1/examples/0/ua` — Українською:

Вважають, що картину вкрали.

Поле `infinitive-perfect/data/sections/1/examples/1/en` — Англійською:

The parcels are reported to have been delivered yesterday.

Поле `infinitive-perfect/data/sections/1/examples/1/ua` — Українською:

Повідомляють, що посилки доставили вчора.

Поле `infinitive-perfect/data/sections/1/note` — Примітка:

Reported не означає, що автор сам перевірив доставку. Для повного розбору reporting є окремі уроки; тут важливий часовий і пасивний склад інфінітива.

Поле `infinitive-perfect/data/sections/2/label` — Назва пункту:

ЗАВЕРШЕННЯ ДО МАЙБУТНЬОЇ МЕЖІ

Поле `infinitive-perfect/data/sections/2/color` — навчальне значення:

amber

Поле `infinitive-perfect/data/sections/2/description` — Пояснення:

Perfect може також позначати очікувану завершеність до названої майбутньої точки. Тоді подія ще не обов’язково відбулася: головне hope/expect та строк визначають прочитання.

Поле `infinitive-perfect/data/sections/2/examples/0/en` — Англійською:

We hope to have been told the result by Friday.

Поле `infinitive-perfect/data/sections/2/examples/0/ua` — Українською:

Ми сподіваємося, що до п’ятниці нам уже повідомлять результат.

Поле `infinitive-perfect/data/sections/2/note` — Примітка:

Очікуване повідомлення має завершитися до п’ятниці. Це не універсальний «минулий час» інфінітива й не гарантія, що результат уже повідомлено.

Поле `infinitive-perfect/data/sections/3/label` — Назва пункту:

ПІСЛЯ MODAL — БЕЗ TO

Поле `infinitive-perfect/data/sections/3/color` — навчальне значення:

rose

Поле `infinitive-perfect/data/sections/3/description` — Пояснення:

Must be + V3 і may have been + V3 — споріднені пасивні моделі з інфінітивом без to, не приклади to-infinitive. Значення modal визначає контекст: оцінка, очікування, необхідність або можливість, а не лише здогад про минуле.

Поле `infinitive-perfect/data/sections/3/examples/0/en` — Англійською:

The work should have been completed yesterday, but it is still unfinished.

Поле `infinitive-perfect/data/sections/3/examples/0/ua` — Українською:

Роботу слід було завершити вчора, але вона досі незавершена.

Поле `infinitive-perfect/data/sections/3/examples/1/en` — Англійською:

The message may have been deleted.

Поле `infinitive-perfect/data/sections/3/examples/1/ua` — Українською:

Можливо, повідомлення видалили.

Поле `infinitive-perfect/data/sections/3/examples/2/en` — Англійською:

The bag could have been stolen, but nobody took it.

Поле `infinitive-perfect/data/sections/3/examples/2/ua` — Українською:

Сумку могли вкрасти, але ніхто її не взяв.

Поле `infinitive-perfect/data/sections/3/note` — Примітка:

Перше речення оцінює невиконану очікувану дію; друге висловлює можливість попередньої події; третє в цьому контексті описує можливий, але не реалізований небажаний результат. Деталі modal meanings залишаються в окремих уроках.

### 4. Дієслівна модель визначає ролі — usage-panels

Source slot: 4; key: `infinitive-patterns`; column: left; level: B2

Поле `infinitive-patterns/data/title` — Заголовок:

4. Дієслівна модель визначає ролі

Поле `infinitive-patterns/data/sections/0/label` — Назва пункту:

WANT: ХТО ЗАПРОШУЄ І КОГО

Поле `infinitive-patterns/data/sections/0/color` — навчальне значення:

emerald

Поле `infinitive-patterns/data/sections/0/description` — Пояснення:

Без окремого object підмет want зазвичай є й відповідним учасником інфінітивної дії. З object + to-infinitive з’являється інший явно названий учасник. Active/passive визначає, виконує він дію чи зазнає її.

Поле `infinitive-patterns/data/sections/0/examples/0/en` — Англійською:

I want to invite Maya.

Поле `infinitive-patterns/data/sections/0/examples/0/ua` — Українською:

Я хочу запросити Майю.

Поле `infinitive-patterns/data/sections/0/examples/1/en` — Англійською:

I want to be invited.

Поле `infinitive-patterns/data/sections/0/examples/1/ua` — Українською:

Я хочу, щоб мене запросили.

Поле `infinitive-patterns/data/sections/0/examples/2/en` — Англійською:

I want Maya to be invited.

Поле `infinitive-patterns/data/sections/0/examples/2/ua` — Українською:

Я хочу, щоб Майю запросили.

Поле `infinitive-patterns/data/sections/0/note` — Примітка:

У першому реченні я маю намір запросити; у другому запросити мають мене; у третьому — Майю. Want не вказує, хто саме має запросити в другій і третій моделях.

Поле `infinitive-patterns/data/sections/1/label` — Назва пункту:

NEED, EXPECT ТА ФОРМАЛЬНЕ WISH

Поле `infinitive-patterns/data/sections/1/color` — навчальне значення:

blue

Поле `infinitive-patterns/data/sections/1/description` — Пояснення:

Зберігай керування потрібного дієслова. Need може описувати необхідність дії з предметом; expect — очікування; wish to — формальніше висловлене бажання. Не замінюй їх одне одним без зміни змісту.

Поле `infinitive-patterns/data/sections/1/examples/0/en` — Англійською:

The floor needs to be cleaned.

Поле `infinitive-patterns/data/sections/1/examples/0/ua` — Українською:

Підлогу потрібно помити.

Поле `infinitive-patterns/data/sections/1/examples/1/en` — Англійською:

I expect to be paid on time.

Поле `infinitive-patterns/data/sections/1/examples/1/ua` — Українською:

Я очікую, що мені заплатять вчасно.

Поле `infinitive-patterns/data/sections/1/examples/2/en` — Англійською:

I wish to be informed of any changes.

Поле `infinitive-patterns/data/sections/1/examples/2/ua` — Українською:

Я бажаю, щоб мене інформували про будь-які зміни.

Поле `infinitive-patterns/data/sections/1/note` — Примітка:

Це моделі конкретних дієслів, а не дозвіл додавати object після кожного слова зі списку. Hope, наприклад, не копіює want + object + to-infinitive у звичайному навчальному вживанні.

Поле `infinitive-patterns/data/sections/2/label` — Назва пункту:

SEEM, HAPPEN І TURN OUT НЕ ТОТОЖНІ

Поле `infinitive-patterns/data/sections/2/color` — навчальне значення:

amber

Поле `infinitive-patterns/data/sections/2/description` — Пояснення:

Seem/appear передають враження; happen може позначати випадковий збіг; turn out — те, що з’ясувалося. Заміна головного дієслова змінює позицію мовця, тому її не робимо лише заради простішої формули.

Поле `infinitive-patterns/data/sections/2/examples/0/en` — Англійською:

He seems to be trusted by his colleagues.

Поле `infinitive-patterns/data/sections/2/examples/0/ua` — Українською:

Здається, колеги йому довіряють.

Поле `infinitive-patterns/data/sections/2/examples/1/en` — Англійською:

She happened to be chosen for the role.

Поле `infinitive-patterns/data/sections/2/examples/1/ua` — Українською:

Так сталося, що її обрали на роль.

Поле `infinitive-patterns/data/sections/2/examples/2/en` — Англійською:

The key turned out to have been left at reception.

Поле `infinitive-patterns/data/sections/2/examples/2/ua` — Українською:

З’ясувалося, що ключ залишили на стійці реєстрації.

Поле `infinitive-patterns/data/sections/2/note` — Примітка:

Природний приклад із ключем замінює перевантажене turned out to be proven wrong. Turn out не підмінено seem або prove; збережено значення з’ясованого результату.

Поле `infinitive-patterns/data/sections/3/label` — Назва пункту:

НЕ КОЖНЕ TO BE — ПАСИВ

Поле `infinitive-patterns/data/sections/3/color` — навчальне значення:

slate

Поле `infinitive-patterns/data/sections/3/description` — Пояснення:

Після reporting або seem може стояти й be з прикметником, а не пасивним V3. Спершу визнач структуру: бути розумним — не те саме, що зазнати чиєїсь дії.

Поле `infinitive-patterns/data/sections/3/examples/0/en` — Англійською:

He is said to be very intelligent.

Поле `infinitive-patterns/data/sections/3/examples/0/ua` — Українською:

Кажуть, що він дуже розумний.

Поле `infinitive-patterns/data/sections/3/examples/1/en` — Англійською:

He is said to be respected by his neighbours.

Поле `infinitive-patterns/data/sections/3/examples/1/ua` — Українською:

Кажуть, що сусіди його поважають.

Поле `infinitive-patterns/data/sections/3/note` — Примітка:

У першій версії intelligent — прикметник, у другій respected by his neighbours передає пасивну роль. Саме to be не доводить пасивної конструкції.

Поле `infinitive-patterns/data/sections/4/label` — Назва пункту:

ПІСЛЯ ПРИКМЕТНИКА ЧИ ІМЕННИКА

Поле `infinitive-patterns/data/sections/4/color` — навчальне значення:

sky

Поле `infinitive-patterns/data/sections/4/description` — Пояснення:

Інфінітив може доповнювати не лише дієслово. Ready позначає готовність, а plan — план; конкретне слово визначає, чи потрібен for + учасник. Пасивна форма називає дію з цим учасником, а не того, хто складає план.

Поле `infinitive-patterns/data/sections/4/examples/0/en` — Англійською:

The room is ready to be painted.

Поле `infinitive-patterns/data/sections/4/examples/0/ua` — Українською:

Кімната готова до того, щоб її пофарбували.

Поле `infinitive-patterns/data/sections/4/examples/1/en` — Англійською:

We made a plan for the room to be painted in June.

Поле `infinitive-patterns/data/sections/4/examples/1/ua` — Українською:

Ми склали план, за яким кімнату мають пофарбувати в червні.

Поле `infinitive-patterns/data/sections/4/note` — Примітка:

У другому прикладі we склали план, room має зазнати фарбування, а самих малярів не названо. Не додаємо for після кожного прикметника або іменника механічно.

### 5. Час визначай відносно головної конструкції — comparison-table

Source slot: 5; key: `infinitive-time-comparison`; column: left; level: B2

Поле `infinitive-time-comparison/data/title` — Заголовок:

5. Час визначай відносно головної конструкції

Поле `infinitive-time-comparison/data/intro` — Вступ:

Простий інфінітив не називай окремим Present/Future tense. Perfect зіставляй із конкретною точкою відліку, а не тільки зі словом «раніше».

Поле `infinitive-time-comparison/data/headers/0` — навчальне значення:

Конструкція і переклад

Поле `infinitive-time-comparison/data/headers/1` — навчальне значення:

Точка відліку

Поле `infinitive-time-comparison/data/headers/2` — навчальне значення:

Що можна стверджувати

Поле `infinitive-time-comparison/data/rows/0/cells/0` — навчальне значення:

The hall appears to be cleaned every evening.
Схоже, залу прибирають щовечора.

Поле `infinitive-time-comparison/data/rows/0/cells/1` — навчальне значення:

Теперішнє враження про регулярну дію.

Поле `infinitive-time-comparison/data/rows/0/cells/2` — навчальне значення:

Appears передає висновок/враження; every evening задає повторюваність.

Поле `infinitive-time-comparison/data/rows/1/cells/0` — навчальне значення:

I hoped to be invited, but no invitation came.
Я сподівався, що мене запросять, але запрошення не надійшло.

Поле `infinitive-time-comparison/data/rows/1/cells/1` — навчальне значення:

Минула надія щодо пізнішого запрошення.

Поле `infinitive-time-comparison/data/rows/1/cells/2` — навчальне значення:

To be invited сумісне з минулим hoped. Друга частина прямо повідомляє, що очікування не здійснилося.

Поле `infinitive-time-comparison/data/rows/2/cells/0` — навчальне значення:

She seems to have been informed earlier.
Здається, її поінформували раніше.

Поле `infinitive-time-comparison/data/rows/2/cells/1` — навчальне значення:

Раніше за теперішнє враження.

Поле `infinitive-time-comparison/data/rows/2/cells/2` — навчальне значення:

Попередність передає have been informed; seem не гарантує істинності висновку.

Поле `infinitive-time-comparison/data/rows/3/cells/0` — навчальне значення:

We hope to have been told the result by Friday.
Ми сподіваємося, що до п’ятниці нам уже повідомлять результат.

Поле `infinitive-time-comparison/data/rows/3/cells/1` — навчальне значення:

Завершення до майбутнього строку.

Поле `infinitive-time-comparison/data/rows/3/cells/2` — навчальне значення:

Hope + by Friday допускає майбутню завершеність, не твердження про вже отриманий результат.

Поле `infinitive-time-comparison/data/warning` — Застереження:

Should have been finished не містить to; його значення залежить від контексту. Не записуй усі modal-perfect форми в колонку to have been + V3.

### 6. Перевір повний ланцюжок і заданий час — mistakes-grid

Source slot: 6; key: `infinitive-mistakes`; column: left; level: B2

Поле `infinitive-mistakes/data/title` — Заголовок:

6. Перевір повний ланцюжок і заданий час

Поле `infinitive-mistakes/data/items/0/label` — Назва пункту:

V3 ПІСЛЯ BE

Поле `infinitive-mistakes/data/items/0/color` — навчальне значення:

rose

Поле `infinitive-mistakes/data/items/0/title` — Заголовок:

У запрошенні потрібне invited, а не invite.

Поле `infinitive-mistakes/data/items/0/wrong_en` — Помилкова / невідповідна заданому задуму версія:

She wants to be invite.

Поле `infinitive-mistakes/data/items/0/wrong_ua` — Український задум:

Задум: Вона хоче, щоб її запросили.

Поле `infinitive-mistakes/data/items/0/right_en` — Виправлення:

She wants to be invited.

Поле `infinitive-mistakes/data/items/0/right_ua` — Переклад виправлення:

Вона хоче, щоб її запросили.

Поле `infinitive-mistakes/data/items/0/note` — Примітка:

Wants лишається головним дієсловом, to be invited називає бажану дію з нею.

Поле `infinitive-mistakes/data/items/1/label` — Назва пункту:

ПОПЕРЕДНЯ РАЗОВА ПОДІЯ

Поле `infinitive-mistakes/data/items/1/color` — навчальне значення:

amber

Поле `infinitive-mistakes/data/items/1/title` — Заголовок:

За теперішнім враженням підвищення відбулося торік.

Поле `infinitive-mistakes/data/items/1/wrong_en` — Помилкова / невідповідна заданому задуму версія:

He seems to be promoted last year.

Поле `infinitive-mistakes/data/items/1/wrong_ua` — Український задум:

Задум: Здається, його підвищили торік.

Поле `infinitive-mistakes/data/items/1/right_en` — Виправлення:

He seems to have been promoted last year.

Поле `infinitive-mistakes/data/items/1/right_ua` — Переклад виправлення:

Здається, його підвищили торік.

Поле `infinitive-mistakes/data/items/1/note` — Примітка:

Для заданої попередньої завершеної події потрібне have been promoted. Не змінюємо теперішнє seems на минуле лише заради слова last year.

Поле `infinitive-mistakes/data/items/2/label` — Назва пункту:

ПОРЯДОК PERFECT PASSIVE

Поле `infinitive-mistakes/data/items/2/color` — навчальне значення:

sky

Поле `infinitive-mistakes/data/items/2/title` — Заголовок:

Have стоїть перед been, а V3 — після been.

Поле `infinitive-mistakes/data/items/2/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The parcel seems to been have delivered.

Поле `infinitive-mistakes/data/items/2/wrong_ua` — Український задум:

Задум: Здається, посилку доставили.

Поле `infinitive-mistakes/data/items/2/right_en` — Виправлення:

The parcel seems to have been delivered.

Поле `infinitive-mistakes/data/items/2/right_ua` — Переклад виправлення:

Здається, посилку доставили.

Поле `infinitive-mistakes/data/items/2/note` — Примітка:

Цілісний ланцюжок: to + have + been + delivered.

Поле `infinitive-mistakes/data/items/3/label` — Назва пункту:

ПІСЛЯ MUST БЕЗ TO

Поле `infinitive-mistakes/data/items/3/color` — навчальне значення:

rose

Поле `infinitive-mistakes/data/items/3/title` — Заголовок:

Modal-пасив — споріднена, але інша будова.

Поле `infinitive-mistakes/data/items/3/wrong_en` — Помилкова / невідповідна заданому задуму версія:

The form must to be signed.

Поле `infinitive-mistakes/data/items/3/wrong_ua` — Український задум:

Задум: Форму треба підписати.

Поле `infinitive-mistakes/data/items/3/right_en` — Виправлення:

The form must be signed.

Поле `infinitive-mistakes/data/items/3/right_ua` — Переклад виправлення:

Форму треба підписати.

Поле `infinitive-mistakes/data/items/3/note` — Примітка:

Після must іде be + V3 без to. Це необхідність у заданому контексті, не автоматичний здогад про минуле.

### 7. Короткий підсумок — summary-list

Source slot: 7; key: `infinitive-summary`; column: left; level: B2

Поле `infinitive-summary/data/title` — Заголовок:

7. Короткий підсумок

Поле `infinitive-summary/data/items/0` — навчальне значення:

Проста пасивна модель: to be + V3; перфектна: to have been + V3.

Поле `infinitive-summary/data/items/1` — навчальне значення:

Інфінітив не має окремого Present/Future tense: час зіставляємо з головним дієсловом і контекстом.

Поле `infinitive-summary/data/items/2` — навчальне значення:

Perfect часто означає попередність, але hope/expect зі строком може передавати завершення до майбутньої межі.

Поле `infinitive-summary/data/items/3` — навчальне значення:

Перевіряй ролі: I want to invite — я хочу запросити; I want to be invited — я хочу, щоб запросили мене; I want Maya to be invited — я хочу, щоб запросили Майю.

Поле `infinitive-summary/data/items/4` — навчальне значення:

Питання утворює головна особова конструкція. Not to be і заперечення головного дієслова можуть передавати різний буквальний зміст.

Поле `infinitive-summary/data/items/5` — навчальне значення:

Стандартна навчальна модель після need — needs to be finished; needs finishing також можливе, якщо завдання не обмежене інфінітивом.

Поле `infinitive-summary/data/items/6` — навчальне значення:

Після modal немає to: must be signed — треба підписати; may have been deleted — можливо, видалили. Значення modal визначає контекст.

Поле `infinitive-summary/data/items/7` — навчальне значення:

To be + -ed може також описувати стан; не кожний такий запис є однозначною пасивною подією.

### Passive Infinitive and Gerund — navigation-chips

Source slot: 8; key: `infinitive-navigation`; column: footer; level: не заданий

Поле `infinitive-navigation/data/title` — Заголовок:

Passive Infinitive and Gerund

Поле `infinitive-navigation/data/items/0/label` — Назва пункту:

Passive Infinitive — to be done / to have been done

Поле `infinitive-navigation/data/items/1/label` — Назва пункту:

Passive Gerund — being done / having been done

Поле `infinitive-navigation/data/items/1/url` — Посилання:

/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-gerund

### 8. Практика — practice-set

Source slot: новий appended practice; key: `infinitive-practice`; column: left; level: B2

Поле `infinitive-practice/data/title` — Заголовок:

8. Практика

Поле `infinitive-practice/data/select_title` — Група форми:

Доповни саме інфінітивну модель

Поле `infinitive-practice/data/select_intro` — Інструкція до форми:

Час і потрібну конструкцію задано. Обери повний ланцюжок, а не іншу можливу перефразу.

Поле `infinitive-practice/data/selects/0/id` — навчальне значення:

m55-infinitive-form-simple

Поле `infinitive-practice/data/selects/0/label` — Назва пункту:

У стандартному to-infinitive потрібно сказати: «Бейджі потрібно надрукувати до понеділка».
The badges need ___ by Monday.

Поле `infinitive-practice/data/selects/0/options/0` — навчальне значення:

to be print

Поле `infinitive-practice/data/selects/0/options/1` — навчальне значення:

to be printed

Поле `infinitive-practice/data/selects/0/options/2` — навчальне значення:

to printed

Поле `infinitive-practice/data/selects/0/answer` — Канонічна відповідь:

to be printed

Поле `infinitive-practice/data/selects/0/accepted/0` — навчальне значення:

to be printed

Поле `infinitive-practice/data/selects/0/feedback/to be print` — навчальне значення:

The badges need to be printed by Monday. — Бейджі потрібно надрукувати до понеділка. Після be потрібне V3 printed.

Поле `infinitive-practice/data/selects/0/feedback/to be printed` — навчальне значення:

Правильно: The badges need to be printed by Monday. — Бейджі потрібно надрукувати до понеділка. To be + printed передає потрібну пасивну дію.

Поле `infinitive-practice/data/selects/0/feedback/to printed` — навчальне значення:

The badges need to be printed by Monday. — Бейджі потрібно надрукувати до понеділка. У заданому пасивному інфінітиві бракує be.

Поле `infinitive-practice/data/selects/1/id` — навчальне значення:

m55-infinitive-form-perfect

Поле `infinitive-practice/data/selects/1/label` — Назва пункту:

Зараз є враження, що квитки надіслали ще вчора. Доповни пасивний інфінітив: «Здається, квитки надіслали вчора».
The tickets seem ___ yesterday.

Поле `infinitive-practice/data/selects/1/options/0` — навчальне значення:

to have been sent

Поле `infinitive-practice/data/selects/1/options/1` — навчальне значення:

to be sent

Поле `infinitive-practice/data/selects/1/options/2` — навчальне значення:

to have be sent

Поле `infinitive-practice/data/selects/1/answer` — Канонічна відповідь:

to have been sent

Поле `infinitive-practice/data/selects/1/accepted/0` — навчальне значення:

to have been sent

Поле `infinitive-practice/data/selects/1/feedback/to have been sent` — навчальне значення:

Правильно: The tickets seem to have been sent yesterday. — Здається, квитки надіслали вчора. Have been sent показує попередню дію відносно теперішнього враження.

Поле `infinitive-practice/data/selects/1/feedback/to be sent` — навчальне значення:

The tickets seem to have been sent yesterday. — Здається, квитки надіслали вчора. Задано попередню завершену подію, тому потрібне perfect.

Поле `infinitive-practice/data/selects/1/feedback/to have be sent` — навчальне значення:

The tickets seem to have been sent yesterday. — Здається, квитки надіслали вчора. Після have потрібне been, не be.

Поле `infinitive-practice/data/choice_title` — Група змісту:

Перевір ролі та межі твердження

Поле `infinitive-practice/data/choice_intro` — Інструкція до змісту:

Обери висновок за англійським стимулом. Точний переклад з’явиться після перевірки, щоб не підказувати відповідь.

Поле `infinitive-practice/data/choices/0/id` — навчальне значення:

m55-infinitive-meaning-receiver

Поле `infinitive-practice/data/choices/0/label` — Назва пункту:

У розмові є я, Ніна й організатори. З повідомлення визнач, кого мають запросити; не вигадуй, хто саме має це зробити.

Поле `infinitive-practice/data/choices/0/prompt` — Стимул:

I want Nina to be invited.

Поле `infinitive-practice/data/choices/0/options/0` — навчальне значення:

Запросити мають Ніну; хто саме запросить, не вказано.

Поле `infinitive-practice/data/choices/0/options/1` — навчальне значення:

Ніна має запросити мене.

Поле `infinitive-practice/data/choices/0/options/2` — навчальне значення:

Я обов’язково сам запрошу Ніну.

Поле `infinitive-practice/data/choices/0/answer` — Канонічна відповідь:

Запросити мають Ніну; хто саме запросить, не вказано.

Поле `infinitive-practice/data/choices/0/accepted/0` — навчальне значення:

Запросити мають Ніну; хто саме запросить, не вказано.

Поле `infinitive-practice/data/choices/0/feedback/запросити мають ніну; хто саме запросить, не вказано.` — навчальне значення:

Правильно: I want Nina to be invited. — Я хочу, щоб Ніну запросили. I висловлює бажання, Nina має зазнати запрошення; виконавця не названо.

Поле `infinitive-practice/data/choices/0/feedback/ніна має запросити мене.` — навчальне значення:

I want Nina to be invited. — Я хочу, щоб Ніну запросили. Пасив to be invited робить Nina тією, кого запросять, не виконавицею запрошення.

Поле `infinitive-practice/data/choices/0/feedback/я обов’язково сам запрошу ніну.` — навчальне значення:

I want Nina to be invited. — Я хочу, щоб Ніну запросили. Want передає бажання, не обіцянку; речення не встановлює мене виконавцем запрошення.

Поле `infinitive-practice/data/choices/1/id` — навчальне значення:

m55-infinitive-meaning-expectation

Поле `infinitive-practice/data/choices/1/label` — Назва пункту:

Сьогодні вівторок. Який висновок підтримує це повідомлення без додаткових фактів?

Поле `infinitive-practice/data/choices/1/prompt` — Стимул:

We hope to be informed on Friday.

Поле `infinitive-practice/data/choices/1/options/0` — навчальне значення:

Інформування точно відбулося до вівторка.

Поле `infinitive-practice/data/choices/1/options/1` — навчальне значення:

Є надія отримати інформацію в п’ятницю; здійснення ще не підтверджене.

Поле `infinitive-practice/data/choices/1/options/2` — навчальне значення:

Мовці обов’язково самі когось інформуватимуть.

Поле `infinitive-practice/data/choices/1/answer` — Канонічна відповідь:

Є надія отримати інформацію в п’ятницю; здійснення ще не підтверджене.

Поле `infinitive-practice/data/choices/1/accepted/0` — навчальне значення:

Є надія отримати інформацію в п’ятницю; здійснення ще не підтверджене.

Поле `infinitive-practice/data/choices/1/feedback/інформування точно відбулося до вівторка.` — навчальне значення:

We hope to be informed on Friday. — Ми сподіваємося, що нас поінформують у п’ятницю. Hope не підтверджує завершеної події, а п’ятниця тут ще попереду.

Поле `infinitive-practice/data/choices/1/feedback/є надія отримати інформацію в п’ятницю; здійснення ще не підтверджене.` — навчальне значення:

Правильно: We hope to be informed on Friday. — Ми сподіваємося, що нас поінформують у п’ятницю. To be informed називає бажану майбутню щодо вівторка дію з нами.

Поле `infinitive-practice/data/choices/1/feedback/мовці обов’язково самі когось інформуватимуть.` — навчальне значення:

We hope to be informed on Friday. — Ми сподіваємося, що нас поінформують у п’ятницю. We зазнає інформування; виконавця тут не названо.

Поле `infinitive-practice/data/input_title` — Група побудови речень:

Побудуй повне речення з пасивним інфінітивом

Поле `infinitive-practice/data/input_intro` — Інструкція до побудови:

Введи речення вручну або використай усі токени банку. Дотримуйся заданого дієслова й to-infinitive, щоб не змінити модель.

Поле `infinitive-practice/data/inputs/0/id` — навчальне значення:

m55-infinitive-build-need

Поле `infinitive-practice/data/inputs/0/before` — Перемішаний банк:

Friday. / to / The / be / needs / repaired / printer / by

Поле `infinitive-practice/data/inputs/0/after` — Українська умова:

Скажи: «Принтер потрібно відремонтувати до п’ятниці». Почни з The printer, використай needs і саме пасивний to-infinitive з repair. Лексика: printer — принтер; by Friday — до п’ятниці. Не називай виконавця.

Поле `infinitive-practice/data/inputs/0/answer` — Канонічна відповідь:

The printer needs to be repaired by Friday.

Поле `infinitive-practice/data/inputs/0/accepted/0` — навчальне значення:

The printer needs to be repaired by Friday.

Поле `infinitive-practice/data/inputs/0/feedback/the printer needs to be repaired by friday.` — навчальне значення:

Правильно: The printer needs to be repaired by Friday. — Принтер потрібно відремонтувати до п’ятниці. Printer зазнає ремонту; by Friday тут строк, а не агент.

Поле `infinitive-practice/data/inputs/0/feedback/the printer needs to be repaired by friday` — навчальне значення:

Правильно: The printer needs to be repaired by Friday. — Принтер потрібно відремонтувати до п’ятниці.

Поле `infinitive-practice/data/inputs/0/feedback/the printer needs repairing by friday.` — навчальне значення:

The printer needs repairing by Friday. — Принтер потрібно відремонтувати до п’ятниці. Це граматична альтернатива, але завдання прямо просить to-infinitive: needs to be repaired.

Поле `infinitive-practice/data/inputs/1/id` — навчальне значення:

m55-infinitive-build-object

Поле `infinitive-practice/data/inputs/1/before` — Перемішаний банк:

today. / invited / I / to / want / be / guests / the

Поле `infinitive-practice/data/inputs/1/after` — Українська умова:

Передай бажання: «Я хочу, щоб гостей запросили сьогодні». Почни з I want і використай object the guests та пасивний to-infinitive з invite. Лексика: guests — гості. Залиш today наприкінці, щоб часовий акцент був однаковим.

Поле `infinitive-practice/data/inputs/1/answer` — Канонічна відповідь:

I want the guests to be invited today.

Поле `infinitive-practice/data/inputs/1/accepted/0` — навчальне значення:

I want the guests to be invited today.

Поле `infinitive-practice/data/inputs/1/feedback/i want the guests to be invited today.` — навчальне значення:

Правильно: I want the guests to be invited today. — Я хочу, щоб гостей запросили сьогодні. I висловлює бажання; the guests зазнають запрошення.

Поле `infinitive-practice/data/inputs/1/feedback/i want the guests to be invited today` — навчальне значення:

Правильно: I want the guests to be invited today. — Я хочу, щоб гостей запросили сьогодні.

Поле `infinitive-practice/data/inputs/1/feedback/i want to invite the guests today.` — навчальне значення:

I want to invite the guests today. — Я хочу запросити гостей сьогодні. Це граматична інша модель, у якій я сам хочу запросити. Завдання просить залишити виконавця неназваним: the guests to be invited.

Поле `infinitive-practice/data/linked_practice/source` — навчальне значення:

theory_links

Поле `infinitive-practice/data/linked_practice/question_types/0` — навчальне значення:

4

Поле `infinitive-practice/data/linked_practice/seeder_classes/0` — навчальне значення:

Database\Seeders\V3\Polyglot\PolyglotPassiveVoicePassiveInfinitiveAllLevelsLessonSeeder

Поле `infinitive-practice/data/linked_practice/title` — Заголовок:

Вправа 4. Побудуй речення

Поле `infinitive-practice/data/linked_practice/intro` — Вступ:

Склади саме інфінітивну конструкцію в нейтральному порядку, починаючи з підмета. Обставини часу став наприкінці. Перевір ролі та повний to be / to have been ланцюжок; враження й очікування не перетворюй на гарантований факт.

Поле `infinitive-practice/data/linked_practice/footer` — Підпис:

Завдання підібрані лише для цієї теми.

### Змістовні point-level «Докладніше»

#### Стандартна модель, -ing альтернатива й регіональний варіант

Key: `infinitive-need-variation`; owner: `infinitive-simple/sections/0`

Поле `infinitive-need-variation/fragment/data/sections/0/label` — Назва пункту:

НЕ ЗМІШУЙ НОРМУ З ОПИСОМ УСІХ РІЗНОВИДІВ

Поле `infinitive-need-variation/fragment/data/sections/0/color` — навчальне значення:

slate

Поле `infinitive-need-variation/fragment/data/sections/0/description` — Пояснення:

Для цього уроку основна стандартна модель — need + to be + V3. Need + -ing також природно передає потребу виконати дію з предметом, тож відкрита відповідь без обмеження форми не повинна відхиляти її як неграматичну. У деяких регіональних різновидах англійської трапляється need + participle без to be; дослідження Yale описує цю варіативність. Вона не робить регіональний варіант основним шаблоном нашої стандартної навчальної вправи.

Поле `infinitive-need-variation/fragment/data/sections/0/examples/0/en` — Англійською:

The report needs to be finished.

Поле `infinitive-need-variation/fragment/data/sections/0/examples/0/ua` — Українською:

Звіт потрібно завершити.

Поле `infinitive-need-variation/fragment/data/sections/0/examples/1/en` — Англійською:

The report needs finishing.

Поле `infinitive-need-variation/fragment/data/sections/0/examples/1/ua` — Українською:

Звіт потрібно завершити.

Поле `infinitive-need-variation/fragment/data/sections/0/examples/2/en` — Англійською:

The report needs finished.

Поле `infinitive-need-variation/fragment/data/sections/0/examples/2/ua` — Українською:

Звіт потрібно завершити.

Поле `infinitive-need-variation/fragment/data/sections/0/note` — Примітка:

Третій приклад тут позначений лише як регіональне вживання, не як основна навчальна відповідь. Коли завдання прямо просить to-infinitive, це обмеження моделі, а не твердження про неможливість усіх інших різновидів.

#### Чому work to do не потрібно автоматично виправляти

Key: `infinitive-work-to-do`; owner: `infinitive-patterns/sections/0`

Поле `infinitive-work-to-do/fragment/data/sections/0/label` — Назва пункту:

ЗАВДАННЯ АБО ДІЯ З ОБ’ЄКТОМ

Поле `infinitive-work-to-do/fragment/data/sections/0/color` — навчальне значення:

slate

Поле `infinitive-work-to-do/fragment/data/sections/0/description` — Пояснення:

Після іменника активний інфінітив може природно називати завдання або призначення, навіть якщо іменник не виконує дію. У конструкції з there is різниця між активним і пасивним формулюванням обов’язку часто невелика. Тому наявність предмета перед to не дозволяє механічно вимагати пасив. Спершу визначаємо цілу конструкцію, а не застосовуємо правило з want + object без меж.

Поле `infinitive-work-to-do/fragment/data/sections/0/examples/0/en` — Англійською:

There is still work to do.

Поле `infinitive-work-to-do/fragment/data/sections/0/examples/0/ua` — Українською:

Ще є робота, яку треба виконати.

Поле `infinitive-work-to-do/fragment/data/sections/0/examples/1/en` — Англійською:

There is still work to be done.

Поле `infinitive-work-to-do/fragment/data/sections/0/examples/1/ua` — Українською:

Ще є робота, яку треба виконати.

Поле `infinitive-work-to-do/fragment/data/sections/0/note` — Примітка:

Обидва речення можуть повідомляти про невиконане завдання. Однаковий український переклад не означає, що active/passive взаємозамінні після кожного іменника або дієслова.

## Первинні джерела: actual URL і спосіб доступу

[https://dictionary.cambridge.org/grammar/british-grammar/passive-forms](https://dictionary.cambridge.org/grammar/british-grammar/passive-forms)

Доступ: direct-403; publisher-indexed-editorial-text-read

Перевірено: Утворення be-passive, допоміжні ланцюжки; не копіювалися видавничі exercise banks.

[https://dictionary.cambridge.org/grammar/british-grammar/passive-uses](https://dictionary.cambridge.org/grammar/british-grammar/passive-uses)

Доступ: direct-403; publisher-indexed-editorial-text-read

Перевірено: Фокус, тема й агент; окремий граматичний засіб не гарантує достовірності повідомлення.

[https://dictionary.cambridge.org/grammar/british-grammar/passive-active](https://dictionary.cambridge.org/grammar/british-grammar/passive-active)

Доступ: direct-403; publisher-indexed-editorial-text-read

Перевірено: Actual requested page title Infinitive: active or passive?; passive infinitive roles, work to do / work to be done.

[https://dictionary.cambridge.org/grammar/british-grammar/infinitives](https://dictionary.cambridge.org/grammar/british-grammar/infinitives)

Доступ: direct-403; actual-indexed-URL-below

Перевірено: Не заявляється повне пряме читання запитаного URL.

[https://dictionary.cambridge.org/de/grammatik/british-grammar/infinitives-with-and-without-to](https://dictionary.cambridge.org/de/grammatik/british-grammar/infinitives-with-and-without-to)

Доступ: publisher-indexed-editorial-text-read

Перевірено: Форма інфінітива з/без to; після modal немає to. Німецька URL-localisation з англійським редакційним body.

[https://dictionary.cambridge.org/grammar/british-grammar/perfect-infinitive-with-to](https://dictionary.cambridge.org/grammar/british-grammar/perfect-infinitive-with-to)

Доступ: direct-403; publisher-indexed-editorial-text-read

Перевірено: Попередність та очікувана завершеність до майбутньої точки; perfect не універсально означає минуле до тепер.

[https://dictionary.cambridge.org/grammar/british-grammar/finite-and-non-finite-](https://dictionary.cambridge.org/grammar/british-grammar/finite-and-non-finite-)

Доступ: publisher-indexed-editorial-text-read

Перевірено: Finite форми передають tense/person/number; інфінітив — non-finite, не окремий Present/Future tense.

[https://dictionary.cambridge.org/grammar/british-grammar/want.](https://dictionary.cambridge.org/grammar/british-grammar/want.)

Доступ: publisher-indexed-editorial-text-read

Перевірено: Want + infinitive та want + object + infinitive; не переносити object-pattern на всі дієслова.

[https://dictionary.cambridge.org/grammar/british-grammar/wish](https://dictionary.cambridge.org/grammar/british-grammar/wish)

Доступ: publisher-indexed-editorial-text-read

Перевірено: Формальне wish + to-infinitive як бажання; не змішано з іншими wish-моделями.

[https://dictionary.cambridge.org/dictionary/english/good-time-was-had-by-all](https://dictionary.cambridge.org/dictionary/english/good-time-was-had-by-all)

Доступ: publisher-indexed-editorial-dictionary-text-read

Перевірено: Засвідчений idiom, а не автоматична помилка пасиву; corpus rows відділені від редакційного визначення.

[https://dictionary.cambridge.org/grammar/british-grammar/passives-with-and-](https://dictionary.cambridge.org/grammar/british-grammar/passives-with-and-)

Доступ: publisher-indexed-UK-localisation-editorial-text-read

Перевірено: Важливий агент/причина через by, інструмент через with, можливість агента-неістоти.

[https://dictionary.cambridge.org/uk/grammar/british-grammar/passives-with-and-](https://dictionary.cambridge.org/uk/grammar/british-grammar/passives-with-and-)

Доступ: publisher-indexed-editorial-text-read

Перевірено: Actual indexed URL для попереднього запиту; жодної універсальної заборони by someone.

[https://dictionary.cambridge.org/grammar/british-grammar/verb](https://dictionary.cambridge.org/grammar/british-grammar/verb)

Доступ: publisher-indexed-editorial-text-read

Перевірено: V2 та V3 можуть збігатися за написанням; розрізняються функції в конструкції.

[https://apastyle.apa.org/style-grammar-guidelines/grammar/active-passive-voice](https://apastyle.apa.org/style-grammar-guidelines/grammar/active-passive-voice)

Доступ: direct-editorial-body-read

Перевірено: APA дозволяє обидва стани з різними комунікативними цілями; це конкретна редакційна система, не правило всіх журналів.

[https://ygdp.yale.edu/phenomena/needs-washed](https://ygdp.yale.edu/phenomena/needs-washed)

Доступ: direct-university-research-body-read

Перевірено: Needs + participle — опис регіональної варіативності; стандартні need + to be + V3 та need + -ing не відхиляються. Регіональний варіант не став основним навчальним ключем.

[https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/active-passive-voice](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/active-passive-voice)

Доступ: direct-editorial-body-read

Перевірено: Be/V3, get, passive infinitive та зв’язок допоміжних; учнівські коментарі й exercise banks не використовувалися як нормативне джерело чи готові вправи.

[https://developers.google.com/search/docs/fundamentals/creating-helpful-content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content)

Доступ: direct-editorial-body-read

Перевірено: Корисний самодостатній матеріал і якість для читача; без штучного word count, вигаданих авторів, дат чи ranking guarantees.
