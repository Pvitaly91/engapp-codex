<?php

/** Pure M61 author/native/metadata projection; unchanged M53 serializer procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M61', 8], ['m53', 'm61', 4],
    ['PassiveConstructions', 'ReportedSpeechChanges', 4],
    ['passive-constructions', 'reported-speech-changes', 2],
    ['Passive_Constructions', 'Reported_Speech_Changes', 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        'ba414b3857d4a6d430f7037a62a9d50b8fac589be8bea30eaeeeed99df71059d', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
]);
