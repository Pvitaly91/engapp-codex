<?php

namespace App\Services;

use App\Support\M39AuthoredRevisionPackage as Author;
use App\Support\M39PracticeUiPackage as Package;
use RuntimeException;

/** Only the three existing M39 practice bodies. No new rows or educational rewriting. */
class M39PracticeUiPatch extends M26ContentPatch
{
    public const NAMES = M39ContentPatch::NAMES;
    public const SHARED_VIEWS = ['resources/views/theory/partials/content-block.blade.php', 'resources/views/engram/theory/blocks-v3/practice-set.blade.php'];
    public const SHARED_REVIEW = 'm39-practice-ui-shared-review-v1.json';
    protected const ID = 'm39-practice-ui-v1';
    protected function allowedUpdateFields(): array { return ['body']; }
    protected function expectedInsertCount(): int { return 0; }
    protected function localGuard(): string { return M39LocalTargetGuard::class; }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath);
        $package = Package::load($root); Package::validate($package);
        [, $author] = Author::load($root);
        if (array_column($package['targets'], 'identity') !== self::NAMES) { throw new RuntimeException('M39 UI exact three-owner scope differs.'); }
        $plan = ['patch'=>static::ID, 'version'=>1, 'names'=>self::NAMES, 'connection'=>$this->connection(),
            'sources'=>[], 'pages'=>[], 'updates'=>[], 'inserts'=>[]];
        if ($this->localTarget !== null) {
            self::reviewedSharedSources($root, $this->privateDirectory);
            $plan['sources']['private:'.self::SHARED_REVIEW]=hash_file('sha256',$this->privateDirectory.'/'.self::SHARED_REVIEW);
        }
        foreach ([Package::SOURCE, Author::BEFORE, Author::SOURCE,
            'app/Support/M39PracticeUiPackage.php', 'app/Support/M39AuthoredRevisionPackage.php',
            'app/Services/M39PracticeUiPatch.php', 'app/Services/M26ContentPatch.php',
            'app/Services/M39ContentPatch.php', 'app/Services/M39LocalTargetGuard.php', 'app/Services/M11LocalTargetGuard.php',
            'app/Console/Commands/PatchM39PracticeUi.php',
            'tools/diagnostics/run-m39-practice-ui-working-local.php', 'tools/diagnostics/project-m39-practice-ui.php',
            'tools/diagnostics/m39-practice-ui-projection.php', 'public/js/m39-practice-ui.js',
            'tools/diagnostics/inspect-m11-local-target.ps1',
            'docs/content/m23-authored-content.v1.json', 'docs/content/m23-author-sources.md',
            'resources/views/theory/partials/content-block.blade.php',
            'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
            'resources/views/engram/theory/blocks-v3/m39-practice-ui.blade.php'] as $path) {
            if (!is_file($root.'/'.$path) || is_link($root.'/'.$path)) { throw new RuntimeException('M39 UI source missing or linked: '.$path); }
            $plan['sources'][$path] = hash_file('sha256', $root.'/'.$path);
        }
        $states=[]; $practiceIds=[];
        foreach ($package['targets'] as $i=>$target) {
            $name=$target['identity']; $before=$target['before']; $after=$target['after'];
            if ($before !== $author['targets'][$i]['after']) { throw new RuntimeException('M39 UI before is not frozen accepted v1.'); }
            $oldBlocks=$before['page']['blocks']; $newBlocks=$after['page']['blocks'];
            if (count($oldBlocks)!==count($newBlocks)) { throw new RuntimeException('M39 UI block count changed.'); }
            $restored=$after;
            $practicePositions=[];
            foreach ($oldBlocks as $j=>$old) {
                $new=$newBlocks[$j];
                if ($old['type']==='practice-set') {
                    $restored['page']['blocks'][$j]['body']=$old['body'];
                    $practicePositions[]=$j;
                } elseif ($new!==$old) { throw new RuntimeException('M39 UI non-practice source changed.'); }
            }
            if (count($practicePositions)!==1 || $restored!==$before) { throw new RuntimeException('M39 UI changes fields beyond one practice body.'); }
            $path=$target['path']; $bytes=file_get_contents($root.'/'.$path);
            try { $canonical=json_decode($bytes,true,flags:JSON_THROW_ON_ERROR); }
            catch (\JsonException) { throw new RuntimeException('M39 UI canonical source is invalid JSON; no writes.'); }
            if ($canonical!==$after) { throw new RuntimeException('M39 UI canonical source differs.'); }
            $plan['sources'][$path]=hash('sha256',$bytes);
            foreach (glob(dirname($root.'/'.$path).'/localizations/*.json')?:[] as $p) {
                $plan['sources'][substr(str_replace('\\','/',$p),strlen(str_replace('\\','/',$root))+1)]=hash_file('sha256',$p);
            }
            $owners=$this->rows('pages',fn($q)=>$q->where('seeder',$name),$lock);
            if (count($owners)!==1) { throw new RuntimeException('M39 UI owner missing or ambiguous.'); }
            $owner=$owners[0]; $chain=$this->categoryChain($owner['page_category_id'],$lock);
            if ($owner['slug']!==$before['slug'] || $owner['type']!=='theory'
                || $owner['title']!==$before['page']['title'] || $owner['text']!==$before['page']['subtitle_text']
                || array_column($chain,'slug')!==M39ContentPatch::ANCESTRIES[$name]) {
                throw new RuntimeException('M39 UI owner/category/metadata conflict.');
            }
            $rows=$this->rows('text_blocks',fn($q)=>$q->where('page_id',$owner['id']),$lock);
            $uk=array_values(array_filter($rows,fn($r)=>$r['locale']==='uk'));
            $subtitle=['type'=>'subtitle','column'=>'header','heading'=>null,'level'=>$before['page']['subtitle_level']??null,
                'body'=>$before['page']['subtitle_html'],'uuid_key'=>$before['page']['subtitle_uuid_key']??'subtitle'];
            $configs=[$subtitle,...$oldBlocks];
            if (count($uk)!==count($configs)) { throw new RuntimeException('M39 UI unexpected UK rows.'); }
            foreach ($configs as $position=>$old) {
                $uuid=$this->resolveUuid($name,$old,$position);
                $global=$this->rows('text_blocks',fn($q)=>$q->where('uuid',$uuid),$lock);
                if (count($global)!==1) { throw new RuntimeException('M39 UI existing UUID missing or ambiguous.'); }
                $r=$global[0];
                if ($r['page_id']!==$owner['id'] || $r['page_category_id']!==$owner['page_category_id']
                    || $r['seeder']!==$name || $r['locale']!=='uk' || (int)$r['sort_order']!==$position) {
                    throw new RuntimeException('M39 UI existing row ownership differs.');
                }
                foreach (['type','column','heading','level','css_class'] as $field) {
                    if ($r[$field]!==($old[$field]??null)) { throw new RuntimeException('M39 UI protected row field differs: '.$field); }
                }
                if ($old['type']==='practice-set') {
                    $practiceIds[]=$r['id'];
                    $states[]=$this->candidate($plan['updates'],'text_blocks',$r,$name,
                        ['body'=>$old['body']],['body'=>$newBlocks[$position-1]['body']]);
                } elseif ($r['body']!==$old['body']) { throw new RuntimeException('M39 UI actual non-practice content changed.'); }
            }
            $plan['pages'][$name]=['page'=>$owner,'category_ancestry'=>$chain,'blocks'=>$rows,
                'relations'=>$this->relations($owner,$rows,$lock)];
        }
        if (count($practiceIds)!==count(self::NAMES) || count(array_unique($states))!==1
            || !in_array($states[0],['before','after'],true)) { throw new RuntimeException('M39 UI partial/manual state; no writes.'); }
        $plan['state']=$states[0]; $plan['protected']=$this->protectedFingerprints($practiceIds);
        ksort($plan['sources']); $plan['sha256']=self::digest($plan);
        return $plan;
    }

    public static function reviewedSharedSources(string $source, string $directory): array
    {
        $path=$directory.'/'.self::SHARED_REVIEW;
        if(!is_file($path)||is_link($path))throw new RuntimeException('Missing exact M39 UI shared-source review.');
        $review=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
        if(($review['version']??null)!==1||($review['patch']??null)!==static::ID
            ||array_column($review['files']??[],'path')!==self::SHARED_VIEWS)throw new RuntimeException('M39 UI shared-source allowlist differs.');
        foreach($review['files'] as $record){
            if(array_keys($record)!==['path','root_sha256','worktree_sha256','before_sha256','before_basename']
                ||!preg_match('/^m39-practice-ui-shared-before-v1-[01]\.bin$/D',$record['before_basename']))throw new RuntimeException('M39 UI shared-source record invalid.');
            foreach(['root_sha256','worktree_sha256','before_sha256'] as $key)if(!preg_match('/^[a-f0-9]{64}$/D',$record[$key]))throw new RuntimeException('M39 UI shared hash invalid.');
            $before=$directory.'/'.$record['before_basename'];$working=M39LocalTargetGuard::ROOT.'/'.$record['path'];$canonical=$source.'/'.$record['path'];
            if(is_link($before)||is_link($working)||is_link($canonical)||!is_file($before)
                ||hash_file('sha256',$before)!==$record['before_sha256']||hash_file('sha256',$working)!==$record['root_sha256']
                ||hash_file('sha256',$canonical)!==$record['worktree_sha256'])throw new RuntimeException('M39 UI reviewed source bytes changed.');
        }
        return $review['files'];
    }
}
