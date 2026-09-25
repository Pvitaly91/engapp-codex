"""Private, fixed-revision A/B PHP stands. No checkout changes or working DB writes.

prepare performs no HTTP. serve is an explicit, separately authorized step and
stops on an owned STOP file; check then verifies frozen data and protected files.
All generated archives, dependencies and snapshots remain ignored local evidence.
"""
import argparse
import datetime
import hashlib
import importlib.util
import json
import os
from pathlib import Path
import shutil
import socket
import subprocess
import sys
import tarfile
import time
import uuid
import re
import urllib.request
import urllib.error
import urllib.parse
import html as html_entities
from concurrent.futures import ThreadPoolExecutor

REPO = Path(__file__).resolve().parents[2]
OUT = REPO / 'storage/app/seo-m3-2-local'
SHAS = {'A': '7c649117e747fb922da166c2e1fba59474fee203', 'B': '54d34083a67e30084b967b572cc5a6b93c2eaf99'}
HERE = Path(__file__).resolve().parent
spec = importlib.util.spec_from_file_location('m31_guard', HERE / 'run-isolated-tests.py')
guard = importlib.util.module_from_spec(spec)
spec.loader.exec_module(guard)


def stamp():
    return datetime.datetime.now(datetime.timezone.utc).isoformat()


