@extends('layouts.app')

@section('title', 'Search')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Search results for "{{ \Illuminate\Support\Str::of($query)->e() }}"</h1>

    @if($results->isEmpty())
        <p>No results found.</p>
    @else
        <ul class="space-y-2">
            @foreach($results as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="text-blue-600 hover:underline">
                        {{ $item['title'] }}
                        <span class="text-gray-500 text-sm">({{ $item['type'] }})</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
