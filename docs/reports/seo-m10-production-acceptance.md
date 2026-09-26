# M10 — production SEO та browser acceptance

Дата перевірки: 26.09.2026. Production origin: `https://gramlyze.com`.

## Самері

Обмежене production-приймання має статус **PARTIAL**. Через звичайні гостьові HTTP-запити перевірено server redirects, 19 обов'язкових matrix paths, robots/canonical/noindex, два послідовні sitemap GET, 20 детермінованих URL із sitemap, production manifest/assets та три контентні spot-check. Через Google Chrome 154 / Playwright виконано headed smoke і 8 унікальних desktop/mobile сценаріїв.

Основні indexable сторінки доступні й не мають випадкового `noindex`. Questions HTML indexable, JSON має technical `X-Robots-Tag`, `/step` та `/search` мають `noindex, follow`, state GET є справжнім 404 із technical noindex. Sitemap має 554 унікальні ordered loc, стабільний у двох GET і збігається з прийнятим M9.3 ordered baseline. Production manifest та всі його чотири файли існують; browser реально завантажив CSS/JS/Livewire. Questions у двох fresh guest contexts зберіг 3 відповіді, пережив reload і незалежно відновився із сервера після видалення тільки власного browser snapshot.

**Блокерів індексації основних сторінок не знайдено.** Є один підтверджений server-level SEO-дефект: `https://www.gramlyze.com/` повертає `200`, а не redirect на non-www; окремий `curl` відтворив те саме. Canonical на цій відповіді правильно вказує `https://gramlyze.com/`, тому дефект послаблений canonical, але вимога єдиного transport origin не виконана. HTTP redirect також повертає еквівалентну, але ненормалізовану адресу `https://gramlyze.com:443/`.

Exact deployed SHA не підтверджений незалежним production marker; перевірена поведінкова відповідність актуальному main. Власник заявив, що розгорнуто latest main. Production не має release/revision/commit header чи version meta, а read-only `/health` повертає лише status. Тому asset hash не видається за Git SHA.

## 1. Git-база, гілка та межі

| Параметр | Фактичне значення |
| --- | --- |
| GitHub `main` через `git ls-remote` | `8b4d52b003f7f827b6a584f54fa28eb6934d14ab` |
| `origin/main` після `git fetch --prune` | `8b4d52b003f7f827b6a584f54fa28eb6934d14ab` |
| Останній merge у main | `8b4d52b…`, `Merge current Gramlyze source state into main (#654)`, 25.09.2026 22:40:02 +03:00 |
| Початковий local HEAD у вихідному worktree | `41820a2bebdf69004fa7209a2a38457f93efabbd`, гілка `codex/production-ready-a14788dac` |
| Робоча гілка M10 | `codex/seo-m10-production-acceptance` |
| Worktree M10 | окремий чистий worktree від `origin/main`; сторонні видалення, runtime-файли, архіви й зміни вихідного дерева не торкалися |

Production-код, конфігурація, БД, cache і deployment actions не змінювалися. Не використовувалися SSH, production DB/admin, AI endpoints, mail, migrations, seeders, repair/apply/restore, cache clear, workflow dispatch, PR, merge або deploy. Єдиний клас production write — штатні POST власного нового анонімного Questions state у двох ізольованих viewport contexts.

## 2. Час і мережеве середовище

- HTTP acceptance: `2026-09-26T17:53:11Z`–`17:54:14Z` (20:53–20:54 Europe/Kyiv).
- Headed + primary browser run: `17:55:49Z`–`18:00:37Z`; адресний read-only follow-up п'яти selector cases: `18:03:29Z`–`18:03:57Z`.
- Windows, Python 3.12.14 `urllib` з системним CA, proxy вимкнений; curl 8.21.0/Schannel для незалежної redirect-перевірки.
- Playwright із установленим Google Chrome `154.0.8037.57`; viewport 1440×1000 і 390×844; fresh context на кожний сценарій; service workers blocked; звичайний Chrome UA.
- Cookie, Authorization і Referer у HTTP helper не передавалися. Автоматичних retries — 0. Максимальна паралельність production GET — 2.
- Production evidence санітизоване й ignored у `storage/app/seo-m10-production/`; screenshots, cookies, CSRF, session identifiers, answer values, HTML та question bank у Git не входять.

