# M49 — editorial mapping 1.0.0

Current status: editorially-reviewed/frozen. Нижче — явні reader needs, exact BEFORE→AFTER decisions і історичні pre-freeze notes. Draft-статуси у notes не описують поточний frozen master.

Main review: Had better і May / Might незалежно переглянуті іншим агентом; Can / Could main-review охопив усі форми, basic, таблицю, detail, 6 умов/feedback і рівень A1–A2. External reviewer/certification не заявляється.

Engine before freeze: actual ROOT factory + AnswerVariants, 21/21 final PASS; 18 controls/47 options/173 combinations. Initial private harness-only translation assertion corrected to exact approved multiple-correct translations; source/engine не змінено.

May hero до freeze починається з exact topic May / Might для чинного PageMetadata; немає duplicated prefix. Stable oldslots0–5 та null appended source indices збережено.

## had-better

- Зрозуміти сильну пораду в конкретній ситуації та відрізнити її від факту минулого.
- Побудувати ствердження й заперечення без зайвих to, do, have/has або -s.
- Правильно розгорнути 'd better як had better, не переносити would із 'd rather.
- Упізнати природну заперечну питальну модель й знати її регістр без штучної таблиці коротких відповідей.
- Відокремити особисту перевагу, пораду й вимогу за наміром мовця, а не жорсткою шкалою сили.
- Перевірити розуміння в шести коротких однозначних завданнях без спеціальних медичних чи технічних передумов.

### Exact source mapping

{
  "before": {
    "pointer": "/page/blocks/0/body/rules/0/example",
    "en": "You had better leave now."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/0/data/rules/0/example"
  },
  "reason": "Прозора ситуативна порада збережена EN-exact; додано окремий UK переклад."
}

{
  "before": {
    "pointer": "/page/blocks/0/body/rules/1/example",
    "en": "We'd better hurry, or we'll miss the train."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/0/data/rules/1/example"
  },
  "reason": "Збережено явний можливий наслідок; додано UK переклад без нових часових умов."
}

{
  "before": {
    "pointer": "/page/blocks/0/body/rules/2/example",
    "en": "You'd better not touch that wire."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/0/data/rules/2/example"
  },
  "reason": "Збережено мовний приклад заперечення з точним перекладом; він не використовується для оцінювання технічних знань."
}

{
  "before": {
    "pointer": "/page/blocks/1/body/items/0/subtitle",
    "en": "You had better call him now."
  },
  "after": {
    "status": "retained-moved",
    "pointer": "/blocks/1/data/items/0/rules/0/example"
  },
  "reason": "Приклад перенесено у штатне bilingual rule field; subtitle тепер пояснює незмінність had."
}

{
  "before": {
    "pointer": "/page/blocks/1/body/items/1/subtitle",
    "en": "You had better not be late."
  },
  "after": {
    "status": "retained-moved",
    "pointer": "/blocks/1/data/items/1/rules/0/example"
  },
  "reason": "EN-exact; додано окремий переклад і видиме правило not без do."
}

{
  "before": {
    "pointer": "/page/blocks/1/body/items/2/subtitle",
    "en": "We'd better go."
  },
  "after": {
    "status": "retained-moved",
    "pointer": "/blocks/1/data/items/2/rules/0/example"
  },
  "reason": "Скорочення збережено; поруч додано повне had, щоб не плутати з would."
}

{
  "before": {
    "pointer": "/page/blocks/1/body/items/3/subtitle",
    "en": "Hadn't we better check first?"
  },
  "after": {
    "status": "retained-moved",
    "pointer": "/blocks/1/data/items/3/rules/0/example"
  },
  "reason": "Питальна модель природна; додано переклад, регістр і відмінність від менш поширеного стверджувального питання."
}

{
  "before": {
    "pointer": "/page/blocks/2/body/sections/0/examples/0/en",
    "en": "You'd better see a doctor about that cough."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/2/data/sections/0/examples/0/en"
  },
  "reason": "Корисна стара EN–UK пара збережена; жоден practice key не залежить від діагнозу або медичних тверджень."
}

{
  "before": {
    "pointer": "/page/blocks/2/body/sections/0/examples/1/en",
    "en": "I'd better finish this report tonight."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/2/data/sections/0/examples/1/en"
  },
  "reason": "Збережено пораду самому собі й майбутній tonight; видимо уточнено, що завершення звіту не стверджується."
}

{
  "before": {
    "pointer": "/page/blocks/2/body/sections/1/examples/0/en",
    "en": "You'd better back up your files."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/2/data/sections/1/examples/0/en"
  },
  "reason": "Збережено мовний приклад неявного наслідку; технічну поведінку пристрою не використано як підставу правильності вправ."
}

{
  "before": {
    "pointer": "/page/blocks/2/body/sections/1/examples/1/en",
    "en": "We'd better leave now, or the roads will be blocked."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/2/data/sections/1/examples/1/en"
  },
  "reason": "EN–UK пара збережена; пояснення явно приписує очікування перекриття мовцеві, а не видає його за доведений факт."
}

{
  "before": {
    "pointer": "/page/blocks/2/body/sections/2/examples/0/en",
    "en": "I'd rather stay home tonight."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/2/data/sections/2/examples/0/en"
  },
  "reason": "Збережено зіставлення з перевагою; should більше не подається як її синонім."
}

{
  "before": {
    "pointer": "/page/blocks/2/body/sections/2/examples/1/en",
    "en": "You should try the new restaurant."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/2/data/sections/2/examples/1/en"
  },
  "reason": "Збережено звичайну рекомендацію; її відокремлено від особистого смаку та сильного ситуативного попередження."
}

{
  "before": {
    "pointer": "/page/blocks/3/body/items/0/wrong",
    "en": "You had better to go now."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/3/data/items/0/wrong_en"
  },
  "reason": "Справжня помилка зайвого to залишається wrong; додано переклад задуму."
}

{
  "before": {
    "pointer": "/page/blocks/3/body/items/0/right",
    "en": "You had better go now."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/3/data/items/0/right_en"
  },
  "reason": "Правильний рядок EN-exact, із точним окремим перекладом."
}

{
  "before": {
    "pointer": "/page/blocks/3/body/items/1/wrong",
    "en": "You don't had better wait."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/3/data/items/1/wrong_en"
  },
  "reason": "Справжня помилка do лишається wrong; переклад не втрачає заперечення."
}

{
  "before": {
    "pointer": "/page/blocks/3/body/items/1/right",
    "en": "You had better not wait."
  },
  "after": {
    "status": "retained",
    "pointer": "/blocks/3/data/items/1/right_en"
  },
  "reason": "Збережено правильну заперечну форму й додано переклад."
}

{
  "before": {
    "pointer": "/page/blocks/3/body/items/2/wrong",
    "en": "I'd better have tea than coffee."
  },
  "after": {
    "status": "retained-reclassified",
    "pointer": "/details/0/fragment/data/sections/0/examples/0/en"
  },
  "reason": "Вилучено безумовний статус помилки: старий рядок не задає смаку чи обставин. Він залишений для явного контекстного розбору, а третя картка mistakes тепер перевіряє справжнє has better."
}

{
  "before": {
    "pointer": "/page/blocks/3/body/items/2/right",
    "en": "I'd rather have tea than coffee."
  },
  "after": {
    "status": "retained-moved",
    "pointer": "/details/0/fragment/data/sections/0/examples/1/en"
  },
  "reason": "Збережено як прозорий варіант для особистого смаку, а не універсальне виправлення будь-якого повідомлення з had better."
}

{
  "before": {
    "scope": "Формули",
    "pointers": [
      "/page/blocks/1/body/items/0/title",
      "/page/blocks/1/body/items/1/title",
      "/page/blocks/1/body/items/2/title",
      "/page/blocks/1/body/items/3/title"
    ],
    "values": [
      "had better + base verb",
      "had better not + base verb",
      "'d better",
      "Hadn't we better ...?"
    ]
  },
  "after": {
    "pointers": [
      "/blocks/1/data/items/0/title",
      "/blocks/1/data/items/1/title",
      "/blocks/1/data/items/2/title",
      "/blocks/1/data/items/3/title"
    ]
  },
  "reason": "Формули конкретизують підмет, розгортання had і підтриману інверсію; не додають нових часових форм або штучних коротких відповідей."
}

