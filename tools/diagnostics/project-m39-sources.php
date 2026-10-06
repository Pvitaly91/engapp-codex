<?php

// Finite technical projection of accepted M23 author content; no Laravel/DB boot.
// Capture is exclusive. Projection requires actual read-only linked-bank evidence.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$baseSha = 'a525a5e03b59904b9fe5987abacf5c038fe5293f';
$names = ['NominalStyleAndInformationDensity', 'C1MixedRevision', 'C2MixedRevision'];
$categories = ['FormalEnglish', 'BasicGrammar', 'BasicGrammar'];
$ancestries = [['formal-english'], ['mixed-revision'], ['mixed-revision']];
$slugs = ['nominal-style-and-information-density', 'c1-mixed-revision', 'c2-mixed-revision'];
$levels = ['C2', 'C1', 'C2'];
$blobs = ['cef77f1e1ab2493515c3db39cb51b39b06d81330', 'c6c1c404e3a61763a8f97bb7edf0278779bee85d', '9702e82b33b3aab38e5a7653d412e006622ebb64'];
$paths = array_map(fn ($n, $category) => 'database/seeders/Page_V3/'.$category.'/'.$n.'TheorySeeder/definition.json', $names, $categories);
$manifestPath = $root.'/database/content-patches/m39-m23-authored-revision-before.json';
// Immutable source identity uses Git LF bytes; the working CRLF file is never rewritten.
$masterPath = 'docs/content/m23-authored-content.v1.json';
$notesPath = 'docs/content/m23-author-sources.md';
$masterBytes = file_get_contents($root.'/'.$masterPath);
$masterLf = str_replace("\r\n", "\n", $masterBytes);
$masterSha = 'eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6';
$masterBlob = '34a03a7146141fbff50c66ec8e41f3fc2a59b787';
if (hash('sha256', $masterLf) !== $masterSha
    || hash('sha1', 'blob '.strlen($masterLf)."\0".$masterLf) !== $masterBlob) {
    throw new RuntimeException('M39 immutable author master differs; stop without any source writes.');
}
$author = json_decode($masterBytes, true, flags: JSON_THROW_ON_ERROR);
if (count($author['lessons']) !== 3) { throw new RuntimeException('M39 master scope differs.'); }
foreach (['rewrite', 'summarise', 'translate_again', 'add_examples', 'add_exercises', 'production_write', 'mixed_question_bank_write'] as $policy) {
    if ($author['content_policy'][$policy] !== false) { throw new RuntimeException('M39 frozen author policy differs.'); }
}
$notesBytes = file_get_contents($root.'/'.$notesPath);
$notesLf = str_replace("\r\n", "\n", $notesBytes);
$notesBlob = hash('sha1', 'blob '.strlen($notesLf)."\0".$notesLf);
if ($notesBlob !== '859c4263cb00c0ff318bf2b41f3e450ca65efc0d') { throw new RuntimeException('M39 frozen author-source notes differ.'); }

function m39Json(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}

