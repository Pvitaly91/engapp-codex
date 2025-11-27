<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * A virtual (non-persisted) representation of a grammar test.
 * 
 * This class mimics SavedGrammarTest but doesn't save to the database.
 * It's used for auto-generated tests on pages that should be displayed
 * based on filters without persisting to the database.
 */
class VirtualSavedTest
{
    public readonly string $uuid;
    public readonly string $name;
    public readonly string $slug;
    public readonly array $filters;
    public readonly ?string $description;
    public readonly ?int $id;
    public readonly bool $exists;

    /** @var int Total questions available for this test */
    public int $totalQuestionsAvailable = 0;

    public function __construct(
        string $name,
        string $slug,
        array $filters,
        ?string $description = null,
        int $totalQuestionsAvailable = 0
    ) {
        $this->uuid = (string) Str::uuid();
        $this->name = $name;
        $this->slug = $slug;
        $this->filters = $filters;
        $this->description = $description;
        $this->id = null;
        $this->exists = false;
        $this->totalQuestionsAvailable = $totalQuestionsAvailable;
    }

    /**
     * Create a virtual test for a level pair with the given filters.
     */
    public static function forLevelPair(
        array $levelPair,
        array $tagNames,
        string $contextPrefix,
        int $questionsPerTest = 15,
        int $totalQuestionsAvailable = 0
    ): self {
        [$levelFrom, $levelTo] = $levelPair;

        $slug = self::createSlug($contextPrefix, $levelFrom, $levelTo, $tagNames);
        $name = self::createName($contextPrefix, $levelFrom, $levelTo);
        $filters = self::buildFilters($levelFrom, $levelTo, $tagNames, $questionsPerTest);

        return new self($name, $slug, $filters, null, $totalQuestionsAvailable);
    }

    /**
     * Check if this is a virtual (non-persisted) test.
     */
    public function isVirtual(): bool
    {
        return true;
    }

    /**
     * Set the total questions available count (fluent setter for chaining).
     */
    public function setTotalQuestionsAvailable(int $count): self
    {
        $this->totalQuestionsAvailable = $count;
        return $this;
    }

    /**
     * Get attribute (for compatibility with Eloquent-style access).
     */
    public function getAttribute(string $key): mixed
    {
        // Whitelist of allowed attributes for Eloquent-style access
        $allowedAttributes = [
            'total_questions_available' => fn() => $this->totalQuestionsAvailable,
            'uuid' => fn() => $this->uuid,
            'name' => fn() => $this->name,
            'slug' => fn() => $this->slug,
            'filters' => fn() => $this->filters,
            'description' => fn() => $this->description,
            'id' => fn() => $this->id,
            'exists' => fn() => $this->exists,
        ];

        if (isset($allowedAttributes[$key])) {
            return $allowedAttributes[$key]();
        }

        return null;
    }

    /**
     * Create empty question links collection (for compatibility).
     */
    public function getQuestionLinksAttribute(): Collection
    {
        return collect();
    }

    /**
     * Property access for questionLinks.
     */
    public function __get(string $name): mixed
    {
        if ($name === 'questionLinks') {
            return $this->getQuestionLinksAttribute();
        }

        return null;
    }

    /**
     * Check if property is set.
     */
    public function __isset(string $name): bool
    {
        return in_array($name, ['uuid', 'name', 'slug', 'filters', 'description', 'id', 'exists', 'questionLinks', 'totalQuestionsAvailable'], true);
    }

    /**
     * Create a deterministic slug based on context and filters.
     */
    private static function createSlug(string $contextPrefix, string $levelFrom, string $levelTo, array $tagNames): string
    {
        $parts = array_filter([
            $contextPrefix,
            'auto',
            strtolower($levelFrom),
            strtolower($levelTo),
        ]);

        $sortedTags = $tagNames;
        sort($sortedTags);
        $tagHash = substr(md5(implode(',', $sortedTags)), 0, 8);
        $parts[] = $tagHash;

        return Str::slug(implode('-', $parts));
    }

    /**
     * Create a test name.
     */
    private static function createName(string $contextPrefix, string $levelFrom, string $levelTo): string
    {
        $prefix = $contextPrefix ? "{$contextPrefix}: " : '';
        return "{$prefix}Тест {$levelFrom}-{$levelTo}";
    }

    /**
     * Build filters for a generated test.
     */
    private static function buildFilters(string $levelFrom, string $levelTo, array $tagNames, int $questionsPerTest): array
    {
        return [
            'only_ai_v2' => true,
            'tags' => $tagNames,
            'levels' => [$levelFrom, $levelTo],
            'num_questions' => $questionsPerTest,
            'randomize_filtered' => true,
            '__meta' => ['mode' => 'filters'],
        ];
    }
}
