# Сторінки теорії: перша трійка зі списку 42

Дата: 2026-10-10. Гілка: `codex/theory-single-reference-template`.
Вихідний commit: `a445a6754`.

## Результат і межі

Файлова реалізація цього проходу стосується **лише перших трьох UK сторінок M27** зі списку користувача. Це не завершення всіх 42 сторінок і не повторне приймання попередніх пакетів.

| № / Page ID | Сторінка з точним URL реєстру | Розбиття теорії у metadata | Meaningful details |
|---|---|---|---:|
| 1 / 290 | [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | 4 basic-поля та 3 повні варіанти; 8 пар у basic, 16 entries разом із повними варіантами | 3 |
| 2 / 302 | [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | 6 basic-полів і 1 detail-поле; 7 пар | 7 |
| 3 / 315 | [Concessive And Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | 12 basic-полів, зокрема summary `/items/2`; 17 пар | 3 |

Числа описують прочитані source ranges і metadata, не кількість виконаних UI-перевірок. Повні варіанти першої сторінки дублюють необхідні ranges для відповідного source-представлення, а не додають нові авторські приклади. Усі **13 meaningful details** зберігають початкових власників і погоджений basic/detail розподіл.

## Що змінено

Змішані фрагменти «правило → English example → переклад → коментар» розділено за явними авторськими межами. Приклади передаються в чинний shared `example`, решта — у спільні paragraph/fragment. Не додаються окремі картки для кожного речення, нові кольори, дрібніший текст або пакетні стилі.

На Page302 коментар після прикладу про Marta відокремлено від українського перекладу: пояснення різниці практичного наслідку та логічного висновку більше не має роль translation. Воно залишається в тому самому detail. Формули з українським словом «речення», inline-терміни та решта пояснень не оголошуються повними English examples.

У практиці **шість choice items** — по два на сторінку — отримують окремий stem і змістові варіанти на кнопках. Початкові `a`/`b`, правильні відповіді та JS bindings не змінюються. На Page315 у двох selects повний український контекст і англійське речення розміщено окремими рядками. Умови не скорочуються; прихованого авторського ключа в цих трьох native sources немає, тому звичайні кандидати не ховаються до Check.

## Реалізація та fallback

Нові скінченні presentation-файли:

- [linking-words-reason-result-contrast.v1.json](../content/theory-inline-examples/linking-words-reason-result-contrast.v1.json);
- [advanced-linking-devices.v1.json](../content/theory-inline-examples/advanced-linking-devices.v1.json);
- [concessive-and-contrastive-structures.v1.json](../content/theory-inline-examples/concessive-and-contrastive-structures.v1.json).

`TheoryHtmlAdapter::nativePresentation()` прив’язує pinned metadata до точного M42 owner/design, UUID, source index і body hash. `finiteNativeExamples()` звіряє pointer та hash поля; послідовна конкатенація `html`/`source_html` повинна відтворювати весь початковий рядок. English і UK parts разом мають дорівнювати `source_html`. Порядок наявних language annotations також зберігається. Це скінченні ranges, не вгадування мови чи розбиття за довжиною речення.

У `TheoryPracticePresentation::nativeDisplay()` додано гілку для native sources без author keys: вона споживає лише exact hash-bound labels. `practice-set.blade.php` підставляє presentation slots; answer engine не отримує display-рядки замість raw values. Незбіг guards залишає повне початкове представлення. Caller-owned theory boundary не переносить ці зміни в курси чи окремі тести.

Frozen [M27 source](../../database/content-patches/m27-m11-linking-words.v2.json), plans, навчальні тексти, переклади, умови, answers/accepted, tokens, punctuation-sensitive rules, linked-bank scope та anchors не редагуються. Немає запису в БД або нового контентного apply.

## Відоме змістове обмеження

Page302, `choices[1]`: у реченні `The model is useful ___ it explains recurring errors.` без додаткового контексту можливі різні прочитання `provided that` і `insofar as`. Цей прохід **не виправляє змістову неоднозначність**: не додає нової умови, не змінює accepted answer і не заявляє перевірку якості відповіді. Змінено лише розміщення початкових варіантів.

## Статус виконання

Перед змінами main-agent зберіг окремий приватний файловий BEFORE `storage/app/theory-three-pages-local/before-a445a6754`. Snapshot і runtime artifacts не призначені для commit.

**За прямою вказівкою користувача перевірки не запускалися.** Виконано тільки читання та редагування файлів: без HTTP, браузера, тестів, app bootstrap, БД, apply, asset build, сервера чи production. Немає заяв browser PASS або live acceptance. Власні runtime-hunks, три metadata-файли та документацію перенесено до served ROOT `D:/DEV/htdocs/gramlyze.loc`, зберігши сторонні PPC hooks у native view. Відсутній ROOT `.gitattributes` не відновлювався; LF-правило metadata є лише у робочій гілці. Файлове підключення саме по собі не є візуальною перевіркою.

## Кольори: окремий follow-up користувача

Після commit `376b7a953` користувач окремо попросив повернути канонічні кольори цієї трійки. Шлях залишається спільним: старий `m42-native-design-styles` не вмикається, оскільки він також змінював би відступи, шрифти, рамки та курсив.

`TheoryHtmlAdapter::nativeAccent()` дозволяє opt-in лише для трьох точних UK owners із pinned metadata й verified M42 plan. `TheoryLegacyAdapter` передає семантичний accent у номер секції. Native практика отримує той самий guarded opt-in, а shared exercise передає свою навчальну роль: blue, amber або emerald. Кольори номерів, фону заголовків вправ і idle borders беруться зі спільної native-палітри; біла поверхня, нейтральний фон прикладів і синя лінія прикладу зберігаються як у канонічному еталоні. Correct/wrong, selected/disabled states не перефарбовуються.

Правила знаходяться тільки в `resources/css/theory-unified-design.css` і обмежені `data-theory-palette="canonical"`. У PPC, інших сторінок, курсів та окремих тестів цього нового opt-in немає. Тексти, parts, keys, basic/detail, anchors, layout, typography, padding, border widths та scoring не змінені.

Для застосування цієї CSS-зміни виконано локальну збірку лише `catalog-public.css`: активний asset — `assets/catalog-public-Cje6awad.css`. Усі інші manifest entries та JavaScript збережено. Власні кольорові hunks синхронізовано в served ROOT; сторонні зміни не замінювалися. Це реалізаційна збірка, не HTTP/браузерне або тестове приймання; БД та production залишаються поза scope. Generated assets і приватний `colors-before-376b7a953` не включаються до Git.

### Уточнення 2026-10-11: кольори тільки контентної частини

Користувач уточнив, що кольоровий запит стосувався контенту, не практики. Тому practice palette зміни з `4d36be82d` скасовано: прибрано opt-in у native practice wrapper, додані атрибути shared exercise/option/token і пов’язані правила CSS. Ці чотири Blade-файли повертаються до версії `376b7a953`; ранні compact display, кандидатні labels, raw values, scoring, reset і пояснення після Check залишаються чинними. Контентні accent guards та кольори номерів секцій не відкочуються.

Збережено новий приватний BEFORE `practice-colors-revert-before-4d36be82d`. Відкат перенесено точковими hunks у served ROOT, без заміни сторонніх PPC hooks. Локальну CSS-збірку застосовано: активний asset — `assets/catalog-public-DLnzdDTu.css`; інші manifest entries та JavaScript збережено. Файловий Git diff чотирьох practice views відносно `376b7a953` порожній. БД і production не змінювалися; браузерні й автоматичні перевірки не запускалися.

### Видимі кольори контенту, 2026-10-11

Попередній content opt-in впливав лише на номер секції: тіла прикладів усе ще брали загальний нейтральний фон. Після прямого зауваження користувача скінченні семантичні кольори застосовано до самих canonical content surfaces: example/form/usage/note, заголовків таблиць і маркерів summary. Фони, кольори наявних ліній та маркерів відповідають ролі підтвердженого розділу (blue, emerald, sky, amber, rose або slate), а не випадковому чергуванню номерів. Зовнішні картки й звичайні абзаци залишаються білими/прозорими; layout, fonts, padding та border widths не змінюються.

CSS вимагає одночасно `data-theory-component="section"` і guarded `data-theory-palette="canonical"`. Кожна кінцева ціль виключає самі `.theory-exercise`/`[data-sentence-builder]` та всіх їхніх нащадків. Практика, linked-bank, wrong/right correction surfaces, курси, PPC та інші сторінки не отримують нові кольори. Чинні dark/print variables збережено. Metadata, навчальні тексти та правила оцінювання не змінені.

Збережено приватний BEFORE `content-colors-before-2d8898cba`. Власні CSS/documentation hunks перенесено до served ROOT; локальну CSS-збірку застосовано (`assets/catalog-public-CRwKM160.css`), решту manifest entries і JavaScript збережено. У diff цього проходу є тільки CSS, canonical contract і report — жодного practice view або answer engine. Браузерні/автоматичні перевірки, БД та production не використовувалися.
