# M83 — B2 Mixed Revision і п’ять базових уроків граматики

## 1. Статус і межі

**ЛОКАЛЬНЕ ВПРОВАДЖЕННЯ ПРИЙНЯТО Й CONTENT COMMIT ДОСТАВЛЕНО — усі шість уроків і точкову редакцію 1.0.1 застосовано; global registry 193, paired source/staged review і normal push завершено.** Початковий apply: 83 exact updates та 1 новий A practice, далі два no-op з 0 writes. Реально перевірено 36 станів сторінок, 64 manual/token випадки, усі 60 власних завдань і шість widgets до 5/5 з читанням 30 settled sentences. Знайдену нову помилку даних Imperatives виправлено редакцією 1.0.1: один continuing body update, що додає сім роздільників між прикладами; обидва inline no-op, canonical-package no-op, незалежні postconditions, cleanup/404 та повторні шість станів Imperatives виконано. Нові 46 isolated tests пройшли в ROOT/WT; старі 13 lists повторені з тими самими шістьма новими source-guard failures. Старі receipts не видаються за докази нових bytes; нижче явно розмежовані свіжі перевірки й transitive lineage.

Scope: шість UK-сторінок теорії, їхні versioned definitions, пояснення й власна практика та по п’ять питань чинного вбудованого конструктора. Не новий дизайн, не новий grader, не модернізація курсів або окремих тестів. Робота тільки на gramlyze.loc; production та gramlyze.ub не перевірялися і не змінювалися в межах M83.

Канонічні компоненти, кольори, рамки, типографіка, відступи та caller-owned межа theory зберігаються. Контент не обирає окрему M83-верстку. Короткі пояснення, переклади, контекстні критерії та необхідні застереження залишені у visible basic. Рішення про 0 «Докладніше» для кожного уроку семантичне, а не результат глобального порога довжини.

| Gate | Фактичний стан |
| --- | --- |
| Свіжий незалежний source/SQL/HTTP BEFORE | Отримано; 12 GET — 200, усі 46 SQL-таблиць незмінні під час читання |
| Окремий paired source BEFORE | 3333 шляхи, окремі RAW/LF/EOL-профілі та відсутності ROOT/WT |
| Course-source BEFORE | 122 шляхи A1/A2/B2: 57 прямих джерел, 17 caller-файлів, 48 views/assets |
| Чинний sentence-builder registry BEFORE | 187 сторінок; окреме read-only підтвердження |
| Чистий author projection | 6 уроків, 72 native-компоненти; без DB/HTTP |
| Незалежний old-consumer BEFORE | 48 успішних GET; ще 16 старих branded course-compose URL повертають 404 і мають статус not_checked |
| Заморожений фінальний пакет і coverage | M83 1.0.0; 19 frozen outputs включно з 6 definitions; 41 finite coverage row |
| Fresh proof → preview → exact diff → exclusive backup → apply | Виконано локально: 83 updates, 1 insert, 0 deletes / question writes |
| Повторний no-op та SQL AFTER | Два no-op: 0 writes; повна реконструкція BEFORE усіх 46 таблиць PASS |
| Old-consumer AFTER | PASS для exact збереження 64 станів: 48 HTTP 200 і 16 старих branded 404 not_checked; 46 таблиць незмінні під час GET |
| Sentence-builder registry AFTER 1.0.0 | Scoped 6 і global 193 targets: 0 failed / 0 writes; попередні 187 semantic contracts збережено exact |
| Sentence-builder registry AFTER 1.0.1 | Scoped 6 і global 193 v2: 0 failed / 0 writes; усі попередні 187 semantic rows exact, виключено тільки volatile http_sha256 |
| Нові M83 isolated tests 1.0.1 | 7 classes / 46 unique methods у двох factory-контекстах, кожен 23790 assertions / F0 E0 / skipped0 / exit0; 1 deprecation на запуск |
| Старі 13 test lists AFTER | 602 executions; F49 → F55 / E1; 6 нових source-guard failures явно описані в §8 |
| Основне реальне браузерне приймання 1.0.0 | 36 target states, 64 full-input cases, усі 60 own tasks, 6 widgets 5/5, 30 settled sentences і 30 footer clicks виконано; окремі обмеження в §10 |
| Post-UI SQL 1.0.0 та 1.0.1 | Початковий repeat — exact SQL AFTER; свіжий post-correction/post-UI v4 зберіг exact усі 46 таблиць відносно v3 і перевірив усі 41 source pairs |
| Точкова редакція 1.0.1 | Лише 7 supported br у 6 inline subtitles Imperatives; apply 1/0, inline no-op ×2, canonical no-op, postconditions, cleanup/404 і 6 реальних повторних states виконано |
| Commit / push | Content commit 6d4501df139580bc980b891e8b93e8e80771cf49, normal push у робочу гілку; незалежний ls-remote SHA == HEAD підтверджено |

## 2. Усі шість сторінок і фактичні рівні

Посилання нижче клікабельні. HTTP/H1 зафіксовані свіжим BEFORE; frozen package та локальний SQL AFTER зберігають ті самі заголовки й metadata. Усі шість actual H1 повторно прочитані на реальних сторінках у браузерній матриці 1.0.0. Корекція Imperatives 1.0.1 має окреме завершене повторне приймання всіх її шести viewport/theme states; це повтор тих самих targets, а не шість нових сторінок.

