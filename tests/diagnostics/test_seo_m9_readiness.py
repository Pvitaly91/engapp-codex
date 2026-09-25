import importlib.util
from pathlib import Path
import unittest
from unittest.mock import patch

spec = importlib.util.spec_from_file_location('m9', Path(__file__).resolve().parents[2] / 'tools/diagnostics/seo-m9-http.py')
m9 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(m9)


class ReadinessTest(unittest.TestCase):
    def test_sitemap_uses_full_ordered_entries_not_count(self):
        a = [{'loc': 'https://gramlyze.com/a', 'lastmod': None}, {'loc': 'https://gramlyze.com/b', 'lastmod': None}]
        self.assertTrue(m9.compare_entries(a, a)['ordered_equal'])
        self.assertFalse(m9.compare_entries(a, list(reversed(a)))['ordered_equal'])
        changed = m9.compare_entries(a, [a[0], {'loc': 'https://gramlyze.com/c', 'lastmod': None}])
        self.assertEqual(changed['added'], ['https://gramlyze.com/c'])
        self.assertEqual(changed['removed'], ['https://gramlyze.com/b'])

    def test_course_mapping_requires_unique_real_local_navigation(self):
        link = 'http://gramlyze.loc/courses/english-grammar-theory/lesson/pronouns/one-ones'
        self.assertEqual(m9.course_mapping([link], 'one-ones'), link.removeprefix(m9.http.BASE))
        for links in [[], [link.replace('gramlyze.loc', 'gramlyze.com')], [link, link.replace('/pronouns/', '/other/')]]:
            with self.assertRaises(ValueError):
                m9.course_mapping(links, 'one-ones')

    def test_timeout_cannot_pass(self):
        with patch.object(m9.http, 'request_local', return_value=({'error': 'TimeoutError'}, b'')):
            row, _ = m9.capture('/theory')
            self.assertFalse(row['pass'])

    def test_empty_html_cannot_pass(self):
        with patch.object(m9.http, 'request_local', return_value=({'status': 200, 'content_type': 'text/html'}, b'<title>Empty</title>')):
            row, _ = m9.capture('/theory')
            self.assertFalse(row['pass'])

    def test_production_is_rejected_before_request(self):
        with self.assertRaises(ValueError):
            m9.capture('https://gramlyze.com/theory')

    def test_passive_voice_checks_the_actual_debug_signatures(self):
        path = '/theory/passive-voice/theory-passive-voice-formation-rules'
        html = ('<title>Passive Voice</title><meta name="description" content="Description">'
                '<link rel="canonical" href="https://gramlyze.com' + path + '">'
                '<main><h1>Passive Voice</h1><p>' + 'Learning content. ' * 30 + '</p>{}</main>'
                '<script src="/build/assets/catalog-public-test.js"></script>')
        headers = {'status': 200, 'content_type': 'text/html', 'x_robots_tag': 'noindex'}
        for marker in ['', 'Page Folder Unseed Targets Debug', 'Page_V3 folder unseed block.']:
            with patch.object(m9.http, 'request_local', return_value=(headers.copy(), html.format(marker).encode())):
                row, _ = m9.capture(path)
                self.assertEqual(bool(row['pass']), not marker)

    def test_header_only_development_robots_is_valid_but_conflicting_meta_is_not(self):
        row = {'path': '/theory', 'status': 200, 'content_type': 'text/html', 'assets': True,
               'metadata': {'title': ['Title'], 'description': ['Description'], 'h1': ['Heading'],
                            'canonical': ['https://gramlyze.com/theory'], 'robots': []},
               'x_robots_tag': 'noindex, nofollow, noarchive', 'content': {'main_characters': 500}}
        self.assertTrue(m9.reassess(row))
        row['metadata']['robots'] = ['index, follow']
        self.assertFalse(m9.reassess(row))
        row['error'] = 'TimeoutError'
        self.assertFalse(m9.reassess(row))


if __name__ == '__main__':
    unittest.main()
