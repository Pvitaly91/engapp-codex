<?php

/** Explicit M60/full/prior-registry read-only audit; no draft source registration. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M60', 7], ['m52', 'm60', 5],
    ["['m60', 'registry']", "['m60', 'registry', 'registry-before']", 1],
    ['App\\Services\\M60LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m60-passive-basics.v1.json', 'm60-reported-speech-basics.v1.json', 1],
]);
