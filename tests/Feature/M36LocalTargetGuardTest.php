<?php

namespace Tests\Feature;

use App\Services\M36LocalTargetGuard;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class M36LocalTargetGuardTest extends TestCase
{
    public function test_public_identity_returns_only_safe_allowlisted_fields(): void
    {
        config(['app.key'=>'never-return-this-app-key']);
        $db=\Mockery::mock(\Illuminate\Database\Connection::class);
        $db->shouldReceive('selectOne')->once()->with('SELECT DATABASE() AS db, @@port AS port')->andReturn((object)['db'=>'fixture','port'=>3306]);
        $db->shouldReceive('getDriverName')->once()->andReturn('mysql');
        $db->shouldReceive('getConfig')->once()->with('host')->andReturn('localhost');
        $identity=M36LocalTargetGuard::publicIdentity($db,base_path('public'));
        self::assertSame(['environment','site_mode','application_root','document_root','db_driver','db_host','db_port','database'],array_keys($identity));
        self::assertStringNotContainsString('never-return-this-app-key',json_encode($identity));
        self::assertSame('development',$identity['site_mode']);
    }
    public function test_m36_inherits_physical_windows_vhost_mysql_and_runtime_guards(): void
    {
        $guard=new M36LocalTargetGuard;
        $guard->assertEvidence(M11LocalTargetGuardTest::evidence(),'d:/dev/htdocs/gramlyze.loc',200,3306);
        $proof=['target'=>'gramlyze.loc','nonce'=>str_repeat('a',32),'sha256'=>hash('sha256','local-db-config')];
        $guard->assertRuntimeProof($proof,$proof);
        $this->expectException(RuntimeException::class);
        $guard->assertRuntimeProof($proof,array_replace($proof,['sha256'=>hash('sha256','other-db')]));
    }
    public function test_m36_rejects_remote_evidence(): void
    {
        $this->expectException(RuntimeException::class);
        (new M36LocalTargetGuard)->assertEvidence(array_replace(M11LocalTargetGuardTest::evidence(),['addresses'=>['192.168.0.104']]),'d:/dev/htdocs/gramlyze.loc',200,3306);
    }
    public function test_m36_never_accepts_isolated_fixture_as_working_database(): void
    {
        $this->expectException(RuntimeException::class);
        (new M36LocalTargetGuard)->verify(DB::connection(),'gramlyze.loc',storage_path('app/seo-m36-local'),null,['port'=>3306]);
    }
    public function test_m36_never_accepts_production_domain_optin(): void
    {
        $this->expectException(RuntimeException::class);
        (new M36LocalTargetGuard)->verify(DB::connection(),'gramlyze.com',storage_path(),null,[]);
    }

    public static function forbiddenRequests(): array
    {
        return [['HEAD'], ['POST'], ['PUT'], ['GET', 'gramlyze.com'], ['GET', 'gramlyze.ub'],
            ['GET', 'gramlyze.loc', '192.168.0.104'], ['GET', 'gramlyze.loc', '127.0.0.1', 'https'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_FORWARDED'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_X_FORWARDED_FOR'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_X_FORWARDED_HOST'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_X_REAL_IP'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_AUTHORIZATION'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_COOKIE']];
    }

    #[DataProvider('forbiddenRequests')]
    public function test_temporary_route_policy_explicitly_rejects_head_and_foreign_requests(string $method, string $host = 'gramlyze.loc', string $peer = '127.0.0.1', string $scheme = 'http', ?string $header = null): void
    {
        $nonce = str_repeat('a', 32);
        $server = ['REMOTE_ADDR' => $peer, 'HTTP_HOST' => $host];
        if ($header !== null) { $server[$header] = 'forbidden'; }
        $request = \Illuminate\Http\Request::create($scheme.'://'.$host.'/api/_local/m36-target-'.$nonce, $method, server: $server);
        self::assertFalse(M36LocalTargetGuard::allowsProofRequest($request, $nonce, M36LocalTargetGuard::ROOT, M36LocalTargetGuard::ROOT.'/public'));
    }

    public function test_temporary_route_policy_accepts_only_exact_loopback_application_document_and_nonce(): void
    {
        $nonce = str_repeat('a', 32);
        $request = \Illuminate\Http\Request::create('http://gramlyze.loc/api/_local/m36-target-'.$nonce, 'GET', server: ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_HOST' => 'gramlyze.loc']);
        self::assertTrue(M36LocalTargetGuard::allowsProofRequest($request, $nonce, M36LocalTargetGuard::ROOT, M36LocalTargetGuard::ROOT.'/public'));
        self::assertFalse(M36LocalTargetGuard::allowsProofRequest($request, str_repeat('b', 32), M36LocalTargetGuard::ROOT, M36LocalTargetGuard::ROOT.'/public'));
        self::assertFalse(M36LocalTargetGuard::allowsProofRequest($request, $nonce, base_path(), base_path('public')));
        self::assertFalse(M36LocalTargetGuard::allowsProofRequest($request, $nonce, M36LocalTargetGuard::ROOT, M36LocalTargetGuard::ROOT));
    }
}
