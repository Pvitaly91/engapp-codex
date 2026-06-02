<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('seed_runs')) {
            return;
        }

        $this->deduplicateSeedRuns();

        Schema::table('seed_runs', function (Blueprint $table) {
            $table->unique('class_name');
        });

        $existing = DB::table('seed_runs')
            ->pluck('class_name')
            ->all();

        $known = array_flip($existing);

        foreach ($this->discoverSeederClasses(base_path('database/seeders')) as $seederClass) {
            if (!isset($known[$seederClass])) {
                DB::table('seed_runs')->insert([
                    'class_name' => $seederClass,
                    'ran_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('seed_runs')) {
            return;
        }

        Schema::table('seed_runs', function (Blueprint $table) {
            $table->dropUnique('seed_runs_class_name_unique');
        });
    }

    private function deduplicateSeedRuns(): void
    {
        $duplicates = DB::table('seed_runs')
            ->select('class_name', DB::raw('MIN(id) as keep_id'))
            ->groupBy('class_name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('seed_runs')
                ->where('class_name', $duplicate->class_name)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }
    }

    private function discoverSeederClasses(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        return collect(File::allFiles($directory))
            ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
            ->map(fn (SplFileInfo $file) => $this->classFromFile($file, $directory))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function classFromFile(SplFileInfo $file, string $baseDirectory): ?string
    {
        $relativePath = Str::after($file->getPathname(), rtrim($baseDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

        if ($relativePath === $file->getPathname()) {
            return null;
        }

        $classPath = str_replace('.php', '', $relativePath);
        $classPath = str_replace(['/', '\\'], '\\', $classPath);

        return 'Database\\Seeders\\' . $classPath;
    }
};
