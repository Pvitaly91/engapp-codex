<?php

/** Pure M57 author/native/metadata projection; unchanged M53 serializer procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M57', 8], ['m53', 'm57', 4],
    ['PassiveConstructions', 'ContinuousPerfectPassives', 4],
    ['passive-constructions', 'continuous-perfect-passives', 2],
    ['Passive_Constructions', 'Continuous_Perfect_Passives', 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        'dee8df7e2f51d2a9d7adb0c3858a99e93f4beb0abc961c6ff6741120e1fb0d11', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
]);
