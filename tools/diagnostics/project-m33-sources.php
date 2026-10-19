<?php

// Finite technical projection of accepted M17 author content; no Laravel/DB boot.
// Capture is exclusive. Projection requires actual read-only linked-bank evidence.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$baseSha = '872fc9b939818155ca1765f25adac94c1e855702';
$names = ['HedgingAndCautiousLanguageBasics', 'HedgingAndCautiousLanguage', 'StanceRegisterAndEvaluation'];
$slugs = ['hedging-and-cautious-language-basics', 'hedging-and-cautious-language', 'stance-register-and-evaluation'];
$levels = ['B2', 'C1', 'C2'];
$blobs = ['d5eb376fd2c55a30133704a61a6ecca3b8ea2220', '01f6fa20ed67d6cd01b101061e22f505750867c1', '5ec8de96bf6a11c19548ec1a5922905b0fd6b888'];
$paths = array_map(fn ($n) => 'database/seeders/Page_V3/AcademicEnglish/'.$n.'TheorySeeder/definition.json', $names);
$manifestPath = $root.'/database/content-patches/m33-m17-academic-english-before.json';

function m33Json(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}

if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('M33 before manifest already exists.'); }
    $manifest = ['base_sha' => $baseSha, 'targets' => []];
    foreach ($paths as $i => $path) {
        $bytes = file_get_contents($root.'/'.$path);
        $repositoryBytes = str_replace("\r\n", "\n", $bytes);
        $gitBlob = hash('sha1', 'blob '.strlen($repositoryBytes)."\0".$repositoryBytes);
        if ($gitBlob !== $blobs[$i]) { throw new RuntimeException('M17 accepted blob differs: '.$path); }
        $manifest['targets'][] = ['path' => $path, 'source_sha256' => hash('sha256', $bytes), 'source_git_blob' => $gitBlob,
            'before' => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)];
    }
    $file = fopen($manifestPath, 'xb');
    if ($file === false) { throw new RuntimeException('M33 exclusive capture failed.'); }
    $bytes = m33Json($manifest);
    if (fwrite($file, $bytes) !== strlen($bytes)) { fclose($file); throw new RuntimeException('M33 capture incomplete.'); }
    fclose($file);
    echo "Captured three accepted M17 sources, not DB rows.\n";
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
if ($manifest['base_sha'] !== $baseSha || count($manifest['targets']) !== 3) { throw new RuntimeException('M33 finite manifest differs.'); }
$previousTargets = [];
$replaceFlag = array_search('--replace-projection-sha', $argv, true);
if ($replaceFlag !== false) {
    $previousBytes = file_get_contents($root.'/database/content-patches/m33-m17-academic-english.v1.json');
    if (!isset($argv[$replaceFlag + 1]) || !hash_equals($argv[$replaceFlag + 1], hash('sha256', $previousBytes))) {
        throw new RuntimeException('M33 prior generated projection hash differs.');
    }
    foreach (json_decode($previousBytes, true, flags: JSON_THROW_ON_ERROR)['targets'] as $previous) {
        $previousTargets[$previous['identity']] = $previous['after'];
    }
}

function m33Clean(string $html): string { return preg_replace('/\sstyle="[^"]*"/', '', $html); }
function m33Inner(DOMNode $node): string
{
    $out = '';
    foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); }
    return m33Clean(trim($out));
}
function m33Plain(string $html): string { return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }
// Diagnostic whitespace tokens: block/line breaks separate words; inline punctuation stays attached.
// This does not alter answer normalization, author HTML or semantic disclosure decisions.
function m33DiagnosticPlain(string $html): string
{
    $separated = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
    return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($separated), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}
