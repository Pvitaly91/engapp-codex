"""Run PHPUnit with a guarded runtime and retain byte-exact child output/exit codes."""
import argparse
import datetime
import hashlib
import json
import os
import re
import subprocess
import sys
import uuid
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

REPO = Path(__file__).resolve().parents[2]
M1 = [
    'tests/Unit/TheoryInlineHtmlTest.php', 'tests/Feature/TheoryInlineHtmlRenderingTest.php',
    'tests/Unit/PassiveVoiceDebugContentRepairTest.php', 'tests/Feature/PageV3UnseedFolderCommandTest.php',
    'tests/Feature/ResolvedLearningPageSeoTest.php', 'tests/Feature/SeoRobotsTest.php',
    'tests/Feature/CanonicalUrlTest.php', 'tests/Feature/SiteModeTest.php',
    'tests/Feature/TheoryCanonicalLessonUrlTest.php',
]
M2 = [
    'tests/Unit/ThreadSafeEnvironmentTest.php', 'tests/Feature/SavedTestJsStateTest.php',
    'tests/Unit/SavedTestJsStateTest.php', 'tests/Unit/SavedTestJsStateSynonymsTest.php',
]
PREFLIGHT = r'''
require "tests/bootstrap.php";
$app = require "bootstrap/app.php";
Tests\Support\IsolatedTestEnvironment::configure($app);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
Tests\Support\IsolatedTestEnvironment::assertSafeDatabase($db);
if ($db->getDatabaseName() !== ":memory:" || config("cache.default") !== "array"
    || config("session.driver") !== "array" || filter_var(ini_get("opcache.enable_cli"), FILTER_VALIDATE_BOOLEAN))
    throw new RuntimeException("Unsafe test defaults");
foreach ([storage_path(), config("filesystems.disks.local.root"), config("filesystems.disks.public.root"),
    config("filesystems.disks.s3.root"), config("questions.export_path"),
    config("view.compiled"), config("session.files"), dirname($app->getCachedConfigPath()),
    dirname($app->getCachedRoutesPath()), dirname($app->getCachedPackagesPath()),
    dirname($app->getCachedServicesPath()), dirname($app->getCachedEventsPath())] as $path) {
    Tests\Support\IsolatedTestEnvironment::assertOwnedPath($path);
}
$compiler = app("blade.compiler");
Tests\Support\IsolatedTestEnvironment::assertOwnedPath(dirname($compiler->getCompiledPath(resource_path("views/home.blade.php"))));
echo json_encode(["environment" => app()->environment(), "driver" => $db->getDriverName(),
    "database" => $db->getDatabaseName(), "storage" => storage_path(), "views" => config("view.compiled"),
    "cache" => config("cache.default"), "session" => config("session.driver"),
    "opcache_enable_cli" => filter_var(ini_get("opcache.enable_cli"), FILTER_VALIDATE_BOOLEAN),
    "working_env_loaded" => $app->environmentPath() === base_path(),
    "key_present" => is_string(config("app.key")) && config("app.key") !== ""]);
'''


def file_sha256(path):
    digest = hashlib.sha256()
    with path.open('rb') as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b''):
            digest.update(chunk)
    return digest.hexdigest()


def fingerprint_files(repo=REPO):
    paths = list((repo / 'database/seeders/questions').glob('*.json'))
    paths += list((repo / 'bootstrap/cache').glob('*.php'))
    paths += [repo / '.env']
    paths += list((repo / 'storage/framework/testing').glob('present-perfect-continuous-theory-links-audit.*'))
    paths += list((repo / 'storage/app').glob('gramlyze-*.tar.gz'))
    paths += list((repo / '.codex').rglob('*'))
    for directory in ['views', 'views-public-flow-tests', 'views-theory-tests', 'views-admin-flow-tests']:
        paths += list((repo / 'storage/framework' / directory).glob('*.php'))
    print(f'Fingerprinting {len(paths)} protected paths (8 read-only workers)...', flush=True)
    def fingerprint(path):
        return (path.relative_to(repo).as_posix(), file_sha256(path)) if path.is_file() else None
    with ThreadPoolExecutor(max_workers=8) as pool:
        return dict(item for item in pool.map(fingerprint, sorted(paths)) if item is not None)


def fingerprint_digest(files):
    return hashlib.sha256(json.dumps(files, sort_keys=True, separators=(',', ':')).encode('utf-8')).hexdigest()


def decode_output(value):
    return value.decode('utf-8', errors='backslashreplace')


def configure_php_startup(env, runtime):
    # PHPUnit grandchildren execute fresh STDIN scripts. Windows CLI OPcache can
    # replay an earlier script/config path, so disable it for this test tree only.
    # An empty scan-dir element retains PHP's compiled-in default scan directory.
    directory = runtime / 'php-ini'
    directory.mkdir()
    (directory / '99-isolated-tests.ini').write_text('opcache.enable_cli=0\n', encoding='utf-8')
    env['PHP_INI_SCAN_DIR'] = env.get('PHP_INI_SCAN_DIR', '') + os.pathsep + str(directory)


def capture(command, env, runtime, name):
    result = subprocess.run(command, cwd=REPO, env=env, capture_output=True)
    (runtime / f'{name}.stdout.bin').write_bytes(result.stdout)
    (runtime / f'{name}.stderr.bin').write_bytes(result.stderr)
    return result, {'exit_code': result.returncode, 'stdout': decode_output(result.stdout),
                    'stderr': decode_output(result.stderr)}


