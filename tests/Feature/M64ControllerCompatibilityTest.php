<?php

namespace Tests\Feature;

use App\Services\M63ContentPatch;
use App\Services\M64ContentPatch;
use Illuminate\Support\Facades\File;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Exact M64 insertions preserve unchanged M63 and its historical controller chain. */
class M64ControllerCompatibilityTest extends TestCase
{
    private const CURRENT_WT_RAW_SHA = '6769b8beb4bab7c25055fb32d5af5a6cc1f38c0b0e8d547e2fd755f90a1cf9de';
    private const BEFORE_M63_WT_RAW_SHA = 'da1ce1585a3fa68af4aad6927d1cdcb539c65576504e6b762ced06c304e96391';

    private function historicalM63(): string
    {
        $bytes = File::get(base_path(M64ContentPatch::CONTROLLER_PATH));
        self::assertSame(self::CURRENT_WT_RAW_SHA, hash('sha256', $bytes));
        $eol = str_contains($bytes, "\r\n") ? "\r\n" : "\n";
        $call = '\\App\\Support\\M64ReasonResultContrastPackage::preserveExcludedTextBlock';
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
        self::assertSame(self::BEFORE_M63_WT_RAW_SHA, hash('sha256', $bytes));
        self::assertSame(M63ContentPatch::CONTROLLER_WT_AFTER_SHA, hash('sha256', $bytes));
        return $bytes;
    }

    public function test_four_exact_m64_calls_and_two_null_checks_reconstruct_accepted_m63(): void
    {
        $bytes = $this->historicalM63();
        foreach (['M63RelativeCompletionLinkingPackage', 'M62ReportedReviewRelativeBasicsPackage', 'M61ReportedSpeechChangesPackage', 'M60ReportedSpeechBasicsPackage', 'M59PassiveFutureAgentReviewPackage', 'M58PastFuturePassivesPackage', 'M57ContinuousPerfectPassivesPackage', 'M56GerundSimplePassivesPackage', 'M55PassiveReviewInfinitivePackage', 'M54PassivePatternsPackage', 'M53PassiveConstructionsPackage', 'M52PassiveBasicsPackage', 'M51ModalMeaningsPackage', 'M50ObligationAdvicePackage'] as $package) {
            self::assertSame(4, substr_count($bytes, $package.'::preserveExcludedTextBlock'));
        }
    }

    public function test_original_m63_chain_checks_run_unchanged_on_a_private_historical_fixture(): void
    {
        $bytes = $this->historicalM63();
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $fixture = storage_path('app/m64-controller-compatibility-'.bin2hex(random_bytes(7)));
        File::makeDirectory($fixture.'/app/Http/Controllers', 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($fixture);
        $file = $fixture.'/'.M64ContentPatch::CONTROLLER_PATH;
        File::put($file, $bytes); IsolatedTestEnvironment::assertOwnedPath($file);
        $application = app(); $originalBase = $application->basePath();
        try {
            $application->setBasePath($fixture);
            foreach (['test_four_exact_m63_calls_and_two_null_checks_reconstruct_accepted_m62',
                'test_original_m62_chain_checks_run_unchanged_on_a_private_historical_fixture'] as $method) {
                $historical = new M63ControllerCompatibilityTest($method);
                $historical->$method(); // Pure file assertions; never invoke old DB fixture setup.
            }
        } finally { $application->setBasePath($originalBase); }
        self::assertSame($originalBase, base_path());
        self::assertSame($bytes, File::get($file));
    }
}
