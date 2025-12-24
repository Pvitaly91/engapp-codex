@extends('layouts.app')

@section('title', 'Редагувати мову')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('language-manager.index') }}" class="text-gray-500 hover:text-gray-700 transition">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Назад до списку мов
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h1 class="text-xl font-bold text-gray-800">
                Редагувати мову: {{ $language->native_name }}
            </h1>
        </div>

        <form action="{{ route('language-manager.update', $language) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                        Код мови <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        value="{{ old('code', $language->code) }}"
                        placeholder="uk, en, pl..."
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('code') border-red-500 @enderror"
                        required
                        maxlength="10"
                    >
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                        Порядок сортування
                    </label>
                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        value="{{ old('sort_order', $language->sort_order) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Назва (англійською) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $language->name) }}"
                        placeholder="Ukrainian, English, Polish..."
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                        required
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="native_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Назва (рідною) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="native_name"
                        name="native_name"
                        value="{{ old('native_name', $language->native_name) }}"
                        placeholder="Українська, English, Polski..."
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('native_name') border-red-500 @enderror"
                        required
                    >
                    @error('native_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-6 pt-4 border-t border-gray-100">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $language->is_active) ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        {{ $language->is_default ? 'disabled' : '' }}
                    >
                    <span class="text-sm font-medium text-gray-700">Активна мова</span>
                    @if($language->is_default)
                        <span class="text-xs text-gray-500">(неможливо деактивувати мову за замовчуванням)</span>
                    @endif
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_default"
                        value="1"
                        {{ old('is_default', $language->is_default) ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <span class="text-sm font-medium text-gray-700">Мова за замовчуванням</span>
                </label>
            </div>

            @error('is_default')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                <div>
                    @unless($language->is_default)
                        <form action="{{ route('language-manager.destroy', $language) }}" method="POST" class="inline" onsubmit="return confirm('Видалити мову {{ $language->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 transition">
                                <i class="fa-solid fa-trash-can mr-1"></i>
                                Видалити мову
                            </button>
                        </form>
                    @endunless
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('language-manager.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition">
                        Скасувати
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fa-solid fa-check mr-2"></i>
                        Зберегти зміни
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
