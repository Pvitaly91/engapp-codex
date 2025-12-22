<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\Translate;
use App\Models\Word;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'json_file' => 'required|file|mimes:json|max:10240',
        ]);

        $file = $request->file('json_file');
        $overwriteTranslations = $request->boolean('overwrite_translations');
        
        // Additional size check before reading
        if ($file->getSize() > 10 * 1024 * 1024) {
            return redirect()
                ->route('admin.words.export.index')
                ->with('error', 'Файл занадто великий (максимум 10MB).');
        }
        
        $jsonContent = file_get_contents($file->getRealPath());

        if ($jsonContent === false) {
            return redirect()
                ->route('admin.words.export.index')
                ->with('error', 'Не вдалося прочитати файл.');
        }

        $data = json_decode($jsonContent, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return redirect()
                ->route('admin.words.export.index')
                ->with('error', 'Файл містить некоректний JSON: ' . json_last_error_msg());
        }

        // Validate JSON structure
        if (!isset($data['lang']) || !in_array($data['lang'], self::ALLOWED_LANGS, true)) {
            return redirect()
                ->route('admin.words.export.index')
                ->with('error', 'JSON не містить коректного поля "lang" (uk, pl, en).');
        }

        $lang = $data['lang'];
        $allWords = array_merge($data['with_translation'] ?? [], $data['without_translation'] ?? []);

        if (empty($allWords)) {
            return redirect()
                ->route('admin.words.export.index', ['lang' => $lang])
                ->with('error', 'JSON не містить слів для імпорту.');
        }

        $stats = [
            'words_created' => 0,
            'words_skipped' => 0,
            'translations_created' => 0,
            'translations_updated' => 0,
            'translations_overwritten' => 0,
            'translations_deleted' => 0,
            'translations_skipped' => 0,
            'tags_created' => 0,
            'tags_attached' => 0,
        ];

        DB::beginTransaction();

        try {
            // Pre-load existing words by word text for faster lookup
            $existingWords = Word::pluck('id', 'word')->all();

            // Pre-load existing tags by name for faster lookup
            $existingTags = Tag::pluck('id', 'name')->all();
            
            // Pre-load existing word-tag relationships
            $wordTagRelations = DB::table('tag_word')
                ->select('word_id', 'tag_id')
                ->get()
                ->groupBy('word_id')
                ->map(fn ($items) => $items->pluck('tag_id')->all())
                ->all();

            foreach ($allWords as $wordData) {
                if (!isset($wordData['word']) || trim($wordData['word']) === '') {
                    continue;
                }

                $wordText = trim($wordData['word']);
                $wordType = $wordData['type'] ?? null;
                // Get translation - array_key_exists to detect null vs missing key
                $translation = array_key_exists('translation', $wordData) ? $wordData['translation'] : null;
                $tagNames = $wordData['tags'] ?? [];

                // Check if word exists
                if (isset($existingWords[$wordText])) {
                    $wordId = $existingWords[$wordText];
                    $stats['words_skipped']++;
                } else {
                    // Create new word
                    $word = Word::create([
                        'word' => $wordText,
                        'type' => $wordType,
                    ]);
                    $wordId = $word->id;
                    $existingWords[$wordText] = $wordId;
                    $stats['words_created']++;
                }

                // Handle translation based on overwrite mode
                if ($overwriteTranslations) {
                    // Overwrite mode: update/delete/create translations based on JSON
                    $existingTranslate = Translate::where('word_id', $wordId)
                        ->where('lang', $lang)
                        ->first();

                    if ($translation === null) {
                        // translation is null in JSON - delete existing translation
                        if ($existingTranslate) {
                            $existingTranslate->delete();
                            $stats['translations_deleted']++;
                        }
                    } else {
                        $translationText = is_string($translation) ? trim($translation) : '';
                        
                        if ($translationText !== '') {
                            if ($existingTranslate) {
                                // Overwrite existing translation
                                if ($existingTranslate->translation !== $translationText) {
                                    $existingTranslate->update(['translation' => $translationText]);
                                    $stats['translations_overwritten']++;
                                } else {
                                    $stats['translations_skipped']++;
                                }
                            } else {
                                // Create new translation
                                Translate::create([
                                    'word_id' => $wordId,
                                    'lang' => $lang,
                                    'translation' => $translationText,
                                ]);
                                $stats['translations_created']++;
                            }
                        }
                    }
                } else {
                    // Normal mode: only create/update empty translations
                    if ($translation !== null) {
                        $translationText = is_string($translation) ? trim($translation) : '';
                        
                        if ($translationText !== '') {
                            $existingTranslate = Translate::where('word_id', $wordId)
                                ->where('lang', $lang)
                                ->first();

                            if ($existingTranslate) {
                                if (trim($existingTranslate->translation ?? '') === '') {
                                    // Update empty translation
                                    $existingTranslate->update(['translation' => $translationText]);
                                    $stats['translations_updated']++;
                                } else {
                                    // Translation already exists and is not empty
                                    $stats['translations_skipped']++;
                                }
                            } else {
                                // Create new translation
                                Translate::create([
                                    'word_id' => $wordId,
                                    'lang' => $lang,
                                    'translation' => $translationText,
                                ]);
                                $stats['translations_created']++;
                            }
                        }
                    }
                }

                // Handle tags
                if (!empty($tagNames) && is_array($tagNames)) {
                    $tagIds = [];

                    foreach ($tagNames as $tagName) {
                        $tagName = trim($tagName);
                        if ($tagName === '') {
                            continue;
                        }

                        if (isset($existingTags[$tagName])) {
                            $tagIds[] = $existingTags[$tagName];
                        } else {
                            // Create new tag
                            $tag = Tag::create(['name' => $tagName]);
                            $existingTags[$tagName] = $tag->id;
                            $tagIds[] = $tag->id;
                            $stats['tags_created']++;
                        }
                    }

                    if (!empty($tagIds)) {
                        // Use pre-loaded word-tag relationships instead of querying
                        $existingTagIdsForWord = $wordTagRelations[$wordId] ?? [];
                        $newTagIds = array_diff($tagIds, $existingTagIdsForWord);

                        if (!empty($newTagIds)) {
                            // Batch insert new tag relationships
                            $insertData = array_map(fn ($tagId) => [
                                'word_id' => $wordId,
                                'tag_id' => $tagId,
                            ], $newTagIds);
                            
                            DB::table('tag_word')->insert($insertData);
                            
                            // Update the cache for future iterations
                            $wordTagRelations[$wordId] = array_merge($existingTagIdsForWord, $newTagIds);
                            
                            $stats['tags_attached'] += count($newTagIds);
                        }
                    }
                }
            }

            DB::commit();

            if ($overwriteTranslations) {
                $message = sprintf(
                    'Імпорт завершено для мови [%s] (режим перезапису). Слів створено: %d, пропущено: %d. Перекладів створено: %d, перезаписано: %d, видалено: %d, без змін: %d. Тегів створено: %d, прив\'язано: %d.',
                    $lang,
                    $stats['words_created'],
                    $stats['words_skipped'],
                    $stats['translations_created'],
                    $stats['translations_overwritten'],
                    $stats['translations_deleted'],
                    $stats['translations_skipped'],
                    $stats['tags_created'],
                    $stats['tags_attached']
                );
            } else {
                $message = sprintf(
                    'Імпорт завершено для мови [%s]. Слів створено: %d, пропущено: %d. Перекладів створено: %d, оновлено: %d, пропущено: %d. Тегів створено: %d, прив\'язано: %d.',
                    $lang,
                    $stats['words_created'],
                    $stats['words_skipped'],
                    $stats['translations_created'],
                    $stats['translations_updated'],
                    $stats['translations_skipped'],
                    $stats['tags_created'],
                    $stats['tags_attached']
                );
            }

            return redirect()
                ->route('admin.words.export.index', ['lang' => $lang])
                ->with('status', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('admin.words.export.index')
                ->with('error', 'Помилка імпорту: ' . $e->getMessage());
        }
    }
}
