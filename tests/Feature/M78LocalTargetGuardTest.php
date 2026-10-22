<?php

namespace Tests\Feature;

use App\Services\M78LocalTargetGuard as Guard;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure policy and request objects only: no PDO, physical inspection, live route, SQL or HTTP. */
class M78LocalTargetGuardTest extends TestCase
{
    private function refuses(callable $call, string $case): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    public function test_scope_is_the_exact_six_m78_owners_and_complete_ancestries(): void
    {
        $phrasal = 'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\';
        $question = 'Database\\Seeders\\Page_V3\\QuestionsNegations\\';
        $types = $question.'TypesOfQuestions\\';
        self::assertSame([
            ['identity' => $phrasal.'PhrasalVerbsSeparableInseparableTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/prepositions-and-phrasal-verbs/separable-and-inseparable-phrasal-verbs'],
            ['identity' => $phrasal.'CommonPhrasalVerbsByTopicTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/prepositions-and-phrasal-verbs/common-phrasal-verbs-by-topic'],
            ['identity' => $question.'QuestionsNegationsQuestionFormsTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/pytalni-rechennia-ta-zaperechennia/question-forms'],
            ['identity' => $types.'TypesOfQuestionsYesNoQuestionsGeneralQuestionsTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/yesno-questions-general-questions'],
            ['identity' => $types.'TypesOfQuestionsWhQuestionsSpecialQuestionsTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/wh-questions-special-questions-who-what-where-when-why-how'],
            ['identity' => $types.'TypesOfQuestionsAnswersToQuestionsTheorySeeder', 'locale' => 'uk',
                'path' => '/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/answers-to-questions-yes-i-do-no-i-dont'],
        ], Guard::scope());
        self::assertSame('D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m78-local', Guard::privateDirectory());
    }

