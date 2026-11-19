<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Question;
use App\Models\TextBlock;
use App\Services\PracticeCompletionLocalTargetGuard;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use App\Support\PracticeCompletionLinkedPractice;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Manually invoked, read-only local release gate; it never applies content or clears caches. */
class AuditTheorySentenceBuilders extends Command
{
    protected $signature = 'theory:audit-sentence-builders
        {--target= : Explicit local target, must be gramlyze.loc}
        {--source=* : Versioned content-patch basename; omit to audit the entire discovered registry}';

    protected $description = 'Audit one native five-question thematic sentence builder per modernized theory lesson';

    public function handle(TextBlockToQuestionsMatcherService $matcher): int
    {
        if ($this->option('target') !== 'gramlyze.loc'
            || strtolower(str_replace('\\', '/', (string) realpath(base_path()))) !== 'd:/dev/htdocs/gramlyze.loc') {
            $this->error('This read-only gate requires --target=gramlyze.loc and the inspected served application ROOT.');

            return self::FAILURE;
        }
        $db = DB::connection();
        $context = ['stage' => 'local_configuration'];
        try {
            PracticeCompletionLocalTargetGuard::assertConfiguredDatabase($db);
            $context = ['stage' => 'target_discovery'];
            $targets = Acceptance::discoverTargets(base_path());
            $sources = $this->option('source');
            foreach ($sources as $source) {
                if (!is_string($source) || preg_match('/^[a-z0-9][a-z0-9._-]*\.json$/D', $source) !== 1
                    || str_contains($source, '..') || !is_file(base_path('database/content-patches/'.$source))) {
                    throw new RuntimeException('An exact existing content-patch basename is required.');
                }
                $context = ['stage' => 'source_selection', 'source_basename' => $source];
            }
            if ($sources !== []) {
                $relative = array_map(static fn ($source) => 'database/content-patches/'.$source, $sources);
                $targets = array_values(array_filter($targets, static fn ($target) => array_intersect($target['sources'], $relative) !== []));
            }
            if ($targets === []) {
                throw new RuntimeException('No registered AFTER theory targets; a draft or BEFORE is not an acceptance scope.');
            }
            $db->getPdo()->exec('SET TRANSACTION READ ONLY');
            $db->beginTransaction();
            $runtime = PracticeCompletionLocalTargetGuard::publicIdentity($db, public_path());
            $rows = [];
            foreach ($targets as $target) {
                $context = ['stage' => 'target_audit', 'owner' => $target['identity'], 'url' => $target['local_url']];
                $row = ['url' => $target['local_url'], 'identity' => $target['identity'],
                    'sources' => $target['sources'], 'errors' => $target['errors'],
                    'browser_visibility' => 'not_checked', 'interaction' => 'not_checked'];
                if ($row['errors'] !== []) { $rows[] = $row; continue; }
                $pages = Page::query()->where('seeder', $target['identity'])->where('type', 'theory')->get();
                if ($pages->count() !== 1) {
                    $row['errors'][] = 'missing_or_ambiguous_page_owner'; $rows[] = $row; continue;
                }
                $page = $pages->first();
                $blocks = TextBlock::query()->where('page_id', $page->id)->where('locale', 'uk')->where('type', 'practice-set')->get();
                if ($blocks->count() !== 1) {
                    $row['errors'][] = 'missing_or_duplicate_practice'; $rows[] = $row; continue;
                }
                $block = $blocks->first();
                $original = $block->getAttributes();
                $copy = PracticeCompletionLinkedPractice::matchingBlock($block, true);
                $data = json_decode((string) $copy->body, true, flags: JSON_THROW_ON_ERROR);
                $config = is_array($data['linked_practice'] ?? null) ? $data['linked_practice'] : null;
                $uuids = TextBlock::query()->where('page_id', $page->id)->pluck('uuid')->all();
                // Independent expected banks: changing the actual config cannot make a foreign pool pass.
                $query = Question::query()->whereHas('theoryTextBlocks', static function ($q) use ($uuids): void {
                    $q->whereIn('text_blocks.uuid', $uuids);
                })->where('questions.type', Question::TYPE_COMPOSE_TOKENS)
                    ->whereIn('questions.seeder', $target['contract']['bank_seeders'])->orderBy('questions.uuid');
                if (preg_match('/^[ABC][12]$/D', (string) $target['contract']['level']) === 1) {
                    $query->where('questions.level', $target['contract']['level']);
                }
                $eligible = $query->get(['questions.id', 'questions.type', 'questions.level', 'questions.seeder']);
                $selected = $matcher->findQuestionsForTextBlocks(new Collection([$copy]), 5)[$block->uuid] ?? collect();
                $http = $this->getLocal($target['local_url']);
                $result = Acceptance::check($target['contract'], $page, $block, $config, $eligible, $selected, $http['html']);
                unset($result['payload']);
                $row += ['page_id' => $page->id, 'practice_uuid' => $block->uuid, 'practice_id' => $block->id,
                    'effective_config' => $config, 'http_status' => $http['status'], 'final_url' => $http['url'],
                    'http_sha256' => hash('sha256', $http['html'])];
                $row = array_replace($row, $result);
                if ($http['status'] !== 200 || $http['url'] !== $target['local_url']) { $row['errors'][] = 'http_status_or_url_differs'; }
                if ($original !== $block->getAttributes()) { $row['errors'][] = 'original_model_mutated'; }
                $row['ok'] = $row['errors'] === [];
                $rows[] = $row;
            }
            $db->rollBack();
            $failed = count(array_filter($rows, static fn ($row) => ($row['errors'] ?? []) !== []));
            $this->line(json_encode(['at' => gmdate('c'), 'runtime' => $runtime,
                'proof_kind' => 'read-only CLI matcher plus fresh local HTTP; not browser acceptance',
                'targets' => count($rows), 'failed' => $failed, 'writes' => 0, 'rows' => $rows],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return $failed === 0 ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $error) {
            // No credentials, DSNs, query bindings, cookies or stack traces in audit output.
            $this->error(json_encode(['error_class' => get_class($error), 'context' => $context,
                'message' => 'Sentence-builder gate could not complete. No content apply was attempted.'],
                JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return self::FAILURE;
        } finally {
            if ($db->transactionLevel() > 0) { $db->rollBack(); }
        }
    }

    private function getLocal(string $url): array
    {
        if (preg_match('~^http://gramlyze\.loc/theory/[a-z0-9-]+(?:/[a-z0-9-]+)+$~D', $url) !== 1) {
            throw new RuntimeException('HTTP target is outside local theory.');
        }
        $curl = curl_init($url);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 30, CURLOPT_PROXY => '',
            CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_HTTPHEADER => ['Accept: text/html']]);
        try {
            $html = curl_exec($curl);
            if (!is_string($html) || curl_getinfo($curl, CURLINFO_PRIMARY_IP) !== '127.0.0.1') {
                throw new RuntimeException('Local GET unavailable.');
            }

            return ['html' => $html, 'status' => curl_getinfo($curl, CURLINFO_RESPONSE_CODE),
                'url' => curl_getinfo($curl, CURLINFO_EFFECTIVE_URL)];
        } finally {
            curl_close($curl);
        }
    }
}
