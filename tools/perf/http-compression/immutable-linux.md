# PERF-31 — Linux/Nginx immutable publishing contract

This tooling is separate from XAMPP. It prepares a **candidate**, not a live
Nginx configuration. It has no SSH, service-management, database, or deploy
entry point.

## Publisher and worker

* Use an existing physical vault outside the public release tree, on the
  publication filesystem. The owner is a trusted deploy UID (or root), not the
  Nginx worker. Every ancestor is checked with `lstat` and `realpath`; it must
  have a trusted owner, no group/other write, and execute for traversal. For
  example a dedicated trusted-owned vault may be `0711`. A symlinked release
  pointer isn't a physical source path; resolve it after verifying the intended
  release and pass its physical `public` directory.
* Pass a PID for the actual active **worker**, not the privileged root master.
  The adapter checks the worker role and root master in `/proc`, non-root
  real/effective/saved/filesystem credentials, supplementary groups, process
  start identity, absent inherited/permitted/effective/ambient capabilities,
  and rejects overlapping publisher and worker UIDs. The master is part of the
  trusted administrative boundary.
* Publication reuses the canonical PERF-29 manifest, exact Vite graph,
  deterministic padded gzip and original pin checks. The private `gen-*`
  tree is `0700/0600`, including the two manifest files. A second, serving
  tree `serve-<full manifest SHA>` is deliberately metadata-free: same-path
  identity and adjacent `.gz` under `build/assets`, directories `0555`, files
  `0444`, canonical physical paths, one link per file. Both encoded and
  decoded SHA and Last-Modified are checked. It is atomically renamed into
  place under an exclusive publisher lock after fsync and is never overwritten.
  An existing exact tree is reverified rather than replaced. Locked older
  generations are never removed.
* No named/ACL write can be granted beyond the group-mode mask. The
  disposable fixture probe also uses the real worker UID/GID/supplementary
  groups with privileges cleared and verifies that write, delete, rename,
  replacement, timestamp, permission/owner updates, and link operations fail.
  It verifies allowed reads and a worker-owned scratch write separately. It
  does not impersonate by injecting into the running worker or modify any live
  asset. Fixture evidence remains in the private vault.

## Prepare and verify on Ubuntu

Use the installed native Linux Node and Nginx on the confirmed test server.
The trusted operator must select the worker PID of the intended isolated
Nginx listener. Keep command output in protected private receipts.

```sh
node tools/perf/http-compression/immutable-linux.cjs publish \
  --public "$physical_public" --vault "$physical_vault" \
  --publisher-uid "$publisher_uid" --worker-pid "$worker_pid"

# Execute the following probe in the trusted root context.
node tools/perf/http-compression/immutable-linux.cjs probe \
  --vault "$physical_vault" --publisher-uid "$publisher_uid" --worker-pid "$worker_pid"

node tools/perf/http-compression/immutable-linux.cjs render \
  --public "$physical_public" --vault "$physical_vault" \
  --publisher-uid "$publisher_uid" --worker-pid "$worker_pid" \
  --manifest "$full_generation_sha256"
```

A trusted operator typically renders with root permissions to read the
root master's executable in `/proc`. The executable inspected with `nginx -V`
must have the same device/inode as the selected worker's actual Nginx master.
If the path is not `/usr/sbin/nginx`, supply the reviewed executable path with
`--nginx`. No fallback fabricates Content-Encoding if the native module
is unavailable. Pass `--retain "$old_full_sha256"` per expected older generation.
Every decoded URL is safe and finite; shared fingerprinted filenames with
different encoded/decoded representation pins are rejected. Use only the
`config` member of the successful renderer JSON as a staged server include.
Preserve its `configSha256` and compare the bytes installed, not merely its name.

## Nginx review and acceptance

The include belongs to **one** reviewed Gramlyze `server {}`, not global Nginx:

* Each exact fingerprinted Vite CSS/JS location maps into the verified serving
  tree. Static gzip uses the native [gzip_static module](https://nginx.org/en/docs/http/ngx_http_gzip_static_module.html)
  only where a verified sidecar is present. Native gzip chooses the
  representation; the response filter's dynamic `gzip` is off. ETags differ
  by representation length, Last-Modified matches the sealed source, and
  native Range applies to the selected bytes (decoded identity bytes
  only for identity, **encoded** bytes for gzip).
* The exact locations and explicit `/build/` 404 fence prevent direct sidecar,
  manifest and map access, including fallback into Laravel for a missing
  fingerprint. Before fencing, the renderer rejects any unaccounted for Vite
  dependency or public build file. Other app routes, PHP, public storage and
  sibling virtual hosts are not configured by this include.
* Verify the effective `nginx -T` target stanza first: no duplicate location,
  no conflicting regex/rewrite, no overlapping `/build/` rule, and no
  inherited Brotli, alternative precompressed handler, broad gzip/gzip_static
  or unexpected asset auth. Do not install a competing fallback or remove
  established static authorization. The one explicit `Vary: Accept-Encoding`
  avoids Nginx's redundant sidecar lookup on identity and includes 304; native
  conditional/range behavior must still pass on this specific Nginx build.
* Default Nginx [response header inheritance](https://nginx.org/en/docs/http/ngx_http_headers_module.html)
  changes when a location has its own `add_header`. Compare inherited headers
  and deliberate per-location behavior. Restate required static headers
  with `--headers /trusted/path/static-headers.json`, using entries such as
  `{"name":"X-Content-Type-Options","value":"nosniff","always":true}` in a JSON
  array. Existing dynamic values, cookie/auth/cache or encoding rules
  requiring different behavior need explicit migration review; do not drop
  or replace such rules to force this include.
* Test the edited **candidate** config with `nginx -t` and a private
  loopback listener; compare A/A then A/B/C with the same server binary and
  baseline where possible. Run `node --test tools/perf/http-compression/immutable-linux.test.cjs`
  on native Linux and execute the credential probe above. The HTTP contract
  verifies actual 200, HEAD, 304, 206, q=0, strong distinct validators,
  one Vary, immutable TTL, gzip content with exact raw and decoded SHAs, and a
  finite list of missing/private build URLs:

```sh
node tools/perf/http-compression/immutable-linux-http.cjs \
  --port "$shadow_port" --generation "$physical_generation" \
  --manifest "$full_generation_sha256"
```

The contract always connects to `127.0.0.1` on the Ubuntu host, without DNS or
redirects, and uses Host `gramlyze.ub`. To assert an older unique asset is
retained, pass both `--old-generation` and `--old-manifest`. For the active
HTTPS listener (only after authorized, verified activation) add
`--active --https --port 443` and, if necessary, `--ca` with the already
trusted CA certificate path. Reject missing TLS verification; do not downgrade
to insecure curl flags. Dynamic HTTP, browser, and performance checks are
separate from this static contract.

Keep the old app pointer/config bytes and sealed generations until rollback
is no longer needed. The trusted operator stages an atomic include/pointer
change after testing, validates the real config with `nginx -t` before
reload, and validates live routes afterward. If rollback is necessary,
restore only the exact previous release pointer and vhost include, retest
configuration and reload; do not delete data, clear shared caches, or
restart unrelated PHP pools.
