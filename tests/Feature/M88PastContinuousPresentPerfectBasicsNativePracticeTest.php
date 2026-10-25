<?php

namespace Tests\Feature;

use App\Support\M88PastContinuousPresentPerfectBasics as Package;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Offline model replay only; actual browser/layout acceptance remains a separate requirement. */
class M88PastContinuousPresentPerfectBasicsNativePracticeTest extends TestCase
{
    public function test_all_36_controls_12_inputs_and_original_accepted_only_case_use_unchanged_native_methods(): void
    {
        $root = dirname(__DIR__, 2); $release = Package::load($root); Package::validate(...$release);
        $factory = getenv('GRAMLYZE_M88_FACTORY_ROOT') ?: $root;
        $factorySha = hash_file('sha256', $factory.'/resources/views/engram/theory/blocks-v3/practice-set.blade.php');
        $focusProfiles = [
            '0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186' =>
                ['property_present' => true, 'raw_value' => false, 'effective_focus_suppression' => false],
            '05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e' =>
                ['property_present' => false, 'raw_value' => null, 'effective_focus_suppression' => false],
        ];
        self::assertArrayHasKey($factorySha, $focusProfiles, 'An exact independently captured ROOT or WT native profile is required.');
        $process = new Process([getenv('NODE_BINARY') ?: 'node', $root.'/tests/Browser/m88-native-practice.cjs', $factory], $root, timeout: 45);
        $original = json_decode($release[0]['targets'][5]['before']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayNotHasKey('answer', $original['inputs'][0]);
        $process->setInput(json_encode(['lessons' => $release[2]['lessons'], 'legacy_accepted_only' => $original],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $process->run();
        self::assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $r = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        foreach (['tasks' => 36, 'demos' => 0, 'full_inputs' => 12, 'aliases' => 46, 'shown_options' => 65,
            'manual_token_nominal_cases' => 48, 'delete_return_cases' => 24, 'mixed_edit_cases' => 24,
            'clear_rebuild_cases' => 24, 'group_score_cases' => 18, 'semantic_cases' => 51,
            'original_accepted_only_manual_cases' => 4, 'original_accepted_only_token_cases' => 2,
            'terminal_positive_reply_rejections' => 3, 'network_calls' => 0, 'db_calls' => 0, 'file_writes' => 0] as $key => $value) {
            self::assertSame($value, $r[$key], $key);
        }
        self::assertSame([6, 6, 6, 6, 6, 6], array_column($r['lessons'], 'graded'));
        self::assertSame([0, 0, 0, 0, 0, 0], array_column($r['lessons'], 'demos'));
        self::assertSame([2, 2, 2, 2, 2, 2], array_column($r['lessons'], 'full_inputs'));
        self::assertSame($factorySha, $r['native_sha256']);
        self::assertSame($focusProfiles[$factorySha], $r['native_focus_profile']);
        self::assertFalse($r['native_no_input_token_focus']);
        self::assertGreaterThan(0, $r['focus_stub_calls']);
        self::assertTrue($r['focus_is_stub_not_browser_acceptance']);
        self::assertSame([1440, 320], $r['nominal_widths']);
        self::assertTrue($r['width_labels_are_not_layout_acceptance']);
        self::assertTrue($r['source_bytes_unchanged']);
        self::assertSame('offline native factory only; no live browser or layout claim', $r['acceptance']);
    }
}
