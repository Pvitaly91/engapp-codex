# M18 — аргументація, зв’язність і переформулювання

Дата: 28 вересня 2026 року (локальний час Europe/Kyiv). **Зміни застосовано до робочого gramlyze.loc.**

## База та межі

Прочитано AGENTS.md, прийняті звіти M17, M16 і M12 styling. Після fetch підтверджено базу M17 `3f08b0c7aab2a4cccdfe1ff3da51625c2a79e8f7` та її відповідність origin/codex/seo-m17-hedging-stance-content. Нова гілка: `codex/seo-m18-argumentation-cohesion-content`.

Повторно використано вільний worktree `storage/app/seo-m11-worktree`. Основний checkout на `41820a2bebdf69004fa7209a2a38457f93efabbd` / `codex/production-ready-a14788dac` мав сторонні незавершені зміни; його не перемикали, не скидали й не комітили. Тимчасовий proof route прибрано з побайтним відновленням початкового routes/api.php.

Scope — рівно три C2 definitions у трьох різних категоріях. Рівень C2 — редакційний рівень матеріалу, не сертифікація. Mixed-банки, алгоритми відповідей і прогрес не змінювалися. Повних seed/migrate, очищення кешів/сесій, rebuild, dependency update, production HTTP/SSH, PR, merge, workflow dispatch чи деплою не було.

## Ідентичності й реальні адреси

Шляхи перевірено за робочими Page/test/course resolvers, навігацією та HTTP. Кожен definition: `database/seeders/Page_V3/<підкаталог>/<Seeder>/definition.json`.