{
  "before": {
    "scope": "Навігація",
    "pointer": "/page/blocks/5/body",
    "destinations": 10
  },
  "after": {
    "pointer": "/blocks/5/data",
    "status": "unchanged",
    "destinations": 10
  },
  "reason": "Збережено всі старі destinations і current. Додатковий summary link Should / Ought to підтверджено versioned definition, але локальний HTTP у цьому author subtask не виконувався."
}

### Primary source access

{
  "url": "https://dictionary.cambridge.org/grammar/british-grammar/had-better",
  "indexed_url": "https://dictionary.cambridge.org/grammar/british-grammar/had-better?hc_location=ufi",
  "publisher": "Cambridge English Grammar Today",
  "checked_at": "2026-10-12",
  "access": "Пряме відкриття основного URL повернуло 403. Прочитано індексований редакційний текст через web search, включно з формою, значенням, запереченням, питаннями та межами з preference/requirement; пряме повне читання не заявлено.",
  "checked_rules": [
    "Незмінне had, базова форма без to, теперішнє/майбутнє значення",
    "Had better not і скорочення 'd",
    "Інверсія в питаннях, формальніший регістр щодо should, частіша заперечна питальна форма",
    "Специфічна порада на відміну від простої переваги або повідомлення про вимогу"
  ],
  "use": "Перевірка правил. Нові приклади видавця не копіювалися; збіги старих типових речень походять із наданої BEFORE definition."
}

{
  "url": "https://www.oxfordlearnersdictionaries.com/definition/english/better_2",
  "publisher": "Oxford Advanced Learner’s Dictionary",
  "checked_at": "2026-10-12",
  "access": "Прочитано індексовану редакційну словникову статтю, idiom had better/best та Grammar Point; пряме відкриття не виконувалося, full direct read не заявлено.",
  "checked_rules": [
    "Порада в актуальній ситуації",
    "Погрозливе вживання можливе в контексті, але не є значенням кожної репліки"
  ],
  "use": "Перевірка прагматичних меж; власні вправи не відтворюють видавничі завдання."
}

{
  "url": "https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/suggestions-obligations",
  "publisher": "British Council LearnEnglish",
  "checked_at": "2026-10-12",
  "access": "Сторінку відкрито; прочитано редакційні розділи Suggestions та Obligations. Коментарі учнів і відповіді в comment threads не використовувалися як нормативне джерело.",
  "checked_rules": [
    "Should як порада/рекомендація",
    "Must та need to для необхідності; відмінність поради від вимоги"
  ],
  "use": "Коротке зіставлення сусідніх тем, без розгорнутого нового уроку про модальні дієслова."
}

{
  "url": "https://dictionary.cambridge.org/us/grammar/british-grammar/Should",
  "publisher": "Cambridge English Grammar Today",
  "checked_at": "2026-10-12",
  "access": "Прочитано індексований редакційний розділ Advice and suggestions через web search. Прямий /grammar/british-grammar/should повернув 403; успішного direct read не заявлено.",
  "checked_rules": [
    "Should передає пораду, не є простим еквівалентом особистої переваги"
  ],
  "use": "Усунення старого змішування should і would rather."
}

{
  "url": "https://developers.google.com/search/docs/fundamentals/creating-helpful-content",
  "publisher": "Google Search Central",
  "checked_at": "2026-10-12",
  "access": "Сторінку відкрито й прочитано редакційні розділи про корисний, точний, оригінальний people-first content.",
  "checked_rules": [
    "Потреби читача й повнота відповіді без довільної квоти слів або keyword stuffing"
  ],
  "use": "Структура уроку та редакційні питання; жодних claims про індексацію, volumes, CTR або позиції."
}

### Historical pre-freeze review notes

# M49 — Had better: авторська чернетка

Статус: pre-freeze draft для окремої мовної, методичної та інтеграційної перевірки. Це не accepted master, не локальний apply і не звіт про браузерне приймання.

Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsHadBetterTheorySeeder`.
Локальний URL: [Had better](http://gramlyze.loc/theory/conditionals/had-better).
Locale `uk`, ancestry `conditionals`, display A2–B1, technical block level A2 збережені.

Джерело BEFORE: `database/seeders/Page_V3/Conditionals/ConditionalsHadBetterTheorySeeder/definition.json`, повністю прочитано разом із body JSON усіх шести блоків. Жодну definition, frozen M48, runtime, БД або візуальний файл у цьому author subtask не змінено.

## Що отримує читач

- Сильна порада стосується конкретних теперішніх або майбутніх обставин; had не означає здійснену минулу дію.
- Ствердження, заперечення, незмінне had, базове дієслово без to/-s та розгортання `'d = had` показані у видимому basic.
- Питання з інверсією не оголошені забороненими чи основним повсякденним проханням. Видимо пояснено формальніший регістр щодо should і частішу негативну модель.
- Розрізнені ситуативна порада, особиста перевага, звичайна рекомендація та вимога — без фіксованої шкали сили чи ввічливості.
- Старі English приклади збережені. Чай/кава більше не є безконтекстним «wrong»; це контекстне поглиблення свого meaning-point.

Немає нової таблиці лише заради симетрії з іншими уроками, штучних коротких відповідей, нового quiz/UI типу чи уроку про past modals.

## Карта native-компонентів

| Індекс draft | Key | Старий slot | Компонент | Наповнення |
|---:|---|---:|---|---|
| 0 | had-better-hero | 0 | hero | Вступ + 3 правила з окремими EN/UK рядками |
| 1 | had-better-forms | 1 | forms-grid | 4 картки, 6 bilingual rule rows |
| 2 | had-better-uses | 2 | usage-panels | 4 іменовані semantic points, 8 EN–UK пар |
| 3 | had-better-mistakes | 3 | mistakes-grid | 3 справжні form errors, усі wrong/right із перекладами задуму |
| 4 | had-better-summary | 4 | summary-list | 5 коротких пунктів і дві доречні внутрішні підв’язки |
| 5 | had-better-navigation | 5 | navigation-chips | Усі 10 старих destinations, current Had better |
| 6 | had-better-practice | новий | practice-set | 6 tasks / 6 required controls / 14 видимих options |

Перші шість slots, їхні `type`, `column` і наявний `level` продовжуються без перестановки. Є лише один appended block — practice. Заголовок практики «5. Практика» не означає зміни source index. Жодні нові payload CSS-класи, styles, executable views або блоки M49 UI не додаються.

`hero.rules[].example` та `forms.items[].rules[].example` використовують чинні HTML-поля з EN `<span>`, `<br>` і UK `<span>`, без layout markup. Usage/details — штатні `{en, ua}`. Mistakes мають підтримані bilingual fields і повний HTML fallback. Labels/title не містять власної верстки.

## Basic / detail

Є одне поглиблення `had-better-tea-context`, прив’язане лише до `had-better-uses /sections/2`.

Воно розрізняє причину практичного вибору і смакову перевагу: старі два English рядки з чаєм/кавою та нове прозоре повідомлення про час очікування кави. Це окремий пояснювальний розбір із трьома EN–UK парами, а не схований переклад або ремарка про регістр. Усі потрібні для A2–B1 формули, значення й застереження залишаються у basic.

Усього: 1 detail / 1 native usage fragment / 3 bilingual example objects. Третій example object містить два речення обома мовами.

## Повна карта старих English полів

У BEFORE-шляхах `body` означає декодований JSON відповідного блока. AFTER-шляхи відносні до `had-better.json`; англійський текст у rich field збережений дослівно. Карта охоплює всі 19 старих sentence-bearing fields; жодне речення не вилучено. Окремо нижче наведено чотири формули.

