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

## Plan Folder

Both planner commands are read-only. They resolve either one package or one subtree, inspect package disk state plus canonical ownership in `seed_runs` and the database, and build a deterministic execution plan without seeding, refreshing, unseeding, or deleting anything.

Accepted target forms:

- folder root
- package directory
- `definition.json`
- top-level loader stub PHP
- real seeder PHP

Relative targets are resolved from `base_path()`. Absolute targets are also supported. V3 targets must stay inside `database/seeders/V3`, and Page_V3 targets must stay inside `database/seeders/Page_V3`.

Planner modes:

- `missing`: only not-yet-seeded packages are planned for `seed`
- `refresh`: only already-seeded packages are planned for `refresh`
- `unseed`: only already-seeded packages are planned for `unseed`
- `destroy-files`: only on-disk packages with no canonical database or `seed_runs` ownership are planned for `destroy_files`
- `destroy`: combined subtree destroy planning for `destroy`, including `needs_unseed` and `needs_file_destroy` phase metadata
- `sync`: seeded packages plan `refresh`, not-seeded packages plan `seed`

`sync` is the default mode. `--with-release-check` adds per-package readiness summaries through the existing release-check services. `--strict` makes planner warnings, inconsistent states, and release-check warnings/failures fatal.

`plan-folder --mode=destroy-files` is still fully read-only. It never deletes files, never mutates database rows, and never changes `seed_runs`; it only returns a destructive file plan for one resolved subtree.

`plan-folder --mode=destroy` is also fully read-only. It never unseeds rows, never deletes files, and never changes `seed_runs`; it only builds a combined full-destroy plan for one resolved subtree so `destroy-folder` can later run DB cleanup first and file cleanup second.

### V3 sync plan for one namespace subtree

```bash
php artisan v3:plan-folder \
  database/seeders/V3/AI/ChatGptPro \
  --json
```

This scans only that resolved subtree, classifies each discovered V3 package, and returns a machine-readable plan with `seed` / `refresh` / `skip` / `blocked` recommendations plus ready-to-run next-step commands.

### V3 refresh-only plan with release-check aggregation

```bash
php artisan v3:plan-folder \
  database/seeders/V3/AI/ChatGptPro \
  --mode=refresh \
  --with-release-check \
  --write-report
```

The command stays read-only, adds per-package release-check summaries, and writes a compact Markdown report into `storage/app/folder-plans/v3`.

### V3 strict scaffold readiness gate

```bash
php artisan v3:plan-folder \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder \
  --with-release-check \
  --check-profile=scaffold \
  --strict \
  --json
```

Use this when scaffold warnings or inconsistent package ownership should already fail CI-style planning.

### V3 destructive unseed plan

```bash
php artisan v3:plan-folder \
  database/seeders/V3/AI/ChatGptPro \
  --mode=unseed \
  --json
```

This stays read-only, lists seeded packages as `unseed`, leaves already-absent packages as `skip`, and orders the subtree in deterministic reverse lexical package-path order for destructive execution.

### V3 read-only destroy-files plan

```bash
php artisan v3:plan-folder \
  database/seeders/V3/AI/ChatGptPro \
  --mode=destroy-files \
  --json
```

This stays read-only, marks unseeded on-disk packages as `destroy_files`, keeps seeded or ownership-broken packages as `blocked`, and hints to `v3:destroy-folder-files <target> --dry-run` without deleting anything.

### V3 read-only combined destroy plan

```bash
php artisan v3:plan-folder \
  database/seeders/V3/AI/ChatGptPro \
  --mode=destroy \
  --json
```

This stays read-only, classifies seeded packages with files as combined `destroy`, files-only packages as `destroy` with `needs_unseed=false`, DB-only cleanup as `destroy` with warnings, and hints to `v3:destroy-folder <target> --dry-run`.

### Page_V3 sync plan for one category subtree

```bash
php artisan page-v3:plan-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --json
```

Within one Page_V3 subtree the plan is deterministic: category packages are listed before page packages when both are in scope, then lexical path order is used inside each type.

### Page_V3 missing-only plan with release-check summaries

```bash
php artisan page-v3:plan-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --mode=missing \
  --with-release-check \
  --write-report
```

This keeps already-seeded packages as `skip`, highlights not-yet-seeded packages as `seed`, and writes the report into `storage/app/folder-plans/page-v3`.

### Page_V3 strict single-package scaffold plan

```bash
php artisan page-v3:plan-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsAlternativeQuestionsTheorySeeder/definition.json \
  --with-release-check \
  --check-profile=scaffold \
  --strict \
  --json
```

If the resolved package is disk-broken, has inconsistent DB ownership, or its scaffold release-check still warns/fails, the planner exits with failure while remaining completely read-only.

### Page_V3 destructive unseed plan

```bash
php artisan page-v3:plan-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --mode=unseed \
  --json
```

For destructive planning the same subtree stays read-only, but page packages are listed before category packages so later folder unseed runs can remove dependent pages before their categories.

### Page_V3 read-only destroy-files plan

```bash
php artisan page-v3:plan-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --mode=destroy-files \
  --json
```

The planner remains read-only, preserves page/category dependency warnings, lists page packages before category packages for later file cleanup, and suggests `page-v3:destroy-folder-files <target> --dry-run` instead of executing deletes itself.

### Page_V3 read-only combined destroy plan

```bash
php artisan page-v3:plan-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --mode=destroy \
  --json
```

The planner stays read-only, keeps page-before-category destructive ordering, preserves category/page dependency warnings, and produces `destroy` candidates with per-package DB and file phase metadata for later `page-v3:destroy-folder` execution.

Use the planner first, then apply the same resolved subtree explicitly:

- `plan-folder` = read-only planner for one resolved subtree
- `apply-folder` = ordered batch apply for one resolved subtree
- `unseed-folder` = ordered destructive batch cleanup for one resolved subtree
- `destroy-folder-files` = ordered batch package-file cleanup for one resolved subtree
- `destroy-folder` = ordered full destroy for one resolved subtree, with DB cleanup first and file cleanup second
- `plan-changed` = read-only git-diff-aware changed-package planner for one resolved scope
- `apply-changed` = execution layer over the changed-package plan, with deleted cleanup first and current upsert second
- execution is planner-ordered, package-atomic, folder-non-transactional, and fail-fast by default

## Unseed Folder

Both folder-unseed commands resolve either one package or one subtree from the same target forms as `plan-folder`:

- folder root
- package directory
- `definition.json`
- top-level loader stub PHP
- real seeder PHP

Relative targets are resolved from `base_path()`. Absolute targets are also supported. V3 targets must stay inside `database/seeders/V3`, and Page_V3 targets must stay inside `database/seeders/Page_V3`.

The command always starts with a full planner pass in `mode=unseed`, then runs package-by-package unseed preflight in rollback-only mode for every `unseed` candidate in scope. If any preflight candidate fails, live execution never starts. `--dry-run` stops after that full-scope preflight. Live destructive execution requires `--force`.

`--strict` makes planner warnings, blocked states, and package-unseed warnings fatal. The flow stays package-atomic, folder-non-transactional, and fail-fast, but it never deletes package files on disk.

### V3 folder unseed dry run

```bash
php artisan v3:unseed-folder \
  database/seeders/V3/AI/ChatGptPro \
  --dry-run \
  --write-report \
  --json
```

