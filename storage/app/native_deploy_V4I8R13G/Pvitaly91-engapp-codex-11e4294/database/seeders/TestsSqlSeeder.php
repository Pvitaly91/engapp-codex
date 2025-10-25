<?php
namespace Database\Seeders;

use App\Support\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestsSqlSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('tests.sql');
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path);
        $collect = false;
        $sql = '';
        foreach ($lines as $line) {
            if (str_contains($line, 'INSERT INTO `tests`')) {
                $collect = true;
            }
            if ($collect) {
                $sql .= $line;
            }
        }

        if ($sql) {
            DB::unprepared($sql);
        }
    }
}
