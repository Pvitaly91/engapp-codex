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

        <section class="space-y-6">
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
        </section>
    </div>
@endsection
