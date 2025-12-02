<?php

namespace App\Modules\PromptGenerator\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class PromptGeneratorController extends Controller
{
    private const DEFAULT_LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    public function index(): View
    {
        return view('prompt-generator::index', $this->prepareViewData());
    }

    public function generate(Request $request): View
    {
        $validated = $request->validate([
            'topic_name' => ['required', 'string', 'max:255'],
            'optional_theory_url' => ['nullable', 'string', 'max:500'],
            'base_seeder_class' => ['required', 'array', 'min:1'],
            'base_seeder_class.*' => ['string', 'max:255'],
            'new_seeder_namespace_path' => ['required', 'string', 'max:255'],
            'new_seeder_class_name' => ['nullable', 'string', 'max:255'],
            'levels' => ['array'],
            'levels.*' => ['string', 'max:10'],
            'custom_levels' => ['nullable', 'string', 'max:255'],
            'questions_per_level' => ['required', 'integer', 'min:1', 'max:200'],
            'hints_language' => ['required', 'string', 'max:100'],
        ]);

        $levels = $this->prepareLevels($validated['levels'] ?? [], $validated['custom_levels'] ?? '');

        $baseSeeders = collect($validated['base_seeder_class'])
            ->map(fn ($value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($baseSeeders)) {
            $baseSeeders = self::defaultState()['base_seeder_classes'];
        }

        $state = [
            'topic_name' => $validated['topic_name'],
            'optional_theory_url' => $validated['optional_theory_url'] ?: 'none',
            'base_seeder_classes' => $baseSeeders,
            'new_seeder_namespace_path' => $validated['new_seeder_namespace_path'],
            'new_seeder_class_name' => $validated['new_seeder_class_name'] ?? '',
            'levels' => $levels,
            'custom_levels' => $validated['custom_levels'] ?? '',
            'questions_per_level' => (int) $validated['questions_per_level'],
            'hints_language' => $validated['hints_language'],
            'total_questions' => count($levels) * (int) $validated['questions_per_level'],
        ];

        $prompt = view('prompt-generator::prompt', [
            'topicName' => $state['topic_name'],
            'theoryUrl' => $state['optional_theory_url'],
            'baseSeederClasses' => $state['base_seeder_classes'],
            'newSeederNamespacePath' => $state['new_seeder_namespace_path'],
            'newSeederClassName' => $state['new_seeder_class_name'],
            'levels' => $state['levels'],
            'questionsPerLevel' => $state['questions_per_level'],
            'hintsLanguage' => $state['hints_language'],
            'totalQuestions' => $state['total_questions'],
        ])->render();

        return view('prompt-generator::index', $this->prepareViewData($state, $prompt));
    }

    private function prepareViewData(array $state = [], ?string $prompt = null): array
    {
        $defaults = $this->defaultState();
        $state = array_merge($defaults, $state);

        return [
            'state' => $state,
            'generatedPrompt' => $prompt,
            'topicOptions' => $this->topics(),
            'theoryOptions' => $this->theoryLinks(),
            'seederClasses' => $this->seederClasses(),
            'seederNamespaces' => $this->seederNamespaces(),
            'levelOptions' => self::DEFAULT_LEVELS,
            'languageOptions' => $this->languageOptions(),
        ];
    }

    private function defaultState(): array
    {
        return [
            'topic_name' => 'Mixed Conditionals',
            'optional_theory_url' => 'https://gramlyze.com/pages/conditions/mixed-conditional',
            'base_seeder_classes' => ['V2\\ConditionalsMixedPracticeV2Seeder'],
            'new_seeder_namespace_path' => 'database/seeders/AI/Claude',
            'new_seeder_class_name' => 'MixedConditionalsAIGeneratedSeeder',
            'levels' => self::DEFAULT_LEVELS,
            'custom_levels' => '',
            'questions_per_level' => 12,
            'hints_language' => config('prompt-generator.default_language', 'Ukrainian'),
            'total_questions' => count(self::DEFAULT_LEVELS) * 12,
        ];
    }

    private function topics(): Collection
    {
        $tags = Tag::query()->orderBy('name')->pluck('name');
        $categories = Category::query()->orderBy('name')->pluck('name');

        return $tags->merge($categories)
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function theoryLinks(): Collection
    {
        return Page::query()
            ->with('category')
            ->where('type', 'theory')
            ->get()
            ->filter(fn (Page $page) => $page->category)
            ->map(function (Page $page) {
                return [
                    'title' => $page->title,
                    'url' => route('theory.show', [$page->category->slug, $page->slug]),
                ];
            })
            ->unique('url')
            ->sortBy('title')
            ->values();
    }

    private function seederClasses(): Collection
    {
        return collect(File::allFiles(database_path('seeders')))
            ->map(fn (SplFileInfo $file) => $this->extractClassFqn($file))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function seederNamespaces(): Collection
    {
        $seedersPath = database_path('seeders');

        return collect(File::allFiles($seedersPath))
            ->map(function (SplFileInfo $file) use ($seedersPath) {
                return Str::of($file->getPath())
                    ->after($seedersPath)
                    ->trim('/');
            })
            ->filter()
            ->unique()
            ->sort()
            ->map(fn ($path) => $path ? 'database/seeders/' . $path : 'database/seeders')
            ->values();
    }

    private function languageOptions(): array
    {
        return ['Ukrainian', 'English', 'Polish', 'Spanish', 'German'];
    }

    private function extractClassFqn(SplFileInfo $file): ?string
    {
        $contents = File::get($file->getPathname());

        if (! preg_match('/namespace\s+([^;]+);/m', $contents, $namespaceMatch)) {
            return null;
        }

        if (! preg_match('/class\s+(\w+)/m', $contents, $classMatch)) {
            return null;
        }

        return trim($namespaceMatch[1]) . '\\' . trim($classMatch[1]);
    }

    private function prepareLevels(array $levels, string $customLevels): array
    {
        $custom = Str::of($customLevels)
            ->split('/[,;]+|\n+/')
            ->filter()
            ->map(fn ($value) => trim($value))
            ->filter();

        $merged = collect($levels)
            ->merge($custom)
            ->filter()
            ->unique()
            ->values();

        if ($merged->isEmpty()) {
            return self::DEFAULT_LEVELS;
        }

        return $merged->all();
    }
}
