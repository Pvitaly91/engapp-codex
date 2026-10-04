<?php

// Finite technical projection of accepted M18 author content; no Laravel/DB boot.
// Capture is exclusive. Projection requires actual read-only linked-bank evidence.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$baseSha = '736a00dc7ade11bfc2e1b6b4ef282fa8a8d64118';
$names = ['ArgumentationAndAcademicTone', 'DiscourseMarkersAndCohesion', 'ParaphraseAndReformulation'];
$categories = ['AcademicEnglish', 'ClausesAndLinkingWords', 'FormalEnglish'];
$ancestries = ['academic-english', 'clauses-and-linking-words', 'formal-english'];
$slugs = ['argumentation-and-academic-tone', 'discourse-markers-and-cohesion', 'paraphrase-and-reformulation'];
$levels = ['C2', 'C2', 'C2'];
$blobs = ['a2ab4114f979ddc6a81e9b79d3b11836a7f9f7de', '93801c32885c33c451918022997081dabf2a094d', '57f0c9536cb92fae21bafd39dd17cb8b8b2e82e4'];
$paths = array_map(fn ($n, $category) => 'database/seeders/Page_V3/'.$category.'/'.$n.'TheorySeeder/definition.json', $names, $categories);
$manifestPath = $root.'/database/content-patches/m34-m18-argumentation-cohesion-before.json';

function m34Json(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}

if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('M34 before manifest already exists.'); }
    $manifest = ['base_sha' => $baseSha, 'targets' => []];
    foreach ($paths as $i => $path) {
        $bytes = file_get_contents($root.'/'.$path);
        $repositoryBytes = str_replace("\r\n", "\n", $bytes);
        $gitBlob = hash('sha1', 'blob '.strlen($repositoryBytes)."\0".$repositoryBytes);
        if ($gitBlob !== $blobs[$i]) { throw new RuntimeException('M18 accepted blob differs: '.$path); }
        $manifest['targets'][] = ['path' => $path, 'source_sha256' => hash('sha256', $bytes), 'source_git_blob' => $gitBlob,
            'before' => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)];
    }
    $file = fopen($manifestPath, 'xb');
    if ($file === false) { throw new RuntimeException('M34 exclusive capture failed.'); }
    $bytes = m34Json($manifest);
    if (fwrite($file, $bytes) !== strlen($bytes)) { fclose($file); throw new RuntimeException('M34 capture incomplete.'); }
    fclose($file);
    echo "Captured three accepted M18 sources, not DB rows.\n";
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
if ($manifest['base_sha'] !== $baseSha || count($manifest['targets']) !== 3) { throw new RuntimeException('M34 finite manifest differs.'); }
$previousTargets = [];
$replaceFlag = array_search('--replace-projection-sha', $argv, true);
if ($replaceFlag !== false) {
    $previousBytes = file_get_contents($root.'/database/content-patches/m34-m18-argumentation-cohesion.v1.json');
    if (!isset($argv[$replaceFlag + 1]) || !hash_equals($argv[$replaceFlag + 1], hash('sha256', $previousBytes))) {
        throw new RuntimeException('M34 prior generated projection hash differs.');
    }
    foreach (json_decode($previousBytes, true, flags: JSON_THROW_ON_ERROR)['targets'] as $previous) {
        $previousTargets[$previous['identity']] = $previous['after'];
    }
}

function m34Clean(string $html): string { return preg_replace('/\sstyle="[^"]*"/', '', $html); }
function m34Inner(DOMNode $node): string
{
    $out = '';
    foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); }
    return m34Clean(trim($out));
}
function m34Plain(string $html): string { return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }
// Diagnostic whitespace tokens: block/line breaks separate words; inline punctuation stays attached.
// This does not alter answer normalization, author HTML or semantic disclosure decisions.
function m34DiagnosticPlain(string $html): string
{
    $separated = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
    return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($separated), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}
