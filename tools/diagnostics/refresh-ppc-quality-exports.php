<?php

declare(strict_types=1);

use App\Models\Question;
use App\Services\PpcQualityLocalTargetGuard;
use App\Services\QuestionExportService;
use App\Support\LocalizedComposeText;
use App\Support\PpcOrderedTheoryLinks;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Support\Facades\DB;

/** Exactly 624 WT export files; no save/import/reseed or database mutation. */
function ppcQualityExportScope(array $inventory): array
{
    $records = $inventory['questions'] ?? [];
    if (count($records) !== 624 || ($inventory['counts']['bank_questions'] ?? null) !== 624) {
        throw new RuntimeException('Export inventory must contain exactly 624 questions.');
    }
    $scope = [];
    foreach ($records as $record) {
        $uuid = $record['persistent_uuid'] ?? '';
        if (! is_string($uuid) || ! preg_match('/^[a-zA-Z0-9_-]{1,191}$/D', $uuid) || isset($scope[$uuid])) {
            throw new RuntimeException('Unsafe, empty or duplicate export UUID.');
        }
        $scope[$uuid] = $record;
    }
    ksort($scope);
    return $scope;
}

function ppcQualityExportPayloadCheck(array $record, array $authored, array $payload, array $links, array $canonical): array
{
    $check = static function (mixed $expected, mixed $actual, string $field) use ($record): void {
        if ($expected !== $actual) {
            throw new RuntimeException('Export mismatch '.$record['persistent_uuid'].':'.$field);
        }
    };
    $check($record['persistent_uuid'], $payload['question']['uuid'] ?? null, 'uuid');
    $check($canonical['id'], $payload['question']['id'] ?? null, 'id');
    $check($record['question_template'], $payload['question']['question'] ?? null, 'question');
    $check((string) $record['type'], (string) ($payload['question']['type'] ?? ''), 'type');
    $check($record['seeder_class'], $payload['question']['seeder'] ?? null, 'seeder');
    $check($record['level'], $payload['question']['level'] ?? null, 'level');
    $check($canonical['options_by_marker'], $payload['question']['options_by_marker'] ?? null, 'options_by_marker');
    $check($record['primary_theory_text_block_uuid'], $payload['question']['theory_text_block_uuid'] ?? null, 'primary-theory-uuid');
    $check($links, $payload[PpcOrderedTheoryLinks::FIELD] ?? null, 'ordered-theory-links');
    $expectedLinks = array_map(static fn ($uuid, $position): array => ['text_block_uuid' => $uuid, 'position' => $position],
        $record['ordered_theory_text_block_uuids'], array_keys($record['ordered_theory_text_block_uuids']));
    $check($expectedLinks, $links, 'source-versus-persisted-links');
    $markers = 0;
    foreach (['uk', 'en', 'pl'] as $locale) {
        $prompts = array_values(array_filter($payload['hints'] ?? [], static fn ($hint): bool => $hint['provider'] === 'compose_prompt' && $hint['locale'] === $locale));
        $check(1, count($prompts), 'source-count-'.$locale);
        $check($record['source_condition'][$locale], $prompts[0]['hint'] ?? null, 'source-'.$locale);
        foreach ($authored['localizations'][$locale]['verb_hints'] ?? [] as $marker => $text) {
            $rows = array_values(array_filter($payload['verb_hints'] ?? [], static fn ($hint): bool =>
                strtolower($hint['verb_hint']['marker']) === strtolower($marker) && $hint['verb_hint']['locale'] === $locale));
            $check(1, count($rows), 'marker-hint-count-'.$locale.'-'.$marker);
            $check($text, $rows[0]['option']['option'] ?? null, 'marker-hint-'.$locale.'-'.$marker);
            $markers++;
        }
    }
    return ['compose_prompts' => 3, 'marker_hints' => $markers, 'theory_links' => count($links)];
}

