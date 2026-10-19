<?php

/** M53 profile of the existing read-only matcher/registry/HTTP acceptance gate. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M53', 7], ['m52', 'm53', 5],
    ['m53-passive-basics.v1.json', 'm53-passive-constructions.v1.json', 1],
]);
