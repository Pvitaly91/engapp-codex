@php
    $lessonToc = $lessonToc ?? [];
    $sidebarTags = $sidebarTags ?? collect();
    $hasLessonContents = $lessonToc !== [];
@endphp

<div
    class="theory-sidebar-shell"
    data-theory-sidebar-shell
    data-theory-sidebar-layout="{{ $hasLessonContents ? 'stacked' : 'topics' }}"
>
    <section class="theory-sidebar-card theory-sidebar-menu-card surface-card-strong"
        data-theory-sidebar-card="topics" aria-labelledby="theory-sidebar-topics-title">
        <div class="theory-sidebar-toolbar">
            <h2 id="theory-sidebar-topics-title" class="theory-sidebar-heading theory-sidebar-expanded-only">{{ __('public.common.categories') }}</h2>
            <button type="button" class="theory-sidebar-collapse" data-theory-sidebar-collapse @click="toggleTheorySidebar()"
                :aria-expanded="(!theorySidebarCollapsed).toString()" aria-label="{{ __('public.common.categories') }}">
                <svg class="h-4 w-4" :class="{ 'rotate-180': theorySidebarCollapsed }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <div id="theory-sidebar-topics-panel" class="theory-sidebar-topics-panel" data-theory-sidebar-panel="topics"
            data-theory-sidebar :data-collapsed="theorySidebarCollapsed.toString()">
            @include('theory.partials.desktop-navigation-loader', [
                'selectedCategory' => $selectedCategory ?? null,
                'currentPage' => $currentPage ?? null,
                'routePrefix' => $routePrefix,
            ])
        </div>

        @if(!$hasLessonContents && $sidebarTags->isNotEmpty())
            <details class="theory-sidebar-tags theory-sidebar-expanded-only">
                <summary>{{ $sidebarTagsLabel ?? __('public.common.page_tags') }}</summary>
                <div class="flex flex-wrap gap-2">
                    @foreach($sidebarTags as $tag)
                        <span class="rounded-full px-2 py-1 text-xs" style="background: var(--accent-soft); color: var(--text);">{{ \App\Support\TheoryTagLabel::display($tag->name, app()->getLocale()) }}</span>
                    @endforeach
                </div>
            </details>
        @endif
        <noscript><p class="text-sm"><a href="{{ localized_route($routePrefix . '.index') }}">{{ __('public.common.categories') }}</a></p></noscript>
    </section>

    @if($hasLessonContents)
        <section class="theory-sidebar-card theory-sidebar-contents-card surface-card-strong"
            data-theory-sidebar-card="lesson" aria-labelledby="theory-sidebar-lesson-title"
            x-show="!theorySidebarCollapsed">
            <h2 id="theory-sidebar-lesson-title" class="theory-sidebar-heading">{{ __('theory_blocks.section.contents') }}</h2>
            <div id="theory-sidebar-lesson-panel" class="theory-sidebar-lesson-panel" data-theory-sidebar-panel="lesson" tabindex="0">
                @include('theory.partials.lesson-toc')
                @if($sidebarTags->isNotEmpty())
                    <details class="theory-sidebar-tags">
                        <summary>{{ $sidebarTagsLabel ?? __('public.common.page_tags') }}</summary>
                        <div class="flex flex-wrap gap-2">
                            @foreach($sidebarTags as $tag)
                                <span class="rounded-full px-2 py-1 text-xs" style="background: var(--accent-soft); color: var(--text);">{{ \App\Support\TheoryTagLabel::display($tag->name, app()->getLocale()) }}</span>
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>
        </section>
    @endif
</div>
