<?php

// Finite technical projection of accepted M21 author content; no Laravel/DB boot.
// Capture is exclusive. Projection requires actual read-only linked-bank evidence.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$baseSha = 'f6f1f22e00103118dd1641bbcf29ed4e69a9a33a';
$names = ['AdvancedGerundInfinitivePatterns', 'ComplexRelativeClauses', 'InversionAfterNegativeAdverbials'];
$categories = ['VerbPatterns', 'RelativeClauses', 'BasicGrammar/WordOrder'];
$ancestries = [['verb-patterns'], ['relative-clauses'], ['basic-grammar', 'word-order']];
$slugs = ['advanced-gerund-infinitive-patterns', 'complex-relative-clauses', 'inversion-after-negative-adverbials'];
$levels = ['B2', 'C1', 'C1'];
$blobs = ['166cf0821826cf55a90347ccb57db61eba91884a', 'e6ac6befb2eb11ec3f1584cfc4b59054ce1212ca', '72e194f72431210c1c80708b192064809a07468b'];
$paths = array_map(fn ($n, $category) => 'database/seeders/Page_V3/'.$category.'/'.$n.'TheorySeeder/definition.json', $names, $categories);
$manifestPath = $root.'/database/content-patches/m37-m21-grammar-structures-before.json';

function m37Json(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}

if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('M37 before manifest already exists.'); }
    $manifest = ['base_sha' => $baseSha, 'targets' => []];
    foreach ($paths as $i => $path) {
        $bytes = file_get_contents($root.'/'.$path);
        $repositoryBytes = str_replace("\r\n", "\n", $bytes);
        $gitBlob = hash('sha1', 'blob '.strlen($repositoryBytes)."\0".$repositoryBytes);
        if ($gitBlob !== $blobs[$i]) { throw new RuntimeException('M21 accepted blob differs: '.$path); }
        $manifest['targets'][] = ['path' => $path, 'source_sha256' => hash('sha256', $bytes), 'source_git_blob' => $gitBlob,
            'before' => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)];
    }
    $file = fopen($manifestPath, 'xb');
    if ($file === false) { throw new RuntimeException('M37 exclusive capture failed.'); }
    $bytes = m37Json($manifest);
    if (fwrite($file, $bytes) !== strlen($bytes)) { fclose($file); throw new RuntimeException('M37 capture incomplete.'); }
    fclose($file);
    echo "Captured three accepted M21 sources, not DB rows.\n";
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
if ($manifest['base_sha'] !== $baseSha || count($manifest['targets']) !== 3) { throw new RuntimeException('M37 finite manifest differs.'); }
$previousTargets = [];
$replaceFlag = array_search('--replace-projection-sha', $argv, true);
if ($replaceFlag !== false) {
    $previousBytes = file_get_contents($root.'/database/content-patches/m37-m21-grammar-structures.v1.json');
    if (!isset($argv[$replaceFlag + 1]) || !hash_equals($argv[$replaceFlag + 1], hash('sha256', $previousBytes))) {
        throw new RuntimeException('M37 prior generated projection hash differs.');
    }
    foreach (json_decode($previousBytes, true, flags: JSON_THROW_ON_ERROR)['targets'] as $previous) {
        $previousTargets[$previous['identity']] = $previous['after'];
    }
}

function m37Clean(string $html): string { return preg_replace('/\sstyle="[^"]*"/', '', $html); }
function m37Inner(DOMNode $node): string
{
    $out = '';
    foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); }
    return m37Clean(trim($out));
}
function m37Plain(string $html): string
{
    // Preserve word boundaries between author paragraphs in derived UI labels.
    $html = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
    return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}
// Diagnostic whitespace tokens: block/line breaks separate words; inline punctuation stays attached.
// This does not alter answer normalization, author HTML or semantic disclosure decisions.
function m37DiagnosticPlain(string $html): string
{
    $separated = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
    return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($separated), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}
