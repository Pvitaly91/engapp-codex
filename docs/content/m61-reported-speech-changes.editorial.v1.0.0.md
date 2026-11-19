# M61 редакторський аудит 1.0.0

## Межі та незалежний BEFORE

Оновлено навчальні дані рівно трьох UK theory owners: Backshift of Tenses (Page 141), Time and Place Changes (145), Say / Tell / Ask (144), category 3. Base `d60a288565f841dceefc16ac6ee10fa5c8c55395`. Незалежний свіжий source/DB/HTTP BEFORE має SHA `ba414b3857d4a6d430f7037a62a9d50b8fac589be8bea30eaeeeed99df71059d`. Category «Непряма мова», її learning description, M60 та інші сторінки залишаються read-only.

У кожного owner було шість slots: hero/header/B1 з display B1-B2, чотири HTML box slots left,left,right,right з omitted/null level і navigation/footer/null. Погоджений finite map змінює лише 12 box types та прибирає відповідні старі headings; null levels і columns не підвищуються. Hero, navigation, UUID, index/order та anchors зберігає чинний deterministic serializer. Додано лише один final practice-set/left/B1 на owner; додаткових teaching slots немає.

## Семантичний OLD і AFTER map

| Owner | Slot 1 | Slot 2 | Slot 3 | Slot 4 |
| --- | --- | --- | --- | --- |
| Backshift | box «Найтиповіші зміни» → comparison-table | box «Коли backshift є типовим, а коли ні» → usage-panels | box → mistakes-grid, right/null | box → summary-list, right/null |
| Time and Place | box «Найуживаніші зміни» → comparison-table | box «Коли зміни справді потрібні» → usage-panels | box → mistakes-grid, right/null | box → summary-list, right/null |
| Say / Tell / Ask | box «Основна різниця» → forms-grid | box «Приклади вживання» → comparison-table | box → mistakes-grid, right/null | box → summary-list, right/null |

Backshift зберігає корисне старе застереження проти механічного зсуву. У таблиці прямі й непрямі версії перекладено; стандартний перехід явно відділено від допустимого вибору перспективи. Минула форма не оголошує факт неправдивим, а will після said не оголошено універсальною помилкою. Збережено aspect, пасив і not; Past Perfect та Past Perfect Continuous далі не зсуваються. Can як уміння і дозвіл, may як можливість і дозвіл, must як вимога і висновок розділені. Must not не ототожнюється з did not have to. З had to не робиться хибне загальне твердження, ніби воно завжди лише обов’язок.

Time and Place зберігає правильне старе правило про точку відліку, але виправляє механічне «переказ наступного дня → обов’язково the next day». У фіксованому навчальному календарі понеділок 1 червня 2026 року, вівторок 2 червня й п’ятниця 5 червня дають одну й ту саму дату події за різних способів називання. Для earlier/before використано суботу 30 травня та неділю 31 травня; in two days / two days later від понеділка вказує на середу 3 червня. Слово here залежить від фактичної кімнати, this — від конкретного показуваного предмета, а не лише від зміни приміщення. That-сполучник і that-вказівне слово мають різні ролі; that that допустиме. That day не заборонено для same-day report з обраною минулою перспективою.

Say / Tell / Ask замінює хибне узагальнення «tell не може вводити wh» на розмежування інформування й запиту: told someone where — повідомив місце; asked someone where — запитав про нього. Say має додаток-зміст, а перед адресатом — to; у tell-моделі повідомлення адресат стоїть без to. Ask to та ask someone to мають різних виконавців; ask for не застосовується перед будь-яким іменником. Сталі tell the truth/a lie/a story не підпадають під загальне «tell завжди потребує людини». Ask не оголошено завжди ввічливим, tell — завжди грубим. Прохання й вказівка не доводять виконання; didn't tell/ask не дорівнює told/asked not to.

## Basic і змістовне поглиблення

Рішення явне, семантичне, без квоти й runtime word threshold. Три details мають окреме пояснення, а короткі необхідні застереження та переклади залишені visible basic.

- `backshift-modal-meaning` → `backshift-use/sections/2`: may як можливість/дозвіл, must як вимога/логічний висновок із різними контрольованими прикладами.
- `time-place-motion-perspective` → `time-place-context/sections/2`: come/go з явно названим первинним адресатом і фактичним пунктом призначення, без автоматичної заміни.
- `say-tell-ask-utterance-intent` → `say-tell-ask-patterns/items/2`: Could you tell me… як прохання всієї репліки та формальне ask that як інша допустима модель.

## Практика і plain feedback

18 own tasks мають по 2/2/2 на owner: 36 коротких options, шість full manual/token inputs. У контрольованих умовах названі початкові учасники, новий оповідач, потрібна часова/календарна перспектива й конкретні займенникові referents. Учасників не встановлено лише за гендером імені. Якщо завдання вимагає стандартний backshift, два that або задану повну модель, це прямо сказано; граматичні позацільові варіанти не оголошуються універсально неправильними.

19 accepted input variants = шість canonical + 13 aliases. Дозволено конкретні that/no-that, had not/hadn't, the day before/the previous day, the next/the following day, if/whether та she'd = she had там, де умова це прямо передбачає. Чотири she'd aliases Time and Place зберігають ту саму неділю й той самий completed receipt. Точні curly apostrophe feedback mappings і варіанти без terminal punctuation є даними, не новим grader.

