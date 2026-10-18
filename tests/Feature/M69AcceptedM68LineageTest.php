<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/** Remove only thirteen M69 dispatch lines on copies; accepted M68 sources and assertions stay literal. */
class M69AcceptedM68LineageTest extends TestCase
{
    private const ACCEPTED_M68 = [
        'app/Support/TheoryContentSource.php'=>'6db75769c7fd9a1bd4b4c0d812d34bff761b8af27d46e4353a95ffd99650ee48',
        'app/Support/TheoryHtmlAdapter.php'=>'2f0e055d40433ffeefde55b75649b4bb782a9a6957699b2cc1e0c779f470c48c',
        'app/Support/TheoryLegacyAdapter.php'=>'4ec27365d8da7c91839f1ca6673b63c4709493f48f4812b8fd4a4278f5160a64',
        'app/Support/TheoryContentRenderer.php'=>'13ee3989193bf8dabe1b7dfe3af37f9586dad56a87fd4f07bc0fa61564f2bb82',
        'app/Support/PracticeCompletionLinkedPractice.php'=>'23cd62c8931e21dd85110554284f767eaa049a2ba01f401fb9468a98f2ff9679',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php'=>'f051e40649fa8080e6399d122d696f58d745b8df73f4bdca8b33d3c8a9845977',
        'app/Services/TheoryCourseManifestService.php'=>'9bb2384894499f83fa36dd0c8f25f939f1f7b187281d5e516adc783e2cf1f2d9',
        'app/Http/Controllers/TestJsV2Controller.php'=>'a8a3964cb38d72c6e0075b16c77b3355f039a361881854fd68489b74f23caabb',
    ];

    private function hunks(): array
    {
        return [
            'app/Support/TheoryContentSource.php'=>[
                ['anchor'=>"\n        [M68ComparisonBasics::class, 'm42StyleContext'],\n",
                    'insert'=>"        [M69AdjectivesAdverbs::class, 'm42StyleContext'],\n"],
            ],
            'app/Support/TheoryHtmlAdapter.php'=>[
                ['anchor'=>"\n        if ((\$design['package'] ?? null) === 'm68-native-v1') { return M68ComparisonBasics::pointPanels(\$design); }\n",
                    'insert'=>"        if ((\$design['package'] ?? null) === 'm69-native-v1') { return M69AdjectivesAdverbs::pointPanels(\$design); }\n"],
                ['anchor'=>"\n        if ((\$design['package'] ?? null) === 'm68-native-v1') { return M68ComparisonBasics::validDesign(\$design); }\n",
                    'insert'=>"        if ((\$design['package'] ?? null) === 'm69-native-v1') { return M69AdjectivesAdverbs::validDesign(\$design); }\n"],
            ],
            'app/Support/TheoryLegacyAdapter.php'=>[
                ['anchor'=>"\n                \$correction = M68ComparisonBasics::correction(\$item, \$design);\n                if (\$correction !== null) { \$point = array_replace(\$point, \$correction); }\n",
                    'insert'=>"                \$correction = M69AdjectivesAdverbs::correction(\$item, \$design);\n                if (\$correction !== null) { \$point = array_replace(\$point, \$correction); }\n"],
            ],
            'app/Support/TheoryContentRenderer.php'=>[
                ['anchor'=>"\n                \$bindings = M68BuilderSelectionPresentation::bindings(\$bindings);\n",
                    'insert'=>"                \$bindings = M69BuilderSelectionPresentation::bindings(\$bindings);\n"],
            ],
            'app/Support/PracticeCompletionLinkedPractice.php'=>[
                ['anchor'=>"\n        \$block = M68BuilderSelectionPresentation::matchingBlock(\$block, \$canonicalTheory);\n",
                    'insert'=>"        \$block = M69BuilderSelectionPresentation::matchingBlock(\$block, \$canonicalTheory);\n"],
            ],
            'app/Services/Theory/TextBlockToQuestionsMatcherService.php'=>[
                ['anchor'=>"\n            \$excludeQuestionIds = \\App\\Support\\M68BuilderSelectionPresentation::exclusions(\$block, \$linkedPractice, \$excludeQuestionIds);\r\n",
                    'insert'=>"            \$excludeQuestionIds = \\App\\Support\\M69BuilderSelectionPresentation::exclusions(\$block, \$linkedPractice, \$excludeQuestionIds);\r\n"],
            ],
            'app/Services/TheoryCourseManifestService.php'=>[
                ['anchor'=>"\n        \$page = \\App\\Support\\M68ComparisonBasics::preserveCoursePage(\$page);\r\n",
                    'insert'=>"        \$page = \\App\\Support\\M69AdjectivesAdverbs::preserveCoursePage(\$page);\r\n"],
            ],
            'app/Http/Controllers/TestJsV2Controller.php'=>[
                ['anchor'=>"\n                    if (\$block !== null) { \$block = \\App\\Support\\M68ComparisonBasics::preserveExcludedTextBlock(\$block, 'standalone_test'); }\r\n",
                    'insert'=>"                    if (\$block !== null) { \$block = \\App\\Support\\M69AdjectivesAdverbs::preserveExcludedTextBlock(\$block, 'standalone_test'); }\r\n"],
                ['anchor'=>"\n                if (\$textBlock) { \$textBlock = \\App\\Support\\M68ComparisonBasics::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\r\n",
                    'insert'=>"                if (\$textBlock) { \$textBlock = \\App\\Support\\M69AdjectivesAdverbs::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\r\n"],
                ['anchor'=>"\n                if (\$block !== null) { \$block = \\App\\Support\\M68ComparisonBasics::preserveExcludedTextBlock(\$block, 'standalone_test'); }\r\n",
                    'insert'=>"                if (\$block !== null) { \$block = \\App\\Support\\M69AdjectivesAdverbs::preserveExcludedTextBlock(\$block, 'standalone_test'); }\r\n"],
                ['anchor'=>"\n            if (\$textBlock) { \$textBlock = \\App\\Support\\M68ComparisonBasics::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\r\n",
                    'insert'=>"            if (\$textBlock) { \$textBlock = \\App\\Support\\M69AdjectivesAdverbs::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\r\n"],
            ],
        ];
    }

    public function test_current_shared_eight_reconstruct_exact_accepted_m68_using_only_thirteen_added_lines_in_twelve_hunks(): void
    {
        self::assertSame('C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc', str_replace('\\', '/', base_path()),
            'This assertion is for accepted WT raw bytes; independent ROOT foreign/EOL evidence is a separate audit.');
        self::assertSame(array_keys(self::ACCEPTED_M68), array_keys($this->hunks()));
        self::assertSame(12, array_sum(array_map('count', $this->hunks())));
        $removedLines = 0;
        foreach (self::ACCEPTED_M68 as $path=>$expected) {
            $original = File::get(base_path($path)); $accepted = $original;
            foreach ($this->hunks()[$path] as $hunk) {
                $after = $hunk['anchor'].$hunk['insert'];
                self::assertSame(1, substr_count($accepted, $after), $path.': exact owned raw-EOL hunk');
                $accepted = str_replace($after, $hunk['anchor'], $accepted);
                $removedLines += substr_count($hunk['insert'], "\n");
            }
            self::assertStringNotContainsString('M69', $accepted, $path);
            self::assertSame($expected, hash('sha256', $accepted), $path.': accepted M68 raw source');
            self::assertSame($original, File::get(base_path($path)), 'No writes, source normalization or fixture substitution.');
        }
        self::assertSame(13, $removedLines);
    }
}
