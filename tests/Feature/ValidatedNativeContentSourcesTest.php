<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M56GerundSimplePassivesPackage;
use App\Support\M67VerbPatternsCompletion;
use App\Support\M77OnePrepositionsPhrasal;
use App\Support\M83B2RevisionBasicGrammar;
use App\Support\TheoryNativeContentRelease;
use App\Support\ValidatedNativeContentSources;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;
use WeakReference;

final class ValidatedNativeContentSourcesTest extends TestCase
{
    public static function sourceCohorts(): array
    {
        return [
            'four pins, passive legacy wrappers' => [M56GerundSimplePassivesPackage::class, 1, 4, 'test.show', 'uk'],
            'five pins, inline bindings' => [M67VerbPatternsCompletion::class, 0, 5, 'test.step', 'en'],
            'six pins, frozen bindings' => [M77OnePrepositionsPhrasal::class, 1, 6, 'test.step-compose', 'pl'],
            'seven pins, frozen navigation and bindings' => [M83B2RevisionBasicGrammar::class, 1, 7, 'test.show', 'uk'],
        ];
    }

    #[DataProvider('sourceCohorts')]
    public function test_standalone_reuses_a_frozen_tree_but_rebinds_each_block(
        string $package, int $targetIndex, int $artifactCount, string $routeName, string $locale,
    ): void {
        $profile = $package::profile();
        $this->assertCount($artifactCount, $profile['artifacts']);
        [$before, $patch] = (new TheoryNativeContentRelease($profile))->load();
        $target = $patch['targets'][$targetIndex];
        $old = $before['targets'][$targetIndex]['before'];
        $slot = $this->findSlot($target, ['forms-grid', 'usage-panels', 'comparison-table'], $old);
        $block = $this->block($target, $slot);
        $raw = $block->getRawOriginal();
        $key = $this->key('standalone_test', $profile['label']);

        $outside = $this->bindRoute('test.js.questions', $target['slug'], $locale);
        $cold = $package::preserveExcludedTextBlock($block, 'standalone_test');
        $this->assertNotSame($block, $cold);
        $this->assertSame($old['page']['blocks'][$slot]['body'], $cold->body);
        $this->assertFalse($outside->attributes->has($key));

        $request = $this->bindRoute($routeName, $target['slug'], $locale);
        $this->assertSame($cold->getAttributes(), $package::preserveExcludedTextBlock($block, 'standalone_test')->getAttributes());
        $cache = $request->attributes->get($key);
        $this->assertSame($request, $cache['request']->get());
        $this->assertSame($profile, $cache['profile']);
        $probe = new stdClass;
        $request->attributes->set($key, $cache + ['test_probe' => $probe]);

        $this->assertSame($cold->getAttributes(), $package::preserveExcludedTextBlock($block, 'standalone_test')->getAttributes());
        foreach (['uuid' => 'different', 'page_id' => 0, 'page_category_id' => 0, 'body' => $raw['body']."\n",
            'sort_order' => 0, 'seeder' => 'DifferentSeeder', 'locale' => 'pl'] as $name => $bad) {
            $invalid = (new TextBlock)->setRawAttributes(array_replace($raw, [$name => $bad]), true);
            $this->assertSame($invalid, $package::preserveExcludedTextBlock($invalid, 'standalone_test'), $name);
        }
        $this->assertSame($probe, $request->attributes->get($key)['test_probe']);
        $this->assertSame($raw, $block->getAttributes());
        $this->assertSame($raw, $block->getRawOriginal());
    }

