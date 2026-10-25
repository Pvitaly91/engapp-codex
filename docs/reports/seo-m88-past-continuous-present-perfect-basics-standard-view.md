# M88 — Past Continuous / Present Perfect: звіт про локальне впровадження

Статус: навчальний пакет застосовано локально; SQL/source postconditions і SSR завершені. Власні ізольовані перевірки пройшли. Загальний старий regression gate має STRICT FAIL, не прихований і не переведений у PASS. Повний registry AFTER 223 і фактичне UI-приймання шести сторінок завершено з явними межами. Після останніх UI-дій виконано окремий post-UI SELECT-only audit (див. розділ 6). Git delivery виконується окремо після review цього звіту; full commit SHA та незалежна remote verification будуть у final handoff/private Git receipt. Цей звіт не стверджує, що commit/push уже відбулися.

## 1. Межі та сторінки

Робота лише в локальному Gramlyze, без production/deploy/main. Робоча гілка: `codex/seo-m88-past-continuous-present-perfect-basics-standard-view`; accepted base `c15a34259f5f95621cce871208052f7c8473b59f`. Навчальні дані модернізовано під чинні native-компоненти; окремого дизайну M88, нового виду компонента або нового глобального правила оцінювання не додано.

| Код | Сторінка теорії | Page / category | Фактичний SQL level | Display / editorial focus | Власна практика після M88 |
|---|---|---|---|---|---|
| A | [Past Continuous: Forms and Use](http://gramlyze.loc/theory/tenses/past-continuous/past-continuous-forms) | 154 / 24 | NULL | A2 / A2 | нова, slot 7; 2 form + 2 meaning + 2 full inputs |
| B | [Past Continuous: Negatives](http://gramlyze.loc/theory/tenses/past-continuous/past-continuous-negatives) | 155 / 24 | NULL | A2 / A2 | нова, slot 7; 2 + 2 + 2 |
| C | [Past Continuous: Questions and Short Answers](http://gramlyze.loc/theory/tenses/past-continuous/past-continuous-questions) | 156 / 24 | NULL | A2 / A2 | нова, slot 8; 2 + 2 + 2 |
| D | [Past Continuous: Time Expressions](http://gramlyze.loc/theory/tenses/past-continuous/past-continuous-time-expressions) | 157 / 24 | NULL | A2 / A2 | нова, slot 7; 2 + 2 + 2 |
| E | [Present Perfect: Forms and Use](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-forms) | 174 / 29 | NULL | A2–B1 / A2 | збережена, slot 6; 2 + 2 + 2 |
| F | [Present Perfect: Negatives](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-negatives) | 175 / 29 | NULL | A2–B1 / A2 | збережена, slot 6; 2 + 2 + 2 |

SQL NULL — фактичний технічний фільтр, а не editorial A2. Його не замінено display-діапазоном. Для кожної сторінки окремо підключено п'ять питань власного type-4 банку внизу практики; це не частина 36 власних controls.

Основні mixed-URL, охоплені окремим старим consumer collection (не новими питаннями M88): [Past Continuous forms](http://gramlyze.loc/test/past-continuous-forms-all-levels-v3), [negatives](http://gramlyze.loc/test/past-continuous-negatives-all-levels-v3), [questions](http://gramlyze.loc/test/past-continuous-questions-all-levels-v3), [time expressions](http://gramlyze.loc/test/past-continuous-time-expressions-all-levels-v3), [Present Perfect forms](http://gramlyze.loc/test/present-perfect-forms-all-levels-v3), [negatives](http://gramlyze.loc/test/present-perfect-negatives-all-levels-v3). Наявність GET у collection не означає strict незмінність його HTML: результати наведено в розділі 9.

## 2. Канонічні джерела й семантичні зміни

Заморожено 23 output-файли: 17 публічних артефактів і 6 цільових definitions, матеріалізованих у двох source-контекстах із перевіркою raw SHA. Канонічний master: `docs/content/m88-past-continuous-present-perfect-basics.v1.json`; patch: `database/content-patches/m88-past-continuous-present-perfect-basics.v1.json`; незалежний source BEFORE, bindings, checksum, navigation, author parts і bank contract — окремі файли пакета. Master SHA `0d6064ef72590b3e77dc044c3ba3b6cdc3dddd020cb7cf185c89a2c041e03f76`; patch SHA `2d2888db61cdbae09055c6c0b3982b552ad5849113ab52d79235e1d16449cc35`.

Усі первісні навчальні функції відображено в finite `component_map` і `task_map`. Немає box migration: збережено тип, column, порядок і власника кожного continuing block. Strong bilingual H1, навігаційні labels/order/current та існуючі practice identity збережено. Короткі пояснення й застереження видимі в basic; семантичне рішення — **0 details**, не quota або runtime word threshold.

Ключові уточнення:

- A: `was/were + -ing`, іменникові підмети й написання `running`; процес у минулий момент не доводить завершення або відправлення листа. Подія з `when` не означає автоматичної зупинки процесу. `think` у значенні думки відрізняється від активного обдумування.
- B: full/contracted negatives, узгодження, lexical `doing` без зайвого `did`. Заперечення дії о певній годині не означає `never`, `nowhere` чи конкретну іншу дію.
- C: neutral question проти можливого echo-питання; `who`-підмет без додаткового `he/she`; lexical `doing`; explicit speaker/addressee для `Were you…?`; short-answer polarity задається фактом, не вгадується. Перекладено і питання, і обидві відповіді в порівняльній таблиці.
- D: маркери часу задають орієнтир, а не механічно вибирають tense. `this time yesterday` прив'язано до однієї явно заданої розмови; у form-select потрібен вислів із власним значенням «той самий час попереднього дня», не anaphoric `at that moment`. `while` показує overlap без однакового початку/кінця; `when` не доводить переривання.
- E: `have/has + V3`, regular/irregular forms, результат/досвід/відкритий період/стан донині; `since/for`, `been/gone` і первісні моделі залишено. «Отримали листи» не означає «прочитали й відповіли».
- F: `haven't/hasn't + V3`, `not yet`, `never`, обидві наявні позиції `yet`, full/contracted варіанти. `haven't finished` не дорівнює «не починали» і не обіцяє завершення; `haven't lived here for long` не означає «ніколи тут не жили».

Переклади у `ua` є перекладами, не повторними англійськими формулами. Граматичний аналіз залишено в supported note. Для 19 mistake items є окремі `wrong_en/wrong_ua/right_en/right_ua` та видимий `note`; переклад помилкового прикладу передає задум і не легітимізує помилку. Неправильність обмежена видимою умовою завдання, без blanket заборон на граматично можливі інші контексти.

Первинна перевірка правил: British Council LearnEnglish [Past continuous](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/past-continuous), [Questions and negatives](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/questions-negatives) та каталог Cambridge English Grammar Today у master `sources`. У каталозі чесно розрізнено direct editorial body і indexed primary excerpt; окремі canonical Cambridge GET повертали 403. Learner comments не використовувалися як нормативне джерело; publisher exercises не копіювалися.

## 3. OLD → AFTER: компоненти

Індекси нижче нульові, source-based; стрілка того самого типу означає збережений слот і native-компонент, а не заміну верстки.

| Owner | Source indices / OLD → AFTER | Зміст редакції |
|---|---|---|
| A | 0 hero→hero; 1 forms-grid→forms-grid; 2 usage-panels→usage-panels; 3 comparison-table→comparison-table; 4 mistakes-grid→mistakes-grid; 5 summary-list→summary-list; 6 navigation-chips→navigation-chips; absent→7 practice-set | Переклади всіх source моделей; noun agreement/spelling; process/result/stop limits; 3 paired mistakes; точні footer href; нова практика |
| B | 0 hero→hero; 1 forms-grid→forms-grid; 2 usage-panels→usage-panels; 3 comparison-table→comparison-table; 4 mistakes-grid→mistakes-grid; 5 summary-list→summary-list; 6 navigation-chips→navigation-chips; absent→7 practice-set | Scope Continuous-negation, full/short forms, no-extra-did, lexical doing, time/place negation; 3 paired mistakes; нова практика |
| C | 0 hero→hero; 1 forms-grid→forms-grid; 2 usage-panels→usage-panels; 3 comparison-table→comparison-table; 4 mistakes-grid→mistakes-grid; 5 summary-list→summary-list; 6 primary navigation→same; 7 secondary navigation→same; absent→8 practice-set | Neutral/echo distinction, wh/who, speaker/polarity, питання й відповіді EN/UA; 3 paired mistakes. Другий footer збережено в source, але він не відображався в BEFORE і не відображається в SSR AFTER |
| D | 0 hero→hero; 1 forms-grid→forms-grid; 2 usage-panels→usage-panels; 3 comparison-table→comparison-table; 4 mistakes-grid→mistakes-grid; 5 summary-list→summary-list; 6 navigation-chips→navigation-chips; absent→7 practice-set | Reference instead of marker algorithm; overlap/no forced stop; усі чотири EN table models з перекладами; 3 paired mistakes; нова практика |
| E | 0 hero→hero; 1 forms-grid→forms-grid; 2 usage-panels→usage-panels; 3 comparison-table→comparison-table; 4 mistakes-grid→mistakes-grid; 5 summary-list→summary-list; 6 practice-set→same; 7 navigation-chips→same | Усі первісні формули/моделі/функції збережено; real UA; visible notes; 4 retained tasks + 2 new meaning choices; footer data exact |
| F | 0 hero→hero; 1 forms-grid→forms-grid; 2 usage-panels→usage-panels; 3 comparison-table→comparison-table; 4 mistakes-grid→mistakes-grid; 5 summary-list→summary-list; 6 practice-set→same; 7 navigation-chips→same | Full/short negatives, never/yet/open period/duration; source EN pairs збережено; 4 retained tasks + 2 new meaning choices; footer data exact |

6 subtitles залишено без змін. З 45 continuing teaching/navigation/practice bodies змінено рівно 45; 4 нові practice-set додано лише A–D. Дані другого navigation block C не видалено й не перенесено.

## 4. OLD → AFTER: усі власні controls

36 graded controls = 8 retained/refined + 28 new; 0 demos. На кожному owner 2 selects + 2 meaning choices + 2 full-sentence inputs. Старі E/F 2 selects + 2 inputs залишилися в тих самих group/index, з canonical/aliases і відсутністю legacy IDs/source_index там, де їх раніше не було. Нові tasks мають власні stable IDs; чужі або заднім числом вигадані old IDs не додавалися.

| Owner | OLD → AFTER task positions | Функції |
|---|---|---|
| A | absent→selects[0], selects[1]; absent→choices[0], choices[1]; absent→inputs[0], inputs[1] | named singular `was`; `running`; email без висновку completion/sending; дзвінок без forced stop; Olia/map/time; plural children/place/when |
| B | absent→selects[0], selects[1]; absent→choices[0], choices[1]; absent→inputs[0], inputs[1] | `wasn't` agreement; lexical doing; one-time negation≠never; outside negation≠nowhere; explicit polarity transform; full negative/place/when |
| C | absent→selects[0], selects[1]; absent→choices[0], choices[1]; absent→inputs[0], inputs[1] | plural `Were`; explicit group negative short answer; who-doer not recipient; unanswered question не стверджує факт; What/doing/Nina; who-subject/Marta |
| D | absent→selects[0], selects[1]; absent→choices[0], choices[1]; absent→inputs[0], inputs[1] | fixed conversation reference; explicit ongoing viewpoint; overlap без однакових меж; no forced stop; this time yesterday; while + parallel actions |
| E | selects[0]→same; selects[1]→same; inputs[0]→same; inputs[1]→same; absent→choices[0], choices[1] | retained form choices і Tom/report, three emails; нові since/open state та received≠read/replied |
| F | selects[0]→same; selects[1]→same; inputs[0]→same; inputs[1]→same; absent→choices[0], choices[1] | retained negatives і keys/yet, never flown; нові not-finished≠not-started/guarantee та not-for-long≠no-state |

Повний literal OLD/AFTER payload кожного task, reason і identity policy — у master `editorial_review.task_map`. Усі власні повнореченнєві keys:

| Owner / input | Canonical answer | Додаткові прийняті ручні варіанти |
|---|---|---|
| A0 | Olia was drawing a map at eight o'clock. | — |
| A1 | The two children were waiting near the school when I came out. | — |
| B0 | The mechanic wasn't repairing the bike at noon. | The mechanic was not repairing the bike at noon. |
| B1 | We weren't waiting near the entrance when she called. | We were not waiting near the entrance when she called. |
| C0 | What was Nina doing at eight yesterday? | — |
| C1 | Who was helping Marta at six yesterday? | — |
| D0 | This time yesterday, we were travelling to Lviv. | …traveling to Lviv. |
| D1 | We were cooking while they were setting the table. | …while they were laying the table. |
| E0 | Tom has finished the report. | Tom's finished the report. |
| E1 | We have received three emails. | We've received three emails. |
| F0 | He hasn't found his keys yet. | He hasn't yet found his keys.; He has not found his keys yet.; He has not yet found his keys. |
| F1 | She has never flown alone. | She's never flown alone. |

Видимі українські умови задають ролі, час, лексему, кількість/артикль, порядок обставин і потрібну полярність. Підказки не є прихованим критерієм. Власні банки slash-груп по 1–3 слова перемішані та composable для canonical; ручне введення приймає зазначені aliases. Для F0 grouped bank складає canonical end-yet; не заявляється, що всі альтернативні ручні yet-позиції можна зібрати з цього самого групованого банку. Native token mode перевіряє порядок готових форм, не незалежне вміння написати їх із пам'яті. Усі native x-text feedback є plain text.

## 5. Вбудовані конструктори: банк і межі

Кожен owner має свій точний `Polyglot…AllLevelsLessonSeeder`, type 4, actual SQL NULL pool із 72 питань. 6 × 72 = 432 eligible original questions; відібрано 30 existing, **0 new Questions**, без редагування початкових Questions/Answers/options/hints/variants/links/saved memberships/progress. Редагуються лише display-condition clones, і лише для exact canonical theory caller; course і standalone test не отримують цих condition clones.

Bank audit `docs/content/m88-sentence-builder-bank-audit.v1.json`, SHA `4d0115170624021585d3099a3af1b0a0e2f01ed53ff56738dd0011941bd1eedd`; selected-target digest `21e8cebaf5b6ff7db7443d0557e5a6e1a2134c66a28118cd064c5ce7d4615f9a`. Для всіх 30 прочитано source і actual snapshot: condition, ordered answer instances, multiplicity, options, hints, variants. Public audit містить навчальні дані й непрозорі snapshot hashes, не сирі SQL rows/timestamps/runtime identity.

Фактичний offline replay незміненого native formatter/grader у двох контекстах: 30/30 canonical literal settled, empty rejected і stable recheck у кожному; 11 868 finite adversarial sequences на контекст, 0 wrong-lexical scored-correct. 7 formatting-only normalization matches/контекст записані окремо; це не твердження про strict punctuation/case grading. Область adversarial — removals, pairwise swaps, punctuation moves і доступні option replacements, не всі можливі permutations. Фактичне UI-приймання всіх шести widgets і власної практики наведено окремо в розділі 10; offline probes не підміняють UI.

Збережені невдалі попередні спроби: native v1 виявив 7 непридатних selections (medial When/While case або Yes/No comma). Після прочитання actual source/options погоджено 7 finite existing replacements, не engine fix. Native v2 canonical пройшов, але public privacy gate відхилив SQL-before поля; whole v2 не оголошено PASS. У v3 public export залишив source-authored condition і captured-condition SHA без сирого SQL text; original privacy gate не послаблено. Повні private BEFORE/selection і failed receipts збережено.

Не заявляється повне покриття теми тільки п'ятьма widget keys: C не містить What/subject-who/Yes-No replies; ці функції є в own controls і basic. D widget охоплює interval/period/reference, але не when/while або this time yesterday; вони є в own controls і basic. A і D мають дві однакові англійські моделі у різних topical owners; within-owner дублювання selection немає.

## 6. Guarded local apply та SQL postconditions

Порядок виконано: fresh independent BEFORE → runtime/local-target proof → fresh preview → exact diff review → exclusive backup → transactional apply → postconditions → два фактичні repeated apply no-ops → route cleanup і окремий anonymous GET 404. Робоча БД не пересівалася, startup/HTTP/hooks/scheduler застосування не запускалися.

- Reviewed plan digest: `0d5c005664ac9e6bd6e684bf31506aab711010b30a52ef3cb33cc77ad3a05f5a`.
- Exact diff review SHA: `3fb269ac0089c61bd964fb86d0eb6122842440bff2734724f320fb38b9bb35c2`.
- Exclusive backup SHA: `132a4b236645dfe1cbe02ac9364708ffd053c29a1ed8a5944d4dd8eeeb9dfe28`; backup exact reviewed preview. Backup/preview/proof лишаються приватними й не додаються до Git.
- Apply: 45 body updates + 4 practice inserts = 49 row operations; 0 Page.text updates, 0 subtitle updates, 0 question-family writes, 0 deletes, 0 metadata/level transitions. Обидва повторних apply: 0 updates / 0 inserts.
- E/F practice UUID і slot збережено; A–D мають рівно по одній новій практиці, без дублікатів.
- Cleanup SHA: `2a4114d0c03afe492aa99723a6109af1de16475f3a05b2a10cfe1aeb901c4a7c`; routes file повернуто byte-exact до BEFORE SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`; окремий GET видаленого endpoint дав 404, redirects 0.
- Independent final-readonly-v1 SHA: `68ef0259ff1c8b29cc4a1111ae1e3f7c5c34de5ff94f4a12f2ace3e85db4edb2`: 46 SQL tables, 51 continuing UK rows і 102 EN/PL rows; exact old rows/timestamps після дозволеної reconstruction; 4 new practices; 36 graded / 12 full inputs; 6 subtitles exact.
- Окремий повний bank scope: 15 source banks / 984 original questions / 15 saved pools і 984 saved memberships. Page-linked subset 918 не підміняє full-bank 984: ще 66 full-bank-only VerbToBePast rows теж захищено. Questions/answers/options/pivots/hints/variants/tags/links/order/timestamps не змінено. Course progress/enrolments/attempts захищені all46 reconstruction; це не UI-тест проходження курсу.

Після завершення actual UI main ініціював окремий незалежний SELECT-only final-readonly-v2: SHA `fb947ef7de24ccccca13536f6825baa9280dfeacfcf9313575a458204e9fc14c`, один запуск exit0, 0 DB writes / 0 HTTP. Усі 46 AFTER table fingerprints, 4 new practice rows і source lineage exact порівняно з v1; old rows/timestamps і independent BEFORE reconstruction теж exact. v1 залишається pre-UI доказом і не видається за post-UI. Повторний SQL audit не виконував повторного apply.

## 7. Source-preservation і privacy

Незалежний paired source BEFORE SHA `018a23eaf1063e263d12a037e8f2de686ddfe57a97402cd29ef99a3b9f1e7099`: 8 435 paths. 326 visual paths і 2 957 prior frozen paths перевірені за їхніми фактичними per-context profiles, не шляхом копіювання ROOT на WT. Незмінені native release validator/builder/formatter/grading engines; усі 25 сторонніх pre-existing untracked entries залишаються наявними, untracked і виключеними зі scope: для15 source files fresh capture містить raw hashes, і вони exact; для10 runtime artifacts початкові raw hashes не зняті, тому їхня byte-equality не заявляється. Bank/course source declarations теж прив'язано до fresh M88 capture.

Є лише 12 additive registrations у 8 shared files/контекст, 16 raw/LF reversal profiles, fixture SHA `1be8adcabf1569fef0fe185bce868705b998a53be66dea4d59c3fe9231f3dc81`. ROOT і WT controller мають окремі raw fingerprints, їх не уніфіковано. Reverse only M88 additions відновлює їхні exact independent BEFORE і M87 AFTER bytes. Source sync і final all-path accounting пройшли; public native views не редагувалися.

ROOT `.gitattributes` залишається відсутнім. WT має exact captured old prefix + 49 finite reviewed `-text` rules, без wildcard. Suffix SHA `16364f47c3336daa7b42ffde5143501f00625957cc946eab9d7bcd32d6fcec49`, final WT attrs SHA `280671550ea4ecfd23b407d3ff942d36c048ab2dd749b0bc35c0ebd96629a641`. Не було копіювання чи нормалізації сторонніх файлів.

Freeze privacy review відхилив початковий v1 plan із nested before-binding/SQL-metadata bookkeeping; він не матеріалізувався й не вважається delivery. Approved finite v2 sanitizer прибрав такі metadata лише з public author reviews/master/editorial, зберігши literal lesson/task-map і primary catalogs. v2 plan SHA `dc85af3708030c793e62ef2f9afcd61734736ed61118a3ded5392fd44e316846`, independent review SHA `ffbc840e7754c920de6ab7a71fbb6ce7361d889b3e4cba93b6fa3459ae8e061e`. Public payload не містить `.env`, cookies/tokens, DB credentials, runtime identity, приватних SQL snapshots, proof/backup або learner progress.

## 8. Автоматичні перевірки

Власний accepted v2: 10 нових PHP classes, **67 unique methods**, виконаних двічі = **134 method executions**, не 134 різні методи.

| Профіль | Methods / assertions | F / E / skips | Process / runner exit | Receipt SHA |
|---|---|---|---|---|
| ROOT application + ROOT native factory/test sources | 67 / 9 814 | 0 / 0 / 0 | 0 / 0 | `4181e5f81c33dc35937c7e43405c0f0b93043fc691640a25de7b57139fc0a5e6` |
| ROOT application + WT native factory/absolute WT test sources | 67 / 9 823 | 0 / 0 / 0 | 0 / 0 | `4f802533f06f20431a88ffb76530607e192224da40c2ddf840d1b216db33cfc4` |

WT не має окремого vendor bootstrap: другий рядок не є повним запуском WT application. Різниця 9 assertions походить із test-source branch formatter parity, не з різного набору 67 methods. Обидва профілі: `.env` не завантажено, testing/SQLite `:memory:`, array cache/session, CLI opcache off; 675 protected files, inputs і test bytes unchanged. Власні тести перевіряють finite identities/levels/retained positions/aliases, form/meaning/input criteria, 19 paired visible notes, manual/token state replay, pools/snapshots/caller clones, transactional rollback/stale/partial/collision/no-op, source/privacy boundaries та diagnostic contract.

Початковий v1 WT-native run FAIL залишено. Причина — новий test adapter припускав own property `noInputTokenFocus=false`, тоді як незмінений historical WT factory має її absent/undefined. Погоджено вузьку правку лише двох нових test files: raw presence/value/null тепер перевіряються по SHA-bound native context і derived behavior. Native factory не мутували/не дописували властивість, actual focus calls та інші assertions не вилучалися. v2 rerun описано вище; v1 не перейменовано на PASS.

### Fresh old18 regression: strict FAIL збережено

18 exact lists, незмінні accepted runner/parser, один AFTER run, без retry-to-green. Safe SQLite isolation і всі 675 protected fingerprints unchanged у кожному профілі. Старі test bytes не редагувалися. Concurrent finite HTTP acceptance від інших агентів не приховується; фактичні fingerprint receipts усе одно мають 0 drift.

| Вимір | Fresh BEFORE | Fresh AFTER |
|---|---:|---:|
| Profiles | 18 | 18 |
| Method executions / unique / overlap | 949 / 856 / 93 | 949 / 856 / 93 |
| Assertions | 205 279 | 204 879 |
| Failure executions / unique failing methods | 61 / 53 | 63 / 55 |
| Errors / skips / warnings / deprecations | 1 / 0 / 1 / 18 | 1 / 0 / 1 / 18 |

AFTER index SHA `8c6a9c4251007f59ec2a56882d4743633469251334a9c0c8b784fd8fba756155`; exact literal comparison SHA `c5255cb6047ad982c036d2e01464b01d6a47545b698db50011fd46138675dcd5`; окрема post-observation attribution SHA `54fac1693b5f9ee1afe62da2f9f5ecf87d0a41aa5694c0a6afb2371ee2487e58`.

- **2 нові strict FAIL**, обидва M87SourceBoundaryTest: `test_exact_twelve_nonvisual_registrations_reverse_to_independent_lf_before_on_each_profile` і `test_native_validator_builder_formatter_and_grading_engine_keep_their_independent_raw_bytes`. Actual M88 controller не збігається з жодним історичним M87 AFTER whole-file profile; повідомлення: «Actual foreign-inclusive controller must match exactly one independently reviewed AFTER profile. … actual size 0 … expected size 1». Це не старий baseline FAIL і не PASS/waiver. Новий finite M88 reversal незалежно відновлює historical bytes.
- **4 старі FAIL лишилися FAIL**, literal body змінився рівно одним actual-source SHA: M77OwnedSharedSourceDeltaTest (TheoryContentSource); M73SharedLineageTest (TheoryContentSource); M74OwnedSharedSourceDeltaTest registrations (TheoryContentSource); M74OwnedSharedSourceDeltaTest standalone callbacks (controller). Before/after bodies і SHA збережено. Historical expected constants не змінювалися.
- **2 PASS лишилися PASS**, лише assertions зросли: generic catalogue +42 = 7 assertions × 6 нових targets; M74 registry +6 = 1 equality × 6 нових contracts. M87 assertions −448 через ранню зупинку двох нових byte-guard failures; net assertions −400, не зниклі methods.
- 0 resolved nonpassing, 0 added/missing methods. 941/949 executions і 848/856 unique methods literal unchanged. Нового behavior-test FAIL серед цих восьми method deltas не ідентифіковано; це не сертифікат коректності старих 61 failure executions і 1 error.

Фактичні process exits старих18 profiles: **2×exit0, 15×exit1, 1×exit2**. Wrapper, який зібрав усі outcomes, завершився0; це не підміняє child exits. Generic F0/E0 теж має exit1 через PHPUnit warning `No tests found in class "Tests\Feature\SavedTestStepWrongAnswerTest"` і не вважається passing process. Interaction exit2 має8F/1E. У кожному профілі зафіксовано також PHP8.5 deprecation; warnings/deprecations і повні literal messages збережені.

| AFTER profile | Methods / assertions | F / E | Process exit |
|---|---:|---:|---:|
| m78 | 41 / 22 119 | 4 / 0 | 1 |
| m75 | 33 / 9 522 | 1 / 0 | 1 |
| m76 | 46 / 10 575 | 0 / 0 | 0 |
| m77 | 30 / 22 051 | 1 / 0 | 1 |
| generic | 30 / 15 071 | 0 / 0 | 1 |
| interaction | 134 / 1 588 | 8 / 1 | 2 |
| wide | 136 / 27 269 | 15 / 0 | 1 |
| m79 | 28 / 7 915 | 4 / 0 | 1 |
| m80root | 20 / 5 919 | 4 / 0 | 1 |
| m80wt | 20 / 5 931 | 4 / 0 | 1 |
| m81root | 24 / 7 373 | 4 / 0 | 1 |
| m81wt | 24 / 7 385 | 4 / 0 | 1 |
| m82 | 36 / 7 631 | 6 / 0 | 1 |
| m83 | 46 / 23 790 | 0 / 0 | 0 |
| m84 | 63 / 11 180 | 2 / 0 | 1 |
| m85 | 75 / 8 729 | 2 / 0 | 1 |
| m86 | 80 / 5 328 | 2 / 0 | 1 |
| m87 | 83 / 5 503 | 2 / 0 | 1 |

Окремий фінальний pure `m88-validate-source` main виконав із exit0: 6owners, 36graded (8retained +28new), 12full inputs, 6banks/30selected, 0new Questions, 0DB. Цей source validation не робить старі suites зеленими.

Повного green старого suite немає. Source-registration exemptions, нормалізація повідомлень або historical waivers не застосовувалися.

## 9. SSR, навігація та old consumers

SSR v2 SHA `7078dabe9803db184d7bb53858fa618d228f33d42bd70ac1a99df39ce99b66bb`: 17 unique local GET, 689 learning fields, усі 36 graded tasks / 12 full inputs / 30 linked questions / 19 mistake notes. 12 selects + 12 meaning choices + 12 full-sentence inputs; 0 demos/rephrase. SSR не замінює реальний Check/Reset/token/layout UI-тест.

Перший SSR attempt залишився FAIL: усі шість M88 pages уже перевірено, але count 15/17 пропустив два unchanged footer controls. Діагностичну прогалину виправлено в новому M88-only helper (SHA prefix `48de9`); повторний v2 фактично виконав 17, включно з відсутніми controls. Перший receipt не стерто й не оголошено PASS.

Основний footer: labels/order/current збережені. Для A–D погоджено 16 exact href-only виправлень (4×4) зі short paths на повну `/theory/tenses/past-continuous/...` ancestry. Для E/F footer data literal exact. У C source index7 related-category footer був відсутній в незалежному BEFORE SSR і лишається відсутнім AFTER. Дані збережено, view не змінено, **secondary navigation visible acceptance=false**, не PASS. Proof SHA `3b07a36eacabd414f68f2bd3bccc9a3d3ad922ad9adf6ee85c389076ae2d682e`.

SSR read-only reference controls: [PPC forms](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms), [Present Continuous questions](http://gramlyze.loc/theory/tenses/present-continuous/present-continuous-questions), [Present Continuous time expressions](http://gramlyze.loc/theory/tenses/present-continuous/present-continuous-time-expressions), [Past Simple forms](http://gramlyze.loc/theory/tenses/past-simple/past-simple-forms), [negatives](http://gramlyze.loc/theory/tenses/past-simple/past-simple-negatives), [questions](http://gramlyze.loc/theory/tenses/past-simple/past-simple-questions), [time expressions](http://gramlyze.loc/theory/tenses/past-simple/past-simple-time-expressions) — HTTP 200. Це SSR/source-controls, не їхня повторна UI-приймальна перевірка.

Old-consumer AFTER SHA `03826432fa394fbe7bd84384cad603c3c9ae289c7c69c4bde04075348322ef32`: **57 endpoints × один GET**, unchanged full-entry M52 comparison без normalization, filters, pinned variants або rerolls. **22 entries exact; 35 strict FAIL**: у кожному змінився тільки top-level `body_sha256`. Status/kind/derived-field equality не дозволяє видати whole-entry FAIL за PASS. Причина змін body SHA не доведена цим collection і тут не припускається. Всі46SQLtables unchanged під час collection; повні15banks/984questions/15saved pools порівняно без втрати66bank-only rows.

Одна відома BEFORE exception лишається not accepted: [requested Past Continuous questions SSR](http://gramlyze.loc/test/past-continuous/questions) потрапляє в existing JSON route collision і повертає404; requested kind `ssr`, actual kind `json`. Exact збереження exception не є успішним SSR. Routes/controllers не виправлялися в M88.

Повний native registry AFTER: **223 targets, 0 failures, 0 writes, exit 0, stderr 0**; stdout збережено raw, без trim/re-encoding/retry. Raw SHA `4dc124c88d7b0e657c5825e3bf6cc73b8d7c192d10d1fa4718a811d04d8d7e4c`. Це fresh 217 BEFORE + exact 6 M88. Незалежне semantic comparison SHA `f7d950c09c3dffdc6b591a2eefb95667711793d724ba45f9d9b1b5ecea99960b`: усі 217 старих records, field/key order і owner order exact, виключено лише volatile whole-response `http_sha256`; differences 0. Для шести M88 rows підтверджено exact selected IDs/order/UUID/type 4 і eligible pools 72 кожен. Це native registry/semantic acceptance, **не whole raw HTML equality і не browser interaction PASS**. Strict old-consumer body-SHA FAIL із попереднього абзацу залишається FAIL. Немає твердження, що SSR17GET сам по собі доводить повну old217 regression.

## 10. Реальне UI-приймання та delivery

Final browser receipt v2 SHA `a2ed565df1614898551f027519aa2e0031e6a9c42258fdb222f63217fe94eb64`: **completed with explicit limits**. v1 залишено immutable; v2 виправляє лише stale progress_note, усі actual records/flags/summary literal незмінні. Зображення й raw browser captures залишаються приватними, до public source/report не переносяться.

| Фактичний UI-підетап | Результат і точна межа |
|---|---|
| Початкові layout states | 36 = 6 owners × widths 1440/390/320 × light/dark. У кожному initial state learning_overflow=[] і visible technical markers=false; це не глобальна overflow-zero гарантія |
| Full inputs | 48 nominal cases = 12 tasks × manual/tokens × widths1440/320, усі native scores2/2. Це 12 різних завдань, не48 нових питань |
| Mixed editing / delete-return | 24 supporting cases = 12 tasks × widths1440/320: видалені token parts стають доступними, rebuild після mixed/manual editing дає2/2. Не додаються до nominal48 як нові завдання |
| Options і feedback | 65 actual select/meaning option probes; visible feedback звірено з frozen source. Груповий score не видається за окремий per-option0/1 |
| Manual aliases | 46 literal positive alias probes, accepted visible. Це finite accepted-list перевірка, не довільні синоніми або всі можливі англійські відповіді |
| Select/choice Reset | 12 owner/group cases: correct2 → Reset empty0 → correct recheck2 |
| Input states | Фактично перевірено empty/whitespace/wrong, редагування, recheck і Reset у двох input-widths; supporting cases не видаються за окремі graded tasks |
| Linked widgets | Усі6 фактичних widgets на320px завершені5/5; прочитано30existing IDs/settled sentences, усі literal frozen-audit keys збігаються |
| Повернення налаштувань | Початковий dark відновлено, default viewport1280×720 та viewport override reset; zoom не змінювали |

Початковий immutable acceptance aid планував nominal inputs на1440/390, а фактичні48cases виконано на1440/320. Aid не переписувався: 320 є actual mobile width, не стверджується exact збіг усіх planned viewport tuples. Вимогу desktop/mobile48 охоплено, а390 окремо входить до всіх36layout states. Це відхилення процесу вимірювання, не нова зміна навчальних даних або grading behavior.

Межі layout evidence: на вузькому екрані raw shell/document width може бути більшим за client width. Окреме E320 observation містить порожні absolutely-positioned SPANs у pointer-events-none parent з right327 при viewport320; це спостереження, не доведення причини всіх overflow. Native tables мають власний local horizontal scroll. Довгі заповнені single-line inputs прокручуються всередині поля: в A320 client200, scroll288/447, input лишається в межах card. Ці filled-input виміри окремі від 36 initial learning_overflow=[]; твердження «всюди нуль горизонтального scroll» не робиться. Дизайн/engine для усунення цих спостережень не змінювалися.

Збережені UI harness findings: перший B compositor capture відхилено як stale, наступний stable capture збережено поряд з оригіналом. Перше immediate post-Check читання B option feedback не збіглося, наступний fresh DOM збігся з exact frozen feedback; timing є припущенням, не доведеною причиною, далі застосовано visible-feedback waits. Початкова F desktop helper assertion вимагала дублювати canonical EN у feedback, чого frozen F0 feedback не обіцяє: він українською. Цю overstrict assertion збережено; exact canonical перевірено в зеленому input.value при2/2 і окремо exact Ukrainian source feedback. Ні дані, ні native formatter/grader не змінювалися заради такого дублювання. UI evidence не переводить старі regression або consumer strict FAIL у PASS.

Print, full no-JS usability і WCAG audit не перевірялися. Keyboard «Докладніше» не застосовується: package має 0 details. Немає твердження про production-перевірку.

Git delivery plan: після review цього звіту — explicit allowlist58 related paths, staged-diff review, commit у робочу гілку та звичайний push без force. На момент формування тексту це ще не completed delivery. Full commit SHA і незалежне порівняння remote ref із локальним commit наводяться у final handoff/private Git receipt, а не self-reference цього самого commit. Виключити `.env`, runtime caches, private diagnostics, backups/dumps/proofs і всі25 сторонніх entries; з них лише15 мають перевірений raw BEFORE,10runtime artifacts — present/untracked/excluded без вигаданого raw baseline. Deploy не дозволений цим звітом. Main branch і сервери не змінювалися цим підетапом.

Перевірка staged EOL-профілю: рівно58 дозволених paths, сторонніх файлів в індексі немає. Звичайний `git diff --cached --check` має native Git exit2 (shell wrapper повідомив1) лише для чотирьох доданих controller lines із CRLF у збереженому raw-профілі. Команда `git -c core.whitespace=cr-at-eol diff --cached --check` завершилася exit0; trailing CR трактовано як EOL, інші whitespace checks не відключено. Working-tree `.gitattributes` лишився byte-exact SHA `280671550ea4ecfd23b407d3ff942d36c048ab2dd749b0bc35c0ebd96629a641`. Actual cached audit:55 blobs raw-equal; для `.gitattributes`, `TextBlockToQuestionsMatcherService.php` і `TheoryCourseManifestService.php` Git має LF-normalized index representation. Обидва service-файли збережено з їхніми captured CRLF disk bytes; їхні index LF fingerprints звірено з finite fixture.49 M88 paths мають finite `-text`. Жодних global Git settings або EOL-нормалізації старих working-tree source files не виконано.

## Додаток A. Усі30 selected widget keys — фактичний settled EN

Це actual literal native settled keys з frozen public audit, не нові Questions. Український criterion і переклад кожного є в audit; function limits наведені в розділі5.

| Owner | Existing question ID | Settled answer |
|---|---:|---|
| A | 21387 | My brother was doing homework at nine yesterday. |
| A | 21388 | The children were playing in the yard at lunchtime yesterday. |
| A | 21391 | Anna was writing a message at lunchtime yesterday. |
| A | 21394 | The teacher was making plans all afternoon. |
| A | 21397 | My friends were driving to school from two to three. |
| B | 21459 | My brother wasn't doing homework at nine yesterday. |
| B | 21460 | The children weren't playing in the yard at lunchtime yesterday. |
| B | 21461 | The teacher was not explaining the rule at that moment. |
| B | 21462 | My friends were not driving to school at that moment. |
| B | 21466 | The children weren't playing in the yard because they finished earlier. |
| C | 21531 | Was my brother doing homework at nine yesterday? |
| C | 21532 | Were the children playing in the yard at lunchtime yesterday? |
| C | 21534 | Were my friends driving to school at that moment? |
| C | 21537 | Where was the teacher going at that moment? |
| C | 21538 | Where were the children going from two to three? |
| D | 21610 | The children were playing in the yard all morning. |
| D | 21609 | The teacher was explaining the rule all afternoon. |
| D | 21611 | My friends were driving to school from two to three. |
| D | 21606 | At that moment the children were playing in the yard. |
| D | 21603 | My brother was doing homework at nine yesterday. |
| E | 21966 | My brother has worked from home for six months. |
| E | 21967 | We have never seen snow before. |
| E | 21968 | Emma still cannot find her glasses because she has lost them. |
| E | 21970 | The children have just found the missing puzzle piece. |
| E | 21971 | I have watched four documentaries this month and the month is not over. |
| F | 22035 | You have not completed the online form yet. |
| F | 22038 | My parents have never travelled abroad before. |
| F | 22041 | We haven't booked a table for tonight yet. |
| F | 22042 | Ella hasn't watched the new episode yet. |
| F | 22043 | We have not taken this route before. |
