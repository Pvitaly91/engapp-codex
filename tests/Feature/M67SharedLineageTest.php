<?php

namespace Tests\Feature;

use App\Support\M66VerbPatternsCorePackage as M66;
use Illuminate\Support\Facades\File;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Remove only exact M67 additions on copies; accepted M66 bytes and older assertions stay unchanged. */
class M67SharedLineageTest extends TestCase
{
    private const ACCEPTED_M66 = [
        'app/Support/TheoryContentSource.php' => '0a2040df0f4ae6ab53cf9ad06ef0a0cb1a6b0081086828ed5bc89b8f8dee9764',
        'app/Support/TheoryHtmlAdapter.php' => '0ae22762871c41fb8494966d49451be8c53884943c22178243b74aabf6f665c8',
        'app/Support/TheoryLegacyAdapter.php' => '9526cf11805dab38112529bc90609281b6495adda8622bf8aec5bc4e73ddb2a2',
        'app/Services/TheoryCourseManifestService.php' => '69cc9f8ee400d88b987c428424c04008e20e50b10803d793a6fd0f468f4b0783',
        'app/Http/Controllers/TestJsV2Controller.php' => '03db15b6d53381df1801c08ee380d29714ec3bb7bfab49484f3f38b47d4483c6',
        'app/Support/TheoryContentRenderer.php' => 'a7a5700f8a1543699d2024f56bd8ba9d5ee5b3f5621ac197a77fdf9f7a98080f',
        'app/Support/PracticeCompletionLinkedPractice.php' => '32f1d6248c4934fb7fe24db8114ec97fd3e5a1ace9bb6aebf21ab659a6d0ce8c',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php' => '19007d9164588fad8cd1cf5a9f3b52bb08852517b8f5b9671eaff74167d3c1dc',
    ];
    private const ROOT_CONTROLLER_BEFORE = '22f12d2756503c50df3064cb2c9e6187d9beb35859b3469a9f122ce7d4ea7e3e';

    private function hunks(): array
    {
        $call = '\\App\\Support\\M67VerbPatternsCompletion::preserveExcludedTextBlock';
        return [
            'app/Support/TheoryContentSource.php' => ["        [M67VerbPatternsCompletion::class, 'm42StyleContext'],\n"],
            'app/Support/TheoryHtmlAdapter.php' => [
                "        if ((\$design['package'] ?? null) === 'm67-native-v1') { return M67VerbPatternsCompletion::pointPanels(\$design); }\n",
                "        if ((\$design['package'] ?? null) === 'm67-native-v1') { return M67VerbPatternsCompletion::validDesign(\$design); }\n",
            ],
            'app/Support/TheoryLegacyAdapter.php' => ["                \$correction = M67VerbPatternsCompletion::correction(\$item, \$design);\n                if (\$correction !== null) { \$point = array_replace(\$point, \$correction); }\n"],
            'app/Services/TheoryCourseManifestService.php' => ["        \$page = \\App\\Support\\M67VerbPatternsCompletion::preserveCoursePage(\$page);\n"],
            'app/Http/Controllers/TestJsV2Controller.php' => [
                "                    \$block = ".$call."(\$block, 'standalone_test');\n                    if (\$block === null) { continue; }\n",
                "                \$block = ".$call."(\$block, 'standalone_test');\n                if (\$block === null) { continue; }\n",
                "                if (\$textBlock) { \$textBlock = ".$call."(\$textBlock, 'standalone_test'); }\n",
                "            if (\$textBlock) { \$textBlock = ".$call."(\$textBlock, 'standalone_test'); }\n",
            ],
            'app/Support/TheoryContentRenderer.php' => ["                \$bindings = M67BuilderSelectionPresentation::bindings(\$bindings);\n"],
            'app/Support/PracticeCompletionLinkedPractice.php' => ["        \$block = M67BuilderSelectionPresentation::matchingBlock(\$block, \$canonicalTheory);\n"],
            'app/Services/Theory/TextBlockToQuestionsMatcherService.php' => ["            \$excludeQuestionIds = \\App\\Support\\M67BuilderSelectionPresentation::exclusions(\$block, \$linkedPractice, \$excludeQuestionIds);\n"],
        ];
    }

    private function acceptedBytes(string $path): string
    {
        $bytes = File::get(base_path($path));
        $eol = in_array($path, ['app/Services/TheoryCourseManifestService.php',
            'app/Http/Controllers/TestJsV2Controller.php',
            'app/Services/Theory/TextBlockToQuestionsMatcherService.php'], true) ? "\r\n" : "\n";
        foreach ($this->hunks()[$path] as $hunk) {
            $literal = str_replace("\n", $eol, $hunk);
            self::assertSame(1, substr_count($bytes, $literal), $path.': exact owned hunk, not broad source filtering');
            $bytes = str_replace($literal, '', $bytes);
        }
        self::assertStringNotContainsString('M67', $bytes, $path);
        return $bytes;
    }

