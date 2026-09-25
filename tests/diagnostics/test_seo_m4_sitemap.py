import importlib.util
import json
import unittest
from pathlib import Path
from unittest import mock

SPEC = importlib.util.spec_from_file_location('seo_m4_sitemap', Path(__file__).resolve().parents[2] / 'tools/diagnostics/seo-m4-sitemap.py')
TOOL = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(TOOL)


def xml(entries=None):
    entries = entries if entries is not None else [('https://gramlyze.com/theory/basic-grammar', '2026-09-08T12:00:00+03:00'),
                                                  ('https://gramlyze.com/test/future-perfect/questions', None)]
    parts = ['<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="' + TOOL.NS + '">']
    for loc, lastmod in entries:
        parts.append('<url><loc>' + loc + '</loc>' + ('<lastmod>' + lastmod + '</lastmod>' if lastmod else '') + '</url>')
    return (''.join(parts) + '</urlset>').encode()


class FakeResponse:
    def __init__(self, body, status=200, content_type='text/html; charset=UTF-8', location=None):
        self.body = body
        self.code = status
        self.headers = {'Content-Type': content_type, 'X-Site-Mode': 'development', 'X-Robots-Tag': 'noindex, nofollow'}
        if location:
            self.headers['Location'] = location

    def read(self, limit):
        return self.body[:limit]

    def __enter__(self):
        return self

    def __exit__(self, *args):
        pass


def html(path='/test/future-perfect/questions', questions=True):
    return ('<!doctype html><html><head><meta name="csrf-token" content="PRIVATE-CSRF">'
            '<link rel="canonical" href="https://gramlyze.com' + path + '"><meta name="robots" content="noindex,nofollow">'
            '</head><body><main><h1>Lesson</h1><p>' + ('Public teaching material. ' * 20) + '</p>'
            '<a href="/theory/basic-grammar/sentence-types">Lesson link</a></main>'
            '<script>window.JS_TEST_PERSISTENCE={token:"PRIVATE-CSRF"};</script>'
            + ('<script>window.__INITIAL_JS_TEST_QUESTIONS__ = [{"uuid":"PRIVATE-ID","answers":["PRIVATE-ANSWER"]}];</script>' if questions else '')
            + '</body></html>').encode()


