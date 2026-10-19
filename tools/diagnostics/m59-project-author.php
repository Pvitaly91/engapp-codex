<?php

/** Pure M59 author/native/metadata projection; unchanged M53 serializer procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M59', 8], ['m53', 'm59', 4],
    ['PassiveConstructions', 'PassiveFutureAgentReview', 4],
    ['passive-constructions', 'passive-future-agent-review', 2],
    ['Passive_Constructions', 'Passive_Future_Agent_Review', 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        '27fbf37afc8efb9152b0f0a90d73dfab7b2114f72a15a9ceaf4c336b678d036d', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
]);
