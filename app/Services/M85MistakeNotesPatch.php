<?php

namespace App\Services;

use App\Support\M85VerbToBe as Package;
use RuntimeException;

/** Two existing body cells only; all mutations belong to the unchanged M26 writer. */
class M85MistakeNotesPatch extends M26ContentPatch
{
    public const NAMES = Package::OWNERS;
    public const MANIFEST = 'docs/content/m85-mistake-notes.v1.0.1.json';
    public const MANIFEST_SHA = 'afc07bab6a860264fd34542f43fe25b0763059648da681af5c9ae9ad5023c8ce';
    public const BASELINE = 'final-readonly-v1.json';
    public const BASELINE_SHA = '09384892849ec9cbe400b1dff952094cbeb0e73a27931b7cc4e395acdb95a199';
    public const CURRENT_BEFORE = 'mistake-notes-before-v1.json';
    public const SYNC_RECEIPT = 'source-sync-mistake-notes-v1.json';
    public const SOURCE_BEFORE = 'independent-source-before-v1.json';
    public const SOURCE_BEFORE_SHA = '7f554bebc5c15ea86bef65592d35da75cc1fdbec3631e1f3060de55926508fe1';
    public const SOURCE_FIXTURE = 'tests/Fixtures/m85-owned-source-hunks.json';
    public const SOURCE_FIXTURE_SHA = 'e7debd4fd99d1c930aeefa52f7c78fc1d50d0bfb00d845571ae9252e52185352';
    public const SCHEMA_BEFORE = 'schema-before-v1.json';
    public const SCHEMA_BEFORE_SHA = '7458fafc390d615a1b11ee7467b3004a51f1b50e12032426bf5b716de0d17174';
    public const PREVIOUS_SOURCE = 'database/content-patches/m85-verb-to-be.v1.json';
    public const PREVIOUS_SOURCE_SHA = 'c0459e1178de381afd6bcf0584cb718330cbc70de5dd5620fab21dc5079df972';
    public const FOREIGN_FORMATTER_PATH = 'app/Support/AnswerOptionCase.php';
    public const TARGETS = [
        ['owner_index'=>4,'source_index'=>5,'id'=>1313,'uuid'=>'6aa083a7-7ee7-5bf7-b6bd-c79e5ab3fafe'],
        ['owner_index'=>5,'source_index'=>4,'id'=>1288,'uuid'=>'15659f74-548b-5f63-a545-230ed357ce14'],
    ];
    public const OWNED_PATHS = [
        'app/Services/M85MistakeNotesPatch.php',
        'tools/diagnostics/m85-mistake-notes-patch.php',
        'tools/diagnostics/m85-mistake-notes-before.php',
        'tools/diagnostics/m85-mistake-notes-source-sync.php',
        'tools/diagnostics/m85-mistake-notes-review.php',
        'tools/diagnostics/m85-mistake-notes-postconditions.php',
        'tools/diagnostics/m85-mistake-notes-old-consumers.php',
        'tests/Feature/M85MistakeNotesPatchTest.php',
        'tests/Unit/M85MistakeNotesSourceProfileTest.php',
    ];
    protected const ID = 'm85-mistake-notes-1.0.0-to-1.0.1';
    private array $bodies = [];

    protected function localGuard(): string { return M85LocalTargetGuard::class; }
    protected function expectedInsertCount(): int { return 0; }
    protected function allowedUpdateFields(): array { return ['body']; }
    public function restore(string $backupPath, ?string $expectedDatabase = null): array
    {
        throw new RuntimeException('M85 note repair has no restore authority.');
    }
    public function connection(?string $expected = null, bool $writing = false): array
    {
        if ($this->db->getDriverName() === 'mysql') {
            M85LocalTargetGuard::assertConfiguredDatabase($this->db);
            if ($this->localTarget !== 'gramlyze.loc' || $this->localProof === null) {
                throw new RuntimeException('M85 note preview/apply requires fresh explicit gramlyze.loc proof.');
            }
        }
        return parent::connection($expected, $writing);
    }
    public static function fileHash(string $file): string { return TheoryNativeContentPatch::fileHash($file); }
    public static function readPinned(string $file, ?string $sha = null): array
    {
        $actual = self::fileHash($file);
        if ($sha !== null && !hash_equals($sha, $actual)) { throw new RuntimeException('M85 immutable evidence changed: '.basename($file)); }
        return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    }

