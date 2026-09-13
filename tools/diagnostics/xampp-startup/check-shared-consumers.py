"""Bounded, loopback-only PHP handler checks for the inventoried local XAMPP sites.

Creates one unique PHP file per existing consumer root and removes only those
exact files, after checking their hashes. No application body, cookie, credential,
or configuration is retained. Existing application GETs never follow redirects.
An existing invalid HTTPS certificate is reported with strict verification.
An explicitly supplied replacement certificate SHA permits positive acceptance
after rotation, with both normal trust/hostname checks and an exact peer pin.
This tool has no insecure TLS option. Requires permission to write the
seven inventoried sibling-site roots. Does not change handlers/configuration.
"""
from __future__ import annotations

import argparse
import concurrent.futures
import datetime as dt
import hashlib
import http.client
import json
import re
import secrets
import socket
import ssl
from pathlib import Path


ROOT = Path(__file__).resolve().parents[3]
OUT = ROOT / 'storage/app/seo-m9-4-startup-local'
XAMPP = Path('C:/Program Files/xampp')
ROOTS = {
    'lara': Path('D:/DEV/htdocs/lara.loc/public'),
    'adminer': Path('D:/DEV/htdocs/adminer.loc'),
    'diyxml': Path('D:/DEV/htdocs/diyxml.loc'),
    'xml-mapper': Path('D:/DEV/htdocs/xml-mapper.loc/public'),
    'e-shpop': Path('D:/DEV/htdocs/e-shpop.loc/public'),
    'vs-frontend': Path('D:/DEV/htdocs/vs.loc/frontend/web'),
    'vs-backend': Path('D:/DEV/htdocs/vs.loc/backend/web'),
    'phpmyadmin': XAMPP / 'phpMyAdmin',
    'webalizer': XAMPP / 'webalizer',
    'default-https': XAMPP / 'htdocs',
}
# scheme, Host/SNI, URL prefix, physical root. HTTPS always connects to loopback.
PROBES = (
    ('http', 'lara.loc', '/', 'lara'),
    ('http', 'localhost', '/', 'lara'),
    ('http', 'm94-unmatched.localhost', '/', 'lara'),
    ('http', 'adminer.loc', '/', 'adminer'),
    ('http', 'diyxml.loc', '/', 'diyxml'),
    ('http', 'xml-mapper.loc', '/', 'xml-mapper'),
    ('http', 'e-shpop.loc', '/', 'e-shpop'),
    ('http', 'vs.loc', '/', 'vs-frontend'),
    ('http', 'vs.loc', '/admin/', 'vs-backend'),
    ('http', 'localhost', '/phpmyadmin/', 'phpmyadmin'),
    ('http', 'localhost', '/webalizer/', 'webalizer'),
    ('http', 'gramlyze.loc', '/phpmyadmin/', 'phpmyadmin'),
    ('http', 'gramlyze.loc', '/webalizer/', 'webalizer'),
    ('https', 'localhost', '/', 'default-https'),
    ('https', 'localhost', '/phpmyadmin/', 'phpmyadmin'),
    ('https', 'localhost', '/webalizer/', 'webalizer'),
)
# No webalizer.php, diyxml conversion action, login submission or DB API.
APPLICATION_GETS = (
    ('lara.loc', '/'), ('localhost', '/'), ('adminer.loc', '/'),
    ('xml-mapper.loc', '/'), ('e-shpop.loc', '/'),
    ('vs.loc', '/'), ('vs.loc', '/admin/'),
    ('localhost', '/phpmyadmin/'), ('vsemerch.loc', '/'),
    ('vsemerch.loc', '/admin/'),
)

