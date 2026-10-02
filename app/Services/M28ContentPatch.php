<?php

namespace App\Services;

use App\Support\M28EmphasisPackage as Package;
use RuntimeException;

/** Finite three-owner projection; reuses audited exclusive backup/transaction primitives. */
class M28ContentPatch extends M26ContentPatch
{
    public const NAMES = [
        'Database\\Seeders\\Page_V3\\SentenceStructure\\CleftSentencesBasicsTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionBasicsTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\AdvancedFrontingAndEmphasisTheorySeeder',
    ];
    protected const ID = 'm28-m12-emphasis-inversion-v1';
    protected function allowedUpdateFields(): array { return ['type', 'body']; }
    protected function localGuard(): string { return M28LocalTargetGuard::class; }
    protected function expectedInsertCount(): int
    {
        [, $package] = Package::load(dirname($this->databasePath));
        return array_sum(array_map(fn ($t) => count($t['after']['page']['blocks']) - 2, $package['targets']));
    }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath); [$manifest, $package] = Package::load($root);
        Package::validate($manifest, $package);
        if (array_column($package['targets'], 'identity') !== self::NAMES) { throw new RuntimeException('M28 exact three-owner scope differs.'); }
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => static::NAMES, 'connection' => $this->connection(),
            'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => []];
        foreach ([Package::BEFORE, Package::SOURCE, 'app/Support/M28EmphasisPackage.php',
            'app/Services/M28ContentPatch.php', 'app/Services/M26ContentPatch.php', 'app/Services/M28LocalTargetGuard.php',
            'app/Services/M11LocalTargetGuard.php', 'app/Console/Commands/PatchM28Content.php',
            'resources/views/theory/partials/content-block.blade.php', 'resources/views/theory/partials/point-detail-fragment.blade.php',
            'resources/views/theory/partials/point-disclosure.blade.php', 'resources/views/theory/partials/section-disclosure.blade.php',
            'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
            'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
            'resources/views/engram/theory/blocks-v3/practice-set.blade.php'] as $path) {
            if (!is_file($root.'/'.$path)) { throw new RuntimeException('M28 code source missing.'); }
            $plan['sources'][$path] = hash_file('sha256', $root.'/'.$path);
        }
        $states = []; $targetIds = [];
        foreach ($package['targets'] as $i => $target) {
            $name = $target['identity']; $before = $manifest['targets'][$i]['before']; $after = $target['after'];
            $path = $target['path']; $bytes = file_get_contents($root.'/'.$path);
            try { $source=json_decode($bytes,true,flags:JSON_THROW_ON_ERROR); }
            catch (\JsonException) { throw new RuntimeException('M28 canonical definition is invalid JSON.'); }
            if ($source !== $after) { throw new RuntimeException('M28 canonical definition differs.'); }
            $plan['sources'][$path] = hash('sha256', $bytes);
            foreach (glob(dirname($root.'/'.$path).'/localizations/*.json') ?: [] as $p) {
                $plan['sources'][substr(str_replace('\\','/',$p), strlen(str_replace('\\','/',$root))+1)] = hash_file('sha256',$p);
            }
            $owners = $this->rows('pages', fn ($q) => $q->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('M28 missing/ambiguous owner.'); }
            $owner = $owners[0]; $category = $this->categoryChain($owner['page_category_id'], $lock); $c = $before['page'];
            if (array_column($category,'slug') !== $target['ancestry'] || $owner['slug'] !== $before['slug']
                || $owner['type'] !== 'theory' || $owner['title'] !== $c['title'] || $owner['text'] !== $c['subtitle_text']) {
                throw new RuntimeException('M28 owner/category/metadata conflict.');
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
                if (count($global) !== 1) { throw new RuntimeException('M28 missing/ambiguous old UUID.'); }
                $r = $global[0]; $new = $newConfigs[$position];
                $this->assertOwner($r,$owner,$name,$position);
                foreach (['column','heading','level','css_class'] as $field) {
                    if ($r[$field] !== ($old[$field]??null) || ($new[$field]??null) !== ($old[$field]??null)) {
                        throw new RuntimeException('M28 old block protected field differs: '.$field);
                    }
                }
                if ($position === 2) {
                    $targetIds[]=$r['id'];
                    $rowStates[]=$this->candidate($plan['updates'],'text_blocks',$r,$name,
                        ['type'=>$old['type'],'body'=>$old['body']],['type'=>$new['type'],'body'=>$new['body']]);
                } elseif ($r['type'] !== $old['type'] || $r['body'] !== $old['body']) { throw new RuntimeException('M28 subtitle/hero changed.'); }
            }
            $state=$rowStates[0]; $present=0;
            foreach (array_slice($newConfigs,3, null, true) as $position=>$b) {
                $uuid=$this->resolveUuid($name,$b,$position);
                $global=$this->rows('text_blocks',fn($q)=>$q->where('uuid',$uuid),$lock);
                $fields=['uuid'=>$uuid,'page_id'=>$owner['id'],'page_category_id'=>$owner['page_category_id'],'locale'=>'uk',
                    'type'=>$b['type'],'column'=>$b['column'],'heading'=>$b['heading']??null,'css_class'=>$b['css_class']??null,
                    'sort_order'=>$position,'body'=>$b['body'],'level'=>$b['level']??null,'seeder'=>$name];
                if ($state==='before') {
                    if ($global) { throw new RuntimeException('M28 unknown/partial UUID; no writes.'); }
                    $plan['inserts'][]=['table'=>'text_blocks','seeder'=>$name,'fields'=>$fields,'sha256'=>self::digest($fields)];
                } else {
                    if (count($global)!==1) { throw new RuntimeException('M28 inserted block missing/ambiguous.'); }
                    $r=$global[0]; $this->assertOwner($r,$owner,$name,$position);
                    foreach ($fields as $f=>$v) { if ($r[$f]!==$v) { throw new RuntimeException('M28 inserted row manually edited.'); } }
                    if (empty($r['created_at']) || $r['created_at']!==$r['updated_at']) { throw new RuntimeException('M28 inserted timestamps changed.'); }
                    $targetIds[]=$r['id']; $present++;
                }
            }
            if (count($uk)!==count($oldConfigs)+$present) { throw new RuntimeException('M28 unexpected UK rows; no writes.'); }
            $states[]=$state;
            $plan['pages'][$name]=['page'=>$owner,'category_ancestry'=>$category,'blocks'=>$rows,
                'relations'=>$this->relations($owner,$rows,$lock)];
        }
        if (count(array_unique($states))!==1) { throw new RuntimeException('M28 partial package; no writes.'); }
        $plan['state']=$states[0]; $plan['protected']=$this->protectedFingerprints($targetIds);
        ksort($plan['sources']); $plan['sha256']=self::digest($plan); return $plan;
    }

    private function assertOwner(array $r,array $owner,string $name,int $position): void
    {
        if ($r['seeder']!==$name || $r['locale']!=='uk' || $r['page_id']!==$owner['id']
            || $r['page_category_id']!==$owner['page_category_id'] || (int)$r['sort_order']!==$position) {
            throw new RuntimeException('M28 exact row owner/locale/order conflict.');
        }
    }
}
