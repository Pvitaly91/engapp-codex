<?php

/** M55 filesystem-only receipt; transfers remain separate reviewed exact hunks. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-source-sync.php', 'b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273', [
    ['M52', 'M55', 22], ['m52', 'm55', 1],
    ['M55PassiveBasicsPackage', 'M55PassiveReviewInfinitivePackage', 3],
]);
