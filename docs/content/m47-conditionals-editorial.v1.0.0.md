# M47 — редакторський BEFORE/AFTER 1.0.0

База: `02f016a7def826c159b555b1ed5b992259e90461`. Незалежний source/DB/HTTP BEFORE отримано до definition sync; дані збережені окремо від generated AFTER.

Потреби читача — редакційні, не виміряні keyword volumes або позиції.

## third-conditional

### Потреби читача й відмінність

[
  "Зрозуміти, що змінюємо вже завершену минулу ситуацію, а не прогнозуємо майбутнє.",
  "Зібрати умову й результат, не загубивши had, have або V3.",
  "Утворити заперечення, пряме питання й коротку відповідь; правильно поставити кому.",
  "Розрізнити ’d = had/would і зміст could/might have без фіксованих відсотків.",
  "Не плутати конкретний минулий епізод із загальною звичкою або минулий результат із теперішнім.",
  "Потренувати шість визначених цілей без вгадування прихованої авторської фрази."
]

### Редакторські рішення

BEFORE: {"source_pointer":"/page/blocks/0/body/rules/0/example","en":"If we had left earlier, we would have caught the train."}

AFTER: {"status":"retained","locations":["/blocks/0/data/rules/0/example"]}

Причина: Англійське речення збережено дослівно; додано або збережено окремий український переклад.

BEFORE: {"source_pointer":"/page/blocks/0/body/rules/1/example","en":"If she had studied more, she would have passed."}

AFTER: {"status":"retained","locations":["/details/0/fragment/data/sections/0/examples/1/en"]}

Причина: Речення збережено у контрасті конкретної минулої підготовки із загальною звичкою; прибрано його дублювання в hero.

BEFORE: {"source_pointer":"/page/blocks/0/body/rules/2/example","en":"If I had listened, I wouldn't have made that mistake."}

AFTER: {"status":"retained","locations":["/blocks/2/data/sections/0/examples/2/en"]}

Причина: Англійське речення збережено дослівно; додано або збережено окремий український переклад.

BEFORE: {"source_pointer":"/page/blocks/1/body/items/0/subtitle","en":"If he had called, I would have answered."}

AFTER: {"status":"retained","locations":["/blocks/1/data/items/0/rules/0/example"]}

Причина: Англійське речення збережено дослівно; додано або збережено окремий український переклад.

BEFORE: {"source_pointer":"/page/blocks/1/body/items/1/subtitle","en":"I would have helped if I had known."}

AFTER: {"status":"retained","locations":["/blocks/1/data/items/3/rules/0/example"]}

Причина: Англійське речення збережено дослівно; додано або збережено окремий український переклад.

BEFORE: {"source_pointer":"/page/blocks/1/body/items/2/subtitle","en":"If they hadn't missed the bus, they would have been on time."}

AFTER: {"status":"retained","locations":["/blocks/1/data/items/1/rules/0/example"]}

Причина: Англійське речення збережено дослівно; додано або збережено окремий український переклад.

BEFORE: {"source_pointer":"/page/blocks/1/body/items/3/subtitle","en":"If you had asked, I might have joined."}

AFTER: {"status":"retained","locations":["/blocks/7/data/rows/2/en"]}

Причина: Дослівно перенесено з картки форми в порівняння модальностей; додано переклад і факти, щоб відокремити можливий результат від певного.

BEFORE: {"source_pointer":"/page/blocks/2/body/sections/0/examples/0/en","en":"If I had left home earlier, I would have caught the plane.","ua":"Якби я вийшов з дому раніше, то встиг би на літак."}

AFTER: {"status":"retained","locations":["/blocks/2/data/sections/0/examples/0/en"]}

Причина: Англійське речення збережено дослівно; додано або збережено окремий український переклад.

BEFORE: {"source_pointer":"/page/blocks/2/body/sections/0/examples/1/en","en":"If she had taken the map, she wouldn't have got lost.","ua":"Якби вона взяла карту, то не заблукала б."}

AFTER: {"status":"retained","locations":["/blocks/2/data/sections/0/examples/1/en"]}

