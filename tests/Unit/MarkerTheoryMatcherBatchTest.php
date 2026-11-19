<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\MarkerTheoryMatcherService;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

final class MarkerTheoryMatcherBatchTest extends TestCase
{
    private const TAG_CYRILLIC = ['id' => 7, 'name' => 'Українська: прийменники', 'category' => null];

    private const TAG_EMPTY_CATEGORY = ['id' => 11, 'name' => '空の分類', 'category' => ''];

    private const TAG_CATEGORY = ['id' => 30, 'name' => 'L’ordre — grüßen', 'category' => 'граматика'];

    // Independent expectations for the scalar read in the PERF-05 baseline.
    // Deliberately not built from a batch result, and neither map is sorted in the assertions.
    private const BEFORE = [
        1 => [
            'A1' => [self::TAG_EMPTY_CATEGORY],
            'a1' => [self::TAG_CYRILLIC, self::TAG_CATEGORY, self::TAG_CATEGORY],
            'z9' => [self::TAG_EMPTY_CATEGORY],
            'м1' => [self::TAG_CATEGORY],
        ],
        2 => [
            'a1' => [self::TAG_EMPTY_CATEGORY, self::TAG_CATEGORY],
            'b2' => [self::TAG_CATEGORY],
        ],
        3 => [],
        4 => [
            0 => [self::TAG_CYRILLIC],
            1 => [self::TAG_CATEGORY],
        ],
        5 => [],
        54 => ['a1' => [self::TAG_EMPTY_CATEGORY]],
        500 => ['m' => [self::TAG_CYRILLIC, self::TAG_CATEGORY]],
        501 => ['m' => [self::TAG_CATEGORY]],
        502 => ['outside' => [self::TAG_CYRILLIC]],
        99999 => [],
    ];

