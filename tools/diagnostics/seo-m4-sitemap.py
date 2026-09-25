"""Read-only HTTP acceptance on gramlyze.loc; sitemap .com URLs are metadata only."""
from __future__ import annotations

import argparse
import concurrent.futures
import datetime as dt
import hashlib
import http.cookiejar
import json
import re
import time
import urllib.error
import urllib.parse
import urllib.request
import xml.etree.ElementTree as ET
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / 'storage/app/seo-m4-local'
BASE = 'http://gramlyze.loc'
SEO_ORIGIN = 'https://gramlyze.com'
NS = 'http://www.sitemaps.org/schemas/sitemap/0.9'
MAX_XML = 50 * 1024 * 1024
MAX_HTML = 16 * 1024 * 1024


def now():
    return dt.datetime.now(dt.timezone.utc).isoformat()


def digest(value: bytes):
    return hashlib.sha256(value).hexdigest()


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


def local_url(path):
    if not isinstance(path, str) or not path.startswith('/') or path.startswith('//'):
        raise ValueError('Expected an absolute local path')
    parts = urllib.parse.urlsplit(path)
    decoded = urllib.parse.unquote(parts.path)
    if (parts.scheme or parts.netloc or parts.query or parts.fragment or '\\' in decoded
            or any(segment in ('.', '..') for segment in decoded.split('/'))
            or any(ord(c) < 32 for c in decoded)):
        raise ValueError('Unsafe local path')
    return BASE + path


def clean_location(value):
    if not value:
        return None
    try:
        parts = urllib.parse.urlsplit(value)
        # Never retain userinfo, query, fragment, cookies, or arbitrary headers.
        host = parts.hostname or ''
        origin = f'{parts.scheme}://{host}' if parts.scheme else ''
        if parts.port:
            origin += f':{parts.port}'
        return origin + parts.path
    except ValueError:
        return '[invalid-location]'


def request_local(path, accept, *, opener=None, limit=MAX_XML, context='fresh-guest'):
    url = local_url(path)
    request = urllib.request.Request(url, headers={'Accept': accept, 'User-Agent': 'Gramlyze-M4-local-acceptance/1'}, method='GET')
    # No environment proxy, CookieJar, authentication, Referer, redirect or retry.
    opener = opener or urllib.request.build_opener(urllib.request.ProxyHandler({}), NoRedirect())
    row = {'path': path, 'started_at': now(), 'accept': accept, 'method': 'GET', 'context': context,
           'redirects_followed': False, 'retries': 0}
    start = time.monotonic()
    try:
        try:
            response = opener.open(request, timeout=45)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            body = response.read(limit + 1)
            row.update(status=response.code, content_type=response.headers.get('Content-Type'),
                       x_robots_tag=response.headers.get('X-Robots-Tag'), site_mode=response.headers.get('X-Site-Mode'),
                       location=clean_location(response.headers.get('Location')), body_bytes=len(body), body_sha256=digest(body))
        if len(body) > limit:
            row['error'] = 'response-size-limit'
            body = b''
    except Exception as error:
        row['error'] = type(error).__name__
        body = b''
    row.update(finished_at=now(), duration_ms=round((time.monotonic() - start) * 1000, 3))
    return row, body


