<?php

/** M67 BEFORE-only registry audit; no M67 active registration may exist yet. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M67', 7], ['m52', 'm67', 5],
    ["['m67', 'registry']", "['registry-before']", 1],
    ['App\\Services\\M67LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if (($data['targets'] ?? null) !== 121 || glob($root.'/database/content-patches/m67-*.json')) {
    throw new RuntimeException('M67 BEFORE registry must be the exact pre-registration M66 stage.');
}
PHP, 1],
]);
