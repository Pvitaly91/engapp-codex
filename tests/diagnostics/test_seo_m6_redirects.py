import importlib.util
import unittest
from pathlib import Path
from unittest.mock import patch

spec = importlib.util.spec_from_file_location('m6', Path(__file__).resolve().parents[2] / 'tools/diagnostics/seo-m6-redirects.py')
m6 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(m6)


class LocalRedirectEvidenceTest(unittest.TestCase):
    def test_origin_guard_rejects_external_and_credentialed_urls(self):
        for value in ['https://gramlyze.com/x', 'https://gramlyze.ub/x', '//attacker.invalid',
                      'http://user:pass@gramlyze.loc/x', 'http://gramlyze.loc.attacker.invalid',
                      '/%5C%5Cattacker.invalid']:
            with self.subTest(value=value), self.assertRaises(ValueError):
                m6.local(value)
        self.assertEqual('http://gramlyze.loc/tests/cards?tags%5B0%5D=1', m6.local('/tests/cards?tags%5B0%5D=1#anchor'))

    def test_chain_retains_only_its_jar_and_final_target_gets_an_independent_jar(self):
        clients = []

        def response(url, client, accept):
            clients.append(client)
            return {'url': url, 'status': 302, 'location': '/target'} if url.endswith('/old') else {'url': url, 'status': 200}

        with patch.object(m6, 'get', side_effect=response):
            result = m6.chain('/old')
        self.assertEqual([302, 200], [row['status'] for row in result['steps']])
        self.assertIs(clients[0], clients[1])
        self.assertIsNot(clients[1], clients[2])
        self.assertEqual(200, result['fresh_final']['status'])

    def test_external_destination_stops_before_second_request(self):
        with patch.object(m6, 'get', return_value={'status': 302, 'location': 'https://gramlyze.com/target'}) as get:
            result = m6.chain('/old')
        self.assertEqual(1, get.call_count)
        self.assertEqual('nonlocal-or-unsafe-url', result['error'])

    def test_loop_and_limit_are_reported_without_retry(self):
        with patch.object(m6, 'get', return_value={'status': 302, 'location': '/old'}) as get:
            self.assertEqual('redirect-loop', m6.chain('/old')['error'])
            self.assertEqual(1, get.call_count)
        with patch.object(m6, 'get', side_effect=[{'status': 302, 'location': '/next'+str(i)} for i in range(5)]) as get:
            self.assertEqual('five-step-limit', m6.chain('/old')['error'])
            self.assertEqual(5, get.call_count)


if __name__ == '__main__':
    unittest.main()
