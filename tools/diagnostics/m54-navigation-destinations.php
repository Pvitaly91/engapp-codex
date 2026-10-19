<?php

/** Explicit anonymous local GET-only evidence. No Laravel bootstrap, SQL, route or content writes. */
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, $error instanceof RuntimeException
        ? 'M54 navigation: '.$error->getMessage()."\n" : "M54 navigation evidence incomplete.\n");
    exit(1);
});
$name = $argv[1] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 2
    || preg_match('/^navigation-destinations-v[1-9][0-9]*\.json$/D', $name) !== 1) {
    throw new RuntimeException('Use one explicit new navigation-destinations-vN.json private receipt.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $private = $root.'/storage/app/seo-m54-local';
$normal = static fn (string $path): string => rtrim(strtolower(str_replace('\\', '/', (string) realpath($path))), '/');
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || !is_dir($private) || is_link($private)
    || $normal($private) !== $normal($root).'/storage/app/seo-m54-local'
    || file_exists($private.'/'.$name) || is_link($private.'/'.$name)) {
    throw new RuntimeException('Run only from actual served ROOT with a new exclusive private receipt.');
}
require $root.'/vendor/autoload.php';

use App\Services\M54ContentPatch;
use App\Support\M54PassivePatternsPackage as Package;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;

$same = static function (mixed $a, mixed $b, string $label): void {
    if ($a !== $b) { throw new RuntimeException($label.' differs.'); }
};
$hash = static function (string $path): string {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('Reviewed input missing or linked.'); }
    $value = hash_file('sha256', $path);
    if (!is_string($value)) { throw new RuntimeException('Reviewed input cannot be hashed.'); }
    return $value;
};
[$before, $patch, $master, $contract] = Package::load($root);
Package::validate($before, $patch, $master, $contract);
$auditPath = $root.'/'.M54ContentPatch::AUDIT;
$same($hash($auditPath), M54ContentPatch::AUDIT_SHA, 'Independent bank audit hash');
$audit = json_decode(file_get_contents($auditPath), true, flags: JSON_THROW_ON_ERROR);
$inputs = [$auditPath => M54ContentPatch::AUDIT_SHA];
foreach (Package::profile()['artifacts'] as $path => $sha) { $inputs[$root.'/'.$path] = $sha; }
$slugs = [
    'formation-rules', 'modal-verbs', 'causative', 'get-passive', 'impersonal-passive',
    'negatives-questions', 'phrasal-verbs', 'restrictions', 'two-object-verbs',
    'common-mistakes', 'formality-style',
];
$allowed = array_map(static fn ($slug): string => '/theory/passive-voice/theory-passive-voice-'.$slug, $slugs);
$expectedPaths = $allowed; sort($expectedPaths); $sourcePaths = []; $targetByUrl = [];
foreach ($master['lessons'] as $i => $lesson) {
    $blocks = array_values(array_filter($lesson['blocks'], static fn ($b): bool => $b['type'] === 'navigation-chips'));
    $same(count($blocks), 1, 'One frozen navigation per lesson'); $paths = []; $current = [];
    foreach ($blocks[0]['data']['items'] as $item) {
        if (!is_string($item['url']) || !in_array($item['url'], $allowed, true) || !is_bool($item['current'])) {
            throw new RuntimeException('Frozen navigation is outside the exact eleven local paths.');
        }
        $paths[] = $item['url']; $sourcePaths[$item['url']] = true;
        if ($item['current']) { $current[] = 'http://gramlyze.loc'.$item['url']; }
    }
    sort($paths); $same($paths, $expectedPaths, 'Finite navigation set');
    $same($current, [$lesson['local_url']], 'Exact current navigation owner');
    $targetByUrl[$lesson['local_url']] = $i;
}
$sourcePaths = array_keys($sourcePaths); sort($sourcePaths); $same($sourcePaths, $expectedPaths, 'Deduplicated eleven destinations');
$plain = static fn (string $text): string => trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
$started = gmdate('c'); $rows = []; $failures = [];
foreach ($allowed as $path) {
    $url = 'http://gramlyze.loc'.$path; $curl = curl_init($url); $robots = null;
    curl_setopt_array($curl, [CURLOPT_HTTPGET => true, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false, CURLOPT_MAXREDIRS => 0, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 45,
        CURLOPT_PROXY => '', CURLOPT_NOPROXY => '*', CURLOPT_NETRC => CURL_NETRC_IGNORED, CURLOPT_COOKIE => '',
        CURLOPT_PROTOCOLS => CURLPROTO_HTTP, CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'],
        CURLOPT_HTTPHEADER => ['Accept: text/html'], CURLOPT_HEADERFUNCTION => static function ($handle, string $header) use (&$robots): int {
            if (stripos($header, 'X-Robots-Tag:') === 0) { $robots = trim(substr($header, 13)); }
            return strlen($header);
        }]);
    try {
        $html = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP); $port = curl_getinfo($curl, CURLINFO_PRIMARY_PORT);
        $final = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL); $redirects = curl_getinfo($curl, CURLINFO_REDIRECT_COUNT);
    } finally { curl_close($curl); }
    if ($ip !== '127.0.0.1' || $port !== 80 || $final !== $url || $redirects !== 0) {
        throw new RuntimeException('Transport left its exact anonymous loopback URL; no successful receipt.');
    }
    $row = ['url' => $url, 'http_status' => $status, 'final_url' => $final, 'redirects' => $redirects,
        'primary_ip' => $ip, 'html_sha256' => is_string($html) ? hash('sha256', $html) : null,
        'robots_header' => $robots, 'ok' => false];
    if (!is_string($html) || $status !== 200) {
        $row['errors'] = ['Direct HTTP 200 unavailable.']; $failures[] = $url; $rows[] = $row; continue;
    }
    $dom = new DOMDocument('1.0', 'UTF-8'); $previous = libxml_use_internal_errors(true);
    try { $loaded = $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET); }
    finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    if (!$loaded) { throw new RuntimeException('Destination is not parseable local HTML.'); }
    $xp = new DOMXPath($dom); $h1 = $xp->query('//h1'); $canonical = $xp->query('//link[@rel="canonical"]');
    $same($h1->length, 1, 'Destination H1 count'); $same($canonical->length, 1, 'Destination canonical count');
    $same($canonical->item(0)->getAttribute('href'), 'https://gramlyze.com'.$path, 'Canonical string, never requested');
    $same($robots, 'noindex, nofollow, noarchive', 'Local robots header');
    $row['h1'] = $plain($h1->item(0)->textContent);
    $row['title'] = $plain($xp->query('//title')->item(0)?->textContent ?? '');
    $row['canonical_string'] = $canonical->item(0)->getAttribute('href');
    if (isset($targetByUrl[$url])) {
        $i = $targetByUrl[$url]; $lesson = $master['lessons'][$i]; $bank = $audit['targets'][$i];
        $binding = $audit['practice_bindings'][$i];
        $same($binding['identity'], $lesson['identity'], 'Practice binding owner');
        $payload = Acceptance::payload($html, $binding['uuid']);
        $same(is_array($payload) ? count($payload) : null, 5, 'Actual target widget count');
        $same(array_column($payload, 'id'), $bank['expected_existing_selected_ids'], 'Exact selected existing IDs');
        $captured = [];
        foreach ($payload as $n => $question) {
            $expected = $bank['first_five'][$n];
            $same($question['id'], $expected['id'], 'Independent selected question ID');
            $same((string) $question['type'], '4', 'Widget question type');
            $same($question['level'], $contract['targets'][$i]['level'], 'Widget exact CEFR');
            $same($plain($question['question']), $plain($expected['condition_uk']), 'Full Ukrainian condition');
            $same($question['correct_tokens'], $expected['correct_tokens'], 'Exact token sequence including authored casing');
            $available = array_count_values($question['options']);
            foreach (array_count_values($question['correct_tokens']) as $token => $count) {
                if (($available[$token] ?? 0) < $count) { throw new RuntimeException('Exact-cased correct token instance missing from bank.'); }
            }
            $captured[] = ['id' => $question['id'], 'source_uuid' => $expected['uuid'], 'condition_uk' => $question['question'],
                'type' => $question['type'], 'level' => $question['level'], 'correct_tokens' => $question['correct_tokens'],
                'options' => $question['options']];
        }
        $row['exact_case_selected_payload'] = $captured;
        $row['practice_uuid'] = $binding['uuid'];
        $row['selected_case_and_condition_source_parity'] = true;
    }
    $row['ok'] = true; $row['errors'] = []; $rows[] = $row;
}
foreach ($inputs as $path => $sha) { $same($hash($path), $sha, 'Reviewed source unchanged during HTTP'); }
$receipt = ['schema_version' => 1, 'status' => $failures === [] ? 'accepted-local-navigation-and-exact-widget-case' : 'incomplete-local-navigation',
    'started_at' => $started, 'finished_at' => gmdate('c'), 'base_sha' => Package::BASE_SHA,
    'master_sha256' => Package::MASTER_SHA, 'patch_sha256' => Package::SOURCE_SHA,
    'bank_audit_sha256' => M54ContentPatch::AUDIT_SHA, 'transport' => ['anonymous' => true, 'method' => 'GET',
        'fixed_loopback' => '127.0.0.1:80', 'proxy' => false, 'cookies_sent' => false, 'redirects' => 0],
    'destinations' => $rows, 'failed_urls' => $failures, 'HTTP_200' => count(array_filter($rows, static fn ($r): bool => $r['ok'])),
    'selected_question_exact_case_checks' => array_sum(array_map(static fn ($r): int => count($r['exact_case_selected_payload'] ?? []), $rows)),
    'SQL_access' => false, 'production_requests' => 0, 'content_writes' => 0, 'runtime_or_route_edits' => 0,
    'not_checked' => ['browser visibility/interaction', 'all content of the eight neighboring lessons', 'SQL postconditions']];
$json = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($private.'/'.$name, 'x');
if ($handle === false) { throw new RuntimeException('Exclusive receipt creation failed.'); }
try { if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) { throw new RuntimeException('Private receipt write incomplete.'); } }
finally { fclose($handle); }
echo json_encode(['status' => $receipt['status'], 'file' => $name, 'sha256' => $hash($private.'/'.$name),
    'HTTP_200' => $receipt['HTTP_200'], 'selected_question_exact_case_checks' => $receipt['selected_question_exact_case_checks'],
    'SQL_access' => false], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
exit($failures === [] ? 0 : 1);