Fresh `http://gramlyze.loc/sitemap.xml` не був доступний: порт 80 не слухав (`curl` status `000`, connect failure приблизно 2,06 s). Shared Apache не запускався і не переналаштовувався заради цього acceptance. Для контексту виконано read-only порівняння з уже прийнятим M9.3 local evidence: 554 → 554, повна ordered equality, added 0, removed 0. Це історичний accepted baseline, а не новий current-main HTTP run.

## 3. Exact SHA status

Статус: **owner-stated but not independently proven**.

- Response headers головної не містять release/revision/commit/version/deploy marker.
- HTML не містить version/revision/commit/release/build meta.
- `/health` відповів `200 application/json`, але публікує тільки `status=ok`.
- Новий endpoint, Git metadata у webroot або SHA у HTML не додавалися.
- Поведінкові контракти збігаються з актуальним main; production manifest byte-identical до актуальної локальної production build, але це не доказ повного Git SHA.

## 4. Server-level redirects

| Запит без Cookie/Referer | Chain | Фінал | Результат |
| --- | --- | --- | --- |
| `http://gramlyze.com/` | `301 → https://gramlyze.com:443/ → 200` | HTTPS non-www; canonical `https://gramlyze.com/` | PARTIAL: без loop, але explicit default port у Location |
| `https://www.gramlyze.com/` | `200` | www лишається у browser URL; canonical non-www | **FAIL:** немає server redirect на primary origin |
| `https://gramlyze.com/` | `200` | `https://gramlyze.com/` | PASS |

Незалежний `curl --max-redirs 0` підтвердив `www=200` без `redirect_url` та HTTP `301` на `https://gramlyze.com:443/`. Іншу мову/home через помилку не відкривало; loop відсутній.

## 5. Основна HTTP-матриця

У private evidence для кожної відповіді є requested path, усі hops, final URL, status, Content-Type, duration, `Content-Length` або фактичні bytes, `X-Robots-Tag`, Cache-Control, Vary, повні title/description/H1/canonical/robots/OG/Twitter та SHA-256 очищеного metadata object. Повний HTML не збережено.

| Path | Chain / status | Type | ms / bytes | Robots | Canonical | Результат |
| --- | --- | --- | ---: | --- | --- | --- |
| `/` | 200 | HTML | 422 / 74,273 | indexable | `/` | PASS |
| `/theory` | 200 | HTML | 516 / 195,560 | indexable | `/theory` | PASS |
| `/theory/future-perfect` | 200 | HTML | 19,016 / 137,552 | indexable | same | PASS; один повільний GET, без performance-висновку |
| `/theory/basic-grammar/sentence-types` | 200 | HTML | 1,016 / 340,275 | indexable | same | PASS |
| `/test/future-perfect/questions` | 200 | HTML | 2,187 / 510,590 | indexable | full Questions URL | PASS; навчальний HTML, 84 questions |
| `/test/future-perfect/questions/questions?mode=saved-test-js-v2` | 200 | JSON | 1,453 / 197,907 | header `noindex, nofollow, noarchive` | none | PASS; sanitized count/shape only |
| `/test/future-perfect/questions/step` | 200 | HTML | 1,672 / 506,967 | meta + header `noindex, follow` | primary Questions | PASS |
| `/courses/english-grammar-theory` | 200 | HTML | 844 / 2,657,004 | indexable | same | PASS |
| `/courses/english-grammar-theory/lesson/basic-grammar/sentence-types` | 200 | HTML | 844 / 1,783,029 | indexable | theory sentence-types | PASS |
| `/theory/…/one-ones` | 200 | HTML | 563 / 102,592 | indexable | same | PASS |
| `/theory/…/reciprocal-pronouns-each-other-one-another` | 200 | HTML | 563 / 107,706 | indexable | same | PASS |
| `/theory/passive-voice/theory-passive-voice-formation-rules` | 200 | HTML | 562 / 182,142 | indexable | same | PASS |
| `/search?q=future+perfect` | 200 | HTML | 406 / 63,996 | meta + header `noindex, follow` | `/search`, без query | PASS |
| `/robots.txt` | 200 | `text/plain` | 266 / 67 | — | — | PASS |
| `/sitemap.xml` | 200 | XML UTF-8 | 2,750 / 80,045 | — | — | PASS |
| `/en/theory/future-perfect` | 302 → 200 | HTML | 1,547 / 137,552 | indexable final | `/theory/future-perfect` | PASS; topic path збережено, locale prefix прибрано |
| `/pl/theory/future-perfect` | 302 → 200 | HTML | 1,140 / 137,552 | indexable final | `/theory/future-perfect` | PASS |
| `/test/future-perfect/questions?seo_m10=1` | 302 → 200 | HTML | 3,843 / 510,590 | indexable final | Questions без query | PASS |
| `/seo-m10-definitely-missing-7de616a670de` | 404 | HTML | 297 / 4,865 | — | none | PASS; no Location/home redirect |

