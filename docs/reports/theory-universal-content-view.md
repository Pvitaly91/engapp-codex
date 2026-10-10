# Універсальна в’юха контенту теорії

Дата: 2026-10-11. Робоча гілка: `codex/theory-single-reference-template`.

## Резервна точка

Перед реалізацією створено й запушено резервний коміт `a802ca0ae162aa4fdc716fe6b0bd8ff0c5eb5a99` — `Backup approved theory design before universal content view`.
Це checkpoint погодженого versioned стану, без сторонніх локальних змін, секретів чи дампів. Для відкату саме цього рефакторингу слід зробити revert його фінального коміту; не виконувати hard reset брудного локального каталогу.

## Що змінено

- `TheoryContentSource` централізує вибір погоджених джерел. Порядок пакетів, identity/locale/body guards і повні fallback збережено.
- `TheoryContentRenderer` перетворює native та authored блоки на спільну семантичну модель через наявні адаптери. Виконувані views обмежені закритим реєстром у коді, а не полями навчальних даних.
- `resources/views/theory/content.blade.php` — спільна в’юха контенту. Вона використовує чинні компоненти, без додаткових зовнішніх рамок.
- `content-block.blade.php` делегує підготовку й відображення цим класам. Двофазна перевірка та власники point-level «Докладніше» збережені.
- Оновлено контракт `docs/content/theory-template.md` та додано регресійний тест чотирьох еталонів.

CSS, шрифти, кольори, відступи, sidebar, навчальні тексти, джерела пакетів, практика та її answer engine не змінювалися. Записів у робочу БД, деплою або звернень до production не було. Пов’язані файли синхронізовано з каталогом, який обслуговує `gramlyze.loc`; сторонні правки не включено.

## Сторінки-еталони

| Сторінка | Локальний GET | Point-level «Докладніше» |
|---|---|---:|
| [Past Perfect Continuous: Forms and Use](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | HTTP 200 | 12 |
| [Concessive and Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | HTTP 200 | 3 |
| [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | HTTP 200 | 7 |
| [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | HTTP 200 | 3 |

У кожній відповіді присутня практика. Старий зовнішній framed-container не повернувся. HTTP-перевірка не є заявою про нове візуальне browser acceptance.

## Ізольовані перевірки

Новий `TheoryUniversalContentViewTest` і сім чинних suites:

- `TheoryCanonicalTemplateTest`
- `TheoryHtmlAdapterTest`
- `TheoryNativePointDetailParityTest`
- `TheoryLegacyMixedExamplesTest`
- `M27LinkingWordsPackageTest`
- `M26PointDetailsTest`
- `M26InteractivePracticePackageTest`

Результат: **53 tests, 2985 assertions, exit 0**, одне повідомлення PHPUnit deprecation. Тести використовують ізольовану SQLite in-memory БД, окремі caches/views/storage, без робочого `.env`. Контроль 46661 захищеного файла після запуску: **0 змін**. Private receipt: `storage/app/seo-m2-local/theory-universal-content-regressions-v2-a308fc96306342649869220cca3e13e8-result.json` (не входить до коміту).

Нові перевірки порівнюють DOM із попередніми native-компонентами: текст, структуру, класи, anchors, ARIA і JS bindings, нормалізуючи лише пробіли. Також перевірено незалежні закриті details, M27 = 13 details, byte-exact passthrough практики, повний fallback за порушених guards, екранування невідомих форматів і заборону вибору довільного view з payload.

## Межа сумісності

Структурований контент визначає навчальні дані, тип компонента й семантичний акцент; дизайн належить спільним компонентам і CSS. Історичний trusted HTML поки має compatibility fallback: його невідомі структури не переписуються й не очищаються глобально. Отже, це єдиний шлях відображення та контракт нових матеріалів, але не автоматична конвертація всього старого HTML у нову схему даних.
