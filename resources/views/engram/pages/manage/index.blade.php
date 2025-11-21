@extends('layouts.app')

@section('title', 'Управління сторінками')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-6xl flex-col gap-8">
            <header class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">Сторінки теорії</h1>
                        <p class="text-slate-500">Керуйте сторінками теорії та їх тегами.</p>
                    </div>
                    <a
                        href="{{ route('pages.manage.create') }}"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700"
                    >
                        <i class="fa-solid fa-plus mr-2"></i>Додати сторінку
                    </a>
                </div>
                <p class="text-sm text-slate-400">Всього сторінок: {{ $pages->count() }}</p>
            </header>

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($pages->isEmpty())
                <div class="rounded-lg border border-slate-200 bg-white p-8 text-center">
                    <p class="text-slate-500">Сторінки ще не створено.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Назва</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Категорія</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Теги</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-slate-700">Дії</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($pages as $page)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $page->title }}</div>
                                            <div class="text-sm text-slate-500">{{ $page->slug }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($page->category)
                                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                {{ $page->category->title }}
                                            </span>
                                        @else
                                            <span class="text-sm text-slate-400">Без категорії</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                            {{ $page->tags_count }} {{ $page->tags_count === 1 ? 'тег' : 'тегів' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a
                                                href="{{ route('pages.manage.edit', $page) }}"
                                                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                            >
                                                Редагувати
                                            </a>
                                            @if($page->category && $page->slug)
                                                <a
                                                    href="{{ route('pages.show', [$page->category->slug, $page->slug]) }}"
                                                    target="_blank"
                                                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                                >
                                                    <i class="fa-solid fa-external-link"></i>
                                                </a>
                                            @endif
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
