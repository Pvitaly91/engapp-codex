"""Run selected PHPUnit files without touching local DB, caches, views or sessions."""
import argparse
import base64
import datetime
import json
import os
import subprocess
import uuid
from pathlib import Path

REPO=Path(__file__).resolve().parents[2]
M1=[
    'tests/Unit/TheoryInlineHtmlTest.php', 'tests/Feature/TheoryInlineHtmlRenderingTest.php',
    'tests/Unit/PassiveVoiceDebugContentRepairTest.php', 'tests/Feature/PageV3UnseedFolderCommandTest.php',
    'tests/Feature/ResolvedLearningPageSeoTest.php', 'tests/Feature/SeoRobotsTest.php',
    'tests/Feature/CanonicalUrlTest.php', 'tests/Feature/SiteModeTest.php',
    'tests/Feature/TheoryCanonicalLessonUrlTest.php',
]
M2=[
    'tests/Unit/ThreadSafeEnvironmentTest.php', 'tests/Feature/SavedTestJsStateTest.php',
    'tests/Unit/SavedTestJsStateTest.php', 'tests/Unit/SavedTestJsStateSynonymsTest.php',
]
def main():
    parser=argparse.ArgumentParser()
    parser.add_argument('--php',default=os.environ.get('PHP_BINARY','php'))
    parser.add_argument('--label',default='tests')
    parser.add_argument('--include-m2',action='store_true')
    parser.add_argument('tests',nargs='*')
    args=parser.parse_args()
    runtime=REPO/'storage/app/seo-m2-local'/('test-runtime-'+uuid.uuid4().hex)
    for part in ['app/public','framework/views','framework/sessions','framework/cache/data','framework/testing','logs']:
        (runtime/part).mkdir(parents=True,exist_ok=False)
    env=os.environ.copy()
    safe={
        'APP_ENV':'testing','APP_DEBUG':'false','APP_KEY':'base64:'+base64.b64encode(os.urandom(32)).decode(),
        'DB_CONNECTION':'sqlite','DB_DATABASE':':memory:','DATABASE_URL':'','DB_URL':'',
        'CACHE_DRIVER':'array','SESSION_DRIVER':'array','QUEUE_CONNECTION':'sync','MAIL_MAILER':'array',
        'LOG_CHANNEL':'stderr','PULSE_ENABLED':'false','TELESCOPE_ENABLED':'false',
        'APP_CONFIG_CACHE':(runtime/'absent-config.php').relative_to(REPO).as_posix(),
        'APP_ROUTES_CACHE':(runtime/'absent-routes.php').relative_to(REPO).as_posix(),
        'APP_SERVICES_CACHE':(runtime/'services.php').relative_to(REPO).as_posix(),
        'APP_PACKAGES_CACHE':(runtime/'packages.php').relative_to(REPO).as_posix(),
        'LARAVEL_STORAGE_PATH':str(runtime), 'FILESYSTEM_DISK':'local',
        'VIEW_COMPILED_PATH':str(runtime/'framework/views'),
    }
    env.update(safe)
    preflight='''require "vendor/autoload.php";
    $app=require "bootstrap/app.php"; $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
    $db=Illuminate\\Support\\Facades\\DB::connection();
    if(!app()->environment("testing")||$db->getDriverName()!=="sqlite"||$db->getDatabaseName()!==":memory:"||config("cache.default")!=="array"||config("session.driver")!=="array")throw new RuntimeException("Unsafe test configuration");
    foreach($db->select("PRAGMA database_list") as $row)if($row->name==="main"&&$row->file!=="")throw new RuntimeException("File database refused");
    $root=realpath(getenv("LARAVEL_STORAGE_PATH"));
    foreach([storage_path(),config("filesystems.disks.local.root"),config("filesystems.disks.public.root"),config("view.compiled"),config("session.files")] as $path) {
        $resolved=realpath($path);
        if(!$root||!$resolved||($resolved!==$root&&!str_starts_with($resolved,$root.DIRECTORY_SEPARATOR)))throw new RuntimeException("Shared filesystem refused");
    }
    echo json_encode(["environment"=>app()->environment(),"driver"=>$db->getDriverName(),"database"=>$db->getDatabaseName(),"cache"=>config("cache.default"),"session"=>config("session.driver"),"key_present"=>is_string(config("app.key"))&&config("app.key")!==""]);'''
    check=subprocess.run([args.php,'-r',preflight],cwd=REPO,env=env,capture_output=True,encoding='utf-8')
    if check.returncode: raise RuntimeError('Preflight failed: '+check.stdout+check.stderr)
    command=[args.php,'vendor/bin/phpunit',*(args.tests or (M1+(M2 if args.include_m2 else []))),'--do-not-cache-result','--colors=never']
    started=datetime.datetime.now(datetime.timezone.utc).isoformat()
    result=subprocess.run(command,cwd=REPO,env=env,capture_output=True,encoding='utf-8')
    record={'started_at_utc':started,'finished_at_utc':datetime.datetime.now(datetime.timezone.utc).isoformat(),
        'command':command,'environment':{k:v for k,v in safe.items() if k!='APP_KEY'},'preflight':json.loads(check.stdout),
        'exit_code':result.returncode,'stdout':result.stdout,'stderr':result.stderr}
    (runtime.parent/f'{args.label}-result.json').write_text(json.dumps(record,indent=2)+'\n',encoding='utf-8')
    print(result.stdout);print(result.stderr)
    raise SystemExit(result.returncode)
if __name__=='__main__': main()