Причина: Англійське речення збережено дослівно; додано або збережено окремий український переклад.

BEFORE: {"source_pointer":"/page/blocks/2/body/sections/1/examples/0/en","en":"If we had booked sooner, we would have paid less.","ua":"Якби ми забронювали раніше, то заплатили б менше."}

AFTER: {"status":"retained","locations":["/blocks/2/data/sections/1/examples/0/en"]}

Причина: Англійське речення збережено дослівно; додано або збережено окремий український переклад.

BEFORE: {"source_pointer":"/page/blocks/2/body/sections/1/examples/1/en","en":"If they had listened to the warning, they would have avoided the problem.","ua":"Якби вони послухали попередження, то уникнули б проблеми."}

AFTER: {"status":"retained","locations":["/blocks/2/data/sections/1/examples/1/en"]}

Причина: Англійське речення збережено дослівно; додано або збережено окремий український переклад.

BEFORE: {"source_pointer":"/page/blocks/2/body/sections/2/examples/0/en","en":"If you had checked the file, you would have seen the typo.","ua":"Якби ти перевірив файл, то побачив би помилку."}

AFTER: {"status":"retained","locations":["/blocks/2/data/sections/2/examples/0/en"]}

Причина: Англійське речення збережено. Переклад typo уточнено до «друкарську помилку», а не загального «помилку».

BEFORE: {"source_pointer":"/page/blocks/2/body/sections/2/examples/1/en","en":"If I had known about the delay, I would have stayed at home longer.","ua":"Якби я знав про затримку, то довше залишився б удома."}

AFTER: {"status":"retained","locations":["/blocks/2/data/sections/2/examples/1/en"]}

Причина: Англійське речення збережено дослівно; додано або збережено окремий український переклад.

BEFORE: {"source_pointer":"/page/blocks/3/body/items/0/wrong","en":"If I would have known, I would have called."}

AFTER: {"status":"retained","locations":["/blocks/3/data/items/0/wrong","/blocks/3/data/items/0/wrong_en"]}

Причина: Збережено як невідповідність конкретній стандартній навчальній моделі, з уточненням контексту та меж правила; не оголошено глобальним blacklist.

BEFORE: {"source_pointer":"/page/blocks/3/body/items/0/right","en":"If I had known, I would have called."}

AFTER: {"status":"retained","locations":["/blocks/3/data/items/0/right","/blocks/3/data/items/0/right_en"]}

Причина: Англійське речення збережено дослівно; додано або збережено окремий український переклад.

BEFORE: {"source_pointer":"/page/blocks/3/body/items/1/wrong","en":"If she studied more, she would have passed."}

AFTER: {"status":"reclassified","locations":["/details/0/fragment/data/sections/0/examples/0/en"]}

Причина: Вилучено з безумовного списку помилок і збережено в поглибленні з явним можливим mixed-контекстом загальної звички, актуальної також під час минулого іспиту.

BEFORE: {"source_pointer":"/page/blocks/3/body/items/1/right","en":"If she had studied more, she would have passed."}

AFTER: {"status":"retained","locations":["/details/0/fragment/data/sections/0/examples/1/en"]}

Причина: Речення збережено у контрасті конкретної минулої підготовки із загальною звичкою; прибрано його дублювання в hero.

BEFORE: {"source_pointer":"/page/blocks/3/body/items/2/wrong","en":"If I had more money yesterday, I would be happier now."}

AFTER: {"status":"replaced","locations":[],"replacement_locations":["/blocks/8/data/rows/0","/blocks/8/data/rows/1"]}

Причина: Стару безконтекстну пару wrong/right вилучено. Для явно нереальної конкретної минулої умови задано had had; результат now збережено в окремому Mixed-прикладі. Then показано окремо як інше повідомлення, а не мовчазне виправлення now.

BEFORE: {"source_pointer":"/page/blocks/3/body/items/2/right","en":"If I had had more money yesterday, I would have been happier then."}

AFTER: {"status":"retained","locations":["/blocks/8/data/rows/0/en"]}

Причина: Збережено як самостійний Third-приклад із минулим результатом, а не як «виправлення» іншого повідомлення з результатом now.

