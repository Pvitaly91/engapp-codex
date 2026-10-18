<?php

/** M68 BEFORE-only registry: actual accepted dynamic owners, no M68 registration. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M68', 7], ['m52', 'm68', 5],
    ["['m68', 'registry']", "['registry-before']", 1],
    ['App\\Services\\M68LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if (($data['targets'] ?? null) < 124 || glob($root.'/database/content-patches/m68-*.json')) {
    throw new RuntimeException('M68 BEFORE registry must precede this new registration and retain all accepted owners.');
}
PHP, 1],
]);
