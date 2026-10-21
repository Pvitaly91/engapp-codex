<?php

/** Filesystem-only exact mirror receipt, with no transfer, bootstrap, SQL or HTTP. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-source-sync.php','b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273',[
    ['M52PassiveBasicsPackage','M74OtherPronounsBasics',3],['M52','M74',19],['m52','m74',1],
]);
