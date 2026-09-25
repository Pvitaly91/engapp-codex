# M3.2 — локальне mobile/performance-приймання

## Короткий підсумок

| Залишок M3.1 | Фактичний результат | Статус |
|---|---|---|
| Mobile cold LCP | 40/40 валідних A/B; парні median ST 0 ms, PP +30 ms; широкі CI | Причинність application regression не встановлена; runtime не змінено |
| Recent-input без UI дій | Повторено в Chromium 147 і Chrome 152; viewport змінюється, реальні shifts виключаються стандартом | Browser mechanism допускає viewport notification, але точне походження кожного timestamp не доведено |
| Історичний sidebar loading/error +84 px | 14 поточних сценаріїв не відтворили; знайдено й виправлено новий false positive probe | Історичний одиничний випадок, причина не встановлена |

Підтверджені й виправлені помилки **інструментів**, не застосунку:
класифікація mutation snapshot як rendered frame; неправильна вимога GET
`/state` в новому UI smoke; захист діагностичного error output, loopback bind
і нового SQLite destination. Навчальний контент, runtime-код, `.env`,
прогрес-контракт і production не змінені.

## Наперед зафіксований план (до вимірювань)

Дата початку: 8 вересня 2026. Робоча база:
`54d34083a67e30084b967b572cc5a6b93c2eaf99`, гілка
`codex/seo-m3-2-mobile-performance`. Після fetch: origin/main
`c77b4326a92b2c1e92c80b07393d8e7000c0fe33`, origin M3.1 = робоча база.
M1/M2/M3/M3.1 підтверджені як предки. Початкові сторонні PPC-аудити,
`.codex/`, архіви та приватні діагностичні матеріали збережені.

Це три залишкові перевірки M3.1, не загальний SEO-аудит.
Production-домени блокуються до навігації. Робочі `.env`, серверна
конфігурація, навчальна БД, кеші та історичні результати не перебудовуються.

### Дизайн та правила включення

- A: M3 `7c649117e747fb922da166c2e1fba59474fee203`.
- B: M3.1 `54d34083a67e30084b967b572cc5a6b93c2eaf99`.
- Ізольовані loopback-копії, однакові PHP/SAPI, залежності й незмінні
  навчальні дані, окремі runtime та assets; це не Apache `.loc`.
- Дві сторінки: `/theory/basic-grammar/sentence-types` та
  `/theory/tenses/present-perfect/present-perfect-forms`.
- Рівно 10 cold-пар на сторінку (40 навігацій). Для непарних пар AB,
  парних BA; сторінки чергуються всередині номера пари. Після перегляду
  результатів кількість не змінюється, невдалі спроби не замінюються.
- Chromium 147 (наявний Playwright headless shell), 390×844, mobile/touch,
  uk-UA, light, новий гостьовий context на кожну навігацію; HTTP cache
  дозволений, але cold context не успадковує кеш/сесію попереднього.
- Чинні observer/reducer/verifier розширюються, стандартний CLS не змінюється.
  Фіксоване вікно 0–12000 ms, готовність за 2000 ms до його завершення.
  Немає throttling, стабілізації декорацій, скриншотів або UI-дій у цьому вікні.
- Жодних паралельних build/test/performance процесів під час вимірювання.
- Кожна спроба зберігається. Валідність: HTTP 200, коректний завершений
  observer, видимий документ, один Alpine, успішні необхідні ресурси,
  без page/guard errors, iframe і неочікуваних network failures.
  Font CSS та завантажені binaries перевіряються за SHA-256 і фактично
  використаними platform fonts; статус FontFaceSet сам по собі недостатній.
- Для оцінки впливу включаються всі валідні пари, а всі одиничні валідні
  вимірювання та причини неповних пар також публікуються. Парні B−A:
  медіана ms та %, min/max, IQR і детермінований bootstrap 95% CI
  (10000 ресемплів пар, малий n явно зазначається).

### Обмежені додаткові сценарії

Перед A/B — поточний `.loc`: по одній cold mobile навігації двох сторінок
та `/test/future-perfect/questions`, одна desktop Sentence Types як контроль.
Траса містить LCP candidates, TTFB, CSS/font timing і bytes, platform fonts,
long tasks, layout/style counters, readiness, observer overhead, безпечну
часову шкалу input/viewport/focus/visibility/navigation та automation.
Якщо recent-input без input повторюється, окрема проба на вже встановленому
Chrome 152: ті самі три mobile URL по одному разу; не включати її до A/B.

Sidebar: рівно 10 нових desktop guests Sentence Types і по одному окремому
сценарію delayed Alpine, delayed AJAX, saved collapsed, saved expanded.
До першої спроби — hashes отриманого HTML/CSS/source/manifest та вузькі
санітизовані атрибути/геометрія. Жодного очищення caches/OPcache.

Додатковий performance-цикл допускається лише після виправлення
підтвердженого дефекту, не для добору сприятливих цифр.