This resolves exactly one V3 subtree, preflights every seeded package via the existing single-package unseed service, writes a compact report into `storage/app/folder-unseed-reports/v3`, and leaves database rows plus `seed_runs` untouched.

### V3 live folder unseed

```bash
php artisan v3:unseed-folder \
  database/seeders/V3/AI/ChatGptPro \
  --force
```

Live mode reuses the same planner order and the same single-package unseed semantics, then deletes package-owned data package-by-package, removing canonical `seed_runs` records only for successfully unseeded packages before the first failure stops the run.

### Page_V3 folder unseed dry run

```bash
php artisan page-v3:unseed-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --dry-run \
  --json
```

This performs full-scope destructive preflight for one Page_V3 subtree, running page packages before category packages, and surfaces dependency-guard failures before any live delete is allowed.

### Page_V3 live folder unseed

```bash
php artisan page-v3:unseed-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --force \
  --write-report
```

Successful live runs delete only package-owned Page_V3 data inside the resolved subtree, keep scope boundaries strict, and write a Markdown report into `storage/app/folder-unseed-reports/page-v3`.

## Destroy Folder Files

Both folder file-destroy commands resolve either one package or one subtree from the same target forms as `plan-folder`:

- folder root
- package directory
- `definition.json`
- top-level loader stub PHP
- real seeder PHP

Relative targets are resolved from `base_path()`. Absolute targets are also supported. V3 targets must stay inside `database/seeders/V3`, and Page_V3 targets must stay inside `database/seeders/Page_V3`.

The command always starts with `plan-folder --mode=destroy-files`, then performs full-scope package-file preflight for every planner-selected `destroy_files` candidate before any live delete is allowed. `--dry-run` stops after that planner-ordered preflight. Live file deletion requires `--force`.

`destroy-folder-files` never mutates database rows and never changes `seed_runs`. It deletes only canonical package-owned files inside the resolved scope, stays folder-non-transactional, and fails fast on the first live package failure.

### V3 subtree dry run

```bash
php artisan v3:destroy-folder-files \
  database/seeders/V3/AI/ChatGptPro \
  --dry-run
```

This builds the read-only destructive file plan, preflights loader stub plus package files for every in-scope V3 candidate, and prints a live hint with `--force` if the subtree is clean.

### V3 subtree live file destroy

```bash
php artisan v3:destroy-folder-files \
  database/seeders/V3/AI/ChatGptPro \
  --force \
  --remove-empty-dirs
```

Live mode deletes only package-owned V3 files inside that resolved subtree, then prunes empty package directories inside scope when `--remove-empty-dirs` is enabled. It never removes `database/seeders/V3` itself.

### V3 single-package scope through the folder command

```bash
php artisan v3:destroy-folder-files \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/definition.json \
  --dry-run \
  --json
```

Single-package resolution accepts package directory, `definition.json`, top-level loader stub PHP, or real seeder PHP. The delete set still includes the canonical loader stub, but no sibling packages outside that one package scope are included.

### Page_V3 subtree dry run

```bash
php artisan page-v3:destroy-folder-files \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --dry-run \
  --json
```

This preflights every planner-selected Page_V3 file-destroy candidate in one subtree, keeps out-of-scope category/page warnings in the report, and aborts honestly if any candidate is blocked or unsafe.

### Page_V3 page-before-category destructive order

```bash
php artisan page-v3:destroy-folder-files \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --force
```

For live Page_V3 file cleanup, page packages run before category packages. If a category still has obvious dependent pages outside the current scope, preflight blocks the whole live run before any file delete starts.

### JSON output

```bash
php artisan v3:destroy-folder-files \
  database/seeders/V3/AI/ChatGptPro \
  --dry-run \
  --json
```

The JSON payload includes nested `scope`, `plan`, `preflight`, `execution`, and `artifacts` sections suitable for automation, tests, and later orchestration.

### Report writing

```bash
php artisan page-v3:destroy-folder-files \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --dry-run \
  --write-report
```

Reports are written into:

- `storage/app/folder-file-destroy-reports/v3`
- `storage/app/folder-file-destroy-reports/page-v3`

### Strict mode

```bash
php artisan v3:destroy-folder-files \
  database/seeders/V3/AI/ChatGptPro \
  --dry-run \
  --strict
```

Use `--strict` when planner warnings or preflight warnings, such as inconsistent ownership or already-missing package files, should fail instead of returning a warning-backed dry run.

### Remove empty directories

```bash
php artisan page-v3:destroy-folder-files \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --force \
  --remove-empty-dirs
```

Empty-directory pruning is optional and stays scope-bound: only empty directories inside the resolved subtree are removed, and the Page_V3 root itself is never deleted.

## Destroy Folder

Both high-level folder-destroy commands resolve either one package or one subtree from the same target forms as `plan-folder`:

- folder root
- package directory
- `definition.json`
- top-level loader stub PHP
- real seeder PHP

Relative targets are resolved from `base_path()`. Absolute targets are also supported. V3 targets must stay inside `database/seeders/V3`, and Page_V3 targets must stay inside `database/seeders/Page_V3`.

The command always starts with `plan-folder --mode=destroy`, then runs one full-scope combined preflight:

- DB phase candidates are preflighted through the existing single-package unseed services in `dry_run=true`
- file phase candidates reuse the existing folder file-destroy delete-set builder and path guards
- blocked packages or failed preflight stop the whole live run before any write starts

`--dry-run` stops after that combined preflight. Live full destroy requires `--force`. The live contract is intentionally non-transactional at folder scope:

- DB phase runs first
- file phase runs second
- file phase starts only if DB phase fully succeeds
- there is no whole-subtree rollback orchestration

`seed_runs` change only in the DB phase through the existing single-package unseed services. Files-only destroy paths never mutate `seed_runs`.

### V3 subtree dry run

```bash
php artisan v3:destroy-folder \
  database/seeders/V3/AI/ChatGptPro \
  --dry-run
```

This builds the combined destroy plan, preflights package DB cleanup plus file cleanup for the resolved V3 subtree, and prints a live hint with `--force` if the scope is clean.

### V3 subtree live full destroy

```bash
php artisan v3:destroy-folder \
  database/seeders/V3/AI/ChatGptPro \
  --force \
  --remove-empty-dirs
```

Live mode first unseeds package-owned DB content and removes canonical `seed_runs`, then deletes only in-scope package files. Empty-directory pruning is optional and still scope-bound.

### V3 package with DB-only warning

```bash
php artisan v3:destroy-folder \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder \
  --dry-run \
  --json
```

If a package is still seeded in DB but some canonical package files are already missing, the planner keeps it as `destroy` with `needs_unseed=true`, `needs_file_destroy=false`, and a warning explaining the DB-only destroy shape.

### V3 package with files-only destroy

```bash
php artisan v3:destroy-folder \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/definition.json \
  --dry-run \
  --json
```

If the package is no longer seeded but canonical package files still exist on disk, the planner keeps it as `destroy` with `needs_unseed=false`, `needs_file_destroy=true`; live execution will skip the DB phase for that package and delete files only.

### Page_V3 subtree dry run

```bash
php artisan page-v3:destroy-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --dry-run \
  --json
```

This performs one combined preflight for the resolved Page_V3 subtree, surfaces dependency warnings in the result, and does not mutate DB rows, `seed_runs`, files, or directories.

