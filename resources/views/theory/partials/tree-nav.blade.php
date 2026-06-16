@php
    $categories = $categories ?? collect();
    $selectedCategory = $selectedCategory ?? null;
    $currentPage = $currentPage ?? null;
    $routePrefix = $routePrefix ?? 'theory';
    $level = $level ?? 0;
@endphp

@once
    <style>
        @media (min-width: 1024px) {
            [data-theory-aside] {
                contain: layout paint;
                inline-size: 390px;
            }

            [data-theory-layout][data-collapsed="true"] [data-theory-aside] {
                inline-size: 116px;
            }
        }

        @media (min-width: 1280px) {
            [data-theory-aside] {
                inline-size: 410px;
            }

            [data-theory-layout][data-collapsed="true"] [data-theory-aside] {
                inline-size: 116px;
            }
        }

        [data-theory-sidebar] {
            transform: translateZ(0);
            overflow: hidden;
        }

        [data-theory-main] {
            will-change: transform;
        }

        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-sidebar-expanded-only,
        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-label,
        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-count,
        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-toggle,
        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-children,
        [data-theory-sidebar][data-collapsed="true"] .theory-sidebar-expanded-only,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-label,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-count,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-toggle,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-children {
            display: none !important;
        }

        [data-theory-sidebar][data-theory-sidebar-searching="true"] .theory-nav-children {
            display: block !important;
        }

        [data-theory-layout][data-settled="false"] [data-theory-sidebar] [data-theory-sidebar-scroll],
        [data-theory-sidebar][data-collapsed="true"] [data-theory-sidebar-scroll] {
            padding-left: 3px;
            padding-right: 8px;
            scrollbar-gutter: stable;
        }

        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-link,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-link {
            display: flex;
            justify-content: center;
            border-radius: 18px;
            padding: 10px !important;
        }

        [data-theory-layout][data-settled="false"] [data-theory-sidebar] .theory-nav-icon,
        [data-theory-sidebar][data-collapsed="true"] .theory-nav-icon {
            height: 44px;
            width: 44px;
        }
    </style>

    <script>
        window.scrollTheorySidebarToActive = () => {
            document.querySelectorAll('[data-theory-sidebar-scroll]').forEach((container) => {
                const active = container.querySelector('[data-theory-nav-active="true"]');

                if (!active) {
                    return;
                }

                const containerRect = container.getBoundingClientRect();
                const activeRect = active.getBoundingClientRect();
                const targetTop = container.scrollTop + activeRect.top - containerRect.top - 8;

                container.scrollTo({
                    top: Math.max(0, targetTop),
                    behavior: 'auto',
                });
            });
        };

        window.initTheorySidebarAutoscroll = () => {
            window.scrollTheorySidebarToActive();
            window.requestAnimationFrame(() => window.scrollTheorySidebarToActive());
            window.setTimeout(() => window.scrollTheorySidebarToActive(), 150);
            window.setTimeout(() => window.scrollTheorySidebarToActive(), 450);
        };

        document.addEventListener('DOMContentLoaded', window.initTheorySidebarAutoscroll);
        window.addEventListener('load', window.initTheorySidebarAutoscroll);
        document.addEventListener('alpine:initialized', window.initTheorySidebarAutoscroll);

        window.animateTheoryMainFlip = (main, before) => {
            if (!main || !before) {
                return;
            }

            const after = main.getBoundingClientRect();
            const deltaX = before.left - after.left;

            if (Math.abs(deltaX) < 1) {
                return;
            }

            main.style.transition = 'none';
            main.style.transform = `translate3d(${deltaX}px, 0, 0)`;
            main.getBoundingClientRect();

            window.requestAnimationFrame(() => {
                main.style.transition = 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1)';
                main.style.transform = 'translate3d(0, 0, 0)';

                window.setTimeout(() => {
                    main.style.transition = '';
                    main.style.transform = '';
                }, 240);
            });
        };

        window.initTheorySidebarSearch = () => {
            document.querySelectorAll('[data-theory-sidebar-search]').forEach((searchRoot) => {
                if (searchRoot.dataset.theorySearchReady === 'true') {
                    return;
                }

                searchRoot.dataset.theorySearchReady = 'true';

                const sidebar = searchRoot.closest('[data-theory-sidebar]');
                const scrollRoot = sidebar?.querySelector('[data-theory-sidebar-scroll]');
                const input = searchRoot.querySelector('[data-theory-sidebar-search-input]');
                const clearButton = searchRoot.querySelector('[data-theory-sidebar-search-clear]');
                const count = searchRoot.querySelector('[data-theory-sidebar-search-count]');
                const empty = searchRoot.querySelector('[data-theory-sidebar-search-empty]');
                const topNodes = Array.from(scrollRoot?.querySelectorAll('[data-theory-sidebar-top-node]') || []);
                const allNodes = Array.from(scrollRoot?.querySelectorAll('[data-theory-sidebar-node]') || []);
                const total = topNodes.length;

                if (!input || !sidebar || !scrollRoot || topNodes.length === 0) {
                    return;
                }

                const normalize = (value) => String(value || '').toLocaleLowerCase().trim();

                const clearHighlight = (element) => {
                    element.textContent = element.dataset.theorySidebarOriginalText || element.textContent;
                };

                const highlightText = (element, query) => {
                    const original = element.dataset.theorySidebarOriginalText || element.textContent;
                    element.dataset.theorySidebarOriginalText = original;
                    element.textContent = '';

                    if (!query) {
                        element.textContent = original;
                        return;
                    }

                    const lowerOriginal = original.toLocaleLowerCase();
                    const lowerQuery = query.toLocaleLowerCase();
                    let cursor = 0;
                    let matchIndex = lowerOriginal.indexOf(lowerQuery);

                    if (matchIndex === -1) {
                        element.textContent = original;
                        return;
                    }

                    while (matchIndex !== -1) {
                        if (matchIndex > cursor) {
                            element.append(document.createTextNode(original.slice(cursor, matchIndex)));
                        }

                        const mark = document.createElement('mark');
                        mark.className = 'rounded-md bg-amber/30 px-1 py-0.5 text-inherit';
                        mark.textContent = original.slice(matchIndex, matchIndex + query.length);
                        element.append(mark);

                        cursor = matchIndex + query.length;
                        matchIndex = lowerOriginal.indexOf(lowerQuery, cursor);
                    }

                    if (cursor < original.length) {
                        element.append(document.createTextNode(original.slice(cursor)));
                    }
                };

                const clearSearch = () => {
                    input.value = '';
                    applySearch();
                };

                const applySearch = () => {
                    const query = normalize(input.value);
                    let visible = 0;

                    sidebar.dataset.theorySidebarSearching = query ? 'true' : 'false';

                    allNodes.forEach((node) => {
                        const matched = !query || normalize(node.textContent).includes(query);
                        node.classList.toggle('hidden', !matched);
                    });

                    topNodes.forEach((node) => {
                        if (!node.classList.contains('hidden')) {
                            visible += 1;
                        }
                    });

                    scrollRoot.querySelectorAll('[data-theory-sidebar-highlight]').forEach((element) => {
                        if (query && normalize(element.textContent).includes(query)) {
                            highlightText(element, query);
                        } else {
                            clearHighlight(element);
                        }
                    });

                    if (count) {
                        count.textContent = `${visible} / ${total}`;
                    }

                    clearButton?.classList.toggle('hidden', !query);
                    empty?.classList.toggle('hidden', visible !== 0);
                };

                input.addEventListener('input', applySearch);
                clearButton?.addEventListener('click', () => {
                    clearSearch();
                    input.focus();
                });

                const observer = new MutationObserver(() => {
                    if (sidebar.dataset.collapsed === 'true' && input.value) {
                        clearSearch();
                    }
                });

                observer.observe(sidebar, { attributes: true, attributeFilter: ['data-collapsed'] });

                applySearch();
            });
        };

        document.addEventListener('DOMContentLoaded', window.initTheorySidebarSearch);
        document.addEventListener('alpine:initialized', window.initTheorySidebarSearch);
    </script>
