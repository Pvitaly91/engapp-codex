# Page Tag Assignment Seeder

This seeder assigns tags from `config/tags/exported_tags.json` to pages and page categories based on the data in `config/pages/exported_pages.json`.

## Usage

To run this seeder, use the following artisan command:

```bash
php artisan db:seed --class=PageTagAssignmentSeeder
```

**Safe to run multiple times** - The seeder uses `syncWithoutDetaching()` which prevents duplicate tag assignments.

## What it does

The seeder:
1. Reads all available tags from the exported tags JSON file
2. Reads all pages and categories from the exported pages JSON file
3. Matches tags to pages and categories based on:
   - **Direct slug mappings**: For specific pages (e.g., "present-simple" → "Present Simple" tag)
   - **Keyword-based matching**: For categories (e.g., "Часи" category → all tense-related tags)
4. Uses `syncWithoutDetaching()` to add tags without removing existing assignments

## Duplicate Prevention

The seeder is designed to prevent duplicate tags:
- Uses Laravel's `syncWithoutDetaching()` method which only adds new tags
- Running the seeder multiple times will NOT create duplicate entries
- Existing tag assignments remain unchanged
- New tags are added only if they don't already exist

## Recent Updates (November 2025)

Enhanced tag assignments with 50+ additional tags:
- **verb-to-be**: Added 10 tags including "To be negative/question" forms
- **there-is-there-are**: Added 9 tags including "There isn't/aren't"
- **question-forms**: Added question-related tags for different tenses
- **modal-verbs**: Added general Modal Verbs tags to all modal pages
- **pronouns**: Updated with Indefinite Pronoun tags
- **some-any**: Enhanced with compound tags
- **degrees-of-comparison**: Added practice tags
- Removed non-existent tags to prevent errors
- All 243 tags verified to exist in the system

## Mapping Strategy

### Direct Mappings (Pages)
Pages are matched using their slug. For example:
- `present-simple` → ["Present Simple", "Present Simple Do/Does Choice", "Present Simple To Be Choice"]
- `verb-to-be` → 10 tags including "To be negative (present/past/future)", "To be question (present/past/future)"
- `modal-verbs-can-could` → ["Can / Could", "Can", "Modal Verbs Practice", "Modal Verbs"]
- `degrees-of-comparison` → 8 tags including comparatives, superlatives, and practice exercises

### Keyword Mappings (Categories)
Categories are matched using keywords in their title or slug. For example:
- Title contains "Часи" or "tenses" → All 12 main tense tags
- Title contains "Умовні" or "conditions" → All conditional tags
- Title contains "Модальні" or "modal" → All modal verb tags
- Title contains "Прикметник" or "comparison" → Comparison and practice tags

## Configuration

To modify the mappings, edit the following methods in `PageTagAssignmentSeeder.php`:
- `getDirectMappings()`: Add or modify page-to-tags mappings (42 pages mapped)
- `getKeywordMappings()`: Add or modify keyword-to-tags mappings (25 keywords mapped)

## Notes

- The seeder uses case-insensitive matching for tag names
- Duplicate tags are automatically removed from results
- The seeder logs its progress to help track assignments
- **It's safe to run multiple times** - guaranteed by `syncWithoutDetaching()`
- All referenced tags are validated to exist in the system
