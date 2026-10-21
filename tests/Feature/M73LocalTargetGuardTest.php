<?php

namespace Tests\Feature;

use App\Services\M73LocalTargetGuard as Guard;
use Illuminate\Http\Request;
use RuntimeException;
use Tests\TestCase;

/** Pure local-proof policy: no physical inspection, route, network or database access. */
class M73LocalTargetGuardTest extends TestCase
{
    public function test_scope_is_the_three_exact_m73_owners_not_the_previous_release_or_a_fourth_page(): void
    {
        $prefix = 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\';
        self::assertSame([
            ['identity' => $prefix.'NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/imennyky-artykli-ta-kilkist/advanced-articles-generic-reference-the-rich-a-tiger-people'],
            ['identity' => $prefix.'NounsArticlesQuantityIrregularPluralsTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/imennyky-artykli-ta-kilkist/irregular-plurals'],
            ['identity' => $prefix.'NounsArticlesQuantityCollectiveNounsTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/imennyky-artykli-ta-kilkist/collective-nouns'],
        ], Guard::scope());
        self::assertSame('D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m73-local', Guard::privateDirectory());
    }

    public function test_proof_pins_scope_nonce_freshness_and_ordered_physical_hash_inventory(): void
    {
        $now = 1780000000; $nonce = str_repeat('a', 32); $name = 'local-proof-'.$nonce.'.json';
        $proof = ['target' => 'gramlyze.loc', 'nonce' => $nonce, 'created_at' => gmdate('c', $now), 'scope' => Guard::scope(),
            'physical_hashes' => array_fill_keys(['C:/Program Files/xampp/apache/conf/httpd.conf',
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', Guard::ROOT.'/public/index.php'], str_repeat('b', 64))];
        Guard::assertFreshProof($proof, $name, $now);
        foreach (['target', 'old-scope', 'ancestry', 'locale', 'fourth-owner', 'nonce', 'stale', 'future', 'inventory', 'hash'] as $case) {
            $bad = $proof;
            if ($case === 'target') { $bad['target'] = 'gramlyze.ub'; }
            if ($case === 'old-scope') { $bad['scope'] = \App\Services\M72LocalTargetGuard::scope(); }
            if ($case === 'ancestry') { $bad['scope'][0]['path'] = '/theory/advanced-articles-and-quantifiers/generic-reference'; }
            if ($case === 'locale') { $bad['scope'][0]['locale'] = 'pl'; }
            if ($case === 'fourth-owner') { $bad['scope'][] = $bad['scope'][0]; }
            if ($case === 'nonce') { $bad['nonce'] = str_repeat('c', 32); }
            if ($case === 'stale') { $bad['created_at'] = gmdate('c', $now - 1801); }
            if ($case === 'future') { $bad['created_at'] = gmdate('c', $now + 61); }
            if ($case === 'inventory') { $bad['physical_hashes'] = array_reverse($bad['physical_hashes'], true); }
            if ($case === 'hash') { $bad['physical_hashes'][Guard::ROOT.'/public/index.php'] = 'PENDING'; }
            $caught = null;
            try { Guard::assertFreshProof($bad, $name, $now); } catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, $case);
        }
    }

    private function request(string $url, string $method = 'GET', array $server = []): Request
    {
        $request = Request::create($url, $method, [], [], [], ['REMOTE_ADDR' => '127.0.0.1', 'REQUEST_METHOD' => $method]);
        $request->server->add($server); $request->headers->replace($request->server->getHeaders()); return $request;
    }

    public function test_nonce_endpoint_is_plain_http_loopback_get_without_auth_forwarding_or_ambient_cookies(): void
    {
        $nonce = str_repeat('a', 32); $url = 'http://gramlyze.loc/api/_local/m73-target-'.$nonce;
        self::assertTrue(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        self::assertTrue(Guard::allowsProofRequest($this->request($url, 'GET', ['REMOTE_ADDR' => '::1']), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        $requests = [$this->request($url, 'POST'), $this->request($url, 'HEAD'), $this->request($url.'?x=1'),
            $this->request(str_replace('http:', 'https:', $url)), $this->request(str_replace('m73-target-', 'm72-target-', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.ub', $url)), $this->request(str_replace('gramlyze.loc', 'gramlyze.com', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.loc:8080', $url)), $this->request($url, 'GET', ['REMOTE_ADDR' => '192.168.0.104'])];
        foreach (['HTTP_COOKIE' => 'fixture=1', 'HTTP_AUTHORIZATION' => 'fixture', 'HTTP_REFERER' => 'http://gramlyze.loc/',
            'HTTP_FORWARDED' => 'for=127.0.0.1', 'HTTP_X_FORWARDED_HOST' => 'gramlyze.loc',
            'HTTP_X_FORWARDED_PROTO' => 'http', 'HTTP_X_REAL_IP' => '127.0.0.1', 'HTTP_VIA' => 'fixture'] as $header => $value) {
            $requests[] = $this->request($url, 'GET', [$header => $value]);
        }
        foreach ($requests as $request) { self::assertFalse(Guard::allowsProofRequest($request, $nonce, Guard::ROOT, Guard::ROOT.'/public')); }
        self::assertFalse(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT.'/public', Guard::ROOT));
        self::assertFalse(Guard::allowsProofRequest($this->request($url), 'invalid', Guard::ROOT, Guard::ROOT.'/public'));
    }
}
