"""M9.1 private Composer evidence. Never installs into the working vendor.

The inventory is not a lockfile. Only Composer may resolve a candidate lock.
No application bootstrap, database access, deploy or repair actions here.
"""
import argparse
from concurrent.futures import ThreadPoolExecutor
import datetime
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import urllib.request
import zipfile

ROOT = Path(__file__).resolve().parents[2]
PRIVATE = ROOT / 'storage/app/seo-m9-1-local'
COMPOSER_VERSION = '2.10.3'


def digest(path):
    with path.open('rb') as stream:
        return hashlib.file_digest(stream, 'sha256').hexdigest()


def write_new(path, value):
    with path.open('x', encoding='utf-8') as stream:
        json.dump(value, stream, ensure_ascii=False, indent=2)


def read(path):
    return json.loads(path.read_text(encoding='utf-8'))


def regular_tree(directory):
    """Never follow junctions/symlinks, including existing parent components."""
    directory = Path(directory).absolute()
    for parent in [directory, *directory.parents]:
        if parent.exists() and (parent.is_symlink() or parent.is_junction()):
            raise ValueError('Linked path refused: ' + str(parent))
    if not directory.exists():
        return []
    files = []
    for base, dirs, names in os.walk(directory, followlinks=False):
        for name in dirs + names:
            item = Path(base) / name
            if item.is_symlink() or item.is_junction():
                raise ValueError('Linked entry refused: ' + str(item))
        files.extend(Path(base) / name for name in names)
    return files


def fingerprint():
    paths = [ROOT / '.env']
    for name in ['vendor', 'bootstrap/cache', 'database/seeders', 'database/content-patches',
                 'storage/framework', '.codex']:
        paths += regular_tree(ROOT / name)
    paths += list((ROOT / 'storage/app').glob('gramlyze-*.tar.gz'))
    paths = sorted(set(p for p in paths if p.is_file()))
    print('Fingerprinting working files:', len(paths), flush=True)
    with ThreadPoolExecutor(max_workers=8) as pool:
        return dict(zip([p.relative_to(ROOT).as_posix() for p in paths], pool.map(digest, paths)))


def safe_environment(directory, php):
    """Whitelist OS necessities; no inherited Composer/auth/proxy/application overrides."""
    allowed = {'SYSTEMROOT', 'WINDIR', 'COMSPEC', 'PATHEXT', 'NUMBER_OF_PROCESSORS',
               'PROCESSOR_ARCHITECTURE', 'SYSTEMDRIVE'}
    env = {k: v for k, v in os.environ.items() if k.upper() in allowed}
    for name in ['home', 'cache', 'temp', 'profile', 'appdata', 'localappdata']:
        (directory / name).mkdir(parents=True, exist_ok=True)
    env.update({'COMPOSER_HOME': str(directory / 'home'), 'COMPOSER_CACHE_DIR': str(directory / 'cache'),
                'COMPOSER_NO_INTERACTION': '1', 'HOME': str(directory / 'profile'),
                'USERPROFILE': str(directory / 'profile'), 'APPDATA': str(directory / 'appdata'),
                'LOCALAPPDATA': str(directory / 'localappdata'), 'TEMP': str(directory / 'temp'),
                'TMP': str(directory / 'temp'), 'PYTHONIOENCODING': 'utf-8',
                'GIT_CEILING_DIRECTORIES': str(PRIVATE), 'GIT_CONFIG_NOSYSTEM': '1',
                'GIT_CONFIG_GLOBAL': str(directory / 'profile/gitconfig'),
                'PATH': os.pathsep.join([str(Path(php).parent), str(Path(shutil.which('git') or 'git').parent),
                                       str(Path(os.environ.get('SYSTEMROOT', 'C:/Windows')) / 'System32')])})
    return env


def capture(label, command, cwd, env):
    file = PRIVATE / (label + '.json')
    if file.exists():
        raise ValueError('Evidence exists: use a new label')
    started = datetime.datetime.now(datetime.timezone.utc).isoformat()
    result = subprocess.run(command, cwd=cwd, env=env, capture_output=True)
    record = {'started': started, 'command': command, 'cwd': str(cwd), 'exit_code': result.returncode,
              'stdout': result.stdout.decode('utf-8', errors='backslashreplace'),
              'stderr': result.stderr.decode('utf-8', errors='backslashreplace'),
              'finished': datetime.datetime.now(datetime.timezone.utc).isoformat()}
    write_new(file, record)
    print(json.dumps({'label': label, 'exit_code': result.returncode, 'evidence': str(file)}), flush=True)
    return record


