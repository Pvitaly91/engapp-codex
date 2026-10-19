<?php

namespace Tests\Feature;

use App\Services\M58ContentPatch;
use App\Services\M59ContentPatch;
use Illuminate\Support\Facades\File;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Exact new hunks preserve the accepted M53→M52→M51 compatibility chain. */
class M59ControllerCompatibilityTest extends TestCase
{
    private const CURRENT_WT_RAW_SHA = '4a0c9d040aab2ba22a5fea79770f48f3429b8d2ac26c589a14ac640b3dbc6339';
    private const BEFORE_M58_WT_RAW_SHA = '5a59b896a4f90bb46b8ef22601bd384e3d70e661d105b48617a843ca9bae0ce1';

    private function historicalM58(): string
    {
        $bytes = File::get(base_path(M59ContentPatch::CONTROLLER_PATH));
        self::assertSame(self::CURRENT_WT_RAW_SHA, hash('sha256', $bytes));
        $eol = str_contains($bytes, "\r\n") ? "\r\n" : "\n";
        $call = '\\App\\Support\\M59PassiveFutureAgentReviewPackage::preserveExcludedTextBlock';
        $hunks = [
            "                    \$block = ".$call."(\$block, 'standalone_test');\n                    if (\$block === null) { continue; }\n",
            "                \$block = ".$call."(\$block, 'standalone_test');\n                if (\$block === null) { continue; }\n",
            "                if (\$textBlock) { \$textBlock = ".$call."(\$textBlock, 'standalone_test'); }\n",
            "            if (\$textBlock) { \$textBlock = ".$call."(\$textBlock, 'standalone_test'); }\n",
        ];
        foreach ($hunks as $hunk) {
            $literal = str_replace("\n", $eol, $hunk);
            self::assertSame(1, substr_count($bytes, $literal), 'One exact owned insertion, not broad line filtering.');
            $bytes = str_replace($literal, '', $bytes);
        }
        self::assertStringNotContainsString($call, $bytes);
        self::assertSame(self::BEFORE_M58_WT_RAW_SHA, hash('sha256', $bytes));
        self::assertSame(M58ContentPatch::CONTROLLER_WT_AFTER_SHA, hash('sha256', $bytes));
        return $bytes;
    }

    public function test_four_exact_m59_calls_and_two_null_checks_reconstruct_accepted_m58(): void
    {
        $bytes = $this->historicalM58();
        foreach (['M58PastFuturePassivesPackage', 'M57ContinuousPerfectPassivesPackage', 'M56GerundSimplePassivesPackage', 'M55PassiveReviewInfinitivePackage', 'M54PassivePatternsPackage', 'M53PassiveConstructionsPackage', 'M52PassiveBasicsPackage', 'M51ModalMeaningsPackage', 'M50ObligationAdvicePackage'] as $package) {
            self::assertSame(4, substr_count($bytes, $package.'::preserveExcludedTextBlock'));
        }
    }

    public function test_original_m58_chain_checks_run_unchanged_on_a_private_historical_fixture(): void
    {
        $bytes = $this->historicalM58();
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $fixture = storage_path('app/m59-controller-compatibility-'.bin2hex(random_bytes(7)));
        File::makeDirectory($fixture.'/app/Http/Controllers', 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($fixture);
        $file = $fixture.'/'.M59ContentPatch::CONTROLLER_PATH;
        File::put($file, $bytes); IsolatedTestEnvironment::assertOwnedPath($file);
        $application = app(); $originalBase = $application->basePath();
        try {
            $application->setBasePath($fixture);
            foreach (['test_four_exact_m58_calls_and_two_null_checks_reconstruct_accepted_m57',
                'test_original_m57_chain_checks_run_unchanged_on_a_private_historical_fixture'] as $method) {
                $historical = new M58ControllerCompatibilityTest($method);
                $historical->$method(); // Pure file assertions; never invoke old DB fixture setup.
            }
        } finally { $application->setBasePath($originalBase); }
        self::assertSame($originalBase, base_path());
        self::assertSame($bytes, File::get($file));
    }
}
