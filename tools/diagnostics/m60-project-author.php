<?php

/** Pure M60 author/native/metadata projection; unchanged M53 serializer procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M60', 8], ['m53', 'm60', 4],
    ['PassiveConstructions', 'ReportedSpeechBasics', 4],
    ['passive-constructions', 'reported-speech-basics', 2],
    ['Passive_Constructions', 'Reported_Speech_Basics', 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        '2cf4f9d9038a66546b90aee3d26e25a1a9b31aa5fd552ea870a70ef2d81f705f', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
]);
