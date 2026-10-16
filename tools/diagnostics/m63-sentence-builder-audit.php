<?php

/** Explicit M63/full/prior-registry read-only audit; no draft source registration. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M63', 7], ['m52', 'm63', 5],
    ["['m63', 'registry']", "['m63', 'registry', 'registry-before']", 1],
    ['App\\Services\\M63LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m63-passive-basics.v1.json', 'm63-relative-completion-linking.v1.json', 1],
]);
