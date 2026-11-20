<?php

namespace Tests\Feature;

use App\Http\Controllers\TestJsV2Controller;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M85VerbToBe;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class ComposeTheoryNativeContentTest extends TestCase
{
    use RebuildsComposeTestSchema;

    public function test_exact_owned_blocks_restore_legacy_data_without_mutating_the_model(): void
    {
        [$before, $target] = $this->presentFormsSources();
        $content = $this->boundBlock($target, $target['after']['page']['blocks'][1], 2);
        $original = $content->getAttributes();
        $old = $before['page']['blocks'][1];

        $restored = M85VerbToBe::preserveExcludedTextBlock($content, 'standalone_test');

        $this->assertInstanceOf(TextBlock::class, $restored);
        $this->assertNotSame($content, $restored);
        $this->assertSame($content->uuid, $restored->uuid);
        $this->assertNotSame($original['body'], $restored->body);
        foreach (['body', 'type', 'column', 'heading', 'level', 'css_class'] as $field) {
            $this->assertSame($old[$field] ?? null, $restored->$field, $field);
        }
        $this->assertSame($original, $content->getAttributes());
        $this->assertSame($original, $content->getRawOriginal());
        $this->assertSame($content, M85VerbToBe::preserveExcludedTextBlock($content, 'theory'));

        $subtitle = $this->boundBlock($target, [
            'type' => 'subtitle', 'column' => 'header', 'level' => $before['page']['subtitle_level'] ?? null,
            'uuid_key' => $before['page']['subtitle_uuid_key'] ?? 'subtitle',
            'body' => $target['after']['page']['subtitle_html'],
        ], 0);
        $subtitleOriginal = $subtitle->getAttributes();
        $restoredSubtitle = M85VerbToBe::preserveExcludedTextBlock($subtitle, 'standalone_test');
        $this->assertInstanceOf(TextBlock::class, $restoredSubtitle);
        $this->assertNotSame($subtitle, $restoredSubtitle);
        $this->assertSame($before['page']['subtitle_html'], $restoredSubtitle->body);
        $this->assertSame($subtitleOriginal, $subtitle->getAttributes());

        $newSlot = count($before['page']['blocks']);
        $appended = $this->boundBlock($target, $target['after']['page']['blocks'][$newSlot], $newSlot + 1);
        $this->assertNull(M85VerbToBe::preserveExcludedTextBlock($appended, 'standalone_test'));
    }

    public function test_foreign_locale_and_non_exact_bindings_keep_the_original_block(): void
    {
        [, $target] = $this->presentFormsSources();
        $source = $target['after']['page']['blocks'][1];

        foreach ([
            'foreign owner' => ['seeder' => 'ForeignTheorySeeder'],
            'foreign locale' => ['locale' => 'pl'],
            'other page' => ['page_id' => $target['page_binding']['id'] + 1000],
            'other category' => ['page_category_id' => $target['page_binding']['page_category_id'] + 1000],
            'other UUID' => ['uuid' => (string) Str::uuid()],
            'other type' => ['type' => 'summary-list'],
            'other position' => ['sort_order' => 1000],
            'other heading' => ['heading' => 'Unexpected heading'],
            'other column' => ['column' => 'right'],
            'other level' => ['level' => 'C1'],
            'other class' => ['css_class' => 'different-class'],
            'same decoded body but different frozen bytes' => ['body' => $source['body']."\n"],
            'invalid JSON body' => ['body' => '{'],
        ] as $case => $overrides) {
            $block = $this->boundBlock($target, $source, 2);
            $block->setRawAttributes(array_replace($block->getAttributes(), $overrides), true);
            $this->assertSame($block, M85VerbToBe::preserveExcludedTextBlock($block, 'standalone_test'), $case);
        }
    }

    public function test_corrupt_private_artifacts_do_not_reuse_previous_validation_in_either_entry_point(): void
    {
        [, $target] = $this->presentFormsSources();
        $block = $this->boundBlock($target, $target['after']['page']['blocks'][1], 2);
        $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
        [$profile, $patchCopy] = $this->copyPrivateProfile();
        $release = new TheoryNativeContentRelease($profile);
        $bytes = file_get_contents(base_path($patchCopy));
        $this->assertIsString($bytes);
        app()->setLocale('uk');

        $presented = $release->presentation($block, $data);
        $this->assertSame($target['identity'], $presented['m42_native_design']['identity'] ?? null);
        $this->assertNotSame($block, $release->preserveExcludedTextBlock($block, 'standalone_test'));

        // Only this test's private copy is changed; a previously checked source must not be trusted now.
        $this->assertNotFalse(file_put_contents(base_path($patchCopy), $bytes."\n"));
        $this->assertSame($block, $release->preserveExcludedTextBlock($block, 'standalone_test'));
        $this->assertNull($release->presentation($block, $data));

        // Even a matching checksum is insufficient if the semantic package fails validation.
        $invalid = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        $invalid['schema_version'] = 999;
        $invalid = json_encode($invalid, JSON_THROW_ON_ERROR);
        $this->assertNotFalse(file_put_contents(base_path($patchCopy), $invalid));
        $profile['artifacts'][$patchCopy] = hash('sha256', $invalid);
        $invalidRelease = new TheoryNativeContentRelease($profile);
        $this->assertSame($invalid, file_get_contents(base_path($patchCopy)));
        $this->assertSame($block, $invalidRelease->preserveExcludedTextBlock($block, 'standalone_test'));
        $this->assertNull($invalidRelease->presentation($block, $data));

        $this->assertNotFalse(file_put_contents(base_path($patchCopy), $bytes));
        $this->assertSame($presented, $release->presentation($block, $data));
    }

    public function test_compose_payload_keeps_ordered_pivot_priority_deduplication_and_legacy_fallback(): void
    {
        $this->rebuildComposeTestSchema();
        [$before, $target] = $this->presentFormsSources();
        $native = $this->boundBlock($target, $target['after']['page']['blocks'][1], 2);
        $newSlot = count($before['page']['blocks']);
        $excluded = $this->boundBlock($target, $target['after']['page']['blocks'][$newSlot], $newSlot + 1);
        $other = new TextBlock(['uuid' => (string) Str::uuid(), 'type' => 'box', 'body' => 'Second pivot block', 'level' => 'A1']);
        $legacy = TextBlock::query()->create(['uuid' => (string) Str::uuid(), 'type' => 'box', 'body' => 'Legacy fallback', 'level' => 'A1']);
        $question = new Question(['uuid' => (string) Str::uuid(), 'question' => 'Готовий.', 'type' => Question::TYPE_COMPOSE_TOKENS]);
        $question->theory_text_block_uuid = $legacy->uuid;
        $option = new QuestionOption(['option' => 'Ready']);
        $answer = new QuestionAnswer(['marker' => 'a1']);
        $answer->setRelation('option', $option);
        $question->setRelation('answers', new Collection([$answer]));
        $question->setRelation('options', new Collection([$option]));

        $controller = app(TestJsV2Controller::class);
        $payload = new ReflectionMethod(TestJsV2Controller::class, 'buildComposeQuestionPayload');
        $legacyPayload = $payload->invoke($controller, $question);
        $expectedLegacy = ['uuid' => $legacy->uuid, 'type' => 'box', 'body' => 'Legacy fallback', 'level' => 'A1'];
        $this->assertSame([$expectedLegacy], $legacyPayload['theory_blocks']);
        $this->assertSame($expectedLegacy, $legacyPayload['theory_block']);

        $question->setRelation('theoryTextBlocks', new Collection([$excluded]));
        $fallback = $payload->invoke($controller, $question);
        $this->assertSame([$expectedLegacy], $fallback['theory_blocks']);
        $this->assertSame($expectedLegacy, $fallback['theory_block']);

        $question->setRelation('theoryTextBlocks', new Collection([$excluded, $native, clone $native, $other]));
        $ordered = $payload->invoke($controller, $question);
        $old = $before['page']['blocks'][1];
        $expectedFirst = ['uuid' => $native->uuid, 'type' => $old['type'], 'body' => $old['body'], 'level' => $old['level'] ?? null];
        $this->assertSame([$expectedFirst, [
            'uuid' => $other->uuid, 'type' => 'box', 'body' => 'Second pivot block', 'level' => 'A1',
        ]], $ordered['theory_blocks']);
        $this->assertSame($expectedFirst, $ordered['theory_block']);
        $this->assertSame($target['after']['page']['blocks'][1]['body'], $native->body);
    }

    private function presentFormsSources(): array
    {
        [$before, $patch] = M85VerbToBe::load();

        return [$before['targets'][0]['before'], $patch['targets'][0]];
    }

    private function boundBlock(array $target, array $source, int $position): TextBlock
    {
        return (new TextBlock)->setRawAttributes([
            'uuid' => M26DetailPackage::uuid($target['identity'], $source, $position),
            'page_id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
            'locale' => 'uk', 'seeder' => $target['identity'], 'sort_order' => $position,
            'body' => $source['body'], 'type' => $source['type'], 'column' => $source['column'] ?? null,
            'heading' => $source['heading'] ?? null, 'level' => $source['level'] ?? null, 'css_class' => $source['css_class'] ?? null,
        ], true);
    }

    private function copyPrivateProfile(): array
    {
        $root = str_replace('\\', '/', IsolatedTestEnvironment::prepare());
        $base = str_replace('\\', '/', base_path());
        $this->assertStringStartsWith($base.'/', $root);
        $private = substr($root, strlen($base) + 1).'/native-release-'.bin2hex(random_bytes(8));
        $this->assertTrue(mkdir(base_path($private)));
        IsolatedTestEnvironment::assertOwnedPath(base_path($private));

        $profile = M85VerbToBe::profile();
        $artifacts = [];
        foreach ($profile['artifacts'] as $source => $digest) {
            $copy = $private.'/'.$source;
            $parent = dirname(base_path($copy));
            if (! is_dir($parent)) {
                $this->assertTrue(mkdir($parent, 0777, true));
            }
            IsolatedTestEnvironment::assertOwnedPath($parent);
            $this->assertTrue(copy(base_path($source), base_path($copy)));
            $artifacts[$copy] = $digest;
        }
        $profile['artifacts'] = $artifacts;

        return [$profile, $private.'/'.M85VerbToBe::SOURCE];
    }
}
