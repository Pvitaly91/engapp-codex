<?php

/** Pure M58 author/native/metadata projection; unchanged M53 serializer procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M58', 8], ['m53', 'm58', 4],
    ['PassiveConstructions', 'PastFuturePassives', 4],
    ['passive-constructions', 'past-future-passives', 2],
    ['Passive_Constructions', 'Past_Future_Passives', 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        '89dbf1213d3ebabcb1f4b30680c8600b58a4f7eb957038750addc5ed8cfe8ef0', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
]);
