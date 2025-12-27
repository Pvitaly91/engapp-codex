<?php

namespace App\Http\Controllers;

use App\Services\IrregularVerbsService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class IrregularVerbsTestController extends Controller
{
    public function __construct(private readonly IrregularVerbsService $service)
    {
    }

    public function index(): View
    {
        $verbs = $this->service->getVerbs();

        return view('verbs.test', [
            'verbs' => $verbs,
        ]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->service->getVerbs());
    }
}
