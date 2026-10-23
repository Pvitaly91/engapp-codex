<?php

/** Explicit read-only proof lifecycle only; main separately owns nonce-route installation/removal. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-runtime-proof.php','207dfaed1d574215f80a728973b35eacb7e2c2fa37e97dc9a3ce40ffa391d220',[
    ['M52','M81',31],['m52','m81',3],
]);
