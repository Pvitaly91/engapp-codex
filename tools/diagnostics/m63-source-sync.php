<?php

/** M63 filesystem-only receipt; transfers remain separate reviewed exact hunks. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-source-sync.php', 'b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273', [
    ['M52', 'M63', 22], ['m52', 'm63', 1],
    ['M63PassiveBasicsPackage', 'M63RelativeCompletionLinkingPackage', 3],
]);
