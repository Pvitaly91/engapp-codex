# Модуль Git Deployment

Цей модуль інкапсулює усю логіку керування Git-оновленнями та резервними гілками, що раніше знаходилась у проєкті. Його можна легко перенести в інший Laravel-додаток.

## Структура
- `Http/Controllers` — контролери для SSH та API режимів оновлення.
- `Services` — робота з GitHub API та файловою системою без shell-команд.
- `Models` — модель `BackupBranch` для обліку резервних гілок.
- `routes/web.php` — маршрути адмінської панелі для розгортання.
- `resources/views` — Blade-шаблони інтерфейсу.
- `config/git-deployment.php` — налаштування GitHub та список шляхів, що зберігаються при оновленні.
- `database/migrations` — міграції для таблиці резервних гілок.

## Content-aware preview and gate

Модуль інтегрує unified changed-content planner у deployment preview path:

- перед full deploy або rollback можна подивитися read-only content diff preview
- preview reuse-ить `ChangedContentPlanService` як canonical source of truth
- preview показує merged V3 + Page_V3 impact, grouped phases, blockers, warnings, і release-check readiness summary
- якщо preview повертає blocked states або strict warnings, старт deploy/rollback блокується до початку code update

Маршрути preview:

- `GET /admin/deployment/content-preview`
- `GET /admin/deployment/native/content-preview`

Параметри:

- `source_kind=deploy&branch=<branch>`
- `source_kind=backup_restore&commit=<commit>`
- optional `json=1` для machine-readable payload

Preview path залишається повністю read-only:

- не запускає `content:apply-changed`
- не мутує content DB rows
- не мутує `seed_runs`
- не видаляє файли
- не виконує `git checkout/reset/clean`

## Deployment-owned content apply

Після успішного full deploy або rollback модуль тепер може явно запускати unified `content:apply-changed` як deployment-owned крок:

- code update / restore лишається в existing GitDeployment flow
- changed-content apply запускається тільки після успішного code update / restore
- reuse-иться той самий preview payload і ті самі `base_ref` / `head_ref`
- якщо changed-content apply увімкнений, deployment резервує global content-operation lock **до** code update / restore
- якщо content apply падає, deploy/restore позначається як failed і це видно в UI
- глобального rollback orchestration для content apply немає

UI інтеграція:

- у формах full deploy і rollback є explicit toggle `Apply changed content after deploy`
- у формах є explicit stale-lock takeover checkbox; fresh active locks ніколи не крадуться
- є read-only `Dry-run content apply` action для shell та native screens
- результат з preflight/execution summary і report path показується прямо в deployment UI

Маршрути dry-run preview:

- `GET /admin/deployment/content-apply-preview`
- `GET /admin/deployment/native/content-apply-preview`

Параметри такі самі, як у preview:

- `source_kind=deploy&branch=<branch>`
- `source_kind=backup_restore&commit=<commit>`
- optional `json=1`

Post-deploy content apply path reuse-ить canonical mutation layers:

- deleted cleanup через existing deleted cleanup services
- current-package seed/refresh через existing package services
- `seed_runs` мутуються тільки всередині цих canonical services

## Content sync state

GitDeployment preview/apply тепер reuse-ить canonical per-domain content sync cursor:

- `v3` і `page-v3` мають окремі persisted synced refs
- preview показує `current deployed code ref`, `content sync ref`, `effective base ref`, `target ref`
- якщо sync-state ще не ініціалізований, preview явно показує fallback на current deployed code ref
- drift між deployed code ref і content sync ref не блокує deploy автоматично сам по собі, але clearly visible в UI
- successful live ref-based changed-content apply advance-ить synced ref тільки для реально заторкнутих domain-ів
- preview, dry-run і failed apply не advance-ять `last_successful_ref`

## Content sync drift repair

Окремо від deploy / rollback flow модуль тепер дає operator-owned repair path для випадків, коли code ref already moved, а content sync ref ще відстає:

- repair flow не запускає code deploy або restore
- repair flow не мутує git state
- `head_ref` = current deployed code ref
- `base_ref` per domain = persisted `content_sync_states.last_successful_ref`
- drifted initialized domains reuse-ять canonical unified `content:apply-changed`
- uninitialized domains блокуються by default і можуть бути only explicitly bootstrapped

CLI parity:

- `php artisan content:sync-status`
- `php artisan content:plan-sync`
- `php artisan content:apply-sync --dry-run`
- `php artisan content:apply-sync --force`

