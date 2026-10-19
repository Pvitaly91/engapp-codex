<?php

/** Explicit M68/full-registry read-only audit; actual discovered scope, not a permanent target cap. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M68', 7], ['m52', 'm68', 5],
    ['App\\Services\\M68LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m68-passive-basics.v1.json', 'm68-comparison-basics.v1.json', 1],
]);
