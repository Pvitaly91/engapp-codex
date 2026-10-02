@php
    $lessonToc = $lessonToc ?? [];
    $sidebarTags = $sidebarTags ?? collect();
    $sidebarSticky = $sidebarSticky ?? false;
@endphp

<div class="{{ $sidebarSticky ? 'sticky top-24 space-y-6' : 'space-y-6' }}" data-theory-sidebar-legacy>
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
            <button type="button"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border transition hover:border-ocean surface-card"
                style="border-color: var(--line); color: var(--muted);"
                aria-label="{{ __('public.common.categories') }}"
                :aria-expanded="(!theorySidebarCollapsed).toString()"
                data-theory-sidebar-collapse @click="toggleTheorySidebar()">
                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': theorySidebarCollapsed }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>
        @include('theory.partials.desktop-navigation-loader', [
            'selectedCategory' => $selectedCategory ?? null,
            'currentPage' => $currentPage ?? null,
            'routePrefix' => $routePrefix,
        ])
        <noscript><p class="text-sm"><a href="{{ localized_route($routePrefix . '.index') }}">{{ __('public.common.categories') }}</a></p></noscript>
    </section>

    @if($lessonToc !== [])
        <div x-show="!theorySidebarCollapsed && theorySidebarSettled" x-cloak data-theory-toc-pin-root>
            <section class="rounded-[28px] border p-4 shadow-card surface-card xl:p-5" style="border-color: var(--line);" data-theory-toc-card>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.copilot_theory.contents') }}</p>
                <div class="mt-4 space-y-2" data-theory-toc-links>
                    @foreach($lessonToc as $entry)
                        <a href="#{{ $entry['id'] }}" class="flex items-start gap-3 rounded-[18px] border px-3 py-3 text-sm transition hover:-translate-y-0.5 surface-card-strong" style="border-color: var(--line); color: var(--muted);">
                            <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-amber text-[10px] font-extrabold text-white">{{ $loop->iteration }}</span>
                            <span class="min-w-0 break-words leading-5">{{ preg_replace('/^\d+\.\s*/', '', $entry['title']) }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    @endif

    @if($sidebarTags->isNotEmpty())
        <section x-show="!theorySidebarCollapsed && theorySidebarSettled" x-cloak
            class="{{ $sidebarSticky ? 'rounded-[28px] border p-5 shadow-card surface-card' : 'rounded-[28px] border p-4 shadow-card surface-card xl:p-5' }}"
            style="border-color: var(--line);" data-theory-sidebar-tags>
            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ $sidebarTagsLabel ?? __('public.common.page_tags') }}</p>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($sidebarTags as $tag)
                    <span class="rounded-full px-3 py-1.5 text-xs font-bold" style="background: var(--accent-soft); color: var(--text);">{{ \App\Support\TheoryTagLabel::display($tag->name, app()->getLocale()) }}</span>
                @endforeach
            </div>
        </section>
    @endif
</div>
