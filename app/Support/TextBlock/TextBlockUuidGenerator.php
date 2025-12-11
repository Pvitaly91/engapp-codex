<?php

namespace App\Support\TextBlock;

use App\Models\TextBlock;
use Ramsey\Uuid\Uuid;

/**
 * Generates deterministic UUIDs for text_blocks based on seeder class and block index.
 * This allows seeders to regenerate the same UUIDs on re-run and enables
 * cross-seeder referencing of text_blocks by UUID.
 */
class TextBlockUuidGenerator
{
    /**
     * Custom namespace UUID for text_blocks.
     * Generated once and used consistently for all text_block UUIDs.
     */
    private const NAMESPACE = '6ba7b810-9dad-11d1-80b4-00c04fd430c8'; // UUID namespace for URL

    /**
     * Generate a deterministic UUID for a text block.
     *
     * The UUID is generated based on:
     * - seeder class name
     * - block index within the seeder
     *
     * @param  string  $seederClass  The fully qualified seeder class name
     * @param  int  $blockIndex  The index of the block within the seeder (0-based)
     * @return string The generated UUID string
     */
    public static function generate(string $seederClass, int $blockIndex): string
    {
        $seed = "textblock:{$seederClass}:{$blockIndex}";

        return (string) Uuid::uuid5(self::NAMESPACE, $seed);
    }

    /**
     * Generate a deterministic UUID for a text block using a custom key.
     *
     * Use this when you need a more specific identifier than just the index,
     * for example when blocks might be reordered.
     *
     * @param  string  $seederClass  The fully qualified seeder class name
     * @param  string  $key  A unique key within the seeder (e.g., 'hero', 'section-1', 'comparison-main')
     * @return string The generated UUID string
     */
    public static function generateWithKey(string $seederClass, string $key): string
    {
        $seed = "textblock:{$seederClass}:key:{$key}";

        return (string) Uuid::uuid5(self::NAMESPACE, $seed);
    }

    /**
     * Find a text block by its UUID.
     *
     * @param  string  $uuid  The UUID to search for
     * @return TextBlock|null The text block if found, null otherwise
     */
    public static function findByUuid(string $uuid): ?TextBlock
    {
        return TextBlock::where('uuid', $uuid)->first();
    }

    /**
     * Get the UUID for a block in a specific seeder by index.
     * Useful for referencing blocks from other seeders.
     *
     * @param  string  $seederClass  The fully qualified seeder class name
     * @param  int  $blockIndex  The index of the block within the seeder (0-based)
     * @return string The deterministic UUID for that block
     */
    public static function getUuidForBlock(string $seederClass, int $blockIndex): string
    {
        return self::generate($seederClass, $blockIndex);
    }

    /**
     * Get the UUID for a block in a specific seeder by key.
     * Useful for referencing blocks from other seeders.
     *
     * @param  string  $seederClass  The fully qualified seeder class name
     * @param  string  $key  A unique key within the seeder
     * @return string The deterministic UUID for that block
     */
    public static function getUuidForBlockByKey(string $seederClass, string $key): string
    {
        return self::generateWithKey($seederClass, $key);
    }
}
