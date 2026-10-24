<?php

namespace Tests\Unit;

use App\Support\M85VerbToBe as Package;
use M85LiveDiagnosticContract as Contract;
use PHPUnit\Framework\TestCase;
use RuntimeException;

require_once dirname(__DIR__,2).'/tools/diagnostics/m85-live-contract.php';

/** Pure data/profile checks; no Laravel bootstrap, environment, SQL or HTTP. */
class M85LiveDiagnosticContractTest extends TestCase
{
    private static function syntheticLessons():array
    {
        $out=[];
        foreach(Contract::OWNERS as $i=>$owner){
            $slot=Contract::MISTAKE_SLOTS[$i];$blocks=array_fill(0,$slot+1,['type'=>'synthetic-unused']);$items=[];
            foreach(range(0,2) as $n){$item=['note'=>'Незмінне синтетичне пояснення '.$i.':'.$n.'.'];if($i>=4){$item['hint']=$item['note'];}$items[]=$item;}
            $blocks[$slot]=['type'=>'mistakes-grid','data'=>['items'=>$items]];$out[]=['identity'=>$owner,'blocks'=>$blocks];
        }return $out;
    }

    public function test_synthetic_exact_eighteen_notes_and_six_aliases_are_required():void
    {
        $facts=Contract::assertMistakeNotes(self::syntheticLessons());
        self::assertSame(6,$facts['mistake_blocks']);self::assertSame(18,$facts['required_notes']);self::assertSame(6,$facts['exact_retained_hint_aliases']);
        foreach($facts['aliases'] as $row){self::assertSame($row['hint_sha256'],$row['note_sha256']);self::assertTrue($row['exact']);}
    }

    public function test_legacy_hint_does_not_mask_a_missing_canonical_note():void
    {
        $lessons=self::syntheticLessons();unset($lessons[4]['blocks'][5]['data']['items'][0]['note']);
        $this->expectException(RuntimeException::class);$this->expectExceptionMessage('required mistake note missing');Contract::assertMistakeNotes($lessons);
    }

    public function test_a_non_alias_note_is_required_too():void
    {
        $lessons=self::syntheticLessons();$lessons[0]['blocks'][4]['data']['items'][1]['note']='  ';
        $this->expectException(RuntimeException::class);$this->expectExceptionMessage('required mistake note missing');Contract::assertMistakeNotes($lessons);
    }

    public function test_alias_wording_cannot_be_rewritten_or_whitespace_normalized():void
    {
        $lessons=self::syntheticLessons();$lessons[5]['blocks'][4]['data']['items'][2]['note'].=' ';
        $this->expectException(RuntimeException::class);$this->expectExceptionMessage('byte-for-byte');Contract::assertMistakeNotes($lessons);
    }

    public function test_a_seventh_legacy_alias_is_not_silently_accepted():void
    {
        $lessons=self::syntheticLessons();$lessons[1]['blocks'][4]['data']['items'][0]['hint']=$lessons[1]['blocks'][4]['data']['items'][0]['note'];
        $this->expectException(RuntimeException::class);$this->expectExceptionMessage('outside the exact six');Contract::assertMistakeNotes($lessons);
    }

