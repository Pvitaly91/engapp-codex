<?php

// Finite technical projection of accepted M15 author content; no Laravel/DB boot.
// Capture is exclusive. Projection requires actual read-only linked-bank evidence.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$baseSha = 'ceb2162c862978ca461607be3913a9f717f380b2';
$names = ['ConditionalsWithUnlessProvidedAsLongAs', 'AdvancedConditionals', 'ConditionalAlternativesAndNuance'];
$slugs = ['conditionals-with-unless-provided-as-long-as', 'advanced-conditionals', 'conditional-alternatives-and-nuance'];
$levels = ['B2', 'C1', 'C2'];
$blobs = ['84b42d6b57c27b1c4d47719395758f19f57b9d63', 'cc056a2abdb231c7883e905703502d44e6885570', 'c685fcea76e89659d9dd8b639f5ce9d3e5a905f6'];
$paths = array_map(fn ($n) => 'database/seeders/Page_V3/Conditionals/'.$n.'TheorySeeder/definition.json', $names);
$manifestPath = $root.'/database/content-patches/m31-m15-conditionals-before.json';

function m31Json(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}

if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('M31 before manifest already exists.'); }
    $manifest = ['base_sha' => $baseSha, 'targets' => []];
    foreach ($paths as $i => $path) {
        $bytes = file_get_contents($root.'/'.$path);
        $repositoryBytes = str_replace("\r\n", "\n", $bytes);
        $gitBlob = hash('sha1', 'blob '.strlen($repositoryBytes)."\0".$repositoryBytes);
        if ($gitBlob !== $blobs[$i]) { throw new RuntimeException('M15 accepted blob differs: '.$path); }
        $manifest['targets'][] = ['path' => $path, 'source_sha256' => hash('sha256', $bytes), 'source_git_blob' => $gitBlob,
            'before' => json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)];
    }
    $file = fopen($manifestPath, 'xb');
    if ($file === false) { throw new RuntimeException('M31 exclusive capture failed.'); }
    $bytes = m31Json($manifest);
    if (fwrite($file, $bytes) !== strlen($bytes)) { fclose($file); throw new RuntimeException('M31 capture incomplete.'); }
    fclose($file);
    echo "Captured three accepted M15 sources, not DB rows.\n";
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
if ($manifest['base_sha'] !== $baseSha || count($manifest['targets']) !== 3) { throw new RuntimeException('M31 finite manifest differs.'); }
$previousTargets = [];
$replaceFlag = array_search('--replace-projection-sha', $argv, true);
if ($replaceFlag !== false) {
    $previousBytes = file_get_contents($root.'/database/content-patches/m31-m15-conditionals.v1.json');
    if (!isset($argv[$replaceFlag + 1]) || !hash_equals($argv[$replaceFlag + 1], hash('sha256', $previousBytes))) {
        throw new RuntimeException('M31 prior generated projection hash differs.');
    }
    foreach (json_decode($previousBytes, true, flags: JSON_THROW_ON_ERROR)['targets'] as $previous) {
        $previousTargets[$previous['identity']] = $previous['after'];
    }
}

