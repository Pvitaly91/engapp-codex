<?php

namespace App\Http\Controllers;

use App\Models\VerbHint;
use Illuminate\Http\Request;

class VerbHintController extends Controller
{
    public function edit(VerbHint $verbHint)
    {
        $verbHint->load('option');
        return view('verb-hint-edit', compact('verbHint'));
    }

    public function update(Request $request, VerbHint $verbHint)
    {
        $request->validate([
            'hint' => 'required|string|max:255',
        ]);
        $option = $verbHint->option;
        $option->option = $request->input('hint');
        $option->save();

        return redirect()->back();
    }
}