Для 13 indexable HTML rows рівно один непорожній title/description/H1/canonical, HTTPS `gramlyze.com`, query-free canonical, no `noindex`; сирих translation keys або HTML-тегів у metadata не знайдено. Для всіх релевантних 200 HTML OG title/description/url та Twitter title/description непорожні. `X-Robots-Tag noindex` на indexable HTML відсутній.

Locale-query supplement для обох `/en` і `/pl` із `next=https://example.invalid/outside` лишився на `gramlyze.com`, зберіг topic path і query та не використав query як зовнішню ціль. GET state URL `/test/future-perfect/questions/state` дав справжній `404` із `X-Robots-Tag: noindex, nofollow, noarchive`; штатне представлення state існує тільки як POST/204.

## 6. Robots, canonical та noindex

`robots.txt`:

```text
User-agent: *
Disallow:
Sitemap: https://gramlyze.com/sitemap.xml
```

Файл не містить localhost або `.loc` і не блокує основні сторінки. Canonical усіх indexable rows ведуть на HTTPS non-www, course lesson — на theory copy. `/step` коректно має `noindex, follow`, а не `nofollow`. `/search` не створює indexable query-space. JSON не має HTML canonical та має technical noindex. Unknown 404 не має canonical успішної сторінки.

## 7. Sitemap

| Перевірка | Результат |
| --- | --- |
| Production count | **554** |
| Два послідовні GET | 80,045 bytes, однаковий SHA-256 `d4feafd1308939528cf3c0dc9cd1ca6fe22a4051982a7d016b684e01da739b14` |
| Ordered loc hash | `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334` |
| Unique / HTTPS non-www / query-free | PASS / PASS / PASS |
| Forbidden modes | 0 |
| JSON/state endpoints | 0 |
| Course lesson copies | 0 |
| `lastmod` | 0, відповідно до чинної політики |
| Accepted M9.3 local baseline | 554, ordered equality true, added 0, removed 0 |
| Fresh current local | не виконано: `gramlyze.loc:80` недоступний; не підмінено історичним результатом |
| Availability sample | 20/20 HTTP 200, errors 0; home, усі top-level типи, theory/test edges і course URLs у детермінованій вибірці |

554 — фактичний результат, не hardcode. Повного GET crawl 554 URL не було.

## 8. Frontend assets і main fingerprint

На home, theory, Questions і course зібрано фактичні CSS/JS. Browser у кожному з 8 прийнятих rows реально завантажив production CSS, JS і Livewire з HTTP 200. Mixed content, `public/hot`/HMR, Tailwind Play CDN, unpkg Alpine або другий Alpine init відсутні; Alpine 3.17.2 ініціалізувався рівно один раз, `window.Livewire` присутній.

