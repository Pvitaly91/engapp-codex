<?php

namespace Tests\Feature;

use App\Support\M87ContinuousCompletionPastSimple as Package;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Model replay only; the real 36 browser states and 64 input cases are separate acceptance. */
class M87ContinuousCompletionPastSimpleNativePracticeTest extends TestCase
{
    public function test_all_57_controls_four_demos_and_16_inputs_use_unchanged_native_model(): void
    {
        $root=dirname(__DIR__,2);$release=Package::load($root);Package::validate(...$release);
        $factory=getenv('GRAMLYZE_M87_FACTORY_ROOT')?:$root;
        $p=new Process([getenv('NODE_BINARY')?:'node',$root.'/tests/Browser/m87-native-practice.cjs',$factory],$root,timeout:45);
        $p->setInput(json_encode(['lessons'=>$release[2]['lessons']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));$p->run();
        self::assertSame(0,$p->getExitCode(),$p->getOutput().$p->getErrorOutput());
        $r=json_decode($p->getOutput(),true,flags:JSON_THROW_ON_ERROR);
        foreach(['tasks'=>57,'demos'=>4,'full_inputs'=>16,'manual_token_nominal_cases'=>64,'delete_return_cases'=>32,
            'mixed_edit_cases'=>32,'clear_rebuild_cases'=>32,'group_score_cases'=>22,'semantic_cases'=>53,
            'terminal_positive_reply_rejections'=>3,'network_calls'=>0,'db_calls'=>0,'file_writes'=>0] as $key=>$expected){self::assertSame($expected,$r[$key],$key);}
        self::assertSame([6,6,11,11,11,12],array_column($r['lessons'],'graded'));
        self::assertSame([0,0,1,1,1,1],array_column($r['lessons'],'demos'));
        self::assertSame([2,2,3,3,3,3],array_column($r['lessons'],'full_inputs'));
        self::assertGreaterThanOrEqual(57,$r['aliases']);self::assertGreaterThanOrEqual(58,$r['shown_options']);
        self::assertSame([1440,320],$r['nominal_widths']);self::assertTrue($r['width_labels_are_not_layout_acceptance']);
        self::assertTrue($r['source_bytes_unchanged']);self::assertSame('offline native factory only; no live browser or layout claim',$r['acceptance']);
    }
}
