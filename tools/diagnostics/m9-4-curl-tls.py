"""Private M9.4 TLS fixture and web-SAPI cURL acceptance driver.

The only HTTP request made by this tool is a fixed, local request to the
temporary Gramlyze API probe. Its fixture binds strictly to 127.0.0.1 and all
certificates, keys, nonce control data and raw subprocess output stay under an
ignored storage directory. It removes only files it created.
"""
from __future__ import annotations

import argparse
import datetime as dt
import hashlib
import http.server
import json
import os
from pathlib import Path
import re
import secrets
import shutil
import ssl
import subprocess
import threading
import time
import urllib.error
import urllib.request
import uuid


ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / 'storage/app/seo-m9-4-local'
CONTROL = OUT / 'curl-tls-control.json'
SCHEMA = 'gramlyze-m9-4-curl-tls-control-v1'
ENDPOINT = 'http://gramlyze.loc/api/_local/m9-4-curl-tls'
RUN_PREFIX = 'm94-curl-tls-'
WEB_CONFIG_KEYS = (
    'own_binary', 'own_ini', 'no_extra_ini', 'own_extension_dir', 'curl_ca_file', 'openssl_ca_file',
    'front_controller', 'document_root', 'cgi_fix_pathinfo_disabled', 'required_extensions',
    'runtime_directories_writable', 'session_path_preserved', 'session_serialization_preserved',
    'php_children_disabled', 'php_max_requests',
)


def stamp() -> str:
    return dt.datetime.now(dt.timezone.utc).isoformat()


def validate_label(label: str) -> str:
    if not isinstance(label, str) or not re.fullmatch(r'[a-z0-9][a-z0-9-]{0,63}', label):
        raise ValueError('A simple lowercase unique label is required')
    return label


def owned_run(path: Path) -> Path:
    resolved = path.resolve()
    output = OUT.resolve()
    if resolved.parent != output or not re.fullmatch(RUN_PREFIX + r'[0-9a-f]{32}', resolved.name):
        raise ValueError('Refusing a path outside an owned M9.4 fixture directory')
    return resolved


def fixture_urls(port: int) -> tuple[str, str]:
    if not isinstance(port, int) or not 1024 <= port <= 65535:
        raise ValueError('Fixture port must be an unprivileged TCP port')
    return f'https://localhost:{port}/ok', f'https://127.0.0.1:{port}/ok'


def safe_control(control: dict) -> bool:
    fixture = control.get('fixture') if isinstance(control, dict) else None
    if not isinstance(fixture, dict) or control.get('schema') != SCHEMA:
        return False
    nonce = control.get('nonce')
    if not isinstance(nonce, str) or not re.fullmatch(r'[a-f0-9]{64}', nonce):
        return False
    required = ['positive_url', 'negative_url', 'ca_path']
    return all(isinstance(fixture.get(key), str) for key in required)


def write_json_exclusive(path: Path, value: dict) -> None:
    with path.open('x', encoding='utf-8') as stream:
        json.dump(value, stream, ensure_ascii=False, indent=2)
        stream.write('\n')


def openssl_subprocess_environment(openssl: Path) -> dict[str, str]:
    """Use only the verified XAMPP Apache OpenSSL configuration for children."""
    if openssl.is_symlink():
        raise RuntimeError('openssl-symlink-not-allowed')
    executable = openssl.resolve()
    if not executable.is_file():
        raise RuntimeError('openssl-not-found')
    apache_root = executable.parent.parent
    config_dir = apache_root / 'conf'
    config = config_dir / 'openssl.cnf'
    if config.is_symlink() or not config.is_file():
        raise RuntimeError('openssl-config-not-found')
    resolved_config = config.resolve()
    if resolved_config.parent != config_dir.resolve():
        raise RuntimeError('openssl-config-outside-apache-conf')
    environment = os.environ.copy()
    # Do not alter the process-wide environment: this only affects openssl children.
    environment['OPENSSL_CONF'] = str(resolved_config)
    return environment


def run_command(command: list[str], cwd: Path, output: Path, name: str, environment: dict[str, str]) -> None:
    result = subprocess.run(command, cwd=cwd, capture_output=True, check=False,
                            creationflags=subprocess.CREATE_NO_WINDOW if os.name == 'nt' else 0,
                            env=environment)
    (output / f'{name}.stdout.bin').write_bytes(result.stdout)
    (output / f'{name}.stderr.bin').write_bytes(result.stderr)
    if result.returncode != 0:
        raise RuntimeError('openssl-command-failed')


