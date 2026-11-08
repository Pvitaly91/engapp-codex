<?php

namespace App\Services;

use App\Support\M44AuthoredFutureFormsPackage as Package;
use RuntimeException;

/** Authored M44 revision: exact three UK owners; only Page.text and scoped block type/body. Never seeds. */
class M44ContentPatch extends M26ContentPatch
{
    public const NAMES=[
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsWillVsBeGoingToTheorySeeder',
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsPresentContinuousForFutureTheorySeeder',
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsChoosingTheRightFutureFormTheorySeeder',
    ];
    public const ACCEPTED_BASE='d3c5fa811e3e7eb5de2e348a6cd2d0bed3a76ba0';
    public const CATEGORIES=[self::NAMES[0]=>'future-simple',self::NAMES[1]=>'maibutni-formy',self::NAMES[2]=>'maibutni-formy'];
    public const ANCESTRIES=[self::NAMES[0]=>['maibutni-formy','future-simple'],self::NAMES[1]=>['maibutni-formy'],self::NAMES[2]=>['maibutni-formy']];
    public const SHARED_VIEWS=[
        'resources/views/theory/partials/content-block.blade.php',
        'resources/views/theory/show.blade.php',
        'resources/views/theory/partials/point-detail-fragment.blade.php',
        'resources/views/courses/partials/theory-page-content.blade.php',
        'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php',
        'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
        'resources/views/engram/theory/widgets/lesson-rule-cards.blade.php',
        'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
    ];
    public const SHARED_REVIEW='m44-shared-review-v1.json';
    public const SYNC_MANIFEST='source-sync-before-v1/manifest.json';
    protected const ID='m44-authored-future-forms-v1-0-0';
    protected function allowedUpdateFields():array{return ['type','body'];}
    protected function allowedPageUpdateFields():array{return ['text'];}
    protected function localGuard():string{return M44LocalTargetGuard::class;}
    protected function expectedInsertCount():int
    {
        $root=dirname($this->databasePath);[$before,$package]=Package::load($root);Package::validate($before,$package,$root);
        $counts=[];
        foreach($package['targets'] as $i=>$target){
            self::assertDefinitionScope($before['targets'][$i]['before'],$target['after'],$target,$i);
            $counts[]=count($target['after']['page']['blocks'])-count($before['targets'][$i]['before']['page']['blocks']);
        }
        return array_sum($counts);
    }
    /** M44 exposes no restore command or automatic reverse write; retain exclusive backup for audit. */
    public function restore(string $backupPath, ?string $expectedDatabase=null):array
    {
        throw new RuntimeException('M44 restore is not part of this authorized workflow; no database access or writes.');
    }
    /** Pure finite guard, independently testable without booting Laravel, HTTP or any database. */
    public static function assertDefinitionScope(array $before,array $after,array $target,int $index):void
    {
        $name=self::NAMES[$index]??null;
        if($name===null||($target['identity']??null)!==$name
            ||($before['seeder']['class']??null)!==$name||($before['page']['locale']??null)!=='uk'
            ||($before['type']??null)!=='theory'||($before['page']['category']['slug']??null)!==self::CATEGORIES[$name]
            ||($target['ancestry']??null)!==self::ANCESTRIES[$name]
            ||count($before['page']['blocks']??[])!==[9,6,7][$index]
            ||count($after['page']['blocks']??[])-count($before['page']['blocks']??[])!==[2,2,3][$index]){
            throw new RuntimeException('M44 exact definition owner/locale/category ancestry or projected added-block scope differs.');
        }
        $protectedAfter=$after;$protectedAfter['page']['blocks']=$before['page']['blocks'];
        foreach(['subtitle_html','subtitle_text'] as $field)$protectedAfter['page'][$field]=$before['page'][$field];
        if($protectedAfter!==$before)throw new RuntimeException('M44 source changes protected identity/metadata.');
    }
    public function plan(bool $lock=false):array
    {
        $root=dirname($this->databasePath);[$manifest,$package]=Package::load($root);Package::validate($manifest,$package,$root);
        if(array_column($package['targets'],'identity')!==self::NAMES)throw new RuntimeException('M44 exact owner scope differs.');
        $plan=['patch'=>static::ID,'version'=>1,'names'=>self::NAMES,'connection'=>$this->connection(),'sources'=>[],'pages'=>[],'updates'=>[],'inserts'=>[]];
        Package::authorMaster($manifest,$root);
        if($this->localTarget!==null){
            self::reviewedSharedSources($root,$this->privateDirectory);
            self::reviewedWorkingSources($root,$this->privateDirectory,$manifest,$package);
            $plan['sources']['private:'.self::SHARED_REVIEW]=hash_file('sha256',$this->privateDirectory.'/'.self::SHARED_REVIEW);
            $plan['sources']['private:'.self::SYNC_MANIFEST]=hash_file('sha256',$this->privateDirectory.'/'.self::SYNC_MANIFEST);
        }
        foreach(self::sourcePaths($manifest,$package,$root) as $path){
            if(!is_file($root.'/'.$path)||is_link($root.'/'.$path))throw new RuntimeException('M44 code/author source missing or linked: '.$path);
            $plan['sources'][$path]=hash_file('sha256',$root.'/'.$path);
        }
        $states=[];$targetIds=[];
        foreach($package['targets'] as $i=>$target){
            $name=$target['identity'];$before=$manifest['targets'][$i]['before'];$after=$target['after'];$path=$target['path'];
            self::assertDefinitionScope($before,$after,$target,$i);
            $bytes=file_get_contents($root.'/'.$path);try{$actual=json_decode($bytes,true,flags:JSON_THROW_ON_ERROR);}catch(\JsonException){throw new RuntimeException('M44 canonical JSON invalid; no writes.');}
            if($actual!==$after)throw new RuntimeException('M44 canonical source differs.');
            $plan['sources'][$path]=hash('sha256',$bytes);
            foreach(glob(dirname($root.'/'.$path).'/localizations/*.json')?:[] as $localePath)$plan['sources'][substr(str_replace('\\','/',$localePath),strlen(str_replace('\\','/',$root))+1)]=hash_file('sha256',$localePath);
            $owners=$this->rows('pages',fn($q)=>$q->where('seeder',$name),$lock);if(count($owners)!==1)throw new RuntimeException('M44 missing/ambiguous owner.');
            $owner=$owners[0];$chain=$this->categoryChain($owner['page_category_id'],$lock);
            if(array_column($chain,'slug')!==self::ANCESTRIES[$name]||$owner['slug']!==$before['slug']||$owner['type']!=='theory'||$owner['title']!==$before['page']['title'])throw new RuntimeException('M44 owner/category conflict.');
            $rowStates=[];
            $state=$this->candidate($plan['updates'],'pages',$owner,$name,['text'=>$before['page']['subtitle_text']],['text'=>$after['page']['subtitle_text']]);if($state!==null)$rowStates[]=$state;
            $rows=$this->rows('text_blocks',fn($q)=>$q->where('page_id',$owner['id']),$lock);$uk=array_values(array_filter($rows,fn($r)=>$r['locale']==='uk'));
            $subtitle=['type'=>'subtitle','column'=>'header','heading'=>null,'level'=>$before['page']['subtitle_level']??null,'body'=>$before['page']['subtitle_html'],'uuid_key'=>$before['page']['subtitle_uuid_key']??'subtitle'];
            $newSubtitle=$subtitle;$newSubtitle['body']=$after['page']['subtitle_html'];
            $oldConfigs=[$subtitle,...$before['page']['blocks']];$newConfigs=[$newSubtitle,...$after['page']['blocks']];
            foreach($oldConfigs as $position=>$old){
                $new=$newConfigs[$position]??null;if(!$new)throw new RuntimeException('M44 source removes an existing slot.');
                $oldIdentity=$old;$newIdentity=$new;unset($oldIdentity['type'],$oldIdentity['body'],$newIdentity['type'],$newIdentity['body']);
                if($oldIdentity!==$newIdentity)throw new RuntimeException('M44 existing source block metadata changed.');
                $uuid=$this->resolveUuid($name,$old,$position);if($this->resolveUuid($name,$new,$position)!==$uuid)throw new RuntimeException('M44 existing UUID changed.');
                $global=$this->rows('text_blocks',fn($q)=>$q->where('uuid',$uuid),$lock);if(count($global)!==1)throw new RuntimeException('M44 old UUID missing or ambiguous.');
                $r=$global[0];$this->assertOwner($r,$owner,$name,$position);
                foreach(['column','heading','level','css_class'] as $field)if($r[$field]!==($old[$field]??null)||($new[$field]??null)!==($old[$field]??null))throw new RuntimeException('M44 old block metadata changed: '.$field);
                $targetIds[]=$r['id'];$state=$this->candidate($plan['updates'],'text_blocks',$r,$name,['type'=>$old['type'],'body'=>$old['body']],['type'=>$new['type'],'body'=>$new['body']]);if($state!==null)$rowStates[]=$state;
            }
            $rowStates=array_values(array_unique($rowStates));if(count($rowStates)!==1)throw new RuntimeException('M44 partial/manual owner state.');
            $state=$rowStates[0];$present=0;
            foreach(array_slice($newConfigs,count($oldConfigs),null,true) as $position=>$block){
                $uuid=$this->resolveUuid($name,$block,$position);$global=$this->rows('text_blocks',fn($q)=>$q->where('uuid',$uuid),$lock);
                $fields=['uuid'=>$uuid,'page_id'=>$owner['id'],'page_category_id'=>$owner['page_category_id'],'locale'=>'uk','type'=>$block['type'],'column'=>$block['column'],
                    'heading'=>$block['heading']??null,'css_class'=>$block['css_class']??null,'sort_order'=>$position,'body'=>$block['body'],'level'=>$block['level']??null,'seeder'=>$name];
                if($state==='before'){if($global)throw new RuntimeException('M44 unknown/partial new UUID.');$plan['inserts'][]=['table'=>'text_blocks','seeder'=>$name,'fields'=>$fields,'sha256'=>self::digest($fields)];}
                else{if(count($global)!==1)throw new RuntimeException('M44 inserted row missing/ambiguous.');$r=$global[0];$this->assertOwner($r,$owner,$name,$position);foreach($fields as $f=>$v)if($r[$f]!==$v)throw new RuntimeException('M44 inserted row edited.');if(empty($r['created_at'])||$r['created_at']!==$r['updated_at'])throw new RuntimeException('M44 inserted timestamps changed.');$targetIds[]=$r['id'];$present++;}
            }
            if(count($uk)!==count($oldConfigs)+$present)throw new RuntimeException('M44 unexpected UK rows.');
            $states[]=$state;$plan['pages'][$name]=['page'=>$owner,'category_ancestry'=>$chain,'blocks'=>$rows,'relations'=>$this->relations($owner,$rows,$lock)];
        }
        if(count(array_unique($states))!==1)throw new RuntimeException('M44 partial package; no writes.');
        $plan['state']=$states[0];$plan['protected']=$this->protectedFingerprints($targetIds);ksort($plan['sources']);$plan['sha256']=self::digest($plan);return $plan;
    }
    /** Fingerprint every actual application table, not a historical fixed list or name heuristic. */
    protected function protectedFingerprints(array $targetIds):array
    {
        $schema=$this->db->getDriverName()==='sqlite'?'main':$this->db->getDatabaseName();
        $tables=$this->db->getSchemaBuilder()->getTableListing(schema:$schema,schemaQualified:false);sort($tables);
        $pageIds=$this->db->table('pages')->whereIn('seeder',self::NAMES)->pluck('id')->all();$result=[];
        foreach($tables as $table){
            $columns=$this->db->getSchemaBuilder()->getColumnListing($table);$q=$this->db->table($table);
            foreach(in_array('id',$columns,true)?['id']:$columns as $column)$q->orderBy($column);
            if($table==='text_blocks')$q->whereNotIn('id',$targetIds);
            $hash=hash_init('sha256');$count=0;
            foreach($q->cursor() as $r){
                $row=(array)$r;
                if($table==='pages'&&in_array($row['id'],$pageIds,true))unset($row['text']);
                hash_update($hash,self::digest($row)."\n");$count++;
            }
            $result[$table]=['count'=>$count,'sha256'=>hash_final($hash)];
        }
        return $result;
    }
    private function assertOwner(array $row,array $owner,string $name,int $position):void
    {
        if($row['seeder']!==$name||$row['locale']!=='uk'||$row['page_id']!==$owner['id']||$row['page_category_id']!==$owner['page_category_id']||(int)$row['sort_order']!==$position)throw new RuntimeException('M44 existing row ownership/order differs.');
    }
    /** All relevant M44 sources plus unchanged native primitives are bound into each preview. */
    public static function sourcePaths(array $before,array $package,?string $root=null):array
    {
        $root??=base_path();
        $paths=[Package::BEFORE,Package::SOURCE,Package::MASTER_PATH,Package::MAPPING_PATH,
            'app/Support/M44AuthoredFutureFormsPackage.php','app/Support/M44NativeHtml.php','app/Support/M43NativeHtml.php',
            'app/Support/M26DetailPackage.php','app/Support/TextBlock/TextBlockUuidGenerator.php',
            'resources/css/theory-unified-design.css',
            'app/Services/M44ContentPatch.php','app/Services/M44LocalTargetGuard.php',
            'app/Services/M26ContentPatch.php','app/Services/M11LocalTargetGuard.php','app/Services/PronounContentRepair.php',
            'app/Console/Commands/PatchM44Content.php','tools/diagnostics/run-m44-working-local.php',
            'tools/diagnostics/review-m44-plan.php','tools/diagnostics/prepare-m44-shared-review.php',
            'tools/diagnostics/prepare-m44-proof.php','tools/diagnostics/proof-m44-working-local.php',
            'tools/diagnostics/inspect-m11-local-target.ps1',
            'app/Support/TheorySection.php',
            'resources/views/theory/partials/point-disclosure.blade.php',
            'resources/views/engram/theory/blocks-v3/forms-grid.blade.php',
            'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
            'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
            'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
            'public/js/authored-practice-ui.js',...self::SHARED_VIEWS,...array_column($package['targets'],'path')];
        // Only M44-named versioned source directories, never storage, .env, vendor or build.
        foreach(['app/Support/M44*.php','app/Services/M44*.php','app/Console/Commands/*M44*.php',
            'resources/views/engram/theory/blocks-v3/m44-*.blade.php','public/js/m44-*.js',
            'docs/content/m44-*','tools/diagnostics/*m44*.php','tools/diagnostics/*m44*.cjs'] as $pattern){
            foreach(glob($root.'/'.$pattern)?:[] as $path){
                if(is_file($path))$paths[]=substr(str_replace('\\','/',$path),strlen(str_replace('\\','/',$root))+1);
            }
        }
        foreach($package['targets'] as $target){
            foreach(glob(dirname($root.'/'.$target['path']).'/localizations/*.json')?:[] as $path){
                $paths[]=substr(str_replace('\\','/',$path),strlen(str_replace('\\','/',$root))+1);
            }
        }
        $paths=array_values(array_unique($paths));sort($paths);return $paths;
    }

