@php
  $doctor = is_array($contentDoctor ?? null) ? $contentDoctor : null;
  $overallStatus = (string) data_get($doctor, 'overall_status', 'not_run');
  $summary = (array) data_get($doctor, 'summary', []);
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
        <h2 class="text-2xl font-semibold">ContentOps Doctor</h2>
        <p class="text-sm text-muted-foreground">
          Read-only readiness checks for changed-content planning/apply, sync-state, history artifacts, global lock, GitDeployment wiring, and package roots.
        </p>
      </div>
      <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
        {{ $overallStatus === 'not_run' ? 'not run' : $overallStatus }}
      </span>
    </div>

    <form method="GET" action="{{ $doctorRoute }}" class="grid gap-3 rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm md:grid-cols-2">
      <label class="space-y-1">
        <span class="font-medium text-foreground">Domains</span>
        <select name="domains" class="w-full rounded-2xl border border-input bg-background px-3 py-2">
          <option value="v3,page-v3">v3,page-v3</option>
          <option value="v3">v3</option>
          <option value="page-v3">page-v3</option>
        </select>
      </label>

      <div class="grid gap-2 text-xs text-muted-foreground md:grid-cols-2">
        <label class="flex items-center gap-2">
          <input type="checkbox" name="with_deployment" value="1" class="rounded border-input text-primary focus:ring-primary" checked />
          Deployment wiring
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="with_package_roots" value="1" class="rounded border-input text-primary focus:ring-primary" checked />
          Package roots
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="with_git" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Git ref probe
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="with_artifacts" value="1" class="rounded border-input text-primary focus:ring-primary" />
          History artifacts
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="with_dry_plan" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Dry changed-plan read
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="strict" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Strict warnings
        </label>
      </div>

      <div class="flex flex-wrap items-center gap-3 md:col-span-2">
        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
          Run Doctor
        </button>
        <button type="submit" name="write_report" value="1" class="inline-flex items-center justify-center rounded-2xl border border-border/70 bg-background px-5 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-muted/40">
          Run and Write Report
        </button>
        <a href="{{ $doctorRoute }}?json=1&with_deployment=1&with_package_roots=1" class="text-xs font-medium text-muted-foreground hover:text-foreground">
          JSON endpoint
        </a>
      </div>
    </form>

    @if($doctor)
      <div class="grid gap-3 text-sm md:grid-cols-4">
        <div class="rounded-2xl border border-border/60 bg-muted/20 p-3">
          <div class="text-xs text-muted-foreground">total</div>
          <div class="text-xl font-semibold">{{ (int) ($summary['total'] ?? 0) }}</div>
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
      </div>

      <div class="space-y-2">
        @foreach(array_slice((array) ($doctor['checks'] ?? []), 0, 10) as $check)
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

      @if(count((array) ($doctor['checks'] ?? [])) > 10)
        <p class="text-xs text-muted-foreground">Showing first 10 checks. Use JSON output or the report for the full payload.</p>
      @endif

      @if(!empty($doctor['artifacts']['report_path']))
        <p class="text-xs text-muted-foreground">Report: <code>{{ $doctor['artifacts']['report_path'] }}</code></p>
      @endif
    @else
      <p class="text-xs text-muted-foreground">
        CLI equivalent: <code>php artisan content:doctor --with-deployment --with-package-roots --json</code>
      </p>
    @endif
  </div>
</section>
