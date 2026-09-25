<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Services\PronounContentRepair;
use App\Support\Database\JsonPageDefinitionIndex;
use App\Support\Database\JsonPageSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PronounContentRepairTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $originalDatabasePath;
    private array $sources = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->originalDatabasePath = database_path();
        $private = storage_path('app/m8-fixture-'.bin2hex(random_bytes(8)));
        File::makeDirectory($private, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($private);
        foreach (['OneOnes', 'ReciprocalPronouns'] as $name) {
            $relative = 'seeders/Page_V3/PronounsDemonstratives/PronounsDemonstratives'.$name.'TheorySeeder/definition.json';
            File::ensureDirectoryExists(dirname($private.'/'.$relative));
            File::copy($this->originalDatabasePath.'/'.$relative, $private.'/'.$relative);
            File::copyDirectory(dirname($this->originalDatabasePath.'/'.$relative).'/localizations', dirname($private.'/'.$relative).'/localizations');
            $this->sources[$name] = $private.'/'.$relative;
        }
        // The REAL localization manager discovers only these private copies.
        app()->useDatabasePath($private);
        config(['coming-soon.enabled' => false]);
    }

    protected function tearDown(): void
    {
        app()->useDatabasePath($this->originalDatabasePath);
        parent::tearDown();
    }

    private function loadSource(string $path): array
    {
        $definition = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        (new class($path) extends JsonPageSeeder {
            public function __construct(private string $source) {}
            protected function definitionPath(): string { return $this->source; }
        })->run();
        return $definition;
    }

    public function test_real_loader_preserves_source_explanation_in_both_lessons(): void
    {
        foreach ($this->sources as $path) {
            $source = $this->loadSource($path);
            $page = Page::where('seeder', $source['seeder']['class'])->sole();
            $block = TextBlock::where('page_id', $page->id)->where('locale', 'uk')->where('sort_order', 1)->sole();
            $expected = $source['page']['blocks'][0]['content']['html'];
            self::assertNotEmpty($expected, 'Legacy source really contains the explanation.');
            self::assertStringContainsString($expected, (string) $block->body, 'Real loader lost the source explanation: '.$source['slug']);
            $index = app(JsonPageDefinitionIndex::class)->indexBlocks($source, $path);
            self::assertSame($block->body, $index['items'][$index['by_index'][1]]['body']);
        }
    }

    private function seedBoth(bool $legacy = false): array
    {
        $definitions = [];
        foreach ($this->sources as $name => $path) {
            $bytes = File::get($path);
            $source = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            $definitions[$name] = $source;
            if ($legacy) {
                foreach ($source['page']['blocks'] as &$block) {
                    if (isset($block['layout'])) { unset($block['type'], $block['heading'], $block['body']); }
                }
                unset($block);
                File::put($path, json_encode($source, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            }
            try { $this->loadSource($path); } finally { File::put($path, $bytes); }
        }
        return $definitions;
    }

    private function repair(): PronounContentRepair
    {
        return new PronounContentRepair(DB::connection(), database_path(), dirname(reset($this->sources)));
    }

    private function path(string $name): string { return dirname(reset($this->sources)).'/'.$name.'.json'; }

    private function snapshot(): array
    {
        $snapshot = [];
        foreach (['pages', 'page_categories', 'text_blocks', 'tag_text_block', 'page_tag', 'tags', 'questions', 'question_answers', 'question_options', 'question_theory_text_blocks'] as $table) {
            $snapshot[$table] = DB::table($table)->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();
        }
        return $snapshot;
    }

    public function test_legacy_fixture_reproduces_loss_for_every_target_then_repair_matches_fresh_import(): void
    {
        $sources = $this->seedBoth(true);
        // Nonempty reference/answer fixtures prove repair does not break consumers.
        // Direct fixture SQL avoids question exporters entirely.
        $uuid = TextBlock::where('locale', 'uk')->where('sort_order', 1)->value('uuid');
        $questionId = DB::table('questions')->insertGetId(['uuid' => 'm8-reference-fixture', 'question' => 'Fixture {a1}', 'theory_text_block_uuid' => $uuid]);
        $optionId = DB::table('question_options')->insertGetId(['option' => 'fixture answer']);
        DB::table('question_answers')->insert(['question_id' => $questionId, 'option_id' => $optionId, 'marker' => 'a1']);
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => 'm8-reference-fixture', 'text_block_uuid' => $uuid]);
        $legacy = $this->snapshot();
        $repair = $this->repair();
        $plan = $repair->savePlan($this->path('plan'));
        self::assertSame($legacy, $this->snapshot(), 'Dry-run must not write.');
        self::assertCount(19, $plan['changes']);
        foreach ($plan['changes'] as $change) {
            self::assertSame(['type' => 'box', 'heading' => null, 'body' => null], $change['before']);
        }
        self::assertSame(19, $repair->apply($this->path('plan'), $this->path('backup'))['updated']);
        $repaired = $this->snapshot();
        self::assertSame(0, $repair->apply($this->path('plan'), $this->path('unused'))['updated']);
        self::assertFileDoesNotExist($this->path('unused'));
        self::assertSame($repaired, $this->snapshot());
        foreach ($legacy as $table => $rows) {
            if ($table !== 'text_blocks') { self::assertSame($rows, $repaired[$table], $table); }
        }
        $changes = collect($plan['changes'])->keyBy('id');
        foreach ($legacy['text_blocks'] as $i => $old) {
            self::assertSame(isset($changes[$old['id']]) ? array_replace($old, $changes[$old['id']]['after']) : $old, $repaired['text_blocks'][$i]);
        }
        foreach ($sources as $source) { $this->assertLessonContent($source); }
        self::assertSame(19, $repair->restore($this->path('backup'))['updated']);
        self::assertSame($legacy, $this->snapshot(), 'Rollback restores exactly the fixture, not working DB.');
        self::assertSame(0, $repair->restore($this->path('backup'))['updated']);
        $this->seedBoth();
        // Fresh seeding may replace numeric IDs/timestamps; identity and content must agree.
        $fields = ['uuid', 'locale', 'seeder', 'sort_order', 'type', 'heading', 'body'];
        $content = fn ($rows) => collect($rows)->mapWithKeys(fn ($r) => [$r['uuid'] => array_intersect_key($r, array_flip($fields))])->sortKeys()->all();
        self::assertSame($content($repaired['text_blocks']), $content($this->snapshot()['text_blocks']));
        self::assertCount(count($repaired['text_blocks']), $this->snapshot()['text_blocks']);
        foreach ($sources as $source) { $this->assertLessonContent($source); }
    }

    private function assertLessonContent(array $source): void
    {
        $page = Page::where('seeder', $source['seeder']['class'])->sole();
        $uk = TextBlock::where('page_id', $page->id)->where('locale', 'uk')->orderBy('sort_order')->get();
        $page->setRelation('textBlocks', $uk);
        $page->setRelation('tags', collect());
        app()->setLocale('uk');
        $htmls = [view('theory.show', ['page' => $page, 'categories' => collect(), 'categoryPages' => collect()])->render(),
            view('courses.partials.theory-page-content', ['page' => $page])->render()];
        foreach ($source['page']['blocks'] as $i => $expected) {
            $actual = $uk->firstWhere('sort_order', $i + 1);
            self::assertNotNull($actual);
            self::assertSame($expected['body'], $actual->body, $source['slug'].' block '.($i + 1));
            if (!isset($expected['layout'])) { continue; }
            $fragments = [];
            if (isset($expected['content']['html'])) { $fragments[] = $expected['content']['html']; }
            foreach ($expected['columns'] ?? [] as $col) { $fragments[] = $col['html']; $fragments[] = $col['heading']; }
            foreach ($expected['mistakes'] ?? [] as $mistake) { array_push($fragments, ...array_values($mistake)); }
            array_push($fragments, ...($expected['summary_points'] ?? []));
            foreach ($expected['exercises'] ?? [] as $exercise) { $fragments[] = $exercise['instruction']; array_push($fragments, ...$exercise['items']); }
            foreach ($expected['links'] ?? [] as $link) { array_push($fragments, ...array_values($link)); }
            if (!empty($expected['content']['category_back'])) { $fragments[] = $source['page']['category']['title']; }
            self::assertNotEmpty($fragments);
            foreach ($fragments as $fragment) { self::assertStringContainsString($fragment, html_entity_decode($actual->body, ENT_QUOTES | ENT_HTML5), $actual->uuid); }
            foreach ($htmls as $html) {
                self::assertStringContainsString($actual->body, $html, 'Real theory/course renderer must preserve block HTML.');
                self::assertStringNotContainsString(htmlspecialchars($actual->body), $html);
            }
            if (isset($expected['columns'])) { self::assertSame(2, substr_count($actual->body, '<section>')); }
        }
    }

    #[DataProvider('conflicts')]
    public function test_refuses_foreign_or_manual_rows_without_any_write(string $kind): void
    {
        $this->seedBoth(true);
        $id = DB::table('text_blocks')->where('locale', 'uk')->where('sort_order', 1)->value('id');
        $change = match ($kind) {
            'foreign-page' => ['page_id' => 999999], 'locale' => ['locale' => 'en'],
            'uuid' => ['uuid' => 'foreign'], 'owner' => ['seeder' => 'Manual'],
            'order' => ['sort_order' => 88], 'manual' => ['body' => '<p>Manual content</p>'],
            'heading' => ['heading' => 'Manual title'],
        };
        DB::table('text_blocks')->where('id', $id)->update($change);
        $before = $this->snapshot();
        try { $this->repair()->savePlan($this->path('plan')); self::fail('Must refuse '.$kind); }
        catch (RuntimeException $e) { self::assertNotEmpty($e->getMessage()); }
        self::assertSame($before, $this->snapshot());
        self::assertFileDoesNotExist($this->path('plan'));
    }

    public static function conflicts(): array
    {
        return array_map(fn ($kind) => [$kind], ['foreign-page', 'locale', 'uuid', 'owner', 'order', 'manual', 'heading']);
    }

    public function test_stale_source_and_stale_database_are_refused(): void
    {
        $this->seedBoth(true);
        $repair = $this->repair();
        $repair->savePlan($this->path('plan'));
        $path = reset($this->sources); $original = File::get($path);
        File::append($path, "\n");
        try { $repair->apply($this->path('plan'), $this->path('backup')); self::fail('Changed source'); }
        catch (RuntimeException $e) { self::assertStringContainsString('stale', $e->getMessage()); }
        File::put($path, $original);
        DB::table('text_blocks')->where('locale', 'en')->update(['heading' => 'Concurrent locale edit']);
        $before = $this->snapshot();
        try { $repair->apply($this->path('plan'), $this->path('backup')); self::fail('Changed DB'); }
        catch (RuntimeException $e) { self::assertStringContainsString('stale', $e->getMessage()); }
        self::assertSame($before, $this->snapshot());
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_corrupt_plans_backups_and_mid_transaction_failure_are_atomic(): void
    {
        $this->seedBoth(true);
        $repair = $this->repair();
        $plan = $repair->savePlan($this->path('plan'));
        $before = $this->snapshot();
        $bad = $plan; array_pop($bad['changes']);
        unset($bad['sha256']); $bad['sha256'] = PronounContentRepair::digest($bad);
        File::put($this->path('bad'), json_encode($bad));
        try { $repair->apply($this->path('bad'), $this->path('backup')); self::fail('Incomplete plan'); }
        catch (RuntimeException) { self::assertSame($before, $this->snapshot()); }
        $failing = new class(DB::connection(), database_path(), dirname(reset($this->sources))) extends PronounContentRepair {
            protected function afterUpdate(int $index): void { if ($index === 0) { throw new RuntimeException('Simulated write failure'); } }
        };
        try { $failing->apply($this->path('plan'), $this->path('failed-backup')); self::fail('Failure must roll back'); }
        catch (RuntimeException $e) { self::assertSame('Simulated write failure', $e->getMessage()); }
        self::assertSame($before, $this->snapshot());
        self::assertFileExists($this->path('failed-backup'));
        $repair->apply($this->path('plan'), $this->path('backup'));
        $after = $this->snapshot();
        foreach ([$bad, array_diff_key($plan, ['pages' => true])] as $i => $broken) {
            File::put($this->path('broken-'.$i), json_encode($broken));
            try { $repair->restore($this->path('broken-'.$i)); self::fail('Incomplete backup'); }
            catch (RuntimeException) { self::assertSame($after, $this->snapshot()); }
        }
        try { $failing->restore($this->path('backup')); self::fail('Restore failure must roll back'); }
        catch (RuntimeException) { self::assertSame($after, $this->snapshot()); }
    }

    public function test_existing_backup_is_not_overwritten_and_normal_box_format_and_locales_work(): void
    {
        $this->seedBoth(true);
        $repair = $this->repair();
        $repair->savePlan($this->path('plan'));
        File::put($this->path('backup'), 'existing evidence');
        $before = $this->snapshot();
        try { $repair->apply($this->path('plan'), $this->path('backup')); self::fail('Do not overwrite'); }
        catch (RuntimeException) { self::assertSame($before, $this->snapshot()); }
        self::assertSame('existing evidence', File::get($this->path('backup')));
        $source = json_decode(File::get(reset($this->sources)), true, flags: JSON_THROW_ON_ERROR);
        $source['slug'] = 'ordinary-control';
        $source['seeder']['class'] = 'ControlSeeder';
        $source['page']['locale'] = 'pl';
        $source['page']['blocks'] = [['type' => 'box', 'heading' => 'Zwykły blok', 'body' => '<p>Kontrola <strong>treści</strong>.</p>']];
        File::put($this->path('ordinary'), json_encode($source));
        $this->loadSource($this->path('ordinary'));
        self::assertSame('<p>Kontrola <strong>treści</strong>.</p>', TextBlock::where('seeder', 'ControlSeeder')->where('sort_order', 1)->sole()->body);
        self::assertGreaterThan(0, TextBlock::where('locale', 'en')->count());
        self::assertGreaterThan(0, TextBlock::where('locale', 'pl')->count());
    }

    public function test_missing_or_ambiguous_page_and_remote_connection_are_refused(): void
    {
        $this->seedBoth(true);
        $page = DB::table('pages')->first();
        $row = (array) $page; unset($row['id']);
        DB::table('pages')->insert($row);
        try { $this->repair()->plan(); self::fail('Ambiguous Page'); }
        catch (RuntimeException $e) { self::assertStringContainsString('ambiguous', $e->getMessage()); }
        DB::table('pages')->where('seeder', $page->seeder)->delete();
        try { $this->repair()->plan(); self::fail('Missing Page'); }
        catch (RuntimeException $e) { self::assertStringContainsString('Missing', $e->getMessage()); }
        $remote = new \Illuminate\Database\MySqlConnection(new \PDO('sqlite::memory:'), 'target', '', ['host' => '192.0.2.1']);
        $service = new PronounContentRepair($remote, database_path(), dirname(reset($this->sources)));
        try { $service->connection('target', true); self::fail('Remote DB'); }
        catch (RuntimeException $e) { self::assertStringContainsString('local MySQL', $e->getMessage()); }
        $falseDriver = new \Illuminate\Database\MySqlConnection(new \PDO('sqlite::memory:'), 'target', '', ['host' => 'localhost']);
        $service = new PronounContentRepair($falseDriver, database_path(), dirname(reset($this->sources)));
        try { $service->connection('target', true); self::fail('PDO/config mismatch'); }
        catch (RuntimeException $e) { self::assertStringContainsString('local MySQL', $e->getMessage()); }
    }

    public function test_command_is_dry_run_by_default_and_uses_private_new_names(): void
    {
        $this->seedBoth(true);
        $before = $this->snapshot();
        $this->artisan('content:repair-pronoun-blocks', ['--plan' => 'command-preview.json'])->assertExitCode(0);
        self::assertSame($before, $this->snapshot());
        self::assertFileExists(storage_path('app/seo-m8-local/command-preview.json'));
        $this->artisan('content:repair-pronoun-blocks', ['--plan' => '../outside.json'])->assertExitCode(1);
        self::assertSame($before, $this->snapshot());
    }
}
