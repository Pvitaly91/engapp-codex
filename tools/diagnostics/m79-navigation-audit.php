<?php

/**
 * Explicit M79 post-apply read-only gate. Review before running in served ROOT:
 * php tools/diagnostics/m79-navigation-audit.php nav-metadata-v1.json final-readonly-v1.json <raw-final-sha256>
 * Fixed loopback anonymous GET only; SELECT-only transactions; exclusive ignored receipt.
 * No writer, route, source edit, browser or byte-identical-page claim.
 */
$failure=static function(Throwable $error):void{
    fwrite(STDERR,$error instanceof RuntimeException&&!$error instanceof PDOException
        ? 'M79 navigation: '.$error->getMessage()."\n" : "M79 navigation incomplete; no successful receipt. No SQL writes.\n");exit(1);
};
set_exception_handler($failure);
if(PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||count($argv)!==4
    ||preg_match('/^nav-metadata-v[1-9][0-9]*\.json$/D',$argv[1]??'')!==1
    ||preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[2]??'')!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$argv[3]??'')!==1){throw new RuntimeException('Fresh private output plus reviewed final basename/raw SHA required.');}
[$script,$outputName,$finalName,$finalSha]=$argv;
$root='D:/DEV/htdocs/gramlyze.loc';$dir=$root.'/storage/app/seo-m79-local';
$normal=static function(string $path):string{$real=realpath($path);if($real===false){throw new RuntimeException('Required local path absent.');}return rtrim(strtolower(str_replace('\\','/',$real)),'/');};
if($normal(dirname(__DIR__,2))!==$normal($root)||!is_dir($dir)||is_link($dir)
    ||$normal($dir)!==$normal($root).'/storage/app/seo-m79-local'||file_exists($dir.'/'.$outputName)||is_link($dir.'/'.$outputName)){
    throw new RuntimeException('Actual served ROOT/private directory differs or output exists.');
}
$hash=static function(string $file):string{if(!is_file($file)||is_link($file)){throw new RuntimeException('Pinned source/evidence absent or linked.');}$sha=hash_file('sha256',$file);if(!is_string($sha)){throw new RuntimeException('Cannot hash pinned source.');}return $sha;};
$pins=[__FILE__=>$hash(__FILE__)];
$same=static function(mixed $actual,mixed $expected,string $reason):void{if($actual!==$expected){throw new RuntimeException($reason.' differs.');}};
$read=static function(string $name,string $sha)use($dir,$hash,&$pins):array{
    if(preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$name)!==1||!hash_equals($sha,$hash($dir.'/'.$name))){throw new RuntimeException('Independent private evidence basename/bytes differ.');}
    $pins[$dir.'/'.$name]=$sha;return json_decode(file_get_contents($dir.'/'.$name),true,flags:JSON_THROW_ON_ERROR);
};
$beforeSha='8c261b17b3f7e332fc5fc86eef8b555af83de19938aee7ae83d2a23e7dbb3add';
$consumerSha='ada89c6d6961dd7446313e401caec85a7dc40e9c6e673dc9042ee63ba8f80c63';
$prospectiveSha='18f0f141aa84e6ed7317207c39af9de57b48038be191a9308c0345d67c9877a9';
$before=$read('before-v1.json',$beforeSha);$consumers=$read('before-old-consumers-v1.json',$consumerSha);
$final=$read($finalName,$finalSha);$prospective=$read('prospective-metadata-v1.json',$prospectiveSha);
$same([$finalName,$finalSha],['final-readonly-v2.json','3a05df3eab0b664fb076386868908b8b91d94d1c30410b0419f0b441a2fe9b14'],'Explicit final two-layer proof');
require __DIR__.'/m79-final-readonly-lineage.php';
$lineage=m79FinalReadOnlyLineage($root,$dir);$same($final,$lineage['final'],'Complete independently recomputed two-layer final proof');
$pins+= $lineage['pins'];$pins[__DIR__.'/m79-final-readonly-lineage.php']=$hash(__DIR__.'/m79-final-readonly-lineage.php');
$initialPath=static fn(string $path):string=>str_replace([
    'database/content-patches/m79-question-types-completion.v1.0.1.json','docs/content/m79-question-types-completion.v1.0.1.json',
    'docs/content/m79-source-checksums.v1.0.1.json','docs/content/m79-sentence-builder-bank-audit.v1.0.1.json'],[
    'database/content-patches/m79-question-types-completion.v1.json','docs/content/m79-question-types-completion.v1.json',
    'docs/content/m79-source-checksums.v1.json','docs/content/m79-sentence-builder-bank-audit.v1.json'],$path);
$pinInitial=static function(string $path,string $sha,string $file)use($lineage,$same,$hash,&$pins):void {
    if(isset($lineage['source_successors'][$path])){
        $same($sha,$lineage['source_successors'][$path]['before'],'Exact initial source predecessor');
        $sha=$lineage['source_successors'][$path]['after'];
    }
    $same($hash($file),$sha,'Exact unchanged initial source or finite successor');$pins[$file]=$sha;
};
if(($before['served_root']??null)!==$root||($before['sql_before']??null)!==($before['sql_after_http']??null)
    ||count($before['sql_before']??[])!==46||count($before['visual_hashes']??[])!==326||count($before['frozen_hashes']??[])!==2701
    ||($consumers['all_tables_before_http']??null)!==$before['sql_before']||($consumers['all_tables_after_http']??null)!==$before['sql_before']
    ||($consumers['unchanged_during_http']??null)!==true||($final['independent_before_sha256']??null)!==$beforeSha
    ||($final['old_consumers_sha256']??null)!==$consumerSha||($final['runtime']??null)!==$before['identity']
    ||($final['normalized_before']??null)!==$before['sql_before']||count($final['sql_after']??[])!==46
    ||($final['sql_tables']??null)!==46||($final['update_operations']??null)!==70||($final['distinct_changed_rows']??null)!==70
    ||($final['inserted_content_rows']??null)!==3||($final['deleted_rows']??null)!==0||($final['continuing_uk_rows']??null)!==64
    ||($final['unchanged_non_uk_rows']??null)!==128||($final['page_text_updates']??null)!==6
    ||($final['body_updates']??null)!==['subtitle'=>6,'teaching'=>55,'practice'=>3]
    ||count($final['metadata_transitions']??[])!==0||count($final['inserted_practices']??[])!==3
    ||count($final['all_six_practice_bindings']??[])!==6||($final['question_insert_rows']??null)!==131
    ||($final['question_family_insert_counts']??null)!==['questions'=>8,'question_options'=>27,'question_answers'=>31,'question_option_question'=>57,'question_theory_text_blocks'=>8]
    ||($final['root_visual_files_unchanged']??null)!==326||($final['frozen_root_files_unchanged']??null)!==2701
    ||($final['db_writes']??null)!==0||($final['http_requests']??null)!==0){throw new RuntimeException('Reviewed final does not bind the complete finite M79 AFTER/BEFORE inventory.');}
