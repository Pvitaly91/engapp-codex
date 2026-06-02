# Deployment Content Apply

- Mode: `standard`
- Source: `deploy`
- Base: ``
- Head: ``
- Requested: `true`
- Live Executed: `false`
- Dry Run: `true`
- Status: `preview_failed`

## Content Sync


## Gate

- Blocked: `true`
- Strict: `true`
- Reason: SQLSTATE[42S02]: Base table or view not found: 1146 Table 'gr2.content_sync_states' doesn't exist (Connection: mysql, SQL: select * from `content_sync_states` where `domain` in (v3, page-v3))

## Changed Content Apply

- Changed Packages: 0
- Deleted Cleanup Candidates: 0
- Seed Candidates: 0
- Refresh Candidates: 0
- Preflight OK: 0
- Preflight Warn: 0
- Preflight Fail: 0

## Cleanup Deleted Phase

- None.

## Upsert Present Phase

- None.

## Error

- Stage: `deployment_preview`
- Message: SQLSTATE[42S02]: Base table or view not found: 1146 Table 'gr2.content_sync_states' doesn't exist (Connection: mysql, SQL: select * from `content_sync_states` where `domain` in (v3, page-v3))
