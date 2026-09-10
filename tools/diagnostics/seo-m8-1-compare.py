"""Exact M8.1 body-only acceptance; saved local evidence, no network or DB writes."""
import hashlib
import json
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / 'storage/app/seo-m8-1-local'
BASE = 'be84d48e3071d6988c381f8020c89672e84938c4'


def read(name):
    return json.loads((OUT / name).read_text(encoding='utf-8'))


def compare_db(before, after, plan):
    assert before['connection'] == after['connection']
    assert [c['sort_order'] for c in plan['changes']] == [2, 5, 7, 8, 9]
    changes = {c['id']: c for c in plan['changes']}
    changed_ids = {str(i) for i in changes}
    a, b = before['text_block_row_hashes'], after['text_block_row_hashes']
    assert a.keys() == b.keys()
    assert {k for k in a if a[k] != b[k]} == changed_ids
    changed = []
    for old, new in zip(before['pages'], after['pages'], strict=True):
        assert old['page'] == new['page']
        count = 0
        for previous, current in zip(old['blocks'], new['blocks'], strict=True):
            expected = changes.get(previous['id'])
            if expected:
                assert set(expected['before']) == set(expected['after']) == {'body'}
                assert previous['body'] == expected['before']['body'] and previous['body']
                assert previous['uuid'] == expected['uuid'] and previous['locale'] == 'uk'
                assert previous['seeder'] == expected['seeder'] and old['page']['slug'] == 'one-ones'
                count += 1
            assert current == (previous | expected['after'] if expected else previous)
        changed.append({'slug': old['page']['slug'], 'updated': count})
    assert [r['updated'] for r in changed] == [5, 0]
    for table, previous in before['tables'].items():
        if table != 'text_blocks':
            assert previous == after['tables'][table], table
        else:
            assert previous['count'] == after['tables'][table]['count']
    return changed


def compare_http(before, after, changed_ids):
    assert before['pass'] and after['pass']
    assert before['sitemap']['entries'] == after['sitemap']['entries']
    assert len(after['sitemap']['entries']) == 554
    assert all(e['lastmod'] is None for e in after['sitemap']['entries'])
    assert before['sitemap']['request']['body_sha256'] == after['sitemap']['request']['body_sha256']
    assert len(before['rows']) == len(after['rows']) == 4
    for old, new in zip(before['rows'], after['rows'], strict=True):
        assert old['path'] == new['path'] and old['status'] == new['status'] == 200
        assert old['metadata'] == new['metadata'], new['path']
        assert old['x_robots_tag'] == new['x_robots_tag'] and 'noindex' in new['x_robots_tag']
        assert old['built_assets'] and new['built_assets']
        assert old['blocks'].keys() == new['blocks'].keys()
        actual = set()
        for block_id, block in new['blocks'].items():
            if block != old['blocks'][block_id]:
                actual.add(block_id.removeprefix('block-'))
                assert block['text'] and not block['raw_tags']
        assert actual == (changed_ids if new['path'].endswith('/one-ones') else set())
        assert all(m['source_body_in_html'] for m in old['source_matches'])
        assert all(m['source_body_in_html'] for m in new['source_matches'])


def compare_sources(manifest):
    relative = 'database/' + manifest['source']
    original = subprocess.check_output(['git', 'show', BASE + ':' + relative], cwd=ROOT)
    current = (ROOT / relative).read_bytes()
    old, new = json.loads(original), json.loads(current)
    for entry in manifest['blocks']:
        i = entry['sort_order'] - 1
        assert old['page']['blocks'][i]['body'] == old['page']['blocks'][i]['content']['html'] == entry['before_body']
        assert new['page']['blocks'][i]['body'] == new['page']['blocks'][i]['content']['html'] == entry['after_body']
        new['page']['blocks'][i]['body'] = entry['before_body']
        new['page']['blocks'][i]['content']['html'] = entry['before_body']
    assert old == new, 'No other definition changes are allowed'
    reciprocal = relative.replace('OneOnes', 'ReciprocalPronouns')
    reciprocal_old = subprocess.check_output(['git', 'show', BASE + ':' + reciprocal], cwd=ROOT)
    assert json.loads(reciprocal_old) == json.loads((ROOT / reciprocal).read_bytes())
    return {'definition': relative, 'before_sha256': hashlib.sha256(original).hexdigest(),
            'after_sha256': hashlib.sha256(current).hexdigest(), 'reciprocal_unchanged': True}


def compare():
    before, after, plan = read('before-db.json'), read('after-db.json'), read('m8-1-plan-v1.json')
    manifest = json.loads((ROOT / 'database/content-patches/one-ones-m8-1.json').read_text(encoding='utf-8'))
    changed = compare_db(before, after, plan)
    h1, h2 = read('before-http.json'), read('after-http.json')
    compare_http(h1, h2, {str(c['id']) for c in plan['changes']})
    return {'pass': True, 'changed': changed, 'unchanged_text_blocks': len(before['text_block_row_hashes']) - 5,
            'unchanged_other_tables': len(before['tables']) - 1, 'table_counts': {t: v['count'] for t, v in after['tables'].items()},
            'http_pages': 4, 'ordered_sitemap_urls': 554, 'sitemap_sha256': h2['sitemap']['request']['body_sha256'],
            'sources': compare_sources(manifest)}


if __name__ == '__main__':
    result = compare()
    with (OUT / 'comparison.json').open('x', encoding='utf-8') as out:
        json.dump(result, out, ensure_ascii=False, indent=2)
    print(json.dumps(result, ensure_ascii=False, indent=2))
