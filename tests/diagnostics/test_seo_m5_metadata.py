import importlib.util
import tempfile
import unittest
from pathlib import Path
from unittest import mock

spec = importlib.util.spec_from_file_location('m5', Path(__file__).resolve().parents[2] / 'tools/diagnostics/seo-m5-metadata.py')
tool = importlib.util.module_from_spec(spec)
spec.loader.exec_module(tool)
compare_spec = importlib.util.spec_from_file_location('m5_compare', Path(__file__).resolve().parents[2] / 'tools/diagnostics/seo-m5-compare.py')
comparison = importlib.util.module_from_spec(compare_spec)
compare_spec.loader.exec_module(comparison)


class MetadataDiagnosticsTest(unittest.TestCase):
    def test_parser_retains_metadata_not_private_body(self):
        p = tool.Metadata()
        p.feed('<head><title>Future Perfect &amp; Past Simple</title><meta name="description" content="Українська &quot;мова&quot;"><meta name="csrf-token" content="SECRET"></head><main><h1>Назва <b>уроку</b></h1><script>PRIVATE</script></main>')
        self.assertEqual(p.values['title'], ['Future Perfect & Past Simple'])
        self.assertEqual(p.values['h1'], ['Назва уроку'])
        self.assertNotIn('PRIVATE', str(p.values))
        self.assertNotIn('SECRET', str(p.values))

    def test_duplicate_tags_are_not_hidden(self):
        p = tool.Metadata()
        p.feed('<title>A</title><title>B</title><meta name="description" content="A"><meta name="description" content="B">')
        self.assertEqual(p.values['title'], ['A', 'B'])
        self.assertEqual(p.values['description'], ['A', 'B'])

    def test_failed_http_is_not_reported_as_empty_metadata(self):
        with mock.patch.object(tool.http, 'request_local', return_value=({'status': 500}, b'<title>Error</title>')):
            self.assertNotIn('metadata', tool.capture('/theory'))

    def test_unsafe_paths_and_concurrency_and_overwrite_are_rejected(self):
        for path in ['https://gramlyze.com/', '//gramlyze.ub/', '/theory?source=secret']:
            with self.assertRaises(ValueError): tool.http.local_url(path)
        with self.assertRaises(ValueError): tool.run('bad', 3)
        with tempfile.TemporaryDirectory() as folder, mock.patch.object(tool, 'OUTPUT', Path(folder)):
            Path(folder, 'existing.json').touch()
            with self.assertRaises(FileExistsError): tool.run('existing')

    def test_comparison_keeps_failures_unknown_and_semantic_ellipsis_distinct(self):
        p = tool.Metadata()
        p.feed('<title>As…as — правила | Gramlyze</title><meta name="description" content="Порівняння as…as.">')
        result = comparison.stats({'/ok': {'path': '/ok', 'metadata': p.values}, '/failed': {'status': 500}})
        self.assertEqual(result['successful'], 1)
        self.assertEqual(result['failed'], 1)
        self.assertEqual(result['empty_title'], 0)
        self.assertEqual(result['ellipsis_title'], 1)
        self.assertEqual(result['trailing_ellipsis_title'], 0)
        self.assertEqual(result['trailing_ellipsis_description'], 0)


if __name__ == '__main__': unittest.main()