BEFORE: {"scope":"Форми","text":"Не було прямих питань, короткої відповіді, пояснення ’d та різниці had/have."}

AFTER: {"locations":["/blocks/1/data/items/2","/blocks/6/data"]}

Причина: Додано відсутні типові потреби читача через чинні forms-grid та usage-panels.

BEFORE: {"scope":"Практика","text":"У шести первинних блоках practice-set відсутній."}

AFTER: {"locations":["/blocks/9/data"],"tasks":6,"required_controls":6}

Причина: Додано шість самостійно створених завдань: V3, заперечення, питання, час результату, значення та справжня помилка допоміжного дієслова.

BEFORE: {"scope":"Навігація","text":"10 посилань, включно з поточною Third та Mixed."}

AFTER: {"locations":["/blocks/5/data"],"status":"unchanged"}

Причина: Повний чинний список і destinations збережено; доречне Third → Mixed посилання також є в часовому поясненні.

BEFORE: {"scope":"Редакційна чернетка","owner":"third-uses /sections/1"}

AFTER: {"owner":"third-uses /sections/2","basic_note":"Для конкретного завершеного епізоду умову оформлюємо як had + V3. Past Simple може натомість описувати уявну загальну звичку, актуальну й на час події; це інший, mixed-контекст, а не автоматична помилка."}

Причина: Контраст загальної звички та конкретного минулого епізоду належить пункту про аналіз після події. Необхідну межу правила залишено видимою в basic, окреме поглиблення пояснює приклади.

### Перевірені джерела

- [British Council](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/conditionals-third-mixed) — Повну сторінку відкрито; використано редакційне пояснення Third/Mixed, не коментарі користувачів.

