<?php

/**
 * Finite M79 profile of the immutable M52 proof lifecycle, as accepted in M76.
 * Preparation/cleanup never installs or removes a route. Main separately owns
 * the reviewed temporary GET-only loopback nonce route and exact restoration.
 * The six-owner M79LocalTargetGuard must be frozen, reviewed and mirrored first.
 * This file is preparation only: creating it does not execute the lifecycle.
 */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-runtime-proof.php', '207dfaed1d574215f80a728973b35eacb7e2c2fa37e97dc9a3ce40ffa391d220', [
    ['M52', 'M79', 31], ['m52', 'm79', 3],
]);
