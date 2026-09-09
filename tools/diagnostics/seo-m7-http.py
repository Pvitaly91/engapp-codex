"""Focused, cookie-free M7 GETs. Reuses M5 parsing; never crawls sitemap URLs."""
import argparse
import concurrent.futures
import hashlib
import importlib.util
import json
import re
import sys
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / 'storage/app/seo-m7-local'
spec = importlib.util.spec_from_file_location('m5', Path(__file__).with_name('seo-m5-metadata.py'))
m5 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(m5)
CONTROLS = ['/theory/maibutni-formy/future-perfect/future-perfect-forms',
            '/test/future-perfect/questions', '/theory/future-perfect', '/courses/english-grammar-theory']


class LessonText(HTMLParser):
    """Only explanatory block text; never persist full HTML, scripts or practice answers."""
    VOID = {'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'}

    def __init__(self, blocks):
        super().__init__(convert_charrefs=True)
        self.ids = {'block-' + str(b['id']) for b in blocks}
        self.stack, self.parts = [], []
        self.selected, self.skip = 0, 0
        self.main_depth, self.hero_seen = 0, False

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        selected = a.get('id') in self.ids
        if 'data-theory-main' in a:
            self.main_depth = len(self.stack) + 1
        if self.main_depth and tag == 'section' and len(self.stack) == self.main_depth:
            if not self.hero_seen or 'xl:grid-cols-3' in a.get('class', '').split():
                selected = True
            self.hero_seen = True
        skip = tag in ('script', 'style', 'template', 'noscript')
        if tag not in self.VOID:
            self.stack.append((tag, selected, skip))
            self.selected += selected
            self.skip += skip

    def handle_endtag(self, tag):
        if not any(item[0] == tag for item in self.stack):
            return
        while self.stack:
            name, selected, skip = self.stack.pop()
            self.selected -= selected
            self.skip -= skip
            if name == tag:
                break
        if len(self.stack) < self.main_depth:
            self.main_depth = 0

    def handle_data(self, value):
        if self.selected and not self.skip and value.strip():
            self.parts.append(re.sub(r'\s+', ' ', value).strip())


def capture(candidate):
    row, body = m5.http.request_local(candidate['path'], 'text/html', limit=m5.http.MAX_HTML)
    text = ''
    if row.get('status') == 200 and 'text/html' in (row.get('content_type') or '') and not row.get('error'):
        decoded = body.decode('utf-8', errors='strict')
        parser = m5.Metadata()
        parser.feed(decoded)
        row['metadata'] = parser.values
        content = LessonText(candidate.get('blocks', []))
        content.feed(decoded)
        text = '\n'.join(content.parts)
        row['lesson_text_sha256'] = hashlib.sha256(text.encode()).hexdigest()
        row['lesson_text_characters'] = len(text)
        row['built_assets'] = '/build/assets/' in decoded and '@vite/client' not in decoded
    return row, text


def main():
    sys.stdout.reconfigure(encoding='utf-8')
    parser = argparse.ArgumentParser()
    parser.add_argument('--label', required=True)
    parser.add_argument('--start', type=int, default=1)
    parser.add_argument('--end', type=int)
    parser.add_argument('--controls', action='store_true')
    parser.add_argument('--read-content', action='store_true')
    parser.add_argument('--sitemap', action='store_true')
    args = parser.parse_args()
    if not re.fullmatch(r'[a-zA-Z0-9_-]+', args.label):
        raise ValueError('Simple unique label required')
    target = OUTPUT / (args.label + '.json')
    with target.open('x', encoding='utf-8') as out:
        json.dump({'started_at': m5.http.now()}, out)
    candidates = json.loads((OUTPUT / 'context.json').read_text(encoding='utf-8'))
    selected = candidates[args.start - 1:args.end]
    if args.controls:
        selected = [{'path': path} for path in CONTROLS]
    report = {'base': m5.http.BASE, 'workers': 2, 'rows': []}
    if args.sitemap:
        report['sitemap'] = m5.http.capture_sitemap()
    with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
        for row, text in pool.map(capture, selected):
            report['rows'].append(row)
            target.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
            print(json.dumps({'path': row['path'], 'status': row.get('status'), 'error': row.get('error'),
                              'characters': row.get('lesson_text_characters')}, ensure_ascii=False), flush=True)
            if args.read_content:
                print(text, flush=True)
    report['pass'] = all(r.get('metadata') for r in report['rows']) and report.get('sitemap', {'pass': True})['pass']
    target.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
    return report['pass']


if __name__ == '__main__':
    raise SystemExit(0 if main() else 1)