def create_fixture_certificates(openssl: Path, run: Path) -> tuple[Path, Path, Path]:
    executable = openssl.resolve()
    environment = openssl_subprocess_environment(openssl)

    ca_key, ca_cert = run / 'ca.key', run / 'ca.pem'
    leaf_key, leaf_csr, leaf_cert = run / 'leaf.key', run / 'leaf.csr', run / 'leaf.pem'
    extension = run / 'leaf-ext.cnf'
    extension.write_text(
        '[v3_req]\n'
        'subjectAltName=DNS:localhost\n'
        'basicConstraints=critical,CA:FALSE\n'
        'keyUsage=critical,digitalSignature,keyEncipherment\n'
        'extendedKeyUsage=serverAuth\n',
        encoding='ascii',
    )
    run_command([str(executable), 'req', '-x509', '-new', '-nodes', '-newkey', 'rsa:2048', '-sha256', '-days', '1',
                 '-subj', '/CN=Gramlyze M9.4 Local CA', '-keyout', str(ca_key), '-out', str(ca_cert)], run, run, 'ca', environment)
    run_command([str(executable), 'req', '-new', '-nodes', '-newkey', 'rsa:2048', '-sha256',
                 '-subj', '/CN=localhost', '-keyout', str(leaf_key), '-out', str(leaf_csr)], run, run, 'leaf-request', environment)
    run_command([str(executable), 'x509', '-req', '-in', str(leaf_csr), '-CA', str(ca_cert), '-CAkey', str(ca_key),
                 '-CAcreateserial', '-out', str(leaf_cert), '-days', '1', '-sha256', '-extfile', str(extension),
                 '-extensions', 'v3_req'], run, run, 'leaf-sign', environment)
    if not all(path.is_file() and not path.is_symlink() for path in [ca_key, ca_cert, leaf_key, leaf_cert]):
        raise RuntimeError('fixture-certificate-missing')
    return ca_cert, leaf_cert, leaf_key


class FixtureHandler(http.server.BaseHTTPRequestHandler):
    protocol_version = 'HTTP/1.1'

    def do_GET(self) -> None:  # noqa: N802 - BaseHTTPRequestHandler API
        if self.path != '/ok':
            self.send_error(404)
            return
        self.send_response(204)
        self.send_header('Content-Length', '0')
        self.send_header('Cache-Control', 'no-store')
        self.end_headers()

    def log_message(self, format: str, *args: object) -> None:
        # The runner stores structured, secret-free evidence only.
        return


def start_fixture(certificate: Path, key: Path) -> tuple[http.server.ThreadingHTTPServer, threading.Thread]:
    server = http.server.ThreadingHTTPServer(('127.0.0.1', 0), FixtureHandler)
    context = ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER)
    context.minimum_version = ssl.TLSVersion.TLSv1_2
    context.load_cert_chain(certfile=str(certificate), keyfile=str(key))
    server.socket = context.wrap_socket(server.socket, server_side=True)
    thread = threading.Thread(target=server.serve_forever, kwargs={'poll_interval': 0.1}, daemon=True)
    thread.start()
    return server, thread


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):  # type: ignore[no-untyped-def]
        return None


def safe_transfer(value: object) -> dict:
    source = value if isinstance(value, dict) else {}
    result = {
        'name': source.get('name') if source.get('name') in {
            'php-net', 'getcomposer', 'local-fixture-positive', 'local-fixture-negative'} else 'unknown',
        'ok': source.get('ok') is True,
        'http_status': source.get('http_status') if isinstance(source.get('http_status'), int) else 0,
        'errno': source.get('errno') if isinstance(source.get('errno'), int) else 0,
        'ssl_verify_result': source.get('ssl_verify_result') if isinstance(source.get('ssl_verify_result'), int) else None,
        'duration_ms': source.get('duration_ms') if isinstance(source.get('duration_ms'), (int, float)) else 0,
        'failure': source.get('failure') if source.get('failure') in {
            None, 'tls-verification-failed', 'curl-transport-failed', 'curl-unavailable',
            'curl-init-failed', 'curl-options-rejected', 'curl-exception', 'laravel-transport-failed',
            'http-status-failed'} else 'invalid-result',
    }
    return result


