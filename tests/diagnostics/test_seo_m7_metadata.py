import copy
import importlib.util
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]


def module(name):
    spec = importlib.util.spec_from_file_location(name, ROOT / 'tools/diagnostics' / (name + '.py'))
    result = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(result)
    return result


http = module('seo-m7-http')
comparison = module('seo-m7-compare')


class EditorialDiagnosticsTest(unittest.TestCase):
    def test_only_local_safe_paths_can_be_requested(self):
        for path in ['https://gramlyze.com/theory', '//gramlyze.ub/theory', '/theory?seeder=spoof', '/%2e%2e/theory']:
            with self.assertRaises(ValueError):
                http.m5.http.local_url(path)
        self.assertEqual('http://gramlyze.loc/theory/tenses/narrative-tenses', http.m5.http.local_url('/theory/tenses/narrative-tenses'))

    def test_parser_reads_hero_rules_and_allowed_blocks_not_practice_or_scripts(self):
        parser = http.LessonText([{'id': 1}])
        parser.feed('<div data-theory-main><section><h1>Lesson</h1>Intro</section><section class="grid xl:grid-cols-3">Rule</section>'
                    '<section><div id="block-1"><p>Explanation &amp; example</p><script>SECRET</script></div>'
                    '<div id="block-2">PRACTICE ANSWER</div></section><section>Related random test</section></div>')
        self.assertEqual(['Lesson', 'Intro', 'Rule', 'Explanation & example'], parser.parts)

    def test_comparison_requires_exact_copy_and_preserved_invariants(self):
        old = {'path': '/theory/x/y', 'status': 200, 'content_type': 'text/html', 'x_robots_tag': 'noindex',
               'built_assets': True, 'lesson_text_sha256': 'content',
               'metadata': {key: ['same'] for key in comparison.INVARIANTS}}
        old['metadata']['description'] = ['Пояснення теми «Тема» в англійській граматиці.']
        new = copy.deepcopy(old)
        for key in ['description', 'og:description', 'twitter:description']:
            new['metadata'][key] = ['Конкретний опис.']
        self.assertTrue(comparison.compare_row(old, new, 'Конкретний опис.')['pass'])
        for key in comparison.INVARIANTS:
            changed = copy.deepcopy(new)
            changed['metadata'][key] = ['changed']
            self.assertFalse(comparison.compare_row(old, changed, 'Конкретний опис.')['pass'])
        new['error'] = 'TimeoutError'
        self.assertFalse(comparison.compare_row(old, new, 'Конкретний опис.')['pass'])

    def test_control_metadata_must_remain_identical(self):
        row = {'path': '/test/x/questions', 'status': 200, 'content_type': 'text/html', 'x_robots_tag': 'noindex',
               'built_assets': True, 'metadata': {key: ['same'] for key in comparison.INVARIANTS}}
        self.assertTrue(comparison.compare_row(row, row)['pass'])
        changed = copy.deepcopy(row)
        changed['metadata']['description'] = ['New']
        self.assertFalse(comparison.compare_row(row, changed)['pass'])


if __name__ == '__main__':
    unittest.main()
