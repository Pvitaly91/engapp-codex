<?php

// Mechanical projection of the three frozen M11 documents. Never boots Laravel or a DB.
// All educational strings below are extraction boundaries, not replacement text.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$names = ['LinkingWordsReasonResultContrast', 'AdvancedLinkingDevices', 'ConcessiveAndContrastiveStructures'];
$paths = array_map(fn ($n) => 'database/seeders/Page_V3/ClausesAndLinkingWords/'.$n.'TheorySeeder/definition.json', $names);
$manifestPath = $root.'/database/content-patches/m27-m11-linking-words-before.json';
if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('Before manifest already exists.'); }
    $manifest = ['base_sha' => '47491c43f820d2efd1756e6022e6c7114f85c1c5', 'targets' => []];
    foreach ($paths as $path) {
        $bytes = file_get_contents($root.'/'.$path); $d = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        $manifest['targets'][] = ['path' => $path, 'source_sha256' => hash('sha256', $bytes), 'before' => $d];
    }
    file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
    echo "Captured three versioned source definitions (not DB rows).\n"; exit;
}
if (($argv[1] ?? '') !== '--project') { throw new RuntimeException('Use --capture-before once or --project.'); }
$manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
function cleanHtml(string $html): string {
    return preg_replace('/\sstyle="[^"]*"/', '', $html);
}
function inner(DOMNode $node): string {
    $out = ''; foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); }
    return cleanHtml(trim($out));
}
function parts(string $html, string $marker): array {
    if (substr_count($html, $marker) !== 1) { throw new RuntimeException('Non-unique split: '.$marker); }
    [$a,$b] = explode($marker, $html, 2);
    // Every boundary is outside inline markup: neither half may require parser repair.
    foreach ([$a, $marker.$b] as $half) {
        foreach (['em','strong','a'] as $tag) {
            if (preg_match_all('/<'.$tag.'\b/', $half) !== substr_count($half, '</'.$tag.'>')) {
                throw new RuntimeException('Split cuts inline markup.');
            }
        }
    }
    return [trim($a), trim($marker.$b)];
}
function point(string $basic, string $detail = ''): array {
    return ['basic' => $basic, 'detail' => $detail];
}
function parseSections(string $html): array {
    $dom = new Dom('1.0','UTF-8'); libxml_use_internal_errors(true);
    $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
    $groups = []; $i = -1;
    foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
        if (!$node instanceof DOMElement) { continue; }
        if ($node->tagName === 'h4') { $groups[++$i] = ['title' => trim($node->textContent), 'nodes' => []]; }
        elseif ($node->tagName === 'section') { $groups[++$i] = ['title' => 'Самоперевірка', 'practice' => $node->getAttribute('id'), 'nodes' => []]; }
        else { $groups[$i]['nodes'][] = $node; }
    }
    return $groups;
}
function prose(array $nodes): string {
    return implode('<br><br>', array_map(fn ($n) => in_array($n->nodeName,['p','li'],true) ? inner($n) : cleanHtml($n->ownerDocument->saveHTML($n)), $nodes));
}
function linked(string $name, int $index): array {
    return ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => ['Database\\Seeders\\V3\\Polyglot\\Polyglot'.$name.['B2','C1','C2'][$index].'LessonSeeder'],
        'title' => 'Вправа 4. Побудуй речення', 'intro' => 'Склади англійське речення за українським.',
        'footer' => 'Завдання з наявного тесту цієї сторінки.'];
}
function practice(int $i, string $name): array {
    $p = ['title' => 'Практика', 'select_title' => 'Вправа 1. Заповни пропуск',
        'choice_title' => 'Вправа 2. Обери правильний варіант', 'input_title' => 'Вправа 3. Побудуй речення',
        'choice_options' => ['a','b'], 'linked_practice' => linked($name, $i)];
    if ($i === 0) {
        $p['options'] = ['because','because of','due to'];
        $p['selects'] = [
            ['label' => 'We moved the workshop online ___ the building was closed. Після пропуску є підмет і присудок.', 'answer' => 'because'],
            ['label' => 'The workshop was moved online ___ a power cut. Після пропуску стоїть іменникова група.', 'answer' => 'because of', 'accepted' => ['because of','due to']]];
        $p['choices'] = [
            ['label' => 'a) The final bus had left, therefore we walked home. / b) The final bus had left; therefore, we walked home.', 'prompt' => 'Потрібно замінити so на therefore.', 'answer' => 'b'],
            ['label' => 'a) Although the hall was small, everyone found a seat. / b) Despite the hall was small, everyone found a seat.', 'answer' => 'a']];
        $p['inputs'] = [
            ['before' => 'we walked home / therefore / The final bus had left', 'answer' => 'The final bus had left; therefore, we walked home.'],
            ['before' => 'answered every question / Ben / In spite of / feeling tired', 'answer' => 'In spite of feeling tired, Ben answered every question.']];
    } elseif ($i === 1) {
        $p['options'] = ['consequently','moreover','provided that','insofar as'];
        $p['selects'] = [['label' => 'Half the files were missing; ___, the audit was postponed.', 'answer' => 'consequently', 'options' => ['consequently','moreover']],
            ['label' => 'You may use the recordings ___ every participant gives written consent.', 'answer' => 'provided that', 'options' => ['provided that','insofar as']]];
        $p['choices'] = [
            ['label' => 'The service costs less. ___, it includes evening support. a) Moreover / Furthermore are suitable additions. / b) Therefore is the only correct option.', 'answer' => 'a'],
            ['label' => 'The model is useful ___ it explains recurring errors. a) provided that / b) insofar as', 'answer' => 'b']];
        $p['input_title'] = 'Вправа 3. Відредагуй речення';
        $p['inputs'] = [
            ['before' => 'Moreover, furthermore, the library offers free legal advice.', 'answer' => 'Moreover, the library offers free legal advice.', 'accepted' => ['Moreover, the library offers free legal advice.','Furthermore, the library offers free legal advice.']],
            ['before' => 'The dataset is incomplete, therefore the estimate is provisional.', 'answer' => 'The dataset is incomplete; therefore, the estimate is provisional.', 'accepted' => ['The dataset is incomplete; therefore, the estimate is provisional.','The dataset is incomplete. Therefore, the estimate is provisional.'], 'punctuation_sensitive' => true]];
    } else {
        $p['options'] = ['even though','even if'];
        $p['selects'] = [
            ['label' => 'Записи підтверджують: учора зв’язку не було. ___ the network was down, the team delivered the report on time.', 'answer' => 'even though'],
            ['label' => 'Ми ще не знаємо, чи буде завтра зв’язок. We will meet as planned ___ the network is down tomorrow.', 'answer' => 'even if']];
        $p['choices'] = [
            ['label' => 'I took notes ___ the speaker was explaining the diagram. a) while / b) whereas', 'answer' => 'a'],
            ['label' => 'a) Although exhausted, the presentation continued. / b) Although Maya was exhausted, the presentation continued.', 'prompt' => 'Мая була виснажена, презентація тривала.', 'answer' => 'b']];
        $p['input_title'] = 'Вправа 3. Перепиши речення';
        $p['inputs'] = [
            ['before' => 'Although the instructions were clear, two participants missed a step.', 'after' => 'Перепиши з despite the fact that.', 'answer' => 'Despite the fact that the instructions were clear, two participants missed a step.'],
            ['before' => 'The committee approved the plan although the budget was tight.', 'after' => 'Почни з допустової частини.', 'answer' => 'Although the budget was tight, the committee approved the plan.']];
    }
    return $p;
}
$package = ['version' => 1, 'targets' => []];
foreach ($manifest['targets'] as $i => $target) {
    $before = $target['before']; $groups = parseSections($before['page']['blocks'][1]['body']);
    $blocks = [$before['page']['blocks'][0]]; $plans = [];
    foreach ($groups as $s => $group) {
        $nodes = $group['nodes']; $points = []; $intro = ''; $type = 'usage-panels'; $extra = [];
        if (isset($group['practice'])) {
            $type = 'practice-set'; $data = practice($i, $names[$i]);
            $extra['legacy_practice_id'] = $group['practice'];
        } elseif (($i === 0 && $s === 5) || ($i > 0 && $s === 6)) {
            $type = 'summary-list'; $data = ['title' => $group['title'], 'items' => array_map('inner', iterator_to_array($nodes[0]->getElementsByTagName('li')))];
        } elseif ($i === 0 && $s === 1) {
            $type = 'comparison-table'; $rows = [];
            foreach ($nodes[1]->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr) {
                $cells=[]; foreach ($tr->childNodes as $cell) { if ($cell instanceof DOMElement) { $cells[]=inner($cell); } }
                $rows[]=['cells'=>$cells];
            }
            $headers = array_map(fn ($n) => trim($n->textContent), iterator_to_array($nodes[1]->getElementsByTagName('thead')->item(0)->getElementsByTagName('th')));
            $data=['title'=>$group['title'],'intro'=>inner($nodes[0]),'headers'=>$headers,'rows'=>$rows];
        } else {
            if ($i === 0) {
                if ($s === 0) { $points=[point(prose($nodes))]; }
                if ($s === 2) {
                    [$a,$b]=parts(inner($nodes[0]), 'У багатьох таких контекстах');
                    $points=[point($a,$b.'<br><br>'.inner($nodes[1]))];
                }
                if ($s === 3) {
                    [$a,$b]=parts(inner($nodes[1]), 'У нейтральному письмовому тексті');
                    $points=[point(inner($nodes[0]).'<br><br>'.$a,$b.'<br><br>'.inner($nodes[2]))];
                }
                if ($s === 4) {
                    [$a,$b]=parts(inner($nodes[0]), '<strong>Even though</strong>');
                    [$c,$d]=parts(inner($nodes[3]), 'Можливе й');
                    $points=[point($a.'<br><br>'.inner($nodes[1]).'<br><br>'.$c,$b.'<br><br>'.inner($nodes[2]).'<br><br>'.$d)];
                }
            } elseif ($i === 1) {
                if ($s === 1) {
                    $lis=iterator_to_array($nodes[0]->getElementsByTagName('li'));
                    $markers=['Або:', '<em>Every registered visitor', 'Обидві зв’язки формальні;'];
                    foreach ($lis as $j=>$li) { [$a,$b]=parts(inner($li),$markers[$j]); $points[]=point($a,$b); }
                    // The clause/noun construction belongs to the concession point, never its neighbour.
                    $points[0]['detail'].='<br><br>'.inner($nodes[1]);
                } elseif ($s === 5) { $points=[point(prose(array_slice($nodes,0,2)),prose(array_slice($nodes,2)))]; }
                else { $points=[point(inner($nodes[0]),prose(array_slice($nodes,1)))]; }
            } else {
                if ($s === 0) { [$a,$b]=parts(inner($nodes[1]),'Конструкції можуть перетинатися'); $points=[point(inner($nodes[0]).'<br><br>'.$a,$b)]; }
                if ($s === 1) { $points=[point(prose(array_slice($nodes,0,2)),inner($nodes[2]))]; }
                if ($s === 2) { $points=[point(prose($nodes))]; }
                if ($s === 3) {
                    $markers=['<em>Though</em> часто', '<em>In spite of reading', 'Зміст той самий', 'Тут <em>though</em>'];
                    foreach ($nodes as $j=>$n) { [$a,$b]=parts(inner($n),$markers[$j]); $points[]=point($a,$b); }
                }
                if ($s === 4) {
                    $intro=inner($nodes[0]); $lis=iterator_to_array($nodes[1]->getElementsByTagName('li'));
                    $markers=['Обидві ознаки', 'Попереджали саме Нору.', 'Така конструкція формальніша'];
                    foreach ($lis as $j=>$li) { [$a,$b]=parts(inner($li),$markers[$j]); $points[]=point($a,$b); }
                    [$a,$b]=parts(inner($nodes[2]), 'Скорочення <em>Although exhausted');
                    $points[]=point($a,$b.'<br><br>'.inner($nodes[3]));
                }
                if ($s === 5) { [$a,$b]=parts(inner($nodes[1]),'Після головної частини'); $points=[point(inner($nodes[0]).'<br><br>'.$a,$b.'<br><br>'.inner($nodes[2]))]; }
            }
            // The final internal-links section is always fully visible and has no empty disclosure.
            if (!$points) { $points=[point(prose($nodes))]; }
            $data=['title'=>$group['title'],'sections'=>array_map(fn ($p)=>['description'=>$p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')],$points)];
            if ($intro !== '') { $data['intro']=$intro; }
        }
        $key='m27-'.strtolower(['b2','c1','c2'][$i]).'-section-'.($s+1);
        $data['m27_v1']=['key'=>$key,'legacy_section'=>$s+1]+$extra;
        $block=$s===0 ? $before['page']['blocks'][1] : ['column'=>'left','heading'=>null,'level'=>null,'uuid_key'=>$key,'inherit_base_tags'=>false,'tags'=>[]];
        $block['type']=$type; $block['body']=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        if ($type === 'practice-set') { $block['level']=['B2','C1','C2'][$i]; }
        $blocks[]=$block;
        $plans[]=['key'=>$key,'source_section'=>$s+1,'type'=>$type,'points'=>$points];
    }
    $after=$before; $after['page']['blocks']=$blocks;
    $package['targets'][]=['path'=>$target['path'],'identity'=>$before['seeder']['class'],'slug'=>$before['slug'],'after'=>$after,'plans'=>$plans];
    file_put_contents($root.'/'.$target['path'],json_encode($after,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
}
file_put_contents($root.'/database/content-patches/m27-m11-linking-words.v1.json',json_encode($package,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
echo "Projected three finite native definitions and their exact point plans.\n";
