<?php

/** Explicit M62/full/prior-registry read-only audit; no draft source registration. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M62', 7], ['m52', 'm62', 5],
    ["['m62', 'registry']", "['m62', 'registry', 'registry-before']", 1],
    ['App\\Services\\M62LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m62-passive-basics.v1.json', 'm62-reported-review-relative-basics.v1.json', 1],
]);
