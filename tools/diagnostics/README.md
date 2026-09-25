# Local M1/M2 diagnostics

Run from the repository root. These tools only request `http://gramlyze.loc`;
`.com` canonical values are checked as metadata, never followed. No production
credentials or deployment are needed. Do not commit their runtime output in
`storage/app/seo-m2-local/`. Use unique labels to retain failed attempts.

## Requirements and isolated tests

Python 3, the project's PHP 8.2 + Composer dependencies, and Node.js. Resolve
executables from PATH or pass `--php` / `PHP_BINARY`; no user-profile paths are
embedded. For browser checks install Playwright and its Chromium in your usual
tool environment. `PLAYWRIGHT_MODULE` may identify an existing Playwright module;
`CHROMIUM_EXECUTABLE` optionally selects an existing browser executable.

```sh
python tools/diagnostics/run-isolated-tests.py --php php --include-m2 --label m1-m2
node --test tests/Browser/saved-test-persistence.test.cjs tests/Browser/state-request-classification.test.cjs
```

The Python runner sets process-only temporary encryption key, SQLite `:memory:`,
array cache/session, unique bootstrap caches, compiled views and full Laravel
storage root. It refuses a non-memory DB or filesystem paths outside that root
before PHPUnit. It never writes the working `.env` or its config cache.
Without `--include-m2` it reruns the nine M1 test files. Explicit test filenames
can be supplied instead. Test fixtures disable authored-question snapshot events.

## HTTP and browser acceptance

```sh
python tools/diagnostics/local-get-series.py --phase before-tests --count 30 --workers 1
python tools/diagnostics/local-get-series.py --phase before-tests --count 30 --workers 3
# Run isolated tests, then repeat with --phase after-tests.
python tools/diagnostics/local-seo-smoke.py --label regression
node tools/diagnostics/probe-state-network.cjs
node tools/diagnostics/local-state-browser.cjs acceptance
```

Do not run the GET series concurrently with another series: the intended maximum
is three HTTP requests at once. Every attempted response/error is retained; no
automatic success-only retry. GET probes do not send cookies, auth or Referer,
follow redirects or use proxies. The SEO smoke treats the `source=theory` 302
as query cleanup, and checks its exact local destination without following it.

Browser contexts are fresh guests; production domains are blocked before the
first navigation, while required CDN reads are allowed. The state acceptance
uses only its own local/sessionStorage keys and keeps its cookies in memory.
It exercises real UI answers, rapid partial edits, internal navigation, reload,
one rejected save, one delayed save, server-only restoration and separation by
guest/test/mode on desktop and mobile. Existing user browser sessions are not used.
It intentionally fills partial, unsubmitted words to avoid external answer/AI calls.

The acceptance comparison preserves chosen answers, all nonempty manual input,
slot positions, counters and question order. It equates `null` and `''` only in
manual input slots because Laravel's `ConvertEmptyStringsToNull` normalizes empty
POST values. This is not suppression of a lost nonempty answer.

Raw `requestfailed` events remain in output. The request classifier distinguishes
a rejected fetch from `204` + resolved fetch + a later Chromium `ERR_ABORTED`.
Neither a 204 nor that classification alone proves persisted progress: use the
independent server-restoration scenario as well. Navigation aborts stay distinct.
The comparison probe also leaves local save requests unintercepted in one pass.

## Optional protected bootstrap observer

The application has **no observer hook at rest**, and no debug HTTP endpoint.
For an explicitly authorized local diagnosis only:

1. Run `php tools/diagnostics/prepare-observer.php`. It creates a private nonce
   and expected-key digest without printing either or the key.
2. Temporarily add `(require __DIR__.'/../tools/diagnostics/observe-bootstrap.php')($app);`
   immediately before `return $app;` in `bootstrap/app.php`.
3. Run the GET series. Its header activates the observer only for exact
   `gramlyze.loc`, loopback client, and matching nonce. It records presence,
   validity and equality booleans, never key values/fragments or full config.
4. Remove the temporary hook and the private `observer-control.json`. Keep raw
   observations local and out of Git. Do not leave the hook in a commit.

Do not change the working APP_KEY, disable encryption, mutate `.env` for negative
tests, clear working caches, or edit vendor. The unsafe interleaving regression
in `ThreadSafeEnvironmentTest` is entirely an isolated in-memory fixture.

## M3.1 isolated smoke acceptance

Use the wrapper, not `php artisan test`: it starts PHPUnit directly, before an
Artisan parent could load the working environment. `tests/bootstrap.php` fixes
testing/SQLite-memory defaults, a random process-only key, and private storage,
views and all bootstrap-cache paths before application providers boot. Test
kernels use an empty environment directory, never the working `.env`.
The wrapper adds a private startup INI scan directory with only
`opcache.enable_cli=0`, preserving existing/default scan directories. This also
reaches PHPUnit grandchildren, preventing Windows CLI OPcache from replaying an
old STDIN script. Working `php.ini`, Apache OPcache and `.env` are untouched;
the preflight requires and records the CLI-only setting as disabled.
Public/Theory/Admin fixtures use separate SQLite files under that private root
and validate the actual PDO driver and `PRAGMA database_list` before schema work.
They never clear or retarget a live compiled-view directory. Question exports
use a private destination; only unrelated fixture creation suppresses events.