def main():
    # Windows cp1251 must never replace a PHPUnit failure with UnicodeEncodeError.
    for stream in (sys.stdout, sys.stderr):
        if hasattr(stream, 'reconfigure'):
            stream.reconfigure(encoding='utf-8', errors='backslashreplace')
    parser = argparse.ArgumentParser()
    parser.add_argument('--php', default=os.environ.get('PHP_BINARY', 'php'))
    parser.add_argument('--label', default='tests')
    parser.add_argument('--include-m2', action='store_true')
    parser.add_argument('--reverse', action='store_true', help='Reverse PHPUnit execution order (including methods).')
    parser.add_argument('--preflight-only', action='store_true')
    parser.add_argument('--matrix', choices=['individual', 'smoke', 'courses'],
                        help='Run isolated child suites under one complete before/after file inventory.')
    parser.add_argument('tests', nargs='*')
    args = parser.parse_args()
    if not re.fullmatch(r'[A-Za-z0-9_-]+', args.label):
        parser.error('label must be a simple filename component')
    runtime = REPO / 'storage/app/seo-m2-local' / ('test-runtime-' + uuid.uuid4().hex)
    runtime.mkdir(parents=True)
    env = os.environ.copy()
    env['GRAMLYZE_TEST_RUNTIME'] = str(runtime)
    env['PYTHONIOENCODING'] = 'utf-8:backslashreplace'
    configure_php_startup(env, runtime)
    before = fingerprint_files()
    record = {'started_at_utc': datetime.datetime.now(datetime.timezone.utc).isoformat(),
              'runtime': str(runtime), 'fingerprints_before': before}
    (runtime / 'baseline-fingerprints.json').write_text(json.dumps(before, sort_keys=True), encoding='utf-8')
    print('Protected baseline captured; starting isolated PHP preflight.', flush=True)
    check, preflight = capture([args.php, '-r', PREFLIGHT], env, runtime, 'preflight')
    record['preflight_process'] = preflight
    exit_code = check.returncode
    if check.returncode == 0:
        record['preflight'] = json.loads(preflight['stdout'])
        if not args.preflight_only:
            smoke = ['tests/Feature/PublicFlows', 'tests/Feature/Theory', 'tests/Feature/AdminFlows']
            if args.matrix == 'courses':
                courses = ['tests/Feature/PolyglotCourseBlueprintTest.php', 'tests/Feature/PolyglotCourseLandingPageTest.php']
                plan = [('blueprint', courses[:1], False), ('landing', courses[1:], False),
                        ('courses-combined', courses, False), ('courses-reversed', courses, True)]
                if args.tests:
                    plan.insert(0, ('regressions', args.tests, False))
            elif args.matrix:
                plan = [('isolation', ['tests/Feature/SmokeIsolationTest.php'], False)]
                plan += [(Path(path).name.lower(), [path], False) for path in smoke]
                if args.matrix == 'smoke':
                    plan += [('combined', smoke, False), ('reversed', smoke, True)]
                if args.include_m2:
                    plan += [('m1-m2', M1 + M2, False)]
            else:
                plan = [('phpunit', args.tests or (M1 + (M2 if args.include_m2 else [])), args.reverse)]
            runs = []
            for name, selected, reverse in plan:
                child_runtime = runtime.parent / ('test-runtime-' + uuid.uuid4().hex) if args.matrix else runtime
                child_runtime.mkdir(exist_ok=True)
                child_env = {**env, 'GRAMLYZE_TEST_RUNTIME': str(child_runtime)}
                command = [args.php, 'vendor/bin/phpunit', *selected, '--do-not-cache-result', '--colors=never',
                           '--log-junit', str(child_runtime / f'{name}.junit.xml')]
                if reverse:
                    command += ['--order-by=reverse']
                print(f'Starting {name}: {" ".join(selected)}', flush=True)
                result, output = capture(command, child_env, child_runtime, name)
                runs.append({'name': name, 'runtime': str(child_runtime), 'command': command, **output})
                exit_code = exit_code or result.returncode
                # Flush each completed child result immediately; no wrapper/session loss
                # can hide a completed failure. The final batch still returns nonzero.
                (runtime / f'{name}.result.json').write_text(json.dumps(runs[-1], ensure_ascii=False, indent=2), encoding='utf-8')
                print(f'{name}: exit {result.returncode}\n{output["stdout"]}', flush=True)
                sys.stderr.write(output['stderr'])
                sys.stderr.flush()
            if args.matrix:
                record['runs'] = runs
            else:
                record.update({key: value for key, value in runs[0].items() if key != 'name'})
    else:
        record.update(preflight)
    after = fingerprint_files()
    changed = sorted(path for path in before.keys() | after.keys() if before.get(path) != after.get(path))
    record.update({'finished_at_utc': datetime.datetime.now(datetime.timezone.utc).isoformat(),
                   'fingerprints_after': after, 'protected_files_changed': changed,
                   'fingerprint_before_sha256': fingerprint_digest(before),
                   'fingerprint_after_sha256': fingerprint_digest(after),
                   'exit_code': exit_code, 'runner_exit_code': exit_code or (3 if changed else 0)})
    # Never overwrite evidence from a previous run, even when labels are reused.
    output_path = runtime.parent / f'{args.label}-{runtime.name.removeprefix("test-runtime-")}-result.json'
    output_path.write_text(json.dumps(record, indent=2, ensure_ascii=False) + '\n', encoding='utf-8')
    if args.preflight_only or check.returncode:
        sys.stdout.write(preflight['stdout'])
        sys.stderr.write(preflight['stderr'])
    print(f'\nEvidence: {output_path}\nProtected files: {len(before)}; changes: {len(changed)}')
    if changed:
        print('Isolation failure: protected files changed: ' + ', '.join(changed), file=sys.stderr)
    raise SystemExit(record['runner_exit_code'])


if __name__ == '__main__':
    main()
