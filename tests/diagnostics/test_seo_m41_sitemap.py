import importlib.util
import json
import tempfile
import unittest
from pathlib import Path
from unittest import mock

SPEC = importlib.util.spec_from_file_location('m41', Path(__file__).resolve().parents[2] / 'tools/diagnostics/seo-m4-1-sitemap.py')
TOOL = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(TOOL)
XML = b'<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>https://gramlyze.com/</loc></url></urlset>'


class SitemapSamplesTest(unittest.TestCase):
    def setUp(self):
        self.directory = tempfile.TemporaryDirectory()
        self.addCleanup(self.directory.cleanup)
        self.patch = mock.patch.object(TOOL, 'OUTPUT', Path(self.directory.name))
        self.patch.start()
        self.addCleanup(self.patch.stop)

    def response(self):
        return {'status': 200, 'content_type': 'application/xml', 'site_mode': 'development', 'x_robots_tag': 'noindex'}, XML

    @mock.patch.object(TOOL.http, 'request_local')
    def test_all_samples_are_real_new_calls_and_exact_order_is_compared(self, get):
        get.side_effect = lambda *args: self.response()
        before = TOOL.run('before', 3)
        after = TOOL.run('after', 5, compare='before.json')
        pair = TOOL.run('pair', 2, workers=2, compare='before.json')
        self.assertTrue(before['pass'] and after['pass'] and pair['pass'])
        self.assertEqual(10, get.call_count)
        self.assertEqual(10, len(list(TOOL.OUTPUT.glob('*.xml'))))
        for call in get.call_args_list:
            self.assertEqual(('/sitemap.xml', 'application/xml'), call.args)

    @mock.patch.object(TOOL.http, 'request_local')
    def test_failure_retained_without_retry_or_empty_success(self, get):
        get.return_value = ({'error': 'TimeoutError'}, b'')
        report = TOOL.run('failed', 1)
        self.assertFalse(report['pass'])
        self.assertEqual(1, get.call_count)
        self.assertEqual('TimeoutError', json.loads((TOOL.OUTPUT / 'failed.json').read_text())['samples'][0]['error'])

    @mock.patch.object(TOOL.http, 'request_local')
    def test_evidence_is_not_overwritten_and_concurrency_is_bounded(self, get):
        get.side_effect = lambda *args: self.response()
        TOOL.run('once', 1)
        with self.assertRaises(FileExistsError): TOOL.run('once', 1)
        with self.assertRaises(FileExistsError): TOOL.capture(1, 'once')
        for count, workers in [(4, 1), (5, 2), (3, 3)]:
            with self.assertRaises(ValueError): TOOL.run('unsafe', count, workers)
        with self.assertRaises(ValueError): TOOL.run('../unsafe', 1)
        with self.assertRaises(ValueError): TOOL.run('bad-reference', 1, compare='../before.json')

    @mock.patch.object(TOOL.http, 'request_local')
    def test_equal_count_with_different_url_is_not_equivalence(self, get):
        get.side_effect = lambda *args: self.response()
        TOOL.run('before', 1)
        get.side_effect = None
        get.return_value = (self.response()[0], XML.replace(b'gramlyze.com/', b'gramlyze.com/theory'))
        self.assertFalse(TOOL.run('changed', 1, compare='before.json')['pass'])

    @mock.patch.object(TOOL.http, 'request_local')
    def test_representatives_discover_local_course_copy_and_expect_theory_canonical(self, get):
        def response(path, accept, **kwargs):
            row = self.response()[0]
            row['path'] = path
            if accept == 'application/json':
                row['content_type'] = 'application/json'
                return row, b'{"questions":[{}]}'
            row['content_type'] = 'text/html'
            canonical = path.replace('/courses/english-grammar-theory/lesson/', '/theory/')
            return row, ('<html><head><link rel="canonical" href="https://gramlyze.com' + canonical + '"></head>'
                         '<main><h1>Lesson</h1><p>' + 'educational content ' * 20 + '</p>'
                         '<a href="http://gramlyze.loc/courses/english-grammar-theory/lesson/topic/forms">Start</a>'
                         '</main></html>').encode()
        get.side_effect = response
        result = TOOL.representative_acceptance('representatives')
        self.assertTrue(result['pass'])
        self.assertEqual(9, get.call_count)
        self.assertTrue(all(call.args[0].startswith('/') for call in get.call_args_list))
        self.assertEqual('/test/future-perfect/questions/questions', get.call_args_list[-1].args[0])


if __name__ == '__main__':
    unittest.main()
