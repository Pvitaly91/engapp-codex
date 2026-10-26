<?php

namespace Tests\Feature;

use App\Support\M94RelativeClausesWhoseWhich as Package;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Offline actual native factory replay. Stub focus is not browser, DOM or layout acceptance. */
class M94RelativeClausesWhoseWhichNativePracticeTest extends TestCase
{
    private const NATIVE_TEST_SHA = 'beec37e7b3053953033ad8651c7157087e9395bffed2ae6e3e081e51d4f7c4bf';

    public function test_six_controls_seven_keys_and_two_full_inputs_use_the_unchanged_native_factory(): void
    {
        $root = dirname(__DIR__, 2);
        $release = Package::load($root);
        Package::validate(...$release);
        self::assertCount(1, $release[2]['lessons']);
        $lesson = $release[2]['lessons'][0];
        self::assertSame(Package::OWNERS[0], $lesson['identity']);
        self::assertCount(8, $lesson['blocks']);
        self::assertSame('m94-practice', $lesson['blocks'][7]['key']);
        self::assertSame('practice-set', $lesson['blocks'][7]['type']);
        self::assertSame('B2', $lesson['blocks'][7]['level']);
        foreach (['selects', 'choices', 'inputs'] as $group) {
            self::assertCount(2, $lesson['blocks'][7]['data'][$group]);
        }

        $factory = getenv('GRAMLYZE_M94_FACTORY_ROOT') ?: $root;
        $factorySha = hash_file('sha256', $factory.'/resources/views/engram/theory/blocks-v3/practice-set.blade.php');
        self::assertContains($factorySha, [
            '0a8672d7ac90b4f3290c6167a8945856eb227dd060144ad8f3efbeeb2eaaa186',
            '05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e',
        ], 'Only independently captured ROOT or WT native source contexts are allowed.');
        $script = $root.'/tests/Browser/m94-native-practice.cjs';
        self::assertSame(self::NATIVE_TEST_SHA, hash_file('sha256', $script));
        $protected = [];
        foreach (['resources/views/engram/theory/blocks-v3/practice-set.blade.php', 'tests/Browser/load-answer-variants.cjs',
            'public/js/english-answer-variants.js', 'public/data/english-contractions.json'] as $path) {
            $protected[$path] = hash_file('sha256', $factory.'/'.$path);
        }

        $process = new Process([getenv('NODE_BINARY') ?: 'node', '--unhandled-rejections=strict', $script, $factory], $root, timeout: 60);
        $process->setInput(json_encode(['lessons' => $release[2]['lessons'], 'master_sha256' => Package::MASTER_SHA],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $process->run();
        self::assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        self::assertSame('', $process->getErrorOutput());
        $r = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('M94-offline-actual-native-factory-replay', $r['kind']);
        self::assertSame(Package::MASTER_SHA, $r['master_sha256']);
        foreach (['graded_authored_tasks' => 6, 'full_authored_inputs' => 2, 'declared_keys_including_canonical' => 7,
            'displayed_options' => 12, 'empty_cases' => 30, 'wrong_cases' => 6,
            'offline_full_manual_token_executions' => 4, 'synthetic_multiplicity_fixtures' => 1,
            'counted_assertions' => 302, 'network_calls' => 0,
            'DB_connections' => 0, 'DB_writes' => 0, 'HTTP_requests' => 0, 'file_writes' => 0] as $key => $expected) {
            self::assertSame($expected, $r[$key], $key);
        }
        self::assertCount(7, $r['observations']);
        self::assertCount(12, $r['option_observations']);
        self::assertCount(2, $r['full']);
        foreach ($r['full'] as $full) {
            foreach (['manual', 'tokens', 'mixedEditing', 'deleteReturn', 'clearRebuild'] as $flag) {
                self::assertTrue($full[$flag], $full['id'].' '.$flag);
            }
            $ids = array_column($full['bank'], 'id');
            self::assertSame(count($ids), count(array_unique($ids)), 'Token instances have independent IDs.');
            foreach ($full['bank'] as $token) {
                $wordCount = count(preg_split('/\s+/u', trim($token['value'])));
                self::assertGreaterThanOrEqual(1, $wordCount);
                self::assertLessThanOrEqual(3, $wordCount);
            }
        }
        self::assertFalse($r['full'][0]['containsComma']);
        self::assertNull($r['full'][0]['commaProbe']);
        self::assertTrue($r['full'][1]['containsComma']);
        $comma = $r['full'][1]['commaProbe'];
        self::assertSame('We missed the last bus, which delayed our arrival.', $comma['original']);
        self::assertSame('We missed the last bus which delayed our arrival.', $comma['commaRemoved']);
        self::assertTrue($comma['ordinaryNormalizerCollision']);
        self::assertTrue($comma['nativeLexicalScoreAcceptsCommaRemoved']);
        self::assertFalse($comma['punctuationAcceptanceClaim'], 'This records the unchanged normalizer limit, not punctuation assessment.');

        self::assertSame(['repeatedLabel' => 'the', 'instances' => 2, 'distinctIDs' => true,
            'deleteReturnsOneInstance' => true, 'notAnAuthoredTask' => true], $r['synthetic_multiplicity']);
        self::assertSame(['answer' => 'We missed the last bus, which made us arrive late.',
            'nativeAccepted' => true, 'tokenModePromised' => false], $r['manual_only_alias']);
        self::assertTrue($r['focus_calls_are_stubs']);
        self::assertFalse($r['nominal_width_labels_used']);
        self::assertFalse($r['application_bootstrap']);
        self::assertFalse($r['working_env_loaded']);
        self::assertSame('not claimed', $r['browser_acceptance']);
        self::assertSame('not claimed', $r['layout_acceptance']);
        foreach ($protected as $path => $sha) {
            self::assertSame($sha, hash_file('sha256', $factory.'/'.$path), $path);
        }
        self::assertSame(self::NATIVE_TEST_SHA, hash_file('sha256', $script));
    }
}