def parse_sitemap(body):
    if body.startswith(b'\xef\xbb\xbf') or not body.startswith(b'<?xml'):
        raise ValueError('XML must start with its declaration, without BOM/debug prefix')
    text = body.decode('utf-8', errors='strict')
    declaration = re.match(r'<\?xml\s+[^?]*\?>', text)
    if not declaration or not re.search(r'\bencoding\s*=\s*([\"\'])UTF-8\1', declaration[0], re.IGNORECASE):
        raise ValueError('Expected explicit UTF-8 XML encoding')
    if '<!DOCTYPE' in text.upper() or '<!ENTITY' in text.upper():
        raise ValueError('DTD/entities are not allowed')
    root = ET.fromstring(text)
    if root.tag != f'{{{NS}}}urlset':
        raise ValueError('Expected sitemap namespace/urlset')
    rows, seen = [], set()
    for node in root:
        if node.tag != f'{{{NS}}}url':
            raise ValueError('Unexpected sitemap element')
        locations = node.findall(f'{{{NS}}}loc')
        dates = node.findall(f'{{{NS}}}lastmod')
        if len(locations) != 1 or len(dates) > 1:
            raise ValueError('Expected one loc and at most one lastmod')
        loc = locations[0].text or ''
        parts = urllib.parse.urlsplit(loc)
        if f'{parts.scheme}://{parts.netloc}' != SEO_ORIGIN or not parts.path.startswith('/') or '?' in loc or '#' in loc:
            raise ValueError('Invalid production-origin loc metadata')
        if not loc.isascii() or re.search(r'%(?![a-fA-F0-9]{2})', loc) or any(c.isspace() for c in loc) or '\\' in loc:
            raise ValueError('Invalid URL percent encoding')
        local_url(parts.path)  # Reject unsafe encoded paths before constructing a crawl plan.
        if loc in seen:
            raise ValueError('Duplicate sitemap loc')
        seen.add(loc)
        lastmod = dates[0].text if dates else None
        if lastmod is not None:
            if not re.fullmatch(r'\d{4}-\d{2}-\d{2}(?:T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2}))?', lastmod):
                raise ValueError('Invalid lastmod syntax')
            dt.datetime.fromisoformat(lastmod.replace('Z', '+00:00'))
        rows.append({'loc': loc, 'lastmod': lastmod})
    if not rows or len(rows) > 50000:
        raise ValueError('Invalid urlset size')
    return rows


def capture_sitemap(*, opener=None, context='fresh-guest'):
    request, body = request_local('/sitemap.xml', 'application/xml', opener=opener, context=context)
    result = {'request': request, 'entries': [], 'errors': []}
    if request.get('error') or request.get('status') != 200:
        result['errors'].append('sitemap-http-failed')
    if (request.get('content_type') or '').split(';')[0].strip().lower() != 'application/xml':
        result['errors'].append('sitemap-content-type')
    try:
        result['entries'] = parse_sitemap(body)
    except (ValueError, UnicodeError, ET.ParseError) as error:
        result['errors'].append(type(error).__name__ + ': invalid sitemap structure')
    result['pass'] = not result['errors']
    return result


