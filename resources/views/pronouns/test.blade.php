@extends('layouts.app')

@section('title', 'Pronouns Test')

@section('content')
    <div class="max-w-xl mx-auto mt-8 p-8 bg-white rounded-xl shadow">
        <h2 class="text-2xl font-bold mb-4 text-blue-700">Pronouns Test</h2>

        @if(isset($stats))
            <div class="mb-4 flex gap-4 text-gray-600 text-base">
                <div>Total: <b>{{ $stats['total'] }}</b></div>
                <div>Correct: <b class="text-green-700">{{ $stats['correct'] }}</b></div>
                <div>Wrong: <b class="text-red-700">{{ $stats['wrong'] }}</b></div>
            </div>
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

        <div class="mb-6 text-center">
            <img src="{{ route('pronouns.image') }}" alt="Pronouns" class="mx-auto mb-4 max-w-full">
        </div>

        @if($item)
            <form method="POST" action="{{ route('pronouns.test.check') }}" class="mt-4">
                @csrf
                <input type="hidden" name="en" value="{{ $item['en'] }}">
                <input type="hidden" name="questionType" value="{{ $questionType }}">

                <div class="mb-6">
                    @if($questionType == 'en_to_uk')
                        <div class="text-lg mb-2">Choose the correct <b>Ukrainian translation</b> for:</div>
                        <div class="text-3xl font-bold text-blue-900 mb-6">{{ $item['en'] }}</div>
                    @else
                        <div class="text-lg mb-2">Choose the correct <b>English pronoun</b> for:</div>
                        <div class="text-3xl font-bold text-blue-900 mb-6">{{ $item['uk'] }}</div>
                    @endif

                    <div class="flex flex-col gap-3">
                        @foreach($options as $option)
                            <label class="block">
                                <input type="radio" name="answer" value="{{ $option }}" required class="mr-2 align-middle">
                                <span class="align-middle">{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 transition">
                    Check
                </button>
            </form>
        @else
            <div class="text-gray-500">No pronouns available!</div>
        @endif
    </div>
@endsection
