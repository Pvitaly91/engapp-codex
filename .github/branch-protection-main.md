# Main Release Gate

This repository cannot currently apply `main` branch protection directly from the available authenticated tool surface in this environment. The GitHub connector does not expose branch-protection writes, and the local `gh` CLI is installed but not authenticated. Apply the following rule manually in GitHub for `main`.

## Rule Target

- Branch name pattern: `main`

## Required Status Checks

Use these three check-run names as the required status checks for `main`:

- `Theory Smoke`
- `Public Flows Smoke`
- `Admin Flows Smoke`

These checks are emitted by:

- `.github/workflows/theory-smoke.yml`
- `.github/workflows/public-flows-smoke.yml`
- `.github/workflows/admin-flows-smoke.yml`

The workflow display names match the same three labels. The `pull_request` trigger for each workflow intentionally runs on every pull request to `main`, because GitHub leaves required checks pending when a required workflow is skipped by path filters.

## Protection Settings

Enable the following for `main`:

- Require a pull request before merging: enabled
- Require approvals: `1`
- Dismiss stale pull request approvals when new commits are pushed: enabled
- Require status checks to pass before merging: enabled
- Require branches to be up to date before merging: enabled
- Require conversation resolution before merging: enabled
- Do not allow bypassing the above settings: enabled where available
- Include administrators: enabled
- Allow force pushes: disabled
- Allow deletions: disabled

Direct pushes to `main` should be blocked. Merges should happen through pull requests that satisfy the three required smoke checks above.

## GitHub UI Handoff

In GitHub, open:

1. `Pvitaly91/engapp-codex`
2. `Settings`
3. `Branches`
4. Add or edit the rule for `main`

Then enable the settings listed above and select these exact required checks:

- `Theory Smoke`
- `Public Flows Smoke`
- `Admin Flows Smoke`

## Local Pre-Push Verification

Contributors should run these commands locally before pushing:

```bash
php artisan test tests/Feature/Theory
php artisan test tests/Feature/PublicFlows
php artisan test tests/Feature/AdminFlows
```

## Notes

- Workflow execution logic and test commands are unchanged; only check naming and PR trigger suitability for required checks were normalized.
- If this repository later enables merge queue, add `merge_group` triggers to these workflows before making them required in merge queue flows.
