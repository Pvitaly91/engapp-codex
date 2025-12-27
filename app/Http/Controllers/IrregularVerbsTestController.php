<?php

namespace App\Http\Controllers;

use App\Services\IrregularVerbsService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class IrregularVerbsTestController extends Controller
{
    public function __construct(
        private readonly IrregularVerbsService $verbsService
    ) {}

    /**
     * Display the irregular verbs test page.
     */
    public function index(): View
    {
        $locale = session('locale', app()->getLocale());
        $verbs = $this->verbsService->getVerbs($locale);

        return view('verbs.test', [
            'verbs' => $verbs,
            'locale' => $locale,
        ]);
    }

    /**
     * Return verbs data as JSON.
     */
    public function data(): JsonResponse
    {
        $locale = session('locale', app()->getLocale());
        $verbs = $this->verbsService->getVerbs($locale);

        return response()->json($verbs);
    }
}