    /** One named pre-existing difference, not a claim of historical WT raw capture. */
    public static function foreignFormatterProfile(array $profile,array $initialPreview,string $initialDigest): array
    {
        $copy=$initialPreview; unset($copy['sha256']);
        $expected=[
            'path'=>self::FOREIGN_FORMATTER_PATH,
            'root_raw_sha256'=>'4a0d7cf7bcedecf480839ed040a3675965854f274e8993b89c07eddcbcc807ba',
            'root_bytes'=>3299,'root_lf'=>85,'root_crlf'=>77,
            'worktree_raw_sha256'=>'1b05f9e539424e43cf007e82305d01a5b7307dad6a7f653343b4555b76aaeb8f',
            'worktree_bytes'=>3190,'worktree_lf'=>85,'worktree_crlf'=>85,
            'accepted_base'=>Package::BASE_SHA,'worktree_head'=>Package::BASE_SHA,'worktree_path_status'=>'',
            'git_blob_sha1'=>'2b4a74a6a4e5507dbe180eeb2b4732749f5d08de',
            'git_blob_sha256'=>'f2f2689dd1677e23173e13e83f219bf37841eecf5b0d39663b118018f7f18285',
            'git_blob_bytes'=>3105,'git_blob_lf'=>85,'git_blob_crlf'=>0,'worktree_lf_equals_accepted_git_blob'=>true,
        ];
        if ($profile!==$expected || ($initialPreview['sha256']??null)!==$initialDigest || self::digest($copy)!==$initialDigest
            || ($initialPreview['sources'][self::FOREIGN_FORMATTER_PATH]??null)!==$expected['root_raw_sha256']) {
            throw new RuntimeException('M85 single foreign formatter source profile is not exact and independently bound.');
        }
        return $expected+['root_provenance'=>'Immutable original M85 preview.sources, bound by successful initial AFTER digest/backup.',
            'worktree_provenance'=>'Accepted M84 Git blob plus clean current WT HEAD/path and exact observed raw CRLF profile.',
            'historical_worktree_raw_capture_claimed'=>false,'copied_or_normalized'=>false];
    }
    public static function assertCapturedContextBytes(?string $bytes,array $profile): void
    {
        if (($profile['exists']??null)===false) {
            if ($bytes!==null) { throw new RuntimeException('M85 previously absent context file appeared.'); }
            return;
        }
        if (($profile['exists']??null)!==true || $bytes===null || strlen($bytes)!==($profile['bytes']??null)
            || hash('sha256',$bytes)!==($profile['raw_sha256']??null)
            || hash('sha256',str_replace("\r\n","\n",$bytes))!==($profile['lf_sha256']??null)
            || substr_count($bytes,"\r\n")!==($profile['crlf']??null) || substr_count($bytes,"\n")!==($profile['lf']??null)) {
            throw new RuntimeException('M85 exact independently captured context bytes/EOL differ.');
        }
    }

