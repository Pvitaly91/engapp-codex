"""Four representative local GETs, with M5 metadata equivalence; no HTML/cookies saved."""
import argparse
import importlib.util
import json
from pathlib import Path
import re

REPO = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('m5', Path(__file__).with_name('seo-m5-metadata.py'))
m5 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(m5)
http = m5.http


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--label', required=True)
    args = parser.parse_args()
    if not re.fullmatch(r'[A-Za-z0-9_-]+', args.label):
        parser.error('Simple unique label required')
    baseline_dir = REPO / 'storage/app/seo-m5-local'
    baseline = json.loads((baseline_dir / 'm5-accepted.json').read_text(encoding='utf-8'))['rows']
    supplement = json.loads((baseline_dir / 'm5-courses-final.json').read_text(encoding='utf-8'))
    by_path = {row['path']: row for row in baseline + supplement}
    report = {'started_at': http.now(), 'conditions': 'Fresh cookie-free local GETs; no retries or redirects; M5 saved metadata reference.', 'rows': []}
    destination = REPO / 'storage/app/seo-m4-2-local' / (args.label + '.json')
    with destination.open('x', encoding='utf-8') as handle:
        json.dump(report, handle)
    endpoint = '/test/future-perfect/questions/questions'
    for path in ['/theory/tenses/present-perfect-continuous/present-perfect-continuous-questions',
                 '/test/future-perfect/questions', '/courses/english-grammar-theory', endpoint]:
        is_json = path == endpoint
        row, body = http.request_local(path, 'application/json' if is_json else 'text/html', limit=http.MAX_HTML)
        if is_json:
            payload = json.loads(body) if row.get('status') == 200 else {}
            row['question_count'] = len(payload.get('questions', []))
            row['pass'] = (row.get('status') == 200 and 'application/json' in row.get('content_type', '')
                           and row['question_count'] > 0 and 'noindex' in row.get('x_robots_tag', ''))
        else:
            text = body.decode('utf-8', errors='strict')
            metadata = m5.Metadata()
            metadata.feed(text)
            visible = http.HtmlEvidence()
            visible.feed(text)
            row['metadata'] = metadata.values
            row['html'] = visible.evidence()
            previous = by_path.get(path, {})
            row['same_m5_metadata'] = metadata.values == previous.get('metadata')
            row['same_m5_robots'] = row.get('x_robots_tag') == previous.get('x_robots_tag')
            row['pass'] = (row.get('status') == 200 and row['same_m5_metadata'] and row['same_m5_robots']
                           and row.get('site_mode') == 'development' and 'noindex' in row.get('x_robots_tag', '')
                           and row['html']['h1_count'] == 1 and row['html']['main_characters'] > 100
                           and not row['html']['coming_soon_heading'])
            if path == '/test/future-perfect/questions':
                row['real_endpoint_in_html'] = endpoint in text.replace('\\/', '/')
                row['pass'] = row['pass'] and row['real_endpoint_in_html']
        report['rows'].append(row)
        destination.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
        print(json.dumps({k: row.get(k) for k in ['path', 'status', 'duration_ms', 'pass', 'same_m5_metadata', 'question_count']}, ensure_ascii=False), flush=True)
    report['pass'] = all(row['pass'] for row in report['rows'])
    report['finished_at'] = http.now()
    destination.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
    raise SystemExit(0 if report['pass'] else 1)


if __name__ == '__main__':
    main()
