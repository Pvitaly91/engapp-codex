@extends('layouts.catalog-public')

@section('title', $page->title)
@section('body_class', 'scroll-optimized')

@section('content')
@php
    $blocks = $page->textBlocks ?? collect();
    $routePrefix = $routePrefix ?? 'theory';
    $heroBlock = $blocks->firstWhere('type', 'hero-v2') ?? $blocks->firstWhere('type', 'hero');
    $heroData = $heroBlock ? (json_decode($heroBlock->body ?? '[]', true) ?? []) : [];
    $contentBlocks = $blocks->reject(fn ($block) => in_array($block->type, ['hero', 'hero-v2', 'navigation-chips']));
    $navBlock = $blocks->firstWhere('type', 'navigation-chips');
    $categoryPages = $categoryPages ?? collect();
    $tocBlocks = $contentBlocks->filter(fn ($block) => !empty(json_decode($block->body ?? '[]', true)['title'] ?? ''));
    $practiceQuestionsByBlock = $practiceQuestionsByBlock ?? [];
    $pageTags = $page->tags ?? collect();

    if (app()->getLocale() !== 'uk') {
        $pageTags = $pageTags
            ->reject(fn ($tag) => preg_match('/\p{Cyrillic}/u', (string) ($tag->name ?? '')) === 1)
            ->values();
    }
@endphp

