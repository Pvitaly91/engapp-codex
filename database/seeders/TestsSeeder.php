<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TestsSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('tests.sql');
        if (File::exists($path)) {
            DB::unprepared(File::get($path));
        }
    }
}
