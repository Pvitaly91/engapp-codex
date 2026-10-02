<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M29SentenceStructurePackage as Package;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M29SentenceStructurePackageTest extends TestCase
{
    use RebuildsComposeTestSchema;
    protected function setUp(): void { parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite(); }
    private function bag(string $html): array
    {
        $text=html_entity_decode(strip_tags(str_replace(['<br>','</p>','</h4>','</td>','</th>','</li>'], ' ', $html)),ENT_QUOTES|ENT_HTML5,'UTF-8');
        preg_match_all('/[\p{L}\p{N}]+(?:[’\x27-][\p{L}\p{N}]+)*/u',$text,$m); $bag=array_count_values($m[0]); ksort($bag); return $bag;
    }
    public function test_exact_sources_metadata_hero_and_every_author_word_preserved_except_approved_selfcheck(): void
    {
        [$before,$package]=Package::load(); Package::validate($before,$package);
        self::assertCount(3,$package['targets']);
        foreach ($package['targets'] as $i=>$target) {
            self::assertSame($target['after'],json_decode(file_get_contents(base_path($target['path'])),true));
            $old=$before['targets'][$i]['before']; $body=$old['page']['blocks'][1]['body'];
            $body=preg_replace('~<section id="self-check-.*?</section>~s','',$body);
            $out='';
            foreach (array_slice($target['after']['page']['blocks'],1) as $b) {
                $d=json_decode($b['body'],true); if ($b['type']==='practice-set') { continue; }
                $out.=$d['title'].' '.($d['intro']??'').' '.($d['outro']??'').' ';
                foreach ($d['sections']??[] as $p) { $out.=$p['description'].' '; }
                foreach ($d['items']??[] as $p) { $out.=$p.' '; }
                foreach ($d['headers']??[] as $p) { $out.=$p.' '; }
                foreach ($d['rows']??[] as $r) { $out.=implode(' ',$r['cells']).' '; }
            }
            self::assertSame($this->bag($body),$this->bag($out),'No added, lost, translated or repeated author words.');
            self::assertSame($old['page']['blocks'][0],$target['after']['page']['blocks'][0]);
            $restore=$target['after']; $restore['page']['blocks']=$old['page']['blocks']; self::assertSame($old,$restore);
        }
    }
    public function test_native_views_own_independent_details_complete_fallback_and_unique_anchors(): void
    {
        [, $package]=Package::load(); $totals=[];
        foreach ($package['targets'] as $i=>$target) {
            $html=''; $count=0;
            foreach (array_slice($target['after']['page']['blocks'],1,null,true) as $j=>$b) {
                $block=new TextBlock; $block->forceFill(['id'=>1000+$i*100+$j,'uuid'=>M26DetailPackage::uuid($target['identity'],$b,$j+1),
                    'seeder'=>$target['identity'],'locale'=>'uk','sort_order'=>$j+1,'type'=>$b['type'],'body'=>$b['body'],'level'=>$b['level']??null]);
                $block->setRelation('tags',collect()); $block->setRelation('page',null);
                $fragment=view('theory.partials.content-block',['block'=>$block,'practiceQuestions'=>collect()])->render();
                $html.=$fragment; $d=json_decode($b['body'],true); $p=Package::presentation($block,$d);
                self::assertNotNull($p); $count+=count($p['points']);
                foreach ($p['points'] as $point) { self::assertStringContainsString($point['fragments'][0]['id'],$fragment); }
                $block->seeder='Foreign'; self::assertNull(Package::presentation($block,$d));
                $fallback=view('theory.partials.content-block',['block'=>$block,'practiceQuestions'=>collect()])->render();
                self::assertStringNotContainsString('data-theory-native-extension',$fallback);
                foreach ($d['sections']??[] as $section) { self::assertStringContainsString($section['description'],$fallback); }
            }
            $totals[]=$count;
            self::assertSame($count,substr_count($html,'data-theory-native-extension'));
            self::assertStringNotContainsString('theory-section-toggle-arrow',$html);
            $dom=new DOMDocument; libxml_use_internal_errors(true); $dom->loadHTML('<meta charset="UTF-8">'.$html); libxml_clear_errors(); $xp=new DOMXPath($dom);
            $ids=[]; foreach ($xp->query('//*[@id]') as $el) { self::assertNotContains($el->getAttribute('id'),$ids); $ids[]=$el->getAttribute('id'); }
            foreach ($xp->query('//details[@data-theory-details]') as $el) { self::assertFalse($el->hasAttribute('open')); self::assertNotSame('',trim($el->textContent)); }
        }
        self::assertSame([12,13,15],$totals);
    }
    public static function mutations(): array
    {
        return array_map(fn($s)=>[$s],['not','focus','wh','request','agreement','head','description','classification','comma','readings','information','hope','did-so','former','her','translation','neighbour','anchor','answer']);
    }
    #[DataProvider('mutations')]
    public function test_semantic_negative_fixtures_all_fail(string $kind): void
    {
        [$before,$p]=Package::load(); $raw=Package::json($p);
        $pairs=[
            'not'=>["It wasn't the price","It was the price"],
            'focus'=>['It was the editor who removed the repeated paragraph.','It was the repeated paragraph that the editor removed.'],
            'wh'=>['When was it that the supplier changed the delivery date?','When was it that did the supplier change the delivery date?'],
            'request'=>['was request an independent review','was need an independent review'],
            'agreement'=>['It is the reviewers','It are the reviewers'],
            'head'=>['Головне слово — <em>report</em>','Головне слово — <em>erosion</em>'],
            'description'=>['The description of the experimental procedures ___ incomplete.','The description of the experimental procedures are incomplete.'],
            'classification'=>['content of belief','relative clause'], // handled below with actual practice
            'comma'=>['Leila, the project coordinator, approved the change.','Leila the project coordinator approved the change.'],
            'readings'=>['While we were in the corridor, we discussed the photograph of the actor.','We discussed the photograph showing the actor in the corridor.'],
            'information'=>['I need more information. Do you have any?','I need more information. Do you have any ones?'],
            'hope'=>['I hope not.','I don’t hope so.'],
            'did-so'=>['не всю обіцянку','саме всю обіцянку'],
            'former'=>['<em>The former</em> — автобус','<em>The former</em> — трамвай'],
            'her'=>["Alina told Iryna that her application was incomplete.","Alina told Iryna that Iryna's application was incomplete."],
            'translation'=>['Нас турбувала не ціна, а відсутність гарантії.','']];
        if($kind==='request'){
            foreach($p['targets'][0]['after']['page']['blocks'] as &$block)if($block['type']==='practice-set'){
                $d=json_decode($block['body'],true);$d['selects'][1]['answer']='need';$d['selects'][1]['accepted']=['need'];
                $block['body']=Package::json($d);break;
            }
        }elseif(isset($pairs[$kind])&&$kind!=='classification'){
            [$a,$b]=$pairs[$kind];self::assertStringContainsString($a,$raw);
            $pos=strpos($raw,$a);$raw=substr_replace($raw,$b,$pos,strlen($a));$p=json_decode($raw,true,flags:JSON_THROW_ON_ERROR);
        }elseif(in_array($kind,['classification','answer'],true)){
            foreach($p['targets'][1]['after']['page']['blocks'] as &$block)if($block['type']==='practice-set'){
                $d=json_decode($block['body'],true);
                if($kind==='classification'){$d['choices'][0]['answer']='b';}else{unset($d['selects'][0]['answer']);}
                $block['body']=Package::json($d);break;
            }
        }elseif($kind==='neighbour'){
            [$p['targets'][0]['plans'][1]['points'][0]['detail'],$p['targets'][0]['plans'][1]['points'][1]['detail']]=
                [$p['targets'][0]['plans'][1]['points'][1]['detail'],$p['targets'][0]['plans'][1]['points'][0]['detail']];
        }else{$p['targets'][0]['plans'][1]['key']=$p['targets'][0]['plans'][0]['key'];}
        $this->expectException(RuntimeException::class);Package::validate($before,$p);
    }
    public function test_exact_root_ancestry_and_approved_practice(): void
    {
        [, $p]=Package::load();self::assertSame(array_fill(0,3,['sentence-structure']),array_column($p['targets'],'ancestry'));
        $banks = [
            'Database\\Seeders\\V3\\Polyglot\\PolyglotCleftSentencesEmphasisC1LessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotComplexNounPhrasesC2LessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotEllipsisSubstitutionAndReferenceC2LessonSeeder',
        ];
        foreach($p['targets'] as $index => $t){
            $b=array_values(array_filter($t['after']['page']['blocks'],fn($b)=>$b['type']==='practice-set'))[0];$d=json_decode($b['body'],true);
            foreach(['selects','choices','inputs'] as $g){self::assertCount(2,$d[$g]);foreach($d[$g] as $item){self::assertNotEmpty($item['answer']);}}
            self::assertSame(['4'],$d['linked_practice']['question_types']);self::assertCount(1,$d['linked_practice']['seeder_classes']);
            self::assertSame([$banks[$index]], $d['linked_practice']['seeder_classes']);
            self::assertStringNotContainsString('AllLevels',implode(' ',$d['linked_practice']['seeder_classes']));
        }
        $practice=fn($i)=>json_decode(array_values(array_filter($p['targets'][$i]['after']['page']['blocks'],fn($b)=>$b['type']==='practice-set'))[0]['body'],true);
        self::assertSame(['request','to request'],$practice(0)['selects'][1]['accepted']);
        self::assertStringContainsString('A граматичне',$practice(0)['choices'][0]['feedback']['a']);
        self::assertTrue($practice(1)['inputs'][0]['punctuation_sensitive']);
        self::assertCount(2,$practice(1)['inputs'][1]['accepted']);self::assertCount(2,$practice(2)['inputs'][1]['accepted']);
        self::assertStringContainsString('The former = workshop; the latter = handbook.',$practice(2)['inputs'][0]['after']);
    }
    public function test_stale_or_invalid_mapping_keeps_all_stored_text_without_buttons(): void
    {
        [, $p]=Package::load(); $t=$p['targets'][0];$b=$t['after']['page']['blocks'][2];
        foreach(['key','body','uuid','order','locale'] as $kind){
            $d=json_decode($b['body'],true);$block=new TextBlock;
            $block->forceFill(['id'=>1002,'uuid'=>M26DetailPackage::uuid($t['identity'],$b,3),'seeder'=>$t['identity'],
                'locale'=>'uk','sort_order'=>3,'type'=>$b['type'],'body'=>$b['body']]);
            if($kind==='key'){$d['m29_v1']['key']='unknown';} elseif($kind==='body'){$d['sections'][0]['description'].=' Additional stored text.';}
            elseif($kind==='uuid'){$block->uuid='foreign';}elseif($kind==='order'){$block->sort_order=99;}else{$block->locale='pl';}
            $block->body=Package::json($d);$block->setRelation('tags',collect());$block->setRelation('page',null);
            self::assertNull(Package::presentation($block,$d));
            $html=view('theory.partials.content-block',['block'=>$block,'practiceQuestions'=>collect()])->render();
            self::assertStringNotContainsString('data-theory-native-extension',$html);
            foreach($d['sections'] as $point){self::assertStringContainsString($point['description'],$html);}
        }
    }
}