| Manifest entry | Production file | Bytes | SHA-256 | Перевірка |
| --- | --- | ---: | --- | --- |
| `resources/css/app.css` | `assets/app-Byfx_EJW.css` | 153,076 | `0d09899fd2c6d60c4d5431e652cfc666f21b38f7c7d9ac4f108fa7774b9a70b6` | 200, `text/css`, byte-identical local production build |
| `resources/css/catalog-public.css` | `assets/catalog-public-Db1j9xND.css` | 99,904 | `742030afdf5b2ad64c3db0f553e25659403752ed855fd4d681022f167f07508a` | 200, exact source-only main build |
| `resources/js/app.js` | `assets/app-DNxiirP_.js` | 35,320 | `a808a35c964a3f1599dca9663ef161deca27975f35ff26c761ad39a9cfef314d` | 200, exact source-only main build |
| `resources/js/catalog-public.js` | `assets/catalog-public-CzFNw-3Z.js` | 9,531 | `7cdbd9ecbb6dc5b50abb3dc4e4cc740c43dcfc4eb00b8aebb6ac5e08b3670487` | 200, exact source-only main build |

Production manifest — 200 JSON, 623 bytes, SHA-256 `b0e158f7acb62ee7b2f193d7ab450981ee87fd43f05cb11caeb1ddc49e9050a1`; він і всі чотири files byte-identical актуальній локальній production build. Приватна source-only збірка з `origin/main` (`npm ci`, Vite 5.4.19, 55 modules) пройшла. Три assets збіглися byte-for-byte; legacy `app.css` у clean build має інший `app-DiDIcLPd.css` (134,116 bytes), відомий наслідок широкої Tailwind workspace autodiscovery. Це не missing/mixed manifest: production manifest внутрішньо узгоджений і точний локальному production fingerprint, але app CSS не використовується як незалежний доказ revision.

## 9. Вісім browser-сценаріїв

Перший run зберіг перші selector failures. Headed smoke, theory desktop і Questions desktop/mobile були PASS. Course helper помилково вибрав алфавітно перший locked lesson; One/Ones рахував внутрішні службові `.prose` як target boxes; mobile helper повторно шукав toggle за нестабільним accessible-name. Після адресної корекції self-tests повторено, а follow-up виконав тільки п'ять зачеплених read-only cases. Questions POST не повторювалися. Нижче — 8 прийнятих унікальних сценаріїв, а не 13.

| Сценарій | Desktop | Mobile |
| --- | --- | --- |
| Теорія | PASS: 2,940 visible chars, 295 sidebar links, current marker, collapse, theme + reload, overflow 0 | PASS: material/sidebar, theory menu і site menu open/close, theme + reload, overflow 0 |
| Questions | PASS: 84 items; 0→3 answered/correct; resolved 204; reload; server restore | PASS: ті самі інваріанти; fresh context |
| Курс | PASS: перший штатно доступний lesson `parts-of-speech`, course UI, theory canonical, reload | PASS: те саме |
| One/Ones | PASS: 9/9 exact source target blocks, repaired rules/exercises, no literal tags, overflow 0 | PASS: 9/9, вправи нижче першого екрана, overflow 0 |

Headed smoke відкрив реальний Chrome, відобразив Sentence Types і зберіг screenshot до headless automation. У прийнятих rows: page errors 0, console errors/warnings 0, HTTP ≥400 у browser resource traffic 0, unexpected failed requests 0, blocked requests 0, Google Fonts failures 0. У Questions raw `ERR_ABORTED` state requests збережені (6 desktop, 8 mobile) і прийняті тільки разом із resolved fetch 204 та незалежним server-only restore.

## 10. Questions persistence proof

Для кожного viewport створено окремий fresh context без storageState. До відповіді: 84 items, answered 0, correct 0. Після трьох відповідей: answered 3, correct 3; усі instrumented save fetch завершилися 204. Санітизований state hash після save, reload, server bootstrap і restored UI однаковий:

`e05dddbacf16004695c5f4736914fb461b50101802bbdb181dd1a686629826c0`