if (defined('PPC_QUALITY_EXPORT_REFRESH_LIBRARY_ONLY')) {
    return;
}
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || ! in_array($argv[1] ?? '', ['--source-check', '--refresh'], true)) {
    throw new RuntimeException('Use Windows CLI --source-check or explicit --refresh.');
}
$mode = $argv[1];
$source = str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2)));
$root = 'D:/DEV/htdocs/gramlyze.loc';
if (strcasecmp($source, $root.'/storage/app/m30') !== 0 || is_link(dirname(__DIR__, 2))) {
    throw new RuntimeException('Export source must be the assigned m30 worktree, never ROOT.');
}
$inventoryBytes = file_get_contents($source.'/docs/reports/past-perfect-continuous-quality-inventory.json');
$inventory = json_decode($inventoryBytes, true, flags: JSON_THROW_ON_ERROR);
$scope = ppcQualityExportScope($inventory);
foreach ($inventory['source_sha256'] as $path => $sha) {
    if (is_link($source.'/'.$path) || hash('sha256', str_replace("\r\n", "\n", file_get_contents($source.'/'.$path))) !== $sha) {
        throw new RuntimeException('Stale or linked authored inventory source: '.$path);
    }
}
$authored = [];
foreach ($inventory['banks'] as $bank) {
    $definition = json_decode(file_get_contents($source.'/'.$bank['definition_path']), true, flags: JSON_THROW_ON_ERROR);
    foreach ($definition['questions'] as $question) {
        $authored[$bank['definition_path']][$question['uuid']] = $question;
    }
}
if ($mode === '--source-check') {
    echo json_encode(['status' => 'source-only-no-bootstrap-no-writes', 'questions' => count($scope), 'banks' => count($inventory['banks'])])."\n";
    exit(0);
}
require $root.'/vendor/autoload.php';
foreach (['app/Services/QuestionExportService.php', 'app/Support/LocalizedComposeText.php', 'app/Support/PpcOrderedTheoryLinks.php'] as $path) {
    if (! is_file($root.'/'.$path) || str_replace("\r\n", "\n", file_get_contents($root.'/'.$path)) !== str_replace("\r\n", "\n", file_get_contents($source.'/'.$path))) {
        throw new RuntimeException('Latest finite source sync required: '.$path);
    }
}
require_once $source.'/app/Services/M11LocalTargetGuard.php';
require_once $source.'/app/Services/PpcQualityLocalTargetGuard.php';
require_once $source.'/app/Support/LocalizedComposeText.php';
require_once $source.'/app/Support/PpcOrderedTheoryLinks.php';
require_once $source.'/app/Services/QuestionExportService.php';
$private = $root.'/storage/app/ppc-quality-local';
if (! is_dir($private) || is_link($private)) { throw new RuntimeException('Private local evidence directory is unsafe.'); }
$run = $private.'/export-refresh-'.bin2hex(random_bytes(8));
if (! mkdir($run, 0700)) { throw new RuntimeException('Exclusive export backup failed.'); }
foreach (['previous-exports', 'runtime/bootstrap', 'runtime/framework/views', 'runtime/logs'] as $part) {
    if (! mkdir($run.'/'.$part, 0700, true)) { throw new RuntimeException('Exclusive private runtime failed.'); }
}
foreach (['APP_CONFIG_CACHE' => 'config', 'APP_ROUTES_CACHE' => 'routes', 'APP_SERVICES_CACHE' => 'services',
    'APP_PACKAGES_CACHE' => 'packages', 'APP_EVENTS_CACHE' => 'events'] as $key => $name) {
    $value = substr($run, strlen($root) + 1).'/runtime/bootstrap/'.$name.'.php';
    putenv($key.'='.$value); $_ENV[$key] = $_SERVER[$key] = $value;
}
$app = require $root.'/bootstrap/app.php';
$app->useStoragePath($run.'/runtime');
$app->afterBootstrapping(LoadConfiguration::class, static function ($app) use ($run): void {
    $app['config']->set('cache.default', 'array');
    $app['config']->set('cache.driver', 'array');
    $app['config']->set('session.driver', 'array');
    $app['config']->set('view.compiled', $run.'/runtime/framework/views');
});
$queryGuard = static function (string $query): void {
    if (! preg_match('/^\s*(SELECT|SHOW)\b/i', $query) && trim(strtoupper($query)) !== 'SET SESSION TRANSACTION READ ONLY') {
        throw new RuntimeException('Read-only export rejected a non-SELECT/SHOW statement before execution.');
    }
};
$app->afterBootstrapping(RegisterProviders::class, static function ($app) use ($queryGuard): void {
    $app->make('db')->connection()->beforeExecuting($queryGuard);
});
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = DB::connection();
$physical = (array) $db->selectOne('SELECT DATABASE() db, @@hostname server, @@port port');
(new PpcQualityLocalTargetGuard)->verifyReadOnlyExport($db, 'gramlyze.loc', $private, $physical);
$exportDirectory = $source.'/database/seeders/questions';
if (! is_dir($exportDirectory) || is_link($exportDirectory) || realpath($exportDirectory) !== realpath($source).DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'seeders'.DIRECTORY_SEPARATOR.'questions') {
    throw new RuntimeException('Refusing non-WT export directory.');
}
config(['questions.export_path' => $exportDirectory]);
$write = static function (string $file, string $bytes): void {
    $handle = fopen($file, 'x');
    if (! $handle || fwrite($handle, $bytes) !== strlen($bytes) || ! fflush($handle)) { throw new RuntimeException('Exclusive evidence write failed.'); }
    fclose($handle);
};
$protected = static function () use ($db): array {
    $digests = [];
    foreach ($db->select('SELECT TABLE_NAME name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()') as $row) {
        $table = $row->name;
        if (! preg_match('/(progress|attempt|state|user|session)/i', $table) && $table !== 'saved_grammar_test_questions') { continue; }
        if (! preg_match('/^[a-zA-Z0-9_]+$/D', $table)) { throw new RuntimeException('Unsafe protected table name.'); }
        $columns = $db->getSchemaBuilder()->getColumnListing($table);
        $query = $db->table($table);
        foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
        $hash = hash_init('sha256'); $count = 0;
        foreach ($query->cursor() as $data) { hash_update($hash, json_encode($data, JSON_THROW_ON_ERROR)."\n"); $count++; }
        $digests[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
    }
    ksort($digests);
    return $digests;
};
$db->statement('SET SESSION TRANSACTION READ ONLY');
$db->beginTransaction();
$before = $protected();
$models = Question::query()->whereIn('uuid', array_keys($scope))->with(['hints', 'verbHints.option', 'answers.option', 'options'])->get()->keyBy('uuid');
if ($models->count() !== 624) { throw new RuntimeException('A finite UUID is missing or duplicated in gr2.'); }
$ids = []; $links = []; $manifest = [];
foreach ($scope as $uuid => $record) {
    $question = $models[$uuid];
    if (! is_string($question->uuid) || $question->uuid !== $uuid || $question->seeder !== $record['seeder_class'] || ! LocalizedComposeText::revisionEligible($question)) {
        throw new RuntimeException('Canonical finite source opt-in mismatch: '.$uuid);
    }
    $ids[$uuid] = $question->id;
    $links[$uuid] = PpcOrderedTheoryLinks::export($question);
    $wantedLinks = array_map(static fn ($block, $position): array => ['text_block_uuid' => $block, 'position' => $position],
        $record['ordered_theory_text_block_uuids'], array_keys($record['ordered_theory_text_block_uuids']));
    if ($links[$uuid] !== $wantedLinks || $question->question !== $record['question_template']
        || (string) $question->type !== (string) $record['type'] || $question->level !== $record['level']) {
        throw new RuntimeException('Source/database task mismatch before export: '.$uuid);
    }
    foreach (['uk', 'en', 'pl'] as $locale) {
        $prompt = $question->hints->filter(fn ($hint): bool => $hint->provider === 'compose_prompt' && $hint->locale === $locale);
        if ($prompt->count() !== 1 || $prompt->first()->hint !== $record['source_condition'][$locale]) {
            throw new RuntimeException('Source/database prompt mismatch before export: '.$uuid.':'.$locale);
        }
    }
    $path = $exportDirectory.'/'.$uuid.'.json';
    if (! is_file($path) || is_link($path)) { throw new RuntimeException('Existing canonical WT export required: '.$uuid); }
    $bytes = file_get_contents($path);
    if ((json_decode($bytes, true, flags: JSON_THROW_ON_ERROR)['question']['uuid'] ?? null) !== $uuid) {
        throw new RuntimeException('Old WT export has conflicting UUID: '.$uuid);
    }
    $write($run.'/previous-exports/'.$uuid.'.json', $bytes);
    $manifest[$uuid] = ['before_sha256' => hash('sha256', $bytes), 'id' => $question->id];
}
$write($run.'/backup-manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
$stats = ['questions' => 0, 'compose_prompts' => 0, 'marker_hints' => 0, 'theory_links' => 0];
try {
    foreach ($scope as $uuid => $record) {
        $question = $models[$uuid];
        $path = $exportDirectory.'/'.$uuid.'.json';
        if (hash_file('sha256', $path) !== $manifest[$uuid]['before_sha256']) { throw new RuntimeException('Concurrent WT export change: '.$uuid); }
        app(QuestionExportService::class)->export($question);
        $bytes = file_get_contents($path);
        $payload = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        $count = ppcQualityExportPayloadCheck($record, $authored[$record['definition_path']][$record['editorial_uuid']],
            $payload, $links[$uuid], ['id' => $question->id, 'options_by_marker' => $question->options_by_marker]);
        foreach ($count as $name => $value) { $stats[$name] += $value; }
        $stats['questions']++;
        $manifest[$uuid]['after_sha256'] = hash('sha256', $bytes);
        if ($stats['questions'] % 72 === 0) { echo 'Verified exports '.$stats['questions']."/624\n"; }
    }
    if ($stats['questions'] !== 624 || $stats['compose_prompts'] !== 1872 || $stats['marker_hints'] !== 885) {
        throw new RuntimeException('Finite exported localization counts changed.');
    }
} finally {
    $db->rollBack();
}
$after = $protected();
$idsAfter = $db->table('questions')->whereIn('uuid', array_keys($scope))->pluck('id', 'uuid')->all();
ksort($idsAfter);
if ($idsAfter !== $ids || $after !== $before) { throw new RuntimeException('Protected progress or canonical IDs changed during SELECT-only export.'); }
$write($run.'/result.json', json_encode(['at' => gmdate('c'), 'status' => 'verified-select-only-wt-exports',
    'target' => ['root' => $root, 'database' => $physical['db'], 'port' => (int) $physical['port']],
    'inventory_sha256' => hash('sha256', str_replace("\r\n", "\n", $inventoryBytes)),
    'stats' => $stats, 'protected_before' => $before, 'protected_after' => $after, 'ids_stable' => true,
    'exports' => $manifest], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
echo json_encode(['status' => 'verified-select-only-wt-exports', 'stats' => $stats, 'ids_stable' => true,
    'protected_tables' => count($after), 'exclusive_backup_and_evidence' => $run], JSON_UNESCAPED_SLASHES)."\n";
