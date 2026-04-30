<?php

namespace App\Http\Controllers;

use App\Services\CourseCatalogService;

class CourseCatalogController extends Controller
{
    public function __construct(
        private CourseCatalogService $catalogService,
    ) {}

    public function index()
    {
        return view('courses.index', [
            'courses' => $this->catalogService->courses(),
        ]);
    }
}
