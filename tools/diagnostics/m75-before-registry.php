<?php

/** Fresh pre-registration M75 registry; read-only, no package apply. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M75', 7], ['m52', 'm75', 5],
    ["['m75', 'registry']", "['registry-before']", 1],
    ['App\\Services\\M75LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if (($data['targets'] ?? null) !== 145 || glob($root.'/database/content-patches/m75-*.json')) {
    throw new RuntimeException('M75 fresh BEFORE registry differs or M75 was already registered.');
}
PHP, 1],
]);
