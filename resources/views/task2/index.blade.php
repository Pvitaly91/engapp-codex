@extends('layouts.app')

@section('title', 'Task 2 Test')

@section('content')
<div class="mx-auto mt-8 p-8 bg-white rounded-xl shadow w-[800px]" x-data="dragDrop(@json($words))">
    <h2 class="text-2xl font-bold mb-4 text-blue-700">Task 2</h2>

    @if($feedback)
        <div class="mb-4 text-lg font-semibold">Score: {{ $feedback['score'] }} / {{ $feedback['total'] }}</div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2">
        <template x-for="(word, index) in remainingWords" :key="index">
            <span class="px-3 py-1 rounded bg-gray-200 cursor-move" draggable="true"
                  @dragstart="dragging = word">
                <span x-text="word"></span>
            </span>
        </template>
    </div>

    <form method="POST" action="{{ route('task2.check') }}">
        @csrf
        <div class="space-y-2 leading-8">
            {!! $textWithBlanks !!}
        </div>
        <button type="submit" class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 transition">
            Check
        </button>
    </form>

    <script>
    function dragDrop(words) {
        return {
            dragging: null,
            remainingWords: words,
            drop(event, key) {
                event.preventDefault();
                if (this.dragging) {
                    const idx = this.remainingWords.indexOf(this.dragging);
                    if (idx !== -1) this.remainingWords.splice(idx, 1);
                    document.getElementById('blank-' + key).textContent = this.dragging;
                    document.getElementById('input-' + key).value = this.dragging;
                    this.dragging = null;
                }
            }
        }
    }
    </script>
</div>
@endsection
