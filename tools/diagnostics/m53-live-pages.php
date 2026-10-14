<?php

/**
 * Explicit, anonymous local HTTP/SSR acceptance only; not a browser interaction test.
 * php tools/diagnostics/m53-live-pages.php live-pages-v1.json
 * No Laravel bootstrap, SQL access, route installation, fixture or content writes.
 */
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, $error instanceof RuntimeException
        ? 'M53 live HTTP: '.$error->getMessage()."\n"
        : "M53 live HTTP incomplete; no successful receipt.\n");
    exit(1);
});
$name = $argv[1] ?? '';
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 2
    || preg_match('/^live-pages-v[1-9][0-9]*\.json$/D', $name) !== 1) {
    throw new RuntimeException('An explicit new live-pages-vN.json private receipt basename is required.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $private = $root.'/storage/app/seo-m53-local';
$normal = static fn (string $path): string => rtrim(strtolower(str_replace('\\', '/', (string) realpath($path))), '/');
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || !is_dir($private) || is_link($private)
    || $normal($private) !== $normal($root).'/storage/app/seo-m53-local'
    || file_exists($private.'/'.$name) || is_link($private.'/'.$name)) {
    throw new RuntimeException('Run from the actual served ROOT; the private receipt must not already exist.');
}
require $root.'/vendor/autoload.php';

use App\Services\M53ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M53BuilderHintPresentation;
use App\Support\M53PassiveConstructionsPackage as Package;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;

$fileHash = static function (string $path): string {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('Missing or linked reviewed input.'); }
    $value = hash_file('sha256', $path);
    if (!is_string($value)) { throw new RuntimeException('Reviewed input cannot be hashed.'); }
    return $value;
};
$beforePath = $private.'/'.M53ContentPatch::BEFORE_FILE;
if (!hash_equals(M53ContentPatch::BEFORE_SHA, $fileHash($beforePath))) {
    throw new RuntimeException('Independent pre-apply BEFORE bytes differ.');
}
$before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
[$sourceBefore, $patch, $master, $contract] = Package::load($root);
Package::validate($sourceBefore, $patch, $master, $contract);
$auditPath = $root.'/'.M53BuilderHintPresentation::AUDIT;
if (!hash_equals(M53BuilderHintPresentation::AUDIT_SHA, $fileHash($auditPath))) {
    throw new RuntimeException('Independent sentence-bank audit bytes differ.');
}
$audit = json_decode(file_get_contents($auditPath), true, flags: JSON_THROW_ON_ERROR);
$inputs = [$beforePath => M53ContentPatch::BEFORE_SHA, $auditPath => M53BuilderHintPresentation::AUDIT_SHA];
foreach (Package::profile()['artifacts'] as $path => $sha) { $inputs[$root.'/'.$path] = $sha; }
$same = static function (mixed $actual, mixed $expected, string $label): void {
    if ($actual !== $expected) { throw new RuntimeException($label.' differs.'); }
};
$plain = static function (string $text): string {
    $text = preg_replace('/<(?:br\s*\/?|\/(?:p|div|li|section))>/i', ' ', $text);
    $plain = preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    return trim(preg_replace('/\s+([,.!?;:])/u', '$1', $plain));
};
$learningText = static function (DOMXPath $xp, DOMNode $node) use ($plain): string {
    $parts = [];
    foreach ($xp->query('.//text()[not(ancestor::script) and not(ancestor::style)]', $node) as $text) {
        $parts[] = $text->nodeValue;
    }
    return $plain(implode(' ', $parts));
};
$fieldCount = 0;
$contains = static function (string $text, mixed $expected, string $label) use ($plain, &$fieldCount): void {
    if (!is_string($expected) || $plain($expected) === '') { return; }
    if (!str_contains($text, $plain($expected))) { throw new RuntimeException('Missing SSR learning field: '.$label); }
    $fieldCount++;
};
$sectionFields = static function (array $sections, string $text, string $label) use ($contains): void {
    foreach ($sections as $index => $section) {
        foreach (['label', 'description', 'note'] as $field) {
            $contains($text, $section[$field] ?? null, $label.'/'.$index.'/'.$field);
        }
        foreach ($section['examples'] ?? [] as $n => $example) {
            foreach (['en', 'ua'] as $field) { $contains($text, $example[$field] ?? null, $label.'/'.$index.'/examples/'.$n.'/'.$field); }
        }
    }
};
$http = static function (string $url): array {
    if (preg_match('~^http://gramlyze\.loc/theory/passive-voice/theory-passive-voice-(?:causative|get-passive|impersonal-passive)$~D', $url) !== 1) {
        throw new RuntimeException('HTTP target is outside the exact three local lessons.');
    }
    $curl = curl_init($url); $robots = null;
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
        if (!is_string($html) || $status !== 200 || curl_getinfo($curl, CURLINFO_PRIMARY_IP) !== '127.0.0.1'
            || curl_getinfo($curl, CURLINFO_PRIMARY_PORT) !== 80 || curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) !== $url
            || curl_getinfo($curl, CURLINFO_REDIRECT_COUNT) !== 0) {
            throw new RuntimeException('Exact anonymous loopback GET did not return direct HTTP 200.');
        }
        return [$html, $status, $robots];
    } finally { curl_close($curl); }
};

