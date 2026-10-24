# M85 — шість уроків Verb to Be у стандартній в’юсі

Шість уроків локально застосовано та прийнято. Відомі strict-regression/consumer обмеження не приховані й наведені окремо нижче. Лише `http://gramlyze.loc`. Main, production, `.ub`, deploy, PR, merge та force push не використовувалися.

## 1. Точні сторінки й межі

Прийнята база M84: `6f7751612a3bc9d8bec2315c052e81f9e2fc0973`. Робоча гілка: `codex/seo-m85-verb-to-be-standard-view`. Served ROOT і delivery worktree перевірялися окремо; сторонній dirty ROOT не переключався, не відновлювався і не комітився.

| Owner / локальна сторінка | Page ID | Display / SQL level / own focus | Continuing source blocks |
| --- | --- | --- | --- |
| [Verb to Be: Present Forms](http://gramlyze.loc/theory/basic-grammar/verb-to-be/verb-to-be-present) | 28 | A1 / NULL / A1 | 7 |
| [Verb to Be: Negatives](http://gramlyze.loc/theory/basic-grammar/verb-to-be/verb-to-be-negatives) | 26 | A1 / NULL / A1 | 7 |
| [Verb to Be: Questions and Short Answers](http://gramlyze.loc/theory/basic-grammar/verb-to-be/verb-to-be-questions) | 29 | A1 / NULL / A1 | 8 |
| [Verb to Be: Past Forms](http://gramlyze.loc/theory/basic-grammar/verb-to-be/verb-to-be-past) | 27 | A1–A2 / NULL / A2 | 8 |
| [Verb to Be: Future](http://gramlyze.loc/theory/basic-grammar/verb-to-be/verb-to-be-future) | 25 | A1–A2 / NULL / A2 | 8 |
| [There Is / There Are](http://gramlyze.loc/theory/basic-grammar/verb-to-be/there-is-there-are) | 24 | A1–A2 / NULL / A1 | 7 |

Усі owners — UK, category ID8, ancestry `basic-grammar/verb-to-be`. Actual raw titles, English H1, canonical/breadcrumb, anchors, levels, continuing UUID/sort/column, tags і relations захищені незалежними BEFORE. Шість Page.text залишаються NULL; metadata descriptions обчислено до apply, а не з AFTER.

## 2. Навчальний результат

| Урок | Конкретні зміни | Межа покриття |
| --- | --- | --- |
| Present | Am/is/are за займенником і названим noun subject; людина/люди/пара імен; роль, стан, характеристика й місце. Complement після be не названо direct object. Для football correction явно задано звичку, а не приховано замінено поточну дію. | Не повний огляд auxiliary be, усіх часів або всіх complements. |
| Negatives | Повні й короткі форми, I'm not, обидва доречні negative contractions; not після be у звичайному present declarative. Not happy не прирівняно до sad, not at school не доводить at home. | Повний запис не визначає інтонацію; правило без do/does не забороняє Don’t be в іншій моделі. |
| Questions | Інверсія в нейтральному прямому питанні, явний діалог you→I/we, узгоджена short answer, subject who без зайвого займенника. Питання не підтверджує готового факту. | Розмовне declarative checking question не оголошено неможливим; окрема positive short answer не скорочується в кінці. |
| Past | Was/were, noun agreement, явні учасники й минулий час; questions без did у цільовій be-моделі; short answers із правильною особою. | Минулий стан не встановлює протилежного стану зараз; If I were не «виправляється» правилом звичайної минулої розповіді. |
| Future | Will be / will not be / won’t be, питання з will і названим subject; Who will be без доданого he. Прогноз відділено від гарантованого факту. | Will be — предмет цього уроку, не єдина майбутня форма; present із future reference не заборонено глобально. |
| There | Наявність проти місця вже відомого предмета й ідентифікації через it; singular/plural та uncountable milk; стандартна письмова short answer. | Not two не доводить нуль; any не оголошено обов’язковим у кожному питанні; розмовне there's із множиною має register boundary. |

Таблиці зберігають обидві англійські моделі, справжні українські переклади та окремі пояснення; англійські підписи на кшталт One thing не видаються за переклад. У Q/A перекладено обидві репліки. Нові приклади й оцінювані завдання авторські; агентний review не є human review.

Мовні джерела, actual access method і перевірене положення — у versioned master та author parts. Використано редакційні матеріали British Council LearnEnglish і Cambridge Grammar Today; прямий body, indexed excerpt, editorial reply і blocked response розрізняються. Зокрема [Present simple: be](https://learnenglish.britishcouncil.org/free-resources/grammar/a1-a2/present-simple-be) та [Questions and negatives](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/questions-negatives). Publisher exercises не копіювалися.

У всіх шести семантично обрано **0 point-level details**: короткі правила, переклади, потрібні межі моделі й пояснення помилок лишаються visible basic. Це не runtime word-count threshold і не квота кнопок.

## 3. Єдиний дизайн та caller-owned межа

Збережено штатний шлях `theory.show → content-block → TheoryContentSource → TheoryContentRenderer → theory.content → theory.components.*`. **0 нових visual-source змін**: served ROOT зберіг усі326 visual paths і2884 старих frozen paths exact; WT перевірено за його власним незалежним BEFORE — 326 visual та1987 frozen paths, включно з відсутніми paths. View/CSS/JS, formatter, grader, engine, header/sidebar/background не редагувалися.

Дванадцять additive registration/projection/preservation lines у восьми shared PHP файлах використовують чинні primitives. Їхня reversal byte-for-byte відновлює незалежні ROOT/WT BEFORE, включно з різними controller raw/EOL профілями. Немає package views/styles, автоматичних apply hooks чи SQL у presenter.

ROOT і WT не мають удаваного спільного raw-byte baseline. Із2884 ROOT-frozen paths901 не були охоплені окремим WT BEFORE (13 із них у WT відсутні); для них історичну raw-незмінність WT не заявлено. Одна finite dependency exception для вже різного `AnswerOptionCase.php` зв’язує ROOT з initial reviewed preview, а WT — з accepted M84 Git blob і поточним raw/EOL профілем. Сторонній файл не копіювали й не нормалізували. Початкові нові source-sync відмови через неправильне припущення однакових context bytes не названі PASS; помилку виправлено тільки у власному новому діагностичному профілі, не в старих guards.

Відповідність OLD→AFTER, component/task/coverage maps і точні bindings — у [M85 artifacts](../content/m85-native-bindings.v1.json). Шість sources уже native, тому box→native migration не проводилася. Ліві mistakes/summary не переносилися в іншу колонку.

## 4. Власна практика й шість окремих widgets

На кожній сторінці додано рівно 2 selects на форму, 2 meaning choices і 2 inputs на повне речення: **36 нових graded controls, 12 full inputs, 0 demos**. До M85 власного practice-set у цих шести actual owners не було. Кожний input має видиму українську умову, manual+token спосіб, перемішані групи по 1–3 слова, accepted keys/aliases і plain feedback. Готову відповідь у placeholder/hint не додано.

Native normalization, aliases, reset/retry й scores не змінені. Пунктуація, capitalization або форма апострофа не видаються за окрему сувору перевірку правопису. Для semantic letter controls приймається тільки reviewed candidate identifier, не contraction alias його англійського тексту.

Нижче кожної власної практики — один незалежний тематичний type4 widget із п’ятьма питаннями. Використано **30 existing questions, 0 inserts/edits старих question families**. Six all-levels banks не підміняються course banks або одним загальним be-bank.

| Урок | Actual eligible pool (SQL filter NULL) | Selected IDs | Рівень добору |
| --- | --- | --- | --- |
| Present | 71 | 6710, 6713, 6716, 6718, 6720 | A1 |
| Negatives | 72 | 6567, 6568, 6571, 6575, 6576 | A1 |
| Questions | 72 | 6781, 6783, 6787, 6789, 6792 | A1 |
| Past | 72 | 6650, 6652, 6656, 6658, 6659 | A2 |
| Future | 72 | 6508, 6510, 6512, 6516, 6518 | A2 |
| There | 70 | 6424, 6426, 6427, 6430, 6434 | A1 |

Source банки мають по72 records, але actual assigned/page-linked eligibility — 429 загалом. Missing links не додавали заради симетрії. There selection обмежена affirmative countable models; own practice окремо покриває uncountable, питання й значення. Це не вичерпне тематичне/CEFR покриття.

Clone-only presentation уточнює українські conditions тільки на exact-bound canonical theory clones. Застарілий positive SQL condition у вибраних Past negative models не переписує original question. Повний source snapshot/fallback і multiplicity збережені; двічі she у Questions не губиться. Offline replay: 30 canonical та 1894 adversarial sequences на профіль; 0 лексично неправильних послідовностей оцінено правильними. Три case/punctuation-only normalization matches рахуються окремо.

## 5. Локальні записи та versioned mapping repair

Перший guarded apply: **49 body updates** (45 continuing blocks + 4 subtitles A–D) і **6 practice inserts**. Page.text, question families, schema, metadata columns і старі timestamps не змінювалися. Exclusive backup дорівнює reviewed preview byte-for-byte; повтори original preview та fresh AFTER preview дали0 змін. Незалежний SELECT-only scan перевірив усі46 таблиць, 51 continuing UK row, 102 EN/PL rows та реконструкцію initial BEFORE.

Під час фактичного приймання знайдено дефект нової проєкції: 6 authored E/F mistake hints існували в джерелі, але native correction показує поле note. Actual два GET200 підтвердили6 source hints /0 own SSR hints при збережених24 paired EN/UA fields. Це не CSS/шрифт і не pre-existing omission.

Застосовано finite projection1.0.1: для E slot5 та F slot4 items0–2 додано `note` з точним наявним `hint`, залишивши hint для legacy fallback. Жодного нового пояснення, переписування тексту, зміни практики або engine. Старі17 v1 артефактів збережені exact у ROOT/WT. Teaching/native version лишається1.0.0; це виправлення відповідності даних підтримуваному полю, не друга авторська редакція уроків. Активні [patch](../../database/content-patches/m85-verb-to-be.v1.0.1.json), [master](../content/m85-verb-to-be.v1.0.1.json) і [точний mapping diff](../content/m85-mistake-notes.v1.0.1.json) мають окремі pins.

Follow-up після нового proof, свіжого BEFORE та exact review виконав **2 body-only updates /0 inserts** в одній транзакції. Exclusive backup exact reviewed preview; повтор original preview і застосування fresh AFTER preview дали0/0, невикористані backup-файли не створилися. Загалом **51 update operation +6 insert operations =57 операцій**, але **49 distinct updated rows +6 inserted rows =55 різних змінених рядків**: два mistake rows оновлені двічі. Question writes/deletes/schema/Page.text/progress writes —0.

Незалежний two-overlay SELECT-only audit: усі46 таблиць, повні timestamps і sources exact; зворотна заміна двох note bodies відтворює initial AFTER, а зворотний initial write-set відтворює незалежний M85 BEFORE. Initial final receipt SHA `09384892849ec9cbe400b1dff952094cbeb0e73a27931b7cc4e395acdb95a199`, follow-up final SHA `0b0c35aca6601cc600ca4a5a71ca36f9304944cb0638bc949d8bec93ef1e9187`. **Post-all-HTTP повтор PASS**:46 fresh tables,2 note cells normalized,49 initial updates reversed,6 new practices omitted, DB writes0/HTTP0; prior final SQL/source state exact. Остаточний receipt SHA `4476111afa8de42a1e3d3dca5ee577110b0f07e1e2eb8ad566309a8a9f6f2ed3`.

Обидва тимчасові proof routes видалено; routes/api.php кожного разу відновлено exact до foreign-inclusive BEFORE, окремі GET404 підтверджено. DB password, APP_KEY, cookies/tokens не публікувалися. Apache/XAMPP/.env/hosts/ACL/порти не змінювалися.

## 6. Навігація й відомі обмеження старої в’юхи

У36 source footer entries збережено labels/order/current flags; короткі destination URLs фактично повертали404. Дані виправлено на підтверджені ancestry URLs; 30 noncurrent links відповідають шести canonical destinations. Router/resolver/redirect не змінено, href="#" не додано.

C другий navigation block «Пов’язана тема» збережено exact у source/SQL. Незмінна універсальна в’юха вже до M85 відображала тільки перший navigation block: другі title/label/href відсутні і в незалежному BEFORE, і в AFTER HTML. **Visible acceptance цього другого footer=false**, не PASS. Його не видаляли й не ремонтували в’юху поза scope.

## 7. Фактичне браузерне приймання

Завершено **36/36 унікальних target states**, **48/48 унікальних input task×mode×size cases**, **6/6 widgets5/5**, **30/30 прочитаних settled sentences**. Для кожного owner —6 станів,8 input cases і5 distinct existing question IDs. Actual desktop/mobile manual+tokens,65 option cases і63 interaction probes перевірені: correct/distractors/declared aliases, empty/whitespace, wrong→edit correct, повторний Check, reset, mixed editing, delete/return/rebuild, відсутність зайвих listboxes і незалежні scores2/2. Семантичні probes охоплюють noun agreement, lexical verb/be, not happy, you→I/we, past без теперішнього висновку, who, втрату not/be, uncountable/there/it та not two.

Кожний widget реально пройдено до кінця: Check/feedback/settled sentence, Next і completion. Видимі відповіді містять потрібні not/there/will/be; у Questions збережено обидва she. Приватний фінальний UI receipt SHA `f2c3fcb14c41344001ee2486cc4581a3cf4d250aaa5722dc65c2cf9e7d35eddb`;36 screenshot hashes повторно звірено. Очікування взято з незалежної author projection до source materialization, не з самого AFTER. Особистий progress/global localStorage не очищали; після приймання viewport і початкову темну тему відновлено.

Незалежний actual SSR audit виконав14 GET200: шість targets, PPC, шість M84 controls і related category. Перевірено835 fields,36 controls,12 inputs,30 widget questions,18 видимих mistake notes (6 repaired aliases),72 ordered EN/UA fields,36 footer source entries і30 noncurrent visible hrefs. SSR receipt SHA `0aa81afb0b8f5bde68e523fe95e9d98cd60c38dd6a44190fe20c5458e6b1e8c2`; SQL access=false. SSR не видається за browser interaction.

Матриця: 1440×1000, 390×844, 320×844 × light/dark для кожного owner. Actual viewport/theme читаються з DOM; кожна сторінка переглядається до footer, screenshots приватні. Шрифти/нативні стилі підтверджуються computed styles та знімками; `.sheet` у read-only browser DOM не є доступним network-load evidence. CSS не змінювався заради скриншота.

На всіх36 states немає non-table learning-text/input/button overflow; широкі таблиці мають локальний scroll. Decorative shell має невеликий horizontal overflow, який записано окремо й не видано за відсутність global overflow. Стан layout не виправлявся CSS. Point-level details — N/A; звичайні native disclosures не рахуються authored details. Print/no-JS/WCAG audit — not_checked.

## 8. Автоматичні перевірки

Final own active-projection profile: **48 methods /8658 assertions /F0/E0/skipped0**, warnings0, notices0, exit0. Є одна унікальна PHP8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA` у config/database.php:62, яку спричинили26 methods. Усі675 protected runner fingerprints exact. SQLite :memory:, array cache/session; робоча .env/MySQL/progress у тестах не використовувалися. ROOT PASS не видається за full WT run без vendor.

Окремі final профілі: finite note transaction8 methods/157 assertions; pure source-profile3/31; live diagnostic contract16/117 — F0/E0, exit0. Разом disjoint ROOT union **75 unique methods /8963 assertions**, не один спільний suite і не сума повторних запусків. WT окремо виконав ті самі16 pure Unit methods через ROOT vendor/autoload; це не повний WT application/runtime run. Note profile також має стару PDO deprecation; pure source/live profiles — без неї. Offline JS nominal size/mode replay не рахується actual browser acceptance.

Fresh old15 lists: **711 method executions /618 unique /93 overlaps**. BEFORE185346 assertions/F55/E1 → final AFTER185175/F57/E1. Сімейства old tests/list/parser/runner не змінювалися. Два нових failing methods — M84 source-byte context pins, а не baseline failures; два registry methods залишилися PASS із додатковими48 assertions; чотири старі failure bodies мають лише actual-SHA substitutions. M83 усі46 methods/23790 assertions exact PASS. M84 final AFTER63/11180/F2/E0/exit1;219 assertions не досягнуті. Нових behavioral/UI failure methods або errors у цьому порівнянні не виявлено, але all-green/exact-output equality не заявляється.

Final projection old15 повтор завершено. Initial AFTER→final AFTER: усі711 executions, assertions, outcomes, messages/full failure bodies та exit codes literal exact; receipt SHA `a680b213aed646061913dca319123eacabc0591f4ef0cecaa54fb277b087265d`. Порівняння final із незалежним BEFORE SHA `eb0893d035a3c0be17c0237d6e2db777ca5705d2b1d2cdceb0407323263dfd4f`. Старі assertions/fixtures не послаблювали; private counterfactual не використовується як actual AFTER PASS. Початкова помилка власного fixture49-vs45 і нові діагностичні невдалі preflight-спроби збережені як невдалі, не зараховані до final PASS.

## 9. Старі consumers та збереження

Свіжі BEFORE/final AFTER:20 banks,17 saved tests,1675 unique question IDs,61 endpoint GET за один bounded collection без reroll.60HTTP200 і один pre-existing requested SSR404; усі46 таблиць exact до/після, learning/course source hashes та saved memberships збережені.

Final strict comparison: **23 exact /38 FAIL retained**. Із38 розбіжностей36 лише raw-body SHA; причину самих hash-only змін не вигадано.37/37 comparable static-fact records і6/6 course learning DOM exact. Keyed payload52/54 exact;4875 question occurrences. Дві random-text occurrences на SSR+JSON належать одному unchanged question `vtbf-cp-sn-a2-q18`/ID5823: BEFORE обрав старий variant4581 `I {a1} will be happy about it, I promise.`, final AFTER — unchanged canonical `I {a1} happy about it, I promise.` Старий malformed variant не названо граматичним/нешкідливим і не редагувався в M85. Усі1675 old questions,1078 variants, families/links exact; derived drift0, unclassified drift0. Final classification SHA `0cbc9af93eb5678827422157fd049c6a2dad362c86a195ebf09b144c1269f272`, actual final collection SHA `0c64c84ad8a48d699805bc35fd78041deafd95ee64b2057250413dd1e80c485a`.

Точна причина старого variant defect підтверджена з BEFORE: answer marker a1→`will be`; variant4581 утворює `I will be will be happy about it, I promise.`, canonical — коректне `I will be happy about it, I promise.` Це не нова правка питання й не дефект canonical AFTER.

Initial AFTER collection також збережено:23 strict exact/38 FAIL,37 raw-body-only та53/54 keyed exact. Його не замінили final збігом і не видають повторний bounded acceptance після versioned repair за retry-to-green.

Сім BEFORE exceptions лишаються not accepted: три There70/72, три Present71/72 та Questions requested SSR404. Classification не стирає strict FAIL. Не виконували GET повторно до випадкового збігу, не очищали cookies/global localStorage, не змінювали старі variants/comparator.

Final finite repair consumer boundary завершено: source/SQL/membership exact; strict HTTP FAIL лишаються FAIL. Усі курси/final drills, category descriptions, EN/PL, standalone theory-help, saved tests, progress і нецільові banks захищені caller-owned fallback та all46 reconstruction; недоступний consumer не видається за перевірений.

## 10. Реєстр і доставка

Незалежний registry BEFORE:199 previous owners,0 failed. Обидві штатні read-only команди виконано по одному разу: scoped **6/6 PASS**, aggregate **205/205 PASS**, failed0, stderr0, exit0, DB writes0. Усі199 попередніх рядків exact за всіма полями, крім мінливого `http_sha256`; scoped/global шість нових рядків exact, union точно199+6=205, issues0.205 — фактичний union, не нова постійна межа в коді. Semantic receipt SHA `7847392d69d566e308543ccb2a84e49ac89e19f8472cbb2b8134fa72e6c1ffa7`. Це matcher+fresh HTTP, не browser visibility/interaction і не whole HTML equality; C second footer цим gate не приймається.

Команди: `php artisan theory:audit-sentence-builders --target=gramlyze.loc --source=m85-verb-to-be.v1.0.1.json` та `php artisan theory:audit-sentence-builders --target=gramlyze.loc`.

Delivery scope —84 explicit paths:15 tracked changes та69 нових M85 paths; сторонні15 untracked non-storage і10 private storage paths виключені й збережені. Raw `git diff --check` показує лише4 нові CRLF lines у controller, який уже має `-text` і збережений accepted raw/EOL profile. Із `-c core.whitespace=trailing-space,space-before-tab,cr-at-eol` перевірка проходить; нормалізації shared controller не робили. Для нових M85 text paths встановлено LF до hashes; старий prefix `.gitattributes` збережено.

Actual staged index перевірено окремо:84 exact allowlisted paths,69A/15M,0 unmerged,0 сторонніх tracked/unstaged changes.81 blobs exact working bytes;3 paths (`.gitattributes` і дві shared services) мають лише штатне CRLF→LF index-перетворення, що не є source rewrite. Контролер зберігає raw CRLF. Staged EOL-profiled `diff --check` — exit0. Первинна staged перевірка додатково виявила2 кінцеві пробіли у власному non-runtime генераторі `m85-bank-audit-projection.php`; його відсутність у всіх frozen/runtime93 paths підтверджено незалежно. Видалено тільки ці2 байти у ROOT/WT, PHP lint пройшов, in-string substitutions і всі frozen artifacts/runtime/DB лишилися незмінними. Вхідні source pins не перераховували заради whitespace total.

На момент freeze цього звіту scoped stage/index review завершено; наступна доставка — commit → normal push у вказану M85 гілку. Фінальні commit SHA та незалежна remote-SHA перевірка повідомляються в підсумку доставки, поза самопосиланням цього commit. Приватні receipts, screenshots, proofs, backups/dumps, .env, caches, тимчасові routes та сторонні dirty changes не входять до allowlist.
