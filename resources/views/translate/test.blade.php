@extends('layouts.app')

@section('title', 'Sentence Translation Test')

@section('content')
    <div class="mx-auto mt-8 p-8 bg-white rounded-xl shadow w-[800px]">
        <h2 class="text-2xl font-bold mb-4 text-blue-700">Sentence Translation Test</h2>

        <div class="mb-4 flex gap-4 text-gray-600 text-base">
            <div>Total: <b>{{ $stats['total'] }} / {{ $totalCount }}</b></div>
            <div>Correct: <b class="text-green-700">{{ $stats['correct'] }}</b></div>
            <div>Wrong: <b class="text-red-700">{{ $stats['wrong'] }}</b></div>
            <div>Percent: <b>{{ $percentage }}%</b></div>
        </div>
        <form method="POST" action="{{ route('translate.test.reset') }}" class="mb-4">
            @csrf
            <button type="submit" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300 transition text-sm">Reset</button>
        </form>

        @if(isset($feedback))
            <div class="mb-4">
                @if($feedback['isCorrect'])
                    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">
                        Correct! <b>{{ $feedback['correct'] }}</b>
                    </div>
                @else
                    <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">
                        {{ $feedback['explanation'] }}<br>
                        Your answer: <b>{{ $feedback['userAnswer'] }}</b>
                    </div>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('translate.test.check') }}" class="mt-4">
            @csrf
            <input type="hidden" name="sentence_id" value="{{ $sentence->id }}">
            <div class="mb-6 text-xl font-semibold">{{ $sentence->text_uk }}</div>
            <input type="text" name="answer" class="border rounded w-full p-2 mb-4" autofocus required>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 transition">
                {{ $attempts > 0 ? 'Submit' : 'Check' }}
            </button>
        </form>
    </div>
@endsection

