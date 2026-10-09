<?php

namespace App\Support;

use Illuminate\Support\Collection;
use RuntimeException;

/** Exact three-owner author projection. Does not read/write a database or select arbitrary views. */
final class M45FutureComparisonsPackage
{
    public const MASTER_PATH = 'docs/content/m45-authored-future-comparisons.v1.0.0.json';
    public const MASTER_SHA = '333b39ead3e5bb20c2bcde9c42878cf98f176158d7ae6324d0c6510b09b2afb7';
    public const BEFORE = 'database/content-patches/m45-authored-future-comparisons-before.json';
    public const BEFORE_SHA = '7f9b208f9e52dc566556e5ca41de121af8ca2fada2c3c055bc9252b517caa9c0';
    public const SOURCE = 'database/content-patches/m45-authored-future-comparisons.v1.0.0.json';
    public const SOURCE_SHA = '209a0bdb62716eb5cd35718b0496d31eda6616f951c6a14c94f087092dc8ca19';
    public const BASE_SHA = 'e5b3ee339410afe3bd6b6bad33eb62fced4d6671';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFuturePerfectVsFutureContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder',
    ];
    private static array $parsed = [];
    private static function root(?string $root): string { return $root ?? base_path(); }
    public static function json(array $data): string { return M26DetailPackage::json($data); }
    private static function read(string $root, string $path, string $sha): array
    {
        $bytes = file_get_contents($root.'/'.$path);
        if (!is_string($bytes) || hash('sha256', $bytes) !== $sha || str_contains($bytes, "\r")
            || str_starts_with($bytes, "\xef\xbb\xbf") || !str_ends_with($bytes, "\n") || str_ends_with($bytes, "\n\n")) {
            throw new RuntimeException('M45 frozen source differs: '.$path);
        }
        $key=$root.'/'.$path.':'.$sha;
        return self::$parsed[$key] ??= json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    }
    public static function load(?string $root = null): array
    {
        $root = self::root($root);
        $before = self::read($root, self::BEFORE, self::BEFORE_SHA);
        $source = self::read($root, self::SOURCE, self::SOURCE_SHA);
        $master = self::authorMaster($before, $root);
        if ($source !== self::project($before, $master)) { throw new RuntimeException('M45 mechanical projection differs'); }
        return [$before, $source];
    }
    public static function authorMaster(array $before, ?string $root = null): array
    {
        $root = self::root($root);
        $master = self::read($root, self::MASTER_PATH, self::MASTER_SHA);
        if (($before['base_sha'] ?? null) !== self::BASE_SHA || array_column($before['targets'] ?? [], 'identity') !== self::OWNERS
            || ($master['schema'] ?? null) !== 'gramlyze.m45.author.v1' || ($master['version'] ?? null) !== '1.0.0'
            || array_column($master['lessons'] ?? [], 'identity') !== self::OWNERS) {
            throw new RuntimeException('M45 author/base/owners differ');
        }
        return $master;
    }
    public static function validate(array $before, array $source, ?string $root = null): void
    {
        [$expectedBefore, $expectedSource] = self::load($root);
        if ($before !== $expectedBefore || $source !== $expectedSource) { throw new RuntimeException('M45 finite package differs'); }
    }
    /** Pure deterministic transfer; basic/detail are already author fields, never runtime summaries. */
    public static function project(array $before, array $master): array
    {
        $targets = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $record = $before['targets'][$i];
            $old = $record['before']; $after = $old; $owner = self::OWNERS[$i];
            if ($lesson['identity'] !== $owner || $record['identity'] !== $owner || $old['seeder']['class'] !== $owner
                || $old['page']['locale'] !== 'uk' || $old['page']['category']['slug'] !== 'maibutni-formy'
                || count($old['page']['blocks']) !== 7 || count($lesson['sections']) !== 5 || count($lesson['practice']) !== 6) {
                throw new RuntimeException('M45 exact author definition scope differs');
            }
            $after['page']['subtitle_html'] = '<p lang="uk">'.e($lesson['subtitle_uk']).'</p>';
            $after['page']['subtitle_text'] = $lesson['subtitle_uk'];
            $hero = json_decode($old['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
            $hero['intro'] = '<p lang="uk">'.e($lesson['hero_intro_uk']).'</p>'; $hero['rules'] = [];
            $hero['m45_v1'] = self::marker($lesson, 'hero', 0);
            $after['page']['blocks'][0]['body'] = self::json($hero);
            foreach ($lesson['sections'] as $section) {
                $slot = $section['slot'];
                if (!in_array($slot, [1,2,3,4,5], true)) { throw new RuntimeException('M45 section slot invalid'); }
                $after['page']['blocks'][$slot]['body'] = self::json([
                    'title' => $slot.'. '.$section['title'],
                    'm45_v1' => self::marker($lesson, 'section', $slot),
                    'author_section' => $section,
                ]);
            }
            $nav = json_decode($old['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach ($nav['items'] as &$item) {
                if (!isset($record['navigation_urls'][$item['url']])) { throw new RuntimeException('M45 navigation destination not reviewed'); }
                $item['url'] = $record['navigation_urls'][$item['url']];
            } unset($item);
            $after['page']['blocks'][6]['body'] = self::json($nav);
            $practice = [
                'title' => 'Практика', 'm45_v1' => self::marker($lesson, 'practice', 7),
                'author_practice' => $lesson['practice'],
                'author_self_check' => [
                    'title' => 'Відповідь і пояснення', 'intro' => '<p lang="uk">Виконай шість завдань. Відповідь і пояснення відкриються після перевірки.</p>',
                    'prompts' => array_map(self::prompt(...), $lesson['practice']),
                    'answers' => array_map(self::feedback(...), $lesson['practice']),
                ],
                'cases' => array_map(fn ($task, $n) => self::task($task, $n + 1), $lesson['practice'], array_keys($lesson['practice'])),
                'linked_practice' => $record['linked_practice'],
            ];
            $after['page']['blocks'][] = ['type'=>'practice-set','column'=>'left','heading'=>null,
                'level'=>$old['page']['blocks'][0]['level'],'uuid_key'=>'m45-'.$lesson['key'].'-practice',
                'inherit_base_tags'=>false,'tags'=>[],'body'=>self::json($practice)];
            $protected = $after; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html','subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            if ($protected !== $old) { throw new RuntimeException('M45 projection changed protected metadata'); }
            $targets[] = ['identity'=>$owner,'path'=>$record['path'],'ancestry'=>['maibutni-formy'],'after'=>$after];
        }
        return ['schema'=>'gramlyze.m45.projection.v1','version'=>'1.0.0','base_sha'=>self::BASE_SHA,
            'master_sha256'=>self::MASTER_SHA,'targets'=>$targets];
    }
    private static function marker(array $lesson, string $role, int $slot): array
    {
        return ['revision'=>'1.0.0','master_sha256'=>self::MASTER_SHA,'lesson_key'=>$lesson['key'],'role'=>$role,'slot'=>$slot];
    }
    private static function prompt(array $task): string
    {
        $text = '<h4>'.e($task['title']).'</h4><p lang="uk" class="text-base text-muted-foreground mt-1 leading-relaxed" data-practice-instruction>'.e($task['prompt_uk']).'</p>';
        if (!empty($task['context_uk'])) { $text .= '<p lang="uk">'.e($task['context_uk']).'</p>'; }
        return $text;
    }
    private static function feedback(array $task): string
    {
        return M43NativeHtml::paragraphs($task['feedback']['paragraphs_uk'])
            .M43NativeHtml::examples($task['feedback']['answer_examples']);
    }
    private static function task(array $task, int $position): array
    {
        $controls = [];
        foreach ($task['controls'] as $c) {
            $control = ['id'=>$c['id'],'kind'=>$c['kind']==='tokens'?'manual':$c['kind'],
                'source_kind'=>$c['kind'],'label'=>$c['label_uk'],'required'=>true];
            foreach (['stimulus_en','stimulus_uk'] as $field) { if (isset($c[$field])) { $control[$field] = $c[$field]; } }
            if (in_array($c['kind'], ['select','choice'], true)) {
                $control['options'] = array_map(fn ($o) => ['value'=>$o['value'],'label'=>$o['label_uk']], $c['options']);
                $control['answer'] = $c['correct_value'];
            } else {
                $control['options']=[]; $control['answer']=$c['canonical_answer'];
                $control['accepted']=array_values(array_unique([$c['canonical_answer'],...($c['accepted_answers'] ?? [])]));
                $control['tokens']=$c['tokens'] ?? [];
            }
            $controls[] = $control;
        }
        return ['id'=>$task['id'],'source_index'=>$position,'scoring'=>'all_required_controls',
            'interaction'=>count($controls)>1?'compound':$controls[0]['kind'],'controls'=>$controls];
    }
    public static function hasStoredAuthor(array $data): bool
    {
        return isset($data['m45_v1'])
            || (is_array($data['author_section'] ?? null) && str_starts_with($data['author_section']['id'] ?? '', 'm45-'))
            || (is_array($data['author_practice'] ?? null) && str_starts_with($data['author_practice'][0]['id'] ?? '', 'm45-'));
    }
    /** A plain explanatory subtitle is not a new localized Page title. No query or model mutation. */
    public static function displayTitle(\App\Models\Page $page, string $locale): ?string
    {
        $owner=$page->getRawOriginal('seeder');
        if($locale!=='uk'||!in_array($owner,self::OWNERS,true)){return null;}
        try{
            [, $source]=self::load();
            foreach($source['targets'] as $target){
                $expected=$target['after'];
                if($target['identity']===$owner && $page->getRawOriginal('type')==='theory'
                    && $page->getRawOriginal('slug')===$expected['slug']
                    && $page->getRawOriginal('title')===$expected['page']['title']
                    && $page->getRawOriginal('text')===$expected['page']['subtitle_text']){
                    return $expected['page']['title'];
                }
            }
        }catch(\Throwable){ /* Other packages retain the existing localization algorithm. */ }
        return null;
    }
    private static function matches(object $block, string $identity, array $config, int $position): bool
    {
        foreach (['type','column','heading','level','css_class'] as $field) {
            if (($block->$field ?? null) !== ($config[$field] ?? null)) { return false; }
        }
        $bodyMatches=($config['type']??null)==='subtitle'
            ? ($block->body??null)===$config['body']
            : is_string($block->body??null) && json_decode($block->body,true,flags:JSON_THROW_ON_ERROR)===json_decode($config['body'],true,flags:JSON_THROW_ON_ERROR);
        return ($block->locale ?? null)==='uk' && ($block->seeder ?? null)===$identity
            && ($block->uuid ?? null)===M26DetailPackage::uuid($identity,$config,$position)
            && (int)($block->sort_order ?? -1)===$position
            && $bodyMatches;
    }
    private static function pageTarget(Collection $blocks): ?array
    {
        if($blocks->isEmpty()||$blocks->contains(fn($b)=>($b->locale??null)!=='uk')){return null;}
        $owners=$blocks->map(fn($b)=>$b->seeder??null)->unique()->values();
        if($owners->count()!==1||!in_array($owners[0],self::OWNERS,true)){return null;}
        try {
            [$before,$source]=self::load();
            foreach ($source['targets'] as $i=>$target) {
                $owner=$target['identity']; $page=$target['after']['page'];
                if ($blocks->count()!==count($page['blocks'])+1 || $blocks->map(fn($b)=>$b->uuid)->unique()->count()!==$blocks->count()) { continue; }
                $subtitle=['type'=>'subtitle','column'=>'header','heading'=>null,'css_class'=>null,
                    'level'=>$page['subtitle_level']??null,'uuid_key'=>$page['subtitle_uuid_key']??'subtitle','body'=>$page['subtitle_html']];
                $configs=[$subtitle,...$page['blocks']]; $valid=true;
                foreach($configs as $position=>$config) {
                    $block=$blocks->first(fn($b)=>$b->uuid===M26DetailPackage::uuid($owner,$config,$position));
                    if(!$block||!self::matches($block,$owner,$config,$position)){$valid=false;break;}
                }
                if($valid){return ['target'=>$target,'before'=>$before['targets'][$i]['before']];}
            }
        } catch (\Throwable) { /* Complete old/foreign collections keep their existing renderer. */ }
        return null;
    }
    public static function allowsTheoryContext(Collection $blocks,string $locale): bool
    {
        return $locale==='uk' && self::pageTarget($blocks)!==null;
    }
    public static function presentation(object $block,array $data): ?array
    {
        if(!isset($data['m45_v1'])||($block->locale??null)!=='uk'){return null;}
        try {
            [, $source]=self::load();
            foreach($source['targets'] as $target) foreach($target['after']['page']['blocks'] as $slot=>$config) {
                if(!self::matches($block,$target['identity'],$config,$slot+1)){continue;}
                if($data!==json_decode($config['body'],true,flags:JSON_THROW_ON_ERROR)){return null;}
                $role=$data['m45_v1']['role']??null;
                if(!in_array($role,['hero','section','practice'],true)){return null;}
                return ['data'=>$data,'points'=>[],'legacy_section'=>null,'legacy_practice_id'=>null,
                    'native_view'=>match($role){'hero'=>'engram.theory.blocks-v3.hero','section'=>'engram.theory.blocks-v3.m45-section',default=>'engram.theory.blocks-v3.m45-practice-ui'}];
            }
        } catch (\Throwable) { /* Keep complete escaped author fallback. */ }
        return null;
    }
    public static function preserveCourseBlocks(Collection $blocks): Collection
    {
        $valid=self::pageTarget($blocks);if($valid===null){return $blocks;}
        $target=$valid['target'];$before=$valid['before']['page'];$owner=$target['identity'];
        $subtitle=['type'=>'subtitle','column'=>'header','body'=>$before['subtitle_html'],'uuid_key'=>$before['subtitle_uuid_key']??'subtitle'];
        $old=[M26DetailPackage::uuid($owner,$subtitle,0)=>$subtitle];
        foreach($before['blocks'] as $i=>$config){$old[M26DetailPackage::uuid($owner,$config,$i+1)]=$config;}
        return $blocks->filter(fn($b)=>isset($old[$b->uuid]))->map(function($block)use($old){
            $clone=clone $block;$config=$old[$block->uuid];$clone->type=$config['type'];$clone->body=$config['body'];return $clone;
        })->sortBy('sort_order')->values();
    }
}
