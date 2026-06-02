@extends('layouts.app')

@section('title', 'Редагувати сторінку')

@section('content')
    <div class="mx-auto max-w-6xl space-y-8">
        @if ($page->tags->isNotEmpty())
            <section class="rounded-2xl border border-blue-200 bg-blue-50 p-6 shadow">
                <header class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Прикріплені теги</h2>
                    <p class="text-sm text-gray-600">Теги, призначені для цієї сторінки</p>
                </header>
                <div class="flex flex-wrap gap-2">
                    @foreach ($page->tags as $tag)
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-blue-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700">
                            <svg class="h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                            </svg>
                            <span>{{ $tag->name }}</span>
                            @if (!empty($tag->category))
                                <span class="text-xs text-gray-500">({{ $tag->category }})</span>
                            @endif
                        </span>
                    @endforeach
                </div>
            </section>
        @endif

        @include('page-manager::partials.form', [
            'heading' => 'Редагування: ' . $page->title,
            'description' => 'Оновіть основні дані сторінки. Нижче ви можете керувати блоками контенту.',
            'formAction' => route('pages.manage.update', $page),
            'formMethod' => 'PUT',
            'submitLabel' => 'Зберегти сторінку',
            'page' => $page,
            'categories' => $categories,
            'tagsByCategory' => $tagsByCategory,
        ])

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow">
            <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Блоки контенту</h2>
                    <p class="text-sm text-gray-500">Редагуйте, видаляйте або додавайте блоки для цієї сторінки.</p>
                </div>
                <a href="{{ route('pages.manage.blocks.create', $page) }}" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">+ Новий блок</a>
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
                                            <a href="{{ route('pages.manage.blocks.edit', [$page, $block]) }}" class="rounded-lg border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-100">Редагувати</a>
                                            <form action="{{ route('pages.manage.blocks.destroy', [$page, $block]) }}" method="POST" onsubmit="return confirm('Видалити цей блок?');">
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
