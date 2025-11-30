<?php

namespace App\Http\Controllers;

use App\Models\SiteTreeItem;
use Database\Seeders\SiteTreeSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SiteTreeController extends Controller
{
    public function index(): View
    {
        $tree = $this->getTreeWithChildren();

        return view('admin.site-tree.index', compact('tree'));
    }

    private function getTreeWithChildren()
    {
        return SiteTreeItem::roots()
            ->with(['children' => function ($query) {
                $query->with(['children' => function ($q) {
                    $q->with(['children' => function ($q2) {
                        $q2->orderBy('sort_order');
                    }])->orderBy('sort_order');
                }])->orderBy('sort_order');
            }])
            ->get();
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'level' => 'nullable|string|max:20',
            'parent_id' => 'nullable|integer|exists:site_tree_items,id',
        ]);

        // Get the max sort_order for the parent
        $maxSortOrder = SiteTreeItem::where('parent_id', $validated['parent_id'] ?? null)
            ->max('sort_order') ?? -1;

        $item = SiteTreeItem::create([
            'title' => $validated['title'],
            'level' => $validated['level'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_checked' => true,
            'sort_order' => $maxSortOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'item' => $item,
        ]);
    }

    public function update(Request $request, SiteTreeItem $item): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'level' => 'nullable|string|max:20',
            'is_checked' => 'sometimes|boolean',
        ]);

        $item->update($validated);

        return response()->json([
            'success' => true,
            'item' => $item->fresh(),
        ]);
    }

    public function destroy(SiteTreeItem $item): JsonResponse
    {
        // Delete item and all children (cascades via foreign key)
        $item->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function move(Request $request, SiteTreeItem $item): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|integer|exists:site_tree_items,id',
            'sort_order' => 'required|integer|min:0',
        ]);

        $newParentId = $validated['parent_id'];
        $newSortOrder = $validated['sort_order'];

        // Prevent moving an item to be its own descendant
        if ($newParentId) {
            $parent = SiteTreeItem::find($newParentId);
            while ($parent) {
                if ($parent->id === $item->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot move item to its own descendant',
                    ], 422);
                }
                $parent = $parent->parent;
            }
        }

        DB::transaction(function () use ($item, $newParentId, $newSortOrder) {
            $oldParentId = $item->parent_id;
            $oldSortOrder = $item->sort_order;

            // If moving within the same parent
            if ($oldParentId == $newParentId) {
                if ($newSortOrder > $oldSortOrder) {
                    // Moving down
                    SiteTreeItem::where('parent_id', $oldParentId)
                        ->where('sort_order', '>', $oldSortOrder)
                        ->where('sort_order', '<=', $newSortOrder)
                        ->decrement('sort_order');
                } else if ($newSortOrder < $oldSortOrder) {
                    // Moving up
                    SiteTreeItem::where('parent_id', $oldParentId)
                        ->where('sort_order', '>=', $newSortOrder)
                        ->where('sort_order', '<', $oldSortOrder)
                        ->increment('sort_order');
                }
            } else {
                // Moving to a different parent
                // Close gap in old parent
                SiteTreeItem::where('parent_id', $oldParentId)
                    ->where('sort_order', '>', $oldSortOrder)
                    ->decrement('sort_order');

                // Make room in new parent
                SiteTreeItem::where('parent_id', $newParentId)
                    ->where('sort_order', '>=', $newSortOrder)
                    ->increment('sort_order');
            }

            $item->update([
                'parent_id' => $newParentId,
                'sort_order' => $newSortOrder,
            ]);
        });

        return response()->json([
            'success' => true,
            'item' => $item->fresh(),
        ]);
    }

    public function reset(): JsonResponse
    {
        // Run the seeder to reset to original state
        $seeder = new SiteTreeSeeder();
        $seeder->run();

        return response()->json([
            'success' => true,
            'message' => 'Tree reset to original state',
        ]);
    }

    public function getTree(): JsonResponse
    {
        $tree = $this->getTreeWithChildren();

        return response()->json([
            'success' => true,
            'tree' => $tree,
        ]);
    }

    public function exportTree(): JsonResponse
    {
        $tree = $this->buildExportTree(null);

        return response()->json([
            'success' => true,
            'tree' => $tree,
        ]);
    }

    private function buildExportTree(?int $parentId): array
    {
        $items = SiteTreeItem::where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->get();

        $result = [];
        foreach ($items as $item) {
            $node = [
                'title' => $item->title,
                'level' => $item->level,
                'is_checked' => $item->is_checked,
            ];
            
            $children = $this->buildExportTree($item->id);
            if (!empty($children)) {
                $node['children'] = $children;
            }
            
            $result[] = $node;
        }

        return $result;
    }

    public function importTree(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tree' => 'required|array',
        ]);

        DB::transaction(function () use ($validated) {
            SiteTreeItem::truncate();
            $this->importTreeData($validated['tree'], null, 0);
        });

        return response()->json([
            'success' => true,
            'message' => 'Tree imported successfully',
        ]);
    }

    private function importTreeData(array $items, ?int $parentId, int &$sortOrder): void
    {
        foreach ($items as $data) {
            $item = SiteTreeItem::create([
                'parent_id' => $parentId,
                'title' => $data['title'],
                'level' => $data['level'] ?? null,
                'is_checked' => $data['is_checked'] ?? true,
                'sort_order' => $sortOrder++,
            ]);

            if (!empty($data['children'])) {
                $childSortOrder = 0;
                $this->importTreeData($data['children'], $item->id, $childSortOrder);
            }
        }
    }
}