function m37Words(string $html): int { return preg_match_all('/\S+/u', m37DiagnosticPlain($html)); }
function m37Sentences(string $html): int { return preg_match_all('/[.!?](?:\s|$)/u', m37DiagnosticPlain($html)); }
function m37Prose(array $nodes): string
{
    return implode('<br><br>', array_map(fn ($n) => in_array($n->nodeName, ['p', 'li'], true)
        ? m37Inner($n) : m37Clean($n->ownerDocument->saveHTML($n)), $nodes));
}
function m37Sections(string $html): array
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
    if (count($groups) !== 8) { throw new RuntimeException('M21 must have six teaching sections, self-check and continuation.'); }
    return $groups;
}
function m37Points(array $nodes): array
{
    $points = [];
    foreach ($nodes as $node) {
        if (in_array($node->nodeName, ['ul', 'ol'], true)) {
            foreach ($node->childNodes as $li) {
                if ($li instanceof DOMElement && $li->tagName === 'li') { $points[] = ['basic' => m37Inner($li), 'detail' => '']; }
            }
        } else {
            $points[] = ['basic' => $node->nodeName === 'p' ? m37Inner($node) : m37Clean($node->ownerDocument->saveHTML($node)), 'detail' => ''];
        }
    }
    return $points;
}
function m37Table(DOMElement $wrapper, int $columns): array
{
    $headers = array_map(fn ($n) => trim($n->textContent), iterator_to_array($wrapper->getElementsByTagName('th')));
    if (count($headers) !== $columns) { throw new RuntimeException('M21 accepted table columns differ.'); }
    $rows = [];
    foreach ($wrapper->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr) {
        $cells = [];
        foreach ($tr->childNodes as $cell) { if ($cell instanceof DOMElement && $cell->tagName === 'td') { $cells[] = m37Inner($cell); } }
        if (count($cells) !== $columns) { throw new RuntimeException('M21 accepted table row differs.'); }
        $rows[] = ['cells' => $cells];
    }
    $table = $wrapper->getElementsByTagName('table')->item(0);
    if (!preg_match('/min-width:(\\d+)px/', $table->getAttribute('style'), $width)) { throw new RuntimeException('M21 accepted table width missing.'); }
    $minimums = [];
    foreach ($table->getElementsByTagName('th') as $header) {
        $minimums[] = preg_match('/min-width:(\\d+)px/', $header->getAttribute('style'), $column) ? (int) $column[1] : null;
    }
    return ['headers' => $headers, 'rows' => $rows, 'table_min_width' => (int) $width[1], 'column_min_widths' => $minimums];
}
function m37AuthorChecks(DOMElement $source): array
{
    $xp = new DOMXPath($source->ownerDocument);
    $prompts = $xp->query('.//ol[@data-self-checks]/li', $source);
    $keys = $xp->query('.//ol[@data-self-check-answers]/li', $source);
    if ($prompts->length !== 6 || $keys->length !== 6) { throw new RuntimeException('M21 exact six author cases/keys required.'); }
    return ['section_title' => trim($source->getElementsByTagName('h4')->item(0)->textContent),
        'intro' => $source->getElementsByTagName('p')->length > 0 ? m37Inner($source->getElementsByTagName('p')->item(0)) : '',
        'prompts' => array_map('m37Inner', iterator_to_array($prompts)),
        'title' => trim($source->getElementsByTagName('summary')->item(0)->textContent),
        'answers' => array_map('m37Inner', iterator_to_array($keys))];
}
// Mechanical finite token groups; they only rearrange words of the accepted answer.
function m37TokenInput(string $answer, int $case, array $accepted = []): array
{
    $chunks = array_map(fn ($words) => implode(' ', $words), array_chunk(preg_split('/\s+/u', $answer), 3));
    return ['before' => implode(' / ', array_reverse($chunks)), 'm37_token_groups' => array_reverse($chunks), 'answer' => $answer,
        'accepted' => $accepted !== [] ? $accepted : [$answer], 'source_case' => $case, 'punctuation_sensitive' => true];
}
require __DIR__.'/m37-practice-projection.php';

