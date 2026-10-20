<?php

/** Fresh BEFORE-only registry; never a content writer. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52','M72',7], ['m52','m72',5],
    ["['m72', 'registry']", "['registry-before']",1],
    ['App\\Services\\M72LocalTargetGuard::assertConfiguredDatabase','App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase',1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if (($data['targets'] ?? null) !== 136 || glob($root.'/database/content-patches/m72-*.json')) {
    throw new RuntimeException('M72 fresh BEFORE registry differs or M72 was already registered.');
}
PHP,1],
]);
