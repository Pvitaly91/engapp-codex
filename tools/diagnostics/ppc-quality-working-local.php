<?php

// Explicit finite local sync. No HTTP/startup hooks, migrations, full reseed or production target.
declare(strict_types=1);
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array($argv[1] ?? '', ['preview', 'apply', 'verify'], true)) { exit(1); }
$mode = $argv[1]; $root = 'D:/DEV/htdocs/gramlyze.loc'; $source = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
foreach (['app/Services/M11LocalTargetGuard.php', 'app/Services/PpcQualityLocalTargetGuard.php', 'app/Support/LocalizedComposeText.php',
    'app/Support/PastPerfectContinuousPracticeQuality.php', 'app/Support/Database/JsonTestLocalizationManager.php', 'app/Support/Database/JsonTestSeeder.php', 'app/Services/QuestionExportService.php'] as $path) { require_once $source.'/'.$path; }
$app = require $root.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app->useDatabasePath($source.'/database');
$db = Illuminate\Support\Facades\DB::connection(); $dir = $root.'/storage/app/ppc-quality-local';
$physical = (array) $db->selectOne('SELECT DATABASE() db, @@hostname server, @@port port');
(new App\Services\PpcQualityLocalTargetGuard)->verify($db, 'gramlyze.loc', $dir, $argv[2] ?? null, $physical);
$mutationTables = ['questions', 'question_answers', 'question_options', 'question_option_question', 'verb_hints', 'question_hints',
    'question_variants', 'question_tag', 'question_marker_tag', 'question_theory_text_blocks', 'text_blocks',
    'saved_grammar_tests', 'saved_grammar_test_questions', 'chatgpt_explanations', 'categories', 'sources', 'tags'];
$storage = $db->table('information_schema.TABLES')->whereRaw('TABLE_SCHEMA = DATABASE()')->whereIn('TABLE_NAME', $mutationTables)
    ->get(['TABLE_NAME', 'ENGINE', 'TABLE_COLLATION'])->keyBy('TABLE_NAME');
