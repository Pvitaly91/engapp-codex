<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$root = App\Models\PageCategory::where('slug', 'passive-voice')->first();
if (!$root) {
    echo "ROOT_NOT_FOUND\n";
    exit(0);
}

$printCategory = function ($category, $depth = 0) use (&$printCategory) {
    $indent = str_repeat('  ', $depth);
    echo $indent . "CATEGORY|{$category->slug}|{$category->title}|parent=" . ($category->parent?->slug ?? 'null') . "\n";
    foreach ($category->pages()->where('type', 'theory')->orderBy('title')->get() as $page) {
        echo $indent . "  PAGE|{$page->slug}|{$page->title}\n";
    }
    foreach ($category->children()->where('type', 'theory')->orderBy('title')->get() as $child) {
        $child->load('parent');
        $printCategory($child, $depth + 1);
    }
};

$root->load('parent');
$printCategory($root);
