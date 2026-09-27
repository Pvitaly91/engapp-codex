<?php

namespace Tests\Feature;

use App\Services\LinkingWordsContentPatch;
use App\Services\M11LocalTargetGuard;
use App\Support\Database\JsonPageSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class LinkingWordsContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $originalDatabasePath;
    private string $private;
    private array $sources = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->originalDatabasePath = database_path();
        $this->private = storage_path('app/m11-fixture-'.bin2hex(random_bytes(8)));
        File::makeDirectory($this->private, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($this->private);
        File::makeDirectory($this->private.'/content-patches');
        File::copy(database_path('content-patches/m11-linking-words-before.json'), $this->private.'/content-patches/m11-linking-words-before.json');
        foreach (LinkingWordsContentPatch::NAMES as $name) {
            $relative = 'seeders/Page_V3/ClausesAndLinkingWords/'.$name;
            File::copyDirectory(database_path($relative), $this->private.'/'.$relative);
            $this->sources[$name] = $this->private.'/'.$relative.'/definition.json';
        }
        app()->useDatabasePath($this->private);
        config(['coming-soon.enabled' => false]);
    }

    protected function tearDown(): void
    {
        app()->useDatabasePath($this->originalDatabasePath);
        parent::tearDown();
    }

    private function seedFixture(bool $before = true): void
    {
        $historical = json_decode(File::get($this->private.'/content-patches/m11-linking-words-before.json'), true, flags: JSON_THROW_ON_ERROR);
        foreach ($this->sources as $name => $path) {
            $bytes = File::get($path);
            if ($before) { File::put($path, json_encode($historical['definitions'][$name], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)); }
            try {
                (new class($path) extends JsonPageSeeder {
                    public function __construct(private string $source) {}
                    protected function definitionPath(): string { return $this->source; }
                })->run();
            } finally { File::put($path, $bytes); }
        }
        if ($before) {
            // The package has no EN/PL source files; existing manual translations
            // still belong to the user and must survive an in-place UK patch.
            $row = (array) DB::table('text_blocks')->where('locale', 'uk')->where('sort_order', 2)->first();
            unset($row['id']);
            foreach (['en', 'pl'] as $locale) {
                DB::table('text_blocks')->insert(array_replace($row, ['uuid' => 'm11-'.$locale.'-fixture', 'locale' => $locale, 'body' => '<p>Preserved '.$locale.'</p>']));
            }
        }
    }

    private function service(): LinkingWordsContentPatch { return new LinkingWordsContentPatch(DB::connection(), database_path(), $this->private); }
    private function path(string $name): string { return $this->private.'/'.$name.'.json'; }

    private function snapshot(): array
    {
        $out = [];
        foreach (['pages', 'page_categories', 'text_blocks', 'page_tag', 'tag_text_block', 'tags', 'page_category_tag', 'questions',
            'question_answers', 'question_options', 'question_option_question', 'question_theory_text_blocks', 'question_tag', 'question_marker_tag',
            'verb_hints', 'question_hints', 'question_variants', 'saved_grammar_tests', 'saved_grammar_test_questions'] as $table) {
            $rows = DB::table($table)->get()->map(fn ($r) => (array) $r)->all();
            usort($rows, fn ($a, $b) => strcmp(LinkingWordsContentPatch::digest($a), LinkingWordsContentPatch::digest($b)));
            $out[$table] = $rows;
        }
        return $out;
    }

    private function addQuestionReferences(): void
    {
        $uuid = DB::table('text_blocks')->where('locale', 'uk')->where('sort_order', 2)->value('uuid');
        $id = DB::table('questions')->insertGetId(['uuid' => 'm11-reference', 'question' => 'Because of {a1}', 'theory_text_block_uuid' => $uuid]);
        $option = DB::table('question_options')->insertGetId(['option' => 'the delay']);
        DB::table('question_answers')->insert(['question_id' => $id, 'option_id' => $option, 'marker' => 'a1']);
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => 'm11-reference', 'text_block_uuid' => $uuid]);
        $test = DB::table('saved_grammar_tests')->insertGetId(['uuid' => 'm11-test', 'name' => 'Fixture test', 'slug' => 'm11-test']);
        DB::table('saved_grammar_test_questions')->insert(['saved_grammar_test_id' => $test, 'question_uuid' => 'm11-reference']);
    }

    public function test_exact_patch_noop_restore_and_fresh_fixture_match_preserve_every_identity_and_relation(): void
    {
        $this->seedFixture(); $this->addQuestionReferences();
        $before = $this->snapshot();
        $patch = $this->service(); $plan = $patch->savePlan($this->path('plan'));
        self::assertSame($before, $this->snapshot(), 'Preview is read-only.');
        self::assertCount(12, $plan['changes']);
        self::assertSame(12, $patch->apply($this->path('plan'), $this->path('backup'))['updated']);
        $after = $this->snapshot();
        self::assertSame(0, $patch->apply($this->path('plan'), $this->path('unused'))['updated']);
        self::assertFileDoesNotExist($this->path('unused'));
        foreach ($before as $table => $rows) {
            if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $after[$table], $table); continue; }
            $newById = array_column($after[$table], null, 'id');
            foreach ($rows as $old) {
                $changes = array_values(array_filter($plan['changes'], fn ($c) => $c['table'] === $table && $c['id'] === $old['id']));
                self::assertSame($changes ? array_replace($old, $changes[0]['after']) : $old, $newById[$old['id']], 'Only permitted fields may change.');
            }
        }
        self::assertGreaterThan(0, DB::table('text_blocks')->where('locale', 'en')->count());
        self::assertGreaterThan(0, DB::table('text_blocks')->where('locale', 'pl')->count());
        self::assertSame(12, $patch->restore($this->path('backup'))['updated']);
        self::assertSame($before, $this->snapshot());
        self::assertSame(0, $patch->restore($this->path('backup'))['updated']);
        $this->seedFixture(false);
        $fields = array_flip(['uuid', 'seeder', 'locale', 'sort_order', 'type', 'column', 'level', 'heading', 'body']);
        $content = fn ($rows) => collect($rows)->filter(fn ($r) => $r['locale'] === 'uk')->mapWithKeys(fn ($r) => [$r['uuid'] => array_intersect_key($r, $fields)])->sortKeys()->all();
        self::assertSame($content($after['text_blocks']), $content($this->snapshot()['text_blocks']));
        $texts = fn ($rows) => collect($rows)->mapWithKeys(fn ($r) => [$r['seeder'] => $r['text']])->sortKeys()->all();
        self::assertSame($texts($after['pages']), $texts($this->snapshot()['pages']));
    }

    #[DataProvider('conflicts')]
    public function test_manual_or_identity_conflicts_are_preserved(string $kind): void
    {
        $this->seedFixture();
        if ($kind === 'page-text') { DB::table('pages')->limit(1)->update(['text' => 'Manual subtitle']); }
        elseif ($kind === 'extra-block') {
            $row = (array) DB::table('text_blocks')->where('locale', 'uk')->first(); unset($row['id']); $row['uuid'] = 'extra-manual-block';
            DB::table('text_blocks')->insert($row);
        } else {
            $change = match ($kind) {
                'body' => ['body' => '<p>Manual</p>'], 'heading' => ['heading' => 'Manual'], 'uuid' => ['uuid' => 'foreign'],
                'locale' => ['locale' => 'de'], 'owner' => ['seeder' => 'Manual'], 'order' => ['sort_order' => 33], 'type' => ['type' => 'table'],
            };
            DB::table('text_blocks')->where('locale', 'uk')->where('sort_order', 2)->limit(1)->update($change);
        }
        $before = $this->snapshot();
        try { $this->service()->savePlan($this->path('plan')); self::fail('Refuse '.$kind); } catch (RuntimeException) {}
        self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->path('plan'));
    }

    public static function conflicts(): array { return array_map(fn ($v) => [$v], ['page-text', 'extra-block', 'body', 'heading', 'uuid', 'locale', 'owner', 'order', 'type']); }

    public function test_preview_staleness_for_source_locales_and_question_links_is_detected(): void
    {
        $this->seedFixture(); $this->addQuestionReferences(); $patch = $this->service();
        $patch->savePlan($this->path('plan'));
        $source = reset($this->sources); $bytes = File::get($source); File::append($source, "\n");
        try { $patch->apply($this->path('plan'), $this->path('backup')); self::fail('Changed source'); }
        catch (RuntimeException $e) { self::assertStringContainsString('stale', $e->getMessage()); }
        File::put($source, $bytes);
        DB::table('text_blocks')->where('locale', 'en')->update(['heading' => 'Concurrent edit']);
        $before = $this->snapshot();
        try { $patch->apply($this->path('plan'), $this->path('backup')); self::fail('Changed EN'); }
        catch (RuntimeException $e) { self::assertStringContainsString('stale', $e->getMessage()); }
        self::assertSame($before, $this->snapshot());
        $patch->savePlan($this->path('plan2'));
        DB::table('question_theory_text_blocks')->update(['position' => 12]);
        $before = $this->snapshot();
        try { $patch->apply($this->path('plan2'), $this->path('backup')); self::fail('Changed pivot'); }
        catch (RuntimeException $e) { self::assertStringContainsString('stale', $e->getMessage()); }
        self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_exclusive_backup_and_transaction_failure_and_corrupt_plans_are_safe(): void
    {
        $this->seedFixture(); $patch = $this->service(); $plan = $patch->savePlan($this->path('plan')); $before = $this->snapshot();
        File::put($this->path('existing'), 'evidence');
        try { $patch->apply($this->path('plan'), $this->path('existing')); self::fail('Existing backup'); } catch (RuntimeException) {}
        self::assertSame('evidence', File::get($this->path('existing'))); self::assertSame($before, $this->snapshot());
        $bad = $plan; array_pop($bad['changes']); unset($bad['sha256']); $bad['sha256'] = LinkingWordsContentPatch::digest($bad);
        File::put($this->path('bad'), json_encode($bad));
        try { $patch->apply($this->path('bad'), $this->path('backup')); self::fail('Truncated rehashed plan'); } catch (RuntimeException) {}
        self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->path('backup'));
        $failing = new class(DB::connection(), database_path(), $this->private) extends LinkingWordsContentPatch {
            protected function afterUpdate(int $index): void { if ($index === 0) { throw new RuntimeException('Simulated failure'); } }
        };
        try { $failing->apply($this->path('plan'), $this->path('failed-backup')); self::fail('Must roll back'); } catch (RuntimeException) {}
        self::assertSame($before, $this->snapshot()); self::assertFileExists($this->path('failed-backup'));
        $patch->apply($this->path('plan'), $this->path('backup')); $after = $this->snapshot();
        try { $patch->restore($this->path('bad')); self::fail('Invalid restore'); } catch (RuntimeException) {}
        self::assertSame($after, $this->snapshot());
        try { $failing->restore($this->path('backup')); self::fail('Restore must roll back'); } catch (RuntimeException) {}
        self::assertSame($after, $this->snapshot());
        DB::table('text_blocks')->where('locale', 'pl')->update(['heading' => 'Later edit']); $later = $this->snapshot();
        try { $patch->restore($this->path('backup')); self::fail('Later locale edit'); } catch (RuntimeException) {}
        self::assertSame($later, $this->snapshot());
    }

    public function test_production_environment_and_remote_or_mismatched_connections_refuse_writes(): void
    {
        $this->seedFixture(); $patch = $this->service(); $patch->savePlan($this->path('plan')); $before = $this->snapshot();
        app()->detectEnvironment(fn () => 'production');
        try {
            foreach (['apply', 'restore'] as $action) {
                try {
                    $action === 'apply' ? $patch->apply($this->path('plan'), $this->path('backup')) : $patch->restore($this->path('plan'));
                    self::fail('Refuse production '.$action);
                } catch (RuntimeException $e) { self::assertStringContainsString('production writes', $e->getMessage()); }
            }
            $this->artisan('content:patch-linking-words-m11', ['--apply' => true, '--plan' => 'plan.json', '--backup' => 'backup.json'])->assertExitCode(1);
        } finally { app()->detectEnvironment(fn () => 'testing'); }
        self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->path('backup'));
        foreach (['192.0.2.1', 'localhost'] as $host) {
            $db = new \Illuminate\Database\MySqlConnection(new \PDO('sqlite::memory:'), 'target', '', ['host' => $host]);
            try { (new LinkingWordsContentPatch($db, database_path(), $this->private))->connection('target', true); self::fail('Invalid actual connection'); }
            catch (RuntimeException $e) { self::assertStringContainsString('local MySQL', $e->getMessage()); }
        }
    }

    public function test_command_preview_apply_restore_and_scoped_independent_page(): void
    {
        $this->seedFixture(); $before = $this->snapshot();
        $args = ['--plan' => 'preview.json', '--only' => [LinkingWordsContentPatch::NAMES[0]]];
        $this->artisan('content:patch-linking-words-m11', $args)->assertExitCode(0);
        self::assertSame($before, $this->snapshot());
        $plan = json_decode(File::get(storage_path('app/seo-m11-local/preview.json')), true);
        self::assertCount(4, $plan['changes']); self::assertCount(1, $plan['pages']);
        $this->artisan('content:patch-linking-words-m11', ['--apply' => true, '--plan' => 'preview.json', '--backup' => 'backup.json'])->assertExitCode(0);
        $this->artisan('content:patch-linking-words-m11', ['--restore' => true, '--backup' => 'backup.json'])->assertExitCode(0);
        self::assertSame($before, $this->snapshot());
        $this->artisan('content:patch-linking-words-m11', ['--plan' => '../bad.json'])->assertExitCode(1);
        $this->artisan('content:patch-linking-words-m11', ['--plan' => 'bad.json', '--only' => ['Foreign']])->assertExitCode(1);
        self::assertSame($before, $this->snapshot());
    }

    public function test_partial_lesson_and_unauthorized_source_changes_refuse_but_independent_page_can_be_planned(): void
    {
        $this->seedFixture(); $patch = $this->service(); $plan = $patch->plan();
        $change = $plan['changes'][0];
        DB::table($change['table'])->where('id', $change['id'])->update($change['after']);
        $before = $this->snapshot();
        try { $patch->plan(); self::fail('Partial lesson'); }
        catch (RuntimeException $e) { self::assertStringContainsString('Partially edited lesson', $e->getMessage()); }
        self::assertCount(4, $patch->plan([LinkingWordsContentPatch::NAMES[1]])['changes']);
        self::assertSame($before, $this->snapshot());
        $path = $this->sources[LinkingWordsContentPatch::NAMES[1]];
        $source = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        $source['page']['title'] = 'Forbidden retitle';
        File::put($path, json_encode($source));
        try { $patch->plan([LinkingWordsContentPatch::NAMES[1]]); self::fail('Retitled source'); }
        catch (RuntimeException $e) { self::assertStringContainsString('only subtitle, heading and body', $e->getMessage()); }
        self::assertSame($before, $this->snapshot());
    }

    public function test_explicit_local_optin_uses_verifier_before_backup_and_retains_noop_conflict_restore(): void
    {
        $this->seedFixture(); $before = $this->snapshot();
        // Only host I/O is substituted: the complete patch still uses isolated PDO,
        // exact snapshots, backup, transaction and the production default refusal.
        $verifier = new class extends M11LocalTargetGuard {
            public int $calls = 0;
            public bool $confirmed = true;
            public function verify(\Illuminate\Database\Connection $db, string $target, string $directory, ?string $proof, array $physical): void
            {
                $this->calls++;
                if (!$this->confirmed || $target !== 'gramlyze.loc' || $proof !== 'fixture-proof.json') {
                    throw new RuntimeException('Fixture local target unconfirmed.');
                }
                $this->assertEvidence(M11LocalTargetGuardTest::evidence(), 'd:/dev/htdocs/gramlyze.loc', 200, 3306);
            }
        };
        app()->instance(M11LocalTargetGuard::class, $verifier);
        app()->detectEnvironment(fn () => 'production');
        try {
            $patch = new LinkingWordsContentPatch(DB::connection(), database_path(), $this->private, 'gramlyze.loc', 'fixture-proof.json');
            $patch->savePlan($this->path('optin-plan'));
            $verifier->confirmed = false;
            try { $patch->apply($this->path('optin-plan'), $this->path('optin-backup')); self::fail('Unconfirmed proof'); }
            catch (RuntimeException $e) { self::assertStringContainsString('unconfirmed', $e->getMessage()); }
            self::assertFileDoesNotExist($this->path('optin-backup')); self::assertSame($before, $this->snapshot());
            $verifier->confirmed = true;
            self::assertSame(12, $patch->apply($this->path('optin-plan'), $this->path('optin-backup'))['updated']);
            self::assertSame(0, $patch->apply($this->path('optin-plan'), $this->path('not-created'))['updated']);
            self::assertFileDoesNotExist($this->path('not-created'));
            self::assertSame(12, $patch->restore($this->path('optin-backup'))['updated']);
            self::assertSame($before, $this->snapshot()); self::assertGreaterThan(3, $verifier->calls);
            DB::table('pages')->limit(1)->update(['text' => 'Manual change']);
            $manual = $this->snapshot();
            try { $patch->apply($this->path('optin-plan'), $this->path('other-backup')); self::fail('Manual change'); }
            catch (RuntimeException $e) { self::assertStringContainsString('mismatch', $e->getMessage()); }
            self::assertSame($manual, $this->snapshot()); self::assertFileDoesNotExist($this->path('other-backup'));
        } finally { app()->detectEnvironment(fn () => 'testing'); }
    }
}
