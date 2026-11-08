<?php

namespace App\Services;

use App\Support\M40TensesB1Package as Package;
use RuntimeException;

/** Finite three-owner projection; reuses audited exclusive backup/transaction primitives. */
class M40ContentPatch extends M26ContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\Tenses\\TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\TensesNarrativeTensesTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarB1MixedRevisionTheorySeeder',
    ];
    // Exact accepted identity => leaf category and full root-to-leaf ancestry.
    public const CATEGORIES = [
        self::NAMES[0] => 'tenses',
        self::NAMES[1] => 'tenses',
        self::NAMES[2] => 'mixed-revision',
    ];
    public const ANCESTRIES = [
        self::NAMES[0] => ['tenses'],
        self::NAMES[1] => ['tenses'],
        self::NAMES[2] => ['mixed-revision'],
    ];
    public const ACCEPTED_BASE = 'd92d759a5c950e0f9eabd2f0fa95557327f42005';
    public const SHARED_VIEWS = [
        'resources/views/theory/partials/content-block.blade.php',
        'resources/views/theory/partials/point-detail-fragment.blade.php',
        'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
        'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
        'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
        'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
    ];
    public const SHARED_REVIEW = 'shared-source-review-v1.json';
    protected const ID = 'm40-m24-tenses-b1-v1';
    protected function allowedUpdateFields(): array { return ['type', 'body']; }
    protected function localGuard(): string { return M40LocalTargetGuard::class; }
    protected function expectedInsertCount(): int
    {
        [$before, $package] = Package::load(dirname($this->databasePath));
        $count=0;
        foreach($package['targets'] as $i=>$target)$count+=count($target['after']['page']['blocks'])-count($before['targets'][$i]['before']['page']['blocks']);
        return $count;
    }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath); [$manifest, $package] = Package::load($root);
        Package::validate($manifest, $package);
        foreach (['author_master_source', 'author_notes_source'] as $key) {
            $source = $manifest[$key] ?? null;
            $path = is_array($source) ? $root.'/'.($source['path'] ?? '') : '';
            if (!is_array($source) || is_link($path) || !is_file($path)) {
                throw new RuntimeException('M40 frozen planning author source changed or missing; no writes.');
            }
        }
        if (array_column($package['targets'], 'identity') !== self::NAMES) { throw new RuntimeException('M40 exact three-owner scope differs.'); }
        foreach ($package['targets'] as $target) {
            if ($target['ancestry'] !== self::ANCESTRIES[$target['identity']]) {
                throw new RuntimeException('M40 finite owner/category mapping differs.');
            }
        }
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => static::NAMES, 'connection' => $this->connection(),
            'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => []];
        if ($this->localTarget !== null) {
            self::reviewedSharedSources($root, $this->privateDirectory);
            $plan['sources']['private:'.self::SHARED_REVIEW] = hash_file('sha256', $this->privateDirectory.'/'.self::SHARED_REVIEW);
        }
        foreach ([Package::BEFORE, Package::SOURCE, 'app/Support/M40TensesB1Package.php',
            'app/Services/M40ContentPatch.php', 'app/Services/M26ContentPatch.php', 'app/Services/M40LocalTargetGuard.php',
            'app/Services/M11LocalTargetGuard.php', 'app/Console/Commands/PatchM40Content.php',
            'tools/diagnostics/run-m40-working-local.php', 'tools/diagnostics/inspect-m11-local-target.ps1',
            'docs/content/m24-authored-content.v1.json', 'docs/content/m24-author-sources.md',
            'resources/views/theory/partials/content-block.blade.php', 'resources/views/theory/partials/point-detail-fragment.blade.php',
            'resources/views/theory/partials/point-disclosure.blade.php', 'resources/views/theory/partials/section-disclosure.blade.php',
            'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
            'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
            'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
            'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
            'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php',
            'resources/views/engram/theory/blocks-v3/m39-practice-ui.blade.php',
            'resources/views/engram/theory/blocks-v3/m40-practice-ui.blade.php',
            'public/js/authored-practice-ui.js', 'public/js/m39-practice-ui.js', 'public/js/m40-practice-ui.js',
            'app/Services/M39PracticeUiPatch.php', 'app/Support/M39PracticeUiPackage.php',
            'database/content-patches/m39-practice-ui.v1.json', 'app/Support/M24NativeTitleNumber.php'] as $path) {
            if (!is_file($root.'/'.$path)) { throw new RuntimeException('M40 code source missing.'); }
            $plan['sources'][$path] = hash_file('sha256', $root.'/'.$path);
        }
        $states = []; $targetIds = [];
        foreach ($package['targets'] as $i => $target) {
            $name = $target['identity']; $before = $manifest['targets'][$i]['before']; $after = $target['after'];
            $path = $target['path']; $bytes = file_get_contents($root.'/'.$path);
            try { $source=json_decode($bytes,true,flags:JSON_THROW_ON_ERROR); }
            catch (\JsonException) { throw new RuntimeException('M40 canonical definition is invalid JSON.'); }
            if ($source !== $after) { throw new RuntimeException('M40 canonical definition differs.'); }
            $plan['sources'][$path] = hash('sha256', $bytes);
            foreach (glob(dirname($root.'/'.$path).'/localizations/*.json') ?: [] as $p) {
                $plan['sources'][substr(str_replace('\\','/',$p), strlen(str_replace('\\','/',$root))+1)] = hash_file('sha256',$p);
            }
            $owners = $this->rows('pages', fn ($q) => $q->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('M40 missing/ambiguous owner.'); }
            $owner = $owners[0]; $category = $this->categoryChain($owner['page_category_id'], $lock); $c = $before['page'];
            if (array_column($category,'slug') !== self::ANCESTRIES[$name] || $owner['slug'] !== $before['slug']
                || $owner['type'] !== 'theory' || $owner['title'] !== $c['title'] || $owner['text'] !== $c['subtitle_text']) {
                throw new RuntimeException('M40 owner/category/metadata conflict.');
            }
            $rows = $this->rows('text_blocks', fn ($q) => $q->where('page_id',$owner['id']), $lock);
            $uk = array_values(array_filter($rows, fn ($r) => $r['locale'] === 'uk'));
            $subtitle = ['type'=>'subtitle','column'=>'header','heading'=>null,'level'=>$c['subtitle_level']??null,
                'body'=>$c['subtitle_html'],'uuid_key'=>$c['subtitle_uuid_key']??'subtitle'];
            $oldConfigs = [$subtitle,...$c['blocks']]; $newConfigs = [$subtitle,...$after['page']['blocks']];
            $oldLastPosition=count($oldConfigs)-1;
            if(array_slice($after['page']['blocks'],0,-1*(count($after['page']['blocks'])-count($c['blocks'])+1))!==array_slice($c['blocks'],0,-1))throw new RuntimeException('M40 existing native sources changed.');
            $rowStates = [];
            foreach ($oldConfigs as $position => $old) {
                $uuid = $this->resolveUuid($name,$old,$position);
                $global = $this->rows('text_blocks', fn ($q) => $q->where('uuid',$uuid),$lock);
                if (count($global) !== 1) { throw new RuntimeException('M40 missing/ambiguous old UUID.'); }
                $r = $global[0]; $new = $newConfigs[$position];
                $this->assertOwner($r,$owner,$name,$position);
                foreach (['column','heading','level','css_class'] as $field) {
                    if ($r[$field] !== ($old[$field]??null) || ($new[$field]??null) !== ($old[$field]??null)) {
                        throw new RuntimeException('M40 old block protected field differs: '.$field);
                    }
                }
                if ($position === $oldLastPosition) {
                    $targetIds[]=$r['id'];
                    $rowStates[]=$this->candidate($plan['updates'],'text_blocks',$r,$name,
                        ['type'=>$old['type'],'body'=>$old['body']],['type'=>$new['type'],'body'=>$new['body']]);
                } elseif ($r['type'] !== $old['type'] || $r['body'] !== $old['body']) { throw new RuntimeException('M40 subtitle/hero changed.'); }
            }
            $state=$rowStates[0]; $present=0;
            foreach (array_slice($newConfigs,count($oldConfigs), null, true) as $position=>$b) {
                $uuid=$this->resolveUuid($name,$b,$position);
                $global=$this->rows('text_blocks',fn($q)=>$q->where('uuid',$uuid),$lock);
                $fields=['uuid'=>$uuid,'page_id'=>$owner['id'],'page_category_id'=>$owner['page_category_id'],'locale'=>'uk',
                    'type'=>$b['type'],'column'=>$b['column'],'heading'=>$b['heading']??null,'css_class'=>$b['css_class']??null,
                    'sort_order'=>$position,'body'=>$b['body'],'level'=>$b['level']??null,'seeder'=>$name];
                if ($state==='before') {
                    if ($global) { throw new RuntimeException('M40 unknown/partial UUID; no writes.'); }
                    $plan['inserts'][]=['table'=>'text_blocks','seeder'=>$name,'fields'=>$fields,'sha256'=>self::digest($fields)];
                } else {
                    if (count($global)!==1) { throw new RuntimeException('M40 inserted block missing/ambiguous.'); }
                    $r=$global[0]; $this->assertOwner($r,$owner,$name,$position);
                    foreach ($fields as $f=>$v) { if ($r[$f]!==$v) { throw new RuntimeException('M40 inserted row manually edited.'); } }
                    if (empty($r['created_at']) || $r['created_at']!==$r['updated_at']) { throw new RuntimeException('M40 inserted timestamps changed.'); }
                    $targetIds[]=$r['id']; $present++;
                }
            }
            if (count($uk)!==count($oldConfigs)+$present) { throw new RuntimeException('M40 unexpected UK rows; no writes.'); }
            $states[]=$state;
            $plan['pages'][$name]=['page'=>$owner,'category_ancestry'=>$category,'blocks'=>$rows,
                'relations'=>$this->relations($owner,$rows,$lock)];
        }
        if (count(array_unique($states))!==1) { throw new RuntimeException('M40 partial package; no writes.'); }
        $plan['state']=$states[0]; $plan['protected']=$this->protectedFingerprints($targetIds);
        ksort($plan['sources']); $plan['sha256']=self::digest($plan); return $plan;
    }

    /** Protect all existing current-schema progress/attempt/review/state tables, without returning private rows. */
    protected function protectedFingerprints(array $targetIds): array
    {
        $result=parent::protectedFingerprints($targetIds);
        $schema=$this->db->getDriverName()==='sqlite'?'main':$this->db->getDatabaseName();
        $tables=array_values(array_filter($this->db->getSchemaBuilder()->getTableListing(schema:$schema,schemaQualified:false),
            fn($table)=>preg_match('/(?:progress|attempt|review|result|state)/i',$table)));
        sort($tables);
        foreach($tables as $table){
            if(isset($result[$table]))continue;
            $query=$this->db->table($table);$columns=$this->db->getSchemaBuilder()->getColumnListing($table);
            foreach(in_array('id',$columns,true)?['id']:$columns as $column)$query->orderBy($column);
            $hash=hash_init('sha256');$count=0;
            foreach($query->cursor() as $row){hash_update($hash,self::digest((array)$row)."\n");$count++;}
            $result[$table]=['count'=>$count,'sha256'=>hash_final($hash)];
        }
        return $result;
    }

    /** Read-only exact reviewed hashes. Dirty shared ROOT files are never copied from this worktree. */
    public static function reviewedSharedSources(string $source, string $directory): array
    {
        $path = $directory.'/'.self::SHARED_REVIEW;
        if (!is_file($path) || is_link($path)) { throw new RuntimeException('Missing exact reviewed M40 shared-source evidence.'); }
        $review = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($review['version'] ?? null) !== 1 || ($review['base_sha'] ?? null) !== self::ACCEPTED_BASE
            || array_column($review['files'] ?? [], 'path') !== self::SHARED_VIEWS) {
            throw new RuntimeException('M40 reviewed shared-source allowlist differs.');
        }
        foreach ($review['files'] as $file) {
            if (array_keys($file) !== ['path', 'root_sha256', 'worktree_sha256', 'before_sha256', 'before_basename']
                || !preg_match('/^[a-z0-9-]+\.bin$/D', $file['before_basename'])) {
                throw new RuntimeException('M40 shared-source record is incomplete.');
            }
            foreach (['root_sha256', 'worktree_sha256', 'before_sha256'] as $key) {
                if (!is_string($file[$key]) || !preg_match('/^[a-f0-9]{64}$/D', $file[$key])) {
                    throw new RuntimeException('M40 shared-source digest is invalid.');
                }
            }
            $before = $directory.'/'.$file['before_basename'];
            $working = M40LocalTargetGuard::ROOT.'/'.$file['path']; $canonical = $source.'/'.$file['path'];
            if (is_link($before) || is_link($working) || is_link($canonical) || !is_file($before)
                || hash_file('sha256', $before) !== $file['before_sha256']
                || hash_file('sha256', $working) !== $file['root_sha256']
                || hash_file('sha256', $canonical) !== $file['worktree_sha256']) {
                throw new RuntimeException('M40 reviewed shared-source bytes changed.');
            }
        }
        return $review['files'];
    }

    private function assertOwner(array $r,array $owner,string $name,int $position): void
    {
        if ($r['seeder']!==$name || $r['locale']!=='uk' || $r['page_id']!==$owner['id']
            || $r['page_category_id']!==$owner['page_category_id'] || (int)$r['sort_order']!==$position) {
            throw new RuntimeException('M40 exact row owner/locale/order conflict.');
        }
    }
}