foreach(['all_old_rows_and_timestamps_exact','independent_before_projection_exact','reviewed_protected_projection_exact',
    'backup_equals_reviewed_preview','source_sync_unchanged','all_preview_sources_exact'] as $field){$same($final[$field]??null,true,'Final proof '.$field);}

require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();set_exception_handler($failure);

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Services\M79ContentPatch as Patch;
use App\Services\M79LocalTargetGuard as Guard;
use App\Support\M26DetailPackage as Source;
use App\Support\M79BuilderSelectionPresentation as Bank;
use App\Support\M79QuestionTypesCompletion as Package;
use App\Support\PageMetadata;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;

if($normal(base_path())!==$normal($root)||$normal(app()->environmentPath())!==$normal($root)||app()->getLocale()!=='uk'
    ||Package::BASE_SHA!=='f33d1a0e2eff08e66044d9b746a46bc33f207bce'||Package::PAGE_IDS!==[132,137,136,138,134,135]
    ||Package::CATEGORY_IDS!==[22,22,22,22,22,22]||Package::LEVELS!==['A2','B1','B1','A2','B1','B1']
    ||Patch::BEFORE_SHA!==$beforeSha||Patch::CONSUMERS_SHA!==$consumerSha){throw new RuntimeException('Actual served runtime/package identity differs.');}
App\Services\M51ContentPatch::assertConsumerCapture($before,$consumers);
[$sourceBefore,$patch,$master,$contract]=Package::load($root);Package::validate($sourceBefore,$patch,$master,$contract);$audit=Bank::loadAudit($root);
foreach(Package::profile()['artifacts'] as $path=>$sha){$same($hash($root.'/'.$path),$sha,'Frozen package artifact');$pins[$root.'/'.$path]=$sha;}
$same($hash($root.'/'.Bank::AUDIT),Bank::AUDIT_SHA,'Frozen thematic bank audit');$pins[$root.'/'.Bank::AUDIT]=Bank::AUDIT_SHA;
$checksums=json_decode(file_get_contents($root.'/'.Package::CHECKSUMS),true,flags:JSON_THROW_ON_ERROR);
foreach([...$checksums['artifacts'],...$checksums['active_definitions']] as $record){$file=$root.'/'.$record['path'];
    $same($hash($file),$record['sha256'],'Complete frozen artifact/active definition');$same(filesize($file),$record['bytes'],'Exact frozen source length');$pins[$file]=$record['sha256'];}
$planName=$final['plan_file']??'';$backupName=$final['backup_file']??'';
if(preg_match('/^preview-v[1-9][0-9]*\.json$/D',$planName)!==1||preg_match('/^backup-v[1-9][0-9]*\.json$/D',$backupName)!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$final['backup_sha256']??'')!==1){throw new RuntimeException('Final preview/backup lineage absent.');}
$plan=$read($planName,$hash($dir.'/'.$planName));$backup=$read($backupName,$final['backup_sha256']);$unsigned=$plan;unset($unsigned['sha256']);
$same($plan,$backup,'Complete reviewed preview/backup data');$same(file_get_contents($dir.'/'.$planName),file_get_contents($dir.'/'.$backupName),'Exclusive backup raw bytes');
$same(App\Services\M26ContentPatch::digest($unsigned),$final['plan_digest'],'Reviewed plan digest');$same($plan['sha256'],$final['plan_digest'],'Signed reviewed plan');
$same([$plan['patch'],$plan['version'],$plan['names'],$plan['state']],['m79-question-types-completion-standard-view-v1',1,Package::OWNERS,'before'],'Exact finite preview');
$same([count($plan['updates']),count($plan['inserts']),$plan['deletes']??[]],[70,3,[]],'Exact reviewed operations');$same($plan['protected'],$final['protected'],'Final protected projection');
$expectedSources=array_values(array_unique([...Patch::sourcePaths($patch,$contract,$root),'private:'.Patch::BEFORE_FILE,'private:'.Patch::CONSUMERS_FILE,'private:'.Patch::SYNC_RECEIPT,'private:question-inventory-v2.json','private:question-plan-v1.json']));
$expectedSources=array_map($initialPath,$expectedSources);sort($expectedSources);$sourceKeys=array_keys($plan['sources']);sort($sourceKeys);$same($sourceKeys,$expectedSources,'Complete reviewed source list');
foreach($plan['sources'] as $path=>$sha){$file=str_starts_with($path,'private:')?$dir.'/'.substr($path,8):$root.'/'.$path;$pinInitial($path,$sha,$file);}
$sync=$read(Patch::SYNC_RECEIPT,$plan['sources']['private:'.Patch::SYNC_RECEIPT]);$syncPaths=array_column($sync['files'],'path');sort($syncPaths);
$expectedSync=array_map($initialPath,Patch::syncPaths($patch,$contract));sort($expectedSync);$same($syncPaths,$expectedSync,'Served/worktree finite initial source scope');
$same([$sync['base_sha'],$sync['served_root'],$sync['source_worktree'],$sync['visual_before']],
    [Package::BASE_SHA,$root,Patch::SOURCE_WORKTREE,['file'=>Patch::BEFORE_FILE,'sha256'=>$beforeSha]],'Source-sync independent lineage');
foreach($sync['files'] as $record){foreach([$root.'/'.$record['path']=>$record['root_after_sha256'],Patch::SOURCE_WORKTREE.'/'.$record['path']=>$record['worktree_sha256']] as $file=>$sha){
    $pinInitial($record['path'],$sha,$file);}}