class HtmlEvidence(HTMLParser):
    """Parse in memory; expose only metadata, counts and booleans, never text/JSON."""
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.canonical = []
        self.canonical_invalid = False
        self.robots = []
        self.h1 = 0
        self.main_depth = 0
        self.main_characters = 0
        self.skip = []
        self.script_parts = []
        self.question_count = None
        self.question_cards = 0
        self.course_links = 0
        self.learning_links = 0
        self.coming_soon_heading = False
        self.in_heading = False
        self.heading_parts = []

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'main':
            self.main_depth += 1
        if tag == 'link' and 'canonical' in (a.get('rel') or '').lower().split():
            value = a.get('href') or ''
            parts = urllib.parse.urlsplit(value)
            self.canonical_invalid |= bool(parts.query or parts.fragment or parts.username or parts.password)
            self.canonical.append(clean_location(value))
        if tag == 'meta' and (a.get('name') or '').lower() in ('robots', 'googlebot'):
            # Persist recognized directives only, not an arbitrary server string.
            self.robots.extend(token for token in re.split(r'[,\s]+', (a.get('content') or '').lower())
                               if token in ('index', 'noindex', 'follow', 'nofollow', 'none', 'all'))
        if tag == 'h1':
            self.h1 += 1
            self.in_heading = True
            self.heading_parts = []
        if tag == 'article' and 'data-idx' in a:
            self.question_cards += 1
        if tag == 'a' and self.main_depth:
            try:
                target = urllib.parse.urlsplit(a.get('href') or '')
                local_or_metadata = not target.netloc or f'{target.scheme}://{target.netloc}' in (BASE, SEO_ORIGIN)
                if local_or_metadata and target.path.startswith('/courses/'):
                    self.course_links += 1
                if local_or_metadata and target.path.startswith(('/courses/', '/theory/', '/test/')):
                    self.learning_links += 1
            except ValueError:
                pass
        if tag in ('script', 'style', 'noscript', 'template'):
            self.skip.append(tag)
            if tag == 'script':
                self.script_parts = []

    def handle_endtag(self, tag):
        if tag == 'script' and self.script_parts:
            script = ''.join(self.script_parts)
            match = re.search(r'window\.__INITIAL_JS_TEST_QUESTIONS__\s*=\s*', script)
            if match:
                try:
                    value, _ = json.JSONDecoder().raw_decode(script[match.end():].lstrip())
                    if isinstance(value, list):
                        self.question_count = len(value)
                except (ValueError, TypeError):
                    pass
            self.script_parts = []
        if self.skip and tag == self.skip[-1]:
            self.skip.pop()
        if tag == 'main':
            self.main_depth = max(0, self.main_depth - 1)
        if tag == 'h1':
            heading = ' '.join(self.heading_parts).lower()
            self.coming_soon_heading |= bool(re.search(r'coming\s+soon|незабаром|скоро\s+буде', heading))
            self.in_heading = False
            self.heading_parts = []

    def handle_data(self, data):
        if self.skip:
            if self.skip[-1] == 'script':
                self.script_parts.append(data)
            return
        if self.main_depth:
            self.main_characters += len(data.strip())
        if self.in_heading:
            self.heading_parts.append(data)

    def evidence(self):
        return {'canonical': self.canonical, 'canonical_invalid': self.canonical_invalid,
                'robots': sorted(set(self.robots)), 'h1_count': self.h1, 'main_characters': self.main_characters,
                'question_count': self.question_count, 'question_cards': self.question_cards,
                'course_links': self.course_links, 'learning_links': self.learning_links,
                'coming_soon_heading': self.coming_soon_heading}


def classify_path(path):
    if path == '/courses':
        return 'course-catalog'
    if path.startswith('/courses/'):
        return 'course'
    if path.startswith('/test/'):
        return 'test'
    if path == '/':
        return 'home'
    if path == '/theory':
        return 'theory-catalog'
    return 'theory'


def inspect_html(path, *, group=None):
    row, body = request_local(path, 'text/html', limit=MAX_HTML)
    row['group'] = group or classify_path(path)
    errors = []
    if row.get('error') or row.get('status') != 200:
        errors.append('guest-document-not-200')
    if (row.get('content_type') or '').split(';')[0].lower().strip() != 'text/html':
        errors.append('document-not-html')
    if row.get('status') == 200 and not row.get('error'):
        try:
            parser = HtmlEvidence()
            parser.feed(body.decode('utf-8', errors='strict'))
            parser.close()
            row['html'] = data = parser.evidence()
            if data['canonical_invalid'] or data['canonical'] != [SEO_ORIGIN + path]:
                errors.append('canonical-mismatch')
            if row.get('site_mode') != 'development' or 'noindex' not in (row.get('x_robots_tag') or '').lower():
                errors.append('expected-local-development-noindex')
            if data['h1_count'] != 1 or data['main_characters'] < 100 or data['coming_soon_heading']:
                errors.append('missing-substantive-learning-html')
            if row['group'] == 'test' and not ((data['question_count'] or 0) > 0 or data['question_cards'] > 0):
                errors.append('missing-nonempty-question-evidence')
            if row['group'] in ('course', 'course-catalog') and data['learning_links'] == 0:
                errors.append('missing-course-learning-links')
        except Exception as error:
            errors.append(type(error).__name__ + ': html-evidence-invalid')
    row['errors'] = errors
    row['pass'] = not errors
    return row