$started = gmdate('c'); $results = [];
foreach ($patch['targets'] as $i => $target) {
    $owner = $target['identity']; $lesson = $master['lessons'][$i]; $url = $target['local_url'];
    $same($url, Package::profile()['bindings'][$i]['local_url'], 'Frozen local URL');
    $oldHttp = array_values(array_filter($before['http'] ?? [], static fn ($record) => ($record['kind'] ?? null) === 'theory' && ($record['identity'] ?? null) === $owner));
    $same(count($oldHttp), 1, 'Independent theory BEFORE record count'); $oldHttp = $oldHttp[0];
    $same($oldHttp['url'], $url, 'Independent theory BEFORE URL');
    $metadata = array_values(array_filter($master['editorial_review']['pre_apply_metadata'] ?? [], static fn ($record) => ($record['identity'] ?? null) === $owner));
    $same(count($metadata), 1, 'Independent editorial metadata record count'); $metadata = $metadata[0];
    $same($metadata['h1'], $oldHttp['h1'], 'Editorial preserved H1');
    $same($metadata['expected_seo']['title'], $oldHttp['title'], 'Editorial preserved title');
    [$html, $status, $robots] = $http($url);
    $dom = new DOMDocument('1.0', 'UTF-8'); $previous = libxml_use_internal_errors(true);
    try { $loaded = $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET); }
    finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    if (!$loaded) { throw new RuntimeException('Local response is not parseable HTML.'); }
    $xp = new DOMXPath($dom);
    $one = static function (string $query) use ($xp): DOMElement {
        $nodes = $xp->query($query);
        if ($nodes->length !== 1 || !$nodes->item(0) instanceof DOMElement) { throw new RuntimeException('Expected one SSR node: '.$query); }
        return $nodes->item(0);
    };
    $main = $one('//*[@data-theory-main]'); $mainText = $learningText($xp, $main);
    $facts = ['h1_count' => $xp->query('//h1')->length, 'h1' => $plain($one('//h1')->textContent),
        'title' => $plain($one('//title')->textContent),
        'breadcrumb' => $plain($one('//nav[@aria-label][contains(@class,"mb-8")]')->textContent),
        'canonical' => $one('//link[@rel="canonical"]')->getAttribute('href'),
        'description' => $one('//meta[@name="description"]')->getAttribute('content'),
        'robots_header' => $robots, 'robots_meta' => $xp->query('//meta[@name="robots"]')->item(0)?->getAttribute('content')];
    foreach (['h1_count', 'h1', 'title', 'breadcrumb', 'canonical', 'robots_header', 'robots_meta'] as $key) {
        $same($facts[$key], $oldHttp[$key], 'Preserved '.$key);
    }
    $same($facts['description'], $metadata['expected_seo']['description'], 'Reviewed new SEO description');
    $resources = [];
    $visit = static function (mixed $value) use (&$visit, &$resources): void {
        if (!is_array($value)) { return; }
        if (in_array('LearningResource', (array) ($value['@type'] ?? []), true)) {
            $resources[] = array_intersect_key($value, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage']));
        }
        foreach ($value as $child) { if (is_array($child)) { $visit($child); } }
    };
    foreach ($xp->query('//script[@type="application/ld+json"]') as $script) { $visit(json_decode($script->textContent, true, flags: JSON_THROW_ON_ERROR)); }
    $oldResources = array_map(static fn ($value) => array_intersect_key($value, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage'])), $oldHttp['learning_resources']);
    $same(count($resources), 1, 'LearningResource count'); $same($resources, $oldResources, 'Stable LearningResource identity');
    $anchors = [];
    foreach ($xp->query('//*[@id]') as $node) { $anchors[] = $node->getAttribute('id'); }
    $missingAnchors = array_values(array_diff(array_unique($oldHttp['anchors']), array_unique($anchors)));
    $same($missingAnchors, [], 'Preserved pre-existing anchors');
    $oldBlocks = array_values(array_filter($before['pages'][$owner]['blocks'], static fn ($row) => $row['locale'] === 'uk'));
    $byOrder = [];
    foreach ($oldBlocks as $row) { $byOrder[(int) $row['sort_order']] = $row; }
    $blockTexts = []; $blockResults = []; $navLinks = []; $lessonFieldStart = $fieldCount;
    foreach ($lesson['blocks'] as $slot => $author) {
        $type = $author['type']; $data = $author['data']; $label = $author['key'];
        if ($type === 'hero') {
            foreach (['level', 'intro'] as $field) { $contains($mainText, $data[$field], $label.'/'.$field); }
            foreach ($data['rules'] as $n => $rule) { foreach (['label', 'text', 'example'] as $field) { $contains($mainText, $rule[$field], $label.'/rules/'.$n.'/'.$field); } }
            continue;
        }
        if ($type === 'navigation-chips') {
            $contains($mainText, $data['title'], $label.'/title');
            foreach ($data['items'] as $item) {
                $contains($mainText, $item['label'], $label.'/label');
                if ($item['current']) { continue; }
                if (preg_match('~^/theory/passive-voice/[a-z0-9-]+$~D', $item['url']) !== 1) { throw new RuntimeException('Navigation destination is not an exact local theory path.'); }
                $found = false;
                foreach ($xp->query('.//nav//a[@href]', $main) as $link) {
                    if ($link->getAttribute('href') === $item['url'] && $plain($link->textContent) === $plain($item['label'])) { $found = true; }
                }
                if (!$found) { throw new RuntimeException('Missing exact navigation destination: '.$item['url']); }
                $navLinks[] = 'http://gramlyze.loc'.$item['url'];
            }
            continue;
        }
        if ($type === 'practice-set') {
            $same($xp->query('//*[@data-sentence-builder]')->length, 1, 'Whole-page sentence-builder count');
            $node = $one('//*[@data-sentence-builder]/ancestor::section[contains(concat(" ",normalize-space(@class)," ")," theory-native-block ")][1]');
        } else {
            $old = $byOrder[$slot + 1] ?? null;
            if ($old === null) { throw new RuntimeException('Missing independently captured continuing block.'); }
            $node = $one('//*[@id="block-'.(int) $old['id'].'"]');
        }
        $text = $learningText($xp, $node); $blockTexts[$label] = ['node' => $node, 'text' => $text];
        foreach (['title', 'intro', 'warning'] as $field) { $contains($text, $data[$field] ?? null, $label.'/'.$field); }
        if ($type === 'forms-grid') {
            foreach ($data['items'] as $n => $item) { foreach (['label', 'title', 'subtitle'] as $field) { $contains($text, $item[$field], $label.'/items/'.$n.'/'.$field); } }
        } elseif ($type === 'usage-panels') {
            $points = $xp->query('.//*[@data-theory-point-panel]', $node);
            $same($points->length, count($data['sections']), $label.' point-panel count');
            foreach ($data['sections'] as $n => $section) { $same($points->item($n)->getAttribute('data-theory-accent'), $section['color'], $label.' semantic accent'); }
            $sectionFields($data['sections'], $text, $label.'/sections');
        } elseif ($type === 'comparison-table') {
            $same($xp->query('.//table', $node)->length, 1, $label.' table count');
            $same($xp->query('.//tbody/tr', $node)->length, count($data['rows']), $label.' table row count');
            foreach ($data['headers'] as $header) { $contains($text, $header, $label.'/header'); }
            foreach ($data['rows'] as $n => $row) { foreach ($row['cells'] as $c => $cell) { $contains($text, $cell, $label.'/rows/'.$n.'/'.$c); } }
        } elseif ($type === 'mistakes-grid') {
            foreach ($data['items'] as $n => $item) {
                foreach (['label', 'title', 'wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) { $contains($text, $item[$field], $label.'/items/'.$n.'/'.$field); }
            }
        } elseif ($type === 'summary-list') {
            foreach ($data['items'] as $n => $item) { $contains($text, $item, $label.'/items/'.$n); }
        } elseif ($type === 'practice-set') {
            $groups = $xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]', $node);
            $same($groups->length, 3, 'Practice own-group count');
            foreach (['selects', 'choices', 'inputs'] as $n => $group) {
                $same(count($data[$group]), 2, 'Frozen practice '.$group.' task count');
                $groupText = $learningText($xp, $groups->item($n));
                foreach ($data[$group] as $t => $task) {
                    $contains($groupText, $task[$group === 'inputs' ? 'after' : 'label'], $label.'/'.$group.'/'.$t.'/condition');
                    if ($group === 'choices') { $contains($groupText, $task['prompt'], $label.'/'.$group.'/'.$t.'/stimulus'); }
                }
                if ($group !== 'inputs') { $same($xp->query('.//label', $groups->item($n))->length, 2, 'Practice '.$group.' SSR task count'); }
            }
            $same($xp->query('.//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]', $node)->length, 2, 'Practice manual/token input count');
            foreach (['select_title', 'select_intro', 'choice_title', 'choice_intro', 'input_title', 'input_intro'] as $field) { $contains($text, $data[$field], $label.'/'.$field); }
            foreach (['title', 'intro', 'footer'] as $field) { $contains($text, $data['linked_practice'][$field], $label.'/linked_practice/'.$field); }
            $source = $target['after']['page']['blocks'][$slot];
            $uuid = M26DetailPackage::uuid($owner, $source, $slot + 1);
            $payload = Acceptance::payload($html, $uuid);
            $same(is_array($payload) ? count($payload) : null, 5, 'Actual linked widget public payload count');
            $same(count(array_unique(array_column($payload, 'id'))), 5, 'Actual linked widget unique question IDs');
            $bank = $audit['targets'][$i]; $same($bank['identity'], $owner, 'Independent bank audit owner');
            $expectedQuestions = [];
            foreach ([...($bank['first_five'] ?? []), ...($bank['existing_selected_two'] ?? [])] as $question) { $expectedQuestions[$question['uuid']] = $question; }
            foreach ($bank['proposed_supplement']['questions'] ?? [] as $question) {
                $expectedQuestions[$question['uuid']] = ['condition_uk' => $question['question'], 'correct_tokens' => $question['tokens_correct']];
            }
            foreach ($bank['expected_selected_uuids'] as $q => $questionUuid) {
                $expected = $expectedQuestions[$questionUuid] ?? null;
                if ($expected === null) { throw new RuntimeException('Independent selected-question source missing.'); }
                $same((string) $payload[$q]['type'], '4', 'Actual widget type'); $same($payload[$q]['level'], $contract['targets'][$i]['level'], 'Actual widget exact level');
                $same($plain($payload[$q]['question']), $plain($expected['condition_uk']), 'Actual widget selected question');
                $lower = static fn (array $tokens): array => array_map(static fn ($token) => mb_strtolower(trim((string) $token), 'UTF-8'), $tokens);
                $same($lower($payload[$q]['correct_tokens']), $lower($expected['correct_tokens']), 'Actual widget ordered correct tokens');
                $available = array_count_values($lower($payload[$q]['options']));
                foreach (array_count_values($lower($payload[$q]['correct_tokens'])) as $token => $count) {
                    if (($available[$token] ?? 0) < $count) { throw new RuntimeException('Actual widget loses repeated correct token instances.'); }
                }
            }
            $blockResults['practice'] = ['own_groups' => 3, 'tasks_per_group' => [2, 2, 2], 'linked_widgets' => 1,
                'linked_payload_count' => 5, 'linked_ids' => array_column($payload, 'id'), 'selected_question_sequence_and_ordered_tokens' => true];
        } else { throw new RuntimeException('Unexpected frozen author block type.'); }
    }
    $same($xp->query('.//details[@data-theory-details]', $main)->length, count($lesson['details']), 'Actual point-detail count');
    $same($xp->query('.//details[@data-theory-details and @open]', $main)->length, 0, 'Initially open point-detail count');
    foreach ($lesson['details'] as $detail) {
        $ownerBlock = $blockTexts[$detail['owner_block_key']] ?? null;
        if ($ownerBlock === null) { throw new RuntimeException('Point detail owner is absent from SSR.'); }
        $details = $xp->query('.//details[@data-theory-details]', $ownerBlock['node']);
        $same($details->length, 1, 'Owned point-detail count');
        $sectionFields($detail['fragment']['data']['sections'], $learningText($xp, $details->item(0)), 'detail/'.$detail['owner_block_key']);
    }
    $results[] = ['identity' => $owner, 'url' => $url, 'status' => $status, 'html_sha256' => hash('sha256', $html),
        'metadata' => $facts, 'stable_learning_resource_identity' => $resources[0],
        'preserved_anchor_count' => count(array_unique($oldHttp['anchors'])), 'missing_old_anchors' => [],
        'basic_and_detail_SSR_learning_fields' => $fieldCount - $lessonFieldStart, 'point_details' => count($lesson['details']),
        'point_details_initially_closed' => true, 'practice' => $blockResults['practice'], 'navigation_exact_local_destinations' => $navLinks];
}
foreach ($inputs as $path => $sha) { if (!hash_equals($sha, $fileHash($path))) { throw new RuntimeException('Reviewed input changed during HTTP acceptance.'); } }
$receipt = ['schema_version' => 1, 'status' => 'read-only-local-http-ssr-accepted', 'started_at' => $started, 'finished_at' => gmdate('c'),
    'base_sha' => Package::BASE_SHA, 'independent_before_sha256' => M53ContentPatch::BEFORE_SHA,
    'master_sha256' => Package::MASTER_SHA, 'patch_sha256' => Package::SOURCE_SHA, 'bank_audit_sha256' => M53BuilderHintPresentation::AUDIT_SHA,
    'transport' => ['anonymous' => true, 'method' => 'GET', 'fixed_loopback' => '127.0.0.1:80', 'redirects' => 0, 'proxy' => false, 'cookies_sent' => false],
    'pages' => $results, 'SSR_learning_fields_checked' => $fieldCount,
    'not_checked' => ['browser layout or visibility', 'client Check/Reset/token interaction', 'client-only feedback presence in SSR',
        'navigation destination availability beyond the exact three requested URLs', 'SQL postconditions (separate diagnostic)'],
    'production_requests' => 0, 'SQL_access' => false, 'content_writes' => 0, 'runtime_or_route_edits' => 0];
$json = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($private.'/'.$name, 'x');
if ($handle === false) { throw new RuntimeException('Exclusive receipt creation failed.'); }
try { if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) { throw new RuntimeException('Private receipt write incomplete.'); } }
finally { fclose($handle); }
echo json_encode(['status' => $receipt['status'], 'file' => $name, 'sha256' => $fileHash($private.'/'.$name), 'HTTP_200' => count($results),
    'SSR_learning_fields_checked' => $fieldCount, 'browser_interactions_checked' => false, 'SQL_access' => false], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
