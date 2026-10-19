<?php

// Finite technical projection of accepted M16 author content; no Laravel/DB boot.
// Capture is exclusive. Projection requires actual read-only linked-bank evidence.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$baseSha = '326bf629e84cd9385ba2ac531a00fb30f82aab73';
$names = ['FormalRegisterAndNominalisationBasics', 'NominalisationFormalRegister', 'RegisterToneAndParaphrase'];
$slugs = ['formal-register-and-nominalisation-basics', 'nominalisation-formal-register', 'register-tone-and-paraphrase'];
$levels = ['B2', 'C1', 'C1'];
$blobs = ['2f36b86d9e19af0739387d9117f0074cb2ba92c7', '0679c7b3c84e3317a184280ad4a5ff1054863515', '79966df02d62d62f3481ddd4bb06a16955ad260c'];
$paths = array_map(fn ($n) => 'database/seeders/Page_V3/FormalEnglish/'.$n.'TheorySeeder/definition.json', $names);
$manifestPath = $root.'/database/content-patches/m32-m16-formal-english-before.json';

function m32Json(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}

if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('M32 before manifest already exists.'); }
    $manifest = ['base_sha' => $baseSha, 'targets' => []];
    foreach ($paths as $i => $path) {
        $bytes = file_get_contents($root.'/'.$path);
        $repositoryBytes = str_replace("\r\n", "\n", $bytes);
        $gitBlob = hash('sha1', 'blob '.strlen($repositoryBytes)."\0".$repositoryBytes);
        if ($gitBlob !== $blobs[$i]) { throw new RuntimeException('M16 accepted blob differs: '.$path); }
        $manifest['targets'][] = ['path' => $path, 'source_sha256' => hash('sha256', $bytes), 'source_git_blob' => $gitBlob,
            'before' => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)];
    }
    $file = fopen($manifestPath, 'xb');
    if ($file === false) { throw new RuntimeException('M32 exclusive capture failed.'); }
    $bytes = m32Json($manifest);
    if (fwrite($file, $bytes) !== strlen($bytes)) { fclose($file); throw new RuntimeException('M32 capture incomplete.'); }
    fclose($file);
    echo "Captured three accepted M16 sources, not DB rows.\n";
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
        || empty($record['level']) || isset($banks[$record['identity']]) || count(array_unique($record['question_ids'] ?? [])) !== 48) {
        throw new RuntimeException('Missing/duplicate linked-bank evidence.');
    }
    $banks[$record['identity']] = $record;
}
$manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
if ($manifest['base_sha'] !== $baseSha || count($manifest['targets']) !== 3) { throw new RuntimeException('M32 finite manifest differs.'); }
$previousTargets = [];
$replaceFlag = array_search('--replace-projection-sha', $argv, true);
if ($replaceFlag !== false) {
    $previousBytes = file_get_contents($root.'/database/content-patches/m32-m16-formal-english.v1.json');
    if (!isset($argv[$replaceFlag + 1]) || !hash_equals($argv[$replaceFlag + 1], hash('sha256', $previousBytes))) {
        throw new RuntimeException('M32 prior generated projection hash differs.');
    }
    foreach (json_decode($previousBytes, true, flags: JSON_THROW_ON_ERROR)['targets'] as $previous) {
        $previousTargets[$previous['identity']] = $previous['after'];
    }
}

