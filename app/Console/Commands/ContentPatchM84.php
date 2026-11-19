<?php

namespace App\Console\Commands;

use App\Services\M84ContentPatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit local CLI preview/apply only; no HTTP, startup or scheduler write hook. */
class ContentPatchM84 extends Command
{
    protected $signature = 'content:patch-word-order-have-got-m84 {--plan=} {--apply} {--backup=} {--database=} {--local-target=} {--local-proof=}';
    protected $description = 'Scoped M84 native content and insert-only thematic question preview/apply';

    public function handle(): int
    {
        try {
            if ($this->option('local-target') !== 'gramlyze.loc' || !is_string($this->option('local-proof'))
                || preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $this->option('local-proof')) !== 1) {
                throw new RuntimeException('M84 requires gramlyze.loc and fresh nonce proof.');
            }
            if (!$this->option('apply') && $this->option('backup') !== null) {
                throw new RuntimeException('M84 backup requires explicit apply.');
            }
            $directory = storage_path('app/seo-m84-local');
            if (!is_dir($directory) || is_link($directory)) { throw new RuntimeException('M84 private evidence directory unavailable.'); }
            $path = static function ($name) use ($directory): string {
                if (!is_string($name) || preg_match('/^[a-zA-Z0-9_-]+\.json$/D', $name) !== 1) {
                    throw new RuntimeException('M84 direct private JSON basename required.');
                }
                return $directory.'/'.$name;
            };
            $patch = new M84ContentPatch(DB::connection(), database_path(), $directory,
                $this->option('local-target'), $this->option('local-proof'));
            $planPath = $path($this->option('plan'));
            if ($this->option('apply')) {
                if (!is_file($planPath) || is_link($planPath)) { throw new RuntimeException('M84 reviewed plan is missing or linked.'); }
                $plan = json_decode(file_get_contents($planPath), true, flags: JSON_THROW_ON_ERROR);
                $questionScope = M84ContentPatch::questionPlanSummary($plan);
                // The inherited writer independently revalidates the complete plan and all evidence before any write.
                $result = $patch->apply($planPath, $path($this->option('backup')), $this->option('database'));
            } else {
                $plan = $patch->savePlan($planPath);
                $questionScope = M84ContentPatch::questionPlanSummary($plan);
                $result = ['status' => 'dry-run', 'connection' => $plan['connection'], 'state' => $plan['state'],
                    'updated' => count($plan['updates']), 'inserted' => count($plan['inserts']),
                    'protected_tables' => count($plan['protected']), 'sha256' => $plan['sha256']];
            }
            $written = $result['status'] === 'applied';
            $questionCounts = $questionScope['reviewed_question_family_rows'];
            $insertedQuestions = $written ? $questionCounts : array_fill_keys(array_keys($questionCounts), 0);
            $result += $questionScope + [
                'inserted_content_rows' => $result['inserted'],
                'planned_question_family_inserts' => $plan['state'] === 'before' ? $questionCounts : array_fill_keys(array_keys($questionCounts), 0),
                'inserted_question_family_rows' => $insertedQuestions,
                'inserted_questions' => $written ? $questionScope['reviewed_questions'] : 0,
                'updated_question_family_rows' => 0, 'deleted' => 0,
            ];
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (\Throwable $error) {
            $this->error($error instanceof RuntimeException && !$error instanceof \PDOException
                ? $error->getMessage() : 'M84 incomplete; no successful transaction committed.');
            return self::FAILURE;
        }
    }
}
