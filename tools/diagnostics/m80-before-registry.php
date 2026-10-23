<?php

/** Fresh M80 pre-registration snapshot; existing read-only registry procedure only. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',[
    ['M52','M80',7],['m52','m80',5],
    ["['m80', 'registry']","['registry-before']",1],
    ['App\\Services\\M80LocalTargetGuard::assertConfiguredDatabase','App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase',1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if (($data['targets'] ?? null) !== 169 || glob($root.'/database/content-patches/m80-*.json')) {
    throw new RuntimeException('M80 pre-registration registry differs or was already registered.');
}
PHP,1],
]);
