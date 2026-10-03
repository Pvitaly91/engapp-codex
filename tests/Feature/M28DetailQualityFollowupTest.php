<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M27LinkingWordsPackage;
use App\Support\M28EmphasisPackage;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M28DetailQualityFollowupTest extends TestCase
{
    use RebuildsComposeTestSchema;
    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    public function test_finite_short_mappings_are_visible_without_buttons_and_author_bytes_unchanged(): void
    {
        $removed=['m27-c1-section-2'=>[3],'m27-c2-section-1'=>[1],'m27-c2-section-4'=>[1,2,3,4],'m27-c2-section-5'=>[1,2,3]];
        $totals=[]; $merges=[];
        foreach ([M27LinkingWordsPackage::class,M28EmphasisPackage::class] as $class) {
            [$before,$current]=$class::load(); $class::validate($before,$current);
            $frozen=json_decode(file_get_contents(base_path(str_replace('.v2.json','.v1.json',$class::SOURCE))),true,flags:JSON_THROW_ON_ERROR);
            $count=0; $merge=0;
            foreach ($current['targets'] as $i=>$target) {
                self::assertSame($frozen['targets'][$i]['after'],$target['after'],'No DB, practice, metadata or bank payload change.');
                foreach ($target['plans'] as $j=>$plan) {
                    $b=$target['after']['page']['blocks'][$j+1]; $block=new TextBlock;
                    $block->forceFill(['id'=>100+$j,'uuid'=>M26DetailPackage::uuid($target['identity'],$b,$j+2),
                        'seeder'=>$target['identity'],'locale'=>'uk','sort_order'=>$j+2,'type'=>$b['type'],'body'=>$b['body']]);
                    $block->setRelation('tags',collect());$block->setRelation('page',null);
                    $p=$class::presentation($block,json_decode($b['body'],true));self::assertNotNull($p);
                    $html=view('theory.partials.content-block',['block'=>$block,'practiceQuestions'=>collect()])->render();
                    foreach($plan['points'] as $k=>$point){
                        $old=$frozen['targets'][$i]['plans'][$j]['points'][$k];
                        $shouldMerge=$old['detail']!=='' && ($class===M28EmphasisPackage::class || in_array($k+1,$removed[$plan['key']]??[],true));
                        if($shouldMerge){
                            $merge++;self::assertSame($old['basic'].'<br><br>'.$old['detail'],$point['basic']);self::assertSame('',$point['detail']);
                            self::assertArrayNotHasKey($k,$p['points']);self::assertSame($point['basic'],$p['data']['sections'][$k]['description']);
                            self::assertStringContainsString($point['basic'],$html);
                            self::assertStringNotContainsString('id="block-'.$plan['key'].'-point-'.($k+1).'-detail"',$html);
                        }else{self::assertSame($old,$point);}
                        if($point['detail']!==''){$count++;self::assertArrayHasKey($k,$p['points']);}
                    }
                }
            }
            $totals[]=$count;$merges[]=$merge;
        }
        self::assertSame([9,20],$merges);self::assertSame([13,0],$totals);
        self::assertSame('5440f6e2d92ed6c979d3b4f29ba42c5fa034f3eb09bf8eb92a46d8c4863d68cb',hash_file('sha256',base_path('database/content-patches/m27-m11-linking-words.v1.json')));
        self::assertSame('9765237a505097b9bd84c8d5896449cd6be514952e825f6c0ea4e101615cc99f',hash_file('sha256',base_path('database/content-patches/m28-m12-emphasis-inversion.v1.json')));
    }
}
