<?php

/** Explicit M64/full/prior-registry read-only audit; no draft source registration. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M64', 7], ['m52', 'm64', 5],
    ["['m64', 'registry']", "['m64', 'registry', 'registry-before']", 1],
    ['App\\Services\\M64LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m64-passive-basics.v1.json', 'm64-reason-result-contrast.v1.json', 1],
]);
