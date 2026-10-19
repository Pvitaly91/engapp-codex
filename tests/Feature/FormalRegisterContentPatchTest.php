<?php

namespace Tests\Feature;

use App\Services\FormalRegisterContentPatch;
use App\Services\LinkingWordsContentPatch;
use App\Services\M16LocalTargetGuard;
use App\Support\Database\JsonPageSeeder;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class FormalRegisterContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $databasePath;

    private string $private;

    private array $sources = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->databasePath = database_path();
        $this->private = storage_path('app/m16-fixture-'.bin2hex(random_bytes(8)));
        File::makeDirectory($this->private.'/content-patches', 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($this->private);
        File::copy(database_path('content-patches/m16-formal-register-before.json'), $this->private.'/content-patches/m16-formal-register-before.json');
        foreach (FormalRegisterContentPatch::DEFINITIONS as $index => $relative) {
            File::copyDirectory(dirname(database_path($relative)), dirname($this->private.'/'.$relative));
            $this->sources[FormalRegisterContentPatch::NAMES[$index]] = $this->private.'/'.$relative;
        }
        app()->useDatabasePath($this->private);
    }

    protected function tearDown(): void
    {
        app()->useDatabasePath($this->databasePath);
        parent::tearDown();
    }

    private function seedFixture(): void
    {
        $historical = json_decode(File::get($this->private.'/content-patches/m16-formal-register-before.json'), true, flags: JSON_THROW_ON_ERROR);
        $root = DB::table('page_categories')->insertGetId(['slug' => 'basic-grammar', 'title' => 'Basic Grammar', 'language' => 'uk', 'type' => 'theory']);
        DB::table('page_categories')->insert(['slug' => 'word-order', 'title' => 'Word Order', 'language' => 'uk', 'type' => 'theory', 'parent_id' => $root]);
        foreach ($this->sources as $name => $path) {
            $bytes = File::get($path);
            File::put($path, json_encode($historical['definitions'][$name], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            try {
                (new class($path) extends JsonPageSeeder
                {
                    public function __construct(private string $source) {}

                    protected function definitionPath(): string
                    {
                        return $this->source;
                    }
                })->run();
            } finally {
                File::put($path, $bytes);
            }
        }
        $row = (array) DB::table('text_blocks')->where('locale', 'uk')->where('sort_order', 2)->first();
        unset($row['id']);
        foreach (['en', 'pl'] as $locale) {
            DB::table('text_blocks')->insert(array_replace($row, ['uuid' => 'm16-'.$locale, 'locale' => $locale, 'body' => 'Preserved translation']));
        }
        // A separate lesson and a related bank must remain untouched.
        DB::table('pages')->insert(['slug' => 'm11-preserved', 'title' => 'M11 preserved', 'text' => 'Verified M11 content', 'seeder' => LinkingWordsContentPatch::PREFIX.LinkingWordsContentPatch::NAMES[0]]);
        $id = DB::table('questions')->insertGetId(['uuid' => 'm16-reference', 'question' => 'Only then {a1}', 'theory_text_block_uuid' => $row['uuid']]);
        $option = DB::table('question_options')->insertGetId(['option' => 'did we understand']);
        DB::table('question_answers')->insert(['question_id' => $id, 'option_id' => $option, 'marker' => 'a1']);
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => 'm16-reference', 'text_block_uuid' => $row['uuid']]);
    }

    private function service(): FormalRegisterContentPatch
    {
        return new FormalRegisterContentPatch(DB::connection(), database_path(), $this->private);
    }

    private function path(string $name): string
    {
        return $this->private.'/'.$name.'.json';
    }

    private function snapshot(): array
    {
        $out = [];
        foreach (['pages', 'page_categories', 'text_blocks', 'page_tag', 'tag_text_block', 'tags', 'page_category_tag', 'questions',
            'question_answers', 'question_options', 'question_option_question', 'question_theory_text_blocks', 'question_tag', 'question_marker_tag',
            'verb_hints', 'question_hints', 'question_variants', 'saved_grammar_tests', 'saved_grammar_test_questions'] as $table) {
            $rows = DB::table($table)->get()->map(fn ($r) => (array) $r)->all();
            usort($rows, fn ($a, $b) => strcmp(FormalRegisterContentPatch::digest($a), FormalRegisterContentPatch::digest($b)));
            $out[$table] = $rows;
        }

        return $out;
    }

    public function test_exact_three_lesson_patch_backup_noop_restore_preserves_all_unapproved_data(): void
    {
        $this->seedFixture();
        $patch = $this->service();
        $before = $this->snapshot();
        $plan = $patch->savePlan($this->path('plan'));
        self::assertSame($before, $this->snapshot());
        self::assertSame(FormalRegisterContentPatch::NAMES, array_keys($plan['pages']));
        self::assertSame(['formal-english'], array_column($plan['pages'][FormalRegisterContentPatch::NAMES[0]]['category_ancestry'], 'slug'));
        self::assertSame(['formal-english'], array_column($plan['pages'][FormalRegisterContentPatch::NAMES[1]]['category_ancestry'], 'slug'));
        self::assertNotEmpty($plan['changes']);
        self::assertSame(count($plan['changes']), $patch->apply($this->path('plan'), $this->path('backup'))['updated']);
        self::assertSame($plan, json_decode(File::get($this->path('backup')), true));
        $after = $this->snapshot();
        foreach ($before as $table => $rows) {
            if (! in_array($table, ['pages', 'text_blocks'], true)) {
                self::assertSame($rows, $after[$table], $table);

                continue;
            }
            $newById = array_column($after[$table], null, 'id');
            foreach ($rows as $old) {
                $changes = array_values(array_filter($plan['changes'], fn ($c) => $c['table'] === $table && $c['id'] === $old['id']));
                self::assertSame($changes ? array_replace($old, $changes[0]['after']) : $old, $newById[$old['id']]);
            }
        }
        self::assertSame(['status' => 'no-op', 'updated' => 0], $patch->apply($this->path('plan'), $this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
        self::assertSame([], $patch->plan()['changes']);
        self::assertSame(count($plan['changes']), $patch->restore($this->path('backup'))['updated']);
        self::assertSame($before, $this->snapshot());
        self::assertSame(0, $patch->restore($this->path('backup'))['updated']);
    }

    public static function conflicts(): array
    {
        return array_map(fn ($kind) => [$kind], ['page-text', 'block-body', 'uuid', 'order', 'owner', 'extra-block', 'parent-missing', 'parent-cycle', 'parent-slug', 'parent-locale']);
    }

    #[DataProvider('conflicts')]
    public function test_manual_identity_and_ancestry_conflicts_never_write(string $kind): void
    {
        $this->seedFixture();
        $block = DB::table('text_blocks')->where('seeder', FormalRegisterContentPatch::NAMES[0])->where('locale', 'uk')->where('sort_order', 2);
        if ($kind === 'page-text') {
            DB::table('pages')->where('seeder', FormalRegisterContentPatch::NAMES[0])->update(['text' => 'Manual']);
        } elseif ($kind === 'extra-block') {
            $row = (array) $block->first();
            unset($row['id']);
            $row['uuid'] = 'manual-extra';
            DB::table('text_blocks')->insert($row);
        } elseif (str_starts_with($kind, 'parent-')) {
            $root = DB::table('page_categories')->where('slug', 'formal-english');
            if ($kind === 'parent-missing') {
                $root->update(['parent_id' => DB::table('page_categories')->where('slug', 'basic-grammar')->value('id')]);
            }
            if ($kind === 'parent-cycle') {
                $root->update(['parent_id' => DB::table('page_categories')->where('slug', 'word-order')->value('id')]);
            }
            if ($kind === 'parent-slug') {
                $root->update(['slug' => 'other-root']);
            }
            if ($kind === 'parent-locale') {
                $root->update(['language' => 'en']);
            }
        } else {
            $block->update(match ($kind) {
                'block-body' => ['body' => 'Manual'], 'uuid' => ['uuid' => 'foreign'], 'order' => ['sort_order' => 12], 'owner' => ['seeder' => 'Foreign']
            });
        }
        $before = $this->snapshot();
        try {
            $this->service()->savePlan($this->path('plan'));
            self::fail('Expected conflict '.$kind);
        } catch (RuntimeException) {
        }
        self::assertSame($before, $this->snapshot());
        self::assertFileDoesNotExist($this->path('plan'));
        if (! str_starts_with($kind, 'parent-')) {
            self::assertCount(1, $this->service()->plan([FormalRegisterContentPatch::NAMES[1]])['pages']);
        }
    }

    public function test_stale_sources_ancestry_locales_relations_and_partial_states_refuse_before_backup(): void
    {
        $this->seedFixture();
        $patch = $this->service();
        $plan = $patch->savePlan($this->path('plan'));
        $source = reset($this->sources);
        $bytes = File::get($source);
        File::append($source, "\n");
        try {
            $patch->apply($this->path('plan'), $this->path('backup'));
            self::fail('Changed source');
        } catch (RuntimeException) {
        }
        File::put($source, $bytes);
        foreach (['ancestor', 'locale', 'pivot'] as $kind) {
            $patch->savePlan($this->path($kind));
            if ($kind === 'ancestor') {
                DB::table('page_categories')->where('slug', 'formal-english')->update(['title' => 'Concurrent ancestor edit']);
            }
            if ($kind === 'locale') {
                DB::table('text_blocks')->where('locale', 'en')->update(['body' => 'Concurrent translation']);
            }
            if ($kind === 'pivot') {
                DB::table('question_theory_text_blocks')->update(['position' => 11]);
            }
            $before = $this->snapshot();
            try {
                $patch->apply($this->path($kind), $this->path('backup'));
                self::fail('Changed '.$kind);
            } catch (RuntimeException $e) {
                self::assertStringContainsString('stale', $e->getMessage());
            }
            self::assertSame($before, $this->snapshot());
            self::assertFileDoesNotExist($this->path('backup'));
        }
        $change = $plan['changes'][0];
        DB::table($change['table'])->where('id', $change['id'])->update($change['after']);
        $before = $this->snapshot();
        try {
            $patch->plan();
            self::fail('Partial lesson');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('Partially edited', $e->getMessage());
        }
        self::assertSame($before, $this->snapshot());
    }

    public function test_exclusive_backup_corrupt_preview_failure_rollback_and_later_edit_restore_safety(): void
    {
        $this->seedFixture();
        $patch = $this->service();
        $plan = $patch->savePlan($this->path('plan'));
        $before = $this->snapshot();
        File::put($this->path('existing'), 'evidence');
        try {
            $patch->apply($this->path('plan'), $this->path('existing'));
            self::fail('Existing backup');
        } catch (RuntimeException) {
        }
        self::assertSame('evidence', File::get($this->path('existing')));
        $bad = $plan;
        array_pop($bad['changes']);
        unset($bad['sha256']);
        $bad['sha256'] = FormalRegisterContentPatch::digest($bad);
        File::put($this->path('bad'), json_encode($bad));
        try {
            $patch->apply($this->path('bad'), $this->path('backup'));
            self::fail('Truncated plan');
        } catch (RuntimeException) {
        }
        self::assertFileDoesNotExist($this->path('backup'));
        self::assertSame($before, $this->snapshot());
        $failing = new class(DB::connection(), database_path(), $this->private) extends FormalRegisterContentPatch
        {
            protected function afterUpdate(int $index): void
            {
                if ($index === 0) {
                    throw new RuntimeException('Simulated failure');
                }
            }
        };
        try {
            $failing->apply($this->path('plan'), $this->path('failed-backup'));
            self::fail('Rollback required');
        } catch (RuntimeException) {
        }
        self::assertSame($before, $this->snapshot());
        self::assertFileExists($this->path('failed-backup'));
        $patch->apply($this->path('plan'), $this->path('backup'));
        $after = $this->snapshot();
        try {
            $failing->restore($this->path('backup'));
            self::fail('Restore rollback required');
        } catch (RuntimeException) {
        }
        self::assertSame($after, $this->snapshot());
        DB::table('text_blocks')->where('locale', 'pl')->update(['heading' => 'Later edit']);
        $later = $this->snapshot();
        try {
            $patch->restore($this->path('backup'));
            self::fail('Restore must protect later edits');
        } catch (RuntimeException) {
        }
        self::assertSame($later, $this->snapshot());
    }

    public function test_separate_command_full_identity_allowlist_and_m11_format_remain_scoped(): void
    {
        $this->seedFixture();
        $before = $this->snapshot();
        $this->artisan('content:patch-formal-register-m16', ['--plan' => 'preview.json', '--only' => [FormalRegisterContentPatch::NAMES[1]]])->assertExitCode(0);
        $plan = json_decode(File::get(storage_path('app/seo-m16-local/preview.json')), true);
        self::assertSame('m16-formal-register-v1', $plan['patch']);
        self::assertCount(1, $plan['pages']);
        $this->artisan('content:patch-formal-register-m16', ['--apply' => true, '--plan' => 'preview.json', '--backup' => 'backup.json'])->assertExitCode(0);
        $this->artisan('content:patch-formal-register-m16', ['--restore' => true, '--backup' => 'backup.json'])->assertExitCode(0);
        self::assertSame($before, $this->snapshot());
        foreach (['CleftSentencesBasicsTheorySeeder', LinkingWordsContentPatch::NAMES[0], 'Foreign'] as $name) {
            $this->artisan('content:patch-formal-register-m16', ['--plan' => 'bad.json', '--only' => [$name]])->assertExitCode(1);
        }
        $this->artisan('content:patch-linking-words-m11', ['--plan' => 'bad.json', '--only' => [FormalRegisterContentPatch::NAMES[0]]])->assertExitCode(1);
        $this->artisan('content:patch-formal-register-m16', ['--plan' => '../bad.json'])->assertExitCode(1);
        $this->artisan('content:patch-formal-register-m16', ['--restore' => true, '--backup' => 'backup.json', '--only' => [FormalRegisterContentPatch::NAMES[0]]])->assertExitCode(1);
        self::assertSame($before, $this->snapshot());
    }

    public function test_m16_source_identity_tags_levels_and_foreign_package_plans_are_not_writable(): void
    {
        $this->seedFixture();
        $patch = $this->service();
        $before = $this->snapshot();
        $plan = $patch->savePlan($this->path('plan'));
        $sourcePath = reset($this->sources);
        $bytes = File::get($sourcePath);
        foreach (['page.title' => 'Retitled', 'slug' => 'other-address', 'page.category.slug' => 'other-category',
            'page.blocks.0.level' => 'B1', 'page.tags' => ['Replacement']] as $key => $value) {
            $source = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            data_set($source, $key, $value);
            File::put($sourcePath, json_encode($source, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            try {
                $patch->plan();
                self::fail('Disallowed source mutation '.$key);
            } catch (RuntimeException $e) {
                self::assertStringContainsString('only subtitle, heading and body', $e->getMessage());
            } finally {
                File::put($sourcePath, $bytes);
            }
        }
        $foreign = $plan;
        $foreign['patch'] = 'm11-linking-words-v1';
        unset($foreign['sha256']);
        $foreign['sha256'] = FormalRegisterContentPatch::digest($foreign);
        File::put($this->path('foreign'), json_encode($foreign, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        try {
            $patch->apply($this->path('foreign'), $this->path('backup'));
            self::fail('Foreign package plan');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('Corrupt/incomplete M16', $e->getMessage());
        }
        self::assertFileDoesNotExist($this->path('backup'));
        self::assertSame($before, $this->snapshot());
    }

    public function test_production_default_refusal_and_verified_optin_before_backup(): void
    {
        $this->seedFixture();
        $before = $this->snapshot();
        $patch = $this->service();
        $patch->savePlan($this->path('plan'));
        $verifier = new class extends M16LocalTargetGuard
        {
            public bool $confirmed = false;

            public int $calls = 0;

            public function verify(Connection $db, string $target, string $directory, ?string $proof, array $physical): void
            {
                $this->calls++;
                if (! $this->confirmed || $target !== 'gramlyze.loc' || $proof !== 'fixture-proof.json') {
                    throw new RuntimeException('Unconfirmed M16 target.');
                }
                $this->assertEvidence(M11LocalTargetGuardTest::evidence(), 'd:/dev/htdocs/gramlyze.loc', 200, 3306);
            }
        };
        app()->instance(M16LocalTargetGuard::class, $verifier);
        app()->detectEnvironment(fn () => 'production');
        try {
            try {
                $patch->apply($this->path('plan'), $this->path('backup'));
                self::fail('Production default');
            } catch (RuntimeException $e) {
                self::assertStringContainsString('production writes', $e->getMessage());
            }
            $optin = new FormalRegisterContentPatch(DB::connection(), database_path(), $this->private, 'gramlyze.loc', 'fixture-proof.json');
            try {
                $optin->apply($this->path('plan'), $this->path('backup'));
                self::fail('Unconfirmed target');
            } catch (RuntimeException) {
            }
            self::assertFileDoesNotExist($this->path('backup'));
            self::assertSame($before, $this->snapshot());
            $verifier->confirmed = true;
            $optin->savePlan($this->path('confirmed'));
            self::assertGreaterThan(0, $optin->apply($this->path('confirmed'), $this->path('backup'))['updated']);
            self::assertSame(0, $optin->apply($this->path('confirmed'), $this->path('unused'))['updated']);
            self::assertGreaterThan(0, $optin->restore($this->path('backup'))['updated']);
            self::assertGreaterThan(3, $verifier->calls);
            self::assertSame($before, $this->snapshot());
        } finally {
            app()->detectEnvironment(fn () => 'testing');
        }
    }
}
