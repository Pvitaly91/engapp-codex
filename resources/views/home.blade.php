@extends('layouts.app')

@section('title', 'English Test Hub')

@section('content')
    <div class="text-center py-12">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-800 mb-6">English Test Hub</h1>
        <p class="text-lg text-gray-600 mb-8">Improve your grammar, vocabulary and translation skills with quick tests.</p>
        <a href="{{ route('words.test') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700 transition">Start Training</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-12">
        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-xl font-semibold mb-2">Words Test</h3>
            <p class="text-gray-600 mb-4">Expand your vocabulary with random word quizzes.</p>
            <a href="{{ route('words.test') }}" class="text-blue-600 hover:underline">Try it</a>
        </div>
        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-xl font-semibold mb-2">Translate Test</h3>
            <p class="text-gray-600 mb-4">Practice translating sentences from English.</p>
            <a href="{{ route('translate.test') }}" class="text-blue-600 hover:underline">Try it</a>
        </div>
        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-xl font-semibold mb-2">Grammar Tests</h3>
            <p class="text-gray-600 mb-4">Create custom grammar tests for different tenses.</p>
            <a href="{{ route('grammar-test') }}" class="text-blue-600 hover:underline">Try it</a>
        </div>
        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-xl font-semibold mb-2">Question Review</h3>
            <p class="text-gray-600 mb-4">Fix mistakes by reviewing tricky questions.</p>
            <a href="{{ route('question-review.index') }}" class="text-blue-600 hover:underline">Try it</a>
        </div>
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('saved-tests.cards') }}" class="inline-block bg-gray-200 text-gray-800 px-5 py-3 rounded-lg hover:bg-gray-300 transition">Browse saved tests</a>
    </div>
@endsection
