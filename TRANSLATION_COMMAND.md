# Translation Fill Command (Universal)

## Overview

This command fills translations in word export files for any supported language using AI-powered translation via **Gemini API** or **OpenAI/ChatGPT API**.

## Usage

### Basic Usage

```bash
php artisan words:fill-export {lang}
```

### Supported Languages

- `uk` - Ukrainian
- `pl` - Polish  
- `en` - English
- `de` - German
- `fr` - French
- `es` - Spanish
- `it` - Italian

### Options

- `--provider=auto` - Translation provider (auto, gemini, openai) - default: auto
- `--batch-size=50` - Number of words to translate in each batch (default: 50)
- `--no-db` - Skip database synchronization
- `--dry-run` - Run without saving changes (for testing)

### Examples

```bash
# Fill Polish translations (auto-detect provider)
php artisan words:fill-export pl

# Fill Ukrainian translations using ChatGPT/OpenAI
php artisan words:fill-export uk --provider=openai

# Fill German translations using Gemini
php artisan words:fill-export de --provider=gemini

# Fill with larger batches
php artisan words:fill-export pl --batch-size=100

# Test without saving
php artisan words:fill-export pl --dry-run

# Skip database sync
php artisan words:fill-export pl --no-db
```

## Configuration

The command supports two translation providers:

### Option 1: Gemini API (Google)

```env
GEMINI_API_KEY=your-api-key-here
GEMINI_MODEL=gemini-2.0-flash-exp
GEMINI_TIMEOUT=60
GEMINI_MAX_RETRIES=3
```

Get API key from: https://makersuite.google.com/app/apikey

### Option 2: OpenAI/ChatGPT API

```env
CHAT_GPT_API_KEY=your-api-key-here
# OR
OPENAI_API_KEY=your-api-key-here

OPENAI_MODEL=gpt-4o-mini
OPENAI_TIMEOUT=60
OPENAI_MAX_RETRIES=3
```

Get API key from: https://platform.openai.com/api-keys

### Provider Selection

- `--provider=auto` (default) - Automatically uses Gemini if available, otherwise OpenAI
- `--provider=gemini` - Force use of Gemini API
- `--provider=openai` - Force use of OpenAI/ChatGPT API

You can also configure timeouts and retries in `config/services.php`.

## How It Works

1. **Read**: Loads `public/exports/words/words_{lang}.json`
2. **Identify**: Finds all entries where `translation === null`
3. **Translate**: Sends words to Gemini API in batches
4. **Validate**: Checks translations to ensure they're not just transliterations
5. **Update**: Moves translated words to `with_translation` array
6. **Sync**: Updates database `translates` table (unless `--no-db`)
7. **Report**: Shows statistics and saves detailed log

## Translation Rules

The translation service follows these rules for all languages:

1. **Actual translations**, not transliterations
2. **Nouns**: Base form (singular, nominative case)
3. **Verbs**: Infinitive form
4. **Phrases**: Natural translation preserving meaning
5. **Proper names/brands**: Keep as-is (e.g., "Google", "London")
6. **Loanwords**: May keep if naturally used in target language (e.g., "pizza", "radio")

## Validation

The command validates translations to detect transliteration vs proper translation:

- If translation matches the original word (case-insensitive), it verifies if it's a proper loanword
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
- Detailed report saved to `storage/logs/{lang}_translation_report_*.txt`

## File Structure

The `words_{lang}.json` file has this structure:

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

The command includes built-in rate limiting to avoid API quota issues:

### Gemini API
- 0.5 second delay between batches
- Default batch size: 50 words
- More generous rate limits

### OpenAI/ChatGPT API
- 2 second delay between batches (longer to avoid 429 errors)
- **Recommended batch size: 10-20 words** (stricter rate limits)
- Automatic retry with exponential backoff on 429 errors
- Free tier accounts have lower quotas

**Important for OpenAI users:**
If you encounter "You exceeded your current quota" (429 error), use a smaller batch size:

```bash
# Use smaller batches for OpenAI
php artisan words:fill-export pl --provider=openai --batch-size=10

# Or even smaller if you're on free tier
php artisan words:fill-export pl --provider=openai --batch-size=5
```

The command will warn you if you're using OpenAI with a large batch size.
- Retry logic with exponential backoff

## Creating Export Files

Before using this command, you need to create the export file for your target language:

1. Go to Admin Panel → Words Export
2. Select the target language
3. Click "Export" to generate `words_{lang}.json`

Alternatively, use the existing WordsExportController to export programmatically.

## Backward Compatibility

The old Polish-specific command is deprecated but still available:

```bash
# Old command (deprecated)
php artisan words:fill-pl-export

# New universal command (recommended)
php artisan words:fill-export pl
```

## Troubleshooting

### API Key Issues

If you get authentication errors:

```bash
# Check your API key
grep GEMINI_API_KEY .env
```

### Language Not Supported

If you need to add a new language:

1. Add the language code to `ALLOWED_LANGS` in `FillTranslationsCommand.php`
2. Add the language name to `LANGUAGE_NAMES` in `TranslationService.php`
3. Create the export file via admin panel

### Large Files

If you have many words to translate:

```bash
# Use larger batches
php artisan words:fill-export pl --batch-size=100

# Skip database sync for faster operation
php artisan words:fill-export pl --no-db
```

### Testing

Before running on production:

```bash
# Test with dry-run
php artisan words:fill-export pl --dry-run
```

## Cost Considerations

- Each API call translates a batch of words (default 50)
- Total API calls = `ceil(words_to_translate / batch_size)`
- For 6,426 words with batch size 50: ~129 API calls
- Gemini API pricing varies - check current rates

## Related Commands

- `php artisan words:scan` - Scan questions and store unique words
- See `app/Http/Controllers/Admin/WordsExportController.php` for web-based export/import

## Files Modified

- `public/exports/words/words_{lang}.json` - Updated with translations
- `translates` table - Synced with new translations (unless `--no-db`)
- `storage/logs/{lang}_translation_report_*.txt` - Detailed report

## Support

For issues or questions, refer to:
- `app/Console/Commands/FillTranslationsCommand.php` - Command implementation
- `app/Services/TranslationService.php` - Translation service
