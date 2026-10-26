<?php

namespace App\Services;

use App\Support\M94RelativeClausesWhoseWhich as Package;
use App\Support\TheorySentenceBuilderAcceptance;
use RuntimeException;

/**
 * One-owner finite planner. The unchanged M26 writer owns locks, exact stale
 * preview equality, exclusive backup, transaction, postconditions and no-op.
 * Only box8741 can change heading; no shared planner feature is introduced.
 */
class M94ContentPatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    public const BEFORE_FILE = 'before-v1.json';
    public const BEFORE_SHA = 'efad9a30f6f24c139a57e09f39916bdb815266dc938927d176d53a6e5a3c4cfd';
    public const CONSUMERS_FILE = 'before-old-consumers-v1.json';
    public const CONSUMERS_SHA = '18250721e73eaba8a0bfc81559d13e6eeac00eeb490f3f419b5fcc691eae0186';
    public const SYNC_RECEIPT = 'source-sync-v2.json';
    public const SOURCE_WORKTREE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/m75-worktree';
    public const BANK_SOURCE_CAPTURE_SHA = '5fd7c5c02445d3cdf9488aefa7136c203b2c77b14767c5783484b01f42a0322c';
    public const BANK_SOURCE_PROFILES = [
        'database/seeders/V3/ClausesAndLinkingWords/RelativeClausesWithWhoseAndWhichAllLevelsV3Seeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\ClausesAndLinkingWords\\RelativeClausesWithWhoseAndWhichAllLevelsV3Seeder',
            'question_count' => 72,
            'root' => [
                'sha256' => '3d18adc463d99b762329c2aafac57b0ff50aae18b3a02e4fa4f3c0130057c25c',
                'bytes' => 73365,
                'crlf' => 2137,
                'lf' => 2137,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '3d18adc463d99b762329c2aafac57b0ff50aae18b3a02e4fa4f3c0130057c25c',
                'bytes' => 73365,
                'crlf' => 2137,
                'lf' => 2137,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/ClausesAndLinkingWords/RelativeClausesWithWhoseAndWhichAllLevelsV3Seeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\ClausesAndLinkingWords\\RelativeClausesWithWhoseAndWhichAllLevelsV3Seeder',
            'question_count' => null,
            'root' => [
                'sha256' => 'a50a217030e2091ad07449e24319c53d3258ea8d4fc12b9147bd456867eacc8e',
                'bytes' => 198,
                'crlf' => 5,
                'lf' => 5,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => 'a50a217030e2091ad07449e24319c53d3258ea8d4fc12b9147bd456867eacc8e',
                'bytes' => 198,
                'crlf' => 5,
                'lf' => 5,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotRelativeClausesWithWhoseAndWhichAllLevelsLessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotRelativeClausesWithWhoseAndWhichAllLevelsLessonSeeder',
            'question_count' => 72,
            'root' => [
                'sha256' => '12c78b904d78542f42bb173012989f8bd9ba2ec5752a4c64354ada407a13bc00',
                'bytes' => 155157,
                'crlf' => 4872,
                'lf' => 4872,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '12c78b904d78542f42bb173012989f8bd9ba2ec5752a4c64354ada407a13bc00',
                'bytes' => 155157,
                'crlf' => 4872,
                'lf' => 4872,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotRelativeClausesWithWhoseAndWhichAllLevelsLessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotRelativeClausesWithWhoseAndWhichAllLevelsLessonSeeder',
            'question_count' => null,
            'root' => [
                'sha256' => '3980c74056ec7d4b9bb00045236a5d34ac605a1ba116c8efe4250708619b52c7',
                'bytes' => 208,
                'crlf' => 5,
                'lf' => 5,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '3980c74056ec7d4b9bb00045236a5d34ac605a1ba116c8efe4250708619b52c7',
                'bytes' => 208,
                'crlf' => 5,
                'lf' => 5,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotRelativeClausesWithWhoseAndWhichB2LessonSeeder/definition.json' => [
            'kind' => 'definition',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotRelativeClausesWithWhoseAndWhichB2LessonSeeder',
            'question_count' => 48,
            'root' => [
                'sha256' => '688d9927deab437a25a046ce672e7b4a0b59aaaf8c045e511b4845594f08250f',
                'bytes' => 113120,
                'crlf' => 3408,
                'lf' => 3408,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '688d9927deab437a25a046ce672e7b4a0b59aaaf8c045e511b4845594f08250f',
                'bytes' => 113120,
                'crlf' => 3408,
                'lf' => 3408,
                'utf8_bom' => false,
            ],
        ],
        'database/seeders/V3/Polyglot/PolyglotRelativeClausesWithWhoseAndWhichB2LessonSeeder.php' => [
            'kind' => 'wrapper',
            'identity' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotRelativeClausesWithWhoseAndWhichB2LessonSeeder',
            'question_count' => null,
            'root' => [
                'sha256' => '09d232ca091f9ce4ed0a955aed86edd8be2ef1246f062dac5f6915a737e0591c',
                'bytes' => 151,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
            'worktree' => [
                'sha256' => '09d232ca091f9ce4ed0a955aed86edd8be2ef1246f062dac5f6915a737e0591c',
                'bytes' => 151,
                'crlf' => 3,
                'lf' => 3,
                'utf8_bom' => false,
            ],
        ],
    ];
    public const CONTROLLER_PATH = 'app/Http/Controllers/TestJsV2Controller.php';
    public const CONTROLLER_ROOT_AFTER_SHA = '78bf9ccfc383217848a35e3ea5d84d68bb0f7512264eb3745e0ef7af9762ef7f';
    public const CONTROLLER_WT_AFTER_SHA = '7658a70b1a23ba8f5be6846723f8ee91bb181d0a115fa44dfe8240f01a2ec440';
    public const SYNC_CODE_PATHS = [
        'app/Support/TheoryContentSource.php', 'app/Support/TheoryHtmlAdapter.php',
        'app/Support/TheoryLegacyAdapter.php', 'app/Support/TheoryContentRenderer.php',
        'app/Support/PracticeCompletionLinkedPractice.php', 'app/Services/Theory/TextBlockToQuestionsMatcherService.php',
        'app/Services/TheoryCourseManifestService.php', self::CONTROLLER_PATH,
        'app/Support/M94RelativeClausesWhoseWhich.php', 'app/Support/M94BuilderSelectionPresentation.php',
        'app/Services/M94ContentPatch.php', 'app/Services/M94LocalTargetGuard.php', 'app/Console/Commands/ContentPatchM94.php',
        Package::BINDINGS, Package::CHECKSUMS, Package::NAVIGATION,
        'tests/Fixtures/m94-owned-source-hunks.json',
        'docs/content/m94-sentence-builder-bank-audit.v1.json',
        'docs/content/m94-relative-clauses-whose-which.components.v1.json',
        'docs/content/m94-relative-clauses-whose-which.tasks.v1.json',
        'docs/content/m94-relative-clauses-whose-which.coverage.v1.json',
        'docs/content/m94-relative-clauses-whose-which.editorial.v1.json',
        'docs/content/m94-relative-clauses-whose-which.uk.md',
    ];
    protected const ID = 'm94-relative-clauses-whose-which-standard-view-v1';
    protected static function packageClass(): string { return Package::class; }
    protected function allowedUpdateFields(): array { return ['body', 'heading']; }
    protected function allowedPageUpdateFields(): array { return ['text']; }
    protected function expectedInsertCount(): int { return 6; }

    protected static function policy(): array
    {
        return ['guard' => M94LocalTargetGuard::class, 'served_root' => M94LocalTargetGuard::ROOT,
            'source_worktree' => self::SOURCE_WORKTREE, 'before_file' => self::BEFORE_FILE, 'before_sha' => self::BEFORE_SHA,
            'consumers_file' => self::CONSUMERS_FILE, 'consumers_sha' => self::CONSUMERS_SHA,
            'sync_receipt' => self::SYNC_RECEIPT, 'sync_code_paths' => self::SYNC_CODE_PATHS,
            'extra_source_paths' => [
                ...array_keys(self::BANK_SOURCE_PROFILES),
                'app/Support/TheoryNativeContentRelease.php', 'app/Support/TheoryBuilderSelectionPresentation.php',
                'app/Services/TheoryNativeContentPatch.php', 'app/Services/TheoryLocalReleaseGuard.php',
                'app/Support/M51QuestionSupplement.php', 'app/Support/M76PronounRelations.php',
                'app/Support/ComposeTokenCase.php', 'app/Support/AnswerOptionCase.php', 'app/Support/AcceptedAnswerVariants.php',
                'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
            ],
            'controller_path' => self::CONTROLLER_PATH, 'controller_root_after_sha' => self::CONTROLLER_ROOT_AFTER_SHA,
            'controller_worktree_after_sha' => self::CONTROLLER_WT_AFTER_SHA];
    }

    public static function assertDefinitionScope(array $before, array $after, array $target, int $i): void
    {
        $binding = Package::profile()['bindings'][0];
        if ($i !== 0 || ($target['identity'] ?? null) !== Package::OWNERS[0]
            || ($before['seeder']['class'] ?? null) !== Package::OWNERS[0]
            || ($before['type'] ?? null) !== 'theory' || ($before['page']['locale'] ?? null) !== 'uk'
            || ($before['slug'] ?? null) !== Package::SLUGS[0] || ($target['slug'] ?? null) !== Package::SLUGS[0]
            || ($target['ancestry'] ?? null) !== ['clauses-and-linking-words']
            || ($before['page']['category']['slug'] ?? null) !== 'clauses-and-linking-words'
            || ($target['path'] ?? null) !== TheorySentenceBuilderAcceptance::definitionPath(Package::OWNERS[0])
            || ($target['page_binding'] ?? null) !== ['id' => 284, 'page_category_id' => 34]
            || ($target['local_url'] ?? null) !== $binding['local_url']
            || count($before['page']['blocks'] ?? []) !== 2 || count($after['page']['blocks'] ?? []) !== 8
            || count($binding['appended_blocks'] ?? []) !== 6) { throw new RuntimeException('M94 exact one-owner source scope differs.'); }
        $protected = $after; $protected['page']['blocks'] = $before['page']['blocks'];
        foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $before['page'][$field]; }
        if ($protected !== $before) { throw new RuntimeException('M94 protected raw source metadata changed.'); }
        foreach ($before['page']['blocks'] as $slot => $old) {
            $new = $after['page']['blocks'][$slot];
            if (($old['type'] ?? null) !== Package::BLOCK_TYPES[$slot]
                || ($new['type'] ?? null) !== Package::BLOCK_TYPES[$slot]
                || ($old['column'] ?? null) !== Package::BLOCK_COLUMNS[$slot]
                || ($old['level'] ?? null) !== Package::CONTINUING_LEVELS[0][$slot]
                || \App\Support\M26DetailPackage::uuid(Package::OWNERS[0], $old, $slot + 1) !== Package::CONTINUING_UUIDS[$slot]
                || \App\Support\M26DetailPackage::uuid(Package::OWNERS[0], $new, $slot + 1) !== Package::CONTINUING_UUIDS[$slot]) {
                throw new RuntimeException('M94 continuing type/column/level/UUID differs.');
            }
            unset($old['body'], $new['body']);
            if ($slot === 1) {
                if (($old['heading'] ?? null) !== 'B2 focus' || ($new['heading'] ?? null) !== Package::BOX_HEADING) {
                    throw new RuntimeException('M94 only reviewed box heading transition is allowed.');
                }
                $new['heading'] = $old['heading'];
            }
            if ($old !== $new) { throw new RuntimeException('M94 continuing metadata or field absence changed.'); }
        }
        foreach ($binding['appended_blocks'] as $offset => $config) {
            $source = $after['page']['blocks'][$offset + 2];
            if (($source['type'] ?? null) !== $config['type'] || ($source['column'] ?? null) !== $config['column']
                || ($source['level'] ?? null) !== $config['level']
                || ($source['uuid_key'] ?? null) !== 'm94:'.Package::SLUGS[0].':'.$config['key']) {
                throw new RuntimeException('M94 native appended identity differs.');
            }
        }
    }

    public function plan(bool $lock = false): array
    {
        $root = dirname($this->databasePath); $package = static::packageClass(); $connection = $this->connection();
        [$before, $patch, $master, $contract] = $package::load($root); $package::validate($before, $patch, $master, $contract);
        $this->m94AssertCapacity($patch);
        $plan = ['patch' => static::ID, 'version' => 1, 'names' => static::NAMES, 'connection' => $connection,
            'sources' => [], 'pages' => [], 'updates' => [], 'inserts' => []];
        $baseline = $this->localTarget === null ? null : $this->m94AssertServedSources($root, $patch, $contract);
        if ($baseline !== null) {
            foreach ([static::policy()['before_file'], static::policy()['sync_receipt'], static::policy()['consumers_file']] as $file) {
                $plan['sources']['private:'.$file] = self::fileHash($this->privateDirectory.'/'.$file);
            }
        }
        foreach (static::sourcePaths($patch, $contract, $root) as $path) { $plan['sources'][$path] = self::fileHash($root.'/'.$path); }
        $states = []; $targetIds = [];
        foreach ($patch['targets'] as $i => $target) {
            $name = $target['identity']; $old = $before['targets'][$i]['before']; $new = $target['after'];
            static::assertDefinitionScope($old, $new, $target, $i);
            if ($baseline !== null && ($baseline['definitions'][$name]['definition'] ?? null) !== $old) {
                throw new RuntimeException('Native public source BEFORE differs from independent private capture.');
            }
            if (json_decode(file_get_contents($root.'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR) !== $new) {
                throw new RuntimeException('Native active source differs from exact AFTER.');
            }
            $owners = $this->rows('pages', static fn ($q) => $q->where('seeder', $name), $lock);
            if (count($owners) !== 1) { throw new RuntimeException('Native Page owner is missing or ambiguous.'); }
            $page = $owners[0]; $chain = $this->categoryChain($page['page_category_id'], $lock);
            if ($page['id'] !== $target['page_binding']['id'] || $page['page_category_id'] !== $target['page_binding']['page_category_id']
                || $page['slug'] !== $old['slug'] || $page['title'] !== $old['page']['title'] || $page['type'] !== 'theory'
                || array_column($chain, 'slug') !== $target['ancestry']) { throw new RuntimeException('Native Page/category identity differs.'); }
            $rows = $this->rows('text_blocks', static fn ($q) => $q->where('page_id', $page['id']), $lock);
            if ($baseline !== null) { $this->m94AssertMetadataBaseline($baseline['pages'][$name], $page, $rows, $old, $new, $i); }
            // This finite package independently captured its existing subtitle.
            $subtitleAbsent = false;
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'heading' => null,
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle',
                'body' => $subtitleAbsent ? null : $old['page']['subtitle_html']];
            $afterSubtitle = $subtitle; $afterSubtitle['body'] = $new['page']['subtitle_html'];
            $oldConfigs = $subtitleAbsent ? [] : [$subtitle];
            foreach ($old['page']['blocks'] as $slot => $config) {
                if ($subtitleAbsent) { $config['uuid'] = $package::profile()['bindings'][$i]['continuing_uuids'][$slot]; }
                $oldConfigs[$slot + 1] = $config;
            }
            $newConfigs = [$afterSubtitle, ...$new['page']['blocks']]; $rowStates = [];
            $state = $this->candidate($plan['updates'], 'pages', $page, $name,
                ['text' => $subtitleAbsent ? null : $old['page']['subtitle_text']], ['text' => $new['page']['subtitle_text']]);
            if ($state !== null) { $rowStates[] = $state; }
            foreach ($oldConfigs as $position => $config) {
                $uuid = $this->resolveUuid($name, $config, $position);
                if ($uuid !== $this->resolveUuid($name, $newConfigs[$position], $position)) { throw new RuntimeException('Native continuing UUID differs.'); }
                $matches = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), $lock);
                if (count($matches) !== 1) { throw new RuntimeException('Native continuing row missing or ambiguous.'); }
                $row = $matches[0]; $this->m94AssertOwner($row, $page, $name, $position);
                $fields = $this->allowedUpdateFields(); $oldValues = []; $newValues = [];
                $afterConfig = $newConfigs[$position];
                if ($position === 2) {
                    if ($row['id'] !== 8741 || $uuid !== Package::CONTINUING_UUIDS[1]
                        || ($config['heading'] ?? null) !== 'B2 focus'
                        || ($afterConfig['heading'] ?? null) !== Package::BOX_HEADING) {
                        throw new RuntimeException('M94 heading update is not the exact reviewed box row.');
                    }
                } elseif (($config['heading'] ?? null) !== ($afterConfig['heading'] ?? null)) {
                    throw new RuntimeException('M94 heading changes outside box8741 are forbidden.');
                }
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                    if (!in_array($field, $fields, true) && $row[$field] !== ($config[$field] ?? null)) {
                        throw new RuntimeException('Native continuing metadata differs: '.$field);
                    }
                }
                foreach ($fields as $field) { $oldValues[$field] = $config[$field] ?? null; $newValues[$field] = $newConfigs[$position][$field] ?? null; }
                $targetIds[] = $row['id'];
                $state = $this->candidate($plan['updates'], 'text_blocks', $row, $name, $oldValues, $newValues);
                if ($state !== null) { $rowStates[] = $state; }
            }
            $rowStates = array_values(array_unique($rowStates));
            if (count($rowStates) !== 1) { throw new RuntimeException('Native partial/manual owner state; no writes.'); }
            $state = $rowStates[0]; $addedCount = count($newConfigs) - count($oldConfigs);
            foreach (array_diff_key($newConfigs, $oldConfigs) as $position => $source) {
                $uuid = $this->resolveUuid($name, $source, $position);
                $fields = ['uuid' => $uuid, 'page_id' => $page['id'], 'page_category_id' => $page['page_category_id'], 'locale' => 'uk',
                    'type' => $source['type'], 'column' => $source['column'], 'heading' => $source['heading'] ?? null,
                    'css_class' => $source['css_class'] ?? null, 'sort_order' => $position,
                    'body' => $source['body'], 'level' => $source['level'] ?? null, 'seeder' => $name];
                $matches = $this->rows('text_blocks', static fn ($q) => $q->where('uuid', $uuid), $lock);
                if ($state === 'before') {
                    if ($matches !== []) { throw new RuntimeException('Native new block UUID collides or is partially present.'); }
                    $plan['inserts'][] = ['table' => 'text_blocks', 'seeder' => $name, 'fields' => $fields, 'sha256' => self::digest($fields)];
                } else {
                    if (count($matches) !== 1) { throw new RuntimeException('Native applied block missing or duplicated.'); }
                    foreach ($fields as $field => $value) {
                        if ($matches[0][$field] !== $value) { throw new RuntimeException('Native appended block edited: '.$field); }
                    }
                    if (empty($matches[0]['created_at']) || $matches[0]['created_at'] !== $matches[0]['updated_at']) {
                        throw new RuntimeException('Native block creation timestamps differ.');
                    }
                    $targetIds[] = $matches[0]['id'];
                }
            }
            if (count(array_filter($rows, static fn ($r) => $r['locale'] === 'uk')) !== count($oldConfigs) + ($state === 'after' ? $addedCount : 0)) {
                throw new RuntimeException('Native unexpected UK row count.');
            }
            $relations = $this->relations($page, $rows, $lock);
            $this->assertExistingPools($relations['questions'], $contract['targets'][$i], $baseline['pages'][$name] ?? null);
            $plan['pages'][$name] = ['page' => $page, 'category_ancestry' => $chain, 'blocks' => $rows, 'relations' => $relations]; $states[] = $state;
        }
        if (count(array_unique($states)) !== 1) { throw new RuntimeException('Native partial release; no writes.'); }
        $plan['state'] = $states[0];
        if (($plan['state'] === 'before' && count($plan['inserts']) !== $this->expectedInsertCount())
            || ($plan['state'] === 'after' && ($plan['updates'] !== [] || $plan['inserts'] !== []))) {
            throw new RuntimeException('Native content insert/no-op allowlist differs.');
        }
        $this->extendPlan($plan, $patch, $baseline, $lock);
        $plan['protected'] = $this->protectedFingerprints($targetIds); ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }

    /** Fresh baseline comparison; only a fully proven box AFTER heading is projected on a copy. */
    private function m94AssertMetadataBaseline(array $captured, array $page, array $rows, array $old, array $new, int $index): void
    {
        if ($index !== 0 || $captured['page']['id'] !== 284 || $captured['page']['page_category_id'] !== 34) {
            throw new RuntimeException('M94 captured metadata owner differs.');
        }
        $withoutAppends = [];
        foreach ($rows as $row) {
            $isAppend = false;
            foreach (array_slice($new['page']['blocks'], 2, null, true) as $slot => $source) {
                $expected = ['uuid' => $this->resolveUuid(Package::OWNERS[0], $source, $slot + 1),
                    'seeder' => Package::OWNERS[0], 'page_id' => 284, 'page_category_id' => 34, 'locale' => 'uk',
                    'sort_order' => $slot + 1, 'type' => $source['type'], 'column' => $source['column'],
                    'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null,
                    'level' => $source['level'] ?? null, 'body' => $source['body']];
                if ($row['uuid'] !== $expected['uuid']) { continue; }
                foreach ($expected as $field => $value) {
                    if (($row[$field] ?? null) !== $value) { throw new RuntimeException('M94 appended baseline row is not exact.'); }
                }
                if (empty($row['created_at']) || $row['created_at'] !== $row['updated_at']) {
                    throw new RuntimeException('M94 appended baseline timestamp differs.');
                }
                $isAppend = true; break;
            }
            if (!$isAppend) { $withoutAppends[] = $row; }
        }
        $capturedIds = array_column($captured['blocks'], 'id'); $actualIds = array_column($withoutAppends, 'id');
        sort($capturedIds); sort($actualIds);
        if ($capturedIds !== $actualIds) { throw new RuntimeException('M94 continuing/non-UK row inventory differs from fresh capture.'); }
        foreach ($withoutAppends as &$row) {
            if ($row['id'] !== 8741 || $row['uuid'] !== Package::CONTINUING_UUIDS[1]) { continue; }
            $source = $new['page']['blocks'][1]; $matches = true;
            foreach (['seeder' => Package::OWNERS[0], 'page_id' => 284, 'page_category_id' => 34,
                'locale' => 'uk', 'sort_order' => 2, 'type' => 'box', 'column' => 'left',
                'level' => null, 'css_class' => null, 'heading' => Package::BOX_HEADING,
                'body' => $source['body']] as $field => $value) {
                if (($row[$field] ?? null) !== $value) { $matches = false; }
            }
            if ($matches) { $row['heading'] = $old['page']['blocks'][1]['heading']; }
        }
        unset($row);
        M49ContentPatch::assertMetadataBaseline($captured, $page, $withoutAppends);
    }

    /** Complete three-bank source guard, including both contexts and all EOL bytes. */
    public static function assertOriginalBankSourceBytes(array $sources): void
    {
        if (array_keys($sources) !== array_keys(self::BANK_SOURCE_PROFILES)) {
            throw new RuntimeException('M94 exact three-bank/six-path source inventory differs.');
        }
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            if (array_keys($sources[$path] ?? []) !== ['root', 'worktree']) { throw new RuntimeException('M94 bank source contexts differ.'); }
            foreach (['root', 'worktree'] as $context) {
                $bytes = $sources[$path][$context]; $expected = $profile[$context];
                if (!is_string($bytes) || hash('sha256', $bytes) !== $expected['sha256'] || strlen($bytes) !== $expected['bytes']
                    || substr_count($bytes, "\r\n") !== $expected['crlf'] || substr_count($bytes, "\n") !== $expected['lf']
                    || str_starts_with($bytes, "\xEF\xBB\xBF") !== $expected['utf8_bom']) {
                    throw new RuntimeException('M94 captured original bank source raw/EOL bytes differ.');
                }
            }
            if ($profile['kind'] === 'definition') {
                $root = json_decode($sources[$path]['root'], true, flags: JSON_THROW_ON_ERROR);
                $worktree = json_decode($sources[$path]['worktree'], true, flags: JSON_THROW_ON_ERROR);
                if ($root !== $worktree || ($root['seeder']['class'] ?? null) !== $profile['identity']
                    || count($root['questions'] ?? []) !== $profile['question_count']) {
                    throw new RuntimeException('M94 original complete bank identity/content differs.');
                }
            }
        }
    }

    protected static function originalBankSourceBytes(): array
    {
        $out = [];
        foreach (self::BANK_SOURCE_PROFILES as $path => $profile) {
            foreach (['root' => M94LocalTargetGuard::ROOT, 'worktree' => self::SOURCE_WORKTREE] as $context => $root) {
                $file = $root.'/'.$path;
                if (!is_file($file) || is_link($file)) { throw new RuntimeException('M94 captured original bank source missing or linked.'); }
                $bytes = file_get_contents($file);
                if (!is_string($bytes)) { throw new RuntimeException('M94 original bank source unreadable.'); }
                $out[$path][$context] = $bytes;
            }
        }
        return $out;
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        self::assertOriginalBankSourceBytes(static::originalBankSourceBytes());
        return parent::connection($expected, $writing);
    }

    protected function assertExistingPools(array $questions, array $contract, ?array $baseline): void
    {
        if (($contract['identity'] ?? null) !== Package::OWNERS[0] || ($contract['level'] ?? null) !== 'B2'
            || ($contract['bank_seeders'] ?? null) !== Package::BANKS) { throw new RuntimeException('M94 exact B2 thematic contract differs.'); }
        $filter = static fn ($q) => $q['seeder'] === Package::BANKS[0] && (string) $q['type'] === '4' && $q['level'] === 'B2';
        $eligible = array_values(array_filter($questions, $filter)); $sort = static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']);
        usort($eligible, $sort);
        if (count($eligible) !== 48 || count(array_unique(array_column($eligible, 'id'))) !== 48) {
            throw new RuntimeException('M94 original B2 type4 pool must contain the captured48 unique questions.');
        }
        if ($baseline !== null) {
            $old = array_values(array_filter($baseline['relations']['questions'], $filter)); usort($old, $sort);
            if ($eligible !== $old || array_column($eligible, 'id') !== ($baseline['bank_inventory'][Package::BANKS[0]]['eligible_ids_uuid_order'] ?? null)) {
                throw new RuntimeException('M94 complete captured B2 question rows or UUID order changed.');
            }
        }
    }

    private function m94AssertOwner(array $row, array $page, string $owner, int $position): void
    {
        if ($row['seeder'] !== $owner || $row['locale'] !== 'uk' || $row['page_id'] !== $page['id']
            || $row['page_category_id'] !== $page['page_category_id'] || (int) $row['sort_order'] !== $position) {
            throw new RuntimeException('Native continuing owner/locale/order differs.');
        }
    }

    private function m94AssertCapacity(array $patch): void
    {
        if ($this->db->getDriverName() !== 'mysql') { return; }
        $column = (array) $this->db->selectOne('SELECT DATA_TYPE AS kind, CHARACTER_OCTET_LENGTH AS max_bytes FROM information_schema.COLUMNS '
            .'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?', [$this->db->getDatabaseName(), 'text_blocks', 'body']);
        if (!in_array($column['kind'] ?? null, ['tinytext', 'text', 'mediumtext', 'longtext'], true) || !is_numeric($column['max_bytes'] ?? null)) {
            throw new RuntimeException('Native actual body SQL capacity is unknown.');
        }
        foreach ($patch['targets'] as $target) {
            PracticeCompletionPatch::assertBodyCapacity($target['after']['page']['subtitle_html'], (int) $column['max_bytes']);
            foreach ($target['after']['page']['blocks'] as $block) { PracticeCompletionPatch::assertBodyCapacity($block['body'], (int) $column['max_bytes']); }
        }
    }

    private function m94AssertServedSources(string $root, array $patch, array $contract): array
    {
        $policy = static::policy(); $package = static::packageClass();
        $normal = static fn ($path) => strtolower(str_replace('\\', '/', (string) realpath($path)));
        if (!in_array($normal($root), [$normal($policy['served_root']), $normal($policy['source_worktree'])], true)) {
            throw new RuntimeException('Native application/source ROOT differs from finite allowlist.');
        }
        foreach ([$policy['before_file'] => $policy['before_sha'], $policy['consumers_file'] => $policy['consumers_sha']] as $file => $sha) {
            if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !hash_equals($sha, self::fileHash($this->privateDirectory.'/'.$file))) {
                throw new RuntimeException('Native independent BEFORE/consumer evidence is not reviewed or changed.');
            }
        }
        $before = json_decode(file_get_contents($this->privateDirectory.'/'.$policy['before_file']), true, flags: JSON_THROW_ON_ERROR);
        $consumers = json_decode(file_get_contents($this->privateDirectory.'/'.$policy['consumers_file']), true, flags: JSON_THROW_ON_ERROR);
        M51ContentPatch::assertConsumerCapture($before, $consumers);
        if (($before['base_sha'] ?? null) !== $package::BASE_SHA || ($before['served_root'] ?? null) !== $policy['served_root']
            || ($before['source_worktree'] ?? null) !== $policy['source_worktree']
            || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null)
            || array_keys($before['pages'] ?? []) !== static::NAMES || empty($consumers['route_declarations']) || empty($consumers['http'])) {
            throw new RuntimeException('Native independent source/DB/HTTP BEFORE scope differs.');
        }
        foreach ($before['http'] as $record) {
            $allowedGate = $record['kind'] === 'course' && in_array($record['status'], [301, 302, 303, 307, 308, 401, 403, 404], true);
            if (empty($record['sha256']) || ($record['status'] !== 200 && !$allowedGate)) {
                throw new RuntimeException('Native required theory BEFORE unavailable or unrecognised course gate.');
            }
            if ($record['kind'] === 'course' && $record['status'] === 200 && empty($record['course_theory_html_sha256'])) {
                throw new RuntimeException('Native accessible BEFORE course learning DOM is missing.');
            }
        }
        $kinds = array_count_values(array_column($consumers['http'], 'kind'));
        if (($kinds['course'] ?? 0) < count(static::NAMES) || ($kinds['ssr'] ?? 0) < count(static::NAMES) || ($kinds['json'] ?? 0) < count(static::NAMES)) {
            throw new RuntimeException('Native independent consumer capture is incomplete.');
        }
        $sync = json_decode(file_get_contents($this->privateDirectory.'/'.$policy['sync_receipt']), true, flags: JSON_THROW_ON_ERROR);
        $paths = array_column($sync['files'] ?? [], 'path'); sort($paths);
        if (($sync['base_sha'] ?? null) !== $package::BASE_SHA || $paths !== static::syncPaths($patch, $contract)
            || ($sync['source_worktree'] ?? null) !== $policy['source_worktree'] || ($sync['served_root'] ?? null) !== $policy['served_root']
            || ($sync['visual_before'] ?? null) !== ['file' => $policy['before_file'], 'sha256' => $policy['before_sha']]) {
            throw new RuntimeException('Native exact reviewed source-sync receipt differs.');
        }
        foreach ($sync['files'] as $record) {
            $a = file_get_contents($policy['served_root'].'/'.$record['path']); $b = file_get_contents($policy['source_worktree'].'/'.$record['path']);
            if (self::fileHash($policy['served_root'].'/'.$record['path']) !== ($record['root_after_sha256'] ?? null)
                || self::fileHash($policy['source_worktree'].'/'.$record['path']) !== ($record['worktree_sha256'] ?? null)) {
                throw new RuntimeException('Native served/source bytes drifted after reviewed sync.');
            }
            if ($record['path'] === $policy['controller_path']) {
                if (preg_match('/^[a-f0-9]{64}$/D', $policy['controller_root_after_sha']) !== 1
                    || preg_match('/^[a-f0-9]{64}$/D', $policy['controller_worktree_after_sha']) !== 1
                    || hash('sha256', $a) !== $policy['controller_root_after_sha'] || hash('sha256', $b) !== $policy['controller_worktree_after_sha']
                    || ($record['root_before_sha256'] ?? null) !== ($before['shared_source_hashes'][$policy['controller_path']]['root'] ?? null)) {
                    throw new RuntimeException('Native controller differs from exact approved foreign-inclusive bytes.');
                }
            } elseif ($a !== $b && !(str_ends_with($record['path'], '.php') && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b))) {
                throw new RuntimeException('Native source mirror differs beyond preserved PHP EOL.');
            }
        }
        $unchangedController = 'app/Http/Controllers/PageController.php';
        if (self::fileHash($policy['served_root'].'/'.$unchangedController) !== $before['shared_source_hashes'][$unchangedController]['root']
            || self::fileHash($policy['source_worktree'].'/'.$unchangedController) !== $before['shared_source_hashes'][$unchangedController]['worktree']) {
            throw new RuntimeException('Native release cannot change global PageController/title heuristics.');
        }
        if (empty($before['visual_hashes']) || empty($before['frozen_hashes'])) { throw new RuntimeException('Native visual/frozen BEFORE is incomplete.'); }
        foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
            foreach ($hashes as $path => $sha) {
                if (!hash_equals($sha, self::fileHash($policy['served_root'].'/'.$path))) {
                    throw new RuntimeException('Native visual or historical frozen source changed.');
                }
            }
        }
        return $before;
    }
}