function m32Clean(string $html): string { return preg_replace('/\sstyle="[^"]*"/', '', $html); }
function m32Inner(DOMNode $node): string
{
    $out = '';
    foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); }
    return m32Clean(trim($out));
}
function m32Plain(string $html): string { return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }
function m32Words(string $html): int { return preg_match_all('/\S+/u', m32Plain($html)); }
function m32Sentences(string $html): int { return preg_match_all('/[.!?](?:\s|$)/u', m32Plain($html)); }
function m32Prose(array $nodes): string
{
    return implode('<br><br>', array_map(fn ($n) => in_array($n->nodeName, ['p', 'li'], true)
        ? m32Inner($n) : m32Clean($n->ownerDocument->saveHTML($n)), $nodes));
}
function m32Sections(string $html): array
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
    if (count($groups) !== 8) { throw new RuntimeException('M16 must have six teaching sections, self-check and continuation.'); }
    return $groups;
}
function m32Points(array $nodes): array
{
    $points = [];
    foreach ($nodes as $node) {
        if (in_array($node->nodeName, ['ul', 'ol'], true)) {
            foreach ($node->childNodes as $li) {
                if ($li instanceof DOMElement && $li->tagName === 'li') { $points[] = ['basic' => m32Inner($li), 'detail' => '']; }
            }
        } else {
            $points[] = ['basic' => $node->nodeName === 'p' ? m32Inner($node) : m32Clean($node->ownerDocument->saveHTML($node)), 'detail' => ''];
        }
    }
    return $points;
}
function m32Table(DOMElement $wrapper, int $columns): array
{
    $headers = array_map(fn ($n) => trim($n->textContent), iterator_to_array($wrapper->getElementsByTagName('th')));
    if (count($headers) !== $columns) { throw new RuntimeException('M16 accepted table columns differ.'); }
    $rows = [];
    foreach ($wrapper->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr) {
        $cells = [];
        foreach ($tr->childNodes as $cell) { if ($cell instanceof DOMElement && $cell->tagName === 'td') { $cells[] = m32Inner($cell); } }
        if (count($cells) !== $columns) { throw new RuntimeException('M16 accepted table row differs.'); }
        $rows[] = ['cells' => $cells];
    }
    return ['headers' => $headers, 'rows' => $rows];
}
function m32AuthorChecks(DOMElement $source): array
{
    $xp = new DOMXPath($source->ownerDocument);
    $prompts = $xp->query('.//ol[@data-self-checks]/li', $source);
    $keys = $xp->query('.//ol[@data-self-check-answers]/li', $source);
    if ($prompts->length !== 6 || $keys->length !== 6) { throw new RuntimeException('M16 exact six author cases/keys required.'); }
    return ['section_title' => trim($source->getElementsByTagName('h4')->item(0)->textContent),
        'intro' => m32Inner($source->getElementsByTagName('p')->item(0)),
        'prompts' => array_map('m32Inner', iterator_to_array($prompts)),
        'title' => trim($source->getElementsByTagName('summary')->item(0)->textContent),
        'answers' => array_map('m32Inner', iterator_to_array($keys))];
}
function m32Practice(int $i, array $author, array $bank): array
{
    $p = ['title' => 'Практика', 'choice_options' => ['a', 'b'], 'author_self_check' => $author,
        'selects' => [], 'choices' => [], 'inputs' => [],
        'linked_practice' => ['source' => 'theory_links', 'question_types' => [$bank['question_type']],
            'seeder_classes' => [$bank['seeder_class']], 'title' => $i === 1 ? 'Вправа 2. Побудуй речення' : 'Вправа 3. Побудуй речення',
            'intro' => 'Склади англійське речення за українським.', 'footer' => 'Завдання з наявного тесту цієї сторінки.']];
    $p['choice_title'] = 'Вправа 1. Обери доречний варіант';
    $p['input_title'] = $i === 1 ? 'Вправа 1. Перебудуй речення' : 'Вправа 2. Перебудуй речення';
    if ($i === 0) {
        $p['choices'] = [
            ['answer' => 'b', 'source_case' => 1, 'feedback' => ['a' => m32Plain($author['answers'][0])]],
            ['prompt' => 'A) the director’s approval of the poster; approve.<br>B) the yellow poster; approve.',
                'answer' => 'a', 'source_case' => 2],
            ['prompt' => 'A) We arrived at the gallery at ten.<br>B) We got to the gallery at ten.<br>C) We obtained to the gallery at ten.',
                'options' => ['a', 'b', 'c'], 'answer' => 'a', 'accepted' => ['a', 'b'], 'source_case' => 4],
        ];
        $p['inputs'] = [
            ['before' => 'two speakers. / on Monday / We / to invite / made a decision',
                'answer' => 'We made a decision on Monday to invite two speakers.', 'source_case' => 3],
            ['before' => 'the name cards. / by noon tomorrow, / I need it / send me / please? / to prepare / Could you / the seating plan',
                'answer' => 'Could you send me the seating plan by noon tomorrow, please? I need it to prepare the name cards.',
                'accepted' => [
                    'Could you send me the seating plan by noon tomorrow, please? I need it to prepare the name cards.',
                    'I would be grateful if you could send me the seating plan by noon tomorrow. I need it to prepare the name cards.',
                ], 'source_case' => 5, 'punctuation_sensitive' => true],
            ['before' => 'today. / the booking / Yesterday / to confirm / we decided',
                'answer' => 'Yesterday we decided to confirm the booking today.', 'source_case' => 6],
        ];
    } elseif ($i === 1) {
        $p['inputs'] = [
            ['before' => 'in progress. / is currently / The conservators’ examination / of the mural',
                'answer' => 'The conservators’ examination of the mural is currently in progress.', 'source_case' => 1],
            ['before' => 'two incorrect dates. / Their review / The editors / revealed / reviewed the captions.',
                'answer' => 'The editors reviewed the captions. Their review revealed two incorrect dates.',
                'accepted' => [
                    'The editors reviewed the captions. Their review revealed two incorrect dates.',
                    'The editors reviewed the captions. As a result of this review, they found two incorrect dates.',
                ], 'source_case' => 2, 'punctuation_sensitive' => true],
            ['before' => 'next week / is possible. / Repair / by the volunteers / of the gate',
                'answer' => 'Repair of the gate by the volunteers next week is possible.',
                'accepted' => [
                    'Repair of the gate by the volunteers next week is possible.',
                    'The repair of the gate by the volunteers next week is possible.',
                ], 'source_case' => 3],
            ['before' => 'booking requests. / conducted an analysis / We / of the',
                'answer' => 'We conducted an analysis of the booking requests.', 'source_case' => 4,
                'feedback' => ['we carried out an analysis of the booking requests.' => m32Plain($author['answers'][3])]],
            ['before' => 'had been cancelled. / that the workshop / yesterday / It was announced',
                'answer' => 'It was announced yesterday that the workshop had been cancelled.', 'source_case' => 5],
            ['before' => 'on Friday. / This evaluation / The committee / a missing label. / plans to add / the two designs / on Tuesday. / revealed / The committee / the label / evaluated',
                'answer' => 'The committee evaluated the two designs on Tuesday. This evaluation revealed a missing label. The committee plans to add the label on Friday.',
                'source_case' => 6, 'punctuation_sensitive' => true],
        ];
    } else {
        $p['choices'] = [
            ['answer' => 'b', 'source_case' => 1, 'feedback' => ['a' => m32Plain($author['answers'][0])]],
            ['prompt' => 'A) Not all → no; some → all; may → will.<br>B) Змінено лише порядок слів.',
                'answer' => 'a', 'source_case' => 3, 'feedback' => ['b' => m32Plain($author['answers'][2])]],
            ['answer' => 'a', 'source_case' => 5],
        ];
        $p['inputs'] = [
            ['before' => 'on Wednesday. / by our designer / to you / The revised map / by 4 p.m. / will be sent',
                'answer' => 'The revised map will be sent to you by our designer by 4 p.m. on Wednesday.', 'source_case' => 2],
            ['before' => 'please? / the invoice / Could you / by noon tomorrow, / send me',
                'answer' => 'Could you send me the invoice by noon tomorrow, please?',
                'accepted' => [
                    'Could you send me the invoice by noon tomorrow, please?',
                    'I would be grateful if you could send me the invoice by noon tomorrow.',
                ], 'source_case' => 4],
            ['before' => 'in person. / only / If the hall / the Saturday workshop / Friday’s session / may move / is unavailable, / take place / the organiser / will still / online.',
                'answer' => 'If the hall is unavailable, the organiser may move only the Saturday workshop online. Friday’s session will still take place in person.',
                'accepted' => [
                    'If the hall is unavailable, the organiser may move only the Saturday workshop online. Friday’s session will still take place in person.',
                    'The organiser may move only the Saturday workshop online if the hall is unavailable. Friday’s session will remain in person.',
                ], 'source_case' => 6, 'punctuation_sensitive' => true],
        ];
    }
    foreach (['selects', 'choices', 'inputs'] as $kind) {
        foreach ($p[$kind] as &$item) {
            $case = $item['source_case']; unset($item['source_case']);
            $item['source_index'] = $case; $item['context'] = $author['prompts'][$case - 1];
            $item['author_explanation'] = $author['answers'][$case - 1];
            if ($kind !== 'inputs') { $item['label'] = ''; }
        }
        unset($item);
    }
    return $p;
}

