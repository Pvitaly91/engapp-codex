<?php

namespace Tests\Feature;

use App\Services\M50LocalTargetGuard as Guard;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use RuntimeException;
use Tests\TestCase;

/** Pure policy fixtures: never opens MySQL, visits HTTP or changes routes. */
class M50LocalTargetGuardTest extends TestCase
{
    public function test_exact_connection_shape_is_checked_without_connecting_or_issuing_queries(): void
    {
        $config = ['driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 3306, 'database' => 'gr2'];
        Guard::assertConfiguredDatabase(new Connection(null, 'gr2', '', $config));
        foreach (['driver' => 'sqlite', 'host' => 'remote.invalid', 'port' => 3307, 'database' => 'other',
            'url' => 'mysql://remote.invalid', 'unix_socket' => '/tmp/mysql.sock', 'read' => ['host' => 'other'],
            'write' => ['host' => 'other']] as $field => $value) {
            $error = null;
            try { Guard::assertConfiguredDatabase(new Connection(null, 'gr2', '', array_replace($config, [$field => $value]))); }
            catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $field);
        }
    }

    private function proof(int $at): array
    {
        return ['target' => 'gramlyze.loc', 'nonce' => str_repeat('a', 32), 'created_at' => gmdate('c', $at), 'scope' => Guard::scope(),
            'physical_hashes' => array_fill_keys(['C:/Program Files/xampp/apache/conf/httpd.conf',
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', Guard::ROOT.'/public/index.php'], str_repeat('b', 64))];
    }

    public function test_fresh_proof_scope_time_nonce_and_exact_physical_inventory_are_closed(): void
    {
        $now = 1760000000; $proof = $this->proof($now); $name = 'local-proof-'.$proof['nonce'].'.json';
        Guard::assertFreshProof($proof, $name, $now); self::assertCount(3, Guard::scope());
        foreach (['target', 'scope', 'nonce', 'stale', 'future', 'physical-paths', 'sha'] as $change) {
            $bad = $proof;
            if ($change === 'target') { $bad['target'] = 'gramlyze.ub'; }
            if ($change === 'scope') { $bad['scope'][0]['locale'] = 'en'; }
            if ($change === 'nonce') { $bad['nonce'] = str_repeat('c', 32); }
            if ($change === 'stale') { $bad['created_at'] = gmdate('c', $now - 1801); }
            if ($change === 'future') { $bad['created_at'] = gmdate('c', $now + 61); }
            if ($change === 'physical-paths') { $bad['physical_hashes']['foreign'] = str_repeat('b', 64); }
            if ($change === 'sha') { $bad['physical_hashes'][Guard::ROOT.'/public/index.php'] = 'invalid'; }
            $error = null; try { Guard::assertFreshProof($bad, $name, $now); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $change);
        }
    }

    private function request(string $url, string $method = 'GET', array $server = []): Request
    {
        return Request::create($url, $method, [], [], [], array_replace(['REMOTE_ADDR' => '127.0.0.1', 'REQUEST_METHOD' => $method], $server));
    }

    public function test_temporary_proof_request_must_be_exact_local_get_loopback_without_secrets_or_forwarding(): void
    {
        $nonce = str_repeat('a', 32); $url = 'http://gramlyze.loc/api/_local/m50-target-'.$nonce;
        self::assertTrue(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        foreach ([$this->request(str_replace('gramlyze.loc', 'gramlyze.ub', $url)),
            $this->request(str_replace('http:', 'https:', $url)), $this->request($url.'?extra=1'),
            $this->request($url, 'POST'), $this->request($url, 'HEAD'), $this->request($url, 'GET', ['REMOTE_ADDR' => '192.168.0.104']),
            $this->request($url, 'GET', ['HTTP_COOKIE' => 'synthetic=fixture']),
            $this->request($url, 'GET', ['HTTP_AUTHORIZATION' => 'synthetic']),
            $this->request($url, 'GET', ['HTTP_X_FORWARDED_HOST' => 'gramlyze.loc'])] as $request) {
            self::assertFalse(Guard::allowsProofRequest($request, $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        }
        self::assertFalse(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT.'/public', Guard::ROOT));
        self::assertFalse(Guard::allowsProofRequest($this->request($url), 'malformed', Guard::ROOT, Guard::ROOT.'/public'));
    }
}
