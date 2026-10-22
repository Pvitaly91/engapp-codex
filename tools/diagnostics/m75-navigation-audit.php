<?php

/**
 * Closed M75 navigation/metadata successor of the immutable M74 read-only gate.
 * Run only after main review and source sync, from the actual served ROOT:
 * php tools/diagnostics/m75-navigation-audit.php navigation-after-v1.json
 *   final-readonly-v1.json <reviewed-final-raw-sha256>
 * No apply, route/configuration edits, browser claims or production requests.
 */
$file = __DIR__.'/m74-navigation-audit.php';
if (!is_file($file) || is_link($file)) { throw new RuntimeException('Immutable M74 navigation helper missing or linked.'); }
$source = file_get_contents($file);
if (!is_string($source) || !str_starts_with($source, "<?php\n")
    || !hash_equals('d2b8705e2ff53f8cd8549261a27cc963685fdfd72781b0d1101431923ad9de4f', hash('sha256', $source))) {
    throw new RuntimeException('Immutable M74 navigation helper bytes differ.');
}

$evidence = <<<'PHP'
$inputs=[(__DIR__.'/m74-navigation-audit.php')=>$hash((__DIR__.'/m74-navigation-audit.php')),
    __DIR__.'/m75-navigation-audit.php'=>$hash(__DIR__.'/m75-navigation-audit.php'),
    $dir.'/before-v1.json'=>$beforeSha,$dir.'/'.$finalName=>$finalSha];
if (($final['visual_files_unchanged']??null)!==326 || ($final['frozen_files_unchanged']??null)!==2624
    || ($final['source_sync_unchanged']??null)!==true || ($final['all_preview_sources_exact']??null)!==true
    || count($before['visual_hashes']??[])!==326 || count($before['frozen_hashes']??[])!==2624) {
    throw new RuntimeException('M75 final evidence does not prove exact visual/frozen/source scope.');
}
foreach ([$before['visual_hashes'],$before['frozen_hashes']] as $hashes) {
    foreach ($hashes as $path=>$sha) {
        if (!is_string($path) || preg_match('~^(?:[A-Za-z]:|/|\\\\)|(?:^|[/\\\\])\.\.(?:[/\\\\]|$)~D',$path)) {
            throw new RuntimeException('M75 visual/frozen path is outside repository-relative scope.');
        }
        if (!hash_equals($sha,$hash($root.'/'.$path))) { throw new RuntimeException('M75 visual/frozen source changed: '.$path); }
        $inputs[$root.'/'.$path]=$sha;
    }
}
foreach (App\Services\M75ContentPatch::questionEvidencePins() as $path=>$sha) {
    if (!str_starts_with($path,'private:') || !hash_equals($sha,$hash($dir.'/'.substr($path,8)))) {
        throw new RuntimeException('M75 independent question-plan/inventory pin differs.');
    }
    $inputs[$dir.'/'.substr($path,8)]=$sha;
}
if (($final['question_plan_sha256']??null)!==App\Services\M75ContentPatch::QUESTION_PLAN_SHA
    || ($final['question_inventory_sha256']??null)!==App\Services\M75ContentPatch::QUESTION_INVENTORY_SHA
    || ($final['question_source_sha256']??null)!==App\Support\M75QuestionSupplement::SOURCE_SHA) {
    throw new RuntimeException('M75 final question evidence lineage differs.');
}
$inputs[$root.'/'.App\Support\M75QuestionSupplement::SOURCE]=App\Support\M75QuestionSupplement::SOURCE_SHA;
foreach ([
    // Served ROOT uses the accepted helper's exact LF transport bytes (WT CRLF has the same LF digest).
    'app/Support/PageMetadata.php'=>'1d782d27483bdc0cff63515a1d69153dc908adbf2c3b3eea47bec35f141e0a9e',
    'app/Http/Middleware/AddStructuredData.php'=>'ea3774ba90512bfa2bf7cee9277b87a3951e4a43fbd48eeed21ffb83ec2a58ac',
] as $path=>$sha) {
    if (!hash_equals($sha,$hash($root.'/'.$path))) { throw new RuntimeException('Unchanged native metadata policy bytes differ.'); }
    $inputs[$root.'/'.$path]=$sha;
}
foreach ([App\Support\PageMetadata::class=>'app/Support/PageMetadata.php',
    App\Http\Middleware\AddStructuredData::class=>'app/Http/Middleware/AddStructuredData.php'] as $class=>$path) {
    if ($normal((new ReflectionClass($class))->getFileName())!==$normal($root.'/'.$path)) {
        throw new RuntimeException('Actual native metadata helper does not come from served ROOT.');
    }
}
PHP;

$navigation = <<<'PHP'
        $same(count($data['items']),9,'Per-owner footer item count');
        $oldItems=json_decode($sourceBefore['targets'][$i]['before']['page']['blocks'][Package::CONTINUING_COUNTS[$i]-1]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];
        $labelState=static fn(array $items):array=>array_map(static fn(array $item):array=>array_intersect_key($item,array_flip(['label','current'])),$items);
        $same($labelState($data['items']),$labelState($oldItems),'All nine original labels/current flags');
