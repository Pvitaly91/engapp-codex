import importlib.util
import json
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch


ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('seo_m9_4_http', ROOT / 'tools/diagnostics/seo-m9-4-http.py')
tool = importlib.util.module_from_spec(spec)
spec.loader.exec_module(tool)


def entries(count=3):
    return [{'loc': f'https://gramlyze.com/fixture-{number}', 'lastmod': None} for number in range(count)]


def sitemap_xml(items):
    rows = ''.join(f'<url><loc>{entry["loc"]}</loc></url>' for entry in items)
    return ('<?xml version="1.0" encoding="UTF-8"?>'
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' + rows + '</urlset>').encode()


def html(path, *, questions=False, course=False, not_found=False):
    canonical = '' if not_found else f'<link rel="canonical" href="https://gramlyze.com{path}">'
    question_data = ('<script>window.__INITIAL_JS_TEST_QUESTIONS__ = [{"uuid":"fixture"}];'
                     'const endpoint="/test/future-perfect/questions/questions";</script>') if questions else ''
    course_link = ('<a href="/theory/basic-grammar/sentence-types">Theory</a>'
                   '<a href="/courses/english-grammar-theory/lesson/pronouns/one-ones">Course lesson</a>') if course else ''
    return (f'<html><head><title>Fixture title</title><meta name="description" content="Fixture description">{canonical}'
            '<link rel="stylesheet" href="/build/assets/catalog-public-fixture.css">'
            f'</head><body><main><h1>Fixture heading</h1><p>{"Learning content. " * 20}</p>{course_link}'
            f'</main>{question_data}</body></html>').encode()


def row(path, content_type, status=200):
    return {'path': path, 'status': status, 'content_type': content_type, 'x_robots_tag': 'noindex',
            'started_at': 'fixture', 'finished_at': 'fixture', 'duration_ms': 1}


class M94HttpAcceptanceTest(unittest.TestCase):
    def test_request_plan_is_exactly_the_seven_bounded_local_gets(self):
        self.assertEqual([name for name, _, _ in tool.REQUEST_PLAN], [
            'home', 'theory', 'questions-html', 'questions-json', 'course', 'sitemap', 'not-found',
        ])
        self.assertEqual([path for _, path, _ in tool.REQUEST_PLAN], [
            '/', tool.THEORY, tool.QUESTIONS, tool.QUESTIONS + '/questions', tool.COURSE, '/sitemap.xml', tool.NOT_FOUND,
        ])
        self.assertEqual(len(tool.REQUEST_PLAN), 7)
        for _, path, _ in tool.REQUEST_PLAN:
            self.assertEqual(tool.http.local_url(path), tool.http.BASE + path)

    def test_comparison_uses_actual_nonempty_baseline_count_and_full_order(self):
        baseline = entries()
        self.assertTrue(tool.compare_sitemaps(baseline, baseline)['pass'])
        self.assertFalse(tool.compare_sitemaps(baseline, list(reversed(baseline)))['pass'])
        self.assertFalse(tool.compare_sitemaps(baseline, entries(2))['pass'])
        self.assertTrue(tool.compare_sitemaps(entries(7), entries(7))['pass'])
        self.assertFalse(tool.compare_sitemaps([], [])['pass'])
        changed_lastmod = [*baseline]
        changed_lastmod[-1] = {**changed_lastmod[-1], 'lastmod': '2026-09-12'}
        self.assertFalse(tool.compare_sitemaps(baseline, changed_lastmod)['pass'])

    def test_questions_html_requires_nonempty_embedded_questions_and_endpoint(self):
        headers = row(tool.QUESTIONS, 'text/html; charset=UTF-8')
        with patch.object(tool.http, 'request_local', return_value=(headers, html(tool.QUESTIONS, questions=True))):
            accepted = tool.html_case(tool.QUESTIONS)
        self.assertTrue(accepted['pass'])
        with patch.object(tool.http, 'request_local', return_value=(headers, html(tool.QUESTIONS))):
            rejected = tool.html_case(tool.QUESTIONS)
        self.assertFalse(rejected['pass'])

    def test_json_requires_local_noindex_and_nonempty_questions(self):
        headers = row(tool.QUESTIONS + '/questions', 'application/json; charset=UTF-8')
        with patch.object(tool.http, 'request_local', return_value=(headers, b'{"questions":[{"uuid":"fixture"}]}')):
            self.assertTrue(tool.json_case()['pass'])
        headers['x_robots_tag'] = None
        with patch.object(tool.http, 'request_local', return_value=(headers, b'{"questions":[{"uuid":"fixture"}]}')):
            self.assertFalse(tool.json_case()['pass'])

    def test_html_rejects_canonical_query_even_when_sanitized_metadata_matches(self):
        headers = row(tool.THEORY, 'text/html; charset=UTF-8')
        body = html(tool.THEORY).replace((tool.THEORY + '">').encode(), (tool.THEORY + '?unexpected=1">').encode())
        with patch.object(tool.http, 'request_local', return_value=(headers, body)):
            self.assertFalse(tool.html_case(tool.THEORY)['pass'])

    def test_sitemap_rejects_invalid_xml_without_a_retry(self):
        headers = row('/sitemap.xml', 'application/xml; charset=UTF-8')
        with patch.object(tool.http, 'request_local', return_value=(headers, b'not xml')) as local_get:
            result = tool.sitemap_case()
        self.assertFalse(result['pass'])
        self.assertEqual(len(local_get.call_args_list), 1)

    def test_question_comparison_preserves_answers_while_allowing_fresh_random_variants(self):
        headers = row(tool.QUESTIONS + '/questions', 'application/json')
        results = []
        for question, answer in [('Variant one', 'answer'), ('Variant two', 'answer'), ('Variant two', 'changed')]:
            body = json.dumps({'questions': [{'uuid': 'fixture', 'question': question, 'answer': answer}]}).encode()
            with patch.object(tool.http, 'request_local', return_value=(dict(headers), body)):
                results.append(tool.json_case())
        self.assertNotEqual(results[0]['question_content_sha256'], results[1]['question_content_sha256'])
        self.assertEqual(results[0]['question_stable_sha256'], results[1]['question_stable_sha256'])
        self.assertNotEqual(results[1]['question_stable_sha256'], results[2]['question_stable_sha256'])

    def test_run_makes_only_the_planned_seven_requests_and_returns_true_only_for_complete_evidence(self):
        baseline_entries = entries()
        with tempfile.TemporaryDirectory() as temporary:
            root = Path(temporary)

            def request(path, accept, *, limit):
                if path == '/sitemap.xml':
                    return row(path, 'application/xml'), sitemap_xml(baseline_entries)
                if path == tool.QUESTIONS + '/questions':
                    return row(path, 'application/json'), b'{"questions":[{"uuid":"fixture"}]}'
                if path == tool.NOT_FOUND:
                    return row(path, 'text/html', status=404), html(path, not_found=True)
                return row(path, 'text/html'), html(path, questions=path == tool.QUESTIONS, course=path == tool.COURSE)

            with patch.object(tool, 'OUT', root / 'output'), patch.object(tool.http, 'request_local', side_effect=request) as local_get:
                self.assertTrue(tool.run('before'))
            self.assertEqual([call.args[0] for call in local_get.call_args_list], [path for _, path, _ in tool.REQUEST_PLAN])
            baseline = root / 'output' / 'before-http.json'
            with patch.object(tool, 'OUT', root / 'output'), patch.object(tool.http, 'request_local', side_effect=request) as local_get:
                self.assertTrue(tool.run('fixture', str(baseline)))
            self.assertEqual([call.args[0] for call in local_get.call_args_list], [path for _, path, _ in tool.REQUEST_PLAN])
            report = json.loads((root / 'output' / 'fixture-http.json').read_text(encoding='utf-8'))
            self.assertTrue(report['pass'])
            self.assertEqual(report['request_count'], 7)
            self.assertTrue(report['sitemap_comparison']['ordered_equal'])
            self.assertTrue(report['page_comparison']['pass'])
            self.assertEqual(report['course_path'], '/courses/english-grammar-theory/lesson/pronouns/one-ones')

    def test_invalid_baseline_stops_before_any_http_request(self):
        with tempfile.TemporaryDirectory() as temporary:
            baseline = Path(temporary) / 'baseline.json'
            baseline.write_text(json.dumps({'sitemap': {'pass': True, 'entries': entries()}}), encoding='utf-8')
            with patch.object(tool.http, 'request_local') as local_get:
                with self.assertRaisesRegex(ValueError, 'fresh M9.4 baseline'):
                    tool.run('fixture', str(baseline))
            local_get.assert_not_called()

    def test_page_comparison_detects_metadata_and_learning_content_changes(self):
        baseline = [{'path': tool.THEORY, 'status': 200, 'metadata': {'title': ['Before']},
                     'content': {'main_text_sha256': 'original'}}]
        self.assertTrue(tool.compare_pages(baseline, baseline)['pass'])
        changed = [{**baseline[0], 'metadata': {'title': ['After']}, 'content': {'main_text_sha256': 'changed'}}]
        result = tool.compare_pages(baseline, changed)
        self.assertFalse(result['pass'])
        self.assertEqual(result['changed'][0]['fields'], ['metadata', 'content'])

    def test_visible_text_digest_ignores_session_scripts_but_detects_learning_text(self):
        before = tool.parse_html(b'<main><p>Learning text</p><script>var token="one"</script></main>')[1]
        token_changed = tool.parse_html(b'<main><p>Learning text</p><script>var token="two"</script></main>')[1]
        content_changed = tool.parse_html(b'<main><p>Changed text</p><script>var token="one"</script></main>')[1]
        self.assertEqual(before['main_text_sha256'], token_changed['main_text_sha256'])
        self.assertNotEqual(before['main_text_sha256'], content_changed['main_text_sha256'])


if __name__ == '__main__':
    unittest.main()
