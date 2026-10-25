<?php

namespace Tests\Unit;

use App\Services\M88ContentPatch as Patch;
use App\Services\M88LocalTargetGuard as Guard;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure source-byte checks only; no .env/bootstrap, SQL, HTTP, process or source writes. */
class M88OriginalBankSourceProfilesTest extends TestCase
{
    private const PATHS = [
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectFormsAllLevelsLessonSeeder/definition.json',
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectFormsAllLevelsLessonSeeder.php',
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectNegativesAllLevelsLessonSeeder/definition.json',
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectNegativesAllLevelsLessonSeeder.php',
    ];

    private function sources(): array
    {
        $out = [];
        foreach (self::PATHS as $path) {
            foreach (['root' => Guard::ROOT, 'worktree' => Patch::SOURCE_WORKTREE] as $context => $root) {
                self::assertFileExists($root.'/'.$path);
                self::assertFalse(is_link($root.'/'.$path));
                $bytes = file_get_contents($root.'/'.$path);
                self::assertIsString($bytes);
                $out[$path][$context] = $bytes;
            }
        }
        return $out;
    }

    private function refused(callable $call): void
    {
        $error = null;
        try { $call(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
    }

    public function test_exact_fresh_four_path_profiles_are_literal_and_do_not_borrow_a_previous_release(): void
    {
        self::assertSame(self::PATHS, array_keys(Patch::BANK_SOURCE_PROFILES));
        self::assertSame('018a23eaf1063e263d12a037e8f2de686ddfe57a97402cd29ef99a3b9f1e7099', Patch::BANK_SOURCE_CAPTURE_SHA);
        self::assertSame('a3ec4104ee093abf801f09e4ee00164b11782149b462b17d6eff43142229aef1', Patch::BANK_SOURCE_PROFILES[self::PATHS[0]]['root']['sha256']);
        self::assertSame('b885a1d52ac10de97f4a9cd2fdfdbc1c8b50efb643f0f3b73e3cdd322226843b', Patch::BANK_SOURCE_PROFILES[self::PATHS[0]]['worktree']['sha256']);
        self::assertSame('d9f355fc3368746f58a021d52ecc8540cabcf5213daca6933a1090764f9718e1', Patch::BANK_SOURCE_PROFILES[self::PATHS[2]]['root']['sha256']);
        self::assertSame('97b7a53f52601029506b744bb44d01d1e6803def64a3816a641dcd4a37b67dfd', Patch::BANK_SOURCE_PROFILES[self::PATHS[2]]['worktree']['sha256']);
        self::assertSame(72, count(json_decode($this->sources()[self::PATHS[0]]['root'], true, flags: JSON_THROW_ON_ERROR)['questions']));
        self::assertSame(72, count(json_decode($this->sources()[self::PATHS[2]]['root'], true, flags: JSON_THROW_ON_ERROR)['questions']));
    }

    public function test_only_the_two_captured_json_eol_differences_are_accepted_without_normalization(): void
    {
        $sources = $this->sources();
        Patch::assertOriginalBankSourceBytes($sources);
        $this->addToAssertionCount(1);
        foreach ([self::PATHS[0], self::PATHS[2]] as $path) {
            self::assertNotSame($sources[$path]['root'], $sources[$path]['worktree']);
            self::assertSame(str_replace("\r\n", "\n", $sources[$path]['root']), str_replace("\r\n", "\n", $sources[$path]['worktree']));
            self::assertSame(json_decode($sources[$path]['root'], true, flags: JSON_THROW_ON_ERROR), json_decode($sources[$path]['worktree'], true, flags: JSON_THROW_ON_ERROR));
        }
        foreach ([self::PATHS[1], self::PATHS[3]] as $path) {
            self::assertSame($sources[$path]['root'], $sources[$path]['worktree']);
        }
    }

    public function test_one_byte_or_wrapper_change_in_either_context_is_rejected(): void
    {
        $sources = $this->sources();
        foreach (self::PATHS as $path) {
            foreach (['root', 'worktree'] as $context) {
                $bad = $sources;
                $bad[$path][$context] .= ' ';
                $this->refused(fn () => Patch::assertOriginalBankSourceBytes($bad));
            }
        }
    }

    public function test_normalizing_bom_context_swap_or_whole_json_replacement_does_not_refresh_authority(): void
    {
        $sources = $this->sources();
        foreach ([self::PATHS[0], self::PATHS[2]] as $path) {
            foreach (['root', 'worktree'] as $context) {
                $bad = $sources;
                $bad[$path][$context] = str_replace("\r\n", "\n", $bad[$path][$context]);
                $this->refused(fn () => Patch::assertOriginalBankSourceBytes($bad));
                $bad = $sources;
                $bad[$path][$context] = "\xEF\xBB\xBF".$bad[$path][$context];
                $this->refused(fn () => Patch::assertOriginalBankSourceBytes($bad));
            }
            $bad = $sources;
            $bad[$path] = ['root' => $sources[$path]['worktree'], 'worktree' => $sources[$path]['root']];
            $this->refused(fn () => Patch::assertOriginalBankSourceBytes($bad));
            $json = json_decode($sources[$path]['root'], true, flags: JSON_THROW_ON_ERROR);
            $json['questions'][0]['answers']['a1'] = 'Foreign';
            $bad = $sources;
            $bad[$path] = array_fill_keys(['root', 'worktree'], json_encode($json, JSON_THROW_ON_ERROR));
            $this->refused(fn () => Patch::assertOriginalBankSourceBytes($bad));
        }
    }

    public function test_foreign_missing_extra_reordered_paths_and_malformed_pair_keys_are_rejected(): void
    {
        $sources = $this->sources();
        $bad = $sources; array_pop($bad);
        $this->refused(fn () => Patch::assertOriginalBankSourceBytes($bad));
        $bad = $sources; $bad['../foreign.json'] = array_shift($bad);
        $this->refused(fn () => Patch::assertOriginalBankSourceBytes($bad));
        $bad = $sources; $bad['database/seeders/V3/Polyglot/ForeignSeeder/definition.json'] = $bad[self::PATHS[0]];
        $this->refused(fn () => Patch::assertOriginalBankSourceBytes($bad));
        $this->refused(fn () => Patch::assertOriginalBankSourceBytes(array_reverse($sources, true)));
        foreach ([['root' => 'fixture'], ['served_root' => 'fixture', 'worktree' => 'fixture'], ['worktree' => 'fixture', 'root' => 'fixture']] as $pair) {
            $bad = $sources; $bad[self::PATHS[0]] = $pair;
            $this->refused(fn () => Patch::assertOriginalBankSourceBytes($bad));
        }
    }

    public function test_the_first_connection_step_rejects_changed_banks_before_any_database_method(): void
    {
        $db = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()
            ->onlyMethods(['getDriverName', 'getConfig', 'selectOne', 'getPdo', 'statement'])->getMock();
        foreach (['getDriverName', 'getConfig', 'selectOne', 'getPdo', 'statement'] as $method) {
            $db->expects(self::never())->method($method);
        }
        M88ChangedBankSourcePairPatch::$fixture = $this->sources();
        M88ChangedBankSourcePairPatch::$fixture[self::PATHS[0]]['worktree'] .= ' ';
        $patch = new M88ChangedBankSourcePairPatch($db, '/unused/database', '/unused/private');
        $this->refused(fn () => $patch->connection(null, true));
    }
}

class M88ChangedBankSourcePairPatch extends Patch
{
    public static array $fixture = [];
    protected static function originalBankSourceBytes(): array { return self::$fixture; }
}