    private static function syntheticMistakeDom(bool $twoTranslations=true,bool $swapEnglish=false):array
    {
        $wrong=$swapEnglish?'She is ready.':'She are ready.';$right=$swapEnglish?'She are ready.':'She is ready.';
        $html='<article id="point"><div class="theory-example theory-example--wrong"><code lang="en">'.$wrong.'</code></div><p lang="uk" class="theory-translation">Вона готова.</p><div class="theory-example theory-example--right"><span lang="en">'.$right.'</span></div>'.($twoTranslations?'<p lang="uk" class="theory-translation">Вона готова.</p>':'').'</article>';
        $dom=new \DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);try{$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);}finally{libxml_clear_errors();libxml_use_internal_errors($previous);}$xp=new \DOMXPath($dom);
        return [$xp,$xp->query('//*[@id="point"]')->item(0),['wrong_en'=>'She are ready.','right_en'=>'She is ready.','wrong_ua'=>'Вона готова.','right_ua'=>'Вона готова.'],static fn($v)=>trim(strip_tags($v))];
    }

    public function test_identical_uk_translations_require_two_actual_dom_nodes():void
    {
        $facts=Contract::assertPairedMistakeDom(...self::syntheticMistakeDom());self::assertSame(['english_nodes'=>2,'ukrainian_nodes'=>2,'ordered_fields_exact'=>true],$facts);
    }

    public function test_one_uk_node_cannot_satisfy_two_identical_source_fields():void
    {
        $this->expectException(RuntimeException::class);$this->expectExceptionMessage('two separate UK translation nodes');Contract::assertPairedMistakeDom(...self::syntheticMistakeDom(false));
    }

    public function test_swapped_english_models_fail_the_exact_paired_dom_check():void
    {
        $this->expectException(RuntimeException::class);$this->expectExceptionMessage('wrong_en');Contract::assertPairedMistakeDom(...self::syntheticMistakeDom(true,true));
    }

    public function test_current_frozen_master_requires_the_exact_six_repaired_aliases():void
    {
        $path=dirname(__DIR__,2).'/'.Package::MASTER;self::assertFileExists($path);self::assertFalse(is_link($path));
        $raw=file_get_contents($path);self::assertSame(Package::MASTER_SHA,hash('sha256',$raw));$master=json_decode($raw,true,flags:JSON_THROW_ON_ERROR);
        $facts=Contract::assertMistakeNotes($master['lessons']);self::assertSame(18,$facts['required_notes']);self::assertCount(6,$facts['aliases']);
    }

    private static function canonicalEditorialFixture():array
    {
        $root=dirname(__DIR__,2);$out=[];
        foreach([Package::BEFORE=>Package::BEFORE_SHA,Package::SOURCE=>Package::SOURCE_SHA,Package::MASTER=>Package::MASTER_SHA,Package::NAVIGATION=>Package::NAVIGATION_SHA] as $path=>$sha){
            $file=$root.'/'.$path;self::assertFileExists($file);self::assertFalse(is_link($file));$raw=file_get_contents($file);self::assertSame($sha,hash('sha256',$raw));$out[]=json_decode($raw,true,flags:JSON_THROW_ON_ERROR);
        }return $out;
    }

    public function test_canonical_editorial_requires_all_eighteen_notes_and_six_exact_aliases():void
    {
        Package::assertEditorial(...self::canonicalEditorialFixture());$this->addToAssertionCount(1);
    }

    public function test_canonical_editorial_rejects_one_missing_note_even_with_a_legacy_hint():void
    {
        [$before,$patch,$master,$navigation]=self::canonicalEditorialFixture();unset($master['lessons'][4]['blocks'][5]['data']['items'][0]['note']);
        $this->expectException(RuntimeException::class);$this->expectExceptionMessage('required mistake note missing');Package::assertEditorial($before,$patch,$master,$navigation);
    }

    public function test_canonical_editorial_rejects_literal_hint_alias_rewording():void
    {
        [$before,$patch,$master,$navigation]=self::canonicalEditorialFixture();$master['lessons'][5]['blocks'][4]['data']['items'][2]['note'].=' ';
        $this->expectException(RuntimeException::class);$this->expectExceptionMessage('byte-for-byte');Package::assertEditorial($before,$patch,$master,$navigation);
    }

    public function test_canonical_editorial_rejects_any_hint_key_outside_the_six_aliases():void
    {
        [$before,$patch,$master,$navigation]=self::canonicalEditorialFixture();$master['lessons'][0]['blocks'][4]['data']['items'][0]['hint']=null;
        $this->expectException(RuntimeException::class);$this->expectExceptionMessage('outside the exact six');Package::assertEditorial($before,$patch,$master,$navigation);
    }

    public function test_canonical_editorial_rejects_a_missing_third_mistake_item():void
    {
        [$before,$patch,$master,$navigation]=self::canonicalEditorialFixture();array_pop($master['lessons'][2]['blocks'][4]['data']['items']);
        $this->expectException(RuntimeException::class);$this->expectExceptionMessage('three-item vector');Package::assertEditorial($before,$patch,$master,$navigation);
    }

    public function test_live_profile_keeps_the_c_exception_finite_and_notes_strict():void
    {
        $source=file_get_contents(dirname(__DIR__,2).'/tools/diagnostics/m85-live-pages.php');
        self::assertStringContainsString('$owner===Package::OWNERS[2]&&$slot===7',$source);
        self::assertStringContainsString('1e4d481857313376cb2836efc3b6a6df4e7f4120e2531c208406129130b0adda',$source);
        self::assertStringContainsString('secondary_navigation_visible_acceptance\'=>false',$source);
        self::assertStringContainsString('accepted-with-pre-existing-secondary-navigation-omission',$source);
        self::assertStringContainsString('$task[\'prompt\']??null',$source);
        self::assertStringContainsString("\$contains(\$groupText,\$task['label']",$source);
        self::assertStringContainsString('M85LiveDiagnosticContract::assertMistakeNotes($master[\'lessons\'])',$source);
        self::assertStringContainsString('Exact actual mistake note count',$source);
        self::assertStringContainsString('[36,30,1]',$source);
    }

    public function test_new_live_profile_parses_without_app_sql_or_http():void
    {
        $root=dirname(__DIR__,2);$pipes=[];$process=proc_open([PHP_BINARY,$root.'/tools/diagnostics/m85-live-pages.php','--profile-check-only'],[1=>['pipe','w'],2=>['pipe','w']],$pipes,$root);
        self::assertIsResource($process);$stdout=stream_get_contents($pipes[1]);$stderr=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$code=proc_close($process);
        self::assertSame(0,$code,$stderr);self::assertSame('',$stderr);$result=json_decode($stdout,true,flags:JSON_THROW_ON_ERROR);
        self::assertTrue($result['parsed']);self::assertFalse($result['SQL_access']);self::assertSame(0,$result['http_requests']);
    }
}