foreach([$before['physical_hashes'],$before['visual_hashes'],$before['frozen_hashes']] as $kind=>$records){foreach($records as $path=>$sha){
    if($kind>0&&preg_match('~^(?:[A-Za-z]:|/|\\\\)|(?:^|[/\\\\])\.\.(?:[/\\\\]|$)~D',$path)){throw new RuntimeException('Frozen relative path escaped repository.');}
    $file=$kind===0?$path:$root.'/'.$path;$same($hash($file),$sha,'Physical/visual/frozen source');$pins[$file]=$sha;}}
$same($prospective,$master['editorial_review']['expected_http_metadata']??null,'Frozen pre-apply metadata receipt');
if(($prospective['independent_before_sha256']??null)!==$beforeSha||($prospective['application_bootstrapped']??null)!==false
    ||($prospective['working_env_loaded']??null)!==false||($prospective['db_connections']??null)!==0||($prospective['db_queries']??null)!==0
    ||($prospective['db_writes']??null)!==0||($prospective['http_requests']??null)!==0
    ||array_column($prospective['metadata']??[],'identity')!==Package::OWNERS){throw new RuntimeException('Pre-apply metadata provenance differs.');}
$controllerPath='app/Http/Controllers/PageController.php';$controllerSha=$before['shared_source_hashes'][$controllerPath]['root']??null;
if(!is_string($controllerSha)){throw new RuntimeException('Independent native PageController pin absent.');}
$metadataPins=$prospective['native_policy_pins']+[$controllerPath=>$controllerSha];
foreach($metadataPins as $path=>$sha){$same($hash($root.'/'.$path),$sha,'Unchanged native metadata/resolver policy');$pins[$root.'/'.$path]=$sha;}
foreach([Package::class=>'app/Support/M79QuestionTypesCompletion.php',Guard::class=>'app/Services/M79LocalTargetGuard.php',
    PageMetadata::class=>'app/Support/PageMetadata.php',App\Http\Controllers\PageController::class=>$controllerPath] as $class=>$path){
    $same($normal((new ReflectionClass($class))->getFileName()),$normal($root.'/'.$path),'Actual served helper location');}
$plain=static fn(string $s):string=>trim((string)preg_replace('/[\s\x{00a0}]+/u',' ',html_entity_decode(strip_tags($s),ENT_QUOTES|ENT_HTML5,'UTF-8')));
$approved=[];
foreach(Package::OWNERS as $i=>$owner){$row=$prospective['metadata'][$i];$definition=$patch['targets'][$i]['after'];$hero=json_decode($definition['page']['blocks'][0]['body'],true,flags:JSON_THROW_ON_ERROR);
    $same([$row['raw_title'],$row['preserved_h1'],$row['local_url'],$row['hero_intro_sha256']],
        [$definition['page']['title'],Package::RENDERED_TITLES[$i],$patch['targets'][$i]['local_url'],hash('sha256',$hero['intro'])],'Exact pre-apply target/hero identity');
    $same(PageMetadata::theory($definition['page']['title'],'',$hero['intro'],$owner,'uk'),$row['prospective_page_metadata'],'Pre-apply native title/description');
    $resource=null;foreach($definition['page']['blocks'] as $block){$data=json_decode($block['body'],true);if(!is_array($data)){continue;}
        foreach(['intro','description','summary','subtitle','text'] as $key){if(is_string($data[$key]??null)&&trim(strip_tags($data[$key]))!==''){$resource=Illuminate\Support\Str::limit($plain($data[$key]),300,'…');break 2;}}}
    $same($resource,$row['prospective_learning_resource_description'],'Pre-apply native structured description');$approved[$owner]=$row;
}

$db=Illuminate\Support\Facades\DB::connection();Guard::assertConfiguredDatabase($db);$guard=new Guard;$physical=$guard->physicalEvidence();
$server=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach(['datadir','pid_file'] as $field){if(!is_string($server[$field]??null)||preg_match('~^[A-Za-z]:/~D',str_replace('\\','/',$server[$field]))!==1){throw new RuntimeException('MySQL physical path is not absolute local Windows.');}}
$dataPath=$normal($server['datadir']);$pidPath=$normal($server['pid_file']);$pid=trim((string)file_get_contents($server['pid_file']));
if(($server['db']??null)!=='gr2'||(int)($server['port']??0)!==3306||!str_starts_with($server['os']??'','Win')
    ||!ctype_digit($pid)||!str_starts_with($pidPath,$dataPath.'/')||str_starts_with($dataPath,'//')){throw new RuntimeException('Fresh physical local MySQL identity differs.');}
$guard->assertEvidence($physical,$normal($root),(int)$pid,3306);$runtime=Guard::publicIdentity($db,$root.'/public');
$same($runtime,$before['identity'],'Independent local runtime');$same($runtime,$final['runtime'],'Reviewed final local runtime');
$readOnly=static function(Closure $callback)use($db):mixed{$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();try{return $callback();}finally{$db->rollBack();}};
$scan=static function()use($db,$before,$same):array{
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDatabaseName(),schemaQualified:false);sort($tables);$same($tables,array_keys($before['sql_before']),'Complete46 table inventory');$out=[];
    foreach($tables as $table){$schema=$db->getSchemaBuilder()->getColumns($table);if(isset($before['schema'][$table])){$same($schema,$before['schema'][$table],'Captured unchanged schema');}
        $columns=array_column($schema,'name');$query=$db->table($table);foreach(in_array('id',$columns,true)?['id']:$columns as $column){$query->orderBy($column);}
        $h=hash_init('sha256');$count=0;foreach($query->cursor() as $record){hash_update($h,App\Services\M26ContentPatch::digest((array)$record)."\n");++$count;}
        $out[$table]=['count'=>$count,'sha256'=>hash_final($h)];}return $out;
};
$sqlBefore=$readOnly($scan);$same($sqlBefore,$final['sql_after'],'Fresh all46 exact reviewed AFTER before resolver/GET');

