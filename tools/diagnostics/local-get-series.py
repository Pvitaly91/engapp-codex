"""Local-only GET series. No redirects, cookies, secrets, full bodies or external hosts."""
import argparse
import concurrent.futures
import datetime
import json
import time
import urllib.error
import urllib.request
from pathlib import Path

REPO=Path(__file__).resolve().parents[2]
PATHS=[
    '/theory/common-mistakes/countable-vs-uncountable-nouns-common-mistakes',
    '/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions',
    '/test/future-perfect/questions',
]
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self,*args,**kwargs):
        return None
def main():
    parser=argparse.ArgumentParser()
    parser.add_argument('--phase',required=True)
    parser.add_argument('--count',type=int,default=30,help='Requests per path in each pass')
    parser.add_argument('--workers',type=int,choices=[1,2,3],default=3)
    parser.add_argument('--output',type=Path,default=REPO/'storage/app/seo-m2-local')
    args=parser.parse_args()
    args.output.mkdir(parents=True,exist_ok=True)
    control=REPO/'storage/app/seo-m2-local/observer-control.json'
    nonce=json.loads(control.read_text())['nonce'] if control.exists() else None
    rows=[]
    def get(job):
        index,path=job
        uid=f'{args.phase}-{args.workers}-{index}'
        headers={'Accept':'text/html','User-Agent':'GramlyzeLocalStability/1.0'}
        if nonce: headers.update({'X-Gramlyze-Probe':nonce,'X-Gramlyze-Probe-Id':uid})
        start=time.monotonic()
        row={'id':uid,'url':'http://gramlyze.loc'+path,'at':datetime.datetime.now(datetime.timezone.utc).isoformat()}
        try:
            opener=urllib.request.build_opener(NoRedirect(),urllib.request.ProxyHandler({}))
            try: response=opener.open(urllib.request.Request(row['url'],headers=headers),timeout=45)
            except urllib.error.HTTPError as error: response=error
            with response:
                body=response.read()
                row.update(status=response.code,content_type=response.headers.get('Content-Type'),
                    x_robots_tag=response.headers.get('X-Robots-Tag'),body_bytes=len(body))
        except Exception as error: row['error']=type(error).__name__+': '+str(error)
        row['seconds']=round(time.monotonic()-start,3)
        return row
    jobs=list(enumerate(PATHS*args.count))
    with concurrent.futures.ThreadPoolExecutor(max_workers=args.workers) as pool:
        for row in pool.map(get,jobs):
            rows.append(row)
            if row.get('status')!=200: print(json.dumps(row),flush=True)
    result={'phase':args.phase,'workers':args.workers,'count_per_path':args.count,'attempts':rows,
        'errors':sum(row.get('status')!=200 for row in rows)}
    (args.output/f'get-{args.phase}-{args.workers}.json').write_text(json.dumps(result,indent=2)+'\n',encoding='utf-8')
    print(json.dumps({'phase':args.phase,'workers':args.workers,'requests':len(rows),'errors':result['errors']}))
if __name__=='__main__': main()
