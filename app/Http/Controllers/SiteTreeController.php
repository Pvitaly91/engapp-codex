<?php

namespace App\Http\Controllers;

use App\Models\SiteTreeItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteTreeController extends Controller
{
    public function index(): View
    {
        $tree = SiteTreeItem::roots()
            ->with(['children' => function ($query) {
                $query->with(['children' => function ($q) {
                    $q->with(['children' => function ($q2) {
                        $q2->orderBy('sort_order');
                    }])->orderBy('sort_order');
                }])->orderBy('sort_order');
            }])
            ->get();

        return view('admin.site-tree.index', compact('tree'));
    }

    public function toggle(Request $request, SiteTreeItem $item): JsonResponse
    {
        $validated = $request->validate([
            'is_checked' => 'required|boolean',
        ]);

        $item->update([
            'is_checked' => $validated['is_checked'],
        ]);

        return response()->json([
            'success' => true,
            'is_checked' => $item->is_checked,
        ]);
    }
}
