<?php

/** Pure M54 author/native/metadata projection using the reviewed M53 procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M54', 8], ['m53', 'm54', 4],
    ['PassiveConstructions', 'PassivePatterns', 4],
    ['passive-constructions', 'passive-patterns', 2],
    ['Passive_Constructions', 'Passive_Patterns', 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        'a0fc78b98a12e8af17c56d3f4fde8dccf95031d6de2a5a4c8081afeba15edb64', 1],
]);
