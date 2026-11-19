# M46–M49 · Practice Completion 1.0.1

Читабельна українська версія нового авторського пакета. Джерело істини — [frozen master](m46-m49-practice-completion.v1.0.1.json); це авторська документація, а не додаткова сторінка або новий renderer сайту.

База: `7d339c237060d76e9f7c7981ab71f07dfad3529d`. SHA-256 master: `f98fb2e19a8bd619802dcd2f3e350b19414f81a508c69a75a2de3147f3652b89`.

Цей документ відображає зафіксовані дані та попередню редакторську перевірку. Він не засвідчує завершення локального apply, повторного no-op чи браузерного приймання. Результати цих етапів оформлюються окремо.

Редакція 1.0.1 змінює тільки набір lookup keys у finite feedback maps: 661 → 315. Усі 24 умови, банки, canonical/accepted відповіді та наведені нижче переклади й пояснення залишилися точними копіями 1.0.0. Стару редакцію не перезаписано.

## Обсяг

12 чинних українських уроків, по 2 нові завдання складання речень (`source_index` 7 і 8). Збережено 72 попередні завдання з індексами 1–6. Пакет містить 24 канонічні відповіді, 50 явно авторських accepted entries загалом (24 канонічні + 26 додаткових варіантів), 169 окремих елементів банків і 315 finite feedback keys.

У кожному уроці BEFORE містить 6 власних select/choice завдань, 0 inputs і 0 rephrase. Цільова проєкція додає inputs 2, тобто дає 8 власних завдань; пов’язаний Sentence Builder не включений до цих чисел і не замінює нову практику.

Банки нижче відтворено в їхньому перемішаному порядку. Повна українська умова зберігається в штатному видимому полі `after`, банк — у `before`. Канонічні відповіді та feedback у цьому файлі потрібні для редакторської перевірки; вони не додаються до initial UI як підказки.

## Реєстр

