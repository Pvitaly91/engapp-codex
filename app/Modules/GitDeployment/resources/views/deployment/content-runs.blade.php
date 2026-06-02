@extends('layouts.app')

@section('title', 'Content Operation History')

@section('content')
  <div class="max-w-5xl mx-auto space-y-8">
    <header class="space-y-2">
      <h1 class="text-3xl font-semibold">Content Operation History</h1>
      <p class="text-muted-foreground">Recent changed-content apply and sync-repair runs recorded from CLI and deployment flows.</p>
    </header>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div class="text-sm text-muted-foreground">
          total={{ data_get($historyPayload, 'summary.total', 0) }},
          by-status={{ json_encode((array) data_get($historyPayload, 'summary.by_status', []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }},
          by-kind={{ json_encode((array) data_get($historyPayload, 'summary.by_kind', []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}
        </div>

        @php $runs = (array) data_get($historyPayload, 'runs', []); @endphp
        @if($runs === [])
          <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
            No content operation runs found for the selected filters.
          </div>
        @else
          <div class="space-y-3">
            @foreach($runs as $run)
              @php
                $status = (string) ($run['status'] ?? 'failed');
                $statusClass = match ($status) {
                  'success' => 'bg-success/15 text-success',
                  'dry_run' => 'bg-primary/15 text-primary',
                  'blocked', 'partial' => 'bg-warning/15 text-warning',
                  default => 'bg-destructive/15 text-destructive-foreground',
                };
              @endphp
              <div class="rounded-2xl border border-border/60 bg-muted/20 p-4">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                      <span class="font-semibold text-foreground">#{{ $run['id'] }}</span>
                      <span class="text-sm text-muted-foreground">{{ $run['operation_kind'] }}</span>
                      <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span>
                    </div>
                    <div class="text-sm text-muted-foreground">
                      trigger={{ $run['trigger_source'] ?? 'unknown' }},
                      replayed_from={{ ($run['replayed_from_run_id'] ?? null) ?: '—' }},
                      domains={{ implode(',', (array) ($run['domains'] ?? [])) ?: '—' }},
                      base={{ ($run['base_ref'] ?? null) ?: '—' }},
                      head={{ ($run['head_ref'] ?? null) ?: '—' }},
                      execution={{ !empty($run['dry_run']) ? 'dry-run' : 'live' }}
                    </div>
                    <div class="text-xs text-muted-foreground">
                      started={{ ($run['started_at'] ?? null) ?: '—' }},
                      finished={{ ($run['finished_at'] ?? null) ?: '—' }}
                    </div>
                  </div>
                  <a href="{{ route('deployment.content-runs.show', $run['id']) }}" class="inline-flex items-center justify-center rounded-2xl border border-border/70 bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-muted/40">
                    View Details
                  </a>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </section>
  </div>
@endsection