Bootstrap semantics:

- `--bootstrap-uninitialized` не є diff-verified apply
- це explicit operator trust action, яка просто записує `last_successful_ref = current deployed ref`
- dry-run only simulates bootstrap
- failed repair never advance-ить `last_successful_ref`

Admin integration reuse-ить existing deployment screens:

- `GET /admin/deployment/content-sync-preview`
- `GET /admin/deployment/native/content-sync-preview`
- `POST /admin/deployment/content-sync`
- `POST /admin/deployment/native/content-sync`

UI показує:

- current deployed ref
- content sync ref
- per-domain status: `synced`, `drifted`, `uninitialized`, `failed_last_apply`
- drift preview grouped into `cleanup_deleted` and `upsert_present`
- dry-run repair result
- live repair result

Repair path залишається reuse-first:

- `ContentSyncStateService` = source of truth for persisted sync cursor
- `ChangedContentPlanService` = source of truth for drifted package planning
- `ChangedContentApplyService` = source of truth for deleted cleanup + upsert execution
- GitDeployment layer only resolves refs, triggers preview/apply, and renders reporting

## Content operation history

Execution-grade content operations now persist a small operator-facing history trail:

- `content:apply-changed`
- `content:apply-sync`
- deployment-owned post-deploy / post-restore changed-content apply
- deployment-owned sync repair
- dry-run variants of the operations above

History does **not** replace sync-state and does **not** become a second planner/apply engine:

- `ContentSyncStateService` still owns per-domain synced refs
- `ContentOperationRun` is history/audit only
- full canonical details live in JSON artifacts, not in a second DB schema

Persisted data model:

- compact DB row in `content_operation_runs`
- full canonical JSON payload artifact in `storage/app/content-operation-runs/YYYY/MM/DD/<id>.json`
- optional report artifact path if the underlying command/service wrote one

CLI access:

- `php artisan content:history`
- `php artisan content:history <runId>`
- `php artisan content:history --json`
- `php artisan content:history --write-report`

Admin UI integration:

- both deployment screens now show a compact `Recent Content Operations` card
- list route: `GET /admin/deployment/content-runs`
- detail route: `GET /admin/deployment/content-runs/{run}`

Each history row surfaces:

- run id
- operation kind
- trigger source
- replay linkage via `replayed_from_run_id`

## Content run replay

Operators can now replay a recorded execution-grade content run directly from the history detail page.

- replay stays content-only
- it never reruns code deployment / rollback
- the replay action creates a **new** `ContentOperationRun`
- the new row links back through `replayed_from_run_id`
- stale deployment/sync context is surfaced before canonical execution starts

CLI equivalent:

- `php artisan content:retry-run <run-id>`
- `php artisan content:retry-run <run-id> --force`
- `php artisan content:retry-run <run-id> --allow-success --dry-run`

Admin flow:

- open `GET /admin/deployment/content-runs/{run}`
- choose dry-run or live retry
- optionally enable strict stale-context blocking
- submit `POST /admin/deployment/content-runs/{run}/retry`

Replay reports are written under:

- `storage/app/content-operation-replays/...`
- domains
- base/head refs
- dry-run vs live
- status: `success|partial|failed|blocked|dry_run`
- compact phase/package summary
- artifact paths
- error excerpt when available

Preview-only GET flows remain read-only and do not create history rows:

- deployment preview/gate
- deployment sync preview
- `content:plan-changed`
- `content:plan-sync`
- `content:sync-status`

## Content operation lock

Execution-grade content operations now use one global persisted lock:

- lock table: `content_operation_locks`
- lock key: `global_content_operations`
- status card: `Active Content Operation Lock` on shell and native deployment screens
- CLI status: `php artisan content:lock-status --json`

The lock is acquired by:

- `content:apply-changed`
- `content:apply-sync`
- `content:retry-run`
- deploy / restore with post-code-update changed-content apply enabled, starting before code mutation
- deployment-owned sync repair
- dry-run variants of those operations

The lock is not acquired by read-only paths:

- deployment preview/gate GETs
- deployment sync preview GETs
- content history list/detail
- `content:plan-changed`
- `content:plan-sync`
- `content:sync-status`
- `content:history`
- `content:lock-status`
- `content:doctor`
- `content:release-gate`
- `content:ci-status`

Stale lock behavior is explicit:

