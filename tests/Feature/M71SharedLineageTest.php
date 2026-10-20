<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/** Pure public-source evidence; no Laravel boot, environment, SQL, network or Git at test runtime. */
class M71SharedLineageTest extends TestCase
{
    private const BASE = '1291b576b47dc28e5ba9e0c58faa052f6e49460e';
    private const FIXTURE_SHA = '8e254e289066935d17d6e80b562cda79d8321530c9354c32d14290ffa8f491b4';
    private const VISUAL_ROOTS = ['resources/views', 'resources/css', 'resources/js', 'public/js'];

    private static function root(): string
    {
        return str_replace('\\', '/', dirname(__DIR__, 2));
    }

    private static function fixture(): array
    {
        $path = self::root().'/tests/Fixtures/m71-owned-source-hunks.json';
        self::assertFalse(is_link($path));
        self::assertSame(self::FIXTURE_SHA, hash_file('sha256', $path));
        $fixture = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, $fixture['schema_version']); self::assertSame(self::BASE, $fixture['base_sha']);
        return $fixture;
    }

    private static function read(string $relative): string
    {
        self::assertMatchesRegularExpression('~^(?:app|docs/content|database/(?:content-patches|seeders)|resources|public/js)/[A-Za-z0-9_./-]+$~D', $relative);
        self::assertStringNotContainsString('..', $relative);
        $path = self::root().'/'.$relative;
        self::assertFileExists($path); self::assertFalse(is_link($path));
        return file_get_contents($path);
    }

    private static function lf(string $raw): string
    {
        return str_replace("\r\n", "\n", $raw);
    }

    public function test_reversing_only_twelve_reviewed_dispatch_hunks_recovers_the_independent_accepted_base_in_eight_files(): void
    {
        $fixture = self::fixture(); self::assertCount(8, $fixture['files']);
        $paths = []; $hunks = 0; $addedLines = 0;
        foreach ($fixture['files'] as $file) {
            $paths[] = $file['path']; $raw = self::read($file['path']); $copy = self::lf($raw);
            self::assertNotEmpty($file['hunks']);
            foreach ($file['hunks'] as $hunk) {
                ++$hunks;
                self::assertSame(1, substr_count($copy, $hunk['after']), $file['path'].' exact unique owned hunk');
                self::assertStringContainsString('M70', $hunk['before'], 'The existing M70 callback remains in place.');
                self::assertStringContainsString('M71', $hunk['after']);
                self::assertStringNotContainsString('M71', $hunk['before']);
                $delta = substr_count($hunk['after'], "\n") - substr_count($hunk['before'], "\n");
                self::assertContains($delta, [1, 2]); $addedLines += $delta;
                $copy = str_replace($hunk['after'], $hunk['before'], $copy);
            }
            self::assertSame($file['base_lf_sha256'], hash('sha256', $copy), $file['path'].' full accepted-base code after only the owned reversal');
            self::assertSame($raw, self::read($file['path']), 'No source writes or runtime substitutions.');
        }
        self::assertCount(8, array_unique($paths)); self::assertSame(12, $hunks);
        self::assertSame(13, $addedLines); self::assertSame($addedLines, $fixture['owned_added_lines']);
        $controller = self::read('app/Http/Controllers/TestJsV2Controller.php');
        self::assertSame('8469f1e9407c193f00e77c9a2a52fc29ac602abaa18822325b45dfd99875f373', $fixture['controller_raw_sha256']);
        self::assertSame($fixture['controller_raw_sha256'], hash('sha256', $controller));
        self::assertGreaterThan(0, substr_count($controller, "\r\n"));
        self::assertSame(substr_count($controller, "\n"), substr_count($controller, "\r\n"), 'Preserve the controller raw CRLF profile.');
    }

    public function test_all_four_accepted_generic_primitives_remain_the_exact_base_code_without_m71_changes(): void
    {
        $fixture = self::fixture(); self::assertCount(4, $fixture['immutable_primitives']);
        self::assertSame([
            'app/Support/TheoryNativeContentRelease.php', 'app/Services/TheoryNativeContentPatch.php',
            'app/Services/TheoryLocalReleaseGuard.php', 'app/Support/TheoryBuilderSelectionPresentation.php',
        ], array_column($fixture['immutable_primitives'], 'path'));
        foreach ($fixture['immutable_primitives'] as $file) {
            $raw = self::read($file['path']);
            self::assertSame($file['base_lf_sha256'], hash('sha256', self::lf($raw)), $file['path']);
        }
    }

    public function test_all_twelve_m70_frozen_sources_and_active_definitions_keep_their_independent_raw_blob_bytes(): void
    {
        $fixture = self::fixture(); self::assertCount(12, $fixture['protected_m70_sources']);
        $paths = array_column($fixture['protected_m70_sources'], 'path'); self::assertCount(12, array_unique($paths));
        foreach ($fixture['protected_m70_sources'] as $file) {
            self::assertStringNotContainsString('m71', $file['path']);
            $raw = self::read($file['path']);
            self::assertSame($file['bytes'], strlen($raw), $file['path']);
            self::assertSame($file['base_blob_sha256'], hash('sha256', $raw), $file['path'].' raw UTF-8 accepted blob, without EOL normalization');
        }
        $manifest = json_decode(self::read('docs/content/m70-source-checksums.v1.json'), true, flags: JSON_THROW_ON_ERROR);
        $declared = [...array_column($manifest['artifacts'], 'path'), 'docs/content/m70-source-checksums.v1.json', ...array_column($manifest['active_definitions'], 'path')];
        self::assertSame($declared, $paths, 'The whole M70 manifest and all three active definitions are protected.');
        $master = json_decode(self::read('docs/content/m70-intensifiers-noun-basics.v1.0.0.json'), true, flags: JSON_THROW_ON_ERROR);
        $tasks = 0; $demos = 0;
        foreach ($master['lessons'] as $lesson) {
            foreach ($lesson['blocks'] as $block) {
                if ($block['type'] !== 'practice-set') { continue; }
                foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
                    foreach ($block['data'][$group] ?? [] as $index => $task) {
                        if ($group === 'rephrase' && $index === 0 && !empty($task['example_original'])) { ++$demos; }
                        else { ++$tasks; }
                    }
                }
            }
        }
        self::assertSame(34, $tasks); self::assertSame(2, $demos);
    }

    public function test_no_visual_sources_are_added_removed_or_changed_from_the_public_accepted_base_code(): void
    {
        $fixture = self::fixture(); self::assertCount(326, $fixture['visual_sources']);
        $expected = array_column($fixture['visual_sources'], 'path'); $actual = [];
        foreach (self::VISUAL_ROOTS as $relative) {
            $directory = self::root().'/'.$relative;
            self::assertDirectoryExists($directory); self::assertFalse(is_link($directory));
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                self::assertFalse($file->isLink());
                if (!$file->isFile()) { continue; }
                $path = str_replace('\\', '/', $file->getPathname());
                $actual[] = substr($path, strlen(self::root()) + 1);
            }
        }
        sort($actual, SORT_STRING); $sortedExpected = $expected; sort($sortedExpected, SORT_STRING);
        self::assertSame($sortedExpected, $actual, 'No new package view, stylesheet, font hook or frontend file is hidden outside the fixture.');
        foreach ($fixture['visual_sources'] as $file) {
            self::assertTrue(array_any(self::VISUAL_ROOTS, static fn ($prefix) => str_starts_with($file['path'], $prefix.'/')));
            self::assertSame($file['base_lf_sha256'], hash('sha256', self::lf(self::read($file['path']))), $file['path'].' accepted public visual source code');
        }
    }
}
