<?php

namespace Tests\Feature;

use App\Support\M67VerbPatternsCompletion as M67;
use App\Support\M66VerbPatternsCorePackage as M66;
use Illuminate\Support\Facades\File;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Only exact M68 hunks on copies; raw mixed EOL and foreign ROOT controller bytes stay distinct. */
class M68SharedLineageTest extends TestCase
{
    private const ACCEPTED_M67 = [
        'app/Support/TheoryContentSource.php' => 'f622cd74b71ac4b8ba902bd9e7c6a6f7c5632666364801baec30c8ddd0bbe8e9',
        'app/Support/TheoryHtmlAdapter.php' => '23db10a3e6e021eb801320cb90f5dd8e7c27d5a4ba66598eb2054eee09c8b099',
        'app/Support/TheoryLegacyAdapter.php' => 'c528f66467b07dfe42cdd7eb4e1c71f6ecfcb04d40e9483e616cfcf415fd3df8',
        'app/Support/TheoryContentRenderer.php' => 'b8cae634563625720ee16d07b9de19b76b38e6ebe971dedd33eb29c8db896335',
        'app/Support/PracticeCompletionLinkedPractice.php' => 'f90cefc5c851a0a367341858fea1f4fe9d4f09dc76c2df75d03add6df9224c53',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php' => '909b1883be80280923bc1d84322a30d58166ad52885d0737ecd8122df3826ae2',
        'app/Services/TheoryCourseManifestService.php' => '2c3061408f0f23d792bd7c28616ab84d7ade8cf20afecddd5679e80ab590b404',
        'app/Http/Controllers/TestJsV2Controller.php' => 'f0a9ef016825c8b33feeca1d5bc2eedb366088879a18348e6a87b5f89dfb9b39',
    ];
    private const ROOT_CONTROLLER_BEFORE = '0b533601c495af35eb64bcbb36ee47d3a6e59a323d8a96800354fa9bbff01c43';

