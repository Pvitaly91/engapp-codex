<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M43AuthoredTenseUsagePackage as Package;
use Illuminate\Support\Collection;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M43CoursePreservationTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function blocks(array $definition): Collection
    {
        $identity = $definition['seeder']['class']; $page = $definition['page'];
        $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $page['subtitle_html'],
            'level' => $page['subtitle_level'] ?? null, 'uuid_key' => $page['subtitle_uuid_key'] ?? 'subtitle'];
        $out = [];
        foreach ([$subtitle, ...$page['blocks']] as $position => $config) {
            $block = new TextBlock;
            $block->forceFill(['id' => 43000 + $position, 'page_id' => 430, 'page_category_id' => 431,
                'uuid' => M26DetailPackage::uuid($identity, $config, $position), 'seeder' => $identity,
                'locale' => 'uk', 'type' => $config['type'], 'sort_order' => $position,
                'body' => $config['body'], 'column' => $config['column'] ?? 'left',
                'heading' => $config['heading'] ?? null, 'css_class' => $config['css_class'] ?? null,
                'level' => $config['level'] ?? null, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00']);
            $block->setRelation('tags', collect()); $block->setRelation('page', null); $out[] = $block;
        }
        return collect($out);
    }

    private function courseHtml(array $definition, Collection $blocks): string
    {
        $page = new Page;
        $page->forceFill(['id' => 430, 'title' => $definition['page']['title'], 'text' => $definition['page']['subtitle_text'],
            'seeder' => $definition['seeder']['class'], 'type' => 'theory', 'slug' => $definition['slug']]);
        $page->setRelation('textBlocks', $blocks); $page->setRelation('tags', collect());
        return view('courses.partials.theory-page-content', compact('page'))->render();
    }

    public function test_all_three_course_partials_remain_byte_identical_to_frozen_before_without_mutating_models(): void
    {
        [$before, $package] = Package::load();
        foreach ($package['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $original = $this->blocks($old); $current = $this->blocks($target['after']);
            $snapshot = $current->map(fn ($b) => $b->getAttributes())->all();
            $preserved = Package::preserveCourseBlocks($current);
            self::assertSame($original->map(fn ($b) => $b->getAttributes())->all(), $preserved->map(fn ($b) => $b->getAttributes())->all());
            self::assertSame($snapshot, $current->map(fn ($b) => $b->getAttributes())->all());
            foreach ($preserved as $position => $block) { self::assertNotSame($current[$position], $block); }
            $expected = $this->courseHtml($old, $original); $actual = $this->courseHtml($target['after'], $current);
            self::assertSame($expected, $actual, $target['identity'].' course HTML changed');
            self::assertStringNotContainsString('data-m43-', $actual);
            self::assertSame($snapshot, $current->map(fn ($b) => $b->getAttributes())->all(), 'View must not mutate the controller relation');
            self::assertSame($preserved->pluck('uuid')->all(), Package::preserveCourseBlocks($current->reverse())->pluck('uuid')->all());
            $theoryOrder = Package::orderBlocks($current)->pluck('sort_order')->all();
            self::assertSame(array_merge([0, 1], array_map(fn ($slot) => $slot + 1, $target['section_slots']),
                [$target['practice_slot'] + 1, $target['navigation_slot'] + 1]), $theoryOrder);
        }
    }

    public function test_unknown_incomplete_or_tampered_course_collections_are_not_rewritten(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $target) {
            $rows = $this->blocks($target['after']);
            foreach (['locale' => 'en', 'seeder' => 'ForeignOwner', 'uuid' => 'unknown', 'type' => 'box',
                'sort_order' => 99, 'column' => 'footer', 'heading' => 'Foreign', 'level' => 'C2', 'body' => '{}'] as $field => $value) {
                $copy = $rows->map(fn ($row) => clone $row); $copy[1]->setAttribute($field, $value);
                self::assertSame($copy, Package::preserveCourseBlocks($copy), 'Must reject '.$field);
            }
            $missing = $rows->slice(1); self::assertSame($missing, Package::preserveCourseBlocks($missing));
            $duplicate = $rows->concat([$rows[0]]); self::assertSame($duplicate, Package::preserveCourseBlocks($duplicate));
            $empty = collect(); self::assertSame($empty, Package::preserveCourseBlocks($empty));
        }
    }
}
