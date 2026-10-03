<?php

// Mechanical, finite presentation of frozen M14 author content; never boots Laravel/DB.
// --capture-before is exclusive. --project requires read-only verified linked-bank input.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$baseSha = 'c308b328ece596d92f91943d9dfb2495fc9af10a';
$names = ['ParticipleClausesBasics', 'ParticipleClauses', 'AdvancedParticipleAndAbsoluteClauses'];
$slugs = ['participle-clauses-basics', 'participle-clauses', 'advanced-participle-and-absolute-clauses'];
$levels = ['B2', 'C1', 'C2'];
$blobs = ['9e2b8dc2d32937332b0fc931c1cad38f9b1202a9', '47b17a940752a54aeda05bf1362febbe11e20e26', '0c9658497fd31b001d92c8a7f42f63f132723a7e'];
$paths = array_map(fn ($n) => 'database/seeders/Page_V3/ClausesAndLinkingWords/'.$n.'TheorySeeder/definition.json', $names);
$manifestPath = $root.'/database/content-patches/m30-m14-participle-clauses-before.json';
$jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

function m30Json(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}

if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('M30 before manifest already exists.'); }
    $manifest = ['base_sha' => $baseSha, 'targets' => []];
    foreach ($paths as $i => $path) {
        $bytes = file_get_contents($root.'/'.$path);
        // Git's Windows text checkout may be CRLF; the accepted blob is repository LF.
        // Raw checkout bytes keep their independent SHA-256 in the manifest below.
        $repositoryBytes = str_replace("\r\n", "\n", $bytes);
        $gitBlob = hash('sha1', 'blob '.strlen($repositoryBytes)."\0".$repositoryBytes);
        if ($gitBlob !== $blobs[$i]) { throw new RuntimeException('M14 accepted blob differs: '.$path); }
        $manifest['targets'][] = ['path' => $path, 'source_sha256' => hash('sha256', $bytes), 'source_git_blob' => $gitBlob,
            'before' => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)];
    }
    if (file_put_contents($manifestPath, m30Json($manifest), LOCK_EX) === false) { throw new RuntimeException('M30 capture failed.'); }
    echo "Captured three exact versioned M14 sources, not DB rows.\n";
    exit;
}

if (($argv[1] ?? '') !== '--project') { throw new RuntimeException('Use --capture-before once or --project --linked-banks FILE.'); }
$bankFlag = array_search('--linked-banks', $argv, true);
if ($bankFlag === false || empty($argv[$bankFlag + 1])) { throw new RuntimeException('Read-only verified linked-bank input is required; never infer classes.'); }
$bankRecords = json_decode(file_get_contents($argv[$bankFlag + 1]), true, flags: JSON_THROW_ON_ERROR);
if (isset($bankRecords['linked_banks'])) {
    $bankRecords = array_map(fn ($identity, $record) => ['identity' => $identity] + $record,
        array_keys($bankRecords['linked_banks']), array_values($bankRecords['linked_banks']));
}
if (!is_array($bankRecords) || count($bankRecords) !== 3) { throw new RuntimeException('Exactly three proven linked-bank entries required.'); }
$banks = [];
foreach ($bankRecords as $record) {
    if (!is_array($record) || empty($record['identity']) || empty($record['seeder_class']) || empty($record['question_type'])
        || empty($record['level']) || isset($banks[$record['identity']])) { throw new RuntimeException('Missing/duplicate linked-bank evidence.'); }
    $banks[$record['identity']] = $record;
}
$manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
if ($manifest['base_sha'] !== $baseSha || count($manifest['targets']) !== 3) { throw new RuntimeException('M30 finite source manifest differs.'); }

