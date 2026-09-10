<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Services\OneOnesEditorialPatch;
use App\Services\PronounContentRepair;
use App\Support\Database\JsonPageSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class OneOnesEditorialPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $originalDatabasePath;
    private array $sources = [];
    private array $manifest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->originalDatabasePath = database_path();
        $private = storage_path('app/m8-1-fixture-'.bin2hex(random_bytes(8)));
        File::makeDirectory($private, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($private);
        foreach (['OneOnes', 'ReciprocalPronouns'] as $name) {
            $relative = 'seeders/Page_V3/PronounsDemonstratives/PronounsDemonstratives'.$name.'TheorySeeder/definition.json';
            File::ensureDirectoryExists(dirname($private.'/'.$relative));
            File::copy($this->originalDatabasePath.'/'.$relative, $private.'/'.$relative);
            File::copyDirectory(dirname($this->originalDatabasePath.'/'.$relative).'/localizations', dirname($private.'/'.$relative).'/localizations');
            $this->sources[$name] = $private.'/'.$relative;
        }
        File::ensureDirectoryExists($private.'/content-patches');
        File::copy($this->originalDatabasePath.'/'.OneOnesEditorialPatch::MANIFEST, $private.'/'.OneOnesEditorialPatch::MANIFEST);
        $this->manifest = json_decode(File::get($private.'/'.OneOnesEditorialPatch::MANIFEST), true, flags: JSON_THROW_ON_ERROR);
        app()->useDatabasePath($private);
        config(['coming-soon.enabled' => false]);
    }

    protected function tearDown(): void
    {
        app()->useDatabasePath($this->originalDatabasePath);
        parent::tearDown();
    }

    private function importEdition(string $edition = 'm8'): void
    {
        foreach ($this->sources as $name => $path) {
            $bytes = File::get($path);
            $source = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            if ($edition === 'm8' && $name === 'OneOnes') {
                foreach ($this->manifest['blocks'] as $entry) {
                    $source['page']['blocks'][$entry['sort_order'] - 1]['body'] = $entry['before_body'];
                    $source['page']['blocks'][$entry['sort_order'] - 1]['content']['html'] = $entry['before_body'];
                }
            } elseif ($edition === 'legacy') {
                foreach ($source['page']['blocks'] as &$block) {
                    if (isset($block['layout'])) { unset($block['type'], $block['heading'], $block['body']); }
                }
                unset($block);
            }
            File::put($path, json_encode($source, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            try {
                (new class($path) extends JsonPageSeeder {
                    public function __construct(private string $path) {}
                    protected function definitionPath(): string { return $this->path; }
                })->run();
            } finally { File::put($path, $bytes); }
        }
    }

    private function editorial(): OneOnesEditorialPatch { return new OneOnesEditorialPatch(DB::connection(), database_path(), database_path()); }
    private function path(string $name): string { return database_path($name.'.json'); }

    private function assertConflict(callable $action, string $message = ''): void
    {
        try { $action(); }
        catch (RuntimeException $e) {
            self::assertNotEmpty($e->getMessage());
            if ($message !== '') { self::assertStringContainsString($message, $e->getMessage()); }
            return;
        }
        self::fail('Expected a conflict, but the operation succeeded.');
    }

    private function snapshot(): array
    {
        $snapshot = [];
        foreach (['pages', 'page_categories', 'text_blocks', 'tag_text_block', 'page_tag', 'tags', 'questions', 'question_answers', 'question_options', 'question_theory_text_blocks', 'verb_hints'] as $table) {
            $snapshot[$table] = DB::table($table)->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();
        }
        return $snapshot;
    }

    private function assertFinalBodiesAndRender(): void
    {
        $source = json_decode(File::get($this->sources['OneOnes']), true, flags: JSON_THROW_ON_ERROR);
        $page = Page::where('seeder', OneOnesEditorialPatch::SEEDER)->sole();
        $uk = TextBlock::where('page_id', $page->id)->where('locale', 'uk')->orderBy('sort_order')->get();
        $page->setRelation('textBlocks', $uk);
        $page->setRelation('tags', collect());
        app()->setLocale('uk');
        $htmls = [view('theory.show', ['page' => $page, 'categories' => collect(), 'categoryPages' => collect()])->render(),
            view('courses.partials.theory-page-content', ['page' => $page])->render()];
        foreach ($source['page']['blocks'] as $i => $block) {
            self::assertSame($block['body'], $uk->firstWhere('sort_order', $i + 1)->body);
            if (isset($block['content']['html'])) { self::assertSame($block['content']['html'], $block['body']); }
        }
        foreach ($this->manifest['blocks'] as $entry) {
            foreach ($htmls as $html) {
                self::assertStringContainsString($entry['after_body'], $html);
                self::assertStringNotContainsString($entry['before_body'], $html);
                self::assertStringNotContainsString(htmlspecialchars($entry['after_body']), $html);
            }
        }
    }

    public function test_fresh_real_import_is_correct_and_patch_is_no_op(): void
    {
        $this->importEdition('current');
        $this->assertFinalBodiesAndRender();
        self::assertSame([], $this->editorial()->savePlan($this->path('plan'))['changes']);
        self::assertSame(['status' => 'no-op', 'updated' => 0], $this->editorial()->apply($this->path('plan'), $this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
    }

    public function test_accepted_m8_updates_only_five_bodies_and_preserves_every_other_record(): void
    {
        $this->importEdition();
        $qid = DB::table('questions')->insertGetId(['uuid' => 'm8-1-reference', 'question' => 'Fixture {a1}']);
        $oid = DB::table('question_options')->insertGetId(['option' => 'fixture']);
        DB::table('question_answers')->insert(['question_id' => $qid, 'option_id' => $oid, 'marker' => 'a1']);
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => 'm8-1-reference', 'text_block_uuid' => $this->manifest['blocks'][0]['uuid']]);
        $before = $this->snapshot();
        $plan = $this->editorial()->savePlan($this->path('plan'));
        self::assertSame($before, $this->snapshot());
        self::assertSame([2, 5, 7, 8, 9], array_column($plan['changes'], 'sort_order'));
        self::assertSame(5, $this->editorial()->apply($this->path('plan'), $this->path('backup'))['updated']);
        self::assertSame($plan, json_decode(File::get($this->path('backup')), true));
        $after = $this->snapshot();
        foreach ($before as $table => $rows) {
            if ($table !== 'text_blocks') { self::assertSame($rows, $after[$table], $table); continue; }
            $changes = collect($plan['changes'])->keyBy('id');
            foreach ($rows as $i => $row) {
                $change = $changes[$row['id']] ?? null;
                if ($change) {
                    self::assertSame(['body'], array_keys($change['after']));
                    self::assertSame(['body' => $row['body']], $change['before']);
                }
                self::assertSame($change ? array_replace($row, $change['after']) : $row, $after[$table][$i]);
            }
        }
        self::assertSame(0, $this->editorial()->apply($this->path('plan'), $this->path('unused'))['updated']);
        self::assertSame($after, $this->snapshot());
        self::assertFileDoesNotExist($this->path('unused'));
        $this->assertFinalBodiesAndRender();
    }

    #[DataProvider('conflicts')]
    public function test_unknown_or_foreign_content_conflicts_without_writes(string $kind): void
    {
        $this->importEdition();
        $uuid = $this->manifest['blocks'][0]['uuid'];
        $values = match ($kind) {
            'manual' => ['body' => '<p>Manual edit</p>'], 'empty' => ['body' => null],
            'locale' => ['locale' => 'en'], 'owner' => ['seeder' => 'Foreign'],
            'page' => ['page_id' => 99999], 'uuid' => ['uuid' => 'foreign'],
            'order' => ['sort_order' => 99], 'heading' => ['heading' => 'Edited heading'],
            'type' => ['type' => 'html'],
        };
        DB::table('text_blocks')->where('uuid', $uuid)->update($values);
        $before = $this->snapshot();
        $this->assertConflict(fn () => $this->editorial()->savePlan($this->path('plan')));
        self::assertSame($before, $this->snapshot());
        self::assertFileDoesNotExist($this->path('plan'));
    }

    public static function conflicts(): array
    {
        return array_map(fn ($k) => [$k], ['manual', 'empty', 'locale', 'owner', 'page', 'uuid', 'order', 'heading', 'type']);
    }

    public function test_preview_source_and_concurrent_locale_changes_are_refused(): void
    {
        $this->importEdition();
        $this->editorial()->savePlan($this->path('plan'));
        $path = $this->sources['OneOnes']; $raw = File::get($path);
        File::append($path, "\n"); // Same semantic edition, different inspected raw hash.
        $this->assertConflict(fn () => $this->editorial()->apply($this->path('plan'), $this->path('backup')), 'stale');
        File::put($path, $raw);
        $first = $this->manifest['blocks'][0];
        DB::table('text_blocks')->where('uuid', $first['uuid'])->update(['body' => '<p>Concurrent manual body</p>']);
        $manual = $this->snapshot();
        $this->assertConflict(fn () => $this->editorial()->apply($this->path('plan'), $this->path('backup')), 'Manual/unknown');
        self::assertSame($manual, $this->snapshot());
        // Restore only this isolated fixture to test an independent EN conflict.
        DB::table('text_blocks')->where('uuid', $first['uuid'])->update(['body' => $first['before_body']]);
        $pageId = Page::where('seeder', OneOnesEditorialPatch::SEEDER)->sole()->id;
        self::assertGreaterThan(0, DB::table('text_blocks')->where('page_id', $pageId)->where('locale', 'en')->update(['heading' => 'Concurrent edit']));
        $before = $this->snapshot();
        $this->assertConflict(fn () => $this->editorial()->apply($this->path('plan'), $this->path('backup')), 'stale');
        self::assertSame($before, $this->snapshot());
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_tampered_historical_manifest_and_new_definition_are_rejected(): void
    {
        $this->importEdition();
        $path = database_path(OneOnesEditorialPatch::MANIFEST); $raw = File::get($path);
        $data = $this->manifest; $data['blocks'][0]['before_body'] .= 'manual';
        File::put($path, json_encode($data));
        $this->assertConflict(fn () => $this->editorial()->plan(), 'accepted M8');
        File::put($path, $raw);
        $source = json_decode(File::get($this->sources['OneOnes']), true);
        $source['page']['blocks'][1]['body'] .= 'manual';
        File::put($this->sources['OneOnes'], json_encode($source));
        $this->expectExceptionMessage('approved M8.1 edition');
        $this->editorial()->plan();
    }

    public function test_backup_collision_truncated_plan_and_mid_transaction_failure_are_atomic(): void
    {
        $this->importEdition();
        $patch = $this->editorial(); $plan = $patch->savePlan($this->path('plan')); $before = $this->snapshot();
        File::put($this->path('existing'), 'Preserve evidence');
        $this->assertConflict(fn () => $patch->apply($this->path('plan'), $this->path('existing')), 'exclusive');
        self::assertSame('Preserve evidence', File::get($this->path('existing')));
        self::assertSame($before, $this->snapshot());
        $bad = $plan; array_pop($bad['changes']); unset($bad['sha256']); $bad['sha256'] = PronounContentRepair::digest($bad);
        File::put($this->path('bad'), json_encode($bad));
        $this->assertConflict(fn () => $patch->apply($this->path('bad'), $this->path('unused')), 'incomplete');
        self::assertFileDoesNotExist($this->path('unused'));
        $failing = new class(DB::connection(), database_path(), database_path()) extends OneOnesEditorialPatch {
            protected function afterUpdate(int $index): void { if ($index === 1) { throw new RuntimeException('Simulated write failure'); } }
        };
        $this->assertConflict(fn () => $failing->apply($this->path('plan'), $this->path('backup')), 'Simulated write failure');
        self::assertSame($before, $this->snapshot());
        self::assertSame($plan, json_decode(File::get($this->path('backup')), true));
    }

    public function test_legacy_empty_m8_route_still_works_without_weakening_its_guard(): void
    {
        $this->importEdition('legacy');
        $repair = new PronounContentRepair(DB::connection(), database_path(), database_path());
        self::assertCount(19, $repair->savePlan($this->path('legacy'))['changes']);
        $ids = DB::table('text_blocks')->orderBy('id')->pluck('uuid', 'id')->all();
        self::assertSame(19, $repair->apply($this->path('legacy'), $this->path('legacy-backup'))['updated']);
        self::assertSame($ids, DB::table('text_blocks')->orderBy('id')->pluck('uuid', 'id')->all());
        self::assertSame([], $this->editorial()->plan()['changes']);
        $this->assertFinalBodiesAndRender();
        DB::table('text_blocks')->where('uuid', $this->manifest['blocks'][0]['uuid'])->update(['body' => $this->manifest['blocks'][0]['before_body']]);
        $this->expectExceptionMessage('Nonempty/manual content is preserved');
        $repair->plan(); // M8 must NOT become an editor of already nonempty old content.
    }

    public function test_missing_ambiguous_pages_and_default_cli_preview(): void
    {
        $this->importEdition(); $before = $this->snapshot();
        $this->artisan('content:patch-one-ones-m8-1', ['--plan' => 'cli-preview.json'])->assertExitCode(0);
        self::assertSame($before, $this->snapshot());
        self::assertFileExists(storage_path('app/seo-m8-1-local/cli-preview.json'));
        $this->artisan('content:patch-one-ones-m8-1', ['--plan' => '../escape.json'])->assertExitCode(1);
        $page = (array) DB::table('pages')->where('seeder', OneOnesEditorialPatch::SEEDER)->first(); unset($page['id']);
        DB::table('pages')->insert($page);
        $this->assertConflict(fn () => $this->editorial()->plan(), 'ambiguous');
        DB::table('pages')->where('seeder', OneOnesEditorialPatch::SEEDER)->delete();
        $this->expectExceptionMessage('Missing'); $this->editorial()->plan();
    }

    public function test_eight_reviewed_exercises_key_and_noncontradictory_examples(): void
    {
        $source = json_decode(File::get($this->sources['OneOnes']), true);
        $body = $source['page']['blocks'][8]['body'];
        preg_match_all('/<li>(.*?)<\/li>/s', $body, $items);
        self::assertSame([
            "I don't like this bag. Can I see a blue ___? (one / ones)",
            'These apples are too small. Do you have two bigger ___? (one / ones)',
            'Which car is yours? — The red ___. (one / ones)',
            "I need a pen. — Here's a black ___. (one / ones)",
            'My book is damaged. Can I borrow ___? (your / yours)',
            'I need some chairs. — We have some wooden ___. (one / ones)',
            'There is some water in the jug. Would you like ___? (one / some)',
            'I like both dresses, but ___ is more expensive. (this one / these ones)',
        ], $items[1]);
        self::assertStringContainsString('1. one, 2. ones, 3. one, 4. one, 5. yours, 6. ones, 7. some, 8. this one', $body);
        $historical = $this->manifest['blocks'][4]['before_body'];
        preg_match_all('/<li>(.*?)<\/li>/s', $historical, $old);
        foreach ([2, 3, 5] as $i) { self::assertSame($old[1][$i], $items[1][$i]); }
        $mistakes = $source['page']['blocks'][6]['body'];
        preg_match_all('/❌ <strong>(.*?)<\/strong>/s', $mistakes, $wrong);
        preg_match_all('/✅ <strong>(.*?)<\/strong>/s', $mistakes, $right);
        self::assertSame([], array_values(array_intersect($wrong[1], $right[1])));
        self::assertStringNotContainsString('❌ <strong>I like your one.', $mistakes);
        self::assertStringContainsString('some wooden ones', $source['page']['blocks'][7]['body']);
        self::assertStringNotContainsString('either, neither', $source['page']['blocks'][7]['body']);
    }
}
