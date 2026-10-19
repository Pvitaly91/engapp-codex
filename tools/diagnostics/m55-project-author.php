<?php

/** Pure M55 author/native/metadata projection; unchanged M53 serializer procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M55', 8], ['m53', 'm55', 4],
    ['PassiveConstructions', 'PassiveReviewInfinitive', 4],
    ['passive-constructions', 'passive-review-infinitive', 2],
    ['Passive_Constructions', 'Passive_Review_Infinitive', 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        '7c9adc7e77bd37092cf7dcad7eb62516b260b6d76a8ea1609f2741c42a1a33be', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
    ["App\\Support\\TheoryPresentation::nativeView(\$block['type'])",
        "App\\Support\\TheoryPresentation::nativeView(App\\Support\\M55PassiveReviewInfinitivePackage::profile()['bindings'][\$i]['component_overrides'][\$n] ?? \$block['type'])", 1],
]);