```sh
python tools/diagnostics/run-isolated-tests.py --php php --label acceptance --matrix smoke --include-m2
python tools/diagnostics/run-isolated-tests.py --php php --label safety --preflight-only
python tools/diagnostics/run-isolated-tests.py --php php --label isolation tests/Feature/SmokeIsolationTest.php
python tools/diagnostics/run-isolated-tests.py --php php --label public tests/Feature/PublicFlows
python tools/diagnostics/run-isolated-tests.py --php php --label theory tests/Feature/Theory
python tools/diagnostics/run-isolated-tests.py --php php --label admin tests/Feature/AdminFlows
python tools/diagnostics/run-isolated-tests.py --php php --label combined tests/Feature/PublicFlows tests/Feature/Theory tests/Feature/AdminFlows
python tools/diagnostics/run-isolated-tests.py --php php --label reversed --reverse tests/Feature/PublicFlows tests/Feature/Theory tests/Feature/AdminFlows
python -B -m unittest discover -s tests/diagnostics -p test_isolated_runner.py
```

The recommended matrix command runs the isolation regressions, each smoke suite,
the combined suite, reversed combined suite, and M1/M2 in distinct child processes
with private runtimes. It captures the complete protected inventory once before
and once after the whole batch (eight read-only SHA-256 workers, no sampling).
Each child retains its own JUnit, byte-exact logs, and real exit code immediately;
later child runs do not turn an earlier failure into a successful batch.

Run these separately from performance measurements and from browser requests
that might legitimately compile changed working views. Before/after SHA-256
inventories cover working question snapshots, existing compiled views/bootstrap
caches, `.env`, PPC audits, `.codex` files and local `gramlyze-*.tar.gz` archives.
Any difference fails the wrapper; it never repairs files after a test run.
Each uniquely named result keeps the child's real exit code, UTF-8 stdout/stderr,
and byte-exact output files plus JUnit in its runtime. An isolation failure uses
wrapper exit 3 only when PHPUnit itself returned 0. Private runtime directories
are retained after use for diagnosis; they are not build inputs or commit inputs.

## M3.1 layout measurements

Run only against Apache `http://gramlyze.loc`, with built assets and production
origins blocked by the probes before navigation. Set `PLAYWRIGHT_MODULE` and
`CHROMIUM_EXECUTABLE` only when Playwright/Chromium are provided outside the repo.
Do not run build, PHP suites or other browser work concurrently with performance.

```sh
node tools/diagnostics/public-layout-shifts.cjs before-layout
# Apply and verify the scoped layout fix, then use the unchanged performance probe.
node tools/diagnostics/public-layout-shifts.cjs after-layout
node tools/diagnostics/verify-layout-comparison.cjs storage/app/seo-m3-1-local/before-layout-perf.json storage/app/seo-m3-1-local/after-layout-perf.json
node --test tests/Browser/cls-session-window.test.cjs tests/Browser/theory-sidebar-stability.test.cjs tests/Browser/layout-comparison.test.cjs
```

Each performance phase is 60 sequential navigations: three pages, two viewports,
five cold/reload pairs, fixed 12-second windows with a two-second readiness
margin. Preserve all raw entries, including recent-input and post-cutoff shifts.
Standard CLS is the maximum session window; the separate legacy sum is over
the same new window, not the historical M3 short observation period.

The read-only comparison gate independently recalculates metrics and requires
matching probe/dependency/condition evidence, complete runs and actual successful
font resources. `document.fonts.ready` alone can resolve after a failed font
stylesheet; a blocked-font run must not be compared to a font-loaded baseline.
Only a local Questions state `ERR_ABORTED` with a corresponding Fetch HTTP 204
is counted separately; unrelated failures reject acceptance. The gate verifies
measurement integrity, not improvement or field Core Web Vitals. The probe's
source inventory omits the head layout; retain supplementary provenance when
that template changes. Never overwrite historical labels or evidence.

Run fault/state scenarios and screenshots separately from performance:

```sh
node tools/diagnostics/theory-sidebar-stability.cjs sidebar-before reproduce
node tools/diagnostics/theory-sidebar-stability.cjs sidebar-after accept
node tools/diagnostics/theory-sidebar-stability.cjs sidebar-collapse accept --page=sentence-types --viewport=desktop --scenario=saved-collapsed-delayed-alpine
```

Raw logs, reports, screenshots and runtime files under `storage/app/seo-*-local`
are private diagnostic artifacts, not commit inputs.
