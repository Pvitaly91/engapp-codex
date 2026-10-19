# M30 — M14 Participle Clauses та M29 detail-quality

Дата: 2026-10-03. Ціль — тільки робочий `http://gramlyze.loc`.
Зміни застосовано до робочого gramlyze.loc; real HTTP/browser/no-JS приймання завершено.
Production `.com`/`.ub`, main і PR не використовувалися.

## Інтеграція accepted історії

Робоча гілка: `codex/seo-m30-m14-participle-clauses-layers`.
Окремий worktree: `D:/DEV/htdocs/gramlyze.loc/storage/app/m30`.
Основний dirty checkout не reset/stash/clean; його HEAD залишився `41820a2bebdf69004fa7209a2a38457f93efabbd`.

Справжній merge-коміт: [c308b328ece596d92f91943d9dfb2495fc9af10a](https://github.com/Pvitaly91/engapp-codex/commit/c308b328ece596d92f91943d9dfb2495fc9af10a).
Його два parents — рівно:

- M29: `2a5043f9de7307bbb41a620df24729e214fa3f75`;
- M27/M28 quality: `0b7538ebe2303e8ebac00a9afcf825339bf2b256`.

Конфлікти лише технічні: union `.gitattributes` і коментар screenshot-runner M26. Обидві accepted роботи збережені; конфліктів author payload/definitions не було.
Squash, rebase, cherry-pick і force-push не застосовувалися.
Обидва `git merge-base --is-ancestor <accepted SHA> HEAD` завершилися exit 0 після merge і повторно після реалізації.

## Сторінки M14 та frozen sources

| Рівень / сторінка | Seeder у `Database\Seeders\Page_V3\ClausesAndLinkingWords\` | Accepted Git blob |
| --- | --- | --- |
| [B2 — Participle Clauses Basics](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses-basics) | `ParticipleClausesBasicsTheorySeeder` | `9e2b8dc2d32937332b0fc931c1cad38f9b1202a9` |
| [C1 — Participle Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses) | `ParticipleClausesTheorySeeder` | `47b17a940752a54aeda05bf1362febbe11e20e26` |
| [C2 — Advanced Participle and Absolute Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-participle-and-absolute-clauses) | `AdvancedParticipleAndAbsoluteClausesTheorySeeder` | `0c9658497fd31b001d92c8a7f42f63f132723a7e` |

Locale `uk`, type `theory`, ancestry рівно `clauses-and-linking-words`.
Усі три blob JSON незалежно зіставлено з frozen before через `git cat-file`.
Before SHA-256: `ab98112a9fe75886125b4d7ab2942d4262c5369b943bc33af55648e7dc409aaa`.
Пакет v1 SHA-256: `56a8f8894ba7554f7840ac8a35cbf61f41efea9f993d686e95211f17c4270f05`.
Hash-bound JSON мають `.gitattributes -text`.

Було: subtitle + hero + великий author box зі static self-check.
Стало: ті самі subtitle/hero + вісім native content-блоків на кожній сторінці.
Перший старий box зберіг ID, UUID, sort order, heading, column, locale, owner; змінилися тільки `type` і `body`.
Сім нових блоків на сторінку мають deterministic UUID keys.

| Рівень | Native блоки | Usage-пункти | Рядки таблиці / колонки | Self-check cases / keys | Meaningful details |
| --- | ---: | ---: | ---: | ---: | ---: |
| B2 | 8 | 17 | 3 / 4 | 6 / 6 | 0 |
| C1 | 8 | 22 | 4 / 4 | 6 / 6 | 0 |
| C2 | 8 | 29 | 5 / 4 | 6 / 6 | 0 |

Нормалізований ordered corpus зберігає всі слова, пунктуацію, приклади, переклади, table cells, cohesion paragraphs і посилання. Незалежні mutation tests відхиляють вилучення, перестановку, зміну пунктуації/перекладу/моделі чи авторського завдання. Hero/subtitle/metadata не редагувалися.

### Чому M14 не має штучних «Докладніше»

Рішення для кожного source-final-paragraph явне й семантичне: це застереження, основний розбір або завершення пояснення, а не окрема необов’язкова глибина. Весь текст одразу visible basic. Поріг 30 слів — тільки діагностичний прапорець, не runtime-правило.

У таблиці B/D/S — слова попереднього матеріалу секції / слова кандидата / речення кандидата; кандидат нічого не втрачає і не ховається.

| Урок / source section | B | D | S | Рішення `visible_basic`: причина |
| --- | ---: | ---: | ---: | --- |
| B2 / 1 | 97 | 27 | 3 | Основне застереження про учасників і модальність під час скорочення. |
| B2 / 2 | 88 | 35 | 3 | Основний active/passive контраст і застереження щодо стану. |
| B2 / 3 | 104 | 36 | 2 | Правило ком пояснює щойно наведену таблицю. |
| B2 / 4 | 65 | 28 | 2 | Межі правила: adverbial, noun-modifying, absolute. |
| B2 / 5 | 47 | 37 | 3 | Виправлення з відомим виконавцем без вигаданих фактів. |
| B2 / 6 | 32 | 25 | 2 | Короткий підсумковий checklist. |
| C1 / 1 | 152 | 27 | 2 | Необхідне уточнення time versus cause. |
| C1 / 2 | 129 | 29 | 2 | Perfect/passive контраст і збереження ролей учасників. |
| C1 / 3 | 64 | 37 | 5 | After/perfect зіставлення завершує lexical having контраст. |
| C1 / 4 | 126 | 33 | 3 | Основний приклад negative passive perfect. |
| C1 / 5 | 90 | 20 | 2 | Продовження пояснення, коли повна конструкція ясніша. |
| C1 / 6 | 51 | 35 | 4 | Основний аналіз зв’язків cohesion paragraph. |
| C2 / 1 | 111 | 45 | 3 | Absolute versus finite завершує визначення. |
| C2 / 2 | 184 | 24 | 3 | Коротке perfect/V3 застереження поруч із таблицею. |
| C2 / 3 | 118 | 39 | 3 | Основний розбір dangling modifier та невідомого читача. |
| C2 / 4 | 96 | 21 | 2 | Застереження від механічного видалення with. |
| C2 / 5 | 161 | 31 | 3 | Noun-modifying контраст пояснює відмінність ком. |
| C2 / 6 | 146 | 20 | 2 | Коротка рекомендація завершує зв’язний текст. |

Кандидатів <30 слів: 3/3/3, разом 9 із 18. Довші дев’ять також основні, а не optional addenda. Нові пояснення не генерувалися.

### Практика та точні банки

На кожному уроці всі шість оригінальних випадків адаптовано в 2 selects + 2 choices + 2 token/manual inputs. Source indices 1–6 унікальні; повний prompt і весь авторський ключ збережено. Інструкція й шість ключів доступні одразу; no-JS fallback містить точні шість завдань. В обох C2 manual cases перевіряється внутрішня пунктуація (`punctuation_sensitive=true`).

Основний linked widget бере тільки фактично прив’язаний single-level банк. Вибір доведено SELECT-інвентаризацією, зокрема distinct levels по всіх питаннях seeder, не виведено лише з назви.

| Рівень | Seeder у `Database\Seeders\V3\Polyglot\` | Type | Питання / IDs |
| --- | --- | --- | --- |
| B2 | `PolyglotParticipleClausesBasicsB2LessonSeeder` | 4 | 48; 17169–17216 |
| C1 | `PolyglotParticipleClausesC1LessonSeeder` | 4 | 48; 17937–17984 |
| C2 | `PolyglotAdvancedParticipleAndAbsoluteClausesC2LessonSeeder` | 4 | 48; 18945–18992 |

Жодних записів question/answer/option/hint/pivot/saved-test не змінено.

## M29 quality audit після merge

Перевірені сторінки:

- [Cleft Sentences and Emphasis](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis);
- [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases);
- [Ellipsis, Substitution and Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference).

Frozen M29 v1 незмінний: `e28b75ee73f02fb7ed12bff25ef35423ee724a384927156eac877a3f79429d06`.
Новий v2: `b630a175cb9058a7e847ac852b0bcd56be5e18106996de1ff8979c1831aab18a`.
Для кожного з 40 пунктів exact `old_basic + '<br><br>' + old_detail`, `detail=''`.
Усі `after` payloads v2 точно дорівнюють v1: повний content, practice, metadata та bank bindings не перегенеровано.
Actual DB вже містила цей повний native payload; її M29 page/block hashes до й після однакові. M29 DB diff: 0 updates / 0 inserts / 0 deletes.

| Урок | До details | Перенесено до власного basic | Meaningful після |
| --- | ---: | ---: | ---: |
| Cleft | 12 | 12 | 0 |
| Noun | 13 | 13 | 0 |
| Ellipsis | 15 | 15 | 0 |

Нижче ID задано як `<lesson> / S.P`; точний колишній HTML ID — `block-m29-<lesson>-section-S-point-P-detail`. B/D/S — початкові basic words / detail words / detail sentences. У v2 D/S=0 для всіх пунктів. Усі 40 перевірені семантично; 36 мали D<30, чотири довших також були частинами основного пояснення.

| Point | B | D | S | Причина exact merge у basic |
| --- | ---: | ---: | ---: | --- |
| cleft / 2.1 | 23 | 3 | 1 | Кого виправляє основний діалог. |
| cleft / 2.2 | 25 | 4 | 1 | Фокус видимого прикладу. |
| cleft / 2.3 | 28 | 6 | 1 | Підсумок видимого порівняння. |
| cleft / 3.1 | 30 | 17 | 2 | Контрастний приклад завершує зіставлення заперечень. |
| cleft / 3.2 | 16 | 4 | 1 | Функція основного запитання. |
| cleft / 3.3 | 25 | 26 | 3 | Продовження після крапки з комою; інтонація й доречність. |
| cleft / 4.1 | 17 | 19 | 2 | Варіант was to remove та застереження від узагальнення. |
| cleft / 4.2 | 20 | 7 | 1 | Значення основного прикладу. |
| cleft / 4.3 | 16 | 7 | 1 | Фокус основного прикладу. |
| cleft / 5.1 | 23 | 7 | 1 | Допустимі who/that. |
| cleft / 5.2 | 37 | 17 | 1 | Варіативне узгодження та межі вправ. |
| cleft / 5.3 | 18 | 13 | 1 | Часові форми основного прикладу. |
| noun / 3.1 | 17 | 25 | 3 | Кроки 2–4 продовжують видимий крок 1. |
| noun / 3.2 | 17 | 28 | 3 | Кроки 2–4 завершують той самий розбір. |
| noun / 3.3 | 20 | 36 | 3 | Завершення розбору та неоднозначне приєднання. |
| noun / 4.1 | 36 | 19 | 1 | Структура вже наведеного complement. |
| noun / 4.2 | 18 | 17 | 1 | Роль that у видимому relative. |
| noun / 4.3 | 12 | 23 | 2 | Ненадійність тесту видалення. |
| noun / 5.1 | 25 | 31 | 2 | Межі узгодження; нова модель не розгортається. |
| noun / 6.1 | 13 | 24 | 1 | Apposition завершується двокрапкою перед basic-прикладом. |
| noun / 6.2 | 10 | 11 | 1 | Пунктуація цього прикладу. |
| noun / 6.4 | 19 | 4 | 1 | Належність місця. |
| noun / 6.5 | 20 | 9 | 1 | Належність місця в контрасті. |
| noun / 7.1 | 30 | 9 | 1 | Контекстна ремарка про термін. |
| noun / 7.2 | 58 | 20 | 2 | Рекомендація завершує приклад спрощення. |
| ellipsis / 1.1 | 18 | 30 | 2 | Термінологічне застереження про класифікації. |
| ellipsis / 2.1 | 44 | 13 | 1 | Службові квадратні дужки. |
| ellipsis / 2.2 | 31 | 6 | 1 | Допустимість повної форми. |
| ellipsis / 2.3 | 20 | 10 | 1 | Не видаляти підмет довільно. |
| ellipsis / 2.4 | 6 | 44 | 4 | Відновлення Ready to leave? та основне порівняння регістрів. |
| ellipsis / 3.4 | 30 | 11 | 1 | Register note I think not. |
| ellipsis / 3.5 | 17 | 12 | 1 | Значення I'm afraid. |
| ellipsis / 3.6 | 31 | 10 | 1 | Рекомендація щодо незнайомого дієслова. |
| ellipsis / 4.1 | 27 | 11 | 1 | Допоміжне дієслово відповідно до часу й особи. |
| ellipsis / 4.2 | 21 | 23 | 2 | Що замінює did so, register note. |
| ellipsis / 4.4 | 33 | 13 | 1 | Допустима agreed to. |
| ellipsis / 6.1 | 31 | 24 | 1 | That і межі механічного правила відстані. |
| ellipsis / 6.2 | 30 | 9 | 1 | Анафоричний зв’язок. |
| ellipsis / 6.3 | 39 | 9 | 1 | Редакційна рекомендація щодо довгих переліків. |
| ellipsis / 7.5 | 7 | 10 | 1 | Явніше This test. |

Frozen v1 не редагувався; глобальні шаблони не отримали word-count heuristic. Усі 40 removed detail anchors перевірено на href/target/anchor references в repository/runtime і SELECT по всіх DB pages/text_blocks: посилань 0. Чужий detail до нового пункту не переносився.

## Local-target proof → preview → backup → apply → no-op

Фізична ціль підтверджена окремо від `APP_ENV`: Windows Apache document root `D:/DEV/htdocs/gramlyze.loc/public`, vhost `gramlyze.loc`, DNS loopback, Apache PID/pid-file match, MySQL local listener PID, DB `gr2` на локальному `DESKTOP-3C05HGF:3306`. Web і CLI connection/runtime fingerprints збігаються. `APP_ENV=production`, але SiteMode `development`; це не production-сервер.

Тимчасовий випадковий GET nonce route працював лише на HTTP `.loc`, raw loopback REMOTE_ADDR; HEAD і forwarded requests давали 404. Нічого не записував, не повертав APP_KEY/password/cookies/tokens. Після apply/no-op route видалено; повторний GET 404. Routes file повернуто побайтно до попереднього SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`, зі збереженням сторонніх змін.

Fresh preview `m30-preview-v3.json`: before state, 3 updates / 21 inserts / 0 deletes.
Plan digest: `80c5aeb033c175c2c9bda2847e3c2a4e0a6a504c6c12a27cd0d800d7743fd217`.
Незалежний exact-field review звірив кожен before/after body/type, owner ID/UUID/order, усі 21 inserted fields і source hashes. Відповідність резервних source bytes також перевірено.

| Page | Старий ID, оновлено тільки type/body | Нових блоків |
| --- | ---: | ---: |
| B2 — 285 | 8744 | 7 |
| C1 — 299 | 8786 | 7 |
| C2 — 318 | 8848 | 7 |

Exclusive DB backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m30-local/m30-before-v3.json`.
SHA-256: `5325cf38eeaaf19300c99191a33623347acd186e154a27f5da90efbf87954bda`.
Source backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m30-local/source-backup-d33ac2b0e929e635`.
Backups/preview/private evidence не комітяться і не видаляються.

Apply транзакційний, fresh proof повторений командою; postconditions перевірені до commit транзакції. Результат: 3 updates / 21 inserts.
Повтор тієї самої команди: `no-op`, 0 updates / 0 inserts; `m30-unused-noop-v3.json` не створено.

Незалежні повні table fingerprints до/після apply і повторно після всього browser acceptance: змінився тільки `text_blocks` (6334 → 6355). Решта 19 таблиць, усі M29 page/block hashes, linked bank identities та IDs unchanged. Це включає pages, categories, tags/pivots, questions/answers/options, hints/variants, saved tests і seed_runs. Перші subtitle/hero та protected owner fields незмінні; M29 inserts/updates/deletes — 0. Фінальна SELECT evidence: `storage/app/seo-m30-local/m30-after-browser-v4.json`.
Немає seeding/migrations/reset/full restore, HTTP/startup apply hooks, cache/session clear, Apache/XAMPP/hosts/config змін.

## Автоматичні перевірки

Ізольований PHP wrapper, PHP 8.5.10, SQLite fixtures / array caches / array sessions; робоча MySQL не використовується для test fixture writes. 20 цільових suites M26–M30:

- PHPUnit exit 0; 206 tests, 4680 assertions.
- Одна існуюча deprecation; без failures/errors. Її не приховано.
- 46 648 protected files: 0 змін. Before/after inventory SHA-256 однаковий: `2a2b567939462ff6a2dbcf6354a1c097665204f4cb44f1d71fd6c80f90474028`.
- M30 author-fidelity, 17 mutation cases, finite package/identity/guard, preview/apply/backup/no-op/restore, conflicts/partial/manual/ambiguous states, injected rollback, isolated production-profile denial, M29 quality та M26–M28 regressions охоплені.
- Node/JSDOM M27–M30 practice suites: 77/77 PASS (окремо від live browser).
- JSON parse: 6/6; PHP lint дев'яти нових PHP runtime/diagnostic файлів, Node syntax checks та `git diff --check`: PASS.
- Staged exact-index bytes для трьох hash-bound JSON перевірені; secret-pattern/path scan без знахідок. Independent final diff review проти merge base — без actionable findings.

Private PHP evidence: `storage/app/m30/storage/app/seo-m2-local/m30-integrated-62a406a907874194b226dd26498a9d32-result.json`.

## Реальні HTTP та браузерні перевірки

Перед apply знято 19 guest GET сторінок та ordered sitemap. Усі GET — 200. Baseline: `storage/app/seo-m30-local/before-http.json`.
Ordered sitemap: 554 URLs, SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`.

Фінальне strict приймання `acceptance-v2` — PASS:

- M14: 12 states = 3 сторінки × desktop 1440×1000 / mobile 390×844 × light/dark.
- M29: окремі 12 states з тією самою матрицею.
- No-JS: 3 M14 + 3 M29 сторінки, повний basic і точні author prompts/keys доступні.
- M27/M28: 6 реальних регресійних сторінок, практика correct/wrong/reset, bank ownership, keyboard/mouse/deep-link/print перевірки там, де є details. M27 — 3/7/3=13 meaningful; M28 — 0/0/0.
- В усіх M14/M29 states повний basic видимий одразу, немає порожніх disclosure wrappers. Reload, legacy section anchors і print з відновленням state перевірено; removed M29 detail hrefs не залишено.
- Практика: кожна група 2/2 correct, wrong rejected, reset працює. Token-only складання, ручна відповідь, delete/reuse токена та optional terminal punctuation перевірені. У C2 окремо відхилено три неправильні випадки внутрішньої пунктуації/finite-versus-absolute моделі.
- Primary linked pools: 5 випадкових unique питань на widget, кожне ID з фактично inventoried власного 48-question single-level банку; foreign questions немає.
- Console errors / JS page errors / local failed requests / HTTP errors / policy violations: 0. Окремо 66 зовнішніх Google Fonts запитів заблоковані середовищем `net::ERR_NETWORK_ACCESS_DENIED`; fallback fonts, не помилка локального JS.
- Збережено 80 bounded viewport/focused PNG: top кожного state та B2 sections 3/5/7, C1 2/3/5/6/7, C2 2/3/4/5/6/7. Основний агент візуально переглянув desktop/mobile light/dark, tables, dangling, cohesion і practice. Скриншоти приватні, не Git.
- Навчальний main/card overflow: 0 px. Desktop document overflow: 0 px. На mobile декоративний document overflow становить 0–10 px для M14 та 0–9 px для M29. Таблиці мають власний `overflow-x:auto` (наприклад B2 client 322 / scroll 576 px); навчальні колонки не втрачені.
- Декоративні bounding boxes рахуються окремо (M14 максимум 16.15 px); не виправляли фон, не додавали global overflow hiding і не трактували decor як втрату навчального контенту. Нульового document overflow для всіх mobile states не заявляється.

Evidence: `storage/app/seo-m30-local/acceptance-v2-browser.json` і `acceptance-v2-http.json`.
Post-GET: усі 19 URLs — 200; title/description/H1/canonical/meta robots/X-Robots-Tag та legacy section anchors unchanged. Ordered sitemap — ті самі 554 URLs і hash. Контрольні ordinary native Present Perfect Continuous Forms та M15 B2 текстові hashes unchanged.

### Окреме real M26 приймання

`storage/app/seo-m26-local/m30-regression-v1-browser.json` — PASS: 5 actual GET → 200, усі 56 unique meaningful details, 20 desktop/mobile light/dark states, 5 no-JS. Повний basic, ownership, independent disclosures, keyboard/mouse, reload/deep-link/print та початкові metadata збережені. 20 додаткових viewport PNG; representative screenshots також переглянуті основним агентом.
JS page errors / local failed requests / HTTP errors / policy violations — 0; M26 runner не збирає загальний console event stream, тому нуль усіх console messages для нього не заявляється. Окремо 45 заблокованих Google Fonts і 5 expected disabled-script CSP подій no-JS.
Навчальний overflow — 0 px; desktop document — 0 px. На mobile раніше наявний overflow поза навчальним контентом — 1–15 px: `strictDocumentOverflowPass=false`, `learningOverflowPass=true`. Його не маскували й не змінювали global CSS.

| Регресія | Реальні сторінки | Details після |
| --- | --- | --- |
| M26 | [Overview](http://gramlyze.loc/theory/past-perfect-continuous), [Forms](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms), [Negatives](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives), [Questions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions), [Time Expressions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-time-expressions) | 8/12/12/12/12 = 56 |
| M27 | [Reason/Result/Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast), [Advanced Linking](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices), [Concessive/Contrastive](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | 3/7/3 = 13 |
| M28 | [Cleft Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics), [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics), [Advanced Fronting](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | 0/0/0 |
| Controls | [Ordinary native PPC Forms](http://gramlyze.loc/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms), [M15 B2 Unless](http://gramlyze.loc/theory/conditionals/conditionals-with-unless-provided-as-long-as) | Main content/metadata hashes unchanged |

Перший strict запуск `acceptance-v1` не зараховано: після 16 успішних states Chromium отримав `net::ERR_NO_BUFFER_SPACE` для локального JS у наступному state. Evidence збережено. Другий запуск обмежує процес Chromium чотирма послідовними contexts, без retries або фільтрації локальних помилок; усі assertions залишені strict. Вебзастосунок/серверні налаштування для цього не змінювалися.

## Межі доставки

До commit обираються тільки M30/M29 quality sources, canonical definitions, opt-in shared renderers, цільові tests/diagnostics і цей report. `.env`, vendor/build, cookies/tokens, runtime caches, private backups/DB dump і сторонні незавершені зміни не включаються.
Normal push лише робочої M30 гілки; main/PR/production/deploy — поза scope.
