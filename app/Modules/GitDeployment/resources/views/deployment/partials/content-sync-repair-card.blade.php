@php
  $syncPreview = $contentSyncPreview ?? null;
  $syncApply = $contentSyncApply ?? null;
  $syncDomains = (array) data_get($syncPreview, 'domains', data_get($syncApply, 'domains_after', data_get($syncApply, 'domains_before', [])));
  $syncSummary = (array) data_get($syncPreview, 'summary', data_get($syncApply, 'plan.summary', []));
  $bootstrapRequiredDomains = (array) data_get($syncPreview, 'bootstrap.required_domains', data_get($syncApply, 'plan.bootstrap.required_domains', []));
  $currentDeployedRef = data_get($syncPreview, 'deployment_refs.current_deployed_ref', data_get($syncApply, 'plan.deployment_refs.current_deployed_ref'));
  $syncError = is_array(data_get($syncPreview, 'error'))
    ? data_get($syncPreview, 'error')
    : (is_array(data_get($syncApply, 'error')) ? data_get($syncApply, 'error') : null);
@endphp

<section class="rounded-3xl border border-border/70 bg-card shadow-soft">
  <div class="space-y-6 p-6">
    <div class="space-y-2">
      <h2 class="text-2xl font-semibold">Content Sync Drift / Repair</h2>
      <p class="text-sm text-muted-foreground">
        Preview or repair drift between the current deployed code ref and the persisted per-domain content sync refs without running another deploy.
      </p>
    </div>

    <div class="grid gap-3 text-sm md:grid-cols-4">
      <div class="rounded-2xl border border-border/60 bg-muted/30 p-4">
        <div class="text-xs uppercase tracking-wide text-muted-foreground">Current Deployed Ref</div>
        <div class="mt-2 break-all font-semibold text-foreground">{{ $currentDeployedRef ?: '—' }}</div>
      </div>
      <div class="rounded-2xl border border-border/60 bg-muted/30 p-4">
        <div class="text-xs uppercase tracking-wide text-muted-foreground">Synced Domains</div>
        <div class="mt-2 font-semibold text-foreground">{{ (int) ($syncSummary['synced_domains'] ?? 0) }}</div>
      </div>
      <div class="rounded-2xl border border-border/60 bg-muted/30 p-4">
        <div class="text-xs uppercase tracking-wide text-muted-foreground">Drifted Domains</div>
        <div class="mt-2 font-semibold text-foreground">{{ (int) ($syncSummary['drifted_domains'] ?? 0) }}</div>
      </div>
      <div class="rounded-2xl border border-border/60 bg-muted/30 p-4">
        <div class="text-xs uppercase tracking-wide text-muted-foreground">Bootstrap Required</div>
        <div class="mt-2 font-semibold text-foreground">{{ count($bootstrapRequiredDomains) }}</div>
      </div>
    </div>

    @if($syncError)
      <div class="rounded-2xl border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive-foreground">
        <div class="font-semibold">Sync repair issue</div>
        <div class="mt-2">{{ data_get($syncError, 'message', 'Unknown content sync repair error.') }}</div>
      </div>
    @endif

    @if($syncDomains !== [])
      <div class="grid gap-4 md:grid-cols-2">
        @foreach($syncDomains as $domain => $domainState)
          @php
            $domainState = (array) $domainState;
            $status = (string) ($domainState['status'] ?? 'uninitialized');
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
          @endphp
          <div class="rounded-2xl border border-border/60 bg-muted/20 p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-xs uppercase tracking-wide text-muted-foreground">{{ strtoupper($domain) }}</div>
                <div class="mt-2 text-sm text-foreground break-all">content sync ref=<code>{{ ($domainState['sync_state_ref'] ?? null) ?: '—' }}</code></div>
              </div>
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
            <div class="mt-4 space-y-2 text-sm text-muted-foreground">
              <div>current deployed ref=<code>{{ ($domainState['current_deployed_ref'] ?? null) ?: '—' }}</code></div>
              <div>effective base=<code>{{ ($domainState['effective_base_ref'] ?? null) ?: '—' }}</code></div>
              <div>bootstrap required={{ ($domainState['bootstrap_required'] ?? false) ? 'true' : 'false' }}</div>
              <div>last attempt={{ ($domainState['last_attempted_status'] ?? null) ?: '—' }} at {{ ($domainState['last_attempted_at'] ?? null) ?: '—' }}</div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
        Run a sync preview to inspect per-domain drift and bootstrap-required states.
      </div>
    @endif

    @if($syncPreview)
      <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-3">
          <h3 class="text-lg font-semibold">Deleted Packages Cleanup Phase</h3>
          @php $cleanupPackages = (array) data_get($syncPreview, 'content_plan.phases.cleanup_deleted', []); @endphp
          @if($cleanupPackages === [])
            <p class="text-sm text-muted-foreground">No deleted content cleanup is required for the selected domains.</p>
          @else
            <ul class="space-y-2 text-sm">
              @foreach($cleanupPackages as $package)
                <li class="rounded-2xl border border-border/60 bg-background/70 p-3">
                  <div class="font-medium text-foreground">[{{ data_get($package, 'domain', 'unknown') }}] {{ data_get($package, 'relative_path', data_get($package, 'package_key', '—')) }}</div>
                  <div class="mt-1 text-xs text-muted-foreground">action={{ data_get($package, 'recommended_action', 'skip') }}</div>
                </li>
              @endforeach
            </ul>
          @endif
        </div>
        <div class="space-y-3">
          <h3 class="text-lg font-semibold">Current Packages Upsert Phase</h3>
          @php $upsertPackages = (array) data_get($syncPreview, 'content_plan.phases.upsert_present', []); @endphp
          @if($upsertPackages === [])
            <p class="text-sm text-muted-foreground">No current packages require seed or refresh for sync repair.</p>
          @else
            <ul class="space-y-2 text-sm">
              @foreach($upsertPackages as $package)
                <li class="rounded-2xl border border-border/60 bg-background/70 p-3">
                  <div class="font-medium text-foreground">[{{ data_get($package, 'domain', 'unknown') }}] {{ data_get($package, 'relative_path', data_get($package, 'package_key', '—')) }}</div>
                  <div class="mt-1 text-xs text-muted-foreground">action={{ data_get($package, 'recommended_action', 'skip') }}, release={{ data_get($package, 'release_check.status', 'skipped') }}</div>
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    @endif

    @if($syncApply)
      <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
        <div class="font-semibold text-foreground">Last repair result</div>
        <div class="mt-2">
          status=<code>{{ data_get($syncApply, 'status', 'ready') }}</code>,
          dry_run=<code>{{ data_get($syncApply, 'apply.dry_run', false) ? 'true' : 'false' }}</code>,
          report=<code>{{ data_get($syncApply, 'artifacts.report_path', '—') }}</code>
        </div>
      </div>
    @endif

    <div class="rounded-2xl border border-border/60 bg-muted/20 p-4">
      <div class="text-sm font-medium text-foreground">Repair Actions</div>
      <div class="mt-3 flex flex-wrap items-center gap-3">
        <form method="GET" action="{{ $syncPreviewRoute }}" class="inline">
          <input type="hidden" name="content_sync_with_release_check" value="{{ config('git-deployment.content_apply.with_release_check', true) ? '1' : '0' }}" />
          <input type="hidden" name="content_sync_check_profile" value="{{ config('git-deployment.content_apply.check_profile', 'release') }}" />
          <input type="hidden" name="content_sync_strict" value="{{ config('git-deployment.content_apply.strict', true) ? '1' : '0' }}" />
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl border border-border/70 bg-background px-5 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-muted/40">
            Preview Sync Drift
          </button>
        </form>

        <form method="POST" action="{{ $syncApplyRoute }}" class="inline-flex flex-wrap items-center gap-3">
          @csrf
          <input type="hidden" name="content_sync_with_release_check" value="{{ config('git-deployment.content_apply.with_release_check', true) ? '1' : '0' }}" />
          <input type="hidden" name="content_sync_skip_release_check" value="{{ config('git-deployment.content_apply.skip_release_check', false) ? '1' : '0' }}" />
          <input type="hidden" name="content_sync_check_profile" value="{{ config('git-deployment.content_apply.check_profile', 'release') }}" />
          <input type="hidden" name="content_sync_strict" value="{{ config('git-deployment.content_apply.strict', true) ? '1' : '0' }}" />
          <label class="inline-flex items-center gap-2 text-xs text-muted-foreground">
            <input type="checkbox" name="content_sync_bootstrap_uninitialized" value="1" class="rounded border-input text-primary focus:ring-primary" />
            Bootstrap uninitialized content sync state to current deployed ref
          </label>
          <label class="inline-flex items-center gap-2 text-xs text-muted-foreground">
            <input type="checkbox" name="content_sync_takeover_stale_lock" value="1" class="rounded border-input text-primary focus:ring-primary" />
            Take over stale content lock
          </label>
          <button type="submit" name="run_mode" value="dry_run" class="inline-flex items-center justify-center rounded-2xl border border-border/70 bg-background px-5 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-muted/40">
            Dry-run Repair
          </button>
          <button type="submit" name="run_mode" value="live" class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
            Repair Sync
          </button>
        </form>
      </div>
      <div class="mt-4 text-xs text-muted-foreground">
        CLI hints:
        <code>php artisan content:sync-status --json</code>
        <span class="block mt-2"><code>php artisan content:plan-sync --json</code></span>
        <span class="block mt-2"><code>php artisan content:apply-sync --dry-run</code></span>
      </div>
    </div>
  </div>
</section>
