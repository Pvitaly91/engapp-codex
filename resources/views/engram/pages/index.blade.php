@extends('layouts.engram')

@section('title', 'Теорія')

@section('content')
    @php($categoryPages = $categoryPages ?? collect())

    <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
        @include('engram.pages.partials.sidebar', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'categoryPages' => $categoryPages,
            'showCategoryPagesNav' => false,
        ])

        @php($categoryDescription = $categoryDescription ?? ['hasBlocks' => false])

        <section class="space-y-6">
            

            @if ($selectedCategory && ($categoryDescription['hasBlocks'] ?? false))
            
                    @include('engram.pages.partials.grammar-card', [
                        'page' => $selectedCategory,
                        'subtitleBlock' => $categoryDescription['subtitleBlock'] ?? null,
                        'columns' => $categoryDescription['columns'] ?? [],
                        'locale' => $categoryDescription['locale'] ?? app()->getLocale(),
                    ])
            @else
                <header class="space-y-2">
                    <h1 class="text-3xl font-semibold tracking-tight">{{ optional($selectedCategory)->title ?? 'Теорія' }}</h1>
                    <p class="text-sm text-muted-foreground">
                        @if ($selectedCategory)
                            Матеріали розділу «{{ $selectedCategory->title }}».
                        @else
                            Виберіть категорію, щоб переглянути сторінки теорії.
                        @endif
                    </p>
                </header>
            @endif

            @if ($categoryPages->isNotEmpty())
                @include('engram.pages.partials.page-grid', [
                    'pages' => $categoryPages,
                    'category' => $selectedCategory,
                ])
            @else
                <div class="rounded-2xl border border-dashed border-muted p-8 text-center text-muted-foreground">
                    Поки що в цій категорії немає сторінок. Спробуйте іншу категорію зліва.
                </div>
            @endif

            @if(isset($relatedTests) && $relatedTests->isNotEmpty())
                <div class="rounded-2xl border border-border/80 bg-card shadow-soft p-6" x-data="{ expanded: true }">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Пов'язані тести</h2>
                        <button 
                            @click="expanded = !expanded"
                            class="text-sm text-muted-foreground hover:text-foreground transition flex items-center gap-1"
                            :aria-expanded="expanded"
                        >
                            <span x-text="expanded ? 'Згорнути' : 'Розгорнути'"></span>
                            <svg 
                                class="w-4 h-4 transition-transform" 
                                :class="{ 'rotate-180': !expanded }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                    @if($selectedCategory && $selectedCategory->tags->isNotEmpty())
                        <div class="mb-4 pb-4 border-b border-border/60">
                            <div class="text-xs text-muted-foreground mb-2 font-semibold">Теги категорії:</div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($selectedCategory->tags as $tag)
                                    <span class="inline-block bg-secondary/20 text-secondary-foreground text-xs px-2 py-0.5 rounded">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div x-show="expanded" x-collapse>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($relatedTests as $test)
                                <x-related-test-card :test="$test" />
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>
@endsection