Уточнення provisioning до будь-якого HTTP на стендах: по одному GET кожної
з двох сторінок на A та B (4 GET), щоб підтвердити 200, навчальний контент,
assets, фактичні PHP/SAPI/read-only headers. Це не performance-вимірювання;
компіляція views і серверні кеші обох стендів проходять однаковий прогрів.
Cold у парній серії означає холодний браузерний context, а не перший запуск PHP.
Якщо provisioning не проходить, серія не починається до виправлення саме
ізоляційного wrapper або фіксації неможливості безпечного стенда.

## Результати

### Початкова траса поточного Apache `.loc`

`storage/app/seo-m3-2-local/m32-current-trace.json`: рівно 4 навігації,
усі завершені. Це не старий M3 baseline і не парний експеримент.

| Сторінка / viewport | TTFB ms | FCP ms | LCP ms | Standard CLS | Raw shifts / recent-input |
|---|---:|---:|---:|---:|---:|
| Sentence Types / mobile | 4188.3 | 4756 | 4892 | 0 | 3 / 3 |
| Present Perfect / mobile | 589.5 | 836 | 836 | 0 | 3 / 3 |
| Questions / mobile | 3808.4 | 4140 | 4140 | 0 | 1 / 1 |
| Sentence Types / desktop | 1069.1 | 1624 | 1624 | 0.01377638 | 2 / 0 |

Sentence Types mobile мав два LCP candidates: brand P (4756 ms), потім
вступний DIV (4892 ms). Present Perfect — той самий вступний DIV, Questions —
H1. Traces містять start/end запитів CSS/font, long tasks і CDP layout/style
counters. Google CSS та всі три font binaries фактично повернули 200;
body SHA-256/розміри перевірені, не виведені зі стану `document.fonts`.
Сімейства остаточних glyphs: Manrope у вступах, Archivo в Questions H1.
Це CDP-вибірка **після 12 s**, не ретроспективний доказ шрифту кожного
попереднього paint. `fonts.ready` і близькість timestamps не доводять причину LCP.

Наприклад, Present Perfect mobile: public CSS request 602.8 → end 609.4 ms,
Google CSS 602.6 → 709.8 ms; запити font binaries почалися 731.3–731.8 ms,
network completion 923.7–951.6 ms. LCP=836 ms передує цьому completion.
Тому кінцевий Manrope не можна автоматично приписати початковому paint.
Request start + initiator — найраніша зафіксована мережева ознака discovery,
не окремий точний timestamp parser discovery. CDP response event може
доставлятися пізніше за network completion; для end використано саме
`loadingFinished.timestamp`, а не час отримання повідомлення інструментом.

Власні витрати inspect observer за повне вікно: 75.8 / 32.1 / 305.0 /
133.2 ms відповідно; install: 21.9 / 0.5 / 1.8 / 0.5 ms. Це частковий облік
callback/geometry роботи, включно з можливими синхронними layout reads,
не повна ціна instrumentation. Його не віднімаємо від LCP. Симетрична
інструментація не гарантує однакової ціни для різних DOM/стилів.
`LCP−TTFB` також не називається чистим render delay.

Додаткові лічильники цієї самої початкової траси (не окремі runs):

| Сторінка / viewport | Layout count / ms | Style recalc count / ms | Long tasks count / total / max ms | Alpine initialized / sidebar ready ms |
|---|---:|---:|---:|---:|
| ST mobile | 14 / 200.93 | 17 / 66.57 | 7 / 681 / 168 | 5222 / 5613.2 |
| PP mobile | 11 / 97.11 | 13 / 11.23 | 3 / 207 / 80 | 875.2 / 1109.8 |
| Questions mobile | 12 / 299.02 | 9 / 43.39 | 5 / 630 / 277 | 4628.5 / не застосовується |
| ST desktop | 12 / 201.80 | 226 / 86.50 | 5 / 452 / 176 | 1645.4 / 2013.3 |

CDP durations конвертовані із секунд у ms, накопичені за спостереження,
а не тільки до LCP. Long tasks та observer costs можуть перекриватися;
їх не можна додавати як незалежні складові LCP. У mobile theory
`sidebarReady` означає готовність CSS-прихованого desktop loader, не
відкриття меню користувачем. Ці counters не встановлюють причинність diff.

У mobile до parsing viewport meta: 980×2121, після — 390×844, scale 1.
Window/visual viewport resize зафіксовано на 4622.4 / 777.5 / 4088.1 ms;
pointer/touch/key/click/wheel подій немає. На desktop resize немає.
Present Perfect має реальний рух тексту −30, потім +28 px, хоча CLS=0.
Пізніші траси доповнені `lastInputTime`; цей початковий raw-файл не переписано.

