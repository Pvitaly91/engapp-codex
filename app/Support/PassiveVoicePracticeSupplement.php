<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Exact canonical-UK projection; no stored models, identities, progress or frozen releases are changed. */
final class PassiveVoicePracticeSupplement
{
    public const SOURCE = 'docs/content/passive-voice-practice-supplement.v1.json';
    public const SOURCE_SHA = 'e551573b2e26011cfe32616df7853539ab952638088172a89007325a6bfd69ac';

    public static function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public static function specs(): array
    {
        return [
            'formation'=>[M52PassiveBasicsPackage::class,0], 'causative'=>[M53PassiveConstructionsPackage::class,0],
            'mistakes'=>[M55PassiveReviewInfinitivePackage::class,0], 'style'=>[M55PassiveReviewInfinitivePackage::class,1],
            'get'=>[M53PassiveConstructionsPackage::class,1], 'impersonal'=>[M53PassiveConstructionsPackage::class,2],
            'modals'=>[M52PassiveBasicsPackage::class,2], 'negatives'=>[M52PassiveBasicsPackage::class,1],
            'phrasal'=>[M54PassivePatternsPackage::class,0], 'restrictions'=>[M54PassivePatternsPackage::class,1],
            'two-object'=>[M54PassivePatternsPackage::class,2], 'gerund'=>[M56GerundSimplePassivesPackage::class,0],
            'infinitive'=>[M55PassiveReviewInfinitivePackage::class,2], 'agent'=>[M59PassiveFutureAgentReviewPackage::class,1],
            'future-continuous'=>[M59PassiveFutureAgentReviewPackage::class,0], 'future-perfect'=>[M58PastFuturePassivesPackage::class,2],
            'future-simple'=>[M58PastFuturePassivesPackage::class,1], 'past-continuous'=>[M57ContinuousPerfectPassivesPackage::class,1],
            'past-perfect'=>[M58PastFuturePassivesPackage::class,0], 'past-simple'=>[M56GerundSimplePassivesPackage::class,2],
            'present-continuous'=>[M57ContinuousPerfectPassivesPackage::class,0], 'present-perfect'=>[M57ContinuousPerfectPassivesPackage::class,2],
            'present-simple'=>[M56GerundSimplePassivesPackage::class,1], 'advanced'=>[M59PassiveFutureAgentReviewPackage::class,2],
            'reporting'=>[M35PassiveReportingPackage::class,0], 'complex-causative'=>[M35PassiveReportingPackage::class,1],
            'complex-impersonal'=>[M35PassiveReportingPackage::class,2],
        ];
    }

    public static function shuffled(array $groups, string $seed): array
    {
        $rows = [];
        foreach ($groups as $i => $text) { $rows[] = ['text'=>$text,'sort'=>hash('sha256',$seed.'|'.$i.'|'.$text)]; }
        usort($rows, static fn ($a,$b)=>strcmp($a['sort'],$b['sort']));
        $shuffled = array_column($rows,'text');
        if ($shuffled === $groups) { $shuffled[] = array_shift($shuffled); }
        return $shuffled;
    }

