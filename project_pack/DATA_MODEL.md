# DATA_MODEL.md — Модель даних

## Основні сутності

### Users & Auth

| Таблиця | Модель | Призначення | Ключові поля |
|---------|--------|-------------|--------------|
| `users` | `User` | Користувачі системи | id, name, email, password, is_admin |
| `personal_access_tokens` | — | Sanctum токени | tokenable_type, tokenable_id, token |
| `password_reset_tokens` | — | Токени скидання пароля | email, token |

### Questions & Tests

| Таблиця | Модель | Призначення | Ключові поля |
|---------|--------|-------------|--------------|
| `questions` | `Question` | Граматичні питання | id, uuid, question, level, category_id, source_id, type |
| `question_answers` | `QuestionAnswer` | Правильні відповіді | question_id, marker, answer, option_id |
| `question_options` | `QuestionOption` | Варіанти відповідей | id, option |
| `question_option_question` | — | Pivot: питання-опції | question_id, option_id, flag |
| `question_hints` | `QuestionHint` | AI підказки | question_id, provider, locale, hint |
| `question_variants` | `QuestionVariant` | Варіанти питань | question_id, question, metadata |
| `verb_hints` | `VerbHint` | Підказки дієслів | question_id, marker, option_id |

### Saved Tests

| Таблиця | Модель | Призначення | Ключові поля |
|---------|--------|-------------|--------------|
| `saved_grammar_tests` | `SavedGrammarTest` | Збережені тести | id, uuid, name, slug, filters, description |
| `saved_grammar_test_questions` | `SavedGrammarTestQuestion` | Питання тесту | saved_grammar_test_id, question_uuid, position |

### Tags & Categories

| Таблиця | Модель | Призначення | Ключові поля |
|---------|--------|-------------|--------------|
| `tags` | `Tag` | Теги (часи, теми) | id, name, category |
| `question_tag` | — | Pivot: питання-теги | question_id, tag_id |
| `question_marker_tag` | — | Теги для маркерів | question_id, tag_id, marker |
| `categories` | `Category` | Категорії питань | id, name |
| `sources` | `Source` | Джерела питань | id, name |

### Pages & Theory

| Таблиця | Модель | Призначення | Ключові поля |
|---------|--------|-------------|--------------|
| `pages` | `Page` | Сторінки контенту | id, slug, title, text, type, page_category_id, seeder |
| `page_categories` | `PageCategory` | Категорії сторінок | id, title, slug, type, parent_id, language |
| `text_blocks` | `TextBlock` | Блоки тексту | id, uuid, page_id, type, body, column, locale, sort_order, level |
| `page_tag` | — | Pivot: сторінки-теги | page_id, tag_id |
| `page_category_tag` | — | Pivot: категорії-теги | page_category_id, tag_id |
| `tag_text_block` | — | Pivot: блоки-теги | tag_id, text_block_id |

### Words & Translations

| Таблиця | Модель | Призначення | Ключові поля |
|---------|--------|-------------|--------------|
| `words` | `Word` | Англійські слова | id, word, type (base/past/participle) |
| `translates` | `Translate` | Переклади слів | id, word_id, lang, translation |
| `tag_word` | — | Pivot: слова-теги | word_id, tag_id |

### Site Structure

| Таблиця | Модель | Призначення | Ключові поля |
|---------|--------|-------------|--------------|
| `site_tree_items` | `SiteTreeItem` | Елементи дерева сайту | id, variant_id, parent_id, title, sort_order, links |
| `site_tree_variants` | `SiteTreeVariant` | Варіанти дерева | id, slug, name, is_base |

### AI Explanations

| Таблиця | Модель | Призначення | Ключові поля |
|---------|--------|-------------|--------------|
| `chatgpt_explanations` | `ChatGPTExplanation` | Кешовані пояснення | question, wrong_answer, correct_answer, language, explanation |
| `chatgpt_translation_checks` | `ChatGPTTranslationCheck` | Перевірки перекладів | original, reference, user_text, language, is_correct, explanation |

### Other

| Таблиця | Модель | Призначення | Ключові поля |
|---------|--------|-------------|--------------|
| `tests` | `Test` | Legacy тести | id, name, slug |
| `sentences` | `Sentence` | Речення для перекладу | id, text, translation |
| `seed_runs` | — | Записи виконаних сідерів | id, seeder, executed_at |
| `question_review_results` | `QuestionReviewResult` | Результати рев'ю | question_id, result, comment |

