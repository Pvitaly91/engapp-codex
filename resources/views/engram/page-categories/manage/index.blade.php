@extends('layouts.app')

@section('title', 'Управління категоріями')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-6xl flex-col gap-8">
            <header class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">Категорії сторінок теорії</h1>
                        <p class="text-slate-500">Керуйте категоріями сторінок та їх тегами.</p>
                    </div>
                    <a
                        href="{{ route('page-categories.manage.create') }}"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700"
                    >
                        <i class="fa-solid fa-plus mr-2"></i>Додати категорію
                    </a>
                </div>
                <p class="text-sm text-slate-400">Всього категорій: {{ $categories->count() }}</p>
            </header>

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($categories->isEmpty())
                <div class="rounded-lg border border-slate-200 bg-white p-8 text-center">
                    <p class="text-slate-500">Категорії ще не створено.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Назва</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Мова</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Сторінок</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Теги</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-slate-700">Дії</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($categories as $category)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $category->title }}</div>
                                            <div class="text-sm text-slate-500">{{ $category->slug }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                            {{ strtoupper($category->language) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                            {{ $category->pages_count }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                            {{ $category->tags_count }} {{ $category->tags_count === 1 ? 'тег' : 'тегів' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a
                                                href="{{ route('page-categories.manage.edit', $category) }}"
                                                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                            >
                                                Редагувати
                                            </a>
                                            <a
                                                href="{{ route('pages.category', $category->slug) }}"
                                                target="_blank"
                                                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                            >
                                                <i class="fa-solid fa-external-link"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