    private MarkerTheoryMatcherService $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->matcher = $this->app->make(MarkerTheoryMatcherService::class);
    }

    public function test_scalar_baseline_matches_independent_ordered_and_typed_oracle(): void
    {
        $this->seedFixture(permissivePivot: true);

        foreach (self::BEFORE as $id => $expected) {
            $this->assertSame($expected, $this->matcher->getAllMarkerTags($id), "Scalar question {$id}");
        }
        // A marker that PHP treats as an integer key must keep the old JSON container shape.
        $this->assertStringStartsWith('[[', $this->encodeMarkerMap($this->matcher->getAllMarkerTags(4)));
        $this->assertStringStartsWith('{"A1":', $this->encodeMarkerMap($this->matcher->getAllMarkerTags(1)));
    }

    public function test_batch_preserves_one_and_multiple_scalar_maps_and_json_without_reordering(): void
    {
        $this->seedFixture(permissivePivot: true);

        foreach ([[1], [2, 1, 2, 3, 1, 4, 5, 99999]] as $ids) {
            $batch = $this->matcher->getAllMarkerTagsForQuestions($ids);
            $this->assertSame([], array_diff(array_keys($batch), $ids));
            foreach ($ids as $id) {
                $before = $this->matcher->getAllMarkerTags($id);
                $after = $batch[$id] ?? [];

                $this->assertSame(self::BEFORE[$id], $before);
                $this->assertSame($before, $after, "Ordered marker map for question {$id}");
                $this->assertSame($this->encodeMarkerMap($before), $this->encodeMarkerMap($after), "JSON representation for question {$id}");
            }
        }
    }

    public function test_empty_ids_avoid_schema_and_data_queries(): void
    {
        [$batch, $queries] = $this->withQueryLog(fn () => $this->matcher->getAllMarkerTagsForQuestions([]));

        $this->assertSame([], $batch);
        $this->assertSame([], $queries);
    }

    public function test_typical_dataset_reads_schema_and_join_once_instead_of_once_per_question(): void
    {
        $this->seedFixture();
        $ids = range(1, 54);

        [$before, $scalarQueries] = $this->withQueryLog(fn () => array_map($this->matcher->getAllMarkerTags(...), $ids));
        [$batch, $batchQueries] = $this->withQueryLog(fn () => $this->matcher->getAllMarkerTagsForQuestions($ids));

        $this->assertQueryCounts($scalarQueries, 54, 54);
        $this->assertQueryCounts($batchQueries, 1, 1);
        $this->assertSame($before, array_map(static fn (int $id) => $batch[$id] ?? [], $ids));
        $expectedFirst = self::BEFORE[1];
        $expectedFirst['a1'] = [self::TAG_CYRILLIC, self::TAG_CATEGORY]; // The production unique key excludes a duplicate.
        $this->assertSame($expectedFirst, $batch[1]);
        $this->assertArrayNotHasKey(500, $batch, 'Rows outside the requested question IDs must not leak into the map.');
    }

    public function test_large_dataset_chunks_unique_ids_and_keeps_tags_at_chunk_boundaries(): void
    {
        $this->seedFixture();
        $ids = [...range(1, 501), 1, 500, 501];
        $before = array_map($this->matcher->getAllMarkerTags(...), $ids);

        [$batch, $queries] = $this->withQueryLog(fn () => $this->matcher->getAllMarkerTagsForQuestions($ids));

        $this->assertQueryCounts($queries, 1, 2);
        $this->assertSame($before, array_map(static fn (int $id) => $batch[$id] ?? [], $ids));
        $this->assertSame(self::BEFORE[500], $batch[500]);
        $this->assertSame(self::BEFORE[501], $batch[501]);
        $this->assertArrayNotHasKey(502, $batch);
    }

    public function test_missing_pivot_falls_back_without_caching_the_schema_result(): void
    {
        [$missing, $firstQueries] = $this->withQueryLog(fn () => $this->matcher->getAllMarkerTagsForQuestions([1, 2, 3]));

        $this->assertSame($this->matcher->getAllMarkerTags(1), $missing[1] ?? []);
        $this->assertSame([], $missing[2] ?? []);
        $this->assertSame([], $missing[3] ?? []);
        $this->assertQueryCounts($firstQueries, 1, 0);

        $this->seedFixture(permissivePivot: true);
        [$present, $secondQueries] = $this->withQueryLog(fn () => $this->matcher->getAllMarkerTagsForQuestions([1]));
        $this->assertSame(self::BEFORE[1], $present[1] ?? []);
        $this->assertQueryCounts($secondQueries, 1, 1);

        Schema::drop('question_marker_tag');
        [$missingAgain, $thirdQueries] = $this->withQueryLog(fn () => $this->matcher->getAllMarkerTagsForQuestions([1]));
        $this->assertSame([], $missingAgain[1] ?? []);
        $this->assertQueryCounts($thirdQueries, 1, 0);
    }

    public function test_missing_tags_table_or_failed_join_is_not_silently_treated_as_empty(): void
    {
        $this->seedFixture(permissivePivot: true);
        Schema::drop('tags');
        $this->assertBothReadsFail('no such table: tags');

        Schema::create('tags', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            // The category is deliberately missing so this exercises a different DB error.
        });
        $this->assertBothReadsFail('no such column: tags.category');
    }

    private function seedFixture(bool $permissivePivot = false): void
    {
        Schema::create('questions', static fn (Blueprint $table) => $table->id());
        Schema::create('tags', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
        });
        Schema::create('question_marker_tag', static function (Blueprint $table) use ($permissivePivot): void {
            $table->id();
            if ($permissivePivot) {
                $table->unsignedBigInteger('question_id');
            } else {
                $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            }
            $table->string('marker', 10);
            if ($permissivePivot) {
                // Preserve lookup key shape while permitting duplicate and orphan rows for compatibility tests.
                $table->unsignedBigInteger('tag_id');
                $table->index(['question_id', 'marker', 'tag_id']);
            } else {
                $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
                $table->unique(['question_id', 'marker', 'tag_id']);
            }
            $table->timestamps();
            $table->index(['question_id', 'marker']);
        });

        foreach (array_chunk(range(1, 502), 200) as $chunk) {
            DB::table('questions')->insert(array_map(static fn (int $id) => ['id' => $id], $chunk));
        }
        DB::table('tags')->insert([self::TAG_CATEGORY, self::TAG_EMPTY_CATEGORY, self::TAG_CYRILLIC]);
        $rows = [
            [1, 'z9', 11], [2, 'b2', 30], [1, 'a1', 30], [1, 'м1', 30], [1, 'A1', 11],
            [2, 'a1', 30], [1, 'a1', 7], [2, 'a1', 11], [4, '1', 30], [4, '0', 7], [54, 'a1', 11],
            [501, 'm', 30], [500, 'm', 30], [502, 'outside', 7], [500, 'm', 7],
        ];
        if ($permissivePivot) {
            array_push($rows, [1, 'a1', 30], [1, 'orphan', 999], [5, 'orphan', 999]);
        }
        DB::table('question_marker_tag')->insert(array_map(static fn (array $row) => [
            'question_id' => $row[0],
            'marker' => $row[1],
            'tag_id' => $row[2],
        ], $rows));
    }

    private function withQueryLog(callable $operation): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        try {
            $result = $operation();

            return [$result, array_column(DB::getQueryLog(), 'query')];
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }

    private function assertQueryCounts(array $queries, int $schema, int $tags): void
    {
        $this->assertCount($schema + $tags, $queries, 'Only schema inspection and tag reads are expected.');
        $this->assertCount($schema, array_filter($queries, static fn (string $sql) => str_contains($sql, 'sqlite_master')
            && str_contains($sql, 'question_marker_tag')));
        $this->assertCount($tags, array_filter($queries, static fn (string $sql) => str_contains($sql, 'from "question_marker_tag"')
            && str_contains($sql, 'inner join "tags"')));
    }

    private function assertBothReadsFail(string $message): void
    {
        foreach ([
            fn () => $this->matcher->getAllMarkerTags(1),
            fn () => $this->matcher->getAllMarkerTagsForQuestions([1]),
        ] as $operation) {
            try {
                $operation();
                $this->fail('A failed read must propagate the database exception.');
            } catch (QueryException $error) {
                $this->assertStringContainsString($message, $error->getMessage());
            }
        }
    }

    private function encodeMarkerMap(array $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
