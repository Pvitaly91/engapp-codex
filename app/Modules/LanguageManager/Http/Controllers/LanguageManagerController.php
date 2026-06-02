<?php

namespace App\Modules\LanguageManager\Http\Controllers;

use App\Modules\LanguageManager\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class LanguageManagerController extends Controller
{
    /**
     * Display a listing of languages.
     */
    public function index()
    {
        $languages = Language::orderBy('sort_order')->get();
        
        return view('language-manager::index', compact('languages'));
    }

    /**
     * Show the form for creating a new language.
     */
    public function create()
    {
        return view('language-manager::create');
    }

    /**
     * Store a newly created language.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'regex:/^[a-z]{2}(-[A-Z]{2})?$/', 'unique:languages,code'],
            'name' => ['required', 'string', 'max:255'],
            'native_name' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ], [
            'code.regex' => 'Код мови має бути у форматі ISO 639-1 (наприклад: uk, en, pt-BR)',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // If this is the first language or is_default is true, make it default
        if ($validated['is_default'] || Language::count() === 0) {
            Language::where('is_default', true)->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        Language::create($validated);

        return redirect()
            ->route('language-manager.index')
            ->with('success', 'Мову успішно додано.');
    }

    /**
     * Show the form for editing a language.
     */
    public function edit(Language $language)
    {
        return view('language-manager::edit', compact('language'));
    }

    /**
     * Update the specified language.
     */
    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'regex:/^[a-z]{2}(-[A-Z]{2})?$/', Rule::unique('languages', 'code')->ignore($language->id)],
            'name' => ['required', 'string', 'max:255'],
            'native_name' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ], [
            'code.regex' => 'Код мови має бути у форматі ISO 639-1 (наприклад: uk, en, pt-BR)',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        // If setting as default, remove default from others
        if ($validated['is_default']) {
            Language::where('is_default', true)
                ->where('id', '!=', $language->id)
                ->update(['is_default' => false]);
        }

        // Prevent deactivating or removing default from the only default language
        if ($language->is_default && !$validated['is_default']) {
            $otherDefault = Language::where('is_default', true)
                ->where('id', '!=', $language->id)
                ->exists();
            
            if (!$otherDefault) {
                return back()
                    ->withInput()
                    ->withErrors(['is_default' => 'Має бути хоча б одна мова за замовчуванням.']);
            }
        }

        $language->update($validated);

        return redirect()
            ->route('language-manager.index')
            ->with('success', 'Мову успішно оновлено.');
    }

    /**
     * Remove the specified language.
     */
    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return back()->with('error', 'Неможливо видалити мову за замовчуванням. Спочатку встановіть іншу мову за замовчуванням.');
        }

        $language->delete();

        return redirect()
            ->route('language-manager.index')
            ->with('success', 'Мову успішно видалено.');
    }

    /**
     * Set a language as default.
     */
    public function setDefault(Language $language)
    {
        $language->setAsDefault();

        return redirect()
            ->route('language-manager.index')
            ->with('success', "Мову \"{$language->name}\" встановлено за замовчуванням.");
    }

    /**
     * Toggle language active status.
     */
    public function toggleActive(Language $language)
    {
        if ($language->is_default && $language->is_active) {
            return back()->with('error', 'Неможливо деактивувати мову за замовчуванням.');
        }

        $language->update(['is_active' => !$language->is_active]);

        $status = $language->is_active ? 'активовано' : 'деактивовано';
        
        return redirect()
            ->route('language-manager.index')
            ->with('success', "Мову \"{$language->name}\" {$status}.");
    }

    /**
     * Update sort order via AJAX.
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:languages,id'],
        ]);

        foreach ($validated['order'] as $index => $id) {
            Language::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
