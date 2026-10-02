<?php

// Mechanical projection of the three frozen M12 documents. Never boots Laravel or a DB.
// All educational strings below are extraction boundaries, not replacement text.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$names = ['CleftSentencesBasics', 'InversionBasics', 'AdvancedFrontingAndEmphasis'];
$paths = ['database/seeders/Page_V3/SentenceStructure/CleftSentencesBasicsTheorySeeder/definition.json', 'database/seeders/Page_V3/BasicGrammar/WordOrder/InversionBasicsTheorySeeder/definition.json', 'database/seeders/Page_V3/BasicGrammar/WordOrder/AdvancedFrontingAndEmphasisTheorySeeder/definition.json'];
$manifestPath = $root.'/database/content-patches/m28-m12-emphasis-inversion-before.json';
if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('Before manifest already exists.'); }
    $manifest = ['base_sha' => '32f3ffd2059df357f7ccccadacbb2c2984a8fdb3', 'targets' => []];
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

function practice(int $i,string $name): array {
    $p=['title'=>'Практика','select_title'=>'Вправа 1. Заповни пропуск','choice_title'=>'Вправа 2. Обери правильний варіант',
        'input_title'=>'Вправа 3. Побудуй речення','choice_options'=>['a','b'],
        'linked_practice'=>['source'=>'theory_links','question_types'=>['4'],
            // These exact primary banks were corroborated by read-only working inventory.
            'seeder_classes'=>['Database\\Seeders\\V3\\Polyglot\\Polyglot'.$name.['B2','B2','C2'][$i].'LessonSeeder'],
            'title'=>'Вправа 4. Побудуй речення','intro'=>'Склади англійське речення за українським.',
            'footer'=>'Завдання з наявного тесту цієї сторінки.']];
    if($i===0){
        $p['options']=['Nora','we need','do we need','she'];
        $p['selects']=[
            ['label'=>'It was ___ who found the spare key yesterday. Саме Nora, не Leo.','answer'=>'Nora'],
            ['label'=>'What ___ is a quieter room.','answer'=>'we need']];
        $p['choices']=[
            ['label'=>'a) It was the technician who reset the alarm. / b) It was the technician who she reset the alarm.','answer'=>'a'],
            ['label'=>'a) It was difficult to find the entrance. / b) It was behind the library that we found the entrance.','prompt'=>'Яке речення є cleft у цій парі?','answer'=>'b']];
        $p['inputs']=[
            ['before'=>'yesterday / the spare key / It was Nora / who found','answer'=>'It was Nora who found the spare key yesterday.',
                'accepted'=>['It was Nora who found the spare key yesterday.','It was Nora that found the spare key yesterday.']],
            ['before'=>'the agreement / It was on Friday / that we signed','answer'=>'It was on Friday that we signed the agreement.']];
    }elseif($i===1){
        $p['options']=['speak','speaks','repair','repaired'];
        $p['selects']=[
            ['label'=>'Rarely does the guide ___ so quickly.','answer'=>'speak'],
            ['label'=>'Not only did the team ___ the roof, but it also painted the walls.','answer'=>'repair']];
        $p['choices']=[
            ['label'=>'a) Only after did the bell ring the guard opened the door. / b) Only after the bell rang did the guard open the door.','answer'=>'b'],
            ['label'=>'a) Only the caretaker knew the code.<br>b) Not only the caretaker but also the manager knew the code.<br>c) Only then the manager checked the lock.<br>d) The manager rarely checks that lock.',
                'prompt'=>'Which sentence needs inversion?','options'=>['a','b','c','d'],'answer'=>'c']];
        $p['inputs']=[
            ['before'=>'live / this orchestra / Never / have I heard','answer'=>'Never have I heard this orchestra live.'],
            ['before'=>'the sign / could we read / Not until / the lights came on','answer'=>'Not until the lights came on could we read the sign.']];
    }else{
        $p['select_title']='Вправа 1. Обери правильний порядок';
        $p['selects']=[
            ['label'=>'Here ___. Choose correct personal-pronoun order.','options'=>['they come','come they','the meeting starts','starts the meeting'],'answer'=>'they come'],
            ['label'=>'Neutral schedule.','options'=>['At nine, the meeting starts.','At nine starts the meeting.'],'answer'=>'At nine, the meeting starts.']];
        $p['choices']=[
            ['label'=>'A) That estimate we cannot accept.<br>B) Only later did we discover the error.<br>C) Beside the entrance stood a young violinist.',
                'prompt'=>'Обери повну карту класифікації. a) A = fronting without inversion; B = subject–auxiliary inversion; C = full-verb inversion. b) A = subject–auxiliary inversion; B = full-verb inversion; C = fronting without inversion.','answer'=>'a'],
            ['label'=>'The new model we had to replace after a week.',
                'prompt'=>'a) fronting without inversion; neutral: We had to replace the new model after a week. b) subject–auxiliary inversion; neutral: Had we to replace the new model after a week.','answer'=>'a']];
        $p['inputs']=[
            ['before'=>'I can accept / That condition','answer'=>'That condition I can accept.'],
            ['before'=>'a tall mirror / Behind the curtain / stood','answer'=>'Behind the curtain stood a tall mirror.']];
    }
    return $p;
}
function tableData(DOMElement $wrapper): array {
    $headers=array_map(fn($n)=>trim($n->textContent),iterator_to_array($wrapper->getElementsByTagName('th')));
    $rows=[];
    foreach($wrapper->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr){
        $cells=[];foreach($tr->childNodes as $cell){if($cell instanceof DOMElement){$cells[]=inner($cell);}}
        $rows[]=['cells'=>$cells];
    }
    return ['headers'=>$headers,'rows'=>$rows];
}
$package=['version'=>1,'targets'=>[]];
foreach($manifest['targets'] as $i=>$target){
    $before=$target['before'];$groups=parseSections($before['page']['blocks'][1]['body']);
    $blocks=[$before['page']['blocks'][0]];$plans=[];
    foreach($groups as $s=>$group){
        $nodes=$group['nodes'];$h=array_map('inner',$nodes);$points=[];$intro='';$outro='';$type='usage-panels';$extra=[];$data=[];
        if(isset($group['practice'])){
            $type='practice-set';$data=practice($i,$names[$i]);$extra['legacy_practice_id']=$group['practice'];
        }elseif(($i===0&&$s===0)||($i===1&&$s===1)||($i===2&&$s===0)){
            $type='comparison-table';
            $tableIndex=$i===2?1:2;
            $data=['title'=>$group['title']]+tableData($nodes[$tableIndex]);
            if($i===0){$intro=prose(array_slice($nodes,0,2));}
            if($i===2){$intro=$h[0];$outro=$h[2];}
            if($i===1){
                [$a,$b]=parts($h[0],'З формою <em>be</em>');
                [$c,$d]=parts($h[1],'Після них');
                [$e,$f]=parts($d,'<strong>did notice</strong>');
                $intro=$c;
                $points=[point($a),point($b),point($e),point($f)];
            }
        }elseif($i===0){
            if($s===1){
                [$a,$b]=parts($h[0],'Для фокусу');
                [$c,$d]=parts($b,'Для предмета');
                [$e,$f]=parts($h[1],'Місце виділеної');
                [$g,$k]=parts($h[2],'Для ясності');
                $points=[point($a),point($c),point($d),point($e,$f),point($g,$k)];
            }elseif($s===2){
                [$a,$b]=parts($h[0],'У кімнаті');
                [$c,$d]=parts($b,'<em>What we need</em> означає');
                [$e,$f]=parts($h[1],'Порівняй пряме');
                [$g,$k]=parts($h[2],'Це не запитання');
                $points=[point($a),point($e,$f),point($c,$d),point($g,$k)];
            }elseif($s===3){
                [$a,$b]=parts($h[1],'Вибір <em>is/was</em>');
                [$c,$d]=parts($b,'Тому механічна');
                $points=[point($h[0]),point($a),point($c,$d),point($h[2])];
            }
        }elseif($i===1){
            if($s===0){[$a,$b]=parts($h[1],'Позначка B2');$points=[point($h[0].'<br><br>'.$a,$b)];}
            if($s===2){[$a,$b]=parts($h[1],'Вибір часу');$points=[point($h[0].'<br><br>'.$a,$b)];}
            if($s===3){
                [$a,$b]=parts($h[1],'Якщо після');
                $points=[point($h[0],$b),point($a),point($h[2]),point($h[3])];
            }
            if($s===4){
                [$a,$b]=parts($h[0],'<em>Also</em>');
                [$c,$d]=parts($h[1],'Збережи паралельність');
                $points=[point($a,$b),point($c),point($d)];
            }
        }else{
            if($s===1){
                [$a,$b]=parts($h[0],'Обидва об’єкти');
                [$c,$d]=parts($h[1],'Такий контрастний');
                [$e,$f]=parts($h[2],'Розмовне');
                [$g,$k]=parts($f,', але це');
                $points=[point($a,$b),point($c,$d),point($e),point($g,$k)];
            }
            if($s===2){
                [$a,$b]=parts($h[1],'Базові правила');
                $points=[point($h[0]),point($a,$b)];
            }
            if($s===3){
                [$a,$b]=parts($h[0],'Частина з місцем');
                [$c,$d]=parts($h[1],'<em>Beside the gate');
                [$e,$f]=parts($d,'Не додаємо');
                [$g,$k]=parts($h[2],'Навіть там');
                $intro=$c;$points=[point($a,$b),point($e),point($f),point($g),point($k)];
            }
            if($s===4){
                [$a,$b]=parts($h[0],'Порівняй іменниковий');
                [$c,$d]=parts($b,'Так само');
                [$e,$f]=parts($h[1],'У художньому');
                $points=[point($a,$d),point($c),point($e,$f)];
            }
            if($s===5){
                $intro=prose(array_slice($nodes,0,2));
                foreach($nodes[2]->getElementsByTagName('li') as $j=>$li){
                    if($j<3){[$a,$b]=parts(inner($li),': ');$points[]=point($a,$b);}
                    else{$points[]=point(inner($li));}
                }
            }
        }
        if($type!=='practice-set'){
            if($type==='usage-panels'){$data=['title'=>$group['title']];}
            if(!$points&&$type==='usage-panels'){$points=[point(prose($nodes))];}
            if($points){$data['sections']=array_map(fn($p)=>['description'=>$p['basic'].($p['detail']!==''?'<br><br>'.$p['detail']:'')],$points);}
            if($intro!==''){$data['intro']=$intro;}if($outro!==''){$data['outro']=$outro;}
        }
        $key='m28-'.['cleft','inversion','fronting'][$i].'-section-'.($s+1);
        $data['m28_v1']=['key'=>$key,'legacy_section'=>$s+1]+$extra;
        $block=$s===0?$before['page']['blocks'][1]:['column'=>'left','heading'=>null,'level'=>null,'uuid_key'=>$key,'inherit_base_tags'=>false,'tags'=>[]];
        $block['type']=$type;$block['body']=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        if($type==='practice-set'){$block['level']=['B2','B2','C2'][$i];}
        $blocks[]=$block;$plans[]=['key'=>$key,'source_section'=>$s+1,'type'=>$type,'points'=>$points];
    }
    $after=$before;$after['page']['blocks']=$blocks;
    $package['targets'][]=['path'=>$target['path'],'identity'=>$before['seeder']['class'],'slug'=>$before['slug'],
        'ancestry'=>$i===0?['sentence-structure']:['basic-grammar','word-order'],'after'=>$after,'plans'=>$plans];
    file_put_contents($root.'/'.$target['path'],json_encode($after,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
}
file_put_contents($root.'/database/content-patches/m28-m12-emphasis-inversion.v1.json',json_encode($package,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
echo "Projected three finite native definitions and exact author point plans.\n";
