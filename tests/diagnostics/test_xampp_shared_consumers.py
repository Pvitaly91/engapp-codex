"""Ownership/cleanup and transport regressions; never touches a live site."""
import importlib.util
import hashlib
import json
from pathlib import Path
import tempfile
import unittest
from unittest.mock import MagicMock, patch


PATH = Path(__file__).resolve().parents[2] / 'tools/diagnostics/xampp-startup/check-shared-consumers.py'
SPEC = importlib.util.spec_from_file_location('shared_consumers', PATH)
CHECK = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(CHECK)


class SharedChecks(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix='m94-shared-test-')
        self.addCleanup(self.temp.cleanup)
        self.directory = Path(self.temp.name) / 'web'
        self.directory.mkdir()
        self.out = Path(self.temp.name) / 'evidence'
        for name, value in (
            ('ROOTS', {'fixture': self.directory}), ('OUT', self.out),
            ('PROBES', (('http', 'fixture.localhost', '/', 'fixture'),)),
            ('APPLICATION_GETS', ()),
        ):
            monkey = patch.object(CHECK, name, value)
            monkey.start()
            self.addCleanup(monkey.stop)

    def response(self, *args, **kwargs):
        self.assertEqual(len(list(self.directory.glob('*.php'))), 1)
        self.assertIn('X-M94-Probe', args[3])
        return {'scheme': args[0], 'host': args[1], 'status': 200, 'source_exposed': False,
                'runtime': {'sapi': 'apache2handler', 'php': '8.5.10',
                            'openssl_loaded': True, 'mysqli_loaded': True, 'pdo_mysql_loaded': True}}

    def test_probe_executes_and_is_removed(self):
        with patch.object(CHECK, 'request', self.response):
            result = CHECK.collect('before')
        self.assertTrue(result['http_php_handlers_pass'])
        self.assertTrue(result['cleanup_pass'])
        self.assertEqual(list(self.directory.iterdir()), [])
        self.assertTrue((self.out / 'before-shared-consumers.json').is_file())

    def test_exception_still_removes_own_probe(self):
        with patch.object(CHECK, 'request', side_effect=RuntimeError('failure')):
            with self.assertRaises(RuntimeError):
                CHECK.collect('failure')
        self.assertEqual(list(self.directory.iterdir()), [])
        result = json.loads((self.out / 'failure-shared-consumers.json').read_text())
        self.assertTrue(result['cleanup_pass'])

    def test_modified_file_is_preserved(self):
        def modified(*args, **kwargs):
            result = self.response(*args, **kwargs)
            next(self.directory.glob('*.php')).write_text('foreign modification')
            return result
        with patch.object(CHECK, 'request', modified):
            result = CHECK.collect('modified')
        self.assertFalse(result['cleanup_pass'])
        self.assertFalse(result['cleanup'][0]['content_owned'])
        self.assertEqual(next(self.directory.iterdir()).read_text(), 'foreign modification')

    def test_collision_never_overwrites_or_deletes_existing_file(self):
        existing = self.directory / ('m94-shared-' + 'a' * 32 + '.php')
        existing.write_text('existing file')
        with patch.object(CHECK.secrets, 'token_hex', side_effect=lambda count: 'a' * (count * 2)):
            with self.assertRaises(FileExistsError):
                CHECK.collect('collision')
        self.assertEqual(existing.read_text(), 'existing file')

    def test_http_uses_loopback_and_discards_app_body(self):
        connection = MagicMock()
        response = connection.getresponse.return_value
        response.status = 302
        response.getheader.return_value = 'text/html; charset=utf-8'
        with patch.object(CHECK.http.client, 'HTTPConnection', return_value=connection) as factory:
            result = CHECK.request('http', 'fixture.localhost', '/')
        factory.assert_called_once_with('127.0.0.1', 80, timeout=40)
        response.read.assert_not_called()
        self.assertEqual(connection.request.call_count, 1)
        self.assertTrue(result['redirect'])
        self.assertNotIn('body', result)
        self.assertEqual(connection.request.call_args.kwargs['headers']['Host'], 'fixture.localhost')

    def test_comparison_detects_a_lost_existing_extension(self):
        with patch.object(CHECK, 'request', self.response):
            CHECK.collect('before')
        def missing_extension(*args, **kwargs):
            result = self.response(*args, **kwargs)
            result['runtime']['pdo_mysql_loaded'] = False
            return result
        with patch.object(CHECK, 'request', missing_extension):
            result = CHECK.collect('after', self.out / 'before-shared-consumers.json')
        self.assertTrue(result['http_php_handlers_pass'])
        self.assertFalse(result['existing_php_runtime_unchanged'])
        self.assertTrue(result['cleanup_pass'])

    def test_unexpected_probe_fields_are_not_retained(self):
        connection = MagicMock()
        response = connection.getresponse.return_value
        response.status = 200
        response.getheader.return_value = 'application/json'
        response.read.return_value = b'{"probe":"m94-shared-runtime-v1","private":"not retained"}'
        with patch.object(CHECK.http.client, 'HTTPConnection', return_value=connection):
            result = CHECK.request('http', 'fixture.localhost', '/probe.php', probe=True)
        self.assertTrue(result['invalid_probe'])
        self.assertNotIn('runtime', result)
        self.assertNotIn('not retained', json.dumps(result))

    def test_tls_rejection_has_no_http_or_insecure_fallback(self):
        context = MagicMock()
        connection = MagicMock()
        failure = CHECK.ssl.SSLCertVerificationError('rejected')
        failure.verify_code = 66
        failure.verify_message = 'EE certificate key too weak'
        connection.request.side_effect = failure
        with patch.object(CHECK.ssl, 'create_default_context', return_value=context) as secure:
            with patch.object(CHECK, 'LoopbackTLS', return_value=connection) as tls:
                with patch.object(CHECK.http.client, 'HTTPConnection') as http:
                    result = CHECK.request('https', 'localhost', '/')
        secure.assert_called_once()
        tls.assert_called_once_with('localhost', timeout=40, context=context)
        http.assert_not_called()
        self.assertEqual(result['error'], 'tls-verification')
        self.assertEqual(result['verify_code'], 66)

    def certificate_fixture(self):
        xampp = Path(self.temp.name) / 'xampp'
        path = xampp / 'apache/conf/ssl.crt/server.crt'
        path.parent.mkdir(parents=True)
        pem = b'-----BEGIN CERTIFICATE-----\nZml4dHVyZQ==\n-----END CERTIFICATE-----\n'
        path.write_bytes(pem)
        return xampp, pem

    def test_wrong_certificate_pin_aborts_before_creating_any_probe(self):
        xampp, _ = self.certificate_fixture()
        with patch.object(CHECK, 'XAMPP', xampp):
            with patch.object(CHECK, 'request') as request:
                with self.assertRaisesRegex(ValueError, 'does not match'):
                    CHECK.collect('wrong-pin', certificate_sha256='0' * 64)
        request.assert_not_called()
        self.assertEqual(list(self.directory.iterdir()), [])
        self.assertFalse(self.out.exists())

    def test_certificate_snapshot_pins_file_and_peer_der(self):
        xampp, pem = self.certificate_fixture()
        digest = hashlib.sha256(pem).hexdigest()
        with patch.object(CHECK, 'XAMPP', xampp):
            snapshot = CHECK.certificate_material(digest.upper())
        self.assertEqual(snapshot['pem'], pem.decode())
        self.assertEqual(snapshot['file_sha256'], digest)
        self.assertEqual(snapshot['der_sha256'], hashlib.sha256(b'fixture').hexdigest())

    def test_peer_certificate_mismatch_closes_socket(self):
        connection = CHECK.LoopbackTLS('localhost', context=CHECK.ssl.create_default_context(),
                                       expected_certificate_sha256='0' * 64)
        context = MagicMock()
        wrapped = context.wrap_socket.return_value
        wrapped.getpeercert.return_value = b'different certificate'
        connection._context = context
        raw = MagicMock()
        with patch.object(CHECK.socket, 'create_connection', return_value=raw) as connect:
            with self.assertRaisesRegex(CHECK.ssl.SSLError, 'explicit pin'):
                connection.connect()
        self.assertEqual(connect.call_args.args[0], ('127.0.0.1', 443))
        context.wrap_socket.assert_called_once_with(raw, server_hostname='localhost')
        wrapped.close.assert_called_once()
        raw.close.assert_called_once()

    def test_pin_uses_default_verified_context_and_exact_peer_hash(self):
        certificate = {'pem': 'snapshot certificate', 'der_sha256': 'a' * 64}
        connection = MagicMock()
        response = connection.getresponse.return_value
        response.status = 200
        response.getheader.return_value = 'text/plain'
        secure_context = CHECK.ssl.create_default_context()
        with patch.object(CHECK.ssl, 'create_default_context', return_value=secure_context) as secure:
            with patch.object(CHECK, 'LoopbackTLS', return_value=connection) as tls:
                result = CHECK.request('https', 'localhost', '/', certificate=certificate)
        secure.assert_called_once_with(cadata=certificate['pem'])
        tls.assert_called_once_with('localhost', timeout=40, context=secure_context,
                                     expected_certificate_sha256=certificate['der_sha256'])
        self.assertTrue(secure_context.check_hostname)
        self.assertEqual(secure_context.verify_mode, CHECK.ssl.CERT_REQUIRED)
        self.assertTrue(result['tls_verified'])
        self.assertTrue(result['certificate_pin_verified'])

    def test_explicit_rotation_accepts_three_verified_https_consumers(self):
        plan = (('http', 'lara.loc', '/', 'fixture'),
                ('https', 'localhost', '/', 'fixture'),
                ('https', 'localhost', '/phpmyadmin/', 'fixture'),
                ('https', 'localhost', '/webalizer/', 'fixture'))
        def before(*args, **kwargs):
            if args[0] == 'https':
                return {'scheme': 'https', 'host': args[1],
                        'error': 'tls-verification', 'verify_code': 66}
            return self.response(*args, **kwargs)
        def after(*args, **kwargs):
            result = self.response(*args, **kwargs)
            result['runtime']['curl_loaded'] = True
            if args[0] == 'https':
                self.assertIsNotNone(kwargs['certificate'])
                result.update(tls_verified=True, certificate_pin_verified=True)
            else:
                result['runtime']['https'] = {'success': True, 'status': 200,
                                             'errno': 0, 'ssl_verify_result': 0}
            return result
        with patch.object(CHECK, 'PROBES', plan):
            with patch.object(CHECK, 'request', before):
                CHECK.collect('before')
            certificate = {'pem': 'private snapshot excluded', 'der_sha256': 'b' * 64,
                           'file_sha256': 'a' * 64}
            with patch.object(CHECK, 'certificate_material', return_value=certificate):
                with patch.object(CHECK, 'request', after):
                    result = CHECK.collect('after', self.out / 'before-shared-consumers.json', 'a' * 64)
        self.assertTrue(result['pinned_https_php_handlers_pass'])
        self.assertTrue(result['existing_php_runtime_unchanged'])
        self.assertTrue(result['https_curl_pass'])
        self.assertFalse(result['default_https_verification_unchanged'])
        self.assertEqual(result['explicit_certificate_sha256'], 'a' * 64)
        self.assertNotIn(certificate['pem'], json.dumps(result))


if __name__ == '__main__':
    unittest.main()
