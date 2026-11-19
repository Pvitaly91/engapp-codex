# M66 — навчальна редакція 1.0.2

Це явне уточнення відображуваного навчального заголовка після виявлення дефекту під час локального браузерного приймання 1.0.1. Історичні frozen master/UK 1.0.0 та 1.0.1 і їхня revision history не переписуються. База M66 залишається `c96e6b4e0c042f98fb0c105f61225f72d85a4850`.

## Причина та exact diff

У реальному браузері перша forms-картка Bare Infinitive мала intrinsic minimum width 367px через нерозривний перелік модальних слів. На viewport 390px і 320px це спричиняло learning-content overflow. За повідомленим браузерним аудитом дві інші сторінки цього learning overflow не мали. Ці спостереження пояснюють поправку, але не є повторним прийманням 1.0.2.

Єдиний змінений навчальний field: `lessons/0/blocks/1/data/items/0/title` (`bare-forms`, item 0).

- Було: `підмет + can/could/must/should/may/might/will + базова форма`.
- Стало: `підмет + can / could / must / should / may / might / will + базова форма`.

Пробіли біля `/` дозволяють чинному native-компоненту переносити формулу. Перелік слів, граматична модель і значення не змінені. Інші появи стислого переліку в body descriptions або summary не переписувалися.

Технічні master metadata changes: верхній `version` → `1.0.2`, новий `revision_history/1` з exact predecessor SHA та цією причиною. Попередній `revision_history/0` лишився exact. У новій UK-редакції змінено тільки поточний номер версії, цей один рядок формули й додано примітку про 1.0.2; примітка 1.0.1 збережена.

## Незмінний scope і межі виконання

Усі інші teaching fields, семантична поправка 1.0.1, EN/UK приклади, detail ownership, metadata/navigation, 18 власних practice tasks, answers/aliases/tokens/feedback та 15 reused widget questions незмінні. Нових питань або question-family writes немає. CSS, Blade, renderer, native practice engine та shared token formatter не змінюються.

Цим кроком створено тільки нові master/UK/revision files у ROOT і commit-worktree. Він не застосовує контент до БД і не переписує active package, patch, definitions, checksums, tests чи report. Точний finite body-only follow-up apply, backup/preview/postconditions/no-op і повторне live browser acceptance виконує головний агент окремо. Цей документ не називає їх PASS до фактичного виконання.

Редакційна перевірка агентська; зовнішнє human review не заявляється. До hash-freeze перевірено strict UTF-8 без BOM, LF, один кінцевий LF і відсутність trailing whitespace. Deep structural diff master містить тільки title/version/новий history entry.

## Raw-byte lineage

| Артефакт | SHA-256 |
| --- | --- |
| Незмінний master 1.0.1 | 1a3abd237a413f33f053726185450d9a814c5f01a3c12dfba370268cdc709e4e |
| Незмінна UK 1.0.1 | 73df282df87f34e4279db6e4b4587d3a609e8a8d4dab4388c5c57057bc9e0798 |
| Новий master 1.0.2 | 39f5a58004cd14dcb17818851ebd3442e1c84ba5535a8d748c1aeb51ab030e2c |
| Нова UK 1.0.2 | 7dd800082a40c2b410639a79b6008a90e20127e35843cd059a6170715a229761 |
