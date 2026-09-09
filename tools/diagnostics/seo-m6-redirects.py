"""Bounded M6 guest GET chains. Only gramlyze.loc; cookies stay in memory."""
import argparse
from http.cookiejar import CookieJar
import importlib.util
import json
import re
import time
import urllib.error
import urllib.parse
import urllib.request
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / 'storage/app/seo-m6-local'
BASE = 'http://gramlyze.loc'


def module(name, file):
    spec = importlib.util.spec_from_file_location(name, Path(__file__).with_name(file))
    value = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(value)
    return value


http = module('m6_http', 'seo-m4-sitemap.py')
meta = module('m6_meta', 'seo-m5-metadata.py')


def local(value):
    url = urllib.parse.urljoin(BASE + '/', value)
    p = urllib.parse.urlsplit(url)
    if (p.scheme != 'http' or p.netloc != 'gramlyze.loc' or p.username or p.password
            or '\\' in urllib.parse.unquote(url) or any(ord(c) < 32 for c in url)):
        raise ValueError('nonlocal-or-unsafe-url')
    return urllib.parse.urlunsplit((p.scheme, p.netloc, p.path or '/', p.query, ''))


class Links(HTMLParser):
    def __init__(self):
        super().__init__()
        self.links = set()

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'a' and a.get('href'):
            p = urllib.parse.urlsplit(a['href'])
            # Store only public navigation paths, never tokens or arbitrary queries.
            if (not p.netloc or p.netloc == 'gramlyze.loc') and p.path.startswith(('/theory', '/test', '/catalog', '/courses', '/en/', '/pl/')):
                self.links.add(p.path)


def opener():
    return urllib.request.build_opener(urllib.request.ProxyHandler({}), http.NoRedirect(),
                                      urllib.request.HTTPCookieProcessor(CookieJar()))


def get(url, client, accept='text/html'):
    url = local(url)
    row = {'url': url, 'started_at': http.now(), 'method': 'GET', 'accept': accept}
    start = time.monotonic()
    try:
        req = urllib.request.Request(url, headers={'Accept': accept, 'User-Agent': 'Gramlyze-M6-local/1'})
        try:
            response = client.open(req, timeout=45)
        except urllib.error.HTTPError as e:
            response = e
        with response:
            body = response.read(http.MAX_HTML + 1)
            row.update(status=response.code, location=response.headers.get('Location'),
                       content_type=response.headers.get('Content-Type', ''),
                       x_robots_tag=response.headers.get('X-Robots-Tag'), body_bytes=len(body))
        if len(body) > http.MAX_HTML:
            raise ValueError('response-size-limit')
        if 'text/html' in row['content_type']:
            text = body.decode('utf-8', errors='strict')
            metadata, content, links = meta.Metadata(), http.HtmlEvidence(), Links()
            for parser in (metadata, content, links):
                parser.feed(text)
                parser.close()
            row.update(metadata=metadata.values, content=content.evidence(), links=sorted(links.links))
        elif 'application/json' in row['content_type']:
            value = json.loads(body)
            row['question_count'] = len(value.get('questions', [])) if isinstance(value, dict) else None
    except Exception as e:
        # Request URLs are a fixed public/synthetic allowlist. No headers/cookies are persisted.
        row['error'] = type(e).__name__ + ': ' + str(e)
    row['duration_ms'] = round((time.monotonic() - start) * 1000, 2)
    return row


def chain(path, accept='text/html'):
    result = {'path': path, 'environment': 'local HTTP', 'steps': [], 'retries': 0}
    client, seen, url = opener(), set(), local(path)
    for _ in range(5):
        if url in seen:
            result['error'] = 'redirect-loop'
            break
        seen.add(url)
        row = get(url, client, accept)
        result['steps'].append(row)
        if row.get('error'):
            result['error'] = row['error']
            break
        if row['status'] not in (301, 302, 303, 307, 308):
            break
        try:
            url = local(urllib.parse.urljoin(url, row.get('location') or ''))
        except ValueError as e:
            result['error'] = str(e)
            break
    else:
        result['error'] = 'five-step-limit'
    if not result.get('error'):
        # A second independent guest must not inherit launch/admin/theory state.
        result['fresh_final'] = get(result['steps'][-1]['url'], opener(), accept)
    return result


