# M13 — Sentence Structure: фокус, іменникові групи та зв’язність

Дата: 2026-09-27. **Зміни застосовано до робочого `http://gramlyze.loc`.** Це три українські уроки та 18 завдань самоперевірки всередині них, не зміна Mixed-банків.

## 1. База й межі

- Після `fetch` база `origin/codex/seo-m12-theory-content-styling` — `1bb40489c448d2f29f6b494c8696bc3d9e3bed10`. Ancestry M11/M12 і прийнятого styling перевірено. Нова робоча гілка: `codex/seo-m13-sentence-structure-content`.
- Використано вже наявний вільний окремий checkout `storage/app/seo-m11-worktree`; його історична назва не означає стару базу. Основний checkout на `41820a2bebdf69004fa7209a2a38457f93efabbd` зі сторонніми незавершеними змінами не перемикався й не скидався.
- Прочитано AGENTS.md, звіти M11/M12/styling, чинні `TheoryRichContent`, Blade-компонент і CSS. Новий renderer, CSS, клієнтський JS чи build не потрібні й не додані.
- Дозволені зміни даних: `pages.text` та `heading/body` дев’яти конкретних українських `text_blocks`. Ідентичності, title/H1, рівні, локалі, UUID, порядок, зв’язки, тестові банки та прогрес збережені.
- `.com` у canonical перевірявся лише як рядок; production-профіль — лише в ізольованих тестах. Запитів до `.com/.ub`, SSH, деплою, PR/merge, main/force push не виконувалося.

## 2. Точні identities та реальні URL

Namespace усіх трьох identities: `Database\Seeders\Page_V3\SentenceStructure\`. Sources: `database/seeders/Page_V3/SentenceStructure/<Seeder>/definition.json`. Категорія всіх трьох — коренева `sentence-structure`, `language=uk`, `type=theory`, без батьківської категорії.

| Seeder | Slug | Рівень | Page ID | UK subtitle / hero / box ID |
| --- | --- | --- | --- | --- |
| `CleftSentencesEmphasisTheorySeeder` | `cleft-sentences-emphasis` | C1 | 297 | 8778 / 8779 / 8780 |
| `ComplexNounPhrasesTheorySeeder` | `complex-noun-phrases` | C2 | 312 | 8823 / 8824 / 8825 |
| `EllipsisSubstitutionAndReferenceTheorySeeder` | `ellipsis-substitution-and-reference` | C2 | 311 | 8820 / 8821 / 8822 |

UUID, порядок `0/1/2` та зв’язки звірено з визначеннями й живою БД до запису; усі три початкові сторінки точно відповідали manifest. Невідомих ручних конфліктів у цільових записах не було. Рівні збережено як редакційні позначки сайту, не як офіційну CEFR-сертифікацію.

| Урок | Теорія | Основний тест | Курсова копія |
| --- | --- | --- | --- |
| Cleft Sentences and Emphasis | [Теорія](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis) | [Тест](http://gramlyze.loc/test/sentence-structure/cleft-sentences-emphasis) | [Урок курсу](http://gramlyze.loc/courses/english-grammar-theory/lesson/sentence-structure/cleft-sentences-emphasis) |
| Complex Noun Phrases | [Теорія](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) | [Тест](http://gramlyze.loc/test/sentence-structure/complex-noun-phrases) | [Урок курсу](http://gramlyze.loc/courses/english-grammar-theory/lesson/sentence-structure/complex-noun-phrases) |
| Ellipsis Substitution And Reference | [Теорія](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference) | [Тест](http://gramlyze.loc/test/sentence-structure/ellipsis-substitution-and-reference) | [Урок курсу](http://gramlyze.loc/courses/english-grammar-theory/lesson/sentence-structure/ellipsis-substitution-and-reference) |

URL отримано через чинні Page/test/course resolvers і звірено з навігацією. Три теорії та три основні тести реально повернули GET 200; кнопки основних тестів відкрито у браузері. Усі курсові URL перевірені resolver-тестами, фактичний guest HTTP/browser — для Cleft Sentences and Emphasis. Кнопку «Пройти тест» не дубльовано.

Read-only переглянуті й перевірені GET 200:

- [M11 Linking Words](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) — контроль оформлення;
- [M12 Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) — розмежування і контроль;
- [Advanced Fronting And Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis);
- [One/Ones](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/one-ones);
- [Defining Relative Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/defining-relative-clauses).

Їхній текст і metadata не змінені. Це не додатковий загальний аудит сусідніх уроків.

## 3. Що було й що допрацьовано

### Cleft Sentences and Emphasis — C1

**Було:** три короткі англомовні hero-правила про it-cleft, what-cleft та питання. Box містив лише службове повідомлення про theory anchor, без змістовного уроку й самоперевірки.

**Стало:** український вступ, контекстні діалоги, відоме/нове, зіставлення двох фокусів тієї самої події, виправлення припущення, заперечний фокус, cleft-запитання, акцент на дії/потребі/результаті, розбір зв’язного абзацу, помилки та шість пояснених ключів.

Приклад нового розмежування: `It was the editor who removed the repeated paragraph` виділяє виконавця, а `It was the repeated paragraph that the editor removed` — вилучений матеріал. Обидва граматичні; відповідність контексту не підмінена оцінкою граматичності.

**Відмінність від Basics:** формули нагадано коротко. Основна робота — вибрати потрібний акцент у діалозі, відкинути неправильну причину, зберегти значення дії й пов’язати відповідь із попереднім реченням. Жодне з шести завдань Basics не скопійоване. Формальне `It was difficult to finish the survey` не названо cleft. У what-clefts не нав’язується абсолютна заборона множинного `be`; дія після `What ... did was` допускає обидва доречні варіанти з `to` і без нього.

### Complex Noun Phrases — C2

**Було:** назви pre/postmodification та інформаційної щільності без покрокового аналізу, таблиці й практики; службовий box.

**Стало:** головне слово та залежні компоненти; таблиця «компонент → роль → приклад → переклад»; три покрокові розбори; визначники, прикметники, noun modifiers, participles, PP/to-infinitive/clauses; complement проти додаткового опису; узгодження, прикладка й пунктуація; неоднозначне приєднання та читабельність.

Приклад: `The description of the experimental procedures is incomplete` має однину за `description`, а не за найближчим `procedures`; після заміни на `descriptions` потрібне `are`. Додано два прочитання малюнка техніка «в майстерні» й явні переформулювання. Компактне `a new-staff training plan` зіставлено з прозорішим `a plan for training new staff`; факт початку навчання в травні залишено окремим реченням. Довжина групи не подається як ознака кращого стилю.

### Ellipsis, Substitution and Reference — C2

**Було:** переважно перелік замінників; приклад `the former/the latter` без двох названих об’єктів; не пояснено, які саме слова відновлюються і які моделі обмежують заміну.

**Стало:** окремо пояснені пропуск, заміна та відсилання; повні/скорочені пари із квадратними дужками; розмовний регістр; `hope so/not`, `don't think so`, `I'm afraid not`; `do/do so` і контрприклад властивості з `be`; one/ones тільки зі злічуваними іменниками; this/that/such; два явні референти former/latter; розібраний абзац і виправлення неоднозначного `her`.

Приклади: `Max can't [translate the abstract]` показує пропущену дію, але зберігає модальність/заперечення. `We need reliable information. Do you have any?` не підміняє незлічуване information словом ones. `Olena told Marta that her notes were missing` переформульовано повтором імені та прямою мовою з явним власником.

