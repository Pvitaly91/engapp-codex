<?php

// Finite technical projection of accepted M19 author content; no Laravel/DB boot.
// Capture is exclusive. Projection requires actual read-only linked-bank evidence.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$baseSha = 'd2581e3bbcc8a5b89d0a93a454bb010d3c4606eb';
$names = ['PassiveReportingStructures', 'ComplexPassiveAndCausative', 'ComplexPassiveImpersonalStyle'];
$categories = ['PassiveVoice', 'PassiveVoice', 'PassiveVoice'];
$ancestries = ['passive-voice', 'passive-voice', 'passive-voice'];
$slugs = ['passive-reporting-structures', 'complex-passive-and-causative', 'complex-passive-impersonal-style'];
$levels = ['C1', 'C1', 'C2'];
$blobs = ['d6108186897211e6b8c705f805e60403e5a0b5b0', 'efa7568192b82787e2091d19cb5983be55c68224', '52163bdf7870623bccba1ed1ad5687cfcd5cf006'];
$paths = array_map(fn ($n, $category) => 'database/seeders/Page_V3/'.$category.'/'.$n.'TheorySeeder/definition.json', $names, $categories);
$manifestPath = $root.'/database/content-patches/m35-m19-passive-reporting-before.json';

function m35Json(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}

if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('M35 before manifest already exists.'); }
    $manifest = ['base_sha' => $baseSha, 'targets' => []];
    foreach ($paths as $i => $path) {
        $bytes = file_get_contents($root.'/'.$path);
        $repositoryBytes = str_replace("\r\n", "\n", $bytes);
        $gitBlob = hash('sha1', 'blob '.strlen($repositoryBytes)."\0".$repositoryBytes);
        if ($gitBlob !== $blobs[$i]) { throw new RuntimeException('M19 accepted blob differs: '.$path); }
        $manifest['targets'][] = ['path' => $path, 'source_sha256' => hash('sha256', $bytes), 'source_git_blob' => $gitBlob,
            'before' => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)];
    }
    $file = fopen($manifestPath, 'xb');
    if ($file === false) { throw new RuntimeException('M35 exclusive capture failed.'); }
    $bytes = m35Json($manifest);
    if (fwrite($file, $bytes) !== strlen($bytes)) { fclose($file); throw new RuntimeException('M35 capture incomplete.'); }
    fclose($file);
    echo "Captured three accepted M19 sources, not DB rows.\n";
    exit;
}

if (($argv[1] ?? '') !== '--project') { throw new RuntimeException('Use --capture-before once or --project --linked-banks FILE.'); }
$bankFlag = array_search('--linked-banks', $argv, true);
if ($bankFlag === false || empty($argv[$bankFlag + 1])) { throw new RuntimeException('Read-only verified linked-bank input required; never infer classes.'); }
$bankRecords = json_decode(file_get_contents($argv[$bankFlag + 1]), true, flags: JSON_THROW_ON_ERROR);
if (isset($bankRecords['linked_banks'])) {
    $bankRecords = array_map(fn ($identity, $record) => ['identity' => $identity] + $record,
        array_keys($bankRecords['linked_banks']), array_values($bankRecords['linked_banks']));
}
if (!is_array($bankRecords) || count($bankRecords) !== 3) { throw new RuntimeException('Exactly three proven linked-bank entries required.'); }
$banks = [];
foreach ($bankRecords as $record) {
    if (!is_array($record) || empty($record['identity']) || empty($record['seeder_class']) || ($record['question_type'] ?? null) !== '4'
        || empty($record['level']) || isset($banks[$record['identity']]) || empty($record['question_ids']) || count(array_unique($record['question_ids'])) !== count($record['question_ids'])) {
        throw new RuntimeException('Missing/duplicate linked-bank evidence.');
    }
    $banks[$record['identity']] = $record;
}
$manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
if ($manifest['base_sha'] !== $baseSha || count($manifest['targets']) !== 3) { throw new RuntimeException('M35 finite manifest differs.'); }
$previousTargets = [];
$replaceFlag = array_search('--replace-projection-sha', $argv, true);
if ($replaceFlag !== false) {
    $previousBytes = file_get_contents($root.'/database/content-patches/m35-m19-passive-reporting.v1.json');
    if (!isset($argv[$replaceFlag + 1]) || !hash_equals($argv[$replaceFlag + 1], hash('sha256', $previousBytes))) {
        throw new RuntimeException('M35 prior generated projection hash differs.');
    }
    foreach (json_decode($previousBytes, true, flags: JSON_THROW_ON_ERROR)['targets'] as $previous) {
        $previousTargets[$previous['identity']] = $previous['after'];
    }
}

