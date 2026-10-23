<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/** Source lineage only. Never boots Laravel, reads working .env, uses SQL, or mutates source/Git. */
class M79OwnedSharedSourceDeltaTest extends TestCase
{
    private const BASE = 'f33d1a0e2eff08e66044d9b746a46bc33f207bce';
    private const SOURCE_BEFORE_SHA = '0fa53aa56c13487594e39b8460157cbafab5967d57baa5e930e785eb3ed93f8c';
    private const DB_BEFORE_SHA = '8c261b17b3f7e332fc5fc86eef8b555af83de19938aee7ae83d2a23e7dbb3add';
    private const FIXTURE_SHA = '13cd6185dc553b31c6028d3f4b1f9f2becdb37f8fb0434bf6cc8cc19225e3eeb';
    private const VISUAL_ROOTS = ['resources/views', 'resources/css', 'resources/js', 'public/js'];
    private const DISPATCH = [
        'app/Support/TheoryContentSource.php',
        'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php',
        'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php',
        'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php',
        'app/Http/Controllers/TestJsV2Controller.php',
    ];

    private static function root(): string { return str_replace('\\', '/', dirname(__DIR__, 2)); }
    private static function lf(string $raw): string { return str_replace("\r\n", "\n", $raw); }

    private static function read(string $root, string $relative): string
    {
        self::assertMatchesRegularExpression('~^(?:app|docs/content|docs/reports|database/content-patches|database/seeders|resources|public/js|tests|tools|routes)/[A-Za-z0-9_./-]+$~D', $relative);
        self::assertStringNotContainsString('..', $relative);
        $path = $root.'/'.$relative;
        self::assertFileExists($path); self::assertFalse(is_link($path));
        return file_get_contents($path);
    }

