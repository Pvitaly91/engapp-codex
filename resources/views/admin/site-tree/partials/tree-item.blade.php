@php
    $hasChildren = $item->children->isNotEmpty();
    // Use Tailwind CSS classes for margin-left based on depth
    $marginClasses = match ($depth) {
        0 => '',
        1 => 'ml-6',
        2 => 'ml-12',
        3 => 'ml-[4.5rem]',
        default => 'ml-24',
    };
@endphp

<div 
    data-tree-item
    data-item-id="{{ $item->id }}"
    class="border-l-2 {{ $item->is_checked ? 'border-blue-400' : 'border-gray-200 opacity-50' }} transition-opacity {{ $marginClasses }}"
>
    <div class="flex items-center gap-2 py-1.5 px-2 hover:bg-gray-50 rounded-r transition">
        @if ($hasChildren)
            <button 
                type="button" 
                data-toggle-children
                class="flex-shrink-0 p-0.5 text-gray-400 hover:text-gray-600 transition"
                title="Розгорнути/згорнути"
            >
                <svg class="w-4 h-4 rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        @else
            <span class="w-5"></span>
        @endif

        <label class="flex items-center gap-2 cursor-pointer flex-1 min-w-0">
            <input 
                type="checkbox" 
                data-tree-item-checkbox
                data-item-id="{{ $item->id }}"
                {{ $item->is_checked ? 'checked' : '' }}
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-offset-0"
            />
            <span class="text-sm text-gray-900 truncate {{ !$item->is_checked ? 'line-through text-gray-400' : '' }}">
                {{ $item->title }}
            </span>
            @if ($item->level)
                <span class="flex-shrink-0 inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                    {{ $item->level }}
                </span>
            @endif
        </label>
    </div>

    @if ($hasChildren)
        <div data-tree-children class="pl-2">
            @foreach ($item->children as $child)
                @include('admin.site-tree.partials.tree-item', ['item' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
