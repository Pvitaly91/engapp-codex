<?php

/** M53 exact profile: prepare/cleanup only; never installs a route or writes SQL. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-runtime-proof.php', '207dfaed1d574215f80a728973b35eacb7e2c2fa37e97dc9a3ce40ffa391d220', [
    ['M52', 'M53', 31], ['m52', 'm53', 3],
]);