    /** Only M44 additions, the three definitions and eight reviewed shared files are sync candidates. */
    public static function syncPaths(array $before,array $package,?string $root=null):array
    {
        $explicit=[...self::SHARED_VIEWS,...array_column($package['targets'],'path')];
        return array_values(array_filter(self::sourcePaths($before,$package,$root),
            static fn(string $path):bool=>str_contains($path,'M44')||str_contains($path,'m44')||in_array($path,$explicit,true)));
    }

    /** Keep MySQL preview/apply on the same proved physical local target; fixtures remain SQLite-only. */
    public function connection(?string $expected=null,bool $writing=false):array
    {
        if($this->db->getDriverName()==='mysql'){
            M44LocalTargetGuard::assertConfiguredDatabase($this->db);
            if($this->localTarget!=='gramlyze.loc'||$this->localProof===null){
                throw new RuntimeException('M44 MySQL access requires explicit gramlyze.loc and fresh nonce proof.');
            }
        }
        return parent::connection($expected,$writing);
    }

    /** Apply exact unique text hunks without changing unrelated bytes or ROOT-only changes. */
    public static function projectSharedFragments(string $bytes,array $hunks,bool $rootBytes=false):string
    {
        if(!$rootBytes)$bytes=str_replace("\r\n","\n",$bytes);
        foreach($hunks as $hunk){
            if(array_keys($hunk)!==['before','after']||!is_string($hunk['before'])||!is_string($hunk['after'])||$hunk['before']===''){
                throw new RuntimeException('M44 shared hunk must have exact nonempty before/after fragments.');
            }
            if($rootBytes){
                $normalizedPattern='~'.str_replace("\n","\r?\n",preg_quote($hunk['before'],'~')).'~';
                if(preg_match_all($normalizedPattern,$bytes)!==1)throw new RuntimeException('M44 ROOT shared hunk is missing or ambiguous across line-ending variants.');
            }
            $candidates=[[$hunk['before'],$hunk['after']]];
            if($rootBytes)$candidates[]=[str_replace("\n","\r\n",$hunk['before']),str_replace("\n","\r\n",$hunk['after'])];
            $matches=[];$occurrences=0;
            foreach($candidates as $candidate){$count=substr_count($bytes,$candidate[0]);$occurrences+=$count;if($count>0)$matches[]=$candidate;}
            if($rootBytes&&$occurrences===0){
                // A pre-existing ROOT hunk may itself mix CRLF and LF. Match text
                // exactly while retaining the original bytes of unchanged lines.
                $pattern='~'.str_replace("\n","\r?\n",preg_quote($hunk['before'],'~')).'~';
                if(preg_match_all($pattern,$bytes,$found)!==1)throw new RuntimeException('M44 mixed-EOL shared hunk is missing or ambiguous.');
                $old=$found[0][0];$oldLines=explode("\n",substr($hunk['before'],0,-1));$newLines=explode("\n",substr($hunk['after'],0,-1));
                preg_match_all('/[^\r\n]*(?:\r\n|\n)/',$old,$raw);$raw=$raw[0];
                if(count($raw)!==count($oldLines))throw new RuntimeException('M44 unsupported mixed-EOL hunk.');
                $prefix=0;while($prefix<count($oldLines)&&$prefix<count($newLines)&&$oldLines[$prefix]===$newLines[$prefix])$prefix++;
                $suffix=0;while($suffix<count($oldLines)-$prefix&&$suffix<count($newLines)-$prefix
                    &&$oldLines[count($oldLines)-1-$suffix]===$newLines[count($newLines)-1-$suffix])$suffix++;
                $near=$raw[max(0,$prefix-1)]??"\n";$eol=str_ends_with($near,"\r\n")?"\r\n":"\n";
                $middle=array_slice($newLines,$prefix,count($newLines)-$prefix-$suffix);
                $new=implode('',array_slice($raw,0,$prefix)).($middle?implode($eol,$middle).$eol:'')
                    .($suffix?implode('',array_slice($raw,-$suffix)):'');
                $matches=[[$old,$new]];$occurrences=1;
            }
            if($occurrences!==1)throw new RuntimeException('M44 shared hunk conflicts with ROOT/base or is ambiguous; preserve foreign changes.');
            [$old,$new]=$matches[0];
            $bytes=str_replace($old,$new,$bytes);
        }
        return $bytes;
    }

