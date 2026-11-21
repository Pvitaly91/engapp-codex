<?php

namespace Tests\Feature;

use App\Http\Controllers\TestJsV2Controller;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M69AdjectivesAdverbs;
use App\Support\M84WordOrderHaveGot;
use App\Support\M85VerbToBe;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;
use WeakReference;

class ComposeTheoryNativeContentTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const WORD_ORDER_SLUG = 'word-order/theory-basic-word-order';

    private const WORD_ORDER_SOURCE_KEY = TheoryNativeContentRelease::class.'.standalone-word-order-sources';

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

    public function test_corrupt_private_artifacts_do_not_reuse_previous_validation(): void
    {
        [$before, $target] = $this->presentFormsSources();
        $page = $this->boundCoursePage($before, $target);
        $block = $this->boundBlock($target, $target['after']['page']['blocks'][1], 2);
        $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
        [$profile, $patchCopy] = $this->copyPrivateProfile(M85VerbToBe::profile(), M85VerbToBe::SOURCE);
        $release = new TheoryNativeContentRelease($profile);
        $bytes = file_get_contents(base_path($patchCopy));
        $this->assertIsString($bytes);
        app()->setLocale('uk');

        $presented = $release->presentation($block, $data);
        $this->assertSame($target['identity'], $presented['m42_native_design']['identity'] ?? null);
        $this->assertNotSame($block, $release->preserveExcludedTextBlock($block, 'standalone_test'));
        $restoredPage = $release->preserveCoursePage($page);
        $this->assertInstanceOf(Page::class, $restoredPage);
        $this->assertNotSame($page, $restoredPage);
        $this->assertSame($before['page']['blocks'][1]['body'], $restoredPage->textBlocks->firstWhere('uuid', $block->uuid)->body);

        // Only this test's private copy is changed; a previously checked source must not be trusted now.
        $this->assertNotFalse(file_put_contents(base_path($patchCopy), $bytes."\n"));
        $this->assertSame($block, $release->preserveExcludedTextBlock($block, 'standalone_test'));
        $this->assertSame($page, $release->preserveCoursePage($page));
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
        $this->assertSame($page, $invalidRelease->preserveCoursePage($page));
        $this->assertNull($invalidRelease->presentation($block, $data));

        $this->assertNotFalse(file_put_contents(base_path($patchCopy), $bytes));
        $this->assertSame($presented, $release->presentation($block, $data));
        $this->assertSame($restoredPage->toArray(), $release->preserveCoursePage($page)->toArray());
    }

    public function test_word_order_standalone_reuses_reviewed_sources_but_rebinds_each_block(): void
    {
        [$before, $patch] = M84WordOrderHaveGot::load();
        $target = $patch['targets'][0];
        $request = $this->bindWordOrderRequest();
        $originals = [];

        foreach ([0, 1] as $slot) {
            $source = $target['after']['page']['blocks'][$slot];
            $native = $this->boundBlock($target, $source, $slot + 1);
            $originals[$slot] = $native;
            $stored = $native->getAttributes();
            // Uses a new release instance on each call, just as the controller does.
            $restored = M84WordOrderHaveGot::preserveExcludedTextBlock($native, 'standalone_test');
            $this->assertInstanceOf(TextBlock::class, $restored);
            $this->assertNotSame($native, $restored);
            $this->assertSame($native->uuid, $restored->uuid);
            $old = $before['targets'][0]['before']['page']['blocks'][$slot];
            foreach (['body', 'type', 'column', 'heading', 'level', 'css_class'] as $field) {
                $this->assertSame($old[$field] ?? null, $restored->$field, $field);
            }
            $this->assertSame($stored, $native->getAttributes());
            $this->assertSame($stored, $native->getRawOriginal());
            $cached = $request->attributes->get(self::WORD_ORDER_SOURCE_KEY);
            $this->assertSame($request, $cached['request']->get());
            if ($slot === 0) {
                // An inert, test-only field distinguishes reuse of this entry from replacement.
                $cached['test_receipt'] = $receipt = new \stdClass;
                $request->attributes->set(self::WORD_ORDER_SOURCE_KEY, $cached);
            } else {
                $this->assertSame($receipt, $cached['test_receipt'] ?? null, 'The second binding must reuse validated sources.');
            }
        }

        $native = $originals[1];
        foreach ([
            'uuid' => 'foreign-block', 'seeder' => 'ForeignTheorySeeder', 'locale' => 'pl',
            'page_id' => -1, 'page_category_id' => -1, 'sort_order' => 1000, 'type' => 'summary-list',
            'column' => 'other-column', 'heading' => 'other-heading', 'level' => 'XX', 'css_class' => 'other-css',
            'body' => $native->body."\n",
        ] as $field => $wrong) {
            $foreign = (clone $native)->setRawAttributes(array_replace($native->getAttributes(), [$field => $wrong]), true);
            $this->assertSame($foreign, M84WordOrderHaveGot::preserveExcludedTextBlock($foreign, 'standalone_test'), $field);
        }
        $this->assertSame($native, M84WordOrderHaveGot::preserveExcludedTextBlock($native, 'theory'));
    }

    public function test_word_order_cache_hit_rechecks_every_private_artifact_and_does_not_keep_a_failure(): void
    {
        [$profile] = $this->copyPrivateProfile(M84WordOrderHaveGot::profile(), M84WordOrderHaveGot::SOURCE);
        $release = new TheoryNativeContentRelease($profile);
        [$before, $patch] = $release->load();
        $target = $patch['targets'][0];
        $block = $this->boundBlock($target, $target['after']['page']['blocks'][0], 1);
        $request = $this->bindWordOrderRequest();
        $oldBody = $before['targets'][0]['before']['page']['blocks'][0]['body'];
        $this->assertSame($oldBody, $release->preserveExcludedTextBlock($block, 'standalone_test')?->body);
        $this->assertTrue($request->attributes->has(self::WORD_ORDER_SOURCE_KEY));

        foreach (array_keys($profile['artifacts']) as $relative) {
            $file = base_path($relative);
            IsolatedTestEnvironment::assertOwnedPath($file);
            $bytes = file_get_contents($file);
            $this->assertIsString($bytes, $relative);
            try {
                // Same decoded document, but no longer the independently pinned bytes.
                $this->assertNotFalse(file_put_contents($file, $bytes."\n"));
                $this->assertSame($block, $release->preserveExcludedTextBlock($block, 'standalone_test'), $relative);
                $this->assertTrue(unlink($file));
                $this->assertSame($block, $release->preserveExcludedTextBlock($block, 'standalone_test'), $relative);
            } finally {
                $this->assertNotFalse(file_put_contents($file, $bytes));
            }
            $this->assertSame($oldBody, $release->preserveExcludedTextBlock($block, 'standalone_test')?->body, $relative);
        }
    }

    public function test_word_order_source_memo_is_limited_to_one_exact_request_scope_and_profile(): void
    {
        [, $patch] = M84WordOrderHaveGot::load();
        $target = $patch['targets'][0];
        $block = $this->boundBlock($target, $target['after']['page']['blocks'][0], 1);
        $request = $this->bindWordOrderRequest(name: 'test.show', method: 'HEAD');
        $expected = M84WordOrderHaveGot::preserveExcludedTextBlock($block, 'standalone_test');
        $this->assertInstanceOf(TextBlock::class, $expected);
        $this->assertNotSame($block, $expected);
        $this->assertSame($request, $request->attributes->get(self::WORD_ORDER_SOURCE_KEY)['request']->get());

        // A duplicated Request inherits arbitrary attributes, but not another request's proof.
        $duplicate = $request->duplicate();
        $this->assertSame($request, $duplicate->attributes->get(self::WORD_ORDER_SOURCE_KEY)['request']->get());
        $this->app->instance('request', $duplicate);
        $this->assertSame($expected->getAttributes(), M84WordOrderHaveGot::preserveExcludedTextBlock($block, 'standalone_test')?->getAttributes());
        $this->assertSame($duplicate, $duplicate->attributes->get(self::WORD_ORDER_SOURCE_KEY)['request']->get());
        $this->assertSame($request, $request->attributes->get(self::WORD_ORDER_SOURCE_KEY)['request']->get());

        $foreignApp = new \stdClass;
        foreach (['app' => WeakReference::create($foreignApp), 'root' => base_path('foreign')] as $field => $foreignValue) {
            $previous = $duplicate->attributes->get(self::WORD_ORDER_SOURCE_KEY);
            $duplicate->attributes->set(self::WORD_ORDER_SOURCE_KEY, array_replace($previous, [$field => $foreignValue]));
            $again = M84WordOrderHaveGot::preserveExcludedTextBlock($block, 'standalone_test');
            $this->assertSame($expected->getAttributes(), $again?->getAttributes());
            $current = $duplicate->attributes->get(self::WORD_ORDER_SOURCE_KEY);
            $this->assertSame($previous[$field], $current[$field], $field);
        }

        $wrongProfile = M84WordOrderHaveGot::profile();
        $wrongProfile['bindings'][0]['slug'] = 'different-profile-binding';
        $this->assertSame($block, (new TheoryNativeContentRelease($wrongProfile))->preserveExcludedTextBlock($block, 'standalone_test'));

        foreach ([
            ['test.step', 'GET', 'word-order/other', 'uk'],
            ['test.step', 'POST', self::WORD_ORDER_SLUG, 'uk'],
            ['test.other', 'GET', self::WORD_ORDER_SLUG, 'uk'],
            ['test.step', 'GET', self::WORD_ORDER_SLUG, 'en'],
        ] as [$name, $method, $slug, $locale]) {
            $outside = $this->bindWordOrderRequest($name, $method, $slug, $locale);
            $this->assertSame($expected->getAttributes(), M84WordOrderHaveGot::preserveExcludedTextBlock($block, 'standalone_test')?->getAttributes());
            $this->assertFalse($outside->attributes->has(self::WORD_ORDER_SOURCE_KEY));
        }

        [, $otherTarget] = $this->presentFormsSources();
        $other = $this->boundBlock($otherTarget, $otherTarget['after']['page']['blocks'][0], 1);
        $foreignProfileRequest = $this->bindWordOrderRequest();
        $restored = M85VerbToBe::preserveExcludedTextBlock($other, 'standalone_test');
        $this->assertInstanceOf(TextBlock::class, $restored);
        $this->assertNotSame($other, $restored);
        $this->assertFalse($foreignProfileRequest->attributes->has(self::WORD_ORDER_SOURCE_KEY));
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

    public function test_course_page_restores_all_original_blocks_in_order_on_independent_clones(): void
    {
        [$before, $target] = $this->presentFormsSources();
        $page = $this->boundCoursePage($before, $target);
        $foreign = new TextBlock(['type' => 'box', 'uuid' => (string) Str::uuid(), 'locale' => 'en', 'sort_order' => 1000]);
        $page->textBlocks->prepend($foreign);
        $inputBlocks = $page->textBlocks;
        $inputRaw = $inputBlocks->map->getRawOriginal()->all();
        $inputCurrent = $inputBlocks->map->getAttributes()->all();
        $attributes = $page->getAttributes();
        $copy = M85VerbToBe::preserveCoursePage($page);

        $this->assertInstanceOf(Page::class, $copy);
        $this->assertNotSame($page, $copy);
        $this->assertNotSame($inputBlocks, $copy->textBlocks);
        $this->assertSame(array_replace($attributes, ['text' => $before['page']['subtitle_text']]), $copy->getAttributes());
        $this->assertSame($attributes, $page->getAttributes());
        $this->assertSame($attributes, $page->getRawOriginal());
        $this->assertSame($inputBlocks, $page->textBlocks);
        $this->assertSame($inputCurrent, $inputBlocks->map->getAttributes()->all());
        $this->assertSame($inputRaw, $inputBlocks->map->getRawOriginal()->all());

        $restored = $copy->textBlocks;
        $this->assertSame([...range(0, count($before['page']['blocks'])), 1000], $restored->pluck('sort_order')->all());
        $this->assertSame($foreign, $restored->last());
        $subtitle = $restored->first();
        $this->assertSame('subtitle', $subtitle->type);
        $this->assertSame($before['page']['subtitle_html'], $subtitle->body);
        $this->assertNotSame($inputBlocks->firstWhere('type', 'subtitle'), $subtitle);

        foreach ($before['page']['blocks'] as $slot => $source) {
            $new = $restored[$slot + 1];
            $original = $inputBlocks->firstWhere('sort_order', $slot + 1);
            $this->assertSame($original->uuid, $new->uuid);
            $this->assertNotSame($original, $new);
            foreach (['type', 'column', 'heading', 'level', 'css_class', 'body'] as $field) {
                $this->assertSame($source[$field] ?? null, $new->$field, $field);
            }
        }
    }

    public function test_course_page_requires_both_exact_original_and_current_page_attributes(): void
    {
        [$before, $target] = $this->presentFormsSources();
        $page = $this->boundCoursePage($before, $target);

        foreach (['id' => -1, 'page_category_id' => -1, 'seeder' => 'ForeignPageSeeder', 'slug' => 'wrong-slug',
            'title' => 'Wrong title', 'type' => 'non-theory', 'text' => 'Unexpected introduction'] as $field => $wrong) {
            $rawMismatch = clone $page;
            $rawMismatch->setRawAttributes(array_replace($page->getAttributes(), [$field => $wrong]), true);
            $rawMismatch->setAttribute($field, $page->getAttribute($field));
            $this->assertSame($rawMismatch, M85VerbToBe::preserveCoursePage($rawMismatch), 'Wrong original '.$field);

            $dirtyMismatch = clone $page;
            $dirtyMismatch->setAttribute($field, $wrong);
            $this->assertSame($dirtyMismatch, M85VerbToBe::preserveCoursePage($dirtyMismatch), 'Wrong current '.$field);
        }

        $withoutRelation = clone $page;
        $withoutRelation->unsetRelation('textBlocks');
        $this->assertSame($withoutRelation, M85VerbToBe::preserveCoursePage($withoutRelation));
        $this->assertFalse($withoutRelation->relationLoaded('textBlocks'));
        $foreignPage = clone $page;
        $foreignPage->setRawAttributes(array_replace($page->getAttributes(), ['seeder' => 'ForeignPageSeeder']), true);
        $this->assertSame($foreignPage, M85VerbToBe::preserveCoursePage($foreignPage));
        $this->assertNull(M85VerbToBe::preserveCoursePage(null));
    }

    public function test_course_page_rejects_missing_duplicate_and_changed_blocks_without_partial_restoration(): void
    {
        [$before, $target] = $this->presentFormsSources();
        $page = $this->boundCoursePage($before, $target);
        $blocks = $page->textBlocks;
        $last = $blocks->firstWhere('sort_order', count($target['after']['page']['blocks']));
        $changedLast = clone $last;
        $changedLast->body .= "\n"; // Same decoded data, but the stored artifact binding is byte exact.
        $subtitle = $blocks->firstWhere('type', 'subtitle');
        $wrongSubtitle = clone $subtitle;
        $wrongSubtitle->heading = 'Unexpected subtitle heading';

        foreach ([
            'missing native block' => $blocks->reject(fn (TextBlock $block) => $block === $last),
            'duplicated UUID at unchanged block count' => $blocks->map(fn (TextBlock $block) => $block === $last ? clone $blocks->firstWhere('sort_order', 1) : $block),
            'changed last native block' => $blocks->map(fn (TextBlock $block) => $block === $last ? $changedLast : $block),
            'invalid subtitle' => $blocks->map(fn (TextBlock $block) => $block === $subtitle ? $wrongSubtitle : $block),
        ] as $case => $corrupt) {
            $broken = clone $page;
            $broken->setRelation('textBlocks', $corrupt->values());
            $input = $broken->textBlocks;
            $attributes = $input->map->getAttributes()->all();
            $this->assertSame($broken, M85VerbToBe::preserveCoursePage($broken), $case);
            $this->assertSame($input, $broken->textBlocks);
            $this->assertSame($attributes, $broken->textBlocks->map->getAttributes()->all());
        }
    }

    public function test_course_page_removes_the_added_subtitle_if_the_original_had_none(): void
    {
        [$before, $patch] = M69AdjectivesAdverbs::load();
        $source = $before['targets'][2]['before'];
        $target = $patch['targets'][2];
        $page = $this->boundCoursePage($source, $target);
        $this->assertArrayNotHasKey('subtitle_html', $source['page']);
        $this->assertNotNull($page->textBlocks->firstWhere('type', 'subtitle'));

        $copy = M69AdjectivesAdverbs::preserveCoursePage($page);

        $this->assertInstanceOf(Page::class, $copy);
        $this->assertNotSame($page, $copy);
        $this->assertNull($copy->text);
        $this->assertNull($copy->textBlocks->firstWhere('type', 'subtitle'));
        $this->assertSame(M69AdjectivesAdverbs::EDING_CONTINUING_UUIDS, $copy->textBlocks->pluck('uuid')->all());
        $this->assertSame(array_column($source['page']['blocks'], 'body'), $copy->textBlocks->pluck('body')->all());
        $this->assertNotNull($page->textBlocks->firstWhere('type', 'subtitle'));
    }

    public function test_ukrainian_course_lesson_and_test_share_the_restored_page_without_persisting_it(): void
    {
        $this->rebuildComposeTestSchema();
        config(['coming-soon.enabled' => false, 'tests.tech_info_enabled' => false]);
        [$before, $target] = $this->presentFormsSources();
        $page = $this->boundCoursePage($before, $target);
        $root = PageCategory::create(['slug' => 'basic-grammar', 'title' => 'Основна граматика', 'language' => 'uk', 'type' => 'theory']);
        PageCategory::forceCreate(['id' => $page->page_category_id, 'slug' => 'verb-to-be', 'title' => 'Verb to Be',
            'language' => 'uk', 'type' => 'theory', 'parent_id' => $root->id]);
        DB::table('pages')->insert($page->getAttributes());
        DB::table('text_blocks')->insert($page->textBlocks->map->getAttributes()->all());

        $url = 'http://gramlyze.loc/courses/english-grammar-theory/lesson/basic-grammar/verb-to-be/'.$page->slug;
        $lesson = $this->get($url)->assertOk()->assertViewIs('courses.theory-lesson');
        $quiz = $this->get($url.'/test?attempt=perf19-fixture')->assertOk()->assertViewIs('test-modes.theory-mixed');
        $this->assertSame('uk', $lesson->viewData('locale'));
        $this->assertSame($page->id, $lesson->viewData('lesson')['page_id']);
        foreach ([$lesson, $quiz] as $response) {
            $viewPage = $response->viewData('page');
            $this->assertSame($before['page']['subtitle_text'], $viewPage->text);
            $this->assertSame([
                $before['page']['subtitle_html'], ...array_column($before['page']['blocks'], 'body'),
            ], $viewPage->textBlocks->pluck('body')->all());
        }
        $this->assertSame($target['after']['page']['subtitle_text'], Page::findOrFail($page->id)->text);
        $this->assertSame($target['after']['page']['subtitle_html'], TextBlock::where('page_id', $page->id)->where('type', 'subtitle')->firstOrFail()->body);
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

    private function boundCoursePage(array $before, array $target): Page
    {
        $after = $target['after']['page'];
        $page = (new Page)->setRawAttributes([
            'id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
            'seeder' => $target['identity'], 'slug' => $target['slug'], 'title' => $before['page']['title'],
            'type' => 'theory', 'text' => $after['subtitle_text'],
        ], true);
        $blocks = [$this->boundBlock($target, [
            'type' => 'subtitle', 'column' => 'header', 'level' => $before['page']['subtitle_level'] ?? null,
            'uuid_key' => $before['page']['subtitle_uuid_key'] ?? 'subtitle', 'body' => $after['subtitle_html'],
        ], 0)];
        foreach ($after['blocks'] as $slot => $source) {
            $blocks[] = $this->boundBlock($target, $source, $slot + 1);
        }

        return $page->setRelation('textBlocks', new Collection(array_reverse($blocks)));
    }

    private function bindWordOrderRequest(
        string $name = 'test.step', string $method = 'GET', string $slug = self::WORD_ORDER_SLUG, string $locale = 'uk'
    ): Request {
        $suffix = $name === 'test.step' ? '/step' : '';
        $request = Request::create('http://gramlyze.loc/test/'.$slug.$suffix, $method);
        $route = new Route([$method], '/test/{slug}'.$suffix, static fn () => null);
        $route->name($name)->where('slug', '.*')->bind($request);
        $request->setRouteResolver(static fn () => $route);
        $this->app->instance('request', $request);
        $this->app->setLocale($locale);
        $this->assertSame($slug, $request->route('slug'));

        return $request;
    }

    private function copyPrivateProfile(array $profile, string $patchPath): array
    {
        $root = str_replace('\\', '/', IsolatedTestEnvironment::prepare());
        $base = str_replace('\\', '/', base_path());
        $this->assertStringStartsWith($base.'/', $root);
        $private = substr($root, strlen($base) + 1).'/native-release-'.bin2hex(random_bytes(8));
        $this->assertTrue(mkdir(base_path($private)));
        IsolatedTestEnvironment::assertOwnedPath(base_path($private));

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

        return [$profile, $private.'/'.$patchPath];
    }
}
