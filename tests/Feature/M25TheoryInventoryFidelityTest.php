<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/** Every versioned Page_V3 lesson renders in an isolated kernel, not live MySQL. */
class M25TheoryInventoryFidelityTest extends TestCase
{
    public function test_every_versioned_lesson_renders_without_modifying_its_source(): void
    {
        $this->withoutVite();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        app()->setLocale('uk');
        $mode = getenv('GRAMLYZE_M25_CAPTURE') ?: '';
        $this->assertContains($mode, ['', 'before', 'after']);
        $label = getenv('GRAMLYZE_M25_CAPTURE_TAG') ?: $mode;
        if ($mode !== '') $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/D', $label);
        $private = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m25-local';
        if ($mode === 'before') {
            // Capture the unchanged ACTUAL working implementation, not new code
            // masquerading as its own baseline. Only private evidence is written.
            $frozen = $private.'/baseline-views';
            $workingViews = 'D:/DEV/htdocs/gramlyze.loc/resources/views';
            if (!is_dir($frozen)) foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($workingViews)) as $file) {
                if (!$file->isFile()) continue;
                $relative = substr($file->getPathname(), strlen($workingViews) + 1);
                $destination = $frozen.'/'.$relative;
                if (!is_dir(dirname($destination))) mkdir(dirname($destination), 0700, true);
                copy($file->getPathname(), $destination);
            }
            app('view')->getFinder()->setPaths([$frozen, resource_path('views')]);
            app('view')->getFinder()->flush();
        }
        $paths = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(database_path('seeders/Page_V3'))) as $file) {
            if ($file->isFile() && $file->getFilename() === 'definition.json') $paths[] = $file->getPathname();
        }
        sort($paths);
        $sources = []; $rows = []; $types = [];
        foreach ($paths as $path) {
            $definition = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            if (!isset($definition['page']['blocks'])) continue;
            $hash = hash_file('sha256', $path);
            $identity = str_replace('\\', '/', substr($path, strlen(base_path()) + 1));
            $sources[$identity] = $hash;
            $page = new Page($definition['page']);
            $page->setRawAttributes(array_merge($page->getAttributes(), ['seeder' => $definition['seeder']['class'] ?? '']));
            $page->setRelation('tags', collect());
            $blocks = collect($definition['page']['blocks'])->map(function ($data, $index) use (&$types) {
                $block = new TextBlock($data);
                $block->id = $index + 1;
                $block->uuid = 'm25-fixture-block-'.$index;
                $block->setRelation('tags', collect());
                $types[$block->type ?: '[null]'] = true;
                return $block;
            });
            $page->setRelation('textBlocks', $blocks);
            $original = $blocks->map->getAttributes()->all();
            $html = [];
            foreach (['theory.show', 'courses.partials.theory-page-content'] as $view) {
                $html[$view] = view($view, ['page' => $page, 'categories' => collect(), 'categoryPages' => collect(), 'topicTests' => collect()])->render();
                $this->assertNotEmpty($html[$view], $identity.' '.$view);
                $this->assertStringNotContainsString('data-theory-details', $html[$view], 'Real source must remain full content-only');
            }
            $this->assertSame($original, $blocks->map->getAttributes()->all(), $identity.' in-memory source changed');
            $this->assertSame($hash, hash_file('sha256', $path), $identity.' source bytes changed');
            $rows[] = ['identity' => $identity, 'source_sha256' => $hash, 'block_types' => $blocks->pluck('type')->all(), 'html' => $html];
        }
        foreach (['m23', 'm24'] as $package) {
            $path = base_path('docs/content/'.$package.'-authored-content.v1.json');
            $sources['docs/content/'.$package.'-authored-content.v1.json'] = hash_file('sha256', $path);
        }
        $this->assertNotEmpty($rows);
        if ($mode !== '') {
            $destination = $private.'/'.$label.'-isolated-render.json';
            $this->assertFileDoesNotExist($destination);
            file_put_contents($destination, json_encode(['at' => gmdate('c'), 'sources' => $sources,
                'count' => count($rows), 'types' => array_keys($types), 'rows' => $rows], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        }
    }
}
