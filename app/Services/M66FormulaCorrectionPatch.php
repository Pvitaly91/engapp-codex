<?php

namespace App\Services;

use App\Support\M66VerbPatternsCorePackage as Package;
use RuntimeException;

/** One reviewed formula-spacing correction, using unchanged M26 backup/lock/transaction primitives. */
class M66FormulaCorrectionPatch extends M66ContentPatch
{
    public const PREVIOUS_SOURCE = 'database/content-patches/m66-verb-patterns-core.v2.json';
    public const PREVIOUS_SOURCE_SHA = 'de1b679e2ec4981eb00a677191dd849c230c86bad34265bc8efa0ac3688a375b';
    public const TARGET_ID = 5757;
    public const TARGET_UUID = 'df4f8737-a738-56b1-8cab-f14c047558b1';
    public const OLD_FORMULA = 'підмет + can/could/must/should/may/might/will + базова форма';
    public const NEW_FORMULA = 'підмет + can / could / must / should / may / might / will + базова форма';
    protected const ID = 'm66-verb-patterns-core-formula-correction-v1';

    private array $pending = [];
    private ?string $previousBody = null;

    protected function expectedInsertCount(): int { return 0; }

    public static function sourcePaths(array $patch, array $contract, ?string $root = null): array
    {
        $paths = [...parent::sourcePaths($patch, $contract, $root), self::PREVIOUS_SOURCE,
            'app/Services/M66FormulaCorrectionPatch.php'];
        $paths = array_values(array_unique($paths)); sort($paths); return $paths;
    }

    public function plan(bool $lock = false): array
    {
        $this->pending = []; $this->previousBody = null;
        $path = dirname($this->databasePath).'/'.self::PREVIOUS_SOURCE;
        if (!hash_equals(self::PREVIOUS_SOURCE_SHA, self::fileHash($path))) {
            throw new RuntimeException('M66 formula correction previous frozen source differs.');
        }
        $bytes = file_get_contents($path);
        if (!is_string($bytes) || !hash_equals(self::PREVIOUS_SOURCE_SHA, hash('sha256', $bytes))) {
            throw new RuntimeException('M66 formula correction previous source changed while reading.');
        }
        $previous = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        $target = $previous['targets'][0] ?? [];
        $body = $target['after']['page']['blocks'][1]['body'] ?? null;
        if (($target['identity'] ?? null) !== Package::OWNERS[0]
            || ($target['page_binding'] ?? null) !== ['id' => 193, 'page_category_id' => 33]
            || !is_string($body) || substr_count($body, self::OLD_FORMULA) !== 1) {
            throw new RuntimeException('M66 formula correction exact previous target differs.');
        }
        $this->previousBody = $body;
        $plan = parent::plan($lock);
        if ($plan['state'] !== 'after' || $plan['updates'] !== [] || $plan['inserts'] !== [] || count($this->pending) > 1) {
            throw new RuntimeException('M66 formula correction requires all three otherwise fully applied owners.');
        }
        if (($plan['sources'][self::PREVIOUS_SOURCE] ?? null) !== self::PREVIOUS_SOURCE_SHA) {
            throw new RuntimeException('M66 formula correction immutable previous-source fingerprint differs.');
        }
        $plan['updates'] = $this->pending;
        $plan['state'] = $this->pending === [] ? 'after' : 'before';
        unset($plan['sha256']); ksort($plan['sources']); $plan['sha256'] = self::digest($plan);
        return $plan;
    }

    protected function candidate(array &$updates, string $table, array $row, string $seeder, array $before, array $after): ?string
    {
        if ($table !== 'text_blocks' || (($row['uuid'] ?? null) !== self::TARGET_UUID && ($row['id'] ?? null) !== self::TARGET_ID)) {
            return parent::candidate($updates, $table, $row, $seeder, $before, $after);
        }
        if (($row['id'] ?? null) !== self::TARGET_ID || ($row['uuid'] ?? null) !== self::TARGET_UUID
            || $seeder !== Package::OWNERS[0] || ($row['seeder'] ?? null) !== $seeder
            || ($row['page_id'] ?? null) !== 193 || ($row['page_category_id'] ?? null) !== 33
            || ($row['locale'] ?? null) !== 'uk' || ($row['sort_order'] ?? null) !== 2
            || ($row['type'] ?? null) !== 'forms-grid' || ($row['column'] ?? null) !== 'left'
            || ($row['level'] ?? null) !== 'A2' || array_keys($before) !== ['body'] || array_keys($after) !== ['body']
            || $this->previousBody === null
            || $after !== ['body' => str_replace(self::OLD_FORMULA, self::NEW_FORMULA, $this->previousBody)]) {
            throw new RuntimeException('M66 formula correction exact row or single replacement differs.');
        }
        if (($row['body'] ?? null) !== $this->previousBody) {
            return parent::candidate($updates, $table, $row, $seeder, $before, $after);
        }
        $change = [];
        $state = parent::candidate($change, $table, $row, $seeder, ['body' => $this->previousBody], $after);
        if ($state !== 'before' || count($change) !== 1 || $this->pending !== []) {
            throw new RuntimeException('M66 formula correction must collect exactly one body-only change.');
        }
        $this->pending = $change;
        return 'after'; // The parent must fully validate the applied release without any pending update.
    }
}
