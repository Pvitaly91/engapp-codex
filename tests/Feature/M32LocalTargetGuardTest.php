<?php

namespace Tests\Feature;

use App\Services\M32LocalTargetGuard;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class M32LocalTargetGuardTest extends TestCase
{
    public function test_public_identity_returns_only_safe_allowlisted_fields(): void
    {
        config(['app.key'=>'never-return-this-app-key']);
        $db=\Mockery::mock(\Illuminate\Database\Connection::class);
        $db->shouldReceive('selectOne')->once()->with('SELECT DATABASE() AS db, @@port AS port')->andReturn((object)['db'=>'fixture','port'=>3306]);
        $db->shouldReceive('getDriverName')->once()->andReturn('mysql');
        $db->shouldReceive('getConfig')->once()->with('host')->andReturn('localhost');
        $identity=M32LocalTargetGuard::publicIdentity($db,base_path('public'));
        self::assertSame(['environment','site_mode','application_root','document_root','db_driver','db_host','db_port','database'],array_keys($identity));
        self::assertStringNotContainsString('never-return-this-app-key',json_encode($identity));
        self::assertSame('development',$identity['site_mode']);
    }
    public function test_m32_inherits_physical_windows_vhost_mysql_and_runtime_guards(): void
    {
        $guard=new M32LocalTargetGuard;
        $guard->assertEvidence(M11LocalTargetGuardTest::evidence(),'d:/dev/htdocs/gramlyze.loc',200,3306);
        $proof=['target'=>'gramlyze.loc','nonce'=>str_repeat('a',32),'sha256'=>hash('sha256','local-db-config')];
        $guard->assertRuntimeProof($proof,$proof);
        $this->expectException(RuntimeException::class);
        $guard->assertRuntimeProof($proof,array_replace($proof,['sha256'=>hash('sha256','other-db')]));
    }
    public function test_m32_rejects_remote_evidence(): void
    {
        $this->expectException(RuntimeException::class);
        (new M32LocalTargetGuard)->assertEvidence(array_replace(M11LocalTargetGuardTest::evidence(),['addresses'=>['192.168.0.104']]),'d:/dev/htdocs/gramlyze.loc',200,3306);
    }
    public function test_m32_never_accepts_isolated_fixture_as_working_database(): void
    {
        $this->expectException(RuntimeException::class);
        (new M32LocalTargetGuard)->verify(DB::connection(),'gramlyze.loc',storage_path('app/seo-m32-local'),null,['port'=>3306]);
    }
    public function test_m32_never_accepts_production_domain_optin(): void
    {
        $this->expectException(RuntimeException::class);
        (new M32LocalTargetGuard)->verify(DB::connection(),'gramlyze.com',storage_path(),null,[]);
    }
}
