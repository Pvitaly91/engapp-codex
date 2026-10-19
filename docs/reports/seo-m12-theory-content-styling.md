# M12 — єдине оформлення нового theory-контенту

Дата: 27.09.2026. Проєкт: `Pvitaly91/engapp-codex`.

## Результат і межі

Стилізацію застосовано до справжнього `http://gramlyze.loc`. Змінено renderer і локально зібраний CSS, а не зміст уроків. Definitions, записи БД, M11/M12 manifests та адресні apply-команди не редагувалися. Немає DB apply, міграцій, сідерів, зміни `.env`, PHP/Composer/XAMPP, очищення кешів або нового контентного пакета.

Після fetch база — завершений M12 `37996e3581a1c4866d115766c0c3921433a646ca`, що містить прийнятий M11 `fe2544e8364437f661d8e4cd6bfa084776604836`. Створено `codex/seo-m12-theory-content-styling` у повторно використаному вільному worktree `storage/app/seo-m11-worktree`. Основний checkout зі сторонніми змінами не перемикався, не скидався й не включався до коміту; потрібні шість implementation-файлів адресно застосовано також до нього для реального локального рендерингу.

Production `.com`/`.ub` не перевірявся й не змінювався. `.com` у canonical/sitemap аналізувався лише як рядок. Немає SSH, PR, merge, push у main, force push або деплою.

## Візуальна проблема й UI-референс

M11/M12 уже мали якісний український матеріал, приклади, таблиці та самоперевірку, але `box` виводив усе одним довгим `prose` усередині кількох вкладених рамок. Пронумеровані h4 не відокремлювали секції достатньо виразно, англійські речення зливалися з перекладом, а ключі виглядали продовженням основного тексту.

