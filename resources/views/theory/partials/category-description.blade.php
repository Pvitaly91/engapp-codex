@php
    $blocks = $categoryDescription['blocks'] ?? collect();
    $subtitleBlock = $categoryDescription['subtitleBlock'] ?? null;
    $contentBlocks = $blocks->reject(fn($block) => in_array($block->type, ['subtitle', 'hero', 'hero-v2', 'navigation-chips']));
    $heroBlock = $blocks->firstWhere('type', 'hero-v2') ?? $blocks->firstWhere('type', 'hero');
    $heroData = $heroBlock ? (json_decode($heroBlock->body ?? '[]', true) ?? []) : [];
    $lessonLinks = $categoryDescription['lessonLinks'] ?? [];
@endphp

<section class="theory-design theory-category-learning space-y-6">
    <div class="theory-hero" style="border-color: var(--line);">
        <div class="flex items-start gap-4">
            <span class="inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-[22px] bg-ocean text-lg font-extrabold text-white">
                TH
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.copilot_theory.category_overview') }}</p>
                <h2 class="mt-2 font-display text-[2rem] font-extrabold leading-tight">{{ $page->title }}</h2>
                @if($subtitleBlock && !empty($subtitleBlock->body))
                    <div class="mt-3 prose prose-sm max-w-none leading-7" style="color: var(--muted);">
                        {!! $subtitleBlock->body !!}
                    </div>
                @endif
            </div>
        </div>

        @if(!empty($heroData['rules']))
            <div class="theory-rules mt-7 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($heroData['rules'] as $rule)
                    <div class="rounded-[20px] border px-4 py-4 surface-card-strong" style="border-color: var(--line);">
                        @if(!empty($rule['label']))
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ $rule['label'] }}</p>
                        @endif
                        <div class="mt-2 text-sm leading-6" style="color: var(--text);">{!! $rule['text'] ?? '' !!}</div>
                        @if(!empty($rule['example']))
                            <code class="theory-example mt-3 block rounded-[16px] px-3 py-2 text-xs" style="background: var(--accent-soft); color: var(--text);">{{ \App\Support\TheoryRichContent::example($rule['example']) }}</code>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($contentBlocks->isNotEmpty())
        <div class="theory-content-blocks space-y-5">
            @foreach($contentBlocks as $block)
                @include('theory.partials.content-block', ['lessonLinks' => $lessonLinks, 'presentation' => null])
            @endforeach
        </div>
    @endif
</section>
