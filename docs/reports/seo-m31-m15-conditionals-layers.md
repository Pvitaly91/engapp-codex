# M31 — M15 Conditionals: native basic та meaningful point details

Дата початку: 2026-10-03. Ціль — тільки `http://gramlyze.loc`.
Завершено 2026-10-04: локальний transactional apply і M31 browser acceptance виконано. Старе no-JS обмеження токен-практики M26–M29 зафіксовано окремо; це не нова регресія M31.
Production `.com`/`.ub`, main, PR та деплой поза scope.

## База та межі

Accepted база: `ceb2162c862978ca461607be3913a9f717f380b2`.
Після `git fetch origin` HEAD і `origin/codex/seo-m30-m14-participle-clauses-layers` точно дорівнювали цій базі; ancestry exit 0, tracked worktree clean.
Створено `codex/seo-m31-m15-conditionals-layers` у придатному чистому worktree `D:/DEV/htdocs/gramlyze.loc/storage/app/m30`.
Основний dirty checkout лишився на `41820a2bebdf69004fa7209a2a38457f93efabbd`; reset/clean/stash/rebase/merge/force не виконувалися.
Прочитано AGENTS.md, point-detail-quality contract та звіти M28 quality, M30, M15.

Це лише технічна проєкція accepted M15, не нова редакція навчальних текстів. Не створено нових правил, прикладів або перекладів. Дистрактори й групування токенів — адаптери тих самих шести author self-check cases.

