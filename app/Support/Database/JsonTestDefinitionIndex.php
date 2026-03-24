<?php

namespace App\Support\Database;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class JsonTestDefinitionIndex
{
    private const UUID_LENGTH = 36;

    public function loadDefinitionFromFile(string $path): array
    {
        if (! File::exists($path)) {
            throw new RuntimeException("V3 test definition not found: {$path}");
        }

        $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid V3 test definition: {$path}");
        }

        return $decoded;
    }

    public function definitionKeyFromPath(?string $path): ?string
    {
        $normalized = trim((string) $path);

        if ($normalized === '') {
            return null;
        }

        return pathinfo($normalized, PATHINFO_FILENAME) ?: null;
    }

    public function resolveSeederClassName(array $definition, ?string $fallbackSeederClass = null): string
    {
        $configured = trim((string) Arr::get($definition, 'seeder.class', ''));

        if ($configured !== '') {
            return $configured;
        }

        return trim((string) $fallbackSeederClass);
    }

    public function resolveUuidNamespace(array $definition, string $seederClass): string
    {
        $configured = trim((string) Arr::get($definition, 'seeder.uuid_namespace', ''));

        if ($configured !== '') {
            return $configured;
        }

        return Str::afterLast($seederClass, '\\');
    }

    public function indexQuestions(
        array $definition,
        ?string $definitionPath = null,
        ?string $fallbackSeederClass = null,
    ): array {
        $questions = Arr::get($definition, 'questions', []);

        if (! is_array($questions)) {
            throw new RuntimeException('V3 test definition must contain a questions array.');
        }

        $seederClass = $this->resolveSeederClassName($definition, $fallbackSeederClass);
        $uuidNamespace = $this->resolveUuidNamespace($definition, $seederClass);
        $definitionKey = $this->definitionKeyFromPath($definitionPath);

        $items = [];
        $byUuid = [];
        $byId = [];
        $byQuestion = [];
        $byIndex = [];

        foreach ($questions as $index => $questionDefinition) {
            if (! is_array($questionDefinition)) {
                continue;
            }

            $questionText = trim((string) ($questionDefinition['question'] ?? ''));
            $normalizedMarkers = $this->normalizeMarkers($questionDefinition);
            $answers = [];

            foreach ($normalizedMarkers as $marker => $markerData) {
                $answers[$marker] = $markerData['answer'];
            }

            $uuid = $this->resolveQuestionUuid(
                $questionDefinition,
                $uuidNamespace,
                $index,
                $questionText
            );

            $definitionIndex = $index + 1;
            $resolvedId = array_key_exists('id', $questionDefinition)
                ? (string) $questionDefinition['id']
                : (string) $definitionIndex;

            $items[$uuid] = [
                'uuid' => $uuid,
                'id' => $resolvedId,
                'index' => $definitionIndex,
                'question' => $questionText,
                'answers' => $answers,
                'markers' => array_keys($normalizedMarkers),
                'option_markers' => array_replace(
                    $this->buildOptionMarkerMapFromMarkers($normalizedMarkers),
                    $this->normalizeOptionMarkerMap($questionDefinition['option_markers'] ?? [])
                ),
                'definition_path' => $definitionPath,
                'definition_key' => $definitionKey,
                'seeder_class' => $seederClass,
            ];

            $byUuid[$uuid] = $uuid;
            $byId[$resolvedId] = $uuid;
            $byIndex[(string) $definitionIndex] = $uuid;

            if ($questionText !== '' && ! array_key_exists($questionText, $byQuestion)) {
                $byQuestion[$questionText] = $uuid;
            }
        }

        return [
            'items' => $items,
            'by_uuid' => $byUuid,
            'by_id' => $byId,
            'by_question' => $byQuestion,
            'by_index' => $byIndex,
            'definition_key' => $definitionKey,
            'definition_path' => $definitionPath,
            'seeder_class' => $seederClass,
        ];
    }

    public function resolveIndexedQuestion(array $index, array $questionReference): ?array
    {
        $resolvedUuid = $this->resolveIndexedQuestionUuid($index, $questionReference);

        if ($resolvedUuid === null) {
            return null;
        }

        return $index['items'][$resolvedUuid] ?? null;
    }

    public function resolveIndexedQuestionUuid(array $index, array $questionReference): ?string
    {
        $explicitUuid = trim((string) ($questionReference['uuid'] ?? ''));

        if ($explicitUuid !== '' && array_key_exists($explicitUuid, $index['by_uuid'] ?? [])) {
            return $explicitUuid;
        }

        if (array_key_exists('id', $questionReference)) {
            $id = trim((string) $questionReference['id']);

            if ($id !== '' && array_key_exists($id, $index['by_id'] ?? [])) {
                return $index['by_id'][$id];
            }
        }

        $questionText = trim((string) ($questionReference['question'] ?? ''));

        if ($questionText !== '' && array_key_exists($questionText, $index['by_question'] ?? [])) {
            return $index['by_question'][$questionText];
        }

        if (array_key_exists('index', $questionReference)) {
            $definitionIndex = trim((string) $questionReference['index']);

            if ($definitionIndex !== '' && array_key_exists($definitionIndex, $index['by_index'] ?? [])) {
                return $index['by_index'][$definitionIndex];
            }
        }

        return null;
    }

    private function normalizeMarkers(array $questionDefinition): array
    {
        $normalized = [];
        $markers = $questionDefinition['markers'] ?? null;

        if (is_array($markers) && $markers !== []) {
            foreach ($markers as $marker => $markerDefinition) {
                $markerName = trim((string) $marker);

                if ($markerName === '' || ! is_array($markerDefinition)) {
                    continue;
                }

                $answer = trim((string) ($markerDefinition['answer'] ?? ''));

                if ($answer === '') {
                    continue;
                }

                $options = $this->normalizeOptionList($markerDefinition['options'] ?? []);

                if (! in_array($answer, $options, true)) {
                    $options[] = $answer;
                }

                $normalized[$markerName] = [
                    'answer' => $answer,
                    'options' => $options,
                ];
            }
        }

        if ($normalized !== []) {
            uksort($normalized, 'strnatcmp');

            return $normalized;
        }

        $answers = $questionDefinition['answers'] ?? null;

        if (! is_array($answers) || $answers === []) {
            if (array_key_exists('correct', $questionDefinition)) {
                $answers = ['a1' => $questionDefinition['correct']];
            } else {
                return [];
            }
        }

        $sharedOptions = $this->normalizeOptionList($questionDefinition['options'] ?? []);
        $optionsByMarker = is_array($questionDefinition['options_by_marker'] ?? null)
            ? $questionDefinition['options_by_marker']
            : [];

        foreach ($answers as $marker => $answerDefinition) {
            $markerName = trim((string) $marker);
            $answer = '';

            if (is_array($answerDefinition)) {
                $markerName = trim((string) ($answerDefinition['marker'] ?? $markerName));
                $answer = trim((string) ($answerDefinition['answer'] ?? ''));
            } else {
                $answer = trim((string) $answerDefinition);
            }

            if ($markerName === '' || $answer === '') {
                continue;
            }

            $markerOptions = $this->normalizeOptionList($optionsByMarker[$markerName] ?? $sharedOptions);

            if (! in_array($answer, $markerOptions, true)) {
                $markerOptions[] = $answer;
            }

            $normalized[$markerName] = [
                'answer' => $answer,
                'options' => $markerOptions,
            ];
        }

        uksort($normalized, 'strnatcmp');

        return $normalized;
    }

    private function resolveQuestionUuid(
        array $questionDefinition,
        string $uuidNamespace,
        int $index,
        string $questionText,
    ): string {
        $explicitUuid = trim((string) ($questionDefinition['uuid'] ?? ''));

        if ($explicitUuid !== '') {
            return $explicitUuid;
        }

        $segments = $questionDefinition['uuid_segments'] ?? null;

        if (is_array($segments) && $segments !== []) {
            return $this->generateScopedQuestionUuid($uuidNamespace, ...$segments);
        }

        $idSegment = $questionDefinition['id'] ?? ($index + 1);

        return $this->generateScopedQuestionUuid($uuidNamespace, $idSegment, $questionText);
    }

    private function generateScopedQuestionUuid(string $scope, int|string ...$segments): string
    {
        $base = Str::slug(Str::afterLast($scope, '\\'));

        $normalizedSegments = [];

        foreach ($segments as $segment) {
            $segment = Str::slug((string) $segment);

            if ($segment === '') {
                continue;
            }

            $normalizedSegments[] = $segment;
        }

        $suffix = $normalizedSegments !== [] ? '-' . implode('-', $normalizedSegments) : '';
        $maxLength = self::UUID_LENGTH - strlen($suffix);

        if ($maxLength <= 0) {
            return substr(ltrim($suffix, '-'), 0, self::UUID_LENGTH);
        }

        $base = substr($base, 0, $maxLength);

        if ($base === '') {
            return substr(ltrim($suffix, '-'), 0, self::UUID_LENGTH);
        }

        return $base . $suffix;
    }

    private function buildOptionMarkerMapFromMarkers(array $markers): array
    {
        $map = [];

        foreach ($markers as $marker => $markerDefinition) {
            foreach ($markerDefinition['options'] ?? [] as $option) {
                $option = trim((string) $option);

                if ($option !== '' && ! array_key_exists($option, $map)) {
                    $map[$option] = $marker;
                }
            }
        }

        return $map;
    }

    private function normalizeOptionMarkerMap(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $normalized = [];

        foreach ($payload as $option => $marker) {
            $optionName = trim((string) $option);
            $markerName = trim((string) $marker);

            if ($optionName === '' || $markerName === '') {
                continue;
            }

            $normalized[$optionName] = $markerName;
        }

        return $normalized;
    }

    private function normalizeOptionList(mixed $options): array
    {
        if (! is_array($options)) {
            return [];
        }

        $normalized = [];

        foreach ($options as $option) {
            $value = trim((string) $option);

            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        return $normalized;
    }
}
