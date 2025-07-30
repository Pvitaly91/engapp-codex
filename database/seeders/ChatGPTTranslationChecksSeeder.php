<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatGPTTranslationChecksSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('chatgpt_translation_checks.sql');
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path);
        $collect = false;
        $sql = '';
        foreach ($lines as $line) {
            if (str_contains($line, 'INSERT INTO `chatgpt_translation_checks`')) {
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