def save(path, value):
    path.write_text(json.dumps(value, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')


def native(path):
    """Long archived seeder names exceed Windows MAX_PATH in private stands."""
    value = str(Path(path).absolute())
    return Path('\\\\?\\' + value) if os.name == 'nt' and not value.startswith('\\\\?\\') else Path(value)


def owned(path):
    resolved = Path(path).resolve()
    relative = resolved.relative_to(OUT.resolve())
    first = relative.parts[0]
    if not first.startswith('stands-') or len(first) != 39 or any(c not in '0123456789abcdef' for c in first[7:]):
        raise ValueError('Not an owned stand directory')
    return resolved


def clean_env():
    # Do not hand inherited API/DB/auth tokens to archived application processes.
    allowed = {'PATH', 'SYSTEMROOT', 'WINDIR', 'COMSPEC', 'PATHEXT', 'TEMP', 'TMP', 'HOME',
               'USERPROFILE', 'APPDATA', 'LOCALAPPDATA', 'PHP_INI_SCAN_DIR', 'NUMBER_OF_PROCESSORS'}
    return {k: v for k, v in os.environ.items() if k.upper() in allowed}


def run(command, cwd, env, prefix):
    if prefix.with_suffix('.stdout.bin').exists():
        prefix = prefix.parent / (prefix.name + '-' + uuid.uuid4().hex)
    result = subprocess.run(command, cwd=cwd, env=env, capture_output=True,
                            creationflags=subprocess.CREATE_NO_WINDOW if os.name == 'nt' else 0)
    prefix.with_suffix('.stdout.bin').write_bytes(result.stdout)
    prefix.with_suffix('.stderr.bin').write_bytes(result.stderr)
    if result.returncode:
        raise RuntimeError(f'{prefix.name} failed exit {result.returncode}; inspect private stderr')
    return result.stdout


def copy_tree(source, destination):
    source = source.resolve()
    files = [p for p in source.rglob('*') if p.is_file()]
    def one(path):
        path.resolve().relative_to(source)  # No dependency junction may escape its source tree.
        target = destination / path.relative_to(source)
        native(target.parent).mkdir(parents=True, exist_ok=True)
        if native(target).is_file():
            if guard.file_sha256(path) != guard.file_sha256(native(target)):
                raise ValueError('Existing private dependency differs from source')
        else:
            shutil.copy2(path, native(target))
        return path.relative_to(source).as_posix(), guard.file_sha256(native(target))
    with ThreadPoolExecutor(max_workers=8) as pool:
        fingerprints = dict(pool.map(one, files))
    return guard.fingerprint_digest(fingerprints)


def extract_archive(archive, destination):
    with tarfile.open(archive) as handle:
        for member in handle:
            target = (destination / member.name).resolve()
            target.relative_to(destination.resolve())
            if member.name == 'public/storage':
                continue  # Tracked symlink points at working runtime; never reproduce it.
            if member.issym() or member.islnk():
                raise ValueError('Archive links are not allowed')
            if member.name.startswith('database/seeders/questions/'):
                continue  # DB snapshot is authoritative; no observer/snapshot writes.
            if member.isfile():
                native(target.parent).mkdir(parents=True, exist_ok=True)
                if native(target).is_file():
                    with handle.extractfile(member) as src:
                        if native(target).read_bytes() != src.read():
                            raise ValueError('Existing archived source differs: ' + member.name)
                    continue
                with handle.extractfile(member) as src, native(target).open('xb') as dst:
                    shutil.copyfileobj(src, dst)
            elif member.isdir():
                native(target).mkdir(parents=True, exist_ok=True)


def free_port():
    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        return sock.getsockname()[1]


def require_loopback_base(base):
    match = re.fullmatch(r'http://127\.0\.0\.1:([1-9][0-9]{3,4})', base) if isinstance(base, str) else None
    if not match or int(match.group(1)) > 65535:
        raise ValueError('Stand server must bind a valid explicit IPv4 loopback port')


def verify(record, label):
    folder = owned(record['folder'])
    before = json.loads((folder / 'protected-before.json').read_text(encoding='utf-8'))
    after = guard.fingerprint_files(REPO)
    changed = sorted(p for p in before.keys() | after.keys() if before.get(p) != after.get(p))
    data_hashes = {key: guard.file_sha256(owned(folder / key / 'data.sqlite')) for key in SHAS}
    proof = {'checkedAt': stamp(), 'workingFilesBeforeSha256': guard.fingerprint_digest(before),
             'workingFilesAfterSha256': guard.fingerprint_digest(after), 'workingFilesChanged': changed,
             'protectedFilesCount': len(before), 'dataHashes': data_hashes,
             'dataReadOnly': all(value == record['dataSha256'] for value in data_hashes.values())}
    save(folder / f'{label}-safety.json', proof)
    if changed or not proof['dataReadOnly']:
        raise RuntimeError('Protected/frozen files changed; evidence retained')
    return proof


def prepare(args):
    OUT.resolve().relative_to(REPO.resolve())
    OUT.mkdir(parents=True, exist_ok=True)
    folder = owned(args.resume) if getattr(args, 'resume', None) else OUT / ('stands-' + uuid.uuid4().hex)
    if getattr(args, 'resume', None):
        existing = json.loads((folder / 'manifest.json').read_text(encoding='utf-8'))
        if existing.get('status') != 'preparing' or (folder / 'serving.json').exists():
            raise ValueError('Only never-served failed preparation may resume')
        args.snapshot = str(folder / 'data.sqlite')
    else:
        folder.mkdir()
    before = guard.fingerprint_files(REPO)
    suffix = '-' + uuid.uuid4().hex if getattr(args, 'resume', None) else ''
    save(folder / ('protected-before' + suffix + '.json'), before)
    try:
        prepare_body(args, folder)
    finally:
        after = guard.fingerprint_files(REPO)
        changed = sorted(p for p in before.keys() | after.keys() if before.get(p) != after.get(p))
        save(folder / ('prepare-finally-safety' + suffix + '.json'), {
            'workingFilesBeforeSha256': guard.fingerprint_digest(before),
            'workingFilesAfterSha256': guard.fingerprint_digest(after),
            'workingFilesChanged': changed, 'protectedFilesCount': len(before), 'checkedAt': stamp()})
        if changed:
            raise RuntimeError('Protected files changed during preparation')


def prepare_body(args, folder):
    record = {'folder': str(folder), 'createdAt': stamp(), 'status': 'preparing', 'variants': {},
              'label': 'isolated A/B; shared frozen SQLite backend, not Apache/MySQL production parity',
              'planFile': str(OUT / 'm32-frozen-plan.json')}
    save(folder / 'manifest.json', record)
    env = clean_env()
    ini = folder / 'php-ini'
    if ini.exists():
        if [p.name for p in ini.iterdir()] != ['99-isolated-tests.ini'] or (ini / '99-isolated-tests.ini').read_text() != 'opcache.enable_cli=0\n':
            raise ValueError('Unexpected existing private PHP startup override')
        env['PHP_INI_SCAN_DIR'] = env.get('PHP_INI_SCAN_DIR', '') + os.pathsep + str(ini)
    else:
        guard.configure_php_startup(env, folder)
    record['phpIniScanDir'] = env['PHP_INI_SCAN_DIR']
    record['phpBinary'] = str(Path(args.php).resolve())
    exporter = HERE / 'm32-isolated-stands-export.php'
    if args.snapshot:
        snapshot = owned(args.snapshot)
        if snapshot.name != 'data.sqlite' or snapshot.parent.name.startswith('stands-') is False:
            raise ValueError('Expected a retained root snapshot')
        raw = (snapshot.parent / 'snapshot.stdout.bin').read_bytes()
        if guard.file_sha256(snapshot) != json.loads(raw)['sha256']:
            raise ValueError('Retained snapshot hash mismatch')
        if snapshot != folder / 'data.sqlite':
            shutil.copy2(snapshot, folder / 'data.sqlite')
        (folder / 'snapshot.stdout.bin').write_bytes(raw)
        record['snapshotReusedFrom'] = str(snapshot)
        print('Reusing verified frozen educational snapshot; no new DB reads.', flush=True)
    else:
        print('Exporting read-only local educational snapshot (no Laravel bootstrap).', flush=True)
        raw = run([args.php, str(exporter), str(REPO), str(folder / 'data.sqlite')], REPO, env, folder / 'snapshot')
    record['snapshot'] = json.loads(raw)
    record['dataSha256'] = guard.file_sha256(folder / 'data.sqlite')
    archive_paths = ['app', 'bootstrap', 'config', 'public', 'resources', 'routes', 'database',
                     'composer.json', 'package.json', 'package-lock.json', 'vite.config.js',
                     'postcss.config.js', 'tailwind.config.js', 'tailwind.public.config.js', 'tools/build']
    ports = []
    for key, sha in SHAS.items():
        stand = folder / key
        stand.mkdir(exist_ok=True)
        source = stand / 'source'
        source.mkdir(exist_ok=True)
        archive = stand / 'source.tar'
        print(f'{key}: archive {sha}, copy private dependencies and build.', flush=True)
        if archive.is_file():
            with tarfile.open(archive) as handle:
                if handle.pax_headers.get('comment') != sha:
                    raise ValueError('Existing archive revision mismatch')
        elif key == 'A' and args.archive_a:
            retained = owned(args.archive_a)
            with tarfile.open(retained) as handle:
                if handle.pax_headers.get('comment') != sha:
                    raise ValueError('Retained archive revision mismatch')
            shutil.copy2(retained, archive)
        else:
            run(['git', '-c', 'safe.directory=' + str(REPO), 'archive', '--format=tar', '--output=' + str(archive), sha, *archive_paths], REPO, env, stand / 'archive')
        extract_archive(archive, source)
        for dependency in ['vendor', 'node_modules']:
            digest = copy_tree(REPO / dependency, source / dependency)
            record.setdefault('dependencyCopies', {}).setdefault(key, {})[dependency] = digest
        shutil.copy2(REPO / 'composer.lock', source / 'composer.lock')
        if (stand / 'data.sqlite').exists():
            if guard.file_sha256(stand / 'data.sqlite') != record['dataSha256']:
                raise ValueError('Existing stand snapshot differs')
        else:
            shutil.copy2(folder / 'data.sqlite', stand / 'data.sqlite')
        runtime = stand / 'runtime'
        for path in ['environment', 'bootstrap', 'storage/app', 'storage/framework/cache/data',
                     'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'storage/question-exports']:
            (runtime / path).mkdir(parents=True, exist_ok=True)
        router = stand / 'router.php'
        shutil.copy2(HERE / 'm32-isolated-stands-router.php', router)
        build_js = source / 'node_modules/vite/bin/vite.js'
        if not (source / 'public/build/manifest.json').exists():
            run([args.node, str(build_js), 'build'], source, env, stand / 'build')
        port = free_port()
        while port in ports:
            port = free_port()
        ports.append(port)
        base = 'http://127.0.0.1:' + str(port)
        child_env = {**env, 'M32_STAND': str(stand), 'M32_BASE': base}
        preflight = json.loads(run([args.php, str(router)], source, child_env, stand / 'preflight'))
        if preflight['workingEnvLoaded'] or preflight['opcacheCli'] or not preflight['dataReadOnly']:
            raise RuntimeError('Unsafe stand preflight')
        assets = json.loads((source / 'public/build/manifest.json').read_text(encoding='utf-8'))
        record['variants'][key] = {'gitSha': sha, 'base': base, 'php': preflight['php'], 'sapi': preflight['sapi'],
            'dependencies': record['dependencyCopies'][key],
            'packageLockSha256': guard.file_sha256(source / 'package-lock.json'),
            'composerLockSha256': guard.file_sha256(source / 'composer.lock'),
            'runtimePolicySha256': guard.file_sha256(router), 'dataSha256': record['dataSha256'],
            'storageId': str(runtime / 'storage'), 'sessionId': str(runtime / 'storage/framework/sessions'),
            'buildId': str(source / 'public/build'), 'preflight': preflight,
            'assets': {entry: {'file': item['file'], 'sha256': guard.file_sha256(source / 'public/build' / item['file'])}
                       for entry, item in assets.items()}}
        save(folder / 'manifest.json', record)
    for field in ['dependencies', 'packageLockSha256', 'composerLockSha256', 'runtimePolicySha256', 'dataSha256', 'php', 'sapi']:
        if record['variants']['A'][field] != record['variants']['B'][field]:
            raise RuntimeError('A/B identity mismatch: ' + field)
    record['status'] = 'prepared-not-serving'
    record['safety'] = verify(record, 'prepared')
    save(folder / 'manifest.json', record)
    print('Prepared manifest: ' + str(folder / 'manifest.json'), flush=True)


def serve(record):
    folder = owned(record['folder'])
    # Validate every variant before launching even the first child. The PHP
    # router's request-time check is too late to prevent an unintended bind.
    for variant in record['variants'].values():
        require_loopback_base(variant['base'])
    if (folder / 'STOP').exists():
        raise RuntimeError('Stand already closed; do not reuse an old lifecycle')
    verify(record, 'before-serve')
    env = clean_env()
    env['PHP_INI_SCAN_DIR'] = record['phpIniScanDir']
    children = []
    try:
        for key, variant in record['variants'].items():
            stand = folder / key
            command = [record['phpBinary'], '-S', variant['base'].removeprefix('http://'), '-t', str(stand / 'source/public'), str(stand / 'router.php')]
            stdout = (stand / 'server.stdout.bin').open('xb')
            stderr = (stand / 'server.stderr.bin').open('xb')
            child = subprocess.Popen(command, cwd=stand / 'source', env={**env, 'M32_STAND': str(stand), 'M32_BASE': variant['base']},
                stdout=stdout, stderr=stderr, creationflags=subprocess.CREATE_NO_WINDOW if os.name == 'nt' else 0)
            children.append((child, stdout, stderr))
        print('Loopback children started; no HTTP made by supervisor.', flush=True)
        save(folder / 'serving.json', {'startedAt': stamp(), 'bases': {k: v['base'] for k, v in record['variants'].items()},
                                    'pids': [child.pid for child, _, _ in children]})
        while not (folder / 'STOP').exists():
            if any(child.poll() is not None for child, _, _ in children):
                raise RuntimeError('A stand stopped unexpectedly')
            time.sleep(0.25)
    finally:
        for child, stdout, stderr in children:
            child.terminate()
            child.wait(timeout=10)
            stdout.close()
            stderr.close()
        verify(record, 'after-serve')


def provision(record):
    """Exactly four symmetric GETs, not performance samples; never follow redirects."""
    class NoRedirect(urllib.request.HTTPRedirectHandler):
        def redirect_request(self, *args, **kwargs):
            return None
    opener = urllib.request.build_opener(urllib.request.ProxyHandler({}), NoRedirect())
    rows = []
    for key in ['A', 'B']:
        variant = record['variants'][key]
        for path in ['/theory/tenses/present-perfect/present-perfect-forms', '/theory/basic-grammar/sentence-types']:
            url = variant['base'] + path
            if not re.fullmatch(r'http://127\.0\.0\.1:[1-9][0-9]{3,4}/theory/[a-z/-]+', url):
                raise ValueError('Unsafe provisioning URL')
            row = {'variant': key, 'url': url, 'startedAt': stamp()}
            try:
                with opener.open(urllib.request.Request(url, headers={'Accept': 'text/html'}), timeout=60) as response:
                    data = response.read()
                    html = data.decode('utf-8', errors='replace')
                    css = variant['assets']['resources/css/catalog-public.css']['file']
                    scripts = [urllib.parse.urlparse(html_entities.unescape(src)).path
                               for src in re.findall(r'<script\b[^>]*\bsrc=[\"\']([^\"\']+)', html, flags=re.I)]
                    public = owned(record['folder']) / key / 'source/public'
                    blocked_scripts = [script for script in scripts if not (
                        re.fullmatch(r'/livewire/livewire(?:\.min)?\.js', script)
                        or (script.startswith('/') and (public / script.lstrip('/')).is_file()))]
                    row.update({'status': response.status, 'contentSha256': hashlib.sha256(data).hexdigest(),
                        'bytes': len(data), 'h1Count': len(re.findall(r'<h1\b', html)),
                        'hasEducationalContent': 'Практика' in html and len(data) > 10000,
                        'usesExpectedPublicCss': css in html,
                        'sapi': response.headers.get('X-M32-SAPI'), 'php': response.headers.get('X-M32-PHP'),
                        'readOnly': response.headers.get('X-M32-Data-ReadOnly'),
                        'contentType': response.headers.get('Content-Type'), 'scriptPaths': scripts,
                        'blockedScriptPaths': blocked_scripts})
                    row['pass'] = (row['status'] == 200 and row['h1Count'] == 1 and row['hasEducationalContent']
                        and row['usesExpectedPublicCss'] and row['sapi'] == 'cli-server'
                        and row['php'] == variant['php'] and row['readOnly'] == 'sqlite-mode-ro-query-only'
                        and not blocked_scripts)
            except urllib.error.HTTPError as error:
                row.update({'status': error.code, 'pass': False})
            except Exception as error:
                row.update({'errorClass': type(error).__name__, 'pass': False})
            row['finishedAt'] = stamp()
            rows.append(row)
    target = owned(record['folder']) / ('provisioning-' + uuid.uuid4().hex + '.json')
    save(target, {'kind': 'four preregistered provisioning GETs; not performance', 'rows': rows})
    print(json.dumps({'evidence': str(target), 'passed': sum(row['pass'] for row in rows), 'total': 4}))
    if not all(row['pass'] for row in rows):
        raise RuntimeError('Provisioning failed; do not start performance samples')


def refresh_router(record):
    folder = owned(record['folder'])
    if (folder / 'STOP').exists():
        raise ValueError('Closed lifecycle; do not alter an accepted stand')
    previous = {key: variant['runtimePolicySha256'] for key, variant in record['variants'].items()}
    for key, variant in record['variants'].items():
        target = owned(folder / key / 'router.php')
        shutil.copy2(HERE / 'm32-isolated-stands-router.php', target)
        variant['runtimePolicySha256'] = guard.file_sha256(target)
    save(folder / ('router-refresh-' + uuid.uuid4().hex + '.json'), {'at': stamp(), 'previous': previous,
         'current': {key: value['runtimePolicySha256'] for key, value in record['variants'].items()}})
    save(folder / 'manifest.json', record)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('mode', choices=['prepare', 'serve', 'provision', 'refresh-router', 'check', 'stop'])
    parser.add_argument('--php', default='C:/Program Files/xampp/php/php.exe')
    parser.add_argument('--node', default='node')
    parser.add_argument('--manifest')
    parser.add_argument('--snapshot', help='Reuse a retained owned root snapshot after SHA verification; no live DB reads.')
    parser.add_argument('--archive-a', help='Reuse a retained owned git archive with exact A revision in its PAX header.')
    parser.add_argument('--resume', help='Resume only a never-served failed private preparation; verify existing source/dependency bytes.')
    args = parser.parse_args()
    if args.mode == 'prepare':
        prepare(args)
        return
    if not args.manifest:
        parser.error('--manifest required')
    path = owned(args.manifest)
    if path.name != 'manifest.json':
        parser.error('Expected owned manifest.json')
    record = json.loads(path.read_text(encoding='utf-8'))
    if owned(record['folder']) != path.parent or {k: v['gitSha'] for k, v in record['variants'].items()} != SHAS:
        raise ValueError('Manifest identity mismatch')
    if args.mode == 'serve':
        serve(record)
    elif args.mode == 'provision':
        provision(record)
    elif args.mode == 'refresh-router':
        refresh_router(record)
    elif args.mode == 'stop':
        (path.parent / 'STOP').touch(exist_ok=False)
    else:
        print(json.dumps(verify(record, 'checked'), indent=2))


if __name__ == '__main__':
    main()
