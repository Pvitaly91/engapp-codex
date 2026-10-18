<?php

namespace Tests\Feature;

use App\Services\M65ContentPatch;
use App\Services\M66ContentPatch;
use Illuminate\Support\Facades\File;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Current shared files must equal accepted M65 after removing only exact finite M66 additions. */
class M66SharedLineageTest extends TestCase
{
    private const BEFORE_RECEIPT_SHA = '517118d0fe7cf764229b5926e82fa7044b10a92535923a7301bc46602311d6a3';
    private const ACCEPTED_M65 = [
        'app/Support/TheoryContentSource.php' => '53fd2fae2380cb77ba55062c276ba561bc23d3c541895e09676fc1f519f5a5a5',
        'app/Support/TheoryHtmlAdapter.php' => 'd52e27f7ae895affe4d27ed8c675e1c6be4e61bd744905106e67ec6e3fda7caf',
        'app/Support/TheoryLegacyAdapter.php' => '044069efef9191c3fdeac15f258b742ba7b24515b5f833fa0fb3ac90ef28f463',
        'app/Services/TheoryCourseManifestService.php' => '140de51e6045f8368afabfdb3a518c9593309dfbe4e91d42adce8ccf234a60fa',
        'app/Http/Controllers/TestJsV2Controller.php' => 'b0a5a89a8d9ece5c84ca49f8686fb4725bc71faaff4980b72c95ce9c54cf2f9c',
        'app/Support/TheoryContentRenderer.php' => '1c9f56f07ed5607f896a24be639374d0b28b25a590771a99729428c568f9f85c',
        'app/Support/PracticeCompletionLinkedPractice.php' => '068dc8cf42ff02784adc50ebef1dc08a2acaeeb31198b0cf2a689867b2b8e3c9',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php' => '008993484251cd69a8ca8ecdd1a8bcf6663b95c474a72ce213b84dfc311cdbfd',
    ];

    private function hunks(): array
    {
        $call = '\\App\\Support\\M66VerbPatternsCorePackage::preserveExcludedTextBlock';
        return [
            'app/Support/TheoryContentSource.php' => ["        [M66VerbPatternsCorePackage::class, 'm42StyleContext'],\n"],
            'app/Support/TheoryHtmlAdapter.php' => [
                "        if ((\$design['package'] ?? null) === 'm66-native-v1') { return M66VerbPatternsCorePackage::pointPanels(\$design); }\n",
                "        if ((\$design['package'] ?? null) === 'm66-native-v1') { return M66VerbPatternsCorePackage::validDesign(\$design); }\n",
            ],
            'app/Support/TheoryLegacyAdapter.php' => ["                \$correction = M66VerbPatternsCorePackage::correction(\$item, \$design);\n                if (\$correction !== null) { \$point = array_replace(\$point, \$correction); }\n"],
            'app/Services/TheoryCourseManifestService.php' => ["        \$page = \\App\\Support\\M66VerbPatternsCorePackage::preserveCoursePage(\$page);\n"],
            'app/Http/Controllers/TestJsV2Controller.php' => [
                "                    \$block = ".$call."(\$block, 'standalone_test');\n                    if (\$block === null) { continue; }\n",
                "                \$block = ".$call."(\$block, 'standalone_test');\n                if (\$block === null) { continue; }\n",
                "                if (\$textBlock) { \$textBlock = ".$call."(\$textBlock, 'standalone_test'); }\n",
                "            if (\$textBlock) { \$textBlock = ".$call."(\$textBlock, 'standalone_test'); }\n",
            ],
            'app/Support/TheoryContentRenderer.php' => ["                \$bindings = M66BuilderSelectionPresentation::bindings(\$bindings);\n"],
            'app/Support/PracticeCompletionLinkedPractice.php' => ["        \$block = M66BuilderSelectionPresentation::matchingBlock(\$block, \$canonicalTheory);\n"],
            'app/Services/Theory/TextBlockToQuestionsMatcherService.php' => ["            \$excludeQuestionIds = \\App\\Support\\M66BuilderSelectionPresentation::exclusions(\$block, \$linkedPractice, \$excludeQuestionIds);\n"],
        ];
    }

