@extends('layouts.catalog-public')

@php
    $seoHeroBlock = $page->textBlocks->firstWhere('type', 'hero-v2') ?? $page->textBlocks->firstWhere('type', 'hero');
    $seoHeroData = $seoHeroBlock ? (json_decode($seoHeroBlock->body ?? '[]', true) ?? []) : [];
    $seoIntro = is_string($seoHeroData['intro'] ?? null) ? $seoHeroData['intro'] : '';
    $seoPlainIntro = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($seoIntro), ENT_QUOTES | ENT_HTML5)));
    $seoDescription = $seoPlainIntro !== ''
        ? \Illuminate\Support\Str::limit(__('public.theory.seo.page_intro_description', [
            'page' => $page->title,
            'category' => $selectedCategory->title ?? __('public.theory.title'),
            'intro' => $seoPlainIntro,
        ]), 159, '…')
        : __('public.theory.seo.page_description', [
            'page' => $page->title,
            'category' => $selectedCategory->title ?? __('public.theory.title'),
        ]);
    $seoTitle = (string) $page->title . ' | Gramlyze';
    if (app()->getLocale() === 'uk') {
        // The display title may be shortened/localized in memory by the controller; keep the full stored identity for UK SEO only.
        $seo = \App\Support\PageMetadata::theory(
            (string) ($page->getRawOriginal('title') ?: $page->title),
            (string) ($selectedCategory->title ?? ''),
            $seoIntro,
            $page->type === 'theory' ? (string) $page->getRawOriginal('seeder') : '',
            app()->getLocale()
        );
        $seoTitle = $seo['title'];
        $seoDescription = $seo['description'];
    }
@endphp
@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('body_class', 'scroll-optimized')

@section('content')
@php
    $blocks = $page->textBlocks ?? collect();
    $m45StyleContext = \App\Support\M45FutureComparisonsPackage::allowsTheoryContext($blocks, app()->getLocale());
    $blocks = \App\Support\M43AuthoredTenseUsagePackage::orderBlocks($blocks);
    $m44StyleContext = \App\Support\M44AuthoredFutureFormsPackage::allowsTheoryContext($blocks, app()->getLocale());
    $blocks = \App\Support\M44AuthoredFutureFormsPackage::orderBlocks($blocks);
    $routePrefix = $routePrefix ?? 'theory';
    $heroBlock = $blocks->firstWhere('type', 'hero-v2') ?? $blocks->firstWhere('type', 'hero');
    $heroData = $heroBlock ? (json_decode($heroBlock->body ?? '[]', true) ?? []) : [];
    $contentBlocks = $blocks->reject(fn ($block) => in_array($block->type, ['hero', 'hero-v2', 'navigation-chips']));
    $presentationByBlock = $contentBlocks->mapWithKeys(fn ($block) => [
        $block->id => ($block->type === 'box' || empty($block->type)) ? \App\Support\TheoryPresentation::html($block, canonical: true) : null,
    ]);
    $navBlock = $blocks->firstWhere('type', 'navigation-chips');
    $categoryPages = $categoryPages ?? collect();
    $lessonToc = [];
    foreach ($contentBlocks as $tocBlock) {
        $tocData = \App\Support\TheoryPresentation::data($tocBlock->body);
        if (!empty($tocData['title']) && is_string($tocData['title'])) {
            $lessonToc[] = ['id' => 'block-' . $tocBlock->id, 'title' => $tocData['title']];
        } elseif (!empty($presentationByBlock[$tocBlock->id]['toc'])) {
            $lessonToc = array_merge($lessonToc, $presentationByBlock[$tocBlock->id]['toc']);
        } elseif (!empty($tocBlock->heading)) {
            $lessonToc[] = ['id' => 'block-' . $tocBlock->id, 'title' => $tocBlock->heading];
        }
    }
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
        <aside class="relative hidden shrink-0 overflow-visible lg:block lg:self-stretch" data-theory-aside>
            @include('theory.partials.desktop-sidebar', [
                'currentPage' => $page,
                'sidebarTags' => $pageTags,
            ])
        </aside>

        <div class="min-w-0 flex-1 space-y-8 theory-design" data-theory-main>
            <section class="theory-hero" style="border-color: var(--line);">
                <div class="relative">
                    @if(!empty($heroData['level']))
                        <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                            {{ __('theory_blocks.hero.level', ['level' => $heroData['level']]) }}
                        </span>
                    @endif
                    <h1 class="mt-4 max-w-4xl font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">{{ $page->title }}</h1>
                    @if($m45StyleContext)
                        <p lang="uk" data-m45-subtitle class="mt-4 max-w-3xl text-sm leading-7 sm:text-base text-muted-foreground">{{ $page->getRawOriginal('text') }}</p>
                    @endif
                    @if(!empty($heroData['intro']))
                        <div class="mt-5 max-w-3xl text-sm leading-7 sm:text-base" style="color: var(--muted);">
                            {!! $heroData['intro'] !!}
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

            @if($lessonToc !== [])
                <details class="theory-mobile-toc lg:hidden" data-theory-ui>
                    <summary>{{ __('theory_blocks.section.contents') }}</summary>
                    @include('theory.partials.lesson-toc')
                </details>
            @endif

            @if(!empty($heroData['rules']))
                <section class="theory-rules grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($heroData['rules'] as $rule)
                        <article class="rounded-[24px] border p-5 shadow-card surface-card-strong" style="border-color: var(--line);">
                            @if(!empty($rule['label']))
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ $rule['label'] }}</p>
                            @endif
                            <div class="mt-3 text-sm leading-6" style="color: var(--text);">{!! $rule['text'] ?? '' !!}</div>
                            @if(!empty($rule['example']))
                                <code class="theory-example mt-4 block rounded-[16px] px-3 py-2 text-xs" style="background: var(--accent-soft); color: var(--text);">{{ \App\Support\TheoryRichContent::example($rule['example']) }}</code>
                            @endif
                        </article>
                    @endforeach
                </section>
            @endif

            <section class="theory-content-blocks">
                <div class="space-y-6">
                    @foreach($contentBlocks as $block)
                        @include('theory.partials.content-block', [
                            'theoryCanonical' => true,
                            'm42StyleContext' => app()->getLocale() === 'uk',
                            'm43StyleContext' => app()->getLocale() === 'uk',
                            'm44StyleContext' => $m44StyleContext,
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