PHP;

$metadata = <<<'PHP'
        $target=$targetByPath[$path];$i=$target['index'];$same($row['h1'],Package::RENDERED_TITLES[$i],'M75 exact rendered H1');
        $owner=$target['owner'];
        $oldHttp=array_values(array_filter($before['http']??[],static fn(array $r):bool=>($r['kind']??null)==='theory'&&($r['identity']??null)===$owner));
        $same(count($oldHttp),1,'One independent original theory HTTP record');$oldHttp=$oldHttp[0];
        $same($oldHttp['url'],'http://gramlyze.loc'.$path,'Independent original theory URL');
        $same($oldHttp['h1'],Package::RENDERED_TITLES[$i],'Independent preserved M75 H1');
        $one=static function(string $query)use($xp):DOMElement{
            $nodes=$xp->query($query);
            if($nodes->length!==1||!$nodes->item(0) instanceof DOMElement){throw new RuntimeException('Expected one native metadata node: '.$query);}
            return $nodes->item(0);
        };
        $facts=['h1_count'=>$h1->length,'h1'=>$row['h1'],'title'=>$plain($one('//title')->textContent),
            'breadcrumb'=>$plain($one('//nav[@aria-label][contains(@class,"mb-8")]')->textContent),
            'canonical'=>$canonical->item(0)->getAttribute('href'),'robots_header'=>$robots,
            'robots_meta'=>$xp->query('//meta[@name="robots"]')->item(0)?->getAttribute('content')];
        foreach(['h1_count','h1','title','breadcrumb','canonical','robots_header','robots_meta'] as $key){
            $same($facts[$key],$oldHttp[$key],'Preserved native '.$key);
        }
        $definition=$patch['targets'][$i]['after'];
        $same($definition['page']['title'],$before['pages'][$owner]['page']['title'],'Preserved raw Page title');
        $heroes=array_values(array_filter($definition['page']['blocks'],static fn(array $b):bool=>in_array($b['type'],['hero-v2','hero'],true)));
        $same(count($heroes),1,'One frozen native hero for metadata');
        $hero=json_decode($heroes[0]['body'],true,flags:JSON_THROW_ON_ERROR);
        if(!is_string($hero['intro']??null)||trim($hero['intro'])===''){throw new RuntimeException('Frozen hero intro is missing.');}
        $expectedSeo=App\Support\PageMetadata::theory($definition['page']['title'],'',$hero['intro'],$owner,'uk');
        $same($facts['title'],$expectedSeo['title'],'Frozen source-bound native SEO title');
        $description=$one('//meta[@name="description"]')->getAttribute('content');
        $same($description,$expectedSeo['description'],'Frozen source-bound native SEO description');
        $resources=[];$resourceDescriptions=[];
        $visit=static function(mixed $value)use(&$visit,&$resources,&$resourceDescriptions):void{
            if(!is_array($value)){return;}
            if(in_array('LearningResource',(array)($value['@type']??[]),true)){
                $resources[]=array_intersect_key($value,array_flip(['@type','url','name','headline','inLanguage']));
                $resourceDescriptions[]=$value['description']??null;
            }
            foreach($value as $child){if(is_array($child)){$visit($child);}}
        };
        foreach($xp->query('//script[@type="application/ld+json"]') as $script){$visit(json_decode($script->textContent,true,flags:JSON_THROW_ON_ERROR));}
        $oldResources=array_map(static fn(array $v):array=>array_intersect_key($v,array_flip(['@type','url','name','headline','inLanguage'])),$oldHttp['learning_resources']);
        $same(count($resources),1,'One native LearningResource');$same($resources,$oldResources,'Stable native LearningResource identity');
        // Derive only from reviewed source bytes and the pinned unchanged native field priority.
        $expectedResourceDescription=null;
        foreach($definition['page']['blocks'] as $block){
            $body=json_decode($block['body'],true);
            if(!is_array($body)){continue;}
            foreach(['intro','description','summary','subtitle','text'] as $key){
                $value=$body[$key]??null;
                if(is_string($value)&&trim(strip_tags($value))!==''){
                    $expectedResourceDescription=Illuminate\Support\Str::limit($plain($value),300,'…');break 2;
                }
            }
        }
        if(!is_string($expectedResourceDescription)||$expectedResourceDescription===''){throw new RuntimeException('No frozen structured description source.');}
        $same($resourceDescriptions,[$expectedResourceDescription],'Frozen source-bound native LearningResource description');
        $anchors=[];foreach($xp->query('//*[@id]') as $node){$anchors[]=$node->getAttribute('id');}
        $same(array_values(array_diff(array_unique($oldHttp['anchors']),array_unique($anchors))),[],'Every original page anchor preserved');
        $same($xp->query('.//*[@data-theory-render-fallback]',$main->item(0))->length,0,'No renderer fallback');
        $row['metadata']=$facts+['description'=>$description,'learning_resource_identity'=>$resources[0],
            'learning_resource_description'=>$expectedResourceDescription,'preserved_original_anchors'=>count(array_unique($oldHttp['anchors']))];
