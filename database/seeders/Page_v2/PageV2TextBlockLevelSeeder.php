<?php

namespace Database\Seeders\Page_v2;

use App\Models\Page;
use App\Models\TextBlock;
use App\Support\Database\Seeder;
use Illuminate\Support\Collection;

class PageV2TextBlockLevelSeeder extends Seeder
{
    private const LEVEL_ORDER = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    public function run(): void
    {
        Page::query()
            ->with(['tags:id,name', 'textBlocks'])
            ->where('seeder', 'like', 'Database\\\\Seeders\\\\Page_v2\\\\%')
            ->each(function (Page $page) {
                $pageLevel = $this->determineLevelFromTags($page->tags->pluck('name'));

                $page->textBlocks->each(function (TextBlock $block) use ($page, $pageLevel) {
                    $blockLevel = $pageLevel ?? $this->extractLevelFromBlock($block->body);

                    $block->forceFill([
                        'level' => $blockLevel,
                        'seeder' => $page->seeder ?? $block->seeder,
                    ])->save();
                });
            });
    }

    private function determineLevelFromTags(Collection $tags): ?string
    {
        return $this->highestLevel($tags->map(fn ($tag) => (string) $tag));
    }

    private function extractLevelFromBlock(?string $body): ?string
    {
        if (! $body) {
            return null;
        }

        $decodedBody = json_decode($body, true);

        if (is_array($decodedBody) && isset($decodedBody['level'])) {
            return $this->highestLevel([(string) $decodedBody['level']]);
        }

        return $this->highestLevel([(string) $body]);
    }

    private function highestLevel(iterable $candidates): ?string
    {
        $levels = collect(self::LEVEL_ORDER)->flip();

        $foundLevels = collect($candidates)
            ->map(fn ($candidate) => $this->findLevelsInString((string) $candidate))
            ->flatten()
            ->unique();

        if ($foundLevels->isEmpty()) {
            return null;
        }

        return $foundLevels
            ->sort(fn ($left, $right) => $levels[$left] <=> $levels[$right])
            ->last();
    }

    private function findLevelsInString(string $candidate): array
    {
        preg_match_all('/A1|A2|B1|B2|C1|C2/i', $candidate, $matches);

        return collect($matches[0] ?? [])
            ->map(fn ($level) => strtoupper($level))
            ->filter(fn ($level) => in_array($level, self::LEVEL_ORDER, true))
            ->values()
            ->all();
    }
}