    private static function fixture(): array
    {
        $path = self::root().'/tests/Fixtures/m79-owned-source-hunks.json';
        self::assertFileExists($path); self::assertFalse(is_link($path));
        self::assertSame(self::FIXTURE_SHA, hash_file('sha256', $path));
        $ledger = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, $ledger['schema_version']); self::assertSame(self::BASE, $ledger['base_sha']);
        self::assertSame(self::SOURCE_BEFORE_SHA, $ledger['independent_source_before_sha256']);
        self::assertSame(self::DB_BEFORE_SHA, $ledger['independent_db_before_sha256']);
        return $ledger;
    }

    /** Foreign-inclusive served profiles are explicit and separate from the accepted WT. */
    private static function contexts(array $ledger): array
    {
        $roots = [self::root()];
        $served = getenv('GRAMLYZE_M79_SERVED_ROOT');
        if (is_string($served) && $served !== '') {
            self::assertMatchesRegularExpression('~^(?:[A-Za-z]:[/\\\\]|/)~', $served);
            self::assertDirectoryExists($served); self::assertFalse(is_link($served));
            $served = str_replace('\\', '/', rtrim($served, '/\\'));
            if ($served !== self::root()) { $roots[] = $served; }
        }
        $contexts = [];
        foreach ($roots as $root) {
            $raw = self::read($root, self::DISPATCH[7]);
            $profiles = array_keys(array_filter($ledger['files'][7]['profiles'], static fn ($profile) => $profile['after_raw_sha256'] === hash('sha256', $raw)));
            self::assertCount(1, $profiles, 'Actual complete controller bytes must match exactly one known ROOT/WT profile.');
            $contexts[] = ['root' => $root, 'profile' => $profiles[0]];
        }
        return $contexts;
    }

    public function test_twelve_new_registration_lines_reverse_to_exact_independently_captured_predecessors(): void
    {
        $ledger = self::fixture(); self::assertSame(self::DISPATCH, array_column($ledger['files'], 'path'));
        foreach (self::contexts($ledger) as $context) {
            $lines = 0;
            foreach ($ledger['files'] as $file) {
                self::assertSame('finite-package-registration', $file['kind']);
                $pin = $file['profiles'][$context['profile']]; $raw = self::read($context['root'], $file['path']); $lf = self::lf($raw);
                self::assertSame($pin['after_raw_sha256'], hash('sha256', $raw), $file['path'].' complete raw profile');
                self::assertSame($pin['after_lf_sha256'], hash('sha256', $lf));
                self::assertSame($pin['after_lf_line_count'], substr_count($raw, "\n"));
                self::assertSame($pin['after_crlf_line_count'], substr_count($raw, "\r\n"));
                foreach ($file['hunks'] as $index => $hunk) {
                    ++$lines; self::assertSame(1, $hunk['added_lines']); self::assertSame(0, $hunk['removed_lines']);
                    self::assertStringContainsString('M78', $hunk['before']); self::assertStringNotContainsString('M79', $hunk['before']);
                    self::assertStringStartsWith($hunk['before'], $hunk['after']);
                    $added = substr($hunk['after'], strlen($hunk['before']));
                    self::assertSame(1, substr_count($added, "\n")); self::assertStringContainsString('M79', $added);
                    self::assertSame(1, substr_count($lf, $hunk['after']), 'Each owned context is exact and unique.');
                    $lf = str_replace($hunk['after'], $hunk['before'], $lf);
                    $eol = $pin['hunk_added_line_eols'][$index]; self::assertContains($eol, ['LF', 'CRLF']);
                    $rawAdded = $eol === 'CRLF' ? str_replace("\n", "\r\n", $added) : $added;
                    self::assertSame(1, substr_count($raw, "\n".$rawAdded), 'Newline anchor prevents matching a differently indented controller line.');
                    $raw = str_replace("\n".$rawAdded, "\n", $raw);
                }
                self::assertSame($pin['before_raw_sha256'], hash('sha256', $raw), $file['path'].' raw BEFORE without EOL normalization');
                self::assertSame($pin['before_lf_sha256'], hash('sha256', $lf));
                self::assertSame('independent-M79-source-BEFORE', $pin['before_pin_origin']);
                self::assertSame('independent-finite-twelve-hunk-expectation', $pin['after_pin_origin']);
                if ($context['profile'] === 'worktree' || $file['path'] !== self::DISPATCH[7]) {
                    self::assertSame($file['accepted_m78_lf_sha256'], hash('sha256', $lf));
                } else {
                    self::assertNotSame($file['accepted_m78_lf_sha256'], hash('sha256', $lf), 'Foreign served controller behavior remains, not flattened to WT.');
                }
                if ($pin['eol'] === 'mixed-preserved') {
                    self::assertSame('served_root', $context['profile']); self::assertSame(self::DISPATCH[6], $file['path']);
                }
            }
            self::assertSame(12, $lines); self::assertSame($lines, $ledger['owned_added_registration_lines']);
            self::assertSame(0, $ledger['owned_validator_added_lines']); self::assertSame(0, $ledger['owned_validator_removed_lines']);
        }
    }

    public function test_theory_caller_guards_m77_variable_owner_and_m78_retained_identity_guards_stay_nonvisual(): void
    {
        $ledger = self::fixture();
        foreach (self::contexts($ledger) as $context) {
            $read = static fn ($path) => self::read($context['root'], $path);
            $source = $read(self::DISPATCH[0]);
            self::assertSame(1, substr_count($source, "[M79QuestionTypesCompletion::class, 'm42StyleContext']"));
            self::assertStringContainsString('if ($requiredContext !== null && !($context[$requiredContext] ?? false)) { continue; }', $source);
            $controller = $read(self::DISPATCH[7]);
            foreach (['M76PronounRelations', 'M77OnePrepositionsPhrasal', 'M78PhrasalQuestionBasics', 'M79QuestionTypesCompletion'] as $class) {
                self::assertSame(2, substr_count($controller, 'if ($block !== null) { $block = \\App\\Support\\'.$class.'::preserveExcludedTextBlock($block, \'standalone_test\'); }'));
                self::assertSame(2, substr_count($controller, 'if ($textBlock) { $textBlock = \\App\\Support\\'.$class.'::preserveExcludedTextBlock($textBlock, \'standalone_test\'); }'));
            }
            self::assertSame(1, substr_count($read(self::DISPATCH[6]), '$page = \\App\\Support\\M79QuestionTypesCompletion::preserveCoursePage($page);'));
            self::assertSame(1, substr_count($read(self::DISPATCH[3]), '$bindings = M79BuilderSelectionPresentation::bindings($bindings);'));
            self::assertStringContainsString("if ((\$bindings['theoryCanonical'] ?? false) !== true)", $read(self::DISPATCH[3]));
            self::assertSame(1, substr_count($read(self::DISPATCH[4]), '$block = M79BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory);'));
            self::assertSame(1, substr_count($read(self::DISPATCH[5]), '$excludeQuestionIds = \\App\\Support\\M79BuilderSelectionPresentation::exclusions($block, $linkedPractice, $excludeQuestionIds);'));
            $validator = $read('app/Support/TheoryNativeContentRelease.php');
            foreach (['$capturedPractice = null;', '$this->existingPractice($binding, $old[\'before\'])',
                "foreach (['id', 'source_index'] as \$field)", 'array_key_exists($field, $retained) !== array_key_exists($field, $task)',
                "\$this->fail('new native task requires a stable ID.');",
                "\$capturedWithoutId = is_array(\$retained) && !array_key_exists('id', \$retained) && !array_key_exists('id', \$input);",
                "\$pin['uuid'] !== M26DetailPackage::uuid(\$binding['identity'], \$source, \$slot + 1)",
                "\$pin['level'] !== \$binding['practice_level']"] as $guard) {
                self::assertStringContainsString($guard, $validator);
            }
            $generic = $read('app/Support/TheoryBuilderSelectionPresentation.php');
            self::assertStringContainsString('$ownerCount=count($package::OWNERS);', $generic);
            self::assertStringContainsString('$ownerCount<1', $generic); self::assertSame(4, substr_count($generic, '!==$ownerCount'));
        }
    }

    public function test_all_326_visual_source_profiles_and_every_shift_label_binding_are_unchanged(): void
    {
        $ledger = self::fixture(); self::assertCount(326, $ledger['visual_sources']);
        foreach (self::contexts($ledger) as $context) {
            $actual = [];
            foreach (self::VISUAL_ROOTS as $directory) {
                $path = $context['root'].'/'.$directory; self::assertDirectoryExists($path); self::assertFalse(is_link($path));
                foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                    self::assertFalse($file->isLink());
                    if ($file->isFile()) { $actual[] = substr(str_replace('\\', '/', $file->getPathname()), strlen($context['root']) + 1); }
                }
            }
            $expected = array_column($ledger['visual_sources'], 'path'); sort($actual, SORT_STRING); sort($expected, SORT_STRING);
            self::assertSame($expected, $actual, 'No added Blade, CSS, JS, formatter or visual file.');
            foreach ($ledger['visual_sources'] as $file) {
                $raw = self::read($context['root'], $file['path']);
                self::assertSame($file[$context['profile'] === 'worktree' ? 'worktree_before_raw_sha256' : 'served_before_raw_sha256'], hash('sha256', $raw));
                if ($context['profile'] === 'worktree') { self::assertSame($file['accepted_m78_lf_sha256'], hash('sha256', self::lf($raw))); }
            }
            $widget = self::read($context['root'], 'resources/views/components/text-block-practice-questions.blade.php');
            self::assertSame(1, substr_count($widget, '&& items.every((item, index) => item.value === tokens[index])'));
            self::assertSame(1, substr_count($widget, 'items.push(items.shift());'));
            self::assertSame(1, substr_count($widget, 'x-text="Object.prototype.hasOwnProperty.call(currentQuestion?.token_labels ?? {}, token.value) && typeof currentQuestion.token_labels[token.value] === \'string\' ? currentQuestion.token_labels[token.value] : token.value"'));
            self::assertStringContainsString('x-text="token.value"', $widget);
        }
    }

    public function test_historical_sources_raw_byte_tests_and_captured_absences_are_not_rewritten(): void
    {
        $ledger = self::fixture(); $paths = array_column($ledger['immutable_sources'], 'path');
        foreach (['app/Support/TheoryNativeContentRelease.php', 'app/Support/TheoryBuilderSelectionPresentation.php',
            'app/Support/M78PhrasalQuestionBasics.php', 'app/Support/M78BuilderSelectionPresentation.php',
            'tests/Feature/M77OwnedSharedSourceDeltaTest.php', 'tests/Fixtures/m77-owned-source-hunks.json',
            'tests/Feature/M78OwnedSharedSourceDeltaTest.php', 'tests/Fixtures/m78-owned-source-hunks.json'] as $path) {
            self::assertContains($path, $paths);
        }
        self::assertNotContains('app/Support/ComposeTokenCase.php', $paths, 'No claim that foreign served PHP formatter equals the accepted WT.');
        foreach (self::contexts($ledger) as $context) {
            foreach ($ledger['immutable_sources'] as $file) {
                $profile = $file['profiles'][$context['profile']];
                if (!$profile['exists']) {
                    self::assertFileDoesNotExist($context['root'].'/'.$file['path'], 'An absent historical ROOT test is not invented as a BEFORE-equivalent file.');
                    continue;
                }
                $raw = self::read($context['root'], $file['path']);
                self::assertSame($profile['raw_sha256'], hash('sha256', $raw), $file['path']);
                self::assertSame($profile['lf_sha256'], hash('sha256', self::lf($raw)));
                if ($context['profile'] === 'worktree') { self::assertSame($file['accepted_m78_lf_sha256'], hash('sha256', self::lf($raw))); }
            }
        }
    }

    public function test_public_fixture_contains_only_safe_source_paths_hunks_and_opaque_hashes(): void
    {
        $ledger = self::fixture();
        self::assertSame(['schema_version', 'base_sha', 'scope', 'independent_source_before_sha256', 'independent_db_before_sha256',
            'owned_added_registration_lines', 'owned_validator_added_lines', 'owned_validator_removed_lines', 'files', 'visual_sources', 'immutable_sources'], array_keys($ledger));
        $json = json_encode($ledger, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        self::assertDoesNotMatchRegularExpression('~(?:[A-Z]:[/\\\\]|storage/app/|password|DB_PASSWORD|APP_KEY|created_at|updated_at|runtime_identity|sql_before|question_snapshot)~', $json);
        foreach ($ledger['files'] as $file) {
            self::assertSame(['path', 'kind', 'accepted_m78_git_raw_sha256', 'accepted_m78_lf_sha256', 'hunks', 'profiles'], array_keys($file));
            self::assertSame(['worktree', 'served_root'], array_keys($file['profiles']));
            foreach ($file['hunks'] as $hunk) { self::assertSame(['before', 'after', 'added_lines', 'removed_lines'], array_keys($hunk)); }
            foreach ($file['profiles'] as $profile) {
                foreach (['before_raw_sha256', 'before_lf_sha256', 'after_raw_sha256', 'after_lf_sha256'] as $key) {
                    self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $profile[$key]);
                }
            }
        }
    }
}
