<?php

/** Explicit M55/full/before-registry read-only gate; no active draft source. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M55', 7], ['m52', 'm55', 5],
    ["['m55', 'registry']", "['m55', 'registry', 'registry-before']", 1],
    ['App\\Services\\M55LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m55-passive-basics.v1.json', 'm55-passive-review-infinitive.v1.json', 1],
]);