foreach ($mutationTables as $table) {
    if (!isset($storage[$table]) || strcasecmp((string) $storage[$table]->ENGINE, 'InnoDB') !== 0) {
        throw new RuntimeException('Transactional engine is unconfirmed for '.$table.'; refusing apply.');
    }
}
$package = App\Support\PastPerfectContinuousPracticeQuality::load($source);
$json = fn ($x) => json_encode($x, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
$write = static function ($file, $value) use ($json): void {
    $f = fopen($file, 'x'); if (!$f) { throw new RuntimeException('Exclusive evidence collision.'); }
    $bytes = $json($value); if (fwrite($f, $bytes) !== strlen($bytes) || !fflush($f)) { throw new RuntimeException('Incomplete evidence.'); } fclose($f);
};
$banks = [];
foreach (['Forms', 'Negatives', 'Questions', 'TimeExpressions'] as $suffix) {
    foreach (['V3/Tenses/PastPerfectContinuous/PastPerfectContinuous'.$suffix.'AllLevelsV3Seeder', 'V3/Polyglot/PolyglotPastPerfectContinuous'.$suffix.'AllLevelsLessonSeeder'] as $folder) {
        $banks[] = $folder;
    }
}
$banks[] = 'V3/Polyglot/PolyglotPastPerfectContinuousBasicsB2LessonSeeder';
$definitions = []; $instances = []; $expected = []; $expectedMarkerOptions = []; $expectedAnswers = []; $expectedOptions = [];
foreach ($banks as $folder) {
    $path = $source.'/database/seeders/'.$folder.'/definition.json';
    $class = 'Database\\Seeders\\'.str_replace('/', '\\', $folder);
    require_once dirname($path).'/'.basename($folder).'.php';
    $instances[$class] = app($class);
    $definition = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    $definition = app(App\Support\Database\JsonTestLocalizationManager::class)->mergeDefinitionLocalizations($definition, $path, $class);
    $definitions[$class] = $definition;
    foreach ($definition['questions'] as $question) {
        $uuid = app(App\Support\Database\QuestionUuidResolver::class)->toPersistent($question['uuid']);
        if (isset($expected[$uuid])) { throw new RuntimeException('Duplicate canonical UUID.'); }
        $expected[$uuid] = [$class, $question];
        $markers = (new ReflectionMethod($instances[$class], 'normalizeMarkers'))->invoke($instances[$class], $question, $definition['defaults']['default_locale'] ?? 'uk');
        $inferredOptions = array_map(fn ($marker) => $marker['options'], $markers);
        $expectedMarkerOptions[$uuid] = (new ReflectionMethod($instances[$class], 'resolveOptionsByMarker'))->invoke($instances[$class], $question, $inferredOptions);
        $expectedAnswers[$uuid] = array_map(fn ($marker) => $marker['answer'], $markers);
        $expectedOptions[$uuid] = (new ReflectionMethod($instances[$class], 'resolveQuestionOptions'))->invoke($instances[$class], $question, $inferredOptions, $expectedAnswers[$uuid]);
    }
}
if (count($expected) !== 624) { throw new RuntimeException('Finite 624-question bank scope changed.'); }
$idsBefore = $db->table('questions')->whereIn('uuid', array_keys($expected))->pluck('id', 'uuid')->all();
if (count($idsBefore) !== 624) { throw new RuntimeException('Missing existing editorial UUID; refuse insertion.'); }
$linkInstances = []; $linkManifests = []; $expectedLinks = [];
foreach (['Forms' => 'forms', 'Negatives' => 'negatives', 'Questions' => 'questions', 'TimeExpressions' => 'time-expressions'] as $suffix => $slug) {
    $class = 'Database\\Seeders\\V3\\TheoryLinks\\PastPerfectContinuous'.$suffix.'TheoryLinksSeeder';
    require_once $source.'/database/seeders/V3/TheoryLinks/PastPerfectContinuous'.$suffix.'TheoryLinksSeeder.php';
    $instance = $linkInstances[$class] = app($class);
    $path = $source.'/database/seeders/V3/TheoryLinks/data/past-perfect-continuous-'.$slug.'-theory-links.json';
    $manifest = $linkManifests[$class] = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    $aliases = (new ReflectionMethod($instance, 'resolveTheoryTextBlocks'))->invoke($instance, $manifest, $path);
    $bundles = (new ReflectionMethod($instance, 'normalizeBundles'))->invoke($instance, $manifest['bundles']);
    foreach ($manifest['tests_on_page'] as $test) {
        if ($test['strategy'] !== 'explicit_question_uuid_map') { throw new RuntimeException('Broad theory-link fallback forbidden.'); }
        foreach ($test['question_links'] as $editorialUuid => $bundleNames) {
            $uuid = app(App\Support\Database\QuestionUuidResolver::class)->toPersistent($editorialUuid);
            if (isset($expectedLinks[$uuid]) || !isset($expected[$uuid]) || $expected[$uuid][0] !== $test['seeder_class']) { throw new RuntimeException('Theory-link UUID/source conflict.'); }
            $expectedLinks[$uuid] = (new ReflectionMethod($instance, 'resolveBundlesToBlockUuids'))->invoke($instance, $bundleNames, $aliases, $bundles, $path);
            if (!$expectedLinks[$uuid]) { throw new RuntimeException('Empty scoped theory-link list.'); }
        }
    }
}
if (count($expectedLinks) !== 624) { throw new RuntimeException('All 624 questions require explicit theory links.'); }
$practiceRows = []; $pageIds = [];
foreach ($package['targets'] as $target) {
    $page = $db->table('pages')->where('seeder', $target['identity'])->sole(); $pageIds[] = $page->id;
    if ($page->slug !== $target['slug'] || $page->type !== 'theory') { throw new RuntimeException('Practice owner conflict.'); }
    foreach (['uk', 'en', 'pl'] as $locale) {
        $uuid = App\Support\TextBlock\TextBlockUuidGenerator::generateWithKey($target['identity'].'::'.$locale, $target['uuid_key']);
        $row = $db->table('text_blocks')->where('uuid', $uuid)->first();
        $owner = $locale === 'uk' ? $target['identity'] : 'Database\\Seeders\\Page_V3\\Localizations\\'.ucfirst($locale).'\\'
            .str_replace('TheorySeeder', 'TheoryLocalizationSeeder', basename(str_replace('\\', '/', $target['identity'])));
        if ($row && ($row->page_id !== $page->id || $row->type !== 'practice-set' || $row->locale !== $locale || $row->seeder !== $owner || (int) $row->sort_order !== 7)) { throw new RuntimeException('Practice identity conflict.'); }
        if ($row && $locale === 'uk' && !in_array(json_decode($row->body, true, flags: JSON_THROW_ON_ERROR), [$target['body_data']['uk'], App\Support\M26InteractivePractice::load($source)['targets'][array_search($target, $package['targets'], true)]['practice']['body_data']], true)) {
            throw new RuntimeException('Unknown existing practice content; exact diff required.');
        }
        if ($row && $locale !== 'uk' && json_decode($row->body, true, flags: JSON_THROW_ON_ERROR) !== $target['body_data'][$locale]) { throw new RuntimeException('Unknown localized practice content; refusing overwrite.'); }
        $practiceRows[$uuid] = ['row' => $row, 'attributes' => ['uuid' => $uuid, 'page_id' => $page->id, 'page_category_id' => null, 'type' => 'practice-set', 'locale' => $locale,
            'seeder' => $owner, 'sort_order' => 7, 'level' => $row->level ?? 'A2–B1', 'body' => App\Support\M26DetailPackage::json($target['body_data'][$locale])]];
    }
}
$protected = static function () use ($db, $idsBefore, $practiceRows): array {
    $out = [];
    foreach (['users', 'user_polyglot_answer_attempts', 'user_polyglot_lesson_progress', 'content_sync_states', 'pages', 'page_categories'] as $table) {
        $cols = $db->getSchemaBuilder()->getColumnListing($table); $query = $db->table($table);
        foreach (in_array('id', $cols, true) ? ['id'] : $cols as $col) { $query->orderBy($col); }
        $h = hash_init('sha256'); $count = 0; foreach ($query->cursor() as $row) { hash_update($h, json_encode($row)."\n"); $count++; }
        $out[$table] = ['count' => $count, 'sha256' => hash_final($h)];
    }
    foreach (['questions' => fn ($q) => $q->whereNotIn('id', array_values($idsBefore)), 'text_blocks' => fn ($q) => $q->whereNotIn('uuid', array_keys($practiceRows))] as $table => $scope) {
        $h = hash_init('sha256'); $count = 0; foreach ($scope($db->table($table))->orderBy('id')->cursor() as $row) { hash_update($h, json_encode($row)."\n"); $count++; }
        $out['non_target_'.$table] = ['count' => $count, 'sha256' => hash_final($h)];
    }
    $query = $db->table('question_theory_text_blocks')->whereNotIn('question_uuid', array_keys($idsBefore))->orderBy('id');
    $h = hash_init('sha256'); $count = 0; foreach ($query->cursor() as $row) { hash_update($h, json_encode($row)."\n"); $count++; }
    $out['non_target_theory_links'] = ['count' => $count, 'sha256' => hash_final($h)];
    $membership = $db->table('saved_grammar_test_questions')->orderBy('saved_grammar_test_id')->orderBy('position')->orderBy('question_uuid')->get(['saved_grammar_test_id', 'question_uuid', 'position']);
    $out['saved_test_membership'] = ['count' => $membership->count(), 'sha256' => hash('sha256', json_encode($membership))];
    return $out;
};
$before = $protected();
$targetDigest = static function () use ($db, $idsBefore, $practiceRows, $definitions, $linkManifests, $expectedLinks, $json): string {
    $data = ['definitions' => $definitions, 'link_manifests' => $linkManifests, 'desired_links' => $expectedLinks, 'desired_practice' => array_map(fn ($item) => $item['attributes'], $practiceRows), 'practice_package_sha256' => App\Support\PastPerfectContinuousPracticeQuality::SOURCE_SHA];
    foreach (['questions' => 'id', 'question_answers' => 'question_id', 'question_hints' => 'question_id', 'verb_hints' => 'question_id', 'question_option_question' => 'question_id'] as $table => $key) {
        $data[$table] = $db->table($table)->whereIn($key, array_values($idsBefore))->orderBy('id')->get();
    }
    $data['practice'] = $db->table('text_blocks')->whereIn('uuid', array_keys($practiceRows))->orderBy('id')->get();
    $data['theory_links'] = $db->table('question_theory_text_blocks')->whereIn('question_uuid', array_keys($idsBefore))->orderBy('id')->get();
    $optionIds = $db->table('question_option_question')->whereIn('question_id', array_values($idsBefore))->pluck('option_id');
    $data['option_values'] = $db->table('question_options')->whereIn('id', $optionIds)->orderBy('id')->get();
    return hash('sha256', $json($data));
};
$mismatches = static function () use ($expected, $expectedLinks, $expectedMarkerOptions, $expectedAnswers, $expectedOptions, $practiceRows, $definitions, $db): array {
    $bad = [];
    // Shared MySQL question_options are case-insensitively unique. Compare storage
    // exactly except for casing/edge whitespace; authored per-marker casing stays exact.
    $optionKey = fn ($value) => mb_strtolower(trim((string) $value), 'UTF-8');
    $rows = App\Models\Question::query()->whereIn('uuid', array_keys($expected))->with(['answers.option', 'options', 'hints', 'verbHints.option'])->get()->keyBy('uuid');
    foreach ($expected as $uuid => [$class, $q]) {
        $row = $rows[$uuid]; $answers = $row->answers->mapWithKeys(fn ($a) => [strtolower($a->marker) => $a->option->option])->all();
        $wanted = $expectedAnswers[$uuid]; ksort($wanted); ksort($answers);
        $type = (string) ($q['type'] ?? $definitions[$class]['defaults']['type'] ?? '0');
        if ($row->seeder !== $class || $row->question !== $q['question'] || $row->level !== $q['level'] || (string) $row->type !== $type || $row->options_by_marker !== $expectedMarkerOptions[$uuid]
            || $row->answers->count() !== count($wanted) || array_map($optionKey, $answers) !== array_map($optionKey, $wanted)) { $bad[] = $uuid.':question/answers'; continue; }
        foreach (['uk', 'en', 'pl'] as $locale) {
            $prompt = $locale === 'uk' ? ($q['source_text_uk'] ?? '') : ($q['localizations'][$locale]['source_text'] ?? '');
            if ($prompt !== '' && $row->hints->first(fn ($h) => $h->provider === App\Support\LocalizedComposeText::PROVIDER && $h->locale === $locale)?->hint !== $prompt) { $bad[] = $uuid.':source:'.$locale; }
            $hints = $q['localizations'][$locale]['verb_hints'] ?? [];
            foreach ($hints as $marker => $hint) {
                if ($row->verbHints->first(fn ($h) => strtolower($h->marker) === strtolower($marker) && $h->locale === $locale)?->option?->option !== $hint) { $bad[] = $uuid.':hint:'.$locale.':'.$marker; }
            }
        }
        $options = $row->options->pluck('option')->map($optionKey)->sort()->values()->all();
        $wantOptions = array_values(array_unique(array_map($optionKey, [...$expectedOptions[$uuid], ...array_values($wanted)]))); sort($wantOptions);
        if ($options !== $wantOptions) { $bad[] = $uuid.':options'; }
    }
    foreach ($expectedLinks as $uuid => $blockUuids) {
        $links = $db->table('question_theory_text_blocks')->where('question_uuid', $uuid)->orderBy('position')->get(['text_block_uuid', 'position']);
        $wanted = array_map(fn ($block, $index) => ['text_block_uuid' => $block, 'position' => $index], $blockUuids, array_keys($blockUuids));
        if ($links->map(fn ($link) => ['text_block_uuid' => $link->text_block_uuid, 'position' => (int) $link->position])->all() !== $wanted || $rows[$uuid]->theory_text_block_uuid !== $blockUuids[0]) { $bad[] = $uuid.':theory-links'; }
    }
    foreach ($practiceRows as $uuid => $item) { if ($db->table('text_blocks')->where('uuid', $uuid)->value('body') !== $item['attributes']['body']) { $bad[] = $uuid.':practice'; } }
    return $bad;
};
$diff = $mismatches(); $run = $mode.'-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4));
if ($mode === 'preview') {
    $review = [];
    foreach ($expected as $uuid => [$class, $q]) {
        $row = App\Models\Question::query()->where('uuid', $uuid)->with(['answers.option', 'options'])->sole();
        $review[] = ['uuid' => $uuid, 'id' => $row->id, 'seeder' => $class, 'level' => $q['level'], 'before' => $row->question, 'after' => $q['question'],
            'before_answers' => $row->answers->mapWithKeys(fn ($a) => [$a->marker => $a->option->option])->all(), 'answers' => $expectedAnswers[$uuid],
            'before_options_by_marker' => $row->options_by_marker, 'options_by_marker' => $expectedMarkerOptions[$uuid], 'localizations' => $q['localizations'] ?? []];
    }
    $write($dir.'/'.$run.'.json', ['at' => gmdate('c'), 'storage' => $storage, 'target_and_sources_sha256' => $targetDigest(), 'mismatches' => $diff, 'exact_question_diff' => $review, 'desired_theory_links' => $expectedLinks, 'practice' => $practiceRows, 'protected' => $before]);
    echo $json(['status' => 'preview-no-writes', 'questions' => 624, 'practice_rows' => 12, 'mismatches' => count($diff), 'review' => $dir.'/'.$run.'.json'])."\n"; exit(0);
}
if ($mode === 'verify') { echo $json(['status' => $diff ? 'mismatch' : 'verified', 'mismatches' => $diff])."\n"; exit($diff ? 2 : 0); }
if (!$diff) { echo $json(['status' => 'repeated-no-op', 'questions' => 624, 'practice_rows' => 12])."\n"; exit(0); }
// Require a fresh exact preview of this same finite source and original working state.
$preview = $argv[3] ?? ''; if (!preg_match('/^preview-[a-z0-9-]+\.json$/D', $preview)) { throw new RuntimeException('Apply requires exact preview basename.'); }
$review = json_decode(file_get_contents($dir.'/'.$preview), true, flags: JSON_THROW_ON_ERROR);
if ($review['protected'] !== $before || $review['mismatches'] !== $diff || $review['target_and_sources_sha256'] !== $targetDigest()) { throw new RuntimeException('Preview is stale.'); }
$backup = ['at' => gmdate('c'), 'protected' => $before, 'questions' => $db->table('questions')->whereIn('id', array_values($idsBefore))->get(), 'text_blocks' => $db->table('text_blocks')->whereIn('page_id', $pageIds)->get()];
foreach (['question_answers', 'question_option_question', 'verb_hints', 'question_hints', 'question_variants', 'question_tag', 'question_marker_tag'] as $table) { $backup[$table] = $db->table($table)->whereIn('question_id', array_values($idsBefore))->get(); }
$backup['question_options'] = $db->table('question_options')->get();
$backup['question_theory_text_blocks'] = $db->table('question_theory_text_blocks')->whereIn('question_uuid', array_keys($expected))->get();
$testIds = $db->table('saved_grammar_test_questions')->whereIn('question_uuid', array_keys($expected))->pluck('saved_grammar_test_id');
$backup['saved_grammar_tests'] = $db->table('saved_grammar_tests')->whereIn('id', $testIds)->get();
$backup['saved_grammar_test_questions'] = $db->table('saved_grammar_test_questions')->whereIn('saved_grammar_test_id', $testIds)->get();
$backup['chatgpt_explanations'] = $db->table('chatgpt_explanations')->whereIn('question', collect($backup['questions'])->pluck('question'))->get();
$write($dir.'/backup-'.$run.'.json', $backup);
$db->transaction(function () use ($instances, $linkInstances, $practiceRows, $db, $before, $protected, $idsBefore, $mismatches): void {
    App\Models\Question::withoutEvents(function () use ($instances, $linkInstances): void {
        foreach ($instances as $instance) { $instance->run(); }
        foreach ($linkInstances as $instance) { $instance->run(); }
    });
    foreach ($practiceRows as $uuid => $item) {
        $attrs = $item['attributes']; unset($attrs['uuid']);
        if ($item['row']) { $db->table('text_blocks')->where('uuid', $uuid)->update(['body' => $attrs['body']]); }
        else { $db->table('text_blocks')->insert($item['attributes']); }
    }
    if ($protected() !== $before || $db->table('questions')->whereIn('uuid', array_keys($idsBefore))->pluck('id', 'uuid')->all() !== $idsBefore) { throw new RuntimeException('Protected data or editorial identity changed; rollback.'); }
    if ($bad = $mismatches()) { throw new RuntimeException('Postconditions failed: '.implode(', ', array_slice($bad, 0, 20))); }
});
// Export only the scoped, fully localized questions after the transaction, never incomplete observer data.
config(['questions.export_path' => $source.'/database/seeders/questions']);
foreach (App\Models\Question::query()->whereIn('uuid', array_keys($expected))->cursor() as $q) { app(App\Services\QuestionExportService::class)->export($q); }
$write($dir.'/'.$run.'.json', ['at' => gmdate('c'), 'questions' => 624, 'practice_rows' => 12, 'protected' => $protected(), 'postconditions' => 'pass', 'backup' => 'backup-'.$run.'.json']);
echo $json(['status' => 'applied-transactionally', 'questions' => 624, 'practice_rows' => 12, 'evidence' => $dir.'/'.$run.'.json'])."\n";
