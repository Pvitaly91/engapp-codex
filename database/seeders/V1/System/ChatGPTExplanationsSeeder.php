<?php

namespace Database\Seeders\V1\System;

use App\Support\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatGPTExplanationsSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('chatgpt_explanations.sql');
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path);
        $collect = false;
        $sql = '';
        foreach ($lines as $line) {
            if (str_contains($line, 'INSERT INTO `chatgpt_explanations`')) {
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
