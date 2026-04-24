<?php

namespace App\Services\ContentDeployment;

use App\Models\ContentSyncState;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use RuntimeException;

class ContentSyncStateService
{
    /**
     * @return list<string>
     */
    public function supportedDomains(): array
    {
        return ['v3', 'page-v3'];
    }

    public function get(string $domain): ?ContentSyncState
    {
        $normalizedDomain = $this->normalizeDomain($domain);

        return ContentSyncState::query()
            ->where('domain', $normalizedDomain)
            ->first();
    }

    /**
     * @param  list<string>  $domains
     * @return array<string, ContentSyncState|null>
     */
    public function getMany(array $domains): array
    {
        $normalizedDomains = $this->normalizeDomains($domains);
        $states = ContentSyncState::query()
            ->whereIn('domain', $normalizedDomains)
            ->get()
            ->keyBy('domain');

        $result = [];

        foreach ($normalizedDomains as $domain) {
            $result[$domain] = $states->get($domain);
        }

        return $result;
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, string|null>  $fallbackRefs
     * @return array<string, string|null>
     */
    public function resolveEffectiveBaseRefs(array $domains, array $fallbackRefs): array
    {
        $states = $this->getMany($domains);
        $effectiveRefs = [];

        foreach ($states as $domain => $state) {
            $syncRef = trim((string) ($state?->last_successful_ref ?? ''));
            $fallbackRef = $this->normalizeRef($fallbackRefs[$domain] ?? null);

            $effectiveRefs[$domain] = $syncRef !== '' ? $syncRef : $fallbackRef;
        }

        return $effectiveRefs;
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, string|null>  $fallbackRefs
     * @return array<string, array<string, mixed>>
     */
    public function describe(array $domains, array $fallbackRefs, ?string $headRef = null): array
    {
        $normalizedDomains = $this->normalizeDomains($domains);
        $states = $this->getMany($normalizedDomains);
        $effectiveRefs = $this->resolveEffectiveBaseRefs($normalizedDomains, $fallbackRefs);
        $normalizedHeadRef = $this->normalizeRef($headRef);
        $descriptions = [];

        foreach ($normalizedDomains as $domain) {
            $state = $states[$domain];
            $syncStateRef = $this->normalizeRef($state?->last_successful_ref);
            $fallbackBaseRef = $this->normalizeRef($fallbackRefs[$domain] ?? null);
            $effectiveBaseRef = $this->normalizeRef($effectiveRefs[$domain] ?? null);
            $syncStateUninitialized = $syncStateRef === null;
            $driftFromCodeRef = ! $syncStateUninitialized
                && $fallbackBaseRef !== null
                && strtolower($syncStateRef) !== strtolower($fallbackBaseRef);
            $lastAttemptStatus = $this->normalizeAttemptStatus($state?->last_attempted_status);

            $descriptions[$domain] = [
                'domain' => $domain,
                'sync_state_ref' => $syncStateRef,
                'fallback_base_ref' => $fallbackBaseRef,
                'effective_base_ref' => $effectiveBaseRef,
                'fallback_used' => $syncStateUninitialized || $effectiveBaseRef === $fallbackBaseRef,
                'drift_from_code_ref' => $driftFromCodeRef,
                'sync_state_uninitialized' => $syncStateUninitialized,
                'status' => $this->statusFor($syncStateUninitialized, $driftFromCodeRef, $lastAttemptStatus),
                'last_successful_ref' => $syncStateRef,
                'last_successful_applied_at' => $this->isoTimestamp($state?->last_successful_applied_at),
                'last_attempted_base_ref' => $this->normalizeRef($state?->last_attempted_base_ref),
                'last_attempted_head_ref' => $this->normalizeRef($state?->last_attempted_head_ref),
                'last_attempted_status' => $lastAttemptStatus,
                'last_attempted_at' => $this->isoTimestamp($state?->last_attempted_at),
                'last_attempt_meta' => is_array($state?->last_attempt_meta) ? $state->last_attempt_meta : null,
                'target_head_ref' => $normalizedHeadRef,
                'would_advance_to_head' => $normalizedHeadRef !== null
                    && $effectiveBaseRef !== null
                    && strtolower($effectiveBaseRef) !== strtolower($normalizedHeadRef),
            ];
        }

        return $descriptions;
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, string|null>  $baseRefs
     * @param  array<string, mixed>  $meta
     */
    public function recordPreview(array $domains, array $baseRefs, string $headRef, array $meta = []): void
    {
        $this->recordAttempt($domains, $baseRefs, $headRef, 'preview_only', $meta, false);
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, string|null>  $baseRefs
     * @param  array<string, mixed>  $meta
     */
    public function recordDryRun(array $domains, array $baseRefs, string $headRef, array $meta = []): void
    {
        $this->recordAttempt($domains, $baseRefs, $headRef, 'dry_run', $meta, false);
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, string|null>  $baseRefs
     * @param  array<string, mixed>  $meta
     */
    public function recordSuccess(array $domains, array $baseRefs, string $headRef, array $meta = []): void
    {
        $this->recordAttempt($domains, $baseRefs, $headRef, 'success', $meta, true);
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, string|null>  $baseRefs
     * @param  array<string, mixed>  $meta
     */
    public function recordFailure(array $domains, array $baseRefs, string $headRef, array $meta = []): void
    {
        $this->recordAttempt($domains, $baseRefs, $headRef, 'failed', $meta, false);
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, string|null>  $baseRefs
     * @param  array<string, mixed>  $meta
     */
    public function recordSkipped(array $domains, array $baseRefs, string $headRef, array $meta = []): void
    {
        $this->recordAttempt($domains, $baseRefs, $headRef, 'skipped', $meta, false);
    }

    private function normalizeDomain(string $domain): string
    {
        $normalizedDomain = str_replace('_', '-', strtolower(trim($domain)));

        if (! in_array($normalizedDomain, $this->supportedDomains(), true)) {
            throw new RuntimeException(sprintf(
                'Unsupported content sync domain [%s].',
                $domain
            ));
        }

        return $normalizedDomain;
    }

    /**
     * @param  list<string>  $domains
     * @return list<string>
     */
    private function normalizeDomains(array $domains): array
    {
        $normalizedDomains = array_values(array_unique(array_map(
            fn (string $domain): string => $this->normalizeDomain($domain),
            $domains
        )));

        if ($normalizedDomains === []) {
            throw new RuntimeException('Content sync state operations require at least one domain.');
        }

        return $normalizedDomains;
    }

    private function normalizeRef(?string $ref): ?string
    {
        $normalizedRef = trim((string) ($ref ?? ''));

        return $normalizedRef !== '' ? $normalizedRef : null;
    }

    private function normalizeAttemptStatus(?string $status): ?string
    {
        $normalizedStatus = strtolower(trim((string) ($status ?? '')));

        return $normalizedStatus !== '' ? $normalizedStatus : null;
    }

    private function isoTimestamp(Carbon|\DateTimeInterface|string|null $value): ?string
    {
        if ($value instanceof Carbon) {
            return $value->toIso8601String();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance(\DateTimeImmutable::createFromInterface($value))->toIso8601String();
        }

        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : null;
    }

    private function statusFor(bool $uninitialized, bool $drifted, ?string $lastAttemptStatus): string
    {
        if ($uninitialized) {
            return 'uninitialized';
        }

        if ($lastAttemptStatus === 'failed') {
            return 'failed_last_apply';
        }

        if ($drifted) {
            return 'drifted';
        }

        return 'synced';
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, string|null>  $baseRefs
     * @param  array<string, mixed>  $meta
     */
    private function recordAttempt(array $domains, array $baseRefs, string $headRef, string $status, array $meta, bool $advanceSuccessfulRef): void
    {
        $normalizedDomains = $this->normalizeDomains($domains);
        $normalizedHeadRef = $this->normalizeRef($headRef);
        $attemptedAt = now();

        foreach ($normalizedDomains as $domain) {
            $baseRef = $this->normalizeRef($baseRefs[$domain] ?? null);
            $state = ContentSyncState::query()->firstOrNew(['domain' => $domain]);
            $state->last_attempted_base_ref = $baseRef;
            $state->last_attempted_head_ref = $normalizedHeadRef;
            $state->last_attempted_status = $status;
            $state->last_attempted_at = $attemptedAt;
            $state->last_attempt_meta = $this->metaForDomain($domain, $meta);

            if ($advanceSuccessfulRef && $normalizedHeadRef !== null) {
                $state->last_successful_ref = $normalizedHeadRef;
                $state->last_successful_applied_at = $attemptedAt;
            }

            $state->save();
        }
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>|null
     */
    private function metaForDomain(string $domain, array $meta): ?array
    {
        $domainSpecificMeta = Arr::get($meta, 'domains.' . $domain);

        if (is_array($domainSpecificMeta)) {
            return $domainSpecificMeta;
        }

        if (is_array($meta[$domain] ?? null)) {
            return $meta[$domain];
        }

        return $meta !== [] ? $meta : null;
    }
}
