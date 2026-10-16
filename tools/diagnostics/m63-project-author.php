<?php

/** Pure M63 author/native/metadata projection; unchanged M53 serializer procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M63', 8], ['m53', 'm63', 4],
    ['PassiveConstructions', 'RelativeCompletionLinking', 4],
    ['passive-constructions', 'relative-completion-linking', 2],
    ['Passive_Constructions', 'Relative_Completion_Linking', 1],
    ["require \$root.'/vendor/autoload.php';",
        "require \$root.'/vendor/autoload.php';\nrequire_once \$wt.'/app/Support/M63RelativeCompletionLinkingPackage.php';", 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        'daf7c1306cb8e292c5ccaa98aade4d611038509495871eb426a79be6fd67c585', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
]);
