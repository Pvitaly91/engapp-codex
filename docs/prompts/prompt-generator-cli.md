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
