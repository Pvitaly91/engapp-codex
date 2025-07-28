@extends('layouts.app')

@section('title', 'Review Complete')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Всі питання переглянуто</h1>
    <a href="{{ route('question-review.index') }}" class="text-blue-600 underline">Почати спочатку</a>
</div>
@endsection
