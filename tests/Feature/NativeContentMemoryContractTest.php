<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M56GerundSimplePassivesPackage;
use App\Support\M71ArticlesQuantifiers;
use App\Support\M85VerbToBe;
use App\Support\TheoryNativeContentRelease;
use App\Support\ValidatedNativeContentSources;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use stdClass;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

final class NativeContentMemoryContractTest extends TestCase
{
    public function test_two_compose_packages_keep_independent_proofs_when_one_tree_is_released_or_its_file_changes(): void
    {
        [$verbProfile, $artifact] = $this->privateProfile(M85VerbToBe::profile());
        $articleProfile = M71ArticlesQuantifiers::profile();
        $verb = new TheoryNativeContentRelease($verbProfile);
        $article = new TheoryNativeContentRelease($articleProfile);
        [$verbBlock, $verbBody] = $this->continuingBlock($verb);
        [$articleBlock, $articleBody] = $this->continuingBlock($article);
        $verbKey = $this->key('standalone_test', $verbProfile['label']);
        $articleKey = $this->key('standalone_test', $articleProfile['label']);
        $request = $this->bind('test.step-compose', '/pl/test/{slug}/step/compose',
            '/pl/test/sentence-builder-to-be-a1/step/compose', 'pl');

        $this->assertSame($verbBody, $verb->preserveExcludedTextBlock($verbBlock, 'standalone_test')?->body);
        $this->assertSame($articleBody, $article->preserveExcludedTextBlock($articleBlock, 'standalone_test')?->body);
        foreach ([$verbKey, $articleKey] as $key) {
            [$before, $patch, $master] = $request->attributes->get($key)['sources'];
            $this->assertIsArray($before);
            $this->assertIsArray($patch);
            $this->assertNull($master);
        }
        $oldVerb = $this->markProof($request, $verbKey);
        $otherProof = $this->markProof($request, $articleKey);

        $request->attributes->remove($verbKey);
        $this->assertSame($articleBody, $article->preserveExcludedTextBlock($articleBlock, 'standalone_test')?->body);
        $this->assertSame($otherProof, $request->attributes->get($articleKey)['test_memory_proof'] ?? null);
        $this->assertSame($verbBody, $verb->preserveExcludedTextBlock($verbBlock, 'standalone_test')?->body);
        $this->assertTrue($request->attributes->has($verbKey));
        $this->assertNotSame($oldVerb, $request->attributes->get($verbKey)['test_memory_proof'] ?? null);

        $bytes = $this->ownedBytes($artifact);
        try {
            $this->assertSame(strlen($bytes) + 1, file_put_contents($artifact, $bytes."\n"));
            $this->assertSame($verbBlock, $verb->preserveExcludedTextBlock($verbBlock, 'standalone_test'));
            $this->assertFalse($request->attributes->has($verbKey));
            $this->assertSame($articleBody, $article->preserveExcludedTextBlock($articleBlock, 'standalone_test')?->body);
            $this->assertSame($otherProof, $request->attributes->get($articleKey)['test_memory_proof'] ?? null);

            $this->assertSame(strlen($bytes), file_put_contents($artifact, $bytes));
            $this->assertSame($verbBody, $verb->preserveExcludedTextBlock($verbBlock, 'standalone_test')?->body);
            $this->assertTrue($request->attributes->has($verbKey));

            $this->assertTrue(unlink($artifact));
            $this->assertSame($verbBlock, $verb->preserveExcludedTextBlock($verbBlock, 'standalone_test'));
            $this->assertFalse($request->attributes->has($verbKey));
            $this->assertSame($articleBody, $article->preserveExcludedTextBlock($articleBlock, 'standalone_test')?->body);
            $this->assertSame($otherProof, $request->attributes->get($articleKey)['test_memory_proof'] ?? null);
        } finally {
            $this->assertSame(strlen($bytes), file_put_contents($artifact, $bytes));
        }
        $this->assertSame($verbBody, $verb->preserveExcludedTextBlock($verbBlock, 'standalone_test')?->body);
        $this->assertTrue($request->attributes->has($verbKey));
        $this->assertSame($request, app('request'));
    }

