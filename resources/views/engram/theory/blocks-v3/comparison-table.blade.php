@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($rows = $data['rows'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-2xl border border-border/60 bg-card overflow-hidden">
        @if(!empty($data['title']))
            <div class="border-b border-border/40 bg-muted/30 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="flex items-center gap-3 text-lg font-bold text-foreground">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-accent text-accent-foreground text-xs font-bold">
                            {{ preg_replace('/[^0-9]/', '', $data['title']) ?: '#' }}
                        </span>
                        {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
                    </h2>
                    <x-text-block-level-badge :level="$block->level ?? null" />
                </div>
            </div>
        @endif

        <div class="p-5">
            @if(!empty($data['intro']))
                <p class="text-sm text-muted-foreground mb-5 leading-relaxed">{!! $data['intro'] !!}</p>
            @endif

            {{-- Table View --}}
            <div class="overflow-x-auto -mx-5 px-5">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">English</th>
                            <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">Українською</th>
                            <th class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">Форми / Примітки</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/50">
                        @foreach($rows as $index => $row)
                            <tr class="hover:bg-muted/30 transition-colors">
                                <td class="py-3 px-4">
                                    <code class="font-mono text-sm font-semibold text-foreground">
                                        {{ $row['en'] ?? '' }}
                                    </code>
                                </td>
                                <td class="py-3 px-4 text-muted-foreground">
                                    {{ $row['ua'] ?? '' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm text-foreground/70">
                                        {!! $row['note'] ?? '' !!}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Warning --}}
            @if(!empty($data['warning']))
                <div class="mt-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-amber-400 text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-amber-800">
                        {!! $data['warning'] !!}
                    </p>
                </div>
            @endif
        </div>
    </div>
</section>
