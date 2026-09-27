<?php

namespace Tests\Feature;

use App\Services\M11LocalTargetGuard;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class M11LocalTargetGuardTest extends TestCase
{
    public static function evidence(): array
    {
        return ['addresses' => ['127.0.0.1', '::1'], 'document_root' => 'd:/dev/htdocs/gramlyze.loc/public',
            'active_vhosts' => 1, 'apache_name' => 'httpd.exe', 'apache_pid' => 100, 'apache_pid_file' => 100,
            'mysql_listeners' => [['port' => 3306, 'pid' => 200, 'name' => 'mysqld.exe']]];
    }

    public function test_corroborated_windows_vhost_and_mysql_listener_are_accepted(): void
    {
        $guard = new M11LocalTargetGuard;
        $guard->assertEvidence(self::evidence(), 'd:/dev/htdocs/gramlyze.loc', 200, 3306);
        $maria = self::evidence(); $maria['mysql_listeners'][0]['name'] = 'mariadbd.exe';
        $guard->assertEvidence($maria, 'd:/dev/htdocs/gramlyze.loc', 200, 3306);
        self::assertTrue(true);
    }

    public static function invalidEvidence(): array
    {
        return array_map(fn ($change) => [$change], [
            ['addresses' => ['192.168.0.104']], ['addresses' => []], ['addresses' => ['127.0.0.1', '203.0.113.9']],
            ['document_root' => 'd:/dev/htdocs/other/public'], ['active_vhosts' => 2], ['active_vhosts' => 0],
            ['apache_name' => 'ssh.exe'], ['apache_pid' => 0, 'apache_pid_file' => 0], ['apache_pid_file' => 101],
            ['mysql_listeners' => []], ['mysql_listeners' => [['port' => 3306, 'pid' => 200, 'name' => 'ssh.exe']]],
            ['mysql_listeners' => [['port' => 3307, 'pid' => 200, 'name' => 'mysqld.exe']]],
            ['mysql_listeners' => [['port' => 3306, 'pid' => 201, 'name' => 'mysqld.exe']]],
        ]);
    }

    #[DataProvider('invalidEvidence')]
    public function test_remote_forwarded_mismatched_or_unconfirmed_evidence_is_rejected(array $change): void
    {
        $this->expectException(RuntimeException::class);
        (new M11LocalTargetGuard)->assertEvidence(array_replace(self::evidence(), $change), 'd:/dev/htdocs/gramlyze.loc', 200, 3306);
    }

    public function test_live_runtime_digest_must_match_including_database_and_web_environment(): void
    {
        $guard = new M11LocalTargetGuard;
        $expected = ['target' => 'gramlyze.loc', 'nonce' => str_repeat('a', 32), 'sha256' => hash('sha256', 'working-db/config/key')];
        $guard->assertRuntimeProof($expected, $expected);
        $this->expectException(RuntimeException::class);
        $guard->assertRuntimeProof($expected, array_replace($expected, ['sha256' => hash('sha256', 'different-db')]));
    }

    public function test_the_real_guard_refuses_a_fixture_checkout_even_with_an_optin(): void
    {
        $this->expectException(RuntimeException::class);
        (new M11LocalTargetGuard)->verify(DB::connection(), 'gramlyze.loc', storage_path('app/seo-m11-local'), null, ['port' => 3306]);
    }

    public function test_domain_optin_is_not_a_generic_force_permission(): void
    {
        $this->expectException(RuntimeException::class);
        (new M11LocalTargetGuard)->verify(DB::connection(), 'gramlyze.com', storage_path(), null, []);
    }
}
