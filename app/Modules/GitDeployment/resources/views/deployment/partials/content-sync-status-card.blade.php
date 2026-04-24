@php
  $previewSyncDomains = (array) data_get($contentPreview ?? null, 'content_sync.domains', []);
  $applyBeforeDomains = (array) data_get($contentApply ?? null, 'content_sync.before.domains', []);
  $applyAfterDomains = (array) data_get($contentApply ?? null, 'content_sync.after.domains', []);
  $domains = array_values(array_unique(array_merge(array_keys($previewSyncDomains), array_keys($applyBeforeDomains), array_keys($applyAfterDomains))));
  $advancedDomains = (array) data_get($contentApply ?? null, 'content_sync.advanced_domains', []);
  $isDryRunApply = (bool) data_get($contentApply ?? null, 'content_apply.dry_run', false);
@endphp

@if($domains !== [])
  <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
    <div class="space-y-6 p-6">
      <div class="space-y-2">
        <h2 class="text-2xl font-semibold">Content Sync State</h2>
        <p class="text-sm text-muted-foreground">
          Canonical per-domain synced refs used for changed-content deploy planning and post-deploy apply.
        </p>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        @foreach($domains as $domain)
          @php
            $before = (array) ($applyBeforeDomains[$domain] ?? $previewSyncDomains[$domain] ?? []);
            $after = (array) ($applyAfterDomains[$domain] ?? []);
            $status = (string) (($after['status'] ?? null) ?: ($before['status'] ?? 'uninitialized'));
            $statusLabel = match ($status) {
              'synced' => 'Synced',
              'drifted' => 'Drifted',
              'failed_last_apply' => 'Last apply failed',
              default => 'Uninitialized',
            };
            $statusClass = match ($status) {
              'synced' => 'bg-success/15 text-success',
              'failed_last_apply' => 'bg-destructive/15 text-destructive-foreground',
              default => 'bg-warning/15 text-warning',
            };
            $currentCodeRef = $before['fallback_base_ref'] ?? null;
            $targetRef = $before['target_head_ref'] ?? ($after['target_head_ref'] ?? null);
            $syncRef = $after !== [] ? ($after['sync_state_ref'] ?? null) : ($before['sync_state_ref'] ?? null);
            $outcome = $after !== []
              ? (in_array($domain, $advancedDomains, true) ? 'Sync ref advanced during apply.' : 'Sync ref did not advance during apply.')
              : ($isDryRunApply
                ? (($before['would_advance_to_head'] ?? false) ? 'Dry-run indicates sync ref would advance on successful live apply.' : 'Dry-run indicates sync ref would stay unchanged.')
                : (($before['sync_state_uninitialized'] ?? false) ? 'Sync state is not initialized yet; fallback to current deployed code ref is in use.' : 'Using persisted sync ref for deploy planning.'));
          @endphp
          <div class="rounded-2xl border border-border/60 bg-muted/20 p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-xs uppercase tracking-wide text-muted-foreground">{{ strtoupper($domain) }}</div>
                <div class="mt-2 text-sm text-foreground break-all">
                  current code ref=<code>{{ $currentCodeRef ?: '—' }}</code>
                </div>
              </div>
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                {{ $statusLabel }}
              </span>
            </div>

            <div class="mt-4 space-y-2 text-sm text-muted-foreground">
              <div>content sync ref=<code>{{ $syncRef ?: '—' }}</code></div>
              <div>target ref=<code>{{ $targetRef ?: '—' }}</code></div>
              <div>effective base=<code>{{ ($before['effective_base_ref'] ?? null) ?: '—' }}</code></div>
              <div>fallback used={{ ($before['fallback_used'] ?? false) ? 'true' : 'false' }}, drift={{ ($before['drift_from_code_ref'] ?? false) ? 'true' : 'false' }}</div>
              <div>last attempt={{ ($after['last_attempted_status'] ?? null) ?: ($before['last_attempted_status'] ?? '—') }} at {{ ($after['last_attempted_at'] ?? null) ?: ($before['last_attempted_at'] ?? '—') }}</div>
            </div>

            <div class="mt-4 rounded-2xl border border-border/50 bg-background/70 p-3 text-xs text-muted-foreground">
              {{ $outcome }}
            </div>
          </div>
        @endforeach
      </div>

      <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
        CLI hints:
        <code>php artisan content:sync-status --json</code>
        @if(($contentPreview['deployment']['base_ref'] ?? null) && ($contentPreview['deployment']['head_ref'] ?? null))
          <span class="block mt-2">
            <code>php artisan content:apply-changed --base={{ data_get($contentPreview, 'deployment.base_ref') }} --head={{ data_get($contentPreview, 'deployment.head_ref') }} --dry-run</code>
          </span>
        @endif
      </div>
    </div>
  </section>
@endif
