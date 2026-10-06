# M39 — M23 Nominal Style / C1 Mixed Revision / C2 Mixed Revision

Дата локальної роботи: 2026-10-06. Scope: три exact accepted M23 UK theory owners, тільки `http://gramlyze.loc`.

Зміни застосовано до робочого `gramlyze.loc`: 3 оновлення / 21 вставка / 0 видалень. Авторський master незмінний, усі 18 original cases збережені. Postconditions та повторний no-op пройдено; браузерні результати наведено нижче окремо від ізольованих тестів.

## Версія та межі

Accepted base: `a525a5e03b59904b9fe5987abacf5c038fe5293f`, гілка `codex/seo-m38-m22-articles-collocations-layers`.
Робоча гілка: `codex/seo-m39-m23-authored-revision-layers`.
Ізольований checkout: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`; actual application/document root: `D:/DEV/htdocs/gramlyze.loc`.

M39 є технічною інтеграцією готового авторського тексту. Не змінює правила, приклади, переклади, дати, числа, виконавців, modality, voice, causality, outcomes або банк тестів. Primary dirty checkout не reset/stash/clean; його чужі зміни не є commit source. Main/PR/merge main/force push/deploy/workflow dispatch, dependency update, Apache/XAMPP/hosts, migrations/seeds/full restore/cache/session clear поза scope. Production `.com`/`.ub` не запитується й не змінюється.

`git merge-base --is-ancestor a525a5e03b59904b9fe5987abacf5c038fe5293f HEAD` — exit 0. ROOT HEAD лишається `41820a2bebdf69004fa7209a2a38457f93efabbd`; сторонній PPC checkout/staged work не використовували як commit source. У ROOT перенесено тільки finite M39 sources і точкові reviewed shared hunks, зокрема збережено сторонні PPC зміни в practice-set.

На пізніший прямий запит користувача «Запусти сервер» дозволено запуск наявного Apache, але не зміну його налаштувань. Перевірка наявної конфігурації дала `Syntax OK`; виконано hidden Start-Process, повторний запуск відхилений уже зайнятим портом. Fresh GET поза мережевою ізоляцією sandbox підтвердив HTTP200. Apache/XAMPP/hosts/config files не редагували. Помилка curl(7) із sandbox не використовується як доказ стану мережі поза ним.

## Immutable M23 author master та author policy

Первинне джерело: `docs/content/m23-authored-content.v1.json`, editorial revision 1.0.0, authored_on 2026-09-28.

| Ідентичність | Exact value |
| --- | --- |
| Immutable master Git blob | `34a03a7146141fbff50c66ec8e41f3fc2a59b787` |
| Declared SHA-256, UTF-8 Git-LF bytes | `eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6` |
| Actual Windows working CRLF raw SHA-256 | `3d919d5894ac37a11a107d31075078c419394a0b3f537f21e303523bd4213c32` |
| Frozen notes path | `docs/content/m23-author-sources.md` |
| Frozen notes Git blob | `859c4263cb00c0ff318bf2b41f3e450ca65efc0d` |
| Actual Windows notes raw SHA-256 | `afe9ec2d6a5f10b496fc0fdcabcea1e546599f3063af8bbb1fd7d4ba34e78ffa` |

Git-LF SHA не названо raw-file SHA: Windows checkout має CRLF. Git-LF bytes реконструюють exact accepted blob; actual Windows bytes перевірено окремо й не EOL-normalized/перезаписано. Обидва frozen author files лишаються незмінними.

Policy `rewrite`, `summarise`, `translate_again`, `add_examples`, `add_exercises`, `production_write`, `mixed_question_bank_write` — **false**, exact перевірено. Codex authorized for technical integration only. Presentation окрема від master.

У served ROOT ці author documents історично відсутні й не копіюються туди. Versioned before manifest містить exact Git-LF source snapshot з bound SHA/blob: це копія immutable bytes, не новий learner text. Коли actual file існує в CLI/tests/worktree, його перевіряють незалежно; коли ROOT runtime не має документів, bound exact snapshot забезпечує той самий author contract без private-worktree dependency. Tests actual-source drift відхиляють і не ремонтують джерело.

## Exact targets, casing та source fidelity gate

Повний префікс identities: `Database\Seeders\Page_V3\`.

| Theory URL | Identity suffix | Category root / level / page ID | Page.title / actual H1 | Accepted definition blob |
| --- | --- | --- | --- | --- |
| [Nominal Style and Information Density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density) | `FormalEnglish\NominalStyleAndInformationDensityTheorySeeder` | `formal-english` / C2 / 321 | `Nominal Style and Information Density` / `Nominal style and information density` | `cef77f1e1ab2493515c3db39cb51b39b06d81330` |
| [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision) | `BasicGrammar\C1MixedRevisionTheorySeeder` | `mixed-revision` / C1 / 329 | `C1 Mixed Revision` / `C1 Mixed Revision` | `c6c1c404e3a61763a8f97bb7edf0278779bee85d` |
| [C2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision) | `BasicGrammar\C2MixedRevisionTheorySeeder` | `mixed-revision` / C2 / 330 | `C2 Mixed Revision` / `C2 mixed revision` | `9702e82b33b3aab38e5a7653d412e006622ebb64` |

Канонічні definitions:

- `database/seeders/Page_V3/FormalEnglish/NominalStyleAndInformationDensityTheorySeeder/definition.json`.
- `database/seeders/Page_V3/BasicGrammar/C1MixedRevisionTheorySeeder/definition.json`.
- `database/seeders/Page_V3/BasicGrammar/C2MixedRevisionTheorySeeder/definition.json`.

Nominal accepted category payload не має `title`; його не дописують за здогадом. Actual category ID 55 має `Formal English`; дві Mixed pages мають category ID 59, `Mixed Revision`. Root ancestry, locale=uk/type=theory, page title/strong H1 casing, subtitle, hero, tags та relations збережено, без cross-owner/category copy.

Fresh SELECT-only gate перед canonical edits підтвердив master → accepted definition → actual DB exact для всіх трьох owners: original bodies, subtitle HTML/text, hero, heading, Page.title, identities/categories. Версії не reconciled і не обиралися навмання.

| Owner | Exact decoded author body SHA-256 | Original box ID / UUID |
| --- | --- | --- |
| Nominal | `ebcba2cc42076b5be3ff0e271dd0e3546ef7d258cccfa61c5c6714f72ebcf8b0` | 8852 / `c8faed66-75dc-59fe-a5b4-e966a0ed706c` |
| C1 Mixed | `2d4f2f06c44a12366202a105fbf2b3c78dd87cd3c62c59648394ede0c075f84f` | 8889 / `2196918e-903f-5992-ad93-08e73907ae06` |
| C2 Mixed | `58c39d1cc3a95aa449b6c74a7beb68bdf568b06980a04bfa4b5b95fd15c3dbca` | 8892 / `af1d7d05-4db8-55b7-90d3-c18b1e2726e9` |

All original UUIDs are non-null and protected. Frozen before SHA-256: `76c445345923c1007c09806072a8a7421cdcab835bf492c1573b92c1d7c1a433`. Finite projection SHA-256: `bbfe17773121f6d13cda6beda3d15b5285c54fe73360c605045080e8beddb817`.

## Native structure і повноцінний basic

Кожен owner має 8 native sections, незмінні subtitle та hero. Nominal: 5 usage-panels + comparison-table (section 3) + practice-set + summary-list. C1: 5 usage-panels + comparison-table (section 1) + practice-set + summary-list. C2: 6 usage-panels + practice-set + summary-list; **таблиці немає**, бо її не має author source. Разом 9 definition blocks / 10 expected DB rows із subtitle, не hardcoded permission на write count.

Дві accepted tables мають exact 3 columns / 3 data rows, min-width 720px / column minimums 240px і власний local horizontal scroll. No invented C2 comparison table, no hidden duplicate of the old whole box. Native UUID keys deterministic `m39-*`; first existing row protected fields retained except agreed type/body.

Повний basic зберігає core rule, центральні приклади, translations, factual boundaries, participant/time/modality, tables і practice до click. Source intro, hero labels/text/examples, section headings, ordered paragraphs, table cells, examples/translations, all 18 nested `<li><p>` author cases/keys, internal/external links не переписано. Нормалізація fidelity лише entities/NBSP/presentation wrappers/whitespace boundaries; не пунктуація, casing, numbers, modal verbs, negation або ordering.

Legacy self-check anchors збережено:

- `self-check-nominal-style-and-information-density`.
- `self-check-c1-mixed-revision`.
- `self-check-c2-mixed-revision`.

Repo та actual DB anchor-reference inventory зафіксований у `m39-before-v1.json`; exact plan review перевірив збереження старих anchors. After/no-op SELECT evidence підтверджує ті самі anchors, owner metadata та bank relations. Live deep-fragment checks наведені в браузерній секції.

## Detail-quality audit — усі 18 candidates

Candidate кожної author section 1–6 — exact final paragraph, `point=source-final-paragraph`. Decisions semantic та finite, не word-count runtime filter. Усі `visible_basic`, hidden `detail=""`; zero retained meaningful details. Longer paragraphs також core/continuation, а не автоматичне поглиблення через довжину.

| Page | Section | Point | basic_word_count | detail_word_count | detail_sentence_count | Decision | Semantic reason |
| --- | ---: | --- | ---: | ---: | ---: | --- | --- |
| Nominal | 1 | source-final-paragraph | 118 | 23 | 1 | visible_basic | Коротке продовження теми інформаційного фокусу, не independent depth. |
| Nominal | 2 | source-final-paragraph | 109 | 50 | 3 | visible_basic | Exact actor/object і конкретні of/by-моделі пояснюють саме базове перетворення. |
| Nominal | 3 | source-final-paragraph | 105 | 27 | 3 | visible_basic | Межа planned/process/result та застереження проти доданого успіху — core table meaning. |
| Nominal | 4 | source-final-paragraph | 113 | 31 | 3 | visible_basic | Головне слово, agreement і читабельність потрібні поруч з основними прикладами. |
| Nominal | 5 | source-final-paragraph | 133 | 27 | 3 | visible_basic | Два різні noun-chain meanings не можна приховати як стилістичний додаток. |
| Nominal | 6 | source-final-paragraph | 164 | 44 | 4 | visible_basic | Аналіз трьох невиконаних статусів є центральним контролем повного абзацу. |
| C1 Mixed | 1 | source-final-paragraph | 114 | 31 | 2 | visible_basic | Meaning-first table та additional factual boundaries не mechanical replacements. |
| C1 Mixed | 2 | source-final-paragraph | 134 | 26 | 3 | visible_basic | Would/could/might змінюють точну модальність; це essential caveat. |
| C1 Mixed | 3 | source-final-paragraph | 130 | 22 | 2 | visible_basic | Only-subject scope проти only-after визначає саме правило інверсії. |
| C1 Mixed | 4 | source-final-paragraph | 148 | 32 | 3 | visible_basic | Defining subgroup, commas і who без duplicate subject — основна модель. |
| C1 Mixed | 5 | source-final-paragraph | 119 | 30 | 3 | visible_basic | Could-request/deadline function пояснює центральний приклад, не optional depth. |
| C1 Mixed | 6 | source-final-paragraph | 111 | 39 | 2 | visible_basic | Paragraph checklist і коротка therefore-reminder завершують той самий basic. |
| C2 Mixed | 1 | source-final-paragraph | 99 | 22 | 2 | visible_basic | Untested ≠ failed — головне смислове розмежування. |
| C2 Mixed | 2 | source-final-paragraph | 113 | 25 | 2 | visible_basic | Specific Had-model scope, не універсальна інверсія довільних дієслів. |
| C2 Mixed | 3 | source-final-paragraph | 134 | 20 | 3 | visible_basic | Reporting negation scope визначає саме твердження. |
| C2 Mixed | 4 | source-final-paragraph | 127 | 32 | 3 | visible_basic | Not all/none та unknown exact distribution потрібні до click. |
| C2 Mixed | 5 | source-final-paragraph | 111 | 28 | 3 | visible_basic | Singular assessment та incomplete ≠ defective пояснюють primary example. |
| C2 Mixed | 6 | source-final-paragraph | 201 | 34 | 3 | visible_basic | Коротка ремарка про інші формулювання/атрибуцію, не окремий author detail. |

Disclosures **0 / 0 / 0**. Short candidates <30 words: **3 / 2 / 4 = 9**, усі лишено visible basic. Counts diagnostic only; 30 exactly не рахують як <30. New teaching material для кнопок не створено.

## Усі 18 original interactive author cases

На кожній сторінці: 2 selects (cases 1/2), 2 choices (3/4), 2 token/manual inputs (5/6), exact own-bank widget. Кожний original case/всі subparts збережено once. Full author HTML у `author_self_check`; нижче читабельний plain-text виклад з тими самими словами, перекладами та explanatory keys.

### [Nominal Style and Information Density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density#self-check-nominal-style-and-information-density)

1. **select** — Головне слово. Обери is або are та поясни вибір. The gradual expansion of the reading rooms ___ scheduled for autumn. — Поступове розширення читальних зал заплановане на осінь.

   Ключ: The gradual expansion of the reading rooms is scheduled for autumn. — Поступове розширення читальних зал заплановане на осінь. Головне слово expansion в однині, тому is. Множина rooms належить до уточнення. Запланованість не означає початку чи завершення робіт.

2. **select** — Та сама можливість. Перепиши речення, почавши з A reassessment. Збережи виконавця, час і may. The committee may reassess the exhibition plan in October. — Комітет, можливо, повторно оцінить план виставки в жовтні.

   Ключ: A reassessment of the exhibition plan by the committee may take place in October. — Повторна оцінка плану виставки комітетом може відбутися в жовтні. Збережено комітет, план виставки, жовтень і можливість події. Will take place підсилює твердження, а took place робить подію минулим фактом — обидва варіанти змінюють умову. Інше природне формулювання з may допустиме, якщо починається заданими словами й не втрачає виконавця.

3. **choice** — Де з’явився новий факт? Чи є друга версія точним перефразуванням першої? Назви додані відомості й запропонуй точну редакцію з іменником reduction. The team plans to reduce waiting times. — Команда планує скоротити час очікування. The reduction in waiting times has improved the service. — Скорочення часу очікування поліпшило обслуговування.

   Ключ: Ні. Друга версія додає, що час уже скоротився та обслуговування поліпшилося. У першій є лише план. The team plans a reduction in waiting times. — Команда планує скорочення часу очікування. Це зразок точної редакції. У ній немає твердження про досягнутий результат.

4. **choice** — Не загуби учасників. Перепиши з іменником assessment, зберігши куратора, предмет оцінювання та понеділок. The curator assessed the insurance documents on Monday. — Куратор оцінив страхові документи в понеділок.

   Ключ: The curator’s assessment of the insurance documents took place on Monday. — Оцінювання страхових документів куратором відбулося в понеділок. Куратор залишається виконавцем, документи — предметом оцінювання. Допустимо: The assessment of the insurance documents by the curator took place on Monday. — «Оцінювання страхових документів куратором відбулося в понеділок». Не додавай висновку, що документи схвалені.

5. **token/manual** — Початок не дорівнює завершенню. Перепиши двома реченнями, почавши перше з The digitisation. Збережи обидва часові повідомлення й виконавців. The volunteers began to digitise the catalogue in June. The work is still in progress. — Волонтери почали оцифровувати каталог у червні. Робота ще триває.

   Ключ: The digitisation of the catalogue by the volunteers began in June. The work is still in progress. — Оцифровування каталогу волонтерами почалося в червні. Робота ще триває. Збережено початок і незавершений статус. Was completed in June — «було завершене в червні» — суперечить умові. Інше розміщення by the volunteers можливе, якщо ролі зрозумілі.

6. **token/manual** — Відредагуй нотатку. Відомо тільки таке: команда запропонувала розширення залу; рішення ще не ухвалене; новий час очікування ніхто не вимірював. Заміни неточне речення двома-трьома точними; використай proposal або expansion. The successful implementation of the expansion has reduced waiting times. — Успішне здійснення розширення скоротило час очікування.

   Ключ: The team has proposed an expansion of the hall. No decision has been made, and the new waiting times have not been measured. — Команда запропонувала розширення залу. Рішення ще не ухвалене, а новий час очікування ще не вимірювали. Це зразок відповіді. Прибрано вигадані виконання, успіх і скорочення часу. Два чи три речення допустимі; конкретні додаткові виконавці, дати й результати — ні.

### [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision#self-check-c1-mixed-revision)

1. **select** — Минуле → теперішнє. Технік не зберіг налаштування, і зараз ми не можемо їх відновити. Побудуй задану уявну альтернативу: почни з If our technician had saved the settings і використай would be able to для результату тепер.

   Ключ: If our technician had saved the settings, we would be able to restore them now. — Якби наш технік зберіг налаштування, ми могли б відновити їх зараз. Збереження — минула уявна зміна; можливість відновлення — теперішній результат. Would have been able без іншого контексту переносить результат у минуле й не виконує задану модель. Речення не повідомляє про фактичне збереження.

2. **select** — Only after та пасив. Перепиши, почавши з Only after. Збережи обидві часові форми та пасив у головній частині. The boxes were dispatched only after the supervisor had approved the labels. — Коробки відправили лише після того, як керівник схвалив етикетки.

   Ключ: Only after the supervisor had approved the labels were the boxes dispatched. — Лише після того, як керівник схвалив етикетки, коробки відправили. У підрядній частині the supervisor had approved без інверсії. У головній were the boxes dispatched, не did the boxes dispatched. Пасив і попередність схвалення збережені.

3. **choice** — Повідомлення про ранішу подію. Почни з The envelope; збережи is believed та часовий зв’язок. It is believed that the envelope was opened before delivery. — Вважають, що конверт відкрили до доставки.

   Ключ: The envelope is believed to have been opened before delivery. — Вважають, що конверт було відкрито до доставки. Повідомлення з is believed залишається теперішнім; відкриття передувало доставці. Конверт зазнав дії, тому to have been opened. Хто відкрив конверт, не встановлено й не додано.

4. **choice** — Які саме гіди? Із п’ятьох гідів двоє завершили курс. Саме ці двоє зараз проводять екскурсію. Напиши одне речення з The two guides та означальною частиною who have completed the course. Не додавай неперевірених відомостей про трьох інших.

   Ключ: The two guides who have completed the course are leading the tour. — Двоє гідів, які завершили курс, проводять екскурсію. Означальна частина без ком ідентифікує двох із заданої групи. Are leading передає дію зараз. Не стверджуємо, що троє інших пішли, відмовилися або не здатні завершити курс.

5. **token/manual** — Не роби припущення фактом. Відомо, що Лейла могла надіслати чернетку вчора, але поштову скриньку ще не перевіряли. Виправ надмірно категоричну нотатку, використавши may have. Leila sent the draft yesterday. We have not checked the mailbox. — Лейла надіслала чернетку вчора. Ми ще не перевірили поштову скриньку.

   Ключ: Leila may have sent the draft yesterday, but we have not checked the mailbox. — Можливо, Лейла надіслала чернетку вчора, але ми ще не перевірили поштову скриньку. Подія не оголошена фактом. Might have sent і could have sent також можуть передавати можливість у цьому контексті, але завдання прямо просить may have. Must have без додаткових підстав невиправдано підсилює висновок.

6. **token/manual** — Точність і ввічливий запит. Відомості: учора перевірили лише дати в каталозі; описи ще не перевіряли; остаточний файл потрібно попросити до п’ятниці. Перепиши нотатку трьома реченнями без нових фактів. Останнє почни з Could you. The catalogue was checked yesterday. Therefore, every description is certainly correct. Please to send the final file by Friday. — Каталог перевірили вчора. Отже, кожний опис безумовно правильний. Надішліть, будь ласка, остаточний файл до п’ятниці.

   Ключ: The dates in the catalogue were checked yesterday. The descriptions have not yet been checked. Could you send the final file by Friday? — Дати в каталозі перевірили вчора. Описи ще не перевіряли. Чи могли б ви надіслати остаточний файл до п’ятниці? Це зразок відповіді. Відокремлено перевірені дати від неперевірених описів; прибрано безпідставний висновок і неправильне please to send. Строк та прохання збережені. Інше формулювання перших двох речень допустиме, якщо містить ті самі відомості.

### [C2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision#self-check-c2-mixed-revision)

1. **select** — Інверсія без зміни можливості. Перепиши без if, почавши з Had. Збережи заперечення та could have. If the guide had not brought a spare lamp, we could have been stranded underground. — Якби провідник не взяв запасну лампу, ми могли б застрягти під землею.

   Ключ: Had the guide not brought a spare lamp, we could have been stranded underground. — Якби провідник не взяв запасну лампу, ми могли б застрягти під землею. Not стоїть після підмета. Could have been stranded зберігає можливий уявний наслідок. Не замінюємо could на would і не повідомляємо, що група справді застрягла.

2. **select** — Перфектний пасивний інфінітив. Почни з The portraits, зберігши теперішній статус повідомлення й порядок подій. It is thought that the portraits were moved before the gallery closed. — Вважають, що портрети перемістили до того, як галерея зачинилася.

   Ключ: The portraits are thought to have been moved before the gallery closed. — Вважають, що портрети було переміщено до закриття галереї. Для множини portraits потрібне are thought. Портрети зазнали дії, тому to have been moved. Залишено попередність щодо закриття й непідтверджений статус думки; виконавця переміщення не вигадано.

3. **choice** — Чи достатньо відомостей? А) Марта надрукувала другий розклад, хоча в цьому не було потреби. Б) У другому повідомленні сказано лише Marta didn’t need to print a second timetable. — «Марті не потрібно було друкувати другий розклад». Для А напиши речення з needn’t have. Поясни, чому для Б така заміна без додаткового контексту не гарантовано точна.

   Ключ: Marta needn’t have printed a second timetable. — Марта даремно надрукувала другий розклад: у цьому не було потреби. В А відомі і виконання, і непотрібність. Повна форма need not have printed також підходить. У Б відома лише відсутність необхідності; факт друку не заданий. Тому не можна без перевірки додати до Б значення виконаної дії.

4. **choice** — Обсяг заперечення. Відомо тільки: Not all five samples were usable. — «Не всі п’ять зразків були придатними». Яке твердження гарантоване: а) кожний непридатний; б) принаймні один непридатний; в) рівно один непридатний? Назви також те, чого ця умова не встановлює.

   Ключ: Гарантоване б): принаймні один зразок був непридатним. Не встановлено точного числа непридатних і придатних. Варіант «жоден не придатний» сумісний із самою логічною умовою, але не доведений нею; «рівно один» також не випливає. Не підміняємо неповні відомості конкретним розподілом.

5. **token/manual** — Прозоре відсилання. У реєстрі є кожний учасник; у двох записах немає номера телефону; саме ці неповні записи потрібно оновити. Напиши три речення, використавши however і конкретну іменникову групу замість неясного this. Не додавай відсутніх учасників.

   Ключ: The register includes every member. However, two entries have no phone number. These incomplete entries need to be updated. — У реєстрі є кожний учасник. Однак у двох записах немає номера телефону. Ці неповні записи потрібно оновити. Це зразок. Можна назвати the two entries without phone numbers — «два записи без номерів телефону». Потрібно зберегти повний перелік учасників і рівно два неповні записи, не оголошуючи весь реєстр неправильним.

6. **token/manual** — Редакція без перебільшення. Використай тільки відомості про Аніку та два прототипи з розділу 6. Виправ наведене повідомлення трьома-чотирма реченнями. Збережи час, місце, виконавицю, невипробуваний другий зразок і статус припущення про роботу надворі. Both prototypes have been proven reliable outdoors. Their successful validation guarantees good performance in every setting. — Доведено, що обидва прототипи надійні надворі. Їхнє успішне випробування гарантує добру роботу за будь-яких умов.

   Ключ: Anika’s indoor test of the first prototype on Monday was successful. The second prototype has not been tested. According to Anika, the first prototype may also work outdoors, but no outdoor test has taken place. — Випробування першого прототипу, яке Аніка провела в приміщенні в понеділок, було успішним. Другий прототип не випробовували. За припущенням Аніки, перший прототип може працювати й надворі, але зовнішнього випробування ще не було. Це одна прийнятна редакція. Прибрано непідтверджені both, proven reliable outdoors і guarantees … every setting. Усі задані відомості збережені; точний набір слів не обов’язковий. Чотири речення теж допустимі. Невипробуваний другий прототип не названо несправним.

### Finite accepted alternatives та межі scoring

Finite accepted lists не є універсальним semantic-paraphrase scorer. Author open tasks дозволяють інші граматично правильні formulations лише зі збереженням усіх facts та заданих моделей; M39 explicit alternatives не заявляють охоплення всіх можливих paraphrases. Internal punctuation зберігається; terminal punctuation, contractions, manual/tokens/Backspace reuse та autocomplete off перевіряються runtime окремо.

- Nominal 2: source key дозволяє natural `A reassessment...` із may/committee/exhibition plan/October; full statement and explicit will/took counterexamples retained.
- Nominal 4: обидві exact source forms із `The curator’s assessment...` / `The assessment ... by the curator...` збережені у full key.
- Nominal input 5: canonical плюс `The digitisation by the volunteers of the catalogue began in June. The work is still in progress.` — author-approved by-placement.
- Nominal input 6: canonical двома реченнями плюс `The team has proposed an expansion of the hall. No decision has been made. The new waiting times have not been measured.` — source допускає два/три речення.
- C1 input 5: explicit requested `may have` збережено; source згадує might/could, але саме ця task просить may, не must.
- C1 input 6: canonical плюс `The dates in the catalogue were checked yesterday. The descriptions have not been checked yet. Could you send the final file by Friday?` — those same three facts, three sentences.
- C2 case 3: FULL Marta A+B, `needn’t have` / `need not have`, unknown execution in B. Не зведено до однієї phrase.
- C2 case 4: гарантоване b та FULL unknown exact distribution; none/exactly-one не оголошено фактами. UI має три original options.
- C2 input 5: canonical плюс `The register includes every member. However, two entries have no phone number. The two entries without phone numbers need to be updated.` — exact source noun-phrase alternative.
- C2 input 6: canonical, explicit four-sentence split before `No outdoor test has taken place.`, і source note version із `remains untested` / `outdoors; however, no outdoor test has taken place.`. Indoor success не перетворено на outdoor guarantee; second untested не названо defective.

## Actual linked-bank inventory

SELECT-only inventory `m39-bank-inventory-v1.json`, captured `2026-10-06T00:47:23+00:00`. Identities і primary ownership установлені actual relations перед projection, не inferred from theory slug і не assumed 48. Повний bank prefix `Database\Seeders\V3\`.

| Page | Actual bank class suffix | Type / level / own or extra count | Exact IDs |
| --- | --- | --- | --- |
| 321 | `Polyglot\PolyglotNominalStyleAndInformationDensityC2LessonSeeder` | 4 / C2 / 48 own | 19041–19088 |
| 321 | `FormalEnglish\NominalStyleAndInformationDensityAllLevelsV3Seeder` | 0 / A1–C2 / 72 extra | 48807–48878 |
| 321 | `Polyglot\PolyglotNominalStyleAndInformationDensityAllLevelsLessonSeeder` | 4 / A1–C2 / 72 extra | 48951–49022 |
| 329 | `Polyglot\PolyglotFinalDrillC1LessonSeeder` | 4 / C1 / 48 own | 18417–18464 |
| 329 | `MixedRevision\C1MixedRevisionAllLevelsV3Seeder` | 0 / A1–C2 / 72 extra | 45999–46070 |
| 329 | `Polyglot\PolyglotC1MixedRevisionAllLevelsLessonSeeder` | 4 / A1–C2 / 72 extra | 46215–46286 |
| 330 | `Polyglot\PolyglotFinalDrillC2LessonSeeder` | 4 / C2 / 48 own | 19185–19232 |
| 330 | `MixedRevision\C2MixedRevisionAllLevelsV3Seeder` | 0 / A1–C2 / 72 extra | 46071–46142 |
| 330 | `Polyglot\PolyglotC2MixedRevisionAllLevelsLessonSeeder` | 4 / A1–C2 / 72 extra | 46287–46358 |

Own **48 / 48 / 48**, all linked **192 / 192 / 192**. Each extra pool має actual 12/A1–C2. Власні Mixed widgets використовують **FinalDrill C1/C2**, не весь mixed pool і не guessed MixedRevision class. Questions/answers/options/verb_hint/pivots/saved tests не змінюють.

Actual primary test URLs:

- [Nominal Style and Information Density — test](http://gramlyze.loc/test/formal-english/nominal-style-and-information-density).
- [C1 Mixed Revision — test](http://gramlyze.loc/test/mixed-revision/c1-mixed-revision).
- [C2 Mixed Revision — test](http://gramlyze.loc/test/mixed-revision/c2-mixed-revision).

## Semantic negative fixtures

Independent PHP author fidelity audit: **45 actual mutations** of real accepted fragments, not absent-string replacements. Each mutation changes content and leaves valid JSON, then finite validation rejects it. Structural negatives separately cover wrong detail ownership, duplicate anchor, lost case, punctuation change, author order swap.

Nominal negatives: proposal→completed implementation; may→will; scheduled→completed; began→completed; available→correct/approved; singular head agreement taken from nearby plural; unknown cost invented; unmeasured waiting-time improvement; nominal wording adding positive result. C1: present→past result; would→could/might; Present Perfect Passive lost; rarely→never; only-after subordinate inverted; passive lost; believed→known; defining subgroup changed by commas; who duplicate subject; may-have inference→factual past; polite request treated as past possibility; unchecked descriptions declared correct. C2: untested→failed; might/could→would; hypothetical→actual outcome; thought→confirmed; moved negation scope; didn’t need to→automatic nonperformance/performance/prohibition; not all→none/exactly-one; incomplete assessment→failed prototypes; untested second→defective; indoor success→outdoor proof; first result→both; may outdoors→guarantee. Translation loss, changed number/date/actor covered too.

Runtime finite manual-key negative inventory **35 = 10 / 11 / 14** targets the six actual input keys. It is a different scope from PHP projection fidelity, not a universal semantic grammar judge. Node suite відхиляє ці реальні мутації accepted answers; live перевірки наведені окремо нижче. No invented learner examples are added by test-only mutations.

## Guarded local pipeline

Fresh master/definition/actual DB fidelity gate PASS перед source edits. Physical/vhost/CLI-web proof підтвердив application root `D:/DEV/htdocs/gramlyze.loc`, document root `public`, local MySQL `localhost:3306/gr2` на `DESKTOP-3C05HGF`, APP environment `production` і effective SiteMode `development` для `.loc`. Environment label не підміняє фізичне підтвердження цілі. Proof only SELECT, без credentials.

Source backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m39-local/source-backup-4605e6f9917407c8`, 16 records: 10 finite-sync, 6 shared-verify-only. Шість original shared files окремо збережені exclusive до hunks; ROOT/WT/current/before SHA reviewed. ROOT author documents не копіювали.