### Page_V3 page-before-category combined destroy order

```bash
php artisan page-v3:destroy-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --force
```

For destructive execution, page packages run before category packages in both the DB phase and the file phase. If a category still has obvious dependent pages outside scope, preflight blocks the whole live run before any write starts.

### JSON output

```bash
php artisan v3:destroy-folder \
  database/seeders/V3/AI/ChatGptPro \
  --dry-run \
  --json
```

The JSON payload contains nested `scope`, `plan`, `preflight`, `execution`, and `artifacts` sections suitable for automation, tests, and later orchestration.

### Report writing

```bash
php artisan page-v3:destroy-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --dry-run \
  --write-report
```

Reports are written into:

- `storage/app/folder-destroy-reports/v3`
- `storage/app/folder-destroy-reports/page-v3`

### Strict mode

```bash
php artisan v3:destroy-folder \
  database/seeders/V3/AI/ChatGptPro \
  --dry-run \
  --strict
```

Use `--strict` when planner or preflight warnings, such as DB-only destroy shapes or dependency warnings, should fail instead of returning a warning-backed dry run.

### Remove empty directories

```bash
php artisan page-v3:destroy-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --force \
  --remove-empty-dirs
```

Directory pruning is optional and happens only after successful file-phase deletes. Only empty directories inside the resolved subtree are removed, and the Page_V3 root itself is never deleted.

## Plan Changed

Both changed-package planner commands are read-only. They map git diff paths to canonical package units, collapse multiple file changes into one package record, and plan the next lifecycle action without mutating database rows, `seed_runs`, files, or directories.

Commands:

- `php artisan v3:plan-changed {target?}`
- `php artisan page-v3:plan-changed {target?}`

Accepted optional target forms:

- omitted target = `database/seeders/V3` or `database/seeders/Page_V3`
- folder root
- package directory
- `definition.json`
- top-level loader stub PHP
- real seeder PHP

Relative targets resolve from `base_path()`. Absolute targets also work. V3 targets must stay inside `database/seeders/V3`; Page_V3 targets must stay inside `database/seeders/Page_V3`.

Diff source options:

- `--working-tree` = compare the current working tree to `HEAD`
- `--staged` = compare staged changes to `HEAD`
- `--base=<ref>` and optional `--head=<ref>` = ref-diff mode
- `--include-untracked` = treat untracked files as `added`

If no diff mode is passed, `--working-tree` is used. If only `--base` is passed, `--head=HEAD` is assumed. `--head` without `--base` fails. Ref-diff mode cannot be combined with `--staged` or `--working-tree`.

Changed-package planning keeps two deterministic phases:

- `cleanup_deleted` = deleted packages that still need DB cleanup
- `upsert_present` = current packages that should be seeded or refreshed

Ordering:

- V3 `cleanup_deleted` = reverse lexical by historical/current package path
- V3 `upsert_present` = lexical by current package path
- Page_V3 `cleanup_deleted` = page packages before category packages, then lexical
- Page_V3 `upsert_present` = category packages before page packages, then lexical

Optional release-check aggregation is available with `--with-release-check`, but it runs only for current on-disk packages whose recommended action is `seed` or `refresh`. Deleted packages never run release-check.

`plan-changed` is future-facing for CI and deploy orchestration:

- current on-disk packages usually plan `seed` or `refresh`
- deleted packages with historical metadata and surviving ownership plan `unseed_deleted`
- already-absent deleted packages plan `skip`
- broken current packages, unresolved deleted metadata, or inconsistent ownership plan `blocked`

### V3 subtree working-tree plan

```bash
php artisan v3:plan-changed \
  database/seeders/V3/AI/ChatGptPro \
  --include-untracked \
  --json
```

This scans only the git diff paths inside that V3 subtree, maps them back to canonical V3 packages, and groups the result into deleted cleanup and current upsert phases.

### V3 ref-diff plan

```bash
php artisan v3:plan-changed \
  database/seeders/V3/AI/ChatGptPro \
  --base=origin/main \
  --head=HEAD \
  --json
```

Use this in CI or deploy previews to see which V3 packages changed between two refs without touching the working tree.

### V3 deleted package planning

```bash
php artisan v3:plan-changed \
  database/seeders/V3/AI/ChatGptPro \
  --base=origin/main \
  --head=HEAD
```

Deleted V3 packages are still planned through historical `definition.json` metadata when available. If the package still owns DB rows or a canonical `seed_runs` record, the planner recommends `unseed_deleted`; otherwise it recommends `skip` or `blocked`.

### Page_V3 subtree working-tree plan

```bash
php artisan page-v3:plan-changed \
  database/seeders/Page_V3/BasicGrammar/VerbToBe \
  --include-untracked \
  --json
```

The planner keeps Page_V3 ordering explicit: deleted page cleanup stays ahead of deleted category cleanup, while current category packages are listed before current page packages for later upsert work.

### Page_V3 staged diff plan

```bash
php artisan page-v3:plan-changed \
  database/seeders/Page_V3/BasicGrammar/VerbToBe \
  --staged \
  --json
```

Use staged mode when you want a plan only for what is already in the index, not for additional unstaged edits in the working tree.

### Release-check aggregation for current changed packages

```bash
php artisan v3:plan-changed \
  database/seeders/V3/AI/ChatGptPro \
  --include-untracked \
  --with-release-check \
  --check-profile=scaffold \
  --json
```

Only current actionable packages run release-check. Deleted packages stay read-only and skip release-check entirely.

### Strict mode

```bash
php artisan page-v3:plan-changed \
  database/seeders/Page_V3/BasicGrammar/VerbToBe \
  --working-tree \
  --strict
```

Use `--strict` when blocked packages, unresolved deleted-package metadata, planner warnings, or release-check warnings should fail the command instead of returning a warning-backed plan.

### Report writing

```bash
php artisan v3:plan-changed \
  database/seeders/V3/AI/ChatGptPro \
  --include-untracked \
  --write-report
```

Reports are written into:

- `storage/app/changed-package-plans/v3`
- `storage/app/changed-package-plans/page-v3`

### JSON output

```bash
php artisan page-v3:plan-changed \
  database/seeders/Page_V3/BasicGrammar/VerbToBe \
  --working-tree \
  --json
```

The JSON payload contains `diff`, `scope`, `summary`, `phases`, `packages`, and `artifacts`, which makes it suitable for CI, deploy previews, and future `apply-changed` orchestration.

## Apply Changed

Both changed-package apply commands reuse `plan-changed` as the source of truth and then execute the recommended lifecycle in deterministic phase order.

Commands:

- `php artisan v3:apply-changed {target?}`
- `php artisan page-v3:apply-changed {target?}`

Accepted optional target forms:

- omitted target = `database/seeders/V3` or `database/seeders/Page_V3`
- folder root
- package directory
- `definition.json`
- top-level loader stub PHP
- real seeder PHP

Relative targets resolve from `base_path()`. Absolute targets also work. V3 targets must stay inside `database/seeders/V3`; Page_V3 targets must stay inside `database/seeders/Page_V3`.

Diff source selection matches `plan-changed`:

- `--working-tree` = compare the current working tree to `HEAD`
- `--staged` = compare staged changes to `HEAD`
- `--base=<ref>` and optional `--head=<ref>` = ref-diff mode
- `--include-untracked` = treat untracked files as `added`

