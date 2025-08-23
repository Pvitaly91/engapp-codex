@extends('layouts.app')

@section('title', $verbHint->exists ? 'Edit Verb Hint' : 'Add Verb Hint')

@section('content')
<div class="max-w-md mx-auto p-4">
    <h1 class="text-xl font-bold mb-4">{{ $verbHint->exists ? 'Edit Verb Hint' : 'Add Verb Hint' }}</h1>
    <form method="POST" action="{{ $verbHint->exists ? route('verb-hints.update', $verbHint->id) : route('verb-hints.store') }}" class="space-y-4">
        @csrf
        @if($verbHint->exists)
            @method('PUT')
        @endif
        <input type="hidden" name="from" value="{{ $from }}">
        <input type="hidden" name="question_id" value="{{ $verbHint->question_id }}">
        <input type="hidden" name="marker" value="{{ $verbHint->marker }}">
        <div>
            <label class="block mb-1" for="hint">Verb Hint</label>
            <input type="text" id="hint" name="hint" value="{{ old('hint', $verbHint->option->option ?? '') }}" class="border rounded px-2 py-1 w-full">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
    </form>
</div>
@endsection
