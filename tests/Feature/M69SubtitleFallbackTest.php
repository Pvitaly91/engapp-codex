<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\Page;
use App\Support\M68ComparisonBasics;
use App\Support\M69AdjectivesAdverbs as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Data-only 1.0.1 fix: exercise the unchanged real extractor, never replace its heuristics. */
class M69SubtitleFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk');
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
    }

    private function refused(callable $call): void
    {
        try { $call(); self::fail('Expected exact plain subtitle refusal.'); }
        catch (RuntimeException $error) { self::assertNotSame('', $error->getMessage()); }
    }

    private function declaration(array $profile, array $before, string $body, array $master): void
    {
        $method = new \ReflectionMethod(TheoryNativeContentRelease::class, 'assertPlainFallbackSubtitle');
        $method->invoke(new TheoryNativeContentRelease($profile), $before, $profile['bindings'][2], $body, $master);
    }

    private function reviewed(): array
    {
        [$before, $patch, $master, $contract] = Package::load();
        self::assertSame('1.0.1', $master['version']);
        $profile = Package::profile(); self::assertSame('plain-fallback', $profile['bindings'][2]['after_subtitle']['mode']);
        return [$profile, $before['targets'][2]['before'], $master['lessons'][2]['subtitle_html'], $master];
    }

    public function test_exact_plain_paragraph_makes_actual_unchanged_extractor_return_null_and_keeps_the_raw_leading_hyphen(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        (new TheoryNativeContentRelease(Package::profile()))->validate($before, $patch, $master, $contract);
        [$profile, $old, $body, $master] = $this->reviewed(); $this->declaration($profile, $old, $body, $master);
        self::assertGreaterThan(110, mb_strlen(html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        self::assertStringNotContainsString('<strong', $body); self::assertStringNotContainsString('<h', $body);
        $controller = (new \ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $extract = new \ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        self::assertNull($extract->invoke($controller, $body));
        $page = new Page; $page->setRawAttributes(['id' => 275, 'title' => '-ed / -ing Adjectives'], true);
        $attributes = $page->getAttributes();
        (new \ReflectionMethod(PageController::class, 'applyLocalizedTitlesToModels'))
            ->invoke($controller, collect([$page]), [], 'uk', 'uk');
        self::assertSame('-ed / -ing Adjectives', $page->title); self::assertSame($attributes, $page->getAttributes());
        self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
    }

    public function test_frozen_1_0_0_strong_subtitle_demonstrates_the_real_regression_without_editing_global_code(): void
    {
        $old = json_decode(file_get_contents(base_path('docs/content/m69-adjectives-adverbs.v1.0.0.json')), true, flags: JSON_THROW_ON_ERROR);
        $controller = (new \ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $extract = new \ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        self::assertSame('ed / -ing Adjectives', $extract->invoke($controller, $old['lessons'][2]['subtitle_html']));
        self::assertSame('-ed / -ing Adjectives', Package::RENDERED_TITLES[2]);
    }

    public function test_only_the_captured_absent_owner_and_exact_hash_title_and_null_extraction_declaration_are_allowed(): void
    {
        [$profile, $old, $body, $master] = $this->reviewed();
        foreach (['mode' => 'strong-title', 'sha256' => str_repeat('0', 64), 'extracted_title' => 'ed / -ing Adjectives',
            'h1' => 'ed / -ing Adjectives'] as $field => $value) {
            $bad = $profile; $bad['bindings'][2]['after_subtitle'][$field] = $value;
            $this->refused(fn () => $this->declaration($bad, $old, $body, $master));
        }
        foreach (['title' => 'Foreign title', 'locale' => 'pl'] as $field => $value) {
            $bad = $old; $bad['page'][$field] = $value;
            $this->refused(fn () => $this->declaration($profile, $bad, $body, $master));
        }
        $bad = $old; $bad['page']['category']['slug'] = 'foreign';
        $this->refused(fn () => $this->declaration($profile, $bad, $body, $master));
        $bad = $profile; $bad['bindings'][2]['before_subtitle']['mode'] = 'captured-plain';
        $this->refused(fn () => $this->declaration($bad, $old, $body, $master));
    }

    public function test_rehashed_strong_headings_attributes_encoded_tags_multiple_paragraphs_and_short_text_are_still_refused(): void
    {
        [$profile, $old, $body, $master] = $this->reviewed();
        $plain = strip_tags($body);
        foreach (['<p><strong>'.$plain.'</strong></p>', '<h2>'.$plain.'</h2>', '<p style="color:red">'.$plain.'</p>',
            '<p class="new-design">'.$plain.'</p>', '<p>&lt;strong&gt;'.$plain.'&lt;/strong&gt;</p>',
            $body.'<p>Ще один абзац.</p>', '<p>Короткий заголовок.</p>'] as $badBody) {
            $badProfile = $profile; $badMaster = $master; $sha = hash('sha256', $badBody);
            $badProfile['bindings'][2]['after_subtitle']['sha256'] = $sha;
            $badMaster['editorial_review']['post_apply_metadata'] = [['identity' => Package::OWNERS[2]] + $badProfile['bindings'][2]['after_subtitle']];
            $this->refused(fn () => $this->declaration($badProfile, $old, $badBody, $badMaster));
        }
    }

    public function test_explicit_after_metadata_must_be_present_unique_exact_and_owned(): void
    {
        [$profile, $old, $body, $master] = $this->reviewed();
        foreach (['missing', 'duplicate', 'foreign-owner', 'stale-hash', 'non-null-extraction', 'changed-h1'] as $case) {
            $bad = $master;
            if ($case === 'missing') { unset($bad['editorial_review']['post_apply_metadata']); }
            elseif ($case === 'duplicate') { $bad['editorial_review']['post_apply_metadata'][] = $bad['editorial_review']['post_apply_metadata'][0]; }
            elseif ($case === 'foreign-owner') { $bad['editorial_review']['post_apply_metadata'][0]['identity'] = Package::OWNERS[0]; }
            elseif ($case === 'stale-hash') { $bad['editorial_review']['post_apply_metadata'][0]['sha256'] = str_repeat('0', 64); }
            elseif ($case === 'non-null-extraction') { $bad['editorial_review']['post_apply_metadata'][0]['extracted_title'] = 'ed / -ing Adjectives'; }
            else { $bad['editorial_review']['post_apply_metadata'][0]['h1'] = 'ed / -ing Adjectives'; }
            $this->refused(fn () => $this->declaration($profile, $old, $body, $bad));
        }
    }

    public function test_old_m68_profiles_still_validate_literally_and_do_not_gain_a_plain_fallback_option(): void
    {
        [$before, $patch, $master, $contract] = M68ComparisonBasics::load(); $profile = M68ComparisonBasics::profile();
        foreach ($profile['bindings'] as $binding) { self::assertArrayNotHasKey('after_subtitle', $binding); }
        $release = new TheoryNativeContentRelease($profile); $release->validate($before, $patch, $master, $contract);
        $badMaster = $master; $badPatch = $patch;
        $badMaster['lessons'][0]['subtitle_html'] = '<p>'.str_repeat('Змістовний звичайний абзац без заголовка. ', 5).'</p>';
        $badPatch['targets'][0]['after'] = $release->definition($badMaster['lessons'][0], $before['targets'][0]['before']);
        $this->refused(fn () => $release->validate($before, $badPatch, $badMaster, $contract));
    }
}
