<?php

/** Fresh pre-registration M76 registry; read-only, no package apply. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M76', 7], ['m52', 'm76', 5],
    ["['m76', 'registry']", "['registry-before']", 1],
    ['App\\Services\\M76LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if (($data['targets'] ?? null) !== 148 || glob($root.'/database/content-patches/m76-*.json')) {
    throw new RuntimeException('M76 fresh BEFORE registry differs or M76 was already registered.');
}
PHP, 1],
]);
