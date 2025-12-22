<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Word;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WordsExportController extends Controller
{
    private const ALLOWED_LANGS = ['uk', 'pl', 'en'];

    private function validateLang(?string $lang): string
    {
        if (!in_array($lang, self::ALLOWED_LANGS, true)) {
            return 'uk';
        }

        return $lang;
    }

    private function getExportFilePath(string $lang): string
    {
        return public_path("exports/words/words_{$lang}.json");
    }

    private function getPublicUrl(string $lang): string
    {
        return url("/exports/words/words_{$lang}.json");
    }

    public function index(Request $request): View
    {
        $lang = $this->validateLang($request->query('lang', 'uk'));
        $filePath = $this->getExportFilePath($lang);

        $fileExists = file_exists($filePath);
        $fileSize = $fileExists ? filesize($filePath) : 0;
        $lastModified = $fileExists ? filemtime($filePath) : null;
        $publicUrl = $this->getPublicUrl($lang);

        return view('admin.words-export.index', [
            'lang' => $lang,
            'allowedLangs' => self::ALLOWED_LANGS,
            'fileExists' => $fileExists,
            'fileSize' => $fileSize,
            'lastModified' => $lastModified,
            'publicUrl' => $publicUrl,
        ]);
    }

    public function export(Request $request): RedirectResponse
    {
        $lang = $this->validateLang($request->input('lang', 'uk'));

        // Load all words with translations for the selected language and tags
        $words = Word::with([
            'translates' => fn ($q) => $q->where('lang', $lang),
            'tags',
        ])->get();

        $withTranslation = [];
        $withoutTranslation = [];

        foreach ($words as $word) {
            $translate = $word->translates->first();
            $translation = $translate ? trim($translate->translation ?? '') : '';

            $wordData = [
                'id' => $word->id,
                'word' => $word->word,
                'translation' => $translation !== '' ? $translation : null,
                'type' => $word->type,
                'tags' => $word->tags->pluck('name')->all(),
            ];

            if ($translation !== '') {
                $withTranslation[] = $wordData;
            } else {
                $withoutTranslation[] = $wordData;
            }
        }

        $jsonData = [
            'exported_at' => now()->toIso8601String(),
            'lang' => $lang,
            'counts' => [
                'total_words' => count($words),
                'with_translation' => count($withTranslation),
                'without_translation' => count($withoutTranslation),
            ],
            'with_translation' => $withTranslation,
            'without_translation' => $withoutTranslation,
        ];

        $filePath = $this->getExportFilePath($lang);
        $directory = dirname($filePath);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return redirect()
                ->route('admin.words.export.index', ['lang' => $lang])
                ->with('error', 'Не вдалося створити директорію для експорту.');
        }

        $result = file_put_contents(
            $filePath,
            json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        if ($result === false) {
            return redirect()
                ->route('admin.words.export.index', ['lang' => $lang])
                ->with('error', 'Не вдалося записати файл експорту.');
        }

        $publicUrl = $this->getPublicUrl($lang);

        return redirect()
            ->route('admin.words.export.index', ['lang' => $lang])
            ->with('status', "Слова успішно експортовано для мови [{$lang}]. Публічний URL: {$publicUrl}");
    }

    public function view(Request $request): View|RedirectResponse
    {
        $lang = $this->validateLang($request->query('lang', 'uk'));
        $filePath = $this->getExportFilePath($lang);

        if (!file_exists($filePath)) {
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

        $relativePath = "public/exports/words/words_{$lang}.json";

        return view('admin.words-export.view', [
            'jsonContent' => $jsonContent,
            'jsonData' => $jsonData,
            'filePath' => $relativePath,
            'fileSize' => filesize($filePath),
            'lastModified' => filemtime($filePath),
            'publicUrl' => $this->getPublicUrl($lang),
            'lang' => $lang,
        ]);
    }

    public function download(Request $request): BinaryFileResponse|RedirectResponse
    {
        $lang = $this->validateLang($request->query('lang', 'uk'));
        $filePath = $this->getExportFilePath($lang);

        if (!file_exists($filePath)) {
            return redirect()
                ->route('admin.words.export.index', ['lang' => $lang])
                ->with('error', 'Файл експорту не знайдено. Спочатку виконайте експорт.');
        }

        return response()->download($filePath, "words_{$lang}.json");
    }
}
