"""Cookie-free local-only metadata inventory; no HTML, session or question persistence."""
import argparse
import concurrent.futures
import importlib.util
import json
import re
import urllib.parse
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / 'storage/app/seo-m5-local'
spec = importlib.util.spec_from_file_location('m4_http', Path(__file__).with_name('seo-m4-sitemap.py'))
http = importlib.util.module_from_spec(spec)
spec.loader.exec_module(http)


class Metadata(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.values = {k: [] for k in ('title', 'description', 'h1', 'canonical', 'robots', 'og:title', 'og:description', 'twitter:title', 'twitter:description')}
        self.active = None
        self.parts = []

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag in ('title', 'h1'):
            self.active, self.parts = tag, []
        if tag == 'meta':
            key = a.get('name', a.get('property', '')).lower()
            if key in self.values:
                self.values[key].append(a.get('content', ''))
        if tag == 'link' and 'canonical' in a.get('rel', '').split():
            self.values['canonical'].append(http.clean_location(a.get('href', '')))

    def handle_data(self, data):
        if self.active:
            self.parts.append(data)

    def handle_endtag(self, tag):
        if tag == self.active:
            self.values[tag].append(re.sub(r'\s+', ' ', ''.join(self.parts)).strip())
            self.active = None


def capture(path):
    row, body = http.request_local(path, 'text/html', limit=http.MAX_HTML)
    if row.get('status') == 200 and 'text/html' in row.get('content_type', '') and not row.get('error'):
        parser = Metadata()
        parser.feed(body.decode('utf-8', errors='strict'))
        row['metadata'] = parser.values
        row['lengths'] = {k: [len(v) for v in parser.values[k]] for k in ('title', 'description')}
    return row


def run(label, workers=2):
    if not re.fullmatch(r'[A-Za-z0-9_-]+', label) or workers not in (1, 2):
        raise ValueError('Simple unique label and at most two workers required')
    OUTPUT.mkdir(parents=True, exist_ok=True)
    target = OUTPUT / (label + '.json')
    report = {'started_at': http.now(), 'base': http.BASE, 'workers': workers, 'rows': []}
    with target.open('x', encoding='utf-8') as out:
        json.dump(report, out)
    report['sitemap'] = http.capture_sitemap()
    target.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
    if not report['sitemap']['pass']:
        return False
    paths = [urllib.parse.urlsplit(e['loc']).path for e in report['sitemap']['entries']]
    with concurrent.futures.ThreadPoolExecutor(max_workers=workers) as pool:
        for i, row in enumerate(pool.map(capture, paths), 1):
            report['rows'].append(row)
            target.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
            if i % 25 == 0 or not row.get('metadata'):
                print(json.dumps({'done': i, 'total': len(paths), 'path': row['path'], 'status': row.get('status'), 'error': row.get('error')}), flush=True)
    report['finished_at'] = http.now()
    report['pass'] = all(r.get('metadata') for r in report['rows'])
    target.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
    print(json.dumps({'total': len(paths), 'pass': report['pass']}), flush=True)
    return report['pass']


if __name__ == '__main__':
    p = argparse.ArgumentParser()
    p.add_argument('--label', required=True)
    p.add_argument('--workers', type=int, choices=(1, 2), default=2)
    a = p.parse_args()
    raise SystemExit(0 if run(a.label, a.workers) else 1)
