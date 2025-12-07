# QuestionsDifferentTypesV2Seeder

## Опис
Цей сидер створює питання для практики різних типів питань в англійській мові на основі матеріалів з https://test-english.com/grammar-points/b1-b2/questions-different-types/

## Структура

### Exercise 1 (10 питань)
Вибір правильної форми для завершення питання. Кожне питання має:
- 3 варіанти відповіді
- 1 правильна відповідь
- verb_hint з підказкою про тип питання

**Типи питань:**
- Embedded questions (непрямі питання)
- Wh-questions з прийменниками
- Subject questions (питання про підмет)
- Question word order (порядок слів у питаннях)

### Exercise 2 (10 питань)
Змішані типи питань, включає:
- 3 варіанти відповіді
- 1 правильна відповідь (9 питань)
- 2 правильні відповіді (1 питання - питання 3)
- verb_hint з підказкою

**Особливості:**
- Питання 3 має два правильні варіанти: "has she not replied" та "hasn't she replied"
- Різні структури: embedded questions, negative questions, subject questions

### Exercise 3 (10 питань)
Формування питань про підкреслені слова. Кожне питання включає:
- Оригінальне речення з підкресленими словами
- 4 варіанти відповіді
- 1 правильна відповідь
- verb_hint з типом питання

**Типи питань:**
- How many/How much questions
- What questions (object questions)
- How often/How long questions
- Who questions з прийменниками
- Embedded questions (вбудовані питання)

## Технічні деталі

### Параметри питань
- **flag**: 0 (нові питання)
- **level**: B1, B2
- **category**: "Questions - Different Types"
- **source**: https://test-english.com/grammar-points/b1-b2/questions-different-types/

### Tags (теги)
**Theme tag:**
- Question Formation Practice

**Detail tags:**
- Indirect Questions
- Embedded Questions
- Wh-Questions Formation
- Question Word Order
- Subject Questions

### verb_hint
Формат: `(підказка)`

verb_hint містить короткі підказки про тип питання, наприклад:
- `(embedded question word order)` - для непрямих питань
- `(preposition at end)` - для питань з прийменником в кінці
- `(subject question)` - для питань про підмет
- `(auxiliary + subject + verb)` - для стандартних питань

**Важливо:** verb_hint НЕ містить правильну відповідь, тільки підказку про структуру.

### Explanations (пояснення)
Кожен варіант відповіді має пояснення українською мовою:
- ✅ для правильних відповідей з прикладом
- ❌ для неправильних з поясненням помилки та правильною відповіддю

## Використання методів з базового класу

Сидер використовує методи з `QuestionSeeder`:
- `generateQuestionUuid()` - генерація унікального UUID для питання
- `seedQuestionData()` - збереження питань в БД
- `normalizeHint()` - нормалізація verb_hint
- `formatExample()` - форматування прикладів

## Логіка генерації

1. Створюються три масиви: `$exercise1`, `$exercise2`, `$exercise3`
2. Кожне питання обробляється через `processQuestion()` або `processExercise3Question()`
3. Генеруються пояснення для кожного варіанту відповіді
4. Створюються підказки (hints) з прикладами
5. Всі дані збираються в масиви `$items` та `$meta`
6. Виконується збереження через `seedQuestionData()`

## Приклад питання

```php
[
    'level' => 'B1',
    'question' => "Tom has gone out, but I don't know {a1}.",
    'correct' => 'where he has gone',
    'options' => [
        'where he has gone',
        'where has he gone',
        'where gone he has',
    ],
    'verb_hint' => 'embedded question word order',
    'detail' => 'embedded_questions',
]
```

Це питання перетворюється в:
- UUID: автоматично згенерований
- question: "Tom has gone out, but I don't know {a1}."
- answers: [['marker' => 'a1', 'answer' => 'where he has gone', 'verb_hint' => 'embedded question word order']]
- options: ['where he has gone', 'where has he gone', 'where gone he has']
- explanations: по одному для кожного варіанту
- hints: "Підказка: embedded question word order. \nПриклад правильної відповіді: *Tom has gone out, but I don't know where he has gone.*"
- flag: 0