    /**
     * Read-only proposal for MAIN's explicit sync. Captured BASE -> WT hunks must
     * apply exactly to captured ROOT; callers own the separately authorized write.
     */
    public static function sharedSourceProjection(string $source,string $directory,int $index,string $path):array
    {
        if((self::SHARED_VIEWS[$index]??null)!==$path)throw new RuntimeException('M44 shared source index/path differs.');
        $baseName='m44-shared-base-v1-'.$index.'.bin';$beforeName='source-sync-before-v1/'.$path;
        $base=self::privateSource($directory,$baseName);$before=self::privateSource($directory,$beforeName);
        $canonical=$source.'/'.$path;
        if(!is_file($canonical)||is_link($canonical))throw new RuntimeException('Missing/linked M44 shared WT source.');
        $process=new \Symfony\Component\Process\Process(['git','-c','core.safecrlf=false','diff','--no-index','--no-color',
            '--no-ext-diff','--ignore-cr-at-eol','--unified=1','--',$base,$canonical],$source);
        $process->run();if(!in_array($process->getExitCode(),[0,1],true))throw new RuntimeException('M44 read-only shared diff failed.');
        $hunks=[];$current=null;
        foreach(explode("\n",str_replace("\r\n","\n",$process->getOutput())) as $line){
            if(str_starts_with($line,'@@ ')){
                if($current!==null)$hunks[]=$current;$current=['before'=>'','after'=>''];continue;
            }
            if($current===null)continue;
            if($line==='')continue;
            $prefix=$line[0];$text=substr($line,1)."\n";
            if($prefix===' '){$current['before'].=$text;$current['after'].=$text;}
            elseif($prefix==='-')$current['before'].=$text;
            elseif($prefix==='+')$current['after'].=$text;
            else throw new RuntimeException('Unsupported M44 shared diff (including missing final newline).');
        }
        if($current!==null)$hunks[]=$current;
        $baseBytes=file_get_contents($base);$beforeBytes=file_get_contents($before);$afterBytes=file_get_contents($canonical);
        if(self::projectSharedFragments($baseBytes,$hunks)!==str_replace("\r\n","\n",$afterBytes)){
            throw new RuntimeException('M44 shared hunks do not exactly reconstruct the WT source.');
        }
        try { $projected=self::projectSharedFragments($beforeBytes,$hunks,true); }
        catch (RuntimeException $e) { throw new RuntimeException('M44 ROOT shared hunk conflict: '.$path, previous:$e); }
        return ['bytes'=>$projected,'record'=>['path'=>$path,'before_basename'=>$beforeName,'base_basename'=>$baseName,
            'before_sha256'=>hash('sha256',$beforeBytes),'base_sha256'=>hash('sha256',$baseBytes),
            'root_sha256'=>hash('sha256',$projected),'worktree_sha256'=>hash('sha256',$afterBytes),'hunks'=>$hunks]];
    }

