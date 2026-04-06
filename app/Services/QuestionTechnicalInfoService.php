<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuestionTechnicalInfoService
{
    private const KNOWN_LLM_SEGMENTS = [
        'chatgpt' => 'ChatGPT',
        'chatgptpro' => 'ChatGPT Pro',
        'gemini' => 'Gemini',
        'copilot' => 'Copilot',
        'codex' => 'Codex',
        'claude' => 'Claude',
        'openai' => 'OpenAI',
        'anthropic' => 'Anthropic',
    ];

    public function mapForQuestions(Collection $questions): array
    {
        $seederClasses = $questions
            ->pluck('seeder')
            ->filter(fn ($seeder) => is_string($seeder) && trim($seeder) !== '')
            ->unique()
            ->values();

        $lastSeedRuns = $this->lastSeedRunsByClass($seederClasses);

        return $questions
            ->mapWithKeys(function (Question $question) use ($lastSeedRuns) {
                return [
                    $question->id => $this->buildTechnicalInfo(
                        $question,
                        $lastSeedRuns[$question->seeder] ?? null
                    ),
                ];
            })
            ->all();
    }

    private function buildTechnicalInfo(Question $question, ?string $lastSeedRunAt): array
    {
        $markers = $this->extractMarkers($question);
        $typeValue = $question->type === null ? null : (string) $question->type;
        $typeLabel = $typeValue === null ? null : (Question::typeLabels()[$typeValue] ?? null);
        $tags = $question->relationLoaded('tags')
            ? $question->tags
                ->pluck('name')
                ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
                ->values()
                ->all()
            : [];

        return [
            'question_id' => $question->id,
            'question_uuid' => $question->uuid,
            'difficulty' => $question->difficulty,
            'level' => $question->level,
            'category' => $question->category?->name,
            'source' => [
                'id' => $question->source_id,
                'name' => $question->source?->name,
            ],
            'seeder' => $this->formatSeeder($question->seeder, $lastSeedRunAt),
            'type' => [
                'value' => $typeValue,
                'label' => $typeLabel,
            ],
            'markers' => $markers,
            'markers_count' => count($markers),
            'answers_count' => $question->relationLoaded('answers') ? $question->answers->count() : null,
            'options_count' => $question->relationLoaded('options') ? $question->options->count() : null,
            'variants_count' => $question->relationLoaded('variants') ? $question->variants->count() : null,
            'theory_text_block_uuid' => $question->theory_text_block_uuid,
            'tags' => $tags,
            'created_at' => $this->formatDateTime($question->created_at),
            'updated_at' => $this->formatDateTime($question->updated_at),
        ];
    }

    private function formatSeeder(?string $seederClass, ?string $lastSeedRunAt): ?array
    {
        if (! is_string($seederClass) || trim($seederClass) === '') {
            return null;
        }

        $normalized = str_replace('/', '\\', trim($seederClass));
        $segments = array_values(array_filter(explode('\\', $normalized), fn ($segment) => $segment !== ''));
        $className = $segments !== [] ? end($segments) : $normalized;

        $v3Meta = $this->parseV3Seeder($segments);

        return [
            'class' => $normalized,
            'name' => $className,
            'last_ran_at' => $lastSeedRunAt,
            'is_v3' => $v3Meta['is_v3'],
            'v3_namespace' => $v3Meta['namespace'],
            'v3_path_after_ai' => $v3Meta['path_after_ai'],
            'llm' => $v3Meta['llm'],
            'llm_display_name' => $v3Meta['llm_display_name'],
        ];
    }

    private function parseV3Seeder(array $segments): array
    {
        $result = [
            'is_v3' => false,
            'namespace' => null,
            'path_after_ai' => null,
            'llm' => null,
            'llm_display_name' => null,
        ];

        $lowerSegments = array_map('strtolower', $segments);
        $v3Index = array_search('v3', $lowerSegments, true);

        if ($v3Index === false) {
            return $result;
        }

        $result['is_v3'] = true;
        $classlessSegments = array_slice($segments, 0, -1);
        $classlessLowerSegments = array_map('strtolower', $classlessSegments);
        $aiIndex = array_search('ai', $classlessLowerSegments, true);

        if ($aiIndex === false || $aiIndex < $v3Index) {
            return $result;
        }

        $namespaceSegments = array_slice($classlessSegments, $aiIndex + 1);
        if ($namespaceSegments === []) {
            return $result;
        }

        $result['namespace'] = implode('\\', $namespaceSegments);
        $result['path_after_ai'] = implode('/', $namespaceSegments);

        $llmSegment = $this->detectLlmSegment($namespaceSegments);
        if ($llmSegment !== null) {
            $result['llm'] = $llmSegment;
            $result['llm_display_name'] = $this->humanizeLlmSegment($llmSegment);
        }

        return $result;
    }

    private function detectLlmSegment(array $namespaceSegments): ?string
    {
        $matched = null;

        foreach ($namespaceSegments as $segment) {
            $normalized = strtolower((string) $segment);
            if (array_key_exists($normalized, self::KNOWN_LLM_SEGMENTS)) {
                $matched = $segment;
            }
        }

        if ($matched !== null) {
            return $matched;
        }

        $lastSegment = end($namespaceSegments);

        return is_string($lastSegment) && trim($lastSegment) !== '' ? $lastSegment : null;
    }

    private function humanizeLlmSegment(string $segment): string
    {
        $normalized = strtolower($segment);

        if (array_key_exists($normalized, self::KNOWN_LLM_SEGMENTS)) {
            return self::KNOWN_LLM_SEGMENTS[$normalized];
        }

        return preg_replace('/(?<!^)([A-Z])/', ' $1', $segment) ?? $segment;
    }

    private function extractMarkers(Question $question): array
    {
        $markers = collect();

        if (is_string($question->question)
            && preg_match_all('/\{(a\d+)\}/i', $question->question, $matches)) {
            $markers = $markers->merge($matches[1]);
        }

        if ($question->relationLoaded('answers')) {
            $markers = $markers->merge($question->answers->pluck('marker'));
        }

        return $markers
            ->filter(fn ($marker) => is_string($marker) && trim($marker) !== '')
            ->map(fn ($marker) => strtolower(trim($marker)))
            ->unique()
            ->values()
            ->all();
    }

    private function lastSeedRunsByClass(Collection $seederClasses): array
    {
        if ($seederClasses->isEmpty()
            || ! Schema::hasTable('seed_runs')
            || ! Schema::hasColumn('seed_runs', 'class_name')
            || ! Schema::hasColumn('seed_runs', 'ran_at')) {
            return [];
        }

        return DB::table('seed_runs')
            ->select('class_name', DB::raw('MAX(ran_at) as last_ran_at'))
            ->whereIn('class_name', $seederClasses->all())
            ->groupBy('class_name')
            ->pluck('last_ran_at', 'class_name')
            ->toArray();
    }

    private function formatDateTime(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (method_exists($value, 'toDateTimeString')) {
            return $value->toDateTimeString();
        }

        return is_string($value) ? $value : null;
    }
}
