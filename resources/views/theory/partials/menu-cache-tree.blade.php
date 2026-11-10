@foreach($categories as $category)
    @php
        $mobile = $variant === 'mobile';
        $orderedItems = $category->ordered_tree_items ?? collect();
        $itemCount = $orderedItems->count() ?: ($category->pages_count ?? 0);
        $indent = $level * 16;
        $categoryUrl = localized_route($routePrefix . '.category', $category->slug, false);
    @endphp
    <div class="{{ $mobile || $level === 0 ? 'space-y-2.5' : 'space-y-2' }}" data-theory-menu-category="{{ $category->getKey() }}" data-theory-sidebar-node @if($level === 0) data-theory-sidebar-top-node @endif>
        @if($mobile)
            <div class="flex items-center gap-2.5" style="padding-left: {{ $indent }}px;">
                @if($orderedItems->isNotEmpty())
                    <button type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-2xl border surface-card" data-theory-menu-toggle aria-expanded="false" aria-label="{{ $category->title }}">
                        <svg class="h-4 w-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>
                @else
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-2xl border surface-card" style="border-color: var(--line); color: var(--muted);">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full" data-theory-menu-category-dot></span>
                    </span>
                @endif
                <a href="{{ $categoryUrl }}" class="flex-1 rounded-[22px] border px-3.5 py-3 transition surface-card-strong" data-theory-menu-category-link>
                    <span class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[16px] border" data-theory-menu-icon>
                            @include('theory.partials.category-icon', ['active' => false])
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-3">
                                <span class="block text-sm font-extrabold leading-5" data-theory-sidebar-highlight>{{ $category->title }}</span>
                                <span class="inline-flex min-w-[2rem] items-center justify-center rounded-full px-2 py-1 text-[10px] font-extrabold" data-theory-menu-count>{{ $itemCount }}</span>
                            </span>
                        </span>
                    </span>
                </a>
            </div>
        @else
            <div class="min-w-0 flex-1">
                <div class="relative">
                    <a href="{{ $categoryUrl }}" class="theory-nav-link group block rounded-[22px] border px-3.5 py-3.5 pr-12 transition hover:-translate-y-0.5 surface-card-strong" data-theory-menu-category-link>
                        <div class="flex items-center gap-2.5">
                            <span class="theory-nav-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[16px] border" data-theory-menu-icon>
                                @include('theory.partials.category-icon', ['active' => false])
                            </span>
                            <span class="theory-nav-label min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-3">
                                    <span class="block text-sm font-extrabold leading-6" data-theory-sidebar-highlight>{{ $category->title }}</span>
                                    <span class="theory-nav-count inline-flex min-w-[2rem] items-center justify-center rounded-full px-2 py-1 text-[10px] font-extrabold" data-theory-menu-count>{{ $itemCount }}</span>
                                </span>
                            </span>
                        </div>
                    </a>
                    @if($orderedItems->isNotEmpty())
                        <button type="button" class="theory-nav-toggle absolute right-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-2xl border transition hover:border-ocean surface-card" data-theory-menu-toggle aria-expanded="false" aria-label="{{ $category->title }}">
                            <svg class="h-4 w-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    @endif
                </div>
                @if($orderedItems->isNotEmpty())
                    @include('theory.partials.menu-cache-children')
                @endif
            </div>
        @endif
        @if($mobile && $orderedItems->isNotEmpty())
            @include('theory.partials.menu-cache-children')
        @endif
    </div>
@endforeach
