<?php

/**
 * Anonymous direct loopback HTTP/SSR acceptance, not a browser interaction test.
 * php tools/diagnostics/m74-live-pages.php live-pages-v1.json
 * No Laravel bootstrap, SQL, route edits, apply or content writes.
 */
set_exception_handler(static function(Throwable $error):void{
    fwrite(STDERR,$error instanceof RuntimeException?'M74 live HTTP: '.$error->getMessage()."\n":"M74 live HTTP incomplete; no successful receipt.\n");exit(1);
});
set_error_handler(static function(int $severity,string $message):never{throw new RuntimeException('M74 HTTP diagnostic warning: '.$message);});
$name=$argv[1]??'';
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==2||preg_match('/^live-pages-v[1-9][0-9]*\.json$/D',$name)!==1){
    throw new RuntimeException('Explicit new live-pages-vN.json private receipt basename required.');
}
$root='D:/DEV/htdocs/gramlyze.loc';$private=$root.'/storage/app/seo-m74-local';
$normal=static fn(string $path):string=>rtrim(strtolower(str_replace('\\','/',(string)realpath($path))),'/');
if($normal(dirname(__DIR__,2))!==$normal($root)||!is_dir($private)||is_link($private)
    ||$normal($private)!==$normal($root).'/storage/app/seo-m74-local'||file_exists($private.'/'.$name)||is_link($private.'/'.$name)){
    throw new RuntimeException('Run from actual served ROOT; the private receipt must not already exist.');
}
require $root.'/vendor/autoload.php';
use App\Services\M74ContentPatch as Patch;
use App\Support\M26DetailPackage;
use App\Support\M74OtherPronounsBasics as Package;
use App\Support\M74BuilderSelectionPresentation as Presentation;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;

if(Patch::BEFORE_FILE!=='before-v1.json'
    ||Patch::BEFORE_SHA!=='9e7abb860af2a61bfb10f61047f7ffaad055e20368edfc24c9ed095301516fdc'
    ||Package::BASE_SHA!=='fd1ad30e710b3d950e574848c886d86f6045f2e3'
    ||Package::PAGE_IDS!==[81,126,124]||Package::CATEGORY_IDS!==[16,20,20]
    ||Package::CONTINUING_COUNTS!==[6,12,10]||Package::PRACTICE_SOURCE_INDICES!==[6,10,8]
    ||Package::LEVELS!==['B1',null,null]||Package::RENDERED_TITLES!==['Another / Other / The Other','Pronouns','Personal / Object Pronouns']
    ||Package::profile()['marker']!=='m74_native_v1'){
    throw new RuntimeException('Exact frozen M74 physical/source/mixed old-new practice scope differs.');
}

