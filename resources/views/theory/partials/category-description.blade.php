@php
    $blocks = $categoryDescription['blocks'] ?? collect();
    $subtitleBlock = $categoryDescription['subtitleBlock'] ?? null;
    $contentBlocks = $blocks->reject(fn($block) => in_array($block->type, ['subtitle', 'hero', 'hero-v2', 'navigation-chips']));
    $heroBlock = $blocks->firstWhere('type', 'hero-v2') ?? $blocks->firstWhere('type', 'hero');
    $heroData = $heroBlock ? (json_decode($heroBlock->body ?? '[]', true) ?? []) : [];
@endphp

<section class="space-y-6 rounded-[30px] border p-7 shadow-card surface-card-strong" style="border-color: var(--line);">
    <div class="rounded-[26px] border p-7 surface-card" style="border-color: var(--line);">
        <div class="flex items-start gap-4">
            <span class="inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-[22px] bg-ocean text-lg font-extrabold text-white">
                TH
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Category overview</p>
                <h2 class="mt-2 font-display text-[2rem] font-extrabold leading-tight">{{ $page->title }}</h2>
                @if($subtitleBlock && !empty($subtitleBlock->body))
                    <div class="mt-3 prose prose-sm max-w-none leading-7" style="color: var(--muted);">
                        {!! $subtitleBlock->body !!}
                    </div>
                @endif
            </div>
        </div>

        @if(!empty($heroData['rules']))
            <div class="mt-7 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($heroData['rules'] as $rule)
                    <div class="rounded-[20px] border px-4 py-4 surface-card-strong" style="border-color: var(--line);">
                        @if(!empty($rule['label']))
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ $rule['label'] }}</p>
                        @endif
                        <div class="mt-2 text-sm leading-6" style="color: var(--text);">{!! $rule['text'] ?? '' !!}</div>
                        @if(!empty($rule['example']))
                            <code class="mt-3 block rounded-[16px] px-3 py-2 text-xs" style="background: var(--accent-soft); color: var(--text);">{{ $rule['example'] }}</code>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($contentBlocks->isNotEmpty())
        <div class="space-y-5">
            @foreach($contentBlocks as $block)
                @php($blockData = json_decode($block->body ?? '[]', true) ?? [])
                @if(in_array($block->type, ['forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list', 'practice-set']))
                    @includeIf('engram.theory.blocks-v3.' . $block->type, [
                        'block' => $block,
                        'data' => $blockData,
                    ])
                @elseif($block->type === 'box' || empty($block->type))
                    <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                        @if(!empty($block->heading))
                            <h3 class="font-display text-[1.35rem] font-extrabold leading-tight">{{ $block->heading }}</h3>
                        @endif
                        <div class="prose prose-sm mt-4 max-w-none leading-7" style="color: var(--muted);">
                            {!! $block->body !!}
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    @endif
</section>
