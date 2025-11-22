# Page Tag Assignment Seeder

This seeder assigns tags from `config/tags/exported_tags.json` to pages and page categories based on the data in `config/pages/exported_pages.json`.

## Usage

To run this seeder, use the following artisan command:

```bash
php artisan db:seed --class=PageTagAssignmentSeeder
```

## What it does

The seeder:
1. Reads all available tags from the exported tags JSON file
2. Reads all pages and categories from the exported pages JSON file
3. Matches tags to pages and categories based on:
   - **Direct slug mappings**: For specific pages (e.g., "present-simple" → "Present Simple" tag)
   - **Keyword-based matching**: For categories (e.g., "Часи" category → all tense-related tags)
4. Uses `syncWithoutDetaching()` to add tags without removing existing assignments

## Mapping Strategy

### Direct Mappings (Pages)
Pages are matched using their slug. For example:
- `present-simple` → ["Present Simple", "Present Simple Do/Does Choice", "Present Simple To Be Choice"]
- `first-conditional` → ["First Conditional", "First Conditional Sentences", "First Conditional Clause Completion"]
- `modal-verbs-can-could` → ["Can / Could", "Ability (can/could)", "Permission (can/could)"]

### Keyword Mappings (Categories)
Categories are matched using keywords in their title or slug. For example:
- Title contains "Часи" or "tenses" → All 12 main tense tags
- Title contains "Умовні" or "conditions" → All conditional tags
- Title contains "Модальні" or "modal" → All modal verb tags

## Configuration

To modify the mappings, edit the following methods in `PageTagAssignmentSeeder.php`:
- `getDirectMappings()`: Add or modify page-to-tags mappings
- `getKeywordMappings()`: Add or modify keyword-to-tags mappings

## Notes

- The seeder uses case-insensitive matching for tag names
- Duplicate tags are automatically removed
- The seeder logs its progress to help track assignments
- It's safe to run multiple times as it uses `syncWithoutDetaching()`