If no diff mode is passed, `--working-tree` is used. If only `--base` is passed, `--head=HEAD` is assumed. `--head` without `--base` fails. Ref-diff mode cannot be combined with `--staged` or `--working-tree`.

Runtime contract:

- `--dry-run` runs the full changed plan plus full-scope preflight only
- live mode requires `--force`
- deleted cleanup phase runs first
- current-package upsert phase runs second
- there is no global rollback orchestration

The apply layer remains orchestration-only:

- deleted packages reuse canonical historical-metadata cleanup semantics
- current packages reuse the existing single-package `seed-package` and `refresh-package` services
- `seed_runs` mutate only through those existing mutation layers
- no files are deleted by `apply-changed`

### V3 working-tree dry run

```bash
php artisan v3:apply-changed \
  database/seeders/V3/AI/ChatGptPro \
  --include-untracked \
  --dry-run
```

This resolves changed V3 packages in one subtree, runs full-scope preflight for deleted cleanup plus current upsert candidates, and prints a live hint with `--force` when the scope is clean.

### V3 staged dry run

```bash
php artisan v3:apply-changed \
  database/seeders/V3/AI/ChatGptPro \
  --staged \
  --dry-run \
  --json
```

Use staged mode when only index changes should drive the apply plan; unstaged edits in the working tree are ignored.

### V3 ref-diff live apply

```bash
php artisan v3:apply-changed \
  database/seeders/V3/AI/ChatGptPro \
  --base=origin/main \
  --head=HEAD \
  --force
```

Live mode first cleans deleted packages that still own DB content or `seed_runs`, then seeds or refreshes current packages using the planner order for that ref diff.

### V3 subtree-scoped apply

```bash
php artisan v3:apply-changed \
  database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/definition.json \
  --include-untracked \
  --dry-run
```

Single-package scope works through the same folder/package/definition/loader/real-seeder target contract as `plan-changed`.

### V3 skip runtime release-check

```bash
php artisan v3:apply-changed \
  database/seeders/V3/AI/ChatGptPro \
  --include-untracked \
  --dry-run \
  --skip-release-check
```

This keeps planner-side classification intact, but the current-package seed/refresh services skip their own release-check preflight during changed-package preflight and live execution.

### Page_V3 working-tree dry run

```bash
php artisan page-v3:apply-changed \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --include-untracked \
  --dry-run \
  --json
```

Page_V3 keeps phase ordering explicit: deleted page cleanup stays ahead of deleted category cleanup, while current category packages are applied before current page packages.

### Page_V3 page/category apply order

```bash
php artisan page-v3:apply-changed \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --include-untracked \
  --force
```

Deleted cleanup runs page packages before category packages. If that phase fully succeeds, current packages are upserted with category packages before page packages.

### With planner release-check aggregation

```bash
php artisan v3:apply-changed \
  database/seeders/V3/AI/ChatGptPro \
  --include-untracked \
  --dry-run \
  --with-release-check \
  --check-profile=scaffold
```

`--with-release-check` affects planner summaries and reports only. Deleted packages never run release-check.

### JSON output

```bash
php artisan page-v3:apply-changed \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --include-untracked \
  --dry-run \
  --json
```

The JSON payload contains nested `diff`, `scope`, `plan`, `preflight`, `execution`, and `artifacts` sections suitable for automation and later deploy orchestration.

### Report writing

```bash
php artisan v3:apply-changed \
  database/seeders/V3/AI/ChatGptPro \
  --include-untracked \
  --dry-run \
  --write-report
```

Reports are written into:

- `storage/app/changed-package-apply-reports/v3`
- `storage/app/changed-package-apply-reports/page-v3`

### Strict mode

```bash
php artisan page-v3:apply-changed \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --include-untracked \
  --dry-run \
  --strict
```

Use `--strict` when planner warnings, unresolved deleted-package metadata, or preflight warnings should fail the whole changed apply before any live write starts.

## Apply Folder

Both apply commands resolve either one package or one subtree from the same target forms as `plan-folder`:

- folder root
- package directory
- `definition.json`
- top-level loader stub PHP
- real seeder PHP

Relative targets are resolved from `base_path()`. Absolute targets are also supported. V3 targets must stay inside `database/seeders/V3`, and Page_V3 targets must stay inside `database/seeders/Page_V3`.

`sync` is the default mode:

- `missing`: execute only planner-recommended `seed`
- `refresh`: execute only planner-recommended `refresh`
- `sync`: execute both planner-recommended `seed` and `refresh`

`apply-folder` does not execute `unseed`, `destroy_files`, or `destroy`; destructive cleanup remains separate `unseed-folder`, `destroy-folder-files`, and `destroy-folder` commands.

Live folder apply requires `--force`. `--dry-run` walks the same ordered apply flow, calls the existing single-package seed/refresh services in rollback-only modes, leaves database rows and `seed_runs` untouched, and still fails honestly on the first package error.

If the planner finds `blocked` packages, folder apply aborts before the first write side effect. `--strict` also makes planner warnings and release-check warnings fatal. `--skip-release-check` is forwarded into the package seed/refresh services. `--with-release-check` only adds planner-side readiness summaries into the result/report.

### V3 sync dry run

```bash
php artisan v3:apply-folder \
  database/seeders/V3/AI/ChatGptPro \
  --mode=sync \
  --dry-run
```

This reuses `v3:plan-folder` ordering for the resolved subtree, then calls the existing single-package V3 seed/refresh services package-by-package without persisting changes.

### V3 sync live apply

```bash
php artisan v3:apply-folder \
  database/seeders/V3/AI/ChatGptPro \
  --mode=sync \
  --force
```

Live mode applies only inside the resolved subtree. Earlier successful packages stay applied if a later package fails.

### V3 missing-only apply

```bash
php artisan v3:apply-folder \
  database/seeders/V3/AI/ChatGptPro \
  --mode=missing \
  --force \
  --skip-release-check
```

Already-seeded packages stay `skip`; only planner-selected `seed` actions run.

### V3 refresh-only apply

```bash
php artisan v3:apply-folder \
  database/seeders/V3/AI/ChatGptPro \
  --mode=refresh \
  --dry-run \
  --skip-release-check
```

Not-yet-seeded packages stay `skip`; only planner-selected `refresh` actions run.

### Page_V3 sync dry run

```bash
php artisan page-v3:apply-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --mode=sync \
  --dry-run
```

Within the resolved Page_V3 subtree, category packages execute before page packages, then lexical path order is used inside each type.

### Page_V3 subtree apply

```bash
php artisan page-v3:apply-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --mode=sync \
  --force \
  --skip-release-check
```

If a page references a category outside the current scope, the planner/apply report keeps that warning but does not expand the scope automatically.

### JSON output

```bash
php artisan v3:apply-folder \
  database/seeders/V3/AI/ChatGptPro \
  --mode=sync \
  --dry-run \
  --json
```

The JSON payload contains nested `scope`, `plan`, `execution`, and `artifacts` sections suitable for automation and tests.

### Report writing

```bash
php artisan page-v3:apply-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --mode=sync \
  --dry-run \
  --write-report
```

Reports are written into:

- `storage/app/folder-apply-reports/v3`
- `storage/app/folder-apply-reports/page-v3`

### Strict mode

