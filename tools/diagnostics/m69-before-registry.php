<?php

/** BEFORE-only dynamic registry capture; must precede M69 registration. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php', '9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69', [
    ['M52', 'M69', 7], ['m52', 'm69', 5],
    ["['m69', 'registry']", "['registry-before']", 1],
    ['App\\Services\\M69LocalTargetGuard::assertConfiguredDatabase', 'App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase', 1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if (($data['targets'] ?? null) < 127 || glob($root.'/database/content-patches/m69-*.json')) {
    throw new RuntimeException('M69 BEFORE registry must precede registration and preserve accepted owners.');
}
PHP, 1],
]);
