<?php

namespace App\Services;

use App\Support\M79PracticeCriteriaRevision as Revision;
use App\Support\M79QuestionTypesCompletion as Package;
use Illuminate\Database\Connection;
use RuntimeException;

/** Fresh-proof, exclusive-backup two-body successor. No insert, seed, shared-code or Page writes. */
class M79PracticeCriteriaPatch extends M26ContentPatch
{
    public const NAMES=[Package::OWNERS[0],Package::OWNERS[2]];
    public const BASELINE_FILE='m79-practice-criteria-before-v1.json';
    public const BASELINE_SHA='43b480a36883d551a6b5f05320c228f1fed8e79584a6d5b7a0eb22fadb0d5b09';
    public const ORIGINAL_POSTCONDITIONS_SHA='c9c8f0d1b42d5a0158555ef29f3843c66a448cbcacb963348f4f46c1e5c5fb67';
    protected const ID='m79-practice-visible-criteria-v1.0.1';

    protected function localGuard(): string { return M79LocalTargetGuard::class; }
    protected function expectedInsertCount(): int { return 0; }
    protected function allowedUpdateFields(): array { return ['body']; }
    protected function allowedPageUpdateFields(): array { return []; }
    public function restore(string $backupPath,?string $expectedDatabase=null): array
    {
        throw new RuntimeException('M79 criteria restore is outside this approved correction scope.');
    }

