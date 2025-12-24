@extends('layouts.app')

@section('title', 'Керування мовами')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Керування мовами сайту</h1>
        <a href="{{ route('language-manager.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <i class="fa-solid fa-plus"></i>
            Додати мову
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-100">
            <div class="flex items-center gap-4 text-sm text-gray-600">
                <span><i class="fa-solid fa-circle-info text-blue-500"></i> Мова за замовчуванням відображається по корінному URL (<code class="bg-gray-100 px-1 rounded">/</code>)</span>
                <span><i class="fa-solid fa-globe text-green-500"></i> Інші мови — з префіксом (<code class="bg-gray-100 px-1 rounded">/en/</code>, <code class="bg-gray-100 px-1 rounded">/pl/</code>)</span>
            </div>
        </div>

        @if($languages->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <i class="fa-solid fa-language text-4xl mb-4 text-gray-300"></i>
                <p class="text-lg font-medium">Мови ще не додані</p>
                <p class="mt-2">Додайте першу мову, щоб почати</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full" id="languages-table">
                    <thead>
                        <tr class="bg-gray-50 text-left text-sm font-medium text-gray-600">
                            <th class="px-4 py-3 w-10"></th>
                            <th class="px-4 py-3">Код</th>
                            <th class="px-4 py-3">Назва</th>
                            <th class="px-4 py-3">Назва (рідна)</th>
                            <th class="px-4 py-3">URL приклад</th>
                            <th class="px-4 py-3 text-center">Статус</th>
                            <th class="px-4 py-3 text-center">За замовчуванням</th>
                            <th class="px-4 py-3 text-right">Дії</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="sortable-languages">
                        @foreach($languages as $language)
                            <tr class="hover:bg-gray-50 transition" data-id="{{ $language->id }}">
                                <td class="px-4 py-3 cursor-move text-gray-400 hover:text-gray-600">
                                    <i class="fa-solid fa-grip-vertical"></i>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center justify-center px-2 py-1 bg-gray-100 rounded font-mono text-sm font-bold">
                                        {{ strtoupper($language->code) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $language->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $language->native_name }}</td>
                                <td class="px-4 py-3">
                                    <code class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                        @if($language->is_default)
                                            /example-page
                                        @else
                                            /{{ $language->code }}/example-page
                                        @endif
                                    </code>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <form action="{{ route('language-manager.toggle-active', $language) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-full text-sm font-medium transition {{ $language->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                            {{ $language->is_active ? 'Активна' : 'Неактивна' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($language->is_default)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                                            <i class="fa-solid fa-star text-xs"></i>
                                            За замовчуванням
                                        </span>
                                    @else
                                        <form action="{{ route('language-manager.set-default', $language) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-sm text-gray-500 hover:text-blue-600 transition" title="Зробити за замовчуванням">
                                                <i class="fa-regular fa-star"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('language-manager.edit', $language) }}" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Редагувати">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        @unless($language->is_default)
                                            <form action="{{ route('language-manager.destroy', $language) }}" method="POST" class="inline" onsubmit="return confirm('Видалити мову {{ $language->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Видалити">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-8 p-6 bg-blue-50 rounded-xl border border-blue-100">
        <h3 class="text-lg font-semibold text-blue-900 mb-3">
            <i class="fa-solid fa-book mr-2"></i>
            Як використовувати
        </h3>
        <div class="space-y-3 text-sm text-blue-800">
            <div>
                <strong>URL структура:</strong>
                <ul class="mt-1 ml-4 list-disc space-y-1">
                    <li>Мова за замовчуванням: <code class="bg-blue-100 px-1 rounded">/about</code>, <code class="bg-blue-100 px-1 rounded">/contacts</code></li>
                    <li>Інші мови: <code class="bg-blue-100 px-1 rounded">/en/about</code>, <code class="bg-blue-100 px-1 rounded">/pl/contacts</code></li>
                </ul>
            </div>
            <div>
                <strong>Blade-директиви:</strong>
                <ul class="mt-1 ml-4 list-disc space-y-1">
                    <li><code class="bg-blue-100 px-1 rounded">&#64;localizedUrl('en', '/about')</code> — генерує локалізований URL</li>
                    <li><code class="bg-blue-100 px-1 rounded">&#64;switchLocaleUrl('en')</code> — URL для перемикання мови на поточній сторінці</li>
                    <li><code class="bg-blue-100 px-1 rounded">$__languageSwitcher</code> — масив даних для перемикача мов</li>
                </ul>
            </div>
            <div>
                <strong>Middleware:</strong>
                <code class="bg-blue-100 px-1 rounded">locale.url</code> — автоматично визначає мову з URL
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" integrity="sha384-kHCl8B3Z7h6xKi0eBmcBVUF5GbrYw6zLsO+hE8oOJpAKm3H5W8qw3bN3C6OYPZLm" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortable = document.getElementById('sortable-languages');
    if (sortable) {
        new Sortable(sortable, {
            animation: 150,
            handle: '.cursor-move',
            onEnd: function() {
                const order = Array.from(sortable.querySelectorAll('tr[data-id]'))
                    .map(row => row.dataset.id);
                
                fetch('{{ route('language-manager.update-order') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ order })
                });
            }
        });
    }
});
</script>
@endpush
@endsection
