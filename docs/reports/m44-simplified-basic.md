# M44 follow-up — спрощений базовий шар

Дата: 2026-10-09. Гілка: `codex/seo-m44-simplified-basic`.
База: `07b157991ae7a296f16efba6bbd0c244a9e6a4b0`.

За запитом користувача спрощено початковий вигляд трьох локальних уроків. Орієнтир щільності — наданий знімок старої сторінки Will vs Be Going To; оформлення — чинні native-компоненти Past Perfect Continuous: Forms and Use.

| Урок | Локальна сторінка | Видимі групи до → після | «Докладніше» після |
|---|---|---|---|
| Will vs Be Going To | [Відкрити](http://gramlyze.loc/theory/maibutni-formy/future-simple/will-vs-be-going-to) | 27 → 23 | 6 |
| Present Continuous for Future | [Відкрити](http://gramlyze.loc/theory/maibutni-formy/present-continuous-for-future) | 17 → 13 | 4 |
| Choosing the Right Future Form | [Відкрити](http://gramlyze.loc/theory/maibutni-formy/choosing-the-right-future-form) | 25 → 16 | 7 |

## Зміни

Пов’язані пояснення зібрані у скінченні редакційні групи. Основний шар показує головне правило, важливі обмеження та приклади з перекладами. Поглиблення відкривається всередині відповідної картки. Короткі застереження, типові помилки та підсумки залишені видимими. Для Will / Going To збережено шість карток: ствердження, заперечення й питання для кожної конструкції; короткі відповіді згруповані з питаннями.

Формули використовують звичайний текст прикладів усередині native-картки, без додаткових вкладених рамок. Таблиці показують приклади, переклади та примітки без окремої картки в кожній клітинці. Глобальні CSS, розміри шрифтів, палітра, хедер і sidebar не змінені.

Усі 69 початкових пунктів, повні авторські формулювання, приклади, переклади, формули, 7 старих деталей та 4 таблиці збережені. Видимі стислі формулювання — окрема presentation projection; frozen master, package, definitions і DB rows не переписувалися. Перегрупування не потребує DB apply. Курси зберігають попереднє відображення; питання тестів і практика не змінені.

Проєкція: `docs/content/m44-simplified-presentation.v1.json`, SHA-256 `547e9db5647c5708b5f699122a61fb53d10fa7332912829ce07b6703de7dc24e`. Вона прив’язана до незміненого master SHA-256 `541db4dec57ed372d23832e849f84c0554fb40faf73693c5b7ab2d306fa7b888`. Зміна вхідного авторського тексту відхиляє компактну проєкцію та зберігає повний renderer. Чинна перевірка оригінальних details виконується до компактного відображення; перевірки duplicate anchors не послаблені.

## Перевірки

Ізольований PHPUnit: **54 tests / 3832 assertions, exit 0**, одне повідомлення deprecation. Перевірено точну кількість авторських входжень у basic + detail, формули, переклади, tables, сталі посилання, змінений/чужий payload, початкові значення та відповіді практики, незмінність курсів. 46 661 захищений файл: 0 змін після тестів. Попередній тест, що вимагав окрему картку для кожного пункту, замінено повною перевіркою нових груп у `M44SimplifiedPresentationTest`; тести власників, fallback та практики залишені.

Команда з робочої гілки:

```text
python tools/diagnostics/run-isolated-tests.py --php "C:/Program Files/xampp/php/php.exe" --label m44-simplified-v1 tests/Feature/M44SimplifiedPresentationTest.php tests/Feature/M44AuthorFidelityTest.php tests/Feature/M44CoursePreservationTest.php tests/Unit/M44SourceScopeGuardTest.php
```

Живий Chromium: **16/16 PASS** — три уроки та PPC-еталон × 1440×1000 / 390×844 × light/dark. Усі GET повернули 200. Немає page/console/HTTP/network errors, спроб non-GET чи переповнення навчальних елементів; широкі таблиці мають власну прокрутку. Перевірено всі 69 пунктів і 627 авторських полів у кожній парі viewport/theme, всі старі BEFORE-якорі, унікальність ID, відсутність fallback, незалежне відкриття, Space/Enter, старі detail deep links і відновлення стану після друку. Файли залишалися незмінними протягом фінального запуску.

Практика: на кожній сторінці перша складена вправа (разом 6 полів) пройшла неправильна відповідь → правильна → reset; рахунок повернувся до нуля. Повний набір відповідей усіх 18 вправ додатково перевірений ізольованими тестами. Це не повторне ручне проходження кожного питання великих тестових банків.

PPC-еталон: текст, метадані та якорі збережені; випадковий sentence-builder вилучений лише з порівняння тексту за точними межами. Збережені повні та viewport-скриншоти. Візуально переглянуті вибрані формули, usage та відкриті деталі на desktop/mobile у двох темах; не заявляється ручний перегляд кожного PNG.

| Видима висота теоретичних секцій | Desktop, CSS px | Mobile, CSS px |
|---|---:|---:|
| Will / Going To | 10073 → 8177 (−18,8%) | 14848 → 11946 (−19,5%) |
| Present Continuous for Future | 6904 → 4936 (−28,5%) | 10935 → 7155 (−34,6%) |
| Choosing the Right Future Form | 9815 → 6731 (−31,4%) | 15669 → 9936 (−36,6%) |

Це сума висот теоретичних секцій із закритими деталями; практика, hero, sidebar і проміжки між секціями до метрики не входять. Фон/типографіку не зменшували для отримання цих значень.

Приватні докази: ROOT `storage/app/m44-simplify/before-v1`, `after-v5`, `worktree-before`; PHPUnit receipt у WT `storage/app/seo-m2-local/m44-simplified-v1-43f65d2c5c5048bea5ff119d2f22d21b-result.json`. Попередні діагностичні спроби збережені; фінальний результат — `after-v5/source-stability.json` з `pass: true`.

Зміни застосовані до ROOT `D:/DEV/htdocs/gramlyze.loc`; commit створюється в attached worktree. Сторонній dirty state ROOT збережений. Production, main, hosts, серверна конфігурація, dependencies, build і БД не змінювалися. Деплой не виконувався.

## Додаткове спрощення блоку формул

Окремий запит користувача після `fdff5600f53c9adb6e1407bed87b98586cb62274`: спростити лише блок «Ствердження, заперечення, питання й короткі відповіді» на Will vs Be Going To.

Шість великих карток замінені двома native-картками — Will та Be going to. У кожній три видимі рядки: ствердження, заперечення, питання; точна авторська формула та один незмінений двомовний приклад на рядок. Коротке правило відповідей залишається видимим. Повні пояснення, додаткові приклади та всі варіанти коротких відповідей відкриваються в окремому змістовному «Докладніше» відповідної конструкції. Усі 8 вихідних пунктів та старі посилання збережені. Кількість груп уроку A: 23 → 19; B/C не змінені.

Нова вузька проєкція `docs/content/m44-forms-presentation.v2.json` має SHA-256 `0f9019db84e8b8573ceb99f655e9dde083df6bbd6bf0b19a82516e4892e26aac` і прив’язана до незмінених v1 та author master. DB apply не потрібен; frozen payload, практика, shared widgets, CSS, sidebar та інші блоки не редагувалися.

Живий Chromium, `tools/diagnostics/m44-forms-browser.cjs`: **6/6 PASS**. Цільова сторінка перевірена на 1440×900 та 390×900 у світлій/темній темах; інші два M44 уроки — desktop/light. Поза цільовою секцією текст, H1, title та старі anchors збігаються з BEFORE. У секції 2 картки, 6 видимих формул, 8 оригінальних пунктів. Enter/Space відкриває лише відповідну деталь; старі detail deep links відкривають її батьківський блок; після друку стан закритих деталей відновлюється. Виявлених помилок page/console/HTTP або переповнення тексту немає.

Висота саме цього блоку із закритими деталями: desktop **1152 → 678 px**, mobile **2347 → 1382 px** — приблизно на 41% менше без зменшення шрифтів. Приватні BEFORE/AFTER-докази: ROOT `storage/app/m44-forms/before-v1` та `after-v2`. Візуально переглянуті desktop/light та mobile/dark.

Фінальний ізольований PHPUnit: **12 tests / 3766 assertions, exit 0**, одне deprecation; 46 661 захищений файл — 0 змін. Перевірені точні входження всіх авторських полів у 20 секціях, формули, переклади, якорі, практика й незмінність курсів. Команда: `python tools/diagnostics/run-isolated-tests.py --php "C:/Program Files/xampp/php/php.exe" --label m44-forms-v2 tests/Feature/M44SimplifiedPresentationTest.php tests/Feature/M44AuthorFidelityTest.php tests/Feature/M44CoursePreservationTest.php`. Приватний receipt: WT `storage/app/seo-m2-local/m44-forms-v2-29a376ebd3d748b9b038fcd4f7f952ee-result.json`. Дані evidence, runtime caches та сторонні зміни до коміту не включені. Production не перевірявся й не змінювався.

## Спрощення «Форма: be та дієслово з -ing»

Наступний вузький запит після `70af09a19`: блок із наданого screenshot належить [Present Continuous for Future](http://gramlyze.loc/theory/maibutni-formy/present-continuous-for-future#m44-cont-forms), а не Will vs Be Going To з ambient URL.

Видимий basic тепер має три компактні native-картки: формула ствердження, заперечення та питання, по одному точному прикладу з перекладом. Питальне слово явно необов’язкове. Повні правила, скорочення, короткі відповіді та додаткові приклади збережені в одному змістовному «Докладніше». Написання -ing залишається повністю видимою компактною приміткою без великої картки чи зайвого disclosure. Два короткі приклади відповідей зведені в один рядок. Збережено 5 source points, усі author fields та старі якорі; кількість груп уроку B: 13 → 12.

Вузька проєкція: `docs/content/m44-cont-forms-presentation.v3.json`, SHA-256 `81ca7d4f60bd3be33fd3ec5a7708cb11397b5b8e2b2795747b8d456f6fadc785`. Frozen master, v1/v2, shared widgets, CSS, практика й БД не змінені. Змінена лише presentation цільової секції; DB apply не потрібен.

Живий браузер: **6/6 PASS** — B × desktop/mobile × light/dark, A/C × desktop/light. Висота блоку: **986 → 714 px** desktop (−28%) та **1548 → 1175 px** mobile (−24%). Перевірені старі anchors, Enter/Space, deep links, print-state restoration, відсутність переповнення та page/console/HTTP errors. Поза цільовим блоком текст, H1 і title збігаються з BEFORE. Візуально перевірені desktop/light та mobile/dark; read-only review не знайшов втрат тексту або розширення scope. Приватні докази: ROOT `storage/app/m44-forms/continuous/before-v1` та `after-v2`. Команди: `node tools/diagnostics/m44-forms-browser.cjs before-v1 continuous` і `node tools/diagnostics/m44-forms-browser.cjs after-v2 continuous`.

Ізольований PHPUnit: **12 tests / 3763 assertions, exit 0**, одне deprecation. Збережена точна кількість входжень усіх авторських полів у 20 секціях, перевірені практика та незмінність курсів. Запуск: `python tools/diagnostics/run-isolated-tests.py --php "C:/Program Files/xampp/php/php.exe" --label m44-cont-forms-v1 tests/Feature/M44SimplifiedPresentationTest.php tests/Feature/M44AuthorFidelityTest.php tests/Feature/M44CoursePreservationTest.php`.

46 661 захищений файл — 0 змін. Приватний receipt: WT `storage/app/seo-m2-local/m44-cont-forms-v1-7ab4e4810c444e59ad1f2b0fadf4914c-result.json`. У commit входять лише пов’язані source/test/report файли; runtime evidence та сторонній dirty state збережені поза ним. Production і main не змінювалися, деплой не виконувався.
