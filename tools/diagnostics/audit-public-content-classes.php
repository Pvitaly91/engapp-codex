<?php

// Read-only inventory; not a build dependency and never exports learning content.
if (PHP_SAPI !== 'cli') {
    exit(1);
}
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = \Illuminate\Support\Facades\DB::connection();
if ($db->getDriverName() !== 'mysql' || ! in_array($db->getConfig('host'), ['localhost', '127.0.0.1', '::1'], true)) {
    throw new RuntimeException('Only the configured local MySQL database may be inspected.');
}
$classes = [];
$inspect = function ($value) use (&$inspect, &$classes): void {
    if (is_array($value)) {
        foreach ($value as $part) $inspect($part);
    } elseif (is_string($value)) {
        preg_match_all('/\bclass\s*=\s*([\x22\x27])(.*?)\1/s', $value, $matches);
        foreach ($matches[2] as $attribute) {
            foreach (preg_split('/\s+/', trim($attribute)) as $class) {
                if ($class !== '' && strlen($class) < 180) $classes[$class] = true;
            }
        }
    }
};
$counts = [];
foreach (['text_blocks' => ['body'], 'pages' => ['text'], 'page_categories' => ['description']] as $table => $fields) {
    $columns = $db->getSchemaBuilder()->getColumnListing($table);
    $fields = array_values(array_intersect($fields, $columns));
    if (! $fields) continue;
    $counts[$table] = 0;
    foreach ($db->table($table)->select($fields)->cursor() as $row) {
        $counts[$table]++;
        foreach ($fields as $field) {
            $text = $row->$field;
            $inspect(json_decode($text ?? '', true) ?? $text);
        }
    }
}
$list = array_keys($classes);
sort($list);
$output = $app->storagePath('app/seo-m3-local/content-classes.json');
if (! is_dir(dirname($output))) mkdir(dirname($output), 0700, true);
file_put_contents($output, json_encode(['rows_read' => $counts, 'classes' => $list], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo json_encode(['rows_read' => $counts, 'unique_class_tokens' => count($list), 'output' => 'storage/app/seo-m3-local/content-classes.json']);
