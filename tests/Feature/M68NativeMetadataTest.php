<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M68ComparisonBasics as Package;
use App\Support\TheoryNativeContentRelease;
use Tests\TestCase;

/** Native metadata and one finite neutral checklist; no new visual type or SQL fixture. */
class M68NativeMetadataTest extends TestCase
{
    public function test_all_thirty_two_continuing_slots_retain_literal_metadata_and_absent_level_keys(): void
    {
        [$before, $patch, $master] = Package::load();
        $release = new TheoryNativeContentRelease(Package::profile()); $slots = 0;
        self::assertSame([9, 11, 12], Package::CONTINUING_COUNTS);
        foreach ($before['targets'] as $i => $target) {
            $old = $target['before']; self::assertSame($old, $release->projectedBefore($old));
            foreach ($old['page']['blocks'] as $n => $source) {
                ++$slots; $after = $patch['targets'][$i]['after']['page']['blocks'][$n];
                self::assertSame(Package::CONTINUING_TYPES[$i][$n], $source['type']);
                self::assertSame(Package::CONTINUING_LEVELS[$i][$n], $source['level'] ?? null);
                self::assertSame(array_key_exists('level', $source), array_key_exists('level', $after));
                self::assertSame($source['level'] ?? null, $after['level'] ?? null);
                self::assertSame($source['column'], $after['column']);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $n + 1),
                    M26DetailPackage::uuid($target['identity'], $after, $n + 1));
                if ($i === 0) { self::assertArrayNotHasKey('level', $source); self::assertArrayNotHasKey('level', $after); }
                unset($source['body'], $after['body']); self::assertSame($source, $after, 'No covert technical metadata transition.');
            }
            self::assertSame(Package::LEVELS[$i], $master['lessons'][$i]['blocks'][Package::CONTINUING_COUNTS[$i]]['level']);
        }
        self::assertSame(32, $slots);
    }

    public function test_equality_legacy_config_seeder_is_preserved_but_never_becomes_the_top_level_owner(): void
    {
        [$before, $patch] = Package::load(); $old = $before['targets'][2]['before'];
        $legacy = 'Database\\Seeders\\Page_v2\\Adjectives\\AdjectivesEqualityComparisonAsAsNotAsAsTheorySeeder';
        self::assertSame(Package::OWNERS[2], $old['seeder']['class']);
        self::assertSame(Package::OWNERS[2], $patch['targets'][2]['identity']);
        foreach ($old['page']['blocks'] as $n => $source) {
            self::assertSame($legacy, $source['seeder']);
            self::assertSame($legacy, $patch['targets'][2]['after']['page']['blocks'][$n]['seeder']);
        }
        $bad = $old; $bad['seeder']['class'] = $legacy;
        $this->expectException(\RuntimeException::class);
        (new TheoryNativeContentRelease(Package::profile()))->projectedBefore($bad);
    }

    private function checklist(): array
    {
        $data = ['title' => 'Контрольні запитання', 'sections' => [
            ['label' => 'Назви еталон', 'description' => 'Що саме порівнюємо?', 'color' => 'blue',
                'examples' => [['en' => 'This parcel is heavier.', 'ua' => 'Ця посилка важча.']]],
        ]];
        $block = new TextBlock;
        $block->setRawAttributes(['id' => 680009, 'seeder' => Package::OWNERS[1], 'locale' => 'uk',
            'page_id' => 273, 'page_category_id' => 52, 'uuid' => Package::NEUTRAL_SLOT_UUID,
            'type' => 'mistakes-grid', 'column' => 'right', 'heading' => null, 'css_class' => null,
            'level' => 'A2', 'sort_order' => 9, 'body' => M26DetailPackage::json($data)], true);
        return [$block, $data];
    }

    public function test_exact_neutral_checklist_changes_only_rendered_component_without_model_mutation(): void
    {
        [$block, $data] = $this->checklist(); $attrs = $block->getAttributes(); $copy = $data;
        self::assertSame('usage-panels', Package::semanticComponent($block, $data, null));
        self::assertSame($attrs, $block->getAttributes()); self::assertSame([], $block->getDirty());
        self::assertSame($copy, $data); self::assertSame('mistakes-grid', $block->type);
        self::assertSame('usage-panels', Package::profile()['bindings'][1]['component_overrides'][8]);
        foreach ([0, 2] as $i) { self::assertSame([], Package::profile()['bindings'][$i]['component_overrides']); }
    }

    public function test_foreign_or_correction_payload_cannot_receive_the_neutral_component_override(): void
    {
        [$block, $data] = $this->checklist();
        foreach (['seeder' => Package::OWNERS[0], 'locale' => 'en', 'page_id' => 274, 'page_category_id' => 1,
            'uuid' => 'foreign', 'type' => 'usage-panels', 'column' => 'left', 'heading' => 'manual heading',
            'css_class' => 'foreign', 'level' => 'B1', 'sort_order' => 8, 'body' => '{}'] as $field => $value) {
            $foreign = clone $block; $foreign->$field = $value; $attrs = $foreign->getAttributes();
            self::assertNull(Package::semanticComponent($foreign, $data, null), $field);
            self::assertSame($attrs, $foreign->getAttributes());
        }
        foreach (['items', 'wrong', 'right', 'wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'empty-label', 'foreign-accent'] as $case) {
            $bad = $data;
            if ($case === 'items') { $bad['items'] = []; }
            elseif ($case === 'empty-label') { $bad['sections'][0]['label'] = ''; }
            elseif ($case === 'foreign-accent') { $bad['sections'][0]['color'] = 'purple'; }
            else { $bad['sections'][0]['note'] = [$case => 'Literal correction cannot become a neutral checklist.']; }
            $foreign = clone $block; $foreign->body = M26DetailPackage::json($bad);
            self::assertNull(Package::semanticComponent($foreign, $bad, null), $case);
        }
    }
}