PHP;

$replacements = [
    ['M74','M75',9],['m74','m75',3],
    ['9e7abb860af2a61bfb10f61047f7ffaad055e20368edfc24c9ed095301516fdc','5e238ff98020caf4ce97a3057c7a624bd48898d64791e83a720e3fe792b09c4a',1],
    ['fd1ad30e710b3d950e574848c886d86f6045f2e3','67ebba5ed98fae05ca6022cac802870cfacf0d52',1],
    ['M75OtherPronounsBasics','M75PronounForms',1],
    ['[81,126,124]','[125,128,122]',1],['[16,20,20]','[20,20,20]',1],
    ['__FILE__',"(__DIR__.'/m74-navigation-audit.php')",3],
    [<<<'OLD'
$inputs=[(__DIR__.'/m74-navigation-audit.php')=>$hash((__DIR__.'/m74-navigation-audit.php')),$dir.'/before-v1.json'=>$beforeSha,$dir.'/'.$finalName=>$finalSha];
OLD,$evidence,1],
    [<<<'OLD'
        $same(count($data['items']),[8,9,9][$i],'Per-owner footer item count');
        if($i===0){$same($data['items'],json_decode($sourceBefore['targets'][0]['before']['page']['blocks'][5]['body'],true,flags:JSON_THROW_ON_ERROR)['items'],'All eight original Another URLs/labels/current flags');}
OLD,$navigation,1],
    ["(imennyky-artykli-ta-kilkist|zaimennyky-ta-vkazivni-slova)","(zaimennyky-ta-vkazivni-slova)",1],
    ["($"."m[1]==='imennyky-artykli-ta-kilkist'?16:20)",'20',1],
    ["$"."same($"."chips,26,'All frozen footer chips');$"."same($"."realAnchors,23,'All noncurrent footer anchors');",
        "$"."same($"."chips,27,'All frozen footer chips');$"."same($"."realAnchors,24,'All noncurrent footer anchors');",1],
    ["$"."same(count($"."routes),17,'Deduplicated eight noun and nine pronoun destinations');",
        "$"."same(count($"."routes),9,'Deduplicated nine pronoun destinations');",1],
    [<<<'OLD'
        $target=$targetByPath[$path];$i=$target['index'];$same($row['h1'],Package::RENDERED_TITLES[$i],'M75 exact rendered H1');
OLD,$metadata,1],
    ["$"."same($"."renderedChips,26,'Rendered footer chips');$"."same($"."renderedAnchors,23,'Rendered real anchors');",
        "$"."same($"."renderedChips,27,'Rendered footer chips');$"."same($"."renderedAnchors,24,'Rendered real anchors');",1],
    ["count($"."linkGets),23,'Every clickable anchor", "count($"."linkGets),24,'Every clickable anchor",1],
    ["'footer_chips'=>26,'clickable_anchors'=>23,'distinct_destinations'=>17,'pronoun_explicit_url_items'=>18",
        "'footer_chips'=>27,'clickable_anchors'=>24,'distinct_destinations'=>9,'pronoun_explicit_url_items'=>27",1],
    ["'distinct_destinations'=>17,","'distinct_destinations'=>9,",1],
    [<<<'OLD'
    'independent_before_sha256'=>$beforeSha,'reviewed_final_file'=>$finalName,'reviewed_final_sha256'=>$finalSha,'runtime'=>$runtime,
OLD,<<<'NEW'
    'independent_before_sha256'=>$beforeSha,'reviewed_final_file'=>$finalName,'reviewed_final_sha256'=>$finalSha,'runtime'=>$runtime,
    'm75_wrapper_sha256'=>$inputs[__DIR__.'/m75-navigation-audit.php'],
    'package_sha256'=>Package::SOURCE_SHA,'master_sha256'=>Package::MASTER_SHA,'contract_sha256'=>Package::CONTRACT_SHA,
    'visual_files_unchanged'=>326,'frozen_files_unchanged'=>2624,'metadata_pages_checked'=>3,
    'metadata_description_policy'=>'Reviewed frozen source fields through byte-pinned unchanged native metadata policy; identity fields separately equal independent BEFORE.',
NEW,1],
];
foreach ($replacements as [$old,$new,$count]) {
    if (substr_count($source,$old)!==$count) { throw new RuntimeException('M75 finite navigation literal profile count differs.'); }
    $source=str_replace($old,$new,$source);
}
// Execute only fixed repository bytes and closed literals; never interpolate runtime code.
eval(substr($source,strlen("<?php\n")));
