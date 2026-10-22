# M75 — Possessive Forms, Reflexive Pronouns, Indefinite Pronouns

## Результат і межі

Три уроки застосовано до перевіреної локальної цілі `http://gramlyze.loc`. Збережено канонічну в’юху, усі continuing identities, джерельні навчальні функції й caller-owned межу theory. Окремої верстки M75, CSS, Blade, JS, нового grader або автоматичного apply немає. Четвертий урок не редагувався. Production, `main`, PR, deploy, merge, rebase і force-push не використовувалися.

- Accepted base: `67ebba5ed98fae05ca6022cac802870cfacf0d52`.
- Базова гілка: `codex/seo-m74-other-pronouns-basics-standard-view`.
- Робоча гілка: `codex/seo-m75-possessive-reflexive-indefinite-standard-view`.
- Served ROOT має окремий пізніший історичний стан; його сторонні dirty changes, видалення та EOL не включаються до цієї гілки. Зокрема стороннє видалення ROOT `.gitattributes` не відновлювалося.
- Перевірений content/code commit: `4cc1daa10a6727a85a106f7cdf246f79c2dba6fe`. Normal push цієї робочої гілки завершився exit0; незалежний `ls-remote --exit-code origin refs/heads/codex/seo-m75-possessive-reflexive-indefinite-standard-view` повернув саме цей повний SHA.
- Окремий documentation-only follow-up фіксує цей delivery result у звіті. Повний фінальний HEAD після нього та повторну remote verification наведено у фінальному повідомленні; власний SHA документа не вигадується як самопосилання.

## Перевірені сторінки

| Урок / H1 | Локальна сторінка | Page / category | Display | Фактичний SQL level практики | Reviewed own-practice focus |
|---|---|---|---|---|---|
| Possessive Forms | [Possessive Forms](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/possessive-adjectives-vs-pronouns-my-mine-your-yours) | 125 / 20 | A1–A2 | NULL | A2 |
| Зворотні займенники | [Reflexive Pronouns](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/reflexive-pronouns-myself-yourself-themselves) | 128 / 20 | A2–B1 | NULL | A2 |
| Indefinite pronouns | [Indefinite Pronouns](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/indefinite-pronouns-someone-anyone-nobody-nothing) | 122 / 20 | A2–B1 | NULL | A2 |

Owners — незмінені класи у `Database\Seeders\Page_V3\PronounsDemonstratives`:

1. `PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder`.
2. `PronounsDemonstrativesReflexivePronounsTheorySeeder`.
3. `PronounsDemonstrativesIndefinitePronounsTheorySeeder`.

Continuing practice bindings:

| Урок | TextBlock | UUID | source index / sort | Структуровані блоки + subtitle |
|---|---|---|---|---|
| Possessive | 4011 | `135baaed-d147-599a-b147-5b2c93924300` | 9 / 10 | 11 + 1 |
| Reflexive | 4103 | `aedf70d6-fcc4-57b0-b303-c98548d17060` | 9 / 10 | 11 + 1 |
| Indefinite | 3924 | `57ef1463-3d71-5a26-971a-53022c7be0b7` | 10 / 11 | 12 + 1 |

SQL NULL не замінювався ані точним CEFR, ані display range. Runtime explicit-link matcher використовує всі рівні точного тематичного type-4 банку. Старий diagnostic `eligible_count=0` від строгого SQL NULL не є цим matcher pool.

## Авторські доповнення і native projection

Повні редакції та явні рішення збережено у:

- `docs/content/m75-possessive-reflexive-indefinite.v1.0.0.json` — frozen master.
- `docs/content/m75-before-source.v1.json` — незалежна джерельна база порівняння.
- `docs/content/m75-possessive-reflexive-indefinite.editorial.v1.json` — editorial decisions, перевірки й джерела.
- `docs/content/m75-possessive-reflexive-indefinite.components.v1.json` — source → штатні компоненти.
- `docs/content/m75-possessive-reflexive-indefinite.tasks.v1.json` — retained / corrected / transferred / new task map.
- `docs/content/m75-possessive-reflexive-indefinite.v1.0.0.uk.md` — читабельна авторська редакція.
- `database/content-patches/m75-possessive-reflexive-indefinite.v1.json` — точний patch.
- Три чинні `Page_V3/.../definition.json` синхронізовані з AFTER.