def compare_entries(before, after):
    old = {entry['loc']: entry.get('lastmod') for entry in before}
    new = {entry['loc']: entry.get('lastmod') for entry in after}
    added = [entry for entry in after if entry['loc'] not in old]
    removed = [entry for entry in before if entry['loc'] not in new]
    retained = [loc for loc in old if loc in new]
    groups = {}
    for entry in added:
        group = classify_path(urllib.parse.urlsplit(entry['loc']).path)
        groups[group] = groups.get(group, 0) + 1
    return {'before_count': len(before), 'after_count': len(after), 'retained_count': len(retained),
            'added': added, 'removed': removed, 'added_by_group': groups,
            'lastmod_changes': [{'loc': loc, 'before': old[loc], 'after': new[loc]} for loc in retained if old[loc] != new[loc]]}


def old_representatives(entries):
    paths = {urllib.parse.urlsplit(entry['loc']).path for entry in entries}
    requested = ['/', '/theory', '/theory/basic-grammar/sentence-types',
                 '/theory/tenses/present-perfect/present-perfect-forms',
                 '/theory/imennyky-artykli-ta-kilkist/collective-nouns',
                 '/theory/passive-voice/theory-passive-voice-formation-rules']
    leaf = next((path for path in sorted(paths) if path.startswith('/theory/') and path.count('/') == 2), None)
    if leaf:
        requested.append(leaf)
    return list(dict.fromkeys(path for path in requested if path in paths))


