<?php

namespace App\Support\Database;

use Illuminate\Database\Seeder as BaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class Seeder extends BaseSeeder
{
    public function __invoke(array $parameters = [])
    {
        $result = parent::__invoke($parameters);

        $this->logRun();

        return $result;
    }

    protected function logRun(): void
    {
        if (!Schema::hasTable('seed_runs')) {
            return;
        }

        DB::table('seed_runs')->insert([
            'class_name' => static::class,
            'ran_at' => now(),
        ]);
    }
}
