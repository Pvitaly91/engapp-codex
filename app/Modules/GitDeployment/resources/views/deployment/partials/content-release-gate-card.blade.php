@php
  $gate = is_array($contentReleaseGate ?? null) ? $contentReleaseGate : null;
  $overallStatus = (string) data_get($gate, 'gate.overall_status', 'not_run');
  $rawStatus = (string) data_get($gate, 'gate.raw_status', $overallStatus);
  $summary = (array) data_get($gate, 'summary', []);
  $badgeClass = match ($overallStatus) {
    'pass' => 'bg-emerald-500/15 text-emerald-700',
    'warn' => 'bg-warning/15 text-warning',
    'fail' => 'bg-destructive/15 text-destructive-foreground',
    default => 'bg-muted text-muted-foreground',
  };
@endphp

<section class="rounded-3xl border border-border/70 bg-card shadow-soft">
  <div class="space-y-5 p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div class="space-y-2">
        <h2 class="text-2xl font-semibold">ContentOps Release Gate</h2>
        <p class="text-sm text-muted-foreground">
          Read-only CI/deployment preflight that combines doctor checks, changed-content planning, sync-state, and the global content lock.
        </p>
      </div>
      <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
        {{ $overallStatus === 'not_run' ? 'not run' : $overallStatus }}
      </span>
    </div>

    <form method="GET" action="{{ $releaseGateRoute }}" class="grid gap-3 rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm md:grid-cols-3">
      <label class="space-y-1">
        <span class="font-medium text-foreground">Profile</span>
        <select name="profile" class="w-full rounded-2xl border border-input bg-background px-3 py-2">
          <option value="deployment">deployment</option>
          <option value="ci">ci</option>
          <option value="local">local</option>
        </select>
      </label>

      <label class="space-y-1">
        <span class="font-medium text-foreground">Domains</span>
        <select name="domains" class="w-full rounded-2xl border border-input bg-background px-3 py-2">
          <option value="v3,page-v3">v3,page-v3</option>
          <option value="v3">v3</option>
          <option value="page-v3">page-v3</option>
        </select>
      </label>

      <label class="space-y-1">
        <span class="font-medium text-foreground">Target scope</span>
        <input type="text" name="target" placeholder="optional package/subtree path" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
      </label>

      <label class="space-y-1">
        <span class="font-medium text-foreground">Base ref</span>
        <input type="text" name="base" placeholder="optional" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
      </label>

      <label class="space-y-1">
        <span class="font-medium text-foreground">Head ref</span>
        <input type="text" name="head" placeholder="optional, defaults HEAD when base is set" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
      </label>

      <label class="space-y-1">
        <span class="font-medium text-foreground">Check profile</span>
        <select name="check_profile" class="w-full rounded-2xl border border-input bg-background px-3 py-2">
          <option value="release">release</option>
          <option value="scaffold">scaffold</option>
        </select>
      </label>

      <div class="grid gap-2 text-xs text-muted-foreground md:col-span-3 md:grid-cols-3">
        <label class="flex items-center gap-2">
          <input type="checkbox" name="with_release_check" value="1" class="rounded border-input text-primary focus:ring-primary" checked />
          Release-check readiness
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="with_git" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Git ref probe
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="with_deployment" value="1" class="rounded border-input text-primary focus:ring-primary" checked />
          Deployment wiring
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="with_package_roots" value="1" class="rounded border-input text-primary focus:ring-primary" checked />
          Package roots
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="with_artifacts" value="1" class="rounded border-input text-primary focus:ring-primary" />
          History artifacts
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="with_dry_plan" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Doctor dry-plan read
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="include_untracked" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Include untracked
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="strict" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Strict warnings
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="fail_on_sync_drift" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Fail on sync drift
        </label>
      </div>

      <div class="flex flex-wrap items-center gap-3 md:col-span-3">
        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
          Run Release Gate
        </button>
        <button type="submit" name="write_report" value="1" class="inline-flex items-center justify-center rounded-2xl border border-border/70 bg-background px-5 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-muted/40">
          Run and Write Report
        </button>
        <a href="{{ $releaseGateRoute }}?json=1&profile=deployment&with_release_check=1&with_deployment=1&with_package_roots=1" class="text-xs font-medium text-muted-foreground hover:text-foreground">
          JSON endpoint
        </a>
      </div>
    </form>

    @if($gate)
      <div class="grid gap-3 text-sm md:grid-cols-5">
        <div class="rounded-2xl border border-border/60 bg-muted/20 p-3">
          <div class="text-xs text-muted-foreground">raw status</div>
          <div class="text-lg font-semibold">{{ $rawStatus }}</div>
        </div>
        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-3">
          <div class="text-xs text-muted-foreground">pass</div>
          <div class="text-xl font-semibold text-emerald-700">{{ (int) ($summary['pass'] ?? 0) }}</div>
        </div>
        <div class="rounded-2xl border border-warning/20 bg-warning/10 p-3">
          <div class="text-xs text-muted-foreground">warn</div>
          <div class="text-xl font-semibold text-warning">{{ (int) ($summary['warn'] ?? 0) }}</div>
        </div>
        <div class="rounded-2xl border border-destructive/20 bg-destructive/10 p-3">
          <div class="text-xs text-muted-foreground">fail</div>
          <div class="text-xl font-semibold text-destructive-foreground">{{ (int) ($summary['fail'] ?? 0) }}</div>
        </div>
        <div class="rounded-2xl border border-border/60 bg-muted/20 p-3">
          <div class="text-xs text-muted-foreground">changed</div>
          <div class="text-xl font-semibold">{{ (int) ($summary['changed_packages'] ?? 0) }}</div>
        </div>
      </div>

      <div class="space-y-2">
        @foreach(array_slice((array) ($gate['checks'] ?? []), 0, 12) as $check)
          @php
            $check = (array) $check;
            $status = (string) ($check['status'] ?? 'fail');
            $statusClass = match ($status) {
              'pass' => 'bg-emerald-500/15 text-emerald-700',
              'warn' => 'bg-warning/15 text-warning',
              default => 'bg-destructive/15 text-destructive-foreground',
            };
          @endphp
          <div class="rounded-2xl border border-border/60 bg-muted/20 p-3 text-sm">
            <div class="flex flex-wrap items-start gap-3">
              <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span>
              <div class="min-w-0 flex-1">
                <div class="font-semibold text-foreground">{{ $check['code'] ?? 'unknown' }}</div>
                <div class="text-muted-foreground">{{ $check['message'] ?? '' }}</div>
                @if(!empty($check['recommendation']))
                  <div class="mt-1 text-xs text-muted-foreground">recommendation: {{ $check['recommendation'] }}</div>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>

      @if(count((array) ($gate['checks'] ?? [])) > 12)
        <p class="text-xs text-muted-foreground">Showing first 12 checks. Use JSON output or the report for the full payload.</p>
      @endif

      @if(!empty($gate['artifacts']['report_path']))
        <p class="text-xs text-muted-foreground">Report: <code>{{ $gate['artifacts']['report_path'] }}</code></p>
      @endif
    @else
      <p class="text-xs text-muted-foreground">
        CLI equivalent: <code>php artisan content:release-gate --profile=deployment --with-release-check --json</code>
      </p>
    @endif
  </div>
</section>