def sanitize_payload(value: object) -> dict:
    source = value if isinstance(value, dict) else {}
    runtime = source.get('runtime') if isinstance(source.get('runtime'), dict) else {}
    fixture = source.get('fixture') if isinstance(source.get('fixture'), dict) else {}
    laravel = source.get('laravel_http') if isinstance(source.get('laravel_http'), dict) else {}
    return {
        'ok': source.get('ok') is True,
        'runtime': {**{
            key: runtime.get(key) for key in ['php_version', 'sapi', 'thread_safe', 'architecture', 'curl_loaded',
                                               'curl_init', 'curl_version', 'curl_tls_backend', 'openssl_loaded',
                                               'openssl_version']
            if isinstance(runtime.get(key), (str, bool))
        }, 'web_config_checks': {key: runtime.get('web_config_checks', {}).get(key) is True for key in WEB_CONFIG_KEYS
            if isinstance(runtime.get('web_config_checks'), dict)}},
        'direct_curl': safe_transfer(source.get('direct_curl')),
        'direct_curl_secondary': safe_transfer(source.get('direct_curl_secondary')),
        'laravel_http': {
            'name': laravel.get('name') if laravel.get('name') == 'php-net' else 'unknown',
            'ok': laravel.get('ok') is True,
            'http_status': laravel.get('http_status') if isinstance(laravel.get('http_status'), int) else 0,
            'duration_ms': laravel.get('duration_ms') if isinstance(laravel.get('duration_ms'), (int, float)) else 0,
            'handler': laravel.get('handler') if laravel.get('handler') == 'GuzzleHttp\\Handler\\CurlHandler' else 'unknown',
            'verification': laravel.get('verification') is True,
            'failure': laravel.get('failure') if laravel.get('failure') in {None, 'laravel-transport-failed', 'http-status-failed'} else 'invalid-result',
        },
        'fixture': {
            'configured': fixture.get('configured') is True,
            'ok': fixture.get('ok') is True,
            'positive': safe_transfer(fixture.get('positive')),
            'negative': safe_transfer(fixture.get('negative')),
            'negative_tls_verified': fixture.get('negative_tls_verified') is True,
        },
    }


def endpoint_probe(nonce: str, timeout: int) -> tuple[int, dict]:
    request = urllib.request.Request(ENDPOINT, headers={
        'Accept': 'application/json',
        'X-Gramlyze-M94-Probe': nonce,
    }, method='GET')
    opener = urllib.request.build_opener(urllib.request.ProxyHandler({}), NoRedirect())
    try:
        response = opener.open(request, timeout=timeout)
    except urllib.error.HTTPError as error:
        response = error
    except Exception as error:
        return 0, {'ok': False, 'transport_error': type(error).__name__}
    with response:
        body = response.read(64 * 1024 + 1)
        status = response.status
    if len(body) > 64 * 1024:
        return status, {'ok': False, 'response_error': 'response-size-limit'}
    try:
        return status, sanitize_payload(json.loads(body.decode('utf-8', errors='strict')))
    except (UnicodeError, ValueError, TypeError):
        return status, {'ok': False, 'response_error': 'invalid-json'}


def expected_success(status: int, value: dict) -> bool:
    fixture = value.get('fixture') if isinstance(value, dict) else {}
    return bool(
        status == 200
        and value.get('ok') is True
        and value.get('runtime', {}).get('php_version') == '8.5.10'
        and value.get('runtime', {}).get('sapi') == 'cgi-fcgi'
        and value.get('runtime', {}).get('thread_safe') is False
        and value.get('runtime', {}).get('architecture') == 'x64'
        and value.get('runtime', {}).get('curl_loaded') is True
        and value.get('runtime', {}).get('curl_init') is True
        and all(value.get('runtime', {}).get('web_config_checks', {}).get(key) is True for key in WEB_CONFIG_KEYS)
        and value.get('direct_curl', {}).get('ok') is True
        and value.get('direct_curl', {}).get('name') == 'php-net'
        and value.get('direct_curl_secondary', {}).get('ok') is True
        and value.get('direct_curl_secondary', {}).get('name') == 'getcomposer'
        and value.get('laravel_http', {}).get('ok') is True
        and value.get('laravel_http', {}).get('handler') == 'GuzzleHttp\\Handler\\CurlHandler'
        and value.get('laravel_http', {}).get('verification') is True
        and fixture.get('configured') is True
        and fixture.get('ok') is True
        and fixture.get('positive', {}).get('ok') is True
        and fixture.get('positive', {}).get('http_status') == 204
        and fixture.get('negative', {}).get('ok') is False
        and fixture.get('negative', {}).get('failure') == 'tls-verification-failed'
        and fixture.get('negative', {}).get('errno') == 60
        and fixture.get('negative_tls_verified') is True
    )


