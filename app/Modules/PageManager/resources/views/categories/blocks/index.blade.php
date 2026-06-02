@extends('layouts.app')

@section('title', 'Опис категорії — ' . $category->title)

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Опис категорії</h1>
                <p class="text-sm text-gray-500">Керуйте текстовими блоками опису для розділу «{{ $category->title }}».</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('pages.manage.index', ['tab' => 'categories']) }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">← До категорій</a>
                <a href="{{ route('pages.manage.categories.blocks.create', $category) }}" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">+ Новий блок</a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
        @endif

        @php($blocks = $blocks ?? collect())

        @if ($blocks->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-8 text-center text-sm text-gray-500">
                Поки що опису немає. Додайте перший блок, щоб розповісти про цю категорію.
            </div>
        @else
            @php(
                $columnLabels = [
                    'left' => 'Ліва колонка',
                    'right' => 'Права колонка',
                    'header' => 'Шапка',
                ]
            )

            <div class="overflow-hidden rounded-2xl border border-gray-200 shadow">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">№</th>
                            <th class="px-4 py-3 text-left font-medium">Мова</th>
                            <th class="px-4 py-3 text-left font-medium">Тип</th>
                            <th class="px-4 py-3 text-left font-medium">Заголовок / прев'ю</th>
                            <th class="px-4 py-3 text-left font-medium">Колонка</th>
                            <th class="px-4 py-3 text-left font-medium">Порядок</th>
                            <th class="px-4 py-3 text-right font-medium">Дії</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($blocks as $index => $block)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium">{{ $block->locale }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $block->type }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @if ($block->heading)
                                        {{ $block->heading }}
                                    @elseif(! empty($block->body))
                                        {{ \Illuminate\Support\Str::limit(strip_tags($block->body), 80) }}
                                    @else
                                        <span class="text-gray-400">(без назви)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $columnLabels[$block->column] ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $block->sort_order }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('pages.manage.categories.blocks.edit', [$category, $block]) }}" class="rounded-lg border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-100">Редагувати</a>
                                        <form action="{{ route('pages.manage.categories.blocks.destroy', [$category, $block]) }}" method="POST" onsubmit="return confirm('Видалити цей блок?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1 text-sm text-red-600 hover:bg-red-100">Видалити</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
