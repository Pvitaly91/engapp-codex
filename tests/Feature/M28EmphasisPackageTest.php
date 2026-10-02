<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M28EmphasisPackage as Package;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M28EmphasisPackageTest extends TestCase
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
        self::assertSame([6,4,10],$totals);
    }
    public static function mutations(): array { return array_map(fn($s)=>[$s],['not','who','what','does','did','subordinate','pronoun','translation','example','neighbour','answer','anchor']); }
    #[DataProvider('mutations')]
    public function test_semantic_negative_fixtures_all_fail(string $kind): void
    {
        [$before,$p]=Package::load(); $raw=Package::json($p);
        $pairs=['not'=>['Не додавай','Додавай'],'who'=>['It was Maya who sent','It was Maya that sent'],
            'what'=>['what we need','what do we need'],'did'=>['did notice','did noticed'],
            'subordinate'=>['Only after the guide explained','Only after did the guide explain'],
            'pronoun'=>['Here they come','Here come they'],'translation'=>['Саме Мая надіслала план',''],
            'example'=>['Rarely do we hear owls here','']];
        if (isset($pairs[$kind])) {
            [$a,$b]=$pairs[$kind]; self::assertStringContainsString($a,$raw);
            $raw=preg_replace('/'.preg_quote($a,'/').'/', $b, $raw,1); $p=json_decode($raw,true,flags:JSON_THROW_ON_ERROR);
        } elseif (in_array($kind,['does','answer'])) {
            foreach($p['targets'][1]['after']['page']['blocks'] as &$block) if ($block['type']==='practice-set') {
                $d=json_decode($block['body'],true); if($kind==='does'){$d['selects'][0]['answer']='speaks';}else{unset($d['selects'][0]['answer']);}
                $block['body']=Package::json($d); break;
            }
        } elseif ($kind==='neighbour') {
            [$p['targets'][0]['plans'][2]['points'][1]['detail'],$p['targets'][0]['plans'][2]['points'][2]['detail']]=
                [$p['targets'][0]['plans'][2]['points'][2]['detail'],$p['targets'][0]['plans'][2]['points'][1]['detail']];
        } else {$p['targets'][0]['plans'][1]['key']=$p['targets'][0]['plans'][0]['key'];}
        $this->expectException(RuntimeException::class); Package::validate($before,$p);
    }
    public function test_exact_ancestry_and_complete_approved_practice_cases(): void
    {
        [, $p]=Package::load(); self::assertSame([['sentence-structure'],['basic-grammar','word-order'],['basic-grammar','word-order']],array_column($p['targets'],'ancestry'));
        foreach($p['targets'] as $t){
            $b=array_values(array_filter($t['after']['page']['blocks'],fn($b)=>$b['type']==='practice-set'))[0];
            $d=json_decode($b['body'],true); foreach(['selects','choices','inputs'] as $g){self::assertCount(2,$d[$g]);foreach($d[$g] as $item){self::assertNotEmpty($item['answer']);}}
            self::assertSame(['4'],$d['linked_practice']['question_types']); self::assertCount(1,$d['linked_practice']['seeder_classes']);
        }
        $d=json_decode($p['targets'][1]['after']['page']['blocks'][7]['body'],true);
        self::assertSame(['a','b','c','d'],$d['choices'][1]['options']);self::assertSame('c',$d['choices'][1]['answer']);
        foreach(['Only the caretaker','Not only the caretaker','Only then the manager','The manager rarely'] as $text){self::assertStringContainsString($text,$d['choices'][1]['label']);}
        $d=json_decode($p['targets'][2]['after']['page']['blocks'][7]['body'],true);
        foreach(['That estimate','Only later','Beside the entrance'] as $text){self::assertStringContainsString($text,$d['choices'][0]['label']);}
        foreach(['fronting without inversion','subject–auxiliary inversion','full-verb inversion'] as $text){self::assertStringContainsString($text,$d['choices'][0]['prompt']);}
    }
    public function test_stale_or_invalid_mapping_keeps_all_stored_text_without_buttons(): void
    {
        [, $p]=Package::load(); $t=$p['targets'][0];$b=$t['after']['page']['blocks'][2];
        foreach(['key','body','uuid','order','locale'] as $kind){
            $d=json_decode($b['body'],true);$block=new TextBlock;
            $block->forceFill(['id'=>1002,'uuid'=>M26DetailPackage::uuid($t['identity'],$b,3),'seeder'=>$t['identity'],
                'locale'=>'uk','sort_order'=>3,'type'=>$b['type'],'body'=>$b['body']]);
            if($kind==='key'){$d['m28_v1']['key']='unknown';} elseif($kind==='body'){$d['sections'][0]['description'].=' Additional stored text.';}
            elseif($kind==='uuid'){$block->uuid='foreign';}elseif($kind==='order'){$block->sort_order=99;}else{$block->locale='pl';}
            $block->body=Package::json($d);$block->setRelation('tags',collect());$block->setRelation('page',null);
            self::assertNull(Package::presentation($block,$d));
            $html=view('theory.partials.content-block',['block'=>$block,'practiceQuestions'=>collect()])->render();
            self::assertStringNotContainsString('data-theory-native-extension',$html);
            foreach($d['sections'] as $point){self::assertStringContainsString($point['description'],$html);}
        }
    }
}
