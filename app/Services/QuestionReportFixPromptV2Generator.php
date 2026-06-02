<?php

namespace App\Services;

/**
 * Alternative prompt generator (V2) for /admin/question-reports.
 *
 * Independent of QuestionReportFileStore::buildFixPrompt() — different
 * structure, language, and content focus. Designed for batch consumption
 * by an editor agent: structured, English-first, grouped by seeder so
 * the agent can open each seeder file once and edit every related
 * question in a single pass.
 *
 * Highlights vs V1:
 *  - English instructions (most LLMs handle technical English best).
 *  - Reports grouped by seeder class, with file path surfaced once.
 *  - Per-report: filled question (markers replaced by accepted answers),
 *    verb_hint map, snapshot-vs-current divergence flagged.
 *  - Issue vocabulary section lists ONLY the issue types appearing in
 *    this batch (no noise about types nobody flagged).
 *  - Explicit final-response checklist so the agent's reply is consistent.
 */
class QuestionReportFixPromptV2Generator
{
    public function __construct(
        private QuestionReportIssueCatalog $issueCatalog = new QuestionReportIssueCatalog(),
    ) {
    }

    /**
     * @param  array<int, array<string, mixed>>  $reports
     */
    public function build(array $reports): string
    {
        // Group by seeder class for efficient batch editing.
        $bySeeder = [];
        foreach ($reports as $r) {
            if (! is_array($r)) {
                continue;
            }
            $cls = trim((string) data_get($r, 'question.seeder.class', '')) ?: '(unknown seeder)';
            $bySeeder[$cls][] = $r;
        }
        ksort($bySeeder);

        $totalReports = count($reports);
        $totalSeeders = count($bySeeder);

        $vocab = $this->buildVocabularySection($reports);
        $sections = [];
        $reportIndex = 0;
        foreach ($bySeeder as $cls => $items) {
            $sections[] = $this->seederSection($cls, $items, $reportIndex);
        }
        $body = implode("\n\n", $sections);

        return trim(<<<PROMPT
# Gramlyze — Question Report Fix Batch (V2)

You are an editor agent in the Gramlyze Laravel repository. Below is a batch of admin-flagged broken questions. Your task: apply minimal, targeted fixes to the seeder source files so each affected question becomes correct.

**Total reports:** {$totalReports}
**Unique seeders:** {$totalSeeders}

## Workflow

1. **Per report.** Open the seeder file at `seeder.file`. Locate the question by `question.uuid` (fall back to `question.id`). Apply every fix demanded by the listed issue types **and** the admin's comment.
2. **Per seeder.** When a seeder appears in more than one report, finish those reports first, then sweep the entire seeder for the same kinds of issues — admins typically flag only a sample, so analogous bugs likely lurk elsewhere in the file.
3. **Snapshot divergence.** When a report's `Snapshot vs current state` says "DIVERGED", treat the report-time snapshot as the failing state. The current DB may already be partially fixed; compare both to make sure your patch addresses the real divergence and isn't duplicating an in-flight edit.
4. **Scope discipline.** Do not modify questions in seeders that have no reports in this batch.
5. **Re-seed when safe.** If the seeder is idempotent (uses `updateOrCreate`-style writes — most V3 JsonTestSeeder / Page_V3 seeders are), end with `php artisan db:seed --class='<class>' --force`. If you're unsure, list the command in the final response and let a human run it.

## Issue type vocabulary (only the keys present in this batch)

{$vocab}

## Reports (grouped by seeder)

{$body}

## Final response — required format

For each report (use `id` as the key):
- **Files changed:** path + line range(s)
- **What changed:** one-line summary
- **Issue types addressed:** echo the keys

For each seeder where you swept beyond the reported questions:
- **Additional questions fixed:** count + UUIDs (or ids)
- **Same one-line summary** of edits applied

If you ran any artisan/seed commands, list the exact commands at the end.
PROMPT);
    }

