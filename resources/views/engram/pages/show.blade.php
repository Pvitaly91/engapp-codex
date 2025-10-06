@extends('layouts.engram')

@section('title', $page->title)

@section('content')
    <article class="max-w-none space-y-4">
        @if(!empty($page->blocks) && $page->blocks->isNotEmpty())
            @includeIf('engram.pages.templates.' . $page->template, ['page' => $page])
        @elseif(!empty($page->legacy_view))
            @include($page->legacy_view)
        @else
            <div class="rounded-xl border border-dashed border-muted-foreground/40 p-6 text-muted-foreground">
                <p class="font-medium">Для цієї сторінки ще не налаштовано жодних текстових блоків.</p>
            </div>
        @endif
    </article>
@endsection
