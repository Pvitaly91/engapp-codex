<?php

/** Explicit M59/full/before-registry read-only gate; no active draft source. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M59', 7], ['m52', 'm59', 5],
    ["['m59', 'registry']", "['m59', 'registry', 'registry-before']", 1],
    ['App\\Services\\M59LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m59-passive-basics.v1.json', 'm59-passive-future-agent-review.v1.json', 1],
]);
