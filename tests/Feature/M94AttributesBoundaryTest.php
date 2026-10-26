<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/** One finite delivery-format guard; no application, DB, HTTP, Git or source writes. */
class M94AttributesBoundaryTest extends TestCase
{
    public function test_only_thirty_exact_m94_lf_paths_follow_the_entire_accepted_attributes_prefix(): void
    {
        $root = str_replace('\\', '/', getenv('GRAMLYZE_M94_SERVED_ROOT') ?: dirname(__DIR__, 2));
        $wt = str_replace('\\', '/', getenv('GRAMLYZE_M94_SOURCE_WORKTREE') ?: $root.'/storage/app/m75-worktree');
        self::assertNotSame(realpath($root), realpath($wt));
        self::assertFileDoesNotExist($root.'/.gitattributes');
        $raw = file_get_contents($wt.'/.gitattributes');
        self::assertSame('642e28142a57290510f29b95835dc6fc2154b995f43818a246cacb4e251e1c0b', hash('sha256', substr($raw, 0, 81742)));
        $paths = [
            'app/Console/Commands/ContentPatchM94.php',
            'app/Services/M94ContentPatch.php',
            'app/Services/M94LocalTargetGuard.php',
            'app/Support/M94BuilderSelectionPresentation.php',
            'app/Support/M94RelativeClausesWhoseWhich.php',
            'database/content-patches/m94-relative-clauses-whose-which.v1.json',
            'docs/content/m94-relative-clauses-whose-which.before.v1.json',
            'docs/content/m94-relative-clauses-whose-which.bindings.v1.json',
            'docs/content/m94-relative-clauses-whose-which.checksums.v1.json',
            'docs/content/m94-relative-clauses-whose-which.components.v1.json',
            'docs/content/m94-relative-clauses-whose-which.coverage.v1.json',
            'docs/content/m94-relative-clauses-whose-which.editorial.v1.json',
            'docs/content/m94-relative-clauses-whose-which.navigation.v1.json',
            'docs/content/m94-relative-clauses-whose-which.tasks.v1.json',
            'docs/content/m94-relative-clauses-whose-which.uk.md',
            'docs/content/m94-relative-clauses-whose-which.v1.json',
            'docs/content/m94-sentence-builder-bank-audit.v1.json',
            'docs/content/theory-sentence-builders/m94-relative-clauses-whose-which.v1.json',
            'docs/reports/seo-m94-relative-clauses-whose-which-standard-view.md',
            'tests/Browser/m94-native-practice.cjs',
            'tests/Feature/M94AttributesBoundaryTest.php',
            'tests/Feature/M94LocalGuardTest.php',
            'tests/Feature/M94RelativeClausesWhoseWhichContentPatchTest.php',
            'tests/Feature/M94RelativeClausesWhoseWhichNativePracticeTest.php',
            'tests/Feature/M94RelativeClausesWhoseWhichPackageTest.php',
            'tests/Feature/M94RelativeClausesWhoseWhichRuntimeTest.php',
            'tests/Feature/M94SourceBoundaryTest.php',
            'tests/Feature/M94StandalonePivotBoundaryTest.php',
            'tests/Fixtures/m94-owned-source-hunks.json',
            'tests/Unit/M94BuilderSelectionPresentationTest.php',
        ];
        self::assertCount(30, array_unique($paths));
        $suffix = "# M94 exact LF release paths; preserve the complete accepted prefix and all historical EOL.\n";
        foreach ($paths as $path) {
            self::assertStringNotContainsString('*', $path);
            self::assertStringNotContainsString('..', $path);
            self::assertStringContainsString('m94', strtolower($path));
            $suffix .= $path." text eol=lf\n";
        }
        self::assertSame($suffix, substr($raw, 81742));
        self::assertStringNotContainsString('RelativeClausesWithWhoseAndWhichTheorySeeder/definition.json', $suffix, 'The existing raw-CRLF definition is not a new LF file.');
    }
}
