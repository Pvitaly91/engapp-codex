<?php

namespace App\Support\Database;

use Illuminate\Database\Seeder as BaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class Seeder extends BaseSeeder
{
    public function __invoke()
    {
        $this->logRun();

        return parent::__invoke();
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
