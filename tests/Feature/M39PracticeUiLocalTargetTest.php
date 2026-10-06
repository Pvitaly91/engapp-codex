<?php

namespace Tests\Feature;

use App\Services\M39LocalTargetGuard;
use App\Services\M39PracticeUiPatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class M39PracticeUiLocalTargetTest extends TestCase
{
    public function test_ui_patch_reuses_exact_existing_m39_physical_guard():void
    {
        $method=new \ReflectionMethod(M39PracticeUiPatch::class,'localGuard');
        $instance=new M39PracticeUiPatch(DB::connection(),database_path(),storage_path());
        self::assertSame(M39LocalTargetGuard::class,$method->invoke($instance));
        self::assertSame(['body'],(new \ReflectionMethod(M39PracticeUiPatch::class,'allowedUpdateFields'))->invoke($instance));
        self::assertSame(0,(new \ReflectionMethod(M39PracticeUiPatch::class,'expectedInsertCount'))->invoke($instance));
    }
    public static function forbidden():array{return M39LocalTargetGuardTest::forbiddenRequests();}
    #[DataProvider('forbidden')]
    public function test_ui_proof_never_accepts_head_foreign_or_forwarded_requests(string $method,string $host='gramlyze.loc',string $peer='127.0.0.1',string $scheme='http',?string $header=null):void
    {
        $nonce=str_repeat('a',32);$server=['REMOTE_ADDR'=>$peer,'HTTP_HOST'=>$host];if($header!==null)$server[$header]='forbidden';
        $request=Request::create($scheme.'://'.$host.'/api/_local/m39-target-'.$nonce,$method,server:$server);
        self::assertFalse(M39LocalTargetGuard::allowsProofRequest($request,$nonce,M39LocalTargetGuard::ROOT,M39LocalTargetGuard::ROOT.'/public'));
    }
    public function test_ui_guard_rejects_fixture_as_working_database():void
    {
        $this->expectException(RuntimeException::class);
        (new M39LocalTargetGuard)->verify(DB::connection(),'gramlyze.loc',storage_path('app/seo-m39-local'),null,['port'=>3306]);
    }
    public function test_ui_guard_rejects_wrong_private_directory_without_query():void
    {
        $this->expectException(RuntimeException::class);
        (new M39LocalTargetGuard)->verify(DB::connection(),'gramlyze.loc',storage_path('app/seo-m39-practice-ui'),null,[]);
    }
}
