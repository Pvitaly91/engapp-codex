<?php

/** Explicit M57/full/before-registry read-only gate; no active draft source. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M57', 7], ['m52', 'm57', 5],
    ["['m57', 'registry']", "['m57', 'registry', 'registry-before']", 1],
    ['App\\Services\\M57LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m57-passive-basics.v1.json', 'm57-continuous-perfect-passives.v1.json', 1],
]);
