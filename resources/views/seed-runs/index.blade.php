@extends('layouts.app')

@section('title', 'Seed Runs')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Seed Runs</h1>
                    <p class="text-sm text-gray-500">Керуйте виконаними та невиконаними сидарами.</p>
                </div>
                @if($tableExists)
                    <form method="POST" action="{{ route('seed-runs.run-missing') }}" data-preloader>
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-500 transition disabled:opacity-50" @if($pendingSeeders->isEmpty()) disabled @endif>
                            <i class="fa-solid fa-play"></i>
                            Виконати всі невиконані
                        </button>
                    </form>
                @endif
            </div>

            @if(session('status'))
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @unless($tableExists)
                <div class="rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-yellow-800">
                    Таблиця <code class="font-mono">seed_runs</code> ще не створена. Запустіть міграції, щоб продовжити.
                </div>
            @endunless
        </div>

        @if($tableExists)
            <div >
                <div class="bg-white shadow rounded-lg p-6 my-4">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Невиконані сидери</h2>
                    @if($pendingSeeders->isEmpty())
                        <p class="text-sm text-gray-500">Усі сидери вже виконані.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($pendingSeeders as $pendingSeeder)
                                <li class="flex items-center justify-between gap-4">
                                    <span class="text-sm font-mono text-gray-700 break-all">{{ $pendingSeeder->display_class_name }}</span>
                                    <form method="POST" action="{{ route('seed-runs.run') }}" data-preloader>
                                        @csrf
                                        <input type="hidden" name="class_name" value="{{ $pendingSeeder->class_name }}">
                                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-500 transition">
                                            <i class="fa-solid fa-play"></i>
                                            Виконати
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="bg-white shadow rounded-lg p-6 overflow-hidden">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Виконані сидери</h2>
                    @if($executedSeeders->isEmpty())
                        <p class="text-sm text-gray-500">Поки що немає виконаних сидерів.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-600">Class</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-600">Виконано</th>
                                        <th class="px-4 py-2 text-right font-medium text-gray-600">Дії</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($executedSeeders as $seedRun)
                                        <tr>
                                            <td class="px-4 py-2 text-xs text-gray-700 break-all">
                                                <div class="font-mono text-sm text-gray-800">{{ $seedRun->display_class_name }}</div>

                                                @if($seedRun->question_count > 0)
                                                    <div x-data="{ open: false }" class="mt-2 space-y-3">
                                                        <button type="button"
                                                                class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                                                @click="open = !open">
                                                            <span x-show="!open" x-cloak>Показати питання ({{ $seedRun->question_count }})</span>
                                                            <span x-show="open" x-cloak>Сховати питання ({{ $seedRun->question_count }})</span>
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>

                                                        <div x-show="open" x-transition style="display: none;" class="space-y-3">
                                                            @foreach($seedRun->question_groups as $categoryGroup)
                                                                <div x-data="{ openCategory: true }" class="border border-slate-200 rounded-xl overflow-hidden">
                                                                    <div class="flex items-center justify-between gap-3 px-4 py-2 bg-slate-100">
                                                                        <div>
                                                                            <h3 class="text-sm font-semibold text-gray-800">{{ $categoryGroup['display_name'] }}</h3>
                                                                            <p class="text-xs text-gray-500">
                                                                                @if($categoryGroup['category'])
                                                                                    Категорія ID: {{ $categoryGroup['category']['id'] }}
                                                                                @else
                                                                                    Категорія не вказана
                                                                                @endif
                                                                                · Питань: {{ $categoryGroup['question_count'] }}
                                                                            </p>
                                                                        </div>
                                                                        <button type="button"
                                                                                class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-100 hover:bg-blue-200 transition"
                                                                                @click="openCategory = !openCategory">
                                                                            <span x-text="openCategory ? 'Згорнути' : 'Розгорнути'"></span>
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openCategory }" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                    <div x-show="openCategory" x-transition style="display: none;" class="space-y-3 px-4 pb-4 pt-3 bg-white">
                                                                        @foreach($categoryGroup['sources'] as $sourceGroup)
                                                                            <div x-data="{ openSource: true }" class="border border-slate-200 rounded-lg overflow-hidden">
                                                                                <div class="flex items-center justify-between gap-3 px-3 py-2 bg-slate-50">
                                                                                    <div>
                                                                                        <h4 class="text-sm font-semibold text-slate-700">{{ $sourceGroup['display_name'] }}</h4>
                                                                                        <p class="text-xs text-slate-500">
                                                                                            @if($sourceGroup['source'])
                                                                                                Джерело ID: {{ $sourceGroup['source']['id'] }}
                                                                                            @else
                                                                                                Джерело не вказане
                                                                                            @endif
                                                                                            · Питань: {{ $sourceGroup['questions']->count() }}
                                                                                        </p>
                                                                                    </div>
                                                                                    <button type="button"
                                                                                            class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                                                                            @click="openSource = !openSource">
                                                                                        <span x-text="openSource ? 'Сховати' : 'Показати'"></span>
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openSource }" viewBox="0 0 20 20" fill="currentColor">
                                                                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                                                        </svg>
                                                                                    </button>
                                                                                </div>
                                                                                <div x-show="openSource" x-transition style="display: none;" class="space-y-2 px-3 pb-3 pt-2 bg-white">
                                                                                    @foreach($sourceGroup['questions'] as $question)
                                                                                        <div class="border border-slate-200 rounded-lg bg-slate-50 px-3 py-2 text-left text-sm leading-relaxed">
                                                                                            <div class="flex items-start justify-between gap-3">
                                                                                                <div class="text-gray-800 space-y-1">{!! $question['highlighted_text'] !!}</div>
                                                                                                <form method="POST" action="{{ route('seed-runs.questions.destroy', $question['id']) }}" data-preloader data-confirm="Видалити це питання?">
                                                                                                    @csrf
                                                                                                    @method('DELETE')
                                                                                                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-red-700 px-2.5 py-1 rounded-full bg-red-50 hover:bg-red-100 transition">
                                                                                                        <i class="fa-solid fa-trash-can"></i>
                                                                                                        Видалити
                                                                                                    </button>
                                                                                                </form>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-gray-600">{{ optional($seedRun->ran_at)->format('Y-m-d H:i:s') }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="{{ route('seed-runs.destroy-with-questions', $seedRun->id) }}" data-preloader data-confirm="Видалити лог та пов’язані питання?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-md hover:bg-amber-500 transition">
                                                            <i class="fa-solid fa-broom"></i>
                                                            Видалити з питаннями
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('seed-runs.destroy', $seedRun->id) }}" data-preloader data-confirm="Видалити лише запис про виконання?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
                                                            <i class="fa-solid fa-trash"></i>
                                                            Видалити запис
                                                        </button>
                                                    </form>
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
        @endif
    </div>

    <div id="seed-run-preloader" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg px-6 py-4 flex items-center gap-3 text-sm text-gray-700">
            <span class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
            <span>Виконується операція…</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const preloader = document.getElementById('seed-run-preloader');

            if (!preloader) {
                return;
            }

            document.querySelectorAll('form[data-preloader]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    const confirmMessage = form.dataset.confirm;

                    if (confirmMessage && !window.confirm(confirmMessage)) {
                        event.preventDefault();

                        return;
                    }

                    preloader.classList.remove('hidden');
                });
            });
        });
    </script>
@endsection
