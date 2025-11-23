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
                <div class="rounded-2xl border border-border/80 bg-card shadow-soft p-6">
                    <h2 class="text-lg font-semibold mb-4">Пов'язані тести</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($relatedTests as $test)
                            <div class="bg-background border border-border/60 rounded-xl p-4 flex flex-col hover:border-primary/40 hover:shadow-md transition">
                                <a href="{{ route('saved-test.js', $test->slug) }}" class="font-medium text-foreground hover:text-primary mb-2">
                                    {{ $test->name }}
                                </a>
                                
                                @if($test->level_range->isNotEmpty())
                                    <div class="text-xs text-muted-foreground mb-2">
                                        <span class="font-semibold">Рівні:</span> {{ $test->level_range->join(', ') }}
                                    </div>
                                @endif

                                @if($test->matching_tags->isNotEmpty())
                                    <div class="mb-3 flex flex-wrap gap-1">
                                        @foreach($test->matching_tags as $tag)
                                            <span class="inline-block bg-primary/10 text-primary text-xs px-2 py-0.5 rounded">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($test->description)
                                    <p class="text-sm text-muted-foreground mb-3 line-clamp-2">{{ $test->description }}</p>
                                @endif

                                <a href="{{ route('saved-test.js', $test->slug) }}" class="mt-auto inline-block text-center bg-primary hover:bg-primary/80 text-primary-foreground px-4 py-2 rounded-xl text-sm font-semibold transition">
                                    Пройти тест
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </div>
@endsection
