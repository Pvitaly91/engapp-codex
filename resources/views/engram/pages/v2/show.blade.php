@extends('layouts.engram')

@section('title', $page->title)

@section('content')
    <article class="max-w-none space-y-4">
        @include('engram.pages.partials.grammar-card', [
            'page' => $page,
            'subtitleBlock' => $subtitleBlock ?? null,
            'columns' => $columns ?? [],
            'locale' => $locale ?? app()->getLocale(),
        ])
    </article>
@endsection