```bash
php artisan v3:apply-folder \
  database/seeders/V3/AI/ChatGptPro \
  --mode=sync \
  --dry-run \
  --with-release-check \
  --check-profile=scaffold \
  --strict
```

Use `--strict` when planner warnings, inconsistent ownership states, or release-check warnings should stop execution before any package service runs.

### Skip per-package release checks

```bash
php artisan page-v3:apply-folder \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --mode=sync \
  --dry-run \
  --skip-release-check
```

This keeps planner ordering and planner warnings intact, but the actual package seed/refresh services skip their own release-check preflight.

## Unified Changed Content

Domain-specific changed commands still exist and stay useful when only one domain is involved:

- `v3:plan-changed`
- `page-v3:plan-changed`
- `v3:apply-changed`
- `page-v3:apply-changed`

Use the unified content commands when one git diff can touch both V3 and Page_V3 and you want one orchestration layer to preserve the safe cross-domain order:

- `content:plan-changed` = read-only merged changed-package planner across V3 and Page_V3
- `content:apply-changed` = execution layer over that merged plan
- `content:sync-status` = read-only per-domain content sync cursor/status for `v3` and `page-v3`
- `cleanup_deleted` always runs first
- `upsert_present` always runs second
- cleanup order across domains is `V3 -> Page_V3`
- upsert order across domains is `Page_V3 -> V3`
- there is no global rollback orchestration
- successful live ref-based `content:apply-changed` advances the synced ref for affected domains
- preview and dry-run flows never advance the synced ref

Accepted optional target forms match the domain changed commands:

- omitted target = both roots unless `--domains` narrows the scope
- folder root
- package directory
- `definition.json`
- top-level loader stub PHP
- real seeder PHP

Relative targets resolve from `base_path()`. Absolute targets also work. Targets must stay inside `database/seeders/V3` or `database/seeders/Page_V3`. If a target resolves inside one domain, the unified command scopes to that domain only and rejects contradictory `--domains` filters.

Diff source selection matches the changed-package commands:

- default = `--working-tree`
- `--staged` = staged diff against `HEAD`
- `--base=<ref>` and optional `--head=<ref>` = ref-diff mode
- `--include-untracked` = treat untracked files as `added`
- `--head` without `--base` fails
- ref mode cannot be mixed with `--staged` or `--working-tree`

### Default working-tree plan across both domains

```bash
php artisan content:plan-changed \
  --json
```

This builds one merged changed-content plan for the current working tree, keeps per-domain package metadata intact, and groups packages into cross-domain `cleanup_deleted` and `upsert_present` phases.

### Dry-run apply across both domains

```bash
php artisan content:apply-changed \
  --dry-run
```

This runs the same unified planner and then full-scope preflight across all actionable V3 and Page_V3 packages. It never mutates DB rows, never changes `seed_runs`, and never deletes files.

### Staged dry run

```bash
php artisan content:apply-changed \
  --staged \
  --dry-run \
  --json
```

Use staged mode when deploy/apply should consider only what is already in the index.

### Ref diff live apply

```bash
php artisan content:apply-changed \
  --base=origin/main \
  --head=HEAD \
  --force
```

Live execution requires `--force`, runs full-scope preflight first, then executes deleted cleanup and current-package upserts in the merged cross-domain order.

### Target-scoped apply for V3

```bash
php artisan content:apply-changed \
  database/seeders/V3/AI/ChatGptPro \
  --dry-run
```

When the target resolves inside V3, the unified command automatically scopes to V3 only.

### Target-scoped apply for Page_V3

```bash
php artisan content:apply-changed \
  database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions \
  --dry-run
```

When the target resolves inside Page_V3, the unified command automatically scopes to Page_V3 only.

### Explicit domain filter for V3 only

```bash
php artisan content:plan-changed \
  --domains=v3 \
  --include-untracked
```

### Explicit domain filter for Page_V3 only

```bash
php artisan content:plan-changed \
  --domains=page-v3 \
  --with-release-check \
  --check-profile=scaffold
```

### Include untracked packages

```bash
php artisan content:apply-changed \
  --include-untracked \
  --dry-run
```

This is useful right after scaffold generation, when packages exist on disk but are not committed yet.

### Planner release-check aggregation

```bash
php artisan content:plan-changed \
  --with-release-check \
  --check-profile=release \
  --json
```

Release-check runs only for current on-disk packages that already plan `seed` or `refresh`. Deleted packages never run release-check.

### Skip runtime release-check during apply

```bash
php artisan content:apply-changed \
  --dry-run \
  --skip-release-check
```

This forwards `--skip-release-check` into the current-package seed/refresh services during preflight and live execution. Planner-side release-check summaries still remain controlled by `--with-release-check`.

### JSON mode

```bash
php artisan content:plan-changed \
  --json
```

```bash
php artisan content:apply-changed \
  --dry-run \
  --json
```

The unified JSON payloads keep nested `diff`, `scope`, `domains`, `phases`, `plan`, `preflight`, `execution`, and `artifacts` sections so they can be consumed by CI and later automation.

### Report writing

```bash
php artisan content:plan-changed \
  --write-report
```

```bash
php artisan content:apply-changed \
  --dry-run \
  --write-report
```

Reports are written into:

- `storage/app/content-changed-plans/`
- `storage/app/content-changed-apply-reports/`

## Content Sync Status

Unified changed-content deploy/apply now persists a canonical synced git ref per domain instead of assuming that deployed code ref and content-applied ref are always identical.

```bash
php artisan content:sync-status
```

```bash
php artisan content:sync-status \
  --domains=v3,page-v3 \
  --json \
  --write-report
```

The command reports, per domain:

- `last_successful_ref`
- `last_successful_applied_at`
- `last_attempted_base_ref`
- `last_attempted_head_ref`
- `last_attempted_status`
- `last_attempted_at`
- whether the domain is currently `synced`, `drifted`, `uninitialized`, or `failed_last_apply`

## Content Sync Repair

Use sync repair when code is already deployed but content is still behind because deploy-time changed-content apply was skipped, failed, or only ran as a dry run.

- `content:plan-sync` = read-only drift preview from persisted per-domain sync refs to the currently deployed code ref
- `content:apply-sync` = explicit repair flow that reuses canonical unified changed-content apply for initialized drifted domains
- sync repair is domain-wide only; there is no subtree sync cursor in this contract
- the deployed code ref and the content sync ref are intentionally separate operational values
- dry-run and failed repair never advance `last_successful_ref`
- successful live repair advances `last_successful_ref` only for domains that actually completed successfully
- uninitialized domains do not silently fall back to "already synced"; they require explicit bootstrap

### Drift preview against the current deployed code ref

```bash
php artisan content:plan-sync
```

This resolves the current deployed code ref through the existing deployment ref probe, loads the persisted sync-state for `v3` and `page-v3`, and then plans only the drifted initialized domains through the canonical unified changed-content planner.

### Domain-filtered sync preview

```bash
php artisan content:plan-sync \
  --domains=v3 \
  --with-release-check \
  --check-profile=release
```

Use `--domains` when only one domain should be inspected or repaired. The sync cursor is still domain-wide for that selected domain.

### Dry-run repair

```bash
php artisan content:apply-sync \
  --dry-run
```

This runs the same drift resolution and then executes the canonical full-scope changed-content preflight for initialized drifted domains, without mutating DB content, `seed_runs`, or files.

### Live repair

```bash
php artisan content:apply-sync \
  --force
```

