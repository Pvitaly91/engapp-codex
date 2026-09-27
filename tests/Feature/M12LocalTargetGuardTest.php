<?php

namespace Tests\Feature;

use App\Services\M11LocalTargetGuard;
use App\Services\M12LocalTargetGuard;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class M12LocalTargetGuardTest extends TestCase
{
    public function test_m12_reuses_physical_policy_but_not_m11_private_evidence_namespace(): void
    {
        $guard = new M12LocalTargetGuard;
        $guard->assertEvidence(M11LocalTargetGuardTest::evidence(), 'd:/dev/htdocs/gramlyze.loc', 200, 3306);
        $expected = ['target' => 'gramlyze.loc', 'nonce' => str_repeat('a', 32), 'sha256' => hash('sha256', 'same-runtime')];
        $guard->assertRuntimeProof($expected, $expected);
        foreach ([M11LocalTargetGuard::class => ['seo-m11-local', 'm11-target-'], M12LocalTargetGuard::class => ['seo-m12-local', 'm12-target-']] as $class => $values) {
            $r = new \ReflectionClass($class);
            self::assertSame($values[0], $r->getConstant('PRIVATE_DIRECTORY'));
            self::assertSame($values[1], $r->getConstant('ENDPOINT'));
        }
    }

    public static function invalidEvidence(): array { return M11LocalTargetGuardTest::invalidEvidence(); }

    #[DataProvider('invalidEvidence')]
    public function test_same_unconfirmed_dns_vhost_and_forwarder_checks_apply_to_m12(array $change): void
    {
        $this->expectException(RuntimeException::class);
        (new M12LocalTargetGuard)->assertEvidence(array_replace(M11LocalTargetGuardTest::evidence(), $change), 'd:/dev/htdocs/gramlyze.loc', 200, 3306);
    }

    public function test_unconfirmed_live_runtime_cannot_authorize_writing(): void
    {
        $this->expectException(RuntimeException::class);
        (new M12LocalTargetGuard)->assertRuntimeProof(['target' => 'gramlyze.loc', 'nonce' => 'a', 'sha256' => 'expected'], ['target' => 'gramlyze.loc', 'nonce' => 'a', 'sha256' => 'foreign']);
    }

    public function test_fixture_checkout_cannot_impersonate_working_target(): void
    {
        $this->expectException(RuntimeException::class);
        (new M12LocalTargetGuard)->verify(DB::connection(), 'gramlyze.loc', storage_path('app/seo-m12-local'), null, ['port' => 3306]);
    }

    public function test_domain_flag_is_not_a_generic_override(): void
    {
        $this->expectException(RuntimeException::class);
        (new M12LocalTargetGuard)->verify(DB::connection(), 'gramlyze.ub', storage_path(), null, []);
    }
}