def package_map(value):
    return {p['name']: p for p in value['packages'] + value.get('packages-dev', [])}


def inventory(php):
    PRIVATE.mkdir(parents=True, exist_ok=True)
    if (PRIVATE / 'baseline.json').exists():
        raise ValueError('Baseline exists; do not overwrite or silently restart')
    baseline = fingerprint()
    write_new(PRIVATE / 'baseline.json', baseline)
    for name, target in [('composer.lock', 'old-composer.lock'), ('composer.json', 'old-composer.json'),
                         ('vendor/composer/installed.json', 'old-installed.json'),
                         ('vendor/composer/installed.php', 'old-installed.php')]:
        shutil.copyfile(ROOT / name, PRIVATE / target)
    env = safe_environment(PRIVATE / 'inventory-process', php)
    # No autoloader or Laravel bootstrap: this file only returns Composer metadata.
    installed_php = capture('installed-php', [php, '-d', 'opcache.enable_cli=0', '-r',
        'echo json_encode(require "vendor/composer/installed.php", JSON_THROW_ON_ERROR);'], ROOT, env)
    if installed_php['exit_code']:
        raise ValueError('Cannot inspect installed.php')
    versions = json.loads(installed_php['stdout'])['versions']
    installed = package_map(read(PRIVATE / 'old-installed.json'))
    locked = package_map(read(PRIVATE / 'old-composer.lock'))
    discrepancies = []
    for name, package in installed.items():
        actual = versions.get(name, {})
        if actual.get('pretty_version') != package['version'] or actual.get('reference') != package.get('source', {}).get('reference'):
            discrepancies.append(name)
    report = {'at': datetime.datetime.now(datetime.timezone.utc).isoformat(),
              'old_lock_sha256': digest(PRIVATE / 'old-composer.lock'),
              'installed_count': len(installed), 'locked_count': len(locked),
              'installed_php_disagreements': discrepancies,
              'different_versions': [{'name': n, 'installed': installed.get(n, {}).get('version'),
                                      'locked': locked.get(n, {}).get('version')}
                                     for n in sorted(installed.keys() | locked.keys())
                                     if installed.get(n, {}).get('version') != locked.get(n, {}).get('version')],
              'inherited_composer_env_names_only': sorted(k for k in os.environ if k.startswith('COMPOSER')),
              'protected_files': len(baseline)}
    write_new(PRIVATE / 'inventory.json', report)
    print(json.dumps(report, indent=2))


class OfficialRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        require_official(newurl)
        return super().redirect_request(req, fp, code, msg, headers, newurl)


def require_official(url):
    from urllib.parse import urlsplit
    u = urlsplit(url)
    if u.scheme != 'https' or u.hostname != 'getcomposer.org' or u.username or u.password or u.port not in (None, 443):
        raise ValueError('Only TLS official Composer download is allowed')


def download_composer():
    regular_tree(PRIVATE)
    url = 'https://getcomposer.org/download/' + COMPOSER_VERSION + '/composer.phar'
    opener = urllib.request.build_opener(urllib.request.ProxyHandler({}), OfficialRedirect())
    def get(url):
        require_official(url)
        with opener.open(url, timeout=60) as response:
            content = response.read(10_000_001)
            if len(content) > 10_000_000:
                raise ValueError('Unexpected download size')
            return content
    expected = get(url + '.sha256sum').decode().strip().split()[0]
    data = get(url)
    if not re.fullmatch('[a-f0-9]{64}', expected) or hashlib.sha256(data).hexdigest() != expected:
        raise ValueError('Official Composer SHA-256 mismatch')
    target = PRIVATE / ('composer-' + COMPOSER_VERSION + '.phar')
    with target.open('xb') as handle:
        handle.write(data)
    write_new(PRIVATE / 'composer-download.json', {'url': url, 'sha256': expected, 'bytes': len(data)})
    print(json.dumps({'version': COMPOSER_VERSION, 'sha256': expected, 'bytes': len(data)}))


def prepare_candidate():
    target = PRIVATE / 'candidate'
    regular_tree(PRIVATE)
    target.mkdir()
    source = read(PRIVATE / 'old-composer.json')
    source['require']['php'] = '^8.2.12'
    source['config']['platform'] = {'php': '8.2.12'}
    # Only installed/required trusted plugin; no blanket allow and no audit overrides.
    source['config']['allow-plugins'] = {'php-http/discovery': True}
    write_new(target / 'composer.json', source)
    shutil.copyfile(PRIVATE / 'old-composer.lock', target / 'composer.lock')
    return target


