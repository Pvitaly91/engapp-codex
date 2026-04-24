@php
  $preview = $contentPreview ?? null;
  $gateBlocked = (bool) data_get($preview, 'gate.blocked', false);
  $hasError = is_array(data_get($preview, 'error'));
  $summary = (array) data_get($preview, 'content_plan.summary', []);
  $cleanupPackages = (array) data_get($preview, 'content_plan.phases.cleanup_deleted', []);
  $upsertPackages = (array) data_get($preview, 'content_plan.phases.upsert_present', []);
  $gateReasons = array_values(array_filter((array) data_get($preview, 'gate.reasons', [])));
  $lockRequired = (bool) data_get($preview, 'lock.required_for_content_apply', false);
  $lockStatus = (string) data_get($preview, 'lock.current_status', data_get($preview, 'lock.status', 'unknown'));
  $lockBlocked = (bool) data_get($preview, 'lock.blocked', false);
@endphp

@if($preview)
  <section class="rounded-3xl border shadow-soft @if($gateBlocked || $hasError) border-destructive/50 bg-destructive/5 @else border-border/70 bg-card @endif">
    <div class="space-y-6 p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
          <h2 class="text-2xl font-semibold">Content-Aware Deployment Preview</h2>
          <p class="text-sm text-muted-foreground">
            Source: <code>{{ data_get($preview, 'deployment.source_kind', 'deploy') }}</code>,
            mode: <code>{{ data_get($preview, 'deployment.mode', 'standard') }}</code>,
            base: <code>{{ data_get($preview, 'deployment.base_ref', '—') }}</code>,
            head: <code>{{ data_get($preview, 'deployment.head_ref', '—') }}</code>
          </p>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold @if($gateBlocked || $hasError) bg-destructive/15 text-destructive-foreground @else bg-success/15 text-success @endif">
          <span class="inline-flex h-2.5 w-2.5 rounded-full @if($gateBlocked || $hasError) bg-destructive @else bg-success @endif"></span>
          {{ $gateBlocked || $hasError ? 'Deployment blocked' : 'Deployment preview ready' }}
        </span>
      </div>

      <div class="grid gap-3 text-sm md:grid-cols-3">
        <div class="rounded-2xl border border-border/60 bg-muted/30 p-4">
          <div class="text-xs uppercase tracking-wide text-muted-foreground">Release Check</div>
          <div class="mt-2 font-semibold text-foreground">
            {{ data_get($preview, 'deployment.with_release_check', false) ? 'Enabled' : 'Skipped' }}
          </div>
          <div class="mt-1 text-xs text-muted-foreground">Profile: {{ data_get($preview, 'deployment.check_profile', 'release') }}</div>
        </div>
        <div class="rounded-2xl border border-border/60 bg-muted/30 p-4">
          <div class="text-xs uppercase tracking-wide text-muted-foreground">Summary</div>
          <div class="mt-2 text-sm text-foreground">
            changed={{ (int) ($summary['changed_packages'] ?? 0) }},
            deleted-cleanup={{ (int) ($summary['deleted_cleanup_candidates'] ?? 0) }},
            seed={{ (int) ($summary['seed_candidates'] ?? 0) }},
            refresh={{ (int) ($summary['refresh_candidates'] ?? 0) }}
          </div>
        </div>
        <div class="rounded-2xl border border-border/60 bg-muted/30 p-4">
          <div class="text-xs uppercase tracking-wide text-muted-foreground">Gate</div>
          <div class="mt-2 text-sm text-foreground">
            blocked={{ $gateBlocked ? 'true' : 'false' }},
            strict={{ data_get($preview, 'gate.strict', false) ? 'true' : 'false' }},
            warnings={{ (int) ($summary['warnings'] ?? 0) }},
            blocked-packages={{ (int) ($summary['blocked'] ?? 0) }}
          </div>
        </div>
        <div class="rounded-2xl border border-border/60 bg-muted/30 p-4 md:col-span-3">
          <div class="text-xs uppercase tracking-wide text-muted-foreground">Pre-Code Content Lock</div>
          <div class="mt-2 text-sm text-foreground">
            required={{ $lockRequired ? 'true' : 'false' }},
            status=<code>{{ $lockStatus }}</code>,
            blocked={{ $lockBlocked ? 'true' : 'false' }},
            stale-takeover={{ data_get($preview, 'lock.stale_takeover_possible', false) ? 'available' : 'not-available' }}
          </div>
          <div class="mt-1 text-xs text-muted-foreground">
            When changed-content apply is enabled, deploy/restore reserves this lock before code update starts.
          </div>
        </div>
      </div>

      @if($gateReasons !== [])
        <div class="rounded-2xl border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive-foreground">
          <div class="font-semibold">Gate reasons</div>
          <ul class="mt-3 space-y-2">
            @foreach($gateReasons as $reason)
              <li>• {{ $reason }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if((int) ($summary['changed_packages'] ?? 0) === 0)
        <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
          No changed V3 or Page_V3 content packages were detected for this deploy diff.
        </div>
      @endif

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-3">
          <h3 class="text-lg font-semibold">Deleted Packages Cleanup Phase</h3>
          @if($cleanupPackages === [])
            <p class="text-sm text-muted-foreground">No deleted content packages in this deploy diff.</p>
          @else
            <ul class="space-y-2 text-sm">
              @foreach($cleanupPackages as $package)
                <li class="rounded-2xl border border-border/60 bg-background/70 p-3">
                  <div class="font-medium text-foreground">[{{ data_get($package, 'domain', 'unknown') }}] {{ data_get($package, 'relative_path', data_get($package, 'package_key', '—')) }}</div>
                  <div class="mt-1 text-xs text-muted-foreground">
                    type={{ data_get($package, 'package_type', 'unknown') }},
                    change={{ data_get($package, 'change_type', 'deleted') }},
                    state={{ data_get($package, 'package_state', 'unknown') }},
                    action={{ data_get($package, 'recommended_action', 'skip') }}
                  </div>
                </li>
              @endforeach
            </ul>
          @endif
        </div>

        <div class="space-y-3">
          <h3 class="text-lg font-semibold">Current Packages Upsert Phase</h3>
          @if($upsertPackages === [])
            <p class="text-sm text-muted-foreground">No current content packages require seed or refresh.</p>
          @else
            <ul class="space-y-2 text-sm">
              @foreach($upsertPackages as $package)
                <li class="rounded-2xl border border-border/60 bg-background/70 p-3">
                  <div class="font-medium text-foreground">[{{ data_get($package, 'domain', 'unknown') }}] {{ data_get($package, 'relative_path', data_get($package, 'package_key', '—')) }}</div>
                  <div class="mt-1 text-xs text-muted-foreground">
                    type={{ data_get($package, 'package_type', 'unknown') }},
                    change={{ data_get($package, 'change_type', 'modified') }},
                    state={{ data_get($package, 'package_state', 'unknown') }},
                    action={{ data_get($package, 'recommended_action', 'skip') }},
                    release={{ data_get($package, 'release_check.status', 'skipped') }}
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