---

## Зв'язки між моделями

### Question
```php
class Question extends Model
{
    // belongsTo
    public function category()     // -> Category
    public function source()       // -> Source
    public function theoryTextBlock() // -> TextBlock (via theory_text_block_uuid)
    
    // hasMany
    public function answers()      // -> QuestionAnswer[]
    public function hints()        // -> QuestionHint[]
    public function variants()     // -> QuestionVariant[]
    public function verbHints()    // -> VerbHint[]
    
    // belongsToMany
    public function tags()         // -> Tag[] (via question_tag)
    public function options()      // -> QuestionOption[] (via question_option_question)
    public function markerTags()   // -> Tag[] (via question_marker_tag with pivot marker)
}
```

### SavedGrammarTest
```php
class SavedGrammarTest extends Model
{
    // hasMany
    public function questionLinks() // -> SavedGrammarTestQuestion[]
    
    // Custom accessor
    public function getQuestionUuidsAttribute() // -> array of question UUIDs
}
```

### Page
```php
class Page extends Model
{
    // belongsTo
    public function category()     // -> PageCategory
    
    // hasMany
    public function textBlocks()   // -> TextBlock[]
    
    // belongsToMany
    public function tags()         // -> Tag[]
}
```

### Word
```php
class Word extends Model
{
    // hasMany
    public function translates()   // -> Translate[]
    
    // belongsToMany
    public function tags()         // -> Tag[]
    
    // Custom
    public function translate($lang = 'uk') // -> Translate (single)
}
```

---

## Де migrations і seeders

### Migrations
**Шлях**: `database/migrations/`

Ключові міграції:
- `2025_07_20_143210_create_quastion_table.php` — questions
- `2025_08_15_000000_create_saved_grammar_tests_tables.php` — saved_grammar_tests
- `2025_11_15_000001_create_page_tag_tables.php` — page tags
- `2025_07_18_182347_create_words_table.php` — words
- `2025_07_30_000001_create_tags_table.php` — tags
- `2025_11_30_131639_create_site_tree_items_table.php` — site tree

### Seeders
**Шлях**: `database/seeders/`

Структура:
```
database/seeders/
├── DatabaseSeeder.php           # Головний сідер
├── Page_v2/                     # Сідери сторінок теорії
│   ├── BasicGrammar/
│   ├── Articles/
│   └── ...
├── V1/, V2/                     # Версіоновані сідери питань
├── AI/, Ai/, chatGpt/           # AI-генеровані питання
├── DragDrop/                    # Drag & drop питання
├── TenseTagsSeeder.php          # Теги часів
├── WordsWithTranslationsSeeder.php # Слова з перекладами
└── SiteTreeSeeder.php           # Структура сайту
```

---

## Особливості

### Soft Deletes
**Не виявлено** — моделі не використовують `SoftDeletes` trait

### Polymorphic Relations
**Не виявлено** — стандартні belongsTo/hasMany зв'язки

### Pivot Tables з додатковими полями
- `question_option_question` — має поле `flag`
- `question_marker_tag` — має поле `marker`

### UUID як identifier
- `questions.uuid` — унікальний ідентифікатор питання
- `saved_grammar_tests.uuid` — унікальний ідентифікатор тесту
- `text_blocks.uuid` — унікальний ідентифікатор блоку
- `questions.theory_text_block_uuid` — зв'язок з теоретичним блоком

### Локалізація контенту
- `text_blocks.locale` — мова блоку (uk, en, pl)
- `question_hints.locale` — мова підказки
- `translates.lang` — мова перекладу слова
- `page_categories.language` — мова категорії

### Теги/Таксономії
- Теги (`tags`) зв'язуються з:
  - Questions (question_tag)
  - Words (tag_word)
  - Pages (page_tag)
  - PageCategories (page_category_tag)
  - TextBlocks (tag_text_block)
- `tags.category` — категоризація тегів (tense, topic, etc.)

### Ієрархія
- `page_categories.parent_id` — вкладені категорії
- `site_tree_items.parent_id` — дерево сайту

### Type-based differentiation
- `questions.type` — тип питання (1=match, 2=dialogue, 3=drag-drop)
- `words.type` — тип слова (base, past, participle)
- `pages.type` — тип сторінки (null, theory)
- `page_categories.type` — тип категорії (null, theory)
- `text_blocks.type` — тип блоку (subtitle, paragraph, table, etc.)
