<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use Tests\TestCase;

/** Private public-learning snapshots only; never a connection to working MySQL. */
class M25ActualRecordFidelityTest extends TestCase
{
    public function test_every_captured_actual_locale_and_category_uses_the_isolated_renderer(): void
    {
        $this->withoutVite();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $private = 'D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m25-local';
        if (!is_file($private.'/before-inventory.json')) {
            $this->markTestSkipped('Actual-record fidelity requires the private SELECT-only M25 inventory; it is never committed.');
        }
        $inventory = json_decode(file_get_contents($private.'/before-inventory.json'), true, 512, JSON_THROW_ON_ERROR);
        $mode = getenv('GRAMLYZE_M25_CAPTURE') ?: '';
        $this->assertContains($mode, ['', 'before', 'after']);
        $label = getenv('GRAMLYZE_M25_CAPTURE_TAG') ?: $mode;
        if ($mode !== '') $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/D', $label);
        if ($mode === 'before') {
            $frozen = $private.'/baseline-views';
            $this->assertDirectoryExists($frozen);
            app('view')->getFinder()->setPaths([$frozen, resource_path('views')]);
            app('view')->getFinder()->flush();
        }
        $categories = collect($inventory['data']['categories'])->keyBy('id');
        $allBlocks = collect($inventory['data']['blocks']);
        $rowCount = 0; $blockCount = 0; $handle = null;
        if ($mode !== '') {
            $destination = $private.'/'.$label.'-actual-record-render.json';
            $this->assertFileDoesNotExist($destination);
            $handle = fopen($destination, 'x');
            fwrite($handle, '{"rows":[');
        }
        $record = static function (array $row) use (&$rowCount, $handle): void {
            if ($handle) fwrite($handle, ($rowCount ? ',' : '').json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            $rowCount++;
        };
        foreach ($inventory['data']['pages'] as $data) {
            foreach ($allBlocks->where('page_id', $data['id'])->groupBy('locale') as $locale => $blockData) {
                app()->setLocale($locale);
                $page = new Page;
                $page->setRawAttributes($data);
                $page->setRelation('tags', collect());
                $category = new PageCategory;
                $category->setRawAttributes($categories[$data['page_category_id']]);
                $category->setRelation('tags', collect());
                $page->setRelation('category', $category);
                $blocks = $blockData->sortBy('sort_order')->values()->map(function ($data) {
                    $block = new TextBlock; $block->setRawAttributes($data);
                    $block->setRelation('tags', collect());
                    return $block;
                });
                $page->setRelation('textBlocks', $blocks);
                $original = $blocks->map->getAttributes()->all();
                $html = [];
                foreach (['theory.show', 'courses.partials.theory-page-content'] as $view) {
                    $html[$view] = view($view, ['page' => $page, 'selectedCategory' => $category,
                        'categories' => collect(), 'categoryPages' => collect(), 'topicTests' => collect()])->render();
                    $this->assertNotEmpty($html[$view]);
                    $this->assertStringNotContainsString('data-theory-details', $html[$view]);
                }
                $this->assertSame($original, $blocks->map->getAttributes()->all());
                $blockCount += $blocks->count();
                $record(['identity' => 'page-'.$data['id'].':'.$locale, 'kind' => 'lesson',
                    'locale' => $locale, 'path' => $inventory['page_paths'][$data['id']],
                    'block_ids' => $blocks->pluck('id')->all(), 'html' => $html]);
            }
        }
        foreach ($inventory['data']['categories'] as $data) {
            foreach ($allBlocks->whereNull('page_id')->where('page_category_id', $data['id'])->groupBy('locale') as $locale => $blockData) {
                app()->setLocale($locale);
                $page = new PageCategory; $page->setRawAttributes($data);
                $blocks = $blockData->sortBy('sort_order')->values()->map(function ($data) {
                    $block = new TextBlock; $block->setRawAttributes($data);
                    $block->setRelation('tags', collect());
                    return $block;
                });
                $description = ['blocks' => $blocks, 'subtitleBlock' => $blocks->firstWhere('type', 'subtitle'), 'lessonLinks' => []];
                $html = view('theory.partials.category-description', ['page' => $page, 'categoryDescription' => $description])->render();
                $this->assertNotEmpty($html);
                $blockCount += $blocks->count();
                $record(['identity' => 'category-'.$data['id'].':'.$locale, 'kind' => 'category',
                    'locale' => $locale, 'block_ids' => $blocks->pluck('id')->all(),
                    'html' => ['theory.partials.category-description' => $html]]);
            }
        }
        $this->assertSame($inventory['counts']['blocks'], $blockCount);
        if ($handle) {
            fwrite($handle, '],"count":'.$rowCount.',"blocks":'.$blockCount.',"content_sha256":"'.$inventory['combined_sha256'].'","at":"'.gmdate('c').'"}');
            fclose($handle);
        }
    }
}
