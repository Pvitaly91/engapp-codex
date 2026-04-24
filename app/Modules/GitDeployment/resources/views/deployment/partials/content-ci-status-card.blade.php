@php
  $ciStatus = is_array($contentCiStatus ?? null) ? $contentCiStatus : null;
  $ciDispatch = is_array($contentCiDispatch ?? null) ? $contentCiDispatch : null;
  $readiness = (string) data_get($ciStatus, 'readiness.status', 'not_run');
  $rawStatus = (string) data_get($ciStatus, 'readiness.raw_status', $readiness);
  $highLevel = (string) data_get($ciStatus, 'readiness.high_level_status', 'unknown');
  $targetBranch = (string) (data_get($ciStatus, 'target.branch') ?: '');
  $targetRef = (string) (data_get($ciStatus, 'target.ref') ?: $targetBranch);
  $targetSha = (string) (data_get($ciStatus, 'target.sha') ?: '');
  $badgeClass = match ($readiness) {
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
        <h2 class="text-2xl font-semibold">ContentOps CI Status</h2>
        <p class="text-sm text-muted-foreground">
          Read-only GitHub Actions lookup for the ContentOps Release Gate workflow on the target ref/SHA.
        </p>
      </div>
      <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
        {{ $readiness === 'not_run' ? 'not run' : $readiness }}
      </span>
    </div>

    <form method="GET" action="{{ $ciStatusRoute }}" class="grid gap-3 rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm md:grid-cols-4">
      <label class="space-y-1">
        <span class="font-medium text-foreground">Ref</span>
        <input type="text" name="ref" placeholder="branch, tag, SHA, HEAD" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
      </label>

      <label class="space-y-1">
        <span class="font-medium text-foreground">Branch</span>
        <input type="text" name="branch" placeholder="main" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
      </label>

      <label class="space-y-1">
        <span class="font-medium text-foreground">SHA</span>
        <input type="text" name="sha" placeholder="exact target SHA" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
      </label>

      <label class="space-y-1">
        <span class="font-medium text-foreground">Max age minutes</span>
        <input type="number" name="max_age_minutes" min="0" value="{{ config('git-deployment.contentops_ci_status.max_age_minutes', 1440) }}" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
      </label>

      <div class="grid gap-2 text-xs text-muted-foreground md:col-span-4 md:grid-cols-4">
        <label class="flex items-center gap-2">
          <input type="checkbox" name="require_success" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Require success
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="allow_in_progress" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Allow in-progress
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="strict" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Strict warnings
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="write_report" value="1" class="rounded border-input text-primary focus:ring-primary" />
          Write report
        </label>
      </div>

      <div class="flex flex-wrap items-center gap-3 md:col-span-4">
        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
          Check CI Status
        </button>
        <a href="{{ $ciStatusRoute }}?json=1&require_success=1" class="text-xs font-medium text-muted-foreground hover:text-foreground">
          JSON endpoint
        </a>
      </div>
    </form>

    @if(isset($ciDispatchRoute))
      <form method="POST" action="{{ $ciDispatchRoute }}" class="grid gap-3 rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm md:grid-cols-4">
        @csrf
        <input type="hidden" name="workflow" value="{{ config('git-deployment.contentops_ci_status.workflow_file', 'contentops-release-gate.yml') }}" />

        <div class="md:col-span-4">
          <h3 class="font-semibold text-foreground">Run ContentOps CI Gate</h3>
          <p class="text-xs text-muted-foreground">
            Dispatches only the read-only ContentOps Release Gate workflow. It does not deploy or apply content.
          </p>
        </div>

        <label class="space-y-1">
          <span class="font-medium text-foreground">Dispatch ref / branch</span>
          <input type="text" name="branch" value="{{ old('branch', $targetBranch) }}" placeholder="main" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
        </label>

        <label class="space-y-1">
          <span class="font-medium text-foreground">Target ref</span>
          <input type="text" name="ref" value="{{ old('ref', $targetRef) }}" placeholder="branch, tag, SHA" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
        </label>

        <label class="space-y-1">
          <span class="font-medium text-foreground">Target SHA</span>
          <input type="text" name="sha" value="{{ old('sha', $targetSha) }}" placeholder="exact target SHA" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
        </label>

        <label class="space-y-1">
          <span class="font-medium text-foreground">Base ref</span>
          <input type="text" name="base_ref" value="{{ old('base_ref', config('git-deployment.contentops_ci_status.default_base_ref', 'origin/main')) }}" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
        </label>

        <label class="space-y-1">
          <span class="font-medium text-foreground">Head ref input</span>
          <input type="text" name="head_ref" value="{{ old('head_ref', $targetSha ?: $targetRef) }}" placeholder="HEAD or target SHA" class="w-full rounded-2xl border border-input bg-background px-3 py-2" />
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
          <span class="font-medium text-foreground">Profile</span>
          <select name="profile" class="w-full rounded-2xl border border-input bg-background px-3 py-2">
            <option value="ci">ci</option>
            <option value="deployment">deployment</option>
            <option value="local">local</option>
          </select>
        </label>

        <label class="space-y-1">
          <span class="font-medium text-foreground">Run mode</span>
          <select name="run_mode" class="w-full rounded-2xl border border-input bg-background px-3 py-2">
            <option value="dry_run">dry-run only</option>
            <option value="live">live dispatch</option>
          </select>
        </label>

        <div class="grid gap-2 text-xs text-muted-foreground md:col-span-4 md:grid-cols-4">
          <input type="hidden" name="with_release_check" value="0" />
          <label class="flex items-center gap-2">
            <input type="checkbox" name="with_release_check" value="1" class="rounded border-input text-primary focus:ring-primary" @checked(config('git-deployment.contentops_ci_status.dispatch_with_release_check', true)) />
            With release-check
          </label>
          <input type="hidden" name="strict" value="0" />
          <label class="flex items-center gap-2">
            <input type="checkbox" name="strict" value="1" class="rounded border-input text-primary focus:ring-primary" @checked(config('git-deployment.contentops_ci_status.dispatch_strict', true)) />
            Strict
          </label>
          <label class="flex items-center gap-2">
            <input type="checkbox" name="write_report" value="1" class="rounded border-input text-primary focus:ring-primary" />
            Write report
          </label>
          <label class="flex items-center gap-2">
            <input type="checkbox" name="force" value="1" class="rounded border-input text-primary focus:ring-primary" />
            Confirm live dispatch
          </label>
        </div>

        <div class="flex flex-wrap items-center gap-3 md:col-span-4">
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-2 text-sm font-semibold text-secondary-foreground shadow-soft hover:bg-secondary/90">
            Run ContentOps CI Gate
          </button>
          <span class="text-xs text-muted-foreground">
            Live dispatch requires run mode = live and confirm live dispatch.
          </span>
        </div>
      </form>
    @endif

    @if($ciDispatch)
      <div class="rounded-2xl border border-border/60 bg-muted/20 p-3 text-sm">
        <div class="font-semibold text-foreground">Latest CI dispatch request</div>
        <div class="mt-2 grid gap-1 text-xs text-muted-foreground md:grid-cols-3">
          <span>dispatch: <code>{{ data_get($ciDispatch, 'dispatch.status', 'unknown') }}</code></span>
          <span>ref: <code>{{ data_get($ciDispatch, 'dispatch.ref') ?: 'n/a' }}</code></span>
          <span>target SHA: <code>{{ data_get($ciDispatch, 'target.sha') ?: 'n/a' }}</code></span>
        </div>
        @if(data_get($ciDispatch, 'run.found'))
          <div class="mt-2 text-xs text-muted-foreground">
            Run <code>{{ data_get($ciDispatch, 'run.id') }}</code> is <code>{{ data_get($ciDispatch, 'run.status') }}</code>.
            @if(data_get($ciDispatch, 'run.html_url'))
              <a href="{{ data_get($ciDispatch, 'run.html_url') }}" target="_blank" rel="noopener" class="font-medium text-primary hover:text-primary/80">Open run</a>
            @endif
          </div>
        @elseif(data_get($ciDispatch, 'dispatch.status') === 'dispatched')
          <div class="mt-2 text-xs text-muted-foreground">
            Dispatch accepted; GitHub may need a few seconds before the run appears in status lookup.
          </div>
        @endif
        @if(data_get($ciDispatch, 'error.message'))
          <div class="mt-2 text-xs text-destructive-foreground">{{ data_get($ciDispatch, 'error.message') }}</div>
        @endif
        @if(data_get($ciDispatch, 'artifacts.report_path'))
          <div class="mt-2 text-xs text-muted-foreground">Dispatch report: <code>{{ data_get($ciDispatch, 'artifacts.report_path') }}</code></div>
        @endif
      </div>
    @endif

    @if($ciStatus)
      <div class="grid gap-3 text-sm md:grid-cols-4">
        <div class="rounded-2xl border border-border/60 bg-muted/20 p-3">
          <div class="text-xs text-muted-foreground">raw status</div>
          <div class="text-lg font-semibold">{{ $rawStatus }}</div>
        </div>
        <div class="rounded-2xl border border-border/60 bg-muted/20 p-3">
          <div class="text-xs text-muted-foreground">CI state</div>
          <div class="text-lg font-semibold">{{ $highLevel }}</div>
        </div>
        <div class="rounded-2xl border border-border/60 bg-muted/20 p-3">
          <div class="text-xs text-muted-foreground">exact SHA</div>
          <div class="text-lg font-semibold">{{ data_get($ciStatus, 'match.exact_sha_verified') ? 'yes' : 'no' }}</div>
        </div>
        <div class="rounded-2xl border border-border/60 bg-muted/20 p-3">
          <div class="text-xs text-muted-foreground">run</div>
          <div class="text-lg font-semibold">{{ data_get($ciStatus, 'run.id') ?: 'n/a' }}</div>
        </div>
      </div>

      <div class="rounded-2xl border border-border/60 bg-muted/20 p-3 text-sm">
        <div class="font-semibold text-foreground">{{ data_get($ciStatus, 'readiness.code', 'ci_unknown') }}</div>
        <div class="text-muted-foreground">{{ data_get($ciStatus, 'readiness.message', '') }}</div>
        <div class="mt-2 grid gap-1 text-xs text-muted-foreground md:grid-cols-3">
          <span>target branch: <code>{{ data_get($ciStatus, 'target.branch') ?: 'n/a' }}</code></span>
          <span>target SHA: <code>{{ data_get($ciStatus, 'target.sha') ?: 'n/a' }}</code></span>
          <span>run SHA: <code>{{ data_get($ciStatus, 'run.head_sha') ?: 'n/a' }}</code></span>
        </div>
      </div>

      @if(!empty(data_get($ciStatus, 'run.html_url')))
        <a href="{{ data_get($ciStatus, 'run.html_url') }}" target="_blank" rel="noopener" class="inline-flex text-sm font-medium text-primary hover:text-primary/80">
          Open workflow run
        </a>
      @endif

      @if(!empty($ciStatus['artifacts']['report_path']))
        <p class="text-xs text-muted-foreground">Report: <code>{{ $ciStatus['artifacts']['report_path'] }}</code></p>
      @endif
    @else
      <p class="text-xs text-muted-foreground">
        CLI equivalent: <code>php artisan content:ci-status --ref=origin/main --require-success --json</code>
      </p>
    @endif
  </div>
</section>
