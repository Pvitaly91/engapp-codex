@extends('layouts.app')

@section('title', 'Theory')

@section('content')
    <div class="grid gap-4">
        @foreach($pages as $page)
            <a href="{{ route('pages.show', $page->slug) }}" class="block p-4 bg-white rounded-lg shadow hover:bg-gray-50">
                {{ $page->title }}
            </a>
        @endforeach
    </div>
@endsection