    /** Removing only six new note keys must recover both predecessor bodies and the entire old patch. */
    public static function revision(?string $root = null): array
    {
        $root ??= base_path();
        $manifest = self::readPinned($root.'/'.self::MANIFEST, self::MANIFEST_SHA);
        $old = self::readPinned($root.'/'.self::PREVIOUS_SOURCE, self::PREVIOUS_SOURCE_SHA);
        [$before, $next, $master, $contract] = Package::load($root);
        Package::validate($before, $next, $master, $contract);
        if (Package::VERSION !== '1.0.0' || ($master['author_revision'] ?? null) !== '1.0.1'
            || ($manifest['native_version'] ?? null) !== '1.0.0' || ($manifest['author_revision'] ?? null) !== '1.0.1'
            || count($manifest['targets'] ?? []) !== 2 || Package::SOURCE === self::PREVIOUS_SOURCE) {
            throw new RuntimeException('M85 note revision/protocol/finite target vector differs.');
        }
        $normalized = $next;
        foreach (self::TARGETS as $n => $target) {
            $i=$target['owner_index']; $slot=$target['source_index']; $entry=$manifest['targets'][$n];
            $a=$old['targets'][$i]['after']['page']['blocks'][$slot]['body'] ?? null;
            $b=$next['targets'][$i]['after']['page']['blocks'][$slot]['body'] ?? null;
            $metadata=['id'=>$target['id'],'uuid'=>$target['uuid'],'locale'=>'uk','type'=>'mistakes-grid','column'=>'left',
                'heading'=>null,'css_class'=>null,'sort_order'=>$slot+1,'level'=>null];
            if (($entry['identity']??null)!==self::NAMES[$i] || ($entry['page_id']??null)!==Package::PAGE_IDS[$i]
                || ($entry['category_id']??null)!==8 || ($entry['source_index']??null)!==$slot || ($entry['row']??null)!==$metadata
                || !is_string($a) || !is_string($b) || ($entry['before_body_sha256']??null)!==hash('sha256',$a)
                || ($entry['after_body_sha256']??null)!==hash('sha256',$b) || ($entry['before_body_bytes']??null)!==strlen($a)
                || ($entry['after_body_bytes']??null)!==strlen($b) || count($entry['changes']??[])!==3) {
                throw new RuntimeException('M85 note exact row/body identity differs.');
            }
            $oldData=json_decode($a,true,flags:JSON_THROW_ON_ERROR); $newData=json_decode($b,true,flags:JSON_THROW_ON_ERROR);
            $copy=$newData;
            foreach (range(0,2) as $item) {
                $hint=$oldData['items'][$item]['hint']??null; $change=$entry['changes'][$item];
                if (array_key_exists('note',$oldData['items'][$item]) || !is_string($hint) || trim($hint)===''
                    || ($newData['items'][$item]['note']??null)!==$hint || ($newData['items'][$item]['hint']??null)!==$hint
                    || ($change['item_index']??null)!==$item
                    || ($change['pointer']??null)!=='/lessons/'.$i.'/blocks/'.$slot.'/data/items/'.$item.'/note'
                    || ($change['hint_before']??null)!==$hint || ($change['note_after']??null)!==$hint) {
                    throw new RuntimeException('M85 note must exactly duplicate an existing hint, not author new text.');
                }
                unset($copy['items'][$item]['note']);
            }
            if ($copy!==$oldData) { throw new RuntimeException('M85 note revision changed another data field.'); }
            $normalized['targets'][$i]['after']['page']['blocks'][$slot]['body']=$a;
        }
        if ($normalized!==$old) { throw new RuntimeException('M85 note revision changed source outside two bodies.'); }
        foreach (['source','master','checksums'] as $key) {
            self::readPinned($root.'/'.$manifest['predecessor'][$key],$manifest['predecessor'][$key.'_sha256']);
        }
        if (($manifest['predecessor']['source']??null)!==self::PREVIOUS_SOURCE
            || ($manifest['predecessor']['source_sha256']??null)!==self::PREVIOUS_SOURCE_SHA) {
            throw new RuntimeException('M85 immutable predecessor identity differs.');
        }
        foreach ($next['targets'] as $target) {
            if (self::readPinned($root.'/'.$target['path'])!==$target['after']) {
                throw new RuntimeException('M85 active definition differs from exact successor.');
            }
        }
        return [$manifest,$old,$next];
    }

    public static function syncPaths(?string $root = null): array
    {
        $root ??= base_path(); [$manifest,,$patch]=self::revision($root);
        $contract=self::readPinned($root.'/'.Package::CONTRACT,Package::CONTRACT_SHA);
        $paths=[...M85ContentPatch::sourcePaths($patch,$contract,$root),...self::OWNED_PATHS,self::MANIFEST,
            self::PREVIOUS_SOURCE,$manifest['predecessor']['master'],$manifest['predecessor']['checksums']];
        $checks=self::readPinned($root.'/'.Package::CHECKSUMS,Package::CHECKSUMS_SHA);
        foreach (['artifacts','active_definitions','author_inputs'] as $group) {
            foreach ($checks[$group]??[] as $row) { $paths[]=$row['path']; }
        }
        $paths=array_values(array_unique($paths)); sort($paths); return $paths;
    }