def run_comparison(report, baseline, save, workers):
    if workers not in (1, 2):
        raise ValueError('Only one or two concurrent requests are allowed')
    if baseline.get('schema') != 'gramlyze-m4-sitemap-baseline-v1' or not baseline.get('passed'):
        raise ValueError('Expected a successful preserved M4 baseline')
    report['comparison_available'] = False

    def halt(phase):
        # A failed download is unknown sitemap content, not an empty urlset.
        # Do not queue later contexts/crawls after a failure or manufacture removals.
        report['halted_at'] = phase
        report['limitations'] = ['Sitemap comparison and crawl were not completed because a prerequisite request failed. No URL-removal conclusion is available.']
        save()
        return False

    report['after'] = capture_sitemap()
    save()
    if not report['after']['pass']:
        return halt('initial-sitemap')
    report['fresh_repeat'] = capture_sitemap()
    save()
    if not report['fresh_repeat']['pass']:
        return halt('fresh-repeat-sitemap')
    # This separate ordinary guest session is kept in memory only. All crawl
    # requests below still construct fresh cookie-free openers independently.
    jar = http.cookiejar.CookieJar()
    session = urllib.request.build_opener(urllib.request.ProxyHandler({}), NoRedirect(), urllib.request.HTTPCookieProcessor(jar))
    visit, _ = request_local('/theory', 'text/html', opener=session, limit=MAX_HTML, context='private-memory-session')
    report['session_visit'] = visit
    save()
    if visit.get('status') != 200 or visit.get('error') or len(jar) == 0:
        return halt('ordinary-guest-session')
    report['session_sitemap'] = capture_sitemap(opener=session, context='private-memory-session')
    report['session_cookie_count'] = len(jar)
    save()
    if not report['session_sitemap']['pass']:
        return halt('session-sitemap')
    entries = report['after']['entries']
    report['comparison'] = compare_entries(baseline['sitemap']['entries'], entries)
    report['comparison_available'] = True
    report['determinism'] = {
        'fresh_exact_loc_lastmod_order': entries == report['fresh_repeat']['entries'],
        'session_exact_loc_lastmod_order': entries == report['session_sitemap']['entries'],
        'session_loc_set_equal': {x['loc'] for x in entries} == {x['loc'] for x in report['session_sitemap']['entries']},
    }
    added = [urllib.parse.urlsplit(entry['loc']).path for entry in report['comparison']['added']]
    old = old_representatives(baseline['sitemap']['entries'])
    report['crawl_plan'] = {'workers': workers, 'new_paths': added, 'old_representatives': old,
                            'policy': 'one fresh guest GET per path; Accept text/html; no Cookie/Authorization/Referer; no redirects/retries'}
    report['crawl'] = []
    save()
    planned = [(path, 'new') for path in added] + [(path, 'old-representative') for path in old if path not in added]
    with concurrent.futures.ThreadPoolExecutor(max_workers=workers) as executor:
        futures = {executor.submit(inspect_html, path): (index, kind) for index, (path, kind) in enumerate(planned)}
        for future in concurrent.futures.as_completed(futures):
            index, kind = futures[future]
            row = future.result()
            row.update(index=index, selection=kind)
            report['crawl'].append(row)
            report['crawl'].sort(key=lambda row: row['index'])
            save()  # Every first failure is durable; there is no retry branch.
            print(json.dumps({'path': row['path'], 'status': row.get('status'), 'pass': row['pass'], 'errors': row['errors']}), flush=True)
    report['limitations'] = [
        'Local HTTP only. Development noindex is expected; indexability in production requires separate isolated production-profile tests.',
        'New guests and a private cookie session do not prove empty server-cache independence; no working cache is cleared.',
        'HTML/question/link counts are acceptance signals, not semantic verification of every learning item or a full historical crawl.',
        'Every first attempt is retained. A later invocation uses a new label and cannot overwrite this evidence.',
    ]
    return (all(report[key]['pass'] for key in ('after', 'fresh_repeat', 'session_sitemap'))
            and visit.get('status') == 200 and not visit.get('error') and len(jar) > 0 and all(report['determinism'].values())
            and not report['comparison']['removed'] and all(row['pass'] for row in report['crawl']))


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--label', required=True)
    parser.add_argument('--baseline', action='store_true')
    parser.add_argument('--compare', help='Preserved baseline filename inside storage/app/seo-m4-local')
    parser.add_argument('--workers', type=int, choices=(1, 2), default=2)
    args = parser.parse_args()
    if not re.fullmatch(r'[A-Za-z0-9_-]+', args.label):
        parser.error('Use a simple unique evidence label')
    if args.baseline == bool(args.compare):
        parser.error('Select exactly one of --baseline or --compare FILE')
    OUTPUT.mkdir(parents=True, exist_ok=True)
    output = OUTPUT / f'{args.label}.json'
    # Claim a fresh label before HTTP; never overwrite an earlier failed attempt.
    with output.open('x', encoding='utf-8') as handle:
        report = {'schema': 'gramlyze-m4-sitemap-baseline-v1' if args.baseline else 'gramlyze-m4-sitemap-acceptance-v1', 'started_at': now(), 'base': BASE,
                  'production_origin_metadata_only': SEO_ORIGIN, 'status': 'started'}
        handle.write(json.dumps(report, indent=2) + '\n')
    save = lambda: output.write_text(json.dumps(report, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    try:
        if args.baseline:
            report['sitemap'] = capture_sitemap()
            passed = report['sitemap']['pass']
        else:
            source = (OUTPUT / args.compare).resolve()
            if source.parent != OUTPUT.resolve() or source == output.resolve():
                raise ValueError('Baseline must be a separate file inside the evidence directory')
            report['baseline_file'] = source.name
            report['baseline_sha256'] = digest(source.read_bytes())
            passed = run_comparison(report, json.loads(source.read_text(encoding='utf-8')), save, args.workers)
        report.update(finished_at=now(), status='finished', passed=passed)
    except Exception as error:
        report.update(finished_at=now(), status='failed', passed=False, error=type(error).__name__)
    save()
    print(json.dumps({'output': str(output), 'passed': report['passed'],
                      'urls': len(report.get('sitemap', report.get('after', {})).get('entries', [])),
                      'comparison_available': report.get('comparison_available'), 'halted_at': report.get('halted_at'),
                      'errors': report.get('error')}, ensure_ascii=False))
    return 0 if report['passed'] else 1


if __name__ == '__main__':
    raise SystemExit(main())