| № | Сторінка теорії | Фактичний H1 | SQL-рівень native-блоків | Авторський фокус / видимий рівень | Native-блоки BEFORE → AFTER |
| --- | --- | --- | --- | --- | --- |
| A | [B2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/b2-mixed-revision) | B2 Mixed Revision | B2 | B2 / B2 | 5 → 6 |
| B | [Parts of speech](http://gramlyze.loc/theory/basic-grammar/parts-of-speech) | Parts of speech | NULL | A1 / A1–A2 | 15 → 15 |
| C | [Sentence structure](http://gramlyze.loc/theory/basic-grammar/sentence-structure-svo) | Sentence structure | NULL | A1 / A1–A2 | 13 → 13 |
| D | [Sentence types](http://gramlyze.loc/theory/basic-grammar/sentence-types) | Sentence types | NULL | A1 / A1–A2 | 12 → 12 |
| E | [Imperative sentences](http://gramlyze.loc/theory/basic-grammar/imperatives-sit-down-dont-open-it) | Imperative sentences | NULL | A1 / A1–A2 | 13 → 13 |
| F | [Conjunctions](http://gramlyze.loc/theory/basic-grammar/basic-conjunctions-and-but-or-because-so) | Conjunctions | NULL | A1 / A1–A2 | 13 → 13 |

NULL — реальний збережений технічний рівень, не пропуск, який можна мовчки замінити на A1. Авторський фокус A1 та видимий напис A1–A2 не перетворюються на SQL/банківський CEFR-фільтр. Raw Page titles та фактичні theory H1 збережені; окремі course H1 можуть мати старі двомовні назви і не підміняються theory-редакцією.

Пов’язані mixed-сторінки зі свіжим HTTP 200 BEFORE; AFTER SSR та JSON payloads усіх шести залишилися exact BEFORE, 84 питання на кожній mixed-сторінці:

| Тема | Mixed-тест | Окремий фактичний browser navigation |
| --- | --- | --- |
| A | [B2 Mixed Revision — mixed](http://gramlyze.loc/test/mixed-revision/b2-mixed-revision) | Відкрито, actual H1 і 84 питання прочитано; відповіді не надсилалися |
| B | [Parts of speech — mixed](http://gramlyze.loc/test/basic-grammar/parts-of-speech) | Відкрито, actual H1 і 84 питання прочитано; відповіді не надсилалися |
| C | [Sentence structure — mixed](http://gramlyze.loc/test/basic-grammar/sentence-structure-svo) | Після початкового DOM stall повторно відкрито, actual H1 і 84 питання прочитано; відповіді не надсилалися |
| D | [Sentence types — mixed](http://gramlyze.loc/test/basic-grammar/sentence-types) | Відкрито, actual H1 і 84 питання прочитано; відповіді не надсилалися |
| E | [Imperatives — mixed](http://gramlyze.loc/test/basic-grammar/imperatives-sit-down-dont-open-it) | Відкрито, actual H1 і 84 питання прочитано; відповіді не надсилалися |
| F | [Conjunctions — mixed](http://gramlyze.loc/test/basic-grammar/basic-conjunctions-and-but-or-because-so) | Відкрито, actual H1 і 84 питання прочитано; відповіді не надсилалися |

## 3. Збережені й нові навчальні завдання

У джерелах B–F було 42 градуйовані позиції та 5 окремих неградуйованих demos. У frozen package й локальному apply збережено всі їхні навчальні функції та додано 18 нових завдань: разом 60 власних градуйованих завдань, ті самі 5 demos і 16 повних sentence inputs. Тридцять питань widget рахуються окремо, а не як власні 60 завдань.

| Урок | Старі graded → AFTER | Нові graded | Selects | Meaning choices | Inputs | Graded rephrase | Demos | Повні sentence inputs | Widget |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| A | 0 → 6 | 6 | 2 | 2 | 2 | 0 | 0 | 2 | 5 |
| B | 8 → 12 | 4 | 3 | 2 | 5 | 2 | 1 | 2 | 5 |
| C | 8 → 10 | 2 | 3 | 2 | 3 | 2 | 1 | 3 | 5 |
| D | 9 → 11 | 2 | 4 | 2 | 3 | 2 | 1 | 3 | 5 |
| E | 8 → 10 | 2 | 3 | 2 | 3 | 2 | 1 | 3 | 5 |
| F | 9 → 11 | 2 | 4 | 3 | 3 | 1 | 1 | 3 | 5 |
| Разом | 42 → 60 | 18 | 19 | 13 | 19 | 9 | 5 | 16 | 30 |

У B три короткі word-form inputs збережено, але їх не зараховано як повні речення; обидва старі повні граматичні розбори також залишено. У F одна стара comma-correction функція перенесена rephrase → semantic choice: це не вилучення старого завдання й не новий grader.

Для збережених завдань старі id/source_index та їхні відсутності не вигадуються заднім числом. Нові позиції мають власні стабільні M83 IDs. Окремий точний OLD → AFTER task ledger фіксує кожну стару, нову, демонстраційну та перенесену позицію.

Шістнадцять повних inputs мають видимі українські умови та перемішані чисті групи по 1–3 слова у чинному форматі native token bank. Використовуються існуючі manual/token, accepted aliases, edit, delete і reset механізми, без зміни глобального оцінювання. Реальні 64 manual/token випадки й 32 delete-return перевірки виконано; accepted manual aliases та mixed token/manual editing прийняті штатним механізмом. Відоме обмеження не приховується: прийняття еквівалентної ручної скороченої форми не означає, що кожен еквівалентний повний токен автоматично позначається використаним. Окрему поведінку native dictionary popup під час довгого граматичного розбору описано в §10; grader не змінювався.

## 4. Редакційні зміни та межі покриття за уроками

### A. B2 Mixed Revision

У тих самих п’яти continuing native-функціях збережено сім груп повторення та наявні EN-приклади. Додано українські пояснення й справжні переклади; уявне минуле Third Conditional не прирівнюється до обов’язкового жалю. Passive expectation розділяє джерело очікування, час можливої події та відсутність гарантії, що подія вже сталася.

Новий єдиний practice-set містить 2 form, 2 meaning і 2 повні sentence inputs. Пояснення про повні форми обмежене конкретними завданнями, які прямо цього вимагають; немає заборони скорочень для всього B2 або всього курсу.

Відібрані widget UUIDs: mr-b2-poly-b2-01, 02, 03, 04, 06. Вони вибірково охоплюють Future Continuous, Future Perfect Passive, modal perfect, Third Conditional і concession/past passive. Reported speech та nominalisation/relative мають власні sentence tasks; це не доказ вичерпного тестування всіх часів, wishes, reporting verbs чи формальних моделей.

### B. Parts of speech

Збережено навчальну схему восьми source-груп, не оголошуючи її єдиною універсальною класифікацією. Article/determiner note узгоджено з граматичними розборами. Word class, phrase і sentence role розмежовані: is та sleeping — окремі verbs у verb phrase; happy після linking be — не object. My перед noun та mine самостійно пояснені окремо; weather it не потребує попереднього предметного референта.

Для select виправлено реальні options: source keys noun/adverb/preposition більше не зіставляються з непридатними a/b/c. У трьох старих коротких inputs збережено навчальну функцію, але slash більше не ховає stem у випадковий token bank. Два старі розбори залишаються повними послідовностями 7/10 класів усіх слів; формат і English/Ukrainian accepted keys пояснені без нового parser. Додано 2 meaning tasks і 2 повні sentence inputs.

Widget: poly-pos-a1-01, 02, 03; poly-pos-a2-01, 02. Це вживання класів у реченнях, не автоматичний іспит із класифікації кожного слова або всіх значень прийменника.

### C. Sentence structure

SVO пояснено як поширену нейтральну transitive-модель, не обов’язкову формулу кожного речення. Збережено She sleeps як повне S–V; Object відділено від subject complement. Simple sentence визначається незалежною clause без залежної, а не кількістю людей чи написаних дієслів.

Видимо уточнено initial adjunct, passive subject, стан, question order та базовий imperative. Give/send recipient-to і buy beneficiary-for не подано як довільну взаємозаміну to/for. Старі учасники, objects і часові обставини залишені.

Три старі повні inputs мають чисті shuffled banks; старі rephrase тепер прямо просять нейтральний порядок без контекстного fronting. Додано 2 meaning tasks: повне неперехідне речення без вигаданого object та subject complement після linking verb.

Widget: poly-ss-svo-a1-01, 05, 06, 10, 11. Перевіряються нейтральне SVO, noun phrase object, місце adjunct, negative auxiliary та question order; не всі double-object alternations, пасивні й фокусні моделі.

### D. Sentence types

Чотири source-групи збережено, але polarity відділено від statement/question/imperative. Affirmative не посвідчує істинність факту. Питання, твердження і спонукання не оголошуються рівнозначними повідомленнями.

Do-support обмежено відповідними простими lexical-verb моделями; be/modals та subject wh-question не підганяються під одне правило. You like coffee? не оголошено завжди неграматичним: конкретне завдання просить нейтральне повне питання. Emphatic you в imperative можливе; його вилучення потрібне лише за явно заданим критерієм базової форми.

Збережено всі три full-sentence transformations, додано shuffled banks та 2 meaning tasks. Пунктуація — типовий письмовий сигнал, не універсальний класифікатор чи доказ перевіреної інтонації.

Widget: poly-stype-a1-01–05. Це affirmative/negative statements, yes/no та wh-questions, imperative. Наявність цих п’яти прикладів не означає оцінювання всіх negative questions, модальних моделей чи інтонації.

### E. Imperative sentences

Збережено шість функцій спонукання, affirmative/negative, be, please, let’s/let’s not, старі приклади, три full inputs, дві graded corrections та demo. Base form не плутається з першим написаним словом: Please та Let’s можуть передувати lexical verb. Підмет зазвичай опущений, але emphatic/contrastive you можливе. Наявність object та кількість адресатів не виводяться механічно з форми.

Природні переклади включають Be quiet → «Поводься тихо», Be on time → «Приходь вчасно». Помилковий No touch it відділено від нормальних написів No parking/No smoking. Please може пом’якшити прохання в контексті, а let’s включає мовця. Додано 2 meaning choices; у перетворенні полярності критерій видимий.

Widget: poly-imp-a1-02, 03, 06, 07, 10 — звичайна заборона, manner adverb без обов’язкового object, початкове please, affirmative be та object/place phrase. **Не заявляється**, що ці п’ять питань оцінюють усі шість прагматичних функцій, emphatic-you contrast, negative be, let’s/let’s not, каталог написів або інтонацію. Короткі винятки залишаються visible basic; 0 details.

### F. Conjunctions

Збережено всі п’ять груп and/but/or/because/so, слова/фрази/clauses, старі повні inputs, correction functions та demo. And додає інформацію, але не вимагає «подібності» думок. But спирається на явно заданий задум контрасту, а не соціальне припущення про rich/smart. Or не завжди означає взаємовиключність; neither-scope для конкретного заперечення задається видимо, без глобальної заборони not-and.

Because/so зберігають напрям причини й наслідку. Because of пояснено з конкретною noun phrase; so that розмежовано за значенням без універсальної заборони resultative-вживання. У полях перекладу стоять переклади, а не метакоментарі.

Реальні selects показують п’ять source options; неоднозначність прибрана видимими контекстними критеріями. Стару comma-only correction переведено у semantic A/B choice, бо чинний normalizer стирає різницю коми. Це точний перенос навчальної функції, а не заява про punctuation-sensitive grader. Додано ще 2 meaning tasks.

Widget: poly-conj-a1-01, a1-02, a2-01, a2-02, b1-01 — addition, explicit contrast, alternatives, postposed reason, result. Останнє питання має чинний B1, а practice — SQL NULL: рівень не переписаний для красивої квоти A1. Немає заяви, що widget перевіряє negation scope, because of, усі порядки clauses, comma production або всі значення so that.

## 5. Редакційні джерела та provenance

Нижче — первинні джерела з редакційного аудиту. Вправи видавців не копіюються. Статус доступу розрізняє реально прочитаний body, індексований primary excerpt та невдалий прямий запит. Заблокований 403/timeout не видається за прочитаний повний документ.

| Урок / питання | Джерело й фактична межа доступу |
| --- | --- |
| A: imagined past | [British Council — Third and mixed conditionals](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/conditionals-third-mixed), прямий editorial body; не learner comments |
| A: contractions | [Cambridge — Contractions](https://dictionary.cambridge.org/grammar/british-grammar/contractions), indexed primary text; прямий 403 |
| A: expectation | [Cambridge — Expect](https://dictionary.cambridge.org/dictionary/english/expect), indexed dictionary entry; прямий canonical запит 403 |
| B: word classes | [Cambridge — Word classes and phrase classes](https://dictionary.cambridge.org/grammar/british-grammar/word-classes-and-phrase-classes) та [Determiners](https://dictionary.cambridge.org/grammar/british-grammar/determiners-the-my-some-this), indexed primary text; прямі 403 |
| B/C: linking complement | [British Council — Link verbs](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/link-verbs), прямий editorial body; named-team reply у B відокремлено від body, learner comments не є правилами |
| B: classes and phrase | [Purdue — Parts of speech overview](https://owl.purdue.edu/owl/general_writing/mechanics/parts_of_speech_overview.html), прямий body; не використовується як привід мовчки злити несумісні терміни джерел |
| C: clause patterns | [British Council — Clause structure and verb patterns](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/clause-structure-verb-patterns), прямий body |
| C: double objects | [British Council — Double object verbs body at publisher anchor](https://learnenglish.britishcouncil.org/comment/204741), прочитано article body до коментарів; canonical resource timeout не названо успішним читанням |
| C/D: structural taxonomy | [Purdue — Sentence types](https://owl.purdue.edu/owl/general_writing/academic_writing/sentence_variety/sentence_types.html), прямий body; simple/compound/complex — структурна, не polarity-класифікація |
| C: fronting / complement | [Cambridge — Word order and focus](https://dictionary.cambridge.org/grammar/british-grammar/word-order-and-focus_2) та [Subject complements](https://dictionary.cambridge.org/grammar/british-grammar/subject-complements), indexed primary text; прямі 403 |
| D: do-support | [British Council — Questions and negatives](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/questions-negatives), прямий body |
| D: statement questions | [Cambridge — Statement questions](https://dictionary.cambridge.org/us/grammar/british-grammar/questions-statement-questions-you-re-over-18), indexed primary text; прямий 403 |
| D/E: imperative / you | [Cambridge — Imperative](https://dictionary.cambridge.org/grammar/british-grammar/imperative), indexed primary excerpt; прямий 403 |
| E: base / negative / please | [British Council Kids — Imperatives](https://learnenglishkids.britishcouncil.org/grammar-vocabulary/grammar-practice/imperatives), прямий body; спрощене omission rule не стає забороною emphatic you |
| E: let’s / let’s not | [Cambridge — Let](https://dictionary.cambridge.org/grammar/british-grammar/let), indexed primary excerpt; прямий 403 |
| E: signs | [Cambridge publisher PDF — Reading signs](https://www.cambridge.org/elt/ccc/pdfs/instpet-reading-signs.pdf), indexed publisher excerpt; прямий fetch timeout, повне PDF не заявлено прочитаним |
| F: commas | [Purdue — Extended rules for commas](https://owl.purdue.edu/owl/general_writing/punctuation/commas/extended_rules_for_commas.html), прямий body |
| F: alternatives / conjunctions | [Cambridge — Or](https://dictionary.cambridge.org/us/grammar/british-grammar/or) та [Conjunctions](https://dictionary.cambridge.org/us/grammar/british-grammar/conjunctions-), indexed primary excerpts; прямі 403 |
| F: reason / purpose / result | [Cambridge — Because of](https://dictionary.cambridge.org/grammar/british-grammar/because-of), [Purpose](https://dictionary.cambridge.org/uk/grammar/british-grammar/purpose), [So that](https://dictionary.cambridge.org/grammar/british-grammar/so-that...), indexed primary excerpts; прямі запити недоступні/403 |

Purdue відносить so до coordinating FANBOYS, тоді як Cambridge у своєму викладі включає so серед subordinators. M83 не вигадує спільну глобальну taxonomy: пояснює конкретні п’ять meaning/structure models та явно зберігає межу джерел.

Frozen provenance 1.0.0: m83-b2-revision-basic-grammar.v1.json (master), .components.v1.json (72 компоненти), .tasks.v1.json (точний OLD → AFTER ledger), .editorial.v1.json (редакційні рішення) та .coverage.v1.json (41 рядок: A6/B9/C7/D8/E5/F6) у docs/content. Окремо заморожені m83-native-bindings.v1.json, m83-navigation-review.v1.json, m83-sentence-builder-bank-audit.v1.json, source checksums, readable v1.0.0.uk.md та точні definitions. Початковий freeze має 19 outputs включно з шістьма definitions. Публічні frozen artifacts 1.0.0 не перезаписуються редакцією 1.0.1; лише active Imperatives definition переходить до її exact versioned корекції. Coverage прив’язує теоретичні моделі до конкретних own-task positions і п’яти selected widget UUIDs кожного уроку та явно зберігає uncovered limits; не оголошує повний курс або CEFR-іспит. Freeze/source/SQL PASS не підмінює live browser acceptance.

Редакція 1.0.1 має окремі source patch, master, checksums, component map, readable UK edition та exact correction manifest m83-inline-separation.v1.0.1.json. Авторська версія 1.0.1 не змінює native release/profile VERSION 1.0.0. Її точний scope і ще незавершені gates описані в §9.

## 6. Банки й old consumers: що захищено

| Урок | Чинний primary type-4 bank, усі рівні | Eligible за реальним SQL-рівнем practice | Selected widget |
| --- | --- | --- | --- |
| A | 72 | 12 B2 | 5 |
| B | 18 | 18, SQL NULL | 5 |
| C | 72 | 72, SQL NULL | 5 |
| D | 72 | 72, SQL NULL | 5 |
| E | 72 | 72, SQL NULL | 5 |
| F | 12 | 12, SQL NULL | 5 |
| Разом | 318 | 258 | 30 |

318 primary type-4, 258 eligible, 30 selected і 2641 фактичне linked question у 29 page-wide банках — різні множини, їх не можна підміняти однією цифрою. Наявні сторонні page-wide be/there links у F захищені як знайдені, але не стали кандидатами thematic widget.

Незалежний old-consumer BEFORE містить 11 strict direct/standard/final saved declarations і 18 supplemental saved declarations. Джерела AI мають saved metadata, зокрема underscore slugs; вони не видаються за вигадані нові публічні direct routes. UUID-проєкція supplemental джерел використовує ті самі чисті resolver-функції, без запуску seeder.

Знайдено чотири **старі** source-versus-actual membership розбіжності, які не виправляються пересіванням:

| Існуючий bank | Source → actual | Межа |
| --- | --- | --- |
| Opus46 Conjunctions | 108 → 107 | 1 source-only UUID |
| Opus46 VerbToBeNegatives | 144 → 143 | 1 source-only UUID |
| Opus46 VerbToBePastForms | 144 → 143 | 1 source-only UUID |
| Sonate SentenceTypes | 108 → 106 | 2 source-only UUIDs |

Це зафіксовані до M83 факти, не дозволені M83 insert/delete. AFTER аудит підтвердив exact old_sql, source hashes, saved rows і memberships, порядок, options/hints, linked theory та timestamps усіх 29 банків / 2641 питань. Чотири старі source-versus-actual розбіжності лишилися рівно тими самими; нових question/relation записів M83 немає.

Фактичний read-only registry AFTER 1.0.0: scoped audit усіх шести M83 owners та global audit 193 unique owner/URL targets завершено з 0 failed / 0 writes. Попередні 187 semantic rows — owner, locale, URL, practice identity, bank/CEFR/selection та інші contract fields — exact BEFORE. Volatile whole-HTTP hashes не приховувалися: 0 зі 187 збіглися повністю, 187 відрізняються; це не видано за byte-exact HTTP equality. Registry не проходив старі сотні відповідей у UI; нові шість widgets прийняті окремо до 5/5.

Після редакції 1.0.1 окремий scoped v2 audit усіх шести M83 owners — PASS. Native rows прив’язані до точного впорядкованого набору frozen sources: latest 1.0.1 та immutable 1.0.0; попередній source не переписано. Fresh global registry v2 на 193 targets і зіставлення попередніх 187 semantic contracts ще pending; початковий global PASS не перейменовано на новий receipt.

## 7. B2 course, final drill і прогрес

Курс збережений окремим caller-owned consumer. Незалежні BEFORE та SQL AFTER підтверджують: 16 lessons, 16 saved-test rows, 768 ordered membership rows і 768 course question rows. Усі 16 source-versus-actual порядки membership збігаються. Lesson 16 — polyglot-final-drill-b2 із 48 питаннями.

Два різні completion-представлення не змішуються:

| Джерело | Фактичне значення BEFORE і SQL AFTER |
| --- | --- |
| Raw saved-test final filters | score; minimum_rating_percent = 80; minimum_correct = 38 |
| Нормалізований public course manifest | rolling_window = 100; min_rating = 4.5 |

Це не твердження, що 38/48 дорівнює іншому normalized правилу; фіксуються обидва фактичні контракти. M83 їх не редагує.

Окремий source-аудит не підтвердив blanket-заборону contractions для B2. Збережено task-specific full-form criterion у Linking Words B2 та чинну word-order policy lesson 1, що допускає contractions. Урок теорії не змінює course keys, питання, filters, sequence чи прогрес.

BEFORE і AFTER збігаються exact: старий course catalog, шість course theory-help DOM і direct legacy final compose доступні з HTTP 200. Усі 16 branded /test/sentence-builder-…-b2/step/compose URL повертали й далі повертають старий 404; ці стани збережені exact і позначені **not_checked**, а не successful course interaction. M83 не виправляє цю сторонню маршрутизацію.

Course JS не виконувався, progress GET не викликався. Enrollment/progress/attempt rows захищені fingerprint усіх 46 фактичних SQL-таблиць; відкриття потенційно записувального progress endpoint не замінює read-only доказ.

## 8. Автоматичні BEFORE → AFTER: не весь набір зелений

Свіжий ізольований BEFORE повторив 13 accepted списків незміненим runner/parser: **602 method executions, 509 unique methods, 93 повторні виконання через overlap, 184002 assertions, F49/E1**. Свіжий AFTER v1 і пізніший AFTER v3 після редакції 1.0.1 порівняно саме з незалежним BEFORE v2 — без перейменування ітерацій, нормалізації paths, line numbers, messages або повних failure bodies.

AFTER має ті самі 602 executions / 509 unique / 93 overlap, **150109 assertions, F55/E1**. 0 added, 0 missing; 590 executions / 497 unique methods exact, 12 executions / 12 unique methods змінилися. Усі 675 protected files та old test bytes незмінні під час запусків; SQLite :memory:, array cache/session, без завантаження робочого .env або підключення до робочої MySQL.

| Список | Executions | Assertions BEFORE → AFTER | F/E BEFORE → AFTER | W | D | Exit BEFORE → AFTER |
| --- | --- | --- | --- | --- | --- | --- |
| M78 | 41 | 22119 → 22119 | 4/0 → 4/0 | 0 | 1 | 1 → 1 |
| M75 | 33 | 9522 → 9522 | 1/0 → 1/0 | 0 | 1 | 1 → 1 |
| M76 | 46 | 10575 → 10575 | 0/0 → 0/0 | 0 | 1 | 0 → 0 |
| M77 | 30 | 22051 → 22051 | 1/0 → 1/0 | 0 | 1 | 1 → 1 |
| Generic | 30 | 14819 → 14861 | 0/0 → 0/0 | 1 | 1 | 1 → 1 |
| Interaction | 134 | 1588 → 1588 | 8/1 → 8/1 | 0 | 1 | 2 → 2 |
| Wide | 136 | 27233 → 27239 | 15/0 → 15/0 | 0 | 1 | 1 → 1 |
| M79 | 28 | 7915 → 7915 | 4/0 → 4/0 | 0 | 1 | 1 → 1 |
| M80 ROOT | 20 | 5919 → 5919 | 4/0 → 4/0 | 0 | 1 | 1 → 1 |
| M80 WT | 20 | 5931 → 5931 | 4/0 → 4/0 | 0 | 1 | 1 → 1 |
| M81 ROOT | 24 | 7373 → 7373 | 4/0 → 4/0 | 0 | 1 | 1 → 1 |
| M81 WT | 24 | 7385 → 7385 | 4/0 → 4/0 | 0 | 1 | 1 → 1 |
| M82 | 36 | 41572 → 7631 | 0/0 → 6/0 | 0 | 1 | 0 → 1 |

W — warnings, D — deprecation у відповідному runner execution; skipped = 0. 13 deprecation occurrences у 13 запусках не названі 13 унікальними новими проблемами. Свіжий BEFORE був exact accepted M82 AFTER для всіх method/assertion/outcome/message/body/count/exit полів.

Шість **нових, не baseline** failures — методи незміненого M82OwnedSharedSourceDeltaTest:

- test_twelve_owned_additions_reverse_to_the_fresh_independent_raw_and_lf_before;
- test_theory_caller_and_old_retained_practice_guards_remain_unchanged_nonvisual;
- test_all_326_visual_profiles_and_existing_token_shift_label_binding_are_raw_exact;
- test_all_historical_sources_and_919_test_paths_keep_individual_raw_profiles_and_absences;
- test_a1_a2_course_source_and_caller_boundaries_retain_all_32_lessons_and_final_drills;
- test_e_f_navigation_adds_only_eight_approved_data_urls_without_native_validator_changes.

Усі шість проходили BEFORE, а тепер зупиняються в contexts() на старому whole-controller byte-profile guard: фактичний controller із чотирма M83 additions — частиною дванадцяти reviewed registrations у восьми shared files — не дорівнює жодному immutable accepted-M82 ROOT/WT AFTER profile. Ці методи не дійшли до своїх подальших visual/course/navigation assertions, тому їх не названо PASS. Інші 30 M82 method records та їхні 7455 assertions exact. Зниження M82 assertions спричинене ранньою зупинкою цих шести методів, а не видаленням assertions.

Ще чотири вже невдалі BEFORE source-guard methods мають **змінені повні failure bodies/hashes**: M77 lineage; M73 shared lineage; M74 dispatch lineage; M74 standalone controller lineage. Їхні counts/outcome залишилися failure, але reported actual source SHA змінився на M83 source. Вони не названі byte-exact baseline.

Два методи лишилися PASS, але мають більшу кількість assertions: dynamic catalogue 1312 → 1354 (+42 = 6 нових owners × 7 перевірок), M74 registry 193 → 199 (+6 owner contracts). Це реальне розширення проходження незмінених динамічних tests, не нова failure і не exact count equality.

Прийнятність finite M83 source delta доводиться окремими M83 source guards і SQL/source-аудитом, а не переписуванням старих M82 expectations. Старі тести, runner, parser та comparator не змінювалися. У NEW private summary wrapper додано лише допустиме CLI-ім’я actual iteration v1; comparison semantics не змінено.

Новий власний M83 test profile: фінальні v4 ROOT-factory і WT-factory запуски GREEN — кожен 38 methods / 23485 assertions / F0 E0 / skipped0 / exit0, з однією чинною deprecation у кожному runner. Це 38 унікальних методів, виконаних у двох контекстах (76 executions), не 76 унікальних тестів. Обидва SQLite :memory: запуски з array cache/session, working_env_loaded=false та CLI opcache=false зберегли всі 675 protected files. Offline native-JS replay охоплює 64 full-input mode cases, 32 delete-return cases та 26 semantic cases; це engine/model evidence, не браузерне приймання реальних сторінок.

Окремий завершений profile нових bytes 1.0.1: **7 classes / 46 unique methods / 23790 assertions / F0 E0 / skipped0 / exit0 у кожному ROOT-factory та WT-factory контексті**, по одній чинній deprecation. Це 46 унікальних методів і 92 executions, не 92 різні тести. Усі 675 protected files незмінні; тести ізольовані від робочої MySQL/.env. Ранні sandbox attempts не виконали набір, а перерваний v101c не названо PASS. Конфлікт імені test-helper методу json виправлено лише в новому revision test; assertions не послаблювалися.

Після корекції свіжий old AFTER v3 повторив усі 13 lists: **602 executions / 509 unique / 93 overlap / 150109 assertions / F55 E1 / W1 / 13 deprecation occurrences**. Усі 13 row-by-row counts_after objects exact AFTER v1, тому таблиця вище підтверджена свіжим повтором. Порівняно з незалежним BEFORE лишаються ті самі 12 changed unique methods, включно із шістьма **новими** controller byte-guard failures. Це не «всі тести зелені»; source-lineage failure bodies не оголошено byte-exact незмінними між редакціями.

## 9. Локальний apply, source lineage та незалежні AFTER-gates

Fresh локальний web/CLI proof, reviewed preview та exact diff перевірено до запису. Exclusive backup має ті самі raw bytes, що reviewed BEFORE preview; він не перезаписувався. Транзакційно виконано **83 updates / 1 insert / 0 deletes / 0 question-family writes**: 6 Page.text, 6 subtitle bodies і 71 teaching body; один новий practice-set тільки для A.

| Урок | Оновлених старих rows | Нові rows |
| --- | --- | --- |
| A | 7 | 1 practice-set |
| B | 17 | 0 |
| C | 15 | 0 |
| D | 14 | 0 |
| E | 15 | 0 |
| F | 15 | 0 |
| Разом | 83 | 1 |

П’ять B–F existing practice UUIDs/source slots/NULL levels, усі continuing metadata та 142 non-UK rows збережені. Немає type/heading переходів, нових question/option/hint/relation rows, масового пересівання або progress/enrollment записів.

Обидва наступні no-op дали 0 updates / 0 inserts. Незалежний SELECT-only final audit реконструював повний BEFORE усіх **46 таблиць**, повертаючи лише exact reviewed body/text cells і виключаючи тільки незалежно перевірений новий A practice. Reviewed protected projection, 77 continuing UK rows, усі старі timestamps, B2 course rows та source-sync — PASS. Це SQL/source evidence, не підміна browser acceptance.

Source-sync 1.0.0 охоплює 35 finite paths; окремий fresh source-sync 1.0.1 перевірив 41 finite path, не перевикористовуючи старий receipt. Окремо захищено 326 visual sources і 2826 historical frozen files. Fresh paired source inventory має 3333 paths; 6636 non-target context checks і 240 course-source context checks виконано для окремих ROOT/WT профілів. Post-correction paired source repeat також PASS: відносно 1.0.0 змінилися лише два RAW/LF profiles однієї Imperatives definition, по одному в ROOT/WT. Від початкового BEFORE лишаються рівно 15 changed paths / 29 profiles: шість definitions, вісім finite registration files і delivery attributes. Перевірено 16 точних shared reversals, 122 course paths та збереження 20 foreign untracked files; ROOT attributes залишаються відсутніми, WT зберігає exact foreign prefix. Сформовано явний delivery allowlist 72 файлів, без приватних доказів чи backups; staged review розглядається окремо.

Фінальний PHP lint перевірених M83 source paths: 36/36 PASS. Git diff whitespace check з явним cr-at-eol — PASS; default check позначає чотири нові CRLF controller lines як whitespace через збережений binary/EOL attribute profile. Це відмінність режимів перевірки, не привід нормалізувати цілий foreign-inclusive shared file; його попередні bytes/EOL не переписувалися. Останній staged review також прибрав лише зайві порожні EOF-рядки у трьох нових M83 diagnostics, без зміни виконуваних statements; їхні попередні receipts не перезаписано.

Рівно 12 nonvisual registration additions у 8 shared files: content source 1, HTML adapter 2, legacy 1, renderer 1, linked practice 1, matcher 1, course preservation 1, standalone controller 4. У 16 source/context перевірках reversal тільки нових рядків відновлює exact RAW і LF BEFORE. Існуючі foreign-inclusive ROOT/WT differences та CRLF/LF збережені; не заявляється, що всі source-sync files мають однакові raw bytes між контекстами. Native release validator та дизайн незмінні.

Додано 30 незалежно перевірених footer destination URL data fields; title/labels/order/current flags збережені. Це навчальні URL-дані, не зміна resolver або navigation component.

Обидва тимчасові proof routes — початковий 1.0.0 та окремий fresh 1.0.1 — видалено після відповідних apply/no-op. Routes повернуто до exact попередніх 1742 bytes зі збереженням сторонніх змін; окремий GET кожного видаленого nonce endpoint повернув 404 без redirect. Другий cleanup має окремий фактичний receipt, не копію першого. Apache/XAMPP/hosts не змінювалися; тимчасового route у delivery немає.

Old-consumer AFTER v1 і свіжий AFTER v2 після корекції: read-only перевірки прив’язані до exact BEFORE v2 та незалежних SQL receipts. Усі 64 route states, question payloads/theory-help DOM/course manifest, source hashes та old_sql exact; із них 48 GET успішні (200), 16 старих branded course-compose gates — 404/not_checked. Збережено 29 banks / 2641 question families, 11 strict + 18 supplemental saved declarations та B2 16 lessons / 768 memberships / 768 question rows / final48. Усі 46 таблиць незмінні протягом GET і збігаються з відповідним independent SQL AFTER. Немає JavaScript, progress endpoints, cookies/auth або SQL writes.

Після основної браузерної матриці 1.0.0, 64 full-input cases і шести widgets виконано незалежний SELECT-only post-UI repeat: усі 46 таблиць exact попередньому independent SQL AFTER, включно з course/progress families. Після корекції та її browser/navigation acceptance виконано ще один свіжий post-UI v4: усі 46 таблиць exact post-correction v3; усі 41 source pairs також перевірено свіжо. Межа original reconstruction і captured schemas цього повтору явно зазначена нижче; старий post-UI receipt не перейменовано на доказ нових bytes.

### 9.1. Окрема точкова корекція Imperatives 1.0.1 — apply/no-op/postconditions/browser виконано

Візуальний огляд знайшов нову, створену даними M83 проблему: шість forms-grid inline subtitles Imperatives містили сусідні paragraph fragments, які чинний inline formatter з’єднував без роздільника. Це не приховано під старим baseline і не виправляється зміною formatter або CSS.

Exact versioned корекція додає лише сім підтримуваних br-роздільників у шести subtitle fields: 2/1/1/1/1/1, +28 UTF-8 bytes у тілі одного continuing block. Усі 13 EN/UK example pairs, слова, переклади й пунктуація лишаються exact. Інші п’ять definitions, уся практика, widget selection, levels, metadata та continuing identity не змінюються. Старі public frozen artifacts і приватні receipts не перезаписуються.

Canonical sources 1.0.1 підготовлено, fresh source-sync 41 paths і нові isolated tests виконано. Після свіжого one-cell preview, exact review та exclusive backup транзакційно застосовано **1 body update / 0 inserts / 0 deletes / 0 question-family writes**. Це повторне оновлення одного з уже змінених старих rows, не новий distinct row: сукупно дві редакції виконали 84 update operations над 83 continuing rows та створили 1 A practice row.

Обидва повторні inline apply дали no-op: 0 updates / 0 inserts. Окремий fresh canonical-package preview та повторний canonical apply також дали no-op 0/0; generic пакет не обходив частковий стан і не переписував інші п’ять owners.

Незалежні postconditions v3 звірили фактичні fingerprints усіх 46 таблиць з immutable post-UI 1.0.0 receipt v2, нормалізуючи лише exact reviewed body одного continuing block 1153 до попередніх bytes. Новий post-UI v4 потім підтвердив exact фактичний стан усіх 46 таблиць відносно v3 та всі 41 fresh source pairs. Original BEFORE reconstruction і source lineage у цьому ланцюгу **успадковані через явний transitive proof chain**, а не виконані повторно новим повним original scanner. У нових receipts захоплено schemas 11 таблиць, **не 46 schemas**; це не зменшує обсяг row fingerprints 46 фактичних таблиць, але й не видається за повний schema rescan. Жодного додаткового запису під час цих SELECT-only повторів немає.

Proof route 1.0.1 видалено з exact restoration та окремим GET 404. Окреме повторне browser acceptance Imperatives, scoped registry v2, global registry 193 v2 і old-consumer AFTER v2 завершено. Global registry зберіг exact 187 попередніх semantic rows: 0 однакових і 187 різних whole-HTTP hashes обліковано окремо, не видано за повну HTTP equality. Для нових шести owners native discovery містить точну впорядковану пару immutable sources 1.0.1 та 1.0.0; matcher/старі 187 contracts не змінені.

## 10. Фактичне браузерне приймання 1.0.0 та його межі

Браузерні дії виконано на справжніх сторінках gramlyze.loc після локального apply, без очищення personal progress або global localStorage. Expected умови, ключі та settled sentences прив’язані до окремого frozen contract до UI перевірок, не зчитані як expected із самого AFTER.

| Урок | Actual target states | Повні inputs | Manual/token × desktop/mobile cases | Widget | Прочитані settled sentences | Реальні footer destination clicks |
| --- | --- | --- | --- | --- | --- | --- |
| A | 6 | 2 | 8 | 5/5 | 5 | 5 |
| B | 6 | 2 | 8 | 5/5 | 5 | 5 |
| C | 6 | 3 | 12 | 5/5 | 5 | 5 |
| D | 6 | 3 | 12 | 5/5 | 5 | 5 |
| E | 6 | 3 | 12 | 5/5 | 5 | 5 |
| F | 6 | 3 | 12 | 5/5 | 5 | 5 |
| Разом | 36 | 16 | 64 | 6 × 5/5 | 30 | 30 |

Кожні шість states — 1440×1000, 390×844 і 320×844 у light/dark; actual viewport/theme перевірено, zoom не змінювався. Прочитані H1, завантаження шрифту, native-компоненти, переклади, anchors і один widget на кожній сторінці; сирих HTML/service markers у перевірених полях не виявлено. Семантичних point-level details немає — initial-closed/keyboard/detail ownership позначено N/A, а не перевіркою стороннього mobile TOC details.

На 60 власних graded controls перевірено 86 реально показаних select/choice options, включно з distractors, та 74 declared accepted keys/aliases. Усі п’ять demos прочитані окремо й не отримують score. Виконано empty/whitespace, wrong→edit→correct, повторний Check без подвійного бала й reset. Для всіх 32 token-mode cases перевірено used token → видалення тексту → повторна доступність; mixed token/manual editing та повторне складання прийнято. Обидва повні граматичні розбори B перевірені англійськими й українськими послідовностями класів, а не замінені одним коротким word-class питанням.

У шести widgets справді отримано 5/5, після Check прочитано всі 30 visible settled English sentences, звірено case/apostrophes, BANK labels, answer chips і multiplicity, Next та завершення. Reload/deep-link зберіг 5/5; сторінки не скидалися шляхом очищення сховища. Кожен із 30 footer destinations реально відкрито натисканням, перевірено destination URL/H1 та повернутося назад. Усі шість mixed destinations зрештою відкрито в UI: actual H1 та 84 rendered questions прочитані; початковий DOM stall C не приховано, успішний повтор зафіксовано окремо. Відповіді mixed не надсилалися, тому navigation/render PASS не є answer-interaction PASS.

### Окреме повторне приймання Imperatives 1.0.1

На actual сторінці повторено всі шість viewport/theme states. Read-only DOM підтвердив шість function cards, 13 exact EN/UK pairs та сім supported br; actual card overflow не виявлено. Чотири існуючі practice groups та один штатний widget збережені; 5/5 лишилося після reload, а видиме завершене речення Listen carefully прочитано без нового проходження або reset. Практика й selected question data не змінювалися семи-роздільниковою корекцією: початкові 64 interaction cases не видаються за повторно виконані після 1.0.1.

Шість нових captures hash-bound до окремого revision receipt; статично переглянуто всі шість і вісім native-scale crops. У всіх чотирьох mobile states усі 13 example pairs візуально відокремлені; при 320 px є природний wrap усередині пари, без card escape/overlap або raw HTML. У двох desktop captures повністю видно перші чотири cards / дев’ять pairs, а нижні дві cards обрізані viewport — їхні тексти підтверджені DOM, але не видаються за повністю видимі в цих кадрах. Full-page canvas width mismatch також не названо загальним whole-page overflow PASS. Native design лишився тим самим.

Одне початкове mobile theme/footer pointer натискання відкрило інший read-only words/test destination замість наміченого елемента; точну pointer-причину не доведено. Цей крок не названо PASS: повернутося до target без відповідей або reset, тему надалі переключено штатним keyboard activation і фактичний theme state перевірено. Наприкінці знову відновлено початкові dark theme та viewport.

### Native dictionary popup: початкові невдалі pointer attempts не приховано

У Parts of speech під час введення довгого англійського grammar analysis app-owned native dictionary suggestion popup міг перекривати pointer Check. Три початкові спроби не прийнято як PASS: в одній видимий текст іншого rephrase змінився на словникову підстановку. Перегляд чинного коду підтвердив, що Check лише вмикає feedback, а native popup має власний delayed lookup та insertion шлях; точну початкову послідовність focus/caret подій не доведено. Це не названо новою помилкою grader і не «виправлено» поза scope.

Повторні ordinary UI перевірки активували фактично видиму штатну кнопку Check клавішею Enter та чекали видимого score. Обидва English/UK аналізи й усі aliases пройшли; paired fill із звичайним blur також пройшов 2/2. Отже, прийнятність відповідей доведена, але ствердження «кожний початковий pointer click працював» було б неправдивим. Native suggestions, engine і правила оцінювання не змінені.

### Візуальний огляд: не загальний visual PASS

Збережено й переглянуто 72 matrix screenshots, 6 full-page light captures та 6 widget-completion captures — разом 84, додатково 40 overlapping main-column slices у native scale. Початковий static review 1.0.0 має статус reviewed_with_explicit_caveats, **overall_visual_pass=false**; він не перезаписаний після корекції й не є її новим receipt. Окремий revision review шести нових кадрів описано вище. У перевірених станах не знайдено learning text/card overlap або actual content overflow, але це не тотожне нульовому document scroll overflow.

- У native dark theme світлі B2/A1 badges мають помітне обмеження контрасту. CSS, палітру й дизайн у контентному M83 не змінено; повну WCAG-відповідність не заявлено.
- Aria-hidden декоративні shell shapes у частині mobile captures збільшують document scroll width приблизно на 1–13 px. Actual content overflow list був порожнім; декоративний overflow не видано за content overflow і не сховано новим CSS.
- У довгих full-page captures sticky header може компонуватися посеред документа, а нижня tests-card область іноді захоплюється порожньою. Це screenshot-capture limitation; її не використано як доказ відсутності DOM links, які окремо прочитані й натиснуті.
- Конкатенація Imperatives examples — новий дефект даних M83, не лише capture artifact. Його exact корекцію 1.0.1 застосовано локально й окремо прийнято в шести реальних states; новий receipt не змінює історичного невдалого visual status 1.0.0.

Після основного приймання повернуто початкову dark theme і штатний viewport. Зібрані тоді browser console error/warn logs порожні; це не повний network/performance audit.

## 11. Доставка й явні обмеження

Content commit: **6d4501df139580bc980b891e8b93e8e80771cf49**. Його єдиний parent — accepted base **6d1681c9e5b7ffc06d47b00537c87a5588b05df8**. Normal push виконано у **codex/seo-m83-b2-revision-basic-grammar-standard-view**; окремий git ls-remote підтвердив повну рівність remote SHA та content HEAD.

Staged audit перевірив точні 72 файли, усі 6666 fresh ROOT/WT source profiles, відповідність кожного staged blob перевіреним working bytes, EOL/whitespace та збереження 20 foreign untracked files. Приватних receipts, runtime caches, backups, dumps або секретів у commit немає. Після source audit змінено лише фінальний звіт і видалено зайві EOF-рядки трьох нових diagnostics; ці точні зміни перевірені окремо. Content commit має лише 12 registrations / 0 removals у восьми shared files.

Це фінальне доповнення звіту доставляється окремим report-only commit поверх перевіреного content commit; воно не змінює навчальні джерела, SQL або прийнятий runtime. Остаточний report-only HEAD перевіряється окремим normal push та remote SHA comparison; власний SHA commit не вбудовується рекурсивно в його текст.

Нові M82 source-guard failures описані в §8 і не оголошені старим baseline або «всі тести зелені». Повний transcript/raw diagnostic evidence залишається приватним; public report містить факти й межі перевірок.

Print, повний WCAG-аудит і курсова JavaScript-прогрес-сесія — not_checked. No-JS SSR читання не є доказом повної no-JS працездатності практики. Шістнадцять старих branded course-compose 404 лишаються not_checked, а не успішно прийнятими інтерактивними уроками курсу.

Main, PR, merge, force-push, workflow dispatch і deploy не виконувалися. Сервери production та gramlyze.ub не змінювалися. Приватні SQL snapshots, runtime identity, секрети, backups, screenshots та діагностичні raw payloads не публікуються у звіті або Git.
