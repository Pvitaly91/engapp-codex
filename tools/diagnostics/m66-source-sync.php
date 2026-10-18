<?php

/** M66 filesystem-only parity receipt. Transfer is a separate exact-hunk operation; no DB/HTTP. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-source-sync.php', 'b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273', [
    ['M52','M66',22], ['m52','m66',1],
    ['M66PassiveBasicsPackage','M66VerbPatternsCorePackage',3],
]);
