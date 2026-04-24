# ContentOps Doctor

- Generated At: `2026-04-24T14:45:09+03:00`
- Overall Status: `warn`
- Domains: `v3, page-v3`
- Summary: `{"total":9,"pass":6,"warn":3,"fail":0}`

## Checks

- `PASS` content_schema_content_sync_states - Required table [content_sync_states] is present.
- `PASS` content_schema_content_operation_runs - Required table [content_operation_runs] is present.
- `PASS` content_schema_content_operation_locks - Required table [content_operation_locks] is present.
- `PASS` content_ops_config - ContentOps config keys are present.
- `PASS` content_storage_root - storage/app is readable and writable.
- `WARN` content_storage_artifact_dirs - Some ContentOps artifact directories have not been created yet: content-changed-plans, content-sync-plans, content-sync-apply-reports, content-operation-replays, content-doctor-reports.
  - Recommendation: This is safe before first use; running commands with --write-report will create needed directories.
- `PASS` content_operation_lock_status - No execution-grade content operation currently holds the lock.
- `WARN` content_sync_state_v3 - v3 content sync cursor is uninitialized.
  - Recommendation: Run content:plan-sync and bootstrap explicitly only if the DB is trusted to match current code.
- `WARN` content_sync_state_page_v3 - page-v3 content sync cursor is uninitialized.
  - Recommendation: Run content:plan-sync and bootstrap explicitly only if the DB is trusted to match current code.
