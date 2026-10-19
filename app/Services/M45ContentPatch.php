<?php
namespace App\Services;
use App\Support\M45FutureComparisonsPackage as Package;
use RuntimeException;

/** M45 reuses the guarded M26 transaction; exact three UK pages, no banks/progress/locale changes. */
class M45ContentPatch extends M26ContentPatch
{
    public const NAMES=Package::OWNERS;
    public const ACCEPTED_BASE=Package::BASE_SHA;
    public const ANCESTRIES=[self::NAMES[0]=>['maibutni-formy'],self::NAMES[1]=>['maibutni-formy'],self::NAMES[2]=>['maibutni-formy']];
    public const SHARED_VIEWS=[
        'resources/views/theory/show.blade.php',
        'resources/views/theory/partials/content-block.blade.php',
        'resources/views/courses/partials/theory-page-content.blade.php',
        'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php',
        'app/Http/Controllers/PageController.php',
    ];
    protected const ID='m45-compact-future-comparisons-v1';
    protected function allowedUpdateFields():array{return ['type','body'];}
    protected function allowedPageUpdateFields():array{return ['text'];}
    protected function localGuard():string{return M45LocalTargetGuard::class;}
    protected function expectedInsertCount():int{return 3;}
    public function restore(string $backupPath,?string $expectedDatabase=null):array{throw new RuntimeException('M45 restore is outside scope; no writes');}
    public static function assertDefinitionScope(array $before,array $after,array $target,int $index):void
    {
        $name=self::NAMES[$index]??null;
        if($name===null||$target['identity']!==$name||$before['seeder']['class']!==$name
            ||$before['page']['locale']!=='uk'||$before['type']!=='theory'||$before['page']['category']['slug']!=='maibutni-formy'
            ||$target['ancestry']!==['maibutni-formy']||count($before['page']['blocks'])!==7||count($after['page']['blocks'])!==8){
            throw new RuntimeException('M45 definition owner/category/locale/slots differ');
        }
        $protected=$after;$protected['page']['blocks']=$before['page']['blocks'];
        foreach(['subtitle_html','subtitle_text'] as $field){$protected['page'][$field]=$before['page'][$field];}
        if($protected!==$before){throw new RuntimeException('M45 protected metadata changed');}
    }
    public function plan(bool $lock=false):array
    {
        $root=dirname($this->databasePath);[$manifest,$package]=Package::load($root);Package::validate($manifest,$package,$root);
        if(array_column($package['targets'],'identity')!==self::NAMES)throw new RuntimeException('M45 exact owner scope differs.');
        $plan=['patch'=>static::ID,'version'=>1,'names'=>self::NAMES,'connection'=>$this->connection(),'sources'=>[],'pages'=>[],'updates'=>[],'inserts'=>[]];
        Package::authorMaster($manifest,$root);
        if($this->localTarget!==null){
            self::assertServedSources($root,$this->privateDirectory);
            $plan['sources']['private:source-sync.json']=hash_file('sha256',$this->privateDirectory.'/source-sync.json');



        }
        foreach(self::sourcePaths($manifest,$package,$root) as $path){
            if(!is_file($root.'/'.$path)||is_link($root.'/'.$path))throw new RuntimeException('M45 code/author source missing or linked: '.$path);
            $plan['sources'][$path]=hash_file('sha256',$root.'/'.$path);
        }
        $states=[];$targetIds=[];
        foreach($package['targets'] as $i=>$target){
            $name=$target['identity'];$before=$manifest['targets'][$i]['before'];$after=$target['after'];$path=$target['path'];
            self::assertDefinitionScope($before,$after,$target,$i);
            $bytes=file_get_contents($root.'/'.$path);try{$actual=json_decode($bytes,true,flags:JSON_THROW_ON_ERROR);}catch(\JsonException){throw new RuntimeException('M45 canonical JSON invalid; no writes.');}
            if($actual!==$after)throw new RuntimeException('M45 canonical source differs.');
            $plan['sources'][$path]=hash('sha256',$bytes);
            foreach(glob(dirname($root.'/'.$path).'/localizations/*.json')?:[] as $localePath)$plan['sources'][substr(str_replace('\\','/',$localePath),strlen(str_replace('\\','/',$root))+1)]=hash_file('sha256',$localePath);
            $owners=$this->rows('pages',fn($q)=>$q->where('seeder',$name),$lock);if(count($owners)!==1)throw new RuntimeException('M45 missing/ambiguous owner.');
            $owner=$owners[0];$chain=$this->categoryChain($owner['page_category_id'],$lock);
            if(array_column($chain,'slug')!==self::ANCESTRIES[$name]||$owner['slug']!==$before['slug']||$owner['type']!=='theory'||$owner['title']!==$before['page']['title'])throw new RuntimeException('M45 owner/category conflict.');
            $rowStates=[];
            $state=$this->candidate($plan['updates'],'pages',$owner,$name,['text'=>$before['page']['subtitle_text']],['text'=>$after['page']['subtitle_text']]);if($state!==null)$rowStates[]=$state;
            $rows=$this->rows('text_blocks',fn($q)=>$q->where('page_id',$owner['id']),$lock);$uk=array_values(array_filter($rows,fn($r)=>$r['locale']==='uk'));
            $subtitle=['type'=>'subtitle','column'=>'header','heading'=>null,'level'=>$before['page']['subtitle_level']??null,'body'=>$before['page']['subtitle_html'],'uuid_key'=>$before['page']['subtitle_uuid_key']??'subtitle'];
            $newSubtitle=$subtitle;$newSubtitle['body']=$after['page']['subtitle_html'];
            $oldConfigs=[$subtitle,...$before['page']['blocks']];$newConfigs=[$newSubtitle,...$after['page']['blocks']];
            foreach($oldConfigs as $position=>$old){
                $new=$newConfigs[$position]??null;if(!$new)throw new RuntimeException('M45 source removes an existing slot.');
                $oldIdentity=$old;$newIdentity=$new;unset($oldIdentity['type'],$oldIdentity['body'],$newIdentity['type'],$newIdentity['body']);
                if($oldIdentity!==$newIdentity)throw new RuntimeException('M45 existing source block metadata changed.');
                $uuid=$this->resolveUuid($name,$old,$position);if($this->resolveUuid($name,$new,$position)!==$uuid)throw new RuntimeException('M45 existing UUID changed.');
                $global=$this->rows('text_blocks',fn($q)=>$q->where('uuid',$uuid),$lock);if(count($global)!==1)throw new RuntimeException('M45 old UUID missing or ambiguous.');
                $r=$global[0];$this->assertOwner($r,$owner,$name,$position);
                foreach(['column','heading','level','css_class'] as $field)if($r[$field]!==($old[$field]??null)||($new[$field]??null)!==($old[$field]??null))throw new RuntimeException('M45 old block metadata changed: '.$field);
                $targetIds[]=$r['id'];$state=$this->candidate($plan['updates'],'text_blocks',$r,$name,['type'=>$old['type'],'body'=>$old['body']],['type'=>$new['type'],'body'=>$new['body']]);if($state!==null)$rowStates[]=$state;
            }
            $rowStates=array_values(array_unique($rowStates));if(count($rowStates)!==1)throw new RuntimeException('M45 partial/manual owner state.');
            $state=$rowStates[0];$present=0;
            foreach(array_slice($newConfigs,count($oldConfigs),null,true) as $position=>$block){
                $uuid=$this->resolveUuid($name,$block,$position);$global=$this->rows('text_blocks',fn($q)=>$q->where('uuid',$uuid),$lock);
                $fields=['uuid'=>$uuid,'page_id'=>$owner['id'],'page_category_id'=>$owner['page_category_id'],'locale'=>'uk','type'=>$block['type'],'column'=>$block['column'],
                    'heading'=>$block['heading']??null,'css_class'=>$block['css_class']??null,'sort_order'=>$position,'body'=>$block['body'],'level'=>$block['level']??null,'seeder'=>$name];
                if($state==='before'){if($global)throw new RuntimeException('M45 unknown/partial new UUID.');$plan['inserts'][]=['table'=>'text_blocks','seeder'=>$name,'fields'=>$fields,'sha256'=>self::digest($fields)];}
                else{if(count($global)!==1)throw new RuntimeException('M45 inserted row missing/ambiguous.');$r=$global[0];$this->assertOwner($r,$owner,$name,$position);foreach($fields as $f=>$v)if($r[$f]!==$v)throw new RuntimeException('M45 inserted row edited.');if(empty($r['created_at'])||$r['created_at']!==$r['updated_at'])throw new RuntimeException('M45 inserted timestamps changed.');$targetIds[]=$r['id'];$present++;}
            }
            if(count($uk)!==count($oldConfigs)+$present)throw new RuntimeException('M45 unexpected UK rows.');
            $states[]=$state;$plan['pages'][$name]=['page'=>$owner,'category_ancestry'=>$chain,'blocks'=>$rows,'relations'=>$this->relations($owner,$rows,$lock)];
        }
        if(count(array_unique($states))!==1)throw new RuntimeException('M45 partial package; no writes.');
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
        if($row['seeder']!==$name||$row['locale']!=='uk'||$row['page_id']!==$owner['id']||$row['page_category_id']!==$owner['page_category_id']||(int)$row['sort_order']!==$position)throw new RuntimeException('M45 existing row ownership/order differs.');
    }

