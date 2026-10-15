<?php

/**
 * Execute one explicitly reviewed, immutable read-only diagnostic with a finite
 * literal profile. No source rewrite, regex/code injection, temporary executable
 * or fallback is allowed. The caller owns every replacement and exact count.
 */
function runFrozenReadOnlyProfile(string $file, string $sha256, array $replacements, array $profileContext = []): void
{
    global $argv;
    if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows'
        || !in_array($file, ['m52-working-local.php', 'm52-old-consumers.php',
            'm52-runtime-proof.php', 'm52-source-sync.php', 'm52-sentence-builder-audit.php',
            'm53-project-author.php', 'm53-postconditions.php', 'm53-live-pages.php'], true)
        || preg_match('/^[a-f0-9]{64}$/D', $sha256) !== 1) {
        throw new RuntimeException('Read-only diagnostic profile is outside its finite allowlist.');
    }
    $path = __DIR__.'/'.$file;
    if (!is_file($path) || is_link($path)) {
        throw new RuntimeException('Immutable read-only diagnostic is unavailable or linked.');
    }
    $source = file_get_contents($path);
    if (!is_string($source) || !hash_equals($sha256, hash('sha256', $source)) || !str_starts_with($source, "<?php\n")) {
        throw new RuntimeException('Immutable read-only diagnostic bytes differ.');
    }
    foreach ($replacements as $replacement) {
        if (count($replacement) !== 3 || !is_string($replacement[0]) || $replacement[0] === ''
            || !is_string($replacement[1]) || !is_int($replacement[2]) || $replacement[2] < 1
            || substr_count($source, $replacement[0]) !== $replacement[2]) {
            throw new RuntimeException('Reviewed read-only literal profile count differs.');
        }
        $source = str_replace($replacement[0], $replacement[1], $source);
    }
    // Only hash-bound repository code and reviewed literal substitutions reach
    // this call. Runtime arguments are validated by the inherited CLI procedure.
    eval(substr($source, strlen("<?php\n")));
}
