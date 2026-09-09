"""Bounded local-only GET acceptance, without JS, cookies, or persisted HTML."""
import argparse
import hashlib
import importlib.util
import json
import re
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / 'storage/app/seo-m8-local'
spec = importlib.util.spec_from_file_location('m5', Path(__file__).with_name('seo-m5-metadata.py'))
m5 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(m5)


class Blocks(HTMLParser):
    VOID = {'br', 'hr', 'img', 'input', 'meta', 'link', 'area', 'base', 'wbr', 'source', 'embed', 'col', 'param', 'track'}

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.stack, self.blocks, self.links = [], {}, []
        self.active = None

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'a' and a.get('href'):
            self.links.append(a['href'])
        block = a.get('id', '')
        if re.fullmatch(r'block-\d+', block):
            self.active = block
            self.blocks[block] = {'parts': [], 'tags': {}, 'raw_tags': False}
        if self.active:
            counts = self.blocks[self.active]['tags']
            counts[tag] = counts.get(tag, 0) + 1
        if tag not in self.VOID:
            self.stack.append((tag, block))

    def handle_endtag(self, tag):
        if not any(t == tag for t, _ in self.stack):
            return
        while self.stack:
            name, block = self.stack.pop()
            if block == self.active:
                self.active = None
            if name == tag:
                break

    def handle_data(self, value):
        if self.active:
            self.blocks[self.active]['parts'].append(value)
            self.blocks[self.active]['raw_tags'] |= bool(re.search(r'</?(?:p|strong|ul|li|em|div)\b', value))


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--label', required=True)
    args = parser.parse_args()
    if not re.fullmatch('[a-z0-9-]+', args.label):
        raise ValueError('Simple new label required')
    target = OUT / (args.label + '-http.json')
    with target.open('x', encoding='utf-8') as f:
        f.write('{}')
    db = json.loads((OUT / 'before-db.json').read_text(encoding='utf-8'))
    report = {'at': m5.http.now(), 'base': m5.http.BASE, 'rows': [], 'sitemap': m5.http.capture_sitemap()}
    row, body = m5.http.request_local('/courses/english-grammar-theory', 'text/html', limit=m5.http.MAX_HTML)
    navigation = Blocks()
    navigation.feed(body.decode('utf-8'))
    report['navigation'] = row
    cases = []
    for item in db['pages']:
        slug = item['page']['slug']
        matches = sorted(set(m5.http.urllib.parse.urlsplit(link).path for link in navigation.links
                             if '/lesson/' in link and link.endswith('/' + slug)))
        if len(matches) != 1:
            raise ValueError('Course navigation mapping missing/ambiguous: ' + slug)
        cases += [('/theory/zaimennyky-ta-vkazivni-slova/' + slug, item), (matches[0], item)]
    cases += [(p, None) for p in ['/theory/tenses/narrative-tenses', '/theory/conditionals/conditional-alternatives-and-nuance']]
    for path, item in cases:
        row, body = m5.http.request_local(path, 'text/html', limit=m5.http.MAX_HTML)
        html = body.decode('utf-8')
        meta, blocks = m5.Metadata(), Blocks()
        meta.feed(html)
        blocks.feed(html)
        row['metadata'] = meta.values
        row['built_assets'] = '/build/assets/' in html and '@vite/client' not in html
        row['blocks'] = {}
        for block_id, result in blocks.blocks.items():
            text = re.sub(r'\s+', ' ', ' '.join(result.pop('parts'))).strip()
            row['blocks'][block_id] = {**result, 'text': text, 'sha256': hashlib.sha256(text.encode()).hexdigest()}
        if item:
            row['source_matches'] = []
            source = ROOT / 'database/seeders' / (item['page']['seeder'].removeprefix('Database\\Seeders\\').replace('\\', '/') + '/definition.json')
            definition = json.loads(source.read_text(encoding='utf-8'))
            by_order = {int(b['sort_order']): b for b in item['blocks'] if b['locale'] == 'uk'}
            for order, block in enumerate(definition['page']['blocks'], 1):
                if 'layout' not in block:
                    continue
                current = row['blocks'].get('block-' + str(by_order[order]['id']), {})
                row['source_matches'].append({'id': by_order[order]['id'], 'uuid': by_order[order]['uuid'], 'order': order,
                    'source_body_in_html': bool(block.get('body')) and block['body'] in html,
                    'visible_text_characters': len(current.get('text', ''))})
        report['rows'].append(row)
        target.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
        print(json.dumps({'path': path, 'status': row.get('status'), 'error': row.get('error'), 'blocks': len(row['blocks'])}), flush=True)
    report['pass'] = all(r.get('status') == 200 and r.get('metadata') for r in report['rows']) and report['sitemap']['pass']
    target.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
    return report['pass']


if __name__ == '__main__':
    raise SystemExit(0 if main() else 1)