    /** Prove current ROOT retains every captured foreign byte outside exact M44 WT hunks. */
    public static function reviewedSharedSources(string $source,string $directory):array
    {
        $path=self::privateSource($directory,self::SHARED_REVIEW);
        $review=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
        if(($review['version']??null)!==1||($review['base_sha']??null)!==self::ACCEPTED_BASE
            ||array_column($review['files']??[],'path')!==self::SHARED_VIEWS){
            throw new RuntimeException('M44 shared source review base/allowlist differs.');
        }
        foreach($review['files'] as $index=>$record){
            if(($record['before_basename']??null)!=='source-sync-before-v1/'.$record['path']
                ||($record['base_basename']??null)!=='m44-shared-base-v1-'.$index.'.bin'){
                throw new RuntimeException('M44 shared source evidence names differ.');
            }
            $before=self::privateSource($directory,$record['before_basename']);
            $base=self::privateSource($directory,$record['base_basename']);
            $working=M44LocalTargetGuard::ROOT.'/'.$record['path'];$canonical=$source.'/'.$record['path'];
            if(!is_file($working)||!is_file($canonical)||is_link($working)||is_link($canonical)
                ||hash_file('sha256',$before)!==$record['before_sha256']||hash_file('sha256',$base)!==$record['base_sha256']
                ||hash_file('sha256',$working)!==$record['root_sha256']||hash_file('sha256',$canonical)!==$record['worktree_sha256']
                ||self::projectSharedFragments(file_get_contents($base),$record['hunks'])!==str_replace("\r\n","\n",file_get_contents($canonical))
                ||self::projectSharedFragments(file_get_contents($before),$record['hunks'],true)!==file_get_contents($working)){
                throw new RuntimeException('M44 reviewed shared source bytes/hunks changed or foreign ROOT changes were lost.');
            }
        }
        return $review['files'];
    }