Live sync repair requires `--force`. It reuses the same `cleanup_deleted` then `upsert_present` ordering as unified changed-content apply, but only for domains whose sync cursor is behind the currently deployed code ref.

### Bootstrap uninitialized domains

```bash
php artisan content:apply-sync \
  --dry-run \
  --bootstrap-uninitialized
```

```bash
php artisan content:apply-sync \
  --force \
  --bootstrap-uninitialized
```

Bootstrap is an explicit operator trust action:

- it applies only to domains that do not yet have a persisted sync cursor
- it does not prove that DB content already matches code
- in dry-run it is only simulated and reported
- in live mode it records `last_successful_ref = current deployed ref` for those uninitialized domains without running content mutations for them

### JSON mode

```bash
php artisan content:plan-sync \
  --json
```

```bash
php artisan content:apply-sync \
  --dry-run \
  --json
```

JSON output stays stable and machine-readable for CLI automation, Blade integration, and deployment repair tooling.

### Report writing

```bash
php artisan content:plan-sync \
  --write-report
```

```bash
php artisan content:apply-sync \
  --dry-run \
  --write-report
```

Reports are written into:

- `storage/app/content-sync-plans/`
- `storage/app/content-sync-apply-reports/`

### Strict mode

```bash
php artisan content:plan-sync \
  --strict
```

```bash
php artisan content:apply-sync \
  --dry-run \
  --strict
```

With `--strict`, planner warnings and bootstrap-required states become fatal instead of returning a warning-backed repair plan.

## Content Operation History

Execution-grade changed-content runs now persist a compact DB summary plus a full canonical JSON artifact.

Recorded operations:

- `content:apply-changed`
- `content:apply-sync`
- deployment-owned changed-content apply
- deployment-owned sync repair
- dry-run variants of the operations above

Not recorded by default:

- `content:plan-changed`
- `content:plan-sync`
- `content:sync-status`
- plain deployment preview/gate GET requests

Each recorded run stores:

- `operation_kind`
- `trigger_source`
- domains
- base/head refs
- live vs dry-run
- compact summary counts and stop point
- payload JSON artifact path
- report path when available
- error excerpt for failed or partial runs

Full canonical payload artifacts are written under:

- `storage/app/content-operation-runs/YYYY/MM/DD/<run-id>.json`

Read-only history reports from the CLI are written under:

- `storage/app/content-operation-history/`

### Recent history list

```bash
php artisan content:history
```

### Filter recent runs

```bash
php artisan content:history \
  --kind=apply_sync \
  --status=partial \
  --domains=page-v3 \
  --limit=10
```

### Detail view for one run

```bash
php artisan content:history 42
```

### Machine-readable history output

```bash
php artisan content:history \
  --kind=deployment_apply_changed \
  --json
```

### Detail output + Markdown report

```bash
php artisan content:history 42 \
  --json \
  --write-report
```

Human mode shows:

- run id
- operation kind
- trigger source
- domains
- base/head refs
- dry-run or live
- status
- started/finished timestamps
- compact summary
- payload/report artifact paths
- stopped phase or error excerpt when present

JSON mode keeps a stable machine-readable shape for both list and detail output, and uses the canonical stored payload artifact instead of rebuilding a second reporting schema.

### Strict mode

```bash
php artisan content:plan-changed \
  --strict
```

```bash
php artisan content:apply-changed \
  --dry-run \
  --strict
```

Use `--strict` when planner warnings, release-check warnings, inconsistent states, or unresolved deleted-package metadata should fail instead of returning a warning-backed plan or dry run.

## Replay Recorded Content Runs

`content:retry-run` replays one recorded execution-grade content run from canonical history context.

- supported source kinds:
  - `apply_changed`
  - `apply_sync`
  - `deployment_apply_changed`
  - `deployment_sync_repair`
- unsupported kinds are blocked clearly
- replay never reruns code deployment or restore
- replay always creates a **new** `ContentOperationRun`
- the new run links back through `replayed_from_run_id`
- stale deployment/sync context is surfaced as warnings or blockers
- dry-run / blocked / failed replay never advances sync-state

### Safe default replay

```bash
php artisan content:retry-run 42
```

If neither `--force` nor `--reuse-original-mode` is passed, replay defaults to dry run.

### Live replay

```bash
php artisan content:retry-run 42 \
  --force
```

### Reuse original mode

```bash
php artisan content:retry-run 42 \
  --reuse-original-mode \
  --force
```

If the original run was live, `--reuse-original-mode` still requires `--force`.

### Replay a previously successful run

```bash
php artisan content:retry-run 42 \
  --allow-success \
  --dry-run
```

Successful original runs are blocked unless `--allow-success` is explicitly present.

### Strict stale-context validation

```bash
php artisan content:retry-run 42 \
  --strict \
  --json
```

Stable replay validation codes include examples like:

- `current_deployed_ref_changed_since_original_run`
- `content_sync_state_advanced_since_original_run`
- `recorded_head_ref_missing`
- `recorded_base_ref_missing`
- `artifact_missing`
- `unsupported_operation_kind`

### Replay report writing

```bash
php artisan content:retry-run 42 \
  --dry-run \
  --write-report
```

Replay reports are written under:

- `storage/app/content-operation-replays/...`

## Content Operation Lock

Execution-grade content operations now share one persisted global mutex keyed as `global_content_operations`.

The lock is acquired by:

- `content:apply-changed`
- `content:apply-sync`
- `content:retry-run`
- deploy / restore actions with post-code-update changed-content apply enabled, starting before code mutation
- deployment-owned sync repair
- dry-run variants of those execution-grade operations

The lock is not acquired by read-only paths:

- `content:plan-changed`
- `content:plan-sync`
- `content:sync-status`
- `content:history`
- `content:lock-status`
- `content:doctor`
- `content:release-gate`
- `content:ci-status`
- deployment preview/gate GET requests
- history list/detail admin pages

Dry-run takes the lock because it runs execution-grade preflight over mutable DB state. Planning/history/status stay lock-free.

### Lock status

```bash
php artisan content:lock-status
```

```bash
php artisan content:lock-status \
  --json \
  --write-report
```

Reports are written under:

- `storage/app/content-lock-status/...`

Human and JSON output show active/free/stale state, operation kind, trigger source, domains, run id, acquired/heartbeat/expires timestamps, and whether stale takeover is available.

### Stale takeover

Fresh active locks are never stolen. A stale lock still blocks execution unless the operator explicitly opts into takeover:

```bash
php artisan content:apply-changed \
  --dry-run \
  --takeover-stale-lock
```

```bash
php artisan content:apply-sync \
  --force \
  --takeover-stale-lock
```

```bash
php artisan content:retry-run 42 \
  --force \
  --takeover-stale-lock
```

Blocked-by-lock execution attempts are recorded in `content_operation_runs` as `status=blocked` with lock metadata in the canonical payload and compact history meta. There is intentionally no generic `content:unlock` command in this contract.

### Deployment lock reservation

When `Apply changed content after deploy/restore` is enabled in GitDeployment, the deployment action reserves the same `global_content_operations` lock before git code mutation starts.

