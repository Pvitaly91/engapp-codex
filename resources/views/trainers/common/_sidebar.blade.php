@php
    // $baseRoute — route name (наприклад, 'train', якщо потрібно)
    // $subtopics — масив ['test' => 'Тест', ...]
    // $currentSub — поточний підпункт
@endphp

<aside class=" shrink-0 bg-white rounded-xl shadow p-4">
    <nav class="flex flex-col gap-2">
        @foreach($subtopics as $key => $label)
            <a href="?sub={{ $key }}"
               class="block px-3 py-2 rounded hover:bg-blue-50 font-medium
                {{ $currentSub == $key ? 'bg-blue-100 text-blue-700' : 'text-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>
</aside>