| Рівень / сторінка | Seeder у `Database\Seeders\Page_V3\Conditionals\` | Accepted Git blob | Основний mixed-тест |
| --- | --- | --- | --- |
| [B2 — Conditionals with Unless, Provided That, and As Long As](http://gramlyze.loc/theory/conditionals/conditionals-with-unless-provided-as-long-as) | `ConditionalsWithUnlessProvidedAsLongAsTheorySeeder` | `84b42d6b57c27b1c4d47719395758f19f57b9d63` | [Тест B2](http://gramlyze.loc/test/conditionals/with-unless-provided-as-long-as) |
| [C1 — Advanced Conditionals](http://gramlyze.loc/theory/conditionals/advanced-conditionals) | `AdvancedConditionalsTheorySeeder` | `cc056a2abdb231c7883e905703502d44e6885570` | [Тест C1](http://gramlyze.loc/test/conditionals/advanced-conditionals) |
| [C2 — Conditional Alternatives And Nuance](http://gramlyze.loc/theory/conditionals/conditional-alternatives-and-nuance) | `ConditionalAlternativesAndNuanceTheorySeeder` | `c685fcea76e89659d9dd8b639f5ce9d3e5a905f6` | [Тест C2](http://gramlyze.loc/test/conditionals/conditional-alternatives-and-nuance) |

Locale `uk`, type `theory`, ancestry рівно `conditionals`. Frozen before — author Git definitions, не приватний DB backup.
Before SHA-256: `4b9d9486b89ec88fc7295ba5b5112579b1797ddfe432c958432981c28fb3e206`.
Projection v1 SHA-256: `a71a3c410e472f6d82d7652b0c307e9b5455385413af66e1b526458bb5768a12`.
Обидва hash-bound JSON мають `.gitattributes -text`.

## Native structure та fidelity

Було: subtitle + hero + один великий author box зі static self-check.
Стало: незмінені subtitle/hero + вісім native-блоків на сторінку: шість teaching sections, practice-set, continuation summary-list.
Comparison table: B2 section 2, C1 section 2, C2 section 4. Решта teaching sections — usage-panels.
Перший старий box зберігає ID/UUID/order/heading/column/locale/owner; змінюються лише type/body.
Сім нових блоків на сторінку мають deterministic uuid_key; hidden duplicate старого box немає.

| Урок | Native blocks | Usage points | Table rows × columns | Author cases / keys | Meaningful details |
| --- | ---: | ---: | ---: | ---: | ---: |
| B2 | 8 | 15 | 3 × 4 | 6 / 6 | 0 |
| C1 | 8 | 14 | 3 × 5 | 6 / 6 | 1 |
| C2 | 8 | 16 | 4 × 3 | 6 / 6 | 1 |

Accepted blobs → finite projection → canonical definitions звірені незалежно: повний ordered core, wording, punctuation, examples, translations, table headers/cells, href order та protected metadata. Усі 18 prompts і 18 explanation keys точно збережено. Static self-check presentation — єдиний виняток щодо порядку: адаптери згруповано за UI-типом, але source_index 1–6 унікальні, оригінальні keys і no-JS prompts лишаються в author order.
Базовий контекст C1 section 3 з Олегом не замінювався Данилом: accepted source містить Олега у teaching section та Данила в self-check case 3; збережено обидва точні контексти.

Негативні fixtures перевіряють unless polarity/if-not meaning, as-long-as condition/duration, future form, result time, mixed direction, might/could→would, assumption/requirement, otherwise antecedent, but-for conditional/except, malformed should/were/had, not before subject, question-as-condition, lost translation, neighbour detail, duplicate anchor та lost practice answer.
Unknown/mismatched source має full-content native fallback. Немає runtime word-count filter або client fetch для details.

## Семантичний audit кандидатів

Point усіх рядків — `source-final-paragraph` конкретної teaching section. B/D/S — слова матеріалу секції до кандидата / слова кандидата / речення кандидата; це лише діагностика, не поріг. Для C1/C2 аналіз належить одному paragraph-level point після повного англійського абзацу й повного перекладу.

| Урок / section / point | B | D | S | Рішення | Причина |
| --- | ---: | ---: | ---: | --- | --- |
| B2 / 1 / final paragraph | 48 | 26 | 2 | visible basic | Редакційна ремарка й основна мета уроку. |
| B2 / 2 / final paragraph | 77 | 52 | 3 | visible basic | Регістр/сумісність пояснюють основну таблицю; unless caveat потрібний одразу. |
| B2 / 3 / final paragraph | 89 | 62 | 5 | visible basic | Відомий past counterfactual — важлива межа механічної unless-заміни. |
| B2 / 4 / final paragraph | 35 | 45 | 4 | visible basic | Тривалість — друга половина основного контрасту. |
| B2 / 5 / final paragraph | 76 | 43 | 4 | visible basic | Сфера future rule та will/would caveat необхідні одразу. |
| B2 / 6 / final paragraph | 103 | 31 | 3 | visible basic | Контроль заперечення завершує короткі ситуації. |
| C1 / 1 / final paragraph | 95 | 36 | 3 | visible basic | Основний аналіз facts/imagined process. |
| C1 / 2 / final paragraph | 94 | 40 | 3 | visible basic | Time contrast і state/process завершують таблицю. |
| C1 / 3 / final paragraph | 89 | 53 | 3 | visible basic | Окремий past episode потрібний для контрасту зі стійкою рисою. |
| C1 / 4 / final paragraph | 99 | 43 | 3 | visible basic | Основне modal rule перед прикладами. |
| C1 / 5 / final paragraph | 81 | 49 | 5 | meaningful detail | Окремий coherent analysis: facts, past→present, uncertainty, tomorrow’s, might/would. |
| C1 / 6 / final paragraph | 105 | 30 | 2 | visible basic | Caveat про possibility та reverse causation завершує core analysis. |
| C2 / 1 / final paragraph | 100 | 41 | 3 | visible basic | Основна requirement/assumption distinction. |
| C2 / 2 / final paragraph | 107 | 23 | 2 | visible basic | Punctuation поруч з otherwise/antecedent. |
| C2 / 3 / final paragraph | 167 | 27 | 3 | visible basic | Короткий міст до if/inversion. |
| C2 / 4 / final paragraph | 190 | 57 | 6 | visible basic | Навіть довгий caveat тут є основною межею inversion rule. |
| C2 / 5 / final paragraph | 154 | 26 | 2 | visible basic | Question contrast завершує negation rule. |
| C2 / 6 / final paragraph | 88 | 58 | 5 | meaningful detail | Coherent analysis чотирьох marker functions та точного otherwise antecedent. |

Разом: 16 visible basic / 2 meaningful. Чотири кандидати <30 слів (B2=1, C1=0, C2=3) усі видимі. Довші core comments також не приховувалися.
Retained anchors: `block-m31-c1-section-5-point-1-detail`, `block-m31-c2-section-6-point-1-detail`.
Обидва виправдовують один клік окремим п’ятиреченнєвим розбором повного контексту, а не одним прикладом або перекладом. Основні правила/таблиці/винятки не переміщені в detail.

## Практика та linked banks

На кожній сторінці 2 selects + 2 choices + 2 grouped-token/manual inputs, correct/wrong/reset. Повний prompt, original explanation і всі keys видимі; для M31 no-JS є точний список шести авторських завдань.

| Урок | Select cases | Choice cases | Token/manual cases |
| --- | --- | --- | --- |
| B2 | 1 booking unless; 2 rain exception | 3 studio/provided-that; 4 atlas/lantern meaning | 5 Eva confirms; 6 reply by noon + pay today |
| C1 | 1 battery past→now; 2 Iryna current museum work | 3 Danylo stable fear/past climb; 5 curator/might | 6 access code/could now; 4 finished Tuesday versus ready now |
| C2 | 2 full send-today otherwise antecedent; 3 past generator/but-for | 1 assumption versus requirement; 5 Lena not after subject/might | 4 all three Should/Were/Had transformations; 6 full grant/permission/otherwise paragraph |

C1 case 4 не названо equivalent transformation: перевіряється пара різних result clauses A→B, повний контекст і пояснення time/meaning contrast залишені. C2 case 4 перевіряє всі три речення разом; case 6 приймає тільки author-approved on-condition-that/provided-that і semicolon/period alternatives. B2 case 6 приймає I’ll/I will. Внутрішні sentence boundaries у multi-sentence inputs збережено; terminal punctuation опційна.
As long as у B2 case 3 не оголошено неграматичним: exact instruction вимагає provided that, author explanation і contextual feedback це пояснюють.

SELECT-only inventory виконано до generation; dedicated single-level banks визначено за фактичними links, type та distinct levels усіх питань, не за назвами.

| Рівень | Seeder у `Database\Seeders\V3\Polyglot\` | Type | Question IDs / count |
| --- | --- | --- | --- |
| B2 | `PolyglotConditionalsWithUnlessProvidedAsLongAsB2LessonSeeder` | 4 | 17265–17312 / 48 |
| C1 | `PolyglotAdvancedConditionalsC1LessonSeeder` | 4 | 17601–17648 / 48 |
| C2 | `PolyglotConditionalAlternativesAndNuanceC2LessonSeeder` | 4 | 18465–18512 / 48 |

Questions/answers/options/verb_hint/pivots/saved tests не є цілями запису.

## Guarded apply та postconditions

Фізичний read-only proof: DNS тільки loopback; один Apache port-80 vhost, document root `D:/DEV/htdocs/gramlyze.loc/public`, Apache PID/pid-file match; локальний mysqld listener і server hostname match. Web/CLI identity і nonce-bound runtime digest збігаються.
`APP_ENV=production`, але SiteMode `development`; фактична ціль — робочий локальний MySQL `gr2`, localhost:3306. `.env` не змінюється.
Temporary GET-only nonce route не повертає password/APP_KEY/cookie/token. HEAD і forwarded GET уже перевірені — 404.
Sources синхронізовано тільки після reconciliation з accepted Git bytes та exclusive backup; сторонні unknown bytes викликають відмову, concurrent edit перевіряється перед записом.

Source backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m31-local/source-backup-1b58f584b9121644`.
Перший exact-reviewed preview: `m31-preview-v1.json`, before state, 3 updates / 21 inserts / 0 deletes; digest `8a88985e66e1139450b2ef141bfe2edcb99d6afe2744b0aa1ed5ba8c2d072551`.
Independent review перевірив кожне поле, ID/UUID/order/body, hashes sources і повноту source backup.
Fresh preview `m31-preview-v2.json` повторив той самий digest; independent exact review PASS перед записом.
Exclusive DB backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m31-local/m31-before-v2.json`, SHA-256 `713f6a3507cb330436b865507bd370596dbf58dfbbfa320ba8d3e8eafddf1cd6`.
Transactional apply: 3 updates (type/body), 21 inserts, 0 deletes. IDs сторінок: 287 / 294 / 309.
Повторний apply — `no-op`, 0 updates / 0 inserts; `m31-unused-noop-v1.json` не створений.
Independent SELECT postconditions `m31-after-apply-v1.json` PASS: text_blocks 6355→6376, точні DB bodies/UUID/order, subtitle/hero/old ID збережені; 19 інших таблиць і 12 M27–M30 author-page snapshots незмінні.
Temporary route видалено після no-op; контрольний GET nonce path → 404. Raw routes file byte-identical до стану перед proof: SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Чужі зміни routes не перезаписувалися.
Фінальний SELECT snapshot після всіх браузерних запусків: `m31-after-browser-v2.json`, independent verification PASS. Крім 19 таблиць/12 регресійних сторінок/власних банків, digest усіх **6352 нецільових text_blocks** точно збігається з reviewed preview (зокрема весь M26). `.env` і routes raw hashes незмінні.

## Автоматичні перевірки

- Node/JSDOM M27–M31 practice, один окремий запуск: 120/120 PASS, без skips/failures.
- Node diagnostic-contract suite, інший запуск: 5/5 PASS; includes negative guards/metadata/fallback checks.
- Vitest, окремий запуск: 3 files / 32 tests PASS (theorySections, theoryNavigation, theorySidebarLayout).
- Перший ізольований PHP batch із 24 suites M26–M31: 269 tests / 5301 assertions, одна помилка тесту (не даних): він вимагав nonempty intro B2-таблиці, хоча accepted source не має вступного параграфа. Виправлено лише тест на exact DOM-derived intro/outro; порожній accepted intro збережений. Fingerprints: 46648 protected files, changes 0.
- Окремий повторний M31AuthorFidelityTest: 22 tests / 272 assertions PASS, protected changes 0.
- Остаточний повний 24-suite batch M26–M31: **269 tests / 5328 assertions PASS**, child/runner exit 0, 46648 protected files / changes 0. PHP 8.5.10, PHPUnit 12.5.35, 05:16.763, 96 MB. Evidence: `storage/app/m30/storage/app/seo-m2-local/m31-integrated-final-551e89f973d94796911dfa896a51fef9-result.json` (private, not committed). SQLite :memory:, array cache/session, owned runtime; working MySQL не використовується для fixtures. Відомий PHP 8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA` у config/database.php не приховувався.
- Окремий Node M26-practice diagnostic-contract run: 3/3 PASS; не підміняє реального браузерного приймання.
- Після розширення no-JS diagnostics окремий Node contract run: 6/6 PASS.
- Остаточний спільний Node/JSDOM + diagnostic run (7 files M27–M31): **131/131 PASS**, 0 failures/skips. Це окремий запуск, не сума попередніх.
- Після виправлення JSDOM selector й додаткового regression test: окремий contract run 7/7, потім новий фінальний 7-file Node run **132/132 PASS**, 0 failures/skips. Попередні 131 не додавалися до цього числа.
- PHP/Node syntax, JSON parsing та `git diff --cached --check`: PASS. Незалежний фінальний review усіх 33 staged файлів — без блокерів; index/worktree normalized bytes exact, raw hash-bound blobs exact, canonical=index=projection.after, forbidden/private paths excluded, secret-pattern scan PASS.

