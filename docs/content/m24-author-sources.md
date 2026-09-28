# M24 — авторські джерела та редакторська перевірка

Дата підготовки: 28 вересня 2026 року. База репозиторію: `cf8f3a1380415b32206e0f781d0e403bb4ca9544`.

## Що є джерелом, а що написано заново

Назви, рівні, структура чинних сторінок та їхні навчальні напрями взяті з трьох `definition.json` у перевіреній базі GitHub. Повний учнівський текст M24, англійські приклади, українські переклади, ситуації, вправи та пояснені ключі написані ChatGPT для цього пакета. Це погоджене авторське розширення й уточнення, не дослівний переказ старих файлів і не копіювання зовнішніх статей. Персонажі та події — вигадані навчальні ситуації.

Codex не доручено виконувати нову мовну редактуру. Технічна точність перенесення й мовна правильність — різні перевірки: контрольні суми та порівняння DOM доводять збереження наданого тексту, а не незалежне підтвердження граматики.

## Репозиторні джерела

- [Present Perfect vs Present Perfect Continuous](https://github.com/Pvitaly91/engapp-codex/blob/cf8f3a1380415b32206e0f781d0e403bb4ca9544/database/seeders/Page_V3/Tenses/TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder/definition.json), blob `df1a0cb8bed476a9dfa19cdc7b8fe4e11955cb80`.
- [Narrative Tenses](https://github.com/Pvitaly91/engapp-codex/blob/cf8f3a1380415b32206e0f781d0e403bb4ca9544/database/seeders/Page_V3/Tenses/TensesNarrativeTensesTheorySeeder/definition.json), blob `6828ee2b65e7d50c2d1d78fa2cc150a3482ae325`.
- [B1 Mixed Revision](https://github.com/Pvitaly91/engapp-codex/blob/cf8f3a1380415b32206e0f781d0e403bb4ca9544/database/seeders/Page_V3/BasicGrammar/BasicGrammarB1MixedRevisionTheorySeeder/definition.json), blob `55f0fad7a52a16736348bccc1c42b04e431f389e`.
- Чинний `LinkingWordsContentPatch` очікує лише три українські DB-блоки й два source-блоки. Це не структура M24: у трьох sources їх відповідно 8, 6 і 4 до автоматичного subtitle. Звичайне копіювання M23 wrapper непридатне.
- Чинний `JsonPageSeeder::seedPageDefinition` під час повного сідингу видаляє попередні блоки сторінки. Запускати його на робочій БД заради M24 не дозволено.
- Native comparison-table має колонки англійського тексту, українського перекладу та примітки. Нові `rows.en/ua/note` заповнені відповідно до цього контракту, а не довільними назвами колонок.

## Зовнішня перевірка правил

Адреси й стан доступу нижче стосуються цієї підготовки. Для Cambridge частина прямих відкриттів повернула 403 або помилку отримання; використано доступний індексований матеріал самого видавця. Це не заявляється як пряме прочитання повних статей. Учнівські коментарі не є нормативною основою. Короткі відповіді команди LearnEnglish відокремлено від основного матеріалу.

| Код | Першоджерело | Використаний зміст і межа доступу |
| --- | --- | --- |
| S1 | [British Council — Present perfect simple and continuous](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/present-perfect-simple-continuous) | Основний текст прочитано: результат/активність, тривалі стани, for/since. Окремо перевірено редакційні відповіді Peter M. від 26–27.08.2026 про кількість у Continuous; запитання учня не використано як правило. |
| S2 | [Cambridge — Present perfect simple or continuous](https://dictionary.cambridge.org/us/grammar/british-grammar/present-perfect-simple-or-present-perfect-continuous) | Індексований текст: можливість обох форм із live, звичайні значення дієслів стану. Пряме відкриття неуспішне. |
| S3 | [Cambridge — Present perfect: typical errors](https://dictionary.cambridge.org/ko/%EB%AC%B8%EB%B2%95/british-grammar/present-perfect-typical-errors) | Індексований англомовний текст видавця: for для тривалості, since для початкової точки; використано тільки ці правила. |
| S4 | [Cambridge — Past simple or present perfect](https://dictionary.cambridge.org/us/grammar/british-grammar/past-perfect-simple-or-past-) | Індексований текст: конкретна завершена минула подія проти періоду до сьогодні. Нетиповий slug не підміняє перевірку фактичного заголовка статті. |
| S5 | [British Council — Past continuous and past simple](https://learnenglish.britishcouncil.org/free-resources/grammar/a1-a2/past-continuous-past-simple) | Основний текст прочитано: was/were + -ing, did + базова форма, зміна часового прочитання при зміні Simple/Continuous. |
| S6 | [Cambridge — Past perfect simple](https://dictionary.cambridge.org/grammar/british-grammar/past-perfect-i-) | Індексований текст: had + V3 і минула точка відліку. Окремо [Oxford — Past perfect simple](https://www.oxfordlearnersdictionaries.com/grammar/online-grammar/past-perfect-simple) підтверджує форми та ранішу подію; доступ через індексований текст. |
| S7 | [British Council — Past perfect](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/past-perfect) | Доступний індексований текст основного пояснення й окремої відповіді Jonathan R. від 18.11.2022: інші слова можуть уже прояснювати порядок, тому Simple не завжди є помилкою. Не переносимо це як дозвіл довільно замінювати всі perfect-форми. |
| S8 | [British Council — Future continuous and future perfect](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/future-continuous-future-perfect) | Основний текст прочитано: процес у майбутній точці й завершення до межі; у M24 лише коротке повторення. |
| S9 | [British Council — Reported speech: questions](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-questions) | Основний текст прочитано: if/whether, порядок твердження, ask + object + to. Відповідь Peter M. від 28.06.2026 про контекстний backshift перевірена окремо. |
| S10 | [Cambridge — Reported speech: indirect speech](https://dictionary.cambridge.org/uk/grammar/british-grammar/reported-speech-indirect-speech) | Індексований текст: команди, not to, займенники, backshift та випадки без нього. Пряме відкриття базової адреси дало 403. |
| S11 | [Cambridge — Passive: forms](https://dictionary.cambridge.org/us/grammar/british-grammar/passive-forms) | Індексована таблиця modal + be + V3. Використано конкретну просту пасивну модель, не всі форми таблиці. |
| S12 | [Cambridge — Have something done](https://dictionary.cambridge.org/ja/grammar/british-grammar/have-something-done) | Індексований англомовний текст: замовлена дія, порядок have + object + V3. У M24 контекст замовлення задано прямо; не стверджується, що всі такі конструкції завжди означають замовлення. |
| S13 | [British Council — Non-defining relative clauses](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/relative-clauses-non-defining-relative-clauses) | Основний текст доступний: додаткова інформація про вже визначену особу, коми, who. |
| S14 | [British Council — Contrasting ideas](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/contrasting-ideas-although-despite-others) | Основне пояснення й редакційне уточнення: although + clause, despite + noun/-ing. Власний приклад не копіює вправ видавця. |
| S15 | [British Council — Wishes: wish and if only](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/wishes-wish-if-only) | Доступний індексований основний текст: бажаний інший теперішній стан і жаль про минуле. |
| S16 | [British Council — Conditionals: third and mixed](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/conditionals-third-mixed) | Основний текст прочитано; для B1-повторення використано лише звичайну третю умовну модель. |
| S17 | [British Council — Modals: deductions about the present](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/modals-deductions-about-present) | Доступний індексований основний текст: контекстні must/might/can’t для висновку; не встановлюємо універсальні відсотки ймовірності. |

## Підтверджені уточнення, а не прихована заміна теми

**Perfect comparison.** Збережено початкові напрями: результат, досвід, процес, форми, дієслова стану й алгоритм вибору. Нове пояснення не прирівнює будь-який Simple до закінченого стану та будь-який Continuous до дії саме цієї секунди. Початкову заборону Continuous із числом замінено контекстним розмежуванням роботи над об’єктами й підрахунку готових результатів. Слова for/since не використовуються як автоматичний перемикач часу.

**Narrative.** Збережено Simple/Continuous/Perfect, базові форми, when/while/before/after і структуру вибору. Приклади, що можуть мати інше граматичне прочитання, не подаються як безумовні помилки. Блок помилок тепер містить однозначні помилки форми; зміна значення між правильними варіантами пояснюється окремо. Past Perfect не приписується кожній дії, яка відбулася раніше за іншу.

**B1 revision.** Усі шість початкових напрямів залишено: perfect/narrative, future, passive/causative, reported speech, relative/contrast, wish/third conditional/deduction. Принцип single canonical answer уточнено як обмеження конкретного тренажера або інструкції, не як твердження про всі природні англійські речення. Шість вправ — компактна вибіркова самоперевірка, а не заявлений іспит по всьому курсу.

## Редакторський перегляд усіх 18 відповідей

| Урок / № | Перевірка ключа |
| --- | --- |
| Perfect 1 | `has packed four` дає готову кількість; `has been packing` не вигадує кількості Марти. |
| Perfect 2 | For + twenty minutes; since + September; know лишається простою формою. |
| Perfect 3 | `have known` зберігає стан від понеділка до зараз; жодного факту забуття пароля. |
| Perfect 4 | Недавня активність не суперечить зупинці; не приписано готовності всіх стільців чи незавершеності кожного. |
| Perfect 5 | Live допускає обидва варіанти в заданому контексті; намір переїхати не доданий. |
| Perfect 6 | Вступ завершений, другий розділ ще ні; дві години збережені. |
| Narrative 1 | Три послідовні дієслова Simple; учасник і порядок незмінні. |
| Narrative 2 | `was sorting` на тлі `arrived`; факт припинення сортування не домислюється. |
| Narrative 3 | Courier left before arrival; exact clock times may be omitted because the instruction explicitly requests only the relative order. |
| Narrative 4 | Обидва after-варіанти допустимі; відрізняється акцент, не фактична послідовність. |
| Narrative 5 | Did + open; had + gone; інші відомості не змінені. |
| Narrative 6 | Someone не перетворено на Olena; розмова лишається процесом, після якого не домислюється завершення. |
| B1 1 | Present Perfect vs Past Perfect розведені за часом; Past Perfect прямо заданий інструкцією. |
| B1 2 | О 15:00 процес, до 17:00 завершення; майбутній план не видано за виконаний факт. |
| B1 3 | Asked me if I was; told me not to; збережені мовець, адресат і негативна вказівка. Виконання вказівки не стверджується. |
| B1 4 | `The volunteers` → `by the volunteers`; must не перетворено на has been. Causative має явно заданий контекст замовлення і завершення. Labeled допускається як AmE spelling. |
| B1 5 | Конкретна відома Marta — дві коми; agreed не перетворюється на вже допомогла. Although збережено, зайве but прибрано. |
| B1 6 | Wish — теперішнє бажання; third conditional — явно уявна альтернатива; might — невідоме місце як можливість, не факт. |

Авторська перевірка не гарантує відсутності будь-якої можливої мовної неточності. За конкретної змістовної суперечності Codex має навести уривок і зупинити застосування цього пакета, не переписувати текст мовчки.

## Два контрольовані навігаційні рішення

1. Narrative: наявний label `Past Perfect` отримує `/theory/past-perfect` замість старого короткого `/theory/past-perfect/past-perfect-forms`. Це наперед визначене посилання на тематичний розділ, не здогад Codex. Реальний resolver і HTTP 200 необхідно підтвердити локально перед apply. Невдача — конкретний конфлікт, а не дозвіл шукати довільну заміну.
2. B1: безадресні chips замінено трьома точними посиланнями на сторінки M24. Навчальні теми повідомленої мови, модальних форм і зв’язок не видалені: вони залишилися в основних поясненнях. Кнопки окремого Mixed-тесту не дублюються й не змінюються.

Усі решта paths — source-derived або вже явно задані в payload. Під час цієї підготовки не було доступу до локального Apache користувача: жоден `.loc` HTTP-результат не приписується авторові пакета.
