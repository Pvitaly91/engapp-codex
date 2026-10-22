<?php

/** Fresh pre-registration M77 registry; read-only, no package apply. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M77', 7], ['m52', 'm77', 5],
    ["['m77', 'registry']", "['registry-before']", 1],
    ['App\\Services\\M77LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if (($data['targets'] ?? null) !== 151 || glob($root.'/database/content-patches/m77-*.json')) {
    throw new RuntimeException('M77 fresh BEFORE registry differs or M77 was already registered.');
}
PHP, 1],
]);