    /** Bind every synced ROOT file as well as WT bytes; no unreviewed served-source drift. */
    public static function reviewedWorkingSources(string $source,string $directory,array $before,array $package):array
    {
        $manifest=self::privateSource($directory,self::SYNC_MANIFEST);
        $records=json_decode(file_get_contents($manifest),true,flags:JSON_THROW_ON_ERROR);
        if(array_column($records,'path')!==self::syncPaths($before,$package,$source)){
            throw new RuntimeException('M44 working source manifest differs from the exact sync allowlist.');
        }
        foreach($records as $record){
            $shared=in_array($record['path'],self::SHARED_VIEWS,true);
            $working=M44LocalTargetGuard::ROOT.'/'.$record['path'];$canonical=$source.'/'.$record['path'];
            if(($record['mode']??null)!==($shared?'shared-reviewed-hunks':'finite-sync')
                ||!is_file($working)||!is_file($canonical)||is_link($working)||is_link($canonical)
                ||hash_file('sha256',$working)!==($record['after_sha256']??null)
                ||hash_file('sha256',$canonical)!==($record['worktree_sha256']??null)
                ||(!$shared&&$record['after_sha256']!==$record['worktree_sha256'])){
                throw new RuntimeException('M44 served/canonical source or sync mode changed since review.');
            }
            if(!array_key_exists('before_sha256',$record))throw new RuntimeException('M44 source backup identity missing.');
            if($record['before_sha256']!==null){
                $saved=self::privateSource($directory,'source-sync-before-v1/'.$record['path']);
                if(hash_file('sha256',$saved)!==$record['before_sha256'])throw new RuntimeException('M44 original source backup changed.');
            }
        }
        return $records;
    }

    private static function privateSource(string $directory,string $name):string
    {
        $file=$directory.'/'.$name;$root=realpath($directory);$actual=realpath($file);
        $normal=static fn(string $p):string=>strtolower(str_replace('\\','/',$p));
        if($root===false||$actual===false||is_link($directory)||is_link($file)||!is_file($file)
            ||!str_starts_with($normal($actual),rtrim($normal($root),'/').'/')){
            throw new RuntimeException('Missing/redirected private M44 shared evidence.');
        }
        return $file;
    }
}