// Explicit semantic decisions. Length is diagnostic, never a runtime threshold.
$reasons = [
    ['Керування, часовий контекст і рішення без доведеного виконання — базове розмежування.',
        'Два різновиди to та різні функції -ing — центральна основа вибору моделі.',
        'Спогад, нагадування й виконане доручення треба бачити разом із точними прикладами.',
        'Повна таблиця stop/try та межі результату потрібні до кліку.',
        'Аналіз безпосередньо пояснює репліки; невідомий результат не є optional depth.',
        'Чинний п’яти-пунктний author checklist і виправлення завершують visible basic.'],
    ['Ролі whose, належність і оренда проти власності — основний зміст прикладів.',
        'Керування й граматична межа після винесеного прийменника — основа моделі.',
        'Повна таблиця опущення, дублювання ролей і вкладений who — центральні правила.',
        'Коми визначають групу; контексти, кількість і межі висновку мають бути видимі.',
        'Референт which та ясна редакція — основне пояснення, не додатковий клік.',
        'Повний абзац і його рольовий аналіз потрібні разом; короткий checklist видимий.'],
    ['Перший auxiliary, do-support та незмінність присудка — коротка базова опора C1.',
        'Таблиця часових пар, be-випадок і близькість без причинності — центральний зміст.',
        'Підрядна проти головної, пасив і antecedent then — основна межа інверсії.',
        'Факт, заборона й частотність не можна ховати за спільною формою.',
        'Сфера only/not only та учасники — центральне розмежування, не optional depth.',
        'Обидві редакції й незмінність фактів утворюють один повний навчальний пункт.'],
];
$retained = []; // All accepted material is core, continuation or a short caveat; no invented depth.
$package = ['version' => 1, 'targets' => []];
foreach ($manifest['targets'] as $i => $target) {
    if ($target['path'] !== $paths[$i] || $target['source_git_blob'] !== $blobs[$i]) { throw new RuntimeException('M21 source allowlist differs.'); }
    $before = $target['before']; $identity = $before['seeder']['class'];
    if ($before['slug'] !== $slugs[$i] || !isset($banks[$identity]) || $banks[$identity]['level'] !== $levels[$i]
        || $before['page']['locale'] !== 'uk' || $before['type'] !== 'theory' || $before['page']['category']['slug'] !== $ancestries[$i][count($ancestries[$i]) - 1]) {
        throw new RuntimeException('M21 page/bank identity/level/category differs.');
    }
    $groups = m37Sections($before['page']['blocks'][1]['body']);
    $blocks = [$before['page']['blocks'][0]]; $plans = []; $audit = [];
    foreach ($groups as $s => $group) {
        $key = 'm37-'.['gerund-infinitive', 'relative-clauses', 'negative-inversion'][$i].'-section-'.($s + 1);
        $nodes = $group['nodes']; $points = []; $extra = []; $type = 'usage-panels';
        if (isset($group['practice'])) {
            $type = 'practice-set'; $data = m37Practice($i, m37AuthorChecks($group['source_node']), $banks[$identity]);
            $extra['legacy_practice_id'] = $group['practice'];
        } elseif ($s === 7) {
            $type = 'summary-list'; $data = ['title' => $group['title'], 'items' => array_map('m37Inner', $nodes)];
        } else {
            $tableIndex = null;
            foreach ($nodes as $n => $node) { if ($node instanceof DOMElement && $node->getElementsByTagName('table')->length > 0) { $tableIndex = $n; } }
            $retain = in_array([$i, $s], $retained, true);
            if ($tableIndex !== null) {
                $type = 'comparison-table'; $data = ['title' => $group['title']] + m37Table($nodes[$tableIndex], 4);
                $data['intro'] = m37Prose(array_slice($nodes, 0, $tableIndex));
                $data['outro'] = m37Prose(array_slice($nodes, $tableIndex + 1));
            } else {
                $points = m37Points($nodes);
                $data = ['title' => $group['title'], 'sections' => array_map(fn ($p) =>
                    ['description' => $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')], $points)];
            }
            $paragraphs = array_values(array_filter($nodes, fn ($n) => $n->nodeName === 'p'));
            $candidateNode = $paragraphs !== [] ? $paragraphs[count($paragraphs) - 1] : $nodes[count($nodes) - 1];
            $candidate = m37Inner($candidateNode);
            $visible = m37Prose($nodes);
            $point = $candidateNode->nodeName === 'p' ? 'source-final-paragraph' : 'source-final-list';
            $audit[] = ['source_section' => $s + 1, 'point' => $point,
                'basic_word_count' => m37Words($visible) - m37Words($candidate), 'detail_word_count' => m37Words($candidate),
                'detail_sentence_count' => m37Sentences($candidate), 'candidate_html' => $candidate,
                'decision' => $retain ? 'meaningful_detail' : 'visible_basic', 'reason' => $reasons[$i][$s]];
        }
        $data['m37_v1'] = ['key' => $key, 'legacy_section' => $s + 1] + $extra;
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
        throw new RuntimeException('M37 refuses to overwrite a manually changed definition.');
    }
    $package['targets'][] = ['path' => $target['path'], 'identity' => $identity, 'slug' => $before['slug'],
        'ancestry' => $ancestries[$i], 'after' => $after, 'plans' => $plans, 'detail_quality_audit' => $audit];
}
foreach ($package['targets'] as $target) {
    if (file_put_contents($root.'/'.$target['path'], m37Json($target['after'])) === false) { throw new RuntimeException('M37 definition projection failed.'); }
}
if (file_put_contents($root.'/database/content-patches/m37-m21-grammar-structures.v1.json', m37Json($package)) === false) {
    throw new RuntimeException('M37 package projection failed.');
}
echo "Projected three M21 sources into eight native blocks/page; meaningful details 0/0/0, exact six cases each.\n";
