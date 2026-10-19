<?php

/** M65 explicit prepare/cleanup; never installs routes or writes SQL. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-runtime-proof.php', '207dfaed1d574215f80a728973b35eacb7e2c2fa37e97dc9a3ce40ffa391d220', [
    ['M52','M65',31], ['m52','m65',3],
]);