Possessive: повна таблиця, власник і вся іменникова група, adjective між my та іменником, subject/object use, узгодження за предметом, singular they, its/it’s, межа `of its own`, `my own / of my own`, `a friend of mine`, питання й заперечення. Не використано правило про довільний «найближчий noun».

Reflexive: усі форми, один/кілька адресатів, singular they, reflexive object проти emphasis, контекстні `by / for / to oneself`, self-care, звичайне dress/wash/shave і доречний reflexive, взаємність проти дії на себе. Граматичні `She taught her Spanish` та `They love themselves` не названі універсально неправильними: вказано інший задум і учасників.

Indefinite: some/any/no/every за змістом, люди/речі/місця, offers/requests, free-choice any, no + affirmative verb, часткове `not everyone`, singular agreement і singular they, adjective після займенника, double-negative boundary нейтрального стандарту. Вибір не зведено до знака питання чи слова not.

Details: **0 / 0 / 0**, явне семантичне рішення finite projection. Потрібні короткі межі, переклади й застереження залишені у visible basic. Немає прихованого продовження речення, runtime word-count threshold чи нового дизайну. Перевірки owner/keyboard/deep-link для відсутніх details — N/A.

## Власна практика — 48 graded + 3 demos

На кожній сторінці: 5 selects + 2 meaning choices + 6 inputs + 3 rephrase = **16 оцінюваних завдань**, плюс один неоцінюваний демонстраційний приклад.

- 36 старих навчальних позицій збережено або явно виправлено зі збереженням функції.
- 12 нових позицій: 2 meaning choices + 2 full-sentence inputs на кожний урок.
- Два full-sentence inputs на урок використовують незмінений native manual/token path; slash chunks по 1–3 слова, shuffle, ручне введення й змішане редагування. Це не заміна окремого нижнього widget.
- Literal plain feedback має ключі для прийнятих значень і видимих options; EN-відповідь та український зміст прочитані після Check.
- У назвах нових вправ немає слова «поліглот».

Нове повне речення / доречні альтернативи:

| Урок | Inputs[4] | Inputs[5] |
|---|---|---|
| Possessive | `This is my new blue bag.` | `I need a room of my own.` / `I need my own room.` |
| Reflexive | `I bought this notebook for myself.`; також notepad | `We carried the sofa upstairs by ourselves.`; couch та without help у заявлених комбінаціях |
| Indefinite | `Everyone has their ticket.` / Everybody | `Nobody knows the answer.` / No one / No-one |

Важливі виправлення оцінювання, без зміни engine:

- Старий punctuation-insensitive input приймав і `Its a beautiful day.`, і `It's a beautiful day.`. У тому самому rephrase[3] перенесено функцію до семантичного вибору запису А/Б: приймаються `речення Б` / `Б`; неправильний вибір реально відхиляється. Це не заявляється як суворий apostrophe grader.
- Старий dash нормалізувався до порожнього значення. У двох Reflexive selects замість `—` використано явне непорожнє `Займенник не потрібен`. Unanswered і reset→empty отримують 0; свідомий правильний вибір — бал.
- `She taught herself Spanish` має видиму умову про самонавчання; `her` не названо неграматичним поза цим задумом.
- Someone/somebody, anyone/anybody, everyone/everybody, nobody/no one та інші declared aliases не відхиляються лише через вибір природного відповідника у власній практиці.
- У кінцевому exact-token widget умова явно звужує форму там, де старий банк має кілька синонімів. Це не твердження про універсальну неправильність інших природних речень.

## Окремі sentence builders — 15 питань, 5/5 на кожній сторінці

Контракт: `docs/content/theory-sentence-builders/m75-possessive-reflexive-indefinite.v1.json`.
Audit: `docs/content/m75-sentence-builder-bank-audit.v1.json`.

