<?php

/**
 * Filesystem-only M76 mirror receipt, following the pinned M52/M74 procedure.
 * Review/copy to served ROOT before explicit execution; no transfer, bootstrap,
 * SQL, HTTP, route edit or active-source mutation is performed by this profile.
 * The source worktree is the new finite D: M76 checkout, never the accepted M74
 * C: checkout. Main must finish freeze/mirror before this receipt can succeed.
 * Pending pins are owned by the release classes, not invented here:
 * - M76PronounRelations BEFORE/SOURCE/MASTER/CONTRACT/CHECKSUMS SHA constants;
 * - M76BuilderSelectionPresentation AUDIT_SHA and TARGETS_SHA;
 * - M76ContentPatch CONTROLLER_ROOT_AFTER_SHA / CONTROLLER_WT_AFTER_SHA;
 * The inherited load/validate and controller checks fail closed on pending pins.
 * Prepared only; do not execute until main has independently reviewed the file.
 */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-source-sync.php','b6d274a81c8fe06ab3a427035140efaffb92d9b28a873578715154eb3fa13273',[
    ['M52PassiveBasicsPackage','M76PronounRelations',3], ['M52','M76',19], ['m52','m76',1],
    ['C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc',
        'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree',1],
]);
