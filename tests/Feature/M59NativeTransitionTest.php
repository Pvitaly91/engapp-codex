<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M56GerundSimplePassivesPackage;
use App\Support\M57ContinuousPerfectPassivesPackage;
use App\Support\M58PastFuturePassivesPackage;
use App\Support\M59PassiveFutureAgentReviewPackage as Package;
use App\Support\TheoryNativeContentRelease;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure serializer/metadata checks: no Laravel bootstrap, .env, DB or write. */
class M59NativeTransitionTest extends TestCase
{
    private function before(): array
    {
        return ['seeder' => ['class' => Package::OWNERS[2]], 'page' => [
            'subtitle_html' => '<p><strong>Advanced Passive Voice</strong> old introduction.</p>',
            'subtitle_text' => 'Old introduction.', 'blocks' => [
                ['type' => 'hero', 'column' => 'header', 'level' => 'B2', 'body' => '{}'],
                ['type' => 'box', 'column' => 'left', 'heading' => 'B2 focus', 'body' => '<p>Old scaffold.</p>'],
            ],
        ]];
    }

    public function test_exact_metadata_projection_keeps_literal_before_column_order_and_uuid(): void
    {
        $before = $this->before(); $unchanged = $before;
        $release = new TheoryNativeContentRelease(Package::profile());
        $after = $release->projectedBefore($before);
        self::assertSame($unchanged, $before);
        $expected = $before; $expected['page']['blocks'][1] = [
            'type' => 'usage-panels', 'column' => 'left', 'body' => '<p>Old scaffold.</p>', 'level' => 'B2',
        ];
        self::assertSame($expected, $after);
        self::assertSame(M26DetailPackage::uuid(Package::OWNERS[2], $before['page']['blocks'][1], 2),
            M26DetailPackage::uuid(Package::OWNERS[2], $after['page']['blocks'][1], 2));
    }

    public static function rejectedMetadata(): array
    {
        return [['type', 'usage-panels'], ['heading', 'Different focus'], ['level', 'B2'],
            ['column', 'header'], ['owner', 'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveVoiceCategorySeeder']];
    }

    #[DataProvider('rejectedMetadata')]
    public function test_unreviewed_before_or_foreign_owner_cannot_project(string $field, string $value): void
    {
        $before = $this->before();
        if ($field === 'owner') { $before['seeder']['class'] = $value; }
        else { $before['page']['blocks'][1][$field] = $value; }
        $release = new TheoryNativeContentRelease(Package::profile());
        $this->expectException(RuntimeException::class); $release->projectedBefore($before);
    }

    public function test_no_transition_is_inherited_by_other_owners_or_old_packages(): void
    {
        foreach ([M56GerundSimplePassivesPackage::class, M57ContinuousPerfectPassivesPackage::class,
            M58PastFuturePassivesPackage::class] as $package) {
            [$before, $patch, $master, $contract] = $package::load(dirname(__DIR__, 2));
            $release = new TheoryNativeContentRelease($package::profile());
            foreach ($before['targets'] as $i => $target) {
                self::assertSame($target['before'], $release->projectedBefore($target['before']));
                self::assertSame($patch['targets'][$i]['after'], $package::definition($master['lessons'][$i], $target['before']));
            }
            $package::validate($before, $patch, $master, $contract);
        }
    }
}
