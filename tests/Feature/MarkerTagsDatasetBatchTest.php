<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Test;
use App\Support\SentenceReorderQuestionFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class MarkerTagsDatasetBatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const SLUG = 'perf06-marker-tags-fixture';
    private const URL = '/test/'.self::SLUG;
    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    private const FIRST_STEM = 'First fixture {a1} next {a2} at the library.';
    private const FIRST_VARIANT = 'A fresh fixture {a1} next {a2} at the museum.';
    // Captured from the real scalar controller on 8e48c44f3, with the same
    // synthetic fixture. No sorting or normalization of payload keys or rows.
    private const BEFORE_GUEST_SHA = 'a11c46ac1ac334a238dcc46bf367f485b358cde320221e199b23452046e5526c';
    private const BEFORE_ADMIN_SHA = '677a3fe016867d728bba9d80603474646ca1d6f3adc5166254b12d9323dce3bb';

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->rebuildComposeTestSchema();
        config(['coming-soon.enabled' => false, 'tests.tech_info_enabled' => true]);

        Schema::table('verb_hints', static fn (Blueprint $table) => $table->string('locale', 8)->nullable());
        DB::table('categories')->insert(['id' => 1, 'name' => 'Fixture grammar']);
        DB::table('sources')->insert(['id' => 1, 'name' => 'Fixture source']);
        DB::table('tags')->insert([
            ['id' => 2, 'name' => 'Український тег', 'category' => null],
            ['id' => 5, 'name' => 'Another tag', 'category' => ''],
            ['id' => 9, 'name' => 'Shared tag', 'category' => 'Theory'],
            ['id' => 19, 'name' => 'Unselected tag', 'category' => 'Theory'],
        ]);
        DB::table('text_blocks')->insert([
            ['id' => 1, 'uuid' => 'p6-theory-legacy', 'locale' => 'uk', 'type' => 'box', 'body' => 'Legacy reference', 'level' => 'A1'],
            ['id' => 2, 'uuid' => 'p6-theory-second', 'locale' => 'uk', 'type' => 'example', 'body' => 'Second reference', 'level' => 'A2'],
            ['id' => 3, 'uuid' => 'p6-theory-first', 'locale' => 'uk', 'type' => 'box', 'body' => 'First reference', 'level' => 'A1'],
        ]);

        $options = [1 => 'cannot', 2 => 'work', 3 => 'They', 4 => 'read', 5 => 'увага', 6 => 'attention', 7 => 'could'];
        foreach ($options as $id => $value) {
            DB::table('question_options')->insert(['id' => $id, 'option' => $value]);
        }

        $ids = [];
        foreach (self::LEVELS as $index => $level) {
            $start = 100 + 10 * $index;
            foreach (range(0, 8) as $offset) {
                $id = $start + $offset;
                $ids[] = $id;
                $compose = $offset >= 6;
                DB::table('questions')->insert([
                    'id' => $id,
                    'uuid' => 'p6-question-'.$id,
                    'question' => $compose ? 'Твердження '.$id.'.'
                        : ($id === 100 ? self::FIRST_STEM : 'Fixture '.$id.' {a1} next {a2} at the library.'),
                    'category_id' => 1,
                    'source_id' => 1,
                    'difficulty' => $index + 1,
                    'level' => $level,
                    'type' => $compose ? Question::TYPE_COMPOSE_TOKENS : '0',
                    'options_by_marker' => json_encode($compose
                        ? ['a1' => ['They', 'We', 'You'], 'a2' => ['read', 'work', 'write']]
                        : ['a1' => ['could', 'cannot', 'must'], 'a2' => ['work', 'write', 'read']], JSON_THROW_ON_ERROR),
                    'theory_text_block_uuid' => $id === 101 ? 'p6-theory-legacy' : null,
                    'seeder' => 'Perf06FixtureSeeder',
                ]);
                DB::table('question_answers')->insert([
                    ['question_id' => $id, 'option_id' => $compose ? 3 : 1, 'marker' => 'a1'],
                    ['question_id' => $id, 'option_id' => $compose ? 4 : 2, 'marker' => 'a2'],
                ]);
                DB::table('question_option_question')->insert([
                    ['question_id' => $id, 'option_id' => 2, 'flag' => 0],
                    ['question_id' => $id, 'option_id' => 7, 'flag' => 0],
                ]);
            }
        }
        DB::table('questions')->insert(['id' => 999, 'uuid' => 'p6-unselected', 'question' => 'Do not load']);
        DB::table('question_marker_tag')->insert([
            ['question_id' => 100, 'tag_id' => 9, 'marker' => 'a1'],
            ['question_id' => 100, 'tag_id' => 5, 'marker' => 'a2'],
            ['question_id' => 100, 'tag_id' => 2, 'marker' => 'a1'],
            ['question_id' => 101, 'tag_id' => 9, 'marker' => 'a2'],
            ['question_id' => 106, 'tag_id' => 2, 'marker' => 'a1'],
            ['question_id' => 999, 'tag_id' => 19, 'marker' => 'a1'],
        ]);
        DB::table('question_tag')->insert(['question_id' => 100, 'tag_id' => 9]);
        DB::table('question_theory_text_blocks')->insert([
            ['question_uuid' => 'p6-question-100', 'text_block_uuid' => 'p6-theory-second', 'position' => 2],
            ['question_uuid' => 'p6-question-100', 'text_block_uuid' => 'p6-theory-first', 'position' => 1],
        ]);
        DB::table('verb_hints')->insert([
            ['question_id' => 100, 'marker' => 'a1', 'option_id' => 6, 'locale' => 'en'],
            ['question_id' => 100, 'marker' => 'a1', 'option_id' => 5, 'locale' => 'uk'],
        ]);
        DB::table('question_hints')->insert([
            'question_id' => 106, 'provider' => 'compose_prompt', 'locale' => 'uk', 'hint' => 'Складіть речення.',
        ]);
        DB::table('question_variants')->insert(['question_id' => 100, 'text' => self::FIRST_VARIANT]);
        Test::create([
            'name' => 'Synthetic mixed fixture',
            'slug' => self::SLUG,
            'questions' => $ids,
            'filters' => [
                'aggregated_theory_page_test' => true,
                'theory_page_mixed_all_levels' => true,
                'theory_page_mixed_type_profile' => SentenceReorderQuestionFactory::WORD_ORDER_PROFILE,
            ],
        ]);
    }

    public function test_fresh_guest_render_matches_scalar_payload_and_reopens_without_tag_queries(): void
    {
        $this->seedPreviousVariant();
        [$fresh, $reads, $checks] = $this->load();
        [$resumed, $resumeReads, $resumeChecks] = $this->load();
        $fresh->assertViewHas('showTechnicalInfo', false);
        $resumed->assertViewHas('showTechnicalInfo', false);
        $payload = $this->payload($fresh);
        $this->assertPayloadContract($payload);
        $this->assertTrue(collect($payload)->every(fn (array $item): bool => $item['tech_info'] === null));
        $this->assertSame(['guest' => self::BEFORE_GUEST_SHA, 'resumed' => self::BEFORE_GUEST_SHA],
            ['guest' => $this->digest($payload), 'resumed' => $this->digest($this->payload($resumed))]);
        $this->assertBatchRead($reads, $checks);
        $this->assertSame([], $resumeReads);
        $this->assertSame([], $resumeChecks);
    }

    public function test_fresh_admin_render_preserves_full_scalar_payload_and_technical_metadata(): void
    {
        $this->seedPreviousVariant();
        $this->withSession(['admin_authenticated' => true]);
        [$response, $reads, $checks] = $this->load();
        $response->assertViewHas('showTechnicalInfo', true);
        $payload = $this->payload($response);
        $this->assertPayloadContract($payload);
        $info = collect($payload)->firstWhere('id', 100)['tech_info'];
        $this->assertSame(['id' => 1, 'name' => 'Fixture source'], $info['source']);
        $this->assertSame(['Shared tag'], $info['tags']);
        $this->assertSame(1, $info['variants_count']);
        $this->assertSame(self::BEFORE_ADMIN_SHA, $this->digest($payload));
        $this->assertBatchRead($reads, $checks);
    }

    private function assertPayloadContract(array $payload): void
    {
        $this->assertCount(54, $payload);
        $this->assertSame(self::LEVELS, collect($payload)->pluck('level')->unique()->values()->all());
        $expectedKinds = array_merge(...array_fill(0, 18, ['gap', 'compose', 'reorder']));
        $this->assertSame($expectedKinds, array_map(static fn (array $item): string =>
            ($item['presentation'] ?? '') === SentenceReorderQuestionFactory::PRESENTATION ? 'reorder'
                : ($item['type'] === Question::TYPE_COMPOSE_TOKENS ? 'compose' : 'gap'), $payload));
        $first = collect($payload)->firstWhere('id', 100);
        $this->assertSame(self::FIRST_VARIANT, $first['question']);
        $this->assertSame('увага', $first['verb_hint']);
        $this->assertContains("can't", $first['accepted_answers_by_marker']['a1']);
        $this->assertSame(['p6-theory-first', 'p6-theory-second'], array_column($first['theory_blocks'], 'uuid'));
        $this->assertSame('p6-theory-legacy', collect($payload)->firstWhere('id', 101)['theory_block']['uuid']);
        $this->assertSame('Складіть речення.', collect($payload)->firstWhere('id', 106)['question']);
        $this->assertSame([], collect($payload)->firstWhere('id', 102)['marker_tags']);
        $this->assertSame([
            'a1' => [
                ['id' => 2, 'name' => 'Український тег', 'category' => null],
                ['id' => 9, 'name' => 'Shared tag', 'category' => 'Theory'],
            ],
            'a2' => [['id' => 5, 'name' => 'Another tag', 'category' => '']],
        ], $first['marker_tags']);
    }

    private function seedPreviousVariant(): void
    {
        // Exactly one other choice in the authored variants pool, so fresh
        // variant selection is exercised without replacing the randomizer.
        $this->withSession(['test_variants.'.self::SLUG.'.100' => self::FIRST_STEM]);
    }

    private function load(): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        try {
            $response = $this->get(self::URL);
            $queries = DB::getQueryLog();
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }

        $reads = [];
        $checks = [];
        foreach ($queries as $query) {
            $sql = strtolower($query['query']);
            if (str_contains($sql, 'from "question_marker_tag"') && str_contains($sql, 'join "tags"')) {
                $reads[] = $query;
            } elseif (str_contains($sql, 'sqlite_master') && (
                str_contains($sql, "'question_marker_tag'") || in_array('question_marker_tag', $query['bindings'], true)
            )) {
                $checks[] = $query;
            }
        }

        return [$response, $reads, $checks];
    }

    private function assertBatchRead(array $reads, array $checks): void
    {
        $this->assertCount(1, $reads, 'The actual saved-test controller must use one joined marker read.');
        $this->assertCount(1, $checks, 'The actual saved-test controller must check the pivot table once.');
        $expectedIds = [];
        foreach (range(0, 5) as $i) {
            array_push($expectedIds, ...range(100 + 10 * $i, 108 + 10 * $i));
        }
        $this->assertSame($expectedIds, $reads[0]['bindings'], 'Only the finalized dataset IDs may feed the marker query.');
    }

    private function payload(TestResponse $response): array
    {
        $response->assertOk();
        $this->assertSame(1, preg_match('/window\.__INITIAL_JS_TEST_QUESTIONS__\s*=\s*(\[[^\r\n]*\]);/', $response->getContent(), $match));
        $payload = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame($response->viewData('questionData'), $payload, 'Wire payload must retain the controller structure and types.');

        return $payload;
    }

    private function digest(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
