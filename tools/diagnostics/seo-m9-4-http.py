"""Exactly seven fresh, cookie-free local GETs for the M9.4 PHP-handler acceptance.

The tool deliberately reuses M9's local-only HTTP primitives.  It never follows
the production canonical origin, never makes an outgoing HTTPS request itself,
and writes only a private, ignored evidence file.
"""
from __future__ import annotations

import argparse
import hashlib
import importlib.util
import json
import re
import xml.etree.ElementTree as ET
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / 'storage/app/seo-m9-4-local'
THEORY = '/theory/basic-grammar/sentence-types'
QUESTIONS = '/test/future-perfect/questions'
COURSE = '/courses/english-grammar-theory'
NOT_FOUND = '/__seo-m9-4-curl-handler-acceptance-not-found__'

# This is the complete live request plan.  Keep it as data so tests can prove
# that later edits do not accidentally turn this into a broader crawl.
REQUEST_PLAN = (
    ('home', '/', 'html'),
    ('theory', THEORY, 'html'),
    ('questions-html', QUESTIONS, 'html'),
    ('questions-json', QUESTIONS + '/questions', 'json'),
    ('course', COURSE, 'html'),
    ('sitemap', '/sitemap.xml', 'sitemap'),
    ('not-found', NOT_FOUND, 'not-found'),
)


def module(name: str, filename: str):
    spec = importlib.util.spec_from_file_location(name, Path(__file__).with_name(filename))
    result = importlib.util.module_from_spec(spec)
    assert spec.loader is not None
    spec.loader.exec_module(result)
    return result


m9 = module('seo_m9_4_parent', 'seo-m9-http.py')
http, Metadata = m9.http, m9.Metadata


class StableContent(http.HtmlEvidence):
    """Digest visible main text without scripts, session tokens or full HTML."""

    def __init__(self):
        super().__init__()
        self.parts = []

    def handle_data(self, data):
        if self.main_depth and not self.skip:
            self.parts.append(data)
        super().handle_data(data)

    def evidence(self):
        text = re.sub(r'\s+', ' ', ' '.join(self.parts)).strip()
        return {**super().evidence(), 'main_text_sha256': hashlib.sha256(text.encode('utf-8')).hexdigest()}


def noindex(value):
    return 'noindex' in (value or '').lower()


def has_meta_index(values):
    return any('index' in re.split(r'[,\s]+', value.lower()) for value in values)


def parse_html(body: bytes):
    """Return bounded metadata/evidence only; never retain full page HTML."""
    text = body.decode('utf-8', errors='strict')
    metadata, content = Metadata(), StableContent()
    for parser in (metadata, content):
        parser.feed(text)
        parser.close()
    return metadata.values, content.evidence(), text


def html_case(path: str, *, expected_status: int = 200):
    row, body = http.request_local(path, 'text/html', limit=http.MAX_HTML)
    row['pass'] = row.get('status') == expected_status and not row.get('error')
    row['final_url'] = http.local_url(path)
    row['expected_status'] = expected_status
    if not row['pass']:
        return row
    if 'text/html' not in (row.get('content_type') or '').lower():
        row['pass'] = False
        row['error'] = 'expected-text-html'
        return row
    try:
        metadata, content, text = parse_html(body)
    except (UnicodeError, ValueError, ET.ParseError) as error:
        row['pass'] = False
        row['error'] = 'html-evidence-' + type(error).__name__
        return row
    row['metadata'], row['content'] = metadata, content
    row['built_assets'] = ('/build/assets/' in text and '@vite/client' not in text
                           and 'cdn.tailwindcss.com' not in text and 'unpkg.com/alpinejs' not in text)
    if expected_status == 404:
        row['pass'] &= not metadata['canonical'] and noindex(row.get('x_robots_tag'))
        return row
    row['pass'] &= (
        all(len(metadata[key]) == 1 and metadata[key][0] for key in ('title', 'description', 'h1'))
        and metadata['canonical'] == [http.SEO_ORIGIN + path]
        and not content['canonical_invalid']
        and not has_meta_index(metadata['robots'])
        and noindex(row.get('x_robots_tag'))
        and content['main_characters'] > 100
        and row['built_assets']
    )
    if path == QUESTIONS:
        row['pass'] &= (content.get('question_count') or 0) > 0
        row['question_endpoint_in_html'] = QUESTIONS + '/questions' in text.replace('\\/', '/')
        row['pass'] &= row['question_endpoint_in_html']
    if path == COURSE:
        row['pass'] &= content.get('learning_links', 0) > 0
        links = m9.m8.Blocks()
        links.feed(text)
        links.close()
        try:
            row['course_path'] = m9.course_mapping(links.links, 'one-ones')
        except ValueError:
            row['pass'] = False
            row['error'] = 'missing-native-course-mapping'
    return row


