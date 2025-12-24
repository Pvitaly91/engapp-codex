# Polish Translation Fill Command

## Overview

This command fills Polish translations in the `public/exports/words/words_pl.json` export file using AI-powered translation via Gemini API.

## Usage

### Basic Usage

```bash
php artisan words:fill-pl-export
```

### Options

- `--batch-size=50` - Number of words to translate in each batch (default: 50)
- `--no-db` - Skip database synchronization
- `--dry-run` - Run without saving changes (for testing)

### Examples

```bash
# Translate with default batch size
php artisan words:fill-pl-export

# Translate with larger batches (faster but more API usage)
php artisan words:fill-pl-export --batch-size=100

# Test without saving
php artisan words:fill-pl-export --dry-run

# Skip database sync
php artisan words:fill-pl-export --no-db
```

## Configuration

The command uses the Gemini API for translations. Make sure your `.env` file contains:

```env
GEMINI_API_KEY=your-api-key-here
GEMINI_MODEL=gemini-2.0-flash-exp
GEMINI_TIMEOUT=60
GEMINI_MAX_RETRIES=3
```

You can also configure timeouts and retries in `config/services.php`.

## How It Works

1. **Read**: Loads `public/exports/words/words_pl.json`
2. **Identify**: Finds all entries where `translation === null`
3. **Translate**: Sends words to Gemini API in batches
4. **Validate**: Checks translations to ensure they're not just transliterations
5. **Update**: Moves translated words to `with_translation` array
6. **Sync**: Updates database `translates` table (unless `--no-db`)
7. **Report**: Shows statistics and saves detailed log

## Translation Rules

The translation service follows these rules:

1. **Actual translations**, not transliterations
2. **Nouns**: Base form (singular, nominative case)
3. **Verbs**: Infinitive form
4. **Phrases**: Natural translation preserving meaning
5. **Proper names/brands**: Keep as-is (e.g., "Google", "London")
6. **Loanwords**: May keep if naturally used in Polish (e.g., "pizza", "radio")

## Validation

The command validates translations to detect transliteration vs proper translation:

- If translation matches the original word (case-insensitive), it verifies if it's a proper Polish loanword
- Suspicious translations are marked and can be reviewed
- Failed translations remain `null` and are listed in the report

## Output

The command provides:

- Progress bar showing translation progress
- Final statistics:
  - Translations added
  - Translations remaining null
  - Failed words list
  - Suspicious words list
- Detailed report saved to `storage/logs/pl_translation_report_*.txt`

## File Structure

The `words_pl.json` file has this structure:

```json
{
    "exported_at": "2025-12-24T16:53:08+00:00",
    "lang": "pl",
    "counts": {
        "total_words": 6426,
        "with_translation": 20,
        "without_translation": 6406
    },
    "with_translation": [
        {
            "id": 1,
            "word": "all",
            "translation": "wszyscy",
            "type": null,
            "tags": []
        }
    ],
    "without_translation": [
        {
            "id": 100,
            "word": "unknown",
            "translation": null,
            "type": null,
            "tags": []
        }
    ]
}
```

## Database Synchronization

When database sync is enabled (default), the command:

1. Creates new `translates` records for successful translations
2. Updates existing records if they have empty translations
3. Skips records with existing non-empty translations
4. Runs all operations in a transaction for data integrity

## Rate Limiting

The command includes built-in rate limiting:

- 0.5 second delay between batches
- Configurable batch size (default 50 words)
- Retry logic with exponential backoff

## Troubleshooting

### API Key Issues

If you get authentication errors:

```bash
# Check your API key
grep GEMINI_API_KEY .env
```

### Large Files

If you have many words to translate:

```bash
# Use larger batches
php artisan words:fill-pl-export --batch-size=100

# Skip database sync for faster operation
php artisan words:fill-pl-export --no-db
```

### Testing

Before running on production:

```bash
# Test with dry-run
php artisan words:fill-pl-export --dry-run
```

## Caching

The translation service caches results in memory during execution to avoid re-translating the same words within a single run.

## Cost Considerations

- Each API call translates a batch of words (default 50)
- Total API calls = `ceil(words_to_translate / batch_size)`
- For 6,426 words with batch size 50: ~129 API calls
- Gemini API pricing varies - check current rates

## Related Commands

- `php artisan words:scan` - Scan questions and store unique words
- See `app/Http/Controllers/Admin/WordsExportController.php` for web-based export/import

## Files Modified

- `public/exports/words/words_pl.json` - Updated with translations
- `translates` table - Synced with new translations (unless `--no-db`)
- `storage/logs/pl_translation_report_*.txt` - Detailed report

## Support

For issues or questions, refer to:
- `app/Console/Commands/FillPolishTranslationsCommand.php` - Command implementation
- `app/Services/PolishTranslationService.php` - Translation service
