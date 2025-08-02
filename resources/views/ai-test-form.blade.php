@extends('layouts.app')

@section('title', 'AI Grammar Test')

@section('content')
<div class="w-[800px] mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">AI Grammar Test</h1>
    <form method="POST" action="{{ route('ai-test.start') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block font-bold mb-1">Choose tags:</label>
            @foreach($tags as $category => $group)
                <div class="mt-2">
                    <h2 class="font-semibold mb-1">{{ $category }}</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($group as $tag)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="hidden peer">
                                <span class="px-3 py-1 rounded-full border peer-checked:bg-blue-600 peer-checked:text-white">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <div>
            <label class="block font-bold mb-1">Number of blanks (min and max up to 10):</label>
            <div class="flex items-center gap-2">
                <input type="number" name="answers_min" value="1" class="border rounded p-1 w-24" min="1" max="10">
                <span>-</span>
                <input type="number" name="answers_max" value="1" class="border rounded p-1 w-24" min="1" max="10">
            </div>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-semibold">Start</button>
    </form>
</div>
@endsection
