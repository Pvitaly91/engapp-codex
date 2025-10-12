@extends('layouts.engram')

@section('title', $page->title)

@section('content')
    <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
        @include('engram.pages.partials.sidebar', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory ?? null,
            'categoryPages' => $categoryPages ?? collect(),
            'currentPage' => $page,
            'showCategoryPagesNav' => true,
        ])

        <article class="max-w-none space-y-4">
            @include('engram.pages.partials.grammar-card', [
                'page' => $page,
                'subtitleBlock' => $subtitleBlock ?? null,
                'columns' => $columns ?? [],
                'locale' => $locale ?? app()->getLocale(),
            ])
        </article>
    </div>
@endsection