## 4. Усі 18 редакторськи перевірених завдань

Це відкривні native `details` із поясненими ключами, не автоматично оцінюваний новий банк. Відкриті відповіді позначені як приклади або умова звужена до конкретної конструкції. Пояснення й інструкції українські; власні англійські приклади супроводжуються перекладом.

| Урок / № | Що перевіряється | Ключ / редакційний висновок |
| --- | --- | --- |
| Cleft 1 | Контраст причини відхилення пропозиції | `It wasn't the cost ...; it was the lack of evidence.` Заперечено причину, не подію. |
| Cleft 2 | Редактор чи вилучений абзац | Об’єктний фокус B доречніший у заданому питанні; A граматичне. |
| Cleft 3 | Cleft-запитання про час зміни доставки | `When was it that the supplier changed the delivery date?` Без інверсії після that. |
| Cleft 4 | Акцент на дії експертної групи | `What the panel did was (to) request an independent review.` Не підміна факту потребою. |
| Cleft 5 | Узгодження двох частин | `It is the local volunteers who help visitors.` Is із it, help із volunteers. |
| Cleft 6 | Потреба, що пояснює труднощі команди | `What the team needed was a clearer brief.` Допустимі рівнозначні what-clefts. |
| Noun 1 | Розбір `those three carefully edited policy reports` | Head reports; carefully уточнює edited, не reports. |
| Noun 2 | Description / descriptions | `is` / `are` за головним словом. |
| Noun 3 | Дві функції that-clause | Зміст claim проти relative clause, що визначає device. |
| Noun 4 | Додаткова прикладка | `Leila, the project coordinator, approved the change.` Дві коми відповідно до явного контексту. |
| Noun 5 | Приєднання `in the workshop` | Явно розрізнені зображена сцена та місце розглядання малюнка; можливі інші однозначні варіанти. |
| Noun 6 | Ущільнення без noun-chain перевантаження | `new-staff training plan` або прозоре пояснення; факт старту — окремо. |
| Ellipsis 1 | Відновити другу частину про переклад | `Max can't [translate the abstract]`; пояснено, що саме не повторене. |
| Ellipsis 2 | Негативні короткі відповіді hope / think | `I hope not` / `I don't think so`, різниця бажання й оцінки ймовірності. |
| Ellipsis 3 | Заміна повтореної перевірки етикеток | `did so` відновлює check, не promise; з властивістю залишається `is too`. |
| Ellipsis 4 | Information проти folders | `Do you have any?` / `any blue ones`; злічуваність не ігнорується. |
| Ellipsis 5 | Workshop / handbook / this | Former — семінар, latter — посібник; `This ease of reference` явно називає факт. |
| Ellipsis 6 | Неоднозначне her | `Olena's notes` або пряма мова `My notes are missing`; власник визначений. |

