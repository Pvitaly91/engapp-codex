<?php

/** Explicit M65/full-registry read-only audit; no draft source registration. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M65', 7], ['m52', 'm65', 5],
    ['App\\Services\\M65LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ['m65-passive-basics.v1.json', 'm65-purpose-gerund-infinitive.v1.json', 1],
]);