PHP = r'''<?php
declare(strict_types=1);
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
    || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET'
    || !hash_equals('__NONCE__', $_SERVER['HTTP_X_M94_PROBE'] ?? '')) {
    http_response_code(404); exit;
}
header('Content-Type: application/json');
header('Cache-Control: no-store');
$result = [
    'probe' => 'm94-shared-runtime-v1', 'sapi' => PHP_SAPI,
    'php' => PHP_VERSION, 'curl_loaded' => extension_loaded('curl'),
    'openssl_loaded' => extension_loaded('openssl'),
    'mysqli_loaded' => extension_loaded('mysqli'),
    'pdo_mysql_loaded' => extension_loaded('pdo_mysql'),
    'openssl_version' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : null,
    'curl_version' => extension_loaded('curl') ? curl_version()['version'] : null,
    'curl_ssl_version' => extension_loaded('curl') ? curl_version()['ssl_version'] : null,
];
if (($_SERVER['HTTP_X_M94_CURL'] ?? '') === '1') {
    $result['https'] = ['attempted' => false];
    if (extension_loaded('curl')) {
        $handle = curl_init('https://www.php.net/');
        curl_setopt_array($handle, [CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => 'C:/Program Files/xampp/php-8.5.10-nts-gramlyze/extras/ssl/cacert.pem',
            CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 25,
            CURLOPT_WRITEFUNCTION => static fn($handle, string $bytes): int => strlen($bytes)]);
        $ok = curl_exec($handle);
        $result['https'] = ['attempted' => true, 'success' => $ok === true,
            'errno' => curl_errno($handle),
            'status' => curl_getinfo($handle, CURLINFO_RESPONSE_CODE),
            'ssl_verify_result' => curl_getinfo($handle, CURLINFO_SSL_VERIFYRESULT),
            'verify_peer' => true, 'verify_host' => 2,
            'ca_source' => 'accepted-gramlyze-ca-bundle'];
    }
}
echo json_encode($result, JSON_THROW_ON_ERROR);
'''


class LoopbackTLS(http.client.HTTPSConnection):
    def __init__(self, *args, expected_certificate_sha256=None, **kwargs):
        super().__init__(*args, **kwargs)
        self.expected_certificate_sha256 = expected_certificate_sha256

    def connect(self):
        raw = socket.create_connection(('127.0.0.1', 443), self.timeout)
        try:
            self.sock = self._context.wrap_socket(raw, server_hostname=self.host)
            if self.expected_certificate_sha256 is not None:
                actual = hashlib.sha256(self.sock.getpeercert(binary_form=True)).hexdigest()
                if actual != self.expected_certificate_sha256:
                    raise ssl.SSLError('Presented certificate does not match the explicit pin')
        except BaseException:
            if self.sock:
                self.sock.close()
            raw.close()
            raise


def certificate_material(expected_sha256):
    """Pin exact PEM bytes once, before probe creation, then use that snapshot."""
    if not re.fullmatch(r'[a-fA-F0-9]{64}', expected_sha256):
        raise ValueError('Certificate SHA-256 must be 64 hexadecimal characters')
    data = (XAMPP / 'apache/conf/ssl.crt/server.crt').read_bytes()
    if hashlib.sha256(data).hexdigest() != expected_sha256.lower():
        raise ValueError('Installed certificate does not match the explicit SHA-256')
    pem = data.decode('ascii')
    if pem.count('-----BEGIN CERTIFICATE-----') != 1:
        raise ValueError('The explicit pin requires exactly one certificate')
    der = ssl.PEM_cert_to_DER_cert(pem)
    return {'pem': pem, 'der_sha256': hashlib.sha256(der).hexdigest(),
            'file_sha256': expected_sha256.lower()}