class SitemapTests(unittest.TestCase):
    def test_valid_namespace_questions_and_optional_lastmod(self):
        rows = TOOL.parse_sitemap(xml())
        self.assertEqual(len(rows), 2)
        self.assertTrue(rows[1]['loc'].endswith('/questions'))
        self.assertIsNone(rows[1]['lastmod'])
        self.assertEqual(TOOL.parse_sitemap(xml([('https://gramlyze.com/theory/%D1%82%D0%B5%D0%BC%D0%B0', '2026-09-08')] ))[0]['lastmod'], '2026-09-08')

    def test_xml_bom_debug_bad_namespace_entities_duplicates_fail(self):
        for body in [b'\xef\xbb\xbf' + xml(), b'warning' + xml(), xml().replace(TOOL.NS.encode(), b'https://wrong.example'),
                     b'<?xml version="1.0"?><!DOCTYPE urlset [<!ENTITY x SYSTEM "file:///secret">]><urlset/>',
                     xml([('https://gramlyze.com/', None)] * 2), xml([]), xml() + b'\xff',
                     xml().replace(b'UTF-8', b'UTF-16'), xml().replace(b' encoding="UTF-8"', b'')]:
            with self.subTest(body=body[:30]), self.assertRaises(Exception):
                TOOL.parse_sitemap(body)

    def test_url_encoding_origin_queries_and_invalid_dates_fail(self):
        for loc in ['http://gramlyze.loc/theory', 'https://gramlyze.com/theory?q=1', 'https://gramlyze.com/theory#x',
                    'https://private:secret@gramlyze.com/theory', 'https://gramlyze.com/теорія', 'https://gramlyze.com/theory/%oops',
                    'https://gramlyze.com/theory?', 'https://gramlyze.com/theory#', 'https://gramlyze.com/%2e%2e/.env']:
            with self.subTest(loc=loc), self.assertRaises(ValueError):
                TOOL.parse_sitemap(xml([(loc, None)]))
        for date in ['now', '2026-13-01', '20260908', '2026-09-08T12:00:00']:
            with self.subTest(date=date), self.assertRaises(ValueError):
                TOOL.parse_sitemap(xml([('https://gramlyze.com/', date)]))

    def test_only_local_paths_and_no_redirect_handler(self):
        self.assertEqual(TOOL.local_url('/test/future-perfect/questions'), TOOL.BASE + '/test/future-perfect/questions')
        for path in ['https://gramlyze.com/', '//gramlyze.com/', '/x?secret=1', '/x#y', '/%2e%2e/.env', '/%5c%5cevil', '/x%0aHost:evil']:
            with self.subTest(path=path), self.assertRaises(ValueError):
                TOOL.local_url(path)
        self.assertIsNone(TOOL.NoRedirect().redirect_request(None, None, 302, None, {}, 'https://gramlyze.com/'))

    def test_fresh_get_has_only_safe_headers_and_no_follow_or_retry(self):
        opener = mock.Mock()
        opener.open.return_value = FakeResponse(b'not followed', 302, location='https://gramlyze.com/login?csrf=PRIVATE#secret')
        row, body = TOOL.request_local('/courses', 'text/html', opener=opener)
        self.assertEqual(opener.open.call_count, 1)
        request = opener.open.call_args.args[0]
        self.assertEqual(request.full_url, TOOL.BASE + '/courses')
        self.assertEqual(request.get_method(), 'GET')
        self.assertEqual(request.get_header('Accept'), 'text/html')
        for forbidden in ('Cookie', 'Authorization', 'Referer'):
            self.assertIsNone(request.get_header(forbidden))
        self.assertEqual(row['status'], 302)
        self.assertEqual(row['location'], 'https://gramlyze.com/login')
        self.assertNotIn('PRIVATE', json.dumps(row))
        self.assertEqual(body, b'not followed')

    def test_failed_request_is_retained_once_without_private_exception_text(self):
        opener = mock.Mock()
        opener.open.side_effect = RuntimeError('PRIVATE-CSRF and PRIVATE-ANSWER')
        row, body = TOOL.request_local('/courses', 'text/html', opener=opener)
        self.assertEqual(opener.open.call_count, 1)
        self.assertEqual(row['error'], 'RuntimeError')
        self.assertEqual(body, b'')
        self.assertNotIn('PRIVATE', json.dumps(row))

    def test_html_parser_exposes_counts_not_script_answers_tokens_or_full_html(self):
        parser = TOOL.HtmlEvidence()
        parser.feed(html().decode())
        evidence = parser.evidence()
        self.assertEqual(evidence['question_count'], 1)
        self.assertEqual(evidence['h1_count'], 1)
        self.assertGreater(evidence['main_characters'], 100)
        self.assertNotIn('PRIVATE', json.dumps(evidence))
        self.assertNotIn('Public teaching', json.dumps(evidence))
        self.assertNotIn('answers', json.dumps(evidence))

    def test_html_acceptance_checks_status_canonical_material_and_question_bank(self):
        path = '/test/future-perfect/questions'
        good_row = {'status': 200, 'content_type': 'text/html', 'site_mode': 'development', 'x_robots_tag': 'noindex'}
        for body, expected in [(html(), True), (html(questions=False), False), (html('/test/alias'), False),
                               (html().replace(b'<h1>Lesson</h1>', b'<h1>Coming Soon</h1>'), False)]:
            with mock.patch.object(TOOL, 'request_local', return_value=(dict(good_row), body)):
                result = TOOL.inspect_html(path)
            self.assertEqual(result['pass'], expected)
            self.assertNotIn('PRIVATE', json.dumps(result))
        with mock.patch.object(TOOL, 'request_local', return_value=({**good_row, 'status': 302}, html())):
            self.assertFalse(TOOL.inspect_html(path)['pass'])

    def test_before_after_retention_additions_and_lastmod_changes_are_explicit(self):
        before = TOOL.parse_sitemap(xml())
        after = [dict(before[0], lastmod=None), before[1], {'loc': TOOL.SEO_ORIGIN + '/courses', 'lastmod': None}]
        original = json.loads(json.dumps(before))
        result = TOOL.compare_entries(before, after)
        self.assertEqual(result['retained_count'], 2)
        self.assertEqual(result['removed'], [])
        self.assertEqual(result['added_by_group'], {'course-catalog': 1})
        self.assertEqual(len(result['lastmod_changes']), 1)
        self.assertEqual(before, original)
        self.assertEqual(len(TOOL.compare_entries(before, after[1:])['removed']), 1)

    def test_parallelism_above_two_is_rejected_before_any_http(self):
        with mock.patch.object(TOOL, 'capture_sitemap') as capture:
            with self.assertRaises(ValueError):
                TOOL.run_comparison({}, {}, lambda: None, 3)
            capture.assert_not_called()

    def test_every_new_path_is_attempted_once_and_first_failure_is_saved(self):
        baseline = {'schema': 'gramlyze-m4-sitemap-baseline-v1', 'passed': True,
                    'sitemap': {'entries': [{'loc': TOOL.SEO_ORIGIN + '/', 'lastmod': None}]}}
        paths = ['/', '/test/future-perfect/questions', '/test/future-perfect/forms', '/courses']
        entries = [{'loc': TOOL.SEO_ORIGIN + path, 'lastmod': None} for path in paths]
        report, saved = {}, []

        def inspect(path):
            success = path != paths[1]
            return {'path': path, 'status': 200 if success else 500, 'pass': success, 'errors': [] if success else ['guest-document-not-200']}

        with mock.patch.object(TOOL, 'capture_sitemap', return_value={'pass': True, 'entries': entries}) as capture, \
                mock.patch.object(TOOL, 'request_local', return_value=({'status': 200}, b'')), \
                mock.patch.object(TOOL.http.cookiejar, 'CookieJar', return_value=[object()]), \
                mock.patch.object(TOOL.urllib.request, 'build_opener'), \
                mock.patch.object(TOOL, 'inspect_html', side_effect=inspect) as crawl, mock.patch('builtins.print'), \
                mock.patch.object(TOOL.concurrent.futures, 'ThreadPoolExecutor', wraps=TOOL.concurrent.futures.ThreadPoolExecutor) as pool:
            success = TOOL.run_comparison(report, baseline, lambda: saved.append(json.loads(json.dumps(report))), 2)
        self.assertFalse(success)
        pool.assert_called_once_with(max_workers=2)
        self.assertCountEqual([call.args[0] for call in crawl.call_args_list], paths)
        self.assertEqual(crawl.call_count, len(paths))
        self.assertEqual(capture.call_count, 3)
        self.assertEqual(report['session_cookie_count'], 1)
        self.assertEqual(report['determinism'], {'fresh_exact_loc_lastmod_order': True, 'session_exact_loc_lastmod_order': True, 'session_loc_set_equal': True})
        self.assertEqual(len(report['crawl']), len(paths))
        failure = next(row for row in report['crawl'] if not row['pass'])
        self.assertEqual(failure['status'], 500)
        self.assertEqual(failure['selection'], 'new')
        self.assertTrue(any(any(not row['pass'] for row in state.get('crawl', [])) for state in saved))

    def test_session_or_repeat_set_changes_cannot_pass(self):
        entries = [{'loc': TOOL.SEO_ORIGIN + '/', 'lastmod': None}]
        baseline = {'schema': 'gramlyze-m4-sitemap-baseline-v1', 'passed': True, 'sitemap': {'entries': entries}}
        capture_rows = [{'pass': True, 'entries': entries}, {'pass': True, 'entries': entries},
                        {'pass': True, 'entries': entries + [{'loc': TOOL.SEO_ORIGIN + '/courses', 'lastmod': None}]}]
        report = {}
        with mock.patch.object(TOOL, 'capture_sitemap', side_effect=capture_rows), \
                mock.patch.object(TOOL, 'request_local', return_value=({'status': 200}, b'')), \
                mock.patch.object(TOOL.http.cookiejar, 'CookieJar', return_value=[object()]), \
                mock.patch.object(TOOL.urllib.request, 'build_opener'), \
                mock.patch.object(TOOL, 'inspect_html', return_value={'path': '/', 'status': 200, 'pass': True, 'errors': []}), \
                mock.patch('builtins.print'):
            self.assertFalse(TOOL.run_comparison(report, baseline, lambda: None, 1))
        self.assertFalse(report['determinism']['session_loc_set_equal'])
        self.assertFalse(report['determinism']['session_exact_loc_lastmod_order'])

    def test_failed_sitemap_capture_stops_before_next_context_and_never_invents_removals(self):
        entries = [{'loc': TOOL.SEO_ORIGIN + '/', 'lastmod': None}]
        baseline = {'schema': 'gramlyze-m4-sitemap-baseline-v1', 'passed': True, 'sitemap': {'entries': entries}}
        good = {'pass': True, 'entries': entries}
        failed = {'pass': False, 'entries': [], 'request': {'error': 'TimeoutError'}, 'errors': ['sitemap-http-failed']}
        for phases, expected_halt in [([failed], 'initial-sitemap'), ([good, failed], 'fresh-repeat-sitemap')]:
            report, saved = {}, []
            with self.subTest(expected_halt=expected_halt), mock.patch.object(TOOL, 'capture_sitemap', side_effect=phases) as capture, \
                    mock.patch.object(TOOL, 'request_local') as visit, mock.patch.object(TOOL, 'inspect_html') as crawl:
                self.assertFalse(TOOL.run_comparison(report, baseline, lambda: saved.append(json.loads(json.dumps(report))), 2))
            self.assertEqual(capture.call_count, len(phases))
            visit.assert_not_called()
            crawl.assert_not_called()
            self.assertEqual(report['halted_at'], expected_halt)
            self.assertFalse(report['comparison_available'])
            self.assertNotIn('comparison', report)
            self.assertNotIn('crawl_plan', report)
            self.assertEqual(saved[-1], report)

    def test_failed_session_prerequisite_never_queues_crawl_or_more_sitemaps(self):
        entries = [{'loc': TOOL.SEO_ORIGIN + '/', 'lastmod': None}]
        baseline = {'schema': 'gramlyze-m4-sitemap-baseline-v1', 'passed': True, 'sitemap': {'entries': entries}}
        good = {'pass': True, 'entries': entries}
        failed = {'pass': False, 'entries': [], 'request': {'error': 'TimeoutError'}}
        for visit_result, phases, expected_halt in [({'error': 'TimeoutError'}, [good, good], 'ordinary-guest-session'),
                                                   ({'status': 200}, [good, good, failed], 'session-sitemap')]:
            report = {}
            with self.subTest(expected_halt=expected_halt), mock.patch.object(TOOL, 'capture_sitemap', side_effect=phases) as capture, \
                    mock.patch.object(TOOL, 'request_local', return_value=(visit_result, b'')) as visit, \
                    mock.patch.object(TOOL.http.cookiejar, 'CookieJar', return_value=[object()]), \
                    mock.patch.object(TOOL.urllib.request, 'build_opener'), mock.patch.object(TOOL, 'inspect_html') as crawl:
                self.assertFalse(TOOL.run_comparison(report, baseline, lambda: None, 2))
            self.assertEqual(capture.call_count, len(phases))
            visit.assert_called_once()
            crawl.assert_not_called()
            self.assertEqual(report['halted_at'], expected_halt)
            self.assertFalse(report['comparison_available'])
            self.assertNotIn('comparison', report)


if __name__ == '__main__':
    unittest.main()
