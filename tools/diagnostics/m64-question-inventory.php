<?php

/** M64 finite three-bank profile of immutable M53 SELECT/inventory procedure. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-question-inventory.php', '1bc065454d558606c26a236828cd37916a1e33f0228d064caed00da069b0ae00', [
    ['M53', 'M64', 8],
    ['m53', 'm64', 1],
    ['ba489be880d9a7c2b3dbc1698e887a05eb166fbe', 'b5f521d9886a1a9b1c5298f84f581bf7b54e2713', 1],
    ['if ($source[\'scope\'][\'page_id\'] !== 95 || count($source[\'questions\']) !== 3
    || $source[\'seeder\'][\'class\'] !== \'Database\\\\Seeders\\\\V3\\\\Polyglot\\\\M64GetPassiveB1SupplementSeeder\') {
    throw new RuntimeException(\'M64 question inventory scope differs.\');
}
', '$questions = array_merge(...array_column($source[\'banks\'], \'questions\'));
if (count($source[\'banks\']) !== 3 || count($questions) !== 8
    || array_column($source[\'banks\'], \'bank_seeder\') !== App\\Support\\M64QuestionSupplement::BANKS) {
    throw new RuntimeException(\'M64 exact three-bank source differs.\');
}
', 1],
    ['\'source_draft_sha256\' => $source[\'source_draft_sha256\'], ', '', 1],
    ['foreach ($source[\'questions\'] as $question)', 'foreach ($questions as $question)', 1],
    ['$rows($db->table(\'categories\')->where(\'id\', 45))', '$rows($db->table(\'categories\')->whereIn(\'id\', array_unique(array_column($source[\'banks\'], \'category_id\')))->orderBy(\'id\'))', 1],
    ['$rows($db->table(\'sources\')->where(\'id\', 354))', '$rows($db->table(\'sources\')->whereIn(\'id\', array_unique(array_column($source[\'banks\'], \'source_id\')))->orderBy(\'id\'))', 1],
    ['function ($query) use ($source)', 'function ($query) use ($questions)', 1],
    ['$query->whereIn(\'uuid\', array_column($source[\'questions\'], \'uuid\'))->orWhere(\'seeder\', $source[\'seeder\'][\'class\']);', '$query->whereIn(\'uuid\', array_column($questions, \'uuid\'))->orWhereIn(\'seeder\', App\\Support\\M64QuestionSupplement::BANKS);', 1],
    ['count($out[\'taxonomy\'][\'categories\']) !== 1 || count($out[\'taxonomy\'][\'sources\']) !== 1', 'count($out[\'taxonomy\'][\'categories\']) !== count(array_unique(array_column($source[\'banks\'], \'category_id\')))
    || count($out[\'taxonomy\'][\'sources\']) !== count(array_unique(array_column($source[\'banks\'], \'source_id\')))', 1],
]);
