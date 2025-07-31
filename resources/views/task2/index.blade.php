@extends('layouts.app')

@section('title', 'Task 2 Test')

@section('content')
<div class="mx-auto mt-8 p-8 bg-white rounded-xl shadow w-[800px]">
    <h2 class="text-2xl font-bold mb-4 text-blue-700">Task 2</h2>

    @if($feedback)
        <div class="mb-4 text-lg font-semibold">Score: {{ $feedback['score'] }} / {{ $feedback['total'] }}</div>
    @endif

    <form method="POST" action="{{ route('task2.check') }}">
        @csrf
        <div class="space-y-2 leading-8">
            {!! $textWithInputs !!}
        </div>
        <button type="submit" class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 transition">
            Check
        </button>
    </form>
</div>
@endsection
