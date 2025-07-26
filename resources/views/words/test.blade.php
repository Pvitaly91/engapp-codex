@extends('layouts.app')

@section('title', 'Words Test')

@section('content')
    <div class="max-w-xl mx-auto mt-8 p-8 bg-white rounded-xl shadow">
        <h2 class="text-2xl font-bold mb-4 text-blue-700">Test your vocabulary</h2>

        {{-- Статистика --}}
        @if(isset($stats))
            <div class="mb-4 flex gap-4 text-gray-600 text-base">
                <div>Всього: <b>{{ $stats['total'] }}</b></div>
                <div>Вірно: <b class="text-green-700">{{ $stats['correct'] }}</b></div>
                <div>Помилки: <b class="text-red-700">{{ $stats['wrong'] }}</b></div>
            </div>
        @endif

        {{-- Flash feedback про попередню відповідь --}}
        @if(isset($feedback))
            <div class="mb-4">
                @if($feedback['isCorrect'])
                    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">
                        Вірно! 🎉 — <b>{{ $feedback['word'] }}</b> = <b>{{ $feedback['correctAnswer'] }}</b>
                    </div>
                @else
                    <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">
                        Неправильно для <b>{{ $feedback['word'] }}</b>.<br>
                        Ваша відповідь: <b>{{ $feedback['userAnswer'] }}</b><br>
                        Правильно: <b>{{ $feedback['correctAnswer'] }}</b>
                    </div>
                @endif
            </div>
        @endif

        @if($word)
            <form method="POST" action="{{ route('words.test.check') }}">
                @csrf
                <input type="hidden" name="word_id" value="{{ $word->id }}">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <input type="hidden" name="questionType" value="{{ $questionType }}">

                <div class="mb-6">
                    @if($questionType == 'en_to_uk')
                        <div class="text-lg mb-2">Оберіть правильний <b>український переклад</b> для слова:</div>
                        <div class="text-3xl font-bold text-blue-900 mb-6">
                            {{ $word->word }}
                        </div>
                    @else
                        <div class="text-lg mb-2">Оберіть правильне <b>англійське слово</b> для перекладу:</div>
                        <div class="text-3xl font-bold text-blue-900 mb-6">
                            {{ $translation }}
                        </div>
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
                    Перевірити
                </button>
            </form>
        @else
            <div class="text-gray-500">Немає слів у словнику!</div>
        @endif
    </div>
@endsection