    private static function uk(string $value): void
    {
        if (trim($value) === '' || !preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$value)
            || preg_match('/[A-Za-z<>]|\{a\d+\}|&(?:#[0-9]+|[a-z]+);/iu',$value)) {
            throw new RuntimeException('Passive native hint must be plain contextual Ukrainian.');
        }
    }

    private static function groups(string $ordered, string $answer): array
    {
        $groups = explode('|',$ordered);
        if (count($groups)<2 || implode(' ',$groups)!==$answer) { throw new RuntimeException('Passive native grouping changed an answer.'); }
        foreach ($groups as $group) {
            if (trim($group)!==$group || $group==='' || str_contains($group,'/') || count(preg_split('/\s+/u',$group))>3) {
                throw new RuntimeException('Passive native groups need one to three words.');
            }
        }
        return $groups;
    }

    private static function labels(array $groups, array $accepted): array
    {
        $labels=[]; $canonical=implode(' ',$groups); $words=preg_split('/\s+/u',$canonical);
        foreach ($accepted as $alternative) {
            if ($alternative===$canonical) { continue; }
            $other=preg_split('/\s+/u',$alternative); $left=0;
            while (isset($words[$left],$other[$left]) && $words[$left]===$other[$left]) { $left++; }
            $right=0;
            while ($right<count($words)-$left && $right<count($other)-$left && $words[count($words)-1-$right]===$other[count($other)-1-$right]) { $right++; }
            $old=implode(' ',array_slice($words,$left,count($words)-$left-$right));
            $new=implode(' ',array_slice($other,$left,count($other)-$left-$right));
            if ($old==='' || $new==='') { continue; }
            foreach ($groups as $group) {
                if (str_contains($group,$old)) { $alias=str_replace($old,$new,$group); $labels[$group][$alias]=true; break; }
            }
        }
        $out=[];
        foreach ($labels as $group=>$aliases) { $out[$group]=$group.' ('.implode(' / ',array_keys($aliases)).')'; }
        return $out;
    }

    public static function compile(): array
    {
        if (array_keys(self::specs())!==PassiveVoiceNativePlans::TOPICS || array_keys(PassiveVoiceNativePlans::additions())!==PassiveVoiceNativePlans::TOPICS) {
            throw new RuntimeException('Passive native exact topic order differs.');
        }
        $document=['schema_version'=>1,'package'=>'passive-voice-native-practice-supplement','status'=>'frozen','targets'=>[]];
        $releases=[];
        foreach (self::specs() as $topic=>[$class,$owner]) {
            if (!isset($releases[$class])) { $releases[$class]=$class::load(); $class::validate(...$releases[$class]); }
            $target=$releases[$class][1]['targets'][$owner];
            $definition=$target['path']; $path=dirname(__DIR__,2).'/'.$definition;
            $current=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
            if (($current['seeder']['class']??null)!==$target['identity'] || ($current['page']['locale']??null)!=='uk') {
                throw new RuntimeException('Passive native canonical definition owner differs.');
            }
            $slots=[];
            foreach ($current['page']['blocks'] as $slot=>$source) { if ($source['type']==='practice-set') { $slots[$slot]=$source; } }
            if (count($slots)!==1) { throw new RuntimeException('Passive native exact practice slot missing.'); }
            $slot=array_key_first($slots); $stored=$slots[$slot];
            if ($class===M35PassiveReportingPackage::class) {
                $pageId=[296,305,319][$owner]; $categoryId=1;
                $localUrl='http://gramlyze.loc/theory/passive-voice/'.$target['slug'];
            } else {
                $pageId=$class::PAGE_IDS[$owner]; $categoryId=$class::CATEGORY_IDS[$owner];
                $localUrl=$target['local_url'];
            }
            $binding=['seeder'=>$target['identity'],'page_id'=>$pageId,'page_category_id'=>$categoryId,
                'uuid'=>M26DetailPackage::uuid($target['identity'],$stored,$slot+1),'locale'=>'uk','type'=>'practice-set',
                'column'=>$stored['column']??null,'sort_order'=>$slot+1,'level'=>$stored['level']??null,
                'heading'=>$stored['heading']??null,'css_class'=>$stored['css_class']??null];
            $block=(object)($binding+['body'=>$stored['body']]); $raw=json_decode($stored['body'],true,flags:JSON_THROW_ON_ERROR);
            $presentation=$class::presentation($block,$raw);
            if (!is_array($presentation['data']??null)) { throw new RuntimeException('Passive native source no longer matches the frozen presenter: '.$topic); }
            $before=$presentation['data']; $after=$before; $updates=[]; $labels=[];
            foreach ($before['inputs'] as $i=>$input) {
                if ($class===M35PassiveReportingPackage::class) {
                    $hint=[
                        'reporting'=>['Виправ узгодження та структуру обох повідомлень; збережи однаковий зміст і порядок двох речень.','Збережи джерело, обидва дні та час прибуття; окремо зазнач відсутність незалежного підтвердження.'],
                        'complex-causative'=>['Розрізни замовлену послугу та власноручну попередню дію; поясни виконавця й часові відношення.','Збережи нинішню домовленість про майбутній ремонт та окреме очікування готовності, не додаючи гарантії.'],
                        'complex-impersonal'=>['Збережи джерело, двох виконавців і поточний процес; використай повне підрядне речення.','Збережи джерело, обидва дні, кількість і саме друк; окремо зазнач межі підтверджених відомостей.'],
                    ][$topic][$i];
                    $after['inputs'][$i]['practice_kind']='editing';
                    $after['inputs'][$i]['punctuation_sensitive']=false;
                    $ordered=null;
                } elseif (isset(PassiveVoiceNativePlans::retained()[$topic])) {
                    [$ordered,$hint]=PassiveVoiceNativePlans::retained()[$topic][$i];
                    $groups=self::groups($ordered,$input['answer']);
                    $shuffled=self::shuffled($groups,$topic.':retained:'.$i);
                    $after['inputs'][$i]['before']=implode(' / ',$shuffled);
                    $after['inputs'][$i]['practice_kind']='order';
                } else {
                    $hint=$input['verb_hint']; $ordered=null;
                    $after['inputs'][$i]['practice_kind']=($input['exercise_type']??null)==='translation'?'translation':'order';
                }
                self::uk($hint); $after['inputs'][$i]['verb_hint']=$hint;
                $updates[]=['index'=>$i,'kind'=>$after['inputs'][$i]['practice_kind'],'ordered_groups'=>$ordered===null?null:explode('|',$ordered),'hint_uk'=>$hint];
            }
            $added=[]; $kindCounts=[];
            foreach (PassiveVoiceNativePlans::additions()[$topic] as $row) {
                [$kind,$condition,$ordered,$hint]=$row; self::uk($hint);
                $n=($kindCounts[$kind]??0)+1; $kindCounts[$kind]=$n;
                $id='pv-native-'.$topic.'-'.($kind==='translation'?'translate':$kind).'-'.$n;
                $groups=[]; $shuffled=[];
                if ($kind==='gap') {
                    if (substr_count($condition,'{a1}')!==1 || preg_match('/\{a[2-9]/u',$condition)) { throw new RuntimeException('Passive native gap needs exactly one phrase slot.'); }
                    $answer=$ordered; $visible=str_replace('{a1}','____',$condition); $afterText=$hint;
                } else {
                    self::uk($condition); $answer=str_replace('|',' ',$ordered); $groups=self::groups($ordered,$answer);
                    $shuffled=self::shuffled($groups,$id); $visible=implode(' / ',$shuffled); $afterText=$condition;
                }
                $accepted=array_values(array_unique(array_merge([$answer],$row[4]??[])));
                if ($kind==='gap' && count($accepted)>1) { $afterText.=' Допустимі варіанти: ('.implode(' / ',array_slice($accepted,1)).').'; }
                $index=count($after['inputs']);
                $after['inputs'][]=['id'=>$id,'practice_kind'=>$kind,'before'=>$visible,'after'=>$afterText,'verb_hint'=>$hint,
                    'answer'=>$answer,'accepted'=>$accepted,'feedback'=>array_fill_keys(array_map('mb_strtolower',$accepted),'Правильно. '.$hint)];
                if ($groups!==[]) { $tokenLabels=self::labels($groups,$accepted); if ($tokenLabels!==[]) { $labels[$index]=$tokenLabels; } }
                $added[]=['id'=>$id,'index'=>$index,'kind'=>$kind,'condition'=>$condition,'answer'=>$answer,'accepted'=>$accepted,
                    'hint_uk'=>$hint,'ordered_groups'=>$groups,'shuffled_groups'=>$shuffled];
            }
            $after['passive_voice_practice_supplement']=['revision'=>'v1','topic'=>$topic];
            $document['targets'][]=['topic'=>$topic,'local_url'=>$localUrl,'definition'=>$definition,'definition_sha256'=>hash_file('sha256',$path),
                'release'=>$class::SOURCE,'release_sha256'=>$class::SOURCE_SHA,'focus_level'=>$binding['level'],
                'binding'=>$binding+['body_sha256'=>hash('sha256',$stored['body'])],
                'before_data'=>$before,'before_data_sha256'=>self::digest($before),'after_data'=>$after,'after_data_sha256'=>self::digest($after),
                'input_updates'=>$updates,'added'=>$added,'input_token_labels'=>$labels];
        }
        return $document;
    }

    public static function load(): array
    {
        $path=dirname(__DIR__,2).'/'.self::SOURCE;
        if (!is_file($path) || is_link($path) || !hash_equals(self::SOURCE_SHA,hash_file('sha256',$path))) { throw new RuntimeException('Passive native supplement bytes differ.'); }
        static $document;
        if ($document===null) { $candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR); self::validate($candidate); $document=$candidate; }
        return $document;
    }

    public static function validate(array $document): void
    {
        if ($document!==self::compile()) { throw new RuntimeException('Passive native finite source projection differs.'); }
    }

    private static function entry(object $block,array $data,bool $projected): ?array
    {
        if (app()->getLocale()!=='uk' || ($block->locale??null)!=='uk' || ($block->type??null)!=='practice-set'
            || !str_starts_with($block->seeder??'','Database\\Seeders\\Page_V3\\PassiveVoice\\')) { return null; }
        try {
            foreach (self::load()['targets'] as $target) {
                foreach ($target['binding'] as $field=>$expected) { if ($field!=='body_sha256' && ($block->$field??null)!==$expected) { continue 2; } }
                if (is_string($block->body??null) && hash_equals($target['binding']['body_sha256'],hash('sha256',$block->body))
                    && $data===$target[$projected?'after_data':'before_data']) { return $target; }
            }
        } catch (Throwable) { /* Changed owners, metadata, sources and callers retain the complete previous native view. */ }
        return null;
    }

    public static function project(object $block,array $data,bool $canonicalCaller): array { return $canonicalCaller?(self::entry($block,$data,false)['after_data']??$data):$data; }
    public static function isProjected(object $block,array $data): bool { return self::entry($block,$data,true)!==null; }
    public static function originalDataForDisplay(object $block,array $data): array { return self::entry($block,$data,true)['before_data']??$data; }
    public static function inputTokenLabels(object $block,array $data): array
    {
        $entry=self::entry($block,$data,true);
        if ($entry===null) { return []; }
        $before=$entry['before_data'];
        return $entry['input_token_labels']+PresentSimplePassivePracticeQuality::inputTokenLabels($block,$before)
            +PastSimplePassivePracticeQuality::inputTokenLabels($block,$before)+FutureSimplePassivePracticeQuality::inputTokenLabels($block,$before);
    }

    public static function projectWidget(object $question,?string $blockUuid,array $payload,bool $canonicalCaller=false): array
    {
        return PassiveVoiceWidgetHints::project($question,$blockUuid,$payload,$canonicalCaller);
    }
}
