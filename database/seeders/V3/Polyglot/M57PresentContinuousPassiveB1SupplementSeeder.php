<?php

namespace Database\Seeders\V3\Polyglot;

use Illuminate\Database\Seeder;
use RuntimeException;

/** Stable bank provenance only. Reviewed M57 insert projection owns all five new question rows. */
final class M57PresentContinuousPassiveB1SupplementSeeder extends Seeder
{
    public function run(): void
    {
        throw new RuntimeException('Use reviewed guarded M57 content patch; direct seeding is disabled.');
    }
}
