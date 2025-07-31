<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Task2Controller extends Controller
{
    private array $answers = [
        'a1' => "I'm cooking",
        'a2' => 'is playing',
        'a3' => 'is reading',
        'a4' => 'is listening',
        'a5' => 'is barking',
        'a6' => 'is bringing',
        'a7' => 'am watching',
        'a8' => 'is gardening',
        'a9' => 'is walking',
        'a10' => 'are singing',
    ];

    private string $text = "It's Saturday. {a1} lunch for my family.\nMy husband {a2} baseball.\nMy daughter {a3} in her room.\nMy son {a4} to music.\nNow, the dog {a5}.\nA delivery driver {a6} a package to the door.\nI {a7} the window.\nMy neighbor {a8} outside.\nA family {a9} on the sidewalk.\nWhat’s that sound? Oh, the birds {a10} outside.";

    public function index()
    {
        $feedback = session('task2_feedback');
        session()->forget('task2_feedback');
        $textWithBlanks = preg_replace_callback('/\{(a\d+)\}/', function ($m) use ($feedback) {
            $key = $m[1];
            $val = $feedback['result'][$key]['user'] ?? '';
            $status = $feedback['result'][$key]['is_correct'] ?? null;
            $class = $status === null ? '' : ($status ? 'text-green-700' : 'text-red-700');
            return '<span id="blank-'.$key.'" class="inline-block min-w-[100px] border-b border-gray-400 text-center '.$class.'" ondragover="event.preventDefault()" @drop="drop(event, \''.$key.'\')">'.e($val).'</span><input type="hidden" id="input-'.$key.'" name="answers['.$key.']" value="'.e($val).'">';
        }, e($this->text));
        $words = array_values($this->answers);
        return view('task2.index', [
            'textWithBlanks' => $textWithBlanks,
            'words' => $words,
            'feedback' => $feedback,
        ]);
    }

    public function check(Request $request)
    {
        $userAnswers = $request->input('answers', []);
        $result = [];
        $score = 0;
        foreach ($this->answers as $key => $correct) {
            $user = $userAnswers[$key] ?? '';
            $isCorrect = trim($user) === $correct;
            $result[$key] = [
                'correct' => $correct,
                'user' => $user,
                'is_correct' => $isCorrect,
            ];
            if ($isCorrect) {
                $score++;
            }
        }
        session()->flash('task2_feedback', [
            'result' => $result,
            'score' => $score,
            'total' => count($this->answers),
        ]);

        return redirect()->route('task2');
    }
}