- active fresh locks block overlapping execution
- stale locks are visible in UI/CLI
- stale takeover requires explicit operator input
- CLI uses `--takeover-stale-lock`
- admin forms expose a stale-lock takeover checkbox for content apply / sync repair / replay actions
- there is no generic force-unlock command

Deployment-specific reservation:

- if content apply is enabled, the lock is acquired before `git fetch`, `git reset`, native API deploy, or restore starts
- the same reservation is held through the code update / restore and the post-deploy content apply phase
- the post-deploy apply service reuses that lease instead of acquiring a second lock
- if reservation fails, code mutation does not start and the blocked attempt is recorded with lock metadata
- if content apply is disabled, the deployment flow does not reserve the content lock

If a content execution is blocked by the lock, the attempt is recorded in `content_operation_runs` with `status=blocked` and compact lock metadata. The deployment flow surfaces this as a pre-code lock gate or content-phase block/failure; it does not pretend the deployment content step succeeded.

## ContentOps doctor

Both deployment screens include a compact `ContentOps Doctor` card. It runs the same read-only diagnostics as the CLI command:

```bash
php artisan content:doctor --with-deployment --with-package-roots --json
```

Available routes:

- `GET /admin/deployment/content-doctor`
- `GET /admin/deployment/content-doctor?json=1`
- `GET /admin/deployment/native/content-doctor`
- `GET /admin/deployment/native/content-doctor?json=1`

The doctor checks:

- ContentOps migrations/tables and required columns
- `content-deployment` and GitDeployment content config
- storage readability/writability and artifact directory readiness
- active/stale/free global content-operation lock state
- per-domain content sync-state status
- optional current git ref probing
- optional recent history artifact integrity
- optional GitDeployment route/service wiring
- optional V3/Page_V3 package roots
- optional harmless changed-content dry plan read

Reports are written under `storage/app/content-doctor-reports/...` when `--write-report` or the UI report action is used. The doctor is read-only: it does not mutate content DB rows, `seed_runs`, sync-state, package files, locks, or git state.

## ContentOps release gate

Both deployment screens also include a compact `ContentOps Release Gate` card. It is a read-only CI/operator preflight that combines the existing doctor, unified changed-content planner, sync-state status, and global lock status:

```bash
php artisan content:release-gate --profile=deployment --with-release-check --json
```

Useful CI/deployment examples:

```bash
php artisan content:release-gate --profile=ci --staged --with-release-check
```

```bash
php artisan content:release-gate --profile=deployment --base=origin/main --head=HEAD --with-release-check --write-report
```

Available routes:

- `GET /admin/deployment/content-release-gate`
- `GET /admin/deployment/content-release-gate?json=1`
- `GET /admin/deployment/native/content-release-gate`
- `GET /admin/deployment/native/content-release-gate?json=1`

Profiles:

- `local` keeps warnings non-fatal unless strict/fail flags are provided.
- `ci` uses stricter defaults for doctor/git/package-root/dry-plan checks, release-check aggregation, active/stale locks, and uninitialized sync-state.
- `deployment` mirrors GitDeployment-safe defaults for deployment wiring, package roots, release-check readiness, and lock failures.

Reports are written under `storage/app/content-release-gates/...`. The release gate is read-only: it does not reserve/take over the content lock, create operation history rows, mutate sync-state, mutate content DB rows, touch `seed_runs`, change package files, or mutate git state.

## ContentOps CI workflow

The repository includes a read-only GitHub Actions ContentOps preflight workflow:

- workflow: `.github/workflows/contentops-release-gate.yml`
- helper script: `scripts/contentops-ci-preflight.sh`
- triggers: `pull_request` to `main` and `workflow_dispatch`

The workflow installs PHP/Node dependencies, prepares an isolated SQLite CI database, runs migrations, executes targeted ContentOps/GitDeployment tests, then runs:

```bash
php artisan content:doctor --with-git --with-package-roots --with-dry-plan --strict --write-report --json
php artisan content:release-gate --profile=ci --base=<base-ref> --head=<head-ref> --with-release-check --strict --write-report --json
```

It uploads `storage/app/contentops-ci/**`, `storage/app/content-doctor-reports/**`, and `storage/app/content-release-gates/**` as workflow artifacts, and writes a GitHub step summary.

Safety constraints:

- no production deploy or restore
- no `content:apply-changed`, `content:apply-sync`, or `content:retry-run`
- no production database credentials
- no production content DB mutation
- no package file deletion
- no content-operation lock acquisition outside tests in the isolated CI DB