function m31Clean(string $html): string { return preg_replace('/\sstyle="[^"]*"/', '', $html); }
function m31Inner(DOMNode $node): string
{
    $out = '';
    foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); }
    return m31Clean(trim($out));
}
function m31Plain(string $html): string { return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }
function m31Words(string $html): int { return preg_match_all('/\S+/u', m31Plain($html)); }
function m31Sentences(string $html): int { return preg_match_all('/[.!?](?:\s|$)/u', m31Plain($html)); }
function m31Prose(array $nodes): string
{
    return implode('<br><br>', array_map(fn ($n) => in_array($n->nodeName, ['p', 'li'], true)
        ? m31Inner($n) : m31Clean($n->ownerDocument->saveHTML($n)), $nodes));
}
function m31Sections(string $html): array
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
    if (count($groups) !== 8) { throw new RuntimeException('M15 must have six teaching sections, self-check and continuation.'); }
    return $groups;
}
function m31Points(array $nodes): array
{
    $points = [];
    foreach ($nodes as $node) {
        if (in_array($node->nodeName, ['ul', 'ol'], true)) {
            foreach ($node->childNodes as $li) {
                if ($li instanceof DOMElement && $li->tagName === 'li') { $points[] = ['basic' => m31Inner($li), 'detail' => '']; }
            }
        } else {
            $points[] = ['basic' => $node->nodeName === 'p' ? m31Inner($node) : m31Clean($node->ownerDocument->saveHTML($node)), 'detail' => ''];
        }
    }
    return $points;
}
function m31Table(DOMElement $wrapper, int $columns): array
{
    $headers = array_map(fn ($n) => trim($n->textContent), iterator_to_array($wrapper->getElementsByTagName('th')));
    if (count($headers) !== $columns) { throw new RuntimeException('M15 accepted table columns differ.'); }
    $rows = [];
    foreach ($wrapper->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr) {
        $cells = [];
        foreach ($tr->childNodes as $cell) { if ($cell instanceof DOMElement && $cell->tagName === 'td') { $cells[] = m31Inner($cell); } }
        if (count($cells) !== $columns) { throw new RuntimeException('M15 accepted table row differs.'); }
        $rows[] = ['cells' => $cells];
    }
    return ['headers' => $headers, 'rows' => $rows];
}
function m31AuthorChecks(DOMElement $source): array
{
    $xp = new DOMXPath($source->ownerDocument);
    $prompts = $xp->query('.//ol[@data-self-checks]/li', $source);
    $keys = $xp->query('.//ol[@data-self-check-answers]/li', $source);
    if ($prompts->length !== 6 || $keys->length !== 6) { throw new RuntimeException('M15 exact six author cases/keys required.'); }
    return ['section_title' => trim($source->getElementsByTagName('h4')->item(0)->textContent),
        'intro' => m31Inner($source->getElementsByTagName('p')->item(0)),
        'prompts' => array_map('m31Inner', iterator_to_array($prompts)),
        'title' => trim($source->getElementsByTagName('summary')->item(0)->textContent),
        'answers' => array_map('m31Inner', iterator_to_array($keys))];
}
function m31Practice(int $i, array $author, array $bank): array
{
    $p = ['title' => 'Практика', 'select_title' => 'Вправа 1. Обери правильну відповідь',
        'choice_title' => 'Вправа 2. Обери правильний варіант', 'input_title' => 'Вправа 3. Побудуй речення',
        'choice_options' => ['a', 'b'], 'author_self_check' => $author,
        'linked_practice' => ['source' => 'theory_links', 'question_types' => [$bank['question_type']],
            'seeder_classes' => [$bank['seeder_class']], 'title' => 'Вправа 4. Побудуй речення',
            'intro' => 'Склади англійське речення за українським.', 'footer' => 'Завдання з наявного тесту цієї сторінки.']];
    if ($i === 0) {
        $p['selects'] = [
            ['options' => ['Unless you confirm the booking, we’ll release the room.', 'Unless you don’t confirm the booking, we’ll release the room.'],
                'answer' => 'Unless you confirm the booking, we’ll release the room.', 'source_case' => 1],
            ['options' => ['We’ll hold the workshop outside unless it rains.', 'We’ll hold the workshop outside unless it doesn’t rain.'],
                'answer' => 'We’ll hold the workshop outside unless it rains.', 'source_case' => 2]];
        $p['choices'] = [
            ['prompt' => 'A) You may use the studio provided that you return the key by eight.<br>B) You may use the studio as long as you return the key by eight.',
                'answer' => 'a', 'source_case' => 3, 'feedback' => [
                    'b' => 'As long as було б природним за змістом, але не виконує конкретної інструкції використати provided that.',
                ]],
            ['prompt' => 'A) A — умова дозволу; B — тривалість.<br>B) A — тривалість; B — умова дозволу.',
                'answer' => 'a', 'source_case' => 4]];
        $p['inputs'] = [
            ['before' => 'Eva / will reserve / by Friday. / The organiser / confirms / provided that / a desk',
                'answer' => 'The organiser will reserve a desk provided that Eva confirms by Friday.', 'source_case' => 5],
            ['before' => 'you pay / by noon, / it / Unless / provided that / I’ll cancel / You can keep / today. / you reply / your seat.',
                'answer' => 'Unless you reply by noon, I’ll cancel your seat. You can keep it provided that you pay today.',
                'accepted' => ['Unless you reply by noon, I’ll cancel your seat. You can keep it provided that you pay today.',
                    'Unless you reply by noon, I will cancel your seat. You can keep it provided that you pay today.'],
                'source_case' => 6, 'punctuation_sensitive' => true]];
    } elseif ($i === 1) {
        $p['selects'] = [
            ['options' => ['If we had charged the battery yesterday, the device would be working now.',
                    'If we had charged the battery yesterday, the device would have been working yesterday.'],
                'answer' => 'If we had charged the battery yesterday, the device would be working now.', 'source_case' => 1],
            ['options' => ['If Iryna had accepted the offer last year, she would be working at the museum now.',
                    'If Iryna had accepted the offer last year, she would have worked there last year.'],
                'answer' => 'If Iryna had accepted the offer last year, she would be working at the museum now.', 'source_case' => 2]];
        $p['choices'] = [
            ['prompt' => 'A) If Danylo weren’t afraid of heights, he would have climbed the tower last week.<br>B) If Danylo hadn’t been afraid of heights last week, he would climb the tower now.',
                'answer' => 'a', 'source_case' => 3],
            ['prompt' => 'A) Had the curator seen the letter, she might have postponed the exhibition.<br>B) Had the curator seen the letter, she would have postponed the exhibition.',
                'answer' => 'a', 'source_case' => 5]];
        $p['inputs'] = [
            ['before' => 'the access code, / now. / Had / we could open / we kept / the archive',
                'answer' => 'Had we kept the access code, we could open the archive now.', 'source_case' => 6],
            ['before' => 'the report / ready to start / on Tuesday. / we would have / the report / we would be / finished / now.',
                'answer' => 'We would have finished the report on Tuesday. We would be ready to start the report now.',
                'source_case' => 4, 'punctuation_sensitive' => true]];
    } else {
        $p['selects'] = [
            ['options' => ['If you do not send the signed form today, we will postpone the visit.',
                    'If the form is not signed, we will postpone the visit.'],
                'answer' => 'If you do not send the signed form today, we will postpone the visit.', 'source_case' => 2],
            ['options' => ['If it had not been for the backup generator, the archive would have lost its files.',
                    'If it were not for the backup generator, the archive would have lost its files.'],
                'answer' => 'If it had not been for the backup generator, the archive would have lost its files.', 'source_case' => 3]];
        $p['choices'] = [
            ['prompt' => 'A) A — робоче припущення; B — установлена вимога.<br>B) A — установлена вимога; B — робоче припущення.',
                'answer' => 'a', 'source_case' => 1],
            ['prompt' => 'A) Had Lena not checked the address, the parcel might have gone to the wrong office.<br>B) Hadn’t Lena checked the address, the parcel might have gone to the wrong office?',
                'answer' => 'a', 'source_case' => 5]];
        $p['inputs'] = [
            ['before' => 'the librarian. / Had the courier / a printed copy, / we would stay / could have sent / Should you need / on the island. / contact / Were the ferry / arrived earlier, / the sample. / to stop running, / we',
                'answer' => 'Should you need a printed copy, contact the librarian. Were the ferry to stop running, we would stay on the island. Had the courier arrived earlier, we could have sent the sample.',
                'source_case' => 4, 'punctuation_sensitive' => true],
            ['before' => 'the director gives / the opening. / Assuming / on condition that / We may open / we might open / otherwise, / the grant / written approval; / on Monday. / we will postpone / is confirmed,',
                'answer' => 'Assuming the grant is confirmed, we might open on Monday. We may open on condition that the director gives written approval; otherwise, we will postpone the opening.',
                'accepted' => [
                    'Assuming the grant is confirmed, we might open on Monday. We may open on condition that the director gives written approval; otherwise, we will postpone the opening.',
                    'Assuming the grant is confirmed, we might open on Monday. We may open provided that the director gives written approval; otherwise, we will postpone the opening.',
                    'Assuming the grant is confirmed, we might open on Monday. We may open on condition that the director gives written approval. Otherwise, we will postpone the opening.',
                    'Assuming the grant is confirmed, we might open on Monday. We may open provided that the director gives written approval. Otherwise, we will postpone the opening.',
                ], 'source_case' => 6, 'punctuation_sensitive' => true]];
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
    ['Коротка редакційна ремарка й основна мета уроку, не окреме поглиблення.',
        'Регістр і сумісність трьох сполучників пояснюють основну таблицю; unless потребує видимого застереження.',
        'Відомий минулий контрфакт — важлива межа механічної заміни unless, не optional addendum.',
        'Приклад тривалості є необхідною другою половиною основного контрасту as long as.',
        'Сфера правила майбутньої умови та виняток will/would повинні бути видимими.',
        'Коротке застереження й контроль заперечення завершують видимі ситуації.'],
    ['Основний аналіз фактів і уявного процесу пояснює foundational framework.',
        'Часовий контраст і state/process пояснюють основну таблицю.',
        'Окремий минулий епізод необхідний для контрасту зі стійкою рисою; приклад і caveat лишаються basic.',
        'Основне правило would/could/might та збереження модальності потрібні перед прикладами.',
        'Окремий зв’язний розбір повного абзацу: факти, past→present, непевність, tomorrow’s і сила might/would; п’ять пояснювальних речень виправдовують один клік.',
        'Коротке застереження про можливість і зворотну причинність завершує core analysis.'],
    ['Основна відмінність requirement/assumption та редакційна ремарка лишаються видимими.',
        'Коротке правило пунктуації потрібно поруч з otherwise і повним antecedent.',
        'Короткий міст до if/inversion не є окремим поглибленням.',
        'Застереження від механічної інверсії та повний приклад — важлива межа основного правила.',
        'Короткий контраст із запитанням завершує правило заперечної умови.',
        'Окремий зв’язний розбір чотирьох маркерів у повному тексті: assumption, permission requirement, точний otherwise antecedent і запасна should-інструкція; п’ять речень виправдовують один клік.'],
];
$retained = [[1, 4], [2, 5]]; // zero-based page/section pairs, each one paragraph-level point.
$package = ['version' => 1, 'targets' => []];
foreach ($manifest['targets'] as $i => $target) {
    if ($target['path'] !== $paths[$i] || $target['source_git_blob'] !== $blobs[$i]) { throw new RuntimeException('M15 source allowlist differs.'); }
    $before = $target['before']; $identity = $before['seeder']['class'];
    if ($before['slug'] !== $slugs[$i] || !isset($banks[$identity]) || $banks[$identity]['level'] !== $levels[$i]
        || $before['page']['locale'] !== 'uk' || $before['type'] !== 'theory' || $before['page']['category']['slug'] !== 'conditionals') {
        throw new RuntimeException('M15 page/bank identity/level/category differs.');
    }
    $groups = m31Sections($before['page']['blocks'][1]['body']);
    $blocks = [$before['page']['blocks'][0]]; $plans = []; $audit = [];
    foreach ($groups as $s => $group) {
        $key = 'm31-'.['b2', 'c1', 'c2'][$i].'-section-'.($s + 1);
        $nodes = $group['nodes']; $points = []; $extra = []; $type = 'usage-panels';
        if (isset($group['practice'])) {
            $type = 'practice-set'; $data = m31Practice($i, m31AuthorChecks($group['source_node']), $banks[$identity]);
            $extra['legacy_practice_id'] = $group['practice'];
        } elseif ($s === 7) {
            $type = 'summary-list'; $data = ['title' => $group['title'], 'items' => array_map('m31Inner', $nodes)];
        } else {
            $tableIndex = null;
            foreach ($nodes as $n => $node) { if ($node instanceof DOMElement && $node->getElementsByTagName('table')->length > 0) { $tableIndex = $n; } }
            $retain = in_array([$i, $s], $retained, true);
            if ($tableIndex !== null) {
                $type = 'comparison-table'; $data = ['title' => $group['title']] + m31Table($nodes[$tableIndex], [4, 5, 3][$i]);
                $data['intro'] = m31Prose(array_slice($nodes, 0, $tableIndex));
                $data['outro'] = m31Prose(array_slice($nodes, $tableIndex + 1));
            } else {
                if ($retain) {
                    if (count($nodes) !== 3 || $nodes[0]->nodeName !== 'blockquote' || $nodes[1]->nodeName !== 'p' || $nodes[2]->nodeName !== 'p') {
                        throw new RuntimeException('M31 retained paragraph basic/detail ownership differs.');
                    }
                    $points = [['basic' => m31Prose(array_slice($nodes, 0, 2)), 'detail' => m31Inner($nodes[2])]];
                } else { $points = m31Points($nodes); }
                $data = ['title' => $group['title'], 'sections' => array_map(fn ($p) =>
                    ['description' => $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')], $points)];
            }
            $paragraphs = array_values(array_filter($nodes, fn ($n) => $n->nodeName === 'p'));
            $candidate = m31Inner($paragraphs[count($paragraphs) - 1]);
            $visible = m31Prose($nodes);
            $audit[] = ['source_section' => $s + 1, 'point' => 'source-final-paragraph',
                'basic_words' => m31Words($visible) - m31Words($candidate), 'detail_words' => m31Words($candidate),
                'detail_sentences' => m31Sentences($candidate), 'candidate_html' => $candidate,
                'decision' => $retain ? 'meaningful_detail' : 'visible_basic', 'reason' => $reasons[$i][$s]];
        }
        $data['m31_v1'] = ['key' => $key, 'legacy_section' => $s + 1] + $extra;
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
        throw new RuntimeException('M31 refuses to overwrite a manually changed definition.');
    }
    $package['targets'][] = ['path' => $target['path'], 'identity' => $identity, 'slug' => $before['slug'],
        'ancestry' => ['conditionals'], 'after' => $after, 'plans' => $plans, 'detail_quality_audit' => $audit];
}
foreach ($package['targets'] as $target) {
    if (file_put_contents($root.'/'.$target['path'], m31Json($target['after'])) === false) { throw new RuntimeException('M31 definition projection failed.'); }
}
if (file_put_contents($root.'/database/content-patches/m31-m15-conditionals.v1.json', m31Json($package)) === false) {
    throw new RuntimeException('M31 package projection failed.');
}
echo "Projected three M15 sources into eight native blocks/page; meaningful details 0/1/1, exact six cases each.\n";