if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('M39 before manifest already exists.'); }
    $manifest = ['base_sha' => $baseSha,
        'author_master_source' => ['path' => $masterPath, 'git_blob' => $masterBlob, 'git_lf_sha256' => $masterSha,
            'working_raw_sha256' => hash('sha256', $masterBytes), 'git_lf_bytes' => $masterLf],
        'author_notes_source' => ['path' => $notesPath, 'git_blob' => $notesBlob, 'working_raw_sha256' => hash('sha256', $notesBytes)],
        'targets' => []];
    foreach ($paths as $i => $path) {
        $bytes = file_get_contents($root.'/'.$path);
        $repositoryBytes = str_replace("\r\n", "\n", $bytes);
        $gitBlob = hash('sha1', 'blob '.strlen($repositoryBytes)."\0".$repositoryBytes);
        if ($gitBlob !== $blobs[$i]) { throw new RuntimeException('M23 accepted blob differs: '.$path); }
        $manifest['targets'][] = ['path' => $path, 'source_sha256' => hash('sha256', $bytes), 'source_git_blob' => $gitBlob,
            'before' => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)];
        $definition = $manifest['targets'][$i]['before']; $lesson = $author['lessons'][$i];
        if ($definition['page']['blocks'][1]['body'] !== $lesson['body_html']
            || $definition['page']['subtitle_html'] !== $lesson['subtitle_html']
            || $definition['page']['subtitle_text'] !== $lesson['subtitle_text']
            || json_decode($definition['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR) !== $lesson['hero']
            || $definition['page']['title'] !== $lesson['preserve_page_title']
            || $definition['page']['blocks'][1]['heading'] !== $lesson['box_heading']) {
            throw new RuntimeException('M39 author-master/definition drift; no capture.');
        }
    }
    $file = fopen($manifestPath, 'xb');
    if ($file === false) { throw new RuntimeException('M39 exclusive capture failed.'); }
    $bytes = m39Json($manifest);
    if (fwrite($file, $bytes) !== strlen($bytes)) { fclose($file); throw new RuntimeException('M39 capture incomplete.'); }
    fclose($file);
    echo "Captured three accepted M23 sources, not DB rows.\n";
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
if ($manifest['base_sha'] !== $baseSha || count($manifest['targets']) !== 3) { throw new RuntimeException('M39 finite manifest differs.'); }
$previousTargets = [];
$replaceFlag = array_search('--replace-projection-sha', $argv, true);
if ($replaceFlag !== false) {
    $previousBytes = file_get_contents($root.'/database/content-patches/m39-m23-authored-revision.v1.json');
    if (!isset($argv[$replaceFlag + 1]) || !hash_equals($argv[$replaceFlag + 1], hash('sha256', $previousBytes))) {
        throw new RuntimeException('M39 prior generated projection hash differs.');
    }
    foreach (json_decode($previousBytes, true, flags: JSON_THROW_ON_ERROR)['targets'] as $previous) {
        $previousTargets[$previous['identity']] = $previous['after'];
    }
}

function m39Clean(string $html): string { return preg_replace('/\sstyle="[^"]*"/', '', $html); }
function m39Inner(DOMNode $node): string
{
    $out = '';
    foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); }
    return m39Clean(trim($out));
}
function m39Plain(string $html): string
{
    // Preserve word boundaries between author paragraphs in derived UI labels.
    $html = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
    return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}
// Diagnostic whitespace tokens: block/line breaks separate words; inline punctuation stays attached.
// This does not alter answer normalization, author HTML or semantic disclosure decisions.
function m39DiagnosticPlain(string $html): string
{
    $separated = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
    return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($separated), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}
