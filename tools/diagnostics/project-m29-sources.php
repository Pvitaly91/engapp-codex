<?php

// Mechanical projection of the three frozen M13 documents. Never boots Laravel or a DB.
// All educational strings below are extraction boundaries, not replacement text.
use DOMDocument as Dom;

$root = dirname(__DIR__, 2);
$names = ['CleftSentencesEmphasis', 'ComplexNounPhrases', 'EllipsisSubstitutionAndReference'];
$paths = array_map(fn($n) => 'database/seeders/Page_V3/SentenceStructure/'.$n.'TheorySeeder/definition.json', $names);
$manifestPath = $root.'/database/content-patches/m29-m13-sentence-structure-before.json';
if (($argv[1] ?? '') === '--capture-before') {
    if (file_exists($manifestPath)) { throw new RuntimeException('Before manifest already exists.'); }
    $manifest = ['base_sha' => '9edca5db593585d09411ec8107272dd24e8151ab', 'targets' => []];
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
    $p=['title'=>'Практика','select_title'=>'Вправа 1. Обери правильну відповідь','choice_title'=>'Вправа 2. Обери правильний варіант',
        'input_title'=>'Вправа 3. Побудуй речення','choice_options'=>['a','b'],
        'linked_practice'=>['source'=>'theory_links','question_types'=>['4'],
            'seeder_classes'=>['Database\\Seeders\\V3\\Polyglot\\Polyglot'.$name.['C1','C2','C2'][$i].'LessonSeeder'],
            'title'=>'Вправа 4. Побудуй речення','intro'=>'Склади англійське речення за українським.',
            'footer'=>'Завдання з наявного тесту цієї сторінки.']];
    if($i===0){
        $p['options']=['cost','lack of evidence','request','to request'];
        $p['selects']=[
            ['label'=>"It wasn't the ___ that led the committee to reject the proposal; it was the lack of evidence.",'answer'=>'cost'],
            ['label'=>'What the panel did was ___ an independent review.','answer'=>'request','accepted'=>['request','to request']]];
        $p['choices']=[
            ['label'=>'Питають: «Редактор прибрав додаток чи повторений абзац?»<br>A) It was the editor who removed the repeated paragraph.<br>B) It was the repeated paragraph that the editor removed.',
                'prompt'=>'Обери точніший фокус для цього контексту.','answer'=>'b',
                'feedback'=>['a'=>'A граматичне, але виділяє виконавця, а не вилучений матеріал. B точніше відповідає цьому контексту.',
                    'b'=>'Обидва речення граматичні; B виділяє саме вилучений матеріал.']],
            ['label'=>'A) It is the local volunteers who help visitors.<br>B) It are the local volunteers who helps visitors.','answer'=>'a']];
        $p['inputs']=[
            ['before'=>'the delivery date / When was it / that the supplier changed','answer'=>'When was it that the supplier changed the delivery date?'],
            ['before'=>'a clearer brief / was / What the team needed',
                'after'=>'We had enough equipment, but the team could not finish the survey.',
                'answer'=>'What the team needed was a clearer brief.']];
    }elseif($i===1){
        $p['selects']=[
            ['label'=>'those three carefully edited policy reports — ті три ретельно відредаговані звіти про політику. What is the head?',
                'options'=>['reports','policy','edited','three'],'answer'=>'reports'],
            ['label'=>'The description of the experimental procedures ___ incomplete.<br>The descriptions of the experimental procedures ___ incomplete.',
                'options'=>['is / are','are / is','is / is','are / are'],'answer'=>'is / are']];
        $p['choices']=[
            ['label'=>'the claim that the device saves energy; the device that the engineers tested',
                'prompt'=>'A) that the device saves energy = зміст claim; that the engineers tested = relative clause, яка ідентифікує device.<br>B) Перша частина = relative clause, яка ідентифікує claim; друга = зміст device.','answer'=>'a'],
            ['label'=>'We examined the drawing of the technician in the workshop.',
                'prompt'=>'Яка пара явно розрізняє два прочитання?<br>A) We examined the drawing showing the technician in the workshop. / While we were in the workshop, we examined the drawing of the technician.<br>B) We examined the drawing showing the technician in the workshop. / We examined the drawing of the technician who was in the workshop.','answer'=>'a']];
        $p['inputs']=[
            ['before'=>'approved the change / Leila, / the project coordinator,',
                'after'=>'Лейла — конкретна названа особа; the project coordinator — додаткова довідка.',
                'answer'=>'Leila, the project coordinator, approved the change.','punctuation_sensitive'=>true],
            ['before'=>'will start in May. / We need / The training / a new-staff training plan.',
                'answer'=>'We need a new-staff training plan. The training will start in May.',
                'accepted'=>['We need a new-staff training plan. The training will start in May.','We need a plan for training new staff. The training will start in May.']]];
    }else{
        $p['options']=['translate the abstract','any','any ones','blue ones'];
        $p['selects']=[
            ['label'=>"Lena can translate the abstract, but Max can't ___.",'answer'=>'translate the abstract'],
            ['label'=>'We need reliable information. Do you have ___?','answer'=>'any']];
        $p['choices']=[
            ['label'=>'Will the inspection cause another delay?',
                'prompt'=>"A) I hope not. I don't think so.<br>B) I don't hope so. I think so not.",'answer'=>'a'],
            ['label'=>'Обери правильну пару замін.',
                'prompt'=>'A) The curator promised to check the labels and did so before opening. / The first display is clear, and the second is too.<br>B) The curator promised to check the labels and was so before opening. / The first display is clear, and the second does so.','answer'=>'a']];
        $p['inputs']=[
            ['before'=>'useful for revision / This ease of reference / made the handbook',
                'after'=>'We compared an online workshop and a printed handbook. The former = workshop; the latter = handbook. This = зручність подальшого звернення до посібника.',
                'answer'=>'This ease of reference made the handbook useful for revision.'],
            ['before'=>"were missing / Olena told Marta / that Olena's notes",
                'after'=>'Olena told Marta that her notes were missing. Загублено нотатки Олени. Усунь неоднозначність.',
                'answer'=>"Olena told Marta that Olena's notes were missing.",
                'accepted'=>["Olena told Marta that Olena's notes were missing.",'Olena told Marta, “My notes are missing.”']]];
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
            $type='comparison-table';$tableIndex=[3,0,1][$i];
            $data=['title'=>$group['title']]+tableData($nodes[$tableIndex]);
            $intro=prose(array_slice($nodes,0,$tableIndex));
            if($i===1){$outro=$h[1];}
            if($i===2){
                // The terminology caveat belongs to the actual intro, not the complete table.
                [$a,$b]=parts($h[0],'Розрізняй');
                $intro=$a;$points=[point($b,$h[2])];
            }
        }elseif($i===0){
            if($s===1){
                [$a,$b]=parts($h[0],'Виправляємо');[$c,$d]=parts($b,'Інший діалог:');[$e,$f]=parts($d,'Тут фокусом');
                [$g,$k]=parts($h[1],'Подія одна');[$m,$n]=parts($k,'Нейтральне');
                $points=[point($a,$c),point($e,$f),point($g,$m),point($n)];
            }elseif($s===2){
                [$a,$b]=parts($h[0],'Натомість');
                [$c,$d]=parts($h[1],'Запитання перевіряє');[$e,$f]=parts($d,'<em>When was it');
                [$g,$k]=parts($f,'після <em>that</em>');
                $points=[point($a,$b),point($c,$e),point($g,$k.'<br><br>'.$h[2])];
            }elseif($s===3){
                $markers=['Після рамки','Це не твердження','Увагу спрямовано'];
                foreach($nodes[0]->getElementsByTagName('li') as $j=>$li){[$a,$b]=parts(inner($li),$markers[$j]);$points[]=point($a,$b);}
                $points[]=point($h[1]);
            }elseif($s===4){
                [$a,$b]=parts($h[0],'Для особи');
                [$c,$d]=parts($h[1],'Не відкидай');
                [$e,$f]=parts($h[2],'Теперішнє уточнення');
                $points=[point($a,$b),point($c,$d),point($e,$f)];
            }elseif($s===5){
                $intro=prose(array_slice($nodes,0,2));
                [$a,$b]=parts($h[2],'Заперечний');[$c,$d]=parts($b,'Останній');[$e,$f]=parts($d,'Якщо кожне');
                $points=[point($a),point($c),point($e),point($f)];
            }elseif($s===6){
                foreach($nodes[0]->getElementsByTagName('li') as $li){$points[]=point(inner($li));}
            }
        }elseif($i===1){
            if($s===2){
                foreach($nodes[0]->getElementsByTagName('li') as $li){[$a,$b]=parts(inner($li),'Крок 2:');$points[]=point($a,$b);}
            }elseif($s===3){
                [$a,$b]=parts($h[0],'<strong>Postmodifier</strong>');
                [$c,$d]=parts($h[1],'У <em>the bridge');
                [$e,$f]=parts($d,'Саме слово');
                [$g,$k]=parts($h[2],'Порівняй також');
                $points=[point($a,$c),point($b,$e),point($f,$g),point($k)];
            }elseif($s===4){
                [$a,$b]=parts($h[0],'<em>The labels');
                $points=[point($a,$h[1]),point($b)];
            }elseif($s===5){
                [$a,$b]=parts($h[0],'Дві іменникові');
                [$c,$d]=parts($b,'<em>The designer Rina');
                [$e,$f]=parts($d,'Пунктуація залежить');
                $points=[point($a,$c),point($e,$f),point($h[1])];
                foreach($nodes[2]->getElementsByTagName('li') as $j=>$li){
                    [$g,$k]=parts(inner($li),$j===0?'Місце належить':'Місце стосується');$points[]=point($g,$k);
                }
            }elseif($s===6){
                [$a,$b]=parts($h[0],'Тут зв’язок');
                // Both the chain and readable rewrite remain visible; the accepted warning is not rewritten.
                $points=[point($a,$b),point($h[1],$h[2])];
            }
        }else{
            if($s===1){
                foreach(array_slice($h,0,3) as $j=>$html){
                    [$a,$b]=parts($html,['Квадратні дужки','Повна форма','Це не загальний'][$j]);$points[]=point($a,$b);
                }
                [$a,$b]=parts($h[3],'— спирається');$points[]=point($a,$b);
            }elseif($s===2){
                $points=[point($h[0])];
                foreach($nodes[1]->getElementsByTagName('li') as $j=>$li){
                    $html=inner($li);
                    if($j===2){[$a,$b]=parts($html,'<em>I think not</em>');$points[]=point($a,$b);}
                    elseif($j===3){[$a,$b]=parts($html,'У цій відповіді');$points[]=point($a,$b);}
                    else{$points[]=point($html);}
                }
                [$a,$b]=parts($h[2],'Для незнайомого');$points[]=point($a,$b);
            }elseif($s===3){
                foreach($h as $j=>$html){
                    if($j===2){$points[]=point($html);continue;}
                    [$a,$b]=parts($html,['Форма має','<em>Did so</em>','', 'Можливе також'][$j]);$points[]=point($a,$b);
                }
            }elseif($s===4){
                [$a,$b]=parts($h[0],'<em>The blue folder is');
                [$c,$d]=parts($h[1],'Названа злічувана');[$e,$f]=parts($d,'Докладні випадки');
                $points=[point($a),point($b),point($c),point($e)];$outro=$f;
            }elseif($s===5){
                [$a,$b]=parts($h[0],'Можливе й');
                [$c,$d]=parts($h[1],'Це не будь-яка');
                [$e,$f]=parts($h[2],'У довшому');
                $points=[point($a,$b),point($c,$d),point($e,$f)];
            }elseif($s===6){
                $intro=prose(array_slice($nodes,0,2));$lis=iterator_to_array($nodes[2]->getElementsByTagName('li'));
                [$a,$b]=parts(inner($lis[1]),'<em>did so</em>');[$c,$d]=parts($b,'<em>The following week</em>');
                $points=[point(inner($lis[0])),point($a),point($c),point($d)];
                [$e,$f]=parts(inner($lis[2]),'Повтор');$points[]=point($e,$f);
                $points[]=point(inner($lis[3]));
            }
        }
        if($type!=='practice-set'){
            if($type==='usage-panels'){$data=['title'=>$group['title']];}
            if(!$points&&$type==='usage-panels'){$points=[point(prose($nodes))];}
            if($points){$data['sections']=array_map(fn($p)=>['description'=>$p['basic'].($p['detail']!==''?'<br><br>'.$p['detail']:'')],$points);}
            if($intro!==''){$data['intro']=$intro;}if($outro!==''){$data['outro']=$outro;}
        }
        $key='m29-'.['cleft','noun','ellipsis'][$i].'-section-'.($s+1);
        $data['m29_v1']=['key'=>$key,'legacy_section'=>$s+1]+$extra;
        $block=$s===0?$before['page']['blocks'][1]:['column'=>'left','heading'=>null,'level'=>null,'uuid_key'=>$key,'inherit_base_tags'=>false,'tags'=>[]];
        $block['type']=$type;$block['body']=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        if($type==='practice-set'){$block['level']=['C1','C2','C2'][$i];}
        $blocks[]=$block;$plans[]=['key'=>$key,'source_section'=>$s+1,'type'=>$type,'points'=>$points];
    }
    $after=$before;$after['page']['blocks']=$blocks;
    $package['targets'][]=['path'=>$target['path'],'identity'=>$before['seeder']['class'],'slug'=>$before['slug'],
        'ancestry'=>['sentence-structure'],'after'=>$after,'plans'=>$plans];
    file_put_contents($root.'/'.$target['path'],json_encode($after,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
}
file_put_contents($root.'/database/content-patches/m29-m13-sentence-structure.v1.json',json_encode($package,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
echo "Projected three finite native definitions and exact author point plans.\n";
