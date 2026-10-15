<?php

/** M56 filesystem-only receipt; transfers remain separate reviewed exact hunks. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-source-sync.php', 'b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273', [
    ['M52', 'M56', 22], ['m52', 'm56', 1],
    ['M56PassiveBasicsPackage', 'M56GerundSimplePassivesPackage', 3],
]);
