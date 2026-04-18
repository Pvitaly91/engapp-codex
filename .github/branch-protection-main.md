# Main Branch Protection Manual Handoff

This repository already has the smoke checks needed to protect `main`, but the current tool surface in this environment cannot read or change the remote branch-protection rule.

What was verified in this environment:

- The GitHub app can access `Pvitaly91/engapp-codex`, but it does not expose branch-protection read or write methods.
- The local `gh` CLI is installed, but `gh auth status` and `gh api repos/Pvitaly91/engapp-codex/branches/main/protection` both fail because the CLI is not authenticated.

Because of that, this document is the exact manual handoff for applying or verifying the `main` protection rule in GitHub. It is not evidence that the remote rule is already enabled.

## Rule Target

- Branch name pattern: `main`

## Required Status Checks

Use these exact required check names for `main`:

- `Theory Smoke`
- `Public Flows Smoke`
- `Admin Flows Smoke`

These names are durable because each workflow file sets the same label at both the workflow and job level:

- `.github/workflows/theory-smoke.yml`
- `.github/workflows/public-flows-smoke.yml`
- `.github/workflows/admin-flows-smoke.yml`

Each workflow also runs on `pull_request` to `main`, which is required for GitHub to enforce the checks on pull requests.

## Final Protection Policy For `main`

Enable or confirm the following settings:

- Require a pull request before merging: enabled
- Require approvals: `1`
- Dismiss stale pull request approvals when new commits are pushed: enabled
- Require status checks to pass before merging: enabled
- Required status checks: `Theory Smoke`, `Public Flows Smoke`, `Admin Flows Smoke`
- Require branches to be up to date before merging: enabled
- Require conversation resolution before merging: enabled
- Do not allow bypassing the above settings: enabled
- Include administrators: enabled
- Allow force pushes: disabled
- Allow deletions: disabled

Direct pushes to `main` are not allowed by policy.

- For this user-owned repository, GitHub may not expose `Restrict who can push to matching branches`.
- If that control is available in the UI, do not grant any direct-push exceptions for `main`.
- If that control is not available, enforce PR-only changes to `main` through the settings above and maintainer discipline.

## GitHub UI Steps

1. Open `Pvitaly91/engapp-codex`.
2. Go to `Settings` -> `Branches`.
3. Add a branch protection rule for `main`, or edit the existing rule for `main`.
4. Under `Protect matching branches`, select `Require a pull request before merging`.
5. Under the pull-request section, select `Require approvals`, then set `Required number of approvals before merging` to `1`.
6. Under the pull-request section, select `Dismiss stale pull request approvals when new commits are pushed`.
7. Select `Require status checks to pass before merging`.
8. Select `Require branches to be up to date before merging`.
9. Under required status checks, select only `Theory Smoke`, `Public Flows Smoke`, and `Admin Flows Smoke`.
10. Select `Require conversation resolution before merging`.
11. Select `Do not allow bypassing the above settings`.
12. Select `Include administrators`.
13. Leave `Allow force pushes` cleared.
14. Leave `Allow deletions` cleared.
15. If `Restrict who can push to matching branches` is available in the UI, do not grant any direct-push exceptions for `main`.
16. Save the rule.
17. Re-open the saved rule and confirm the settings exactly match this document.

## Local Verification

Use the composer smoke commands below before opening or updating a pull request:

```bash
composer test:theory-smoke
composer test:public-flows-smoke
composer test:admin-flows-smoke
composer test:smoke-all
```

## Maintainer Flow

Use `docs/release-main-checklist.md` for the merge checklist and `docs/post-merge-checklist.md` for the short post-merge verification.
