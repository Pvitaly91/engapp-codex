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
