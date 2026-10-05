<?php

// Finite technical projection of accepted M22 author content; no Laravel/DB boot.
// Capture is exclusive. Projection requires actual read-only linked-bank evidence.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$baseSha = '8607e2394d347a16c0465a7fb331b467399dab74';
$names = ['AdvancedArticleAndQuantifierNuance', 'PrecisionWithArticlesAndDeterminers', 'AdvancedCollocationAndLexicalChoice'];
$categories = ['ArticlesAndQuantifiers', 'ArticlesAndQuantifiers', 'VocabularyAndCollocations'];
$ancestries = [['articles-and-quantifiers'], ['articles-and-quantifiers'], ['vocabulary-and-collocations']];
$slugs = ['advanced-article-and-quantifier-nuance', 'precision-with-articles-and-determiners', 'advanced-collocation-and-lexical-choice'];
$levels = ['C1', 'C2', 'C2'];
$blobs = ['7c11647100e2c47d7c00c6e8be053822373f573b', 'ab364f6f74540a473d4856376830aaa5fda2ecf5', '0f978eedae3fc7bee2f8f0d449fb3aeb7e131937'];
$paths = array_map(fn ($n, $category) => 'database/seeders/Page_V3/'.$category.'/'.$n.'TheorySeeder/definition.json', $names, $categories);
$manifestPath = $root.'/database/content-patches/m38-m22-articles-collocations-before.json';

function m38Json(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}

if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('M38 before manifest already exists.'); }
    $manifest = ['base_sha' => $baseSha, 'targets' => []];
    foreach ($paths as $i => $path) {
        $bytes = file_get_contents($root.'/'.$path);
        $repositoryBytes = str_replace("\r\n", "\n", $bytes);
        $gitBlob = hash('sha1', 'blob '.strlen($repositoryBytes)."\0".$repositoryBytes);
        if ($gitBlob !== $blobs[$i]) { throw new RuntimeException('M22 accepted blob differs: '.$path); }
        $manifest['targets'][] = ['path' => $path, 'source_sha256' => hash('sha256', $bytes), 'source_git_blob' => $gitBlob,
            'before' => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)];
    }
    $file = fopen($manifestPath, 'xb');
    if ($file === false) { throw new RuntimeException('M38 exclusive capture failed.'); }
    $bytes = m38Json($manifest);
    if (fwrite($file, $bytes) !== strlen($bytes)) { fclose($file); throw new RuntimeException('M38 capture incomplete.'); }
    fclose($file);
    echo "Captured three accepted M22 sources, not DB rows.\n";
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
if ($manifest['base_sha'] !== $baseSha || count($manifest['targets']) !== 3) { throw new RuntimeException('M38 finite manifest differs.'); }
$previousTargets = [];
$replaceFlag = array_search('--replace-projection-sha', $argv, true);
if ($replaceFlag !== false) {
    $previousBytes = file_get_contents($root.'/database/content-patches/m38-m22-articles-collocations.v1.json');
    if (!isset($argv[$replaceFlag + 1]) || !hash_equals($argv[$replaceFlag + 1], hash('sha256', $previousBytes))) {
        throw new RuntimeException('M38 prior generated projection hash differs.');
    }
    foreach (json_decode($previousBytes, true, flags: JSON_THROW_ON_ERROR)['targets'] as $previous) {
        $previousTargets[$previous['identity']] = $previous['after'];
    }
}

function m38Clean(string $html): string { return preg_replace('/\sstyle="[^"]*"/', '', $html); }
function m38Inner(DOMNode $node): string
{
    $out = '';
    foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); }
    return m38Clean(trim($out));
}
function m38Plain(string $html): string
{
    // Preserve word boundaries between author paragraphs in derived UI labels.
    $html = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
    return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}
// Diagnostic whitespace tokens: block/line breaks separate words; inline punctuation stays attached.
// This does not alter answer normalization, author HTML or semantic disclosure decisions.
function m38DiagnosticPlain(string $html): string
{
    $separated = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
    return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($separated), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}
