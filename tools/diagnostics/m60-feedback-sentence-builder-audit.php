<?php

/** Read-only corrected M60/full-registry audit with separate v2 receipts. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M60', 7], ['m52', 'm60', 5],
    ['App\\Services\\M60LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m60-passive-basics.v1.json', 'm60-reported-speech-basics.v1.0.1.json', 1],
    ['-sentence-builders-v1.json', '-sentence-builders-v2.json', 1],
]);
