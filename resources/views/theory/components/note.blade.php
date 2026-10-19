@if(($node['variant'] ?? 'info') === 'plain')
    <div class="text-sm text-muted-foreground leading-relaxed theory-point-fragment">{{ \App\Support\TheoryComponents::body($node['html'] ?? '') }}</div>
@elseif(($node['variant'] ?? 'info') === 'compact')
    <p class="theory-note text-sm rounded-lg p-3">{{ \App\Support\TheoryComponents::body($node['html'] ?? '') }}</p>
@elseif(($node['variant'] ?? 'info') === 'hint')
    <div class="theory-note flex items-start gap-2 rounded-lg bg-slate-50 border border-slate-100 px-3 py-2 mt-2">
        <svg class="h-4 w-4 flex-shrink-0 text-slate-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        <span class="text-xs text-muted-foreground leading-relaxed">{{ \App\Support\TheoryComponents::body($node['html'] ?? '') }}</span>
    </div>
@elseif(($node['variant'] ?? 'info') === 'warning')
    <div class="theory-note mt-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-amber-400 text-white"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        <p class="text-sm text-amber-800">{{ \App\Support\TheoryComponents::body($node['html'] ?? '') }}</p>
    </div>
@else
    <div class="theory-note mt-3 flex items-start gap-2 text-xs text-muted-foreground bg-white/40 rounded-lg p-2.5">
        <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ \App\Support\TheoryComponents::body($node['html'] ?? '') }}</span>
    </div>
@endif
