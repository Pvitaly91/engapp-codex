# Канонічний шаблон теорії

Контентний пакет змінює текст і структуровані навчальні дані. Він не створює власний дизайн. Візуальний еталон — локальна сторінка `/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms` і чинні native-компоненти для тих типів матеріалу, яких на цій сторінці немає.

## Шлях від даних до сторінки

`theory.show` явно вмикає presentation-контекст теорії. Перевірки пакета спочатку підтверджують owner, locale, UUID, порядок і body; frozen sources та deterministic projection залишаються незмінними. Потім render-only адаптер передає семантичну модель у `theory.components.node`.

- `App\Support\TheoryLegacyAdapter` нормалізує чинні native-формати даних.
- `App\Support\TheoryAuthoredAdapter` нормалізує погоджені авторські структури та їхні скінченні basic/detail mappings.
- `App\Support\TheoryHtmlAdapter` переносить розпізнані структури trusted legacy HTML. Нерозпізнаний або пошкоджений матеріал залишається повністю доступним у fallback.
- `App\Support\TheoryComponents` містить закритий список семантичних компонентів. Payload не вибирає довільний Blade view, CSS-клас або стиль.

Канонічні views — `resources/views/theory/components/{section,usage,form,example,note,table,mistake,summary,disclosure,fragment,group,correction,paragraph}.blade.php`. Вхід — `$node`, тип визначає `kind`; допустимі семантичні властивості задають структуру та акцент. Номер етапу, seeder name та URL не визначають зовнішній вигляд. Картка секції підтримує семантичні ролі plain/summary/mistakes; структурний fragment має закритий вибір div/section, типографіку inherit/prose та compact-stack. Це зберігає початкове оформлення native-пояснень, а не створює пакетні варіанти дизайну.

Заголовок секції використовує `components.theory-native-header`; point-level disclosure — спільні `theory.partials.point-disclosure` і `theory.partials.section-disclosure`. Спільні стилі — `resources/css/theory-unified-design.css`. Механіки практики залишаються окремими; їхні візуальні частини використовують спільні компоненти й `engram.theory.blocks-v3.authored-practice-ui`.

## Додавання матеріалу

1. Додай або онови versioned навчальні дані в межах погодженого контентного завдання.
2. Використай наявний тип блока та адаптер. Для нового формату даних додай його нормалізацію до тієї самої моделі, зберігши guards і повний fallback.
3. Перевір усі авторські поля, порядок, anchors, levels, переклади, таблиці та практику; frozen hashes не змінюються заради проходження rendering-тестів.
4. Перевір реальну сторінку на `http://gramlyze.loc` та відсутність регресії еталона й інших consumers.

Не створюються нові `mXX-section`, `mXX-detail` або `mXX-styles` із незалежною presentation-реалізацією. Історичні package wrappers допускаються лише як адаптери даних до канонічних компонентів. Сумісність інших consumers відділена від theory-контексту й не є альтернативним шаблоном теорії.

Новий тип навчального блока або зміна дизайну потребують окремо визначеного завдання. Звичайна SEO-модернізація такого дозволу не дає. Компактність досягається редактурою й погодженим розподілом basic/detail, а не новою версткою, приховуванням потрібного тексту або дрібнішим шрифтом. Картка форм може містити кілька рядків; кількість конструкцій визначає контент, оформлення — спільний компонент.

Короткі ремарки й приклади без окремого пояснення залишаються у visible basic за правилами [point-detail-quality.md](point-detail-quality.md). Архітектурні перевірки мають доводити однаковий DOM і візуальні класи для однакових нормалізованих даних із різних adapters, незалежність від номера пакета та збереження guards/fallback.
