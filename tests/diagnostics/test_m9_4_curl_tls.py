import importlib.util
import json
import os
from pathlib import Path
import re
import tempfile
import unittest
from unittest.mock import patch


ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('m94_curl_tls', ROOT / 'tools/diagnostics/m9-4-curl-tls.py')
m94 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(m94)


def valid_payload():
    return {
        'ok': True,
        'runtime': {'php_version': '8.5.10', 'sapi': 'cgi-fcgi', 'thread_safe': False,
                    'architecture': 'x64', 'curl_loaded': True, 'curl_init': True,
                    'web_config_checks': dict.fromkeys(m94.WEB_CONFIG_KEYS, True)},
        'direct_curl': {'name': 'php-net', 'ok': True, 'http_status': 200, 'errno': 0, 'duration_ms': 1, 'failure': None},
        'direct_curl_secondary': {'name': 'getcomposer', 'ok': True, 'http_status': 200, 'errno': 0, 'duration_ms': 1, 'failure': None},
        'laravel_http': {'name': 'php-net', 'ok': True, 'http_status': 200, 'duration_ms': 1,
                         'handler': 'GuzzleHttp\\Handler\\CurlHandler', 'verification': True, 'failure': None},
        'fixture': {
            'configured': True,
            'ok': True,
            'positive': {'name': 'local-fixture-positive', 'ok': True, 'http_status': 204, 'errno': 0, 'duration_ms': 1, 'failure': None},
            'negative': {'name': 'local-fixture-negative', 'ok': False, 'http_status': 0, 'errno': 60, 'duration_ms': 1, 'failure': 'tls-verification-failed'},
            'negative_tls_verified': True,
        },
    }


