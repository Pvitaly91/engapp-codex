<?php

namespace Tests\Feature;

use App\Services\M53ContentPatch;
use App\Services\M54ContentPatch;
use Illuminate\Support\Facades\File;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Exact new hunks preserve the accepted M53→M52→M51 compatibility chain. */
class M54ControllerCompatibilityTest extends TestCase
{
    private const CURRENT_WT_RAW_SHA = '919d4b894584b528e9d433c9073f67f6cd20b6ce2e30994f65d1cf3e314d3edc';
    private const BEFORE_M53_WT_RAW_SHA = '0500ac5f89f29884b02e64c07b563bb0e49635bd4d1c7b4009148324eb11f239';
    private const ACCEPTED_M53_GIT_LF_SHA = '7c4011378fac9be6ea063e4b6d095ff4a7ce9d7940f53f077ea3ddafd13f64e7';

    private function historicalM53(): string
    {
        $bytes = File::get(base_path(M54ContentPatch::CONTROLLER_PATH));
        self::assertSame(self::CURRENT_WT_RAW_SHA, hash('sha256', $bytes));
        $eol = str_contains($bytes, "\r\n") ? "\r\n" : "\n";
        $call = '\\App\\Support\\M54PassivePatternsPackage::preserveExcludedTextBlock';
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
        self::assertSame(self::BEFORE_M53_WT_RAW_SHA, hash('sha256', $bytes));
        self::assertSame(M53ContentPatch::CONTROLLER_WT_AFTER_SHA, hash('sha256', $bytes));
        self::assertSame(self::ACCEPTED_M53_GIT_LF_SHA, hash('sha256', str_replace("\r\n", "\n", $bytes)));
        return $bytes;
    }

    public function test_four_exact_m54_calls_and_two_null_checks_reconstruct_accepted_m53(): void
    {
        $bytes = $this->historicalM53();
        foreach (['M53PassiveConstructionsPackage', 'M52PassiveBasicsPackage', 'M51ModalMeaningsPackage', 'M50ObligationAdvicePackage'] as $package) {
            self::assertSame(4, substr_count($bytes, $package.'::preserveExcludedTextBlock'));
        }
    }

    public function test_original_m53_m52_chain_checks_run_unchanged_on_a_private_historical_fixture(): void
    {
        $bytes = $this->historicalM53();
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $fixture = storage_path('app/m54-controller-compatibility-'.bin2hex(random_bytes(7)));
        File::makeDirectory($fixture.'/app/Http/Controllers', 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($fixture);
        $file = $fixture.'/'.M54ContentPatch::CONTROLLER_PATH;
        File::put($file, $bytes); IsolatedTestEnvironment::assertOwnedPath($file);
        $application = app(); $originalBase = $application->basePath();
        try {
            $application->setBasePath($fixture);
            foreach (['test_four_m53_calls_and_two_null_checks_reconstruct_the_exact_accepted_m52_controller',
                'test_original_m52_m51_m50_compatibility_methods_run_unchanged_on_a_private_historical_fixture'] as $method) {
                $historical = new M53ControllerCompatibilityTest($method);
                $historical->$method(); // Pure file assertions; never invoke old DB fixture setup.
            }
        } finally { $application->setBasePath($originalBase); }
        self::assertSame($originalBase, base_path());
        self::assertSame($bytes, File::get($file));
    }
}