    /** Main installs/removes this exact hunk; this service never edits routes. */
    public static function proofRouteHunk(string $nonce): string
    {
        if (preg_match('/^[a-f0-9]{32}$/D',$nonce)!==1) { throw new RuntimeException('M85 invalid nonce.'); }
        $text=<<<'ROUTE'
// Temporary M85 note-repair local-target proof; removed after guarded apply/no-op.
Route::get('/_local/m85-target-__NONCE__', function (Request $request) {
    $nonce = '__NONCE__';
    abort_unless(\App\Services\M85LocalTargetGuard::allowsProofRequest($request, $nonce, base_path(), public_path()), 404);
    $db = \Illuminate\Support\Facades\DB::connection();
    \App\Services\M85LocalTargetGuard::assertConfiguredDatabase($db);
    $db->statement('SET TRANSACTION READ ONLY');
    $db->beginTransaction();
    try {
        return response()->json([
            'runtime' => \App\Services\M85LocalTargetGuard::publicIdentity($db, public_path()),
            'proof' => \App\Services\M11LocalTargetGuard::runtimeProof($db, $nonce, public_path()),
        ])->header('Cache-Control', 'no-store');
    } finally {
        $db->rollBack();
    }
})->domain('gramlyze.loc');
ROUTE;
        return str_replace("\n","\r\n",str_replace('__NONCE__',$nonce,$text))."\r\n\r\n";
    }