    public function test_eight_current_shared_seams_reconstruct_exact_m65_and_preserve_foreign_root_controller_bytes_separately(): void
    {
        $rootProfile = str_replace('\\', '/', base_path()) === 'D:/DEV/htdocs/gramlyze.loc';
        $capture = null;
        if ($rootProfile) {
            $file = base_path('storage/app/seo-m66-local/before-v1.json');
            self::assertSame(self::BEFORE_RECEIPT_SHA, hash_file('sha256', $file));
            $capture = json_decode(File::get($file), true, flags: JSON_THROW_ON_ERROR);
        }
        self::assertSame(array_keys(self::ACCEPTED_M65), array_keys($this->hunks()));
        foreach ($this->hunks() as $path => $hunks) {
            $original = File::get(base_path($path)); $bytes = $original;
            // Exact accepted hunk EOLs, including the mixed-EOL course service.
            // Do not normalize a complete file or strip arbitrary M66 lines.
            $eol = in_array($path, ['app/Services/TheoryCourseManifestService.php',
                'app/Http/Controllers/TestJsV2Controller.php',
                'app/Services/Theory/TextBlockToQuestionsMatcherService.php'], true) ? "\r\n" : "\n";
            foreach ($hunks as $hunk) {
                $literal = str_replace("\n", $eol, $hunk);
                self::assertSame(1, substr_count($bytes, $literal), $path.': one literal owned hunk, not broad filtering');
                $bytes = str_replace($literal, '', $bytes);
            }
            self::assertStringNotContainsString('M66', $bytes, $path.': no unreviewed M66 insertions remain');
            $expected = $rootProfile ? $capture['shared_source_hashes'][$path]['root'] : self::ACCEPTED_M65[$path];
            self::assertSame($expected, hash('sha256', $bytes), $path.': exact pre-M66 bytes');
            if ($rootProfile) { self::assertSame(self::ACCEPTED_M65[$path], $capture['shared_source_hashes'][$path]['worktree']); }
            self::assertSame($original, File::get(base_path($path)), 'No file writes while reconstructing.');
            if ($path === M65ContentPatch::CONTROLLER_PATH && !$rootProfile) {
                self::assertSame(M65ContentPatch::CONTROLLER_WT_AFTER_SHA, hash('sha256', $bytes));
            }
        }
    }

    public function test_original_m65_controller_chain_assertions_run_unchanged_on_exact_private_accepted_fixture(): void
    {
        if (str_replace('\\', '/', base_path()) === 'D:/DEV/htdocs/gramlyze.loc') {
            $this->markTestSkipped('Accepted-WT historical controller chain runs in the versioned worktree; ROOT foreign controller bytes have separate BEFORE and current-runtime coverage.');
        }
        $path = M65ContentPatch::CONTROLLER_PATH;
        $original = File::get(base_path($path));
        self::assertSame(M66ContentPatch::CONTROLLER_WT_AFTER_SHA, hash('sha256', $original));
        $bytes = $original;
        foreach ($this->hunks()[$path] as $hunk) {
            $literal = str_replace("\n", "\r\n", $hunk);
            self::assertSame(1, substr_count($bytes, $literal), 'One exact owned M66 controller insertion.');
            $bytes = str_replace($literal, '', $bytes);
        }
        self::assertStringNotContainsString('M66', $bytes);
        self::assertSame(self::ACCEPTED_M65[$path], hash('sha256', $bytes));
        self::assertSame(M65ContentPatch::CONTROLLER_WT_AFTER_SHA, hash('sha256', $bytes));
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $fixture = storage_path('app/m66-controller-compatibility-'.bin2hex(random_bytes(7)));
        File::makeDirectory($fixture.'/app/Http/Controllers', 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($fixture);
        $file = $fixture.'/'.$path;
        File::put($file, $bytes); IsolatedTestEnvironment::assertOwnedPath($file);
        $application = app(); $originalBase = $application->basePath();
        try {
            $application->setBasePath($fixture);
            foreach (['test_four_exact_m65_calls_and_two_null_checks_reconstruct_accepted_m64',
                'test_original_m64_chain_checks_run_unchanged_on_a_private_historical_fixture'] as $method) {
                $historical = new M65ControllerCompatibilityTest($method);
                $historical->$method(); // Original pure file assertions; never invoke historical DB setup.
            }
        } finally { $application->setBasePath($originalBase); }
        self::assertSame($originalBase, base_path());
        self::assertSame($bytes, File::get($file));
        self::assertSame($original, File::get(base_path($path)), 'Current controller was never replaced.');
    }

    public function test_native_release_and_patch_primitives_remain_literal_m65_bytes(): void
    {
        foreach ([
            'app/Support/TheoryNativeContentRelease.php' => 'c6d93539fa4b0fa70309af2bd9f27d0317ba267e016e2c5a6820182f51b49632',
            'app/Services/TheoryNativeContentPatch.php' => 'f521ac6f93f77dd240e70b1a24b4c69e668830ddf3fc484f9ad4cd8c27351a38',
        ] as $path => $expected) { self::assertSame($expected, hash_file('sha256', base_path($path)), $path); }
    }
}