    public function test_theory_helpers_return_exact_frozen_content_and_do_not_use_it_after_the_artifact_changes(): void
    {
        [$profile, $artifact] = $this->privateProfile(M56GerundSimplePassivesPackage::profile());
        $release = new TheoryNativeContentRelease($profile);
        [, $patch] = $release->load();
        $target = $patch['targets'][1];
        [$panel, $grid] = [$this->blockOfType($target, 'usage-panels'), $this->blockOfType($target, 'mistakes-grid')];
        $panelData = json_decode($panel->body, true, flags: JSON_THROW_ON_ERROR);
        $gridData = json_decode($grid->body, true, flags: JSON_THROW_ON_ERROR);
        $item = $gridData['items'][0];
        $url = '/theory/passive-voice/'.$target['slug'];
        $pattern = '/theory/{categoryPath}/{pageSlug}';

        // A route that is ineligible for the request memo still exercises the identical frozen data path.
        $this->bind('theory.category', $pattern, $url, 'uk');
        $panelPresentation = $release->presentation($panel, $panelData);
        $gridPresentation = $release->presentation($grid, $gridData);
        $this->assertNotNull($panelPresentation);
        $this->assertNotNull($gridPresentation);
        $panelDesign = $panelPresentation['m42_native_design'];
        $gridDesign = $gridPresentation['m42_native_design'];
        $expectedPanels = $release->pointPanels($panelDesign);
        $expectedCorrection = $release->correction($item, $gridDesign);
        $this->assertNotNull($expectedPanels);
        $this->assertNotNull($expectedCorrection);

        $request = $this->bind('theory.show', $pattern, $url, 'uk');
        $key = $this->key('canonical_theory', $profile['label']);
        $this->assertSame($panelPresentation, $release->presentation($panel, $panelData));
        $this->assertSame($gridPresentation, $release->presentation($grid, $gridData));
        $this->assertSame($expectedPanels, $release->pointPanels($panelDesign));
        $this->assertSame(array_map('strval', $expectedCorrection), array_map('strval', $release->correction($item, $gridDesign) ?? []));
        $this->assertNull($release->pointPanels(array_replace($panelDesign, ['body_sha256' => str_repeat('0', 64)])));
        $this->assertNull($release->correction(array_replace($item, ['wrong_en' => $item['wrong_en'].' Unreviewed.']), $gridDesign));
        $this->assertTrue($request->attributes->has($key));
        [$before, $cachedPatch, $master] = $request->attributes->get($key)['sources'];
        $this->assertNull($before);
        $this->assertIsArray($cachedPatch);
        $this->assertIsArray($master);

        $bytes = $this->ownedBytes($artifact);
        try {
            $this->assertSame(1, file_put_contents($artifact, '{'));
            $this->assertNull($release->pointPanels($panelDesign));
            $this->assertNull($release->correction($item, $gridDesign));
            $this->assertFalse($request->attributes->has($key));

            $this->assertSame(strlen($bytes), file_put_contents($artifact, $bytes));
            $this->assertSame($expectedPanels, $release->pointPanels($panelDesign));
            $this->assertTrue($request->attributes->has($key));

            $this->assertTrue(unlink($artifact));
            $this->assertNull($release->correction($item, $gridDesign));
            $this->assertFalse($request->attributes->has($key));
        } finally {
            $this->assertSame(strlen($bytes), file_put_contents($artifact, $bytes));
        }
        $this->assertSame(array_map('strval', $expectedCorrection), array_map('strval', $release->correction($item, $gridDesign) ?? []));
        $this->assertTrue($request->attributes->has($key));
    }

    private function markProof(Request $request, string $key): stdClass
    {
        $memo = $request->attributes->get($key);
        $this->assertIsArray($memo);
        $probe = new stdClass;
        $request->attributes->set($key, $memo + ['test_memory_proof' => $probe]);
        return $probe;
    }

    private function key(string $caller, string $label): string
    {
        return ValidatedNativeContentSources::class.':'.$caller.':'.$label;
    }

    private function bind(string $name, string $pattern, string $path, string $locale): Request
    {
        $request = Request::create('http://gramlyze.loc'.$path);
        $route = new Route(['GET'], $pattern, static fn () => null);
        $route->name($name)->where('slug', '.*')->bind($request);
        $request->setRouteResolver(static fn () => $route);
        $this->app->instance('request', $request);
        $this->app->setLocale($locale);
        return $request;
    }

    private function continuingBlock(TheoryNativeContentRelease $release): array
    {
        [$before, $patch] = $release->load();
        $target = $patch['targets'][0];
        $old = $before['targets'][0]['before']['page']['blocks'];
        foreach ($target['after']['page']['blocks'] as $slot => $source) {
            if (isset($old[$slot]) && $source['body'] !== $old[$slot]['body']
                && in_array($source['type'], ['forms-grid', 'usage-panels', 'comparison-table'], true)) {
                return [$this->block($target, $slot), $old[$slot]['body']];
            }
        }
        self::fail('No changed continuing canonical block for '.$target['identity']);
    }

    private function blockOfType(array $target, string $type): TextBlock
    {
        foreach ($target['after']['page']['blocks'] as $slot => $source) {
            if ($source['type'] === $type) { return $this->block($target, $slot); }
        }
        self::fail('Missing canonical '.$type.' block for '.$target['identity']);
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
        $directory = substr($root, strlen($base) + 1).'/perf26-'.bin2hex(random_bytes(8));
        $pins = [];
        foreach ($profile['artifacts'] as $relative => $sha) {
            $copy = $directory.'/'.$relative;
            $parent = dirname(base_path($copy));
            if (! is_dir($parent)) { $this->assertTrue(mkdir($parent, 0777, true)); }
            IsolatedTestEnvironment::assertOwnedPath($parent);
            $this->assertTrue(copy(base_path($relative), base_path($copy)));
            $pins[$copy] = $sha;
        }
        return [array_replace($profile, ['artifacts' => $pins]), base_path(array_key_last($pins))];
    }

    private function ownedBytes(string $file): string
    {
        IsolatedTestEnvironment::assertOwnedPath($file);
        $bytes = file_get_contents($file);
        $this->assertIsString($bytes);
        return $bytes;
    }
}