    public function connection(?string $expected=null,bool $writing=false):array
    {
        if($this->db->getDriverName()==='mysql'){
            M45LocalTargetGuard::assertConfiguredDatabase($this->db);
            if($this->localTarget!=='gramlyze.loc'||$this->localProof===null){throw new RuntimeException('M45 requires explicit local target and fresh nonce proof');}
        }
        return parent::connection($expected,$writing);
    }
    public static function sourcePaths(array $before,array $package,?string $root=null):array
    {
        $root??=base_path();
        $paths=[Package::MASTER_PATH,Package::BEFORE,Package::SOURCE,
            'app/Support/M45FutureComparisonsPackage.php','app/Services/M45ContentPatch.php','app/Services/M45LocalTargetGuard.php','app/Console/Commands/PatchM45Content.php',
            'app/Services/M26ContentPatch.php','app/Services/M11LocalTargetGuard.php','app/Support/M26DetailPackage.php',
            'app/Support/TextBlock/TextBlockUuidGenerator.php','app/Support/M43NativeHtml.php','resources/css/theory-unified-design.css',
            'public/js/authored-practice-ui.js','public/js/m45-practice-ui.js',
            ...self::SHARED_VIEWS,...array_column($package['targets'],'path')];
        foreach(glob($root.'/resources/views/engram/theory/blocks-v3/m45-*.blade.php')?:[] as $path){
            $paths[]=substr(str_replace('\\','/',$path),strlen(str_replace('\\','/',$root))+1);
        }
        foreach($package['targets'] as $target)foreach(glob(dirname($root.'/'.$target['path']).'/localizations/*.json')?:[] as $path){
            $paths[]=substr(str_replace('\\','/',$path),strlen(str_replace('\\','/',$root))+1);
        }
        $paths=array_values(array_unique($paths));sort($paths);return $paths;
    }
    private static function assertServedSources(string $source,string $directory):void
    {
        $file=$directory.'/source-sync.json';
        if(!is_file($file)||is_link($file)){throw new RuntimeException('M45 reviewed source sync missing');}
        $sync=json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
        if($sync['base_sha']!==self::ACCEPTED_BASE){throw new RuntimeException('M45 wrong source base');}
        [$before,$package]=Package::load($source);
        $expected=array_values(array_filter(self::sourcePaths($before,$package,$source),
            fn($path)=>str_contains($path,'M45')||str_contains($path,'m45')
                ||in_array($path,self::SHARED_VIEWS,true)||in_array($path,array_column($package['targets'],'path'),true)));
        $actual=array_column($sync['files'],'path');sort($actual);sort($expected);
        if($actual!==$expected){throw new RuntimeException('M45 source sync allowlist differs');}
        foreach($sync['files'] as $record){
            $root=M45LocalTargetGuard::ROOT.'/'.$record['path'];$wt=$source.'/'.$record['path'];
            if(!is_file($root)||is_link($root)||!is_file($wt)||is_link($wt)
                ||hash_file('sha256',$root)!==$record['root_after_sha256']||hash_file('sha256',$wt)!==$record['worktree_sha256']){
                throw new RuntimeException('M45 served source drift: '.$record['path']);
            }
        }
    }
}