def composer(php, label, arguments):
    target = PRIVATE / 'candidate'
    if not target.is_dir():
        target = prepare_candidate()
    regular_tree(target)
    if (target / 'vendor').exists() or (target / '.env').exists():
        raise ValueError('Resolver copy must have no vendor or .env')
    # Composer may inspect root classmaps while printing dependency trees.
    # Copy only the real tracked files, never autoload or bootstrap them here.
    for name in read(target / 'composer.json').get('autoload', {}).get('classmap', []):
        source = (ROOT / name).resolve()
        if not source.is_relative_to(ROOT) or not source.is_file() or source.is_symlink():
            raise ValueError('Invalid source classmap')
        destination = target / name
        if not destination.exists():
            destination.parent.mkdir(parents=True, exist_ok=True)
            shutil.copyfile(source, destination)
    binary = PRIVATE / ('composer-' + COMPOSER_VERSION + '.phar')
    expected = read(PRIVATE / 'composer-download.json')['sha256']
    if digest(binary) != expected:
        raise ValueError('Composer binary changed')
    return capture(label, [php, '-d', 'opcache.enable_cli=0', str(binary), '--no-plugins', '--no-scripts',
                           '--no-interaction', *arguments], target, safe_environment(PRIVATE / 'resolver-process', php))


def resolve(php, label):
    installed = package_map(read(PRIVATE / 'old-installed.json'))
    constraints = ['--with=' + n + ':' + p['version'] for n, p in sorted(installed.items())]
    return composer(php, label, ['update', '--no-install', '--prefer-dist', '--minimal-changes', '--audit-format=json', *constraints])


def check_guard(label):
    before, after = read(PRIVATE / 'baseline.json'), fingerprint()
    changed = sorted(p for p in before.keys() | after.keys() if before.get(p) != after.get(p))
    report = {'before_count': len(before), 'after_count': len(after), 'changed': changed,
              'before_sha256': hashlib.sha256(json.dumps(before, sort_keys=True).encode()).hexdigest(),
              'after_sha256': hashlib.sha256(json.dumps(after, sort_keys=True).encode()).hexdigest()}
    write_new(PRIVATE / (label + '-guard.json'), report)
    print(json.dumps(report))
    return not changed


def official_package(name, version):
    """Expand Composer's official minified metadata cache; never write package metadata."""
    cache = PRIVATE / 'resolver-process/cache/repo/https---repo.packagist.org' / ('provider-' + name.replace('/', '~') + '.json')
    data = read(cache)
    if data.get('minified') not in (None, 'composer/2.0'):
        raise ValueError('Unknown Composer metadata encoding')
    expanded = {}
    for item in data['packages'][name]:
        if not data.get('minified'):
            expanded = {}
        for key, value in item.items():
            if value == '__unset':
                expanded.pop(key, None)
            else:
                expanded[key] = value
        if expanded['version'].lstrip('v') == version.lstrip('v'):
            return expanded
    raise ValueError('Installed version not in official metadata: ' + name)


def compare_archive(archive, directory):
    """Compare bytes without extracting or executing downloaded package code."""
    local = {p.relative_to(directory).as_posix(): digest(p) for p in regular_tree(directory)}
    expected = {}
    with zipfile.ZipFile(archive) as package:
        entries = [e for e in package.infolist() if not e.is_dir()]
        if sum(e.file_size for e in entries) > 200_000_000:
            raise ValueError('Unexpected package archive size')
        roots = {e.filename.split('/')[0] for e in entries}
        if len(roots) != 1:
            raise ValueError('Unexpected archive layout')
        for entry in entries:
            relative = entry.filename.split('/', 1)[1]
            if '..' in relative.split('/') or relative.startswith('/') or '\\' in relative:
                raise ValueError('Unsafe archive member')
            if relative in expected:
                raise ValueError('Duplicate archive member')
            expected[relative] = hashlib.sha256(package.read(entry)).hexdigest()
    return {'archive_files': len(expected), 'working_files': len(local),
            'changed': sorted(p for p in expected.keys() & local.keys() if expected[p] != local[p]),
            'missing': sorted(expected.keys() - local.keys()), 'extra': sorted(local.keys() - expected.keys())}


