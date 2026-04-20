# Polyglot V3 Lesson Generator Workflow

This doc is the contract for the repo-level authoring workflow behind:

```bash
php artisan polyglot:generate-v3-prompt {theoryCategorySlug} {theoryPageSlug} {lessonSlug} {lessonOrder}
```

The command does not call any AI API. It resolves a real theory page from `database/seeders/Page_V3`, pulls the current Polyglot V3 conventions from the repo, generates a ready-to-copy Codex prompt, and can optionally scaffold a canonical V3 Polyglot package with `--write-skeleton`.

## CLI Workflow

Supported options:

```bash
php artisan polyglot:generate-v3-prompt verb-to-be verb-to-be-present polyglot-sample-v3-lesson 3 \
  --title="Polyglot Sample Lesson" \
  --topic="verb to be" \
  --seeder=PolyglotSampleLessonSeeder \
  --course=polyglot-english-a1 \
  --level=A1 \
  --previous=polyglot-there-is-there-are-a1 \
  --items=24 \
  --output=storage/app/polyglot-prompts/polyglot-sample-v3-lesson.txt \
  --write-skeleton
```

Notes:

1. Without `--output`, the command prints the generated prompt to stdout.
2. With `--output`, it writes the wrapped prompt to a file.
3. With `--write-skeleton`, it creates the canonical package structure under `database/seeders/V3/Polyglot`.
4. Without `--force`, existing output/scaffold files are not overwritten.

## Generated Prompt Contract

The generated prompt must already contain these wrappers:

```text
PROMPT ID: GLZ-PROMPT-....

Codex Summary (Top):
- Мета:
- Що саме зробити:
- Ключові обмеження / адаптації:
- Підсумковий результат:

...

Codex Summary (Bottom):
- Мета:
- Що саме зробити:
- Ключові обмеження / адаптації:
- Підсумковий результат:

PROMPT ID: GLZ-PROMPT-....
```

And inside the prompt body there must be a `FORMAT OF YOUR RESPONSE — REQUIRED` section that forces Codex to repeat the same Prompt ID and Codex Summary at the top and bottom of its own final answer.

## Canonical V3 Polyglot File Layout

Use the existing repo pattern already present in `database/seeders/V3/Polyglot`:

```text
database/seeders/V3/Polyglot/<SeederClassName>.php
database/seeders/V3/Polyglot/<SeederClassName>/<SeederClassName>.php
database/seeders/V3/Polyglot/<SeederClassName>/definition.json
database/seeders/V3/Polyglot/<SeederClassName>/localizations/uk.json
database/seeders/V3/Polyglot/<SeederClassName>/localizations/en.json
database/seeders/V3/Polyglot/<SeederClassName>/localizations/pl.json
```

The top-level PHP file is only a loader stub. The real seeder class lives inside the package directory.

Current canonical references:

1. `database/seeders/V3/Polyglot/PolyglotToBeLessonSeeder.php`
2. `database/seeders/V3/Polyglot/PolyglotToBeLessonSeeder/PolyglotToBeLessonSeeder.php`
3. `database/seeders/V3/Polyglot/PolyglotToBeLessonSeeder/definition.json`
4. `database/seeders/V3/Polyglot/PolyglotToBeLessonSeeder/localizations/uk.json`
5. `database/seeders/V3/Polyglot/PolyglotThereIsThereAreLessonSeeder.php`
6. `database/seeders/V3/Polyglot/PolyglotThereIsThereAreLessonSeeder/definition.json`

`database/seeders/V2/Polyglot/*` are thin bridges only and must not be treated as the canonical source of truth.

## Seeder Class Convention

Real package seeder:

```php
<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\JsonTestSeeder;

class <SeederClassName> extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
```

Top-level loader stub:

```php
<?php

require_once __DIR__ . '/<SeederClassName>/<SeederClassName>.php';
```

## Required `definition.json` Shape

The final lesson package must follow the real V3 schema consumed by `App\Support\Database\JsonTestSeeder`:

```json
{
  "schema_version": 1,
  "seeder": {
    "class": "Database\\Seeders\\V3\\Polyglot\\<SeederClassName>",
    "uuid_namespace": "<SeederClassName>"
  },
  "defaults": {
    "default_locale": "uk",
    "flag": 0,
    "type": 4,
    "level_difficulty": {
      "A1": 1,
      "A2": 2,
      "B1": 3,
      "B2": 4,
      "C1": 5,
      "C2": 5
    }
  },
  "category": {
    "name": "Polyglot English A1"
  },
  "sources": {
    "<source_key>": {
      "name": "<Theory Page Title>"
    }
  },
  "tags": {
    "polyglot_compose_tokens": {
      "name": "Polyglot compose tokens",
      "category": "mode"
    }
  },
  "default_tag_keys": [
    "polyglot_compose_tokens"
  ],
  "questions": [],
  "saved_test": {}
}
```

## Theory Binding Fields

For direct Polyglot lessons, the current accepted metadata shape is:

```json
{
  "source_type": "theory_page",
  "theory_page": {
    "slug": "theory-page-slug",
    "title": "Theory Page Title",
    "category_slug_path": "root-category/child-category",
    "page_seeder_class": "Database\\Seeders\\Page_V3\\...\\TheorySeeder",
    "url": "https://gramlyze.com/theory/child-category/theory-page-slug"
  }
}
```

Required keys:

1. `slug`
2. `title`
3. `category_slug_path`
4. `page_seeder_class`
5. `url`

Do not introduce another theory-linking system when the theory page already exists.

## Authoring JSON Contract

When you draft Polyglot sentence items before mapping them into the final V3 `definition.json`, keep the real import/source contract in mind:

1. `source_text_uk`
2. `target_text`
3. `tokens_correct`
4. `distractors`
5. `hint_uk`
6. `grammar_tags`
7. optional `distractor_explanations_uk`

Compatibility rules:

1. Compose mode is `compose_tokens`.
2. `question_type` is `4`.
3. Duplicate correct tokens must remain preserved in the ordered answer sequence.
4. The compose payload builder still derives punctuation from the Ukrainian source sentence.

## Localization JSON Convention

Use the package-local localization pattern already used by the canonical Polyglot V3 lessons. Hints and distractor explanations live in `localizations/*.json`, not inline in the PHP class.

Canonical Ukrainian file:

```json
{
  "schema_version": 1,
  "seeder": {
    "class": "Database\\Seeders\\V3\\Localizations\\Uk\\Polyglot\\<LocalizationSeederClass>"
  },
  "target": {
    "seeder_class": "Database\\Seeders\\V3\\Polyglot\\<SeederClassName>",
    "definition_path": "../definition.json"
  },
  "locale": "uk",
  "hint_provider": "polyglot-v3",
  "questions": [
    {
      "id": 1,
      "hints": [
        "Short authored hint in Ukrainian."
      ],
      "explanations": {
        "wrong_token": "Short explanation in Ukrainian."
      }
    }
  ]
}
```

Notes:

1. `saved_test.name` and `saved_test.description` stay in `definition.json`.
2. `uk.json` is the authoritative authored feedback file for Polyglot lessons.
3. `en.json` and `pl.json` may legitimately keep empty `questions` arrays.

## Validation Rules

Before finalizing a lesson, verify:

1. `saved_test.filters.mode` is `compose_tokens`.
2. `saved_test.filters.question_type` is `4`.
3. Every question has a unique `uuid`.
4. Every question has non-empty ordered `answers`.
5. `question_uuids` exactly match the seeded question UUID set when they are present.
6. Duplicate correct tokens are preserved in `answers`.
7. `prompt_generator.theory_page.page_seeder_class` points to a real page seeder in the repo.
8. The lesson course graph fields are correct:
   - `course_slug`
   - `lesson_order`
   - `previous_lesson_slug`
   - `next_lesson_slug`

## Final Checklist

1. `composer dump-autoload`
2. `php artisan db:seed --class='Database\\Seeders\\V3\\Polyglot\\<SeederClassName>'`
3. `php artisan test --filter=Polyglot`
4. `php artisan test --filter=ComposePayloadBuilderTest`
5. `php artisan view:cache`
6. Open:
   - `/test/<slug>/step/compose`
   - `/courses/polyglot-english-a1`
   - the bound theory page route