48 різних пояснень від початку plain text: повне англійське речення, український переклад і коротке пояснення. Вони не містять HTML, entities або декоративних обгорток; зміни x-text на x-html чи глобального stripper немає. Підтримані HTML labels, шість English meaning prompts і приклади в native fields залишені окремо. У meaning choices переклад, який розкриває відповідь, показується після перевірки; український контекст і критерій видимі відразу.

Actual unchanged shipped factory suite дав 22/22 PASS на точній private v2 SHA `fdf9519ae8bfe7723d80ae498085222c9e900fc411c5204d105da1bcd2e345f9`; private receipt SHA `6690ca9c4090e251d52c86338eefb96df87be91609d661229a6afdf20b429c95`. Перевірено 36 options, шість manual і шість token tasks, усі 19 accepted variants, curly apostrophes, exact-case/punctuation/multiplicity, mixed edit, empty/partial/wrong/retry/correct/recheck/reset та незалежні бали, network calls 0. Початковий приватний запуск виявив лише double-escaped expected namespace і lowercase helper locator, що міняв місцями The/the; виправлено тестовий helper, не app/data/engine. Offline gate не замінює DB apply, браузер, реальний widget, no-JS чи print.

Окремий контракт `docs/content/theory-sentence-builders/m61-reported-speech-changes.v1.json`, SHA `cfc3290513297530d6d4848b844e7bf1614023e3fbec808cacac4570978f95cf`, містить фактичні 1/1/1 thematic Polyglot banks, type 4 / B1. Main погодив 15 reused questions, 0 new questions і 0 new links. Own inputs не замінюють п’ять widget questions. Остаточні UUID/body bindings належать чинному serializer; live 5/5 і регресії фіксуються окремими actual receipts.

## Журнал первинних джерел

### https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-statements

Access: Author direct open: full main editorial body read. Signed staff replies and learner comments distinguished separately.

Використання: Usual backshift, Past Perfect unchanged, current relevance, pronouns and context-dependent time/place. No publisher exercise copied.

### https://dictionary.cambridge.org/grammar/british-grammar/reported-speech-indirect-speech

Access: Exact direct request returned 403. Full official publisher-indexed body read at https://dictionary.cambridge.org/us/grammar/british-grammar/reported-speech-indirect-speech; same requested URL also returned an indexed editorial body.

Використання: Tense/aspect/modal perspective; unchanged perfect forms; current/future relevance; no automatic adverb or demonstrative substitution.

### https://dictionary.cambridge.org/grammar/british-grammar/say-and-tell

Access: Exact direct request returned 403. Full official publisher-indexed English grammar body read at https://dictionary.cambridge.org/uk/grammar/british-grammar/say-and-tell, title Say or tell?. No direct redirect success claimed.

Використання: Say content versus recipient with to; tell recipient in message models, tell + wh for information, truth/lie/story exceptions.

### https://dictionary.cambridge.org/grammar/british-grammar/reported-speech-reporting-verbs

Access: Exact direct request returned 403. Official publisher-indexed body returned at this exact URL, with article title Reported speech; fully read. This is indexed access, not a verified redirect or direct success.

Використання: Reporting clauses, statement/question/command intent, recipient patterns and reported punctuation.

### https://dictionary.cambridge.org/grammar/british-grammar/ask-and-ask-for

Access: Exact direct request returned 403. Full official publisher-indexed body read at the same URL.

Використання: Ask a question; ask to versus ask someone to; ask someone for a thing. Actor and object positions.

### https://dictionary.cambridge.org/grammar/british-grammar/word-patterns-ask-for

Access: Exact direct request returned 403. Full short official publisher-indexed body read at the same URL.

Використання: Ask for something to be given; named recipient before for. Not for before every noun.

### https://developers.google.com/search/docs/fundamentals/creating-helpful-content

Access: Direct official main article read in full, including final guidance. Page reports last update 2026-10-05 UTC.

Використання: Original useful learner-first explanation with complete task purpose; no preferred word count, false authorship or ranking claims.

### https://dictionary.cambridge.org/grammar/british-grammar/must

Access: Additional official publisher-indexed main grammar body read; no direct-success claim.

Використання: Obligation/deduction and prohibition versus no necessity. No blanket claim that every had to is deontic.

Усі п’ять точних Cambridge direct requests повернули 403. Повні publisher-indexed editorial bodies прочитані окремо; регіональні /us та /uk URL наведені фактично, без вигаданого direct redirect success. Для requested reporting-verbs URL індексований publisher body мав заголовок Reported speech; це не підміна direct access. British Council і Google прочитані напряму. Learner comments не є нормативним джерелом; signed staff replies відрізнені від main body й не підміняють його. Навчальні приклади та завдання авторські, publisher exercises не скопійовані. Немає word-count quota, вигаданого авторства або обіцянок ранжування.

## Provenance і межі застосування

Frozen teaching погоджено main і незалежним reviewer до apply. Публічний master містить лише погоджені навчальні дані, final thematic bank config та звичайний footer замість приватних pending notes. Metadata title/description, LearningResource, canonical і breadcrumb main звіряє незалежно до локального preview/apply. Цей редакторський аудит не заявляє завершеного DB apply, браузерного приймання, тестів усіх старих consumers, commit/push або deploy; ці результати належать фінальному звіту з actual evidence.
