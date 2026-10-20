<?php

namespace Tests\Feature;

use App\Services\M72LocalTargetGuard as Guard;
use Illuminate\Http\Request;
use RuntimeException;
use Tests\TestCase;

/** Pure M72 policy checks; no PDO, process inspection, network or route edits. */
class M72LocalTargetGuardTest extends TestCase
{
    public function test_scope_is_exactly_the_three_m72_noun_owners_and_not_m71_or_a_fourth_page(): void
    {
        self::assertSame([
            ['identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPartitivesTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/imennyky-artykli-ta-kilkist/partitives-with-uncountable-nouns-a-piece-of-a-cup-of'],
            ['identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/imennyky-artykli-ta-kilkist/no-none-neither-either'],
            ['identity' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/imennyky-artykli-ta-kilkist/articles-with-geographical-names'],
        ], Guard::scope());
        self::assertSame('D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m72-local', Guard::privateDirectory());
    }

    public function test_proof_requires_exact_scope_nonce_fresh_time_and_physical_inventory(): void
    {
        $now = 1780000000; $nonce = str_repeat('a', 32); $name = 'local-proof-'.$nonce.'.json';
        $proof = ['target' => 'gramlyze.loc', 'nonce' => $nonce, 'created_at' => gmdate('c', $now), 'scope' => Guard::scope(),
            'physical_hashes' => array_fill_keys(['C:/Program Files/xampp/apache/conf/httpd.conf',
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', Guard::ROOT.'/public/index.php'], str_repeat('b', 64))];
        Guard::assertFreshProof($proof, $name, $now);
        foreach (['target', 'old-scope', 'ancestry', 'foreign-category', 'locale', 'extra-owner', 'nonce', 'stale', 'future', 'inventory', 'hash'] as $case) {
            $bad = $proof;
            if ($case === 'target') { $bad['target'] = 'gramlyze.ub'; }
            if ($case === 'old-scope') { $bad['scope'] = \App\Services\M71LocalTargetGuard::scope(); }
            if ($case === 'ancestry') { $bad['scope'][0]['path'] = '/theory/nouns/partitives-with-uncountable-nouns-a-piece-of-a-cup-of'; }
            if ($case === 'foreign-category') { $bad['scope'][1]['path'] = '/theory/conditionals/zero-conditional'; }
            if ($case === 'locale') { $bad['scope'][0]['locale'] = 'en'; }
            if ($case === 'extra-owner') { $bad['scope'][] = $bad['scope'][0]; }
            if ($case === 'nonce') { $bad['nonce'] = str_repeat('c', 32); }
            if ($case === 'stale') { $bad['created_at'] = gmdate('c', $now - 1801); }
            if ($case === 'future') { $bad['created_at'] = gmdate('c', $now + 61); }
            if ($case === 'inventory') { $bad['physical_hashes'] = array_reverse($bad['physical_hashes'], true); }
            if ($case === 'hash') { $bad['physical_hashes'][Guard::ROOT.'/public/index.php'] = 'DRAFT'; }
            $caught = null;
            try { Guard::assertFreshProof($bad, $name, $now); } catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, $case);
        }
    }

    private function request(string $url, string $method = 'GET', array $server = []): Request
    {
        $request = Request::create($url, $method, [], [], [], ['REMOTE_ADDR' => '127.0.0.1', 'REQUEST_METHOD' => $method]);
        $request->server->add($server); $request->headers->replace($request->server->getHeaders());
        return $request;
    }

    public function test_nonce_endpoint_accepts_only_plain_http_loopback_get_without_auth_cookies_or_forwarding(): void
    {
        $nonce = str_repeat('a', 32); $url = 'http://gramlyze.loc/api/_local/m72-target-'.$nonce;
        self::assertTrue(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        self::assertTrue(Guard::allowsProofRequest($this->request($url, 'GET', ['REMOTE_ADDR' => '::1']), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        $requests = [$this->request($url, 'POST'), $this->request($url, 'HEAD'), $this->request($url.'?x=1'),
            $this->request(str_replace('http:', 'https:', $url)), $this->request(str_replace('m72-target-', 'm71-target-', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.ub', $url)), $this->request(str_replace('gramlyze.loc', 'gramlyze.com', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.loc:8080', $url)),
            $this->request($url, 'GET', ['REMOTE_ADDR' => '192.168.0.104'])];
        foreach (['HTTP_COOKIE' => 'fixture=1', 'HTTP_AUTHORIZATION' => 'fixture', 'HTTP_REFERER' => 'http://gramlyze.loc/',
            'HTTP_FORWARDED' => 'for=127.0.0.1', 'HTTP_X_FORWARDED_HOST' => 'gramlyze.loc',
            'HTTP_X_FORWARDED_PROTO' => 'http', 'HTTP_X_REAL_IP' => '127.0.0.1', 'HTTP_VIA' => 'fixture'] as $header => $value) {
            $requests[] = $this->request($url, 'GET', [$header => $value]);
        }
        foreach ($requests as $request) {
            self::assertFalse(Guard::allowsProofRequest($request, $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        }
        self::assertFalse(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT.'/public', Guard::ROOT));
        self::assertFalse(Guard::allowsProofRequest($this->request($url), 'malformed', Guard::ROOT, Guard::ROOT.'/public'));
    }
}
