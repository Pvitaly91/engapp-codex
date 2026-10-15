<?php

/** Pure M56 author/native/metadata projection; unchanged M53 serializer procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M56', 8], ['m53', 'm56', 4],
    ['PassiveConstructions', 'GerundSimplePassives', 4],
    ['passive-constructions', 'gerund-simple-passives', 2],
    ['Passive_Constructions', 'Gerund_Simple_Passives', 1],
    ['m56-gerund-simple-passives.v1.0.0.json', 'm56-gerund-simple-passives.v1.0.1.json', 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        '8198c1bcff87fbf270ab5d71a7d6c3343b728846ce9cc7be59e1ff44e5bee406', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
]);