Ручна редакція враховувала не лише кількість: значення перетворень, достатній контекст, узгодження, природність перекладу та припустимі альтернативи. Структурні тести самі по собі цього не доводять.

## 5. Джерела граматичної перевірки та межі доступу

Усі пояснення, діалоги, абзаци й вправи написано для Gramlyze, а не скопійовано зі статей. Вихідні коректні ідеї з hero збережені й розгорнуті.

| Джерело | Перевірюване правило | Фактичний доступ |
| --- | --- | --- |
| [Cambridge — Cleft sentences](https://dictionary.cambridge.org/grammar/british-grammar/cleft-sentences) | It-/what-cleft, відома інформація і виділений фокус | Пряме відкриття 403; використано доступний індексований текст видавця, не заявляється прочитання повної сторінки. |
| [Cambridge — Noun phrases](https://dictionary.cambridge.org/grammar/british-grammar/noun-phrases), [Dependent words](https://dictionary.cambridge.org/grammar/british-grammar/noun-phrases-dependent-words) | Head, залежні компоненти до/після нього | Прямий доступ 403; перевірені доступні індексовані фрагменти видавця. |
| [Cambridge — Order](https://dictionary.cambridge.org/grammar/british-grammar/noun-phrases-order), [Apposition](https://dictionary.cambridge.org/grammar/british-grammar/apposition) | Порядок компонентів та прикладка | Використано індексований текст цих матеріалів, не весь сайт. |
| [Cambridge — Ellipsis](https://dictionary.cambridge.org/grammar/british-grammar/ellipsis), [Substitution](https://dictionary.cambridge.org/grammar/british-grammar/substitution) | Відновлюваний матеріал, so/not, do so, one/ones | Прямі сторінки 403; межі тверджень звірені з доступним індексованим текстом. |
| [Cambridge — So and not](https://dictionary.cambridge.org/uk/grammar/british-grammar/so-and-not-), [Do](https://dictionary.cambridge.org/us/grammar/british-grammar/do) | Лексичні моделі коротких відповідей та повтор дії | Індексований текст; не робиться правило про універсальну заміну після будь-якого дієслова. |
| [Cambridge — Former](https://dictionary.cambridge.org/us/dictionary/english/former) | Перший із двох явно названих референтів | Індексована словникова стаття. |
| [British Council — Emphasis: cleft sentences, inversion and auxiliaries](https://learnenglish.britishcouncil.org/comment/203837) | Фокус, what-clause, форма дії після what ... did; варіативність is/are | Повний текст відкрився; використано пояснення уроку та відповіді команди LearnEnglish, не неперевірені учнівські коментарі. |

Власні неоднозначні приклади редагувалися окремо: переформулювання не додають вигаданих дій персонажам; для відкритих питань наведено допустимі варіанти. Відомі One/Ones і Cleft Basics у Gramlyze враховано як сусідній контент, не як єдине зовнішнє граматичне джерело.

## 6. Фактичне локальне застосування

Збережено чинний механізм M11/M12: вузький `SentenceStructureContentPatch`, M13 guard/command і CLI-adapter з allowlist трьох **повних** seeder identities. Manifest `database/content-patches/m13-sentence-structure-before.json` містить історичні source definitions, а не приватний DB dump. Старі manifests, allowlists і backup/restore формати не змінені.

Фізична перевірка зв’язала справжній vhost `gramlyze.loc`, Windows web-runtime, document root, локальні listeners/processes та фактичне MySQL-з’єднання `gr2`. `APP_ENV=production` залишено як було; записи дозволені тільки через перевірений local-target opt-in. Sandbox-обмеження read-only інспекції процесів подолано дозволеним запуском перевірки, а не обходом guard.

Послідовність виконана: свіжий proof → preview → перевірка scope/полів → ексклюзивний record-backup → транзакційний apply → postcondition → повторний no-op.

- Остаточний preview: `m13-preview-commit-ready-20260927.json`, digest `5f9f7e0eda934d222eeb1a6214ae26d2a48c2341227827a4d2ae6a86f661cb9b`.
- Фактично змінено **12 записів**: 3 `pages.text`, 9 `text_blocks.heading/body`. Це результат порівняння before/after, не примусова очікувана кількість.
- Остаточний record-backup: `storage/app/seo-m13-local/m13-records-commit-ready-20260927.json` у робочому локальному checkout. SHA-256: `8fe4346c7d6c53b595623e1739249c6973633b36593b529383d55f8ddb75597d`. Backup залишено локально, не додано в Git.
- Apply: `status=applied, updated=12`. Повтор: `status=no-op, updated=0`; файл для зайвого no-op backup не створився.
- Перший прохід HTTP приймання виявив зміну H1 Ellipsis через локалізований subtitle й повтор назви в description. Не послаблюючи порівняння, пакет адресно відновлено з першого backup, виправлено source, створено новий preview/backup і застосовано заново. Старий backup також збережений. Додано регресійний тест фактичного controller title extractor, а не тільки bare Blade render.
- Під час staged-перевірки прибрано зайвий порожній рядок наприкінці manifest. Щоб його byte hash відповідав остаточному backup, повторено guarded restore/preview/apply/no-op. Порівняння двох plans підтвердило ті самі 12 записів і абсолютно однаковий навчальний before/after; фінальні GET повторно підтвердили content hashes та metadata з браузерного приймання.
- Тимчасовий proof endpoint від’єднано; GET повернув **404**. `routes/api.php` відновлено байт-у-байт до стану перед proof, зі збереженням сторонніх попередніх змін.
- Фінальне порівняння **46 таблиць**: кількість рядків і ordered hashes усіх захищених значень незмінні. Із hash виключалися лише дозволені поля точних 12 записів. `.env` незмінний; усі шість accepted definitions M11/M12 досі відповідають робочій БД.

Не запускалися робочі сідери, міграції, truncate, масове оновлення чи відновлення всієї БД. Fixture-import відбувався виключно в ізольованих тестах.

## 7. Renderer, HTTP та браузер

Sources містять підтримувані top-level h4, локальну scroll-таблицю та один self-check section із 6 завданнями й 6 ключами. Згенерованих presentation-обгорток у БД немає. PHP renderer opt-in пройдено до apply; live DOM не потрапляє у plain fallback.

| Урок | Rich sections / examples у live body | Desktop / mobile | Світла / темна | Завдання / ключі |
| --- | --- | --- | --- | --- |
| Cleft | 9 / 41 | pass / pass | pass / pass | 6 / 6 |
| Noun phrases | 9 / 23 | pass / pass | pass / pass | 6 / 6 |
| Ellipsis | 10 / 49 | pass / pass | pass / pass | 6 / 6 |

Число sections включає практику й заключні посилання, а examples — оформлені фрагменти, не кількість незалежних завдань.

Live-приймання: Chromium `147.0.7727.15`, desktop `1440×1000`, mobile `390×844`, новий context для кожного сценарію. Шість основних сценаріїв, по дві теми кожен. Перевірено матеріал з початкової HTTP-відповіді, відповідність DOM, reload, контент нижче першого екрана, нумерацію, відкриття/закриття ключів мишею, Enter/Space, читабельність та таблиці. Мінімальний contrast серед виміряних текстових зразків — 6.295 у light і 8.352 у dark; це не повний accessibility-аудит.

На mobile локальна ширина контейнера таблиці 322 px, контент 640/720/700 px; горизонтальний scroll 318/398/378 px працює. Документ не переповнюється в жодній темі. Основні intro/keys/table screenshots збережено та переглянуто, включно з прикладами й перекладами в обох темах.

Три переходи до основних тестів успішні. Автоматичний `/state` POST навмисно заблоковано, відповіді не надсилалися, прогрес не записувався. Це очікувані діагностичні abort, не application failure.

JavaScript page errors: **0**. Неочікувані збої локальних ресурсів: **0**. Google Fonts: `net::ERR_NETWORK_ACCESS_DENIED` (15 запитів у шести основних сценаріях); перевірка проведена з fallback-шрифтами. У чотирьох контрольних сценаріях M11/M12 також pass, 0 page errors, тільки шрифти й навмисні state abort.

Курсова копія Cleft: HTTP 200, актуальний lesson HTML із 6 завданнями й 6 ключами є у серверній відповіді, але навчальний блок невидимий гостю через чинний gate. Це **не** приймання доступу авторизованого учня; gate не обходився й не змінювався. Ізольований course renderer додатково перевіряє ті самі body/UUID.

Використано справжні GET та навігацію на `.loc`; без `page.setContent`, `route.fulfill`, `innerHTML`/fixture-підстановки чи імпорту сесій. Можливість старого M11 fixture runner не використовувалася й не експортується M13 CLI.

## 8. Metadata та sitemap

Порівняння before `15:17:42 UTC` → accepted `15:32:55 UTC`: 13 фіксованих адрес, усі GET 200, без зміни redirect/location, Content-Type чи X-Robots-Tag. H1/title, canonical, robots і основні test links збережені. Нові descriptions узгоджені з OG/Twitter лише на трьох цільових теоріях. Metadata тестів, курсової копії та п’яти контрольних/сусідніх сторінок незмінні.

На `.loc` залишається `X-Robots-Tag: noindex, nofollow, noarchive`; окремі meta robots відсутні, як і до правок. Production canonical залишився тим самим рядком і не відкривався.

[Sitemap](http://gramlyze.loc/sitemap.xml): фактично **554 URL** до й після, порядок повністю однаковий. Ordered SHA-256: `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Тест порівнює масив адрес, а не hardcode 554. Текстові hashes і rich-renderer counts контрольних M11/M12 та сусідів також незмінні.

## 9. Автоматичні перевірки й матеріали

- Ізольований PHP 8.5.10 / PHPUnit 12.5.35: **101 тест, 2721 assertion, exit 0**. Одна наявна deprecation `PDO::MYSQL_ATTR_SSL_CA`; залежності/оточення для її усунення не змінювалися.
- Після остаточного форматування manifest повторено дві M13 suites: **23 тести, 530 assertions, exit 0**, та фінальне порівняння захищених даних усіх 46 таблиць.
- Suites: `SentenceStructureContentPackageTest`, `SentenceStructureContentPatchTest`, M11/M12 content + patch, `TheoryRichContentTest`, `M11LocalTargetGuardTest`.
- Перевірено JSON/hero JSON, actual rich opt-in, українські інструкції, шість унікальних завдань/ключів, resolvers, UUID/порядок/локалі, H1 через controller, metadata, old→new/no-op, stale source/record/pivot, manual conflict, exclusive backup, transaction rollback, guarded restore та production-default refusal.
- Node: **27/27 pass** — M13/M11/M12/styling diagnostic tests та `theory-rich-css.test.cjs`. Shared runner отримав лише opt-in збирання rich counts і mouse details check, старі profiles за замовчуванням не змінені.
- HTTP comparison і live browser описані вище; repository-wide suite, crawl, Lighthouse, performance-кампанія не запускалися.

Приватні локальні матеріали залишені в `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m13-local/`: `inventory-before.json`, preview/record-backups, `before-http.json`, `accepted-http.json`, `accepted-comparison.json`, `protected-before.json`, `protected-accepted.json`, `protected-commit-ready.json`, `accepted-browser.json`, `controls-browser.json`, `accepted-*.png`, `controls-*.png`. Вони не комітяться; cookies/tokens не зберігаються у browser evidence.

Фінальний PHP evidence: `storage/app/seo-m2-local/m13-accepted-5601eee22cfb4b63a0788cac6c0425af-result.json` в окремому checkout (приватний, поза commit).

Повтор M13 після форматування: `storage/app/seo-m2-local/m13-commit-ready-20bb82204f254fe8a012ab0c497c5bd2-result.json`.

## 10. Передача та обмеження

До Git відібрано лише три definitions, вузький before manifest, M13 wrappers, мінімальне opt-in доповнення diagnostics, цільові тести й цей звіт. `.env`, vendor, build artifacts, DB backups/dumps, runtime caches та сторонні незавершені зміни не входять до пакета. Звичайний commit/push адресований тільки `codex/seo-m13-sentence-structure-content`; фінальний SHA та результат звірення з remote наведено в повідомленні передачі (SHA не може бути записаний у власний commit без зміни його hash).

Обмеження: Cambridge direct access 403; зовнішні шрифти недоступні; авторизований course gate не перевірявся; не було повного SEO/a11y/performance-аудиту. Чинні CSS/build inputs не змінені, тому новий build не запускався.

**Production не перевірявся й не змінювався. Пакет ще не перенесений на production.**
