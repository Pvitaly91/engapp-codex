@extends('layouts.engram')

@section('title', 'Тест завершено — Gramlyze')

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Header Section --}}
    <header class="text-center mb-8" data-animate>
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-success/10 mb-4">
            <span class="text-4xl">🎉</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-foreground">Тест завершено!</h1>
        <p class="text-sm text-muted-foreground mt-2">Чудова робота! Ось твої результати:</p>
    </header>

    {{-- Stats Card --}}
    <div class="rounded-3xl border border-border/70 bg-card shadow-soft overflow-hidden mb-6" data-animate data-animate-delay="100">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-foreground mb-4">Статистика</h2>
            
            {{-- Stats Grid --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="rounded-2xl bg-muted/50 p-4">
                    <div class="text-xs text-muted-foreground mb-1">Всього питань</div>
                    <div class="text-2xl font-bold text-foreground">{{ $stats['total'] }}</div>
                    <div class="text-xs text-muted-foreground">із {{ $totalCount }}</div>
                </div>
                <div class="rounded-2xl bg-muted/50 p-4">
                    <div class="text-xs text-muted-foreground mb-1">Точність</div>
                    <div class="text-2xl font-bold text-primary">{{ $percentage }}%</div>
                </div>
                <div class="rounded-2xl bg-success/10 p-4">
                    <div class="text-xs text-muted-foreground mb-1">✅ Правильно</div>
                    <div class="text-2xl font-bold text-success">{{ $stats['correct'] }}</div>
                </div>
                <div class="rounded-2xl bg-destructive/10 p-4">
                    <div class="text-xs text-muted-foreground mb-1">❌ Помилок</div>
                    <div class="text-2xl font-bold text-destructive">{{ $stats['wrong'] }}</div>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="mb-6">
                <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
                    <span>Прогрес</span>
                    <span>{{ $progressPercent ?? 100 }}%</span>
                </div>
                <div class="w-full h-3 bg-muted rounded-full overflow-hidden">
                    <div class="h-full bg-primary transition-all duration-300 rounded-full" style="width: {{ $progressPercent ?? 100 }}%"></div>
                </div>
            </div>

            {{-- Selected Tags --}}
            @if(count($selectedTags) > 0)
                <div class="border-t border-border/50 pt-4">
                    <div class="text-xs text-muted-foreground mb-2">Обрані теги:</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($selectedTags as $tag)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3" data-animate data-animate-delay="200">
        <form method="POST" action="{{ route('words.public.test.reset') }}" class="flex-1">
            @csrf
            <button type="submit" class="w-full rounded-2xl bg-primary px-6 py-4 text-base font-semibold text-primary-foreground shadow-soft hover:bg-primary/90 transition">
                Почати знову
                @if(count($selectedTags) > 0)
                    <span class="block text-xs font-normal opacity-80 mt-0.5">з тими ж тегами</span>
                @endif
            </button>
        </form>
        <a href="{{ route('words.public.test', ['reset' => 1]) }}" class="flex-1 flex items-center justify-center rounded-2xl border border-border px-6 py-4 text-base font-semibold text-foreground hover:bg-muted transition text-center" aria-label="Нова гра зі скиданням фільтра">
            Нова гра
            <span class="block text-xs font-normal text-muted-foreground ml-2" aria-hidden="true">(скинути фільтр)</span>
        </a>
    </div>

    {{-- Encouragement Message --}}
    <div class="mt-8 text-center" data-animate data-animate-delay="300">
        @if($percentage >= 90)
            <p class="text-lg">🏆 Відмінний результат! Ти чудово знаєш ці слова!</p>
        @elseif($percentage >= 70)
            <p class="text-lg">👍 Гарна робота! Продовжуй практикуватись!</p>
        @elseif($percentage >= 50)
            <p class="text-lg">💪 Непогано! Є куди рости, спробуй ще раз!</p>
        @else
            <p class="text-lg">📚 Не здавайся! Практика — ключ до успіху!</p>
        @endif
    </div>
</div>
@endsection
