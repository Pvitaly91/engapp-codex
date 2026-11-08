<?php

namespace App\Services;

use App\Support\M76PronounRelations as Package;
use Illuminate\Database\Connection;
use RuntimeException;

/** Exact one-body 1.0.0 -> 1.0.1 successor; original M76 evidence remains immutable. */
class M76AnchorPatch extends M26ContentPatch
{
    public const OWNER = Package::OWNERS[2];
    public const NAMES = [self::OWNER];
    public const ROW_ID = 4077;
    public const PREVIOUS_PATCH = 'database/content-patches/m76-relative-each-reciprocal.v1.json';
    public const PREVIOUS_PATCH_SHA = 'ab79812b334f489898f2820ed6115b6b5d01372135169a0310063a54b845e3d3';
    public const PREVIOUS_MASTER = 'docs/content/m76-relative-each-reciprocal.v1.0.0.json';
    public const PREVIOUS_MASTER_SHA = 'acd759984a5e3129d46137ab8f99227f3944311da8b8617687f94308c27f681b';
    public const REVISION = 'docs/content/m76-anchor-revision.v1.0.1.json';
    public const REVISION_SHA = '9d30fa87cd71ba53730f7f48d77eec73fb66c245f4149db2a296ee64a8b1bbfb';
    public const BASELINE_FILE = 'final-readonly-v1.json';
    public const BASELINE_SHA = '214ddb282ee6daad426c84ccc3cb9e9b26d12423a0ace44efe33aa2dab21b053';
    public const SYNC_FILE = 'source-sync-v2.json';
    public const SYNC_SHA = 'b70e89462582e1a1938b317672aacd5df5da23d1a7abdb9c0f553d98619e568a';
    public const CODE_PATHS = [
        'app/Services/M76AnchorPatch.php', 'app/Console/Commands/ContentPatchM76Anchor.php',
        'tools/diagnostics/m76-anchor-postconditions.php',
    ];
    public const HTML_BEFORE = '<strong>Each other</strong>';
    public const HTML_AFTER = '<strong id="block-4077">Each other</strong>';
    protected const ID = 'm76-anchor-revision-v1.0.1';

