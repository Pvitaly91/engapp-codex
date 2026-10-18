<?php

namespace Tests\Feature;

use App\Services\M69LocalTargetGuard as Guard;
use Illuminate\Http\Request;
use RuntimeException;
use Tests\TestCase;

/** Pure new-scope policy checks; no PDO, physical scan, network or route edits. */
class M69LocalTargetGuardTest extends TestCase
{
    public function test_new_scope_is_not_the_accepted_m68_scope_or_a_fourth_owner(): void
    {
        self::assertSame([
            ['identity' => 'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesVsAdverbsTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/prykmetniky-ta-pryslinknyky/adjectives-vs-adverbs'],
            ['identity' => 'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesOrderOfAdjectivesTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/prykmetniky-ta-pryslinknyky/order-of-adjectives'],
            ['identity' => 'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesEdIngAdjectivesTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/prykmetniky-ta-pryslinknyky/ed-ing-adjectives'],
        ], Guard::scope());
        self::assertSame('D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m69-local', Guard::privateDirectory());
    }

    public function test_proof_binds_new_scope_nonce_time_and_exact_physical_inventory(): void
    {
        $now = 1780000000; $nonce = str_repeat('a', 32); $name = 'local-proof-'.$nonce.'.json';
        $proof = ['target' => 'gramlyze.loc', 'nonce' => $nonce, 'created_at' => gmdate('c', $now), 'scope' => Guard::scope(),
            'physical_hashes' => array_fill_keys(['C:/Program Files/xampp/apache/conf/httpd.conf',
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', Guard::ROOT.'/public/index.php'], str_repeat('b', 64))];
        Guard::assertFreshProof($proof, $name, $now);
        foreach (['target', 'old-scope', 'ancestry', 'false-tenses', 'locale', 'extra-owner', 'nonce', 'stale', 'future', 'inventory', 'hash'] as $case) {
            $bad = $proof;
            if ($case === 'target') { $bad['target'] = 'gramlyze.ub'; }
            if ($case === 'old-scope') { $bad['scope'] = \App\Services\M68LocalTargetGuard::scope(); }
            if ($case === 'ancestry') { $bad['scope'][0]['path'] = '/theory/passive-voice/theory-passive-voice-passive-gerund'; }
            if ($case === 'false-tenses') { $bad['scope'][1]['path'] = '/theory/passive-voice/tenses/theory-passive-voice-present-simple'; }
            if ($case === 'locale') { $bad['scope'][0]['locale'] = 'en'; }
            if ($case === 'extra-owner') { $bad['scope'][] = $bad['scope'][0]; }
            if ($case === 'nonce') { $bad['nonce'] = str_repeat('c', 32); }
            if ($case === 'stale') { $bad['created_at'] = gmdate('c', $now - 1801); }
            if ($case === 'future') { $bad['created_at'] = gmdate('c', $now + 61); }
            if ($case === 'inventory') { $bad['physical_hashes'] = array_reverse($bad['physical_hashes'], true); }
            if ($case === 'hash') { $bad['physical_hashes'][Guard::ROOT.'/public/index.php'] = 'DRAFT'; }
            $caught = null; try { Guard::assertFreshProof($bad, $name, $now); } catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, $case);
        }
    }

    private function request(string $url, string $method = 'GET', array $server = []): Request
    {
        $request = Request::create($url, $method, [], [], [], ['REMOTE_ADDR' => '127.0.0.1', 'REQUEST_METHOD' => $method]);
        $request->server->add($server); $request->headers->replace($request->server->getHeaders()); return $request;
    }

    public function test_nonce_endpoint_is_only_http_local_get_with_no_cookie_auth_or_forwarding(): void
    {
        $nonce = str_repeat('a', 32); $url = 'http://gramlyze.loc/api/_local/m69-target-'.$nonce;
        self::assertTrue(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        self::assertTrue(Guard::allowsProofRequest($this->request($url, 'GET', ['REMOTE_ADDR' => '::1']), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        $requests = [$this->request($url, 'POST'), $this->request($url, 'HEAD'), $this->request($url.'?x=1'),
            $this->request(str_replace('http:', 'https:', $url)), $this->request(str_replace('m69-target-', 'm53-target-', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.ub', $url)), $this->request(str_replace('gramlyze.loc', 'gramlyze.com', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.loc:8080', $url)),
            $this->request($url, 'GET', ['REMOTE_ADDR' => '192.168.0.104'])];
        foreach (['HTTP_COOKIE' => 'fixture=1', 'HTTP_AUTHORIZATION' => 'fixture', 'HTTP_REFERER' => 'http://gramlyze.loc/',
            'HTTP_FORWARDED' => 'for=127.0.0.1', 'HTTP_X_FORWARDED_HOST' => 'gramlyze.loc',
            'HTTP_X_FORWARDED_PROTO' => 'http', 'HTTP_X_REAL_IP' => '127.0.0.1', 'HTTP_VIA' => 'fixture'] as $header => $value) {
            $requests[] = $this->request($url, 'GET', [$header => $value]);
        }
        foreach ($requests as $request) { self::assertFalse(Guard::allowsProofRequest($request, $nonce, Guard::ROOT, Guard::ROOT.'/public')); }
        self::assertFalse(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT.'/public', Guard::ROOT));
        self::assertFalse(Guard::allowsProofRequest($this->request($url), 'malformed', Guard::ROOT, Guard::ROOT.'/public'));
    }
}