## Реальні HTTP / browser / no-JS / overflow

Baseline: 24 guest GET → 200; `storage/app/seo-m31-local/before-http.json`.
Ordered sitemap: 554 URLs, SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`.
Перший capture не зарахований: 30-second timeout на M26 overview після трьох target GET. Окремий curl GET → 200 за 0.78s; новий повний capture → PASS. Автоматичних прихованих retries немає.
`acceptance-v2-http.json`: 24 guest GET → 200, title/H1/description/OG/Twitter/canonical/robots/X-Robots-Tag, legacy anchors та контрольний навчальний текст незмінні. Ordered sitemap before == after: 554 URLs, той самий SHA-256.
`acceptance-v2-browser.json`: **12 M31 states + 15 no-JS + 12 JS-regressions M27–M30 PASS**. 1440×1000 / 390×844, light/dark, fresh guest contexts, GET-only. Mouse/Enter/Space/focus-visible, own-point placement, independence, simultaneous open там, де >1 detail, reload closed, own deep fragments, print state/restore, no detail fetch перевірені. Повний ordered core і exact author cases/keys збігаються в initial HTML та reload DOM.
M31 correct/wrong/reset, token-only/manual, backspace reuse, terminal punctuation optional, approved variants та exact linked primary bank PASS. Додатково 56 semantic negative inputs відхилені; contextual as-long-as instruction-mismatch feedback PASS у чотирьох станах.
Page/console/local network/HTTP/policy errors: 0; font failures у цьому M31 run: 0. Збережено 92 bounded PNG у приватному `storage/app/seo-m31-local`; representative desktop/mobile/light/dark table/practice/basic/detail screenshots переглянуті головним агентом і незалежним reviewer, обрізання навчального контенту/накладання немає.
No-JS: усі 15 уроків мають exact readable basic. M31/M30 мають literal fallback із шістьма prompts і видимими author keys; для M27–M29 цей runner підтверджує лише видимий practice markup — не повноту token contexts. Окремий stricter diagnostic для старої практики наведено нижче.
Окремий accepted M26 detail runner `seo-m26-local/m31-regression-v1-browser.json`: **5 GET200 + 20 browser states + 5 no-JS PASS**, 56 unique details (8/12/12/12/12), mouse/keyboard/ownership/print/deep links. Page errors/local failed requests/local HTTP/policy violations 0; font requestfailed events 0. Console stream цей runner не збирає — zero-console для нього не заявляється. П’ять очікуваних CSP script-block events у no-JS. Збережено 20 bounded viewport PNG.
M26 main/card overflow 0 px; desktop document 0, mobile animated-decor document 1–9 px. Strict document-zero не PASS, але learningOverflowPass=true; це обліковується окремо і фон не змінюється.
Додатковий practice-only run `m26-practice-v1` не зарахований: JSDOM selector `[x-data^="theoryPracticeSet("]` помилково повернув 0 при реальному x-data/трьох вправах. Minimal DOM reproduction підтвердив selector-engine issue; виправлено лише diagnostic selector на exact attribute startsWith, count=1 assertion збережена.
Fresh `m26-practice-v2`: 4 GET200 і **4/4 M26 JS practice PASS** (correct/wrong/reset/all accepted aliases/token-only/manual/backspace/reload clean), 4 PNG. Page/local network/HTTP/console/font/policy errors: 0. Strict no-JS результат наведений нижче; exit 1 не приховано і не названо повним PASS.
Перший `acceptance-v1` не зараховано як PASS: негайний `isVisible()` після click зупинився на B2 contextual feedback. Окремий read-only live probe підтвердив exact Alpine answer `b`, checked=true та правильний feedback, але DOM x-show ще не оновився. Після `waitFor(visible)` exact текст видимий, page/network/policy errors 0. Виправлено лише діагностичний runner: explicit visibility wait + exact text/state assertions. Повторний lightweight Node run трьох M31 suites — 51/51 PASS. Fresh `acceptance-v2` PASS; runtime/content не змінювалися.
Content і decorative overflow обліковуються окремо; фон/global overflow CSS не змінено.
M31 learning main/cards/points: **0 px** у всіх 12 станах. Таблиці мають власний keyboard-focusable horizontal scroll (mobile client 322 / scroll 576 px); це не document/content overflow. Document overflow desktop: 0; mobile light B2/C1/C2: 5/0/10 px, dark: 7/5/9 px. Окремі animated decorative rectangles: до 14.793 px; фон залишено без змін, global overflow-x:hidden не додано.

## Наявне no-JS обмеження старої практики, не регресія M31

Strict diagnostic `m26-practice-v2-browser.json` повернув exit 1: M26 full-context no-JS 0/4; M27–M29 full-context no-JS 2/9. Усі 13 no-JS HTTP/network checks clean, selects/choices labels і наявні after instructions exact та видимі.
Причина: accepted shared view приховує slash-separated `before` для token inputs, а банк існує лише в Alpine template. У M26–M29 немає M30/M31 literal self-check fallback.
Exact accepted token branch порівняно з `ceb2162c862978ca461607be3913a9f717f380b2`: identical, normalized fragment SHA-256 `1cd9f920295ad119a31242fb308d0201d5f6c5daaf339b4f4088e39c4b000d77`; source-package hashes також accepted. Це не результат нових M31 opt-ins.

| Сторінка | Strict no-JS практика |
| --- | --- |
| [M26 Forms](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | 2 приховані token banks |
| [M26 Negatives](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives) | 2 приховані token banks |
| [M26 Questions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions) | 2 приховані token banks |
| [M26 Time Expressions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-time-expressions) | 2 приховані token banks |
| [M27 Reason, Result, Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | 2 приховані token banks; для input1 повний context додатково видимий у choice1 |
| [M27 Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | PASS: 2 plain-text prompts видимі |
| [M27 Concessive and Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | PASS: 2 plain-text prompts видимі |
| [M28 Cleft Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | 2 приховані token banks |
| [M28 Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | 2 приховані token banks |
| [M28 Advanced Fronting](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | 2 приховані token banks |
| [M29 Cleft and Emphasis](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis) | 2 приховані token banks |
| [M29 Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) | 2 приховані token banks |
| [M29 Ellipsis, Substitution, Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference) | 2 приховані token banks; input2 має окремий повний prompt у after |

Разом 11 сторінок / 22 token inputs: два мають альтернативний видимий повний context, для інших 20 контекст неповний. Відсутній банк не названо «порожнім завданням» у двох винятках і не замасковано PASS. Інтерактивна JS практика M26–M30 проходить; M31/M30 no-JS author fallback проходить. Навчальні rules/details no-JS проходять і для M26–M29.
Це finding для окремого scope: M31 allowlist точно три M15 owners, тому старі payloads або shared M26–M29 no-JS поведінка не змінювалися.

## Межі доставки

Коміт і normal push тільки перевірених M31 sources/diagnostics/tests/report у робочу M31 гілку. `.env`, vendor/build, runtime cache, proofs/private evidence, backups/дампи й сторонні зміни не включаються.
Explicit staging не включає жодного приватного evidence/backup/proof чи routes file. Normal push тільки робочої M31 гілки; фінальний повний SHA та remote match наводяться у повідомленні доставки після commit/push, щоб звіт не містив вигаданої self-referential SHA.
Production не перевірявся й не змінювався. Наступні три сторінки автоматично не оброблялися.
