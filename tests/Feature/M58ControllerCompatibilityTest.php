<?php

namespace Tests\Feature;

use App\Services\M57ContentPatch;
use App\Services\M58ContentPatch;
use Illuminate\Support\Facades\File;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Exact new hunks preserve the accepted M53→M52→M51 compatibility chain. */
class M58ControllerCompatibilityTest extends TestCase
{
    private const CURRENT_WT_RAW_SHA = '5a59b896a4f90bb46b8ef22601bd384e3d70e661d105b48617a843ca9bae0ce1';
    private const BEFORE_M57_WT_RAW_SHA = 'd07fabc695900777aadad20dd30fe66a1a97e91a6745658c9d284d1c5239447c';

    private function historicalM57(): string
    {
        $bytes = File::get(base_path(M58ContentPatch::CONTROLLER_PATH));
        self::assertSame(self::CURRENT_WT_RAW_SHA, hash('sha256', $bytes));
        $eol = str_contains($bytes, "\r\n") ? "\r\n" : "\n";
        $call = '\\App\\Support\\M58PastFuturePassivesPackage::preserveExcludedTextBlock';
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
        self::assertSame(self::BEFORE_M57_WT_RAW_SHA, hash('sha256', $bytes));
        self::assertSame(M57ContentPatch::CONTROLLER_WT_AFTER_SHA, hash('sha256', $bytes));
        return $bytes;
    }

    public function test_four_exact_m58_calls_and_two_null_checks_reconstruct_accepted_m57(): void
    {
        $bytes = $this->historicalM57();
        foreach (['M57ContinuousPerfectPassivesPackage', 'M56GerundSimplePassivesPackage', 'M55PassiveReviewInfinitivePackage', 'M54PassivePatternsPackage', 'M53PassiveConstructionsPackage', 'M52PassiveBasicsPackage', 'M51ModalMeaningsPackage', 'M50ObligationAdvicePackage'] as $package) {
            self::assertSame(4, substr_count($bytes, $package.'::preserveExcludedTextBlock'));
        }
    }

    public function test_original_m57_chain_checks_run_unchanged_on_a_private_historical_fixture(): void
    {
        $bytes = $this->historicalM57();
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $fixture = storage_path('app/m58-controller-compatibility-'.bin2hex(random_bytes(7)));
        File::makeDirectory($fixture.'/app/Http/Controllers', 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($fixture);
        $file = $fixture.'/'.M58ContentPatch::CONTROLLER_PATH;
        File::put($file, $bytes); IsolatedTestEnvironment::assertOwnedPath($file);
        $application = app(); $originalBase = $application->basePath();
        try {
            $application->setBasePath($fixture);
            foreach (['test_four_exact_m57_calls_and_two_null_checks_reconstruct_accepted_m56',
                'test_original_m56_chain_checks_run_unchanged_on_a_private_historical_fixture'] as $method) {
                $historical = new M57ControllerCompatibilityTest($method);
                $historical->$method(); // Pure file assertions; never invoke old DB fixture setup.
            }
        } finally { $application->setBasePath($originalBase); }
        self::assertSame($originalBase, base_path());
        self::assertSame($bytes, File::get($file));
    }
}