Fresh preview `m39-preview-v1.json`: state `before`, actual **3 updates / 21 inserts / 0 deletes**, 24 bound sources. Digest `298c5a4c4722a0a4fcffa793c732b83f382497a049e9f946df7951a1d8635f1c`; raw SHA `a14da86355c958c0104f8e0f01882074abfbd1b3b1adf1ccbe3efb847426b68c`. `review-m39-plan.php m39-preview-v1.json source-backup-4605e6f9917407c8` і незалежний read-only agent review — PASS. Update лише type/body IDs 8852/8889/8892; всі інші original row fields/UUIDs/owners protected; inserts exact finite source.

Exclusive DB record backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m39-local/m39-backup-v1.json`, raw SHA `a14da86355c958c0104f8e0f01882074abfbd1b3b1adf1ccbe3efb847426b68c`; байт у байт дорівнює reviewed preview. Transactional apply повернув `applied`, 3 updates / 21 inserts. Жодних seeds/migrations/full restore.

`m39-after-v1.json` raw SHA `43b3146bc9d0e07c57bd270772f4fcac99e1dfac3866d368a885d787d272225a`: 10 actual rows/page, exact source native bodies/type/order/locale; text_blocks 6531 → 6552. Non-target **6528**, SHA `949d87f16b7dc9d78b72640e675d2131cdc5e26af91106b0f872c1f801f0abd4`, unchanged. Інші **19 таблиць**, **41 M26–M38 owners**, Page/category/title/subtitle/hero/tags/relations/bank IDs незмінні. `verify-m39-evidence.php` та незалежне saved-evidence зіставлення — PASS.

Fresh after preview state `after`, 0 updates / 0 inserts, digest `41b0c92c7dc63a9ff9f3b70c8543c1729a35c55ca982865c5082ba91d8853255`. Повторний apply початкового reviewed plan — **no-op, 0/0**. `m39-unused-noop-v1.json` не створено. `m39-after-noop-v1.json` raw SHA `49fae0100ed95023b4ccdf159ed8e39922248d8517828eca09fda6564d874141`, exact equality з after evidence крім часу `at`.

Temporary nonce route policy strict GET/exact `.loc`/raw loopback/no forwarded; HEAD та Forwarded requests повернули 404. Route видалено після apply/no-op; `routes/api.php` відновлено byte-identical до captured original, SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Fresh guest GET nonce URL після cleanup — **404**. Тимчасового route/proof/backup у Git немає.

## Автоматичні перевірки

PHP 8.5.10 / PHPUnit 12.5.35; isolated SQLite `:memory:`, testing storage/views, array cache/session, no working .env/DB writes. Runs below separate; they are not added to an invented aggregate total.

| Suite | Actual result |
| --- | --- |
| M39 independent AuthorFidelity only v1 | **49 tests / 440 assertions PASS**, exit0, 8.678s, 58MB; one existing PDO deprecation; protected files 46648, changes0. |
| M39 author/package targeted v1, 2 classes | **57 tests / 1435 assertions PASS**, exit0, 15.010s, 60MB; one existing PDO deprecation; protected files 46648, changes0. |
| M39 guard/patch targeted final v2 | **57 tests / 132 assertions PASS**, exit0, 03:26.879, 66MB; one existing PDO deprecation; protected files 46648, changes0. Includes portable LF planning/apply/no-op fixture. |
| Final related combined M38+M39 PHP, 8 classes | **234 tests / 3043 assertions PASS**, exit0, 09:07.721, 78MB, `m39-final-php-v1`; one existing PDO deprecation; protected files 46648, changes0. Actual single-run total. |
| M39 two pure Node suites | Practice-agent **85/85 PASS**; exact final relevant-run scope below remains separate. |
| Final related Node | **864/864 PASS**, exit0, 32 files, 5953.2695ms, `m39-final-node-v2`; доданий course-key container regression. Попередні 863/863 v1 і повторні 863/863 після renderer finalization (13660.0209ms) також PASS, не додаються до final count. Includes M27–M39 practice/runtime tooling + contractions. |
| Relevant Vitest | **61/61 PASS**, exit0, 8 files, 117.07s, `m39-vitest-v1`. No dependency/config change or production asset build. |
| Final syntax checks | **18 PHP files lint, 6 CJS syntax, 5 JSON parse PASS**; staged diff whitespace PASS, secret-pattern scan no matches. |

Existing PDO deprecation `config/database.php:62`, `PDO::MYSQL_ATTR_SSL_CA` under PHP8.5 is not suppressed or fixed outside scope. Author/package evidence:

- `storage/app/seo-m2-local/m39-author-only-v1-9b50e36f8f644b9284d4c9f5d0f53720-result.json`.
- `storage/app/seo-m2-local/m39-author-package-targeted-v1-e1b8f82df0e0488d86342f1c50f6c144-result.json`.
- `storage/app/seo-m2-local/m39-guard-patch-targeted-v2-c91b56c54dfe4bbc831531afa87cbc50-result.json`.
- `storage/app/seo-m2-local/m39-final-php-v1-a595012b686c43f3a8caabb4f8ae2b87-result.json`.
- ROOT `storage/app/seo-m39-local/m39-final-node-v1-{result.json,stdout.txt,stderr.txt}`, final `m39-final-node-v2-{result.json,stdout.txt,stderr.txt}` та `m39-vitest-v1-{result.json,stdout.txt,stderr.txt}`.

Final PHP child command: `php vendor/bin/phpunit tests/Feature/M38AuthorFidelityTest.php tests/Feature/M38ArticlesCollocationsPackageTest.php tests/Feature/M38ContentPatchTest.php tests/Feature/M38LocalTargetGuardTest.php tests/Feature/M39AuthorFidelityTest.php tests/Feature/M39AuthoredRevisionPackageTest.php tests/Feature/M39ContentPatchTest.php tests/Feature/M39LocalTargetGuardTest.php --do-not-cache-result --colors=never`, only through `tools/diagnostics/run-isolated-tests.py`, not the working DB. Node: `node --test` з exact 32-file scope у retained result JSON. Vitest: `node node_modules/vitest/vitest.mjs run --maxWorkers=1 --minWorkers=1`, current config includes усі 8 relevant suites: theoryNavigation, theorySidebarLayout, theorySections, unifiedTheoryDesign, publicAssets і polyglot browserStore/courseProgressState/lessonProgressState.

Private artifacts are retained locally, not committed. Tests include exact master/policy/definition identity, H1/title casing, all original nested cases/subparts, finite alternatives, 720px tables only where present, full no-JS author material, exact FinalDrill scope, source drift, metadata protection, fallback, semantic decisions and all prior disclosure counts.

## Реальні HTTP / browser / no-JS / overflow

Live `acceptance-v1` — **PASS, exit0**, `2026-10-06T01:21:20.363Z` → `01:30:56.389Z`: **12 M39 states**, 1440×1000 і 390×844, light/dark, окремі Chromium guest contexts, GET-only `.loc`. **39 no-JS readability states + 4 normal regression states**, разом 55. Page/console/HTTP/failed/font/network-policy errors **0**; violations `[]`. Initial HTML → reload DOM fidelity, complete visible author basic/cases/keys, exact source ownership, preserved anchors, print/restoration — PASS. За 0 M39 point details взаємодії з такими кнопками чесно N/A, не вигаданий click PASS.

Retained private evidence SHA-256: `acceptance-v1-browser.json` — `4455399306e22c8a5b67e03a9f9d136a0c9c6a2ebf58ba2c0e5c8899145403d7`; `acceptance-v1-http.json` — `112d3ef7ef1fac80174233ee36956c263f6e2f4b924f544ea2f63d404c3e2f8c`; `acceptance-mobile-extra.json` — `4ddc936e1c26ca40599a34ad906edc810c43c9d31883299180582e86c93ea336`; `course-server-properties-v2.json` — `54c64d3cca2a1f681c3e2999aea574995fb9aaf406b208ccf1ba085c7cfe77b9`. Supplemental run 3 contexts/5 PNGs, page/console/HTTP/font/failed/violations errors0.

Кожен із 12 станів: усі 6 tasks правильні/неправильні/accepted variants/score/reset; token-only і manual accepted, Backspace повертає token, terminal punctuation optional, autocomplete off. Exact own widget sample **5 IDs** у кожному стані, всі з фактичного власного **48-question bank**; без підміни extra pool. **35 distinct manual semantic negatives × 4 states = 140 live rejections**, не нові 140 learner tasks.

Matrix зберегла **96 bounded viewport PNGs**, не full-page screenshots. Focus sections: Nominal 1/3/4/5/6/7, C1 1/2/3/4/5/7, C2 1/2/3/4/5/6/7; кожен набір у чотирьох viewport/theme states. Окремі local-table right-edge mobile shots є лише для Nominal/C1, не invented C2 table. Main візуально переглянув desktop practice, C1 meaning-table та mobile practice; supplementary input/Marta/scope visual checks наведені нижче. Артефакти в ROOT `storage/app/seo-m39-local/acceptance-v1-*`, не Git.

Supplemental `acceptance-mobile-extra.json`: mobile390/light input groups усіх трьох pages та окремі C2 case3 Marta/case4 scope, **5 PNGs**. Visible input focus PASS; Enter обирає Marta `a`, Space обирає scope `b`, focus outline visible, три controls читаються без clip/overlap. Main переглянув Nominal tokens/input та обидва C2 control PNGs. Окреме decorative document overflow цього run **8/1/0px**; main/cards **0px**.

Чесне UX-обмеження: успадкований generic practice button застосовує ALL CAPS; correct options містять full accepted author key/пояснення, а distractors коротші. На mobile це дає дуже високі кнопки й візуально очевидну різницю між варіантами. Horizontal clip/overlap немає, scrolling працює, але функціональний PASS не є твердженням про оптимальний дизайн чи педагогічну складність. Source schema/style не змінювали посеред приймання; поліпшення потребує окремої узгодженої presentation projection, без переписування author key.

Token groups механічні, по 1–3 слова; у довгих multi-sentence answers окремі групи можуть перетинати межу речення (наприклад `in June. The`). Вміст, порядок повної правильної відповіді й punctuation збережені, click/manual/reuse працюють; ці групи не заявляємо як авторське смислове членування.

M39 no-JS educational readability **PASS**: повний author basic та практика/ключі читаються; scripts блоковано навмисно CSP, що не рахується як failed resource. Це не JS-free automatic scoring. Усі 39 M27–M39 pages пройшли readability і finite point-disclosure baseline. Normal-JS **3 M38 pages + M28 Inversion Basics PASS**, source/practice/table/fidelity без регресії. Historical limitation окремо: **11 M26–M29 pages / 22 hidden token banks**, поза M39; no-JS readability не доводить повної інтерактивної працездатності старої практики, старі pages не ремонтували.

### Gated course-copy limitation

Historical M23 Nominal course copy returned HTTP200 with server author content but browser `contentVisible=false` через existing gate. M39 перевірив ordinary [course URL](http://gramlyze.loc/courses/english-grammar-theory/lesson/formal-english/nominal-style-and-information-density) лише guest GET/server properties, без gate bypass і без visual lesson acceptance claim.

Fresh `course-server-properties-v2.json`, `2026-10-06T01:34:45.670Z`: **HTTP200**, HTML, H1 `Nominal Style and Information Density`, `hasCourseContent=true`, **6 cases / 6 author keys**, X-Robots-Tag `noindex, nofollow, noarchive`; canonical теорії та metadata unchanged. Fresh browser gate visibility не стверджуємо за server HTML. Initial `acceptance-v1-http.json` показав keys0 через diagnostic direct-child selector, не відсутність ключів. Immutable initial artifact лишено; diagnostic selector виправлено на descendant LI та додано regression test, fresh properties збережено окремо. Це зміна діагностики, не сайту/БД/author payload.

### Overflow

Learning main/cards overflow **0px** у всіх 12 M39 states. Nominal/C1 table wrapper: desktop **840/840px client/scroll**, mobile **322/720px**, own `overflow-x:auto`; right edge доступний local scroll. C2 table count **0**. Desktop document overflow **0px**. Mobile document overflow light/dark: Nominal **0/0px**, C1 **3/9px**, C2 **15/0px**, від animated decorative shapes; decorative bounding overshoot зафіксовано окремо, до ≈14.69px у C2 light. Background та global `overflow-x:hidden` не змінено; не заявляємо «весь document має 0».

## Metadata та sitemap

**49 fixed real guest GET URLs before/after — HTTP200**, metadata equality PASS: Page.title/H1 casing, description, canonical, robots/X-Robots-Tag, OG/Twitter. Slug, category/ancestry, locale/level, subtitle/hero, tags/test relations захищені DB evidence. Контрольний main text/disclosure/legacy IDs unchanged. Ordered sitemap: actual **554 URLs**, before/after SHA `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`, count/order unchanged. Read-only main independently reran `capture-m39-http.compare` — PASS. Один попередній GET старого M26 category мав 30s timeout; окремий fresh GET повернув 200 за 0.607s, повторна повна baseline завершилася. No config/cache repair. `.com` canonical/sitemap strings не означають production HTTP перевірку.

## Protected regression baseline

| Package | Accepted point disclosures |
| --- | ---: |
| M26 | 56 |
| M27 | 13 |
| M28 | 0 |
| M29 | 0 |
| M30 | 0 |
| M31 | 2 |
| M32 | 1 |
| M33 | 2 |
| M34 | 2 |
| M35 | 0 |
| M36 | 0 |
| M37 | 0 |
| M38 | 0 |
| M39 | 0 / 0 / 0, explicit semantic audit |

41 previous owners та finite disclosure baseline unchanged у before/after/no-op/final post-browser SELECT evidence; `m39-after-v2.json` raw SHA `5c0c7f318bb5ec5a0b927e53d1c32380c84bed43f4174f578d54fc09ae4d98f2`, independent verifier PASS. M27–M39 full-author/basic/disclosures підтверджені live no-JS matrix, M38/M28 additionally normal JS. Existing prior M26/PPC practice exceptions не перезаписано й не описано неправдиво як original frozen exact M26 data.

## Final Git handoff

Explicit allowlist: **37 M39 files** разом із цим report. Перевірено JSON/PHP/JS/diff checks, staged diff, secret-pattern scan без збігів, forbidden artifact paths — 0; immutable master/notes no-diff, original raw hashes unchanged. Сторонні dirty files не включено. Push-target лише `codex/seo-m39-m23-authored-revision-layers`; existing workflows не мають push trigger для цієї гілки, workflow dispatch не виконується.

Git handoff посилається на commit, який містить цей report; self-referential SHA всередину самого commit не вставляється. Full 40-character SHA, direct commit URL та exact `git ls-remote` == `git rev-parse HEAD` result наводяться в фінальній відповіді після normal push. Versioned [report URL](https://github.com/Pvitaly91/engapp-codex/blob/codex/seo-m39-m23-authored-revision-layers/docs/reports/seo-m39-m23-authored-revision-layers.md).

No `.env`, private backup, screenshot, runtime proof, DB dump, vendor/build, temporary route or foreign unfinished file is committed. Normal push only `codex/seo-m39-m23-authored-revision-layers`, no main/PR/force/deploy. No automatic M24 continuation.

Production не перевірявся й не змінювався.
