<?php

namespace App\Support;

/** Finite grouping/instructions in the established native exercise components. */
final class PassiveVoicePracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE = 'docs/content/passive-voice-practice-compact.v1.json';
    public const SOURCE_SHA = '3b4a6bbb3865e9e22a845b69bf8a3481b01440fab26b278130ffacd59f57482c';
    public const SUPPLEMENT = PassiveVoicePracticeSupplement::class;

    public static function compile(): array
    {
        $document=['schema_version'=>1,'source'=>['path'=>PassiveVoicePracticeSupplement::SOURCE,'sha256'=>PassiveVoicePracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach (PassiveVoicePracticeSupplement::load()['targets'] as $target) {
            $items=[]; $groups=[]; $byKind=[];
            $nativeDisplay=null;
            if (isset($target['before_data']['m35_v1'])) {
                // Reuse the already accepted exact-source separation of candidates
                // and author explanations; compact cards must not leak the long key again.
                $definition=json_decode(file_get_contents(base_path($target['definition'])),true,flags:JSON_THROW_ON_ERROR);
                $binding=$target['binding']; unset($binding['body_sha256']);
                $block=(object)($binding+['body'=>$definition['page']['blocks'][$binding['sort_order']-1]['body']]);
                [$class]=PassiveVoicePracticeSupplement::specs()[$target['topic']];
                $presentation=M42NativeDesignPackage::decorate($block,$target['before_data'],$class::presentation($block,$target['before_data']),true);
                $nativeDisplay=TheoryPracticePresentation::nativeDisplay($block,$target['before_data'],$presentation['m42_native_design']??null,true);
                if ($nativeDisplay===null) { throw new \RuntimeException('Passive legacy candidate display binding differs.'); }
            }
            foreach (['selects','choices','inputs','rephrase'] as $kind) {
                $items[$kind]=[];
                foreach ($target['after_data'][$kind]??[] as $index=>$item) {
                    $stimuli=[];
                    if ($kind==='inputs') {
                        $practiceKind=$item['practice_kind'];
                        $instruction=match($practiceKind) {'gap'=>'Заповни пропуск англійською.','translation'=>'Переклади українську умову англійською.','order'=>'Розташуй групи слів у правильному порядку.',default=>'Виконай авторське завдання без зміни фактів.'};
                        if (!empty($item['context'])) { $stimuli[]=['lang'=>'uk','text'=>self::plainText($item['context'])]; }
                        if ($practiceKind==='gap') { $stimuli[]=['lang'=>'en','text'=>self::plainText($item['before'])]; }
                        if ($practiceKind!=='gap' && !empty($item['after'])) { $stimuli[]=['lang'=>'uk','text'=>self::plainText($item['after'])]; }
                        $hint=$item['verb_hint'];
                        if (!in_array($hint,array_column($stimuli,'text'),true)) { $stimuli[]=['lang'=>'uk','text'=>$hint]; }
                        if ($practiceKind==='gap' && count($item['accepted'])>1) {
                            $stimuli[]=['lang'=>'en','text'=>'('.implode(' / ',$item['accepted']).')'];
                        }
                        $bucket=$practiceKind;
                    } else {
                        $instruction=$kind==='selects'?'Обери точну форму або відповідь.':'Обери правильне твердження.';
                        foreach (['label','context','prompt'] as $field) {
                            if (!empty($item[$field])) {
                                $text=$field==='prompt'?($nativeDisplay[$kind][$index]['prompt_html']??$item[$field]):$item[$field];
                                $stimuli[]=['lang'=>'uk','text'=>self::plainText($text)];
                            }
                        }
                        $bucket=$kind;
                    }
                    $items[$kind][]=['index'=>$index,'source_item_sha256'=>PassiveVoicePracticeSupplement::digest($item),'instruction_uk'=>$instruction,'stimuli'=>$stimuli];
                    $byKind[$kind][$bucket][]=$index;
                }
            }
            foreach ($byKind as $kind=>$buckets) {
                foreach ($buckets as $bucket=>$indices) {
                    foreach (array_chunk($indices,2) as $n=>$chunk) {
                        $title=match($bucket) {'selects'=>'Обери форму','choices'=>'Збережи зміст','gap'=>'Заповни пропуски','translation'=>'Переклади речення','order'=>'Упорядкуй групи слів',default=>'Авторське редагування'};
                        $instruction=match($bucket) {'gap'=>'Уведи лише відсутню частину, не повторюючи наведені слова.','translation','order'=>'Відповідай вручну або натискай перемішані групи слів.','selects','choices'=>'Збережи факти умови й обери одну відповідь.',default=>'Введи повну відповідь; збережи всі фактичні обмеження.'};
                        $groups[]=['key'=>'pv-'.$target['topic'].'-'.$bucket.'-'.($n+1),'kind'=>$kind,'indices'=>$chunk,'title'=>$title,'instruction'=>$instruction];
                    }
                }
            }
            $document['targets'][]=['topic'=>$target['topic'],'binding'=>$target['binding'],'after_data_sha256'=>$target['after_data_sha256'],'items'=>$items,'groups'=>$groups];
        }
        return $document;
    }

    private static function plainText(string $html): string
    {
        return trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags(str_replace(['<br>','<br/>','<br />'], ' ', $html)),ENT_QUOTES|ENT_HTML5,'UTF-8')));
    }

    public static function validate(array $document): void
    {
        parent::validate($document);
        if ($document!==self::compile()) { throw new \RuntimeException('Passive native compact finite projection differs.'); }
    }
}
