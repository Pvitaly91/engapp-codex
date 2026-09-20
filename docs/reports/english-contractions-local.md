# Скорочені та повні відповіді: локальне приймання

Дата: 2026-09-20. Середовище: `http://gramlyze.loc`.

## Зміни

- Спільний словник `public/data/english-contractions.json` для PHP та JavaScript: стандартні заперечення, форми з `'m`, `'re`, `'ve`, `'ll`, модальні та подвійні скорочення.
- Спільний клієнтський механізм використовується у картках, покрокових і старих режимах, діалогах, перестановці слів, побудові речень та практиці теорії. Серверна перевірка також приймає еквівалентні форми.
- Поля ручного введення підлаштовуються під обрану форму. `have` не завершує `haven't`: залишається поле для `not`. Так само працюють інші багатослівні розгортання.
- Суміжні вихідні маркери на кшталт `I / will` об'єднуються на клієнті, щоб можна було ввести `I'll`; канонічні питання у БД не переписуються.
- Заперечні питання з підтримуваними підметами зберігають граматичний порядок: `Don't you` → `Do you not`, `Isn't the shop` → `Is the shop not`, `Aren't I` → `Am I not`.
- `'s` / `'d` розгортаються за контекстом. Присвійний апостроф не прирівнюється до дієслова; `Yes, I am` не приймається як `Yes, I'm`. Якщо контекст не розрізняє значення скорочення, механізм не додає навмання обидва повні значення: для такого авторського питання потрібна однозначна повна відповідь або явний допустимий варіант.
- Під час відновлення попереднього прогресу зберігаються завершені й частково введені відповіді, включно з об'єднаними маркерами.
- Крапка наприкінці відповіді залишається необов'язковою; внутрішня пунктуація та значущий апостроф перевіряються.

## Браузерне приймання

Chromium, окремі гостьові контексти, desktop 1440×1000 та mobile 390×844. Відповіді вводились через реальні поля та Enter, без підміни стану питань. Після завершення видалено браузерну копію прогресу й перевірено відновлення із серверної сесії. Помилок JavaScript не зафіксовано.

| URL | Перевірка | Результат |
| --- | --- | --- |
| [Can / Could](http://gramlyze.loc/test/modal-verbs/can-could?source=theory) | `I can't hear` → `I cannot hear`, картки | Пройдено |
| [Future Simple](http://gramlyze.loc/test/future-simple/forms?source=theory) | `I'll` → `I will`, картки | Пройдено |
| [Future Simple, кроки](http://gramlyze.loc/test/future-simple/forms/step?source=theory) | `I will call` → `I'll call` | Пройдено |
| [Present Simple](http://gramlyze.loc/test/present-simple/negatives?source=theory) | `does not` → `doesn't`, картки | Пройдено |
| [Past Simple](http://gramlyze.loc/test/past-simple/negatives?source=theory) | `didn't` → `did not`, mobile | Пройдено |
| [Present Continuous](http://gramlyze.loc/test/present-continuous/negatives?source=theory) | `isn't` → `is not`, mobile | Пройдено |
| [Present Perfect Continuous](http://gramlyze.loc/test/present-perfect-continuous/negatives/step?source=theory) | `haven't` → `have not`, mobile, кроки | Пройдено |
| [To be](http://gramlyze.loc/test/verb-to-be/present?source=theory) | `She's` → `She is`, картки | Пройдено |
| [Заперечні питання](http://gramlyze.loc/test/types-of-questions/negative-questions-dont-you-know?source=theory) | `Don't you` → `Do you not` | Пройдено |

Додатковий smoke: [теорія Present Perfect Continuous](http://gramlyze.loc/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms) — HTTP 200, спільний скрипт завантажений один раз, Alpine доступний, JS-помилок немає.

Браузерний сценарій: `tools/diagnostics/contraction-forms-browser.cjs`. Локальні результати й скриншоти: `storage/app/contraction-forms/` (не входять у commit).

## Автотести

- `node --test tests/Browser/*.test.cjs`: **388 тестів пройдено**.
- Цільові PHPUnit: `AcceptedAnswerVariantsTest`, `SavedTestJsStateTest` (Unit і Feature), `SavedTestClientAnswerLogicTest`, `TheoryPracticeSetTokenBankUiTest`, `SentenceReorderQuestionFactoryTest`: **58 тестів, 798 перевірок**, без падінь.
- Є наявне попередження PHP 8.5: `PDO::MYSQL_ATTR_SSL_CA` у `config/database.php:62`; воно не стосується цих змін.
- Додатковий старий `SavedTestStepWrongAnswerTest` не виконується PHPUnit 12 через стару анотацію `@test`; він не включений у число пройдених тестів. Серверний matcher перевірений новим виконуваним feature-тестом.
- `git diff --check`: без помилок.

Навчальні дані БД, snapshots та сервер `gramlyze.ub` не змінювались. Перевірено спільні правила й перелічені браузерні сценарії, а не кожне питання сайту вручну.

Звіт за тестовими сторінками наведено у [згенерованому інвентарі](english-contractions-questions.md). На момент генерації це 78 URL mixed-тестів і 2618 записів питань: під кожним URL вказано конкретний текст, маркер і канонічну відповідь. Питання перелічені за повним пулом відповідного тесту, тому можуть з’являтися після нового запуску залежно від добірки за рівнем.
