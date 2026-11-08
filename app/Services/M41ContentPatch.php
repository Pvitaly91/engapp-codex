<?php

namespace App\Services;

use App\Support\M41AuthoredTenseComparisonsPackage as Package;
use RuntimeException;

/** Approved author revision: Pages.text and exact target UK type/body only. Never seeds. */
class M41ContentPatch extends M26ContentPatch
{
    public const NAMES=[
        'Database\\Seeders\\Page_V3\\Tenses\\TensesPastSimpleVsPastContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\TensesPresentSimpleVsPresentContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\TensesPresentPerfectVsPastSimpleTheorySeeder',
    ];
    public const ACCEPTED_BASE='ab61310a81f2389353c264fe23266025fe49ffd6';
    public const CATEGORIES=[self::NAMES[0]=>'tenses',self::NAMES[1]=>'tenses',self::NAMES[2]=>'tenses'];
    public const ANCESTRIES=[self::NAMES[0]=>['tenses'],self::NAMES[1]=>['tenses'],self::NAMES[2]=>['tenses']];
    public const SHARED_VIEWS=[
        'resources/views/theory/partials/content-block.blade.php',
        'resources/views/theory/partials/point-detail-fragment.blade.php',
        'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
        'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
        'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php',
        'public/js/authored-practice-ui.js',
        'app/Services/M26ContentPatch.php',
    ];
    public const SHARED_REVIEW='shared-source-review-v1.json';
    protected const ID='m41-authored-tense-comparisons-v1-0-1';
    protected function allowedUpdateFields():array{return ['type','body'];}
    protected function allowedPageUpdateFields():array{return ['text'];}
    protected function localGuard():string{return M41LocalTargetGuard::class;}
    protected function expectedInsertCount():int
    {
        [$before,$package]=Package::load(dirname($this->databasePath));$count=0;
        foreach($package['targets'] as $i=>$target)$count+=count($target['after']['page']['blocks'])-count($before['targets'][$i]['before']['page']['blocks']);
        return $count;
    }
    public function plan(bool $lock=false):array
    {
        $root=dirname($this->databasePath);[$manifest,$package]=Package::load($root);Package::validate($manifest,$package);
        if(array_column($package['targets'],'identity')!==self::NAMES)throw new RuntimeException('M41 exact owner scope differs.');
        $plan=['patch'=>static::ID,'version'=>1,'names'=>self::NAMES,'connection'=>$this->connection(),'sources'=>[],'pages'=>[],'updates'=>[],'inserts'=>[]];
        foreach(['author_master_source','author_correction_source','author_approval_source'] as $key){
            $record=$manifest[$key]??[];$path=$record['path']??'';
            if(!is_file($root.'/'.$path)||is_link($root.'/'.$path)||hash_file('sha256',$root.'/'.$path)!==($record['sha256']??null))throw new RuntimeException('M41 actual author source missing, linked or changed.');
            $plan['sources'][$path]=hash_file('sha256',$root.'/'.$path);
        }
        if($this->localTarget!==null){self::reviewedSharedSources($root,$this->privateDirectory);$plan['sources']['private:'.self::SHARED_REVIEW]=hash_file('sha256',$this->privateDirectory.'/'.self::SHARED_REVIEW);}
        foreach([Package::BEFORE,Package::SOURCE,'app/Support/M41AuthoredTenseComparisonsPackage.php',
            'app/Services/M41ContentPatch.php','app/Services/M26ContentPatch.php','app/Services/M41LocalTargetGuard.php','app/Services/M11LocalTargetGuard.php',
            'app/Console/Commands/PatchM41Content.php','tools/diagnostics/run-m41-working-local.php','tools/diagnostics/inspect-m11-local-target.ps1',
            ...self::SHARED_VIEWS,'resources/views/theory/partials/point-disclosure.blade.php',
            'resources/views/engram/theory/blocks-v3/m41-author-section.blade.php',
            'resources/views/engram/theory/blocks-v3/m41-section-styles.blade.php',
            'resources/views/engram/theory/blocks-v3/m41-practice-ui.blade.php','public/js/m41-practice-ui.js',
            'app/Services/M39PracticeUiPatch.php','app/Services/M40ContentPatch.php'] as $path){
            if(!is_file($root.'/'.$path)||is_link($root.'/'.$path))throw new RuntimeException('M41 code source missing or linked.');
            $plan['sources'][$path]=hash_file('sha256',$root.'/'.$path);
        }
        $states=[];$targetIds=[];
        foreach($package['targets'] as $i=>$target){
            $name=$target['identity'];$before=$manifest['targets'][$i]['before'];$after=$target['after'];$path=$target['path'];
            $bytes=file_get_contents($root.'/'.$path);try{$actual=json_decode($bytes,true,flags:JSON_THROW_ON_ERROR);}catch(\JsonException){throw new RuntimeException('M41 canonical JSON invalid; no writes.');}
            if($actual!==$after)throw new RuntimeException('M41 canonical source differs.');
            $plan['sources'][$path]=hash('sha256',$bytes);
            foreach(glob(dirname($root.'/'.$path).'/localizations/*.json')?:[] as $localePath)$plan['sources'][substr(str_replace('\\','/',$localePath),strlen(str_replace('\\','/',$root))+1)]=hash_file('sha256',$localePath);
            $protectedAfter=$after;$protectedAfter['page']['blocks']=$before['page']['blocks'];
            foreach(['subtitle_html','subtitle_text'] as $field)$protectedAfter['page'][$field]=$before['page'][$field];
            if($protectedAfter!==$before)throw new RuntimeException('M41 source changes protected identity/metadata.');
            $owners=$this->rows('pages',fn($q)=>$q->where('seeder',$name),$lock);if(count($owners)!==1)throw new RuntimeException('M41 missing/ambiguous owner.');
            $owner=$owners[0];$chain=$this->categoryChain($owner['page_category_id'],$lock);
            if(array_column($chain,'slug')!==['tenses']||$owner['slug']!==$before['slug']||$owner['type']!=='theory'||$owner['title']!==$before['page']['title'])throw new RuntimeException('M41 owner/category conflict.');
            $rowStates=[];
            $state=$this->candidate($plan['updates'],'pages',$owner,$name,['text'=>$before['page']['subtitle_text']],['text'=>$after['page']['subtitle_text']]);if($state!==null)$rowStates[]=$state;
            $rows=$this->rows('text_blocks',fn($q)=>$q->where('page_id',$owner['id']),$lock);$uk=array_values(array_filter($rows,fn($r)=>$r['locale']==='uk'));
            $subtitle=['type'=>'subtitle','column'=>'header','heading'=>null,'level'=>$before['page']['subtitle_level']??null,'body'=>$before['page']['subtitle_html'],'uuid_key'=>$before['page']['subtitle_uuid_key']??'subtitle'];
            $newSubtitle=$subtitle;$newSubtitle['body']=$after['page']['subtitle_html'];
            $oldConfigs=[$subtitle,...$before['page']['blocks']];$newConfigs=[$newSubtitle,...$after['page']['blocks']];
            foreach($oldConfigs as $position=>$old){
                $new=$newConfigs[$position]??null;if(!$new)throw new RuntimeException('M41 source removes an existing slot.');
                $uuid=$this->resolveUuid($name,$old,$position);if($this->resolveUuid($name,$new,$position)!==$uuid)throw new RuntimeException('M41 existing UUID changed.');
                $global=$this->rows('text_blocks',fn($q)=>$q->where('uuid',$uuid),$lock);if(count($global)!==1)throw new RuntimeException('M41 old UUID missing or ambiguous.');
                $r=$global[0];$this->assertOwner($r,$owner,$name,$position);
                foreach(['column','heading','level','css_class'] as $field)if($r[$field]!==($old[$field]??null)||($new[$field]??null)!==($old[$field]??null))throw new RuntimeException('M41 old block metadata changed: '.$field);
                $targetIds[]=$r['id'];$state=$this->candidate($plan['updates'],'text_blocks',$r,$name,['type'=>$old['type'],'body'=>$old['body']],['type'=>$new['type'],'body'=>$new['body']]);if($state!==null)$rowStates[]=$state;
            }
            $rowStates=array_values(array_unique($rowStates));if(count($rowStates)!==1)throw new RuntimeException('M41 partial/manual owner state.');
            $state=$rowStates[0];$present=0;
            foreach(array_slice($newConfigs,count($oldConfigs),null,true) as $position=>$block){
                $uuid=$this->resolveUuid($name,$block,$position);$global=$this->rows('text_blocks',fn($q)=>$q->where('uuid',$uuid),$lock);
                $fields=['uuid'=>$uuid,'page_id'=>$owner['id'],'page_category_id'=>$owner['page_category_id'],'locale'=>'uk','type'=>$block['type'],'column'=>$block['column'],
                    'heading'=>$block['heading']??null,'css_class'=>$block['css_class']??null,'sort_order'=>$position,'body'=>$block['body'],'level'=>$block['level']??null,'seeder'=>$name];
                if($state==='before'){if($global)throw new RuntimeException('M41 unknown/partial new UUID.');$plan['inserts'][]=['table'=>'text_blocks','seeder'=>$name,'fields'=>$fields,'sha256'=>self::digest($fields)];}
                else{if(count($global)!==1)throw new RuntimeException('M41 inserted row missing/ambiguous.');$r=$global[0];$this->assertOwner($r,$owner,$name,$position);foreach($fields as $f=>$v)if($r[$f]!==$v)throw new RuntimeException('M41 inserted row edited.');if(empty($r['created_at'])||$r['created_at']!==$r['updated_at'])throw new RuntimeException('M41 inserted timestamps changed.');$targetIds[]=$r['id'];$present++;}
            }
            if(count($uk)!==count($oldConfigs)+$present)throw new RuntimeException('M41 unexpected UK rows.');
            $states[]=$state;$plan['pages'][$name]=['page'=>$owner,'category_ancestry'=>$chain,'blocks'=>$rows,'relations'=>$this->relations($owner,$rows,$lock)];
        }
        if(count(array_unique($states))!==1)throw new RuntimeException('M41 partial package; no writes.');
        $plan['state']=$states[0];$plan['protected']=$this->protectedFingerprints($targetIds);ksort($plan['sources']);$plan['sha256']=self::digest($plan);return $plan;
    }
    protected function protectedFingerprints(array $targetIds):array
    {
        $result=parent::protectedFingerprints($targetIds);$hash=hash_init('sha256');$count=0;
        foreach($this->db->table('pages')->orderBy('id')->cursor() as $r){$data=(array)$r;if(in_array($r->seeder,self::NAMES,true))unset($data['text']);hash_update($hash,self::digest($data)."\n");$count++;}
        $result['pages']=['count'=>$count,'sha256'=>hash_final($hash)];
        $schema=$this->db->getDriverName()==='sqlite'?'main':$this->db->getDatabaseName();$tables=array_filter($this->db->getSchemaBuilder()->getTableListing(schema:$schema,schemaQualified:false),fn($t)=>preg_match('/(?:progress|attempt|review|result|state)/i',$t));sort($tables);
        foreach($tables as $table){if(isset($result[$table]))continue;$q=$this->db->table($table);$columns=$this->db->getSchemaBuilder()->getColumnListing($table);foreach(in_array('id',$columns,true)?['id']:$columns as $c)$q->orderBy($c);$hash=hash_init('sha256');$count=0;foreach($q->cursor() as $r){hash_update($hash,self::digest((array)$r)."\n");$count++;}$result[$table]=['count'=>$count,'sha256'=>hash_final($hash)];}
        return $result;
    }
    private function assertOwner(array $row,array $owner,string $name,int $position):void
    {
        if($row['seeder']!==$name||$row['locale']!=='uk'||$row['page_id']!==$owner['id']||$row['page_category_id']!==$owner['page_category_id']||(int)$row['sort_order']!==$position)throw new RuntimeException('M41 existing row ownership/order differs.');
    }
    public static function reviewedSharedSources(string $source,string $directory):array
    {
        $path=$directory.'/'.self::SHARED_REVIEW;if(!is_file($path)||is_link($path))throw new RuntimeException('M41 exact shared-source review missing.');
        $review=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
        if(($review['base_sha']??null)!==self::ACCEPTED_BASE||array_column($review['files']??[],'path')!==self::SHARED_VIEWS)throw new RuntimeException('M41 shared-source allowlist differs.');
        foreach($review['files'] as $r){if(!preg_match('/^m41-shared-before-v1-[0-6]\.bin$/D',$r['before_basename']))throw new RuntimeException('M41 before source basename invalid.');$old=$directory.'/'.$r['before_basename'];$working=M41LocalTargetGuard::ROOT.'/'.$r['path'];$canonical=$source.'/'.$r['path'];if(is_link($old)||is_link($working)||is_link($canonical)||hash_file('sha256',$old)!==$r['before_sha256']||hash_file('sha256',$working)!==$r['root_sha256']||hash_file('sha256',$canonical)!==$r['worktree_sha256'])throw new RuntimeException('M41 reviewed source bytes changed.');}
        return $review['files'];
    }
}
