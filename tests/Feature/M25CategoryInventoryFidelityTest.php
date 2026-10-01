<?php

namespace Tests\Feature;

use App\Models\PageCategory;
use App\Models\TextBlock;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class M25CategoryInventoryFidelityTest extends TestCase
{
    public function test_all_versioned_category_descriptions_render_without_source_changes(): void
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
            $frozen = $private.'/baseline-views';
            $this->assertDirectoryExists($frozen);
            app('view')->getFinder()->setPaths([$frozen, resource_path('views')]);
            app('view')->getFinder()->flush();
        }
        $paths = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(database_path('seeders/Page_V3'))) as $file) {
            if ($file->isFile() && $file->getFilename() === 'definition.json') $paths[] = $file->getPathname();
        }
        sort($paths); $rows = []; $sources = [];
        foreach ($paths as $path) {
            $definition = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            if (!isset($definition['description']['blocks'])) continue;
            $hash = hash_file('sha256', $path);
            $identity = str_replace('\\', '/', substr($path, strlen(base_path()) + 1));
            $sources[$identity] = $hash;
            $page = new PageCategory($definition['category']);
            $blockData = $definition['description']['blocks'];
            array_unshift($blockData, ['type' => 'subtitle', 'body' => $definition['description']['subtitle_html'] ?? '', 'sort_order' => 0]);
            $blocks = collect($blockData)->map(function ($data, $index) {
                $block = new TextBlock($data); $block->id = $index + 1;
                $block->setRelation('tags', collect());
                return $block;
            });
            $original = $blocks->map->getAttributes()->all();
            $html = view('theory.partials.category-description', ['page' => $page,
                'categoryDescription' => ['blocks' => $blocks, 'subtitleBlock' => $blocks->first(), 'lessonLinks' => []]])->render();
            $this->assertNotEmpty($html);
            $this->assertStringNotContainsString('data-theory-details', $html);
            $this->assertSame($original, $blocks->map->getAttributes()->all());
            $this->assertSame($hash, hash_file('sha256', $path));
            $rows[] = ['identity' => $identity, 'source_sha256' => $hash,
                'block_types' => $blocks->pluck('type')->all(), 'html' => ['theory.partials.category-description' => $html]];
        }
        $this->assertNotEmpty($rows);
        if ($mode !== '') {
            $destination = $private.'/'.$label.'-category-render.json';
            $this->assertFileDoesNotExist($destination);
            file_put_contents($destination, json_encode(['at' => gmdate('c'), 'count' => count($rows),
                'sources' => $sources, 'rows' => $rows], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        }
    }
}
