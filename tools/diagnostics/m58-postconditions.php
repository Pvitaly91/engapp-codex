<?php

/** M58 exact content + finite insert profile; immutable M53 46-table reconstruction. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-postconditions.php', '8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d', [
    ['M53', 'M58', 22], ['m53', 'm58', 4],
    ['PassiveConstructions', 'PastFuturePassives', 7],
]);
