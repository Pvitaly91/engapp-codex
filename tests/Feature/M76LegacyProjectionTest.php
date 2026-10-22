<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M75PronounForms;
use App\Support\TheoryNativeContentRelease;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure finite source projections. No application, schema, SQL, HTTP or filesystem writes. */
class M76LegacyProjectionTest extends TestCase
{
    private function fixture(): array
    {
        $owner = 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\M76IsolatedLegacySeeder';
        $blocks = [
            ['type' => 'box', 'column' => 'header', 'heading' => 'Old hero heading', 'body' => '<p>Old hero.</p>',
                'layout' => 'legacy', 'hero_image' => 'old.svg', 'content' => '<p>Parallel hero.</p>'],
            ['type' => 'box', 'column' => 'left', 'body' => '<p>Ten old static tasks.</p>',
                'layout' => 'legacy', 'exercises' => [['type' => 'select', 'items' => ['old exercise']]]],
            ['type' => 'box', 'column' => 'footer', 'body' => '<p>Old navigation.</p>',
                'layout' => 'legacy', 'links' => [['label' => 'Old label', 'url' => '/theory/old']]],
        ];
        $before = ['seeder' => ['class' => $owner], 'page' => [
            'title' => 'Unchanged raw title', 'subtitle_html' => '<p><strong>Unchanged title</strong></p>',
            'subtitle_text' => 'Unchanged title', 'blocks' => $blocks,
        ]];
        $binding = ['identity' => $owner, 'practice_level' => null, 'appended_blocks' => [],
            'existing_practice' => ['source_index' => 1, 'uuid' => M26DetailPackage::uuid($owner, $blocks[1], 2), 'level' => null],
            'continuing_metadata' => [], 'preserved_native_metadata' => [], 'legacy_box_projection' => []];
        foreach (['hero', 'practice-set', 'forms-grid'] as $slot => $type) {
            $binding['continuing_metadata'][$slot] = ['column' => $blocks[$slot]['column'],
                'before' => ['type' => 'box', 'heading' => $blocks[$slot]['heading'] ?? null, 'level' => null],
                'after' => ['type' => $type, 'heading' => null, 'level' => null]];
            $binding['preserved_native_metadata'][$slot] = ['column' => $blocks[$slot]['column'], 'level' => null];
            $binding['legacy_box_projection'][$slot] = ['before_sha256' => M26DetailPackage::digest($blocks[$slot]),
                'remove_fields' => match ($slot) {
                    0 => ['layout', 'hero_image', 'content'], 1 => ['layout', 'exercises'], 2 => ['layout', 'links'],
                }];
        }
        return [$before, ['label' => 'M76 isolated legacy', 'marker' => 'm76_native_v1',
            'detail_marker' => 'm76_detail_key', 'uuid_prefix' => 'm76:', 'bindings' => [$binding]]];
    }

    private function rejects(array $before, array $profile): void
    {
        try {
            (new TheoryNativeContentRelease($profile))->projectedBefore($before);
            self::fail('An unreviewed legacy projection must fail closed.');
        } catch (RuntimeException $error) {
            self::assertStringStartsWith('M76 isolated legacy:', $error->getMessage());
        }
    }

    public function test_exact_projection_removes_only_declared_parallel_payloads_and_preserves_identity_and_raw_before(): void
    {
        [$before, $profile] = $this->fixture(); $original = $before;
        $release = new TheoryNativeContentRelease($profile);
        $after = $release->projectedBefore($before);
        self::assertSame($original, $before);
        self::assertSame(['hero', 'practice-set', 'forms-grid'], array_column($after['page']['blocks'], 'type'));
        self::assertSame(['header', 'left', 'footer'], array_column($after['page']['blocks'], 'column'));
        foreach ($before['page']['blocks'] as $slot => $source) {
            $expected = $source;
            foreach ($profile['bindings'][0]['legacy_box_projection'][$slot]['remove_fields'] as $field) { unset($expected[$field]); }
            $expected['type'] = ['hero', 'practice-set', 'forms-grid'][$slot]; unset($expected['heading']);
            self::assertSame($expected, $after['page']['blocks'][$slot]);
            self::assertSame(M26DetailPackage::uuid($before['seeder']['class'], $source, $slot + 1),
                M26DetailPackage::uuid($before['seeder']['class'], $after['page']['blocks'][$slot], $slot + 1));
            self::assertNull($after['page']['blocks'][$slot]['level'] ?? null);
        }
        self::assertTrue($release->existingPractice($profile['bindings'][0], $before));
    }