class CurlTlsFixturePolicyTest(unittest.TestCase):
    def test_label_and_fixture_port_are_strict(self):
        self.assertEqual(m94.validate_label('m9-4-curl'), 'm9-4-curl')
        for label in ['', 'UPPER', '../escape', 'm9_4', 'm9 4']:
            with self.subTest(label=label):
                with self.assertRaises(ValueError):
                    m94.validate_label(label)
        self.assertEqual(m94.fixture_urls(9443), ('https://localhost:9443/ok', 'https://127.0.0.1:9443/ok'))
        for port in [0, 443, 65536, '9443']:
            with self.subTest(port=port):
                with self.assertRaises(ValueError):
                    m94.fixture_urls(port)

    def test_owned_fixture_directory_cannot_escape_private_output(self):
        with tempfile.TemporaryDirectory() as directory, patch.object(m94, 'OUT', Path(directory)):
            good = Path(directory) / (m94.RUN_PREFIX + 'a' * 32)
            self.assertEqual(m94.owned_run(good), good.resolve())
            for bad in [Path(directory).parent / (m94.RUN_PREFIX + 'a' * 32),
                        Path(directory) / 'not-owned', Path(directory) / (m94.RUN_PREFIX + 'z' * 32)]:
                with self.subTest(path=bad):
                    with self.assertRaises(ValueError):
                        m94.owned_run(bad)

    def test_control_requires_schema_nonce_and_fixture_strings(self):
        control = {'schema': m94.SCHEMA, 'nonce': 'a' * 64, 'fixture': {
            'positive_url': 'https://localhost:9443/ok', 'negative_url': 'https://127.0.0.1:9443/ok', 'ca_path': 'C:/private/ca.pem'}}
        self.assertTrue(m94.safe_control(control))
        for mutation in [lambda c: c.update(schema='wrong'), lambda c: c.update(nonce='short'),
                         lambda c: c['fixture'].pop('ca_path')]:
            value = json.loads(json.dumps(control))
            mutation(value)
            with self.subTest(value=value):
                self.assertFalse(m94.safe_control(value))

    def test_sanitizer_drops_urls_bodies_paths_and_secret_like_fields(self):
        raw = valid_payload()
        raw['runtime'] = {'php_version': '8.5.10', 'sapi': 'cgi-fcgi', 'app_key': 'secret', 'php_ini': 'C:/private/php.ini'}
        raw['direct_curl'].update(url='https://private.invalid/', body='sensitive payload', error='raw error')
        raw['fixture']['ca_path'] = 'C:/private/ca.pem'
        result = m94.sanitize_payload(raw)
        encoded = json.dumps(result)
        self.assertNotIn('secret', encoded)
        self.assertNotIn('private.invalid', encoded)
        self.assertNotIn('sensitive payload', encoded)
        self.assertNotIn('C:/private', encoded)
        self.assertEqual(result['laravel_http']['handler'], 'GuzzleHttp\\Handler\\CurlHandler')

    def test_success_requires_actual_curl_handler_and_certificate_rejection(self):
        value = valid_payload()
        self.assertTrue(m94.expected_success(200, value))
        mutations = [
            lambda v: v['runtime'].update(sapi='apache2handler'),
            lambda v: v['runtime']['web_config_checks'].update(own_ini=False),
            lambda v: v['runtime']['web_config_checks'].update(curl_ca_file=False),
            lambda v: v['direct_curl_secondary'].update(ok=False),
            lambda v: v['direct_curl_secondary'].update(name='unknown'),
            lambda v: v['laravel_http'].update(handler='unknown'),
            lambda v: v['laravel_http'].update(verification=False),
            lambda v: v['fixture']['negative'].update(errno=7, failure='curl-transport-failed'),
            lambda v: v['fixture']['negative'].update(ok=True),
            lambda v: v['fixture'].update(negative_tls_verified=False),
        ]
        for mutation in mutations:
            candidate = json.loads(json.dumps(value))
            mutation(candidate)
            with self.subTest(candidate=candidate):
                self.assertFalse(m94.expected_success(200, candidate))
        self.assertFalse(m94.expected_success(503, value))

    def test_control_cleanup_only_removes_matching_owned_nonce(self):
        with tempfile.TemporaryDirectory() as directory:
            out = Path(directory)
            control_path = out / 'curl-tls-control.json'
            control_path.write_text(json.dumps({'schema': m94.SCHEMA, 'nonce': 'b' * 64, 'fixture': {
                'positive_url': 'https://localhost:9443/ok', 'negative_url': 'https://127.0.0.1:9443/ok', 'ca_path': 'C:/private/ca.pem'}}), encoding='utf-8')
            with patch.object(m94, 'CONTROL', control_path):
                self.assertFalse(m94.remove_control_if_owned('a' * 64))
                self.assertTrue(control_path.exists())
                self.assertTrue(m94.remove_control_if_owned('b' * 64))
                self.assertFalse(control_path.exists())

    def test_endpoint_and_server_binding_are_fixed_local_values(self):
        self.assertEqual(m94.ENDPOINT, 'http://gramlyze.loc/api/_local/m9-4-curl-tls')
        source = (ROOT / 'tools/diagnostics/m9-4-curl-tls.py').read_text(encoding='utf-8')
        self.assertIn("ThreadingHTTPServer(('127.0.0.1', 0), FixtureHandler)", source)
        self.assertNotIn("parser.add_argument('--endpoint'", source)
        self.assertNotIn("verify=False", source)
        self.assertIsNone(re.search(r"['\"]-k['\"]", source))

    def test_openssl_configuration_is_verified_and_child_scoped(self):
        with tempfile.TemporaryDirectory() as directory:
            apache = Path(directory) / 'apache'
            executable = apache / 'bin' / 'openssl.exe'
            configuration = apache / 'conf' / 'openssl.cnf'
            executable.parent.mkdir(parents=True)
            configuration.parent.mkdir(parents=True)
            executable.write_text('fixture', encoding='ascii')
            configuration.write_text('[openssl_init]\n', encoding='ascii')
            with patch.dict(os.environ, {'OPENSSL_CONF': 'unchanged-parent-value'}, clear=False):
                environment = m94.openssl_subprocess_environment(executable)
                self.assertEqual(environment['OPENSSL_CONF'], str(configuration.resolve()))
                self.assertEqual(os.environ['OPENSSL_CONF'], 'unchanged-parent-value')
            configuration.unlink()
            with self.assertRaisesRegex(RuntimeError, 'openssl-config-not-found'):
                m94.openssl_subprocess_environment(executable)


if __name__ == '__main__':
    unittest.main()
