<?php

/** Explicit M56/full/before-registry read-only gate; no active draft source. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M56', 7], ['m52', 'm56', 5],
    ["['m56', 'registry']", "['m56', 'registry', 'registry-before']", 1],
    ['App\\Services\\M56LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m56-passive-basics.v1.json', 'm56-gerund-simple-passives.v1.0.1.json', 1],
]);
