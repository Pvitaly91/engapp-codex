"""Fail closed on any non-M8 DB/content/metadata/sitemap change in saved evidence."""
import hashlib
import json
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / 'storage/app/seo-m8-local'


def read(name):
    return json.loads((OUT / name).read_text(encoding='utf-8'))


def compare():
    before, after = read('before-db.json'), read('after-db.json')
    plan = read('m8-plan-v1.json')
    assert before['connection'] == after['connection']
    changed_ids = {str(c['id']) for c in plan['changes']}
    a, b = before['text_block_row_hashes'], after['text_block_row_hashes']
    assert a.keys() == b.keys()
    assert {k for k in a if a[k] != b[k]} == changed_ids
    changes = {c['id']: c for c in plan['changes']}
    restored = []
    for old, new in zip(before['pages'], after['pages'], strict=True):
        assert old['page'] == new['page']
        assert len(old['blocks']) == len(new['blocks'])
        count = 0
        for previous, current in zip(old['blocks'], new['blocks'], strict=True):
            expected = changes.get(previous['id'])
            assert current == (previous | expected['after'] if expected else previous)
            if expected:
                assert previous['locale'] == 'uk' and previous['type'] == 'box' and previous['body'] is None
                count += 1
        restored.append({'slug': old['page']['slug'], 'restored': count})
    for table, previous in before['tables'].items():
        if table != 'text_blocks':
            assert previous == after['tables'][table], table
        else:
            assert previous['count'] == after['tables'][table]['count']
    h1, h2 = read('before-http.json'), read('after-http.json')
    assert h1['sitemap']['entries'] == h2['sitemap']['entries']
    assert len(h2['sitemap']['entries']) == 554
    assert all(e['lastmod'] is None for e in h2['sitemap']['entries'])
    assert h1['sitemap']['request']['body_sha256'] == h2['sitemap']['request']['body_sha256']
    for old, new in zip(h1['rows'], h2['rows'], strict=True):
        assert old['path'] == new['path'] and old['status'] == new['status'] == 200
        assert old['metadata'] == new['metadata'], new['path']
        assert old['x_robots_tag'] == new['x_robots_tag']
        assert 'noindex' in new['x_robots_tag']
        assert new['built_assets']
        assert old['blocks'].keys() == new['blocks'].keys()
        for block_id, block in new['blocks'].items():
            if block_id.removeprefix('block-') in changed_ids:
                assert block['text'] and not block['raw_tags']
            else:
                assert block == old['blocks'][block_id], (new['path'], block_id)
        assert all(m['source_body_in_html'] for m in new.get('source_matches', []))
    sources = []
    for seeder, info in plan['sources'].items():
        relative = 'database/' + info['path']
        original = subprocess.check_output(['git', 'show', 'fd4ad5d1eec74fb16f320bdf07649333b1ab17b5:' + relative], cwd=ROOT)
        current = (ROOT / relative).read_bytes()
        old, new = json.loads(original), json.loads(current)
        for block in new['page']['blocks']:
            if 'layout' in block:
                for field in ('type', 'heading', 'body'):
                    block.pop(field)
        assert old == new, 'Only supported block fields may be added: ' + relative
        sources.append({'path': relative, 'before_sha256': hashlib.sha256(original).hexdigest(),
                        'after_sha256': hashlib.sha256(current).hexdigest(), 'all_legacy_fields_unchanged': True})
    return {'pass': True, 'restored': restored, 'unchanged_text_blocks': len(a) - len(changed_ids),
            'unchanged_other_tables': len(before['tables']) - 1, 'table_counts': {t: v['count'] for t, v in after['tables'].items()},
            'http_pages': len(h2['rows']), 'ordered_sitemap_urls': 554, 'sitemap_sha256': h2['sitemap']['request']['body_sha256'],
            'sources': sources}


if __name__ == '__main__':
    result = compare()
    with (OUT / 'comparison.json').open('x', encoding='utf-8') as out:
        json.dump(result, out, ensure_ascii=False, indent=2)
    print(json.dumps(result, ensure_ascii=False, indent=2))