    public function connection(?string $expected=null,bool $writing=false): array
    {
        if ($this->db->getDriverName()==='mysql') {
            M79LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget!=='gramlyze.loc'||$this->localProof===null) { throw new RuntimeException('M79 criteria requires gramlyze.loc and a fresh nonce proof.'); }
        } elseif ($this->db->getDriverName()!=='sqlite'||!app()->environment('testing')) {
            throw new RuntimeException('M79 criteria only permits isolated testing SQLite or proven local MySQL.');
        }
        return parent::connection($expected,$writing);
    }

    /** Full-table hashes normalize only two exact successor bodies back to their independently captured predecessors. */
    public static function fingerprints(Connection $db,array $normalizations=[]): array
    {
        $tables=$db->getSchemaBuilder()->getTableListing(schema:$db->getDriverName()==='sqlite'?'main':$db->getDatabaseName(),schemaQualified:false);
        sort($tables); $map=[]; $seen=[]; $result=[];
        foreach ($normalizations as $row) {
            if (!in_array($row['id']??null,Revision::ROW_IDS,true)||isset($map[$row['id']])) { throw new RuntimeException('M79 criteria exact normalization target differs.'); }
            $map[$row['id']]=$row;
        }
        foreach ($tables as $table) {
            $columns=$db->getSchemaBuilder()->getColumnListing($table); $query=$db->table($table);
            foreach (in_array('id',$columns,true)?['id']:$columns as $column) { $query->orderBy($column); }
            $hash=hash_init('sha256'); $count=0;
            foreach ($query->cursor() as $record) {
                $row=(array)$record; $id=$row['id']??null;
                if ($table==='text_blocks'&&isset($map[$id])) {
                    if (($row['body']??null)!==$map[$id]['after']) { throw new RuntimeException('M79 criteria normalization requires exact applied body.'); }
                    $row['body']=$map[$id]['before']; $seen[$id]=true;
                }
                hash_update($hash,self::digest($row)."\n"); ++$count;
            }
            $result[$table]=['count'=>$count,'sha256'=>hash_final($hash)];
        }
        if (count($seen)!==count($map)) { throw new RuntimeException('M79 criteria exact normalization row disappeared.'); }
        return $result;
    }

    protected function evidence(): array
    {
        $root=dirname($this->databasePath); $e=Revision::load($root);
        $baseline=Revision::pinned($this->privateDirectory.'/'.self::BASELINE_FILE,self::BASELINE_SHA);
        if (($baseline['schema_version']??null)!==1||($baseline['kind']??null)!=='M79_practice_criteria_before'
            ||($baseline['target']??null)!=='gramlyze.loc'||($baseline['served_root']??null)!==M79LocalTargetGuard::ROOT
            ||($baseline['row_ids']??null)!==Revision::ROW_IDS||count($baseline['schema']??[])!==46
            ||count($baseline['sql_before']??[])!==46||array_column($baseline['rows']??[],'id')!==Revision::ROW_IDS
            ||($baseline['original_postconditions_sha256']??null)!==self::ORIGINAL_POSTCONDITIONS_SHA
            ||($baseline['db_writes']??null)!==0||($baseline['http_requests']??null)!==0) {
            throw new RuntimeException('M79 criteria independently captured current-v1 baseline differs.');
        }
        if ($this->db->getDriverName()==='mysql'&&M79LocalTargetGuard::publicIdentity($this->db,$root.'/public')!==$baseline['identity']) {
            throw new RuntimeException('M79 criteria runtime differs from independent local capture.');
        }
        $sources=$e['sources']; $sources['private:'.self::BASELINE_FILE]=self::BASELINE_SHA;
        foreach (['app/Support/M79PracticeCriteriaRevision.php','app/Services/M79PracticeCriteriaPatch.php',
            'app/Console/Commands/ContentPatchM79Criteria.php','app/Support/M79QuestionTypesCompletion.php',
            'app/Services/M79LocalTargetGuard.php','app/Services/TheoryLocalReleaseGuard.php',
            'app/Services/M26ContentPatch.php','app/Services/PronounContentRepair.php'] as $path) {
            $sources[$path]=Revision::fileHash($root.'/'.$path);
        }
        return $e+['baseline'=>$baseline,'source_pins'=>$sources];
    }

    public function plan(bool $lock=false): array
    {
        $connection=$this->connection(); $e=$this->evidence(); $baseline=$e['baseline'];
        foreach ($baseline['schema'] as $table=>$columns) {
            if ($this->db->getSchemaBuilder()->getColumns($table)!==$columns) { throw new RuntimeException('M79 criteria independent schema drift.'); }
        }
        $updates=[]; $states=[]; $pages=[]; $normalizations=[];
        foreach ($e['transitions'] as $n=>$t) {
            $rows=$this->rows('text_blocks',static fn($q)=>$q->where('id',$t['id']),$lock);
            if (count($rows)!==1) { throw new RuntimeException('M79 criteria exact practice row missing/repeated.'); }
            $row=$rows[0]; $old=$baseline['rows'][$n];
            foreach (['id'=>$t['id'],'uuid'=>$t['uuid'],'page_id'=>$t['page_id'],'page_category_id'=>22,
                'locale'=>'uk','type'=>'practice-set','column'=>'left','sort_order'=>12,'level'=>$t['level'],
                'seeder'=>$t['identity'],'body'=>$t['before']] as $field=>$value) {
                if (!array_key_exists($field,$old)||$old[$field]!==$value) { throw new RuntimeException('M79 criteria independently captured target identity/body differs.'); }
            }
            $state=$row['body']===$t['before']?'before':($row['body']===$t['after']?'after':null);
            if ($state===null) { throw new RuntimeException('M79 criteria row is neither exact predecessor nor successor.'); }
            $expected=array_replace($old,['body'=>$t[$state==='before'?'before':'after']]);
            $a=$row; $b=$expected; ksort($a); ksort($b);
            if ($a!==$b) { throw new RuntimeException('M79 criteria target metadata/timestamps or non-body fields drifted.'); }
            $states[]=$state;
            if ($state==='before') { $this->candidate($updates,'text_blocks',$row,$t['identity'],['body'=>$t['before']],['body'=>$t['after']]); }
            else { $normalizations[]=$t; }
            $page=$this->rows('pages',static fn($q)=>$q->where('id',$t['page_id']),$lock);
            if (count($page)!==1||$page[0]['seeder']!==$t['identity']||$page[0]['page_category_id']!==22) { throw new RuntimeException('M79 criteria exact owner Page differs.'); }
            $pages[$t['identity']]=['page'=>$page[0],'blocks'=>$this->rows('text_blocks',static fn($q)=>$q->where('page_id',$t['page_id']),$lock)];
        }
        if (count(array_unique($states))!==1) { throw new RuntimeException('M79 criteria partial package; no writes.'); }
        $protected=self::fingerprints($this->db,$normalizations);
        if ($protected!==$baseline['sql_before']) { throw new RuntimeException('M79 criteria all-table independent baseline differs.'); }
        $sources=$e['source_pins']; $root=dirname($this->databasePath);
        foreach ($sources as $path=>$sha) {
            $file=str_starts_with($path,'private:')?$this->privateDirectory.'/'.substr($path,8):$root.'/'.$path;
            if (!hash_equals($sha,Revision::fileHash($file))) { throw new RuntimeException('M79 criteria source/evidence drift.'); }
        }
        ksort($sources);
        $plan=['patch'=>static::ID,'version'=>1,'names'=>self::NAMES,'connection'=>$connection,'state'=>$states[0],
            'sources'=>$sources,'pages'=>$pages,'updates'=>$updates,'inserts'=>[],'protected'=>$protected];
        $plan['sha256']=self::digest($plan); return $plan;
    }
}
