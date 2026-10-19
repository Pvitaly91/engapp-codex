<?php

/** M66 BEFORE-only full registry audit; must run before any M66 active registration. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M66', 7], ['m52', 'm66', 5],
    ["['m66', 'registry']", "['registry-before']", 1],
    ['App\\Services\\M66LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if (($data['targets'] ?? null) !== 118 || glob($root.'/database/content-patches/m66-*.json')) {
    throw new RuntimeException('M66 BEFORE registry must be the exact pre-registration M65 stage.');
}
PHP, 1],
]);
