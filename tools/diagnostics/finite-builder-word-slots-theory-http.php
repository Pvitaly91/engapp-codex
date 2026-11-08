<?php
declare(strict_types=1);

// Read-only live regression for untouched lower-five thematic theory widgets.
if (PHP_SAPI !== 'cli' || ($argv[1] ?? '') !== 'run' || count($argv) !== 2
    || strcasecmp(str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2))), 'D:/DEV/htdocs/gramlyze.loc') !== 0) {
    fwrite(STDERR, "Usage: php tools/diagnostics/finite-builder-word-slots-theory-http.php run\n"); exit(2);
}
$root = dirname(__DIR__, 2);
$cases = [['passive-voice','present-perfect'], ['modal-verbs','need-neednt-dont-have-to'],
    ['sentence-transformations','reported-speech']];
$receipt = ['status'=>'verified', 'pages'=>[], 'failures'=>[], 'database_writes'=>0];
foreach ($cases as [$package, $topic]) {
    try {
        $source = json_decode(file_get_contents($root.'/docs/content/'.$package.'-widget-hints.v1.json'), true, flags: JSON_THROW_ON_ERROR);
        $target = array_column($source['targets'], null, 'topic')[$topic];
        $url = $target['local_url'];
        if (preg_match('~^http://gramlyze\.loc/theory/[a-z0-9/-]+$~D', $url) !== 1) { throw new RuntimeException('Exact local URL required.'); }
        $curl = curl_init($url);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>false, CURLOPT_PROXY=>'',
            CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1'], CURLOPT_CONNECTTIMEOUT=>4, CURLOPT_TIMEOUT=>60]);
        $html = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $local = curl_getinfo($curl, CURLINFO_PRIMARY_IP) === '127.0.0.1'; curl_close($curl);
        if ($status !== 200 || !$local || !is_string($html)) { throw new RuntimeException('Pinned local theory GET failed.'); }
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true); $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors(); libxml_use_internal_errors($previous); $xp = new DOMXPath($dom);
        if ($xp->query('//*[@data-sentence-builder]')->length !== 1
            || $xp->query('//*[@x-data and starts-with(@x-data,"theoryPracticeSet(")]')->length !== 1
            || $xp->query('/html[@lang="uk"]')->length !== 1) { throw new RuntimeException('Existing native practice/widget/locale changed.'); }
        preg_match_all('/questions:\s*(\[.*?\]),\s*currentIndex:\s*0/s', $html, $matches);
        if (count($matches[1]) !== 1) { throw new RuntimeException('One embedded question payload required.'); }
        $rows = json_decode($matches[1][0], true, flags: JSON_THROW_ON_ERROR);
        if (count($rows) !== 5 || array_column($rows, 'id') !== $target['selected_ids']) { throw new RuntimeException('Exact thematic five-question selection changed.'); }
        $questions = array_column($source['questions'], null, 'id');
        foreach ($rows as $row) {
            $expected = $questions[$row['id']];
            if ((string) ($row['type'] ?? '') !== '4' || ($row['question'] ?? null) !== $expected['display_question']
                || ($row['correct_tokens'] ?? null) !== $expected['correct_tokens']) { throw new RuntimeException('Untouched widget prompt/tokens changed.'); }
        }
        $receipt['pages'][] = ['url'=>$url, 'http_status'=>$status, 'widgets'=>1, 'questions'=>5,
            'selected_ids'=>$target['selected_ids'], 'response_sha256'=>hash('sha256', $html)];
    } catch (Throwable $error) {
        $receipt['failures'][] = ['package'=>$package, 'topic'=>$topic,
            'reason'=>$error instanceof RuntimeException ? $error->getMessage() : get_class($error)];
    }
}
$receipt['status'] = $receipt['failures'] === [] && count($receipt['pages']) === 3 ? 'verified' : 'failed';
$directory = $root.'/storage/app/finite-builder-word-slots';
if (!is_dir($directory) || is_link($directory)) { throw new RuntimeException('Private receipt directory required.'); }
$path = $directory.'/theory-http-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json';
$stream = fopen($path, 'x');
if ($stream === false) { throw new RuntimeException('Exclusive receipt required.'); }
try {
    $json = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    if (fwrite($stream, $json) !== strlen($json) || !fflush($stream)) { throw new RuntimeException('Receipt write incomplete.'); }
} finally { fclose($stream); }
echo json_encode($receipt + ['receipt'=>$path], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
exit($receipt['status'] === 'verified' ? 0 : 1);
