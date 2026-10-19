<?php

/** Pure M64 author/native/metadata projection; unchanged M53 serializer procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M64', 8], ['m53', 'm64', 4],
    ['PassiveConstructions', 'ReasonResultContrast', 4],
    ['passive-constructions', 'reason-result-contrast', 2],
    ['Passive_Constructions', 'Reason_Result_Contrast', 1],
    ["require \$root.'/vendor/autoload.php';",
        "require \$root.'/vendor/autoload.php';\nrequire_once \$wt.'/app/Support/M64ReasonResultContrastPackage.php';", 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        '46296d1b282d4f5c5c54b4eeaf1640680a173bb3e81482f5109ded710609f63d', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
]);
