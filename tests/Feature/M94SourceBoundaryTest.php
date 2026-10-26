<?php

namespace Tests\Feature;

use App\Support\M94RelativeClausesWhoseWhich as Package;
use PHPUnit\Framework\TestCase;

/** New release guards: historical tests and their strict source failures remain unchanged. */
class M94SourceBoundaryTest extends TestCase
{
    private const FIXTURE = 'tests/Fixtures/m94-owned-source-hunks.json';
    private const DISPATCH = [
        'app/Support/TheoryContentSource.php',
        'app/Support/TheoryContentRenderer.php',
        'app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryHtmlAdapter.php',
        'app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php',
    ];

    private function readArtifactJson(string $path): array
    {
        self::assertFileExists($path); self::assertFalse(is_link($path));
        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    private function contexts(): array
    {
        $testRoot = str_replace('\\', '/', dirname(__DIR__, 2));
        $wt = str_replace('\\', '/', getenv('GRAMLYZE_M94_SOURCE_WORKTREE') ?: (is_dir($testRoot.'/storage/app/m75-worktree') ? $testRoot.'/storage/app/m75-worktree' : $testRoot));
        $root = getenv('GRAMLYZE_M94_SERVED_ROOT') ?: (str_ends_with($wt, '/storage/app/m75-worktree') ? dirname($wt, 3) : $testRoot);
        self::assertNotSame(realpath($root), realpath($wt), 'Both independently captured source contexts are required, not two aliases for one directory.');
        return ['root' => str_replace('\\', '/', $root), 'worktree' => str_replace('\\', '/', $wt)];
    }

    public function test_eight_shared_files_reverse_only_the_twelve_reviewed_registrations_to_each_raw_before(): void
    {
        $fixture = $this->readArtifactJson(dirname(__DIR__, 2).'/'.self::FIXTURE);
        self::assertSame(1, $fixture['schema_version']);
        self::assertSame('8fbc16ab6dd6c6636b93f4f09406addaf77d3cb2', $fixture['base_sha']);
        self::assertSame('5fd7c5c02445d3cdf9488aefa7136c203b2c77b14767c5783484b01f42a0322c', $fixture['source_capture_sha256']);
        self::assertSame(self::DISPATCH, array_column($fixture['files'], 'path')); self::assertCount(8, $fixture['files']);
        foreach ($this->contexts() as $profile => $root) {
            $added = 0;
            foreach ($fixture['files'] as $file) {
                self::assertNotContains('..', explode('/', $file['path']));
                $path = $root.'/'.$file['path']; self::assertFileExists($path); self::assertFalse(is_link($path));
                $raw = file_get_contents($path); $lines = preg_split('/(?<=\n)/', $raw);
                foreach (array_reverse($file['hunks']) as $hunk) {
                    self::assertSame(['anchor', 'added'], array_keys($hunk));
                    self::assertStringContainsString('M94', $hunk['added']); self::assertStringContainsString('M93', $hunk['anchor']);
                    self::assertStringNotContainsString("\n", $hunk['added']); self::assertStringNotContainsString("\r", $hunk['added']);
                    $matches = [];
                    foreach ($lines as $i => $line) { if (rtrim($line, "\r\n") === $hunk['added']) { $matches[] = $i; } }
                    self::assertCount(1, $matches, $profile.':'.$file['path'].':added whole line'); $index = $matches[0];
                    self::assertGreaterThan(0, $index);
                    self::assertSame($hunk['anchor'], rtrim($lines[$index - 1], "\r\n"), 'The registration remains immediately after its exact predecessor.');
                    array_splice($lines, $index, 1); ++$added;
                }
                $restored = implode('', $lines); $expected = $file['before'][$profile];
                self::assertSame($expected['sha256'], hash('sha256', $restored), $profile.':'.$file['path'].' full raw reversal');
                self::assertSame($expected['bytes'], strlen($restored));
                self::assertSame($expected['crlf'], substr_count($restored, "\r\n")); self::assertSame($expected['lf'], substr_count($restored, "\n"));
                self::assertSame($expected['utf8_bom'], str_starts_with($restored, "\xEF\xBB\xBF"));
            }
            self::assertSame(12, $added, 'No new renderer, formatter, validator or course-wide mutation is hidden among registrations.');
        }
    }

    public function test_checksum_manifest_pins_public_fixture_and_the_one_canonical_definition(): void
    {
        $r = Package::load(dirname(__DIR__, 2)); Package::validate($r[0], $r[1], $r[2], $r[3]);
        $checksums = $r[4];
        self::assertSame(self::FIXTURE, $checksums['owned_source_fixture']['path']);
        self::assertSame(hash_file('sha256', dirname(__DIR__, 2).'/'.self::FIXTURE), $checksums['owned_source_fixture']['sha256']);
        self::assertCount(1, $r[1]['targets']); $target = $r[1]['targets'][0];
        self::assertSame('database/seeders/Page_V3/ClausesAndLinkingWords/RelativeClausesWithWhoseAndWhichTheorySeeder/definition.json', $target['path']);
        $actual = $this->readArtifactJson(dirname(__DIR__, 2).'/'.$target['path']);
        self::assertSame($target['after'], $actual);
        foreach (Package::profile()['artifacts'] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha, 'Pending pins never receive runtime authority.');
            self::assertSame($sha, hash_file('sha256', dirname(__DIR__, 2).'/'.$path));
        }
    }

