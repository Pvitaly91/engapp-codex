"""Explicit opt-in: own a native loopback MySQL process, never attach to a service.

No application .env, working DB, migrations, seeders or external HTTP are used.
Run on Windows with an installed MySQL runtime; retain private evidence/data files.
"""
import argparse
import configparser
import datetime
import importlib.util
import json
import os
from pathlib import Path
import secrets
import socket
import subprocess
import sys
import time
import uuid

REPO = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('protected_runner', Path(__file__).with_name('run-isolated-tests.py'))
guard = importlib.util.module_from_spec(spec)
spec.loader.exec_module(guard)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--opt-in-disposable-mysql', action='store_true', required=True)
    parser.add_argument('--mysql-home', type=Path, required=True)
    parser.add_argument('--php', required=True)
    parser.add_argument('--expect-defect', action='store_true', help='Before-fix probe only: require SQLSTATE 42000 / 1064 in both actual paths.')
    args = parser.parse_args()
    if os.name != 'nt':
        parser.error('This native ownership runner currently supports Windows only.')
    for stream in [sys.stdout, sys.stderr]:
        stream.reconfigure(encoding='utf-8', errors='backslashreplace')
    home = args.mysql_home.resolve(strict=True)
    mysqld = home / 'bin/mysqld.exe'
    admin = home / 'bin/mysqladmin.exe'
    if not mysqld.is_file() or not admin.is_file():
        parser.error('MySQL server/admin binaries not found.')
    run_id = uuid.uuid4().hex
    parent = REPO / 'storage/app/seo-m4-2-local'
    parent.mkdir(exist_ok=True)
    if parent.resolve() != parent.absolute():
        raise RuntimeError('Refusing linked evidence parent.')
    root = parent / ('run-' + run_id)
    root.mkdir()
    datadir = root / 'data'
    runtime = REPO / 'storage/app/seo-m2-local' / ('test-runtime-' + uuid.uuid4().hex)
    runtime.mkdir()
    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        port = sock.getsockname()[1]
    env = {**os.environ, 'GRAMLYZE_TEST_RUNTIME': str(runtime), 'M42_OPT_IN': 'disposable-local-mysql',
           'M42_PORT': str(port), 'M42_DATADIR': str(datadir), 'M42_RUN_ID': run_id,
           'M42_DATABASE': 'm42' + run_id, 'M42_USER': 'u' + run_id[:16],
           'M42_PASSWORD': secrets.token_hex(32), 'M42_ROOT_PASSWORD': secrets.token_hex(32)}
    guard.configure_php_startup(env, runtime)
    before = guard.fingerprint_files()
    record = {'started_at_utc': datetime.datetime.now(datetime.timezone.utc).isoformat(),
              'run_id': run_id, 'expected_defect': args.expect_defect, 'runtime': str(runtime),
              'version_binary': subprocess.check_output([str(mysqld), '--no-defaults', '--version'], text=True).strip()}
    process = None
    bootstrapped = False
    exit_code = 2
    hidden = subprocess.CREATE_NO_WINDOW
    try:
        print('Initializing a new owned datadir; working MariaDB is untouched.', flush=True)
        with (root / 'initialize.log').open('wb') as log:
            initialized = subprocess.run([str(mysqld), '--no-defaults', '--no-monitor', '--initialize-insecure',
                                          '--basedir=' + str(home), '--datadir=' + str(datadir)],
                                         stdout=log, stderr=log, creationflags=hidden, timeout=180)
        record['initialize_exit_code'] = initialized.returncode
        if initialized.returncode:
            raise RuntimeError('Owned MySQL initialization failed; see private initialize.log.')
        config = configparser.ConfigParser()
        config.read(datadir / 'auto.cnf')
        env['M42_SERVER_UUID'] = config['auto']['server-uuid']
        with (root / 'server.log').open('wb') as log:
            process = subprocess.Popen([str(mysqld), '--no-defaults', '--no-monitor', '--console', '--basedir=' + str(home),
                                        '--datadir=' + str(datadir), '--bind-address=127.0.0.1',
                                        '--port=' + str(port), '--mysqlx=OFF', '--skip-log-bin',
                                        '--pid-file=' + str(root / 'owned.pid')],
                                       stdout=log, stderr=log, creationflags=hidden)
        record['owned_pid'] = process.pid
        deadline = time.monotonic() + 90
        while True:
            if process.poll() is not None or time.monotonic() > deadline:
                raise RuntimeError('Owned server did not become ready; see private server.log.')
            try:
                with socket.create_connection(('127.0.0.1', port), timeout=1):
                    break
            except OSError:
                time.sleep(0.25)
        # PID file + auto.cnf + actual DB metadata are independently checked.
        if int((root / 'owned.pid').read_text().strip()) != process.pid:
            raise RuntimeError('Listener PID does not match our child.')
        bootstrap = subprocess.run([args.php, 'tools/diagnostics/m42-mysql-bootstrap.php'], cwd=REPO,
                                   env=env, capture_output=True)
        record['bootstrap_exit_code'] = bootstrap.returncode
        if bootstrap.returncode:
            record['bootstrap_error'] = bootstrap.stderr.decode('utf-8', errors='replace')
            raise RuntimeError('Disposable database bootstrap rejected.')
        bootstrapped = True
        test_env = {key: value for key, value in env.items() if key != 'M42_ROOT_PASSWORD'}
        probe = subprocess.run([args.php, 'tools/diagnostics/m42-mysql-probe.php'], cwd=REPO, env=test_env, capture_output=True)
        record['probe_exit_code'] = probe.returncode
        if probe.returncode:
            record['probe_error'] = probe.stderr.decode('utf-8', errors='replace')
            raise RuntimeError('Probe setup failed.')
        record['probe'] = json.loads(probe.stdout)
        checks = list(record['probe']['checks'].values())
        if args.expect_defect:
            exit_code = 0 if len(checks) == 2 and all(c.get('sqlstate') == '42000' and c.get('error_code') == 1064 for c in checks) else 1
        elif not all(c['pass'] for c in checks):
            exit_code = 1
        else:
            print('Actual builder and sitemap kernel PASS; running shared regression fixtures.', flush=True)
            result = subprocess.run([args.php, 'vendor/bin/phpunit', '-c', 'tests/MySql/mysql.xml',
                                     '--do-not-cache-result', '--log-junit', str(root / 'mysql.junit.xml')],
                                    cwd=REPO, env=test_env, capture_output=True)
            # All fixtures are synthetic, but SQL exceptions still must not expose raw bindings.
            output = result.stdout.decode('utf-8', errors='replace')
            import re
            output = re.sub(r'(?s)Illuminate\\Database\\QueryException:.*?(?=\n\n|\Z)',
                            lambda _: 'Illuminate\\Database\\QueryException: [SQL/bindings omitted; use structured probe]', output)
            (root / 'phpunit.txt').write_text(output, encoding='utf-8')
            # JUnit may contain SQL literals on failure; do not retain that unredacted payload.
            junit = root / 'mysql.junit.xml'
            if result.returncode and junit.exists():
                junit.write_text(re.sub(r'(?s)<(failure|error)\b.*?</\1>',
                                       '<error>Failure detail omitted; see sanitized phpunit.txt.</error>',
                                       junit.read_text(encoding='utf-8')), encoding='utf-8')
            record['phpunit_exit_code'] = result.returncode
            record['phpunit_stdout'] = output
            print(output, flush=True)
            exit_code = result.returncode
    except Exception as error:
        record['runner_error'] = str(error)
        print('Run failed: ' + str(error), flush=True)
    finally:
        if process is not None and process.poll() is None:
            if bootstrapped:
                try:
                    shutdown = subprocess.run([str(admin), '--no-defaults', '--protocol=TCP', '--host=127.0.0.1',
                                               '--port=' + str(port), '--user=root', 'shutdown'],
                                              env={**os.environ, 'MYSQL_PWD': env['M42_ROOT_PASSWORD']},
                                              capture_output=True, creationflags=hidden, timeout=30)
                    record['shutdown_exit_code'] = shutdown.returncode
                except subprocess.TimeoutExpired:
                    record['shutdown_timeout'] = True
            try:
                process.wait(timeout=30 if bootstrapped else 1)
            except subprocess.TimeoutExpired:
                # Popen handle refers only to the process created above; never stop a service.
                process.terminate()
                process.wait(timeout=15)
                record['owned_process_terminated'] = True
        record['owned_process_stopped'] = process is None or process.poll() is not None
        after = guard.fingerprint_files()
        changed = sorted(p for p in before.keys() | after.keys() if before.get(p) != after.get(p))
        record.update({'protected_files': len(before), 'protected_files_changed': changed,
                       'fingerprint_before_sha256': guard.fingerprint_digest(before),
                       'fingerprint_after_sha256': guard.fingerprint_digest(after),
                       'finished_at_utc': datetime.datetime.now(datetime.timezone.utc).isoformat(),
                       'exit_code': exit_code or (3 if changed else 0)})
        (root / 'result.json').write_text(json.dumps(record, ensure_ascii=False, indent=2), encoding='utf-8')
        print(f'Evidence: {root / "result.json"}; exit {record["exit_code"]}; protected changes: {len(changed)}', flush=True)
    raise SystemExit(record['exit_code'])


if __name__ == '__main__':
    main()
