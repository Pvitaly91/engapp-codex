<?php

/** M57 filesystem-only receipt; transfers remain separate reviewed exact hunks. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-source-sync.php', 'b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273', [
    ['M52', 'M57', 22], ['m52', 'm57', 1],
    ['M57PassiveBasicsPackage', 'M57ContinuousPerfectPassivesPackage', 3],
]);