// Explicit semantic decisions. Length is diagnostic, never a runtime threshold.
$reasons = [
    ['Регістр, сила звернення, I/we, скорочення й фразові дієслова є центральними межами правила; усе видно поруч із таблицею.',
        'Основне розмежування іменникової групи й номіналізації, не optional addendum.',
        'Керування approval/of є основною граматикою перетворення.',
        'Усі чотири лексичні групи з прикладами, перекладами й обмеженнями становлять core section; не створюємо дрібних disclosures.',
        'Короткий висновок про формальніший лист і просте I need завершує visible comparison.',
        'Короткий фінальний checklist потрібний без додаткового кліку.'],
    ['Коротке застереження про відомого/невідомого виконавця є основним правилом точності.',
        'Коротка ремарка про необов’язковість перетворення завершує visible method.',
        'Різниця між пасивом і номіналізацією є foundational distinction, а не окрема необов’язкова глибина.',
        'Короткий register/complementation caveat потрібний поруч зі сполученнями.',
        'Окремий зв’язний чотириреченнєвий розбір: корисне their comparison проти важких nominal chains, точні спрощення та збереження кількості, часу й статусу плану; один клік виправданий.',
        'Короткий checklist точності завершує core explanation.'],
    ['Основний active/passive приклад і збереження відомого виконавця мають бути видимими.',
        'Два режими редагування і точна інструкція є core framework.',
        'Коротке obtain/get застереження пояснює основну семантичну межу синонімів.',
        'Перехід до прямої інструкції є основною другою частиною tone contrast.',
        'Коротка вимога атрибуції зовнішніх джерел лишається visible basic.',
        'Фінальна перевірка та можливість природної альтернативи завершують основний workflow.'],
];
$retained = [[1, 4]]; // zero-based page/section pair: full two-paragraph context remains basic.
$package = ['version' => 1, 'targets' => []];
foreach ($manifest['targets'] as $i => $target) {
    if ($target['path'] !== $paths[$i] || $target['source_git_blob'] !== $blobs[$i]) { throw new RuntimeException('M16 source allowlist differs.'); }
    $before = $target['before']; $identity = $before['seeder']['class'];
    if ($before['slug'] !== $slugs[$i] || !isset($banks[$identity]) || $banks[$identity]['level'] !== $levels[$i]
        || $before['page']['locale'] !== 'uk' || $before['type'] !== 'theory' || $before['page']['category']['slug'] !== 'formal-english') {
        throw new RuntimeException('M16 page/bank identity/level/category differs.');
    }
    $groups = m32Sections($before['page']['blocks'][1]['body']);
    $blocks = [$before['page']['blocks'][0]]; $plans = []; $audit = [];
    foreach ($groups as $s => $group) {
        $key = 'm32-'.['b2', 'nominalisation', 'tone'][$i].'-section-'.($s + 1);
        $nodes = $group['nodes']; $points = []; $extra = []; $type = 'usage-panels';
        if (isset($group['practice'])) {
            $type = 'practice-set'; $data = m32Practice($i, m32AuthorChecks($group['source_node']), $banks[$identity]);
            $extra['legacy_practice_id'] = $group['practice'];
        } elseif ($s === 7) {
            $type = 'summary-list'; $data = ['title' => $group['title'], 'items' => array_map('m32Inner', $nodes)];
        } else {
            $tableIndex = null;
            foreach ($nodes as $n => $node) { if ($node instanceof DOMElement && $node->getElementsByTagName('table')->length > 0) { $tableIndex = $n; } }
            $retain = in_array([$i, $s], $retained, true);
            if ($tableIndex !== null) {
                $type = 'comparison-table'; $data = ['title' => $group['title']] + m32Table($nodes[$tableIndex], [4, 3, 3][$i]);
                $data['intro'] = m32Prose(array_slice($nodes, 0, $tableIndex));
                $data['outro'] = m32Prose(array_slice($nodes, $tableIndex + 1));
            } else {
                if ($retain) {
                    if (count($nodes) !== 4 || count(array_filter($nodes, fn ($n) => $n->nodeName === 'p')) !== 4) {
                        throw new RuntimeException('M32 retained paragraph basic/detail ownership differs.');
                    }
                    $points = [['basic' => m32Prose(array_slice($nodes, 0, 3)), 'detail' => m32Inner($nodes[3])]];
                } else { $points = m32Points($nodes); }
                $data = ['title' => $group['title'], 'sections' => array_map(fn ($p) =>
                    ['description' => $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')], $points)];
            }
            $paragraphs = array_values(array_filter($nodes, fn ($n) => $n->nodeName === 'p'));
            $candidateNode = $paragraphs !== [] ? $paragraphs[count($paragraphs) - 1] : $nodes[count($nodes) - 1];
            $candidate = m32Inner($candidateNode);
            $visible = m32Prose($nodes);
            $audit[] = ['source_section' => $s + 1, 'point' => $candidateNode->nodeName === 'p' ? 'source-final-paragraph' : 'source-final-list',
                'basic_words' => m32Words($visible) - m32Words($candidate), 'detail_words' => m32Words($candidate),
                'detail_sentences' => m32Sentences($candidate), 'candidate_html' => $candidate,
                'decision' => $retain ? 'meaningful_detail' : 'visible_basic', 'reason' => $reasons[$i][$s]];
        }
        $data['m32_v1'] = ['key' => $key, 'legacy_section' => $s + 1] + $extra;
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
        throw new RuntimeException('M32 refuses to overwrite a manually changed definition.');
    }
    $package['targets'][] = ['path' => $target['path'], 'identity' => $identity, 'slug' => $before['slug'],
        'ancestry' => ['formal-english'], 'after' => $after, 'plans' => $plans, 'detail_quality_audit' => $audit];
}
foreach ($package['targets'] as $target) {
    if (file_put_contents($root.'/'.$target['path'], m32Json($target['after'])) === false) { throw new RuntimeException('M32 definition projection failed.'); }
}
if (file_put_contents($root.'/database/content-patches/m32-m16-formal-english.v1.json', m32Json($package)) === false) {
    throw new RuntimeException('M32 package projection failed.');
}
echo "Projected three M16 sources into eight native blocks/page; meaningful details 0/1/0, exact six cases each.\n";
