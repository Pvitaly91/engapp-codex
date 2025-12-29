@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($selects = $data['selects'] ?? [])
@php($inputs = $data['inputs'] ?? [])
@php($rephrase = $data['rephrase'] ?? [])
@php($options = $data['options'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-3xl border border-border/50 bg-card/80 p-6 md:p-8 shadow-sm backdrop-blur-sm">
        @if(!empty($data['title']))
            <h2 class="text-xl md:text-2xl font-bold text-foreground mb-6 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-brand-600 text-white text-sm font-bold shadow-sm">
                    {{ preg_replace('/[^0-9]/', '', $data['title']) ?: '⚡' }}
                </span>
                {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
            </h2>
        @endif

        <div class="space-y-8">
            {{-- Select Exercise --}}
            @if(!empty($selects))
                <div class="rounded-2xl border border-border/50 bg-gradient-to-br from-blue-50/50 to-background overflow-hidden">
                    <div class="border-b border-border/50 bg-blue-100/30 px-5 py-4">
                        <h3 class="font-bold text-foreground flex items-center gap-2">
                            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                            </svg>
                            {{ $data['select_title'] ?? 'Обери правильне слово' }}
                        </h3>
                        @if(!empty($data['select_intro']))
                            <p class="text-sm text-muted-foreground mt-1">{!! $data['select_intro'] !!}</p>
                        @endif
                    </div>
                    <div class="p-5 space-y-4">
                        @foreach($selects as $index => $item)
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 text-xs font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <div class="flex-1">
                                    <label class="block text-sm text-foreground/80 mb-2">
                                        {!! $item['label'] ?? '' !!}
                                    </label>
                                    <select class="w-full sm:w-auto rounded-xl border-border bg-background px-4 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-all">
                                        <option value="">— обери —</option>
                                        @foreach($options as $option)
                                            <option>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Input Exercise --}}
            @if(!empty($inputs))
                <div class="rounded-2xl border border-border/50 bg-gradient-to-br from-emerald-50/50 to-background overflow-hidden">
                    <div class="border-b border-border/50 bg-emerald-100/30 px-5 py-4">
                        <h3 class="font-bold text-foreground flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ $data['input_title'] ?? 'Заповни пропуски' }}
                        </h3>
                        @if(!empty($data['input_intro']))
                            <p class="text-sm text-muted-foreground mt-1">{!! $data['input_intro'] !!}</p>
                        @endif
                    </div>
                    <div class="p-5 space-y-4">
                        @foreach($inputs as $index => $item)
                            <div class="flex flex-wrap items-center gap-2 text-sm text-foreground/80">
                                <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-xs font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <span>{!! $item['before'] ?? '' !!}</span>
                                <input 
                                    type="text" 
                                    class="w-32 rounded-xl border-border bg-background px-3 py-1.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-all" 
                                    placeholder="..."
                                />
                                <span>{!! $item['after'] ?? '' !!}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Rephrase Exercise --}}
            @if(!empty($rephrase))
                <div class="rounded-2xl border border-border/50 bg-gradient-to-br from-purple-50/50 to-background overflow-hidden">
                    <div class="border-b border-border/50 bg-purple-100/30 px-5 py-4">
                        <h3 class="font-bold text-foreground flex items-center gap-2">
                            <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            {{ $data['rephrase_title'] ?? 'Перефразуй' }}
                        </h3>
                        @if(!empty($data['rephrase_intro']))
                            <p class="text-sm text-muted-foreground mt-1">{!! $data['rephrase_intro'] !!}</p>
                        @endif
                    </div>
                    <div class="p-5 space-y-5">
                        @foreach($rephrase as $index => $item)
                            @if($index === 0 && !empty($item['example_original']))
                                {{-- Example --}}
                                <div class="rounded-xl bg-purple-100/50 border border-purple-200/50 p-4">
                                    <span class="text-xs font-bold uppercase tracking-wider text-purple-600 mb-2 block">
                                        {{ $item['example_label'] ?? 'Приклад:' }}
                                    </span>
                                    <div class="space-y-1 font-mono text-xs">
                                        <p class="text-foreground/70">{{ $item['example_original'] }}</p>
                                        <p class="text-emerald-600 flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                            {{ $item['example_target'] ?? '' }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                {{-- Task --}}
                                <div class="space-y-2">
                                    <p class="text-sm text-foreground/80 font-mono">
                                        {{ $item['original'] ?? '' }}
                                    </p>
                                    <input 
                                        type="text" 
                                        class="w-full rounded-xl border-border bg-background px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-all" 
                                        placeholder="{{ $item['placeholder'] ?? '' }}"
                                    />
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