function m30Clean(string $html): string { return preg_replace('/\sstyle="[^"]*"/', '', $html); }
function m30Inner(DOMNode $node): string
{
    $out = '';
    foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); }
    return m30Clean(trim($out));
}
function m30Plain(string $html): string { return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }
function m30Words(string $html): int { return preg_match_all('/\S+/u', m30Plain($html)); }
function m30Sentences(string $html): int { return preg_match_all('/[.!?](?:\s|$)/u', m30Plain($html)); }
function m30Prose(array $nodes): string
{
    return implode('<br><br>', array_map(fn ($n) => in_array($n->nodeName, ['p', 'li'], true)
        ? m30Inner($n) : m30Clean($n->ownerDocument->saveHTML($n)), $nodes));
}
function m30Sections(string $html): array
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
    if (count($groups) !== 8) { throw new RuntimeException('M14 must have six teaching sections, one self-check and one continuation.'); }
    return $groups;
}
function m30Points(array $nodes): array
{
    $points = [];
    foreach ($nodes as $node) {
        if (in_array($node->nodeName, ['ul', 'ol'], true)) {
            foreach ($node->childNodes as $li) {
                if ($li instanceof DOMElement && $li->tagName === 'li') { $points[] = ['basic' => m30Inner($li), 'detail' => '']; }
            }
        } else {
            $points[] = ['basic' => $node->nodeName === 'p' ? m30Inner($node) : m30Clean($node->ownerDocument->saveHTML($node)), 'detail' => ''];
        }
    }
    return $points;
}
function m30Table(DOMElement $wrapper): array
{
    $headers = array_map(fn ($n) => trim($n->textContent), iterator_to_array($wrapper->getElementsByTagName('th')));
    if (count($headers) !== 4) { throw new RuntimeException('M14 accepted four-column table differs.'); }
    $rows = [];
    foreach ($wrapper->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr) {
        $cells = [];
        foreach ($tr->childNodes as $cell) { if ($cell instanceof DOMElement && $cell->tagName === 'td') { $cells[] = m30Inner($cell); } }
        if (count($cells) !== 4) { throw new RuntimeException('M14 accepted table row differs.'); }
        $rows[] = ['cells' => $cells];
    }
    return ['headers' => $headers, 'rows' => $rows];
}
function m30AuthorChecks(DOMElement $source): array
{
    $xp = new DOMXPath($source->ownerDocument);
    $prompts = $xp->query('.//ol[@data-self-checks]/li', $source);
    $keys = $xp->query('.//ol[@data-self-check-answers]/li', $source);
    if ($prompts->length !== 6 || $keys->length !== 6) { throw new RuntimeException('M14 exact six author cases/keys required.'); }
    return ['section_title' => trim($source->getElementsByTagName('h4')->item(0)->textContent),
        'intro' => m30Inner($source->getElementsByTagName('p')->item(0)),
        'prompts' => array_map('m30Inner', iterator_to_array($prompts)),
        'title' => trim($source->getElementsByTagName('summary')->item(0)->textContent),
        'answers' => array_map('m30Inner', iterator_to_array($keys))];
}
function m30Practice(int $i, array $author, array $bank): array
{
    $p = ['title' => 'Практика', 'select_title' => 'Вправа 1. Обери правильну відповідь',
        'choice_title' => 'Вправа 2. Обери правильний варіант', 'input_title' => 'Вправа 3. Побудуй речення',
        'choice_options' => ['a', 'b'], 'author_self_check' => $author,
        'linked_practice' => ['source' => 'theory_links', 'question_types' => [(string) $bank['question_type']],
            'seeder_classes' => [$bank['seeder_class']], 'title' => 'Вправа 4. Побудуй речення',
            'intro' => 'Склади англійське речення за українським.', 'footer' => 'Завдання з наявного тесту цієї сторінки.']];
    if ($i === 0) {
        $p['selects'] = [
            ['label' => 'The woman ___ a red folder is our guide.<br>Жінка, яка несе червону папку, — наша гідеса.',
                'options' => ['carrying', 'is'], 'answer' => 'carrying', 'source_case' => 1],
            ['label' => $author['prompts'][1], 'options' => ['printing', 'printed'], 'answer' => 'printed', 'source_case' => 2]];
        $p['choices'] = [
            ['label' => $author['prompts'][3], 'prompt' => 'A) the bus<br>B) we', 'answer' => 'a', 'source_case' => 4],
            ['label' => $author['prompts'][4], 'prompt' => 'A) Коректне приєднання звороту.<br>B) Помилкове приєднання: за будовою шафку відкриває тарілка.',
                'answer' => 'b', 'source_case' => 5]];
        $p['inputs'] = [
            ['before' => 'need a ladder. / The people / repairing the roof', 'after' => $author['prompts'][2],
                'answer' => 'The people repairing the roof need a ladder.', 'source_case' => 3],
            ['before' => 'went out. / While Taras / the lights / was reading / the message,', 'after' => $author['prompts'][5],
                'answer' => 'While Taras was reading the message, the lights went out.', 'source_case' => 6]];
    } elseif ($i === 1) {
        $p['selects'] = [
            ['label' => $author['prompts'][0], 'options' => ['Sorting', 'Having sorted'], 'answer' => 'Having sorted', 'source_case' => 1],
            ['label' => $author['prompts'][1], 'options' => ['Having inspected', 'Having been inspected'], 'answer' => 'Having been inspected', 'source_case' => 2]];
        $p['choices'] = [
            ['label' => $author['prompts'][3], 'prompt' => 'A) Перше having означає «маючи»; друге having obtained — perfect. After obtaining також зберігає порядок.<br>B) Перше having — perfect; друге having obtained означає «маючи».',
                'answer' => 'a', 'source_case' => 4],
            ['label' => $author['prompts'][5], 'prompt' => 'A) Так, скорочення зберігає наведені факти.<br>B) Ні, губиться might, а damaged приєднується до оператора.',
                'answer' => 'b', 'source_case' => 6]];
        $p['inputs'] = [
            ['before' => 'the old route. / seen / Roman took / Not having / the revised map,', 'after' => $author['prompts'][2],
                'answer' => 'Not having seen the revised map, Roman took the old route.', 'source_case' => 3],
            ['before' => 'he stopped / Because Pavlo / the machine. / saw / the warning light,', 'after' => $author['prompts'][4],
                'answer' => 'Because Pavlo saw the warning light, he stopped the machine.', 'source_case' => 5]];
    } else {
        $p['selects'] = [
            ['label' => $author['prompts'][2], 'options' => ['packing', 'packed'], 'answer' => 'packed', 'source_case' => 3],
            ['label' => $author['prompts'][3], 'options' => ['arriving', 'having arrived'], 'answer' => 'having arrived', 'source_case' => 4]];
        $p['choices'] = [
            ['label' => $author['prompts'][0], 'prompt' => 'A) Абсолютна конструкція з власним підметом the audience.<br>B) Помилкове приєднання: watching приписується піаністові.',
                'answer' => 'a', 'source_case' => 1],
            ['label' => $author['prompts'][1], 'prompt' => 'A) Перше — абсолютна конструкція; друге — помилкове приєднання.<br>B) Перше — помилкове приєднання; друге — абсолютна конструкція.',
                'answer' => 'a', 'source_case' => 2]];
        $p['inputs'] = [
            ['before' => 'the guard / switched off, / The lights / locked the hall. / having been', 'after' => $author['prompts'][4],
                'answer' => 'The lights having been switched off, the guard locked the hall.', 'source_case' => 5, 'punctuation_sensitive' => true],
            ['before' => 'The conductor / were packed. / After the rehearsal / The players / thanked everyone. / had ended, / were tired. / the instruments',
                'after' => $author['prompts'][5], 'answer' => 'After the rehearsal had ended, the instruments were packed. The players were tired. The conductor thanked everyone.',
                'source_case' => 6, 'punctuation_sensitive' => true]];
    }
    foreach (['selects', 'choices', 'inputs'] as $kind) {
        foreach ($p[$kind] as &$item) {
            $case = $item['source_case'];
            unset($item['source_case']);
            $item['source_index'] = $case;
            $item['context'] = $author['prompts'][$case - 1];
            $item['author_explanation'] = $author['answers'][$case - 1];
            // The exact case appears once in its interactive group, not repeated as label/after.
            if ($kind === 'inputs') { unset($item['after']); }
            else { $item['label'] = ''; }
        }
        unset($item);
    }
    return $p;
}