// Existing protected methods, not a new URL/title/order/SEO implementation. No cache method is invoked.
$resolver=new class extends App\Http\Controllers\TheoryController{
    public function __construct(){}
    public function categoryFor(string $path):?PageCategory{return $this->resolveCategoryBySlugPath($path);}
    public function path(PageCategory $category):string{return $this->categorySlugPath($category);}
    public function display(?string $subtitle,string $fallback):string{return $this->extractLocalizedTitleFromSubtitle($subtitle)??$fallback;}
    public function categoryMetadata(PageCategory $category):array{
        $originalPages=Page::query()->where('page_category_id',$category->id)->where('type','theory')->orderBy('title')->get();
        $available=[...Page::query()->where('type','theory')->pluck('title')->all(),...PageCategory::query()->where('type','theory')->where('language','uk')->pluck('title')->all()];
        $blocks=TextBlock::query()->where('page_category_id',$category->id)->whereNull('page_id')->where('locale','uk')->orderBy('sort_order')->get();
        // PageController::category uses an empty ordering after a cached category-list
        // hit, or its computed site-tree ordering on the uncached native branch.
        // Compute both from original models, before observing HTTP; do not access caches.
        $candidates=[];$h1=null;
        foreach(['cached-empty-ordering'=>[],'uncached-site-tree-ordering'=>$this->siteTreeLinkOrdering($available)] as $branch=>$ordering){
            $copy=clone $category;$pages=$originalPages->map(static fn($page)=>clone $page);
            $pages=$this->sortPagesWithOrdering($pages,$ordering);
            $copy->setRelation('pages',$pages);$copy->setRelation('textBlocks',$blocks->map(static fn($block)=>clone $block));
            $this->localizeDisplayedTitles(collect([$copy]),$copy,$pages,null,'uk','uk');
            if($h1!==null&&$h1!==$copy->title){throw new RuntimeException('Native category branches disagree on H1.');}$h1=$copy->title;
            $hero=$copy->textBlocks->firstWhere('type','hero-v2')??$copy->textBlocks->firstWhere('type','hero');$data=$hero?json_decode($hero->body,true):[];
            $candidates[$branch]=PageMetadata::category($copy->title,is_string($data['intro']??null)?$data['intro']:'',$pages->pluck('title')->all());
        }
        return ['h1'=>$h1,'seo_candidates'=>$candidates];
    }
};
// Expected destinations and counts are compiled from frozen teaching sources BEFORE observing HTTP.
$categoryPaths=['pytalni-rechennia-ta-zaperechennia'=>21,
    'pytalni-rechennia-ta-zaperechennia/types-of-questions'=>22];
