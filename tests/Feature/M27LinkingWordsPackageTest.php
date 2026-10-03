<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M27LinkingWordsPackage as Package;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M27LinkingWordsPackageTest extends TestCase
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
                $out.=$d['title'].' '.($d['intro']??'').' ';
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
        self::assertSame([3,7,3],$totals);
    }
    public static function mutations(): array { return array_map(fn($s)=>[$s],['not','because','therefore','provided','even','while','translation','example','neighbour','answer','anchor']); }
    public function test_fidelity_normalization_never_discards_english_not_or_internal_punctuation_tokens(): void
    {
        self::assertNotSame($this->bag('<p>This is not a condition.</p>'),$this->bag('<p>This is a condition.</p>'));
        self::assertNotSame($this->bag('<p>because of the delay</p>'),$this->bag('<p>because the delay</p>'));
    }
    #[DataProvider('mutations')]
    public function test_semantic_negative_fixtures_all_fail(string $kind): void
    {
        [$before,$p]=Package::load();
        $raw=Package::json($p);
        $pairs=['not'=>['Не дублюй','Дублюй'], 'because'=>['because','because of'],'therefore'=>['therefore','moreover'],
            'provided'=>['provided that','insofar as'],'even'=>['even though','even if'],'while'=>['while','whereas'],
            'translation'=>['Ми змінили маршрут',''], 'example'=>['We changed our route','']];
        if (isset($pairs[$kind])) { [$a,$b]=$pairs[$kind]; self::assertStringContainsString($a,$raw); $raw=preg_replace('/'.preg_quote($a,'/').'/', $b, $raw,1); $p=json_decode($raw,true,flags:JSON_THROW_ON_ERROR); }
        elseif ($kind==='neighbour') { [$p['targets'][1]['plans'][1]['points'][0]['detail'],$p['targets'][1]['plans'][1]['points'][1]['detail']]=[$p['targets'][1]['plans'][1]['points'][1]['detail'],$p['targets'][1]['plans'][1]['points'][0]['detail']]; }
        elseif ($kind==='answer') { $d=json_decode($p['targets'][0]['after']['page']['blocks'][7]['body'],true); unset($d['selects'][0]['answer']); $p['targets'][0]['after']['page']['blocks'][7]['body']=Package::json($d); }
        else { $p['targets'][0]['plans'][1]['key']=$p['targets'][0]['plans'][0]['key']; }
        $this->expectException(RuntimeException::class); Package::validate($before,$p);
    }
}