$fileHash=static function(string $path):string{
    if(!is_file($path)||is_link($path)){throw new RuntimeException('Missing or linked reviewed input.');}
    $sha=hash_file('sha256',$path);if(!is_string($sha)){throw new RuntimeException('Reviewed input cannot be hashed.');}return $sha;
};
$beforePath=$private.'/'.Patch::BEFORE_FILE;
if(!hash_equals(Patch::BEFORE_SHA,$fileHash($beforePath))){throw new RuntimeException('Independent pre-apply BEFORE bytes differ.');}
$before=json_decode(file_get_contents($beforePath),true,flags:JSON_THROW_ON_ERROR);
[$sourceBefore,$patch,$master,$contract]=Package::load($root);Package::validate($sourceBefore,$patch,$master,$contract);
$auditPath=$root.'/'.Presentation::AUDIT;
if(!hash_equals(Presentation::AUDIT_SHA,$fileHash($auditPath))){throw new RuntimeException('Independent sentence-bank audit bytes differ.');}
$audit=json_decode(file_get_contents($auditPath),true,flags:JSON_THROW_ON_ERROR);
Presentation::validateAudit($audit,$patch,$contract);
if(count($audit['targets'])!==3||($audit['review_summary']['technical_filter']??null)!==['B1',null,null]
    ||($audit['review_summary']['intended_selected_levels']??null)!==['B1','A2','A1']){
    throw new RuntimeException('Frozen M74 bank technical/teaching level scope differs.');
}
foreach($audit['targets'] as $i=>$bank){
    $pool=$bank['expected_pool_ids_by_bank'][$bank['bank_seeders'][0]]??[];
    if(count($bank['bank_seeders'])!==1||count($pool)!==[12,72,72][$i]||count(array_unique($pool))!==[12,72,72][$i]
        ||count($bank['expected_selected_ids'])!==5||count($bank['first_five'])!==5
        ||($contract['targets'][$i]['level']??null)!==['B1',null,null][$i]){
        throw new RuntimeException('Exact M74 existing thematic bank pool or five-question selection differs.');
    }
    if(($bank['intended_selected_level']??null)!==['B1','A2','A1'][$i]
        ||($bank['level']??null)!==['B1',null,null][$i]){
        throw new RuntimeException('M74 bank display level and technical filter were conflated.');
    }
    $expectedBanks=['Database\\Seeders\\V3\\Polyglot\\PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPronounsTheoryAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPersonalObjectPronounsAllLevelsLessonSeeder'];
    $expectedIds=[[32707,32708,32711,32712,32714],[33123,33126,33128,33131,33133],[51184,51185,51187,51188,51189]];
    if($bank['bank_seeders']!==[$expectedBanks[$i]]||$bank['expected_selected_ids']!==$expectedIds[$i]
        ||($contract['targets'][$i]['bank_seeders']??null)!==[$expectedBanks[$i]]){
        throw new RuntimeException('M74 exact declared thematic bank or reviewed selection differs.');
    }
}
$inputs=[__FILE__=>$fileHash(__FILE__),$beforePath=>Patch::BEFORE_SHA,$auditPath=>Presentation::AUDIT_SHA];
foreach([Package::class,Presentation::class,Patch::class] as $class){
    $path=(new ReflectionClass($class))->getFileName();
    if(!is_string($path)){throw new RuntimeException('Finite M74 implementation source is unavailable.');}
    $inputs[$path]=$fileHash($path);
}
foreach(Package::profile()['artifacts'] as $path=>$sha){$inputs[$root.'/'.$path]=$sha;}
$same=static function(mixed $actual,mixed $expected,string $label):void{
    if($actual!==$expected){throw new RuntimeException($label.' differs.');}
};
$plain=static function(string $text):string{
    $text=preg_replace('/<(?:br\s*\/?|\/(?:p|div|li|section))>/i',' ',$text);
    $value=preg_replace('/\s+/u',' ',html_entity_decode(strip_tags($text),ENT_QUOTES|ENT_HTML5,'UTF-8'));
    return trim(preg_replace('/\s+([,.!?;:])/u','$1',$value));
};
$learningText=static function(DOMXPath $xp,DOMNode $node)use($plain):string{
    $parts=[];foreach($xp->query('.//text()[not(ancestor::script) and not(ancestor::style)]',$node) as $text){$parts[]=$text->nodeValue;}
    return $plain(implode(' ',$parts));
};
$fieldCount=0;$classifiedFields=0;
$contains=static function(string $text,mixed $expected,string $label)use($plain,&$fieldCount):void{
    if(!is_string($expected)||$plain($expected)===''){return;}
    if(!str_contains($text,$plain($expected))){throw new RuntimeException('Missing SSR learning field: '.$label);}$fieldCount++;
};
$keys=static function(array $value,array $allowed,string $label)use(&$classifiedFields):void{
    $unknown=array_values(array_diff(array_keys($value),$allowed));
    if($unknown!==[]){throw new RuntimeException('Unsupported frozen author fields: '.$label.' '.implode(',',$unknown));}
    $classifiedFields+=count($value);
};
$sectionFields=static function(array $sections,string $text,string $label)use($contains,$keys):void{
    foreach($sections as $n=>$section){
        $keys($section,['label','color','description','examples','note'],$label.'/'.$n);
        foreach(['label','description','note'] as $field){$contains($text,$section[$field]??null,$label.'/'.$n.'/'.$field);}
        foreach($section['examples']??[] as $j=>$example){
            $keys($example,['en','ua'],$label.'/'.$n.'/examples/'.$j);
            foreach(['en','ua'] as $field){$contains($text,$example[$field]??null,$label.'/'.$n.'/examples/'.$j.'/'.$field);}
        }
    }
};
$http=static function(string $url):array{
    $allowed=['http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/another-other-the-other',
        'http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/pronouns-theory',
        'http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/personal-object-pronouns'];
    if(!in_array($url,$allowed,true)||!in_array($url,array_column(Package::profile()['bindings'],'local_url'),true)){
        throw new RuntimeException('HTTP target is outside exact three plain-HTTP local lessons.');
    }
    $curl=curl_init($url);$robots=null;
    curl_setopt_array($curl,[CURLOPT_HTTPGET=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,
        CURLOPT_MAXREDIRS=>0,CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>45,CURLOPT_PROXY=>'',CURLOPT_NOPROXY=>'*',
        CURLOPT_NETRC=>CURL_NETRC_IGNORED,CURLOPT_COOKIE=>'',CURLOPT_PROTOCOLS=>CURLPROTO_HTTP,
        CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1'],CURLOPT_HTTPHEADER=>['Accept: text/html'],
        CURLOPT_HEADERFUNCTION=>static function($handle,string $header)use(&$robots):int{
            if(stripos($header,'X-Robots-Tag:')===0){$robots=trim(substr($header,13));}return strlen($header);
        }]);
    try{
        $html=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);
        if(!is_string($html)||$status!==200||curl_getinfo($curl,CURLINFO_PRIMARY_IP)!=='127.0.0.1'
            ||curl_getinfo($curl,CURLINFO_PRIMARY_PORT)!==80||curl_getinfo($curl,CURLINFO_EFFECTIVE_URL)!==$url
            ||curl_getinfo($curl,CURLINFO_REDIRECT_COUNT)!==0){throw new RuntimeException('Anonymous loopback GET did not return exact direct HTTP 200.');}
        return [$html,$status,$robots];
    }finally{unset($curl);}
};
// Decode only the server's literal JSON.parse string; no JS evaluation or browser runtime access.
$nativeConfig=static function(DOMElement $node):array{
    $value=$node->getAttribute('x-data');$prefix="theoryPracticeSet(JSON.parse('";$suffix="'))";
    if(!str_starts_with($value,$prefix)||!str_ends_with($value,$suffix)){throw new RuntimeException('Native practice literal config shape differs.');}
    $literal=substr($value,strlen($prefix),-strlen($suffix));
    if(str_contains($literal,'"')||str_contains($literal,"'")){throw new RuntimeException('Native config is not the reviewed escaped JSON literal.');}
    $json=json_decode('"'.$literal.'"',true,flags:JSON_THROW_ON_ERROR);
    $config=is_string($json)?json_decode($json,true,flags:JSON_THROW_ON_ERROR):null;
    if(!is_array($config)){throw new RuntimeException('Native practice config is not an object.');}return $config;
};

