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
        $textWithInputs = preg_replace_callback('/\{(a\d+)\}/', function ($m) use ($feedback) {
            $key = $m[1];
            $val = $feedback['result'][$key]['user'] ?? '';
            $status = $feedback['result'][$key]['is_correct'] ?? null;
            $class = $status === null ? '' : ($status ? 'border-green-600' : 'border-red-600');
            return '<input type="text" name="answers['.$key.']" value="'.e($val).'" class="border-b-2 outline-none '.$class.' w-40 text-center">';
        }, e($this->text));

        return view('task2.index', [
            'textWithInputs' => nl2br($textWithInputs),
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