    public function test_memo_is_separated_by_request_app_root_route_context_and_full_profile(): void
    {
        $profile = ['label' => 'M56', 'version' => 'verified', 'bindings' => [['id' => 42]], 'artifacts' => ['x.json' => 'sha']];
        $counts = ['load' => 0, 'verify' => 0];
        $load = static function (string $root) use (&$counts): array { return [$root, ++$counts['load']]; };
        $verify = static function (string $root) use (&$counts): void { $counts['verify']++; };
        $read = static fn (array $p, string $caller = 'standalone_test') => ValidatedNativeContentSources::read($p, $caller, $load, $verify);
        $request = $this->bindRoute('test.show');
        $key = $this->key('standalone_test', 'M56');
        $this->assertSame([base_path(), 1], $read($profile));
        $this->assertSame([base_path(), 1], $read($profile));
        $this->assertSame(['load' => 1, 'verify' => 1], $counts);

        $copy = $request->duplicate();
        $this->assertSame($request, $copy->attributes->get($key)['request']->get());
        $this->app->instance('request', $copy);
        $this->assertSame([base_path(), 2], $read($profile));
        $this->assertSame($copy, $copy->attributes->get($key)['request']->get());
        $this->assertSame($request, $request->attributes->get($key)['request']->get());

        $foreignApp = new stdClass;
        foreach (['app' => WeakReference::create($foreignApp), 'root' => base_path('other-root')] as $keyPart => $wrong) {
            $cached = $copy->attributes->get($key);
            $copy->attributes->set($key, array_replace($cached, [$keyPart => $wrong]));
            $expectedLoad = $counts['load'] + 1;
            $this->assertSame([base_path(), $expectedLoad], $read($profile));
            $recovered = $copy->attributes->get($key)[$keyPart];
            $this->assertSame($keyPart === 'app' ? $this->app : base_path(),
                $keyPart === 'app' ? $recovered->get() : $recovered);
        }

        $newRoute = clone $copy->route();
        $copy->setRouteResolver(static fn () => $newRoute);
        $this->assertSame([base_path(), 5], $read($profile));
        $this->assertSame($newRoute, $copy->attributes->get($key)['route']->get());
        $newRoute->setParameter('slug', 'other-standalone-slug');
        $this->assertSame([base_path(), 6], $read($profile));
        $this->app->setLocale('pl');
        $this->assertSame([base_path(), 7], $read($profile));
        $this->assertSame([base_path(), 8], $read(array_replace($profile, ['version' => 'new'])));
        $this->assertSame([base_path(), 9], $read(array_replace($profile, ['bindings' => [['id' => '42']]])));
        $this->assertSame([base_path(), 10], $read(array_replace($profile, ['artifacts' => ['x.json' => 'new-sha']])));
        $this->assertSame([base_path(), 11], $read($profile, 'canonical_theory'));
        $this->assertFalse($copy->attributes->has($this->key('canonical_theory', 'M56')));
        $this->assertSame(['load' => 11, 'verify' => 1], $counts);
    }

    public function test_no_memo_is_opened_outside_supported_route_contexts_or_cohorts(): void
    {
        $verified = static function (string $root): void {
            self::fail('A cold or unsupported request must not use the warm source callback.');
        };
        foreach ([
            ['M52', 'test.show', 'uk', 'GET', 'standalone_test', 'exact-page'],
            ['M94', 'test.show', 'uk', 'GET', 'standalone_test', 'exact-page'],
            ['M56', 'test.js.questions', 'uk', 'GET', 'standalone_test', 'exact-page'],
            ['M56', 'test.show', 'uk', 'POST', 'standalone_test', 'exact-page'],
            ['M56', 'test.show', 'de', 'GET', 'standalone_test', 'exact-page'],
            ['M56', 'test.show', 'uk', 'GET', 'standalone_test', 'invalid_page'],
            ['M56', 'test.show', 'uk', 'GET', 'course_page', 'exact-page'],
            ['M56', 'theory.show', 'en', 'GET', 'canonical_theory', 'exact-page'],
            ['M56', 'theory.show', 'uk', 'GET', 'standalone_test', 'exact-page'],
        ] as [$label, $name, $locale, $method, $caller, $slug]) {
            $request = $this->bindRoute($name, $slug, $locale, $method);
            $saved = $request->attributes->all();
            $calls = 0;
            $loader = static function (string $root) use (&$calls): array { return [++$calls]; };
            $this->assertSame([1], ValidatedNativeContentSources::read(['label' => $label], $caller, $loader, $verified));
            $this->assertSame([2], ValidatedNativeContentSources::read(['label' => $label], $caller, $loader, $verified));
            $this->assertSame($saved, $request->attributes->all());
        }
        $request = Request::create('http://gramlyze.loc/test/exact-page');
        $this->app->instance('request', $request);
        $this->assertSame(['cold'], ValidatedNativeContentSources::read(['label' => 'M56'], 'standalone_test', static fn () => ['cold'], $verified));
        $this->assertSame([], $request->attributes->all());
    }