| Повна Page.seeder identity | Категорія / теорія | Основний тест |
| --- | --- | --- |
| `Database\Seeders\Page_V3\AcademicEnglish\ArgumentationAndAcademicToneTheorySeeder` | [Argumentation and Academic Tone](http://gramlyze.loc/theory/academic-english/argumentation-and-academic-tone) — academic-english | [Тест](http://gramlyze.loc/test/academic-english/argumentation-and-academic-tone) |
| `Database\Seeders\Page_V3\ClausesAndLinkingWords\DiscourseMarkersAndCohesionTheorySeeder` | [Discourse Markers And Cohesion](http://gramlyze.loc/theory/clauses-and-linking-words/discourse-markers-and-cohesion) — clauses-and-linking-words | [Тест](http://gramlyze.loc/test/clauses-and-linking-words/discourse-markers-and-cohesion) |
| `Database\Seeders\Page_V3\FormalEnglish\ParaphraseAndReformulationTheorySeeder` | [Paraphrase and Reformulation](http://gramlyze.loc/theory/formal-english/paraphrase-and-reformulation) — formal-english | [Тест](http://gramlyze.loc/test/formal-english/paraphrase-and-reformulation) |

Усі три категорії — кореневі, language=uk, type=theory. Вузький M18 wrapper містить точне identity → category mapping, а не спільну academic-english перевірку. Негативний тест переставляє категорії між двома дозволеними M18 сторінками: обидві відхиляються до створення плану, незалежна третя лишається придатною.

Назви збережено окремо від H1:

| Page.title / HTML title без суфікса | Фактичний controller-derived H1 |
| --- | --- |
| Argumentation and Academic Tone | Argumentation and academic tone |
| Discourse Markers And Cohesion | Discourse Markers And Cohesion |
| Paraphrase and Reformulation | Paraphrase and reformulation |

Read-only сусіди / посилання:

- [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) — styled-контроль M17.
- [Stance Register And Evaluation](http://gramlyze.loc/theory/academic-english/stance-register-and-evaluation).
- [Register Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase) — styled-контроль M16.
- [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices).
- [Ellipsis Substitution And Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference).

[Курсова копія Argumentation](http://gramlyze.loc/courses/english-grammar-theory/lesson/academic-english/argumentation-and-academic-tone) також перевірена; обмеження gate описане нижче.

## Before та редакційні рішення

Before Git і робочої БД збігався побайтно за цільовими subtitle/hero/box і page text; identities, blocks, tags/pivots та ancestry також звірено. Короткий Git-source не використовувався як припущення про стан БД. Невідомих ручних конфліктів не було.

Усі три старі уроки мали три англомовні hero-опори та службовий box без повного уроку. Тепер кожен має українські пояснення, авторські приклади з перекладом, компактну таблицю, аналіз зв’язного тексту, шість завдань і шість пояснених ключів.

| Урок | Збережені опори | Додане / виправлене |
| --- | --- | --- |
| Argumentation | Qualified claims, evidence/reasoning, academic conclusion | Розділені теза, факт, міркування, припущення, межа та заперечення. Власний явно вигаданий каталог гуртків: від заданих фактів до абзацу й ролі кожного речення. Заперечення про телефон частково приймається; рекомендація звужується без вигаданих даних. |
| Discourse markers | Contrast, addition/result, backward reference | Cohesion не дорівнює coherence. Контекстні nevertheless/nonetheless, conversely, that said/having said that, moreover/furthermore, consequently/accordingly, in light of this/with this in mind. Два пов’язані абзаци, прозорий this, корисний повтор, пунктуація й усунення надлишкових переходів. |
| Paraphrase | Clarification, reframing, formal precision | Розділені точний переказ, розкриття терміна, конкретизація, самовиправлення та інтерпретація. Власна нотатка координаторки → контрольовані редакції → збережене/змінене/відповідність інструкції. Конкретні перевірки учасників, джерела, часу, умов, кількості, заперечення й модальності. |

Два важливі уточнення вихідного матеріалу:

- Приклад `The method is robust; accordingly, the findings are reliable.` замінено прозорим контекстом «місткість кімнати → відповідне коригування ліміту». Сам прикметник robust не гарантує достовірності результатів; у поясненні названо потребу перевірити придатність даних і застосування методу.
- Пораду додавати evidence під час reformulation уточнено: новий доказ не приписують джерелу під виглядом перефразування. Власне пояснення чи інтерпретацію позначають окремо. More precisely може додавати знану авторові деталь, але це вже не точний переказ самої ширшої фрази.

Модель аргументу не оголошена універсальним законом есе. Формальний тон не ототожнений зі складними словами, забороною I/we чи штучно однаковою вагою всіх позицій. Порядок подій не поданий як доказ причинності.

## Зовнішня звірка та фактичний доступ

Приклади й вправи — власні; зовнішні навчальні сюжети не копіювалися. Повний доступний основний текст прочитано:

- [Harvard — Anatomy of a Body Paragraph](https://writingcenter.fas.harvard.edu/anatomy-body-paragraph): розрізнення тези, опори й аналізу, без нав’язування одного порядку всім абзацам.
- [Harvard — Counterargument](https://writingcenter.fas.harvard.edu/counterargument): змістовне заперечення може вимагати уточнення тези, а не формального «спростування».
- [Harvard — Transitions](https://writingcenter.fas.harvard.edu/transitions): спочатку відношення між думками, потім маркер; відоме поняття може підтримати перехід.
- [Manchester — Signalling transition](https://www.phrasebank.manchester.ac.uk/signalling-transition/): переходи між частинами тексту й орієнтація читача.
- [Purdue OWL — Paraphrasing](https://owl.purdue.edu/owl/research_and_citation/using_research/quoting_paraphrasing_and_summarizing/paraphrasing.html): точне передавання суттєвого змісту своїми словами та атрибуція.

Пряме відкриття п’яти початкових Cambridge URL (Discourse markers, conversely, accordingly, in other words, that is to say) повернуло Internal Error інструмента. Це не трактувалося як прочитання сторінок. Через пошук отримано доступні індексовані редакційні тексти/фрагменти видавця:

- [Discourse markers — актуальна адреса](https://dictionary.cambridge.org/us/grammar/british-grammar/discourse-markers-so-right-okay): функції організації, зв’язування й керування мовленням; не лише логічні сполучники.
- [Conversely](https://dictionary.cambridge.org/us/dictionary/english/conversely): протилежний напрям/випадок, не універсальна заміна however.
- [Accordingly](https://dictionary.cambridge.org/us/dictionary/english/accordingly): відповідність дії ситуації; можливість висновкового вживання не доводить конкретного логічного переходу.
- [In other words](https://dictionary.cambridge.org/dictionary/english/in-other-words), [that is to say](https://dictionary.cambridge.org/us/dictionary/english/that-is-to-say?topic=defining-and-explaining): пояснення, уточнення; словникова функція не гарантує збереження всіх фактів у довільному тексті.
- [Nevertheless](https://dictionary.cambridge.org/us/dictionary/english/nevertheless), [nonetheless](https://dictionary.cambridge.org/us/dictionary/english/nonetheless), [having said that](https://dictionary.cambridge.org/dictionary/english/having-said-that): поступка та застереження.
- [Rather](https://dictionary.cambridge.org/us/dictionary/english/rather?q=rather_2): перевірено more exactly / or rather як самовиправлення окремо від ступеня й уподобання.
- [Put](https://dictionary.cambridge.org/dictionary/english/put), [precisely](https://dictionary.cambridge.org/us/dictionary/english/precisely?topic=very-and-extreme): значення «висловити словами» й точності.

Індексований доступ не видається за повне відкриття всіх Cambridge статей. Захист не обходився; корпусні приклади та коментарі не використовувалися як редакційні правила. Writing guidance зіставлено зі словниковими функціями, а не перетворено на універсальні граматичні закони.

## Окремий редакторський перегляд усіх 18 ключів

Це перевірка змісту, не лише автоматичне підрахування. Переглянуто граматику, природність, переклад, доступні факти, межі висновків і альтернативні формулювання.

| № | Завдання | Рішення та перевірені межі |
| --- | --- | --- |
| A1 | Теза/опора/міркування в абзаці про розклади | Речення 1/2/3; четверте обмежує висновок. Видимість не підмінена невиміряною швидкістю. |
| A2 | Невідоме припущення про кожен телефон | Уміститися не означає бути читабельним. Every phone виходить за факти. |
| A3 | Звузити every aspect / all users | Конкретне порівняння двох розкладів на описаному desktop, без вигаданих вимірювань. |
| A4 | Сильне заперечення та відповідь | Ризик читабельності на телефоні визнано; рекомендацію обмежено. Немає вигаданих позитивних тестів. |
| A5 | Афіша в понеділок, більше людей у вівторок | Прибрано і therefore, і недоведене caused. Сам порядок лишився фактом. |
| A6 | Аргумент про покажчик посібника Б | Теза + відомий покажчик + його функція + відсутність даних про швидкість; відкриті альтернативи дозволені. |
| D1 | Brief / nonetheless / all three stages | Поступка, можливе nevertheless; conversely не передає потрібного відношення. |
| D2 | Глосарій і покажчик як дві переваги | Moreover/furthermore/in addition, або ясний перелік без маркера. Не consequently. |
| D3 | Small archive, however, every issue | Крапка або крапка з комою між самостійними реченнями, пояснено роль however. |
| D4 | Уточнити this після назв і відсутніх дат | These missing dates називає саме потрібну причину невизначеності хронології. |
| D5 | Кольори маршрутів → чорно-біла копія | Питання про розрізнення підхоплює попередню тему; невідомий результат не вигадано. |
| D6 | Прибрати надлишкові переходи у звіті | Збережено два основні розділи, summary і costs; причинний маркер не потрібен. |
| P1 | Morning → 9.15 | Конкретизація додає точний час; перше речення саме його не містить. |
| P2 | Нотатка організатора про волонтерів | Збережено джерело, двоє, можливість, Monday, if, by noon та неназвані особи. May явно не дозвіл у цьому завданні. |
| P3 | Повернення посібника → confusing | Причину додано без даних; її можна вилучити або явно позначити лише можливим поясненням. |
| P4 | Not all/no, at least/exactly, some may/all will | Перевірені заперечення повноти, нижня межа кількості, розширення групи й посилення модальності. |
| P5 | Eligible applicants за вже даним визначенням | Розкрито всі три умови та збережено only; гарантії успішної реєстрації всім не додано. |
| P6 | Аудіогід на одному планшеті, без phone test | Точна атрибуція + окремий висновок про відсутність phone-result. Не вигадано навіть успіх планшетного тесту. |

A/D/P — лише номери рядків цього звіту, не рівні CEFR. Усі три уроки C2. Завдання й ключі англійською та українською наведені повністю на відповідних сторінках. Автоматично перевірено 18 різних формулювань і відсутність точного збігу з вправами 21 прийнятого уроку M11–M17.

## Застосування до робочої БД

Спочатку локальний домен не резолвився; стандартний hosts був відсутній. Apache і loopback-vhost відповідали HTTP 200. Системні налаштування не змінювалися агентом; користувач відновив домен і підтвердив роботу. Після цього реальні GET виконані за звичайним gramlyze.loc.

Використано незмінні LinkingWordsContentPatch/M11LocalTargetGuard через вузькі M18 wrappers і worktree→working CLI-адаптер. Guard перевірив loopback DNS, Windows MySQL PID/listener, Apache/vhost і nonce-bound відповідність web/CLI. Sandbox не дав процесної діагностики; лише необхідну read-only перевірку повторено з підвищеним доступом. Guard не послаблювали.

Послідовність: fresh proof → inspected preview → exact scope → новий exclusive record-backup → transactional apply/postcondition → repeat no-op.

- Proof: 2026-09-27 21:48:29 UTC.
- Plan: `m18-argumentation-cohesion-v1`, 12 змін; SHA-256 `45ca82c356a4bdc046545da692d2945a24cad17e43130dd907672d481f1a9043`.
- Apply: `status=applied, updated=12`.
- Repeat: `status=no-op, updated=0`; зайвий backup не створився.
- Приватний record-backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m18-local/records-before-final.json`, 4 126 883 байти. Це не повний дамп БД; файл збережено, до Git не включено.
- Після apply джерела контенту більше не редагувалися; guarded restore не знадобився.

| Page ID | Subtitle / hero / box IDs |
| --- | --- |
| 323 | 8856 / 8857 / 8858 |
| 310 | 8817 / 8818 / 8819 |
| 322 | 8853 / 8854 / 8855 |

Оновлено тільки pages.text і text_blocks.heading/body. IDs/UUIDs/owner/order, locale, level, Page.title, category/ancestry, tags/pivots, test relations і переклади збережено. Before-manifest у Git містить лише старі публічні definitions, не приватну БД.

## Автоматичні й живі перевірки

- ArgumentationCohesionContentPackageTest, ArgumentationCohesionContentPatchTest, M11LocalTargetGuardTest: **42 тести, 719 assertions, exit 0**.
- Перевірені JSON/hero, rich opt-in, 18 завдань/ключів, controller-derived H1 окремо від SEO-title, metadata/OG/Twitter, URLs, точні категорії/ancestry, immutable identities, manual/stale conflict, old→new/no-op, exclusive backup, transactional rollback/restore та відмова непідтвердженій цілі.
- PHP-тести — SQLite in-memory, окремі runtime paths. Production-профіль SEO — лише in-memory requests, не production HTTP.
- Одна наявна PHP 8.5 deprecation PDO у конфігурації; не падіння тестів. Перший прогін виявив неточності нових assertions H1/title та назви category FK; виправлено лише тести до apply.
- Node-контракти `seo-m18-local.test.cjs`: **8/8**.
- Pint: шість PHP-файлів — passed.

### HTTP

Before: 21:45:21 UTC; after: 21:50:33 UTC 27 вересня. **13/13 HTTP 200**: три теорії, три основні тести, курсова копія, два контролі, три додаткові сусіди та sitemap.

Матеріал є в початковому серверному HTML: по 6 завдань, 6 ключів, 8 rich-секцій. Немає plain fallback, сирих HTML-тегів, службового box чи технічних маркерів. Штатні основні test href збережені; нових дубльованих кнопок немає.

Page.title, фактичні H1, HTML title, canonical, robots і X-Robots-Tag незмінні. Локальний X-Robots-Tag: noindex, nofollow, noarchive; meta robots відсутній як before. Змінилися лише три descriptions через український hero intro та чинний PageMetadata; meta/OG/Twitter збігаються. Описи тестів, курсу й сусідів незмінні.

Ordered sitemap: фактично **554 URL**, порядок і SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334` незмінні. Кількість спостережена, не hardcode. .com у canonical/sitemap аналізувався лише як рядок.

### Браузер і візуальна перевірка

Chromium 147.0.7727.15; **6/6 сценаріїв**: три теорії × desktop 1440×1000/mobile 390×844, кожна у світлій/темній темі. Новий context, reload, server/DOM content hashes, контент нижче першого екрана, нумерація, details мишею, Enter і Space, три фактичні переходи до тестів — passed.

Контролі M17 Hedging і M16 Register Tone — **4/4 сценарії** у двох темах. Їхні контент і metadata незмінні.

Таблиці: 900 px у контейнері 322 px на mobile, фактичний scrollLeft 578 px для кожного уроку. Перші колонки мають мінімум 190/240/210 px відповідно; довгі назви не стискаються до фрагментів слів. Overflow документа немає. Змін у глобальних CSS/Blade/renderer/JS немає; чинний rich-renderer оформлює секції, приклади й ключі.

Збережено й переглянуто screenshots: вступні картки, мобільні таблиці зліва/справа, відкриті ключі світлої/темної тем, контрольні уроки, курсова копія. Додатково створено 6 screenshots довгих абзаців desktop/mobile; перевірені англійські блоки та переклад без виходу за картку.

Application pageerror — 0. Є лише 3 + 2 навмисно заблокованих автоматичних test-state POST у головному/контрольному runner; їхні ERR_FAILED/console error не є збоєм застосунку. Інших failed requests, зокрема Google Fonts, у цих фінальних проходах не зафіксовано. Відповіді й прогрес не надсилалися.

Курсова копія: HTTP 200, правильний H1, штатний gate «Урок заблоковано» для нового гостя; **gate не обходився**. Серверний HTML має 6 завдань/ключів, спільний resolver/blocks/render додатково перевірені ізольовано. Відкритий навчальний контент курсу для гостя live не приймався.

### Збереження захищеного стану

Read-only fingerprints **46 таблиць** before/after/commit-ready збігаються за винятком лише дозволених полів 12 записів. **21 прийнятий урок M11–M17** досі збігається зі своїми definitions. Questions/answers/options, verb_hint, relations, progress та інші дані незмінні; .env hash незмінний, APP_ENV=production, ключ і підключення не чіпали.

Тимчасовий proof route видалено: GET **404**. routes/api.php побайтно відновлено до початкового SHA-256 `5dba0c2624f8a8040aafe16230cc57bd050d6184c1e9e69297f5db7b4104a522`. Приватні inventory/proof/plan/record-backup/HTTP/browser/screenshots лишилися в storage/app/seo-m18-local поза Git.

## Межі завершення й Git

Не запускали повний suite, crawl, Lighthouse, M10 чи performance-кампанію. Build inputs не змінені — rebuild не потрібний. Нових mixed-питань не додано; основні тести приймалися лише як штатні переходи.

До commit входять лише три definitions, вузький before-manifest, шість PHP wrappers/tests/adapter, Node profile/test і цей звіт. .env, vendor/build, runtime caches, приватні матеріали та сторонні зміни виключені. Звичайний push — лише в codex/seo-m18-argumentation-cohesion-content; SHA і remote-підтвердження наведені у фінальній відповіді.

**Production не перевірявся й не змінювався.**
