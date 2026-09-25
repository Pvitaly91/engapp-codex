import copy
import importlib.util
import json
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]


def module(name):
    spec = importlib.util.spec_from_file_location(name, ROOT / 'tools/diagnostics' / (name + '.py'))
    result = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(result)
    return result


http = module('seo-m8-1-http')
comparison = module('seo-m8-1-compare')


class EditorialDiagnosticsTest(unittest.TestCase):
    def test_definition_diff_is_exactly_the_approved_five_pairs_against_m8(self):
        manifest = json.loads((ROOT / 'database/content-patches/one-ones-m8-1.json').read_text(encoding='utf-8'))
        self.assertTrue(comparison.compare_sources(manifest)['reciprocal_unchanged'])

    def test_local_only_get_paths(self):
        for path in ['https://gramlyze.com/theory', '//gramlyze.ub/theory', '/theory?seeder=spoof', '/%2e%2e/theory']:
            with self.assertRaises(ValueError):
                http.m5.http.local_url(path)

    def test_html_retains_list_structure_and_detects_literal_tags(self):
        parser = http.Blocks()
        parser.feed('<div id="block-1"><ol><li>Water &amp; coffee</li></ol><p>&lt;strong&gt;raw&lt;/strong&gt;</p></div>')
        self.assertEqual(1, parser.blocks['block-1']['tags']['li'])
        self.assertTrue(parser.blocks['block-1']['raw_tags'])

    def test_body_only_comparison_rejects_other_changes(self):
        blocks = [{'id': i, 'uuid': str(i), 'locale': 'uk', 'seeder': 'One', 'body': 'old'} for i in range(1, 6)]
        reciprocal = {'page': {'slug': 'reciprocal'}, 'blocks': [{'id': 6, 'body': 'control'}]}
        before = {'connection': {'db': 'fixture'}, 'pages': [{'page': {'slug': 'one-ones'}, 'blocks': blocks}, reciprocal],
                  'text_block_row_hashes': {str(i): 'old' for i in range(1, 7)},
                  'tables': {'questions': {'sha256': 'same'}, 'text_blocks': {'count': 6}}}
        plan = {'changes': [{'id': i, 'uuid': str(i), 'seeder': 'One', 'sort_order': order,
                            'before': {'body': 'old'}, 'after': {'body': 'new'}}
                           for i, order in enumerate([2, 5, 7, 8, 9], 1)]}
        after = copy.deepcopy(before)
        for block in after['pages'][0]['blocks']:
            block['body'] = 'new'
            after['text_block_row_hashes'][str(block['id'])] = 'new'
        comparison.compare_db(before, after, plan)
        for target in ['metadata', 'questions', 'reciprocal', 'unexpected-field']:
            bad = copy.deepcopy(after)
            if target == 'metadata':
                bad['pages'][0]['page']['title'] = 'changed'
            elif target == 'questions':
                bad['tables']['questions']['sha256'] = 'changed'
            elif target == 'reciprocal':
                bad['pages'][1]['blocks'][0]['body'] = 'changed'
            else:
                bad['pages'][0]['blocks'][0]['heading'] = 'changed'
            with self.assertRaises(AssertionError):
                comparison.compare_db(before, bad, plan)


if __name__ == '__main__':
    unittest.main()