- [Cambridge English Grammar Today](https://dictionary.cambridge.org/grammar/british-grammar/mixed-conditionals) — Прочитано доступний індексований редакційний текст через пошук. Пряме відкриття основної адреси conditionals-if дало HTTP 403; повного прямого читання не заявлено.

- [Cambridge English Grammar Today](https://dictionary.cambridge.org/grammar/british-grammar/contractions) — Прочитано індексований редакційний матеріал через пошук; пряме повне відкриття не заявлено.

- [Cambridge English Grammar Today](https://dictionary.cambridge.org/us/grammar/british-grammar/past-perfect-simple-i-had-worked) — Прочитано індексований редакційний матеріал через пошук; пряме повне відкриття не заявлено.

- [Cambridge English Grammar Today](https://dictionary.cambridge.org/grammar/british-grammar/conditional-sentences) — Прочитано індексований редакційний матеріал через пошук; пряме повне відкриття не заявлено.


## mixed-conditionals

### Потреби читача й відмінність

[
  {
    "need": "Зрозуміти час обох частин до запам’ятовування формули.",
    "covered_by": [
      "mixed-hero",
      "mixed-forms",
      "mixed-time-comparison"
    ],
    "basis": "Редакційна потреба, визначена зі старого уроку й завдання користувача; це не твердження про пошукові метрики."
  },
  {
    "need": "Не трактувати нинішню нову рису як причину вчорашньої події.",
    "covered_by": [
      "mixed-usage/sections/1",
      "mixed-practice/choices/1"
    ],
    "basis": "У старому поясненні межа між сталою рисою й новою теперішньою зміною була неявною."
  },
  {
    "need": "Розрізняти помилку форми та граматичну конструкцію з іншим часовим задумом.",
    "covered_by": [
      "mixed-mistakes",
      "mixed-time-clarifications",
      "mixed-current-state-completed-boundary"
    ],
    "basis": "Три старі категоричні виправлення потребують контексту й явної редакційної заміни."
  },
  {
    "need": "Самостійно обирати форму, заперечення, питальний порядок і модальний відтінок.",
    "covered_by": [
      "mixed-grammar-controls",
      "mixed-practice"
    ],
    "basis": "Відсутня у старому уроці власна інтерактивна практика; завдання охоплюють різні рішення учня."
  }
]

### Редакторські рішення

BEFORE: Mixed Conditionals поєднують різні часові плани, коли причина стосується одного часу, а результат - іншого. / Минулі причини і теперішні результати або теперішні умови та минулі результати. Hero: if-clause Past Perfect + would; if-clause Past Simple + would have; «Логіка важливіша за форму».

AFTER: subtitle_html/subtitle_text і mixed-hero ставлять на перше місце час умови й результату. Збережено обидва корисні hero-приклади про ранній сон та організованість, додано окремі переклади й контексти. Формули пояснюються в mixed-forms.

Причина: Нова теперішня умова не повинна виглядати причиною вже минулої події; сталість риси й її актуальність тоді мають бути видимі одразу. Hero не повторює повну таблицю форм.

BEFORE: Чотири items: «Минуле -> теперішнє»; «Теперішнє -> минуле»; «Фокус» / «причина і результат у різному часі»; «Не змішуй без причини» / «форму підказує значення».

AFTER: mixed-forms має дві реальні модельні картки. Старі приклади про навчання медицини та уважність збережено дослівно, додано переклади й часовий контекст. Поради про логіку перенесено до hero, summary та comparison.

Причина: Дві редакційні ремарки не є ще двома формами й не потребують окремих form-карток. Нових візуальних компонентів немає.

BEFORE: «Минула причина вже завершилася, але результат відчувається зараз». «Теперішня умова пояснює, чому минулий результат був саме таким». «Використовуй mixed forms лише тоді, коли проста друга або третя умовна не передає правильний часовий зв'язок». Шість прикладів: accepted job; booked hotel; more confident; better at maths; taken course; more prepared.

AFTER: Усі шість англійських прикладів і їхні українські переклади збережено в mixed-usage. Опис кожного пункту тепер задає конкретні факти; для more prepared названо тривалу недостатню підготовленість і вчорашній збій. Нотатка пояснює інший можливий Third-фокус на підготовці до одного випадку.

Причина: Без контексту другу модель можна помилково зрозуміти як вплив нової сьогоднішньої риси на минуле. Не потрібно оголошувати Third і Mixed взаємно виключними без уточнення задуму.

BEFORE: Wrong: If I had gone to bed earlier, I would have felt better now. Right: If I had gone to bed earlier, I would feel better now. Hint: «Теперішній результат вимагає would + base verb».

AFTER: Колишній right збережено в hero й mixed-time-clarifications/sections/1 для прямо названого нинішнього стану. Колишній wrong більше не позначається універсально неграматичним. Visible basic розрізняє стан зараз і попередній/завершений до межі результат; один point-detail розгортає це на прозорій парі про перебування біля воріт і прибуття до них.

Причина: Для задуму «почуваюся зараз» would feel є ясною навчальною моделлю. Саме now не забороняє perfect-форму в інших контекстах; не приписуємо старому реченню прихований задум і не робимо глобальної заборони.

BEFORE: Wrong: If it had rained, we would play inside. Right: If it rained, we would play inside. / If it had rained, we would have played inside. Hint: «Обери ту форму, яка реально відповідає часу умови та результату».

AFTER: mixed-time-clarifications/sections/0 має явний контекст нічного дощу й сьогоднішньої гри та модель If it had rained last night, we would play inside today. Поруч — окремий Third-приклад про вчорашній дощ і вчорашню гру.

Причина: Оригінальна mixed-послідовність можлива, коли минулий дощ має пізніший наслідок. Відсутність часових обставин у вирваному реченні не доводить помилку. Порівнюємо різні змісти, не механічно виправляємо час.

BEFORE: Title: «Забувати, що лише одна частина змінює час». Wrong: If she had studied more, she would have a better mark now and yesterday. Right: If she had studied more, she would have a better mark now. Hint: «Mixed conditional має чіткий часовий фокус: або теперішній результат, або минулий результат».

AFTER: mixed-time-clarifications/sections/2 прямо розділяє отримання вищої оцінки вчора та кращий записаний результат зараз: If she had studied more, she would have received a higher mark yesterday and would have a better result on her record now. Нотатка розрізняє lexical have і perfect have + V3.

Причина: Не видаляємо yesterday мовчки й не стверджуємо, що одна умова не може мати кількох результатів. Старий стан «мати оцінку» міг тривати в обох часових точках; новий приклад явно визначає інший, двокомпонентний задум. Твердження про «лише одну частину» вилучено як хибне узагальнення.

BEFORE: Три items old mistakes-grid оцінювали would have felt better now, had rained / would play та better mark now and yesterday як помилки без достатнього контексту.

AFTER: mixed-mistakes містить три однозначні помилки: had went → had gone; would have understand → would have understood; Where you would…? → Where would you…? У кожній парі додано переклад задуму.

Причина: Часові тонкощі винесено в семантичний розбір, де альтернативи не спотворюються. Блок помилок тепер тренує справді некоректну форму чи прямий питальний порядок.

BEFORE: «Минула причина -> теперішній результат»; «Теперішня умова -> минулий результат»; «Змішана форма лише тоді, коли умова й результат справді різного часу»; «Спочатку часова логіка, потім форма».

AFTER: mixed-summary зберігає обидві схеми й алгоритм, але називає другу умову сталою рисою, актуальною тоді; додає коротку перевірку модальності й контексту.

Причина: Підсумок має відтворювати уточнену логіку, не повертати категоричне або двозначне старе формулювання.

BEFORE: У старих шести слотах немає окремого порівняння Second/Third/Mixed, системного блоку заперечень/питань/модальності й practice-set.

AFTER: Після старих слотів додано mixed-time-comparison, mixed-grammar-controls, mixed-time-clarifications і mixed-practice у вже наявних native types. Одна meaningful detail належить точному пункту про стан/межу. Практика: два select для форм і чотири choice для часової інтерпретації, сталої риси, питання та модальності.

Причина: Додано відсутні читацькі потреби без нової верстки чи механіки. Кількість типів вправ визначає навчальна мета, не копія шаблону «3+3».

BEFORE: Старі індекси 0..5: hero/header/B2; forms-grid/left/B2; usage-panels/left/B2; mistakes-grid/right/B2; summary-list/right/B2; navigation-chips/footer без level.

AFTER: Ті самі індекси, типи, колонки й технічні рівні збережено; navigation data, URLs та current flags не змінено. Видимий рівень hero лишається B2-C1.

Причина: Стабільна ідентичність старих блоків та посилань; draft не переписує чужі сторінки, банки або курс.

BEFORE: Чернетковий вступ починався з «У Mixed Conditionals»; title sidecar містив повторне «Докладніше:».

AFTER: Вступ починається назвою теми; sidecar title — «стан зараз і завершення до цього моменту» без дублювання стандартної кнопки.

Причина: Чинний metadata service сам додає назву теми до intro, яке не починається нею; стандартний disclosure уже має свій підпис. Жодної зміни змісту, стилів або metadata infrastructure.

### Перевірені джерела

- [British Council LearnEnglish](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/conditionals-third-mixed) — Пряме успішне читання редакційного матеріалу після перенаправлення на free-resources; коментарі користувачів не використовувалися.

- [Cambridge Dictionary, English Grammar Today](https://dictionary.cambridge.org/grammar/british-grammar/mixed-conditionals) — Обидві прямі спроби open повернули HTTP 403. Прочитано первинний редакційний текст Cambridge у пошуковому індексі за URL mixed-conditionals; це не успішне пряме читання сторінки.

- [Cambridge Dictionary, English Grammar Today](https://dictionary.cambridge.org/grammar/british-grammar/could) — Прочитано первинний індексований редакційний текст у результаті пошуку; окреме пряме відкриття цього URL не виконувалося.

- [Cambridge Dictionary, English Grammar Today](https://dictionary.cambridge.org/grammar/british-grammar/might) — Прочитано первинний індексований редакційний текст у результаті пошуку; окреме пряме відкриття цього URL не виконувалося.

- [Cambridge Dictionary](https://dictionary.cambridge.org/dictionary/english/by-now) — Прочитано індексоване словникове визначення Cambridge; пряме відкриття не виконувалося.


## unless-provided-as-long-as

### Потреби читача й відмінність

{
  "editorial_not_search_statistics": true,
  "goals": [
    "Обрати сполучник у короткому повідомленні, дозволі або попередженні.",
    "Розрізнити виняток, явну умову, тривалість і підготовку про запас.",
    "Не змінити заперечення, модальність і час під час перефразування.",
    "Побачити будову майбутньої умови, запитання й обох порядків частин."
  ],
  "neighbor": {
    "identity": "Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWithUnlessProvidedAsLongAsTheorySeeder",
    "slug": "conditionals-with-unless-provided-as-long-as",
    "url": "/theory/conditionals/conditionals-with-unless-provided-as-long-as",
    "source": "database/content-patches/m31-m15-conditionals.v1.json",
    "projection": "database/content-patches/m42-native-design.v1.json",
    "registry": "database/content-patches/m42-native-design-registry.v1.json",
    "difference": "Цей B1–B2 урок — компактний довідник вибору сполучника з виправленням старих false-error прикладів, короткими картками будови та новими ситуаціями. M31 B2 зберігає розгорнуту перевірку меж перефразування, зокрема відомого минулого контрфакту; її приклади бронювання, студії, камери, атласу, ліхтаря, проєктора, Єви й ключа не запозичені."
  },
  "basic_detail_decision": "0 details: усі шість змістових пунктів, застереження та переклади потрібні для основного вибору; вони короткі. Розгорнуті межі заміни if ... not не дублюємо, а пов’язуємо з сусіднім B2 уроком."
}

### Редакторські рішення

BEFORE: <p><strong>Unless / Provided / As long as</strong> дають альтернативи до <strong>if</strong> і допомагають точніше показати умову та її межі.</p>

AFTER: subtitle_html

Причина: Замість загального «альтернативи до if» названо практичні завдання без обіцянки автоматичної взаємозаміни.

BEFORE: <strong>Unless</strong>, <strong>provided</strong> і <strong>as long as</strong> часто замінюють <strong>if</strong>, але не значать абсолютно одне й те саме. Вони додають ідею <strong>if not</strong>, <strong>тільки якщо</strong> або <strong>доки умова виконується</strong>.

AFTER: m47-unless-hero /intro

Причина: Прибрано злиття умовного as long as із часовим «доки». Hero дає орієнтир, не повторює весь basic.

BEFORE: Unless you hurry, you'll miss the bus.

AFTER: m47-unless-hero /rules/0/example

Причина: Англійське речення збережено дослівно; додано окремий повний український переклад.

BEFORE: You can borrow the car provided that you fill the tank.

AFTER: m47-unless-hero /rules/1/example

Причина: Англійське речення збережено дослівно; додано окремий повний український переклад.

BEFORE: You can stay here as long as you are quiet.

AFTER: m47-unless-hero /rules/2/example

Причина: Англійське речення збережено дослівно; додано окремий повний український переклад.

BEFORE: Unless it rains, we'll go out.

AFTER: m47-unless-meanings /rows/0/en та /ua

Причина: Корисний приклад збережено дослівно; тепер стоїть у штатній English-колонці з окремим перекладом, а note пояснює значення.

BEFORE: Provided that you study, you'll improve.

AFTER: m47-unless-meanings /rows/1/en та /ua

Причина: Корисний приклад збережено дослівно; тепер стоїть у штатній English-колонці з окремим перекладом, а note пояснює значення.

BEFORE: You can join us as long as you are ready by six.

AFTER: m47-unless-meanings /rows/2/en та /ua

Причина: Корисний приклад збережено дослівно; тепер стоїть у штатній English-колонці з окремим перекладом, а note пояснює значення.

BEFORE: If you need help, call me.

AFTER: m47-unless-meanings /rows/3/en та /ua

Причина: Корисний приклад збережено дослівно; тепер стоїть у штатній English-колонці з окремим перекладом, а note пояснює значення.

BEFORE: ⚠ Після <strong>unless</strong> не додаємо ще одне заперечення. Фраза <strong>unless you don't...</strong> майже завжди є помилкою.

AFTER: m47-unless-meanings /warning; m47-unless-uses /sections/1

Причина: Вилучено абсолютне/майже абсолютне оголошення unless + not помилкою. Контекстно потрібний негативний виняток показано окремо, без зміни звичайного if not-перефразування.

BEFORE: Використовуй <strong>unless</strong>, коли хочеш коротко сказати <strong>if not</strong>.

AFTER: m47-unless-uses /sections/0/description та /note

Причина: Замість поради просто скорочувати if not пояснено виняток і необхідність зберігати результат/модальність.

BEFORE: {"en":"Unless you book today, you won't get the discount.","ua":"Якщо ти не забронюєш сьогодні, не отримаєш знижку."}

AFTER: m47-unless-uses /sections/0/examples/0

Причина: EN/UK пару збережено дослівно.

BEFORE: {"en":"We can't start unless everyone is here.","ua":"Ми не можемо почати, якщо не всі тут."}

AFTER: m47-unless-uses /sections/0/examples/1

Причина: EN/UK пару збережено дослівно.

BEFORE: Ці форми звучать сильніше, ніж звичайне <strong>if</strong>, і підкреслюють умову-допуск.

AFTER: m47-unless-uses /sections/2 та /sections/3

Причина: Provided і as long as розведено за цілями; прибрано загальну шкалу «сильніше за if». Added providing (that) і неуніверсальний регістр.

BEFORE: {"en":"You can work from home provided that your internet is stable.","ua":"Ти можеш працювати з дому за умови, що в тебе стабільний інтернет."}

AFTER: m47-unless-uses /sections/2/examples/0

Причина: Приклад роботи з дому й переклад збережені дослівно.

BEFORE: {"en":"I'll support the plan as long as the budget stays realistic.","ua":"Я підтримаю план, якщо бюджет залишиться реалістичним."}

AFTER: m47-unless-uses /sections/3/examples/0

Причина: Англійське речення збережено. UK «за умови, що» виразно позначає умовне, не часове прочитання.

BEFORE: <strong>As long as</strong> може означати не лише умову, а й часове <strong>доки</strong>. Контекст вирішує значення.

AFTER: m47-unless-uses /sections/3 та /sections/4

Причина: Корисний контраст збережено, але кожне значення має явний власний контекст.

BEFORE: {"en":"You can stay as long as you like.","ua":"Ти можеш залишатися скільки хочеш."}

AFTER: m47-unless-uses /sections/4/examples/0

Причина: EN/UK пару збережено дослівно; додано явний контекст вибору тривалості візиту.

BEFORE: {"en":"As long as the door is locked, the alarm stays off.","ua":"Поки двері замкнені, сигналізація не вмикається."}

AFTER: m47-unless-uses /sections/4/examples/1

Причина: Заміна дверей/сигналізації: старе речення допускає умовне й часове прочитання та непрозору логіку системи. Нова розповідь про завершений музичний виступ прямо перевіряє тривалість.

BEFORE: У формальному стилі <strong>provided that</strong> звучить природніше за звичайне <strong>if</strong>.

AFTER: m47-unless-uses /sections/2/note

Причина: Provided that не є обов’язково природнішим за if у всіх формальних текстах. Залишено реальний відтінок регістру, без оцінки if як помилки.

BEFORE: {"label":"Помилка 1","color":"rose","title":"Подвійне заперечення з <strong>unless</strong>.","wrong":"Unless you don't hurry, you'll be late.","right":"Unless you hurry, you'll be late.","hint":"<strong>Unless</strong> уже містить значення <strong>if not</strong>."}

AFTER: m47-unless-mistakes /items/0

Причина: Обидва старі English candidates збережено, додано переклади й явний намір. Проблема визначена як зміна потрібного винятку, не універсальна «подвійна негація».

BEFORE: {"label":"Помилка 2","color":"amber","title":"Плутати умову з запобіжністю.","wrong":"Take an umbrella unless it rains.","right":"Take an umbrella in case it rains.","hint":"<strong>In case</strong> виражає запобіжну дію, а не умову допуску."}

AFTER: m47-unless-mistakes /items/1; m47-unless-uses /sections/5

Причина: Обидва старі English candidates збережено; перед ними названо запобіжну мету. Unless визнано граматичним, але не відповідним цій меті; повні переклади відокремлено.

BEFORE: {"label":"Помилка 3","color":"sky","title":"Використовувати надто нейтральне <strong>if</strong>, коли потрібне сильніше обмеження.","wrong":"You can use my laptop if you return it tonight.","right":"You can use my laptop provided that you return it tonight.","hint":"<strong>Provided that</strong> краще передає значення суворої умови."}

AFTER: m47-unless-meanings /rows/4 та /rows/5

Причина: Обидва старі речення перенесено дослівно до нейтрального порівняння з перекладами: if не стає помилкою через можливу альтернативу provided that.

BEFORE: ["<strong>Unless</strong> = <strong>if not</strong>.","<strong>Provided</strong> і <strong>as long as</strong> підкреслюють сильнішу або чіткішу умову.","Не став додаткове заперечення після <strong>unless</strong>.","Якщо йдеться про запобіжність, а не про умову, частіше потрібне <strong>in case</strong>."]

AFTER: m47-unless-summary /items

Причина: Замість повторної заборони not і загальної «сильнішої умови» — коротка перевірка наміру, заперечення/модальності та типу зв’язку, з посиланнями First/B2.

BEFORE: [{"label":"Zero Conditional","url":"/theory/conditionals/zero-conditional","current":false},{"label":"First Conditional","url":"/theory/conditionals/first-conditional","current":false},{"label":"Second Conditional","url":"/theory/conditionals/second-conditional","current":false},{"label":"Third Conditional","url":"/theory/conditionals/third-conditional","current":false},{"label":"Mixed Conditionals","url":"/theory/conditionals/mixed-conditionals","current":false},{"label":"Unless / Provided / As long as","url":"/theory/conditionals/unless-provided-as-long-as","current":true},{"label":"In case","url":"/theory/conditionals/in-case","current":false},{"label":"Wish / If only","url":"/theory/conditionals/wish-if-only","current":false},{"label":"Would rather","url":"/theory/conditionals/would-rather","current":false},{"label":"Had better","url":"/theory/conditionals/had-better","current":false}]

AFTER: m47-unless-navigation /items

Причина: Усі 10 старих destinations та current-позначка збережені; додано один exact registry-verified B2 destination без redirect/canonical змін.

BEFORE: null

AFTER: m47-unless-forms

Причина: Додано 3 штатні картки конструкцій: звичайний майбутній час, заперечний результат, запитання про дозвіл, заперечна вимога та два порядки частин із комою.

BEFORE: null

AFTER: m47-unless-practice

Причина: Додано шість власних коротких завдань (2 select + 4 choice), 6 controls, 14 options. Нові English stimuli не копіюють M31 або видавничі вправи; повні ключі/переклади лише у штатному post-check feedback.

### Перевірені джерела

- [Граматичне джерело](https://dictionary.cambridge.org/grammar/british-grammar/unless) — Direct web open returned 403 Forbidden; official indexed text read through web search, not full-page open.

- [Граматичне джерело](https://dictionary.cambridge.org/grammar/british-grammar/conditionals-other) — Direct web open returned 403 Forbidden; official indexed text read through web search. Initial descriptive alias conditionals-other-expressions-unless-should-as-long-as also returned 403; canonical indexed URL recorded here.

- [Граматичне джерело](https://dictionary.cambridge.org/grammar/british-grammar/as-long-) — Direct web open returned 403 Forbidden; official indexed text read through web search. Initial descriptive alias as-long-as-and-so-long-as also returned 403.

- [Граматичне джерело](https://dictionary.cambridge.org/us/grammar/british-grammar/in-case-of) — Direct web open returned 403 Forbidden; official indexed text read through web search.

## SEO-орієнтир

[Google Search Central](https://developers.google.com/search/docs/fundamentals/creating-helpful-content): прочитано редакційну настанову, орієнтир — користь і точність для читача, самостійні приклади та зрозумілі пояснення, без штучної кількості слів і заяв про ranking lift.
