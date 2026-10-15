<?php

/** Explicit M54 scoped/full read-only registry audit receipt profile. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M54', 7], ['m52', 'm54', 5],
    ['m54-passive-basics.v1.json', 'm54-passive-patterns.v1.json', 1],
]);
