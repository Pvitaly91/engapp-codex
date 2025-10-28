@extends('layouts.app')

@section('title', 'Керування тегами тестів')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-4xl flex-col gap-8">
            <header class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">Теги тестів</h1>
                        <p class="text-slate-500">Перегляд існуючих тегів та їх категорій.</p>
                    </div>
                    <a
                        href="{{ route('test-tags.manage') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                    >
                        Керувати тегами
                    </a>
                </div>
                <p class="text-sm text-slate-400">Всього тегів: {{ $totalTags }}</p>
            </header>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-800">Категорії</h2>
                @if ($categories->isEmpty())
                    <p class="text-sm text-slate-500">Категорії відсутні.</p>
                @else
                    <ul class="list-disc space-y-1 pl-6 text-slate-700">
                        @foreach ($categories as $category)
                            <li>{{ $category }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="space-y-4">
                <h2 class="text-xl font-semibold text-slate-800">Список тегів</h2>
                @if ($tagsByCategory->isEmpty())
                    <p class="text-sm text-slate-500">Теги ще не додано.</p>
                @else
                    <div class="space-y-6">
                        @foreach ($tagsByCategory as $categoryName => $tags)
                            <div class="space-y-2">
                                <h3 class="text-lg font-medium text-slate-700">{{ $categoryName ?? 'Без категорії' }}</h3>
                                <ul class="list-disc space-y-1 pl-6 text-slate-600">
                                    @foreach ($tags as $tag)
                                        <li>{{ $tag->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
