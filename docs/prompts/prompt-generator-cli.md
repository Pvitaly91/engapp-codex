# Prompt Generator CLI

## Classic V3

### Manual topic

```bash
php artisan v3:generate-prompt \
  --manual-topic="Passive Voice" \
  --target-namespace="AI\\ChatGptPro" \
  --level=A1 \
  --level=B1 \
  --questions-per-level=4
```

### Theory page

```bash
php artisan v3:generate-prompt \
  --source-type=theory_page \
  --theory-page-id=123 \
  --site-domain=gramlyze.com \
  --target-namespace="AI\\ChatGptPro" \
  --level=A2 \
  --questions-per-level=5
```

### External URL

```bash
php artisan v3:generate-prompt \
  --source-type=external_url \
  --external-url="https://example.com/grammar/passive-voice" \
  --target-namespace="AI\\ChatGptPro" \
  --level=B2 \
  --questions-per-level=3 \
  --generation-mode=split \
  --prompt-a-mode=no_repository
```

### Write scaffold package

```bash
php artisan v3:generate-prompt \
  --manual-topic="Passive Voice" \
  --target-namespace="AI\\ChatGptPro" \
  --level=A1 \
  --level=B1 \
  --questions-per-level=4 \
  --write-skeleton
```

### Theory page + scaffold package

```bash
php artisan v3:generate-prompt \
  --source-type=theory_page \
  --theory-page-id=123 \
  --site-domain=gramlyze.com \
  --target-namespace="AI\\ChatGptPro" \
  --level=A2 \
  --questions-per-level=5 \
  --write-skeleton
```

## Page_V3

### Existing category

```bash
php artisan page-v3:generate-prompt \
  --manual-topic="Passive Voice Causative" \
  --category-mode=existing \
  --existing-category-id=15
```

### New category

```bash
php artisan page-v3:generate-prompt \
  --source-type=external_url \
  --external-url="https://example.com/grammar/alternative-questions" \
  --category-mode=new \
  --new-category-title="Types of Questions" \
  --generation-mode=split
```

### Existing category + page scaffold

```bash
php artisan page-v3:generate-prompt \
  --manual-topic="Passive Voice Causative" \
  --category-mode=existing \
  --existing-category-id=15 \
  --write-skeleton
```

### New category + page/category scaffold

```bash
php artisan page-v3:generate-prompt \
  --manual-topic="Alternative Questions" \
  --category-mode=new \
  --new-category-title="Types of Questions" \
  --write-skeleton
```

### `ai_select` refusal for scaffold writing

```bash
php artisan page-v3:generate-prompt \
  --manual-topic="Reported Speech" \
  --category-mode=ai_select \
  --write-skeleton
```

`ai_select` prompt generation still works, but scaffold writing fails early until the category is resolved explicitly through `existing` or `new`.

## Output Modes

### JSON output

```bash
php artisan v3:generate-prompt \
  --manual-topic="Plural Nouns" \
  --target-namespace="AI\\ChatGptPro" \
  --level=A1 \
  --format=json
```

### Consolidated output file

```bash
php artisan page-v3:generate-prompt \
  --manual-topic="Reported Speech" \
  --category-mode=ai_select \
  --output=storage/app/prompts/page-v3-reported-speech.txt
```

### Per-prompt files

```bash
php artisan v3:generate-prompt \
  --source-type=external_url \
  --external-url="https://example.com/grammar/passive-voice" \
  --target-namespace="AI\\ChatGptPro" \
  --level=B2 \
  --generation-mode=split \
  --write-dir=storage/app/prompts/v3-split \
  --force
```

### Combined consolidated output + scaffold writing

```bash
php artisan v3:generate-prompt \
  --manual-topic="Plural Nouns" \
  --target-namespace="AI\\ChatGptPro" \
  --level=A1 \
  --output=storage/app/prompts/v3-plural-nouns.txt \
  --write-skeleton \
  --force
```