    public function test_accepted_serializer_does_not_retain_legacy_secondary_html_or_exercises(): void
    {
        [$before, $profile] = $this->fixture();
        $lesson = ['slug' => 'isolated', 'subtitle_html' => '<p><strong>Unchanged title</strong> — new.</p>',
            'subtitle_text' => 'New summary.', 'details' => [], 'blocks' => []];
        foreach (['hero', 'practice-set', 'forms-grid'] as $slot => $type) {
            $lesson['blocks'][] = ['key' => 'native-'.$slot, 'existing_source_index' => $slot, 'type' => $type,
                'column' => $before['page']['blocks'][$slot]['column'], 'data' => ['title' => 'Native '.$slot]];
        }
        $after = (new TheoryNativeContentRelease($profile))->definition($lesson, $before);
        foreach ($after['page']['blocks'] as $slot => $source) {
            foreach (['layout', 'hero_image', 'content', 'columns', 'exercises', 'mistakes', 'summary_points', 'links'] as $field) {
                self::assertArrayNotHasKey($field, $source);
            }
            self::assertSame(['title' => 'Native '.$slot, 'm76_native_v1' => ['key' => 'native-'.$slot, 'detail_keys' => []]],
                json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR));
        }
    }

    public function test_hero_practice_and_footer_each_require_their_own_explicit_legacy_opt_in(): void
    {
        foreach ([0, 1, 2] as $slot) {
            [$before, $profile] = $this->fixture();
            unset($profile['bindings'][0]['legacy_box_projection'][$slot]);
            $this->rejects($before, $profile);
        }
    }

    public function test_full_source_digest_detects_primary_and_secondary_drift_even_when_old_body_is_unchanged(): void
    {
        foreach (['body', 'content', 'hero_image', 'layout', 'uuid'] as $field) {
            [$before, $profile] = $this->fixture();
            $before['page']['blocks'][0][$field] = 'Unreviewed changed '.$field;
            $this->rejects($before, $profile);
        }
        [$before, $profile] = $this->fixture();
        $before['page']['blocks'] = array_reverse($before['page']['blocks']);
        $this->rejects($before, $profile);
    }

    public function test_removal_policy_requires_complete_ordered_secondary_set_and_forbids_primary_or_unknown_fields(): void
    {
        foreach ([[], ['layout'], ['content', 'hero_image', 'layout'], ['layout', 'hero_image', 'content', 'content'],
            ['layout', 'hero_image', 'content', 'body'], ['layout', 'hero_image', 'content', 'uuid'],
            ['layout', 'hero_image', 'content', 'unknown_payload']] as $removal) {
            [$before, $profile] = $this->fixture();
            $profile['bindings'][0]['legacy_box_projection'][0]['remove_fields'] = $removal;
            $this->rejects($before, $profile);
        }
        [$before, $profile] = $this->fixture();
        $profile['bindings'][0]['legacy_box_projection'][0]['optional_unreviewed_key'] = true;
        $this->rejects($before, $profile);
    }

    public function test_foreign_owner_slots_source_types_columns_levels_css_and_uuid_transition_are_rejected(): void
    {
        foreach (['type' => 'usage-panels', 'column' => 'left', 'level' => 'A2', 'css_class' => 'custom-style'] as $field => $value) {
            [$before, $profile] = $this->fixture();
            $before['page']['blocks'][0][$field] = $value;
            $profile['bindings'][0]['legacy_box_projection'][0]['before_sha256'] = M26DetailPackage::digest($before['page']['blocks'][0]);
            $this->rejects($before, $profile);
        }
        [$before, $profile] = $this->fixture(); $before['seeder']['class'] = 'Foreign\\Owner';
        $this->rejects($before, $profile);
        [$before, $profile] = $this->fixture();
        $profile['bindings'][0]['legacy_box_projection']['foreign-slot'] = $profile['bindings'][0]['legacy_box_projection'][0];
        $this->rejects($before, $profile);
        foreach ([['type', 'm76-section'], ['level', 'A2'], ['uuid', 'foreign']] as [$field, $value]) {
            [$before, $profile] = $this->fixture();
            $profile['bindings'][0]['continuing_metadata'][0]['after'][$field] = $value;
            $this->rejects($before, $profile);
        }
        [$before, $profile] = $this->fixture();
        $profile['bindings'][0]['preserved_native_metadata'][0]['column'] = 'left';
        $this->rejects($before, $profile);
    }

    public function test_m75_default_practice_remains_native_and_cannot_silently_consume_an_unpinned_legacy_box(): void
    {
        $root = dirname(__DIR__, 2); [$before] = M75PronounForms::load($root);
        $profile = M75PronounForms::profile(); $release = new TheoryNativeContentRelease($profile);
        foreach ($before['targets'] as $index => $target) {
            $binding = $profile['bindings'][$index];
            self::assertArrayNotHasKey('legacy_box_projection', $binding);
            self::assertTrue($release->existingPractice($binding, $target['before']));
            $changed = $target['before']; $slot = $binding['existing_practice']['source_index'];
            $changed['page']['blocks'][$slot]['type'] = 'box';
            try {
                $release->existingPractice($binding, $changed);
                self::fail('An older native release must not acquire implicit legacy-box permission.');
            } catch (RuntimeException $error) {
                self::assertStringContainsString('captured continuing practice', $error->getMessage());
            }
        }
    }
}