    public function test_each_changed_artifact_immediately_invalidates_the_same_request_and_failures_are_not_memoized(): void
    {
        $profile = $this->privateProfile(M56GerundSimplePassivesPackage::profile());
        $release = new TheoryNativeContentRelease($profile);
        [$before, $patch] = $release->load();
        $target = $patch['targets'][1];
        $old = $before['targets'][1]['before'];
        $slot = $this->findSlot($target, ['forms-grid', 'usage-panels'], $old);
        $block = $this->block($target, $slot);
        $request = $this->bindRoute('test.show', $target['slug']);
        $key = $this->key('standalone_test', $profile['label']);
        foreach (array_keys($profile['artifacts']) as $relative) {
            $this->assertSame($old['page']['blocks'][$slot]['body'], $release->preserveExcludedTextBlock($block, 'standalone_test')->body);
            $this->assertTrue($request->attributes->has($key));
            $file = base_path($relative);
            IsolatedTestEnvironment::assertOwnedPath($file);
            $bytes = file_get_contents($file);
            $this->assertIsString($bytes);
            try {
                $this->assertNotFalse(file_put_contents($file, $bytes."\n"));
                $this->assertSame($block, $release->preserveExcludedTextBlock($block, 'standalone_test'), $relative);
                $this->assertFalse($request->attributes->has($key), $relative);
                $this->assertNotFalse(file_put_contents($file, $bytes));
                $this->assertSame($old['page']['blocks'][$slot]['body'], $release->preserveExcludedTextBlock($block, 'standalone_test')->body);
                $this->assertTrue($request->attributes->has($key), $relative);
                $this->assertTrue(unlink($file));
                $this->assertSame($block, $release->preserveExcludedTextBlock($block, 'standalone_test'), $relative);
                $this->assertFalse($request->attributes->has($key), $relative);
            } finally {
                $this->assertNotFalse(file_put_contents($file, $bytes));
            }
        }
        $this->assertSame($old['page']['blocks'][$slot]['body'], $release->preserveExcludedTextBlock($block, 'standalone_test')->body);
        $this->assertTrue($request->attributes->has($key));

        $first = array_key_first($profile['artifacts']);
        $file = base_path($first);
        IsolatedTestEnvironment::assertOwnedPath($file);
        $original = file_get_contents($file);
        $this->assertIsString($original);
        $bad = json_encode(array_replace($before, ['schema_version' => 999]), flags: JSON_THROW_ON_ERROR);
        try {
            $equivalent = $original."\n";
            $newProfile = $profile;
            $newProfile['artifacts'][$first] = hash('sha256', $equivalent);
            $this->assertNotFalse(file_put_contents($file, $equivalent));
            $refreshed = new TheoryNativeContentRelease($newProfile);
            $this->assertSame($old['page']['blocks'][$slot]['body'], $refreshed->preserveExcludedTextBlock($block, 'standalone_test')->body);
            $this->assertSame($newProfile, $request->attributes->get($key)['profile']);
            $this->assertSame($block, $release->preserveExcludedTextBlock($block, 'standalone_test'));

            $this->assertNotFalse(file_put_contents($file, $bad));
            $profile['artifacts'][$first] = hash('sha256', $bad);
            $repinned = new TheoryNativeContentRelease($profile);
            $freshRequest = $this->bindRoute('test.show', $target['slug']);
            $this->assertSame($block, $repinned->preserveExcludedTextBlock($block, 'standalone_test'));
            $this->assertFalse($freshRequest->attributes->has($key));
        } finally {
            $this->assertNotFalse(file_put_contents($file, $original));
        }
    }

