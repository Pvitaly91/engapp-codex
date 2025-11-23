# Page Tag Assignment Seeder

This seeder assigns tags from `config/tags/exported_tags.json` to pages and page categories based on the data in `config/pages/exported_pages.json`.

## Usage

To run this seeder, use the following artisan command:

```bash
php artisan db:seed --class=PageTagAssignmentSeeder
```

**Safe to run multiple times** - The seeder explicitly checks for existing tags before attaching new ones, ensuring no duplicates are created.

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
- Explicitly checks existing tag assignments before adding new ones
- Uses `attach()` only for tags that don't already exist
- Running the seeder multiple times will NOT create duplicate entries
- Existing tag assignments remain unchanged
- New tags are added only if they don't already exist
- Logs show how many tags were added vs. skipped

## Recent Updates (November 2025)

Enhanced tag assignments with improved structure and duplicate prevention:

### Tag Assignment Strategy
- **Categories**: Receive general, broad tags that encompass their entire theme
- **Pages**: Receive specific, detailed tags related to their exact content
- **Duplicate Prevention**: Explicit checking ensures no duplicate tag assignments on repeated runs

### Enhanced Mappings
- **Tense Pages**: Added detailed tags for each tense including practice exercises, forms, and usage contexts
  - Present Simple: 10 tags (including auxiliaries, exercises, forms)
  - Past Perfect: 11 tags (including mastery test, review, sequence markers)
  - Future tenses: Completion markers, duration emphasis
- **Modal Verb Pages**: Comprehensive modal tags covering ability, permission, obligation, advice, deduction
  - Can/Could: 12 tags (ability, permission, polite requests, past possibility)
  - Must/Have to: 11 tags (obligation, necessity, deduction, prohibition)
  - May/Might: 11 tags (probability, permission, speculation, formal usage)
- **Conditional Pages**: Enhanced with practice tags and usage contexts
- **Structure Pages**: Detailed tags for auxiliary verbs, contractions, verb forms
- **Category-Level Tags**: General tags covering entire category themes
- All referenced tags verified to exist in the system

## Mapping Strategy

### Category Mappings (General Tags)
Categories receive broad, general tags that encompass their theme:
- **tenses** → All 12 main tense tags + Tense: Present/Past/Future
- **conditions** → All conditional types + practice tags
- **modal-verbs** → All modal verb pairs + practice tags
- **articles-and-quantifiers** → Articles, quantifiers, some/any
- **adjectives** → All comparison-related tags
- **sentence-structures** → Contractions, there is/are, verb to be

### Page Mappings (Detailed Specific Tags)
Pages receive specific, detailed tags related to their exact content:
- **present-simple** → 10 tags: Present Simple + exercises + auxiliaries + forms
- **past-perfect** → 11 tags: Past Perfect + mastery test + sequence markers + forms
- **modal-verbs-can-could** → 12 tags: Can/Could + ability + permission + requests + past possibility
- **modal-verbs-must-have-to** → 11 tags: Must/Have to + obligation + necessity + deduction + prohibition
- **degrees-of-comparison** → 8 tags: Comparatives + superlatives + patterns + practice
- **verb-to-be** → 12 tags: Verb to be + negative/question forms for all tenses + auxiliaries

### Keyword Mappings (Fallback)
Used as fallback for items without direct/category mappings:
- Keywords in title/slug trigger relevant tag sets
- Supports both English and Ukrainian keywords

## Configuration

To modify the mappings, edit the following methods in `PageTagAssignmentSeeder.php`:
- `getCategoryMappings()`: Add or modify category-to-tags mappings (14 categories with general tags)
- `getDirectMappings()`: Add or modify page-to-tags mappings (42+ pages with detailed tags)
- `getKeywordMappings()`: Add or modify keyword-to-tags mappings (fallback matching)

## Notes

- The seeder uses case-insensitive matching for tag names (with `mb_strtolower()` for Unicode support)
- Duplicate tags are automatically prevented through explicit checking
- The seeder logs its progress to help track assignments
  - Shows how many new tags were added
  - Shows how many existing tags were skipped
- **It's safe to run multiple times** - explicit duplicate prevention ensures idempotency
- All referenced tags are validated to exist in the system
- Categories receive general, broad tags; Pages receive specific, detailed tags
