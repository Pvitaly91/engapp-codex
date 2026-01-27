# PassiveVoiceAllTensesV2Seeder

## Опис
Цей сидер створює питання для практики пасивного стану в усіх часових формах англійської мови на рівнях B1-B2. Матеріал базується на тесті з https://test-english.com/grammar-points/b1-b2/passive-voice-all-tenses/

## Структура

### Page 1 (10 питань)
Тестування різних форм пасивного стану. Кожне питання має:
- 3 варіанти відповіді
- 1 правильна відповідь
- verb_hint з базовим дієсловом у дужках

**Форми пасиву:**
- Past Continuous Passive (was/were being + V3)
- Modal Perfect Passive (might/must + have been + V3)
- Present Perfect Passive (has/have been + V3)
- Past Perfect Passive (had been + V3)
- Second Conditional Passive (would + be + V3)
- Gerund Passive (being + V3)
- Future Passive with "be going to" (is/are going to be + V3)
- Present Continuous Passive (is/are being + V3)

### Page 2 (10 питань)
Продовження практики пасивного стану. Кожне питання включає:
- 4 варіанти відповіді
- 1 правильна відповідь
- verb_hint з базовим дієсловом

**Особливості:**
- Modal Perfect Passive (should have been + V3)
- Infinitive Passive (to be + V3) з "used to"
- Gerund Passive після дієслів емоцій (being + V3)
- Past Simple Passive у питаннях
- Active Voice (коли пасив не потрібен)
- Непереходні дієслова (disappear - не має пасивної форми)

### Page 3 (10 питань)
Формування пасивних конструкцій. Кожне питання містить:
- Базове дієслово у дужках
- 3-4 варіанти відповіді
- 1 правильна відповідь (іноді 2 правильні варіанти можливі)
- verb_hint з базовим дієсловом

**Типи завдань:**
- Gerund/Infinitive Passive після різних дієслів
- Past Continuous Passive
- Past Simple Passive
- Present Perfect Passive (negative)
- Third Conditional Passive (would have been + V3)
- Future Simple Passive
- Present Continuous Passive

## Технічні деталі

### Параметри питань
- **flag**: 0 (нові питання)
- **level**: B1, B2
- **category**: "Passive Voice"
- **source**: 
  - Custom: Passive Voice All Tenses V2 (Page 1)
  - Custom: Passive Voice All Tenses V2 (Page 2)
  - Custom: Passive Voice All Tenses V2 (Page 3)

### Tags (теги)
**Theme tag:**
- Passive Voice Practice

**Detail tag:**
- Passive Voice All Tenses

**Structure tag:**
- Passive Constructions

**Focus tag:**
- Active to Passive Transformation

### verb_hint
Формат: `(базове дієслово)`

verb_hint містить базову форму дієслова, наприклад:
- `(test)` - для "was being tested"
- `(inform)` - для "has been informed"
- `(eat)` - для "had been eaten"
- `(not fix)` - для негативних форм "has not been fixed"

**Важливо:** verb_hint показує, яке дієслово потрібно використати у пасивній формі.

### Hints (підказки)
Кожне питання має загальні підказки українською мовою:
- Формула пасивної конструкції (наприклад: **Past Continuous Passive** = was/were + being + V3)
- Коли використовується ця форма
- Приклад використання

**Приклад підказки:**
```
**Present Perfect Passive** = has/have + been + V3.
Використовується для дії, що завершилася з результатом у теперішньому.
Приклад: *The report has been completed.*
```

### Explanations (пояснення)
Кожен варіант відповіді має детальне пояснення українською мовою:
- ✅ для правильних відповідей з формулою та прикладом
- ❌ для неправильних з поясненням помилки та правильною формою

**Приклад пояснення:**
```
'was being tested' => '✅ Past Continuous Passive – дія відбувалася у момент вибуху. 
Формула: **was/were + being + V3**. 
Приклад: *The new drug was being tested when the accident happened.*'

'had being tested' => '❌ Неправильна форма – немає конструкції "had being". 
Правильна форма Past Perfect Passive: had been tested.'
```

## Використання методів з базового класу

