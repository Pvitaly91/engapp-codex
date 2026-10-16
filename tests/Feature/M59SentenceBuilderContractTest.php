<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M59PassiveFutureAgentReviewPackage as Package;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Registry/source contracts only; actual question/matcher/UI acceptance is separate. */
class M59SentenceBuilderContractTest extends TestCase
{
    private function fixtureRoot(): string
    {
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $root = storage_path('app/m59-discovery-'.bin2hex(random_bytes(7)));
        File::makeDirectory($root.'/database/content-patches', 0700, true); IsolatedTestEnvironment::assertOwnedPath($root);
        return $root;
    }

    public function test_three_new_owners_have_independent_exact_level_bank_contracts_and_dynamic_registry_keeps_all_previous_owners(): void
    {
        [, $patch, $master, $contract] = Package::load();
        self::assertSame(Package::OWNERS, array_column($contract['targets'], 'identity'));
        self::assertSame(['B2', 'B1', 'B2'], array_column($contract['targets'], 'level'));
        $allContracts = Acceptance::contracts(base_path()); $targets = array_column(Acceptance::discoverTargets(base_path()), null, 'identity');
        $previous = array_values(array_diff(array_keys($allContracts), Package::OWNERS));
        self::assertSame([], array_values(array_diff($previous, array_keys($targets))));
        self::assertSame(count($previous) + count(Package::OWNERS), count($targets), 'Dynamic contract scope, not a permanent hardcoded target cap.');
        foreach ($patch['targets'] as $i => $target) {
            $declaration = $contract['targets'][$i]; self::assertSame($allContracts[$target['identity']], $declaration);
            self::assertSame([], $targets[$target['identity']]['errors']);
            self::assertContains(Package::SOURCE, $targets[$target['identity']]['sources']);
            self::assertSame(Acceptance::definitionPath($target['identity']), $declaration['definition']);
            self::assertSame($target['local_url'], $declaration['local_url']);
            $practices = array_values(array_filter($master['lessons'][$i]['blocks'], static fn ($b) => $b['type'] === 'practice-set'));
            self::assertCount(1, $practices); self::assertSame($declaration['level'], $practices[0]['level']);
            self::assertSame(['4'], $practices[0]['data']['linked_practice']['question_types']);
            self::assertSame('theory_links', $practices[0]['data']['linked_practice']['source']);
            self::assertSame($declaration['bank_seeders'], $practices[0]['data']['linked_practice']['seeder_classes']);
            self::assertNotEmpty($declaration['bank_seeders']);
        }
    }

    public function test_missing_m59_contract_is_loud_and_new_fourth_after_owner_cannot_silently_disappear(): void
    {
        [, $patch, , $contract] = Package::load(); $root = $this->fixtureRoot();
        foreach ($patch['targets'] as $target) {
            File::makeDirectory(dirname($root.'/'.$target['path']), 0700, true, true);
            File::put($root.'/'.$target['path'], M26DetailPackage::json($target['after']));
        }
        File::put($root.'/'.Package::SOURCE, M26DetailPackage::json($patch));
        $targets = Acceptance::discoverTargets($root); self::assertCount(3, $targets); self::assertSame(Package::OWNERS, array_column($targets, 'identity'));
        foreach ($targets as $target) { self::assertContains('missing_independent_bank_contract', $target['errors']); }
        File::makeDirectory($root.'/'.Acceptance::CONTRACT_DIRECTORY, 0700, true, true);
        File::put($root.'/'.Package::CONTRACT, M26DetailPackage::json($contract));
        foreach (Acceptance::discoverTargets($root) as $target) { self::assertSame([], $target['errors']); }
        $extra = $patch['targets'][0]; $extra['identity'] = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\UnplannedFourthTheorySeeder';
        $extra['path'] = Acceptance::definitionPath($extra['identity']); $patch['targets'][] = $extra;
        File::put($root.'/'.Package::SOURCE, M26DetailPackage::json($patch));
        $discovered = array_column(Acceptance::discoverTargets($root), null, 'identity'); self::assertCount(4, $discovered);
        self::assertContains('missing_independent_bank_contract', $discovered[$extra['identity']]['errors']);
        self::assertContains('missing_definition', $discovered[$extra['identity']]['errors']);
    }

    public function test_non_uk_after_and_duplicate_contract_owners_are_rejected_not_dropped(): void
    {
        [, $patch, , $contract] = Package::load(); $root = $this->fixtureRoot();
        $patch['targets'][1]['after']['page']['locale'] = 'en'; File::put($root.'/'.Package::SOURCE, M26DetailPackage::json($patch));
        $caught = null; try { Acceptance::discoverTargets($root); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught);
        File::makeDirectory($root.'/'.Acceptance::CONTRACT_DIRECTORY, 0700, true, true);
        $contract['targets'][] = $contract['targets'][0]; File::put($root.'/'.Package::CONTRACT, M26DetailPackage::json($contract));
        $caught = null; try { Acceptance::contracts($root); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught);
    }
}