function m39Words(string $html): int { return preg_match_all('/\S+/u', m39DiagnosticPlain($html)); }
function m39Sentences(string $html): int { return preg_match_all('/[.!?](?:\s|$)/u', m39DiagnosticPlain($html)); }
function m39Prose(array $nodes): string
{
    return implode('<br><br>', array_map(fn ($n) => in_array($n->nodeName, ['p', 'li'], true)
        ? m39Inner($n) : m39Clean($n->ownerDocument->saveHTML($n)), $nodes));
}
function m39Sections(string $html): array
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
    if (count($groups) !== 8) { throw new RuntimeException('M23 must have six teaching sections, self-check and continuation.'); }
    return $groups;
}
function m39Points(array $nodes): array
{
    $points = [];
    foreach ($nodes as $node) {
        if (in_array($node->nodeName, ['ul', 'ol'], true)) {
            foreach ($node->childNodes as $li) {
                if ($li instanceof DOMElement && $li->tagName === 'li') { $points[] = ['basic' => m39Inner($li), 'detail' => '']; }
            }
        } else {
            $points[] = ['basic' => $node->nodeName === 'p' ? m39Inner($node) : m39Clean($node->ownerDocument->saveHTML($node)), 'detail' => ''];
        }
    }
    return $points;
}
function m39Table(DOMElement $wrapper, int $columns): array
{
    $headers = array_map(fn ($n) => trim($n->textContent), iterator_to_array($wrapper->getElementsByTagName('th')));
    if (count($headers) !== $columns) { throw new RuntimeException('M23 accepted table columns differ.'); }
    $rows = [];
    foreach ($wrapper->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr) {
        $cells = [];
        foreach ($tr->childNodes as $cell) { if ($cell instanceof DOMElement && $cell->tagName === 'td') { $cells[] = m39Inner($cell); } }
        if (count($cells) !== $columns) { throw new RuntimeException('M23 accepted table row differs.'); }
        $rows[] = ['cells' => $cells];
    }
    $table = $wrapper->getElementsByTagName('table')->item(0);
    if (!preg_match('/min-width:(\\d+)px/', $table->getAttribute('style'), $width)) { throw new RuntimeException('M23 accepted table width missing.'); }
    $minimums = [];
    foreach ($table->getElementsByTagName('th') as $header) {
        $minimums[] = preg_match('/min-width:(\\d+)px/', $header->getAttribute('style'), $column) ? (int) $column[1] : null;
    }
    return ['headers' => $headers, 'rows' => $rows, 'table_min_width' => (int) $width[1], 'column_min_widths' => $minimums];
}
function m39AuthorChecks(DOMElement $source): array
{
    $xp = new DOMXPath($source->ownerDocument);
    $prompts = $xp->query('./ol/li', $source);
    $keys = $xp->query('./details/ol/li', $source);
    if ($prompts->length !== 6 || $keys->length !== 6) { throw new RuntimeException('M23 exact six author cases/keys required.'); }
    return ['section_title' => trim($source->getElementsByTagName('h4')->item(0)->textContent),
        'intro' => $source->getElementsByTagName('p')->length > 0 ? m39Inner($source->getElementsByTagName('p')->item(0)) : '',
        'prompts' => array_map('m39Inner', iterator_to_array($prompts)),
        'title' => trim($source->getElementsByTagName('summary')->item(0)->textContent),
        'answers' => array_map('m39Inner', iterator_to_array($keys))];
}
// Mechanical finite token groups; they only rearrange words of the accepted answer.
function m39TokenInput(string $answer, int $case, array $accepted = []): array
{
    $words = preg_split('/\s+/u', $answer);
    $chunks = array_map(fn ($group) => implode(' ', $group), array_chunk($words, 3));
    // A short phrase still needs two reorderable groups, not one indivisible answer.
    if (count($chunks) === 1 && count($words) > 1) {
        $chunks = [$words[0], implode(' ', array_slice($words, 1))];
    }
    return ['before' => implode(' / ', array_reverse($chunks)), 'm39_token_groups' => array_reverse($chunks), 'answer' => $answer,
        'accepted' => $accepted !== [] ? $accepted : [$answer], 'source_case' => $case, 'punctuation_sensitive' => true];
}
require __DIR__.'/m39-practice-projection.php';

