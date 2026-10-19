<?php

namespace Tests\Feature;

use App\Services\M62ContentPatch;
use App\Services\M63ContentPatch;
use Illuminate\Support\Facades\File;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Exact M63 insertions preserve unchanged M62 and its historical controller chain. */
class M63ControllerCompatibilityTest extends TestCase
{
    private const CURRENT_WT_RAW_SHA = 'da1ce1585a3fa68af4aad6927d1cdcb539c65576504e6b762ced06c304e96391';
    private const BEFORE_M62_WT_RAW_SHA = '7d9b222ba0050211f288d78ad647fbd75c9ee0e68253cc4e8a2e067d77813594';

    private function historicalM62(): string
    {
        $bytes = File::get(base_path(M63ContentPatch::CONTROLLER_PATH));
        self::assertSame(self::CURRENT_WT_RAW_SHA, hash('sha256', $bytes));
        $eol = str_contains($bytes, "\r\n") ? "\r\n" : "\n";
        $call = '\\App\\Support\\M63RelativeCompletionLinkingPackage::preserveExcludedTextBlock';
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
        self::assertSame(self::BEFORE_M62_WT_RAW_SHA, hash('sha256', $bytes));
        self::assertSame(M62ContentPatch::CONTROLLER_WT_AFTER_SHA, hash('sha256', $bytes));
        return $bytes;
    }

    public function test_four_exact_m63_calls_and_two_null_checks_reconstruct_accepted_m62(): void
    {
        $bytes = $this->historicalM62();
        foreach (['M62ReportedReviewRelativeBasicsPackage', 'M61ReportedSpeechChangesPackage', 'M60ReportedSpeechBasicsPackage', 'M59PassiveFutureAgentReviewPackage', 'M58PastFuturePassivesPackage', 'M57ContinuousPerfectPassivesPackage', 'M56GerundSimplePassivesPackage', 'M55PassiveReviewInfinitivePackage', 'M54PassivePatternsPackage', 'M53PassiveConstructionsPackage', 'M52PassiveBasicsPackage', 'M51ModalMeaningsPackage', 'M50ObligationAdvicePackage'] as $package) {
            self::assertSame(4, substr_count($bytes, $package.'::preserveExcludedTextBlock'));
        }
    }

    public function test_original_m62_chain_checks_run_unchanged_on_a_private_historical_fixture(): void
    {
        $bytes = $this->historicalM62();
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $fixture = storage_path('app/m63-controller-compatibility-'.bin2hex(random_bytes(7)));
        File::makeDirectory($fixture.'/app/Http/Controllers', 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($fixture);
        $file = $fixture.'/'.M63ContentPatch::CONTROLLER_PATH;
        File::put($file, $bytes); IsolatedTestEnvironment::assertOwnedPath($file);
        $application = app(); $originalBase = $application->basePath();
        try {
            $application->setBasePath($fixture);
            foreach (['test_four_exact_m62_calls_and_two_null_checks_reconstruct_accepted_m61',
                'test_original_m61_chain_checks_run_unchanged_on_a_private_historical_fixture'] as $method) {
                $historical = new M62ControllerCompatibilityTest($method);
                $historical->$method(); // Pure file assertions; never invoke old DB fixture setup.
            }
        } finally { $application->setBasePath($originalBase); }
        self::assertSame($originalBase, base_path());
        self::assertSame($bytes, File::get($file));
    }
}
