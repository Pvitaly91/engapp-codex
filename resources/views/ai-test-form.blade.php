@extends('layouts.app')

@section('title', 'AI Grammar Test')

@section('content')
<div class="w-[800px] mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">AI Grammar Test</h1>
    <form method="POST" action="{{ route('ai-test.start') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block font-bold mb-1">Choose tags:</label>
            <div class="flex flex-wrap gap-2">
                @foreach($tags as $tag)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2">{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div>
            <label class="block font-bold mb-1">Number of blanks (1-3):</label>
            <input type="number" name="answers_count" min="1" max="3" value="1" class="border rounded p-1 w-20">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-semibold">Start</button>
    </form>
</div>
@endsection
