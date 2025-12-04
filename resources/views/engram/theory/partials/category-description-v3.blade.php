{{-- Category Description V3 Block Renderer --}}
@php
    $blocks = $categoryDescription['blocks'] ?? collect();
    $subtitleBlock = $categoryDescription['subtitleBlock'] ?? null;
    $excludedTypes = ['subtitle', 'hero', 'hero-v2', 'navigation-chips'];
    $v3BlockTypes = ['forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list'];
    $contentBlocks = $blocks->reject(fn($b) => in_array($b->type, $excludedTypes));
    $heroBlock = $blocks->firstWhere('type', 'hero') ?? $blocks->firstWhere('type', 'hero-v2');
    $heroData = $heroBlock ? (json_decode($heroBlock->body ?? '[]', true) ?? []) : [];
@endphp

{{-- Category Hero Card --}}
<div class="rounded-2xl border border-border/60 bg-card overflow-hidden">
    <div class="relative bg-gradient-to-br from-primary/5 via-transparent to-secondary/5 p-6">
        {{-- Category Title with Icon --}}
        <div class="flex items-start gap-4">
            <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-primary/70 text-white shadow-lg">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold text-foreground mb-2">{{ $page->title }}</h2>
                @if($subtitleBlock && !empty($subtitleBlock->body))
                    <div class="text-sm text-muted-foreground leading-relaxed prose prose-sm max-w-none">
                        {!! $subtitleBlock->body !!}
                    </div>
                @endif
                
                {{-- Level Badge if present --}}
                @if(!empty($heroData['level']))
                    <div class="mt-3 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-100 text-amber-700 px-3 py-1 text-xs font-bold">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Рівень {{ $heroData['level'] }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Hero Quick Rules (if present) --}}
@if(!empty($heroData['rules']))
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($heroData['rules'] as $index => $rule)
            @php($ruleColor = $rule['color'] ?? 'slate')
            @php($borderColor = match($ruleColor) {
                'emerald' => 'border-l-emerald-500',
                'blue' => 'border-l-blue-500',
                'rose' => 'border-l-rose-500',
                'amber' => 'border-l-amber-500',
                'sky' => 'border-l-sky-500',
                default => 'border-l-slate-500',
            })
            @php($iconBg = match($ruleColor) {
                'emerald' => 'bg-emerald-100 text-emerald-600',
                'blue' => 'bg-blue-100 text-blue-600',
                'rose' => 'bg-rose-100 text-rose-600',
                'amber' => 'bg-amber-100 text-amber-600',
                'sky' => 'bg-sky-100 text-sky-600',
                default => 'bg-slate-100 text-slate-600',
            })
            <article class="group rounded-xl border border-border/60 {{ $borderColor }} border-l-4 bg-card p-4 transition-all hover:shadow-md hover:bg-card/90">
                <div class="flex items-start gap-3">
                    <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ $iconBg }} text-sm font-bold">
                        {{ $index + 1 }}
                    </span>
                    <div class="min-w-0 flex-1">
                        @if(!empty($rule['label']))
                            <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground block mb-1">
                                {{ $rule['label'] }}
                            </span>
                        @endif
                        <p class="text-sm text-foreground/80 leading-relaxed">
                            {!! $rule['text'] ?? '' !!}
                        </p>
                        @if(!empty($rule['example']))
                            <code class="mt-2 block rounded-lg bg-muted/60 px-3 py-2 font-mono text-xs text-muted-foreground">
                                {{ $rule['example'] }}
                            </code>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif

{{-- Content Blocks - Render using V3 block templates --}}
@if($contentBlocks->isNotEmpty())
    <div class="space-y-4">
        @foreach($contentBlocks as $block)
            @if(in_array($block->type, $v3BlockTypes))
                {{-- V3-style blocks with JSON data --}}
                @php($blockData = json_decode($block->body ?? '[]', true) ?? [])
                @includeIf('engram.theory.blocks-v3.' . $block->type, [
                    'block' => $block,
                    'data' => $blockData,
                ])
            @elseif($block->type === 'box' || empty($block->type))
                {{-- Legacy box blocks - render with modern styling --}}
                <div class="rounded-2xl border border-border/60 bg-card overflow-hidden">
                    @if(!empty($block->heading))
                        <div class="border-b border-border/40 bg-muted/30 px-5 py-4">
                            <h3 class="flex items-center gap-3 text-base font-bold text-foreground">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-secondary text-secondary-foreground text-xs font-bold">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                                {{ $block->heading }}
                            </h3>
                        </div>
                    @endif
                    <div class="p-5">
                        <div class="prose prose-sm max-w-none text-muted-foreground
                            prose-headings:text-foreground prose-headings:font-bold
                            prose-strong:text-foreground prose-strong:font-semibold
                            prose-a:text-primary prose-a:no-underline hover:prose-a:underline
                            prose-code:bg-muted prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-xs
                            prose-ul:my-2 prose-li:my-0.5
                            prose-table:text-sm prose-th:bg-muted/50 prose-th:px-3 prose-th:py-2 prose-td:px-3 prose-td:py-2">
                            {!! $block->body !!}
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif
