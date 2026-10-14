<?php

namespace Tests\Feature;

use App\Services\M50ContentPatch;
use App\Services\M52ContentPatch;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/**
 * Historical literal-insertion compatibility without altering accepted M50/M51 tests.
 * Hash expectations below come from read-only accepted Git blobs, not the M52 AFTER.
 * No database fixture/setup, HTTP request, route or production access is performed.
 */
class M52ControllerCompatibilityTest extends TestCase
{
    private const M51_ACCEPTED_SHA = '0e2ed40c438400e2f2a3992314220fdf206db9a7';
    private const M51_CONTROLLER_LF_SHA = 'e19982c18fce29a12192fe74992e8daa2b645b633f89094c55ad85a950fe34be';
    private const M51_CONTROLLER_WT_BEFORE_RAW_SHA = '04078848b3a1a1a50098a247460f23d28bd845091b86669a3da88b8b0629b253';
    private const M50_ACCEPTED_SHA = '7b0ca49b3cbfd7135b80f6931cdd42834eb24c53';
    private const M50_CONTROLLER_LF_SHA = '3c876bb3b52c70475d20d9b09ac79ddeab83b581208eb8e3c2c255dcde09833b';

    /** Remove only four exact owned calls and the two null checks introduced by that package. */
    private function withoutOwnedInsertions(string $bytes, string $package): string
    {
        if (!in_array($package, ['M52PassiveBasicsPackage', 'M51ModalMeaningsPackage'], true)) {
            throw new RuntimeException('Unapproved compatibility fixture package.');
        }
        $eol = str_contains($bytes, "\r\n") ? "\r\n" : "\n";
        $call = '\\App\\Support\\'.$package.'::preserveExcludedTextBlock';
        $hunks = [
            "                    \$block = ".$call."(\$block, 'standalone_test');\n                    if (\$block === null) { continue; }\n",
            "                \$block = ".$call."(\$block, 'standalone_test');\n                if (\$block === null) { continue; }\n",
            "                if (\$textBlock) { \$textBlock = ".$call."(\$textBlock, 'standalone_test'); }\n",
            "            if (\$textBlock) { \$textBlock = ".$call."(\$textBlock, 'standalone_test'); }\n",
        ];
        foreach ($hunks as $hunk) {
            $hunk = str_replace("\n", $eol, $hunk);
            self::assertSame(1, substr_count($bytes, $hunk), $package.' exact owned insertion');
            $bytes = str_replace($hunk, '', $bytes);
        }
        self::assertStringNotContainsString($call, $bytes);

        return $bytes;
    }

    private function historicalM50Controller(): string
    {
        $current = File::get(base_path(M52ContentPatch::CONTROLLER_PATH));
        self::assertSame(M52ContentPatch::CONTROLLER_WT_AFTER_SHA, hash('sha256', $current));
        $m51 = $this->withoutOwnedInsertions($current, 'M52PassiveBasicsPackage');
        self::assertSame(self::M51_CONTROLLER_WT_BEFORE_RAW_SHA, hash('sha256', $m51), 'Independent physical WT BEFORE at '.self::M51_ACCEPTED_SHA);
        self::assertSame(self::M51_CONTROLLER_LF_SHA, hash('sha256', str_replace("\r\n", "\n", $m51)), 'Accepted M51 Git blob');
        $m50 = $this->withoutOwnedInsertions($m51, 'M51ModalMeaningsPackage');
        self::assertSame(self::M50_CONTROLLER_LF_SHA, hash('sha256', str_replace("\r\n", "\n", $m50)), 'Accepted M50 Git blob at '.self::M50_ACCEPTED_SHA);

        return $m50;
    }

    public function test_exact_current_hunks_reconstruct_accepted_m51_m50_and_the_unchanged_m50_projection(): void
    {
        $m50 = str_replace("\r\n", "\n", $this->historicalM50Controller());
        $lines = preg_split('/\r\n|\n/', $m50);
        // This is the original M50 test's own projection, applied only to its exact historical controller.
        $before = implode("\n", array_values(array_filter($lines, static fn ($line) => !str_contains($line, 'M50ObligationAdvicePackage::preserveExcludedTextBlock')
            && !in_array($line, ['                    if ($block === null) { continue; }', '                if ($block === null) { continue; }'], true))));
        self::assertSame(M50ContentPatch::CONTROLLER_GIT_BASE_LF_SHA, hash('sha256', $before));
        self::assertSame($m50, M50ContentPatch::approvedControllerAfter($before));
        self::assertSame(4, substr_count($m50, 'M50ObligationAdvicePackage::preserveExcludedTextBlock'));
    }

    public function test_original_m50_literal_insertion_method_runs_unchanged_against_its_exact_private_historical_fixture(): void
    {
        $bytes = $this->historicalM50Controller();
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $fixture = storage_path('app/m52-controller-compatibility-'.bin2hex(random_bytes(8)));
        File::makeDirectory($fixture.'/app/Http/Controllers', 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($fixture);
        $file = $fixture.'/'.M50ContentPatch::CONTROLLER_PATH;
        File::put($file, $bytes); IsolatedTestEnvironment::assertOwnedPath($file);
        self::assertSame(self::M50_CONTROLLER_LF_SHA, hash('sha256', str_replace("\r\n", "\n", File::get($file))));
        $application = app(); $originalBase = $application->basePath();
        $method = 'test_literal_controller_insertions_keep_foreign_bytes_and_refuse_ambiguous_or_already_patched_sources';
        $historical = new M50ContentPatchTest($method);
        try {
            $application->setBasePath($fixture);
            // Intentionally no setUp/run(): this original method is pure File+assertions, not its SQL fixture setup.
            $historical->$method();
        } finally { $application->setBasePath($originalBase); }
        self::assertSame($originalBase, base_path());
        self::assertSame($bytes, File::get($file));
    }
}
