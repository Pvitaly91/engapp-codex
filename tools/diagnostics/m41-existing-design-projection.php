<?php

// Pure, read-only source projection. It never bootstraps Laravel or writes files/DB/Git.
require_once dirname(__DIR__, 2).'/app/Support/M41AuthoredTenseComparisonsPackage.php';
require_once dirname(__DIR__, 2).'/app/Support/M41ExistingDesignPackage.php';

use App\Support\M41AuthoredTenseComparisonsPackage as Author;
use App\Support\M41ExistingDesignPackage as Design;

try {
    $root = dirname(__DIR__, 2);
    foreach ([Author::MASTER_PATH => Author::MASTER_SHA, Author::BEFORE => Author::BEFORE_SHA,
        Author::SOURCE => Author::SOURCE_SHA, Author::CORRECTION_PATH => Author::CORRECTION_SHA,
        Author::APPROVAL_PATH => Author::APPROVAL_SHA] as $path => $sha) {
        if (hash_file('sha256', $root.'/'.$path) !== $sha) { throw new RuntimeException('M41 frozen source bytes differ: '.$path); }
    }
    $master = json_decode(file_get_contents($root.'/'.Author::MASTER_PATH), true, flags: JSON_THROW_ON_ERROR);
    $mapping = Design::build($master); Design::validate($master, $mapping);
    $bytes = Design::bytes($mapping);
    if (in_array('--emit', $argv, true)) { echo $bytes; exit(0); }
    if (!in_array('--check', $argv, true) || count($argv) !== 2) {
        throw new RuntimeException('Use --check or --emit; no write mode is provided.');
    }
    if (file_get_contents($root.'/'.Design::SOURCE) !== $bytes || hash('sha256', $bytes) !== Design::SOURCE_SHA) {
        throw new RuntimeException('M41 finite native design mapping is not the exact generated source.');
    }
    echo json_encode(['status' => 'PASS', 'sections' => 18, 'points' => 35, 'explicit_correction_pairs' => 8,
        'forms_sections' => 3, 'form_cells' => 18, 'author_sha256' => Author::MASTER_SHA,
        'mapping_sha256' => Design::SOURCE_SHA, 'writes' => 0], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage()."\n"); exit(1);
}