- If the lock is free, the reservation is held through code update / restore and the post-deploy changed-content apply phase.
- If a fresh active lock exists, deploy/restore is blocked before `git fetch`, `git reset`, native API update, or restore starts.
- If a stale lock exists, deploy/restore is blocked unless the operator explicitly selects stale-lock takeover in the deployment form.
- If content apply is disabled, GitDeployment does not reserve the content-operation lock in this task.
- The post-deploy content apply service receives the existing reservation lease and does not acquire a second lock.

## ContentOps Doctor

`content:doctor` is a read-only readiness report for the ContentOps lifecycle. It does not mutate content rows, `seed_runs`, sync-state, lock rows, package files, or git state.

Core checks run by default:

- ContentOps tables and required columns for sync-state, history, and locks
- content deployment and GitDeployment config keys
- `storage/app` readability/writability and known artifact directory presence
- global content-operation lock status
- per-domain content sync-state status

Optional read-only checks:

```bash
php artisan content:doctor \
  --with-git \
  --with-artifacts \
  --with-deployment \
  --with-package-roots \
  --with-dry-plan
```

Examples:

```bash
php artisan content:doctor
```

```bash
php artisan content:doctor \
  --domains=v3,page-v3 \
  --json
```

```bash
php artisan content:doctor \
  --with-deployment \
  --with-package-roots \
  --with-artifacts \
  --write-report
```

```bash
php artisan content:doctor \
  --strict \
  --with-git \
  --with-dry-plan
```

Report files are written under:

- `storage/app/content-doctor-reports/...`

Status contract:

- `pass` = the check is ready
- `warn` = operator attention is needed, but the condition is not always fatal
- `fail` = the lifecycle is not ready for safe execution

`--strict` makes warnings fatal in the command exit code. This is useful as a pre-deploy safety gate in operator runbooks.

GitDeployment also exposes the same doctor through a compact ContentOps Doctor card:

- `GET /admin/deployment/content-doctor`
- `GET /admin/deployment/content-doctor?json=1`
- `GET /admin/deployment/native/content-doctor`
- `GET /admin/deployment/native/content-doctor?json=1`

## ContentOps Release Gate

`content:release-gate {target?}` is a read-only CI/operator gate over the existing ContentOps surfaces:

- `content:doctor` readiness checks
- unified changed-content planning for the selected diff/source
- per-domain content sync-state drift/uninitialized status
- global content-operation lock status
- optional release-check readiness summaries for actionable current packages

It does not mutate content rows, `seed_runs`, sync-state, history, lock rows, package files, or git state. It does not acquire the global content-operation lock.

Diff source examples:

```bash
php artisan content:release-gate
```

```bash
php artisan content:release-gate \
  --staged \
  --profile=ci \
  --with-release-check
```

```bash
php artisan content:release-gate \
  --base=origin/main \
  --head=HEAD \
  --profile=deployment \
  --with-release-check
```

Target/domain-scoped examples:

```bash
php artisan content:release-gate database/seeders/V3/Polyglot \
  --domains=v3 \
  --profile=ci
```

```bash
php artisan content:release-gate \
  --domains=page-v3 \
  --include-untracked \
  --with-package-roots
```

Output/report examples:

```bash
php artisan content:release-gate \
  --profile=ci \
  --json
```

```bash
php artisan content:release-gate \
  --profile=deployment \
  --with-doctor \
  --with-git \
  --with-deployment \
  --with-package-roots \
  --write-report
```

Report files are written under:

- `storage/app/content-release-gates/...`

Profiles:

- `local` keeps warnings non-fatal unless `--strict` or `--fail-on-warnings` is passed.
- `ci` enables stricter defaults for doctor/git/package-root/dry-plan checks, release-check aggregation, lock failures, and uninitialized sync-state.
- `deployment` mirrors GitDeployment-safe defaults for deployment wiring, package roots, release-check readiness, and lock failures.

Gate status contract:

- `pass` = no failing checks and no fatal warnings
- `warn` = warnings exist but the selected profile/options allow the command to exit successfully
- `fail` = at least one fail check exists, or warnings are fatal because of `--strict`, `--fail-on-warnings`, or the selected profile

Additional fail knobs:

- `--fail-on-lock`
- `--fail-on-stale-lock`
- `--fail-on-sync-drift`
- `--fail-on-uninitialized-sync`

GitDeployment exposes the same read-only gate through a compact ContentOps Release Gate card:

- `GET /admin/deployment/content-release-gate`
- `GET /admin/deployment/content-release-gate?json=1`
- `GET /admin/deployment/native/content-release-gate`
- `GET /admin/deployment/native/content-release-gate?json=1`

## ContentOps CI Release Gate

GitHub Actions includes a read-only ContentOps preflight workflow:

- `.github/workflows/contentops-release-gate.yml`
- helper script: `scripts/contentops-ci-preflight.sh`

Triggers:

- `pull_request` targeting `main`
- `workflow_dispatch`

The workflow does not deploy, restore, run `content:apply-changed`, run `content:apply-sync`, run `content:retry-run`, delete package files, or use production secrets.

The CI job:

- checks out the repository with `fetch-depth: 0` for safe diff planning
- installs Composer dependencies
- installs Node dependencies with `npm ci` when `package-lock.json` exists
- creates an isolated SQLite test database
- runs `php artisan migrate --force`
- creates CI-local ContentOps artifact directories
- writes CI-only `content_sync_states` rows pointing at the checked-out head ref so strict doctor/release-gate checks can run in an empty ephemeral DB
- runs targeted ContentOps/GitDeployment tests that exist in the branch
- runs `content:doctor --with-git --with-package-roots --with-dry-plan --strict --write-report --json`
- runs `content:release-gate --profile=ci --base=<base> --head=<head> --with-release-check --strict --write-report --json`
- uploads doctor/release-gate JSON and Markdown reports as artifacts
- writes a GitHub step summary with statuses, report paths, changed package counts, and top recommendations

The CI-only sync-state bootstrap is not an operator repair flow. It exists only because the GitHub Actions database starts empty; production sync-state remains managed by `content:apply-changed`, `content:apply-sync`, and deployment-owned ContentOps flows.

Manual workflow inputs:

- `base_ref`
- `head_ref`
- `domains`
- `profile`
- `with_release_check`
- `strict`
- `target_sha` for optional exact checked-out HEAD validation

Local equivalent:

```bash
php artisan migrate --force
php artisan content:doctor \
  --with-git \
  --with-package-roots \
  --with-dry-plan \
  --strict \
  --write-report
php artisan content:release-gate \
  --profile=ci \
  --base=origin/main \
  --head=HEAD \
  --with-release-check \
  --strict \
  --write-report
```

To run the same CI wrapper locally, first point `.env` at a disposable database, then run:

```bash
CONTENTOPS_BASE_REF=origin/main \
CONTENTOPS_HEAD_REF=HEAD \
CONTENTOPS_DOMAINS=v3,page-v3 \
CONTENTOPS_PROFILE=ci \
CONTENTOPS_WITH_RELEASE_CHECK=true \
CONTENTOPS_STRICT=true \
bash scripts/contentops-ci-preflight.sh
```

## ContentOps CI Status

`content:ci-status` is a read-only GitHub Actions status lookup for the ContentOps Release Gate workflow. It does not dispatch workflows, run deploys, run content apply, acquire locks, mutate sync-state, or touch `seed_runs`.

Examples:

```bash
php artisan content:ci-status \
  --branch=main \
  --sha=<target-sha> \
  --require-success
```

