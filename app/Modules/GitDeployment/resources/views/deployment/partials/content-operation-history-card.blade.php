@php
  $runs = $recentContentRuns ?? collect();
@endphp

<section class="rounded-3xl border border-border/70 bg-card shadow-soft">
  <div class="space-y-6 p-6">
    <div class="flex items-center justify-between gap-4">
      <div class="space-y-2">
        <h2 class="text-2xl font-semibold">Recent Content Operations</h2>
        <p class="text-sm text-muted-foreground">
          Recent execution-grade changed-content apply and sync-repair runs across CLI and deployment UI.
        </p>
      </div>
      <a href="{{ route('deployment.content-runs') }}" class="inline-flex items-center justify-center rounded-2xl border border-border/70 bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-muted/40">
        View History
      </a>
    </div>

    @if($runs->isEmpty())
      <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
        No content operation runs have been recorded yet.
      </div>
    @else
      <div class="space-y-3">
        @foreach($runs as $run)
          @php
            $status = (string) ($run->status ?? 'failed');
            $statusClass = match ($status) {
              'success' => 'bg-success/15 text-success',
              'dry_run' => 'bg-primary/15 text-primary',
              'blocked' => 'bg-warning/15 text-warning',
              'partial' => 'bg-warning/15 text-warning',
              default => 'bg-destructive/15 text-destructive-foreground',
            };
          @endphp
          <div class="rounded-2xl border border-border/60 bg-muted/20 p-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="font-semibold text-foreground">#{{ $run->id }}</span>
                  <span class="text-sm text-muted-foreground">{{ $run->operation_kind }}</span>
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span>
                </div>
                <div class="text-sm text-muted-foreground">
                  trigger={{ $run->trigger_source }},
                  replayed_from={{ $run->replayed_from_run_id ?: '—' }},
                  domains={{ implode(',', (array) ($run->domains ?? [])) ?: '—' }},
                  base={{ $run->base_ref ?: '—' }},
                  head={{ $run->head_ref ?: '—' }}
                </div>
                <div class="text-xs text-muted-foreground">
                  started={{ optional($run->started_at)->toIso8601String() ?: '—' }},
                  finished={{ optional($run->finished_at)->toIso8601String() ?: '—' }}
                </div>
              </div>
              <a href="{{ route('deployment.content-runs.show', $run->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-border/70 bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-muted/40">
                Details
              </a>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</section>