    public function test_public_learning_payloads_contain_no_private_runtime_or_executable_design_fields(): void
    {
        $r = Package::load(dirname(__DIR__, 2)); $strings = [];
        $visit = function (mixed $value, string $pointer = '') use (&$visit, &$strings): void {
            if (is_array($value)) {
                foreach ($value as $key => $child) {
                    if (is_string($key)) {
                        self::assertNotContains(strtolower($key), ['password', 'secret', 'access_token', 'api_key', 'dsn', 'database_password',
                            'raw_sql', 'sql_rows', 'db_rows', 'progress', 'session_id', 'cookie', 'nonce', 'runtime_identity',
                            'created_at', 'updated_at', 'deleted_at', 'view', 'native_view', 'stylesheet', 'css_class', 'style'], $pointer.'/'.$key);
                    }
                    $visit($child, $pointer.'/'.$key);
                }
            } elseif (is_string($value)) {
                $strings[] = $value;
                self::assertDoesNotMatchRegularExpression('~(?:[A-Z]:[\\/]|storage/app/seo-m94-local|\.env(?:\b|/)|<script\b|<iframe\b|<[^>]+\s(?:style|class|on[a-z]+)\s*=)~i', $value, $pointer);
            }
        };
        // Learning lesson data and details, not source configuration metadata such as existing css_class:null.
        foreach ($r[2]['lessons'][0]['blocks'] as $block) { $visit($block['data']); }
        $visit($r[2]['lessons'][0]['details']); self::assertNotSame([], $strings);
        $editorialJson = json_encode($r[2]['editorial_review'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        self::assertStringNotContainsString('storage/app/seo-m94-local', $editorialJson);
        self::assertDoesNotMatchRegularExpression('/"(?:password|access_token|raw_sql|sql_rows|runtime_identity|nonce)"\s*:/i', $editorialJson);
    }

    public function test_no_independent_m94_visual_components_styles_or_asset_scripts_are_introduced(): void
    {
        foreach (['resources/views/m94*', 'resources/views/theory/m94*', 'resources/views/theory/partials/m94*',
            'resources/views/components/m94*', 'resources/css/*m94*', 'public/js/*m94*', 'public/css/*m94*'] as $pattern) {
            self::assertSame([], glob(dirname(__DIR__, 2).'/'.$pattern) ?: [], $pattern);
        }
        $source = file_get_contents(dirname(__DIR__, 2).'/app/Support/M94RelativeClausesWhoseWhich.php');
        self::assertStringNotContainsString('file_put_contents', $source); self::assertStringNotContainsString('DB::', $source);
        self::assertStringContainsString("'body_format' => 'literal-supported-HTML'", $source);
        self::assertStringContainsString("\$caller !== 'standalone_test'", $source);
    }
}