| № | BEFORE pointer | Старий English | AFTER pointer | Рішення й причина |
|---:|---|---|---|---|
| 1 | `/page/blocks/0/body/rules/0/example` | You had better leave now. | `/blocks/0/data/rules/0/example` | EN-exact; додано точний окремий переклад. |
| 2 | `/page/blocks/0/body/rules/1/example` | We'd better hurry, or we'll miss the train. | `/blocks/0/data/rules/1/example` | Збережено explicit consequence та додано переклад. |
| 3 | `/page/blocks/0/body/rules/2/example` | You'd better not touch that wire. | `/blocks/0/data/rules/2/example` | Збережено мовний приклад not; не використовується для тестування технічних знань. |
| 4 | `/page/blocks/1/body/items/0/subtitle` | You had better call him now. | `/blocks/1/data/items/0/rules/0/example` | Перенесено в штатне bilingual example field; subtitle пояснює форму. |
| 5 | `/page/blocks/1/body/items/1/subtitle` | You had better not be late. | `/blocks/1/data/items/1/rules/0/example` | EN-exact, переклад і not-position видимі. |
| 6 | `/page/blocks/1/body/items/2/subtitle` | We'd better go. | `/blocks/1/data/items/2/rules/0/example` | Поруч є повне We had better go. з окремим перекладом. |
| 7 | `/page/blocks/1/body/items/3/subtitle` | Hadn't we better check first? | `/blocks/1/data/items/3/rules/0/example` | Природна негативна питальна модель збережена; додано register, не вигадану матрицю відповідей. |
| 8 | `/page/blocks/2/body/sections/0/examples/0/en` | You'd better see a doctor about that cough. | `/blocks/2/data/sections/0/examples/0/en` | Стара EN–UK пара збережена як ілюстрація поради, не медичний діагноз чи основа scoring. |
| 9 | `/page/blocks/2/body/sections/0/examples/1/en` | I'd better finish this report tonight. | `/blocks/2/data/sections/0/examples/1/en` | Збережено, видимо уточнено, що звіт ще треба закінчити. |
| 10 | `/page/blocks/2/body/sections/1/examples/0/en` | You'd better back up your files. | `/blocks/2/data/sections/1/examples/0/en` | Збережено неявний наслідок; вправи не потребують технічних передумов. |
| 11 | `/page/blocks/2/body/sections/1/examples/1/en` | We'd better leave now, or the roads will be blocked. | `/blocks/2/data/sections/1/examples/1/en` | Очікування перекриття явно приписане мовцеві; воно не доводиться як зовнішній факт. |
| 12 | `/page/blocks/2/body/sections/2/examples/0/en` | I'd rather stay home tonight. | `/blocks/2/data/sections/2/examples/0/en` | Збережено особисту перевагу. |
| 13 | `/page/blocks/2/body/sections/2/examples/1/en` | You should try the new restaurant. | `/blocks/2/data/sections/2/examples/1/en` | Збережено як рекомендацію; should більше не названо еквівалентом «я віддаю перевагу». |
| 14 | `/page/blocks/3/body/items/0/wrong` | You had better to go now. | `/blocks/3/data/items/0/wrong_en` | Реальна помилка зайвого to збережена; UK передає задум. |
| 15 | `/page/blocks/3/body/items/0/right` | You had better go now. | `/blocks/3/data/items/0/right_en` | Збережено точне виправлення. |
| 16 | `/page/blocks/3/body/items/1/wrong` | You don't had better wait. | `/blocks/3/data/items/1/wrong_en` | Реальна помилка do збережена; переклад не губить заперечення. |
| 17 | `/page/blocks/3/body/items/1/right` | You had better not wait. | `/blocks/3/data/items/1/right_en` | Збережено точне виправлення й переклад. |
| 18 | `/page/blocks/3/body/items/2/wrong` | I'd better have tea than coffee. | `/details/0/fragment/data/sections/0/examples/0/en` | Перекласифіковано: саме than не доводить помилку. Безконтекстний рядок розглядається поряд із двома явними задумами. |
| 19 | `/page/blocks/3/body/items/2/right` | I'd rather have tea than coffee. | `/details/0/fragment/data/sections/0/examples/1/en` | Збережено для смакової переваги, не як універсальне виправлення першого речення. |

| BEFORE formula path | BEFORE | AFTER formula path | AFTER |
|---|---|---|---|
| `/page/blocks/1/body/items/0/title` | had better + base verb | `/blocks/1/data/items/0/title` | subject + had better + base verb |
| `/page/blocks/1/body/items/1/title` | had better not + base verb | `/blocks/1/data/items/1/title` | subject + had better not + base verb |
| `/page/blocks/1/body/items/2/title` | 'd better | `/blocks/1/data/items/2/title` | 'd better = had better |
| `/page/blocks/1/body/items/3/title` | Hadn't we better ...? | `/blocks/1/data/items/3/title` | Had(n't) + subject + better + base verb? |

Формули не є завершеними реченнями: їхню функцію пояснюють українські label/subtitle. Новий третій mistake — `She has better check the time.` → `She had better check the time.` — перевіряє реальну незмінність had, замість старого контекстно неповного протиставлення смаку.

## Шість завдань: мета визначає механіку

| Source index / ID | Мета | Механіка | Options | Canonical |
|---|---|---|---:|---|
| 1 / m49-had-better-base | Базове дієслово після названого had better | select | 3 | leave |
| 2 / m49-had-better-negative | Не розкривати сюрприз: звичайна заперечна форма | select | 3 | had better not |
| 3 / m49-had-better-contraction | Розгорнути саме 'd better | choice | 2 | I had better call Nina now. |
| 4 / m49-had-better-time | Відрізнити пораду зараз від минулого | choice | 2 | Мені краще піти зараз. |
| 5 / m49-had-better-preference | Особистий смак, не рекомендація через обставини | choice | 2 | I'd rather sit by the window. |
| 6 / m49-had-better-question | Інверсія, а не do, у заперечному питанні | choice | 2 | Hadn't we better check the date first? |

2 selects + 4 choices — не квота: перші дві цілі локальні морфосинтаксичні; решта потребують вибору розгортання, смислу або цілого питання. Один control на task; немає compound inflation. Лексика повсякденна: бібліотека, сюрприз, дзвінок, кафе, місце біля вікна, дата квитків.

До check — українська умова, потрібний English stimulus/варіанти. Завдання 4 свідомо пропонує UK тлумачення як варіанти відповіді, а не пояснювальну підказку. Після check — точний переклад і розбір для кожного видимого кандидата; для неправильних sentence candidates передано задумане значення, не створено враження, що їхня форма правильна.

У вправі 3 canonical — повне `I had better ...`, а не скорочене `'d`, яке чужий generic normalizer міг би розгорнути як would. У вправі 5 є тільки змістовно рівний alias `I would rather sit by the window.`; жодного alias із had. Фактичний engine / ASCII-типографські apostrophes / state-cycle acceptance ще має виконати інтегратор. Цей документ не заявляє PASS лише за читанням коду.

Вибір між had better і should не оцінюється без додаткового критерію: practice 1/2/6 називає цільову конструкцію прямо; practice 5 задає саме особистий смак. Жоден правильний варіант не залежить від медичних або технічних знань.

## Сусідні теми й навігація

Усі 10 старих footer destinations, порядок і current збережені: Zero, First, Second, Third, Mixed, Unless/Provided/As long as, In case, Wish/If only, Would rather, Had better. Жодної зміни category не пропонується.