<div class="nd-page">
    <nav class="mb-8 flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);" aria-label="{{ __('public.common.breadcrumb') }}">
        <a href="{{ localized_route('home') }}" class="transition hover:text-ocean">{{ __('public.common.home') }}</a>
        <span>/</span>
        <a href="{{ localized_route($routePrefix . '.index') }}" class="transition hover:text-ocean">{{ $sectionTitle ?? __('frontend.copilot_theory.theory') }}</a>
        @if(isset($selectedCategory))
            <span>/</span>
            <a href="{{ localized_route($routePrefix . '.category', $selectedCategory->slug) }}" class="transition hover:text-ocean">{{ $selectedCategory->title }}</a>
        @endif
        <span>/</span>
        <span style="color: var(--text);">{{ $page->title }}</span>
    </nav>

    <section class="relative overflow-hidden rounded-[30px] border p-7 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="absolute -right-10 top-0 hidden h-36 w-36 rounded-full border-[18px] border-ocean/30 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-44 w-14 rounded-tl-[2rem] bg-ocean lg:block"></div>
        <div class="relative">
            @if(!empty($heroData['level']))
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    {{ __('theory_blocks.hero.level', ['level' => $heroData['level']]) }}
                </span>
            @endif
            <h1 class="mt-4 max-w-4xl font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">{{ $page->title }}</h1>
            @if(!empty($heroData['intro']))
                <div class="mt-5 max-w-3xl text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    {!! $heroData['intro'] !!}
                </div>
            @endif

            @if(!empty($heroData['rules']))
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach($heroData['rules'] as $rule)
                        <span class="rounded-full px-3 py-2 text-xs font-bold" style="background: var(--accent-soft); color: var(--text);">
                            {{ $rule['label'] ?? '' }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @include('theory.partials.mobile-navigation', [
        'categories' => $categories,
        'selectedCategory' => $selectedCategory ?? null,
        'categoryPages' => $categoryPages,
        'currentPage' => $page,
        'routePrefix' => $routePrefix,
    ])

    <div
        x-data="{
            theorySidebarCollapsed: localStorage.getItem('theorySidebarCollapsed') === 'true',
            theorySidebarSettled: true,
            toggleTheorySidebar() {
                const main = this.$root.querySelector('[data-theory-main]');
                const before = main ? main.getBoundingClientRect() : null;

                this.theorySidebarSettled = false;
                this.theorySidebarCollapsed = !this.theorySidebarCollapsed;

                this.$nextTick(() => {
                    window.animateTheoryMainFlip && window.animateTheoryMainFlip(main, before);
                    window.setTimeout(() => {
                        this.theorySidebarSettled = true;
                        this.$nextTick(() => window.initTheorySidebarAutoscroll && window.initTheorySidebarAutoscroll());
                    }, 220);
                });
            },
        }"
        x-effect="localStorage.setItem('theorySidebarCollapsed', theorySidebarCollapsed ? 'true' : 'false')"
        class="mt-8 grid gap-6 lg:flex lg:items-start"
        :data-collapsed="theorySidebarCollapsed.toString()"
        :data-settled="theorySidebarSettled.toString()"
        data-theory-layout
    >
        <aside class="hidden shrink-0 overflow-hidden lg:block" data-theory-aside>
            <div class="sticky top-24 space-y-6">
                <section
                    class="flex max-h-[calc(100vh-7rem)] flex-col rounded-[28px] border p-4 shadow-card surface-card-strong xl:p-5"
                    :data-collapsed="theorySidebarCollapsed.toString()"
                    data-theory-sidebar
                    style="border-color: var(--line);"
                >
                    <div class="shrink-0 flex items-end justify-between gap-3">
                        <div class="theory-sidebar-expanded-only">
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.copilot_theory.map') }}</p>
                            <h2 class="mt-2 font-display text-xl font-extrabold leading-none">{{ __('public.common.categories') }}</h2>
                        </div>
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border transition hover:border-ocean surface-card"
                            style="border-color: var(--line); color: var(--muted);"
                            aria-label="{{ __('public.common.categories') }}"
                            @click="toggleTheorySidebar()"
                        >
                            <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': theorySidebarCollapsed }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-4 min-h-0 flex-1 overflow-y-auto overscroll-contain pr-1 space-y-3" data-theory-sidebar-scroll style="scrollbar-color: color-mix(in srgb, var(--accent) 34%, transparent) transparent;">
                        @include('theory.partials.tree-nav', [
                            'categories' => $categories,
                            'selectedCategory' => $selectedCategory ?? null,
                            'currentPage' => $page,
                            'routePrefix' => $routePrefix,
                        ])
                    </div>
                </section>

                @if($tocBlocks->isNotEmpty())
                    <section x-show="!theorySidebarCollapsed && theorySidebarSettled" x-cloak class="rounded-[28px] border p-4 shadow-card surface-card xl:p-5" style="border-color: var(--line);">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.copilot_theory.contents') }}</p>
                        <div class="mt-4 space-y-2">
                            @foreach($tocBlocks as $tocBlock)
                                @php($tocData = json_decode($tocBlock->body ?? '[]', true))
                                @if(!empty($tocData['title']))
                                    <a href="#block-{{ $tocBlock->id }}" class="flex items-start gap-3 rounded-[18px] border px-3 py-3 text-sm transition hover:-translate-y-0.5 surface-card-strong" style="border-color: var(--line); color: var(--muted);">
                                        <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-amber text-[10px] font-extrabold text-white">{{ $loop->iteration }}</span>
                                        <span class="min-w-0 break-words leading-5">{{ preg_replace('/^\d+\.\s*/', '', $tocData['title']) }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($pageTags->isNotEmpty())
                    <section x-show="!theorySidebarCollapsed && theorySidebarSettled" x-cloak class="rounded-[28px] border p-4 shadow-card surface-card xl:p-5" style="border-color: var(--line);">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.page_tags') }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($pageTags as $tag)
                                <span class="rounded-full px-3 py-1.5 text-xs font-bold" style="background: var(--accent-soft); color: var(--text);">{{ \App\Support\TheoryTagLabel::display($tag->name, app()->getLocale()) }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </aside>

        <div class="min-w-0 flex-1 space-y-8" data-theory-main>
            @if(!empty($heroData['rules']))
                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($heroData['rules'] as $rule)
                        <article class="rounded-[24px] border p-5 shadow-card surface-card-strong" style="border-color: var(--line);">
                            @if(!empty($rule['label']))
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ $rule['label'] }}</p>
                            @endif
                            <div class="mt-3 text-sm leading-6" style="color: var(--text);">{!! $rule['text'] ?? '' !!}</div>
                            @if(!empty($rule['example']))
                                <code class="mt-4 block rounded-[16px] px-3 py-2 text-xs" style="background: var(--accent-soft); color: var(--text);">{{ $rule['example'] }}</code>
                            @endif
                        </article>
                    @endforeach
                </section>
            @endif

            <section class="rounded-[30px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
                <div class="space-y-6">
                    @foreach($contentBlocks as $block)
                        <div id="block-{{ $block->id }}" class="theory-lazy-section">
                            @if(in_array($block->type, ['forms-grid', 'lesson-rule-cards', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list', 'practice-set', 'tense-forms-table']))
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
                                        <a href="{{ $item['url'] ?? '#' }}" class="rounded-[16px] border px-4 py-2 text-sm font-bold transition hover:-translate-y-0.5 surface-card-strong" style="border-color: var(--line); color: var(--muted);">
                                            {{ $item['label'] ?? '' }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </nav>
                    @endif
                @endif
            </section>

            @if(isset($topicTests) && $topicTests->isNotEmpty())
                <section class="theory-lazy-section rounded-[30px] border p-6 shadow-card surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.tests_on_topic') }}</p>
                    <h2 class="mt-2 font-display text-2xl font-extrabold">{{ __('public.common.tests_on_topic') }}</h2>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach($topicTests as $test)
                            <x-auto-generated-test-card :test="$test" />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>
@endsection