def vendor_integrity(label, only=None):
    destination = PRIVATE / (label + '-archives')
    destination.mkdir()
    installed = package_map(read(PRIVATE / 'old-installed.json'))
    if only:
        if only not in installed:
            raise ValueError('Unknown package in installed inventory')
        installed = {only: installed[only]}
    def inspect(item):
        name, current = item
        record = {'package': name, 'version': current['version']}
        try:
            official = official_package(name, current['version'])
            if any(official[k]['reference'] != current[k]['reference'] for k in ['source', 'dist']):
                raise ValueError('Installed references differ from official Packagist metadata')
            if official['source'] != current['source'] or official['dist'] != current['dist']:
                record['metadata_url_change'] = {'installed_source': current['source'], 'installed_dist': current['dist']}
            match = re.fullmatch(r'https://github.com/([\w.-]+/[\w.-]+)\.git', official['source']['url'])
            reference = official['source']['reference']
            if not match or not re.fullmatch('[a-f0-9]{40}', reference) or reference != official['dist']['reference']:
                raise ValueError('Unsupported public package source')
            url = 'https://codeload.github.com/' + match[1] + '/legacy.zip/' + reference
            # These pinned GitHub archives are the dist source, not a Composer repository override.
            class NoRedirect(urllib.request.HTTPRedirectHandler):
                def redirect_request(self, *args, **kwargs):
                    raise ValueError('Unexpected package redirect')
            opener = urllib.request.build_opener(urllib.request.ProxyHandler({}), NoRedirect())
            target = destination / (name.replace('/', '~') + '.zip')
            with opener.open(url, timeout=60) as response, target.open('xb') as handle:
                total = 0
                while chunk := response.read(1024 * 1024):
                    total += len(chunk)
                    if total > 100_000_000:
                        raise ValueError('Unexpected download size')
                    handle.write(chunk)
            record.update({'metadata_verified': True, 'source': official['source'], 'dist': official['dist'],
                           'download_url': url, 'archive_sha256': digest(target),
                           **compare_archive(target, ROOT / 'vendor' / name)})
        except Exception as error:
            record['error'] = type(error).__name__ + ': ' + str(error)
        write_new(destination / (name.replace('/', '~') + '.json'), record)
        print(json.dumps({'package': name, 'changed': len(record.get('changed', [])),
                          'missing': len(record.get('missing', [])), 'extra': len(record.get('extra', [])),
                          'error': record.get('error')}), flush=True)
        return record
    with ThreadPoolExecutor(max_workers=4) as pool:
        records = list(pool.map(inspect, sorted(installed.items())))
    write_new(PRIVATE / (label + '-integrity.json'), records)
    return all(not r.get('error') and not r['changed'] and not r['missing'] and not r['extra'] for r in records)


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('phase', choices=['inventory', 'download-composer', 'inspect', 'validate', 'resolve', 'boundary', 'vendor-integrity', 'guard'])
    parser.add_argument('--php', default=shutil.which('php'))
    parser.add_argument('--label', default='initial')
    parser.add_argument('--package', help='Explicit single-package integrity follow-up; no install')
    args = parser.parse_args()
    if not re.fullmatch('[a-z0-9-]+', args.label):
        parser.error('Use a new simple evidence label')
    if args.package and args.phase != 'vendor-integrity':
        parser.error('--package is only for an explicit integrity follow-up')
    if args.phase == 'inventory':
        inventory(args.php)
    elif args.phase == 'download-composer':
        download_composer()
    elif args.phase == 'inspect':
        results = [composer(args.php, args.label + '-version', ['--version']),
                   composer(args.php, args.label + '-why-css', ['why', 'symfony/css-selector', '--locked', '--tree']),
                   composer(args.php, args.label + '-platform', ['check-platform-reqs', '--lock']),
                   composer(args.php, args.label + '-audit', ['audit', '--locked', '--format=json'])]
        raise SystemExit(next((r['exit_code'] for r in results if r['exit_code']), 0))
    elif args.phase == 'validate':
        raise SystemExit(composer(args.php, args.label, ['validate', '--strict', '--no-check-publish'])['exit_code'])
    elif args.phase == 'resolve':
        raise SystemExit(resolve(args.php, args.label)['exit_code'])
    elif args.phase == 'boundary':
        composer(args.php, args.label + '-policy', ['config', '--list', '--source'])
        composer(args.php, args.label + '-validate', ['validate', '--strict', '--no-check-publish'])
        result = composer(args.php, args.label, ['update', 'laravel/framework', 'symfony/css-selector',
            'guzzlehttp/guzzle', 'livewire/livewire', 'league/commonmark', '--with-all-dependencies',
            '--minimal-changes', '--no-install', '--prefer-dist', '--audit-format=json'])
        raise SystemExit(result['exit_code'])
    elif args.phase == 'vendor-integrity':
        raise SystemExit(0 if vendor_integrity(args.label, args.package) else 1)
    elif args.phase == 'guard':
        raise SystemExit(0 if check_guard(args.label) else 3)
