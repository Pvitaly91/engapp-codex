<?php

namespace App\Http\Controllers;

use App\Support\SiteMode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevSiteModeController extends Controller
{
    public function __invoke(Request $request, SiteMode $siteMode): JsonResponse
    {
        return response()->json([
            'host' => $request->getHost(),
            'mode' => $siteMode->current($request),
            'features' => $siteMode->availableFeatures($request),
            'production_domains' => $siteMode->productionDomains(),
        ]);
    }
}
