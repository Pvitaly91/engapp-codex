# Deployment Content Apply

- Mode: `native`
- Source: `deploy`
- Base: `base-sha`
- Head: `head-sha`
- Requested: `true`
- Live Executed: `true`
- Dry Run: `false`
- Status: `completed`

## Content Sync

- [v3] before status=`drifted` effective_base=`v3-synced-sha` target=`head-sha`
- [page-v3] before status=`uninitialized` effective_base=`base-sha` target=`head-sha`
- [v3] after status=`drifted` sync_ref=`v3-synced-sha`
- [page-v3] after status=`uninitialized` sync_ref=``

## Gate

- Blocked: `false`
- Strict: `true`

## Changed Content Apply

- Changed Packages: 1
- Deleted Cleanup Candidates: 0
- Seed Candidates: 1
- Refresh Candidates: 0
- Preflight OK: 1
- Preflight Warn: 0
- Preflight Fail: 0

## Cleanup Deleted Phase

- None.

## Upsert Present Phase

- None.