function m35Clean(string $html): string { return preg_replace('/\sstyle="[^"]*"/', '', $html); }
function m35Inner(DOMNode $node): string
{
    $out = '';
    foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); }
    return m35Clean(trim($out));
}
function m35Plain(string $html): string { return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }
// Diagnostic whitespace tokens: block/line breaks separate words; inline punctuation stays attached.
// This does not alter answer normalization, author HTML or semantic disclosure decisions.
function m35DiagnosticPlain(string $html): string
{
    $separated = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
    return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($separated), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}
function m35Words(string $html): int { return preg_match_all('/\S+/u', m35DiagnosticPlain($html)); }
function m35Sentences(string $html): int { return preg_match_all('/[.!?](?:\s|$)/u', m35DiagnosticPlain($html)); }
function m35Prose(array $nodes): string
{
    return implode('<br><br>', array_map(fn ($n) => in_array($n->nodeName, ['p', 'li'], true)
        ? m35Inner($n) : m35Clean($n->ownerDocument->saveHTML($n)), $nodes));
}
function m35Sections(string $html): array
{
    $dom = new Dom('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
    $groups = []; $i = -1;
    foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
        if (!$node instanceof DOMElement) { continue; }
        if ($node->tagName === 'h4') { $groups[++$i] = ['title' => trim($node->textContent), 'nodes' => []]; }
        elseif ($node->tagName === 'section') {
            $groups[++$i] = ['title' => trim($node->getElementsByTagName('h4')->item(0)->textContent),
                'practice' => $node->getAttribute('id'), 'source_node' => $node, 'nodes' => []];
        } else { $groups[$i]['nodes'][] = $node; }
    }
    if (count($groups) !== 8) { throw new RuntimeException('M19 must have six teaching sections, self-check and continuation.'); }
    return $groups;
}
function m35Points(array $nodes): array
{
    $points = [];
    foreach ($nodes as $node) {
        if (in_array($node->nodeName, ['ul', 'ol'], true)) {
            foreach ($node->childNodes as $li) {
                if ($li instanceof DOMElement && $li->tagName === 'li') { $points[] = ['basic' => m35Inner($li), 'detail' => '']; }
            }
        } else {
            $points[] = ['basic' => $node->nodeName === 'p' ? m35Inner($node) : m35Clean($node->ownerDocument->saveHTML($node)), 'detail' => ''];
        }
    }
    return $points;
}
function m35Table(DOMElement $wrapper, int $columns): array
{
    $headers = array_map(fn ($n) => trim($n->textContent), iterator_to_array($wrapper->getElementsByTagName('th')));
    if (count($headers) !== $columns) { throw new RuntimeException('M19 accepted table columns differ.'); }
    $rows = [];
    foreach ($wrapper->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr) {
        $cells = [];
        foreach ($tr->childNodes as $cell) { if ($cell instanceof DOMElement && $cell->tagName === 'td') { $cells[] = m35Inner($cell); } }
        if (count($cells) !== $columns) { throw new RuntimeException('M19 accepted table row differs.'); }
        $rows[] = ['cells' => $cells];
    }
    $table = $wrapper->getElementsByTagName('table')->item(0);
    if (!preg_match('/min-width:(\\d+)px/', $table->getAttribute('style'), $width)) { throw new RuntimeException('M19 accepted table width missing.'); }
    $minimums = [];
    foreach ($table->getElementsByTagName('th') as $header) {
        $minimums[] = preg_match('/min-width:(\\d+)px/', $header->getAttribute('style'), $column) ? (int) $column[1] : null;
    }
    return ['headers' => $headers, 'rows' => $rows, 'table_min_width' => (int) $width[1], 'column_min_widths' => $minimums];
}
function m35AuthorChecks(DOMElement $source): array
{
    $xp = new DOMXPath($source->ownerDocument);
    $prompts = $xp->query('.//ol[@data-self-checks]/li', $source);
    $keys = $xp->query('.//ol[@data-self-check-answers]/li', $source);
    if ($prompts->length !== 6 || $keys->length !== 6) { throw new RuntimeException('M19 exact six author cases/keys required.'); }
    return ['section_title' => trim($source->getElementsByTagName('h4')->item(0)->textContent),
        'intro' => $source->getElementsByTagName('p')->length > 0 ? m35Inner($source->getElementsByTagName('p')->item(0)) : '',
        'prompts' => array_map('m35Inner', iterator_to_array($prompts)),
        'title' => trim($source->getElementsByTagName('summary')->item(0)->textContent),
        'answers' => array_map('m35Inner', iterator_to_array($keys))];
}
// Mechanical finite token groups; they only rearrange words of the accepted answer.
function m35TokenInput(string $answer, int $case, array $accepted = []): array
{
    $chunks = array_map(fn ($words) => implode(' ', $words), array_chunk(preg_split('/\s+/u', $answer), 3));
    return ['before' => implode(' / ', array_reverse($chunks)), 'm35_token_groups' => array_reverse($chunks), 'answer' => $answer,
        'accepted' => $accepted !== [] ? $accepted : [$answer], 'source_case' => $case, 'punctuation_sensitive' => true];
}
require __DIR__.'/m35-practice-projection.php';

