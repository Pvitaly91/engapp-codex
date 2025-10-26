@extends('layouts.app')

@section('title', __('Перегляд файлу сидера'))

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">{{ __('Перегляд файлу сидера') }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ __('Ознайомтеся з PHP-кодом сидера перед виконанням або видаленням.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('seed-runs.index') }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                        {{ __('Повернутися до списку') }}
                    </a>
                    <form method="POST" action="{{ route('seed-runs.run') }}" data-preloader>
                        @csrf
                        <input type="hidden" name="class_name" value="{{ $className }}">
                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-500 transition">
                            <i class="fa-solid fa-play"></i>
                            {{ __('Виконати сидер') }}
                        </button>
                    </form>
                </div>
            </div>

            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div>
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Клас сидера') }}</dt>
                    <dd class="font-mono break-all">{{ $className }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Читабельна назва') }}</dt>
                    <dd>{{ $displayClassName }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Шлях до файлу') }}</dt>
                    <dd class="font-mono break-all">{{ $filePath }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-slate-900 text-slate-100 rounded-lg shadow overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800 text-xs text-slate-300 uppercase tracking-wide">
                <span>{{ __('Вміст PHP-файлу') }}</span>
                <span>{{ __('Лише для перегляду') }}</span>
            </div>
            <pre class="p-4 text-xs md:text-sm font-mono whitespace-pre overflow-x-auto">{{ $fileContents }}</pre>
        </div>
    </div>
@endsection
