# M62 Редакційні зміни трьох уроків

Редакція 1.0.0 охоплює Advanced Reported Speech, Relative Clauses та Defining Relative Clauses. Це зміна навчальних даних для чинних native-компонентів, без зміни дизайну, grader, курсів або окремих тестів. B2 review пояснює інтегровані reporting decisions; B1 overview — будову й ролі; B1 defining — вибір референта та підгрупи.

## Незалежний BEFORE

Свіжий source/DB/HTTP BEFORE має SHA-256 `c1d652a764102b5abc9b5dd8a8c9226cd79126302dce58a69e52aab7a9b3c55d`. Фактичні UK Page IDs: 283 / 202 / 31; category IDs: 3 / 34 / 34. Слоти: 2 / 7 / 7. English raw titles, slugs і локальні URLs збережено; title категорії з БД не замінюється вкладеним source title.

- [Advanced Reported Speech](http://gramlyze.loc/theory/reported-speech/advanced-reported-speech)
- [Relative Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/relative-clauses)
- [Defining Relative Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/defining-relative-clauses)

## Advanced Reported Speech

| BEFORE | Редакційне рішення | AFTER |
| --- | --- | --- |
| Українсько-англійський hero із короткими англійськими правилами без повних перекладів | Збережено тему й B2; розгорнуто зміст, не оголошуючи типові початкові правила помилковими | Український вступ: намір → модель → виконавець → час/місце; три короткі bilingual hero examples |
| Службовий box «B2 focus» про English Sentence Builder B2 anchor | Standalone scaffold замінено повним уроком; Page identity та course references не видалено | Old slot1 → forms-grid, null level і left збережено, технічний heading вилучено; OLD course/help presentation зберігається clone-only межею |
| Загальна згадка backshift, питань і вказівок | Не дублювати шість прийнятих M60/M61 великими таблицями | Самостійна навігація до цих шести уроків і поточного Advanced; B2 рішення на різних коротких мінідіалогах |
| Немає моделей reporting verbs за виконавцем | Нове авторське пояснення конкретних моделей, не взаємозамінної «групи» | Clause; promise/offer/refuse/agree + to; advise/remind/warn + object + to; admit/deny + -ing; suggest -ing/that; apologise for/insist on -ing |
| Немає межі змістового переказу | Нове пояснення | Переказ не вигадує намір, згоду, виконання чи впевненість; tried to persuade відрізняється від успішного persuaded |
| Немає сфери not | Нове видиме basic, не hidden detail | Didn't promise to ≠ promised not to; denied doing ≠ admitted not doing |
| Переказ простого факту без часу/місця | Новий самодостатній приклад двох точок оповіді | Понеділок у лабораторії: tomorrow/here; вівторок з іншого місця: that day/in the laboratory; перевірка лишається планом того самого вівторка |
| Немає point detail | Окреме змістовне поглиблення, не квота | Гіпотетичні had/would не перетворюються на встановлений минулий факт лише через said |

## Relative Clauses

| BEFORE | Редакційне рішення | AFTER |
| --- | --- | --- |
| Правильна базова ідея опису при іменнику та приклади teacher/film/café | Ідею збережено, фрагменти замінено незалежними повними авторськими ситуаціями | Межі clause і два присудки; головна частина та опис мають окрему перевірку ролей |
| Who/which/that/whose/where переважно за типом іменника | Уточнено межі, не відкинуто початкову карту | Роль у clause і тип defining/non-defining; whom/when/why компактно; whose не лише люди, who's не належність |
| «The man who he called» і правило «після who he не потрібний» | Конкретна джерельна помилка: оцінено уривок без ролей; object who дозволяє іншого підмета he | Два повні контексти: дядько сам телефонує; Андрій телефонує дядькові. He потрібний у другому. Справжні дублікати subject і object показано окремо |
| «The boy who very friendly» виправляється як уривок | Не лишати «правильну» відповідь без головного присудка | The boy who is very friendly helped us — «Хлопець, який дуже привітний, допоміг нам»; is і helped мають різні частини |
| Subject/object heading допускає змішування з роллю в головному реченні | Основний контраст залишено у visible basic | Who може бути object усередині опису при man, що є main subject; окреме detail зіставляє дві повні конструкції без вигаданого порядку подій |
| Where пояснено просто як місце | Роль уточнена | Hall where we rehearsed ≠ hall that we visited; місце дії та об’єкт visited |
| Object omission подано на незавершеному прикладі | Обмежено простою defining object-моделлю | Повна пара map that I showed you / map I showed you; subject I і адресат you лишаються; subject/whose/non-defining не видаляються |
| Немає узгодження та relative vs question | Нове необхідне basic | Plural volunteers who repair; головне Is…? не робить relative who прямим wh-питанням |
| Дев’ять navigation items | Освітні дані збережено | Ті самі labels/hrefs/order/current flags у clauses-and-linking-words; serializer додає provenance marker, тому raw body byte identity не заявляється |

## Defining Relative Clauses

| BEFORE | Редакційне рішення | AFTER |
| --- | --- | --- |
| Essential information як «без неї неясно» | Корисну ідею збережено, абсолют граматичної невидаляності не додано | Видалення може лишити граматичне речення, але зняти ознаку потрібного вибору |
| People who work on weekends — «не всі люди» | Не виводити існування решти групи з однієї defining clause | Явний склад: 20 пасажирів, допомогу попросили четверо; контраст справді задано |
| Опис може здаватися вибором лише одного предмета | Нове пояснення підгрупи/класу | Окремі ключі, група пасажирів і клас ілюстрованих інструкцій |
| Потенційний висновок про My sister | Контекст задає кількість, граматика не вгадує її | У Марка дві сестри з різними місцями роботи; опис обирає одну саме за цієї умови |
| Немає ком, але можливе перенесення на UK | Межі пояснено явно | Англійський defining опис не відокремлюємо; українські коми природні; не будь-яка сусідня кома належить опису |
| Which/who/where за типом іменника | Уточнено допустимі alternatives та ролі | Who/that і which/that допускаються; object omission у простій моделі; where vs photographed; whose + noun |
| Білінгви нібито швидше перемикаються між завданнями | Замінено нейтральною авторською ситуацією, без заяви, що факт спростовано | Ілюстровані інструкції на окремій полиці; жодної неперевіреної наукової тези як умови граматичної вправи |
| Which one? як майже автоматичний тест | Замість евристики — короткий алгоритм | Знання співрозмовника → потрібний референт/група → ролі → модель → пунктуація |
| Немає позиції прийменника | Коротке meaningful detail, не весь advanced курс | Кінцеве to не помилка; to whom у заданій парі; that не після винесеного прийменника; whose + noun окремо не заборонено |
| Дев’ять navigation items | Освітні дані збережено | Ті самі labels/hrefs/order/current flags у вихідній групі; без нового ancestry relative-clauses |

## Basic і point details

Усі основні правила, потрібні переклади, ролі та застереження видимі в basic. Details — по одному окремому змістовному поглибленню, не механічна квота:

| Detail key | Власник |
| --- | --- |
| advanced-reported-hypothetical | advanced-reported-decisions /sections/3 |
| relative-two-subject-positions | relative-structure /sections/2 |
| defining-preposition-position | defining-patterns /items/1 |

Кінцева структура — 23 blocks: 16 continuing і 7 appended. Advanced: 2 → 7, тобто чотири teaching/navigation та один practice; інші дві сторінки: 7 → 8, лише appended practice. Null levels усіх початкових box/navigation slots збережені; не підвищені механічно до B1/B2. Два нові Advanced blocks precision/summary мають left/B2, як вимагає чинний незмінений NativeRelease; старих right columns це не стосується. Body-type transitions та heading removal мають окремий reviewed allowlist, не маскуються як body-only edits.

## Практика й межі grader

Кожна сторінка має 2 selects, 2 contextual meaning choices, 2 manual/token inputs. Разом 18 own tasks; шість canonical inputs та чотири явно названі aliases — 10 accepted variants. Шість meaning choices показують український контекст одразу, а повний переклад англійського стимулу — після Check. Незалежні plain-text feedbacks містять повне EN, UK і конкретне пояснення, без HTML/entities у x-text.

Default unchanged matcher нормалізує коми, регістр, апострофи й інші знаки. M62 не використовує punctuation_sensitive і не заявляє перевірку defining/non-defining лише комами input. A/B English candidates із різним оформленням подано як stimuli; відповідями choice є різні українські пояснення, чи вибрано двох екскурсоводів із шести. Їх normalized values різні, тому engine не зараховує обидва як один ключ.

Відповіді, що належать до тієї самої простої defining object-моделі, приймаються лише там, де явно дозволені: box that/which/Ø; students who/that. В іншому input прямо задано who, аби перевірити потрібний she як іншого виконавця. Apologised/apologized дозволені явно. Складних декартових комбінацій aliases немає.

Довільний невідомий typo може отримати чинний generic fallback без повного авторського перекладу; content scope не розширює цей engine. Empty/partial/wrong/repeated wrong/edit correct/recheck/reset/manual/token/mixed/delete-return-rebuild перевіряються окремо. Ці конкретні редакторські fixtures не є універсальним лінгвістичним аналізатором.

Окремі widget questions рахуються окремо від 18 own tasks. Банки, actual IDs, strict case, hints і multiplicity фіксуються в незалежному bank audit/contract. Редакторська перевірка не підміняє actual UI 5/5.

## Походження й перевірка джерел

Початкова source-derived основа — теми, основні моделі, старі owner/level/navigation дані. Усі нові навчальні ситуації, контексти, діалоги, ролі й own tasks — самостійні. Вправи зовнішніх видавців не скопійовано. Зовнішній короткий приклад persuading не використано як доказ успішного переконання без окремого факту згоди.

### https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-reporting-verbs

Direct main editorial body read, lines 25–66; staff replies distinguished from learner comments.

Перевірене питання: Reporting verbs + infinitive / object + infinitive / -ing; no exercises copied. The publisher persuade example is not adopted without evidence of successful persuasion.

### https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-statements

Direct full main editorial body read, lines 24–115.

Перевірене питання: Backshift and no-backshift; unchanged participants/event and context-dependent time/place.

### https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/relative-clauses-defining-relative-clauses

Direct full main editorial body read, lines 23–69.

Перевірене питання: Defining reference, who/that, which/that, introductory place/time and simple object omission; roles checked independently, not a following-word heuristic.

### https://dictionary.cambridge.org/grammar/british-grammar/relative-pronouns

Exact direct open returned 403. Official publisher-indexed full editorial sections read at https://dictionary.cambridge.org/grammar/british-grammar/relative-pronouns%5D and https://dictionary.cambridge.org/us/grammar/british-grammar/relative-pronouns/. No direct redirect success claimed.

Перевірене питання: Who subject/object; which in defining clauses; whose beyond humans; object omission; preposition positions; where/when/why; subject/object not automatic main-clause role.

### https://dictionary.cambridge.org/grammar/british-grammar/relative-clauses-defining-and-non-defining

Exact direct open returned 403. Official publisher-indexed full main body read at https://dictionary.cambridge.org/grammar/british-grammar/non-defining-relative-clauses; matching article title, not a verified redirect.

Перевірене питання: Defined referent versus known referent; punctuation; repeated subject/object pronouns.

### https://dictionary.cambridge.org/grammar/british-grammar/suggest

Exact direct open returned 403; official publisher-indexed main body read at the same URL.

Перевірене питання: Suggest -ing / that-clause; no suggest someone to in the target recommendation pattern.

### https://dictionary.cambridge.org/dictionary/english/deny

Exact direct open returned 403; official publisher-indexed dictionary NOT TRUE and NOT ADMIT senses read at the same URL. Corpus examples excluded as normative evidence.

Перевірене питання: Deny doing is a denial, not a refusal to act or proof of actual innocence.

### https://dictionary.cambridge.org/dictionary/english/refuse

Exact direct open returned 403; publisher-indexed dictionary body read at https://dictionary.cambridge.org/dictionary/english/refuse?topic=rubbish-and-waste. Verb sense separated from waste noun.

Перевірене питання: Refuse to do: unwillingness / rejection, distinct from deny doing.

### https://dictionary.cambridge.org/dictionary/english/apologize

Exact direct open returned 403; publisher-indexed editorial dictionary body read at same URL; British spelling explicitly identified.

Перевірене питання: Apologise to person / for action; preserve actor and not after for.

### https://dictionary.cambridge.org/dictionary/english/persuade

Exact direct open returned 403; official publisher-indexed dictionary body read at same URL.

Перевірене питання: Successful persuasion versus tried to persuade; never invent agreement or performed action.

### https://developers.google.com/search/docs/fundamentals/creating-helpful-content

Direct full main body read, lines128–237, reported update2026-10-05.

Перевірене питання: Original learner-first content, transparent scope, no word quota / fake reviewer / ranking guarantee.

Уроки не містять word quota, прихованої SEO-копії, вигаданого рецензента, сертифікації чи ranking guarantees. Журнал розрізняє direct main body, publisher-indexed body та 403; learner comments не є нормативним джерелом.

## Preservation і межі приймання

M59 Advanced Passive досвід використано лише для технічної межі: короткий source anchor не дає дозволу знищити course reference або переписати course body. M62 додає standalone lesson через чинні native components та clone-only BEFORE preservation для виключених consumers; не копіює M59 навчальний текст, mapping чи metadata transitions.

Старі M60 1.0.0/1.0.1 і M61 artifacts не переписуються. Категорії, EN/PL siblings, course banks, saved tests, прогрес і standalone help не є цілями цього пакета. Описові історичні недоліки frozen provenance не виправляються заднім числом; усі нові M62 labels мають власний номер і scope.

Цей editorial artifact фіксує схвалений навчальний зміст і предметні рішення. Guarded apply, фінальний SSR/DOM, browser states, no-op, preservation audit та Git delivery мають власні фактичні receipts у звіті; редакційне схвалення не оголошує їх автоматично PASS.
