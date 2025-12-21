@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($selects = $data['selects'] ?? [])
@php($inputs = $data['inputs'] ?? [])
@php($rephrase = $data['rephrase'] ?? [])
@php($options = $data['options'] ?? [])

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-2xl border border-border/60 bg-card overflow-hidden">
        @if(!empty($data['title']))
            <div class="border-b border-border/40 bg-gradient-to-r from-primary/5 to-secondary/5 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="flex items-center gap-3 text-lg font-bold text-foreground">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-secondary text-white text-xs font-bold">
                            ⚡
                        </span>
                        {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
                    </h2>
                    <x-text-block-level-badge :level="$block->level ?? null" />
                </div>
            </div>
        @endif

        <div class="p-5 space-y-6">
            {{-- Select Exercise --}}
            @if(!empty($selects))
                <div class="rounded-xl border border-blue-100 bg-blue-50/30 overflow-hidden">
                    <div class="border-b border-blue-100 bg-blue-50/50 px-4 py-3">
                        <h3 class="font-semibold text-foreground text-sm flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded bg-blue-500 text-white text-[10px]">1</span>
                            {{ $data['select_title'] ?? 'Обери правильне слово' }}
                        </h3>
                        @if(!empty($data['select_intro']))
                            <p class="text-xs text-muted-foreground mt-1">{!! $data['select_intro'] !!}</p>
                        @endif
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($selects as $index => $item)
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 bg-white/60 rounded-lg p-3 border border-white">
                                <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 text-[10px] font-bold">
                                    {{ chr(97 + $index) }}
                                </span>
                                <div class="flex-1">
                                    <label class="block text-sm text-foreground/80 mb-1.5">
                                        {!! $item['label'] ?? '' !!}
                                    </label>
                                    <select class="w-full sm:w-48 rounded-lg border-border bg-white px-3 py-1.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary/30 transition-all">
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
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/30 overflow-hidden">
                    <div class="border-b border-emerald-100 bg-emerald-50/50 px-4 py-3">
                        <h3 class="font-semibold text-foreground text-sm flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded bg-emerald-500 text-white text-[10px]">2</span>
                            {{ $data['input_title'] ?? 'Заповни пропуски' }}
                        </h3>
                        @if(!empty($data['input_intro']))
                            <p class="text-xs text-muted-foreground mt-1">{!! $data['input_intro'] !!}</p>
                        @endif
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($inputs as $index => $item)
                            <div class="flex flex-wrap items-center gap-2 text-sm text-foreground/80 bg-white/60 rounded-lg p-3 border border-white">
                                <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-[10px] font-bold">
                                    {{ chr(97 + $index) }}
                                </span>
                                <span>{!! $item['before'] ?? '' !!}</span>
                                <input 
                                    type="text" 
                                    class="w-28 rounded-lg border-border bg-white px-2.5 py-1 text-sm focus:border-primary focus:ring-1 focus:ring-primary/30 transition-all" 
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
                <div class="rounded-xl border border-purple-100 bg-purple-50/30 overflow-hidden">
                    <div class="border-b border-purple-100 bg-purple-50/50 px-4 py-3">
                        <h3 class="font-semibold text-foreground text-sm flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded bg-purple-500 text-white text-[10px]">3</span>
                            {{ $data['rephrase_title'] ?? 'Перефразуй' }}
                        </h3>
                        @if(!empty($data['rephrase_intro']))
                            <p class="text-xs text-muted-foreground mt-1">{!! $data['rephrase_intro'] !!}</p>
                        @endif
                    </div>
                    <div class="p-4 space-y-4">
                        @foreach($rephrase as $index => $item)
                            @if($index === 0 && !empty($item['example_original']))
                                {{-- Example --}}
                                <div class="rounded-lg bg-purple-100/50 border border-purple-200/50 p-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 mb-1.5 block">
                                        {{ $item['example_label'] ?? 'Приклад:' }}
                                    </span>
                                    <div class="space-y-1 font-mono text-xs">
                                        <p class="text-foreground/60">{{ $item['example_original'] }}</p>
                                        <p class="text-emerald-600 flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                            {{ $item['example_target'] ?? '' }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                {{-- Task --}}
                                <div class="space-y-1.5 bg-white/60 rounded-lg p-3 border border-white">
                                    <p class="text-sm text-foreground/80 font-mono">
                                        {{ $item['original'] ?? '' }}
                                    </p>
                                    <input 
                                        type="text" 
                                        class="w-full rounded-lg border-border bg-white px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary/30 transition-all" 
                                        placeholder="{{ $item['placeholder'] ?? '' }}"
                                    />
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Block Tags --}}
            <x-text-block-tags :block="$block" />

            {{-- Practice Questions --}}
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
        </div>
    </div>
</section>