    public function test_canonical_theory_presentation_panels_and_correction_preserve_fallback_on_artifact_loss(): void
    {
        $profile = $this->privateProfile(M56GerundSimplePassivesPackage::profile());
        $release = new TheoryNativeContentRelease($profile);
        [, $patch] = $release->load();
        $target = $patch['targets'][1];
        $panel = $this->block($target, $this->findSlot($target, ['usage-panels']));
        $grid = $this->block($target, $this->findSlot($target, ['mistakes-grid']));
        $panelData = json_decode($panel->body, true, flags: JSON_THROW_ON_ERROR);
        $gridData = json_decode($grid->body, true, flags: JSON_THROW_ON_ERROR);

        $this->bindRoute('theory.category', $target['slug']);
        $panelPresentation = $release->presentation($panel, $panelData);
        $gridPresentation = $release->presentation($grid, $gridData);
        $this->assertNotNull($panelPresentation);
        $this->assertNotNull($gridPresentation);
        $panelDesign = $panelPresentation['m42_native_design'];
        $gridDesign = $gridPresentation['m42_native_design'];
        $points = $release->pointPanels($panelDesign);
        $correction = $release->correction($gridData['items'][0], $gridDesign);
        $this->assertNotNull($points);
        $this->assertNotNull($correction);

        $request = $this->bindRoute('theory.show', $target['slug']);
        $key = $this->key('canonical_theory', 'M56');
        $this->assertSame($panelPresentation, $release->presentation($panel, $panelData));
        $this->assertSame($points, $release->pointPanels($panelDesign));
        $this->assertSame($gridPresentation, $release->presentation($grid, $gridData));
        $warmCorrection = $release->correction($gridData['items'][0], $gridDesign);
        $this->assertNotNull($warmCorrection);
        $this->assertSame(array_map('strval', $correction), array_map('strval', $warmCorrection));
        $this->assertTrue($request->attributes->has($key));
        $this->assertFalse($request->attributes->has($this->key('standalone_test', 'M56')));
        $this->assertFalse($release->validDesign(array_replace($panelDesign, ['uuid' => 'invalid'])));

        $file = base_path(array_key_last($profile['artifacts']));
        IsolatedTestEnvironment::assertOwnedPath($file);
        $original = file_get_contents($file);
        $this->assertIsString($original);
        try {
            $this->assertTrue(unlink($file));
            $this->assertNull($release->presentation($panel, $panelData));
            $this->assertFalse($request->attributes->has($key));
            $this->assertNull($release->pointPanels($panelDesign));
            $this->assertNull($release->correction($gridData['items'][0], $gridDesign));
            $this->assertFalse($request->attributes->has($key));
        } finally {
            $this->assertNotFalse(file_put_contents($file, $original));
        }
        $this->assertSame($panelPresentation, $release->presentation($panel, $panelData));
    }

    private function key(string $caller, string $label): string
    {
        return ValidatedNativeContentSources::class.':'.$caller.':'.$label;
    }

    private function bindRoute(string $name, string $slug = 'sample-test', string $locale = 'uk', string $method = 'GET'): Request
    {
        $prefix = $locale === 'uk' ? '' : '/'.$locale;
        $suffix = match ($name) {
            'test.step' => '/step', 'test.step-compose' => '/step/compose', 'test.js.questions' => '/questions', default => '',
        };
        $theory = str_starts_with($name, 'theory.');
        $pattern = $theory ? '/theory/{categoryPath}/{pageSlug}' : '/test/{slug}'.$suffix;
        $path = $theory ? '/theory/passive-voice/'.$slug : '/test/'.$slug.$suffix;
        $request = Request::create('http://gramlyze.loc'.$prefix.$path, $method);
        $route = new Route([$method], $prefix.$pattern, static fn () => null);
        $route->name($name)->where('slug', '.*')->bind($request);
        $request->setRouteResolver(static fn () => $route);
        $this->app->instance('request', $request);
        $this->app->setLocale($locale);

        return $request;
    }

    private function findSlot(array $target, array $types, ?array $old = null): int
    {
        foreach ($target['after']['page']['blocks'] as $slot => $source) {
            if (in_array($source['type'], $types, true) && ($old === null || isset($old['page']['blocks'][$slot]))) {
                return $slot;
            }
        }
        self::fail('Missing exact canonical fixture slot for '.$target['identity']);
    }

    private function block(array $target, int $slot): TextBlock
    {
        $source = $target['after']['page']['blocks'][$slot];
        return (new TextBlock)->setRawAttributes([
            'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'page_id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
            'locale' => 'uk', 'seeder' => $target['identity'], 'sort_order' => $slot + 1,
            'body' => $source['body'], 'type' => $source['type'], 'column' => $source['column'] ?? null,
            'heading' => $source['heading'] ?? null, 'level' => $source['level'] ?? null, 'css_class' => $source['css_class'] ?? null,
        ], true);
    }

    private function privateProfile(array $profile): array
    {
        $root = str_replace('\\', '/', IsolatedTestEnvironment::prepare());
        $base = str_replace('\\', '/', base_path());
        $this->assertStringStartsWith($base.'/', $root);
        $directory = substr($root, strlen($base) + 1).'/perf25-'.bin2hex(random_bytes(8));
        $pins = [];
        foreach ($profile['artifacts'] as $relative => $sha) {
            $copy = $directory.'/'.$relative;
            $parent = dirname(base_path($copy));
            if (! is_dir($parent)) { $this->assertTrue(mkdir($parent, 0777, true)); }
            IsolatedTestEnvironment::assertOwnedPath($parent);
            $this->assertTrue(copy(base_path($relative), base_path($copy)));
            $pins[$copy] = $sha;
        }
        return array_replace($profile, ['artifacts' => $pins]);
    }
}