    private function hunks(): array
    {
        return [
            'app/Support/TheoryContentSource.php' => [
                ['after' => "\n        [M67VerbPatternsCompletion::class, 'm42StyleContext'],\n        [M68ComparisonBasics::class, 'm42StyleContext'],\n", 'before' => "\n        [M67VerbPatternsCompletion::class, 'm42StyleContext'],\n"],
            ],
            'app/Support/TheoryHtmlAdapter.php' => [
                ['after' => "\n        if ((\$design['package'] ?? null) === 'm67-native-v1') { return M67VerbPatternsCompletion::pointPanels(\$design); }\n        if ((\$design['package'] ?? null) === 'm68-native-v1') { return M68ComparisonBasics::pointPanels(\$design); }\n", 'before' => "\n        if ((\$design['package'] ?? null) === 'm67-native-v1') { return M67VerbPatternsCompletion::pointPanels(\$design); }\n"],
                ['after' => "\n        if ((\$design['package'] ?? null) === 'm67-native-v1') { return M67VerbPatternsCompletion::validDesign(\$design); }\n        if ((\$design['package'] ?? null) === 'm68-native-v1') { return M68ComparisonBasics::validDesign(\$design); }\n", 'before' => "\n        if ((\$design['package'] ?? null) === 'm67-native-v1') { return M67VerbPatternsCompletion::validDesign(\$design); }\n"],
            ],
            'app/Support/TheoryLegacyAdapter.php' => [
                ['after' => "\n                \$correction = M67VerbPatternsCompletion::correction(\$item, \$design);\n                if (\$correction !== null) { \$point = array_replace(\$point, \$correction); }\n                \$correction = M68ComparisonBasics::correction(\$item, \$design);\n                if (\$correction !== null) { \$point = array_replace(\$point, \$correction); }\n", 'before' => "\n                \$correction = M67VerbPatternsCompletion::correction(\$item, \$design);\n                if (\$correction !== null) { \$point = array_replace(\$point, \$correction); }\n"],
                ['after' => "\n        \$type = M68ComparisonBasics::semanticComponent(\$block, \$data, \$design) ?? M55PassiveReviewInfinitivePackage::semanticComponent(\$block, \$data, \$design) ?? \$storedType;\n", 'before' => "\n        \$type = M55PassiveReviewInfinitivePackage::semanticComponent(\$block, \$data, \$design) ?? \$storedType;\n"],
            ],
            'app/Support/TheoryContentRenderer.php' => [
                ['after' => "\n                \$bindings = M67BuilderSelectionPresentation::bindings(\$bindings);\n                \$bindings = M68BuilderSelectionPresentation::bindings(\$bindings);\n", 'before' => "\n                \$bindings = M67BuilderSelectionPresentation::bindings(\$bindings);\n"],
            ],
            'app/Support/PracticeCompletionLinkedPractice.php' => [
                ['after' => "\n        \$block = M67BuilderSelectionPresentation::matchingBlock(\$block, \$canonicalTheory);\n        \$block = M68BuilderSelectionPresentation::matchingBlock(\$block, \$canonicalTheory);\n", 'before' => "\n        \$block = M67BuilderSelectionPresentation::matchingBlock(\$block, \$canonicalTheory);\n"],
            ],
            'app/Services/Theory/TextBlockToQuestionsMatcherService.php' => [
                ['after' => "\n            \$excludeQuestionIds = \\App\\Support\\M67BuilderSelectionPresentation::exclusions(\$block, \$linkedPractice, \$excludeQuestionIds);\r\n            \$excludeQuestionIds = \\App\\Support\\M68BuilderSelectionPresentation::exclusions(\$block, \$linkedPractice, \$excludeQuestionIds);\r\n", 'before' => "\n            \$excludeQuestionIds = \\App\\Support\\M67BuilderSelectionPresentation::exclusions(\$block, \$linkedPractice, \$excludeQuestionIds);\r\n"],
            ],
            'app/Services/TheoryCourseManifestService.php' => [
                ['after' => "\n        \$page = \\App\\Support\\M67VerbPatternsCompletion::preserveCoursePage(\$page);\r\n        \$page = \\App\\Support\\M68ComparisonBasics::preserveCoursePage(\$page);\r\n", 'before' => "\n        \$page = \\App\\Support\\M67VerbPatternsCompletion::preserveCoursePage(\$page);\r\n"],
            ],
            'app/Http/Controllers/TestJsV2Controller.php' => [
                ['after' => "\n                    \$block = \\App\\Support\\M67VerbPatternsCompletion::preserveExcludedTextBlock(\$block, 'standalone_test');\r\n                    if (\$block !== null) { \$block = \\App\\Support\\M68ComparisonBasics::preserveExcludedTextBlock(\$block, 'standalone_test'); }\r\n", 'before' => "\n                    \$block = \\App\\Support\\M67VerbPatternsCompletion::preserveExcludedTextBlock(\$block, 'standalone_test');\r\n"],
                ['after' => "\n                if (\$textBlock) { \$textBlock = \\App\\Support\\M67VerbPatternsCompletion::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\r\n                if (\$textBlock) { \$textBlock = \\App\\Support\\M68ComparisonBasics::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\r\n", 'before' => "\n                if (\$textBlock) { \$textBlock = \\App\\Support\\M67VerbPatternsCompletion::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\r\n"],
                ['after' => "\n                \$block = \\App\\Support\\M67VerbPatternsCompletion::preserveExcludedTextBlock(\$block, 'standalone_test');\r\n                if (\$block !== null) { \$block = \\App\\Support\\M68ComparisonBasics::preserveExcludedTextBlock(\$block, 'standalone_test'); }\r\n", 'before' => "\n                \$block = \\App\\Support\\M67VerbPatternsCompletion::preserveExcludedTextBlock(\$block, 'standalone_test');\r\n"],
                ['after' => "\n            if (\$textBlock) { \$textBlock = \\App\\Support\\M67VerbPatternsCompletion::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\r\n            if (\$textBlock) { \$textBlock = \\App\\Support\\M68ComparisonBasics::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\r\n", 'before' => "\n            if (\$textBlock) { \$textBlock = \\App\\Support\\M67VerbPatternsCompletion::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\r\n"],
            ],
        ];
    }

    private function acceptedBytes(string $path): string
    {
        $bytes = File::get(base_path($path));
        foreach ($this->hunks()[$path] as $hunk) {
            self::assertSame(1, substr_count($bytes, $hunk['after']), $path.': one exact owned hunk including captured raw EOL');
            $bytes = str_replace($hunk['after'], $hunk['before'], $bytes);
        }
        self::assertStringNotContainsString('M68', $bytes, $path);
        return $bytes;
    }

