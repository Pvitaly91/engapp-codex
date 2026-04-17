<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Models\TheoryVariant;
use App\Models\TheoryVariantSelection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TheoryVariantResolver
{
    protected ?bool $variantTablesAvailable = null;

    /**
     * @param  EloquentCollection<int, TextBlock>  $allBlocks
     * @return array<string, mixed>
     */
    public function resolve(
        Page|PageCategory $target,
        EloquentCollection $allBlocks,
        string $preferredLocale,
        string $fallbackLocale,
        ?Request $request = null
    ): array {
        $request ??= request();
        [$baseBlocks, $baseLocale] = $this->filterBlocksByLocale($allBlocks, $preferredLocale, $fallbackLocale);

        if (! $this->variantTablesAvailable()) {
            $subtitleBlock = $baseBlocks->firstWhere(fn (TextBlock $block) => $block->type === 'subtitle');

            return [
                'title' => $target->title,
                'subtitleBlock' => $subtitleBlock,
                'blocks' => $baseBlocks,
                'columns' => [
                    'left' => $baseBlocks->filter(fn (TextBlock $block) => $block->column === 'left'),
                    'right' => $baseBlocks->filter(fn (TextBlock $block) => $block->column === 'right'),
                ],
                'locale' => $baseLocale,
                'practiceReferenceBlocks' => $baseBlocks,
                'availableVariants' => collect(),
                'currentVariant' => null,
                'currentVariantKey' => 'base',
                'currentVariantSource' => 'base',
                'publishedVariant' => null,
                'publishedVariantKey' => 'base',
                'targetType' => $target instanceof Page ? 'page' : 'category',
                'targetId' => $target->getKey(),
                'requestedVariantKey' => null,
            ];
        }

        $availableVariants = TheoryVariant::query()
            ->ready()
            ->where('variantable_type', $target::class)
            ->where('variantable_id', $target->getKey())
            ->where('locale', $preferredLocale)
            ->orderBy('label')
            ->orderBy('variant_key')
            ->get();

        $selection = TheoryVariantSelection::query()
            ->with('theoryVariant')
            ->where('variantable_type', $target::class)
            ->where('variantable_id', $target->getKey())
            ->where('locale', $preferredLocale)
            ->first();

        $publishedVariant = $selection?->theoryVariant;

        if ($publishedVariant && $publishedVariant->status !== 'ready') {
            $publishedVariant = null;
        }

        $requestedVariantKey = trim((string) $request->query('variant', ''));
        $isAdmin = $request->session()->get('admin_authenticated', false) === true;
        $isBasePreview = $isAdmin && $requestedVariantKey === 'base';
        $previewVariant = null;

        if ($isAdmin && $requestedVariantKey !== '' && $requestedVariantKey !== 'base') {
            $previewVariant = $availableVariants->firstWhere('variant_key', $requestedVariantKey);
        }

        $activeVariant = $isBasePreview ? null : ($previewVariant ?? $publishedVariant);
        $resolvedTitle = trim((string) data_get($activeVariant?->payload, 'title', ''));
        $displayBlocks = $activeVariant
            ? $this->buildVariantBlocks($target, $activeVariant, $baseBlocks)
            : $baseBlocks;

        $subtitleBlock = $displayBlocks->firstWhere(fn (TextBlock $block) => $block->type === 'subtitle');

        return [
            'title' => $resolvedTitle !== '' ? $resolvedTitle : $target->title,
            'subtitleBlock' => $subtitleBlock,
            'blocks' => $displayBlocks,
            'columns' => [
                'left' => $displayBlocks->filter(fn (TextBlock $block) => $block->column === 'left'),
                'right' => $displayBlocks->filter(fn (TextBlock $block) => $block->column === 'right'),
            ],
            'locale' => $activeVariant?->locale ?? $baseLocale,
            'practiceReferenceBlocks' => $baseBlocks,
            'availableVariants' => $availableVariants,
            'currentVariant' => $activeVariant,
            'currentVariantKey' => $isBasePreview ? 'base' : ($activeVariant?->variant_key ?? 'base'),
            'currentVariantSource' => $isBasePreview
                ? 'preview'
                : ($previewVariant ? 'preview' : ($publishedVariant ? 'published' : 'base')),
            'publishedVariant' => $publishedVariant,
            'publishedVariantKey' => $publishedVariant?->variant_key ?? 'base',
            'targetType' => $target instanceof Page ? 'page' : 'category',
            'targetId' => $target->getKey(),
            'requestedVariantKey' => $requestedVariantKey !== '' ? $requestedVariantKey : null,
        ];
    }

    protected function variantTablesAvailable(): bool
    {
        if ($this->variantTablesAvailable !== null) {
            return $this->variantTablesAvailable;
        }

        return $this->variantTablesAvailable = Schema::hasTable('theory_variants')
            && Schema::hasTable('theory_variant_selections');
    }

    /**
     * @param  EloquentCollection<int, TextBlock>  $blocks
     * @return array{0: EloquentCollection<int, TextBlock>, 1: string}
     */
    protected function filterBlocksByLocale(EloquentCollection $blocks, string $preferredLocale, string $fallbackLocale): array
    {
        $usedLocale = $this->chooseLocaleForBlocks($blocks, $preferredLocale, $fallbackLocale);

        return [
            new EloquentCollection($blocks->where('locale', $usedLocale)->values()->all()),
            $usedLocale,
        ];
    }

    /**
     * @param  EloquentCollection<int, TextBlock>  $blocks
     */
    protected function chooseLocaleForBlocks(EloquentCollection $blocks, string $preferredLocale, string $fallbackLocale): string
    {
        if ($blocks->firstWhere('locale', $preferredLocale)) {
            return $preferredLocale;
        }

        if ($blocks->firstWhere('locale', $fallbackLocale)) {
            return $fallbackLocale;
        }

        return $preferredLocale;
    }

    /**
     * @param  EloquentCollection<int, TextBlock>  $baseBlocks
     * @return EloquentCollection<int, TextBlock>
     */
    protected function buildVariantBlocks(
        Page|PageCategory $target,
        TheoryVariant $variant,
        EloquentCollection $baseBlocks
    ): EloquentCollection {
        $payload = is_array($variant->payload) ? $variant->payload : [];
        $blocks = [];

        $baseSubtitleBlock = $baseBlocks->firstWhere(fn (TextBlock $block) => $block->type === 'subtitle');
        $baseContentBlocks = $baseBlocks
            ->reject(fn (TextBlock $block) => $block->type === 'subtitle')
            ->values();

        $subtitleHtml = trim((string) ($payload['subtitle_html'] ?? ''));

        if ($subtitleHtml !== '') {
            $blocks[] = $this->makeTransientBlock(
                $target,
                [
                    'type' => 'subtitle',
                    'column' => 'header',
                    'heading' => null,
                    'body' => $subtitleHtml,
                    'sort_order' => 0,
                ],
                $variant->locale,
                $baseSubtitleBlock,
                0
            );
        }

        foreach (collect($payload['blocks'] ?? [])->values() as $index => $blockData) {
            if (! is_array($blockData)) {
                continue;
            }

            $sortOrder = isset($blockData['sort_order']) ? (int) $blockData['sort_order'] : (($index + 1) * 10);

            $blocks[] = $this->makeTransientBlock(
                $target,
                $blockData,
                $variant->locale,
                $baseContentBlocks->get($index),
                $sortOrder
            );
        }

        return new EloquentCollection(
            collect($blocks)
                ->sortBy(fn (TextBlock $block) => [(int) ($block->sort_order ?? 0), (string) ($block->id ?? '')])
                ->values()
                ->all()
        );
    }

    /**
     * @param  array<string, mixed>  $blockData
     */
    protected function makeTransientBlock(
        Page|PageCategory $target,
        array $blockData,
        string $locale,
        ?TextBlock $baseBlock,
        int $sortOrder
    ): TextBlock {
        $block = new TextBlock();
        $block->forceFill([
            'id' => $baseBlock?->getKey() ?? (100000 + $sortOrder),
            'uuid' => $baseBlock?->uuid ?? (string) Str::uuid(),
            'page_id' => $target instanceof Page ? $target->getKey() : null,
            'page_category_id' => $target instanceof Page ? $target->page_category_id : $target->getKey(),
            'locale' => $locale,
            'type' => (string) ($blockData['type'] ?? 'box'),
            'column' => (string) ($blockData['column'] ?? 'left'),
            'heading' => data_get($blockData, 'heading'),
            'css_class' => data_get($blockData, 'css_class'),
            'sort_order' => $sortOrder,
            'body' => (string) ($blockData['body'] ?? ''),
            'level' => $baseBlock?->level,
        ]);

        if ($baseBlock?->relationLoaded('tags')) {
            $block->setRelation('tags', $baseBlock->tags);
        }

        return $block;
    }
}