    public function test_current_shared_seams_reconstruct_independent_accepted_m66_before_without_touching_files(): void
    {
        self::assertSame(array_keys(self::ACCEPTED_M66), array_keys($this->hunks()));
        $root = str_replace('\\', '/', base_path()) === 'D:/DEV/htdocs/gramlyze.loc';
        foreach (self::ACCEPTED_M66 as $path => $sha) {
            $original = File::get(base_path($path));
            $expected = $root && $path === 'app/Http/Controllers/TestJsV2Controller.php' ? self::ROOT_CONTROLLER_BEFORE : $sha;
            self::assertSame($expected, hash('sha256', $this->acceptedBytes($path)), $path);
            self::assertSame($original, File::get(base_path($path)));
        }
    }

    public function test_unchanged_m66_and_nested_m65_lineage_assertions_run_on_exact_private_accepted_fixture(): void
    {
        if (str_replace('\\', '/', base_path()) === 'D:/DEV/htdocs/gramlyze.loc') {
            $this->markTestSkipped('Historical accepted-WT M66/M65 chain runs in the versioned worktree; foreign ROOT controller bytes have an independent exact BEFORE and current-runtime coverage.');
        }
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $fixture = storage_path('app/m67-accepted-m66-lineage-'.bin2hex(random_bytes(7)));
        File::makeDirectory($fixture, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($fixture);
        $originals = [];
        foreach (self::ACCEPTED_M66 as $path => $sha) {
            $originals[$path] = File::get(base_path($path));
            $bytes = $this->acceptedBytes($path);
            self::assertSame($sha, hash('sha256', $bytes));
            File::makeDirectory(dirname($fixture.'/'.$path), 0700, true, true);
            File::put($fixture.'/'.$path, $bytes);
        }
        $application = app(); $base = $application->basePath();
        try {
            $application->setBasePath($fixture);
            foreach (['test_eight_current_shared_seams_reconstruct_exact_m65_and_preserve_foreign_root_controller_bytes_separately',
                'test_original_m65_controller_chain_assertions_run_unchanged_on_exact_private_accepted_fixture'] as $method) {
                $historical = new M66SharedLineageTest($method);
                $historical->$method();
            }
        } finally { $application->setBasePath($base); }
        self::assertSame($base, base_path());
        foreach ($originals as $path => $bytes) { self::assertSame($bytes, File::get(base_path($path))); }
    }

    public function test_native_primitives_global_title_controller_and_all_frozen_m66_release_bytes_stay_exact(): void
    {
        foreach ([
            'app/Support/TheoryNativeContentRelease.php' => 'c6d93539fa4b0fa70309af2bd9f27d0317ba267e016e2c5a6820182f51b49632',
            'app/Services/TheoryNativeContentPatch.php' => 'f521ac6f93f77dd240e70b1a24b4c69e668830ddf3fc484f9ad4cd8c27351a38',
            'app/Http/Controllers/PageController.php' => '491672fed84cc11a690f24d9111999049e258ad5a06f881149495a102b8d1659',
            'docs/content/m66-verb-patterns-core-checksums.v3.json' => '7d06b0c11561c74239ee19b7955e98a3c3244f948934910c71fa738cfd6bf773',
        ] as $path => $sha) { self::assertSame($sha, hash_file('sha256', base_path($path)), $path); }
        $checksums = json_decode(File::get(base_path('docs/content/m66-verb-patterns-core-checksums.v3.json')), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(21, $checksums['artifacts']);
        foreach ($checksums['artifacts'] as $artifact) {
            self::assertSame($artifact['sha256'], hash_file('sha256', base_path($artifact['path'])), $artifact['path']);
            self::assertSame($artifact['bytes'], filesize(base_path($artifact['path'])), $artifact['path']);
        }
        [$before, $patch, $master, $contract] = M66::load(); M66::validate($before, $patch, $master, $contract);
        self::assertSame('1.0.2', $master['version']);
        self::assertSame('підмет + can / could / must / should / may / might / will + базова форма',
            $master['lessons'][0]['blocks'][1]['data']['items'][0]['title']);
        self::assertSame('Тітка дозволила; я обрав піти пішки',
            $master['lessons'][0]['blocks'][6]['data']['choices'][1]['answer']);
    }
}