$started=gmdate('c');$results=[];$gradedOwn=0;$oldOwn=0;$newOwn=0;$demos=0;$tokenInputTotal=0;$linkedIds=[];$newPractices=0;
foreach($patch['targets'] as $i=>$target){
    $owner=$target['identity'];$lesson=$master['lessons'][$i];$url=$target['local_url'];
    $same($url,Package::profile()['bindings'][$i]['local_url'],'Frozen local URL');
    $oldHttp=array_values(array_filter($before['http']??[],static fn($r)=>($r['kind']??null)==='theory'&&($r['identity']??null)===$owner));
    $same(count($oldHttp),1,'Independent theory BEFORE record count');$oldHttp=$oldHttp[0];
    $same($oldHttp['url'],$url,'Independent theory BEFORE URL');
    $metadata=array_values(array_filter($master['editorial_review']['pre_apply_metadata']??[],static fn($r)=>($r['identity']??null)===$owner));
    $same(count($metadata),1,'Independent editorial metadata record count');$metadata=$metadata[0];
    $same($metadata['h1'],$oldHttp['h1'],'Editorial preserved H1');
    $same($metadata['h1'],Package::profile()['bindings'][$i]['rendered_title'],'Exact expected M74 H1');
    $same($metadata['expected_seo']['title'],$oldHttp['title'],'Editorial preserved title');
    $rawTitles=['Another / Other / The Other','Pronouns — Огляд','Personal / Object Pronouns'];
    $same($metadata['raw_page_title'],$rawTitles[$i],'Independent raw SQL title contract');
    $same($before['pages'][$owner]['page']['title'],$rawTitles[$i],'Captured raw Page title');
    $same($target['after']['page']['title'],$rawTitles[$i],'Preserved raw Page title in proposed source');
    [$html,$status,$robots]=$http($url);
    $dom=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);
    try{$loaded=$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($previous);}
    if(!$loaded){throw new RuntimeException('Local response is not parseable HTML.');}
    $xp=new DOMXPath($dom);
    $one=static function(string $query)use($xp):DOMElement{
        $nodes=$xp->query($query);
        if($nodes->length!==1||!$nodes->item(0) instanceof DOMElement){throw new RuntimeException('Expected one SSR node: '.$query);}return $nodes->item(0);
    };
    $main=$one('//*[@data-theory-main]');$mainText=$learningText($xp,$main);
    $same($xp->query('.//*[@data-theory-render-fallback]',$main)->length,0,'No guarded renderer fallback in actual content');
    $facts=['h1_count'=>$xp->query('//h1')->length,'h1'=>$plain($one('//h1')->textContent),
        'title'=>$plain($one('//title')->textContent),'breadcrumb'=>$plain($one('//nav[@aria-label][contains(@class,"mb-8")]')->textContent),
        'canonical'=>$one('//link[@rel="canonical"]')->getAttribute('href'),
        'description'=>$one('//meta[@name="description"]')->getAttribute('content'),'robots_header'=>$robots,
        'robots_meta'=>$xp->query('//meta[@name="robots"]')->item(0)?->getAttribute('content')];
    foreach(['h1_count','h1','title','breadcrumb','canonical','robots_header','robots_meta'] as $key){$same($facts[$key],$oldHttp[$key],'Preserved '.$key);}
    $same($facts['description'],$metadata['expected_seo']['description'],'Pre-apply reviewed SEO description');
    $resources=[];$descriptions=[];
    $visit=static function(mixed $value)use(&$visit,&$resources,&$descriptions):void{
        if(!is_array($value)){return;}
        if(in_array('LearningResource',(array)($value['@type']??[]),true)){
            $resources[]=array_intersect_key($value,array_flip(['@type','url','name','headline','inLanguage']));
            $descriptions[]=$value['description']??null;
        }
        foreach($value as $child){if(is_array($child)){$visit($child);}}
    };
    foreach($xp->query('//script[@type="application/ld+json"]') as $script){$visit(json_decode($script->textContent,true,flags:JSON_THROW_ON_ERROR));}
    $oldResources=array_map(static fn($v)=>array_intersect_key($v,array_flip(['@type','url','name','headline','inLanguage'])),$oldHttp['learning_resources']);
    $same(count($resources),1,'LearningResource count');$same($resources,$oldResources,'Stable LearningResource identity');
    $same($descriptions,[$metadata['expected_learning_resource_description']],'Pre-apply reviewed LearningResource description');
    $anchors=[];foreach($xp->query('//*[@id]') as $node){$anchors[]=$node->getAttribute('id');}
    $same(array_values(array_diff(array_unique($oldHttp['anchors']),array_unique($anchors))),[],'Preserved old anchors');
    $byOrder=[];foreach($before['pages'][$owner]['blocks'] as $row){if($row['locale']==='uk'){$byOrder[(int)$row['sort_order']]=$row;}}
    $sourceOld=$sourceBefore['targets'][$i]['before'];
    $same(count($sourceOld['page']['blocks']),[6,12,10][$i],'Fresh continuing source count');
    $same(count($lesson['blocks']),[7,12,10][$i],'Mixed continuing/appended author count');
    $same(count($target['after']['page']['blocks']),[7,12,10][$i],'Mixed continuing/appended definition count');
    $same(count(array_filter($sourceOld['page']['blocks'],static fn($b)=>$b['type']==='practice-set')),$i===0?0:1,'Actual old practice inventory');
    $same(count(array_filter($target['after']['page']['blocks'],static fn($b)=>$b['type']==='practice-set')),1,'Exactly one resulting native practice');
    $preservedMetadata=[];
    foreach($sourceOld['page']['blocks'] as $slot=>$config){
        $afterConfig=$target['after']['page']['blocks'][$slot];
        $same($lesson['blocks'][$slot]['existing_source_index'],$slot,'Continuing author index');
        $same(array_key_exists('level',$config),array_key_exists('level',$afterConfig),'Preserved raw level key presence');
        unset($config['body'],$afterConfig['body']);$same($afterConfig,$config,'Preserved continuing column/level/UUID source metadata');
        $captured=$byOrder[$slot+1]??null;
        if($captured===null){throw new RuntimeException('Missing independently captured continuing block.');}
        $same($captured['column'],$config['column'],'Captured continuing column');
        $same($captured['level'],$config['level']??null,'Captured continuing technical level');
        $same($captured['uuid'],M26DetailPackage::uuid($owner,$sourceOld['page']['blocks'][$slot],$slot+1),'Captured continuing UUID');
        $preservedMetadata[]=['source_index'=>$slot,'id'=>$captured['id'],'uuid'=>$captured['uuid'],
            'column'=>$captured['column'],'level'=>$captured['level'],'source_level_key_present'=>array_key_exists('level',$config)];
    }
    $footerSlot=[5,11,9][$i];
    $same($target['after']['page']['blocks'][$footerSlot]['type'],'navigation-chips','Old footer source position');
    $same($target['after']['page']['blocks'][$footerSlot]['column'],'footer','Old footer column');
    $same(array_key_exists('level',$sourceOld['page']['blocks'][$footerSlot]),false,'Old footer has no explicit source level');
    $same($sourceOld['page']['title'],$rawTitles[$i],'Preserved raw definition title');
    if($i===2){
        $post=array_values(array_filter($master['editorial_review']['post_apply_metadata']??[],static fn($r)=>($r['identity']??null)===$owner));
        $same(count($post),1,'Independent Personal plain-subtitle metadata record');
        $same(hash('sha256',$target['after']['page']['subtitle_html']),$post[0]['sha256'],'Personal plain subtitle reviewed bytes');
        $same($post[0]['extracted_title'],$facts['h1'],'Personal plain subtitle extracted title');
        $same($post[0]['h1'],$facts['h1'],'Personal plain subtitle preserved rendered H1');
    }
    $blockTexts=[];$blockResults=[];$navLinks=[];$lessonFieldStart=$fieldCount;$lessonClassifiedStart=$classifiedFields;
    foreach($lesson['blocks'] as $slot=>$author){
        $type=$author['type'];$data=$author['data'];$label=$author['key'];
        $sourceBody=json_decode($target['after']['page']['blocks'][$slot]['body'],true,flags:JSON_THROW_ON_ERROR);
        $same($sourceBody['m74_native_v1']??null,['key'=>$label,'detail_keys'=>[]],'Exact finite M74 native marker');
        if($type!=='hero'){$same(array_key_exists('m73_native_v1',$sourceBody),false,'No stale previous native package marker');}
        if($type==='hero'){
            $keys($data,['level','intro','rules'],$label);
            foreach(['level','intro'] as $field){$contains($mainText,$data[$field],$label.'/'.$field);}
            foreach($data['rules'] as $n=>$rule){
                $keys($rule,['label','color','text','example'],$label.'/rules/'.$n);
                foreach(['label','text','example'] as $field){$contains($mainText,$rule[$field],$label.'/rules/'.$n.'/'.$field);}
            }continue;
        }
        if($type==='navigation-chips'){
            $keys($data,['title','items'],$label);$contains($mainText,$data['title'],$label.'/title');
            foreach($data['items'] as $item){
                $keys($item,['label','current','url'],$label.'/item');$contains($mainText,$item['label'],$label.'/label');
                if(isset($item['url'])&&str_contains($item['url'],'#')){throw new RuntimeException('Navigation contains a placeholder or fragment destination.');}
                if($item['current']){continue;}
                if(preg_match('~^/theory/(?:imennyky-artykli-ta-kilkist|zaimennyky-ta-vkazivni-slova)/[a-z0-9-]+$~D',$item['url'])!==1){throw new RuntimeException('Navigation destination is not an exact local M74 category path.');}
                $found=false;foreach($xp->query('.//nav//a[@href]',$main) as $link){
                    if($link->getAttribute('href')===$item['url']&&$plain($link->textContent)===$plain($item['label'])){$found=true;}
                }
                if(!$found){throw new RuntimeException('Missing exact navigation destination: '.$item['url']);}$navLinks[]='http://gramlyze.loc'.$item['url'];
            }continue;
        }
        $old=$byOrder[$slot+1]??null;
        $uuid=M26DetailPackage::uuid($owner,$target['after']['page']['blocks'][$slot],$slot+1);
        if($old!==null){
            $same($author['existing_source_index'],$slot,'Continuing SSR source slot');
            $same($old['uuid'],$uuid,'Continuing SSR UUID');
            $node=$one('//*[@id="block-'.(int)$old['id'].'"]');
        }else{
            $same($i,0,'Only Another may append a block');
            $same($slot,[6,10,8][$i],'Exact appended source slot');$same($type,'practice-set','Only a new practice is appended');
            $same($author['existing_source_index'],null,'New practice cannot claim an old index');
            $same($author['column'],'left','New practice left column');$same($author['level'],['B1',null,null][$i],'New exact practice level');
            $factory='practiceQuestion_practice_'.str_replace('-','_',$uuid).'()';
            $one('//*[@data-sentence-builder and @x-data="'.$factory.'"]');
            $node=$one('//*[@data-sentence-builder and @x-data="'.$factory.'"]/ancestor::section[1]');
            if(preg_match('/^block-([1-9][0-9]*)$/D',$node->getAttribute('id'),$newId)!==1
                ||in_array((int)$newId[1],array_column($byOrder,'id'),true)){
                throw new RuntimeException('New practice SSR is not a distinct block identified through its exact UUID widget.');
            }
            ++$newPractices;
        }
        $text=$learningText($xp,$node);
        $blockTexts[$label]=['node'=>$node,'text'=>$text];
        foreach(['title','intro','warning'] as $field){$contains($text,$data[$field]??null,$label.'/'.$field);}
        if($type==='forms-grid'){
            $keys($data,['title','intro','items'],$label);
            foreach($data['items'] as $n=>$item){
                $keys($item,['label','title','subtitle'],$label.'/items/'.$n);
                foreach(['label','title','subtitle'] as $field){$contains($text,$item[$field],$label.'/items/'.$n.'/'.$field);}
            }
        }elseif($type==='usage-panels'){
            $keys($data,['title','intro','sections'],$label);$points=$xp->query('.//*[@data-theory-point-panel]',$node);
            $same($points->length,count($data['sections']),$label.' point-panel count');
            foreach($data['sections'] as $n=>$section){$same($points->item($n)->getAttribute('data-theory-accent'),$section['color'],$label.' semantic accent');}
            $sectionFields($data['sections'],$text,$label.'/sections');
        }elseif($type==='comparison-table'){
            $keys($data,['title','intro','headers','rows','warning'],$label);
            $same($xp->query('.//table',$node)->length,1,$label.' table count');
            $same($xp->query('.//tbody/tr',$node)->length,count($data['rows']),$label.' row count');
            $same($xp->query('.//thead//th',$node)->length,3,$label.' header count');
            foreach($data['headers']??[] as $n=>$header){$contains($text,$header,$label.'/headers/'.$n);}
            foreach($data['rows'] as $n=>$row){
                $keys($row,['en','ua','note'],$label.'/rows/'.$n);
                foreach(['en','ua','note'] as $field){$contains($text,$row[$field],$label.'/rows/'.$n.'/'.$field);}
            }
        }elseif($type==='mistakes-grid'){
            $keys($data,['title','items'],$label);
            foreach($data['items'] as $n=>$item){
                $keys($item,['label','color','title','wrong','right','wrong_en','wrong_ua','right_en','right_ua','note'],$label.'/items/'.$n);
                foreach(['wrong','right'] as $legacy){if(isset($item[$legacy])){$same($item[$legacy],$item[$legacy.'_en'],$label.' equivalent legacy correction field');}}
                foreach(['label','title','wrong_en','wrong_ua','right_en','right_ua','note'] as $field){$contains($text,$item[$field],$label.'/items/'.$n.'/'.$field);}
            }
        }elseif($type==='summary-list'){
            $keys($data,['title','items'],$label);
            foreach($data['items'] as $n=>$item){$contains($text,$item,$label.'/items/'.$n);}
        }elseif($type==='practice-set'){
            $keys($data,['title','select_title','select_intro','options','selects','choice_title','choice_intro','choice_options','choices',
                'input_title','input_intro','inputs','rephrase_title','rephrase_intro','rephrase','rephrase_items','linked_practice'],$label);
            if(array_key_exists('rephrase_items',$data)){$same($data['rephrase_items'],[],'Unused legacy author rephrase_items is explicitly empty');}
            $same($slot,Package::PRACTICE_SOURCE_INDICES[$i],'Mixed existing/appended practice source index');
            $same($target['after']['page']['blocks'][$slot]['column'],'left','Native practice column');
            $same($target['after']['page']['blocks'][$slot]['level']??null,['B1',null,null][$i],'Native exact technical filter');
            if($i>0){
                $same($old['uuid'],$i===1?'b00124f3-ec4d-54fb-91b8-b6e5becaf4b9':'1613f5c2-8294-57fd-bbd8-ea49c51a4df4','Preserved continuing Pronouns practice UUID');
                $same($old['level'],null,'Continuing Pronouns technical NULL stays distinct from selected teaching CEFR');
            }else{$same($old,null,'New Another practice did not exist in independent BEFORE');}
            $same($xp->query('//*[@data-sentence-builder]')->length,1,'Whole-page builder count');
            $same($xp->query('.//*[@data-sentence-builder]',$node)->length,1,'Builder owned by exact mixed existing/new practice');
            $groups=$xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]',$node);
            $same($groups->length,[3,4,4][$i],'Exact native practice group count');
            $factories=$xp->query('.//*[@x-data and starts-with(@x-data,"theoryPracticeSet(")]',$node);
            $same($factories->length,1,'Native practice factory count');$config=$nativeConfig($factories->item(0));
            $payloadFields=0;$tokenInputs=0;
            foreach(['selects','choices','inputs','rephrase'] as $n=>$group){
                $items=$data[$group]??[];
                $same($config[$group]??null,$items,'Exact native serialized '.$group.' controls/keys/aliases/feedback');
                if($items===[]){$same($group,'rephrase','Only optional rephrase may be absent');continue;}
                $groupNode=$groups->item($n);$groupText=$learningText($xp,$groupNode);
                if($group!=='rephrase'){
                    $counts=[['selects'=>2,'choices'=>2,'inputs'=>2],['selects'=>4,'choices'=>2,'inputs'=>5],['selects'=>5,'choices'=>2,'inputs'=>6]];
                    $same(count($items),$counts[$i][$group],'Frozen '.$group.' task count');
                }
                foreach($items as $t=>$task){
                    if($group==='inputs'){
                        $keys($task,['id','source_index','before','prompt','after','answer','accepted','feedback'],$label.'/'.$group.'/'.$t);
                        foreach(['prompt','after'] as $field){$contains($groupText,$task[$field]??null,$label.'/'.$group.'/'.$t.'/'.$field);}
                        if(!empty($task['before'])&&str_contains($task['before'],'/')){++$tokenInputs;}
                        else{$contains($groupText,$task['before']??null,$label.'/'.$group.'/'.$t.'/before');}
                        if(empty($task['prompt'])&&empty($task['after'])){throw new RuntimeException('Input lacks a visible Ukrainian condition.');}
                    }elseif($group==='rephrase'){
                        if($t===0&&!empty($task['example_original'])){
                            $keys($task,['example_label','example_original','example_target'],$label.'/rephrase/demo');
                            foreach(['example_label','example_original','example_target'] as $field){$contains($groupText,$task[$field]??null,$label.'/rephrase/demo/'.$field);}
                            ++$demos;continue;
                        }
                        $keys($task,['id','source_index','original','placeholder','answer','accepted','feedback'],$label.'/rephrase/'.$t);
                        $contains($groupText,$task['original'],$label.'/rephrase/'.$t.'/original');
                        $controls=$xp->query('.//*[@data-word-suggestion-input="rephrase-'.$t.'"]',$groupNode);
                        $same($controls->length,1,'Rephrase input '.$t.' count');
                        $same($controls->item(0)->getAttribute('placeholder'),$task['placeholder']??'','Rephrase input placeholder');
                    }else{
                        $keys($task,$group==='selects'?['id','source_index','label','prompt','options','answer','accepted','feedback']:['id','source_index','label','prompt','options','answer','accepted','feedback'],$label.'/'.$group.'/'.$t);
                        $contains($groupText,$task['label'],$label.'/'.$group.'/'.$t.'/label');
                        // The old Personal selects carry this redundant generic prompt.
                        // Native selects render the complete label, not a prompt field;
                        // retain the source field without claiming it is visible text.
                        if($group==='selects'&&array_key_exists('prompt',$task)){
                            $same($i,2,'Only continuing Personal selects retain a serialized prompt');
                            $same($task['prompt'],'Яка форма потрібна?','Retained redundant select prompt');
                        }
                        if($group==='choices'){$contains($groupText,$task['prompt']??null,$label.'/'.$group.'/'.$t.'/stimulus');}
                        foreach($task['options']??$data[$group==='selects'?'options':'choice_options']??[] as $o=>$option){
                            $contains($groupText,$option,$label.'/'.$group.'/'.$t.'/options/'.$o);
                        }
                    }
                    ++$gradedOwn;
                    if(array_key_exists('id',$task)){++$newOwn;}else{++$oldOwn;}
                    if(array_key_exists('id',$task)){
                        if($i===1){
                            $same(array_key_exists('source_index',$task),false,'Overview new stable IDs do not invent a source_index');
                            if(!($group==='choices'||($group==='inputs'&&in_array($t,[3,4],true)))){
                                throw new RuntimeException('Unexpected new Overview task position.');
                            }
                        }else{
                            $expectedIndex=$i===0?(['selects'=>1,'choices'=>3,'inputs'=>5][$group]+$t):
                                ($group==='choices'?13+$t:($group==='inputs'&&$t>=4?15+$t-4:null));
                            $same($task['source_index']??null,$expectedIndex,'Stable new thematic task source index');
                            if($expectedIndex===null){throw new RuntimeException('Unexpected new indexed task position.');}
                        }
                        if(!is_string($task['id'])||trim($task['id'])===''){throw new RuntimeException('Missing stable new task ID.');}
                    }
                    $same(in_array($task['answer'],$task['accepted'],true),true,'Nonempty accepted canonical answer');
                    if(trim($task['answer'])===''){throw new RuntimeException('Empty response entered a frozen graded control.');}
                    foreach($task['feedback']??[] as $feedback){
                        if(!is_string($feedback)||preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu',$feedback)){
                            throw new RuntimeException('Native x-text feedback is not plain text.');
                        }
                    }
                    $payloadFields+=count($task);
                }
                if(in_array($group,['selects','choices'],true)){
                    $same($xp->query('.//label',$groupNode)->length,count($items),'SSR '.$group.' task count');
                }
            }
            $same($tokenInputs,2,'Exactly two full-sentence token inputs per M74 lesson');$tokenInputTotal+=$tokenInputs;
            $sentenceIndices=[[0,1],[3,4],[4,5]][$i];
            foreach($sentenceIndices as $t){
                $task=$data['inputs'][$t];
                if(!str_contains($task['before'],'/')||preg_match('/[.!?]$/D',$task['answer'])!==1
                    ||preg_match('/[\x{0400}-\x{04ff}]/u',$task['answer'])===1||count(explode(' ',trim($task['answer'])))<4){
                    throw new RuntimeException('New M74 input is not a deliberate full English sentence with a native token bank.');
                }
                foreach($task['accepted'] as $accepted){
                    if(trim($accepted)===''||in_array(mb_strtolower(trim($accepted)),['the','ø','без артикля','no article'],true)){
                        throw new RuntimeException('Full-sentence input admits an empty or standalone article label.');
                    }
                }
            }
            if($i>0){
                foreach(['selects'=>$i===1?4:5,'inputs'=>$i===1?3:4,'rephrase'=>$i===1?3:4] as $group=>$count){for($t=0;$t<$count;$t++){
                    $same(array_key_exists('id',$data[$group][$t]),false,'Retained Pronouns task/demo ID absence');
                    $same(array_key_exists('source_index',$data[$group][$t]),false,'Retained Pronouns source-index absence');
                }}
                $same(isset($data['rephrase'][0]['answer']),false,'Retained demonstration is not scored');
                if($i===1){
                    $same($data['rephrase'][1]['answer'],'речення Б','Meaning-preserving apostrophe task UK transfer');
                    $same($data['rephrase'][1]['accepted'],['речення Б','Б'],'Reviewed finite apostrophe task accepted labels');
                }
            }
            $same($xp->query('.//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]',$node)->length,[2,5,6][$i],'Exact short/full manual field count');
            $same($xp->query('.//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"rephrase-")]',$node)->length,[0,2,3][$i],'Exact retained graded rephrase field count');
            foreach(['select_title','select_intro','choice_title','choice_intro','input_title','input_intro','rephrase_title','rephrase_intro'] as $field){$contains($text,$data[$field]??null,$label.'/'.$field);}
            $keys($data['linked_practice'],['source','question_types','seeder_classes','title','intro','footer'],$label.'/linked_practice');
            foreach(['title','intro','footer'] as $field){$contains($text,$data['linked_practice'][$field],$label.'/linked_practice/'.$field);}
            $source=$target['after']['page']['blocks'][$slot];$uuid=M26DetailPackage::uuid($owner,$source,$slot+1);
            $payload=Acceptance::payload($html,$uuid);$same(is_array($payload)?count($payload):null,5,'Actual linked public payload count');
            $bank=$audit['targets'][$i];$same($bank['identity'],$owner,'Independent bank owner');
            $same(array_column($payload,'id'),$bank['expected_selected_ids'],'Exact selected IDs/order');
            foreach($bank['first_five'] as $q=>$expected){
                $same((string)$payload[$q]['type'],'4','Actual widget type');$same($payload[$q]['level'],$expected['level']??['B1','A2','A1'][$i],'Actual selected question teaching CEFR, not technical NULL filter');
                $same($plain($payload[$q]['question']),$plain($expected['visible_condition_uk']),'Actual selected visible condition');
                $same($payload[$q]['correct_tokens'],$expected['correct_tokens'],'Native case-sensitive ordered correct tokens');
                $same($payload[$q]['verb_hints'],[],'No unexpected verb hints');$same($payload[$q]['hints'],[],'No unexpected bank hints');
                $actualOptions=array_count_values($payload[$q]['options']);$expectedOptions=array_count_values($expected['token_bank_with_multiplicity']);
                ksort($actualOptions);ksort($expectedOptions);$same($actualOptions,$expectedOptions,'Native exact option-token multiset');
                foreach(array_count_values($payload[$q]['correct_tokens']) as $token=>$count){
                    if(($actualOptions[$token]??0)<$count){throw new RuntimeException('Actual widget loses repeated correct-token instances.');}
                    if(trim($token)===''||in_array(mb_strtolower($token),['ø','без артикля','no article'],true)){
                        throw new RuntimeException('Widget canonical sequence has an empty or zero-label token.');
                    }
                }
            }
            array_push($linkedIds,...array_column($payload,'id'));
            $blockResults['practice']=['source_index'=>$slot,'uuid'=>$uuid,'SSR_block_id'=>$node->getAttribute('id'),
                'source_state'=>$i===0?'appended-after-all-old-configs':'continuing',
                'column'=>'left','technical_level'=>['B1',null,null][$i],'own_groups'=>[3,4,4][$i],
                'graded_own_tasks'=>[6,13,16][$i],'old_graded_tasks'=>[0,9,12][$i],'new_graded_tasks'=>[6,4,4][$i],
                'ungraded_demos'=>[0,1,1][$i],
                'empty_and_reset_scoring'=>'separate browser evidence; not executed by SSR audit',
                'tasks_per_group'=>['selects'=>[2,4,5][$i],'choices'=>2,'inputs'=>[2,5,6][$i],
                    'graded_rephrase'=>[0,2,3][$i],'ungraded_demo'=>[0,1,1][$i]],
                'full_sentence_input_indices'=>$sentenceIndices,'token_backed_full_sentence_inputs'=>$tokenInputs,
                'native_factory_payload_fields_exact'=>$payloadFields,
                'linked_widgets'=>1,'linked_payload_count'=>5,'linked_ids'=>array_column($payload,'id'),
                'selected_question_sequence_native_case_options_and_multiplicity'=>true];
        }else{throw new RuntimeException('Unexpected frozen author block type.');}
    }
    $same($xp->query('.//details[@data-theory-details]',$main)->length,count($lesson['details']),'Actual point-detail count');
    $same(count($lesson['details']),0,'All M74 author detail decisions keep visible basic');
    $same($xp->query('.//details[@data-theory-details and @open]',$main)->length,0,'Initially open detail count');
    foreach($lesson['details'] as $detail){
        $ownerBlock=$blockTexts[$detail['owner_block_key']]??null;if($ownerBlock===null){throw new RuntimeException('Detail owner absent from SSR.');}
        $details=$xp->query('.//details[@data-theory-details]',$ownerBlock['node']);$same($details->length,1,'Owned detail count');
        $sectionFields($detail['fragment']['data']['sections'],$learningText($xp,$details->item(0)),'detail/'.$detail['owner_block_key']);
    }
    $results[]=['identity'=>$owner,'url'=>$url,'status'=>$status,'html_sha256'=>hash('sha256',$html),'metadata'=>$facts,
        'raw_page_title_source_bound'=>$rawTitles[$i],'stable_learning_resource_identity'=>$resources[0],'preserved_anchor_count'=>count(array_unique($oldHttp['anchors'])),'missing_old_anchors'=>[],
        'basic_and_detail_SSR_learning_fields'=>$fieldCount-$lessonFieldStart,'author_fields_classified'=>$classifiedFields-$lessonClassifiedStart,
        'unsupported_author_fields'=>0,'point_details'=>count($lesson['details']),
        'point_details_initially_closed'=>count($lesson['details'])>0?true:'not_applicable_no_details',
        'continuing_source_metadata_preserved'=>$preservedMetadata,'practice'=>$blockResults['practice'],'navigation_exact_local_destinations'=>$navLinks];
}
$same([$gradedOwn,$oldOwn,$newOwn,$demos,$tokenInputTotal,$newPractices],[35,21,14,2,6,1],'Exact mixed old/new own-task inventory');
$same(count($linkedIds),15,'Three five-question widgets');
$same(count(array_unique($linkedIds)),15,'Fifteen distinct thematic linked questions');
foreach($inputs as $path=>$sha){if(!hash_equals($sha,$fileHash($path))){throw new RuntimeException('Reviewed input changed during HTTP acceptance.');}}
$receipt=['schema_version'=>1,'status'=>'read-only-local-http-ssr-accepted','started_at'=>$started,'finished_at'=>gmdate('c'),
    'base_sha'=>Package::BASE_SHA,'independent_before_sha256'=>Patch::BEFORE_SHA,'master_sha256'=>Package::MASTER_SHA,
    'patch_sha256'=>Package::SOURCE_SHA,'bank_audit_sha256'=>Presentation::AUDIT_SHA,
    'diagnostic_sha256'=>$inputs[__FILE__],'native_source_marker'=>'m74_native_v1',
    'transport'=>['anonymous'=>true,'method'=>'GET','fixed_loopback'=>'127.0.0.1:80','redirects'=>0,'proxy'=>false,'cookies_sent'=>false],
    'pages'=>$results,'own_graded_tasks'=>$gradedOwn,'old_graded_tasks'=>$oldOwn,'new_graded_tasks'=>$newOwn,'ungraded_demos'=>$demos,
    'continuing_source_configs'=>28,'new_practice_blocks'=>1,'full_sentence_token_inputs'=>$tokenInputTotal,
    'linked_questions'=>count($linkedIds),
    'SSR_learning_fields_checked'=>$fieldCount,'author_fields_classified'=>$classifiedFields,'unsupported_author_fields'=>0,
    'field_boundary'=>'Learning text checked in SSR; native answers/aliases/feedback and client-only token banks checked against exact serialized factory data. Client visibility/interaction remains separate. Subtitle storage/derived metadata has independent SQL and pre-apply metadata checks; the canonical template does not display subtitle prose twice.',
    'not_checked'=>['browser layout or visibility','client Check/Reset/token interaction','client-only feedback visibility after Check',
        'navigation availability beyond the exact three target URLs','SQL postconditions (separate diagnostic)'],
    'production_requests'=>0,'SQL_access'=>false,'content_writes'=>0,'runtime_or_route_edits'=>0];
$json=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n";
$handle=fopen($private.'/'.$name,'x');if($handle===false){throw new RuntimeException('Exclusive receipt creation failed.');}
try{if(fwrite($handle,$json)!==strlen($json)||!fflush($handle)){throw new RuntimeException('Private receipt write incomplete.');}}
finally{fclose($handle);}
echo json_encode(['status'=>$receipt['status'],'file'=>$name,'sha256'=>$fileHash($private.'/'.$name),'HTTP_200'=>count($results),
    'SSR_learning_fields_checked'=>$fieldCount,'author_fields_classified'=>$classifiedFields,'unsupported_author_fields'=>0,
    'browser_interactions_checked'=>false,'SQL_access'=>false],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
