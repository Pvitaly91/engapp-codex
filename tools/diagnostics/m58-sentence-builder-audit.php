<?php

/** Explicit M58/full/before-registry read-only gate; no active draft source. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M58', 7], ['m52', 'm58', 5],
    ["['m58', 'registry']", "['m58', 'registry', 'registry-before']", 1],
    ['App\\Services\\M58LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m58-passive-basics.v1.json', 'm58-past-future-passives.v1.json', 1],
]);