- [Would rather](http://gramlyze.loc/theory/conditionals/would-rather): особиста перевага, `'d = would`; destination наявний у BEFORE навігації та master M48.
- [Should / Ought to](http://gramlyze.loc/theory/modal-verbs/should-ought-to): рекомендація; slug/category прочитані з чинної `ModalVerbsShouldOughtToTheorySeeder/definition.json`.

Actual destination HTTP не виконувався цим author subtask, отже успішний live status не заявлено. Must / Have to згадано для межі значення, але сусідній урок не редагується й не дублюється.

## Первинні джерела й фактичний доступ

Перевірено 2026-10-12. Нові видавничі речення/вправи не копіювалися. Типові старі приклади з BEFORE залишені як користувацький вихідний матеріал; це не нове запозичення з publisher page.

| Джерело | Фактичний доступ | Що перевірено |
|---|---|---|
| [Cambridge English Grammar Today — Had better](https://dictionary.cambridge.org/grammar/british-grammar/had-better) | Прямий URL повернув 403; редакційний текст прочитано через [індексований результат](https://dictionary.cambridge.org/grammar/british-grammar/had-better?hc_location=ufi). Прямого full read не заявлено. | Незмінне had, bare infinitive, теперішнє/майбутнє, not, скорочення, questions/register, specific advice vs preference/requirement. |
| [Oxford Advanced Learner’s Dictionary — better](https://www.oxfordlearnersdictionaries.com/definition/english/better_2) | Індексована редакційна стаття та Grammar Point. Прямий URL не відкривався. | Порада в актуальній ситуації; можливий threat у відповідному контексті, не глобальне значення. |
| [British Council — Suggestions and obligations](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/suggestions-obligations) | Сторінку відкрито; прочитані редакційні Suggestions / Obligations. Learner comments і staff answers у threads не використовувалися. | Should як порада; необхідність/вимога з must / need to. |
| [Cambridge — Should](https://dictionary.cambridge.org/us/grammar/british-grammar/Should) | Індексований редакційний розділ Advice and suggestions. Прямий lowercase `/grammar/british-grammar/should` повернув 403. | Не ототожнювати should із простою смаковою перевагою. |
| [Google Search Central — helpful content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content) | Відкрито й прочитано редакційні настанови. | Повнота та користь для читача; без word-count quota, keyword stuffing і claims про ranking. |

Додаткова спроба прямого Cambridge `would-rather-would-sooner` повернула 403; успішне читання цього URL не заявляється й окремого нового правила на його підставі не додано. Контраст preference підтверджений доступним редакційним Had better та незмінним accepted M48.

## Що ще потрібно до freeze

Окремий мовний/методичний review, фінальна перевірка metadata пріоритету hero, actual source/owner binding, aliases у чинному engine та реальний локальний UI. Detail про старий чай/каву потребує особливої уваги review: він навмисне не оголошує рядок універсально неправильним і не рекласифікує смак як пораду. Короткі register notes лишилися у basic.

No-JS / print не оголошені реалізованими або перевіреними. Нових fallback-механізмів, прихованого ключа чи visual змін цей draft не пропонує.


## can-could

- {"question":"Як утворити ствердження, заперечення, запитання й коротку відповідь?","answer_location":"can-could-forms; can-could-mistakes"}
- {"question":"Чи could завжди означає минуле, а can — лише теперішнє?","answer_location":"can-could-hero; can-could-uses /sections/1 та /sections/3"}
- {"question":"Хто має діяти: я прошу дозволу чи допомоги?","answer_location":"can-could-uses /sections/2 та /sections/3; practice source_index 3 та 4"}
- {"question":"Чим минула здатність відрізняється від завершеного ремонту, невдачі й сприйняття?","answer_location":"can-could-uses /sections/0; can-could-past-result"}
- {"question":"Коли потрібне be able to?","answer_location":"can-could-uses /sections/1; can-could-other-forms"}
- {"question":"Чи can і could показують точний відсоток імовірності?","answer_location":"can-could-uses /sections/4"}

### Exact source mapping

{
  "source_pointer": "/page/subtitle_html",
  "before": "<p><strong>Can / Could</strong> допомагає говорити про здатність, можливість, базовий дозвіл і ввічливі прохання.</p>",
  "after": "subtitle_html",
  "reason": "Збережено H1-identity; виправлено узгодження й названо практичні потреби читача. Категорія залишається modal-verbs."
}

{
  "source_pointer": "/page/blocks/0/body/rules",
  "before": "I can swim. / She can drive.; When I was ten, I could swim. / Could you help me?; Can I sit here? / It could rain later.",
  "after": "can-could-hero; can-could-forms; can-could-uses /sections/4",
  "reason": "П’ять корисних старих речень збережено й перекладено. It could rain later замінено вже наявним точнішим It could rain this evening, щоб не дублювати приклад погоди в hero."
}

{
  "source_pointer": "/page/blocks/1/body/items",
  "before": "can swim / can't swim / Can you swim?; could swim / couldn't swim / Could you help?; Yesterday I was able to solve it.; will be able to / want to be able to",
  "after": "can-could-forms; can-could-past-result; can-could-other-forms",
  "reason": "Фрагменти перетворено на повні короткі EN–UK пари. Розпливчасте solve it замінено конкретним старим printer-контекстом. Be able to не стоїть нарівні з базовими чотирма формами як обов’язкова велика тема."
}

{
  "source_pointer": "/page/blocks/2/body/sections/0",
  "before": "I can cook, but I can't bake well.; My grandfather could speak three languages.; She will be able to join us next week.",
  "after": "can-could-uses /sections/0; can-could-other-forms",
  "reason": "Усі три EN–UK пари збережено; will be able to перенесено до окремого змістовного пояснення форми."
}

{
  "source_pointer": "/page/blocks/2/body/sections/1",
  "before": "Can I open the window?; Can you call me after class?; Could you send me the file again?",
  "after": "can-could-uses /sections/2 та /sections/3",
  "reason": "Усі старі EN–UK пари збережено; дозвіл для себе відокремлено від прохання до співрозмовника, додано природні відповіді."
}

{
  "source_pointer": "/page/blocks/2/body/sections/2",
  "before": "This road can be dangerous in winter.; It could rain this evening.; We could be late if we leave now.",
  "after": "can-could-uses /sections/4",
  "reason": "Перші дві EN–UK пари збережено. Останню прибрано як зайвий третій приклад без потрібного контексту часу відправлення; значення конкретної непевної можливості збережене у прикладі дощу. Відмовлено від шкали «сильніша/слабша» як автоматичного правила."
}

{
  "source_pointer": "/page/blocks/3/body/items/0 та /items/1",
  "before": "She cans swim well. → She can swim well.; I can to help you. → I can help you.",
  "after": "can-could-mistakes /items/0 та /items/1",
  "reason": "Старі справжні помилки й виправлення збережено; додано UK намір/переклад, прибрано payload CSS/іконки."
}

{
  "source_pointer": "/page/blocks/3/body/items/2",
  "before": "Yesterday I could fix the printer at last. → Yesterday I was able to fix the printer at last.",
  "after": "can-could-past-result /rows/0..2",
  "reason": "Не лишено глобального ярлика «could для однієї дії — помилка». Corrected printer-речення збережено з явно завершеним ремонтом; поруч наведено конкретну невдачу couldn't та сприйняття could hear. Старе could-речення не засуджується поза контекстом."
}

{
  "source_pointer": "/page/blocks/4/body/items",
  "before": "Can = теперішня або загальна здатність; Could = загальна здатність у минулому; could you м’якше за can you; одна успішна дія was able to / managed to.",
  "after": "can-could-summary",
  "reason": "Збережено корисні висновки без обмеження can лише теперішнім, could лише минулим або універсальної заборони для одного минулого епізоду."
}

{
  "source_pointer": "/page/blocks/5/body",
  "before": "8 navigation items, can-could current=true",
  "after": "can-could-navigation",
  "reason": "Навчальні дані старої навігації збережені точно, з сімома links і поточним пунктом; інші теми не редагуються."
}

### Primary source access

{
  "url": "https://dictionary.cambridge.org/grammar/british-grammar/could",
  "access_date": "2026-10-12",
  "access": "Direct open: 403. Official indexed page text read through web search, including form, permission, past achievement and senses/mental-process sections.",
  "claims": [
    "Could має неминулі значення; порядок у запитаннях і запереченнях.",
    "Для фактичного завершеного успіху usual was/were able to / managed to; could можливе для past senses/mental processes.",
    "Could I запитує дозвіл, але актуальний дозвіл можна надати can."
  ],
  "use": "Межі правил, не копіювання видавничих прикладів."
}

{
  "url": "https://dictionary.cambridge.org/grammar/british-grammar/can-could-or-may",
  "access_date": "2026-10-12",
  "access": "Direct open: 403. Official indexed page text read through web search.",
  "claims": [
    "Can/could для запитів дозволу й прохань, різниця з наданням дозволу.",
    "Загальна можливість відрізняється від непевного конкретного варіанта; future availability не дорівнює припущенню."
  ],
  "use": "Контекст і accepted alternatives; жодних відсотків імовірності."
}

{
  "url": "https://dictionary.cambridge.org/us/grammar/british-grammar/can",
  "access_date": "2026-10-12",
  "access": "Official indexed page text read through web search; no successful direct-open claim.",
  "claims": [
    "Can для present/future ability та permission; cannot, без do/does/did у target questions/negatives."
  ],
  "use": "Перевірка future availability; урокові приклади локальні або авторські."
}

{
  "url": "https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/ability",
  "access_date": "2026-10-12",
  "access": "Direct page retrieval succeeded; main beginner grammar explanation read. Learner comments not used as normative rules.",
  "claims": [
    "Can/can't для загального вміння і конкретної теперішньої або майбутньої здатності; could/couldn't для минулого."
  ],
  "use": "A1-A2 basic; intermediate could have не розгортається в цьому уроці."
}

{
  "url": "https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/past-ability",
  "access_date": "2026-10-12",
  "access": "Direct page retrieval succeeded; main General ability / Successful / Unsuccessful explanation read. Comments excluded.",
  "claims": [
    "Минулу загальну здатність відокремлено від досягнутого результату; couldn't підходить для конкретної невдалої дії."
  ],
  "use": "Точність короткого зіставлення, без перенесення B1-B2 складності вправ у A1-A2 lesson."
}

{
  "url": "https://dictionary.cambridge.org/grammar/british-grammar/be-able-to",
  "access_date": "2026-10-12",
  "access": "Direct open failed. No successful page-read claim; this URL is an access limitation, not independent evidence.",
  "claims": [],
  "use": "Початкова невдала адреса; чинний офіційний матеріал знайдено за Be expressions, наведено окремо."
}

{
  "url": "https://dictionary.cambridge.org/uk/grammar/british-grammar/be-expressions-be-able-to-be-due-to",
  "access_date": "2026-10-12",
  "access": "Official indexed page text read through web search, specifically Be able to / Abilities and Past achievement. No successful direct-open claim.",
  "claims": [
    "Be able to може вживатися там, де can неможливе, зокрема після іншого модального дієслова.",
    "Past affirmative achievement та negative couldn't/wasn't able to розрізнено."
  ],
  "use": "Підтвердження окремого meaningful detail; приклади з довідника не копіювалися."
}

### Historical pre-freeze review notes

# M49 draft — Can / Could

Статус: авторська чернетка для незалежного перегляду, не frozen, не applied.
Owner: `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsCanCouldTheorySeeder`.
URL із поточної definition/nav: `/theory/modal-verbs/can-could`.
Locale `uk`, category `modal-verbs`, visible level `A1-A2`, technical `A1`.

## Редакційне рішення

Вісім native blocks: шість старих slots 0–5 з їхніми type/column/level плюс comparison-table і practice-set після них. Чотири forms cards відповідають ствердженню, запереченню, запитанню й короткій відповіді; не дублюють усі значення модальних дієслів. П’ять named usage points розрізняють уміння, доступну дію, власний дозвіл, прохання до іншого та можливість. Три рядки таблиці — завершений ремонт, невдала спроба, конкретне сприйняття. Це не новий дизайн і не окремий авторський renderer.

Could не зведено до минулого, can — до теперішнього. У permissions/request вказано, хто має діяти. Для надання поточного дозволу є Yes, you can, для відмови — No, you can't; для прийняття прохання — природна відповідь із Sure. Could зазвичай менш пряме, але не робить будь-яке звернення автоматично ввічливим. Значення не визначається лише please, yesterday чи tomorrow.

Одне meaningful detail, `can-could-other-forms`, належить `can-could-uses /sections/1`: чому інша будова вимагає be able to після will або want. Воно має окреме пояснення і дві EN–UK пари. Видимий basic уже містить future can, will be able to, заборону will can і застереження від механічної заміни. Жоден переклад, коротке застереження або потрібне правило про past perception не сховані за кнопкою.

Не переносимо Could have, складні умовні чи deduction-завдання в A1–A2 basic. Посилання на May / Might та Past Modals уже існують у старій навігації; сусідні definitions не редагувалися. Вісім navigation items збережено, включно з current Can / Could.

## Повна карта старих English-полів

Позначення `B0` тощо — `page.blocks[N]`, pointer усередині декодованого `body`. Формули/назви модальних дієслів не трактуються як англійські речення. Для всіх старих teaching English sentences і списків форм наведено рішення; переклади збережені або уточнені відкрито.

| Старий field / точний EN | Нова адреса / рішення | Причина |
|---|---|---|
| B0 rules[0].example: `I can swim. / She can drive.` | I can swim. → hero rules[0]; She can drive. → forms items[0].rules[0] | Обидва збережено, додано окремі повні UK переклади. |
| B0 rules[1].example: `When I was ten, I could swim. / Could you help me?` | hero rules[1] і rules[2] | Обидва збережено й перекладено; не злиті «минуле/ввічливе». |
| B0 rules[2].example: `Can I sit here? / It could rain later.` | `Can I sit here?` → forms items[2].rules[1]; rain later → старе точніше `It could rain this evening.` у usage sections[4] | Перше речення збережено буквально; другий погодний приклад об’єднано з наявним, не змінено past/future правило. |
| B1 items[0].title/subtitle: `can + V`; `can swim / can't swim / Can you swim?` | forms items[0..2]: формула + повні I can swim / I can't swim / Can you swim? із перекладом | Додано зрозумілий підмет, звичайні три форми без довгого ряду. |
| B1 items[1].title/subtitle: `could + V`; `could swim / couldn't swim / Could you help?` | forms items[0..1] At six, I could swim / At four, I couldn't swim; hero Could you help me? | Past-контекст у прикладах здатності; старий корисний request із hero, додано одержувача допомоги. |
| B1 items[2].title/subtitle: `was able to / managed to`; `Yesterday I was able to solve it.` | past-result rows[0]/outro з printer | Замість невизначеного it — уже наявний предмет і явно завершений ремонт. Принцип обох конструкцій збережено. |
| B1 items[3].title/subtitle: `be able to`; `will be able to / want to be able to` | basic usage[1].note + meaningful detail із She will be able... / I want to be able... | Форми збережено, розгорнуто тільки окреме корисне пояснення. |
| B2 sections[0].examples[0]: `I can cook, but I can't bake well.` | usage[0].examples[0], буквально | UK «Я вмію готувати, але не дуже добре печу.» збережено. |
| B2 sections[0].examples[1]: `My grandfather could speak three languages.` | usage[0].examples[1], буквально | UK «Мій дідусь умів говорити трьома мовами.» збережено. |
| B2 sections[0].examples[2]: `She will be able to join us next week.` | detail examples[0], буквально | UK «Вона зможе приєднатися до нас наступного тижня.» збережено. |
| B2 sections[1].examples[0]: `Can I open the window?` | usage[2].examples[0], буквально | UK «Можна я відкрию вікно?» збережено; дозвіл відділено від чужої дії. |
| B2 sections[1].examples[1]: `Can you call me after class?` | usage[3].examples[0], буквально | UK «Можеш подзвонити мені після заняття?» збережено; також не є distractor проти could. |
| B2 sections[1].examples[2]: `Could you send me the file again?` | usage[3].examples[1], буквально | UK «Не міг би ти надіслати мені файл ще раз?» збережено. |
| B2 sections[2].examples[0]: `This road can be dangerous in winter.` | usage[4].examples[0], буквально | UK «Ця дорога може бути небезпечною взимку.» збережено; general possibility. |
| B2 sections[2].examples[1]: `It could rain this evening.` | usage[4].examples[1], буквально | UK «Сьогодні ввечері може піти дощ.» збережено; конкретний непевний прогноз. |
| B2 sections[2].examples[2]: `We could be late if we leave now.` | Прибрано з visible basic, зміст possibility вже показує дощ | Старе UK «Ми можемо запізнитися, якщо вийдемо зараз.» не переписується потай; це явне вилучення зайвого прикладу, для якого бракує контексту часу дороги. |
| B3 items[0].wrong/right: `She cans swim well.` / `She can swim well.` | mistakes[0], буквально EN | Додано UK намір/переклад; старі ✅ і font-mono не переносяться у learning payload. |
| B3 items[1].wrong/right: `I can to help you.` / `I can help you.` | mistakes[1], буквально EN | Додано UK намір/переклад, реальне зайве to лишається помилкою. |
| B3 items[1].hint: `help`, `to help` | mistakes[1].hint + форма can help | Лексичний контраст збережено через повне виправлення; hint не дублює окремі fragments. |
| B3 items[2].wrong: `Yesterday I could fix the printer at last.` | Не лишено у red wrong card; правило перенесено до past-result з завершеним ремонтом | Речення без контексту не оголошується універсально неграматичним. При результативному прочитанні учневі показано was able to / managed to. |
| B3 items[2].right: `Yesterday I was able to fix the printer at last.` | past-result rows[0], буквально EN + повний UK | Збережено yesterday, printer, at last; факт завершення ремонту явно зазначено. |
| B4 summary: can, could, can / could + базова форма, was able to / managed to, Could you...? / Can you...? | summary[0..4] + basic/forms/table | Збережено корисні моделі, виправлено надто широкі висновки; ці fragments не є неперекладеними повними реченнями. |
| B5 navigation labels/URLs/current | navigation, структурно ті самі 8 records | Не перекладати усталені назви уроків і не змінювати цільові маршрути. |

## Шість вправ: мета, неоднозначність, aliases

Два completion-select перевіряють форму й past ability; чотири choice перевіряють роль і зміст. Це рішення за метою, не квота 3+3. Є 6 controls, 17 видимих options і 9 прийнятних видимих варіантів відповідей. Canonical keys — 6; aliases не додають балів за той самий control.

1. Lina can swim: -s/to проти правильної форми, A1 лексика.
2. Ben at eight: умова явно називає минулу здатність, а не спробу вгадати could лише з yesterday.
3. Дозвіл відкрити вікно самому: Can I і Could I приймаються; Could you змінює виконавця.
4. Дзвінок друга після заняття: Can you і Could you приймаються; Can I змінює виконавця.
5. Іра вільна завтра о п’ятій: can/can't розрізняють позитивну доступність і неможливість. Could не використане як штучно неправильне схоже прохання.
6. Принтер полагоджено: was able to і managed to приймаються; couldn't є граматичним реченням про інший результат.

Усі literal option values мають окремий lowercased-key feedback: повне правильне речення, UK переклад і причина; для граматичних неправильних choice-кандидатів спочатку пояснюється їхній власний зміст. До Check немає готових перекладених відповідей чи розборів у кнопках. Selected-response використовує чинний engine й не вимагає нового manual/token механізму. Linked-bank sentence builder не копіюється й не змінюється. Наявність serialized JS answers не є usable no-JS ключем; no-JS/print приймання не виконувалося в авторській підзадачі.

## Джерела й спосіб доступу, 2026-10-12

- [Cambridge Could](https://dictionary.cambridge.org/grammar/british-grammar/could): direct 403; прочитано офіційний indexed текст із розділами form, permission, past achievement, perception/mental processes.
- [Cambridge Can, could or may](https://dictionary.cambridge.org/grammar/british-grammar/can-could-or-may): direct 403; офіційний indexed текст для запиту/надання дозволу, request і типів possibility.
- [Cambridge Can](https://dictionary.cambridge.org/us/grammar/british-grammar/can): офіційний indexed текст, без заяви про успішний direct open; present/future ability, question/negative forms.
- [British Council Ability](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/ability): успішне пряме читання main beginner explanation; present/future ability, past ability. Коментарі учнів не використовувалися.
- [British Council Past ability](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/past-ability): успішне пряме читання головних розділів General / Successful / Unsuccessful; складність B1-B2 не переноситься до нових вправ.
- [Cambridge Be able to](https://dictionary.cambridge.org/grammar/british-grammar/be-able-to): початкова адреса недоступна; не названа прочитаним доказом.
- [Cambridge Be expressions: Be able to](https://dictionary.cambridge.org/uk/grammar/british-grammar/be-expressions-be-able-to-be-due-to): після пошуку знайдено офіційний indexed текст; прочитано Abilities та Past achievement. Підтверджує be able to після іншого modal та розрізнення past success/failure; не заявляється пряме відкриття сторінки.

Джерела використовуються для меж правил; видавничі приклади не скопійовані. Старі локальні приклади атрибутовані через точну карту вище, нові складено для цього уроку.

## Що ще не доведено

Це лише source authoring. Не було DB writes, локального HTTP/browser, renderer execution, isolated tests, visual acceptance чи Git-операцій. JSON parsing/hash/count може бути перевірений як file-only housekeeping, але це не доказ фактичного рендеру. Перед freeze потрібен незалежний перегляд таблиці past ability й detail be able to; parent перевірить package guard, translations у реальному canonical render, усі accepted options і caller-boundary. Сусідні джерела, попередні frozen packages, Blade/CSS/JS та існуюча definition не змінювалися.


## may-might

- Зрозуміти, коли may / might означає припущення про теперішнє чи майбутнє, а не минулий час або вміння.
- Побудувати базову форму, заперечення та речення зі станом через be.
- Відрізнити прохання дозволу для себе, надання або відмову в дозволі від прохання іншої людини діяти.
- Не плутати may not як можливу відсутність події з may not як забороною або з твердженням про неможливість.
- Розрізнити may be / maybe у повному реченні та потренувати обидві доречні модальні альтернативи без вигаданих відсотків.

### Exact source mapping

{
  "before_pointer": "/page/subtitle_html",
  "after_pointer": "/subtitle_html",
  "before": "May / Might показує можливість, ймовірність і формальніші варіанти дозволу.",
  "after": "May / Might: можливість зараз і в майбутньому, дозвіл, два значення may not та різниця між may be і maybe.",
  "reason": "Уточнено читачеві реальні питання уроку без зміни H1, slug, category або обіцянки SEO-результату."
}

{
  "before_pointer": "/page/blocks/0/body/rules/0/example",
  "after_pointers": [
    "/blocks/0/data/rules/0/example",
    "/blocks/1/data/items/2/rules/0/example"
  ],
  "before": "She may come later. / He might be busy.",
  "after": "Обидва речення збережено окремо з точними перекладами.",
  "reason": "Дію й стан рознесено за навчальною функцією; be пояснюється в картці форми."
}

{
  "before_pointer": "/page/blocks/0/body/rules/1/example",
  "after_pointers": [
    "/blocks/0/data/rules/1/example",
    "/blocks/2/data/sections/1/examples/3"
  ],
  "before": "May I ask a question? / Students may leave early.",
  "after": "Обидва речення збережено; питання в hero, надання дозволу в usage.",
  "reason": "Явно розділено роль того, хто просить, і того, хто дозволяє; додано переклади."
}

{
  "before_pointer": "/page/blocks/0/body/rules/2/example",
  "after_pointers": [
    "/blocks/2/data/sections/2/examples/2",
    "/blocks/2/data/sections/2/examples/3"
  ],
  "before": "It may rain. / It might rain.",
  "after": "Обидва збережені в пункті без шкали відсотків, з однаковим перекладом за відсутнього розрізнювального контексту.",
  "reason": "Не нав’язуємо відмінності певної величини; звільнене hero місце показує негативне припущення."
}

{
  "before_pointer": "/page/blocks/1/body/items/0/subtitle",
  "after_pointers": [
    "/blocks/1/data/items/0/rules/0/formula",
    "/blocks/1/data/items/1/rules/0/formula"
  ],
  "before": "may arrive / may not know",
  "after": "Фрагменти збережені у формулах may arrive / might arrive та may not know / might not know.",
  "reason": "До коротких форм додано повні речення з підметом і перекладом; ствердження та заперечення мають окремі ролі."
}

{
  "before_pointer": "/page/blocks/1/body/items/1/subtitle",
  "after_pointers": [
    "/blocks/1/data/items/0/rules/0/formula",
    "/blocks/1/data/items/1/rules/0/formula"
  ],
  "before": "might arrive / might not know",
  "after": "Обидва фрагменти збережено в відповідних формулах.",
  "reason": "Стару назву «Менш певна можливість» замінено на нейтральну модель без обов’язкової шкали."
}

{
  "before_pointer": "/page/blocks/1/body/items/2/subtitle",
  "after_pointers": [
    "/blocks/1/data/items/3/rules/0/example",
    "/blocks/1/data/items/3/rules/1/example"
  ],
  "before": "May I come in? / May I ask something?",
  "after": "Обидва речення збережено з окремими перекладами.",
  "reason": "Розкрито роль мовця й порядок питання замість неперекладеної slash-пари."
}

{
  "before_pointer": "/page/blocks/1/body/items/3/subtitle",
  "after_pointers": [
    "/blocks/2/data/sections/4/examples/0",
    "/blocks/2/data/sections/4/examples/1"
  ],
  "before": "He may be late. / Maybe he is late.",
  "after": "Обидва речення збережено в повноцінному usage-пункті з єдиним теперішнім контекстом.",
  "reason": "Кожна конструкція отримує переклад; видима базова інформація не прихована в detail."
}

{
  "before_pointer": "/page/blocks/2/body/sections/0/examples/0",
  "after_pointer": "/blocks/2/data/sections/0/examples/0",
  "before": "She may be at home now.",
  "after": "She may be at home now.",
  "reason": "Збережено речення й точний переклад."
}

{
  "before_pointer": "/page/blocks/2/body/sections/0/examples/1",
  "after_pointer": "/blocks/2/data/sections/0/examples/1",
  "before": "We might miss the bus if we wait here.",
  "after": "We might miss the bus if we wait here.",
  "reason": "Збережено речення й умовну обставину; might не подається як минулий час."
}

{
  "before_pointer": "/page/blocks/2/body/sections/0/examples/2",
  "after_pointer": "/blocks/2/data/sections/0/examples/2",
  "before": "It may take longer than we think.",
  "after": "It may take longer than we think.",
  "reason": "Збережено речення й переклад без додаткового часово-модального зсуву."
}

{
  "before_pointer": "/page/blocks/2/body/sections/1/examples/0",
  "after_pointer": "/blocks/2/data/sections/1/examples/0",
  "before": "May I sit here?",
  "after": "May I sit here?",
  "reason": "Збережено зрозуміле прохання дозволу й переклад."
}

{
  "before_pointer": "/page/blocks/2/body/sections/1/examples/1",
  "after_pointer": "/blocks/2/data/sections/1/examples/1",
  "before": "You may use a dictionary during the test. — Ви можете користуватися словником під час тесту.",
  "after": "You may use a dictionary during the test. — Вам дозволено користуватися словником під час тесту.",
  "reason": "EN незмінне; UK уточнює дозвіл, а не здатність."
}

{
  "before_pointer": "/page/blocks/2/body/sections/1/examples/2",
  "after_pointer": "/blocks/2/data/sections/1/examples/2",
  "before": "Visitors may enter after 10 a.m.",
  "after": "Visitors may enter after 10 a.m.",
  "reason": "Речення й час 10-ї ранку збережено; це правило допуску."
}

{
  "before_pointer": "/page/blocks/2/body/sections/2/examples/0",
  "after_pointer": "/blocks/2/data/sections/2/examples/0",
  "before": "I may go out later.",
  "after": "I may go out later.",
  "reason": "Збережено майбутню можливість."
}

{
  "before_pointer": "/page/blocks/2/body/sections/2/examples/1",
  "after_pointer": "/blocks/2/data/sections/2/examples/1",
  "before": "I might go out later. — Я, може, вийду пізніше.",
  "after": "I might go out later. — Я, можливо, вийду пізніше.",
  "reason": "EN збережено; однакове можливо в парі не нав’язує кількісної різниці в шансах."
}

{
  "before_pointer": "/page/blocks/2/body/sections/2/examples/2",
  "after_pointer": "/blocks/2/data/sections/2/examples/4",
  "before": "He may know the answer, but he might stay quiet.",
  "after": "He may know the answer, but he might stay quiet.",
  "reason": "Обидва modal і протиставлення збережено; старий переклад лишається точним."
}

{
  "before_pointer": "/page/blocks/3/body/items/0/wrong",
  "after_pointer": "/blocks/3/data/items/0/wrong_en",
  "before": "Maybe be late.",
  "after": "He maybe late.",
  "reason": "Старий уривок без підмета допускає іншу інтерпретацію, тому не придатний для універсальної заборони. Нове повне за задумом висловлення з підметом He явно вимагає may be або перебудови Maybe he is late."
}

{
  "before_pointer": "/page/blocks/3/body/items/0/right",
  "after_pointers": [
    "/blocks/3/data/items/0/right_en",
    "/blocks/3/data/items/0/hint",
    "/blocks/2/data/sections/4/examples"
  ],
  "before": "✅ <span class=\"font-mono\">He may be late.</span> / <span class=\"font-mono\">Maybe he is late.</span>",
  "after": "He may be late. / Maybe he is late. — обидва збережено з перекладами без власних CSS-класів.",
  "reason": "Нові дані використовують підтримувані поля, не старе presentation HTML."
}

{
  "before_pointer": "/page/blocks/3/body/items/1/wrong",
  "after_pointer": "/blocks/3/data/items/1/wrong_en",
  "before": "She might to come later.",
  "after": "She might to come later.",
  "reason": "Справжню помилку форми збережено; додано задуманий UK переклад."
}

{
  "before_pointer": "/page/blocks/3/body/items/1/right",
  "after_pointer": "/blocks/3/data/items/1/right_en",
  "before": "✅ <span class=\"font-mono\">She might come later.</span>",
  "after": "She might come later.",
  "reason": "Речення незмінне; presentation-обгортку замінює штатний компонент, переклад додано."
}

{
  "before_pointer": "/page/blocks/3/body/items/2/wrong",
  "after_pointer": "/blocks/3/data/items/2/wrong_en",
  "before": "He may comes tomorrow.",
  "after": "He may comes tomorrow.",
  "reason": "Справжню помилку -s збережено; tomorrow не використано як алгоритм вибору модального."
}

{
  "before_pointer": "/page/blocks/3/body/items/2/right",
  "after_pointer": "/blocks/3/data/items/2/right_en",
  "before": "✅ <span class=\"font-mono\">He may come tomorrow.</span>",
  "after": "He may come tomorrow.",
  "reason": "Речення збережено без CSS-класів із точним перекладом."
}

{
  "before_pointer": "/page/blocks/4/body/items",
  "after_pointer": "/blocks/4/data/items",
  "before": "П’ять тез про можливість, формальніший дозвіл, обережність might, базову форму й may be / maybe.",
  "after": "П’ять компактних тез із явною відсутністю шкали, двома значеннями may not та роллю мовця; правило базової форми збережене в першій тезі.",
  "reason": "Не дублюємо всі приклади; виправлено ризик абсолютної ієрархії впевненості."
}

{
  "before_pointer": "/page/blocks/5/body",
  "after_pointer": "/blocks/5/data",
  "before": "Вісім navigation items категорії modal-verbs.",
  "after": "Ті самі вісім labels, URLs і current flags без змін.",
  "reason": "Збережено категорію modal-verbs і сусідні маршрути; їхній контент не редагується."
}

{
  "before_pointer": null,
  "after_pointers": [
    "/blocks/6",
    "/details/0"
  ],
  "before": "Native practice та point-level detail у definition відсутні.",
  "after": "Один practice-set: чотири selects і дві choices; один detail з двома поясненими контекстами.",
  "reason": "Механіки відповідають цілям форми та тлумачення. Detail поглиблює наслідки невизначеності, не приховує базове правило чи переклад."
}

### Primary source access

{
  "url": "https://dictionary.cambridge.org/grammar/british-grammar/may",
  "publisher": "Cambridge Dictionary — English Grammar Today",
  "checked_at": "2026-10-12",
  "access": "Direct open повернув HTTP 403. Прочитано індексований редакційний текст видавця May: forms та May: uses; це не повне пряме читання сторінки.",
  "checked_rules": [
    "Базова модальна форма, may not без do",
    "May для прохання, надання та відмови в дозволі",
    "Теперішня й майбутня можливість"
  ],
  "use": "Звірка правил; видавничі приклади не копіюються."
}

{
  "url": "https://dictionary.cambridge.org/grammar/british-grammar/might",
  "publisher": "Cambridge Dictionary — English Grammar Today",
  "checked_at": "2026-10-12",
  "access": "Direct open повернув HTTP 403. Доступний індексований редакційний текст Might: forms/uses, включно з формальним дозволом; не заявлено прямого повного доступу.",
  "checked_rules": [
    "Might not / mightn't",
    "Можливість не лише в минулому",
    "Might I — дуже формальне нечасте прохання дозволу; не стандартна відповідь Yes, you might"
  ],
  "use": "Коротка межа в basic; розгорнутий reported speech і may/might have не додаються."
}

{
  "url": "https://dictionary.cambridge.org/grammar/british-grammar/could-may-and-might",
  "publisher": "Cambridge Dictionary — English Grammar Today",
  "checked_at": "2026-10-12",
  "access": "Direct open повернув HTTP 403; прочитано індексований редакційний текст із застереженням про різні оцінки носіїв та роль інтонації.",
  "checked_rules": [
    "Немає універсальної фіксованої шкали певності",
    "Контекст і наголос впливають на відтінок"
  ],
  "use": "Збережено обережний можливий відтінок might без відсотків і без відхилення доречного may/might у вправах."
}

{
  "url": "https://dictionary.cambridge.org/grammar/british-grammar/can-could-or-may",
  "indexed_url": "https://dictionary.cambridge.org/grammar/british-grammar/can-could",
  "publisher": "Cambridge Dictionary — English Grammar Today",
  "checked_at": "2026-10-12",
  "access": "Заданий URL direct open повернув HTTP 403. Пошук повернув редакційну сторінку Can, could or may? під фактичним indexed URL /can-could; прочитано permission/requests. Не підмінюється прямим успіхом.",
  "checked_rules": [
    "Can/could/may для прохання дозволу",
    "Can/could you для прохання дії співрозмовника, не may you в цьому значенні"
  ],
  "use": "Розрізнення ролей у visible basic та шостому завданні, без універсальної заборони всіх послідовностей May you."
}

{
  "url": "https://dictionary.cambridge.org/grammar/british-grammar/maybe-or-may-be",
  "publisher": "Cambridge Dictionary — English Grammar Today",
  "checked_at": "2026-10-12",
  "access": "Direct open повернув Internal Error без доступного HTTP-коду. Прочитано індексований редакційний текст Maybe or may be?; це не пряме повне читання.",
  "checked_rules": [
    "Maybe — прислівник одним словом",
    "May be — modal + основне або допоміжне be, два слова"
  ],
  "use": "Повні авторські речення з явним підметом замість неоднозначного старого уривка."
}

{
  "url": "https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/probability",
  "publisher": "British Council LearnEnglish",
  "checked_at": "2026-10-12",
  "access": "Пряме відкриття успішне. Прочитано редакційні Possibility, Impossibility, Certainty; коментарі учнів і дискусії під уроком не використано як нормативну основу.",
  "checked_rules": [
    "May/might/could для можливого, але не певного",
    "Припущення може стосуватися теперішнього або майбутнього",
    "Непевний заперечний результат не дорівнює неможливості"
  ],
  "use": "Звірка базової логіки; власні ситуації та старі definition-приклади, не вправи видавця."
}

### Historical pre-freeze review notes

# M49 — May / Might: редакційна чернетка

Main pre-freeze normalization: hero intro починається з точного topic `May / Might`, щоб чинний PageMetadata не додавав повторний topic-prefix. Навчальний зміст, приклади, tasks, aliases та detail ownership від цього не змінюються; це зроблено до freeze.

Статус: **draft-for-editorial-review**, не frozen і не застосовано до БД. Ця робота створює лише дані одного уроку, без дизайну, engine, definitions або сусідніх сторінок.

## Точний owner і межі

Identity: `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsMayMightTheorySeeder`.
Locale: `uk`; category ancestry: `modal-verbs`; slug/H1: `may-might` / `May / Might`.
Очікуваний локальний URL: `http://gramlyze.loc/theory/modal-verbs/may-might`.
Display A2-B1, technical block A2. Actual Page ID, physical target, DB apply та HTTP приймання перевіряє основний процес; авторська чернетка не видає ці кроки за виконані.

Старі шість slots збережено в тому самому порядку з їхніми type/column/level; navigation не отримує level. Новий сьомий slot — один native practice-set. У footer збережено всі вісім навігаційних посилань. Окрему таблицю не додано: для цієї теми короткі зіставлення вже пояснюються у відповідних пунктах і не потребують повтору.

## Потреба читача й редакційне рішення

Читачеві потрібні три розрізнення: можливість або дозвіл; невизначений негативний результат або відмова в дозволі; модальна конструкція may be або прислівник maybe. Чотири картки форми й п’ять іменованих usage-пунктів подають ці відмінності без великого довідника всіх уживань модальних.

Might не прирівняно до минулого, а можливий обережніший відтінок не перетворено на шкалу відсотків. У першому й четвертому завданнях два модальні варіанти свідомо приймаються. Читач бачить may not у двох явно різних ситуаціях. Might I зазначено як дуже формальну нечасту модель у видимому basic, а не оголошено неможливим. May you не навчається як звичайне прохання іншої людини діяти; застереження обмежене саме цим значенням.

Кожне навчальне EN речення має UK переклад. Для wrong-рядків переклад позначає задум, а не твердження, що помилкова конструкція нормативна. У практиці EN stimuli та умови залишаються до check; повні відповіді, їхні переклади й коротке пояснення — після check. Переклади A/B у п’ятому завданні є самим предметом семантичного вибору, не зайвим ключем.

## Збереження старих прикладів

Точна поелементна карта міститься в `editorial_changes` JSON. `body` у BEFORE pointers означає декодований JSON із відповідного source block, не зміну його формату.

- Усі шість EN речень із трьох старих hero slash-пар збережено, але рознесено між hero, forms та usage за змістом. Кожне отримало окремий переклад.
- Обидва питання `May I come in?` і `May I ask something?` збережено у формах.
- `He may be late.` та `Maybe he is late.` збережено у visible basic, з єдиним контекстом зустрічі, яка вже починається.
- Усі дев’ять EN прикладів із старих usage sections збережено дослівно. UK `Ви можете користуватися словником…` уточнено до `Вам дозволено…`; `Я, може, вийду…` вирівняно до `Я, можливо, вийду…` без вигаданої різниці у шансах.
- Дві справжні помилки `She might to come later.` та `He may comes tomorrow.` і їхні виправлення збережено. CSS-обгортки старого `right` не переносяться у нові дані; їхню функцію виконує чинний компонент.
- Старий `Maybe be late.` **не збережено як універсальний wrong**: це неповний уривок без підмета. Нове `He maybe late.` задає прозорий задум повідомлення, де бракує дієслова; виправлення `He may be late.` та альтернатива `Maybe he is late.` не змінюють виконавця або час.

## Basic і єдине поглиблення

Basic самодостатній: форми, переклади, permission roles, Might I, відсутність fixed percentages і два значення may not видно одразу.

Один detail `may-might-not-uncertainty-not-impossibility` належить `may-might-uses /sections/3`. Два фрагменти розбирають різний стан знання про графік: невизначеність і вже відому зайнятість протягом зустрічі. Це окремий розбір того, який висновок можна зробити, а не прихований переклад або коротка ремарка. Він не робить кожне can't логічною неможливістю та не перетворює урок на Modals of Deduction.

## Практика за навчальною метою

Шість завдань, шість required controls, 16 видимих варіантів: чотири selects по три варіанти й дві choices по два. Canonical answers — шість; прийнятних правильних видимих варіантів — вісім через дві відкриті may/might альтернативи.

| № | Тип | Мета | Canonical / accepted |
|---|---|---|---|
| 1 | select | Невпевнене припущення, не штучний відсоток | may / may, might |
| 2 | select | Базова форма без to та -s | arrive |
| 3 | select | Повний присудок may be, не maybe | may be |
| 4 | select | Негативна можливість, не повідомлення розкладу як факту | may not be / may not be, might not be |
| 5 | choice | May not у правилі бібліотеки | a |
| 6 | choice | Дозвіл для власної дії, правильний підмет | b |

Усі 16 видимих значень мають власний exact-key feedback. Дві правильні відповіді в одному control не створюють двох балів. Не додаються нові JS-поля, типи чи спеціальна normalization. Ручне введення та sentence builder не потрібні для цих шести вузьких цілей; вони створили б зайві лексичні неоднозначності. Стандартні aliases, retry/reset та scoring ще мають бути перевірені основним процесом перед freeze; їх не оголошено PASS за читанням коду.

## Первинні джерела й фактичний доступ

Власні нові приклади не копіюють видавничих вправ. Старі приклади походять із definition, а не з нового web-запозичення.

- [Cambridge: May](https://dictionary.cambridge.org/grammar/british-grammar/may): direct HTTP 403; прочитано індексований редакційний текст forms/uses. Перевірено базову форму, may not, прохання/надання/відмову в дозволі та теперішню/майбутню можливість.
- [Cambridge: Might](https://dictionary.cambridge.org/grammar/british-grammar/might): direct HTTP 403; індексований редакційний текст. Перевірено might not / mightn't, теперішню/майбутню можливість і формальне нечасте Might I. Пряма повна сторінка недоступна.
- [Cambridge: Could, may and might](https://dictionary.cambridge.org/grammar/british-grammar/could-may-and-might): direct HTTP 403; індексоване редакційне застереження про відмінні оцінки носіїв, інтонацію та наголос. Воно не дає підстав встановлювати постійні відсотки.
- [Заданий Cambridge URL Can, could or may](https://dictionary.cambridge.org/grammar/british-grammar/can-could-or-may): direct HTTP 403; пошук повернув [фактичну індексовану сторінку /can-could](https://dictionary.cambridge.org/grammar/british-grammar/can-could). Перевірено permission vs requests. Indexed не названо direct full access.
- [Cambridge: Maybe or may be](https://dictionary.cambridge.org/grammar/british-grammar/maybe-or-may-be): direct Internal Error без видимого HTTP-коду; індексований редакційний текст. Перевірено прислівник одним словом і modal + be двома словами.
- [British Council: Probability](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/probability): direct успішний; прочитано основні редакційні розділи Possibility, Impossibility, Certainty. Learner comments та дискусії під сторінкою не використовувалися як норма.

## Межі цієї чернетки

Не виконано DB apply, HTTP/browser acceptance, engine runs, freeze або commit. Не змінено definition, Blade, CSS, JS, master M48, shared adapters, banks чи git state. Під час main review окремо звірити переклади всіх feedback, початковий порядок шести continuing slots і фактичні destinations старих navigation links.