$categoryHrefs=[];
foreach($categoryPaths as $path=>$_){
    $segments=explode('/',$path);$slug=end($segments);
    // The existing category route takes one slug; only theory.show takes the full ancestry.
    $href=route('theory.category',['category'=>$slug],false);
    $same($href,'/theory/'.$slug,'Native category route from frozen ancestry');$categoryHrefs[$href]=$path;
}
$routes=$targets=$navigation=[];$navItems=$clickable=$formItems=$sourceGraded=$sourceDemos=$sourceInputs=$sourceFull=0;$authoredPaths=[];
foreach($master['lessons'] as $i=>$lesson){
    $owner=$lesson['identity'];$ownerPath=parse_url($lesson['local_url'],PHP_URL_PATH);
    $same($lesson['local_url'],'http://gramlyze.loc'.$ownerPath,'Frozen exact local target URL');
    $same($ownerPath,'/theory/'.implode('/',Package::ANCESTRIES[$i]).'/'.Package::SLUGS[$i],'Nested canonical path from frozen identity');
    $targets[$ownerPath]=['index'=>$i,'owner'=>$owner];$navs=array_values(array_filter($lesson['blocks'],static fn($b)=>$b['type']==='navigation-chips'));
    $same(count($navs),1,'One actual footer navigation');$nav=$navs[0];$same(count($nav['data']['items']),[7,7,7,9,8,8][$i],'Retained exact footer item count');
    $same(count(array_filter($nav['data']['items'],static fn($item)=>$item['current']===true)),1,'One frozen current item');
    $forms=[];
    foreach($lesson['blocks'] as $slot=>$block){if($block['type']==='forms-grid'){foreach($block['data']['items'] as $item){
        if(isset($item['url'])){$forms[]=['source_index'=>$slot,'item'=>$item];}}}}
    $same(count($forms),0,'M79 formula cards have no additional links');
    $navigation[$owner]=['nav'=>$nav['data'],'forms_links'=>$forms];
    foreach($nav['data']['items'] as $item){++$navItems;if(!$item['current']){++$clickable;}$authoredPaths[]=$item['url'];}
    foreach($forms as $form){++$clickable;++$formItems;$authoredPaths[]=$form['item']['url'];}
    $walk=static function(array $value)use(&$walk,&$authoredPaths):void{foreach($value as $key=>$child){if(is_array($child)){$walk($child);}elseif(is_string($child)){
        if(in_array($key,['url','href'],true)){$authoredPaths[]=$child;}
        if(preg_match_all('/\bhref=["\']([^"\']+)["\']/u',$child,$found)){foreach($found[1] as $href){$authoredPaths[]=html_entity_decode($href,ENT_QUOTES|ENT_HTML5,'UTF-8');}}
    }}};$walk($lesson['blocks']);$authoredPaths[]=$ownerPath;
    $practice=$lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];$graded=$demos=0;
    foreach(['selects','choices','inputs','rephrase'] as $group){foreach($practice[$group] as $task){
        if(isset($task['example_original'])){++$demos;}else{++$graded;}
    }}
    $same($graded,Package::GRADED_COUNTS[$i],'Frozen graded task count excluding retained demos');
    $same($demos,$i<3?1:0,'Frozen retained demo count');$same(count($practice['inputs']),Package::INPUT_COUNTS[$i],'Frozen all-input count');
    $same(count($lesson['details']),[0,1,0,1,1,1][$i],'Exact finite meaningful detail decisions');
    $sourceGraded+=$graded;$sourceDemos+=$demos;$sourceInputs+=count($practice['inputs']);$sourceFull+=count(Package::SENTENCE_INPUT_INDICES[$i]);
}
foreach($categoryHrefs as $href=>$_){$authoredPaths[]=$href;}
$same([$navItems,$clickable,$formItems,$sourceGraded,$sourceDemos,$sourceInputs,$sourceFull],[46,40,0,57,3,20,16],'Exact frozen M79 navigation/practice cardinalities');
$authoredPaths=array_values(array_unique($authoredPaths));sort($authoredPaths);
$readOnly(static function()use(&$routes,$targets,$authoredPaths,$categoryPaths,$categoryHrefs,$resolver,$root,$hash,&$pins,$same):void{
    foreach($authoredPaths as $path){
        if(preg_match('~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+){0,2}$~D',$path)!==1){throw new RuntimeException('Authored href is outside finite local M79 paths.');}
        $tail=substr($path,8);$isCategory=array_key_exists($path,$categoryHrefs);
        $categoryPath=$isCategory?$categoryHrefs[$path]:substr($tail,0,(int)strrpos($tail,'/'));
        if(!array_key_exists($categoryPath,$categoryPaths)){throw new RuntimeException('Authored href has an unapproved nested category.');}
        $categoryId=$categoryPaths[$categoryPath];$category=$resolver->categoryFor($categoryPath);
        if(!$category instanceof PageCategory||$category->id!==$categoryId||$category->type!=='theory'||$category->language!=='uk'){throw new RuntimeException('Native UK category resolution differs.');}
        $same($resolver->path($category),$categoryPath,'Native full nested category path');
        if($isCategory){
            $meta=$resolver->categoryMetadata($category);
            $routes[$path]=['kind'=>'category','category_id'=>$categoryId,'identity'=>null,'url'=>$path,'expected_h1'=>$meta['h1'],'expected_seo_candidates'=>$meta['seo_candidates']];continue;
        }
        $slug=substr($tail,(int)strrpos($tail,'/')+1);
        $page=Page::query()->where('page_category_id',$categoryId)->where('slug',$slug)->where('type','theory')->sole();
        $same(route('theory.show',['categoryPath'=>$categoryPath,'pageSlug'=>$page->slug],false),$path,'Native actual nested Page href');
        $definitionPath=Acceptance::definitionPath($page->seeder);$file=$root.'/'.$definitionPath;$sha=$hash($file);$pins[$file]=$sha;
        $definition=json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
        $categorySlug=substr($categoryPath,(int)(strrpos('/'.$categoryPath,'/')));
        $same([$definition['seeder']['class']??null,$definition['slug']??null,$definition['page']['locale']??null,$definition['page']['category']['slug']??null],
            [$page->seeder,$page->slug,'uk',$categorySlug],'Exact UK neighbor/target versioned source');
        $same($page->getRawOriginal('title'),$definition['page']['title'],'Versioned raw Page title');$hero=[];
        foreach(['hero-v2','hero'] as $type){foreach($definition['page']['blocks'] as $block){if($block['type']===$type){$hero=json_decode($block['body'],true,flags:JSON_THROW_ON_ERROR);break 2;}}}
        $routes[$path]=['kind'=>'theory','identity'=>$page->seeder,'page_id'=>$page->id,'category_id'=>$categoryId,'raw_title'=>$page->getRawOriginal('title'),'url'=>$path,
            'expected_h1'=>$resolver->display($definition['page']['subtitle_html']??null,$page->getRawOriginal('title')),
            'expected_seo'=>PageMetadata::theory($definition['page']['title'],'',$hero['intro']??'',$page->seeder,'uk')];
    }
});
$same([count($routes),count($targets),count(array_filter($routes,static fn($r)=>$r['kind']==='category'))],[12,6,2],'Ten exact theory destinations including six targets, plus two categories');
$same($readOnly($scan),$sqlBefore,'All46 tables after native resolver before GET');
$started=gmdate('c');$rows=[];$renderedNav=$renderedLinks=$renderedForms=$ownTotal=$demoTotal=$inputTotal=$fullTotal=$widgetTotal=0;
foreach($routes as $path=>$resolved){
    echo json_encode(['checking_local_path'=>$path],JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR),PHP_EOL;
    $url='http://gramlyze.loc'.$path;$curl=curl_init($url);$robots=$location=null;
    curl_setopt_array($curl,[CURLOPT_HTTPGET=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_MAXREDIRS=>0,
        CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>45,CURLOPT_PROXY=>'',CURLOPT_NOPROXY=>'*',CURLOPT_NETRC=>CURL_NETRC_IGNORED,CURLOPT_COOKIE=>'',
        CURLOPT_PROTOCOLS=>CURLPROTO_HTTP,CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1'],CURLOPT_HTTPHEADER=>['Accept: text/html'],
        CURLOPT_HEADERFUNCTION=>static function($h,string $header)use(&$robots,&$location):int{if(stripos($header,'X-Robots-Tag:')===0){$robots=trim(substr($header,13));}if(stripos($header,'Location:')===0){$location=trim(substr($header,9));}return strlen($header);}]);
    try{$html=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);
        if(!is_string($html)||$status!==200||$location!==null||curl_getinfo($curl,CURLINFO_PRIMARY_IP)!=='127.0.0.1'
            ||curl_getinfo($curl,CURLINFO_PRIMARY_PORT)!==80||curl_getinfo($curl,CURLINFO_EFFECTIVE_URL)!==$url||curl_getinfo($curl,CURLINFO_REDIRECT_COUNT)!==0){throw new RuntimeException('Direct anonymous fixed-loopback GET failed: '.$path);}
    }finally{unset($curl);}
    $dom=new DOMDocument('1.0','UTF-8');$oldErrors=libxml_use_internal_errors(true);try{$loaded=$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($oldErrors);}
    if(!$loaded){throw new RuntimeException('Native HTML cannot be parsed.');}$xp=new DOMXPath($dom);
    $one=static function(string $query,?DOMNode $context=null)use($xp):DOMElement{$nodes=$xp->query($query,$context);if($nodes->length!==1||!$nodes->item(0) instanceof DOMElement){throw new RuntimeException('Expected one native DOM node: '.$query);}return $nodes->item(0);};
    $h1=$one('//h1');$canonical=$one('//link[@rel="canonical"]');$description=$one('//meta[@name="description"]')->getAttribute('content');
    $facts=['h1_count'=>1,'h1'=>$plain($h1->textContent),'title'=>$plain($one('//title')->textContent),
        'breadcrumb'=>$plain($one('//nav[@aria-label][contains(@class,"mb-8")]')->textContent),'canonical'=>$canonical->getAttribute('href'),
        'robots_header'=>$robots,'robots_meta'=>$xp->query('//meta[@name="robots"]')->item(0)?->getAttribute('content')];
    $same($facts['h1'],$resolved['expected_h1'],'Native source-derived H1');$same($facts['canonical'],'https://gramlyze.com'.$path,'Canonical string only, never requested');
    $same($robots,'noindex, nofollow, noarchive','Local noindex header');$actualSeo=['title'=>$facts['title'],'description'=>$description];$categoryBranches=[];
    if($resolved['kind']==='category'){
        $categoryBranches=array_keys(array_filter($resolved['expected_seo_candidates'],static fn($candidate)=>$candidate===$actualSeo));
        if($categoryBranches===[]){throw new RuntimeException('Category SEO differs from both independently computed native ordering branches.');}
    }else{$same($actualSeo,$resolved['expected_seo'],'Native source/resolver SEO metadata');}
    $row=$resolved+['status'=>$status,'html_sha256'=>hash('sha256',$html),'target'=>isset($targets[$path]),'metadata'=>$facts+['description'=>$description]];
    if($resolved['kind']==='category'){$row['matched_native_category_branches']=$categoryBranches;}
    if(isset($targets[$path])){$i=$targets[$path]['index'];$owner=$targets[$path]['owner'];$approval=$approved[$owner];$main=$one('//*[@data-theory-main]');
        $old=array_values(array_filter($before['http'],static fn($r)=>$r['kind']==='theory'&&$r['identity']===$owner));$same(count($old),1,'Independent target HTTP row');$old=$old[0];
        foreach(['h1_count','h1','title','breadcrumb','canonical','robots_header','robots_meta'] as $field){$same($facts[$field],$old[$field],'Protected BEFORE target '.$field);}
        $protectedFacts=array_intersect_key($facts,array_flip(['h1_count','h1','title','breadcrumb','canonical','robots_header','robots_meta']));
        $protectedApproval=$approval['independent_before_protected'];ksort($protectedFacts);ksort($protectedApproval);
        $same($protectedFacts,$protectedApproval,'Frozen pre-apply protected target metadata');
        $same(['title'=>$facts['title'],'description'=>$description],$approval['prospective_page_metadata'],'Served description equals pre-apply exact approval');
        $resources=$descriptions=[];$visit=static function(mixed $value)use(&$visit,&$resources,&$descriptions):void{if(!is_array($value)){return;}
            if(in_array('LearningResource',(array)($value['@type']??[]),true)){$resources[]=array_intersect_key($value,array_flip(['@type','url','name','headline','inLanguage']));$descriptions[]=$value['description']??null;}
            foreach($value as $child){if(is_array($child)){$visit($child);}}};foreach($xp->query('//script[@type="application/ld+json"]') as $node){$visit(json_decode($node->textContent,true,flags:JSON_THROW_ON_ERROR));}
        $oldResources=array_map(static fn($r)=>array_intersect_key($r,array_flip(['@type','url','name','headline','inLanguage'])),$old['learning_resources']);
        $same(count($resources),1,'One target LearningResource');$same($resources,$oldResources,'Unchanged BEFORE LearningResource identity');$same($descriptions,[$approval['prospective_learning_resource_description']],'Exact prospective LearningResource description');
        $anchors=[];foreach($xp->query('//*[@id]') as $node){$anchors[]=$node->getAttribute('id');}$same(array_values(array_diff(array_unique($old['anchors']),array_unique($anchors))),[],'Every original target anchor');
        $same($xp->query('.//*[@data-theory-render-fallback]',$main)->length,0,'No native fallback');
        $same($xp->query('.//section[@data-theory-component="section"]//details',$main)->length,count($master['lessons'][$i]['details']),'Only finite meaningful teaching details');
        $expectedSections=count(array_filter($master['lessons'][$i]['blocks'],static fn($block)=>!in_array($block['type'],['hero','navigation-chips','practice-set'],true)));
        $same($xp->query('.//*[@data-theory-component="section"]',$main)->length,$expectedSections,'Actual canonical content-section structure from frozen blocks');
        $colorProof=[];$usageCount=0;
        foreach($master['lessons'][$i]['blocks'] as $slot=>$block){if($block['type']!=='usage-panels'){continue;}++$usageCount;
            $sourceRows=array_values(array_filter($before['pages'][$owner]['blocks'],static fn($r)=>$r['locale']==='uk'&&$r['sort_order']===$slot+1));
            $same(count($sourceRows),1,'Continuing native point owner');$section=$one('.//*[@id="block-'.$sourceRows[0]['id'].'"]',$main);
            $panels=$xp->query('.//*[@data-theory-point-panel]',$section);$same($panels->length,count($block['data']['sections']),'Native whole-point panel count');
            foreach($block['data']['sections'] as $n=>$point){$same($panels->item($n)->getAttribute('data-theory-accent'),$point['color'],'Reviewed native semantic accent');
                $colorProof[]=['source_index'=>$slot,'point_index'=>$n,'accent'=>$point['color']];}
        }
        $same($xp->query('.//*[@data-theory-reference="native"]',$main)->length,$usageCount,'Native point-panel reference structure, not browser color acceptance');
        $data=$navigation[$owner]['nav'];$nav=null;foreach($xp->query('.//nav[p]',$main) as $candidate){if($plain($candidate->getElementsByTagName('p')->item(0)?->textContent??'')===$plain($data['title'])){if($nav!==null){throw new RuntimeException('Duplicate native footer navigation.');}$nav=$candidate;}}
        if(!$nav instanceof DOMElement){throw new RuntimeException('Native footer navigation absent.');}$nodes=$xp->query('./div/*',$nav);$same($nodes->length,count($data['items']),'Exact rendered footer item count');$links=[];
        foreach($data['items'] as $n=>$item){$node=$nodes->item($n);$same($plain($node->textContent),$plain($item['label']),'Complete footer label');++$renderedNav;
            if($item['current']){$same($node->nodeName,'span','Current chip is not an anchor');$same($item['url'],$path,'Current native resource');}
            else{$same($node->nodeName,'a','Noncurrent chip is clickable');$same($node->getAttribute('href'),$item['url'],'Exact native href');$links[]=$item['url'];++$renderedLinks;}}
        foreach($navigation[$owner]['forms_links'] as $extra){$a=$one('.//a[@data-theory-component="form" and @href="'.$extra['item']['url'].'"]',$main);
            foreach(['label','title','subtitle'] as $field){$same(str_contains($plain($a->textContent),$plain($extra['item'][$field])),true,'Visible native linked formula card '.$field);}$links[]=$extra['item']['url'];++$renderedForms;}
        $binding=$audit['practice_bindings'][$i];$sqlBinding=array_values(array_filter($final['all_six_practice_bindings'],static fn($r)=>$r['owner']===$owner));$same(count($sqlBinding),1,'Final exact practice physical ID');
        $practice=$one('.//*[@id="block-'.$sqlBinding[0]['id'].'"]',$main);$models=$xp->query('.//*[@x-data and starts-with(@x-data,"theoryPracticeSet(")]',$practice);$same($models->length,1,'One own native practice model');
        $expr=$models->item(0)->getAttribute('x-data');$prefix="theoryPracticeSet(JSON.parse('";$suffix="'))";
        if(!str_starts_with($expr,$prefix)||!str_ends_with($expr,$suffix)){throw new RuntimeException('Native Js::from practice config shape differs; no eval fallback.');}
        $encoded=substr($expr,strlen($prefix),-strlen($suffix));$json=json_decode('"'.$encoded.'"',true,flags:JSON_THROW_ON_ERROR);$config=json_decode($json,true,flags:JSON_THROW_ON_ERROR);
        $same($expr,'theoryPracticeSet('.Illuminate\Support\Js::from($config)->toHtml().')','Exact safe native own-config roundtrip');
        $ownData=$master['lessons'][$i]['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];$ownCount=$demoCount=0;
        foreach(['selects','choices','inputs','rephrase'] as $group){
            $same($config[$group],$ownData[$group],'Complete server own group '.$group);
            foreach($config[$group] as $task){if(isset($task['example_original'])){++$demoCount;}else{++$ownCount;}}
        }
        $same($ownCount,Package::GRADED_COUNTS[$i],'Server exact graded own count excluding demos');
        $same($demoCount,$i<3?1:0,'Server retained demo count');$ownTotal+=$ownCount;$demoTotal+=$demoCount;
        $controls=$xp->query('.//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]',$practice);
        $same($controls->length,Package::INPUT_COUNTS[$i],'All real native input controls');$inputTotal+=$controls->length;
        $visible='';foreach($xp->query('.//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::template)]',$practice) as $node){$visible.=' '.$node->textContent;}
        foreach(Package::SENTENCE_INPUT_INDICES[$i] as $index){
            $field=$one('.//*[@data-word-suggestion-input="inputs-'.$index.'"]',$practice);
            $same($field->hasAttribute('@input.debounce.150ms'),false,'Full-sentence token/manual control has no word-suggestion request binding');
            $same($field->getAttribute('autocomplete'),'off','Native full-sentence autocomplete is disabled');
            $same(str_contains($plain($visible),$plain($ownData['inputs'][$index]['after'])),true,'Visible Ukrainian full-sentence condition');
            ++$fullTotal;
        }
        $same($xp->query('.//*[@data-sentence-builder]',$practice)->length,1,'One native widget inside exact practice');$same($xp->query('.//*[@data-sentence-builder]',$main)->length,1,'No duplicate target widget');++$widgetTotal;
        $payload=Acceptance::payload($html,$binding['uuid']);if(!is_array($payload)){throw new RuntimeException('Native widget payload absent.');}$same(count($payload),5,'Five server questions');
        $same(array_column($payload,'id'),$audit['targets'][$i]['expected_selected_ids'],'Exact public-audit selected widget IDs');
        foreach($payload as $n=>$question){$sample=$audit['targets'][$i]['first_five'][$n];$same($question['type'],'4','Native compose type');$same($question['question'],$sample['visible_condition_uk'],'Caller-owned condition clone');$same($question['correct_tokens'],$sample['correct_tokens'],'Unchanged canonical native tokens');}
        $row['rendered_teaching_links']=$links;$row['native_semantic_accents']=$colorProof;$row['own_graded']=$ownCount;$row['retained_demos']=$demoCount;$row['input_controls']=$controls->length;$row['full_sentence_inputs']=count(Package::SENTENCE_INPUT_INDICES[$i]);$row['widget_selected_ids']=array_column($payload,'id');
        $row['metadata']['learning_resource_identity']=$resources[0];$row['metadata']['learning_resource_description']=$descriptions[0];$row['metadata']['preserved_old_anchor_count']=count(array_unique($old['anchors']));
    }$rows[$path]=$row;
}
$same([$renderedNav,$renderedLinks,$renderedForms,$ownTotal,$demoTotal,$inputTotal,$fullTotal,$widgetTotal],[46,40,0,57,3,20,16,6],'All target native navigation/own/widget structures');
// Old D/E/F sources used short category paths; they are not new authored links.
// Check their actual native boundary independently, never follow a redirect or request .com.
$legacy=[];
foreach($sourceBefore['targets'] as $target){foreach($target['before']['page']['blocks'] as $block){
    if($block['type']!=='navigation-chips'){continue;}$data=json_decode($block['body'],true,flags:JSON_THROW_ON_ERROR);
    foreach($data['items'] as $item){$path=$item['url']??null;if(!is_string($path)){continue;}
        if(!str_starts_with($path,'/theory/types-of-questions/')){throw new RuntimeException('Unexpected legacy authored path.');}
        $canonical='/theory/pytalni-rechennia-ta-zaperechennia/'.substr($path,8);
        $same($rows[$canonical]['status']??null,200,'Legacy short href replacement canonical GET');
        $legacy[$path]=['before_source_path'=>$path,'replacement'=>$canonical];
    }
}}
$same(count($legacy),9,'All nine distinct old short hrefs');
$same($readOnly(static fn()=>$resolver->categoryFor('types-of-questions')),null,'Existing native resolver rejects ancestry-less old category');
foreach($legacy as $path=>&$item){
    echo json_encode(['checking_legacy_source_path'=>$path],JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR),PHP_EOL;
    $curl=curl_init('http://gramlyze.loc'.$path);$location=null;
    curl_setopt_array($curl,[CURLOPT_HTTPGET=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_MAXREDIRS=>0,
        CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>45,CURLOPT_PROXY=>'',CURLOPT_NOPROXY=>'*',CURLOPT_NETRC=>CURL_NETRC_IGNORED,CURLOPT_COOKIE=>'',
        CURLOPT_PROTOCOLS=>CURLPROTO_HTTP,CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1'],
        CURLOPT_HEADERFUNCTION=>static function($h,string $header)use(&$location):int{if(stripos($header,'Location:')===0){$location=trim(substr($header,9));}return strlen($header);}]);
    try{$html=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);
        if(!is_string($html)||$status!==404||$location!==null||curl_getinfo($curl,CURLINFO_PRIMARY_IP)!=='127.0.0.1'
            ||curl_getinfo($curl,CURLINFO_PRIMARY_PORT)!==80||curl_getinfo($curl,CURLINFO_EFFECTIVE_URL)!=='http://gramlyze.loc'.$path
            ||curl_getinfo($curl,CURLINFO_REDIRECT_COUNT)!==0){throw new RuntimeException('Old short path native 404 boundary differs.');}
        $item+=['status'=>$status,'location'=>$location,'replacement_status'=>200,'http_sha256'=>hash('sha256',$html)];
    }finally{unset($curl);}
}unset($item);

