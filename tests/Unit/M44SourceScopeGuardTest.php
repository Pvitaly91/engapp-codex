<?php

namespace Tests\Unit;

use App\Services\M44ContentPatch as Patch;
use Illuminate\Database\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure source/restore negative fixtures: no Laravel kernel, host, real DB or protected-file access. */
class M44SourceScopeGuardTest extends TestCase
{
    private static function fixture(int $index): array
    {
        $identity = Patch::NAMES[$index];
        $before = ['schema_version' => 1, 'type' => 'theory', 'slug' => 'fixture-'.$index,
            'page' => ['title' => 'Fixture '.$index, 'locale' => 'uk',
                'category' => ['slug' => Patch::CATEGORIES[$identity]],
                'tags' => ['fixture'], 'subtitle_html' => '<p>Old</p>', 'subtitle_text' => 'Old',
                'blocks' => array_fill(0, [9, 6, 7][$index], ['type' => 'summary-list', 'body' => '{}', 'level' => 'A2'])],
            'seeder' => ['class' => $identity]];
        $after = $before;
        $after['page']['subtitle_html'] = '<p>New</p>';
        $after['page']['subtitle_text'] = 'New';
        $after['page']['blocks'] = array_fill(0, [11, 8, 10][$index], ['type' => 'usage-panels', 'body' => '{"title":"new"}', 'level' => 'A2']);
        $target = ['identity' => $identity, 'ancestry' => Patch::ANCESTRIES[$identity]];
        return [$before, $after, $target, $index];
    }

    public function test_exact_future_owners_and_ancestries_accept_only_their_projected_deltas(): void
    {
        foreach (range(0, 2) as $index) {
            Patch::assertDefinitionScope(...self::fixture($index));
        }
        self::assertSame(['future-simple', 'maibutni-formy', 'maibutni-formy'], array_values(Patch::CATEGORIES));
        self::assertSame(['maibutni-formy', 'future-simple'], Patch::ANCESTRIES[Patch::NAMES[0]]);
        self::assertSame('d3c5fa811e3e7eb5de2e348a6cd2d0bed3a76ba0', Patch::ACCEPTED_BASE);
    }

    public static function invalidDefinitions(): array
    {
        $cases = [];
        foreach (range(0, 2) as $index) {
            foreach (['before_owner', 'target_owner', 'locale', 'type', 'category', 'ancestry', 'old_count', 'new_count',
                'title', 'slug', 'tags', 'base_locale', 'base_category'] as $mutation) {
                [$before, $after, $target, $i] = self::fixture($index);
                match ($mutation) {
                    'before_owner' => $before['seeder']['class'] = 'Database\\Seeders\\ForeignSeeder',
                    'target_owner' => $target['identity'] = 'Database\\Seeders\\ForeignSeeder',
                    'locale' => $before['page']['locale'] = 'en',
                    'type' => $before['type'] = 'course',
                    'category' => $before['page']['category']['slug'] = 'tenses',
                    'ancestry' => $target['ancestry'] = ['future-simple'],
                    'old_count' => $before['page']['blocks'][] = ['type' => 'hero', 'body' => '{}'],
                    'new_count' => $after['page']['blocks'][] = ['type' => 'practice-set', 'body' => '{}'],
                    'title' => $after['page']['title'] = 'Changed title',
                    'slug' => $after['slug'] = 'changed-slug',
                    'tags' => $after['page']['tags'][] = 'unreviewed-tag',
                    'base_locale' => $after['page']['locale'] = 'pl',
                    'base_category' => $after['page']['category']['slug'] = 'tenses',
                };
                $cases[$index.'-'.$mutation] = [$before, $after, $target, $i];
            }
        }
        $cases['unknown-owner-index'] = [...self::fixture(0)];
        $cases['unknown-owner-index'][3] = 3;
        return $cases;
    }

    #[DataProvider('invalidDefinitions')]
    public function test_foreign_owner_category_partial_projection_and_protected_metadata_are_rejected(array $before, array $after, array $target, int $index): void
    {
        $this->expectException(RuntimeException::class);
        Patch::assertDefinitionScope($before, $after, $target, $index);
    }

    public function test_restore_is_not_an_authorized_m44_operation_and_does_not_query_or_write(): void
    {
        $db = \Mockery::mock(Connection::class);
        $db->shouldNotReceive('getDriverName');
        $db->shouldNotReceive('selectOne');
        $db->shouldNotReceive('transaction');
        $patch = new Patch($db, 'unused-database-path', 'unused-private-path');
        $this->expectException(RuntimeException::class);
        $patch->restore('unused-backup.json', 'gr2');
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
