# M27/M28 — якість point-level «Докладніше»

Дата: 2026-10-03. Accepted база: `9edca5db593585d09411ec8107272dd24e8151ab` (`codex/seo-m28-m12-emphasis-inversion-layers`). Робоча гілка: `codex/seo-m28-detail-quality-followup`, створена саме від цієї бази, без перенесення M29. Реальні HTTP/браузерні перевірки виконані тільки на `http://gramlyze.loc`.

## Результат і сторінки

| Урок / перевірений URL | Було | Прибрано | Залишено |
| --- | ---: | ---: | ---: |
| [Linking Words B2](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | 3 | 0 | 3 |
| [Advanced Linking Devices C1](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | 8 | 1 | 7 |
| [Concessive and Contrastive Structures C2](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | 11 | 8 | 3 |
| [Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | 6 | 6 | 0 |
| [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | 4 | 4 | 0 |
| [Advanced Fronting and Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | 10 | 10 | 0 |

Фактично пораховано package і live DOM: M27 — **3/7/3**, разом **13**; M28 — **0/0/0**. Прибрано **9 + 20** interactions, не навчальні тексти. Усі 29 колишніх short details тепер відразу видимі в basic своїх пунктів.

## Точний список прибраних disclosures

Нумерація пунктів нижче — 1-based; ключ секції наведений повністю.

M27:

- `m27-c1-section-2`, п. 3: ремарка про формальність furthermore/moreover і відсутність штучної різниці в прикладі.
- `m27-c2-section-1`, п. 1: коротке уточнення про контекст, а не лише переклад «хоча».
- `m27-c2-section-4`, п. 1: регістр though/although і even though.
- `m27-c2-section-4`, п. 2: In spite of reading… та переклад прикладу.
- `m27-c2-section-4`, п. 3: застереження про despite the fact that / despite of.
- `m27-c2-section-4`, п. 4: though у кінці речення.
- `m27-c2-section-5`, п. 1: «Обидві ознаки стосуються пропозиції.»
- `m27-c2-section-5`, п. 2: «Попереджали саме Нору.»
- `m27-c2-section-5`, п. 3: «Така конструкція формальніша за повну підрядну частину.»

M28 — усі колишні фрагменти, без нового авторського матеріалу:

- Cleft: `m28-cleft-section-2`, п. 4–5; `m28-cleft-section-3`, п. 2–4; `m28-cleft-section-4`, п. 3.
- Inversion: `m28-inversion-section-1`, п. 1; `m28-inversion-section-3`, п. 1; `m28-inversion-section-4`, п. 1; `m28-inversion-section-5`, п. 1.
- Fronting: `m28-fronting-section-2`, п. 1, 2, 4; `m28-fronting-section-3`, п. 2; `m28-fronting-section-4`, п. 1; `m28-fronting-section-5`, п. 1, 3; `m28-fronting-section-6`, п. 1–3.

## Збережені змістовні disclosures

- B2: `m27-b2-section-3`, п. 1 — because of/due to, порядок і коми; `m27-b2-section-4`, п. 1 — наслідок і пунктуація; `m27-b2-section-5`, п. 1 — even though / despite the fact that / nevertheless.
- C1: `m27-c1-section-1`, п. 1 — додавання переваги проти необґрунтованого наслідку; `m27-c1-section-2`, п. 1–2 — допустова конструкція, практичний наслідок і логічний висновок; `m27-c1-section-3`, п. 1 — умова, час і порядок; `m27-c1-section-4`, п. 1 — provided that проти insofar as; `m27-c1-section-5`, п. 1 — позиція зв’язки й пунктуація; `m27-c1-section-6`, п. 1 — пояснення п’яти зв’язків у цілому аргументі.
- C2: `m27-c2-section-2`, п. 1 — визнана обставина проти умови; `m27-c2-section-5`, п. 4 — виконавець скороченої конструкції та dangling subject; `m27-c2-section-6`, п. 1 — пунктуація, порядок подання й акцент.

Рішення семантичне й скінченне, без глобального runtime-правила «<30 слів». Постійне правило записане в `AGENTS.md` та `docs/content/point-detail-quality.md`.

## Fidelity, джерела та БД

Frozen v1 не змінено. Додано v2; для кожного прибраного пункту точна рівність `new_basic === old_basic + '<br><br>' + old_detail`. Retained points byte-identical. Кожен `after` v2 строго ідентичний `after` v1, включно з metadata, hero, усіма текстами, пунктуацією, прикладами, перекладами, практикою й посиланнями на банки. Переписування, вилучення або додавання навчальних слів немає.

Нові exact SHA-256:

- M27 v2: `d0e642b34f9289e722f9c157667d8569992f0bd0a60d7dabe67b570faa7fdcf6`.
- M28 v2: `f8d4b67ebb876cb36b17af3edf90afe6d593e401c85dbd38a99fc6f3ed88ca18`.

SOURCE і SHA переведено на v2; exact hash validation, owner/UUID/order/locale validation і повнотекстовий fallback не послаблено. Для v2 у `.gitattributes` додано `-text`, щоб Git не змінював байти JSON через CRLF.

Read-only inventory реального working application `D:/DEV/htdocs/gramlyze.loc` підтвердив MySQL `localhost:3306`, database `gr2`, і точні accepted bodies всіх **61 блока** шести сторінок. Повний basic + detail уже був у БД до правок. Після локальної синхронізації тільки двох package classes і двох v2 файлів page/block/question/link fingerprints та склади linked banks збігаються з початковими.

**Actual DB diff: 0 updates / 0 inserts / 0 deletes.** Apply, backup перед DB write, сідери, міграції, reseed, cache clear та тимчасовий route не потрібні й не запускались. Це presentation-only зміна; не fixture-підміна live перевірки.

Legacy section/self-check anchors збережені й зіставлені до/після в live HTML. Repository references з `#block-m27-` / `#block-m28-` та HTML/JSON references у реальних `text_blocks.body` і `pages.text` перевірені: посилань на 29 прибраних detail-якорів немає. Зайвих hidden targets не створено.

## Перевірки

- PHPUnit: **92 тести / 2079 assertions**, exit 0; package fidelity, finite short mapping без кнопок, native renderer з 0 disclosures, stale/unknown fallback, negative mutations, patch/local-target guards, M26 point details та interactive practice. Ізольовані SQLite `:memory:`, array cache/session, CLI OPcache off. Одне deprecated попередження конфігурації PDO MySQL; stderr порожній. Відбитки wrapper стосуються лише його worktree, доказ робочої БД — окремий read-only inventory.
- Node practice/contractions: **32/32 PASS**, current v2 practice payload, правильні/неправильні відповіді, альтернативи, reset, ручне редагування, повторне використання токенів.
- Vitest: **32/32 PASS** у 3 файлах: theorySections, theoryNavigation, theorySidebarLayout.
- Live M27/M28: **24/24 states PASS** — кожна з 6 сторінок при desktop 1440×1000 / mobile 390×844 × light/dark. Точний visible basic, own detail, відсутність merged buttons, повнота та порядок авторських пунктів, таблиці, унікальні IDs; retained detail mouse/Enter/Space, independent open/close, reload, deep fragment, print/restore. Без порожніх disclosures.
- Live no-JS: **6/6 PASS**; повний merged basic без JS, native retained disclosures відкриваються без fetch.
- Live practice: на кожному з 24 states усі 3 групи × 2 завдання, правильна/неправильна відповідь, альтернативи, reset, токени й ручне введення; linked widget містить тільки type 4 та question IDs зі свого фактичного банку.
- Metadata: title/H1/description/canonical/robots/OG/Twitter/X-Robots-Tag та legacy anchors не змінились. Усі 6 GET — HTTP 200.
- Sitemap: **554 URL**, ordered SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`, ідентичний до/після.
- M27/M28 learning main/cards overflow **0** в усіх states. Окремо декоративні shapes до ~18.2 px поза viewport: це не навчальний контент і не виправлялось у цьому scope.
- Page errors, локальні HTTP errors і failed local requests: **0**. Google Fonts: sandbox `net::ERR_NETWORK_ACCESS_DENIED`, відокремлено від локальних помилок; оцінки performance/CWV не робились.

M26: **56/56 деталей збережено**, 5 actual GET, **20/20 browser states**, **5/5 no-JS PASS**; повний basic, own author units, незалежне керування, клавіатура, reload, deep links і print/restore. **Практика всіх 4 підтем PASS** (3 групи × 2 завдання, correct/wrong/reset/manual/token reuse, linked widget). Learning-content overflow **0**; decorative document overflow на mobile **0–12 px**, тому strict whole-document overflow PASS не заявляється. Перший запуск мав GET timeout до початку приймання; його failure evidence збережено, повторний `m28-quality-regression-v2` завершений повністю й успішно.

Focused screenshot acceptance: два реальних видимих пункти C2 Concessive та Advanced Fronting — former detail відразу в basic, жодної кнопки/вкладеного details. 24 viewport screenshots M27/M28 та 20 M26 збережені приватно; representative mobile dark і focused screenshots оглянуті.

## Відтворення і межі

Новий entrypoint: `tools/diagnostics/seo-m28-detail-quality-local.cjs PRIVATE_DIR before before`, потім `... PRIVATE_DIR after LABEL`; PRIVATE_DIR basename `seo-m28-detail-quality`. `inspect-m28-quality-local.php` — тільки read-only working DB inventory; `project-m28-detail-quality.cjs --write` — виключно механічна генерація відсутніх v2 через exclusive file creation. Браузер використовує Playwright/Chromium, новий guest context для кожного сценарію, GET-only guard.

Приватні JSON evidence і screenshots зберігаються у `storage/app/seo-m28-detail-quality` та `storage/app/seo-m26-local`, а DB/test evidence — в `storage/app/seo-m11-worktree/storage/app`. Вони не входять у commit. M26 screenshot capture обмежений viewport через opt-in `GRAMLYZE_M26_VIEWPORT_SCREENSHOTS=1` для пам’яті; функціональні assertions не пропускаються, default full-page збережений.

Основний dirty checkout не скидався; сторонні зміни та вже наявний M29 runtime не переносились у нову гілку, M29 sources/prompts не застосовувались. JS/CSS build, server configuration, `.env` і routes не змінювались. Production `.com` / `.ub` не перевірявся й не змінювався; main, PR, merge, force і deploy не виконувались. Погоджений normal push — тільки нова follow-up гілка; фактичний SHA і перевірка remote == HEAD наведені у фінальній відповіді.
