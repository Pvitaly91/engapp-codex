<?php

/** Pure M62 author/native/metadata projection; unchanged M53 serializer procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M62', 8], ['m53', 'm62', 4],
    ['PassiveConstructions', 'ReportedReviewRelativeBasics', 4],
    ['passive-constructions', 'reported-review-relative-basics', 2],
    ['Passive_Constructions', 'Reported_Review_Relative_Basics', 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        'c1d652a764102b5abc9b5dd8a8c9226cd79126302dce58a69e52aab7a9b3c55d', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
]);
