<?php

namespace App\Http\Controllers;

use App\Models\VerbHint;
use Illuminate\Http\Request;

class VerbHintController extends Controller
{
    public function edit(Request $request, VerbHint $verbHint)
    {
        $verbHint->load('option');
        $from = $request->query('from', url()->previous());

        return view('verb-hint-edit', compact('verbHint', 'from'));
    }

    public function update(Request $request, VerbHint $verbHint)
    {
        $request->validate([
            'hint' => 'required|string|max:255',
        ]);
        $option = $verbHint->option;
        $option->option = $request->input('hint');
        $option->save();

        $redirectTo = $request->input('from', url()->previous());
        return redirect($redirectTo);
    }
}