// Explicit semantic decisions. Length is diagnostic, never a runtime threshold.
$reasons = [
    ['Фокус і початок проти завершення та коротке продовження теми належать до видимого core.',
        'Виконавець, об’єкт, may та конкретні of/by-моделі пояснюють саме базове перетворення.',
        'Повна таблиця плану, перебігу й результату та заборона додавати успіх потрібні до кліку.',
        'Головне слово, узгодження й читабельність групи — центральна основа, не optional depth.',
        'Два різні зв’язки іменникового ланцюга та межа наслідку extension мають лишатися видимі.',
        'Аналіз редакції з трьома невиконаними статусами потрібний разом із повним абзацом.'],
    ['Час, учасники й статус у повній таблиці — core meaning-first, не механічні заміни.',
        'Два моменти часу та точна would/could/might-модальність — основа цієї моделі.',
        'Only після підмета проти only-after головної частини — центральна область інверсії.',
        'Reporting, пасив і defining-підгрупа без дублювання who — основне розмежування.',
        'May have проти факту та could як прохання зі строком — core functions.',
        'Контроль абзацу й коротка therefore-ремарка завершують видимий basic.'],
    ['Невипробуваний зразок проти невдалого випробування — центральний смисловий контраст.',
        'Had/not/might та коротка scope-note про інші моделі потрібні до кліку.',
        'Reporting-частина й місце not визначають твердження; це не додатковий клік.',
        'Need/виконання та not all/none — основні межі, жодної вигаданої кількості.',
        'Конкретні три записи й singular assessment пояснюють саме приклади.',
        'Відомі межі indoor-тесту та коротка ремарка про інші формулювання й атрибуцію — basic.'],
];
$retained = []; // All accepted material is core, continuation or a short caveat; no invented depth.
$package = ['version' => 1, 'targets' => []];
foreach ($manifest['targets'] as $i => $target) {
    if ($target['path'] !== $paths[$i] || $target['source_git_blob'] !== $blobs[$i]) { throw new RuntimeException('M23 source allowlist differs.'); }
    $before = $target['before']; $identity = $before['seeder']['class'];
    if ($before['slug'] !== $slugs[$i] || !isset($banks[$identity]) || $banks[$identity]['level'] !== $levels[$i]
        || $before['page']['locale'] !== 'uk' || $before['type'] !== 'theory' || $before['page']['category']['slug'] !== $ancestries[$i][count($ancestries[$i]) - 1]) {
        throw new RuntimeException('M23 page/bank identity/level/category differs.');
    }
    $lesson = $author['lessons'][$i];
    if ($lesson['seeder'] !== $identity || $before['page']['blocks'][1]['body'] !== $lesson['body_html']
        || hash('sha256', $lesson['body_html']) !== $lesson['body_sha256']) {
        throw new RuntimeException('M39 authored body/identity drift; no projection writes.');
    }
    $groups = m39Sections($lesson['body_html']);
    $blocks = [$before['page']['blocks'][0]]; $plans = []; $audit = [];
    foreach ($groups as $s => $group) {
        $key = 'm39-'.['nominal-style', 'c1-review', 'c2-review'][$i].'-section-'.($s + 1);
        $nodes = $group['nodes']; $points = []; $extra = []; $type = 'usage-panels';
        if (isset($group['practice'])) {
            $type = 'practice-set'; $data = m39Practice($i, m39AuthorChecks($group['source_node']), $banks[$identity]);
            $data['title'] = $group['title']; // Preserve the immutable author's section heading.
            $extra['legacy_practice_id'] = $group['practice'];
        } elseif ($s === 7) {
            $type = 'summary-list'; $data = ['title' => $group['title'], 'items' => array_map(
                fn ($node) => m39Clean($node->ownerDocument->saveHTML($node)), $nodes)];
        } else {
            $tableIndex = null;
            foreach ($nodes as $n => $node) { if ($node instanceof DOMElement && $node->getElementsByTagName('table')->length > 0) { $tableIndex = $n; } }
            $retain = in_array([$i, $s], $retained, true);
            if ($tableIndex !== null) {
                $type = 'comparison-table'; $data = ['title' => $group['title']] + m39Table($nodes[$tableIndex], 3);
                $data['intro'] = m39Prose(array_slice($nodes, 0, $tableIndex));
                $data['outro'] = m39Prose(array_slice($nodes, $tableIndex + 1));
            } else {
                $points = m39Points($nodes);
                $data = ['title' => $group['title'], 'sections' => array_map(fn ($p) =>
                    ['description' => $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')], $points)];
            }
            $paragraphs = array_values(array_filter($nodes, fn ($n) => $n->nodeName === 'p'));
            $candidateNode = $paragraphs !== [] ? $paragraphs[count($paragraphs) - 1] : $nodes[count($nodes) - 1];
            $candidate = m39Inner($candidateNode);
            $visible = m39Prose($nodes);
            $point = $candidateNode->nodeName === 'p' ? 'source-final-paragraph' : 'source-final-list';
            $audit[] = ['source_section' => $s + 1, 'point' => $point,
                'basic_word_count' => m39Words($visible) - m39Words($candidate), 'detail_word_count' => m39Words($candidate),
                'detail_sentence_count' => m39Sentences($candidate), 'candidate_html' => $candidate,
                'decision' => $retain ? 'meaningful_detail' : 'visible_basic', 'reason' => $reasons[$i][$s]];
        }
        $data['m39_v1'] = ['key' => $key, 'legacy_section' => $s + 1] + $extra;
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
        throw new RuntimeException('M39 refuses to overwrite a manually changed definition.');
    }
    $package['targets'][] = ['path' => $target['path'], 'identity' => $identity, 'slug' => $before['slug'],
        'ancestry' => $ancestries[$i], 'after' => $after, 'plans' => $plans, 'detail_quality_audit' => $audit];
}
foreach ($package['targets'] as $target) {
    if (file_put_contents($root.'/'.$target['path'], m39Json($target['after'])) === false) { throw new RuntimeException('M39 definition projection failed.'); }
}
if (file_put_contents($root.'/database/content-patches/m39-m23-authored-revision.v1.json', m39Json($package)) === false) {
    throw new RuntimeException('M39 package projection failed.');
}
echo "Projected three M23 sources into eight native blocks/page; meaningful details 0/0/0, exact six cases each.\n";
