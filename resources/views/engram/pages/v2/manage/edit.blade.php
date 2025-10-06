@extends('layouts.app')

@section('title', 'Редагувати сторінку')

@section('content')
    <div class="mx-auto max-w-6xl space-y-8">
        @include('engram.pages.v2.manage.partials.form', [
            'heading' => 'Редагування: ' . $page->title,
            'description' => 'Оновіть основні дані сторінки. Нижче ви можете керувати блоками контенту.',
            'formAction' => route('pages-v2.manage.update', $page),
            'formMethod' => 'PUT',
            'submitLabel' => 'Зберегти сторінку',
            'page' => $page,
        ])

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow">
            <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Блоки контенту</h2>
                    <p class="text-sm text-gray-500">Редагуйте, видаляйте або додавайте блоки для цієї сторінки.</p>
                </div>
                <a href="{{ route('pages-v2.manage.blocks.create', $page) }}" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">+ Новий блок</a>
            </header>

            @if ($blocks->isEmpty())
                <p class="mt-6 text-sm text-gray-500">Для цієї сторінки ще немає блоків. Додайте перший, щоб розпочати.</p>
            @else
                @php
                    $columnLabels = [
                        'left' => 'Ліва колонка',
                        'right' => 'Права колонка',
                        'header' => 'Шапка',
                    ];
                @endphp

                <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200">
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
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-700">
                                            @if ($block->heading)
                                                {{ $block->heading }}
                                            @elseif (! empty($block->body))
                                                {{ \Illuminate\Support\Str::limit(strip_tags($block->body), 80) }}
                                            @else
                                                <span class="text-gray-400">(без назви)</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $columnLabels[$block->column] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $block->sort_order }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('pages-v2.manage.blocks.edit', [$page, $block]) }}" class="rounded-lg border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-100">Редагувати</a>
                                            <form action="{{ route('pages-v2.manage.blocks.destroy', [$page, $block]) }}" method="POST" onsubmit="return confirm('Видалити цей блок?');">
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
        </section>
    </div>
@endsection
