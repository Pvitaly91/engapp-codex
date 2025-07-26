@extends('layouts.app')

@section('title', 'Train')

@section('content')
    <div class="mb-6">
        
        <!-- Меню з темами -->
        <div class="flex flex-wrap gap-2 mb-8">
            @foreach($topics as $key => $label)
                <a href="{{ route('train', $key == 'all' ? null : $key) }}"
                   class="px-4 py-2 rounded-xl border @if($current == $key) bg-blue-600 text-white @else bg-white text-gray-700 hover:bg-blue-50 @endif transition text-sm font-medium shadow-sm">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>
    
    <!-- Основний контент розділу (приклад) -->
    <div>
        @if($current == 'all')
            <div class="text-gray-600 italic">Please, select a topic to start training.</div>
        @else
            @php
                $viewPath = 'trainers.' . $current;
            @endphp
            @if (view()->exists($viewPath))
                @include($viewPath)
            @else
                <div class="p-8 bg-white rounded-xl shadow text-center text-gray-400">
                    [ Trainer for topic <b>{{ $topics[$current] ?? ucfirst($current) }}</b> is not implemented yet ]
                </div>
            @endif
        @endif
    </div>
@endsection
