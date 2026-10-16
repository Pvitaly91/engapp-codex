<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M60ReportedSpeechBasicsPackage as Package;
use App\Support\TheoryNativeContentRelease;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure finite metadata checks; no Laravel bootstrap, .env, DB, HTTP or writes. */
class M60NativeTransitionTest extends TestCase
{
    private function before(int $index = 0): array
    {
        return ['seeder' => ['class' => Package::OWNERS[$index]], 'page' => ['blocks' => [
            ['type' => 'hero', 'column' => 'header', 'level' => 'B1', 'body' => '{}'],
            ['type' => 'box', 'column' => 'left', 'heading' => '1. Форма і структура', 'body' => '<p>Old forms.</p>'],
            ['type' => 'box', 'column' => 'left', 'heading' => '2. Основні випадки вживання і приклади', 'body' => '<p>Old usage.</p>'],
            ['type' => 'box', 'column' => 'right', 'heading' => '3. Типові помилки', 'body' => '<p>Old mistakes.</p>'],
            ['type' => 'box', 'column' => 'right', 'heading' => '4. Коротке правило', 'body' => '<p>Old summary.</p>'],
            ['type' => 'navigation-chips', 'column' => 'footer', 'body' => '{}'],
        ]]];
    }

    public function test_exact_twelve_box_transitions_preserve_null_levels_columns_body_order_and_uuid(): void
    {
        $release = new TheoryNativeContentRelease(Package::profile());
        foreach (Package::OWNERS as $i => $owner) {
            $before = $this->before($i); $unchanged = $before; $expected = $before;
            foreach ([1 => 'forms-grid', 2 => 'comparison-table', 3 => 'mistakes-grid', 4 => 'summary-list'] as $slot => $type) {
                $expected['page']['blocks'][$slot]['type'] = $type;
                unset($expected['page']['blocks'][$slot]['heading']);
            }
            $after = $release->projectedBefore($before);
            self::assertSame($expected, $after); self::assertSame($unchanged, $before);
            foreach ($before['page']['blocks'] as $slot => $old) {
                self::assertSame(M26DetailPackage::uuid($owner, $old, $slot + 1),
                    M26DetailPackage::uuid($owner, $after['page']['blocks'][$slot], $slot + 1));
                self::assertSame($old['column'], $after['page']['blocks'][$slot]['column']);
                self::assertSame($old['level'] ?? null, $after['page']['blocks'][$slot]['level'] ?? null);
                self::assertSame($old['body'], $after['page']['blocks'][$slot]['body']);
            }
        }
    }

    public function test_null_and_right_are_not_implicitly_authorized_for_undeclared_legacy_profiles(): void
    {
        $profile = Package::profile(); unset($profile['bindings'][0]['preserved_native_metadata']);
        $this->expectException(RuntimeException::class);
        (new TheoryNativeContentRelease($profile))->projectedBefore($this->before());
    }

    public static function rejectedPreservation(): array
    {
        return array_map(static fn ($name) => [$name], ['null-declaration', 'scalar-declaration', 'null-slot',
            'scalar-slot', 'extra-key', 'missing-level', 'reordered-keys', 'unknown-slot', 'negative-slot',
            'named-slot', 'hero-slot', 'column-header', 'column-forged', 'declared-b1', 'before-b1', 'after-b1',
            'before-not-box', 'after-unsupported', 'before-column-drift', 'before-heading-drift', 'foreign-owner']);
    }

    #[DataProvider('rejectedPreservation')]
    public function test_unreviewed_preservation_or_foreign_before_fails_closed(string $case): void
    {
        $profile = Package::profile(); $before = $this->before(); $binding = &$profile['bindings'][0];
        if ($case === 'null-declaration') { $binding['preserved_native_metadata'] = null; }
        if ($case === 'scalar-declaration') { $binding['preserved_native_metadata'] = 'right'; }
        if ($case === 'null-slot') { $binding['preserved_native_metadata'][1] = null; }
        if ($case === 'scalar-slot') { $binding['preserved_native_metadata'][1] = 'right'; }
        if ($case === 'extra-key') { $binding['preserved_native_metadata'][1]['type'] = 'forms-grid'; }
        if ($case === 'missing-level') { unset($binding['preserved_native_metadata'][1]['level']); }
        if ($case === 'reordered-keys') { $binding['preserved_native_metadata'][1] = ['level' => null, 'column' => 'left']; }
        if ($case === 'unknown-slot') { $binding['preserved_native_metadata'][6] = ['column' => 'left', 'level' => null]; }
        if ($case === 'negative-slot') { $binding['preserved_native_metadata'][-1] = ['column' => 'left', 'level' => null]; }
        if ($case === 'named-slot') { $binding['preserved_native_metadata']['slot-one'] = ['column' => 'left', 'level' => null]; }
        if ($case === 'hero-slot') { $binding['preserved_native_metadata'][0] = ['column' => 'header', 'level' => null]; }
        if ($case === 'column-header') { $binding['preserved_native_metadata'][1]['column'] = 'header'; }
        if ($case === 'column-forged') { $binding['preserved_native_metadata'][1]['column'] = 'right'; }
        if ($case === 'declared-b1') { $binding['preserved_native_metadata'][1]['level'] = 'B1'; }
        if ($case === 'before-b1') {
            $binding['continuing_metadata'][1]['before']['level'] = 'B1'; $before['page']['blocks'][1]['level'] = 'B1';
        }
        if ($case === 'after-b1') { $binding['continuing_metadata'][1]['after']['level'] = 'B1'; }
        if ($case === 'before-not-box') {
            $binding['continuing_metadata'][1]['before']['type'] = 'forms-grid'; $before['page']['blocks'][1]['type'] = 'forms-grid';
        }
        if ($case === 'after-unsupported') { $binding['continuing_metadata'][1]['after']['type'] = 'box'; }
        if ($case === 'before-column-drift') { $before['page']['blocks'][1]['column'] = 'right'; }
        if ($case === 'before-heading-drift') { $before['page']['blocks'][1]['heading'] = 'Different heading'; }
        if ($case === 'foreign-owner') { $before['seeder']['class'] = 'Database\\Seeders\\Page_V3\\ReportedSpeech\\AdvancedReportedSpeechTheorySeeder'; }
        unset($binding); $this->expectException(RuntimeException::class);
        (new TheoryNativeContentRelease($profile))->projectedBefore($before);
    }
}