    public function test_all_eight_current_shared_files_reconstruct_accepted_m67_with_exact_thirteen_literal_hunks(): void
    {
        self::assertSame(array_keys(self::ACCEPTED_M67), array_keys($this->hunks()));
        self::assertSame(13, array_sum(array_map('count', $this->hunks())));
        $root = str_replace('\\', '/', base_path()) === 'D:/DEV/htdocs/gramlyze.loc';
        foreach (self::ACCEPTED_M67 as $path => $sha) {
            $original = File::get(base_path($path));
            $expected = $root && $path === 'app/Http/Controllers/TestJsV2Controller.php' ? self::ROOT_CONTROLLER_BEFORE : $sha;
            self::assertSame($expected, hash('sha256', $this->acceptedBytes($path)), $path);
            self::assertSame($original, File::get(base_path($path)), 'Never normalize or restore real source files.');
        }
    }

    public function test_unchanged_m67_m66_m65_lineage_assertions_run_on_exact_private_accepted_worktree_fixture(): void
    {
        if (str_replace('\\', '/', base_path()) === 'D:/DEV/htdocs/gramlyze.loc') {
            $this->markTestSkipped('Accepted-WT lineage runs in versioned worktree; foreign ROOT controller has its own captured raw BEFORE and current-runtime coverage.');
        }
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $fixture = storage_path('app/m68-accepted-m67-lineage-'.bin2hex(random_bytes(7)));
        File::makeDirectory($fixture, 0700, true); IsolatedTestEnvironment::assertOwnedPath($fixture);
        $originals = [];
        foreach (self::ACCEPTED_M67 as $path => $sha) {
            $originals[$path] = File::get(base_path($path)); $bytes = $this->acceptedBytes($path);
            self::assertSame($sha, hash('sha256', $bytes));
            File::makeDirectory(dirname($fixture.'/'.$path), 0700, true, true); File::put($fixture.'/'.$path, $bytes);
        }
        $application = app(); $base = $application->basePath();
        try {
            $application->setBasePath($fixture);
            foreach (['test_current_shared_seams_reconstruct_independent_accepted_m66_before_without_touching_files',
                'test_unchanged_m66_and_nested_m65_lineage_assertions_run_on_exact_private_accepted_fixture'] as $method) {
                $historical = new M67SharedLineageTest($method); $historical->$method();
            }
        } finally { $application->setBasePath($base); }
        self::assertSame($base, base_path());
        foreach ($originals as $path => $bytes) { self::assertSame($bytes, File::get(base_path($path))); }
    }

    public function test_native_primitives_h1_and_frozen_m67_m66_releases_are_still_literal(): void
    {
        foreach ([
            'app/Support/TheoryNativeContentRelease.php' => 'c6d93539fa4b0fa70309af2bd9f27d0317ba267e016e2c5a6820182f51b49632',
            'app/Services/TheoryNativeContentPatch.php' => 'f521ac6f93f77dd240e70b1a24b4c69e668830ddf3fc484f9ad4cd8c27351a38',
            'app/Services/TheoryLocalReleaseGuard.php' => '4e608e71541d99409cb939fca5a2ff65adedc3d4caa74cc833a8886d46bb1a7c',
            'app/Http/Controllers/PageController.php' => '491672fed84cc11a690f24d9111999049e258ad5a06f881149495a102b8d1659',
            'docs/content/m67-source-checksums.v1.json' => 'a1ec7f331d1ea457f94b158ff5f1420056a82ef91029d286007cfe2b2a7151fd',
            'docs/content/m66-verb-patterns-core-checksums.v3.json' => '7d06b0c11561c74239ee19b7955e98a3c3244f948934910c71fa738cfd6bf773',
        ] as $path => $sha) { self::assertSame($sha, hash_file('sha256', base_path($path)), $path); }
        foreach (['docs/content/m67-source-checksums.v1.json' => 8, 'docs/content/m66-verb-patterns-core-checksums.v3.json' => 21] as $manifest => $count) {
            $checksums = json_decode(File::get(base_path($manifest)), true, flags: JSON_THROW_ON_ERROR);
            self::assertCount($count, $checksums['artifacts']);
            foreach ($checksums['artifacts'] as $artifact) {
                self::assertSame($artifact['sha256'], hash_file('sha256', base_path($artifact['path'])), $artifact['path']);
                self::assertSame($artifact['bytes'], filesize(base_path($artifact['path'])), $artifact['path']);
            }
        }
        [$before, $patch, $master, $contract] = M67::load(); M67::validate($before, $patch, $master, $contract);
        self::assertSame('1.0.0', $master['version']);
        [$before, $patch, $master, $contract] = M66::load(); M66::validate($before, $patch, $master, $contract);
        self::assertSame('1.0.2', $master['version']);
        self::assertSame('підмет + can / could / must / should / may / might / will + базова форма',
            $master['lessons'][0]['blocks'][1]['data']['items'][0]['title']);
    }
}
