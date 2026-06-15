@php
    $data = $data ?? json_decode($block->body ?? '[]', true) ?? [];
    $items = $data['items'] ?? [];
    $lessonLinks = $lessonLinks ?? [];
@endphp

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="rounded-2xl border border-border/60 bg-card overflow-hidden">
        @if(!empty($data['title']))
            <div class="border-b border-border/40 bg-muted/30 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="flex items-center gap-3 text-lg font-bold text-foreground">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-white text-xs font-bold">
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

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($items as $item)
                    @php
                        $itemTitle = (string) ($item['title'] ?? '');
                        $normalizedTitle = \Illuminate\Support\Str::lower(trim(preg_replace('/^\d+[.\d\s]*\s*/u', '', $itemTitle) ?? $itemTitle));
                        $itemUrl = $item['url'] ?? $lessonLinks[$itemTitle] ?? $lessonLinks[$normalizedTitle] ?? null;
                        $cardClass = 'group relative rounded-xl border border-border/50 bg-gradient-to-br from-muted/20 to-transparent p-4 transition-all hover:border-brand-500 hover:shadow-sm';
                        $rules = $item['rules'] ?? [];
                    @endphp

                    @if($itemUrl)
                        <a href="{{ $itemUrl }}" class="{{ $cardClass }} block">
                    @else
                        <div class="{{ $cardClass }}">
                    @endif
                        <div class="pr-7">
                            @if(!empty($item['label']))
                                <span class="mb-2 inline-block rounded-md bg-brand-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand-700">
                                    {{ $item['label'] }}
                                </span>
                            @endif

                            <h3 class="text-base font-bold text-foreground mb-1">
                                {{ $item['title'] ?? '' }}
                            </h3>

                            @if(!empty($item['subtitle']))
                                <p class="text-sm text-muted-foreground">
                                    {{ $item['subtitle'] }}
                                </p>
                            @endif
                        </div>

                        @if(!empty($rules))
                            <div class="mt-4 space-y-2.5">
                                @foreach($rules as $rule)
                                    <div class="rounded-2xl border border-border/50 bg-background/80 px-3 py-2.5">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if(!empty($rule['label']))
                                                <span class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-700">
                                                    {{ $rule['label'] }}
                                                </span>
                                            @endif
                                            @if(!empty($rule['formula']))
                                                <span class="rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-[0.12em] text-brand-700">
                                                    {!! $rule['formula'] !!}
                                                </span>
                                            @endif
                                        </div>
                                        @if(!empty($rule['text']))
                                            <div class="mt-2 text-xs leading-5 text-muted-foreground">
                                                {!! $rule['text'] !!}
                                            </div>
                                        @endif
                                        @if(!empty($rule['example']))
                                            <div class="mt-2 rounded-xl bg-muted/60 px-3 py-2 text-xs font-semibold leading-5 text-foreground">
                                                {!! $rule['example'] !!}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="absolute right-3 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="h-4 w-4 text-brand-600/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    @if($itemUrl)
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>

            <x-text-block-tags :block="$block" />

            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
        </div>
    </div>
</section>
