@extends('layouts.app')

@section('title', 'Grammar Test Result')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <h2 class="text-xl font-bold mb-4">Результати тесту</h2>
    <ul class="space-y-4">
        @foreach($results as $res)
            <li class="p-4 rounded-2xl shadow {{ $res['is_correct'] ? 'bg-green-100' : 'bg-red-100' }}">
                <div>
                    <span class="font-bold">{{ $loop->iteration }}.</span>
                    @php
                        $questionText = $res['question']->question;
                        $answers = $res['user_answers'];
                        preg_match_all('/\{a(\d+)\}/', $questionText, $matches);
                        $replacements = [];
                        foreach ($matches[1] as $i => $num) {
                            $key = "a".$num;
                            $answer = $answers[$key] ?? '—';
                            $right = $res['correct_answers'][$key] ?? '';
                            $explanation = $res['explanations'][$key] ?? null;
                            $color = strtolower($answer) === strtolower($right) ? 'text-green-700 font-bold' : 'text-red-700 font-bold underline';
                            $show = '<span class="'.$color.'">'.$answer.'</span>';
                            if (strtolower($answer) !== strtolower($right)) {
                                $show .= ' <span class="text-xs text-gray-500">(правильна: '.$right.')</span>';
                                if ($explanation) {
                                    $show .= '<div class="text-xs text-gray-700 mt-1">'.e($explanation).'</div>';
                                }
                            }
                            $replacements['{a'.$num.'}'] = $show;
                        }
                        $finalQuestion = strtr(e($questionText), $replacements);
                    @endphp
                    {!! $finalQuestion !!}
                </div>
                <div class="text-xs text-gray-500">
                    Категорія: {{ ucfirst($res['question']->category->name) }} | Складність: {{ $res['question']->difficulty }}/10
                </div>
            </li>
        @endforeach
    </ul>
    <a href="{{ route('grammar-test') }}" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-2xl shadow">Спробувати ще раз</a>
</div>
@endsection