    /** Raw source boundary: no broad package-prefix exemption; only six definitions/four captured new test drafts. */
    public static function sourceState(string $root,string $private,?string $proofName=null): array
    {
        self::revision($root);
        $capture=self::readPinned($private.'/'.self::SOURCE_BEFORE,self::SOURCE_BEFORE_SHA);
        $fixture=self::readPinned($root.'/'.self::SOURCE_FIXTURE,self::SOURCE_FIXTURE_SHA);
        $initial=self::readPinned($private.'/'.M85ContentPatch::BEFORE_FILE,M85ContentPatch::BEFORE_SHA);
        $sync=self::readPinned($private.'/'.self::SYNC_RECEIPT);
        $paths=self::syncPaths($root); $listed=array_column($sync['files']??[],'path'); sort($listed);
        if ($listed!==$paths || ($sync['served_root']??null)!==M85LocalTargetGuard::ROOT
            || ($sync['source_worktree']??null)!==M85ContentPatch::SOURCE_WORKTREE || count($capture['files']??[])!==3458
            || count($fixture['files']??[])!==8) { throw new RuntimeException('M85 note source-sync exact path/identity scope differs.'); }
        $sources=[]; $byPath=array_column($sync['files'],null,'path'); $shared=array_column($fixture['files'],null,'path');
        $foreign=$sync['foreign_context_profiles']??[];
        if (array_keys($foreign)!==[self::FOREIGN_FORMATTER_PATH]) { throw new RuntimeException('M85 exact single foreign source provenance record missing.'); }
        $baseline=self::readPinned($private.'/'.self::BASELINE,self::BASELINE_SHA);
        $original=self::readPinned($private.'/'.$baseline['plan_file']); $profile=$foreign[self::FOREIGN_FORMATTER_PATH];
        unset($profile['root_provenance'],$profile['worktree_provenance'],$profile['historical_worktree_raw_capture_claimed'],$profile['copied_or_normalized']);
        if (self::foreignFormatterProfile($profile,$original,$baseline['plan_digest'])!==$foreign[self::FOREIGN_FORMATTER_PATH]
            || ($byPath[self::FOREIGN_FORMATTER_PATH]['root_after_sha256']??null)!==$profile['root_raw_sha256']
            || ($byPath[self::FOREIGN_FORMATTER_PATH]['worktree_sha256']??null)!==$profile['worktree_raw_sha256']) {
            throw new RuntimeException('M85 foreign source receipt lost its independent exact provenance.');
        }
        $definitions=array_map(\App\Support\TheorySentenceBuilderAcceptance::definitionPath(...),Package::OWNERS);
        $own=['tests/Feature/M85LocalGuardTest.php','tests/Feature/M85SourceBoundaryTest.php',
            'tests/Feature/M85VerbToBeContentPatchTest.php','tests/Feature/M85VerbToBePackageTest.php'];
        foreach (['served_root'=>M85LocalTargetGuard::ROOT,'worktree'=>M85ContentPatch::SOURCE_WORKTREE] as $context=>$directory) {
            $visual=[];
            foreach (['resources/views','resources/css','resources/js','public/js'] as $folder) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory.'/'.$folder,\FilesystemIterator::SKIP_DOTS)) as $entry) {
                    if ($entry->isLink()) { throw new RuntimeException('M85 note visual inventory contains a link.'); }
                    if ($entry->isFile()) { $visual[]=substr(str_replace('\\','/',$entry->getPathname()),strlen($directory)+1); }
                }
            }
            $expectedVisual=$capture['groups']['visual']??[]; sort($visual); sort($expectedVisual);
            if (count($expectedVisual)!==326 || $visual!==$expectedVisual) { throw new RuntimeException('M85 note full visual inventory differs.'); }
            foreach ($paths as $path) {
                $sha=self::fileHash($directory.'/'.$path);
                if ($sha!==($byPath[$path][$context==='served_root'?'root_after_sha256':'worktree_sha256']??null)) {
                    throw new RuntimeException('M85 note fresh synced source differs: '.$path);
                }
                if ($context==='served_root') { $sources[$path]=$sha; }
            }
            foreach ($capture['files'] as $path=>$profiles) {
                $profile=$profiles[$context]; $file=$directory.'/'.$path;
                if (in_array($path,$own,true)) { continue; }
                if ($path==='routes/api.php' && $context==='served_root' && $proofName!==null) {
                    $proof=M85LocalTargetGuard::readFreshProof($proofName);
                    if (($proof['routes_before_file']??null)!=='routes-before-'.$proof['nonce'].'.bin') {
                        throw new RuntimeException('M85 note proof route snapshot basename differs.');
                    }
                    $saved=$private.'/'.$proof['routes_before_file'];
                    self::fileHash($saved); $raw=file_get_contents($file); $original=file_get_contents($saved);
                    $hunk=self::proofRouteHunk($proof['nonce']);
                    if ($proof['routes_before_file']!=='routes-before-'.$proof['nonce'].'.bin'
                        || hash('sha256',$original)!==$profile['raw_sha256']
                        || ($proof['routes_before_sha256']??null)!==hash('sha256',$original)
                        || substr_count($raw,$hunk)!==1 || str_replace($hunk,'',$raw)!==$original) {
                        throw new RuntimeException('M85 note exact temporary proof route reversal differs.');
                    }
                    $sources['served:routes/api.php']=self::fileHash($file);
                    $sources['private:'.$proofName]=self::fileHash($private.'/'.$proofName);
                    $sources['private:'.$proof['routes_before_file']]=self::fileHash($saved); continue;
                }
                if ($path==='.gitattributes' && ($profile['exists']??false)) {
                    if (hash('sha256',substr(file_get_contents($file),0,$profile['bytes']))!==$profile['raw_sha256']) {
                        throw new RuntimeException('M85 note delivery attributes lost the raw foreign prefix.');
                    }
                    continue;
                }
                $expected=isset($shared[$path]) ? $shared[$path]['profiles'][$context]['after_raw_sha256']
                    : (in_array($path,$definitions,true) ? ($byPath[$path][$context==='served_root'?'root_after_sha256':'worktree_sha256']??null)
                        : ($profile['raw_sha256']??null));
                if (($profile['exists']??null)===false && !in_array($path,$definitions,true)) {
                    if (file_exists($file)||is_link($file)) { throw new RuntimeException('M85 note foreign absent source appeared: '.$path); }
                } elseif (!is_string($expected)||self::fileHash($file)!==$expected) {
                    throw new RuntimeException('M85 note independent raw source boundary differs: '.$path);
                }
            }
            // These original hashes were captured on ROOT, not on WT. WT is
            // protected above by its own exact per-context 3458-path capture.
            if ($context==='served_root') {
                foreach ([$initial['visual_hashes'],$initial['frozen_hashes']] as $hashes) {
                    foreach ($hashes as $path=>$sha) {
                        if (self::fileHash($directory.'/'.$path)!==$sha) { throw new RuntimeException('M85 note ROOT visual/historical source changed.'); }
                    }
                }
            }
        }
        foreach ([self::BASELINE,self::SOURCE_BEFORE,self::SYNC_RECEIPT,self::SCHEMA_BEFORE,M85ContentPatch::BEFORE_FILE] as $file) {
            $sources['private:'.$file]=self::fileHash($private.'/'.$file);
        }
        foreach ($initial['physical_hashes'] as $file=>$sha) {
            if (self::fileHash($file)!==$sha) { throw new RuntimeException('M85 note physical local configuration changed.'); }
        }
        ksort($sources); return $sources;
    }

    protected function baselineEvidence(): array
    {
        $baseline=self::readPinned($this->privateDirectory.'/'.self::BASELINE,self::BASELINE_SHA);
        foreach (['all_old_rows_and_timestamps_exact','independent_before_projection_exact','reviewed_protected_projection_exact','source_sync_unchanged'] as $field) {
            if (($baseline[$field]??null)!==true) { throw new RuntimeException('M85 note initial preservation receipt incomplete.'); }
        }
        if (($baseline['sql_tables']??null)!==46 || count($baseline['sql_after']??[])!==46
            || ($baseline['independent_before_sha256']??null)!==M85ContentPatch::BEFORE_SHA
            || ($baseline['new_question_families']??null)!==[] || count($baseline['new_practices']??[])!==6
            || ($baseline['db_writes']??null)!==0 || ($baseline['http_requests']??null)!==0) {
            throw new RuntimeException('M85 note initial 46-table/six-practice evidence differs.');
        }
        return $baseline;
    }
    protected function correctionFingerprints(bool $normalize=false,bool $protect=false): array
    {
        if ($normalize && count($this->bodies)!==2) { throw new RuntimeException('M85 note predecessor bodies not prepared.'); }
        $schema=$this->db->getDriverName()==='sqlite'?'main':$this->db->getDatabaseName();
        $tables=$this->db->getSchemaBuilder()->getTableListing(schema:$schema,schemaQualified:false); sort($tables); $out=[];
        foreach ($tables as $table) {
            $columns=$this->db->getSchemaBuilder()->getColumnListing($table); $q=$this->db->table($table);
            foreach (in_array('id',$columns,true)?['id']:$columns as $column) { $q->orderBy($column); }
            $hash=hash_init('sha256'); $count=0;
            foreach ($q->cursor() as $record) {
                $row=(array)$record;
                if ($table==='text_blocks' && in_array($row['id']??null,array_column(self::TARGETS,'id'),true)) {
                    if ($protect) { unset($row['body']); } elseif ($normalize) { $row['body']=$this->bodies[$row['id']]['before']; }
                }
                hash_update($hash,self::digest($row)."\n"); ++$count;
            }
            $out[$table]=['count'=>$count,'sha256'=>hash_final($hash)];
        }
        return $out;
    }
    protected function protectedFingerprints(array $targetIds): array { return $this->correctionFingerprints(protect:true); }
    /** SELECT-only diagnostic seam; does not prepare or authorize a write. */
    public function rawFingerprints(): array { return $this->correctionFingerprints(); }
    public function readOnlyProtectedFingerprints(): array { return $this->correctionFingerprints(protect:true); }
    public function readOnlyOwnerSnapshots(): array
    {
        $owners=[];
        foreach (self::NAMES as $i=>$owner) {
            $pages=$this->rows('pages',static fn($q)=>$q->where('seeder',$owner),false);
            if (count($pages)!==1 || $pages[0]['id']!==Package::PAGE_IDS[$i]) { throw new RuntimeException('M85 note fresh owner snapshot identity differs.'); }
            $owners[$owner]=['page'=>$pages[0],
                'blocks'=>$this->rows('text_blocks',static fn($q)=>$q->where('page_id',$pages[0]['id']),false)];
        }
        return $owners;
    }

    public function plan(bool $lock=false): array
    {
        $root=dirname($this->databasePath); $connection=$this->connection(); [$manifest,$old,$next]=self::revision($root);
        foreach (self::TARGETS as $target) {
            $i=$target['owner_index']; $slot=$target['source_index'];
            $this->bodies[$target['id']]=['before'=>$old['targets'][$i]['after']['page']['blocks'][$slot]['body'],
                'after'=>$next['targets'][$i]['after']['page']['blocks'][$slot]['body']];
        }
        $baseline=$this->baselineEvidence();
        $plan=['patch'=>static::ID,'version'=>1,'names'=>self::NAMES,'connection'=>$connection,'sources'=>[],'pages'=>[],'updates'=>[],'inserts'=>[]];
        foreach (self::syncPaths($root) as $path) { $plan['sources'][$path]=self::fileHash($root.'/'.$path); }
        if ($this->db->getDriverName()==='mysql') {
            $plan['sources']=self::sourceState($root,$this->privateDirectory,$this->localProof);
            $fresh=self::readPinned($this->privateDirectory.'/'.self::CURRENT_BEFORE);
            $capturedAt=strtotime($fresh['created_at']??'');
            if (($fresh['sql_after']??null)!==$baseline['sql_after'] || ($fresh['baseline_sha256']??null)!==self::BASELINE_SHA
                || ($fresh['phase']??null)!=='current-before-note-repair' || ($fresh['sql_tables']??null)!==46
                || ($fresh['db_writes']??null)!==0 || ($fresh['http_requests']??null)!==0
                || !$capturedAt || $capturedAt>time()+60 || time()-$capturedAt>1800
                || ($fresh['runtime']??null)!==M85LocalTargetGuard::publicIdentity($this->db,$root.'/public')) {
                throw new RuntimeException('M85 note fresh current BEFORE does not equal proven initial AFTER.');
            }
            $sameSources=$plan['sources'];
            foreach (array_keys($sameSources) as $path) {
                if (str_starts_with($path,'served:') || str_starts_with($path,'private:local-proof-') || str_starts_with($path,'private:routes-before-')) {
                    unset($sameSources[$path]);
                }
            }
            if (($fresh['sources']??null)!==$sameSources) { throw new RuntimeException('M85 note sources changed after fresh current BEFORE.'); }
            $plan['sources']['private:'.self::CURRENT_BEFORE]=self::fileHash($this->privateDirectory.'/'.self::CURRENT_BEFORE);
            $schema=self::readPinned($this->privateDirectory.'/'.self::SCHEMA_BEFORE,self::SCHEMA_BEFORE_SHA);
            foreach ($schema['schema'] as $table=>$columns) {
                if ($this->db->getSchemaBuilder()->getColumns($table)!==$columns) { throw new RuntimeException('M85 note full captured schema changed.'); }
            }
        }
        $states=[]; $seen=[];
        foreach (self::NAMES as $i=>$owner) {
            $pages=$this->rows('pages',static fn($q)=>$q->where('seeder',$owner),$lock);
            if (count($pages)!==1) { throw new RuntimeException('M85 note missing/ambiguous owner.'); }
            $page=$pages[0]; $chain=$this->categoryChain($page['page_category_id'],$lock);
            if ($page['id']!==Package::PAGE_IDS[$i] || $page['page_category_id']!==8 || $page['slug']!==Package::SLUGS[$i]
                || $page['type']!=='theory' || array_column($chain,'slug')!==Package::ANCESTRIES[$i]) {
                throw new RuntimeException('M85 note page/category identity differs.');
            }
            $blocks=$this->rows('text_blocks',static fn($q)=>$q->where('page_id',$page['id']),$lock);
            $plan['pages'][$owner]=['page'=>$page,'category_ancestry'=>$chain,'blocks'=>$blocks,'relations'=>$this->relations($page,$blocks,$lock)];
            foreach ($blocks as $row) {
                foreach (self::TARGETS as $n=>$target) {
                    if ($row['id']!==$target['id'] && $row['uuid']!==$target['uuid']) { continue; }
                    if (isset($seen[$n]) || $i!==$target['owner_index']) { throw new RuntimeException('M85 note duplicate/foreign target identity.'); }
                    $expected=$manifest['targets'][$n]['row']+['page_id'=>$page['id'],'page_category_id'=>8,'seeder'=>$owner];
                    foreach ($expected as $field=>$value) {
                        if (!array_key_exists($field,$row)||$row[$field]!==$value) { throw new RuntimeException('M85 note exact target metadata differs: '.$field); }
                    }
                    $seen[$n]=true; $states[]=$this->candidate($plan['updates'],'text_blocks',$row,$owner,
                        ['body'=>$this->bodies[$target['id']]['before']],['body'=>$this->bodies[$target['id']]['after']]);
                }
            }
        }
        if (count($seen)!==2 || count(array_unique($states))!==1 || !in_array($states[0],['before','after'],true)
            || ($states[0]==='before'&&count($plan['updates'])!==2) || ($states[0]==='after'&&$plan['updates']!==[])
            || $this->correctionFingerprints(normalize:true)!==$baseline['sql_after']) {
            throw new RuntimeException('M85 note partial/manual state or SQL drift outside two body cells.');
        }
        $plan['state']=$states[0]; $plan['protected']=$this->protectedFingerprints(array_column(self::TARGETS,'id'));
        ksort($plan['sources']); $plan['sha256']=self::digest($plan); return $plan;
    }
}