Референс — [Present Perfect: Forms](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-forms). У справжньому браузері переглянуто його секції правил, numbered headers, порівняльну таблицю, warning note та практику. Звідси взято принципи: окрема картка на логічний розділ, спокійний кольоровий header, компактний номер, розведення прикладу й пояснення, локальний table scroll та самостійний practice-блок. Це не pixel-perfect копія і не redesign старих V3 блоків. Додаткова контрольна сторінка — [Past Perfect: Forms](http://gramlyze.loc/theory/tenses/past-perfect/past-perfect-forms).

### Before → after

- Один довгий framed box → окремі секції-картки з наявними номерами в badge; зайві зовнішні рамки/відступи прибрано тільки для повністю сумісного rich-контенту.
- Англійський приклад курсивом посеред українського абзацу → виділений mini-block; переклад лишається поруч у вихідному порядку. Короткі inline-терміни не перетворюються на картки.
- Hero-приклад з англійським і українським текстом одним дрібним monospace рядком → чіткий англійський рядок і спокійніший переклад. Оригінальний роздільник і слова збережено.
- Самоперевірка та ключі як продовження тексту → окрема секція з м’яким зеленим header, видимими номерами та нативним розкриттям відповідей.
- Таблиця з однаковими рамками всіх комірок → узгоджені header/row separators, padding і локальний scroll; типові помилки мають стриманий warning-header. Наявний фінальний перехід до суміжних уроків оформлено нейтральною карткою, нового висновку не дописано.

## Повторно використовувана реалізація

- `app/Support/TheoryRichContent.php`: render-only presentation adapter для вже довіреного HTML у `box`, не новий sanitizer. Opt-in за структурою: щонайменше два верхньорівневі пронумеровані h4 та одна наявна `section[id^="self-check-"]` з ol/details/summary/ключами. Немає списку спеціально зашитих URL чи ID уроків.
- `resources/views/components/theory-rich-box.blade.php`: спільний renderer для теорії й курсової копії. Несумісний/звичайний або пошкоджений фрагмент залишає попередній raw-box шлях; Present/Past Perfect не переводяться на нове оформлення.
- Два вузькі hooks: `resources/views/theory/show.blade.php` і `resources/views/courses/partials/theory-page-content.blade.php`. Збережено sidebar/TOC, title/H1, metadata, тести та gates. Підзаголовки й номери обгортаються, але текст, його порядок, href, ID та accessibility-атрибути не переписуються.
- `resources/css/theory-rich-content.css`, імпортований чинним `catalog-public.css`: правила лише під `.theory-rich-*`, чинні `--text`, `--surface-strong`, `--accent`, `--line` і theme toggle. Без глобальної зміни `prose`, нового framework, CDN або JavaScript.

Знімалися лише точні відомі inline presentation-стилі всередині сумісного фрагмента. Незнайомі атрибути/стилі, table header scopes, region labels, ol start/value збережено. Нумерація залишається реальною decimal-list; summary працює з клавіатури й має focus ring. Приклади з HTML/entities проходять старий `TheoryInlineHtml`, щоб не змінити escaping чи видимі слова. Для цього додано окремі регресії.

## Реальне локальне застосування

Перед зміною основної копії звірено її renderer/CSS з базою worktree (різниця лише CRLF/LF). Збережено приватні копії трьох змінюваних файлів та попереднього Vite manifest у `storage/app/seo-m12-styling-local/code-before/`. Потім адресно застосовано три нові й три змінені implementation-файли; їхня тотожність worktree перевірена після нормалізації line endings.

Vite build завершився успішно: 55 modules, 47.94 s. Новий public CSS — `assets/catalog-public-BUGA8MrZ.css`, 105.51 kB (gzip 17.72 kB). На робочий сайт перенесено тільки цей зібраний CSS і змінено тільки відповідний manifest entry. Existing app.css, app.js, catalog-public.js entries та старі assets залишилися без змін; generated build не комітиться. Залежності не встановлювалися/оновлювалися. Попередження про застарілу Browserslist базу не спричинило оновлення залежностей.

DB preview/backup/apply тут не потрібні: контент у БД не адаптувався. Не створювався тимчасовий HTTP endpoint, не змінювався локальний apply-процес M11. `.env` не редагувався.

## Перевірені сторінки

| Теорія з новим оформленням | Основний тест: перевірений перехід |
|---|---|
| [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | [Mixed](http://gramlyze.loc/test/clauses-and-linking-words/linking-words-reason-result-contrast) |
| [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | [Mixed](http://gramlyze.loc/test/clauses-and-linking-words/advanced-linking-devices) |
| [Concessive and Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | [Mixed](http://gramlyze.loc/test/clauses-and-linking-words/concessive-and-contrastive-structures) |
| [Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | [Mixed](http://gramlyze.loc/test/sentence-structure/cleft-sentences-basics) |
| [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | [Mixed](http://gramlyze.loc/test/word-order/inversion-basics) |
| [Advanced Fronting And Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | [Mixed](http://gramlyze.loc/test/word-order/advanced-fronting-and-emphasis) |

Дві контрольні старі сторінки наведені вище. Додатково один ordered GET `/sitemap.xml` до/після; повного crawl не було.

## Результати перевірок

- Ізольовані PHP suites: **80 tests, 2403 assertions, exit 0**. Охоплено новий adapter/components, точну незмінність тексту/посилань шести definitions, hero/entities, conservative fallback, Present Perfect, M11/M12 content/render та patch-регресії, inline HTML safety. Робоча БД для цих тестів не використовувалася. Один наявний PHP 8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA` залишено поза scope.
- Чотири Node suites: **20/20 pass** — opt-in CSS scope, mobile/table/list/focus/dark правила, read-only/local-only browser guard, точні before/after comparisons і сумісність попередніх M11/M12 diagnostics.
- HTTP before/after: **15/15 по 200**, comparison pass. Видимий навчальний текст після нормалізації лише пробілів, його порядок і пунктуація незмінні. Exact title/H1/description/OG/Twitter/canonical/meta robots/X-Robots-Tag, ordered href inventories та форми самоперевірки незмінні для восьми theory і шести tests. Script/style/inert-template payloads виключено з learner-text comparison: вони не є навчальним текстом і можуть містити runtime-значення.
- Sitemap: **554 URL**, порядок і склад незмінні; SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Число отримано з живого response, не використано як hardcode.

- Окремий `vitest run tests/js/publicAssets.test.js`: **6/6 pass**, зокрема незмінність public Vite/Alpine pipeline та сумісність наявного legacy app.css compiler. Прогін зайняв 182.70 s; залежності не оновлювалися.
- Chromium **147.0.7727.15**: **16/16 page×viewport сценаріїв**, кожний у світлій і темній темах — **32 перевірки теми**. Desktop 1440×1000, mobile 390×844, нові contexts. Шість структурованих уроків дійсно приходять зі styled sections/examples в оригінальному response; дві старі сторінки залишають старий renderer. Не використовувалися `page.setContent`, `route.fulfill` чи підстановка нового HTML.
- У всіх шести уроків лишилося по шість завдань і шість пояснених ключів. Decimal numbering і native details пройшли Enter → Space → Enter. Англійські приклади/українські переклади, секції нижче першого екрана й keys перевірено на збережених screenshots. Таблиці прокручуються локально; горизонтального overflow документа немає на всіх восьми сторінках.
- Шість основних тестових посилань реально відкрито в обох viewport. **Uncaught JS errors: 0**. У фінальному прогоні 28 відмов Google Fonts `net::ERR_NETWORK_ACCESS_DENIED` відокремлено від дефектів контенту; використано fallback fonts. 12 автоматичних POST `/test/.../state` навмисно зупинено read-only guard, щоб не змінювати progress.
- Мінімальний виміряний контраст вибраних елементів **нового оформлення — 5.682:1** при вимозі 4.5:1 для звичайного тексту. Це не повний accessibility-аудит. На старих контрольних сторінках є окремі низькоконтрастні елементи темної теми; вони не отримали rich-стилів. Before/after screenshots Present Perfect підтверджують уже наявні світлі панелі з блідим текстом у dark mode. Пакет їх не виправляє й не подається як повне приймання доступності старих блоків. Їхні вимірювання збережено як спостереження, не як критерій нового дизайну.
- Перший післяпрогін не зміг виміряти OKLab кольори старих блоків через обмеження діагностичного RGB parser. Для фінального прогону browser перетворює computed color через від’єднаний canvas; DOM/контент не змінюється, пороги нового оформлення не послаблено. Перший звіт залишено окремо; фінальний — `after-final-browser.json`.
- Збережено 156 screenshots фінального основного прогону та додаткові mobile section views. Основні desktop/mobile приклади, таблиці, ключі, світлу/темну тему та old-reference before/after відкрито й візуально переглянуто. Широкі таблиці обрізаються лише всередині свого scroll-контейнера, не на рівні сторінки.

Приватні докази й screenshots збережені у `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m12-styling-local/`, зокрема `before-text-http.json`, `after-http.json`, `after-comparison.json`. Вони не входять у Git. PHP evidence: `storage/app/seo-m2-local/m12-styling-verified-5bd3a400421d4024ba8dcce0e65ad0bc-result.json` в ізольованому worktree.

## Обмеження й передача

Це контрольований presentation-пакет, не redesign усіх типів блоків. Старі неструктуровані HTML-фрагменти не отримують нові картки автоматично. Англійські речення визначаються консервативно; неоднозначні inline-приклади зберігаються без реструктуризації. Контент, назви й рівні уроків не переписувалися.

Пакет комітиться й пушиться лише в `codex/seo-m12-theory-content-styling`; SHA та перевірку remote наведено у фінальній відповіді. `.env`, backups, diagnostics, vendor, public/build та сторонні зміни не включаються.

Production не перевірявся й не змінювався.
