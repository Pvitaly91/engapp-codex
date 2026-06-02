@php
  $deploymentContentApply = $contentApply ?? null;
  $applyResult = (array) data_get($deploymentContentApply, 'content_apply.result', []);
  $applyStatus = (string) data_get($deploymentContentApply, 'status', 'ready');
  $applyError = is_array(data_get($deploymentContentApply, 'error')) ? data_get($deploymentContentApply, 'error') : null;
  $isFailure = in_array($applyStatus, ['preview_failed', 'deploy_failed', 'content_apply_failed'], true) || $applyError !== null;
  $cleanupPackages = (array) data_get($applyResult, 'execution.cleanup_deleted.packages', []);
  $upsertPackages = (array) data_get($applyResult, 'execution.upsert_present.packages', []);
  $lock = (array) data_get($deploymentContentApply, 'lock', []);
@endphp

@if($deploymentContentApply)
  <section class="rounded-3xl border shadow-soft @if($isFailure) border-destructive/50 bg-destructive/5 @else border-border/70 bg-card @endif">
    <div class="space-y-6 p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
          <h2 class="text-2xl font-semibold">Deployment Content Apply</h2>
          <p class="text-sm text-muted-foreground">
            Source: <code>{{ data_get($deploymentContentApply, 'deployment.source_kind', 'deploy') }}</code>,
            mode: <code>{{ data_get($deploymentContentApply, 'deployment.mode', 'standard') }}</code>,
            base: <code>{{ data_get($deploymentContentApply, 'deployment.base_ref', '—') }}</code>,
            head: <code>{{ data_get($deploymentContentApply, 'deployment.head_ref', '—') }}</code>
          </p>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold @if($isFailure) bg-destructive/15 text-destructive-foreground @else bg-success/15 text-success @endif">
          <span class="inline-flex h-2.5 w-2.5 rounded-full @if($isFailure) bg-destructive @else bg-success @endif"></span>
          {{ $isFailure ? 'Content apply failed' : (data_get($deploymentContentApply, 'content_apply.dry_run', false) ? 'Dry-run ready' : 'Content apply completed') }}
        </span>
      </div>

      <div class="grid gap-3 text-sm md:grid-cols-4">
        <div class="rounded-2xl border border-border/60 bg-muted/30 p-4">
          <div class="text-xs uppercase tracking-wide text-muted-foreground">Execution</div>
          <div class="mt-2 font-semibold text-foreground">
            {{ data_get($deploymentContentApply, 'content_apply.dry_run', false) ? 'dry-run' : 'live' }}
          </div>
          <div class="mt-1 text-xs text-muted-foreground">
            requested={{ data_get($deploymentContentApply, 'deployment.content_apply_requested', false) ? 'true' : 'false' }},
            executed={{ data_get($deploymentContentApply, 'deployment.content_apply_executed', false) ? 'true' : 'false' }}
          </div>
        </div>
        <div class="rounded-2xl border border-border/60 bg-muted/30 p-4">
          <div class="text-xs uppercase tracking-wide text-muted-foreground">Plan</div>
          <div class="mt-2 text-sm text-foreground">
            changed={{ (int) data_get($applyResult, 'plan.summary.changed_packages', 0) }},
            deleted-cleanup={{ (int) data_get($applyResult, 'plan.summary.deleted_cleanup_candidates', 0) }}
          </div>
          <div class="mt-1 text-xs text-muted-foreground">
            seed={{ (int) data_get($applyResult, 'plan.summary.seed_candidates', 0) }},
            refresh={{ (int) data_get($applyResult, 'plan.summary.refresh_candidates', 0) }}
          </div>
        </div>
        <div class="rounded-2xl border border-border/60 bg-muted/30 p-4">
          <div class="text-xs uppercase tracking-wide text-muted-foreground">Preflight</div>
          <div class="mt-2 text-sm text-foreground">
            ok={{ (int) data_get($applyResult, 'preflight.summary.ok', 0) }},
            warn={{ (int) data_get($applyResult, 'preflight.summary.warn', 0) }},
            fail={{ (int) data_get($applyResult, 'preflight.summary.fail', 0) }}
          </div>
          <div class="mt-1 text-xs text-muted-foreground">
            candidates={{ (int) data_get($applyResult, 'preflight.summary.candidates', 0) }},
            skipped={{ (int) data_get($applyResult, 'preflight.summary.skipped', 0) }}
          </div>
        </div>
        <div class="rounded-2xl border border-border/60 bg-muted/30 p-4">
          <div class="text-xs uppercase tracking-wide text-muted-foreground">Report</div>
          <div class="mt-2 text-sm text-foreground break-all">
            {{ data_get($deploymentContentApply, 'artifacts.report_path', '—') }}
          </div>
        </div>
      </div>

      @if($lock !== [])
        <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm">
          <div class="font-semibold text-foreground">Content operation lock</div>
          <div class="mt-2 text-muted-foreground">
            acquired={{ data_get($lock, 'acquired', false) ? 'true' : 'false' }},
            status=<code>{{ data_get($lock, 'status', 'unknown') }}</code>,
            takeover={{ data_get($lock, 'takeover.performed', false) ? 'performed' : (data_get($lock, 'takeover.requested', false) ? 'requested' : 'not-requested') }},
            run_id=<code>{{ data_get($lock, 'content_operation_run_id', '—') ?: '—' }}</code>
          </div>
        </div>
      @endif

      @if($applyError)
        <div class="rounded-2xl border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive-foreground">
          <div class="font-semibold">Execution error</div>
          <div class="mt-2">{{ data_get($applyError, 'message', 'Unknown deployment content-apply failure.') }}</div>
        </div>
      @endif

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-3">
          <h3 class="text-lg font-semibold">Deleted Packages Cleanup Phase</h3>
          @if($cleanupPackages === [])
            <p class="text-sm text-muted-foreground">No deleted content packages executed in this phase.</p>
          @else
            <ul class="space-y-2 text-sm">
              @foreach($cleanupPackages as $package)
                <li class="rounded-2xl border border-border/60 bg-background/70 p-3">
                  <div class="font-medium text-foreground">
                    [{{ data_get($package, 'domain', 'unknown') }}] {{ data_get($package, 'relative_path', data_get($package, 'package_key', '—')) }}
                  </div>
                  <div class="mt-1 text-xs text-muted-foreground">
                    action={{ data_get($package, 'action', 'unseed_deleted') }},
                    status={{ data_get($package, 'status', 'pending') }},
                    seed_run_removed={{ data_get($package, 'seed_run_removed', false) ? 'true' : 'false' }}
                  </div>
                </li>
              @endforeach
            </ul>
          @endif
        </div>

        <div class="space-y-3">
          <h3 class="text-lg font-semibold">Current Packages Upsert Phase</h3>
          @if($upsertPackages === [])
            <p class="text-sm text-muted-foreground">No current content packages executed in this phase.</p>
          @else
            <ul class="space-y-2 text-sm">
              @foreach($upsertPackages as $package)
                <li class="rounded-2xl border border-border/60 bg-background/70 p-3">
                  <div class="font-medium text-foreground">
                    [{{ data_get($package, 'domain', 'unknown') }}] {{ data_get($package, 'relative_path', data_get($package, 'package_key', '—')) }}
                  </div>
                  <div class="mt-1 text-xs text-muted-foreground">
                    action={{ data_get($package, 'action', 'seed') }},
                    status={{ data_get($package, 'status', 'pending') }},
                    seed_run_logged={{ data_get($package, 'seed_run_logged', false) ? 'true' : 'false' }}
                  </div>
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>
  </section>
@endif