// Explicit semantic decisions. Length is diagnostic, never a runtime threshold.
$reasons = [
    ['Послідовність перенесення і розмежування джерела та підмета є основним алгоритмом.',
        'Повна таблиця часових відношень із перекладами потрібна до будь-якого кліку.',
        'Майбутнє очікування, backshift і процес є центральними часовими відмінностями.',
        'Збереження конкретного джерела є основною умовою точного повідомлення.',
        'Каркаси that/to, узгодження і may — основні правила керування, не optional detail.',
        'Повний точний звіт, переклад, джерело й непідтвердженість є core contrast.'],
    ['Організатор, виконавець, об’єкт і відома домовленість — основне розмежування.',
        'Have person do/get person to do та V3 є основним правилом керування.',
        'Had had є повною перфектною causative моделлю, потрібною поруч із Past Perfect.',
        'Питання, заперечення й природні скорочення завершують основні форми.',
        'Коротка контекстна ремарка про регістр get не виправдовує окремого кліку.',
        'Збереження плану без вигаданого результату є головною межею секції.'],
    ['Коротке нагадування that/raised subject продовжує основне розмежування ролей.',
        'Важкий continuous passive і ясна that-альтернатива є центральною межею читабельності.',
        'Been+adjective проти been+V3 — основний контраст, не приховане поглиблення.',
        'Явне незнання і область not є центральною відмінністю значень.',
        'Два пасивні рівні та збереження процесу не можна ховати за кліком.',
        'Повний критерій точного редагування з атрибуцією і невигаданою безпекою — основний висновок.'],
];
$retained = []; // All accepted material is core, continuation or a short caveat; no invented depth.
$package = ['version' => 1, 'targets' => []];
foreach ($manifest['targets'] as $i => $target) {
    if ($target['path'] !== $paths[$i] || $target['source_git_blob'] !== $blobs[$i]) { throw new RuntimeException('M19 source allowlist differs.'); }
    $before = $target['before']; $identity = $before['seeder']['class'];
    if ($before['slug'] !== $slugs[$i] || !isset($banks[$identity]) || $banks[$identity]['level'] !== $levels[$i]
        || $before['page']['locale'] !== 'uk' || $before['type'] !== 'theory' || $before['page']['category']['slug'] !== $ancestries[$i]) {
        throw new RuntimeException('M19 page/bank identity/level/category differs.');
    }
    $groups = m35Sections($before['page']['blocks'][1]['body']);
    $blocks = [$before['page']['blocks'][0]]; $plans = []; $audit = [];
    foreach ($groups as $s => $group) {
        $key = 'm35-'.['reporting', 'causative', 'impersonal'][$i].'-section-'.($s + 1);
        $nodes = $group['nodes']; $points = []; $extra = []; $type = 'usage-panels';
        if (isset($group['practice'])) {
            $type = 'practice-set'; $data = m35Practice($i, m35AuthorChecks($group['source_node']), $banks[$identity]);
            $extra['legacy_practice_id'] = $group['practice'];
        } elseif ($s === 7) {
            $type = 'summary-list'; $data = ['title' => $group['title'], 'items' => array_map('m35Inner', $nodes)];
        } else {
            $tableIndex = null;
            foreach ($nodes as $n => $node) { if ($node instanceof DOMElement && $node->getElementsByTagName('table')->length > 0) { $tableIndex = $n; } }
            $retain = in_array([$i, $s], $retained, true);
            if ($tableIndex !== null) {
                $type = 'comparison-table'; $data = ['title' => $group['title']] + m35Table($nodes[$tableIndex], $i === 0 ? 4 : 3);
                $data['intro'] = m35Prose(array_slice($nodes, 0, $tableIndex));
                $data['outro'] = m35Prose(array_slice($nodes, $tableIndex + 1));
            } else {
                $points = m35Points($nodes);
                $data = ['title' => $group['title'], 'sections' => array_map(fn ($p) =>
                    ['description' => $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')], $points)];
            }
            $paragraphs = array_values(array_filter($nodes, fn ($n) => $n->nodeName === 'p'));
            $candidateNode = $paragraphs !== [] ? $paragraphs[count($paragraphs) - 1] : $nodes[count($nodes) - 1];
            $candidate = m35Inner($candidateNode);
            $visible = m35Prose($nodes);
            $point = $candidateNode->nodeName === 'p' ? 'source-final-paragraph' : 'source-final-list';
            $audit[] = ['source_section' => $s + 1, 'point' => $point,
                'basic_words' => m35Words($visible) - m35Words($candidate), 'detail_words' => m35Words($candidate),
                'detail_sentences' => m35Sentences($candidate), 'candidate_html' => $candidate,
                'decision' => $retain ? 'meaningful_detail' : 'visible_basic', 'reason' => $reasons[$i][$s]];
        }
        $data['m35_v1'] = ['key' => $key, 'legacy_section' => $s + 1] + $extra;
        $block = $s === 0 ? $before['page']['blocks'][1]
            : ['column' => 'left', 'heading' => null, 'level' => null, 'uuid_key' => $key, 'inherit_base_tags' => false, 'tags' => []];
        $block['type'] = $type; $block['body'] = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if ($type === 'practice-set') { $block['level'] = $levels[$i]; }
        $blocks[] = $block;
        $plans[] = ['key' => $key, 'source_section' => $s + 1, 'type' => $type, 'points' => $points];
    }
    $after = $before; $after['page']['blocks'] = $blocks;
    $current = json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR);
    if ($current !== $before && $current !== $after && $current !== ($previousTargets[$identity] ?? null)) {
        throw new RuntimeException('M35 refuses to overwrite a manually changed definition.');
    }
    $package['targets'][] = ['path' => $target['path'], 'identity' => $identity, 'slug' => $before['slug'],
        'ancestry' => [$ancestries[$i]], 'after' => $after, 'plans' => $plans, 'detail_quality_audit' => $audit];
}
foreach ($package['targets'] as $target) {
    if (file_put_contents($root.'/'.$target['path'], m35Json($target['after'])) === false) { throw new RuntimeException('M35 definition projection failed.'); }
}
if (file_put_contents($root.'/database/content-patches/m35-m19-passive-reporting.v1.json', m35Json($package)) === false) {
    throw new RuntimeException('M35 package projection failed.');
}
echo "Projected three M19 sources into eight native blocks/page; meaningful details 0/0/0, exact six cases each.\n";
