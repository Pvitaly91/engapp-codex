@php
  $statusPayload = is_array($contentOperationLockStatus ?? null) ? $contentOperationLockStatus : ['status' => 'free'];
  $status = (string) data_get($statusPayload, 'status', 'free');
  $lock = (array) data_get($statusPayload, 'lock', []);
  $badgeClass = match ($status) {
    'active' => 'bg-warning/15 text-warning',
    'stale' => 'bg-red-500/15 text-red-600',
    'disabled', 'unavailable' => 'bg-muted text-muted-foreground',
    default => 'bg-emerald-500/15 text-emerald-700',
  };
@endphp

<section class="rounded-3xl border border-border/70 bg-card p-5 shadow-soft">
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <h2 class="text-lg font-semibold text-foreground">Active Content Operation Lock</h2>
      <p class="mt-1 text-sm text-muted-foreground">
        Execution-grade content apply, sync repair, replay, and deploy/restore actions with post-deploy content apply share one global mutex.
      </p>
    </div>
    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClass }}">{{ $status }}</span>
  </div>

  @if(in_array($status, ['free', 'disabled', 'unavailable'], true))
    <p class="mt-4 text-sm text-muted-foreground">
      {{ $status === 'free' ? 'No execution-grade content operation currently holds the lock.' : 'Lock status is not active.' }}
    </p>
    @if(!empty($statusPayload['error']))
      <p class="mt-2 text-sm text-red-600">{{ $statusPayload['error'] }}</p>
    @endif
  @else
    <div class="mt-4 grid gap-3 text-sm text-muted-foreground md:grid-cols-2">
      <div>operation=<code>{{ $lock['operation_kind'] ?? 'unknown' }}</code></div>
      <div>trigger=<code>{{ $lock['trigger_source'] ?? 'unknown' }}</code></div>
      <div>domains=<code>{{ implode(',', (array) ($lock['domains'] ?? [])) ?: '—' }}</code></div>
      <div>run_id=<code>{{ ($lock['content_operation_run_id'] ?? null) ?: '—' }}</code></div>
      <div>acquired_at=<code>{{ ($lock['acquired_at'] ?? null) ?: '—' }}</code></div>
      <div>heartbeat_at=<code>{{ ($lock['heartbeat_at'] ?? null) ?: '—' }}</code></div>
      <div>expires_at=<code>{{ ($lock['expires_at'] ?? null) ?: '—' }}</code></div>
      <div>age_seconds=<code>{{ ($lock['age_seconds'] ?? null) ?: '0' }}</code></div>
    </div>

    @if($status === 'stale')
      <div class="mt-4 rounded-2xl border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-700">
        This lock is stale. Fresh locks cannot be stolen; stale takeover requires an explicit CLI/admin option such as
        <code>--takeover-stale-lock</code>.
      </div>
    @endif
  @endif

  <div class="mt-4 text-xs text-muted-foreground">
    Deploy/restore reserves the lock before code update when changed-content apply is enabled. CLI: <code>php artisan content:lock-status --json</code>
  </div>
</section>
