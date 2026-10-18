<?php

/** Explicit M67/full-registry read-only audit; actual discovered scope, not a permanent target cap. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M67', 7], ['m52', 'm67', 5],
    ['App\\Services\\M67LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m67-passive-basics.v1.json', 'm67-verb-patterns-completion.v1.json', 1],
]);
