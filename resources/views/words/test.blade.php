@extends('layouts.app')

@section('title', 'Words Test')

@section('content')
    <div class=" mx-auto mt-8 p-8 bg-white rounded-xl shadow w-[800px]">
        <h2 class="text-2xl font-bold mb-4 text-blue-700">Words Test</h2>

        <form method="GET" action="{{ route('words.test') }}" class="mb-4">
            <input type="hidden" name="filter" value="1">
            <div class="mb-2 text-sm">Filter Tags:</div>
            <div class="flex flex-wrap gap-2">
                @foreach($allTags as $tag)
                    <div>
                        <input
                            type="checkbox"
                            name="tags[]"
                            value="{{ $tag->name }}"
                            id="tag-{{ $tag->id }}"
                            class="hidden peer"
                            {{ in_array($tag->name, $selectedTags) ? 'checked' : '' }}
                        >
                        <label
                            for="tag-{{ $tag->id }}"
                            class="px-3 py-1 rounded border cursor-pointer text-sm bg-gray-200 peer-checked:bg-blue-600 peer-checked:text-white"
                        >
                            {{ $tag->name }}
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex gap-2">
                <button type="submit" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700 transition text-sm">Apply</button>
                <a href="{{ route('words.test', ['reset' => 1]) }}" class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700 transition text-sm">Reset Filter</a>
            </div>
        </form>

        @if(isset($stats))
            <div class="mb-4 flex gap-4 text-gray-600 text-base">
                <div>Total: <b>{{ $stats['total'] }} / {{ $totalCount }}</b></div>
                <div>Correct: <b class="text-green-700">{{ $stats['correct'] }}</b></div>
                <div>Wrong: <b class="text-red-700">{{ $stats['wrong'] }}</b></div>
                <div>Percent: <b>{{ $percentage }}%</b></div>
            </div>
            <form method="POST" action="{{ route('words.test.reset') }}" class="mb-4">
                @csrf
                <button type="submit" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300 transition text-sm">Reset</button>
            </form>
        @endif

        @if(isset($feedback))
            <div class="mb-4">
                @if($feedback['isCorrect'])
                    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">
                        Correct! <b>{{ $feedback['word'] }}</b> = <b>{{ $feedback['correctAnswer'] }}</b>
                    </div>
                @else
                    <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">
                        Wrong for <b>{{ $feedback['word'] }}</b>.<br>
                        Your answer: <b>{{ $feedback['userAnswer'] }}</b><br>
                        Correct: <b>{{ $feedback['correctAnswer'] }}</b>
                    </div>
                @endif
            </div>
        @endif

        @if($word)
            <form method="POST" action="{{ route('words.test.check') }}" class="mt-4">
                @csrf
                <input type="hidden" name="word_id" value="{{ $word->id }}">
                <input type="hidden" name="questionType" value="{{ $questionType }}">
                <input type="hidden" name="redirect_route" value="words.test">

                <div class="mb-6">
                    <div class="text-sm text-gray-500 mb-1">
                        @foreach($word->tags as $tag)
                            {{ $tag->name }}@if(!$loop->last),@endif
                        @endforeach
                    </div>
                    @if($questionType == 'en_to_uk')
                        <div class="text-lg mb-2">Choose the correct <b>Ukrainian translation</b> for:</div>
                        <div class="text-3xl font-bold text-blue-900 mb-6">{{ $word->word }}</div>
                    @else
                        <div class="text-lg mb-2">Choose the correct <b>English word</b> for:</div>
                        <div class="text-3xl font-bold text-blue-900 mb-6">{{ $translation }}</div>
                    @endif

                    <div class="flex flex-wrap gap-2">
                        @foreach($options as $option)
                            <div>
                                <input
                                    type="radio"
                                    name="answer"
                                    value="{{ $option }}"
                                    id="opt-{{ $loop->index }}"
                                    class="hidden peer"
                                    required
                                >
                                <label for="opt-{{ $loop->index }}" class="px-4 py-2 rounded border cursor-pointer bg-gray-200 peer-checked:bg-green-600 peer-checked:text-white">
                                    {{ $option }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 transition">
                    Check
                </button>
            </form>
        @else
            <div class="text-gray-500">No words available!</div>
        @endif
    </div>
@endsection