@endonce

@foreach($categories as $category)
    @php
        $isActive = $selectedCategory && $selectedCategory->is($category);
        $hasSelectedDescendant = $category->hasChildren() && $category->hasDescendant($selectedCategory);
        $orderedItems = $category->ordered_tree_items ?? collect();
        $isExpanded = $isActive || $hasSelectedDescendant;
        $isBranchActive = $isActive || $hasSelectedDescendant;
        $itemCount = $orderedItems->count() ?: ($category->pages_count ?? 0);
    @endphp

    <div
        x-data="{ expanded: {{ $isExpanded ? 'true' : 'false' }} }"
        class="{{ $level === 0 ? 'space-y-2.5' : 'space-y-2' }}"
        data-theory-sidebar-node
        @if($level === 0) data-theory-sidebar-top-node @endif
    >
        <div class="min-w-0 flex-1">
            <div class="relative">
                <a
                    href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                    class="theory-nav-link group block rounded-[22px] border px-3.5 py-3.5 pr-12 transition hover:-translate-y-0.5 surface-card-strong"
                    @if($isActive) data-theory-nav-active="true" @endif
                    style="{{ $isActive
                        ? 'border-color: var(--accent); background: linear-gradient(180deg, color-mix(in srgb, var(--accent-soft) 88%, white) 0%, white 100%); box-shadow: inset 4px 0 0 var(--accent); color: var(--text);'
                        : ($hasSelectedDescendant
                            ? 'border-color: color-mix(in srgb, var(--accent) 40%, var(--line)); background: color-mix(in srgb, var(--accent-soft) 48%, white); color: var(--text);'
                            : 'border-color: var(--line); color: var(--text);') }}"
                >
                    <div class="flex items-center gap-2.5">
                        <span
                            class="theory-nav-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[16px] border"
                            style="{{ $isBranchActive
                                ? 'border-color: transparent; background: linear-gradient(135deg, var(--accent) 0%, color-mix(in srgb, var(--accent) 76%, white) 100%);'
                                : 'border-color: color-mix(in srgb, var(--accent) 16%, var(--line)); background: color-mix(in srgb, var(--accent-soft) 38%, white);' }}"
                        >
                            @include('theory.partials.category-icon', [
                                'category' => $category,
                                'active' => $isBranchActive,
                            ])
                        </span>

                        <span class="theory-nav-label min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-3">
                                <span class="block text-sm font-extrabold leading-6" data-theory-sidebar-highlight>{{ $category->title }}</span>
                                <span class="theory-nav-count inline-flex min-w-[2rem] items-center justify-center rounded-full px-2 py-1 text-[10px] font-extrabold" style="background: {{ $isBranchActive ? 'rgba(47, 103, 177, 0.12)' : 'rgba(91, 108, 128, 0.10)' }}; color: {{ $isBranchActive ? 'var(--accent)' : 'var(--muted)' }};">
                                    {{ $itemCount }}
                                </span>
                            </span>
                        </span>
                    </div>
                </a>

                @if($orderedItems->isNotEmpty())
                    <button
                        type="button"
                        @click.stop.prevent="expanded = !expanded"
                        class="theory-nav-toggle absolute right-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-2xl border transition hover:border-ocean surface-card"
                        style="border-color: {{ $isBranchActive ? 'color-mix(in srgb, var(--accent) 35%, var(--line))' : 'var(--line)' }}; color: {{ $isBranchActive ? 'var(--accent)' : 'var(--muted)' }};"
                        :aria-expanded="expanded.toString()"
                    >
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-90': expanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @endif
            </div>

            @if($orderedItems->isNotEmpty())
                <div x-show="expanded" x-transition x-cloak class="theory-nav-children ml-5 mt-2.5 space-y-2.5 border-l pl-3" style="border-color: color-mix(in srgb, var(--line) 88%, transparent);">
                    @foreach($orderedItems as $item)
                        @if($item['type'] === 'category')
                            @include('theory.partials.tree-nav', [
                                'categories' => collect([$item['model']]),
                                'selectedCategory' => $selectedCategory,
                                'currentPage' => $currentPage,
                                'routePrefix' => $routePrefix,
                                'level' => $level + 1,
                            ])
                        @else
                            @php($pageItem = $item['model'])
                            @php($isCurrentPage = $currentPage && $currentPage->is($pageItem))
                            <a
                                href="{{ localized_route($routePrefix . '.show', [$category->slug, $pageItem->slug]) }}"
                                class="group relative flex items-start gap-2.5 rounded-[16px] px-2.5 py-2.5 text-sm transition hover:bg-slate-100/80 dark:hover:bg-slate-800/40"
                                style="{{ $isCurrentPage
                                    ? 'background: color-mix(in srgb, var(--accent-soft) 78%, white); color: var(--text);'
                                    : 'color: var(--muted);' }}"
                                @if($isCurrentPage) aria-current="page" @endif
                                data-theory-sidebar-node
                            >
                                <span class="mt-1.5 inline-flex h-3 w-3 shrink-0 rounded-full border-2" style="border-color: {{ $isCurrentPage ? 'var(--accent)' : 'color-mix(in srgb, var(--line) 88%, var(--muted))' }}; background: {{ $isCurrentPage ? 'var(--accent)' : 'transparent' }};"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold leading-5" data-theory-sidebar-highlight>{{ $pageItem->title }}</span>
                                </span>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endforeach
