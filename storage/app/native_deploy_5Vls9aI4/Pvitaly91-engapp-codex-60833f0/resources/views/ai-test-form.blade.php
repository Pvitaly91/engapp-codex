@extends('layouts.app')

@section('title', 'AI Grammar Test')

@section('content')
<div class="w-[800px] mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">AI Grammar Test</h1>
    <form method="POST" action="{{ route('ai-test.start') }}" class="space-y-4" x-data="{ provider: 'chatgpt' }">
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
            <label class="block font-bold mb-1">Number of blanks (min and max up to {{$max}}):</label>
            <div class="flex items-center gap-2">
                <input type="number" name="answers_min" value="1" class="border rounded p-1 w-24" min="1" max="{{$max}}">
                <span>-</span>
                <input type="number" name="answers_max" value="1" class="border rounded p-1 w-24" min="1" max="{{$max}}">
            </div>
        </div>
        <div>
            <label class="block font-bold mb-1">AI Provider:</label>
            <div class="flex gap-4">
                <label class="flex items-center">
                    <input type="radio" name="provider" value="chatgpt" class="mr-2" x-model="provider" checked>
                    ChatGPT
                </label>
                <label class="flex items-center">
                    <input type="radio" name="provider" value="gemini" class="mr-2" x-model="provider">
                    Gemini
                </label>
                <label class="flex items-center">
                    <input type="radio" name="provider" value="mixed" class="mr-2" x-model="provider">
                    Mixed
                </label>
            </div>
            <div class="mt-2" x-show="provider === 'chatgpt'">
                <label class="block font-bold mb-1">Model:</label>
                <select name="model" class="border rounded p-1">
                    <option value="random">Random</option>
                    @foreach($models as $model)
                        <option value="{{ $model }}">{{ $model }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-semibold">Start</button>
    </form>
</div>
@endsection
