<?php

namespace App\Support\Database;

use Illuminate\Database\Seeder as BaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

abstract class Seeder extends BaseSeeder
{
    public function __invoke(array $parameters = [])
    {
        if (!$this->shouldRun()) {
            return null;
        }

        $result = parent::__invoke($parameters);

        $this->logRun();

        return $result;
    }

    protected function shouldRun(): bool
    {
        if (!Schema::hasTable('seed_runs')) {
            return true;
        }

        return !DB::table('seed_runs')
            ->where('class_name', static::class)
            ->exists();
    }

    protected function logRun(): void
    {
        if (!Schema::hasTable('seed_runs')) {
            return;
        }

        DB::table('seed_runs')->updateOrInsert(
            ['class_name' => static::class],
            ['ran_at' => now()]
        );
    }

    protected function makeTextBlockUuid(string $slug, int $sortOrder, array $block = []): string
    {
        $name = implode('|', [
            static::class,
            $slug,
            $block['type'] ?? '',
            $block['column'] ?? '',
            $sortOrder,
        ]);

        return (string) Uuid::uuid5(Uuid::NAMESPACE_URL, $name);
    }
}
