@extends('layouts.app')

@section('title', 'Content Operation Run')

@section('content')
  @php $run = (array) data_get($historyPayload, 'run', []); @endphp
  @php $contentReplay = session('content_operation_replay', $contentReplay ?? null); @endphp

  <div class="max-w-5xl mx-auto space-y-8">
    <header class="space-y-2">
      <h1 class="text-3xl font-semibold">Content Operation Run #{{ $run['id'] ?? '—' }}</h1>
      <p class="text-muted-foreground">Execution-grade detail for one changed-content apply or sync-repair run.</p>
    </header>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div class="grid gap-4 md:grid-cols-2">
          <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
            <div>kind=<code>{{ $run['operation_kind'] ?? '—' }}</code></div>
            <div class="mt-2">trigger=<code>{{ $run['trigger_source'] ?? '—' }}</code></div>
            <div class="mt-2">domains=<code>{{ implode(',', (array) ($run['domains'] ?? [])) ?: '—' }}</code></div>
            <div class="mt-2">status=<code>{{ $run['status'] ?? '—' }}</code></div>
            <div class="mt-2">execution=<code>{{ !empty($run['dry_run']) ? 'dry-run' : 'live' }}</code></div>
            <div class="mt-2">replayed_from_run_id=<code>{{ ($run['replayed_from_run_id'] ?? null) ?: '—' }}</code></div>
          </div>
          <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
            <div>base=<code>{{ ($run['base_ref'] ?? null) ?: '—' }}</code></div>
            <div class="mt-2">head=<code>{{ ($run['head_ref'] ?? null) ?: '—' }}</code></div>
            <div class="mt-2">started=<code>{{ ($run['started_at'] ?? null) ?: '—' }}</code></div>
            <div class="mt-2">finished=<code>{{ ($run['finished_at'] ?? null) ?: '—' }}</code></div>
            <div class="mt-2">operator_user_id=<code>{{ ($run['operator_user_id'] ?? null) ?: '—' }}</code></div>
          </div>
        </div>

        <div class="rounded-2xl border border-border/60 bg-muted/20 p-4">
          <div class="font-semibold text-foreground">Compact Summary</div>
          <pre class="mt-3 overflow-x-auto whitespace-pre-wrap text-xs leading-relaxed text-muted-foreground">{{ json_encode((array) ($run['summary'] ?? []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>

        @if(!empty($run['error_excerpt']))
          <div class="rounded-2xl border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive-foreground">
            <div class="font-semibold">Error Excerpt</div>
            <div class="mt-2">{{ $run['error_excerpt'] }}</div>
          </div>
        @endif

        <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
          <div>payload_json_path=<code>{{ ($run['payload_json_path'] ?? null) ?: '—' }}</code></div>
          <div class="mt-2">report_path=<code>{{ ($run['report_path'] ?? null) ?: '—' }}</code></div>
          <div class="mt-2">replay_ids=<code>{{ implode(',', (array) ($run['replay_ids'] ?? [])) ?: '—' }}</code></div>
        </div>

        <section class="rounded-2xl border border-border/60 bg-muted/20 p-4 space-y-4">
          <div>
            <div class="font-semibold text-foreground">Replay Run</div>
            <p class="mt-1 text-sm text-muted-foreground">
              Replay this recorded execution context through the canonical changed-content or sync-repair service. Live replay never reruns code deployment.
            </p>
          </div>

          <form method="POST" action="{{ route('deployment.content-runs.retry', $run['id']) }}" class="space-y-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm text-muted-foreground">
                <span class="font-medium text-foreground">Replay Mode</span>
                <select name="run_mode" class="w-full rounded-2xl border border-border/70 bg-background px-4 py-3 text-sm text-foreground shadow-soft">
                  <option value="dry_run">Dry Run</option>
                  <option value="live">Live Retry</option>
                </select>
              </label>
              <div class="grid gap-3 text-sm text-muted-foreground">
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="reuse_original_mode" value="1" class="rounded border-border/70 text-primary focus:ring-primary">
                  <span>Reuse original live/dry-run mode</span>
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="allow_success" value="1" class="rounded border-border/70 text-primary focus:ring-primary">
                  <span>Allow replaying an original success</span>
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="takeover_stale_lock" value="1" class="rounded border-border/70 text-primary focus:ring-primary">
                  <span>Take over stale content lock</span>
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="strict" value="1" class="rounded border-border/70 text-primary focus:ring-primary">
                  <span>Treat stale-context warnings as blockers</span>
                </label>
              </div>
            </div>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl border border-border/70 bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-muted/40">
              Retry From Canonical History
            </button>
          </form>
        </section>

        @if(is_array($contentReplay))
          <section class="rounded-2xl border border-border/60 bg-muted/20 p-4 space-y-3">
            <div class="font-semibold text-foreground">Latest Replay Result</div>
            <div class="text-sm text-muted-foreground">
              status=<code>{{ data_get($contentReplay, 'replay.status', 'unknown') }}</code>,
              new_run=<code>{{ data_get($contentReplay, 'replay.new_run.id', '—') }}</code>,
              mode=<code>{{ data_get($contentReplay, 'replay.mode.dry_run', true) ? 'dry-run' : 'live' }}</code>
            </div>
            <div class="text-sm text-muted-foreground">
              warnings=<code>{{ json_encode((array) data_get($contentReplay, 'replay.validation.warnings', []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code>
            </div>
            <div class="text-sm text-muted-foreground">
              reasons=<code>{{ json_encode((array) data_get($contentReplay, 'replay.validation.reasons', []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code>
            </div>
            <div class="text-sm text-muted-foreground">
              payload_json_path=<code>{{ data_get($contentReplay, 'replay.new_run.payload_json_path', '—') }}</code>
            </div>
            <div class="text-sm text-muted-foreground">
              report_path=<code>{{ data_get($contentReplay, 'replay.new_run.report_path', data_get($contentReplay, 'artifacts.report_path', '—')) }}</code>
            </div>
          </section>
        @endif

        @if(array_key_exists('payload', $run))
          <details class="rounded-2xl border border-border/60 bg-muted/20 p-4">
            <summary class="cursor-pointer font-semibold text-foreground">Canonical Payload Artifact</summary>
            <pre class="mt-3 overflow-x-auto whitespace-pre-wrap text-xs leading-relaxed text-muted-foreground">{{ json_encode($run['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
          </details>
        @endif

        <div>
          <a href="{{ route('deployment.content-runs') }}" class="inline-flex items-center justify-center rounded-2xl border border-border/70 bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-muted/40">
            Back To History
          </a>
        </div>
      </div>
    </section>
  </div>
@endsection