function m34Words(string $html): int { return preg_match_all('/\S+/u', m34DiagnosticPlain($html)); }
function m34Sentences(string $html): int { return preg_match_all('/[.!?](?:\s|$)/u', m34DiagnosticPlain($html)); }
function m34Prose(array $nodes): string
{
    return implode('<br><br>', array_map(fn ($n) => in_array($n->nodeName, ['p', 'li'], true)
        ? m34Inner($n) : m34Clean($n->ownerDocument->saveHTML($n)), $nodes));
}
function m34Sections(string $html): array
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
    if (count($groups) !== 8) { throw new RuntimeException('M18 must have six teaching sections, self-check and continuation.'); }
    return $groups;
}
function m34Points(array $nodes): array
{
    $points = [];
    foreach ($nodes as $node) {
        if (in_array($node->nodeName, ['ul', 'ol'], true)) {
            foreach ($node->childNodes as $li) {
                if ($li instanceof DOMElement && $li->tagName === 'li') { $points[] = ['basic' => m34Inner($li), 'detail' => '']; }
            }
        } else {
            $points[] = ['basic' => $node->nodeName === 'p' ? m34Inner($node) : m34Clean($node->ownerDocument->saveHTML($node)), 'detail' => ''];
        }
    }
    return $points;
}
function m34Table(DOMElement $wrapper, int $columns): array
{
    $headers = array_map(fn ($n) => trim($n->textContent), iterator_to_array($wrapper->getElementsByTagName('th')));
    if (count($headers) !== $columns) { throw new RuntimeException('M18 accepted table columns differ.'); }
    $rows = [];
    foreach ($wrapper->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr) {
        $cells = [];
        foreach ($tr->childNodes as $cell) { if ($cell instanceof DOMElement && $cell->tagName === 'td') { $cells[] = m34Inner($cell); } }
        if (count($cells) !== $columns) { throw new RuntimeException('M18 accepted table row differs.'); }
        $rows[] = ['cells' => $cells];
    }
    $table = $wrapper->getElementsByTagName('table')->item(0);
    if (!preg_match('/min-width:(\\d+)px/', $table->getAttribute('style'), $width)) { throw new RuntimeException('M18 accepted table width missing.'); }
    $minimums = [];
    foreach ($table->getElementsByTagName('th') as $header) {
        $minimums[] = preg_match('/min-width:(\\d+)px/', $header->getAttribute('style'), $column) ? (int) $column[1] : null;
    }
    return ['headers' => $headers, 'rows' => $rows, 'table_min_width' => (int) $width[1], 'column_min_widths' => $minimums];
}
function m34AuthorChecks(DOMElement $source): array
{
    $xp = new DOMXPath($source->ownerDocument);
    $prompts = $xp->query('.//ol[@data-self-checks]/li', $source);
    $keys = $xp->query('.//ol[@data-self-check-answers]/li', $source);
    if ($prompts->length !== 6 || $keys->length !== 6) { throw new RuntimeException('M18 exact six author cases/keys required.'); }
    return ['section_title' => trim($source->getElementsByTagName('h4')->item(0)->textContent),
        'intro' => $source->getElementsByTagName('p')->length > 0 ? m34Inner($source->getElementsByTagName('p')->item(0)) : '',
        'prompts' => array_map('m34Inner', iterator_to_array($prompts)),
        'title' => trim($source->getElementsByTagName('summary')->item(0)->textContent),
        'answers' => array_map('m34Inner', iterator_to_array($keys))];
}
// Mechanical finite token groups; they only rearrange words of the accepted answer.
function m34TokenInput(string $answer, int $case, array $accepted = []): array
{
    $chunks = array_map(fn ($words) => implode(' ', $words), array_chunk(preg_split('/\s+/u', $answer), 3));
    return ['before' => implode(' / ', array_reverse($chunks)), 'answer' => $answer,
        'accepted' => $accepted !== [] ? $accepted : [$answer], 'source_case' => $case, 'punctuation_sensitive' => true];
}
function m34Practice(int $i, array $author, array $bank): array
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
        $bounded = 'On the desktop screen described here, the new design allows two club timetables to be compared without switching views.';
        $boundedAlternative = 'The new layout supports side-by-side comparison on the desktop screen tested here.';
        $objection = 'Two timetables may be hard to read on a phone. This concern is valid because phone legibility has not been checked. The recommendation should therefore be limited to the desktop comparison task for now.';
        $objectionAlternative = 'A reasonable objection is that two timetables may be difficult to read on a narrow phone screen. That concern limits a recommendation for all devices. Since no phone test is available, the present case supports the desktop comparison task only.';
        $p['selects'] = [
            ['options' => [$bounded, $boundedAlternative, 'The new design improves every aspect of booking for all users.',
                'The new design makes everyone book faster on every device.'],
                'answer' => $bounded, 'accepted' => [$bounded, $boundedAlternative], 'source_case' => 3, 'punctuation_sensitive' => true],
            ['options' => [$objection, $objectionAlternative,
                'Phone legibility has been tested successfully, so the layout should be used on all devices.',
                'The desktop comparison proves that two timetables are readable on every phone.'],
                'answer' => $objection, 'accepted' => [$objection, $objectionAlternative], 'source_case' => 4, 'punctuation_sensitive' => true],
        ];
        $p['choices'] = [
            ['prompt' => 'A) Теза — 1; опора — 2; міркування — 3; речення 4 обмежує висновок одночасним доступом, не доведеною швидкістю.<br>B) Речення 4 доводить швидше бронювання, бо обидва розклади видно.',
                'answer' => 'a', 'source_case' => 1],
            ['prompt' => 'A) Бракує перевірки читабельності обох розкладів на конкретному телефоні; every phone виходить за дані.<br>B) Якщо обидва розклади вміщаються, їхню читабельність та користь на всіх телефонах уже доведено.',
                'answer' => 'a', 'source_case' => 2],
        ];
        $poster = 'The poster changed on Monday, and attendance increased on Tuesday. These facts alone do not establish that the new poster caused the increase.';
        $guide = 'Guide B offers a useful aid for locating definitions. Its index points to every definition, whereas Guide A has no index. Readers can therefore use Guide B’s index to find the page of a particular definition. This does not establish that they will find it faster, since search times were not measured.';
        $p['inputs'] = [
            m34TokenInput($poster, 5, [$poster,
                'The poster changed on Monday. Attendance increased on Tuesday. These facts alone do not establish that the new poster caused the increase.']),
            m34TokenInput($guide, 6, [$guide,
                'For locating the page of a particular definition, Guide B offers a useful aid: its index points to every definition, whereas Guide A has no index. Readers can therefore use Guide B’s index to locate that page. This does not establish faster searching, since search times were not measured.']),
        ];
    } elseif ($i === 1) {
        $nonetheless = 'The instructions are brief. Nonetheless, they describe all three stages.';
        $nevertheless = 'The instructions are brief. Nevertheless, they describe all three stages.';
        $addition = 'The handbook includes a glossary. Moreover, it provides an index of names.';
        $additionVariants = [$addition,
            'The handbook includes a glossary. Furthermore, it provides an index of names.',
            'The handbook includes a glossary. In addition, it provides an index of names.',
            'The handbook includes a glossary. It provides an index of names.',
            'The handbook includes a glossary and provides an index of names.'];
        $p['selects'] = [
            ['options' => [$nonetheless, $nevertheless, 'The instructions are brief. Conversely, they describe all three stages.',
                'The instructions are brief. Consequently, they describe all three stages.'],
                'answer' => $nonetheless, 'accepted' => [$nonetheless, $nevertheless], 'source_case' => 1, 'punctuation_sensitive' => true],
            ['options' => array_merge($additionVariants, ['The handbook includes a glossary. Consequently, it provides an index of names.']),
                'answer' => $addition, 'accepted' => $additionVariants, 'source_case' => 2, 'punctuation_sensitive' => true],
        ];
        $precise = 'The files have clear titles, but three files have no dates. In light of these missing dates, the chronology remains uncertain.';
        $report = 'The report has two main sections: a summary and a list of costs.';
        $reportAlternative = 'The report contains a summary and a list of costs. These are its two main sections.';
        $p['choices'] = [
            ['prompt' => 'A) '.$precise.'<br>B) The files have clear titles, but three files have no dates. In light of these clear titles, the chronology remains uncertain.',
                'answer' => 'a', 'source_case' => 4],
            ['prompt' => 'A) '.$report.'<br>B) '.$reportAlternative.'<br>C) The report contains a summary; consequently, it lists the costs.',
                'options' => ['a', 'b', 'c'], 'answer' => 'a', 'accepted' => ['a', 'b'], 'source_case' => 6],
        ];
        $archive = 'The archive is small; however, it contains every issue from 2020.';
        $transition = 'This colour-based distinction raises a further question: how clearly would the three routes remain distinguishable in a black-and-white copy?';
        $p['inputs'] = [
            m34TokenInput($archive, 3, [$archive, 'The archive is small. However, it contains every issue from 2020.']),
            m34TokenInput($transition, 5, [$transition,
                'Given this colour-based distinction between the three routes, how clearly would they remain distinguishable in a black-and-white copy?']),
        ];
    } else {
        $clarification = 'Only eligible applicants—that is, applicants who meet all three stated conditions—can register.';
        $clarificationVariants = [$clarification,
            'Only eligible applicants—that is to say, applicants who meet all three stated conditions—can register.',
            'Only applicants who meet all three stated conditions can register.'];
        $p['selects'] = [
            ['options' => [m34Plain($author['answers'][0]), 'Це конкретизація; 9.15 не можна вивести лише з in the morning.',
                'Це точне перефразування; з in the morning випливає саме 9.15.',
                'Це самовиправлення: ранковий час відкликано.'],
                'answer' => m34Plain($author['answers'][0]),
                'accepted' => [m34Plain($author['answers'][0]), 'Це конкретизація; 9.15 не можна вивести лише з in the morning.'], 'source_case' => 1],
            ['options' => array_merge($clarificationVariants, ['All applicants who meet all three stated conditions will be registered.',
                'Only applicants who meet at least one stated condition can register.']),
                'answer' => $clarification, 'accepted' => $clarificationVariants, 'source_case' => 5, 'punctuation_sensitive' => true],
        ];
        $record = 'The record says that four readers returned the guide after one day, but gives no reasons. Confusion is only a possible explanation, not a recorded finding.';
        $recordAlternative = 'The record says that four readers returned the guide after one day, but gives no reasons. The reason is unknown.';
        $p['choices'] = [
            ['prompt' => 'A) '.$record.'<br>B) '.$recordAlternative.'<br>C) The record proves that four readers returned the guide because they found it confusing.',
                'options' => ['a', 'b', 'c'], 'answer' => 'a', 'accepted' => ['a', 'b'], 'source_case' => 3],
            ['prompt' => 'A) Not all не задає точного числа; no заперечує будь-яку кількість. At least four дозволяє більше чотирьох; exactly four — ні. Some may → all will розширює кількість і посилює можливість до прогнозу.<br>B) Усі три заміни лише стилістичні й не змінюють твердження.',
                'answer' => 'a', 'source_case' => 4],
        ];
        $volunteers = 'The organiser’s note says that it is possible for two volunteers to open the exhibition on Monday if the keys arrive by noon. It does not identify those volunteers.';
        $museum = 'According to the note, the museum tested the audio guide on a single tablet and did not test it on a phone. The note therefore provides no phone-test result; I cannot use it to claim either successful or unsuccessful phone operation.';
        $p['inputs'] = [
            m34TokenInput($volunteers, 2, [$volunteers,
                'According to the organiser’s note, the exhibition may be opened on Monday by two volunteers if the keys arrive by noon. Those volunteers are not named in the note.']),
            m34TokenInput($museum, 6, [$museum,
                'The note says that the museum tested the audio guide on one tablet, with no phone test carried out. I therefore cannot infer either successful or unsuccessful phone operation from the note, since it gives no phone-test result.']),
        ];
    }
    foreach (['selects', 'choices', 'inputs'] as $kind) {
        foreach ($p[$kind] as &$item) {
            $case = $item['source_case']; unset($item['source_case']);
            $item['source_index'] = $case; $item['context'] = $author['prompts'][$case - 1];
            $item['author_explanation'] = $author['answers'][$case - 1];
            if ($kind !== 'inputs') {
                $item['label'] = '';
                $options = $kind === 'choices' ? ($item['options'] ?? $p['choice_options']) : $item['options'];
                $item['feedback'] = array_fill_keys(array_map(fn ($option) => mb_strtolower($option, 'UTF-8'), $options),
                    m34Plain($author['answers'][$case - 1]));
            }
        }
        unset($item);
    }
    return $p;
}