PATHS = [
    '/catalog-tests/cards', '/tests/cards', '/catalog/tests-cards',
    '/courses/polyglot-english-a1', '/courses/sentence-builder-english-a1',
    '/test/polyglot-to-be-a1/step/compose', '/test/sentence-builder-to-be-a1/step/compose',
    '/en/catalog-tests/cards', '/pl/tests/cards',
    '/en/courses/polyglot-english-a1', '/pl/courses/polyglot-english-a1',
    '/theory/future-simple/future-simple-forms',
    '/theory/maibutni-formy/future-simple/future-simple-forms',
    '/en/theory/future-simple', '/pl/theory/future-simple',
    '/test/future-perfect/questions', '/test/future-perfect/questions?source=theory',
    '/test/future-perfect/questions/step', '/theory/future-perfect', '/courses',
    '/courses/english-grammar-theory', '/m6-definitely-unknown-page',
]

EXTRA_PATHS = [
    '/courses/polyglot-english-a9',
    '/tests/cards?levels%5B0%5D=A1&levels%5B1%5D=B2&tags%5B0%5D=%D1%82%D0%B5%D0%BC%D0%B0%20%D0%B7%20%D0%BF%D1%80%D0%BE%D0%B1%D1%96%D0%BB%D0%BE%D0%BC&tags%5B1%5D=50%25%20%2B%20%2F&mode=manual&launch=lesson&next=%2F%2Fattacker.invalid',
]


def run(label, compare=None, extra=False):
    if not re.fullmatch(r'[A-Za-z0-9_-]+', label):
        raise ValueError('Simple unique label required')
    OUTPUT.mkdir(parents=True, exist_ok=True)
    target = OUTPUT / (label + '.json')
    report = {'started_at': http.now(), 'rows': [], 'pass': False,
              'conditions': 'No auth/Referer/proxy/retries. New in-memory cookie jar per chain and final-target check.'}
    with target.open('x', encoding='utf-8') as f:
        json.dump(report, f)
    paths = (EXTRA_PATHS if extra else PATHS).copy()
    for path in paths:
        row = chain(path)
        report['rows'].append(row)
        target.write_text(json.dumps(report, indent=2, ensure_ascii=False), encoding='utf-8')
        print(json.dumps({'path': path, 'statuses': [r.get('status') for r in row['steps']],
                          'locations': [r.get('location') for r in row['steps']], 'error': row.get('error')}), flush=True)
        if path == '/courses/english-grammar-theory':
            lessons = [p for p in row['steps'][-1].get('links', [])
                       if p.startswith('/courses/english-grammar-theory/lesson/') and not p.endswith('/test')]
            if lessons:
                paths.append(lessons[0])
    if not extra:
        report['rows'].append(chain('/test/future-perfect/questions/questions', 'application/json'))
    if compare:
        if Path(compare).name != compare:
            raise ValueError('Reference must be in private evidence')
        baseline = json.loads((OUTPUT / compare).read_text(encoding='utf-8'))
        old = {r['path']: r['steps'][-1].get('metadata') for r in baseline['rows']}
        report['metadata_changed'] = [r['path'] for r in report['rows']
                                      if old.get(r['path']) != r['steps'][-1].get('metadata')]
    report['pass'] = all(not r.get('error') and not r.get('fresh_final', {}).get('error') for r in report['rows'])
    report['finished_at'] = http.now()
    target.write_text(json.dumps(report, indent=2, ensure_ascii=False), encoding='utf-8')
    return report['pass']


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--label', required=True)
    parser.add_argument('--compare')
    parser.add_argument('--extra', action='store_true')
    args = parser.parse_args()
    raise SystemExit(0 if run(args.label, args.compare, args.extra) else 1)