Сидер використовує методи з `QuestionSeeder`:
- `generateQuestionUuid()` - генерація унікального UUID для питання
- `seedQuestionData()` - збереження питань в БД
- `attachHintsAndExplanations()` - прикріплення підказок та пояснень
- `formatHints()` - форматування підказок

### Власні методи
- `questionEntries()` - основний масив з усіма 30 питаннями
- `flattenOptions()` - перетворення масиву опцій у плоский список

## Логіка генерації

1. Створюється масив з 30 питаннями через метод `questionEntries()`
2. Кожне питання містить:
   - question - текст питання з маркером {a1}
   - verb_hints - базове дієслово у дужках
   - options - варіанти відповідей згруповані по маркерам
   - answers - правильна відповідь для кожного маркера
   - level - рівень складності (B1 або B2)
   - source - сторінка (page1, page2, page3)
   - hints - загальні підказки для питання
   - explanations - детальні пояснення для кожного варіанту
3. Всі дані збираються в масиви `$items` та `$meta`
4. Виконується збереження через `seedQuestionData()`
5. Прикріплюються підказки та пояснення через `attachHintsAndExplanations()`

## Приклад питання

### Вихідний формат (з оригінального масиву):
```php
[
    'q' => 'The new chemical _____ when it exploded.',
    'a' => 'was being tested',
    'options' => ['had being tested', 'was testing', 'was being tested'],
]
```

### Формат у сидері:
```php
[
    'question' => 'The new chemical {a1} when it exploded.',
    'verb_hints' => ['a1' => '(test)'],
    'options' => [
        'a1' => ['was being tested', 'had being tested', 'was testing'],
    ],
    'answers' => ['a1' => 'was being tested'],
    'level' => 'B1',
    'source' => 'page1',
    'hints' => [
        '**Past Continuous Passive** = was/were + being + V3.',
        'Використовується для дії, що відбувалася у момент у минулому.',
        'Приклад: *The house was being painted when I arrived.*',
    ],
    'explanations' => [
        'a1' => [
            'was being tested' => '✅ Past Continuous Passive – дія відбувалася у момент вибуху...',
            'had being tested' => '❌ Неправильна форма – немає конструкції "had being"...',
            'was testing' => '❌ Active Voice (Past Continuous) – але хімічна речовина була об\'єктом...',
        ],
    ],
]
```

### Результат у БД:
- UUID: автоматично згенерований на основі класу та номера питання
- question: "The new chemical {a1} when it exploded."
- answers: [['marker' => 'a1', 'answer' => 'was being tested', 'verb_hint' => '(test)']]
- options: ['was being tested', 'had being tested', 'was testing']
- category_id: ID категорії "Passive Voice"
- difficulty: 3 (B1 рівень)
- level: 'B1'
- hints: Текст підказок з formatHints()
- explanations: Збережені в таблиці ChatGPTExplanation для кожного варіанту

## Особливості реалізації

### Різноманітність форм пасиву
Сидер охоплює всі основні форми пасивного стану:
- Simple (Present/Past/Future)
- Continuous (Present/Past)
- Perfect (Present/Past/Future)
- Modals (must/might/should/could + have been + V3)
- Conditionals (Second/Third)
- Infinitive (to be + V3)
- Gerund (being + V3)

### Непереходні дієслова
Особлива увага приділена дієсловам, які не мають пасивної форми:
- "disappear" - використовується Active Voice у Past Perfect
- Пояснення чому пасивна форма неможлива

### Active Voice vs Passive Voice
Деякі питання перевіряють розуміння, коли потрібен активний, а не пасивний стан:
- "The school doesn't normally provide accommodation" (Active)
- Пояснення, чому тут потрібна активна форма

## Запуск сидера

```bash
php artisan db:seed --class="Database\Seeders\V2\PassiveVoiceAllTensesV2Seeder"
```

## Статистика

- **Загальна кількість питань**: 30
- **Рівень**: B1-B2
- **Маркери**: {a1} для всіх питань
- **Варіанти відповідей**: 3-4 на питання
- **Підказки**: 3 підказки на питання
- **Пояснення**: для кожного варіанту відповіді (90+ пояснень)
- **Джерела**: 3 сторінки (page1, page2, page3)
