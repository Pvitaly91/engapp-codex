<?php

/** M53 filesystem-only proof of already reviewed exact owned transfers. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-source-sync.php', 'b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273', [
    ['M52', 'M53', 22], ['m52', 'm53', 1],
    ['M53PassiveBasicsPackage', 'M53PassiveConstructionsPackage', 3],
]);
