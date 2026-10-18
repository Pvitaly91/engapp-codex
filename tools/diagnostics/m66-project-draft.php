<?php

/** Pure frozen M66 author/native projection; output remains private and never writes sources or SQL. */
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
    ['M53', 'M66', 8], ['m53', 'm66', 4],
    ['PassiveConstructions', 'VerbPatternsCore', 4],
    ['passive-constructions', 'verb-patterns-core', 2],
    ['Passive_Constructions', 'Verb_Patterns_Core', 1],
    ["require \$root.'/vendor/autoload.php';",
        "require \$root.'/vendor/autoload.php';\nrequire_once \$root.'/app/Support/M66VerbPatternsCorePackage.php';", 1],
    ['62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33',
        '517118d0fe7cf764229b5926e82fa7044b10a92535923a7301bc46602311d6a3', 1],
    ["\$state['category_ancestry'][0]['title']",
        "\$state['category_ancestry'][array_key_last(\$state['category_ancestry'])]['title']", 1],
    ["\$wt.'/docs/content/m66-verb-patterns-core.v1.0.0.json'",
        "\$wt.'/docs/content/m66-verb-patterns-core.v1.0.2.json'", 1],
    ["\$wt.'/docs/content/theory-sentence-builders/m66-verb-patterns-core.v1.json'",
        "\$wt.'/docs/content/theory-sentence-builders/m66-verb-patterns-core.v1.json'", 1],
    ["    \$metadata[] = ['identity' => \$owner, 'raw_page_title' => \$state['page']['title'],", $metadataHook, 1],
]);
