<?php

/** Explicit M66/full-registry read-only audit; no draft source registration. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M66', 7], ['m52', 'm66', 5],
    ['App\\Services\\M66LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m66-passive-basics.v1.json', 'm66-verb-patterns-core.v3.json', 1],
    [".'-sentence-builders-v1.json'", ".'-sentence-builders-v2.json'", 1],
]);
