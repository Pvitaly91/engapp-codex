"""Small M1 regression check on .loc only; production URLs are metadata, never fetched."""
import argparse
import datetime
import hashlib
import json
import re
import urllib.error
import urllib.request
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
BASE = 'http://gramlyze.loc'
EXPECTED_BANK = '47f7cf6c04b9ddad174749eacab6aa5aa6863d1f80438e49c2f6811e0e2cf101'

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None

class Metadata(HTMLParser):
    def __init__(self):
        super().__init__()
        self.canonical, self.robots, self.text = [], [], []
        self.skip = 0

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag in ('script', 'style', 'noscript'): self.skip += 1
        if tag == 'link' and a.get('rel') == 'canonical': self.canonical.append(a.get('href'))
        if tag == 'meta' and a.get('name') == 'robots': self.robots.append(a.get('content'))

    def handle_endtag(self, tag):
        if tag in ('script', 'style', 'noscript'): self.skip = max(0, self.skip - 1)

    def handle_data(self, data):
        if not self.skip: self.text.append(data)

def stable(value):
    if isinstance(value, dict): return {k: stable(v) for k, v in sorted(value.items())}
    if isinstance(value, list):
        return sorted([stable(v) for v in value], key=lambda v: json.dumps(v, sort_keys=True, ensure_ascii=False))
    return value

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--label', default=datetime.datetime.now().strftime('%Y%m%d-%H%M%S'))
    args = parser.parse_args()
    if not re.fullmatch(r'[a-zA-Z0-9_-]+', args.label): parser.error('Use a simple filename label')
    rows = []
    checks = [
        ('/test/future-perfect/questions', 'text/html', 200),
        ('/test/future-perfect/questions?source=theory', 'text/html', 302),
        ('/test/future-perfect/questions', 'application/json', 404),
        ('/test/future-perfect/questions/questions?mode=saved-test-js-v2', 'application/json', 200),
        ('/theory/basic-grammar/parts-of-speech', 'text/html', 200),
        ('/courses/english-grammar-theory/lesson/basic-grammar/parts-of-speech', 'text/html', 200),
        ('/theory/tenses/present-perfect/present-perfect-forms', 'text/html', 200),
        ('/theory/imennyky-artykli-ta-kilkist/collective-nouns', 'text/html', 200),
        ('/theory/passive-voice/theory-passive-voice-formation-rules', 'text/html', 200),
    ]
    for path, accept, expected in checks:
        row = {'path': path, 'accept': accept, 'time': datetime.datetime.now(datetime.timezone.utc).isoformat()}
        try:
            # New opener: no CookieJar, authentication, Referer, proxy or redirects.
            opener = urllib.request.build_opener(NoRedirect(), urllib.request.ProxyHandler({}))
            request = urllib.request.Request(BASE + path, headers={'Accept': accept})
            try: response = opener.open(request, timeout=45)
            except urllib.error.HTTPError as error: response = error
            with response:
                body = response.read().decode('utf-8')
                row.update(status=response.code, content_type=response.headers.get('Content-Type'),
                           x_robots_tag=response.headers.get('X-Robots-Tag'), site_mode=response.headers.get('X-Site-Mode'),
                           location=response.headers.get('Location'))
            assert row['status'] == expected, 'Unexpected HTTP status'
            if expected == 302:
                assert row['location'] == BASE + '/test/future-perfect/questions', 'Unexpected source-query cleanup redirect'
            if expected == 200:
                assert row['site_mode'] == 'development', 'Local development mode missing'
                assert 'noindex' in (row['x_robots_tag'] or ''), 'Local/technical noindex missing'
            if expected == 200 and 'html' in row['content_type']:
                doc = Metadata()
                doc.feed(body)
                visible = ' '.join(doc.text)
                row.update(canonical=doc.canonical, robots=doc.robots,
                           literal_inline_tags=bool(re.search(r'</?(?:strong|span)\b[^>]*>', visible)),
                           debug_text_present=any(v in visible for v in ['Page_V3 folder unseed block.', 'Page Folder Unseed Targets']))
                assert len(doc.canonical) == 1, 'Canonical must be unique'
                canonical_path = path.split('?')[0]
                if path.startswith('/courses/'):
                    canonical_path = '/theory/basic-grammar/parts-of-speech'
                assert doc.canonical[0] == 'https://gramlyze.com' + canonical_path, 'Canonical regression'
                assert not row['literal_inline_tags'], 'Visible raw inline HTML'
                assert not row['debug_text_present'], 'Fixture text leaked'
            if expected == 200 and 'json' in row['content_type']:
                questions = json.loads(body)['questions']
                row['questions_count'] = len(questions)
                row['bank_sha256'] = hashlib.sha256(json.dumps(stable(questions), ensure_ascii=False, sort_keys=True).encode()).hexdigest()
                assert len(questions) == 84 and row['bank_sha256'] == EXPECTED_BANK, 'Authored bank changed'
            row['pass'] = True
        except Exception as error:
            row.update({'pass': False, 'error': type(error).__name__ + ': ' + str(error)})
        rows.append(row)
        print(json.dumps(row, ensure_ascii=False))
    output = ROOT / f'storage/app/seo-m2-local/seo-smoke-{args.label}.json'
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(rows, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    raise SystemExit(0 if all(row.get('pass') for row in rows) else 1)

if __name__ == '__main__': main()