function m38Words(string $html): int { return preg_match_all('/\S+/u', m38DiagnosticPlain($html)); }
function m38Sentences(string $html): int { return preg_match_all('/[.!?](?:\s|$)/u', m38DiagnosticPlain($html)); }
function m38Prose(array $nodes): string
{
    return implode('<br><br>', array_map(fn ($n) => in_array($n->nodeName, ['p', 'li'], true)
        ? m38Inner($n) : m38Clean($n->ownerDocument->saveHTML($n)), $nodes));
}
function m38Sections(string $html): array
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
    if (count($groups) !== 8) { throw new RuntimeException('M22 must have six teaching sections, self-check and continuation.'); }
    return $groups;
}
function m38Points(array $nodes): array
{
    $points = [];
    foreach ($nodes as $node) {
        if (in_array($node->nodeName, ['ul', 'ol'], true)) {
            foreach ($node->childNodes as $li) {
                if ($li instanceof DOMElement && $li->tagName === 'li') { $points[] = ['basic' => m38Inner($li), 'detail' => '']; }
            }
        } else {
            $points[] = ['basic' => $node->nodeName === 'p' ? m38Inner($node) : m38Clean($node->ownerDocument->saveHTML($node)), 'detail' => ''];
        }
    }
    return $points;
}
function m38Table(DOMElement $wrapper, int $columns): array
{
    $headers = array_map(fn ($n) => trim($n->textContent), iterator_to_array($wrapper->getElementsByTagName('th')));
    if (count($headers) !== $columns) { throw new RuntimeException('M22 accepted table columns differ.'); }
    $rows = [];
    foreach ($wrapper->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr) {
        $cells = [];
        foreach ($tr->childNodes as $cell) { if ($cell instanceof DOMElement && $cell->tagName === 'td') { $cells[] = m38Inner($cell); } }
        if (count($cells) !== $columns) { throw new RuntimeException('M22 accepted table row differs.'); }
        $rows[] = ['cells' => $cells];
    }
    $table = $wrapper->getElementsByTagName('table')->item(0);
    if (!preg_match('/min-width:(\\d+)px/', $table->getAttribute('style'), $width)) { throw new RuntimeException('M22 accepted table width missing.'); }
    $minimums = [];
    foreach ($table->getElementsByTagName('th') as $header) {
        $minimums[] = preg_match('/min-width:(\\d+)px/', $header->getAttribute('style'), $column) ? (int) $column[1] : null;
    }
    return ['headers' => $headers, 'rows' => $rows, 'table_min_width' => (int) $width[1], 'column_min_widths' => $minimums];
}
function m38AuthorChecks(DOMElement $source): array
{
    $xp = new DOMXPath($source->ownerDocument);
    $prompts = $xp->query('.//ol[@data-self-checks]/li', $source);
    $keys = $xp->query('.//ol[@data-self-check-answers]/li', $source);
    if ($prompts->length !== 6 || $keys->length !== 6) { throw new RuntimeException('M22 exact six author cases/keys required.'); }
    return ['section_title' => trim($source->getElementsByTagName('h4')->item(0)->textContent),
        'intro' => $source->getElementsByTagName('p')->length > 0 ? m38Inner($source->getElementsByTagName('p')->item(0)) : '',
        'prompts' => array_map('m38Inner', iterator_to_array($prompts)),
        'title' => trim($source->getElementsByTagName('summary')->item(0)->textContent),
        'answers' => array_map('m38Inner', iterator_to_array($keys))];
}
// Mechanical finite token groups; they only rearrange words of the accepted answer.
function m38TokenInput(string $answer, int $case, array $accepted = []): array
{
    $words = preg_split('/\s+/u', $answer);
    $chunks = array_map(fn ($group) => implode(' ', $group), array_chunk($words, 3));
    // A short phrase still needs two reorderable groups, not one indivisible answer.
    if (count($chunks) === 1 && count($words) > 1) {
        $chunks = [$words[0], implode(' ', array_slice($words, 1))];
    }
    return ['before' => implode(' / ', array_reverse($chunks)), 'm38_token_groups' => array_reverse($chunks), 'answer' => $answer,
        'accepted' => $accepted !== [] ? $accepted : [$answer], 'source_case' => $case, 'punctuation_sensitive' => true];
}
require __DIR__.'/m38-practice-projection.php';

