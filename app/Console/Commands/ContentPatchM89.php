<?php

namespace App\Console\Commands;

use App\Services\M89ContentPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit local CLI only; never a startup, scheduler or HTTP content writer. */
class ContentPatchM89 extends Command
{
    protected $signature = 'content:patch-present-perfect-completion-continuous-m89 {--plan=} {--apply} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description = 'Scoped M89 native teaching preview/apply; no Page or question writes';
    public function handle(): int
    {
        try {
            if ($this->option('local-target') !== 'gramlyze.loc' || !is_string($this->option('local-proof'))
                || preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $this->option('local-proof')) !== 1) {
                throw new RuntimeException('M89 requires gramlyze.loc and fresh nonce proof.');
            }
            if (!$this->option('apply') && $this->option('backup') !== null) { throw new RuntimeException('M89 backup requires explicit apply.'); }
            $dir = storage_path('app/seo-m89-local');
            if (!is_dir($dir) || is_link($dir)) { throw new RuntimeException('M89 private directory missing or linked.'); }
            $path = static function ($name) use ($dir) {
                if (!is_string($name) || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1) {
                    throw new RuntimeException('M89 private JSON basename required.');
                }
                return $dir.'/'.$name;
            };
            $patch = new M89ContentPatch(DB::connection(), database_path(), $dir, $this->option('local-target'), $this->option('local-proof'));
            $planPath = $path($this->option('plan'));
            if ($this->option('apply')) { $result = $patch->apply($planPath, $path($this->option('backup')), $this->option('database')); }
            else {
                $plan = $patch->savePlan($planPath);
                $result = ['status' => 'dry-run', 'connection' => $plan['connection'], 'state' => $plan['state'],
                    'updated' => count($plan['updates']), 'inserted' => count($plan['inserts']),
                    'protected_tables' => count($plan['protected']), 'sha256' => $plan['sha256']];
            }
            $result += ['inserted_questions' => 0, 'updated_question_family_rows' => 0, 'updated_pages' => 0, 'deleted' => 0];
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e instanceof RuntimeException && !$e instanceof \PDOException
                ? $e->getMessage() : 'M89 incomplete; no successful transaction committed.');
            return self::FAILURE;
        }
    }
}