def json_case():
    path = QUESTIONS + '/questions'
    row, body = http.request_local(path, 'application/json', limit=http.MAX_HTML)
    row['final_url'] = http.local_url(path)
    row['expected_status'] = 200
    row['pass'] = (row.get('status') == 200 and not row.get('error')
                   and 'application/json' in (row.get('content_type') or '').lower()
                   and noindex(row.get('x_robots_tag')))
    if not row['pass']:
        return row
    try:
        value = json.loads(body)
        row['question_count'] = len(value.get('questions', []))
        row['question_content_sha256'] = hashlib.sha256(
            json.dumps(value.get('questions', []), ensure_ascii=False, sort_keys=True,
                       separators=(',', ':')).encode('utf-8')).hexdigest()
        # Fresh guest requests deliberately choose question_variants text.
        # Compare all remaining fields, including IDs, answers, hints and levels;
        # retain the complete raw-content digest separately without claiming it
        # must match between independently randomized guest requests.
        variant_fields = {'question', 'presentation', 'reorder_answer', 'reorder_tokens', 'reorder_source_question'}
        stable = [{key: item for key, item in question.items() if key not in variant_fields}
                  for question in value.get('questions', [])]
        row['question_stable_sha256'] = hashlib.sha256(
            json.dumps(stable, ensure_ascii=False, sort_keys=True,
                       separators=(',', ':')).encode('utf-8')).hexdigest()
    except (UnicodeError, ValueError, TypeError, AttributeError) as error:
        row['pass'] = False
        row['error'] = 'json-evidence-' + type(error).__name__
        return row
    row['pass'] &= row['question_count'] > 0
    return row


def sitemap_case():
    path = '/sitemap.xml'
    row, body = http.request_local(path, 'application/xml', limit=http.MAX_XML)
    row['final_url'] = http.local_url(path)
    row['expected_status'] = 200
    row['entries'], row['sitemap_errors'] = [], []
    row['pass'] = (row.get('status') == 200 and not row.get('error')
                   and (row.get('content_type') or '').split(';')[0].strip().lower() == 'application/xml')
    if not row['pass']:
        return row
    try:
        row['entries'] = http.parse_sitemap(body)
        row['pass'] &= bool(row['entries'])
    except (UnicodeError, ValueError, ET.ParseError) as error:
        row['pass'] = False
        row['sitemap_errors'].append(type(error).__name__ + ': invalid sitemap structure')
    return row


def load_baseline(path):
    source = Path(path).resolve()
    value = json.loads(source.read_text(encoding='utf-8'))
    sitemap = value.get('sitemap', {})
    entries = sitemap.get('entries')
    if (value.get('schema') != 'gramlyze-m9-4-http-v1' or not value.get('pass')
            or value.get('base') != http.BASE or not value.get('finished_at')
            or not sitemap.get('pass') or not isinstance(entries, list) or not entries
            or [row.get('path') for row in value.get('rows', [])] != [path for _, path, _ in REQUEST_PLAN]):
        raise ValueError('A completed passing fresh M9.4 baseline is required')
    return source, value