    public function test_proof_pins_all_six_owners_nonce_freshness_and_ordered_physical_inventory(): void
    {
        $now = 1780000000; $nonce = str_repeat('a', 32); $name = 'local-proof-'.$nonce.'.json';
        $proof = ['target' => 'gramlyze.loc', 'nonce' => $nonce, 'created_at' => gmdate('c', $now), 'scope' => Guard::scope(),
            'physical_hashes' => array_fill_keys(['C:/Program Files/xampp/apache/conf/httpd.conf',
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', Guard::ROOT.'/public/index.php'], str_repeat('b', 64))];
        Guard::assertFreshProof($proof, $name, $now);
        foreach ([-1800, 60] as $offset) {
            $boundary = $proof; $boundary['created_at'] = gmdate('c', $now + $offset);
            Guard::assertFreshProof($boundary, $name, $now);
        }
        $mutations = [
            'remote-target' => static function (&$p): void { $p['target'] = 'gramlyze.ub'; },
            'production-target' => static function (&$p): void { $p['target'] = 'gramlyze.com'; },
            'previous-release-scope' => static function (&$p): void { $p['scope'] = \App\Services\M77LocalTargetGuard::scope(); },
            'incomplete-ancestry' => static function (&$p): void { $p['scope'][3]['path'] = '/theory/types-of-questions/yesno-questions-general-questions'; },
            'foreign-locale' => static function (&$p): void { $p['scope'][0]['locale'] = 'pl'; },
            'seventh-owner' => static function (&$p): void { $p['scope'][] = $p['scope'][0]; },
            'missing-owner' => static function (&$p): void { array_pop($p['scope']); },
            'reordered-owners' => static function (&$p): void { $p['scope'] = array_reverse($p['scope']); },
            'foreign-owner' => static function (&$p): void { $p['scope'][4]['identity'] = 'Foreign\\Owner'; },
            'nonce' => static function (&$p): void { $p['nonce'] = str_repeat('c', 32); },
            'stale' => static function (&$p) use ($now): void { $p['created_at'] = gmdate('c', $now - 1801); },
            'future' => static function (&$p) use ($now): void { $p['created_at'] = gmdate('c', $now + 61); },
            'inventory-order' => static function (&$p): void { $p['physical_hashes'] = array_reverse($p['physical_hashes'], true); },
            'missing-physical-file' => static function (&$p): void { array_pop($p['physical_hashes']); },
            'extra-physical-file' => static function (&$p): void { $p['physical_hashes']['foreign-file'] = str_repeat('b', 64); },
            'pending-hash' => static function (&$p): void { $p['physical_hashes'][Guard::ROOT.'/public/index.php'] = 'PENDING'; },
        ];
        foreach ($mutations as $case => $change) {
            $bad = $proof; $change($bad);
            $this->refuses(static fn () => Guard::assertFreshProof($bad, $name, $now), $case);
        }
        foreach (['../'.$name, '/'.$name, 'proof.json', strtoupper($name), 'local-proof-'.str_repeat('a', 31).'.json'] as $invalid) {
            $this->refuses(static fn () => Guard::assertFreshProof($proof, $invalid, $now), 'invalid basename '.$invalid);
            $this->refuses(static fn () => Guard::readFreshProof($invalid), 'reject invalid basename before any private-file read');
        }
    }

    private function request(string $url, string $method = 'GET', array $server = []): Request
    {
        $request = Request::create($url, $method, [], [], [], ['REMOTE_ADDR' => '127.0.0.1', 'REQUEST_METHOD' => $method]);
        $request->server->add($server); $request->headers->replace($request->server->getHeaders());
        return $request;
    }

    public function test_proof_endpoint_accepts_only_exact_plain_http_loopback_get_without_ambient_authority(): void
    {
        $nonce = str_repeat('a', 32); $url = 'http://gramlyze.loc/api/_local/m78-target-'.$nonce;
        self::assertTrue(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        self::assertTrue(Guard::allowsProofRequest($this->request($url, 'GET', ['REMOTE_ADDR' => '::1']), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        self::assertTrue(Guard::allowsProofRequest($this->request(str_replace('gramlyze.loc', 'gramlyze.loc:80', $url)), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        $requests = [$this->request($url, 'POST'), $this->request($url, 'HEAD'), $this->request($url, 'PUT'),
            $this->request($url.'?x=1'), $this->request(str_replace('http:', 'https:', $url)),
            $this->request(str_replace('m78-target-', 'm77-target-', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.ub', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.com', $url)),
            $this->request(str_replace('gramlyze.loc', 'localhost', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.loc:8080', $url)),
            $this->request($url, 'GET', ['REMOTE_ADDR' => '192.168.0.104']),
            $this->request($url, 'GET', ['REQUEST_METHOD' => 'POST'])];
        foreach (['HTTP_COOKIE' => 'fixture=1', 'HTTP_AUTHORIZATION' => 'fixture', 'HTTP_REFERER' => 'http://gramlyze.loc/',
            'HTTP_FORWARDED' => 'for=127.0.0.1', 'HTTP_X_FORWARDED_HOST' => 'gramlyze.loc',
            'HTTP_X_FORWARDED_PROTO' => 'http', 'HTTP_X_FORWARDED_CUSTOM' => 'fixture',
            'HTTP_X_REAL_IP' => '127.0.0.1', 'HTTP_VIA' => 'fixture'] as $header => $value) {
            $requests[] = $this->request($url, 'GET', [$header => $value]);
        }
        foreach ($requests as $request) { self::assertFalse(Guard::allowsProofRequest($request, $nonce, Guard::ROOT, Guard::ROOT.'/public')); }
        self::assertFalse(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT.'/public', Guard::ROOT));
        self::assertFalse(Guard::allowsProofRequest($this->request($url), $nonce, dirname(__DIR__, 2), Guard::ROOT.'/public'), 'The worktree is not the served application root.');
        self::assertFalse(Guard::allowsProofRequest($this->request($url), 'invalid', Guard::ROOT, Guard::ROOT.'/public'));
    }

    private function connection(array $config, string $driver = 'mysql'): Connection
    {
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()
            ->onlyMethods(['getDriverName', 'getConfig', 'getPdo', 'select', 'statement'])->getMock();
        $connection->method('getDriverName')->willReturn($driver);
        $connection->method('getConfig')->willReturnCallback(static fn ($key = null) => $key === null ? $config : ($config[$key] ?? null));
        foreach (['getPdo', 'select', 'statement'] as $method) { $connection->expects(self::never())->method($method); }
        return $connection;
    }

    public function test_configured_target_policy_cannot_open_a_connection_or_accept_remote_split_or_url_authority(): void
    {
        $config = ['host' => '127.0.0.1', 'port' => 3306, 'database' => 'gr2', 'url' => null,
            'unix_socket' => null, 'read' => null, 'write' => null];
        foreach (['127.0.0.1', 'localhost', '::1'] as $host) {
            foreach ([3306, '3306'] as $port) { Guard::assertConfiguredDatabase($this->connection(array_replace($config, ['host' => $host, 'port' => $port]))); }
        }
        foreach (['host' => '192.168.0.104', 'port' => 3307, 'database' => 'production',
            'url' => 'mysql://untrusted.invalid/gr2', 'unix_socket' => '/tmp/foreign.sock',
            'read' => ['host' => '127.0.0.1'], 'write' => ['host' => '127.0.0.1']] as $field => $value) {
            $bad = $this->connection(array_replace($config, [$field => $value]));
            $this->refuses(static fn () => Guard::assertConfiguredDatabase($bad), 'unapproved '.$field);
        }
        $sqlite = $this->connection($config, 'sqlite');
        $this->refuses(static fn () => Guard::assertConfiguredDatabase($sqlite), 'working-target policy is MySQL-only without opening a PDO');
    }
}
