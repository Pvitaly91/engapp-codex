@php
    $blocks = $page->textBlocks ?? collect();
    $heroBlock = $blocks->firstWhere('type', 'hero-v2') ?? $blocks->firstWhere('type', 'hero');
    $heroData = $heroBlock ? (json_decode($heroBlock->body ?? '[]', true) ?? []) : [];
    $contentBlocks = $blocks->reject(fn ($block) => in_array($block->type, ['hero', 'hero-v2', 'navigation-chips']));
    $navBlock = $blocks->firstWhere('type', 'navigation-chips');
    $practiceQuestionsByBlock = $practiceQuestionsByBlock ?? [];
@endphp

<div class="space-y-6">
    @if(!empty($heroData['intro']) || !empty($heroData['rules']))
        <section class="rounded-[28px] border p-6 surface-card-strong" style="border-color: var(--line);">
            @if(!empty($heroData['level']))
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    {{ __('theory_blocks.hero.level', ['level' => $heroData['level']]) }}
                </span>
            @endif
            @if(!empty($heroData['intro']))
                <div class="mt-4 max-w-3xl text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    {!! $heroData['intro'] !!}
                </div>
            @endif
            @if(!empty($heroData['rules']))
                <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($heroData['rules'] as $rule)
                        <article class="rounded-[22px] border p-5 surface-card" style="border-color: var(--line);">
                            @if(!empty($rule['label']))
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ $rule['label'] }}</p>
                            @endif
                            <div class="mt-3 text-sm leading-6" style="color: var(--text);">{!! $rule['text'] ?? '' !!}</div>
                            @if(!empty($rule['example']))
                                <code class="mt-4 block rounded-[16px] px-3 py-2 text-xs" style="background: var(--accent-soft); color: var(--text);">{{ $rule['example'] }}</code>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    <section class="rounded-[30px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="space-y-6">
            @foreach($contentBlocks as $block)
                <div id="block-{{ $block->id }}">
                    @if(in_array($block->type, ['forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list', 'practice-set']))
                        @includeIf('engram.theory.blocks-v3.' . $block->type, [
                            'page' => $page,
                            'block' => $block,
                            'data' => json_decode($block->body ?? '[]', true),
                            'practiceQuestions' => $practiceQuestionsByBlock[$block->uuid] ?? collect(),
                        ])
                    @elseif($block->type === 'box' || empty($block->type))
                        <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                            @if(!empty($block->heading))
                                <h3 class="font-display text-lg font-extrabold leading-tight">{{ $block->heading }}</h3>
                            @endif
                            <div class="prose prose-sm mt-4 max-w-none leading-7" style="color: var(--muted);">
                                {!! $block->body !!}
                            </div>
                        </article>
                    @endif
                </div>
            @endforeach
        </div>

        @if($navBlock)
            @php($navData = json_decode($navBlock->body ?? '[]', true) ?? [])
            @if(!empty($navData['items']))
                <nav class="mt-8 rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    @if(!empty($navData['title']))
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ $navData['title'] }}</p>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($navData['items'] as $item)
                            @if(!empty($item['current']))
                                <span class="rounded-[16px] px-4 py-2 text-sm font-bold" style="background: var(--accent-soft); color: var(--text);">{{ $item['label'] ?? '' }}</span>
                            @else
                                <span class="rounded-[16px] border px-4 py-2 text-sm font-bold surface-card-strong" style="border-color: var(--line); color: var(--muted);">
                                    {{ $item['label'] ?? '' }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </nav>
            @endif
        @endif
    </section>
</div>