| Урок | Actual pool / SQL filter | Selected IDs | Рівні відібраних питань | Функції | Live completion |
|---|---|---|---|---|---|
| Possessive | 72 / NULL | 33255, 33256, 33260, 33261, 33262 | 5 × A1 | dependent my/your, its, independent mine/yours | 5/5 |
| Reflexive | 72 / NULL | 33771, 33774, 33778, 33780, 33782 | 5 × A2 | reflexive object, self-perception, alone, emphasis, reciprocal boundary | 5/5 |
| Indefinite | 72 + 2 / NULL | 33687, 33689, 33690, 59721, 59722 | 3 × A1 + 2 × A2 | some assertion, no-person, every, no-thing, free-choice any | 5/5 |

13 питань reused, 2 нових мінімальних insert-only питання:

- 59721 / `m75-indefinite-a2-01`: `Nothing is on the shelf.` — На полиці нічого немає.
- 59722 / `m75-indefinite-a2-02`: `Anyone can use this computer.` — Будь-хто може скористатися цим комп’ютером.

Джерело доповнення: `docs/content/m75-indefinite-question-supplement.v1.json`; bank identity `Database\Seeders\V3\PronounsDemonstratives\M75IndefinitePronounsSupplementSeeder`. Два питання type 4, A2; старі questions/answers/options/hints/variants/links не переписано. Жодної нової shared option: 15 наявних option rows повторно використано з exact collation/full-row guards.

Opaque audit містить усі 218 pool snapshots та partition: 15 selected + 203 scope exclusions. Exclusion не означає «питання неправильне»; це межа саме цього приймання. Не заявляється повний мовний acceptance усіх виключених питань.

Змінено лише finite caller-owned selection/condition projection. Ordered answers, casing/multiplicity, relation instances і оригінальні моделі не мутуються. Для королеви збережено обидва екземпляри The/the. У UI натискалися лише bank buttons; після кожного Check прочитано settled sentence, потім Next/completion. Порядок п’яти питань задає незмінений native shuffle, не порядок ID у таблиці.

Registry: M75 — 3/3 targets, 0 failures, 0 DB writes; повний registry — **148 targets**, 0 failures, 0 DB writes, попередні **145** structural records збережені. Це structural audit, а не повторне UI-проходження сотень старих відповідей.

## Guarded local apply та незалежні postconditions

Виконано: fresh independent BEFORE → source freeze/sync → fresh web/CLI target proof → fresh preview → exact diff review → exclusive backup → transactional apply → postconditions → повторний no-op → live browser → фінальна незалежна read-only звірка.

Тимчасовий nonce GET proof route працював лише на `http://gramlyze.loc`, loopback, без forwarded/auth/cookie приймання, без секретів і записів. Після apply/no-op route видалено. Окремий GET повернув **404**; raw routes bytes відновлено точно, сторонні зміни збережено. Route не входить у commit.

Preview logical digest: `e29635d33130d022d05c2fe3912b4a75b17bfd48dd52d8071f1ca7a221dfa891`.
Backup raw SHA: `837a5aae5e48730ea315c8c897c522c9cb8337fd5237d5b539d22be132970413`.
Backup/proof/raw SQL evidence залишені приватними; не комітяться.

| Операція | Кількість |
|---|---:|
| Existing Page.text updates | 3 |
| Continuing subtitle body updates | 3 |
| Continuing teaching body updates | 31 |
| Continuing practice body updates | 3 |
| Разом existing update operations | **40** |
| Нові questions | 2 |
| Нові question_answers | 10 |
| Нові question_option_question pivots | 16 |
| Нові question_theory_text_blocks links | 2 |
| Нові question_options / TextBlocks / deletes | **0 / 0 / 0** |
| Разом нові question-family rows | **30** |
| Distinct changed rows разом | **70** |

Командне `inserted=0` належить TextBlocks, не приховує 30 insert-only question-family rows. Повтор тієї самої reviewed plan: no-op, updated=0, inserted=0, deleted=0; повторного backup не створено.

Заключний `final-readonly-v4.json` після всіх browser/HTTP та останніх alias checks:

- SHA `78dd7d7d04899f3f609e74d1f15ed688f97181dc5c520d1b97adcd688c2f32b3`.
- 46 таблиць; exact independent BEFORE reconstruction та reviewed AFTER.
- 37 continuing UK rows; 74 EN/PL rows незмінні.
- Усі нецільові старі rows і timestamps exact; для 40 цільових rows перевірено лише дозволені поля.
- 5 allocation schemas, old options, taxonomy, category descriptions, progress, нецільові banks та saved-test membership збережено.
- 326 visual hashes і 2624 frozen-source hashes незмінні; M74 active 1.0.1 і попередні maps/releases не редагувалися.
- У самої фінальної звірки 0 SQL writes / 0 HTTP requests.

## Старі consumers та metadata

Незалежний AFTER old-consumer audit порівняно з до-редагувальним BEFORE: **24 HTTP consumers, усі 200**, 6 saved tests, 432 old questions. Курси, старі test source/question payloads і старі bank links/relations збережені. Порівняння охоплює course static learning DOM та валідовані dynamic question arrays, не лише marker/HTTP status. 46 таблиць однакові до й після цих GET.

Receipt `after-old-consumers-v1.json` SHA `ab5f7f0ef2c40d277754911d2b124a53a1dd0124d2437fbfb4917e12c1839ab3`.

Окремі незмінені mixed URL (84 питання кожний):

