"""One bounded M9 GET set. Never follows metadata origins or writes application data."""
import argparse
import importlib.util
import json
import re
import urllib.parse
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / 'storage/app/seo-m9-local'


def module(name, filename):
    spec = importlib.util.spec_from_file_location(name, Path(__file__).with_name(filename))
    result = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(result)
    return result


m8 = module('m9_blocks', 'seo-m8-1-http.py')
http, Metadata = m8.m5.http, m8.m5.Metadata
PREFIX = 'database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstratives'
SOURCES = {name: PREFIX + name + 'TheorySeeder/definition.json' for name in ['OneOnes', 'ReciprocalPronouns']}


def compare_entries(before, after):
    old, new = [e['loc'] for e in before], [e['loc'] for e in after]
    return {'ordered_equal': before == after, 'before_count': len(old), 'after_count': len(new),
            'added': sorted(set(new) - set(old)), 'removed': sorted(set(old) - set(new)),
            'lastmod_count': sum(e.get('lastmod') is not None for e in after)}


def course_mapping(links, slug):
    matches = sorted({urllib.parse.urlsplit(link).path for link in links
                      if urllib.parse.urlsplit(link).netloc in ('', 'gramlyze.loc')
                      and urllib.parse.urlsplit(link).path.startswith('/courses/english-grammar-theory/lesson/')
                      and urllib.parse.urlsplit(link).path.endswith('/' + slug)})
    if len(matches) != 1:
        raise ValueError('Missing/ambiguous native course mapping')
    http.local_url(matches[0])
    return matches[0]


def capture(path, status=200, canonical=None, source=None, accept='text/html'):
    row, body = http.request_local(path, accept, limit=http.MAX_HTML)
    row['pass'] = row.get('status') == status and not row.get('error')
    row['final_url'] = http.BASE + path
    row['source'] = source
    links = []
    if 'text/html' in row.get('content_type', ''):
        text = body.decode('utf-8', errors='strict')
        meta, content, blocks = Metadata(), http.HtmlEvidence(), m8.Blocks()
        for parser in [meta, content, blocks]:
            parser.feed(text)
            parser.close()
        row['metadata'], row['content'] = meta.values, content.evidence()
        links = blocks.links
        row['assets'] = '/build/assets/' in text and '@vite/client' not in text and 'cdn.tailwindcss.com' not in text and 'unpkg.com/alpinejs' not in text
        if status == 200:
            row['pass'] &= (all(len(meta.values[k]) == 1 and meta.values[k][0] for k in ['title', 'description', 'h1'])
                            and meta.values['canonical'] == ['https://gramlyze.com' + (canonical or path)]
                            and not any('index' in re.split(r'[,\s]+', value) for value in meta.values['robots'])
                            and 'noindex' in (row.get('x_robots_tag') or '')
                            and content.main_characters > 100 and row['assets'])
        if status == 404:
            row['pass'] &= not meta.values['canonical'] and 'noindex' in (row.get('x_robots_tag') or '')
        if source:
            definition = json.loads((ROOT / source).read_text(encoding='utf-8'))
            row['source_matches'] = [{'order': order, 'exact_body': block['body'] in text}
                                     for order, block in enumerate(definition['page']['blocks'], 1) if 'layout' in block]
            row['pass'] &= bool(row['source_matches']) and all(x['exact_body'] for x in row['source_matches'])
            row['pass'] &= not any(block['raw_tags'] for block in blocks.blocks.values())
        if path == '/test/future-perfect/questions':
            row['pass'] &= bool(content.question_count and content.question_count > 0)
            # The authoritative browser property is checked separately in M9 browser.
            row['nested_endpoint_in_html'] = '/test/future-perfect/questions/questions' in text.replace('\\/', '/')
            row['pass'] &= row['nested_endpoint_in_html']
        if 'theory-passive-voice-formation-rules' in path:
            row['pass'] &= not any(t in text for t in ['Page Folder Unseed Targets Debug', 'Page_V3 folder unseed block.'])
    elif accept == 'application/json':
        value = json.loads(body)
        row['question_count'] = len(value.get('questions', []))
        row['pass'] &= row['question_count'] > 0 and 'noindex' in (row.get('x_robots_tag') or '')
    else:
        row['pass'] = False
    return row, links


def reassess(row):
    """Recheck saved evidence after correcting the header-only robots assumption; no GET."""
    if row.get('error'):
        return False
    path, status = row['path'], row.get('status')
    if path == '/tests/cards':
        return status == 302 and row.get('location') == http.BASE + '/catalog/tests-cards'
    meta = row.get('metadata', {})
    if path == '/courses/polyglot-english-a9':
        return status == 404 and not meta.get('canonical') and 'noindex' in (row.get('x_robots_tag') or '')
    if row.get('accept') == 'application/json':
        return status == 200 and row.get('question_count', 0) > 0 and 'noindex' in (row.get('x_robots_tag') or '')
    canonical = '/theory/zaimennyky-ta-vkazivni-slova/one-ones' if '/lesson/' in path else path
    return (status == 200 and 'text/html' in row.get('content_type', '') and row.get('assets')
            and all(len(meta.get(k, [])) == 1 and meta[k][0] for k in ['title', 'description', 'h1'])
            and meta.get('canonical') == ['https://gramlyze.com' + canonical]
            and 'noindex' in (row.get('x_robots_tag') or '')
            and not any('index' in re.split(r'[,\s]+', value) for value in meta.get('robots', []))
            and row.get('content', {}).get('main_characters', 0) > 100
            and (not row.get('source') or bool(row.get('source_matches')) and all(x['exact_body'] for x in row['source_matches']))
            and (path != '/test/future-perfect/questions' or row.get('nested_endpoint_in_html') and row['content'].get('question_count', 0) > 0))


