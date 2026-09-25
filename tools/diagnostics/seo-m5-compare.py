"""Summarize sanitized M5 inventories; preserve missing/failed captures as unknown."""
import collections
import argparse
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
DATA = ROOT / 'storage/app/seo-m5-local'


def rows(label, supplement=None):
    report = json.loads((DATA / (label + '.json')).read_text(encoding='utf-8'))
    result = {r['path']: r for r in report['rows']}
    if supplement:
        for r in json.loads((DATA / (supplement + '.json')).read_text(encoding='utf-8')):
            if r.get('metadata'): result[r['path']] = r
    return report, result


def stats(items):
    valid = [r for r in items.values() if r.get('metadata')]
    result = {'successful': len(valid), 'failed': len(items) - len(valid)}
    for key in ('title', 'description'):
        groups = collections.defaultdict(list)
        result['empty_' + key] = 0
        result['multiple_' + key] = 0
        result['ellipsis_' + key] = 0
        result['trailing_ellipsis_' + key] = 0
        result['technical_' + key] = 0
        for row in valid:
            values = row['metadata'][key]
            result['empty_' + key] += not values or not any(v.strip() for v in values)
            result['multiple_' + key] += len(values) > 1
            value = values[0] if values else ''
            result['ellipsis_' + key] += '…' in value
            result['trailing_ellipsis_' + key] += value.endswith('…') or value.endswith('… | Gramlyze')
            result['technical_' + key] += bool(re.search(r'<[/a-zA-Z][^>]*>|&(?:amp|lt|gt|quot|#\d+);|\b(?:public|frontend)\.[a-z_.]+', value))
            if value: groups[value].append(row['path'])
        result['duplicates_' + key] = [paths for paths in groups.values() if len(paths) > 1]
        result['max_length_' + key] = max((len(r['metadata'][key][0]) if r['metadata'][key] else 0 for r in valid), default=0)
    result['social_mismatch'] = sum(any(r['metadata'][k] != r['metadata'][base] for k, base in [('og:title', 'title'), ('twitter:title', 'title'), ('og:description', 'description'), ('twitter:description', 'description')]) for r in valid)
    return result


def compare(after_label='m5-after', after_supplement=None):
    before, old = rows('m5-before', 'm5-before-recheck')
    after, new = rows(after_label, after_supplement)
    changes, invariants = [], []
    for path, previous in old.items():
        current = new.get(path, {})
        if not previous.get('metadata') or not current.get('metadata'): continue
        a, b = previous['metadata'], current['metadata']
        if a['title'] != b['title'] or a['description'] != b['description']:
            changes.append({'path': path, 'before': {k: a[k] for k in ['title', 'description']}, 'after': {k: b[k] for k in ['title', 'description']}})
        for key in ['h1', 'canonical', 'robots']:
            if a[key] != b[key]: invariants.append({'path': path, 'key': key})
        for key in ['status', 'x_robots_tag', 'site_mode']:
            if previous.get(key) != current.get(key): invariants.append({'path': path, 'key': key})
    result = {'after_label': after_label, 'after_supplement': after_supplement, 'before': stats(old), 'after': stats(new), 'changed': len(changes), 'invariant_changes': invariants,
              'same_ordered_sitemap': before['sitemap']['entries'] == after['sitemap']['entries'], 'changes': changes}
    (DATA / 'comparison.json').write_text(json.dumps(result, ensure_ascii=False, indent=2), encoding='utf-8')
    print(json.dumps({k: v for k, v in result.items() if k != 'changes'}, ensure_ascii=False, indent=2))


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--after-label', default='m5-after')
    parser.add_argument('--after-supplement')
    args = parser.parse_args()
    if any(not re.fullmatch(r'[A-Za-z0-9_-]+', label) for label in (args.after_label, args.after_supplement) if label is not None):
        parser.error('Simple inventory label required')
    compare(args.after_label, args.after_supplement)