## Release Checks

### V3 scaffold preflight

```bash
php artisan v3:release-check \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder \
  --profile=scaffold
```

### V3 release-ready JSON check

```bash
php artisan v3:release-check \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/definition.json \
  --json
```

### V3 theory-page package + report artifact

```bash
php artisan v3:release-check \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder.php \
  --write-report \
  --strict
```

### Page_V3 page package preflight

```bash
php artisan page-v3:release-check \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder \
  --profile=scaffold
```

### Page_V3 category package preflight

```bash
php artisan page-v3:release-check \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsCategorySeeder/definition.json \
  --json
```

### Combined prompt output + later release-check

```bash
php artisan page-v3:generate-prompt \
  --manual-topic="Alternative Questions" \
  --category-mode=new \
  --new-category-title="Types of Questions" \
  --output=storage/app/prompts/page-v3-alternative-questions.txt \
  --write-skeleton \
  --force

php artisan page-v3:release-check \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder \
  --write-report
```

`release` is the default profile. Use `scaffold` right after `--write-skeleton` to confirm package wiring before content is filled in. Use `--strict` when warnings should also fail CI-style checks.

## Seed Package

Both seed commands resolve exactly one package from any of these target forms:

- package directory
- `definition.json`
- top-level loader stub PHP
- real seeder PHP

Relative targets are resolved from `base_path()`. Absolute targets are also supported. V3 targets must stay inside `database/seeders/V3`, and Page_V3 targets must stay inside `database/seeders/Page_V3`.

### V3 dry-run with scaffold preflight

```bash
php artisan v3:seed-package \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/definition.json \
  --dry-run \
  --check-profile=scaffold
```

This runs the real JSON runtime seeder inside a rollback-only transaction. If the package is seedable, the command succeeds and reports `rolled_back=true` without persisting rows or touching `seed_runs`.

### V3 live seed with default release-check gating

```bash
php artisan v3:seed-package \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder.php
```

By default the command runs `v3:release-check` semantics first with the `release` profile and aborts on failed checks.

### V3 skip preflight and request machine-readable output

```bash
php artisan v3:seed-package \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder \
  --skip-release-check \
  --json
```

Use `--skip-release-check` only when you intentionally want runtime seeding without the preflight gate.

### Page_V3 dry-run for a fresh scaffold package

```bash
php artisan page-v3:seed-package \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder \
  --dry-run \
  --check-profile=scaffold
```

`scaffold` is useful right after `--write-skeleton`: warnings remain visible, but the command can still continue into runtime seeding unless you also pass `--strict`.

### Page_V3 strict scaffold gating

```bash
php artisan page-v3:seed-package \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder/definition.json \
  --dry-run \
  --check-profile=scaffold \
  --strict
```

With `--strict`, scaffold warnings become fatal before runtime seeding starts.

### Page_V3 live seed with JSON output

```bash
php artisan page-v3:seed-package \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder/TypesOfQuestionsAlternativeQuestionsTheorySeeder.php \
  --skip-release-check \
  --json
```

Successful live runs update or create the package `seed_runs` record using the resolved base seeder class as the canonical `class_name`.

## Unseed Package

Both unseed commands resolve exactly one package from the same target forms as `seed-package`:

- package directory
- `definition.json`
- top-level loader stub PHP
- real seeder PHP

Relative targets are resolved from `base_path()`. Absolute targets are also supported. V3 targets must stay inside `database/seeders/V3`, and Page_V3 targets must stay inside `database/seeders/Page_V3`.

Live unseed is blocked unless you pass `--force`. `--dry-run` does not require `--force`: it runs the real delete flow inside a rollback-only transaction, leaves `seed_runs` untouched, and reports the actual impact that would be deleted in live mode.

### V3 dry-run impact report

```bash
php artisan v3:unseed-package \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/definition.json \
  --dry-run \
  --write-report \
  --json
```

