@extends('layouts.app')

@section('title', $testName)

@section('content')
<style>
    .text-base {
        line-height: 2.5rem;
    }
</style>   
<div class="max-w-3xl mx-auto p-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold mb-2">{{ $testName }}</h1>
        <button onclick="window.history.back()" class="bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-2xl font-semibold hover:bg-blue-100">
            ← Назад
        </button>
    </div>
    <div class="text-sm text-gray-500 mb-6">
        <p>Рівні: {{ $levelFrom }}-{{ $levelTo }}</p>
        <p>Кількість питань: {{ count($questions) }}</p>
        @if(!empty($tags))
            <p>Теги: {{ implode(', ', $tags) }}</p>
        @endif
    </div>

    @if($questions->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 mb-4">
            <p class="text-yellow-800">Немає доступних питань для цього тесту. Спробуйте інші фільтри.</p>
        </div>
    @else
        @include('components.word-search')
        <form action="{{ route('grammar-test.check') }}" method="POST" class="space-y-6">
            @csrf
            @foreach($questions as $q)
                <input type="hidden" name="questions[{{ $q->id }}]" value="1">
                <div class="bg-white shadow rounded-2xl p-4 mb-4">
                    <div class="text-xs text-gray-500 mb-1">Level: {{ $q->level ?? 'N/A' }}</div>
                    <div class="flex gap-2 items-baseline">
                        <span class="font-bold text-base">{{ $loop->iteration }}.</span>

@php preg_match_all('/\{a(\d+)\}/', $q->question, $matches); @endphp
@include('components.question-input', [
    'question' => $q,
    'inputNamePrefix' => "question_{$q->id}_",
    'manualInput' => $manualInput,
    'autocompleteInput' => $autocompleteInput,
    'builderInput' => $builderInput,
    'showVerbHintEdit' => false,
    'showQuestionEdit' => false,
])
                    </div>
                    @if($q->tags->count())
                        <div class="mt-1 space-x-1">
                            @php
                                $colors = ['bg-blue-200 text-blue-800', 'bg-green-200 text-green-800', 'bg-red-200 text-red-800', 'bg-purple-200 text-purple-800', 'bg-pink-200 text-pink-800', 'bg-yellow-200 text-yellow-800', 'bg-indigo-200 text-indigo-800', 'bg-teal-200 text-teal-800'];
                            @endphp
                            @foreach($q->tags as $tag)
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold {{ $colors[$loop->index % count($colors)] }}">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl shadow font-semibold text-lg">
                Перевірити
            </button>
        </form>
    @endif
</div>
@endsection
