# Past Perfect Continuous — речення окремо від підказок

Дата: 2026-10-05. Локальна ціль: `http://gramlyze.loc`. Робоча гілка: `codex/past-perfect-continuous-practice-quality`; базовий accepted commit цього follow-up: `4c1df9f14e64ef951ba8cd3992954a4b2ebb4780`.

## Результат

Великий заголовок Sentence Builder тепер містить лише авторську умову речення. Загальні вимоги «Побудуй речення…», «Почни з підмета…» та лексична допомога не додаються до нього і не показуються автоматично під полями. Вони доступні до відповіді через «Показати підказку» / «Show help» / «Pokaż pomoc», окремим дрібним текстом. Повторне натискання приховує допомогу. У manual та linked practice використано закритий native disclosure.

Приклад фактично перевіреного заголовка: «Я перед цим плавав, тому моє волосся було мокрим.» Перед натисканням підказки немає ані великої інструкції, ані лексичного абзацу. Показ готового англійського перекладу не додано.

Виправлення застосоване до карток, покрокових карток, manual / step-manual, native token composer, практики на сторінках теорії та окремого course mixed renderer.

## Посилання

- [Forms mixed](http://gramlyze.loc/test/past-perfect-continuous/forms)
- [Negatives mixed](http://gramlyze.loc/test/past-perfect-continuous/negatives)
- [Questions mixed](http://gramlyze.loc/test/past-perfect-continuous/questions)
- [Time Expressions mixed](http://gramlyze.loc/test/past-perfect-continuous/time-expressions)
- [Forms theory](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms)
- [Negatives theory](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives)
- [Questions theory](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions)
- [Time Expressions theory](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-time-expressions)
- [Окремий тест уроку курсу](http://gramlyze.loc/courses/english-grammar-theory/lesson/tenses/past-perfect-continuous/past-perfect-continuous-forms/test)

Дев'ять прямих банків і course saved-test projection наведені в [основному inventory-звіті](past-perfect-continuous-practice-quality.md). UK/EN/PL використовують ті самі UUID.

## Безпека та збереження даних

- Додано скінченну versioned presentation projection: 336 Builder + 288 Mixed = 624 питання, 1872 locale records. Кожний рядок має exact seeder, persistent UUID, locale і повний `expected_source`. Невідоме або змінене джерело залишається без обрізання; глобального regex для довільних питань немає.
- Десять контекстних Builder short-answer завдань та дванадцять Mixed short-answer завдань зберігають факти, особу і потрібну полярність. Змістовні часові умови також не вилучаються.
- EN має коротку semantic condition або англійський stem з пропусками, а не готовий target. Інструкція порядку слів збережена в допомозі; strict matcher не послаблено.
- Канонічні definitions, targets, answers, UUID, theory links і 624 exports у цьому follow-up не змінені. Змінюється лише їх показ; запис у робочу БД, сідери, міграції чи повторний apply не потрібні й не запускалися.
- Presentation revision оновлює застарілі source/hint snapshots через чинний finite merge. Вибрані відповіді, attempts, history та навігація зберігаються. Підказка відкривається локально, без запиту генерації.
- До реального `.loc` синхронізовано тільки 63 явно перелічені джерела, з exact conflict guards та exclusive source backups. Сторонні незавершені зміни основного checkout не скидалися.
- Production `.com` / `.ub`, `main`, Apache/XAMPP/hosts і конфігурація не змінювалися. Vendor, build, дампи, секрети й приватні runtime evidence не входять у commit.

## Автоматичні перевірки

- Node: 132/132 tests PASS. Перевірено початково закриті підказки, справжні show/hide handlers, escaping, legacy fallback, strict full/contraction matching, omitted `not` / `been` rejection та збереження прогресу.
- Ізольований PHP пакет: 56 tests / 45062 assertions PASS, одна відома framework deprecation. Після запуску fingerprint захищених файлів не змінився.
- Додатковий payload пакет: 10 tests / 5857 assertions PASS; 4 source tests перетинаються з пакетом вище, 6 перевіряють фактичний course compose payload. Окремий pre-answer disclosure пакет: 7 tests / 29 assertions PASS. Обидва запускалися ізольовано; зміни захищених файлів — 0, одна framework deprecation на запуск.
- Production-profile check: 1 test / 15 assertions PASS, одна deprecation; UK/EN/PL title/help та незмінний target перевірено через synthetic in-process request / SiteMode, без зовнішнього HTTP, конфігураційних файлів чи БД. Fingerprint захищених файлів незмінний. Разом 70 різних PHP tests; дубльовані 4 source tests не рахуються вдруге.
- Presentation generator `--check`: 624 питання / 1872 locale rows, без drift. Builder generator `--check`: 336 питань і 144 companion locale rows, без drift; дублів нормалізованих prompt/target немає.
- Незалежний read-only review: конкретних дефектів display/help, exact fallback, escaping чи target/UUID/progress regression не виявлено.

## Живі браузерні перевірки

Mixed: 12/12 сторінок (4 підтеми × UK/EN/PL), усі HTTP 200, по 84 питання. Усі Builder заголовки точно збігаються з finite sentence-only projection; до натискання допомогу не видно. Виконано 72 show/hide пари та 216 фактичних завершень (3 типи × 6 рівнів × 12 сторінок). На всіх 12 reload відновив 18/18 правильних відповідей і зберіг короткі заголовки, restart дав чистий тест. 24 desktop/mobile screenshots; репрезентативний Forms UK desktop переглянуто візуально.

Regression smoke: Present Perfect Forms, Future Perfect Forms і Present Perfect Continuous Forms theory — 3/3 HTTP 200, навчальний dataset / практика наявні, page errors — 0. Learning-control overflow та violations — 0. Окремо зафіксовано 8 старих shell/random-background overflow warnings; декорації не змінювалися.

Для reload/restart створено нові анонімні browser contexts без користувацької сесії. Дозволено 138 запитів тільки до перевіреного guest `/state` endpoint: він зберігає поточну тестову сесію, не user/progress rows у БД. Решту non-GET заблоковано (2), зовнішні GET шрифтів також (39). Ранній GET-only probe не міг виконати фактичний restart; його timeout не зараховано як дефект сайту чи як успішний сценарій.

Теорія / native / manual: 66/66 фактичних UI станів (24 linked practice, 30 native compose, 12 manual / step-manual; UK/EN/PL × desktop/mobile). Речення точно відповідають projection, допомога початково закрита; виконано 264 реальні show/hide кліки. Для всіх 30 native станів окремо перевірено reload реальної lesson queue / attempt counters та видимого лічильника. Help font: 12px linked, 14px native/manual, поза великим заголовком; manual heading 18.4–32px, native heading 24–37.6px залежно від viewport. Скриншот Forms UK /manual містить саме «Коли магазин відчинився, вони чекали вже з ранку.» без загальних інструкцій.

У прийнятих 66 станах: 66 full-target acceptance, 54 omitted-been rejection, 18 omitted-not rejection, 6 offered-contraction checks і 12 manual incomplete rejection. Усі 84 порожні manual fields читаються й вкладаються у viewport. Статична практика/M26 regression: 72 static task executions, 24 input targets, 48 accepted alternatives, 33 semantic rejections, 48 UK M26 disclosures та 12 matcher-bootstrap checks. Є 132 required screenshots + 1 точний приклад із магазином; 49 репрезентативних PNG переглянуто візуально, не заявляється перегляд усіх 133. У цьому GET-only flow 57 routine non-GET запитів заблоковано; server hint requests — 0.

Первинна спроба мала 1 HTTP 500 і 2 navigation timeouts; причину одноразового 500 не встановлено. Ще 29 відмов були помилками приватного QA-harness: exact sentence identity після навмисного native shuffle та перевірка omitted `been` у short-answer завданні без цього слова. Виправлено тільки приватні очікування перевірки, не runtime. Один bounded retry 32/32 PASS; page/console/HTTP errors і надіслані non-GET у ньому — 0. Початкові відмови збережено окремо й не зараховано як успішні.

Чисті native course prerequisites перевіряються окремо від browser-only guest unlock fixture. Fixture не пише на сервер і не є доказом автоматичного розблокування всього курсу; чинний gate у цьому завданні не перероблявся.

Окремий course mixed renderer: UK і PL — 2/2 прийнятих desktop сценаріїв, HTTP 200; реальний pool 192 UUID (120 native Builder + 72 gap), повністю в уже врахованих 624. Перевірено короткий заголовок, початково закриту локальну допомогу, show/hide та reload. Clean course lock записано окремо; guest fixture позначає виконаним лише реальний попередній урок із manifest, а цільовий урок лишається невиконаним. Це інший route, не старий saved-test projection із 15 Basics B2 UUID; membership / БД не змінювалися.

Не перевірено живим браузером: EN версія цього окремого course route. Початкова навігація не отримала DOMContentLoaded за 30 секунд; один послідовний retry за 60 секунд також завершився timeout до UI assertions. HTTP-статус завершеної навігації не встановлено. Причину не підтверджено; це не доказ дефекту display/help чи блокування сайту. EN payload / JS handler перевірені автоматично, але не видані за успішну браузерну перевірку. Інші EN mixed / theory / native / manual сценарії пройшли. Зайва 216-state direct GET матриця припинена через локальні navigation timeouts і не зарахована; основний finite bank перевірено generator/PHP contracts.

## Перевірка незмінності та відтворення

Фінальна SELECT-only перевірка фізичної локальної БД порівняна з accepted baseline: усі 9 груп (`protected`, pages, blocks, links, linked/candidate questions, saved tests, answer/hint/option data, saved-test links) збігаються. Захищені user/progress/attempt/session/state digests незмінні. Дані з приватних snapshots не публікуються.

Finite sync `check`: 63 джерела, pending 0. `git diff --check` і staged check пройшли; явно обрано лише 35 файлів follow-up, без канонічних definitions / exports, runtime caches, backups, vendor/build чи секретів. Історія accepted M35 збережена.

З робочого worktree:

```powershell
php -d opcache.enable_cli=0 scripts/build_ppc_compose_presentation.php --check
php -d opcache.enable_cli=0 scripts/upgrade_ppc_quality_builder.php --check
php -d opcache.enable_cli=0 scripts/upgrade_ppc_quality_mixed.php --check
git diff --check
```

Перша спроба додаткового PHP preflight не змогла створити PackageManifest у приватному тестовому каталозі. Повтор із належним filesystem-доступом пройшов; production / робоча БД не використовувалися, protected-file changes в обох спробах — 0.
