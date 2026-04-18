# Main Release Checklist

Use this checklist for every merge into `main`.

## Policy Snapshot

- Pull request required before merge: yes
- Required approvals: `1`
- Dismiss stale approvals after new commits: yes
- Required checks: `Theory Smoke`, `Public Flows Smoke`, `Admin Flows Smoke`
- Branch must be up to date before merge: yes
- Review conversations resolved before merge: yes
- Include administrators: yes
- Direct pushes to `main`: no by policy
- Force pushes to `main`: no
- Deleting `main`: no

## Local Smoke Commands

Run the command or commands that fit the change:

```bash
composer test:theory-smoke
composer test:public-flows-smoke
composer test:admin-flows-smoke
composer test:smoke-all
```

## Maintainer Merge Flow

1. Developer runs the local smoke command or commands that fit the change.
2. Developer opens or updates the pull request targeting `main`.
3. Wait for `Theory Smoke`, `Public Flows Smoke`, and `Admin Flows Smoke` in GitHub Actions.
4. Confirm at least one approval is present on the pull request.
5. Resolve all review comments and open conversations.
6. If any required check fails, do not merge. Fix the branch, rerun the relevant local smoke command or `composer test:smoke-all`, push the fix, and wait for the required checks to turn green again.
7. Merge only when the branch is up to date with `main`, all three required checks are green, and the conversation state is clean.
8. Run `docs/post-merge-checklist.md`.
