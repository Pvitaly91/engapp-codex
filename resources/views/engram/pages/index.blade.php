@extends('layouts.engram')

@section('title', 'Pages')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($pages as $page)
            <a href="{{ route('pages.show', $page->slug) }}" class="block p-4 bg-card text-card-foreground rounded-xl shadow-soft hover:bg-muted">
                {{ $page->title }}
            </a>
        @endforeach
    </div>
@endsection