| Урок | Page ID | Категорія | BEFORE selects / choices / inputs | Цільова проєкція inputs |
| --- | ---: | ---: | --- | ---: |
| [Zero Conditional](http://gramlyze.loc/theory/conditionals/zero-conditional) | 53 | 10 | 3 / 3 / 0 | 2 |
| [First Conditional](http://gramlyze.loc/theory/conditionals/first-conditional) | 44 | 10 | 3 / 3 / 0 | 2 |
| [Second Conditional](http://gramlyze.loc/theory/conditionals/second-conditional) | 48 | 10 | 3 / 3 / 0 | 2 |
| [Third Conditional](http://gramlyze.loc/theory/conditionals/third-conditional) | 49 | 10 | 2 / 4 / 0 | 2 |
| [Mixed Conditionals](http://gramlyze.loc/theory/conditionals/mixed-conditionals) | 47 | 10 | 2 / 4 / 0 | 2 |
| [Unless / Provided / As long as](http://gramlyze.loc/theory/conditionals/unless-provided-as-long-as) | 50 | 10 | 2 / 4 / 0 | 2 |
| [In case](http://gramlyze.loc/theory/conditionals/in-case) | 46 | 10 | 3 / 3 / 0 | 2 |
| [Wish / If only](http://gramlyze.loc/theory/conditionals/wish-if-only) | 51 | 10 | 3 / 3 / 0 | 2 |
| [Would rather](http://gramlyze.loc/theory/conditionals/would-rather) | 52 | 10 | 3 / 3 / 0 | 2 |
| [Had better](http://gramlyze.loc/theory/conditionals/had-better) | 45 | 10 | 2 / 4 / 0 | 2 |
| [Can / Could](http://gramlyze.loc/theory/modal-verbs/can-could) | 72 | 15 | 2 / 4 / 0 | 2 |
| [May / Might](http://gramlyze.loc/theory/modal-verbs/may-might) | 74 | 15 | 4 / 2 / 0 | 2 |

## 1. Zero Conditional

URL: [zero-conditional](http://gramlyze.loc/theory/conditionals/zero-conditional). Locale: `uk`. Page ID: 53; category ID: 10.

Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsZeroTheorySeeder`.

Practice UUID: `03b70723-7110-5194-bc15-29355a9fe57a`.

BEFORE: selects: 3; choices: 3; inputs: 0; rephrase: 0. Цільова проєкція: selects: 3; choices: 3; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне англійське речення з елементів банку або введи його вручну. Збережи зміст, час і заперечення українського речення. Доречний інший порядок частин, повні форми та скорочення приймаються.

### Завдання 7

ID: `pc-m46-m49-zero-conditional-habit-soup`.

**Повна видима українська умова (`after`):**

Це моя звичка, а не план на один день. Склади речення: «Якщо в мене є стиглі помідори, я готую суп».

**Перемішаний банк (`before`):**

`soup. / I / have / make / If / ripe tomatoes, / I`

**Канонічна відповідь (`answer`):**

`If I have ripe tomatoes, I make soup.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `If I have ripe tomatoes, I make soup.`
- `I make soup if I have ripe tomatoes.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: If I have ripe tomatoes, I make soup. — Якщо в мене є стиглі помідори, я готую суп. Обидві частини описують повторюваний зв’язок у теперішньому. Після початкової умови ставимо кому; коли результат перший, кома зазвичай не потрібна.

### Завдання 8

ID: `pc-m46-m49-zero-conditional-negative-dictionary`.

**Повна видима українська умова (`after`):**

Під час читання я завжди роблю так, коли слово незнайоме. Склади речення: «Якщо я не розумію якогось слова, я користуюся словником».

**Перемішаний банк (`before`):**

`a dictionary. / do not / I / use / a word, / If / understand / I`

**Канонічна відповідь (`answer`):**

`If I do not understand a word, I use a dictionary.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `If I do not understand a word, I use a dictionary.`
- `I use a dictionary if I do not understand a word.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: If I do not understand a word, I use a dictionary. — Якщо я не розумію якогось слова, я користуюся словником. Заперечення належить умові: do not understand. У результаті лишається стверджувальне use. Обидві частини описують звичну ситуацію.

## 2. First Conditional

URL: [first-conditional](http://gramlyze.loc/theory/conditionals/first-conditional). Locale: `uk`. Page ID: 44; category ID: 10.

Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsFirstTheorySeeder`.

Practice UUID: `e1d69f49-298e-56dc-808b-53131f1f2b6b`.

BEFORE: selects: 3; choices: 3; inputs: 0; rephrase: 0. Цільова проєкція: selects: 3; choices: 3; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне англійське речення з елементів банку або введи його вручну. Збережи зміст, час і заперечення українського речення. Доречний інший порядок частин, повні форми та скорочення приймаються.

### Завдання 7

ID: `pc-m46-m49-first-conditional-future-poster`.

**Повна видима українська умова (`after`):**

Плакат ще не готовий. Я планую надрукувати його завтра, якщо встигну завершити сьогодні ввечері. Склади речення: «Якщо я закінчу плакат сьогодні ввечері, я надрукую його завтра».

**Перемішаний банк (`before`):**

`tomorrow. / I / the poster / will print / If / tonight, / I / finish / it`

**Канонічна відповідь (`answer`):**

`If I finish the poster tonight, I will print it tomorrow.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `If I finish the poster tonight, I will print it tomorrow.`
- `I will print it tomorrow if I finish the poster tonight.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: If I finish the poster tonight, I will print it tomorrow. — Якщо я закінчу плакат сьогодні ввечері, я надрукую його завтра. Це можлива майбутня умова: після if потрібне finish, а майбутній результат передає will print. Сьогоднішній вечір і завтрашній друк збережено.

### Завдання 8

ID: `pc-m46-m49-first-conditional-question-guests`.

**Повна видима українська умова (`after`):**

Завтра ти готуєш вечерю для наших гостей. Я ще не знаю, чи вони їдять м’ясо, і питаю про твій вибір страви. Склади питання: «Що ти приготуєш завтра, якщо наші гості не їдять м’яса?»

**Перемішаний банк (`before`):**

`our guests / eat meat? / tomorrow / do not / What / you / if / will / cook`

**Канонічна відповідь (`answer`):**

`What will you cook tomorrow if our guests do not eat meat?`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `What will you cook tomorrow if our guests do not eat meat?`
- `If our guests do not eat meat, what will you cook tomorrow?`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: What will you cook tomorrow if our guests do not eat meat? — Що ти приготуєш завтра, якщо наші гості не їдять м’яса? У прямому питанні will стоїть перед you. Умовна частина має звичайний порядок our guests do not eat; вона не стає окремим питанням.

## 3. Second Conditional

URL: [second-conditional](http://gramlyze.loc/theory/conditionals/second-conditional). Locale: `uk`. Page ID: 48; category ID: 10.

Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsSecondTheorySeeder`.

Practice UUID: `7640dc2b-5d95-5ea5-bccb-3617854df98e`.

BEFORE: selects: 3; choices: 3; inputs: 0; rephrase: 0. Цільова проєкція: selects: 3; choices: 3; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне англійське речення з елементів банку або введи його вручну. Збережи зміст, час і заперечення українського речення. Доречний інший порядок частин, повні форми та скорочення приймаються.

### Завдання 7

ID: `pc-m46-m49-second-conditional-imaginary-sign-language`.

**Повна видима українська умова (`after`):**

Зараз я не знаю жестової мови й не розумію відео без озвучення. Уяви іншу ситуацію. Склади речення: «Якби я знав жестову мову, я б розумів це відео».

**Перемішаний банк (`before`):**

`would understand / I / this video. / If / sign language, / I / knew`

**Канонічна відповідь (`answer`):**

`If I knew sign language, I would understand this video.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `If I knew sign language, I would understand this video.`
- `I would understand this video if I knew sign language.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: If I knew sign language, I would understand this video. — Якби я знав жестову мову, я б розумів це відео. Knew тут описує уявні знання зараз, а не минуле навчання. Уявний теперішній результат — would understand.

### Завдання 8

ID: `pc-m46-m49-second-conditional-advice-estimate`.

**Повна видима українська умова (`after`):**

Тобі назвали лише приблизну вартість ремонту. Я раджу спершу отримати суму письмово. Склади пораду: «На твоєму місці я б попросив письмовий кошторис». Використай стандартну форму уявної умови з банку.

**Перемішаний банк (`before`):**

`a written estimate. / I / were / ask for / If / would / you, / I`

**Канонічна відповідь (`answer`):**

`If I were you, I would ask for a written estimate.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `If I were you, I would ask for a written estimate.`
- `I would ask for a written estimate if I were you.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: If I were you, I would ask for a written estimate. — На твоєму місці я б попросив письмовий кошторис. У стандартній навчальній формулі поради використовуємо If I were you. Після would потрібна базова форма ask, без to.

## 4. Third Conditional

URL: [third-conditional](http://gramlyze.loc/theory/conditionals/third-conditional). Locale: `uk`. Page ID: 49; category ID: 10.

Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsThirdTheorySeeder`.

Practice UUID: `386140a6-2c9c-5c1a-9a30-35a089f1ee3c`.

BEFORE: selects: 2; choices: 4; inputs: 0; rephrase: 0. Цільова проєкція: selects: 2; choices: 4; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне англійське речення з елементів банку або введи його вручну. Збережи зміст, час і заперечення українського речення. Доречний інший порядок частин, повні форми та скорочення приймаються.

### Завдання 7

ID: `pc-m46-m49-third-conditional-past-cake`.

**Повна видима українська умова (`after`):**

Учора ми вийняли гарячий пиріг із форми, і він розламався. Тепер уявляємо інший перебіг саме цього випадку. Склади речення: «Якби ми дочекалися, поки пиріг охолоне, він би не розламався».

**Перемішаний банк (`before`):**

`have broken. / it / for the cake / would not / If / to cool, / we / had waited`

**Канонічна відповідь (`answer`):**

`If we had waited for the cake to cool, it would not have broken.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `If we had waited for the cake to cool, it would not have broken.`
- `It would not have broken if we had waited for the cake to cool.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: If we had waited for the cake to cool, it would not have broken. — Якби ми дочекалися, поки пиріг охолоне, він би не розламався. І умова, і результат належать завершеному минулому. Had waited уявно змінює нашу дію, а would not have broken називає результат, якого вдалося б уникнути.

### Завдання 8

ID: `pc-m46-m49-third-conditional-past-question-guide`.

**Повна видима українська умова (`after`):**

Учора під час екскурсії ми не попросили про зупинку біля озера, і гід там не зупинився. Тепер питаємо про інший можливий перебіг тієї події. Склади питання: «Чи зупинився б учора гід біля озера, якби ми попросили?»

**Перемішаний банк (`before`):**

`at the lake / we / have stopped / yesterday / Would / had asked? / the guide / if`

**Канонічна відповідь (`answer`):**

`Would the guide have stopped at the lake yesterday if we had asked?`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `Would the guide have stopped at the lake yesterday if we had asked?`
- `If we had asked, would the guide have stopped at the lake yesterday?`
- `Would the guide have stopped yesterday at the lake if we had asked?`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: Would the guide have stopped at the lake yesterday if we had asked? — Чи зупинився б учора гід біля озера, якби ми попросили? Would стоїть перед підметом the guide, а have — після нього. Had asked називає уявну минулу умову; питання не стверджує, якою була б відповідь гіда.

## 5. Mixed Conditionals

URL: [mixed-conditionals](http://gramlyze.loc/theory/conditionals/mixed-conditionals). Locale: `uk`. Page ID: 47; category ID: 10.

Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsMixedTheorySeeder`.

Practice UUID: `ddee7c10-9260-54d8-8b38-5204ad9f77a3`.

BEFORE: selects: 2; choices: 4; inputs: 0; rephrase: 0. Цільова проєкція: selects: 2; choices: 4; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне англійське речення з елементів банку або введи його вручну. Збережи зміст, час і заперечення українського речення. Доречний інший порядок частин, повні форми та скорочення приймаються.

### Завдання 7

ID: `pc-m46-m49-mixed-conditionals-past-trees-now-shade`.

**Повна видима українська умова (`after`):**

Десять років тому ми не посадили дерева в саду; зараз на цьому місці немає затінку. Склади речення про інший стан зараз: «Якби ми посадили дерева десять років тому, то зараз були б у затінку».

**Перемішаний банк (`before`):**

`in the shade / we / ten years ago, / would be / If / now. / had planted / trees / we`

**Канонічна відповідь (`answer`):**

`If we had planted trees ten years ago, we would be in the shade now.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `If we had planted trees ten years ago, we would be in the shade now.`
- `We would be in the shade now if we had planted trees ten years ago.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: If we had planted trees ten years ago, we would be in the shade now. — Якби ми посадили дерева десять років тому, то зараз були б у затінку. Had planted змінює минулу дію, а would be описує стан зараз. Це різні часові плани, а не два результати в минулому.

### Завдання 8

ID: `pc-m46-m49-mixed-conditionals-stable-taste-past-curry`.

**Повна видима українська умова (`after`):**

Я загалом не люблю гостру їжу й не любив її вчора, тому вчора ввечері не скуштував карі. Уяви інший сталий смак. Склади речення: «Якби я любив гостру їжу, то скуштував би карі вчора ввечері».

**Перемішаний банк (`before`):**

`the curry / I / last night. / liked / would have tried / If / spicy food, / I`

**Канонічна відповідь (`answer`):**

`If I liked spicy food, I would have tried the curry last night.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `If I liked spicy food, I would have tried the curry last night.`
- `I would have tried the curry last night if I liked spicy food.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: If I liked spicy food, I would have tried the curry last night. — Якби я любив гостру їжу, то скуштував би карі вчора ввечері. Liked описує уявний сталий смак, який мав би бути іншим і під час учорашньої вечері. Would have tried називає вже минулу дію; сьогоднішня зміна смаку не переписує вчорашню подію.

## 6. Unless / Provided / As long as

URL: [unless-provided-as-long-as](http://gramlyze.loc/theory/conditionals/unless-provided-as-long-as). Locale: `uk`. Page ID: 50; category ID: 10.

Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsUnlessProvidedAsLongAsTheorySeeder`.

Practice UUID: `2cf832f6-8b24-53b5-9c62-9552feaae1fb`.

BEFORE: selects: 2; choices: 4; inputs: 0; rephrase: 0. Цільова проєкція: selects: 2; choices: 4; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне англійське речення з елементів банку або введи його вручну. Збережи зміст, час і заперечення українського речення. Доречний інший порядок частин, повні форми та скорочення приймаються.

### Завдання 7

ID: `pc-m46-m49-unless-provided-as-long-as-exception-library`.

**Повна видима українська умова (`after`):**

Ми домовилися зустрітися завтра в бібліотеці. Виняток із плану — якщо вона зачиниться рано. Склади речення зі сполучником із банку: «Ми зустрінемося завтра в бібліотеці, якщо вона не зачиниться рано».

**Перемішаний банк (`before`):**

`tomorrow / it / We / unless / at the library / closes early. / will meet`

**Канонічна відповідь (`answer`):**

`We will meet at the library tomorrow unless it closes early.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `We will meet at the library tomorrow unless it closes early.`
- `Unless it closes early, we will meet at the library tomorrow.`
- `We will meet tomorrow at the library unless it closes early.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: We will meet at the library tomorrow unless it closes early. — Ми зустрінемося завтра в бібліотеці, якщо вона не зачиниться рано. Unless називає виняток: раннє зачинення може змінити план. Після unless тут потрібне стверджувальне closes; додаткове not перевернуло б умову.

### Завдання 8

ID: `pc-m46-m49-unless-provided-as-long-as-permission-photo`.

**Повна видима українська умова (`after`):**

Організатор дає тобі дозвіл опублікувати спільне фото лише за згоди всіх людей на ньому. Склади речення зі сполучником із банку: «Ти можеш опублікувати фото за умови, що всі погодяться».

**Перемішаний банк (`before`):**

`everyone / the photo / You / provided that / agrees. / can publish`

**Канонічна відповідь (`answer`):**

`You can publish the photo provided that everyone agrees.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `You can publish the photo provided that everyone agrees.`
- `You can publish the photo provided everyone agrees.`
- `Provided that everyone agrees, you can publish the photo.`
- `Provided everyone agrees, you can publish the photo.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно: You can publish the photo provided that everyone agrees. — Ти можеш опублікувати фото за умови, що всі погодяться. Can зберігає дозвіл, а provided that називає його умову. У цій конструкції that можна опустити. Це вимога згоди, не повідомлення про тривалість.

## 7. In case

URL: [in-case](http://gramlyze.loc/theory/conditionals/in-case). Locale: `uk`. Page ID: 46; category ID: 10.

Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsInCaseTheorySeeder`.

Practice UUID: `06f78006-775f-551c-994c-7539cadc0220`.

BEFORE: selects: 3; choices: 3; inputs: 0; rephrase: 0. Цільова проєкція: selects: 3; choices: 3; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне речення зі слів банку або введи його вручну. Збережи виконавця, час і заперечення; доречні скорочення також приймаються.

### Завдання 7

ID: `pc-m46-m49-in-case-7`.

**Повна видима українська умова (`after`):**

Перед поїздкою я ще не знаю, чи знадобиться таксі. Підготую телефон завчасно. Склади: «Я заряджу телефон на випадок, якщо мені потрібно буде викликати таксі». Почни з головної дії, а можливу потребу назви після неї.

**Перемішаний банк (`before`):**

`need to call / I / my phone / in case / a taxi. / will / I / charge`

**Канонічна відповідь (`answer`):**

`I will charge my phone in case I need to call a taxi.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `I will charge my phone in case I need to call a taxi.`
- `I'll charge my phone in case I need to call a taxi.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. I will charge my phone in case I need to call a taxi. — Я заряджу телефон на випадок, якщо мені потрібно буде викликати таксі. Заряджання — запобіжна дія до можливої потреби. У звичайній майбутній частині після in case потрібне need, не will need.

### Завдання 8

ID: `pc-m46-m49-in-case-8`.

**Повна видима українська умова (`after`):**

Учора вона зберегла чек, ще не знаючи, чи доведеться повертати взуття. Склади розповідь про минулу пересторогу: «Вона зберегла чек на випадок, якщо їй потрібно буде повернути взуття». Почни зі збереження чека; обидві частини подай з тодішньої точки зору.

**Перемішаний банк (`before`):**

`the shoes. / She / in case / the receipt / to return / needed / saved / she`

**Канонічна відповідь (`answer`):**

`She saved the receipt in case she needed to return the shoes.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `She saved the receipt in case she needed to return the shoes.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. She saved the receipt in case she needed to return the shoes. — Вона зберегла чек на випадок, якщо їй потрібно буде повернути взуття. Saved називає зроблену тоді дію, а needed — можливу потребу з минулої точки зору. Речення не стверджує, що взуття справді повернули.

## 8. Wish / If only

URL: [wish-if-only](http://gramlyze.loc/theory/conditionals/wish-if-only). Locale: `uk`. Page ID: 51; category ID: 10.

Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsWishIfOnlyTheorySeeder`.

Practice UUID: `951b4b66-dc6f-5e1d-b833-f2ab4afc0431`.

BEFORE: selects: 3; choices: 3; inputs: 0; rephrase: 0. Цільова проєкція: selects: 3; choices: 3; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне речення зі слів банку або введи його вручну. Збережи виконавця, час і заперечення; доречні скорочення також приймаються.

### Завдання 7

ID: `pc-m46-m49-wish-if-only-7`.

**Повна видима українська умова (`after`):**

У нашій кухні зараз немає вікна; я хотів би іншої теперішньої ситуації. Склади: «Шкода, що в нашій кухні немає вікна». Бажання висловлюю я, а вікно хотілося б мати саме в кухні.

**Перемішаний банк (`before`):**

`a window. / wish / had / I / our kitchen`

**Канонічна відповідь (`answer`):**

`I wish our kitchen had a window.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `I wish our kitchen had a window.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. I wish our kitchen had a window. — Шкода, що в нашій кухні немає вікна. Had після wish показує бажаний інший стан зараз, не минуле володіння. Підмет бажаної ситуації — our kitchen.

### Завдання 8

ID: `pc-m46-m49-wish-if-only-8`.

**Повна видима українська умова (`after`):**

Учора я закрив документ без збереження й утратив зміни. Тепер шкодую про невиконану дію. Склади: «От якби я зберіг документ перед тим, як закрити його!» Документ закрив і мав зберегти саме я.

**Перемішаний банк (`before`):**

`the document / I / before / had / closing it! / If only / saved`

**Канонічна відповідь (`answer`):**

`If only I had saved the document before closing it!`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `If only I had saved the document before closing it!`
- `If only I'd saved the document before closing it!`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. If only I had saved the document before closing it! — От якби я зберіг документ перед тим, як закрити його! Реальне збереження не відбулося. Had saved після if only створює бажану альтернативу завершеному минулому; closing стосується дії того самого I.

## 9. Would rather

URL: [would-rather](http://gramlyze.loc/theory/conditionals/would-rather). Locale: `uk`. Page ID: 52; category ID: 10.

Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsWouldRatherTheorySeeder`.

Practice UUID: `6e5976e3-22db-59b1-955e-16c0033052eb`.

BEFORE: selects: 3; choices: 3; inputs: 0; rephrase: 0. Цільова проєкція: selects: 3; choices: 3; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне речення зі слів банку або введи його вручну. Збережи виконавця, час і заперечення; доречні скорочення також приймаються.

### Завдання 7

ID: `pc-m46-m49-would-rather-7`.

**Повна видима українська умова (`after`):**

Цей стілець можна полагодити, і мені більше подобається відремонтувати його самому, ніж купувати заміну. Склади: «Я б краще відремонтував цей стілець, ніж купував новий». В обох діях виконавець — я.

**Перемішаний банк (`before`):**

`buy / I / a new one. / this chair / than / would / repair / rather`

**Канонічна відповідь (`answer`):**

`I would rather repair this chair than buy a new one.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `I would rather repair this chair than buy a new one.`
- `I'd rather repair this chair than buy a new one.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. I would rather repair this chair than buy a new one. — Я б краще відремонтував цей стілець, ніж купував новий. Власні дії repair і buy мають паралельні базові форми. One замінює chair; 'd rather розгортається як would rather, не had rather.

### Завдання 8

ID: `pc-m46-m49-would-rather-8`.

**Повна видима українська умова (`after`):**

Ми готуємо оселю до сьогоднішнього вечора й хочемо, щоб гості самі залишили своє взуття біля дверей. Склади: «Ми б воліли, щоб відвідувачі сьогодні ввечері залишили взуття біля дверей». Використай вивчену модель з минулою формою для бажаної дії іншого виконавця; час постав наприкінці.

**Перемішаний банк (`before`):**

`tonight. / the visitors / rather / left / We / by the door / would / their shoes`

**Канонічна відповідь (`answer`):**

`We would rather the visitors left their shoes by the door tonight.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `We would rather the visitors left their shoes by the door tonight.`
- `We'd rather the visitors left their shoes by the door tonight.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. We would rather the visitors left their shoes by the door tonight. — Ми б воліли, щоб відвідувачі сьогодні ввечері залишили взуття біля дверей. We висловлює перевагу, а the visitors виконує дію. Left — past-форма моделі для іншого виконавця, хоча подія очікується сьогодні ввечері.

## 10. Had better

URL: [had-better](http://gramlyze.loc/theory/conditionals/had-better). Locale: `uk`. Page ID: 45; category ID: 10.

Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsHadBetterTheorySeeder`.

Practice UUID: `b86091c3-5bdc-55bb-a5c9-5db128088d97`.

BEFORE: selects: 2; choices: 4; inputs: 0; rephrase: 0. Цільова проєкція: selects: 2; choices: 4; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне речення зі слів банку або введи його вручну. Збережи виконавця, час і заперечення; доречні скорочення також приймаються.

### Завдання 7

ID: `pc-m46-m49-had-better-7`.

**Повна видима українська умова (`after`):**

Завтра мені треба дуже рано прокинутися, інакше пропущу поїзд. Раджу самому собі підготуватися сьогодні. Склади: «Мені краще поставити будильник на завтра».

**Перемішаний банк (`before`):**

`an alarm / I / for tomorrow. / set / better / had`

**Канонічна відповідь (`answer`):**

`I had better set an alarm for tomorrow.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `I had better set an alarm for tomorrow.`
- `I'd better set an alarm for tomorrow.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. I had better set an alarm for tomorrow. — Мені краще поставити будильник на завтра. Це ситуативна порада через ризик проспати. Після had better потрібне set без to; 'd better означає had better і не повідомляє про минулу дію.

### Завдання 8

ID: `pc-m46-m49-had-better-8`.

**Повна видима українська умова (`after`):**

Фарба на цій лавці ще не висохла. Попереджаєш друга, що він може забруднити одяг. Склади: «Тобі краще не сідати на цю лавку». Сідати не радять саме другові.

**Перемішаний банк (`before`):**

`this bench. / better / You / on / not / sit / had`

**Канонічна відповідь (`answer`):**

`You had better not sit on this bench.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `You had better not sit on this bench.`
- `You'd better not sit on this bench.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. You had better not sit on this bench. — Тобі краще не сідати на цю лавку. Not стоїть після better і заперечує sit. Порада стосується конкретного ризику зараз, а не особистої переваги щодо місця.

## 11. Can / Could

URL: [can-could](http://gramlyze.loc/theory/modal-verbs/can-could). Locale: `uk`. Page ID: 72; category ID: 15.

Identity: `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsCanCouldTheorySeeder`.

Practice UUID: `240cf788-b9ba-547d-8b31-1672a489235a`.

BEFORE: selects: 2; choices: 4; inputs: 0; rephrase: 0. Цільова проєкція: selects: 2; choices: 4; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне речення зі слів банку або введи його вручну. Збережи виконавця, час і заперечення; доречні скорочення також приймаються.

### Завдання 7

ID: `pc-m46-m49-can-could-7`.

**Повна видима українська умова (`after`):**

Мій маленький брат ще не навчився ходити. Склади коротке повідомлення про його теперішню нездатність: «Мій маленький брат ще не вміє ходити». Збережи заперечення.

**Перемішаний банк (`before`):**

`walk / My little brother / yet. / cannot`

**Канонічна відповідь (`answer`):**

`My little brother cannot walk yet.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `My little brother cannot walk yet.`
- `My little brother can't walk yet.`
- `My little brother can not walk yet.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. My little brother cannot walk yet. — Мій маленький брат ще не вміє ходити. Cannot / can't заперечує теперішню здатність. Після нього потрібне walk без to; yet зберігає значення «ще». Звичайну повну форму cannot пишемо разом.

### Завдання 8

ID: `pc-m46-m49-can-could-8`.

**Повна видима українська умова (`after`):**

За столом ти просиш сусіда передати тобі сіль. Передавати має сусід, не ти. Склади зі слів банку: «Не міг би ти передати мені сіль?»

**Перемішаний банк (`before`):**

`me / the salt? / Could / pass / you`

**Канонічна відповідь (`answer`):**

`Could you pass me the salt?`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `Could you pass me the salt?`
- `Can you pass me the salt?`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. Could you pass me the salt? — Не міг би ти передати мені сіль? Could you …? просить співрозмовника виконати дію; me називає одержувача. Can you …? також природне прохання з цими ролями, тому приймається вручну.

## 12. May / Might

URL: [may-might](http://gramlyze.loc/theory/modal-verbs/may-might). Locale: `uk`. Page ID: 74; category ID: 15.

Identity: `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsMayMightTheorySeeder`.

Practice UUID: `83055bed-7a20-579c-9bac-6824d4df325e`.

BEFORE: selects: 4; choices: 2; inputs: 0; rephrase: 0. Цільова проєкція: selects: 4; choices: 2; inputs: 2; rephrase: 0.

Заголовок групи: **Побудуй речення**.

Склади кожне речення зі слів банку або введи його вручну. Збережи виконавця, час і заперечення; доречні скорочення також приймаються.

### Завдання 7

ID: `pc-m46-m49-may-might-7`.

**Повна видима українська умова (`after`):**

Ми шукаємо ключі й не знаємо, де вони зараз. Кухня — лише можливе місце. Склади припущення зі слів банку: «Ключі, можливо, на кухні». Не перетворюй припущення на певний факт.

**Перемішаний банк (`before`):**

`in the kitchen. / might / The keys / be`

**Канонічна відповідь (`answer`):**

`The keys might be in the kitchen.`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `The keys might be in the kitchen.`
- `The keys may be in the kitchen.`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. The keys might be in the kitchen. — Ключі, можливо, на кухні. Might be передає можливе місце зараз. May be також відповідає цьому контексту: заданої різниці у впевненості немає, а фіксованих відсотків не встановлюємо.

### Завдання 8

ID: `pc-m46-m49-may-might-8`.

**Повна видима українська умова (`after`):**

Ти з друзями хочеш зробити одне фото в музеї й питаєш працівницю про дозвіл для всієї вашої групи. Фотографуватимете ви самі. Склади зі слів банку: «Можна нам зробити тут фото?»

**Перемішаний банк (`before`):**

`a photo / we / here? / May / take`

**Канонічна відповідь (`answer`):**

`May we take a photo here?`

**Явно прийнятні відповіді (`accepted`, включно з canonical):**

- `May we take a photo here?`

**Feedback для canonical — повний переклад і пояснення:**

Правильно. May we take a photo here? — Можна нам зробити тут фото? May we …? просить дозволу для нас. Працівницю не просять фотографувати; may стоїть перед we, а take має базову форму.

## Межі відтворення

Це читабельна добірка canonical feedback для всіх 24 вправ. Усі 315 ключів feedback збережені в master; їх не продубльовано тут. `accepted` — скінченний редакторський перелік, а не вичерпний перелік усіх рядків, які технічно нормалізує чинний рушій.

Пояснення збереження старих завдань, семантичної новизни, передфіксаційних рішень та відомих меж рушія наведені в [редакторській записці](m46-m49-practice-completion-editorial.v1.0.1.md).
