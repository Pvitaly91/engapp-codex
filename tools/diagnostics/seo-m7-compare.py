"""Offline comparison of focused M7 evidence. No HTTP, DB or full inventory crawl."""
import argparse
import json
import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / 'storage/app/seo-m7-local'
INVARIANTS = ('title', 'h1', 'canonical', 'robots', 'og:title', 'twitter:title')


def compare_row(before, after, expected=None):
    checks = {}
    for label, row in [('before', before), ('after', after)]:
        checks[label + '_html'] = row.get('status') == 200 and 'text/html' in (row.get('content_type') or '') and not row.get('error')
    old, new = before.get('metadata', {}), after.get('metadata', {})
    checks['invariants'] = all(key in old and old[key] == new.get(key) for key in INVARIANTS)
    checks['x_robots'] = before.get('x_robots_tag') == after.get('x_robots_tag') and 'noindex' in (after.get('x_robots_tag') or '')
    checks['lesson_content'] = before.get('lesson_text_sha256') == after.get('lesson_text_sha256')
    checks['built_assets'] = after.get('built_assets') is True
    if expected is None:
        checks['control_unchanged'] = old == new
    else:
        checks['fallback_before'] = len(old.get('description', [])) == 1 and old['description'][0].startswith('Пояснення теми «')
        checks['exact_description'] = new.get('description') == [expected]
        checks['social_descriptions'] = new.get('og:description') == [expected] and new.get('twitter:description') == [expected]
        checks['safe_plain_text'] = not re.search(r'<[^>]+>|&(?:lt|gt|amp|quot);|\{a\d+\}', expected)
    return {'path': before['path'], 'checks': checks, 'pass': all(checks.values())}


def load_rows(labels):
    rows = {}
    for label in labels:
        if not re.fullmatch(r'[a-zA-Z0-9_-]+', label):
            raise ValueError('Simple evidence label required')
        report = json.loads((OUTPUT / (label + '.json')).read_text(encoding='utf-8'))
        if not report.get('pass'):
            raise ValueError('An unsuccessful attempt cannot replace acceptance evidence: ' + label)
        for row in report['rows']:
            if row['path'] in rows:
                raise ValueError('Ambiguous evidence for ' + row['path'])
            rows[row['path']] = row
    return rows


def main():
    p = argparse.ArgumentParser()
    p.add_argument('--label', required=True)
    p.add_argument('--php', required=True)
    p.add_argument('--before', nargs='+', required=True)
    p.add_argument('--after', nargs='+', required=True)
    p.add_argument('--after-supplement', nargs='+', default=[])
    p.add_argument('--sitemap-before', required=True)
    p.add_argument('--sitemap-after', required=True)
    a = p.parse_args()
    for label in [a.label, a.sitemap_before, a.sitemap_after]:
        if not re.fullmatch(r'[a-zA-Z0-9_-]+', label):
            raise ValueError('Simple evidence label required')
    # Pure formatter export, no application bootstrap, .env, DB or network access.
    process = subprocess.run([a.php, '-r', "require 'vendor/autoload.php'; echo json_encode(App\\Support\\TheoryEditorialDescriptions::UK, JSON_THROW_ON_ERROR);"],
                             cwd=ROOT, capture_output=True, check=True)
    registry = json.loads(process.stdout)
    context = json.loads((OUTPUT / 'context.json').read_text(encoding='utf-8'))
    expected = {r['path']: registry[r['identity']] for r in context if r['identity'] in registry}
    before, after = load_rows(a.before), load_rows(a.after)
    supplement = load_rows(a.after_supplement)
    if not supplement.keys() <= after.keys():
        raise ValueError('Supplement must explicitly recheck an already captured page')
    after.update(supplement)  # Original attempts stay immutable in the named evidence files.
    if before.keys() != after.keys() or not expected.keys() <= before.keys():
        raise ValueError('Incomplete before/after coverage')
    report = {'rows': [compare_row(row, after[path], expected.get(path)) for path, row in before.items()],
              'registry_count': len(registry), 'changed_count': len(expected), 'controls': len(before) - len(expected),
              'after_files': a.after, 'after_supplement_files': a.after_supplement,
              'unique_verified_identity': all(r['identity_count'] == 1 and r['fallback'] for r in context if r['path'] in expected)}
    old = json.loads((OUTPUT / (a.sitemap_before + '.json')).read_text(encoding='utf-8'))['sitemap']
    new = json.loads((OUTPUT / (a.sitemap_after + '.json')).read_text(encoding='utf-8'))['sitemap']
    report['sitemap'] = {'count': len(new['entries']), 'ordered_entries_unchanged': old['pass'] and new['pass'] and old['entries'] == new['entries'],
                         'sha256_before': old['request']['body_sha256'], 'sha256_after': new['request']['body_sha256']}
    report['pass'] = all(r['pass'] for r in report['rows']) and report['unique_verified_identity'] and report['sitemap']['ordered_entries_unchanged'] and len(registry) == len(expected)
    with (OUTPUT / (a.label + '.json')).open('x', encoding='utf-8') as out:
        json.dump(report, out, ensure_ascii=False, indent=2)
    print(json.dumps({k: v for k, v in report.items() if k != 'rows'}))
    for row in report['rows']:
        if not row['pass']:
            print(json.dumps(row))
    return report['pass']


if __name__ == '__main__':
    raise SystemExit(0 if main() else 1)