def main(label, baseline):
    if not re.fullmatch('[a-z0-9-]+', label):
        raise ValueError('Simple new label required')
    old = json.loads(Path(baseline).read_text(encoding='utf-8'))['sitemap']
    if not old['pass'] or not old['entries']:
        raise ValueError('A completed historical sitemap is required')
    OUT.mkdir(parents=True, exist_ok=True)
    file = OUT / (label + '-http.json')
    report = {'started_at': http.now(), 'base': http.BASE, 'rows': [], 'baseline': str(Path(baseline)),
              'policy': 'One GET per case, no cookie/auth/Referer/proxy/retry; explicit local alias destination only.'}
    with file.open('x', encoding='utf-8') as stream:
        json.dump(report, stream)
    def save():
        file.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
    def add(path, **kwargs):
        row, links = capture(path, **kwargs)
        report['rows'].append(row)
        save()
        print(json.dumps({k: row.get(k) for k in ['path', 'status', 'duration_ms', 'pass', 'error']}), flush=True)
        return row, links
    for path in ['/', '/theory', '/theory/future-perfect', '/theory/basic-grammar/sentence-types', '/test/future-perfect/questions']:
        add(path)
    add('/test/future-perfect/questions/questions', accept='application/json')
    _, links = add('/courses/english-grammar-theory')
    definitions = {name: json.loads((ROOT / source).read_text(encoding='utf-8')) for name, source in SOURCES.items()}
    for name, definition in definitions.items():
        theory = '/theory/' + definition['page']['category']['slug'] + '/' + definition['slug']
        add(theory, source=SOURCES[name])
        if name == 'OneOnes':
            course = course_mapping(links, definition['slug'])
            report['course_path'] = course
            add(course, canonical=theory, source=SOURCES[name])
    add('/theory/passive-voice/theory-passive-voice-formation-rules')
    alias, _ = add('/tests/cards', status=302)
    alias['pass'] &= alias.get('location') == http.BASE + '/catalog/tests-cards'
    add('/catalog/tests-cards')
    add('/courses/polyglot-english-a9', status=404)
    report['sitemap'] = http.capture_sitemap()
    report['sitemap_comparison'] = compare_entries(old['entries'], report['sitemap']['entries'])
    report['finished_at'] = http.now()
    report['pass'] = (all(row['pass'] for row in report['rows']) and report['sitemap']['pass']
                      and report['sitemap_comparison']['ordered_equal'] and report['sitemap_comparison']['lastmod_count'] == 0)
    save()
    print(json.dumps({'file': str(file), 'pass': report['pass'], 'sitemap': report['sitemap_comparison']}), flush=True)
    return report['pass']


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--label', required=True)
    parser.add_argument('--baseline')
    parser.add_argument('--reassess', help='Completed prior M9 JSON; offline, no HTTP')
    parser.add_argument('--recheck-timeout', action='store_true', help='Explicit single follow-up GET of the failed Future Perfect category')
    args = parser.parse_args()
    if args.reassess:
        if not re.fullmatch('[a-z0-9-]+', args.label):
            parser.error('Simple new label required')
        report = json.loads(Path(args.reassess).read_text(encoding='utf-8'))
        if not report.get('finished_at') or report.get('base') != http.BASE or len(report.get('rows', [])) != 14:
            parser.error('Completed bounded M9 set required')
        report['reassessed_from'] = str(Path(args.reassess))
        report['reassessment'] = 'Offline: development X-Robots-Tag suffices; meta robots may be absent. Original capture preserved.'
        new_requests = 0
        if args.recheck_timeout:
            failed = [r for r in report['rows'] if r.get('error')]
            if len(failed) != 1 or failed[0]['path'] != '/theory/future-perfect' or failed[0]['error'] != 'TimeoutError':
                parser.error('Only the one recorded category timeout may be rechecked')
            report['original_timeout'] = failed[0]
            replacement, _ = capture('/theory/future-perfect')
            report['rows'][report['rows'].index(failed[0])] = replacement
            report['explicit_followup'] = 'One new category GET only; no retry loop/cache reset/performance series.'
            new_requests = 1
        for row in report['rows']:
            row['original_pass'], row['pass'] = row['pass'], bool(reassess(row))
        report['pass'] = all(r['pass'] for r in report['rows']) and report['sitemap']['pass'] and report['sitemap_comparison']['ordered_equal'] and report['sitemap_comparison']['lastmod_count'] == 0
        target = OUT / (args.label + '-http.json')
        with target.open('x', encoding='utf-8') as stream:
            json.dump(report, stream, ensure_ascii=False, indent=2)
        print(json.dumps({'file': str(target), 'pass': report['pass'], 'new_requests': new_requests}))
        raise SystemExit(0 if report['pass'] else 1)
    if not args.baseline:
        parser.error('--baseline is required for live acceptance')
    if args.recheck_timeout:
        parser.error('--recheck-timeout requires --reassess')
    raise SystemExit(0 if main(args.label, args.baseline) else 1)