def request(scheme, host, path, headers=None, probe=False, certificate=None):
    """A fixed loopback transport; no proxy, DNS, redirect or cookie jar."""
    result = {'scheme': scheme, 'host': host, 'path': path}
    connection = None
    try:
        if scheme == 'https':
            if certificate is None:
                context = ssl.create_default_context(cafile=str(XAMPP / 'apache/conf/ssl.crt/server.crt'))
                connection = LoopbackTLS(host, timeout=40, context=context)
            else:
                context = ssl.create_default_context(cadata=certificate['pem'])
                connection = LoopbackTLS(host, timeout=40, context=context,
                                         expected_certificate_sha256=certificate['der_sha256'])
        else:
            connection = http.client.HTTPConnection('127.0.0.1', 80, timeout=40)
        connection.request('GET', path, headers={
            'Host': host, 'Connection': 'close',
            'User-Agent': 'Gramlyze-M9.4-shared-local-acceptance', **(headers or {}),
        })
        response = connection.getresponse()
        result.update(status=response.status,
                      content_type=response.getheader('Content-Type', '').split(';')[0],
                      redirect=300 <= response.status < 400)
        if scheme == 'https':
            result['tls_verified'] = True
            result['certificate_pin_verified'] = certificate is not None
        # Existing app bodies and redirects may contain private information.
        # Only tiny diagnostic JSON is read, and only allowlisted fields retained.
        if probe:
            body = response.read(8193)
            result['source_exposed'] = b'<?php' in body
            try:
                decoded = json.loads(body)
                allowed = {'probe', 'sapi', 'php', 'curl_loaded', 'openssl_loaded',
                           'mysqli_loaded', 'pdo_mysql_loaded', 'openssl_version',
                           'curl_version', 'curl_ssl_version', 'https'}
                if (isinstance(decoded, dict) and set(decoded) <= allowed
                        and decoded.get('probe') == 'm94-shared-runtime-v1'):
                    result['runtime'] = decoded
                else:
                    result['invalid_probe'] = True
            except (ValueError, UnicodeError):
                result['invalid_probe'] = True
    except ssl.SSLCertVerificationError as error:
        result.update(error='tls-verification', verify_code=error.verify_code,
                      verify_message=error.verify_message)
    except Exception as error:
        result['error'] = type(error).__name__
    finally:
        if connection:
            connection.close()
    return result


