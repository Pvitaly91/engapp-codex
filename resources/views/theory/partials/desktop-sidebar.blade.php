@php
    $lessonToc = $lessonToc ?? [];
    $sidebarTags = $sidebarTags ?? collect();
    $hasLessonContents = $lessonToc !== [];
@endphp

<section
    class="theory-sidebar-shell surface-card-strong"
    data-theory-sidebar-shell
    data-theory-sidebar-default="{{ $hasLessonContents ? 'lesson' : 'topics' }}"
    x-data="{
        pane: @js($hasLessonContents ? 'lesson' : 'topics'),
        selectPane(value) {
            this.pane = value;
            this.$nextTick(() => {
                if (value === 'topics') window.initTheorySidebarAutoscroll?.();
            });
        },
        focusPane(value) {
            this.selectPane(value);
            this.$nextTick(() => this.$refs[value + 'Tab']?.focus());
        },
    }"
>
    <div class="theory-sidebar-toolbar">
        @if($hasLessonContents)
            <div class="theory-sidebar-tabs theory-sidebar-expanded-only" role="tablist" aria-label="{{ __('theory_blocks.section.contents') }}">
                <button type="button" id="theory-sidebar-lesson-tab" x-ref="lessonTab" role="tab"
                    data-theory-sidebar-tab="lesson" aria-controls="theory-sidebar-lesson-panel"
                    aria-selected="true" tabindex="0" :aria-selected="(pane === 'lesson').toString()" :tabindex="pane === 'lesson' ? 0 : -1"
                    @click="selectPane('lesson')" @keydown.arrow-right.prevent="focusPane('topics')"
                    @keydown.arrow-left.prevent="focusPane('topics')" @keydown.home.prevent="focusPane('lesson')"
                    @keydown.end.prevent="focusPane('topics')">
                    {{ __('theory_blocks.section.contents') }}
                </button>
                <button type="button" id="theory-sidebar-topics-tab" x-ref="topicsTab" role="tab"
                    data-theory-sidebar-tab="topics" aria-controls="theory-sidebar-topics-panel"
                    aria-selected="false" tabindex="-1" :aria-selected="(pane === 'topics').toString()" :tabindex="pane === 'topics' ? 0 : -1"
                    @click="selectPane('topics')" @keydown.arrow-right.prevent="focusPane('lesson')"
                    @keydown.arrow-left.prevent="focusPane('lesson')" @keydown.home.prevent="focusPane('lesson')"
                    @keydown.end.prevent="focusPane('topics')">
                    {{ __('public.common.categories') }}
                </button>
            </div>
        @else
            <h2 class="theory-sidebar-expanded-only font-bold">{{ __('public.common.categories') }}</h2>
        @endif
        <button type="button" class="theory-sidebar-collapse" data-theory-sidebar-collapse @click="toggleTheorySidebar()"
            :aria-expanded="(!theorySidebarCollapsed).toString()" aria-label="{{ __('public.common.categories') }}">
            <svg class="h-4 w-4" :class="{ 'rotate-180': theorySidebarCollapsed }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
    </div>

    @if($hasLessonContents)
        <div id="theory-sidebar-lesson-panel" role="tabpanel" aria-labelledby="theory-sidebar-lesson-tab"
            class="theory-sidebar-lesson-panel" data-theory-sidebar-panel="lesson" tabindex="0"
            x-show="pane === 'lesson' && !theorySidebarCollapsed">
            @include('theory.partials.lesson-toc')
        </div>
    @endif

    <div id="theory-sidebar-topics-panel" @if($hasLessonContents) role="tabpanel" aria-labelledby="theory-sidebar-topics-tab" @endif
        class="theory-sidebar-topics-panel" data-theory-sidebar-panel="topics"
        data-theory-sidebar :data-collapsed="theorySidebarCollapsed.toString()"
        x-show="pane === 'topics' || theorySidebarCollapsed" x-cloak>
        @include('theory.partials.desktop-navigation-loader', [
            'selectedCategory' => $selectedCategory ?? null,
            'currentPage' => $currentPage ?? null,
            'routePrefix' => $routePrefix,
        ])
    </div>

    @if($sidebarTags->isNotEmpty())
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
