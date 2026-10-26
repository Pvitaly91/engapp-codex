<?php

namespace App\Console\Commands;

use App\Services\M94ContentPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit local CLI: exact three continuing rows, one Page text and six appended blocks; no question or delete writes. */
class ContentPatchM94 extends Command
{
    protected $signature = 'content:patch-relative-clauses-whose-which-m94 {--plan=} {--apply} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description = 'Scoped M94 local preview/apply; one exact Page text, three continuing rows and six native appends';

    public function handle(): int
    {
        try {
            if ($this->option('local-target') !== 'gramlyze.loc' || !is_string($this->option('local-proof'))
                || preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $this->option('local-proof')) !== 1) {
                throw new RuntimeException('M94 requires gramlyze.loc and fresh nonce proof.');
            }
            if (!$this->option('apply') && $this->option('backup') !== null) { throw new RuntimeException('M94 backup requires explicit apply.'); }
            $dir = storage_path('app/seo-m94-local');
            if (!is_dir($dir) || is_link($dir)) { throw new RuntimeException('M94 private directory missing or linked.'); }
            $path = static function ($name) use ($dir) {
                if (!is_string($name) || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1) {
                    throw new RuntimeException('M94 private JSON basename required.');
                }
                return $dir.'/'.$name;
            };
            $patch = new M94ContentPatch(DB::connection(), database_path(), $dir, $this->option('local-target'), $this->option('local-proof'));
            $planPath = $path($this->option('plan')); $updatedPages = 0;
            if ($this->option('apply')) {
                // Read reporting counts before any transaction; these values grant no write authority.
                $reviewed = json_decode(file_get_contents($planPath), true, flags: JSON_THROW_ON_ERROR);
                $result = $patch->apply($planPath, $path($this->option('backup')), $this->option('database'));
                if (($result['status'] ?? null) === 'applied') {
                    // The service independently exact-validates the private plan before committing.
                    $updatedPages = count(array_filter($reviewed['updates'], static fn ($row) => $row['table'] === 'pages'));
                }
            } else {
                $plan = $patch->savePlan($planPath);
                $updatedPages = count(array_filter($plan['updates'], static fn ($row) => $row['table'] === 'pages'));
                $result = ['status' => 'dry-run', 'connection' => $plan['connection'], 'state' => $plan['state'],
                    'updated' => count($plan['updates']), 'inserted' => count($plan['inserts']),
                    'protected_tables' => count($plan['protected']), 'sha256' => $plan['sha256']];
            }
            $result += ['inserted_questions' => 0, 'updated_question_family_rows' => 0, 'updated_pages' => $updatedPages, 'deleted' => 0];
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e instanceof RuntimeException && !$e instanceof \PDOException
                ? $e->getMessage() : 'M94 incomplete; no successful transaction committed.');
            return self::FAILURE;
        }
    }
}
