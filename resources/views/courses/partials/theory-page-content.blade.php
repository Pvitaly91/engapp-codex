@php
    $blocks = $page->textBlocks ?? collect();
    $heroBlock = $blocks->firstWhere('type', 'hero-v2') ?? $blocks->firstWhere('type', 'hero');
    $heroData = $heroBlock ? (json_decode($heroBlock->body ?? '[]', true) ?? []) : [];
    $contentBlocks = $blocks->reject(fn ($block) => in_array($block->type, ['hero', 'hero-v2', 'navigation-chips']));
    $presentationByBlock = $contentBlocks->mapWithKeys(fn ($block) => [
        $block->id => ($block->type === 'box' || empty($block->type)) ? \App\Support\TheoryPresentation::html($block) : null,
    ]);
    $navBlock = $blocks->firstWhere('type', 'navigation-chips');
    $practiceQuestionsByBlock = $practiceQuestionsByBlock ?? [];
@endphp

<div class="theory-design space-y-6">
    @if(!empty($heroData['intro']) || !empty($heroData['rules']))
        <section class="theory-hero" style="border-color: var(--line);">
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
                <div class="theory-rules mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($heroData['rules'] as $rule)
                        <article class="rounded-[22px] border p-5 surface-card" style="border-color: var(--line);">
                            @if(!empty($rule['label']))
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ $rule['label'] }}</p>
                            @endif
                            <div class="mt-3 text-sm leading-6" style="color: var(--text);">{!! $rule['text'] ?? '' !!}</div>
                            @if(!empty($rule['example']))
                                <code class="theory-example mt-4 block rounded-[16px] px-3 py-2 text-xs" style="background: var(--accent-soft); color: var(--text);">{{ \App\Support\TheoryRichContent::example($rule['example']) }}</code>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    <section class="theory-content-blocks">
        <div class="space-y-6">
            @foreach($contentBlocks as $block)
                @include('theory.partials.content-block', [
                    'presentation' => $presentationByBlock[$block->id],
                    'practiceQuestions' => $practiceQuestionsByBlock[$block->uuid] ?? collect(),
                ])
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
