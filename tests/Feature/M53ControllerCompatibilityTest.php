<?php

namespace Tests\Feature;

use App\Services\M52ContentPatch;
use App\Services\M53ContentPatch;
use Illuminate\Support\Facades\File;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** New hook compatibility; accepted historical literal tests remain unchanged. */
class M53ControllerCompatibilityTest extends TestCase
{
    private const BASE_SHA = 'ba489be880d9a7c2b3dbc1698e887a05eb166fbe';
    private const CURRENT_WT_RAW_SHA = '0500ac5f89f29884b02e64c07b563bb0e49635bd4d1c7b4009148324eb11f239';
    private const BEFORE_M52_WT_RAW_SHA = 'e8b6dc81c4026872a40065310401bf87e42ef49c8e05b671a8eef218c3d1a59d';
    // Independently read accepted M52 Git blob, not calculated from M53 AFTER.
    private const ACCEPTED_M52_GIT_LF_SHA = '227df00370ed88c2924287898ee2c7bcbd842e73e52210319034231a448ca8de';

    private function historicalM52(): string
    {
        $bytes = File::get(base_path(M53ContentPatch::CONTROLLER_PATH));
        self::assertSame(self::CURRENT_WT_RAW_SHA, hash('sha256', $bytes));
        $eol = str_contains($bytes, "\r\n") ? "\r\n" : "\n";
        $call = '\\App\\Support\\M53PassiveConstructionsPackage::preserveExcludedTextBlock';
        $hunks = [
            "                    \$block = ".$call."(\$block, 'standalone_test');\n                    if (\$block === null) { continue; }\n",
            "                \$block = ".$call."(\$block, 'standalone_test');\n                if (\$block === null) { continue; }\n",
            "                if (\$textBlock) { \$textBlock = ".$call."(\$textBlock, 'standalone_test'); }\n",
            "            if (\$textBlock) { \$textBlock = ".$call."(\$textBlock, 'standalone_test'); }\n",
        ];
        foreach ($hunks as $hunk) {
            $literal = str_replace("\n", $eol, $hunk);
            self::assertSame(1, substr_count($bytes, $literal), 'One exact owned M53 insertion, no broad line filtering.');
            $bytes = str_replace($literal, '', $bytes);
        }
        self::assertStringNotContainsString($call, $bytes);
        self::assertSame(self::BEFORE_M52_WT_RAW_SHA, hash('sha256', $bytes), 'New independent physical BEFORE at '.self::BASE_SHA);
        self::assertSame(self::ACCEPTED_M52_GIT_LF_SHA, hash('sha256', str_replace("\r\n", "\n", $bytes)), 'Accepted M52 Git blob');
        self::assertSame(M52ContentPatch::CONTROLLER_WT_AFTER_SHA, hash('sha256', $bytes));
        return $bytes;
    }

    public function test_four_m53_calls_and_two_null_checks_reconstruct_the_exact_accepted_m52_controller(): void
    {
        $bytes = $this->historicalM52();
        self::assertSame(4, substr_count($bytes, 'M52PassiveBasicsPackage::preserveExcludedTextBlock'));
        self::assertSame(4, substr_count($bytes, 'M51ModalMeaningsPackage::preserveExcludedTextBlock'));
        self::assertSame(4, substr_count($bytes, 'M50ObligationAdvicePackage::preserveExcludedTextBlock'));
    }

    public function test_original_m52_m51_m50_compatibility_methods_run_unchanged_on_a_private_historical_fixture(): void
    {
        $bytes = $this->historicalM52(); IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $fixture = storage_path('app/m53-controller-compatibility-'.bin2hex(random_bytes(7)));
        File::makeDirectory($fixture.'/app/Http/Controllers', 0700, true); IsolatedTestEnvironment::assertOwnedPath($fixture);
        $file = $fixture.'/'.M52ContentPatch::CONTROLLER_PATH; File::put($file, $bytes); IsolatedTestEnvironment::assertOwnedPath($file);
        $application = app(); $originalBase = $application->basePath();
        try {
            $application->setBasePath($fixture);
            foreach (['test_exact_current_hunks_reconstruct_accepted_m51_m50_and_the_unchanged_m50_projection',
                'test_original_m50_literal_insertion_method_runs_unchanged_against_its_exact_private_historical_fixture'] as $method) {
                // These original methods are pure File/assertions. No SQL fixture setUp is invoked.
                $historical = new M52ControllerCompatibilityTest($method); $historical->$method();
            }
        } finally { $application->setBasePath($originalBase); }
        self::assertSame($originalBase, base_path()); self::assertSame($bytes, File::get($file));
    }
}
