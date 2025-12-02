<?php

namespace App\Http\Controllers;

use App\Models\SiteTreeItem;
use App\Models\SiteTreeVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SiteTreeController extends Controller
{
    public function index(?string $variantSlug = null): View
    {
        $variants = SiteTreeVariant::orderBy('is_base', 'desc')->orderBy('name')->get();
        
        // Get current variant or base variant
        if ($variantSlug) {
            $currentVariant = SiteTreeVariant::where('slug', $variantSlug)->firstOrFail();
        } else {
            $currentVariant = SiteTreeVariant::getBase();
        }
        
        $tree = $currentVariant ? $this->getTreeWithChildren($currentVariant->id) : collect();
        
        // Load existing pages with URLs from exported_pages.json
        $existingPages = $this->getExistingPagesWithUrls();

        return view('admin.site-tree.index', compact('tree', 'variants', 'currentVariant', 'existingPages'));
    }
    
    private function getExistingPagesWithUrls(): array
    {
        $pages = [];
        $path = config_path('pages/exported_pages.json');
        
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (isset($data['categories'])) {
                foreach ($data['categories'] as $category) {
                    // Add category with URL to its slug
                    $pages[$category['category_title']] = '/pages/' . $category['category_slug'];
                    if (isset($category['pages'])) {
                        foreach ($category['pages'] as $page) {
                            // Add page with URL
                            $pages[$page['page_title']] = '/pages/' . $category['category_slug'] . '/' . $page['page_slug'];
                        }
                    }
                }
            }
        }
        
        return $pages;
    }

    private function getTreeWithChildren(?int $variantId = null)
    {
        $query = SiteTreeItem::roots();
        
        if ($variantId) {
            $query->where('variant_id', $variantId);
        }
        
        return $query
            ->with(['children' => function ($query) {
                $query->with(['children' => function ($q) {
                    $q->with(['children' => function ($q2) {
                        $q2->orderBy('sort_order');
                    }])->orderBy('sort_order');
                }])->orderBy('sort_order');
            }])
            ->get();
    }

    public function storeVariant(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Get base variant to copy from
        $baseVariant = SiteTreeVariant::getBase();
        
        $variant = SiteTreeVariant::create([
            'name' => $validated['name'],
            'is_base' => false,
        ]);

        // Copy items from base variant
        if ($baseVariant) {
            $this->copyVariantItems($baseVariant->id, $variant->id);
        }

        return response()->json([
            'success' => true,
            'variant' => $variant,
            'url' => route('site-tree.variant', $variant->slug),
        ]);
    }

    private function copyVariantItems(int $sourceVariantId, int $targetVariantId, ?int $sourceParentId = null, ?int $targetParentId = null): void
    {
        $items = SiteTreeItem::where('variant_id', $sourceVariantId)
            ->where('parent_id', $sourceParentId)
            ->orderBy('sort_order')
            ->get();

        foreach ($items as $item) {
            $newItem = SiteTreeItem::create([
                'variant_id' => $targetVariantId,
                'parent_id' => $targetParentId,
                'title' => $item->title,
                'level' => $item->level,
                'is_checked' => $item->is_checked,
                'sort_order' => $item->sort_order,
            ]);

            $this->copyVariantItems($sourceVariantId, $targetVariantId, $item->id, $newItem->id);
        }
    }

    public function updateVariant(Request $request, SiteTreeVariant $variant): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $variant->update($validated);

        return response()->json([
            'success' => true,
            'variant' => $variant,
        ]);
    }

    public function destroyVariant(SiteTreeVariant $variant): JsonResponse
    {
        if ($variant->is_base) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete base variant',
            ], 422);
        }

        $variant->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function listVariants(): JsonResponse
    {
        $variants = SiteTreeVariant::orderBy('is_base', 'desc')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'variants' => $variants,
        ]);
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
            'variant_id' => 'nullable|integer|exists:site_tree_variants,id',
        ]);

        $variantId = $validated['variant_id'] ?? SiteTreeVariant::getBase()?->id;

        // Get the max sort_order for the parent within the same variant
        $maxSortOrder = SiteTreeItem::where('parent_id', $validated['parent_id'] ?? null)
            ->where('variant_id', $variantId)
            ->max('sort_order') ?? -1;

        $item = SiteTreeItem::create([
            'title' => $validated['title'],
            'level' => $validated['level'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'variant_id' => $variantId,
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
            $variantId = $item->variant_id;

            // If moving within the same parent
            if ($oldParentId === $newParentId) {
                if ($newSortOrder > $oldSortOrder) {
                    // Moving down
                    SiteTreeItem::where('parent_id', $oldParentId)
                        ->where('variant_id', $variantId)
                        ->where('sort_order', '>', $oldSortOrder)
                        ->where('sort_order', '<=', $newSortOrder)
                        ->decrement('sort_order');
                } elseif ($newSortOrder < $oldSortOrder) {
                    // Moving up
                    SiteTreeItem::where('parent_id', $oldParentId)
                        ->where('variant_id', $variantId)
                        ->where('sort_order', '>=', $newSortOrder)
                        ->where('sort_order', '<', $oldSortOrder)
                        ->increment('sort_order');
                }
            } else {
                // Moving to a different parent
                // Close gap in old parent
                SiteTreeItem::where('parent_id', $oldParentId)
                    ->where('variant_id', $variantId)
                    ->where('sort_order', '>', $oldSortOrder)
                    ->decrement('sort_order');

                // Make room in new parent
                SiteTreeItem::where('parent_id', $newParentId)
                    ->where('variant_id', $variantId)
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

    public function reset(Request $request): JsonResponse
    {
        $variantId = $request->input('variant_id');
        
        if ($variantId) {
            $variant = SiteTreeVariant::find($variantId);
            if ($variant && !$variant->is_base) {
                // Reset variant to base state by copying from base
                $baseVariant = SiteTreeVariant::getBase();
                if ($baseVariant) {
                    DB::transaction(function () use ($variant, $baseVariant) {
                        // Delete all items from this variant
                        SiteTreeItem::where('variant_id', $variant->id)->delete();
                        // Copy from base
                        $this->copyVariantItems($baseVariant->id, $variant->id);
                    });
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Variant reset to base state',
                ]);
            }
        }

        // Reset base variant using seeder
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\SiteTreeSeeder',
            '--force' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tree reset to original state',
        ]);
    }

    public function getTree(Request $request): JsonResponse
    {
        $variantId = $request->input('variant_id') ?? SiteTreeVariant::getBase()?->id;
        $tree = $this->getTreeWithChildren($variantId);

        return response()->json([
            'success' => true,
            'tree' => $tree,
        ]);
    }

    public function exportTree(Request $request): JsonResponse
    {
        $variantId = $request->input('variant_id') ?? SiteTreeVariant::getBase()?->id;
        $tree = $this->buildExportTree(null, $variantId);

        return response()->json([
            'success' => true,
            'tree' => $tree,
        ]);
    }

    private function buildExportTree(?int $parentId, ?int $variantId = null): array
    {
        $query = SiteTreeItem::where('parent_id', $parentId)->orderBy('sort_order');
        
        if ($variantId) {
            $query->where('variant_id', $variantId);
        }
        
        $items = $query->get();

        $result = [];
        foreach ($items as $item) {
            $node = [
                'title' => $item->title,
                'level' => $item->level,
                'is_checked' => $item->is_checked,
            ];
            
            $children = $this->buildExportTree($item->id, $variantId);
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
            'variant_id' => 'nullable|integer|exists:site_tree_variants,id',
        ]);

        $variantId = $validated['variant_id'] ?? SiteTreeVariant::getBase()?->id;

        DB::transaction(function () use ($validated, $variantId) {
            // Delete all items for this variant
            SiteTreeItem::where('variant_id', $variantId)->delete();
            $sortOrder = 0;
            $this->importTreeData($validated['tree'], null, $sortOrder, $variantId);
        });

        return response()->json([
            'success' => true,
            'message' => 'Tree imported successfully',
        ]);
    }

    private function importTreeData(array $items, ?int $parentId, int &$sortOrder, ?int $variantId = null): void
    {
        foreach ($items as $data) {
            $item = SiteTreeItem::create([
                'parent_id' => $parentId,
                'variant_id' => $variantId,
                'title' => $data['title'],
                'level' => $data['level'] ?? null,
                'is_checked' => $data['is_checked'] ?? true,
                'sort_order' => $sortOrder++,
            ]);

            if (!empty($data['children'])) {
                $childSortOrder = 0;
                $this->importTreeData($data['children'], $item->id, $childSortOrder, $variantId);
            }
        }
    }
}
