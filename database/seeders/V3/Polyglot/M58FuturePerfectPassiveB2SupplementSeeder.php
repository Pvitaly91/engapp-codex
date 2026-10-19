<?php

namespace Database\Seeders\V3\Polyglot;

use Illuminate\Database\Seeder;
use RuntimeException;

/** Provenance identity only; live inserts belong to the reviewed guarded content patch. */
final class M58FuturePerfectPassiveB2SupplementSeeder extends Seeder
{
    public function run(): void
    {
        throw new RuntimeException('Use reviewed guarded M58 content patch; direct seeding is disabled');
    }
}
