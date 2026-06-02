@extends('layouts.app')

@section('title', $test->name . ' Finished')

@section('content')
<div class="max-w-xl mx-auto mt-8 p-8 bg-white rounded-xl shadow">
    <h2 class="text-2xl font-bold mb-4 text-blue-700">{{ $test->name }} Finished</h2>
    <div class="mb-4 flex gap-4 text-gray-600 text-base">
        <div>Total: <b>{{ $stats['total'] }} / {{ $totalCount }}</b></div>
        <div>Correct: <b class="text-green-700">{{ $stats['correct'] }}</b></div>
        <div>Wrong: <b class="text-red-700">{{ $stats['wrong'] }}</b></div>
        <div>Percent: <b>{{ $percentage }}%</b></div>
    </div>
    <form method="POST" action="{{ route('saved-test.step.reset', $test->slug) }}">
        @csrf
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 transition">Restart</button>
    </form>
</div>
@endsection
