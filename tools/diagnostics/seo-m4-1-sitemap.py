"""Bounded direct local sitemap samples, with immutable XML and ordered metadata."""
import argparse
import concurrent.futures
import importlib.util
import json
import re
import urllib.parse
from html import unescape
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / 'storage/app/seo-m4-1-local'
spec = importlib.util.spec_from_file_location('m4_http', Path(__file__).with_name('seo-m4-sitemap.py'))
http = importlib.util.module_from_spec(spec)
spec.loader.exec_module(http)


def capture(index, label):
    row, body = http.request_local('/sitemap.xml', 'application/xml')
    row['index'] = index
    row['entries'] = None
    row['pass'] = False
    if body:
        filename = f'{label}-{index}.xml'
        with (OUTPUT / filename).open('xb') as handle:
            handle.write(body)  # Public sitemap only; never HTML/cookies.
        row['xml_file'] = filename
    if not row.get('error') and row.get('status') == 200:
        try:
            row['entries'] = http.parse_sitemap(body)
            row['pass'] = (row.get('content_type', '').split(';')[0].strip() == 'application/xml'
                           and row.get('site_mode') == 'development'
                           and 'noindex' in row.get('x_robots_tag', '').lower())
        except Exception as error:
            row['parse_error'] = type(error).__name__
    return row


def run(label, requests, workers=1, compare=None):
    if not re.fullmatch(r'[a-zA-Z0-9_-]+', label):
        raise ValueError('Unique simple label required')
    if workers not in (1, 2) or requests not in (1, 2, 3, 5) or (workers == 2 and requests != 2):
        raise ValueError('Only sequential samples or one pair are permitted')
    OUTPUT.mkdir(parents=True, exist_ok=True)
    target = OUTPUT / f'{label}.json'
    report = {'schema': 'gramlyze-m41-sitemap-v1', 'started_at': http.now(), 'base': http.BASE,
              'workers': workers, 'requested': requests, 'samples': [],
              'conditions': 'New cookie-free GET; no redirects/retries/result cache; OS/DB caches not cleared.'}
    reference = None
    if compare:
        if Path(compare).name != compare:
            raise ValueError('Reference must be a filename within private evidence')
        previous = json.loads((OUTPUT / compare).read_text(encoding='utf-8'))
        if not previous.get('pass'):
            raise ValueError('A successful reference is required')
        reference = previous['samples'][0]['entries']
        report['reference'] = compare
    with target.open('x', encoding='utf-8') as handle:
        json.dump(report, handle, indent=2)
    with concurrent.futures.ThreadPoolExecutor(max_workers=workers) as executor:
        # Sequential mode submits one at a time, not an unbounded queue.
        batches = [range(1, requests + 1)] if workers == 2 else [[i] for i in range(1, requests + 1)]
        for batch in batches:
            for row in executor.map(lambda i: capture(i, label), batch):
                report['samples'].append(row)
                target.write_text(json.dumps(report, indent=2), encoding='utf-8')
                print(json.dumps({k: row.get(k) for k in ('index', 'status', 'duration_ms', 'body_bytes', 'body_sha256', 'pass', 'error')}), flush=True)
    entries = reference if reference is not None else report['samples'][0]['entries']
    report['same_ordered_entries'] = entries is not None and all(row['entries'] == entries for row in report['samples'])
    report['pass'] = all(row['pass'] for row in report['samples']) and report['same_ordered_entries']
    report['finished_at'] = http.now()
    target.write_text(json.dumps(report, indent=2), encoding='utf-8')
    return report


def representative_acceptance(label):
    if not re.fullmatch(r'[a-zA-Z0-9_-]+', label):
        raise ValueError('Unique simple label required')
    OUTPUT.mkdir(parents=True, exist_ok=True)
    target = OUTPUT / f'{label}.json'
    report = {'started_at': http.now(), 'base': http.BASE, 'rows': [], 'pass': False}
    with target.open('x', encoding='utf-8') as handle:
        json.dump(report, handle)

    def document(path, canonical=None):
        row, body = http.request_local(path, 'text/html', limit=http.MAX_HTML)
        parser = http.HtmlEvidence()
        parser.feed(body.decode('utf-8', errors='strict'))
        parser.close()
        data = parser.evidence()
        row['html'] = data
        row['pass'] = (row.get('status') == 200 and not row.get('error')
                       and row.get('site_mode') == 'development' and 'noindex' in row.get('x_robots_tag', '')
                       and data['canonical'] == [http.SEO_ORIGIN + (canonical or path)]
                       and not data['canonical_invalid'] and data['h1_count'] == 1
                       and data['main_characters'] > 100 and not data['coming_soon_heading'])
        report['rows'].append(row)
        target.write_text(json.dumps(report, indent=2), encoding='utf-8')
        return body

    for path in ['/test/future-perfect/forms', '/test/future-perfect/questions', '/test/basic-grammar/sentence-types',
                 '/courses', '/courses/theory-driven']:
        document(path)
    home = document('/courses/english-grammar-theory')
    # Discover a real lesson from the course navigation, not a guessed URL.
    links = re.findall(rb'href=["\']([^"\']*/courses/english-grammar-theory/lesson/[^"\']+)["\']', home)
    if not links:
        raise ValueError('Course lesson navigation missing')
    link = urllib.parse.urlsplit(unescape(links[0].decode('utf-8')))
    if link.netloc and f'{link.scheme}://{link.netloc}' != http.BASE:
        raise ValueError('Refusing nonlocal course navigation')
    lesson = link.path
    theory = lesson.replace('/courses/english-grammar-theory/lesson/', '/theory/', 1)
    document(theory)
    document(lesson, canonical=theory)
    row, body = http.request_local('/test/future-perfect/questions/questions', 'application/json', limit=http.MAX_HTML)
    payload = json.loads(body) if row.get('status') == 200 else {}
    row['question_count'] = len(payload.get('questions', []))
    row['pass'] = (row.get('status') == 200 and 'application/json' in row.get('content_type', '')
                   and row['question_count'] > 0 and 'noindex' in row.get('x_robots_tag', ''))
    report['rows'].append(row)
    report['pass'] = all(row['pass'] for row in report['rows'])
    report['finished_at'] = http.now()
    target.write_text(json.dumps(report, indent=2), encoding='utf-8')
    print(json.dumps({'pass': report['pass'], 'paths': [{k: row.get(k) for k in ('path', 'status', 'pass', 'question_count')} for row in report['rows']]}))
    return report


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--label', required=True)
    parser.add_argument('--requests', type=int)
    parser.add_argument('--representative', action='store_true')
    parser.add_argument('--workers', type=int, default=1)
    parser.add_argument('--compare')
    args = parser.parse_args()
    if args.representative and (args.requests is not None or args.compare or args.workers != 1):
        parser.error('Representative acceptance does not accept sampling arguments')
    result = representative_acceptance(args.label) if args.representative else run(args.label, args.requests, args.workers, args.compare)
    raise SystemExit(0 if result['pass'] else 1)
