<?php

/**
 * Filesystem-only M77 receipt from the immutable M52/M76 procedure. No transfer,
 * bootstrap, DB, HTTP, route edit or active-source mutation is performed here.
 * Main must freeze all six-owner artifacts, mirror only exact owned hunks and
 * pin both foreign-inclusive ROOT/WT controller profiles before execution.
 * Inherited load/validate checks reject pending artifact/controller hashes.
 * Prepared only; do not execute until the complete profile is reviewed.
 */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-source-sync.php', 'b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273', [
    ['M52PassiveBasicsPackage', 'M77OnePrepositionsPhrasal', 3], ['M52', 'M77', 19], ['m52', 'm77', 1],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc',
        'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree', 1],
]);