Because the CI database starts empty, the helper script writes CI-local `content_sync_states` rows pointing at the checked-out head ref before strict doctor/release-gate checks run. This is not a repair/bootstrap operation for real environments; it only prevents an empty ephemeral CI database from failing readiness checks that are meant for long-lived deployments.

Manual workflow inputs allow overriding `base_ref`, `head_ref`, `domains`, `profile`, `with_release_check`, `strict`, and optional `target_sha` exact checkout validation.

## ContentOps CI status gate

Both deployment screens include a compact `ContentOps CI Status` card. It performs a read-only GitHub Actions lookup for `.github/workflows/contentops-release-gate.yml` and compares the latest matching workflow run to the target ref/SHA:

```bash
php artisan content:ci-status --branch=main --sha=<target-sha> --require-success --json
```

Available routes:

- `GET /admin/deployment/content-ci-status`
- `GET /admin/deployment/content-ci-status?json=1`
- `GET /admin/deployment/native/content-ci-status`
- `GET /admin/deployment/native/content-ci-status?json=1`

Matching behavior:

- exact SHA match is preferred when the target SHA is known
- branch-only checks are allowed but reported as SHA-unverified
- missing, running, stale, unavailable, failed, or SHA-mismatched runs are surfaced with stable readiness codes
- no workflow is dispatched and no deployment/content operation is executed

Deployment blocking is controlled by `git-deployment.contentops_ci_status.required_for_deploy`, which defaults to `false`. When enabled, deploy/restore checks the target ContentOps CI status after the existing content preview/gate and before code mutation starts. A non-ready CI result blocks the deploy/restore before `git fetch`, `git reset`, native API update, or restore.

Reports are written under `storage/app/content-ci-status/...` when `--write-report` or the UI report option is used.

The lookup is uncached by default (`cache_ttl_seconds=0`). If repeated admin polling needs it, set `DEPLOYMENT_CONTENTOPS_CI_STATUS_CACHE_TTL_SECONDS` to a short TTL; this does not change the matching or gate rules.

## ContentOps CI dispatch

Operators can request the existing read-only ContentOps Release Gate workflow directly from CLI or the deployment UI when status is missing, stale, failed, running on the wrong SHA, or not yet available:

```bash
php artisan content:ci-dispatch --branch=main --sha=<target-sha> --base-ref=origin/main --dry-run --json
php artisan content:ci-dispatch --branch=main --sha=<target-sha> --base-ref=origin/main --with-release-check --strict --force
```

Available routes:

- `POST /admin/deployment/content-ci-dispatch`
- `POST /admin/deployment/native/content-ci-dispatch`

Dispatch rules:

- only the configured `contentops-release-gate.yml` workflow can be dispatched
- dry-run renders the exact payload and does not call GitHub
- live dispatch requires explicit force confirmation
- `target_sha` is passed to the workflow and verified against checked-out `HEAD`
- dispatch does not deploy code, run content apply, mutate content DB rows, mutate `seed_runs`, acquire content locks, or change sync-state

The GitHub token used by the deployment module must be allowed to dispatch Actions workflows for the repository. Reports are written under `storage/app/content-ci-dispatches/...` when the report option is used.

## Підключення в іншому проєкті
1. Скопіюйте каталог `app/Modules/GitDeployment` у свій репозиторій.
2. Переконайтеся, що стандартний PSR-4-маппінг `"App\\": "app/"` увімкнений (цей крок типовий для Laravel).
3. Зареєструйте сервіс-провайдер у `config/app.php` (розділ `providers`):
   ```php
   App\Modules\GitDeployment\GitDeploymentServiceProvider::class,
   ```
4. Налаштуйте змінні середовища для GitHub у `.env`:
   ```env
   DEPLOYMENT_GITHUB_OWNER=your-org
   DEPLOYMENT_GITHUB_REPO=your-repo
   DEPLOYMENT_GITHUB_TOKEN=github_token_with_repo_scope
   DEPLOYMENT_GITHUB_USER_AGENT="ProjectDeploymentBot/1.0"
   ```
5. Запустіть міграції: `php artisan migrate`.
6. За потреби опублікуйте конфіг та шаблони:
   ```bash
   php artisan vendor:publish --tag=git-deployment-config
   php artisan vendor:publish --tag=git-deployment-views
   ```

Після цього маршрути `/admin/deployment` та `/admin/deployment/native` будуть доступні автоматично, а модель `BackupBranch` використовуватиметься для запису резервних гілок.
