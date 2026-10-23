<?php

/** Fresh M82 pre-registration snapshot; existing read-only registry procedure only. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',[
    ['M52','M82',7],['m52','m82',5],
    ["['m82', 'registry']","['registry-before']",1],
    ['App\\Services\\M82LocalTargetGuard::assertConfiguredDatabase','App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase',1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if (($data['targets'] ?? null) !== 181 || glob($root.'/database/content-patches/m82-*.json')) {
    throw new RuntimeException('M82 fresh registry differs or the release was already registered.');
}
PHP,1],
]);
