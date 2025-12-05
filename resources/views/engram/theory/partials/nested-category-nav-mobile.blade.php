{{-- 
    Nested category navigation for mobile menu
    @param Collection $categories - Root categories with children
    @param ?PageCategory $selectedCategory - Currently selected category
    @param string $routePrefix - Route prefix (e.g., 'theory')
    @param int $level - Current nesting level (default 0)
--}}
@php
    $categories = $categories ?? collect();
    $selectedCategory = $selectedCategory ?? null;
    $routePrefix = $routePrefix ?? 'theory';
    $level = $level ?? 0;
    $indent = $level * 12; // Indentation in pixels
@endphp

@foreach($categories as $category)
    @php
        $isActive = $selectedCategory && $selectedCategory->is($category);
        $hasChildren = $category->hasChildren();
        $hasSelectedDescendant = $hasChildren && $category->hasDescendant($selectedCategory);
        $hasPages = $category->relationLoaded('pages') && $category->pages->isNotEmpty();
        $isExpandable = $hasChildren || $hasPages;
        $isExpanded = $isActive || $hasSelectedDescendant;
    @endphp
    
    <div class="category-mobile-item" x-data="{ expanded: {{ $isExpanded ? 'true' : 'false' }} }">
        <div class="flex items-center" style="padding-left: {{ $indent }}px">
            @if($isExpandable)
                <button 
                    @click.stop="expanded = !expanded"
                    class="flex h-7 w-7 items-center justify-center rounded text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors flex-shrink-0"
                    type="button"
                >
                    <svg 
                        class="h-3.5 w-3.5 transition-transform duration-200"
                        :class="{ 'rotate-90': expanded }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @else
                <span class="w-7 flex-shrink-0"></span>
            @endif
            
            <a 
                href="{{ route($routePrefix . '.category', $category->slug) }}"
                class="flex-1 flex items-center justify-between rounded-lg px-2 py-2 text-sm {{ $isActive ? 'bg-primary text-primary-foreground font-medium' : 'text-muted-foreground hover:bg-muted' }}"
            >
                <span>{{ $category->title }}</span>
                @if(isset($category->pages_count) && $category->pages_count > 0)
                    <span class="text-xs opacity-60">{{ $category->pages_count }}</span>
                @endif
            </a>
        </div>
        
        @if($isExpandable)
            <div x-show="expanded" x-collapse>
                {{-- Child categories --}}
                @if($hasChildren)
                    @include('engram.theory.partials.nested-category-nav-mobile', [
                        'categories' => $category->children,
                        'selectedCategory' => $selectedCategory,
                        'routePrefix' => $routePrefix,
                        'level' => $level + 1,
                    ])
                @endif
                
                {{-- Pages within this category --}}
                @if($hasPages)
                    <div class="space-y-0.5 {{ $hasChildren ? 'mt-1' : '' }}" style="padding-left: {{ $indent + 28 }}px">
                        @foreach($category->pages as $page)
                            <a 
                                href="{{ route($routePrefix . '.show', [$category->slug, $page->slug]) }}"
                                class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs text-muted-foreground hover:bg-muted transition-all"
                            >
                                <svg class="h-3 w-3 flex-shrink-0 text-muted-foreground/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="truncate">{{ $page->title }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
@endforeach
