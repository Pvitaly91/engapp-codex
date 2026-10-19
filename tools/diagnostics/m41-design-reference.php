<?php
declare(strict_types=1);

// Isolated, unsaved pre-M41 native cards. Never bootstrap the working MySQL DB.
require __DIR__.'/../../vendor/autoload.php';
$root = dirname(__DIR__, 2);
$out = $argv[1] ?? '';
if (basename($out) !== 'seo-m41-design-reference' || !is_dir($out)) {
    throw new RuntimeException('Existing private reference directory required.');
}
foreach (['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array', 'APP_URL' => 'http://gramlyze.loc'] as $key => $value) {
    putenv($key.'='.$value); $_ENV[$key] = $_SERVER[$key] = $value;
}
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
    'cache.default' => 'array', 'session.driver' => 'array', 'view.compiled' => $out.'/compiled']);
$app->instance('request', Illuminate\Http\Request::create('http://gramlyze.loc/', 'GET'));
$app->setLocale('uk');
Illuminate\Support\Facades\DB::purge();
$queries = 0;
Illuminate\Support\Facades\DB::listen(function () use (&$queries): void {
    $queries++; throw new RuntimeException('Reference renderer attempted a database query.');
});
view()->getFinder()->prependLocation($out.'/views');
$index = json_decode(file_get_contents($out.'/sources.json'), true, flags: JSON_THROW_ON_ERROR);
$rows = [];
foreach ($index['definitions'] as $definition) {
    $source = json_decode(file_get_contents($out.'/'.$definition['private_file']), true, flags: JSON_THROW_ON_ERROR);
    $html = ''; $types = []; $tags = [];
    foreach ($source['page']['blocks'] as $slot => $raw) {
        $view = App\Support\TheoryPresentation::nativeView($raw['type']);
        if ($view === null) { continue; }
        $block = new App\Models\TextBlock;
        $block->forceFill(['id' => 99000 + $slot, 'uuid' => App\Support\M26DetailPackage::uuid($definition['identity'], $raw, $slot + 1),
            'seeder' => $definition['identity'], 'locale' => 'uk', 'sort_order' => $slot + 1,
            'type' => $raw['type'], 'body' => $raw['body'], 'level' => $raw['level'] ?? null,
            'heading' => $raw['heading'] ?? null, 'column' => $raw['column']]);
        $blockTags = collect($raw['tags'] ?? [])->values()->map(function ($name, $i) {
            $tag = new App\Models\Tag; $tag->forceFill(['id' => 90000 + $i, 'name' => is_array($name) ? ($name['name'] ?? '') : $name]); return $tag;
        });
        $block->setRelation('tags', $blockTags); $block->setRelation('page', null);
        $data = json_decode($raw['body'], true, flags: JSON_THROW_ON_ERROR);
        $html .= view($view, ['block' => $block, 'data' => $data, 'pointSections' => [],
            'practiceQuestions' => collect(), 'lessonLinks' => []])->render();
        $types[] = ['slot' => $slot, 'type' => $raw['type'], 'title' => $data['title'] ?? '', 'id' => $block->id];
        $tags[] = $blockTags->pluck('name')->all();
    }
    $file = $source['slug'].'-native.html';
    $handle = fopen($out.'/'.$file, 'x'); if (!$handle) { throw new RuntimeException('Exclusive reference HTML required.'); }
    fwrite($handle, $html); fclose($handle);
    $rows[] = ['slug' => $source['slug'], 'title' => $source['page']['title'], 'path' => '/theory/tenses/'.$source['slug'],
        'html' => $file, 'sha256' => hash('sha256', $html), 'native_types' => $types, 'tags' => $tags];
}
if ($queries !== 0 || config('database.connections.sqlite.database') !== ':memory:') { throw new RuntimeException('Reference isolation failed.'); }
$report = ['reference_commit' => $index['reference_commit'], 'purpose' => 'Old content is reference-only, not after teaching.',
    'database' => 'sqlite::memory:', 'database_queries' => $queries, 'working_database_touched' => false, 'rows' => $rows];
$handle = fopen($out.'/render.json', 'x'); if (!$handle) { throw new RuntimeException('Exclusive render evidence required.'); }
fwrite($handle, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); fclose($handle);
echo json_encode(['pass' => true, 'rows' => count($rows), 'database_queries' => $queries]).PHP_EOL;