    /**
     * @param  array<int, array<string, mixed>>  $reports
     */
    private function buildVocabularySection(array $reports): string
    {
        $present = collect($reports)
            ->flatMap(fn ($r) => $this->issueCatalog->normalize(array_merge(
                is_array($r['issue_types'] ?? null) ? $r['issue_types'] : [],
                is_array($r['issues'] ?? null) ? $r['issues'] : [],
            )))
            ->unique()
            ->values();

        if ($present->isEmpty()) {
            return '_(No issue checkboxes were flagged in this batch — rely on the admin comment per report.)_';
        }

        return $present
            ->map(function (string $key): string {
                $entry = $this->issueCatalog->find($key);

                if ($entry === null) {
                    return "- `{$key}`";
                }

                $action = trim((string) ($entry['prompt_instruction'] ?? $entry['prompt_directive'] ?? ''));
                $action = $action !== '' ? $action : '—';

                return "- `{$entry['key']}` — {$entry['label']}\n  Action: {$action}";
            })
            ->implode("\n");
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function seederSection(string $cls, array $items, int &$reportIndex): string
    {
        $file = trim((string) data_get($items[0] ?? [], 'question.seeder.file', ''));
        $count = count($items);

        $blocks = [];
        foreach ($items as $r) {
            $reportIndex++;
            $blocks[] = $this->reportBlock($r, $reportIndex);
        }

        $bodyBlocks = implode("\n\n──────────────────────────────────────────────\n\n", $blocks);
        $fileLine = $file !== '' ? "**File:** `{$file}`\n" : '';

        return <<<SEC
═══════════════════════════════════════════════
**SEEDER:** `{$cls}`
{$fileLine}**Reports in this seeder:** {$count}
═══════════════════════════════════════════════

{$bodyBlocks}
SEC;
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function reportBlock(array $report, int $index): string
    {
        $reportId = trim((string) ($report['id'] ?? ''));
        $status = trim((string) ($report['status'] ?? 'open'));
        $reportedAt = trim((string) ($report['reported_at'] ?? ''));
        $level = trim((string) data_get($report, 'question.level', ''));
        $qid = trim((string) data_get($report, 'question.id', ''));
        $quuid = trim((string) data_get($report, 'question.uuid', ''));
        $qtext = trim((string) data_get($report, 'question.text', ''));
        $testSlug = trim((string) data_get($report, 'test.slug', ''));
        $testUrl = trim((string) data_get($report, 'test.url', ''));

        // Build accepted answer + verb_hint maps.
        [$answers, $verbHints] = $this->extractAnswersAndVerbHints($report);

        $filled = $qtext !== ''
            ? (preg_replace_callback('/\{([^}]+)\}/', function ($m) use ($answers) {
                $key = trim($m[1]);

                return $answers->has($key) ? $answers->get($key) : $m[0];
            }, $qtext) ?? $qtext)
            : '';

        $options = collect(data_get($report, 'question.options', []))
            ->map(function ($o) {
                if (is_array($o)) {
                    return trim((string) ($o['option'] ?? $o['value'] ?? ''));
                }

                return trim((string) $o);
            })
            ->filter()
            ->values();

        $issueTypes = $this->issueCatalog->normalize(array_merge(
            is_array($report['issue_types'] ?? null) ? $report['issue_types'] : [],
            is_array($report['issues'] ?? null) ? $report['issues'] : [],
        ));
        $issueLines = collect($issueTypes)
            ->map(function (string $key): string {
                $entry = $this->issueCatalog->find($key);

                return $entry !== null
                    ? "  - {$key}: {$entry['label']}"
                    : "  - {$key}";
            })
            ->implode("\n");
        $issueText = $issueLines !== '' ? $issueLines : '  - (no checkboxes — see admin comment)';

        $comment = trim((string) ($report['comment'] ?? ''));
        $commentRendered = $comment !== ''
            ? collect(preg_split('/\R/', $comment) ?: [$comment])
                ->map(fn (string $line): string => '> ' . $line)
                ->implode("\n")
            : '> (empty)';

        // Snapshot diff.
        $diff = is_array($report['snapshot_diff'] ?? null) ? $report['snapshot_diff'] : [];
        $diffSection = $this->renderDiffSection($report, $diff);

        $optsLine = $options->isNotEmpty() ? '[' . $options->implode(', ') . ']' : '[]';
        $answersLine = $answers->isNotEmpty()
            ? $answers->map(fn ($v, $k) => "{$k}=>\"{$v}\"")->implode(', ')
            : '(none)';
        $verbHintsLine = $verbHints->isNotEmpty()
            ? $verbHints->map(fn ($v, $k) => "{$k}=>\"{$v}\"")->implode(', ')
            : '(none)';

        return <<<RPT
**Report #{$index}** `id={$reportId}` · status={$status} · reported_at={$reportedAt}

- question.uuid: `{$quuid}`
- question.id: {$qid}
- level: {$level}
- test: {$testSlug}
- test_url: {$testUrl}
- question_text: "{$qtext}"
- filled_question: "{$filled}"
- accepted_answers: { {$answersLine} }
- verb_hints: { {$verbHintsLine} }
- options: {$optsLine}

Issue types flagged by admin:
{$issueText}

Admin comment:
{$commentRendered}

{$diffSection}
RPT;
    }

    /**
     * Build accepted-answer and verb-hint maps from the report's question
     * payload (and snapshot, when available).
     *
     * @param  array<string, mixed>  $report
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     */
    private function extractAnswersAndVerbHints(array $report): array
    {
        $answers = collect(data_get($report, 'question.answers', []))
            ->filter(fn ($a) => is_array($a))
            ->mapWithKeys(function ($a) {
                $marker = trim((string) ($a['marker'] ?? ''));
                $value = trim((string) ($a['answer']
                    ?? $a['option_value']
                    ?? $a['value']
                    ?? ''));

                return $marker !== '' && $value !== '' ? [$marker => $value] : [];
            });

        $snapshot = is_array($report['question_snapshot'] ?? null) ? $report['question_snapshot'] : [];
        $verbHints = collect(data_get($snapshot, 'verb_hints', []))
            ->filter(fn ($h) => is_array($h))
            ->mapWithKeys(function ($h) {
                $marker = trim((string) ($h['marker'] ?? ''));
                $value = trim((string) ($h['option_value']
                    ?? $h['value']
                    ?? $h['verb_hint']
                    ?? ''));

                return $marker !== '' && $value !== '' ? [$marker => $value] : [];
            });

        // Legacy fallback: verb_hint stored alongside the answer entry.
        foreach (data_get($report, 'question.answers', []) as $a) {
            if (! is_array($a)) {
                continue;
            }
            $marker = trim((string) ($a['marker'] ?? ''));
            $hint = trim((string) ($a['verb_hint'] ?? ''));
            if ($marker !== '' && $hint !== '' && ! $verbHints->has($marker)) {
                $verbHints->put($marker, $hint);
            }
        }

        return [$answers, $verbHints];
    }

    /**
     * @param  array<string, mixed>  $report
     * @param  array<string, mixed>  $diff
     */
    private function renderDiffSection(array $report, array $diff): string
    {
        $hasChanges = (bool) ($diff['has_changes'] ?? false);
        $snapshotStatus = trim((string) ($report['snapshot_status'] ?? 'missing'));

        if (! $hasChanges) {
            return "Snapshot vs current state — same. Snapshot status: {$snapshotStatus}.";
        }

        $lines = [];
        foreach ($diff as $key => $section) {
            if ($key === 'has_changes' || ! is_array($section)) {
                continue;
            }
            $label = (string) ($section['label'] ?? $key);
            $statusLabel = (string) ($section['status_label']
                ?? $section['status']
                ?? '?');
            $lines[] = sprintf('  - %s: %s', $label, $statusLabel);
        }

        $linesText = $lines !== [] ? implode("\n", $lines) : '  - (no per-field detail)';

        return "Snapshot vs current state — DIVERGED. Snapshot status: {$snapshotStatus}.\n{$linesText}";
    }
}
