@extends('layouts.app')

@section('title', 'Редагувати категорію тегів')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-2xl flex-col gap-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-800">Редагування категорії</h1>
                <p class="text-slate-500">
                    @if ($category === null)
                        Задайте назву для тегів без категорії, щоб згрупувати їх разом.
                    @else
                        Оновіть назву категорії «{{ $category }}».
                    @endif
                </p>
            </div>

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold">Будь ласка, виправте помилки:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('test-tags.categories.update', ['category' => $categoryKey]) }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')

                <div class="space-y-2">
                    <label for="new_name" class="block text-sm font-medium text-slate-700">Нова назва категорії</label>
                    <input
                        id="new_name"
                        name="new_name"
                        type="text"
                        value="{{ old('new_name', $category ?? '') }}"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring"
                    >
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                    >
                        Зберегти категорію
                    </button>
                    <a
                        href="{{ route('test-tags.index') }}"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-5 py-2 text-sm font-medium text-slate-600 hover:border-slate-300"
                    >
                        Скасувати
                    </a>
                </div>
            </form>

            <form
                method="POST"
                action="{{ route('test-tags.categories.destroy', ['category' => $categoryKey]) }}"
                class="rounded-xl border border-red-100 bg-red-50 p-5 shadow-sm"
                onsubmit="return confirm('Видалити категорію та всі її теги?')"
            >
                @csrf
                @method('DELETE')
                <h2 class="text-lg font-semibold text-red-700">Видалення категорії</h2>
                <p class="mt-1 text-sm text-red-600">Цю дію неможливо скасувати. Всі теги з цієї категорії також буде видалено.</p>
                <button
                    type="submit"
                    class="mt-3 inline-flex items-center rounded-lg border border-red-300 px-5 py-2 text-sm font-medium text-red-700 hover:bg-red-100"
                >
                    Видалити категорію
                </button>
            </form>
        </div>
    </div>
@endsection
