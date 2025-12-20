@extends('layouts.engram')

@section('title', 'Тест завершено')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-12 text-stone-900" data-animate>
    <div class="rounded-3xl border border-border/70 bg-card p-8 text-center shadow-soft">
        <p class="text-sm uppercase tracking-wide text-stone-500">Результат</p>
        <h1 class="mt-2 text-3xl font-bold text-stone-900">Тест завершено</h1>
        <p class="mt-2 text-sm text-stone-600">Добре попрацювали! Перегляньте статистику та спробуйте ще раз.</p>

        <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-border/70 bg-white/70 p-4 shadow-soft">
                <p class="text-xs text-stone-500">Всього</p>
                <p class="text-xl font-semibold text-stone-900">{{ $stats['total'] }} / {{ $totalCount }}</p>
            </div>
            <div class="rounded-2xl border border-border/70 bg-white/70 p-4 shadow-soft">
                <p class="text-xs text-stone-500">✅ Правильно</p>
                <p class="text-xl font-semibold text-emerald-700">{{ $stats['correct'] }}</p>
            </div>
            <div class="rounded-2xl border border-border/70 bg-white/70 p-4 shadow-soft">
                <p class="text-xs text-stone-500">❌ Помилок</p>
                <p class="text-xl font-semibold text-rose-700">{{ $stats['wrong'] }}</p>
            </div>
            <div class="rounded-2xl border border-border/70 bg-white/70 p-4 shadow-soft">
                <p class="text-xs text-stone-500">% Успіху</p>
                <p class="text-xl font-semibold text-stone-900">{{ $percentage }}%</p>
            </div>
        </div>

        <div class="mt-6 space-y-2">
            <div class="flex items-center justify-between text-sm text-stone-600">
                <span>Прогрес</span>
                <span>{{ $progressPercent ?? $percentage }}%</span>
            </div>
            <div class="h-2 rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary" style="width: {{ $progressPercent ?? $percentage }}%"></div>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <form method="POST" action="{{ route('words.public.test.reset') }}">
                @csrf
                <input type="hidden" name="redirect_route" value="words.public.test">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow-soft transition hover:opacity-90">
                    Почати знову
                </button>
            </form>
            <a href="{{ route('words.public.test', ['reset' => 1]) }}" class="inline-flex items-center justify-center rounded-xl border border-border/70 px-5 py-3 text-sm font-semibold shadow-soft transition hover:bg-muted/60">
                Скинути фільтр і почати заново
            </a>
        </div>
    </div>
</div>
@endsection