// Explicit semantic decisions. Length is diagnostic, never a runtime threshold.
$reasons = [
    ['Обмежена модель аргументу й атрибуція є центральними умовами, потрібними поруч із таблицею.',
        'Окремий зв’язний чотирипунктовий розбір функцій повного абзацу; весь вигаданий контекст, EN та UK лишаються basic.',
        'Умовний телефонний висновок, переклад і відсутність проведеного тесту — основна межа аргументу.',
        'Часткове прийняття заперечення, звуження тези й вага підстав є головним правилом відповіді.',
        'Контрприклад понеділок/вівторок і застереження sequence≠cause є центральним поясненням маркерів.',
        'Коротке правило direct known/unknown і відсутність універсальної заборони I/we потрібні одразу.'],
    ['Відношення між думками передує вибору маркера; це основний алгоритм.',
        'Коротке застереження про несинонімічність функцій і не обов’язковість маркера лишається біля таблиці.',
        'That said/Having said that і межа кваліфікації не становлять окремого поглиблення.',
        'Robust/reliable та прозорий зв’язок accordingly — центральне пояснення семантики.',
        'Окремий зв’язний аналіз відсилання у парі абзаців; повні обидва EN/UK абзаци, відсутність даних і правило референта видимі.',
        'Повний виправлений звіт, переклад і відомі факти є основним контрастом, не detail.'],
    ['Коротке застереження marker≠guarantee завершує основне розмежування функцій.',
        'Коротке правило невзаємозамінності потрібне разом із видимими прикладами.',
        'Контекст, усі версії, переклади й межа own interpretation проти повного переказу є core distinction.',
        'Усі сім пунктів є основним контрольним списком учасників, часу, умов, заперечення, кількості, модальності й причинності.',
        'Перефокусування не доводить оцінку; повний приклад із перекладом потрібен одразу.',
        'Точний журнал і межа власного висновку з перекладом — центральне виправлення, не додатковий аналіз.'],
];
$retained = [[0, 1], [1, 4]]; // Only the two coherent analyses; full context and translation stay visible.
$package = ['version' => 1, 'targets' => []];
foreach ($manifest['targets'] as $i => $target) {
    if ($target['path'] !== $paths[$i] || $target['source_git_blob'] !== $blobs[$i]) { throw new RuntimeException('M18 source allowlist differs.'); }
    $before = $target['before']; $identity = $before['seeder']['class'];
    if ($before['slug'] !== $slugs[$i] || !isset($banks[$identity]) || $banks[$identity]['level'] !== $levels[$i]
        || $before['page']['locale'] !== 'uk' || $before['type'] !== 'theory' || $before['page']['category']['slug'] !== $ancestries[$i]) {
        throw new RuntimeException('M18 page/bank identity/level/category differs.');
    }
    $groups = m34Sections($before['page']['blocks'][1]['body']);
    $blocks = [$before['page']['blocks'][0]]; $plans = []; $audit = [];
    foreach ($groups as $s => $group) {
        $key = 'm34-'.['argumentation', 'discourse', 'paraphrase'][$i].'-section-'.($s + 1);
        $nodes = $group['nodes']; $points = []; $extra = []; $type = 'usage-panels';
        if (isset($group['practice'])) {
            $type = 'practice-set'; $data = m34Practice($i, m34AuthorChecks($group['source_node']), $banks[$identity]);
            $extra['legacy_practice_id'] = $group['practice'];
        } elseif ($s === 7) {
            $type = 'summary-list'; $data = ['title' => $group['title'], 'items' => array_map('m34Inner', $nodes)];
        } else {
            $tableIndex = null;
            foreach ($nodes as $n => $node) { if ($node instanceof DOMElement && $node->getElementsByTagName('table')->length > 0) { $tableIndex = $n; } }
            $retain = in_array([$i, $s], $retained, true);
            if ($tableIndex !== null) {
                $type = 'comparison-table'; $data = ['title' => $group['title']] + m34Table($nodes[$tableIndex], 3);
                $data['intro'] = m34Prose(array_slice($nodes, 0, $tableIndex));
                $data['outro'] = m34Prose(array_slice($nodes, $tableIndex + 1));
            } else {
                if ($i === 0 && $s === 1) {
                    if (count($nodes) !== 4 || $nodes[3]->nodeName !== 'ol') {
                        throw new RuntimeException('M34 argumentation paragraph/analysis ownership differs.');
                    }
                    $points = [['basic' => m34Prose(array_slice($nodes, 0, 3)),
                        'detail' => m34Clean($nodes[3]->ownerDocument->saveHTML($nodes[3]))]];
                } elseif ($i === 1 && $s === 4) {
                    if (count($nodes) !== 7 || count(array_filter($nodes, fn ($n) => $n->nodeName === 'p')) !== 7) {
                        throw new RuntimeException('M34 discourse paragraph/analysis ownership differs.');
                    }
                    $points = [['basic' => m34Prose(array_slice($nodes, 0, 4)), 'detail' => m34Inner($nodes[4])]];
                    $points = array_merge($points, m34Points(array_slice($nodes, 5)));
                } else { $points = m34Points($nodes); }
                $data = ['title' => $group['title'], 'sections' => array_map(fn ($p) =>
                    ['description' => $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')], $points)];
            }
            $paragraphs = array_values(array_filter($nodes, fn ($n) => $n->nodeName === 'p'));
            $candidateNode = $i === 0 && $s === 1 ? $nodes[3]
                : ($i === 1 && $s === 4 ? $nodes[4]
                : ($paragraphs !== [] ? $paragraphs[count($paragraphs) - 1] : $nodes[count($nodes) - 1]));
            $candidate = m34Inner($candidateNode);
            $visible = m34Prose($nodes);
            $point = $i === 0 && $s === 1 ? 'source-analysis-list'
                : ($i === 1 && $s === 4 ? 'source-paragraph-analysis'
                : ($candidateNode->nodeName === 'p' ? 'source-final-paragraph' : 'source-final-list'));
            $audit[] = ['source_section' => $s + 1, 'point' => $point,
                'basic_words' => m34Words($visible) - m34Words($candidate), 'detail_words' => m34Words($candidate),
                'detail_sentences' => m34Sentences($candidate), 'candidate_html' => $candidate,
                'decision' => $retain ? 'meaningful_detail' : 'visible_basic', 'reason' => $reasons[$i][$s]];
        }
        $data['m34_v1'] = ['key' => $key, 'legacy_section' => $s + 1] + $extra;
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
        throw new RuntimeException('M34 refuses to overwrite a manually changed definition.');
    }
    $package['targets'][] = ['path' => $target['path'], 'identity' => $identity, 'slug' => $before['slug'],
        'ancestry' => [$ancestries[$i]], 'after' => $after, 'plans' => $plans, 'detail_quality_audit' => $audit];
}
foreach ($package['targets'] as $target) {
    if (file_put_contents($root.'/'.$target['path'], m34Json($target['after'])) === false) { throw new RuntimeException('M34 definition projection failed.'); }
}
if (file_put_contents($root.'/database/content-patches/m34-m18-argumentation-cohesion.v1.json', m34Json($package)) === false) {
    throw new RuntimeException('M34 package projection failed.');
}
echo "Projected three M18 sources into eight native blocks/page; meaningful details 1/1/0, exact six cases each.\n";
