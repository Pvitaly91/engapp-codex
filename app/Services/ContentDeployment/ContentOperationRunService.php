<?php

namespace App\Services\ContentDeployment;

use App\Models\ContentOperationRun;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ContentOperationRunService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function start(array $context): ContentOperationRun
    {
        return ContentOperationRun::query()->create([
            'replayed_from_run_id' => $context['replayed_from_run_id'] ?? null,
            'operation_kind' => $this->stringValue($context['operation_kind'] ?? null) ?? 'apply_changed',
            'trigger_source' => $this->stringValue($context['trigger_source'] ?? null) ?? 'cli',
            'domains' => $this->domainsValue($context['domains'] ?? []),
            'base_ref' => $this->stringValue($context['base_ref'] ?? null),
            'head_ref' => $this->stringValue($context['head_ref'] ?? null),
            'base_refs_by_domain' => $this->baseRefsByDomainValue($context['base_refs_by_domain'] ?? null),
            'dry_run' => (bool) ($context['dry_run'] ?? false),
            'strict' => (bool) ($context['strict'] ?? false),
            'with_release_check' => array_key_exists('with_release_check', $context)
                ? (bool) $context['with_release_check']
                : null,
            'skip_release_check' => array_key_exists('skip_release_check', $context)
                ? (bool) $context['skip_release_check']
                : null,
            'bootstrap_uninitialized' => (bool) ($context['bootstrap_uninitialized'] ?? false),
            'status' => null,
            'started_at' => now(),
            'finished_at' => null,
            'operator_user_id' => $context['operator_user_id'] ?? null,
            'summary' => null,
            'payload_json_path' => null,
            'report_path' => $this->normalizeArtifactPath($context['report_path'] ?? null),
            'error_excerpt' => null,
            'meta' => is_array($context['meta'] ?? null) ? $context['meta'] : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $summary
     */
    public function finishSuccess(ContentOperationRun $run, array $payload, array $summary = []): ContentOperationRun
    {
        return $this->finish($run, 'success', $payload, null, $summary);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $summary
     */
    public function finishDryRun(ContentOperationRun $run, array $payload, array $summary = []): ContentOperationRun
    {
        return $this->finish($run, 'dry_run', $payload, null, $summary);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $summary
     */
    public function finishBlocked(ContentOperationRun $run, array $payload, array $summary = []): ContentOperationRun
    {
        return $this->finish($run, 'blocked', $payload, null, $summary);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $summary
     */
    public function finishPartial(ContentOperationRun $run, array $payload, array $summary = []): ContentOperationRun
    {
        return $this->finish($run, 'partial', $payload, null, $summary);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $summary
     */
    public function finishFailure(ContentOperationRun $run, Throwable|array|string $error, array $payload = [], array $summary = []): ContentOperationRun
    {
        return $this->finish($run, 'failed', $payload, $error, $summary);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function latest(array $filters = [], int $limit = 20): Collection
    {
        $query = ContentOperationRun::query()->orderByDesc('started_at')->orderByDesc('id');

        if (($kind = $this->stringValue($filters['kind'] ?? null)) !== null) {
            $query->where('operation_kind', $kind);
        }

        if (($status = $this->stringValue($filters['status'] ?? null)) !== null) {
            $query->where('status', $status);
        }

        $domains = $this->domainsValue($filters['domains'] ?? []);

        foreach ($domains as $domain) {
            $query->whereJsonContains('domains', $domain);
        }

        return $query->limit(max(1, $limit))->get();
    }

    public function findWithArtifacts(int $id): ?ContentOperationRun
    {
        $run = ContentOperationRun::query()
            ->with([
                'replayedFrom:id,operation_kind,status',
                'replays:id,replayed_from_run_id,operation_kind,status,started_at',
            ])
            ->find($id);

        if ($run === null) {
            return null;
        }

        $run->setAttribute('payload_json', $this->loadPayload($run));
        $run->setAttribute('payload_json_absolute_path', $this->absoluteArtifactPath($run->payload_json_path));
        $run->setAttribute('report_absolute_path', $this->absoluteArtifactPath($run->report_path));

        return $run;
    }

    public function loadPayload(ContentOperationRun $run): ?array
    {
        $relativePath = $this->normalizeArtifactPath($run->payload_json_path);

        if ($relativePath === null || ! Storage::disk('local')->exists($relativePath)) {
            return null;
        }

        $decoded = json_decode((string) Storage::disk('local')->get($relativePath), true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $summary
     */
    private function finish(
        ContentOperationRun $run,
        string $status,
        array $payload = [],
        Throwable|array|string|null $error = null,
        array $summary = []
    ): ContentOperationRun {
        $artifactWarning = null;
        $payloadJsonPath = null;

        try {
            if ($payload !== []) {
                $payloadJsonPath = $this->writePayloadArtifact($run->id, $payload);
            }
        } catch (Throwable $artifactException) {
            $artifactWarning = 'Payload artifact write failed: ' . $artifactException->getMessage();
        }

        $derivedSummary = array_merge(
            $this->deriveSummary($run->operation_kind, $payload, $status),
            $summary
        );
        $refs = $this->extractRefs($payload);
        $domains = $this->extractDomains($payload);
        $errorExcerpt = $this->errorExcerpt($error, $payload);

        if ($artifactWarning !== null) {
            $errorExcerpt = trim($errorExcerpt !== null ? $errorExcerpt . ' | ' . $artifactWarning : $artifactWarning);
        }

        $run->forceFill([
            'domains' => $domains !== [] ? $domains : $run->domains,
            'base_ref' => $refs['base_ref'] ?? $run->base_ref,
            'head_ref' => $refs['head_ref'] ?? $run->head_ref,
            'base_refs_by_domain' => $refs['base_refs_by_domain'] !== [] ? $refs['base_refs_by_domain'] : $run->base_refs_by_domain,
            'status' => $status,
            'finished_at' => now(),
            'summary' => $derivedSummary !== [] ? $derivedSummary : null,
            'payload_json_path' => $payloadJsonPath ?? $run->payload_json_path,
            'report_path' => $this->normalizeArtifactPath(data_get($payload, 'artifacts.report_path')) ?? $run->report_path,
            'error_excerpt' => $errorExcerpt,
            'meta' => $this->mergedMeta($run->meta, $payload, $artifactWarning),
        ])->save();

        return $run->fresh() ?? $run;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function writePayloadArtifact(int $runId, array $payload): string
    {
        $datePath = now()->format('Y/m/d');
        $relativePath = 'content-operation-runs/' . $datePath . '/' . $runId . '.json';

        Storage::disk('local')->put(
            $relativePath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function mergedMeta(?array $meta, array $payload, ?string $artifactWarning = null): ?array
    {
        $merged = is_array($meta) ? $meta : [];

        if (($runMode = $this->stringValue(data_get($payload, 'status'))) !== null) {
            $merged['result_status'] = $runMode;
        }

        if (($stoppedPhase = $this->stringValue(data_get($payload, 'error.phase'))) !== null) {
            $merged['stopped_phase'] = $stoppedPhase;
        }

        if (($stoppedPackage = $this->stringValue(data_get($payload, 'error.package'))) !== null) {
            $merged['stopped_package'] = $stoppedPackage;
        }

        if (is_array(data_get($payload, 'lock'))) {
            $merged['lock'] = [
                'status' => data_get($payload, 'lock.status'),
                'acquired' => (bool) data_get($payload, 'lock.acquired', false),
                'operation_kind' => data_get($payload, 'lock.operation_kind'),
                'trigger_source' => data_get($payload, 'lock.trigger_source'),
                'content_operation_run_id' => data_get($payload, 'lock.content_operation_run_id'),
                'reason' => data_get($payload, 'error.reason'),
                'takeover' => data_get($payload, 'lock.takeover'),
                'lock_snapshot' => data_get($payload, 'lock.lock'),
            ];
        }

        if ($artifactWarning !== null) {
            $merged['artifact_warning'] = $artifactWarning;
        }

        return $merged !== [] ? $merged : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{base_ref:?string,head_ref:?string,base_refs_by_domain:array<string,string|null>}
     */
    private function extractRefs(array $payload): array
    {
        $deploymentBase = $this->stringValue(data_get($payload, 'deployment.base_ref'));
        $deploymentHead = $this->stringValue(data_get($payload, 'deployment.head_ref'));
        $replayBase = $this->stringValue(data_get($payload, 'replay.resolved_context.base_ref'));
        $replayHead = $this->stringValue(data_get($payload, 'replay.resolved_context.head_ref'));

        if ($replayBase !== null || $replayHead !== null) {
            return [
                'base_ref' => $replayBase,
                'head_ref' => $replayHead,
                'base_refs_by_domain' => $this->baseRefsByDomainValue(data_get($payload, 'replay.resolved_context.base_refs_by_domain'))
                    ?? [],
            ];
        }

        if ($deploymentBase !== null || $deploymentHead !== null) {
            return [
                'base_ref' => $deploymentBase,
                'head_ref' => $deploymentHead,
                'base_refs_by_domain' => $this->baseRefsByDomainValue(data_get($payload, 'content_sync.before.domains'))
                    ?? [],
            ];
        }

        return [
            'base_ref' => $this->stringValue(data_get($payload, 'diff.base')),
            'head_ref' => $this->stringValue(data_get($payload, 'diff.head', data_get($payload, 'plan.deployment_refs.current_deployed_ref'))),
            'base_refs_by_domain' => $this->baseRefsByDomainValue(data_get($payload, 'diff.base_refs_by_domain')) ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function extractDomains(array $payload): array
    {
        $domains = $this->domainsValue(data_get(
            $payload,
            'replay.resolved_context.domains',
            data_get($payload, 'scope.domains', data_get($payload, 'plan.options.domains', data_get($payload, 'domains_before')))
        ));

        if ($domains !== []) {
            return $domains;
        }

        $syncDomains = array_keys((array) data_get($payload, 'domains_before', data_get($payload, 'content_sync.before.domains', [])));

        return $this->domainsValue($syncDomains);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function deriveSummary(string $operationKind, array $payload, string $status): array
    {
        $canonical = $this->canonicalApplyPayload($payload);
        $planSummary = (array) data_get($canonical, 'plan.summary', data_get($payload, 'plan.summary', data_get($payload, 'plan.content_plan.summary', [])));
        $execution = [
            'cleanup_deleted' => (array) data_get($canonical, 'execution.cleanup_deleted', []),
            'upsert_present' => (array) data_get($canonical, 'execution.upsert_present', []),
        ];
        $bootstrapDomains = collect(array_merge(
            array_values((array) data_get($payload, 'plan.bootstrap.required_domains', [])),
            array_map(static fn (array $entry): string => (string) ($entry['domain'] ?? 'unknown'), (array) data_get($payload, 'apply.bootstrap.simulated', [])),
            array_map(static fn (array $entry): string => (string) ($entry['domain'] ?? 'unknown'), (array) data_get($payload, 'apply.bootstrap.applied', []))
        ))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'operation_kind' => $operationKind,
            'domains' => $this->extractDomains($payload),
            'base_ref' => $this->extractRefs($payload)['base_ref'],
            'head_ref' => $this->extractRefs($payload)['head_ref'],
            'changed_packages' => (int) ($planSummary['changed_packages'] ?? 0),
            'deleted_cleanup_candidates' => (int) ($planSummary['deleted_cleanup_candidates'] ?? 0),
            'seed_candidates' => (int) ($planSummary['seed_candidates'] ?? 0),
            'refresh_candidates' => (int) ($planSummary['refresh_candidates'] ?? 0),
            'blocked_count' => (int) ($planSummary['blocked'] ?? 0),
            'warning_count' => (int) ($planSummary['warnings'] ?? 0),
            'phase_counts' => [
                'cleanup_deleted' => $this->phaseCounts($execution['cleanup_deleted']),
                'upsert_present' => $this->phaseCounts($execution['upsert_present']),
            ],
            'stopped_phase' => $this->stringValue(data_get($canonical, 'error.phase', data_get($payload, 'error.phase'))),
            'stopped_package_key' => $this->stringValue(data_get($canonical, 'error.package', data_get($payload, 'error.package'))),
            'bootstrap_domains' => $bootstrapDomains,
            'sync_state_advanced_domains' => $this->advancedDomainsFromPayload($payload),
            'lock_status' => $this->stringValue(data_get($payload, 'lock.status')),
            'lock_reason' => $this->stringValue(data_get($payload, 'error.reason')),
            'status' => $status,
        ];
    }

    /**
     * @param  array<string, mixed>  $phase
     * @return array<string, int|string|null>
     */
    private function phaseCounts(array $phase): array
    {
        return [
            'started' => (int) ($phase['started'] ?? 0),
            'completed' => (int) ($phase['completed'] ?? 0),
            'succeeded' => (int) ($phase['succeeded'] ?? 0),
            'failed' => (int) ($phase['failed'] ?? 0),
            'stopped_on' => $phase['stopped_on'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function canonicalApplyPayload(array $payload): array
    {
        if (is_array(data_get($payload, 'replay.result'))) {
            return (array) data_get($payload, 'replay.result');
        }

        if (is_array(data_get($payload, 'content_apply.result'))) {
            return (array) data_get($payload, 'content_apply.result');
        }

        if (is_array(data_get($payload, 'apply.changed_content_result'))) {
            return (array) data_get($payload, 'apply.changed_content_result');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function advancedDomainsFromPayload(array $payload): array
    {
        $advanced = (array) data_get($payload, 'content_sync.advanced_domains', []);

        if ($advanced !== []) {
            return array_values(array_filter(array_map('strval', $advanced)));
        }

        $before = (array) data_get($payload, 'domains_before', []);
        $after = (array) data_get($payload, 'domains_after', []);
        $result = [];

        foreach ($after as $domain => $afterState) {
            $beforeRef = strtolower(trim((string) ($before[$domain]['sync_state_ref'] ?? '')));
            $afterRef = strtolower(trim((string) ($afterState['sync_state_ref'] ?? '')));

            if ($afterRef !== '' && $afterRef !== $beforeRef) {
                $result[] = (string) $domain;
            }
        }

        return $result;
    }

    /**
     * @param  Throwable|array<string, mixed>|string|null  $error
     * @param  array<string, mixed>  $payload
     */
    private function errorExcerpt(Throwable|array|string|null $error, array $payload): ?string
    {
        if ($error instanceof Throwable) {
            return Str::limit($error->getMessage(), 1000);
        }

        if (is_string($error) && trim($error) !== '') {
            return Str::limit(trim($error), 1000);
        }

        if (is_array($error)) {
            $message = $this->stringValue($error['message'] ?? null)
                ?? $this->stringValue($error['reason'] ?? null)
                ?? json_encode($error, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            return $message !== null ? Str::limit($message, 1000) : null;
        }

        $payloadError = data_get($payload, 'error');

        if (is_array($payloadError)) {
            $message = $this->stringValue($payloadError['message'] ?? null)
                ?? $this->stringValue($payloadError['reason'] ?? null)
                ?? json_encode($payloadError, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            return $message !== null ? Str::limit($message, 1000) : null;
        }

        return null;
    }

    /**
     * @param  string|array<string, mixed>|list<string>|null  $value
     * @return list<string>
     */
    private function domainsValue(mixed $value): array
    {
        if (is_array($value)) {
            $tokens = array_is_list($value)
                ? $value
                : array_keys($value);
        } else {
            $tokens = explode(',', (string) ($value ?? ''));
        }

        return collect($tokens)
            ->map(static fn ($token): string => str_replace('_', '-', strtolower(trim((string) $token))))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  mixed  $value
     * @return array<string, string|null>|null
     */
    private function baseRefsByDomainValue(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $result = [];

        foreach ($value as $domain => $ref) {
            if (! is_string($domain) || trim($domain) === '') {
                continue;
            }

            if (is_array($ref) && array_key_exists('effective_base_ref', $ref)) {
                $ref = $ref['effective_base_ref'];
            }

            $result[str_replace('_', '-', strtolower(trim($domain)))] = $this->stringValue($ref);
        }

        return $result !== [] ? $result : null;
    }

    private function stringValue(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeArtifactPath(mixed $path): ?string
    {
        $normalized = $this->stringValue($path);

        if ($normalized === null) {
            return null;
        }

        $storagePrefix = str_replace('\\', '/', storage_path('app'));
        $candidate = str_replace('\\', '/', $normalized);

        if (str_starts_with($candidate, $storagePrefix . '/')) {
            return ltrim(substr($candidate, strlen($storagePrefix)), '/');
        }

        return ltrim($candidate, '/');
    }

    private function absoluteArtifactPath(?string $relativePath): ?string
    {
        $normalized = $this->normalizeArtifactPath($relativePath);

        return $normalized !== null ? storage_path('app/' . $normalized) : null;
    }
}
