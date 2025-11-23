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
                            <x-related-test-card :test="$test" />
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </div>
@endsection
