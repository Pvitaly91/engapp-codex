<?php

/** M62 explicit proof prepare/cleanup; no route installer or SQL writer. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m52-runtime-proof.php', '207dfaed1d574215f80a728973b35eacb7e2c2fa37e97dc9a3ce40ffa391d220', [
    ['M52', 'M62', 31], ['m52', 'm62', 3],
]);
