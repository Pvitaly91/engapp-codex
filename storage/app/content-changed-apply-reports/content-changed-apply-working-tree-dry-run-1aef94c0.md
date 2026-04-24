# Changed Content Apply

- Diff Mode: `working_tree`
- Base: `f048d8ee7ae00cac62e059e9f72370f4b362f40e`
- Base Refs By Domain: `[]`
- Head: `13e91be42c36b59eeee1e4128eb851fc970f6afc`
- Include Untracked: `false`
- Domains: ``
- Dry Run: `true`
- Force: `false`

## Plan Summary

- Changed Packages: 0
- Deleted Cleanup Candidates: 0
- Seed Candidates: 0
- Refresh Candidates: 0
- Skipped: 0
- Blocked: 0
- Warnings: 0

## Preflight Summary

- Candidates: 0
- OK: 0
- Warn: 0
- Fail: 0
- Skipped: 0

## Deleted Packages Cleanup Phase

- None.

## Current Packages Upsert Phase

- None.

## Error

- Stage: `planning`
- Message: SQLSTATE[42S02]: Base table or view not found: 1146 Table 'gr2.content_sync_states' doesn't exist (Connection: mysql, SQL: select * from `content_sync_states` where (`domain` = page-v3) limit 1)
