@extends('layouts.app')

@section('title', 'Створити категорію тегів')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-2xl flex-col gap-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-800">Нова категорія тегів</h1>
                <p class="text-slate-500">Задайте назву для нової категорії, щоб згодом додавати до неї теги.</p>
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

            <form method="POST" action="{{ route('test-tags.categories.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf

                <div class="space-y-2">
                    <label for="name" class="block text-sm font-medium text-slate-700">Назва категорії</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring"
                    >
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                    >
                        Створити категорію
                    </button>
                    <a
                        href="{{ route('test-tags.index') }}"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-5 py-2 text-sm font-medium text-slate-600 hover:border-slate-300"
                    >
                        Скасувати
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