def compare_sitemaps(before, after):
    comparison = m9.compare_entries(before, after)
    comparison['pass'] = bool(before) and comparison['ordered_equal']
    return comparison


def compare_pages(before, after):
    fields = ('status', 'content_type', 'x_robots_tag', 'metadata', 'content',
              'question_count', 'question_stable_sha256', 'course_path')
    old = {row['path']: row for row in before}
    changes = [{'path': row['path'], 'fields': [key for key in fields if old[row['path']].get(key) != row.get(key)]}
               for row in after if row['path'] != '/sitemap.xml']
    changes = [row for row in changes if row['fields']]
    raw_equal = all(old[row['path']].get('question_content_sha256') == row.get('question_content_sha256')
                    for row in after if row['path'] == QUESTIONS + '/questions')
    return {'pass': not changes, 'changed': changes, 'compared_fields': list(fields),
            'question_raw_content_equal': raw_equal,
            'question_comparison_policy': 'All JSON question fields except randomized question text and derived presentation/reorder fields; raw content digests retained.'}


def run(label: str, baseline: str | None = None):
    if not re.fullmatch(r'[a-z0-9-]+', label):
        raise ValueError('Simple new label required')
    baseline_file, baseline_report = load_baseline(baseline) if baseline else (None, None)
    OUT.mkdir(parents=True, exist_ok=True)
    output = OUT / (label + '-http.json')
    report = {
        'schema': 'gramlyze-m9-4-http-v1',
        'started_at': http.now(),
        'base': http.BASE,
        'baseline': str(baseline_file) if baseline_file else None,
        'mode': 'comparison' if baseline_report else 'capture-baseline',
        'request_plan': [{'name': name, 'path': path, 'kind': kind} for name, path, kind in REQUEST_PLAN],
        'policy': 'Exactly seven fresh local GETs; no cookies, authentication, Referer, redirects, retries, proxy, or production navigation.',
        'rows': [],
    }
    with output.open('x', encoding='utf-8') as handle:
        json.dump(report, handle, ensure_ascii=False, indent=2)

    def save():
        output.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')

    for name, path, kind in REQUEST_PLAN:
        if kind == 'html':
            row = html_case(path)
        elif kind == 'not-found':
            row = html_case(path, expected_status=404)
        elif kind == 'json':
            row = json_case()
        else:
            row = sitemap_case()
            report['sitemap'] = {'pass': row['pass'], 'entries': row['entries'], 'errors': row['sitemap_errors']}
        row['name'] = name
        if name == 'course':
            report['course_path'] = row.get('course_path')
        report['rows'].append(row)
        save()
        print(json.dumps({key: row.get(key) for key in ('name', 'path', 'status', 'duration_ms', 'pass', 'error')}), flush=True)

    report['request_count'] = len(report['rows'])
    if baseline_report:
        report['sitemap_comparison'] = compare_sitemaps(baseline_report['sitemap']['entries'], report['sitemap']['entries'])
        report['page_comparison'] = compare_pages(baseline_report['rows'], report['rows'])
    report['finished_at'] = http.now()
    report['pass'] = (report['request_count'] == len(REQUEST_PLAN) == 7
                      and all(row.get('pass') for row in report['rows'])
                      and (not baseline_report or (report['sitemap_comparison']['pass'] and report['page_comparison']['pass'])))
    save()
    print(json.dumps({'file': str(output), 'pass': report['pass'], 'request_count': report['request_count'],
                      'sitemap_count': len(report['sitemap']['entries']),
                      'sitemap': report.get('sitemap_comparison'), 'pages': report.get('page_comparison')}), flush=True)
    return report['pass']


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--label', required=True)
    mode = parser.add_mutually_exclusive_group(required=True)
    mode.add_argument('--baseline', help='Completed passing fresh M9.4 HTTP baseline JSON')
    mode.add_argument('--capture-baseline', action='store_true', help='Capture the seven planned GETs before handler changes')
    args = parser.parse_args()
    raise SystemExit(0 if run(args.label, args.baseline) else 1)