// Exact semantic decisions: candidate end-paragraphs stay visible; length is diagnostic only.
$reasons = [
    ['Core reduction warning preserves participants/modality; not optional depth.', 'Core active/passive distinction and state caveat explain the form contrast.',
        'Core comma rule immediately explains the visible comparison table.', 'Scope caveat prevents confusing adverbial, noun-modifying and absolute constructions.',
        'Core repair identifies the known actor without inventing facts.', 'Short summary checklist/bridge, not separate deepening.'],
    ['Core warning clarifies time versus cause for the four visible relations.', 'Core perfect/passive distinction preserves actor roles in the visible table.',
        'Core after/perfect comparison completes the lexical-having contrast.', 'Core negative passive perfect example/model, not an extra optional addendum.',
        'Short continuation summarises when full clauses are clearer.', 'Core analysis identifies each relation in the visible cohesion paragraph.'],
    ['Core absolute-versus-finite distinction completes the opening definition.', 'Short perfect/V3 contextual caveat belongs beside the model table.',
        'Core dangling repair explains the known-versus-unknown reader issue.', 'Short function/register caveat forbids mechanical removal of with.',
        'Core noun-modifying contrast explains why its comma differs.', 'Short writing-load guidance completes the cohesion paragraph.'],
];
$package = ['version' => 1, 'targets' => []];
foreach ($manifest['targets'] as $i => $target) {
    if ($target['path'] !== $paths[$i] || $target['source_git_blob'] !== $blobs[$i]) { throw new RuntimeException('M14 source allowlist differs.'); }
    $before = $target['before']; $identity = $before['seeder']['class'];
    if ($before['slug'] !== $slugs[$i] || !isset($banks[$identity]) || $banks[$identity]['level'] !== $levels[$i]) {
        throw new RuntimeException('M14 page/bank identity/level differs.');
    }
    $groups = m30Sections($before['page']['blocks'][1]['body']);
    $blocks = [$before['page']['blocks'][0]]; $plans = []; $audit = [];
    foreach ($groups as $s => $group) {
        $key = 'm30-'.['b2', 'c1', 'c2'][$i].'-section-'.($s + 1);
        $nodes = $group['nodes']; $points = []; $extra = []; $type = 'usage-panels';
        if (isset($group['practice'])) {
            $type = 'practice-set'; $data = m30Practice($i, m30AuthorChecks($group['source_node']), $banks[$identity]);
            $extra['legacy_practice_id'] = $group['practice'];
        } elseif ($s === 7) {
            $type = 'summary-list'; $data = ['title' => $group['title'], 'items' => array_map('m30Inner', $nodes)];
        } else {
            $tableIndex = null;
            foreach ($nodes as $n => $node) { if ($node instanceof DOMElement && $node->getElementsByTagName('table')->length > 0) { $tableIndex = $n; } }
            if ($tableIndex !== null) {
                $type = 'comparison-table'; $data = ['title' => $group['title']] + m30Table($nodes[$tableIndex]);
                $data['intro'] = m30Prose(array_slice($nodes, 0, $tableIndex));
                $data['outro'] = m30Prose(array_slice($nodes, $tableIndex + 1));
            } else {
                $points = m30Points($nodes);
                $data = ['title' => $group['title'], 'sections' => array_map(fn ($p) => ['description' => $p['basic']], $points)];
            }
            $paragraphs = array_values(array_filter($nodes, fn ($n) => $n->nodeName === 'p'));
            $candidate = m30Inner($paragraphs[count($paragraphs) - 1]);
            $visible = m30Prose($nodes);
            $audit[] = ['source_section' => $s + 1, 'point' => 'source-final-paragraph',
                'basic_words' => m30Words($visible) - m30Words($candidate), 'detail_words' => m30Words($candidate),
                'detail_sentences' => m30Sentences($candidate), 'candidate_html' => $candidate,
                'decision' => 'visible_basic', 'reason' => $reasons[$i][$s]];
        }
        $data['m30_v1'] = ['key' => $key, 'legacy_section' => $s + 1] + $extra;
        $block = $s === 0 ? $before['page']['blocks'][1]
            : ['column' => 'left', 'heading' => null, 'level' => null, 'uuid_key' => $key, 'inherit_base_tags' => false, 'tags' => []];
        $block['type'] = $type;
        $block['body'] = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if ($type === 'practice-set') { $block['level'] = $levels[$i]; }
        $blocks[] = $block;
        $plans[] = ['key' => $key, 'source_section' => $s + 1, 'type' => $type, 'points' => $points];
    }
    $after = $before; $after['page']['blocks'] = $blocks;
    $package['targets'][] = ['path' => $target['path'], 'identity' => $identity, 'slug' => $before['slug'],
        'ancestry' => ['clauses-and-linking-words'], 'after' => $after, 'plans' => $plans, 'detail_quality_audit' => $audit];
    if (file_put_contents($root.'/'.$target['path'], m30Json($after)) === false) { throw new RuntimeException('M30 definition projection failed.'); }
}
if (file_put_contents($root.'/database/content-patches/m30-m14-participle-clauses.v1.json', m30Json($package)) === false) {
    throw new RuntimeException('M30 package projection failed.');
}
echo "Projected three exact M14 sources into eight native blocks each; all author core remains visible, no point disclosures.\n";
