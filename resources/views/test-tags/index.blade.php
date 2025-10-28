@extends('layouts.app')

@section('title', 'Керування тегами тестів')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-5xl flex-col gap-8">
            <header class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">Теги тестів</h1>
                        <p class="text-slate-500">Переглядайте категорії та теги й переходьте до редагування за потреби.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('test-tags.create') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                        >
                            Додати тег
                        </a>
                    </div>
                </div>
                <p class="text-sm text-slate-400">Всього тегів: {{ $totalTags }}</p>
            </header>

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <section class="space-y-4">
                <h2 class="text-xl font-semibold text-slate-800">Категорії та теги</h2>
                @if ($tagGroups->isEmpty())
                    <p class="text-sm text-slate-500">Теги ще не додано.</p>
                @else
                    <div class="space-y-6">
                        @foreach ($tagGroups as $group)
                            <div x-data="{ open: false }" class="space-y-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <button
                                        type="button"
                                        class="flex flex-1 items-start gap-3 text-left"
                                        @click="open = !open"
                                        :aria-expanded="open.toString()"
                                        aria-controls="tag-group-{{ $loop->index }}"
                                    >
                                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-500">
                                            <i class="fa-solid fa-chevron-down text-sm transform transition-transform" :class="{ '-rotate-90': !open }"></i>
                                        </span>
                                        <span class="space-y-1">
                                            <span class="block text-lg font-semibold text-slate-800">{{ $group['label'] }}</span>
                                            <span class="block text-sm text-slate-500">{{ trans_choice('{0}Немає тегів|{1}1 тег|[2,4]:count теги|[5,*]:count тегів', $group['tags']->count(), ['count' => $group['tags']->count()]) }}</span>
                                        </span>
                                    </button>
                                    <div class="flex items-center gap-2">
                                        <a
                                            href="{{ route('test-tags.categories.edit', ['category' => $group['key']]) }}"
                                            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-blue-200 hover:text-blue-600"
                                        >
                                            Редагувати
                                        </a>
                                        <form
                                            action="{{ route('test-tags.categories.destroy', ['category' => $group['key']]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Видалити категорію та всі її теги?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                                            >
                                                Видалити
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div
                                    id="tag-group-{{ $loop->index }}"
                                    x-show="open"
                                    x-transition
                                    x-cloak
                                >
                                    <ul class="space-y-2">
                                        @forelse ($group['tags'] as $tag)
                                            <li class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-100 px-3 py-2">
                                                <span class="text-slate-700">{{ $tag->name }}</span>
                                                <span class="flex items-center gap-2 text-xs">
                                                    <a
                                                        href="{{ route('test-tags.edit', $tag) }}"
                                                        class="rounded-lg border border-slate-200 px-3 py-1.5 font-medium text-slate-600 hover:border-blue-200 hover:text-blue-600"
                                                    >
                                                        Редагувати
                                                    </a>
                                                    <form
                                                        action="{{ route('test-tags.destroy', $tag) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Видалити тег «{{ $tag->name }}»?')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="rounded-lg border border-red-200 px-3 py-1.5 font-medium text-red-600 hover:bg-red-50"
                                                        >
                                                            Видалити
                                                        </button>
                                                    </form>
                                                </span>
                                            </li>
                                        @empty
                                            <li class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 py-4 text-center text-sm text-slate-500">
                                                У цій категорії ще немає тегів.
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