- [Possessive Forms mixed](http://gramlyze.loc/test/zaimennyky-ta-vkazivni-slova/possessive-adjectives-vs-pronouns-my-mine-your-yours).
- [Reflexive Pronouns mixed](http://gramlyze.loc/test/zaimennyky-ta-vkazivni-slova/reflexive-pronouns-myself-yourself-themselves).
- [Indefinite Pronouns mixed](http://gramlyze.loc/test/zaimennyky-ta-vkazivni-slova/indefinite-pronouns-someone-anyone-nobody-nothing).

Navigation audit: 9 destinations HTTP200; 27 footer chips, із них 24 clickable anchors і 3 поточні не-link items. Усі дев’ять початкових labels/current flags на owner збережено. H1 count/text, title, breadcrumb, native canonical/robots та LearningResource identity звірено з незалежним BEFORE. Canonical `.com` прочитано лише як рядок metadata; жодного запиту до production не було.

Contextual Some/Any destination [Some / Any — People](http://gramlyze.loc/theory/some-any/theory-some-any-people) окремо відкрито в браузері та отримано HTTP200. Reciprocal і Each/Every входили до navigation audit. Повний byte-identical HTML не заявляється: динамічні shell/CSRF елементи не є такою гарантією.

## Live browser acceptance

Дійсні, дедупліковані target states — **18**. Actual DOM viewport перевірено перед capture; невдалі початкові спроби viewport/theme не зараховано.

| Кожна з трьох сторінок | Light | Dark | Main client/scroll width |
|---|---|---|---|
| 1440 × 1000 | PASS | PASS | 877 / 877 |
| 390 × 844 | PASS | PASS | 358 / 358 |
| 320 × 844 | PASS | PASS | 288 / 288 |

На кожному стані: H1, завантажені fonts/assets, forms, переклади, retained/new practice, один widget і досягнення footer. Learning overflow=0, broken images=0, visible technical markers=false. На 390/320 таблиці мають локальний `overflow-x:auto`: 576 px усередині 322/252 px. Це очікуваний table scroll, не page-content overflow.

Декоративний animated shell іноді дає body width 391–404 на viewport390 та 321–330 на viewport320. Це окремий результат; звіт не заявляє zero document overflow і не маскує його CSS. Навчальний main не переповнюється.

Практичні UI-проходження:

- Усі 48 власних graded tasks хоча б раз правильно: 5/5 + 2/2 + 6/6 + 3/3 для кожного owner; demos не додають бал.
- **24 унікальні** full-sentence task × manual/token × desktop/mobile cases: 3 × 2 × 2 × 2. Повтори під час відновлення UI не додаються до числа.
- Apostrophe semantic wrong → correct; Reflexive unanswered / explicit absence / reset-empty; видимий контекст self-teaching.
- Live aliases: my own room, notebook/notepad у штатному банку/варіантах; couch / without help; Everybody і Somebody. Усі declared 68 accepted values додатково пройдено actual-factory тестом; не заявляється, що кожний із 68 окремо введено в live UI.
- На всіх трьох owners виконано empty, whitespace, repeated wrong → edit correct, повторний Check без подвійного score, reset→empty, delete/return/rebuild, mixed manual/token редагування. Autocomplete=off; word-suggestion/listbox не з’являвся у перевірених token paths. Scores інших груп залишилися незалежними.
- Усі три linked widgets реально завершено до 5/5; 15 settled sentences прочитані після Check.

Приватні full-page screenshots 18 станів та фінальний 5/5 screenshot не входять у commit. Browser zoom, глобальний localStorage і персональний progress не очищалися. Тимчасовий viewport override скинуто, власні допоміжні вкладки закрито, target results залишено доступними.

## Автоматичні тести — фактичні результати

SQLite `:memory:`, окрема testing environment, array cache/session, CLI OPcache вимкнено. Робоча `.env` не завантажувалася. 675 protected files до/після кожного ізольованого run: 0 changes.

| Run | Tests | Assertions | Failures / errors / skips | Warnings / deprecations | Exit |
|---|---:|---:|---|---|---:|
| `m75-final-own` — повний кінцевий M75 profile, 4 файли | 33 | 9529 | 0 / 0 / 0 | 0 / 1 | 0 |
| `m75-preservation` — 17 ширших історичних/current файлів | 136 | 27201 | 14 / 0 / 0 | 0 / 1 | 1 |
| `m75-generic` — generic/current complement | 30 | 14546 | 0 / 0 / 0 | 1 / 1 | 1 |

Кінцевий M75 run включає 8 content, 7 runtime, 13 writer та 5 builder-selection tests. Нові selection tests перевіряють frozen evidence, SQL NULL/range, stale/foreign guards, повні synthetic relations і condition-only clone без original mutation. Writer tests охоплюють реальний M75 insert-scope з generic transactional writer, schema/option/taxonomy guards, rollback та no-op.

Ранні запуски не додають нові унікальні tests: `m75-corrected` мав 28/5075, exit0, 1 deprecation; перший M75 run мав 2 помилкові нові test assumptions, виправлені без runtime/design змін. Sandbox preflight до ескалації не запустив tests через filesystem writable check; це setup failure, не test PASS.

**14 failures preservation залишено явними; набір не green:**

1. 2 `M74ContentPatchTest`: старий hardcoded-ID fixture / original C2 і shortage expectations проти active revision.
2. 5 `M74BuilderSelectionPresentationTest`: старий `v1.json` проти active `v1.0.1.json` audit path, зупинка на початковому exact assertion.
3. 3 `M74OwnedSharedSourceDeltaTest`: історичні whole-file source/controller/visual SHA expectations.
4. 1 `PracticeCompletionPackageTest`: поточний runtime додає `linked_practice` для Zero Conditional, старий exact expected array його не містить. Це current-runtime mismatch, не просто історичний hash failure.
5. 3 `M73SharedLineageTest`: історичні shared-source / primitive / visual SHA expectations.

Assertions/snapshots цих старих тестів не послаблювалися. Ці результати не названо ані green preservation, ані автоматично доведеними новими M75 регресіями. Окремі 9 active `M74BankRevisionRuntimeTest` пройшли, включно із successor owner-resolved C2/shortage guards.

Generic run: 11 `TheorySentenceBuilderAcceptanceTest` + 6 universal-content + 4 universal-rollout + ті самі 9 M74 active runtime. `SavedTestStepWrongAnswerTest` фактично не виявлено PHPUnit: старий `/** @test */` без `test_*`; його не зараховано як passed. Warning спричинив exit1. Deprecation count=1 у кожному run; конкретне повідомлення у збереженому output не розкрито, тому причина не вигадується.

Не підсумовувати 33+136+30 як 199 унікальних tests: 9 M74 cases повторено між preservation/generic. Це різні runs, не один full rerun.

Pure actual-native-factory Node test — PASS на двох незмінених парних профілях: accepted M74 WT та незалежно зафіксований served ROOT BEFORE. 48 graded, 3 demos, 47 displayed options, 26 declared correct options, 68 accepted values, 6 manual, 6 token, 6 delete-return/rebuild, 6 mixed paths, 96 repeated checks, 21 semantic cases. 0 network / SQL / file writes. Це **не live browser acceptance**; live наведено окремо вище. Новий тест допускає лише цілі точні raw/LF profile pairs, не довільні hashes чи змішані пари.

## Frozen checksums і джерела

`docs/content/m75-source-checksums.v1.0.0.json` SHA: `404c61c18fea10a6ded1d7fc71551e4979325bb9b8c60dedbfaf53406a96b4d8`.
Усі 13 записів manifest (10 artifacts + 3 active_definitions) звірено у staged blobs за raw bytes + length. Frozen supplement має два terminal LF, що явно задокументовано до delivery; його вже frozen SHA не «підчищено».

| Artifact | SHA-256 |
|---|---|
| Master 1.0.0 | `817b280b6e59bc60198a717b4b965238fa8d3ebcbc8a5181a8c909687db1f6e9` |
| Source BEFORE | `93ad3acd46790de520b4ef39d0f16d0b7300e43f77db5b111df8fe2a508c0e97` |
| Patch | `099f4173f18713ca568c15ba72e49a6e4e77789b2880507549a25b01ab22eaa5` |
| Builder contract | `3ad762f87af6b32c23df64d1ca3baf02993fdb6ba9760a1d79ae168dccb130a1` |
| Bank audit | `78872f9dfb4594e1c3caf58ee899c910ec7638d4bfbe867221e04e9f6dd6d0e6` |
| Question supplement | `84f09262cd8a20fba5ae6d4a505e93ec7463639dcb65407ac127a6c574695eea` |

Мовні джерела та scope доступу записані в master/editorial. Використані офіційні [British Council possessive adjectives](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/possessives-adjectives), [possessive pronouns](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/possessives-pronouns), [reflexive pronouns](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/reflexive-pronouns), [indefinite pronouns](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/indefinite-pronouns) та відповідні publisher-indexed Cambridge Grammar Today сторінки. Direct timeout / Cambridge403 відрізнено від indexed access; редакторську відповідь British Council відділено від learner comments. Вправи не копіювалися. Мовний review агентський, **не людський**; SEO-індексація чи позиції не гарантуються.

## Обмеження та delivery scope

- Prefreeze real-page 320 check був `not_checked`; після apply усі шість 320 light/dark target states перевірено. Ця послідовність явно не видається за prefreeze acceptance.
- Print, no-JS, WCAG audit — `not_checked`, не PASS.
- Усі виключені pool questions та сотні старих widgets не проходили повторно в UI; їхні дані/структурні boundaries збережено незалежними audit.
- Historical test failures і current-runtime mismatch наведені вище; сторонні джерела не виправлялися поза M75.
- До staging належать лише M75 runtime/package/definitions/tests/read-only tools, 12 finite shared registration lines, власні `.gitignore`/`.gitattributes` hunks і цей звіт. Authoring drafts/generation-only scripts, приватні screenshots/proofs/raw captures/backups, secrets, caches та сторонні ROOT changes виключено.
- Перед commit: exact allowlist 47 файлів, staged diff, усі 13 frozen manifest records у staged blobs, exact WT controller pin і 0 staged visual/route diffs — PASS. Whitespace check з `cr-at-eol` — PASS для всього scope, крім окремо перевіреного frozen supplement EOF-винятку; його raw SHA і два terminal LF exact. Default check бачить тільки 4 історично CRLF controller additions та цей declared EOF, не нові пробіли в тексті. Три нові непіновані diagnostic files механічно нормалізовано наприкінці, без зміни логіки чи runtime sources. Після commit: тільки normal push робочої гілки й незалежний `ls-remote` SHA == HEAD; deploy не дозволено і не виконується.
