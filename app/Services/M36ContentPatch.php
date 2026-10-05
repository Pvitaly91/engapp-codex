<?php

namespace App\Services;

use App\Support\M36ModalsSubjunctivePackage as Package;
use RuntimeException;

/** Finite three-owner projection; reuses audited exclusive backup/transaction primitives. */
class M36ContentPatch extends M26ContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalPerfectAndDeductionTheorySeeder',
        'Database\\Seeders\\Page_V3\\FormalEnglish\\SubjunctiveAndFormalStructuresTheorySeeder',
        'Database\\Seeders\\Page_V3\\ModalVerbs\\SubtleModalMeaningsTheorySeeder',
    ];
    // Exact accepted identity => root category. Another allowed M36 root is still foreign to this owner.
    public const CATEGORIES = [
        self::NAMES[0] => 'modal-verbs',
        self::NAMES[1] => 'formal-english',
        self::NAMES[2] => 'modal-verbs',
    ];
    public const ACCEPTED_BASE = '4644a1729d326b9a967d03c397998506f00d704b';
    public const SHARED_VIEWS = [
        'resources/views/theory/partials/content-block.blade.php',
        'resources/views/theory/partials/point-detail-fragment.blade.php',
        'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
        'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
        'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
        'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
    ];
    public const SHARED_REVIEW = 'shared-source-review-v1.json';
    protected const ID = 'm36-m20-modals-subjunctive-v1';
    protected function allowedUpdateFields(): array { return ['type', 'body']; }
    protected function localGuard(): string { return M36LocalTargetGuard::class; }
    protected function expectedInsertCount(): int
    {
        [, $package] = Package::load(dirname($this->databasePath));
        return array_sum(array_map(fn ($t) => count($t['after']['page']['blocks']) - 2, $package['targets']));
    }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath); [$manifest, $package] = Package::load($root);
        Package::validate($manifest, $package);
        if (array_column($package['targets'], 'identity') !== self::NAMES) { throw new RuntimeException('M36 exact three-owner scope differs.'); }
        foreach ($package['targets'] as $target) {
            if ($target['ancestry'] !== [self::CATEGORIES[$target['identity']]]) {
                throw new RuntimeException('M36 finite owner/category mapping differs.');
            }
        }
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => static::NAMES, 'connection' => $this->connection(),
            'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => []];
        if ($this->localTarget !== null) {
            self::reviewedSharedSources($root, $this->privateDirectory);
            $plan['sources']['private:'.self::SHARED_REVIEW] = hash_file('sha256', $this->privateDirectory.'/'.self::SHARED_REVIEW);
        }
        foreach ([Package::BEFORE, Package::SOURCE, 'app/Support/M36ModalsSubjunctivePackage.php',
            'app/Services/M36ContentPatch.php', 'app/Services/M26ContentPatch.php', 'app/Services/M36LocalTargetGuard.php',
            'app/Services/M11LocalTargetGuard.php', 'app/Console/Commands/PatchM36Content.php',
            'tools/diagnostics/run-m36-working-local.php', 'tools/diagnostics/inspect-m11-local-target.ps1',
            'resources/views/theory/partials/content-block.blade.php', 'resources/views/theory/partials/point-detail-fragment.blade.php',
            'resources/views/theory/partials/point-disclosure.blade.php', 'resources/views/theory/partials/section-disclosure.blade.php',
            'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
            'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
            'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
            'resources/views/engram/theory/blocks-v3/practice-set.blade.php'] as $path) {
            if (!is_file($root.'/'.$path)) { throw new RuntimeException('M36 code source missing.'); }
            $plan['sources'][$path] = hash_file('sha256', $root.'/'.$path);
        }
        $states = []; $targetIds = [];
        foreach ($package['targets'] as $i => $target) {
            $name = $target['identity']; $before = $manifest['targets'][$i]['before']; $after = $target['after'];
            $path = $target['path']; $bytes = file_get_contents($root.'/'.$path);
            try { $source=json_decode($bytes,true,flags:JSON_THROW_ON_ERROR); }
            catch (\JsonException) { throw new RuntimeException('M36 canonical definition is invalid JSON.'); }
            if ($source !== $after) { throw new RuntimeException('M36 canonical definition differs.'); }
            $plan['sources'][$path] = hash('sha256', $bytes);
            foreach (glob(dirname($root.'/'.$path).'/localizations/*.json') ?: [] as $p) {
                $plan['sources'][substr(str_replace('\\','/',$p), strlen(str_replace('\\','/',$root))+1)] = hash_file('sha256',$p);
            }
            $owners = $this->rows('pages', fn ($q) => $q->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('M36 missing/ambiguous owner.'); }
            $owner = $owners[0]; $category = $this->categoryChain($owner['page_category_id'], $lock); $c = $before['page'];
            if (array_column($category,'slug') !== [self::CATEGORIES[$name]] || $owner['slug'] !== $before['slug']
                || $owner['type'] !== 'theory' || $owner['title'] !== $c['title'] || $owner['text'] !== $c['subtitle_text']) {
                throw new RuntimeException('M36 owner/category/metadata conflict.');
            }
            $rows = $this->rows('text_blocks', fn ($q) => $q->where('page_id',$owner['id']), $lock);
            $uk = array_values(array_filter($rows, fn ($r) => $r['locale'] === 'uk'));
            $subtitle = ['type'=>'subtitle','column'=>'header','heading'=>null,'level'=>$c['subtitle_level']??null,
                'body'=>$c['subtitle_html'],'uuid_key'=>$c['subtitle_uuid_key']??'subtitle'];
            $oldConfigs = [$subtitle,...$c['blocks']]; $newConfigs = [$subtitle,...$after['page']['blocks']];
            $rowStates = [];
            foreach ($oldConfigs as $position => $old) {
                $uuid = $this->resolveUuid($name,$old,$position);
                $global = $this->rows('text_blocks', fn ($q) => $q->where('uuid',$uuid),$lock);
                if (count($global) !== 1) { throw new RuntimeException('M36 missing/ambiguous old UUID.'); }
                $r = $global[0]; $new = $newConfigs[$position];
                $this->assertOwner($r,$owner,$name,$position);
                foreach (['column','heading','level','css_class'] as $field) {
                    if ($r[$field] !== ($old[$field]??null) || ($new[$field]??null) !== ($old[$field]??null)) {
                        throw new RuntimeException('M36 old block protected field differs: '.$field);
                    }
                }
                if ($position === 2) {
                    $targetIds[]=$r['id'];
                    $rowStates[]=$this->candidate($plan['updates'],'text_blocks',$r,$name,
                        ['type'=>$old['type'],'body'=>$old['body']],['type'=>$new['type'],'body'=>$new['body']]);
                } elseif ($r['type'] !== $old['type'] || $r['body'] !== $old['body']) { throw new RuntimeException('M36 subtitle/hero changed.'); }
            }
            $state=$rowStates[0]; $present=0;
            foreach (array_slice($newConfigs,3, null, true) as $position=>$b) {
                $uuid=$this->resolveUuid($name,$b,$position);
                $global=$this->rows('text_blocks',fn($q)=>$q->where('uuid',$uuid),$lock);
                $fields=['uuid'=>$uuid,'page_id'=>$owner['id'],'page_category_id'=>$owner['page_category_id'],'locale'=>'uk',
                    'type'=>$b['type'],'column'=>$b['column'],'heading'=>$b['heading']??null,'css_class'=>$b['css_class']??null,
                    'sort_order'=>$position,'body'=>$b['body'],'level'=>$b['level']??null,'seeder'=>$name];
                if ($state==='before') {
                    if ($global) { throw new RuntimeException('M36 unknown/partial UUID; no writes.'); }
                    $plan['inserts'][]=['table'=>'text_blocks','seeder'=>$name,'fields'=>$fields,'sha256'=>self::digest($fields)];
                } else {
                    if (count($global)!==1) { throw new RuntimeException('M36 inserted block missing/ambiguous.'); }
                    $r=$global[0]; $this->assertOwner($r,$owner,$name,$position);
                    foreach ($fields as $f=>$v) { if ($r[$f]!==$v) { throw new RuntimeException('M36 inserted row manually edited.'); } }
                    if (empty($r['created_at']) || $r['created_at']!==$r['updated_at']) { throw new RuntimeException('M36 inserted timestamps changed.'); }
                    $targetIds[]=$r['id']; $present++;
                }
            }
            if (count($uk)!==count($oldConfigs)+$present) { throw new RuntimeException('M36 unexpected UK rows; no writes.'); }
            $states[]=$state;
            $plan['pages'][$name]=['page'=>$owner,'category_ancestry'=>$category,'blocks'=>$rows,
                'relations'=>$this->relations($owner,$rows,$lock)];
        }
        if (count(array_unique($states))!==1) { throw new RuntimeException('M36 partial package; no writes.'); }
        $plan['state']=$states[0]; $plan['protected']=$this->protectedFingerprints($targetIds);
        ksort($plan['sources']); $plan['sha256']=self::digest($plan); return $plan;
    }

    /** Read-only exact reviewed hashes. Dirty shared ROOT files are never copied from this worktree. */
    public static function reviewedSharedSources(string $source, string $directory): array
    {
        $path = $directory.'/'.self::SHARED_REVIEW;
        if (!is_file($path) || is_link($path)) { throw new RuntimeException('Missing exact reviewed M36 shared-source evidence.'); }
        $review = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($review['version'] ?? null) !== 1 || ($review['base_sha'] ?? null) !== self::ACCEPTED_BASE
            || array_column($review['files'] ?? [], 'path') !== self::SHARED_VIEWS) {
            throw new RuntimeException('M36 reviewed shared-source allowlist differs.');
        }
        foreach ($review['files'] as $file) {
            if (array_keys($file) !== ['path', 'root_sha256', 'worktree_sha256', 'before_sha256', 'before_basename']
                || !preg_match('/^[a-z0-9-]+\.bin$/D', $file['before_basename'])) {
                throw new RuntimeException('M36 shared-source record is incomplete.');
            }
            foreach (['root_sha256', 'worktree_sha256', 'before_sha256'] as $key) {
                if (!is_string($file[$key]) || !preg_match('/^[a-f0-9]{64}$/D', $file[$key])) {
                    throw new RuntimeException('M36 shared-source digest is invalid.');
                }
            }
            $before = $directory.'/'.$file['before_basename'];
            $working = M36LocalTargetGuard::ROOT.'/'.$file['path']; $canonical = $source.'/'.$file['path'];
            if (is_link($before) || is_link($working) || is_link($canonical) || !is_file($before)
                || hash_file('sha256', $before) !== $file['before_sha256']
                || hash_file('sha256', $working) !== $file['root_sha256']
                || hash_file('sha256', $canonical) !== $file['worktree_sha256']) {
                throw new RuntimeException('M36 reviewed shared-source bytes changed.');
            }
        }
        return $review['files'];
    }

    private function assertOwner(array $r,array $owner,string $name,int $position): void
    {
        if ($r['seeder']!==$name || $r['locale']!=='uk' || $r['page_id']!==$owner['id']
            || $r['page_category_id']!==$owner['page_category_id'] || (int)$r['sort_order']!==$position) {
            throw new RuntimeException('M36 exact row owner/locale/order conflict.');
        }
    }
}
