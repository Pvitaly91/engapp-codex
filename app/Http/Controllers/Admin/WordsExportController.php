<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Word;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WordsExportController extends Controller
{
    private const ALLOWED_LANGS = ['uk', 'pl', 'en'];

    private function resolveLang(Request $request): string
    {
        $lang = $request->input('lang', 'uk');

        if (! in_array($lang, self::ALLOWED_LANGS, true)) {
            return 'uk';
        }

        return $lang;
    }

    private function exportRelativePath(string $lang): string
    {
        return "exports/words/words_{$lang}.json";
    }

    private function exportFullPath(string $lang): string
    {
        return public_path($this->exportRelativePath($lang));
    }

    public function index(Request $request): View
    {
        $lang = $this->resolveLang($request);

        $filePath = $this->exportFullPath($lang);
        $fileExists = file_exists($filePath);

        return view('admin.words-export.index', [
            'lang' => $lang,
            'langs' => self::ALLOWED_LANGS,
            'fileExists' => $fileExists,
            'fileSize' => $fileExists ? filesize($filePath) : null,
            'lastModified' => $fileExists ? filemtime($filePath) : null,
            'publicUrl' => URL::to('/'.$this->exportRelativePath($lang)),
            'relativePath' => $this->exportRelativePath($lang),
        ]);
    }

    public function export(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lang' => ['required', Rule::in(self::ALLOWED_LANGS)],
        ]);

        $lang = $validated['lang'];

        $words = Word::with([
            'translates' => fn ($query) => $query->where('lang', $lang),
            'tags',
        ])->orderBy('id')->get();

        $withTranslation = [];
        $withoutTranslation = [];

        foreach ($words as $word) {
            $translation = optional($word->translates->first())->translation;
            $hasTranslation = $translation !== null && trim((string) $translation) !== '';

            $wordData = [
                'id' => $word->id,
                'word' => $word->word,
                'translation' => $hasTranslation ? trim($translation) : null,
                'type' => $word->type,
                'tags' => $word->tags->pluck('name')->values()->all(),
            ];

            if ($hasTranslation) {
                $withTranslation[] = $wordData;
            } else {
                $withoutTranslation[] = $wordData;
            }
        }

        $jsonData = [
            'exported_at' => now()->toIso8601String(),
            'lang' => $lang,
            'counts' => [
                'total_words' => $words->count(),
                'with_translation' => count($withTranslation),
                'without_translation' => count($withoutTranslation),
            ],
            'with_translation' => $withTranslation,
            'without_translation' => $withoutTranslation,
        ];

        $filePath = $this->exportFullPath($lang);
        $directory = dirname($filePath);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return redirect()
                ->route('admin.words.export.index', ['lang' => $lang])
                ->with('error', 'Не вдалося створити директорію для експорту.');
        }

        $result = file_put_contents($filePath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if ($result === false) {
            return redirect()
                ->route('admin.words.export.index', ['lang' => $lang])
                ->with('error', 'Не вдалося записати файл експорту.');
        }

        return redirect()
            ->route('admin.words.export.index', ['lang' => $lang])
            ->with('status', "Слова успішно експортовані у файл {$this->exportRelativePath($lang)}");
    }

    public function view(Request $request): View|RedirectResponse
    {
        $lang = $this->resolveLang($request);
        $filePath = $this->exportFullPath($lang);

        if (! file_exists($filePath)) {
            return redirect()
                ->route('admin.words.export.index', ['lang' => $lang])
                ->with('error', 'Файл експорту не знайдено. Спочатку виконайте експорт.');
        }

        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            return redirect()
                ->route('admin.words.export.index', ['lang' => $lang])
                ->with('error', 'Не вдалося прочитати файл експорту.');
        }

        $jsonData = json_decode($jsonContent, true);
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            return redirect()
                ->route('admin.words.export.index', ['lang' => $lang])
                ->with('error', 'Файл експорту містить некоректний JSON.');
        }

        return view('admin.words-export.view', [
            'jsonContent' => $jsonContent,
            'jsonData' => $jsonData,
            'filePath' => $this->exportRelativePath($lang),
            'fileSize' => filesize($filePath),
            'lastModified' => filemtime($filePath),
            'lang' => $lang,
            'publicUrl' => URL::to('/'.$this->exportRelativePath($lang)),
        ]);
    }

    public function download(Request $request): BinaryFileResponse|RedirectResponse
    {
        $lang = $this->resolveLang($request);
        $filePath = $this->exportFullPath($lang);

        if (! file_exists($filePath)) {
            return redirect()
                ->route('admin.words.export.index', ['lang' => $lang])
                ->with('error', 'Файл експорту не знайдено. Спочатку виконайте експорт.');
        }

        return response()->download($filePath, "words_{$lang}.json");
    }
}