def remove_control_if_owned(nonce: str) -> bool:
    if not CONTROL.is_file() or CONTROL.is_symlink():
        return False
    try:
        value = json.loads(CONTROL.read_text(encoding='utf-8'))
    except (OSError, ValueError):
        return False
    if not safe_control(value) or value.get('nonce') != nonce:
        return False
    CONTROL.unlink()
    return True


def run(label: str, openssl: Path, timeout: int) -> tuple[bool, Path]:
    validate_label(label)
    if not isinstance(timeout, int) or not 1 <= timeout <= 60:
        raise ValueError('timeout must be between 1 and 60 seconds')
    OUT.mkdir(parents=True, exist_ok=True)
    if CONTROL.exists() or CONTROL.is_symlink():
        raise RuntimeError('existing-m9-4-control-file')
    report = OUT / f'{label}-curl-tls.json'
    if report.exists() or report.is_symlink():
        raise RuntimeError('evidence-label-already-exists')
    run_dir = OUT / f'{RUN_PREFIX}{uuid.uuid4().hex}'
    run_dir.mkdir(mode=0o700)
    nonce = secrets.token_hex(32)
    server = None
    thread = None
    value: dict = {'ok': False, 'failure': 'not-started'}
    status = 0
    started = stamp()
    cleanup = {'control_removed': False, 'fixture_removed': False}
    try:
        ca, certificate, key = create_fixture_certificates(openssl, run_dir)
        server, thread = start_fixture(certificate, key)
        port = server.server_address[1]
        positive, negative = fixture_urls(port)
        control = {'schema': SCHEMA, 'nonce': nonce, 'fixture': {
            'positive_url': positive, 'negative_url': negative, 'ca_path': str(ca.resolve()),
        }}
        if not safe_control(control):
            raise RuntimeError('unsafe-generated-control')
        write_json_exclusive(CONTROL, control)
        status, value = endpoint_probe(nonce, timeout)
        passed = expected_success(status, value)
    except Exception as error:
        value = {'ok': False, 'failure': type(error).__name__}
        passed = False
    finally:
        if server is not None:
            server.shutdown()
            server.server_close()
        if thread is not None:
            thread.join(timeout=5)
        cleanup['control_removed'] = remove_control_if_owned(nonce)
        owned = owned_run(run_dir)
        shutil.rmtree(owned)
        cleanup['fixture_removed'] = not owned.exists()
    evidence = {
        'schema': 'gramlyze-m9-4-curl-tls-evidence-v1',
        'started_at': started,
        'finished_at': stamp(),
        'endpoint': '/api/_local/m9-4-curl-tls',
        'endpoint_status': status,
        'result': value,
        'pass': passed,
        'cleanup': cleanup,
    }
    write_json_exclusive(report, evidence)
    return passed, report


def main() -> int:
    parser = argparse.ArgumentParser(description='Run a private local TLS fixture against the M9.4 web-SAPI probe.')
    parser.add_argument('--label', required=True)
    parser.add_argument('--openssl', required=True, type=Path)
    parser.add_argument('--timeout', type=int, default=60)
    args = parser.parse_args()
    try:
        passed, report = run(args.label, args.openssl, args.timeout)
    except Exception as error:
        print(json.dumps({'pass': False, 'error': type(error).__name__}))
        return 1
    print(json.dumps({'file': str(report.relative_to(ROOT)), 'pass': passed}))
    return 0 if passed else 1


if __name__ == '__main__':
    raise SystemExit(main())