$linkEvidence=[];foreach($rows as $row){foreach($row['rendered_teaching_links']??[] as $href){$same($rows[$href]['status']??null,200,'Every native anchor direct GET200');$linkEvidence[]=['owner'=>$row['identity'],'href'=>$href,'status'=>200,'destination_html_sha256'=>$rows[$href]['html_sha256']];}}
$same(count($linkEvidence),40,'All40 noncurrent footer anchors direct GET200');$sqlAfter=$readOnly($scan);$same($sqlAfter,$sqlBefore,'All46 exact tables unchanged across resolver/GET');$same($sqlAfter,$final['sql_after'],'All46 still match final reviewed AFTER');
foreach($pins as $file=>$sha){$same($hash($file),$sha,'Every physical/evidence/source pin after GET: '.$file);}
$receipt=['schema_version'=>1,'status'=>'PASS','started_at'=>$started,'finished_at'=>gmdate('c'),'base_sha'=>Package::BASE_SHA,
    'independent_before_sha256'=>$beforeSha,'old_consumers_sha256'=>$consumerSha,'reviewed_final_file'=>$finalName,'reviewed_final_sha256'=>$finalSha,'combined_lineage'=>$final['combined_lineage'],
    'prospective_metadata_file'=>'prospective-metadata-v1.json','prospective_metadata_sha256'=>$prospectiveSha,'runtime'=>$runtime,'diagnostic_sha256'=>$pins[__FILE__],
    'master_sha256'=>Package::MASTER_SHA,'package_sha256'=>Package::SOURCE_SHA,'contract_sha256'=>Package::CONTRACT_SHA,'bank_audit_sha256'=>Bank::AUDIT_SHA,
    'legacy_short_policy'=>'Nine original D/E/F short paths are checked as existing native404s, not claimed as BEFORE HTTP captures; every replacement nested href is independently GET200. No routes or redirects were changed.',
    'transport'=>['method'=>'GET','anonymous'=>true,'fixed_loopback'=>'127.0.0.1:80','redirects'=>0,'proxy'=>false,'cookies_sent'=>false,'auth_sent'=>false],
    'pre_apply_metadata_verified_before_http'=>6,'target_before_h1_title_breadcrumb_canonical_robots_and_resource_identity_exact'=>true,
    'target_descriptions_exact_prospective'=>true,'all_original_target_anchors_preserved'=>true,
    'category_metadata_policy'=>'Exact match to one of two independently computed unchanged native branches: empty ordering on cached category-list hit, or computed site-tree ordering. Original models are cloned and localized only after sorting. No cache access, clear, mutation or observed-only description allowance.',
    'source_navigation'=>$navigation,'legacy_short_source_navigation'=>array_values($legacy),'legacy_short_GET_404'=>9,'HTTP'=>array_values($rows),'HTTP_200'=>count($rows),'target_theory_GETs'=>6,'readonly_neighbor_theory_GETs'=>4,'category_GETs'=>2,
    'source_nav_items'=>46,'rendered_nav_items'=>46,'current_nonanchors'=>6,'noncurrent_nav_anchors'=>40,'question_forms_linked_cards'=>0,'clickable_anchors'=>40,'every_navigation_or_formula_anchor_get_200'=>$linkEvidence,
    'own_graded_server_tasks'=>57,'retained_demo_server_items'=>3,'all_native_input_controls'=>20,'full_sentence_server_controls'=>16,'authored_details'=>4,'native_widgets'=>6,'each_widget_questions'=>5,
    'category_descriptions_and_neighbor_SQL_protected_by_final_all46'=>true,'visual_files_unchanged'=>326,'frozen_files_unchanged'=>2701,
    'source_sync_unchanged'=>true,'all_old_rows_and_timestamps_exact_in_final_proof'=>true,'current_all46_match_final_proof'=>true,
    'all_46_tables_before_http'=>$sqlBefore,'all_46_tables_after_http'=>$sqlAfter,'sql_unchanged_during_http'=>true,
    'production_requests'=>0,'db_writes'=>0,'runtime_route_or_source_edits'=>0,
    'not_checked'=>['Browser layout, actual visible colors and interaction','Whole-page byte identity (related content may vary)',
        'Neighbor/category BEFORE HTTP parity (not present in the independent capture); current native source/metadata and exact final protected SQL are checked']];
$bytes=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n";
$handle=fopen($dir.'/'.$outputName,'x');if(!$handle){throw new RuntimeException('Exclusive private navigation receipt unavailable.');}
try{if(fwrite($handle,$bytes)!==strlen($bytes)||!fflush($handle)){throw new RuntimeException('Private navigation receipt incomplete.');}}finally{fclose($handle);}
echo json_encode(['status'=>'PASS','file'=>$outputName,'sha256'=>hash('sha256',$bytes),'HTTP_200'=>count($rows),'target_theory_GETs'=>6,
    'clickable_anchors_get_200'=>40,'own_graded'=>57,'all_inputs'=>20,'full_sentence_inputs'=>16,'widgets'=>6,'sql_unchanged_during_http'=>true,
    'db_writes'=>0,'browser_interaction'=>'not_checked'],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),PHP_EOL;