    protected function localGuard(): string { return M76LocalTargetGuard::class; }
    protected function expectedInsertCount(): int { return 0; }
    protected function allowedPageUpdateFields(): array { return []; }
    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        throw new RuntimeException('M76 anchor successor restore is outside this approved scope.');
    }
    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            M76LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('M76 anchor requires gramlyze.loc and fresh nonce proof.');
            }
        } elseif ($this->db->getDriverName() !== 'sqlite' || !app()->environment('testing')) {
            throw new RuntimeException('M76 anchor supports only isolated testing SQLite or proven local MySQL.');
        }
        return parent::connection($expected, $writing);
    }
    public static function fileHash(string $file): string
    {
        if (!is_file($file) || is_link($file)) { throw new RuntimeException('M76 anchor exact source/evidence missing or linked.'); }
        return hash_file('sha256', $file);
    }
    protected function pinned(string $file, string $sha): array
    {
        if (!hash_equals($sha, self::fileHash($file))) { throw new RuntimeException('M76 anchor frozen source/evidence changed.'); }
        return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    }

    /** Pure finite source proof. No arbitrary markup, attribute, text or second block is authorized. */
    public static function exactTransition(array $old, array $next, array $oldMaster, array $newMaster): array
    {
        $a=$old['targets'][2]['after']['page']['blocks'][0]['body']??null;
        $b=$next['targets'][2]['after']['page']['blocks'][0]['body']??null;
        if (!is_string($a)||!is_string($b)||$a===$b) { throw new RuntimeException('M76 anchor exact predecessor/successor body absent.'); }
        $before=json_decode($a,true,flags:JSON_THROW_ON_ERROR);$after=json_decode($b,true,flags:JSON_THROW_ON_ERROR);
        $intro=$before['intro']??null;
        if (!is_string($intro)||substr_count($intro,self::HTML_BEFORE)!==1
            ||str_contains($intro,'id="block-4077"')) { throw new RuntimeException('M76 anchor original first strong differs.'); }
        $projected=$before;$projected['intro']=str_replace(self::HTML_BEFORE,self::HTML_AFTER,$intro);
        if ($after!==$projected||strip_tags($after['intro'])!==strip_tags($intro)
            ||substr_count($after['intro'],'id="block-4077"')!==1) { throw new RuntimeException('M76 anchor changes more than its single invisible fragment ID.'); }
        $normalized=$next;$normalized['targets'][2]['after']['page']['blocks'][0]['body']=$a;
        if ($normalized!==$old) { throw new RuntimeException('M76 anchor successor changes another source field.'); }
        if (($oldMaster['version']??null)!=='1.0.0'||($newMaster['version']??null)!=='1.0.1'
            ||($oldMaster['lessons'][2]['blocks'][0]['data']??null)!==$before) {
            // The serialized body additionally owns the already accepted native provenance marker.
            $withoutMarker=$before;unset($withoutMarker['m76_native_v1']);
            if (($oldMaster['version']??null)!=='1.0.0'||($newMaster['version']??null)!=='1.0.1'
                ||($oldMaster['lessons'][2]['blocks'][0]['data']??null)!==$withoutMarker) {
                throw new RuntimeException('M76 anchor master predecessor is not its exact serialized hero.');
            }
        }
        $normalizedMaster=$newMaster;$normalizedMaster['version']='1.0.0';
        $normalizedMaster['lessons'][2]['blocks'][0]['data']['intro']=$intro;
        if(($oldMaster['editorial_review']['expected_http_metadata']['metadata'][2]['hero_intro_sha256']??null)!==hash('sha256',$intro)
            ||($newMaster['editorial_review']['expected_http_metadata']['metadata'][2]['hero_intro_sha256']??null)!==hash('sha256',$after['intro'])){
            throw new RuntimeException('M76 anchor exact old/new raw intro hashes differ.');
        }
        $normalizedMaster['editorial_review']['expected_http_metadata']['metadata'][2]['hero_intro_sha256']=hash('sha256',$intro);
        if ($normalizedMaster!==$oldMaster
            ||($newMaster['lessons'][2]['blocks'][0]['data']['intro']??null)!==$after['intro']) {
            throw new RuntimeException('M76 anchor master changes beyond version and one intro ID.');
        }
        return ['before'=>$a,'after'=>$b];
    }

    public static function syncPaths(array $patch,array $contract): array
    {
        return M76ContentPatch::syncPaths($patch,$contract);
    }

    /** Fixed public sources plus independent original/final private evidence, never a fresh fabricated BEFORE. */
    protected function evidence(): array
    {
        $root=dirname($this->databasePath);
        $revision=$this->pinned($root.'/'.self::REVISION,self::REVISION_SHA);
        $baseline=$this->pinned($this->privateDirectory.'/'.self::BASELINE_FILE,self::BASELINE_SHA);
        $captured=$this->pinned($this->privateDirectory.'/'.M76ContentPatch::BEFORE_FILE,M76ContentPatch::BEFORE_SHA);
        $old=$this->pinned($root.'/'.self::PREVIOUS_PATCH,self::PREVIOUS_PATCH_SHA);
        $oldMaster=$this->pinned($root.'/'.self::PREVIOUS_MASTER,self::PREVIOUS_MASTER_SHA);
        [$before,$next,$master,$contract]=Package::load($root);Package::validate($before,$next,$master,$contract);
        $transition=self::exactTransition($old,$next,$oldMaster,$master);
        $rows=array_values(array_filter($captured['pages'][self::OWNER]['blocks']??[],static fn($r)=>$r['id']===self::ROW_ID));
        if (count($rows)!==1) { throw new RuntimeException('M76 anchor independently captured original row is absent or repeats.'); }
        $row=array_replace($rows[0],['type'=>'hero','heading'=>null,'body'=>$transition['before']]);
        $expected=['table'=>'text_blocks','id'=>self::ROW_ID,'uuid'=>$row['uuid'],'page_id'=>127,'page_category_id'=>20,
            'seeder'=>self::OWNER,'locale'=>'uk','type'=>'hero','column'=>'header','sort_order'=>1,'level'=>null,
            'before_body_sha256'=>hash('sha256',$transition['before']),'after_body_sha256'=>hash('sha256',$transition['after']),
            'allowed_field'=>'body','html_before'=>self::HTML_BEFORE,'html_after'=>self::HTML_AFTER];
        if (($revision['schema_version']??null)!==1||($revision['package']??null)!=='m76-anchor-revision'
            ||($revision['version']??null)!=='1.0.1'||($revision['status']??null)!=='frozen'
            ||($revision['previous']??null)!==['patch'=>self::PREVIOUS_PATCH,'patch_sha256'=>self::PREVIOUS_PATCH_SHA,
                'master'=>self::PREVIOUS_MASTER,'master_sha256'=>self::PREVIOUS_MASTER_SHA]
            ||($revision['active']??null)!==['patch'=>Package::SOURCE,'patch_sha256'=>Package::SOURCE_SHA,
                'master'=>Package::MASTER,'master_sha256'=>Package::MASTER_SHA]
            ||($revision['transition']??null)!==$expected
            ||($revision['baseline']??null)!==['file'=>self::BASELINE_FILE,'sha256'=>self::BASELINE_SHA]
            ||($baseline['independent_before_sha256']??null)!==M76ContentPatch::BEFORE_SHA
            ||($baseline['all_old_rows_and_timestamps_exact']??null)!==true||count($baseline['sql_after']??[])!==46
            ||($captured['sql_before']??null)!==($captured['sql_after_http']??null)) {
            throw new RuntimeException('M76 anchor immutable predecessor/final baseline/revision lineage differs.');
        }
        $sources=[self::PREVIOUS_PATCH=>self::PREVIOUS_PATCH_SHA,self::PREVIOUS_MASTER=>self::PREVIOUS_MASTER_SHA,
            self::REVISION=>self::REVISION_SHA,'private:'.self::BASELINE_FILE=>self::BASELINE_SHA,
            'private:'.M76ContentPatch::BEFORE_FILE=>M76ContentPatch::BEFORE_SHA];
        foreach(Package::profile()['artifacts'] as $path=>$sha){$sources[$path]=$sha;}
        foreach(M76ContentPatch::sourcePaths($next,$contract,$root) as $path){$sources[$path]=self::fileHash($root.'/'.$path);}
        foreach(self::CODE_PATHS as $path){$sources[$path]=self::fileHash($root.'/'.$path);}
        $syncPath=$this->privateDirectory.'/'.self::SYNC_FILE;$syncHash=self::SYNC_SHA;
        $sync=$this->pinned($syncPath,$syncHash);$paths=array_column($sync['files']??[],'path');sort($paths);
        if ($paths!==self::syncPaths($next,$contract)||($sync['base_sha']??null)!==Package::BASE_SHA
            ||($sync['served_root']??null)!==M76LocalTargetGuard::ROOT
            ||($sync['source_worktree']??null)!==M76ContentPatch::SOURCE_WORKTREE
            ||($sync['visual_before']??null)!==['file'=>M76ContentPatch::BEFORE_FILE,'sha256'=>M76ContentPatch::BEFORE_SHA]) {
            throw new RuntimeException('M76 anchor exact source-sync scope differs.');
        }
        foreach($sync['files'] as $file){
            $path=$file['path'];
            if (!hash_equals($file['root_after_sha256'],self::fileHash($root.'/'.$path))
                ||!hash_equals($file['worktree_sha256'],self::fileHash(M76ContentPatch::SOURCE_WORKTREE.'/'.$path))) { throw new RuntimeException('M76 anchor ROOT/WT synchronization drift.'); }
            $sources[$path]=$file['root_after_sha256'];
        }
        foreach(self::CODE_PATHS as $path){
            if(!hash_equals($sources[$path],self::fileHash(M76ContentPatch::SOURCE_WORKTREE.'/'.$path))){
                throw new RuntimeException('M76 anchor own successor code ROOT/WT differs.');
            }
        }
        foreach(['app/Services/M74BankCompletionPatch.php','app/Services/TheoryLocalReleaseGuard.php'] as $path){
            $sources[$path]=self::fileHash($root.'/'.$path);
        }
        $sources['private:'.self::SYNC_FILE]=$syncHash;
        return $transition+['row_before'=>$row,'baseline'=>$baseline,'schema'=>$captured['schema'],'sources'=>$sources];
    }

    /** Every original table/field is protected; only the proven body is normalized to its predecessor. */
    public static function fingerprints(Connection $db,?array $bodyBefore=null): array
    {
        return M74BankCompletionPatch::fingerprints($db,[],$bodyBefore);
    }

    public function plan(bool $lock=false): array
    {
        $connection=$this->connection();$e=$this->evidence();
        $rows=$this->rows('text_blocks',static fn($q)=>$q->where('id',self::ROW_ID),$lock);
        if(count($rows)!==1){throw new RuntimeException('M76 anchor exact one target row required.');}$row=$rows[0];
        $state=$row['body']===$e['before']?'before':($row['body']===$e['after']?'after':null);
        if($state===null){throw new RuntimeException('M76 anchor target is neither exact predecessor nor successor.');}
        $expected=array_replace($e['row_before'],['body'=>$state==='before'?$e['before']:$e['after']]);
        $a=$row;$b=$expected;ksort($a);ksort($b);
        if($a!==$b){throw new RuntimeException('M76 anchor target identity, metadata or timestamps differ.');}
        foreach($e['schema'] as $table=>$schema){
            if($this->db->getSchemaBuilder()->getColumns($table)!==$schema){throw new RuntimeException('M76 anchor captured schema drift.');}
        }
        $normalize=$state==='after'?['id'=>self::ROW_ID,'before'=>$e['before'],'after'=>$e['after']]:null;
        $protected=self::fingerprints($this->db,$normalize);
        if(count($protected)!==46||$protected!==$e['baseline']['sql_after']){throw new RuntimeException('M76 anchor all-table predecessor differs.');}
        $page=$this->rows('pages',static fn($q)=>$q->where('id',127),$lock);
        if(count($page)!==1||$page[0]['seeder']!==self::OWNER||$page[0]['page_category_id']!==20){throw new RuntimeException('M76 anchor exact owner Page differs.');}
        $blocks=$this->rows('text_blocks',static fn($q)=>$q->where('page_id',127),$lock);
        $updates=[];if($state==='before'){$this->candidate($updates,'text_blocks',$row,self::OWNER,['body'=>$e['before']],['body'=>$e['after']]);}
        $root=dirname($this->databasePath);
        foreach($e['sources'] as $path=>$sha){
            $file=str_starts_with($path,'private:')?$this->privateDirectory.'/'.substr($path,8):$root.'/'.$path;
            if(!hash_equals($sha,self::fileHash($file))){throw new RuntimeException('M76 anchor source drift.');}
        }
        $sources=$e['sources'];ksort($sources);
        $plan=['patch'=>static::ID,'version'=>1,'names'=>self::NAMES,'connection'=>$connection,'state'=>$state,
            'sources'=>$sources,'pages'=>[self::OWNER=>['page'=>$page[0],'blocks'=>$blocks]],
            'updates'=>$updates,'inserts'=>[],'protected'=>$protected];
        $plan['sha256']=self::digest($plan);return $plan;
    }
}

