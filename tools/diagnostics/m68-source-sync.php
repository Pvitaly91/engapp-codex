<?php

/** M68 filesystem-only parity receipt. Transfer is a separate exact-hunk operation; no DB/HTTP. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-source-sync.php', 'b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273', [
    ['M52','M68',22], ['m52','m68',1],
    ['M68PassiveBasicsPackage','M68ComparisonBasics',3],
]);
