<?php

/** Pure frozen M68 author/native projection; output remains private and never writes sources or SQL. */
require __DIR__.'/frozen-readonly-profile.php';

$metadataHook = <<<'PHP'
    $schemaPage = new \App\Models\Page;
    $schemaPage->setRawAttributes(['text' => $after['page']['subtitle_text'], 'title' => $state['page']['title']], true);
    $schemaPage->setRelation('textBlocks', new \Illuminate\Database\Eloquent\Collection(array_map(
        static fn ($config) => (new \App\Models\TextBlock)->setRawAttributes(['body' => $config['body']], true),
        $after['page']['blocks'])));
    $schemaDescription = (new ReflectionMethod(\App\Http\Middleware\AddStructuredData::class, 'pageDescription'))
        ->invoke(new \App\Http\Middleware\AddStructuredData, $schemaPage);
    $metadata[] = ['expected_learning_resource_description' => $schemaDescription,
        'identity' => $owner, 'raw_page_title' => $state['page']['title'],
PHP;

runFrozenReadOnlyProfile('m53-project-author.php', '28fe02902eeb1af70a8e43528996ecae07646762e3d497fc56c810b36f974360', [
    ['M53', 'M68', 8], ['m53', 'm68', 4],
    ['PassiveConstructions', 'ComparisonBasics', 4],
    ['M68ComparisonBasicsPackage', 'M68ComparisonBasics', 4],
    ['passive-constructions', 'comparison-basics', 2],
    ['Passive_Constructions', 'Comparison_Basics', 1],
    ["require \$root.'/vendor/autoload.php';",
        "require \$root.'/vendor/autoload.php';\nrequire_once \$root.'/app/Support/M68ComparisonBasics.php';", 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        'e23f859491c59bcb182dc22a73b69b2b8bad14fb85b565e3bd8fd6cbcc1dac46', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
    ["\$wt.'/docs/content/m68-comparison-basics.v1.0.0.json'",
        "\$root.'/storage/app/seo-m68-local/master-draft-v1.json'", 1],
    ["\$wt.'/docs/content/theory-sentence-builders/m68-comparison-basics.v1.json'",
        "\$root.'/storage/app/seo-m68-local/builder-contract-draft-v1.json'", 1],
    ["    \$metadata[] = ['identity' => \$owner, 'raw_page_title' => \$state['page']['title'],", $metadataHook, 1],
]);
