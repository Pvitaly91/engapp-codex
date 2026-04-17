<?php

namespace Database\Seeders\Pages\Concerns;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TheoryVariant;
use App\Support\Database\Seeder;
use App\Support\TheoryVariantPayloadSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class GrammarPageVariantSeeder extends Seeder
{
    abstract protected function targetType(): string;

    abstract protected function targetCategorySlug(): string;

    protected function targetPageSlug(): ?string
    {
        return null;
    }

    abstract protected function locale(): string;

    abstract protected function variantKey(): string;

    abstract protected function label(): string;

    abstract protected function payload(): array;

    protected function provider(): ?string
    {
        return null;
    }

    protected function model(): ?string
    {
        return null;
    }

    protected function promptVersion(): ?string
    {
        return null;
    }

    protected function status(): string
    {
        return 'ready';
    }

    protected function sourceHash(array $payload): ?string
    {
        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? hash('sha256', $encoded) : null;
    }

    public function previewDefinition(): array
    {
        $payload = TheoryVariantPayloadSanitizer::sanitizePayload(array_merge(
            $this->payload(),
            ['locale' => $this->locale()]
        ));

        return [
            'target_type' => Str::lower(trim($this->targetType())),
            'category_slug' => trim($this->targetCategorySlug()),
            'page_slug' => $this->targetPageSlug(),
            'locale' => TheoryVariantPayloadSanitizer::sanitizeLocale($this->locale()),
            'variant_key' => trim($this->variantKey()),
            'label' => trim($this->label()),
            'provider' => $this->provider(),
            'model' => $this->model(),
            'status' => trim($this->status()) ?: 'ready',
            'prompt_version' => $this->promptVersion(),
            'payload' => $payload,
            'source_hash' => $this->sourceHash($payload),
            'seeder_class' => static::class,
        ];
    }

    public function run(): void
    {
        $definition = $this->previewDefinition();
        $target = $this->resolveTarget($definition['target_type'], $definition['category_slug'], $definition['page_slug']);

        TheoryVariant::query()->updateOrCreate(
            [
                'variantable_type' => $target::class,
                'variantable_id' => $target->getKey(),
                'locale' => $definition['locale'],
                'variant_key' => $definition['variant_key'],
            ],
            [
                'label' => $definition['label'],
                'provider' => $definition['provider'],
                'model' => $definition['model'],
                'status' => $definition['status'],
                'payload' => $definition['payload'],
                'source_hash' => $definition['source_hash'],
                'prompt_version' => $definition['prompt_version'],
                'seeder_class' => static::class,
            ]
        );
    }

    protected function resolveTarget(string $targetType, string $categorySlug, ?string $pageSlug): Model
    {
        $category = PageCategory::query()
            ->where('slug', $categorySlug)
            ->where('type', 'theory')
            ->first();

        if (! $category) {
            throw new \RuntimeException(__('Не знайдено категорію теорії зі slug :slug.', ['slug' => $categorySlug]));
        }

        if ($targetType === 'category') {
            return $category;
        }

        if ($targetType !== 'page') {
            throw new \RuntimeException(__('Непідтримуваний тип цілі варіанта: :type.', ['type' => $targetType]));
        }

        $page = Page::query()
            ->where('slug', (string) $pageSlug)
            ->where('type', 'theory')
            ->where('page_category_id', $category->getKey())
            ->first();

        if (! $page) {
            throw new \RuntimeException(__('Не знайдено сторінку теорії зі slug :slug.', ['slug' => $pageSlug]));
        }

        return $page;
    }
}
