<?php

namespace Tests\Feature;

use App\Services\AuthoredNativeContentPatch;
use App\Support\Database\JsonPageSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class AuthoredNativeContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $originalDatabasePath;
    private string $root;
    private string $databasePath;
    private string $private;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->originalDatabasePath = database_path();
        $this->root = storage_path('app/m24-fixture-'.bin2hex(random_bytes(8)));
        $this->databasePath = $this->root.'/database';
        $this->private = $this->root.'/evidence';
        foreach ([$this->private, $this->databasePath.'/content-patches', $this->root.'/docs/content'] as $directory) {
            File::makeDirectory($directory, 0700, true);
            IsolatedTestEnvironment::assertOwnedPath($directory);
        }
        File::copy(base_path('docs/content/m24-authored-content.v1.json'), $this->root.'/docs/content/m24-authored-content.v1.json');
        File::copy($this->originalDatabasePath.'/content-patches/m24-authored-native-before.json',
            $this->databasePath.'/content-patches/m24-authored-native-before.json');
        foreach (self::relativeDefinitions() as $relative) {
            File::copyDirectory(dirname($this->originalDatabasePath.'/'.$relative), dirname($this->databasePath.'/'.$relative));
        }
        app()->useDatabasePath($this->databasePath);
    }

    protected function tearDown(): void
    {
        app()->useDatabasePath($this->originalDatabasePath);
        parent::tearDown();
    }

    private static function relativeDefinitions(): array
    {
        return [
            'seeders/Page_V3/Tenses/TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder/definition.json',
            'seeders/Page_V3/Tenses/TensesNarrativeTensesTheorySeeder/definition.json',
            'seeders/Page_V3/BasicGrammar/BasicGrammarB1MixedRevisionTheorySeeder/definition.json',
        ];
    }

    private function seedFixture(): void
    {
        $manifest = json_decode(File::get($this->databasePath.'/content-patches/m24-authored-native-before.json'), true, flags: JSON_THROW_ON_ERROR);
        foreach (AuthoredNativeContentPatch::NAMES as $index => $name) {
            $path = $this->databasePath.'/'.self::relativeDefinitions()[$index];
            $after = File::get($path);
            File::put($path, json_encode($manifest['definitions'][$name], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
            try {
                (new class($path) extends JsonPageSeeder
                {
                    public function __construct(private string $path) {}
                    protected function definitionPath(): string { return $this->path; }
                })->run();
            } finally { File::put($path, $after); }
        }
        // Foreign locales and a bank are present, but are outside the update allowlist.
        $uk = (array) DB::table('text_blocks')->where('seeder', AuthoredNativeContentPatch::NAMES[0])->where('locale', 'uk')->where('sort_order', 2)->sole();
        $id = DB::table('questions')->insertGetId(['uuid' => 'm24-protected-question', 'question' => 'Protected {a1}',
            'theory_text_block_uuid' => $uk['uuid']]);
        $option = DB::table('question_options')->insertGetId(['option' => 'protected']);
        DB::table('question_answers')->insert(['question_id' => $id, 'option_id' => $option, 'marker' => 'a1']);
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => 'm24-protected-question', 'text_block_uuid' => $uk['uuid']]);
    }

    private function service(): AuthoredNativeContentPatch
    {
        return new AuthoredNativeContentPatch(DB::connection(), $this->databasePath, $this->private);
    }

    private function path(string $name): string { return $this->private.'/'.$name.'.json'; }

    private function snapshot(): array
    {
        $out = [];
        foreach (['pages', 'page_categories', 'text_blocks', 'page_tag', 'tag_text_block', 'tags', 'page_category_tag',
            'questions', 'question_answers', 'question_options', 'question_option_question', 'question_theory_text_blocks',
            'question_tag', 'question_marker_tag', 'verb_hints', 'question_hints', 'question_variants',
            'saved_grammar_tests', 'saved_grammar_test_questions', 'seed_runs'] as $table) {
            $out[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
        }
        return $out;
    }

    public function test_exact_three_page_preview_apply_backup_noop_and_guarded_restore(): void
    {
        $this->seedFixture();
        $patch = $this->service();
        $before = $this->snapshot();
        $plan = $patch->savePlan($this->path('preview'));
        self::assertSame($before, $this->snapshot());
        self::assertSame('before', $plan['state']);
        self::assertSame(AuthoredNativeContentPatch::NAMES, array_keys($plan['pages']));
        self::assertCount(3, $plan['inserts']);
        self::assertCount(23, $plan['updates']);
        self::assertSame(['tenses'], array_column($plan['pages'][AuthoredNativeContentPatch::NAMES[0]]['category_ancestry'], 'slug'));
        self::assertSame(['mixed-revision'], array_column($plan['pages'][AuthoredNativeContentPatch::NAMES[2]]['category_ancestry'], 'slug'));
        $result = $patch->apply($this->path('preview'), $this->path('backup'));
        self::assertSame(['status' => 'applied', 'updated' => 23, 'inserted' => 3, 'backup' => $this->path('backup')], $result);
        self::assertSame($plan, json_decode(File::get($this->path('backup')), true));
        $after = $this->snapshot();
        foreach ($before as $table => $rows) {
            if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $after[$table], $table); }
        }
        self::assertCount(count($before['text_blocks']) + 3, $after['text_blocks']);
        self::assertSame('after', $patch->plan()['state']);
        self::assertSame([], $patch->plan()['updates']);
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $patch->apply($this->path('preview'), $this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
        self::assertSame(['status' => 'restored', 'updated' => 23, 'deleted' => 3], $patch->restore($this->path('backup')));
        self::assertSame($before, $this->snapshot());
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'deleted' => 0], $patch->restore($this->path('backup')));
    }

    public static function conflicts(): array
    {
        return array_map(fn ($kind) => [$kind], ['page-text', 'native-body', 'wrong-category', 'missing-block',
            'wrong-owner', 'uuid-collision', 'wrong-order', 'foreign-uuid-collision']);
    }

    #[DataProvider('conflicts')]
    public function test_manual_and_identity_conflicts_refuse_preview_without_writes(string $kind): void
    {
        $this->seedFixture();
        $name = AuthoredNativeContentPatch::NAMES[0];
        $page = (array) DB::table('pages')->where('seeder', $name)->sole();
        $block = (array) DB::table('text_blocks')->where('page_id', $page['id'])->where('locale', 'uk')->where('sort_order', 2)->sole();
        if ($kind === 'page-text') DB::table('pages')->where('id', $page['id'])->update(['text' => 'Manual text']);
        if ($kind === 'native-body') DB::table('text_blocks')->where('id', $block['id'])->update(['body' => 'Manual body']);
        if ($kind === 'wrong-category') {
            $other = DB::table('page_categories')->where('slug', 'mixed-revision')->value('id');
            DB::table('pages')->where('id', $page['id'])->update(['page_category_id' => $other]);
        }
        if ($kind === 'missing-block') DB::table('text_blocks')->where('id', $block['id'])->delete();
        if ($kind === 'wrong-owner') DB::table('text_blocks')->where('id', $block['id'])->update(['page_category_id' => 999]);
        if ($kind === 'wrong-order') DB::table('text_blocks')->where('id', $block['id'])->update(['sort_order' => 99]);
        if ($kind === 'uuid-collision' || $kind === 'foreign-uuid-collision') {
            $planned = $this->service()->plan()['inserts'][0]['fields'];
            DB::table('text_blocks')->insert($planned + ['created_at' => now(), 'updated_at' => now()]);
            if ($kind === 'foreign-uuid-collision') {
                DB::table('text_blocks')->where('uuid', $planned['uuid'])->update(['page_id' => DB::table('pages')->where('seeder', AuthoredNativeContentPatch::NAMES[1])->value('id')]);
            }
        }
        $before = $this->snapshot();
        try { $this->service()->savePlan($this->path('conflict')); self::fail('M24 conflict was accepted: '.$kind); }
        catch (RuntimeException) {}
        self::assertSame($before, $this->snapshot());
        self::assertFileDoesNotExist($this->path('conflict'));
    }

    public function test_stale_preview_and_source_or_row_edit_refuse_apply_without_backup(): void
    {
        $this->seedFixture();
        $patch = $this->service();
        $patch->savePlan($this->path('preview'));
        $name = AuthoredNativeContentPatch::NAMES[0];
        DB::table('pages')->where('seeder', $name)->update(['text' => 'Manual after preview']);
        $before = $this->snapshot();
        try { $patch->apply($this->path('preview'), $this->path('backup')); self::fail('Stale row accepted.'); }
        catch (RuntimeException) {}
        self::assertSame($before, $this->snapshot());
        self::assertFileDoesNotExist($this->path('backup'));
        DB::table('pages')->where('seeder', $name)->update(['text' => $this->manifest()['definitions'][$name]['page']['subtitle_text']]);
        $source = $this->databasePath.'/'.self::relativeDefinitions()[0];
        $bytes = File::get($source);
        File::put($source, $bytes.' ');
        try { $patch->apply($this->path('preview'), $this->path('backup')); self::fail('Stale source accepted.'); }
        catch (RuntimeException) {}
        self::assertFileDoesNotExist($this->path('backup'));
    }

    private function manifest(): array
    {
        return json_decode(File::get($this->databasePath.'/content-patches/m24-authored-native-before.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function rollbackPhases(): array { return [['update'], ['insert']]; }

    #[DataProvider('rollbackPhases')]
    public function test_transaction_rolls_back_after_update_or_insert(string $phase): void
    {
        $this->seedFixture();
        $before = $this->snapshot();
        $patch = new class(DB::connection(), $this->databasePath, $this->private, $phase) extends AuthoredNativeContentPatch
        {
            public function __construct($db, $databasePath, $private, private string $phase)
            { parent::__construct($db, $databasePath, $private); }
            protected function afterUpdate(int $index): void
            { if ($this->phase === 'update' && $index === 0) throw new RuntimeException('Injected update failure'); }
            protected function afterInsert(int $index): void
            { if ($this->phase === 'insert' && $index === 0) throw new RuntimeException('Injected insert failure'); }
        };
        $patch->savePlan($this->path('preview'));
        try { $patch->apply($this->path('preview'), $this->path('backup')); self::fail('Expected rollback.'); }
        catch (RuntimeException $error) { self::assertStringContainsString('Injected', $error->getMessage()); }
        self::assertSame($before, $this->snapshot());
        self::assertFileExists($this->path('backup')); // Backup precedes the first mutation, even on rollback.
    }

    public function test_guarded_restore_refuses_manual_new_box_edits_and_relations(): void
    {
        $this->seedFixture();
        $patch = $this->service();
        $plan = $patch->savePlan($this->path('preview'));
        $patch->apply($this->path('preview'), $this->path('backup'));
        $uuid = $plan['inserts'][0]['fields']['uuid'];
        $id = DB::table('text_blocks')->where('uuid', $uuid)->value('id');
        DB::table('text_blocks')->where('id', $id)->update(['body' => 'A later manual note']);
        $before = $this->snapshot();
        try { $patch->restore($this->path('backup')); self::fail('Manual new box edit was removed.'); }
        catch (RuntimeException) {}
        self::assertSame($before, $this->snapshot());
        DB::table('text_blocks')->where('id', $id)->update(['body' => $plan['inserts'][0]['fields']['body']]);
        DB::table('tag_text_block')->insert(['text_block_id' => $id, 'tag_id' => DB::table('tags')->value('id')]);
        $before = $this->snapshot();
        try { $patch->restore($this->path('backup')); self::fail('New relation was deleted.'); }
        catch (RuntimeException) {}
        self::assertSame($before, $this->snapshot());
    }

    public function test_production_default_refuses_write_without_local_guard(): void
    {
        $this->seedFixture();
        $patch = $this->service();
        $patch->savePlan($this->path('preview'));
        app()->detectEnvironment(fn () => 'production');
        try { $patch->apply($this->path('preview'), $this->path('backup')); self::fail('Production default accepted.'); }
        catch (RuntimeException $error) { self::assertStringContainsString('local-target', $error->getMessage()); }
        self::assertFileDoesNotExist($this->path('backup'));
    }
}