Helper видалив лише один фактично наявний власний Questions snapshot key із local/session storage, підтвердив збереження unrelated storage, cookies не видаляв, після чого новий GET відновив ті самі counters/position/state hash із server bootstrap. Cookies, CSRF, storage keys і answer values у evidence/report не записані. AI hint/explain не натискалися.

## 11. Контентні spot-check M1/M8/M8.1

- Passive Voice: 200, нормальний H1 і навчальний матеріал; `Page Folder Unseed Targets Debug` та `Page_V3 folder unseed block` відсутні.
- One/Ones: 9/9 exact target bodies поточного main source; coffee/water, possessive і some/any + adjective + ones правила присутні; `a cold some` і буквальні `<strong>`/`<span>` у visible text відсутні; desktop/mobile вправи видимі.
- Reciprocal Pronouns: 10/10 exact target bodies поточного main source; відновлені пояснювальні блоки присутні.

Це перевірка відображеного production HTML проти поточних source definitions, не доказ повної DB-ідентичності. Production DB не читалася й не змінювалася.

## 12. Console/network та diagnostics

- Python self-tests: **8/8 PASS**.
- Node self-tests: **7/7 PASS**.
- Headed smoke: PASS; accepted browser scenarios: **8/8**.
- Source-only frontend build: PASS.
- HTTP: 19 matrix paths; 3 server redirect seeds; 2 sitemap stability GET; 20/20 sitemap availability; 4/4 manifest assets; 3/3 content spot-check pages.
- Нормальні production GET не мали автоматичних retries. Один ad-hoc DOM probe отримав `ERR_ABORTED`; рівно один явний network-category repeat завершився успішно й використаний лише для класифікації selector false positive.

## 13. Що не перевірено

- Фактичний deployed Git SHA через незалежний marker.
- Fresh local current-main HTTP sitemap, бо `gramlyze.loc:80` не працював.
- Production DB, повна ідентичність усіх навчальних таблиць, SSH/server config/admin.
- Повний crawl 554 URL, усі browser/OS комбінації, історичні реальні user sessions.
- Google reindexing, coverage/positions, analytics або Search Console.
- Загальна performance campaign, Lighthouse, LCP/CLS A/B; одиничний 19 s GET не оголошується regression.
- PR, merge, workflow dispatch і deploy.

## 14. Підтверджений дефект і адресне наступне виправлення

**Severity: Medium (SEO origin consolidation), не application-indexing blocker.** `https://www.gramlyze.com/` віддає 200 замість redirect; HTTP Location містить зайвий explicit `:443`. Мінімальна окрема зміна — у чинному CDN/Nginx/Apache ingress, не в навчальному runtime: встановити один permanent redirect для `www` на `https://gramlyze.com$request_uri`, а HTTP redirect формувати без `:443`; зберігати path і query. Після окремого дозволу перевірити три початкові URL, один topic path із query, відсутність loop і canonical. У M10 server config не змінювався й deploy не виконувався.

Інших підтверджених production-дефектів у погодженій вибірці немає. Відсутність помилок у вибірці не доводить відсутність будь-яких production-дефектів і не означає, що Google уже переіндексував усі URL.

## 15. Git-склад M10

До commit призначені лише:

- `tools/diagnostics/seo-m10-production-http.py`;
- `tools/diagnostics/seo-m10-production-browser.cjs`;
- `tests/diagnostics/test_seo_m10_production_http.py`;
- `tests/Browser/seo-m10-production-browser.test.cjs`;
- `.gitignore` — private evidence/source-build та diagnostic `__pycache__` rules;
- цей звіт.

Runtime-код, production/config/server files, DB/seeders/definitions, `.env`, vendor, `public/build`, screenshots та private evidence не входять до commit.

Production-приймання виконано через гостьові HTTP та Playwright-сценарії. Production-код, конфігурація і навчальна БД не змінювалися; виконано лише штатний анонімний запис власного тимчасового прогресу Questions. Commit і push містять діагностичні інструменти, тести та звіт. PR, merge і деплой не виконувалися.