function m33Words(string $html): int { return preg_match_all('/\S+/u', m33DiagnosticPlain($html)); }
function m33Sentences(string $html): int { return preg_match_all('/[.!?](?:\s|$)/u', m33DiagnosticPlain($html)); }
function m33Prose(array $nodes): string
{
    return implode('<br><br>', array_map(fn ($n) => in_array($n->nodeName, ['p', 'li'], true)
        ? m33Inner($n) : m33Clean($n->ownerDocument->saveHTML($n)), $nodes));
}
function m33Sections(string $html): array
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
    if (count($groups) !== 8) { throw new RuntimeException('M17 must have six teaching sections, self-check and continuation.'); }
    return $groups;
}
function m33Points(array $nodes): array
{
    $points = [];
    foreach ($nodes as $node) {
        if (in_array($node->nodeName, ['ul', 'ol'], true)) {
            foreach ($node->childNodes as $li) {
                if ($li instanceof DOMElement && $li->tagName === 'li') { $points[] = ['basic' => m33Inner($li), 'detail' => '']; }
            }
        } else {
            $points[] = ['basic' => $node->nodeName === 'p' ? m33Inner($node) : m33Clean($node->ownerDocument->saveHTML($node)), 'detail' => ''];
        }
    }
    return $points;
}
function m33Table(DOMElement $wrapper, int $columns): array
{
    $headers = array_map(fn ($n) => trim($n->textContent), iterator_to_array($wrapper->getElementsByTagName('th')));
    if (count($headers) !== $columns) { throw new RuntimeException('M17 accepted table columns differ.'); }
    $rows = [];
    foreach ($wrapper->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr) {
        $cells = [];
        foreach ($tr->childNodes as $cell) { if ($cell instanceof DOMElement && $cell->tagName === 'td') { $cells[] = m33Inner($cell); } }
        if (count($cells) !== $columns) { throw new RuntimeException('M17 accepted table row differs.'); }
        $rows[] = ['cells' => $cells];
    }
    $table = $wrapper->getElementsByTagName('table')->item(0);
    if (!preg_match('/min-width:(\\d+)px/', $table->getAttribute('style'), $width)) { throw new RuntimeException('M17 accepted table width missing.'); }
    $minimums = [];
    foreach ($table->getElementsByTagName('th') as $header) {
        $minimums[] = preg_match('/min-width:(\\d+)px/', $header->getAttribute('style'), $column) ? (int) $column[1] : null;
    }
    return ['headers' => $headers, 'rows' => $rows, 'table_min_width' => (int) $width[1], 'column_min_widths' => $minimums];
}
function m33AuthorChecks(DOMElement $source): array
{
    $xp = new DOMXPath($source->ownerDocument);
    $prompts = $xp->query('.//ol[@data-self-checks]/li', $source);
    $keys = $xp->query('.//ol[@data-self-check-answers]/li', $source);
    if ($prompts->length !== 6 || $keys->length !== 6) { throw new RuntimeException('M17 exact six author cases/keys required.'); }
    return ['section_title' => trim($source->getElementsByTagName('h4')->item(0)->textContent),
        'intro' => $source->getElementsByTagName('p')->length > 0 ? m33Inner($source->getElementsByTagName('p')->item(0)) : '',
        'prompts' => array_map('m33Inner', iterator_to_array($prompts)),
        'title' => trim($source->getElementsByTagName('summary')->item(0)->textContent),
        'answers' => array_map('m33Inner', iterator_to_array($keys))];
}
function m33Practice(int $i, array $author, array $bank): array
{
    $p = ['title' => 'Практика', 'choice_options' => ['a', 'b'], 'author_self_check' => $author,
        'selects' => [], 'choices' => [], 'inputs' => [],
        'select_title' => 'Вправа 1. Обери точну відповідь',
        'choice_title' => 'Вправа 2. Обери правильне твердження',
        'input_title' => 'Вправа 3. Побудуй речення',
        'linked_practice' => ['source' => 'theory_links', 'question_types' => [$bank['question_type']],
            'seeder_classes' => [$bank['seeder_class']], 'title' => 'Вправа 4. Побудуй речення',
            'intro' => 'Склади англійське речення за українським.', 'footer' => 'Завдання з наявного тесту цієї сторінки.']];
    if ($i === 0) {
        $p['selects'] = [
            ['options' => ['The printer may need paper. The printer seems to need paper.',
                'The printer may needs paper. The printer seems need paper.',
                'The printer may to need paper. The printer seem to need paper.'],
                'answer' => 'The printer may need paper. The printer seems to need paper.', 'source_case' => 2,
                'punctuation_sensitive' => true],
            ['options' => ['The handle might need repair.', 'The handle might needs repair.', 'The handle might to need repair.'],
                'answer' => 'The handle might need repair.', 'source_case' => 5],
        ];
        $p['choices'] = [
            ['prompt' => 'A) Перше речення — спостереження; друге — можливе пояснення.<br>B) Дощ доведено причиною мокрого килима.',
                'answer' => 'a', 'source_case' => 1],
            ['prompt' => 'A) Could be — можливість зараз; generally — типова робота з винятками.<br>B) Generally гарантує справність ліфта саме зараз.',
                'answer' => 'a', 'source_case' => 3],
        ];
        $p['inputs'] = [
            ['before' => 'rather than forty-five. / compared with / On this journey, / twenty minutes / the new route / our usual route: / was relatively quick',
                'answer' => 'On this journey, the new route was relatively quick compared with our usual route: twenty minutes rather than forty-five.',
                'source_case' => 4, 'punctuation_sensitive' => true],
            ['before' => 'the problem. / was replaced. / The old battery / came on after / may have caused / The lamp / the battery',
                'answer' => 'The lamp came on after the battery was replaced. The old battery may have caused the problem.',
                'accepted' => ['The lamp came on after the battery was replaced. The old battery may have caused the problem.',
                    'The lamp came on after the battery was replaced. The old battery could explain the problem.'],
                'source_case' => 6, 'punctuation_sensitive' => true],
        ];
    } elseif ($i === 1) {
        $p['selects'] = [
            ['options' => [
                'In this group of eight volunteers, seven completed the second search faster, when the new labels were used. The fixed order means that practice cannot be ruled out as an explanation.',
                'Everyone searches faster with the new labels.',
                'In this group of eight volunteers, seven completed the second search faster. The new labels proved to be the cause.'],
                'answer' => 'In this group of eight volunteers, seven completed the second search faster, when the new labels were used. The fixed order means that practice cannot be ruled out as an explanation.',
                'source_case' => 2, 'punctuation_sensitive' => true],
            ['options' => [m33Plain($author['answers'][3]),
                'Обидві конструкції мають однакову силу; початкове may not було забороною.',
                'Початкове may not категорично заперечувало будь-яку користь.'],
                'answer' => m33Plain($author['answers'][3]), 'source_case' => 4,
                'feedback' => [
                    'обидві конструкції мають однакову силу; початкове may not було забороною.' => m33Plain($author['answers'][3]),
                    'початкове may not категорично заперечувало будь-яку користь.' => m33Plain($author['answers'][3]),
                ]],
        ];
        $p['choices'] = [
            ['prompt' => 'A) Однаковий час — заданий результат; допомога підписів — можливе пояснення.<br>B) Однаковий час теж треба подати як непевний факт.',
                'answer' => 'a', 'source_case' => 1],
            ['prompt' => 'A) Not necessarily заперечує неминучість висновку, не будь-яку можливість покращення.<br>B) Інтерфейс точно не став зрозумілішим.',
                'answer' => 'a', 'source_case' => 3],
        ];
        $p['inputs'] = [
            ['before' => 'possible explanation. / The signs / came that day. / the lower number / were installed, but / may have helped; / of visitors / The queues / fewer visitors / is another / the new signs / were shorter after',
                'answer' => 'The queues were shorter after the new signs were installed, but fewer visitors came that day. The signs may have helped; the lower number of visitors is another possible explanation.',
                'source_case' => 5, 'punctuation_sensitive' => true],
            ['before' => 'the change. / may have helped, / completed the / practice may / second search faster. / everyone used / Seven of the / but / also explain / them second, so / eight volunteers / The new labels',
                'answer' => 'Seven of the eight volunteers completed the second search faster. The new labels may have helped, but everyone used them second, so practice may also explain the change.',
                'source_case' => 6, 'punctuation_sensitive' => true],
        ];
    } else {
        $sourceB = 'Source B suggests that the shorter form may reduce copying errors but notes that this possibility has not been tested.';
        $p['selects'] = [
            ['options' => ['inconclusive', 'false'], 'answer' => 'inconclusive', 'source_case' => 2],
            ['options' => ["The checklist contains no revision date. This may make it harder to identify the latest version, although users' choices have not been observed.",
                'The checklist might perhaps contain no revision date, so every user will choose the wrong version.',
                'The checklist contains no revision date, so every user will choose the wrong version.'],
                'answer' => "The checklist contains no revision date. This may make it harder to identify the latest version, although users' choices have not been observed.",
                'source_case' => 5, 'punctuation_sensitive' => true],
        ];
        $p['choices'] = [
            ['prompt' => 'A) Опис → оцінка → рекомендація; критерій другого — придатність для швидкого пошуку.<br>B) Опис → рекомендація → оцінка; посібник непридатний для будь-якого читання.',
                'answer' => 'a', 'source_case' => 1],
            ['prompt' => 'A) '.$sourceB.'<br>B) '.str_replace('suggests', 'states', $sourceB)
                .'<br>C) '.str_replace('suggests', 'writes', $sourceB)
                .'<br>D) Source B proves that the shorter form reduces copying errors.',
                'options' => ['a', 'b', 'c', 'd'], 'answer' => 'a', 'accepted' => ['a', 'b', 'c'],
                'source_case' => 4, 'feedback' => ['d' => m33Plain($author['answers'][3])]],
        ];
        $p['inputs'] = [
            ['before' => 'previous edition. / each term now / This edition improves / unlike the / links to one, / access to examples:',
                'answer' => 'This edition improves access to examples: each term now links to one, unlike the previous edition.',
                'source_case' => 3, 'punctuation_sensitive' => true],
            ['before' => 'remains unknown. / listeners can select / one device, so / For navigation / its compatibility / a clear advantage: / directly. However, / between sections, / with other devices / the new / checked on only / a named section / audio guide offers / it has been',
                'answer' => 'For navigation between sections, the new audio guide offers a clear advantage: listeners can select a named section directly. However, it has been checked on only one device, so its compatibility with other devices remains unknown.',
                'source_case' => 6, 'punctuation_sensitive' => true],
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
    ['Тенденція й відносне порівняння є двома центральними функціями, не окремим поглибленням.',
        'Різниця між suggest як ознакою й пропозицією дії є основним правилом конструкції.',
        'Різні функції прислівників, tend як дієслово й відсутність універсальної шкали становлять core distinction.',
        'Явний орієнтир twenty/forty-five й межа порівняння є центральним прикладом.',
        'Коротка друга половина порівняння request/possibility потрібна одразу.',
        'Уся секція розводить відомий факт і можливу причину; приховувати core correction не можна.'],
    ['Можлива причина, альтернативне пояснення та відсутність доведеної причинності — основа уроку.',
        'Короткий caveat вимагає конкретних групи/методу/умов поруч із таблицею.',
        'Unsupported prove контрастує з indicate і не може бути прихованим як стильова ремарка.',
        'Усі чотири пункти final ul містять основні правила заперечення, контекст may not і межі to some extent.',
        'Окремий зв’язний розбір чотирьох шарів повного абзацу та рекомендації з should; вартий одного кліку після visible EN/UK.',
        'Accepted correction over-hedging із перекладом є головною другою частиною порівняння.'],
    ['Коротке правило criterion/observation/boundary завершує центральне розмежування stance types.',
        'Застереження significant і прямий ефект four→two потрібні разом із видимою оцінною таблицею.',
        'Hedge не створює evidence; direct bounded statement і граматична категорія — core пояснення.',
        'Коротка межа one fictional source≠consensus потрібна у visible reporting-verb секції.',
        'Окремий триреченнєвий аналіз джерела проти авторської позиції та межі інших критеріїв; повний EN/UK огляд лишається basic.',
        'Конструктивний приклад з перекладом і method→consequence→next-step — центральне виправлення, не detail.'],
];
$retained = [[1, 4], [2, 4]]; // Full EN + UK paragraph remains visible; own coherent analysis only.
$package = ['version' => 1, 'targets' => []];
foreach ($manifest['targets'] as $i => $target) {
    if ($target['path'] !== $paths[$i] || $target['source_git_blob'] !== $blobs[$i]) { throw new RuntimeException('M17 source allowlist differs.'); }
    $before = $target['before']; $identity = $before['seeder']['class'];
    if ($before['slug'] !== $slugs[$i] || !isset($banks[$identity]) || $banks[$identity]['level'] !== $levels[$i]
        || $before['page']['locale'] !== 'uk' || $before['type'] !== 'theory' || $before['page']['category']['slug'] !== 'academic-english') {
        throw new RuntimeException('M17 page/bank identity/level/category differs.');
    }
    $groups = m33Sections($before['page']['blocks'][1]['body']);
    $blocks = [$before['page']['blocks'][0]]; $plans = []; $audit = [];
    foreach ($groups as $s => $group) {
        $key = 'm33-'.['b2', 'c1', 'c2'][$i].'-section-'.($s + 1);
        $nodes = $group['nodes']; $points = []; $extra = []; $type = 'usage-panels';
        if (isset($group['practice'])) {
            $type = 'practice-set'; $data = m33Practice($i, m33AuthorChecks($group['source_node']), $banks[$identity]);
            $extra['legacy_practice_id'] = $group['practice'];
        } elseif ($s === 7) {
            $type = 'summary-list'; $data = ['title' => $group['title'], 'items' => array_map('m33Inner', $nodes)];
        } else {
            $tableIndex = null;
            foreach ($nodes as $n => $node) { if ($node instanceof DOMElement && $node->getElementsByTagName('table')->length > 0) { $tableIndex = $n; } }
            $retain = in_array([$i, $s], $retained, true);
            if ($tableIndex !== null) {
                $type = 'comparison-table'; $data = ['title' => $group['title']] + m33Table($nodes[$tableIndex], [4, 3, 3][$i]);
                $data['intro'] = m33Prose(array_slice($nodes, 0, $tableIndex));
                $data['outro'] = m33Prose(array_slice($nodes, $tableIndex + 1));
            } else {
                if ($retain) {
                    if (count($nodes) !== 3 || count(array_filter($nodes, fn ($n) => $n->nodeName === 'p')) !== 3) {
                        throw new RuntimeException('M33 retained paragraph basic/detail ownership differs.');
                    }
                    $points = [['basic' => m33Prose(array_slice($nodes, 0, 2)), 'detail' => m33Inner($nodes[2])]];
                } else { $points = m33Points($nodes); }
                $data = ['title' => $group['title'], 'sections' => array_map(fn ($p) =>
                    ['description' => $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')], $points)];
            }
            $paragraphs = array_values(array_filter($nodes, fn ($n) => $n->nodeName === 'p'));
            $candidateNode = $paragraphs !== [] ? $paragraphs[count($paragraphs) - 1] : $nodes[count($nodes) - 1];
            $candidate = m33Inner($candidateNode);
            $visible = m33Prose($nodes);
            $audit[] = ['source_section' => $s + 1, 'point' => $candidateNode->nodeName === 'p' ? 'source-final-paragraph' : 'source-final-list',
                'basic_words' => m33Words($visible) - m33Words($candidate), 'detail_words' => m33Words($candidate),
                'detail_sentences' => m33Sentences($candidate), 'candidate_html' => $candidate,
                'decision' => $retain ? 'meaningful_detail' : 'visible_basic', 'reason' => $reasons[$i][$s]];
        }
        $data['m33_v1'] = ['key' => $key, 'legacy_section' => $s + 1] + $extra;
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
        throw new RuntimeException('M33 refuses to overwrite a manually changed definition.');
    }
    $package['targets'][] = ['path' => $target['path'], 'identity' => $identity, 'slug' => $before['slug'],
        'ancestry' => ['academic-english'], 'after' => $after, 'plans' => $plans, 'detail_quality_audit' => $audit];
}
foreach ($package['targets'] as $target) {
    if (file_put_contents($root.'/'.$target['path'], m33Json($target['after'])) === false) { throw new RuntimeException('M33 definition projection failed.'); }
}
if (file_put_contents($root.'/database/content-patches/m33-m17-academic-english.v1.json', m33Json($package)) === false) {
    throw new RuntimeException('M33 package projection failed.');
}
echo "Projected three M17 sources into eight native blocks/page; meaningful details 0/1/1, exact six cases each.\n";
