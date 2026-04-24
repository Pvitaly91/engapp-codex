<?php

namespace App\Services\ContentDeployment;

use App\Models\ContentOperationLock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ContentOperationLockService
{
    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function acquire(array $context, bool $allowStaleTakeover = false): array
    {
        if (! $this->enabled()) {
            return $this->disabledLease($allowStaleTakeover);
        }

        $key = $this->lockKey();
        $now = now();

        return DB::transaction(function () use ($context, $allowStaleTakeover, $key, $now): array {
            $lock = ContentOperationLock::query()
                ->where('lock_key', $key)
                ->first();

            if ($lock === null) {
                $lock = $this->createLock($key, $context, $now);

                return $this->acquiredLease($lock, 'active', false, $allowStaleTakeover);
            }

            if ($this->isStale($lock)) {
                $lock->forceFill(['status' => 'stale'])->save();

                if (! $allowStaleTakeover) {
                    return $this->blockedLease($lock, 'stale_lock_takeover_required', $allowStaleTakeover);
                }

                $lock = $this->replaceStaleLock($lock, $context, $now);

                return $this->acquiredLease($lock, 'taken_over', true, true);
            }

            return $this->blockedLease($lock, 'active_lock_present', $allowStaleTakeover);
        });
    }

    public function heartbeat(?string $ownerToken): void
    {
        if (! $this->enabled() || trim((string) $ownerToken) === '') {
            return;
        }

        $now = now();

        ContentOperationLock::query()
            ->where('lock_key', $this->lockKey())
            ->where('owner_token', $ownerToken)
            ->update([
                'heartbeat_at' => $now,
                'expires_at' => $now->copy()->addSeconds($this->ttlSeconds()),
                'status' => 'active',
            ]);
    }

    public function attachRun(?string $ownerToken, mixed $runId): void
    {
        if (! $this->enabled() || trim((string) $ownerToken) === '' || $runId === null) {
            return;
        }

        ContentOperationLock::query()
            ->where('lock_key', $this->lockKey())
            ->where('owner_token', $ownerToken)
            ->update([
                'content_operation_run_id' => $runId,
            ]);
    }

    public function release(?string $ownerToken): void
    {
        if (! $this->enabled() || trim((string) $ownerToken) === '') {
            return;
        }

        ContentOperationLock::query()
            ->where('lock_key', $this->lockKey())
            ->where('owner_token', $ownerToken)
            ->delete();
    }

    public function current(): ?ContentOperationLock
    {
        if (! $this->enabled()) {
            return null;
        }

        $lock = ContentOperationLock::query()
            ->where('lock_key', $this->lockKey())
            ->first();

        if ($lock !== null && $this->isStale($lock) && $lock->status !== 'stale') {
            $lock->forceFill(['status' => 'stale'])->save();
            $lock->refresh();
        }

        return $lock;
    }

    /**
     * @return array<string, mixed>
     */
    public function status(): array
    {
        if (! $this->enabled()) {
            return [
                'exists' => false,
                'status' => 'disabled',
                'lock' => null,
                'takeover' => [
                    'available' => false,
                    'allowed_by_default' => false,
                ],
            ];
        }

        $lock = $this->current();

        if ($lock === null) {
            return [
                'exists' => false,
                'status' => 'free',
                'lock' => null,
                'takeover' => [
                    'available' => false,
                    'allowed_by_default' => (bool) config('content-deployment.lock.allow_stale_takeover_default', false),
                ],
            ];
        }

        $stale = $this->isStale($lock);

        return [
            'exists' => true,
            'status' => $stale ? 'stale' : 'active',
            'lock' => $this->snapshot($lock),
            'takeover' => [
                'available' => $stale,
                'allowed_by_default' => (bool) config('content-deployment.lock.allow_stale_takeover_default', false),
            ],
        ];
    }

    public function isStale(ContentOperationLock $lock): bool
    {
        $now = now();
        $expiresAt = $lock->expires_at instanceof Carbon ? $lock->expires_at : null;

        if ($expiresAt !== null && $expiresAt->lessThanOrEqualTo($now)) {
            return true;
        }

        $heartbeatAt = $lock->heartbeat_at instanceof Carbon ? $lock->heartbeat_at : null;

        return $heartbeatAt !== null
            && $heartbeatAt->copy()->addSeconds($this->staleAfterSeconds())->lessThanOrEqualTo($now);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function takeover(ContentOperationLock $lock, array $context): array
    {
        if (! $this->isStale($lock)) {
            return $this->blockedLease($lock, 'active_lock_present', true);
        }

        $lock = $this->replaceStaleLock($lock, $context, now());

        return $this->acquiredLease($lock, 'taken_over', true, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(?ContentOperationLock $lock = null): array
    {
        $lock ??= $this->current();

        if ($lock === null) {
            return [
                'exists' => false,
                'status' => 'free',
            ];
        }

        $now = now();
        $heartbeatAt = $lock->heartbeat_at instanceof Carbon ? $lock->heartbeat_at : null;
        $acquiredAt = $lock->acquired_at instanceof Carbon ? $lock->acquired_at : null;
        $expiresAt = $lock->expires_at instanceof Carbon ? $lock->expires_at : null;

        return [
            'exists' => true,
            'id' => $lock->id,
            'lock_key' => $lock->lock_key,
            'owner_token' => $lock->owner_token,
            'operation_kind' => $lock->operation_kind,
            'trigger_source' => $lock->trigger_source,
            'domains' => is_array($lock->domains) ? $lock->domains : [],
            'content_operation_run_id' => $lock->content_operation_run_id,
            'operator_user_id' => $lock->operator_user_id,
            'acquired_at' => $acquiredAt?->toIso8601String(),
            'heartbeat_at' => $heartbeatAt?->toIso8601String(),
            'expires_at' => $expiresAt?->toIso8601String(),
            'age_seconds' => $acquiredAt?->diffInSeconds($now),
            'heartbeat_age_seconds' => $heartbeatAt?->diffInSeconds($now),
            'status' => $this->isStale($lock) ? 'stale' : (string) $lock->status,
            'meta' => is_array($lock->meta) ? $lock->meta : [],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function createLock(string $key, array $context, Carbon $now): ContentOperationLock
    {
        return ContentOperationLock::query()->create($this->lockAttributes($key, $context, $now));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function replaceStaleLock(ContentOperationLock $lock, array $context, Carbon $now): ContentOperationLock
    {
        $lock->forceFill($this->lockAttributes((string) $lock->lock_key, $context, $now))->save();
        $lock->refresh();

        return $lock;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function lockAttributes(string $key, array $context, Carbon $now): array
    {
        return [
            'lock_key' => $key,
            'owner_token' => (string) Str::uuid(),
            'operation_kind' => (string) ($context['operation_kind'] ?? 'unknown'),
            'trigger_source' => (string) ($context['trigger_source'] ?? 'unknown'),
            'domains' => $this->normalizeDomains($context['domains'] ?? null),
            'content_operation_run_id' => $context['content_operation_run_id'] ?? null,
            'operator_user_id' => $context['operator_user_id'] ?? null,
            'acquired_at' => $now,
            'heartbeat_at' => $now,
            'expires_at' => $now->copy()->addSeconds($this->ttlSeconds()),
            'status' => 'active',
            'meta' => is_array($context['meta'] ?? null) ? $context['meta'] : [],
        ];
    }

    /**
     * @return list<string>|null
     */
    private function normalizeDomains(mixed $domains): ?array
    {
        if (is_string($domains)) {
            $domains = preg_split('/\s*,\s*/', $domains, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        if (! is_array($domains)) {
            return null;
        }

        $normalized = array_values(array_unique(array_filter(array_map(
            static fn (mixed $domain): string => trim((string) $domain),
            $domains
        ))));

        return $normalized !== [] ? $normalized : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function acquiredLease(ContentOperationLock $lock, string $status, bool $takeoverPerformed, bool $takeoverRequested): array
    {
        return [
            'acquired' => true,
            'status' => $status,
            'owner_token' => $lock->owner_token,
            'lock' => $this->snapshot($lock),
            'warnings' => $takeoverPerformed ? ['taken_over_stale_lock'] : [],
            'takeover' => [
                'requested' => $takeoverRequested,
                'performed' => $takeoverPerformed,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blockedLease(ContentOperationLock $lock, string $reason, bool $takeoverRequested): array
    {
        return [
            'acquired' => false,
            'status' => $this->isStale($lock) ? 'stale' : 'active',
            'owner_token' => null,
            'lock' => $this->snapshot($lock),
            'warnings' => [$reason],
            'takeover' => [
                'requested' => $takeoverRequested,
                'performed' => false,
            ],
            'error' => [
                'stage' => 'content_operation_lock',
                'reason' => $reason,
                'message' => $reason === 'active_lock_present'
                    ? 'Another execution-grade content operation is already running.'
                    : 'A stale content operation lock is present; rerun with explicit stale-lock takeover if safe.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function disabledLease(bool $takeoverRequested): array
    {
        return [
            'acquired' => true,
            'status' => 'disabled',
            'owner_token' => null,
            'lock' => [
                'exists' => false,
                'status' => 'disabled',
            ],
            'warnings' => ['content_operation_lock_disabled'],
            'takeover' => [
                'requested' => $takeoverRequested,
                'performed' => false,
            ],
            'error' => null,
        ];
    }

    private function enabled(): bool
    {
        return (bool) config('content-deployment.lock.enabled', true)
            && Schema::hasTable('content_operation_locks');
    }

    private function lockKey(): string
    {
        return (string) config('content-deployment.lock.key', 'global_content_operations');
    }

    private function ttlSeconds(): int
    {
        return max(60, (int) config('content-deployment.lock.ttl_seconds', 3600));
    }

    private function staleAfterSeconds(): int
    {
        return max(30, (int) config('content-deployment.lock.stale_after_seconds', 900));
    }
}