```bash
php artisan content:ci-status \
  --ref=origin/main \
  --strict \
  --write-report
```

```bash
php artisan content:ci-status \
  --workflow=contentops-release-gate.yml \
  --branch=main \
  --allow-in-progress \
  --json
```

Matching rules:

- `--sha` is the strongest match and requires `workflow_run.head_sha` to match the target commit.
- `--branch` filters GitHub workflow runs by branch when only branch context is available.
- `--ref` can be a local ref, branch, tag, or SHA; local refs are resolved read-only through git ref probing when possible.
- a successful run for a different SHA is reported as `sha_mismatch` and fails when exact SHA matching is required.
- missing, running, stale, unavailable, or failed runs are warnings by default unless `--require-success`, `--strict`, or deployment config makes them fatal.

Reports are written under `storage/app/content-ci-status/...`. GitDeployment screens include the same status card and can optionally block deploy/restore before code mutation when `git-deployment.contentops_ci_status.required_for_deploy=true`.

The GitHub Actions lookup is uncached by default (`cache_ttl_seconds=0`) to keep the status path deterministic. Operators may opt into a short Laravel cache TTL through deployment config if repeated UI polling needs it.

## ContentOps CI Dispatch

`content:ci-dispatch` safely requests the existing read-only ContentOps Release Gate workflow from CLI or the GitDeployment UI. It dispatches only `.github/workflows/contentops-release-gate.yml`; it does not deploy, run content apply, acquire the content lock, mutate sync-state, or touch `seed_runs`.

Examples:

```bash
php artisan content:ci-dispatch \
  --branch=main \
  --sha=<target-sha> \
  --base-ref=origin/main \
  --dry-run \
  --json
```

```bash
php artisan content:ci-dispatch \
  --branch=main \
  --sha=<target-sha> \
  --base-ref=origin/main \
  --with-release-check \
  --strict \
  --force \
  --write-report
```

Dispatch behavior:

- live dispatch requires `--force`; `--dry-run` prints the exact workflow payload without calling GitHub.
- `--branch` or a non-SHA `--ref` is required because GitHub `workflow_dispatch` runs on a branch/tag ref.
- `--sha` is sent as `target_sha`; the workflow verifies checked-out `HEAD` equals it and fails clearly on mismatch.
- workflow inputs stay aligned with CI: `base_ref`, `head_ref`, `domains`, `profile`, `with_release_check`, `strict`, and `target_sha`.
- after a live dispatch, the command performs a read-only CI status lookup; the run may still be invisible for a few seconds, so re-run `content:ci-status` if needed.

Reports are written under `storage/app/content-ci-dispatches/...`. GitDeployment screens show the same dispatch form on the ContentOps CI card so operators can request the exact target gate when status is missing, stale, failed, or SHA-mismatched.

## Git Deployment Content Preview

The GitDeployment module now reuses the unified changed-content planner as a read-only deployment preview and safety gate before full deploy or restore actions start.

- deploy / restore preview stays read-only
- no content rows, `seed_runs`, or files are mutated on the preview path
- cleanup ordering preview stays `V3 -> Page_V3`
- upsert ordering preview stays `Page_V3 -> V3`
- blocked packages or strict warnings can stop deployment before code update starts
- preview also shows per-domain content sync refs, fallback code refs, and drift/uninitialized state
- when a persisted sync ref exists, preview diffs that domain from the sync ref instead of blindly diffing from the current deployed code ref
- preview includes current content-operation lock status; the live deploy/restore path enforces the reservation before code update when content apply is requested

Preview integration lives in the admin deployment screens:

- `/admin/deployment`
- `/admin/deployment/native`

Both screens expose a `Попередній content preview` action for full deploy and rollback flows.

### Shell deployment preview for a branch

```bash
GET /admin/deployment/content-preview?source_kind=deploy&branch=main
```

This resolves the current local `HEAD`, validates that local `origin/<branch>` matches the remote branch SHA, and then runs the unified changed-content planner in ref-diff mode.

### Native/API deployment preview for a branch

```bash
GET /admin/deployment/native/content-preview?source_kind=deploy&branch=main
```

This resolves the current deployed commit from the native deployment service, validates that the remote branch commit is already available in local git objects for safe diffing, and then runs the unified changed-content planner in ref-diff mode.

### Rollback preview

```bash
GET /admin/deployment/content-preview?source_kind=backup_restore&commit=<backup-commit>
```

```bash
GET /admin/deployment/native/content-preview?source_kind=backup_restore&commit=<backup-commit>
```

Rollback preview compares the current deployed commit to the selected backup commit and shows the same cross-domain cleanup/upsert phases that a future content rollout would need.

### JSON preview payload

```bash
GET /admin/deployment/content-preview?source_kind=deploy&branch=main&json=1
```

```bash
GET /admin/deployment/native/content-preview?source_kind=backup_restore&commit=<backup-commit>&json=1
```

The payload includes:

- `deployment.mode`
- `deployment.source_kind`
- `deployment.base_ref`
- `deployment.head_ref`
- `content_plan.summary`
- `content_plan.phases.cleanup_deleted`
- `content_plan.phases.upsert_present`
- `gate.strict`
- `gate.blocked`
- `gate.reasons`

### Safety gate behavior

Before full deploy or rollback starts, GitDeployment now runs the same preview service server-side.

- if the unified content plan contains blocked packages, deployment is stopped
- if strict content preview is enabled and the plan returns warnings, deployment is stopped
- if the target ref cannot be resolved safely for changed-content diffing, deployment is stopped

This gate only applies to the full deploy / rollback flow. It does not run `content:apply-changed`, does not add rollback orchestration for content changes, and does not introduce file deletion into deployment.

## Git Deployment Content Apply

GitDeployment can now run the real unified changed-content apply step after a successful full deploy or rollback.

- deployment preview/gate still runs first
- code update or restore still happens through the existing deployment engine
- only after successful code update / restore does GitDeployment call the canonical `ChangedContentApplyService`
- deleted cleanup runs first
- current-package upsert runs second
- there is no global rollback orchestration for content apply

Admin UI controls:

- `Apply changed content after deploy`
- `Dry-run content apply`

Both shell and native screens support:

- full deploy + post-deploy content apply
- rollback + post-restore content apply
- read-only apply dry run with the same resolved `base_ref` / `head_ref`

### Shell admin dry-run content apply preview

```bash
GET /admin/deployment/content-apply-preview?source_kind=deploy&branch=main&json=1
```

This reuses deployment ref resolution and then runs the same execution-grade preflight payload as `content:apply-changed --dry-run`, without mutating content rows, `seed_runs`, or files.

### Native admin dry-run content apply preview

```bash
GET /admin/deployment/native/content-apply-preview?source_kind=backup_restore&commit=<backup-commit>&json=1
```

The native screen uses the same deployment refs as the planned restore and returns the canonical changed-content apply payload inside a deployment-local wrapper.

### Deploy + content apply flow

1. Preview/gate resolves `base_ref` and `head_ref`.
2. Existing deploy/restore code update runs.
3. GitDeployment calls unified changed-content apply with those same refs.
4. UI shows preflight, execution outcome, and report path.

### Result semantics

- if code update fails, content apply never starts
- if content apply fails, deployment is marked failed/partial in the UI
- `seed_runs` mutate only through the canonical deleted-cleanup / seed / refresh services
- no file deletion is introduced by deployment content apply