This resolves the package, checks canonical ownership in the database, runs the real delete flow inside a rollback-only transaction, and writes a Markdown report into `storage/app/package-unseed-reports/v3`.

### V3 live unseed

```bash
php artisan v3:unseed-package \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder.php \
  --force
```

Successful live runs delete only the resolved package-owned V3 database content, then remove the canonical `seed_runs` record for the resolved base seeder class. Package files on disk are never deleted.

### V3 strict no-op / warning handling

```bash
php artisan v3:unseed-package \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder \
  --dry-run \
  --strict \
  --json
```

Use `--strict` when warnings such as missing `seed_runs` ownership metadata or already-absent package data should fail instead of returning a successful warning/no-op result.

### Page_V3 dry-run with localization cleanup validation

```bash
php artisan page-v3:unseed-package \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder \
  --dry-run \
  --json
```

For Page_V3 packages the dry run also walks the matching Page_V3 localization cleanup path inside the rollback-only transaction, so broken ownership or relation issues fail before any live delete is attempted.

### Page_V3 live unseed + report artifact

```bash
php artisan page-v3:unseed-package \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder/TypesOfQuestionsAlternativeQuestionsTheorySeeder.php \
  --force \
  --write-report
```

The command deletes only the resolved package-owned Page_V3 database content, removes the canonical `seed_runs` record for the base seeder class, and writes a Markdown report into `storage/app/package-unseed-reports/page-v3`.

## Refresh Package

Both refresh commands resolve exactly one package from the same target forms as `seed-package` and `unseed-package`:

- package directory
- `definition.json`
- top-level loader stub PHP
- real seeder PHP

Relative targets are resolved from `base_path()`. Absolute targets are also supported. V3 targets must stay inside `database/seeders/V3`, and Page_V3 targets must stay inside `database/seeders/Page_V3`.

By default the command runs the existing release-check service first with the `release` profile. Pass `--check-profile=scaffold` for scaffold-stage checks, or `--skip-release-check` to disable preflight entirely. `--strict` makes both preflight warnings and refresh-impact warnings fatal.

Live refresh is blocked unless you pass `--force`. `--dry-run` does not require `--force`: it executes the same refresh flow inside a rollback-only transaction, leaves `seed_runs` untouched, and fails honestly on broken payloads or reseed errors.

If the resolved package is not currently seeded in the database, refresh falls back to seed-only behavior and returns a warning. Use `--strict` if that warning should fail instead.

### V3 dry-run refresh with default preflight

```bash
php artisan v3:refresh-package \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/definition.json \
  --dry-run \
  --write-report
```

This runs the admin refresh semantics for the resolved package inside a rollback-only transaction and writes a Markdown report into `storage/app/package-refresh-reports/v3`.

### V3 live refresh

```bash
php artisan v3:refresh-package \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder.php \
  --force
```

Live mode reuses the existing admin refresh orchestration for the resolved seeder class, but wraps the refresh path atomically so a failed reseed does not leave the package partially removed.

### V3 strict seed-only fallback check

```bash
php artisan v3:refresh-package \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder \
  --dry-run \
  --strict \
  --json
```

Use this when missing package ownership or missing package data should fail instead of returning a warning-backed seed-only fallback result.

### Page_V3 dry-run refresh with scaffold profile

```bash
php artisan page-v3:refresh-package \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder \
  --dry-run \
  --check-profile=scaffold
```

This is useful right after `--write-skeleton`: the command still runs the real refresh flow inside a rollback-only transaction, but the preflight uses scaffold-level expectations instead of release-ready checks.

### Page_V3 live refresh with report artifact

```bash
php artisan page-v3:refresh-package \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder/TypesOfQuestionsAlternativeQuestionsTheorySeeder.php \
  --force \
  --write-report
```

Successful live runs keep the resolved package seeded, refresh the canonical `seed_runs` state for the base seeder class, and write a Markdown report into `storage/app/package-refresh-reports/page-v3`.
