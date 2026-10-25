<?php

namespace Tests\Feature;

use App\Support\M90PastPerfectFutureSimpleBasics as Package;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Offline actual native factory replay. Width labels/stub focus are not browser or layout acceptance. */
class M90PastPerfectFutureSimpleBasicsNativePracticeTest extends TestCase
{
    private const NATIVE_TEST_SHA = '1b6ce338bab6bf0f1171ca4ffb3fdb6c7a3e665276ba0930786257c61aac7792';

    public function test_all_36_controls_12_full_inputs_and_24_originals_use_the_unchanged_native_factory(): void
    {
        $root=dirname(__DIR__,2);$release=Package::load($root);Package::validate(...$release);
        $factory=getenv('GRAMLYZE_M90_FACTORY_ROOT')?:$root;
        $factorySha=hash_file('sha256',$factory.'/resources/views/engram/theory/blocks-v3/practice-set.blade.php');
        self::assertContains($factorySha,[
            '0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186',
            '05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e',
        ],'Only independently captured ROOT or WT native source contexts are allowed.');
        $script=$root.'/tests/Browser/m90-native-practice.cjs';
        self::assertSame(self::NATIVE_TEST_SHA,hash_file('sha256',$script));
        $protected=[];
        foreach(['resources/views/engram/theory/blocks-v3/practice-set.blade.php','tests/Browser/load-answer-variants.cjs',
            'public/js/english-answer-variants.js','public/data/english-contractions.json']as$path){
            $protected[$path]=hash_file('sha256',$factory.'/'.$path);
        }
        $legacy=[];
        foreach($release[0]['targets'] as $i=>$target){
            $data=json_decode($target['before']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['body'],true,flags:JSON_THROW_ON_ERROR);
            self::assertCount(2,$data['selects']);self::assertCount(2,$data['inputs']);
            $legacy[]=$data;
        }
        $process=new Process([getenv('NODE_BINARY')?:'node','--unhandled-rejections=strict',$script,$factory],$root,timeout:60);
        $process->setInput(json_encode(['lessons'=>$release[2]['lessons'],'legacy_practices'=>$legacy],
            JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
        $process->run();
        self::assertSame(0,$process->getExitCode(),$process->getOutput().$process->getErrorOutput());
        self::assertSame('',$process->getErrorOutput());
        $r=json_decode($process->getOutput(),true,flags:JSON_THROW_ON_ERROR);
        foreach(['graded'=>36,'full_inputs'=>12,'declared_aliases'=>42,'displayed_options'=>52,
            'nominal_manual_token_cases'=>48,'delete_return'=>24,'mixed_edit'=>24,'clear_rebuild'=>24,
            'original_controls_replayed'=>24,'original_declared_answers_replayed'=>26,
            'independent_group_scores'=>18,'wrong_context_cases'=>12,'network_calls'=>0,
            'DB_connections'=>0,'DB_writes'=>0,'HTTP_requests'=>0,'file_writes'=>0]as$key=>$expected){
            self::assertSame($expected,$r[$key],$key);
        }
        self::assertSame([6,6,6,6,6,6],array_column($r['lessons'],'graded'));
        self::assertSame([2,2,2,2,2,2],array_column($r['lessons'],'full_inputs'));
        self::assertSame($factorySha,$r['native_sha256']);
        self::assertSame([1440,320],$r['nominal_widths']);
        self::assertTrue($r['focus_calls_are_stubs']);self::assertTrue($r['width_labels_are_not_layout_acceptance']);
        self::assertSame('not claimed',$r['browser_acceptance']);
        foreach($protected as$path=>$sha){self::assertSame($sha,hash_file('sha256',$factory.'/'.$path),$path);}
        self::assertSame(self::NATIVE_TEST_SHA,hash_file('sha256',$script));
    }
}
