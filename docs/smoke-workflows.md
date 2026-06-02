# Smoke Workflows

This repository uses three smoke suites as the release gate for `main`:

- `Theory Smoke`
- `Public Flows Smoke`
- `Admin Flows Smoke`

## Local Commands

Run the suite that matches the surface you changed:

```bash
composer test:theory-smoke
composer test:public-flows-smoke
composer test:admin-flows-smoke
```

Run the full local smoke gate before opening or updating a PR when the change crosses multiple surfaces, touches shared routing/models/views, or you want the same combined check every time:

```bash
composer test:smoke-all
```

`test:smoke-all` runs the three suites in sequence and stops when one of them fails.

## What Each Suite Covers

- `Theory Smoke`: theory pages and their supporting public route/view wiring.
- `Public Flows Smoke`: learner-facing public flows outside theory, including catalog and test-mode surfaces.
- `Admin Flows Smoke`: admin/content-management paths, including the existing safe admin write-path coverage in that suite.

## PR Flow For `main`

1. Run the local smoke command or commands that fit the change.
2. Open or update the pull request.
3. Wait for `Theory Smoke`, `Public Flows Smoke`, and `Admin Flows Smoke` to finish in GitHub Actions.
4. Merge only after the required checks are green.

For the current branch-protection handoff and required check names, see `.github/branch-protection-main.md`.
