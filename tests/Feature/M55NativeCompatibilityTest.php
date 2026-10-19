<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M54PassivePatternsPackage;
use App\Support\M55PassiveReviewInfinitivePackage;
use App\Support\TheoryLegacyAdapter;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Pure compatibility fixtures; no working release sources or databases change. */
class M55NativeCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk'); $this->withoutVite();
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
    }

    private function rejection(callable $operation, string $case): void
    {
        $error = null;
        try { $operation(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error, $case);
    }

    public function test_existing_frozen_release_keeps_default_title_guards_and_design_shape(): void
    {
        [$before, $patch, $master, $contract] = M54PassivePatternsPackage::load();
        $release = new TheoryNativeContentRelease(M54PassivePatternsPackage::profile());
        $release->validate($before, $patch, $master, $contract);
        $design = new ReflectionMethod($release, 'design');
        foreach ($patch['targets'] as $target) {
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                $expected = ['package' => 'm54-native-v1', 'identity' => $target['identity'], 'locale' => 'uk',
                    'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
                    'source_index' => $slot, 'body_sha256' => hash('sha256', $source['body']), 'component' => $source['type']];
                self::assertSame($expected, $design->invoke($release, compact('target', 'source', 'slot')));
            }
        }
        $categoryBefore = $before; $categoryBefore['targets'][0]['before']['content_type'] = 'category';
        $this->rejection(fn () => $release->validate($categoryBefore, $patch, $master, $contract), 'Page cannot become Category description');
        $before['targets'][0]['before']['page']['subtitle_html'] = '<p>Unregistered plain title.</p>';
        $this->rejection(fn () => $release->validate($before, $patch, $master, $contract), 'no plain-subtitle opt-in');
    }

    public function test_plain_before_subtitle_needs_exact_capture_and_after_still_needs_leading_title(): void
    {
        [$before, $patch, $master, $contract] = M54PassivePatternsPackage::load();
        $profile = M54PassivePatternsPackage::profile(); $title = $profile['bindings'][0]['rendered_title'];
        $before['targets'][0]['before']['page']['subtitle_html'] = '<p>'.$title.' — Незалежний plain BEFORE fixture.</p>';
        $profile['bindings'][0]['before_subtitle'] = ['mode' => 'captured-plain',
            'sha256' => hash('sha256', $before['targets'][0]['before']['page']['subtitle_html']),
            'raw_page_title' => $before['targets'][0]['before']['page']['title'], 'extracted_title' => $title, 'h1' => $title];
        $release = new TheoryNativeContentRelease($profile);
        $patch['targets'][0]['after'] = $release->definition($master['lessons'][0], $before['targets'][0]['before']);
        $release->validate($before, $patch, $master, $contract);
        foreach (['sha256', 'raw_page_title', 'extracted_title', 'h1', 'mode', 'editorial-h1', 'after-title'] as $case) {
            $p = $profile; $m = $master; $target = $patch;
            if ($case === 'editorial-h1') { $m['editorial_review']['pre_apply_metadata'][0]['h1'] = 'Foreign'; }
            elseif ($case === 'after-title') { $m['lessons'][0]['subtitle_html'] = '<p>'.$title.' — No leading strong AFTER.</p>'; }
            else { $p['bindings'][0]['before_subtitle'][$case] = $case === 'sha256' ? str_repeat('0', 64) : 'Foreign'; }
            $candidate = new TheoryNativeContentRelease($p);
            $target['targets'][0]['after'] = $candidate->definition($m['lessons'][0], $before['targets'][0]['before']);
            $this->rejection(fn () => $candidate->validate($before, $target, $m, $contract), $case);
        }
    }

    private function neutral(): array
    {
        return ['title' => 'Дві граматичні версії', 'intro' => 'Порівняй заданий фокус.', 'sections' => [
            ['label' => 'Фокус на м’ячі', 'color' => 'emerald', 'description' => 'Текст уже описує м’яч.',
                'examples' => [['en' => 'The ball was kicked by John.', 'ua' => 'Джон ударив по м’ячу.']]],
            ['label' => 'Фокус на виконавці', 'color' => 'blue', 'description' => 'Почни розповідь із виконавця.',
                'examples' => [['en' => 'John kicked the ball.', 'ua' => 'Джон ударив по м’ячу.']]],
        ]];
    }

    public function test_neutral_component_is_code_owned_and_cannot_authorize_wrong_right_or_unknown_slots(): void
    {
        [$before, $patch, $master, $contract] = M54PassivePatternsPackage::load();
        $profile = M54PassivePatternsPackage::profile(); $profile['bindings'][0]['component_overrides'] = [6 => 'usage-panels'];
        $master['lessons'][0]['blocks'][6]['semantic_component'] = 'usage-panels';
        $master['lessons'][0]['blocks'][6]['data'] = $this->neutral();
        $release = new TheoryNativeContentRelease($profile);
        $patch['targets'][0]['after'] = $release->definition($master['lessons'][0], $before['targets'][0]['before']);
        $release->validate($before, $patch, $master, $contract);
        $source = $patch['targets'][0]['after']['page']['blocks'][6]; $target = $patch['targets'][0]; $slot = 6;
        $design = (new ReflectionMethod($release, 'design'))->invoke($release, compact('target', 'source', 'slot'));
        self::assertSame('usage-panels', $design['component']); self::assertSame('mistakes-grid', $design['stored_component']);
        foreach (['unknown-slot', 'unknown-kind', 'unregistered', 'no-sections', 'items', 'wrong', 'right_en'] as $case) {
            $p = $profile; $m = $master; $target = $patch;
            if ($case === 'unknown-slot') { $p['bindings'][0]['component_overrides'] = [999 => 'usage-panels']; }
            elseif ($case === 'unknown-kind') { $p['bindings'][0]['component_overrides'][6] = 'foreign'; }
            elseif ($case === 'unregistered') { unset($p['bindings'][0]['component_overrides']); }
            elseif ($case === 'no-sections') { $m['lessons'][0]['blocks'][6]['data']['sections'] = []; }
            elseif ($case === 'items') { $m['lessons'][0]['blocks'][6]['data']['items'] = []; }
            else { $m['lessons'][0]['blocks'][6]['data']['sections'][0][$case] = 'False correction'; }
            $candidate = new TheoryNativeContentRelease($p);
            $target['targets'][0]['after'] = $candidate->definition($m['lessons'][0], $before['targets'][0]['before']);
            $this->rejection(fn () => $candidate->validate($before, $target, $m, $contract), $case);
        }
    }

    public function test_neutral_fallback_is_exact_owner_metadata_read_only_and_never_strikes_correct_alternatives(): void
    {
        $data = $this->neutral();
        $block = (object) ['id' => 3090, 'seeder' => M55PassiveReviewInfinitivePackage::OWNERS[1], 'locale' => 'uk',
            'page_id' => 94, 'page_category_id' => 1, 'uuid' => M55PassiveReviewInfinitivePackage::NEUTRAL_SLOT_UUID,
            'type' => 'mistakes-grid', 'column' => 'left', 'heading' => null, 'css_class' => null, 'level' => 'B2',
            'sort_order' => 8, 'body' => M26DetailPackage::json($data)];
        $snapshot = clone $block;
        self::assertSame('usage-panels', M55PassiveReviewInfinitivePackage::semanticComponent($block, $data, null));
        $node = TheoryLegacyAdapter::section($block, $data);
        self::assertEquals($snapshot, $block); self::assertSame('plain', $node['variant']);
        self::assertSame(['usage', 'usage'], array_column($node['items'], 'kind'));
        self::assertArrayNotHasKey('attrs', $node); self::assertArrayNotHasKey('wrong_en', $node['items'][0]);
        self::assertSame('The ball was kicked by John.', (string) $node['items'][0]['examples'][0]['en']);
        foreach (['seeder' => 'Foreign', 'locale' => 'en', 'page_id' => 103, 'page_category_id' => 17,
            'uuid' => 'foreign', 'sort_order' => 7, 'type' => 'usage-panels', 'level' => 'C1', 'column' => 'right'] as $field => $value) {
            $foreign = clone $block; $foreign->$field = $value;
            self::assertNull(M55PassiveReviewInfinitivePackage::semanticComponent($foreign, $data, null), $field);
        }
        $data['sections'][0]['description'] = 'Змінений текст лишається повністю видимим без accepted design authority.';
        $block->body = M26DetailPackage::json($data); $node = TheoryLegacyAdapter::section($block, $data);
        self::assertSame($data['sections'][0]['description'], (string) $node['items'][0]['body_html']);
        self::assertArrayNotHasKey('attrs', $node); self::assertArrayNotHasKey('detail', $node['items'][0]);
    }
}
