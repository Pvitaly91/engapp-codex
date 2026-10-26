<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\TextBlock;
use App\Support\M26DetailPackage as Digest;
use App\Support\M94RelativeClausesWhoseWhich as Package;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Actual framework pivot hydration on unsaved models; no fixture SQL or HTTP. */
class M94StandalonePivotBoundaryTest extends TestCase
{
    private function release(): array
    {
        $release = Package::load(dirname(__DIR__, 2));
        Package::validate($release[0], $release[1], $release[2], $release[3]);
        return $release;
    }

    private function block(array $target, ?int $slot): TextBlock
    {
        $b = new TextBlock;
        if ($slot === null) {
            $row = ['id' => Package::SUBTITLE_ID, 'uuid' => Package::SUBTITLE_UUID, 'sort_order' => 0,
                'type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
                'level' => null, 'body' => $target['after']['page']['subtitle_html']];
        } else {
            $source = $target['after']['page']['blocks'][$slot];
            $row = ['id' => $slot < 2 ? Package::CONTINUING_IDS[$slot] : 8940000 + $slot,
                'uuid' => Digest::uuid(Package::OWNERS[0], $source, $slot + 1), 'sort_order' => $slot + 1,
                'type' => $source['type'], 'column' => $source['column'], 'heading' => $source['heading'] ?? null,
                'css_class' => $source['css_class'] ?? null, 'level' => $source['level'] ?? null, 'body' => $source['body']];
        }
        $b->setRawAttributes($row + ['seeder' => Package::OWNERS[0], 'locale' => 'uk', 'page_id' => 284,
            'page_category_id' => 34, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'], true);
        return $b;
    }

    /** Execute the real hydratePivotRelation -> migratePivotAttributes -> newExistingPivot chain. */
    private function hydrated(TextBlock $block): TextBlock
    {
        $parent = new Question;
        $parent->setRawAttributes(['uuid' => 'm94-isolated-pivot-question'], true);
        $b = clone $block;
        $b->setRawAttributes($b->getAttributes() + ['pivot_question_uuid' => $parent->uuid,
            'pivot_text_block_uuid' => $b->uuid, 'pivot_position' => 0], true);
        $relation = (new ReflectionClass(BelongsToMany::class))->newInstanceWithoutConstructor();
        foreach (['parent' => $parent, 'related' => new TextBlock, 'table' => 'question_theory_text_blocks',
            'foreignPivotKey' => 'question_uuid', 'relatedPivotKey' => 'text_block_uuid'] as $field => $value) {
            (new ReflectionProperty($relation, $field))->setValue($relation, $value);
        }
        (new ReflectionMethod($relation, 'hydratePivotRelation'))->invoke($relation, [$b]);
        self::assertSame(['pivot_question_uuid', 'pivot_text_block_uuid', 'pivot_position'],
            array_keys(array_diff_key($b->getRawOriginal(), $b->getAttributes())));
        self::assertInstanceOf(Pivot::class, $b->getRelation('pivot'));
        self::assertSame('question_theory_text_blocks', $b->getRelation('pivot')->getTable());
        return $b;
    }

    private function snapshot(TextBlock $block): array
    {
        $pivot = $block->relationLoaded('pivot') ? $block->getRelation('pivot') : null;
        return ['attributes' => $block->getAttributes(), 'raw_original' => $block->getRawOriginal(),
            'relations' => $block->getRelations(), 'pivot_object' => $pivot,
            'pivot_attributes' => $pivot instanceof Pivot ? $pivot->getAttributes() : null,
            'pivot_original' => $pivot instanceof Pivot ? $pivot->getRawOriginal() : null,
            'pivot_table' => $pivot instanceof Pivot ? $pivot->getTable() : null];
    }

    private function noQueries(callable $call): void
    {
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing', app()->environment());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertNotSame(realpath(base_path()), realpath(app()->environmentPath()));
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        try { $call(); self::assertSame([], DB::connection()->getQueryLog()); }
        finally { DB::connection()->disableQueryLog(); }
    }

    public function test_real_framework_pivot_hydration_restores_old_hero_literal_box_and_subtitle_only_on_clones(): void
    {
        [$before, $patch] = $this->release(); $target = $patch['targets'][0];
        $this->noQueries(function () use ($before, $target): void {
            foreach ([0, 1, null] as $slot) {
                $block = $this->hydrated($this->block($target, $slot)); $snapshot = $this->snapshot($block);
                $copy = Package::preserveExcludedTextBlock($block, 'standalone_test');
                self::assertInstanceOf(TextBlock::class, $copy); self::assertNotSame($block, $copy);
                $body = $slot === null ? $before['targets'][0]['before']['page']['subtitle_html']
                    : $before['targets'][0]['before']['page']['blocks'][$slot]['body'];
                self::assertSame($body, $copy->body);
                self::assertSame($snapshot, $this->snapshot($block), 'Original block and loaded pivot must not mutate.');
                self::assertSame($block->getRelation('pivot'), $copy->getRelation('pivot'));
                self::assertSame($copy->getRawOriginal(), $copy->getAttributes());
                self::assertSame($block->id, $copy->id); self::assertSame($block->uuid, $copy->uuid);
                if ($slot === 1) { self::assertSame('box', $copy->type); self::assertStringStartsWith('<p>', $copy->body); }
                if ($slot === 0) {
                    $data = json_decode($target['after']['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
                    self::assertNull(Package::presentation($block, $data), 'Canonical theory still rejects a dirty raw/current tuple; no global relaxation.');
                }
            }
        });
    }

    public function test_real_hydrated_new_blocks_stay_excluded_and_clean_plain_behavior_is_unchanged(): void
    {
        [$before, $patch] = $this->release(); $target = $patch['targets'][0];
        $this->noQueries(function () use ($before, $target): void {
            foreach (range(2, 7) as $slot) {
                $b = $this->hydrated($this->block($target, $slot)); $snapshot = $this->snapshot($b);
                self::assertNull(Package::preserveExcludedTextBlock($b, 'standalone_test'));
                self::assertSame($snapshot, $this->snapshot($b));
            }
            foreach ([0, 1] as $slot) {
                $plain = $this->block($target, $slot); $snapshot = $this->snapshot($plain);
                $copy = Package::preserveExcludedTextBlock($plain, 'standalone_test');
                self::assertSame($before['targets'][0]['before']['page']['blocks'][$slot]['body'], $copy->body);
                self::assertSame($snapshot, $this->snapshot($plain));
            }
        });
    }

    public function test_wrong_callers_do_not_normalize_or_mutate_loaded_models_and_never_query(): void
    {
        [, $patch] = $this->release(); $target = $patch['targets'][0];
        $this->noQueries(function () use ($target): void {
            $block = $this->hydrated($this->block($target, 0)); $snapshot = $this->snapshot($block);
            foreach (['', 'theory', 'course', 'standalone', 'standalone_test '] as $caller) {
                self::assertSame($block, Package::preserveExcludedTextBlock($block, $caller));
                self::assertSame($snapshot, $this->snapshot($block));
            }
        });
    }

    public function test_missing_wrong_dirty_or_extra_pivot_state_fails_closed_without_a_broad_original_sync(): void
    {
        [, $patch] = $this->release(); $target = $patch['targets'][0];
        $this->noQueries(function () use ($target): void {
            $mutations = [
                static fn ($b) => $b->unsetRelation('pivot'),
                static fn ($b) => $b->setRelation('pivot', new \stdClass),
                static fn ($b) => $b->getRelation('pivot')->setTable('foreign_pivot'),
                static function ($b) { $p = $b->getRelation('pivot'); $a = $p->getAttributes(); unset($a['position']); $p->setRawAttributes($a, true); },
                static function ($b) { $p = $b->getRelation('pivot'); $p->setRawAttributes($p->getAttributes() + ['id' => 1], true); },
                static function ($b) { $b->getRelation('pivot')->position = 1; },
                static function ($b) { $p = $b->getRelation('pivot'); $a = $p->getAttributes(); $a['question_uuid'] = ''; $p->setRawAttributes($a, true); },
                static function ($b) { $p = $b->getRelation('pivot'); $a = $p->getAttributes(); $a['text_block_uuid'] = 'foreign'; $p->setRawAttributes($a, true); },
                static function ($b) { $p = $b->getRelation('pivot'); $a = $p->getAttributes(); $a['position'] = '0'; $p->setRawAttributes($a, true); },
                static function ($b) { $p = $b->getRelation('pivot'); $a = $p->getAttributes(); $a['position'] = -1; $p->setRawAttributes($a, true); },
                static function ($b) { $b->body .= ' changed'; },
                static function ($b) { $a = $b->getRawOriginal(); $a['pivot_position'] = 7; $b->setRawAttributes($a, true); foreach (['question_uuid','text_block_uuid','position'] as $key) { unset($b->{'pivot_'.$key}); } },
                static function ($b) { $a = $b->getRawOriginal(); $a['pivot_unlisted'] = 'extra'; $b->setRawAttributes($a, true); foreach (['question_uuid','text_block_uuid','position','unlisted'] as $key) { unset($b->{'pivot_'.$key}); } },
                static function ($b) { $b->pivot_position = 0; },
            ];
            foreach ($mutations as $mutate) {
                $block = $this->hydrated($this->block($target, 0)); $mutate($block); $snapshot = $this->snapshot($block);
                self::assertSame($block, Package::preserveExcludedTextBlock($block, 'standalone_test'));
                self::assertSame($snapshot, $this->snapshot($block));
            }
            foreach (['id' => 99, 'uuid' => 'foreign', 'page_id' => 999, 'page_category_id' => 999,
                'seeder' => 'ForeignOwner', 'locale' => 'en', 'type' => 'box', 'column' => 'left',
                'heading' => 'foreign', 'level' => 'C2', 'css_class' => 'foreign', 'sort_order' => 99] as $field => $value) {
                $block = $this->hydrated($this->block($target, 0));
                $a = array_replace($block->getRawOriginal(), [$field => $value]); $block->setRawAttributes($a, true);
                foreach (['question_uuid','text_block_uuid','position'] as $key) { unset($block->{'pivot_'.$key}); }
                $snapshot = $this->snapshot($block);
                self::assertSame($block, Package::preserveExcludedTextBlock($block, 'standalone_test'), $field);
                self::assertSame($snapshot, $this->snapshot($block), $field);
            }
        });
    }
}
