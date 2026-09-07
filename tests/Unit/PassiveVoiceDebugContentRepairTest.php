<?php

namespace Tests\Unit;

use App\Services\PassiveVoiceDebugContentRepair;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PassiveVoiceDebugContentRepairTest extends TestCase
{
    private Connection $db;
    private PassiveVoiceDebugContentRepair $repair;
    private array $backups = [];

    protected function setUp(): void
    {
        parent::setUp();
        // No application bootstrap, .env, migrations, RefreshDatabase or working database.
        $container = new Container;
        $container->instance('config', new Repository([
            'app' => ['env' => 'testing'], 'cache' => ['default' => 'array'],
            'session' => ['driver' => 'array'],
        ]));
        $capsule = new Manager($container);
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $this->db = $capsule->getConnection();
        self::assertSame('testing', $container['config']['app.env']);
        self::assertSame('array', $container['config']['cache.default']);
        self::assertSame('array', $container['config']['session.driver']);
        self::assertSame('sqlite', $this->db->getDriverName());
        self::assertSame(':memory:', $this->db->getDatabaseName());
        $schema = $this->db->getSchemaBuilder();
        $schema->create('page_categories', function (Blueprint $table) {
            $table->id(); $table->string('slug');
        });
        $schema->create('pages', function (Blueprint $table) {
            $table->id(); $table->string('slug'); $table->string('title'); $table->text('text');
            $table->string('type'); $table->string('seeder'); $table->unsignedBigInteger('page_category_id');
        });
        $schema->create('text_blocks', function (Blueprint $table) {
            $table->id(); $table->uuid('uuid')->unique(); $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('page_category_id')->default(1); $table->string('level')->nullable();
            $table->string('locale'); $table->string('type'); $table->string('column');
            $table->integer('sort_order'); $table->string('seeder'); $table->text('body');
            $table->string('heading')->nullable(); $table->string('css_class')->nullable();
        });
        $schema->create('tag_text_block', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tag_id'); $table->unsignedBigInteger('text_block_id');
        });
        $schema->create('questions', function (Blueprint $table) {
            $table->id(); $table->uuid('uuid'); $table->text('question');
            $table->uuid('theory_text_block_uuid')->nullable();
        });
        $schema->create('question_theory_text_blocks', function (Blueprint $table) {
            $table->id(); $table->uuid('question_uuid'); $table->uuid('text_block_uuid');
        });
        $this->repair = new PassiveVoiceDebugContentRepair($this->db,
            dirname(__DIR__, 2).'/database/'.PassiveVoiceDebugContentRepair::DEFINITION);
    }

    protected function tearDown(): void
    {
        foreach ($this->backups as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->db->disconnect();
        parent::tearDown();
    }

    public function test_dry_run_identifies_only_exact_fixture_rows_and_does_not_write(): void
    {
        $this->fixture();
        $before = $this->snapshot();
        $plan = $this->repair->plan();
        self::assertSame('repairable', $plan['status']);
        self::assertSame('Formation Rules — Правила утворення пасиву', $plan['canonical_title']);
        self::assertSame([4, 5, 6], array_column($plan['text_blocks'], 'id'));
        self::assertCount(7, $plan['tag_links']);
        self::assertSame($before, $this->snapshot());
    }

    public function test_apply_keeps_learning_content_and_links_and_is_idempotent_and_reversible(): void
    {
        $this->fixture();
        $before = $this->snapshot();
        $backup = $this->backupPath();
        self::assertTrue($this->repair->apply($backup)['applied']);
        self::assertFileExists($backup);
        $saved = json_decode(file_get_contents($backup), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(3, $saved['text_blocks']);
        self::assertCount(7, $saved['tag_links']);
        self::assertSame('Formation Rules — Правила утворення пасиву', $this->db->table('pages')->value('title'));
        self::assertSame([52], $this->db->table('text_blocks')->pluck('id')->all());
        self::assertSame([99], $this->db->table('tag_text_block')->pluck('id')->all());
        self::assertSame($before['questions'], $this->snapshot()['questions']);
        $second = $this->backupPath();
        self::assertFalse($this->repair->apply($second)['applied']);
        self::assertFileDoesNotExist($second);
        $after = $this->snapshot();
        self::assertSame(3, $this->repair->restore($backup)['text_blocks']);
        self::assertSame($after, $this->snapshot());
        self::assertTrue($this->repair->restore($backup, true)['restored']);
        self::assertSame($before, $this->snapshot());
        self::assertSame(0, $this->repair->restore($backup, true)['text_blocks']);
    }

    public function test_correct_local_title_is_not_rewritten_while_leftover_blocks_are_removed(): void
    {
        $this->fixture();
        $this->db->table('pages')->update(['title' => 'Formation Rules — Правила утворення пасиву', 'seeder' => PassiveVoiceDebugContentRepair::SEEDER]);
        $page = (array) $this->db->table('pages')->first();
        self::assertSame([], $this->repair->plan()['page_changes']);
        $this->repair->apply($this->backupPath());
        self::assertSame($page, (array) $this->db->table('pages')->first());
    }

    public function test_manually_changed_block_aborts_all_changes_without_creating_a_backup(): void
    {
        $this->fixture();
        $this->db->table('text_blocks')->where('id', 5)->update(['body' => '<p>Manually edited learning content.</p>']);
        $before = $this->snapshot();
        self::assertSame('conflict', $this->repair->plan()['status']);
        $this->assertRepairRefused($before, $this->backupPath());
    }

    public function test_manually_changed_title_is_never_overwritten(): void
    {
        $this->fixture();
        $this->db->table('pages')->update(['title' => 'Авторський заголовок']);
        $this->assertRepairRefused($this->snapshot(), $this->backupPath());
    }

    public function test_manually_changed_block_level_or_category_prevents_deletion(): void
    {
        $this->fixture();
        $this->db->table('text_blocks')->where('id', 5)->update(['level' => 'B1']);
        $this->assertRepairRefused($this->snapshot(), $this->backupPath());
        $this->db->table('text_blocks')->where('id', 5)->update(['level' => null, 'page_category_id' => 42]);
        $this->assertRepairRefused($this->snapshot(), $this->backupPath());
    }

    public function test_question_references_prevent_removal_of_required_uuids(): void
    {
        $this->fixture();
        $this->db->table('questions')->update(['theory_text_block_uuid' => 'fe0884c8-c21a-55c9-aaf8-1786d7164550']);
        $this->assertRepairRefused($this->snapshot(), $this->backupPath());
    }

    public function test_existing_backup_cannot_be_overwritten_and_database_stays_unchanged(): void
    {
        $this->fixture();
        $path = $this->backupPath();
        file_put_contents($path, 'Existing backup');
        $before = $this->snapshot();
        try {
            $this->repair->apply($path);
            self::fail('Existing backup must stop the repair.');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('backup', $e->getMessage());
        }
        self::assertSame('Existing backup', file_get_contents($path));
        self::assertSame($before, $this->snapshot());
    }

    public function test_question_theory_junction_references_are_preserved_and_prevent_repair(): void
    {
        $this->fixture();
        $this->db->table('question_theory_text_blocks')->insert(['question_uuid' => 'a2788cd5-0316-4f41-9d3b-47859b750b8f', 'text_block_uuid' => '625d8416-a3ff-51ee-8219-1ae6901c3a21']);
        $this->assertRepairRefused($this->snapshot(), $this->backupPath());
        self::assertSame(1, $this->db->table('question_theory_text_blocks')->count());
    }

    public function test_unknown_foreign_key_dependents_are_not_cascaded_or_orphaned(): void
    {
        $this->fixture();
        $this->db->getSchemaBuilder()->create('extra_block_references', function (Blueprint $table) {
            $table->id(); $table->foreignId('block_id')->constrained('text_blocks')->cascadeOnDelete();
        });
        $this->db->table('extra_block_references')->insert(['block_id' => 5]);
        $this->assertRepairRefused($this->snapshot(), $this->backupPath());
        self::assertSame(1, $this->db->table('extra_block_references')->count());
    }

    public function test_restore_refuses_new_manual_edits(): void
    {
        $this->fixture();
        $path = $this->backupPath();
        $this->repair->apply($path);
        $this->db->table('pages')->update(['title' => 'New manual title']);
        $before = $this->snapshot();
        try {
            $this->repair->restore($path, true);
            self::fail('Restoration must preserve manual edits.');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('edited', $e->getMessage());
        }
        self::assertSame($before, $this->snapshot());
    }

    public function test_remote_database_is_rejected_before_connection_or_query(): void
    {
        $connection = new MySqlConnection(fn () => throw new RuntimeException('A network connection must never be attempted.'), 'gr3', '', ['driver' => 'mysql', 'host' => '192.0.2.1']);
        $repair = new PassiveVoiceDebugContentRepair($connection, 'unused');
        $this->expectExceptionMessage('single local MySQL connection');
        $repair->plan();
    }

    public function test_local_mysql_writes_require_the_exact_inspected_database_name(): void
    {
        $connection = new MySqlConnection(fn () => throw new RuntimeException('A network connection must never be attempted.'), 'gr2', '', ['driver' => 'mysql', 'host' => 'localhost']);
        $repair = new PassiveVoiceDebugContentRepair($connection, 'unused');
        $this->expectExceptionMessage('exact --database');
        $repair->apply($this->backupPath(), 'another-db');
    }

    public function test_a_diagnostic_only_record_is_not_promoted_to_an_empty_public_lesson(): void
    {
        $this->fixture();
        $this->db->table('text_blocks')->where('id', 52)->delete();
        self::assertStringContainsString('No canonical lesson blocks', implode(' ', $this->repair->plan()['conflicts']));
        $this->assertRepairRefused($this->snapshot(), $this->backupPath());
    }

    private function fixture(): void
    {
        $this->db->table('page_categories')->insert(['id' => 1, 'slug' => 'passive-voice']);
        $this->db->table('pages')->insert(['id' => 1, 'slug' => PassiveVoiceDebugContentRepair::SLUG,
            'title' => PassiveVoiceDebugContentRepair::DEBUG_TITLE, 'text' => 'Формула be + V3, active → passive, by-phrase і базова логіка пасиву.',
            'type' => 'theory', 'seeder' => PassiveVoiceDebugContentRepair::DEBUG_SEEDER, 'page_category_id' => 1]);
        $id = 4;
        foreach (PassiveVoiceDebugContentRepair::blockSignatures() as $uuid => $row) {
            $this->db->table('text_blocks')->insert($row + ['id' => $id++, 'uuid' => $uuid, 'page_id' => 1]);
        }
        $this->db->table('text_blocks')->insert(['id' => 52, 'uuid' => '913d089d-81a0-5ea9-8d0b-cfa92615daa6',
            'page_id' => 1, 'locale' => 'uk', 'type' => 'subtitle', 'column' => 'header', 'sort_order' => 0,
            'body' => '<p><strong>Пасивний стан</strong> — be + V3.</p>', 'seeder' => PassiveVoiceDebugContentRepair::SEEDER]);
        foreach ([[6, 3, 4], [7, 4, 4], [8, 3, 5], [9, 4, 5], [10, 5, 5], [11, 3, 6], [12, 5, 6], [99, 8, 52]] as [$id, $tag, $block]) {
            $this->db->table('tag_text_block')->insert(['id' => $id, 'tag_id' => $tag, 'text_block_id' => $block]);
        }
        $this->db->table('questions')->insert(['id' => 17, 'uuid' => 'a2788cd5-0316-4f41-9d3b-47859b750b8f',
            'question' => 'The cake {a1} every day.', 'theory_text_block_uuid' => '913d089d-81a0-5ea9-8d0b-cfa92615daa6']);
    }

    private function snapshot(): array
    {
        $snapshot = [];
        foreach (['pages', 'text_blocks', 'tag_text_block', 'questions', 'question_theory_text_blocks'] as $table) {
            $snapshot[$table] = $this->db->table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        }

        return $snapshot;
    }

    private function backupPath(): string
    {
        $path = sys_get_temp_dir().'/gramlyze-passive-repair-test-'.bin2hex(random_bytes(8)).'.json';
        $this->backups[] = $path;

        return $path;
    }

    private function assertRepairRefused(array $before, string $path): void
    {
        try {
            $this->repair->apply($path);
            self::fail('Expected repair to refuse unexpected data.');
        } catch (RuntimeException $e) {
            self::assertNotEmpty($e->getMessage());
        }
        self::assertFileDoesNotExist($path);
        self::assertSame($before, $this->snapshot());
    }
}