// Explicit semantic decisions. Length is diagnostic, never a runtime threshold.
$reasons = [
    ['Значення іменника й одиниці підрахунку та точні приклади — core countability, не окреме поглиблення.',
        'Таблиця few/little і відмінність наявності від достатності потрібні до будь-якого кліку.',
        'Much/many/several та a number of/the number of з узгодженням — основні видимі моделі.',
        'Ситуаційна ідентифікація й загальне проти визначеного research — центральне відсилання.',
        'BrE/AmE та ролі пацієнтки й відвідувача обмежують саме цей базовий приклад.',
        'Редакція й контроль значення, числа, ідентифікації та достатності завершують один видимий пункт.'],
    ['Generic/specific і a label як узагальнення — центральна межа article/reference, не optional depth.',
        'Повна таблиця чотирьох папок і двох підгруп потрібна для точного відсилання.',
        'Some/any, прагматичне очікування та невідома частка — core scope, а не додатковий клік.',
        'Each/every/of та формальна однина з розмовною ремаркою потрібні поруч із моделями.',
        'Not all/none та not both/neither і невідомий стан другого об’єкта — головне розмежування.',
        'Точна редакція, дві з п’яти карток і межа повноти запису мають залишатися разом.'],
    ['Словникова перевірка значення, моделі й регістру прямо потрібна до використання колокацій.',
        'Повна role table та природна модель challenge/in/-ing — основа без зміни учасників.',
        'Precedent/could, raise проти answer, число й прийменник — центральні межі моделей.',
        'Підстави substantial/documentary/significant та challenge проти impossibility — core strength.',
        'Checklist і two/only the first без посилення відповіді — аналіз конкретного прикладу.',
        'Чернетка, точна редакція й вилучення непідтвердженого результату утворюють єдиний basic.'],
];
$retained = []; // All accepted material is core, continuation or a short caveat; no invented depth.
$package = ['version' => 1, 'targets' => []];
foreach ($manifest['targets'] as $i => $target) {
    if ($target['path'] !== $paths[$i] || $target['source_git_blob'] !== $blobs[$i]) { throw new RuntimeException('M22 source allowlist differs.'); }
    $before = $target['before']; $identity = $before['seeder']['class'];
    if ($before['slug'] !== $slugs[$i] || !isset($banks[$identity]) || $banks[$identity]['level'] !== $levels[$i]
        || $before['page']['locale'] !== 'uk' || $before['type'] !== 'theory' || $before['page']['category']['slug'] !== $ancestries[$i][count($ancestries[$i]) - 1]) {
        throw new RuntimeException('M22 page/bank identity/level/category differs.');
    }
    $groups = m38Sections($before['page']['blocks'][1]['body']);
    $blocks = [$before['page']['blocks'][0]]; $plans = []; $audit = [];
    foreach ($groups as $s => $group) {
        $key = 'm38-'.['articles-c1', 'determiners-c2', 'collocations-c2'][$i].'-section-'.($s + 1);
        $nodes = $group['nodes']; $points = []; $extra = []; $type = 'usage-panels';
        if (isset($group['practice'])) {
            $type = 'practice-set'; $data = m38Practice($i, m38AuthorChecks($group['source_node']), $banks[$identity]);
            $extra['legacy_practice_id'] = $group['practice'];
        } elseif ($s === 7) {
            $type = 'summary-list'; $data = ['title' => $group['title'], 'items' => array_map('m38Inner', $nodes)];
        } else {
            $tableIndex = null;
            foreach ($nodes as $n => $node) { if ($node instanceof DOMElement && $node->getElementsByTagName('table')->length > 0) { $tableIndex = $n; } }
            $retain = in_array([$i, $s], $retained, true);
            if ($tableIndex !== null) {
                $type = 'comparison-table'; $data = ['title' => $group['title']] + m38Table($nodes[$tableIndex], 4);
                $data['intro'] = m38Prose(array_slice($nodes, 0, $tableIndex));
                $data['outro'] = m38Prose(array_slice($nodes, $tableIndex + 1));
            } else {
                $points = m38Points($nodes);
                $data = ['title' => $group['title'], 'sections' => array_map(fn ($p) =>
                    ['description' => $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')], $points)];
            }
            $paragraphs = array_values(array_filter($nodes, fn ($n) => $n->nodeName === 'p'));
            $candidateNode = $paragraphs !== [] ? $paragraphs[count($paragraphs) - 1] : $nodes[count($nodes) - 1];
            $candidate = m38Inner($candidateNode);
            $visible = m38Prose($nodes);
            $point = $candidateNode->nodeName === 'p' ? 'source-final-paragraph' : 'source-final-list';
            $audit[] = ['source_section' => $s + 1, 'point' => $point,
                'basic_word_count' => m38Words($visible) - m38Words($candidate), 'detail_word_count' => m38Words($candidate),
                'detail_sentence_count' => m38Sentences($candidate), 'candidate_html' => $candidate,
                'decision' => $retain ? 'meaningful_detail' : 'visible_basic', 'reason' => $reasons[$i][$s]];
        }
        $data['m38_v1'] = ['key' => $key, 'legacy_section' => $s + 1] + $extra;
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
        throw new RuntimeException('M38 refuses to overwrite a manually changed definition.');
    }
    $package['targets'][] = ['path' => $target['path'], 'identity' => $identity, 'slug' => $before['slug'],
        'ancestry' => $ancestries[$i], 'after' => $after, 'plans' => $plans, 'detail_quality_audit' => $audit];
}
foreach ($package['targets'] as $target) {
    if (file_put_contents($root.'/'.$target['path'], m38Json($target['after'])) === false) { throw new RuntimeException('M38 definition projection failed.'); }
}
if (file_put_contents($root.'/database/content-patches/m38-m22-articles-collocations.v1.json', m38Json($package)) === false) {
    throw new RuntimeException('M38 package projection failed.');
}
echo "Projected three M22 sources into eight native blocks/page; meaningful details 0/0/0, exact six cases each.\n";