def collect(label, baseline=None, certificate_sha256=None):
    if not re.fullmatch(r'[a-z0-9][a-z0-9-]{0,75}', label):
        raise ValueError('Label must be a short lowercase identifier')
    certificate = certificate_material(certificate_sha256) if certificate_sha256 else None
    OUT.mkdir(parents=True, exist_ok=True)
    output = OUT / f'{label}-shared-consumers.json'
    if output.exists():
        raise FileExistsError(output)
    baseline_data = json.loads(Path(baseline).read_text()) if baseline else None
    nonce = secrets.token_hex(32)
    filename = f'm94-shared-{secrets.token_hex(16)}.php'
    payload = PHP.replace('__NONCE__', nonce).encode('utf-8')
    payload_hash = hashlib.sha256(payload).hexdigest()
    created = []
    result = {'label': label, 'utc': dt.datetime.now(dt.timezone.utc).isoformat(),
              'probes': [], 'apps': [], 'cleanup': [], 'strict_tls': True,
              'redirects_followed': False, 'app_bodies_retained': False}
    if certificate:
        result['explicit_certificate_sha256'] = certificate['file_sha256']
    try:
        for key, directory in ROOTS.items():
            directory = directory.resolve(strict=True)
            destination = directory / filename
            with destination.open('xb') as handle:
                # Track immediately so finally also reports interrupted writes.
                created.append((key, destination))
                handle.write(payload)
        def check_probe(spec):
            scheme, host, prefix, key = spec
            headers = {'X-M94-Probe': nonce}
            if host == 'lara.loc' and scheme == 'http':
                headers['X-M94-Curl'] = '1'
            evidence = request(scheme, host, prefix + filename, headers, probe=True,
                               certificate=certificate if scheme == 'https' else None)
            evidence.update(root=key, path=prefix + '<own-probe>.php')
            return evidence
        with concurrent.futures.ThreadPoolExecutor(max_workers=3) as pool:
            result['probes'] = list(pool.map(check_probe, PROBES))
            result['apps'] = list(pool.map(lambda pair: request('http', *pair), APPLICATION_GETS))
        http_probes = [row for row in result['probes'] if row['scheme'] == 'http']
        result['http_php_handlers_pass'] = all(
            row.get('status') == 200 and not row.get('source_exposed')
            and row.get('runtime', {}).get('sapi') == 'apache2handler'
            and row['runtime'].get('php') == '8.5.10' for row in http_probes)
        if certificate:
            https_probes = [row for row in result['probes'] if row['scheme'] == 'https']
            result['pinned_https_php_handlers_pass'] = len(https_probes) == 3 and all(
                row.get('status') == 200 and not row.get('source_exposed')
                and row.get('tls_verified') is True and row.get('certificate_pin_verified') is True
                and row.get('runtime', {}).get('sapi') == 'apache2handler'
                and row['runtime'].get('php') == '8.5.10'
                and all(row['runtime'].get(key) is True for key in (
                    'curl_loaded', 'openssl_loaded', 'mysqli_loaded', 'pdo_mysql_loaded'))
                for row in https_probes)
        if baseline_data:
            def comparable(rows):
                return [{key: row.get(key) for key in ('scheme', 'host', 'path', 'status',
                                                      'content_type', 'redirect', 'error')}
                        for row in rows]
            result['app_statuses_unchanged'] = comparable(result['apps']) == comparable(baseline_data['apps'])
            result['all_http_curl_loaded'] = all(row.get('runtime', {}).get('curl_loaded') for row in http_probes)
            def stable_runtime(rows):
                return [
                    {'host': row['host'], 'root': row['root'],
                     **{key: row.get('runtime', {}).get(key) for key in (
                         'sapi', 'php', 'openssl_loaded', 'mysqli_loaded', 'pdo_mysql_loaded')}}
                    for row in rows if row['scheme'] == 'http'
                ]
            result['existing_php_runtime_unchanged'] = (
                stable_runtime(result['probes']) == stable_runtime(baseline_data['probes']))
            def tls_status(rows):
                return [{key: row.get(key) for key in ('host', 'root', 'status', 'error', 'verify_code')}
                        for row in rows if row['scheme'] == 'https']
            result['default_https_verification_unchanged'] = (
                tls_status(result['probes']) == tls_status(baseline_data['probes']))
            curl_probes = [row for row in http_probes if row['host'] == 'lara.loc']
            result['https_curl_pass'] = len(curl_probes) == 1 and all(
                row.get('runtime', {}).get('https', {}).get('success') is True
                and row['runtime']['https'].get('status') == 200
                and row['runtime']['https'].get('errno') == 0
                and row['runtime']['https'].get('ssl_verify_result') == 0
                for row in curl_probes)
    except Exception as error:
        result['run_error'] = type(error).__name__
        raise
    finally:
        for key, destination in reversed(created):
            cleanup = {'root': key}
            try:
                actual = destination.read_bytes()
                unchanged = hashlib.sha256(actual).hexdigest() == payload_hash
                if unchanged and destination.name == filename and destination.parent == ROOTS[key].resolve():
                    destination.unlink()
                cleanup.update(removed=not destination.exists(), content_owned=unchanged)
            except FileNotFoundError:
                cleanup.update(removed=True, content_owned=None)
            except OSError as error:
                # One failed deletion must not skip the remaining own probes.
                cleanup.update(removed=False, error=type(error).__name__)
            result['cleanup'].append(cleanup)
        result['cleanup_pass'] = len(created) == len(ROOTS) and all(row['removed'] for row in result['cleanup'])
        output.write_text(json.dumps(result, indent=2) + '\n', encoding='utf-8')
        print(json.dumps({'evidence': str(output), **{key: value for key, value in result.items()
                         if key.endswith('_pass') or key.endswith('_unchanged') or key == 'all_http_curl_loaded'}}))
    return result


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--label', required=True)
    parser.add_argument('--baseline', type=Path)
    parser.add_argument('--certificate-sha256',
                        help='Explicit SHA-256 of installed PEM cert for positive HTTPS acceptance after rotation')
    arguments = parser.parse_args()
    evidence = collect(arguments.label, arguments.baseline, arguments.certificate_sha256)
    if not evidence['http_php_handlers_pass'] or not evidence['cleanup_pass']:
        raise SystemExit(1)
    if arguments.certificate_sha256 and not evidence['pinned_https_php_handlers_pass']:
        raise SystemExit(1)
    if arguments.baseline and not all(evidence[key] for key in (
            'app_statuses_unchanged', 'existing_php_runtime_unchanged',
            'all_http_curl_loaded', 'https_curl_pass')):
        raise SystemExit(1)
    if arguments.baseline and not arguments.certificate_sha256 and not evidence['default_https_verification_unchanged']:
        raise SystemExit(1)
