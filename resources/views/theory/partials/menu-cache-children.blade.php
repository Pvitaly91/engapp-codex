<div hidden data-theory-menu-children class="theory-nav-children {{ $mobile ? '' : 'ml-5 mt-2.5' }} space-y-2.5 border-l pl-3" style="border-color: color-mix(in srgb, var(--line) 88%, transparent); @if($mobile) margin-left: {{ $indent + 18 }}px; @endif">
    @foreach($orderedItems as $item)
        @if($item['type'] === 'category')
            @include('theory.partials.menu-cache-tree', ['categories' => collect([$item['model']]), 'level' => $level + 1])
        @else
            @php($pageItem = $item['model'])
            <a
                href="{{ localized_route($routePrefix . '.show', [$category->public_slug_path ?? $category->slug, $pageItem->slug], false) }}"
                class="{{ $mobile ? 'flex items-start gap-3 rounded-[16px] px-3 py-2.5 text-xs transition' : 'group relative flex items-start gap-2.5 rounded-[16px] px-2.5 py-2.5 text-sm transition hover:bg-slate-100/80 dark:hover:bg-slate-800/40' }}"
                data-theory-menu-page="{{ $pageItem->getKey() }}" data-theory-sidebar-node
            >
                <span class="mt-1.5 inline-flex h-3 w-3 shrink-0 rounded-full border-2" data-theory-menu-page-dot></span>
                @if($mobile)
                    <span class="min-w-0 break-words leading-5" data-theory-sidebar-highlight>{{ $pageItem->title }}</span>
                @else
                    <span class="min-w-0 flex-1"><span class="block text-sm font-semibold leading-5" data-theory-sidebar-highlight>{{ $pageItem->title }}</span></span>
                @endif
            </a>
        @endif
    @endforeach
</div>
