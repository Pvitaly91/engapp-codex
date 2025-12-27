<?php

namespace App\Http\Controllers;

use App\Services\IrregularVerbsService;
use Illuminate\Http\Request;

class IrregularVerbsTestController extends Controller
{
    protected $verbsService;

    public function __construct(IrregularVerbsService $verbsService)
    {
        $this->verbsService = $verbsService;
    }

    /**
     * Display the irregular verbs test page.
     */
    public function index()
    {
        $verbs = $this->verbsService->getIrregularVerbs();

        return view('verbs.test', [
            'verbs' => $verbs,
        ]);
    }

    /**
     * Get verbs data as JSON (optional endpoint for AJAX loading).
     */
    public function data()
    {
        $verbs = $this->verbsService->getIrregularVerbs();

        return response()->json([
            'verbs' => $verbs,
        ]);
    }
}
