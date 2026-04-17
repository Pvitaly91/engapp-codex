<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TheoryVariant;
use App\Models\TheoryVariantSelection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TheoryVariantController extends Controller
{
    public function publish(TheoryVariant $theoryVariant): RedirectResponse
    {
        abort_unless($theoryVariant->status === 'ready', 404);

        TheoryVariantSelection::query()->updateOrCreate(
            [
                'variantable_type' => $theoryVariant->variantable_type,
                'variantable_id' => $theoryVariant->variantable_id,
                'locale' => $theoryVariant->locale,
            ],
            [
                'theory_variant_id' => $theoryVariant->getKey(),
            ]
        );

        return back()->with('status', __('Варіант ":label" опубліковано.', ['label' => $theoryVariant->label]));
    }

    public function resetToBase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_type' => ['required', 'string', Rule::in(['page', 'category'])],
            'target_id' => ['required', 'integer'],
            'locale' => ['required', 'string', 'max:12'],
        ]);

        $modelClass = $validated['target_type'] === 'page' ? Page::class : PageCategory::class;
        $target = $modelClass::query()->findOrFail($validated['target_id']);

        TheoryVariantSelection::query()->updateOrCreate(
            [
                'variantable_type' => $modelClass,
                'variantable_id' => $target->getKey(),
                'locale' => strtolower($validated['locale']),
            ],
            [
                'theory_variant_id' => null,
            ]
        );

        return back()->with('status', __('Публічний показ скинуто до базової версії.'));
    }

    public function destroy(TheoryVariant $theoryVariant): RedirectResponse
    {
        $label = $theoryVariant->label;
        $theoryVariant->delete();

        return back()->with('status', __('Варіант ":label" видалено.', ['label' => $label]));
    }
}