У [коді Chromium 147.0.7727.15](https://chromium.googlesource.com/chromium/src/+/refs/tags/147.0.7727.15/third_party/blink/renderer/core/layout/layout_shift_tracker.cc)
viewport-size notification оновлює timestamp виключення; перевірка recent-input
використовує таймер або час обробки поточного task залежно від runtime feature.
Отже, прапорець не є доказом прихованого кліку. Це підтверджений механізм
реалізації браузера, але точна атрибуція кожного історичного shift без його
`lastInputTime` лишається обмеженою. Виправляти або підмінювати browser flag не можна.

### Межі аналізу коду

У diff M3 → M3.1 не змінено viewport meta, URL/preconnect/family шрифтів
або навчальний вступ. Desktop height/collapsed-width правила обмежені
1024/1280 px. Mobile menu у cold-сценарії закритий. Можливі невеликі витрати:
нове раннє читання localStorage/dataset write, додаткова Alpine обробка
CSS-прихованого desktop loader та parsing додаткових bytes. Це кандидати,
а не встановлена причина історичних +112 ms. Застосунок за цією гіпотезою
не переписується.

### Ізольоване A/B: середовище та незмінність

Вимірювання: 2026-09-08 17:08:45.994–17:16:56.236 UTC. Рівно 40 спроб,
40 валідних, 20 повних пар; виключених або замінених спроб немає.
Чинний comparison verifier повторно обчислив усі 40 записів: PASS.
Оригінальний `m32-paired-natural.json` не перезаписувався; фіналізація
`m32-paired-final.json` лише додає доказ після зупинки серверів та явне
підтвердження координатором відсутності паралельної важкої роботи.
Це координаційний контроль, не журнал усіх процесів Windows.

| Параметр | A | B |
|---|---|---|
| Архів коду | M3 `7c649117…` | M3.1 `54d34083…` |
| Loopback | `http://127.0.0.1:57415` | `http://127.0.0.1:55666` |
| PHP / фактичний SAPI | 8.2.12 / cli-server | 8.2.12 / cli-server |
| Runtime | APP_DEBUG=false; приватні sessions/views/logs/build | така сама політика, окремі каталоги |
| Public CSS | `catalog-public-BCzmTLrD.css`, 96783 bytes | `catalog-public-DkOvOqwd.css`, 96944 bytes |
| Public JS | `catalog-public-CzFNw-3Z.js`, 9531 bytes | ідентичний |

Chromium: HeadlessChrome **147.0.7727.15**, revision
`@6b5a1b80ccc1e8a4967901d8e58fc2e162cdf050`, JS 14.7.173.6,
Playwright 1.62.1, Node 22.15.0. Windows, 390×844, без throttling.
Залежності скопійовані побайтово однаково, install/update не виконувався:

- vendor SHA-256: `37a746529718ba40b7db54be56578b211a9eff437c8312ace0a51704cada5fff`;
- node_modules: `f2995beb09ead2e8608162fbd98cf7ec1f0d01bfdc56d508b08c9602e4ef1d71`;
- runtime policy: `eb55459ab1bdc40b27415d043040a0f8029efe476b706bb4ae347bd9d2f09f77`;
- A CSS: `49c2eb770e13fed37cd4515b8506d7857267fa658e7709f399ea9b0cd1bf89e0`;
- B CSS: `58b449fa028e356005e026c71c65358be8817704196c9517d30456579be9c92a`;
- спільний JS: `7cdbd9ecbb6dc5b50abb3dc4e4cc740c43dcfc4eb00b8aebb6ac5e08b3670487`.

Дані: один read-only consistent snapshot 24 дозволених навчальних/навігаційних
таблиць локального MySQL, без users/auth/sessions/deploy-даних. 254 pages,
44460 questions. Обидві приватні SQLite-копії по 163512320 bytes, SHA-256
`7ef27f3d6b13fe5a5ee0e3e31740d98e327251d6450a71d56a13493ff40cb506`.
SQLite відкривається `mode=ro`, `PRAGMA query_only=ON` до providers;
контрольний CREATE відхилений. HTTP-процеси не отримують робочих DB credentials.
Дозволені лише потрібні GET; зовнішні серверні HTTP-запити заборонені.
Однаковий response-only wrapper зберігає loopback port у URL, який локальний
LocaleService інакше відкидає; архівні application sources не редагувалися.

Це **не** Apache/MySQL вимірювання `.loc`: у стендах cli-server/SQLite та
APP_DEBUG=false, у головному локальному сайті Apache і його чинна конфігурація.
Стенди потрібні для симетричного порівняння версій, не оцінки production.
Чотири provisioning GET пройшли до браузерної серії; server views були
симетрично прогріті. Холодним був кожний браузерний context.

Перед/після всього PHP lifecycle перевірено **46735 захищених файлів**;
aggregate SHA однаковий:
`4fdd2df2c3e5d448f008b8523a1bef9400e3759edd5d299d84e6b27628a51efb`.
Після завершення обидва PHP-процеси зупинені, SQLite hashes не змінилися.
`after-serve-safety.json`, 17:18:06 UTC, SHA-256
`592ef065d2612ecce3c218c3f9206f03db9eda15b01b2dfe3de9e3191af21aa4`.
Working `.env`, snapshots, PPC-аудити та попередні приватні артефакти
не відновлювалися поверх побічних ефектів — вони не змінювалися.

Три невдалі підготовчі спроби збережені, **не** включені як performance runs:
Git safe-directory у санітизованому child env; Windows MAX_PATH при extraction;
відсутній Request під час CLI HTTP-kernel preflight. Виправлені відповідно
per-command safe-directory, extended-length paths та bound local Request.
Перед першим браузером також уточнено однаковий allowlist `livewire.min.js`
для APP_DEBUG=false. Усі підготовчі помилки пройшли той самий файловий guard.

### Усі валідні LCP-пари

ST = Sentence Types; PP = Present Perfect. Delta = B−A; відсоток = 100×delta/A.
Сторінки чергувалися всередині номера пари, не окремими фазами.

| Пара | Порядок | ST A ms | ST B ms | ST delta ms / % | PP A ms | PP B ms | PP delta ms / % |
|---:|---|---:|---:|---:|---:|---:|---:|
| 1 | AB | 4288 | 1508 | −2780 / −64.83 | 1196 | 984 | −212 / −17.73 |
| 2 | BA | 1456 | 1540 | +84 / +5.77 | 980 | 1516 | +536 / +54.69 |
| 3 | AB | 4192 | 1404 | −2788 / −66.51 | 932 | 888 | −44 / −4.72 |
| 4 | BA | 1524 | 1468 | −56 / −3.67 | 1156 | 996 | −160 / −13.84 |
| 5 | AB | 1460 | 1572 | +112 / +7.67 | 1092 | 1048 | −44 / −4.03 |
| 6 | BA | 1368 | 1460 | +92 / +6.73 | 1180 | 992 | −188 / −15.93 |
| 7 | AB | 1600 | 1656 | +56 / +3.50 | 1016 | 1120 | +104 / +10.24 |
| 8 | BA | 1660 | 1584 | −76 / −4.58 | 1004 | 1220 | +216 / +21.51 |
| 9 | AB | 1572 | 1444 | −128 / −8.14 | 1048 | 1260 | +212 / +20.23 |
| 10 | BA | 1636 | 1720 | +84 / +5.13 | 988 | 1220 | +232 / +23.48 |

| Оцінка | ST | PP |
|---|---:|---:|
| A median [min,max], IQR ms | 1586 [1368,4288], 178 | 1032 [932,1196], 148 |
| B median [min,max], IQR ms | 1524 [1404,1720], 119 | 1084 [888,1516], 227 |
| **Median парної delta ms** | **0** | **+30** |
| Delta min/max; IQR ms | −2788…+112; 199 | −212…+536; 346 |
| Bootstrap 95% CI median delta ms | −1428…+88 | −160…+222 |
| **Median парної delta %** | **−0.09%** | **+3.10%** |
| Delta min/max; IQR процентних пунктів | −66.51…+7.67; 12.86 | −17.73…+54.69; 32.75 |
| Bootstrap 95% CI median delta % | −34.71…+5.93 | −13.84…+21.86 |

Bootstrap: 10000 ресемплів **цілих пар**, seed 32032, percentile CI з лінійною
інтерполяцією квантилів. n=10 малий; оцінка припускає обмінність/незалежність
пар, яку AB/BA не гарантує. Median відсотків не дорівнює відсотку від median
ms; різниця двох загальних медіан також не є median парних різниць.
CI, який містить нуль, **не доводить еквівалентність чи відсутність регресії**.

ST: початкові +28 ms не відтворилися в парній точковій оцінці, але CI широкий.
PP: початкова величина +112 ms не повторилася як median (+30 ms), проте
залишається всередині CI. **Даних недостатньо для причинного висновку**
про вплив M3.1 на PP. Жодна сторінка не має встановленого механізму
application regression; runtime-правок або повторних серій «до зеленого» немає.

Два повільні ST A — різні мережеві шляхи, обидва залишені в статистиці:

- Пара 1: FCP=LCP 4288, TTFB 1110.3 ms. Google CSS request 1125.7 → 4149.7
  (3024 ms; CDP proxy phase 2709.2 ms), тоді як власний CSS завершився 1173.1.
  У B Google CSS 230.7 ms, end 1400.3, LCP 1508. Тут пов'язана траса показує
  затримку зовнішнього stylesheet path, а не доводить, що M3.1 змінив шрифти.
- Пара 3: FCP=LCP 4192, TTFB 3870.3, LCP−TTFB 321.7 ms. Document
  sendEnd→receiveHeadersStart ≈3866.9 ms; Google CSS лише 151.5 ms.
  Переважає очікування HTML, але внутрішня причина backend wait не встановлена.

Binaries шрифтів у цих двох A завершилися **після** LCP (4529–4558 та
4315–4333 ms). Приписувати обидва outliers font binary loading було б помилкою.

### Успішні шрифти: побайтова тотожність

У всіх 40 навігаціях однаковий набір фактичних HTTP 200 bodies:

| Ресурс | Bytes | SHA-256 |
|---|---:|---|
| Google Fonts CSS | 14770 | `0b0f6cb76ea81381449d3ba56a8a6ecbcac4267a44a097c67d7e8d211014e36a` |
| Archivo v25, `k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLydOxI.woff2` | 34928 | `8f704806dbedeaaeca334b11ec348bc3ac3a439d6431544b3afb54f534ee4967` |
| Manrope v20, `xn7gYHE41ni1AdIRggOxSuXd.woff2` | 14500 | `c268b459a9329e59fecf39a17618efd44c71735532048d60b12aab76a8c14914` |
| Manrope v20, `xn7gYHE41ni1AdIRggexSg.woff2` | 24836 | `a30ddcd349703aff7464c34bef3fffdff405ee50c113440d7c8693c02d210972` |

CDP після cutoff: `Manrope ExtraLight`, custom=true, PostScript
`Manrope-ExtraLight-Bold` + `Manrope-ExtraLight`; ST 18+119 glyphs,
PP 49+92. У кожній сторінці набір однаковий A/B. Назва family взята з
бінарного шрифту, це не висновок про фактичну CSS font-weight ExtraLight.
Окрема проба з локально підставленими fonts не проводилася; природну мережу
не змішано із self-hosted перехопленням.

### Recent-input та фізичні зсуви

У парній серії **standard CLS=0 в усіх 40**, але є **67 raw shifts**, усі
виключені browser recent-input flag. ST A/B: 18/16; PP A/B: 16/17.
У PP окремий raw value до 0.04126182, raw сума одного run до 0.05264458.
Це **не standard CLS**. Є рух текстового source y420.375→390.375 (−30 px),
потім 390.375→418.375 (+28 px); не підміняємо його твердженням про виміряну
висоту батьківського DIV. Нуль CLS не означає нерухомий контент.

У всіх 40 initial viewport 980→390; pointer/touch/key/click/wheel подій немає,
automation містить лише створення context/page, CDP guard, init observer,
goto і очікування cutoff. Жодних tap/click/scrollIntoView до cutoff.
`lastInputTime − останній resize`: min −0.3, median 70.75, max 135.9 ms.
`shift − lastInputTime`: 60.1…488.9 ms, median 193.6. Прапорці узгоджені
з 500 ms recency, але browser timestamp **не** дорівнює DOM resize timestamp
у більшості випадків. Без внутрішньої Blink call trace це не точна атрибуція
кожного повідомлення viewport; причинність конкретного flag лишається обмеженою.

Окрема запланована `.loc` проба вже встановленого Chrome **152.0.7977.76**,
revision `@0d89dfa2dd7c1ec4b8a14b9f303f887bb63b6174`, JS 15.2.124.19:

| Mobile сторінка | LCP ms | Standard CLS | Raw / recent-input | Resize / lastInputTime ms |
|---|---:|---:|---:|---:|
| Sentence Types | 1540 | 0 | 1 / 1 | 1348.4 / 1460.7 |
| Present Perfect | 1180 | 0 | 2 / 2 | 1124.6 / 1270.9 |
| Questions | 2868 | 0 | 2 / 2 | 2843.7 / 2920.2 |

Усі три complete, без ручних input-подій, з тим самим 980→390 viewport.
Це окрема діагностична проба, **не** другий baseline і не частина A/B LCP.
Поведінка повторюється у двох версіях; називати її доведеною помилкою
Chromium або прихованим кліком неправильно. CLS reducer/flag не змінено.

### Sidebar і фінальне функціональне приймання

Рівно **10 нових desktop guests Sentence Types + 4 окремі сценарії**:
delayed Alpine, delayed AJAX, saved collapsed, saved expanded. Останні два
мають додаткове початкове завантаження для встановлення стану: загалом
16 спостережень, але не 16 незалежних запланованих кейсів. Chromium 147;
вікно 8 s; це fault/geometry, не нова LCP-серія. Кеші/OPcache не очищалися.

Усі 16 HTML responses містять однаковий санітизований state contract:
2 error blocks з x-cloak, 2 gated content blocks, early collapsed marker.
State markup SHA-256:
`f4c10bf9ca07df104985f0899280e39e554db8c157920cfcfc825c45162de610`.
Повні HTML hashes різняться між гостями через динамічні bytes, тому не
вимагався хибний byte-match сесійного HTML; кожний hash збережений приватно.
Усі 16 фактичних CSS responses — HTTP 200, SHA `58b449fa…`, як на диску.
21 source/manifest inventory узгоджені, включно з початковим probe hash.

**Початковий новий probe видав FAIL у 14/14 лише за visibility assertion.**
Жоден результат не вилучено. Причина встановлена на raw timeline:

- Run 1: FCP 1496 ms; rAF на 1766 ms показує тільки loading.
- Mutation snapshot на 1766 ms примусово читає геометрію між Alpine
  show/hide callbacks: loading 84 px та content y353.5, error display:none.
- Наступний mutation 1779.3 ms: loading схований, content y269.5.
  Наступний rAF 1805.6 ms: тільки content. Відповідного content LayoutShift немає.

`vendor/livewire/livewire/dist/livewire.js` show/hide використовує rAF та
Promise microtasks. Новий синхронний MutationObserver snapshot бачить
проміжну геометрію між ними. `timestamp >= FCP` не перетворює кожен такий
DOM-знімок на намальований кадр. Це loading/**content**, не історичний
loading/**error** дефект. Observer також може сам спричиняти layout reads;
його transient geometry не можна оголосити видимим переміщенням користувача.

У `theory-sidebar-stability.cjs` введено явні capture phases. Усі mutation,
event та unknown snapshots збережені й підраховані окремо. Gate продовжує
відхиляти rAF overlap, невідому фазу або реальний LayoutShift, що відповідає
спостереженому content box. Це вузька перевірка цього content box, не
універсальний детектор руху будь-якого descendant sidebar.

Offline reassessment **тих самих** п'яти raw-файлів:
`m32-sidebar-phase-review-sidebar-reassessment.json` — **14/14 PASS**, також
2/2 setup observations. 301 інша перевірка збережена без зміни результату.
Усі 16 overlaps — mutation-only loading/content; error overlap = 0,
rAF overlap = 0, unknown/event overlap = 0, matching content shifts = 0.
Оригінальні файли, їх FAIL verdicts та hashes залишилися незмінними.
Повторних браузерних серій для отримання сприятливого результату не було.

Важливо: новий classifier перевірено на **збереженому історичному** M3.1
випадку. Він **досі FAIL**: 4 rAF overlap frames і реальні content shifts
5132.5 ms / 0.0128349132 та 5300.9 ms / 0.0108824074. Регресії також
зберігають відмову для справжнього 84 px зсуву незалежно від recent-input.
Отже, історичний доказ не переписано й не приховано новою класифікацією.
Його причина, зокрема гіпотеза stale cache, **не встановлена**.

### Основне приймання `.loc`

З готовими Vite assets, без HMR, перевірено desktop 1440×1000 та mobile
390×844 для всіх трьох URL:

- [Sentence Types](http://gramlyze.loc/theory/basic-grammar/sentence-types);
- [Present Perfect](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-forms);
- [Questions](http://gramlyze.loc/test/future-perfect/questions).

Теорія: **4/4 кейси, 16/16 груп перевірок PASS**. Один H1, навчальний HTML,
таблиці ST 2/30 cells, PP 1/18 cells, форматування, немає видимих raw HTML
tags чи horizontal overflow. 295 навігаційних посилань, активна сторінка,
scroll, desktop collapse/expand, mobile open/close без залишеної висоти,
пошук/очищення, перемикання теми та її збереження після reload працюють.
Збережено й візуально перевірено desktop/mobile screenshots.

Перші Questions desktop/mobile кейси помилково очікували GET `/state`.
Уже тоді saved/restored hashes та counters збіглися, але smoke verdict був
FAIL через неправильне припущення нового інструмента. Код показав:
`routes/web.php:160` має **POST** state, `GrammarTestController::storeSavedTestJsState`
зберігає гостьову сесію; `TestJsV2Controller::renderSavedTestShell` включає
saved state в document через `saved-test-js-persistence.blade.php`.
`getSavedState()` читає цей bootstrap. Застосунок не потребує GET `/state`.

Після адресної правки smoke виконано **тільки 2 Questions follow-up**, не
повторну теорію чи performance. **2/2 кейси, 8/8 груп перевірок PASS**:
свіжі гості, 84 питання, одна правильна відповідь, вихід на `/theory`,
очищення local/session storage, збереження cookie лише в пам'яті, новий
GET документа 200. Порівняно три hashes: очікуваного стану, server JSON
з HTTP body та відновленого UI — збігаються, порядок і counters 1/1 збережені.
HTTP body/CSRF/відповіді не записувалися; лише hashes і counters.
Початкові два FAIL лишилися в окремому evidence, а не виправлені заднім числом.

Один запит на виконання follow-up був зупинений auto-review через можливий
session write. До повтору read-only перевірено явну вимогу користувача
server-only приймання, POST handler і `SESSION_DRIVER=file`: йдеться про
дві **нові тестові гостьові сесії**, не редагування навчальної БД чи чужого
прогресу. Після надання цих доказів той самий bounded command дозволено.

У фінальних успішних UI кейсах один Alpine **3.15.11** + Livewire,
зібрані CSS/JS та Google Fonts відповідають 200; console errors і
production attempts відсутні. Для Questions збережено 3 відомі
POST204/ERR_ABORTED пари на viewport; це не замінює доказ відновлення,
який отриманий окремо через server bootstrap. Інші network failures не
виключаються широким правилом. Скриншоти Questions зроблено до вибору відповіді.

Фінальна незалежна перевірка protected files після всіх UI:
`checked-safety.json`, 17:36:20 UTC, SHA-256
`03780e31299795bc5bcb0fc4be0d4d1fa77d85105420d841f8de7938bfb6df00`.
46735/46735 незмінні, обидві frozen БД незмінні; нових PHP-процесів для
цього читання не запускали.

## Зміни та автоматичні перевірки

Application diff у `app/resources/routes/config/bootstrap` — **відсутній**.
Змінені тільки діагностика, її тести, цей звіт та вузький `.gitignore`
для `/storage/app/seo-m3-2-local/`.

- `public-layout-shifts.cjs`: input/viewport/lifecycle trace, lastInputTime,
  LCP candidates, частковий observer cost, CDP metrics, font body hashes
  та used fonts; чинний стандарт CLS не змінений.
- `mobile-performance-m32.cjs`, `verify-layout-comparison.cjs`: зафіксований
  interleaved план, перевірка реального PHP/SAPI/read-only response, byte parity,
  парний bootstrap, збереження невдалих/непарних результатів, finalization.
- `m32-isolated-stands.*`: власні каталоги, read-only snapshot, незмінні
  залежності, захищений PHP lifecycle. Після A/B додано defensive validation
  loopback **до Popen** і відхилення dangling destination symlink.
  Ці дві правки не змінювали вже виміряні router/assets. Symlink regression
  перевіряє source/order contract; новий PHP export для неї не запускався.
- `theory-sidebar-stability.cjs`: response/source identity, narrow state
  attrs, capture-phase correction та окреме read-only reassessment.
- `m32-local-acceptance.cjs`: вузькі функціональні перевірки й справжній
  inline server-bootstrap contract. Timeout може містити приватний DOM preview,
  тому в error output лишаються лише allowlisted error type та message hash,
  а counters приймаються лише як nonnegative safe integers; додано synthetic
  privacy regressions. Останні privacy guards перевірені unit tests, без
  повторного браузерного циклу, бо успішний UI шлях не змінювався.

| Перевірка | Результат |
|---|---|
| `npm run build` | PASS, 55 modules; public entry hashes підтверджені; HMR не використовувався |
| `npm test` | PASS, 30 tests / 5 files |
| Node diagnostic/persistence regressions | PASS, 78/78, підсумковий запуск після privacy guards, 4.19 s |
| Python diagnostic regressions | PASS, 18/18: M3.1 guard 6 + stand tool 12 |
| PHP export + обидва isolated preflights | Виконано успішно під full-file guard; CREATE write відхилений read-only |
| Provisioning | PASS 4/4 GET |
| Isolated paired verifier | PASS 40/40; 20 повних пар, 0 invalid |
| Sidebar | Original 14 false positives збережені; offline phase reassessment PASS 14/14 + 2 setup |
| Фінальне `.loc` UI | PASS 16 theory + 8 Questions grouped checks; 2 початкові tool failures збережені |
| Protected-file proof | PASS 46735/46735, обидві frozen SQLite unchanged |

Build мав попередження про застарілі Browserslist data; залежності не
оновлювалися. Повну 205-case PHP matrix і старі 22 smoke failures не
перезапускали: application PHP/templates/middleware не змінювалися.
Production-profile SEO повторно не запускався з тієї самої причини.
Останні defensive PHP exporter bytes перевірені source-contract тестом;
новий clean-room export/prepare після кожної правки не заявляється:
успішний lifecycle використовував валідований resume збереженого stand.

## Команди та приватні докази

Команди виконувалися послідовно, окрім незалежних фінальних unit tests.
Наведені labels вже існують; інструменти забороняють перезапис evidence.
Шляхи нижче від кореня репозиторію, без секретів:

```powershell
git fetch origin
git switch -c codex/seo-m3-2-mobile-performance
npm run build
npm test

$P = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
$S = 'storage/app/seo-m3-2-local/stands-10ca00e639bc499b8e2b1ba51bab7811'
$env:PLAYWRIGHT_MODULE = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'
$env:CHROMIUM_EXECUTABLE = 'C:/Users/admin/AppData/Local/ms-playwright/chromium_headless_shell-1217/chrome-headless-shell-win64/chrome-headless-shell.exe'
node tools/diagnostics/mobile-performance-m32.cjs plan m32-frozen-plan
node tools/diagnostics/mobile-performance-m32.cjs current m32-current-trace
& $P -B tools/diagnostics/m32-isolated-stands.py prepare --resume $S
& $P -B tools/diagnostics/m32-isolated-stands.py serve --manifest "$S/manifest.json"
# serve працював у власному background supervisor, не в одному blocking shell:
& $P -B tools/diagnostics/m32-isolated-stands.py provision --manifest "$S/manifest.json"
& $P -B tools/diagnostics/m32-isolated-stands.py refresh-router --manifest "$S/manifest.json"
node tools/diagnostics/mobile-performance-m32.cjs paired m32-paired-natural "$S/manifest.json"
& $P -B tools/diagnostics/m32-isolated-stands.py stop --manifest "$S/manifest.json"
node tools/diagnostics/mobile-performance-m32.cjs finalize m32-paired-final storage/app/seo-m3-2-local/m32-paired-natural.json --exclusive-window-confirmed

$env:CHROMIUM_EXECUTABLE = 'C:/Program Files/Google/Chrome/Application/chrome.exe'
node tools/diagnostics/mobile-performance-m32.cjs alternate-browser m32-chrome152-trace
$env:CHROMIUM_EXECUTABLE = 'C:/Users/admin/AppData/Local/ms-playwright/chromium_headless_shell-1217/chrome-headless-shell-win64/chrome-headless-shell.exe'
node tools/diagnostics/theory-sidebar-stability.cjs m32-sidebar-guests accept --stage=m3-2 --page=sentence-types --viewport=desktop --scenario=new-guest --runs=10
node tools/diagnostics/theory-sidebar-stability.cjs m32-sidebar-alpine accept --stage=m3-2 --page=sentence-types --viewport=desktop --scenario=delayed-alpine
node tools/diagnostics/theory-sidebar-stability.cjs m32-sidebar-ajax accept --stage=m3-2 --page=sentence-types --viewport=desktop --scenario=delay
node tools/diagnostics/theory-sidebar-stability.cjs m32-sidebar-collapsed accept --stage=m3-2 --page=sentence-types --viewport=desktop --scenario=saved-collapsed
node tools/diagnostics/theory-sidebar-stability.cjs m32-sidebar-expanded accept --stage=m3-2 --page=sentence-types --viewport=desktop --scenario=saved-expanded
node tools/diagnostics/m32-local-acceptance.cjs m32-final-ui
node tools/diagnostics/m32-local-acceptance.cjs m32-questions-bootstrap --page=questions
node --test tests/Browser/cls-session-window.test.cjs tests/Browser/layout-comparison.test.cjs tests/Browser/paired-layout-comparison.test.cjs tests/Browser/mobile-performance-probe.test.cjs tests/Browser/theory-sidebar-stability.test.cjs tests/Browser/m32-local-acceptance.test.cjs tests/Browser/saved-test-persistence.test.cjs tests/Browser/state-request-classification.test.cjs
& $P -B -m unittest discover -s tests/diagnostics -v
& $P -B tools/diagnostics/m32-isolated-stands.py check --manifest "$S/manifest.json"
```

Offline reassessment: `node tools/diagnostics/theory-sidebar-stability.cjs reassess
m32-sidebar-phase-review` з п'ятьма оригінальними
`storage/app/seo-m3-2-local/m32-sidebar-{guests,alpine,ajax,collapsed,expanded}-sidebar-accept.json`
як окремими аргументами (brace notation тут пояснювальна, не PowerShell expansion).
Source hashes оригінальних inputs перевірено до/після.

Приватні матеріали залишені в `storage/app/seo-m3-2-local/`, не в Git:
frozen plan, три performance trace files, natural/final A/B envelopes та
verifier, п'ять sidebar raw reports + reassessment, два UI reports і screenshots,
stand manifest/preflight/provisioning/guard proofs. Повний HTML, cookie,
CSRF, введені відповіді або DB credentials у versioned report не включені.
Snapshot/копії залежностей та старі raw audits не видалялися й не комітилися.

## Межі висновків і завершення

Не виконувалися production HTTP/SSH/DB/API, deploy, PR, merge, Search Console,
очищення caches, міграції, сідери, контентні ремонти, Lighthouse або польові
CWV. Відсутність цих вимірювань не підмінено локальними «production scores».
Окремої причинної проби з підставленими локальними fonts не було: без
встановленого application mechanism вона не потрібна для чесного висновку
цього обмеженого етапу. Не заявляється, що весь сайт оптимізований.

Залишкові ризики, **без автоматичного наступного performance-етапу**:

1. PP LCP: широкий CI допускає і поліпшення, і погіршення. Повторно відкрити,
   якщо з'явиться відтворювана trace з конкретним зміненим resource/layout
   шляхом або нова вимога з наперед узгодженим бюджетом вимірювання.
2. Recent-input: точний internal Blink notification не атрибутований.
   Відкрити за наявності browser-internal trace, яка співвідносить
   lastInputTime з конкретним викликом; не через сам по собі нуль CLS.
3. Sidebar: **історичний одиничний випадок, причина не встановлена,
   на поточній версії в перевірених сценаріях не відтворено**. Відкрити,
   якщо новий raw capture має rAF loading/error overlap або реальний
   content shift разом з HTML/CSS/source identity.

Робоча гілка: `codex/seo-m3-2-mobile-performance`. Перед push перевірені
всі чотири `.github/workflows`: push triggers обмежені main, інші — PR main
або manual dispatch; цей branch push не запускає production deploy.
Явний staged список містить тільки пов'язані перевірені tools/tests/report
та одну ignore-строку; unrelated PPC/.codex/backups не включаються.
Остаточний commit SHA та звірка remote подані в повідомленні здачі —
сам report не може містити власний Git SHA без зміни цього SHA.

Основне приймання виконано на **http://gramlyze.loc**. Ізольовані A/B
описані окремо. Production gramlyze.com не оновлювався; PR і деплой
не виконувалися.
