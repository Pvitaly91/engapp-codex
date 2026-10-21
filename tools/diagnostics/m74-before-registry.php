<?php

/** Fresh pre-registration M74 registry; read-only, no package apply. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M74', 7], ['m52', 'm74', 5],
    ["['m74', 'registry']", "['registry-before']", 1],
    ['App\\Services\\M74LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if (($data['targets'] ?? null) !== 142 || glob($root.'/database/content-patches/m74-*.json')) {
    throw new RuntimeException('M74 fresh BEFORE registry differs or M74 was already registered.');
}
PHP, 1],
]);
